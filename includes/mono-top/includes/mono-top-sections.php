<?php
/**
 * MONO TOP セクション・エンジン
 *
 * MONO TOP を「順序を持つセクション・インスタンス配列」で構成するビルダー基盤。
 *   - 並べ替え可 / 同じセクションの複数配置可
 *   - セクション型レジストリ（hero/about/member/news/cta を標準登録）
 *   - 旧固定キー構造（en_options['mono_top']）からの自動マイグレーション
 *
 * 拡張プラグインは `emono_mono_top_sections` フィルターで独自のセクション型を追加できる。
 * 型名は必ずユニークにプレフィックスすること（標準型と衝突させない）。
 *
 * データ形（en_options['mono_top_sections']）:
 *   array(
 *     array( 'id' => 'sec_ab12', 'type' => 'hero',   'enabled' => '1', 'settings' => array(...) ),
 *     array( 'id' => 'sec_9x0k', 'type' => 'member', 'enabled' => '1', 'settings' => array(...) ),
 *     ...
 *   )
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * セクション型レジストリ
 * ============================================================ */

/**
 * 利用可能なセクション型の一覧。
 * 各型: label / defaults(settings) / sanitize / render / fields のコールバック。
 */
function emono_mono_top_section_types() {
	$d = function_exists( 'emono_mono_top_default_options' ) ? emono_mono_top_default_options() : array();
	$strip = function ( $arr ) {
		unset( $arr['enabled'] );
		return $arr;
	};

	$types = array(
		'hero' => array(
			'label'    => __( 'Hero', 'emerge-mono' ),
			'defaults' => isset( $d['hero'] ) ? $strip( $d['hero'] ) : array(),
			'sanitize' => 'emono_mono_top_sanitize_hero',
			'render'   => 'emono_mono_top_render_hero',
			'fields'   => 'emono_mono_top_fields_hero',
		),
		'about' => array(
			'label'    => __( 'About', 'emerge-mono' ),
			'defaults' => isset( $d['about'] ) ? $strip( $d['about'] ) : array(),
			'sanitize' => 'emono_mono_top_sanitize_about',
			'render'   => 'emono_mono_top_render_about',
			'fields'   => 'emono_mono_top_fields_about',
		),
		'member' => array(
			'label'    => __( 'Slider (post list)', 'emerge-mono' ),
			'defaults' => isset( $d['member'] ) ? $strip( $d['member'] ) : array(),
			'sanitize' => 'emono_mono_top_sanitize_member',
			'render'   => 'emono_mono_top_render_member',
			'fields'   => 'emono_mono_top_fields_member',
		),
		'news' => array(
			'label'    => __( 'News', 'emerge-mono' ),
			'defaults' => isset( $d['news'] ) ? $strip( $d['news'] ) : array(),
			'sanitize' => 'emono_mono_top_sanitize_news',
			'render'   => 'emono_mono_top_render_news',
			'fields'   => 'emono_mono_top_fields_news',
		),
		'cta' => array(
			'label'    => __( 'CTA', 'emerge-mono' ),
			'defaults' => isset( $d['cta'] ) ? $strip( $d['cta'] ) : array(),
			'sanitize' => 'emono_mono_top_sanitize_cta',
			'render'   => 'emono_mono_top_render_cta',
			'fields'   => 'emono_mono_top_fields_cta',
		),
		'contact' => array(
			'label'    => __( 'Contact form', 'emerge-mono' ),
			'defaults' => array(
				'kicker' => 'CONTACT',
				'title'  => 'Get in touch',
				'body'   => '',
			),
			'sanitize' => 'emono_mono_top_sanitize_contact',
			'render'   => 'emono_mono_top_render_contact',
			'fields'   => 'emono_mono_top_fields_contact',
		),
	);

	return apply_filters( 'emono_mono_top_sections', $types );
}

/**
 * 型の defaults を取得。
 */
function emono_mono_top_type_defaults( $type ) {
	$types = emono_mono_top_section_types();
	return isset( $types[ $type ]['defaults'] ) && is_array( $types[ $type ]['defaults'] ) ? $types[ $type ]['defaults'] : array();
}

/* ============================================================
 * インスタンス配列の取得 / 正規化 / マイグレーション
 * ============================================================ */

/**
 * ユニークなインスタンスIDを生成。
 */
function emono_mono_top_new_section_id() {
	return 'sec_' . substr( str_replace( array( '.', ' ' ), '', uniqid( '', true ) ), -8 );
}

/**
 * 現在のセクション・インスタンス配列を取得（無ければ旧構造から移行生成）。
 */
function emono_mono_top_get_sections( $opts = null ) {
	if ( ! is_array( $opts ) ) {
		$opts = emono_get_options();
	}

	// キーが存在すれば（空配列でも）それを正とする。空配列＝「全セクション削除」を維持し、
	// 旧デフォルトを再生成しない。移行するのはキー自体が未設定のときだけ。
	if ( isset( $opts['mono_top_sections'] ) && is_array( $opts['mono_top_sections'] ) ) {
		return emono_mono_top_normalize_sections( $opts['mono_top_sections'] );
	}

	// 旧固定キー構造から移行（enabled のセクションを hero→about→member→news→cta の順で配列化）。
	$legacy = function_exists( 'emono_mono_top_get_options' ) ? emono_mono_top_get_options( $opts ) : array();
	return emono_mono_top_sections_from_legacy( $legacy );
}

/**
 * 旧構造 → セクション配列。
 */
function emono_mono_top_sections_from_legacy( $legacy ) {
	$order = array( 'hero', 'about', 'member', 'news', 'cta' );
	$types = emono_mono_top_section_types();
	$out = array();

	foreach ( $order as $type ) {
		if ( ! isset( $types[ $type ] ) ) {
			continue;
		}
		$section = isset( $legacy[ $type ] ) && is_array( $legacy[ $type ] ) ? $legacy[ $type ] : array();
		$enabled = isset( $section['enabled'] ) ? $section['enabled'] : '1';
		unset( $section['enabled'] );
		$out[] = array(
			'id'       => 'sec_' . $type,
			'type'     => $type,
			'enabled'  => $enabled === '1' ? '1' : '0',
			'settings' => wp_parse_args( $section, emono_mono_top_type_defaults( $type ) ),
		);
	}

	return $out;
}

/**
 * インスタンス配列を正規化（型の存在確認・デフォルト補完・sanitize）。
 */
function emono_mono_top_normalize_sections( $sections ) {
	$types = emono_mono_top_section_types();
	$out = array();

	foreach ( $sections as $inst ) {
		if ( ! is_array( $inst ) ) {
			continue;
		}
		$type = isset( $inst['type'] ) ? sanitize_key( $inst['type'] ) : '';
		if ( ! isset( $types[ $type ] ) ) {
			continue;
		}
		$id = isset( $inst['id'] ) ? sanitize_html_class( $inst['id'] ) : '';
		if ( ! $id ) {
			$id = emono_mono_top_new_section_id();
		}
		$settings = isset( $inst['settings'] ) && is_array( $inst['settings'] ) ? $inst['settings'] : array();
		$settings = wp_parse_args( $settings, emono_mono_top_type_defaults( $type ) );
		if ( is_callable( $types[ $type ]['sanitize'] ) ) {
			$settings = call_user_func( $types[ $type ]['sanitize'], $settings );
		}
		$out[] = array(
			'id'       => $id,
			'type'     => $type,
			'enabled'  => ( isset( $inst['enabled'] ) && $inst['enabled'] === '1' ) ? '1' : '0',
			'settings' => $settings,
		);
	}

	return $out;
}

/**
 * 送信フォーム（unslash 済み配列）→ セクション・インスタンス配列。
 * 期待する構造:
 *   $src['sections_order'] = 'id1,id2,...'
 *   $src['sections'][id]   = array( 'type' => ..., 'enabled' => '1', <各設定キー> )
 */
function emono_mono_top_sections_from_post( $src ) {
	$src = is_array( $src ) ? $src : array();
	$types = emono_mono_top_section_types();

	$order_raw = isset( $src['sections_order'] ) ? (string) $src['sections_order'] : '';
	$order = array_filter( array_map( 'trim', explode( ',', $order_raw ) ) );
	$rows = isset( $src['sections'] ) && is_array( $src['sections'] ) ? $src['sections'] : array();

	// order が空なら rows のキー順にフォールバック。
	if ( empty( $order ) ) {
		$order = array_keys( $rows );
	}

	$out = array();
	foreach ( $order as $id ) {
		$id = sanitize_html_class( $id );
		if ( ! isset( $rows[ $id ] ) || ! is_array( $rows[ $id ] ) ) {
			continue;
		}
		$row = $rows[ $id ];
		$type = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : '';
		if ( ! isset( $types[ $type ] ) ) {
			continue;
		}
		$settings = wp_parse_args( $row, emono_mono_top_type_defaults( $type ) );
		if ( is_callable( $types[ $type ]['sanitize'] ) ) {
			$settings = call_user_func( $types[ $type ]['sanitize'], $settings );
		}
		$out[] = array(
			'id'       => $id,
			'type'     => $type,
			'enabled'  => ( isset( $row['enabled'] ) && $row['enabled'] === '1' ) ? '1' : '0',
			'settings' => $settings,
		);
	}

	return $out;
}

/* ============================================================
 * フロント描画（配列を順番に描画）
 * ============================================================ */

/**
 * MONO TOP 本体の描画（セクション配列版）。
 */
function emono_mono_top_render_sections( $opts = null ) {
	if ( ! is_array( $opts ) ) {
		$opts = emono_get_options();
	}
	$sections = emono_mono_top_get_sections( $opts );
	$types = emono_mono_top_section_types();

	ob_start();
	?>
	<div class="emono-mono-top" data-top-layout="mono_top">
		<?php
		foreach ( $sections as $inst ) {
			if ( empty( $inst['enabled'] ) || $inst['enabled'] !== '1' ) {
				continue;
			}
			$type = $inst['type'];
			if ( ! isset( $types[ $type ] ) || ! is_callable( $types[ $type ]['render'] ) ) {
				continue;
			}
			call_user_func( $types[ $type ]['render'], $inst['settings'], $inst );
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}

/* ---- hero ---- */
function emono_mono_top_sanitize_hero( $s ) {
	$type = sanitize_key( isset( $s['background_type'] ) ? $s['background_type'] : 'image' );
	return array(
		'background_type' => in_array( $type, array( 'image', 'video' ), true ) ? $type : 'image',
		'background_url'  => esc_url_raw( isset( $s['background_url'] ) ? $s['background_url'] : '' ),
		'video_url'       => esc_url_raw( isset( $s['video_url'] ) ? $s['video_url'] : '' ),
		'kicker'          => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'copy'            => sanitize_textarea_field( isset( $s['copy'] ) ? $s['copy'] : '' ),
	);
}
function emono_mono_top_render_hero( $s, $inst = array() ) {
	$style = $s['background_url'] ? '--mono-top-hero-image:url(' . esc_url( $s['background_url'] ) . ');' : '';
	?>
	<section class="emono-mono-top-hero" aria-label="<?php esc_attr_e( 'Hero', 'emerge-mono' ); ?>"<?php if ( $style ) : ?> style="<?php echo esc_attr( $style ); ?>"<?php endif; ?>>
		<?php if ( $s['background_type'] === 'video' && $s['video_url'] ) : ?>
			<video class="emono-mono-top-hero-video" autoplay muted loop playsinline>
				<source src="<?php echo esc_url( $s['video_url'] ); ?>">
			</video>
		<?php else : ?>
			<div class="emono-mono-top-hero-bg" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="emono-mono-top-hero-overlay" aria-hidden="true"></div>
		<div class="emono-mono-top-hero-content">
			<?php if ( $s['kicker'] ) : ?><p class="emono-mono-top-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
			<?php if ( $s['copy'] ) : ?><h1><?php echo nl2br( esc_html( $s['copy'] ) ); ?></h1><?php endif; ?>
		</div>
		<div class="emono-mono-top-scroll" aria-hidden="true"><span></span>SCROLL</div>
	</section>
	<?php
}
function emono_mono_top_fields_hero( $id, $s ) {
	?>
	<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Hero Background Type', 'emerge-mono' ); ?>
			<select name="<?php echo esc_attr( emono_mt_name( $id, 'background_type' ) ); ?>" class="en-field-input">
				<option value="image" <?php selected( $s['background_type'], 'image' ); ?>><?php esc_html_e( 'Image', 'emerge-mono' ); ?></option>
				<option value="video" <?php selected( $s['background_type'], 'video' ); ?>><?php esc_html_e( 'Video', 'emerge-mono' ); ?></option>
			</select>
		</label>
		<label class="en-field-label"><?php esc_html_e( 'Hero Image URL', 'emerge-mono' ); ?>
			<div style="display:flex;gap:8px"><input type="text" id="<?php echo esc_attr( emono_mt_media_id( $id, 'background_url' ) ); ?>" name="<?php echo esc_attr( emono_mt_name( $id, 'background_url' ) ); ?>" value="<?php echo esc_attr( $s['background_url'] ); ?>" class="en-field-input"><button type="button" class="en-media-btn" onclick="enOpenMedia('<?php echo esc_js( emono_mt_media_id( $id, 'background_url' ) ); ?>')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button><button type="button" class="en-media-btn en-media-clear" onclick="enClearMedia('<?php echo esc_js( emono_mt_media_id( $id, 'background_url' ) ); ?>')"><?php esc_html_e( 'Clear', 'emerge-mono' ); ?></button></div>
		</label>
		<label class="en-field-label"><?php esc_html_e( 'Hero Video URL', 'emerge-mono' ); ?>
			<div style="display:flex;gap:8px"><input type="text" id="<?php echo esc_attr( emono_mt_media_id( $id, 'video_url' ) ); ?>" name="<?php echo esc_attr( emono_mt_name( $id, 'video_url' ) ); ?>" value="<?php echo esc_attr( $s['video_url'] ); ?>" class="en-field-input"><button type="button" class="en-media-btn" onclick="enOpenMedia('<?php echo esc_js( emono_mt_media_id( $id, 'video_url' ) ); ?>')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button><button type="button" class="en-media-btn en-media-clear" onclick="enClearMedia('<?php echo esc_js( emono_mt_media_id( $id, 'video_url' ) ); ?>')"><?php esc_html_e( 'Clear', 'emerge-mono' ); ?></button></div>
		</label>
	</div>
	<label class="en-field-label" style="margin-top:12px"><?php esc_html_e( 'Hero Copy', 'emerge-mono' ); ?><textarea name="<?php echo esc_attr( emono_mt_name( $id, 'copy' ) ); ?>" rows="3" class="en-field-textarea"><?php echo esc_textarea( $s['copy'] ); ?></textarea></label>
	<?php
}

/* ---- about ---- */
function emono_mono_top_sanitize_about( $s ) {
	return array(
		'kicker'     => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'title'      => sanitize_textarea_field( isset( $s['title'] ) ? $s['title'] : '' ),
		'body'       => sanitize_textarea_field( isset( $s['body'] ) ? $s['body'] : '' ),
		'image_url'  => esc_url_raw( isset( $s['image_url'] ) ? $s['image_url'] : '' ),
		'link_label' => sanitize_text_field( isset( $s['link_label'] ) ? $s['link_label'] : '' ),
		'link_url'   => esc_url_raw( isset( $s['link_url'] ) ? $s['link_url'] : '' ),
	);
}
function emono_mono_top_render_about( $s, $inst = array() ) {
	$style = $s['image_url'] ? '--mono-top-about-image:url(' . esc_url( $s['image_url'] ) . ');' : '';
	?>
	<section class="emono-mono-top-about"<?php if ( $style ) : ?> style="<?php echo esc_attr( $style ); ?>"<?php endif; ?>>
		<div class="emono-mono-top-about-visual" aria-hidden="true"></div>
		<div class="emono-mono-top-about-copy">
			<?php if ( $s['kicker'] ) : ?><p class="emono-mono-top-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
			<?php if ( $s['title'] ) : ?><h2><?php echo nl2br( esc_html( $s['title'] ) ); ?></h2><?php endif; ?>
			<?php if ( $s['body'] ) : ?><p><?php echo nl2br( esc_html( $s['body'] ) ); ?></p><?php endif; ?>
			<?php if ( $s['link_label'] && $s['link_url'] ) : ?>
				<a class="emono-mono-top-link" href="<?php echo esc_url( $s['link_url'] ); ?>"><?php echo esc_html( $s['link_label'] ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
function emono_mono_top_fields_about( $id, $s ) {
	?>
	<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'About Image URL', 'emerge-mono' ); ?>
			<div style="display:flex;gap:8px"><input type="text" id="<?php echo esc_attr( emono_mt_media_id( $id, 'image_url' ) ); ?>" name="<?php echo esc_attr( emono_mt_name( $id, 'image_url' ) ); ?>" value="<?php echo esc_attr( $s['image_url'] ); ?>" class="en-field-input"><button type="button" class="en-media-btn" onclick="enOpenMedia('<?php echo esc_js( emono_mt_media_id( $id, 'image_url' ) ); ?>')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button><button type="button" class="en-media-btn en-media-clear" onclick="enClearMedia('<?php echo esc_js( emono_mt_media_id( $id, 'image_url' ) ); ?>')"><?php esc_html_e( 'Clear', 'emerge-mono' ); ?></button></div>
		</label>
	</div>
	<label class="en-field-label" style="margin-top:12px"><?php esc_html_e( 'Heading', 'emerge-mono' ); ?><textarea name="<?php echo esc_attr( emono_mt_name( $id, 'title' ) ); ?>" rows="2" class="en-field-textarea"><?php echo esc_textarea( $s['title'] ); ?></textarea></label>
	<label class="en-field-label" style="margin-top:12px"><?php esc_html_e( 'About Body', 'emerge-mono' ); ?><textarea name="<?php echo esc_attr( emono_mt_name( $id, 'body' ) ); ?>" rows="4" class="en-field-textarea"><?php echo esc_textarea( $s['body'] ); ?></textarea></label>
	<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:12px">
		<label class="en-field-label"><?php esc_html_e( 'About Link Label', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'link_label' ) ); ?>" value="<?php echo esc_attr( $s['link_label'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'About Link URL', 'emerge-mono' ); ?><input type="url" name="<?php echo esc_attr( emono_mt_name( $id, 'link_url' ) ); ?>" value="<?php echo esc_attr( $s['link_url'] ); ?>" class="en-field-input"></label>
	</div>
	<?php
}

/* ---- member (slider) ---- */
function emono_mono_top_sanitize_member( $s ) {
	return array(
		'kicker'           => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'title'            => sanitize_text_field( isset( $s['title'] ) ? $s['title'] : '' ),
		'post_type'        => emono_mono_top_sanitize_post_type( isset( $s['post_type'] ) ? $s['post_type'] : 'en_work', 'en_work' ),
		'image_ratio'      => emono_mono_top_sanitize_image_ratio( isset( $s['image_ratio'] ) ? $s['image_ratio'] : 'landscape' ),
		'view_all_page_id' => absint( isset( $s['view_all_page_id'] ) ? $s['view_all_page_id'] : 0 ),
		'count'            => min( 24, max( 1, absint( isset( $s['count'] ) ? $s['count'] : 12 ) ) ),
	);
}
function emono_mono_top_render_member( $s, $inst = array() ) {
	$post_type = emono_mono_top_sanitize_post_type( $s['post_type'], 'en_work' );
	$taxonomy  = emono_mono_top_filter_taxonomy( $post_type );
	$terms = $taxonomy ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'orderby' => 'term_id', 'order' => 'ASC' ) ) : array();
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}
	$posts = get_posts( array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => absint( $s['count'] ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$view_all_url = emono_mono_top_get_list_page_url( $post_type, $s['view_all_page_id'] );
	?>
	<section class="emono-mono-top-slider-section is-ratio-<?php echo esc_attr( emono_mono_top_sanitize_image_ratio( $s['image_ratio'] ) ); ?>">
		<div class="emono-mono-top-section-head">
			<div>
				<?php if ( $s['kicker'] ) : ?><p class="emono-mono-top-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><h2><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
			</div>
			<?php if ( $view_all_url ) : ?><a class="emono-mono-top-view-all" href="<?php echo esc_url( $view_all_url ); ?>"><?php esc_html_e( 'VIEW ALL', 'emerge-mono' ); ?></a><?php endif; ?>
		</div>
		<?php if ( ! empty( $terms ) ) : ?>
			<div class="emono-mono-top-tabs">
				<button type="button" class="emono-mono-top-tab is-active" data-filter="all"><?php esc_html_e( 'ALL', 'emerge-mono' ); ?></button>
				<?php foreach ( $terms as $term ) : ?>
					<button type="button" class="emono-mono-top-tab" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="emono-mono-top-slider-wrap">
			<div class="emono-mono-top-slider-track">
				<?php if ( $posts ) : ?>
					<?php foreach ( $posts as $post_item ) : ?>
						<?php
						$image = get_the_post_thumbnail_url( $post_item->ID, 'large' );
						$slugs = emono_mono_top_post_category_slugs( $post_item->ID, $taxonomy );
						?>
						<a class="emono-mono-top-card" data-categories="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
							<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post_item ) ); ?>"><?php endif; ?>
							<span><?php echo esc_html( get_the_title( $post_item ) ); ?></span>
						</a>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="emono-mono-top-empty"><?php esc_html_e( 'No posts.', 'emerge-mono' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
function emono_mono_top_fields_member( $id, $s ) {
	$post_types = emono_mono_top_get_public_post_types();
	$pages = get_pages( array( 'post_status' => array( 'publish', 'draft' ), 'sort_column' => 'menu_order' ) );
	?>
	<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Heading', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'title' ) ); ?>" value="<?php echo esc_attr( $s['title'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Post Type', 'emerge-mono' ); ?><?php emono_mono_top_render_post_type_select( emono_mt_name( $id, 'post_type' ), $s['post_type'], $post_types ); ?></label>
		<label class="en-field-label"><?php esc_html_e( 'Image Ratio', 'emerge-mono' ); ?><?php emono_mono_top_render_image_ratio_select( emono_mt_name( $id, 'image_ratio' ), $s['image_ratio'] ); ?></label>
		<label class="en-field-label"><?php esc_html_e( 'VIEW ALL Page', 'emerge-mono' ); ?><?php emono_mono_top_render_page_select( emono_mt_name( $id, 'view_all_page_id' ), $s['view_all_page_id'], $pages ); ?></label>
		<label class="en-field-label"><?php esc_html_e( 'Count', 'emerge-mono' ); ?><input type="number" min="1" max="24" name="<?php echo esc_attr( emono_mt_name( $id, 'count' ) ); ?>" value="<?php echo esc_attr( $s['count'] ); ?>" class="en-field-input"></label>
	</div>
	<div class="en-field-desc" style="margin-top:8px"><?php esc_html_e( 'If empty, the page containing the selected post type list shortcode is used automatically.', 'emerge-mono' ); ?></div>
	<?php
}

/* ---- news ---- */
function emono_mono_top_sanitize_news( $s ) {
	return array(
		'kicker'      => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'title'       => sanitize_text_field( isset( $s['title'] ) ? $s['title'] : '' ),
		'post_type'   => 'en_news',
		'image_ratio' => emono_mono_top_sanitize_image_ratio( isset( $s['image_ratio'] ) ? $s['image_ratio'] : 'landscape' ),
		'count'       => min( 12, max( 1, absint( isset( $s['count'] ) ? $s['count'] : 6 ) ) ),
	);
}
function emono_mono_top_render_news( $s, $inst = array() ) {
	$post_type = 'en_news';
	$taxonomy  = emono_mono_top_filter_taxonomy( $post_type );
	$terms = $taxonomy ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'orderby' => 'term_id', 'order' => 'ASC' ) ) : array();
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}
	$posts = get_posts( array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => absint( $s['count'] ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$view_all_url = emono_mono_top_get_list_page_url( $post_type );
	?>
	<section class="emono-mono-top-news-section is-ratio-<?php echo esc_attr( emono_mono_top_sanitize_image_ratio( $s['image_ratio'] ) ); ?>">
		<div class="emono-mono-top-section-head">
			<div>
				<?php if ( $s['kicker'] ) : ?><p class="emono-mono-top-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><h2><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
			</div>
			<?php if ( $view_all_url ) : ?><a class="emono-mono-top-view-all" href="<?php echo esc_url( $view_all_url ); ?>"><?php esc_html_e( 'VIEW ALL', 'emerge-mono' ); ?></a><?php endif; ?>
		</div>
		<?php if ( ! empty( $terms ) ) : ?>
			<div class="emono-mono-top-tabs">
				<button type="button" class="emono-mono-top-tab is-active" data-filter="all"><?php esc_html_e( 'ALL', 'emerge-mono' ); ?></button>
				<?php foreach ( $terms as $term ) : ?>
					<button type="button" class="emono-mono-top-tab" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="emono-mono-top-news-grid">
			<?php if ( $posts ) : ?>
				<?php foreach ( $posts as $post_item ) : ?>
					<?php
					$image = get_the_post_thumbnail_url( $post_item->ID, 'large' );
					$slugs = emono_mono_top_post_category_slugs( $post_item->ID, $taxonomy );
					$p_terms = $taxonomy ? get_the_terms( $post_item->ID, $taxonomy ) : array();
					if ( ! $p_terms || is_wp_error( $p_terms ) ) {
						$p_terms = array();
					}
					?>
					<article class="emono-mono-top-news-card" data-categories="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>">
						<a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
							<div class="emono-mono-top-news-thumb">
								<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post_item ) ); ?>"><?php endif; ?>
							</div>
							<div class="emono-mono-top-news-meta">
								<time datetime="<?php echo esc_attr( get_the_date( 'c', $post_item ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d', $post_item ) ); ?></time>
								<?php foreach ( $p_terms as $term ) : ?><span><?php echo esc_html( $term->name ); ?></span><?php endforeach; ?>
							</div>
							<h3><?php echo esc_html( get_the_title( $post_item ) ); ?></h3>
						</a>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="emono-mono-top-empty"><?php esc_html_e( 'No posts.', 'emerge-mono' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
function emono_mono_top_fields_news( $id, $s ) {
	?>
	<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Heading', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'title' ) ); ?>" value="<?php echo esc_attr( $s['title'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Image Ratio', 'emerge-mono' ); ?><?php emono_mono_top_render_image_ratio_select( emono_mt_name( $id, 'image_ratio' ), $s['image_ratio'] ); ?></label>
		<label class="en-field-label"><?php esc_html_e( 'Count', 'emerge-mono' ); ?><input type="number" min="1" max="12" name="<?php echo esc_attr( emono_mt_name( $id, 'count' ) ); ?>" value="<?php echo esc_attr( $s['count'] ); ?>" class="en-field-input"></label>
	</div>
	<div class="en-field-desc" style="margin-top:8px"><?php esc_html_e( 'The News section always uses the standard News post type and News list page.', 'emerge-mono' ); ?></div>
	<?php
}

/* ---- cta ---- */
function emono_mono_top_sanitize_cta( $s ) {
	return array(
		'kicker'         => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'title'          => sanitize_text_field( isset( $s['title'] ) ? $s['title'] : '' ),
		'body'           => sanitize_textarea_field( isset( $s['body'] ) ? $s['body'] : '' ),
		'background_url' => esc_url_raw( isset( $s['background_url'] ) ? $s['background_url'] : '' ),
		'button_label'   => sanitize_text_field( isset( $s['button_label'] ) ? $s['button_label'] : '' ),
		'button_url'     => esc_url_raw( isset( $s['button_url'] ) ? $s['button_url'] : '' ),
	);
}
function emono_mono_top_render_cta( $s, $inst = array() ) {
	$style = $s['background_url'] ? '--mono-top-cta-image:url(' . esc_url( $s['background_url'] ) . ');' : '';
	?>
	<section class="emono-mono-top-cta"<?php if ( $style ) : ?> style="<?php echo esc_attr( $style ); ?>"<?php endif; ?>>
		<div class="emono-mono-top-cta-bg" aria-hidden="true"></div>
		<div class="emono-mono-top-cta-content">
			<?php if ( $s['kicker'] ) : ?><p class="emono-mono-top-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
			<?php if ( $s['title'] ) : ?><h2><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
			<?php if ( $s['body'] ) : ?><p><?php echo nl2br( esc_html( $s['body'] ) ); ?></p><?php endif; ?>
			<?php if ( $s['button_label'] && $s['button_url'] ) : ?>
				<a class="emono-mono-top-primary-link" href="<?php echo esc_url( $s['button_url'] ); ?>"><?php echo esc_html( $s['button_label'] ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
function emono_mono_top_fields_cta( $id, $s ) {
	?>
	<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'CTA Background Image URL', 'emerge-mono' ); ?>
			<div style="display:flex;gap:8px"><input type="text" id="<?php echo esc_attr( emono_mt_media_id( $id, 'background_url' ) ); ?>" name="<?php echo esc_attr( emono_mt_name( $id, 'background_url' ) ); ?>" value="<?php echo esc_attr( $s['background_url'] ); ?>" class="en-field-input"><button type="button" class="en-media-btn" onclick="enOpenMedia('<?php echo esc_js( emono_mt_media_id( $id, 'background_url' ) ); ?>')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button><button type="button" class="en-media-btn en-media-clear" onclick="enClearMedia('<?php echo esc_js( emono_mt_media_id( $id, 'background_url' ) ); ?>')"><?php esc_html_e( 'Clear', 'emerge-mono' ); ?></button></div>
		</label>
		<label class="en-field-label"><?php esc_html_e( 'Heading', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'title' ) ); ?>" value="<?php echo esc_attr( $s['title'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'CTA Button Label', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'button_label' ) ); ?>" value="<?php echo esc_attr( $s['button_label'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label is-wide"><?php esc_html_e( 'CTA Button URL', 'emerge-mono' ); ?><input type="url" name="<?php echo esc_attr( emono_mt_name( $id, 'button_url' ) ); ?>" value="<?php echo esc_attr( $s['button_url'] ); ?>" class="en-field-input"></label>
	</div>
	<label class="en-field-label" style="margin-top:12px"><?php esc_html_e( 'CTA Body', 'emerge-mono' ); ?><textarea name="<?php echo esc_attr( emono_mt_name( $id, 'body' ) ); ?>" rows="3" class="en-field-textarea"><?php echo esc_textarea( $s['body'] ); ?></textarea></label>
	<?php
}

/* ---- contact ---- */
function emono_mono_top_sanitize_contact( $s ) {
	return array(
		'kicker' => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'title'  => sanitize_text_field( isset( $s['title'] ) ? $s['title'] : '' ),
		'body'   => sanitize_textarea_field( isset( $s['body'] ) ? $s['body'] : '' ),
	);
}
function emono_mono_top_render_contact( $s, $inst = array() ) {
	?>
	<section class="emono-mono-top-contact-section">
		<div class="emono-mono-top-contact-head">
			<?php if ( $s['kicker'] ) : ?><p class="emono-mono-top-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
			<?php if ( $s['title'] ) : ?><h2><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
			<?php if ( $s['body'] ) : ?><p class="emono-mono-top-contact-lead"><?php echo nl2br( esc_html( $s['body'] ) ); ?></p><?php endif; ?>
		</div>
		<div class="emono-mono-top-contact-form">
			<?php echo do_shortcode( '[emerge_mono_contact]' ); ?>
		</div>
	</section>
	<?php
}
function emono_mono_top_fields_contact( $id, $s ) {
	?>
	<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Heading', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( emono_mt_name( $id, 'title' ) ); ?>" value="<?php echo esc_attr( $s['title'] ); ?>" class="en-field-input"></label>
	</div>
	<label class="en-field-label" style="margin-top:12px"><?php esc_html_e( 'Intro text', 'emerge-mono' ); ?><textarea name="<?php echo esc_attr( emono_mt_name( $id, 'body' ) ); ?>" rows="2" class="en-field-textarea"><?php echo esc_textarea( $s['body'] ); ?></textarea></label>
	<div class="en-field-desc" style="margin-top:8px"><?php esc_html_e( 'Embeds the contact form from Contact Form settings.', 'emerge-mono' ); ?></div>
	<?php
}

/* ============================================================
 * 管理UI（ビルダー）
 * ============================================================ */

/**
 * name 属性ヘルパー: sections[<id>][<key>]
 */
function emono_mt_name( $id, $key ) {
	return 'sections[' . $id . '][' . $key . ']';
}

/**
 * メディア入力の要素IDヘルパー。
 */
function emono_mt_media_id( $id, $key ) {
	return 's-' . $id . '-' . $key;
}

/**
 * セクション1件分のカードを描画。
 */
function emono_mono_top_render_section_card( $inst ) {
	$types = emono_mono_top_section_types();
	$type  = $inst['type'];
	if ( ! isset( $types[ $type ] ) ) {
		return;
	}
	$id      = $inst['id'];
	$label   = $types[ $type ]['label'];
	$enabled = ( isset( $inst['enabled'] ) && $inst['enabled'] === '1' );
	?>
	<div class="emono-section-card" data-id="<?php echo esc_attr( $id ); ?>" data-type="<?php echo esc_attr( $type ); ?>">
		<div class="emono-section-card-head">
			<span class="emono-section-handle dashicons dashicons-menu" title="<?php echo esc_attr__( 'Drag to reorder', 'emerge-mono' ); ?>"></span>
			<span class="emono-section-type"><?php echo esc_html( $label ); ?></span>
			<label class="emono-section-enable"><input type="checkbox" class="emono-section-enable-input" name="<?php echo esc_attr( emono_mt_name( $id, 'enabled' ) ); ?>" value="1" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Show', 'emerge-mono' ); ?></label>
			<button type="button" class="emono-section-toggle" aria-label="<?php echo esc_attr__( 'Toggle settings', 'emerge-mono' ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
			<button type="button" class="emono-section-remove" aria-label="<?php echo esc_attr__( 'Remove section', 'emerge-mono' ); ?>"><span class="dashicons dashicons-trash"></span></button>
		</div>
		<div class="emono-section-card-body">
			<input type="hidden" name="<?php echo esc_attr( emono_mt_name( $id, 'type' ) ); ?>" value="<?php echo esc_attr( $type ); ?>">
			<?php
			if ( is_callable( $types[ $type ]['fields'] ) ) {
				call_user_func( $types[ $type ]['fields'], $id, $inst['settings'] );
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * ビルダー全体（デザイン編集 top scope に差し込む）。
 */
function emono_mono_top_render_builder( $opts, $hidden = false ) {
	$sections = emono_mono_top_get_sections( $opts );
	$types    = emono_mono_top_section_types();
	$order_ids = array();
	foreach ( $sections as $inst ) {
		$order_ids[] = $inst['id'];
	}
	?>
	<div class="en-admin-section emono-sections-builder" data-top-layout-settings="mono_top" <?php if ( $hidden ) : ?>hidden<?php endif; ?>>
		<input type="hidden" name="mono_top_settings_present" value="1">
		<div class="en-admin-section-title"><?php esc_html_e( 'MONO TOP Sections', 'emerge-mono' ); ?></div>
		<div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Add, remove, and drag to reorder sections. The same section type can be placed more than once.', 'emerge-mono' ); ?></div>

		<input type="hidden" name="sections_order" id="emono-sections-order" value="<?php echo esc_attr( implode( ',', $order_ids ) ); ?>">

		<div id="emono-sections-list">
			<?php foreach ( $sections as $inst ) : ?>
				<?php emono_mono_top_render_section_card( $inst ); ?>
			<?php endforeach; ?>
		</div>

		<div class="emono-sections-add">
			<select id="emono-sections-add-type" class="en-field-input" style="max-width:260px">
				<?php foreach ( $types as $type_key => $type ) : ?>
					<option value="<?php echo esc_attr( $type_key ); ?>"><?php echo esc_html( $type['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="en-media-btn" id="emono-sections-add-btn"><?php esc_html_e( 'Add section', 'emerge-mono' ); ?></button>
		</div>

		<?php
		// 追加用テンプレート（各型の空カード。IDは __ID__ プレースホルダ）。
		foreach ( $types as $type_key => $type ) :
			$template_inst = array(
				'id'       => '__ID__',
				'type'     => $type_key,
				'enabled'  => '1',
				'settings' => emono_mono_top_type_defaults( $type_key ),
			);
			?>
			<script type="text/html" class="emono-section-template" data-type="<?php echo esc_attr( $type_key ); ?>">
				<?php emono_mono_top_render_section_card( $template_inst ); ?>
			</script>
		<?php endforeach; ?>
	</div>
	<?php
}
