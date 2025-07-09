add_shortcode('solwee_visual_ticker', function () {
    $creative_response = wp_remote_post("https://api.solwee.com/api/v2/search/images/creative", [
        'timeout' => 10,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WebID' => '57'
        ],
        'body' => json_encode([
            'limit' => 6,
            'sortingTypeID' => 5 // newest
        ])
    ]);

    $editorial_response = wp_remote_post("https://api.solwee.com/api/v2/search/images/editorial", [
        'timeout' => 10,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WebID' => '57'
        ],
        'body' => json_encode([
            'limit' => 6,
            'sortingTypeID' => 5 // newest
        ])
    ]);

    $creative = json_decode(wp_remote_retrieve_body($creative_response), true);
    $editorial = json_decode(wp_remote_retrieve_body($editorial_response), true);
    $images = array_merge($creative['results'] ?? [], $editorial['results'] ?? []);
    shuffle($images);

    ob_start();
    ?>
    <style>
    .solwee-visual-ticker {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: repeat(3, 1fr);
        gap: 10px;
    }
    .image-box {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }
    .image-box img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.3s ease;
    }
    .image-box:hover {
        transform: scale(1.03);
        z-index: 2;
    }
    </style>
    <div class="solwee-visual-ticker" id="solweeTicker">
        <?php foreach (array_slice($images, 0, 12) as $img): ?>
            <div class="image-box" data-product-id="<?php echo esc_attr($img['productID']); ?>">
                <a href="/image-detail/?productID=<?php echo esc_attr($img['productID']); ?>">
                    <img src="<?php echo esc_url($img['thumbPath'] ?? $img['largePreviewPath']); ?>" alt="">
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    const refreshInterval = 7000;
    setInterval(() => {
        const boxes = document.querySelectorAll(".solwee-visual-ticker .image-box");
        const box = boxes[Math.floor(Math.random() * boxes.length)];
        fetch("/wp-json/solwee/v1/random-image")
            .then(res => res.json())
            .then(data => {
                if (!data || !data.productID || !data.thumbPath) return;
                const newLink = `/image-detail/?productID=${data.productID}`;
                const newImg = `<a href="${newLink}"><img src="${data.thumbPath}" /></a>`;
                box.innerHTML = newImg;
                box.setAttribute('data-product-id', data.productID);
            });
    }, refreshInterval);
    </script>
    <?php
    return ob_get_clean();
});

// Register custom REST route for dynamic updates
add_action('rest_api_init', function () {
    register_rest_route('solwee/v1', '/random-image', [
        'methods' => 'GET',
        'callback' => function () {
            $source = (rand(0, 1) === 0) ? 'creative' : 'editorial';
            $response = wp_remote_post("https://api.solwee.com/api/v2/search/images/{$source}", [
                'timeout' => 10,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-WebID' => '57'
                ],
                'body' => json_encode([
                    'limit' => 1,
                    'sortingTypeID' => 5
                ])
            ]);
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['results'][0] ?? [];
        },
        'permission_callback' => '__return_true'
    ]);
});
