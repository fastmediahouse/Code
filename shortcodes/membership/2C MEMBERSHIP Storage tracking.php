// ✅ 2C STORAGE TRACKING AND LIMIT ENFORCEMENT

// Track file size on upload
add_action('add_attachment', function($post_ID) {
    $file_path = get_attached_file($post_ID);
    if (!$file_path || !file_exists($file_path)) return;

    $size_mb = filesize($file_path) / 1024 / 1024;
    $user_id = get_current_user_id();
    $team_id = get_user_meta($user_id, 'team_id', true);

    if ($team_id) {
        // It's a team user
        $team_post = get_page_by_path($team_id, OBJECT, 'fm_team');
        $current = (float)get_post_meta($team_post->ID, 'storage_used_mb', true);
        update_post_meta($team_post->ID, 'storage_used_mb', $current + $size_mb);
    } else {
        // Single user
        $current = (float)get_user_meta($user_id, 'storage_used_mb', true);
        update_user_meta($user_id, 'storage_used_mb', $current + $size_mb);
    }
});

// Helper: Check if user is over storage quota
function fm_is_over_storage_limit($user_id = null) {
    $user_id = $user_id ?: get_current_user_id();
    $team_id = get_user_meta($user_id, 'team_id', true);

    if ($team_id) {
        $team_post = get_page_by_path($team_id, OBJECT, 'fm_team');
        $used = (float)get_post_meta($team_post->ID, 'storage_used_mb', true);
        $limit = (float)get_post_meta($team_post->ID, 'storage_limit_mb', true);
    } else {
        $used = (float)get_user_meta($user_id, 'storage_used_mb', true);
        $benefits = fm_get_membership_benefits();
        $level = pmpro_getMembershipLevelForUser($user_id);
        $limit = $benefits[strtolower($level->name)]['storage_limit'] ?? 0;
    }

    return $used >= $limit;
}

// ✅ 2C-EXT STORAGE USAGE BAR (Helper only – call where needed)
function fm_get_storage_warning($user_id = null) {
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return '';

    $team_id = get_user_meta($user_id, 'team_id', true);

    if ($team_id) {
        $team_post = get_page_by_path($team_id, OBJECT, 'fm_team');
        if (!$team_post) return '';
        $used = (float)get_post_meta($team_post->ID, 'storage_used_mb', true);
        $limit = (float)get_post_meta($team_post->ID, 'storage_limit_mb', true);
    } else {
        $used = (float)get_user_meta($user_id, 'storage_used_mb', true);
        $benefits = fm_get_membership_benefits();
        $level = pmpro_getMembershipLevelForUser($user_id);
        $limit = $benefits[strtolower($level->name)]['storage_limit'] ?? 0;
    }

    if ($limit <= 0) return ''; // no storage limit

    $percent = round(($used / $limit) * 100);
    $percent = min($percent, 100); // cap at 100%

    ob_start();
    ?>
    <div style="margin: 10px 0;">
        <div style="font-size: 0.9em; margin-bottom: 4px;">
            Storage used: <?= round($used) ?> MB of <?= round($limit) ?> MB
        </div>
        <div style="background: #eee; border-radius: 5px; overflow: hidden; height: 14px;">
            <div style="width: <?= $percent ?>%; background: <?= $percent < 90 ? '#28a745' : ($percent < 100 ? '#ffc107' : '#dc3545') ?>; height: 100%;"></div>
        </div>
        <?php if ($percent >= 100): ?>
            <div style="color: #dc3545; font-size: 0.9em; margin-top: 6px;">
                🚫 Storage limit reached. Please upgrade your plan.
            </div>
        <?php elseif ($percent >= 90): ?>
            <div style="color: #856404; font-size: 0.9em; margin-top: 6px;">
                ⚠️ You're nearing your storage limit.
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
