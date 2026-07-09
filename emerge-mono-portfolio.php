<?php
/**
 * Plugin Name:       Emerge Mono - Portfolio
 * Plugin URI:        https://github.com/daisukedesign0924/emerge-mono-portfolio
 * Description:       A monochrome portfolio toolkit for creators. Build a full portfolio site with shortcodes: hero, works gallery, profile, news, contact form, estimate simulator, and auto-generated privacy policy / terms pages.
 * Version:           3.0.0
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

define( 'EMONO_VERSION', '3.0.0' );
define( 'EMONO_PATH', plugin_dir_path( __FILE__ ) );
define( 'EMONO_URL',  plugin_dir_url( __FILE__ ) );

// テーマ紹介ページのURL。公式テーマページが用意できたらここを差し替える。
// '#' のときはボタンは表示されるがクリックしても遷移しない（準備中）。
define( 'EMONO_THEME_URL', '#' );

require_once EMONO_PATH . 'includes/post-types.php';
require_once EMONO_PATH . 'includes/custom-fields.php';
require_once EMONO_PATH . 'includes/shortcodes.php';
require_once EMONO_PATH . 'includes/nav.php';
require_once EMONO_PATH . 'includes/contact.php';
require_once EMONO_PATH . 'includes/privacy/privacy.php';
require_once EMONO_PATH . 'includes/terms/terms.php';
require_once EMONO_PATH . 'includes/estimate/estimate.php';
require_once EMONO_PATH . 'includes/top-preview.php';
require_once EMONO_PATH . 'admin/admin.php';
// ── エディター機能 ──
add_action( 'plugins_loaded', 'emono_load_editor' );
function emono_load_editor() {
    $opts    = get_option( 'en_options', array() );
    $enabled = isset($opts['editor_enabled']) ? $opts['editor_enabled'] : '1';
    if ( $enabled !== '1' ) return;

    require_once EMONO_PATH . 'admin/editor/api.php';
    require_once EMONO_PATH . 'admin/editor/style.php';
    require_once EMONO_PATH . 'admin/editor/page.php';

    add_action( 'admin_menu', 'emono_editor_menus', 20 );
    add_action( 'admin_menu', 'emono_editor_hide_menus', 999 );
    add_action( 'admin_bar_menu', 'emono_editor_hide_bar', 999 );
}

function emono_editor_menus() {
    add_menu_page( __( 'Design Editor', 'emerge-mono-portfolio' ), __( 'Design Editor', 'emerge-mono-portfolio' ), 'manage_options', 'ene-top', 'emono_design_editor_page', 'dashicons-art', 31 );
    add_menu_page( __( 'Post', 'emerge-mono-portfolio' ),     __( 'Post', 'emerge-mono-portfolio' ),     'edit_posts',  'ene-post',        'emono_ed_page_post',   'dashicons-edit-page',  32 );
    add_menu_page( __( 'All Works', 'emerge-mono-portfolio' ),   __( 'All Works', 'emerge-mono-portfolio' ),    'edit_posts',  'ene-works',       'emono_ed_page_works',  'dashicons-portfolio',  33 );
    add_menu_page( __( 'All News', 'emerge-mono-portfolio' ), __( 'All News', 'emerge-mono-portfolio' ), 'edit_posts',  'ene-news',        'emono_ed_page_news',   'dashicons-megaphone',  34 );
    add_menu_page( __( 'Page Manager', 'emerge-mono-portfolio' ),   __( 'Page Manager', 'emerge-mono-portfolio' ),   'edit_pages',  'ene-page-create', 'emono_ed_page_create', 'dashicons-admin-page', 35 );
    add_submenu_page( null, __( 'Edit Page', 'emerge-mono-portfolio' ), __( 'Edit Page', 'emerge-mono-portfolio' ), 'edit_pages', 'ene-page-edit', 'emono_ed_page_edit' );
}

function emono_editor_hide_menus() {
    remove_menu_page( 'edit.php?post_type=en_work' );
    remove_menu_page( 'edit.php?post_type=en_news' );
    remove_menu_page( 'edit.php?post_type=page' );
    // 標準投稿メニューは設定で表示する場合は消さない
    if ( ! emono_opt( 'show_default_posts', '0' ) ) {
        remove_menu_page( 'edit.php' );
    }
}

function emono_editor_hide_bar( $bar ) {
    $bar->remove_node( 'new-post' );
    $bar->remove_node( 'new-en_work' );
}

add_action( 'wp_enqueue_scripts', 'emono_enqueue_assets' );
function emono_enqueue_assets() {
    wp_enqueue_style( 'emerge-mono-portfolio', EMONO_URL . 'assets/css/en-style.css', array(), EMONO_VERSION );

    // Design variables (colors, font, logo sizes) are generated from user settings
    // and attached as inline CSS to the main stylesheet handle (WordPress best practice).
    $inline_css = emono_build_design_css() . emono_build_logo_size_css();
    if ( $inline_css !== '' ) {
        wp_add_inline_style( 'emerge-mono-portfolio', $inline_css );
    }

    // Fonts are system fonts or user-uploaded files only. No external font
    // service (e.g. Google Fonts) is loaded, so no visitor data is sent out.
    wp_enqueue_script( 'emerge-mono-portfolio', EMONO_URL . 'assets/js/en-script.js', array(), EMONO_VERSION, true );
    wp_localize_script( 'emerge-mono-portfolio', 'EN', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'rest_url' => rest_url( 'wp/v2/' ),
        'nonce'    => wp_create_nonce( 'en_nonce' ),
        'options'  => emono_get_options(),
        'i18n'     => array(
            'sendFailed' => __( 'Failed to send.', 'emerge-mono-portfolio' ),
        ),
    ));
}

function emono_get_options() {
    return apply_filters( 'emono_options', get_option( 'en_options', array() ) );
}
function emono_opt( $key, $default = '' ) {
    $opts = emono_get_options();
    return isset( $opts[$key] ) ? $opts[$key] : $default;
}

add_action( 'admin_menu', function() {
    // 標準投稿（ブログ）メニューはデフォルトで非表示。
    // 設定「show_default_posts」がONのときだけ表示する。
    if ( ! emono_opt( 'show_default_posts', '0' ) ) {
        remove_menu_page( 'edit.php' );
    }
}, 999 );

register_activation_hook( __FILE__, 'emono_activate' );
function emono_activate() {
    if ( ! get_option( 'en_options' ) ) {
        update_option( 'en_options', array(
            'site_name'    => get_bloginfo('name'),
            'site_tagline' => 'Web Creator',
            'copyright'    => '(c) ' . wp_date('Y') . ' ' . get_bloginfo('name'),
            'top_layout'   => 'mono',
        ));
    }
    // セットアップウィザードを初回のみ表示するためのフラグ
    set_transient( 'en_show_setup_wizard', 1, 60 );

    // ★リライトルールを正しく再生成するため、フラッシュ前にCPTを登録しておく。
    //   有効化フックは init より前に走るため、ここで明示登録しないと
    //   /works/{slug}/ や /news/{slug}/ のルールが生成されず、ページが404になる。
    if ( function_exists( 'emono_register_work_post_type' ) ) {
        emono_register_work_post_type();
    }
    if ( function_exists( 'emono_register_news_post_type' ) ) {
        emono_register_news_post_type();
    }
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, function() { flush_rewrite_rules(); });

// ── SVGアップロードを許可 ──
add_filter( 'upload_mimes', 'emono_allow_svg' );
function emono_allow_svg( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'emono_fix_svg_filetype', 10, 4 );
function emono_fix_svg_filetype( $data, $file, $filename, $mimes ) {
    $filetype = wp_check_filetype( $filename, $mimes );
    return array(
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename'],
    );
}

// メディアライブラリでSVGをプレビュー表示
add_action( 'admin_enqueue_scripts', 'emono_svg_media_preview' );
function emono_svg_media_preview() {
    $css = '.attachment-preview .thumbnail img[src$=".svg"],'
         . 'img[src$=".svg"].attachment-thumb { width:100%; height:auto; }';
    wp_register_style( 'emerge-mono-admin-svg', false, array(), EMONO_VERSION );
    wp_enqueue_style( 'emerge-mono-admin-svg' );
    wp_add_inline_style( 'emerge-mono-admin-svg', $css );
}

// フォントファイルのアップロードを許可（カスタムフォント機能用）
add_filter( 'upload_mimes', 'emono_allow_fonts' );
function emono_allow_fonts( $mimes ) {
    $mimes['woff']  = 'font/woff';
    $mimes['woff2'] = 'font/woff2';
    $mimes['ttf']   = 'font/ttf';
    $mimes['otf']   = 'font/otf';
    return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'emono_fix_font_filetype', 10, 4 );
function emono_fix_font_filetype( $data, $file, $filename, $mimes ) {
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

/**
 * フォント定義の一元管理
 * 'system' => システムフォント（外部読み込み不要・font-family文字列）
 * 外部フォントサービス（Google Fonts等）は使用しない。ユーザーは独自フォントのアップロードも可能。
 */
function emono_font_registry() {
    return array(
        // key => array( 'type' => system, 'stack' => CSS font-family )
        // System fonts only. No external font services are loaded, so no visitor
        // data is sent to third parties. Users can also upload their own font files.
        'System Sans'  => array( 'type' => 'system', 'stack' => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif" ),
        'System Serif' => array( 'type' => 'system', 'stack' => "Georgia, 'Times New Roman', 'YuMincho', 'Hiragino Mincho ProN', 'MS PMincho', serif" ),
        'System Mono'  => array( 'type' => 'system', 'stack' => "ui-monospace, SFMono-Regular, Menlo, Consolas, 'Courier New', monospace" ),
    );
}

/**
 * カスタムフォント一覧を取得（複数対応）
 * 戻り値: array( array('name'=>..., 'url'=>...), ... )
 */
function emono_get_custom_fonts() {
    $fonts = emono_opt( 'en_custom_fonts', array() );
    if ( ! is_array( $fonts ) ) return array();
    // 旧データ（単数版）からの移行
    $legacy_name = emono_opt( 'design_custom_font_name', '' );
    $legacy_url  = emono_opt( 'design_custom_font_url', '' );
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
function emono_custom_font_url( $name ) {
    foreach ( emono_get_custom_fonts() as $f ) {
        if ( isset($f['name']) && $f['name'] === $name ) {
            return isset($f['url']) ? $f['url'] : '';
        }
    }
    return '';
}

/** 選択フォントのCSS font-family文字列を返す */
function emono_font_stack( $font_key ) {
    $reg = emono_font_registry();
    if ( isset( $reg[ $font_key ] ) ) {
        // Registry stacks are fixed internal strings, safe to use as-is.
        return $reg[ $font_key ]['stack'];
    }
    // Custom or unknown font: the key may come from options, so strip characters
    // that could break out of the CSS value. Keep letters, numbers, spaces, hyphens.
    $safe = preg_replace( '/[^A-Za-z0-9 ._-]/', '', (string) $font_key );
    $safe = trim( $safe );
    if ( $safe === '' ) {
        return "ui-monospace, monospace";
    }
    if ( emono_custom_font_url( $font_key ) ) {
        return "'" . $safe . "', sans-serif";
    }
    // Unknown value: use it with a monospace fallback.
    return "'" . $safe . "', monospace";
}

/** Ajax: カスタムフォントを追加 */
add_action( 'wp_ajax_en_add_custom_font', 'emono_ajax_add_custom_font' );
function emono_ajax_add_custom_font() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono-portfolio' ) );
    }
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'en_custom_font_nonce' ) ) {
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
add_action( 'wp_ajax_en_delete_custom_font', 'emono_ajax_delete_custom_font' );
function emono_ajax_delete_custom_font() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono-portfolio' ) );
    }
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'en_custom_font_nonce' ) ) {
        wp_send_json_error( __( 'Invalid request', 'emerge-mono-portfolio' ) );
    }
    $name = isset($_POST['name']) ? sanitize_text_field( wp_unslash($_POST['name']) ) : '';

    $opts = get_option( 'en_options', array() );
    $changed = false;

    // Remove from the current (multi) storage.
    if ( isset($opts['en_custom_fonts']) && is_array($opts['en_custom_fonts']) ) {
        $opts['en_custom_fonts'] = array_values( array_filter( $opts['en_custom_fonts'], function($f) use ($name) {
            return ! ( isset($f['name']) && $f['name'] === $name );
        } ) );
        $changed = true;
    }

    // Also remove the legacy single-font option if it matches, otherwise
    // emono_get_custom_fonts() would keep re-adding it (font reappears after delete).
    if ( isset($opts['design_custom_font_name']) && $opts['design_custom_font_name'] === $name ) {
        $opts['design_custom_font_name'] = '';
        $opts['design_custom_font_url']  = '';
        $changed = true;
    }

    if ( $changed ) {
        update_option( 'en_options', $opts );
    }

    wp_send_json_success( array(
        'fonts' => isset($opts['en_custom_fonts']) ? $opts['en_custom_fonts'] : array(),
    ) );
}

/**
 * Build the design CSS (custom font @font-face + CSS variables) as a string.
 * Returned value is attached via wp_add_inline_style(). No raw style tag here.
 */
function emono_build_design_css() {
    $bg     = emono_opt('design_bg',    '#000000');
    $text   = emono_opt('design_text',  '#ffffff');
    $accent = emono_opt('design_accent','#ffffff');
    $font   = emono_opt('design_font',  'System Mono');
    $mode   = emono_opt('design_mode',  'dark');

    $css = '';

    // If a user-uploaded custom font is selected, output its @font-face.
    // System fonts need nothing here; no external font service is used.
    $custom_url = emono_custom_font_url( $font );
    if ( $custom_url ) {
        // Sanitize the font name for safe use inside the CSS value (no quotes/braces).
        $font_name = trim( preg_replace( '/[^A-Za-z0-9 ._-]/', '', (string) $font ) );
        if ( $font_name !== '' ) {
            $css .= "@font-face{font-family:'" . $font_name . "';font-weight:400 700;font-style:normal;font-display:swap;src:url('" . esc_url( $custom_url ) . "');}\n";
        }
    }

    // Font stack is a safe internal/sanitized string (see emono_font_stack()).
    // Do NOT esc_attr() it: that would turn quotes into &#039; and break the CSS.
    $font_stack = emono_font_stack( $font );
    $a = esc_attr( $accent );

    if ( $mode === 'auto' ) {
        $a_light = ( $accent === '#ffffff' ) ? '#000000' : $a;
        $css .= ":root {\n    --en-font: {$font_stack};\n    --en-accent: {$a};\n}\n@media (prefers-color-scheme: light) {\n    body.en-mode-auto { --en-accent: {$a_light}; }\n}\n";
    } elseif ( $mode === 'light' ) {
        $a_light = ( $accent === '#ffffff' ) ? '#000000' : $a;
        $css .= ":root {\n    --en-font: {$font_stack};\n    --en-accent: {$a_light};\n}\n";
    } else {
        $t = esc_attr( $text );
        $b = esc_attr( $bg );
        $css .= ":root {\n    --en-bg:     {$b};\n    --en-text:   {$t};\n    --en-accent: {$a};\n    --en-muted:  {$t}40;\n    --en-border: {$t}20;\n    --en-font:   {$font_stack};\n}\n";
    }

    return $css;
}

// ── ロゴサイズ（ブレイクポイント別）をCSS変数として出力 ──
/**
 * Build the logo-size CSS variables (per breakpoint) as a string.
 * Returned value is attached via wp_add_inline_style(). No raw style tag here.
 */
function emono_build_logo_size_css() {
    // トップページロゴ（vw）。desktop / tablet(<=768px) / mobile(<=480px)
    $top_d = (float) emono_opt( 'top_logo_size',        14 );
    $top_t = (float) emono_opt( 'top_logo_size_tablet', 0 );
    $top_m = (float) emono_opt( 'top_logo_size_mobile', 0 );
    // 未設定(0)は上位ブレイクポイントの値を継承
    if ( $top_d <= 0 ) $top_d = 14;
    if ( $top_t <= 0 ) $top_t = $top_d;
    if ( $top_m <= 0 ) $top_m = $top_t;

    // ヘッダーロゴ（vw）。従来CSSの 3.2vw をデフォルトに
    $hdr_d = (float) emono_opt( 'header_logo_size',        3.2 );
    $hdr_t = (float) emono_opt( 'header_logo_size_tablet', 0 );
    $hdr_m = (float) emono_opt( 'header_logo_size_mobile', 0 );
    if ( $hdr_d <= 0 ) $hdr_d = 3.2;
    if ( $hdr_t <= 0 ) $hdr_t = $hdr_d;
    if ( $hdr_m <= 0 ) $hdr_m = $hdr_t;

    // All values are cast to (float) above, so they are safe to interpolate into CSS.
    $css  = ":root { --en-top-logo: {$top_d}vw; --en-header-logo: {$hdr_d}vw; }\n";
    $css .= "@media (max-width: 768px) { :root { --en-top-logo: {$top_t}vw; --en-header-logo: {$hdr_t}vw; } }\n";
    $css .= "@media (max-width: 480px) { :root { --en-top-logo: {$top_m}vw; --en-header-logo: {$hdr_m}vw; } }\n";
    return $css;
}

// ── bodyにカラーモードクラスを付与 ──
add_filter( 'body_class', 'emono_add_mode_body_class' );
function emono_add_mode_body_class( $classes ) {
    $mode = emono_opt( 'design_mode', 'dark' );
    if ( $mode === 'light' ) {
        $classes[] = 'en-mode-light';
    } elseif ( $mode === 'auto' ) {
        $classes[] = 'en-mode-auto';
    } else {
        $classes[] = 'en-mode-dark';
    }

    if ( is_front_page() && function_exists( 'emono_get_top_layout' ) ) {
        $classes[] = 'en-top-layout-' . sanitize_html_class( emono_get_top_layout() );
    }

    return $classes;
}

// ── en_work 詳細ページのテンプレートをプラグインから提供 ──
add_filter( 'template_include', 'emono_single_work_template' );
function emono_single_work_template( $template ) {
    if ( is_singular( 'en_work' ) ) {
        return EMONO_PATH . 'includes/single-work.php';
    }
    return $template;
}

// ── en_news（News記事）詳細ページのテンプレートをプラグインから提供 ──
add_filter( 'template_include', 'emono_single_post_template' );
function emono_single_post_template( $template ) {
    if ( is_singular( 'en_news' ) ) {
        return EMONO_PATH . 'includes/single-post.php';
    }
    return $template;
}
