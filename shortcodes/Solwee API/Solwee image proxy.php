add_action('init', function () {
    if (!isset($_GET['solwee_image_proxy'])) return;

    $productID = sanitize_text_field($_GET['solwee_image_proxy']);
    $webID = '57';

    // Step 1: Check for cached token
    $token = get_transient('solwee_api_token');

    if (!$token) {
        // Step 1a: Login to get token
        $login_response = wp_remote_post('https://api.solwee.com/api/v2/login', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-WebID' => $webID
            ],
            'body' => json_encode([
                'login' => 'marco@fm.house',  // ✅ Correct login
                'password' => 'your-password-here'  // 🔥 Your real password here
            ]),
            'timeout' => 10,
        ]);

        $body = wp_remote_retrieve_body($login_response);
        $token = trim($body, "\"");

        if (!$token) {
            status_header(403);
            echo "Unauthorized - Failed to get token.";
            exit;
        }

        set_transient('solwee_api_token', $token, 15 * MINUTE_IN_SECONDS);
    }

    // Step 2: Get large preview
    $preview_url = "https://api.solwee.com/api/v2/large-preview/{$productID}";
    $image_response = wp_remote_get($preview_url, [
        'headers' => [
            'Authorization' => "Bearer {$token}",
            'X-WebID' => $webID
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($image_response)) {
        status_header(500);
        echo "Image fetch failed: " . $image_response->get_error_message();
        exit;
    }

    $image_body = wp_remote_retrieve_body($image_response);
    $content_type = wp_remote_retrieve_header($image_response, 'content-type') ?? 'image/jpeg';

    // Step 3: Output raw image data
    header("Content-Type: {$content_type}");
    echo $image_body;
    exit;
});
