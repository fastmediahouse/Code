add_shortcode('fm_product_detail', function () {
    if (!isset($_GET['id'])) return '<p>No image ID provided.</p>';

    $productID = sanitize_text_field($_GET['id']);
    $dp_base = 'https://api.depositphotos.com';
    $dp_apikey = '9775d90385e6e128768064fd4d370d1e7477a102';

    $response = wp_remote_post($dp_base, [
        'body' => [
            'dp_command' => 'getMedia',
            'dp_apikey' => $dp_apikey,
            'dp_media_id' => $productID,
        ]
    ]);

    if (is_wp_error($response)) return '<p>API error: ' . esc_html($response->get_error_message()) . '</p>';

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $product = $data['result'][0] ?? null;
    if (!$product) return '<p>Image not found.</p>';

    $title = esc_html($product['title'] ?? 'Untitled');
    $caption = esc_html($product['description'] ?? '');
    $author = esc_html($product['author'] ?? '');
    $id = esc_html($product['id'] ?? '');
    $thumb = esc_url($product['url'] ?? '');

    ob_start();
    ?>
    <div class="dp-detail-wrapper" style="display:flex;gap:40px;flex-wrap:wrap;max-width:1100px;margin:auto;padding:40px 20px;">
        <div class="dp-image" style="flex:1 1 500px;max-width:600px;">
            <img src="<?= $thumb ?>" alt="<?= $title ?>" style="width:100%;height:auto;border-radius:4px;box-shadow:0 0 10px rgba(0,0,0,0.1);" />

            <!-- ✅ Inserted Project Toggle -->
            <div style="margin-top:15px;">
                <?= fastmedia_project_toggle_ui($id, 'ST') ?>
            </div>

            <div class="dp-metadata" style="margin-top:20px;">
                <h1 style="font-size:26px;margin-bottom:10px;"><?= $title ?></h1>
                <p><strong>Image ID:</strong> <?= $id ?></p>
                <?php if ($caption): ?><p><strong>Caption:</strong> <?= $caption ?></p><?php endif; ?>
                <?php if ($author): ?><p><strong>Author:</strong> <?= $author ?></p><?php endif; ?>
            </div>
        </div>

        <div class="dp-pricing-placeholder" style="flex:1 1 400px;min-width:280px;">
            <!-- Pricing will be implemented later -->
        </div>
    </div>
    <?php
    return ob_get_clean();
});
