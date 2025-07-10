// Add this to your theme's functions.php file or plugin

// Handle watermarked comp download
add_action('wp_ajax_fastmedia_comp_download', 'fastmedia_comp_download_handler');
add_action('wp_ajax_nopriv_fastmedia_comp_download', 'fastmedia_comp_download_handler');

function fastmedia_comp_download_handler() {
    $attachment_id = intval($_GET['id']);
    $user_id = get_current_user_id();
    
    // Security check
    if (!$attachment_id || get_post_field('post_author', $attachment_id) != $user_id) {
        wp_die('Access denied');
    }
    
    $file_path = get_attached_file($attachment_id);
    if (!file_exists($file_path)) {
        wp_die('File not found');
    }
    
    // Create watermarked image
    $watermarked_image = fastmedia_create_watermarked_image($file_path, $attachment_id);
    
    if ($watermarked_image) {
        // Set headers for download
        $filename = 'COMP_' . basename($file_path);
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($watermarked_image));
        
        // Output the watermarked image
        readfile($watermarked_image);
        
        // Clean up temporary file
        unlink($watermarked_image);
    } else {
        wp_die('Error creating watermarked image');
    }
    
    exit;
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
