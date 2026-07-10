<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'emono_save_estimate' );
function emono_save_estimate() {
    if (
        ! isset( $_POST['en_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['en_nonce'] ?? '' ) ), 'emono_save_estimate' ) ||
        ! current_user_can( 'manage_options' )
    ) return;

    if ( ! isset( $_POST['en_estimate_save'] ) ) return;

    // サービス一覧を保存
    $services = array();
    $names    = wp_unslash( $_POST['service_name'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
    $descs    = wp_unslash( $_POST['service_desc'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
    $items_all = wp_unslash( $_POST['service_items'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.

    foreach ( $names as $i => $name ) {
        $name = sanitize_text_field($name);
        if ( ! $name ) continue;

        $items = array();
        $raw_items = isset($items_all[$i]) ? $items_all[$i] : array();
        foreach ( $raw_items as $item ) {
            $item_type  = sanitize_key( $item['type'] ?? 'sel' );
            $item_label = sanitize_text_field( $item['label'] ?? '' );
            if ( ! $item_label ) continue;

            if ( $item_type === 'qty' ) {
                $items[] = array(
                    'type'  => 'qty',
                    'label' => $item_label,
                    'unit'  => (int)( $item['unit'] ?? 0 ),
                    'min'   => (int)( $item['min']  ?? 0 ),
                );
            } else {
                $opts = array();
                foreach ( ($item['opts'] ?? array()) as $opt ) {
                    $ol = sanitize_text_field($opt['label'] ?? '');
                    $ov = (int)($opt['value'] ?? 0);
                    if ($ol !== '') $opts[] = array('label'=>$ol,'value'=>$ov);
                }
                $items[] = array(
                    'type'     => $item_type,
                    'label'    => $item_label,
                    'multiple' => isset($item['multiple']) ? 1 : 0,
                    'opts'     => $opts,
                );
            }
        }

        $services[] = array(
            'name'  => $name,
            'desc'  => sanitize_text_field($descs[$i] ?? ''),
            'items' => $items,
        );
    }

    // 依頼方法を保存
    $payment_methods = array();
    $pm_names = wp_unslash( $_POST['pm_name'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
    $pm_fees  = wp_unslash( $_POST['pm_fee'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array elements are individually sanitized in the loop below.
    foreach ( $pm_names as $i => $pm_name ) {
        $pm_name = sanitize_text_field($pm_name);
        if ( ! $pm_name ) continue;
        $payment_methods[] = array(
            'name' => $pm_name,
            'fee'  => (float)($pm_fees[$i] ?? 0),
        );
    }

    // 全体設定
    $note_text = sanitize_text_field( wp_unslash( $_POST['estimate_note_text'] ?? __( '* The final amount will be provided after inquiry.', 'emerge-mono' ) ) );
    $btn_text  = sanitize_text_field( wp_unslash( $_POST['estimate_btn_text'] ?? __( 'Send inquiry with these details', 'emerge-mono' ) ) );

    update_option( 'en_estimate_services',       $services );
    update_option( 'en_estimate_payment_methods', $payment_methods );
    update_option( 'en_estimate_settings', array(
        'note_text' => $note_text,
        'btn_text'  => $btn_text,
    ));

    wp_safe_redirect( admin_url('admin.php?page=emerge-mono&tab=estimate&saved=1') );
    exit;
}
