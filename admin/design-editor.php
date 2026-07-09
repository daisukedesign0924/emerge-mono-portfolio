<?php
/**
 * Design Editor（デザイン編集）
 *
 * フロント（訪問者が見る部分）の編集を一箇所に集約した統合画面。
 * 旧「TOP Editor」と「Live Editor」を統合し、以下の scope を
 * 左サブナビで切り替えながら、右側の実フロントプレビューで確認できる。
 *
 *   top      … トップページレイアウト（MONO TOP / ミニマルトップ）
 *   design   … 配色・カラーモード・フォント
 *   general  … ブランディング（サイト名・ロゴ・トップボタン）
 *   nav      … メニュー（ヘッダー/フッター）
 *   profile  … プロフィール（About）
 *
 * プレビューは保存前の一時オプション上書き（emono_options フィルター）方式。
 * 各 scope のフォームは既存の設定タブ描画関数をそのまま再利用する。
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Design Editor が扱う scope の定義。
 */
function emono_design_editor_scopes() {
	return array(
		'top'     => array( 'label' => __( 'Top Page', 'emerge-mono-portfolio' ),   'preview' => 'home' ),
		'design'  => array( 'label' => __( 'Design', 'emerge-mono-portfolio' ),     'preview' => 'home' ),
		'general' => array( 'label' => __( 'Branding', 'emerge-mono-portfolio' ),   'preview' => 'home' ),
		'nav'     => array( 'label' => __( 'Menu', 'emerge-mono-portfolio' ),       'preview' => 'home' ),
		'profile' => array( 'label' => __( 'Profile', 'emerge-mono-portfolio' ),    'preview' => 'about' ),
	);
}

/**
 * [emerge_mono_about] を含む公開ページの URL（プロフィールプレビュー用）。
 * 無ければトップページ。
 */
function emono_design_editor_about_url() {
	$pages = get_pages( array( 'post_status' => 'publish' ) );
	foreach ( $pages as $page ) {
		if ( strpos( $page->post_content, '[emerge_mono_about]' ) !== false ) {
			return get_permalink( $page->ID );
		}
	}
	return home_url( '/' );
}

function emono_design_editor_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$opts    = emono_get_options();
	$scopes  = emono_design_editor_scopes();
	$scope   = isset( $_GET['scope'] ) ? sanitize_key( wp_unslash( $_GET['scope'] ) ) : 'top'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter.
	if ( ! isset( $scopes[ $scope ] ) ) {
		$scope = 'top';
	}

	$home_url    = home_url( '/' );
	$preview_url = ( $scopes[ $scope ]['preview'] === 'about' ) ? emono_design_editor_about_url() : $home_url;
	$saved       = isset( $_GET['saved'] ) ? sanitize_key( wp_unslash( $_GET['saved'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success flag.
	?>
	<div class="ene-wrap ene-top-editor ene-design-editor">
		<div class="ene-header">
			<div class="ene-header-inner">
				<div class="ene-header-title"><?php esc_html_e( 'Design Editor', 'emerge-mono-portfolio' ); ?></div>
				<div class="ene-header-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=emerge-mono-portfolio' ) ); ?>" class="ene-back-btn"><?php esc_html_e( 'Back to Settings', 'emerge-mono-portfolio' ); ?></a>
					<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="ene-new-btn"><?php esc_html_e( 'Preview', 'emerge-mono-portfolio' ); ?></a>
				</div>
			</div>
		</div>

		<nav class="ene-design-scope-nav">
			<?php foreach ( $scopes as $key => $info ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ene-top&scope=' . $key ) ); ?>"
				   class="ene-design-scope-btn <?php echo esc_attr( $scope === $key ? 'active' : '' ); ?>">
					<?php echo esc_html( $info['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="ene-top-editor-grid">
			<main class="ene-top-editor-main ene-design-editor-main">
				<?php emono_design_editor_render_scope( $scope, $opts ); ?>
			</main>

			<aside class="ene-top-editor-sidebar">
				<div class="ene-side-section">
					<div class="ene-side-title">
						<?php esc_html_e( 'Preview', 'emerge-mono-portfolio' ); ?>
						<span class="ene-top-preview-status" data-preview-status hidden></span>
					</div>
					<div class="ene-top-preview-frame">
						<iframe id="ene-design-preview-iframe" src="<?php echo esc_url( $preview_url ); ?>" title="<?php echo esc_attr__( 'Live preview', 'emerge-mono-portfolio' ); ?>"></iframe>
					</div>
					<div class="ene-field-desc"><?php esc_html_e( 'The preview updates automatically as you edit. Your changes are not saved until you press Save.', 'emerge-mono-portfolio' ); ?></div>
					<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="ene-table-btn"><?php esc_html_e( 'Open in new tab', 'emerge-mono-portfolio' ); ?></a>
				</div>
			</aside>
		</div>
	</div>
	<?php
	$preview_nonce = wp_create_nonce( EMONO_TOP_PREVIEW_NONCE );
	wp_localize_script( 'emerge-mono-admin', 'emonoLivePreview', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'action'      => 'emono_live_preview',
		'scope'       => $scope,
		'nonce'       => $preview_nonce,
		'previewBase' => $preview_url,
		'i18n'        => array(
			'updating' => __( 'Updating…', 'emerge-mono-portfolio' ),
			'live'     => __( 'Live', 'emerge-mono-portfolio' ),
			'error'    => __( 'Preview failed', 'emerge-mono-portfolio' ),
		),
	) );
	wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_DESIGN_EDITOR_JS'
(function() {
    var root = document.querySelector('.ene-design-editor');
    if (!root) { return; }
    var cfg = window.emonoLivePreview || {};

    // トップレイアウト選択（top scope）で、選択レイアウトの設定欄を切り替える。
    function updateTopLayout() {
        var selected = root.querySelector('input[name="top_layout"]:checked');
        var layout = selected ? selected.value : '';
        root.querySelectorAll('.ene-top-layout-card').forEach(function(card) {
            var input = card.querySelector('input[name="top_layout"]');
            card.classList.toggle('active', input && input.value === layout);
        });
        root.querySelectorAll('[data-top-layout-settings]').forEach(function(section) {
            section.hidden = section.getAttribute('data-top-layout-settings') !== layout;
        });
    }
    root.querySelectorAll('input[name="top_layout"]').forEach(function(input) {
        input.addEventListener('change', updateTopLayout);
    });
    updateTopLayout();

    // ── ライブプレビュー ──
    var form = root.querySelector('.ene-design-editor-main form');
    var iframe = document.getElementById('ene-design-preview-iframe');
    if (!form || !iframe || typeof window.fetch === 'undefined' || typeof FormData === 'undefined') {
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
    // MONO TOP のセクション・ビルダー操作は Emerge Mono - MONO TOP 拡張の builder.js が担当。
}());
EMONO_DESIGN_EDITOR_JS
	);
}

/**
 * scope ごとのフォームを描画。既存の設定タブ描画関数を再利用する。
 */
function emono_design_editor_render_scope( $scope, $opts ) {
	switch ( $scope ) {
		case 'design':
			if ( function_exists( 'emono_admin_tab_design' ) ) {
				emono_admin_tab_design( $opts );
			}
			break;

		case 'general':
			if ( function_exists( 'emono_admin_tab_general' ) ) {
				emono_admin_tab_general( $opts );
			}
			break;

		case 'nav':
			if ( function_exists( 'emono_admin_tab_nav' ) ) {
				emono_admin_tab_nav( $opts );
			}
			break;

		case 'profile':
			if ( function_exists( 'emono_admin_tab_profile' ) ) {
				emono_admin_tab_profile( $opts );
			}
			break;

		case 'top':
		default:
			emono_design_editor_render_top( $opts );
			break;
	}
}

/**
 * top scope（トップレイアウト）のフォームを描画。
 */
function emono_design_editor_render_top( $opts ) {
	$top_layout  = isset( $opts['top_layout'] ) ? sanitize_key( $opts['top_layout'] ) : 'mono';
	$top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array(
		'mono' => array( 'label' => __( 'Minimal Top', 'emerge-mono-portfolio' ), 'description' => '' ),
	);
	if ( ! isset( $top_layouts[ $top_layout ] ) ) {
		$top_layout = 'mono';
	}
	?>
	<form method="post" action="" class="ene-top-editor-form">
		<?php wp_nonce_field( 'en_save_top_editor', 'en_nonce' ); ?>
		<input type="hidden" name="en_action" value="top_editor">

		<section class="ene-type-bar ene-top-editor-layouts">
			<div class="ene-type-bar-label"><?php esc_html_e( 'Top Page Layout', 'emerge-mono-portfolio' ); ?></div>
			<div class="ene-type-bar-btns">
				<?php foreach ( $top_layouts as $layout_key => $layout ) : ?>
					<?php
					$layout_key  = sanitize_key( $layout_key );
					$label       = isset( $layout['label'] ) ? $layout['label'] : $layout_key;
					$description = isset( $layout['description'] ) ? $layout['description'] : '';
					?>
					<label class="ene-type-bar-btn ene-top-layout-card <?php echo esc_attr( $top_layout === $layout_key ? 'active' : '' ); ?>">
						<input type="radio" name="top_layout" value="<?php echo esc_attr( $layout_key ); ?>" <?php checked( $top_layout, $layout_key ); ?>>
						<span class="dashicons dashicons-layout"></span>
						<span>
							<span class="ene-top-layout-title"><?php echo esc_html( $label ); ?></span>
							<?php if ( $description ) : ?>
								<span class="ene-type-desc"><?php echo esc_html( $description ); ?></span>
							<?php endif; ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="ene-top-editor-empty" data-top-layout-settings="mono" <?php if ( $top_layout !== 'mono' ) : ?>hidden<?php endif; ?>>
			<div class="ene-side-section">
				<div class="ene-side-title"><?php esc_html_e( 'Minimal Top', 'emerge-mono-portfolio' ); ?></div>
				<p><?php esc_html_e( 'Minimal Top uses the shared site name, logo, tagline, and top buttons from Branding.', 'emerge-mono-portfolio' ); ?></p>
			</div>
		</section>

		<?php do_action( 'emono_top_layout_settings', $top_layout, $opts ); ?>

		<div class="ene-actions" style="margin-top:20px">
			<button type="submit" class="ene-submit-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
		</div>
	</form>
	<?php
}
