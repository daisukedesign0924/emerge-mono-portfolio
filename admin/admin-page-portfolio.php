<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_profile($opts) {
    emono_admin_notice();
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_profile','en_nonce'); ?>
        <input type="hidden" name="en_action" value="profile">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Profile Info', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Name', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="profile_name" value="<?php echo esc_attr(isset($opts['profile_name']) ? $opts['profile_name'] : ''); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Title', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="profile_role" value="<?php echo esc_attr(isset($opts['profile_role']) ? $opts['profile_role'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Web Creator / iOS Developer', 'emerge-mono-portfolio' ); ?>">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Bio', 'emerge-mono-portfolio' ); ?></label>
                <textarea name="profile_bio" class="en-field-textarea"><?php echo esc_textarea(isset($opts['profile_bio']) ? $opts['profile_bio'] : ''); ?></textarea>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Profile Image URL', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="profile_img" id="en-profile-img" value="<?php echo esc_attr(isset($opts['profile_img']) ? $opts['profile_img'] : ''); ?>" class="en-field-input">
                    <button type="button" class="en-media-btn" onclick="enOpenMedia('en-profile-img')"><?php esc_html_e( 'Select', 'emerge-mono-portfolio' ); ?></button>
                </div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Skills', 'emerge-mono-portfolio' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:8px"><?php esc_html_e( 'Enter comma-separated values. e.g. Web Design, PHP, Figma, Live2D', 'emerge-mono-portfolio' ); ?></div>
                <input type="text" name="profile_skills" value="<?php echo esc_attr(isset($opts['profile_skills']) ? $opts['profile_skills'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Web Design, PHP, Figma, Live2D', 'emerge-mono-portfolio' ); ?>">
            </div>
        </div>

        <?php $sns = isset($opts['sns_links']) ? $opts['sns_links'] : array(); ?>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Social Links', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:12px"><?php esc_html_e( 'These links appear on your profile page.', 'emerge-mono-portfolio' ); ?></div>
            <div id="en-sns-list">
                <?php foreach ( $sns as $idx => $s ) :
                    $sns_icon = isset($s['icon']) ? $s['icon'] : '';
                ?>
                <div class="en-sns-row">
                    <input type="text" name="sns_label[]" value="<?php echo esc_attr(isset($s['label']) ? $s['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. X / Instagram', 'emerge-mono-portfolio' ); ?>" style="margin-bottom:6px">
                    <div style="display:flex;gap:8px;margin-bottom:6px">
                        <input type="url" name="sns_url[]" value="<?php echo esc_attr(isset($s['url']) ? $s['url'] : ''); ?>" class="en-field-input" placeholder="https://">
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-sns-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono-portfolio' ); ?></button>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" name="sns_icon[]" value="<?php echo esc_attr($sns_icon); ?>" class="en-field-input en-sns-icon-input" placeholder="<?php echo esc_attr__( 'Icon image URL (optional)', 'emerge-mono-portfolio' ); ?>" readonly style="flex:1">
                        <button type="button" class="en-media-btn en-sns-icon-btn" style="white-space:nowrap"><?php esc_html_e( 'Select Icon', 'emerge-mono-portfolio' ); ?></button>
                        <button type="button" class="en-remove-btn en-sns-icon-clear" style="white-space:nowrap"><?php esc_html_e( 'Clear', 'emerge-mono-portfolio' ); ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddSns()"><?php esc_html_e( '+ Add Social Link', 'emerge-mono-portfolio' ); ?></button>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
    </form>
    <?php
    wp_localize_script( 'emerge-mono-admin', 'emonoSnsSettings', array(
        'i18n' => array(
            'placeholder'     => esc_attr__( 'e.g. X / Instagram', 'emerge-mono-portfolio' ),
            'del'             => __( 'Delete', 'emerge-mono-portfolio' ),
            'iconPlaceholder' => esc_attr__( 'Icon image URL (optional)', 'emerge-mono-portfolio' ),
            'selectIcon'      => __( 'Select Icon', 'emerge-mono-portfolio' ),
            'clear'           => __( 'Clear', 'emerge-mono-portfolio' ),
            'mediaTitle'      => __( 'Select Icon Image', 'emerge-mono-portfolio' ),
            'mediaButton'     => __( 'Use this image', 'emerge-mono-portfolio' ),
        ),
    ) );
    wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_SNS_JS'
(function(){
    var enSnsI18n = (window.emonoSnsSettings && window.emonoSnsSettings.i18n) || {};

    window.enAddSns = function(){
        var list = document.getElementById('en-sns-list');
        var div = document.createElement('div');
        div.className = 'en-sns-row';
        div.innerHTML = '<input type="text" name="sns_label[]" class="en-field-input" placeholder="' + enSnsI18n.placeholder + '" style="margin-bottom:6px">'
            + '<div style="display:flex;gap:8px;margin-bottom:6px"><input type="url" name="sns_url[]" class="en-field-input" placeholder="https://"><button type="button" class="en-remove-btn" onclick="this.closest(\'.en-sns-row\').remove()">' + enSnsI18n.del + '</button></div>'
            + '<div style="display:flex;gap:8px;align-items:center"><input type="text" name="sns_icon[]" class="en-field-input en-sns-icon-input" placeholder="' + enSnsI18n.iconPlaceholder + '" readonly style="flex:1"><button type="button" class="en-media-btn en-sns-icon-btn" style="white-space:nowrap">' + enSnsI18n.selectIcon + '</button><button type="button" class="en-remove-btn en-sns-icon-clear" style="white-space:nowrap">' + enSnsI18n.clear + '</button></div>';
        list.appendChild(div);
    };

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
})();
EMONO_SNS_JS
    );
    ?>
    <?php
}

function emono_admin_tab_sns_DEPRECATED($opts) {
    emono_admin_notice();
    $sns = isset($opts['sns_links']) ? $opts['sns_links'] : array();
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_sns','en_nonce'); ?>
        <input type="hidden" name="en_action" value="sns">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Social Links', 'emerge-mono-portfolio' ); ?></div>
            <div id="en-sns-list">
                <?php foreach ( $sns as $s ) : ?>
                <div class="en-sns-row">
                    <input type="text" name="sns_label[]" value="<?php echo esc_attr(isset($s['label']) ? $s['label'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. X / Instagram', 'emerge-mono-portfolio' ); ?>" style="margin-bottom:6px">
                    <div style="display:flex;gap:8px">
                        <input type="url" name="sns_url[]" value="<?php echo esc_attr(isset($s['url']) ? $s['url'] : ''); ?>" class="en-field-input" placeholder="https://">
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-sns-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono-portfolio' ); ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddSns()">+ <?php esc_html_e( 'Add Social Link', 'emerge-mono-portfolio' ); ?></button>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
    </form>
    <?php
}
function emono_admin_tab_cpt($opts) {
    emono_admin_notice();

    $work_label    = isset($opts['work_label'])    ? $opts['work_label']    : 'Works';
    $work_singular = isset($opts['work_singular']) ? $opts['work_singular'] : 'Work';
    $cat_label     = isset($opts['cat_label'])     ? $opts['cat_label']     : 'Category';
    $work_icon     = isset($opts['work_icon'])     ? $opts['work_icon']     : 'dashicons-portfolio';

    // よく使うdashiconsのリスト
    $icons = array(
        'dashicons-portfolio'     => '💼 ' . __( 'Portfolio', 'emerge-mono-portfolio' ),
        'dashicons-images-alt2'   => '🖼 ' . __( 'Images / Works', 'emerge-mono-portfolio' ),
        'dashicons-art'           => '🎨 ' . __( 'Art', 'emerge-mono-portfolio' ),
        'dashicons-camera'        => '📷 ' . __( 'Camera', 'emerge-mono-portfolio' ),
        'dashicons-video-alt3'    => '🎬 ' . __( 'Video', 'emerge-mono-portfolio' ),
        'dashicons-music'         => '🎵 ' . __( 'Music', 'emerge-mono-portfolio' ),
        'dashicons-edit'          => '✏️ ' . __( 'Edit', 'emerge-mono-portfolio' ),
        'dashicons-admin-page'    => '📄 ' . __( 'Page', 'emerge-mono-portfolio' ),
        'dashicons-star-filled'   => '⭐ ' . __( 'Star', 'emerge-mono-portfolio' ),
        'dashicons-heart'         => '❤️ ' . __( 'Heart', 'emerge-mono-portfolio' ),
        'dashicons-products'      => '📦 ' . __( 'Product', 'emerge-mono-portfolio' ),
        'dashicons-admin-tools'   => '🔧 ' . __( 'Tools', 'emerge-mono-portfolio' ),
        'dashicons-lightbulb'     => '💡 ' . __( 'Idea', 'emerge-mono-portfolio' ),
        'dashicons-awards'        => '🏆 ' . __( 'Award', 'emerge-mono-portfolio' ),
        'dashicons-megaphone'     => '📣 ' . __( 'News', 'emerge-mono-portfolio' ),
    );
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_cpt','en_nonce'); ?>
        <input type="hidden" name="en_action" value="cpt">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Works Display Settings', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="line-height:1.9;margin-bottom:16px">
                <?php esc_html_e( 'Customize the name and icon of the "Works" post type shown in the WordPress admin.', 'emerge-mono-portfolio' ); ?><br>
                <?php esc_html_e( 'For example, you can rename "Works" to "Portfolio", "Projects", or "Gallery".', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Display Name (plural)', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="work_label" value="<?php echo esc_attr($work_label); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Works', 'emerge-mono-portfolio' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><?php esc_html_e( 'Name shown in the admin menu (e.g. Portfolio, Projects, Gallery)', 'emerge-mono-portfolio' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Display Name (singular)', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="work_singular" value="<?php echo esc_attr($work_singular); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Work', 'emerge-mono-portfolio' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><?php esc_html_e( 'Name for an individual item (e.g. Work, Piece, Project)', 'emerge-mono-portfolio' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Category Display Name', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="cat_label" value="<?php echo esc_attr($cat_label); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Category', 'emerge-mono-portfolio' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><?php esc_html_e( 'Label for categorizing works (e.g. Genre, Type, Tag)', 'emerge-mono-portfolio' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Admin Menu Icon', 'emerge-mono-portfolio' ); ?></label>
                <select name="work_icon" class="en-field-input" style="margin-bottom:8px">
                    <?php foreach ( $icons as $icon_class => $icon_label ) : ?>
                        <option value="<?php echo esc_attr($icon_class); ?>" <?php selected( $work_icon, $icon_class ); ?>>
                            <?php echo esc_html($icon_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="en-field-desc"><?php esc_html_e( 'After selecting, click "Save" to change the icon in the left admin menu.', 'emerge-mono-portfolio' ); ?></div>
            </div>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
    </form>
    <?php
}
