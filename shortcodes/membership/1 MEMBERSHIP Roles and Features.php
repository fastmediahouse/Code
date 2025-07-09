// ✅ FM Membership Roles and Features

// 1. Define custom roles on activation
function fm_register_roles() {
    add_role('hub_user', 'HUB User', ['read' => true]);
    add_role('team_admin', 'Team Admin', ['read' => true, 'upload_files' => true]);
    add_role('team_member', 'Team Member', ['read' => true]);
}
register_activation_hook(__FILE__, 'fm_register_roles');

// 2. Membership Benefits by Plan
function fm_get_membership_benefits() {
    return [
        'free' => [
            'search' => true,
            'magic_search' => false,
            'download' => true,
            'upload' => false,
            'share' => false,
            'message' => false,
            'forums' => true,
            'create_groups' => false,
            'annotation' => false,
            'storage_limit' => 0,
            'team_size' => 0
        ],
        'premium' => [
            'search' => true,
            'magic_search' => true,
            'download' => true,
            'upload' => false,
            'share' => false,
            'message' => true,
            'forums' => true,
            'create_groups' => false,
            'annotation' => false,
            'storage_limit' => 0,
            'team_size' => 0
        ],
        'hub_single' => [
            'search' => true,
            'magic_search' => true,
            'download' => true,
            'upload' => true,
            'share' => false,
            'message' => true,
            'forums' => true,
            'create_groups' => false,
            'annotation' => false,
            'storage_limit' => 5000,
            'team_size' => 1
        ],
        'hub_team_5' => [
            'search' => true,
            'magic_search' => true,
            'download' => true,
            'upload' => true,
            'share' => true,
            'message' => true,
            'forums' => true,
            'create_groups' => true,
            'annotation' => false,
            'storage_limit' => 20000,
            'team_size' => 5
        ],
        'hub_team_10' => [
            'search' => true,
            'magic_search' => true,
            'download' => true,
            'upload' => true,
            'share' => true,
            'message' => true,
            'forums' => true,
            'create_groups' => true,
            'annotation' => false,
            'storage_limit' => 50000,
            'team_size' => 10
        ],
        'hub_team_25' => [
            'search' => true,
            'magic_search' => true,
            'download' => true,
            'upload' => true,
            'share' => true,
            'message' => true,
            'forums' => true,
            'create_groups' => true,
            'annotation' => false,
            'storage_limit' => 100000,
            'team_size' => 25
        ],
        'enterprise' => [
            'search' => true,
            'magic_search' => true,
            'download' => true,
            'upload' => true,
            'share' => true,
            'message' => true,
            'forums' => true,
            'create_groups' => true,
            'annotation' => true,
            'storage_limit' => 999999,
            'team_size' => 999
        ]
    ];
}

// 3. Feature access check for current user
function fm_user_has_feature($feature) {
    $user_id = get_current_user_id();
    if (!$user_id) return false;

    $level = pmpro_getMembershipLevelForUser($user_id);
    if (!$level) return false;

    $benefits = fm_get_membership_benefits();
    $level_key = strtolower($level->name);

    return $benefits[$level_key][$feature] ?? false;
}

// ✅ Step 2A: Register Team CPT and Meta
add_action('init', 'fm_register_team_cpt');
function fm_register_team_cpt() {
    register_post_type('fm_team', [
        'labels' => [
            'name' => 'Teams',
            'singular_name' => 'Team'
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title'],
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 30,
        'show_in_menu' => true
    ]);
}

add_action('add_meta_boxes', function() {
    add_meta_box('fm_team_meta', 'Team Settings', 'fm_team_meta_box', 'fm_team', 'normal', 'default');
});

function fm_team_meta_box($post) {
    $fields = [
        'team_id' => 'Team ID (slug)',
        'max_seats' => 'Maximum Seats',
        'storage_limit_mb' => 'Storage Limit (MB)',
        'storage_used_mb' => 'Storage Used (MB)',
        'team_admin_id' => 'Team Admin (User ID)'
    ];
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        echo "<p><label for='$key'><strong>$label:</strong></label><br><input type='text' name='$key' value='" . esc_attr($value) . "' class='widefat' /></p>";
    }
}

add_action('save_post_fm_team', function($post_id) {
    $keys = ['team_id', 'max_seats', 'storage_limit_mb', 'storage_used_mb', 'team_admin_id'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
        }
    }
});
