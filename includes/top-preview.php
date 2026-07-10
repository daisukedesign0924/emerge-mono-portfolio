<?php
/**
 * TOP Editor ライブプレビュー
 *
 * 保存前のトップページ入力内容を、実際のフロント描画経路そのままで
 * プレビューするための仕組み。保存処理（en_options の update_option）には
 * 一切触れず、ユーザーごとの transient に一時オプションを退避し、
 * フロント側で emono_options フィルターに重ねて表示する。
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'EMONO_TOP_PREVIEW_NONCE' ) ) {
	define( 'EMONO_TOP_PREVIEW_NONCE', 'emono_top_preview' );
}

/**
 * プレビュー用 transient のキー（ユーザー単位）。
 */
function emono_top_preview_transient_key( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	return 'emono_top_preview_' . $user_id;
}

/**
 * TOP Editor のフォーム内容から、保存せずにプレビュー用オプションを組み立てる AJAX。
 *
 * 保存経路（emono_save_top_layout_settings フィルター）をそのまま再利用するため、
 * フォームのフィールド名は本番保存と完全に一致していれば追加実装は不要。
 */
add_action( 'wp_ajax_emono_top_preview', 'emono_ajax_top_preview' );
function emono_ajax_top_preview() {
	if ( ! check_ajax_referer( EMONO_TOP_PREVIEW_NONCE, 'nonce', false ) ) {
		wp_send_json_error( 'bad_nonce', 403 );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'forbidden', 403 );
	}

	// 生の保存済みオプションを基点にする（emono_get_options は使わない＝プレビュー再帰回避）。
	$opts = get_option( 'en_options', array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}

	$top_layout  = isset( $_POST['top_layout'] ) ? sanitize_key( wp_unslash( $_POST['top_layout'] ) ) : 'mono';
	$top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array( 'mono' => array() );
	$opts['top_layout'] = isset( $top_layouts[ $top_layout ] ) ? $top_layout : 'mono';

	// 本番と同じレイアウト固有サニタイズ経路を通す（$_POST を読む）。
	$opts = apply_filters( 'emono_save_top_layout_settings', $opts, $opts['top_layout'] );

	set_transient( emono_top_preview_transient_key(), $opts, 15 * MINUTE_IN_SECONDS );

	wp_send_json_success();
}

/**
 * 汎用ライブプレビュー AJAX。
 *
 * TOP Editor 以外の設定画面（デザイン設定など）でも、保存せずに
 * フロントプレビューへ反映するための共通エンドポイント。scope ごとに
 * emono_live_preview_build フィルターで一時オプションを組み立てる。
 */
add_action( 'wp_ajax_emono_live_preview', 'emono_ajax_live_preview' );
function emono_ajax_live_preview() {
	if ( ! check_ajax_referer( EMONO_TOP_PREVIEW_NONCE, 'nonce', false ) ) {
		wp_send_json_error( 'bad_nonce', 403 );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'forbidden', 403 );
	}

	$opts = get_option( 'en_options', array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}

	$scope = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( $_POST['scope'] ) ) : '';
	$opts  = apply_filters( 'emono_live_preview_build', $opts, $scope );

	set_transient( emono_top_preview_transient_key(), $opts, 15 * MINUTE_IN_SECONDS );

	wp_send_json_success();
}

/**
 * 各 scope のプレビュー内容を、本番保存と同じサニタイズ関数で組み立てる。
 * 対応表: scope => 適用関数。拡張プラグインは同じ形でフィルターを足せる。
 */
add_filter( 'emono_live_preview_build', 'emono_live_preview_build_scopes', 10, 2 );
function emono_live_preview_build_scopes( $opts, $scope ) {
	$map = array(
		'design'  => 'emono_apply_design_from_post',
		'general' => 'emono_apply_general_from_post',
		'nav'     => 'emono_apply_nav_from_post',
		'profile' => 'emono_apply_profile_from_post',
	);

	if ( isset( $map[ $scope ] ) && function_exists( $map[ $scope ] ) ) {
		// $_POST はプレビュー用フォーム値。各 apply 関数は unslash 済み配列を想定。
		$src = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Nonce verified in emono_ajax_live_preview; each field sanitized in helper.
		return call_user_func( $map[ $scope ], $opts, $src );
	}

	if ( $scope === 'top' ) {
		// トップレイアウト（MONO TOP 等）は既存の保存フィルターを再利用。
		$top_layout  = isset( $_POST['top_layout'] ) ? sanitize_key( wp_unslash( $_POST['top_layout'] ) ) : 'mono'; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in emono_ajax_live_preview.
		$top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array( 'mono' => array() );
		$opts['top_layout'] = isset( $top_layouts[ $top_layout ] ) ? $top_layout : 'mono';
		return apply_filters( 'emono_save_top_layout_settings', $opts, $opts['top_layout'] );
	}

	return $opts;
}

/**
 * フロント側でのみ、プレビュー要求時に一時オプションを重ねる。
 */
add_filter( 'emono_options', 'emono_top_preview_apply_options' );
function emono_top_preview_apply_options( $opts ) {
	// 管理画面側（TOP Editor のフォーム初期値など）には一切干渉しない。
	if ( is_admin() ) {
		return $opts;
	}
	if ( empty( $_GET['emono_top_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified just below.
		return $opts;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return $opts;
	}
	$nonce = isset( $_GET['emono_preview_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['emono_preview_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, EMONO_TOP_PREVIEW_NONCE ) ) {
		return $opts;
	}

	$preview = get_transient( emono_top_preview_transient_key() );
	if ( ! is_array( $preview ) ) {
		return $opts;
	}

	if ( ! is_array( $opts ) ) {
		$opts = array();
	}

	// プレビュー値を優先しつつ、他の設定キーは維持する。
	return array_merge( $opts, $preview );
}

/**
 * プレビュー表示中は WordPress 管理バーを隠す（純粋なフロント表示にする）。
 */
add_filter( 'show_admin_bar', 'emono_top_preview_hide_admin_bar', 99 );
function emono_top_preview_hide_admin_bar( $show ) {
	if ( ! is_admin() && ! empty( $_GET['emono_top_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only guard.
		return false;
	}
	return $show;
}

/**
 * プレビュー中は、サイト内リンクにプレビュー用パラメータを引き継ぐ。
 *
 * これにより iframe 内でトップ以外のページへ移動しても、
 * 管理バー非表示などのプレビュー表示が維持される。
 */
add_action( 'wp_enqueue_scripts', 'emono_top_preview_enqueue_persist', 20 );
function emono_top_preview_enqueue_persist() {
	if ( is_admin() || empty( $_GET['emono_top_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only guard.
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! wp_script_is( 'emerge-mono', 'enqueued' ) ) {
		return;
	}

	$nonce = wp_json_encode( wp_create_nonce( EMONO_TOP_PREVIEW_NONCE ) );
	$js = "(function(){\n"
		. "  var n=" . $nonce . ";\n"
		. "  document.addEventListener('click',function(e){\n"
		. "    var a=e.target && e.target.closest ? e.target.closest('a[href]') : null;\n"
		. "    if(!a || a.target==='_blank') return;\n"
		. "    var url; try{ url=new URL(a.href, location.href); }catch(_){ return; }\n"
		. "    if(url.origin!==location.origin) return;\n"
		. "    if(url.pathname.indexOf('/wp-admin')===0 || url.pathname.indexOf('/wp-login')===0) return;\n"
		. "    if(url.searchParams.get('emono_top_preview')) return;\n"
		. "    url.searchParams.set('emono_top_preview','1');\n"
		. "    url.searchParams.set('emono_preview_nonce',n);\n"
		. "    a.href=url.toString();\n"
		. "  }, true);\n"
		. "}());";
	wp_add_inline_script( 'emerge-mono', $js );
}

/**
 * プレビュー表示中はキャッシュ・検索インデックスを避ける。
 */
add_action( 'template_redirect', 'emono_top_preview_no_cache' );
function emono_top_preview_no_cache() {
	if ( is_admin() || empty( $_GET['emono_top_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only guard.
		return;
	}
	// DONOTCACHEPAGE は WordPress のキャッシュ系プラグイン共通の標準定数。
	// 意図的に非プレフィックス（プレフィックスすると効かない）。
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard cache-plugin constant; must not be prefixed.
	}
	nocache_headers();
}
