<?php
/**
 * Admin functionality for Ad Network Link Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ADNL_Admin {
    
    private $core;

    public function __construct() {
        $this->core = new ADNL_Core();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_adnl_create_link', array($this, 'handle_create_link'));
        add_action('admin_post_adnl_delete_link', array($this, 'handle_delete_link'));
        add_action('admin_post_adnl_save_settings', array($this, 'handle_save_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Ad Network Dashboard',
            'Ad Network',
            'manage_options',
            'ad-network-dashboard',
            array($this, 'display_dashboard_page'),
            'dashicons-share',
            30
        );

        add_submenu_page(
            'ad-network-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'ad-network-dashboard',
            array($this, 'display_dashboard_page')
        );

        add_submenu_page(
            'ad-network-dashboard',
            'Analytics',
            'Analytics',
            'manage_options',
            'ad-network-analytics',
            array($this, 'display_analytics_page')
        );

        add_submenu_page(
            'ad-network-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'ad-network-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ad-network') !== false) {
            wp_enqueue_media();
            wp_enqueue_style('adnl-admin-style', ADNL_PLUGIN_URL . 'assets/css/admin-style.css', array(), ADNL_VERSION);
            wp_enqueue_script('adnl-admin-script', ADNL_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), ADNL_VERSION, true);
            
            // Enqueue Chart.js for analytics page
            if ($hook === 'ad-network_page_ad-network-analytics') {
                wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
                wp_enqueue_script('adnl-analytics-script', ADNL_PLUGIN_URL . 'assets/js/analytics-script.js', array('jquery', 'chartjs'), ADNL_VERSION, true);
            }
        }
    }

    /**
     * Display dashboard page with all link management
     */
    public function display_dashboard_page() {
        $links = $this->core->get_all_ad_links();
        include ADNL_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    /**
     * Display analytics page
     */
    public function display_analytics_page() {
        $sites = $this->core->get_all_sites();
        $click_stats = $this->core->get_click_stats(30);
        $clicks_by_country = $this->core->get_clicks_by_country();
        $clicks_by_device = $this->core->get_clicks_by_device();
        $clicks_by_browser = $this->core->get_clicks_by_browser();
        $top_links = $this->core->get_top_links(5);
        $recent_clicks = $this->core->get_recent_clicks(20);
        include ADNL_PLUGIN_DIR . 'templates/admin-analytics.php';
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        include ADNL_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Handle create link form submission
     */
    public function handle_create_link() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('adnl_create_link');

        $name = sanitize_text_field($_POST['link_name']);
        $image_url = esc_url_raw($_POST['image_url']);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        $is_main_site = isset($_POST['is_main_site']) ? 1 : 0;

        $link_id = $this->core->create_ad_link($name, $image_url, $redirect_url, $is_main_site);

        if ($link_id) {
            wp_redirect(admin_url('admin.php?page=ad-network-dashboard&message=created'));
        } else {
            wp_redirect(admin_url('admin.php?page=ad-network-dashboard&message=error'));
        }
        exit;
    }

    /**
     * Handle delete link
     */
    public function handle_delete_link() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('adnl_delete_link_' . $_GET['link_id']);

        $link_id = sanitize_text_field($_GET['link_id']);
        $this->core->delete_ad_link($link_id);

        wp_redirect(admin_url('admin.php?page=ad-network-dashboard&message=deleted'));
        exit;
    }

    /**
     * Handle save settings
     */
    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('adnl_save_settings');

        update_option('adnl_main_site_url', esc_url_raw($_POST['main_site_url']));
        update_option('adnl_default_redirect', esc_url_raw($_POST['default_redirect']));

        wp_redirect(admin_url('admin.php?page=ad-network-settings&message=saved'));
        exit;
    }
}
