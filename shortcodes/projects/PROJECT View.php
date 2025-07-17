/**
 * FastMedia Project View Page - Complete Implementation
 * Handles grid, list, mosaic, and contact sheet views
 * Use: [fastmedia_project_view]
 */

add_shortcode('fastmedia_project_view', function () {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/signin/">sign in</a> to view this project.</p>';
    }

    $user_id = get_current_user_id();
    $project = isset($_GET['project']) ? sanitize_text_field($_GET['project']) : '';
    $view_mode = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'grid';
    
    // Get user's projects
    $user_projects = get_user_meta($user_id, 'fastmedia_user_projects', true);
    $user_projects = is_array($user_projects) ? $user_projects : array('Default');
    
    // Check if project exists and user has access
    if (!$project || !in_array($project, $user_projects)) {
        return '<p>Invalid or missing project.</p>';
    }
    
    // Get project note
    $project_note = get_user_meta($user_id, 'fastmedia_project_note_' . $project, true);
    
    // Pagination parameters
    $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
    $posts_per_page = 50;
    $offset = ($page - 1) * $posts_per_page;
    
    // Apply sorting
    $orderby = 'date';
    $order = 'DESC';
    
    if (isset($_GET['sort'])) {
        $sort = sanitize_text_field($_GET['sort']);
        switch($sort) {
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
    
    // Label definitions
    $label_map = array(
        'ST' => 'Stock Image',
        'UP' => 'User Upload',
        'BR' => 'Brand Approved',
        'LO' => 'Logo',
        'FI' => 'Final Approved',
        'PH' => 'Photography',
        'VI' => 'Video',
        'VC' => 'Vector',
        'AI' => 'AI Generated',
        'AN' => 'Annotated'
    );
    
    // Get total count for pagination
    $count_args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => $user_id,
        'meta_query' => array(
            array(
                'key' => 'fastmedia_projects',
                'value' => serialize($project),
                'compare' => 'LIKE'
            )
        ),
        'posts_per_page' => -1,
        'fields' => 'ids'
    );
    $total_attachments = count(get_posts($count_args));
    
    // Get paginated attachments
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => $user_id,
        'meta_query' => array(
            array(
                'key' => 'fastmedia_projects',
                'value' => serialize($project),
                'compare' => 'LIKE'
            )
        ),
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,
        'orderby' => $orderby,
        'order' => $order
    );
    $attachments = get_posts($args);
    
    // Handle size sorting
    if (isset($_GET['sort']) && in_array($_GET['sort'], ['size-desc', 'size-asc'])) {
        $sort_order = $_GET['sort'];
        usort($attachments, function($a, $b) use ($sort_order) {
            $file_a = get_attached_file($a->ID);
            $file_b = get_attached_file($b->ID);
            $size_a = $file_a && file_exists($file_a) ? filesize($file_a) : 0;
            $size_b = $file_b && file_exists($file_b) ? filesize($file_b) : 0;
            return $sort_order === 'size-desc' ? $size_b - $size_a : $size_a - $size_b;
        });
    }
    
    // Handle bulk remove
    if (!empty($_POST['fm_pv_remove_selected']) && !empty($_POST['fm_pv_selected_ids'])) {
        $ids_to_remove = array_map('intval', $_POST['fm_pv_selected_ids']);
        $removed_count = 0;
        
        foreach ($ids_to_remove as $attachment_id) {
            $projects = get_post_meta($attachment_id, 'fastmedia_projects', true);
            if (is_array($projects) && in_array($project, $projects)) {
                $projects = array_filter($projects, function($p) use ($project) {
                    return $p !== $project;
                });
                update_post_meta($attachment_id, 'fastmedia_projects', array_values($projects));
                $removed_count++;
                
                // Log activity
                $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: array();
                $user_info = get_userdata($user_id);
                $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' removed from project: ' . $project;
                update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
            }
        }
        
        echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Removed ' . $removed_count . ' assets from project.</div>';
        
        // Refresh data
        $attachments = get_posts($args);
        $total_attachments = count(get_posts($count_args));
    }
    
    // Handle save note
    if (!empty($_POST['fm_pv_save_note']) && isset($_POST['fm_pv_project_note'])) {
        $note = sanitize_textarea_field($_POST['fm_pv_project_note']);
        update_user_meta($user_id, 'fastmedia_project_note_' . $project, $note);
        $project_note = $note;
        echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Project note saved!</div>';
    }

    ob_start();
    ?>
    
    <style>
    .fm-pv-wrapper {
        all: initial;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 16px;
        line-height: 1.5;
        color: #333;
        box-sizing: border-box;
        display: block;
    }
    
    .fm-pv-wrapper * {
        box-sizing: inherit;
    }
    
    .fm-pv-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Header */
    .fm-pv-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .fm-pv-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .fm-pv-title {
        font-size: 32px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .fm-pv-header-buttons {
        display: flex;
        gap: 10px;
    }
    
    .fm-pv-back-btn {
        background: #666;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s;
    }
    
    .fm-pv-back-btn:hover {
        background: #555;
        color: white;
        text-decoration: none;
    }
    
    /* Buttons */
    .fm-pv-btn {
        padding: 8px 16px;
        font-size: 13px;
        border: 1px solid #ddd;
        background: #fff;
        color: #333;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
    }
    
    .fm-pv-btn:hover {
        background: #f5f5f5;
        border-color: #bbb;
        text-decoration: none;
        color: #333;
    }
    
    .fm-pv-btn-primary {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }
    
    .fm-pv-btn-primary:hover {
        background: #45a049;
        color: white;
    }
    
    /* Note section */
    .fm-pv-note-section {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 0;
    }
    
    .fm-pv-note-textarea {
        width: 100%;
        min-height: 80px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        resize: vertical;
        margin-bottom: 10px;
    }
    
    /* View controls */
    .fm-pv-view-controls {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 15px;
        gap: 10px;
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
    
    /* Bulk actions bar */
    .fm-pv-bulkbar {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 20px;
        display: none;
        align-items: center;
        gap: 10px;
    }
    
    .fm-pv-bulkbar.active {
        display: flex;
    }
    
    .fm-pv-bulkbar button {
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
        color: #333;
        font-family: inherit;
    }
    
    .fm-pv-bulkbar button:hover {
        background: #f0f0f0;
    }
    
    /* Grid */
    .fm-pv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .fm-pv-tile {
        position: relative;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .fm-pv-tile:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #ccc;
    }
    
    .fm-pv-checkbox {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 3;
        width: 24px;
        height: 24px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .fm-pv-tile:hover .fm-pv-checkbox,
    .fm-pv-checkbox:checked {
        opacity: 1;
    }
    
    .fm-pv-image-wrapper {
        position: relative;
    }
    
    .fm-pv-image-wrapper img {
        width: 100%;
        height: auto;
        border-radius: 4px;
    }
    
    /* Toolbar */
    .fm-pv-toolbar {
        margin-top: 10px;
    }
    
    .fm-pv-toolbar-content {
        padding: 8px 0;
    }
    
    .fm-pv-toolbar-row {
        display: flex;
        gap: 6px;
        margin: 10px 0;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .fm-pv-toolbar-buttons {
        display: flex;
        gap: 4px;
    }
    
    .fm-pv-toolbar-buttons button,
    .fm-pv-toolbar-buttons a {
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 13px;
        padding: 6px 10px;
        text-align: center;
        cursor: pointer;
        text-decoration: none;
        color: #333;
    }
    
    .fm-pv-toolbar-buttons button:hover,
    .fm-pv-toolbar-buttons a:hover {
        background: #f0f0f0;
        text-decoration: none;
    }
    
    /* Labels */
    .fm-pv-label {
        font-size: 10px;
        font-weight: bold;
        padding: 3px 6px;
        border-radius: 3px;
        color: white;
        display: inline-block;
        margin-right: 4px;
    }
    
    .fm-pv-label-ST { background: #0073aa; }
    .fm-pv-label-UP { background: #00a65a; }
    .fm-pv-label-BR { background: #000; }
    .fm-pv-label-LO { background: #ff7700; }
    .fm-pv-label-FI { background: #e6b800; }
    .fm-pv-label-PH { background: #008080; }
    .fm-pv-label-VI { background: #7a4dc9; }
    .fm-pv-label-VC { background: #c62828; }
    .fm-pv-label-AI { background: #444; }
    .fm-pv-label-AN { background: #9c27b0; }
    
    /* Label dropdown */
    .fm-pv-dropdown-labels {
        position: relative;
        display: inline-block;
    }
    
    .fm-pv-labels-btn {
        padding: 6px 12px;
        border: 1px solid #ccc;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: #333;
    }
    
    .fm-pv-labels-btn:hover {
        background: #f5f5f5;
    }
    
    .fm-pv-dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid #ccc;
        padding: 10px;
        z-index: 100;
        border-radius: 6px;
        min-width: 250px;
        max-height: 400px;
        overflow-y: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .fm-pv-dropdown-labels:hover .fm-pv-dropdown-content {
        display: block;
    }
    
    .fm-pv-dropdown-content label {
        display: flex;
        align-items: center;
        font-size: 13px;
        gap: 6px;
        margin-bottom: 6px;
        cursor: pointer;
        color: #333;
    }
    
    .fm-pv-dropdown-content label:hover {
        background: #f5f5f5;
        padding: 2px 4px;
        margin: -2px -4px 6px -4px;
        border-radius: 3px;
    }
    
    /* MOSAIC VIEW - True masonry layout */
    .fm-pv-grid.mosaic-view {
        display: block !important;
        column-count: 4;
        column-gap: 10px;
        grid: none;
    }
    
    @media (max-width: 1200px) {
        .fm-pv-grid.mosaic-view { column-count: 3; }
    }
    @media (max-width: 768px) {
        .fm-pv-grid.mosaic-view { column-count: 2; }
    }
    @media (max-width: 480px) {
        .fm-pv-grid.mosaic-view { column-count: 1; }
    }
    
    .fm-pv-grid.mosaic-view .fm-pv-tile {
        break-inside: avoid;
        margin-bottom: 10px;
        display: inline-block;
        width: 100%;
        padding: 4px;
    }
    
    .fm-pv-grid.mosaic-view .fm-pv-tile-details,
    .fm-pv-grid.mosaic-view .fm-pv-toolbar {
        display: none !important;
    }
    
    /* LIST VIEW - Properly stacked */
    .fm-pv-grid.list-view {
        display: block !important;
        grid: none;
    }
    
    .fm-pv-grid.list-view .fm-pv-tile {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 8px;
        padding: 10px;
    }
    
    .fm-pv-grid.list-view .fm-pv-checkbox {
        position: relative;
        top: 0;
        left: 0;
        opacity: 1;
        margin-top: 15px;
    }
    
    .fm-pv-grid.list-view .fm-pv-image-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
    }
    
    .fm-pv-grid.list-view .fm-pv-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .fm-pv-grid.list-view .fm-pv-tile-details {
        flex: 0 0 160px;
    }
    
    .fm-pv-grid.list-view .fm-pv-tile-details strong {
        font-size: 13px;
        margin: 0 0 2px 0;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .fm-pv-grid.list-view .fm-pv-tile-details small {
        font-size: 11px;
        color: #666;
    }
    
    .fm-pv-grid.list-view .fm-pv-toolbar {
        flex: 1;
        display: block !important;
        opacity: 1 !important;
    }
    
    .fm-pv-grid.list-view .fm-pv-toolbar-content {
        display: flex;
        gap: 15px;
    }
    
    /* List view stacked sections */
    .fm-pv-grid.list-view .fm-pv-stack-labels {
        flex: 0 0 140px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-labels .fm-pv-toolbar-row {
        flex-direction: column;
        align-items: flex-start;
        margin: 0;
        gap: 2px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-labels .fm-pv-label {
        font-size: 9px;
        padding: 2px 4px;
        margin-bottom: 2px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-labels button {
        font-size: 11px !important;
        padding: 3px 6px !important;
        margin-top: 4px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-project {
        flex: 0 0 120px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-project .fm-pv-toolbar-row {
        flex-direction: column;
        align-items: flex-start;
        margin: 0;
        gap: 4px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-actions {
        flex: 0 0 auto;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-actions .fm-pv-toolbar-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .fm-pv-grid.list-view .fm-pv-stack-actions button,
    .fm-pv-grid.list-view .fm-pv-stack-actions a {
        font-size: 11px !important;
        padding: 3px 8px !important;
        min-width: 100px;
    }
    
    /* Contact Sheet */
    .fm-pv-contact-sheet {
        background: white;
        padding: 40px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .fm-pv-contact-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #333;
    }
    
    .fm-pv-contact-title {
        font-size: 36px;
        font-weight: 700;
        margin: 0 0 10px 0;
    }
    
    .fm-pv-contact-subtitle {
        font-size: 16px;
        color: #666;
    }
    
    .fm-pv-contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 40px;
    }
    
    .fm-pv-contact-item {
        text-align: center;
    }
    
    .fm-pv-contact-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border: 1px solid #ddd;
        margin-bottom: 8px;
    }
    
    .fm-pv-contact-caption {
        font-size: 11px;
        color: #666;
        line-height: 1.3;
    }
    
    @media print {
        .fm-pv-header,
        .fm-pv-bulkbar,
        .fm-pv-checkbox {
            display: none !important;
        }
    }
    </style>

    <div class="fm-pv-wrapper">
        <div class="fm-pv-container">
            <?php if ($view_mode === 'contact'): ?>
                <!-- Contact Sheet View -->
                <div class="fm-pv-contact-sheet">
                    <div class="fm-pv-contact-header">
                        <h1 class="fm-pv-contact-title"><?php echo esc_html($project); ?> Contact Sheet</h1>
                        <p class="fm-pv-contact-subtitle">
                            <?php echo count($attachments); ?> assets • Generated <?php echo date('F j, Y'); ?>
                        </p>
                    </div>
                    
                    <div class="fm-pv-contact-grid">
                        <?php 
                        $index = 1;
                        foreach ($attachments as $attachment): 
                            $image_url = wp_get_attachment_image_url($attachment->ID, 'medium');
                            if (!$image_url) continue;
                            
                            $labels = get_field('fastmedia_asset_labels', $attachment->ID) ?: [];
                        ?>
                            <div class="fm-pv-contact-item">
                                <img src="<?php echo esc_url($image_url); ?>" 
                                     alt="<?php echo esc_attr($attachment->post_title); ?>" 
                                     class="fm-pv-contact-image">
                                <div class="fm-pv-contact-caption">
                                    #<?php echo $index++; ?> - ID: <?php echo $attachment->ID; ?>
                                    <?php if (!empty($labels)): ?>
                                        <br>[<?php echo implode(', ', $labels); ?>]
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="text-align: center; padding-top: 20px; border-top: 2px solid #333;">
                        <p>© <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?> - Project: <?php echo esc_html($project); ?></p>
                        <p style="margin-top: 20px;">
                            <a href="<?php echo site_url('/project-view/?project=' . urlencode($project)); ?>" class="fm-pv-btn">
                                ← Back to Grid View
                            </a>
                            <button onclick="window.print()" class="fm-pv-btn fm-pv-btn-primary" style="margin-left: 10px;">
                                🖨️ Print Contact Sheet
                            </button>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Regular Grid/List/Mosaic View -->
                <div class="fm-pv-header">
                    <div class="fm-pv-title-row">
                        <h1 class="fm-pv-title">📁 <?php echo esc_html($project); ?></h1>
                        <div class="fm-pv-header-buttons">
                            <a href="<?php echo site_url('/project-view/?project=' . urlencode($project) . '&view=contact'); ?>" 
                               class="fm-pv-btn fm-pv-btn-primary">
                                🎛️ Contact Sheet View
                            </a>
                            <a href="<?php echo site_url('/my-projects/'); ?>" class="fm-pv-back-btn">
                                ← Back to Projects
                            </a>
                        </div>
                    </div>
                    
                    <div class="fm-pv-note-section">
                        <form method="post">
                            <textarea name="fm_pv_project_note" 
                                      class="fm-pv-note-textarea" 
                                      placeholder="Add a note for this project..."><?php echo esc_textarea($project_note); ?></textarea>
                            <button type="submit" name="fm_pv_save_note" value="1" class="fm-pv-btn">
                                💾 Save Note
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- View controls and sort -->
                <div class="fm-pv-view-controls">
                    <select class="fm-pv-select" onchange="sortAssets(this.value)">
                        <option value="date-desc" <?php selected(isset($_GET['sort']) && $_GET['sort'] === 'date-desc'); ?>>Newest First</option>
                        <option value="date-asc" <?php selected(isset($_GET['sort']) && $_GET['sort'] === 'date-asc'); ?>>Oldest First</option>
                        <option value="name-asc" <?php selected(isset($_GET['sort']) && $_GET['sort'] === 'name-asc'); ?>>Name A-Z</option>
                        <option value="name-desc" <?php selected(isset($_GET['sort']) && $_GET['sort'] === 'name-desc'); ?>>Name Z-A</option>
                        <option value="size-desc" <?php selected(isset($_GET['sort']) && $_GET['sort'] === 'size-desc'); ?>>Largest First</option>
                        <option value="size-asc" <?php selected(isset($_GET['sort']) && $_GET['sort'] === 'size-asc'); ?>>Smallest First</option>
                    </select>
                    <div class="fm-pv-view-switcher">
                        <button onclick="setView('detail')" class="fm-pv-view-btn active" title="Detail View">⊞</button>
                        <button onclick="setView('mosaic')" class="fm-pv-view-btn" title="Mosaic View">▦</button>
                        <button onclick="setView('list')" class="fm-pv-view-btn" title="List View">☰</button>
                    </div>
                </div>
                
                <!-- SINGLE Bulk actions bar -->
                <div class="fm-pv-bulkbar" id="bulk-actions">
                    <span><strong id="selected-count">0</strong> selected</span>
                    <button onclick="selectAll()">Select All</button>
                    <button onclick="deselectAll()">Deselect All</button>
                    <button onclick="bulkDownload()">📥 Download</button>
                    
                    <button type="button" id="bulk-toggle-btn" onclick="bulkAddToProject()" 
                            style="background: #f5f5f5; color: #333; padding: 6px 10px; border-radius: 4px; font-size: 16px; cursor: pointer; border: 1px solid #ddd;">
                        <span>➕</span>
                    </button>
                    
                    <select id="bulk-project-picker" style="font-size: 13px; padding: 6px 10px; border-radius: 4px;">
                        <?php foreach ($user_projects as $proj): ?>
                            <option value="<?php echo esc_attr($proj); ?>" <?php selected($proj, $project); ?>>
                                <?php echo esc_html($proj); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Copy to different project -->
                    <select id="bulk-copy-to" onchange="bulkCopyToProject()" style="font-size: 13px; padding: 6px 10px; border-radius: 4px;">
                        <option value="">Copy selected to...</option>
                        <?php
                        $other_projects = array_filter($user_projects, function($p) use ($project) {
                            return $p !== $project;
                        });
                        foreach ($other_projects as $other_project):
                        ?>
                            <option value="<?php echo esc_attr($other_project); ?>">
                                <?php echo esc_html($other_project); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Move to different project -->
                    <select id="bulk-move-to" onchange="bulkMoveToProject()" style="font-size: 13px; padding: 6px 10px; border-radius: 4px;">
                        <option value="">Move selected to...</option>
                        <?php foreach ($other_projects as $other_project): ?>
                            <option value="<?php echo esc_attr($other_project); ?>">
                                <?php echo esc_html($other_project); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button onclick="bulkRemoveFromProject()">🗑️ Remove from Project</button>
                </div>
                
                <div class="fm-pv-grid">
                    <?php foreach ($attachments as $attachment):
                        $id = $attachment->ID;
                        $thumb = wp_get_attachment_image_src($id, 'medium');
                        $url = $thumb ? $thumb[0] : 'https://placehold.co/400x300?text=Preview';
                        $title = esc_html(get_the_title($id));
                        $date = get_the_date('', $id);
                        $alt = esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true));

                        // File info
                        $file_path = get_attached_file($id);
                        $file_size = $file_path && file_exists($file_path) ? filesize($file_path) : 0;
                        $file_size_formatted = $file_size ? size_format($file_size) : 'N/A';
                        $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
                        $file_ext_upper = strtoupper($file_ext);
                        
                        $labels = get_field('fastmedia_asset_labels', $id) ?: [];
                        $source = get_post_meta($id, 'source', true);
                        
                        // Ensure ST/UP labels
                        if ($source === 'solwee' && !in_array('ST', $labels)) {
                            $labels[] = 'ST';
                            update_field('fastmedia_asset_labels', $labels, $id);
                        }
                        if ($source !== 'solwee' && !in_array('UP', $labels)) {
                            $labels[] = 'UP';
                            update_field('fastmedia_asset_labels', $labels, $id);
                        }
                        
                        $is_approved = get_post_meta($id, 'fastmedia_brand_approved', true) === 'yes';
                        $is_proposed = get_post_meta($id, 'fastmedia_brand_proposed', true) === 'yes';
                    ?>
                    <div class="fm-pv-tile" data-asset-id="<?php echo $id; ?>">
                        <input type="checkbox" class="fm-pv-checkbox" value="<?php echo $id; ?>" onchange="updateBulkBar()" title="Select image">
                        
                        <div class="fm-pv-image-wrapper">
                            <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>">
                                <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" />
                            </a>
                        </div>
                        
                        <div class="fm-pv-tile-details">
                            <strong>
                                <?php echo $title; ?>
                                <?php if ($file_ext): ?>
                                    <span style="font-size: 11px; color: #666; font-weight: normal;">(<?php echo esc_html($file_ext_upper); ?>)</span>
                                <?php endif; ?>
                            </strong>
                            <small style="color: #666; display: block;"><?php echo $date; ?></small>
                        </div>

                        <div class="fm-pv-toolbar">
                            <div class="fm-pv-toolbar-content">
                                <!-- Stack 1: Rating (thumbs up line 1, down line 2) -->
                                <div class="fm-pv-stack-rating">
                                    <?php if (function_exists('fastmedia_rating_ui')): ?>
                                        <?php echo fastmedia_rating_ui($id); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Stack 2: Labels (max 3 on line 1, rest on line 2, dropdown and suggest on line 1) -->
                                <div class="fm-pv-stack-labels">
                                    <div class="fm-pv-labels-line1">
                                        <?php 
                                        $label_count = 0;
                                        foreach ($labels as $code): 
                                            if (isset($label_map[$code]) && $label_count < 3):
                                                $label_count++;
                                        ?>
                                            <span class="fm-pv-label fm-pv-label-<?php echo esc_attr($code); ?>" 
                                                  title="<?php echo esc_attr($label_map[$code]); ?>">
                                                <?php echo esc_html($code); ?>
                                            </span>
                                        <?php 
                                            endif;
                                        endforeach; ?>
                                        
                                        <div class="fm-pv-dropdown-labels">
                                            <button type="button" class="fm-pv-labels-btn">Labels</button>
                                            <div class="fm-pv-dropdown-content">
                                                <?php foreach ($label_map as $code => $desc):
                                                    $checked = in_array($code, $labels) ? 'checked' : '';
                                                    $disabled = in_array($code, ['ST', 'UP']) ? 'disabled' : '';
                                                ?>
                                                    <label>
                                                        <input type="checkbox" value="<?php echo esc_attr($code); ?>" <?php echo $checked; ?> <?php echo $disabled; ?>> 
                                                        <strong><?php echo esc_html($code); ?></strong> - <?php echo esc_html($desc); ?>
                                                    </label>
                                                <?php endforeach; ?>
                                                <button type="button" onclick="saveLabels(<?php echo $id; ?>, this)" style="margin-top:8px;width:100%;padding:6px;background:#0073aa;color:white;border:none;border-radius:4px;cursor:pointer;font-size:13px;">Save</button>
                                            </div>
                                        </div>
                                        
                                        <?php if (!in_array('BR', $labels)): ?>
                                            <button onclick="suggestForBrand(<?php echo $id; ?>, this)" style="padding:4px 8px;background:#000;color:white;border:none;border-radius:4px;cursor:pointer;font-size:10px;">Suggest for Brand</button>
                                        <?php else: ?>
                                            <span style="padding:2px 6px;background:#999;color:white;border-radius:4px;font-size:10px;">
                                                <?php echo $is_approved ? '✅ Approved' : '⏳ Pending'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="fm-pv-labels-line2">
                                        <?php 
                                        $label_count = 0;
                                        foreach ($labels as $code): 
                                            if (isset($label_map[$code])):
                                                $label_count++;
                                                if ($label_count > 3):
                                        ?>
                                            <span class="fm-pv-label fm-pv-label-<?php echo esc_attr($code); ?>" 
                                                  title="<?php echo esc_attr($label_map[$code]); ?>">
                                                <?php echo esc_html($code); ?>
                                            </span>
                                        <?php 
                                                endif;
                                            endif;
                                        endforeach; ?>
                                    </div>
                                </div>

                                <!-- Stack 3: Project toggle (single line) -->
                                <div class="fm-pv-stack-project">
                                    <div class="fm-pv-toolbar-row">
                                        <div class="fm-pv-project-toggle">
                                            <?php echo fastmedia_project_toggle_ui($id); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stack 4: Actions (share/download line 1, edit/remove line 2) -->
                                <div class="fm-pv-stack-actions">
                                    <div class="fm-pv-toolbar-buttons">
                                        <button title="Copy share link" onclick="copyShareLink(<?php echo $id; ?>)">🔗 Share</button>
                                        <button title="Download full resolution" onclick="downloadAsset(<?php echo $id; ?>)">⬇️ Download</button>
                                        <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>" title="Edit details">✏️ Edit</a>
                                        <button title="Remove from project" onclick="removeFromProject(<?php echo $id; ?>)">❌ Remove</button>
                                    </div>
                                </div>
                                
                                <div style="font-size: 12px; color: #666; margin-top: 8px;">
                                    File size: <?php echo $file_size_formatted; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_attachments > $posts_per_page): ?>
                    <?php echo do_shortcode('[solwee_pagination page="' . $page . '" total="' . $total_attachments . '" limit="' . $posts_per_page . '"]'); ?>
                <?php endif; ?>
                
                <?php if (empty($attachments)): ?>
                <div style="text-align: center; padding: 60px 20px; color: #666;">
                    <h3>No assets in this project yet</h3>
                    <p>Add assets using the project toggle on the asset detail page.</p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    window.fastmedia_nonce = '<?php echo wp_create_nonce("fastmedia_project_nonce"); ?>';
    
    function selectAll() {
        document.querySelectorAll('.fm-pv-checkbox').forEach(cb => cb.checked = true);
        updateBulkBar();
    }
    
    function deselectAll() {
        document.querySelectorAll('.fm-pv-checkbox').forEach(cb => cb.checked = false);
        updateBulkBar();
    }
    
    function updateBulkBar() {
        const checkboxes = document.querySelectorAll('.fm-pv-checkbox:checked');
        const bulkBar = document.getElementById('bulk-actions');
        const countSpan = document.getElementById('selected-count');
        
        if (checkboxes.length > 0) {
            bulkBar.classList.add('active');
            countSpan.textContent = checkboxes.length;
        } else {
            bulkBar.classList.remove('active');
        }
    }
    
    function bulkDownload() {
        const selected = document.querySelectorAll('.fm-pv-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select images to download');
            return;
        }
        
        if (!confirm('Download ' + selected.length + ' full resolution images?')) {
            return;
        }
        
        let downloadFrame = document.getElementById('bulk-download-frame');
        if (!downloadFrame) {
            downloadFrame = document.createElement('iframe');
            downloadFrame.id = 'bulk-download-frame';
            downloadFrame.style.display = 'none';
            document.body.appendChild(downloadFrame);
        }
        
        let index = 0;
        function downloadNext() {
            if (index >= selected.length) {
                alert('Downloads started for ' + selected.length + ' images');
                return;
            }
            
            const assetId = selected[index].value;
            const downloadUrl = '<?php echo admin_url("admin-ajax.php"); ?>?action=download_attachment&id=' + assetId;
            downloadFrame.src = downloadUrl;
            
            index++;
            setTimeout(downloadNext, 500);
        }
        
        downloadNext();
    }
    
    function bulkAddToProject() {
        const selected = document.querySelectorAll('.fm-pv-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select assets first');
            return;
        }
        
        const project = document.getElementById('bulk-project-picker').value;
        const toggleBtn = document.getElementById('bulk-toggle-btn');
        const isAdding = toggleBtn.querySelector('span').textContent === '➕';
        
        if (isAdding) {
            toggleBtn.querySelector('span').textContent = '➖';
            toggleBtn.style.background = '#4CAF50';
            toggleBtn.style.color = 'white';
            
            selected.forEach(cb => {
                const formData = new FormData();
                formData.append('action', 'fastmedia_toggle_project');
                formData.append('attachment_id', cb.value);
                formData.append('project', project);
                formData.append('toggle_action', 'add');
                formData.append('nonce', window.fastmedia_nonce);
                
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            });
            
            alert('Adding ' + selected.length + ' assets to project: ' + project);
        } else {
            toggleBtn.querySelector('span').textContent = '➕';
            toggleBtn.style.background = '#f5f5f5';
            toggleBtn.style.color = '#333';
            
            selected.forEach(cb => {
                const formData = new FormData();
                formData.append('action', 'fastmedia_toggle_project');
                formData.append('attachment_id', cb.value);
                formData.append('project', project);
                formData.append('toggle_action', 'remove');
                formData.append('nonce', window.fastmedia_nonce);
                
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            });
            
            alert('Removing ' + selected.length + ' assets from project: ' + project);
        }
        
        setTimeout(() => location.reload(), 1000);
    }
    
    function bulkRemoveFromProject() {
        const selected = document.querySelectorAll('.fm-pv-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select assets to remove');
            return;
        }
        
        if (!confirm('Remove ' + selected.length + ' selected assets from this project?')) {
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="fm_pv_remove_selected" value="1">';
        
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'fm_pv_selected_ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
    
    function copyShareLink(assetId) {
        const url = '<?php echo site_url('/asset-detail/?id='); ?>' + assetId;
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copied!');
        }).catch(() => {
            prompt('Copy this link:', url);
        });
    }
    
    function downloadAsset(assetId) {
        let downloadFrame = document.getElementById('single-download-frame');
        if (!downloadFrame) {
            downloadFrame = document.createElement('iframe');
            downloadFrame.id = 'single-download-frame';
            downloadFrame.style.display = 'none';
            document.body.appendChild(downloadFrame);
        }
        
        const downloadUrl = '<?php echo admin_url("admin-ajax.php"); ?>?action=download_attachment&id=' + assetId;
        downloadFrame.src = downloadUrl;
    }
    
    function removeFromProject(assetId) {
        if (!confirm('Remove this asset from the project?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="fm_pv_remove_selected" value="1">' +
                        '<input type="hidden" name="fm_pv_selected_ids[]" value="' + assetId + '">';
        document.body.appendChild(form);
        form.submit();
    }
    
    function saveLabels(assetId, button) {
        button.disabled = true;
        button.textContent = 'Saving...';
        
        const labelContainer = button.closest('.fm-pv-dropdown-content');
        const checkedLabels = [];
        labelContainer.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
            checkedLabels.push(cb.value);
        });
        
        const formData = new FormData();
        formData.append('action', 'fastmedia_save_labels');
        formData.append('attachment_id', assetId);
        formData.append('labels', JSON.stringify(checkedLabels));
        formData.append('nonce', window.fastmedia_nonce);
        
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = 'Saved!';
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error saving labels');
                button.textContent = 'Save';
                button.disabled = false;
            }
        });
    }
    
    function suggestForBrand(assetId, button) {
        button.disabled = true;
        button.textContent = 'Suggesting...';
        
        const formData = new FormData();
        formData.append('action', 'fastmedia_suggest_brand');
        formData.append('attachment_id', assetId);
        formData.append('nonce', window.fastmedia_nonce);
        
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pendingSpan = document.createElement('span');
                pendingSpan.style.cssText = 'padding:4px 8px;background:#999;color:white;border-radius:4px;font-size:12px;';
                pendingSpan.textContent = '⏳ Pending';
                button.parentNode.replaceChild(pendingSpan, button);
            } else {
                alert('Error suggesting for brand');
                button.disabled = false;
                button.textContent = 'Suggest for Brand';
            }
        });
    }
    
    function bulkCopyToProject() {
        const selected = document.querySelectorAll('.fm-pv-checkbox:checked');
        const copyTo = document.getElementById('bulk-copy-to').value;
        
        if (selected.length === 0) {
            alert('Please select assets to copy');
            return;
        }
        
        if (!copyTo) {
            return; // No destination selected
        }
        
        if (confirm('Copy ' + selected.length + ' selected assets to "' + copyTo + '"?')) {
            selected.forEach(cb => {
                const formData = new FormData();
                formData.append('action', 'fastmedia_copy_to_project');
                formData.append('attachment_id', cb.value);
                formData.append('to_project', copyTo);
                formData.append('nonce', window.fastmedia_nonce);
                
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            });
            
            alert('Copying ' + selected.length + ' assets to project: ' + copyTo);
            setTimeout(() => location.reload(), 1500);
        }
        
        // Reset dropdown
        document.getElementById('bulk-copy-to').value = '';
    }
    
    function bulkMoveToProject() {
        const selected = document.querySelectorAll('.fm-pv-checkbox:checked');
        const moveTo = document.getElementById('bulk-move-to').value;
        
        if (selected.length === 0) {
            alert('Please select assets to move');
            return;
        }
        
        if (!moveTo) {
            return; // No destination selected
        }
        
        if (confirm('Move ' + selected.length + ' selected assets to "' + moveTo + '"?')) {
            // This would need AJAX implementation to:
            // 1. Remove from current project
            // 2. Add to new project
            selected.forEach(cb => {
                const formData = new FormData();
                formData.append('action', 'fastmedia_move_to_project');
                formData.append('attachment_id', cb.value);
                formData.append('from_project', '<?php echo esc_js($project); ?>');
                formData.append('to_project', moveTo);
                formData.append('nonce', window.fastmedia_nonce);
                
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            });
            
            alert('Moving ' + selected.length + ' assets to project: ' + moveTo);
            setTimeout(() => location.reload(), 1500);
        }
        
        // Reset dropdown
        document.getElementById('bulk-move-to').value = '';
    }
    
    function sortAssets(sortBy) {
        const url = new URL(window.location);
        url.searchParams.set('sort', sortBy);
        window.location.href = url.toString();
    }
    
    function setView(viewType) {
        const grid = document.querySelector('.fm-pv-grid');
        const buttons = document.querySelectorAll('.fm-pv-view-btn');
        
        grid.classList.remove('detail-view', 'mosaic-view', 'list-view');
        buttons.forEach(btn => btn.classList.remove('active'));
        
        grid.classList.add(viewType + '-view');
        
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
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('fastmedia_view') || 'detail';
        if (savedView !== 'detail') {
            setView(savedView);
        }
    });
    </script>

    <?php
    return ob_get_clean();
});

// AJAX handlers
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
    
    $source = get_post_meta($attachment_id, 'source', true);
    
    if ($source === 'solwee' && !in_array('ST', $labels)) {
        $labels[] = 'ST';
    }
    if ($source !== 'solwee' && !in_array('UP', $labels)) {
        $labels[] = 'UP';
    }
    
    update_field('fastmedia_asset_labels', $labels, $attachment_id);
    
    $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: [];
    $user_info = get_userdata($user_id);
    $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' updated labels';
    update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
    
    wp_send_json_success(['labels' => $labels]);
});

add_action('wp_ajax_fastmedia_suggest_brand', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fastmedia_project_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $attachment_id = intval($_POST['attachment_id']);
    $user_id = get_current_user_id();
    
    if (get_post_field('post_author', $attachment_id) != $user_id) {
        wp_send_json_error('Permission denied');
    }
    
    $labels = get_field('fastmedia_asset_labels', $attachment_id) ?: [];
    if (!in_array('BR', $labels)) {
        $labels[] = 'BR';
        update_field('fastmedia_asset_labels', $labels, $attachment_id);
    }
    
    update_post_meta($attachment_id, 'fastmedia_brand_proposed', 'yes');
    
    $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: [];
    $user_info = get_userdata($user_id);
    $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' suggested for brand';
    update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
    
    wp_send_json_success(['message' => 'Suggested for brand approval']);
});

add_action('wp_ajax_download_attachment', function() {
    $attachment_id = intval($_GET['id']);
    $user_id = get_current_user_id();
    
    if (get_post_field('post_author', $attachment_id) != $user_id) {
        wp_die('Access denied');
    }
    
    $file = get_attached_file($attachment_id);
    if (!$file || !file_exists($file)) {
        wp_die('File not found');
    }
    
    $filename = basename($file);
    $mime_type = get_post_mime_type($attachment_id);
    
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($file);
    exit;
});
