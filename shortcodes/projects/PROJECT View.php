// ✅ LIGHTBOX VIEW WITH REMOVE, RENAME + IMAGE LOAD FIX
add_shortcode('lightbox_view', function () {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/signin/">sign in</a> to view this lightbox.</p>';
    }

    $user_id = get_current_user_id();
    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : [];

    $folder = isset($_GET['folder']) ? sanitize_text_field($_GET['folder']) : '';
    if (!$folder || !isset($folders[$folder])) {
        return '<p>Invalid or missing folder.</p>';
    }

    $image_ids = $folders[$folder];
    $notes = get_user_meta($user_id, 'solwee_favorites_notes', true);
    $note = $notes[$folder] ?? '';

    ob_start();
    ?>
    <div class="solwee-lightbox-view">
        <div class="lightbox-name-row">
            <h2 id="lightbox-title">Viewing: <span contenteditable="true" id="editable-folder-name"><?php echo esc_html($folder); ?></span></h2>
            <button class="sharp-btn" onclick="renameLightboxFolder()">Save Name</button>
        </div>

        <div class="solwee-lightbox-actions">
            <button class="sharp-btn" onclick="selectAllImages()">Select All</button>
            <button class="sharp-btn" onclick="removeSelectedImages()">Remove Selected</button>

            <select id="move-folder">
                <option value="">Move to...</option>
                <?php foreach ($folders as $key => $_): if ($key !== $folder): ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
                <?php endif; endforeach; ?>
                <option value="__new__">+ Create New Folder</option>
            </select>
            <button class="sharp-btn" onclick="moveSelectedImages()">Move</button>

            <select id="copy-folder">
                <option value="">Copy to...</option>
                <?php foreach ($folders as $key => $_): if ($key !== $folder): ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
                <?php endif; endforeach; ?>
                <option value="__new__">+ Create New Folder</option>
            </select>
            <button class="sharp-btn" onclick="copySelectedImages()">Copy</button>
        </div>

        <div style="margin-bottom: 10px;">
            <textarea id="lightbox-note" placeholder="Add a note for this lightbox..." style="width:100%;padding:10px;border-radius:4px;border:1px solid #ccc;"><?php echo esc_textarea($note); ?></textarea>
            <button class="sharp-btn" onclick="saveLightboxNote()">Save Note</button>
            <div id="note-status" style="margin-top: 5px; font-size: 13px;"></div>
        </div>

        <div class="solwee-lightbox-grid">
            <?php foreach ($image_ids as $id): if ($id): ?>
                <div class="solwee-lightbox-tile">
                    <a href="/image-detail/?productID=<?php echo urlencode($id); ?>" target="_blank">
                        <img src="<?php echo esc_url(home_url('/?solwee_image_proxy=' . $id)); ?>"
                             alt="Image <?php echo esc_attr($id); ?>"
                             onerror="this.onerror=null;this.src='/no-preview.jpg';"
                             loading="lazy" />
                    </a>
                    <div class="solwee-lightbox-meta">
                        <label>
                            <input type="checkbox" class="lightbox-image-select" value="<?php echo esc_attr($id); ?>">
                            Select
                        </label>
                        <div class="image-id">ID: <?php echo esc_html($id); ?></div>
                    </div>
                </div>
            <?php endif; endforeach; ?>
        </div>
    </div>

    <style>
        .solwee-lightbox-view { max-width: 1200px; margin: 0 auto; }
        .lightbox-name-row { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        .solwee-lightbox-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .solwee-lightbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .solwee-lightbox-tile { background: #f9f9f9; padding: 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; }
        .solwee-lightbox-tile img { width: 100%; height: 160px; object-fit: cover; border-radius: 4px; }
        .solwee-lightbox-meta { margin-top: 8px; font-size: 13px; }
        #editable-folder-name { border: 1px solid #333; padding: 2px 6px; border-radius: 0; display: inline-block; }
        .sharp-btn { background: black; color: white; border: 1px solid black; border-radius: 0; padding: 6px 12px; cursor: pointer; font-weight: 500; }
    </style>

    <script>
    function getSelectedIDs() {
        return Array.from(document.querySelectorAll('.lightbox-image-select:checked')).map(cb => cb.value);
    }

    function selectAllImages() {
        document.querySelectorAll('.lightbox-image-select').forEach(cb => cb.checked = true);
    }

    function removeSelectedImages() {
        const selected = getSelectedIDs();
        const folder = <?php echo json_encode($folder); ?>;
        if (selected.length && confirm('Are you sure you want to remove these images?')) {
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'solwee_remove_from_lightbox',
                    productIDs: selected.join(','),
                    folder: folder
                })
            }).then(() => location.reload());
        }
    }

    function moveSelectedImages() {
        const selected = getSelectedIDs();
        let newFolder = document.getElementById('move-folder').value;
        if (newFolder === '__new__') newFolder = prompt('Enter name for new folder:');
        if (newFolder && selected.length) {
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'solwee_move_images_to_folder',
                    from: <?php echo json_encode($folder); ?>,
                    to: newFolder,
                    ids: selected.join(',')
                })
            }).then(() => location.reload());
        }
    }

    function copySelectedImages() {
        const selected = getSelectedIDs();
        let newFolder = document.getElementById('copy-folder').value;
        if (newFolder === '__new__') newFolder = prompt('Enter name for new folder:');
        if (newFolder && selected.length) {
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'solwee_copy_images_to_folder',
                    to: newFolder,
                    ids: selected.join(',')
                })
            }).then(() => location.reload());
        }
    }

    function saveLightboxNote() {
        const note = document.getElementById('lightbox-note').value;
        const folder = <?php echo json_encode($folder); ?>;
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'solwee_save_lightbox_note',
                folder,
                note
            })
        }).then(res => res.json()).then(res => {
            document.getElementById('note-status').textContent = res.success ? 'Note saved.' : 'Error saving note.';
        });
    }

    function renameLightboxFolder() {
        const newName = document.getElementById('editable-folder-name').innerText.trim();
        const oldName = <?php echo json_encode($folder); ?>;
        if (!newName || newName === oldName) return;
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'solwee_rename_lightbox',
                old: oldName,
                new: newName
            })
        }).then(() => location.href = '/lightbox-view/?folder=' + encodeURIComponent(newName));
    }
    </script>
    <?php
    return ob_get_clean();
});

// ✅ REMOVE HANDLER
add_action('wp_ajax_solwee_remove_from_lightbox', function () {
    if (!is_user_logged_in()) wp_send_json_error();
    $user_id = get_current_user_id();
    $folder = sanitize_text_field($_POST['folder'] ?? '');
    $ids = explode(',', sanitize_text_field($_POST['productIDs'] ?? ''));

    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    if (!isset($folders[$folder])) wp_send_json_error();

    $folders[$folder] = array_values(array_diff($folders[$folder], $ids));
    update_user_meta($user_id, 'solwee_favorites_folders', $folders);
    wp_send_json_success();
});
