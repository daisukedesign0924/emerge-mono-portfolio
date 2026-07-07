<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_editor($opts) {
    emono_admin_notice();
    $enabled = isset($opts['editor_enabled']) ? $opts['editor_enabled'] : '1';
    $show_posts = isset($opts['show_default_posts']) ? $opts['show_default_posts'] : '0';
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_editor', 'en_nonce'); ?>
        <input type="hidden" name="en_action" value="editor">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Editor Feature', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'When enabled, a simple non-Gutenberg editor is used for Works and News posts.', 'emerge-mono-portfolio' ); ?><br>
                <?php esc_html_e( 'Turn it off if you prefer Gutenberg. Reload the page after changing this setting.', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="editor_enabled" value="1" <?php checked($enabled, '1'); ?>>
                    <span><?php esc_html_e( 'Enable the editor (default: on)', 'emerge-mono-portfolio' ); ?></span>
                </label>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Standard Posts', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'News is managed as a dedicated post type, so the standard WordPress Posts menu is hidden by default.', 'emerge-mono-portfolio' ); ?><br>
                <?php esc_html_e( 'Enable this only if you also want to use the standard blog Posts feature.', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="show_default_posts" value="1" <?php checked($show_posts, '1'); ?>>
                    <span><?php esc_html_e( 'Show the standard Posts menu (default: off)', 'emerge-mono-portfolio' ); ?></span>
                </label>
            </div>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
    </form>
    <?php
}
