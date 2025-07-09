add_action('wp_footer', function () {
    if (!is_page('signin')) return;
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Try multiple selectors just in case
            const labels = document.querySelectorAll('label');

            labels.forEach(label => {
                const text = label.innerText.trim().toLowerCase();
                if (text === 'email address' || text.includes('email')) {
                    label.textContent = 'Username or Email';
                }
            });

            const input = document.querySelector('input[type="text"], input[type="email"]');
            if (input && input.placeholder && input.placeholder.toLowerCase().includes('email')) {
                input.placeholder = 'Username or Email';
            }
        });
    </script>
    <?php
});
