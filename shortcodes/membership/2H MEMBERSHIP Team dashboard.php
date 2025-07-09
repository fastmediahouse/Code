/**
 * 2H MEMBERSHIP – Team dashboard with auto BuddyBoss group creation + admin visibility fix (REVISED)
 * Shortcode: [fm_team_dashboard]
 */

add_action('save_post', function ($post_id) {
    if (get_post_type($post_id) !== 'team') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $group_id = get_post_meta($post_id, 'group_id', true);
    $existing_group = $group_id ? groups_get_groupmeta($group_id, 'team_post_id', true) : false;

    if (!$existing_group) {
        $team_name = get_the_title($post_id);
        $team_slug = 'team-' . sanitize_title($team_name);
        $admin_login = get_post_meta($post_id, 'team_admin', true);

        $admin_user = get_user_by('login', $admin_login);
        $creator_id = $admin_user ? $admin_user->ID : get_current_user_id();

        if (!$creator_id || !function_exists('groups_create_group')) return;

        $group_args = [
            'name'        => $team_name,
            'slug'        => $team_slug,
            'description' => 'Group for team: ' . $team_name,
            'status'      => 'private',
            'creator_id'  => $creator_id,
        ];

        $new_group_id = groups_create_group($group_args);

        if ($new_group_id && is_numeric($new_group_id)) {
            groups_update_groupmeta($new_group_id, 'team_post_id', $post_id);
            update_post_meta($post_id, 'group_id', $new_group_id);
            groups_join_group($new_group_id, $creator_id);
        }
    }
});

add_shortcode('fm_team_dashboard', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a> to view your team dashboard.</p>';
    $user_id = get_current_user_id();
    $output = '';

    $teams = get_posts([
        'post_type'   => 'team',
        'numberposts' => -1,
        'post_status' => 'publish'
    ]);

    $user = wp_get_current_user();
    $user_login = $user->user_login;
    $has_team = false;

    foreach ($teams as $team) {
        $admin_user = get_post_meta($team->ID, 'team_admin', true);
        $group_id   = get_post_meta($team->ID, 'group_id', true);

        if ($admin_user === $user_login || ($group_id && groups_is_user_member($user_id, $group_id))) {
            $has_team = true;
            $output .= '<div class="team-box">';
            $output .= '<h3>' . esc_html($team->post_title) . '</h3>';
            $output .= '<p><strong>Team ID:</strong> ' . esc_html(get_post_meta($team->ID, 'team_id', true)) . '</p>';
            $output .= '<p><strong>Seats:</strong> ' . esc_html(get_post_meta($team->ID, 'team_seats', true)) . '</p>';
            $output .= '<p><strong>Storage:</strong> ' . esc_html(get_post_meta($team->ID, 'team_storage', true)) . ' MB</p>';
            $output .= '<p><strong>Admin:</strong> ' . esc_html($admin_user) . '</p>';
            $output .= '</div>';
        }
    }

    if (!$has_team) {
        $output .= '<p>You are not a member of any team yet.</p>';
    }

    return $output;
});
