// WPCode Snippet Name: LICENSING Checkout Logic

// ✅ 1. Display Solwee metadata + thumbnail on Order Received page & My Account → Orders
add_action('woocommerce_order_item_meta_end', function($item_id, $item, $order) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    $license = $item->get_meta('solwee_license');
    if (empty($license) || !is_array($license)) return;

    static $already_printed = [];
    $item_key = $item->get_id();
    if (in_array($item_key, $already_printed)) return;
    $already_printed[] = $item_key;

    echo '<div class="solwee-license-meta" style="margin-top:12px;">';

    if (!empty($license['solwee_imageID'])) {
        $productID = esc_attr($license['solwee_imageID']);
        $proxy = esc_url('https://fastmediahouse.com/?solwee_image_proxy=' . $productID);
        echo '<div><img src="' . $proxy . '" alt="Preview" style="width: 80px; height: auto; border-radius: 6px; margin-bottom: 8px;"></div>';
    }

    $output = function($label, $value) {
        if (!empty($value)) {
            echo '<div><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</div>';
        }
    };

    $output('Image ID', $license['solwee_imageID'] ?? '');
    $output('Usage ID', $license['solwee_usageID'] ?? '');
    $output('Magazine ID', $license['solwee_magazineID'] ?? '');
    $output('Category', $license['category'] ?? '');
    $output('Usage', $license['usage'] ?? '');
    $output('Duration', $license['duration'] ?? '');
    $output('Region', $license['region'] ?? '');
    $output('Modifiers', $license['modifiers'] ?? '');

    echo '</div>';
}, 10, 3);


// ✅ 2. Show Solwee proxy thumbnail in Cart + Checkout (cart column)
add_filter('woocommerce_cart_item_thumbnail', function ($thumbnail, $cart_item) {
    if (!empty($cart_item['solwee_license']['solwee_imageID'])) {
        $productID = sanitize_text_field($cart_item['solwee_license']['solwee_imageID']);
        $proxy = esc_url('https://fastmediahouse.com/?solwee_image_proxy=' . $productID);
        return '<img src="' . $proxy . '" alt="Preview" style="width: 80px; height: auto; border-radius: 6px;">';
    }
    return $thumbnail;
}, 10, 2);


// ✅ 3. Inject thumbnail only on checkout summary (safe override)
add_filter('woocommerce_cart_item_name', function($name, $cart_item, $cart_item_key) {
    if (!is_checkout()) return $name;

    if (!empty($cart_item['solwee_license']['solwee_imageID'])) {
        $productID = sanitize_text_field($cart_item['solwee_license']['solwee_imageID']);
        $proxy = esc_url('https://fastmediahouse.com/?solwee_image_proxy=' . $productID);
        $image_html = '<div><img src="' . $proxy . '" alt="Preview" style="width: 60px; height: auto; border-radius: 6px; margin: 6px 0;"></div>';
        return $image_html . $name;
    }

    return $name;
}, 10, 3);
