// ✅ 2B MEMBERSHIP Invite System

// Create custom invite table on plugin/theme activation
register_activation_hook(__FILE__, 'fm_create_invite_table');
function fm_create_invite_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'fm_invites';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        team_id VARCHAR(100) NOT NULL,
        token VARCHAR(64) NOT NULL,
        invited_by BIGINT UNSIGNED NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Generate a secure invite token
function fm_generate_invite_token($email) {
    return sha1($email . time() . wp_generate_password(12, false));
}

// Handle invite form submission (frontend shortcode)
add_shortcode('fm_invite_user_form', function () {
    if (!current_user_can('team_admin')) return '<p>You do not have permission to invite users.</p>';

    $output = '';

    if (!empty($_POST['fm_invite_email'])) {
        global $wpdb;
        $email = sanitize_email($_POST['fm_invite_email']);
        $user_id = get_current_user_id();
        $team_id = get_user_meta($user_id, 'team_id', true);

        if (!$team_id) return '<p>You are not assigned to a team.</p>';

        // Check team capacity
        $team_post = get_page_by_path($team_id, OBJECT, 'fm_team');
        $max_seats = (int)get_post_meta($team_post->ID, 'max_seats', true);
        $members = fm_get_team_member_count($team_id);
        if ($members >= $max_seats) {
            return '<p>Your team has reached its seat limit.</p>';
        }

        $token = fm_generate_invite_token($email);
        $wpdb->insert($wpdb->prefix . 'fm_invites', [
            'email' => $email,
            'team_id' => $team_id,
            'token' => $token,
            'invited_by' => $user_id
        ]);

        // Send email
        $invite_url = site_url('/accept-invite/?token=' . $token);
        wp_mail($email, 'You\'re invited to join a Fast Media team', "Click to join: $invite_url");

        $output .= '<p>Invite sent to ' . esc_html($email) . '</p>';
    }

    $output .= '<form method="POST">';
    $output .= '<label for="fm_invite_email">Invite Email:</label><br>';
    $output .= '<input type="email" name="fm_invite_email" required style="width: 100%; max-width: 400px;"><br>';
    $output .= '<button type="submit">Send Invite</button>';
    $output .= '</form>';

    return $output;
});

// Count members by team_id
function fm_get_team_member_count($team_id) {
    $users = get_users([
        'meta_key' => 'team_id',
        'meta_value' => $team_id
    ]);
    return count($users);
}

// Handle invite token page (register or auto-assign)
add_action('init', function () {
    if (!isset($_GET['token']) || !is_page('accept-invite')) return;

    global $wpdb;
    $token = sanitize_text_field($_GET['token']);
    $invite = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fm_invites WHERE token = %s", $token));

    if (!$invite || $invite->status !== 'pending') {
        wp_die('Invalid or expired invite.');
    }

    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
        exit;
    }

    $user_id = get_current_user_id();
    update_user_meta($user_id, 'team_id', $invite->team_id);
    update_user_meta($user_id, 'team_role', 'member');
    wp_update_user(['ID' => $user_id, 'role' => 'hub_user']);

    $wpdb->update($wpdb->prefix . 'fm_invites', ['status' => 'accepted'], ['id' => $invite->id]);

    wp_safe_redirect(home_url('/hub/'));
    exit;
});
