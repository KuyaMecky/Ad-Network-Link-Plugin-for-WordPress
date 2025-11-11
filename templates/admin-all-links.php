<?php
/**
 * Template for displaying all ad links
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Ad Network Links</h1>
    <a href="<?php echo admin_url('admin.php?page=ad-network-add-new'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">

    <?php if (isset($_GET['message'])): ?>
        <?php if ($_GET['message'] == 'created'): ?>
            <div class="notice notice-success is-dismissible">
                <p>Ad link created successfully!</p>
            </div>
        <?php elseif ($_GET['message'] == 'deleted'): ?>
            <div class="notice notice-success is-dismissible">
                <p>Ad link deleted successfully!</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($links)): ?>
        <div class="adnl-empty-state">
            <p>No ad links found. <a href="<?php echo admin_url('admin.php?page=ad-network-add-new'); ?>">Create your first ad link</a></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 60px;">Preview</th>
                    <th>Name</th>
                    <th>Link ID</th>
                    <th>Redirect URL</th>
                    <th>Clicks</th>
                    <th>Shortcode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                    <tr>
                        <td>
                            <img src="<?php echo esc_url($link->image_url); ?>" alt="<?php echo esc_attr($link->name); ?>" style="max-width: 60px; height: auto;">
                        </td>
                        <td>
                            <strong><?php echo esc_html($link->name); ?></strong>
                            <?php if ($link->is_main_site): ?>
                                <span class="adnl-badge">Main Site</span>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo esc_html($link->link_id); ?></code></td>
                        <td>
                            <a href="<?php echo esc_url($link->redirect_url); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html(substr($link->redirect_url, 0, 50)) . (strlen($link->redirect_url) > 50 ? '...' : ''); ?>
                            </a>
                        </td>
                        <td><?php echo number_format($link->clicks); ?></td>
                        <td>
                            <input type="text" readonly value='[ad_network_link id="<?php echo esc_attr($link->link_id); ?>"]' class="adnl-shortcode-input" onclick="this.select();" style="width: 100%;">
                            <button type="button" class="button button-small adnl-copy-shortcode" data-shortcode='[ad_network_link id="<?php echo esc_attr($link->link_id); ?>"]'>Copy</button>
                        </td>
                        <td>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=adnl_delete_link&link_id=' . $link->link_id), 'adnl_delete_link_' . $link->link_id); ?>" class="button button-small" onclick="return confirm('Are you sure you want to delete this link?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
