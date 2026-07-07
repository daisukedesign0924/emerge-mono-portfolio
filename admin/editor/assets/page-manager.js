(function(){
    var settings = window.enePageSettings || {};
    var enePageI18n = settings.i18n || {};
    var createNonce = settings.createNonce || '';

    window.eneDeleteEmPage = function(pageId, title, btn) {
        if (! confirm(enePageI18n.confirmDelete.replace('%s', title))) return;
        btn.disabled = true;
        btn.textContent = enePageI18n.deleting;
        var data = new FormData();
        data.append('action', 'ene_delete_post');
        data.append('nonce', ENE.nonce_delete);
        data.append('post_id', pageId);
        fetch(ajaxurl, { method: 'POST', body: data })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    document.getElementById('ene-page-msg').style.color = 'rgba(255,255,255,.6)';
                    document.getElementById('ene-page-msg').textContent = enePageI18n.deletedMsg.replace('%s', title);
                    setTimeout(function(){ location.reload(); }, 800);
                } else {
                    btn.disabled = false;
                    btn.textContent = enePageI18n.del;
                    document.getElementById('ene-page-msg').style.color = 'rgba(255,100,100,.7)';
                    document.getElementById('ene-page-msg').textContent = enePageI18n.errorPrefix + (res.data || enePageI18n.couldNotDelete);
                }
            });
    };

    window.eneCreatePage = function(def, btn) {
        btn.disabled = true;
        btn.textContent = enePageI18n.creating;
        var data = new FormData();
        data.append('action', 'ene_create_em_page');
        data.append('nonce', createNonce);
        data.append('title', def.title);
        data.append('slug', def.slug);
        data.append('sc', def.sc);
        fetch(ajaxurl, { method: 'POST', body: data })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    document.getElementById('ene-page-msg').style.color = 'rgba(255,255,255,.6)';
                    document.getElementById('ene-page-msg').textContent = enePageI18n.createdMsg.replace('%s', def.title);
                    setTimeout(function(){ location.reload(); }, 800);
                } else {
                    btn.disabled = false;
                    btn.textContent = enePageI18n.create;
                    document.getElementById('ene-page-msg').style.color = 'rgba(255,100,100,.7)';
                    document.getElementById('ene-page-msg').textContent = enePageI18n.errorPrefix + (res.data || enePageI18n.couldNotCreate);
                }
            });
    };
})();
