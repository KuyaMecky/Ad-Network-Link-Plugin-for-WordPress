<?php
/**
 * Shortcode functionality for Ad Network Link Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ADNL_Shortcode {
    
    private $core;

    public function __construct() {
        $this->core = new ADNL_Core();
        
        add_shortcode('ad_network_link', array($this, 'render_shortcode'));
    }

    /**
     * Render the shortcode
     * 
     * Usage: [ad_network_link id="adnl_xxxxx" redirect="https://custom-site.com" width="300px" height="250px" alt="Ad text" class="my-class" title="Ad title"]
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'redirect' => '',
            'width' => '100%',
            'height' => 'auto',
            'class' => '',
            'alt' => '',
            'title' => '',
            'style' => '',
            'target' => '_blank',
            'rel' => 'noopener'
        ), $atts);

        if (empty($atts['id'])) {
            return '<p>Ad Network Link: No ID specified</p>';
        }

        $ad_link = $this->core->get_ad_link($atts['id']);

        if (!$ad_link) {
            return '<p>Ad Network Link: Link not found</p>';
        }
        
        // Track site usage (if enabled)
        if (get_option('adnl_enable_tracking', 1)) {
            $site_url = site_url();
            $site_name = get_bloginfo('name');
            $this->core->track_site_usage($ad_link->link_id, $site_url, $site_name);
        }

        // Build click URL
        $click_url = add_query_arg(array(
            'adnl_click' => $ad_link->link_id,
            'site' => urlencode(home_url()),
            'site_name' => urlencode(get_bloginfo('name'))
        ), home_url());

        // Add custom redirect if specified
        if (!empty($atts['redirect'])) {
            $click_url = add_query_arg('redirect', urlencode($atts['redirect']), $click_url);
        }
        
        // Determine alt text
        $alt_text = !empty($atts['alt']) ? $atts['alt'] : $ad_link->name;
        
        // Determine title
        $title_text = !empty($atts['title']) ? $atts['title'] : $ad_link->name;
        
        // Build inline styles
        $inline_styles = array();
        $inline_styles[] = 'width:' . esc_attr($atts['width']);
        $inline_styles[] = 'height:' . esc_attr($atts['height']);
        
        if (!empty($atts['style'])) {
            $inline_styles[] = esc_attr($atts['style']);
        }
        
        $style_attr = implode(';', $inline_styles);

        // Build HTML output
        $output = '<div class="adnl-wrapper ' . esc_attr($atts['class']) . '">';
        $output .= '<a href="' . esc_url($click_url) . '" class="adnl-link" target="' . esc_attr($atts['target']) . '" rel="' . esc_attr($atts['rel']) . '" title="' . esc_attr($title_text) . '">';
        $output .= '<img src="' . esc_url($ad_link->image_url) . '" alt="' . esc_attr($alt_text) . '" class="adnl-image" style="' . $style_attr . '" loading="lazy" />';
        $output .= '</a>';
        $output .= '</div>';

        return $output;
    }
}
