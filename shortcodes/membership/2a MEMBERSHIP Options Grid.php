<?php
add_shortcode('membership_options_grid', function () {
    $plans = [
        1 => 'Free',
        2 => 'Community (\u20ac3.50 + \u20ac4.50/mo)',
        3 => 'Community Year (\u20ac35/yr)',
        4 => 'HUB Individual (\u20ac15/mo)',
        5 => 'HUB Team 5 (\u20ac20/mo)',
        6 => 'HUB Team 10 (\u20ac40/mo)',
        7 => 'HUB Team 25 (\u20ac100/mo)',
        'enterprise' => 'Enterprise'
    ];

    $features = [
        1 => ['Magic/Universal Search', 'Save up to 25 projects', 'Download', 'License', 'Download history', 'Community help'],
        2 => ['All Free options', 'Unlimited projects', '10% discount on licensing', 'Comp downloading', 'Message members', 'Create groups'],
        3 => ['All Free options', 'Unlimited projects', '10% discount on licensing', 'Comp downloading', 'Message members', 'Create groups'],
        4 => ['All Community options', 'Upload images', 'Bulk upload', 'Metadata editing', 'Activity log', 'Editing shortcuts', 'Projects', 'Note taking', 'Version control'],
        5 => ['All HUB Individual options', 'Team access', 'Roles', 'Collaboration tools', 'Admin user'],
        6 => ['All HUB Individual options', 'Team access', 'Roles', 'Collaboration tools', 'Admin user'],
        7 => ['All HUB Individual options', 'Team access', 'Roles', 'Collaboration tools', 'Admin user'],
        'enterprise' => ['All HUB Team options', 'Bigger teams', 'Dedicated support', '1TB storage', 'AI tagging and captions']
    ];

    function render_features($items) {
        $out = '<ul>';
        foreach ($items as $item) {
            $out .= '<li>✅ ' . esc_html($item) . '</li>';
        }
        return $out . '</ul>';
    }

    ob_start();
    ?>
    <style>
        .membership-tabs { margin: 20px 0; }
        .membership-tab-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .membership-tab-buttons button {
            background: #eee; border: none; padding: 10px 20px; border-radius: 10px;
            cursor: pointer; font-weight: bold;
        }
        .membership-tab-buttons button.active { background: #000; color: #fff; }
        .membership-tab-content { display: none; }
        .membership-tab-content.active { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .membership-box {
            background: #fff; border: 1px solid #ddd; border-radius: 16px; padding: 25px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .membership-box h3 { margin-top: 0; text-align: center; }
        .membership-button {
            display: block; margin-top: 15px; text-align: center; background: #000;
            color: #fff; padding: 10px 15px; border-radius: 8px; text-decoration: none;
        }
    </style>

    <div class="membership-tabs">
        <div class="membership-tab-buttons">
            <button data-tab="tab1" class="active">🎓 Community</button>
            <button data-tab="tab2">📤 HUB Plans</button>
            <button data-tab="tab3">🏢 Enterprise</button>
        </div>

        <div id="tab1" class="membership-tab-content active">
            <?php foreach ([1, 2, 3] as $id): ?>
                <div class="membership-box">
                    <h3><?= esc_html($plans[$id]) ?></h3>
                    <?= render_features($features[$id]) ?>
                    <a href="/membership-checkout/?level=<?= esc_attr($id) ?>" class="membership-button">Choose Plan</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tab2" class="membership-tab-content">
            <?php foreach ([4, 5, 6, 7] as $id): ?>
                <div class="membership-box">
                    <h3><?= esc_html($plans[$id]) ?></h3>
                    <?= render_features($features[$id]) ?>
                    <a href="/membership-checkout/?level=<?= esc_attr($id) ?>" class="membership-button">Choose Plan</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tab3" class="membership-tab-content">
            <div class="membership-box">
                <h3><?= esc_html($plans['enterprise']) ?></h3>
                <?= render_features($features['enterprise']) ?>
                <a href="/contact" class="membership-button">Request Enterprise Plan</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.membership-tab-buttons button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.membership-tab-buttons button').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.membership-tab-content').forEach(tab => tab.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });
    </script>
    <?php
    return ob_get_clean();
});
