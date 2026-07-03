<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EMONO_PATH . 'admin/admin-page.php';
require_once EMONO_PATH . 'admin/admin-save.php';
require_once EMONO_PATH . 'admin/admin-style.php';
require_once EMONO_PATH . 'admin/setup-wizard.php';

add_action( 'admin_menu', 'emono_register_admin_menu' );
function emono_register_admin_menu() {
    // Emerge Mono 設定メニュー
    add_menu_page(
        'Emerge Mono - Portfolio',
        'Emerge Mono - Portfolio',
        'manage_options',
        'emerge-mono-portfolio',
        'emono_admin_page',
        'dashicons-star-half',
        30
    );

    // Works独立メニュー（カテゴリーも配下に）
    $work_label = emono_opt('work_label', 'Works');
    $work_icon  = emono_opt('work_icon',  'dashicons-portfolio');
    $cat_label  = emono_opt('cat_label',  __( 'Category', 'emerge-mono-portfolio' ));

    add_menu_page(
        $work_label, $work_label, 'edit_posts',
        'edit.php?post_type=en_work',
        '', $work_icon, 31
    );
    add_submenu_page(
        'edit.php?post_type=en_work',
        $cat_label, $cat_label, 'manage_categories',
        'edit-tags.php?taxonomy=en_work_category&post_type=en_work'
    );

    // News独立メニュー（カテゴリーも配下に）。Worksと対称
    $news_label     = emono_opt('news_label', 'News');
    $news_icon      = emono_opt('news_icon',  'dashicons-megaphone');
    $news_cat_label = emono_opt('news_cat_label', __( 'Category', 'emerge-mono-portfolio' ));

    add_menu_page(
        $news_label, $news_label, 'edit_posts',
        'edit.php?post_type=en_news',
        '', $news_icon, 32
    );
    add_submenu_page(
        'edit.php?post_type=en_news',
        $news_cat_label, $news_cat_label, 'manage_categories',
        'edit-tags.php?taxonomy=en_news_category&post_type=en_news'
    );
}
