<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'emono_handle_save' );
function emono_handle_save() {
    if ( ! isset( $_POST['en_action'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;

    $action = sanitize_key( $_POST['en_action'] );
    $nonce_map = array(
        'design'  => 'en_save_design',
        'general' => 'en_save_general',
        'nav'     => 'en_save_nav',
        'contact' => 'en_save_contact',
        'editor'  => 'en_save_editor',
    );

    if ( function_exists( 'emono_admin_save_portfolio_nonce_map' ) ) {
        $nonce_map = array_merge( $nonce_map, emono_admin_save_portfolio_nonce_map() );
    }

    if ( ! isset( $nonce_map[ $action ] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( isset( $_POST['en_nonce'] ) ? $_POST['en_nonce'] : '' ) ), $nonce_map[ $action ] ) ) return;

    $opts = emono_get_options();
    $tab = $action;
    $should_update = true;

    switch ( $action ) {

        case 'design':
            $design_mode_in = sanitize_key( wp_unslash( isset( $_POST['design_mode'] ) ? $_POST['design_mode'] : 'dark' ) );
            $opts['design_mode'] = in_array( $design_mode_in, array( 'dark', 'light', 'auto' ), true ) ? $design_mode_in : 'dark';
            $opts['design_bg']     = sanitize_hex_color( isset( $_POST['design_bg'] )     ? wp_unslash( $_POST['design_bg'] )     : '#000000' );
            $opts['design_text']   = sanitize_hex_color( isset( $_POST['design_text'] )   ? wp_unslash( $_POST['design_text'] )   : '#ffffff' );
            $opts['design_accent'] = sanitize_hex_color( isset( $_POST['design_accent'] ) ? wp_unslash( $_POST['design_accent'] ) : '#ffffff' );
            $opts['design_font']   = sanitize_text_field( isset( $_POST['design_font'] )  ? wp_unslash( $_POST['design_font'] )  : 'System Mono' );
            break;

        case 'general':
            $opts['site_name']    = sanitize_text_field( isset( $_POST['site_name'] ) ? wp_unslash( $_POST['site_name'] ) : '' );
            $opts['site_tagline'] = sanitize_text_field( isset( $_POST['site_tagline'] ) ? wp_unslash( $_POST['site_tagline'] ) : '' );
            $opts['copyright']    = sanitize_text_field( isset( $_POST['copyright'] ) ? wp_unslash( $_POST['copyright'] ) : '' );
            $opts['logo_url']       = esc_url_raw( isset( $_POST['logo_url'] ) ? wp_unslash( $_POST['logo_url'] ) : '' );
            $opts['logo_url_light'] = esc_url_raw( isset( $_POST['logo_url_light'] ) ? wp_unslash( $_POST['logo_url_light'] ) : '' );

            $opts['top_logo_size']        = isset( $_POST['top_logo_size'] )        && (float) $_POST['top_logo_size'] > 0 ? (float) $_POST['top_logo_size'] : 14;
            $opts['top_logo_size_tablet'] = isset( $_POST['top_logo_size_tablet'] ) && (float) $_POST['top_logo_size_tablet'] > 0 ? (float) $_POST['top_logo_size_tablet'] : 0;
            $opts['top_logo_size_mobile'] = isset( $_POST['top_logo_size_mobile'] ) && (float) $_POST['top_logo_size_mobile'] > 0 ? (float) $_POST['top_logo_size_mobile'] : 0;
            $opts['header_logo_size']        = isset( $_POST['header_logo_size'] )        && (float) $_POST['header_logo_size'] > 0 ? (float) $_POST['header_logo_size'] : 3.2;
            $opts['header_logo_size_tablet'] = isset( $_POST['header_logo_size_tablet'] ) && (float) $_POST['header_logo_size_tablet'] > 0 ? (float) $_POST['header_logo_size_tablet'] : 0;
            $opts['header_logo_size_mobile'] = isset( $_POST['header_logo_size_mobile'] ) && (float) $_POST['header_logo_size_mobile'] > 0 ? (float) $_POST['header_logo_size_mobile'] : 0;

            $btn_labels   = isset( $_POST['top_btn_label'] ) ? wp_unslash( $_POST['top_btn_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $btn_urls     = isset( $_POST['top_btn_url'] ) ? wp_unslash( $_POST['top_btn_url'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $btn_manuals  = isset( $_POST['top_btn_url_manual'] ) ? wp_unslash( $_POST['top_btn_url_manual'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $top_buttons  = array();
            foreach ( $btn_labels as $i => $label ) {
                $label = sanitize_text_field( $label );
                if ( ! $label ) continue;
                $sel = isset( $btn_urls[ $i ] ) ? $btn_urls[ $i ] : '';
                $url = $sel ? esc_url_raw( $sel ) : esc_url_raw( isset( $btn_manuals[ $i ] ) ? $btn_manuals[ $i ] : '' );
                $top_buttons[] = array( 'label' => $label, 'url' => $url );
            }
            $opts['top_buttons'] = $top_buttons;
            break;

        case 'nav':
            $opts['nav_mode']    = sanitize_key( isset( $_POST['nav_mode'] ) ? wp_unslash( $_POST['nav_mode'] ) : 'auto' );
            $opts['nav_wp_menu'] = absint( isset( $_POST['nav_wp_menu'] ) ? $_POST['nav_wp_menu'] : 0 );

            $labels   = isset( $_POST['nav_label'] ) ? wp_unslash( $_POST['nav_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $types    = isset( $_POST['nav_type'] ) ? wp_unslash( $_POST['nav_type'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $urls     = isset( $_POST['nav_url'] ) ? wp_unslash( $_POST['nav_url'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $page_ids = isset( $_POST['nav_page_id'] ) ? wp_unslash( $_POST['nav_page_id'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $nav = array();
            foreach ( $labels as $i => $label ) {
                $type = sanitize_key( isset( $types[ $i ] ) ? $types[ $i ] : 'url' );
                if ( $type === 'page' ) {
                    $page_id = (int) ( isset( $page_ids[ $i ] ) ? $page_ids[ $i ] : 0 );
                    if ( $page_id ) {
                        $nav[] = array(
                            'label'   => sanitize_text_field( $label ),
                            'type'    => 'page',
                            'page_id' => $page_id,
                            'url'     => get_permalink( $page_id ),
                        );
                    }
                } else {
                    $url = esc_url_raw( isset( $urls[ $i ] ) ? $urls[ $i ] : '' );
                    if ( $label && $url ) {
                        $nav[] = array(
                            'label' => sanitize_text_field( $label ),
                            'type'  => 'url',
                            'url'   => $url,
                        );
                    }
                }
            }
            $opts['nav_items'] = $nav;

            $f_labels   = isset( $_POST['footer_nav_label'] ) ? wp_unslash( $_POST['footer_nav_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $f_types    = isset( $_POST['footer_nav_type'] ) ? wp_unslash( $_POST['footer_nav_type'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $f_urls     = isset( $_POST['footer_nav_url'] ) ? wp_unslash( $_POST['footer_nav_url'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $f_page_ids = isset( $_POST['footer_nav_page_id'] ) ? wp_unslash( $_POST['footer_nav_page_id'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $footer_nav = array();
            if ( is_array( $f_types ) ) {
                foreach ( $f_types as $i => $f_type ) {
                    $f_type = sanitize_key( $f_type );
                    $f_label = isset( $f_labels[ $i ] ) ? sanitize_text_field( $f_labels[ $i ] ) : '';
                    if ( $f_type === 'page' ) {
                        $f_page_id = (int) ( isset( $f_page_ids[ $i ] ) ? $f_page_ids[ $i ] : 0 );
                        if ( $f_page_id ) {
                            $footer_nav[] = array(
                                'label'   => $f_label,
                                'type'    => 'page',
                                'page_id' => $f_page_id,
                                'url'     => get_permalink( $f_page_id ),
                            );
                        }
                    } else {
                        $f_url = esc_url_raw( isset( $f_urls[ $i ] ) ? $f_urls[ $i ] : '' );
                        if ( $f_label && $f_url ) {
                            $footer_nav[] = array(
                                'label' => $f_label,
                                'type'  => 'url',
                                'url'   => $f_url,
                            );
                        }
                    }
                }
            }
            $opts['footer_nav_items'] = $footer_nav;
            break;

        case 'contact':
            $opts['contact_email']         = sanitize_email( isset( $_POST['contact_email'] ) ? wp_unslash( $_POST['contact_email'] ) : '' );
            $opts['contact_desc']          = sanitize_textarea_field( isset( $_POST['contact_desc'] ) ? wp_unslash( $_POST['contact_desc'] ) : '' );
            $opts['contact_btn_text']      = sanitize_text_field( isset( $_POST['contact_btn_text'] ) ? wp_unslash( $_POST['contact_btn_text'] ) : 'Send' );
            $opts['contact_success']       = sanitize_text_field( isset( $_POST['contact_success'] ) ? wp_unslash( $_POST['contact_success'] ) : '' );
            $opts['contact_auto_reply']    = sanitize_text_field( isset( $_POST['contact_auto_reply'] ) ? wp_unslash( $_POST['contact_auto_reply'] ) : '1' );
            $opts['contact_reply_subject'] = sanitize_text_field( isset( $_POST['contact_reply_subject'] ) ? wp_unslash( $_POST['contact_reply_subject'] ) : '' );
            $opts['contact_reply_body']    = sanitize_textarea_field( isset( $_POST['contact_reply_body'] ) ? wp_unslash( $_POST['contact_reply_body'] ) : '' );
            $opts['contact_mail_subject']  = sanitize_text_field( isset( $_POST['contact_mail_subject'] ) ? wp_unslash( $_POST['contact_mail_subject'] ) : '' );
            $opts['contact_mail_body']     = sanitize_textarea_field( isset( $_POST['contact_mail_body'] ) ? wp_unslash( $_POST['contact_mail_body'] ) : '' );
            $opts['recaptcha_site_key']    = sanitize_text_field( isset( $_POST['recaptcha_site_key'] ) ? wp_unslash( $_POST['recaptcha_site_key'] ) : '' );
            $opts['recaptcha_secret']      = sanitize_text_field( isset( $_POST['recaptcha_secret'] ) ? wp_unslash( $_POST['recaptcha_secret'] ) : '' );
            $opts['contact_consent_enabled'] = isset( $_POST['contact_consent_enabled'] ) ? '1' : '0';
            $opts['contact_consent_text']    = sanitize_text_field( isset( $_POST['contact_consent_text'] ) ? wp_unslash( $_POST['contact_consent_text'] ) : __( 'I agree to the Privacy Policy.', 'emerge-mono-portfolio' ) );
            $opts['contact_consent_page_id'] = absint( isset( $_POST['contact_consent_page_id'] ) ? $_POST['contact_consent_page_id'] : 0 );
            update_option( 'en_options', $opts );

            $cf_labels       = isset( $_POST['cf_label'] ) ? wp_unslash( $_POST['cf_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $cf_keys         = isset( $_POST['cf_key'] ) ? wp_unslash( $_POST['cf_key'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $cf_types        = isset( $_POST['cf_type'] ) ? wp_unslash( $_POST['cf_type'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $cf_placeholders = isset( $_POST['cf_placeholder'] ) ? wp_unslash( $_POST['cf_placeholder'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $cf_requireds    = isset( $_POST['cf_required'] ) ? wp_unslash( $_POST['cf_required'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $cf_options_raw  = isset( $_POST['cf_options'] ) ? wp_unslash( $_POST['cf_options'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.

            $contact_fields = array();
            foreach ( $cf_keys as $i => $key ) {
                $key = sanitize_key( $key );
                if ( ! $key ) continue;
                $type = sanitize_text_field( isset( $cf_types[ $i ] ) ? $cf_types[ $i ] : 'text' );
                $options = array();
                if ( in_array( $type, array( 'select', 'checkbox' ), true ) && ! empty( $cf_options_raw[ $i ] ) ) {
                    foreach ( explode( "\n", $cf_options_raw[ $i ] ) as $line ) {
                        $line = trim( $line );
                        if ( $line ) $options[] = sanitize_text_field( $line );
                    }
                }
                $contact_fields[] = array(
                    'key'         => $key,
                    'label'       => sanitize_text_field( isset( $cf_labels[ $i ] ) ? $cf_labels[ $i ] : $key ),
                    'type'        => $type,
                    'placeholder' => sanitize_text_field( isset( $cf_placeholders[ $i ] ) ? $cf_placeholders[ $i ] : '' ),
                    'required'    => isset( $cf_requireds[ $i ] ) && $cf_requireds[ $i ] === '1',
                    'options'     => $options,
                );
            }
            update_option( 'en_contact_fields', $contact_fields );
            wp_safe_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=contact&saved=1' ) );
            exit;

        case 'editor':
            $opts['editor_enabled']     = isset( $_POST['editor_enabled'] ) && $_POST['editor_enabled'] === '1' ? '1' : '0';
            $opts['show_default_posts'] = isset( $_POST['show_default_posts'] ) && $_POST['show_default_posts'] === '1' ? '1' : '0';
            break;

        default:
            if ( ! function_exists( 'emono_admin_save_portfolio_action' ) ) return;
            $result = emono_admin_save_portfolio_action( $action, $opts );
            if ( empty( $result['handled'] ) ) return;
            $opts = isset( $result['opts'] ) ? $result['opts'] : $opts;
            $tab = isset( $result['tab'] ) ? $result['tab'] : $action;
            $should_update = ! empty( $result['update_options'] );
            break;
    }

    if ( $should_update ) {
        update_option( 'en_options', $opts );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=' . $tab . '&saved=1' ) );
    exit;
}
