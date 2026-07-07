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
            $opts['profile_name']   = sanitize_text_field( isset( $_POST['profile_name'] ) ? wp_unslash( $_POST['profile_name'] ) : '' );
            $opts['profile_role']   = sanitize_text_field( isset( $_POST['profile_role'] ) ? wp_unslash( $_POST['profile_role'] ) : '' );
            $opts['profile_bio']    = sanitize_textarea_field( isset( $_POST['profile_bio'] ) ? wp_unslash( $_POST['profile_bio'] ) : '' );
            $opts['profile_img']    = esc_url_raw( isset( $_POST['profile_img'] ) ? wp_unslash( $_POST['profile_img'] ) : '' );
            $opts['profile_skills'] = sanitize_text_field( isset( $_POST['profile_skills'] ) ? wp_unslash( $_POST['profile_skills'] ) : '' );

            $labels = isset( $_POST['sns_label'] ) ? wp_unslash( $_POST['sns_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $urls   = isset( $_POST['sns_url'] ) ? wp_unslash( $_POST['sns_url'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $icons  = isset( $_POST['sns_icon'] ) ? wp_unslash( $_POST['sns_icon'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
            $sns = array();
            if ( is_array( $labels ) ) {
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
            }
            $opts['sns_links'] = $sns;

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
