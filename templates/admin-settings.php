<?php
/**
 * Template for plugin settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$main_site_url = get_option('adnl_main_site_url', site_url());
$default_redirect = get_option('adnl_default_redirect', site_url());
?>

<div class="wrap">
    <h1>Ad Network Settings</h1>

    <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p>Settings saved successfully!</p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('adnl_save_settings'); ?>
        <input type="hidden" name="action" value="adnl_save_settings">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="main_site_url">Main Site URL</label>
                </th>
                <td>
                    <input type="url" name="main_site_url" id="main_site_url" class="regular-text" value="<?php echo esc_url($main_site_url); ?>" required>
                    <p class="description">The URL of your main ad network site (where ad links are created)</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="default_redirect">Default Redirect URL</label>
                </th>
                <td>
                    <input type="url" name="default_redirect" id="default_redirect" class="regular-text" value="<?php echo esc_url($default_redirect); ?>" required>
                    <p class="description">Default URL to redirect to if no custom redirect is specified</p>
                </td>
            </tr>
        </table>

        <h2>How to Use This Plugin</h2>
        <div class="adnl-info-box">
            <h3>For Main Site (Ad Creator)</h3>
            <ol>
                <li>Go to <strong>Ad Network → Add New</strong></li>
                <li>Upload your image (PNG, GIF, AVIF)</li>
                <li>Set the default redirect URL</li>
                <li>Check "This is the main site"</li>
                <li>Copy the generated shortcode</li>
                <li>Share this shortcode with partner sites</li>
            </ol>

            <h3>For Partner Sites (Ad Displayers)</h3>
            <ol>
                <li>Install this plugin on your partner site</li>
                <li>Use the shortcode provided by the main site</li>
                <li>Optionally, add a custom redirect URL:
                    <br><code>[ad_network_link id="adnl_xxxxx" redirect="https://your-site.com/landing-page"]</code>
                </li>
                <li>The ad will display the image from the main site</li>
                <li>When clicked, it will redirect to your specified URL (or the default)</li>
            </ol>

            <h3>Shortcode Parameters</h3>
            <ul>
                <li><code>id</code> - Required. The unique ad link ID</li>
                <li><code>redirect</code> - Optional. Custom redirect URL for this specific display</li>
                <li><code>width</code> - Optional. Image width (default: 100%)</li>
                <li><code>height</code> - Optional. Image height (default: auto)</li>
                <li><code>class</code> - Optional. Custom CSS class</li>
            </ul>

            <h3>Example Usage</h3>
            <pre>[ad_network_link id="adnl_12345" redirect="https://partner-site.com" width="300px"]</pre>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
        </p>
    </form>
</div>
