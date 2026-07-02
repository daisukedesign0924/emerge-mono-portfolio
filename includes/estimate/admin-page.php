<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function en_admin_tab_estimate( $opts ) {
    en_admin_notice();
    $services        = get_option( 'en_estimate_services', array() );
    $payment_methods = get_option( 'en_estimate_payment_methods', array() );
    $settings        = get_option( 'en_estimate_settings', array() );
    $note_text       = isset($settings['note_text']) ? $settings['note_text'] : '* The final amount will be provided after inquiry.';
    $btn_text        = isset($settings['btn_text'])  ? $settings['btn_text']  : 'Send inquiry with these details';
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_estimate','en_nonce'); ?>
        <input type="hidden" name="en_estimate_save" value="1">

        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        <!-- 左: サービス設定 -->
        <div>
            <div class="en-admin-section">
                <div class="en-admin-section-title"><?php esc_html_e( 'Service List', 'emerge-mono' ); ?></div>
                <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Add the services you offer. You can set estimate items for each service.', 'emerge-mono' ); ?></div>
                <div id="en-service-list">
                    <?php foreach ( $services as $si => $service ) : ?>
                    <div class="en-service-block" data-index="<?php echo $si; ?>">
                        <?php en_estimate_render_service_block($si, $service); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="en-add-btn" onclick="enAddService()" style="margin-top:12px"><?php esc_html_e( '+ Add Service', 'emerge-mono' ); ?></button>
            </div>

            <div class="en-admin-section">
                <div class="en-admin-section-title"><?php esc_html_e( 'Order Methods', 'emerge-mono' ); ?></div>
                <div class="en-field-desc" style="margin-bottom:16px"><?php esc_html_e( 'Set each order method name and surcharge rate (%). 0% shows as "no fee".', 'emerge-mono' ); ?></div>
                <div id="en-pm-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px">
                    <?php foreach ( $payment_methods as $pi => $pm ) : ?>
                    <div class="en-pm-row" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);padding:10px;border-radius:4px">
                        <input type="text" name="pm_name[]" value="<?php echo esc_attr($pm['name']); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'e.g. Bank transfer', 'emerge-mono' ); ?>" style="flex:1">
                        <input type="number" name="pm_fee[]" value="<?php echo esc_attr($pm['fee']); ?>" class="en-field-input" style="width:80px" step="0.1" min="0" placeholder="0">
                        <span style="font-size:12px;opacity:.5">%</span>
                        <button type="button" class="en-remove-btn" onclick="this.closest('.en-pm-row').remove()"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="en-add-btn" onclick="enAddPaymentMethod()"><?php esc_html_e( '+ Add Order Method', 'emerge-mono' ); ?></button>
            </div>

            <div class="en-admin-section">
                <div class="en-admin-section-title"><?php esc_html_e( 'General Settings', 'emerge-mono' ); ?></div>
                <div class="en-field-group">
                    <label class="en-field-label"><?php esc_html_e( 'Note Text', 'emerge-mono' ); ?></label>
                    <input type="text" name="estimate_note_text" value="<?php echo esc_attr($note_text); ?>" class="en-field-input">
                </div>
                <div class="en-field-group">
                    <label class="en-field-label"><?php esc_html_e( 'Contact Button Text', 'emerge-mono' ); ?></label>
                    <input type="text" name="estimate_btn_text" value="<?php echo esc_attr($btn_text); ?>" class="en-field-input">
                </div>
            </div>

            <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
        </div>

        <!-- 右: 操作ガイド -->
        <div style="position:sticky;top:32px;">
            <div style="font-size:11px;letter-spacing:.3em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:12px;"><?php esc_html_e( 'How to Use', 'emerge-mono' ); ?></div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);padding:16px;font-size:12px;line-height:1.9;color:rgba(255,255,255,.6);">
                <p style="margin-bottom:8px"><strong style="color:#fff">1. Add a service</strong><br>Enter the service name (e.g. VTuber illustration)</p>
                <p style="margin-bottom:8px"><strong style="color:#fff">2. Add items</strong><br>Add estimate items to each service.<br>
                    • <strong>Option</strong>: radio button style<br>
                    • <strong>Check</strong>: multi-select options<br>
                    • <strong>Quantity</strong>: specify count with +/-</p>
                <p style="margin-bottom:8px"><strong style="color:#fff">3. Add order methods</strong><br>Set payment service names and surcharge rates (%)</p>
                <p><strong style="color:#fff">4. Place on a page</strong><br>Paste <code>[emerge_mono_estimate]</code> into your Estimate page</p>
            </div>
        </div>

        </div>
    </form>

    <script>
    var enServiceCount = <?php echo count($services); ?>;
    var enEstI18n = {
        optionName:   <?php echo wp_json_encode( esc_attr__( 'Option name', 'emerge-mono' ) ); ?>,
        bankTransfer: <?php echo wp_json_encode( esc_attr__( 'e.g. Bank transfer', 'emerge-mono' ) ); ?>,
        del:          <?php echo wp_json_encode( __( 'Delete', 'emerge-mono' ) ); ?>,
        serviceName:  <?php echo wp_json_encode( esc_attr__( 'Service name (e.g. VTuber illustration)', 'emerge-mono' ) ); ?>,
        descOptional: <?php echo wp_json_encode( esc_attr__( 'Description (optional)', 'emerge-mono' ) ); ?>,
        optLabel:     <?php echo wp_json_encode( __( 'Option', 'emerge-mono' ) ); ?>,
        chkLabel:     <?php echo wp_json_encode( __( 'Check', 'emerge-mono' ) ); ?>,
        qtyLabel:     <?php echo wp_json_encode( __( 'Quantity', 'emerge-mono' ) ); ?>,
        itemName:     <?php echo wp_json_encode( esc_attr__( 'Item name (e.g. Number of characters)', 'emerge-mono' ) ); ?>,
        unitPrice:    <?php echo wp_json_encode( __( 'Unit price ¥', 'emerge-mono' ) ); ?>,
        minQty:       <?php echo wp_json_encode( __( 'Min qty', 'emerge-mono' ) ); ?>,
        addOption:    <?php echo wp_json_encode( __( '+ Add option', 'emerge-mono' ) ); ?>,
        plusOption:   <?php echo wp_json_encode( __( '+ Option', 'emerge-mono' ) ); ?>,
        plusCheck:    <?php echo wp_json_encode( __( '+ Check', 'emerge-mono' ) ); ?>,
        plusQuantity: <?php echo wp_json_encode( __( '+ Quantity', 'emerge-mono' ) ); ?>
    };

    window.enAddService = function() {
        var idx = enServiceCount++;
        var div = document.createElement('div');
        div.className = 'en-service-block';
        div.setAttribute('data-index', idx);
        div.innerHTML = enServiceBlockHTML(idx, '', '', []);
        document.getElementById('en-service-list').appendChild(div);
    };

    window.enRemoveService = function(btn) {
        btn.closest('.en-service-block').remove();
    };

    window.enAddItem = function(btn, type) {
        var block = btn.closest('.en-service-block');
        var idx   = block.getAttribute('data-index');
        var list  = block.querySelector('.en-item-list');
        var iIdx  = list.children.length;
        var div   = document.createElement('div');
        div.className = 'en-item-block';
        div.innerHTML = enItemBlockHTML(idx, iIdx, type);
        list.appendChild(div);
    };

    window.enRemoveItem = function(btn) {
        btn.closest('.en-item-block').remove();
    };

    window.enAddOpt = function(btn) {
        var item = btn.closest('.en-item-block');
        var list = item.querySelector('.en-opt-list');
        var idx  = item.closest('.en-service-block').getAttribute('data-index');
        var iIdx = Array.from(item.closest('.en-item-list').children).indexOf(item);
        var oIdx = list.children.length;
        var row  = document.createElement('div');
        row.className = 'en-opt-row';
        row.style.cssText = 'display:flex;gap:6px;align-items:center;margin-bottom:4px';
        row.innerHTML = '<input type="text" name="service_items['+idx+']['+iIdx+'][opts]['+oIdx+'][label]" class="en-field-input" placeholder="' + enEstI18n.optionName + '" style="flex:1"><span style="font-size:11px;opacity:.5">¥</span><input type="number" name="service_items['+idx+']['+iIdx+'][opts]['+oIdx+'][value]" class="en-field-input" style="width:90px" value="0"><button type="button" class="en-remove-btn" style="padding:4px 8px" onclick="this.closest(\'.en-opt-row\').remove()">×</button>';
        list.appendChild(row);
    };

    window.enAddPaymentMethod = function() {
        var list = document.getElementById('en-pm-list');
        var div  = document.createElement('div');
        div.className = 'en-pm-row';
        div.style.cssText = 'display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);padding:10px;border-radius:4px';
        div.innerHTML = '<input type="text" name="pm_name[]" class="en-field-input" placeholder="' + enEstI18n.bankTransfer + '" style="flex:1"><input type="number" name="pm_fee[]" class="en-field-input" style="width:80px" step="0.1" min="0" value="0"><span style="font-size:12px;opacity:.5">%</span><button type="button" class="en-remove-btn" onclick="this.closest(\'.en-pm-row\').remove()">' + enEstI18n.del + '</button>';
        list.appendChild(div);
    };

    function enServiceBlockHTML(idx, name, desc, items) {
        return '<div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:6px;padding:16px;margin-bottom:12px">'
            +'<div style="display:flex;gap:8px;margin-bottom:12px;align-items:center">'
            +'<input type="text" name="service_name['+idx+']" value="'+escHtml(name)+'" class="en-field-input" placeholder="' + enEstI18n.serviceName + '" style="flex:1">'
            +'<button type="button" class="en-remove-btn" onclick="enRemoveService(this)">' + enEstI18n.del + '</button>'
            +'</div>'
            +'<div style="margin-bottom:8px">'
            +'<input type="text" name="service_desc['+idx+']" value="'+escHtml(desc)+'" class="en-field-input" placeholder="' + enEstI18n.descOptional + '">'
            +'</div>'
            +'<div class="en-item-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:8px"></div>'
            +'<div style="display:flex;gap:6px">'
            +'<button type="button" class="en-add-btn" onclick="enAddItem(this,\'sel\')">' + enEstI18n.plusOption + '</button>'
            +'<button type="button" class="en-add-btn" onclick="enAddItem(this,\'chk\')">' + enEstI18n.plusCheck + '</button>'
            +'<button type="button" class="en-add-btn" onclick="enAddItem(this,\'qty\')">' + enEstI18n.plusQuantity + '</button>'
            +'</div>'
            +'</div>';
    }

    function enItemBlockHTML(si, ii, type) {
        var typeLabel = type==='sel' ? enEstI18n.optLabel : type==='chk' ? enEstI18n.chkLabel : enEstI18n.qtyLabel;
        var html = '<div style="background:rgba(255,255,255,.06);border-radius:4px;padding:10px">'
            +'<input type="hidden" name="service_items['+si+']['+ii+'][type]" value="'+type+'">'
            +'<div style="display:flex;gap:6px;align-items:center;margin-bottom:8px">'
            +'<span style="font-size:10px;letter-spacing:.2em;opacity:.4;white-space:nowrap">'+typeLabel+'</span>'
            +'<input type="text" name="service_items['+si+']['+ii+'][label]" class="en-field-input" placeholder="' + enEstI18n.itemName + '" style="flex:1">'
            +'<button type="button" class="en-remove-btn" style="padding:4px 8px" onclick="enRemoveItem(this)">×</button>'
            +'</div>';
        if (type === 'qty') {
            html += '<div style="display:flex;gap:6px;align-items:center">'
                +'<span style="font-size:11px;opacity:.5">' + enEstI18n.unitPrice + '</span>'
                +'<input type="number" name="service_items['+si+']['+ii+'][unit]" class="en-field-input" style="width:100px" value="0">'
                +'<span style="font-size:11px;opacity:.5">' + enEstI18n.minQty + '</span>'
                +'<input type="number" name="service_items['+si+']['+ii+'][min]" class="en-field-input" style="width:60px" value="0">'
                +'</div>';
        } else {
            html += '<div class="en-opt-list" style="margin-bottom:6px"></div>'
                +'<button type="button" class="en-add-btn" style="font-size:11px;padding:3px 8px" onclick="enAddOpt(this)">' + enEstI18n.addOption + '</button>';
        }
        html += '</div>';
        return html;
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    </script>
    <?php
}

function en_estimate_render_service_block($si, $service) {
    $name  = isset($service['name']) ? $service['name'] : '';
    $desc  = isset($service['desc']) ? $service['desc'] : '';
    $items = isset($service['items']) ? $service['items'] : array();
    ?>
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:6px;padding:16px;margin-bottom:12px">
        <div style="display:flex;gap:8px;margin-bottom:12px;align-items:center">
            <input type="text" name="service_name[<?php echo $si; ?>]" value="<?php echo esc_attr($name); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'Service name (e.g. VTuber illustration)', 'emerge-mono' ); ?>" style="flex:1">
            <button type="button" class="en-remove-btn" onclick="enRemoveService(this)"><?php esc_html_e( 'Delete', 'emerge-mono' ); ?></button>
        </div>
        <div style="margin-bottom:8px">
            <input type="text" name="service_desc[<?php echo $si; ?>]" value="<?php echo esc_attr($desc); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'Description (optional)', 'emerge-mono' ); ?>">
        </div>
        <div class="en-item-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:8px">
            <?php foreach ( $items as $ii => $item ) :
                $type  = isset($item['type'])  ? $item['type']  : 'sel';
                $label = isset($item['label']) ? $item['label'] : '';
                $type_label = $type==='sel' ? __( 'Option', 'emerge-mono' ) : ($type==='chk' ? __( 'Check', 'emerge-mono' ) : __( 'Quantity', 'emerge-mono' ));
            ?>
            <div class="en-item-block" style="background:rgba(255,255,255,.06);border-radius:4px;padding:10px">
                <input type="hidden" name="service_items[<?php echo $si; ?>][<?php echo $ii; ?>][type]" value="<?php echo esc_attr($type); ?>">
                <div style="display:flex;gap:6px;align-items:center;margin-bottom:8px">
                    <span style="font-size:10px;letter-spacing:.2em;opacity:.4;white-space:nowrap"><?php echo esc_html($type_label); ?></span>
                    <input type="text" name="service_items[<?php echo $si; ?>][<?php echo $ii; ?>][label]" value="<?php echo esc_attr($label); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'Item name', 'emerge-mono' ); ?>" style="flex:1">
                    <button type="button" class="en-remove-btn" style="padding:4px 8px" onclick="enRemoveItem(this)">×</button>
                </div>
                <?php if ( $type === 'qty' ) : ?>
                <div style="display:flex;gap:6px;align-items:center">
                    <span style="font-size:11px;opacity:.5"><?php esc_html_e( 'Unit price ¥', 'emerge-mono' ); ?></span>
                    <input type="number" name="service_items[<?php echo $si; ?>][<?php echo $ii; ?>][unit]" value="<?php echo esc_attr($item['unit'] ?? 0); ?>" class="en-field-input" style="width:100px">
                    <span style="font-size:11px;opacity:.5"><?php esc_html_e( 'Min qty', 'emerge-mono' ); ?></span>
                    <input type="number" name="service_items[<?php echo $si; ?>][<?php echo $ii; ?>][min]" value="<?php echo esc_attr($item['min'] ?? 0); ?>" class="en-field-input" style="width:60px">
                </div>
                <?php else : ?>
                <div class="en-opt-list" style="margin-bottom:6px">
                    <?php foreach ( ($item['opts'] ?? array()) as $oi => $opt ) : ?>
                    <div class="en-opt-row" style="display:flex;gap:6px;align-items:center;margin-bottom:4px">
                        <input type="text" name="service_items[<?php echo $si; ?>][<?php echo $ii; ?>][opts][<?php echo $oi; ?>][label]" value="<?php echo esc_attr($opt['label'] ?? ''); ?>" class="en-field-input" placeholder="<?php echo esc_attr__( 'Option name', 'emerge-mono' ); ?>" style="flex:1">
                        <span style="font-size:11px;opacity:.5">¥</span>
                        <input type="number" name="service_items[<?php echo $si; ?>][<?php echo $ii; ?>][opts][<?php echo $oi; ?>][value]" value="<?php echo esc_attr($opt['value'] ?? 0); ?>" class="en-field-input" style="width:90px">
                        <button type="button" class="en-remove-btn" style="padding:4px 8px" onclick="this.closest('.en-opt-row').remove()">×</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="en-add-btn" style="font-size:11px;padding:3px 8px" onclick="enAddOpt(this)"><?php esc_html_e( '+ Add option', 'emerge-mono' ); ?></button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:6px">
            <button type="button" class="en-add-btn" onclick="enAddItem(this,'sel')"><?php esc_html_e( '+ Option', 'emerge-mono' ); ?></button>
            <button type="button" class="en-add-btn" onclick="enAddItem(this,'chk')"><?php esc_html_e( '+ Check', 'emerge-mono' ); ?></button>
            <button type="button" class="en-add-btn" onclick="enAddItem(this,'qty')"><?php esc_html_e( '+ Quantity', 'emerge-mono' ); ?></button>
        </div>
    </div>
    <?php
}
