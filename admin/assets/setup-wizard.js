(function(){
    document.body.classList.add('en-wiz-active');

    var settings = window.emonoWizardSettings || {};
    var i18n = settings.i18n || {};
    var logoLabels = settings.logoLabels || {};
    var logoHints = settings.logoHints || {};
    var ajaxurl = settings.ajaxUrl || '';
    var nonce = settings.createNonce || '';
    var doneNonce = settings.doneNonce || '';
    var saveNonce = settings.saveNonce || '';
    var dashUrl = settings.dashUrl || '';

    var checks = function(){ return Array.prototype.slice.call(document.querySelectorAll('.en-wiz-check:not([disabled])')); };
    var msg = document.getElementById('en-wiz-msg');

    var panes = Array.prototype.slice.call(document.querySelectorAll('.en-wiz-pane'));
    var stepEls = Array.prototype.slice.call(document.querySelectorAll('.en-wiz-step'));
    var total = panes.length;
    var cur = 0;
    var backBtn = document.getElementById('en-wiz-back');
    var nextBtn = document.getElementById('en-wiz-next');
    var finishBtn = document.getElementById('en-wiz-finish');

    function render(){
        panes.forEach(function(p, i){ p.classList.toggle('active', i === cur); });
        stepEls.forEach(function(s, i){
            s.classList.toggle('active', i === cur);
            s.classList.toggle('done', i < cur);
        });
        backBtn.style.display = cur === 0 ? 'none' : '';
        var last = (cur === total - 1);
        nextBtn.style.display = last ? 'none' : '';
        finishBtn.style.display = last ? '' : 'none';
        msg.textContent = '';
    }

    nextBtn.addEventListener('click', function(){
        if (cur >= total - 1) return;
        nextBtn.disabled = true;
        msg.textContent = i18n.saving;
        saveSettings().then(function(){
            nextBtn.disabled = false;
            msg.textContent = i18n.saved;
            cur++;
            render();
        });
    });
    backBtn.addEventListener('click', function(){ if (cur > 0) { cur--; render(); } });
    render();

    document.getElementById('en-wiz-select-all').addEventListener('click', function(){
        checks().forEach(function(c){ c.checked = true; });
    });
    document.getElementById('en-wiz-clear').addEventListener('click', function(){
        checks().forEach(function(c){ c.checked = false; });
    });
    document.getElementById('en-wiz-select-recommended').addEventListener('click', function(){
        checks().forEach(function(c){ c.checked = (c.closest('.en-wiz-item').querySelector('.en-wiz-tag-rec') !== null); });
    });

    var slotDark = document.querySelector('.en-wiz-logo-slot[data-slot="dark"]');
    var slotLight = document.querySelector('.en-wiz-logo-slot[data-slot="light"]');
    var labelDark = document.getElementById('en-wiz-logo-label-dark');
    var labelLight = document.getElementById('en-wiz-logo-label-light');
    var logoHint = document.getElementById('en-wiz-logo-hint');

    function syncLogoSlots(){
        var modeEl = document.querySelector('input[name="en-wiz-mode"]:checked');
        var mode = modeEl ? modeEl.value : 'dark';
        if (mode === 'dark') {
            slotDark.classList.remove('is-hidden');
            slotLight.classList.add('is-hidden');
            labelDark.textContent = logoLabels.single;
            logoHint.textContent = logoHints.dark;
        } else if (mode === 'light') {
            slotDark.classList.add('is-hidden');
            slotLight.classList.remove('is-hidden');
            labelLight.textContent = logoLabels.single;
            logoHint.textContent = logoHints.light;
        } else {
            slotDark.classList.remove('is-hidden');
            slotLight.classList.remove('is-hidden');
            labelDark.textContent = logoLabels.dark;
            labelLight.textContent = logoLabels.light;
            logoHint.textContent = logoHints.auto;
        }
    }

    Array.prototype.slice.call(document.querySelectorAll('.en-wiz-mode')).forEach(function(m){
        m.addEventListener('click', function(){
            document.querySelectorAll('.en-wiz-mode').forEach(function(x){ x.classList.remove('active'); });
            m.classList.add('active');
            m.querySelector('input').checked = true;
            syncLogoSlots();
        });
    });
    syncLogoSlots();

    var logoEls = {
        dark: { url: document.getElementById('en-wiz-logo-url'), img: document.getElementById('en-wiz-logo-img-dark'), prev: document.getElementById('en-wiz-logo-preview-dark') },
        light: { url: document.getElementById('en-wiz-logo-url-light'), img: document.getElementById('en-wiz-logo-img-light'), prev: document.getElementById('en-wiz-logo-preview-light') }
    };
    var mediaFrames = {};
    function bindMediaButtons(){
        Array.prototype.slice.call(document.querySelectorAll('.en-wiz-media-btn')).forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                if (typeof wp === 'undefined' || ! wp.media) {
                    msg.textContent = i18n.mediaUnavailable;
                    return;
                }
                var target = btn.dataset.target;
                if (! mediaFrames[target]) {
                    var frame = wp.media({
                        title: i18n.mediaTitle,
                        button: { text: i18n.mediaBtn },
                        library: { type: 'image' },
                        multiple: false
                    });
                    frame.on('open', function(){
                        var ov = document.querySelector('.en-wiz-overlay');
                        if (ov) ov.style.background = 'transparent';
                    });
                    frame.on('close', function(){
                        var ov = document.querySelector('.en-wiz-overlay');
                        if (ov) ov.style.background = '';
                    });
                    frame.on('select', function(){
                        var att = frame.state().get('selection').first().toJSON();
                        var el = logoEls[target];
                        el.url.value = att.url;
                        el.img.src = att.url;
                        el.prev.style.display = '';
                        var rm = document.querySelector('.en-wiz-logo-remove[data-target="' + target + '"]');
                        if (rm) rm.style.display = '';
                        msg.textContent = '';
                    });
                    mediaFrames[target] = frame;
                }
                mediaFrames[target].open();
            });
        });
    }

    if (typeof wp !== 'undefined' && wp.media) {
        bindMediaButtons();
    } else {
        var mediaWait = 0;
        var mediaTimer = setInterval(function(){
            mediaWait++;
            if (typeof wp !== 'undefined' && wp.media) {
                clearInterval(mediaTimer);
                bindMediaButtons();
            } else if (mediaWait > 40) {
                clearInterval(mediaTimer);
                bindMediaButtons();
            }
        }, 250);
    }

    Array.prototype.slice.call(document.querySelectorAll('.en-wiz-logo-remove')).forEach(function(rm){
        rm.addEventListener('click', function(){
            var t = rm.dataset.target;
            var el = logoEls[t];
            el.url.value = '';
            el.img.src = '';
            el.prev.style.display = 'none';
            rm.style.display = 'none';
        });
    });

    function markDone(){
        var d = new FormData();
        d.append('action', 'en_wizard_done');
        d.append('nonce', doneNonce);
        return fetch(ajaxurl, { method:'POST', body:d }).catch(function(){});
    }

    document.getElementById('en-wiz-skip-all').addEventListener('click', function(e){
        e.preventDefault();
        var href = this.getAttribute('href');
        saveSettings().then(function(){
            markDone().then(function(){ location.href = href; });
        });
    });

    function saveSettings(){
        var d = new FormData();
        d.append('action', 'en_wizard_save');
        d.append('nonce', saveNonce);
        d.append('site_name', document.getElementById('en-wiz-site-name').value);
        d.append('site_tagline', document.getElementById('en-wiz-tagline').value);
        d.append('logo_url', logoEls.dark.url.value);
        d.append('logo_url_light', logoEls.light.url.value);
        var modeEl = document.querySelector('input[name="en-wiz-mode"]:checked');
        d.append('design_mode', modeEl ? modeEl.value : 'dark');
        return fetch(ajaxurl, { method:'POST', body:d }).then(function(r){ return r.json(); }).catch(function(){});
    }

    function createOne(def){
        var data = new FormData();
        data.append('action', 'en_create_em_page');
        data.append('nonce', nonce);
        data.append('title', def.title);
        data.append('slug', def.slug);
        data.append('sc', def.sc);
        return fetch(ajaxurl, { method:'POST', body:data }).then(function(r){ return r.json(); });
    }

    finishBtn.addEventListener('click', function(){
        finishBtn.disabled = true;
        backBtn.disabled = true;
        msg.textContent = i18n.saving;

        saveSettings().then(function(){
            var selected = checks().filter(function(c){ return c.checked; }).map(function(c){
                return { sc: c.value, title: c.dataset.title, slug: c.dataset.slug };
            });

            if (! selected.length) {
                msg.textContent = i18n.allDone;
                markDone().then(function(){ setTimeout(function(){ location.href = dashUrl; }, 700); });
                return;
            }

            msg.textContent = i18n.creating;
            var idx = 0;
            function next(){
                if (idx >= selected.length) {
                    msg.textContent = i18n.allDone;
                    markDone().then(function(){ setTimeout(function(){ location.href = dashUrl; }, 700); });
                    return;
                }
                var def = selected[idx++];
                createOne(def).then(function(res){
                    msg.textContent = (res && res.success)
                        ? i18n.doneOne.replace('%s', def.title)
                        : i18n.failOne.replace('%s', def.title);
                    next();
                }).catch(function(){
                    msg.textContent = i18n.failOne.replace('%s', def.title);
                    next();
                });
            }
            next();
        });
    });
})();
