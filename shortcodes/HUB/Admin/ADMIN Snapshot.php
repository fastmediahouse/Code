add_shortcode('fm_admin_snapshot', function () {
    if (!current_user_can('administrator')) return '<p>Access denied.</p>';

    // Data Aggregation
    $users         = get_users();
    $total_users   = count($users);
    $total_assets  = count(get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1]));
    $total_projects = count(get_posts(['post_type' => 'lightbox', 'post_status' => 'publish', 'posts_per_page' => -1]));
    $total_groups  = function_exists('groups_get_groups') ? count(groups_get_groups(['per_page' => 1000])['groups']) : 0;

    $new_users = get_users(['orderby' => 'registered', 'order' => 'DESC', 'date_query' => [['after' => '1 week ago']]]);
    $recent_uploads = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC']);
    $uploads_last_week = array_filter($recent_uploads, fn($u) => strtotime($u->post_date) > strtotime('-7 days'));

    $orders = wc_get_orders(['limit' => -1, 'status' => ['wc-completed', 'wc-processing'], 'date_created' => '>=' . (new DateTime('-1 month'))->format('Y-m-d')]);
    $total_orders = count($orders);

    $upload_counts = [];
    foreach ($recent_uploads as $u) {
        $uid = $u->post_author;
        $upload_counts[$uid] = ($upload_counts[$uid] ?? 0) + 1;
    }
    arsort($upload_counts);
    $top_uploaders = array_slice($upload_counts, 0, 3, true);

    $recent_projects = get_posts(['post_type' => 'lightbox', 'post_status' => 'publish', 'posts_per_page' => 10, 'orderby' => 'date', 'order' => 'DESC']);

    $recent_activity = [];
    foreach ($new_users as $u) $recent_activity[] = ['type' => 'user', 'label' => $u->display_name];
    foreach ($recent_uploads as $u) $recent_activity[] = ['type' => 'upload', 'label' => $u->post_title];
    foreach ($recent_projects as $p) $recent_activity[] = ['type' => 'project', 'label' => $p->post_title];
    if (function_exists('bp_activity_get')) {
        $bp = bp_activity_get(['per_page' => 10]);
        foreach ($bp['activities'] as $a) $recent_activity[] = ['type' => 'bboss', 'label' => strip_tags($a->action)];
    }
    $recent_activity = array_slice($recent_activity, 0, 25);

    ob_start();
    ?>
    <style>
    .fm-snapshot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        max-width: 1600px;
        margin: 0 auto;
    }
    .fm-snap-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .fm-snap-title { font-weight: bold; font-size: 16px; margin-bottom: 12px; }
    .fm-snap-card.alerts { border-left: 4px solid #f90; background: #fff8e6; }
    ul.fm-timeline li { font-size: 14px; margin-bottom: 4px; }
    </style>

    <div class="fm-snapshot-grid">
        <div class="fm-snap-card">
            <div class="fm-snap-title">📌 Platform Snapshot</div>
            <ul>
                <li>Users: <?= $total_users ?></li>
                <li>Assets: <?= $total_assets ?></li>
                <li>Projects: <?= $total_projects ?></li>
                <li>Groups: <?= $total_groups ?></li>
            </ul>
        </div>
        <div class="fm-snap-card alerts">
            <div class="fm-snap-title">⚠️ Admin Alerts</div>
            <ul>
                <li><?= count($new_users) ?> new users this week</li>
                <li><?= count($uploads_last_week) ?> uploads this week</li>
                <li><?= $total_orders ?> purchases this month</li>
            </ul>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">📈 Upload Growth</div>
            <canvas id="chartUploads"></canvas>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">📁 Projects per Week</div>
            <canvas id="chartProjects"></canvas>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">📤 Assets Uploaded</div>
            <div><?= count($recent_uploads) ?> in past 50</div>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">✅ Assets Purchased</div>
            <div><?= $total_orders ?> WooCommerce orders</div>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">📉 Purchased vs Uploaded</div>
            <canvas id="chartPurchaseUpload"></canvas>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">📊 Project Status</div>
            <canvas id="chartProjectStatus"></canvas>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">🍰 Asset Types</div>
            <canvas id="chartAssetTypes"></canvas>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">💸 Budget vs Spent</div>
            <p>Placeholder – tracking TBD</p>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">📏 % Licensed vs Uploaded</div>
            <p><?= $total_assets ? round(($total_orders / $total_assets) * 100, 1) : 0 ?>% licensed</p>
        </div>
        <div class="fm-snap-card">
            <div class="fm-snap-title">🕒 Recent Activity</div>
            <ul class="fm-timeline">
                <?php foreach ($recent_activity as $e): ?>
                    <li><?= ucfirst($e['type']) ?>: <?= esc_html($e['label']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx1 = document.getElementById('chartUploads').getContext('2d');
    const ctx2 = document.getElementById('chartProjects').getContext('2d');
    const ctx3 = document.getElementById('chartPurchaseUpload').getContext('2d');
    const ctx4 = document.getElementById('chartProjectStatus').getContext('2d');
    const ctx5 = document.getElementById('chartAssetTypes').getContext('2d');
    new Chart(ctx1, { type: 'bar', data: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets: [{ label: 'Uploads', data: [12,18,7,16,10,4,2], backgroundColor: '#4e79a7' }] } });
    new Chart(ctx2, { type: 'line', data: { labels: ['W1','W2','W3','W4'], datasets: [{ label: 'Projects', data: [2,4,3,6], borderColor: '#f28e2b' }] } });
    new Chart(ctx3, { type: 'bar', data: { labels: ['Purchased','Uploaded'], datasets: [{ label: 'This Month', data: [<?= $total_orders ?>, <?= count($recent_uploads) ?>], backgroundColor: '#ffc658' }] } });
    new Chart(ctx4, { type: 'bar', data: { labels: ['Active','Archived'], datasets: [{ label: 'Projects', data: [5,0], backgroundColor: '#59a14f' }] } });
    new Chart(ctx5, { type: 'doughnut', data: { labels: ['Editorial','Creative','AI','Music'], datasets: [{ data: [120,90,50,20], backgroundColor: ['#e15759','#76b7b2','#f1a340','#bab0ab'] }] } });
    </script>
    <?php
    return ob_get_clean();
});
