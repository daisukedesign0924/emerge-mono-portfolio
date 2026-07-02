<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EN_PATH . 'includes/privacy/admin-page.php';
require_once EN_PATH . 'includes/privacy/admin-save.php';
require_once EN_PATH . 'includes/privacy/shortcode.php';

// ── プライバシープレビュー AJAX ──
add_action( 'wp_ajax_en_preview_privacy', 'en_ajax_preview_privacy' );
function en_ajax_preview_privacy() {
    if ( ! check_ajax_referer('en_preview_privacy', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error( __( 'Permission denied', 'emerge-mono' ) );
    }
    $lang           = sanitize_key( (isset($_POST['privacy_lang']) ? $_POST['privacy_lang'] : 'ja') );
    $owner          = sanitize_text_field( (isset($_POST['legal_owner']) ? $_POST['legal_owner'] : '') );
    $site           = sanitize_text_field( (isset($_POST['legal_site']) ? $_POST['legal_site'] : '') );
    $email          = sanitize_email( (isset($_POST['legal_email']) ? $_POST['legal_email'] : '') );
    $use_cookie     = isset($_POST['privacy_cookie'])     ? '1' : '0';
    $use_ga         = isset($_POST['privacy_ga'])         ? '1' : '0';
    $use_disclaimer = isset($_POST['privacy_disclaimer']) ? '1' : '0';
    $custom         = wp_kses_post( (isset($_POST['privacy_custom']) ? $_POST['privacy_custom'] : '') );

    $html = en_privacy_generate_html( $lang, $owner, $site, $email, $use_cookie, $use_ga, $use_disclaimer, $custom );
    wp_send_json_success( $html );
}

// ── Cookieバナー出力 ──
add_action( 'wp_footer', 'en_cookie_banner' );
function en_cookie_banner() {
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
    <script>
    (function(){
        function getCookie(name) {
            var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
            return v ? v[2] : null;
        }
        function setCookie(name, value, days) {
            var d = new Date();
            d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
        }
        var consent = getCookie('en_cookie_consent');
        if ( ! consent ) {
            document.getElementById('en-cookie-banner').style.display = 'block';
        }
        window.enCookieAccept = function() {
            setCookie('en_cookie_consent', 'accepted', 182);
            document.getElementById('en-cookie-banner').style.display = 'none';
        };
        window.enCookieDecline = function() {
            setCookie('en_cookie_consent', 'declined', 182);
            document.getElementById('en-cookie-banner').style.display = 'none';
        };
    })();
    </script>
    <?php
}
