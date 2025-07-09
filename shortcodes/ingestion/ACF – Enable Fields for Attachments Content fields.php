// ✅ Show 'attachment' as post type in ACF location rules
add_filter('acf/location/rule_values/post_type', function ($choices) {
    $choices['attachment'] = 'Attachment';
    return $choices;
});

// ✅ Allow ACF to match 'attachment' post type rules
add_filter('acf/location/rule_match/post_type', function ($match, $rule, $options) {
    if ($rule['value'] === 'attachment') {
        $post_type = $options['post_type'] ?? '';
        return ($rule['operator'] === '==') ? ($post_type === 'attachment') : ($post_type !== 'attachment');
    }
    return $match;
}, 10, 3);

// ✅ Ensure ACF fields show on Media Edit screen
add_filter('acf/location/screen', function ($show, $screen) {
    return (!empty($screen['post_type']) && $screen['post_type'] === 'attachment') ? true : $show;
}, 10, 2);

// ✅ Elementor-safe: render ACF fields manually after editor content
add_action('edit_form_after_editor', function () {
    global $post;
    if (!is_admin() || !$post || get_post_type($post) !== 'attachment') return;

    echo '<div class="acf-fields-acf-body">';
    acf_form([
        'post_id' => $post->ID,
        'form'    => false,
        'fields'  => [],
    ]);
    echo '</div>';
});
