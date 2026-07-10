<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_nav($opts) {
    emono_admin_notice();
    $nav     = isset($opts['nav_items'])  ? $opts['nav_items']  : array();
    $nav_mode = isset($opts['nav_mode'])  ? $opts['nav_mode']   : 'auto';
    $footer_nav = isset($opts['footer_nav_items']) ? $opts['footer_nav_items'] : array();

    // Page一覧取得
    $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'menu_order' ) );

    // WPメニュー一覧取得
    $wp_menus = wp_get_nav_menus();
    $selected_menu = isset($opts['nav_wp_menu']) ? $opts['nav_wp_menu'] : '';
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_nav','en_nonce'); ?>
        <input type="hidden" name="en_action" value="nav">

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Navigation Mode', 'emerge-mono' ); ?></div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="radio" name="nav_mode" value="auto" <?php checked($nav_mode,'auto'); ?>>
                    <span><?php esc_html_e( 'Auto (show published pages automatically)', 'emerge-mono' ); ?></span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="radio" name="nav_mode" value="manual" <?php checked($nav_mode,'manual'); ?>>
                    <span><?php esc_html_e( 'Manual (choose pages/URLs yourself)', 'emerge-mono' ); ?></span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="radio" name="nav_mode" value="wp_menu" <?php checked($nav_mode,'wp_menu'); ?>>
                    <span><?php esc_html_e( 'Use WordPress menu (manage via Appearance → Menus)', 'emerge-mono' ); ?></span>
                </label>
            </div>
        </div>

        <!-- 手動設定エリア -->
        <div class="en-admin-section" id="en-nav-manual-section" style="<?php echo $nav_mode !== 'manual' ? 'display:none' : ''; ?>">
            <div class="en-admin-section-title"><?php esc_html_e( 'Navigation Items', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Select a page or enter a URL directly. Drag to reorder.', 'emerge-mono' ); ?></div>
            <div id="en-nav-list">
                <?php foreach ( $nav as $item ) :
                    $item_type = isset($item['type']) ? $item['type'] : 'url';
                    $item_page = isset($item['page_id']) ? $item['page_id'] : '';
                ?>
                <div class="en-nav-row" style="display:flex;flex-direction:column;gap:8px;background:rgba(255,255,255,.04);padding:12px;border-radius:4px;margin-bottom:8px;">
                    <div style="display:flex;gap:8px;align-items:center;">
                        <select name="nav_type[]" class="en-field-input" style="width:140px" onchange="enNavTypeChange(this)">
                            <option value="page" <?php selected($item_type,'page'); ?>>Page</option>
                            <option value="url"  <?php selected($item_type,'url');  ?>><?php esc_html_e( 'Direct URL', 'emerge-mono' ); ?></option>
                        </select>
                        <input type="text" name="nav_label[]" value="<?php echo esc_attr(isset($item['label']) ? $item['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Label (e.g. About)', 'emerge-mono' ); ?>" style="width:140px">
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-nav-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                    </div>
                    <div class="en-nav-page-wrap" style="<?php echo $item_type !== 'page' ? 'display:none' : ''; ?>">
                        <select name="nav_page_id[]" class="en-field-input">
                            <option value="">— Select a page —</option>
                            <?php foreach ( $pages as $page ) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($item_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?> （/<?php echo esc_html($page->post_name); ?>）
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="en-nav-url-wrap" style="<?php echo $item_type === 'page' ? 'display:none' : ''; ?>">
                        <input type="text" name="nav_url[]" value="<?php echo esc_attr(isset($item['url']) ? $item['url'] : ''); ?>" class="en-field-input" placeholder="https://">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddNav()">+ Add Item</button>
        </div>

        <!-- WPメニューSelectエリア -->
        <div class="en-admin-section" id="en-nav-wpmenu-section" style="<?php echo $nav_mode !== 'wp_menu' ? 'display:none' : ''; ?>">
            <div class="en-admin-section-title"><?php esc_html_e( 'WordPress Menu to Use', 'emerge-mono' ); ?></div>
            <?php if ( empty($wp_menus) ) : ?>
                <div class="en-field-desc"><?php
                    printf(
                        /* translators: %s is a link to the WordPress menus screen */
                        esc_html__( 'No WordPress menu has been created. Go to %s to create a menu.', 'emerge-mono' ),
                        '<a href="' . esc_url( admin_url('nav-menus.php') ) . '" style="color:inherit;text-decoration:underline">' . esc_html__( 'Appearance → Menus', 'emerge-mono' ) . '</a>'
                    );
                ?></div>
            <?php else : ?>
                <select name="nav_wp_menu" class="en-field-input" style="max-width:300px">
                    <option value="">— Select a menu —</option>
                    <?php foreach ( $wp_menus as $menu ) : ?>
                    <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($selected_menu, $menu->term_id); ?>>
                        <?php echo esc_html($menu->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <!-- フッターメニュー設定 -->
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Footer Menu', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Links shown above the copyright in the footer. For Page type, the page title is displayed (label is ignored). For Direct URL, the label is displayed.', 'emerge-mono' ); ?></div>
            <div id="en-footer-nav-list">
                <?php foreach ( $footer_nav as $item ) :
                    $fitem_type = isset($item['type']) ? $item['type'] : 'page';
                    $fitem_page = isset($item['page_id']) ? $item['page_id'] : '';
                ?>
                <div class="en-footer-nav-row" style="display:flex;flex-direction:column;gap:8px;background:rgba(255,255,255,.04);padding:12px;border-radius:4px;margin-bottom:8px;">
                    <div style="display:flex;gap:8px;align-items:center;">
                        <select name="footer_nav_type[]" class="en-field-input" style="width:140px" onchange="enFooterNavTypeChange(this)">
                            <option value="page" <?php selected($fitem_type,'page'); ?>>Page</option>
                            <option value="url"  <?php selected($fitem_type,'url');  ?>><?php esc_html_e( 'Direct URL', 'emerge-mono' ); ?></option>
                        </select>
                        <input type="text" name="footer_nav_label[]" value="<?php echo esc_attr(isset($item['label']) ? $item['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Label (URL only)', 'emerge-mono' ); ?>" style="width:140px">
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-footer-nav-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                    </div>
                    <div class="en-footer-nav-page-wrap" style="<?php echo $fitem_type !== 'page' ? 'display:none' : ''; ?>">
                        <select name="footer_nav_page_id[]" class="en-field-input">
                            <option value="">— Select a page —</option>
                            <?php foreach ( $pages as $page ) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($fitem_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?> （/<?php echo esc_html($page->post_name); ?>）
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="en-footer-nav-url-wrap" style="<?php echo $fitem_type === 'page' ? 'display:none' : ''; ?>">
                        <input type="text" name="footer_nav_url[]" value="<?php echo esc_attr(isset($item['url']) ? $item['url'] : ''); ?>" class="en-field-input" placeholder="https://">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddFooterNav()">+ Add Item</button>
        </div>

        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>

    <?php
    $en_nav_pages = array_map( function( $p ) {
        return array(
            'id'    => $p->ID,
            'title' => $p->post_title,
            'slug'  => $p->post_name,
        );
    }, $pages );
    wp_localize_script( 'emerge-mono-admin', 'emonoNavSettings', array(
        'pages' => $en_nav_pages,
        'i18n'  => array(
            'selectPage'             => __( '— Select a page —', 'emerge-mono' ),
            'page'                   => __( 'Page', 'emerge-mono' ),
            'directUrl'              => __( 'Direct URL', 'emerge-mono' ),
            'labelPlaceholder'       => esc_attr__( 'Label (e.g. About)', 'emerge-mono' ),
            'footerLabelPlaceholder' => esc_attr__( 'Label (URL only)', 'emerge-mono' ),
            'del'                    => __( 'Delete', 'emerge-mono' ),
        ),
    ) );
    wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_NAV_JS'
(function(){
    var navSettings = window.emonoNavSettings || {};
    var pages = navSettings.pages || [];
    var navI18n = navSettings.i18n || {};

    function buildPageOptions() {
        var opts = '<option value="">' + navI18n.selectPage + '</option>';
        pages.forEach(function(p){
            opts += '<option value="' + p.id + '">' + p.title + ' (/' + p.slug + ')</option>';
        });
        return opts;
    }

    // モード切替
    document.querySelectorAll('input[name="nav_mode"]').forEach(function(r){
        r.addEventListener('change', function(){
            document.getElementById('en-nav-manual-section').style.display = this.value === 'manual'  ? '' : 'none';
            document.getElementById('en-nav-wpmenu-section').style.display = this.value === 'wp_menu' ? '' : 'none';
        });
    });

    // Page/URL切替
    window.enNavTypeChange = function(sel) {
        var row = sel.closest('.en-nav-row');
        row.querySelector('.en-nav-page-wrap').style.display = sel.value === 'page' ? '' : 'none';
        row.querySelector('.en-nav-url-wrap').style.display  = sel.value === 'url'  ? '' : 'none';
    };

    // 新規行Add
    window.enAddNav = function(){
        var opts = buildPageOptions();

        var div = document.createElement('div');
        div.className = 'en-nav-row';
        div.style.cssText = 'display:flex;flex-direction:column;gap:8px;background:rgba(255,255,255,.04);padding:12px;border-radius:4px;margin-bottom:8px;';
        div.innerHTML =
            '<div style="display:flex;gap:8px;align-items:center;">'
            +'<select name="nav_type[]" class="en-field-input" style="width:140px" onchange="enNavTypeChange(this)">'
            +'<option value="page">' + navI18n.page + '</option><option value="url">' + navI18n.directUrl + '</option>'
            +'</select>'
            +'<input type="text" name="nav_label[]" class="en-field-input" placeholder="' + navI18n.labelPlaceholder + '" style="width:140px">'
            +'<button type="button" class="en-remove-btn" onclick="this.closest(\'.en-nav-row\').remove()">' + navI18n.del + '</button>'
            +'</div>'
            +'<div class="en-nav-page-wrap"><select name="nav_page_id[]" class="en-field-input">'+opts+'</select></div>'
            +'<div class="en-nav-url-wrap" style="display:none"><input type="text" name="nav_url[]" class="en-field-input" placeholder="https://"></div>';
        document.getElementById('en-nav-list').appendChild(div);
    };

    // Footer Menu: Page/URL切替
    window.enFooterNavTypeChange = function(sel) {
        var row = sel.closest('.en-footer-nav-row');
        row.querySelector('.en-footer-nav-page-wrap').style.display = sel.value === 'page' ? '' : 'none';
        row.querySelector('.en-footer-nav-url-wrap').style.display  = sel.value === 'url'  ? '' : 'none';
    };

    // Footer Menu: 新規行Add
    window.enAddFooterNav = function(){
        var opts = buildPageOptions();

        var div = document.createElement('div');
        div.className = 'en-footer-nav-row';
        div.style.cssText = 'display:flex;flex-direction:column;gap:8px;background:rgba(255,255,255,.04);padding:12px;border-radius:4px;margin-bottom:8px;';
        div.innerHTML =
            '<div style="display:flex;gap:8px;align-items:center;">'
            +'<select name="footer_nav_type[]" class="en-field-input" style="width:140px" onchange="enFooterNavTypeChange(this)">'
            +'<option value="page">' + navI18n.page + '</option><option value="url">' + navI18n.directUrl + '</option>'
            +'</select>'
            +'<input type="text" name="footer_nav_label[]" class="en-field-input" placeholder="' + navI18n.footerLabelPlaceholder + '" style="width:140px">'
            +'<button type="button" class="en-remove-btn" onclick="this.closest(\'.en-footer-nav-row\').remove()">' + navI18n.del + '</button>'
            +'</div>'
            +'<div class="en-footer-nav-page-wrap"><select name="footer_nav_page_id[]" class="en-field-input">'+opts+'</select></div>'
            +'<div class="en-footer-nav-url-wrap" style="display:none"><input type="text" name="footer_nav_url[]" class="en-field-input" placeholder="https://"></div>';
        document.getElementById('en-footer-nav-list').appendChild(div);
    };
})();
EMONO_NAV_JS
    );
    ?>
    <?php
}
