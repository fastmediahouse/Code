// ✅ Solwee License Submission on WooCommerce Order Completion
add_action('woocommerce_thankyou', function($order_id) {
    if (!$order_id) return;
    $order = wc_get_order($order_id);
    if (!$order || !is_a($order, 'WC_Order')) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    // 🔐 Solwee credentials (dynamic per user)
    $webID       = '57';
    $user        = get_userdata($user_id);
    $username    = $user->user_login;
    $password    = get_user_meta($user_id, 'solwee_password', true);
    $magazine_id = get_user_meta($user_id, 'solwee_magazine_id', true);

    if (!$password || !$magazine_id) {
        error_log("❌ Missing Solwee password or magazineID for user {$username}");
        return;
    }

    // 🔐 Login to Solwee
    $login_response = wp_remote_post('https://api.solwee.com/api/v2/login', [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WebID'       => $webID
        ],
        'body'    => json_encode([
            'login'    => $username,
            'password' => $password
        ]),
        'timeout' => 10
    ]);

    if (is_wp_error($login_response)) {
        error_log("❌ Login error: " . $login_response->get_error_message());
        return;
    }

    $login_body = json_decode(wp_remote_retrieve_body($login_response), true);
    $token = $login_body['token'] ?? null;

    if (!$token || strpos($token, '.') === false) {
        error_log("❌ Invalid token received from Solwee for user {$username}: " . print_r($login_body, true));
        return;
    }

    // ✅ Store token (optional)
    update_user_meta($user_id, 'solwee_token', $token);
    update_user_meta($user_id, 'solwee_token_time', time());

    foreach ($order->get_items() as $item) {
        $meta = $item->get_meta('solwee_license');
        if (!is_array($meta)) {
            error_log("⚠️ Missing solwee_license metadata on order item.");
            continue;
        }

        $imageID = $meta['solwee_imageID'] ?? null;
        $usageID = $meta['solwee_usageID'] ?? null;

        if (!$imageID || !$usageID) {
            error_log("⚠️ Incomplete license metadata: " . print_r($meta, true));
            continue;
        }

        $payload = [
            'magazineID'        => (int) $magazine_id,
            'editorialUsageID'  => (int) $usageID,
            'productID'         => (int) $imageID
        ];

        error_log("📦 Submitting license to Solwee: " . json_encode($payload));

        $license_response = wp_remote_post('https://api.solwee.com/api/v2/editorial-order', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => 'application/json',
                'X-WebID'       => $webID
            ],
            'body'    => json_encode($payload),
            'timeout' => 20
        ]);

        $response_body = wp_remote_retrieve_body($license_response);

        if (is_wp_error($license_response)) {
            error_log("❌ Solwee license error: " . $license_response->get_error_message());
            $item->add_meta_data('solwee_submitted', 'no');
            $item->add_meta_data('solwee_error_message', $license_response->get_error_message());
        } elseif (wp_remote_retrieve_response_code($license_response) >= 300) {
            error_log("❌ Solwee license rejected: " . $response_body);
            $item->add_meta_data('solwee_submitted', 'no');
            $item->add_meta_data('solwee_error_message', $response_body);
        } else {
            $body = json_decode($response_body, true);
            if (isset($body['processID'])) {
                $item->add_meta_data('solwee_submitted', 'yes');
                $item->add_meta_data('solwee_process_id', $body['processID']);
                $item->delete_meta_data('solwee_error_message');
                error_log("✅ Solwee license successful. Process ID: {$body['processID']}");
            } else {
                error_log("❌ Solwee license response missing processID: " . print_r($body, true));
                $item->add_meta_data('solwee_submitted', 'no');
                $item->add_meta_data('solwee_error_message', 'Missing processID or malformed response.');
            }
        }

        $item->save();
    }
}, 10, 1);
