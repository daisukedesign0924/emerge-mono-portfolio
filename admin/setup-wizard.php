<?php
/**
 * Setup Wizard — shown once right after activation.
 * Lets beginners create the required pages (with their shortcodes) in one step.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Canonical list of Emerge Mono pages + their shortcodes.
 * Single source of truth, reused by both the wizard and the Shortcodes tab.
 */
function en_get_em_page_defs() {
    return array(
        array( 'sc' => '[emerge_mono_top]',      'title' => 'Home',             'slug' => '',               'desc' => __( 'Top page — logo, site name, buttons', 'emerge-mono' ), 'icon' => '🏠', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_about]',     'title' => 'Profile',          'slug' => 'about',          'desc' => __( 'Profile page — bio, social links', 'emerge-mono' ),    'icon' => '👤', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_works]',     'title' => 'Works',            'slug' => 'works',          'desc' => __( 'Works page — portfolio grid', 'emerge-mono' ),         'icon' => '📂', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_news]',      'title' => 'News',             'slug' => 'news',           'desc' => __( 'News page — post list', 'emerge-mono' ),               'icon' => '📰', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_contact]',   'title' => 'Contact',          'slug' => 'contact',        'desc' => __( 'Contact page — form', 'emerge-mono' ),                 'icon' => '✉',  'recommended' => true ),
        array( 'sc' => '[emerge_mono_privacy]',   'title' => 'Privacy Policy',   'slug' => 'privacy-policy', 'desc' => __( 'Privacy policy page', 'emerge-mono' ),                 'icon' => '🔒', 'recommended' => false ),
        array( 'sc' => '[emerge_mono_terms]',     'title' => 'Terms of Service', 'slug' => 'terms',          'desc' => __( 'Terms of service page', 'emerge-mono' ),               'icon' => '📋', 'recommended' => false ),
        array( 'sc' => '[emerge_mono_estimate]',  'title' => 'Estimate',         'slug' => 'estimate',       'desc' => __( 'Estimate simulator page', 'emerge-mono' ),             'icon' => '💰', 'recommended' => false ),
    );
}

/**
 * Map each shortcode to an already-existing page (publish/draft), if any.
 * Returns array( '[shortcode]' => WP_Post ).
 */
function en_get_em_sc_page_map() {
    $existing = get_pages( array( 'post_status' => array('publish','draft'), 'sort_column' => 'menu_order' ) );
    $map = array();
    foreach ( $existing as $page ) {
        foreach ( en_get_em_page_defs() as $def ) {
            if ( strpos( $page->post_content, $def['sc'] ) !== false ) {
                $map[ $def['sc'] ] = $page;
            }
        }
    }
    return $map;
}

// ── 有効化時にウィザード表示フラグを立てる ──
function en_wizard_set_redirect_flag() {
    // 一括有効化（複数プラグイン同時）の場合はリダイレクトしない
    set_transient( 'en_show_setup_wizard', 1, 60 );
}

// ── 有効化直後、最初の管理画面アクセスでウィザードへ誘導 ──
add_action( 'admin_init', 'en_wizard_maybe_redirect' );
function en_wizard_maybe_redirect() {
    if ( ! get_transient( 'en_show_setup_wizard' ) ) return;
    delete_transient( 'en_show_setup_wizard' );

    // 一括有効化・自動有効化時はリダイレクトを避ける
    if ( isset($_GET['activate-multi']) || wp_doing_ajax() ) return;
    if ( ! current_user_can('manage_options') ) return;

    // すでに完了済みなら出さない
    if ( get_option('en_wizard_completed') ) return;

    wp_safe_redirect( admin_url('admin.php?page=en-setup-wizard') );
    exit;
}

// ── ウィザード画面を（メニュー非表示の隠しページとして）登録 ──
add_action( 'admin_menu', 'en_wizard_register_page', 99 );
function en_wizard_register_page() {
    $hook = add_submenu_page(
        null, // 親なし＝メニューに出さない
        __( 'Emerge Mono Setup', 'emerge-mono' ),
        __( 'Emerge Mono Setup', 'emerge-mono' ),
        'manage_options',
        'en-setup-wizard',
        'en_wizard_render'
    );
    // このページが読み込まれる時にだけメディアライブラリをenqueue（確実な方法）
    if ( $hook ) {
        add_action( 'load-' . $hook, 'en_wizard_load_assets' );
    }
}
function en_wizard_load_assets() {
    add_action( 'admin_enqueue_scripts', 'en_wizard_enqueue' );
}

// ── ウィザード画面では他プラグインのadmin noticeを抑制（全画面表示のため） ──
add_action( 'admin_head', 'en_wizard_suppress_notices' );
function en_wizard_suppress_notices() {
    if ( ! isset($_GET['page']) || $_GET['page'] !== 'en-setup-wizard' ) return;
    remove_all_actions( 'admin_notices' );
    remove_all_actions( 'all_admin_notices' );
}

// ── メディアライブラリ読み込み（保険として page 判定でも実行） ──
add_action( 'admin_enqueue_scripts', 'en_wizard_enqueue' );
function en_wizard_enqueue( $hook = '' ) {
    if ( ! isset($_GET['page']) || $_GET['page'] !== 'en-setup-wizard' ) return;
    wp_enqueue_media(); // 重複呼び出しはWP側で無害化される
}

// ── ウィザード本体描画 ──
function en_wizard_render() {
    $defs    = en_get_em_page_defs();
    $sc_map  = en_get_em_sc_page_map();
    $opts          = get_option('en_options', array());
    $cur_name      = isset($opts['site_name'])      ? $opts['site_name']      : get_bloginfo('name');
    $cur_tagline   = isset($opts['site_tagline'])   ? $opts['site_tagline']   : '';
    $cur_logo      = isset($opts['logo_url'])       ? $opts['logo_url']       : ''; // ダーク用
    $cur_logo_light= isset($opts['logo_url_light']) ? $opts['logo_url_light'] : ''; // ライト用
    $cur_mode      = isset($opts['design_mode'])    ? $opts['design_mode']    : 'dark';
    // 完了後の遷移先：エディター有効ならPage Manager、無効なら設定トップ
    $editor_on   = ! isset($opts['editor_enabled']) || $opts['editor_enabled'] === '1';
    $dash_url    = $editor_on
        ? admin_url('admin.php?page=ene-page-create')
        : admin_url('admin.php?page=emerge-mono-portfolio');

    $steps = array(
        __( 'Site Info', 'emerge-mono' ),
        __( 'Color', 'emerge-mono' ),
        __( 'Logo', 'emerge-mono' ),
        __( 'Pages', 'emerge-mono' ),
    );
    ?>
    <div class="en-wiz-overlay">
        <div class="en-wiz-modal">

            <!-- 進捗インジケーター -->
            <div class="en-wiz-steps" id="en-wiz-steps">
                <?php foreach ( $steps as $i => $label ) : ?>
                <div class="en-wiz-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i; ?>">
                    <span class="en-wiz-step-num"><?php echo $i + 1; ?></span>
                    <span class="en-wiz-step-label"><?php echo esc_html( $label ); ?></span>
                </div>
                <?php if ( $i < count($steps) - 1 ) : ?><span class="en-wiz-step-line"></span><?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="en-wiz-body">

                <!-- STEP 1: サイト名・キャッチコピー -->
                <section class="en-wiz-pane active" data-pane="0">
                    <div class="en-wiz-head">
                        <div class="en-wiz-badge"><?php esc_html_e( 'Welcome', 'emerge-mono' ); ?></div>
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Emerge Mono Setup', 'emerge-mono' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Let\'s set up the basics. Every step is optional — you can skip anything and change it later in the settings.', 'emerge-mono' ); ?></p>
                    </div>
                    <div class="en-wiz-form">
                        <label class="en-wiz-field">
                            <span class="en-wiz-flabel"><?php esc_html_e( 'Site Name', 'emerge-mono' ); ?></span>
                            <input type="text" id="en-wiz-site-name" class="en-wiz-input" value="<?php echo esc_attr( $cur_name ); ?>" placeholder="<?php echo esc_attr( get_bloginfo('name') ); ?>">
                        </label>
                        <label class="en-wiz-field">
                            <span class="en-wiz-flabel"><?php esc_html_e( 'Tagline', 'emerge-mono' ); ?></span>
                            <input type="text" id="en-wiz-tagline" class="en-wiz-input" value="<?php echo esc_attr( $cur_tagline ); ?>" placeholder="<?php echo esc_attr__( 'Web Creator', 'emerge-mono' ); ?>">
                        </label>
                    </div>
                </section>

                <!-- STEP 2: カラーモード -->
                <section class="en-wiz-pane" data-pane="1">
                    <div class="en-wiz-head">
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Color Mode', 'emerge-mono' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Choose how your site looks. Auto follows each visitor\'s device setting.', 'emerge-mono' ); ?></p>
                    </div>
                    <div class="en-wiz-modes" id="en-wiz-modes">
                        <?php
                        $mode_opts = array(
                            'dark'  => array( __( 'Dark', 'emerge-mono' ),  __( 'Dark background, white text', 'emerge-mono' ) ),
                            'light' => array( __( 'Light', 'emerge-mono' ), __( 'Light background, black text', 'emerge-mono' ) ),
                            'auto'  => array( __( 'Auto', 'emerge-mono' ),  __( 'Match device setting', 'emerge-mono' ) ),
                        );
                        foreach ( $mode_opts as $val => $info ) : ?>
                        <label class="en-wiz-mode<?php echo $cur_mode === $val ? ' active' : ''; ?>" data-mode="<?php echo esc_attr( $val ); ?>">
                            <input type="radio" name="en-wiz-mode" value="<?php echo esc_attr( $val ); ?>" <?php checked( $cur_mode, $val ); ?>>
                            <span class="en-wiz-mode-swatch en-wiz-swatch-<?php echo esc_attr( $val ); ?>"></span>
                            <span class="en-wiz-mode-name"><?php echo esc_html( $info[0] ); ?></span>
                            <span class="en-wiz-mode-desc"><?php echo esc_html( $info[1] ); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- STEP 3: ロゴ（カラーモードに応じて入力欄が変化） -->
                <section class="en-wiz-pane" data-pane="2">
                    <div class="en-wiz-head">
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Logo', 'emerge-mono' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Upload a logo for the site header. You can skip this and add it later.', 'emerge-mono' ); ?></p>
                    </div>
                    <div class="en-wiz-logos">
                        <!-- ダーク用ロゴスロット -->
                        <div class="en-wiz-logo-slot" data-slot="dark">
                            <div class="en-wiz-logo-slot-label" id="en-wiz-logo-label-dark"><?php esc_html_e( 'Logo (Dark Mode)', 'emerge-mono' ); ?></div>
                            <div class="en-wiz-logo-preview en-wiz-logo-prev-dark" id="en-wiz-logo-preview-dark" style="<?php echo $cur_logo ? '' : 'display:none'; ?>">
                                <img id="en-wiz-logo-img-dark" src="<?php echo esc_url( $cur_logo ); ?>" alt="">
                            </div>
                            <input type="hidden" id="en-wiz-logo-url" value="<?php echo esc_attr( $cur_logo ); ?>">
                            <div class="en-wiz-logo-btns">
                                <button type="button" class="en-wiz-media-btn" data-target="dark"><?php esc_html_e( 'Select from Media Library', 'emerge-mono' ); ?></button>
                                <button type="button" class="en-wiz-link en-wiz-logo-remove" data-target="dark" style="<?php echo $cur_logo ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove', 'emerge-mono' ); ?></button>
                            </div>
                        </div>
                        <!-- ライト用ロゴスロット -->
                        <div class="en-wiz-logo-slot" data-slot="light">
                            <div class="en-wiz-logo-slot-label" id="en-wiz-logo-label-light"><?php esc_html_e( 'Logo (Light Mode)', 'emerge-mono' ); ?></div>
                            <div class="en-wiz-logo-preview en-wiz-logo-prev-light" id="en-wiz-logo-preview-light" style="<?php echo $cur_logo_light ? '' : 'display:none'; ?>">
                                <img id="en-wiz-logo-img-light" src="<?php echo esc_url( $cur_logo_light ); ?>" alt="">
                            </div>
                            <input type="hidden" id="en-wiz-logo-url-light" value="<?php echo esc_attr( $cur_logo_light ); ?>">
                            <div class="en-wiz-logo-btns">
                                <button type="button" class="en-wiz-media-btn" data-target="light"><?php esc_html_e( 'Select from Media Library', 'emerge-mono' ); ?></button>
                                <button type="button" class="en-wiz-link en-wiz-logo-remove" data-target="light" style="<?php echo $cur_logo_light ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove', 'emerge-mono' ); ?></button>
                            </div>
                        </div>
                    </div>
                    <p class="en-wiz-logo-hint" id="en-wiz-logo-hint"></p>
                </section>

                <!-- STEP 4: ページ選択 -->
                <section class="en-wiz-pane" data-pane="3">
                    <div class="en-wiz-head">
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Create Pages', 'emerge-mono' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Select the pages to create. Each is created with its shortcode already inserted.', 'emerge-mono' ); ?></p>
                    </div>
                    <div class="en-wiz-toolbar">
                        <button type="button" class="en-wiz-link" id="en-wiz-select-all"><?php esc_html_e( 'Select all', 'emerge-mono' ); ?></button>
                        <span class="en-wiz-sep">·</span>
                        <button type="button" class="en-wiz-link" id="en-wiz-select-recommended"><?php esc_html_e( 'Recommended only', 'emerge-mono' ); ?></button>
                        <span class="en-wiz-sep">·</span>
                        <button type="button" class="en-wiz-link" id="en-wiz-clear"><?php esc_html_e( 'Clear', 'emerge-mono' ); ?></button>
                    </div>
                    <div class="en-wiz-list">
                        <?php foreach ( $defs as $def ) :
                            $exists  = isset( $sc_map[ $def['sc'] ] );
                            $checked = ( ! $exists && $def['recommended'] ) ? 'checked' : '';
                        ?>
                        <label class="en-wiz-item<?php echo $exists ? ' is-exists' : ''; ?>">
                            <input type="checkbox" class="en-wiz-check"
                                   value="<?php echo esc_attr( $def['sc'] ); ?>"
                                   data-title="<?php echo esc_attr( $def['title'] ); ?>"
                                   data-slug="<?php echo esc_attr( $def['slug'] ); ?>"
                                   <?php echo $checked; ?>
                                   <?php echo $exists ? 'disabled' : ''; ?>>
                            <span class="en-wiz-icon"><?php echo $def['icon']; // emoji ?></span>
                            <span class="en-wiz-meta">
                                <span class="en-wiz-name"><?php echo esc_html( $def['title'] ); ?></span>
                                <span class="en-wiz-desc"><?php echo esc_html( $def['desc'] ); ?></span>
                                <code class="en-wiz-sc"><?php echo esc_html( $def['sc'] ); ?></code>
                            </span>
                            <?php if ( $exists ) : ?>
                                <span class="en-wiz-tag en-wiz-tag-done"><?php esc_html_e( 'Created', 'emerge-mono' ); ?></span>
                            <?php elseif ( $def['recommended'] ) : ?>
                                <span class="en-wiz-tag en-wiz-tag-rec"><?php esc_html_e( 'Recommended', 'emerge-mono' ); ?></span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>

            <div class="en-wiz-msg" id="en-wiz-msg"></div>

            <div class="en-wiz-actions">
                <a href="<?php echo esc_url( $dash_url ); ?>" class="en-wiz-skip" id="en-wiz-skip-all"><?php esc_html_e( 'Skip setup', 'emerge-mono' ); ?></a>
                <div class="en-wiz-nav">
                    <button type="button" class="en-wiz-back" id="en-wiz-back" style="display:none"><?php esc_html_e( '← Back', 'emerge-mono' ); ?></button>
                    <button type="button" class="en-wiz-next" id="en-wiz-next"><?php esc_html_e( 'Save & Next →', 'emerge-mono' ); ?></button>
                    <button type="button" class="en-wiz-create" id="en-wiz-finish" style="display:none"><?php esc_html_e( 'Finish & create pages', 'emerge-mono' ); ?></button>
                </div>
            </div>

            <p class="en-wiz-foot"><?php esc_html_e( 'Everything here can be changed later from the plugin settings.', 'emerge-mono' ); ?></p>

        </div>
    </div>

    <style>
    /* WP管理画面のスクロール抑制＆管理バーより前面に */
    html.wp-toolbar { padding-top: 0 !important; }
    body.en-wiz-active { overflow: hidden !important; }
    #wpadminbar { display: none !important; }
    .en-wiz-overlay { position: fixed; inset: 0; background: rgba(10,10,12,.92); display: flex; align-items: stretch; justify-content: center; z-index: 2147483646; overflow: hidden; }
    /* WPメディアライブラリ：オーバーレイより前面に。ただし背景(backdrop)は本体より下げてクリックを通す */
    body .media-modal-backdrop { z-index: 2147483640 !important; }
    body .media-modal { z-index: 2147483647 !important; }
    .en-wiz-modal { width: 100vw; height: 100vh; max-width: 100vw; display: flex; flex-direction: column; background: #16161a; border: none; border-radius: 0; box-shadow: none; padding: 0; color: #eaeaea; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

    /* 進捗インジケーター */
    .en-wiz-steps { display: flex; align-items: center; justify-content: center; gap: 0; padding: 3.5vh 24px 0; flex: none; }
    .en-wiz-step { display: flex; align-items: center; gap: 8px; opacity: .4; transition: opacity .2s; }
    .en-wiz-step.active { opacity: 1; }
    .en-wiz-step.done { opacity: .7; }
    .en-wiz-step-num { width: 26px; height: 26px; border-radius: 50%; border: 1px solid rgba(255,255,255,.3); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex: none; }
    .en-wiz-step.active .en-wiz-step-num { background: #fff; color: #16161a; border-color: #fff; }
    .en-wiz-step.done .en-wiz-step-num { border-color: rgba(120,230,150,.6); color: rgba(120,230,150,.9); }
    .en-wiz-step-label { font-size: 12px; letter-spacing: .05em; white-space: nowrap; }
    .en-wiz-step-line { width: 40px; height: 1px; background: rgba(255,255,255,.15); margin: 0 10px; flex: none; }

    /* 本体（ペイン切替領域） */
    .en-wiz-body { flex: 1 1 auto; overflow-y: auto; display: flex; flex-direction: column; min-height: 0; }
    .en-wiz-pane { display: none; flex-direction: column; flex: 1 1 auto; min-height: 0; }
    .en-wiz-pane.active { display: flex; }

    .en-wiz-head { text-align: center; padding: 4vh 24px 0; flex: none; }
    .en-wiz-badge { display: inline-block; font-size: 11px; letter-spacing: .25em; text-transform: uppercase; color: rgba(255,255,255,.5); border: 1px solid rgba(255,255,255,.15); border-radius: 999px; padding: 4px 14px; margin-bottom: 12px; }
    .en-wiz-title { font-size: 26px; font-weight: 700; margin: 0 0 8px; color: #fff; }
    .en-wiz-lead { font-size: 13px; line-height: 1.7; color: rgba(255,255,255,.6); margin: 0 auto; max-width: 520px; }

    /* フォーム（Step1/2） */
    .en-wiz-form { width: 100%; max-width: 520px; margin: 3vh auto 0; padding: 0 24px; box-sizing: border-box; display: flex; flex-direction: column; gap: 18px; }
    .en-wiz-field { display: flex; flex-direction: column; gap: 7px; }
    .en-wiz-flabel { font-size: 12px; letter-spacing: .08em; color: rgba(255,255,255,.6); text-transform: uppercase; }
    .en-wiz-input { width: 100%; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12); border-radius: 8px; color: #fff; font-size: 14px; padding: 12px 14px; outline: none; box-sizing: border-box; transition: border-color .15s; }
    .en-wiz-input:focus { border-color: rgba(255,255,255,.4); }

    /* ロゴ（Step3：ダーク/ライトのスロット） */
    .en-wiz-logos { display: flex; gap: 24px; justify-content: center; flex-wrap: wrap; margin: 3vh auto 0; padding: 0 24px; max-width: 620px; box-sizing: border-box; }
    .en-wiz-logo-slot { flex: 1; min-width: 220px; max-width: 280px; display: flex; flex-direction: column; align-items: center; gap: 12px; }
    .en-wiz-logo-slot.is-hidden { display: none; }
    .en-wiz-logo-slot-label { font-size: 12px; letter-spacing: .08em; color: rgba(255,255,255,.6); text-transform: uppercase; }
    .en-wiz-logo-preview { width: 100%; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1); border-radius: 10px; padding: 16px; text-align: center; min-height: 80px; display: flex; align-items: center; justify-content: center; }
    .en-wiz-logo-prev-light { background: #f4f4f4; }
    .en-wiz-logo-preview img { max-width: 100%; max-height: 120px; object-fit: contain; }
    .en-wiz-logo-btns { display: flex; align-items: center; justify-content: center; gap: 14px; }
    .en-wiz-media-btn { background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.18); border-radius: 8px; padding: 11px 18px; font-size: 12.5px; cursor: pointer; transition: background .15s; }
    .en-wiz-media-btn:hover { background: rgba(255,255,255,.14); }
    .en-wiz-logo-hint { text-align: center; font-size: 12px; color: rgba(255,255,255,.4); margin: 2.5vh auto 0; max-width: 520px; padding: 0 24px; }

    /* カラーモード（Step3） */
    .en-wiz-modes { width: 100%; max-width: 620px; margin: 3vh auto 0; padding: 0 24px; box-sizing: border-box; display: flex; gap: 12px; }
    .en-wiz-mode { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 14px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.1); border-radius: 12px; cursor: pointer; text-align: center; transition: border-color .15s, background .15s; }
    .en-wiz-mode:hover { border-color: rgba(255,255,255,.25); }
    .en-wiz-mode.active { border-color: #fff; background: rgba(255,255,255,.06); }
    .en-wiz-mode input { position: absolute; opacity: 0; pointer-events: none; }
    .en-wiz-mode-swatch { width: 100%; height: 54px; border-radius: 8px; border: 1px solid rgba(255,255,255,.12); }
    .en-wiz-swatch-dark { background: #111; }
    .en-wiz-swatch-light { background: #fff; }
    .en-wiz-swatch-auto { background: linear-gradient(90deg, #111 50%, #fff 50%); }
    .en-wiz-mode-name { font-size: 14px; font-weight: 600; color: #fff; }
    .en-wiz-mode-desc { font-size: 11px; color: rgba(255,255,255,.45); line-height: 1.4; }

    .en-wiz-toolbar { display: flex; align-items: center; gap: 8px; justify-content: flex-end; width: 100%; max-width: 720px; margin: 18px auto 10px; padding: 0 24px; flex: none; box-sizing: border-box; }
    .en-wiz-link { background: none; border: none; color: rgba(255,255,255,.55); font-size: 12px; cursor: pointer; padding: 2px 4px; text-decoration: underline; text-underline-offset: 2px; }
    .en-wiz-link:hover { color: #fff; }
    .en-wiz-sep { color: rgba(255,255,255,.25); font-size: 12px; }
    .en-wiz-list { display: flex; flex-direction: column; gap: 8px; flex: 1 1 auto; overflow-y: auto; width: 100%; max-width: 720px; margin: 0 auto; padding: 2px 24px; box-sizing: border-box; }
    .en-wiz-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06); border-radius: 10px; cursor: pointer; transition: border-color .15s, background .15s; }
    .en-wiz-item:hover { border-color: rgba(255,255,255,.18); background: rgba(255,255,255,.05); }
    .en-wiz-item.is-exists { opacity: .5; cursor: default; }
    .en-wiz-item.is-exists:hover { border-color: rgba(255,255,255,.06); background: rgba(255,255,255,.03); }
    .en-wiz-check { width: 18px; height: 18px; flex: none; accent-color: #fff; cursor: pointer; }
    .en-wiz-item.is-exists .en-wiz-check { cursor: default; }
    .en-wiz-icon { font-size: 20px; flex: none; width: 24px; text-align: center; }
    .en-wiz-meta { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 0; }
    .en-wiz-name { font-size: 14px; font-weight: 600; color: #fff; }
    .en-wiz-desc { font-size: 12px; color: rgba(255,255,255,.45); }
    .en-wiz-sc { font-size: 11px; color: rgba(255,255,255,.4); background: rgba(255,255,255,.06); padding: 1px 6px; border-radius: 4px; align-self: flex-start; margin-top: 2px; }
    .en-wiz-tag { font-size: 10px; letter-spacing: .08em; text-transform: uppercase; padding: 3px 9px; border-radius: 999px; flex: none; }
    .en-wiz-tag-rec { color: rgba(120,200,255,.9); border: 1px solid rgba(120,200,255,.3); }
    .en-wiz-tag-done { color: rgba(120,230,150,.9); border: 1px solid rgba(120,230,150,.3); }
    .en-wiz-msg { min-height: 18px; font-size: 13px; text-align: center; margin: 10px auto 0; color: rgba(255,255,255,.6); flex: none; }
    .en-wiz-actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; width: 100%; max-width: 720px; margin: 10px auto 0; padding: 0 24px; flex: none; box-sizing: border-box; }
    .en-wiz-skip { color: rgba(255,255,255,.45); font-size: 13px; text-decoration: none; }
    .en-wiz-skip:hover { color: rgba(255,255,255,.7); }
    .en-wiz-nav { display: flex; align-items: center; gap: 12px; }
    .en-wiz-back { background: none; border: 1px solid rgba(255,255,255,.2); color: rgba(255,255,255,.7); border-radius: 10px; padding: 12px 22px; font-size: 14px; cursor: pointer; transition: border-color .15s, color .15s; }
    .en-wiz-back:hover { border-color: rgba(255,255,255,.4); color: #fff; }
    .en-wiz-next { background: #fff; color: #16161a; border: none; border-radius: 10px; padding: 13px 30px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity .15s; }
    .en-wiz-next:hover { opacity: .85; }
    .en-wiz-create { background: #fff; color: #16161a; border: none; border-radius: 10px; padding: 13px 28px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity .15s; }
    .en-wiz-create:hover { opacity: .85; }
    .en-wiz-create:disabled { opacity: .5; cursor: default; }
    .en-wiz-foot { text-align: center; font-size: 12px; color: rgba(255,255,255,.35); margin: 0 auto; padding: 14px 24px 3vh; flex: none; }
    @media (max-width: 600px) {
        .en-wiz-steps { padding: 2.5vh 12px 0; }
        .en-wiz-step-label { display: none; }
        .en-wiz-step-line { width: 20px; margin: 0 6px; }
        .en-wiz-head { padding: 3vh 16px 0; }
        .en-wiz-title { font-size: 22px; }
        .en-wiz-form, .en-wiz-modes, .en-wiz-toolbar, .en-wiz-list, .en-wiz-actions, .en-wiz-foot { padding-left: 16px; padding-right: 16px; }
        .en-wiz-modes { flex-direction: column; }
        .en-wiz-sc { display: none; }
        .en-wiz-actions { flex-direction: column-reverse; align-items: stretch; gap: 10px; }
        .en-wiz-nav { width: 100%; }
        .en-wiz-next, .en-wiz-create { flex: 1; }
        .en-wiz-skip { text-align: center; }
    }
    </style>

    <script>
    (function(){
        document.body.classList.add('en-wiz-active');
        var checks = function(){ return Array.prototype.slice.call(document.querySelectorAll('.en-wiz-check:not([disabled])')); };
        var msg    = document.getElementById('en-wiz-msg');

        var i18n = {
            none:     <?php echo wp_json_encode( __( 'Please select at least one page.', 'emerge-mono' ) ); ?>,
            saving:   <?php echo wp_json_encode( __( 'Saving settings…', 'emerge-mono' ) ); ?>,
            saved:    <?php echo wp_json_encode( __( 'Saved ✓', 'emerge-mono' ) ); ?>,
            creating: <?php echo wp_json_encode( __( 'Creating pages…', 'emerge-mono' ) ); ?>,
            doneOne:  <?php echo wp_json_encode( __( 'Created: %s', 'emerge-mono' ) ); ?>,
            allDone:  <?php echo wp_json_encode( __( 'All set! Redirecting…', 'emerge-mono' ) ); ?>,
            failOne:  <?php echo wp_json_encode( __( 'Failed: %s', 'emerge-mono' ) ); ?>,
            mediaTitle: <?php echo wp_json_encode( __( 'Select Logo', 'emerge-mono' ) ); ?>,
            mediaBtn:   <?php echo wp_json_encode( __( 'Use this logo', 'emerge-mono' ) ); ?>,
            mediaUnavailable: <?php echo wp_json_encode( __( 'Media library could not load. Please reload the page and try again.', 'emerge-mono' ) ); ?>
        };
        var ajaxurl     = <?php echo wp_json_encode( admin_url('admin-ajax.php') ); ?>;
        var nonce       = <?php echo wp_json_encode( wp_create_nonce('ene_create_em_page') ); ?>;
        var doneNonce   = <?php echo wp_json_encode( wp_create_nonce('en_wizard_done') ); ?>;
        var saveNonce   = <?php echo wp_json_encode( wp_create_nonce('en_wizard_save') ); ?>;
        var dashUrl     = <?php echo wp_json_encode( $dash_url ); ?>;

        /* ===== ステップ移動 ===== */
        var panes   = Array.prototype.slice.call(document.querySelectorAll('.en-wiz-pane'));
        var stepEls = Array.prototype.slice.call(document.querySelectorAll('.en-wiz-step'));
        var total   = panes.length;
        var cur     = 0;
        var backBtn   = document.getElementById('en-wiz-back');
        var nextBtn   = document.getElementById('en-wiz-next');
        var finishBtn = document.getElementById('en-wiz-finish');

        function render(){
            panes.forEach(function(p, i){ p.classList.toggle('active', i === cur); });
            stepEls.forEach(function(s, i){
                s.classList.toggle('active', i === cur);
                s.classList.toggle('done', i < cur);
            });
            backBtn.style.display   = cur === 0 ? 'none' : '';
            var last = ( cur === total - 1 );
            nextBtn.style.display   = last ? 'none' : '';
            finishBtn.style.display = last ? '' : 'none';
            msg.textContent = '';
        }
        // 「保存して次へ」：現在の入力を保存してから次のステップへ
        nextBtn.addEventListener('click', function(){
            if ( cur >= total - 1 ) return;
            nextBtn.disabled = true;
            msg.textContent = i18n.saving;
            saveSettings().then(function(){
                nextBtn.disabled = false;
                msg.textContent = i18n.saved;
                cur++;
                render();
            });
        });
        backBtn.addEventListener('click', function(){ if ( cur > 0 ) { cur--; render(); } });
        render();

        /* ===== ページ選択ツールバー ===== */
        document.getElementById('en-wiz-select-all').addEventListener('click', function(){
            checks().forEach(function(c){ c.checked = true; });
        });
        document.getElementById('en-wiz-clear').addEventListener('click', function(){
            checks().forEach(function(c){ c.checked = false; });
        });
        document.getElementById('en-wiz-select-recommended').addEventListener('click', function(){
            checks().forEach(function(c){ c.checked = (c.closest('.en-wiz-item').querySelector('.en-wiz-tag-rec') !== null); });
        });

        /* ===== ロゴスロット要素 ===== */
        var slotDark    = document.querySelector('.en-wiz-logo-slot[data-slot="dark"]');
        var slotLight   = document.querySelector('.en-wiz-logo-slot[data-slot="light"]');
        var labelDark   = document.getElementById('en-wiz-logo-label-dark');
        var labelLight  = document.getElementById('en-wiz-logo-label-light');
        var logoHint    = document.getElementById('en-wiz-logo-hint');
        var logoLabels = {
            dark:   <?php echo wp_json_encode( __( 'Logo (Dark Mode)', 'emerge-mono' ) ); ?>,
            light:  <?php echo wp_json_encode( __( 'Logo (Light Mode)', 'emerge-mono' ) ); ?>,
            single: <?php echo wp_json_encode( __( 'Logo', 'emerge-mono' ) ); ?>
        };
        var logoHints = {
            dark:  <?php echo wp_json_encode( __( 'Dark mode is selected, so only the dark logo is needed.', 'emerge-mono' ) ); ?>,
            light: <?php echo wp_json_encode( __( 'Light mode is selected, so only the light logo is needed.', 'emerge-mono' ) ); ?>,
            auto:  <?php echo wp_json_encode( __( 'Auto mode is selected. Set both a dark and a light logo for the best result.', 'emerge-mono' ) ); ?>
        };

        // カラーモードに応じてロゴスロットの表示を切り替える
        function syncLogoSlots(){
            var modeEl = document.querySelector('input[name="en-wiz-mode"]:checked');
            var mode = modeEl ? modeEl.value : 'dark';
            if ( mode === 'dark' ) {
                slotDark.classList.remove('is-hidden');
                slotLight.classList.add('is-hidden');
                labelDark.textContent = logoLabels.single;
                logoHint.textContent = logoHints.dark;
            } else if ( mode === 'light' ) {
                slotDark.classList.add('is-hidden');
                slotLight.classList.remove('is-hidden');
                labelLight.textContent = logoLabels.single;
                logoHint.textContent = logoHints.light;
            } else { // auto
                slotDark.classList.remove('is-hidden');
                slotLight.classList.remove('is-hidden');
                labelDark.textContent = logoLabels.dark;
                labelLight.textContent = logoLabels.light;
                logoHint.textContent = logoHints.auto;
            }
        }

        /* ===== カラーモード選択 ===== */
        Array.prototype.slice.call(document.querySelectorAll('.en-wiz-mode')).forEach(function(m){
            m.addEventListener('click', function(){
                document.querySelectorAll('.en-wiz-mode').forEach(function(x){ x.classList.remove('active'); });
                m.classList.add('active');
                m.querySelector('input').checked = true;
                syncLogoSlots();
            });
        });
        syncLogoSlots(); // 初期表示を反映

        /* ===== ロゴ：メディアライブラリ（スロットごと） ===== */
        var logoEls = {
            dark:  { url: document.getElementById('en-wiz-logo-url'),       img: document.getElementById('en-wiz-logo-img-dark'),  prev: document.getElementById('en-wiz-logo-preview-dark') },
            light: { url: document.getElementById('en-wiz-logo-url-light'), img: document.getElementById('en-wiz-logo-img-light'), prev: document.getElementById('en-wiz-logo-preview-light') }
        };
        var mediaFrames = {};
        function bindMediaButtons(){
            Array.prototype.slice.call(document.querySelectorAll('.en-wiz-media-btn')).forEach(function(btn){
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    if ( typeof wp === 'undefined' || ! wp.media ) {
                        msg.textContent = i18n.mediaUnavailable;
                        return;
                    }
                    var target = btn.dataset.target;
                    if ( ! mediaFrames[target] ) {
                        var frame = wp.media({
                            title: i18n.mediaTitle,
                            button: { text: i18n.mediaBtn },
                            library: { type: 'image' },
                            multiple: false
                        });
                        // メディアモーダル表示中はウィザードの暗い背景を透明化（二重暗転を防ぐ）
                        frame.on('open', function(){
                            var ov = document.querySelector('.en-wiz-overlay');
                            if ( ov ) ov.style.background = 'transparent';
                        });
                        frame.on('close', function(){
                            var ov = document.querySelector('.en-wiz-overlay');
                            if ( ov ) ov.style.background = '';
                        });
                        frame.on('select', function(){
                            var att = frame.state().get('selection').first().toJSON();
                            var el = logoEls[target];
                            el.url.value = att.url;
                            el.img.src = att.url;
                            el.prev.style.display = '';
                            var rm = document.querySelector('.en-wiz-logo-remove[data-target="' + target + '"]');
                            if ( rm ) rm.style.display = '';
                            msg.textContent = '';
                        });
                        mediaFrames[target] = frame;
                    }
                    mediaFrames[target].open();
                });
            });
        }
        // wp.media が未読込でも、読み込まれ次第バインドする（隠しページでの読み込み遅延対策）
        if ( typeof wp !== 'undefined' && wp.media ) {
            bindMediaButtons();
        } else {
            var mediaWait = 0;
            var mediaTimer = setInterval(function(){
                mediaWait++;
                if ( typeof wp !== 'undefined' && wp.media ) {
                    clearInterval(mediaTimer);
                    bindMediaButtons();
                } else if ( mediaWait > 40 ) { // 約10秒待っても来なければ諦めてバインド（クリック時にメッセージ表示）
                    clearInterval(mediaTimer);
                    bindMediaButtons();
                }
            }, 250);
        }
        Array.prototype.slice.call(document.querySelectorAll('.en-wiz-logo-remove')).forEach(function(rm){
            rm.addEventListener('click', function(){
                var t = rm.dataset.target;
                var el = logoEls[t];
                el.url.value = '';
                el.img.src = '';
                el.prev.style.display = 'none';
                rm.style.display = 'none';
            });
        });

        /* ===== 完了フラグ ===== */
        function markDone(){
            var d = new FormData();
            d.append('action', 'en_wizard_done');
            d.append('nonce', doneNonce);
            return fetch(ajaxurl, { method:'POST', body:d }).catch(function(){});
        }
        document.getElementById('en-wiz-skip-all').addEventListener('click', function(e){
            // スキップ時も現在の入力を保存してから離脱（入力済みを失わない）
            e.preventDefault();
            var href = this.getAttribute('href');
            saveSettings().then(function(){
                markDone().then(function(){ location.href = href; });
            });
        });

        /* ===== 設定保存 ===== */
        function saveSettings(){
            var d = new FormData();
            d.append('action', 'en_wizard_save');
            d.append('nonce', saveNonce);
            d.append('site_name',    document.getElementById('en-wiz-site-name').value);
            d.append('site_tagline', document.getElementById('en-wiz-tagline').value);
            d.append('logo_url',       logoEls.dark.url.value);
            d.append('logo_url_light', logoEls.light.url.value);
            var modeEl = document.querySelector('input[name="en-wiz-mode"]:checked');
            d.append('design_mode',  modeEl ? modeEl.value : 'dark');
            return fetch(ajaxurl, { method:'POST', body:d }).then(function(r){ return r.json(); }).catch(function(){});
        }

        /* ===== ページ作成 ===== */
        function createOne(def){
            var data = new FormData();
            data.append('action', 'en_create_em_page');
            data.append('nonce', nonce);
            data.append('title', def.title);
            data.append('slug',  def.slug);
            data.append('sc',    def.sc);
            return fetch(ajaxurl, { method:'POST', body:data }).then(function(r){ return r.json(); });
        }

        /* ===== 完了処理（保存→ページ作成→遷移） ===== */
        finishBtn.addEventListener('click', function(){
            finishBtn.disabled = true;
            backBtn.disabled = true;
            msg.textContent = i18n.saving;

            saveSettings().then(function(){
                var selected = checks().filter(function(c){ return c.checked; }).map(function(c){
                    return { sc: c.value, title: c.dataset.title, slug: c.dataset.slug };
                });

                if ( ! selected.length ) {
                    // ページ未選択でも設定だけ保存して完了
                    msg.textContent = i18n.allDone;
                    markDone().then(function(){ setTimeout(function(){ location.href = dashUrl; }, 700); });
                    return;
                }

                msg.textContent = i18n.creating;
                var idx = 0;
                function next(){
                    if ( idx >= selected.length ) {
                        msg.textContent = i18n.allDone;
                        markDone().then(function(){ setTimeout(function(){ location.href = dashUrl; }, 700); });
                        return;
                    }
                    var def = selected[idx++];
                    createOne(def).then(function(res){
                        msg.textContent = ( res && res.success )
                            ? i18n.doneOne.replace('%s', def.title)
                            : i18n.failOne.replace('%s', def.title);
                        next();
                    }).catch(function(){
                        msg.textContent = i18n.failOne.replace('%s', def.title);
                        next();
                    });
                }
                next();
            });
        });
    })();
    </script>
    <?php
}

// ── ウィザード完了フラグ保存 AJAX ──
add_action( 'wp_ajax_en_wizard_done', 'en_wizard_ajax_done' );
function en_wizard_ajax_done() {
    if ( ! check_ajax_referer('en_wizard_done', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error('Permission denied');
    }
    update_option( 'en_wizard_completed', 1 );
    wp_send_json_success();
}

// ── ウィザードの基本設定保存 AJAX（既存タブと同じ en_options キーへ） ──
add_action( 'wp_ajax_en_wizard_save', 'en_wizard_ajax_save' );
function en_wizard_ajax_save() {
    if ( ! check_ajax_referer('en_wizard_save', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error('Permission denied');
    }
    $opts = get_option( 'en_options', array() );
    if ( ! is_array($opts) ) $opts = array();

    // 既存タブ（Site Settings / Design）と同じサニタイズ・同じキー
    $opts['site_name']      = sanitize_text_field( isset($_POST['site_name'])    ? $_POST['site_name']    : '' );
    $opts['site_tagline']   = sanitize_text_field( isset($_POST['site_tagline']) ? $_POST['site_tagline'] : '' );
    $opts['logo_url']       = esc_url_raw(         isset($_POST['logo_url'])       ? $_POST['logo_url']       : '' );
    $opts['logo_url_light'] = esc_url_raw(         isset($_POST['logo_url_light']) ? $_POST['logo_url_light'] : '' );
    $mode = isset($_POST['design_mode']) ? $_POST['design_mode'] : 'dark';
    $opts['design_mode']  = in_array( $mode, array('dark','light','auto'), true ) ? $mode : 'dark';

    update_option( 'en_options', $opts );
    wp_send_json_success();
}

/**
 * ページ作成 AJAX（ウィザード用・自前実装）
 * editor モジュールの ene_create_em_page に依存せず常に使えるようにする。
 * 既に同じショートコードのページがある場合はスキップして成功扱い。
 */
add_action( 'wp_ajax_en_create_em_page', 'en_wizard_ajax_create_page' );
function en_wizard_ajax_create_page() {
    global $wpdb;
    if ( ! check_ajax_referer('ene_create_em_page', 'nonce', false) || ! current_user_can('edit_pages') ) {
        wp_send_json_error('Permission denied');
    }
    $title = sanitize_text_field( isset($_POST['title']) ? $_POST['title'] : '' );
    $slug  = sanitize_title( isset($_POST['slug']) ? $_POST['slug'] : '' );
    $sc    = sanitize_text_field( isset($_POST['sc']) ? $_POST['sc'] : '' );

    // ショートコードはホワイトリスト照合（任意文字列の投入を防ぐ）
    $allowed = wp_list_pluck( en_get_em_page_defs(), 'sc' );
    if ( ! $title || ! $sc || ! in_array( $sc, $allowed, true ) ) {
        wp_send_json_error('Invalid parameters');
    }

    // 既存重複チェック（既にあればスキップ＝成功）
    $existing = en_get_em_sc_page_map();
    if ( isset( $existing[ $sc ] ) ) {
        wp_send_json_success( array( 'page_id' => $existing[ $sc ]->ID, 'skipped' => true ) );
    }

    $desired_slug = $slug ?: sanitize_title( $title );

    // 希望スラッグが「ゴミ箱・下書き等の残骸」に占有されている場合は解放する。
    // （これがないとWordPressが terms → terms-2 のように自動採番してしまう）
    en_wizard_reclaim_slug( $desired_slug, $sc );

    $page_id = wp_insert_post( array(
        'post_title'   => $title,
        'post_name'    => $desired_slug,
        'post_content' => $sc,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ) );
    if ( is_wp_error($page_id) ) {
        wp_send_json_error( $page_id->get_error_message() );
    }

    // 挿入後に WordPress が suffix（terms-2 等）を付けていたら、
    // 希望スラッグが他の「生きた固定ページ」に使われていない限り強制的に直す。
    // wp_update_post() は再び wp_unique_post_slug() を通すため suffix が戻り得る。
    // そこで衝突が無いと確認できた場合のみ、post_name を直接DBに書き込んで確定させる。
    $actual_slug = get_post_field( 'post_name', $page_id );
    if ( $actual_slug !== $desired_slug && ! en_wizard_slug_taken_by_live_page( $desired_slug, $page_id ) ) {
        $wpdb->update(
            $wpdb->posts,
            array( 'post_name' => $desired_slug ),
            array( 'ID' => (int) $page_id )
        );
        clean_post_cache( $page_id );
    }

    // トップページは静的フロントページに自動設定
    if ( $sc === '[emerge_mono_top]' ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_id );
    }
    wp_send_json_success( array( 'page_id' => $page_id ) );
}

/**
 * 希望スラッグが「実際にURL衝突する公開/下書きの固定ページ」に占有されているか。
 * 固定ページ(page)同士のみがURL階層で衝突するため、page に限定して判定する。
 * （post / attachment / term の同名は page のパーマリンクとは衝突しないので無視）
 * ゴミ箱・auto-draft・自分自身は除外。
 */
function en_wizard_slug_taken_by_live_page( $desired_slug, $exclude_id ) {
    global $wpdb;
    $id = $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
          WHERE post_type = 'page'
            AND post_name = %s
            AND post_status IN ( 'publish', 'draft', 'pending', 'private', 'future' )
            AND ID != %d
          LIMIT 1",
        $desired_slug,
        (int) $exclude_id
    ) );
    return ! empty( $id );
}

/**
 * 希望スラッグを占有している「破棄可能な残骸」を削除してスラッグを解放する。
 *
 * WordPress は wp_unique_post_slug() で、固定ページのスラッグが
 * 他の page だけでなく、同名スラッグを持つ post / attachment が存在する場合にも
 * 連番（terms-2 等）を付けることがある。そのため対象を page に限定せず、
 * 「安全に破棄できる残骸」を全 post_type 横断で掃除する。
 *
 * 破棄対象（安全なものに限定）：
 *   - ゴミ箱(trash) のレコード
 *   - auto-draft のレコード（ブロックエディタ等が作る空の自動下書き）
 *   - 本文に同じ Emerge Mono ショートコードを含む下書き（自前生成の残骸）
 * 公開中・他人が作った実コンテンツには一切触れない（その場合は採番を許容）。
 */
function en_wizard_reclaim_slug( $desired_slug, $sc ) {
    global $wpdb;
    if ( ! $desired_slug ) return;

    // 同一スラッグ（terms / terms-2 / terms__trashed 等の派生含む）を全post_type・全statusから取得
    $candidates = $wpdb->get_results( $wpdb->prepare(
        "SELECT ID, post_type, post_status, post_content, post_name
           FROM {$wpdb->posts}
          WHERE post_name = %s
             OR post_name LIKE %s
             OR post_name LIKE %s",
        $desired_slug,
        $wpdb->esc_like( $desired_slug ) . '-%',        // terms-2, terms-3 ...
        $wpdb->esc_like( $desired_slug ) . '__trashed'  // ゴミ箱時のリネーム
    ) );
    if ( empty( $candidates ) ) return;

    foreach ( $candidates as $c ) {
        // 派生スラッグ（terms-2 等）は「素のslug」「__trashed付き」に正規化して、対象slugと一致するものだけ扱う
        $base = preg_replace( '/__trashed$/', '', $c->post_name );
        $is_same_family = ( $base === $desired_slug ) || preg_match( '/^' . preg_quote($desired_slug, '/') . '-\d+$/', $base );
        if ( ! $is_same_family ) continue;

        $is_trash     = ( $c->post_status === 'trash' );
        $is_autodraft = ( $c->post_status === 'auto-draft' );
        $is_own_draft = ( $c->post_status === 'draft' && strpos( (string) $c->post_content, $sc ) !== false );

        if ( $is_trash || $is_autodraft || $is_own_draft ) {
            // 完全削除してスラッグを解放
            wp_delete_post( (int) $c->ID, true );
        }
    }
}

// ── 設定画面からいつでも再表示できるリンク用ヘルパ ──
function en_wizard_url() {
    return admin_url('admin.php?page=en-setup-wizard');
}
