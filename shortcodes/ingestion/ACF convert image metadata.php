// ✅ Fast Media – Extract and Map IPTC Metadata on Upload
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    $file_path = get_attached_file($attachment_id);

    // Only process if it's an image
    $mime = get_post_mime_type($attachment_id);
    if (strpos($mime, 'image/') !== 0 || !file_exists($file_path)) {
        return $metadata;
    }

    // Try to read IPTC data
    $size = getimagesize($file_path, $info);
    if (!isset($info['APP13'])) {
        return $metadata; // No IPTC metadata
    }

    $iptc = iptcparse($info['APP13']);
    if (!$iptc || !is_array($iptc)) {
        return $metadata;
    }

    // Define field mappings: IPTC key => internal field name
    $field_map = [
        '2#120' => 'caption',       // Caption/Abstract
        '2#025' => 'tags',          // Keywords
        '2#080' => 'byline_title',  // Job Title
        '2#085' => 'source',        // Source
        '2#110' => 'credit',        // Credit Line
        '2#115' => 'photographer',  // By-line (same as author)
        '2#090' => 'city',          // City
        '2#095' => 'state',         // Province/State
        '2#101' => 'country',       // Country/Primary Location
        '2#005' => 'title',         // Object Name / Title
        '2#103' => 'ref_code',      // Original Transmission Reference
    ];

    // Extract and normalize values
    $meta_values = [];
    foreach ($field_map as $iptc_key => $field_name) {
        if (!empty($iptc[$iptc_key])) {
            $raw = is_array($iptc[$iptc_key]) ? $iptc[$iptc_key][0] : $iptc[$iptc_key];
            $clean = sanitize_text_field(trim($raw));
            if (!empty($clean)) {
                $meta_values[$field_name] = $clean;
            }
        }
    }

    // Special handling: combine city/state/country into 'location'
    $location_parts = array_filter([
        $meta_values['city'] ?? '',
        $meta_values['state'] ?? '',
        $meta_values['country'] ?? ''
    ]);
    if (!empty($location_parts)) {
        $meta_values['location'] = implode(', ', $location_parts);
    }

    // Save as custom post meta with fastmedia_ prefix
    foreach ($meta_values as $field => $value) {
        if (in_array($field, ['city', 'state', 'country'])) continue; // Skip raw location parts
        update_post_meta($attachment_id, '_fastmedia_' . $field, $value);
    }

    return $metadata;
}, 20, 2);
