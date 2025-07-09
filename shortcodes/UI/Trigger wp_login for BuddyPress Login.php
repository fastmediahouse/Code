add_action('buddyboss_user_loggedin', function($user_id) {
    // Manually trigger wp_login for BuddyPress/BuddyBoss login
    do_action('wp_login', get_userdata($user_id)->user_login, get_userdata($user_id));
}, 10, 1);
add_action('wp_login', function($user_login, $user) {
    error_log('wp_login fired for user: ' . $user_login); // Debug line
}, 10, 2);

