/**
 * Emerge Mono - Estimate simulator admin UI.
 * Reads initial data (service count + i18n labels) from window.enEstAdminData,
 * provided via wp_localize_script.
 */
( function() {
    var data = window.enEstAdminData || {};
    var enServiceCount = data.serviceCount || 0;
    var enEstI18n = data.i18n || {};

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
} )();
