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
    /* Fix viewing header - make it smaller and cleaner */
    .fm-collapsible-header {
        background: #f8f9fa;
        padding: 6px 12px;
        margin-bottom: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #666;
    }
    .fm-collapsible-header:hover { 
        background: #f0f0f2;
        color: #333;
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
        z-index: 10;
        border-radius: 6px;
        min-width: 250px;
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
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .fastmedia-toolbar.collapsed {
        max-height: 0;
        margin-top: 0;
    }
    .fm-toolbar-toggle {
        font-size: 12px;
        color: #999;
        cursor: pointer;
        text-align: center;
        padding: 6px;
        margin: 12px -6px 0 -6px;
        border-top: 1px solid #f0f0f0;
    }
    .fm-toolbar-toggle:hover {
        color: #333;
        background: #f5f5f5;
        border-radius: 4px;
    }
    .fm-toolbar-content {
        padding: 8px 0;
    }
    .fm-toolbar-row {
        display: flex;
        gap: 6px;
        margin: 10px 0;
        align-items: center;
        flex-wrap: wrap;
    }
    .fm-toolbar-buttons {
        display: flex;
        gap: 4px;
    }
    .fm-toolbar-buttons button,
    .fm-toolbar-buttons a {
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
    .fm-toolbar-buttons button:hover,
    .fm-toolbar-buttons a:hover {
        background: #f0f0f0;
        text-decoration: none;
    }
    /* Fix project toggle button size */
    .fm-project-toggle button,
    .fm-project-toggle .project-toggle-btn {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 2px;
        font-size: 12px !important;
        line-height: 1;
        cursor: pointer;
        width: 20px !important;
        height: 20px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    /* Fix suggest for brand button size */
    .fm-toolbar-row button[onclick*="suggestForBrand"] {
        padding: 6px 10px !important;
        font-size: 13px !important;
        height: auto !important;
        width: auto !important;
    }
    
    /* View modes */
    .fastmedia-grid.mosaic-view .fm-tile-details,
    .fastmedia-grid.mosaic-view .fastmedia-toolbar,
    .fastmedia-grid.mosaic-view .fm-toolbar-toggle {
        display: none !important;
    }
    
    .fastmedia-grid.mosaic-view {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 4px;
    }
    
    .fastmedia-grid.mosaic-view .fastmedia-tile {
        padding: 4px;
        border-radius: 4px;
    }
    
    .fastmedia-grid.mosaic-view .fm-image-wrapper {
        margin: 0;
    }
    
    .fastmedia-grid.mosaic-view .fastmedia-checkbox {
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .fastmedia-grid.mosaic-view .fastmedia-tile:hover .fastmedia-checkbox {
        opacity: 1;
    }
    
    /* List view - everything on same line, always show toolbar */
    .fastmedia-grid.list-view {
        display: block !important;
        grid-template-columns: none !important;
    }
    
    .fastmedia-grid.list-view .fastmedia-tile {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 5px;
        padding: 8px 12px;
    }
    
    .fastmedia-grid.list-view .fm-image-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
    }
    
    .fastmedia-grid.list-view .fm-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .fastmedia-grid.list-view .fm-tile-details {
        flex: 0 0 250px;
        min-width: 0;
    }
    
    .fastmedia-grid.list-view .fm-tile-details strong {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block !important;
        margin: 0 !important;
    }
    
    .fastmedia-grid.list-view .fm-tile-details small {
        display: inline;
    }
    
    .fastmedia-grid.list-view .fastmedia-toolbar {
        position: static !important;
        opacity: 1 !important;
        display: flex !important;
        flex: 1;
        align-items: center;
        margin: 0;
        overflow: visible !important;
        max-height: none !important;
    }
    
    .fastmedia-grid.list-view .fastmedia-toolbar.collapsed {
        display: flex !important;
        max-height: none !important;
    }
    
    .fastmedia-grid.list-view .fm-toolbar-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0;
    }
    
    .fastmedia-grid.list-view .fm-toolbar-row {
        margin: 0;
        flex-wrap: nowrap;
    }
    
    .fastmedia-grid.list-view .fm-toolbar-toggle {
        display: none !important;
    }
    
    /* Hide file size in list view */
    .fastmedia-grid.list-view .fm-toolbar-content > div:last-child {
        display: none;
    }
    </style>

    <div class="fastmedia-wrapper">
        <div class="fm-collapsible-header" onclick="this.classList.toggle('collapsed')">
            <span>
                <?php 
                $user_id = get_current_user_id();
                $user = get_userdata($user_id);
                $first = esc_html(get_user_meta($user_id, 'first_name', true));
                $last = esc_html(get_user_meta($user_id, 'last_name', true));
                $full_name = trim($first . ' ' . $last) ?: 'Unnamed User';
                
                // Fix the header text based on actual badge type
                if ($badge_type === 'UP') {
                    echo 'Viewing uploaded assets for: ' . $full_name;
                } elseif ($badge_type === 'ST') {
                    echo 'Viewing licensed assets for: ' . $full_name;
                } elseif ($badge_type === 'ALL') {
                    echo 'Viewing all assets for: ' . $full_name;
                } else {
                    echo 'Viewing assets for: ' . $full_name;
                }
                ?>
            </span>
            <span>▼</span>
        </div>

        <div class="fastmedia-bulkbar" id="bulk-actions">
            <span><strong id="selected-count">0</strong> selected</span>
            <button onclick="selectAll()">Select All</button>
            <button onclick="deselectAll()">Deselect All</button>
            <button onclick="bulkDownload()">📥 Download</button>
            <button onclick="bulkAddToProject()">📁 Add to Project</button>
            <button onclick="bulkDelete()">🗑️ Delete</button>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px; gap: 10px;">
            <select id="sort-select" onchange="sortAssets(this.value)" style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                <option value="date-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'date-desc') ? 'selected' : ''; ?>>Newest First</option>
                <option value="date-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'date-asc') ? 'selected' : ''; ?>>Oldest First</option>
                <option value="name-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'name-asc') ? 'selected' : ''; ?>>Name A-Z</option>
                <option value="name-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'name-desc') ? 'selected' : ''; ?>>Name Z-A</option>
                <option value="size-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'size-desc') ? 'selected' : ''; ?>>Largest First</option>
                <option value="size-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'size-asc') ? 'selected' : ''; ?>>Smallest First</option>
            </select>
            <div style="display: flex; gap: 4px; border: 1px solid #ddd; border-radius: 4px; padding: 2px;">
                <button onclick="setView('detail')" class="view-btn active" title="Detail View" style="padding: 4px 8px; border: none; background: #e0e0e0; border-radius: 3px; cursor: pointer; color: #333;">⊞</button>
                <button onclick="setView('mosaic')" class="view-btn" title="Mosaic View" style="padding: 4px 8px; border: none; background: transparent; border-radius: 3px; cursor: pointer; color: #333;">▦</button>
                <button onclick="setView('list')" class="view-btn" title="List View" style="padding: 4px 8px; border: none; background: transparent; border-radius: 3px; cursor: pointer; color: #333;">☰</button>
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
                    <strong style="font-size: 15px; display: block; margin: 8px 0 4px 0;">
                        <?php echo $title; ?>
                        <?php if ($file_ext): ?>
                            <span style="font-size: 11px; color: #666; font-weight: normal;">(<?php echo esc_html($file_ext_upper); ?>)</span>
                        <?php endif; ?>
                    </strong>
                    <small style="color: #666;"><?php echo $date; ?></small>
                </div>

                <div class="fm-toolbar-toggle" onclick="toggleToolbar(this)">
                    ▼ Actions ▼
                </div>
                <div class="fastmedia-toolbar collapsed" data-asset-id="<?php echo $id; ?>">
                    <div class="fm-toolbar-content">
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
                                    <button type="button" style="margin-top:8px;width:100%;padding:6px;background:#0073aa;color:white;border:none;border-radius:4px;cursor:pointer;" onclick="saveLabels(<?php echo $id; ?>, this)">💾 Save</button>
                                </div>
                            </div>
                            
                            <?php if (!in_array('BR', $labels)): ?>
                                <button style="padding:6px 10px;background:#000;color:white;border:none;border-radius:4px;cursor:pointer;font-size:13px;" onclick="suggestForBrand(<?php echo $id; ?>, this)">Suggest for Brand</button>
                            <?php else: ?>
                                <span style="padding:6px 10px;background:#999;color:white;border-radius:4px;">
                                    <?php echo $is_approved ? '✅ Brand Approved' : '⏳ Pending Review'; ?>
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
                            <button title="Copy share link to clipboard" onclick="copyShareLink(<?php echo $id; ?>)">🔗</button>
                            <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>#highres" title="Download high resolution version">⬇️</a>
                            <a href="<?php echo esc_url($url); ?>" target="_blank" title="Download preview/comp version">📥</a>
                            <a href="/asset-detail/?id=<?php echo esc_attr($id); ?>" title="Edit asset details">✏️</a>
                            <button title="Delete this asset permanently" onclick="deleteAsset(<?php echo $id; ?>)">🗑️</button>
                        </div>
                        
                        <div style="font-size: 12px; color: #666; margin-top: 8px;">
                            File size: <?php echo $file_size_formatted; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function copyShareLink(assetId) {
        const url = '<?php echo site_url('/asset-detail/?id='); ?>' + assetId;
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copied!');
        }).catch(() => {
            prompt('Copy this link:', url);
        });
    }
    
    function toggleToolbar(toggle) {
        const toolbar = toggle.nextElementSibling;
        toolbar.classList.toggle('collapsed');
        toggle.textContent = toolbar.classList.contains('collapsed') ? '▼ Actions ▼' : '▲ Actions ▲';
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
            alert('Please select images');
            return;
        }
        selected.forEach(cb => {
            const tile = cb.closest('.fastmedia-tile');
            const assetId = tile.dataset.assetId;
            const link = tile.querySelector('a[title="Highres"]');
            if (link) window.open(link.href, '_blank');
        });
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
        
        // Use exact same method as individual project toggle
        selected.forEach(cb => {
            const tile = cb.closest('.fastmedia-tile');
            const assetId = tile.dataset.assetId;
            
            // This is the exact same call the individual toggle makes
            toggle_project(projectName.trim(), assetId, true);
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
        const buttons = document.querySelectorAll('.view-btn');
        
        // Remove all view classes
        grid.classList.remove('detail-view', 'mosaic-view', 'list-view');
        buttons.forEach(btn => {
            btn.style.background = 'transparent';
            btn.classList.remove('active');
        });
        
        // Add new view class
        grid.classList.add(viewType + '-view');
        
        // Highlight active button
        const activeBtn = Array.from(buttons).find(btn => 
            (viewType === 'detail' && btn.textContent === '⊞') ||
            (viewType === 'mosaic' && btn.textContent === '▦') ||
            (viewType === 'list' && btn.textContent === '☰')
        );
        if (activeBtn) {
            activeBtn.style.background = '#e0e0e0';
            activeBtn.classList.add('active');
        }
        
        // Store preference
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
        setTimeout(() => {
            button.textContent = 'Saved!';
            setTimeout(() => {
                button.textContent = '💾 Save';
                button.disabled = false;
            }, 1000);
        }, 500);
    }
    
    function suggestForBrand(assetId, button) {
        window.location.href = '/asset-detail/?id=' + assetId + '&suggest_brand=1';
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
    
    $output = '';
    $output .= '<h3 style="margin-top: 0;">Uploaded Assets</h3>';
    $output .= do_shortcode('[fastmedia_uploaded]');
    $output .= '<hr style="margin:40px 0;">';
    $output .= '<h3>Licensed Assets</h3>';
    $output .= do_shortcode('[fastmedia_purchased_assets]');
    
    return $output;
});
