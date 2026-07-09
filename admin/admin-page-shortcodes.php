<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_shortcodes() {
    $shortcodes = array(
        array(
            'code'  => '[emerge_mono_top]',
            'title' => __( 'Selected Top Layout', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays the top page layout selected in the plugin settings.', 'emerge-mono-portfolio' ),
        ),
    );

    if ( function_exists( 'emono_get_top_layouts' ) ) {
        foreach ( emono_get_top_layouts() as $layout ) {
            if ( empty( $layout['shortcode'] ) ) {
                continue;
            }
            $shortcodes[] = array(
                'code'  => '[' . sanitize_key( $layout['shortcode'] ) . ']',
                'title' => isset( $layout['label'] ) ? $layout['label'] : sanitize_key( $layout['shortcode'] ),
                'desc'  => isset( $layout['description'] ) ? $layout['description'] : __( 'Displays this top layout directly, regardless of the selected top page layout setting.', 'emerge-mono-portfolio' ),
            );
        }
    }

    $shortcodes = array_merge( $shortcodes, array(
        array(
            'code'  => '[emerge_mono_about]',
            'title' => __( 'Profile', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays the profile image, name, title, bio, and social links. Reflects the Profile settings.', 'emerge-mono-portfolio' ),
        ),
        array(
            'code'  => '[emerge_mono_works]',
            'title' => __( 'Works', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays works in a grid layout with category filtering. Added works appear automatically.', 'emerge-mono-portfolio' ),
        ),
        array(
            'code'  => '[emerge_mono_contact]',
            'title' => __( 'Contact', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays the contact form. Reflects the settings in the "Contact Form" tab.', 'emerge-mono-portfolio' ),
        ),
        array(
            'code'  => '[emerge_mono_news]',
            'title' => __( 'News', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays standard WordPress posts. Useful for a blog or news page. You can set the count with per_page="10", e.g. [emerge_mono_news per_page="10"]', 'emerge-mono-portfolio' ),
        ),
        array(
            'code'  => '[emerge_mono_privacy]',
            'title' => __( 'Privacy Policy Page', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays the privacy policy. Text is auto-generated from the operator name and email in the "Privacy Policy" settings.', 'emerge-mono-portfolio' ),
        ),
        array(
            'code'  => '[emerge_mono_terms]',
            'title' => __( 'Terms of Service Page', 'emerge-mono-portfolio' ),
            'desc'  => __( 'Displays the terms of service. Text is auto-generated from the operator name and email in the "Terms of Service" settings.', 'emerge-mono-portfolio' ),
        ),
    ) );
    ?>
    <div class="en-admin-section">
        <div class="en-admin-section-title"><?php esc_html_e( 'Shortcodes', 'emerge-mono-portfolio' ); ?></div>
        <div class="en-field-desc" style="margin-bottom:20px;line-height:1.9">
            <?php esc_html_e( 'Paste a shortcode into the WordPress page editor to display its content on that page.', 'emerge-mono-portfolio' ); ?><br>
            <?php esc_html_e( 'Use the Copy button on the right to copy it to your clipboard.', 'emerge-mono-portfolio' ); ?>
        </div>
        <?php foreach ( $shortcodes as $sc ) : ?>
        <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:20px;margin-bottom:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:10px">
                <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.8)"><?php echo esc_html($sc['title']); ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <code style="flex:1;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:10px 14px;font-size:13px;color:rgba(255,255,255,.85);font-family:'Courier New',monospace;word-break:break-all"><?php echo esc_html($sc['code']); ?></code>
                <button type="button"
                    class="en-copy-btn"
                    data-code="<?php echo esc_attr($sc['code']); ?>"
                    onclick="enCopyShortcode(this)"
                    style="flex-shrink:0;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:6px;color:rgba(255,255,255,.5);font-size:11px;padding:8px 16px;cursor:pointer;transition:all .2s;white-space:nowrap">
                    📋 Copy
                </button>
            </div>
            <div style="font-size:12px;color:rgba(255,255,255,.3);line-height:1.7"><?php echo esc_html($sc['desc']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="en-admin-section">
        <div class="en-admin-section-title"><?php esc_html_e( 'How to Use', 'emerge-mono-portfolio' ); ?></div>
        <div style="font-size:13px;color:rgba(255,255,255,.4);line-height:2">
            <div style="margin-bottom:12px">
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 1</span>
                <?php esc_html_e( 'WordPress Admin → Pages → Add New', 'emerge-mono-portfolio' ); ?>
            </div>
            <div style="margin-bottom:12px">
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 2</span>
                <?php esc_html_e( 'Enter a page title (e.g. About)', 'emerge-mono-portfolio' ); ?>
            </div>
            <div style="margin-bottom:12px">
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 3</span>
                <?php esc_html_e( 'Paste the shortcode in the content area (e.g. [emerge_mono_about])', 'emerge-mono-portfolio' ); ?>
            </div>
            <div>
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 4</span>
                <?php esc_html_e( 'Click Publish to finish', 'emerge-mono-portfolio' ); ?>
            </div>
        </div>
    </div>

    <?php
    wp_localize_script( 'emerge-mono-admin', 'emonoShortcodeSettings', array(
        'copiedLabel' => '✓ ' . __( 'Copied', 'emerge-mono-portfolio' ),
    ) );
    wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_SHORTCODE_COPY_JS'
window.enCopyShortcode = function(btn) {
        var code = btn.dataset.code;
        var copiedLabel = (window.emonoShortcodeSettings && window.emonoShortcodeSettings.copiedLabel) || 'Copied';
        navigator.clipboard.writeText(code).then(function() {
            var orig = btn.innerHTML;
            btn.innerHTML = copiedLabel;
            btn.style.background = 'rgba(100,200,100,.15)';
            btn.style.borderColor = 'rgba(100,200,100,.3)';
            btn.style.color = 'rgba(150,230,150,.8)';
            setTimeout(function() {
                btn.innerHTML = orig;
                btn.style.background = '';
                btn.style.borderColor = '';
                btn.style.color = '';
            }, 2000);
        });
};
EMONO_SHORTCODE_COPY_JS
    );
    ?>
    <?php
}
