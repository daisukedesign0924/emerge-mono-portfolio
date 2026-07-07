<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Ajax: 投稿・更新 ──
add_action( 'wp_ajax_ene_save_post', 'emono_ed_handle_save_post' );
function emono_ed_handle_save_post() {
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ene_post_nonce') ) {
        wp_send_json_error( array('message' => __( 'Invalid request.', 'emerge-mono-portfolio' )) );
    }
    if ( ! current_user_can('edit_posts') ) {
        wp_send_json_error( array('message' => __( 'Permission denied.', 'emerge-mono-portfolio' )) );
    }

    $type    = sanitize_key( wp_unslash( $_POST['post_type'] ?? 'works' ) );
    $post_id = absint( $_POST['post_id'] ?? 0 );
    $title   = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $content = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
    $status  = sanitize_key( wp_unslash( $_POST['status'] ?? 'publish' ) );
    $thumb_id = absint( $_POST['thumb_id'] ?? 0 );

    if ( ! $title ) {
        wp_send_json_error( array('message' => __( 'Please enter a title.', 'emerge-mono-portfolio' )) );
    }

    $post_type = $type === 'works' ? 'en_work' : 'en_news';

    $post_data = array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $status,
        'post_type'    => $post_type,
    );

    if ( $post_id ) {
        $post_data['ID'] = $post_id;
        $result = wp_update_post( $post_data, true );
    } else {
        $result = wp_insert_post( $post_data, true );
    }

    if ( is_wp_error($result) ) {
        wp_send_json_error( array('message' => $result->get_error_message()) );
    }

    $saved_id = $result;

    // アイキャッチ画像
    if ( $thumb_id ) {
        set_post_thumbnail( $saved_id, $thumb_id );
    } else {
        delete_post_thumbnail( $saved_id );
    }

    // カテゴリー設定
    if ( $type === 'works' ) {
        $cat_ids = array_map( 'intval', (array) json_decode( wp_unslash( $_POST['work_cats'] ?? '[]' ), true ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON decoded, then each element is cast to int.
        wp_set_post_terms( $saved_id, $cat_ids, 'en_work_category' );

        // Worksカスタムフィールド
        $meta_fields = array(
            'en_video_url'    => sanitize_text_field(wp_unslash( $_POST['video_url'] ?? '' )),
            'en_external_url' => esc_url_raw(wp_unslash( $_POST['ext_url'] ?? '' )),
            'en_period'       => sanitize_text_field(wp_unslash( $_POST['period'] ?? '' )),
            'en_role'         => sanitize_text_field(wp_unslash( $_POST['role'] ?? '' )),
            'en_tools'        => sanitize_text_field(wp_unslash( $_POST['tools'] ?? '' )),
        );
        foreach ( $meta_fields as $key => $val ) {
            update_post_meta( $saved_id, $key, $val );
        }

        // ギャラリーアイテム（画像・動画）を保存
        $gallery_raw   = wp_unslash( $_POST['gallery_items'] ?? '[]' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string; each decoded element is sanitized in the loop below.
        $gallery_items = json_decode( $gallery_raw, true );
        if ( ! is_array($gallery_items) ) $gallery_items = array();
        $gallery_clean = array();
        foreach ( $gallery_items as $item ) {
            if ( isset($item['type']) && $item['type'] === 'video' && ! empty($item['url']) ) {
                $gallery_clean[] = array( 'type' => 'video', 'url' => esc_url_raw($item['url']) );
            } elseif ( isset($item['type']) && $item['type'] === 'image' && ! empty($item['id']) ) {
                $gallery_clean[] = array( 'type' => 'image', 'id' => (int)$item['id'] );
            }
        }
        update_post_meta( $saved_id, 'en_gallery_items', wp_json_encode($gallery_clean) );
    } else {
        $cat_ids = array_map( 'intval', (array) json_decode( wp_unslash( $_POST['news_cats'] ?? '[]' ), true ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON decoded, then each element is cast to int.
        wp_set_post_terms( $saved_id, $cat_ids, 'en_news_category' );
    }

    wp_send_json_success( array(
        'message' => $post_id ? __( 'Updated.', 'emerge-mono-portfolio' ) : __( 'Published.', 'emerge-mono-portfolio' ),
        'post_id' => $saved_id,
        'edit_url' => admin_url('admin.php?page=ene-post&type=' . $type . '&edit=' . $saved_id),
        'list_url' => admin_url( 'admin.php?page=' . ( $type === 'works' ? 'ene-works' : 'ene-news' ) ),
    ));
}

// ── Ajax: 削除 ──
add_action( 'wp_ajax_ene_delete_post', 'emono_ed_handle_delete_post' );
function emono_ed_handle_delete_post() {
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ene_delete_nonce') ) {
        wp_send_json_error( array('message' => __( 'Invalid request.', 'emerge-mono-portfolio' )) );
    }
    if ( ! current_user_can('delete_posts') ) {
        wp_send_json_error( array('message' => __( 'Permission denied.', 'emerge-mono-portfolio' )) );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id ) {
        wp_send_json_error( array('message' => __( 'Invalid ID.', 'emerge-mono-portfolio' )) );
    }

    $result = wp_delete_post( $post_id, true );
    if ( $result ) {
        wp_send_json_success( array('message' => __( 'Deleted.', 'emerge-mono-portfolio' )) );
    } else {
        wp_send_json_error( array('message' => __( 'Failed to delete.', 'emerge-mono-portfolio' )) );
    }
}

// ── Ajax: カテゴリー追加 ──
add_action( 'wp_ajax_ene_add_category', 'emono_ed_handle_add_category' );
function emono_ed_handle_add_category() {
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ene_cat_nonce') ) {
        wp_send_json_error( array('message' => __( 'Invalid request.', 'emerge-mono-portfolio' )) );
    }
    if ( ! current_user_can('manage_categories') ) {
        wp_send_json_error( array('message' => __( 'Permission denied.', 'emerge-mono-portfolio' )) );
    }
    $name     = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
    $taxonomy = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? 'category' ) );
    if ( ! $name ) {
        wp_send_json_error( array('message' => __( 'Please enter a category name.', 'emerge-mono-portfolio' )) );
    }
    $result = wp_insert_term( $name, $taxonomy );
    if ( is_wp_error($result) ) {
        wp_send_json_error( array('message' => $result->get_error_message()) );
    }
    wp_send_json_success( array(
        'term_id' => $result['term_id'],
        'name'    => $name,
    ));
}

// ── Ajax: カテゴリー削除 ──
add_action( 'wp_ajax_ene_delete_category', 'emono_ed_handle_delete_category' );
function emono_ed_handle_delete_category() {
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ene_cat_nonce') ) {
        wp_send_json_error( array('message' => __( 'Invalid request.', 'emerge-mono-portfolio' )) );
    }
    if ( ! current_user_can('manage_categories') ) {
        wp_send_json_error( array('message' => __( 'Permission denied.', 'emerge-mono-portfolio' )) );
    }
    $term_id  = absint( $_POST['term_id'] ?? 0 );
    $taxonomy = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? 'category' ) );
    if ( ! $term_id ) {
        wp_send_json_error( array('message' => __( 'Invalid ID.', 'emerge-mono-portfolio' )) );
    }
    $result = wp_delete_term( $term_id, $taxonomy );
    if ( is_wp_error($result) ) {
        wp_send_json_error( array('message' => $result->get_error_message()) );
    }
    wp_send_json_success( array('message' => __( 'Deleted.', 'emerge-mono-portfolio' )) );
}

// ── Ajax: ページ保存 ──
add_action( 'wp_ajax_ene_save_page', 'emono_ed_handle_save_page' );
function emono_ed_handle_save_page() {
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ene_page_nonce') ) {
        wp_send_json_error( array('message' => __( 'Invalid request.', 'emerge-mono-portfolio' )) );
    }
    if ( ! current_user_can('edit_pages') ) {
        wp_send_json_error( array('message' => __( 'Permission denied.', 'emerge-mono-portfolio' )) );
    }

    $post_id  = absint( $_POST['post_id'] ?? 0 );
    $title    = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $content  = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );
    $slug     = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
    $status   = sanitize_key( wp_unslash( $_POST['status'] ?? 'publish' ) );
    $thumb_id = absint( $_POST['thumb_id'] ?? 0 );

    if ( ! $title ) {
        wp_send_json_error( array('message' => __( 'Please enter a title.', 'emerge-mono-portfolio' )) );
    }

    $post_data = array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $status,
        'post_type'    => 'page',
    );
    if ( $slug ) $post_data['post_name'] = $slug;

    if ( $post_id ) {
        $post_data['ID'] = $post_id;
        $result = wp_update_post( $post_data, true );
    } else {
        $result = wp_insert_post( $post_data, true );
    }

    if ( is_wp_error($result) ) {
        wp_send_json_error( array('message' => $result->get_error_message()) );
    }

    if ( $thumb_id ) {
        set_post_thumbnail( $result, $thumb_id );
    } else {
        delete_post_thumbnail( $result );
    }

    wp_send_json_success( array(
        'message'  => $post_id ? 'Page updated.' : 'Page created.',
        'post_id'  => $result,
        'edit_url' => admin_url('admin.php?page=ene-page-create&edit=' . $result),
    ));
}
