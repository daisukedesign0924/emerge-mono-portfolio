<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_general($opts) {
    emono_admin_notice();
    $pages = get_pages( array( 'sort_column' => 'menu_order' ) );
    $top_layout = isset( $opts['top_layout'] ) ? sanitize_key( $opts['top_layout'] ) : 'mono';
    $top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array(
        'mono' => array(
            'label'       => __( 'Minimal Top', 'emerge-mono-portfolio' ),
            'description' => __( 'Minimal portfolio top page with logo, site name, tagline, and buttons.', 'emerge-mono-portfolio' ),
        ),
    );
    $btn1_label = isset($opts['top_btn1_label']) ? $opts['top_btn1_label'] : 'Profile';
    $btn1_url   = isset($opts['top_btn1_url'])   ? $opts['top_btn1_url']   : '';
    $btn2_label = isset($opts['top_btn2_label']) ? $opts['top_btn2_label'] : 'Works';
    $btn2_url   = isset($opts['top_btn2_url'])   ? $opts['top_btn2_url']   : '';
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('en_save_general','en_nonce'); ?>
        <input type="hidden" name="en_action" value="general">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Top Page Layout', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Keep [emerge_mono_top] on your home page. Detailed top page editing has moved to the TOP Editor.', 'emerge-mono-portfolio' ); ?></div>
            <div style="display:flex;flex-direction:column;gap:10px">
                <?php foreach ( $top_layouts as $layout_key => $layout ) : ?>
                    <?php
                    $layout_key = sanitize_key( $layout_key );
                    $label = isset( $layout['label'] ) ? $layout['label'] : $layout_key;
                    $description = isset( $layout['description'] ) ? $layout['description'] : '';
                    ?>
                    <label style="display:flex;gap:10px;align-items:flex-start;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;cursor:pointer">
                        <input type="radio" name="top_layout" value="<?php echo esc_attr( $layout_key ); ?>" <?php checked( $top_layout, $layout_key ); ?> style="margin-top:2px">
                        <span>
                            <span style="display:block;font-size:13px;font-weight:700;color:rgba(255,255,255,.85)"><?php echo esc_html( $label ); ?></span>
                            <?php if ( $description ) : ?>
                                <span style="display:block;font-size:12px;line-height:1.7;color:rgba(255,255,255,.36);margin-top:3px"><?php echo esc_html( $description ); ?></span>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ene-top' ) ); ?>" class="en-admin-theme-btn" style="margin-top:16px">
                <span class="dashicons dashicons-layout" style="font-size:14px;width:14px;height:14px"></span>
                <span class="en-admin-theme-btn-label"><?php esc_html_e( 'Open TOP Editor', 'emerge-mono-portfolio' ); ?></span>
            </a>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Site Info', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Site Name', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="site_name" value="<?php echo esc_attr(isset($opts['site_name']) ? $opts['site_name'] : ''); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Tagline (Title)', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="site_tagline" value="<?php echo esc_attr(isset($opts['site_tagline']) ? $opts['site_tagline'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Web Creator', 'emerge-mono-portfolio' ); ?>">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Copyright', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="copyright" value="<?php echo esc_attr(isset($opts['copyright']) ? $opts['copyright'] : ''); ?>" class="en-field-input">
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Logo', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'You can set separate logos for dark and light mode. If only one is set, it will be used in both modes.', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Logo (Dark Mode)', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="logo_url" id="en-logo-url" value="<?php echo esc_attr(isset($opts['logo_url']) ? $opts['logo_url'] : ''); ?>" class="en-field-input">
                    <button type="button" class="en-media-btn" onclick="enOpenMedia('en-logo-url')"><?php esc_html_e( 'Select', 'emerge-mono-portfolio' ); ?></button>
                </div>
                <?php if ( ! empty($opts['logo_url']) ) : ?>
                    <img src="<?php echo esc_url($opts['logo_url']); ?>" style="margin-top:10px;max-width:120px;max-height:120px;object-fit:contain;background:#111;padding:8px">
                <?php endif; ?>
            </div>
            <div class="en-field-group" style="margin-top:16px">
                <label class="en-field-label"><?php esc_html_e( 'Logo (Light Mode)', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="logo_url_light" id="en-logo-url-light" value="<?php echo esc_attr(isset($opts['logo_url_light']) ? $opts['logo_url_light'] : ''); ?>" class="en-field-input">
                    <button type="button" class="en-media-btn" onclick="enOpenMedia('en-logo-url-light')"><?php esc_html_e( 'Select', 'emerge-mono-portfolio' ); ?></button>
                </div>
                <?php if ( ! empty($opts['logo_url_light']) ) : ?>
                    <img src="<?php echo esc_url($opts['logo_url_light']); ?>" style="margin-top:10px;max-width:120px;max-height:120px;object-fit:contain;background:#eee;padding:8px">
                <?php endif; ?>
            </div>
            <div class="en-field-group" style="margin-top:16px">
                <label class="en-field-label"><?php esc_html_e( 'Top Page Logo Size (vw)', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Desktop', 'emerge-mono-portfolio' ); ?></div>
                        <input type="number" name="top_logo_size" min="2" max="60" step="0.1"
                            value="<?php echo esc_attr(isset($opts['top_logo_size']) ? $opts['top_logo_size'] : 14); ?>"
                            class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Tablet (≤768px)', 'emerge-mono-portfolio' ); ?></div>
                        <input type="number" name="top_logo_size_tablet" min="0" max="80" step="0.1"
                            value="<?php echo esc_attr(isset($opts['top_logo_size_tablet']) ? $opts['top_logo_size_tablet'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Mobile (≤480px)', 'emerge-mono-portfolio' ); ?></div>
                        <input type="number" name="top_logo_size_mobile" min="0" max="100" step="0.1"
                            value="<?php echo esc_attr(isset($opts['top_logo_size_mobile']) ? $opts['top_logo_size_mobile'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                </div>
                <div class="en-field-desc" style="margin-top:6px"><?php esc_html_e( 'Top page logo size in vw, per screen width. Leave Tablet/Mobile empty to inherit the larger breakpoint. Desktop default: 14.', 'emerge-mono-portfolio' ); ?></div>
            </div>

            <div class="en-field-group" style="margin-top:16px">
                <label class="en-field-label"><?php esc_html_e( 'Header Logo Size (vw)', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Desktop', 'emerge-mono-portfolio' ); ?></div>
                        <input type="number" name="header_logo_size" min="1" max="20" step="0.1"
                            value="<?php echo esc_attr(isset($opts['header_logo_size']) ? $opts['header_logo_size'] : '3.2'); ?>"
                            class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Tablet (≤768px)', 'emerge-mono-portfolio' ); ?></div>
                        <input type="number" name="header_logo_size_tablet" min="0" max="30" step="0.1"
                            value="<?php echo esc_attr(isset($opts['header_logo_size_tablet']) ? $opts['header_logo_size_tablet'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                    <div>
                        <div style="font-size:11px;opacity:.6;margin-bottom:4px"><?php esc_html_e( 'Mobile (≤480px)', 'emerge-mono-portfolio' ); ?></div>
                        <input type="number" name="header_logo_size_mobile" min="0" max="40" step="0.1"
                            value="<?php echo esc_attr(isset($opts['header_logo_size_mobile']) ? $opts['header_logo_size_mobile'] : ''); ?>"
                            placeholder="auto" class="en-field-input" style="width:80px">
                    </div>
                </div>
                <div class="en-field-desc" style="margin-top:6px"><?php esc_html_e( 'Header logo size in vw, per screen width. Leave Tablet/Mobile empty to inherit the larger breakpoint. Desktop default: 3.2.', 'emerge-mono-portfolio' ); ?></div>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Top Page Buttons', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Add or remove buttons shown on the top page. Link to a page or enter a URL directly.', 'emerge-mono-portfolio' ); ?></div>
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
                        <label class="en-field-label"><?php esc_html_e( 'Button Label', 'emerge-mono-portfolio' ); ?></label>
                        <input type="text" name="top_btn_label[]" value="<?php echo esc_attr(isset($tbtn['label']) ? $tbtn['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. Profile', 'emerge-mono-portfolio' ); ?>">
                    </div>
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Link (Select Page)', 'emerge-mono-portfolio' ); ?></label>
                        <select name="top_btn_url[]" class="en-field-input" style="margin-bottom:6px">
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono-portfolio' ); ?></option>
                            <?php foreach ( $pages as $page ) :
                                $purl = get_permalink($page->ID); ?>
                                <option value="<?php echo esc_url($purl); ?>" <?php selected( $tbtn_url, $purl ); ?>><?php echo esc_html( $page->post_title ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="url" name="top_btn_url_manual[]" value="<?php echo esc_attr($tbtn_url); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Or enter URL directly: https://', 'emerge-mono-portfolio' ); ?>">
                    </div>
                    <button type="button" class="en-remove-btn" onclick="this.closest('.en-top-btn-row').remove()"><?php esc_html_e( 'Remove this button', 'emerge-mono-portfolio' ); ?></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddTopBtn()">+ Add Button</button>

            <template id="en-top-btn-template">
                <div class="en-top-btn-row">
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Button Label', 'emerge-mono-portfolio' ); ?></label>
                        <input type="text" name="top_btn_label[]" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. Contact', 'emerge-mono-portfolio' ); ?>">
                    </div>
                    <div class="en-field-group" style="margin-bottom:10px">
                        <label class="en-field-label"><?php esc_html_e( 'Link (Select Page)', 'emerge-mono-portfolio' ); ?></label>
                        <select name="top_btn_url[]" class="en-field-input" style="margin-bottom:6px">
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono-portfolio' ); ?></option>
                            <?php foreach ( $pages as $page ) : ?>
                                <option value="<?php echo esc_url(get_permalink($page->ID)); ?>"><?php echo esc_html($page->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="url" name="top_btn_url_manual[]" class="en-field-input" placeholder="<?php esc_attr_e( 'Or enter URL directly: https://', 'emerge-mono-portfolio' ); ?>">
                    </div>
                    <button type="button" class="en-remove-btn" onclick="this.closest('.en-top-btn-row').remove()"><?php esc_html_e( 'Remove this button', 'emerge-mono-portfolio' ); ?></button>
                </div>
            </template>
        </div>

        <?php
        wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_TOP_BUTTON_JS'
window.enAddTopBtn = function() {
    var list = document.getElementById('en-top-btn-list');
    var tpl  = document.getElementById('en-top-btn-template');
    if (tpl) list.appendChild(tpl.content.cloneNode(true));
};

EMONO_TOP_BUTTON_JS
        );
        ?>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
    </form>
    <?php
}
