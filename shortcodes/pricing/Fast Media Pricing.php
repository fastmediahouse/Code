add_shortcode('membership_options_grid', function () {
    $plans = [
        1 => 'Free',
        2 => 'Community (€3.50/mo)',
        3 => 'Community Year (€35/yr)',
        4 => 'HUB Individual (€5/mo)',
        5 => 'HUB Team 5 (€22/mo)',
        6 => 'HUB Team 10 (€40/mo)',
        7 => 'HUB Team 25 (€95/mo)',
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
        $out = '<ul class="fm-features">'; 
        foreach ($list as $item) {
            $out .= '<li><span class="fm-check">✅</span> '.esc_html($item).'</li>'; 
        }
        return $out.'</ul>';
    }

    function get_team_discount_badge($price, $team_size) {
        $individual_price = 5; // HUB Individual is €5/mo
        $per_user = $price / $team_size;
        
        // For Team 5: €22/5 = €4.40 per user (12% savings)
        // For Team 10: €40/10 = €4 per user (20% savings)
        // For Team 25: €95/25 = €3.80 per user (24% savings)
        
        $savings = round((($individual_price - $per_user) / $individual_price) * 100);
        
        // Add per-user price info for clarity
        if ($savings > 0) {
            $per_user_formatted = number_format($per_user, 2);
            return "<div class='fm-discount-badge'>💡 Save {$savings}% vs individual<br><small>€{$per_user_formatted}/user per month</small></div>";
        }
        return "";
    }

    ob_start();
    ?>
    <style>
    .fm-container { max-width: 1200px; margin: 0 auto; }
    
    .fm-tabs { 
        display: flex; 
        gap: 10px; 
        margin-bottom: 30px; 
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .fm-tab {
        padding: 12px 24px;
        border-radius: 12px;
        border: 2px solid transparent;
        background: #e3f2fd;
        color: #0056b3;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .fm-tab:hover {
        background: #bbdefb;
        transform: translateY(-2px);
    }
    
    .fm-tab.active {
        background: #0056b3;
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 86, 179, 0.3);
    }
    
    .fm-grid { 
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        padding: 20px 0;
    }
    
    .fm-card {
        background: #fff;
        border: 2px solid #e5e5e5;
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .fm-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        border-color: #d0d0d0;
    }
    
    .fm-card h3 {
        margin: 0 0 25px 0;
        font-size: 28px;
        color: #222;
    }
    
    .fm-card h4 {
        margin: 0 0 20px 0;
        font-size: 20px;
        color: #333;
    }
    
    .fm-card p {
        font-size: 24px;
        font-weight: 600;
        color: #0056b3;
        margin: 15px 0;
    }
    
    .fm-features {
        list-style: none;
        padding-left: 0;
        margin: 20px 0;
        flex-grow: 1;
    }
    
    .fm-features li {
        padding: 8px 0;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.5;
    }
    
    .fm-check {
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .fm-btn {
        background: linear-gradient(135deg, #28a745, #218838);
        color: #fff;
        padding: 14px 28px;
        border-radius: 12px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        margin-top: 20px;
        border: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        width: 100%;
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }
    
    .fm-btn:hover {
        background: linear-gradient(135deg, #218838, #1e7e34);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4);
        text-decoration: none;
        color: #fff;
    }
    
    .fm-team-selector {
        background: #fff;
        border: 2px solid #e5e5e5;
        border-radius: 12px;
        padding: 12px 16px;
        width: 100%;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        color: #333;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 20px;
        padding-right: 48px;
    }
    
    .fm-team-selector:hover {
        border-color: #28a745;
        background-color: #f8f9fa;
    }
    
    .fm-team-selector:focus {
        outline: none;
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
    
    .fm-team-selector option {
        color: #333;
        background: #fff;
        padding: 10px;
    }
    
    .hub-team-options {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .fm-discount-badge {
        text-align: center;
        margin: 15px 0;
        padding: 12px;
        background: #e8f5e9;
        border-radius: 10px;
        color: #2e7d32;
        font-weight: 600;
        font-size: 15px;
        line-height: 1.4;
    }
    
    .fm-discount-badge small {
        display: block;
        font-size: 13px;
        margin-top: 4px;
        font-weight: 500;
        color: #388e3c;
    }
    
    /* Storage tab specific styles */
    #tab-storage .fm-card {
        text-align: center;
    }
    
    #tab-storage .fm-card h3 {
        color: #0056b3;
        margin-bottom: 15px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .fm-grid {
            grid-template-columns: 1fr;
        }
        
        .fm-card {
            padding: 25px;
        }
        
        .fm-tabs {
            gap: 8px;
        }
        
        .fm-tab {
            padding: 10px 18px;
            font-size: 14px;
        }
    }
    </style>

    <div class="fm-container">
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
                    <select class="fm-team-selector" onchange="document.querySelectorAll('.hub-team-options').forEach(el=>el.style.display='none'); document.getElementById(this.value).style.display='block';">
                        <option value="hub_team_5">Team of 5</option>
                        <option value="hub_team_10">Team of 10</option>
                        <option value="hub_team_25">Team of 25</option>
                    </select>
                    <?php 
                    $team_configs = [
                        5 => ['price' => 22, 'size' => 5],
                        6 => ['price' => 40, 'size' => 10],
                        7 => ['price' => 95, 'size' => 25]
                    ];
                    foreach ($team_configs as $id => $config): ?>
                        <div id="hub_team_<?= $config['size'] ?>" class="hub-team-options" style="<?= $id === 5 ? 'display:block;' : '' ?>">
                            <h4><?= esc_html($plans[$id]) ?></h4>
                            <?= get_team_discount_badge($config['price'], $config['size']) ?>
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
    </div>

    <script>
    function fmShowTab(key) {
        document.querySelectorAll('.fm-tab').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id^="tab-"]').forEach(tab => tab.style.display = 'none');
        document.querySelector(`[onclick*="'${key}'"]`).classList.add('active');
        document.getElementById('tab-' + key).style.display = 'grid';
    }
    </script>
    <?php
    return ob_get_clean();
});
