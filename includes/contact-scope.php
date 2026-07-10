<?php
/**
 * お問い合わせ（Contact）フォームフィールドのデザイン編集スコープ。
 *
 * デザイン編集に「Contact」タブを追加し、フォームの項目（ラベル/ID/種別/
 * プレースホルダー/必須）をライブプレビュー付きで編集できるようにする。
 * バックエンド設定（送信メール・reCAPTCHA・同意など）は従来どおりプラグイン
 * 設定画面のまま。ここで扱うのはフォーム項目（en_contact_fields）だけ。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── フィールド取得（ライブプレビュー時は仮の値で差し替え） ── */
function emono_get_contact_fields() {
	$default = function_exists( 'emono_default_contact_fields' ) ? emono_default_contact_fields() : array();
	if ( function_exists( 'emono_get_options' ) ) {
		$opts = emono_get_options();
		if ( isset( $opts['__contact_fields_preview'] ) && is_array( $opts['__contact_fields_preview'] ) ) {
			return $opts['__contact_fields_preview'];
		}
	}
	return get_option( 'en_contact_fields', $default );
}

/* ── 送信フォーム → フィールド配列（サニタイズ） ── */
function emono_contact_fields_from_post( $src ) {
	$labels  = isset( $src['cf_label'] ) && is_array( $src['cf_label'] ) ? $src['cf_label'] : array();
	$keys    = isset( $src['cf_key'] ) && is_array( $src['cf_key'] ) ? $src['cf_key'] : array();
	$types   = isset( $src['cf_type'] ) && is_array( $src['cf_type'] ) ? $src['cf_type'] : array();
	$places  = isset( $src['cf_placeholder'] ) && is_array( $src['cf_placeholder'] ) ? $src['cf_placeholder'] : array();
	$reqs    = isset( $src['cf_required'] ) && is_array( $src['cf_required'] ) ? $src['cf_required'] : array();
	$opts_in = isset( $src['cf_options'] ) && is_array( $src['cf_options'] ) ? $src['cf_options'] : array();

	$fields = array();
	foreach ( $keys as $i => $key ) {
		$key = sanitize_key( $key );
		if ( ! $key ) {
			// Field ID 未入力でもプレビュー/保存に出るよう自動採番（ラベル→なければ連番）。
			$label_src = isset( $labels[ $i ] ) ? sanitize_key( $labels[ $i ] ) : '';
			$key = $label_src ? $label_src : ( 'field_' . ( (int) $i + 1 ) );
		}
		$type = sanitize_text_field( isset( $types[ $i ] ) ? $types[ $i ] : 'text' );
		$options = array();
		if ( in_array( $type, array( 'select', 'checkbox' ), true ) && ! empty( $opts_in[ $i ] ) ) {
			foreach ( explode( "\n", $opts_in[ $i ] ) as $line ) {
				$line = trim( $line );
				if ( $line !== '' ) {
					$options[] = sanitize_text_field( $line );
				}
			}
		}
		$fields[] = array(
			'key'         => $key,
			'label'       => sanitize_text_field( isset( $labels[ $i ] ) ? $labels[ $i ] : $key ),
			'type'        => $type,
			'placeholder' => sanitize_text_field( isset( $places[ $i ] ) ? $places[ $i ] : '' ),
			'required'    => isset( $reqs[ $i ] ) && $reqs[ $i ] === '1',
			'options'     => $options,
		);
	}
	return $fields;
}

/* ── [emerge_mono_contact] を含むページ（プレビュー先） ── */
function emono_contact_page_url() {
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => -1,
		's'              => '[emerge_mono_contact]',
		'fields'         => 'all',
	) );
	foreach ( $pages as $page ) {
		if ( strpos( $page->post_content, '[emerge_mono_contact]' ) !== false ) {
			return get_permalink( $page->ID );
		}
	}
	return home_url( '/' );
}

/* ── 1) スコープ登録 ── */
add_filter( 'emono_design_editor_scopes', 'emono_contact_register_scope' );
function emono_contact_register_scope( $scopes ) {
	$scopes['contact'] = array( 'label' => __( 'Contact', 'emerge-mono' ), 'preview' => 'home' );
	return $scopes;
}

/* ── 2) プレビューURL ── */
add_filter( 'emono_design_editor_preview_url', 'emono_contact_preview_url', 10, 2 );
function emono_contact_preview_url( $url, $scope ) {
	return ( $scope === 'contact' ) ? emono_contact_page_url() : $url;
}

/* ── 3) フォーム描画（フィールド・リピーター） ── */
function emono_contact_field_types() {
	return array(
		'text'     => __( 'Text (single line)', 'emerge-mono' ),
		'textarea' => __( 'Text (multi-line)', 'emerge-mono' ),
		'email'    => __( 'Email', 'emerge-mono' ),
		'tel'      => __( 'Phone', 'emerge-mono' ),
		'select'   => __( 'Dropdown', 'emerge-mono' ),
		'checkbox' => __( 'Checkbox', 'emerge-mono' ),
	);
}

function emono_contact_render_field_row( $field ) {
	$types   = emono_contact_field_types();
	$key     = isset( $field['key'] ) ? $field['key'] : '';
	$label   = isset( $field['label'] ) ? $field['label'] : '';
	$type    = isset( $field['type'] ) ? $field['type'] : 'text';
	$place   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$req     = ! empty( $field['required'] );
	$options = isset( $field['options'] ) && is_array( $field['options'] ) ? implode( "\n", $field['options'] ) : '';
	$opt_hide = in_array( $type, array( 'select', 'checkbox' ), true ) ? '' : 'style="display:none"';
	?>
	<div class="en-admin-section emono-cf-row" style="margin-bottom:12px">
		<div class="en-field-group" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
			<div style="flex:2;min-width:160px">
				<label class="en-field-label"><?php esc_html_e( 'Label', 'emerge-mono' ); ?></label>
				<input type="text" name="cf_label[]" value="<?php echo esc_attr( $label ); ?>" class="en-field-input">
			</div>
			<div style="flex:1;min-width:140px">
				<label class="en-field-label"><?php esc_html_e( 'Field type', 'emerge-mono' ); ?></label>
				<select name="cf_type[]" class="en-field-input emono-cf-type">
					<?php foreach ( $types as $tk => $tl ) : ?>
						<option value="<?php echo esc_attr( $tk ); ?>" <?php selected( $type, $tk ); ?>><?php echo esc_html( $tl ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button type="button" class="ene-table-btn emono-cf-remove" style="color:#e88"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
		</div>
		<div class="en-field-group" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
			<div style="flex:1;min-width:120px">
				<label class="en-field-label"><?php esc_html_e( 'Field ID', 'emerge-mono' ); ?></label>
				<input type="text" name="cf_key[]" value="<?php echo esc_attr( $key ); ?>" class="en-field-input" placeholder="name">
			</div>
			<div style="flex:2;min-width:160px">
				<label class="en-field-label"><?php esc_html_e( 'Placeholder', 'emerge-mono' ); ?></label>
				<input type="text" name="cf_placeholder[]" value="<?php echo esc_attr( $place ); ?>" class="en-field-input">
			</div>
			<div style="flex:1;min-width:120px">
				<label class="en-field-label"><?php esc_html_e( 'Required', 'emerge-mono' ); ?></label>
				<select name="cf_required[]" class="en-field-input">
					<option value="1" <?php selected( $req, true ); ?>><?php esc_html_e( 'Required', 'emerge-mono' ); ?></option>
					<option value="0" <?php selected( $req, false ); ?>><?php esc_html_e( 'Optional', 'emerge-mono' ); ?></option>
				</select>
			</div>
		</div>
		<div class="en-field-group emono-cf-options" <?php echo $opt_hide; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static attribute string. ?> style="margin-top:8px">
			<label class="en-field-label"><?php esc_html_e( 'Options (one per line, for Dropdown/Checkbox)', 'emerge-mono' ); ?></label>
			<textarea name="cf_options[]" class="en-field-input" rows="3"><?php echo esc_textarea( $options ); ?></textarea>
		</div>
	</div>
	<?php
}

add_action( 'emono_design_editor_render_scope', 'emono_contact_render_scope', 10, 2 );
function emono_contact_render_scope( $scope, $opts ) {
	if ( $scope !== 'contact' ) {
		return;
	}
	$fields = emono_get_contact_fields();
	?>
	<form method="post" action="" class="ene-top-editor-form">
		<?php wp_nonce_field( 'emono_save_contact_fields', 'en_nonce' ); ?>
		<input type="hidden" name="en_action" value="contact_fields">

		<div class="en-admin-section">
			<div class="en-admin-section-title"><?php esc_html_e( 'Contact Form Fields', 'emerge-mono' ); ?></div>
			<div class="en-field-desc" style="margin-bottom:12px"><?php esc_html_e( 'Add, remove, and reorder the form fields. Backend settings (email, reCAPTCHA, consent) stay in the plugin settings.', 'emerge-mono' ); ?></div>
			<div id="emono-cf-list">
				<?php foreach ( $fields as $field ) : emono_contact_render_field_row( $field ); endforeach; ?>
			</div>
			<button type="button" class="en-add-btn" id="emono-cf-add">+ <?php esc_html_e( 'Add Field', 'emerge-mono' ); ?></button>
			<template id="emono-cf-template"><?php emono_contact_render_field_row( array( 'key' => '', 'label' => '', 'type' => 'text', 'placeholder' => '', 'required' => false, 'options' => array() ) ); ?></template>
		</div>

		<div class="ene-actions" style="margin-top:20px">
			<button type="submit" class="ene-submit-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
		</div>
	</form>
	<script>
	(function(){
		var root = document.querySelector('.ene-design-editor');
		if (!root) { return; }
		var list = root.querySelector('#emono-cf-list');
		var addBtn = root.querySelector('#emono-cf-add');
		var tpl = root.querySelector('#emono-cf-template');
		function fireInput(){ var f = list ? list.closest('form') : null; if (f) { f.dispatchEvent(new Event('input', {bubbles:true})); } }
		function wireRow(row){
			var rm = row.querySelector('.emono-cf-remove');
			if (rm) { rm.addEventListener('click', function(){ row.remove(); fireInput(); }); }
			var typeSel = row.querySelector('.emono-cf-type');
			var optBox = row.querySelector('.emono-cf-options');
			if (typeSel && optBox) {
				typeSel.addEventListener('change', function(){
					optBox.style.display = (typeSel.value === 'select' || typeSel.value === 'checkbox') ? '' : 'none';
					fireInput();
				});
			}
		}
		if (list) { list.querySelectorAll('.emono-cf-row').forEach(wireRow); }
		if (addBtn && tpl && list) {
			addBtn.addEventListener('click', function(){
				var node = tpl.content.firstElementChild.cloneNode(true);
				list.appendChild(node);
				wireRow(node);
				fireInput();
			});
		}
	})();
	</script>
	<?php
}

/* ── 4) 保存 ── */
add_filter( 'emono_admin_save_nonce_map', 'emono_contact_nonce_map' );
function emono_contact_nonce_map( $map ) {
	$map['contact_fields'] = 'emono_save_contact_fields';
	return $map;
}

add_filter( 'emono_design_editor_save_scope_map', 'emono_contact_save_scope_map', 10, 2 );
function emono_contact_save_scope_map( $map, $action ) {
	$map['contact_fields'] = 'contact';
	return $map;
}

add_filter( 'emono_admin_save_action', 'emono_contact_save_action', 10, 4 );
function emono_contact_save_action( $result, $action, $opts, $src ) {
	if ( $action === 'contact_fields' ) {
		$fields = emono_contact_fields_from_post( $src );
		update_option( 'en_contact_fields', $fields );
		// en_options 自体は変更しないが、保存フローに乗せるため handled を返す。
		return array( 'handled' => true, 'opts' => $opts, 'tab' => 'contact', 'update_options' => false );
	}
	return $result;
}

/* ── 5) ライブプレビュー ── */
add_filter( 'emono_live_preview_build', 'emono_contact_live_preview_build', 10, 2 );
function emono_contact_live_preview_build( $opts, $scope ) {
	if ( $scope !== 'contact' ) {
		return $opts;
	}
	// $_POST はプレビュー用フォーム値。nonce は本体の live_preview AJAX で検証済み。
	$src = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Nonce verified in base live-preview AJAX; each field sanitized in helper.
	$opts['__contact_fields_preview'] = emono_contact_fields_from_post( $src );
	return $opts;
}
