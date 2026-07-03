<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function en_admin_tab_terms( $opts ) {
    en_admin_notice();
    $lang            = isset( $opts['terms_lang'] )            ? $opts['terms_lang']            : 'ja';
    $owner           = isset( $opts['legal_owner'] )           ? $opts['legal_owner']           : '';
    $site            = isset( $opts['legal_site'] )            ? $opts['legal_site']            : get_bloginfo('name');
    $email           = isset( $opts['legal_email'] )           ? $opts['legal_email']           : get_bloginfo('admin_email');
    $use_disclaimer  = isset( $opts['terms_use_disclaimer'] )  ? $opts['terms_use_disclaimer']  : '1';
    $custom          = isset( $opts['terms_custom'] )          ? $opts['terms_custom']          : '';
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'en_save_terms', 'en_nonce' ); ?>
        <input type="hidden" name="en_terms_save" value="1">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">
        <div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Basic Info', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'The terms of service text is auto-generated from the info you enter.', 'emerge-mono-portfolio' ); ?></div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Display Language', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:12px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px">
                        <input type="radio" name="terms_lang" value="ja" <?php checked( $lang, 'ja' ); ?>>
                        <?php esc_html_e( 'Japanese', 'emerge-mono-portfolio' ); ?>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px">
                        <input type="radio" name="terms_lang" value="en" <?php checked( $lang, 'en' ); ?>>
                        English
                    </label>
                </div>
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Operator Name (personal or business)', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="legal_owner" value="<?php echo esc_attr( $owner ); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'e.g. Taro Yamada', 'emerge-mono-portfolio' ); ?>">
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Site Name', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="legal_site" value="<?php echo esc_attr( $site ); ?>" class="en-field-input">
            </div>

            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Contact Email', 'emerge-mono-portfolio' ); ?></label>
                <input type="email" name="legal_email" value="<?php echo esc_attr( $email ); ?>" class="en-field-input">
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Sections to Include', 'emerge-mono-portfolio' ); ?></div>
            <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="terms_use_disclaimer" value="1" <?php checked( $use_disclaimer, '1' ); ?>>
                    <span><?php esc_html_e( 'Include a disclaimer', 'emerge-mono-portfolio' ); ?></span>
                </label>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Additional Notes (optional)', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <textarea name="terms_custom" rows="5" class="en-field-input" style="width:100%;resize:vertical" placeholder="<?php echo esc_attr__( 'e.g. Refund policy for paid services, etc. ...', 'emerge-mono-portfolio' ); ?>"><?php echo esc_textarea( $custom ); ?></textarea>
            </div>
        </div>

        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
        </div>

        <div style="position:sticky;top:32px;">
            <div style="font-size:11px;letter-spacing:.3em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:10px;">Preview</div>
            <div style="font-size:11px;color:rgba(255,255,255,.3);margin-bottom:10px;"><code>[emerge_mono_terms]</code> Changes are reflected in real time.</div>
            <div id="en-terms-preview" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);padding:20px;line-height:1.9;font-size:12px;max-height:80vh;overflow-y:auto;">
                <?php echo en_terms_generate_html( $lang, $owner, $site, $email, $use_disclaimer, $custom ); ?>
            </div>
        </div>
        </div>

    </form>

    <script>
    (function(){
        var timer = null;
        var fields = ['terms_lang', 'legal_owner', 'legal_site', 'legal_email', 'terms_use_disclaimer', 'terms_custom'];
        function updatePreview() {
            var data = new FormData();
            data.append('action', 'en_preview_terms');
            data.append('nonce', '<?php echo wp_create_nonce("en_preview_terms"); ?>');
            fields.forEach(function(name) {
                var els = document.querySelectorAll('[name="' + name + '"]');
                els.forEach(function(el) {
                    if (el.type === 'checkbox') {
                        if (el.checked) data.append(name, '1');
                    } else if (el.type === 'radio') {
                        if (el.checked) data.append(name, el.value);
                    } else {
                        data.append(name, el.value);
                    }
                });
            });
            fetch(ajaxurl, { method: 'POST', body: data })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if (res.success) document.getElementById('en-terms-preview').innerHTML = res.data;
                });
        }
        fields.forEach(function(name) {
            document.querySelectorAll('[name="' + name + '"]').forEach(function(el) {
                el.addEventListener('change', function(){ clearTimeout(timer); timer = setTimeout(updatePreview, 300); });
                if (el.tagName === 'TEXTAREA' || el.type === 'text' || el.type === 'email') {
                    el.addEventListener('input', function(){ clearTimeout(timer); timer = setTimeout(updatePreview, 600); });
                }
            });
        });
    })();
    </script>
    <?php
}

function en_terms_generate_html( $lang, $owner, $site, $email, $use_disclaimer = '1', $custom = '' ) {
    $owner = $owner ? esc_html($owner) : '（運営者名）';
    $site  = $site  ? esc_html($site)  : '（サイト名）';
    $email = $email ? esc_html($email) : '（メールアドレス）';

    $sections = array();

    if ( $lang === 'en' ) {
        $intro = '<p>These Terms of Service ("Terms") govern your use of ' . $site . ' ("the Site") operated by ' . $owner . '. By using the Site, you agree to these Terms.</p>';

        $sections[] = array('num'=>'Article 01','title'=>'Acceptance of Terms',        'body'=>'<p>By accessing or using the Site, you agree to be bound by these Terms. If you do not agree, please do not use the Site.</p>');
        $sections[] = array('num'=>'Article 02','title'=>'Use of the Site',            'body'=>'<p>You agree to use the Site only for lawful purposes and in a manner that does not infringe the rights of others or restrict their use of the Site.</p>');
        $sections[] = array('num'=>'Article 03','title'=>'Intellectual Property',      'body'=>'<p>All content on the Site, including text, images, and other materials, is the property of ' . $owner . ' and is protected by applicable intellectual property laws. Unauthorized reproduction is prohibited.</p>');
        $sections[] = array('num'=>'Article 04','title'=>'Estimates & Pricing',        'body'=>'<p>Any prices shown in the estimate simulator are approximate and for reference only. They do not constitute a binding offer or contract. Final pricing will be confirmed after consultation.</p>');

        if ( $use_disclaimer === '1' ) {
            $sections[] = array('num'=>'Article 05','title'=>'Disclaimer',             'body'=>'<p>The Site is provided "as is" without warranties of any kind. ' . $owner . ' shall not be liable for any damages arising from the use or inability to use the Site or its content.</p>');
        }

        $n = count($sections) + 1;
        $sections[] = array('num'=>'Article '.sprintf('%02d',$n),'title'=>'Changes to Terms','body'=>'<p>We reserve the right to update these Terms at any time. Continued use of the Site after changes constitutes acceptance of the new Terms.</p>');
        $sections[] = array('num'=>'Contact','title'=>'Contact',                       'body'=>'<p>For questions about these Terms, please contact:<br>Operator: '.$owner.'<br>Email: '.$email.'</p>');

    } else {
        $intro = '<p>' . $owner . '（以下「運営者」）が運営する ' . $site . '（以下「当サイト」）のご利用にあたり、以下の利用規約（以下「本規約」）に同意いただいた上でご利用ください。</p>';

        $sections[] = array('num'=>'第1条','title'=>'適用範囲',         'body'=>'<p>本規約は、当サイトのすべてのコンテンツ・サービスのご利用に適用されます。</p>');
        $sections[] = array('num'=>'第2条','title'=>'禁止事項',         'body'=>'<p>当サイトのご利用にあたり、以下の行為を禁止します。法令または公序良俗に違反する行為、当サイトの運営を妨害する行為、他のユーザーまたは第三者の権利を侵害する行為、その他運営者が不適切と判断する行為。</p>');
        $sections[] = array('num'=>'第3条','title'=>'著作権',           'body'=>'<p>当サイトに掲載するコンテンツ（文章・画像・デザイン等）の著作権は運営者に帰属します。無断転載・複製を禁じます。</p>');
        $sections[] = array('num'=>'第4条','title'=>'見積もりについて', 'body'=>'<p>見積もりシミュレーターに表示される金額はあくまで目安であり、確定金額ではありません。正式な料金はお問い合わせ後にご案内いたします。</p>');

        if ( $use_disclaimer === '1' ) {
            $n = count($sections) + 1;
            $sections[] = array('num'=>'第'.$n.'条','title'=>'免責事項','body'=>'<p>当サイトのコンテンツについて、その正確性・完全性を保証するものではありません。当サイトのご利用により生じた損害について、運営者は責任を負いません。</p>');
        }

        $n = count($sections) + 1;
        $sections[] = array('num'=>'第'.$n.'条','title'=>'規約の変更','body'=>'<p>運営者は、必要に応じて本規約を変更することがあります。変更後も当サイトをご利用の場合は、変更後の規約に同意したものとみなします。</p>');
        $sections[] = array('num'=>'お問い合わせ','title'=>'お問い合わせ','body'=>'<p>本規約に関するお問い合わせは下記までご連絡ください。<br>運営者：'.$owner.'<br>メールアドレス：'.$email.'</p>');
    }

    $html  = '<div class="en-privacy-header">';
    $html .= '<div class="en-privacy-label">Terms of Service</div>';
    $html .= '<div class="en-privacy-title">' . ( $lang === 'en' ? 'Terms of Service' : '利用規約' ) . '</div>';
    $html .= '<div class="en-privacy-date">' . ( $lang === 'en' ? 'Last updated: '.date('F j, Y') : '制定日：'.date('Y年m月j日') ) . '</div>';
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
        $html .= '<div class="en-privacy-section en-privacy-custom">' . wp_kses_post($custom) . '</div>';
    }

    return $html;
}
