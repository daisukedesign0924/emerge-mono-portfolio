<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'en_handle_save' );
function en_handle_save() {
    if ( ! isset( $_POST['en_action'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;

    $action = sanitize_key( $_POST['en_action'] );
    $nonce_map = array(
        'design'  => 'en_save_design',
        'general' => 'en_save_general',
        'profile' => 'en_save_profile',
        'sns'     => 'en_save_sns',
        'nav'     => 'en_save_nav',
        'contact' => 'en_save_contact',
        'cpt'     => 'en_save_cpt',
    );

    $nonce_map['editor'] = 'en_save_editor';
    if ( ! isset( $nonce_map[ $action ] ) ) return;
    if ( ! wp_verify_nonce( $_POST['en_nonce'], $nonce_map[ $action ] ) ) return;

    $opts = en_get_options();

    switch ( $action ) {

        case 'design':
            $opts['design_mode']   = in_array( (isset($_POST['design_mode']) ? $_POST['design_mode'] : 'dark'), array('dark','light','auto') ) ? $_POST['design_mode'] : 'dark';
            $opts['design_bg']     = sanitize_hex_color( isset($_POST['design_bg'])     ? $_POST['design_bg']     : '#000000' );
            $opts['design_text']   = sanitize_hex_color( isset($_POST['design_text'])   ? $_POST['design_text']   : '#ffffff' );
            $opts['design_accent'] = sanitize_hex_color( isset($_POST['design_accent']) ? $_POST['design_accent'] : '#ffffff' );
            $opts['design_font']   = sanitize_text_field( isset($_POST['design_font'])  ? $_POST['design_font']   : 'Space Mono' );
            break;

        case 'general':
            $opts['site_name']    = sanitize_text_field( (isset($_POST['site_name']) ? $_POST['site_name'] : '') );
            $opts['site_tagline'] = sanitize_text_field( (isset($_POST['site_tagline']) ? $_POST['site_tagline'] : '') );
            $opts['copyright']    = sanitize_text_field( (isset($_POST['copyright']) ? $_POST['copyright'] : '') );
            $opts['logo_url']       = esc_url_raw( isset($_POST['logo_url']) ? $_POST['logo_url'] : '' );
            $opts['logo_url_light'] = esc_url_raw( isset($_POST['logo_url_light']) ? $_POST['logo_url_light'] : '' );
            // トップページロゴサイズ（ブレイクポイント別・vw）。空欄/0は継承用に0保存
            $opts['top_logo_size']        = isset($_POST['top_logo_size'])        && (float)$_POST['top_logo_size'] > 0 ? (float)$_POST['top_logo_size'] : 14;
            $opts['top_logo_size_tablet'] = isset($_POST['top_logo_size_tablet']) && (float)$_POST['top_logo_size_tablet'] > 0 ? (float)$_POST['top_logo_size_tablet'] : 0;
            $opts['top_logo_size_mobile'] = isset($_POST['top_logo_size_mobile']) && (float)$_POST['top_logo_size_mobile'] > 0 ? (float)$_POST['top_logo_size_mobile'] : 0;
            // ヘッダーロゴサイズ（ブレイクポイント別・vw）
            $opts['header_logo_size']        = isset($_POST['header_logo_size'])        && (float)$_POST['header_logo_size'] > 0 ? (float)$_POST['header_logo_size'] : 3.2;
            $opts['header_logo_size_tablet'] = isset($_POST['header_logo_size_tablet']) && (float)$_POST['header_logo_size_tablet'] > 0 ? (float)$_POST['header_logo_size_tablet'] : 0;
            $opts['header_logo_size_mobile'] = isset($_POST['header_logo_size_mobile']) && (float)$_POST['header_logo_size_mobile'] > 0 ? (float)$_POST['header_logo_size_mobile'] : 0;
            // TOPボタン（複数対応）
            $btn_labels   = (isset($_POST['top_btn_label']) ? $_POST['top_btn_label'] : array());
            $btn_urls     = (isset($_POST['top_btn_url']) ? $_POST['top_btn_url'] : array());
            $btn_manuals  = (isset($_POST['top_btn_url_manual']) ? $_POST['top_btn_url_manual'] : array());
            $top_buttons  = array();
            foreach ( $btn_labels as $i => $label ) {
                $label = sanitize_text_field( $label );
                if ( ! $label ) continue;
                $sel = isset($btn_urls[$i]) ? $btn_urls[$i] : '';
                $url = $sel ? esc_url_raw($sel) : esc_url_raw( (isset($btn_manuals[$i]) ? $btn_manuals[$i] : '') );
                $top_buttons[] = array( 'label' => $label, 'url' => $url );
            }
            $opts['top_buttons'] = $top_buttons;
            break;

        case 'profile':
            $opts['profile_name']   = sanitize_text_field( (isset($_POST['profile_name']) ? $_POST['profile_name'] : '') );
            $opts['profile_role']   = sanitize_text_field( (isset($_POST['profile_role']) ? $_POST['profile_role'] : '') );
            $opts['profile_bio']    = sanitize_textarea_field( (isset($_POST['profile_bio']) ? $_POST['profile_bio'] : '') );
            $opts['profile_img']    = esc_url_raw( (isset($_POST['profile_img']) ? $_POST['profile_img'] : '') );
            $opts['profile_skills'] = sanitize_text_field( (isset($_POST['profile_skills']) ? $_POST['profile_skills'] : '') );

            // SNSリンク（プロフィールに統合）
            $labels = (isset($_POST['sns_label']) ? $_POST['sns_label'] : array());
            $urls   = (isset($_POST['sns_url']) ? $_POST['sns_url'] : array());
            $icons  = (isset($_POST['sns_icon']) ? $_POST['sns_icon'] : array());
            $sns = array();
            if ( is_array($labels) ) {
                foreach ( $labels as $i => $label ) {
                    $url = esc_url_raw( isset($urls[$i]) ? $urls[$i] : '' );
                    if ( $url ) {
                        $sns[] = array(
                            'label' => sanitize_text_field( $label ),
                            'url'   => $url,
                            'icon'  => esc_url_raw( isset($icons[$i]) ? $icons[$i] : '' ),
                        );
                    }
                }
            }
            $opts['sns_links'] = $sns;
            break;

        case 'sns':
            $labels = (isset($_POST['sns_label']) ? $_POST['sns_label'] : array());
            $urls   = (isset($_POST['sns_url']) ? $_POST['sns_url'] : array());
            $sns = array();
            foreach ( $labels as $i => $label ) {
                $url = esc_url_raw( isset($urls[$i]) ? $urls[$i] : '' );
                if ( $url ) {
                    $sns[] = array(
                        'label' => sanitize_text_field( $label ),
                        'url'   => $url,
                    );
                }
            }
            $opts['sns_links'] = $sns;
            break;

        case 'nav':
            $opts['nav_mode']    = sanitize_key( (isset($_POST['nav_mode']) ? $_POST['nav_mode'] : 'auto') );
            $opts['nav_wp_menu'] = (int)( (isset($_POST['nav_wp_menu']) ? $_POST['nav_wp_menu'] : 0) );

            $labels   = (isset($_POST['nav_label']) ? $_POST['nav_label'] : array());
            $types    = (isset($_POST['nav_type']) ? $_POST['nav_type'] : array());
            $urls     = (isset($_POST['nav_url']) ? $_POST['nav_url'] : array());
            $page_ids = (isset($_POST['nav_page_id']) ? $_POST['nav_page_id'] : array());
            $nav = array();
            foreach ( $labels as $i => $label ) {
                $type = sanitize_key( (isset($types[$i]) ? $types[$i] : 'url') );
                if ( $type === 'page' ) {
                    $page_id = (int)( (isset($page_ids[$i]) ? $page_ids[$i] : 0) );
                    if ( $page_id ) {
                        $nav[] = array(
                            'label'   => sanitize_text_field( $label ),
                            'type'    => 'page',
                            'page_id' => $page_id,
                            'url'     => get_permalink( $page_id ),
                        );
                    }
                } else {
                    $url = esc_url_raw( (isset($urls[$i]) ? $urls[$i] : '') );
                    if ( $label && $url ) {
                        $nav[] = array(
                            'label' => sanitize_text_field( $label ),
                            'type'  => 'url',
                            'url'   => $url,
                        );
                    }
                }
            }
            $opts['nav_items'] = $nav;

            // フッターメニュー
            $f_labels   = (isset($_POST['footer_nav_label']) ? $_POST['footer_nav_label'] : array());
            $f_types    = (isset($_POST['footer_nav_type']) ? $_POST['footer_nav_type'] : array());
            $f_urls     = (isset($_POST['footer_nav_url']) ? $_POST['footer_nav_url'] : array());
            $f_page_ids = (isset($_POST['footer_nav_page_id']) ? $_POST['footer_nav_page_id'] : array());
            $footer_nav = array();
            if ( is_array($f_types) ) {
                foreach ( $f_types as $i => $f_type ) {
                    $f_type = sanitize_key( $f_type );
                    $f_label = isset($f_labels[$i]) ? sanitize_text_field( $f_labels[$i] ) : '';
                    if ( $f_type === 'page' ) {
                        $f_page_id = (int)( isset($f_page_ids[$i]) ? $f_page_ids[$i] : 0 );
                        if ( $f_page_id ) {
                            $footer_nav[] = array(
                                'label'   => $f_label,
                                'type'    => 'page',
                                'page_id' => $f_page_id,
                                'url'     => get_permalink( $f_page_id ),
                            );
                        }
                    } else {
                        $f_url = esc_url_raw( isset($f_urls[$i]) ? $f_urls[$i] : '' );
                        if ( $f_label && $f_url ) {
                            $footer_nav[] = array(
                                'label' => $f_label,
                                'type'  => 'url',
                                'url'   => $f_url,
                            );
                        }
                    }
                }
            }
            $opts['footer_nav_items'] = $footer_nav;
            break;

        case 'contact':
            $opts['contact_email']         = sanitize_email( (isset($_POST['contact_email']) ? $_POST['contact_email'] : '') );
            $opts['contact_desc']          = sanitize_textarea_field( (isset($_POST['contact_desc']) ? $_POST['contact_desc'] : '') );
            $opts['contact_btn_text']      = sanitize_text_field( (isset($_POST['contact_btn_text']) ? $_POST['contact_btn_text'] : 'Send') );
            $opts['contact_success']       = sanitize_text_field( (isset($_POST['contact_success']) ? $_POST['contact_success'] : '') );
            $opts['contact_auto_reply']    = sanitize_text_field( (isset($_POST['contact_auto_reply']) ? $_POST['contact_auto_reply'] : '1') );
            $opts['contact_reply_subject'] = sanitize_text_field( (isset($_POST['contact_reply_subject']) ? $_POST['contact_reply_subject'] : '') );
            $opts['contact_reply_body']    = sanitize_textarea_field( (isset($_POST['contact_reply_body']) ? $_POST['contact_reply_body'] : '') );
            $opts['contact_mail_subject']  = sanitize_text_field( (isset($_POST['contact_mail_subject']) ? $_POST['contact_mail_subject'] : '') );
            $opts['contact_mail_body']     = sanitize_textarea_field( (isset($_POST['contact_mail_body']) ? $_POST['contact_mail_body'] : '') );
            $opts['recaptcha_site_key']    = sanitize_text_field( (isset($_POST['recaptcha_site_key']) ? $_POST['recaptcha_site_key'] : '') );
            $opts['recaptcha_secret']           = sanitize_text_field( (isset($_POST['recaptcha_secret']) ? $_POST['recaptcha_secret'] : '') );
            $opts['contact_consent_enabled']    = isset($_POST['contact_consent_enabled']) ? '1' : '0';
            $opts['contact_consent_text']       = sanitize_text_field( (isset($_POST['contact_consent_text']) ? $_POST['contact_consent_text'] : __( 'I agree to the Privacy Policy.', 'emerge-mono' )) );
            $opts['contact_consent_page_id']    = (int)( (isset($_POST['contact_consent_page_id']) ? $_POST['contact_consent_page_id'] : 0) );
            update_option( 'en_options', $opts );

            // フォームフィールド保存
            $cf_labels       = (isset($_POST['cf_label']) ? $_POST['cf_label'] : array());
            $cf_keys         = (isset($_POST['cf_key']) ? $_POST['cf_key'] : array());
            $cf_types        = (isset($_POST['cf_type']) ? $_POST['cf_type'] : array());
            $cf_placeholders = (isset($_POST['cf_placeholder']) ? $_POST['cf_placeholder'] : array());
            $cf_requireds    = (isset($_POST['cf_required']) ? $_POST['cf_required'] : array());
            $cf_options_raw  = (isset($_POST['cf_options']) ? $_POST['cf_options'] : array());

            $contact_fields = array();
            foreach ( $cf_keys as $i => $key ) {
                $key = sanitize_key( $key );
                if ( ! $key ) continue;
                $type = sanitize_text_field( (isset($cf_types[$i]) ? $cf_types[$i] : 'text') );
                $options = array();
                if ( in_array($type, array('select','checkbox')) && ! empty($cf_options_raw[$i]) ) {
                    foreach ( explode("
", $cf_options_raw[$i]) as $line ) {
                        $line = trim($line);
                        if ( $line ) $options[] = sanitize_text_field($line);
                    }
                }
                $contact_fields[] = array(
                    'key'         => $key,
                    'label'       => sanitize_text_field( isset($cf_labels[$i]) ? $cf_labels[$i] : $key ),
                    'type'        => $type,
                    'placeholder' => sanitize_text_field( (isset($cf_placeholders[$i]) ? $cf_placeholders[$i] : '') ),
                    'required'    => isset($cf_requireds[$i]) && $cf_requireds[$i] === '1',
                    'options'     => $options,
                );
            }
            update_option( 'en_contact_fields', $contact_fields );
            wp_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=contact&saved=1' ) );
            exit;

        case 'cpt':
            $opts['work_label']    = sanitize_text_field( isset($_POST['work_label'])    ? $_POST['work_label']    : 'Works' );
            $opts['work_singular'] = sanitize_text_field( isset($_POST['work_singular']) ? $_POST['work_singular'] : 'Work' );
            $opts['cat_label']     = sanitize_text_field( isset($_POST['cat_label'])     ? $_POST['cat_label']     : __( 'Category', 'emerge-mono' ) );
            $opts['work_icon']     = sanitize_text_field( isset($_POST['work_icon'])     ? $_POST['work_icon']     : 'dashicons-portfolio' );
            update_option( 'en_options', $opts );
            flush_rewrite_rules();
            break;

        case 'nav':
            $opts['nav_mode']    = sanitize_key( (isset($_POST['nav_mode']) ? $_POST['nav_mode'] : 'auto') );
            $opts['nav_wp_menu'] = (int)( (isset($_POST['nav_wp_menu']) ? $_POST['nav_wp_menu'] : 0) );

            $labels   = (isset($_POST['nav_label']) ? $_POST['nav_label'] : array());
            $types    = (isset($_POST['nav_type']) ? $_POST['nav_type'] : array());
            $urls     = (isset($_POST['nav_url']) ? $_POST['nav_url'] : array());
            $page_ids = (isset($_POST['nav_page_id']) ? $_POST['nav_page_id'] : array());
            $nav = array();
            foreach ( $labels as $i => $label ) {
                $type = sanitize_key( (isset($types[$i]) ? $types[$i] : 'url') );
                if ( $type === 'page' ) {
                    $page_id = (int)( (isset($page_ids[$i]) ? $page_ids[$i] : 0) );
                    if ( $page_id ) {
                        $nav[] = array(
                            'label'   => sanitize_text_field( $label ),
                            'type'    => 'page',
                            'page_id' => $page_id,
                            'url'     => get_permalink( $page_id ),
                        );
                    }
                } else {
                    $url = esc_url_raw( (isset($urls[$i]) ? $urls[$i] : '') );
                    if ( $label && $url ) {
                        $nav[] = array(
                            'label' => sanitize_text_field( $label ),
                            'type'  => 'url',
                            'url'   => $url,
                        );
                    }
                }
            }
            $opts['nav_items'] = $nav;
            break;

        case 'contact':
            $opts['contact_email']         = sanitize_email( (isset($_POST['contact_email']) ? $_POST['contact_email'] : '') );
            $opts['contact_desc']          = sanitize_textarea_field( (isset($_POST['contact_desc']) ? $_POST['contact_desc'] : '') );
            $opts['contact_btn_text']      = sanitize_text_field( (isset($_POST['contact_btn_text']) ? $_POST['contact_btn_text'] : 'Send') );
            $opts['contact_success']       = sanitize_text_field( (isset($_POST['contact_success']) ? $_POST['contact_success'] : '') );
            $opts['contact_auto_reply']    = sanitize_text_field( (isset($_POST['contact_auto_reply']) ? $_POST['contact_auto_reply'] : '1') );
            $opts['contact_reply_subject'] = sanitize_text_field( (isset($_POST['contact_reply_subject']) ? $_POST['contact_reply_subject'] : '') );
            $opts['contact_reply_body']    = sanitize_textarea_field( (isset($_POST['contact_reply_body']) ? $_POST['contact_reply_body'] : '') );
            $opts['contact_mail_subject']  = sanitize_text_field( (isset($_POST['contact_mail_subject']) ? $_POST['contact_mail_subject'] : '') );
            $opts['contact_mail_body']     = sanitize_textarea_field( (isset($_POST['contact_mail_body']) ? $_POST['contact_mail_body'] : '') );
            $opts['recaptcha_site_key']    = sanitize_text_field( (isset($_POST['recaptcha_site_key']) ? $_POST['recaptcha_site_key'] : '') );
            $opts['recaptcha_secret']           = sanitize_text_field( (isset($_POST['recaptcha_secret']) ? $_POST['recaptcha_secret'] : '') );
            $opts['contact_consent_enabled']    = isset($_POST['contact_consent_enabled']) ? '1' : '0';
            $opts['contact_consent_text']       = sanitize_text_field( (isset($_POST['contact_consent_text']) ? $_POST['contact_consent_text'] : __( 'I agree to the Privacy Policy.', 'emerge-mono' )) );
            $opts['contact_consent_page_id']    = (int)( (isset($_POST['contact_consent_page_id']) ? $_POST['contact_consent_page_id'] : 0) );
            update_option( 'en_options', $opts );

            // フォームフィールド保存
            $cf_labels       = (isset($_POST['cf_label']) ? $_POST['cf_label'] : array());
            $cf_keys         = (isset($_POST['cf_key']) ? $_POST['cf_key'] : array());
            $cf_types        = (isset($_POST['cf_type']) ? $_POST['cf_type'] : array());
            $cf_placeholders = (isset($_POST['cf_placeholder']) ? $_POST['cf_placeholder'] : array());
            $cf_requireds    = (isset($_POST['cf_required']) ? $_POST['cf_required'] : array());
            $cf_options_raw  = (isset($_POST['cf_options']) ? $_POST['cf_options'] : array());

            $contact_fields = array();
            foreach ( $cf_keys as $i => $key ) {
                $key = sanitize_key( $key );
                if ( ! $key ) continue;
                $type = sanitize_text_field( (isset($cf_types[$i]) ? $cf_types[$i] : 'text') );
                $options = array();
                if ( in_array($type, array('select','checkbox')) && ! empty($cf_options_raw[$i]) ) {
                    foreach ( explode("
", $cf_options_raw[$i]) as $line ) {
                        $line = trim($line);
                        if ( $line ) $options[] = sanitize_text_field($line);
                    }
                }
                $contact_fields[] = array(
                    'key'         => $key,
                    'label'       => sanitize_text_field( isset($cf_labels[$i]) ? $cf_labels[$i] : $key ),
                    'type'        => $type,
                    'placeholder' => sanitize_text_field( (isset($cf_placeholders[$i]) ? $cf_placeholders[$i] : '') ),
                    'required'    => isset($cf_requireds[$i]) && $cf_requireds[$i] === '1',
                    'options'     => $options,
                );
            }
            update_option( 'en_contact_fields', $contact_fields );
            wp_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=contact&saved=1' ) );
            exit;

        case 'cpt':
            $opts['work_label']    = sanitize_text_field( isset($_POST['work_label'])    ? $_POST['work_label']    : 'Works' );
            $opts['work_singular'] = sanitize_text_field( isset($_POST['work_singular']) ? $_POST['work_singular'] : 'Work' );
            $opts['cat_label']     = sanitize_text_field( isset($_POST['cat_label'])     ? $_POST['cat_label']     : __( 'Category', 'emerge-mono' ) );
            $opts['work_icon']     = sanitize_text_field( isset($_POST['work_icon'])     ? $_POST['work_icon']     : 'dashicons-portfolio' );
            update_option( 'en_options', $opts );
            flush_rewrite_rules();
            break;

        case 'fields':
            $labels = (isset($_POST['field_label']) ? $_POST['field_label'] : array());
            $keys   = (isset($_POST['field_key']) ? $_POST['field_key'] : array());
            $types  = (isset($_POST['field_type']) ? $_POST['field_type'] : array());
            $fields = array();
            foreach ( $keys as $i => $key ) {
                $key = sanitize_key( $key );
                if ( $key ) {
                    $fields[] = array(
                        'label' => sanitize_text_field( isset($labels[$i]) ? $labels[$i] : $key ),
                        'key'   => $key,
                        'type'  => in_array( isset($types[$i]) ? $types[$i] : '', array('text','url','textarea','date') ) ? $types[ $i ] : 'text',
                    );
                }
            }
            update_option( 'en_custom_fields', $fields );
            break;
    }

    // エディター設定
    if ( $action === 'editor' ) {
        $opts['editor_enabled']     = isset($_POST['editor_enabled']) && $_POST['editor_enabled'] === '1' ? '1' : '0';
        $opts['show_default_posts'] = isset($_POST['show_default_posts']) && $_POST['show_default_posts'] === '1' ? '1' : '0';
    }

    if ( in_array( $action, array('design','general','profile','sns','nav','contact','editor') ) ) {
        update_option( 'en_options', $opts );
    }

    $tab = $action === 'design' ? 'design' : ( $action === 'cpt' ? 'cpt' : ( $action === 'fields' ? 'fields' : $action ) );
    wp_redirect( admin_url( 'admin.php?page=emerge-mono-portfolio&tab=' . $tab . '&saved=1' ) );
    exit;
}
