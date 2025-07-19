// ✅ MAGIC SEARCH – COMBINED RESULTS VIEW with 3-View System (FIXED WITH SINGLE PROJECT TOGGLE)
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
            $file_path = get_attached_file($u->ID);
            $file_size = $file_path && file_exists($file_path) ? filesize($file_path) : 0;
            $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
            
            $results[] = [
                'productID' => 'upload-' . $u->ID,
                'thumb260Url' => wp_get_attachment_image_url($u->ID, 'medium'),
                'source' => 'UP',
                'title' => get_the_title($u->ID),
                'uploadDate' => get_the_date('Y-m-d', $u->ID),
                'fileSize' => $file_size,
                'fileExt' => strtoupper($file_ext),
                'author' => get_the_author_meta('display_name', $u->post_author)
            ];
            $total++;
        }
    }

    ob_start();
    ?>

    <style>
    /* View controls */
    .fm-pv-view-controls {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        gap: 10px;
    }
    
    .search-results-info {
        font-size: 15px;
        font-weight: 500;
    }
    
    .search-controls-right {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .fm-pv-select {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .fm-pv-view-switcher {
        display: flex;
        gap: 4px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 2px;
    }
    
    .fm-pv-view-btn {
        padding: 4px 8px;
        border: none;
        background: transparent;
        border-radius: 3px;
        cursor: pointer;
        color: #333;
    }
    
    .fm-pv-view-btn.active {
        background: #e0e0e0;
    }
    
    .fm-pv-view-btn:hover {
        background: #f0f0f0;
    }
    
    /* DEFAULT DETAIL VIEW */
    .search-results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .search-tile {
        position: relative;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .search-tile:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #ccc;
    }
    
    .search-tile img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    
    .search-image-wrapper {
        position: relative;
    }
    
    /* Labels */
    .search-label {
        font-size: 10px;
        font-weight: bold;
        padding: 3px 6px;
        border-radius: 3px;
        color: white;
        display: inline-block;
        margin-right: 4px;
    }
    
    .search-label-ST { background: #0073aa; }
    .search-label-UP { background: #00a65a; }
    
    /* Metadata styling */
    .search-tile-details {
        margin-top: 10px;
    }
    
    .search-tile-details strong {
        font-size: 13px;
        margin: 0 0 4px 0;
        display: block;
        overflow: hidden;
        line-height: 1.5;
        max-height: 3em;
        word-break: break-word;
        flex-shrink: 0;
    }
    
    .search-tile-details small {
        font-size: 11px;
        color: #666;
    }
    
    /* Detail view actions */
    .search-detail-actions {
        margin-top: 10px;
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    /* Hide detail actions in other views */
    .search-results-grid.mosaic-view .search-detail-actions,
    .search-results-grid.list-view .search-detail-actions {
        display: none !important;
    }
    
    /* Fix rating button size */
    .fm-rating-overlay button {
        font-size: 16px !important;
        line-height: 1 !important;
        padding: 2px 6px !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    /* Hide list view elements by default */
    .search-list-meta,
    .search-list-labels,
    .search-list-toggle,
    .search-list-metadata,
    .search-list-rating {
        display: none !important;
    }
    
    /* MOSAIC VIEW */
    .search-results-grid.mosaic-view {
        display: block !important;
        column-count: 4;
        column-gap: 10px;
        grid: none;
    }
    
    @media (max-width: 1200px) {
        .search-results-grid.mosaic-view { column-count: 3; }
    }
    @media (max-width: 768px) {
        .search-results-grid.mosaic-view { column-count: 2; }
    }
    @media (max-width: 480px) {
        .search-results-grid.mosaic-view { column-count: 1; }
    }
    
    .search-results-grid.mosaic-view .search-tile {
        break-inside: avoid;
        margin-bottom: 10px;
        display: inline-block;
        width: 100%;
        padding: 4px;
    }
    
    .search-results-grid.mosaic-view .search-tile-details,
    .search-results-grid.mosaic-view .search-detail-actions {
        display: none !important;
    }
    
    /* LIST VIEW */
    .search-results-grid.list-view {
        display: block !important;
        grid: none;
    }
    
    .search-results-grid.list-view .search-tile {
        display: flex;
        align-items: stretch;
        gap: 0;
        margin-bottom: 0;
        padding: 0;
        height: 80px;
        border: none;
        border-bottom: none;
        background: #fafafa;
        position: relative;
        border-radius: 0;
    }
    
    .search-results-grid.list-view .search-tile::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #666;
    }
    
    .search-results-grid.list-view .search-tile:hover {
        background: #f0f0f0;
        box-shadow: none;
    }
    
    .search-results-grid.list-view .search-tile:hover::after {
        background: #444;
    }
    
    .search-results-grid.list-view .search-tile:last-child::after {
        display: none;
    }
    
    .search-results-grid.list-view .search-image-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        align-self: center;
        margin: 0 12px;
    }
    
    .search-results-grid.list-view .search-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .search-results-grid.list-view .search-tile-details {
        display: none !important;
    }
    
    /* Show list view elements only in list view */
    .search-results-grid.list-view .search-list-meta,
    .search-results-grid.list-view .search-list-labels,
    .search-results-grid.list-view .search-list-toggle,
    .search-results-grid.list-view .search-list-metadata,
    .search-results-grid.list-view .search-list-rating {
        display: flex !important;
    }
    
    /* List view: Metadata section */
    .search-results-grid.list-view .search-list-meta {
        flex: 0 0 220px;
        flex-direction: column;
        justify-content: center;
        padding: 10px 15px;
        background: #f8f8f8;
        height: 100%;
        position: relative;
        border-left: none;
        border-right: none;
        min-height: 0;
    }
    
    .search-results-grid.list-view .search-list-meta::before {
        content: '';
        position: absolute;
        left: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .search-results-grid.list-view .search-list-meta::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .search-results-grid.list-view .search-list-meta strong {
        font-size: 13px;
        margin: 0 0 4px 0;
        display: block;
        overflow: hidden;
        line-height: 1.5;
        max-height: 3em;
        word-break: break-word;
        flex-shrink: 0;
    }
    
    .search-results-grid.list-view .search-list-meta small {
        font-size: 11px;
        color: #666;
    }
    
    /* List view: Labels section */
    .search-results-grid.list-view .search-list-labels {
        width: 100px;
        padding: 0 15px;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .search-results-grid.list-view .search-list-labels::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    /* List view: Toggle section */
    .search-results-grid.list-view .search-list-toggle {
        padding: 0 15px;
        align-items: center;
        position: relative;
    }
    
    .search-results-grid.list-view .search-list-toggle::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    /* List view: Metadata section */
    .search-results-grid.list-view .search-list-metadata {
        padding: 0 15px;
        align-items: center;
        flex: 0 0 180px;
        position: relative;
    }
    
    .search-results-grid.list-view .search-list-metadata::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    /* List view: Rating section */
    .search-results-grid.list-view .search-list-rating {
        padding: 0 15px;
        align-items: center;
    }
    </style>

    <div class="fm-pv-view-controls">
        <div class="search-results-info">
            <?= $total ?> results found
        </div>
        <div class="search-controls-right">
            <select class="fm-pv-select" onchange="sortAssets(this.value)">
                <option value="relevant" <?php echo selected($sort === 'relevant'); ?>>Most Relevant</option>
                <option value="newest" <?php echo selected($sort === 'newest'); ?>>Newest First</option>
                <option value="oldest" <?php echo selected($sort === 'oldest'); ?>>Oldest First</option>
            </select>
            <div class="fm-pv-view-switcher">
                <button onclick="setView('detail')" class="fm-pv-view-btn active" title="Detail View">⊞</button>
                <button onclick="setView('mosaic')" class="fm-pv-view-btn" title="Mosaic View">▦</button>
                <button onclick="setView('list')" class="fm-pv-view-btn" title="List View">☰</button>
            </div>
        </div>
    </div>

    <?= do_shortcode('[solwee_pagination page="' . $page . '" total="' . $total . '" limit="' . $limit . '"]') ?>

    <div class="search-results-grid detail-view">
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
            
            // Extract metadata
            $title = $result['title'] ?? 'Image ' . $id;
            $author = $result['author'] ?? ($result['copyrightHolder'] ?? 'Unknown');
            $date = $result['uploadDate'] ?? ($result['createdTime'] ?? date('Y-m-d'));
            $fileSize = isset($result['fileSize']) ? size_format($result['fileSize']) : 'N/A';
            $fileExt = $result['fileExt'] ?? 'JPG';
            $file_ext_upper = strtoupper($fileExt);
            $file_size_formatted = $fileSize;
            
            // For stock images, extract dimensions if available
            if ($source === 'ST' && isset($result['width']) && isset($result['height'])) {
                $dimensions = $result['width'] . ' × ' . $result['height'];
            } else {
                $dimensions = 'N/A';
            }
            
            $detail_link = $source === 'UP' ? '/asset-detail/?id=' . str_replace('upload-', '', $id) : '/image-detail/?productID=' . $id;
            
            // Extract real attachment ID for uploads
            $real_attachment_id = null;
            if ($source === 'UP') {
                $real_attachment_id = str_replace('upload-', '', $id);
            }
            ?>
            <div class="search-tile" data-id="<?php echo $id; ?>" data-source="<?php echo $source; ?>">
                <div class="search-image-wrapper">
                    <a href="<?php echo esc_url($detail_link); ?>" target="_blank">
                        <img src="<?php echo $thumb; ?>" alt="<?php echo esc_attr($title); ?>" 
                             loading="lazy" onerror="this.onerror=null;this.src='<?php echo $fallback; ?>';" />
                    </a>
                </div>
                
                <!-- DETAIL VIEW ELEMENTS -->
                <div class="search-tile-details">
                    <strong>
                        <?php echo $title; ?>
                        <?php if ($fileExt): ?>
                            <span style="font-size: 11px; color: #666; font-weight: normal;">(<?php echo esc_html($file_ext_upper); ?>)</span>
                        <?php endif; ?>
                    </strong>
                    <small style="color: #666; display: block;"><?php echo $date; ?> • <?php echo $file_size_formatted; ?></small>
                </div>
                
                <div class="search-detail-actions">
                    <span class="search-label search-label-<?php echo $label; ?>"><?php echo $label; ?></span>
                    
                    <?php if (function_exists('fastmedia_rating_ui') && $real_attachment_id): ?>
                        <?php echo fastmedia_rating_ui($real_attachment_id); ?>
                    <?php endif; ?>
                    
                    <?php if (function_exists('fastmedia_project_toggle_ui')): ?>
                        <?php echo fastmedia_project_toggle_ui($id, $source); ?>
                    <?php endif; ?>
                </div>
                
                <!-- LIST VIEW ELEMENTS (hidden by default) -->
                <div class="search-list-meta">
                    <strong>
                        <?php echo $title; ?>
                        <?php if ($fileExt): ?>
                            <span style="font-size: 11px; color: #666; font-weight: normal;">(<?php echo esc_html($file_ext_upper); ?>)</span>
                        <?php endif; ?>
                    </strong>
                </div>
                
                <div class="search-list-labels">
                    <span class="search-label search-label-<?php echo $label; ?>"><?php echo $label; ?></span>
                </div>
                
                <div class="search-list-toggle">
                    <?php if (function_exists('fastmedia_project_toggle_ui')): ?>
                        <?php echo fastmedia_project_toggle_ui($id, $source); ?>
                    <?php endif; ?>
                </div>
                
                <div class="search-list-metadata">
                    <small style="color: #666;"><?php echo $date; ?> • <?php echo $file_size_formatted; ?></small>
                </div>
                
                <div class="search-list-rating">
                    <?php if (function_exists('fastmedia_rating_ui') && $real_attachment_id): ?>
                        <?php echo fastmedia_rating_ui($real_attachment_id); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
    ?>
    </div>

    <?= do_shortcode('[solwee_pagination page="' . $page . '" total="' . $total . '" limit="' . $limit . '"]') ?>

    <script>
    // Add nonce to page for AJAX calls
    window.fastmedia_nonce = '<?php echo wp_create_nonce("fastmedia_project_nonce"); ?>';
    
    function sortAssets(sortBy) {
        // Reload page with sort parameter
        const url = new URL(window.location);
        url.searchParams.set('sort', sortBy);
        window.location.href = url.toString();
    }
    
    function setView(viewType) {
        const grid = document.querySelector('.search-results-grid');
        const buttons = document.querySelectorAll('.fm-pv-view-btn');
        
        // Remove all view classes
        grid.classList.remove('detail-view', 'mosaic-view', 'list-view');
        buttons.forEach(btn => btn.classList.remove('active'));
        
        // Add new view class
        grid.classList.add(viewType + '-view');
        
        // Update active button
        const activeBtn = Array.from(buttons).find(btn => 
            (viewType === 'detail' && btn.textContent === '⊞') ||
            (viewType === 'mosaic' && btn.textContent === '▦') ||
            (viewType === 'list' && btn.textContent === '☰')
        );
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
        
        localStorage.setItem('fastmedia_view', viewType);
    }
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        // Load saved view preference
        const savedView = localStorage.getItem('fastmedia_view') || 'detail';
        if (savedView !== 'detail') {
            setView(savedView);
        }
    });
    </script>

    <?php
    return ob_get_clean();
});
