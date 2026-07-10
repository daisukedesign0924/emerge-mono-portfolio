<?php
/**
 * About ページのデザイン編集スコープ（セクションビルダー）。
 *
 * デザイン編集に「About」タブを追加し、MONO TOP と同じセクションビルダーで
 * About ページ（[emerge_mono_about]）の中身を組めるようにする。
 * データは en_options['about_sections']（MONO TOP と同形のセクション配列）。
 *
 * About ページが作成されていない場合はタブ自体を表示しない（不要な人には出さない）。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * [emerge_mono_about] を含む公開/下書きページを探し、その情報を返す。無ければ null。
 */
function emono_about_page() {
	static $cache = false;
	if ( $cache !== false ) {
		return $cache;
	}
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => -1,
		's'              => '[emerge_mono_about]',
		'fields'         => 'all',
	) );
	foreach ( $pages as $page ) {
		if ( strpos( $page->post_content, '[emerge_mono_about]' ) !== false ) {
			$cache = $page;
			return $cache;
		}
	}
	$cache = null;
	return $cache;
}

/* ── 1) スコープ登録（About ページがある時だけ） ── */
add_filter( 'emono_design_editor_scopes', 'emono_about_register_scope' );
function emono_about_register_scope( $scopes ) {
	if ( emono_about_page() ) {
		$scopes['about'] = array( 'label' => __( 'About', 'emerge-mono' ), 'preview' => 'home' );
	}
	return $scopes;
}

/* ── 2) プレビューURL（About ページ） ── */
add_filter( 'emono_design_editor_preview_url', 'emono_about_preview_url', 10, 2 );
function emono_about_preview_url( $url, $scope ) {
	if ( $scope === 'about' ) {
		$page = emono_about_page();
		if ( $page ) {
			return get_permalink( $page->ID );
		}
	}
	return $url;
}

/* ── 3) フォーム描画（セクションビルダー） ── */
add_action( 'emono_design_editor_render_scope', 'emono_about_render_scope', 10, 2 );
function emono_about_render_scope( $scope, $opts ) {
	if ( $scope !== 'about' ) {
		return;
	}
	if ( ! function_exists( 'emono_mono_top_render_builder' ) ) {
		echo '<p>' . esc_html__( 'Section engine not loaded.', 'emerge-mono' ) . '</p>';
		return;
	}
	$sections = isset( $opts['about_sections'] ) && is_array( $opts['about_sections'] ) ? $opts['about_sections'] : array();
	?>
	<form method="post" action="" class="ene-top-editor-form">
		<?php wp_nonce_field( 'emono_save_about', 'en_nonce' ); ?>
		<input type="hidden" name="en_action" value="about">

		<div class="en-admin-section">
			<div class="en-admin-section-title"><?php esc_html_e( 'About Sections', 'emerge-mono' ); ?></div>
			<div class="en-field-desc" style="margin-bottom:12px"><?php esc_html_e( 'Build the About page by stacking sections. The same sections as MONO TOP are available.', 'emerge-mono' ); ?></div>
			<?php emono_mono_top_render_builder( array( 'mono_top_sections' => $sections ) ); ?>
		</div>

		<div class="ene-actions" style="margin-top:20px">
			<button type="submit" class="ene-submit-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
		</div>
	</form>
	<?php
}

/* ── 4) 保存 ── */
add_filter( 'emono_admin_save_nonce_map', 'emono_about_nonce_map' );
function emono_about_nonce_map( $map ) {
	$map['about'] = 'emono_save_about';
	return $map;
}

add_filter( 'emono_design_editor_save_scope_map', 'emono_about_save_scope_map', 10, 2 );
function emono_about_save_scope_map( $map, $action ) {
	$map['about'] = 'about';
	return $map;
}

add_filter( 'emono_admin_save_action', 'emono_about_save_action', 10, 4 );
function emono_about_save_action( $result, $action, $opts, $src ) {
	if ( $action === 'about' ) {
		$opts = emono_about_apply_from_post( $opts, $src );
		return array( 'handled' => true, 'opts' => $opts, 'tab' => 'about', 'update_options' => true );
	}
	return $result;
}

/**
 * 送信フォーム → about_sections（MONO TOP のサニタイズを再利用）。
 */
function emono_about_apply_from_post( $opts, $src ) {
	if ( function_exists( 'emono_mono_top_sections_from_post' ) ) {
		$opts['about_sections'] = emono_mono_top_sections_from_post( $src );
	}
	return $opts;
}

/* ── 5) ライブプレビュー ── */
add_filter( 'emono_live_preview_build', 'emono_about_live_preview_build', 10, 2 );
function emono_about_live_preview_build( $opts, $scope ) {
	if ( $scope !== 'about' ) {
		return $opts;
	}
	// $_POST はプレビュー用フォーム値。nonce は本体の live_preview AJAX で検証済み。
	$src = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Nonce verified in base live-preview AJAX; each field sanitized in engine.
	return emono_about_apply_from_post( $opts, $src );
}
