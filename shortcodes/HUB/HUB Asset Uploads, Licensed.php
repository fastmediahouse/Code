/**
 * ✅ HUB Asset Uploads, Licensed
 * Version: 2025-06-29
 * Shortcodes:
 * - [fastmeduploaded]
 * - [fastmedia_purchased_assets]
 * - [fastmedia_assets_my_assets]
 */

function render_fastmedia_grid($attachments, $badge_type = 'UP') {
    $folder_keys = ['Default'];
    $saved_ids = [];
    $folder_json = json_encode($folder_keys);
    $saved_ids_json = json_encode($saved_ids);

    ob_start();
    ?>
    <style>
    .fastmedia-bulkbar button.icon {
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        font-size: 20px;
        margin-right: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .fastmedia-bulkbar button.icon:hover { background: #f0f0f0; }
    .fastmedia-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }
    .fastmedia-tile {
        position: relative;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px;
        font-size: 14px;
    }
    .fastmedia-tile:hover { box-shadow: 0 0 6px rgba(0,0,0,0.15); }
    .fastmedia-tile img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    .fastmedia-checkbox {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 3;
        width: 32px;
        height: 32px;
    }
    .fastmedia-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        z-index: 4;
    }
    .fastmedia-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 4px;
        margin-top: 8px;
    }
    .fastmedia-toolbar button,
    .fastmedia-toolbar a {
        background: white;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        padding: 6px;
        min-width: 32px;
        text-align: center;
    }
    .brand-action-button {
        margin-top: 10px;
        width: 100%;
        padding: 8px 12px;
        background: white;
        color: black;
        border: 2px solid black;
        font-size: 14px;
        border-radius: 6px;
        cursor: pointer;
    }
    .badge {
        position: absolute;
        top: 10px;
        left: 50px;
        background: #007bff;
        color: #fff;
        font-size: 13px;
        font-weight: bold;
        padding: 6px 10px;
        border-radius: 4px;
        z-index: 3;
        opacity: 0.95;
    }
    </style>

    <div class="fastmedia-grid">
        <?php foreach ($attachments as $attachment):
            $id = is_object($attachment) ? $attachment->ID : $attachment;
            $thumb = wp_get_attachment_image_src($id, 'medium');
            $url = $thumb ? $thumb[0] : 'https://placehold.co/400x300?text=Preview';
            $title = esc_html(get_the_title($id));
            $date = get_the_date('', $id);
            $alt = esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true));
            $badge_label = $badge_type;
            $badge_title = ($badge_type === 'ST') ? 'Syndicated' : 'User Upload';

            $is_approved = get_post_meta($id, 'fastmedia_brand_approved', true) === 'yes';
            $is_proposed = get_post_meta($id, 'fastmedia_brand_proposed', true) === 'yes';
        ?>
        <div class="fastmedia-tile" data-asset-id="<?= $id ?>">
            <input type="checkbox" class="fastmedia-checkbox" title="Select image">
            <div class="fastmedia-actions"></div>
            <div class="badge" title="<?= $badge_title ?>"><?= $badge_label ?></div>
            <a href="/asset-detail/?id=<?= esc_attr($id) ?>">
            <img src="<?= esc_url($url) ?>" alt="<?= esc_attr($alt) ?>" />
</a>
            <strong><?= $title ?></strong><br>
            <small><?= $date ?></small>

            <!-- ✅ PROJECT toggle added -->
            <?= fastmedia_project_toggle_ui($id) ?>

           <div class="fastmedia-toolbar">
           <button title="Share" onclick="copyShareLink(<?= $id ?>)">🔗</button>
           <a href="/asset-detail/?id=<?= esc_attr($id) ?>#highres" title="Highres">⬇️</a>
           <a href="<?= esc_url($url) ?>" target="_blank" title="Download comp">📥</a>
           <a href="/asset-detail/?id=<?= esc_attr($id) ?>" title="Edit">✏️</a>
           <button class="delete-button" title="Delete">🗑️</button>
</div>

            <?php if ($is_approved): ?>
                <button class="brand-action-button" disabled>✅ Added to Brand</button>
            <?php elseif ($is_proposed): ?>
                <button class="brand-action-button" disabled>🕒 Proposed to Admin</button>
            <?php else: ?>
                <button class="brand-action-button">➕ Add to Brand Assets</button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.brand-action-button').forEach(btn => {
        btn.addEventListener('click', function () {
            const tile = btn.closest('.fastmedia-tile');
            const assetId = tile?.dataset.assetId;
            if (!assetId || btn.disabled) return;

            btn.disabled = true;
            btn.textContent = '⏳ Sending...';

            fetch('/wp-admin/admin-ajax.php?action=fastmedia_add_to_brand_assets', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'asset_id=' + encodeURIComponent(assetId)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success === true) {
                    btn.textContent = res.data === 'approved' ? '✅ Added to Brand' : '🕒 Proposed to Admin';
                } else {
                    btn.textContent = '⚠️ Failed';
                }
            })
            .catch(() => {
                btn.textContent = '⚠️ Error';
            });
        });
    });

    // 🔴 Delete button handler
    document.querySelectorAll('.delete-button').forEach(btn => {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';
        btn.addEventListener('click', function () {
            const tile = btn.closest('.fastmedia-tile');
            const assetId = tile?.dataset.assetId;
            if (!assetId || !confirm('Are you sure you want to delete this asset?')) return;

            fetch('/wp-admin/admin-ajax.php?action=fastmedia_delete_asset', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'asset_id=' + encodeURIComponent(assetId)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success === true) {
                    tile.remove();
                } else {
                    alert('Delete failed.');
                }
            })
            .catch(() => alert('Delete failed.'));
        });
    });
});
</script>

<?php
    return ob_get_clean();
}

// 🔵 SHORTCODE 1: [fastmedia_uploaded]
add_shortcode('fastmedia_uploaded', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a>.</p>';
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $first = $user ? esc_html(get_user_meta($user_id, 'first_name', true)) : '';
    $last = $user ? esc_html(get_user_meta($user_id, 'last_name', true)) : '';
    $full_name = trim($first . ' ' . $last);
    echo '<div style="background:#f8f9ff;padding:12px;margin-bottom:10px;border:1px solid #ccd;"><strong>Viewing uploaded assets for:</strong> ' . ($full_name ?: 'Unnamed User') . '</div>';

    $attachments = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'author'         => $user_id,
        'post_mime_type' => 'image',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [[
            'key'     => 'fastmedia_upload_status',
            'compare' => 'NOT EXISTS'
        ]]
    ]);
    return render_fastmedia_grid($attachments, 'UP');
});
// 🔀 SHORTCODE 2: [fastmedia_purchased_assets]
add_shortcode('fastmedia_purchased_assets', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a>.</p>';

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $first = $user ? esc_html(get_user_meta($user_id, 'first_name', true)) : '';
    $last = $user ? esc_html(get_user_meta($user_id, 'last_name', true)) : '';
    $full_name = trim($first . ' ' . $last);
    echo '<div style="background:#f8f9ff;padding:12px;margin-bottom:10px;border:1px solid #ccd;"><strong>Viewing licensed assets for:</strong> ' . ($full_name ?: 'Unnamed User') . '</div>';

    $args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'     => 'fastmedia_licensed',
                'value'   => 'yes',
                'compare' => '='
            ],
            [
                'key'     => 'fastmedia_buyer_id',
                'value'   => $user_id,
                'compare' => '='
            ]
        ]
    ];

    $query = new WP_Query($args);
    $attachments = [];

    foreach ($query->posts as $post) {
        $thumb = wp_get_attachment_image_src($post->ID, 'medium');
        $attachments[] = (object)[
            'ID'    => $post->ID,
            'title' => esc_html(get_the_title($post->ID)),
            'thumb' => $thumb ? $thumb[0] : 'https://placehold.co/400x300?text=Preview',
            'detail_url' => '/hub-asset-detail/?id=' . $post->ID,
        ];
    }

    return render_fastmedia_grid($attachments, 'ST');
});

// 🔀 SHORTCODE 3: [fastmedia_assets_my_assets]
add_shortcode('fastmedia_assets_my_assets', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a>.</p>';
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $first = $user ? esc_html(get_user_meta($user_id, 'first_name', true)) : '';
    $last = $user ? esc_html(get_user_meta($user_id, 'last_name', true)) : '';
    $full_name = trim($first . ' ' . $last);
    echo '<div style="background:#f8f9ff;padding:12px;margin-bottom:10px;border:1px solid #ccd;"><strong>Viewing all personal assets for:</strong> ' . ($full_name ?: 'Unnamed User') . '</div>';

    return do_shortcode('[fastmedia_uploaded]') . '<hr style="margin:40px 0;">' . do_shortcode('[fastmedia_purchased_assets]');
});
