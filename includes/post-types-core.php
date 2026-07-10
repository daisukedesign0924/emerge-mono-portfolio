<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── News（en_news）カスタム投稿タイプ。Worksと対称的な構造 ──
add_action( 'init', 'emono_register_news_post_type' );
function emono_register_news_post_type() {
    $opts     = emono_get_options();
    $label    = isset($opts['news_label'])    ? $opts['news_label']    : 'News';
    $singular = isset($opts['news_singular']) ? $opts['news_singular'] : 'Post';
    $cat_lbl  = isset($opts['news_cat_label'])? $opts['news_cat_label']: __( 'Category', 'emerge-mono' );
    $icon     = isset($opts['news_icon'])     ? $opts['news_icon']     : 'dashicons-megaphone';

    register_post_type( 'en_news', array(
        'labels' => array(
            'name'               => $label,
            'singular_name'      => $singular,
            'add_new'            => __( 'Add New', 'emerge-mono' ),
            /* translators: %s: the post type or taxonomy label */
            'add_new_item'       => sprintf( __( 'Add %s', 'emerge-mono' ), $singular ),
            /* translators: %s: the post type or taxonomy label */
            'edit_item'          => sprintf( __( 'Edit %s', 'emerge-mono' ), $singular ),
            /* translators: %s: the post type or taxonomy label */
            'new_item'           => sprintf( __( 'New %s', 'emerge-mono' ), $singular ),
            /* translators: %s: the post type or taxonomy label */
            'view_item'          => sprintf( __( 'View %s', 'emerge-mono' ), $singular ),
            /* translators: %s: the post type or taxonomy label */
            'search_items'       => sprintf( __( 'Search %s', 'emerge-mono' ), $label ),
            /* translators: %s: the post type or taxonomy label */
            'not_found'          => sprintf( __( 'No %s found', 'emerge-mono' ), $label ),
            /* translators: %s: the post type or taxonomy label */
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'emerge-mono' ), $label ),
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
            /* translators: %s: the post type or taxonomy label */
            'add_new_item'  => sprintf( __( 'Add %s', 'emerge-mono' ), $cat_lbl ),
            /* translators: %s: the post type or taxonomy label */
            'edit_item'     => sprintf( __( 'Edit %s', 'emerge-mono' ), $cat_lbl ),
            /* translators: %s: the post type or taxonomy label */
            'new_item_name' => sprintf( __( 'New %s Name', 'emerge-mono' ), $cat_lbl ),
            'menu_name'     => $cat_lbl,
        ),
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
    ));
}

add_action( 'rest_api_init', function() {
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

// ── en_news 単一ページが同名slugのpostへ誤リダイレクトされるのを防ぐ ──
// WordPressのredirect_canonicalは、CPT(/news/slug/)と通常post(/slug/)が
// 同じslugを持つ場合、誤って通常post側へ301リダイレクトしてしまう。
// CPTの単一ページ表示時はこの正規化リダイレクトを無効化する。
add_filter( 'redirect_canonical', 'emono_prevent_news_canonical_redirect', 10, 2 );
function emono_prevent_news_canonical_redirect( $redirect_url, $requested_url ) {
    if ( is_singular( 'en_news' ) ) {
        return false;
    }
    return $redirect_url;
}
