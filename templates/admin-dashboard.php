<?php
/**
 * Template for Ad Network Dashboard
 * All link management in one place
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Ad Network Dashboard</h1>
    <hr class="wp-header-end">

    <?php if (isset($_GET['message'])): ?>
        <?php if ($_GET['message'] == 'created'): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Success!</strong> Ad link created successfully!</p>
            </div>
        <?php elseif ($_GET['message'] == 'deleted'): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Success!</strong> Ad link deleted successfully!</p>
            </div>
        <?php elseif ($_GET['message'] == 'error'): ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Error!</strong> Failed to create ad link. Please try again.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="adnl-stats-grid">
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">📊</div>
            <div class="adnl-stat-content">
                <h3><?php echo count($links); ?></h3>
                <p>Total Ad Links</p>
            </div>
        </div>
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">👆</div>
            <div class="adnl-stat-content">
                <h3><?php 
                    $total_clicks = 0;
                    foreach ($links as $link) {
                        $total_clicks += $link->clicks;
                    }
                    echo number_format($total_clicks);
                ?></h3>
                <p>Total Clicks</p>
            </div>
        </div>
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">🎯</div>
            <div class="adnl-stat-content">
                <h3><?php 
                    $main_site_links = 0;
                    foreach ($links as $link) {
                        if ($link->is_main_site) {
                            $main_site_links++;
                        }
                    }
                    echo $main_site_links;
                ?></h3>
                <p>Main Site Links</p>
            </div>
        </div>
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">📈</div>
            <div class="adnl-stat-content">
                <h3><?php 
                    if (count($links) > 0) {
                        echo number_format($total_clicks / count($links), 1);
                    } else {
                        echo '0';
                    }
                ?></h3>
                <p>Avg. Clicks per Link</p>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="adnl-dashboard-grid">
        
        <!-- Create New Ad Link Section -->
        <div class="adnl-dashboard-section adnl-create-section">
            <h2>Create New Ad Link</h2>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="adnl-form" id="adnl-create-form">
                <?php wp_nonce_field('adnl_create_link'); ?>
                <input type="hidden" name="action" value="adnl_create_link">

                <div class="adnl-form-group">
                    <label for="link_name">Link Name <span class="required">*</span></label>
                    <input type="text" name="link_name" id="link_name" class="adnl-input" placeholder="e.g., Summer Sale Banner" required>
                    <small>A descriptive name for internal use</small>
                </div>

                <div class="adnl-form-group">
                    <label for="image_url">Image URL <span class="required">*</span></label>
                    <div class="adnl-image-upload-wrapper">
                        <input type="url" name="image_url" id="image_url" class="adnl-input" placeholder="https://example.com/image.png" required>
                        <button type="button" class="button button-secondary" id="adnl_upload_image">
                            📁 Upload Image
                        </button>
                    </div>
                    <small>Upload or paste image URL (PNG, GIF, AVIF, JPG, WebP)</small>
                    <div id="adnl_image_preview" class="adnl-image-preview"></div>
                </div>

                <div class="adnl-form-group">
                    <label for="redirect_url">Default Redirect URL <span class="required">*</span></label>
                    <input type="url" name="redirect_url" id="redirect_url" class="adnl-input" placeholder="https://example.com/landing-page" required>
                    <small>Where should this ad redirect by default?</small>
                </div>

                <div class="adnl-form-group">
                    <label class="adnl-checkbox-label">
                        <input type="checkbox" name="is_main_site" id="is_main_site" value="1">
                        <span>This is the main site (source of the ad link)</span>
                    </label>
                    <small>Check this if you're creating the ad on the primary network site</small>
                </div>

                <div class="adnl-form-actions">
                    <button type="submit" class="button button-primary button-large">
                        ✨ Create Ad Link
                    </button>
                    <button type="reset" class="button button-secondary">
                        🔄 Reset Form
                    </button>
                </div>
            </form>

            <!-- Quick Guide -->
            <div class="adnl-quick-guide">
                <h4>Quick Guide</h4>
                <ol>
                    <li>Create your ad link here on the main site</li>
                    <li>Copy the shortcode from the list below</li>
                    <li>Share it with partner sites</li>
                    <li>Partners can customize the redirect URL</li>
                </ol>
            </div>
        </div>

        <!-- All Ad Links Section -->
        <div class="adnl-dashboard-section adnl-links-section">
            <h2>All Ad Links</h2>

            <?php if (empty($links)): ?>
                <div class="adnl-empty-state">
                    <div class="adnl-empty-icon">📭</div>
                    <h3>No Ad Links Yet</h3>
                    <p>Create your first ad link using the form on the left to get started!</p>
                </div>
            <?php else: ?>
                
                <!-- Search and Filter -->
                <div class="adnl-toolbar">
                    <input type="text" id="adnl-search" class="adnl-search-input" placeholder="🔍 Search ad links...">
                    <select id="adnl-filter" class="adnl-filter-select">
                        <option value="all">All Links</option>
                        <option value="main">Main Site Only</option>
                        <option value="partner">Partner Links</option>
                    </select>
                </div>

                <!-- Links Grid -->
                <div class="adnl-links-grid" id="adnl-links-container">
                    <?php foreach ($links as $link): ?>
                        <div class="adnl-link-card" data-name="<?php echo esc_attr(strtolower($link->name)); ?>" data-type="<?php echo $link->is_main_site ? 'main' : 'partner'; ?>">
                            
                            <!-- Card Header -->
                            <div class="adnl-card-header">
                                <h3><?php echo esc_html($link->name); ?></h3>
                                <?php if ($link->is_main_site): ?>
                                    <span class="adnl-badge adnl-badge-primary">Main Site</span>
                                <?php endif; ?>
                            </div>

                            <!-- Image Preview -->
                            <div class="adnl-card-image">
                                <img src="<?php echo esc_url($link->image_url); ?>" alt="<?php echo esc_attr($link->name); ?>">
                            </div>

                            <!-- Card Body -->
                            <div class="adnl-card-body">
                                <div class="adnl-card-info">
                                    <div class="adnl-info-item">
                                        <span class="adnl-info-label">Link ID:</span>
                                        <code class="adnl-link-id"><?php echo esc_html($link->link_id); ?></code>
                                    </div>
                                    <div class="adnl-info-item">
                                        <span class="adnl-info-label">Redirect:</span>
                                        <a href="<?php echo esc_url($link->redirect_url); ?>" target="_blank" rel="noopener" class="adnl-redirect-link">
                                            <?php echo esc_html(substr($link->redirect_url, 0, 30)) . (strlen($link->redirect_url) > 30 ? '...' : ''); ?>
                                        </a>
                                    </div>
                                    <div class="adnl-info-item">
                                        <span class="adnl-info-label">Clicks:</span>
                                        <strong class="adnl-clicks"><?php echo number_format($link->clicks); ?></strong>
                                    </div>
                                    <div class="adnl-info-item">
                                        <span class="adnl-info-label">Created:</span>
                                        <span><?php echo date('M j, Y', strtotime($link->created_at)); ?></span>
                                    </div>
                                </div>

                                <!-- Shortcode Section -->
                                <div class="adnl-shortcode-section">
                                    <label class="adnl-shortcode-label">Basic Shortcode:</label>
                                    <div class="adnl-shortcode-wrapper">
                                        <input type="text" readonly value='[ad_network_link id="<?php echo esc_attr($link->link_id); ?>"]' class="adnl-shortcode-input adnl-basic-shortcode-<?php echo esc_attr($link->link_id); ?>" onclick="this.select();">
                                        <button type="button" class="button button-small adnl-copy-btn" data-shortcode='[ad_network_link id="<?php echo esc_attr($link->link_id); ?>"]' title="Copy shortcode">
                                            📋
                                        </button>
                                    </div>
                                    
                                    <!-- Advanced Shortcode Customization -->
                                    <details class="adnl-advanced-shortcode">
                                        <summary>⚙️ Customize Shortcode Attributes</summary>
                                        
                                        <div class="adnl-shortcode-attributes">
                                            <h4>Optional Parameters:</h4>
                                            <div class="adnl-attribute-grid">
                                                <div class="adnl-attribute-item">
                                                    <label>Redirect URL</label>
                                                    <input type="url" class="adnl-attr-redirect" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="https://your-site.com">
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>Width</label>
                                                    <input type="text" class="adnl-attr-width" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="100%">
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>Height</label>
                                                    <input type="text" class="adnl-attr-height" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="auto">
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>Alt Text</label>
                                                    <input type="text" class="adnl-attr-alt" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="Ad description">
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>Title</label>
                                                    <input type="text" class="adnl-attr-title" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="Ad title">
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>CSS Class</label>
                                                    <input type="text" class="adnl-attr-class" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="my-custom-class">
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>Target</label>
                                                    <select class="adnl-attr-target" data-link="<?php echo esc_attr($link->link_id); ?>">
                                                        <option value="">Default (_blank)</option>
                                                        <option value="_blank">_blank (New Tab)</option>
                                                        <option value="_self">_self (Same Tab)</option>
                                                    </select>
                                                </div>
                                                <div class="adnl-attribute-item">
                                                    <label>Custom Style</label>
                                                    <input type="text" class="adnl-attr-style" data-link="<?php echo esc_attr($link->link_id); ?>" placeholder="border: 1px solid #ccc">
                                                </div>
                                            </div>
                                            
                                            <div style="margin-top: 12px;">
                                                <span class="adnl-shortcode-preview-label">Generated Shortcode:</span>
                                                <div class="adnl-shortcode-preview adnl-custom-preview-<?php echo esc_attr($link->link_id); ?>">[ad_network_link id="<?php echo esc_attr($link->link_id); ?>"]</div>
                                                <button type="button" class="button button-small adnl-copy-custom-btn" data-link="<?php echo esc_attr($link->link_id); ?>" style="margin-top: 8px;">
                                                    📋 Copy Custom Shortcode
                                                </button>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="adnl-card-actions">
                                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=adnl_delete_link&link_id=' . $link->link_id), 'adnl_delete_link_' . $link->link_id); ?>" class="button button-small button-link-delete" onclick="return confirm('Are you sure you want to delete this ad link?\n\nThis action cannot be undone.');">
                                    🗑️ Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="adnl-no-results" class="adnl-empty-state" style="display: none;">
                    <div class="adnl-empty-icon">🔍</div>
                    <h3>No Results Found</h3>
                    <p>Try adjusting your search or filter.</p>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- Help Section -->
    <div class="adnl-help-section">
        <h3>📚 How to Use</h3>
        <div class="adnl-help-grid">
            <div class="adnl-help-item">
                <h4>1️⃣ Create Ad Link</h4>
                <p>Fill out the form above with your ad details and upload an image.</p>
            </div>
            <div class="adnl-help-item">
                <h4>2️⃣ Copy Shortcode</h4>
                <p>Click the copy button next to the shortcode of your newly created ad link.</p>
            </div>
            <div class="adnl-help-item">
                <h4>3️⃣ Share with Partners</h4>
                <p>Partners can use the same shortcode on their sites with custom redirects.</p>
            </div>
            <div class="adnl-help-item">
                <h4>4️⃣ Track Performance</h4>
                <p>Monitor click statistics for each ad link right from this dashboard.</p>
            </div>
        </div>
    </div>
</div>
