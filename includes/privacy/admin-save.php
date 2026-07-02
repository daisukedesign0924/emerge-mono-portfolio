<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'en_save_privacy' );
function en_save_privacy() {
    if (
        ! isset( $_POST['en_nonce'] ) ||
        ! wp_verify_nonce( $_POST['en_nonce'], 'en_save_privacy' ) ||
        ! current_user_can( 'manage_options' )
    ) return;

    if ( ! isset( $_POST['en_privacy_save'] ) ) return;

    $opts = en_get_options();
    $opts['privacy_lang']        = sanitize_key( (isset($_POST['privacy_lang']) ? $_POST['privacy_lang'] : 'ja') );
    $opts['legal_owner']       = sanitize_text_field( (isset($_POST['legal_owner']) ? $_POST['legal_owner'] : '') );
    $opts['legal_site']        = sanitize_text_field( (isset($_POST['legal_site']) ? $_POST['legal_site'] : '') );
    $opts['legal_email']       = sanitize_email( (isset($_POST['legal_email']) ? $_POST['legal_email'] : '') );
    $opts['privacy_cookie']      = isset($_POST['privacy_cookie'])      ? '1' : '0';
    $opts['privacy_ga']          = isset($_POST['privacy_ga'])          ? '1' : '0';
    $opts['privacy_disclaimer']  = isset($_POST['privacy_disclaimer'])  ? '1' : '0';
    $opts['privacy_custom']      = wp_kses_post( (isset($_POST['privacy_custom']) ? $_POST['privacy_custom'] : '') );
    $opts['cookie_banner_enabled']  = isset($_POST['cookie_banner_enabled']) ? '1' : '0';
    $opts['cookie_banner_page_id']  = (int)( (isset($_POST['cookie_banner_page_id']) ? $_POST['cookie_banner_page_id'] : 0) );

    update_option( 'en_options', $opts );
    wp_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=privacy&saved=1' ) );
    exit;
}
