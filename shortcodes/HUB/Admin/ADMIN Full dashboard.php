add_shortcode('fm_admin_dashboard', function () {
    if (!current_user_can('administrator')) return '<p>Access denied.</p>';

    // Metrics
    $pending_users   = count(get_users(['role' => 'subscriber', 'meta_key' => 'manual_approval_pending', 'meta_value' => 'yes']));
    $pending_assets  = count(get_posts(['post_type' => 'attachment', 'post_status' => 'pending', 'posts_per_page' => -1]));
    $new_users       = count(get_users(['orderby' => 'registered', 'order' => 'DESC', 'date_query' => [['after' => '1 week ago']]]));
    $total_users     = count_users()['total_users'];
    $total_groups    = function_exists('groups_get_groups') ? count(groups_get_groups(['per_page' => 1000])['groups']) : 0;
    $total_projects  = count(get_posts(['post_type' => 'lightbox', 'post_status' => 'publish', 'posts_per_page' => -1]));
    $total_assets    = count(get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1]));

    // Helper for badge class
    function fm_stat_badge($count) {
        $class = $count > 0 ? 'fm-stat-badge active' : 'fm-stat-badge';
        return "<span class=\"$class\">$count</span>";
    }

    ob_start();
    ?>
    <style>
    .fm-admin-wrapper {
        max-width: 1600px;
        margin: 0 auto;
        padding: 24px;
        font-family: system-ui, sans-serif;
        display: grid;
        grid-template-columns: 1fr 1fr 320px;
        gap: 24px;
    }
    .fm-admin-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        padding: 24px;
        border: 1px solid #eee;
        transition: all 0.2s ease;
    }
    .fm-admin-wrapper > div > .fm-admin-card:not(:first-child) {
        margin-top: 16px;
    }
    .fm-admin-card h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 16px;
    }
    .fm-admin-card .chart-wrapper {
        padding-top: 12px;
        padding-bottom: 12px;
    }
    .fm-admin-card canvas {
        width: 100% !important;
        height: 200px !important;
        display: block;
    }
    .fm-snapshot-list {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 16px;
    }
    .fm-snapshot-list li {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f3f3;
        font-weight: 500;
    }
    .fm-alerts p {
        margin: 6px 0;
        font-size: 15px;
    }
    .fm-sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .fm-tile {
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .fm-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.05);
    }
    .fm-tile-icon {
        font-size: 22px;
        margin-bottom: 8px;
    }
    .fm-tile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .fm-tile-title {
        font-weight: 600;
        font-size: 15px;
    }
    .fm-tile-description {
        font-size: 14px;
        color: #555;
    }
    .fm-stat-badge {
        font-size: 13px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
        background: #eee;
        color: #444;
        margin-left: 8px;
    }
    .fm-stat-badge.active {
        background: #007aff1a;
        color: #007aff;
    }
    @media (max-width: 1024px) {
        .fm-admin-wrapper {
            grid-template-columns: 1fr;
        }
        .fm-sidebar-menu {
            flex-direction: row;
            flex-wrap: wrap;
        }
        .fm-admin-card {
            flex: 1 1 300px;
        }
    }
    </style>

    <div class="fm-admin-wrapper">

        <!-- Left Column -->
        <div>
            <div class="fm-admin-card">
                <h3>📌 Platform Snapshot</h3>
                <ul class="fm-snapshot-list">
                    <li><span>Users</span><span><?= $total_users ?></span></li>
                    <li><span>Groups</span><span><?= $total_groups ?></span></li>
                    <li><span>Projects</span><span><?= $total_projects ?></span></li>
                    <li><span>Assets</span><span><?= $total_assets ?></span></li>
                </ul>
            </div>
            <div class="fm-admin-card">
                <h3>Projects per Week</h3>
                <div class="chart-wrapper"><canvas id="chartProjects"></canvas></div>
            </div>
            <div class="fm-admin-card">
                <h3>Assets Uploaded</h3>
                <div class="chart-wrapper"><canvas id="chartUploaded"></canvas></div>
            </div>
            <div class="fm-admin-card">
                <h3>User Activity</h3>
                <div class="chart-wrapper"><canvas id="chartUsers"></canvas></div>
            </div>
        </div>

        <!-- Middle Column -->
        <div>
            <div class="fm-admin-card fm-alerts">
                <h3>🔔 Admin Alerts</h3>
                <p><?= $pending_users ?> users pending approval</p>
                <p><?= $pending_assets ?> brand assets pending</p>
                <p><?= $new_users ?> new users this week</p>
            </div>
            <div class="fm-admin-card">
                <h3>Assets Purchased</h3>
                <div class="chart-wrapper"><canvas id="chartPurchased"></canvas></div>
            </div>
            <div class="fm-admin-card">
                <h3>Purchased vs Uploaded</h3>
                <div class="chart-wrapper"><canvas id="chartCompare"></canvas></div>
            </div>
            <div class="fm-admin-card">
                <h3>Project Status</h3>
                <div class="chart-wrapper"><canvas id="chartProjectStatus"></canvas></div>
            </div>
        </div>

        <!-- Right Sidebar Menu -->
        <div class="fm-sidebar-menu">

            <a href="/admin-security/" class="fm-admin-card fm-tile">
                <div class="fm-tile-header">
                    <div class="fm-tile-title">🔐 Security / Approvals</div>
                    <?= fm_stat_badge($pending_users) ?>
                </div>
                <div class="fm-tile-description">Manual approval queue</div>
            </a>

            <a href="/admin-users/" class="fm-admin-card fm-tile">
                <div class="fm-tile-header">
                    <div class="fm-tile-title">👤 User Manager</div>
                    <?= fm_stat_badge($total_users) ?>
                </div>
                <div class="fm-tile-description">Manage roles & users</div>
            </a>

            <a href="/admin-groups/" class="fm-admin-card fm-tile">
                <div class="fm-tile-header">
                    <div class="fm-tile-title">👥 Group Manager</div>
                    <?= fm_stat_badge($total_groups) ?>
                </div>
                <div class="fm-tile-description">Teams & permissions</div>
            </a>

            <a href="/admin-projects/" class="fm-admin-card fm-tile">
                <div class="fm-tile-header">
                    <div class="fm-tile-title">📁 Projects</div>
                    <?= fm_stat_badge($total_projects) ?>
                </div>
                <div class="fm-tile-description">Monitor active lightboxes</div>
            </a>

            <a href="/admin-brand/" class="fm-admin-card fm-tile">
                <div class="fm-tile-header">
                    <div class="fm-tile-title">🎨 Brand Assets</div>
                    <?= fm_stat_badge($pending_assets) ?>
                </div>
                <div class="fm-tile-description">Review uploaded content</div>
            </a>

            <a href="/admin-dashboard/" class="fm-admin-card fm-tile">
                <div class="fm-tile-title">📊 Analytics</div>
                <div class="fm-tile-description">Charts & metrics</div>
            </a>
        </div>
    </div>

    <!-- Optional Placeholder Preview -->
    <script>
    document.querySelectorAll("canvas").forEach(canvas => {
        const ctx = canvas.getContext("2d");
        ctx.fillStyle = "#f0f0f0";
        ctx.fillRect(0, 50, canvas.width, 50);
        ctx.fillStyle = "#ccc";
        ctx.fillRect(20, 30, 40, 120);
        ctx.fillRect(80, 60, 40, 90);
        ctx.fillRect(140, 40, 40, 110);
    });
    </script>
    <?php
    return ob_get_clean();
});
