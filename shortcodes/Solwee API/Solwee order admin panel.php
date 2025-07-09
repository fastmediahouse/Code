// WPCode Snippet Name: solwee_order_admin_panel (with debug logging)

add_action('admin_menu', function () {
    add_options_page(
        'Solwee Orders',
        'Solwee Orders',
        'manage_options',
        'solwee-order-status',
        'render_solwee_order_admin_page'
    );
});

function render_solwee_order_admin_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['retry_order_id']) && isset($_POST['retry_item_id'])) {
        $retry_order_id = intval($_POST['retry_order_id']);
        $retry_item_id = intval($_POST['retry_item_id']);
        $order = wc_get_order($retry_order_id);

        if ($order && $order instanceof WC_Order) {
            $user_id = $order->get_user_id();
            $username = $user_id ? get_userdata($user_id)->user_login : 'Guest';
            $password = get_user_meta($user_id, 'solwee_password', true);
            $webID = '57';
            $token = get_user_meta($user_id, 'solwee_token', true);

            error_log("🔁 RETRY: Order {$retry_order_id}, Item {$retry_item_id}, User {$username}");

            if (!$token && $password) {
                $login_response = wp_remote_post('https://api.solwee.com/api/v2/login', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-WebID' => $webID
                    ],
                    'body' => json_encode([
                        'login' => $username,
                        'password' => $password
                    ])
                ]);

                if (!is_wp_error($login_response)) {
                    $token = trim(wp_remote_retrieve_body($login_response), '"');
                    if ($token && strpos($token, '.') !== false) {
                        update_user_meta($user_id, 'solwee_token', $token);
                        update_user_meta($user_id, 'solwee_token_time', time());
                    }
                }
            }

            if ($token) {
                $items = $order->get_items();
                if (isset($items[$retry_item_id])) {
                    $item = $items[$retry_item_id];
                    $meta = $item->get_meta('solwee_license');
                    if (!$meta || !isset($meta['solwee_imageID'])) {
                        error_log("⚠️ Retry Failed: No solwee_license metadata found for item {$retry_item_id}");
                        $item->add_meta_data('solwee_submitted', 'no');
                        $item->add_meta_data('solwee_error_message', 'Missing solwee_license metadata');
                        $item->save();
                    } else {
                        $payload = [
                            'magazineID'        => (int) $meta['solwee_magazineID'],
                            'editorialUsageID'  => (int) $meta['solwee_usageID'],
                            'productID'         => (int) $meta['solwee_imageID']
                        ];

                        error_log("🔁 Payload: " . print_r($payload, true));

                        $response = wp_remote_post('https://api.solwee.com/api/v2/editorial-order', [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $token,
                                'Content-Type'  => 'application/json',
                                'X-WebID'       => $webID
                            ],
                            'body' => json_encode($payload)
                        ]);

                        if (is_wp_error($response)) {
                            $item->add_meta_data('solwee_submitted', 'no');
                            $item->add_meta_data('solwee_error_message', $response->get_error_message());
                            error_log("❌ Solwee API error: " . $response->get_error_message());
                        } else {
                            $body = json_decode(wp_remote_retrieve_body($response), true);
                            if (isset($body['processID'])) {
                                $item->add_meta_data('solwee_submitted', 'yes');
                                $item->delete_meta_data('solwee_error_message');
                                $item->add_meta_data('solwee_process_id', $body['processID']);
                                error_log("✅ Success: Process ID " . $body['processID']);
                            } else {
                                $item->add_meta_data('solwee_submitted', 'no');
                                $item->add_meta_data('solwee_error_message', 'Missing processID in response');
                                error_log("❌ No processID in Solwee response: " . wp_remote_retrieve_body($response));
                            }
                        }
                        $item->save();
                    }
                }
            }
        }
        echo '<div class="updated notice"><p><strong>Retry attempted for Order ' . esc_html($retry_order_id) . ' Item ' . esc_html($retry_item_id) . ' — check error log for details.</strong></p></div>';
    }

    $orders = wc_get_orders([
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    echo '<div class="wrap">';
    echo '<h1>Solwee Submit Placed Orders</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr>
            <th>Order ID</th>
            <th>Date/Time</th>
            <th>Username</th>
            <th>Product</th>
            <th>Product ID</th>
            <th>Thumbnail</th>
            <th>Submitted to Solwee</th>
            <th>Error Message</th>
            <th>Process ID</th>
            <th>Retry</th>
        </tr></thead><tbody>';

    foreach ($orders as $order) {
        $user_id = $order->get_user_id();
        $username = $user_id ? get_userdata($user_id)->user_login : 'Guest';
        $order_datetime = $order->get_date_created()->date('Y-m-d H:i');

        foreach ($order->get_items() as $item_id => $item) {
            $product_name = $item->get_name();
            $product_id = $item->get_meta('productID');
            $thumbnail = $item->get_meta('preview_url');
            $submitted = $item->get_meta('solwee_submitted') === 'yes' ? '✅ Yes' : '❌ No';
            $error_msg = $item->get_meta('solwee_error_message');
            $process_id = $item->get_meta('solwee_process_id');

            echo '<tr>';
            echo '<td>' . esc_html($order->get_id()) . '</td>';
            echo '<td>' . esc_html($order_datetime) . '</td>';
            echo '<td>' . esc_html($username) . '</td>';
            echo '<td>' . esc_html($product_name) . '</td>';
            echo '<td>' . esc_html($product_id) . '</td>';
            echo '<td>';
            if ($thumbnail) {
                echo '<img src="' . esc_url($thumbnail) . '" alt="Thumbnail" style="max-width:100px;height:auto;">';
            } else {
                echo '-';
            }
            echo '</td>';
            echo '<td>' . $submitted . '</td>';
            echo '<td>' . esc_html($error_msg ?: '-') . '</td>';
            echo '<td>' . esc_html($process_id ?: '-') . '</td>';
            echo '<td><form method="post">
                    <input type="hidden" name="retry_order_id" value="' . esc_attr($order->get_id()) . '">
                    <input type="hidden" name="retry_item_id" value="' . esc_attr($item_id) . '">
                    <input type="submit" class="button" value="Retry">
                  </form></td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';
    echo '</div>';
}
