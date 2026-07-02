<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EN_PATH . 'includes/estimate/admin-page.php';
require_once EN_PATH . 'includes/estimate/admin-save.php';
require_once EN_PATH . 'includes/estimate/shortcode.php';

// フロントのCSS/JSエンキュー
add_action( 'wp_enqueue_scripts', 'en_estimate_enqueue' );
function en_estimate_enqueue() {
    wp_enqueue_style(  'en-estimate', EN_URL . 'includes/estimate/assets/estimate.css', array(), EN_VERSION );
    wp_enqueue_script( 'en-estimate', EN_URL . 'includes/estimate/assets/estimate.js',  array(), EN_VERSION, true );
}
