// ✅ Admin menu link
add_action('admin_menu', function() {
    add_users_page(
        'Solwee Users',
        'Solwee Users',
        'manage_options',
        'solwee-users',
        'render_solwee_user_dashboard'
    );
});

// ✅ Dashboard renderer
function render_solwee_user_dashboard() {
    $users = get_users(['role__not_in' => ['administrator']]);

    echo '<div class="wrap"><h1>Solwee User Integration</h1>';
    echo '<style>
        .solwee-table code { font-size: 13px; display: block; }
        .solwee-btn { margin-top: 4px; cursor: pointer; font-size: 11px; padding: 2px 6px; }
        .solwee-status { font-weight: bold; }
        .solwee-status.good { color: green; }
        .solwee-status.bad { color: red; }
    </style>';

    echo '<table class="widefat fixed striped solwee-table">';
    echo '<thead><tr><th>Email</th><th>Password</th><th>Status</th><th>Token</th></tr></thead><tbody>';

    foreach ($users as $user) {
        $uid = $user->ID;
        $email = esc_html($user->user_email);

        $password = esc_html(get_user_meta($uid, 'solwee_password', true));
        $token = get_user_meta($uid, 'solwee_token', true);
        $token_display = $token ? esc_html(substr($token, 0, 40)) . '…' : '<em>None</em>';

        $token_time = get_user_meta($uid, 'solwee_token_time', true);
        $token_age = $token_time ? human_time_diff($token_time, time()) . ' ago' : '—';

        $status = $token ? '<span class="solwee-status good">✅ Token OK</span>' : '<span class="solwee-status bad">❌ No Token</span>';

        echo "<tr>
            <td>{$email}</td>
            <td>
                <code id='pw_$uid'>{$password}</code>
                <button class='solwee-btn' onclick=\"copyToClipboard('pw_$uid')\">Copy</button>
            </td>
            <td>{$status}<br><small>{$token_age}</small></td>
            <td>";

        if ($token) {
            echo "<code id='tk_$uid'>{$token_display}</code>
                  <button class='solwee-btn' onclick=\"copyToClipboard('tk_$uid')\">Copy</button>";
        } else {
            echo '<em>—</em>';
        }

        echo "</td></tr>";
    }

    echo '</tbody></table></div>';

    // ✅ Copy to clipboard JS
    echo '<script>
        function copyToClipboard(id) {
            const el = document.getElementById(id);
            const temp = document.createElement("textarea");
            temp.value = el.innerText;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            document.body.removeChild(temp);
            alert("Copied!");
        }
    </script>';
}

// ✅ Save token time when login succeeds (required for age display)
add_action('wp_login', function($user_login, $user) {
    $email = $user->user_email;
    $password = get_user_meta($user->ID, 'solwee_password', true);
    if (!$password) return;

    $response = wp_remote_post('https://api.solwee.com/api/v2/login', [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WebID' => '57'
        ],
        'body' => json_encode([
            'login' => $email,
            'password' => $password
        ]),
        'timeout' => 10
    ]);

    if (is_wp_error($response)) return;

    $body = wp_remote_retrieve_body($response);
    $token = trim($body);

    if ($token && strpos($token, '.') !== false) {
        update_user_meta($user->ID, 'solwee_token', $token);
        update_user_meta($user->ID, 'solwee_token_time', time());
    }
}, 10, 2);
