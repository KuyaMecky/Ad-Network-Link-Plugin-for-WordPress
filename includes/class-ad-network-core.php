<?php
/**
 * Core functionality for Ad Network Link Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ADNL_Core {
    
    public function __construct() {
        // Add any initialization hooks here
    }

    /**
     * Generate unique link ID
     */
    public function generate_link_id() {
        return uniqid('adnl_', true);
    }

    /**
     * Create a new ad link
     */
    public function create_ad_link($name, $image_url, $redirect_url, $is_main_site = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        $link_id = $this->generate_link_id();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'link_id' => $link_id,
                'name' => sanitize_text_field($name),
                'image_url' => esc_url_raw($image_url),
                'redirect_url' => esc_url_raw($redirect_url),
                'is_main_site' => $is_main_site ? 1 : 0,
                'clicks' => 0
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d')
        );

        if ($result) {
            return $link_id;
        }
        
        return false;
    }

    /**
     * Get ad link by ID
     */
    public function get_ad_link($link_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE link_id = %s", $link_id)
        );
        
        return $result;
    }

    /**
     * Get all ad links
     */
    public function get_all_ad_links() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        return $results;
    }

    /**
     * Update ad link
     */
    public function update_ad_link($link_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
            $update_format[] = '%s';
        }
        if (isset($data['image_url'])) {
            $update_data['image_url'] = esc_url_raw($data['image_url']);
            $update_format[] = '%s';
        }
        if (isset($data['redirect_url'])) {
            $update_data['redirect_url'] = esc_url_raw($data['redirect_url']);
            $update_format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('link_id' => $link_id),
            $update_format,
            array('%s')
        );
        
        return $result !== false;
    }

    /**
     * Delete ad link
     */
    public function delete_ad_link($link_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        $result = $wpdb->delete(
            $table_name,
            array('link_id' => $link_id),
            array('%s')
        );
        
        return $result !== false;
    }

    /**
     * Increment click count
     */
    public function increment_clicks($link_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table_name SET clicks = clicks + 1 WHERE link_id = %s",
                $link_id
            )
        );
    }

    /**
     * Track click with detailed information
     */
    public function track_click($link_id, $site_url, $site_name = '') {
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'ad_network_clicks';
        $sites_table = $wpdb->prefix . 'ad_network_sites';

        // Get user information
        $user_ip = $this->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        // Parse user agent
        $device_info = $this->parse_user_agent($user_agent);

        // Get location data (basic, can be enhanced with GeoIP)
        $location = $this->get_location_data($user_ip);

        // Insert click record
        $wpdb->insert(
            $clicks_table,
            array(
                'link_id' => $link_id,
                'site_url' => $site_url,
                'site_name' => $site_name,
                'user_ip' => $user_ip,
                'country' => $location['country'],
                'city' => $location['city'],
                'region' => $location['region'],
                'user_agent' => $user_agent,
                'device_type' => $device_info['device_type'],
                'browser' => $device_info['browser'],
                'os' => $device_info['os'],
                'referrer' => $referrer
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        // Update or insert site statistics
        $existing_site = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $sites_table WHERE site_url = %s", $site_url)
        );

        if ($existing_site) {
            $wpdb->update(
                $sites_table,
                array(
                    'total_clicks' => $existing_site->total_clicks + 1,
                    'last_click' => current_time('mysql')
                ),
                array('site_url' => $site_url),
                array('%d', '%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $sites_table,
                array(
                    'site_url' => $site_url,
                    'site_name' => $site_name,
                    'total_clicks' => 1,
                    'last_click' => current_time('mysql')
                ),
                array('%s', '%s', '%d', '%s')
            );
        }
    }

    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }

    /**
     * Parse user agent for device information
     */
    private function parse_user_agent($user_agent) {
        $device_type = 'Desktop';
        $browser = 'Unknown';
        $os = 'Unknown';

        // Detect device type
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $user_agent)) {
            $device_type = 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
            $device_type = 'Tablet';
        }

        // Detect browser
        if (preg_match('/Edge/i', $user_agent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/MSIE|Trident/i', $user_agent)) {
            $browser = 'Internet Explorer';
        }

        // Detect OS
        if (preg_match('/Windows/i', $user_agent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $user_agent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $user_agent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $user_agent)) {
            $os = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad/i', $user_agent)) {
            $os = 'iOS';
        }

        return array(
            'device_type' => $device_type,
            'browser' => $browser,
            'os' => $os
        );
    }

    /**
     * Get location data from IP
     * Basic version - can be enhanced with GeoIP databases
     */
    private function get_location_data($ip) {
        // Basic implementation - returns empty for now
        // Can be enhanced with services like ipapi.co, ipgeolocation.io, etc.
        return array(
            'country' => '',
            'city' => '',
            'region' => ''
        );
    }

    /**
     * Get all tracked sites
     */
    public function get_all_sites() {
        global $wpdb;
        $sites_table = $wpdb->prefix . 'ad_network_sites';
        
        return $wpdb->get_results(
            "SELECT * FROM $sites_table ORDER BY total_clicks DESC"
        );
    }

    /**
     * Get click statistics by date range
     */
    public function get_click_stats($days = 30) {
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'ad_network_clicks';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(clicked_at) as date, COUNT(*) as clicks 
                FROM $clicks_table 
                WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY DATE(clicked_at)
                ORDER BY date ASC",
                $days
            )
        );
    }

    /**
     * Get clicks by country
     */
    public function get_clicks_by_country() {
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'ad_network_clicks';
        
        return $wpdb->get_results(
            "SELECT country, COUNT(*) as clicks 
            FROM $clicks_table 
            WHERE country != '' 
            GROUP BY country 
            ORDER BY clicks DESC 
            LIMIT 10"
        );
    }

    /**
     * Get clicks by device type
     */
    public function get_clicks_by_device() {
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'ad_network_clicks';
        
        return $wpdb->get_results(
            "SELECT device_type, COUNT(*) as clicks 
            FROM $clicks_table 
            WHERE device_type IS NOT NULL 
            GROUP BY device_type"
        );
    }

    /**
     * Get clicks by browser
     */
    public function get_clicks_by_browser() {
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'ad_network_clicks';
        
        return $wpdb->get_results(
            "SELECT browser, COUNT(*) as clicks 
            FROM $clicks_table 
            WHERE browser != 'Unknown' 
            GROUP BY browser 
            ORDER BY clicks DESC 
            LIMIT 5"
        );
    }

    /**
     * Get top performing links
     */
    public function get_top_links($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ad_network_links';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY clicks DESC LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Get recent clicks
     */
    public function get_recent_clicks($limit = 10) {
        global $wpdb;
        $clicks_table = $wpdb->prefix . 'ad_network_clicks';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, l.name as link_name 
                FROM $clicks_table c
                LEFT JOIN {$wpdb->prefix}ad_network_links l ON c.link_id = l.link_id
                ORDER BY c.clicked_at DESC 
                LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Track detailed click with location and device info
     */
    public function track_click($link_id, $site_url, $redirect_url) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        $table_sites = $wpdb->prefix . 'ad_network_sites';

        // Get user information
        $user_ip = $this->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        // Parse device info
        $device_info = $this->parse_user_agent($user_agent);
        
        // Get location data (only if enabled)
        $location_data = array(
            'country' => null,
            'city' => null,
            'region' => null
        );
        
        if (get_option('adnl_track_location', 1)) {
            $location_data = $this->get_location_from_ip($user_ip);
        }

        // Get site name
        $site_name = $this->get_site_name($site_url);

        // Insert click record
        $wpdb->insert(
            $table_clicks,
            array(
                'link_id' => $link_id,
                'site_url' => $site_url,
                'site_name' => $site_name,
                'user_ip' => $user_ip,
                'country' => $location_data['country'],
                'city' => $location_data['city'],
                'region' => $location_data['region'],
                'user_agent' => $user_agent,
                'device_type' => $device_info['device'],
                'browser' => $device_info['browser'],
                'os' => $device_info['os'],
                'referrer' => $referrer
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        // Update or insert site statistics
        $existing_site = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_sites WHERE site_url = %s", $site_url)
        );

        if ($existing_site) {
            $wpdb->update(
                $table_sites,
                array(
                    'total_clicks' => $existing_site->total_clicks + 1,
                    'last_click' => current_time('mysql'),
                    'site_name' => $site_name
                ),
                array('site_url' => $site_url),
                array('%d', '%s', '%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $table_sites,
                array(
                    'site_url' => $site_url,
                    'site_name' => $site_name,
                    'total_clicks' => 1,
                    'last_click' => current_time('mysql')
                ),
                array('%s', '%s', '%d', '%s')
            );
        }
    }

    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    }

    /**
     * Parse user agent for device, browser, and OS info
     */
    private function parse_user_agent($user_agent) {
        $device = 'Desktop';
        $browser = 'Unknown';
        $os = 'Unknown';

        // Device detection
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile/i', $user_agent)) {
            if (preg_match('/ipad|tablet/i', $user_agent)) {
                $device = 'Tablet';
            } else {
                $device = 'Mobile';
            }
        }

        // Browser detection
        if (preg_match('/MSIE|Trident/i', $user_agent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edge/i', $user_agent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
            $browser = 'Opera';
        }

        // OS detection
        if (preg_match('/windows|win32/i', $user_agent)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $os = 'Mac OS';
        } elseif (preg_match('/linux/i', $user_agent)) {
            $os = 'Linux';
        } elseif (preg_match('/android/i', $user_agent)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) {
            $os = 'iOS';
        }

        return array(
            'device' => $device,
            'browser' => $browser,
            'os' => $os
        );
    }

    /**
     * Get location from IP using free API
     */
    private function get_location_from_ip($ip) {
        // Skip for local/private IPs
        if ($ip === 'Unknown' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return array('country' => null, 'city' => null, 'region' => null);
        }

        // Use transient to cache location data for 24 hours
        $transient_key = 'adnl_location_' . md5($ip);
        $cached_location = get_transient($transient_key);
        
        if ($cached_location !== false) {
            return $cached_location;
        }

        // Try to get location from ip-api.com (free, no key required)
        $api_url = "http://ip-api.com/json/{$ip}";
        $response = wp_remote_get($api_url, array('timeout' => 3));

        if (is_wp_error($response)) {
            return array('country' => null, 'city' => null, 'region' => null);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $location = array(
            'country' => isset($data['country']) ? $data['country'] : null,
            'city' => isset($data['city']) ? $data['city'] : null,
            'region' => isset($data['regionName']) ? $data['regionName'] : null
        );

        // Cache for 24 hours
        set_transient($transient_key, $location, DAY_IN_SECONDS);

        return $location;
    }

    /**
     * Get site name from URL
     */
    private function get_site_name($site_url) {
        $transient_key = 'adnl_sitename_' . md5($site_url);
        $cached_name = get_transient($transient_key);
        
        if ($cached_name !== false) {
            return $cached_name;
        }

        // Try to fetch the site title
        $response = wp_remote_get($site_url, array('timeout' => 5));
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            if (preg_match('/<title>(.+?)<\/title>/i', $body, $matches)) {
                $site_name = trim($matches[1]);
                set_transient($transient_key, $site_name, WEEK_IN_SECONDS);
                return $site_name;
            }
        }

        // Fallback to domain name
        $parsed = parse_url($site_url);
        $site_name = isset($parsed['host']) ? $parsed['host'] : 'Unknown Site';
        set_transient($transient_key, $site_name, WEEK_IN_SECONDS);
        
        return $site_name;
    }

    /**
     * Get analytics data for dashboard
     */
    public function get_analytics_data($days = 30) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $data = array(
            'total_clicks' => $wpdb->get_var("SELECT COUNT(*) FROM $table_clicks WHERE clicked_at >= '$date_from'"),
            'unique_sites' => $wpdb->get_var("SELECT COUNT(DISTINCT site_url) FROM $table_clicks WHERE clicked_at >= '$date_from'"),
            'top_countries' => $wpdb->get_results("SELECT country, COUNT(*) as clicks FROM $table_clicks WHERE clicked_at >= '$date_from' AND country IS NOT NULL GROUP BY country ORDER BY clicks DESC LIMIT 10", ARRAY_A),
            'top_sites' => $wpdb->get_results("SELECT site_url, site_name, COUNT(*) as clicks FROM $table_clicks WHERE clicked_at >= '$date_from' GROUP BY site_url ORDER BY clicks DESC LIMIT 10", ARRAY_A),
            'device_breakdown' => $wpdb->get_results("SELECT device_type, COUNT(*) as clicks FROM $table_clicks WHERE clicked_at >= '$date_from' GROUP BY device_type ORDER BY clicks DESC", ARRAY_A),
            'browser_breakdown' => $wpdb->get_results("SELECT browser, COUNT(*) as clicks FROM $table_clicks WHERE clicked_at >= '$date_from' GROUP BY browser ORDER BY clicks DESC LIMIT 5", ARRAY_A),
            'clicks_by_day' => $wpdb->get_results("SELECT DATE(clicked_at) as date, COUNT(*) as clicks FROM $table_clicks WHERE clicked_at >= '$date_from' GROUP BY DATE(clicked_at) ORDER BY date ASC", ARRAY_A)
        );

        return $data;
    }

    /**
     * Get all tracked sites
     */
    public function get_all_sites() {
        global $wpdb;
        $table_sites = $wpdb->prefix . 'ad_network_sites';
        
        return $wpdb->get_results("SELECT * FROM $table_sites ORDER BY total_clicks DESC");
    }

    /**
     * Get clicks for a specific link
     */
    public function get_link_clicks($link_id, $limit = 100) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_clicks WHERE link_id = %s ORDER BY clicked_at DESC LIMIT %d", $link_id, $limit)
        );
    }

    /**
     * Get shareable shortcode
     */
    public function get_shareable_shortcode($link_id, $custom_redirect = '') {
        $shortcode = '[ad_network_link id="' . esc_attr($link_id) . '"';
        if (!empty($custom_redirect)) {
            $shortcode .= ' redirect="' . esc_url($custom_redirect) . '"';
        }
        $shortcode .= ']';
        
        return $shortcode;
    }
    
    /**
     * Track site usage
     */
    public function track_site_usage($link_id, $site_url, $site_name = '') {
        global $wpdb;
        $table_sites = $wpdb->prefix . 'ad_network_sites';
        
        // Check if site exists
        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_sites WHERE site_url = %s", $site_url)
        );
        
        if ($existing) {
            // Update last seen
            $wpdb->update(
                $table_sites,
                array('last_seen' => current_time('mysql')),
                array('site_url' => $site_url),
                array('%s'),
                array('%s')
            );
        } else {
            // Insert new site
            $wpdb->insert(
                $table_sites,
                array(
                    'site_url' => $site_url,
                    'site_name' => $site_name
                ),
                array('%s', '%s')
            );
        }
    }
    
    /**
     * Log click with detailed tracking
     */
    public function log_click($link_id, $site_url = '', $site_name = '') {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        // Get user information
        $user_ip = $this->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        // Parse user agent for device info
        $device_info = $this->parse_user_agent($user_agent);
        
        // Insert click record
        $wpdb->insert(
            $table_clicks,
            array(
                'link_id' => $link_id,
                'site_url' => $site_url ? $site_url : site_url(),
                'site_name' => $site_name,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'device_type' => $device_info['device_type'],
                'browser' => $device_info['browser'],
                'os' => $device_info['os'],
                'referrer' => $referrer
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        // Update site clicks
        if ($site_url) {
            $table_sites = $wpdb->prefix . 'ad_network_sites';
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_sites SET total_clicks = total_clicks + 1, last_click = %s WHERE site_url = %s",
                    current_time('mysql'),
                    $site_url
                )
            );
        }
    }
    
    /**
     * Get all tracked sites
     */
    public function get_all_sites() {
        global $wpdb;
        $table_sites = $wpdb->prefix . 'ad_network_sites';
        
        $results = $wpdb->get_results("SELECT * FROM $table_sites ORDER BY total_clicks DESC");
        
        return $results;
    }
    
    /**
     * Get site statistics for a specific link
     */
    public function get_link_site_stats($link_id) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    site_url,
                    site_name,
                    COUNT(*) as click_count,
                    MAX(clicked_at) as last_click
                FROM $table_clicks 
                WHERE link_id = %s 
                GROUP BY site_url 
                ORDER BY click_count DESC",
                $link_id
            )
        );
        
        return $results;
    }
    
    /**
     * Get device statistics
     */
    public function get_device_stats($link_id = null) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        if ($link_id) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT device_type, COUNT(*) as count 
                    FROM $table_clicks 
                    WHERE link_id = %s AND device_type IS NOT NULL
                    GROUP BY device_type",
                    $link_id
                )
            );
        } else {
            $results = $wpdb->get_results(
                "SELECT device_type, COUNT(*) as count 
                FROM $table_clicks 
                WHERE device_type IS NOT NULL
                GROUP BY device_type"
            );
        }
        
        return $results;
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    /**
     * Parse user agent for device information
     */
    private function parse_user_agent($user_agent) {
        $device_type = 'Desktop';
        $browser = 'Unknown';
        $os = 'Unknown';
        
        // Detect device type
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $user_agent)) {
            if (preg_match('/ipad|tablet|playbook/i', $user_agent)) {
                $device_type = 'Tablet';
            } else {
                $device_type = 'Mobile';
            }
        }
        
        // Detect browser
        if (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        }
        
        // Detect OS
        if (strpos($user_agent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($user_agent, 'Mac') !== false) {
            $os = 'Mac OS';
        } elseif (strpos($user_agent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($user_agent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($user_agent, 'iOS') !== false || strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false) {
            $os = 'iOS';
        }
        
        return array(
            'device_type' => $device_type,
            'browser' => $browser,
            'os' => $os
        );
    }
    
    /**
     * Get click statistics for last X days
     */
    public function get_click_stats($days = 30) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(clicked_at) as date, COUNT(*) as count 
                FROM $table_clicks 
                WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY DATE(clicked_at)
                ORDER BY date ASC",
                $days
            )
        );
        
        return $results;
    }
    
    /**
     * Get clicks by country
     */
    public function get_clicks_by_country() {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        $results = $wpdb->get_results(
            "SELECT country, COUNT(*) as count 
            FROM $table_clicks 
            WHERE country IS NOT NULL AND country != ''
            GROUP BY country 
            ORDER BY count DESC 
            LIMIT 10"
        );
        
        return $results;
    }
    
    /**
     * Get clicks by device type
     */
    public function get_clicks_by_device() {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        $results = $wpdb->get_results(
            "SELECT device_type, COUNT(*) as count 
            FROM $table_clicks 
            WHERE device_type IS NOT NULL
            GROUP BY device_type"
        );
        
        return $results;
    }
    
    /**
     * Get clicks by browser
     */
    public function get_clicks_by_browser() {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        
        $results = $wpdb->get_results(
            "SELECT browser, COUNT(*) as count 
            FROM $table_clicks 
            WHERE browser IS NOT NULL AND browser != 'Unknown'
            GROUP BY browser 
            ORDER BY count DESC 
            LIMIT 10"
        );
        
        return $results;
    }
    
    /**
     * Get top performing links
     */
    public function get_top_links($limit = 5) {
        global $wpdb;
        $table_links = $wpdb->prefix . 'ad_network_links';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_links ORDER BY clicks DESC LIMIT %d",
                $limit
            )
        );
        
        return $results;
    }
    
    /**
     * Get recent clicks
     */
    public function get_recent_clicks($limit = 20) {
        global $wpdb;
        $table_clicks = $wpdb->prefix . 'ad_network_clicks';
        $table_links = $wpdb->prefix . 'ad_network_links';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, l.name as link_name 
                FROM $table_clicks c
                LEFT JOIN $table_links l ON c.link_id = l.link_id
                ORDER BY c.clicked_at DESC 
                LIMIT %d",
                $limit
            )
        );
        
        return $results;
    }
}
