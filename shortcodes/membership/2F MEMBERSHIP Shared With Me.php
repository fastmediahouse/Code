add_shortcode('fastmedia_shared_with_me', function () {
    if (!is_user_logged_in()) return '<p>Please log in to view shared assets.</p>';

    $user_id = get_current_user_id();

    // Query attachments with 'shared_with' including current user ID
    $args = [
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'shared_with',
                'value' => '"' . $user_id . '"',
                'compare' => 'LIKE'
            ]
        ]
    ];

    $shared_items = get_posts($args);

    ob_start();
    echo '<h3>🔗 Assets Shared With You</h3>';

    if (empty($shared_items)) {
        echo '<p>No assets have been shared with you yet.</p>';
    } else {
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;">';
        foreach ($shared_items as $item) {
            $thumb = wp_get_attachment_image_src($item->ID, 'medium');
            if (!$thumb) continue;

            echo '<div style="border:1px solid #ccc;padding:10px;border-radius:8px;text-align:center;background:#fff;">';
            echo '<a href="' . esc_url(wp_get_attachment_url($item->ID)) . '" target="_blank">';
            echo '<img src="' . esc_url($thumb[0]) . '" style="max-width:100%;height:auto;border-radius:4px;">';
            echo '</a>';
            echo '<div style="margin-top:8px;font-size:13px;">ID: ' . esc_html($item->ID) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    return ob_get_clean();
});
