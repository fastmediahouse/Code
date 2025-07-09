// ✅ Updated shortcode: fastmedia_upload_panel – with activity log reinstated
add_shortcode('fastmedia_upload_panel', function () {
    if (!is_user_logged_in()) return '<p>Please log in to upload and approve images.</p>';
    $user_id = get_current_user_id();

    // ✅ Upload handler
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['fastmedia_upload_file'])) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        foreach ($_FILES['fastmedia_upload_file']['name'] as $i => $name) {
            $file_array = [
                'name'     => $_FILES['fastmedia_upload_file']['name'][$i],
                'tmp_name' => $_FILES['fastmedia_upload_file']['tmp_name'][$i],
                'type'     => $_FILES['fastmedia_upload_file']['type'][$i]
            ];
            $id = media_handle_sideload($file_array, 0);
            if (!is_wp_error($id)) {
                update_post_meta($id, 'fastmedia_upload_status', 'pending');
                add_post_meta($id, 'fastmedia_activity_log', 'Uploaded on ' . current_time('Y-m-d H:i'));
            }
        }
        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    // ✅ Save + Approve logic
    if (!empty($_POST['fastmedia_approve_ids']) || !empty($_POST['fastmedia_save_ids'])) {
        $action_type = !empty($_POST['fastmedia_approve_ids']) ? 'Approved' : 'Saved';
        $target_ids = !empty($_POST['fastmedia_approve_ids']) ? $_POST['fastmedia_approve_ids'] : $_POST['fastmedia_save_ids'];

        foreach ($target_ids as $id) {
            if (get_post_field('post_author', $id) == $user_id) {
                if ($action_type === 'Approved') delete_post_meta($id, 'fastmedia_upload_status');

                if (!empty($_POST['meta'][$id])) {
                    foreach ($_POST['meta'][$id] as $key => $value) {
                        update_post_meta($id, $key, sanitize_text_field($value));
                    }
                }

                if (!empty($_POST['meta_map'][$id])) {
                    foreach ($_POST['meta_map'][$id] as $unmatched => $acf_target) {
                        $unmatched_value = get_post_meta($id, $unmatched, true);
                        if (!empty($unmatched_value)) {
                            update_field($acf_target, sanitize_text_field($unmatched_value), $id);
                        }
                    }
                }

                add_post_meta($id, 'fastmedia_activity_log', "$action_type on " . current_time('Y-m-d H:i'));
            }
        }
        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    // ✅ Reject logic
    if (!empty($_POST['fastmedia_reject_ids'])) {
        foreach ($_POST['fastmedia_reject_ids'] as $id) {
            if (get_post_field('post_author', $id) == $user_id) {
                wp_delete_attachment($id, true);
            }
        }
        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    $attachments = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => $user_id,
        'meta_key' => 'fastmedia_upload_status',
        'meta_value' => 'pending',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $acf_fields = [
        'imagereference', 'secondary_id', 'caption', 'tags', 'credit', 'creator', 'location', 'title', 'ref_code',
        'copyright', 'capture_date', 'camera_make', 'camera_model', 'software', 'color_space', 'license_type',
        'license_summary', 'notes', 'collection', 'filename', 'file_size', 'image_dimensions', 'file_type', 'edit_history'
    ];

    ob_start();
    ?>
    <h2>📄 Upload New Assets</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="fastmedia_upload_file[]" multiple accept="image/*" required>
        <button type="submit" class="button button-primary">Upload Files</button>
    </form>

    <?php if (!empty($attachments)): ?>
    <form method="post" style="margin-top: 30px;">
        <h3 id="upload">📂 Pending Review</h3>
        <button type="submit" name="fastmedia_approve_ids[]" class="button button-primary" style="margin-bottom: 15px;">✅ Approve Selected</button>
        <button type="submit" name="fastmedia_save_ids[]" class="button" style="margin-bottom: 15px; margin-left: 10px;">💾 Save Mapped Fields</button>
        <button type="submit" name="fastmedia_reject_ids[]" class="button" style="margin-bottom: 15px; margin-left: 10px;">🗑️ Ignore Selected</button>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
        <?php foreach ($attachments as $a):
            $thumb = wp_get_attachment_image_src($a->ID, 'medium');
            $all_meta = get_post_meta($a->ID);
            $raw_iptc = get_post_meta($a->ID, '_fastmedia_raw_iptc', true);
            $iptc_field_count = substr_count($raw_iptc, 's:');
            $acf_data = [];
            foreach ($acf_fields as $acf_key) {
                $acf_data[$acf_key] = get_field($acf_key, $a->ID);
            }
            $unmatched = [];
            foreach ($all_meta as $key => $val) {
                if (strpos($key, '_fastmedia_') === 0 && !in_array(str_replace('_fastmedia_', '', $key), $acf_fields)) {
                    $unmatched[$key] = $val[0];
                }
            }
        ?>
            <div style="border:1px solid #ccc; padding:10px; border-radius:6px; background:#fff;">
                <label><input type="checkbox" name="fastmedia_approve_ids[]" value="<?= $a->ID ?>"> Approve</label>
                <label style="margin-left: 10px;"><input type="checkbox" name="fastmedia_reject_ids[]" value="<?= $a->ID ?>"> Ignore</label>
                <img src="<?= esc_url($thumb[0]) ?>" style="width:100%; margin-bottom:10px;">

                <details>
                    <summary style="cursor:pointer; font-weight:600; margin-bottom:8px;">📝 Metadata</summary>
                    <div style="font-size:13px;">
                        <div style="margin-bottom:5px; background:#e5f2f8; padding:5px;">
                            <label><strong>Asset ID (Filename)</strong>:</label><br>
                            <input type="text" value="<?= esc_attr($acf_data['filename']) ?>" readonly style="width:100%;">
                        </div>
                        <div style="margin-bottom:5px; background:#f5f5dc; padding:5px;">
                            <label>Secondary ID (Optional):</label><br>
                            <input type="text" name="meta[<?= $a->ID ?>][_fastmedia_secondary_id]" value="<?= esc_attr($acf_data['secondary_id']) ?>" style="width:100%;">
                        </div>
                        <?php foreach ($acf_data as $acf_field => $val):
                            if (in_array($acf_field, ['filename', 'secondary_id'])) continue; ?>
                            <div style="margin-bottom:5px;">
                                <label><?= ucfirst(str_replace('_', ' ', $acf_field)) ?>:</label><br>
                                <input type="text" name="meta[<?= $a->ID ?>][_fastmedia_<?= esc_attr($acf_field) ?>]" value="<?= esc_attr($val) ?>" style="width:100%;">
                            </div>
                        <?php endforeach; ?>
                        <?php foreach ($unmatched as $raw_key => $raw_val): ?>
                            <div style="margin-bottom:5px;">
                                <label>Unmatched Field: <?= esc_html($raw_key) ?></label><br>
                                <input type="text" disabled value="<?= esc_attr($raw_val) ?>" style="width:100%;">
                                <select name="meta_map[<?= $a->ID ?>][<?= esc_attr($raw_key) ?>]" style="width:100%; margin-top:4px;">
                                    <option value="">Map to ACF field…</option>
                                    <?php foreach ($acf_fields as $acf_option): ?>
                                        <option value="<?= esc_attr($acf_option) ?>">→ <?= ucfirst(str_replace('_', ' ', $acf_option)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>

                <?php if (!empty($raw_iptc)): ?>
                <details style="margin-top:10px;">
                    <summary style="cursor:pointer; font-weight:600;">🔍 Raw IPTC Debug (<?= $iptc_field_count ?> fields)</summary>
                    <div style="position:relative;">
                        <button onclick="navigator.clipboard.writeText(this.nextElementSibling.textContent)" style="position:absolute; top:0; right:0; background:#333; color:#fff; font-size:12px; padding:2px 6px; border:none; border-radius:3px; cursor:pointer;">📋 Copy</button>
                        <pre style="white-space:pre-wrap; font-size:12px; background:#f9f9f9; padding:10px; border:1px dashed #aaa; border-radius:4px; overflow:auto; margin-top:25px;">
                            <?= esc_html($raw_iptc) ?>
                        </pre>
                    </div>
                </details>
                <?php endif; ?>

                <?php $activity = get_post_meta($a->ID, 'fastmedia_activity_log'); if (!empty($activity)): ?>
                <details style="margin-top:10px;">
                    <summary style="cursor:pointer; font-weight:600;">📘 Activity Log (<?= count($activity) ?>)</summary>
                    <ul style="font-size:12px; margin-left:16px;">
                        <?php foreach (array_reverse($activity) as $log): ?>
                            <li><?= esc_html($log) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </form>
    <?php endif; ?>
    <?php
    return ob_get_clean();
});
