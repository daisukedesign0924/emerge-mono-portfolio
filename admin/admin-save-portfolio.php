<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_save_portfolio_nonce_map() {
    return array(
        'profile' => 'en_save_profile',
        'sns'     => 'en_save_sns',
        'cpt'     => 'en_save_cpt',
    );
}

function emono_admin_save_portfolio_action( $action, $opts ) {
    $nonce_map = emono_admin_save_portfolio_nonce_map();
    if ( ! isset( $nonce_map[ $action ] ) ) {
        return array( 'handled' => false );
    }
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( isset( $_POST['en_nonce'] ) ? $_POST['en_nonce'] : '' ) ), $nonce_map[ $action ] ) ) {
        return array( 'handled' => false );
    }

    switch ( $action ) {
        case 'profile':
            $opts = emono_apply_profile_from_post( $opts, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verified above; each field sanitized in helper.

            return array(
                'handled'        => true,
                'opts'           => $opts,
                'tab'            => 'profile',
                'update_options' => true,
            );

        case 'sns':
            $labels = isset( $_POST['sns_label'] ) ? wp_unslash( $_POST['sns_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $urls   = isset( $_POST['sns_url'] ) ? wp_unslash( $_POST['sns_url'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $sns = array();
            foreach ( $labels as $i => $label ) {
                $url = esc_url_raw( isset( $urls[ $i ] ) ? $urls[ $i ] : '' );
                if ( $url ) {
                    $sns[] = array(
                        'label' => sanitize_text_field( $label ),
                        'url'   => $url,
                    );
                }
            }
            $opts['sns_links'] = $sns;

            return array(
                'handled'        => true,
                'opts'           => $opts,
                'tab'            => 'sns',
                'update_options' => true,
            );

        case 'cpt':
            $opts['work_label']    = sanitize_text_field( isset( $_POST['work_label'] )    ? wp_unslash( $_POST['work_label'] )    : 'Works' );
            $opts['work_singular'] = sanitize_text_field( isset( $_POST['work_singular'] ) ? wp_unslash( $_POST['work_singular'] ) : 'Work' );
            $opts['cat_label']     = sanitize_text_field( isset( $_POST['cat_label'] )     ? wp_unslash( $_POST['cat_label'] )     : __( 'Category', 'emerge-mono-portfolio' ) );
            $opts['work_icon']     = sanitize_text_field( isset( $_POST['work_icon'] )     ? wp_unslash( $_POST['work_icon'] )     : 'dashicons-portfolio' );
            update_option( 'en_options', $opts );
            flush_rewrite_rules();

            return array(
                'handled'        => true,
                'opts'           => $opts,
                'tab'            => 'cpt',
                'update_options' => false,
            );
    }

    return array( 'handled' => false );
}

/**
 * プロフィール（名前・肩書き・自己紹介・画像・スキル・SNS）を反映。
 * 保存とライブプレビューで共有。$src は unslash 済み配列。
 */
function emono_apply_profile_from_post( $opts, $src ) {
    $src = is_array( $src ) ? $src : array();

    $opts['profile_name']   = sanitize_text_field( isset( $src['profile_name'] ) ? $src['profile_name'] : '' );
    $opts['profile_role']   = sanitize_text_field( isset( $src['profile_role'] ) ? $src['profile_role'] : '' );
    $opts['profile_bio']    = sanitize_textarea_field( isset( $src['profile_bio'] ) ? $src['profile_bio'] : '' );
    $opts['profile_img']    = esc_url_raw( isset( $src['profile_img'] ) ? $src['profile_img'] : '' );
    $opts['profile_skills'] = sanitize_text_field( isset( $src['profile_skills'] ) ? $src['profile_skills'] : '' );

    $labels = isset( $src['sns_label'] ) && is_array( $src['sns_label'] ) ? $src['sns_label'] : array();
    $urls   = isset( $src['sns_url'] ) && is_array( $src['sns_url'] ) ? $src['sns_url'] : array();
    $icons  = isset( $src['sns_icon'] ) && is_array( $src['sns_icon'] ) ? $src['sns_icon'] : array();
    $sns = array();
    foreach ( $labels as $i => $label ) {
        $url = esc_url_raw( isset( $urls[ $i ] ) ? $urls[ $i ] : '' );
        if ( $url ) {
            $sns[] = array(
                'label' => sanitize_text_field( $label ),
                'url'   => $url,
                'icon'  => esc_url_raw( isset( $icons[ $i ] ) ? $icons[ $i ] : '' ),
            );
        }
    }
    $opts['sns_links'] = $sns;

    return $opts;
}
