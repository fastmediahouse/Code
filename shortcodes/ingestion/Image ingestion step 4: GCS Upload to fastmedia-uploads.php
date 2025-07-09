// ✅ Image Ingestion Step 3: GCS Upload to fastmedia-uploads (Fixed)
add_action('add_attachment', function ($post_ID) {
    $file_path = get_attached_file($post_ID);
    if (!$file_path || !file_exists($file_path)) return;

    $user_id = get_current_user_id();
    if (!$user_id) return;

    // ✅ Set your bucket and service key path
    $bucket_name = 'fastmedia-uploads';
    $service_account = __DIR__ . '/fast-media-hub-98d5786f7950.json';
    putenv("GOOGLE_APPLICATION_CREDENTIALS={$service_account}");

    // ✅ Load Google Cloud Storage client
    require_once ABSPATH . 'vendor/autoload.php';
    $storage = new Google\Cloud\Storage\StorageClient();
    $bucket = $storage->bucket($bucket_name);

    // ✅ Upload file to GCS under /user_{id}/
    $filename = basename($file_path);
    $target_path = "user_{$user_id}/{$filename}";

    try {
        $bucket->upload(
            fopen($file_path, 'r'),
            ['name' => $target_path]
        );
    } catch (Exception $e) {
        error_log('❌ GCS upload error: ' . $e->getMessage());
        return;
    }

    // ✅ Extract IPTC Metadata (same as Step 1)
    $size = getimagesize($file_path, $info);
    $iptc = isset($info['APP13']) ? iptcparse($info['APP13']) : [];

    // ✅ Reliable IPTC mapping
    $iptc_map = [
        '2#025' => 'tags',
        '2#115' => 'creator',
        '2#120' => 'notes',
        '2#110' => 'credit',
        '2#101' => 'location',
        '2#103' => 'license_summary',
        '2#085' => 'license_type',
        '2#010' => 'copyright',
        '2#000' => 'edit_history',
        '2#065' => 'camera_make',
        '2#075' => 'camera_model',
        '2#055' => 'capture_date',
        '2#105' => 'collection',
        '2#105' => 'collection',
        '2#085' => 'license_type',
        '2#065' => 'software',
    ];

    foreach ($iptc_map as $iptc_key => $acf_field) {
        if (!empty($iptc[$iptc_key])) {
            $value = is_array($iptc[$iptc_key]) ? $iptc[$iptc_key][0] : $iptc[$iptc_key];
            $clean = sanitize_text_field($value);
            update_field($acf_field, $clean, $post_ID);
        }
    }

    // ✅ Done!
});
