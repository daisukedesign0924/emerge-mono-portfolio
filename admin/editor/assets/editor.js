(function() {
    // ENEオブジェクトが読み込まれているか確認
    if (typeof ENE === 'undefined') {
        console.error('ENE object not found');
        return;
    }

    var ajaxUrl     = ENE.ajax_url;
    var noncePost   = ENE.nonce_post;
    var nonceDelete = ENE.nonce_delete;
    var nonceCat    = ENE.nonce_cat;
    var noncePage   = ENE.nonce_page;

    // ── アナウンスバナー ──
    window.eneShowBanner = function(message, type) {
        var existing = document.getElementById('ene-banner');
        if (existing) existing.remove();
        var banner = document.createElement('div');
        banner.id = 'ene-banner';
        banner.textContent = message;
        var bg    = type === 'success' ? 'rgba(100,200,100,.15)' : 'rgba(255,100,100,.15)';
        var bd    = type === 'success' ? 'rgba(100,200,100,.3)'  : 'rgba(255,100,100,.3)';
        var color = type === 'success' ? 'rgba(150,230,150,.9)'  : 'rgba(255,150,150,.9)';
        banner.style.cssText = 'position:fixed;top:40px;left:50%;transform:translateX(-50%);background:' + bg + ';border:1px solid ' + bd + ';color:' + color + ';font-size:13px;padding:12px 28px;border-radius:8px;z-index:99999;backdrop-filter:blur(8px);white-space:nowrap;transition:opacity .5s;';
        document.body.appendChild(banner);
        setTimeout(function() { banner.style.opacity = '0'; }, 2500);
        setTimeout(function() { if (banner.parentNode) banner.remove(); }, 3000);
    };

    // ── メディアライブラリ（投稿用） ──
    window.eneOpenMedia = function() {
        var frame = wp.media({ title:ENE.i18n.selectImage, button:{text:ENE.i18n.select}, multiple:false, library:{type:'image'} });
        frame.on('select', function() {
            var att = frame.state().get('selection').first().toJSON();
            document.getElementById('ene-thumb-id').value = att.id;
            var p = document.getElementById('ene-thumb-preview');
            p.innerHTML = '<img src="' + att.url + '"><div class="ene-thumb-overlay">' + ENE.i18n.change + '</div>';
            p.onclick = window.eneOpenMedia;
        });
        frame.open();
    };

    window.eneRemoveThumb = function() {
        document.getElementById('ene-thumb-id').value = '';
        var p = document.getElementById('ene-thumb-preview');
        p.innerHTML = '<div class="ene-thumb-placeholder"><span class="dashicons dashicons-format-image"></span><div>' + ENE.i18n.clickToSelect + '</div></div>';
    };

    // ── ギャラリー: 画像追加 ──
    window.eneAddGalleryImage = function() {
        if (!wp || !wp.media) return;
        var frame = wp.media({ title: ENE.i18n.selectGallery, multiple: true, library: { type: 'image' }, button: { text: ENE.i18n.add } });
        frame.on('select', function() {
            frame.state().get('selection').each(function(attachment) {
                var id  = attachment.get('id');
                var url = attachment.get('sizes') && attachment.get('sizes').thumbnail ? attachment.get('sizes').thumbnail.url : attachment.get('url');
                eneAppendGalleryImage(id, url);
            });
            eneUpdateGalleryItems();
        });
        frame.open();
    };

    // ── ギャラリー: 動画追加 ──
    window.eneAddGalleryVideo = function() {
        var wrap = document.getElementById('ene-video-input-wrap');
        if (wrap) { wrap.style.display = 'block'; document.getElementById('ene-video-url-input').focus(); }
    };
    window.eneCancelGalleryVideo = function() {
        var wrap = document.getElementById('ene-video-input-wrap');
        if (wrap) { wrap.style.display = 'none'; document.getElementById('ene-video-url-input').value = ''; }
    };
    window.eneConfirmGalleryVideo = function() {
        var url = (document.getElementById('ene-video-url-input').value || '').trim();
        if (!url) return;
        eneAppendGalleryVideo(url);
        eneUpdateGalleryItems();
        eneCancelGalleryVideo();
    };

    function eneAppendGalleryImage(id, url) {
        var list = document.getElementById('ene-gallery-list');
        if (!list) return;
        var div = document.createElement('div');
        div.className = 'ene-gallery-item';
        div.setAttribute('data-type', 'image');
        div.setAttribute('data-id', id);
        div.style.cssText = 'display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);padding:6px;border-radius:4px;cursor:grab';
        div.innerHTML = '<img src="' + url + '" style="width:48px;height:36px;object-fit:cover;flex-shrink:0"><span style="font-size:11px;opacity:.5;flex:1">' + ENE.i18n.image + ' #' + id + '</span><button type="button" onclick="eneRemoveGalleryItem(this)" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:14px">×</button>';
        list.appendChild(div);
    }

    function eneAppendGalleryVideo(url) {
        var list = document.getElementById('ene-gallery-list');
        if (!list) return;
        var div = document.createElement('div');
        div.className = 'ene-gallery-item';
        div.setAttribute('data-type', 'video');
        div.setAttribute('data-url', url);
        div.style.cssText = 'display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);padding:6px;border-radius:4px;cursor:grab';
        div.innerHTML = '<div style="width:48px;height:36px;background:rgba(255,255,255,.08);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px">▶</div><span style="font-size:11px;opacity:.5;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + url + '</span><button type="button" onclick="eneRemoveGalleryItem(this)" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:14px">×</button>';
        list.appendChild(div);
    }

    window.eneRemoveGalleryItem = function(btn) {
        btn.closest('.ene-gallery-item').remove();
        eneUpdateGalleryItems();
    };

    function eneUpdateGalleryItems() {
        var items = document.querySelectorAll('.ene-gallery-item');
        var data = Array.from(items).map(function(el) {
            if (el.getAttribute('data-type') === 'video') {
                return { type: 'video', url: el.getAttribute('data-url') };
            } else {
                return { type: 'image', id: parseInt(el.getAttribute('data-id')) };
            }
        });
        var input = document.getElementById('ene-gallery-items');
        if (input) input.value = JSON.stringify(data);
    }

    // ── 投稿送信 ──
    window.eneSubmit = function() {
        var btn    = document.getElementById('ene-submit');
        var msg    = document.getElementById('ene-msg');
        var typeEl = document.getElementById('ene-post-type');
        var postIdEl = document.getElementById('ene-post-id');

        if (!btn || !typeEl || !postIdEl) return;

        var type   = typeEl.value;
        var postId = postIdEl.value;
        var titleEl = document.getElementById('ene-title');
        if (!titleEl) return;
        var title = titleEl.value.trim();

        if (!title) {
            msg.className = 'ene-msg error';
            msg.textContent = ENE.i18n.enterTitle;
            return;
        }

        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = ENE.i18n.submitting;
        msg.className = 'ene-msg';
        msg.textContent = '';

        var workCats = [];
        var newsCats = [];
        document.querySelectorAll('.ene-work-cat:checked').forEach(function(el) { workCats.push(parseInt(el.value)); });
        document.querySelectorAll('.ene-news-cat:checked').forEach(function(el) { newsCats.push(parseInt(el.value)); });

        var data = new FormData();
        data.append('action',    'ene_save_post');
        data.append('nonce',     noncePost);
        data.append('post_type', type);
        data.append('post_id',   postId);
        data.append('title',     title);
        data.append('content',   document.getElementById('ene-content') ? document.getElementById('ene-content').value : '');
        data.append('status',    document.getElementById('ene-status') ? document.getElementById('ene-status').value : 'publish');
        data.append('thumb_id',      document.getElementById('ene-thumb-id') ? document.getElementById('ene-thumb-id').value : '');
        data.append('gallery_items', document.getElementById('ene-gallery-items') ? document.getElementById('ene-gallery-items').value : '[]');
        data.append('work_cats', JSON.stringify(workCats));
        data.append('news_cats', JSON.stringify(newsCats));

        if (type === 'works') {
            var fields = ['ene-video-url','ene-ext-url','ene-period','ene-role','ene-tools'];
            var keys   = ['video_url','ext_url','period','role','tools'];
            fields.forEach(function(id, i) {
                var el = document.getElementById(id);
                data.append(keys[i], el ? el.value : '');
            });

        }

        fetch(ajaxUrl, { method:'POST', body:data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.textContent = origText;
                if (res.success) {
                    msg.className = 'ene-msg success';
                    msg.textContent = res.data.message;
                    window.eneShowBanner(res.data.message, 'success');
                    if (!postId && res.data.edit_url) {
                        setTimeout(function() { window.location.href = res.data.edit_url; }, 1200);
                    }
                } else {
                    msg.className = 'ene-msg error';
                    msg.textContent = res.data.message;
                    window.eneShowBanner(res.data.message, 'error');
                }
            })
            .catch(function(e) {
                btn.disabled = false;
                btn.textContent = origText;
                msg.className = 'ene-msg error';
                msg.textContent = ENE.i18n.failedSubmit;
                console.error(e);
            });
    };

    // ── 削除 ──
    window.eneDelete = function(postId, btn) {
        if (!confirm(ENE.i18n.confirmDeletePost)) return;
        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = ENE.i18n.deleting;

        var data = new FormData();
        data.append('action',  'ene_delete_post');
        data.append('nonce',   nonceDelete);
        data.append('post_id', postId);

        fetch(ajaxUrl, { method:'POST', body:data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    var row = btn.closest('tr');
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity .3s';
                        setTimeout(function() { row.remove(); }, 300);
                    }
                    window.eneShowBanner(ENE.i18n.deleted, 'success');
                } else {
                    btn.disabled = false;
                    btn.textContent = origText;
                    alert(res.data.message);
                }
            });
    };

    // ── カテゴリー追加 ──
    window.eneAddCat = function(taxonomy, inputId, listId) {
        var input = document.getElementById(inputId);
        var name  = input ? input.value.trim() : '';
        if (!name) return;

        var data = new FormData();
        data.append('action',   'ene_add_category');
        data.append('nonce',    nonceCat);
        data.append('name',     name);
        data.append('taxonomy', taxonomy);

        fetch(ajaxUrl, { method:'POST', body:data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    var list = document.getElementById(listId);
                    var empty = list ? list.querySelector('.ene-cat-empty') : null;
                    if (empty) empty.remove();
                    var tag = document.createElement('span');
                    tag.className = 'ene-cat-tag';
                    tag.innerHTML = res.data.name + '<button type="button" class="ene-cat-delete" onclick="eneDeleteCat(' + res.data.term_id + ', \'' + taxonomy + '\', this)">×</button>';
                    if (list) list.appendChild(tag);
                    if (input) input.value = '';
                    window.eneShowBanner(ENE.i18n.categoryAdded, 'success');
                } else {
                    alert(res.data.message);
                }
            });
    };

    // ── カテゴリー削除 ──
    window.eneDeleteCat = function(termId, taxonomy, btn) {
        if (!confirm(ENE.i18n.confirmDeleteCat)) return;
        var data = new FormData();
        data.append('action',   'ene_delete_category');
        data.append('nonce',    nonceCat);
        data.append('term_id',  termId);
        data.append('taxonomy', taxonomy);

        fetch(ajaxUrl, { method:'POST', body:data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    var tag  = btn.closest('.ene-cat-tag');
                    var list = tag ? tag.parentNode : null;
                    if (tag) tag.remove();
                    if (list && list.querySelectorAll('.ene-cat-tag').length === 0) {
                        var empty = document.createElement('span');
                        empty.className = 'ene-cat-empty';
                        empty.textContent = ENE.i18n.noCategoriesYet;
                        list.appendChild(empty);
                    }
                    window.eneShowBanner(ENE.i18n.categoryDeleted, 'success');
                } else {
                    alert(res.data.message);
                }
            });
    };

    // ── ページ作成（メディア） ──
    window.enpOpenMedia = function() {
        var frame = wp.media({ title:ENE.i18n.selectImage, button:{text:ENE.i18n.select}, multiple:false, library:{type:'image'} });
        frame.on('select', function() {
            var att = frame.state().get('selection').first().toJSON();
            document.getElementById('enp-thumb-id').value = att.id;
            var p = document.getElementById('enp-thumb-preview');
            p.innerHTML = '<img src="' + att.url + '"><div class="ene-thumb-overlay">' + ENE.i18n.change + '</div>';
            p.onclick = window.enpOpenMedia;
        });
        frame.open();
    };

    window.enpRemoveThumb = function() {
        document.getElementById('enp-thumb-id').value = '';
        var p = document.getElementById('enp-thumb-preview');
        p.innerHTML = '<div class="ene-thumb-placeholder"><span class="dashicons dashicons-format-image"></span><div>' + ENE.i18n.clickToSelect + '</div></div>';
    };

    // ── ショートコード追加 ──
    window.enpAddShortcode = function() {
        var sel = document.getElementById('enp-sc-select');
        var val = sel ? sel.value : '';
        if (!val) return;
        var list = document.getElementById('enp-sc-list');
        var empty = document.getElementById('enp-sc-empty');
        if (empty) empty.remove();
        var tag = document.createElement('div');
        tag.className = 'enp-sc-tag';
        tag.innerHTML = '<code>' + val + '</code><button type="button" class="ene-cat-delete" onclick="this.closest(\'.enp-sc-tag\').remove()">×</button>';
        if (list) list.appendChild(tag);
        if (sel) sel.value = '';
    };

    // ── ページ保存 ──
    window.enpSubmit = function(editId) {
        var btn   = document.getElementById('enp-submit');
        var msg   = document.getElementById('enp-msg');
        var titleEl = document.getElementById('enp-title');
        if (!btn || !titleEl) return;

        var title = titleEl.value.trim();
        if (!title) {
            if (msg) { msg.className = 'ene-msg error'; msg.textContent = ENE.i18n.enterTitle; }
            return;
        }

        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = ENE.i18n.processing;
        if (msg) { msg.className = 'ene-msg'; msg.textContent = ''; }

        var tags = document.querySelectorAll('.enp-sc-tag code');
        var content = Array.from(tags).map(function(t) { return t.textContent; }).join('\n');

        var data = new FormData();
        data.append('action',   'ene_save_page');
        data.append('nonce',    noncePage);
        data.append('post_id',  editId || 0);
        data.append('title',    title);
        data.append('content',  content);
        data.append('slug',     document.getElementById('enp-slug') ? document.getElementById('enp-slug').value.trim() : '');
        data.append('status',   document.getElementById('enp-status') ? document.getElementById('enp-status').value : 'publish');
        data.append('thumb_id', document.getElementById('enp-thumb-id') ? document.getElementById('enp-thumb-id').value : '');

        fetch(ajaxUrl, { method:'POST', body:data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.textContent = origText;
                if (res.success) {
                    if (msg) { msg.className = 'ene-msg success'; msg.textContent = res.data.message; }
                    window.eneShowBanner(res.data.message, 'success');
                    if (!editId && res.data.edit_url) {
                        setTimeout(function() { window.location.href = res.data.edit_url; }, 1200);
                    }
                } else {
                    if (msg) { msg.className = 'ene-msg error'; msg.textContent = res.data.message; }
                    window.eneShowBanner(res.data.message, 'error');
                }
            })
            .catch(function(e) {
                btn.disabled = false;
                btn.textContent = origText;
                console.error(e);
            });
    };

})();
