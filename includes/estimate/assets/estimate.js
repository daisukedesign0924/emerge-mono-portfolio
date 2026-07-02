/* ── 見積もりシミュレーター JS ── */
(function(){
    'use strict';

    var services       = window.enEstData ? window.enEstData.services       : [];
    var paymentMethods = window.enEstData ? window.enEstData.paymentMethods  : [];
    var recaptchaKey   = window.enEstData ? window.enEstData.recaptchaKey   : '';

    // i18n（翻訳文字列。enEstData.i18n が無い場合は英語フォールバック）
    var T = (window.enEstData && window.enEstData.i18n) ? window.enEstData.i18n : {};
    T.perUnit    = T.perUnit    || 'each';
    T.currency   = T.currency   || '\u00a5';
    T.noFee      = T.noFee      || 'No fee';
    T.fee        = T.fee        || 'Fee';
    T.feeLabel   = T.feeLabel   || 'fee';
    T.method     = T.method     || 'Method';
    T.estTotal   = T.estTotal   || 'Estimated total';
    T.subtotal   = T.subtotal   || 'Subtotal';
    T.listSep    = T.listSep    || ', ';
    T.bullet     = T.bullet     || '\u2022 ';
    T.sendFailed = T.sendFailed || 'Failed to send.';
    T.commError  = T.commError  || 'A communication error occurred.';

    // 画像なしサービスを除外
    services = services.filter(function(s){ return s.image_url && s.image_url !== ''; });

    var selectedServices = [];
    var optionState      = {};
    var selectedPayment  = paymentMethods.length ? paymentMethods[0].id : null;

    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('en-estimate')) return;
        renderStep1();
        bindNav();
    });

    function goToStep(n) {
        for (var i = 1; i <= 5; i++) {
            var p = document.getElementById('en-est-panel-' + i);
            if (p) p.classList.toggle('active', i === n);
        }
        document.querySelectorAll('.en-est-step').forEach(function(item) {
            var s = parseInt(item.dataset.step);
            item.classList.remove('active', 'done');
            if (s === n) item.classList.add('active');
            else if (s < n) item.classList.add('done');
        });
        var el = document.getElementById('en-estimate');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function bindNav() {
        var el = document.getElementById('en-estimate');
        if (!el) return;
        el.addEventListener('click', function(e) {
            var t = e.target;
            if (t.id === 'en-est-to2')   { renderStep2(); goToStep(2); }
            if (t.id === 'en-est-back1')  { goToStep(1); }
            if (t.id === 'en-est-to3')    { renderStep3(); goToStep(3); }
            if (t.id === 'en-est-back2')  { renderStep2(); goToStep(2); }
            if (t.id === 'en-est-to4')    { renderStep4(); goToStep(4); }
            if (t.id === 'en-est-back3')  { renderStep3(); goToStep(3); }
            if (t.id === 'en-est-to5')    { renderStep5(); goToStep(5); }
            if (t.id === 'en-est-back4')  { goToStep(4); }
            if (t.id === 'en-est-reset')  { doReset(); }
        });
    }

    function doReset() {
        selectedServices = [];
        optionState = {};
        selectedPayment = paymentMethods.length ? paymentMethods[0].id : null;
        var form = document.getElementById('en-est-contact-form');
        if (form) { form.reset(); form.style.display = ''; }
        var wrap = document.querySelector('.en-est-submit-wrap');
        if (wrap) wrap.style.display = '';
        var complete = document.getElementById('en-est-complete');
        if (complete) complete.style.display = 'none';
        var nav5 = document.getElementById('en-est-nav-5');
        if (nav5) nav5.style.display = '';
        var submit = document.getElementById('en-est-submit');
        if (submit) submit.disabled = false;
        renderStep1();
        goToStep(1);
    }

    // STEP1
    function renderStep1() {
        var grid = document.getElementById('en-est-svc-grid');
        if (!grid) return;
        grid.innerHTML = '';
        services.forEach(function(svc, idx) {
            var card = document.createElement('button');
            card.type = 'button';
            card.className = 'en-est-svc-card' + (selectedServices.indexOf(idx) !== -1 ? ' active' : '');
            card.dataset.idx = idx;
            card.innerHTML = '<div class="en-est-svc-img"><img src="' + esc(svc.image_url) + '" alt="' + esc(svc.name) + '" loading="lazy"></div>'
                + '<div class="en-est-svc-name">' + esc(svc.name) + '</div>'
                + (svc.desc ? '<div class="en-est-svc-desc">' + esc(svc.desc) + '</div>' : '');
            card.addEventListener('click', function() {
                var i = selectedServices.indexOf(idx);
                if (i !== -1) { selectedServices.splice(i, 1); }
                else { selectedServices.push(idx); if (!optionState[idx]) initState(idx); }
                updateStep1UI();
            });
            grid.appendChild(card);
        });
        updateStep1UI();
    }

    function updateStep1UI() {
        document.querySelectorAll('.en-est-svc-card').forEach(function(c) {
            c.classList.toggle('active', selectedServices.indexOf(parseInt(c.dataset.idx)) !== -1);
        });
        var badge = document.getElementById('en-est-badge');
        if (badge) {
            badge.innerHTML = selectedServices.map(function(idx) {
                return '<span class="en-est-badge">' + esc(services[idx].name) + '</span>';
            }).join('');
        }
        var btn = document.getElementById('en-est-to2');
        if (btn) btn.disabled = !selectedServices.length;
    }

    function initState(idx) {
        var svc = services[idx];
        optionState[idx] = {};
        (svc.items || []).forEach(function(item, ii) {
            if (item.type === 'sel' && item.opts && item.opts.length) {
                optionState[idx]['sel_' + ii] = item.opts[0].value;
            } else if (item.type === 'chk') {
                (item.opts || []).forEach(function(opt, oi) {
                    optionState[idx]['chk_' + ii + '_' + oi] = false;
                });
            } else if (item.type === 'qty') {
                optionState[idx]['qty_' + ii] = parseInt(item.min) || 0;
            }
        });
    }

    // STEP2
    function renderStep2() {
        var container = document.getElementById('en-est-opt-panels');
        if (!container) return;
        container.innerHTML = '';
        selectedServices.forEach(function(idx, arrIdx) {
            var svc = services[idx];
            if (!optionState[idx]) initState(idx);
            if (arrIdx > 0) container.insertAdjacentHTML('beforeend', '<hr class="en-est-svc-sep">');
            var block = document.createElement('div');
            block.className = 'en-est-svc-block';
            block.dataset.idx = idx;
            block.innerHTML = '<div class="en-est-svc-block-header"><span class="en-est-svc-block-name">' + esc(svc.name) + '</span></div>' + renderOptions(svc, idx);
            container.appendChild(block);
        });
        bindOptionEvents(container);
    }

    function renderOptions(svc, idx) {
        var html = '';
        (svc.items || []).forEach(function(item, ii) {
            html += '<div class="en-est-opt-group"><div class="en-est-opt-group-label">' + esc(item.label) + '</div>';
            if (item.type === 'sel') {
                html += '<div class="en-est-choices">';
                (item.opts || []).forEach(function(opt) {
                    var sel = String(optionState[idx]['sel_' + ii]) === String(opt.value);
                    html += '<button type="button" class="en-est-choice' + (sel ? ' active' : '') + '" data-type="sel" data-idx="' + idx + '" data-ii="' + ii + '" data-val="' + opt.value + '">'
                        + '<div class="en-est-choice-left"><div class="en-est-choice-name">' + esc(opt.label) + '</div></div>'
                        + '<div class="en-est-choice-price">' + T.currency + '' + Number(opt.value).toLocaleString() + '</div></button>';
                });
                html += '</div>';
            } else if (item.type === 'chk') {
                html += '<div class="en-est-checks">';
                (item.opts || []).forEach(function(opt, oi) {
                    var chk = optionState[idx]['chk_' + ii + '_' + oi];
                    html += '<button type="button" class="en-est-check' + (chk ? ' active' : '') + '" data-type="chk" data-idx="' + idx + '" data-ii="' + ii + '" data-oi="' + oi + '" data-val="' + opt.value + '">'
                        + '<div class="en-est-check-left"><div class="en-est-check-name">' + esc(opt.label) + '</div></div>'
                        + '<div class="en-est-check-price">+' + T.currency + '' + Number(opt.value).toLocaleString() + '</div></button>';
                });
                html += '</div>';
            } else if (item.type === 'qty') {
                var val = optionState[idx]['qty_' + ii] || 0;
                var min = parseInt(item.min) || 0;
                html += '<div class="en-est-qty">'
                    + '<div class="en-est-qty-info"><div class="en-est-qty-name">' + esc(item.label) + '</div>'
                    + '<div class="en-est-qty-unit">' + T.perUnit + ' ' + T.currency + '' + Number(item.unit || 0).toLocaleString() + '</div></div>'
                    + '<div class="en-est-qty-ctrl">'
                    + '<button type="button" class="en-est-qty-btn" data-type="qty" data-idx="' + idx + '" data-ii="' + ii + '" data-unit="' + (item.unit||0) + '" data-min="' + min + '" data-dir="-1"' + (val <= min ? ' disabled' : '') + '>−</button>'
                    + '<span class="en-est-qty-val" id="en-est-qty-' + idx + '-' + ii + '">' + val + '</span>'
                    + '<button type="button" class="en-est-qty-btn" data-type="qty" data-idx="' + idx + '" data-ii="' + ii + '" data-unit="' + (item.unit||0) + '" data-min="' + min + '" data-dir="1">＋</button>'
                    + '</div></div>';
            }
            html += '</div>';
        });
        return html;
    }

    function bindOptionEvents(container) {
        container.querySelectorAll('.en-est-choice').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.dataset.idx);
                optionState[idx]['sel_' + btn.dataset.ii] = btn.dataset.val;
                refreshBlock(idx);
            });
        });
        container.querySelectorAll('.en-est-check').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.dataset.idx);
                var key = 'chk_' + btn.dataset.ii + '_' + btn.dataset.oi;
                optionState[idx][key] = !optionState[idx][key];
                refreshBlock(idx);
            });
        });
        container.querySelectorAll('.en-est-qty-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.dataset.idx);
                var ii  = btn.dataset.ii;
                var min = parseInt(btn.dataset.min) || 0;
                var cur = parseInt(optionState[idx]['qty_' + ii]) || min;
                optionState[idx]['qty_' + ii] = Math.max(min, cur + parseInt(btn.dataset.dir));
                refreshBlock(idx);
            });
        });
    }

    function refreshBlock(idx) {
        var block = document.querySelector('.en-est-svc-block[data-idx="' + idx + '"]');
        if (!block) return;
        var svc = services[idx];
        block.innerHTML = '<div class="en-est-svc-block-header"><span class="en-est-svc-block-name">' + esc(svc.name) + '</span></div>' + renderOptions(svc, idx);
        bindOptionEvents(document.getElementById('en-est-opt-panels'));
    }

    // STEP3
    function renderStep3() {
        var grid = document.getElementById('en-est-pay-grid');
        if (!grid) return;
        grid.innerHTML = '';
        paymentMethods.forEach(function(pm) {
            var card = document.createElement('div');
            card.className = 'en-est-pay-card' + (selectedPayment === pm.id ? ' active' : '');
            card.dataset.id = pm.id;
            var feeLabel = parseFloat(pm.fee) === 0 ? T.noFee : T.fee + ' ' + pm.fee + '%';
            card.innerHTML = '<div class="en-est-pay-left"><div class="en-est-pay-name">' + esc(pm.name) + '</div></div><div class="en-est-pay-fee">' + feeLabel + '</div>';
            card.addEventListener('click', function() {
                selectedPayment = pm.id;
                document.querySelectorAll('.en-est-pay-card').forEach(function(c) {
                    c.classList.toggle('active', c.dataset.id === pm.id);
                });
            });
            grid.appendChild(card);
        });
    }

    // STEP4
    function calcServiceTotal(idx) {
        var svc = services[idx];
        var state = optionState[idx] || {};
        var total = 0;
        (svc.items || []).forEach(function(item, ii) {
            if (item.type === 'sel')  total += parseInt(state['sel_' + ii]) || 0;
            else if (item.type === 'chk') {
                (item.opts || []).forEach(function(opt, oi) {
                    if (state['chk_' + ii + '_' + oi]) total += parseInt(opt.value) || 0;
                });
            } else if (item.type === 'qty') {
                total += (parseInt(state['qty_' + ii]) || 0) * (parseInt(item.unit) || 0);
            }
        });
        return total;
    }

    function renderStep4() {
        var body = document.getElementById('en-est-summary-body');
        if (!body) return;
        body.innerHTML = '';
        var serviceTotal = 0;
        selectedServices.forEach(function(idx) {
            var svc = services[idx];
            var state = optionState[idx] || {};
            var svcTotal = 0;
            var itemsHtml = '';
            (svc.items || []).forEach(function(item, ii) {
                if (item.type === 'sel') {
                    var val = parseInt(state['sel_' + ii]) || 0;
                    var opt = (item.opts || []).filter(function(o){ return String(o.value) === String(state['sel_' + ii]); })[0];
                    if (opt) { svcTotal += val; itemsHtml += '<div class="en-est-summary-item"><span>' + esc(item.label) + ': ' + esc(opt.label) + '</span><span>' + T.currency + '' + val.toLocaleString() + '</span></div>'; }
                } else if (item.type === 'chk') {
                    (item.opts || []).forEach(function(opt, oi) {
                        if (state['chk_' + ii + '_' + oi]) {
                            var v = parseInt(opt.value) || 0;
                            svcTotal += v;
                            itemsHtml += '<div class="en-est-summary-item"><span>' + esc(opt.label) + '</span><span>+' + T.currency + '' + v.toLocaleString() + '</span></div>';
                        }
                    });
                } else if (item.type === 'qty') {
                    var qty = parseInt(state['qty_' + ii]) || 0;
                    var p = qty * (parseInt(item.unit) || 0);
                    svcTotal += p;
                    itemsHtml += '<div class="en-est-summary-item"><span>' + esc(item.label) + ' × ' + qty + '</span><span>' + T.currency + '' + p.toLocaleString() + '</span></div>';
                }
            });
            serviceTotal += svcTotal;
            body.innerHTML += '<div class="en-est-summary-svc"><div class="en-est-summary-svc-name">' + esc(svc.name) + '</div>' + itemsHtml + '<div class="en-est-summary-item en-est-summary-subtotal"><span>' + T.subtotal + '</span><span>' + T.currency + '' + svcTotal.toLocaleString() + '</span></div></div>';
        });
        var pm = paymentMethods.filter(function(p){ return p.id === selectedPayment; })[0];
        var feeRow = document.getElementById('en-est-fee-row');
        var grandTotal = serviceTotal;
        if (pm && parseFloat(pm.fee) > 0) {
            var feeAmount = Math.round(serviceTotal * parseFloat(pm.fee) / 100);
            grandTotal = serviceTotal + feeAmount;
            if (feeRow) {
                feeRow.style.display = 'flex';
                document.getElementById('en-est-fee-label').textContent = pm.name + ' ' + T.feeLabel + ' (' + pm.fee + '%)';
                document.getElementById('en-est-fee-amount').textContent = '+' + T.currency + '' + feeAmount.toLocaleString();
            }
        } else {
            if (feeRow) feeRow.style.display = 'none';
        }
        var tp = document.getElementById('en-est-total-price');
        if (tp) tp.textContent = T.currency + '' + grandTotal.toLocaleString();
    }

    // STEP5
    function renderStep5() {
        var el = document.getElementById('en-est-form-summary');
        if (!el) return;
        var pm = paymentMethods.filter(function(p){ return p.id === selectedPayment; })[0];
        var totalEl = document.getElementById('en-est-total-price');
        var totalText = totalEl ? totalEl.textContent : '';
        var lines = selectedServices.map(function(idx) {
            var svc = services[idx];
            var state = optionState[idx] || {};
            var details = [];
            (svc.items || []).forEach(function(item, ii) {
                if (item.type === 'sel') {
                    var opt = (item.opts || []).filter(function(o){ return String(o.value) === String(state['sel_' + ii]); })[0];
                    if (opt) details.push(opt.label);
                } else if (item.type === 'chk') {
                    (item.opts || []).forEach(function(opt, oi) {
                        if (state['chk_' + ii + '_' + oi]) details.push(opt.label);
                    });
                } else if (item.type === 'qty') {
                    var qty = parseInt(state['qty_' + ii]) || 0;
                    if (qty > 0) details.push(item.label + ' × ' + qty);
                }
            });
            return '<div class="en-est-form-summary-line">' + T.bullet + esc(svc.name) + (details.length ? ' (' + details.map(esc).join(T.listSep) + ')' : '') + '</div>';
        }).join('');
        var feeText = pm && parseFloat(pm.fee) > 0 ? T.method + ': ' + pm.name + ' (+' + pm.fee + '%)' : T.method + ': ' + (pm ? pm.name : '');
        el.innerHTML = lines + '<div class="en-est-form-summary-line" style="opacity:.6;margin-top:4px">' + feeText + '</div>'
            + '<div class="en-est-form-summary-total">' + T.estTotal + ': ' + totalText + '</div>';
        var hidden = document.getElementById('en-est-hidden-summary');
        if (hidden) {
            var summaryLines = selectedServices.map(function(idx) {
                var svc = services[idx];
                return T.bullet + svc.name;
            }).join('\n');
            hidden.value = summaryLines + '\n' + feeText + '\n' + T.estTotal + ': ' + totalText;
        }
    }

    // Ajax送信
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('en-est-contact-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('en-est-submit');
            var status = document.getElementById('en-est-form-status');
            btn.disabled = true;
            function doSubmit() {
                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', { method: 'POST', body: new FormData(form) })
                    .then(function(r){ return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            form.style.display = 'none';
                            document.querySelector('.en-est-submit-wrap').style.display = 'none';
                            var complete = document.getElementById('en-est-complete');
                            if (complete) complete.style.display = 'block';
                            var nav5 = document.getElementById('en-est-nav-5');
                            if (nav5) nav5.style.display = 'none';
                        } else {
                            if (status) { status.textContent = T.sendFailed; status.style.color = '#e55'; }
                            btn.disabled = false;
                        }
                    })
                    .catch(function() {
                        if (status) { status.textContent = T.commError; status.style.color = '#e55'; }
                        btn.disabled = false;
                    });
            }
            if (recaptchaKey && typeof grecaptcha !== 'undefined') {
                grecaptcha.ready(function() {
                    grecaptcha.execute(recaptchaKey, {action:'contact'}).then(function(token) {
                        var input = document.createElement('input');
                        input.type = 'hidden'; input.name = 'recaptcha_token'; input.value = token;
                        form.appendChild(input);
                        doSubmit();
                    });
                });
            } else {
                doSubmit();
            }
        });
    });

    function esc(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

})();
