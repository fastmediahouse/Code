/**
 * ✅ Fastmedia Project Toggle UI (Unified Favorite & Folder Picker)
 * Usage: echo fastmedia_project_toggle_ui($productID);
 */

function fastmedia_project_toggle_ui($productID) {
    if (!is_user_logged_in() || !$productID) return '';

    $user_id = get_current_user_id();
    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : ['Default' => []];
    $folder_keys = array_keys($folders);

    // Detect all folders the image is in
    $saved_in_folders = [];
    foreach ($folders as $folder => $ids) {
        if (in_array($productID, $ids)) {
            $saved_in_folders[] = $folder;
        }
    }

    ob_start();
    ?>
    <div class="fastmedia-project-toggle project-ui-inline" data-productid="<?= esc_attr($productID) ?>" data-saved='<?= json_encode($saved_in_folders) ?>'>
        <div class="project-toggle-row">
            <div class="toggle-icon<?= in_array('Default', $saved_in_folders) ? ' active' : '' ?>" title="Add/remove from Project">❤️</div>
            <select class="folder-picker">
                <option value="" disabled selected>Select</option>
                <?php foreach ($folder_keys as $folder): ?>
                    <option value="<?= esc_attr($folder) ?>" <?= selected($folder, 'Default') ?>><?= esc_html($folder) ?></option>
                <?php endforeach; ?>
                <option value="__new__">➕ Create New Project</option>
            </select>
        </div>
    </div>

    <style>
        .fastmedia-project-toggle.project-ui-inline {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-top: 8px;
        }
        .project-toggle-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .toggle-icon {
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .toggle-icon.active {
            background: rgba(0, 128, 0, 0.7);
        }
        .folder-picker {
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #fff;
            border: 1px solid #ccc;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.fastmedia-project-toggle').forEach(widget => {
            const productID = widget.dataset.productid;
            let savedIn = JSON.parse(widget.dataset.saved || '[]');
            const toggle = widget.querySelector('.toggle-icon');
            const select = widget.querySelector('.folder-picker');

            function updateToggleUI(isActive) {
                toggle.classList.toggle('active', isActive);
            }

            toggle.addEventListener('click', function () {
                const folder = select.value;
                if (!folder || folder === '__new__') return;
                const isActive = savedIn.includes(folder);
                const action = isActive ? 'solwee_remove_from_folder' : 'solwee_update_lightbox_foldered';

                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=${action}&productID=${encodeURIComponent(productID)}&folder=${encodeURIComponent(folder)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (isActive) {
                            savedIn = savedIn.filter(f => f !== folder);
                        } else {
                            if (!savedIn.includes(folder)) savedIn.push(folder);
                        }
                        updateToggleUI(savedIn.includes(folder));
                    }
                });
            });

            select.addEventListener('change', function () {
                if (select.value === '__new__') {
                    const name = prompt('Enter new project name');
                    if (!name) {
                        select.value = 'Default';
                        return;
                    }
                    fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=solwee_create_folder&name=${encodeURIComponent(name)}`
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            select.insertBefore(opt, select.lastElementChild);
                            select.value = name;
                            savedIn.push(name);
                            updateToggleUI(true);
                        } else {
                            alert('Failed to create project');
                            select.value = 'Default';
                        }
                    });
                } else {
                    updateToggleUI(savedIn.includes(select.value));
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// ✅ AJAX: Create new folder (project)
add_action('wp_ajax_solwee_create_folder', function () {
    if (!is_user_logged_in()) wp_send_json_error();
    $user_id = get_current_user_id();
    $name = sanitize_text_field($_POST['name'] ?? '');
    if (!$name) wp_send_json_error();

    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : [];
    if (!isset($folders[$name])) {
        $folders[$name] = [];
        update_user_meta($user_id, 'solwee_favorites_folders', $folders);
    }
    wp_send_json_success();
});

// ✅ AJAX: Remove image from folder (project)
add_action('wp_ajax_solwee_remove_from_folder', function () {
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

    if (isset($folders[$folder])) {
        $folders[$folder] = array_values(array_filter($folders[$folder], fn($id) => $id !== $productID));
        update_user_meta($user_id, 'solwee_favorites_folders', $folders);
        wp_send_json_success(['message' => "✅ Removed from folder: {$folder}"]);
    } else {
        wp_send_json_success(['message' => "✅ Folder not found, nothing to remove"]);
    }
});
