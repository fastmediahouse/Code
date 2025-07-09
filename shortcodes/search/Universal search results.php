// ✅ MAGIC SEARCH – COMBINED RESULTS VIEW (Solwee + Uploaded) with ST/UP label overlays using unified PROJECT toggle
add_shortcode('magic_search_results', function () {
    $user_id = get_current_user_id();
    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : ['Default' => []];
    $folder_keys = array_keys($folders);

    $query         = sanitize_text_field($_GET['q'] ?? '');
    $page          = (isset($_GET['page_num']) && $_GET['page_num'] > 0) ? intval($_GET['page_num']) : 1;
    $orientation   = sanitize_text_field($_GET['orientation'] ?? '');
    $modelReleased = sanitize_text_field($_GET['modelReleased'] ?? '');
    $archiveID     = sanitize_text_field($_GET['archiveID'] ?? '');
    $sort          = sanitize_text_field($_GET['sort'] ?? '');
    $category      = sanitize_text_field($_GET['category'] ?? '');
    $source        = sanitize_text_field($_GET['source'] ?? 'ALL');

    $limit = 20;
    $offset = ($page - 1) * $limit;

    $search_body = [];
    if (!empty($query)) $search_body['fulltext'] = $query;
    if (!empty($orientation)) $search_body['orientation'] = $orientation;
    if (!empty($archiveID)) $search_body['archiveID'] = intval($archiveID);
    if ($modelReleased === 'true' || $modelReleased === 'false') $search_body['modelReleased'] = ($modelReleased === 'true');
    if ($sort === 'newest')   $search_body['sortingTypeID'] = 4;
    elseif ($sort === 'oldest') $search_body['sortingTypeID'] = 3;
    elseif ($sort === 'relevant') $search_body['sortingTypeID'] = 2;

    $results = [];
    $total = 0;

    function solwee_api_call($endpoint, $body) {
        $res = wp_remote_post("https://api.solwee.com/api/v2/search/images/{$endpoint}", [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/json', 'X-WebID' => '57'],
            'body' => json_encode($body)
        ]);
        if (is_wp_error($res)) return ['totalCount' => 0, 'results' => []];
        return json_decode(wp_remote_retrieve_body($res), true) ?? ['totalCount' => 0, 'results' => []];
    }

    if (in_array($source, ['ALL', 'STOCK'])) {
        $creative_data = solwee_api_call('creative', array_merge($search_body, ['limit' => 100, 'offset' => 0]));
        $editorial_data = solwee_api_call('editorial', array_merge($search_body, ['limit' => 100, 'offset' => 0]));
        $merged = array_merge($creative_data['results'] ?? [], $editorial_data['results'] ?? []);
        usort($merged, function ($a, $b) {
            return strtotime($b['createdTime'] ?? '2000-01-01') - strtotime($a['createdTime'] ?? '2000-01-01');
        });
        $total = intval($creative_data['totalCount'] ?? 0) + intval($editorial_data['totalCount'] ?? 0);
        $results = array_slice($merged, $offset, $limit);
    }

    if (in_array($source, ['ALL', 'UPLOADED'])) {
        $upload_args = [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'author'         => $user_id,
            'post_mime_type' => 'image',
            's'              => $query,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'     => 'fastmedia_upload_status',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        $uploads = get_posts($upload_args);
        foreach ($uploads as $u) {
            $results[] = [
                'productID' => 'upload-' . $u->ID,
                'thumb260Url' => wp_get_attachment_image_url($u->ID, 'medium'),
                'source' => 'UP'
            ];
            $total++;
        }
    }

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

    <?= do_shortcode('[solwee_pagination page="' . $page . '" total="' . $total . '" limit="' . $limit . '"]') ?>

    <div class="solwee-masonry">
    <?php
    if (empty($results)) {
        echo "<p>No results found.</p>";
    } else {
        foreach ($results as $result) {
            $id = esc_attr($result['productID'] ?? 0);
            $thumb = esc_url($result['thumb260Url'] ?? $result['thumbUrl'] ?? '');
            $fallback = "/no-preview.jpg";
            $source = $result['source'] ?? 'ST';
            $label = $source === 'UP' ? 'UP' : 'ST';
            $label_title = $source === 'UP' ? 'Uploaded Image' : 'Stock Image';
            $label_class = $source === 'UP' ? 'upload' : '';

            echo "<div class='solwee-tile'>
                <a href='" . ($source === 'UP' ? '#' : "/image-detail/?productID={$id}") . "' target='_blank'>
                    <img src='{$thumb}' alt='Image {$id}' loading='lazy' onerror=\"this.onerror=null;this.src='{$fallback}';\" />
                </a>
                <div class='label-badge {$label_class}' title='{$label_title}'>{$label}</div>
                " . fastmedia_project_toggle_ui($id, $source) . "
            </div>";
        }
    }
    ?>
    </div>

    <?= do_shortcode('[solwee_pagination page="' . $page . '" total="' . $total . '" limit="' . $limit . '"]') ?>

    <?php
    return ob_get_clean();
});
