/**
 * Emerge Mono - Privacy Policy admin live preview.
 * Reads the nonce from window.enPrivacyPreview (via wp_localize_script).
 */
( function() {
    var cfg = window.enPrivacyPreview || {};
    var timer = null;
    var fields = ['privacy_lang', 'legal_owner', 'legal_site', 'legal_email', 'privacy_cookie', 'privacy_ga', 'privacy_disclaimer', 'privacy_custom'];

    function updatePreview() {
        var data = new FormData();
        data.append('action', 'en_preview_privacy');
        data.append('nonce', cfg.nonce || '');
        fields.forEach(function(name) {
            var els = document.querySelectorAll('[name="' + name + '"]');
            els.forEach(function(el) {
                if (el.type === 'checkbox') {
                    if (el.checked) data.append(name, '1');
                } else if (el.type === 'radio') {
                    if (el.checked) data.append(name, el.value);
                } else {
                    data.append(name, el.value);
                }
            });
        });
        fetch(cfg.ajaxUrl || ajaxurl, { method: 'POST', body: data })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) document.getElementById('en-privacy-preview').innerHTML = res.data;
            });
    }

    fields.forEach(function(name) {
        document.querySelectorAll('[name="' + name + '"]').forEach(function(el) {
            el.addEventListener('change', function(){ clearTimeout(timer); timer = setTimeout(updatePreview, 300); });
            if (el.tagName === 'TEXTAREA' || el.type === 'text' || el.type === 'email') {
                el.addEventListener('input', function(){ clearTimeout(timer); timer = setTimeout(updatePreview, 600); });
            }
        });
    });
} )();
