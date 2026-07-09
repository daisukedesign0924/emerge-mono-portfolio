<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'en_header', 'emono_render_header' );
function emono_render_header() {
    $site_name       = emono_opt('site_name', get_bloginfo('name'));
    $logo_url        = emono_opt('logo_url', '');
    $logo_url_light  = emono_opt('logo_url_light', '');
    $nav_items       = emono_get_nav_items();
    $is_top = is_front_page();
    $show_header_on_top = $is_top && function_exists( 'emono_get_top_layout' ) && emono_get_top_layout() === 'mono_top';
    ?>
    <?php if ( ! $is_top || $show_header_on_top ) : ?>
    <header class="en-header en-header-inner" id="en-header">
        <a href="<?php echo esc_url( home_url('/') ); ?>" class="en-header-logo">
            <?php if ( $logo_url || $logo_url_light ) : ?>
                <?php
                $dark_src  = $logo_url       ? $logo_url       : $logo_url_light;
                $light_src = $logo_url_light ? $logo_url_light : $logo_url;
                ?>
                <img src="<?php echo esc_url($dark_src); ?>"  alt="<?php echo esc_attr($site_name); ?>" class="en-header-logo-img en-logo-dark">
                <?php if ( $logo_url && $logo_url_light ) : ?>
                <img src="<?php echo esc_url($light_src); ?>" alt="<?php echo esc_attr($site_name); ?>" class="en-header-logo-img en-logo-light">
                <?php endif; ?>
            <?php else : ?>
                <span class="en-header-logo-text"><?php echo esc_html($site_name); ?></span>
            <?php endif; ?>
        </a>
        <nav class="en-header-nav">
            <?php foreach ( $nav_items as $item ) : ?>
                <a href="<?php echo esc_url($item['url']); ?>" class="en-nav-item"><?php echo esc_html($item['label']); ?></a>
            <?php endforeach; ?>
        </nav>
        <button class="en-menu-btn" id="en-menu-btn" aria-label="Menu">MENU</button>
    </header>
    <?php endif; ?>

    <div class="en-mobile-menu" id="en-mobile-menu">
        <button class="en-menu-close" id="en-menu-close">CLOSE</button>
        <nav class="en-mobile-nav">
            <?php foreach ( $nav_items as $item ) : ?>
                <a href="<?php echo esc_url($item['url']); ?>" class="en-mobile-nav-item"><?php echo esc_html($item['label']); ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
    <?php
}

add_action( 'en_footer', 'emono_render_footer' );
function emono_render_footer() {
    $copyright = emono_opt('copyright', '(c) ' . wp_date('Y') . ' ' . get_bloginfo('name'));
    $footer_nav_items = emono_get_footer_nav_items();
    ?>
    <footer class="en-footer">
        <?php if ( ! empty($footer_nav_items) ) : ?>
        <nav class="en-footer-menu" aria-label="Footer">
            <?php foreach ( $footer_nav_items as $item ) : ?>
                <a href="<?php echo esc_url($item['url']); ?>" class="en-footer-menu-item"><?php echo esc_html($item['label']); ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>
        <div class="en-footer-copy"><?php echo esc_html($copyright); ?></div>
    </footer>
    <?php
}

function emono_get_footer_nav_items() {
    $saved = emono_opt('footer_nav_items', array());
    if ( ! is_array($saved) ) return array();
    $items = array();
    foreach ( $saved as $item ) {
        $type = isset($item['type']) ? $item['type'] : 'page';
        if ( $type === 'page' && ! empty($item['page_id']) ) {
            $page_id = (int) $item['page_id'];
            $title   = get_the_title( $page_id );
            $url     = get_permalink( $page_id );
            if ( $title && $url ) {
                $items[] = array( 'label' => $title, 'url' => $url );
            }
        } elseif ( $type === 'url' ) {
            $label = isset($item['label']) ? $item['label'] : '';
            $url   = isset($item['url'])   ? $item['url']   : '';
            if ( $label && $url ) {
                $items[] = array( 'label' => $label, 'url' => $url );
            }
        }
    }
    return $items;
}

function emono_get_nav_items() {
    $mode = emono_opt('nav_mode', 'auto');

    // WPメニュー使用
    if ( $mode === 'wp_menu' ) {
        $menu_id = (int) emono_opt('nav_wp_menu', 0);
        if ( $menu_id ) {
            $items    = array();
            $wp_items = wp_get_nav_menu_items( $menu_id );
            if ( $wp_items ) {
                foreach ( $wp_items as $wp_item ) {
                    $items[] = array(
                        'label' => $wp_item->title,
                        'url'   => $wp_item->url,
                    );
                }
            }
            return $items;
        }
    }

    // 手動設定
    if ( $mode === 'manual' ) {
        $saved = emono_opt('nav_items', array());
        // 固定ページ選択の場合はURLを動的に取得
        foreach ( $saved as &$item ) {
            if ( isset($item['type']) && $item['type'] === 'page' && ! empty($item['page_id']) ) {
                $item['url'] = get_permalink( (int)$item['page_id'] );
            }
        }
        unset($item);
        return $saved;
    }

    // 自動生成（全公開固定ページ、ただしプライバシー・利用規約系は除外）
    $exclude_slugs = array( 'privacy-policy', 'privacy', 'terms', 'terms-of-service' );
    $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'menu_order' ) );
    $items = array();
    foreach ( $pages as $page ) {
        if ( in_array( $page->post_name, $exclude_slugs ) ) continue;
        if ( $page->ID === get_option('page_on_front') ) continue;
        $items[] = array(
            'label' => $page->post_title,
            'url'   => get_permalink( $page->ID ),
        );
    }
    return $items;
}
