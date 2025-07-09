// ✅ Generate Solwee password and store on registration
add_action('user_register', function($user_id) {
    $user = get_userdata($user_id);
    $username = $user->user_login;

    // Generate Solwee password (adjust format if needed)
    $random_part = substr(md5(uniqid(mt_rand(), true)), 0, 8);
    $solwee_password = 'fm_' . $user_id . '_' . $random_part;

    // Save to user meta
    update_user_meta($user_id, 'solwee_password', $solwee_password);

    // Optional: Log to admin
    error_log("Solwee password for {$username}: {$solwee_password}");
}, 10, 1);

// ✅ On WP login, log into Solwee and store token (use username!)
add_action('wp_login', function($user_login, $user) {
    $username = $user->user_login;
    $password = get_user_meta($user->ID, 'solwee_password', true);
    if (!$password) return;

    $response = wp_remote_post('https://api.solwee.com/api/v2/login', [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WebID' => '57'
        ],
        'body' => json_encode([
            'login' => $username,
            'password' => $password
        ]),
        'timeout' => 10
    ]);

    if (is_wp_error($response)) {
        update_user_meta($user->ID, 'solwee_error', $response->get_error_message());
        delete_user_meta($user->ID, 'solwee_token');
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $token = trim($body);

    if ($token && strpos($token, '.') !== false) {
        update_user_meta($user->ID, 'solwee_token', $token);
        update_user_meta($user->ID, 'solwee_token_time', time());
        delete_user_meta($user->ID, 'solwee_error');
    } else {
        update_user_meta($user->ID, 'solwee_error', $body);
        delete_user_meta($user->ID, 'solwee_token');
    }
}, 10, 2);
