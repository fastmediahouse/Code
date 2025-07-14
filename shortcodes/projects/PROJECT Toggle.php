/**
 * ✅ Fastmedia Project Toggle UI - Using Pure WordPress Post Meta (No ACF)
 * Usage: echo fastmedia_project_toggle_ui($attachment_id);
 */

function fastmedia_project_toggle_ui($attachment_id) {
    if (!is_user_logged_in() || !$attachment_id) return '';

    $user_id = get_current_user_id();
    
    // Get user's projects from user meta
    $user_projects = get_user_meta($user_id, 'fastmedia_user_projects', true);
    $user_projects = is_array($user_projects) ? $user_projects : ['Default'];
    
    // Get this attachment's projects from post meta (not ACF)
    $attachment_projects = get_post_meta($attachment_id, 'fastmedia_projects', true);
    $attachment_projects = is_array($attachment_projects) ? $attachment_projects : [];
    
    // Get the last selected project for this user
    $last_selected = get_user_meta($user_id, 'fastmedia_last_project', true) ?: 'Default';

    ob_start();
    ?>
    <div class="fastmedia-project-toggle" data-attachment-id="<?= esc_attr($attachment_id) ?>">
        <div class="project-toggle-row">
            <button type="button" class="toggle-btn <?= !empty($attachment_projects) ? 'active' : '' ?>" 
                    title="<?= !empty($attachment_projects) ? 'Remove from project' : 'Add to project' ?>">
                <span class="toggle-text"><?= !empty($attachment_projects) ? '➖' : '➕' ?></span>
            </button>
            <select class="project-picker">
                <?php foreach ($user_projects as $project): ?>
                    <option value="<?= esc_attr($project) ?>" 
                            <?= selected(in_array($project, $attachment_projects) ? $project : $last_selected, $project) ?>>
                        <?= esc_html($project) ?>
                    </option>
                <?php endforeach; ?>
                <option value="__new__">➕ Create New Project</option>
            </select>
        </div>
    </div>

    <style>
        .fastmedia-project-toggle {
            display: inline-block;
        }
        .project-toggle-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .toggle-btn {
            background: #f5f5f5;
            color: #333;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            border: 1px solid #ddd;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
        }
        .toggle-btn:hover {
            background: #e0e0e0;
        }
        .toggle-btn.active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .project-picker {
            font-size: 13px;
            padding: 6px 10px;
            border-radius: 4px;
            background: #fff;
            border: 1px solid #ccc;
            min-width: 150px;
        }
    </style>

    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const widget = document.querySelector('.fastmedia-project-toggle[data-attachment-id="<?= $attachment_id ?>"]');
            if (!widget || widget.dataset.initialized) return;
            widget.dataset.initialized = 'true';
            
            const attachmentId = widget.dataset.attachmentId;
            const toggleBtn = widget.querySelector('.toggle-btn');
            const toggleText = widget.querySelector('.toggle-text');
            const projectPicker = widget.querySelector('.project-picker');
            
            // Get initial projects from PHP
            let attachmentProjects = <?= json_encode($attachment_projects) ?>;
            
            function updateUI() {
                const currentProject = projectPicker.value;
                const isInProject = attachmentProjects.includes(currentProject);
                
                toggleBtn.classList.toggle('active', isInProject);
                toggleText.textContent = isInProject ? '➖' : '➕';
                toggleBtn.title = isInProject ? 
                    'Remove from ' + currentProject + ' project' : 
                    'Add to ' + currentProject + ' project';
            }
            
            toggleBtn.addEventListener('click', function() {
                const project = projectPicker.value;
                if (!project || project === '__new__') {
                    alert('Please select a project first');
                    return;
                }
                
                const isInProject = attachmentProjects.includes(project);
                const action = isInProject ? 'remove' : 'add';
                
                // Disable button during request
                toggleBtn.disabled = true;
                toggleBtn.style.opacity = '0.5';
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'fastmedia_toggle_project');
                formData.append('attachment_id', attachmentId);
                formData.append('project', project);
                formData.append('toggle_action', action);
                formData.append('nonce', '<?= wp_create_nonce("fastmedia_project_nonce") ?>');
                
                fetch('<?= admin_url("admin-ajax.php") ?>', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (action === 'add') {
                            if (!attachmentProjects.includes(project)) {
                                attachmentProjects.push(project);
                            }
                        } else {
                            attachmentProjects = attachmentProjects.filter(p => p !== project);
                        }
                        updateUI();
                    } else {
                        alert('Error: ' + (data.data || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                })
                .finally(() => {
                    toggleBtn.disabled = false;
                    toggleBtn.style.opacity = '1';
                });
            });
            
            projectPicker.addEventListener('change', function() {
                if (projectPicker.value === '__new__') {
                    const newProject = prompt('Enter new project name:');
                    if (newProject && newProject.trim()) {
                        // Add new project via AJAX
                        const formData = new FormData();
                        formData.append('action', 'fastmedia_create_project');
                        formData.append('project_name', newProject.trim());
                        formData.append('nonce', '<?= wp_create_nonce("fastmedia_project_nonce") ?>');
                        
                        fetch('<?= admin_url("admin-ajax.php") ?>', {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Add option to select
                                const option = document.createElement('option');
                                option.value = newProject;
                                option.textContent = newProject;
                                projectPicker.insertBefore(option, projectPicker.lastElementChild);
                                projectPicker.value = newProject;
                                updateUI();
                            } else {
                                alert('Error creating project: ' + (data.data || 'Unknown error'));
                                projectPicker.value = '<?= esc_js($last_selected) ?>';
                            }
                        });
                    } else {
                        projectPicker.value = '<?= esc_js($last_selected) ?>';
                    }
                } else {
                    // Save last selected project
                    const formData = new FormData();
                    formData.append('action', 'fastmedia_save_last_project');
                    formData.append('project', projectPicker.value);
                    formData.append('nonce', '<?= wp_create_nonce("fastmedia_project_nonce") ?>');
                    
                    fetch('<?= admin_url("admin-ajax.php") ?>', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    
                    updateUI();
                }
            });
            
            // Initial UI update
            updateUI();
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

// AJAX handler for toggling project
add_action('wp_ajax_fastmedia_toggle_project', function() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fastmedia_project_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $attachment_id = intval($_POST['attachment_id']);
    $project = sanitize_text_field($_POST['project']);
    $action = sanitize_text_field($_POST['toggle_action']);
    $user_id = get_current_user_id();
    
    // Verify user owns this attachment
    if (get_post_field('post_author', $attachment_id) != $user_id) {
        wp_send_json_error('Permission denied');
    }
    
    // Get current projects using post meta (not ACF)
    $projects = get_post_meta($attachment_id, 'fastmedia_projects', true);
    $projects = is_array($projects) ? $projects : [];
    
    if ($action === 'add') {
        if (!in_array($project, $projects)) {
            $projects[] = $project;
            update_post_meta($attachment_id, 'fastmedia_projects', $projects);
            
            // Log activity
            $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: [];
            $user_info = get_userdata($user_id);
            $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' added to project: ' . $project;
            update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
        }
    } else {
        $projects = array_values(array_filter($projects, function($p) use ($project) {
            return $p !== $project;
        }));
        update_post_meta($attachment_id, 'fastmedia_projects', $projects);
        
        // Log activity
        $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log', true) ?: [];
        $user_info = get_userdata($user_id);
        $activity_log[] = date('Y-m-d H:i') . ' - ' . $user_info->display_name . ' removed from project: ' . $project;
        update_post_meta($attachment_id, 'fastmedia_activity_log', array_slice($activity_log, -50));
    }
    
    wp_send_json_success(['projects' => $projects]);
});

// AJAX handler for creating new project
add_action('wp_ajax_fastmedia_create_project', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fastmedia_project_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $user_id = get_current_user_id();
    $project_name = sanitize_text_field($_POST['project_name']);
    
    if (empty($project_name)) {
        wp_send_json_error('Project name cannot be empty');
    }
    
    // Get user's projects
    $user_projects = get_user_meta($user_id, 'fastmedia_user_projects', true) ?: ['Default'];
    
    if (in_array($project_name, $user_projects)) {
        wp_send_json_error('Project already exists');
    }
    
    // Add new project
    $user_projects[] = $project_name;
    update_user_meta($user_id, 'fastmedia_user_projects', $user_projects);
    
    wp_send_json_success(['project' => $project_name]);
});

// AJAX handler for saving last selected project
add_action('wp_ajax_fastmedia_save_last_project', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fastmedia_project_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $project = sanitize_text_field($_POST['project']);
    update_user_meta(get_current_user_id(), 'fastmedia_last_project', $project);
    
    wp_send_json_success();
});
