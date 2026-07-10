<?php
/**
 * 専用アニメ・ヒーローセクション（MONO TOP のセクション型として登録）。
 *
 * GSAPデモ「Canvas particles」のモーション（中央から湧き出し、拡大しながら
 * 外へ飛ぶ奥行きワープ）を、モノクロ・素canvas・自己完結で再現。
 * 本体の emono_mono_top_sections フィルターで型を追加する。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'emono_mono_top_sections', 'emmt_hero_register' );
function emmt_hero_register( $types ) {
	$types['os_hero'] = array(
		'label'    => __( 'Animated Hero (particles)', 'emerge-mono' ),
		'defaults' => array(
			'kicker'  => 'EMERGE MONO',
			'copy'    => "Monochrome,\nrefined.",
			'density' => 'medium',
			'align'   => 'center',
			'sprites' => array(),
		),
		'sanitize' => 'emmt_hero_sanitize',
		'render'   => 'emmt_hero_render',
		'fields'   => 'emmt_hero_fields',
	);
	return $types;
}

function emmt_hero_sanitize( $s ) {
	$density = isset( $s['density'] ) ? sanitize_key( $s['density'] ) : 'medium';
	if ( ! in_array( $density, array( 'low', 'medium', 'high' ), true ) ) {
		$density = 'medium';
	}
	$align = isset( $s['align'] ) ? sanitize_key( $s['align'] ) : 'center';
	if ( ! in_array( $align, array( 'center', 'left' ), true ) ) {
		$align = 'center';
	}
	$sprites = array();
	if ( isset( $s['sprites'] ) && is_array( $s['sprites'] ) ) {
		foreach ( $s['sprites'] as $u ) {
			$u = esc_url_raw( $u );
			if ( $u ) { $sprites[] = $u; }
		}
	}
	return array(
		'kicker'  => sanitize_text_field( isset( $s['kicker'] ) ? $s['kicker'] : '' ),
		'copy'    => sanitize_textarea_field( isset( $s['copy'] ) ? $s['copy'] : '' ),
		'density' => $density,
		'align'   => $align,
		'sprites' => $sprites,
	);
}

function emmt_hero_render( $s, $inst = array() ) {
	wp_enqueue_style( 'emos-hero' );
	wp_enqueue_script( 'emos-hero' );

	$id      = isset( $inst['id'] ) ? sanitize_html_class( $inst['id'] ) : 'os';
	$canvas  = 'emos-hero-canvas-' . $id;
	$counts  = array( 'low' => 46, 'medium' => 90, 'high' => 150 );
	$density = isset( $counts[ $s['density'] ] ) ? $counts[ $s['density'] ] : 90;
	$align   = ( $s['align'] === 'left' ) ? 'is-left' : 'is-center';
	$sprites = ( isset( $s['sprites'] ) && is_array( $s['sprites'] ) ) ? array_values( $s['sprites'] ) : array();
	?>
	<section class="emos-hero <?php echo esc_attr( $align ); ?>">
		<canvas class="emos-hero-canvas" id="<?php echo esc_attr( $canvas ); ?>" data-density="<?php echo esc_attr( (int) $density ); ?>" data-sprites="<?php echo esc_attr( wp_json_encode( $sprites ) ); ?>" aria-hidden="true"></canvas>
		<div class="emos-hero-content">
			<?php if ( $s['kicker'] ) : ?><p class="emos-hero-kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
			<?php if ( $s['copy'] ) : ?><h1 class="emos-hero-copy"><?php echo nl2br( esc_html( $s['copy'] ) ); ?></h1><?php endif; ?>
		</div>
	</section>
	<?php
}

function emmt_hero_fields( $id, $s ) {
	$name = function ( $k ) use ( $id ) {
		return function_exists( 'emono_mt_name' ) ? emono_mt_name( $id, $k ) : 'sections[' . $id . '][' . $k . ']';
	};
	?>
	<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
		<label class="en-field-label"><?php esc_html_e( 'Kicker', 'emerge-mono' ); ?><input type="text" name="<?php echo esc_attr( $name( 'kicker' ) ); ?>" value="<?php echo esc_attr( $s['kicker'] ); ?>" class="en-field-input"></label>
		<label class="en-field-label"><?php esc_html_e( 'Density', 'emerge-mono' ); ?>
			<select name="<?php echo esc_attr( $name( 'density' ) ); ?>" class="en-field-input">
				<option value="low" <?php selected( $s['density'], 'low' ); ?>><?php esc_html_e( 'Low', 'emerge-mono' ); ?></option>
				<option value="medium" <?php selected( $s['density'], 'medium' ); ?>><?php esc_html_e( 'Medium', 'emerge-mono' ); ?></option>
				<option value="high" <?php selected( $s['density'], 'high' ); ?>><?php esc_html_e( 'High', 'emerge-mono' ); ?></option>
			</select>
		</label>
		<label class="en-field-label"><?php esc_html_e( 'Text alignment', 'emerge-mono' ); ?>
			<select name="<?php echo esc_attr( $name( 'align' ) ); ?>" class="en-field-input">
				<option value="center" <?php selected( $s['align'], 'center' ); ?>><?php esc_html_e( 'Center', 'emerge-mono' ); ?></option>
				<option value="left" <?php selected( $s['align'], 'left' ); ?>><?php esc_html_e( 'Left', 'emerge-mono' ); ?></option>
			</select>
		</label>
	</div>
	<label class="en-field-label" style="margin-top:12px"><?php esc_html_e( 'Headline', 'emerge-mono' ); ?><textarea name="<?php echo esc_attr( $name( 'copy' ) ); ?>" rows="2" class="en-field-textarea"><?php echo esc_textarea( $s['copy'] ); ?></textarea></label>

	<div class="en-field-label" style="margin-top:12px">
		<span><?php esc_html_e( 'Particle images (SVG/PNG)', 'emerge-mono' ); ?></span>
		<div class="emos-hero-sprites">
			<?php
			$sprites = ( isset( $s['sprites'] ) && is_array( $s['sprites'] ) ) ? $s['sprites'] : array();
			$field_name = 'sections[' . $id . '][sprites][]';
			foreach ( $sprites as $url ) :
				?>
				<div class="emos-sprite-row">
					<span class="emos-sprite-thumb"><?php if ( $url ) : ?><img src="<?php echo esc_url( $url ); ?>" alt=""><?php endif; ?></span>
					<input type="hidden" class="emos-sprite-url" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $url ); ?>">
					<button type="button" class="en-media-btn emos-sprite-select"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button>
					<button type="button" class="en-media-btn emos-sprite-remove" aria-label="<?php echo esc_attr__( 'Remove', 'emerge-mono' ); ?>">&times;</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="en-media-btn emos-sprite-add"><?php esc_html_e( 'Add image', 'emerge-mono' ); ?></button>
		<div class="en-field-desc" style="margin-top:6px"><?php esc_html_e( 'Multiple images are mixed randomly. If empty, the Emerge Mono mark is used.', 'emerge-mono' ); ?></div>
	</div>
	<?php
}

add_action( 'wp_enqueue_scripts', 'emmt_hero_register_assets' );
function emmt_hero_register_assets() {
	wp_register_style( 'emos-hero', EMMT_URL . 'assets/hero.css', array(), EMMT_VERSION );
	wp_register_script( 'emos-hero', EMMT_URL . 'assets/hero.js', array(), EMMT_VERSION, true );
	wp_localize_script( 'emos-hero', 'EMOS_HERO', array(
		'fallback' => EMMT_URL . 'assets/em-w.svg',
	) );
}

// デザイン編集（MONO TOP ビルダー）でのSVGリピーターUI。
add_action( 'admin_enqueue_scripts', 'emmt_hero_admin_assets' );
function emmt_hero_admin_assets( $hook ) {
	if ( strpos( $hook, 'ene-top' ) === false ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'emos-hero-admin', EMMT_URL . 'assets/hero-admin.js', array(), EMMT_VERSION, true );
	wp_localize_script( 'emos-hero-admin', 'EMOS_HERO_ADMIN', array(
		'select' => __( 'Select image', 'emerge-mono' ),
		'use'    => __( 'Use this', 'emerge-mono' ),
	) );
	wp_register_style( 'emos-hero-admin', false, array(), EMMT_VERSION );
	wp_enqueue_style( 'emos-hero-admin' );
	wp_add_inline_style( 'emos-hero-admin',
		'.emos-hero-sprites{display:flex;flex-direction:column;gap:6px;margin:8px 0;}'
		. '.emos-sprite-row{display:flex;align-items:center;gap:8px;}'
		. '.emos-sprite-thumb{width:34px;height:34px;flex:0 0 auto;border:1px solid rgba(255,255,255,.15);border-radius:6px;background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;overflow:hidden;}'
		. '.emos-sprite-thumb img{width:100%;height:100%;object-fit:contain;}'
		. '.emos-sprite-remove{color:#ff9a9a;}'
	);
}
