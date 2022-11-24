<?php
/*
Plugin Name: Magic Login
Description: Login with magic links via Magic.mk
Author: DigitalNode
Version: 1.0
Author URI: https://digitalnode.com
*/

/**
 * Disable sslverify in http requests for local development
 */
if (defined('WP_ENV') && WP_ENV !== 'production' ) {
    add_filter( "http_request_args", function( $args, $url ) {
        $args['sslverify'] = false;
        return $args;
    }, 10, 2 );
}

/**
 * Detect a Magic link redirect and authenticate/register a user
 */
add_action( 'init', function() {

    if( !isset($_GET['type']) ) {
        return;
    }

    if ($_GET['type'] !== 'magic') {
        return;
    }
    
    if( empty( $_GET['token'] ) ) {
        return;
    }

    if ( !defined('MAGIC_API_KEY') ) {
        echo 'You need to define a Magic API key in wp-config.php.';
        return;
    }

    $token = $_GET['token'];
    $url = "https://magic.mk/api/validate/";
    $xapikey = MAGIC_API_KEY;
    $body = json_encode([
        'token' => $token
    ]);

    $h = wp_remote_post($url, [
        'body'        => $body,
        'headers'     => [
            'Content-Type' => 'application/json',
            'X-API-Key' => $xapikey
        ],
        'timeout'     => 60,
    ]);

    if( wp_remote_retrieve_response_code($h) !== 200 ) {
        echo "Something went wrong with the Magic Login plugin.";
        return;
    }
    $magicresponse = json_decode( wp_remote_retrieve_body( $h ) );

    // Log the user in
    $user = get_user_by("email", $magicresponse->email);
    if( !empty($user) ) {
        clean_user_cache( $user->ID );
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true, is_ssl() );
        update_user_caches( $user );
    } else {

        $email = sanitize_text_field($magicresponse->email);
        $username = $email;
        $password = uniqid() . wp_generate_uuid4();
        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            $user = get_user_by('id', $user_id);
            $user->set_role('subscriber');
            wp_set_current_user($user_id);
            @wp_set_auth_cookie($user_id);
        }
    }

    wp_redirect( site_url() );
    exit();
} );
