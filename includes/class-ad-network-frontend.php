<?php
/**
 * Frontend functionality for Ad Network Link Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ADNL_Frontend {
    
    private $core;

    public function __construct() {
        $this->core = new ADNL_Core();
        
        add_action('template_redirect', array($this, 'handle_redirect'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('adnl-frontend-style', ADNL_PLUGIN_URL . 'assets/css/frontend-style.css', array(), ADNL_VERSION);
    }

    /**
     * Handle click redirects
     */
    public function handle_redirect() {
        if (isset($_GET['adnl_click']) && !empty($_GET['adnl_click'])) {
            $link_id = sanitize_text_field($_GET['adnl_click']);
            $custom_redirect = isset($_GET['redirect']) ? esc_url_raw($_GET['redirect']) : '';
            $source_site = isset($_GET['site']) ? esc_url_raw($_GET['site']) : '';
            $source_name = isset($_GET['site_name']) ? sanitize_text_field(urldecode($_GET['site_name'])) : '';
            
            $ad_link = $this->core->get_ad_link($link_id);
            
            if ($ad_link) {
                // Increment click count
                $this->core->increment_clicks($link_id);
                
                // Log detailed click tracking
                if (get_option('adnl_enable_tracking', 1)) {
                    $this->core->log_click($link_id, $source_site, $source_name);
                }
                
                // Determine redirect URL
                $redirect_url = !empty($custom_redirect) ? $custom_redirect : $ad_link->redirect_url;
                
                // Redirect
                wp_redirect($redirect_url);
                exit;
            } else {
                // Link not found, redirect to home
                wp_redirect(home_url());
                exit;
            }
        }
    }
}
