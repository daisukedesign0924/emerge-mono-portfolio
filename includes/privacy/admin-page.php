<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_privacy( $opts ) {
    emono_admin_notice();
    $lang        = isset( $opts['privacy_lang'] )        ? $opts['privacy_lang']        : 'ja';
    $owner       = isset( $opts['legal_owner'] )       ? $opts['legal_owner']       : '';
    $site        = isset( $opts['legal_site'] )        ? $opts['legal_site']        : get_bloginfo('name');
    $email       = isset( $opts['legal_email'] )       ? $opts['legal_email']       : get_bloginfo('admin_email');
    $custom      = isset( $opts['privacy_custom'] )      ? $opts['privacy_custom']      : '';
    $use_cookie  = isset( $opts['privacy_cookie'] )      ? $opts['privacy_cookie']      : '1';
    $use_ga      = isset( $opts['privacy_ga'] )          ? $opts['privacy_ga']          : '0';
    $use_disclaimer = isset( $opts['privacy_disclaimer'] ) ? $opts['privacy_disclaimer'] : '1';
    $banner_enabled = isset( $opts['cookie_banner_enabled'] ) ? $opts['cookie_banner_enabled'] : '1';
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'emono_save_privacy', 'en_nonce' ); ?>
        <input type="hidden" name="en_privacy_save" value="1">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">
        <div class="en-privacy-settings-col">

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Basic Info', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'The privacy policy text is auto-generated from the info you enter.', 'emerge-mono' ); ?></div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Display Language', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:12px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px">
                        <input type="radio" name="privacy_lang" value="ja" <?php checked( $lang, 'ja' ); ?>>
                        <?php esc_html_e( 'Japanese', 'emerge-mono' ); ?>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px">
                        <input type="radio" name="privacy_lang" value="en" <?php checked( $lang, 'en' ); ?>>
                        English
                    </label>
                </div>
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Operator Name (personal or business)', 'emerge-mono' ); ?></label>
                <input type="text" name="legal_owner" value="<?php echo esc_attr( $owner ); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'e.g. Taro Yamada', 'emerge-mono' ); ?>">
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Site Name', 'emerge-mono' ); ?></label>
                <input type="text" name="legal_site" value="<?php echo esc_attr( $site ); ?>" class="en-field-input">
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Contact Email', 'emerge-mono' ); ?></label>
                <input type="email" name="legal_email" value="<?php echo esc_attr( $email ); ?>" class="en-field-input">
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Sections to Include', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Checked sections are automatically added to the privacy policy.', 'emerge-mono' ); ?></div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="privacy_cookie" value="1" <?php checked( $use_cookie, '1' ); ?>>
                    <span><?php esc_html_e( 'Add a section about cookie usage', 'emerge-mono' ); ?></span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="privacy_ga" value="1" <?php checked( $use_ga, '1' ); ?>>
                    <span><?php esc_html_e( 'Add a section about Google Analytics', 'emerge-mono' ); ?></span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="privacy_disclaimer" value="1" <?php checked( $use_disclaimer, '1' ); ?>>
                    <span><?php esc_html_e( 'Add a disclaimer section', 'emerge-mono' ); ?></span>
                </label>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Additional Notes (optional)', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:12px"><?php esc_html_e( 'Enter any content you want to append to the end of the template.', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <textarea name="privacy_custom" rows="5" class="en-field-input" style="width:100%;resize:vertical" placeholder="<?php echo esc_attr__( 'e.g. This site participates in affiliate programs. ...', 'emerge-mono' ); ?>"><?php echo esc_textarea( $custom ); ?></textarea>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Cookie Banner', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Shows a cookie consent banner on the first visit. Required in the EU, UK, and US.', 'emerge-mono' ); ?></div>

            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="cookie_banner_enabled" value="1" <?php checked( $banner_enabled, '1' ); ?>>
                    <span><?php esc_html_e( 'Show cookie banner', 'emerge-mono' ); ?></span>
                </label>
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Privacy Policy Page (optional)', 'emerge-mono' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:8px"><?php esc_html_e( 'This becomes the "Learn more" link in the banner. No link if unselected.', 'emerge-mono' ); ?></div>
                <?php
                $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'menu_order' ) );
                $selected_page_id = isset( $opts['cookie_banner_page_id'] ) ? (int)$opts['cookie_banner_page_id'] : 0;
                ?>
                <select name="cookie_banner_page_id" class="en-field-input" style="max-width:400px">
                    <option value=""><?php esc_html_e( '— No page selected —', 'emerge-mono' ); ?></option>
                    <?php foreach ( $pages as $page ) : ?>
                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($selected_page_id, $page->ID); ?>>
                        <?php echo esc_html($page->post_title); ?> （/<?php echo esc_html($page->post_name); ?>）
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="en-field-desc" style="margin-top:12px;padding:12px;background:rgba(255,255,255,.04);border-radius:4px;">
                <strong><?php esc_html_e( 'Banner text (auto)', 'emerge-mono' ); ?></strong><br>
                <?php esc_html_e( 'Japanese:', 'emerge-mono' ); ?> "This site uses cookies. See our Privacy Policy for details."<br>
                <?php esc_html_e( 'English:', 'emerge-mono' ); ?> "This site uses cookies. Please see our Privacy Policy for more details."<br>
                <?php /* translators: %s: the "Display Language" setting name */ echo esc_html( sprintf( __( '* The display language follows the "%s" setting above.', 'emerge-mono' ), __( 'Display Language', 'emerge-mono' ) ) ); ?>
            </div>
        </div>

        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
        </div><!-- /.settings-col -->

        <div style="position:sticky;top:32px;">
            <div style="font-size:11px;letter-spacing:.3em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:10px;">Preview</div>
            <div style="font-size:11px;color:rgba(255,255,255,.3);margin-bottom:10px;"><code>[emerge_mono_privacy]</code> Changes are reflected in real time.</div>
            <div id="en-privacy-preview" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);padding:20px;line-height:1.9;font-size:12px;max-height:80vh;overflow-y:auto;">
                <?php echo wp_kses_post( emono_privacy_generate_html( $lang, $owner, $site, $email, $use_cookie, $use_ga, $use_disclaimer, $custom ) ); ?>
            </div>
        </div>
        </div><!-- /.grid -->
    </form>

    <?php
    // Privacy preview script: external file + localized nonce (no inline PHP script).
    wp_enqueue_script( 'en-privacy-admin', EMONO_URL . 'includes/privacy/assets/privacy-admin.js', array(), EMONO_VERSION, true );
    wp_localize_script( 'en-privacy-admin', 'enPrivacyPreview', array(
        'nonce'   => wp_create_nonce( 'en_preview_privacy' ),
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    ) );
    ?>
    <?php
}

/**
 * プライバシーポリシー HTML生成（Article形式）
 */
function emono_privacy_generate_html( $lang, $owner, $site, $email, $use_cookie = '1', $use_ga = '0', $use_disclaimer = '1', $custom = '' ) {
    $owner = $owner ? esc_html( $owner ) : '（運営者名）';
    $site  = $site  ? esc_html( $site )  : '（サイト名）';
    $email = $email ? esc_html( $email ) : '（メールアドレス）';

    $sections = array();

    if ( $lang === 'en' ) {
        $intro = '<p>' . $site . ' (hereinafter "the Site"), operated by ' . $owner . ', handles personal information as follows.</p>';

        $sections[] = array( 'num' => 'Article 01', 'title' => 'Personal Information Collected',  'body' => '<p>When you use our contact form, we collect the following information: your name, email address, and any other details you provide in your message.</p>' );
        $sections[] = array( 'num' => 'Article 02', 'title' => 'Purpose of Use',                  'body' => '<p>Personal information collected through the contact form is used solely to respond to your inquiry. It will not be used for any other purpose.</p>' );
        $sections[] = array( 'num' => 'Article 03', 'title' => 'Disclosure to Third Parties',     'body' => '<p>We do not disclose personal information to third parties without your prior consent, except where required by law.</p>' );
        $sections[] = array( 'num' => 'Article 04', 'title' => 'Data Security',                   'body' => '<p>We take appropriate measures to protect personal information from unauthorized access, loss, or leakage.</p>' );

        if ( $use_cookie === '1' ) {
            $sections[] = array( 'num' => 'Article 05', 'title' => 'Cookies', 'body' => '<p>The Site may use cookies to improve user experience. You may disable cookies in your browser settings; however, some features of the Site may not function properly.</p>' );
        }

        if ( $use_ga === '1' ) {
            $n = count($sections) + 1;
            $sections[] = array( 'num' => 'Article ' . sprintf('%02d', $n), 'title' => 'Google Analytics', 'body' => '<p>The Site uses Google Analytics to understand how visitors interact with the site. Google Analytics uses cookies to collect information anonymously. You can opt out of Google Analytics by installing the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">Google Analytics Opt-out Browser Add-on</a>.</p>' );
        }

        if ( $use_disclaimer === '1' ) {
            $n = count($sections) + 1;
            $sections[] = array( 'num' => 'Article ' . sprintf('%02d', $n), 'title' => 'Disclaimer', 'body' => '<p>While we strive to provide accurate and up-to-date information, we make no warranties regarding the completeness or accuracy of the content on this Site. We are not liable for any damages arising from the use of this Site or its linked websites. Prices shown in the estimate simulator are approximate and not legally binding.</p>' );
        }

        $n = count($sections) + 1;
        $sections[] = array( 'num' => 'Contact', 'title' => 'Contact Us', 'body' => '<p>For inquiries regarding this Privacy Policy, please contact:<br>Operator: ' . $owner . '<br>Email: ' . $email . '</p>' );

        $date_str = '<p><small>Last updated: ' . date_i18n('F j, Y') . '</small></p>';

    } else {
        $intro = '<p>' . $owner . '（以下「運営者」）が運営する ' . $site . '（以下「当サイト」）では、個人情報の取り扱いについて以下のとおり定めます。</p>';

        $sections[] = array( 'num' => '第1条', 'title' => '収集する個人情報',   'body' => '<p>当サイトでは、お問い合わせフォームのご利用にあたり、お名前・メールアドレス・お問い合わせ内容等の個人情報をご提供いただく場合があります。</p>' );
        $sections[] = array( 'num' => '第2条', 'title' => '利用目的',           'body' => '<p>収集した個人情報は、お問い合わせへの返答のみに使用し、それ以外の目的には使用しません。</p>' );
        $sections[] = array( 'num' => '第3条', 'title' => '第三者への提供',     'body' => '<p>法令に基づく場合を除き、ご本人の同意なく個人情報を第三者に提供することはありません。</p>' );
        $sections[] = array( 'num' => '第4条', 'title' => '安全管理',           'body' => '<p>個人情報の漏えい・滅失・毀損を防止するため、適切な安全管理措置を講じます。</p>' );

        if ( $use_cookie === '1' ) {
            $n = count($sections) + 1;
            $sections[] = array( 'num' => '第' . $n . '条', 'title' => 'Cookie（クッキー）', 'body' => '<p>当サイトでは、サービス向上のためCookieを使用する場合があります。ブラウザの設定によりCookieを無効にすることができますが、一部機能が正常に動作しない場合があります。</p>' );
        }

        if ( $use_ga === '1' ) {
            $n = count($sections) + 1;
            $sections[] = array( 'num' => '第' . $n . '条', 'title' => 'Googleアナリティクスについて', 'body' => '<p>当サイトでは、アクセス解析のためGoogleアナリティクスを使用しています。Googleアナリティクスはトラフィックデータの収集のためCookieを使用しますが、このデータは匿名で収集されており、個人を特定するものではありません。この機能はCookieを無効にすることで収集を拒否することができます。詳しくは<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Googleのプライバシーポリシー</a>をご確認ください。</p>' );
        }

        if ( $use_disclaimer === '1' ) {
            $n = count($sections) + 1;
            $sections[] = array( 'num' => '第' . $n . '条', 'title' => '免責事項', 'body' => '<p>当サイトに掲載する情報は可能な限り正確を期しておりますが、その内容の正確性・完全性を保証するものではありません。当サイトの利用により生じたいかなる損害についても、運営者は責任を負いません。また、見積もりシミュレーターに表示される金額はあくまで目安であり、確定金額ではありません。外部リンク先のサービス・コンテンツについても責任を負いかねます。</p>' );
        }

        $sections[] = array( 'num' => 'お問い合わせ', 'title' => 'プライバシーポリシーに関するお問い合わせ', 'body' => '<p>本ポリシーに関するお問い合わせは下記までご連絡ください。<br>運営者：' . $owner . '<br>メールアドレス：' . $email . '</p>' );

        $date_str = '<p><small>制定日：' . date_i18n('Y年m月j日') . '</small></p>';
    }

    // HTML組み立て（Article形式）
    $html  = '<div class="en-privacy-header">';
    $html .= '<div class="en-privacy-label">' . ( $lang === 'en' ? 'Privacy Policy' : 'Privacy Policy' ) . '</div>';
    $html .= '<div class="en-privacy-title">' . ( $lang === 'en' ? 'Privacy Policy' : 'プライバシーポリシー' ) . '</div>';
    $html .= '<div class="en-privacy-date">' . ( $lang === 'en' ? 'Last updated: ' . date_i18n('F j, Y') : '制定日：' . date_i18n('Y年m月j日') ) . '</div>';
    $html .= '</div>';
    $html .= '<div class="en-privacy-intro">' . $intro . '</div>';
    $html .= '<div class="en-privacy-body">';
    foreach ( $sections as $sec ) {
        $html .= '<div class="en-privacy-section">';
        $html .= '<div class="en-privacy-section-num">' . esc_html($sec['num']) . '</div>';
        $html .= '<div class="en-privacy-section-title">' . esc_html($sec['title']) . '</div>';
        $html .= '<div class="en-privacy-section-body">' . $sec['body'] . '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';

    if ( $custom ) {
        $html .= '<div class="en-privacy-section en-privacy-custom">' . wp_kses_post( $custom ) . '</div>';
    }

    return $html;
}
