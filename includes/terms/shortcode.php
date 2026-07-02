<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'emerge_mono_terms', 'en_shortcode_terms' );
function en_shortcode_terms( $atts ) {
    $opts           = en_get_options();
    $lang           = isset($opts['terms_lang'])           ? $opts['terms_lang']           : 'ja';
    $owner          = isset($opts['legal_owner'])          ? $opts['legal_owner']          : '';
    $site           = isset($opts['legal_site'])           ? $opts['legal_site']           : get_bloginfo('name');
    $email          = isset($opts['legal_email'])          ? $opts['legal_email']          : get_bloginfo('admin_email');
    $use_disclaimer = isset($opts['terms_use_disclaimer']) ? $opts['terms_use_disclaimer'] : '1';
    $custom         = isset($opts['terms_custom'])         ? $opts['terms_custom']         : '';

    $html = en_terms_generate_html( $lang, $owner, $site, $email, $use_disclaimer, $custom );
    return '<div class="en-privacy">' . $html . '</div>';
}

// AJAX プレビュー
add_action( 'wp_ajax_en_preview_terms', 'en_ajax_preview_terms' );
function en_ajax_preview_terms() {
    if ( ! check_ajax_referer('en_preview_terms', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono' ) );
    }
    $lang           = sanitize_key( (isset($_POST['terms_lang']) ? $_POST['terms_lang'] : 'ja') );
    $owner          = sanitize_text_field( (isset($_POST['legal_owner']) ? $_POST['legal_owner'] : '') );
    $site           = sanitize_text_field( (isset($_POST['legal_site']) ? $_POST['legal_site'] : '') );
    $email          = sanitize_email( (isset($_POST['legal_email']) ? $_POST['legal_email'] : '') );
    $use_disclaimer = isset($_POST['terms_use_disclaimer']) ? '1' : '0';
    $custom         = wp_kses_post( (isset($_POST['terms_custom']) ? $_POST['terms_custom'] : '') );

    wp_send_json_success( en_terms_generate_html( $lang, $owner, $site, $email, $use_disclaimer, $custom ) );
}
