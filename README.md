# MagicMK for WordPress

www.magic.mk

## Features

1. **Easy integration** of magic.mk authentication into WordPress projects.
2. **Quick adaptation**, the integration script will dynamically adapt the login form to your magic.mk project settings.

## Installation

First, install the plugin: https://wordpress.com/support/plugins/install-a-plugin/

Before using the Magic Login Plugin, you need to configure the following constants in your `wp-config.php` file.
Visit www.magic.mk, log in and make a project, then:

1. `MAGIC_LOGIN_API_KEY`: Your MagicMK project API key.
2. `MAGIC_LOGIN_PROJECT_KEY`: Your MagicMK project key.

Add these lines to your `wp-config.php`:

```php
define('MAGIC_LOGIN_API_KEY', 'your_api_key_here');
define('MAGIC_LOGIN_PROJECT_KEY', 'your_project_key_here');
```

Optional: If you need to override the default Magic Login URL (e.g., for local testing), you can add:

```php
define('MAGIC_URL_OVERWRITE', 'https://your-custom-magic-url.com');
```

## Customizing the Magic Login Template

**When overwriting, please keep this structure and build upon it:**

```php
<form id="magic-form">
    <input id="magic-input" required>
    <button id="magic-submit"></button>
    <div id="RecaptchaField"></div>
    <p id="validation-message"></p>
</form>
<script>
    window.magicmk = {
        project_slug: '<?php echo esc_js(get_query_var('magic_login_project_key')); ?>',
        language: '',
        redirect_url: '',
        params: {
            extra: "parameters",
        }
    };
</script>
<script src="<?php echo esc_url(get_query_var('magic_url')); ?>/magicmk_integration_min.js"></script>
   ```

### Method 1: Theme Override

You can override the default template by creating a custom template file in your theme. There are two ways to do this:

#### Option A: Plugin-specific folder (Recommended)

1. Create a folder named `magic-login-plugin` in your theme directory.
2. Inside this folder, create a file named `magic-login-template.php`.
3. Copy the content from the plugin's `templates/magic-login-template.php` into your new file.
4. Modify the template as needed.

File path: `wp-content/themes/your-theme/magic-login-plugin/magic-login-template.php`

#### Option B: Direct theme override

1. In your theme's root directory, create a file named `magic-login-template.php`.
2. Copy the content from the plugin's `templates/magic-login-template.php` into your new file.
3. Modify the template as needed.

File path: `wp-content/themes/your-theme/magic-login-template.php`

Note: The plugin will prioritize Option A over Option B if both exist.

### Method 2: Using Filters and Actions

The Magic Login Plugin provides several filters and actions to customize the login page without overriding the entire
template.

#### Available Filters:

1. `magic_login_page_title`: Modify the page title.
   ```php
   add_filter('magic_login_page_title', function($title) {
       return 'Custom Magic Login';
   });
   ```

2. `magic_login_form_class`: Add custom classes to the login form.
   ```php
   add_filter('magic_login_form_class', function($classes) {
       return $classes . ' my-custom-class';
   });
   ```

#### Available Actions:

1. `magic_login_head`: Add custom content to the `<head>` section.
   ```php
   add_action('magic_login_head', function() {
       echo '<style>.magic-login-form { background: #f1f1f1; }</style>';
   });
   ```

2. `magic_login_before_form`: Add content before the login form.
   ```php
   add_action('magic_login_before_form', function() {
       echo '<h2>Welcome to Magic Login</h2>';
   });
   ```

3. `magic_login_before_fields`: Add content inside the form, before the default fields.
   ```php
   add_action('magic_login_before_fields', function() {
       echo '<div class="custom-message">Enter your details below:</div>';
   });
   ```

4. `magic_login_after_fields`: Add content inside the form, after the default fields.
   ```php
   add_action('magic_login_after_fields', function() {
       echo '<div class="terms-agreement">By logging in, you agree to our terms.</div>';
   });
   ```

5. `magic_login_after_form`: Add content after the login form.
   ```php
   add_action('magic_login_after_form', function() {
       echo '<div class="post-form-content">Need help? Contact support.</div>';
   });
   ```

6. `magic_login_footer`: Add custom content just before the closing `</body>` tag.
   ```php
   add_action('magic_login_footer', function() {
       echo '<script>console.log("Magic Login loaded");</script>';
   });
   ```

## Credits

Author: Dushan Cimbaljevic

Email: dushan@digitalnode.com
