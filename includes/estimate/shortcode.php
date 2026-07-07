<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'emerge_mono_estimate', 'emono_shortcode_estimate' );
function emono_shortcode_estimate( $atts ) {
    $services        = get_option( 'en_estimate_services',        array() );
    $payment_methods = get_option( 'en_estimate_payment_methods', array() );
    $settings        = get_option( 'en_estimate_settings',        array() );
    $note_text       = isset($settings['note_text']) ? $settings['note_text'] : __( '* The final amount will be provided after inquiry.', 'emerge-mono-portfolio' );

    // 支払い方法にIDを付与
    $pms = array();
    foreach ( $payment_methods as $pi => $pm ) {
        $pms[] = array(
            'id'   => 'pm_' . $pi,
            'name' => isset($pm['name']) ? $pm['name'] : '',
            'fee'  => isset($pm['fee'])  ? $pm['fee']  : 0,
        );
    }

    // サービスに画像URLを付与
    $svcs = array();
    foreach ( $services as $svc ) {
        $img_url = '';
        if ( ! empty($svc['image_id']) ) {
            $img_url = wp_get_attachment_image_url( (int)$svc['image_id'], 'medium' );
            if ( ! $img_url ) $img_url = '';
        }
        $svc['image_url'] = $img_url;
        $svcs[] = $svc;
    }

    // コンタクトフォーム設定を共有
    $contact_fields  = get_option( 'en_contact_fields', emono_default_contact_fields() );
    $consent_enabled = emono_opt('contact_consent_enabled', '0');
    $consent_text    = emono_opt('contact_consent_text', __( 'I agree to the Privacy Policy.', 'emerge-mono-portfolio' ));
    $consent_page_id = (int)emono_opt('contact_consent_page_id', 0);
    $consent_url     = $consent_page_id ? get_permalink($consent_page_id) : '';
    $btn_text        = emono_opt('contact_btn_text', __( 'Send', 'emerge-mono-portfolio' ));
    $recaptcha_key   = emono_opt('recaptcha_site_key', '');
    $success_msg     = emono_opt('contact_success', __( 'Your message has been sent.', 'emerge-mono-portfolio' ));

    ob_start(); ?>
    <div class="en-estimate" id="en-estimate">
        <?php if ( empty($svcs) ) : ?>
        <div class="en-estimate-empty"><?php esc_html_e( 'The estimate simulator has not been set up.', 'emerge-mono-portfolio' ); ?></div>
        <?php else : ?>

        <!-- ステップバー -->
        <div class="en-est-steps">
            <div class="en-est-step active" data-step="1"><span class="en-est-step-num">1</span><span class="en-est-step-label"><?php esc_html_e( 'Service', 'emerge-mono-portfolio' ); ?></span></div>
            <div class="en-est-step-line"></div>
            <div class="en-est-step" data-step="2"><span class="en-est-step-num">2</span><span class="en-est-step-label"><?php esc_html_e( 'Options', 'emerge-mono-portfolio' ); ?></span></div>
            <div class="en-est-step-line"></div>
            <div class="en-est-step" data-step="3"><span class="en-est-step-num">3</span><span class="en-est-step-label"><?php esc_html_e( 'How to Order', 'emerge-mono-portfolio' ); ?></span></div>
            <div class="en-est-step-line"></div>
            <div class="en-est-step" data-step="4"><span class="en-est-step-num">4</span><span class="en-est-step-label"><?php esc_html_e( 'Estimate', 'emerge-mono-portfolio' ); ?></span></div>
            <div class="en-est-step-line"></div>
            <div class="en-est-step" data-step="5"><span class="en-est-step-num">5</span><span class="en-est-step-label"><?php esc_html_e( 'Contact', 'emerge-mono-portfolio' ); ?></span></div>
        </div>

        <!-- STEP1 -->
        <div class="en-est-panel active" id="en-est-panel-1">
            <div class="en-est-panel-heading"><h3>Service</h3><p><?php esc_html_e( 'Select the services you want (multiple allowed)', 'emerge-mono-portfolio' ); ?></p></div>
            <div class="en-est-service-grid" id="en-est-svc-grid"></div>
            <div class="en-est-selected-badge" id="en-est-badge"></div>
            <div class="en-est-nav">
                <button type="button" class="en-est-btn en-est-btn-next" id="en-est-to2" disabled>Next → <?php esc_html_e( 'Choose options', 'emerge-mono-portfolio' ); ?></button>
            </div>
        </div>

        <!-- STEP2 -->
        <div class="en-est-panel" id="en-est-panel-2">
            <div class="en-est-panel-heading"><h3>Options</h3><p><?php esc_html_e( 'Configure the details for each service', 'emerge-mono-portfolio' ); ?></p></div>
            <div id="en-est-opt-panels"></div>
            <div class="en-est-nav">
                <button type="button" class="en-est-btn en-est-btn-back" id="en-est-back1">← Back</button>
                <button type="button" class="en-est-btn en-est-btn-next" id="en-est-to3">Next → <?php esc_html_e( 'Choose order method', 'emerge-mono-portfolio' ); ?></button>
            </div>
        </div>

        <!-- STEP3 -->
        <div class="en-est-panel" id="en-est-panel-3">
            <div class="en-est-panel-heading"><h3>How to Order</h3><p><?php esc_html_e( 'Choose how to order', 'emerge-mono-portfolio' ); ?></p></div>
            <div class="en-est-pay-grid" id="en-est-pay-grid"></div>
            <div class="en-est-nav">
                <button type="button" class="en-est-btn en-est-btn-back" id="en-est-back2">← Back</button>
                <button type="button" class="en-est-btn en-est-btn-next" id="en-est-to4">Next → <?php esc_html_e( 'Review estimate', 'emerge-mono-portfolio' ); ?></button>
            </div>
        </div>

        <!-- STEP4 -->
        <div class="en-est-panel" id="en-est-panel-4">
            <div class="en-est-panel-heading"><h3>Estimate</h3><p><?php esc_html_e( 'Review the details and proceed to contact', 'emerge-mono-portfolio' ); ?></p></div>
            <div class="en-est-summary">
                <div class="en-est-summary-header"><?php esc_html_e( 'Breakdown', 'emerge-mono-portfolio' ); ?></div>
                <div id="en-est-summary-body"></div>
                <div class="en-est-summary-fee" id="en-est-fee-row" style="display:none">
                    <span id="en-est-fee-label"></span>
                    <span id="en-est-fee-amount"></span>
                </div>
                <div class="en-est-summary-total">
                    <span class="en-est-total-label">Total</span>
                    <span class="en-est-total-price" id="en-est-total-price">¥0</span>
                </div>
            </div>
            <div class="en-est-note"><?php echo esc_html($note_text); ?></div>
            <div class="en-est-nav">
                <button type="button" class="en-est-btn en-est-btn-back" id="en-est-back3">← Back</button>
                <button type="button" class="en-est-btn en-est-btn-next" id="en-est-to5">Next → <?php esc_html_e( 'Contact', 'emerge-mono-portfolio' ); ?></button>
            </div>
        </div>

        <!-- STEP5 -->
        <div class="en-est-panel" id="en-est-panel-5">
            <div class="en-est-panel-heading"><h3>Contact</h3><p><?php esc_html_e( 'Send an inquiry with your estimate details', 'emerge-mono-portfolio' ); ?></p></div>
            <div class="en-est-form-intro">
                <div class="en-est-form-intro-label"><?php esc_html_e( 'Estimate Details', 'emerge-mono-portfolio' ); ?></div>
                <div id="en-est-form-summary"></div>
            </div>
            <form class="en-est-contact-form" id="en-est-contact-form">
                <input type="hidden" name="action" value="en_send_contact">
                <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce('en_nonce') ); ?>">
                <input type="hidden" name="en_field_estimate_summary" id="en-est-hidden-summary">
                <div style="position:absolute;left:-9999px;opacity:0;pointer-events:none" aria-hidden="true">
                    <input type="text" name="en_hp_field" tabindex="-1" autocomplete="off">
                </div>
                <?php foreach ( $contact_fields as $field ) :
                    $key      = sanitize_key( isset($field['key']) ? $field['key'] : '' );
                    $label    = isset($field['label']) ? $field['label'] : '';
                    $type     = isset($field['type'])  ? $field['type']  : 'text';
                    $required = ! empty( $field['required'] );
                    $pholder  = isset($field['placeholder']) ? $field['placeholder'] : '';
                    $options  = isset($field['options']) ? $field['options'] : array();
                    $req_attr = $required ? 'required' : '';
                    $req_mark = $required ? '<span class="en-required">*</span>' : '<span class="en-optional">' . esc_html__( 'Optional', 'emerge-mono-portfolio' ) . '</span>';
                ?>
                <div class="en-est-form-field">
                    <label class="en-est-form-label"><?php echo esc_html($label); ?> <?php echo wp_kses( $req_mark, array( 'span' => array( 'class' => array() ) ) ); ?></label>
                    <?php if ( $type === 'textarea' ) : ?>
                        <textarea name="en_field_<?php echo esc_attr($key); ?>" class="en-est-form-textarea" placeholder="<?php echo esc_attr($pholder); ?>" <?php echo esc_attr( $req_attr ); ?>></textarea>
                    <?php elseif ( $type === 'select' ) : ?>
                        <select name="en_field_<?php echo esc_attr($key); ?>" class="en-est-form-input" <?php echo esc_attr( $req_attr ); ?>>
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono-portfolio' ); ?></option>
                            <?php foreach ( $options as $opt ) : ?>
                                <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="<?php echo esc_attr($type); ?>" name="en_field_<?php echo esc_attr($key); ?>" class="en-est-form-input" placeholder="<?php echo esc_attr($pholder); ?>" <?php echo esc_attr( $req_attr ); ?>>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if ( $consent_enabled === '1' ) : ?>
                <div class="en-est-form-field">
                    <label class="en-est-form-consent-label">
                        <input type="checkbox" name="en_consent" required>
                        <span><?php if ( $consent_url ) : ?><a href="<?php echo esc_url($consent_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($consent_text); ?></a><?php else : ?><?php echo esc_html($consent_text); ?><?php endif; ?> <span class="en-required">*</span></span>
                    </label>
                </div>
                <?php endif; ?>
                <div class="en-est-submit-wrap">
                    <button type="submit" class="en-est-submit-btn" id="en-est-submit"><?php echo esc_html($btn_text); ?></button>
                    <div class="en-est-form-status" id="en-est-form-status"></div>
                </div>
            </form>
            <div class="en-est-complete" id="en-est-complete" style="display:none">
                <div class="en-est-complete-icon">✓</div>
                <div class="en-est-complete-title"><?php echo esc_html($success_msg); ?></div>
            </div>
            <div class="en-est-nav" id="en-est-nav-5">
                <button type="button" class="en-est-btn en-est-btn-back" id="en-est-back4">← Back</button>
            </div>
            <button type="button" class="en-est-reset" id="en-est-reset"><?php esc_html_e( 'Start over', 'emerge-mono-portfolio' ); ?></button>
        </div>

        <?php endif; ?>
    </div>

    <?php
    // Pass data to the estimate script via wp_add_inline_script instead of raw script output.
    $en_est_data = wp_json_encode( array(
        'services'       => $svcs,
        'paymentMethods' => $pms,
        'noteText'       => $note_text,
        'recaptchaKey'   => $recaptcha_key,
        'i18n'           => array(
            'perUnit'       => __( 'each', 'emerge-mono-portfolio' ),
            'perUnitPrefix' => __( 'each', 'emerge-mono-portfolio' ),
            'currency'      => __( '¥', 'emerge-mono-portfolio' ),
            'noFee'         => __( 'No fee', 'emerge-mono-portfolio' ),
            'fee'           => __( 'Fee', 'emerge-mono-portfolio' ),
            'feeLabel'      => __( 'fee', 'emerge-mono-portfolio' ),
            'method'        => __( 'Method', 'emerge-mono-portfolio' ),
            'estTotal'      => __( 'Estimated total', 'emerge-mono-portfolio' ),
            'subtotal'      => __( 'Subtotal', 'emerge-mono-portfolio' ),
            'listSep'       => __( ', ', 'emerge-mono-portfolio' ),
            'bullet'        => __( '• ', 'emerge-mono-portfolio' ),
            'sendFailed'    => __( 'Failed to send.', 'emerge-mono-portfolio' ),
            'commError'     => __( 'A communication error occurred.', 'emerge-mono-portfolio' ),
        ),
    ) );
    wp_add_inline_script( 'en-estimate', 'window.enEstData = ' . $en_est_data . ';', 'before' );

    return ob_get_clean();
}
