<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'emerge_mono_top', 'emono_shortcode_top' );
function emono_shortcode_top( $atts ) {
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
    <div class="en-top" id="en-top">
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

add_shortcode( 'emerge_mono_about', 'emono_shortcode_about' );
function emono_shortcode_about( $atts ) {
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
