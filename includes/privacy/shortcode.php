<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'emerge_mono_privacy', 'emono_shortcode_privacy' );
function emono_shortcode_privacy( $atts ) {
    $opts           = emono_get_options();
    $lang           = isset( $opts['privacy_lang'] )        ? $opts['privacy_lang']        : 'ja';
    $owner          = isset( $opts['legal_owner'] )       ? $opts['legal_owner']       : '';
    $site           = isset( $opts['legal_site'] )        ? $opts['legal_site']        : get_bloginfo('name');
    $email          = isset( $opts['legal_email'] )       ? $opts['legal_email']       : get_bloginfo('admin_email');
    $use_cookie     = isset( $opts['privacy_cookie'] )      ? $opts['privacy_cookie']      : '1';
    $use_ga         = isset( $opts['privacy_ga'] )          ? $opts['privacy_ga']          : '0';
    $use_disclaimer = isset( $opts['privacy_disclaimer'] )  ? $opts['privacy_disclaimer']  : '1';
    $custom         = isset( $opts['privacy_custom'] )      ? $opts['privacy_custom']      : '';

    $html = emono_privacy_generate_html( $lang, $owner, $site, $email, $use_cookie, $use_ga, $use_disclaimer, $custom );

    return '<div class="en-privacy">' . $html . '</div>';
}
