// Add this to your theme's functions.php file or plugin

// Handle watermarked comp download
add_action('wp_ajax_fastmedia_comp_download', 'fastmedia_comp_download_handler');
add_action('wp_ajax_nopriv_fastmedia_comp_download', 'fastmedia_comp_download_handler');

// Handle resize downloads
add_action('wp_ajax_fastmedia_resize_download', 'fastmedia_resize_download_handler');
add_action('wp_ajax_nopriv_fastmedia_resize_download', 'fastmedia_resize_download_handler');

// Handle new project creation
add_action('wp_ajax_create_new_project', 'fastmedia_create_new_project_handler');
add_action('wp_ajax_nopriv_create_new_project', 'fastmedia_create_new_project_handler');

function fastmedia_comp_download_handler() {
    // Check if user is logged in first
    if (!is_user_logged_in()) {
        wp_die('Please log in to download files.', 'Login Required', array('response' => 401));
    }
    
    $attachment_id = intval($_GET['id']);
    $user_id = get_current_user_id();
    
    // Security check - allow if user owns the image
    if (!$attachment_id || get_post_field('post_author', $attachment_id) != $user_id) {
        wp_die('You do not have permission to download this file.', 'Access Denied', array('response' => 403));
    }
    
    $file_path = get_attached_file($attachment_id);
    if (!file_exists($file_path)) {
        wp_die('File not found on server.', 'File Not Found', array('response' => 404));
    }
    
    // Create watermarked image
    $watermarked_image = fastmedia_create_watermarked_image($file_path, $attachment_id);
    
    if ($watermarked_image && file_exists($watermarked_image)) {
        // Get original filename without extension
        $original_name = pathinfo(basename($file_path), PATHINFO_FILENAME);
        $filename = 'COMP_' . $original_name . '.jpg';
        
        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set proper headers for download
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($watermarked_image));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        header('Pragma: public');
        
        // Output the watermarked image
        readfile($watermarked_image);
        
        // Clean up temporary file
        unlink($watermarked_image);
    } else {
        wp_die('Unable to create watermarked version. Please try again.', 'Processing Error', array('response' => 500));
    }
    
    exit;
}

function fastmedia_resize_download_handler() {
    $attachment_id = intval($_GET['id']);
    $size = sanitize_text_field($_GET['size']);
    $user_id = get_current_user_id();
    
    // Security check
    if (!is_user_logged_in() || !$attachment_id || get_post_field('post_author', $attachment_id) != $user_id) {
        wp_die('Access denied', 'Unauthorized', array('response' => 403));
    }
    
    $file_path = get_attached_file($attachment_id);
    if (!file_exists($file_path)) {
        wp_die('File not found', 'Not Found', array('response' => 404));
    }
    
    // Set size limits
    $size_limits = [
        'large' => 1920,
        'medium' => 1200,
        'small' => 800
    ];
    
    if (!isset($size_limits[$size])) {
        wp_die('Invalid size parameter', 'Bad Request', array('response' => 400));
    }
    
    // Create resized image
    $resized_image = fastmedia_create_resized_image($file_path, $attachment_id, $size_limits[$size]);
    
    if ($resized_image && file_exists($resized_image)) {
        // Set headers for download
        $filename = strtoupper($size) . '_' . basename($file_path);
        
        header('Content-Type: ' . mime_content_type($resized_image));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($resized_image));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Output the resized image
        readfile($resized_image);
        
        // Clean up temporary file
        unlink($resized_image);
    } else {
        wp_die('Error creating resized image', 'Server Error', array('response' => 500));
    }
    
    exit;
}

function fastmedia_create_resized_image($source_path, $attachment_id, $max_size) {
    // Get image info
    $image_info = getimagesize($source_path);
    if (!$image_info) return false;
    
    $mime_type = $image_info['mime'];
    $width = $image_info[0];
    $height = $image_info[1];
    
    // Create image resource from source
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source_image) return false;
    
    // Calculate new dimensions
    if ($width <= $max_size && $height <= $max_size) {
        // Image is already smaller than target, just copy it
        $new_width = $width;
        $new_height = $height;
    } else {
        // Resize to fit within max_size
        if ($width > $height) {
            $new_width = $max_size;
            $new_height = ($height * $max_size) / $width;
        } else {
            $new_height = $max_size;
            $new_width = ($width * $max_size) / $height;
        }
    }
    
    // Create new image
    $resized_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG
    if ($mime_type == 'image/png') {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
        imagefill($resized_image, 0, 0, $transparent);
    }
    
    // Resize image
    imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Create temporary file
    $temp_file = wp_tempnam('fastmedia_resize_');
    
    // Save resized image
    $success = false;
    if ($mime_type == 'image/png') {
        $success = imagepng($resized_image, $temp_file, 8); // PNG compression
    } else {
        $success = imagejpeg($resized_image, $temp_file, 92); // High quality JPEG
    }
    
    // Clean up memory
    imagedestroy($source_image);
    imagedestroy($resized_image);
    
    return $success ? $temp_file : false;
}

function fastmedia_create_new_project_handler() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Access denied');
    }
    
    $project_name = sanitize_text_field($_POST['project_name']);
    $attachment_id = intval($_POST['attachment_id']);
    
    if (empty($project_name)) {
        wp_send_json_error('Project name is required');
    }
    
    // Create new project post
    $project_id = wp_insert_post([
        'post_title' => $project_name,
        'post_type' => 'projects',
        'post_status' => 'publish',
        'post_author' => get_current_user_id()
    ]);
    
    if (is_wp_error($project_id)) {
        wp_send_json_error('Failed to create project');
    }
    
    // Assign attachment to new project
    update_post_meta($attachment_id, 'fastmedia_project', $project_id);
    
    wp_send_json_success([
        'project_id' => $project_id,
        'project_name' => $project_name
    ]);
}

function fastmedia_create_watermarked_image($source_path, $attachment_id) {
    // Get image info
    $image_info = getimagesize($source_path);
    if (!$image_info) return false;
    
    $mime_type = $image_info['mime'];
    $width = $image_info[0];
    $height = $image_info[1];
    
    // Create image resource from source
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source_image) return false;
    
    // Resize to comp size (max 1200px on longest side)
    $max_comp_size = 1200;
    if ($width > $height) {
        $new_width = min($width, $max_comp_size);
        $new_height = ($height * $new_width) / $width;
    } else {
        $new_height = min($height, $max_comp_size);
        $new_width = ($width * $new_height) / $height;
    }
    
    // Create new image with comp dimensions
    $comp_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG
    if ($mime_type == 'image/png') {
        imagealphablending($comp_image, false);
        imagesavealpha($comp_image, true);
        $transparent = imagecolorallocatealpha($comp_image, 255, 255, 255, 127);
        imagefill($comp_image, 0, 0, $transparent);
    }
    
    // Resize source image to comp size
    imagecopyresampled($comp_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Add Fast Media logo watermark
    $logo_urls = [
        'https://fastmediahouse.com/wp-content/uploads/2025/02/Black-logo-no-background-scaled.png', // Black transparent
        'https://fastmediahouse.com/wp-content/uploads/2025/07/Color-logo-with-background-scaled.webp'  // Color with background
    ];
    
    $logo_added = false;
    
    // Try to use the black transparent logo first
    foreach ($logo_urls as $logo_url) {
        $logo_data = wp_remote_get($logo_url);
        if (!is_wp_error($logo_data) && wp_remote_retrieve_response_code($logo_data) == 200) {
            $logo_content = wp_remote_retrieve_body($logo_data);
            
            // Create temporary file for logo
            $temp_logo = wp_tempnam('fastmedia_logo_');
            file_put_contents($temp_logo, $logo_content);
            
            // Load logo based on file type
            if (strpos($logo_url, '.webp') !== false) {
                $logo = imagecreatefromwebp($temp_logo);
            } else {
                $logo = imagecreatefrompng($temp_logo);
            }
            
            if ($logo) {
                $logo_width = imagesx($logo);
                $logo_height = imagesy($logo);
                
                // Calculate logo size (20% of image width, max 300px)
                $watermark_width = min($new_width * 0.2, 300);
                $watermark_height = ($logo_height * $watermark_width) / $logo_width;
                
                // Create resized logo with transparency
                $resized_logo = imagecreatetruecolor($watermark_width, $watermark_height);
                imagealphablending($resized_logo, false);
                imagesavealpha($resized_logo, true);
                $transparent = imagecolorallocatealpha($resized_logo, 255, 255, 255, 127);
                imagefill($resized_logo, 0, 0, $transparent);
                
                // Resize logo
                imagecopyresampled($resized_logo, $logo, 0, 0, 0, 0, $watermark_width, $watermark_height, $logo_width, $logo_height);
                
                // Position logo (center)
                $logo_x = ($new_width - $watermark_width) / 2;
                $logo_y = ($new_height - $watermark_height) / 2;
                
                // Add logo with transparency (50% opacity)
                imagecopymerge($comp_image, $resized_logo, $logo_x, $logo_y, 0, 0, $watermark_width, $watermark_height, 50);
                
                // Clean up
                imagedestroy($logo);
                imagedestroy($resized_logo);
                unlink($temp_logo);
                
                $logo_added = true;
                break; // Success, exit loop
            }
            
            unlink($temp_logo);
        }
    }
    
    // Fallback to text watermark if logo loading failed
    if (!$logo_added) {
        $watermark_text = "FAST MEDIA";
        $font_size = max(24, $new_width / 30);
        $watermark_color = imagecolorallocatealpha($comp_image, 255, 255, 255, 80);
        $shadow_color = imagecolorallocatealpha($comp_image, 0, 0, 0, 50);
        
        $text_width = strlen($watermark_text) * ($font_size / 2);
        $x = ($new_width - $text_width) / 2;
        $y = ($new_height) / 2;
        
        // Add shadow and text
        imagestring($comp_image, 5, $x + 2, $y + 2, $watermark_text, $shadow_color);
        imagestring($comp_image, 5, $x, $y, $watermark_text, $watermark_color);
    }
    
    // Create temporary file
    $temp_file = wp_tempnam('fastmedia_comp_');
    
    // Save watermarked image
    $success = imagejpeg($comp_image, $temp_file, 85); // 85% quality for comp
    
    // Clean up memory
    imagedestroy($source_image);
    imagedestroy($comp_image);
    
    return $success ? $temp_file : false;
}
