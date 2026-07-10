<?php
/**
 * Live Editor
 *
 * 設定を編集しながら、実際のフロント表示をライブプレビューできる統合画面。
 * 第一弾としてデザイン設定を扱う。プレビューは TOP Editor と同じ
 * 一時オプション上書き（emono_options フィルター）方式を使う。
 * 将来、編集対象（scope）を増やして拡張できる構造にしている。
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'emono_live_editor_menu', 21 );
function emono_live_editor_menu() {
	// エディター系UI（.ene-* / editor.css）に依存するため、
	// TOP Editor と同じくエディター機能が有効なときだけ表示する。
	$opts = get_option( 'en_options', array() );
	$enabled = isset( $opts['editor_enabled'] ) ? $opts['editor_enabled'] : '1';
	if ( $enabled !== '1' ) {
		return;
	}
	add_menu_page(
		__( 'Live Editor', 'emerge-mono' ),
		__( 'Live Editor', 'emerge-mono' ),
		'manage_options',
		'ene-live',
		'emono_live_editor_page',
		'dashicons-visibility',
		30
	);
}

function emono_live_editor_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$opts      = emono_get_options();
	$front_url = home_url( '/' );
	?>
	<div class="ene-wrap ene-top-editor ene-live-editor">
		<div class="ene-header">
			<div class="ene-header-inner">
				<div class="ene-header-title"><?php esc_html_e( 'Live Editor', 'emerge-mono' ); ?></div>
				<div class="ene-header-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=emerge-mono' ) ); ?>" class="ene-back-btn"><?php esc_html_e( 'Back to Settings', 'emerge-mono' ); ?></a>
					<a href="<?php echo esc_url( $front_url ); ?>" target="_blank" class="ene-new-btn"><?php esc_html_e( 'Preview', 'emerge-mono' ); ?></a>
				</div>
			</div>
		</div>

		<div class="ene-top-editor-grid">
			<main class="ene-top-editor-main ene-live-editor-main">
				<div class="ene-live-editor-scope-title"><?php esc_html_e( 'Design', 'emerge-mono' ); ?></div>
				<?php emono_admin_tab_design( $opts ); ?>
			</main>

			<aside class="ene-top-editor-sidebar">
				<div class="ene-side-section">
					<div class="ene-side-title">
						<?php esc_html_e( 'Preview', 'emerge-mono' ); ?>
						<span class="ene-top-preview-status" data-preview-status hidden></span>
					</div>
					<div class="ene-top-preview-frame">
						<iframe id="ene-live-preview-iframe" src="<?php echo esc_url( $front_url ); ?>" title="<?php echo esc_attr__( 'Live preview', 'emerge-mono' ); ?>"></iframe>
					</div>
					<div class="ene-field-desc"><?php esc_html_e( 'The preview updates automatically as you edit. Your changes are not saved until you press Save.', 'emerge-mono' ); ?></div>
					<a href="<?php echo esc_url( $front_url ); ?>" target="_blank" class="ene-table-btn"><?php esc_html_e( 'Open in new tab', 'emerge-mono' ); ?></a>
				</div>
			</aside>
		</div>
	</div>
	<?php
	$preview_nonce = wp_create_nonce( EMONO_TOP_PREVIEW_NONCE );
	wp_localize_script( 'emerge-mono-admin', 'emonoLivePreview', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'action'      => 'emono_live_preview',
		'scope'       => 'design',
		'nonce'       => $preview_nonce,
		'previewBase' => $front_url,
		'i18n'        => array(
			'updating' => __( 'Updating…', 'emerge-mono' ),
			'live'     => __( 'Live', 'emerge-mono' ),
			'error'    => __( 'Preview failed', 'emerge-mono' ),
		),
	) );
	wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_LIVE_EDITOR_JS'
(function() {
    var root = document.querySelector('.ene-live-editor');
    if (!root) { return; }
    var cfg = window.emonoLivePreview;
    var form = root.querySelector('form');
    var iframe = document.getElementById('ene-live-preview-iframe');
    if (!cfg || !form || !iframe || typeof window.fetch === 'undefined' || typeof FormData === 'undefined') {
        return;
    }
    var i18n = cfg.i18n || {};
    var statusEl = root.querySelector('[data-preview-status]');
    var frame = root.querySelector('.ene-top-preview-frame');
    var timer = null;
    var reqToken = 0;

    var BASE_W = 1280, BASE_H = 800;
    function fitPreview() {
        if (!frame) { return; }
        var w = frame.clientWidth;
        if (!w) { return; }
        var scale = w / BASE_W;
        iframe.style.transform = 'scale(' + scale + ')';
        frame.style.height = (BASE_H * scale) + 'px';
    }
    window.addEventListener('resize', fitPreview);
    iframe.addEventListener('load', fitPreview);
    fitPreview();

    function setStatus(text, state) {
        if (!statusEl) { return; }
        if (!text) { statusEl.hidden = true; statusEl.textContent = ''; return; }
        statusEl.hidden = false;
        statusEl.textContent = text;
        statusEl.setAttribute('data-state', state || '');
    }

    function previewUrl() {
        var base = cfg.previewBase || '/';
        var sep = base.indexOf('?') === -1 ? '?' : '&';
        return base + sep + 'emono_top_preview=1&emono_preview_nonce=' +
            encodeURIComponent(cfg.nonce) + '&t=' + Date.now();
    }

    function pushPreview() {
        var token = ++reqToken;
        setStatus(i18n.updating || 'Updating…', 'busy');
        var data = new FormData(form);
        data.delete('en_action');
        data.delete('en_nonce');
        data.append('action', cfg.action);
        data.append('scope', cfg.scope);
        data.append('nonce', cfg.nonce);
        window.fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function(res) {
            return res.json();
        }).then(function(json) {
            if (token !== reqToken) { return; }
            if (json && json.success) {
                try { iframe.src = previewUrl(); } catch (e) {}
                setStatus(i18n.live || 'Live', 'ok');
            } else {
                setStatus(i18n.error || 'Preview failed', 'error');
            }
        }).catch(function() {
            if (token !== reqToken) { return; }
            setStatus(i18n.error || 'Preview failed', 'error');
        });
    }

    function schedulePreview() {
        if (timer) { clearTimeout(timer); }
        timer = setTimeout(pushPreview, 700);
    }

    form.addEventListener('input', schedulePreview);
    form.addEventListener('change', schedulePreview);
    pushPreview();
}());
EMONO_LIVE_EDITOR_JS
	);
}
