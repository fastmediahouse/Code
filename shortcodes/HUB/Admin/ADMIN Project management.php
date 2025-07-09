add_shortcode('fm_admin_project_monitor', function () {
    if (!current_user_can('administrator')) return '<p>Access denied.</p>';

    $user_id = get_current_user_id();
    $owners = get_users(['fields' => ['ID', 'display_name']]);

    $selected_owner = isset($_GET['owner']) ? intval($_GET['owner']) : 0;

    // Fetch lightboxes from user meta
    $lightboxes = [];
    foreach ($owners as $user) {
        $folders = get_user_meta($user->ID, 'solwee_favorites_folders', true);
        if (!$folders || !is_array($folders)) continue;

        foreach ($folders as $folder => $ids) {
            if ($selected_owner && $user->ID !== $selected_owner) continue;

            $lightboxes[] = [
                'title' => $folder,
                'owner' => $user->display_name,
                'owner_id' => $user->ID
            ];
        }
    }

    ob_start();
    ?>
    <style>
    .admin-project-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 20px;
        margin-top: 16px;
    }
    .admin-project-card {
        border: 1px solid #ccc;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .admin-project-card h3 {
        margin: 0;
        font-size: 18px;
    }
    .admin-project-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    .admin-project-actions button {
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }
    .lock-btn {
        background: #3c3c3c;
        color: #fff;
    }
    .review-btn {
        background: #007bff;
        color: #fff;
    }
    .contact-btn {
        background: #f1f1f1;
        color: #333;
        border: 1px solid #ccc;
    }
    </style>

    <h2>📁 Project Monitor</h2>
    <p>Keep an overview of the different project folders</p>

    <form method="get" style="margin-bottom: 16px;">
        <label for="owner">Filter by owner: </label>
        <select name="owner" onchange="this.form.submit()">
            <option value="0">All Owners</option>
            <?php foreach ($owners as $owner): ?>
                <option value="<?= $owner->ID ?>" <?= $selected_owner === $owner->ID ? 'selected' : '' ?>>
                    <?= esc_html($owner->display_name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="admin-project-grid">
        <?php foreach ($lightboxes as $lightbox): ?>
            <div class="admin-project-card">
                <h3><?= esc_html($lightbox['title']) ?></h3>
                <p>👤 <?= esc_html($lightbox['owner']) ?></p>
                <div class="admin-project-actions">
                    <button class="contact-btn" title="Contact project owner">📧 Contact</button>
                    <button class="lock-btn">🔒 Lock</button>
                    <a class="review-btn" href="/lightbox/?folder=<?= urlencode($lightbox['title']) ?>&user=<?= $lightbox['owner_id'] ?>" target="_blank">▶️ Review</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});
