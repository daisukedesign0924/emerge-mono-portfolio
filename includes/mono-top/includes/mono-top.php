<?php
/**
 * MONO TOP レイアウト本体（旧 Emerge Mono - Portfolio から分離）。
 * テキストドメインは emerge-mono のまま（本体の翻訳を利用）。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_mono_top_default_options() {
    return array(
        'hero' => array(
            'enabled'         => '1',
            'background_type' => 'image',
            'background_url'  => '',
            'video_url'       => '',
            'kicker'          => 'EMERGE MONO',
            'copy'            => "Where creators\nshape their world.",
        ),
        'about' => array(
            'enabled'    => '1',
            'kicker'     => 'ABOUT',
            'title'      => "Voice, story,\nand presence.",
            'body'       => 'A flexible monochrome site system for creators, studios, and projects that need a strong first impression.',
            'image_url'  => '',
            'link_label' => 'MORE',
            'link_url'   => '',
        ),
        'member' => array(
            'enabled'          => '1',
            'kicker'           => 'WORKS',
            'title'            => 'Featured',
            'post_type'        => 'en_work',
            'image_ratio'      => 'landscape',
            'view_all_page_id' => 0,
            'count'            => 12,
        ),
        'news' => array(
            'enabled'     => '1',
            'kicker'      => 'NEWS',
            'title'       => 'Latest',
            'post_type'   => 'en_news',
            'image_ratio' => 'landscape',
            'count'       => 6,
        ),
        'cta' => array(
            'enabled'        => '1',
            'kicker'         => 'CONTACT',
            'title'          => 'Start your next project.',
            'body'           => 'Tell your story with a site that stays minimal, editable, and easy to grow.',
            'background_url' => '',
            'button_label'   => 'CONTACT',
            'button_url'     => '',
        ),
    );
}

function emono_mono_top_merge_options( $defaults, $stored ) {
    foreach ( $defaults as $key => $default_value ) {
        if ( is_array( $default_value ) ) {
            $stored_value = isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ? $stored[ $key ] : array();
            $stored[ $key ] = emono_mono_top_merge_options( $default_value, $stored_value );
        } elseif ( ! array_key_exists( $key, $stored ) ) {
            $stored[ $key ] = $default_value;
        }
    }

    return $stored;
}

function emono_mono_top_get_options( $opts = null ) {
    if ( ! is_array( $opts ) ) {
        $opts = emono_get_options();
    }

    $stored = isset( $opts['mono_top'] ) && is_array( $opts['mono_top'] ) ? $opts['mono_top'] : array();
    if ( emono_mono_top_is_empty_disabled_state( $stored ) ) {
        $stored = array();
    }
    return emono_mono_top_merge_options( emono_mono_top_default_options(), $stored );
}

function emono_mono_top_is_empty_disabled_state( $stored ) {
    if ( empty( $stored ) || ! is_array( $stored ) ) {
        return false;
    }

    $section_keys = array( 'hero', 'about', 'member', 'news', 'cta' );
    foreach ( $section_keys as $section_key ) {
        if ( empty( $stored[ $section_key ] ) || ! is_array( $stored[ $section_key ] ) ) {
            return false;
        }
        if ( ! empty( $stored[ $section_key ]['enabled'] ) ) {
            return false;
        }
    }

    $text_paths = array(
        array( 'hero', 'kicker' ),
        array( 'hero', 'copy' ),
        array( 'about', 'kicker' ),
        array( 'about', 'title' ),
        array( 'about', 'body' ),
        array( 'member', 'kicker' ),
        array( 'member', 'title' ),
        array( 'news', 'kicker' ),
        array( 'news', 'title' ),
        array( 'cta', 'kicker' ),
        array( 'cta', 'title' ),
        array( 'cta', 'body' ),
    );

    foreach ( $text_paths as $path ) {
        if ( ! empty( $stored[ $path[0] ][ $path[1] ] ) ) {
            return false;
        }
    }

    return true;
}

function emono_mono_top_get_public_post_types() {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    $items = array();

    foreach ( $post_types as $post_type => $object ) {
        if ( in_array( $post_type, array( 'attachment', 'page' ), true ) ) {
            continue;
        }

        $items[ $post_type ] = isset( $object->labels->name ) ? $object->labels->name : $post_type;
    }

    if ( ! isset( $items['en_work'] ) && post_type_exists( 'en_work' ) ) {
        $items['en_work'] = __( 'Works', 'emerge-mono' );
    }
    if ( ! isset( $items['en_news'] ) && post_type_exists( 'en_news' ) ) {
        $items['en_news'] = __( 'News', 'emerge-mono' );
    }

    return $items;
}

function emono_mono_top_sanitize_post_type( $post_type, $fallback ) {
    $post_type = sanitize_key( $post_type );
    $items = emono_mono_top_get_public_post_types();

    if ( isset( $items[ $post_type ] ) ) {
        return $post_type;
    }

    if ( isset( $items[ $fallback ] ) ) {
        return $fallback;
    }

    return isset( $items['post'] ) ? 'post' : $fallback;
}

function emono_mono_top_sanitize_image_ratio( $ratio ) {
    $ratio = sanitize_key( $ratio );
    $allowed = array( 'landscape', 'portrait', 'square', 'standard' );

    return in_array( $ratio, $allowed, true ) ? $ratio : 'landscape';
}

function emono_mono_top_sanitize_options( $options ) {
    $defaults = emono_mono_top_default_options();
    $options = emono_mono_top_merge_options( $defaults, is_array( $options ) ? $options : array() );
    $hero_type = sanitize_key( $options['hero']['background_type'] );
    if ( ! in_array( $hero_type, array( 'image', 'video' ), true ) ) {
        $hero_type = 'image';
    }

    return array(
        'hero' => array(
            'enabled'         => ! empty( $options['hero']['enabled'] ) ? '1' : '0',
            'background_type' => $hero_type,
            'background_url'  => esc_url_raw( $options['hero']['background_url'] ),
            'video_url'       => esc_url_raw( $options['hero']['video_url'] ),
            'kicker'          => sanitize_text_field( $options['hero']['kicker'] ),
            'copy'            => sanitize_textarea_field( $options['hero']['copy'] ),
        ),
        'about' => array(
            'enabled'    => ! empty( $options['about']['enabled'] ) ? '1' : '0',
            'kicker'     => sanitize_text_field( $options['about']['kicker'] ),
            'title'      => sanitize_textarea_field( $options['about']['title'] ),
            'body'       => sanitize_textarea_field( $options['about']['body'] ),
            'image_url'  => esc_url_raw( $options['about']['image_url'] ),
            'link_label' => sanitize_text_field( $options['about']['link_label'] ),
            'link_url'   => esc_url_raw( $options['about']['link_url'] ),
        ),
        'member' => array(
            'enabled'          => ! empty( $options['member']['enabled'] ) ? '1' : '0',
            'kicker'           => sanitize_text_field( $options['member']['kicker'] ),
            'title'            => sanitize_text_field( $options['member']['title'] ),
            'post_type'        => emono_mono_top_sanitize_post_type( $options['member']['post_type'], 'en_work' ),
            'image_ratio'      => emono_mono_top_sanitize_image_ratio( $options['member']['image_ratio'] ),
            'view_all_page_id' => absint( $options['member']['view_all_page_id'] ),
            'count'            => min( 24, max( 1, absint( $options['member']['count'] ) ) ),
        ),
        'news' => array(
            'enabled'     => ! empty( $options['news']['enabled'] ) ? '1' : '0',
            'kicker'      => sanitize_text_field( $options['news']['kicker'] ),
            'title'       => sanitize_text_field( $options['news']['title'] ),
            'post_type'   => 'en_news',
            'image_ratio' => emono_mono_top_sanitize_image_ratio( $options['news']['image_ratio'] ),
            'count'       => min( 12, max( 1, absint( $options['news']['count'] ) ) ),
        ),
        'cta' => array(
            'enabled'        => ! empty( $options['cta']['enabled'] ) ? '1' : '0',
            'kicker'         => sanitize_text_field( $options['cta']['kicker'] ),
            'title'          => sanitize_text_field( $options['cta']['title'] ),
            'body'           => sanitize_textarea_field( $options['cta']['body'] ),
            'background_url' => esc_url_raw( $options['cta']['background_url'] ),
            'button_label'   => sanitize_text_field( $options['cta']['button_label'] ),
            'button_url'     => esc_url_raw( $options['cta']['button_url'] ),
        ),
    );
}

add_filter( 'emono_save_top_layout_settings', 'emono_save_mono_top_settings', 10, 2 );
function emono_save_mono_top_settings( $opts, $layout ) {
    if ( $layout !== 'mono_top' ) {
        return $opts;
    }
    // ビルダーのフォームが送信された場合のみ更新（未送信なら既存を維持）。
    // Nonce は呼び出し元（emono_handle_save / emono_ajax_live_preview）で検証済み。
    if ( ! isset( $_POST['mono_top_settings_present'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by caller (save/preview handler).
        return $opts;
    }
    // 各値は emono_mono_top_sections_from_post 内で個別にサニタイズ。
    $opts['mono_top_sections'] = emono_mono_top_sections_from_post( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified by caller; each value sanitized in helper.
    return $opts;
}

add_action( 'emono_top_layout_settings', 'emono_render_mono_top_settings', 10, 2 );
function emono_render_mono_top_settings( $layout, $opts ) {
    // MONO TOP のセクション・ビルダーを描画（includes/mono-top-sections.php）。
    emono_mono_top_render_builder( $opts, $layout !== 'mono_top' );
}

function emono_mono_top_render_post_type_select( $name, $selected, $post_types ) {
    ?>
    <select name="<?php echo esc_attr( $name ); ?>" class="en-field-input">
        <?php foreach ( $post_types as $post_type => $label ) : ?>
            <option value="<?php echo esc_attr( $post_type ); ?>" <?php selected( $selected, $post_type ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function emono_mono_top_render_page_select( $name, $selected, $pages ) {
    ?>
    <select name="<?php echo esc_attr( $name ); ?>" class="en-field-input">
        <option value="0"><?php esc_html_e( 'Auto', 'emerge-mono' ); ?></option>
        <?php foreach ( $pages as $page ) : ?>
            <option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( (int) $selected, (int) $page->ID ); ?>><?php echo esc_html( get_the_title( $page ) ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function emono_mono_top_render_image_ratio_select( $name, $selected ) {
    $ratios = array(
        'landscape' => __( 'Landscape 16:9', 'emerge-mono' ),
        'portrait'  => __( 'Portrait 3:4', 'emerge-mono' ),
        'square'    => __( 'Square 1:1', 'emerge-mono' ),
        'standard'  => __( 'Standard 4:3', 'emerge-mono' ),
    );
    ?>
    <select name="<?php echo esc_attr( $name ); ?>" class="en-field-input">
        <?php foreach ( $ratios as $ratio => $label ) : ?>
            <option value="<?php echo esc_attr( $ratio ); ?>" <?php selected( $selected, $ratio ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function emono_mono_top_filter_taxonomy( $post_type ) {
    $taxonomies = get_object_taxonomies( $post_type, 'objects' );
    foreach ( $taxonomies as $taxonomy => $object ) {
        if ( ! empty( $object->hierarchical ) && ! empty( $object->public ) ) {
            return $taxonomy;
        }
    }
    foreach ( $taxonomies as $taxonomy => $object ) {
        if ( ! empty( $object->public ) ) {
            return $taxonomy;
        }
    }
    return '';
}

function emono_mono_top_post_category_slugs( $post_id, $taxonomy ) {
    if ( $taxonomy === '' ) {
        return array( 'all' );
    }

    $terms = get_the_terms( $post_id, $taxonomy );
    if ( ! $terms || is_wp_error( $terms ) ) {
        return array( 'all' );
    }

    $slugs = array( 'all' );
    foreach ( $terms as $term ) {
        $slugs[] = sanitize_html_class( $term->slug );
    }

    return array_unique( $slugs );
}

function emono_mono_top_get_list_shortcodes() {
    $shortcodes = array(
        'en_work' => 'emerge_mono_works',
        'en_news' => 'emerge_mono_news',
    );

    /**
     * Extension plugins can map their post type to the fixed-page list shortcode.
     *
     * Example: array( 'en_talent' => 'emerge_mono_talents' ).
     */
    return apply_filters( 'emono_mono_top_list_shortcodes', $shortcodes );
}

function emono_mono_top_find_page_by_shortcode( $shortcode ) {
    $shortcode = trim( (string) $shortcode, '[] ' );
    if ( $shortcode === '' ) {
        return 0;
    }

    $pages = get_pages( array( 'post_status' => array( 'publish', 'draft' ), 'sort_column' => 'menu_order' ) );
    foreach ( $pages as $page ) {
        if ( has_shortcode( $page->post_content, $shortcode ) ) {
            return (int) $page->ID;
        }
    }

    return 0;
}

function emono_mono_top_get_list_page_url( $post_type, $page_id = 0 ) {
    $page_id = absint( $page_id );
    if ( $page_id ) {
        $url = get_permalink( $page_id );
        if ( $url ) {
            return $url;
        }
    }

    $shortcodes = emono_mono_top_get_list_shortcodes();
    if ( empty( $shortcodes[ $post_type ] ) ) {
        return '';
    }

    $page_id = emono_mono_top_find_page_by_shortcode( $shortcodes[ $post_type ] );
    return $page_id ? get_permalink( $page_id ) : '';
}

function emono_render_top_layout_mono_top( $atts = array() ) {
    // MONO TOP はセクション・インスタンス配列で構成される（includes/mono-top-sections.php）。
    return emono_mono_top_render_sections( emono_get_options() );
}


// ── レイアウト登録（本体の emono_top_layouts に mono_top を追加） ──
add_filter( 'emono_top_layouts', 'emmt_register_mono_top_layout' );
function emmt_register_mono_top_layout( $layouts ) {
    $layouts['mono_top'] = array(
        'label'       => __( 'MONO TOP', 'emerge-mono' ),
        'description' => __( 'Editorial top page with hero, about, post slider, news grid, and CTA sections.', 'emerge-mono' ),
        'callback'    => 'emono_render_top_layout_mono_top',
        'shortcode'   => 'emerge_mono_mono_top',
    );
    return $layouts;
}
