add_action('add_attachment', function ($attachment_id) {
    if (!wp_attachment_is_image($attachment_id)) return;

    // ✅ DIRECT ACF FIELD MAPPING FROM FASTMEDIA META
    $field_map = [
        '_fastmedia_tags'            => 'tags',
        '_fastmedia_credit'          => 'credit',
        '_fastmedia_license_type'    => 'license_type',
        '_fastmedia_notes'           => 'notes',
        '_fastmedia_license_summary' => 'license_summary',

        '_fastmedia_copyright'       => 'copyright',
        '_fastmedia_camera_make'     => 'camera_make',
        '_fastmedia_camera_model'    => 'camera_model',
        '_fastmedia_date_taken'      => 'date_taken',
        '_fastmedia_collection'      => 'collection',
        '_fastmedia_location'        => 'location',
        '_fastmedia_edit_history'    => 'edit_history',
        '_fastmedia_software'        => 'software',
        '_fastmedia_filename'        => 'filename',
        '_fastmedia_file_size'       => 'file_size',
        '_fastmedia_image_dimensions'=> 'image_dimensions',
        '_fastmedia_file_type'       => 'file_type'
    ];

    foreach ($field_map as $meta_key => $acf_field) {
        $value = get_post_meta($attachment_id, $meta_key, true);
        if (!empty($value)) {
            update_field($acf_field, sanitize_text_field($value), $attachment_id);
        }
    }

    // ✅ SPECIAL HANDLING – CREATOR MERGE LOGIC
    $creator = get_post_meta($attachment_id, '_fastmedia_photographer', true)
            ?: get_post_meta($attachment_id, '_fastmedia_credit', true);
    if (!empty($creator)) {
        update_field('creator', sanitize_text_field($creator), $attachment_id);
    }

    // ✅ OPTIONAL EXTENSIONS
    // Add anything not yet mapped here as needed
});
