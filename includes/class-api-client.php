<?php
/**
 * Rating Bulls API Client
 * 
 * Handles all communication with the Rating Bulls API
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RBR_API_Client {
    
    /**
     * API base URL
     */
    private $api_base;
    
    /**
     * Cache duration in seconds
     */
    private $cache_duration;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_base = RBR_API_BASE;
        $cache_hours = get_option('rbr_cache_duration', 6);
        $this->cache_duration = intval($cache_hours) * HOUR_IN_SECONDS;
    }
    
    /**
     * Get company data by domain
     * 
     * @param string $domain The company domain
     * @return array|null Company data or null on failure
     */
    public function get_company_by_domain($domain) {
        // Sanitize domain
        $domain = $this->sanitize_domain($domain);
        
        if (empty($domain)) {
            return null;
        }
        
        // Check cache first
        $cache_key = 'rbr_company_' . md5($domain);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Make API request
        $url = $this->api_base . '/companies?domain=' . urlencode($domain);
        $response = $this->make_request($url);
        
        if (!$response || !isset($response['content']) || empty($response['content'])) {
            return null;
        }
        
        $company = $response['content'][0];
        
        // Process the data - remove sensitive/unnecessary fields
        $processed = array(
            'id' => $company['id'],
            'companyName' => $company['companyName'],
            'websiteUrl' => $company['websiteUrl'] ?? '',
            'description' => $company['description'] ?? '',
            'isVerified' => $company['isVerified'] ?? false,
            'averageRating' => floatval($company['averageRating'] ?? 0),
            'totalReviews' => intval($company['totalReviews'] ?? 0),
            'starRatings' => $company['starRatings'] ?? array(0, 0, 0, 0, 0),
        );
        
        // Cache the result
        set_transient($cache_key, $processed, $this->cache_duration);
        
        return $processed;
    }
    
    /**
     * Get reviews for a company
     * 
     * @param int $company_id The company ID
     * @param int $limit Number of reviews to return
     * @param int $min_rating Minimum rating filter (default 4)
     * @return array Array of reviews
     */
    public function get_reviews($company_id, $limit = 3, $min_rating = 4) {
        $company_id = intval($company_id);
        $limit = min(intval($limit), 10); // Cap at 10
        $min_rating = intval($min_rating);
        
        if ($company_id <= 0) {
            return array();
        }
        
        // Check cache first
        $cache_key = 'rbr_reviews_' . $company_id . '_' . $limit . '_min' . $min_rating;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Fetch more reviews than needed to filter by rating
        // We fetch 20 to have a good chance of getting enough 4+ star reviews
        $fetch_size = 20;
        
        // Make API request
        $url = $this->api_base . '/reviews?' . http_build_query(array(
            'page' => 0,
            'size' => $fetch_size,
            'companyIds' => $company_id,
            'statuses' => 'APPROVED'
        ));
        
        $response = $this->make_request($url);
        
        if (!$response || !isset($response['content'])) {
            return array();
        }
        
        // Process and filter reviews
        $reviews = array();
        foreach ($response['content'] as $review) {
            $rating = intval($review['rating'] ?? 0);
            
            // Filter by minimum rating
            if ($rating < $min_rating) {
                continue;
            }
            
            $reviews[] = array(
                'id' => $review['id'],
                'rating' => $rating,
                'title' => $review['title'] ?? '',
                'comment' => $review['comment'] ?? '',
                'customerName' => $this->format_customer_name($review['customer'] ?? array()),
                'customerCountry' => $review['customer']['country'] ?? '',
                'dateOfExperience' => $review['dateOfExperience'] ?? '',
                'createdAt' => $review['createdAt'] ?? '',
                'isVerified' => $review['verified'] ?? false,
            );
            
            // Stop once we have enough reviews
            if (count($reviews) >= $limit) {
                break;
            }
        }
        
        // Cache the result
        set_transient($cache_key, $reviews, $this->cache_duration);
        
        return $reviews;
    }
    
    /**
     * Make an API request
     * 
     * @param string $url The URL to request
     * @return array|null Response data or null on failure
     */
    private function make_request($url) {
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            error_log('Rating Bulls API Error: ' . $response->get_error_message());
            return null;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            error_log('Rating Bulls API Error: HTTP ' . $status_code);
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Rating Bulls API Error: Invalid JSON response');
            return null;
        }
        
        return $data;
    }
    
    /**
     * Sanitize a domain string
     * 
     * @param string $domain The domain to sanitize
     * @return string Sanitized domain
     */
    private function sanitize_domain($domain) {
        // Remove protocol
        $domain = preg_replace('#^https?://#', '', $domain);
        
        // Remove trailing slash
        $domain = rtrim($domain, '/');
        
        // Remove www.
        $domain = preg_replace('#^www\.#', '', $domain);
        
        // Only allow valid domain characters
        $domain = preg_replace('/[^a-zA-Z0-9\-\.]/', '', $domain);
        
        return strtolower($domain);
    }
    
    /**
     * Format customer name for display
     * 
     * @param array $customer Customer data
     * @return string Formatted name
     */
    private function format_customer_name($customer) {
        $first = $customer['firstName'] ?? '';
        $last = $customer['lastName'] ?? '';
        
        if (empty($first) && empty($last)) {
            return 'Anonymous';
        }
        
        // Show first name and last initial for privacy
        if (!empty($last)) {
            return $first . ' ' . substr($last, 0, 1) . '.';
        }
        
        return $first;
    }
    
    /**
     * Clear all cached data
     */
    public function clear_cache() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_rbr_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_rbr_%'");
    }
}
