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
        'top_editor' => 'en_save_top_editor',
    );

    if ( function_exists( 'emono_admin_save_portfolio_nonce_map' ) ) {
        $nonce_map = array_merge( $nonce_map, emono_admin_save_portfolio_nonce_map() );
    }

    if ( ! isset( $nonce_map[ $action ] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( isset( $_POST['en_nonce'] ) ? $_POST['en_nonce'] : '' ) ), $nonce_map[ $action ] ) ) return;

    $opts = emono_get_options();
    $tab = $action;
    $should_update = true;

    // Design Editor（page=ene-top）から保存された場合は、そこへ戻す。
    $ref = wp_get_referer();
    $from_design = ( $ref && strpos( $ref, 'page=ene-top' ) !== false );

    switch ( $action ) {

        case 'design':
            // Nonce verified above; sanitization happens per-field inside the helper.
            $opts = emono_apply_design_from_post( $opts, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            break;

        case 'general':
            // Nonce verified above; sanitization happens per-field inside the helper.
            $opts = emono_apply_general_from_post( $opts, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            break;

        case 'top_editor':
            $top_layout = sanitize_key( isset( $_POST['top_layout'] ) ? wp_unslash( $_POST['top_layout'] ) : emono_get_top_layout() );
            $top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array( 'mono' => array() );
            $opts['top_layout'] = isset( $top_layouts[ $top_layout ] ) ? $top_layout : 'mono';
            $opts = apply_filters( 'emono_save_top_layout_settings', $opts, $opts['top_layout'] );
            update_option( 'en_options', $opts );
            wp_safe_redirect( admin_url( 'admin.php?page=ene-top&scope=top&saved=1' ) );
            exit;

        case 'nav':
            // Nonce verified above; sanitization happens per-field inside the helper.
            $opts = emono_apply_nav_from_post( $opts, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
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

    if ( $from_design ) {
        $scope_map = array( 'general' => 'general', 'nav' => 'nav', 'design' => 'design', 'profile' => 'profile' );
        $sc = isset( $scope_map[ $action ] ) ? $scope_map[ $action ] : 'top';
        wp_safe_redirect( admin_url( 'admin.php?page=ene-top&scope=' . $sc . '&saved=1' ) );
        exit;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=' . $tab . '&saved=1' ) );
    exit;
}

/**
 * Design 設定を（すでに unslash 済みの）入力配列から $opts に反映する。
 *
 * 本番保存（design ケース）とライブプレビュー（top-preview.php）で
 * 同一のサニタイズ規則を共有するための関数。update_option は行わない。
 *
 * @param array $opts 既存オプション。
 * @param array $src  wp_unslash 済みの入力配列（$_POST 由来）。
 * @return array 反映後のオプション。
 */
function emono_apply_design_from_post( $opts, $src ) {
    $src = is_array( $src ) ? $src : array();

    $design_mode_in = sanitize_key( isset( $src['design_mode'] ) ? $src['design_mode'] : 'dark' );
    $opts['design_mode'] = in_array( $design_mode_in, array( 'dark', 'light', 'auto' ), true ) ? $design_mode_in : 'dark';

    $opts['design_bg']     = sanitize_hex_color( isset( $src['design_bg'] )     ? $src['design_bg']     : '#000000' );
    $opts['design_text']   = sanitize_hex_color( isset( $src['design_text'] )   ? $src['design_text']   : '#ffffff' );
    $opts['design_accent'] = sanitize_hex_color( isset( $src['design_accent'] ) ? $src['design_accent'] : '#ffffff' );
    $opts['design_font']   = sanitize_text_field( isset( $src['design_font'] )  ? $src['design_font']  : 'System Mono' );

    return $opts;
}

/**
 * Branding / Site 設定（サイト名・ロゴ・トップボタン・トップレイアウト）を反映。
 * 保存とライブプレビューで共有。$src は unslash 済み配列。
 */
function emono_apply_general_from_post( $opts, $src ) {
    $src = is_array( $src ) ? $src : array();

    // top_layout フィールドが送られてきた場合のみ更新（未送信なら既存値を維持）。
    if ( isset( $src['top_layout'] ) ) {
        $top_layout  = sanitize_key( $src['top_layout'] );
        $top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array( 'mono' => array() );
        $opts['top_layout'] = isset( $top_layouts[ $top_layout ] ) ? $top_layout : 'mono';
    }

    $opts['site_name']    = sanitize_text_field( isset( $src['site_name'] ) ? $src['site_name'] : '' );
    $opts['site_tagline'] = sanitize_text_field( isset( $src['site_tagline'] ) ? $src['site_tagline'] : '' );
    $opts['copyright']    = sanitize_text_field( isset( $src['copyright'] ) ? $src['copyright'] : '' );
    $opts['logo_url']       = esc_url_raw( isset( $src['logo_url'] ) ? $src['logo_url'] : '' );
    $opts['logo_url_light'] = esc_url_raw( isset( $src['logo_url_light'] ) ? $src['logo_url_light'] : '' );

    $opts['top_logo_size']        = isset( $src['top_logo_size'] )        && (float) $src['top_logo_size'] > 0 ? (float) $src['top_logo_size'] : 14;
    $opts['top_logo_size_tablet'] = isset( $src['top_logo_size_tablet'] ) && (float) $src['top_logo_size_tablet'] > 0 ? (float) $src['top_logo_size_tablet'] : 0;
    $opts['top_logo_size_mobile'] = isset( $src['top_logo_size_mobile'] ) && (float) $src['top_logo_size_mobile'] > 0 ? (float) $src['top_logo_size_mobile'] : 0;
    $opts['header_logo_size']        = isset( $src['header_logo_size'] )        && (float) $src['header_logo_size'] > 0 ? (float) $src['header_logo_size'] : 3.2;
    $opts['header_logo_size_tablet'] = isset( $src['header_logo_size_tablet'] ) && (float) $src['header_logo_size_tablet'] > 0 ? (float) $src['header_logo_size_tablet'] : 0;
    $opts['header_logo_size_mobile'] = isset( $src['header_logo_size_mobile'] ) && (float) $src['header_logo_size_mobile'] > 0 ? (float) $src['header_logo_size_mobile'] : 0;

    $btn_labels  = isset( $src['top_btn_label'] ) && is_array( $src['top_btn_label'] ) ? $src['top_btn_label'] : array();
    $btn_urls    = isset( $src['top_btn_url'] ) && is_array( $src['top_btn_url'] ) ? $src['top_btn_url'] : array();
    $btn_manuals = isset( $src['top_btn_url_manual'] ) && is_array( $src['top_btn_url_manual'] ) ? $src['top_btn_url_manual'] : array();
    $top_buttons = array();
    foreach ( $btn_labels as $i => $label ) {
        $label = sanitize_text_field( $label );
        if ( ! $label ) continue;
        $sel = isset( $btn_urls[ $i ] ) ? $btn_urls[ $i ] : '';
        $url = $sel ? esc_url_raw( $sel ) : esc_url_raw( isset( $btn_manuals[ $i ] ) ? $btn_manuals[ $i ] : '' );
        $top_buttons[] = array( 'label' => $label, 'url' => $url );
    }
    $opts['top_buttons'] = $top_buttons;
    $opts = apply_filters( 'emono_save_top_layout_settings', $opts, $opts['top_layout'] );

    return $opts;
}

/**
 * ナビ（ヘッダー/フッターメニュー）を反映。保存とライブプレビューで共有。
 * $src は unslash 済み配列。
 */
function emono_apply_nav_from_post( $opts, $src ) {
    $src = is_array( $src ) ? $src : array();

    $opts['nav_mode']    = sanitize_key( isset( $src['nav_mode'] ) ? $src['nav_mode'] : 'auto' );
    $opts['nav_wp_menu'] = absint( isset( $src['nav_wp_menu'] ) ? $src['nav_wp_menu'] : 0 );

    $labels   = isset( $src['nav_label'] ) && is_array( $src['nav_label'] ) ? $src['nav_label'] : array();
    $types    = isset( $src['nav_type'] ) && is_array( $src['nav_type'] ) ? $src['nav_type'] : array();
    $urls     = isset( $src['nav_url'] ) && is_array( $src['nav_url'] ) ? $src['nav_url'] : array();
    $page_ids = isset( $src['nav_page_id'] ) && is_array( $src['nav_page_id'] ) ? $src['nav_page_id'] : array();
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

    $f_labels   = isset( $src['footer_nav_label'] ) && is_array( $src['footer_nav_label'] ) ? $src['footer_nav_label'] : array();
    $f_types    = isset( $src['footer_nav_type'] ) && is_array( $src['footer_nav_type'] ) ? $src['footer_nav_type'] : array();
    $f_urls     = isset( $src['footer_nav_url'] ) && is_array( $src['footer_nav_url'] ) ? $src['footer_nav_url'] : array();
    $f_page_ids = isset( $src['footer_nav_page_id'] ) && is_array( $src['footer_nav_page_id'] ) ? $src['footer_nav_page_id'] : array();
    $footer_nav = array();
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
    $opts['footer_nav_items'] = $footer_nav;

    return $opts;
}
