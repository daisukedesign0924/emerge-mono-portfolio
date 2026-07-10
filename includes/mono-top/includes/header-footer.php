<?php
/**
 * MONO TOP: Header / Footer タブ（デザイン編集への追加）。
 *
 * 本体（Emerge Mono - Portfolio 3.1.0+）が公開する拡張ポイントを使って、
 * デザイン編集画面に「Header」「Footer」タブを足し、設定パネル型で
 * ヘッダー／フッターの見た目を調整できるようにする。ライブプレビュー対応。
 *
 * 保存先は本体と同じ en_options（emono_options 経由でフロントに反映）。
 * フロントでは本体のヘッダー(.en-header / #en-header)・フッター(.en-footer)の
 * マークアップに対してインラインCSSで上書きする。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────────────
 * 1) タブ（scope）を登録
 * ───────────────────────────────────────────── */
add_filter( 'emono_design_editor_scopes', 'emmt_hf_register_scopes' );
function emmt_hf_register_scopes( $scopes ) {
	$scopes['header'] = array( 'label' => __( 'Header', 'emerge-mono' ), 'preview' => 'home' );
	$scopes['footer'] = array( 'label' => __( 'Footer', 'emerge-mono' ), 'preview' => 'home' );
	return $scopes;
}

/* ─────────────────────────────────────────────
 * 2) フォーム描画
 * ───────────────────────────────────────────── */
add_action( 'emono_design_editor_render_scope', 'emmt_hf_render_scope', 10, 2 );
function emmt_hf_render_scope( $scope, $opts ) {
	if ( $scope === 'header' ) {
		emmt_hf_render_header_form( $opts );
	} elseif ( $scope === 'footer' ) {
		emmt_hf_render_footer_form( $opts );
	}
}

/**
 * 数値入力（px）。
 */
function emmt_hf_num_field( $label, $name, $opts, $default = '', $desc = '', $placeholder = '' ) {
	$val = isset( $opts[ $name ] ) ? $opts[ $name ] : $default;
	?>
	<div class="en-field-group">
		<label class="en-field-label"><?php echo esc_html( $label ); ?></label>
		<input type="number" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>" class="en-field-input" min="0" step="1" placeholder="<?php echo esc_attr( $placeholder ); ?>" style="max-width:160px">
		<?php if ( $desc ) : ?><div class="en-field-desc" style="margin-top:6px"><?php echo esc_html( $desc ); ?></div><?php endif; ?>
	</div>
	<?php
}

/**
 * トグル（チェックボックス）。
 */
function emmt_hf_toggle_field( $label, $name, $opts, $default = '0', $desc = '' ) {
	$val = isset( $opts[ $name ] ) ? $opts[ $name ] : $default;
	?>
	<div class="en-field-group">
		<label class="en-field-label" style="display:flex;align-items:center;gap:8px;cursor:pointer">
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $val, '1' ); ?>>
			<?php echo esc_html( $label ); ?>
		</label>
		<?php if ( $desc ) : ?><div class="en-field-desc" style="margin-top:6px"><?php echo esc_html( $desc ); ?></div><?php endif; ?>
	</div>
	<?php
}

function emmt_hf_render_header_form( $opts ) {
	?>
	<form method="post" action="" class="ene-top-editor-form">
		<?php wp_nonce_field( 'emmt_save_header', 'en_nonce' ); ?>
		<input type="hidden" name="en_action" value="header">

		<div class="en-admin-section">
			<div class="en-admin-section-title"><?php esc_html_e( 'Header Behavior', 'emerge-mono' ); ?></div>
			<?php
			emmt_hf_toggle_field( __( 'Sticky header (stays fixed on scroll)', 'emerge-mono' ), 'header_sticky', $opts, '0' );
			emmt_hf_toggle_field( __( 'Show bottom border', 'emerge-mono' ), 'header_border', $opts, '0' );
			?>
		</div>

		<div class="en-admin-section">
			<div class="en-admin-section-title"><?php esc_html_e( 'Spacing', 'emerge-mono' ); ?></div>
			<?php
			emmt_hf_num_field( __( 'Vertical padding (px)', 'emerge-mono' ), 'header_pad_y', $opts, '', '', '24' );
			emmt_hf_num_field( __( 'Horizontal padding (px)', 'emerge-mono' ), 'header_pad_x', $opts, '', '', '40' );
			?>
		</div>

		<div class="en-admin-section">
			<div class="en-admin-section-title"><?php esc_html_e( 'Navigation', 'emerge-mono' ); ?></div>
			<?php
			emmt_hf_num_field( __( 'Gap between menu items (px)', 'emerge-mono' ), 'header_nav_gap', $opts, '', '', '28' );
			emmt_hf_num_field( __( 'Menu font size (px)', 'emerge-mono' ), 'header_nav_size', $opts, '', '', '13' );
			emmt_hf_toggle_field( __( 'Uppercase menu labels', 'emerge-mono' ), 'header_nav_upper', $opts, '0' );
			?>
		</div>

		<div class="ene-actions" style="margin-top:20px">
			<button type="submit" class="ene-submit-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
		</div>
	</form>
	<?php
}

function emmt_hf_render_footer_form( $opts ) {
	$align = isset( $opts['footer_align'] ) ? $opts['footer_align'] : '';
	?>
	<form method="post" action="" class="ene-top-editor-form">
		<?php wp_nonce_field( 'emmt_save_footer', 'en_nonce' ); ?>
		<input type="hidden" name="en_action" value="footer">

		<div class="en-admin-section">
			<div class="en-admin-section-title"><?php esc_html_e( 'Layout', 'emerge-mono' ); ?></div>
			<?php emmt_hf_toggle_field( __( 'Show top border', 'emerge-mono' ), 'footer_border', $opts, '0' ); ?>
			<div class="en-field-group">
				<label class="en-field-label"><?php esc_html_e( 'Alignment', 'emerge-mono' ); ?></label>
				<select name="footer_align" class="en-field-input" style="max-width:200px">
					<option value="" <?php selected( $align, '' ); ?>><?php esc_html_e( 'Default (center)', 'emerge-mono' ); ?></option>
					<option value="left" <?php selected( $align, 'left' ); ?>><?php esc_html_e( 'Left', 'emerge-mono' ); ?></option>
					<option value="center" <?php selected( $align, 'center' ); ?>><?php esc_html_e( 'Center', 'emerge-mono' ); ?></option>
					<option value="right" <?php selected( $align, 'right' ); ?>><?php esc_html_e( 'Right', 'emerge-mono' ); ?></option>
				</select>
			</div>
			<?php
			emmt_hf_num_field( __( 'Vertical padding (px)', 'emerge-mono' ), 'footer_pad_y', $opts, '', '', '48' );
			emmt_hf_num_field( __( 'Gap between footer menu items (px)', 'emerge-mono' ), 'footer_menu_gap', $opts, '', '', '22' );
			emmt_hf_num_field( __( 'Copyright font size (px)', 'emerge-mono' ), 'footer_copy_size', $opts, '', '', '12' );
			?>
		</div>

		<div class="ene-actions" style="margin-top:20px">
			<button type="submit" class="ene-submit-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
		</div>
	</form>
	<?php
}

/* ─────────────────────────────────────────────
 * 3) 保存（nonce + サニタイズ）
 * ───────────────────────────────────────────── */
add_filter( 'emono_admin_save_nonce_map', 'emmt_hf_nonce_map' );
function emmt_hf_nonce_map( $map ) {
	$map['header'] = 'emmt_save_header';
	$map['footer'] = 'emmt_save_footer';
	return $map;
}

add_filter( 'emono_design_editor_save_scope_map', 'emmt_hf_save_scope_map', 10, 2 );
function emmt_hf_save_scope_map( $map, $action ) {
	$map['header'] = 'header';
	$map['footer'] = 'footer';
	return $map;
}

add_filter( 'emono_admin_save_action', 'emmt_hf_save_action', 10, 4 );
function emmt_hf_save_action( $result, $action, $opts, $src ) {
	if ( $action === 'header' ) {
		$opts = emmt_apply_header_from_post( $opts, $src );
		return array( 'handled' => true, 'opts' => $opts, 'tab' => 'header', 'update_options' => true );
	}
	if ( $action === 'footer' ) {
		$opts = emmt_apply_footer_from_post( $opts, $src );
		return array( 'handled' => true, 'opts' => $opts, 'tab' => 'footer', 'update_options' => true );
	}
	return $result;
}

/**
 * 数値のサニタイズ（空欄可＝上書きなし）。
 */
function emmt_hf_sanitize_num( $val ) {
	if ( ! isset( $val ) || $val === '' ) {
		return '';
	}
	return (string) absint( $val );
}

function emmt_apply_header_from_post( $opts, $src ) {
	$opts['header_sticky']    = ! empty( $src['header_sticky'] ) ? '1' : '0';
	$opts['header_border']    = ! empty( $src['header_border'] ) ? '1' : '0';
	$opts['header_nav_upper'] = ! empty( $src['header_nav_upper'] ) ? '1' : '0';
	$opts['header_pad_y']     = emmt_hf_sanitize_num( isset( $src['header_pad_y'] ) ? $src['header_pad_y'] : '' );
	$opts['header_pad_x']     = emmt_hf_sanitize_num( isset( $src['header_pad_x'] ) ? $src['header_pad_x'] : '' );
	$opts['header_nav_gap']   = emmt_hf_sanitize_num( isset( $src['header_nav_gap'] ) ? $src['header_nav_gap'] : '' );
	$opts['header_nav_size']  = emmt_hf_sanitize_num( isset( $src['header_nav_size'] ) ? $src['header_nav_size'] : '' );
	return $opts;
}

function emmt_apply_footer_from_post( $opts, $src ) {
	$opts['footer_border']    = ! empty( $src['footer_border'] ) ? '1' : '0';
	$align                    = isset( $src['footer_align'] ) ? sanitize_key( $src['footer_align'] ) : '';
	$opts['footer_align']     = in_array( $align, array( 'left', 'center', 'right' ), true ) ? $align : '';
	$opts['footer_pad_y']     = emmt_hf_sanitize_num( isset( $src['footer_pad_y'] ) ? $src['footer_pad_y'] : '' );
	$opts['footer_menu_gap']  = emmt_hf_sanitize_num( isset( $src['footer_menu_gap'] ) ? $src['footer_menu_gap'] : '' );
	$opts['footer_copy_size'] = emmt_hf_sanitize_num( isset( $src['footer_copy_size'] ) ? $src['footer_copy_size'] : '' );
	return $opts;
}

/* ─────────────────────────────────────────────
 * 4) ライブプレビュー
 * ───────────────────────────────────────────── */
add_filter( 'emono_live_preview_build', 'emmt_hf_live_preview_build', 10, 2 );
function emmt_hf_live_preview_build( $opts, $scope ) {
	if ( $scope !== 'header' && $scope !== 'footer' ) {
		return $opts;
	}
	// $_POST はプレビュー用フォーム値。nonce は本体の live_preview AJAX で検証済み。
	$src = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Nonce verified in base live-preview AJAX; each field sanitized in helper.
	if ( $scope === 'header' ) {
		return emmt_apply_header_from_post( $opts, $src );
	}
	return emmt_apply_footer_from_post( $opts, $src );
}

/* ─────────────────────────────────────────────
 * 5) フロント反映（インラインCSSで本体のヘッダー／フッターを上書き）
 * ───────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'emmt_hf_front_css', 30 );
function emmt_hf_front_css() {
	if ( ! function_exists( 'emono_get_options' ) ) {
		return;
	}
	$css = emmt_hf_build_css( emono_get_options() );
	if ( $css === '' ) {
		return;
	}
	// 本体スタイルの後に読み込む MONO TOP ハンドルに付与。
	$handle = wp_style_is( 'emerge-mono-mono-top', 'enqueued' ) ? 'emerge-mono-mono-top' : 'emerge-mono';
	wp_add_inline_style( $handle, $css );
}

/**
 * オプションから上書きCSSを生成（設定された項目のみ）。
 */
function emmt_hf_build_css( $opts ) {
	$out = '';

	// ── Header ──
	$header = '';
	if ( ! empty( $opts['header_sticky'] ) && $opts['header_sticky'] === '1' ) {
		$header .= 'position:sticky;top:0;z-index:1000;';
	}
	if ( isset( $opts['header_border'] ) && $opts['header_border'] === '1' ) {
		$header .= 'border-bottom:1px solid var(--en-border,rgba(255,255,255,.14));';
	}
	if ( isset( $opts['header_pad_y'] ) && $opts['header_pad_y'] !== '' ) {
		$header .= 'padding-top:' . absint( $opts['header_pad_y'] ) . 'px;padding-bottom:' . absint( $opts['header_pad_y'] ) . 'px;';
	}
	if ( isset( $opts['header_pad_x'] ) && $opts['header_pad_x'] !== '' ) {
		$header .= 'padding-left:' . absint( $opts['header_pad_x'] ) . 'px;padding-right:' . absint( $opts['header_pad_x'] ) . 'px;';
	}
	if ( $header !== '' ) {
		$out .= '#en-header.en-header{' . $header . '}';
	}
	if ( isset( $opts['header_nav_gap'] ) && $opts['header_nav_gap'] !== '' ) {
		$out .= '.en-header-nav{gap:' . absint( $opts['header_nav_gap'] ) . 'px;}';
	}
	$nav_item = '';
	if ( isset( $opts['header_nav_size'] ) && $opts['header_nav_size'] !== '' ) {
		$nav_item .= 'font-size:' . absint( $opts['header_nav_size'] ) . 'px;';
	}
	if ( ! empty( $opts['header_nav_upper'] ) && $opts['header_nav_upper'] === '1' ) {
		$nav_item .= 'text-transform:uppercase;letter-spacing:.08em;';
	}
	if ( $nav_item !== '' ) {
		$out .= '.en-header-nav .en-nav-item{' . $nav_item . '}';
	}

	// ── Footer ──
	$footer = '';
	if ( isset( $opts['footer_border'] ) && $opts['footer_border'] === '1' ) {
		$footer .= 'border-top:1px solid var(--en-border,rgba(255,255,255,.14));';
	}
	if ( ! empty( $opts['footer_align'] ) ) {
		$footer .= 'text-align:' . $opts['footer_align'] . ';';
	}
	if ( isset( $opts['footer_pad_y'] ) && $opts['footer_pad_y'] !== '' ) {
		$footer .= 'padding-top:' . absint( $opts['footer_pad_y'] ) . 'px;padding-bottom:' . absint( $opts['footer_pad_y'] ) . 'px;';
	}
	if ( $footer !== '' ) {
		$out .= '.en-footer{' . $footer . '}';
	}
	if ( ! empty( $opts['footer_align'] ) ) {
		$just = $opts['footer_align'] === 'left' ? 'flex-start' : ( $opts['footer_align'] === 'right' ? 'flex-end' : 'center' );
		$out .= '.en-footer .en-footer-menu{justify-content:' . $just . ';}';
	}
	if ( isset( $opts['footer_menu_gap'] ) && $opts['footer_menu_gap'] !== '' ) {
		$out .= '.en-footer .en-footer-menu{gap:' . absint( $opts['footer_menu_gap'] ) . 'px;}';
	}
	if ( isset( $opts['footer_copy_size'] ) && $opts['footer_copy_size'] !== '' ) {
		$out .= '.en-footer .en-footer-copy{font-size:' . absint( $opts['footer_copy_size'] ) . 'px;}';
	}

	return $out;
}
