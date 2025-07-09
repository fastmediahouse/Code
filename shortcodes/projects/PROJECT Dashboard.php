add_shortcode('solwee_lightbox_dashboard', function () {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/signin/">sign in</a> to view your saved images.</p>';
    }

    $user_id = get_current_user_id();
    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : ['Default' => []];

    // ✅ Handle CSV Export EARLY to avoid headers already sent
    if (!empty($_POST['export_folder']) && isset($folders[$_POST['export_folder']])) {
        $name = sanitize_file_name($_POST['export_folder']) . '_images.csv';

        // Clear all output buffers
        while (ob_get_level()) ob_end_clean();

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$name\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo "productID\n" . implode("\n", $folders[$_POST['export_folder']]);
        exit;
    }

    // ✅ Handle delete
    if (!empty($_POST['delete_folder']) && isset($folders[$_POST['delete_folder']])) {
        unset($folders[$_POST['delete_folder']]);
        update_user_meta($user_id, 'solwee_favorites_folders', $folders);
        echo "<p style='color:red'>Folder deleted.</p><script>setTimeout(() => location.reload(), 1000)</script>";
    }

    // ✅ Handle rename
    if (!empty($_POST['rename_from']) && !empty($_POST['rename_to']) && isset($folders[$_POST['rename_from']])) {
        $from = sanitize_text_field($_POST['rename_from']);
        $to = sanitize_text_field($_POST['rename_to']);
        if (!isset($folders[$to])) {
            $folders[$to] = $folders[$from];
            unset($folders[$from]);
            update_user_meta($user_id, 'solwee_favorites_folders', $folders);
            echo "<script>location.href='/my-lightbox/'</script>";
        } else {
            echo "<p style='color:red'>Folder name already exists.</p>";
        }
    }

    ob_start();
    ?>
    <style>
        .solwee-lightbox-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        .solwee-folder-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .solwee-folder-header form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .solwee-folder-header input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .solwee-folder-header button {
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .solwee-folder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .solwee-folder-card {
            position: relative;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 10px;
            overflow: visible;
        }
        .solwee-folder-thumb-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 3px;
            height: 140px;
            overflow: hidden;
            border-radius: 6px;
            cursor: pointer;
        }
        .solwee-folder-thumb-grid img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        .solwee-folder-title {
            font-weight: bold;
            margin-top: 10px;
            font-size: 16px;
        }
        .solwee-folder-meta {
            font-size: 12px;
            color: #777;
        }
        .solwee-menu-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            color: #000;
            cursor: pointer;
        }
        .solwee-folder-menu {
            position: absolute;
            bottom: 45px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            min-width: 160px;
            padding: 6px 0;
        }
        .solwee-folder-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 16px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            color: #000;
            text-align: left;
        }
        .solwee-folder-menu form {
            margin: 0;
            padding: 0;
        }
        .solwee-folder-menu form button {
            all: unset;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 16px;
            font-size: 15px;
            cursor: pointer;
            color: #000;
        }
        .solwee-folder-menu button:hover,
        .solwee-folder-menu form button:hover {
            background: #f9f9f9;
        }
        .solwee-rename-form {
            display: none;
            margin-top: 10px;
            text-align: center;
        }
        .solwee-rename-form input {
            width: 80%;
            max-width: 160px;
            padding: 6px;
            margin-bottom: 8px;
        }
        .solwee-rename-form button {
            padding: 6px 12px;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>

    <script>
        function toggleRename(folderId) {
            document.querySelectorAll('.solwee-rename-form').forEach(f => f.style.display = 'none');
            const el = document.getElementById('rename-form-' + folderId);
            if (el) el.style.display = 'block';
        }

        function toggleMenu(el) {
            document.querySelectorAll('.solwee-folder-menu').forEach(menu => {
                if (menu !== el.nextElementSibling) menu.style.display = 'none';
            });
            const menu = el.nextElementSibling;
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        document.addEventListener('click', function (e) {
            if (!e.target.classList.contains('solwee-menu-btn')) {
                document.querySelectorAll('.solwee-folder-menu').forEach(m => m.style.display = 'none');
            }
        });
    </script>

    <div class="solwee-lightbox-content">
        <div class="solwee-folder-header">
            <h2>Your Saved Images</h2>
            <form method="post">
                <input type="text" name="new_folder" placeholder="New folder name..." required>
                <button type="submit">Create Folder</button>
            </form>
        </div>

        <div class="solwee-folder-grid">
            <?php foreach ($folders as $folder => $ids): ?>
                <div class="solwee-folder-card">
                    <div class="solwee-folder-thumb-grid" onclick="window.location.href='/lightbox-view/?folder=<?php echo urlencode($folder); ?>'">
                        <?php
                        $thumbs = array_slice($ids, 0, 4);
                        foreach ($thumbs as $id):
                            if (!$id) continue;
                            $image = esc_url(home_url("/?solwee_image_proxy=$id"));
                            echo "<img src='{$image}' alt='Preview for {$id}' onerror=\"this.style.display='none'\">";
                        endforeach;
                        ?>
                    </div>
                    <div class="solwee-folder-title"><?php echo esc_html($folder); ?></div>
                    <div class="solwee-folder-meta"><?php echo count($ids); ?> saved image<?php echo count($ids) === 1 ? '' : 's'; ?></div>

                    <button class="solwee-menu-btn" onclick="toggleMenu(this)">⋮</button>

                    <div class="solwee-folder-menu">
                        <button onclick="toggleRename('<?php echo md5($folder); ?>')">✏️ Rename</button>
                        <form method="post" onsubmit="return confirm('Delete this folder?');">
                            <input type="hidden" name="delete_folder" value="<?php echo esc_attr($folder); ?>">
                            <button type="submit">🗑️ Delete</button>
                        </form>
                        <button onclick="navigator.clipboard.writeText('<?php echo site_url('/lightbox-view/?folder=' . urlencode($folder)); ?>'); alert('Link copied!')">📤 Share</button>
                        <form method="post" target="solwee_export_frame">
                            <input type="hidden" name="export_folder" value="<?php echo esc_attr($folder); ?>">
                            <button type="submit">📦 Export</button>
                        </form>
                    </div>

                    <div id="rename-form-<?php echo md5($folder); ?>" class="solwee-rename-form">
                        <form method="post">
                            <input type="hidden" name="rename_from" value="<?php echo esc_attr($folder); ?>">
                            <input type="text" name="rename_to" value="<?php echo esc_attr($folder); ?>" required>
                            <button type="submit">Save</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_folder'])) {
            $new = sanitize_text_field($_POST['new_folder']);
            if (!isset($folders[$new])) {
                $folders[$new] = [];
                update_user_meta($user_id, 'solwee_favorites_folders', $folders);
                echo "<p style='color:green'>New folder '{$new}' created.</p>
                      <script>setTimeout(() => location.reload(), 1500)</script>";
            } else {
                echo "<p style='color:red'>Folder already exists.</p>";
            }
        }
        ?>
    </div>

    <iframe name="solwee_export_frame" style="display:none;"></iframe>
    <?php
    return ob_get_clean();
});
