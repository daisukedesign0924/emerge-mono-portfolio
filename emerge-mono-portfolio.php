<?php
/**
 * Plugin Name:       Emerge Mono - Portfolio
 * Plugin URI:        https://github.com/daisukedesign0924/emerge-mono-portfolio
 * Description:       A monochrome portfolio toolkit for creators. Build a full portfolio site with shortcodes: hero, works gallery, profile, news, contact form, estimate simulator, and auto-generated privacy policy / terms pages.
 * Version:           2.21.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            DAISUKE DESIGN
 * Author URI:        https://daisuke-design.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       emerge-mono-portfolio
 * Domain Path:       /languages
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'EN_VERSION', '2.21.0' );
define( 'EN_PATH', plugin_dir_path( __FILE__ ) );
define( 'EN_URL',  plugin_dir_url( __FILE__ ) );

// テーマ紹介ページのURL。公式テーマページが用意できたらここを差し替える。
// '#' のときはボタンは表示されるがクリックしても遷移しない（準備中）。
define( 'EN_THEME_URL', '#' );

add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'emerge-mono-portfolio', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}, 1 );

require_once EN_PATH . 'includes/post-types.php';
require_once EN_PATH . 'includes/custom-fields.php';
require_once EN_PATH . 'includes/shortcodes.php';
require_once EN_PATH . 'includes/nav.php';
require_once EN_PATH . 'includes/contact.php';
require_once EN_PATH . 'includes/privacy/privacy.php';
require_once EN_PATH . 'includes/terms/terms.php';
require_once EN_PATH . 'includes/estimate/estimate.php';
require_once EN_PATH . 'admin/admin.php';
// ── エディター機能 ──
add_action( 'plugins_loaded', 'en_load_editor' );
function en_load_editor() {
    $opts    = get_option( 'en_options', array() );
    $enabled = isset($opts['editor_enabled']) ? $opts['editor_enabled'] : '1';
    if ( $enabled !== '1' ) return;

    require_once EN_PATH . 'admin/editor/api.php';
    require_once EN_PATH . 'admin/editor/style.php';
    require_once EN_PATH . 'admin/editor/page.php';

    add_action( 'admin_menu', 'en_editor_menus', 20 );
    add_action( 'admin_menu', 'en_editor_hide_menus', 999 );
    add_action( 'admin_bar_menu', 'en_editor_hide_bar', 999 );
}

function en_editor_menus() {
    add_menu_page( __( 'Post', 'emerge-mono-portfolio' ),     __( 'Post', 'emerge-mono-portfolio' ),     'edit_posts',  'ene-post',        'ene_page_post',   'dashicons-edit-page',  32 );
    add_menu_page( __( 'All Works', 'emerge-mono-portfolio' ),   __( 'All Works', 'emerge-mono-portfolio' ),    'edit_posts',  'ene-works',       'ene_page_works',  'dashicons-portfolio',  33 );
    add_menu_page( __( 'All News', 'emerge-mono-portfolio' ), __( 'All News', 'emerge-mono-portfolio' ), 'edit_posts',  'ene-news',        'ene_page_news',   'dashicons-megaphone',  34 );
    add_menu_page( __( 'Page Manager', 'emerge-mono-portfolio' ),   __( 'Page Manager', 'emerge-mono-portfolio' ),   'edit_pages',  'ene-page-create', 'ene_page_create', 'dashicons-admin-page', 35 );
    add_submenu_page( null, __( 'Edit Page', 'emerge-mono-portfolio' ), __( 'Edit Page', 'emerge-mono-portfolio' ), 'edit_pages', 'ene-page-edit', 'ene_page_edit' );
}

function en_editor_hide_menus() {
    remove_menu_page( 'edit.php?post_type=en_work' );
    remove_menu_page( 'edit.php?post_type=en_news' );
    remove_menu_page( 'edit.php?post_type=page' );
    // 標準投稿メニューは設定で表示する場合は消さない
    if ( ! en_opt( 'show_default_posts', '0' ) ) {
        remove_menu_page( 'edit.php' );
    }
}

function en_editor_hide_bar( $bar ) {
    $bar->remove_node( 'new-post' );
    $bar->remove_node( 'new-en_work' );
}

add_action( 'wp_enqueue_scripts', 'en_enqueue_assets' );
function en_enqueue_assets() {
    wp_enqueue_style( 'emerge-mono-portfolio', EN_URL . 'assets/css/en-style.css', array(), EN_VERSION );
    wp_enqueue_script( 'emerge-mono-portfolio', EN_URL . 'assets/js/en-script.js', array(), EN_VERSION, true );
    wp_localize_script( 'emerge-mono-portfolio', 'EN', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'rest_url' => rest_url( 'wp/v2/' ),
        'nonce'    => wp_create_nonce( 'en_nonce' ),
        'options'  => en_get_options(),
        'i18n'     => array(
            'sendFailed' => __( 'Failed to send.', 'emerge-mono-portfolio' ),
        ),
    ));
}

function en_get_options() {
    return get_option( 'en_options', array() );
}
function en_opt( $key, $default = '' ) {
    $opts = en_get_options();
    return isset( $opts[$key] ) ? $opts[$key] : $default;
}

add_action( 'admin_menu', function() {
    // 標準投稿（ブログ）メニューはデフォルトで非表示。
    // 設定「show_default_posts」がONのときだけ表示する。
    if ( ! en_opt( 'show_default_posts', '0' ) ) {
        remove_menu_page( 'edit.php' );
    }
}, 999 );

register_activation_hook( __FILE__, 'en_activate' );
function en_activate() {
    if ( ! get_option( 'en_options' ) ) {
        update_option( 'en_options', array(
            'site_name'    => get_bloginfo('name'),
            'site_tagline' => 'Web Creator',
            'copyright'    => '(c) ' . date('Y') . ' ' . get_bloginfo('name'),
        ));
    }
    // セットアップウィザードを初回のみ表示するためのフラグ
    set_transient( 'en_show_setup_wizard', 1, 60 );

    // ★リライトルールを正しく再生成するため、フラッシュ前にCPTを登録しておく。
    //   有効化フックは init より前に走るため、ここで明示登録しないと
    //   /works/{slug}/ や /news/{slug}/ のルールが生成されず、ページが404になる。
    if ( function_exists( 'en_register_work_post_type' ) ) {
        en_register_work_post_type();
    }
    if ( function_exists( 'en_register_news_post_type' ) ) {
        en_register_news_post_type();
    }
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, function() { flush_rewrite_rules(); });

// ── SVGアップロードを許可 ──
add_filter( 'upload_mimes', 'en_allow_svg' );
function en_allow_svg( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'en_fix_svg_filetype', 10, 4 );
function en_fix_svg_filetype( $data, $file, $filename, $mimes ) {
    $filetype = wp_check_filetype( $filename, $mimes );
    return array(
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename'],
    );
}

// メディアライブラリでSVGをプレビュー表示
add_action( 'admin_head', 'en_svg_media_preview' );
function en_svg_media_preview() {
    echo '<style>
    .attachment-preview .thumbnail img[src$=".svg"],
    img[src$=".svg"].attachment-thumb { width:100%; height:auto; }
    </style>';
}

// フォントファイルのアップロードを許可（カスタムフォント機能用）
add_filter( 'upload_mimes', 'en_allow_fonts' );
function en_allow_fonts( $mimes ) {
    $mimes['woff']  = 'font/woff';
    $mimes['woff2'] = 'font/woff2';
    $mimes['ttf']   = 'font/ttf';
    $mimes['otf']   = 'font/otf';
    return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'en_fix_font_filetype', 10, 4 );
function en_fix_font_filetype( $data, $file, $filename, $mimes ) {
    if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
        return $data;
    }
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
    $font_types = array(
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
    );
    if ( isset( $font_types[ $ext ] ) ) {
        $data['ext']  = $ext;
        $data['type'] = $font_types[ $ext ];
    }
    return $data;
}

// ── デザイン設定をCSS変数として出力 ──
add_action( 'wp_head', 'en_output_design_vars' );

/**
 * フォント定義の一元管理
 * 'google' => Google Fontsから読み込むフォント（値はfallback種別）
 * 'system' => システムフォント（外部読み込み不要・font-family文字列）
 */
function en_font_registry() {
    return array(
        // key => array( 'type' => google|system, 'stack' => CSS font-family )
        'Space Mono'          => array( 'type' => 'google', 'stack' => "'Space Mono', monospace" ),
        'Inter'               => array( 'type' => 'google', 'stack' => "'Inter', sans-serif" ),
        'DM Sans'             => array( 'type' => 'google', 'stack' => "'DM Sans', sans-serif" ),
        'Outfit'              => array( 'type' => 'google', 'stack' => "'Outfit', sans-serif" ),
        'Syne'                => array( 'type' => 'google', 'stack' => "'Syne', sans-serif" ),
        'Josefin Sans'        => array( 'type' => 'google', 'stack' => "'Josefin Sans', sans-serif" ),
        'Bebas Neue'          => array( 'type' => 'google', 'stack' => "'Bebas Neue', sans-serif" ),
        'Noto Sans JP'        => array( 'type' => 'google', 'stack' => "'Noto Sans JP', sans-serif" ),
        'M PLUS 1p'           => array( 'type' => 'google', 'stack' => "'M PLUS 1p', sans-serif" ),
        'Zen Kaku Gothic New' => array( 'type' => 'google', 'stack' => "'Zen Kaku Gothic New', sans-serif" ),
        // システムフォント（WP標準・外部読み込みなし）
        'System Sans'         => array( 'type' => 'system', 'stack' => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif" ),
        'System Serif'        => array( 'type' => 'system', 'stack' => "Georgia, 'Times New Roman', 'YuMincho', 'Hiragino Mincho ProN', 'MS PMincho', serif" ),
        'System Mono'         => array( 'type' => 'system', 'stack' => "ui-monospace, SFMono-Regular, Menlo, Consolas, 'Courier New', monospace" ),
    );
}

/**
 * カスタムフォント一覧を取得（複数対応）
 * 戻り値: array( array('name'=>..., 'url'=>...), ... )
 */
function en_get_custom_fonts() {
    $fonts = en_opt( 'en_custom_fonts', array() );
    if ( ! is_array( $fonts ) ) return array();
    // 旧データ（単数版）からの移行
    $legacy_name = en_opt( 'design_custom_font_name', '' );
    $legacy_url  = en_opt( 'design_custom_font_url', '' );
    if ( $legacy_name && $legacy_url ) {
        $exists = false;
        foreach ( $fonts as $f ) {
            if ( isset($f['name']) && $f['name'] === $legacy_name ) { $exists = true; break; }
        }
        if ( ! $exists ) {
            $fonts[] = array( 'name' => $legacy_name, 'url' => $legacy_url );
        }
    }
    return $fonts;
}

/** 指定名のカスタムフォントURLを返す（なければ空文字） */
function en_custom_font_url( $name ) {
    foreach ( en_get_custom_fonts() as $f ) {
        if ( isset($f['name']) && $f['name'] === $name ) {
            return isset($f['url']) ? $f['url'] : '';
        }
    }
    return '';
}

/** 選択フォントのCSS font-family文字列を返す */
function en_font_stack( $font_key ) {
    $reg = en_font_registry();
    if ( isset( $reg[ $font_key ] ) ) {
        return $reg[ $font_key ]['stack'];
    }
    // カスタムフォント（プラグインでアップロードしたフォント）
    if ( en_custom_font_url( $font_key ) ) {
        return "'" . $font_key . "', sans-serif";
    }
    // 未知の値はそのまま使い、monospaceでfallback
    return "'" . $font_key . "', monospace";
}

/** 選択フォントがGoogle Fontsかどうか */
function en_font_is_google( $font_key ) {
    $reg = en_font_registry();
    return isset( $reg[ $font_key ] ) && $reg[ $font_key ]['type'] === 'google';
}

/** Ajax: カスタムフォントを追加 */
add_action( 'wp_ajax_en_add_custom_font', 'en_ajax_add_custom_font' );
function en_ajax_add_custom_font() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono-portfolio' ) );
    }
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field($_POST['nonce']), 'en_custom_font_nonce' ) ) {
        wp_send_json_error( __( 'Invalid request', 'emerge-mono-portfolio' ) );
    }
    $name = isset($_POST['name']) ? sanitize_text_field( wp_unslash($_POST['name']) ) : '';
    $url  = isset($_POST['url'])  ? esc_url_raw( wp_unslash($_POST['url']) )  : '';
    if ( ! $name || ! $url ) {
        wp_send_json_error( __( 'Please enter a font name and select a file.', 'emerge-mono-portfolio' ) );
    }

    $opts = get_option( 'en_options', array() );
    if ( ! isset($opts['en_custom_fonts']) || ! is_array($opts['en_custom_fonts']) ) {
        $opts['en_custom_fonts'] = array();
    }
    // 同名は上書き
    $found = false;
    foreach ( $opts['en_custom_fonts'] as &$f ) {
        if ( isset($f['name']) && $f['name'] === $name ) {
            $f['url'] = $url; $found = true; break;
        }
    }
    unset($f);
    if ( ! $found ) {
        $opts['en_custom_fonts'][] = array( 'name' => $name, 'url' => $url );
    }
    update_option( 'en_options', $opts );

    wp_send_json_success( array(
        'fonts' => $opts['en_custom_fonts'],
    ) );
}

/** Ajax: カスタムフォントを削除 */
add_action( 'wp_ajax_en_delete_custom_font', 'en_ajax_delete_custom_font' );
function en_ajax_delete_custom_font() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono-portfolio' ) );
    }
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field($_POST['nonce']), 'en_custom_font_nonce' ) ) {
        wp_send_json_error( __( 'Invalid request', 'emerge-mono-portfolio' ) );
    }
    $name = isset($_POST['name']) ? sanitize_text_field( wp_unslash($_POST['name']) ) : '';

    $opts = get_option( 'en_options', array() );
    if ( isset($opts['en_custom_fonts']) && is_array($opts['en_custom_fonts']) ) {
        $opts['en_custom_fonts'] = array_values( array_filter( $opts['en_custom_fonts'], function($f) use ($name) {
            return ! ( isset($f['name']) && $f['name'] === $name );
        } ) );
        update_option( 'en_options', $opts );
    }

    wp_send_json_success( array(
        'fonts' => isset($opts['en_custom_fonts']) ? $opts['en_custom_fonts'] : array(),
    ) );
}

function en_output_design_vars() {
    $bg     = en_opt('design_bg',    '#000000');
    $text   = en_opt('design_text',  '#ffffff');
    $accent = en_opt('design_accent','#ffffff');
    $font   = en_opt('design_font',  'Space Mono');
    $mode   = en_opt('design_mode',  'dark');

    // Google Fontsの場合のみ外部読み込み（システムフォントは読み込まない）
    if ( en_font_is_google( $font ) ) {
        $font_url = 'https://fonts.googleapis.com/css2?family=' . urlencode($font) . ':wght@400;700&display=swap';
        echo '<link rel="stylesheet" href="' . esc_url($font_url) . '">' . "\n";
    } else {
        // カスタムフォント（プラグインでアップロード）なら @font-face を出力
        $custom_url = en_custom_font_url( $font );
        if ( $custom_url ) {
            echo '<style id="en-custom-font-face">';
            echo "@font-face{font-family:'" . esc_attr( $font ) . "';font-weight:400 700;font-style:normal;font-display:swap;src:url('" . esc_url( $custom_url ) . "');}";
            echo '</style>' . "\n";
        }
    }

    $font_stack = en_font_stack( $font );
    $a = esc_attr($accent);

    if ( $mode === 'auto' ) {
        // auto: ダーク基準 + メディアクエリでライスト上書きはCSSファイル側で処理
        // アクセントカラーだけPHPで制御
        $a_light = ( $accent === '#ffffff' ) ? '#000000' : $a;
        echo "<style id=\"en-design-vars\">
:root {
    --en-font: {$font_stack};
    --en-accent: {$a};
}
@media (prefers-color-scheme: light) {
    body.en-mode-auto { --en-accent: {$a_light}; }
}
</style>\n";
    } elseif ( $mode === 'light' ) {
        $a_light = ( $accent === '#ffffff' ) ? '#000000' : $a;
        echo "<style id=\"en-design-vars\">
:root {
    --en-font: {$font_stack};
    --en-accent: {$a_light};
}
</style>\n";
    } else {
        // dark固定 or カスタムカラー
        $t = esc_attr($text);
        $b = esc_attr($bg);
        echo "<style id=\"en-design-vars\">
:root {
    --en-bg:     {$b};
    --en-text:   {$t};
    --en-accent: {$a};
    --en-muted:  {$t}40;
    --en-border: {$t}20;
    --en-font:   {$font_stack};
}
</style>\n";
    }
}

// ── ロゴサイズ（ブレイクポイント別）をCSS変数として出力 ──
add_action( 'wp_head', 'en_output_logo_size_vars', 11 );
function en_output_logo_size_vars() {
    // トップページロゴ（vw）。desktop / tablet(<=768px) / mobile(<=480px)
    $top_d = (float) en_opt( 'top_logo_size',        14 );
    $top_t = (float) en_opt( 'top_logo_size_tablet', 0 );
    $top_m = (float) en_opt( 'top_logo_size_mobile', 0 );
    // 未設定(0)は上位ブレイクポイントの値を継承
    if ( $top_d <= 0 ) $top_d = 14;
    if ( $top_t <= 0 ) $top_t = $top_d;
    if ( $top_m <= 0 ) $top_m = $top_t;

    // ヘッダーロゴ（vw）。従来CSSの 3.2vw をデフォルトに
    $hdr_d = (float) en_opt( 'header_logo_size',        3.2 );
    $hdr_t = (float) en_opt( 'header_logo_size_tablet', 0 );
    $hdr_m = (float) en_opt( 'header_logo_size_mobile', 0 );
    if ( $hdr_d <= 0 ) $hdr_d = 3.2;
    if ( $hdr_t <= 0 ) $hdr_t = $hdr_d;
    if ( $hdr_m <= 0 ) $hdr_m = $hdr_t;

    echo "<style id=\"en-logo-size-vars\">\n";
    echo ":root { --en-top-logo: {$top_d}vw; --en-header-logo: {$hdr_d}vw; }\n";
    echo "@media (max-width: 768px) { :root { --en-top-logo: {$top_t}vw; --en-header-logo: {$hdr_t}vw; } }\n";
    echo "@media (max-width: 480px) { :root { --en-top-logo: {$top_m}vw; --en-header-logo: {$hdr_m}vw; } }\n";
    echo "</style>\n";
}

// ── bodyにカラーモードクラスを付与 ──
add_filter( 'body_class', 'en_add_mode_body_class' );
function en_add_mode_body_class( $classes ) {
    $mode = en_opt( 'design_mode', 'dark' );
    if ( $mode === 'light' ) {
        $classes[] = 'en-mode-light';
    } elseif ( $mode === 'auto' ) {
        $classes[] = 'en-mode-auto';
    } else {
        $classes[] = 'en-mode-dark';
    }
    return $classes;
}

// ── en_work 詳細ページのテンプレートをプラグインから提供 ──
add_filter( 'template_include', 'en_single_work_template' );
function en_single_work_template( $template ) {
    if ( is_singular( 'en_work' ) ) {
        return EN_PATH . 'includes/single-work.php';
    }
    return $template;
}

// ── en_news（News記事）詳細ページのテンプレートをプラグインから提供 ──
add_filter( 'template_include', 'en_single_post_template' );
function en_single_post_template( $template ) {
    if ( is_singular( 'en_news' ) ) {
        return EN_PATH . 'includes/single-post.php';
    }
    return $template;
}
