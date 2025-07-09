add_shortcode('solwee_api_status', function() {
    $username = 'jan.jukli@fm.house';
    $password = 'YOUR_PASSWORD_HERE'; // Replace securely

    $response = wp_remote_post('https://api.solwee.com/api/v2/login', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode(['email' => $username, 'password' => $password]),
    ]);

    if (is_wp_error($response)) {
        return '<p>❌ Solwee API status: <strong>Request failed</strong><br>' . $response->get_error_message() . '</p>';
    }

    $body_raw = wp_remote_retrieve_body($response);
    $body = json_decode($body_raw);
    $token = $body->token ?? null;

    if ($token) {
        return '<p>✅ Solwee API is <strong>online</strong> and accepting login requests.</p>';
    } else {
        return '<p>🔥 Solwee API status: <strong>Internal Server Error</strong><br><pre>' . esc_html($body_raw) . '</pre></p>';
    }
});
