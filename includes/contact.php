<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── 送信ログ用テーブル作成 ──
register_activation_hook( EMONO_PATH . 'emerge-mono-portfolio.php', 'emono_create_contact_log_table' );
function emono_create_contact_log_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'en_contact_log';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL DEFAULT '',
        email varchar(255) NOT NULL DEFAULT '',
        fields longtext NOT NULL DEFAULT '',
        ip varchar(64) NOT NULL DEFAULT '',
        status varchar(20) NOT NULL DEFAULT 'unread',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'plugins_loaded', function() {
    global $wpdb;
    $table = $wpdb->prefix . 'en_contact_log';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
        emono_create_contact_log_table();
    }
});

// ── Ajax送信処理 ──
add_action( 'wp_ajax_en_send_contact',        'emono_handle_contact' );
add_action( 'wp_ajax_nopriv_en_send_contact', 'emono_handle_contact' );

function emono_handle_contact() {
    // nonce検証
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'en_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid request.', 'emerge-mono-portfolio' ) ) );
    }

    // ハニーポット（ボット対策）
    if ( ! empty( $_POST['en_hp_field'] ) ) {
        wp_send_json_error( array( 'message' => __( 'Failed to send.', 'emerge-mono-portfolio' ) ) );
    }

    // レート制限（同一IPから1時間に5回まで）
    $ip = emono_get_client_ip();
    $rate_key = 'en_rate_' . md5( $ip );
    $count = (int) get_transient( $rate_key );
    if ( $count >= 5 ) {
        wp_send_json_error( array( 'message' => __( 'You have reached the submission limit. Please try again later.', 'emerge-mono-portfolio' ) ) );
    }
    set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

    // reCAPTCHA v3検証
    $recaptcha_secret = emono_opt('recaptcha_secret', '');
    if ( $recaptcha_secret && isset($_POST['recaptcha_token']) ) {
        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'   => $recaptcha_secret,
                'response' => sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ?? '' ) ),
                'remoteip' => $ip,
            )
        ));
        if ( ! is_wp_error($verify) ) {
            $result = json_decode( wp_remote_retrieve_body($verify), true );
            if ( empty($result['success']) || ( isset($result['score']) && $result['score'] < 0.5 ) ) {
                wp_send_json_error( array( 'message' => __( 'Detected as spam.', 'emerge-mono-portfolio' ) ) );
            }
        }
    }

    // フィールドのバリデーション・サニタイズ
    $form_fields = get_option( 'en_contact_fields', emono_default_contact_fields() );
    $data = array();
    $name_val  = '';
    $email_val = '';

    foreach ( $form_fields as $field ) {
        $key      = sanitize_key( $field['key'] );
        $label    = sanitize_text_field( $field['label'] );
        $type     = $field['type'];
        $required = ! empty( $field['required'] );
        $val      = isset($_POST['en_field_' . $key]) ? sanitize_textarea_field( wp_unslash( $_POST['en_field_' . $key] ) ) : '';

        if ( $type === 'email' ) {
            $val = sanitize_email( $val );
            if ( $required && ! is_email($val) ) {
                /* translators: %s: the field label */
                wp_send_json_error( array( 'message' => sprintf( __( 'Please enter a valid %s.', 'emerge-mono-portfolio' ), $label ) ) );
            }
            $email_val = $val;
        } elseif ( $type === 'checkbox' ) {
            $val = is_array($val) ? array_map('sanitize_text_field', $val) : array();
            if ( $required && empty($val) ) {
                /* translators: %s: the field label */
                wp_send_json_error( array( 'message' => sprintf( __( 'Please select %s.', 'emerge-mono-portfolio' ), $label ) ) );
            }
            $val = implode(', ', $val);
        } else {
            $val = sanitize_textarea_field( $val );
            if ( $required && empty($val) ) {
                /* translators: %s: the field label */
                wp_send_json_error( array( 'message' => sprintf( __( 'Please enter %s.', 'emerge-mono-portfolio' ), $label ) ) );
            }
        }

        if ( $key === 'name' ) $name_val = $val;
        $data[] = array( 'label' => $label, 'value' => $val );
    }

    if ( ! $email_val ) {
        wp_send_json_error( array( 'message' => __( 'Email address not found.', 'emerge-mono-portfolio' ) ) );
    }

    // 管理者へのメール送信
    $to      = emono_opt('contact_email', get_option('admin_email'));
    /* translators: %s: the site name */
    $subject = emono_opt('contact_mail_subject', sprintf( __( '[Inquiry] %s', 'emerge-mono-portfolio' ), get_bloginfo('name') ));
    $estimate_summary = isset($_POST['en_field_estimate_summary']) ? sanitize_textarea_field( wp_unslash( $_POST['en_field_estimate_summary'] ) ) : '';
    $body    = emono_opt('contact_mail_body', __( "You have received a new inquiry:\n\n", 'emerge-mono-portfolio' ));
    if ( $estimate_summary ) {
        $body .= __( "■ Estimate Details\n", 'emerge-mono-portfolio' ) . $estimate_summary . "\n\n";
    }
    foreach ( $data as $d ) {
        $body .= '■ ' . $d['label'] . "\n" . $d['value'] . "\n\n";
    }
    $body .= __( "---\nSender IP: ", 'emerge-mono-portfolio' ) . $ip . __( "\nSent at: ", 'emerge-mono-portfolio' ) . current_time('Y-m-d H:i:s');

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name_val . ' <' . $email_val . '>',
    );
    wp_mail( $to, $subject, $body, $headers );

    // 自動返信メール
    $auto_reply = emono_opt('contact_auto_reply', '1');
    if ( $auto_reply === '1' ) {
        /* translators: %s: the site name */
        $reply_subject = emono_opt('contact_reply_subject', sprintf( __( 'We received your inquiry | %s', 'emerge-mono-portfolio' ), get_bloginfo('name') ));
        $reply_body    = emono_opt('contact_reply_body', __( "Thank you for your inquiry.\nWe will review your message and get back to you shortly.\n\n", 'emerge-mono-portfolio' ) . get_bloginfo('name'));
        wp_mail( $email_val, $reply_subject, $reply_body );
    }

    // 送信ログ保存
    global $wpdb;
    $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
        $wpdb->prefix . 'en_contact_log',
        array(
            'name'       => $name_val,
            'email'      => $email_val,
            'fields'     => json_encode( $data, JSON_UNESCAPED_UNICODE ),
            'ip'         => $ip,
            'status'     => 'unread',
            'created_at' => current_time('mysql'),
        )
    );

    wp_send_json_success( array( 'message' => emono_opt('contact_success', __( 'Your message has been sent.', 'emerge-mono-portfolio' )) ) );
}

// ── IPアドレス取得 ──
function emono_get_client_ip() {
    // Only REMOTE_ADDR is used, because it is set by the web server and cannot be
    // spoofed by the client. Proxy headers such as X-Forwarded-For are attacker-
    // controlled and would let spammers bypass the rate limit by faking IPs.
    if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return $ip;
        }
    }
    return '0.0.0.0';
}

// ── デフォルトフィールド ──
function emono_default_contact_fields() {
    return array(
        array( 'key' => 'name',    'label' => __( 'Name', 'emerge-mono-portfolio' ),            'type' => 'text',     'required' => true,  'placeholder' => __( 'e.g. John Smith', 'emerge-mono-portfolio' ) ),
        array( 'key' => 'email',   'label' => __( 'Email', 'emerge-mono-portfolio' ),           'type' => 'email',    'required' => true,  'placeholder' => 'example@email.com' ),
        array( 'key' => 'message', 'label' => __( 'Message', 'emerge-mono-portfolio' ),         'type' => 'textarea', 'required' => true,  'placeholder' => __( 'Feel free to send us your requests or questions.', 'emerge-mono-portfolio' ) ),
    );
}

// ── reCAPTCHA v3のスクリプト出力 ──
add_action( 'wp_enqueue_scripts', 'emono_recaptcha_script' );
function emono_recaptcha_script() {
    $site_key = emono_opt('recaptcha_site_key', '');
    if ( ! $site_key ) return;

    // GoogleのreCAPTCHA APIを正式にenqueue
    wp_enqueue_script(
        'en-recaptcha-api',
        'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ),
        array(),
        null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External Google reCAPTCHA URL is versionless by design.
        true
    );

    // フォーム送信時のトークン取得処理をインラインで紐付け
    $inline = "
    document.addEventListener('DOMContentLoaded', function(){
        var form = document.getElementById('en-contact-form');
        if ( ! form ) return;
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var btn = form.querySelector('.en-form-submit');
            btn.disabled = true;
            grecaptcha.ready(function(){
                grecaptcha.execute(" . wp_json_encode( $site_key ) . ", {action:'contact'}).then(function(token){
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recaptcha_token';
                    input.value = token;
                    form.appendChild(input);
                    enSubmitContact();
                });
            });
        });
    });
    ";
    wp_add_inline_script( 'en-recaptcha-api', $inline );
}
