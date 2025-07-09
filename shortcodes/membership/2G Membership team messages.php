// ✅ 2G – TEAM MESSAGES SHORTCODE: Shows only threads where all users share the same team_id
add_shortcode('fastmedia_team_messages', function () {
    if (!is_user_logged_in()) return '<p>Please log in to view your team messages.</p>';

    $user_id = get_current_user_id();
    $team_id = get_user_meta($user_id, 'team_id', true);
    if (!$team_id) return '<p>You are not part of a team yet.</p>';

    if (!function_exists('bp_is_active') || !bp_is_active('messages')) {
        return '<p>Messaging is not enabled on this site.</p>';
    }

    if (!class_exists('BP_Messages_Thread')) {
        return '<p>Messaging system is unavailable.</p>';
    }

    $threads = BP_Messages_Thread::get_current_threads_for_user($user_id, 'all', ['per_page' => 20, 'page' => 1]);

    if (empty($threads)) return '<p>No team messages yet.</p>';

    $output = '<div class="fm-team-messages"><h3>💬 Team Messages</h3><ul>';

    foreach ($threads as $thread) {
        $participant_ids = $thread->recipients;
        $valid = true;

        foreach ($participant_ids as $pid => $participant) {
            $p_team = get_user_meta($pid, 'team_id', true);
            if ($p_team !== $team_id) {
                $valid = false;
                break;
            }
        }

        if (!$valid) continue;

        $participants = [];
        foreach ($participant_ids as $pid => $p_data) {
            $participants[] = esc_html(bp_core_get_user_displayname($pid));
        }

        $output .= '<li style="margin-bottom:16px; padding:10px; border:1px solid #ccc; border-radius:6px; background:#f9f9f9;">';
        $output .= '<strong>🧑 Participants:</strong> ' . implode(', ', $participants) . '<br>';
        $output .= '<strong>🗨️ Latest:</strong> ' . esc_html(wp_trim_words($thread->last_message_content, 25)) . '<br>';
        $output .= '<em>📅 ' . esc_html(bp_format_time(strtotime($thread->last_message_date))) . '</em>';
        $output .= '</li>';
    }

    $output .= '</ul></div>';
    return $output;
});
