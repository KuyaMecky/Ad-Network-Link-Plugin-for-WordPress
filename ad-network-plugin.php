<?php
/**
 * Plugin Name: Ad Network Link Plugin
 * Plugin URI: https://github.com/KuyaMecky
 * Description: Create shareable ad links with images that redirect to specified URLs. Perfect for ad networks and affiliate sites.
 * Version: 1.0.0
 * Author: Your Mecky
 * Author URI: https://github.com/KuyaMecky
 * License: GPL v2 or later
 * Text Domain: ad-network-link
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ADNL_VERSION', '1.0.0');
define('ADNL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ADNL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ADNL_PLUGIN_DIR . 'includes/class-ad-network-core.php';
require_once ADNL_PLUGIN_DIR . 'includes/class-ad-network-admin.php';
require_once ADNL_PLUGIN_DIR . 'includes/class-ad-network-frontend.php';
require_once ADNL_PLUGIN_DIR . 'includes/class-ad-network-shortcode.php';

// Activation hook
register_activation_hook(__FILE__, 'adnl_activate');
function adnl_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Main links table
    $table_links = $wpdb->prefix . 'ad_network_links';
    $sql_links = "CREATE TABLE $table_links (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        link_id varchar(50) NOT NULL,
        name varchar(255) NOT NULL,
        image_url varchar(500) NOT NULL,
        redirect_url varchar(500) NOT NULL,
        is_main_site tinyint(1) DEFAULT 0,
        clicks int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY link_id (link_id)
    ) $charset_collate;";

    // Click tracking table
    $table_clicks = $wpdb->prefix . 'ad_network_clicks';
    $sql_clicks = "CREATE TABLE $table_clicks (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        link_id varchar(50) NOT NULL,
        site_url varchar(500) NOT NULL,
        site_name varchar(255) DEFAULT NULL,
        user_ip varchar(45) DEFAULT NULL,
        country varchar(100) DEFAULT NULL,
        city varchar(100) DEFAULT NULL,
        region varchar(100) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        device_type varchar(50) DEFAULT NULL,
        browser varchar(100) DEFAULT NULL,
        os varchar(100) DEFAULT NULL,
        referrer varchar(500) DEFAULT NULL,
        clicked_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY link_id (link_id),
        KEY site_url (site_url(191)),
        KEY clicked_at (clicked_at),
        KEY country (country)
    ) $charset_collate;";

    // Site statistics table
    $table_sites = $wpdb->prefix . 'ad_network_sites';
    $sql_sites = "CREATE TABLE $table_sites (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        site_url varchar(500) NOT NULL,
        site_name varchar(255) DEFAULT NULL,
        total_clicks int(11) DEFAULT 0,
        last_click datetime DEFAULT NULL,
        first_seen datetime DEFAULT CURRENT_TIMESTAMP,
        last_seen datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY site_url (site_url(191))
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_links);
    dbDelta($sql_clicks);
    dbDelta($sql_sites);

    // Add default options
    add_option('adnl_main_site_url', site_url());
    add_option('adnl_default_redirect', site_url());
    add_option('adnl_enable_tracking', 1);
    add_option('adnl_track_location', 1);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'adnl_deactivate');
function adnl_deactivate() {
    // Cleanup if needed
}

// Initialize the plugin
function adnl_init() {
    $core = new ADNL_Core();
    $admin = new ADNL_Admin();
    $frontend = new ADNL_Frontend();
    $shortcode = new ADNL_Shortcode();
}
add_action('plugins_loaded', 'adnl_init');
