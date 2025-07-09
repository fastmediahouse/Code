// ✅ 1. Store Solwee metadata in the cart
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    $fields = [
        'solwee_price', 'solwee_imageID', 'solwee_usageID', 'solwee_magazineID',
        'category', 'usage', 'duration', 'modifiers', 'region'
    ];
    foreach ($fields as $key) {
        if (isset($_GET[$key])) {
            $cart_item_data['solwee_license'][$key] = sanitize_text_field($_GET[$key]);
        }
    }
    return $cart_item_data;
}, 10, 2);

// ✅ 2. Set custom price from Solwee calculator
add_filter('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $item) {
        if (!empty($item['solwee_license']['solwee_price'])) {
            $item['data']->set_price(floatval($item['solwee_license']['solwee_price']));
        }
    }
}, 10, 1);

// ✅ 3. Show all metadata under product in cart
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (!isset($cart_item['solwee_license'])) return $item_data;

    $data = $cart_item['solwee_license'];
    $add = function ($label, $value) use (&$item_data) {
        if (!empty($value)) {
            $item_data[] = ['name' => $label, 'value' => nl2br(esc_html($value))];
        }
    };

    $add('Image ID', $data['solwee_imageID'] ?? '');
    $add('Usage ID', $data['solwee_usageID'] ?? '');
    $add('Magazine ID', $data['solwee_magazineID'] ?? '');

    $add('Category', $data['category'] ?? '');
    $add('Usage', $data['usage'] ?? '');
    $add('Duration', $data['duration'] ?? '');
    $add('Region', $data['region'] ?? '');
    $add('Modifiers', $data['modifiers'] ?? '');

    return $item_data;
}, 10, 2);

// ✅ 4. Force WooCommerce to display proxy image in cart
add_filter('woocommerce_cart_item_thumbnail', function ($thumbnail, $cart_item) {
    if (!empty($cart_item['solwee_license']['solwee_imageID'])) {
        $productID = sanitize_text_field($cart_item['solwee_license']['solwee_imageID']);
        $proxy = esc_url("/?solwee_image_proxy={$productID}");
        return '<img src="' . $proxy . '" alt="Preview" style="width: 80px; height: auto; border-radius: 6px;">';
    }
    return $thumbnail;
}, 10, 2);
