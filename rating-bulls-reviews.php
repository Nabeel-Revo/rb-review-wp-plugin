<?php
/**
 * Plugin Name: Rating Bulls Reviews
 * Plugin URI: https://ratingbulls.com
 * Description: Display your Rating Bulls reviews and ratings on your WordPress site.
 * Version: 1.1.1
 * Author: Rating Bulls
 * Author URI: https://ratingbulls.com
 * License: GPL v2 or later
 * Text Domain: rating-bulls-reviews
 * GitHub Plugin URI: https://github.com/RatingBulls/rating-bulls-wp-plugin
 * GitHub Branch: main
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RBR_VERSION', '1.1.1');
define('RBR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RBR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RBR_API_BASE', 'https://api-v1.ratingbulls.com/public/client');

// GitHub repository configuration for auto-updates
define('RBR_GITHUB_USERNAME', 'Nabeel-Revo');
define('RBR_GITHUB_REPO', 'rb-review-wp-plugin');

/**
 * Get rating label and color based on rating value
 * 
 * @param float $rating The rating value (0-5)
 * @return array Array with 'label' and 'color' keys
 */
function rbr_get_rating_data($rating) {
    if ($rating === null || $rating === 0) {
        return array('label' => 'Not rated', 'color' => '#9CA3AF');
    }
    if ($rating >= 4) {
        return array('label' => 'Excellent', 'color' => '#73CF11');
    }
    if ($rating >= 3) {
        return array('label' => 'Good', 'color' => '#73CF11');
    }
    if ($rating >= 2) {
        return array('label' => 'Average', 'color' => '#FFCE00');
    }
    if ($rating >= 1) {
        return array('label' => 'Poor', 'color' => '#FF8622');
    }
    return array('label' => 'Bad', 'color' => '#FF3722');
}

/**
 * Get rating color based on rating value
 * 
 * @param float $rating The rating value (0-5)
 * @return string Hex color code
 */
function rbr_get_rating_color($rating) {
    if ($rating >= 4.5) return '#00B67A';
    if ($rating >= 4.0) return '#73CF11';
    if ($rating >= 3.0) return '#FFCE00';
    if ($rating >= 2.0) return '#FF8622';
    return '#FF3722';
}

/**
 * Render bull rating icons
 * 
 * @param float $rating The rating value (0-5)
 * @param string $size Size variant: 'sm', 'md', 'lg'
 * @return string HTML for bull icons
 */
function rbr_render_bull_icons($rating, $size = 'md') {
    $rounded_rating = round($rating);
    $rating_color = rbr_get_rating_color($rating);
    $empty_color = '#E5E7EB';
    
    // Size configurations
    $sizes = array(
        'sm' => array('container' => '20px', 'icon' => '12px'),
        'md' => array('container' => '28px', 'icon' => '16px'),
        'lg' => array('container' => '36px', 'icon' => '22px'),
    );
    
    $config = isset($sizes[$size]) ? $sizes[$size] : $sizes['md'];
    
    // Bull icon SVG path
    $bull_svg = '<svg class="rbr-bull-svg" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: ' . $config['icon'] . '; height: ' . $config['icon'] . ';">
        <g clip-path="url(#rbr-bull-clip)">
            <path d="M11.9572 4.88675C12.0191 4.92455 12.0779 4.96047 12.1337 4.99456L12.8931 5.45816C15.2874 4.70306 17 4.07313 17 4.07313L15.619 0L15.1686 2.85136L11.3047 2.7949L13.1623 5.37256L13.1449 5.37811C13.0618 5.40453 12.9779 5.43146 12.8931 5.45816L12.1337 4.99456C12.0779 4.96047 12.0191 4.92455 11.9572 4.88675Z" fill="white"/>
            <path d="M5.04305 4.88651C4.98115 4.92431 4.92233 4.96024 4.86655 4.99433L4.10715 5.45793C4.02227 5.43121 3.93827 5.40449 3.85515 5.37805L3.83789 5.37256L5.69551 2.79489L1.83143 2.85136L1.38099 0L0 4.07313C0 4.07313 1.71285 4.70283 4.10715 5.45793L4.86655 4.99433C4.92233 4.96024 4.98115 4.92431 5.04305 4.88651Z" fill="white"/>
            <path d="M4.63299 7.42806L4.5248 7.45725L4.28691 7.07119L3.92156 5.67732L3.91464 5.64207L2.97407 5.33984L1.6875 6.20923L3.40482 7.76263L4.63299 7.42806Z" fill="white"/>
            <path d="M9.81774 13.5996H7.18262L7.80634 14.0006H9.19401L9.81774 13.5996Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.81139 2.79289L4.5415 4.70717L4.73302 4.87656L7.61112 7.42316L8.49997 8.20956L9.38849 7.42316L12.2666 4.87656L12.4584 4.70717L11.1882 2.79289L8.49997 2.22656L5.81139 2.79289Z" fill="white"/>
            <path d="M13.0856 5.64219L13.079 5.67732L12.7137 7.07119L12.4755 7.45725L12.3677 7.42809L13.5959 7.76263L15.3128 6.20923L14.0263 5.33984L13.0856 5.64219Z" fill="white"/>
            <path d="M4.54181 4.70714L4.43311 4.89479L7.49117 7.47774L6.80508 11.0558L6.03488 12.0195L5.82692 9.36315L4.41857 7.08016L4.10693 5.45771L4.54181 4.70714Z" fill="white"/>
            <path d="M6.97571 11.0095L7.58724 7.55888L8.5 8.32953L9.41254 7.55907L10.0425 11.1112L10.0185 10.9785L9.22503 11.3135H7.77464L6.98121 10.9785L6.97571 11.0095Z" fill="white"/>
            <path d="M9.50871 7.47786L10.1948 11.0558L10.9651 12.0196L11.1734 9.36315L12.5814 7.08016L12.8934 5.45771L12.4586 4.70703L12.5672 4.89479L9.50871 7.47786Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.89716 13.4154L6.05469 12.8739V12.2743L6.95755 11.1113L7.77467 11.4566H9.22507L10.0425 11.1113L10.9451 12.2743V12.8739L10.7485 13.0004L10.1026 13.4154H6.89716ZM7.03355 12.041H7.32049L7.73322 12.4359L6.9458 12.423L7.03355 12.041ZM9.67966 12.041H9.96661L10.054 12.423L9.2666 12.4359L9.67966 12.041Z" fill="white"/>
        </g>
        <defs>
            <clipPath id="rbr-bull-clip">
                <rect width="17" height="14" fill="white"/>
            </clipPath>
        </defs>
    </svg>';
    
    $output = '<div class="rbr-bull-icons rbr-bull-icons-' . esc_attr($size) . '">';
    
    for ($i = 1; $i <= 5; $i++) {
        $is_filled = $i <= $rounded_rating;
        $bg_color = $is_filled ? $rating_color : $empty_color;
        
        $output .= '<div class="rbr-bull-icon-wrapper" style="width: ' . $config['container'] . '; height: ' . $config['container'] . '; background-color: ' . $bg_color . ';">';
        $output .= $bull_svg;
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Get initials from customer name
 * 
 * @param string $name Customer name
 * @return string Initials (max 2 characters)
 */
function rbr_get_initials($name) {
    if (empty($name) || $name === 'Anonymous') {
        return '?';
    }
    
    $parts = explode(' ', trim($name));
    $initials = '';
    
    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
    }
    
    return $initials ?: '?';
}

/**
 * Get avatar background color based on name
 * 
 * @param string $name Customer name
 * @return string Hex color code
 */
function rbr_get_avatar_color($name) {
    $colors = array(
        '#4CAF50', // Green
        '#2196F3', // Blue
        '#9C27B0', // Purple
        '#FF9800', // Orange
        '#00BCD4', // Cyan
        '#E91E63', // Pink
        '#3F51B5', // Indigo
        '#009688', // Teal
    );
    
    // Generate a consistent index based on the name
    $hash = 0;
    for ($i = 0; $i < strlen($name); $i++) {
        $hash = ord($name[$i]) + (($hash << 5) - $hash);
    }
    
    $index = abs($hash) % count($colors);
    return $colors[$index];
}

/**
 * Main Plugin Class
 */
class RatingBullsReviews {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Load includes
        $this->load_includes();
        
        // Initialize GitHub updater for auto-updates
        $this->init_github_updater();
        
        // Initialize hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // Register shortcode
        add_shortcode('ratingbulls_widget', array($this, 'render_widget_shortcode'));
        add_shortcode('ratingbulls_badge', array($this, 'render_badge_shortcode'));
    }
    
    /**
     * Initialize GitHub updater for automatic updates
     */
    private function init_github_updater() {
        if (is_admin()) {
            new RBR_GitHub_Updater(
                __FILE__,
                RBR_GITHUB_USERNAME,
                RBR_GITHUB_REPO
            );
            
            // Initialize admin notices for update check feedback
            RBR_GitHub_Updater::init_admin_notices();
        }
    }
    
    /**
     * Load required files
     */
    private function load_includes() {
        require_once RBR_PLUGIN_DIR . 'includes/class-api-client.php';
        require_once RBR_PLUGIN_DIR . 'includes/class-github-updater.php';
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            'Rating Bulls Reviews',
            'Rating Bulls',
            'manage_options',
            'rating-bulls-reviews',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('rbr_settings_group', 'rbr_company_domain', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        
        register_setting('rbr_settings_group', 'rbr_cache_duration', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 6
        ));
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'rbr-frontend-styles',
            RBR_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            RBR_VERSION
        );
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        if ('settings_page_rating-bulls-reviews' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'rbr-admin-styles',
            RBR_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            RBR_VERSION
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current values
        $domain = get_option('rbr_company_domain', '');
        $cache_duration = get_option('rbr_cache_duration', 6);
        
        // Test connection if domain is set
        $connection_status = null;
        if (!empty($domain)) {
            $api_client = new RBR_API_Client();
            $company_data = $api_client->get_company_by_domain($domain);
            $connection_status = $company_data ? 'success' : 'error';
        }
        
        ?>
        <div class="wrap rbr-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="rbr-admin-container">
                <div class="rbr-admin-main">
                    <form action="options.php" method="post">
                        <?php
                        settings_fields('rbr_settings_group');
                        ?>
                        
                        <div class="rbr-settings-card">
                            <h2>Company Settings</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="rbr_company_domain">Your Website Domain</label>
                                    </th>
                                    <td>
                                        <input 
                                            type="text" 
                                            id="rbr_company_domain" 
                                            name="rbr_company_domain" 
                                            value="<?php echo esc_attr($domain); ?>" 
                                            class="regular-text"
                                            placeholder="example.com"
                                        />
                                        <p class="description">
                                            Enter your company domain as registered on Rating Bulls (without https://).
                                        </p>
                                        
                                        <?php if ($connection_status === 'success'): ?>
                                            <p class="rbr-status rbr-status-success">
                                                ✓ Connected successfully to Rating Bulls
                                            </p>
                                        <?php elseif ($connection_status === 'error'): ?>
                                            <p class="rbr-status rbr-status-error">
                                                ✗ Could not find company. Please check your domain.
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="rbr_cache_duration">Cache Duration (hours)</label>
                                    </th>
                                    <td>
                                        <input 
                                            type="number" 
                                            id="rbr_cache_duration" 
                                            name="rbr_cache_duration" 
                                            value="<?php echo esc_attr($cache_duration); ?>" 
                                            class="small-text"
                                            min="1"
                                            max="24"
                                        />
                                        <p class="description">
                                            How long to cache Rating Bulls data (1-24 hours). Lower values mean fresher data but more API calls.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button('Save Settings'); ?>
                        </div>
                    </form>
                    
                    <?php if ($connection_status === 'success'): ?>
                    <div class="rbr-settings-card">
                        <h2>Clear Cache</h2>
                        <p>If your reviews have been updated and you want to see the changes immediately, clear the cache.</p>
                        <form method="post" action="">
                            <?php wp_nonce_field('rbr_clear_cache', 'rbr_cache_nonce'); ?>
                            <button type="submit" name="rbr_clear_cache" class="button button-secondary">
                                Clear Cache
                            </button>
                        </form>
                        <?php
                        // Handle cache clearing
                        if (isset($_POST['rbr_clear_cache']) && wp_verify_nonce($_POST['rbr_cache_nonce'], 'rbr_clear_cache')) {
                            $api_client = new RBR_API_Client();
                            $api_client->clear_cache();
                            echo '<p class="rbr-status rbr-status-success">✓ Cache cleared successfully</p>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="rbr-admin-sidebar">
                    <div class="rbr-settings-card">
                        <h2>How to Use</h2>
                        <p>Add the Rating Bulls widget to your site using these shortcodes:</p>
                        
                        <h3>Full Widget</h3>
                        <code>[ratingbulls_widget]</code>
                        <p class="description">Shows rating, star distribution, and recent reviews.</p>
                        
                        <h3>Simple Badge</h3>
                        <code>[ratingbulls_badge]</code>
                        <p class="description">Shows a compact rating badge.</p>
                        
                        <h3>Shortcode Options</h3>
                        <p><code>[ratingbulls_widget reviews="5"]</code></p>
                        <p class="description">Show 5 reviews instead of default 3.</p>
                    </div>
                    
                    <div class="rbr-settings-card">
                        <h2>Plugin Info</h2>
                        <p><strong>Version:</strong> <?php echo esc_html(RBR_VERSION); ?></p>
                        <p>Updates are delivered automatically through WordPress.</p>
                    </div>
                    
                    <div class="rbr-settings-card">
                        <h2>Need Help?</h2>
                        <p>Visit <a href="https://ratingbulls.com/help" target="_blank">Rating Bulls Help Center</a> for documentation and support.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render the full widget shortcode
     */
    public function render_widget_shortcode($atts) {
        $atts = shortcode_atts(array(
            'reviews' => 3,
        ), $atts, 'ratingbulls_widget');
        
        $domain = get_option('rbr_company_domain', '');
        
        if (empty($domain)) {
            if (current_user_can('manage_options')) {
                return '<p class="rbr-error">Rating Bulls: Please configure your domain in <a href="' . admin_url('options-general.php?page=rating-bulls-reviews') . '">Settings</a>.</p>';
            }
            return '';
        }
        
        $api_client = new RBR_API_Client();
        $company = $api_client->get_company_by_domain($domain);
        
        if (!$company) {
            if (current_user_can('manage_options')) {
                return '<p class="rbr-error">Rating Bulls: Could not fetch company data. Please check your settings.</p>';
            }
            return '';
        }
        
        // Get reviews
        $reviews = $api_client->get_reviews($company['id'], intval($atts['reviews']));
        
        // Start output buffering
        ob_start();
        
        include RBR_PLUGIN_DIR . 'templates/widget.php';
        
        return ob_get_clean();
    }
    
    /**
     * Render the badge shortcode
     */
    public function render_badge_shortcode($atts) {
        $domain = get_option('rbr_company_domain', '');
        
        if (empty($domain)) {
            if (current_user_can('manage_options')) {
                return '<p class="rbr-error">Rating Bulls: Please configure your domain in Settings.</p>';
            }
            return '';
        }
        
        $api_client = new RBR_API_Client();
        $company = $api_client->get_company_by_domain($domain);
        
        if (!$company) {
            return '';
        }
        
        ob_start();
        
        include RBR_PLUGIN_DIR . 'templates/badge.php';
        
        return ob_get_clean();
    }
}

// Initialize the plugin
function rbr_init() {
    return RatingBullsReviews::get_instance();
}
add_action('plugins_loaded', 'rbr_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options
    add_option('rbr_company_domain', '');
    add_option('rbr_cache_duration', 6);
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clear cached data
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_rbr_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_rbr_%'");
});

add_action( 'wp_enqueue_scripts', 'rbr_enqueue_fonts' );
function rbr_enqueue_fonts() {
    // 400 (regular), 500 (medium), 600 (semi-bold), and 700 (bold)
    wp_enqueue_style( 
        'rbr-google-fonts', 
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', 
        array(), 
        null 
    );
}
