// AJAX handler: Add productID to folder in usermeta
add_action('wp_ajax_solwee_update_lightbox_foldered', function () {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $productID = sanitize_text_field($_POST['productID'] ?? '');
    $folder = sanitize_text_field($_POST['folder'] ?? 'Default');

    if (empty($productID)) {
        wp_send_json_error(['message' => 'No product ID provided']);
    }

    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : [];

    if (!isset($folders[$folder])) {
        $folders[$folder] = [];
    }

    if (!in_array($productID, $folders[$folder])) {
        $folders[$folder][] = $productID;
        update_user_meta($user_id, 'solwee_favorites_folders', $folders);
        wp_send_json_success(['message' => "✅ Added to folder: {$folder}"]);
    }

    wp_send_json_success(['message' => "✅ Already in folder: {$folder}"]);
});
