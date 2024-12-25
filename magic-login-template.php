<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo apply_filters('magic_login_page_title', 'Magic Login'); ?></title>
    <?php wp_head(); ?>
    <?php do_action('magic_login_head'); ?>
</head>
<body>
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
        project_slug: <?php echo defined('MAGIC_LOGIN_PROJECT_KEY') ? MAGIC_LOGIN_PROJECT_KEY : '' ?>,
        language: '',
        redirect_url: '',
        params: {
            extra: "parameters",
        }
    };
</script>
<script src="https://magic.mk/magicmk_integration_min.js"></script>
<?php wp_footer(); ?>
<?php do_action('magic_login_footer'); ?>
</body>
</html>