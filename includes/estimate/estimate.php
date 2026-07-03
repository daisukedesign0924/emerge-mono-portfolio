<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EMONO_PATH . 'includes/estimate/admin-page.php';
require_once EMONO_PATH . 'includes/estimate/admin-save.php';
require_once EMONO_PATH . 'includes/estimate/shortcode.php';

// フロントのCSS/JSエンキュー
add_action( 'wp_enqueue_scripts', 'emono_estimate_enqueue' );
function emono_estimate_enqueue() {
    wp_enqueue_style(  'en-estimate', EMONO_URL . 'includes/estimate/assets/estimate.css', array(), EMONO_VERSION );
    wp_enqueue_script( 'en-estimate', EMONO_URL . 'includes/estimate/assets/estimate.js',  array(), EMONO_VERSION, true );
}
