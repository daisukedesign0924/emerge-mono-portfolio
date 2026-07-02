<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── 送信ログ用テーブル作成 ──
register_activation_hook( EN_PATH . 'emerge-mono-portfolio.php', 'en_create_contact_log_table' );
function en_create_contact_log_table() {
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
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table ) {
        en_create_contact_log_table();
    }
});

// ── Ajax送信処理 ──
add_action( 'wp_ajax_en_send_contact',        'en_handle_contact' );
add_action( 'wp_ajax_nopriv_en_send_contact', 'en_handle_contact' );

function en_handle_contact() {
    // nonce検証
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], 'en_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid request.', 'emerge-mono' ) ) );
    }

    // ハニーポット（ボット対策）
    if ( ! empty( $_POST['en_hp_field'] ) ) {
        wp_send_json_error( array( 'message' => __( 'Failed to send.', 'emerge-mono' ) ) );
    }

    // レート制限（同一IPから1時間に5回まで）
    $ip = en_get_client_ip();
    $rate_key = 'en_rate_' . md5( $ip );
    $count = (int) get_transient( $rate_key );
    if ( $count >= 5 ) {
        wp_send_json_error( array( 'message' => __( 'You have reached the submission limit. Please try again later.', 'emerge-mono' ) ) );
    }
    set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

    // reCAPTCHA v3検証
    $recaptcha_secret = en_opt('recaptcha_secret', '');
    if ( $recaptcha_secret && isset($_POST['recaptcha_token']) ) {
        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'   => $recaptcha_secret,
                'response' => sanitize_text_field( $_POST['recaptcha_token'] ),
                'remoteip' => $ip,
            )
        ));
        if ( ! is_wp_error($verify) ) {
            $result = json_decode( wp_remote_retrieve_body($verify), true );
            if ( empty($result['success']) || ( isset($result['score']) && $result['score'] < 0.5 ) ) {
                wp_send_json_error( array( 'message' => __( 'Detected as spam.', 'emerge-mono' ) ) );
            }
        }
    }

    // フィールドのバリデーション・サニタイズ
    $form_fields = get_option( 'en_contact_fields', en_default_contact_fields() );
    $data = array();
    $name_val  = '';
    $email_val = '';

    foreach ( $form_fields as $field ) {
        $key      = sanitize_key( $field['key'] );
        $label    = sanitize_text_field( $field['label'] );
        $type     = $field['type'];
        $required = ! empty( $field['required'] );
        $val      = isset($_POST['en_field_' . $key]) ? $_POST['en_field_' . $key] : '';

        if ( $type === 'email' ) {
            $val = sanitize_email( $val );
            if ( $required && ! is_email($val) ) {
                wp_send_json_error( array( 'message' => sprintf( __( 'Please enter a valid %s.', 'emerge-mono' ), $label ) ) );
            }
            $email_val = $val;
        } elseif ( $type === 'checkbox' ) {
            $val = is_array($val) ? array_map('sanitize_text_field', $val) : array();
            if ( $required && empty($val) ) {
                wp_send_json_error( array( 'message' => sprintf( __( 'Please select %s.', 'emerge-mono' ), $label ) ) );
            }
            $val = implode(', ', $val);
        } else {
            $val = sanitize_textarea_field( $val );
            if ( $required && empty($val) ) {
                wp_send_json_error( array( 'message' => sprintf( __( 'Please enter %s.', 'emerge-mono' ), $label ) ) );
            }
        }

        if ( $key === 'name' ) $name_val = $val;
        $data[] = array( 'label' => $label, 'value' => $val );
    }

    if ( ! $email_val ) {
        wp_send_json_error( array( 'message' => __( 'Email address not found.', 'emerge-mono' ) ) );
    }

    // 管理者へのメール送信
    $to      = en_opt('contact_email', get_option('admin_email'));
    $subject = en_opt('contact_mail_subject', sprintf( __( '[Inquiry] %s', 'emerge-mono' ), get_bloginfo('name') ));
    $estimate_summary = isset($_POST['en_field_estimate_summary']) ? sanitize_textarea_field( $_POST['en_field_estimate_summary'] ) : '';
    $body    = en_opt('contact_mail_body', __( "You have received a new inquiry:\n\n", 'emerge-mono' ));
    if ( $estimate_summary ) {
        $body .= __( "■ Estimate Details\n", 'emerge-mono' ) . $estimate_summary . "\n\n";
    }
    foreach ( $data as $d ) {
        $body .= '■ ' . $d['label'] . "\n" . $d['value'] . "\n\n";
    }
    $body .= __( "---\nSender IP: ", 'emerge-mono' ) . $ip . __( "\nSent at: ", 'emerge-mono' ) . current_time('Y-m-d H:i:s');

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name_val . ' <' . $email_val . '>',
    );
    wp_mail( $to, $subject, $body, $headers );

    // 自動返信メール
    $auto_reply = en_opt('contact_auto_reply', '1');
    if ( $auto_reply === '1' ) {
        $reply_subject = en_opt('contact_reply_subject', sprintf( __( 'We received your inquiry | %s', 'emerge-mono' ), get_bloginfo('name') ));
        $reply_body    = en_opt('contact_reply_body', __( "Thank you for your inquiry.\nWe will review your message and get back to you shortly.\n\n", 'emerge-mono' ) . get_bloginfo('name'));
        wp_mail( $email_val, $reply_subject, $reply_body );
    }

    // 送信ログ保存
    global $wpdb;
    $wpdb->insert(
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

    wp_send_json_success( array( 'message' => en_opt('contact_success', __( 'Your message has been sent.', 'emerge-mono' )) ) );
}

// ── IPアドレス取得 ──
function en_get_client_ip() {
    $keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );
    foreach ( $keys as $key ) {
        if ( ! empty( $_SERVER[ $key ] ) ) {
            $ip = trim( explode(',', $_SERVER[$key])[0] );
            if ( filter_var($ip, FILTER_VALIDATE_IP) ) return $ip;
        }
    }
    return '0.0.0.0';
}

// ── デフォルトフィールド ──
function en_default_contact_fields() {
    return array(
        array( 'key' => 'name',    'label' => __( 'Name', 'emerge-mono' ),            'type' => 'text',     'required' => true,  'placeholder' => __( 'e.g. John Smith', 'emerge-mono' ) ),
        array( 'key' => 'email',   'label' => __( 'Email', 'emerge-mono' ),           'type' => 'email',    'required' => true,  'placeholder' => 'example@email.com' ),
        array( 'key' => 'message', 'label' => __( 'Message', 'emerge-mono' ),         'type' => 'textarea', 'required' => true,  'placeholder' => __( 'Feel free to send us your requests or questions.', 'emerge-mono' ) ),
    );
}

// ── reCAPTCHA v3のスクリプト出力 ──
add_action( 'wp_footer', 'en_recaptcha_script' );
function en_recaptcha_script() {
    $site_key = en_opt('recaptcha_site_key', '');
    if ( ! $site_key ) return;
    ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var form = document.getElementById('en-contact-form');
        if ( ! form ) return;
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var btn = form.querySelector('.en-form-submit');
            btn.disabled = true;
            grecaptcha.ready(function(){
                grecaptcha.execute('<?php echo esc_js($site_key); ?>', {action:'contact'}).then(function(token){
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
    </script>
    <?php
}
