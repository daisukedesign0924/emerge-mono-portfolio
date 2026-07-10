<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EMONO_PATH . 'includes/privacy/admin-page.php';
require_once EMONO_PATH . 'includes/privacy/admin-save.php';
require_once EMONO_PATH . 'includes/privacy/shortcode.php';

// ── プライバシープレビュー AJAX ──
add_action( 'wp_ajax_en_preview_privacy', 'emono_ajax_preview_privacy' );
function emono_ajax_preview_privacy() {
    if ( ! check_ajax_referer('en_preview_privacy', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono' ) );
    }
    $lang           = sanitize_key( (isset($_POST['privacy_lang']) ? wp_unslash($_POST['privacy_lang']) : 'ja') );
    $owner          = sanitize_text_field( (isset($_POST['legal_owner']) ? wp_unslash($_POST['legal_owner']) : '') );
    $site           = sanitize_text_field( (isset($_POST['legal_site']) ? wp_unslash($_POST['legal_site']) : '') );
    $email          = sanitize_email( (isset($_POST['legal_email']) ? wp_unslash($_POST['legal_email']) : '') );
    $use_cookie     = isset($_POST['privacy_cookie'])     ? '1' : '0';
    $use_ga         = isset($_POST['privacy_ga'])         ? '1' : '0';
    $use_disclaimer = isset($_POST['privacy_disclaimer']) ? '1' : '0';
    $custom         = wp_kses_post( (isset($_POST['privacy_custom']) ? wp_unslash($_POST['privacy_custom']) : '') );

    $html = emono_privacy_generate_html( $lang, $owner, $site, $email, $use_cookie, $use_ga, $use_disclaimer, $custom );
    wp_send_json_success( $html );
}

// ── Cookieバナー出力 ──
add_action( 'wp_footer', 'emono_cookie_banner' );
function emono_cookie_banner() {
    $opts = get_option( 'en_options', array() );
    if ( empty($opts['cookie_banner_enabled']) || $opts['cookie_banner_enabled'] !== '1' ) return;

    $lang        = isset($opts['privacy_lang']) ? $opts['privacy_lang'] : 'ja';
    $page_id     = isset($opts['cookie_banner_page_id']) ? (int)$opts['cookie_banner_page_id'] : 0;
    $privacy_url = $page_id ? get_permalink($page_id) : '';

    if ( $lang === 'en' ) {
        $text_before = 'This site uses cookies to improve your experience.';
        $text_after  = '';
        $link    = __( 'Learn more', 'emerge-mono' );
        $accept  = __( 'Accept', 'emerge-mono' );
        $decline = __( 'Decline', 'emerge-mono' );
    } else {
        $text_before = __( 'This site uses cookies.', 'emerge-mono' );
        $text_after  = '';
        $link    = __( 'Learn more', 'emerge-mono' );
        $accept  = __( 'Accept', 'emerge-mono' );
        $decline = __( 'Decline', 'emerge-mono' );
    }
    ?>
    <div class="en-cookie-banner" id="en-cookie-banner" style="display:none;">
        <div class="en-cookie-inner">
            <p class="en-cookie-text">
                <?php echo esc_html($text_before); ?>
                <?php if ( $privacy_url ) : ?>
                    <a href="<?php echo esc_url($privacy_url); ?>" class="en-cookie-link" target="_blank" rel="noopener"><?php echo esc_html($link); ?></a>
                <?php endif; ?>
                <?php if ( $text_after ) echo esc_html($text_after); ?>
            </p>
            <div class="en-cookie-btns">
                <button class="en-cookie-btn en-cookie-accept" onclick="enCookieAccept()"><?php echo esc_html($accept); ?></button>
                <button class="en-cookie-btn en-cookie-decline" onclick="enCookieDecline()"><?php echo esc_html($decline); ?></button>
            </div>
        </div>
    </div>
    <?php
    wp_enqueue_script( 'emerge-mono-cookie', EMONO_URL . 'assets/js/en-cookie.js', array(), EMONO_VERSION, true );
}
