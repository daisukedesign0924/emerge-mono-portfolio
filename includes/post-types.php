<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'en_register_work_post_type' );
function en_register_work_post_type() {
    $opts     = en_get_options();
    $label    = isset($opts['work_label'])    ? $opts['work_label']    : 'Works';
    $singular = isset($opts['work_singular']) ? $opts['work_singular'] : 'Work';
    $cat_lbl  = isset($opts['cat_label'])     ? $opts['cat_label']     : __( 'Category', 'emerge-mono-portfolio' );
    $icon     = isset($opts['work_icon'])     ? $opts['work_icon']     : 'dashicons-portfolio';

    register_post_type( 'en_work', array(
        'labels' => array(
            'name'               => $label,
            'singular_name'      => $singular,
            'add_new'            => __( 'Add New', 'emerge-mono-portfolio' ),
            'add_new_item'       => sprintf( __( 'Add %s', 'emerge-mono-portfolio' ), $singular ),
            'edit_item'          => sprintf( __( 'Edit %s', 'emerge-mono-portfolio' ), $singular ),
            'new_item'           => sprintf( __( 'New %s', 'emerge-mono-portfolio' ), $singular ),
            'view_item'          => sprintf( __( 'View %s', 'emerge-mono-portfolio' ), $singular ),
            'search_items'       => sprintf( __( 'Search %s', 'emerge-mono-portfolio' ), $label ),
            'not_found'          => sprintf( __( 'No %s found', 'emerge-mono-portfolio' ), $label ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'emerge-mono-portfolio' ), $label ),
        ),
        'public'       => true,
        'has_archive'  => false,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'taxonomies'   => array( 'en_work_category' ),
        'rewrite'      => array( 'slug' => 'works' ),
        'menu_icon'    => $icon,
    ));

    register_taxonomy( 'en_work_category', 'en_work', array(
        'labels' => array(
            'name'          => $cat_lbl,
            'singular_name' => $cat_lbl,
            'add_new_item'  => sprintf( __( 'Add %s', 'emerge-mono-portfolio' ), $cat_lbl ),
            'edit_item'     => sprintf( __( 'Edit %s', 'emerge-mono-portfolio' ), $cat_lbl ),
            'new_item_name' => sprintf( __( 'New %s Name', 'emerge-mono-portfolio' ), $cat_lbl ),
            'menu_name'     => $cat_lbl,
        ),
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
    ));
}

// ── News（en_news）カスタム投稿タイプ。Worksと対称的な構造 ──
add_action( 'init', 'en_register_news_post_type' );
function en_register_news_post_type() {
    $opts     = en_get_options();
    $label    = isset($opts['news_label'])    ? $opts['news_label']    : 'News';
    $singular = isset($opts['news_singular']) ? $opts['news_singular'] : 'Post';
    $cat_lbl  = isset($opts['news_cat_label'])? $opts['news_cat_label']: __( 'Category', 'emerge-mono-portfolio' );
    $icon     = isset($opts['news_icon'])     ? $opts['news_icon']     : 'dashicons-megaphone';

    register_post_type( 'en_news', array(
        'labels' => array(
            'name'               => $label,
            'singular_name'      => $singular,
            'add_new'            => __( 'Add New', 'emerge-mono-portfolio' ),
            'add_new_item'       => sprintf( __( 'Add %s', 'emerge-mono-portfolio' ), $singular ),
            'edit_item'          => sprintf( __( 'Edit %s', 'emerge-mono-portfolio' ), $singular ),
            'new_item'           => sprintf( __( 'New %s', 'emerge-mono-portfolio' ), $singular ),
            'view_item'          => sprintf( __( 'View %s', 'emerge-mono-portfolio' ), $singular ),
            'search_items'       => sprintf( __( 'Search %s', 'emerge-mono-portfolio' ), $label ),
            'not_found'          => sprintf( __( 'No %s found', 'emerge-mono-portfolio' ), $label ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'emerge-mono-portfolio' ), $label ),
        ),
        'public'       => true,
        'has_archive'  => false,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'taxonomies'   => array( 'en_news_category' ),
        'rewrite'      => array( 'slug' => 'news' ),
        'menu_icon'    => $icon,
    ));

    register_taxonomy( 'en_news_category', 'en_news', array(
        'labels' => array(
            'name'          => $cat_lbl,
            'singular_name' => $cat_lbl,
            'add_new_item'  => sprintf( __( 'Add %s', 'emerge-mono-portfolio' ), $cat_lbl ),
            'edit_item'     => sprintf( __( 'Edit %s', 'emerge-mono-portfolio' ), $cat_lbl ),
            'new_item_name' => sprintf( __( 'New %s Name', 'emerge-mono-portfolio' ), $cat_lbl ),
            'menu_name'     => $cat_lbl,
        ),
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
    ));
}

add_action( 'rest_api_init', function() {
    register_rest_field( 'en_work', 'en_categories', array(
        'get_callback' => function( $post ) {
            $terms = get_the_terms( $post['id'], 'en_work_category' );
            if ( ! $terms || is_wp_error( $terms ) ) return array();
            return array_map( function( $t ) {
                return array( 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug );
            }, $terms );
        },
    ));
    register_rest_field( 'en_news', 'en_categories', array(
        'get_callback' => function( $post ) {
            $terms = get_the_terms( $post['id'], 'en_news_category' );
            if ( ! $terms || is_wp_error( $terms ) ) return array();
            return array_map( function( $t ) {
                return array( 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug );
            }, $terms );
        },
    ));
});

// ── en_work / en_news 単一ページが同名slugのpostへ誤リダイレクトされるのを防ぐ ──
// WordPressのredirect_canonicalは、CPT(/works/slug/ や /news/slug/)と通常post(/slug/)が
// 同じslugを持つ場合、誤って通常post側へ301リダイレクトしてしまう。
// CPTの単一ページ表示時はこの正規化リダイレクトを無効化する。
add_filter( 'redirect_canonical', 'en_prevent_work_canonical_redirect', 10, 2 );
function en_prevent_work_canonical_redirect( $redirect_url, $requested_url ) {
    if ( is_singular( 'en_work' ) || is_singular( 'en_news' ) ) {
        return false;
    }
    return $redirect_url;
}
