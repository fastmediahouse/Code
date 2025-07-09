add_shortcode('solwee_similar_gallery', function () {
    if (!isset($_GET['productID'])) return '';

    $productID = sanitize_text_field($_GET['productID']);
    $webID = '57';
    $limit = 8;

    $token = get_transient('solwee_api_token');
    if (!$token) {
        $login_response = wp_remote_post('https://api.solwee.com/api/v2/login', [
            'headers' => ['Content-Type' => 'application/json', 'X-WebID' => $webID],
            'body' => json_encode(['login' => 'marco@fm.house', 'password' => 'your-password-here']),
            'timeout' => 10,
        ]);
        if (!is_wp_error($login_response)) {
            $token = trim(wp_remote_retrieve_body($login_response), "\"");
            set_transient('solwee_api_token', $token, 15 * MINUTE_IN_SECONDS);
        }
    }

    if (!$token) return '';

    $response = wp_remote_get("https://api.solwee.com/api/v2/product-detail/{$productID}", [
        'headers' => ['Authorization' => "Bearer {$token}", 'X-WebID' => $webID],
        'timeout' => 10,
    ]);
    if (is_wp_error($response)) return '';
    $product = json_decode(wp_remote_retrieve_body($response));
    if (!$product) return '';

    $title = $product->title ?? '';
    if (empty($title)) return '';

    // Use only the first 6 words of the title
    $shortTitle = implode(' ', array_slice(explode(' ', $title), 0, 6));

    $search_response = wp_remote_post("https://api.solwee.com/api/v2/search/images/editorial", [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}",
            'X-WebID' => $webID
        ],
        'timeout' => 2,
        'body' => json_encode([
            'fulltext' => $shortTitle,
            'limit' => $limit + 2,
            'offset' => 0
        ])
    ]);
    if (is_wp_error($search_response)) return '';
    $data = json_decode(wp_remote_retrieve_body($search_response), true);
    $results = $data['results'] ?? [];

    $filtered = array_filter($results, function ($img) use ($productID) {
        return $img['productID'] != $productID;
    });
    $filtered = array_slice($filtered, 0, $limit);
    if (empty($filtered)) return '';

    $user_id = get_current_user_id();
    $folders = get_user_meta($user_id, 'solwee_favorites_folders', true);
    $folders = is_array($folders) ? $folders : ['Default' => []];
    $folder_keys = array_keys($folders);
    $saved_ids = [];
    foreach ($folders as $f => $ids) foreach ($ids as $id) $saved_ids[$f][$id] = true;

    ob_start();
    ?>
    <div style="margin-top:40px;">
        <h3>Similar images</h3>
        <div class="solwee-masonry" style="column-count:4; column-gap:20px;">
            <?php foreach ($filtered as $img): 
                $id = esc_attr($img['productID']);
                $thumb = esc_url($img['thumbUrl'] ?? '');
                $fallback = "/no-preview.jpg";
                $is_saved = isset($saved_ids['Default'][$id]);
                $active = $is_saved ? 'active' : '';
            ?>
            <div class="solwee-tile" style="break-inside:avoid;margin-bottom:20px;position:relative;box-shadow:0 2px 6px rgba(0,0,0,0.1);border-radius:6px;overflow:hidden;">
                <a href="/image-detail/?productID=<?= $id ?>" target="_blank">
                    <img src="<?= $thumb ?>" onerror="this.onerror=null;this.src='<?= $fallback ?>';" alt="Similar image" style="width:100%;display:block;" loading="lazy" />
                </a>
                <div style="position:absolute;top:8px;right:8px;display:flex;flex-direction:column;gap:6px;z-index:10;">
                    <span class="solwee-icon <?= $active ?>" data-productid="<?= $id ?>" style="cursor:pointer;padding:6px 8px;background:rgba(0,0,0,0.6);color:#fff;border-radius:4px;">❤️</span>
                    <select class="solwee-folder-picker" data-productid="<?= $id ?>" style="font-size:12px;margin-top:2px;padding:4px;">
                        <?php foreach ($folder_keys as $f): ?>
                            <option value="<?= esc_attr($f) ?>"><?= esc_html($f) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ✅ Fixed routing -->
        <div style="margin-top:20px;text-align:center;">
            <a href="/search-results/?q=<?= urlencode($shortTitle) ?>&category=editorial" 
               style="padding:10px 18px;background:#0056b3;color:#fff;border-radius:6px;text-decoration:none;font-weight:500;">
               🔍 See More Similar Images
            </a>
        </div>
    </div>

    <script>
    document.querySelectorAll('.solwee-icon').forEach(btn => {
        btn.addEventListener('click', function () {
            const productID = this.dataset.productid;
            const select = this.parentElement.querySelector('.solwee-folder-picker');
            const folder = select?.value || 'Default';
            const isActive = this.classList.contains('active');
            const action = isActive ? 'remove' : 'add';
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: action === 'add' ? 'solwee_update_lightbox_foldered' : 'solwee_remove_from_folder',
                    productID,
                    folder
                })
            }).then(res => res.json()).then(res => {
                if (res.success) this.classList.toggle('active');
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
});
