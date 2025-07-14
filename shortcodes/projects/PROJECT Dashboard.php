/**
 * FastMedia Project Dashboard - Complete Code with All Features
 * All CSS prefixed with .fm-pd- to ensure complete isolation
 */

add_shortcode('fastmedia_project_dashboard', function () {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/signin/">sign in</a> to view your projects.</p>';
    }

    $user_id = get_current_user_id();
    
    // Get user's projects from the toggle system
    $user_projects = get_user_meta($user_id, 'fastmedia_user_projects', true);
    $user_projects = is_array($user_projects) ? $user_projects : array('Default');
    
    // Get archived projects
    $archived_projects = get_user_meta($user_id, 'fastmedia_archived_projects', true);
    $archived_projects = is_array($archived_projects) ? $archived_projects : array();
    
    // Filter out archived projects from display
    $active_projects = array_filter($user_projects, function($project) use ($archived_projects) {
        return !in_array($project, $archived_projects);
    });
    
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
    
    // Handle CSV Export EARLY (before any output)
    if (!empty($_POST['fm_pd_export']) && isset($_POST['fm_pd_project'])) {
        $project = sanitize_text_field($_POST['fm_pd_project']);
        $filename = sanitize_file_name($project) . '_assets_' . date('Y-m-d') . '.csv';
        
        // Clear all output buffers
        while (ob_get_level()) ob_end_clean();
        
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
            'posts_per_page' => -1
        );
        $attachments = get_posts($args);
        
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo "ID,Filename,URL,Labels,Likes,Dislikes\n";
        foreach ($attachments as $attachment) {
            $attachment_id = $attachment->ID;
            $attachment_file = basename(get_attached_file($attachment_id));
            $attachment_url = wp_get_attachment_url($attachment_id);
            
            // Get labels
            $labels = array();
            foreach ($label_definitions as $meta_key => $label_info) {
                if (get_post_meta($attachment_id, $meta_key, true)) {
                    $labels[] = $label_info['label'];
                }
            }
            
            // Get ratings
            $likes = get_post_meta($attachment_id, '_image_likes', true);
            $dislikes = get_post_meta($attachment_id, '_image_dislikes', true);
            $like_count = is_array($likes) ? count($likes) : 0;
            $dislike_count = is_array($dislikes) ? count($dislikes) : 0;
            
            echo $attachment_id . ',"' . $attachment_file . '","' . $attachment_url . '","' . 
                 implode(',', $labels) . '",' . $like_count . ',' . $dislike_count . "\n";
        }
        exit;
    }
    
    // Handle Create Project
    if (!empty($_POST['fm_pd_new_project'])) {
        $new_project = sanitize_text_field($_POST['fm_pd_new_project']);
        if (!in_array($new_project, $user_projects)) {
            $user_projects[] = $new_project;
            update_user_meta($user_id, 'fastmedia_user_projects', $user_projects);
            echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Project "' . esc_html($new_project) . '" created successfully!</div>';
        }
    }
    
    // Handle Rename Project
    if (!empty($_POST['fm_pd_rename_from']) && !empty($_POST['fm_pd_rename_to'])) {
        $old_name = sanitize_text_field($_POST['fm_pd_rename_from']);
        $new_name = sanitize_text_field($_POST['fm_pd_rename_to']);
        
        if (in_array($old_name, $user_projects) && !in_array($new_name, $user_projects)) {
            // Update project list
            $key = array_search($old_name, $user_projects);
            $user_projects[$key] = $new_name;
            update_user_meta($user_id, 'fastmedia_user_projects', $user_projects);
            
            // Update all attachments
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'author' => $user_id,
                'meta_query' => array(
                    array(
                        'key' => 'fastmedia_projects',
                        'value' => serialize($old_name),
                        'compare' => 'LIKE'
                    )
                ),
                'posts_per_page' => -1
            );
            $attachments = get_posts($args);
            
            foreach ($attachments as $attachment) {
                $projects = get_post_meta($attachment->ID, 'fastmedia_projects', true);
                if (is_array($projects)) {
                    $projects = array_map(function($p) use ($old_name, $new_name) {
                        return $p === $old_name ? $new_name : $p;
                    }, $projects);
                    update_post_meta($attachment->ID, 'fastmedia_projects', $projects);
                }
            }
            
            echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Project renamed successfully!</div>';
        }
    }
    
    // Handle Archive Project (instead of delete)
    if (!empty($_POST['fm_pd_archive_project'])) {
        $project_to_archive = sanitize_text_field($_POST['fm_pd_archive_project']);
        if ($project_to_archive !== 'Default' && in_array($project_to_archive, $user_projects)) {
            // Add to archived projects list
            $archived_projects = get_user_meta($user_id, 'fastmedia_archived_projects', true);
            $archived_projects = is_array($archived_projects) ? $archived_projects : array();
            
            if (!in_array($project_to_archive, $archived_projects)) {
                $archived_projects[] = $project_to_archive;
                update_user_meta($user_id, 'fastmedia_archived_projects', $archived_projects);
                
                // Remove from active projects list
                $user_projects = array_filter($user_projects, function($p) use ($project_to_archive) {
                    return $p !== $project_to_archive;
                });
                update_user_meta($user_id, 'fastmedia_user_projects', array_values($user_projects));
                
                echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Project archived successfully! <a href="' . site_url('/project-archive/') . '" style="color:#2e7d32;text-decoration:underline;">View archived projects</a></div>';
            }
        }
    }
    
    // Handle Save Project Note
    if (!empty($_POST['fm_pd_note_project']) && isset($_POST['fm_pd_project_note'])) {
        $project = sanitize_text_field($_POST['fm_pd_note_project']);
        $note = sanitize_textarea_field($_POST['fm_pd_project_note']);
        
        update_user_meta($user_id, 'fastmedia_project_note_' . $project, $note);
        echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Project note saved successfully!</div>';
    }
    
    // Handle Move Assets
    if (!empty($_POST['fm_pd_move_from']) && !empty($_POST['fm_pd_move_to'])) {
        $from_project = sanitize_text_field($_POST['fm_pd_move_from']);
        $to_project = sanitize_text_field($_POST['fm_pd_move_to']);
        
        if ($from_project !== $to_project) {
            // Get all attachments in source project
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'author' => $user_id,
                'meta_query' => array(
                    array(
                        'key' => 'fastmedia_projects',
                        'value' => serialize($from_project),
                        'compare' => 'LIKE'
                    )
                ),
                'posts_per_page' => -1
            );
            $attachments = get_posts($args);
            
            $moved_count = 0;
            foreach ($attachments as $attachment) {
                $projects = get_post_meta($attachment->ID, 'fastmedia_projects', true);
                if (is_array($projects)) {
                    // Remove from source project
                    $projects = array_filter($projects, function($p) use ($from_project) {
                        return $p !== $from_project;
                    });
                    // Add to destination project
                    if (!in_array($to_project, $projects)) {
                        $projects[] = $to_project;
                    }
                    update_post_meta($attachment->ID, 'fastmedia_projects', array_values($projects));
                    $moved_count++;
                    
                    // Log activity
                    $activity_log = get_post_meta($attachment->ID, 'fastmedia_activity_log', true) ?: array();
                    $user_info = get_userdata($user_id);
                    $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' moved from project: ' . $from_project . ' to: ' . $to_project;
                    update_post_meta($attachment->ID, 'fastmedia_activity_log', array_slice($activity_log, -50));
                }
            }
            
            echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Moved ' . $moved_count . ' assets from "' . esc_html($from_project) . '" to "' . esc_html($to_project) . '"</div>';
        }
    }
    
    // Handle Copy Assets
    if (!empty($_POST['fm_pd_copy_from']) && !empty($_POST['fm_pd_copy_to'])) {
        $from_project = sanitize_text_field($_POST['fm_pd_copy_from']);
        $to_project = sanitize_text_field($_POST['fm_pd_copy_to']);
        
        // Handle new project creation
        if ($to_project === '__new__' && !empty($_POST['fm_pd_new_copy_project'])) {
            $to_project = sanitize_text_field($_POST['fm_pd_new_copy_project']);
            if (!in_array($to_project, $user_projects)) {
                $user_projects[] = $to_project;
                update_user_meta($user_id, 'fastmedia_user_projects', $user_projects);
            }
        }
        
        if ($from_project !== $to_project && $to_project !== '__new__') {
            // Get all attachments in source project
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'author' => $user_id,
                'meta_query' => array(
                    array(
                        'key' => 'fastmedia_projects',
                        'value' => serialize($from_project),
                        'compare' => 'LIKE'
                    )
                ),
                'posts_per_page' => -1
            );
            $attachments = get_posts($args);
            
            $copied_count = 0;
            foreach ($attachments as $attachment) {
                $projects = get_post_meta($attachment->ID, 'fastmedia_projects', true);
                if (is_array($projects)) {
                    // Add to destination project (keep in source too)
                    if (!in_array($to_project, $projects)) {
                        $projects[] = $to_project;
                        update_post_meta($attachment->ID, 'fastmedia_projects', $projects);
                        $copied_count++;
                        
                        // Log activity
                        $activity_log = get_post_meta($attachment->ID, 'fastmedia_activity_log', true) ?: array();
                        $user_info = get_userdata($user_id);
                        $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' copied to project: ' . $to_project;
                        update_post_meta($attachment->ID, 'fastmedia_activity_log', array_slice($activity_log, -50));
                    }
                }
            }
            
            echo '<div style="background:#e8f5e9;color:#2e7d32;padding:15px;margin:20px 0;border-radius:5px;">✅ Copied ' . $copied_count . ' assets to "' . esc_html($to_project) . '"</div>';
        }
    }

    ob_start();
    
    // Add AJAX handler for activity log
    add_action('wp_ajax_fastmedia_get_project_activity', function() {
        if (!wp_verify_nonce($_POST['nonce'], 'fm_project_activity')) {
            wp_send_json_error('Security check failed');
        }
        
        $project = sanitize_text_field($_POST['project']);
        $user_id = get_current_user_id();
        
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
            'posts_per_page' => -1
        );
        $attachments = get_posts($args);
        
        $all_activities = array();
        
        // Collect activity logs from all attachments
        foreach ($attachments as $attachment) {
            $activity_log = get_post_meta($attachment->ID, 'fastmedia_activity_log', true);
            if (is_array($activity_log)) {
                foreach ($activity_log as $activity) {
                    // Add asset reference to each activity
                    $all_activities[] = $activity . ' (Asset #' . $attachment->ID . ')';
                }
            }
        }
        
        // Sort by date (newest first)
        rsort($all_activities);
        
        // Limit to last 50 activities
        $all_activities = array_slice($all_activities, 0, 50);
        
        wp_send_json_success(array('activities' => $all_activities));
    });
    ?>
    
    <!-- Completely isolated CSS with unique prefix -->
    <style>
        /* Container wrapper for complete isolation */
        .fm-pd-wrapper {
            /* Reset inherited styles */
            all: initial;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            box-sizing: border-box;
            display: block;
        }
        
        .fm-pd-wrapper * {
            box-sizing: inherit;
        }
        
        .fm-pd-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .fm-pd-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .fm-pd-title {
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .fm-pd-header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .fm-pd-search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            width: 250px;
            transition: border-color 0.2s;
        }
        
        .fm-pd-search-input:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .fm-pd-btn-create {
            background: #4CAF50;
            color: white !important;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .fm-pd-btn-create:hover {
            background: #45a049;
            color: white !important;
            text-decoration: none;
        }
        
        .fm-pd-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .fm-pd-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        
        .fm-pd-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .fm-pd-thumb-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 2px;
            height: 200px;
            background: #f5f5f5;
            cursor: pointer;
            position: relative;
        }
        
        .fm-pd-thumb {
            background: #e0e0e0;
            overflow: hidden;
        }
        
        .fm-pd-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .fm-pd-thumb:hover img {
            transform: scale(1.05);
        }
        
        .fm-pd-empty-thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            background: #f0f0f0;
            color: #999;
            font-size: 60px;
            cursor: pointer;
        }
        
        .fm-pd-card-body {
            padding: 20px;
        }
        
        .fm-pd-project-name {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .fm-pd-project-stats {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .fm-pd-stat {
            text-align: center;
            flex: 1;
        }
        
        .fm-pd-stat-value {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            display: block;
        }
        
        .fm-pd-stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
            display: block;
        }
        
        .fm-pd-meta {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }
        
        .fm-pd-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .fm-pd-btn {
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
        
        .fm-pd-btn:hover {
            background: #f5f5f5;
            border-color: #bbb;
            text-decoration: none;
            color: #333;
        }
        
        .fm-pd-menu {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 5;
        }
        
        .fm-pd-menu-btn {
            background: rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.2s;
            color: #333;
        }
        
        .fm-pd-menu-btn:hover {
            background: rgba(0,0,0,0.1);
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }
        
        .fm-pd-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            min-width: 200px;
            display: none;
            z-index: 10;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .fm-pd-dropdown.fm-pd-show {
            display: block;
        }
        
        .fm-pd-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 12px 16px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .fm-pd-dropdown-item:hover {
            background: #f5f5f5;
        }
        
        .fm-pd-form-inline {
            margin: 0;
            padding: 0;
        }
        
        /* Label badges */
        .fm-pd-labels {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }
        
        .fm-pd-label {
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            display: inline-block;
        }
        
        .fm-pd-more-labels {
            background: #666;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
        }
        
        /* Stats summary */
        .fm-pd-stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .fm-pd-summary-stat {
            text-align: center;
        }
        
        .fm-pd-summary-value {
            font-size: 36px;
            font-weight: 700;
            color: #4CAF50;
            display: block;
            line-height: 1;
        }
        
        .fm-pd-summary-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        /* Net rating indicator */
        .fm-pd-rating-badge {
            position: absolute;
            top: 10px;
            right: 50px;
            background: rgba(255,255,255,0.95);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .fm-pd-rating-positive {
            color: #4CAF50;
        }
        
        .fm-pd-rating-negative {
            color: #f44336;
        }
        
        .fm-pd-rating-neutral {
            color: #666;
        }
        
        /* Modal for create/rename */
        .fm-pd-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 1000;
            min-width: 300px;
        }
        
        .fm-pd-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .fm-pd-modal h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #333;
        }
        
        .fm-pd-modal input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .fm-pd-modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .fm-pd-modal-btn {
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .fm-pd-modal-btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .fm-pd-modal-btn-primary:hover {
            background: #45a049;
        }
        
        .fm-pd-modal-btn-cancel {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .fm-pd-modal-btn-cancel:hover {
            background: #e0e0e0;
        }
        
        /* Empty state */
        .fm-pd-empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .fm-pd-empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .fm-pd-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .fm-pd-grid {
                grid-template-columns: 1fr;
            }
            
            .fm-pd-title {
                font-size: 24px;
                margin-bottom: 15px;
            }
        }
    </style>

    <div class="fm-pd-wrapper">
        <div class="fm-pd-container">
            <div class="fm-pd-header">
                <h1 class="fm-pd-title">📁 My Projects</h1>
                <div class="fm-pd-header-actions">
                    <input type="text" 
                           id="fm-pd-search" 
                           class="fm-pd-search-input" 
                           placeholder="Search projects..." 
                           onkeyup="fmPdFilterProjects()">
                    <button class="fm-pd-btn-create" onclick="fmPdCreateProject()">
                        + Create Project
                    </button>
                    <a href="<?php echo site_url('/project-archive/'); ?>" class="fm-pd-btn" style="background:#666;color:white;">
                        📦 Archived Projects
                    </a>
                </div>
            </div>
            
            <?php
            // Calculate overall stats
            $total_projects = count($active_projects);
            $total_assets = 0;
            $total_likes = 0;
            $total_dislikes = 0;
            $all_project_data = array();
            
            foreach ($active_projects as $project) {
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
                    'posts_per_page' => -1
                );
                $attachments = get_posts($args);
                
                $project_likes = 0;
                $project_dislikes = 0;
                $project_labels = array();
                $project_last_modified = 0;
                
                foreach ($attachments as $attachment) {
                    $likes = get_post_meta($attachment->ID, '_image_likes', true);
                    $dislikes = get_post_meta($attachment->ID, '_image_dislikes', true);
                    $project_likes += is_array($likes) ? count($likes) : 0;
                    $project_dislikes += is_array($dislikes) ? count($dislikes) : 0;
                    
                    // Track last modified
                    $attachment_modified = get_post_modified_time('U', false, $attachment->ID);
                    if (!isset($project_last_modified) || $attachment_modified > $project_last_modified) {
                        $project_last_modified = $attachment_modified;
                    }
                    
                    // Collect labels
                    foreach ($label_definitions as $meta_key => $label_info) {
                        if (get_post_meta($attachment->ID, $meta_key, true)) {
                            $label = $label_info['label'];
                            if (!isset($project_labels[$label])) {
                                $project_labels[$label] = 0;
                            }
                            $project_labels[$label]++;
                        }
                    }
                }
                
                $project_data = array(
                    'attachments' => $attachments,
                    'count' => count($attachments),
                    'likes' => $project_likes,
                    'dislikes' => $project_dislikes,
                    'labels' => $project_labels,
                    'thumbnails' => array_slice($attachments, 0, 4),
                    'last_modified' => $project_last_modified
                );
                
                $all_project_data[$project] = $project_data;
                
                $total_assets += count($attachments);
                $total_likes += $project_likes;
                $total_dislikes += $project_dislikes;
            }
            
            $net_rating = $total_likes - $total_dislikes;
            ?>
            
            <!-- Statistics Summary -->
            <div class="fm-pd-stats-summary">
                <div class="fm-pd-summary-stat">
                    <span class="fm-pd-summary-value"><?php echo $total_projects; ?></span>
                    <span class="fm-pd-summary-label">Total Projects</span>
                </div>
                <div class="fm-pd-summary-stat">
                    <span class="fm-pd-summary-value"><?php echo $total_assets; ?></span>
                    <span class="fm-pd-summary-label">Total Assets</span>
                </div>
                <div class="fm-pd-summary-stat">
                    <span class="fm-pd-summary-value"><?php echo $total_likes; ?></span>
                    <span class="fm-pd-summary-label">Total Likes</span>
                </div>
                <div class="fm-pd-summary-stat">
                    <span class="fm-pd-summary-value"><?php echo $total_dislikes; ?></span>
                    <span class="fm-pd-summary-label">Total Dislikes</span>
                </div>
                <div class="fm-pd-summary-stat">
                    <span class="fm-pd-summary-value <?php echo $net_rating > 0 ? 'fm-pd-rating-positive' : ($net_rating < 0 ? 'fm-pd-rating-negative' : ''); ?>">
                        <?php echo $net_rating > 0 ? '+' : ''; ?><?php echo $net_rating; ?>
                    </span>
                    <span class="fm-pd-summary-label">Net Rating</span>
                </div>
            </div>
            
            <div class="fm-pd-grid">
                <?php foreach ($active_projects as $project): 
                    $project_data = $all_project_data[$project];
                    $net_project_rating = $project_data['likes'] - $project_data['dislikes'];
                ?>
                    <div class="fm-pd-card" data-project="<?php echo esc_attr(strtolower($project)); ?>">
                        <?php if ($net_project_rating !== 0): ?>
                        <div class="fm-pd-rating-badge">
                            <span class="<?php echo $net_project_rating > 0 ? 'fm-pd-rating-positive' : 'fm-pd-rating-negative'; ?>">
                                <?php echo $net_project_rating > 0 ? '+' : ''; ?><?php echo $net_project_rating; ?>
                            </span>
                            <span>⭐</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="fm-pd-menu">
                            <button class="fm-pd-menu-btn" onclick="fmPdToggleMenu(this)">⋮</button>
                            <div class="fm-pd-dropdown">
                                <a href="<?php echo site_url('/project-view/?project=' . urlencode($project)); ?>" class="fm-pd-dropdown-item">
                                    👁️ View Project
                                </a>
                                <button class="fm-pd-dropdown-item" onclick="navigator.clipboard.writeText('<?php echo site_url('/project-view/?project=' . urlencode($project)); ?>'); alert('Link copied!')">
                                    📤 Share Link
                                </button>
                                <button class="fm-pd-dropdown-item" onclick="fmPdMoveAssets('<?php echo esc_attr($project); ?>')">
                                    ↔️ Move Assets
                                </button>
                                <button class="fm-pd-dropdown-item" onclick="fmPdCopyAssets('<?php echo esc_attr($project); ?>')">
                                    📋 Copy Assets
                                </button>
                                <button class="fm-pd-dropdown-item" onclick="fmPdEditNote('<?php echo esc_attr($project); ?>')">
                                    📝 Edit Note
                                </button>
                                <button class="fm-pd-dropdown-item" onclick="fmPdViewActivity('<?php echo esc_attr($project); ?>')">
                                    📊 View Activity
                                </button>
                                <button class="fm-pd-dropdown-item" onclick="fmPdRenameProject('<?php echo esc_attr($project); ?>')">
                                    ✏️ Rename
                                </button>
                                <?php if ($project !== 'Default'): ?>
                                <button class="fm-pd-dropdown-item" onclick="fmPdArchiveProject('<?php echo esc_attr($project); ?>')">
                                    📦 Archive
                                </button>
                                <?php endif; ?>
                                <form method="post" class="fm-pd-form-inline" target="fm_pd_export_frame">
                                    <input type="hidden" name="fm_pd_export" value="1">
                                    <input type="hidden" name="fm_pd_project" value="<?php echo esc_attr($project); ?>">
                                    <button type="submit" class="fm-pd-dropdown-item">
                                        📦 Export CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if (!empty($project_data['thumbnails'])): ?>
                            <div class="fm-pd-thumb-grid" onclick="window.location.href='<?php echo site_url('/project-view/?project=' . urlencode($project)); ?>'">
                                <?php 
                                $thumb_count = 0;
                                foreach ($project_data['thumbnails'] as $thumb): 
                                    $thumb_url = wp_get_attachment_image_url($thumb->ID, 'thumbnail');
                                    if ($thumb_url):
                                ?>
                                    <div class="fm-pd-thumb">
                                        <img src="<?php echo esc_url($thumb_url); ?>" alt="" loading="lazy">
                                    </div>
                                <?php 
                                    $thumb_count++;
                                    endif;
                                endforeach; 
                                
                                // Fill empty slots
                                for ($i = $thumb_count; $i < 4; $i++):
                                ?>
                                    <div class="fm-pd-thumb"></div>
                                <?php endfor; ?>
                                
                                <?php if (!empty($project_data['labels'])): ?>
                                <div class="fm-pd-labels">
                                    <?php 
                                    $shown_labels = 0;
                                    foreach ($project_data['labels'] as $label => $count): 
                                        if ($shown_labels >= 5) {
                                            $remaining = count($project_data['labels']) - 5;
                                            echo '<span class="fm-pd-more-labels">+' . $remaining . '</span>';
                                            break;
                                        }
                                        $label_info = null;
                                        foreach ($label_definitions as $def) {
                                            if ($def['label'] === $label) {
                                                $label_info = $def;
                                                break;
                                            }
                                        }
                                        if ($label_info):
                                    ?>
                                        <span class="fm-pd-label" style="background: <?php echo esc_attr($label_info['color']); ?>" 
                                              title="<?php echo esc_attr($label_info['name'] . ' (' . $count . ')'); ?>">
                                            <?php echo esc_html($label); ?>
                                        </span>
                                    <?php 
                                        $shown_labels++;
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="fm-pd-empty-thumb" onclick="window.location.href='<?php echo site_url('/project-view/?project=' . urlencode($project)); ?>'">
                                📁
                            </div>
                        <?php endif; ?>
                        
                        <div class="fm-pd-card-body">
                            <h3 class="fm-pd-project-name"><?php echo esc_html($project); ?></h3>
                            
                            <div class="fm-pd-project-stats">
                                <div class="fm-pd-stat">
                                    <span class="fm-pd-stat-value"><?php echo $project_data['count']; ?></span>
                                    <span class="fm-pd-stat-label">Assets</span>
                                </div>
                                <div class="fm-pd-stat">
                                    <span class="fm-pd-stat-value"><?php echo $project_data['likes']; ?></span>
                                    <span class="fm-pd-stat-label">Likes</span>
                                </div>
                                <div class="fm-pd-stat">
                                    <span class="fm-pd-stat-value"><?php echo $project_data['dislikes']; ?></span>
                                    <span class="fm-pd-stat-label">Dislikes</span>
                                </div>
                            </div>
                            
                            <div class="fm-pd-meta">
                                <small>Last updated: <?php 
                                    if ($project_data['last_modified'] > 0) {
                                        echo human_time_diff($project_data['last_modified'], current_time('timestamp')) . ' ago';
                                    } else {
                                        echo 'Never';
                                    }
                                ?></small>
                            </div>
                            
                            <div class="fm-pd-actions">
                                <a href="<?php echo site_url('/project-view/?project=' . urlencode($project)); ?>" class="fm-pd-btn">
                                    👁️ View Project
                                </a>
                                <a href="<?php echo site_url('/project-view/?project=' . urlencode($project) . '&view=contact'); ?>" class="fm-pd-btn">
                                    🎛️ Contact Sheet
                                </a>
                            </div>
                            
                            <?php 
                            // Show rating UI for the first asset in the project as a sample
                            if (!empty($project_data['attachments'][0])): 
                                $first_asset = $project_data['attachments'][0];
                            ?>
                            <div class="fm-pd-project-sample-rating">
                                <small style="color: #666; display: block; margin: 10px 0 5px 0;">Rate sample asset:</small>
                                <?php echo fastmedia_rating_ui($first_asset->ID); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php
                            // Get project note
                            $project_note = get_user_meta($user_id, 'fastmedia_project_note_' . $project, true);
                            if ($project_note):
                            ?>
                            <div class="fm-pd-project-note">
                                <small style="color: #666;">Note: <?php echo esc_html($project_note); ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($active_projects)): ?>
            <div class="fm-pd-empty-state">
                <h3>No active projects</h3>
                <p>Create your first project to start organizing your assets.</p>
                <?php if (!empty($archived_projects)): ?>
                <p><a href="<?php echo site_url('/project-archive/'); ?>">View <?php echo count($archived_projects); ?> archived project<?php echo count($archived_projects) > 1 ? 's' : ''; ?></a></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modals -->
    <div class="fm-pd-modal-overlay" onclick="fmPdCloseModal()"></div>
    
    <div id="fm-pd-create-modal" class="fm-pd-modal">
        <h3>Create New Project</h3>
        <form method="post" id="fm-pd-create-form">
            <input type="text" 
                   name="fm_pd_new_project" 
                   id="fm-pd-new-project-name" 
                   placeholder="Project name..." 
                   required>
            <div class="fm-pd-modal-actions">
                <button type="submit" class="fm-pd-modal-btn fm-pd-modal-btn-primary">Create</button>
                <button type="button" class="fm-pd-modal-btn fm-pd-modal-btn-cancel" onclick="fmPdCloseModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div id="fm-pd-rename-modal" class="fm-pd-modal">
        <h3>Rename Project</h3>
        <form method="post" id="fm-pd-rename-form">
            <input type="hidden" name="fm_pd_rename_from" id="fm-pd-rename-from">
            <input type="text" 
                   name="fm_pd_rename_to" 
                   id="fm-pd-rename-to" 
                   placeholder="New name..." 
                   required>
            <div class="fm-pd-modal-actions">
                <button type="submit" class="fm-pd-modal-btn fm-pd-modal-btn-primary">Rename</button>
                <button type="button" class="fm-pd-modal-btn fm-pd-modal-btn-cancel" onclick="fmPdCloseModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div id="fm-pd-note-modal" class="fm-pd-modal">
        <h3>Edit Project Note</h3>
        <form method="post" id="fm-pd-note-form">
            <input type="hidden" name="fm_pd_note_project" id="fm-pd-note-project">
            <textarea name="fm_pd_project_note" 
                      id="fm-pd-project-note" 
                      placeholder="Add a note for this project..." 
                      style="width: 100%; min-height: 100px; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; resize: vertical;"></textarea>
            <div class="fm-pd-modal-actions">
                <button type="submit" class="fm-pd-modal-btn fm-pd-modal-btn-primary">Save Note</button>
                <button type="button" class="fm-pd-modal-btn fm-pd-modal-btn-cancel" onclick="fmPdCloseModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div id="fm-pd-activity-modal" class="fm-pd-modal" style="min-width: 500px;">
        <h3>Project Activity Log</h3>
        <div id="fm-pd-activity-content" style="max-height: 400px; overflow-y: auto; margin: 20px 0; padding: 10px; background: #f5f5f5; border-radius: 5px;">
            <p>Loading activity...</p>
        </div>
        <div class="fm-pd-modal-actions">
            <button type="button" class="fm-pd-modal-btn fm-pd-modal-btn-cancel" onclick="fmPdCloseModal()">Close</button>
        </div>
    </div>
    
    <div id="fm-pd-move-modal" class="fm-pd-modal">
        <h3>Move Assets to Another Project</h3>
        <form method="post" id="fm-pd-move-form">
            <input type="hidden" name="fm_pd_move_from" id="fm-pd-move-from">
            <label style="display: block; margin-bottom: 10px;">Move all assets from this project to:</label>
            <select name="fm_pd_move_to" id="fm-pd-move-to" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                <option value="">Select destination project...</option>
                <?php foreach ($active_projects as $proj): ?>
                    <option value="<?php echo esc_attr($proj); ?>"><?php echo esc_html($proj); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="fm-pd-modal-actions">
                <button type="submit" class="fm-pd-modal-btn fm-pd-modal-btn-primary">Move Assets</button>
                <button type="button" class="fm-pd-modal-btn fm-pd-modal-btn-cancel" onclick="fmPdCloseModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div id="fm-pd-copy-modal" class="fm-pd-modal">
        <h3>Copy Assets to Another Project</h3>
        <form method="post" id="fm-pd-copy-form">
            <input type="hidden" name="fm_pd_copy_from" id="fm-pd-copy-from">
            <label style="display: block; margin-bottom: 10px;">Copy all assets from this project to:</label>
            <select name="fm_pd_copy_to" id="fm-pd-copy-to" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                <option value="">Select destination project...</option>
                <?php foreach ($active_projects as $proj): ?>
                    <option value="<?php echo esc_attr($proj); ?>"><?php echo esc_html($proj); ?></option>
                <?php endforeach; ?>
                <option value="__new__">+ Create New Project</option>
            </select>
            <div class="fm-pd-modal-actions">
                <button type="submit" class="fm-pd-modal-btn fm-pd-modal-btn-primary">Copy Assets</button>
                <button type="button" class="fm-pd-modal-btn fm-pd-modal-btn-cancel" onclick="fmPdCloseModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <iframe name="fm_pd_export_frame" style="display:none;"></iframe>

    <script>
    // Scoped JavaScript
    (function() {
        // Menu toggle
        window.fmPdToggleMenu = function(btn) {
            const dropdown = btn.nextElementSibling;
            const wasOpen = dropdown.classList.contains('fm-pd-show');
            
            // Close all dropdowns
            document.querySelectorAll('.fm-pd-dropdown').forEach(d => d.classList.remove('fm-pd-show'));
            
            // Toggle this one
            if (!wasOpen) {
                dropdown.classList.add('fm-pd-show');
            }
        };
        
        // Search/filter projects
        window.fmPdFilterProjects = function() {
            const searchTerm = document.getElementById('fm-pd-search').value.toLowerCase();
            const cards = document.querySelectorAll('.fm-pd-card');
            
            cards.forEach(card => {
                const projectName = card.getAttribute('data-project');
                if (projectName.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        };
        
        // Create project
        window.fmPdCreateProject = function() {
            document.getElementById('fm-pd-create-modal').style.display = 'block';
            document.querySelector('.fm-pd-modal-overlay').style.display = 'block';
            document.getElementById('fm-pd-new-project-name').focus();
        };
        
        // Rename project
        window.fmPdRenameProject = function(projectName) {
            document.getElementById('fm-pd-rename-from').value = projectName;
            document.getElementById('fm-pd-rename-to').value = projectName;
            document.getElementById('fm-pd-rename-modal').style.display = 'block';
            document.querySelector('.fm-pd-modal-overlay').style.display = 'block';
            document.getElementById('fm-pd-rename-to').select();
        };
        
        // Move assets
        window.fmPdMoveAssets = function(projectName) {
            document.getElementById('fm-pd-move-from').value = projectName;
            document.getElementById('fm-pd-move-to').value = '';
            // Remove current project from options
            const options = document.querySelectorAll('#fm-pd-move-to option');
            options.forEach(opt => {
                opt.style.display = opt.value === projectName ? 'none' : '';
            });
            document.getElementById('fm-pd-move-modal').style.display = 'block';
            document.querySelector('.fm-pd-modal-overlay').style.display = 'block';
        };
        
        // Copy assets
        window.fmPdCopyAssets = function(projectName) {
            document.getElementById('fm-pd-copy-from').value = projectName;
            document.getElementById('fm-pd-copy-to').value = '';
            // Remove current project from options
            const options = document.querySelectorAll('#fm-pd-copy-to option');
            options.forEach(opt => {
                opt.style.display = opt.value === projectName ? 'none' : '';
            });
            document.getElementById('fm-pd-copy-modal').style.display = 'block';
            document.querySelector('.fm-pd-modal-overlay').style.display = 'block';
        };
        
        // Edit project note
        window.fmPdEditNote = function(projectName) {
            document.getElementById('fm-pd-note-project').value = projectName;
            // Get current note if exists
            const noteElement = document.querySelector('[data-project="' + projectName.toLowerCase() + '"] .fm-pd-project-note');
            if (noteElement) {
                const currentNote = noteElement.querySelector('small').textContent.replace('Note: ', '');
                document.getElementById('fm-pd-project-note').value = currentNote;
            } else {
                document.getElementById('fm-pd-project-note').value = '';
            }
            document.getElementById('fm-pd-note-modal').style.display = 'block';
            document.querySelector('.fm-pd-modal-overlay').style.display = 'block';
            document.getElementById('fm-pd-project-note').focus();
        };
        
        // View project activity
        window.fmPdViewActivity = function(projectName) {
            document.getElementById('fm-pd-activity-modal').style.display = 'block';
            document.querySelector('.fm-pd-modal-overlay').style.display = 'block';
            
            // Show loading
            document.getElementById('fm-pd-activity-content').innerHTML = '<p>Loading activity...</p>';
            
            // Fetch activity via AJAX
            const formData = new FormData();
            formData.append('action', 'fastmedia_get_project_activity');
            formData.append('project', projectName);
            formData.append('nonce', '<?php echo wp_create_nonce("fm_project_activity"); ?>');
            
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.activities.length > 0) {
                    let html = '<div style="font-size: 13px; line-height: 1.6;">';
                    data.data.activities.forEach(activity => {
                        html += '<div style="padding: 8px 0; border-bottom: 1px solid #ddd;">' + activity + '</div>';
                    });
                    html += '</div>';
                    document.getElementById('fm-pd-activity-content').innerHTML = html;
                } else {
                    document.getElementById('fm-pd-activity-content').innerHTML = '<p style="color: #666;">No activity recorded yet.</p>';
                }
            })
            .catch(error => {
                document.getElementById('fm-pd-activity-content').innerHTML = '<p style="color: #f44336;">Error loading activity.</p>';
            });
        };
        
        // Archive project
        window.fmPdArchiveProject = function(projectName) {
            if (confirm('Are you sure you want to archive the project "' + projectName + '"? You can restore it later from the archive.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="fm_pd_archive_project" value="' + projectName + '">';
                document.body.appendChild(form);
                form.submit();
            }
        };
        
        // Close modal
        window.fmPdCloseModal = function() {
            document.querySelectorAll('.fm-pd-modal').forEach(m => m.style.display = 'none');
            document.querySelector('.fm-pd-modal-overlay').style.display = 'none';
        };
        
        // Handle forms via AJAX to avoid page reload
        document.addEventListener('DOMContentLoaded', function() {
            // Create form
            const createForm = document.getElementById('fm-pd-create-form');
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const projectName = document.getElementById('fm-pd-new-project-name').value;
                    
                    // Submit via form to use existing PHP handler
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = '<input type="hidden" name="fm_pd_new_project" value="' + projectName + '">';
                    document.body.appendChild(form);
                    form.submit();
                });
            }
            
            // Rename form
            const renameForm = document.getElementById('fm-pd-rename-form');
            if (renameForm) {
                renameForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    this.submit();
                });
            }
            
            // Note form
            const noteForm = document.getElementById('fm-pd-note-form');
            if (noteForm) {
                noteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    this.submit();
                });
            }
            
            // Move form
            const moveForm = document.getElementById('fm-pd-move-form');
            if (moveForm) {
                moveForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (document.getElementById('fm-pd-move-to').value) {
                        this.submit();
                    } else {
                        alert('Please select a destination project');
                    }
                });
            }
            
            // Copy form
            const copyForm = document.getElementById('fm-pd-copy-form');
            if (copyForm) {
                copyForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const copyTo = document.getElementById('fm-pd-copy-to').value;
                    if (copyTo === '__new__') {
                        const newProject = prompt('Enter name for new project:');
                        if (newProject && newProject.trim()) {
                            // Add hidden field for new project name
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'fm_pd_new_copy_project';
                            input.value = newProject.trim();
                            this.appendChild(input);
                            this.submit();
                        }
                    } else if (copyTo) {
                        this.submit();
                    } else {
                        alert('Please select a destination project');
                    }
                });
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.fm-pd-menu')) {
                document.querySelectorAll('.fm-pd-dropdown').forEach(d => d.classList.remove('fm-pd-show'));
            }
        });
    })();
    </script>

    <?php
    return ob_get_clean();
});
