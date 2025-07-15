add_shortcode('membership_options_grid', function () {
    $plans = [
        1 => 'Free',
        2 => 'Community (€3.50/mo)',
        3 => 'Community Year (€35/yr)',
        4 => 'HUB Individual (€15/mo)',
        5 => 'HUB Team 5 (€20/mo)',
        6 => 'HUB Team 10 (€40/mo)',
        7 => 'HUB Team 25 (€100/mo)',
        9 => 'Enterprise'
    ];

    $features = [
        1 => ['Magic/Universal Search','Save up to 25 projects','Download','License','Download history','Community help'],
        2 => ['All Free options','Unlimited projects','10% discount on licensing','Comp downloading','Message members','Create groups'],
        3 => ['All Free options','Unlimited projects','10% discount on licensing','Comp downloading','Message members','Create groups'],
        4 => ['All Community options','Upload images','Bulk upload','Metadata editing','Activity log','Editing shortcuts','Projects','Note taking','Version control'],
        5 => ['All HUB Individual options','Team access','Roles','Collaboration tools','Admin user'],
        6 => ['All HUB Individual options','Team access','Roles','Collaboration tools','Admin user'],
        7 => ['All HUB Individual options','Team access','Roles','Collaboration tools','Admin user'],
        9 => ['All HUB Team options','Bigger teams','Dedicated support','1TB storage','AI tagging and captions']
    ];

    function render_plan_features($list) {
        $out = '<ul>'; foreach ($list as $item) $out .= '<li>✅ '.esc_html($item).'</li>'; return $out.'</ul>';
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
    .fm-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .fm-tab {
        padding: 10px 20px; border-radius: 10px; border: none;
        background: #cce5ff; color: #0056b3; font-weight: bold; cursor: pointer;
    }
    .fm-tab.active {
        background: #0056b3; color: #fff;
    }
    .fm-grid { display: flex; flex-wrap: wrap; gap: 30px; }
    .fm-card {
        background: #fff; border: 1px solid #ddd; border-radius: 20px; flex: 1 1 300px; padding: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;
    }
    .fm-card ul { list-style: none; padding-left: 0; }
    .fm-card ul li { padding: 5px 0; }
    .fm-btn { background: #28a745; color: #fff; padding: 10px 20px; border-radius: 10px; text-align: center; text-decoration: none; display: inline-block; margin-top: 20px; border: none; font-weight: bold; }
    .hub-team-options { display: none; }
    </style>

    <div class="fm-tabs">
        <button class="fm-tab active" onclick="fmShowTab('community')">🎓 Community</button>
        <button class="fm-tab" onclick="fmShowTab('hub')">📤 HUB Plans</button>
        <button class="fm-tab" onclick="fmShowTab('enterprise')">📚 Enterprise</button>
        <button class="fm-tab" onclick="fmShowTab('storage')">💾 Storage Add-ons</button>
    </div>

    <div id="fmTabContent">
        <div id="tab-community" class="fm-grid">
            <?php foreach ([1, 2, 3] as $id): ?>
                <div class="fm-card">
                    <h3><?= esc_html($plans[$id]) ?></h3>
                    <?= render_plan_features($features[$id]) ?>
                    <a href="/membership-checkout/?level=<?= $id ?>" class="fm-btn">Choose Plan</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tab-hub" class="fm-grid" style="display:none;">
            <div class="fm-card">
                <h3><?= esc_html($plans[4]) ?></h3>
                <?= render_plan_features($features[4]) ?>
                <a href="/membership-checkout/?level=4" class="fm-btn">Choose Plan</a>
            </div>
            <div class="fm-card">
                <h3>HUB Teams</h3>
                <select onchange="document.querySelectorAll('.hub-team-options').forEach(el=>el.style.display='none'); document.getElementById(this.value).style.display='block';">
                    <option value="hub_team_5">Team of 5</option>
                    <option value="hub_team_10">Team of 10</option>
                    <option value="hub_team_25">Team of 25</option>
                </select>
                <?php foreach ([5 => 20, 6 => 40, 7 => 100] as $id => $price): ?>
                    <?php $key = 'hub_team_' . ((int) filter_var($plans[$id], FILTER_SANITIZE_NUMBER_INT)); ?>
                    <div id="<?= esc_attr($key) ?>" class="hub-team-options" style="<?= $id === 5 ? 'display:block;' : '' ?>">
                        <?= get_team_discount_badge($price, (int) filter_var($plans[$id], FILTER_SANITIZE_NUMBER_INT)) ?>
                        <?= render_plan_features($features[$id]) ?>
                        <a href="/membership-checkout/?level=<?= $id ?>" class="fm-btn">Choose <?= esc_html($plans[$id]) ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="tab-enterprise" class="fm-grid" style="display:none;">
            <div class="fm-card">
                <h3><?= esc_html($plans[9]) ?></h3>
                <?= render_plan_features($features[9]) ?>
                <a href="/contact" class="fm-btn">Request Enterprise Plan</a>
            </div>
        </div>

        <div id="tab-storage" class="fm-grid" style="display:none;">
            <?php foreach ([
                '50' => 4,
                '100' => 7,
                '250' => 12,
                '500' => 20,
                '1000' => 35,
                '2000' => 60
            ] as $gb => $price): ?>
                <div class="fm-card">
                    <h3>+<?= $gb ?> GB Storage</h3>
                    <p>€<?= $price ?> / month</p>
                    <a href="/membership-checkout/?level=storage_<?= $gb ?>" class="fm-btn">Add to Plan</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function fmShowTab(key) {
        document.querySelectorAll('.fm-tab').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.fm-grid').forEach(tab => tab.style.display = 'none');
        document.querySelector(`[onclick*="'${key}'"]`).classList.add('active');
        document.getElementById('tab-' + key).style.display = 'flex';
    }
    </script>
    <?php
    return ob_get_clean();
});
