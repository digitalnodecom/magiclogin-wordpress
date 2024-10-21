<?php
/**
 * Plugin Name: MagicMK Authentication
 * Description: Authentication using magic.mk
 * Version: 1.0
 * Author: dushan@digitalnode.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class MagicLoginPlugin
{
    /**
     * @var string
     */
    private $magic_url;

    public function __construct()
    {
        $this->magic_url = defined('MAGIC_URL_OVERWRITE') ? MAGIC_URL_OVERWRITE : 'https://magic.mk';

        add_action('init', array($this, 'register_magic_login_endpoint'));
        add_action('template_redirect', array($this, 'restrict_magic_login_access'));
        add_action('init', array($this, 'handle_magic_login'));
        add_filter('template_include', array($this, 'load_magic_login_template'));
        add_action('admin_notices', array($this, 'admin_notice_magic_api_key'));
        add_action('admin_notices', array($this, 'admin_notice_magic_project_key'));

        $this->maybe_disable_ssl_verify();
    }

    public function admin_notice_magic_project_key()
    {
        if (!defined('MAGIC_LOGIN_PROJECT_KEY') || empty(MAGIC_LOGIN_PROJECT_KEY)) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Magic Login Plugin: Project Key is not set. Please add MAGIC_LOGIN_PROJECT_KEY to your wp-config.php file.', 'magic-login'); ?></p>
            </div>
            <?php
        }
    }

    public function admin_notice_magic_api_key()
    {
        if (!defined('MAGIC_LOGIN_API_KEY') || empty(MAGIC_LOGIN_API_KEY)) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Magic Login Plugin: API Key is not set. Please add MAGIC_LOGIN_API_KEY to your wp-config.php file.', 'magic-login'); ?></p>
            </div>
            <?php
        }
    }

    public function register_magic_login_endpoint()
    {
        add_rewrite_rule('^magic-login/?$', 'index.php?magic_login=1', 'top');
        add_rewrite_tag('%magic_login%', '([^&]+)');
    }


    public function restrict_magic_login_access()
    {
        if (get_query_var('magic_login') == '1' && is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }
    }

    public function load_magic_login_template($template)
    {
        if (get_query_var('magic_login') == '1' && !is_user_logged_in()) {
            // Theme override in the following order:
            $template_names = array(
                'magic-login-plugin/magic-login-template.php',
                'magic-login-template.php'
            );

            $located = locate_template($template_names, false);

            if ($located) {
                return $located;
            }

            // If no theme overrides, use the plugin's default template
            $plugin_file = plugin_dir_path(__FILE__) . 'templates/magic-login-template.php';
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }
        }
        return $template;
    }

    public function handle_magic_login()
    {
        if (isset($_GET['token']) && isset($_GET['request_id']) && !is_user_logged_in()) {
            $request_id = sanitize_text_field($_GET['request_id']);
            $xapikey = defined('MAGIC_LOGIN_API_KEY') ? MAGIC_LOGIN_API_KEY : '';

            $response = wp_remote_post($this->magic_url . '/api/request_validated/', array(
                'headers' => array(
                    'X-API-Key' => $xapikey,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'request_id' => $request_id,
                ))
            ));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $email = isset($body['email']) ? $body['email'] : null;
                $phone = isset($body['phone']) ? $body['phone'] : null;

                $this->register_or_login_user($email, $phone);

                wp_redirect(home_url());
                exit;
            }
        }
    }

    private function register_or_login_user($email, $phone)
    {
        $user = null;

        if ($email) {
            $user = get_user_by('email', $email);
        } elseif ($phone) {
            $users = get_users(array(
                'meta_key' => 'phone_number',
                'meta_value' => $phone
            ));
            $user = !empty($users) ? $users[0] : null;
        }

        if (!$user) {
            // Create new user
            $username = $email ? $email : 'user_' . wp_generate_password(5, false);
            $user_id = wp_create_user($username, wp_generate_password(), $email);

            if (!is_wp_error($user_id)) {
                $user = get_user_by('ID', $user_id);
                if ($phone) {
                    update_user_meta($user_id, 'phone_number', $phone);
                }
            }
        }

        if ($user && !is_wp_error($user)) {
            $user->set_role(get_option('default_role'));
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login, $user);
        }
    }

    private function maybe_disable_ssl_verify()
    {
        if ($this->is_development_environment()) {
            add_filter('http_request_args', function ($args, $url) {
                $args['sslverify'] = false;
                return $args;
            }, 10, 2);
        }
    }

    private function is_development_environment()
    {
        if (defined('WP_ENV') && WP_ENV !== 'production') {
            return true;
        }
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            return true;
        }
        if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
            return true;
        }
        return false;
    }

    public static function activate()
    {
        $instance = new self();
        $instance->register_magic_login_endpoint();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }
}

new MagicLoginPlugin();


register_activation_hook(__FILE__, array('MagicLoginPlugin', 'activate'));
register_deactivation_hook(__FILE__, array('MagicLoginPlugin', 'deactivate'));