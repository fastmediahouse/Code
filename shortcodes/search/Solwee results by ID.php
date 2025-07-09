function solwee_results_by_id_shortcode() {
    if (empty($_GET['ids'])) return '<p>⚠️ No image IDs provided.</p>';

    $ids = explode(',', sanitize_text_field($_GET['ids']));
    $ids = array_filter($ids, fn($id) => is_numeric($id));
    if (empty($ids)) return '<p>⚠️ No valid product IDs found.</p>';

    $output = '<div class="solwee-grid">';
    foreach ($ids as $id) {
        $thumb = esc_url("/?solwee_image_proxy=" . $id);
        $link = esc_url("/image-detail/?productID=" . $id);

        $output .= '<div class="solwee-tile">';
        $output .= '<a href="' . $link . '" target="_blank">';
        $output .= '<img 
            src="' . $thumb . '" 
            alt="Image ' . esc_attr($id) . '" 
            loading="lazy"
            style="width:100%; height:auto; border-radius:8px; filter: blur(10px); transition: filter 0.4s ease;"
            onload="this.style.filter=\'none\'"
            onerror="this.onerror=null;this.src=\'https://via.placeholder.com/400x300?text=Image+Unavailable\';">';
        $output .= '</a>';
        $output .= '</div>';
    }
    $output .= '</div>';

    $output .= '<style>
        .solwee-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .solwee-tile {
            position: relative;
        }
    </style>';

    return $output;
}
add_shortcode('solwee_results_by_id', 'solwee_results_by_id_shortcode');
