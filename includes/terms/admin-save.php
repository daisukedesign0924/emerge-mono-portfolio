<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'emono_save_terms' );
function emono_save_terms() {
    if (
        ! isset( $_POST['en_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['en_nonce'] ?? '' ) ), 'emono_save_terms' ) ||
        ! current_user_can( 'manage_options' )
    ) return;

    if ( ! isset( $_POST['en_terms_save'] ) ) return;

    $opts = emono_get_options();
    $opts['terms_lang']        = sanitize_key( (isset($_POST['terms_lang']) ? wp_unslash($_POST['terms_lang']) : 'ja') );
    $opts['legal_owner']       = sanitize_text_field( (isset($_POST['legal_owner']) ? wp_unslash($_POST['legal_owner']) : '') );
    $opts['legal_site']        = sanitize_text_field( (isset($_POST['legal_site']) ? wp_unslash($_POST['legal_site']) : '') );
    $opts['legal_email']       = sanitize_email( (isset($_POST['legal_email']) ? wp_unslash($_POST['legal_email']) : '') );
    $opts['terms_use_disclaimer'] = isset($_POST['terms_use_disclaimer']) ? '1' : '0';
    $opts['terms_custom']      = wp_kses_post( (isset($_POST['terms_custom']) ? wp_unslash($_POST['terms_custom']) : '') );

    update_option( 'en_options', $opts );
    wp_safe_redirect( admin_url( 'admin.php?page=emerge-mono&tab=terms&saved=1' ) );
    exit;
}
