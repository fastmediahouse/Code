/**
 * ✅ HUB Brand Assets Page – Updated Layout + User Filtering
 * Shortcode: [fastmedia_brand_assets]
 * Date: 2025-06-29
 */

add_shortcode('fastmedia_brand_assets', function () {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/signin/">sign in</a> to view your brand assets.</p>';
    }

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $first = $user ? esc_html(get_user_meta($user_id, 'first_name', true)) : '';
    $last = $user ? esc_html(get_user_meta($user_id, 'last_name', true)) : '';
    $full_name = trim($first . ' ' . $last);

    echo '<div style="background:#f8f9ff;padding:12px;margin-bottom:10px;border:1px solid #ccd;"><strong>Viewing brand assets for:</strong> ' . ($full_name ?: 'Unnamed User') . '</div>';

    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : ['Default' => []];
    $folder_keys = array_keys($folders);
    $saved_ids = [];
    foreach ($folders as $f => $ids) foreach ($ids as $id) $saved_ids[$f][$id] = true;

    $paged = max(1, intval($_GET['paged'] ?? 1));
    $per_page = in_array($_GET['per_page'] ?? '', ['50','100','250']) ? intval($_GET['per_page']) : 50;
    $offset = ($paged - 1) * $per_page;

    $args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => $per_page,
        'offset'         => $offset,
        'post_mime_type' => 'image',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'author'         => $user_id,
        'meta_query'     => [
            [
                'key'   => 'fastmedia_brand_approved',
                'value' => 'yes',
                'compare' => '='
            ]
        ]
    ];
    $attachments = get_posts($args);
    $total = count(get_posts(array_merge($args, ['posts_per_page' => -1, 'offset' => 0])));

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

    <form method="get" class="fastmedia-bulkbar">
        <label>Images per page:
            <select name="per_page" onchange="this.form.submit()">
                <option value="50" <?= selected($per_page, 50, false) ?>>50</option>
                <option value="100" <?= selected($per_page, 100, false) ?>>100</option>
                <option value="250" <?= selected($per_page, 250, false) ?>>250</option>
            </select>
        </label>
        <button type="button" class="icon" onclick="fastmediaSelectAll(true)" title="Select All">✔️</button>
        <button type="button" class="icon" onclick="fastmediaSelectAll(false)" title="Deselect All">❌</button>
        <button type="button" class="icon" onclick="bulkDownload('comp')" title="Download Comp">📥</button>
        <button type="button" class="icon" onclick="bulkDownload('highres')" title="Download Highres">⬇️</button>
        <button type="button" class="icon" onclick="bulkShare()" title="Share">🔗</button>
        <button type="button" class="icon" onclick="bulkDelete()" title="Delete">🗑️</button>
    </form>

    <div class="fastmedia-grid">
        <?php foreach ($attachments as $attachment):
            $id = $attachment->ID;
            $thumb = wp_get_attachment_image_src($id, 'medium');
            $url = $thumb ? $thumb[0] : 'https://placehold.co/400x300?text=Preview';
            $title = esc_html(get_the_title($id));
            $date = get_the_date('', $id);
            $alt = esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true));
        ?>
        <div class="fastmedia-tile" data-asset-id="<?= $id ?>">
            <input type="checkbox" class="fastmedia-checkbox" title="Select image">
            <div class="fastmedia-actions"></div>
            <div class="badge" title="Brand Asset">BR</div>
            <a href="/asset-detail/?id=<?= $id ?>">
                <img src="<?= esc_url($url) ?>" alt="<?= $alt ?>" />
            </a>
            <strong><?= $title ?></strong><br>
            <small><?= $date ?></small>

            <!-- ✅ PROJECT toggle added -->
            <?= fastmedia_project_toggle_ui($id) ?>

            <div class="fastmedia-toolbar">
                <button onclick="copyShareLink(<?= $id ?>)" title="Share">🔗</button>
                <a href="/asset-detail/?id=<?= $id ?>#highres" title="Highres">⬇️</a>
                <a href="<?= esc_url($url) ?>" target="_blank" title="Download comp" onclick="logActivity(<?= $id ?>, 'Downloaded comp')">📥</a>
                <a href="/asset-detail/?id=<?= $id ?>" title="Edit">✏️</a>
                <button onclick="if(confirm('Delete this asset?')) logActivity(<?= $id ?>, 'Deleted')" title="Delete">🗑️</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="fastmedia-pagination">
        <?php
        echo paginate_links([
            'total'   => ceil($total / $per_page),
            'current' => $paged,
            'format'  => '?paged=%#%&per_page=' . $per_page,
        ]);
        ?>
    </div>

    <script>
    const userFolders = <?= $folder_json ?>;
    const savedIDs = <?= $saved_ids_json ?>;

    function fastmediaSelectAll(state) {
        document.querySelectorAll('.fastmedia-checkbox').forEach(cb => cb.checked = state);
    }

    function copyShareLink(id) {
        const url = `${location.origin}/asset-detail/?id=${id}`;
        navigator.clipboard.writeText(url).then(() => alert('Link copied to clipboard'));
    }

    function logActivity(assetId, action) {
        fetch('/wp-admin/admin-ajax.php?action=fastmedia_log_activity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `asset_id=${assetId}&action_text=${encodeURIComponent(action)}`
        });
    }

    function bulkDownload(type) {
        alert(`Feature in progress: bulk download (${type}).`);
    }

    function bulkShare() {
        alert('Feature in progress: bulk share.');
    }

    function bulkDelete() {
        alert('Feature in progress: bulk delete.');
    }
    </script>

    <?php
    return ob_get_clean();
});
