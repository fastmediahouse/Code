// ✅ Step 4B: Manual Metadata Editor Sidebar (ACF-based override in Upload tab) + Auto import of unmatched _fastmedia_ fields
add_action('wp_ajax_fastmedia_get_metadata_form', function () {
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'unauthorized']);

    $attachment_id = intval($_POST['attachment_id'] ?? 0);
    if (!$attachment_id || get_post_type($attachment_id) !== 'attachment') {
        wp_send_json_error(['message' => 'invalid attachment']);
    }

    // Known ACF field keys => labels
    $acf_fields = [
        'tags' => 'Tags', 'credit' => 'Credit', 'license_type' => 'License Type',
        'notes' => 'Notes', 'license_summary' => 'License Summary', 'creator' => 'Creator',
        'copyright' => 'Copyright', 'capture_date' => 'Capture Date',
        'camera_make' => 'Camera Make', 'camera_model' => 'Camera Model',
        'software' => 'Software', 'collection' => 'Collection', 'location' => 'Location'
    ];

    // Fetch fastmedia_ prefixed fields
    $all_meta = get_post_meta($attachment_id);
    $fastmedia_raw = [];
    foreach ($all_meta as $key => $vals) {
        if (strpos($key, '_fastmedia_') === 0) {
            $stripped = substr($key, 11);
            $fastmedia_raw[$stripped] = sanitize_text_field($vals[0]);
        }
    }

    ob_start();
    ?>
    <form id="fastmedia-metadata-form" data-id="<?= esc_attr($attachment_id) ?>">
        <?php foreach ($acf_fields as $key => $label):
            $value = get_field($key, $attachment_id);
            echo "<label><strong>{$label}</strong><br><input type='text' name='{$key}' value='" . esc_attr($value) . "' style='width:100%;margin-bottom:10px;'></label>";
        endforeach; ?>

        <?php if (!empty($fastmedia_raw)):
            echo "<h4 style='margin-top:20px;'>⚠️ Unmapped Metadata</h4>";
            foreach ($fastmedia_raw as $unmapped_key => $val):
                if (!isset($acf_fields[$unmapped_key])):
                    echo "<label><strong>{$unmapped_key}</strong><br>
                        <input type='text' value='" . esc_attr($val) . "' disabled style='width:100%; margin-bottom:5px;'>
                        <select name='map_{$unmapped_key}' style='width:100%; margin-bottom:10px;'>
                            <option value=''>Map to ACF field...</option>";
                    foreach ($acf_fields as $acf_key => $acf_label) {
                        echo "<option value='{$acf_key}'>{$acf_label}</option>";
                    }
                    echo "</select></label>";
                endif;
            endforeach;
        endif; ?>

        <button type="submit" class="button button-primary" style="width:100%; margin-top:15px;">💾 Save Metadata</button>
    </form>
    <script>
    document.getElementById('fastmedia-metadata-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        data.append('action', 'fastmedia_save_metadata');

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: data
        }).then(res => res.json()).then(response => {
            if (response.success) {
                alert('✅ Metadata saved.');
            } else {
                alert('❌ Failed to save.');
            }
        });
    });
    </script>
    <?php
    wp_send_json_success(['html' => ob_get_clean()]);
});

// ✅ Save handler with mapping support
add_action('wp_ajax_fastmedia_save_metadata', function () {
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'unauthorized']);

    $attachment_id = intval($_POST['attachment_id'] ?? $_POST['id'] ?? 0);
    if (!$attachment_id || get_post_type($attachment_id) !== 'attachment') {
        wp_send_json_error(['message' => 'invalid attachment']);
    }

    $acf_fields = [
        'tags','credit','license_type','notes','license_summary','creator',
        'copyright','capture_date','camera_make','camera_model','software','collection','location'
    ];

    foreach ($acf_fields as $field) {
        if (isset($_POST[$field])) {
            update_field($field, sanitize_text_field($_POST[$field]), $attachment_id);
        }
    }

    // Handle remapped _fastmedia_ fields
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'map_') === 0 && $value) {
            $unmapped_key = substr($key, 4);
            $original_meta = get_post_meta($attachment_id, '_fastmedia_' . $unmapped_key, true);
            if ($original_meta && in_array($value, $acf_fields)) {
                update_field($value, sanitize_text_field($original_meta), $attachment_id);
            }
        }
    }

    wp_send_json_success(['message' => 'saved']);
});
