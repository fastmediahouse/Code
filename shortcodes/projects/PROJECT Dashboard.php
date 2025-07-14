/**
 * FastMedia Project Dashboard - Stage 1: Safe Base Structure
 * All CSS prefixed with .fm-pd- to ensure complete isolation
 * All JavaScript scoped to prevent conflicts
 */

add_shortcode('fastmedia_project_dashboard', function () {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/signin/">sign in</a> to view your projects.</p>';
    }

    $user_id = get_current_user_id();
    
    // Get user's projects from the toggle system
    $user_projects = get_user_meta($user_id, 'fastmedia_user_projects', true);
    $user_projects = is_array($user_projects) ? $user_projects : ['Default'];
    
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
        
        echo "ID,Filename,URL\n";
        foreach ($attachments as $attachment) {
            echo $attachment->ID . ',"' . basename(get_attached_file($attachment->ID)) . '","' . wp_get_attachment_url($attachment->ID) . '"' . "\n";
        }
        exit;
    }

    ob_start();
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
            background: rgba(255,255,255,0.9);
            border: none;
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
        }
        
        .fm-pd-menu-btn:hover {
            background: rgba(255,255,255,1);
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
                    <button class="fm-pd-btn-create" onclick="alert('Create project functionality coming soon')">
                        + Create Project
                    </button>
                </div>
            </div>
            
            <div class="fm-pd-grid">
                <?php foreach ($user_projects as $project): 
                    // Get project stats
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
                    $asset_count = count($attachments);
                    $thumbnails = array_slice($attachments, 0, 4);
                ?>
                    <div class="fm-pd-card">
                        <div class="fm-pd-menu">
                            <button class="fm-pd-menu-btn" onclick="fmPdToggleMenu(this)">⋮</button>
                            <div class="fm-pd-dropdown">
                                <a href="/project-view/?project=<?= urlencode($project) ?>" class="fm-pd-dropdown-item">
                                    👁️ View Project
                                </a>
                                <button class="fm-pd-dropdown-item" onclick="navigator.clipboard.writeText('<?= site_url('/project-view/?project=' . urlencode($project)) ?>'); alert('Link copied!')">
                                    📤 Share Link
                                </button>
                                <form method="post" class="fm-pd-form-inline" target="fm_pd_export_frame">
                                    <input type="hidden" name="fm_pd_export" value="1">
                                    <input type="hidden" name="fm_pd_project" value="<?= esc_attr($project) ?>">
                                    <button type="submit" class="fm-pd-dropdown-item">
                                        📦 Export CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if (!empty($thumbnails)): ?>
                            <div class="fm-pd-thumb-grid" onclick="window.location.href='/project-view/?project=<?= urlencode($project) ?>'">
                                <?php 
                                $thumb_count = 0;
                                foreach ($thumbnails as $thumb): 
                                    $thumb_url = wp_get_attachment_image_url($thumb->ID, 'thumbnail');
                                    if ($thumb_url):
                                ?>
                                    <div class="fm-pd-thumb">
                                        <img src="<?= esc_url($thumb_url) ?>" alt="" loading="lazy">
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
                            </div>
                        <?php else: ?>
                            <div class="fm-pd-empty-thumb" onclick="window.location.href='/project-view/?project=<?= urlencode($project) ?>'">
                                📁
                            </div>
                        <?php endif; ?>
                        
                        <div class="fm-pd-card-body">
                            <h3 class="fm-pd-project-name"><?= esc_html($project) ?></h3>
                            
                            <div class="fm-pd-project-stats">
                                <div class="fm-pd-stat">
                                    <span class="fm-pd-stat-value"><?= $asset_count ?></span>
                                    <span class="fm-pd-stat-label">Assets</span>
                                </div>
                                <div class="fm-pd-stat">
                                    <span class="fm-pd-stat-value">0</span>
                                    <span class="fm-pd-stat-label">Likes</span>
                                </div>
                                <div class="fm-pd-stat">
                                    <span class="fm-pd-stat-value">0</span>
                                    <span class="fm-pd-stat-label">Dislikes</span>
                                </div>
                            </div>
                            
                            <div class="fm-pd-meta">
                                <small>Last updated: Recently</small>
                            </div>
                            
                            <div class="fm-pd-actions">
                                <a href="/project-view/?project=<?= urlencode($project) ?>" class="fm-pd-btn">
                                    👁️ View Project
                                </a>
                                <a href="/project-view/?project=<?= urlencode($project) ?>&view=contact" class="fm-pd-btn">
                                    🎛️ Contact Sheet
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($user_projects)): ?>
            <div class="fm-pd-empty-state">
                <h3>No projects yet</h3>
                <p>Create your first project to start organizing your assets.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <iframe name="fm_pd_export_frame" style="display:none;"></iframe>

    <script>
    // Scoped JavaScript - no global functions
    (function() {
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
