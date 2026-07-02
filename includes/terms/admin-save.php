<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'en_save_terms' );
function en_save_terms() {
    if (
        ! isset( $_POST['en_nonce'] ) ||
        ! wp_verify_nonce( $_POST['en_nonce'], 'en_save_terms' ) ||
        ! current_user_can( 'manage_options' )
    ) return;

    if ( ! isset( $_POST['en_terms_save'] ) ) return;

    $opts = en_get_options();
    $opts['terms_lang']        = sanitize_key( (isset($_POST['terms_lang']) ? $_POST['terms_lang'] : 'ja') );
    $opts['legal_owner']       = sanitize_text_field( (isset($_POST['legal_owner']) ? $_POST['legal_owner'] : '') );
    $opts['legal_site']        = sanitize_text_field( (isset($_POST['legal_site']) ? $_POST['legal_site'] : '') );
    $opts['legal_email']       = sanitize_email( (isset($_POST['legal_email']) ? $_POST['legal_email'] : '') );
    $opts['terms_use_disclaimer'] = isset($_POST['terms_use_disclaimer']) ? '1' : '0';
    $opts['terms_custom']      = wp_kses_post( (isset($_POST['terms_custom']) ? $_POST['terms_custom'] : '') );

    update_option( 'en_options', $opts );
    wp_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=terms&saved=1' ) );
    exit;
}
