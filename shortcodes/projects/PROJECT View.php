/**
 * FastMedia Project View Page
 * Handles both regular grid view and contact sheet view
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
    
    // Label definitions
    $label_definitions = array(
        'is_stock_image' => array('label' => 'ST', 'color' => '#0073aa', 'name' => 'Stock Image'),
        'is_user_upload' => array('label' => 'UP', 'color' => '#00a65a', 'name' => 'User Upload'),
        'is_brand_approved' => array('label' => 'BR', 'color' => '#000', 'name' => 'Brand Approved'),
        'is_logo' => array('label' => 'LO', 'color' => '#ff7700', 'name' => 'Logo'),
        'is_final_approved' => array('label' => 'FI', 'color' => '#e6b800', 'name' => 'Final Approved'),
        'is_photography' => array('label' => 'PH', 'color' => '#008080', 'name' => 'Photography'),
        'is_video' => array('label' => 'VI', 'color' => '#7a4dc9', 'name' => 'Video'),
        'is_vector' => array('label' => 'VC', 'color' => '#c62828', 'name' => 'Vector'),
        'is_ai_generated' => array('label' => 'AI', 'color' => '#444', 'name' => 'AI Generated')
    );
    
    // Get all attachments in this project
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
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $attachments = get_posts($args);
    
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
        
        // Refresh attachments list
        $attachments = get_posts($args);
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
        
        .fm-pv-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
        }
        
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
        
        .fm-pv-btn-danger {
            background: #f44336;
            color: white;
            border-color: #f44336;
        }
        
        .fm-pv-btn-danger:hover {
            background: #d32f2f;
            color: white;
        }
        
        .fm-pv-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .fm-pv-note-section {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        
        /* Grid View */
        .fm-pv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .fm-pv-asset-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: box-shadow 0.2s;
            position: relative;
        }
        
        .fm-pv-asset-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .fm-pv-asset-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .fm-pv-asset-info {
            padding: 15px;
        }
        
        .fm-pv-asset-title {
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 8px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .fm-pv-asset-meta {
            font-size: 12px;
            color: #666;
        }
        
        .fm-pv-asset-select {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .fm-pv-asset-labels {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }
        
        .fm-pv-label {
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            display: inline-block;
        }
        
        /* Contact Sheet View */
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
        
        .fm-pv-contact-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #333;
            color: #666;
            font-size: 14px;
        }
        
        @media print {
            .fm-pv-header,
            .fm-pv-actions,
            .fm-pv-asset-select,
            .fm-pv-btn {
                display: none !important;
            }
            
            .fm-pv-contact-sheet {
                box-shadow: none;
                padding: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .fm-pv-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .fm-pv-contact-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
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
                            
                            // Get labels
                            $labels = array();
                            foreach ($label_definitions as $meta_key => $label_info) {
                                if (get_post_meta($attachment->ID, $meta_key, true)) {
                                    $labels[] = $label_info['label'];
                                }
                            }
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
                    
                    <div class="fm-pv-contact-footer">
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
                <!-- Regular Grid View -->
                <div class="fm-pv-header">
                    <div class="fm-pv-title-row">
                        <h1 class="fm-pv-title">📁 <?php echo esc_html($project); ?></h1>
                        <a href="<?php echo site_url('/my-projects/'); ?>" class="fm-pv-back-btn">
                            ← Back to Projects
                        </a>
                    </div>
                    
                    <div class="fm-pv-actions">
                        <button type="button" class="fm-pv-btn" onclick="fmPvSelectAll()">
                            ☑️ Select All
                        </button>
                        <button type="button" class="fm-pv-btn" onclick="fmPvDeselectAll()">
                            ◻️ Deselect All
                        </button>
                        <form method="post" id="fm-pv-bulk-form" style="display: inline-flex; gap: 10px;">
                            <input type="hidden" name="fm_pv_remove_selected" value="1">
                            <button type="submit" class="fm-pv-btn fm-pv-btn-danger" onclick="return fmPvConfirmRemove()">
                                🗑️ Remove Selected
                            </button>
                        </form>
                        <select id="fm-pv-move-to" class="fm-pv-select">
                            <option value="">Move selected to...</option>
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
                        <button type="button" class="fm-pv-btn" onclick="fmPvMoveSelected()">
                            ↔️ Move
                        </button>
                        <a href="<?php echo site_url('/project-view/?project=' . urlencode($project) . '&view=contact'); ?>" 
                           class="fm-pv-btn fm-pv-btn-primary">
                            🎛️ Contact Sheet View
                        </a>
                    </div>
                    
                    <?php if ($project_note || true): // Always show note section ?>
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
                    <?php endif; ?>
                </div>
                
                <div class="fm-pv-grid">
                    <?php foreach ($attachments as $attachment): 
                        $image_url = wp_get_attachment_image_url($attachment->ID, 'medium');
                        if (!$image_url) continue;
                        
                        // Get labels
                        $labels = array();
                        foreach ($label_definitions as $meta_key => $label_info) {
                            if (get_post_meta($attachment->ID, $meta_key, true)) {
                                $labels[] = $label_info;
                            }
                        }
                        
                        // Get ratings
                        $likes = get_post_meta($attachment->ID, '_image_likes', true);
                        $dislikes = get_post_meta($attachment->ID, '_image_dislikes', true);
                        $like_count = is_array($likes) ? count($likes) : 0;
                        $dislike_count = is_array($dislikes) ? count($dislikes) : 0;
                    ?>
                        <div class="fm-pv-asset-card">
                            <input type="checkbox" 
                                   class="fm-pv-asset-select" 
                                   name="fm_pv_selected_ids[]" 
                                   value="<?php echo $attachment->ID; ?>"
                                   form="fm-pv-bulk-form">
                            
                            <?php if (!empty($labels)): ?>
                            <div class="fm-pv-asset-labels">
                                <?php foreach ($labels as $label_info): ?>
                                    <span class="fm-pv-label" 
                                          style="background: <?php echo esc_attr($label_info['color']); ?>"
                                          title="<?php echo esc_attr($label_info['name']); ?>">
                                        <?php echo esc_html($label_info['label']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <a href="/image-detail/?productID=<?php echo $attachment->ID; ?>" target="_blank">
                                <img src="<?php echo esc_url($image_url); ?>" 
                                     alt="<?php echo esc_attr($attachment->post_title); ?>" 
                                     class="fm-pv-asset-image"
                                     loading="lazy">
                            </a>
                            
                            <div class="fm-pv-asset-info">
                                <h3 class="fm-pv-asset-title" title="<?php echo esc_attr($attachment->post_title); ?>">
                                    <?php echo esc_html($attachment->post_title); ?>
                                </h3>
                                <div class="fm-pv-asset-meta">
                                    ID: <?php echo $attachment->ID; ?> • 
                                    👍 <?php echo $like_count; ?> 
                                    👎 <?php echo $dislike_count; ?>
                                </div>
                                
                                <!-- Rating UI -->
                                <div style="margin-top: 10px;">
                                    <?php echo fastmedia_rating_ui($attachment->ID); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
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
    function fmPvSelectAll() {
        document.querySelectorAll('.fm-pv-asset-select').forEach(cb => cb.checked = true);
    }
    
    function fmPvDeselectAll() {
        document.querySelectorAll('.fm-pv-asset-select').forEach(cb => cb.checked = false);
    }
    
    function fmPvConfirmRemove() {
        const selected = document.querySelectorAll('.fm-pv-asset-select:checked');
        if (selected.length === 0) {
            alert('Please select assets to remove');
            return false;
        }
        return confirm('Remove ' + selected.length + ' selected assets from this project?');
    }
    
    function fmPvMoveSelected() {
        const selected = document.querySelectorAll('.fm-pv-asset-select:checked');
        const moveTo = document.getElementById('fm-pv-move-to').value;
        
        if (selected.length === 0) {
            alert('Please select assets to move');
            return;
        }
        
        if (!moveTo) {
            alert('Please select a destination project');
            return;
        }
        
        if (confirm('Move ' + selected.length + ' selected assets to "' + moveTo + '"?')) {
            // Would implement AJAX move here
            alert('Move functionality will be implemented via AJAX');
        }
    }
    </script>

    <?php
    return ob_get_clean();
});
