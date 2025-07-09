// ✅ Image Ingestion: Step 2 – Extract EXIF Metadata and Write to ACF Fields
add_action('add_attachment', function ($attachment_id) {
    if (!wp_attachment_is_image($attachment_id)) return;

    $file_path = get_attached_file($attachment_id);
    if (!file_exists($file_path)) return;

    // ✅ Only allow EXIF-capable file types
    $mime = mime_content_type($file_path);
    if (!in_array($mime, ['image/jpeg', 'image/tiff'])) return;

    // Read EXIF data safely
    $exif = @exif_read_data($file_path, 'ANY_TAG', true);
    if (!$exif || !is_array($exif)) return;

    // Optional logging for debugging
    error_log('📸 EXIF for image ' . $attachment_id . ': ' . print_r($exif, true));

    // ✅ Extract and clean fields (check ACF slugs match exactly)
    $field_map = [
        'creator'       => $exif['IFD0']['Artist']          ?? '',
        'copyright'     => $exif['IFD0']['Copyright']       ?? '',
        'capture_date'  => $exif['EXIF']['DateTimeOriginal']?? '',
        'camera_make'   => $exif['IFD0']['Make']            ?? '',
        'camera_model'  => $exif['IFD0']['Model']           ?? '',
        'software'      => $exif['IFD0']['Software']        ?? '',
        'color_space'   => $exif['EXIF']['ColorSpace']      ?? '',
    ];

    foreach ($field_map as $acf_field => $raw_value) {
        if (!empty($raw_value)) {
            update_field($acf_field, sanitize_text_field($raw_value), $attachment_id);
        }
    }

}, 10, 1);
