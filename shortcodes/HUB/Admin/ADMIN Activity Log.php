// ✅ Shortcode: [fm_admin_activity_log]
// Shows recent uploads/downloads/edits/deletions in HUB

add_shortcode('fm_admin_activity_log', function () {
    if (!current_user_can('administrator')) {
        return '<p>Access denied.</p>';
    }

    $args = [
        'post_type'      => 'attachment',
        'posts_per_page' => 50,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ];

    $query = new WP_Query($args);

    ob_start();
    ?>
    <style>
    .admin-activity-log table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    .admin-activity-log th, .admin-activity-log td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    .admin-activity-log th {
        background-color: #f8f8f8;
        font-weight: bold;
    }
    .admin-activity-log td small {
        color: #666;
    }
    </style>

    <div class="admin-activity-log">
        <table>
            <thead>
                <tr>
                    <th>🧍‍♂️ User</th>
                    <th>📁 Action</th>
                    <th>🖼️ Asset</th>
                    <th>⏱️ Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->have_posts()) :
                    while ($query->have_posts()) :
                        $query->the_post();
                        $user_id = get_post_field('post_author', get_the_ID());
                        $user = get_userdata($user_id);
                        $username = $user ? $user->display_name : 'Unknown';
                        $action = get_post_status() === 'inherit' ? 'Uploaded' : get_post_status();
                        $title = get_the_title();
                        $time = get_the_modified_time('Y-m-d H:i');
                        ?>
                        <tr>
                            <td><?= esc_html($username) ?></td>
                            <td><?= esc_html(ucfirst($action)) ?></td>
                            <td><?= esc_html($title) ?></td>
                            <td><small><?= esc_html($time) ?></small></td>
                        </tr>
                    <?php endwhile;
                    wp_reset_postdata();
                else : ?>
                    <tr><td colspan="4">No activity found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
});
