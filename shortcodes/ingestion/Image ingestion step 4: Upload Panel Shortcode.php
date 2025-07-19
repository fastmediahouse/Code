/**
 * FastMedia Enhanced Upload Panel - REFINED VERSION
 * 4-View System with cleaner layout
 */

add_shortcode('upload_panel', function () {
    if (!is_user_logged_in()) return '<p>Please log in to upload and approve images.</p>';
    $user_id = get_current_user_id();

    // Upload handler with UP label auto-assignment
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['fastmedia_upload_file'])) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        foreach ($_FILES['fastmedia_upload_file']['name'] as $i => $name) {
            $file_array = [
                'name'     => $_FILES['fastmedia_upload_file']['name'][$i],
                'tmp_name' => $_FILES['fastmedia_upload_file']['tmp_name'][$i],
                'type'     => $_FILES['fastmedia_upload_file']['type'][$i]
            ];
            $id = media_handle_sideload($file_array, 0);
            if (!is_wp_error($id)) {
                update_post_meta($id, 'fastmedia_upload_status', 'pending');
                add_post_meta($id, 'fastmedia_activity_log', 'Uploaded on ' . current_time('Y-m-d H:i'));
                
                // AUTO ADD UP LABEL - Protected from removal
                $labels = get_field('fastmedia_asset_labels', $id) ?: [];
                if (!in_array('UP', $labels)) {
                    $labels[] = 'UP';
                    update_field('fastmedia_asset_labels', $labels, $id);
                }
            }
        }

        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    // Bulk operations handler
    if (!empty($_POST['bulk_action']) && !empty($_POST['selected_ids'])) {
        $action = $_POST['bulk_action'];
        $ids = $_POST['selected_ids'];
        $reason = sanitize_text_field($_POST['bulk_reject_reason'] ?? 'No reason provided');
        
        foreach ($ids as $id) {
            if (get_post_field('post_author', $id) == $user_id) {
                switch ($action) {
                    case 'approve':
                        delete_post_meta($id, 'fastmedia_upload_status');
                        add_post_meta($id, 'fastmedia_activity_log', 'Bulk approved on ' . current_time('Y-m-d H:i'));
                        break;
                    case 'reject':
                        add_post_meta($id, 'fastmedia_activity_log', 'Rejected on ' . current_time('Y-m-d H:i') . ' - Reason: ' . $reason);
                        wp_delete_attachment($id, true);
                        break;
                }
            }
        }
        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    // Individual approve handler
    if (!empty($_POST['fastmedia_approve_ids'])) {
        foreach ($_POST['fastmedia_approve_ids'] as $id) {
            if (get_post_field('post_author', $id) == $user_id) {
                delete_post_meta($id, 'fastmedia_upload_status');

                if (!empty($_POST['meta'][$id])) {
                    foreach ($_POST['meta'][$id] as $key => $value) {
                        update_post_meta($id, $key, sanitize_text_field($value));
                    }
                }

                if (!empty($_POST['meta_map'][$id])) {
                    foreach ($_POST['meta_map'][$id] as $unmatched => $acf_target) {
                        $unmatched_value = get_post_meta($id, $unmatched, true);
                        if (!empty($unmatched_value)) {
                            update_field($acf_target, sanitize_text_field($unmatched_value), $id);
                        }
                    }
                }

                add_post_meta($id, 'fastmedia_activity_log', "Approved on " . current_time('Y-m-d H:i'));
            }
        }

        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    // Individual reject handler with reason
    if (!empty($_POST['fastmedia_reject_ids'])) {
        foreach ($_POST['fastmedia_reject_ids'] as $id) {
            if (get_post_field('post_author', $id) == $user_id) {
                $reason = sanitize_text_field($_POST['reject_reason_' . $id] ?? 'No reason provided');
                add_post_meta($id, 'fastmedia_activity_log', 'Rejected on ' . current_time('Y-m-d H:i') . ' - Reason: ' . $reason);
                wp_delete_attachment($id, true);
            }
        }
        echo "<script>setTimeout(() => window.location.hash = '#upload', 200);</script>";
    }

    // Sorting implementation
    $orderby = 'date';
    $order = 'DESC';
    
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'date-asc':
                $order = 'ASC';
                break;
            case 'name-asc':
                $orderby = 'title';
                $order = 'ASC';
                break;
            case 'name-desc':
                $orderby = 'title';
                $order = 'DESC';
                break;
        }
    }

    // Load all pending uploads with sorting
    $attachments = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => $user_id,
        'meta_key' => 'fastmedia_upload_status',
        'meta_value' => 'pending',
        'posts_per_page' => -1,
        'orderby' => $orderby,
        'order' => $order
    ]);

    $acf_fields = [
        'imagereference', 'secondary_id', 'caption', 'tags', 'credit', 'creator', 'location', 'title', 'ref_code',
        'copyright', 'capture_date', 'camera_make', 'camera_model', 'software', 'color_space', 'license_type',
        'license_summary', 'notes', 'collection', 'filename', 'file_size', 'image_dimensions', 'file_type', 'edit_history'
    ];
    
    $label_map = [
        'ST' => 'Stock Image', 
        'UP' => 'User Upload', 
        'BR' => 'Brand Approved',
        'LO' => 'Logo', 
        'FI' => 'Final Approved', 
        'PH' => 'Photography',
        'VI' => 'Video', 
        'VC' => 'Vector', 
        'AI' => 'AI Generated',
        'AN' => 'Animation'
    ];

    ob_start();
    ?>
    
    <style>
    /* Enhanced Upload Panel Styles - Refined */
    .fmu-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    /* Drag & Drop Upload Zone */
    .fmu-dropzone {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        border: 3px dashed #dee2e6;
        border-radius: 16px;
        padding: 60px 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .fmu-dropzone:hover {
        border-color: #0073aa;
        background: linear-gradient(to bottom, #e7f3ff, #cce5ff);
        transform: scale(1.01);
    }
    
    .fmu-dropzone.drag-over {
        border-color: #0056b3;
        background: linear-gradient(to bottom, #cce5ff, #b3d9ff);
        transform: scale(1.02);
    }
    
    .fmu-dropzone input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    /* View Controls - Matching Search Style */
    .fm-pv-view-controls {
        display: flex;
        justify-content: space-between;
        margin: 30px 0 20px;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .search-results-info {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }
    
    .search-results-info .count-badge {
        background: #0073aa;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        margin-left: 8px;
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
    
    /* 4-View Switcher */
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
        font-size: 13px;
        transition: all 0.2s;
    }
    
    .fm-pv-view-btn.active {
        background: #e0e0e0;
    }
    
    .fm-pv-view-btn:hover {
        background: #f0f0f0;
    }
    
    /* Bulk Actions Bar */
    .fmu-bulk-bar {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: none;
        align-items: center;
        gap: 15px;
    }
    
    .fmu-bulk-bar.active {
        display: flex;
    }
    
    .fmu-bulk-bar button {
        padding: 6px 16px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .fmu-bulk-bar button:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }
    
    /* Grid Container */
    .fmu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }
    
    /* DETAIL VIEW (Default) - Keep nice card style */
    .fmu-grid.detail-view .fmu-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    
    .fmu-grid.detail-view .fmu-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    /* MOSAIC VIEW - Match search results */
    .fmu-grid.mosaic-view {
        display: block !important;
        column-count: 4;
        column-gap: 10px;
    }
    
    @media (max-width: 1200px) {
        .fmu-grid.mosaic-view { column-count: 3; }
    }
    
    @media (max-width: 768px) {
        .fmu-grid.mosaic-view { column-count: 2; }
    }
    
    @media (max-width: 480px) {
        .fmu-grid.mosaic-view { column-count: 1; }
    }
    
    .fmu-grid.mosaic-view .fmu-card {
        break-inside: avoid;
        margin-bottom: 10px;
        display: inline-block;
        width: 100%;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #e0e0e0;
        background: white;
        padding: 4px;
    }
    
    .fmu-grid.mosaic-view .fmu-card-body,
    .fmu-grid.mosaic-view .fmu-metadata {
        display: none !important;
    }
    
    /* LIST VIEW - Match search results */
    .fmu-grid.list-view {
        display: block !important;
    }
    
    .fmu-grid.list-view .fmu-card {
        display: flex;
        align-items: stretch;
        gap: 0;
        margin-bottom: 0;
        padding: 0;
        height: 80px;
        border: none;
        border-radius: 0;
        background: #fafafa;
        position: relative;
        box-shadow: none;
    }
    
    .fmu-grid.list-view .fmu-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #666;
    }
    
    .fmu-grid.list-view .fmu-card:hover {
        background: #f0f0f0;
        transform: none;
        box-shadow: none;
    }
    
    .fmu-grid.list-view .fmu-card:last-child::after {
        display: none;
    }
    
    .fmu-grid.list-view .fmu-card-checkbox {
        position: relative;
        opacity: 1;
        width: auto;
        height: auto;
        margin: 0 12px;
        align-self: center;
    }
    
    .fmu-grid.list-view .fmu-card-image {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        align-self: center;
        margin-right: 12px;
        padding-top: 0;
    }
    
    .fmu-grid.list-view .fmu-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .fmu-grid.list-view .fmu-card-body {
        display: none !important;
    }
    
    .fmu-grid.list-view .fmu-metadata {
        display: none !important;
    }
    
    /* List view special elements */
    .fmu-list-meta {
        display: none;
        flex: 0 0 220px;
        flex-direction: column;
        justify-content: center;
        padding: 10px 15px;
        background: #f8f8f8;
        height: 100%;
        position: relative;
    }
    
    .fmu-grid.list-view .fmu-list-meta {
        display: flex !important;
    }
    
    .fmu-list-meta::before,
    .fmu-list-meta::after {
        content: '';
        position: absolute;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fmu-list-meta::before { left: 0; }
    .fmu-list-meta::after { right: 0; }
    
    .fmu-list-labels {
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
        gap: 6px;
        position: relative;
    }
    
    .fmu-grid.list-view .fmu-list-labels {
        display: flex !important;
    }
    
    .fmu-list-labels::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fmu-list-actions {
        display: none;
        align-items: center;
        padding: 0 20px;
        gap: 8px;
        margin-left: auto;
    }
    
    .fmu-grid.list-view .fmu-list-actions {
        display: flex !important;
    }
    
    /* COMPACT GRID VIEW (4th view) */
    .fmu-grid.compact-view {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
    }
    
    .fmu-grid.compact-view .fmu-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        transition: all 0.2s;
    }
    
    .fmu-grid.compact-view .fmu-card:hover {
        border-color: #0073aa;
        transform: scale(1.02);
    }
    
    .fmu-grid.compact-view .fmu-card-body {
        padding: 8px !important;
    }
    
    .fmu-grid.compact-view .fmu-card-body h4 {
        font-size: 12px !important;
        margin-bottom: 4px !important;
    }
    
    .fmu-grid.compact-view .fmu-metadata {
        display: none !important;
    }
    
    .fmu-grid.compact-view .fmu-action-buttons {
        margin-top: 8px !important;
    }
    
    .fmu-grid.compact-view .fmu-btn {
        padding: 4px 8px !important;
        font-size: 12px !important;
    }
    
    /* Card Components */
    .fmu-card {
        position: relative;
    }
    
    .fmu-card-checkbox {
        position: absolute;
        top: 12px;
        left: 12px;
        width: 24px;
        height: 24px;
        z-index: 10;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .fmu-card:hover .fmu-card-checkbox,
    .fmu-card-checkbox:checked {
        opacity: 1;
    }
    
    .fmu-card-image {
        position: relative;
        padding-top: 66.67%;
        background: #f8f9fa;
        overflow: hidden;
    }
    
    .fmu-card-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Labels - Universal Style from Search */
    .fmu-labels {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .search-label {
        font-size: 10px;
        font-weight: bold;
        padding: 3px 6px;
        border-radius: 3px;
        color: white;
        display: inline-block;
        text-transform: uppercase;
    }
    
    .search-label-ST { background: #0073aa; }
    .search-label-UP { background: #00a65a; }
    .search-label-BR { background: #17a2b8; }
    .search-label-LO { background: #ff7700; }
    .search-label-FI { background: #e6b800; }
    .search-label-PH { background: #008080; }
    .search-label-VI { background: #7a4dc9; }
    .search-label-VC { background: #c62828; }
    .search-label-AI { background: #6c757d; }
    .search-label-AN { background: #9c27b0; }
    
    /* Label Dropdown */
    .fmu-label-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .fmu-label-dropdown-content {
        display: none;
        position: absolute;
        background: white;
        min-width: 320px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        border-radius: 12px;
        padding: 20px;
        z-index: 100;
        top: 100%;
        left: 0;
        margin-top: 8px;
    }
    
    .fmu-label-dropdown:hover .fmu-label-dropdown-content {
        display: block;
    }
    
    /* Suggest for Brand Button - Universal Style */
    .fmu-suggest-btn {
        background: #6c757d;
        color: white;
        padding: 3px 10px;
        border: none;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .fmu-suggest-btn:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }
    
    /* Metadata Panel */
    .fmu-metadata {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 16px;
    }
    
    .fmu-field-primary {
        background: #e3f2fd;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    
    .fmu-field-secondary {
        background: #fff8dc;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    
    .fmu-field {
        margin-bottom: 16px;
    }
    
    .fmu-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #495057;
    }
    
    .fmu-field input,
    .fmu-field select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 14px;
    }
    
    /* IPTC Viewer */
    .fmu-iptc-viewer {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px;
        margin-top: 16px;
        position: relative;
    }
    
    .fmu-iptc-copy {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 8px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
    }
    
    /* Buttons */
    .fmu-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
    }
    
    .fmu-btn-primary {
        background: #0073aa;
        color: white;
    }
    
    .fmu-btn-success {
        background: #28a745;
        color: white;
    }
    
    .fmu-btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .fmu-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    /* Rejection Modal */
    .fmu-reject-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .fmu-reject-modal.active {
        display: flex;
    }
    
    .fmu-reject-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .fmu-reject-content h3 {
        margin: 0 0 20px 0;
        font-size: 20px;
    }
    
    .fmu-reject-content textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
        font-size: 14px;
    }
    
    .fmu-reject-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        justify-content: flex-end;
    }
    
    /* Rating UI inline style */
    .fm-rating-overlay {
        display: inline-flex;
        align-items: center;
    }
    
    .fm-rating-overlay button {
        font-size: 16px !important;
        line-height: 1 !important;
        padding: 2px 6px !important;
        min-width: auto !important;
        height: auto !important;
    }
    </style>

    <div class="fmu-wrapper">
        <h2 style="font-size: 32px; font-weight: 700; margin-bottom: 32px;">📄 Upload New Assets</h2>
        <?php echo fm_get_storage_warning(); ?>
        
        <!-- Modern Drag & Drop Upload -->
        <form method="post" enctype="multipart/form-data">
            <div class="fmu-dropzone" id="dropzone">
                <input type="file" name="fastmedia_upload_file[]" id="file-input" multiple accept="image/*" required>
                <div style="font-size: 64px; margin-bottom: 16px; opacity: 0.6;">📤</div>
                <h3 style="font-size: 24px; margin: 0 0 8px 0; color: #495057;">Drag and drop files here</h3>
                <p style="color: #6c757d; margin-bottom: 24px;">or click to browse your computer</p>
                <button type="button" class="fmu-btn fmu-btn-primary" onclick="document.getElementById('file-input').click()">
                    Choose Files
                </button>
                <p style="color: #6c757d; margin-top: 16px; font-size: 14px;">
                    Supports: JPG, PNG, GIF, WebP • Max 50MB per file
                </p>
            </div>
            
            <div id="file-preview" style="display: none; margin-top: 20px;">
                <h4>Selected Files:</h4>
                <ul id="file-list"></ul>
                <button type="submit" class="fmu-btn fmu-btn-success" style="width: 100%; padding: 16px; font-size: 18px; margin-top: 16px;">
                    Upload All Files
                </button>
            </div>
        </form>

        <?php if (!empty($attachments)): ?>
        
        <!-- View Controls & Sorting -->
        <div class="fm-pv-view-controls">
            <h3 class="search-results-info">
                📂 Pending Review 
                <span class="count-badge"><?php echo count($attachments); ?></span>
            </h3>
            
            <div class="search-controls-right">
                <!-- Sorting Dropdown -->
                <select class="fm-pv-select" onchange="window.location.href='?sort=' + this.value + '#upload'">
                    <option value="date-desc" <?php selected($_GET['sort'] ?? '', 'date-desc'); ?>>Newest First</option>
                    <option value="date-asc" <?php selected($_GET['sort'] ?? '', 'date-asc'); ?>>Oldest First</option>
                    <option value="name-asc" <?php selected($_GET['sort'] ?? '', 'name-asc'); ?>>Name A-Z</option>
                    <option value="name-desc" <?php selected($_GET['sort'] ?? '', 'name-desc'); ?>>Name Z-A</option>
                </select>
                
                <!-- 4-View System -->
                <div class="fm-pv-view-switcher">
                    <button type="button" class="fm-pv-view-btn active" onclick="setView('detail', this)" title="Detail View">⊞ Detail</button>
                    <button type="button" class="fm-pv-view-btn" onclick="setView('mosaic', this)" title="Mosaic View">▦ Mosaic</button>
                    <button type="button" class="fm-pv-view-btn" onclick="setView('list', this)" title="List View">☰ List</button>
                    <button type="button" class="fm-pv-view-btn" onclick="setView('compact', this)" title="Compact Grid">⊡ Compact</button>
                </div>
            </div>
        </div>
        
        <!-- Bulk Actions Bar -->
        <div class="fmu-bulk-bar" id="bulk-bar">
            <strong style="font-size: 16px;">
                <span id="selected-count">0</span> selected
            </strong>
            <button type="button" onclick="selectAll()">Select All</button>
            <button type="button" onclick="deselectAll()">Deselect All</button>
            <div style="margin-left: auto; display: flex; gap: 10px;">
                <button type="button" onclick="bulkAction('approve')" class="fmu-btn fmu-btn-success">
                    ✅ Bulk Approve
                </button>
                <button type="button" onclick="bulkAction('reject')" class="fmu-btn fmu-btn-danger">
                    🗑️ Bulk Reject
                </button>
            </div>
        </div>
        
        <form method="post" id="main-form">
            <!-- Hidden fields for bulk actions -->
            <input type="hidden" name="bulk_action" id="bulk-action">
            <input type="hidden" name="bulk_reject_reason" id="bulk-reject-reason">
            
            <div class="fmu-grid detail-view" id="asset-grid">
            <?php foreach ($attachments as $a):
                $thumb = wp_get_attachment_image_src($a->ID, 'medium');
                $all_meta = get_post_meta($a->ID);
                $filename = basename(get_attached_file($a->ID));

                $acf_data = [];
                foreach ($acf_fields as $acf_key) {
                    $acf_data[$acf_key] = get_field($acf_key, $a->ID);
                }

                $unmatched = [];
                foreach ($all_meta as $key => $val) {
                    if (strpos($key, '_fastmedia_') === 0 && !in_array(str_replace('_fastmedia_', '', $key), $acf_fields)) {
                        $unmatched[$key] = $val[0];
                    }
                }

                $raw_iptc = get_post_meta($a->ID, '_fastmedia_raw_iptc', true);
                $iptc_field_count = substr_count($raw_iptc, 's:');
                
                // Ensure UP label exists and is protected
                $labels = get_field('fastmedia_asset_labels', $a->ID) ?: [];
                if (!in_array('UP', $labels)) {
                    $labels[] = 'UP';
                    update_field('fastmedia_asset_labels', $labels, $a->ID);
                }
                
                // Activity log
                $activity_log = get_post_meta($a->ID, 'fastmedia_activity_log');
            ?>
                <div class="fmu-card" data-id="<?php echo $a->ID; ?>">
                    <input type="checkbox" class="fmu-card-checkbox" name="selected_ids[]" value="<?php echo $a->ID; ?>" onchange="updateBulkBar()">
                    
                    <div class="fmu-card-image">
                        <img src="<?php echo esc_url($thumb[0]); ?>" alt="<?php echo esc_attr($filename); ?>">
                    </div>
                    
                    <!-- Detail/Compact view body -->
                    <div class="fmu-card-body" style="padding: 20px;">
                        <!-- Title & Meta -->
                        <h4 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600;">
                            <?php echo esc_html($filename); ?>
                        </h4>
                        <p style="color: #6c757d; font-size: 14px; margin-bottom: 16px;">
                            <?php echo get_the_date('M j, Y', $a->ID); ?> • 
                            <?php echo size_format(filesize(get_attached_file($a->ID))); ?>
                        </p>
                        
                        <!-- Labels with Rating inline -->
                        <div class="fmu-labels">
                            <?php foreach ($labels as $code): 
                                if (isset($label_map[$code])):
                            ?>
                                <span class="search-label search-label-<?php echo esc_attr($code); ?>">
                                    <?php echo esc_html($code); ?>
                                </span>
                            <?php endif; endforeach; ?>
                            
                            <div class="fmu-label-dropdown">
                                <button type="button" class="fmu-btn" style="padding: 4px 12px; font-size: 12px;">
                                    + Labels
                                </button>
                                <div class="fmu-label-dropdown-content">
                                    <h4 style="margin: 0 0 16px 0;">Manage Labels</h4>
                                    <?php foreach ($label_map as $code => $desc):
                                        $checked = in_array($code, $labels) ? 'checked' : '';
                                        $disabled = $code === 'UP' ? 'disabled title="UP label is protected"' : '';
                                    ?>
                                        <label style="display: flex; align-items: center; margin-bottom: 12px; cursor: pointer;">
                                            <input type="checkbox" value="<?php echo $code; ?>" <?php echo $checked; ?> <?php echo $disabled; ?> style="margin-right: 8px;">
                                            <span style="font-weight: 600; margin-right: 8px;"><?php echo $code; ?></span>
                                            <span style="color: #6c757d;"><?php echo $desc; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                    <button type="button" onclick="saveLabels(<?php echo $a->ID; ?>, this)" 
                                            class="fmu-btn fmu-btn-primary" style="width: 100%; margin-top: 12px;">
                                        Save Labels
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Suggest for Brand Button -->
                            <?php if (!in_array('BR', $labels)): ?>
                                <button type="button" onclick="suggestForBrand(<?php echo $a->ID; ?>)" 
                                        class="fmu-suggest-btn">
                                    Suggest for Brand
                                </button>
                            <?php endif; ?>
                            
                            <!-- Rating UI inline -->
                            <?php if (function_exists('fastmedia_rating_ui')): ?>
                                <div class="fm-rating-overlay">
                                    <?php echo fastmedia_rating_ui($a->ID); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Project Toggle -->
                        <?php if (function_exists('fastmedia_project_toggle_ui')): ?>
                            <div style="margin: 16px 0;">
                                <?php echo fastmedia_project_toggle_ui($a->ID); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div class="fmu-action-buttons" style="display: flex; gap: 8px; margin-top: 16px;">
                            <button type="submit" name="fastmedia_approve_ids[]" value="<?php echo $a->ID; ?>" 
                                    class="fmu-btn fmu-btn-success">
                                ✅ Approve
                            </button>
                            <button type="button" onclick="showRejectModal(<?php echo $a->ID; ?>)" 
                                    class="fmu-btn fmu-btn-danger">
                                🗑️ Reject
                            </button>
                        </div>
                    </div>
                    
                    <!-- List View Elements -->
                    <div class="fmu-list-meta">
                        <strong><?php echo esc_html($filename); ?></strong>
                        <small style="color: #666;">
                            <?php echo get_the_date('M j, Y', $a->ID); ?> • 
                            <?php echo size_format(filesize(get_attached_file($a->ID))); ?>
                        </small>
                    </div>
                    
                    <div class="fmu-list-labels">
                        <?php foreach ($labels as $code): 
                            if (isset($label_map[$code])):
                        ?>
                            <span class="search-label search-label-<?php echo esc_attr($code); ?>">
                                <?php echo esc_html($code); ?>
                            </span>
                        <?php endif; endforeach; ?>
                        
                        <?php if (function_exists('fastmedia_rating_ui')): ?>
                            <div class="fm-rating-overlay">
                                <?php echo fastmedia_rating_ui($a->ID); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="fmu-list-actions">
                        <button type="submit" name="fastmedia_approve_ids[]" value="<?php echo $a->ID; ?>" 
                                class="fmu-btn fmu-btn-success" style="padding: 6px 12px;">
                            ✅ Approve
                        </button>
                        <button type="button" onclick="showRejectModal(<?php echo $a->ID; ?>)" 
                                class="fmu-btn fmu-btn-danger" style="padding: 6px 12px;">
                            🗑️ Reject
                        </button>
                    </div>
                    
                    <!-- Metadata Management -->
                    <details class="fmu-metadata" id="metadata-<?php echo $a->ID; ?>">
                        <summary style="cursor: pointer; font-weight: 600; font-size: 16px; margin-bottom: 16px;">
                            📝 Metadata & Field Mapping
                        </summary>
                        
                        <!-- Primary Field -->
                        <div class="fmu-field-primary">
                            <label><strong>Asset ID (Filename)</strong></label>
                            <input type="text" value="<?php echo esc_attr($acf_data['filename'] ?: $filename); ?>" readonly>
                        </div>

                        <!-- Secondary Field -->
                        <div class="fmu-field-secondary">
                            <label>Secondary ID (Optional)</label>
                            <input type="text" name="meta[<?php echo $a->ID; ?>][_fastmedia_secondary_id]" 
                                   value="<?php echo esc_attr($acf_data['secondary_id']); ?>">
                        </div>

                        <!-- ACF Fields Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                            <?php foreach ($acf_data as $acf_field => $val):
                                if (in_array($acf_field, ['filename', 'secondary_id'])) continue; ?>
                                <div class="fmu-field">
                                    <label><?php echo ucfirst(str_replace('_', ' ', $acf_field)); ?></label>
                                    <input type="text" name="meta[<?php echo $a->ID; ?>][_fastmedia_<?php echo esc_attr($acf_field); ?>]" 
                                           value="<?php echo esc_attr($val); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Unmatched Fields Mapping -->
                        <?php if (!empty($unmatched)): ?>
                            <hr style="margin: 24px 0;">
                            <h4 style="margin-bottom: 16px;">🔗 Unmatched Fields - Map to ACF</h4>
                            <?php foreach ($unmatched as $raw_key => $raw_val): ?>
                                <div style="display: flex; gap: 16px; margin-bottom: 16px; align-items: end;">
                                    <div class="fmu-field" style="flex: 1;">
                                        <label><?php echo esc_html($raw_key); ?></label>
                                        <input type="text" disabled value="<?php echo esc_attr($raw_val); ?>" style="background: #e9ecef;">
                                    </div>
                                    <div class="fmu-field" style="width: 250px;">
                                        <label>Map to:</label>
                                        <select name="meta_map[<?php echo $a->ID; ?>][<?php echo esc_attr($raw_key); ?>]">
                                            <option value="">Select field...</option>
                                            <?php foreach ($acf_fields as $acf_option): ?>
                                                <option value="<?php echo esc_attr($acf_option); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $acf_option)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- IPTC Data Viewer -->
                        <?php if (!empty($raw_iptc)): ?>
                            <hr style="margin: 24px 0;">
                            <h4 style="margin-bottom: 16px;">
                                🔍 Raw IPTC Data 
                                <span style="font-weight: normal; color: #6c757d;">
                                    (<?php echo $iptc_field_count; ?> fields<?php echo $iptc_field_count < 5 ? ' - ⚠️ Low field count' : ''; ?>)
                                </span>
                            </h4>
                            <div class="fmu-iptc-viewer">
                                <button type="button" class="fmu-iptc-copy" onclick="copyIPTC(this)">
                                    📋 Copy
                                </button>
                                <pre style="margin: 0; font-size: 12px; white-space: pre-wrap; max-height: 200px; overflow-y: auto;">
<?php echo esc_html($raw_iptc); ?>
                                </pre>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Activity Log -->
                        <?php if (!empty($activity_log)): ?>
                            <hr style="margin: 24px 0;">
                            <h4 style="margin-bottom: 16px;">📊 Activity Log</h4>
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach (array_slice($activity_log, -5) as $log): ?>
                                    <li style="margin-bottom: 4px; color: #6c757d; font-size: 14px;">
                                        <?php echo esc_html($log); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </details>
                </div>
            <?php endforeach; ?>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <!-- Rejection Reason Modal -->
    <div class="fmu-reject-modal" id="reject-modal">
        <div class="fmu-reject-content">
            <h3>Reason for Rejection</h3>
            <textarea id="reject-reason-text" placeholder="Please provide a reason for rejecting this asset..."></textarea>
            <div class="fmu-reject-buttons">
                <button type="button" class="fmu-btn" onclick="closeRejectModal()">Cancel</button>
                <button type="button" class="fmu-btn fmu-btn-danger" onclick="confirmReject()">Confirm Reject</button>
            </div>
        </div>
    </div>

    <script>
    // Use same nonce as asset code
    window.fastmedia_nonce = '<?php echo wp_create_nonce("fastmedia_project_nonce"); ?>';
    
    let currentRejectId = null;
    
    // Drag and Drop functionality
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        dropzone.classList.add('drag-over');
    }
    
    function unhighlight(e) {
        dropzone.classList.remove('drag-over');
    }
    
    // Handle dropped files
    dropzone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    fileInput.addEventListener('change', function(e) {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        if (files.length > 0) {
            filePreview.style.display = 'block';
            fileList.innerHTML = '';
            
            ([...files]).forEach(file => {
                const li = document.createElement('li');
                li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileList.appendChild(li);
            });
        }
    }
    
    // View switching (4-view system)
    function setView(view, btn) {
        const grid = document.getElementById('asset-grid');
        const buttons = document.querySelectorAll('.fm-pv-view-btn');
        
        // Update grid class
        grid.className = 'fmu-grid ' + view + '-view';
        
        // Update active button
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Save preference
        localStorage.setItem('fmu_view', view);
    }
    
    // Bulk operations
    function updateBulkBar() {
        const checkboxes = document.querySelectorAll('.fmu-card-checkbox:checked');
        const bulkBar = document.getElementById('bulk-bar');
        const selectedCount = document.getElementById('selected-count');
        
        if (checkboxes.length > 0) {
            bulkBar.classList.add('active');
            selectedCount.textContent = checkboxes.length;
        } else {
            bulkBar.classList.remove('active');
        }
    }
    
    function selectAll() {
        document.querySelectorAll('.fmu-card-checkbox').forEach(cb => {
            cb.checked = true;
        });
        updateBulkBar();
    }
    
    function deselectAll() {
        document.querySelectorAll('.fmu-card-checkbox').forEach(cb => {
            cb.checked = false;
        });
        updateBulkBar();
    }
    
    function bulkAction(action) {
        const checkboxes = document.querySelectorAll('.fmu-card-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one asset');
            return;
        }
        
        if (action === 'reject') {
            const modal = document.getElementById('reject-modal');
            modal.classList.add('active');
            document.getElementById('reject-reason-text').value = '';
            
            // Set up for bulk reject
            window.bulkReject = true;
        } else {
            if (!confirm(`Approve ${checkboxes.length} assets?`)) return;
            
            document.getElementById('bulk-action').value = action;
            document.getElementById('main-form').submit();
        }
    }
    
    // Rejection modal functions
    function showRejectModal(assetId) {
        currentRejectId = assetId;
        window.bulkReject = false;
        const modal = document.getElementById('reject-modal');
        modal.classList.add('active');
        document.getElementById('reject-reason-text').value = '';
    }
    
    function closeRejectModal() {
        const modal = document.getElementById('reject-modal');
        modal.classList.remove('active');
        currentRejectId = null;
        window.bulkReject = false;
    }
    
    function confirmReject() {
        const reason = document.getElementById('reject-reason-text').value.trim();
        if (!reason) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        if (window.bulkReject) {
            // Bulk reject
            document.getElementById('bulk-action').value = 'reject';
            document.getElementById('bulk-reject-reason').value = reason;
            document.getElementById('main-form').submit();
        } else {
            // Individual reject
            const form = document.getElementById('main-form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reject_reason_' + currentRejectId;
            input.value = reason;
            form.appendChild(input);
            
            const rejectInput = document.createElement('input');
            rejectInput.type = 'hidden';
            rejectInput.name = 'fastmedia_reject_ids[]';
            rejectInput.value = currentRejectId;
            form.appendChild(rejectInput);
            
            form.submit();
        }
    }
    
    // Label management
    function saveLabels(assetId, btn) {
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        const container = btn.closest('.fmu-label-dropdown-content');
        const labels = [];
        container.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
            labels.push(cb.value);
        });
        
        // Always include UP label
        if (!labels.includes('UP')) {
            labels.push('UP');
        }
        
        const formData = new FormData();
        formData.append('action', 'fastmedia_save_labels');
        formData.append('attachment_id', assetId);
        formData.append('labels', JSON.stringify(labels));
        formData.append('nonce', window.fastmedia_nonce);
        
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.textContent = 'Saved!';
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error saving labels');
                btn.textContent = 'Save Labels';
                btn.disabled = false;
            }
        });
    }
    
    // Suggest for brand
    function suggestForBrand(assetId) {
        if (!confirm('Suggest this asset for brand approval?')) return;
        
        const formData = new FormData();
        formData.append('action', 'fastmedia_suggest_brand');
        formData.append('attachment_id', assetId);
        formData.append('nonce', window.fastmedia_nonce);
        
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Asset suggested for brand approval');
                location.reload();
            } else {
                alert('Error suggesting for brand');
            }
        });
    }
    
    // Copy IPTC data
    function copyIPTC(btn) {
        const pre = btn.nextElementSibling;
        const text = pre.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = '✓ Copied!';
            setTimeout(() => {
                btn.textContent = '📋 Copy';
            }, 2000);
        }).catch(() => {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            btn.textContent = '✓ Copied!';
            setTimeout(() => {
                btn.textContent = '📋 Copy';
            }, 2000);
        });
    }
    
    // Load saved view preference
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('fmu_view');
        if (savedView && savedView !== 'detail') {
            const viewMap = {
                'mosaic': 2,
                'list': 3,
                'compact': 4
            };
            const btn = document.querySelector(`.fm-pv-view-btn:nth-child(${viewMap[savedView]})`);
            if (btn) setView(savedView, btn);
        }
    });
    
    // Close modal on outside click
    document.getElementById('reject-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
    </script>
    <?php
    return ob_get_clean();
});

// Helper functions (if not already defined)
if (!function_exists('fm_get_storage_warning')) {
    function fm_get_storage_warning() {
        $upload_dir = wp_upload_dir();
        $space_used = 0;
        $space_allowed = 5 * 1024 * 1024 * 1024; // 5GB default
        
        if (function_exists('get_dirsize')) {
            $space_used = get_dirsize($upload_dir['basedir']);
        }
        
        $percent_used = ($space_used / $space_allowed) * 100;
        
        if ($percent_used > 90) {
            return '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <strong>⚠️ Storage Warning:</strong> You have used ' . size_format($space_used) . ' of ' . size_format($space_allowed) . ' (' . round($percent_used) . '%)
            </div>';
        } elseif ($percent_used > 75) {
            return '<div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <strong>ℹ️ Storage Info:</strong> ' . size_format($space_allowed - $space_used) . ' remaining
            </div>';
        }
        
        return '';
    }
}

// AJAX handler for brand suggestion
if (!has_action('wp_ajax_fastmedia_suggest_brand')) {
    add_action('wp_ajax_fastmedia_suggest_brand', function() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fastmedia_project_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $user_id = get_current_user_id();
        
        if (get_post_field('post_author', $attachment_id) != $user_id) {
            wp_send_json_error('Permission denied');
        }
        
        // Add BR label
        $labels = get_field('fastmedia_asset_labels', $attachment_id) ?: [];
        if (!in_array('BR', $labels)) {
            $labels[] = 'BR';
            update_field('fastmedia_asset_labels', $labels, $attachment_id);
        }
        
        // Mark as proposed
        update_post_meta($attachment_id, 'fastmedia_brand_proposed', 'yes');
        
        // Add to activity log
        $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: [];
        $user_info = get_userdata($user_id);
        $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' suggested for brand';
        update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
        
        wp_send_json_success(['message' => 'Suggested for brand approval']);
    });
}

// AJAX handler for saving labels (if not exists)
if (!has_action('wp_ajax_fastmedia_save_labels')) {
    add_action('wp_ajax_fastmedia_save_labels', function() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fastmedia_project_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $labels = json_decode(stripslashes($_POST['labels']), true);
        $user_id = get_current_user_id();
        
        if (get_post_field('post_author', $attachment_id) != $user_id) {
            wp_send_json_error('Permission denied');
        }
        
        // Ensure UP label stays
        if (!in_array('UP', $labels)) {
            $labels[] = 'UP';
        }
        
        update_field('fastmedia_asset_labels', $labels, $attachment_id);
        
        // Add to activity log
        $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: [];
        $user_info = get_userdata($user_id);
        $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' updated labels';
        update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
        
        wp_send_json_success(['labels' => $labels]);
    });
}
