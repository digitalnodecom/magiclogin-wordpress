<?php

function magic_login_shortcode($atts) {
    ob_start();
    ?>
    <div class="magic-login-container">
        <?php do_action('magic_login_before_form'); ?>
        <form id="magic-form" class="<?php echo esc_attr(apply_filters('magic_login_form_class', 'magic-login-form')); ?>">
            <?php do_action('magic_login_before_fields'); ?>
            <input id="magic-input" required>
            <button id="magic-submit"></button>
            <div id="RecaptchaField"></div>
            <p id="validation-message"></p>
            <?php do_action('magic_login_after_fields'); ?>
        </form>
        <?php do_action('magic_login_after_form'); ?>
        <script>
            window.magicmk = {
                project_slug: '<?php echo defined('MAGIC_LOGIN_PROJECT_KEY') ? MAGIC_LOGIN_PROJECT_KEY : '' ?>',
                language: 'en',
                redirect_url: 'https://wordpress.test/',
                params: {
                    extra: "parameters",
                }
            };
        </script>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('magic_login', 'magic_login_shortcode');

// Enqueue necessary scripts and styles
function magic_login_enqueue_scripts() {
    if (has_shortcode(get_post()->post_content, 'magic_login')) {
        wp_enqueue_script('magic-login-script', 'https://magic.mk/magicmk_integration_min.js', [], '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'magic_login_enqueue_scripts');

// Add necessary actions to wp_head and wp_footer
function magic_login_head() {
    if (has_shortcode(get_post()->post_content, 'magic_login')) {
        do_action('magic_login_head');
    }
}
add_action('wp_head', 'magic_login_head');

function magic_login_footer() {
    if (has_shortcode(get_post()->post_content, 'magic_login')) {
        do_action('magic_login_footer');
    }
}
add_action('wp_footer', 'magic_login_footer');