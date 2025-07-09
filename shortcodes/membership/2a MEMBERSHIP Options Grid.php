add_shortcode('membership_options_grid', function () {
    $plans = [
        'free' => 'Free',
        'premium' => 'Community (€5 one-off or €7.50/mo)',
        'hub_single' => 'HUB Individual (€15/mo)',
        'hub_team_5' => 'HUB Team 5 (€39/mo)',
        'hub_team_10' => 'HUB Team 10 (€69/mo)',
        'hub_team_25' => 'HUB Team 25 (€129/mo)',
        'enterprise' => 'Enterprise'
    ];

    $features = [
        'free' => [
            'Magic/Universal Search',
            'Save up to 25 projects',
            'Download',
            'License',
            'Download history',
            'Community help'
        ],
        'premium' => [
            'All Free options',
            'Unlimited projects',
            '10% discount on licensing',
            'Comp downloading',
            'Message members',
            'Create groups'
        ],
        'hub_single' => [
            'All Community options',
            'Upload images',
            'Bulk upload',
            'Metadata editing',
            'Activity log',
            'Editing shortcuts',
            'Projects',
            'Note taking',
            'Version control'
        ],
        'hub_team_5' => [
            'All HUB Individual options',
            'Team access',
            'Roles',
            'Collaboration tools',
            'Admin user'
        ],
        'hub_team_10' => [
            'All HUB Individual options',
            'Team access',
            'Roles',
            'Collaboration tools',
            'Admin user'
        ],
        'hub_team_25' => [
            'All HUB Individual options',
            'Team access',
            'Roles',
            'Collaboration tools',
            'Admin user'
        ],
        'enterprise' => [
            'All HUB Team options',
            'Bigger teams',
            'Dedicated support',
            '1TB storage',
            'AI tagging and captions'
        ]
    ];

    function render_plan_features($list) {
        $output = '<ul>';
        foreach ($list as $item) {
            $output .= '<li title="' . esc_attr($item) . '">✅ ' . esc_html($item) . '</li>';
        }
        return $output . '</ul>';
    }

    function get_team_discount_badge($price, $team_size) {
        $individual_price = 15;
        $per_user = $price / $team_size;
        $savings = round((($individual_price - $per_user) / $individual_price) * 100);
        return "<div style='text-align:center;margin:5px 0;color:#0a0;font-weight:bold;'>💡 Save {$savings}% vs individual</div>";
    }

    ob_start();
    ?>
    <style>
        .membership-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }
        .membership-box {
            background: #fff; border: 1px solid #ddd; border-radius: 20px;
            padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .membership-box h3 { margin-top: 0; font-size: 1.5em; text-align: center; }
        .membership-section-label { font-weight: bold; margin-top: 40px; font-size: 1.2em; }
        .membership-box ul { list-style: none; padding: 0; margin: 0; }
        .membership-box ul li { padding: 4px 0; }
        .membership-button { display: block; background: #000; color: #fff; padding: 10px 20px; border-radius: 10px; text-decoration: none; margin: 20px auto 0; text-align: center; width: max-content; }
        .team-selector { margin: 10px auto; display: block; text-align: center; font-size: 0.95em; }
        .upsell-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 40px; }
        .upsell-box { background: #f9f9f9; border: 1px solid #ccc; border-radius: 15px; padding: 20px; text-align: center; }
        .upsell-box h4 { margin-top: 0; }
        .upsell-box .price { font-size: 1.1em; margin: 10px 0; }
    </style>

    <div class="membership-section-label">🔍 Search & License + Join the Community</div>
    <div class="membership-grid">
        <?php foreach (['free', 'premium'] as $key): ?>
            <div class="membership-box">
                <h3><?= esc_html($plans[$key]) ?></h3>
                <?= render_plan_features($features[$key]) ?>
                <a href="/membership-checkout/?level=<?= esc_attr($key) ?>" class="membership-button">Choose <?= esc_html($plans[$key]) ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="membership-section-label">📤 Upload, Organise & Integrate Your Content</div>
    <div class="membership-grid">
        <!-- HUB Individual -->
        <div class="membership-box">
            <h3><?= esc_html($plans['hub_single']) ?></h3>
            <?= render_plan_features($features['hub_single']) ?>
            <a href="/membership-checkout/?level=hub_single" class="membership-button">Choose HUB Individual</a>
        </div>

        <!-- HUB Teams with Dropdown -->
        <div class="membership-box">
            <h3>HUB Teams</h3>
            <select class="team-selector" onchange="const box=this.closest('.membership-box'); box.querySelectorAll('.hub-team-options').forEach(e=>e.style.display='none'); box.querySelector('#'+this.value).style.display='block';">
                <option value="hub_team_5">Team of 5</option>
                <option value="hub_team_10">Team of 10</option>
                <option value="hub_team_25">Team of 25</option>
            </select>
            <?php foreach ([
                'hub_team_5' => 39,
                'hub_team_10' => 69,
                'hub_team_25' => 129
            ] as $key => $price): ?>
                <div class="hub-team-options" id="<?= esc_attr($key) ?>" style="display: <?= $key === 'hub_team_5' ? 'block' : 'none' ?>;">
                    <?= get_team_discount_badge($price, (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT)) ?>
                    <?= render_plan_features($features[$key]) ?>
                    <a href="/membership-checkout/?level=<?= esc_attr($key) ?>" class="membership-button">Choose <?= esc_html($plans[$key]) ?></a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Enterprise -->
        <div class="membership-box">
            <h3><?= esc_html($plans['enterprise']) ?></h3>
            <?= render_plan_features($features['enterprise']) ?>
            <a href="/contact" class="membership-button">Request Enterprise Plan</a>
        </div>
    </div>

    <!-- Storage Upsells -->
    <div class="membership-section-label">💾 Expand your library with affordable and safe storage</div>
    <div class="upsell-grid">
        <div class="upsell-box"><h4>+50 GB Storage</h4><div class="price">€4 / month</div><a href="/membership-checkout/?level=storage_50" class="membership-button">Add to Plan</a></div>
        <div class="upsell-box"><h4>+100 GB Storage</h4><div class="price">€7 / month</div><a href="/membership-checkout/?level=storage_100" class="membership-button">Add to Plan</a></div>
        <div class="upsell-box"><h4>+250 GB Storage</h4><div class="price">€12 / month</div><a href="/membership-checkout/?level=storage_250" class="membership-button">Add to Plan</a></div>
        <div class="upsell-box"><h4>+500 GB Storage</h4><div class="price">€20 / month</div><a href="/membership-checkout/?level=storage_500" class="membership-button">Add to Plan</a></div>
        <div class="upsell-box"><h4>+1 TB Storage</h4><div class="price">€35 / month</div><a href="/membership-checkout/?level=storage_1000" class="membership-button">Add to Plan</a></div>
        <div class="upsell-box"><h4>+2 TB Storage</h4><div class="price">€60 / month</div><a href="/membership-checkout/?level=storage_2000" class="membership-button">Add to Plan</a></div>
    </div>
    <?php
    return ob_get_clean();
});
