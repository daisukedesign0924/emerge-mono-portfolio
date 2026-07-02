<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function en_admin_page() {
    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    $tabs = array(
        'general'  => array( 'label' => __( 'Site Settings', 'emerge-mono' ),              'icon' => '⚙' ),
        'profile'  => array( 'label' => __( 'Profile', 'emerge-mono' ),                'icon' => '👤' ),
        'nav'      => array( 'label' => __( 'Menu Settings', 'emerge-mono' ),              'icon' => '☰' ),
        'contact'  => array( 'label' => __( 'Contact Form', 'emerge-mono' ),        'icon' => '✉' ),
        'design'   => array( 'label' => __( 'Design', 'emerge-mono' ),                'icon' => '🎨' ),
        'cpt'      => array( 'label' => __( 'Post Type (Works)', 'emerge-mono' ),     'icon' => '📂' ),
        'privacy'  => array( 'label' => __( 'Privacy Policy', 'emerge-mono' ),               'icon' => '🔒' ),
        'terms'    => array( 'label' => __( 'Terms of Service', 'emerge-mono' ),                           'icon' => '📋' ),
        'estimate' => array( 'label' => __( 'Estimate Simulator', 'emerge-mono' ),               'icon' => '💰' ),
        'editor'   => array( 'label' => __( 'Editor', 'emerge-mono' ),                            'icon' => '✏️' ),
        'shortcodes' => array( 'label' => __( 'Shortcodes', 'emerge-mono' ),        'icon' => '📋' ),
    );
    $opts = en_get_options();
    ?>
    <div class="en-admin-wrap">
        <div class="en-admin-header">
            <div class="en-admin-logo">
                <span class="en-admin-logo-mark"><img src="<?php echo esc_url( EN_URL . 'assets/img/plugin-icon.webp' ); ?>" alt="Emerge Mono" width="36" height="36"></span>
                <div>
                    <div class="en-admin-title">Emerge Mono</div>
                    <div class="en-admin-version">v<?php echo EN_VERSION; ?></div>
                </div>
            </div>
            <?php
            $en_theme_url = defined( 'EN_THEME_URL' ) ? EN_THEME_URL : '#';
            $en_theme_disabled = ( $en_theme_url === '' || $en_theme_url === '#' );
            ?>
            <a class="en-admin-theme-btn<?php echo $en_theme_disabled ? ' is-disabled' : ''; ?>"
               href="<?php echo esc_url( $en_theme_url ); ?>"
               <?php if ( ! $en_theme_disabled ) : ?>target="_blank" rel="noopener noreferrer"<?php else : ?>onclick="return false;" aria-disabled="true"<?php endif; ?>>
                <span class="en-admin-theme-btn-label"><?php esc_html_e( 'Theme', 'emerge-mono' ); ?></span>
                <span class="en-admin-theme-btn-sep">-</span>
                <span class="en-admin-theme-btn-name">Emerge Mono Zero</span>
            </a>
        </div>
        <div class="en-admin-body">
            <nav class="en-admin-sidebar">
                <?php foreach ( $tabs as $key => $info ) : ?>
                    <a href="?page=emerge-mono-portfolio&tab=<?php echo $key; ?>"
                       class="en-admin-nav-item <?php echo $tab === $key ? 'active' : ''; ?>">
                        <span class="en-admin-nav-icon"><?php echo $info['icon']; ?></span>
                        <?php echo esc_html($info['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="en-admin-content">
                <?php
                switch ( $tab ) {
                    case 'shortcodes': en_admin_tab_shortcodes(); break;
                    case 'general':  en_admin_tab_general($opts);  break;
                    case 'profile':  en_admin_tab_profile($opts);  break;
                    case 'nav':      en_admin_tab_nav($opts);      break;
                    case 'contact':  en_admin_tab_contact($opts);  break;
                    case 'design':   en_admin_tab_design($opts);  break;
                    case 'cpt':      en_admin_tab_cpt($opts);      break;
                    case 'privacy':  en_admin_tab_privacy($opts);  break;
                    case 'terms':    en_admin_tab_terms($opts);    break;
                    case 'estimate': en_admin_tab_estimate($opts); break;
                    case 'editor':   en_admin_tab_editor($opts);   break;
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

function en_admin_tab_general($opts) {
    en_admin_notice();
    $pages = get_pages( array( 'sort_column' => 'menu_order' ) );
    $btn1_label = isset($opts['top_btn1_label']) ? $opts['top_btn1_label'] : 'Profile';
    $btn1_url   = isset($opts['top_btn1_url'])   ? $opts['top_btn1_url']   : '';
    $btn2_label = isset($opts['top_btn2_label']) ? $opts['top_btn2_label'] : 'Works';
    $btn2_url   = isset($opts['top_btn2_url'])   ? $opts['top_btn2_url']   : '';
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('en_save_general','en_nonce'); ?>
        <input type="hidden" name="en_action" value="general">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Site Info', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Site Name', 'emerge-mono' ); ?></label>
                <input type="text" name="site_name" value="<?php echo esc_attr(isset($opts['site_name']) ? $opts['site_name'] : ''); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Tagline (Title)', 'emerge-mono' ); ?></label>
                <input type="text" name="site_tagline" value="<?php echo esc_attr(isset($opts['site_tagline']) ? $opts['site_tagline'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Web Creator', 'emerge-mono' ); ?>">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Copyright', 'emerge-mono' ); ?></label>
                <input type="text" name="copyright" value="<?php echo esc_attr(isset($opts['copyright']) ? $opts['copyright'] : ''); ?>" class="en-field-input">
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Logo', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'You can set separate logos for dark and light mode. If only one is set, it will be used in both modes.', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Logo (Dark Mode)', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="logo_url" id="en-logo-url" value="<?php echo esc_attr(isset($opts['logo_url']) ? $opts['logo_url'] : ''); ?>" class="en-field-input">
                    <button type="button" class="en-media-btn" onclick="enOpenMedia('en-logo-url')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button>
                </div>
                <?php if ( ! empty($opts['logo_url']) ) : ?>
                    <img src="<?php echo esc_url($opts['logo_url']); ?>" style="margin-top:10px;max-width:120px;max-height:120px;object-fit:contain;background:#111;padding:8px">
                <?php endif; ?>
            </div>
            <div class="en-field-group" style="margin-top:16px">
                <label class="en-field-label"><?php esc_html_e( 'Logo (Light Mode)', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="logo_url_light" id="en-logo-url-light" value="<?php echo esc_attr(isset($opts['logo_url_light']) ? $opts['logo_url_light'] : ''); ?>" class="en-field-input">
                    <button type="button" class="en-media-btn" onclick="enOpenMedia('en-logo-url-light')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button>
                </div>
                <?php if ( ! empty($opts['logo_url_light']) ) : ?>
                    <img src="<?php echo esc_url($opts['logo_url_light']); ?>" style="margin-top:10px;max-width:120px;max-height:120px;object-fit:contain;background:#eee;padding:8px">
                <?php endif; ?>
            </div>
            <div class="en-field-group" style="margin-top:16px">
                <label class="en-field-label"><?php esc_html_e( 'Top Page Logo Size (vw)', 'emerge-mono' ); ?></label>
                <div style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Desktop', 'emerge-mono' ); ?></div>
                        <input type="number" name="top_logo_size" min="2" max="60" step="0.1"
                            value="<?php echo esc_attr(isset($opts['top_logo_size']) ? $opts['top_logo_size'] : 14); ?>"
                            class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Tablet (≤768px)', 'emerge-mono' ); ?></div>
                        <input type="number" name="top_logo_size_tablet" min="0" max="80" step="0.1"
                            value="<?php echo esc_attr(isset($opts['top_logo_size_tablet']) ? $opts['top_logo_size_tablet'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Mobile (≤480px)', 'emerge-mono' ); ?></div>
                        <input type="number" name="top_logo_size_mobile" min="0" max="100" step="0.1"
                            value="<?php echo esc_attr(isset($opts['top_logo_size_mobile']) ? $opts['top_logo_size_mobile'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                </div>
                <div class="en-field-desc" style="margin-top:6px"><?php esc_html_e( 'Top page logo size in vw, per screen width. Leave Tablet/Mobile empty to inherit the larger breakpoint. Desktop default: 14.', 'emerge-mono' ); ?></div>
            </div>

            <div class="en-field-group" style="margin-top:16px">
                <label class="en-field-label"><?php esc_html_e( 'Header Logo Size (vw)', 'emerge-mono' ); ?></label>
                <div style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Desktop', 'emerge-mono' ); ?></div>
                        <input type="number" name="header_logo_size" min="1" max="20" step="0.1"
                            value="<?php echo esc_attr(isset($opts['header_logo_size']) ? $opts['header_logo_size'] : '3.2'); ?>"
                            class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Tablet (≤768px)', 'emerge-mono' ); ?></div>
                        <input type="number" name="header_logo_size_tablet" min="0" max="30" step="0.1"
                            value="<?php echo esc_attr(isset($opts['header_logo_size_tablet']) ? $opts['header_logo_size_tablet'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Mobile (≤480px)', 'emerge-mono' ); ?></div>
                        <input type="number" name="header_logo_size_mobile" min="0" max="40" step="0.1"
                            value="<?php echo esc_attr(isset($opts['header_logo_size_mobile']) ? $opts['header_logo_size_mobile'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                </div>
                <div class="en-field-desc" style="margin-top:6px"><?php esc_html_e( 'Header logo size in vw, per screen width. Leave Tablet/Mobile empty to inherit the larger breakpoint. Desktop default: 3.2.', 'emerge-mono' ); ?></div>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Top Page Buttons', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Add or remove buttons shown on the top page. Link to a page or enter a URL directly.', 'emerge-mono' ); ?></div>
            <?php
            $top_buttons = isset($opts['top_buttons']) ? $opts['top_buttons'] : array(
                array( 'label' => $btn1_label, 'url' => $btn1_url ),
                array( 'label' => $btn2_label, 'url' => $btn2_url ),
            );
            ?>
            <div id="en-top-btn-list">
                <?php foreach ( $top_buttons as $i => $tbtn ) :
                    $tbtn_url = isset($tbtn['url']) ? $tbtn['url'] : '';
                ?>
                <div class="en-top-btn-row">
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Button Label', 'emerge-mono' ); ?></label>
                        <input type="text" name="top_btn_label[]" value="<?php echo esc_attr(isset($tbtn['label']) ? $tbtn['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. Profile', 'emerge-mono' ); ?>">
                    </div>
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Link (Select Page)', 'emerge-mono' ); ?></label>
                        <select name="top_btn_url[]" class="en-field-input" style="margin-bottom:6px">
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono' ); ?></option>
                            <?php foreach ( $pages as $page ) :
                                $purl = get_permalink($page->ID); ?>
                                <option value="<?php echo esc_url($purl); ?>" <?php selected( $tbtn_url, $purl ); ?>><?php echo esc_html( $page->post_title ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="url" name="top_btn_url_manual[]" value="<?php echo esc_attr($tbtn_url); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Or enter URL directly: https://', 'emerge-mono' ); ?>">
                    </div>
                    <button type="button" class="en-remove-btn" onclick="this.closest('.en-top-btn-row').remove()"><?php esc_html_e( 'Remove this button', 'emerge-mono' ); ?></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddTopBtn()">+ Add Button</button>

            <template id="en-top-btn-template">
                <div class="en-top-btn-row">
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Button Label', 'emerge-mono' ); ?></label>
                        <input type="text" name="top_btn_label[]" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. Contact', 'emerge-mono' ); ?>">
                    </div>
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Link (Select Page)', 'emerge-mono' ); ?></label>
                        <select name="top_btn_url[]" class="en-field-input" style="margin-bottom:6px">
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono' ); ?></option>
                            <?php foreach ( $pages as $page ) : ?>
                                <option value="<?php echo esc_url(get_permalink($page->ID)); ?>"><?php echo esc_html($page->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="url" name="top_btn_url_manual[]" class="en-field-input" placeholder="<?php esc_attr_e( 'Or enter URL directly: https://', 'emerge-mono' ); ?>">
                    </div>
                    <button type="button" class="en-remove-btn" onclick="this.closest('.en-top-btn-row').remove()"><?php esc_html_e( 'Remove this button', 'emerge-mono' ); ?></button>
                </div>
            </template>
        </div>

        <script>
        function enAddTopBtn() {
            var list = document.getElementById('en-top-btn-list');
            var tpl  = document.getElementById('en-top-btn-template');
            if (tpl) list.appendChild(tpl.content.cloneNode(true));
        }
        </script>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>
    <?php
}

function en_admin_tab_profile($opts) {
    en_admin_notice();
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_profile','en_nonce'); ?>
        <input type="hidden" name="en_action" value="profile">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Profile Info', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Name', 'emerge-mono' ); ?></label>
                <input type="text" name="profile_name" value="<?php echo esc_attr(isset($opts['profile_name']) ? $opts['profile_name'] : ''); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Title', 'emerge-mono' ); ?></label>
                <input type="text" name="profile_role" value="<?php echo esc_attr(isset($opts['profile_role']) ? $opts['profile_role'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Web Creator / iOS Developer', 'emerge-mono' ); ?>">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Bio', 'emerge-mono' ); ?></label>
                <textarea name="profile_bio" class="en-field-textarea"><?php echo esc_textarea(isset($opts['profile_bio']) ? $opts['profile_bio'] : ''); ?></textarea>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Profile Image URL', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="profile_img" id="en-profile-img" value="<?php echo esc_attr(isset($opts['profile_img']) ? $opts['profile_img'] : ''); ?>" class="en-field-input">
                    <button type="button" class="en-media-btn" onclick="enOpenMedia('en-profile-img')"><?php esc_html_e( 'Select', 'emerge-mono' ); ?></button>
                </div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Skills', 'emerge-mono' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:8px"><?php esc_html_e( 'Enter comma-separated values. e.g. Web Design, PHP, Figma, Live2D', 'emerge-mono' ); ?></div>
                <input type="text" name="profile_skills" value="<?php echo esc_attr(isset($opts['profile_skills']) ? $opts['profile_skills'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Web Design, PHP, Figma, Live2D', 'emerge-mono' ); ?>">
            </div>
        </div>

        <?php $sns = isset($opts['sns_links']) ? $opts['sns_links'] : array(); ?>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Social Links', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:12px"><?php esc_html_e( 'These links appear on your profile page.', 'emerge-mono' ); ?></div>
            <div id="en-sns-list">
                <?php foreach ( $sns as $idx => $s ) :
                    $sns_icon = isset($s['icon']) ? $s['icon'] : '';
                ?>
                <div class="en-sns-row">
                    <input type="text" name="sns_label[]" value="<?php echo esc_attr(isset($s['label']) ? $s['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. X / Instagram', 'emerge-mono' ); ?>" style="margin-bottom:6px">
                    <div style="display:flex;gap:8px;margin-bottom:6px">
                        <input type="url" name="sns_url[]" value="<?php echo esc_attr(isset($s['url']) ? $s['url'] : ''); ?>" class="en-field-input" placeholder="https://">
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-sns-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" name="sns_icon[]" value="<?php echo esc_attr($sns_icon); ?>" class="en-field-input en-sns-icon-input" placeholder="<?php echo esc_attr__( 'Icon image URL (optional)', 'emerge-mono' ); ?>" readonly style="flex:1">
                        <button type="button" class="en-media-btn en-sns-icon-btn" style="white-space:nowrap"><?php esc_html_e( 'Select Icon', 'emerge-mono' ); ?></button>
                        <button type="button" class="en-remove-btn en-sns-icon-clear" style="white-space:nowrap"><?php esc_html_e( 'Clear', 'emerge-mono' ); ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddSns()"><?php esc_html_e( '+ Add Social Link', 'emerge-mono' ); ?></button>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>
    <script>
    var enSnsI18n = {
        placeholder: <?php echo wp_json_encode( esc_attr__( 'e.g. X / Instagram', 'emerge-mono' ) ); ?>,
        del: <?php echo wp_json_encode( __( 'Delete', 'emerge-mono' ) ); ?>,
        iconPlaceholder: <?php echo wp_json_encode( esc_attr__( 'Icon image URL (optional)', 'emerge-mono' ) ); ?>,
        selectIcon: <?php echo wp_json_encode( __( 'Select Icon', 'emerge-mono' ) ); ?>,
        clear: <?php echo wp_json_encode( __( 'Clear', 'emerge-mono' ) ); ?>,
        mediaTitle: <?php echo wp_json_encode( __( 'Select Icon Image', 'emerge-mono' ) ); ?>,
        mediaButton: <?php echo wp_json_encode( __( 'Use this image', 'emerge-mono' ) ); ?>
    };
    function enAddSns(){
        var list = document.getElementById('en-sns-list');
        var div = document.createElement('div');
        div.className = 'en-sns-row';
        div.innerHTML = '<input type="text" name="sns_label[]" class="en-field-input" placeholder="' + enSnsI18n.placeholder + '" style="margin-bottom:6px">'
            + '<div style="display:flex;gap:8px;margin-bottom:6px"><input type="url" name="sns_url[]" class="en-field-input" placeholder="https://"><button type="button" class="en-remove-btn" onclick="this.closest(\'.en-sns-row\').remove()">' + enSnsI18n.del + '</button></div>'
            + '<div style="display:flex;gap:8px;align-items:center"><input type="text" name="sns_icon[]" class="en-field-input en-sns-icon-input" placeholder="' + enSnsI18n.iconPlaceholder + '" readonly style="flex:1"><button type="button" class="en-media-btn en-sns-icon-btn" style="white-space:nowrap">' + enSnsI18n.selectIcon + '</button><button type="button" class="en-remove-btn en-sns-icon-clear" style="white-space:nowrap">' + enSnsI18n.clear + '</button></div>';
        list.appendChild(div);
    }

    // SNSアイコンの画像選択（イベント委譲で既存・新規両方に対応）
    document.addEventListener('click', function(e){
        if ( e.target && e.target.classList.contains('en-sns-icon-btn') ) {
            e.preventDefault();
            var row = e.target.closest('.en-sns-row');
            var input = row ? row.querySelector('.en-sns-icon-input') : null;
            if ( ! input ) return;
            var frame = wp.media({
                title: enSnsI18n.mediaTitle,
                button: { text: enSnsI18n.mediaButton },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                input.value = att.url;
            });
            frame.open();
        }
        if ( e.target && e.target.classList.contains('en-sns-icon-clear') ) {
            e.preventDefault();
            var row2 = e.target.closest('.en-sns-row');
            var input2 = row2 ? row2.querySelector('.en-sns-icon-input') : null;
            if ( input2 ) input2.value = '';
        }
    });
    </script>
    <?php
}

function en_admin_tab_sns_DEPRECATED($opts) {
    en_admin_notice();
    $sns = isset($opts['sns_links']) ? $opts['sns_links'] : array();
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_sns','en_nonce'); ?>
        <input type="hidden" name="en_action" value="sns">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Social Links', 'emerge-mono' ); ?></div>
            <div id="en-sns-list">
                <?php foreach ( $sns as $s ) : ?>
                <div class="en-sns-row">
                    <input type="text" name="sns_label[]" value="<?php echo esc_attr(isset($s['label']) ? $s['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. X / Instagram', 'emerge-mono' ); ?>" style="margin-bottom:6px">
                    <div style="display:flex;gap:8px">
                        <input type="url" name="sns_url[]" value="<?php echo esc_attr(isset($s['url']) ? $s['url'] : ''); ?>" class="en-field-input" placeholder="https://">
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-sns-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddSns()">+ <?php esc_html_e( 'Add Social Link', 'emerge-mono' ); ?></button>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>
    <?php
}

function en_admin_tab_nav($opts) {
    en_admin_notice();
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

    <script>
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
        var pages = <?php echo json_encode( array_map( function($p){ return array('id'=>$p->ID,'title'=>$p->post_title,'slug'=>$p->post_name); }, $pages ) ); ?>;
        var navI18n = {
            selectPage: <?php echo wp_json_encode( __( '— Select a page —', 'emerge-mono' ) ); ?>,
            page: <?php echo wp_json_encode( __( 'Page', 'emerge-mono' ) ); ?>,
            directUrl: <?php echo wp_json_encode( __( 'Direct URL', 'emerge-mono' ) ); ?>,
            labelPlaceholder: <?php echo wp_json_encode( esc_attr__( 'Label (e.g. About)', 'emerge-mono' ) ); ?>,
            del: <?php echo wp_json_encode( __( 'Delete', 'emerge-mono' ) ); ?>
        };
        var opts = '<option value="">' + navI18n.selectPage + '</option>';
        pages.forEach(function(p){ opts += '<option value="'+p.id+'">'+p.title+' (/'+p.slug+')</option>'; });

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
        var pages = <?php echo wp_json_encode( array_map( function($p){ return array('id'=>$p->ID,'title'=>$p->post_title,'slug'=>$p->post_name); }, $pages ) ); ?>;
        var fI18n = {
            selectPage: <?php echo wp_json_encode( __( '— Select a page —', 'emerge-mono' ) ); ?>,
            page: <?php echo wp_json_encode( __( 'Page', 'emerge-mono' ) ); ?>,
            directUrl: <?php echo wp_json_encode( __( 'Direct URL', 'emerge-mono' ) ); ?>,
            labelPlaceholder: <?php echo wp_json_encode( esc_attr__( 'Label (URL only)', 'emerge-mono' ) ); ?>,
            del: <?php echo wp_json_encode( __( 'Delete', 'emerge-mono' ) ); ?>
        };
        var opts = '<option value="">' + fI18n.selectPage + '</option>';
        pages.forEach(function(p){ opts += '<option value="'+p.id+'">'+p.title+' (/'+p.slug+')</option>'; });

        var div = document.createElement('div');
        div.className = 'en-footer-nav-row';
        div.style.cssText = 'display:flex;flex-direction:column;gap:8px;background:rgba(255,255,255,.04);padding:12px;border-radius:4px;margin-bottom:8px;';
        div.innerHTML =
            '<div style="display:flex;gap:8px;align-items:center;">'
            +'<select name="footer_nav_type[]" class="en-field-input" style="width:140px" onchange="enFooterNavTypeChange(this)">'
            +'<option value="page">' + fI18n.page + '</option><option value="url">' + fI18n.directUrl + '</option>'
            +'</select>'
            +'<input type="text" name="footer_nav_label[]" class="en-field-input" placeholder="' + fI18n.labelPlaceholder + '" style="width:140px">'
            +'<button type="button" class="en-remove-btn" onclick="this.closest(\'.en-footer-nav-row\').remove()">' + fI18n.del + '</button>'
            +'</div>'
            +'<div class="en-footer-nav-page-wrap"><select name="footer_nav_page_id[]" class="en-field-input">'+opts+'</select></div>'
            +'<div class="en-footer-nav-url-wrap" style="display:none"><input type="text" name="footer_nav_url[]" class="en-field-input" placeholder="https://"></div>';
        document.getElementById('en-footer-nav-list').appendChild(div);
    };
    </script>
    <?php
}

function en_admin_tab_contact($opts) {
    en_admin_notice();
    $fields = get_option('en_contact_fields', en_default_contact_fields());
    $field_types = array(
        'text'     => __( 'Text (single line)', 'emerge-mono' ),
        'email'    => __( 'Email', 'emerge-mono' ),
        'tel'      => __( 'Phone', 'emerge-mono' ),
        'textarea' => __( 'Text (multi-line)', 'emerge-mono' ),
        'select'   => __( 'Select box', 'emerge-mono' ),
        'checkbox' => __( 'Checkbox', 'emerge-mono' ),
    );

    // 送信ログ表示
    global $wpdb;
    $table = $wpdb->prefix . 'en_contact_log';
    $logs = array();
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table ) {
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 30");
        // 既読処理
        if ( isset($_GET['mark_read']) ) {
            $wpdb->update($table, array('status'=>'read'), array('id'=>(int)$_GET['mark_read']));
        }
        if ( isset($_GET['delete_log']) ) {
            $wpdb->delete($table, array('id'=>(int)$_GET['delete_log']));
        }
    }
    $unread = ( $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status='unread'") ? (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status='unread'") : 0 );
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_contact','en_nonce'); ?>
        <input type="hidden" name="en_action" value="contact">

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Basic Settings', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Recipient Email', 'emerge-mono' ); ?></label>
                <input type="email" name="contact_email" value="<?php echo esc_attr(isset($opts['contact_email']) ? $opts['contact_email'] : get_option('admin_email')); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Description', 'emerge-mono' ); ?></label>
                <textarea name="contact_desc" class="en-field-textarea"><?php echo esc_textarea(isset($opts['contact_desc']) ? $opts['contact_desc'] : ''); ?></textarea>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Submit Button Text', 'emerge-mono' ); ?></label>
                <input type="text" name="contact_btn_text" value="<?php echo esc_attr(isset($opts['contact_btn_text']) ? $opts['contact_btn_text'] : 'Send'); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Send', 'emerge-mono' ); ?>">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Success Message', 'emerge-mono' ); ?></label>
                <input type="text" name="contact_success" value="<?php echo esc_attr(isset($opts['contact_success']) ? $opts['contact_success'] : __( 'Your message has been sent.', 'emerge-mono' )); ?>" class="en-field-input">
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Form Fields', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Freely add or remove form fields. You must include at least one email field.', 'emerge-mono' ); ?></div>
            <div id="en-contact-fields-list">
                <?php foreach ( $fields as $i => $field ) :
                    $key  = $field['key'];
                    $type = $field['type'];
                    $opts_val = isset($field['options']) ? implode("
", $field['options']) : '';
                ?>
                <div class="en-contact-field-row">
                    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:start;margin-bottom:8px">
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px">Label</div>
                            <input type="text" name="cf_label[]" value="<?php echo esc_attr($field['label']); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Your Name', 'emerge-mono' ); ?>">
                        </div>
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field Type', 'emerge-mono' ); ?></div>
                            <select name="cf_type[]" class="en-field-input en-field-select" onchange="enToggleOptions(this)">
                                <?php foreach ( $field_types as $ft => $fl ) : ?>
                                    <option value="<?php echo $ft; ?>" <?php selected($type,$ft); ?>><?php echo $fl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="en-remove-btn" style="margin-top:20px" onclick="this.closest('.en-contact-field-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px">
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field ID (alphanumeric)', 'emerge-mono' ); ?></div>
                            <input type="text" name="cf_key[]" value="<?php echo esc_attr($key); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'name', 'emerge-mono' ); ?>">
                        </div>
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Placeholder', 'emerge-mono' ); ?></div>
                            <input type="text" name="cf_placeholder[]" value="<?php echo esc_attr(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Show example', 'emerge-mono' ); ?>">
                        </div>
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Required / Optional', 'emerge-mono' ); ?></div>
                            <select name="cf_required[]" class="en-field-input en-field-select">
                                <option value="1" <?php selected(!empty($field['required']),true); ?>><?php esc_html_e( 'Required', 'emerge-mono' ); ?></option>
                                <option value="0" <?php selected(!empty($field['required']),false); ?>><?php esc_html_e( 'Optional', 'emerge-mono' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="en-cf-options" style="<?php echo in_array($type,array('select','checkbox')) ? '' : 'display:none'; ?>">
                        <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Options (one per line)', 'emerge-mono' ); ?></div>
                        <textarea name="cf_options[]" class="en-field-textarea" style="height:80px" placeholder="<?php esc_attr_e( 'Option A&#10;Option B&#10;Option C', 'emerge-mono' ); ?>"><?php echo esc_textarea($opts_val); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddContactField()">+ Add Field</button>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Auto-Reply Email', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Send Auto-Reply', 'emerge-mono' ); ?></label>
                <select name="contact_auto_reply" class="en-field-input en-field-select">
                    <option value="1" <?php selected(isset($opts['contact_auto_reply']) ? $opts['contact_auto_reply'] : '1', '1'); ?>>Send</option>
                    <option value="0" <?php selected(isset($opts['contact_auto_reply']) ? $opts['contact_auto_reply'] : '1', '0'); ?>><?php esc_html_e( 'Do not send', 'emerge-mono' ); ?></option>
                </select>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Auto-Reply Subject', 'emerge-mono' ); ?></label>
                <input type="text" name="contact_reply_subject" value="<?php echo esc_attr(isset($opts['contact_reply_subject']) ? $opts['contact_reply_subject'] : __( 'We have received your inquiry', 'emerge-mono' )); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Auto-Reply Body', 'emerge-mono' ); ?></label>
                <textarea name="contact_reply_body" class="en-field-textarea" style="height:120px"><?php echo esc_textarea(isset($opts['contact_reply_body']) ? $opts['contact_reply_body'] : __( "Thank you for your inquiry.
We will review your message and get back to you shortly.", 'emerge-mono' )); ?></textarea>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Admin Email Settings', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Subject', 'emerge-mono' ); ?></label>
                <input type="text" name="contact_mail_subject" value="<?php echo esc_attr(isset($opts['contact_mail_subject']) ? $opts['contact_mail_subject'] : '[Inquiry] ' . get_bloginfo('name')); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Email Intro Text', 'emerge-mono' ); ?></label>
                <textarea name="contact_mail_body" class="en-field-textarea"><?php echo esc_textarea(isset($opts['contact_mail_body']) ? $opts['contact_mail_body'] : __( "You have received a new inquiry with the following details.\n\n", 'emerge-mono' )); ?></textarea>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Security Settings', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:12px">
                <?php esc_html_e( 'Honeypot spam protection is always enabled. Add reCAPTCHA v3 for extra protection.', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'reCAPTCHA v3 Site Key', 'emerge-mono' ); ?></label>
                <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr(isset($opts['recaptcha_site_key']) ? $opts['recaptcha_site_key'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Enter your Google reCAPTCHA site key', 'emerge-mono' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><a href="https://www.google.com/recaptcha/admin" target="_blank" style="color:rgba(255,255,255,.4)"><?php esc_html_e( 'Google reCAPTCHA Admin →', 'emerge-mono' ); ?></a>  <?php esc_html_e( 'to get your keys.', 'emerge-mono' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'reCAPTCHA v3 Secret Key', 'emerge-mono' ); ?></label>
                <input type="password" name="recaptcha_secret" value="<?php echo esc_attr(isset($opts['recaptcha_secret']) ? $opts['recaptcha_secret'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Enter your secret key', 'emerge-mono' ); ?>">
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Privacy Policy Consent', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Require a privacy policy consent checkbox before form submission.', 'emerge-mono' ); ?></div>
            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="contact_consent_enabled" value="1" <?php checked( isset($opts['contact_consent_enabled']) ? $opts['contact_consent_enabled'] : '0', '1' ); ?>>
                    <span><?php esc_html_e( 'Show consent checkbox', 'emerge-mono' ); ?></span>
                </label>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Checkbox Text', 'emerge-mono' ); ?></label>
                <input type="text" name="contact_consent_text" value="<?php echo esc_attr(isset($opts['contact_consent_text']) ? $opts['contact_consent_text'] : 'I agree to the Privacy Policy.'); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Link Page (optional)', 'emerge-mono' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:8px"><?php esc_html_e( 'Clicking the checkbox text navigates to this page.', 'emerge-mono' ); ?></div>
                <?php
                $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'menu_order' ) );
                $consent_page_id = isset($opts['contact_consent_page_id']) ? (int)$opts['contact_consent_page_id'] : 0;
                ?>
                <select name="contact_consent_page_id" class="en-field-input" style="max-width:400px">
                    <option value=""><?php esc_html_e( '— No page selected —', 'emerge-mono' ); ?></option>
                    <?php foreach ( $pages as $page ) : ?>
                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($consent_page_id, $page->ID); ?>>
                        <?php echo esc_html($page->post_title); ?> （/<?php echo esc_html($page->post_name); ?>）
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>

    <?php if ( ! empty($logs) ) : ?>
    <div class="en-admin-section" style="margin-top:24px">
        <div class="en-admin-section-title">
            Inbox Log
            <?php if ($unread > 0) : ?>
                <span style="background:rgba(255,100,100,.3);color:rgba(255,150,150,.9);font-size:10px;padding:2px 8px;border-radius:10px;margin-left:8px"><?php echo (int)$unread; ?> unread</span>
            <?php endif; ?>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.08)">
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal;white-space:nowrap">Date</th>
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal">Name</th>
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal">Email</th>
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal"><?php esc_html_e( 'Content', 'emerge-mono' ); ?></th>
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal"><?php esc_html_e( 'Actions', 'emerge-mono' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $logs as $log ) :
                        $fields_data = json_decode($log->fields, true) ?: array();
                        $is_unread = $log->status === 'unread';
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);<?php echo $is_unread ? 'background:rgba(255,255,255,.02)' : ''; ?>">
                        <td style="padding:10px;color:rgba(255,255,255,.4);white-space:nowrap"><?php echo esc_html($log->created_at); ?></td>
                        <td style="padding:10px;color:rgba(255,255,255,.7)"><?php echo $is_unread ? '<strong>' : ''; ?><?php echo esc_html($log->name); ?><?php echo $is_unread ? '</strong>' : ''; ?></td>
                        <td style="padding:10px"><a href="mailto:<?php echo esc_attr($log->email); ?>" style="color:rgba(255,255,255,.5)"><?php echo esc_html($log->email); ?></a></td>
                        <td style="padding:10px">
                            <details>
                                <summary style="cursor:pointer;color:rgba(255,255,255,.4);font-size:11px"><?php esc_html_e( 'View details', 'emerge-mono' ); ?></summary>
                                <div style="margin-top:8px;padding:10px;background:rgba(255,255,255,.03);border-radius:4px">
                                    <?php foreach ( $fields_data as $fd ) : ?>
                                        <div style="margin-bottom:6px"><span style="color:rgba(255,255,255,.3);font-size:10px"><?php echo esc_html($fd['label']); ?>：</span><br><?php echo nl2br(esc_html($fd['value'])); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </td>
                        <td style="padding:10px;white-space:nowrap">
                            <?php if ($is_unread) : ?>
                                <a href="?page=emerge-mono-portfolio&tab=contact&mark_read=<?php echo $log->id; ?>" style="font-size:10px;color:rgba(255,255,255,.35);margin-right:8px"><?php esc_html_e( 'Mark as read', 'emerge-mono' ); ?></a>
                            <?php endif; ?>
                            <a href="?page=emerge-mono-portfolio&tab=contact&delete_log=<?php echo $log->id; ?>" style="font-size:10px;color:rgba(255,100,100,.4)" onclick="return confirm('<?php echo esc_js( __( 'Delete this?', 'emerge-mono' ) ); ?>')"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- フィールドAdd用テンプレート -->
    <template id="en-cf-template">
        <div class="en-contact-field-row">
            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:start;margin-bottom:8px">
                <div>
                    <div class="en-field-label" style="margin-bottom:4px">Label</div>
                    <input type="text" name="cf_label[]" class="en-field-input" placeholder="<?php esc_attr_e( 'Your Name', 'emerge-mono' ); ?>">
                </div>
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field Type', 'emerge-mono' ); ?></div>
                    <select name="cf_type[]" class="en-field-input en-field-select" onchange="enToggleOptions(this)">
                        <option value="text"><?php esc_html_e( 'Text (single line)', 'emerge-mono' ); ?></option>
                        <option value="email"><?php esc_html_e( 'Email', 'emerge-mono' ); ?></option>
                        <option value="tel"><?php esc_html_e( 'Phone', 'emerge-mono' ); ?></option>
                        <option value="textarea"><?php esc_html_e( 'Text (multi-line)', 'emerge-mono' ); ?></option>
                        <option value="select"><?php esc_html_e( 'Select box', 'emerge-mono' ); ?></option>
                        <option value="checkbox"><?php esc_html_e( 'Checkbox', 'emerge-mono' ); ?></option>
                    </select>
                </div>
                <button type="button" class="en-remove-btn" style="margin-top:20px" onclick="this.closest('.en-contact-field-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px">
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field ID (alphanumeric)', 'emerge-mono' ); ?></div>
                    <input type="text" name="cf_key[]" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. inquiry_type', 'emerge-mono' ); ?>">
                </div>
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Placeholder', 'emerge-mono' ); ?></div>
                    <input type="text" name="cf_placeholder[]" class="en-field-input">
                </div>
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Required / Optional', 'emerge-mono' ); ?></div>
                    <select name="cf_required[]" class="en-field-input en-field-select">
                        <option value="1"><?php esc_html_e( 'Required', 'emerge-mono' ); ?></option>
                        <option value="0"><?php esc_html_e( 'Optional', 'emerge-mono' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="en-cf-options" style="display:none">
                <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Options (one per line)', 'emerge-mono' ); ?></div>
                <textarea name="cf_options[]" class="en-field-textarea" style="height:80px" placeholder="<?php esc_attr_e( 'Option A&#10;Option B', 'emerge-mono' ); ?>"></textarea>
            </div>
        </div>
    </template>

    <script>
    function enToggleOptions(select) {
        var row = select.closest('.en-contact-field-row');
        var opts = row.querySelector('.en-cf-options');
        if (!opts) return;
        opts.style.display = (select.value === 'select' || select.value === 'checkbox') ? '' : 'none';
    }
    function enAddContactField() {
        var list = document.getElementById('en-contact-fields-list');
        var tpl  = document.getElementById('en-cf-template');
        if (tpl) {
            var clone = tpl.content.cloneNode(true);
            list.appendChild(clone);
        }
    }
    </script>
    <?php
}

function en_admin_tab_cpt($opts) {
    en_admin_notice();

    $work_label    = isset($opts['work_label'])    ? $opts['work_label']    : 'Works';
    $work_singular = isset($opts['work_singular']) ? $opts['work_singular'] : 'Work';
    $cat_label     = isset($opts['cat_label'])     ? $opts['cat_label']     : 'Category';
    $work_icon     = isset($opts['work_icon'])     ? $opts['work_icon']     : 'dashicons-portfolio';

    // よく使うdashiconsのリスト
    $icons = array(
        'dashicons-portfolio'     => '💼 ' . __( 'Portfolio', 'emerge-mono' ),
        'dashicons-images-alt2'   => '🖼 ' . __( 'Images / Works', 'emerge-mono' ),
        'dashicons-art'           => '🎨 ' . __( 'Art', 'emerge-mono' ),
        'dashicons-camera'        => '📷 ' . __( 'Camera', 'emerge-mono' ),
        'dashicons-video-alt3'    => '🎬 ' . __( 'Video', 'emerge-mono' ),
        'dashicons-music'         => '🎵 ' . __( 'Music', 'emerge-mono' ),
        'dashicons-edit'          => '✏️ ' . __( 'Edit', 'emerge-mono' ),
        'dashicons-admin-page'    => '📄 ' . __( 'Page', 'emerge-mono' ),
        'dashicons-star-filled'   => '⭐ ' . __( 'Star', 'emerge-mono' ),
        'dashicons-heart'         => '❤️ ' . __( 'Heart', 'emerge-mono' ),
        'dashicons-products'      => '📦 ' . __( 'Product', 'emerge-mono' ),
        'dashicons-admin-tools'   => '🔧 ' . __( 'Tools', 'emerge-mono' ),
        'dashicons-lightbulb'     => '💡 ' . __( 'Idea', 'emerge-mono' ),
        'dashicons-awards'        => '🏆 ' . __( 'Award', 'emerge-mono' ),
        'dashicons-megaphone'     => '📣 ' . __( 'News', 'emerge-mono' ),
    );
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_cpt','en_nonce'); ?>
        <input type="hidden" name="en_action" value="cpt">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Works Display Settings', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="line-height:1.9;margin-bottom:16px">
                <?php esc_html_e( 'Customize the name and icon of the "Works" post type shown in the WordPress admin.', 'emerge-mono' ); ?><br>
                <?php esc_html_e( 'For example, you can rename "Works" to "Portfolio", "Projects", or "Gallery".', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Display Name (plural)', 'emerge-mono' ); ?></label>
                <input type="text" name="work_label" value="<?php echo esc_attr($work_label); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Works', 'emerge-mono' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><?php esc_html_e( 'Name shown in the admin menu (e.g. Portfolio, Projects, Gallery)', 'emerge-mono' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Display Name (singular)', 'emerge-mono' ); ?></label>
                <input type="text" name="work_singular" value="<?php echo esc_attr($work_singular); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Work', 'emerge-mono' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><?php esc_html_e( 'Name for an individual item (e.g. Work, Piece, Project)', 'emerge-mono' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Category Display Name', 'emerge-mono' ); ?></label>
                <input type="text" name="cat_label" value="<?php echo esc_attr($cat_label); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Category', 'emerge-mono' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><?php esc_html_e( 'Label for categorizing works (e.g. Genre, Type, Tag)', 'emerge-mono' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Admin Menu Icon', 'emerge-mono' ); ?></label>
                <select name="work_icon" class="en-field-input" style="margin-bottom:8px">
                    <?php foreach ( $icons as $icon_class => $icon_label ) : ?>
                        <option value="<?php echo esc_attr($icon_class); ?>" <?php selected( $work_icon, $icon_class ); ?>>
                            <?php echo esc_html($icon_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="en-field-desc"><?php esc_html_e( 'After selecting, click "Save" to change the icon in the left admin menu.', 'emerge-mono' ); ?></div>
            </div>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>
    <?php
}

function en_admin_notice() {
    if ( isset($_GET['saved']) && $_GET['saved'] === '1' ) {
        echo '<div class="en-admin-notice success">✓ ' . esc_html__( 'Saved.', 'emerge-mono' ) . '</div>';
    }
}

function en_admin_tab_design($opts) {
    en_admin_notice();
    $bg     = isset($opts['design_bg'])    ? $opts['design_bg']    : '#000000';
    $text   = isset($opts['design_text'])  ? $opts['design_text']  : '#ffffff';
    $accent = isset($opts['design_accent'])? $opts['design_accent']: '#ffffff';
    $font   = isset($opts['design_font'])  ? $opts['design_font']  : 'Space Mono';
    $mode   = isset($opts['design_mode'])  ? $opts['design_mode']  : 'dark';

    // フォントリスト（Google Fonts + システムフォント）
    $fonts = array(
        'Space Mono'    => 'Space Mono ' . __( '(default, monospace)', 'emerge-mono' ),
        'Inter'         => 'Inter ' . __( '(simple, modern)', 'emerge-mono' ),
        'DM Sans'       => 'DM Sans ' . __( '(clean, readable)', 'emerge-mono' ),
        'Outfit'        => 'Outfit ' . __( '(stylish)', 'emerge-mono' ),
        'Syne'          => 'Syne ' . __( '(bold, impactful)', 'emerge-mono' ),
        'Josefin Sans'  => 'Josefin Sans ' . __( '(thin, elegant)', 'emerge-mono' ),
        'Bebas Neue'    => 'Bebas Neue ' . __( '(uppercase, impactful)', 'emerge-mono' ),
        'Noto Sans JP'  => 'Noto Sans JP ' . __( '(Japanese support)', 'emerge-mono' ),
        'M PLUS 1p'     => 'M PLUS 1p ' . __( '(Japanese, thin)', 'emerge-mono' ),
        'Zen Kaku Gothic New' => 'Zen Kaku Gothic ' . __( '(Japanese, modern)', 'emerge-mono' ),
        'System Sans'   => 'System Sans-serif ' . __( '(no external load)', 'emerge-mono' ),
        'System Serif'  => 'System Serif ' . __( '(no external load)', 'emerge-mono' ),
        'System Mono'   => 'System Monospace ' . __( '(no external load)', 'emerge-mono' ),
    );

    // カスタムフォント（アップロード済み・複数）を選択肢に追加
    $custom_fonts_list = function_exists('en_get_custom_fonts') ? en_get_custom_fonts() : array();
    foreach ( $custom_fonts_list as $cf ) {
        if ( ! empty($cf['name']) && ! isset($fonts[ $cf['name'] ]) ) {
            $fonts[ $cf['name'] ] = $cf['name'] . ' (' . __( 'Custom', 'emerge-mono' ) . ')';
        }
    }
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_design','en_nonce'); ?>
        <input type="hidden" name="en_action" value="design">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Color Mode', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'Select the site color mode. With Match device setting, the background switches to dark or light automatically based on the visitor device.', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <label class="en-mode-label">
                        <input type="radio" name="design_mode" value="dark" <?php checked($mode,'dark'); ?>>
                        <div class="en-mode-preview en-mode-dark">
                            <div class="en-mode-icon">◐</div>
                            <div>
                                <div class="en-mode-title"><?php esc_html_e( 'Always Dark', 'emerge-mono' ); ?></div>
                                <div class="en-mode-desc"><?php esc_html_e( 'Always dark background, white text', 'emerge-mono' ); ?></div>
                            </div>
                        </div>
                    </label>
                    <label class="en-mode-label">
                        <input type="radio" name="design_mode" value="light" <?php checked($mode,'light'); ?>>
                        <div class="en-mode-preview en-mode-light">
                            <div class="en-mode-icon">○</div>
                            <div>
                                <div class="en-mode-title"><?php esc_html_e( 'Always Light', 'emerge-mono' ); ?></div>
                                <div class="en-mode-desc"><?php esc_html_e( 'Always light background, black text', 'emerge-mono' ); ?></div>
                            </div>
                        </div>
                    </label>
                    <label class="en-mode-label">
                        <input type="radio" name="design_mode" value="auto" <?php checked($mode,'auto'); ?>>
                        <div class="en-mode-preview en-mode-auto">
                            <div class="en-mode-icon">◑</div>
                            <div>
                                <div class="en-mode-title"><?php esc_html_e( 'Match device setting (auto)', 'emerge-mono' ); ?></div>
                                <div class="en-mode-desc"><?php esc_html_e( 'Automatically follows the visitor dark/light mode setting', 'emerge-mono' ); ?></div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Color Settings', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'Set the overall site colors. No change needed if you keep the default dark background with white text.', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Background Color', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="design_bg" value="<?php echo esc_attr($bg); ?>" style="width:48px;height:36px;border:1px solid rgba(255,255,255,.15);background:none;cursor:pointer;border-radius:4px">
                    <input type="text" name="design_bg_text" value="<?php echo esc_attr($bg); ?>" class="en-field-input" style="width:120px" placeholder="#000000" oninput="document.querySelector('[name=design_bg]').value=this.value">
                </div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Text Color', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="design_text" value="<?php echo esc_attr($text); ?>" style="width:48px;height:36px;border:1px solid rgba(255,255,255,.15);background:none;cursor:pointer;border-radius:4px">
                    <input type="text" name="design_text_text" value="<?php echo esc_attr($text); ?>" class="en-field-input" style="width:120px" placeholder="#ffffff" oninput="document.querySelector('[name=design_text]').value=this.value">
                </div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Accent Color (buttons, borders, etc.)', 'emerge-mono' ); ?></label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="design_accent" value="<?php echo esc_attr($accent); ?>" style="width:48px;height:36px;border:1px solid rgba(255,255,255,.15);background:none;cursor:pointer;border-radius:4px">
                    <input type="text" name="design_accent_text" value="<?php echo esc_attr($accent); ?>" class="en-field-input" style="width:120px" placeholder="#ffffff" oninput="document.querySelector('[name=design_accent]').value=this.value">
                </div>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Font Settings', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'Select the overall site font. Choose a Japanese-capable font if you have a lot of Japanese content.', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Font', 'emerge-mono' ); ?></label>
                <select name="design_font" class="en-field-input">
                    <?php foreach ( $fonts as $fkey => $flabel ) : ?>
                        <option value="<?php echo esc_attr($fkey); ?>" <?php selected($font, $fkey); ?>>
                            <?php echo esc_html($flabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="en-field-desc">
                <?php esc_html_e( '* Check your site after saving. Google Fonts are loaded automatically; system fonts require no external load.', 'emerge-mono' ); ?>
            </div>

            <div class="en-field-group" style="margin-top:24px;border-top:1px solid rgba(255,255,255,.08);padding-top:20px">
                <label class="en-field-label"><?php esc_html_e( 'Custom Font Upload', 'emerge-mono' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:10px">
                    <?php esc_html_e( 'Upload your own font file (.woff2 / .woff / .ttf / .otf). It is added immediately and appears in the font list above.', 'emerge-mono' ); ?>
                </div>
                <?php
                $custom_fonts = function_exists('en_get_custom_fonts') ? en_get_custom_fonts() : array();
                // 一覧プレビュー用に@font-faceを管理画面にも読み込む
                if ( $custom_fonts ) {
                    echo '<style>';
                    foreach ( $custom_fonts as $cf ) {
                        if ( empty($cf['name']) || empty($cf['url']) ) continue;
                        echo "@font-face{font-family:'" . esc_attr($cf['name']) . "';font-display:swap;src:url('" . esc_url($cf['url']) . "');}";
                    }
                    echo '</style>';
                }
                ?>
                <input type="text" id="en-custom-font-name" class="en-field-input" placeholder="<?php echo esc_attr__( 'Font name (e.g. Baskerville)', 'emerge-mono' ); ?>" style="margin-bottom:8px">
                <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
                    <input type="text" id="en-custom-font-url" class="en-field-input" placeholder="<?php echo esc_attr__( 'Font file URL', 'emerge-mono' ); ?>" style="flex:1" readonly>
                    <button type="button" class="en-media-btn" id="en-custom-font-select" style="white-space:nowrap"><?php esc_html_e( 'Select File', 'emerge-mono' ); ?></button>
                    <button type="button" class="en-save-btn" id="en-custom-font-add" style="white-space:nowrap;padding:8px 20px"><?php esc_html_e( 'Add Font', 'emerge-mono' ); ?></button>
                </div>
                <div id="en-custom-font-msg" style="font-size:12px;min-height:18px;margin-bottom:10px"></div>

                <div id="en-custom-font-list">
                    <?php if ( $custom_fonts ) : ?>
                        <?php foreach ( $custom_fonts as $cf ) : if ( empty($cf['name']) ) continue; ?>
                        <div class="en-custom-font-row" data-name="<?php echo esc_attr($cf['name']); ?>" style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(255,255,255,.04);padding:10px 14px;border-radius:6px;margin-bottom:6px">
                            <span style="font-family:'<?php echo esc_attr($cf['name']); ?>',sans-serif;font-size:18px"><?php echo esc_html($cf['name']); ?></span>
                            <button type="button" class="en-remove-btn en-custom-font-del" data-name="<?php echo esc_attr($cf['name']); ?>"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <script>
            (function(){
                var nonce   = <?php echo wp_json_encode( wp_create_nonce('en_custom_font_nonce') ); ?>;
                var ajaxUrl = <?php echo wp_json_encode( admin_url('admin-ajax.php') ); ?>;
                var i18n = {
                    needBoth: <?php echo wp_json_encode( __( 'Please enter a font name and select a file.', 'emerge-mono' ) ); ?>,
                    adding:   <?php echo wp_json_encode( __( 'Adding...', 'emerge-mono' ) ); ?>,
                    added:    <?php echo wp_json_encode( __( 'Font added.', 'emerge-mono' ) ); ?>,
                    confirmDel: <?php echo wp_json_encode( __( 'Delete this font?', 'emerge-mono' ) ); ?>,
                    custom:   <?php echo wp_json_encode( __( 'Custom', 'emerge-mono' ) ); ?>,
                    del:      <?php echo wp_json_encode( __( 'Delete', 'emerge-mono' ) ); ?>
                };
                var nameInput = document.getElementById('en-custom-font-name');
                var urlInput  = document.getElementById('en-custom-font-url');
                var msg       = document.getElementById('en-custom-font-msg');
                var listEl    = document.getElementById('en-custom-font-list');
                var fontSel   = document.querySelector('select[name="design_font"]');

                // ファイル選択
                document.getElementById('en-custom-font-select').addEventListener('click', function(e){
                    e.preventDefault();
                    var frame = wp.media({
                        title: <?php echo wp_json_encode( __( 'Select Font File', 'emerge-mono' ) ); ?>,
                        button: { text: <?php echo wp_json_encode( __( 'Use this font', 'emerge-mono' ) ); ?> },
                        multiple: false,
                        library: { type: ['font/woff','font/woff2','font/ttf','font/otf','application/octet-stream'] }
                    });
                    frame.on('select', function(){
                        var att = frame.state().get('selection').first().toJSON();
                        urlInput.value = att.url;
                        if ( ! nameInput.value && att.title ) nameInput.value = att.title;
                    });
                    frame.open();
                });

                function injectFontFace(name, url){
                    var s = document.createElement('style');
                    s.textContent = "@font-face{font-family:'" + name + "';font-display:swap;src:url('" + url + "');}";
                    document.head.appendChild(s);
                }
                function addToDropdown(name){
                    if ( ! fontSel ) return;
                    for ( var i=0; i<fontSel.options.length; i++ ){
                        if ( fontSel.options[i].value === name ) return;
                    }
                    var opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name + ' (' + i18n.custom + ')';
                    fontSel.appendChild(opt);
                }
                function addRow(name){
                    var row = document.createElement('div');
                    row.className = 'en-custom-font-row';
                    row.setAttribute('data-name', name);
                    row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(255,255,255,.04);padding:10px 14px;border-radius:6px;margin-bottom:6px';
                    row.innerHTML = '<span style="font-family:\'' + name + '\',sans-serif;font-size:18px"></span>'
                                  + '<button type="button" class="en-remove-btn en-custom-font-del" data-name="' + name + '"></button>';
                    row.querySelector('span').textContent = name;
                    row.querySelector('button').textContent = i18n.del;
                    listEl.appendChild(row);
                    bindDelete(row.querySelector('.en-custom-font-del'));
                }

                // 追加
                document.getElementById('en-custom-font-add').addEventListener('click', function(e){
                    e.preventDefault();
                    var name = nameInput.value.trim();
                    var url  = urlInput.value.trim();
                    if ( ! name || ! url ) { msg.style.color='rgba(255,120,120,.9)'; msg.textContent = i18n.needBoth; return; }
                    var btn = this; btn.disabled = true;
                    msg.style.color='rgba(255,255,255,.6)'; msg.textContent = i18n.adding;
                    var data = new FormData();
                    data.append('action','en_add_custom_font');
                    data.append('nonce', nonce);
                    data.append('name', name);
                    data.append('url', url);
                    fetch(ajaxUrl, {method:'POST', body:data}).then(function(r){return r.json();}).then(function(res){
                        btn.disabled = false;
                        if ( res.success ) {
                            injectFontFace(name, url);
                            addToDropdown(name);
                            // 既存行になければ追加
                            if ( ! listEl.querySelector('.en-custom-font-row[data-name="'+CSS.escape(name)+'"]') ) {
                                addRow(name);
                            }
                            msg.style.color='rgba(120,220,120,.9)'; msg.textContent = i18n.added;
                            nameInput.value=''; urlInput.value='';
                        } else {
                            msg.style.color='rgba(255,120,120,.9)'; msg.textContent = res.data || 'Error';
                        }
                    });
                });

                // 削除
                function bindDelete(btn){
                    btn.addEventListener('click', function(e){
                        e.preventDefault();
                        if ( ! confirm(i18n.confirmDel) ) return;
                        var name = this.getAttribute('data-name');
                        var b = this; b.disabled = true;
                        var data = new FormData();
                        data.append('action','en_delete_custom_font');
                        data.append('nonce', nonce);
                        data.append('name', name);
                        fetch(ajaxUrl, {method:'POST', body:data}).then(function(r){return r.json();}).then(function(res){
                            if ( res.success ) {
                                var row = listEl.querySelector('.en-custom-font-row[data-name="'+CSS.escape(name)+'"]');
                                if ( row ) row.remove();
                                // ドロップダウンからも削除
                                if ( fontSel ) {
                                    for ( var i=0; i<fontSel.options.length; i++ ){
                                        if ( fontSel.options[i].value === name ) { fontSel.remove(i); break; }
                                    }
                                }
                            } else {
                                b.disabled = false;
                                alert(res.data || 'Error');
                            }
                        });
                    });
                }
                document.querySelectorAll('.en-custom-font-del').forEach(bindDelete);
            })();
            </script>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Preview', 'emerge-mono' ); ?></div>
            <div id="en-design-preview" style="background:<?php echo esc_attr($bg); ?>;color:<?php echo esc_attr($text); ?>;padding:24px;border-radius:8px;font-family:<?php echo esc_attr( en_font_stack($font) ); ?>;border:1px solid rgba(255,255,255,.08)">
                <div style="font-size:20px;font-weight:700;letter-spacing:.1em;margin-bottom:8px">DAISUKE DESIGN</div>
                <div style="font-size:11px;letter-spacing:.4em;opacity:.5;margin-bottom:16px">WEB CREATOR</div>
                <div style="display:inline-block;border:1px solid <?php echo esc_attr($accent); ?>;color:<?php echo esc_attr($accent); ?>;font-size:10px;letter-spacing:.3em;padding:8px 20px;opacity:.7">WORKS</div>
            </div>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>
    <?php
}

function en_admin_tab_shortcodes() {
    $shortcodes = array(
        array(
            'code'  => '[emerge_mono_top]',
            'title' => __( 'Home', 'emerge-mono' ),
            'desc'  => __( 'Top page shortcode showing the logo, site name, and buttons. Paste it into your front page.', 'emerge-mono' ),
        ),
        array(
            'code'  => '[emerge_mono_about]',
            'title' => __( 'Profile', 'emerge-mono' ),
            'desc'  => __( 'Displays the profile image, name, title, bio, and social links. Reflects the Profile settings.', 'emerge-mono' ),
        ),
        array(
            'code'  => '[emerge_mono_works]',
            'title' => __( 'Works', 'emerge-mono' ),
            'desc'  => __( 'Displays works in a grid layout with category filtering. Added works appear automatically.', 'emerge-mono' ),
        ),
        array(
            'code'  => '[emerge_mono_contact]',
            'title' => __( 'Contact', 'emerge-mono' ),
            'desc'  => __( 'Displays the contact form. Reflects the settings in the "Contact Form" tab.', 'emerge-mono' ),
        ),
        array(
            'code'  => '[emerge_mono_news]',
            'title' => __( 'News', 'emerge-mono' ),
            'desc'  => __( 'Displays standard WordPress posts. Useful for a blog or news page. You can set the count with per_page="10", e.g. [emerge_mono_news per_page="10"]', 'emerge-mono' ),
        ),
        array(
            'code'  => '[emerge_mono_privacy]',
            'title' => __( 'Privacy Policy Page', 'emerge-mono' ),
            'desc'  => __( 'Displays the privacy policy. Text is auto-generated from the operator name and email in the "Privacy Policy" settings.', 'emerge-mono' ),
        ),
        array(
            'code'  => '[emerge_mono_terms]',
            'title' => __( 'Terms of Service Page', 'emerge-mono' ),
            'desc'  => __( 'Displays the terms of service. Text is auto-generated from the operator name and email in the "Terms of Service" settings.', 'emerge-mono' ),
        ),

    );
    ?>
    <div class="en-admin-section">
        <div class="en-admin-section-title"><?php esc_html_e( 'Shortcodes', 'emerge-mono' ); ?></div>
        <div class="en-field-desc" style="margin-bottom:20px;line-height:1.9">
            <?php esc_html_e( 'Paste a shortcode into the WordPress page editor to display its content on that page.', 'emerge-mono' ); ?><br>
            <?php esc_html_e( 'Use the Copy button on the right to copy it to your clipboard.', 'emerge-mono' ); ?>
        </div>
        <?php foreach ( $shortcodes as $sc ) : ?>
        <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:20px;margin-bottom:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:10px">
                <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.8)"><?php echo esc_html($sc['title']); ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <code style="flex:1;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:10px 14px;font-size:13px;color:rgba(255,255,255,.85);font-family:'Courier New',monospace;word-break:break-all"><?php echo esc_html($sc['code']); ?></code>
                <button type="button"
                    class="en-copy-btn"
                    data-code="<?php echo esc_attr($sc['code']); ?>"
                    onclick="enCopyShortcode(this)"
                    style="flex-shrink:0;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:6px;color:rgba(255,255,255,.5);font-size:11px;padding:8px 16px;cursor:pointer;transition:all .2s;white-space:nowrap">
                    📋 Copy
                </button>
            </div>
            <div style="font-size:12px;color:rgba(255,255,255,.3);line-height:1.7"><?php echo esc_html($sc['desc']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="en-admin-section">
        <div class="en-admin-section-title"><?php esc_html_e( 'How to Use', 'emerge-mono' ); ?></div>
        <div style="font-size:13px;color:rgba(255,255,255,.4);line-height:2">
            <div style="margin-bottom:12px">
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 1</span>
                <?php esc_html_e( 'WordPress Admin → Pages → Add New', 'emerge-mono' ); ?>
            </div>
            <div style="margin-bottom:12px">
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 2</span>
                <?php esc_html_e( 'Enter a page title (e.g. About)', 'emerge-mono' ); ?>
            </div>
            <div style="margin-bottom:12px">
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 3</span>
                <?php esc_html_e( 'Paste the shortcode in the content area (e.g. [emerge_mono_about])', 'emerge-mono' ); ?>
            </div>
            <div>
                <span style="background:rgba(255,255,255,.08);border-radius:4px;padding:2px 8px;font-size:11px;margin-right:8px;color:rgba(255,255,255,.6)">STEP 4</span>
                <?php esc_html_e( 'Click Publish to finish', 'emerge-mono' ); ?>
            </div>
        </div>
    </div>

    <script>
    function enCopyShortcode(btn) {
        var code = btn.dataset.code;
        var copiedLabel = <?php echo wp_json_encode( '✓ ' . __( 'Copied', 'emerge-mono' ) ); ?>;
        navigator.clipboard.writeText(code).then(function() {
            var orig = btn.innerHTML;
            btn.innerHTML = copiedLabel;
            btn.style.background = 'rgba(100,200,100,.15)';
            btn.style.borderColor = 'rgba(100,200,100,.3)';
            btn.style.color = 'rgba(150,230,150,.8)';
            setTimeout(function() {
                btn.innerHTML = orig;
                btn.style.background = '';
                btn.style.borderColor = '';
                btn.style.color = '';
            }, 2000);
        });
    }
    </script>
    <?php
}

function en_admin_tab_editor($opts) {
    en_admin_notice();
    $enabled = isset($opts['editor_enabled']) ? $opts['editor_enabled'] : '1';
    $show_posts = isset($opts['show_default_posts']) ? $opts['show_default_posts'] : '0';
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_editor', 'en_nonce'); ?>
        <input type="hidden" name="en_action" value="editor">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Editor Feature', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'When enabled, a simple non-Gutenberg editor is used for Works and News posts.', 'emerge-mono' ); ?><br>
                <?php esc_html_e( 'Turn it off if you prefer Gutenberg. Reload the page after changing this setting.', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="editor_enabled" value="1" <?php checked($enabled, '1'); ?>>
                    <span><?php esc_html_e( 'Enable the editor (default: on)', 'emerge-mono' ); ?></span>
                </label>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Standard Posts', 'emerge-mono' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'News is managed as a dedicated post type, so the standard WordPress Posts menu is hidden by default.', 'emerge-mono' ); ?><br>
                <?php esc_html_e( 'Enable this only if you also want to use the standard blog Posts feature.', 'emerge-mono' ); ?>
            </div>
            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="show_default_posts" value="1" <?php checked($show_posts, '1'); ?>>
                    <span><?php esc_html_e( 'Show the standard Posts menu (default: off)', 'emerge-mono' ); ?></span>
                </label>
            </div>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
    </form>
    <?php
}
