add_action('template_redirect', function() {
    if (!is_page('download') || empty($_GET['pid'])) return;
    $pid = sanitize_text_field(wp_unslash($_GET['pid']));
    $url = solwee_get_download_url($pid);
    if (!$url) wp_die('Sorry, the download link is no longer available.');
    wp_safe_redirect($url);
    exit;
});
