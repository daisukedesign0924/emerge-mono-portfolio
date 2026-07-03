<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_enqueue_scripts', 'emono_admin_enqueue' );
function emono_admin_enqueue( $hook ) {
    $is_wizard = ( strpos( $hook, 'en-setup-wizard' ) !== false )
              || ( isset($_GET['page']) && $_GET['page'] === 'en-setup-wizard' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    $is_settings = ( strpos( $hook, 'emerge-mono-portfolio' ) !== false );

    if ( ! $is_settings && ! $is_wizard ) return;

    // WPメディアライブラリ（設定画面・ウィザード両方で必要）
    wp_enqueue_media();

    // 管理画面タブ用のCSS/JSはウィザードには注入しない（ウィザードは独自スタイル）
    if ( $is_wizard && ! $is_settings ) return;

    // インラインでCSS・JS注入
    add_action( 'admin_head', 'emono_admin_inline_style' );
    add_action( 'admin_footer', 'emono_admin_inline_script' );
}

function emono_admin_inline_style() { ?>
<style>
/* ── Emerge Mono Admin UI ── */
#wpcontent { background: #0d0d0d; }
#wpbody-content { padding-bottom: 0; }

.en-admin-wrap {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #e8e8e8;
    min-height: 100vh;
    background: #0d0d0d;
}

/* ── ヘッダー ── */
.en-admin-header {
    background: #111;
    border-bottom: 1px solid rgba(255,255,255,.08);
    padding: 16px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.en-admin-logo { display: flex; align-items: center; gap: 14px; }
.en-admin-logo-mark {
    width: 36px; height: 36px; background: #000;
    display: flex; align-items: center; justify-content: center;
    border-radius: 6px; overflow: hidden;
    border: 1px solid rgba(255,255,255,.12);
}
.en-admin-logo-mark img { width: 100%; height: 100%; object-fit: contain; display: block; }
.en-admin-title { font-size: 15px; font-weight: 600; color: #fff; letter-spacing: .02em; }
.en-admin-version { font-size: 10px; color: rgba(255,255,255,.25); letter-spacing: .05em; margin-top: 1px; }
.en-admin-theme-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 6px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.14);
    color: #fff; text-decoration: none; font-size: 12px; line-height: 1;
    transition: background .2s, border-color .2s;
    white-space: nowrap;
}
.en-admin-theme-btn:hover { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.28); color: #fff; }
.en-admin-theme-btn:focus { color: #fff; }
.en-admin-theme-btn-label { font-weight: 600; letter-spacing: .04em; }
.en-admin-theme-btn-sep { opacity: .4; }
.en-admin-theme-btn-name { opacity: .7; }
.en-admin-theme-btn.is-disabled { cursor: default; opacity: .55; }
.en-admin-theme-btn.is-disabled:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); }

/* ── ボディ ── */
.en-admin-body {
    display: grid;
    grid-template-columns: 200px 1fr;
    min-height: calc(100vh - 69px);
}

/* ── サイドバー ── */
.en-admin-sidebar {
    background: #111;
    border-right: 1px solid rgba(255,255,255,.06);
    padding: 20px 0;
}
.en-admin-nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 20px;
    font-size: 12px; color: rgba(255,255,255,.4);
    text-decoration: none; transition: all .2s;
    border-left: 2px solid transparent;
}
.en-admin-nav-item:hover { color: rgba(255,255,255,.7); background: rgba(255,255,255,.04); }
.en-admin-nav-item.active { color: #fff; border-left-color: #fff; background: rgba(255,255,255,.05); }
.en-admin-nav-icon { font-size: 14px; }

/* ── コンテンツ ── */
.en-admin-content { padding: 32px 36px; max-width: 1200px; }

/* ── セクション ── */
.en-admin-section {
    background: #161616;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 10px;
    padding: 24px;
    margin-bottom: 20px;
}
.en-admin-section-title {
    font-size: 11px; font-weight: 600;
    letter-spacing: .12em; text-transform: uppercase;
    color: rgba(255,255,255,.35);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

/* ── フィールド ── */
.en-field-group { margin-bottom: 18px; }
.en-field-label {
    display: block; font-size: 11px; font-weight: 500;
    color: rgba(255,255,255,.5); margin-bottom: 7px; letter-spacing: .04em;
}
.en-field-input, .en-field-textarea, .en-field-select {
    width: 100%;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 6px; color: #e8e8e8;
    font-size: 13px; padding: 9px 12px;
    outline: none; transition: border-color .2s;
    font-family: inherit;
}
.en-field-input:focus, .en-field-textarea:focus, .en-field-select:focus {
    border-color: rgba(255,255,255,.35);
}
.en-field-textarea { height: 100px; resize: vertical; }
.en-field-select { height: 36px; cursor: pointer; }
.en-field-desc { font-size: 11px; color: rgba(255,255,255,.22); margin-bottom: 14px; line-height: 1.6; }
.en-field-desc code {
    background: rgba(255,255,255,.08); padding: 1px 6px;
    border-radius: 3px; font-size: 11px; color: rgba(255,255,255,.6);
}

/* ── ボタン類 ── */
.en-save-btn {
    background: #fff; color: #000;
    border: none; border-radius: 7px;
    font-size: 12px; font-weight: 600;
    letter-spacing: .05em; padding: 11px 28px;
    cursor: pointer; transition: opacity .2s;
    margin-top: 4px;
}
.en-save-btn:hover { opacity: .85; }

.en-add-btn {
    background: none; border: 1px solid rgba(255,255,255,.15);
    border-radius: 6px; color: rgba(255,255,255,.4);
    font-size: 11px; padding: 7px 14px;
    cursor: pointer; transition: all .2s; margin-top: 12px;
}
.en-add-btn:hover { border-color: rgba(255,255,255,.4); color: #fff; }

.en-remove-btn {
    background: none; border: 1px solid rgba(255,100,100,.2);
    border-radius: 5px; color: rgba(255,100,100,.5);
    font-size: 11px; padding: 6px 10px; cursor: pointer;
    transition: all .2s; white-space: nowrap; flex-shrink: 0;
}
.en-remove-btn:hover { border-color: rgba(255,100,100,.6); color: rgba(255,100,100,.9); }

.en-media-btn {
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
    border-radius: 5px; color: rgba(255,255,255,.5);
    font-size: 11px; padding: 8px 14px; cursor: pointer;
    transition: all .2s; white-space: nowrap; flex-shrink: 0;
}
.en-media-btn:hover { background: rgba(255,255,255,.14); color: #fff; }

/* ── 行レイアウト ── */
.en-sns-row, .en-nav-row, .en-cpt-row, .en-field-row {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.05);
    border-radius: 7px; padding: 14px;
    margin-bottom: 10px;
}

/* ── コンタクトフィールド行 ── */
.en-contact-field-row {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 2vh;
    position: relative;
}
.en-contact-field-row + .en-contact-field-row {
    margin-top: 2vh;
}

/* ── TOPボタン行 ── */
.en-top-btn-row {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 2vh;
}

/* ── 通知 ── */
.en-admin-notice {
    padding: 11px 16px; border-radius: 7px;
    font-size: 12px; margin-bottom: 20px;
}
.en-admin-notice.success {
    background: rgba(100,200,100,.08);
    border: 1px solid rgba(100,200,100,.2);
    color: rgba(150,230,150,.8);
}
</style>
<?php
}

function emono_admin_inline_script() { ?>
<script>
// WPメディアライブラリを開く
function enOpenMedia(targetId) {
    var frame = wp.media({
        title: '<?php echo esc_js( __( 'Select Image', 'emerge-mono-portfolio' ) ); ?>',
        button: { text: '<?php echo esc_js( __( 'Select', 'emerge-mono-portfolio' ) ); ?>' },
        multiple: false,
        library: { type: 'image' }
    });
    frame.on('select', function() {
        var att = frame.state().get('selection').first().toJSON();
        document.getElementById(targetId).value = att.url;
    });
    frame.open();
}
</script>
<?php }

// インラインCSS追加
add_action('admin_head', 'emono_mode_selector_style');
function emono_mode_selector_style() { ?>
<style>
.en-mode-label { display:flex; align-items:center; gap:12px; cursor:pointer; }
.en-mode-label input[type="radio"] { accent-color:#fff; width:16px; height:16px; flex-shrink:0; }
.en-mode-preview { display:flex; align-items:center; gap:14px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:8px; padding:12px 16px; flex:1; transition:all .2s; }
.en-mode-label:has(input:checked) .en-mode-preview { border-color:rgba(255,255,255,.4); background:rgba(255,255,255,.07); }
.en-mode-icon { font-size:22px; color:rgba(255,255,255,.5); width:28px; text-align:center; }
.en-mode-title { font-size:13px; color:rgba(255,255,255,.8); margin-bottom:3px; }
.en-mode-desc  { font-size:11px; color:rgba(255,255,255,.3); }
.en-mode-light .en-mode-icon { color:rgba(255,255,255,.7); }
.en-mode-auto  .en-mode-icon { color:rgba(255,255,255,.6); }
</style>
<?php }
