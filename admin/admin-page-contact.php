<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_contact($opts) {
    emono_admin_notice();
    $fields = get_option('en_contact_fields', emono_default_contact_fields());
    $field_types = array(
        'text'     => __( 'Text (single line)', 'emerge-mono-portfolio' ),
        'email'    => __( 'Email', 'emerge-mono-portfolio' ),
        'tel'      => __( 'Phone', 'emerge-mono-portfolio' ),
        'textarea' => __( 'Text (multi-line)', 'emerge-mono-portfolio' ),
        'select'   => __( 'Select box', 'emerge-mono-portfolio' ),
        'checkbox' => __( 'Checkbox', 'emerge-mono-portfolio' ),
    );

    // 送信ログ表示
    global $wpdb;
    $table = $wpdb->prefix . 'en_contact_log';
    $logs = array();
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
        // $table is an internal, plugin-generated name ($wpdb->prefix . 'en_contact_log'); no user input.
        $logs = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 30" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Internal table name; no user input.
        // 既読処理
        if ( isset($_GET['mark_read']) && isset($_GET['_ennonce'])
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_ennonce'] ?? '' ) ), 'en_log_action' ) ) {
            $wpdb->update($table, array('status'=>'read'), array('id'=>(int)$_GET['mark_read'])); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
        }
        if ( isset($_GET['delete_log']) && isset($_GET['_ennonce'])
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_ennonce'] ?? '' ) ), 'en_log_action' ) ) {
            $wpdb->delete($table, array('id'=>(int)$_GET['delete_log'])); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
        }
    }
    // $table is an internal, plugin-generated name; no user input.
    $unread = ( $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status='unread'" ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status='unread'" ) : 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Internal table name; no user input.
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_contact','en_nonce'); ?>
        <input type="hidden" name="en_action" value="contact">

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Basic Settings', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Recipient Email', 'emerge-mono-portfolio' ); ?></label>
                <input type="email" name="contact_email" value="<?php echo esc_attr(isset($opts['contact_email']) ? $opts['contact_email'] : get_option('admin_email')); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Description', 'emerge-mono-portfolio' ); ?></label>
                <textarea name="contact_desc" class="en-field-textarea"><?php echo esc_textarea(isset($opts['contact_desc']) ? $opts['contact_desc'] : ''); ?></textarea>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Submit Button Text', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="contact_btn_text" value="<?php echo esc_attr(isset($opts['contact_btn_text']) ? $opts['contact_btn_text'] : 'Send'); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Send', 'emerge-mono-portfolio' ); ?>">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Success Message', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="contact_success" value="<?php echo esc_attr(isset($opts['contact_success']) ? $opts['contact_success'] : __( 'Your message has been sent.', 'emerge-mono-portfolio' )); ?>" class="en-field-input">
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Form Fields', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Freely add or remove form fields. You must include at least one email field.', 'emerge-mono-portfolio' ); ?></div>
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
                            <input type="text" name="cf_label[]" value="<?php echo esc_attr($field['label']); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Your Name', 'emerge-mono-portfolio' ); ?>">
                        </div>
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field Type', 'emerge-mono-portfolio' ); ?></div>
                            <select name="cf_type[]" class="en-field-input en-field-select" onchange="enToggleOptions(this)">
                                <?php foreach ( $field_types as $ft => $fl ) : ?>
                                    <option value="<?php echo esc_attr( $ft ); ?>" <?php selected($type,$ft); ?>><?php echo esc_html( $fl ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="en-remove-btn" style="margin-top:20px" onclick="this.closest('.en-contact-field-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono-portfolio' ); ?></button>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px">
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field ID (alphanumeric)', 'emerge-mono-portfolio' ); ?></div>
                            <input type="text" name="cf_key[]" value="<?php echo esc_attr($key); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'name', 'emerge-mono-portfolio' ); ?>">
                        </div>
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Placeholder', 'emerge-mono-portfolio' ); ?></div>
                            <input type="text" name="cf_placeholder[]" value="<?php echo esc_attr(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Show example', 'emerge-mono-portfolio' ); ?>">
                        </div>
                        <div>
                            <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Required / Optional', 'emerge-mono-portfolio' ); ?></div>
                            <select name="cf_required[]" class="en-field-input en-field-select">
                                <option value="1" <?php selected(!empty($field['required']),true); ?>><?php esc_html_e( 'Required', 'emerge-mono-portfolio' ); ?></option>
                                <option value="0" <?php selected(!empty($field['required']),false); ?>><?php esc_html_e( 'Optional', 'emerge-mono-portfolio' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="en-cf-options" style="<?php echo in_array($type,array('select','checkbox')) ? '' : 'display:none'; ?>">
                        <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Options (one per line)', 'emerge-mono-portfolio' ); ?></div>
                        <textarea name="cf_options[]" class="en-field-textarea" style="height:80px" placeholder="<?php esc_attr_e( 'Option A&#10;Option B&#10;Option C', 'emerge-mono-portfolio' ); ?>"><?php echo esc_textarea($opts_val); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="en-add-btn" onclick="enAddContactField()">+ Add Field</button>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Auto-Reply Email', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Send Auto-Reply', 'emerge-mono-portfolio' ); ?></label>
                <select name="contact_auto_reply" class="en-field-input en-field-select">
                    <option value="1" <?php selected(isset($opts['contact_auto_reply']) ? $opts['contact_auto_reply'] : '1', '1'); ?>>Send</option>
                    <option value="0" <?php selected(isset($opts['contact_auto_reply']) ? $opts['contact_auto_reply'] : '1', '0'); ?>><?php esc_html_e( 'Do not send', 'emerge-mono-portfolio' ); ?></option>
                </select>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Auto-Reply Subject', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="contact_reply_subject" value="<?php echo esc_attr(isset($opts['contact_reply_subject']) ? $opts['contact_reply_subject'] : __( 'We have received your inquiry', 'emerge-mono-portfolio' )); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Auto-Reply Body', 'emerge-mono-portfolio' ); ?></label>
                <textarea name="contact_reply_body" class="en-field-textarea" style="height:120px"><?php echo esc_textarea(isset($opts['contact_reply_body']) ? $opts['contact_reply_body'] : __( "Thank you for your inquiry.
We will review your message and get back to you shortly.", 'emerge-mono-portfolio' )); ?></textarea>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Admin Email Settings', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Subject', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="contact_mail_subject" value="<?php echo esc_attr(isset($opts['contact_mail_subject']) ? $opts['contact_mail_subject'] : '[Inquiry] ' . get_bloginfo('name')); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Email Intro Text', 'emerge-mono-portfolio' ); ?></label>
                <textarea name="contact_mail_body" class="en-field-textarea"><?php echo esc_textarea(isset($opts['contact_mail_body']) ? $opts['contact_mail_body'] : __( "You have received a new inquiry with the following details.\n\n", 'emerge-mono-portfolio' )); ?></textarea>
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Security Settings', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:12px">
                <?php esc_html_e( 'Honeypot spam protection is always enabled. Add reCAPTCHA v3 for extra protection.', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'reCAPTCHA v3 Site Key', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr(isset($opts['recaptcha_site_key']) ? $opts['recaptcha_site_key'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Enter your Google reCAPTCHA site key', 'emerge-mono-portfolio' ); ?>">
                <div class="en-field-desc" style="margin-top:4px"><a href="https://www.google.com/recaptcha/admin" target="_blank" style="color:rgba(255,255,255,.4)"><?php esc_html_e( 'Google reCAPTCHA Admin →', 'emerge-mono-portfolio' ); ?></a>  <?php esc_html_e( 'to get your keys.', 'emerge-mono-portfolio' ); ?></div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'reCAPTCHA v3 Secret Key', 'emerge-mono-portfolio' ); ?></label>
                <input type="password" name="recaptcha_secret" value="<?php echo esc_attr(isset($opts['recaptcha_secret']) ? $opts['recaptcha_secret'] : ''); ?>" class="en-field-input" placeholder="<?php esc_attr_e( 'Enter your secret key', 'emerge-mono-portfolio' ); ?>">
            </div>
        </div>

        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Privacy Policy Consent', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Require a privacy policy consent checkbox before form submission.', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-group">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="contact_consent_enabled" value="1" <?php checked( isset($opts['contact_consent_enabled']) ? $opts['contact_consent_enabled'] : '0', '1' ); ?>>
                    <span><?php esc_html_e( 'Show consent checkbox', 'emerge-mono-portfolio' ); ?></span>
                </label>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Checkbox Text', 'emerge-mono-portfolio' ); ?></label>
                <input type="text" name="contact_consent_text" value="<?php echo esc_attr(isset($opts['contact_consent_text']) ? $opts['contact_consent_text'] : 'I agree to the Privacy Policy.'); ?>" class="en-field-input">
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Link Page (optional)', 'emerge-mono-portfolio' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:8px"><?php esc_html_e( 'Clicking the checkbox text navigates to this page.', 'emerge-mono-portfolio' ); ?></div>
                <?php
                $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'menu_order' ) );
                $consent_page_id = isset($opts['contact_consent_page_id']) ? (int)$opts['contact_consent_page_id'] : 0;
                ?>
                <select name="contact_consent_page_id" class="en-field-input" style="max-width:400px">
                    <option value=""><?php esc_html_e( '— No page selected —', 'emerge-mono-portfolio' ); ?></option>
                    <?php foreach ( $pages as $page ) : ?>
                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($consent_page_id, $page->ID); ?>>
                        <?php echo esc_html($page->post_title); ?> （/<?php echo esc_html($page->post_name); ?>）
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
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
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal"><?php esc_html_e( 'Content', 'emerge-mono-portfolio' ); ?></th>
                        <th style="padding:10px;text-align:left;color:rgba(255,255,255,.3);font-weight:normal"><?php esc_html_e( 'Actions', 'emerge-mono-portfolio' ); ?></th>
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
                                <summary style="cursor:pointer;color:rgba(255,255,255,.4);font-size:11px"><?php esc_html_e( 'View details', 'emerge-mono-portfolio' ); ?></summary>
                                <div style="margin-top:8px;padding:10px;background:rgba(255,255,255,.03);border-radius:4px">
                                    <?php foreach ( $fields_data as $fd ) : ?>
                                        <div style="margin-bottom:6px"><span style="color:rgba(255,255,255,.3);font-size:10px"><?php echo esc_html($fd['label']); ?>：</span><br><?php echo nl2br(esc_html($fd['value'])); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </td>
                        <td style="padding:10px;white-space:nowrap">
                            <?php if ($is_unread) : ?>
                                <a href="<?php echo esc_url( wp_nonce_url( '?page=emerge-mono-portfolio&tab=contact&mark_read=' . (int) $log->id, 'en_log_action', '_ennonce' ) ); ?>" style="font-size:10px;color:rgba(255,255,255,.35);margin-right:8px"><?php esc_html_e( 'Mark as read', 'emerge-mono-portfolio' ); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( wp_nonce_url( '?page=emerge-mono-portfolio&tab=contact&delete_log=' . (int) $log->id, 'en_log_action', '_ennonce' ) ); ?>" style="font-size:10px;color:rgba(255,100,100,.4)" onclick="return confirm('<?php echo esc_js( __( 'Delete this?', 'emerge-mono-portfolio' ) ); ?>')"><?php esc_html_e( 'Delete', 'emerge-mono-portfolio' ); ?></a>
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
                    <input type="text" name="cf_label[]" class="en-field-input" placeholder="<?php esc_attr_e( 'Your Name', 'emerge-mono-portfolio' ); ?>">
                </div>
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field Type', 'emerge-mono-portfolio' ); ?></div>
                    <select name="cf_type[]" class="en-field-input en-field-select" onchange="enToggleOptions(this)">
                        <option value="text"><?php esc_html_e( 'Text (single line)', 'emerge-mono-portfolio' ); ?></option>
                        <option value="email"><?php esc_html_e( 'Email', 'emerge-mono-portfolio' ); ?></option>
                        <option value="tel"><?php esc_html_e( 'Phone', 'emerge-mono-portfolio' ); ?></option>
                        <option value="textarea"><?php esc_html_e( 'Text (multi-line)', 'emerge-mono-portfolio' ); ?></option>
                        <option value="select"><?php esc_html_e( 'Select box', 'emerge-mono-portfolio' ); ?></option>
                        <option value="checkbox"><?php esc_html_e( 'Checkbox', 'emerge-mono-portfolio' ); ?></option>
                    </select>
                </div>
                <button type="button" class="en-remove-btn" style="margin-top:20px" onclick="this.closest('.en-contact-field-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono-portfolio' ); ?></button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px">
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Field ID (alphanumeric)', 'emerge-mono-portfolio' ); ?></div>
                    <input type="text" name="cf_key[]" class="en-field-input" placeholder="<?php esc_attr_e( 'e.g. inquiry_type', 'emerge-mono-portfolio' ); ?>">
                </div>
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Placeholder', 'emerge-mono-portfolio' ); ?></div>
                    <input type="text" name="cf_placeholder[]" class="en-field-input">
                </div>
                <div>
                    <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Required / Optional', 'emerge-mono-portfolio' ); ?></div>
                    <select name="cf_required[]" class="en-field-input en-field-select">
                        <option value="1"><?php esc_html_e( 'Required', 'emerge-mono-portfolio' ); ?></option>
                        <option value="0"><?php esc_html_e( 'Optional', 'emerge-mono-portfolio' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="en-cf-options" style="display:none">
                <div class="en-field-label" style="margin-bottom:4px"><?php esc_html_e( 'Options (one per line)', 'emerge-mono-portfolio' ); ?></div>
                <textarea name="cf_options[]" class="en-field-textarea" style="height:80px" placeholder="<?php esc_attr_e( 'Option A&#10;Option B', 'emerge-mono-portfolio' ); ?>"></textarea>
            </div>
        </div>
    </template>

    <?php
    wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_CONTACT_FIELDS_JS'
window.enToggleOptions = function(select) {
        var row = select.closest('.en-contact-field-row');
        var opts = row.querySelector('.en-cf-options');
        if (!opts) return;
        opts.style.display = (select.value === 'select' || select.value === 'checkbox') ? '' : 'none';
};
window.enAddContactField = function() {
        var list = document.getElementById('en-contact-fields-list');
        var tpl  = document.getElementById('en-cf-template');
        if (tpl) {
            var clone = tpl.content.cloneNode(true);
            list.appendChild(clone);
        }
};
EMONO_CONTACT_FIELDS_JS
    );
    ?>
    <?php
}
