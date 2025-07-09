add_shortcode('fm_admin_group_manager', function () {
    if (!current_user_can('administrator')) {
        return '<p>Access denied.</p>';
    }

    if (!function_exists('groups_get_groups')) {
        return '<p>BuddyBoss groups function not available.</p>';
    }

    $groups = groups_get_groups(['per_page' => 1000])['groups'];
    if (empty($groups)) return '<p>No groups found.</p>';

    ob_start();
    ?>
    <style>
    .group-admin-panel {
        margin-top: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }
    .group-card {
        border: 1px solid #ccc;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
    }
    .group-card h4 {
        margin-top: 0;
        font-size: 18px;
    }
    .group-card p {
        margin: 4px 0;
    }
    .group-card .btn {
        display: inline-block;
        padding: 6px 12px;
        margin-top: 10px;
        background: #0073aa;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
    }
    </style>

    <div class="group-admin-panel">
        <?php foreach ($groups as $group):
            $admins = groups_get_group_admins($group->id);
            $admin_names = array_map(function ($a) { return $a->display_name; }, $admins);
            ?>
            <div class="group-card">
                <h4><?= esc_html($group->name) ?></h4>
                <p><strong>Members:</strong> <?= intval($group->total_member_count) ?></p>
                <p><strong>Admins:</strong> <?= implode(', ', $admin_names) ?: 'None' ?></p>
                <p><strong>Created:</strong> <?= date('d M Y', strtotime($group->date_created)) ?></p>
                <a href="<?= esc_url(bp_get_group_permalink($group)) ?>" target="_blank" class="btn">🔍 View Group</a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});
