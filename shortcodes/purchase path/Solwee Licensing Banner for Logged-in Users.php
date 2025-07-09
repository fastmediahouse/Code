add_action('wp_footer', function() {
    if (!is_user_logged_in()) return;

    $user = wp_get_current_user();
    if (!$user || empty($user->ID)) return;

    $token = get_user_meta($user->ID, 'solwee_token', true);
    if ($token) return;

    // Show only if user is within first 7 days of registration
    $registration = strtotime($user->user_registered);
    if ((time() - $registration) > 7 * 86400) return;

    // Show only on BuddyBoss profile or dashboard pages
    if (!function_exists('bp_displayed_user_id') || !bp_is_user()) return;

    echo '
    <style>
        .solwee-banner {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 12px;
            margin: 20px auto;
            max-width: 700px;
            text-align: center;
            font-size: 15px;
            border-radius: 8px;
            position: relative;
        }
        .solwee-banner button {
            position: absolute;
            top: 8px;
            right: 10px;
            background: none;
            border: none;
            font-weight: bold;
            font-size: 16px;
            color: #92400e;
            cursor: pointer;
        }
    </style>

    <div class="solwee-banner" id="solweeBanner" style="display: none;">
        <button onclick="dismissSolweeBanner()">×</button>
        ✅ Your account is active. Full licensing and download access will be enabled shortly after registration. No further action is needed.
    </div>

    <script>
        function dismissSolweeBanner() {
            sessionStorage.setItem("hideSolweeBanner", "1");
            document.getElementById("solweeBanner").style.display = "none";
        }

        if (!sessionStorage.getItem("hideSolweeBanner")) {
            document.getElementById("solweeBanner").style.display = "block";
        }
    </script>';
});
