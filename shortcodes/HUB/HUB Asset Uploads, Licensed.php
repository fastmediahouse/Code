/**
 * ✅ HUB Asset Uploads, Licensed - My Assets Page
 * Version: 2025-01-14
 * Shortcodes:
 * - [fastmedia_uploaded]
 * - [fastmedia_purchased_assets]
 * - [fastmedia_assets_my_assets]
 * 
 * Requires: FastMedia Core Functions snippet
 */

function render_fastmedia_grid($attachments, $badge_type = 'UP') {
    ob_start();
    ?>
    <style>
    .fastmedia-bulkbar {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 20px;
        display: none;
        align-items: center;
        gap: 10px;
    }
    .fastmedia-bulkbar.active { display: flex; }
    .fastmedia-bulkbar button {
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
        color: #333;
        font-family: inherit;
    }
    .fastmedia-bulkbar button:hover { background: #f0f0f0; }
    .fastmedia-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .fastmedia-tile {
        position: relative;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .fastmedia-tile:hover { 
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #ccc;
    }
    .fastmedia-tile img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    .fastmedia-checkbox {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 3;
        width: 24px;
        height: 24px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .fastmedia-tile:hover .fastmedia-checkbox,
    .fastmedia-checkbox:checked {
        opacity: 1;
    }
    .fm-image-wrapper {
        position: relative;
    }
    .fm-rating-overlay {
        position: absolute;
        bottom: 10px;
        left: 10px;
        z-index: 2;
    }
    .fm-rating-overlay .rating-btn {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(0, 0, 0, 0.1);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    /* Fix rating button size */
    .fm-rating-overlay button {
        font-size: 16px !important;
        line-height: 1 !important;
        padding: 2px 6px !important;
        min-width: auto !important;
        height: auto !important;
    }
    .fm-label {
        font-size: 10px;
        font-weight: bold;
        padding: 3px 6px;
        border-radius: 3px;
        color: white;
        display: inline-block;
        margin-right: 4px;
    }
    .fm-label-ST { background: #0073aa; }
    .fm-label-UP { background: #00a65a; }
    .fm-label-BR { background: #000; }
    .fm-label-LO { background: #ff7700; }
    .fm-label-FI { background: #e6b800; }
    .fm-label-PH { background: #008080; }
    .fm-label-VI { background: #7a4dc9; }
    .fm-label-VC { background: #c62828; }
    .fm-label-AI { background: #444; }
    .fm-label-AN { background: #9c27b0; }
    .fm-dropdown-labels {
        position: relative;
        display: inline-block;
    }
    .fm-labels-btn {
        padding: 6px 12px;
        border: 1px solid #ccc;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: #333;
    }
    .fm-labels-btn:hover { background: #f5f5f5; }
    .fm-dropdown-content {
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
    .fm-dropdown-labels:hover .fm-dropdown-content { display: block; }
    .fm-dropdown-content label {
        display: flex;
        align-items: center;
        font-size: 13px;
        gap: 6px;
        margin-bottom: 6px;
        cursor: pointer;
        color: #333;
    }
    .fm-dropdown-content label:hover {
        background: #f5f5f5;
        padding: 2px 4px;
        margin: -2px -4px 6px -4px;
        border-radius: 3px;
    }
    .fastmedia-toolbar {
        margin-top: 10px;
    }
    .fm-toolbar-content {
        padding: 8px 0;
    }
    .fm-toolbar-row {
        display: flex;
        gap: 6px;
        margin: 10px 0;
        align-items: center;
        flex-wrap: nowrap;
    }
    .fm-toolbar-buttons {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }
    .fm-toolbar-buttons button,
    .fm-toolbar-buttons a {
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 12px;
        padding: 5px 8px;
        text-align: center;
        cursor: pointer;
        text-decoration: none;
        color: #333;
        white-space: nowrap;
    }
    .fm-toolbar-buttons button:hover,
    .fm-toolbar-buttons a:hover {
        background: #f0f0f0;
        text-decoration: none;
    }
    /* Fix project toggle button size */
    .fm-project-toggle button,
    .fm-project-toggle .project-toggle-btn,
    .fm-project-toggle .toggle-btn {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 2px;
        font-size: 11px !important;
        line-height: 1;
        cursor: pointer;
        width: 18px !important;
        height: 18px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px !important;
    }
    /* Fix suggest for brand button size */
    .fm-toolbar-row button[onclick*="suggestForBrand"] {
        padding: 6px 10px !important;
        font-size: 13px !important;
        height: auto !important;
        width: auto !important;
        display: inline-block !important;
    }
    /* Fix project toggle select size */
    .fm-project-toggle select,
    .fm-project-toggle .project-picker {
        font-size: 12px !important;
        padding: 4px 8px !important;
        height: 26px !important;
    }
    
    /* View controls FROM PROJECT VIEW */
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
    
    /* MOSAIC VIEW FROM PROJECT VIEW */
    .fastmedia-grid.mosaic-view {
        display: block !important;
        column-count: 4;
        column-gap: 10px;
        grid: none;
    }
    
    @media (max-width: 1200px) {
        .fastmedia-grid.mosaic-view { column-count: 3; }
    }
    @media (max-width: 768px) {
        .fastmedia-grid.mosaic-view { column-count: 2; }
    }
    @media (max-width: 480px) {
        .fastmedia-grid.mosaic-view { column-count: 1; }
    }
    
    .fastmedia-grid.mosaic-view .fastmedia-tile {
        break-inside: avoid;
        margin-bottom: 10px;
        display: inline-block;
        width: 100%;
        padding: 4px;
    }
    
    .fastmedia-grid.mosaic-view .fm-tile-details,
    .fastmedia-grid.mosaic-view .fastmedia-toolbar {
        display: none !important;
    }
    
    /* LIST VIEW - Fixed height with dark separators and grey backgrounds */
    .fastmedia-grid.list-view {
        display: block !important;
        grid: none;
    }
    
    .fastmedia-grid.list-view .fastmedia-tile {
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
    }
    
    .fastmedia-grid.list-view .fastmedia-tile::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0; /* Extended to the end of the box */
        height: 2px;
        background: #666;
    }
    
    .fastmedia-grid.list-view .fastmedia-tile:hover {
        background: #f0f0f0;
    }
    
    .fastmedia-grid.list-view .fastmedia-tile:hover::after {
        background: #444;
    }
    
    .fastmedia-grid.list-view .fastmedia-tile:last-child::after {
        display: none;
    }
    
    .fastmedia-grid.list-view .fastmedia-checkbox {
        position: relative;
        top: 0;
        left: 0;
        opacity: 1;
        margin: 0 12px;
        flex-shrink: 0;
        align-self: center;
    }
    
    .fastmedia-grid.list-view .fm-image-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        align-self: center;
        margin-right: 12px;
    }
    
    .fastmedia-grid.list-view .fm-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .fastmedia-grid.list-view .fm-tile-details {
        flex: 0 0 220px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 10px 15px;
        background: #f8f8f8;
        height: 100%;
        position: relative;
        border-left: none;
        border-right: none;
        min-height: 0; /* Allow flex shrinking */
    }
    
    /* Add pseudo-elements for shorter borders */
    .fastmedia-grid.list-view .fm-tile-details::before {
        content: '';
        position: absolute;
        left: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fastmedia-grid.list-view .fm-tile-details::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fastmedia-grid.list-view .fm-tile-details strong {
        font-size: 13px;
        margin: 0 0 4px 0;
        display: block;
        overflow: hidden;
        line-height: 1.5;
        max-height: 3em; /* 2 lines at 1.5 line-height */
        word-break: break-word;
        flex-shrink: 0; /* Prevent shrinking */
    }
    
    .fastmedia-grid.list-view .fm-tile-details small {
        font-size: 11px;
        color: #666;
    }
    
    .fastmedia-grid.list-view .fastmedia-toolbar {
        flex: 1;
        display: block !important;
        opacity: 1 !important;
        margin: 0;
        height: 100%;
    }
    
    .fastmedia-grid.list-view .fm-toolbar-content {
        display: flex;
        gap: 0;
        height: 100%;
        align-items: stretch;
        padding: 0;
    }
    
    /* List view: Rating stack with vertical thumbs - MORE HORIZONTAL SPACE */
    .fastmedia-grid.list-view .fm-pv-stack-rating {
        display: flex !important;
        flex-direction: column;
        gap: 2px;
        justify-content: center;
        padding-top: 0;
        width: 120px; /* Increased from 80px for ~50% more space */
        flex-shrink: 0;
        padding: 0 20px;
        position: relative;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-rating::after {
        content: '';
        position: absolute;
        right: -10px; /* Middle of the 20px padding */
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-rating .fm-rating-container {
        display: flex;
        flex-direction: column;
        gap: 2px;
        align-items: center;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-rating button {
        font-size: 14px !important;
        padding: 2px 6px !important;
        border: 1px solid #ccc !important;
        background: white !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        margin: 0 !important;
        width: 40px !important;
        height: 26px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-rating button:hover {
        background: #f0f0f0 !important;
    }
    
    /* List view: Labels stack with flexible grid - REDUCED WIDTH */
    .fastmedia-grid.list-view .fm-pv-stack-labels {
        display: flex !important;
        flex-direction: column;
        gap: 4px;
        justify-content: center;
        width: 220px; /* Reduced from 260px */
        flex-shrink: 0;
        padding: 0 15px; /* Reduced padding from 20px */
        position: relative;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-labels::after {
        content: '';
        position: absolute;
        right: -7.5px; /* Middle of the 15px padding */
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fastmedia-grid.list-view .fm-pv-labels-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        max-width: 100%;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-labels .fm-label {
        font-size: 9px !important;
        padding: 2px 2px !important; /* Reduced padding to fit square around letters */
        margin: 0 !important;
        text-align: center;
        min-width: 18px; /* Ensure square shape */
        display: inline-block;
        line-height: 1.2;
    }
    
    .fastmedia-grid.list-view .fm-labels-btn {
        font-size: 11px !important;
        padding: 3px 6px !important;
        border: 1px solid #ccc;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        color: #333;
    }
    
    .fastmedia-grid.list-view .fm-pv-suggest-btn {
        font-size: 10px !important;
        padding: 2px 6px !important;
        background: #000;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    /* List view: Project stack - INCREASED WIDTH */
    .fastmedia-grid.list-view .fm-pv-stack-project {
        display: flex !important;
        align-items: center;
        width: 240px; /* Increased from 200px */
        flex-shrink: 0;
        padding: 0 20px;
        position: relative;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-project::after {
        content: '';
        position: absolute;
        right: -10px; /* Middle of the 20px padding */
        top: 15px;
        bottom: 15px;
        width: 1px;
        background: #666;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-project .fm-project-toggle {
        width: 100%;
    }
    
    .fastmedia-grid.list-view .fm-pv-stack-project select {
        width: 100%;
        font-size: 11px !important;
        padding: 4px 6px !important;
    }
    
    /* List view: Actions stack with 2x2 grid */
    .fastmedia-grid.list-view .fm-pv-stack-actions {
        display: flex !important;
        align-items: center;
        width: 180px;
        flex-shrink: 0;
        padding: 0 20px;
    }
    
    .fastmedia-grid.list-view .fm-pv-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2px;
        width: 100%;
    }
    
    .fastmedia-grid.list-view .fm-pv-actions-grid button,
    .fastmedia-grid.list-view .fm-pv-actions-grid a {
        font-size: 11px !important;
        padding: 4px 6px !important;
        border: 1px solid #ccc;
        background: white;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        text-decoration: none;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 26px;
        white-space: nowrap;
        gap: 2px; /* Space between icon and text */
    }
    
    .fastmedia-grid.list-view .fm-pv-actions-grid button:hover,
    .fastmedia-grid.list-view .fm-pv-actions-grid a:hover {
        background: #f0f0f0;
        text-decoration: none;
    }
    
    /* List view: Dropdown labels in list view */
    .fastmedia-grid.list-view .fm-dropdown-labels {
        position: relative;
        display: inline-block;
    }
    
    .fastmedia-grid.list-view .fm-dropdown-labels .fm-dropdown-content {
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
    
    .fastmedia-grid.list-view .fm-dropdown-labels:hover .fm-dropdown-content {
        display: block;
    }
    
    /* Hide detail layout in list view */
    .fastmedia-grid.list-view .fm-pv-detail-layout {
        display: none !important;
    }
    </style>

    <div class="fastmedia-wrapper">
        <div class="fastmedia-bulkbar" id="bulk-actions">
            <span><strong id="selected-count">0</strong> selected</span>
            <button onclick="selectAll()">Select All</button>
            <button onclick="deselectAll()">Deselect All</button>
            <button onclick="bulkDownload()">📥 Download</button>
            <button onclick="bulkAddToProject()">📁 Add to Project</button>
            <button onclick="bulkDelete()">🗑️ Delete</button>
        </div>

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

        <div class="fastmedia-grid">
            <?php foreach ($attachments as $attachment):
                $id = is_object($attachment) ? $attachment->ID : $attachment;
                $thumb = wp_get_attachment_image_src($id, 'medium');
                $url = $thumb ? $thumb[0] : 'https://placehold.co/400x300?text=Preview';
                $title = esc_html(get_the_title($id));
                $date = get_the_date('', $id);
                $alt = esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true));

                $is_approved = get_post_meta($id, 'fastmedia_brand_approved', true) === 'yes';
                $is_proposed = get_post_meta($id, 'fastmedia_brand_proposed', true) === 'yes';
                
                // Get file info
                $file_path = get_attached_file($id);
                $file_size = $file_path && file_exists($file_path) ? filesize($file_path) : 0;
                $file_size_formatted = $file_size ? size_format($file_size) : 'N/A';
                $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
                $file_ext_upper = strtoupper($file_ext);
                
                $labels = get_field('fastmedia_asset_labels', $id) ?: [];
                $source = get_post_meta($id, 'source', true);
                
                if ($source === 'solwee' && !in_array('ST', $labels)) {
                    $labels[] = 'ST';
                    update_field('fastmedia_asset_labels', $labels, $id);
                }
                if ($source !== 'solwee' && !in_array('UP', $labels)) {
                    $labels[] = 'UP';
                    update_field('fastmedia_asset_labels', $labels, $id);
                }
                
                $label_map = [
                    'ST' => 'Stock Image', 'UP' => 'User Upload', 'BR' => 'Brand Approved',
                    'LO' => 'Logo', 'FI' => 'Final Approved', 'PH' => 'Photography',
                    'VI' => 'Video', 'VC' => 'Vector', 'AI' => 'AI Generated'
                ];
            ?>
            <div class="fastmedia-tile" data-asset-id="<?php echo $id; ?>" data-view-mode="detail">
                <input type="checkbox" class="fastmedia-checkbox" onchange="updateBulkBar()" title="Select image">
                
                <div class="fm-image-wrapper">
                    <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>">
                        <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" />
                    </a>
                </div>
                
                <div class="fm-tile-details">
                    <strong>
                        <?php echo $title; ?>
                        <?php if ($file_ext): ?>
                            <span style="font-size: 11px; color: #666; font-weight: normal;">(<?php echo esc_html($file_ext_upper); ?>)</span>
                        <?php endif; ?>
                    </strong>
                    <small style="color: #666; display: block;"><?php echo $date; ?> • <?php echo $file_size_formatted; ?></small>
                </div>

                <div class="fastmedia-toolbar">
                    <div class="fm-toolbar-content">
                        <!-- List view layout -->
                        <div class="fm-pv-stack-rating">
                            <?php if (function_exists('fastmedia_rating_ui')): ?>
                                <?php echo fastmedia_rating_ui($id); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="fm-pv-stack-labels">
                            <div class="fm-pv-labels-grid">
                                <?php 
                                $label_count = 0;
                                foreach ($labels as $code): 
                                    if (isset($label_map[$code]) && $label_count < 4):
                                        $label_count++;
                                ?>
                                    <span class="fm-label fm-label-<?php echo esc_attr($code); ?>" 
                                          title="<?php echo esc_attr($label_map[$code]); ?>">
                                        <?php echo esc_html($code); ?>
                                    </span>
                                <?php 
                                    endif;
                                endforeach; ?>
                            </div>
                            
                            <div style="display: flex; gap: 4px; margin-top: 4px;">
                                <div class="fm-dropdown-labels">
                                    <button type="button" class="fm-labels-btn" onclick="event.stopPropagation();">Labels</button>
                                    <div class="fm-dropdown-content" onclick="event.stopPropagation();">
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
                                    <button onclick="suggestForBrand(<?php echo $id; ?>, this)" 
                                            class="fm-pv-suggest-btn"
                                            title="Propose this asset for brand approval">Brand Suggest</button>
                                <?php else: ?>
                                    <span style="padding:2px 6px;background:#999;color:white;border-radius:4px;font-size:10px;">
                                        <?php echo $is_approved ? '✅ Approved' : '⏳ Brand Pending'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="fm-pv-stack-project">
                            <?php echo fastmedia_project_toggle_ui($id); ?>
                        </div>

                        <div class="fm-pv-stack-actions">
                            <div class="fm-pv-actions-grid">
                                <button title="Share" onclick="copyShareLink(<?php echo $id; ?>)">🔗 Share</button>
                                <button title="Download" onclick="downloadAsset(<?php echo $id; ?>)">⬇️ Download</button>
                                <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>" title="Edit">✏️ Edit</a>
                                <button title="Delete" onclick="deleteAsset(<?php echo $id; ?>)">🗑️ Delete</button>
                            </div>
                        </div>
                        
                        <!-- Detail view (original toolbar content) -->
                        <div class="fm-pv-detail-layout">
                            <div class="fm-toolbar-row">
                                <?php foreach ($labels as $code): 
                                    if (isset($label_map[$code])):
                                ?>
                                    <span class="fm-label fm-label-<?php echo esc_attr($code); ?>" 
                                          title="<?php echo esc_attr($label_map[$code]); ?>">
                                        <?php echo esc_html($code); ?>
                                    </span>
                                <?php 
                                    endif;
                                endforeach; ?>
                                
                                <div class="fm-dropdown-labels">
                                    <button type="button" class="fm-labels-btn">Labels</button>
                                    <div class="fm-dropdown-content">
                                        <?php foreach ($label_map as $code => $desc):
                                            $checked = in_array($code, $labels) ? 'checked' : '';
                                            $disabled = in_array($code, ['ST', 'UP']) ? 'disabled' : '';
                                        ?>
                                            <label>
                                                <input type="checkbox" value="<?php echo esc_attr($code); ?>" <?php echo $checked; ?> <?php echo $disabled; ?>> 
                                                <strong><?php echo esc_html($code); ?></strong> - <?php echo esc_html($desc); ?>
                                            </label>
                                        <?php endforeach; ?>
                                        <button type="button" style="margin-top:8px;width:100%;padding:6px;background:#0073aa;color:white;border:none;border-radius:4px;cursor:pointer;font-size:13px;" onclick="saveLabels(<?php echo $id; ?>, this)">Save</button>
                                    </div>
                                </div>
                                
                                <?php if (!in_array('BR', $labels)): ?>
                                    <button style="padding:6px 10px;background:#000;color:white;border:none;border-radius:4px;cursor:pointer;font-size:13px;" onclick="suggestForBrand(<?php echo $id; ?>, this)">Suggest for Brand</button>
                                <?php else: ?>
                                    <span style="padding:4px 8px;background:#999;color:white;border-radius:4px;font-size:12px;">
                                        <?php echo $is_approved ? '✅ Approved' : '⏳ Pending'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="fm-toolbar-row">
                                <div class="fm-project-toggle">
                                    <?php echo fastmedia_project_toggle_ui($id); ?>
                                </div>
                                <?php if (function_exists('fastmedia_rating_ui')): ?>
                                    <?php echo fastmedia_rating_ui($id); ?>
                                <?php endif; ?>
                            </div>

                            <div class="fm-toolbar-row fm-toolbar-buttons">
                                <button title="Copy share link" onclick="copyShareLink(<?php echo $id; ?>)">🔗 Share</button>
                                <button title="Download full resolution" onclick="downloadAsset(<?php echo $id; ?>)">⬇️ Download</button>
                                <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>" title="Edit details">✏️ Edit</a>
                                <button title="Delete permanently" onclick="deleteAsset(<?php echo $id; ?>)">🗑️ Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    // Add nonce to page for AJAX calls
    window.fastmedia_nonce = '<?php echo wp_create_nonce("fastmedia_project_nonce"); ?>';
    
    function copyShareLink(assetId) {
        const url = '<?php echo site_url('/asset-detail/?id='); ?>' + assetId;
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copied!');
        }).catch(() => {
            prompt('Copy this link:', url);
        });
    }
    
    function updateBulkBar() {
        const checkboxes = document.querySelectorAll('.fastmedia-checkbox:checked');
        const bulkBar = document.getElementById('bulk-actions');
        const countSpan = document.getElementById('selected-count');
        
        if (checkboxes.length > 0) {
            bulkBar.classList.add('active');
            countSpan.textContent = checkboxes.length;
        } else {
            bulkBar.classList.remove('active');
        }
    }
    
    function selectAll() {
        document.querySelectorAll('.fastmedia-checkbox').forEach(cb => {
            cb.checked = true;
        });
        updateBulkBar();
    }
    
    function deselectAll() {
        document.querySelectorAll('.fastmedia-checkbox').forEach(cb => {
            cb.checked = false;
        });
        updateBulkBar();
    }
    
    function bulkDownload() {
        const selected = document.querySelectorAll('.fastmedia-checkbox:checked');
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
            
            const tile = selected[index].closest('.fastmedia-tile');
            const assetId = tile.dataset.assetId;
            const downloadUrl = '<?php echo admin_url("admin-ajax.php"); ?>?action=download_attachment&id=' + assetId;
            downloadFrame.src = downloadUrl;
            
            index++;
            setTimeout(downloadNext, 500);
        }
        
        downloadNext();
    }
    
    function bulkAddToProject() {
        const selected = document.querySelectorAll('.fastmedia-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select images to add to project');
            return;
        }
        
        const projectName = prompt('Enter project name to add ' + selected.length + ' assets to:');
        
        if (!projectName || projectName.trim() === '') {
            return;
        }
        
        // Make the same AJAX calls as the toggle button
        selected.forEach(cb => {
            const tile = cb.closest('.fastmedia-tile');
            const assetId = tile.dataset.assetId;
            
            const formData = new FormData();
            formData.append('action', 'fastmedia_toggle_project');
            formData.append('attachment_id', assetId);
            formData.append('project', projectName.trim());
            formData.append('toggle_action', 'add');
            formData.append('nonce', window.fastmedia_nonce);
            
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
        });
        
        alert('Added ' + selected.length + ' assets to project: ' + projectName.trim());
        setTimeout(() => location.reload(), 1000);
        deselectAll();
    }
    
    function bulkDelete() {
        const selected = document.querySelectorAll('.fastmedia-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select images to delete');
            return;
        }
        
        if (!confirm('Are you sure you want to delete ' + selected.length + ' assets? This action cannot be undone.')) {
            return;
        }
        
        selected.forEach(cb => {
            const tile = cb.closest('.fastmedia-tile');
            const assetId = tile.dataset.assetId;
            deleteAsset(assetId, true);
        });
        
        alert('Deleting ' + selected.length + ' assets...');
        setTimeout(() => location.reload(), 1500);
    }
    
    function sortAssets(sortBy) {
        // Reload page with sort parameter
        const url = new URL(window.location);
        url.searchParams.set('sort', sortBy);
        window.location.href = url.toString();
    }
    
    function setView(viewType) {
        const grid = document.querySelector('.fastmedia-grid');
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
        
        // Show/hide list view stacks
        if (viewType === 'list') {
            document.querySelectorAll('.fm-pv-stack-rating, .fm-pv-stack-labels, .fm-pv-stack-project, .fm-pv-stack-actions').forEach(el => {
                el.style.display = 'flex';
            });
            document.querySelectorAll('.fm-pv-detail-layout').forEach(el => {
                el.style.display = 'none';
            });
        } else {
            document.querySelectorAll('.fm-pv-stack-rating, .fm-pv-stack-labels, .fm-pv-stack-project, .fm-pv-stack-actions').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.fm-pv-detail-layout').forEach(el => {
                el.style.display = 'block';
            });
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
    
    function deleteAsset(assetId, skipConfirm) {
        if (!skipConfirm && !confirm('Are you sure you want to delete this asset? This action cannot be undone.')) return;
        
        // Create a form and submit to asset detail page for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/asset-detail/?id=' + assetId;
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_asset';
        input.value = assetId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    
    function saveLabels(assetId, button) {
        button.disabled = true;
        button.textContent = 'Saving...';
        
        const labelContainer = button.closest('.fm-dropdown-content');
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
    </script>
    <?php
    return ob_get_clean();
}

// SHORTCODE 1: [fastmedia_uploaded]
add_shortcode('fastmedia_uploaded', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a>.</p>';
    $user_id = get_current_user_id();
    
    // Apply sorting if parameter exists
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

    $attachments = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'author'         => $user_id,
        'post_mime_type' => 'image',
        'orderby'        => $orderby,
        'order'          => $order,
        'meta_query'     => [[
            'key'     => 'fastmedia_upload_status',
            'compare' => 'NOT EXISTS'
        ]]
    ]);
    return render_fastmedia_grid($attachments, 'UP');
});

// SHORTCODE 2: [fastmedia_purchased_assets]
add_shortcode('fastmedia_purchased_assets', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a>.</p>';

    $user_id = get_current_user_id();
    
    // Apply sorting if parameter exists - FIX ADDED HERE
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

    $args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'orderby'        => $orderby,
        'order'          => $order,
        'meta_query'     => [
            [
                'key'     => 'fastmedia_licensed',
                'value'   => 'yes',
                'compare' => '='
            ],
            [
                'key'     => 'fastmedia_buyer_id',
                'value'   => $user_id,
                'compare' => '='
            ]
        ]
    ];

    $query = new WP_Query($args);
    $attachments = [];

    foreach ($query->posts as $post) {
        $thumb = wp_get_attachment_image_src($post->ID, 'medium');
        $attachments[] = (object)[
            'ID'    => $post->ID,
            'title' => esc_html(get_the_title($post->ID)),
            'thumb' => $thumb ? $thumb[0] : 'https://placehold.co/400x300?text=Preview',
            'detail_url' => '/asset-detail/?id=' . $post->ID,
        ];
    }

    return render_fastmedia_grid($attachments, 'ST');
});

// SHORTCODE 3: [fastmedia_assets_my_assets]
add_shortcode('fastmedia_assets_my_assets', function () {
    if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a>.</p>';
    
    $user_id = get_current_user_id();
    
    // Get all uploaded assets
    $uploaded = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'author'         => $user_id,
        'post_mime_type' => 'image',
        'orderby'        => isset($_GET['sort']) ? 'title' : 'date',
        'order'          => 'DESC',
        'meta_query'     => [[
            'key'     => 'fastmedia_upload_status',
            'compare' => 'NOT EXISTS'
        ]]
    ]);
    
    // Get all purchased assets
    $args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'     => 'fastmedia_licensed',
                'value'   => 'yes',
                'compare' => '='
            ],
            [
                'key'     => 'fastmedia_buyer_id',
                'value'   => $user_id,
                'compare' => '='
            ]
        ]
    ];
    
    $query = new WP_Query($args);
    $purchased = [];
    
    foreach ($query->posts as $post) {
        $purchased[] = $post;
    }
    
    // Combine all assets
    $all_assets = array_merge($uploaded, $purchased);
    
    // Render as one grid with ALL badge type
    return render_fastmedia_grid($all_assets, 'ALL');
});

// AJAX handler for saving labels
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

// AJAX handler for suggesting brand
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

// AJAX handler for downloading attachments
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
