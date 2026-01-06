<?php
/**
 * GitHub Plugin Updater
 * 
 * Enables automatic updates for the Rating Bulls Reviews plugin from GitHub releases.
 * 
 * @package RatingBullsReviews
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RBR_GitHub_Updater {
    
    /**
     * GitHub repository owner/username
     * @var string
     */
    private $github_username;
    
    /**
     * GitHub repository name
     * @var string
     */
    private $github_repo;
    
    /**
     * Plugin slug (folder name)
     * @var string
     */
    private $plugin_slug;
    
    /**
     * Plugin basename (folder/file.php)
     * @var string
     */
    private $plugin_basename;
    
    /**
     * Current plugin version
     * @var string
     */
    private $current_version;
    
    /**
     * GitHub API response cache
     * @var object|null
     */
    private $github_response;
    
    /**
     * Cache key for GitHub API response
     * @var string
     */
    private $cache_key = 'rbr_github_update_check';
    
    /**
     * Cache duration in seconds (12 hours)
     * @var int
     */
    private $cache_duration = 43200;
    
    /**
     * Constructor
     * 
     * @param string $plugin_file Main plugin file path (__FILE__ from main plugin)
     * @param string $github_username GitHub username or organization
     * @param string $github_repo GitHub repository name
     */
    public function __construct($plugin_file, $github_username, $github_repo) {
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;
        $this->plugin_slug = dirname(plugin_basename($plugin_file));
        $this->plugin_basename = plugin_basename($plugin_file);
        
        // Get current version from plugin header
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data($plugin_file);
        $this->current_version = $plugin_data['Version'];
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
        
        // Add "Check for updates" link in plugins page
        add_filter('plugin_action_links_' . $this->plugin_basename, array($this, 'add_action_links'));
        
        // Handle manual update check
        add_action('admin_init', array($this, 'handle_manual_update_check'));
    }
    
    /**
     * Get the latest release info from GitHub API
     * 
     * @param bool $force_check Bypass cache and check GitHub directly
     * @return object|false Release info or false on failure
     */
    private function get_github_release_info($force_check = false) {
        // Return cached response if available
        if ($this->github_response !== null && !$force_check) {
            return $this->github_response;
        }
        
        // Check transient cache
        if (!$force_check) {
            $cached = get_transient($this->cache_key);
            if ($cached !== false) {
                $this->github_response = $cached;
                return $this->github_response;
            }
        }
        
        // Fetch from GitHub API
        $api_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->github_username,
            $this->github_repo
        );
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
            ),
        ));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $release = json_decode($body);
        
        if (empty($release) || !isset($release->tag_name)) {
            return false;
        }
        
        // Cache the response
        set_transient($this->cache_key, $release, $this->cache_duration);
        $this->github_response = $release;
        
        return $this->github_response;
    }
    
    /**
     * Parse version from tag name (removes 'v' prefix if present)
     * 
     * @param string $tag_name GitHub release tag name
     * @return string Clean version number
     */
    private function parse_version($tag_name) {
        return ltrim($tag_name, 'vV');
    }
    
    /**
     * Get the download URL for the latest release
     * 
     * @param object $release GitHub release object
     * @return string Download URL
     */
    private function get_download_url($release) {
        // First, check for a specific asset named like the plugin
        if (!empty($release->assets)) {
            foreach ($release->assets as $asset) {
                // Look for a zip file that matches our plugin name
                if (strpos($asset->name, '.zip') !== false) {
                    return $asset->browser_download_url;
                }
            }
        }
        
        // Fallback to the source code zipball
        return $release->zipball_url;
    }
    
    /**
     * Check for plugin updates (hooked to pre_set_site_transient_update_plugins)
     * 
     * @param object $transient WordPress update transient
     * @return object Modified transient
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $release = $this->get_github_release_info();
        
        if (!$release) {
            return $transient;
        }
        
        $latest_version = $this->parse_version($release->tag_name);
        
        // Compare versions
        if (version_compare($this->current_version, $latest_version, '<')) {
            $plugin = new stdClass();
            $plugin->slug = $this->plugin_slug;
            $plugin->plugin = $this->plugin_basename;
            $plugin->new_version = $latest_version;
            $plugin->url = $release->html_url;
            $plugin->package = $this->get_download_url($release);
            $plugin->tested = get_bloginfo('version');
            $plugin->icons = array(
                '1x' => 'https://ratingbulls.com/wp-plugin-icon-128x128.png',
                '2x' => 'https://ratingbulls.com/wp-plugin-icon-256x256.png',
            );
            
            $transient->response[$this->plugin_basename] = $plugin;
        } else {
            // No update available - add to no_update array
            $plugin = new stdClass();
            $plugin->slug = $this->plugin_slug;
            $plugin->plugin = $this->plugin_basename;
            $plugin->new_version = $this->current_version;
            $plugin->url = '';
            $plugin->package = '';
            
            $transient->no_update[$this->plugin_basename] = $plugin;
        }
        
        return $transient;
    }
    
    /**
     * Provide plugin info for the update details popup
     * 
     * @param false|object|array $result The result object or array
     * @param string $action The type of information being requested
     * @param object $args Plugin API arguments
     * @return false|object Plugin info or false
     */
    public function plugin_info($result, $action, $args) {
        // Only handle plugin_information action for our plugin
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }
        
        $release = $this->get_github_release_info();
        
        if (!$release) {
            return $result;
        }
        
        $plugin_info = new stdClass();
        $plugin_info->name = 'Rating Bulls Reviews';
        $plugin_info->slug = $this->plugin_slug;
        $plugin_info->version = $this->parse_version($release->tag_name);
        $plugin_info->author = '<a href="https://ratingbulls.com">Rating Bulls</a>';
        $plugin_info->author_profile = 'https://ratingbulls.com';
        $plugin_info->homepage = 'https://ratingbulls.com';
        $plugin_info->requires = '5.0';
        $plugin_info->tested = get_bloginfo('version');
        $plugin_info->requires_php = '7.4';
        $plugin_info->downloaded = 0;
        $plugin_info->last_updated = $release->published_at;
        $plugin_info->download_link = $this->get_download_url($release);
        
        // Parse changelog from release body (markdown)
        $plugin_info->sections = array(
            'description' => 'Display your Rating Bulls reviews and ratings on your WordPress site with beautiful, customizable widgets.',
            'installation' => '<ol>
                <li>Upload the plugin files to <code>/wp-content/plugins/rating-bulls-reviews</code></li>
                <li>Activate the plugin through the "Plugins" screen in WordPress</li>
                <li>Go to Settings → Rating Bulls to configure your company domain</li>
                <li>Add the shortcode <code>[ratingbulls_widget]</code> to any page or post</li>
            </ol>',
            'changelog' => $this->parse_changelog($release->body),
        );
        
        // Banners
        $plugin_info->banners = array(
            'low' => 'https://ratingbulls.com/wp-plugin-banner-772x250.png',
            'high' => 'https://ratingbulls.com/wp-plugin-banner-1544x500.png',
        );
        
        return $plugin_info;
    }
    
    /**
     * Parse GitHub release body markdown to HTML changelog
     * 
     * @param string $body GitHub release body (markdown)
     * @return string HTML changelog
     */
    private function parse_changelog($body) {
        if (empty($body)) {
            return '<p>See the <a href="https://github.com/' . esc_attr($this->github_username) . '/' . esc_attr($this->github_repo) . '/releases" target="_blank">GitHub releases page</a> for full changelog.</p>';
        }
        
        // Basic markdown to HTML conversion
        $html = esc_html($body);
        
        // Convert markdown headers
        $html = preg_replace('/^### (.+)$/m', '<h4>$1</h4>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $html);
        
        // Convert markdown lists
        $html = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        
        // Wrap consecutive list items in ul tags
        $html = preg_replace('/(<li>.+<\/li>\n?)+/', '<ul>$0</ul>', $html);
        
        // Convert bold
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        
        // Convert line breaks
        $html = nl2br($html);
        
        return $html;
    }
    
    /**
     * Handle post-install to ensure correct folder name
     * 
     * @param bool $response Installation response
     * @param array $hook_extra Extra arguments passed to the hook
     * @param array $result Installation result data
     * @return array Modified result
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        
        // Only process our plugin
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_basename) {
            return $result;
        }
        
        // Get the installed directory
        $installed_dir = $result['destination'];
        
        // Get the correct plugin directory path
        $correct_dir = WP_PLUGIN_DIR . '/' . $this->plugin_slug;
        
        // If the directories are different, rename
        if ($installed_dir !== $correct_dir) {
            $wp_filesystem->move($installed_dir, $correct_dir);
            $result['destination'] = $correct_dir;
            $result['destination_name'] = $this->plugin_slug;
        }
        
        // Re-activate the plugin
        activate_plugin($this->plugin_basename);
        
        return $result;
    }
    
    /**
     * Add action links to plugins page
     * 
     * @param array $links Existing action links
     * @return array Modified action links
     */
    public function add_action_links($links) {
        $check_update_url = wp_nonce_url(
            admin_url('plugins.php?rbr_check_update=1'),
            'rbr_check_update',
            'rbr_nonce'
        );
        
        $links[] = '<a href="' . esc_url($check_update_url) . '">' . __('Check for updates') . '</a>';
        
        return $links;
    }
    
    /**
     * Handle manual update check from plugins page
     */
    public function handle_manual_update_check() {
        if (!isset($_GET['rbr_check_update']) || !isset($_GET['rbr_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_GET['rbr_nonce'], 'rbr_check_update')) {
            return;
        }
        
        if (!current_user_can('update_plugins')) {
            return;
        }
        
        // Clear the cache
        delete_transient($this->cache_key);
        
        // Force WordPress to check for updates
        delete_site_transient('update_plugins');
        
        // Check for update now
        $release = $this->get_github_release_info(true);
        
        // Redirect back to plugins page with message
        $redirect_url = admin_url('plugins.php');
        
        if ($release) {
            $latest_version = $this->parse_version($release->tag_name);
            
            if (version_compare($this->current_version, $latest_version, '<')) {
                $redirect_url = add_query_arg('rbr_update_available', '1', $redirect_url);
            } else {
                $redirect_url = add_query_arg('rbr_up_to_date', '1', $redirect_url);
            }
        } else {
            $redirect_url = add_query_arg('rbr_check_failed', '1', $redirect_url);
        }
        
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    /**
     * Initialize admin notices for update check results
     */
    public static function init_admin_notices() {
        add_action('admin_notices', array(__CLASS__, 'show_admin_notices'));
    }
    
    /**
     * Show admin notices after manual update check
     */
    public static function show_admin_notices() {
        if (isset($_GET['rbr_update_available'])) {
            echo '<div class="notice notice-info is-dismissible"><p>';
            echo '<strong>Rating Bulls Reviews:</strong> A new version is available! ';
            echo '<a href="' . esc_url(admin_url('update-core.php')) . '">Update now</a>';
            echo '</p></div>';
        }
        
        if (isset($_GET['rbr_up_to_date'])) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>Rating Bulls Reviews:</strong> You are running the latest version.';
            echo '</p></div>';
        }
        
        if (isset($_GET['rbr_check_failed'])) {
            echo '<div class="notice notice-error is-dismissible"><p>';
            echo '<strong>Rating Bulls Reviews:</strong> Could not check for updates. Please try again later.';
            echo '</p></div>';
        }
    }
}
