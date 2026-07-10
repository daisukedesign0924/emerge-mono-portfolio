<?php
/**
 * 投稿タイプの表示/非表示（管理画面）。
 *
 * Emerge Mono 本体および拡張プラグインが登録した投稿タイプを一覧化し、
 * それぞれ「表示 / 非表示」を選べるようにする。非表示にすると:
 *   - ダッシュボード左メニューから消える
 *   - ページ管理の一覧から関連ページ定義が消える
 * （フロント側の公開URLは維持。管理画面での見え方だけを制御する。）
 *
 * 拡張は `emono_managed_post_types` フィルターで自分の投稿タイプを登録し、
 * `emono_is_post_type_hidden( $slug )` で非表示状態を確認して自メニューを隠す。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 管理対象の投稿タイプ一覧。
 * 返り値: slug => array( 'label' => 表示名, 'menu_slugs' => array( 隠すメニューslug... ) )
 */
function emono_managed_post_types() {
	$types = array(
		'en_work' => array(
			'label'      => emono_opt( 'work_label', 'Works' ),
			'menu_slugs' => array( 'edit.php?post_type=en_work', 'ene-works' ),
		),
		'en_news' => array(
			'label'      => emono_opt( 'news_label', 'News' ),
			'menu_slugs' => array( 'edit.php?post_type=en_news', 'ene-news' ),
		),
	);
	return apply_filters( 'emono_managed_post_types', $types );
}

/** 非表示に設定された投稿タイプ slug の配列。 */
function emono_get_hidden_post_types() {
	$hidden = emono_opt( 'hidden_post_types', array() );
	return is_array( $hidden ) ? $hidden : array();
}

/** 指定投稿タイプが非表示か。 */
function emono_is_post_type_hidden( $slug ) {
	return in_array( $slug, emono_get_hidden_post_types(), true );
}

/* ── 設定UI（「投稿タイプ設定」タブ内に表示） ── */
function emono_render_post_type_visibility( $opts ) {
	$types  = emono_managed_post_types();
	$hidden = emono_get_hidden_post_types();
	?>
	<div class="en-admin-section">
		<div class="en-admin-section-title"><?php esc_html_e( 'Post Type Visibility', 'emerge-mono' ); ?></div>
		<div class="en-field-desc" style="margin-bottom:16px">
			<?php esc_html_e( 'Show or hide each Emerge Mono post type in the admin. Hidden types disappear from the dashboard menu and the Page Manager list (front-end URLs are unaffected).', 'emerge-mono' ); ?>
		</div>
		<?php foreach ( $types as $slug => $info ) :
			$label     = isset( $info['label'] ) ? $info['label'] : $slug;
			$is_hidden = in_array( $slug, $hidden, true );
		?>
		<div class="en-field-group" style="display:flex;gap:12px;align-items:center;justify-content:space-between;max-width:520px">
			<div>
				<span style="font-weight:600"><?php echo esc_html( $label ); ?></span>
				<code style="font-size:11px;opacity:.5;margin-left:8px"><?php echo esc_html( $slug ); ?></code>
			</div>
			<select name="hidden_pt[<?php echo esc_attr( $slug ); ?>]" class="en-field-input" style="max-width:160px">
				<option value="show" <?php selected( $is_hidden, false ); ?>><?php esc_html_e( 'Show', 'emerge-mono' ); ?></option>
				<option value="hide" <?php selected( $is_hidden, true ); ?>><?php esc_html_e( 'Hide', 'emerge-mono' ); ?></option>
			</select>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * 送信フォーム（hidden_pt[slug]=show|hide）→ 非表示 slug 配列。
 * 管理対象に含まれるものだけを対象にする。
 */
function emono_post_type_visibility_from_post( $src ) {
	$managed = array_keys( emono_managed_post_types() );
	$in      = isset( $src['hidden_pt'] ) && is_array( $src['hidden_pt'] ) ? $src['hidden_pt'] : array();
	$hidden  = array();
	foreach ( $managed as $slug ) {
		$val = isset( $in[ $slug ] ) ? sanitize_key( $in[ $slug ] ) : 'show';
		if ( $val === 'hide' ) {
			$hidden[] = $slug;
		}
	}
	return $hidden;
}

/* ── 非表示タイプのダッシュボードメニューを除去 ── */
add_action( 'admin_menu', 'emono_hide_post_type_menus', 99999 );
function emono_hide_post_type_menus() {
	$types  = emono_managed_post_types();
	$hidden = emono_get_hidden_post_types();
	foreach ( $hidden as $slug ) {
		if ( ! isset( $types[ $slug ] ) ) {
			continue;
		}
		$slugs = isset( $types[ $slug ]['menu_slugs'] ) ? $types[ $slug ]['menu_slugs'] : array();
		foreach ( $slugs as $menu_slug ) {
			remove_menu_page( $menu_slug );
		}
	}
}

/* ── ページ管理の一覧から、非表示タイプ関連のページ定義を除去 ── */
add_filter( 'emono_page_defs', 'emono_hide_post_type_page_defs' );
function emono_hide_post_type_page_defs( $defs ) {
	// ショートコード → 投稿タイプ の対応。
	$sc_pt = array(
		'[emerge_mono_works]' => 'en_work',
		'[emerge_mono_news]'  => 'en_news',
	);
	$sc_pt = apply_filters( 'emono_page_def_post_types', $sc_pt );

	foreach ( $defs as $i => $def ) {
		$sc = isset( $def['sc'] ) ? $def['sc'] : '';
		if ( isset( $sc_pt[ $sc ] ) && emono_is_post_type_hidden( $sc_pt[ $sc ] ) ) {
			unset( $defs[ $i ] );
		}
	}
	return array_values( $defs );
}
