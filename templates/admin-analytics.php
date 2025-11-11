<?php
/**
 * Template for Analytics & Site Tracking
 */

if (!defined('ABSPATH')) {
    exit;
}

$total_sites = count($sites);
$total_clicks_all = 0;
foreach ($sites as $site) {
    $total_clicks_all += $site->total_clicks;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">📊 Analytics & Site Tracking</h1>
    <hr class="wp-header-end">

    <!-- Summary Cards -->
    <div class="adnl-stats-grid">
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">🌐</div>
            <div class="adnl-stat-content">
                <h3><?php echo number_format($total_sites); ?></h3>
                <p>Active Sites Using Plugin</p>
            </div>
        </div>
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">👆</div>
            <div class="adnl-stat-content">
                <h3><?php echo number_format($total_clicks_all); ?></h3>
                <p>Total Network Clicks</p>
            </div>
        </div>
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">📈</div>
            <div class="adnl-stat-content">
                <h3><?php echo $total_sites > 0 ? number_format($total_clicks_all / $total_sites, 1) : '0'; ?></h3>
                <p>Avg. Clicks per Site</p>
            </div>
        </div>
        <div class="adnl-stat-card">
            <div class="adnl-stat-icon">🏆</div>
            <div class="adnl-stat-content">
                <h3><?php echo !empty($sites) ? number_format($sites[0]->total_clicks) : '0'; ?></h3>
                <p>Top Site Clicks</p>
            </div>
        </div>
    </div>

    <!-- Sites Tracking Table -->
    <div class="adnl-dashboard-section" style="margin-top: 20px;">
        <h2>🌐 Sites Using Your Ads</h2>
        
        <?php if (empty($sites)): ?>
            <div class="adnl-empty-state">
                <div class="adnl-empty-icon">🌍</div>
                <h3>No Sites Tracked Yet</h3>
                <p>When partner sites start using your ad links, they'll appear here with detailed metrics.</p>
            </div>
        <?php else: ?>
            <div class="adnl-toolbar">
                <input type="text" id="adnl-site-search" class="adnl-search-input" placeholder="🔍 Search sites...">
            </div>
            
            <div class="adnl-table-wrapper">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Site Name</th>
                            <th>Site URL</th>
                            <th>Total Clicks</th>
                            <th>Last Click</th>
                            <th>First Seen</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody id="sites-table-body">
                        <?php foreach ($sites as $index => $site): ?>
                            <tr class="site-row" data-site="<?php echo esc_attr(strtolower($site->site_name . ' ' . $site->site_url)); ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo esc_html($site->site_name ?: 'Unknown Site'); ?></strong>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($site->site_url); ?>" target="_blank" rel="noopener" class="adnl-site-link">
                                        <?php echo esc_html($site->site_url); ?>
                                    </a>
                                </td>
                                <td>
                                    <strong class="adnl-clicks"><?php echo number_format($site->total_clicks); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    if ($site->last_click) {
                                        echo human_time_diff(strtotime($site->last_click), current_time('timestamp')) . ' ago';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($site->first_seen)); ?>
                                </td>
                                <td>
                                    <?php echo human_time_diff(strtotime($site->last_seen), current_time('timestamp')) . ' ago'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Charts Section -->
    <div class="adnl-charts-grid">
        <!-- Click Trend Chart -->
        <div class="adnl-dashboard-section">
            <h2>📈 Click Trends (Last 30 Days)</h2>
            <canvas id="clickTrendChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Device Distribution -->
        <div class="adnl-dashboard-section">
            <h2>📱 Device Distribution</h2>
            <canvas id="deviceChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <div class="adnl-charts-grid">
        <!-- Browser Distribution -->
        <div class="adnl-dashboard-section">
            <h2>🌐 Browser Distribution</h2>
            <canvas id="browserChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Top Links -->
        <div class="adnl-dashboard-section">
            <h2>🏆 Top Performing Links</h2>
            <?php if (empty($top_links)): ?>
                <p>No data available yet.</p>
            <?php else: ?>
                <div class="adnl-top-links">
                    <?php foreach ($top_links as $index => $link): ?>
                        <div class="adnl-top-link-item">
                            <div class="adnl-top-link-rank"><?php echo $index + 1; ?></div>
                            <div class="adnl-top-link-info">
                                <strong><?php echo esc_html($link->name); ?></strong>
                                <span class="adnl-top-link-clicks"><?php echo number_format($link->clicks); ?> clicks</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Clicks -->
    <div class="adnl-dashboard-section" style="margin-top: 20px;">
        <h2>🕐 Recent Clicks</h2>
        
        <?php if (empty($recent_clicks)): ?>
            <p>No clicks recorded yet.</p>
        <?php else: ?>
            <div class="adnl-table-wrapper">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Ad Link</th>
                            <th>Source Site</th>
                            <th>Device</th>
                            <th>Browser</th>
                            <th>OS</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_clicks as $click): ?>
                            <tr>
                                <td><strong><?php echo esc_html($click->link_name ?: $click->link_id); ?></strong></td>
                                <td>
                                    <a href="<?php echo esc_url($click->site_url); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr($click->site_name); ?>">
                                        <?php echo esc_html($click->site_name ?: parse_url($click->site_url, PHP_URL_HOST)); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($click->device_type ?: '-'); ?></td>
                                <td><?php echo esc_html($click->browser ?: '-'); ?></td>
                                <td><?php echo esc_html($click->os ?: '-'); ?></td>
                                <td><?php echo human_time_diff(strtotime($click->clicked_at), current_time('timestamp')) . ' ago'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Site search functionality
    $('#adnl-site-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.site-row').each(function() {
            var siteData = $(this).data('site');
            if (searchTerm === '' || siteData.indexOf(searchTerm) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});

// Chart data
var clickTrendData = <?php echo json_encode($click_stats); ?>;
var deviceData = <?php echo json_encode($clicks_by_device); ?>;
var browserData = <?php echo json_encode($clicks_by_browser); ?>;
</script>
