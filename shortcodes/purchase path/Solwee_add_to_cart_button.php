add_shortcode('solwee_add_to_cart_button', function () {
    if (!isset($_GET['productID'])) return '';

    $productID = sanitize_text_field($_GET['productID']);
    $woo_product_id = 7447;
    $user_id = get_current_user_id();
    $magazineID = get_user_meta($user_id, 'magazine_id', true); // ✅ ACF field name

    if (!$magazineID) {
        return '<p><strong>Error:</strong> No Solwee magazine ID assigned to your account. Please contact support.</p>';
    }

    $cart_url = wc_get_cart_url();

    ob_start();
    ?>
    <div id="solwee-license-button-container" style="margin-top: 20px;">
        <a id="solwee-cart-button" href="#" style="display:none; padding: 16px 30px; font-size: 18px; font-weight: 600; background-color: #000; color: #fff; text-decoration: none; border-radius: 8px;">
            Add to Cart
        </a>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const priceBox = document.getElementById("final-price");
        const cartButton = document.getElementById("solwee-cart-button");
        const productID = "<?php echo esc_js($productID); ?>";
        const cartBase = "<?php echo esc_url($cart_url); ?>";

        function getSelectedText(selector) {
            const el = document.querySelector(selector);
            return el && el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '';
        }

        function getSelectedValue(selector) {
            const el = document.querySelector(selector);
            return el ? el.value : '';
        }

        function getCheckedValues(className) {
            return Array.from(document.querySelectorAll('.' + className + ':checked')).map(cb => cb.parentElement.textContent.trim()).join(' | ');
        }

        const observer = new MutationObserver(() => {
            const match = priceBox.textContent.match(/€([\d.,]+)/);
            if (!match) return;

            const price = match[1].replace(',', '.');
            const category = getSelectedText("#cat-select");
            const usageText = getSelectedText("#usage-select");
            const usageID = getSelectedValue("#usage-select"); // numeric ID
            const duration = getSelectedText("#print-select");
            const modifiers = getCheckedValues("mod");
            const region = getCheckedValues("region");

            const url = new URL(cartBase);
            url.searchParams.set('add-to-cart', '<?php echo $woo_product_id; ?>');
            url.searchParams.set('solwee_price', price);
            url.searchParams.set('solwee_imageID', productID);
            url.searchParams.set('solwee_usageID', usageID);
            url.searchParams.set('solwee_magazineID', '<?php echo esc_js($magazineID); ?>');
            url.searchParams.set('category', category);
            url.searchParams.set('usage', usageText); // readable
            url.searchParams.set('duration', duration);
            url.searchParams.set('modifiers', modifiers);
            url.searchParams.set('region', region);

            cartButton.href = url.href;
            cartButton.style.display = 'inline-block';
        });

        observer.observe(priceBox, { childList: true, subtree: true });
    });
    </script>
    <?php
    return ob_get_clean();
});
