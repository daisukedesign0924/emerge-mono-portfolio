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
function emono_get_em_page_defs() {
    $defs = array(
        array( 'sc' => '[emerge_mono_top]',      'title' => 'Home',             'slug' => '',               'desc' => __( 'Top page — logo, site name, buttons', 'emerge-mono-portfolio' ), 'icon' => '🏠', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_about]',     'title' => 'Profile',          'slug' => 'about',          'desc' => __( 'Profile page — bio, social links', 'emerge-mono-portfolio' ),    'icon' => '👤', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_works]',     'title' => 'Works',            'slug' => 'works',          'desc' => __( 'Works page — portfolio grid', 'emerge-mono-portfolio' ),         'icon' => '📂', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_news]',      'title' => 'News',             'slug' => 'news',           'desc' => __( 'News page — post list', 'emerge-mono-portfolio' ),               'icon' => '📰', 'recommended' => true ),
        array( 'sc' => '[emerge_mono_contact]',   'title' => 'Contact',          'slug' => 'contact',        'desc' => __( 'Contact page — form', 'emerge-mono-portfolio' ),                 'icon' => '✉',  'recommended' => true ),
        array( 'sc' => '[emerge_mono_privacy]',   'title' => 'Privacy Policy',   'slug' => 'privacy-policy', 'desc' => __( 'Privacy policy page', 'emerge-mono-portfolio' ),                 'icon' => '🔒', 'recommended' => false ),
        array( 'sc' => '[emerge_mono_terms]',     'title' => 'Terms of Service', 'slug' => 'terms',          'desc' => __( 'Terms of service page', 'emerge-mono-portfolio' ),               'icon' => '📋', 'recommended' => false ),
        array( 'sc' => '[emerge_mono_estimate]',  'title' => 'Estimate',         'slug' => 'estimate',       'desc' => __( 'Estimate simulator page', 'emerge-mono-portfolio' ),             'icon' => '💰', 'recommended' => false ),
    );

    return apply_filters( 'emono_page_defs', $defs );
}

/**
 * Map each shortcode to an already-existing page (publish/draft), if any.
 * Returns array( '[shortcode]' => WP_Post ).
 */
function emono_get_em_sc_page_map() {
    $existing = get_pages( array( 'post_status' => array('publish','draft'), 'sort_column' => 'menu_order' ) );
    $map = array();
    foreach ( $existing as $page ) {
        foreach ( emono_get_em_page_defs() as $def ) {
            if ( strpos( $page->post_content, $def['sc'] ) !== false ) {
                $map[ $def['sc'] ] = $page;
            }
        }
    }
    return $map;
}

// ── 有効化時にウィザード表示フラグを立てる ──
function emono_wizard_set_redirect_flag() {
    // 一括有効化（複数プラグイン同時）の場合はリダイレクトしない
    set_transient( 'en_show_setup_wizard', 1, 60 );
}

// ── 有効化直後、最初の管理画面アクセスでウィザードへ誘導 ──
add_action( 'admin_init', 'emono_wizard_maybe_redirect' );
function emono_wizard_maybe_redirect() {
    if ( ! get_transient( 'en_show_setup_wizard' ) ) return;
    delete_transient( 'en_show_setup_wizard' );

    // 一括有効化・自動有効化時はリダイレクトを避ける
    if ( isset($_GET['activate-multi']) || wp_doing_ajax() ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    if ( ! current_user_can('manage_options') ) return;

    // すでに完了済みなら出さない
    if ( get_option('en_wizard_completed') ) return;

    wp_safe_redirect( admin_url('admin.php?page=en-setup-wizard') );
    exit;
}

// ── ウィザード画面を（メニュー非表示の隠しページとして）登録 ──
add_action( 'admin_menu', 'emono_wizard_register_page', 99 );
function emono_wizard_register_page() {
    $hook = add_submenu_page(
        null, // 親なし＝メニューに出さない
        __( 'Emerge Mono Setup', 'emerge-mono-portfolio' ),
        __( 'Emerge Mono Setup', 'emerge-mono-portfolio' ),
        'manage_options',
        'en-setup-wizard',
        'emono_wizard_render'
    );
    // このページが読み込まれる時にだけメディアライブラリをenqueue（確実な方法）
    if ( $hook ) {
        add_action( 'load-' . $hook, 'emono_wizard_load_assets' );
    }
}
function emono_wizard_load_assets() {
    add_action( 'admin_enqueue_scripts', 'emono_wizard_enqueue' );
}

// ── ウィザード画面では他プラグインのadmin noticeを抑制（全画面表示のため） ──
add_action( 'admin_head', 'emono_wizard_suppress_notices' );
function emono_wizard_suppress_notices() {
    if ( ! isset($_GET['page']) || $_GET['page'] !== 'en-setup-wizard' ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    remove_all_actions( 'admin_notices' );
    remove_all_actions( 'all_admin_notices' );
}

// ── メディアライブラリ読み込み（保険として page 判定でも実行） ──
add_action( 'admin_enqueue_scripts', 'emono_wizard_enqueue' );
function emono_wizard_enqueue( $hook = '' ) {
    if ( ! isset($_GET['page']) || $_GET['page'] !== 'en-setup-wizard' ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    wp_enqueue_media(); // 重複呼び出しはWP側で無害化される
    wp_enqueue_style( 'emerge-mono-wizard', EMONO_URL . 'admin/assets/setup-wizard.css', array(), EMONO_VERSION );
    wp_enqueue_script( 'emerge-mono-wizard', EMONO_URL . 'admin/assets/setup-wizard.js', array(), EMONO_VERSION, true );
}

// ── ウィザード本体描画 ──
function emono_wizard_render() {
    $defs    = emono_get_em_page_defs();
    $sc_map  = emono_get_em_sc_page_map();
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
        __( 'Site Info', 'emerge-mono-portfolio' ),
        __( 'Color', 'emerge-mono-portfolio' ),
        __( 'Logo', 'emerge-mono-portfolio' ),
        __( 'Pages', 'emerge-mono-portfolio' ),
    );
    wp_localize_script( 'emerge-mono-wizard', 'emonoWizardSettings', array(
        'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
        'createNonce' => wp_create_nonce( 'ene_create_em_page' ),
        'doneNonce'   => wp_create_nonce( 'en_wizard_done' ),
        'saveNonce'   => wp_create_nonce( 'en_wizard_save' ),
        'dashUrl'     => $dash_url,
        'i18n'        => array(
            'none'             => __( 'Please select at least one page.', 'emerge-mono-portfolio' ),
            'saving'           => __( 'Saving settings…', 'emerge-mono-portfolio' ),
            'saved'            => __( 'Saved ✓', 'emerge-mono-portfolio' ),
            'creating'         => __( 'Creating pages…', 'emerge-mono-portfolio' ),
            /* translators: %s: the created page name */
            'doneOne'          => __( 'Created: %s', 'emerge-mono-portfolio' ),
            'allDone'          => __( 'All set! Redirecting…', 'emerge-mono-portfolio' ),
            /* translators: %s: the page name that failed */
            'failOne'          => __( 'Failed: %s', 'emerge-mono-portfolio' ),
            'mediaTitle'       => __( 'Select Logo', 'emerge-mono-portfolio' ),
            'mediaBtn'         => __( 'Use this logo', 'emerge-mono-portfolio' ),
            'mediaUnavailable' => __( 'Media library could not load. Please reload the page and try again.', 'emerge-mono-portfolio' ),
        ),
        'logoLabels'  => array(
            'dark'   => __( 'Logo (Dark Mode)', 'emerge-mono-portfolio' ),
            'light'  => __( 'Logo (Light Mode)', 'emerge-mono-portfolio' ),
            'single' => __( 'Logo', 'emerge-mono-portfolio' ),
        ),
        'logoHints'   => array(
            'dark'  => __( 'Dark mode is selected, so only the dark logo is needed.', 'emerge-mono-portfolio' ),
            'light' => __( 'Light mode is selected, so only the light logo is needed.', 'emerge-mono-portfolio' ),
            'auto'  => __( 'Auto mode is selected. Set both a dark and a light logo for the best result.', 'emerge-mono-portfolio' ),
        ),
    ) );
    ?>
    <div class="en-wiz-overlay">
        <div class="en-wiz-modal">

            <!-- 進捗インジケーター -->
            <div class="en-wiz-steps" id="en-wiz-steps">
                <?php foreach ( $steps as $i => $label ) : ?>
                <div class="en-wiz-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo (int) $i; ?>">
                    <span class="en-wiz-step-num"><?php echo (int) ( $i + 1 ); ?></span>
                    <span class="en-wiz-step-label"><?php echo esc_html( $label ); ?></span>
                </div>
                <?php if ( $i < count($steps) - 1 ) : ?><span class="en-wiz-step-line"></span><?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="en-wiz-body">

                <!-- STEP 1: サイト名・キャッチコピー -->
                <section class="en-wiz-pane active" data-pane="0">
                    <div class="en-wiz-head">
                        <div class="en-wiz-badge"><?php esc_html_e( 'Welcome', 'emerge-mono-portfolio' ); ?></div>
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Emerge Mono Setup', 'emerge-mono-portfolio' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Let\'s set up the basics. Every step is optional — you can skip anything and change it later in the settings.', 'emerge-mono-portfolio' ); ?></p>
                    </div>
                    <div class="en-wiz-form">
                        <label class="en-wiz-field">
                            <span class="en-wiz-flabel"><?php esc_html_e( 'Site Name', 'emerge-mono-portfolio' ); ?></span>
                            <input type="text" id="en-wiz-site-name" class="en-wiz-input" value="<?php echo esc_attr( $cur_name ); ?>" placeholder="<?php echo esc_attr( get_bloginfo('name') ); ?>">
                        </label>
                        <label class="en-wiz-field">
                            <span class="en-wiz-flabel"><?php esc_html_e( 'Tagline', 'emerge-mono-portfolio' ); ?></span>
                            <input type="text" id="en-wiz-tagline" class="en-wiz-input" value="<?php echo esc_attr( $cur_tagline ); ?>" placeholder="<?php echo esc_attr__( 'Web Creator', 'emerge-mono-portfolio' ); ?>">
                        </label>
                    </div>
                </section>

                <!-- STEP 2: カラーモード -->
                <section class="en-wiz-pane" data-pane="1">
                    <div class="en-wiz-head">
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Color Mode', 'emerge-mono-portfolio' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Choose how your site looks. Auto follows each visitor\'s device setting.', 'emerge-mono-portfolio' ); ?></p>
                    </div>
                    <div class="en-wiz-modes" id="en-wiz-modes">
                        <?php
                        $mode_opts = array(
                            'dark'  => array( __( 'Dark', 'emerge-mono-portfolio' ),  __( 'Dark background, white text', 'emerge-mono-portfolio' ) ),
                            'light' => array( __( 'Light', 'emerge-mono-portfolio' ), __( 'Light background, black text', 'emerge-mono-portfolio' ) ),
                            'auto'  => array( __( 'Auto', 'emerge-mono-portfolio' ),  __( 'Match device setting', 'emerge-mono-portfolio' ) ),
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
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Logo', 'emerge-mono-portfolio' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Upload a logo for the site header. You can skip this and add it later.', 'emerge-mono-portfolio' ); ?></p>
                    </div>
                    <div class="en-wiz-logos">
                        <!-- ダーク用ロゴスロット -->
                        <div class="en-wiz-logo-slot" data-slot="dark">
                            <div class="en-wiz-logo-slot-label" id="en-wiz-logo-label-dark"><?php esc_html_e( 'Logo (Dark Mode)', 'emerge-mono-portfolio' ); ?></div>
                            <div class="en-wiz-logo-preview en-wiz-logo-prev-dark" id="en-wiz-logo-preview-dark" style="<?php echo $cur_logo ? '' : 'display:none'; ?>">
                                <img id="en-wiz-logo-img-dark" src="<?php echo esc_url( $cur_logo ); ?>" alt="">
                            </div>
                            <input type="hidden" id="en-wiz-logo-url" value="<?php echo esc_attr( $cur_logo ); ?>">
                            <div class="en-wiz-logo-btns">
                                <button type="button" class="en-wiz-media-btn" data-target="dark"><?php esc_html_e( 'Select from Media Library', 'emerge-mono-portfolio' ); ?></button>
                                <button type="button" class="en-wiz-link en-wiz-logo-remove" data-target="dark" style="<?php echo $cur_logo ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove', 'emerge-mono-portfolio' ); ?></button>
                            </div>
                        </div>
                        <!-- ライト用ロゴスロット -->
                        <div class="en-wiz-logo-slot" data-slot="light">
                            <div class="en-wiz-logo-slot-label" id="en-wiz-logo-label-light"><?php esc_html_e( 'Logo (Light Mode)', 'emerge-mono-portfolio' ); ?></div>
                            <div class="en-wiz-logo-preview en-wiz-logo-prev-light" id="en-wiz-logo-preview-light" style="<?php echo $cur_logo_light ? '' : 'display:none'; ?>">
                                <img id="en-wiz-logo-img-light" src="<?php echo esc_url( $cur_logo_light ); ?>" alt="">
                            </div>
                            <input type="hidden" id="en-wiz-logo-url-light" value="<?php echo esc_attr( $cur_logo_light ); ?>">
                            <div class="en-wiz-logo-btns">
                                <button type="button" class="en-wiz-media-btn" data-target="light"><?php esc_html_e( 'Select from Media Library', 'emerge-mono-portfolio' ); ?></button>
                                <button type="button" class="en-wiz-link en-wiz-logo-remove" data-target="light" style="<?php echo $cur_logo_light ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove', 'emerge-mono-portfolio' ); ?></button>
                            </div>
                        </div>
                    </div>
                    <p class="en-wiz-logo-hint" id="en-wiz-logo-hint"></p>
                </section>

                <!-- STEP 4: ページ選択 -->
                <section class="en-wiz-pane" data-pane="3">
                    <div class="en-wiz-head">
                        <h1 class="en-wiz-title"><?php esc_html_e( 'Create Pages', 'emerge-mono-portfolio' ); ?></h1>
                        <p class="en-wiz-lead"><?php esc_html_e( 'Select the pages to create. Each is created with its shortcode already inserted.', 'emerge-mono-portfolio' ); ?></p>
                    </div>
                    <div class="en-wiz-toolbar">
                        <button type="button" class="en-wiz-link" id="en-wiz-select-all"><?php esc_html_e( 'Select all', 'emerge-mono-portfolio' ); ?></button>
                        <span class="en-wiz-sep">·</span>
                        <button type="button" class="en-wiz-link" id="en-wiz-select-recommended"><?php esc_html_e( 'Recommended only', 'emerge-mono-portfolio' ); ?></button>
                        <span class="en-wiz-sep">·</span>
                        <button type="button" class="en-wiz-link" id="en-wiz-clear"><?php esc_html_e( 'Clear', 'emerge-mono-portfolio' ); ?></button>
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
                                   <?php echo esc_attr( $checked ); ?>
                                   <?php echo $exists ? 'disabled' : ''; ?>>
                            <span class="en-wiz-icon"><?php echo esc_html( $def['icon'] ); // emoji ?></span>
                            <span class="en-wiz-meta">
                                <span class="en-wiz-name"><?php echo esc_html( $def['title'] ); ?></span>
                                <span class="en-wiz-desc"><?php echo esc_html( $def['desc'] ); ?></span>
                                <code class="en-wiz-sc"><?php echo esc_html( $def['sc'] ); ?></code>
                            </span>
                            <?php if ( $exists ) : ?>
                                <span class="en-wiz-tag en-wiz-tag-done"><?php esc_html_e( 'Created', 'emerge-mono-portfolio' ); ?></span>
                            <?php elseif ( $def['recommended'] ) : ?>
                                <span class="en-wiz-tag en-wiz-tag-rec"><?php esc_html_e( 'Recommended', 'emerge-mono-portfolio' ); ?></span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>

            <div class="en-wiz-msg" id="en-wiz-msg"></div>

            <div class="en-wiz-actions">
                <a href="<?php echo esc_url( $dash_url ); ?>" class="en-wiz-skip" id="en-wiz-skip-all"><?php esc_html_e( 'Skip setup', 'emerge-mono-portfolio' ); ?></a>
                <div class="en-wiz-nav">
                    <button type="button" class="en-wiz-back" id="en-wiz-back" style="display:none"><?php esc_html_e( '← Back', 'emerge-mono-portfolio' ); ?></button>
                    <button type="button" class="en-wiz-next" id="en-wiz-next"><?php esc_html_e( 'Save & Next →', 'emerge-mono-portfolio' ); ?></button>
                    <button type="button" class="en-wiz-create" id="en-wiz-finish" style="display:none"><?php esc_html_e( 'Finish & create pages', 'emerge-mono-portfolio' ); ?></button>
                </div>
            </div>

            <p class="en-wiz-foot"><?php esc_html_e( 'Everything here can be changed later from the plugin settings.', 'emerge-mono-portfolio' ); ?></p>

        </div>
    </div>
    <?php
}

// ── ウィザード完了フラグ保存 AJAX ──
add_action( 'wp_ajax_en_wizard_done', 'emono_wizard_ajax_done' );
function emono_wizard_ajax_done() {
    if ( ! check_ajax_referer('en_wizard_done', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error('Permission denied');
    }
    update_option( 'en_wizard_completed', 1 );
    wp_send_json_success();
}

// ── ウィザードの基本設定保存 AJAX（既存タブと同じ en_options キーへ） ──
add_action( 'wp_ajax_en_wizard_save', 'emono_wizard_ajax_save' );
function emono_wizard_ajax_save() {
    if ( ! check_ajax_referer('en_wizard_save', 'nonce', false) || ! current_user_can('manage_options') ) {
        wp_send_json_error('Permission denied');
    }
    $opts = get_option( 'en_options', array() );
    if ( ! is_array($opts) ) $opts = array();

    // 既存タブ（Site Settings / Design）と同じサニタイズ・同じキー
    $opts['site_name']      = sanitize_text_field( isset($_POST['site_name'])    ? wp_unslash($_POST['site_name']) : '' );
    $opts['site_tagline']   = sanitize_text_field( isset($_POST['site_tagline']) ? wp_unslash($_POST['site_tagline']) : '' );
    $opts['logo_url']       = esc_url_raw(         isset($_POST['logo_url'])       ? wp_unslash($_POST['logo_url']) : '' );
    $opts['logo_url_light'] = esc_url_raw(         isset($_POST['logo_url_light']) ? wp_unslash($_POST['logo_url_light']) : '' );
    $mode = sanitize_key( wp_unslash( $_POST['design_mode'] ?? 'dark' ) );
    $opts['design_mode']  = in_array( $mode, array('dark','light','auto'), true ) ? $mode : 'dark';

    update_option( 'en_options', $opts );
    wp_send_json_success();
}

/**
 * ページ作成 AJAX（ウィザード用・自前実装）
 * editor モジュールの ene_create_em_page に依存せず常に使えるようにする。
 * 既に同じショートコードのページがある場合はスキップして成功扱い。
 */
add_action( 'wp_ajax_en_create_em_page', 'emono_wizard_ajax_create_page' );
function emono_wizard_ajax_create_page() {
    global $wpdb;
    if ( ! check_ajax_referer('ene_create_em_page', 'nonce', false) || ! current_user_can('edit_pages') ) {
        wp_send_json_error('Permission denied');
    }
    $title = sanitize_text_field( isset($_POST['title']) ? wp_unslash($_POST['title']) : '' );
    $slug  = sanitize_title( isset($_POST['slug']) ? wp_unslash($_POST['slug']) : '' );
    $sc    = sanitize_text_field( isset($_POST['sc']) ? wp_unslash($_POST['sc']) : '' );

    // ショートコードはホワイトリスト照合（任意文字列の投入を防ぐ）
    $allowed = wp_list_pluck( emono_get_em_page_defs(), 'sc' );
    if ( ! $title || ! $sc || ! in_array( $sc, $allowed, true ) ) {
        wp_send_json_error('Invalid parameters');
    }

    // 既存重複チェック（既にあればスキップ＝成功）
    $existing = emono_get_em_sc_page_map();
    if ( isset( $existing[ $sc ] ) ) {
        wp_send_json_success( array( 'page_id' => $existing[ $sc ]->ID, 'skipped' => true ) );
    }

    $desired_slug = $slug ?: sanitize_title( $title );

    // 希望スラッグが「ゴミ箱・下書き等の残骸」に占有されている場合は解放する。
    // （これがないとWordPressが terms → terms-2 のように自動採番してしまう）
    emono_wizard_reclaim_slug( $desired_slug, $sc );

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
    if ( $actual_slug !== $desired_slug && ! emono_wizard_slug_taken_by_live_page( $desired_slug, $page_id ) ) {
        $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
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
function emono_wizard_slug_taken_by_live_page( $desired_slug, $exclude_id ) {
    global $wpdb;
    $id = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
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
function emono_wizard_reclaim_slug( $desired_slug, $sc ) {
    global $wpdb;
    if ( ! $desired_slug ) return;

    // 同一スラッグ（terms / terms-2 / terms__trashed 等の派生含む）を全post_type・全statusから取得
    $candidates = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
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
function emono_wizard_url() {
    return admin_url('admin.php?page=en-setup-wizard');
}
