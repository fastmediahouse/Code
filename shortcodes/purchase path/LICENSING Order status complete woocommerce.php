/**
 * ✅ Fast Media – Solwee Ingestion (Token Based, Debug)
 * Triggers on WooCommerce order completion.
 * Uses solwee_token to retrieve image via processID.
 * Logs each step to debug.log.
 */

add_action('woocommerce_order_status_completed', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("❌ Order ID {$order_id} not found.");
        return;
    }

    $user_id = $order->get_user_id();
    if (!$user_id) {
        error_log("❌ No user attached to order {$order_id}");
        return;
    }

    $solwee_token = get_user_meta($user_id, 'solwee_token', true);
    if (empty($solwee_token)) {
        error_log("❌ Missing solwee_token for user ID {$user_id}");
        return;
    }

    error_log("✅ Starting Solwee ingestion for order {$order_id}, user {$user_id}");

    $purchased = get_user_meta($user_id, 'solwee_purchased_images', true);
    $purchased = is_array($purchased) ? $purchased : [];

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    foreach ($order->get_items() as $item) {
        $item_id    = $item->get_id();
        $submitted  = wc_get_order_item_meta($item_id, 'solwee_submitted', true);
        $process_id = wc_get_order_item_meta($item_id, 'solwee_process_id', true);
        $license    = wc_get_order_item_meta($item_id, 'solwee_license', true);
        $productID  = !empty($license['solwee_imageID']) ? $license['solwee_imageID'] : false;
        $title      = sanitize_text_field($license['title'] ?? 'Licensed Image');

        error_log("🧾 Item {$item_id} — submitted: {$submitted}, processID: {$process_id}, productID: {$productID}");

        if ($submitted !== 'yes' || !$process_id || !$productID) {
            error_log("⏭️ Skipping item {$item_id} due to missing data.");
            continue;
        }

        // Skip duplicates
        $already_ingested = false;
        foreach ($purchased as $p) {
            if (!empty($p['productID']) && $p['productID'] === $productID) {
                error_log("⚠️ ProductID {$productID} already ingested.");
                $already_ingested = true;
                break;
            }
        }
        if ($already_ingested) continue;

        // Fetch download link
        $url = "https://api.solwee.com/api/v2/editorial-order/download-link?processID=" . urlencode($process_id);
        error_log("🔗 Fetching Solwee download URL: {$url}");

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . trim($solwee_token, '"'),
                'X-WebID'       => '57',
            ],
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            error_log("❌ Solwee API error for processID {$process_id}: " . $response->get_error_message());
            continue;
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);
        $download_url = $result['url'] ?? false;

        if (!$download_url) {
            error_log("❌ No download URL returned for processID {$process_id}");
            continue;
        }

        error_log("📥 Download URL received: {$download_url}");

        // Download the image
        $tmp = download_url($download_url);
        if (is_wp_error($tmp)) {
            error_log("❌ Failed to download file from Solwee: " . $tmp->get_error_message());
            continue;
        }

        $file_array = [
            'name'     => basename(parse_url($download_url, PHP_URL_PATH)),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, "Purchased Image {$productID}");
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            error_log("❌ Media sideload failed: " . $attachment_id->get_error_message());
            continue;
        }

        error_log("✅ Media uploaded: attachment ID {$attachment_id}");

        // Tag and save
        update_post_meta($attachment_id, 'fastmedia_licensed', 'yes');
        update_post_meta($attachment_id, 'fastmedia_buyer_id', $user_id);
        wc_update_order_item_meta($item_id, '_fastmedia_attachment_id', $attachment_id);

        $purchased[] = [
            'productID'     => $productID,
            'attachment_id' => $attachment_id,
            'download_url'  => esc_url_raw($download_url),
            'title'         => $title,
        ];
    }

    update_user_meta($user_id, 'solwee_purchased_images', $purchased);
    error_log("✅ Ingestion complete for order {$order_id}");
}, 30);
