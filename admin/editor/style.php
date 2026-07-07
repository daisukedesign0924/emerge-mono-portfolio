<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_enqueue_scripts', 'emono_ed_admin_enqueue' );
function emono_ed_admin_enqueue( $hook ) {
    $ene_pages = array(
        'toplevel_page_ene-post',
        'toplevel_page_ene-works',
        'toplevel_page_ene-news',
        'toplevel_page_ene-page-create',
        'toplevel_page_ene-page-list',
        'admin_page_ene-page-edit',
    );
    if ( ! in_array( $hook, $ene_pages ) ) return;

    wp_enqueue_media();

    // JS変数をwp_localize_scriptで安全に渡す
    // src=false のダミー登録。localize の ENE 定義を admin_footer の本体JSより先に出すため、
    // in_footer は false（ヘッダー出力）にする。順序を変えると "ENE is not defined" になる。
    wp_register_script( 'ene-admin', false, array(), EMONO_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
    wp_enqueue_script( 'ene-admin' );
    wp_localize_script( 'ene-admin', 'ENE', array(
        'ajax_url'     => admin_url( 'admin-ajax.php' ),
        'nonce_post'   => wp_create_nonce( 'ene_post_nonce' ),
        'nonce_delete' => wp_create_nonce( 'ene_delete_nonce' ),
        'nonce_cat'    => wp_create_nonce( 'ene_cat_nonce' ),
        'nonce_page'   => wp_create_nonce( 'ene_page_nonce' ),
        'i18n'         => array(
            'selectImage'      => __( 'Select Image', 'emerge-mono-portfolio' ),
            'select'           => __( 'Select', 'emerge-mono-portfolio' ),
            'change'           => __( 'Change', 'emerge-mono-portfolio' ),
            'clickToSelect'    => __( 'Click to select an image', 'emerge-mono-portfolio' ),
            'selectGallery'    => __( 'Select Gallery Images', 'emerge-mono-portfolio' ),
            'add'              => __( 'Add', 'emerge-mono-portfolio' ),
            'image'            => __( 'Image', 'emerge-mono-portfolio' ),
            'enterTitle'       => __( 'Please enter a title.', 'emerge-mono-portfolio' ),
            'submitting'       => __( 'Submitting...', 'emerge-mono-portfolio' ),
            'failedSubmit'     => __( 'Failed to submit.', 'emerge-mono-portfolio' ),
            'confirmDeletePost'=> __( 'Delete this post? This cannot be undone.', 'emerge-mono-portfolio' ),
            'deleting'         => __( 'Deleting...', 'emerge-mono-portfolio' ),
            'deleted'          => __( 'Deleted.', 'emerge-mono-portfolio' ),
            'categoryAdded'    => __( 'Category added.', 'emerge-mono-portfolio' ),
            'confirmDeleteCat' => __( 'Delete this category?', 'emerge-mono-portfolio' ),
            'noCategoriesYet'  => __( 'No categories yet', 'emerge-mono-portfolio' ),
            'categoryDeleted'  => __( 'Category deleted.', 'emerge-mono-portfolio' ),
            'processing'       => __( 'Processing...', 'emerge-mono-portfolio' ),
        ),
    ));

    wp_enqueue_style( 'ene-admin-style', EMONO_URL . 'admin/editor/assets/editor.css', array(), EMONO_VERSION );
    wp_enqueue_script( 'ene-admin-main', EMONO_URL . 'admin/editor/assets/editor.js', array( 'ene-admin' ), EMONO_VERSION, true );
    wp_enqueue_script( 'ene-page-manager', EMONO_URL . 'admin/editor/assets/page-manager.js', array( 'ene-admin-main' ), EMONO_VERSION, true );
}
