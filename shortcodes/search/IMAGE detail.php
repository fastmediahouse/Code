add_shortcode('solwee_product_detail_shortcode', function () {
    if (!isset($_GET['productID'])) return '<p>No product ID provided.</p>';

    $productID = sanitize_text_field($_GET['productID']);
    $webID = '57';
    $user_id = get_current_user_id();

    $token = get_transient('solwee_api_token');
    if (!$token) {
        $login_response = wp_remote_post('https://api.solwee.com/api/v2/login', [
            'headers' => ['Content-Type' => 'application/json', 'X-WebID' => $webID],
            'body' => json_encode(['login' => 'marco@fm.house', 'password' => 'your-password-here']),
            'timeout' => 15,
        ]);
        if (is_wp_error($login_response)) return '<p>Login failed: ' . esc_html($login_response->get_error_message()) . '</p>';
        $token = trim(wp_remote_retrieve_body($login_response), "\"");
        if (empty($token)) return '<p>❌ Failed to retrieve token.</p>';
        set_transient('solwee_api_token', $token, 15 * MINUTE_IN_SECONDS);
    }

    $detail_response = wp_remote_get("https://api.solwee.com/api/v2/product-detail/{$productID}", [
        'headers' => ['Authorization' => "Bearer {$token}", 'X-WebID' => $webID],
        'timeout' => 15,
    ]);
    if (is_wp_error($detail_response)) return '<p>Product request failed: ' . esc_html($detail_response->get_error_message()) . '</p>';
    $product = json_decode(wp_remote_retrieve_body($detail_response));
    if (!$product || empty($product->productID)) return "<p>❌ Product not found.</p>";

    $collectionID = $product->collectionID ?? null;
    $collectionLabel = '';
    if ($collectionID) {
        $collections = get_transient('solwee_collections');
        if (!$collections) {
            $collection_response = wp_remote_get('https://api.solwee.com/api/v2/list/collections', [
                'headers' => ['Content-Type' => 'application/json', 'X-WebID' => $webID],
                'timeout' => 10,
            ]);
            if (!is_wp_error($collection_response)) {
                $collections = json_decode(wp_remote_retrieve_body($collection_response), true);
                if (is_array($collections)) set_transient('solwee_collections', $collections, DAY_IN_SECONDS);
            }
        }
        if (is_array($collections)) {
            foreach ($collections as $col) {
                if (intval($col['collectionID']) === intval($collectionID)) {
                    $collectionLabel = esc_html($col['label']);
                    break;
                }
            }
        }
    }

    $img = esc_url("/?solwee_image_proxy={$product->productID}");
    $title = esc_html($product->title ?? 'Untitled');
    $license = esc_html($product->license ?? '');
    $productIDFormatted = esc_html($product->productIDFormatted ?? $product->productID ?? '');
    $location = esc_html($product->location ?? '');
    $author = esc_html($product->author ?? '');
    $aspect = esc_html(round(floatval($product->aspectRatio ?? 0), 2));
    $date = isset($product->dateAdded->date) ? esc_html(substr($product->dateAdded->date, 0, 10)) : '';
    $keywords = '';

    if (!empty($product->keywords) && is_array($product->keywords)) {
        $keywords_array = array_map(function ($k) {
            return is_object($k) && isset($k->keyword) ? $k->keyword : $k;
        }, $product->keywords);
        $keywords = implode(', ', array_filter($keywords_array));
    }

    ob_start();
    ?>
    <div class="solwee-detail-wrapper" style="display:flex;gap:40px;flex-wrap:wrap;max-width:1100px;margin:auto;padding:40px 20px;">
        <div class="solwee-image" style="flex:1 1 500px;max-width:600px;">
            <img src="<?= $img ?>" alt="<?= $title ?>" style="width:100%;height:auto;border-radius:4px;box-shadow:0 0 10px rgba(0,0,0,0.1);" />

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                <p style="font-size:18px;"><strong>Image Number:</strong> <?= $productIDFormatted ?></p>
                <div style="font-size:18px;display:flex;gap:10px;">
                    <span title="Edit" class="solwee-icon" onclick="alert('Edit clicked')">✏️</span>
                    <span title="Link" class="solwee-icon" onclick="alert('Link clicked')">🔗</span>
                    <span title="Delete" class="solwee-icon" onclick="alert('Delete clicked')">🗑️</span>
                </div>
            </div>

            <div class="solwee-actions" style="display:flex;align-items:center;gap:20px;margin-top:12px;">
                <a href="<?= $img ?>" download target="_blank"
                   title="Download Comp"
                   style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; background:#0056b3; color:white; border-radius:6px; text-decoration:none; font-size:18px;">
                    ⬇️
                </a>
            </div>

            <div style="margin-top:12px;">
                <?= fastmedia_project_toggle_ui($productID); ?>
            </div>

            <div class="solwee-metadata" style="margin-top:20px;">
                <h1 style="font-size:26px;margin-bottom:10px;"><?= $title ?></h1>
                <p><strong>License:</strong> <?= $license ?></p>
                <p><strong>Aspect Ratio:</strong> <?= $aspect ?></p>
                <?php if ($author): ?><p><strong>Author:</strong> <?= $author ?></p><?php endif; ?>
                <?php if ($location): ?><p><strong>Location:</strong> <?= $location ?></p><?php endif; ?>
                <?php if ($date): ?><p><strong>Date Added:</strong> <?= $date ?></p><?php endif; ?>
                <?php if ($collectionLabel): ?><p><strong>Collection:</strong> <?= $collectionLabel ?></p><?php endif; ?>
                <?php if (!empty($keywords)): ?>
                    <p><strong>Keywords:</strong><br><?= esc_html($keywords) ?></p>
                <?php endif; ?>
            </div>

            <div class="solwee-activity" style="margin-top:20px;">
                <a onclick="toggleActivityLog()" style="color:#0056b3;cursor:pointer;text-decoration:underline;font-size:15px;">Activity Log</a>
                <div id="solwee-log" style="display:none;font-size:14px;margin-top:10px;">
                    <ul style="padding-left:20px;margin-top:5px;">
                        <li>2025-06-24 15:28 – wpusername0872: Shared</li>
                        <li>2025-06-24 14:42 – wpusername0872: Shared</li>
                        <li>2025-06-24 14:42 – wpusername0872: Added to lightbox</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="solwee-pricing-placeholder" style="flex:1 1 400px;min-width:280px;">
            <!-- Elementor pricing block -->
        </div>
    </div>

    <style>
        .solwee-icon {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .solwee-icon:hover {
            transform: scale(1.2);
        }
    </style>
    <script>
        function toggleActivityLog() {
            const log = document.getElementById("solwee-log");
            if (log.style.display === "none") {
                log.style.display = "block";
            } else {
                log.style.display = "none";
            }
        }
    </script>
    <?php
    return ob_get_clean();
});
