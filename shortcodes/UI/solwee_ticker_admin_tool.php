// ✅ Register admin settings page
add_action('admin_menu', function () {
    add_options_page(
        'Ticker Titles',
        'Ticker Titles',
        'manage_options',
        'solwee-ticker-titles',
        'render_solwee_ticker_admin_page'
    );
});

// ✅ Render the admin page UI
function render_solwee_ticker_admin_page() {
    if (isset($_POST['solwee_ticker_titles'])) {
        check_admin_referer('solwee_save_ticker_titles');

        $raw_lines = explode("\n", $_POST['solwee_ticker_titles']);
        $entries = [];

        // Preserve existing images
        $existing = get_option('solwee_ticker_titles', []);
        $map = [];
        foreach ($existing as $e) {
            if (!empty($e['title'])) {
                $map[sanitize_title($e['title'])] = $e;
            }
        }

        foreach ($raw_lines as $line) {
            $line = sanitize_text_field(trim($line));
            if (!$line) continue;

            $parts = array_map('trim', explode('|', $line, 2));
            $title = $parts[0] ?? '';
            $category = $parts[1] ?? '';

            if ($title) {
                $key = sanitize_title($title);
                $entry = ['title' => $title, 'category' => $category];
                if (!empty($map[$key]['image'])) {
                    $entry['image'] = $map[$key]['image'];
                }
                $entries[] = $entry;
            }
        }

        update_option('solwee_ticker_titles', $entries);
        echo '<div class="updated"><p>✅ Titles updated (images preserved).</p></div>';
    }

    if (isset($_POST['solwee_update_image_url'])) {
        check_admin_referer('solwee_update_image_url');
        $title = sanitize_text_field($_POST['image_title'] ?? '');
        $url   = esc_url_raw($_POST['image_url'] ?? '');

        $entries = get_option('solwee_ticker_titles', []);
        foreach ($entries as &$entry) {
            if ($entry['title'] === $title) {
                $entry['image'] = $url;
                break;
            }
        }
        update_option('solwee_ticker_titles', $entries);
        echo '<div class="updated"><p>✏️ Image URL updated for "' . esc_html($title) . '"</p></div>';
    }

    if (isset($_GET['clear_image'])) {
        $title = sanitize_text_field($_GET['clear_image']);
        $entries = get_option('solwee_ticker_titles', []);
        foreach ($entries as &$entry) {
            if ($entry['title'] === $title) {
                unset($entry['image']);
                break;
            }
        }
        update_option('solwee_ticker_titles', $entries);
        echo '<div class="updated"><p>🗑️ Image cleared for "' . esc_html($title) . '"</p></div>';
    }

    $entries = get_option('solwee_ticker_titles', []);
    ?>
    <div class="wrap">
        <h1>📰 Solwee Ticker Titles</h1>
        <form method="post">
            <?php wp_nonce_field('solwee_save_ticker_titles'); ?>
            <textarea name="solwee_ticker_titles" rows="10" style="width:100%;font-family:monospace;"><?php
                echo esc_textarea(implode("\n", array_map(function ($entry) {
                    return $entry['title'] . (!empty($entry['category']) ? ' | ' . $entry['category'] : '');
                }, $entries)));
            ?></textarea>
            <p><button type="submit" class="button button-primary">💾 Save Titles</button></p>
        </form>

        <hr>

        <h2>📸 Pull Lead Images</h2>
        <ul style="margin-left:0;">
            <?php foreach ($entries as $entry): ?>
                <li style="margin-bottom:20px;">
                    <strong><?php echo esc_html($entry['title']); ?></strong>
                    (<?php echo esc_html($entry['category'] ?? ''); ?>)

                    <?php if (!empty($entry['image'])): ?>
                        ✅ <img src="<?php echo esc_url($entry['image']); ?>" style="height:32px;vertical-align:middle;margin-left:10px;">

                        <a class="button" href="<?php echo wp_nonce_url(
                            admin_url("admin-post.php?action=solwee_fetch_image&title=" . urlencode($entry['title'])),
                            'solwee_fetch_image'
                        ); ?>">🔁 Retry Image</a>

                        <a class="button" href="<?php echo admin_url("options-general.php?page=solwee-ticker-titles&clear_image=" . urlencode($entry['title'])); ?>">🗑️ Clear</a>

                        <div style="margin-top:6px;">
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('solwee_update_image_url'); ?>
                                <input type="hidden" name="image_title" value="<?php echo esc_attr($entry['title']); ?>">
                                <input type="text" name="image_url" value="<?php echo esc_attr($entry['image']); ?>" style="width:300px;">
                                <button type="submit" name="solwee_update_image_url" class="button">✏️ Save</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <a class="button button-secondary" href="<?php echo wp_nonce_url(
                            admin_url("admin-post.php?action=solwee_fetch_image&title=" . urlencode($entry['title'])),
                            'solwee_fetch_image'
                        ); ?>">📸 Pull Image</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

// ✅ Image fetch logic with Media Library sideloading
add_action('admin_post_solwee_fetch_image', function () {
    if (!current_user_can('manage_options')) wp_die('Access denied');
    check_admin_referer('solwee_fetch_image');

    $title = sanitize_text_field($_GET['title'] ?? '');
    if (!$title) wp_die('No title provided.');

    $search_body = [
        'fulltext' => $title,
        'limit' => 20,
        'sortingTypeID' => 4
    ];

    function solwee_admin_api_call($endpoint, $payload) {
        $res = wp_remote_post("https://api.solwee.com/api/v2/search/images/{$endpoint}", [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/json', 'X-WebID' => '57'],
            'body' => json_encode($payload)
        ]);
        if (is_wp_error($res)) return [];
        $parsed = json_decode(wp_remote_retrieve_body($res), true);
        return $parsed['results'] ?? [];
    }

    $creative = solwee_admin_api_call('creative', $search_body);
    $editorial = solwee_admin_api_call('editorial', $search_body);

    $merged = array_merge($creative, $editorial);
    usort($merged, function ($a, $b) {
        return strtotime($b['createdTime'] ?? '2000-01-01') - strtotime($a['createdTime'] ?? '2000-01-01');
    });

    $top = $merged[0] ?? null;
    if (!$top) wp_die('No image found for "' . esc_html($title) . '"');

    $remote_url = $top['previewPath'] ?? $top['thumb260Url'] ?? $top['thumbUrl'] ?? '';
    if (!$remote_url) wp_die('Image has no valid preview or thumbnail URL.');

    // ✅ Download and attach to Media Library
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url($remote_url);
    if (is_wp_error($tmp)) wp_die('Failed to download image.');

    $file_array = [
        'name'     => basename(parse_url($remote_url, PHP_URL_PATH)),
        'tmp_name' => $tmp
    ];

    $attach_id = media_handle_sideload($file_array, 0);
    if (is_wp_error($attach_id)) {
        @unlink($tmp);
        wp_die('Failed to sideload image.');
    }

    $local_url = wp_get_attachment_url($attach_id);
    if (!$local_url) wp_die('Could not get saved image URL.');

    $entries = get_option('solwee_ticker_titles', []);
    foreach ($entries as &$entry) {
        if ($entry['title'] === $title) {
            $entry['image'] = esc_url_raw($local_url);
            break;
        }
    }
    update_option('solwee_ticker_titles', $entries);

    wp_redirect(admin_url('options-general.php?page=solwee-ticker-titles'));
    exit;
});
