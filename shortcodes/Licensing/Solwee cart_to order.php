// WPCode Snippet Name: solwee_cart_to_order_meta

add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
    if (isset($values['solwee_license'])) {
        $item->add_meta_data('solwee_license', $values['solwee_license'], true);
    }
}, 10, 4);
