// ✅ Shortcode: [fm_admin_asset_approvals]
// Shows pending brand assets + approve/reject buttons + handles status updates

add_shortcode('fm_admin_asset_approvals', function () {
    if (!current_user_can('administrator')) {
        return '<p>Access denied.</p>';
    }

    $args = [
        'post_type'      => 'attachment',
        'posts_per_page' => 100,
        'post_status'    => 'inherit',
        'meta_query'     => [
            [
                'key'     => 'brand_asset_status',
                'value'   => 'pending',
                'compare' => '='
            ]
        ]
    ];

    $assets = get_posts($args);
    if (!$assets) return '<p>No pending brand assets.</p>';

    ob_start();
    ?>
    <style>
    .brand-approvals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }
    .brand-approval-card {
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        text-align: center;
    }
    .brand-approval-card img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
    .approval-actions button {
        margin: 6px;
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .approve-btn { background: #4CAF50; color: white; }
    .reject-btn { background: #f44336; color: white; }
    </style>

    <div class="brand-approvals-grid">
        <?php foreach ($assets as $asset) :
            $img_url = wp_get_attachment_image_url($asset->ID, 'medium');
            $title = get_the_title($asset->ID);
            ?>
            <div class="brand-approval-card" id="asset_<?= $asset->ID ?>">
                <img src="<?= esc_url($img_url) ?>" alt="<?= esc_attr($title) ?>">
                <p><strong><?= esc_html($title) ?></strong></p>
                <div class="approval-actions">
                    <button class="approve-btn" onclick="handleApproval(<?= $asset->ID ?>, 'approved')">✅ Approve</button>
                    <button class="reject-btn" onclick="handleApproval(<?= $asset->ID ?>, 'rejected')">❌ Reject</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    function handleApproval(id, status) {
        fetch("<?= admin_url('admin-ajax.php') ?>", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'fm_update_asset_status',
                asset_id: id,
                status: status
            })
        }).then(() => {
            document.getElementById('asset_' + id)?.remove();
        });
    }
    </script>
    <?php
    return ob_get_clean();
});

// ✅ AJAX handler to update asset status meta
add_action('wp_ajax_fm_update_asset_status', function () {
    if (!current_user_can('administrator')) wp_die();
    $id = intval($_POST['asset_id']);
    $status = sanitize_text_field($_POST['status']);
    update_post_meta($id, 'brand_asset_status', $status);
    wp_die();
});
