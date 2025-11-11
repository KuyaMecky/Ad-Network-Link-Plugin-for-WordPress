<?php
/**
 * Template for adding new ad link
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Add New Ad Link</h1>

    <?php if (isset($_GET['message']) && $_GET['message'] == 'error'): ?>
        <div class="notice notice-error is-dismissible">
            <p>Error creating ad link. Please try again.</p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="adnl-form">
        <?php wp_nonce_field('adnl_create_link'); ?>
        <input type="hidden" name="action" value="adnl_create_link">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="link_name">Link Name <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" name="link_name" id="link_name" class="regular-text" required>
                    <p class="description">A descriptive name for this ad link (for internal use only)</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="image_url">Image URL <span class="required">*</span></label>
                </th>
                <td>
                    <input type="url" name="image_url" id="image_url" class="regular-text" required>
                    <button type="button" class="button" id="adnl_upload_image">Upload Image</button>
                    <p class="description">Select or upload an image (PNG, GIF, AVIF, JPG supported)</p>
                    <div id="adnl_image_preview" style="margin-top: 10px;"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="redirect_url">Default Redirect URL <span class="required">*</span></label>
                </th>
                <td>
                    <input type="url" name="redirect_url" id="redirect_url" class="regular-text" required>
                    <p class="description">Where should this link redirect to by default? (Can be overridden per site)</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="is_main_site">Main Site</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="is_main_site" id="is_main_site" value="1">
                        This is the main site (source of the ad link)
                    </label>
                    <p class="description">Check this if you're creating the ad link on the main/source site</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Create Ad Link">
            <a href="<?php echo admin_url('admin.php?page=ad-network-links'); ?>" class="button">Cancel</a>
        </p>
    </form>

    <div class="adnl-info-box">
        <h3>How It Works</h3>
        <ol>
            <li><strong>Main Site:</strong> Create the ad link here and get the shortcode</li>
            <li><strong>Partner Sites:</strong> Install this plugin and use the same shortcode</li>
            <li><strong>Custom Redirect:</strong> Partner sites can specify where the link should redirect:
                <code>[ad_network_link id="adnl_xxxxx" redirect="https://partner-site.com"]</code>
            </li>
            <li><strong>Track Clicks:</strong> All clicks are tracked in the database</li>
        </ol>
    </div>
</div>
