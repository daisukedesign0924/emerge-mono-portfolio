<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_get_top_layouts() {
    $layouts = array(
        'mono' => array(
            'label'       => __( 'Minimal Top', 'emerge-mono' ),
            'description' => __( 'Minimal portfolio top page with logo, site name, tagline, and buttons.', 'emerge-mono' ),
            'callback'    => 'emono_render_top_layout_mono',
            'shortcode'   => 'emerge_mono_minimal_top',
        ),
    );

    // MONO TOP など追加レイアウトは拡張プラグインが emono_top_layouts で登録する。
    return apply_filters( 'emono_top_layouts', $layouts );
}

function emono_get_top_layout() {
    $layout = sanitize_key( emono_opt( 'top_layout', 'mono' ) );
    $layouts = emono_get_top_layouts();
    return isset( $layouts[ $layout ] ) ? $layout : 'mono';
}

add_shortcode( 'emerge_mono_top', 'emono_shortcode_top' );
function emono_shortcode_top( $atts ) {
    $atts = shortcode_atts( array(
        'layout' => '',
    ), $atts, 'emerge_mono_top' );

    $layout = sanitize_key( $atts['layout'] );
    if ( $layout === '' ) {
        $layout = emono_get_top_layout();
    }

    return emono_render_top_layout( $layout, $atts );
}

function emono_render_top_layout( $layout, $atts = array() ) {
    $layout = sanitize_key( $layout );
    $layouts = emono_get_top_layouts();
    if ( ! isset( $layouts[ $layout ] ) ) {
        $layout = 'mono';
    }

    $callback = isset( $layouts[ $layout ]['callback'] ) ? $layouts[ $layout ]['callback'] : '';
    if ( is_callable( $callback ) ) {
        return call_user_func( $callback, $atts );
    }

    return emono_render_top_layout_mono( $atts );
}

add_action( 'init', 'emono_register_top_layout_shortcodes', 20 );
function emono_register_top_layout_shortcodes() {
    foreach ( emono_get_top_layouts() as $layout_key => $layout ) {
        if ( empty( $layout['shortcode'] ) ) {
            continue;
        }

        $shortcode = sanitize_key( $layout['shortcode'] );
        if ( $shortcode === '' || shortcode_exists( $shortcode ) ) {
            continue;
        }

        add_shortcode( $shortcode, 'emono_shortcode_fixed_top_layout' );
    }
}

function emono_shortcode_fixed_top_layout( $atts, $content = null, $tag = '' ) {
    $tag = sanitize_key( $tag );
    foreach ( emono_get_top_layouts() as $layout_key => $layout ) {
        $shortcode = isset( $layout['shortcode'] ) ? sanitize_key( $layout['shortcode'] ) : '';
        if ( $shortcode === $tag ) {
            return emono_render_top_layout( $layout_key, is_array( $atts ) ? $atts : array() );
        }
    }

    return '';
}

function emono_render_top_layout_mono( $atts = array() ) {
    $site_name      = emono_opt('site_name', get_bloginfo('name'));
    $tagline        = emono_opt('site_tagline', '');
    $logo_url       = emono_opt('logo_url', '');
    $logo_url_light = emono_opt('logo_url_light', '');

    // TOPボタン（複数対応）
    $top_buttons = emono_opt('top_buttons', array());
    if ( empty($top_buttons) ) {
        // 自動検出
        $top_buttons = array();
        $p1 = get_page_by_path('about') ?: get_page_by_path('profile');
        $p2 = get_page_by_path('works');
        if ( $p1 ) $top_buttons[] = array( 'label' => 'Profile', 'url' => get_permalink($p1->ID) );
        if ( $p2 ) $top_buttons[] = array( 'label' => 'Works',   'url' => get_permalink($p2->ID) );
    }
    ob_start(); ?>
    <div class="en-top en-top-layout-mono" id="en-top" data-top-layout="mono">
        <?php if ( $logo_url || $logo_url_light ) :
            $dark_src  = $logo_url       ? $logo_url       : $logo_url_light;
            $light_src = $logo_url_light ? $logo_url_light : $logo_url;
        ?>
            <img src="<?php echo esc_url($dark_src); ?>"  alt="<?php echo esc_attr($site_name); ?>" class="en-top-logo-img en-logo-dark">
            <?php if ( $logo_url && $logo_url_light ) : ?>
            <img src="<?php echo esc_url($light_src); ?>" alt="<?php echo esc_attr($site_name); ?>" class="en-top-logo-img en-logo-light">
            <?php endif; ?>
        <?php else : ?>
            <div class="en-top-logo-placeholder"></div>
        <?php endif; ?>
        <div class="en-top-name"><?php echo esc_html($site_name); ?></div>
        <?php if ( $tagline ) : ?>
            <div class="en-top-sub"><?php echo esc_html($tagline); ?></div>
        <?php endif; ?>
        <?php if ( ! empty($top_buttons) ) : ?>
        <div class="en-top-btns">
            <?php foreach ( $top_buttons as $tbtn ) :
                if ( empty($tbtn['label']) ) continue; ?>
                <a href="<?php echo esc_url(isset($tbtn['url']) ? $tbtn['url'] : '#'); ?>" class="en-btn"><?php echo esc_html($tbtn['label']); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

// プロフィール（厳選デザイン固定）。旧ショートコード [emerge_mono_about] は
// About ページに付け替えたため、プロフィールは [emerge_mono_profile] を使う。
// 既存ページ内の [emerge_mono_about] は emono_profile_about_migrate() で自動置換される。
add_shortcode( 'emerge_mono_profile', 'emono_shortcode_profile' );
function emono_shortcode_profile( $atts ) {
    $name    = emono_opt('profile_name', '');
    $role    = emono_opt('profile_role', '');
    $bio     = emono_opt('profile_bio', '');
    $img_url = emono_opt('profile_img', '');
    $skills  = emono_opt('profile_skills', '');
    $sns     = emono_opt('sns_links', array());

    // スキルをカンマ分割・トリム
    $skill_list = array();
    if ( $skills ) {
        $skill_list = array_filter( array_map( 'trim', explode( ',', $skills ) ) );
    }

    ob_start(); ?>
    <div class="en-about" id="en-about">
        <div class="en-about-inner">

            <div class="en-about-img-wrap">
                <?php if ( $img_url ) : ?>
                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($name); ?>" class="en-about-img">
                <?php else : ?>
                    <div class="en-about-img-placeholder"></div>
                <?php endif; ?>
            </div>

            <div class="en-about-info">
                <?php if ( $name ) : ?>
                    <h1 class="en-about-name"><?php echo esc_html($name); ?></h1>
                <?php endif; ?>

                <?php if ( $role ) : ?>
                    <div class="en-about-role"><?php echo esc_html($role); ?></div>
                <?php endif; ?>

                <?php if ( $bio ) : ?>
                    <div class="en-about-bio"><?php echo nl2br( esc_html($bio) ); ?></div>
                <?php endif; ?>

                <?php if ( ! empty($skill_list) ) : ?>
                    <div class="en-about-skills">
                        <?php foreach ( $skill_list as $skill ) : ?>
                            <span class="en-about-skill"><?php echo esc_html($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty($sns) ) : ?>
                    <div class="en-about-sns">
                        <?php foreach ( $sns as $s ) :
                            if ( empty($s['url']) ) continue;
                            $sns_icon  = isset($s['icon'])  ? $s['icon']  : '';
                            $sns_label = isset($s['label']) ? $s['label'] : $s['url'];
                        ?>
                            <a href="<?php echo esc_url($s['url']); ?>" target="_blank" rel="noopener" class="en-sns-link<?php echo $sns_icon ? ' en-sns-link-icon' : ''; ?>">
                                <?php if ( $sns_icon ) : ?>
                                    <img src="<?php echo esc_url($sns_icon); ?>" alt="<?php echo esc_attr($sns_label); ?>" class="en-sns-icon-img">
                                <?php endif; ?>
                                <span class="en-sns-label"><?php echo esc_html( $sns_label ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}

/**
 * About ページ（業種ごとにカスタムしたい人向け）。
 * デザイン編集でセクションを組み、その結果を描画する。データは
 * en_options['about_sections']（MONO TOP と同じセクション配列形）。
 * 未設定なら何も出さない（About が不要な人には空）。
 */
add_shortcode( 'emerge_mono_about', 'emono_shortcode_about_page' );
function emono_shortcode_about_page( $atts ) {
    if ( ! function_exists( 'emono_mono_top_render_sections' ) ) {
        return '';
    }
    $opts     = emono_get_options();
    $sections = isset( $opts['about_sections'] ) && is_array( $opts['about_sections'] ) ? $opts['about_sections'] : array();
    if ( empty( $sections ) ) {
        return '';
    }
    return emono_mono_top_render_sections( array( 'mono_top_sections' => $sections ) );
}

/**
 * 後方互換の一度きり移行：
 * 旧仕様では [emerge_mono_about] が「プロフィール」だった。プロフィールを
 * [emerge_mono_profile] に移したので、既存ページ内の [emerge_mono_about] を
 * [emerge_mono_profile] に自動置換し、見た目を維持する。置換後 [emerge_mono_about]
 * は空くので、新しい About（セクション型）に使える。
 */
add_action( 'admin_init', 'emono_profile_about_migrate' );
function emono_profile_about_migrate() {
    if ( get_option( 'emono_profile_about_migrated' ) ) {
        return;
    }
    $pages = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
        'posts_per_page' => -1,
        's'              => '[emerge_mono_about]',
        'fields'         => 'ids',
    ) );
    foreach ( $pages as $pid ) {
        $content = get_post_field( 'post_content', $pid );
        if ( strpos( $content, '[emerge_mono_about]' ) === false ) {
            continue;
        }
        $new = str_replace( '[emerge_mono_about]', '[emerge_mono_profile]', $content );
        wp_update_post( array( 'ID' => $pid, 'post_content' => $new ) );
    }
    update_option( 'emono_profile_about_migrated', 1 );
}

add_shortcode( 'emerge_mono_works', 'emono_shortcode_works' );
function emono_shortcode_works( $atts ) {
    ob_start(); ?>
    <div class="en-works" id="en-works">
        <div class="en-works-header">
            <div class="en-works-title">Works</div>
            <div class="en-works-filter" id="en-works-filter">
                <button class="en-filter-btn active" data-cat="all">All</button>
            </div>
        </div>
        <div class="en-works-grid" id="en-works-grid"></div>
        <div class="en-works-loading" id="en-works-loading">Loading...</div>
    </div>
    <?php return ob_get_clean();
}
