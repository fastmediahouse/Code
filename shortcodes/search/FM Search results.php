// ✅ FIXED: FM SEARCH shortcode now using Magic Search layout with correct toggle placement + correct URL param
add_shortcode('fm_search', function () {
    $query = sanitize_text_field($_GET['q'] ?? '');
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    $reseller_key = '9775d90385e6e128768064fd4d370d1e7477a102';
    $search_url = 'https://api.depositphotos.com/search';

    $params = [
        'query' => $query,
        'limit' => $per_page,
        'offset' => $offset,
        'image_type' => 'photo',
        'orientation' => 'all',
        'editorial' => 'all',
        'safe_search' => '1',
        'reseller_key' => $reseller_key
    ];

    $response = wp_remote_get(add_query_arg($params, $search_url));
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $results = $data['results'] ?? [];
    $total = intval($data['total'] ?? 0);

    ob_start();
    ?>

    <style>
    .solwee-masonry { column-count: 4; column-gap: 20px; }
    @media (max-width: 1200px) { .solwee-masonry { column-count: 3; } }
    @media (max-width: 768px) { .solwee-masonry { column-count: 2; } }
    @media (max-width: 480px) { .solwee-masonry { column-count: 1; } }
    .solwee-tile {
        break-inside: avoid; margin-bottom: 20px; background: #fff;
        border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative;
    }
    .solwee-tile img { width: 100%; height: auto; display: block; }
    .label-badge {
        position: absolute; top: 8px; left: 8px;
        background: #0077cc; color: #fff;
        font-size: 11px; font-weight: bold;
        padding: 4px 6px; border-radius: 3px; z-index: 9;
    }
    .label-badge.upload { background: #00a65a; }
    </style>

    <div style="margin: 10px 0; font-size: 15px; font-weight: 500;">
        <?= $total ?> results found
    </div>

    <div class="solwee-masonry">
    <?php
    if (empty($results)) {
        echo "<p>No results found.</p>";
    } else {
        foreach ($results as $item) {
            $id = esc_attr($item['id'] ?? '');
            $thumb = esc_url($item['thumbnail_url'] ?? '');
            $fallback = '/no-preview.jpg';
            $source = 'DP';

            echo "<div class='solwee-tile'>
                <a href='/fm-image-detail/?id=dp-{$id}' target='_blank'>
                    <img src='{$thumb}' alt='Image {$id}' loading='lazy' onerror=\"this.onerror=null;this.src='{$fallback}';\" />
                </a>
                <div class='label-badge' title='Depositphotos'>DP</div>
                " . fastmedia_project_toggle_ui('dp-' . $id, $source) . "
            </div>";
        }
    }
    ?>
    </div>

    <?php
    return ob_get_clean();
});
