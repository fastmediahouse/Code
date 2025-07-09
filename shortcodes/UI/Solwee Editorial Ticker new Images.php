add_shortcode('solwee_editorial_ticker', function () {
    $api_url = 'https://api.solwee.com/api/v2/search/images/editorial';
    $webID = 57;

    $body = json_encode([
        'sortingTypeID' => 4, // Newest first
        'limit' => 40,
        'offset' => 0
    ]);

    $response = wp_remote_post($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WebID' => $webID
        ],
        'body' => $body,
        'timeout' => 10
    ]);

    if (is_wp_error($response)) return '<p>⚠️ Could not load editorial images.</p>';

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($data['results'])) return '<p>⚠️ No editorial images found.</p>';

    ob_start();
    ?>
    <div class="solwee-ticker-wrapper">
        <div class="solwee-label-square">NEW IMAGES</div>
        <div class="solwee-ticker-container">
            <div class="solwee-ticker-track">
                <?php foreach (array_slice($data['results'], 0, 20) as $item):
                    $thumb = esc_url($item['thumb260Url'] ?? $item['thumbUrl'] ?? '');
                    $id = esc_attr($item['productID']);
                    if (!$thumb) continue;
                    ?>
                    <a href="/image-detail/?productID=<?= $id ?>" class="ticker-image" target="_blank">
                        <img src="<?= $thumb ?>" alt="Editorial image" />
                    </a>
                <?php endforeach; ?>
                <?php foreach (array_slice($data['results'], 0, 10) as $item): ?>
                    <?php
                    $thumb = esc_url($item['thumb260Url'] ?? $item['thumbUrl'] ?? '');
                    $id = esc_attr($item['productID']);
                    if (!$thumb) continue;
                    ?>
                    <a href="/image-detail/?productID=<?= $id ?>" class="ticker-image" target="_blank">
                        <img src="<?= $thumb ?>" alt="Editorial image" />
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
    .solwee-ticker-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 10px 0;
        background: #fafafa;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }

    .solwee-label-square {
        font-weight: bold;
        font-size: 14px;
        height: 80px;
        width: 80px;
        background: #000;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .solwee-ticker-container {
        overflow: hidden;
        flex-grow: 1;
    }

    .solwee-ticker-track {
        display: flex;
        gap: 12px;
        animation: tickerScroll 20s linear infinite; /* faster scroll */
        will-change: transform;
    }

    .ticker-image {
        flex-shrink: 0;
        display: inline-block;
    }

    .ticker-image img {
        height: 80px;
        width: auto;
        object-fit: contain;
        border-radius: 6px;
        transition: transform 0.3s ease;
        display: block;
    }

    .ticker-image:hover img {
        transform: scale(1.2);
        z-index: 5;
    }

    @keyframes tickerScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    @media (max-width: 768px) {
        .ticker-image img { height: 60px; }
        .solwee-label-square {
            height: 60px;
            width: 60px;
            font-size: 12px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
});
