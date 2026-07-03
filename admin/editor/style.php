<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_enqueue_scripts', 'emono_ed_admin_enqueue' );
function emono_ed_admin_enqueue( $hook ) {
    $ene_pages = array(
        'toplevel_page_ene-post',
        'toplevel_page_ene-works',
        'toplevel_page_ene-news',
        'toplevel_page_ene-page-create',
        'toplevel_page_ene-page-list',
        'admin_page_ene-page-edit',
    );
    if ( ! in_array( $hook, $ene_pages ) ) return;

    wp_enqueue_media();

    // JS変数をwp_localize_scriptで安全に渡す
    // src=false のダミー登録。localize の ENE 定義を admin_footer の本体JSより先に出すため、
    // in_footer は false（ヘッダー出力）にする。順序を変えると "ENE is not defined" になる。
    wp_register_script( 'ene-admin', false, array(), EMONO_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
    wp_enqueue_script( 'ene-admin' );
    wp_localize_script( 'ene-admin', 'ENE', array(
        'ajax_url'     => admin_url( 'admin-ajax.php' ),
        'nonce_post'   => wp_create_nonce( 'ene_post_nonce' ),
        'nonce_delete' => wp_create_nonce( 'ene_delete_nonce' ),
        'nonce_cat'    => wp_create_nonce( 'ene_cat_nonce' ),
        'nonce_page'   => wp_create_nonce( 'ene_page_nonce' ),
        'i18n'         => array(
            'selectImage'      => __( 'Select Image', 'emerge-mono-portfolio' ),
            'select'           => __( 'Select', 'emerge-mono-portfolio' ),
            'change'           => __( 'Change', 'emerge-mono-portfolio' ),
            'clickToSelect'    => __( 'Click to select an image', 'emerge-mono-portfolio' ),
            'selectGallery'    => __( 'Select Gallery Images', 'emerge-mono-portfolio' ),
            'add'              => __( 'Add', 'emerge-mono-portfolio' ),
            'image'            => __( 'Image', 'emerge-mono-portfolio' ),
            'enterTitle'       => __( 'Please enter a title.', 'emerge-mono-portfolio' ),
            'submitting'       => __( 'Submitting...', 'emerge-mono-portfolio' ),
            'failedSubmit'     => __( 'Failed to submit.', 'emerge-mono-portfolio' ),
            'confirmDeletePost'=> __( 'Delete this post? This cannot be undone.', 'emerge-mono-portfolio' ),
            'deleting'         => __( 'Deleting...', 'emerge-mono-portfolio' ),
            'deleted'          => __( 'Deleted.', 'emerge-mono-portfolio' ),
            'categoryAdded'    => __( 'Category added.', 'emerge-mono-portfolio' ),
            'confirmDeleteCat' => __( 'Delete this category?', 'emerge-mono-portfolio' ),
            'noCategoriesYet'  => __( 'No categories yet', 'emerge-mono-portfolio' ),
            'categoryDeleted'  => __( 'Category deleted.', 'emerge-mono-portfolio' ),
            'processing'       => __( 'Processing...', 'emerge-mono-portfolio' ),
        ),
    ));

    add_action( 'admin_head',   'emono_ed_admin_style' );
    add_action( 'admin_footer', 'emono_ed_admin_script' );
}

function emono_ed_admin_style() { ?>
<style>
#wpcontent { background:#0d0d0d; }
#wpbody-content { padding-bottom:0; }
.ene-wrap { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; color:#e8e8e8; min-height:100vh; background:#0d0d0d; }

/* ── ヘッダー ── */
.ene-header { background:#111; border-bottom:1px solid rgba(255,255,255,.07); padding:16px 28px; }
.ene-header-inner { display:flex; align-items:center; justify-content:space-between; }
.ene-header-title { font-size:16px; font-weight:600; color:#fff; }
.ene-new-btn { background:#fff; color:#000; font-size:12px; font-weight:600; padding:8px 18px; border-radius:7px; text-decoration:none; transition:opacity .2s; }
.ene-new-btn:hover { opacity:.85; color:#000; }
.ene-back-btn { font-size:12px; color:rgba(255,255,255,.35); text-decoration:none; transition:color .2s; }
.ene-back-btn:hover { color:rgba(255,255,255,.7); }

/* ── 投稿タイプ切替バー ── */
.ene-type-bar { background:#111; border-bottom:1px solid rgba(255,255,255,.07); padding:20px 28px; }
.ene-type-bar-label { font-size:11px; letter-spacing:.1em; color:rgba(255,255,255,.25); text-transform:uppercase; margin-bottom:14px; }
.ene-type-bar-btns { display:flex; gap:12px; }
.ene-type-bar-btn { display:flex; align-items:center; gap:12px; padding:16px 24px; border-radius:10px; border:1px solid rgba(255,255,255,.1); text-decoration:none; color:rgba(255,255,255,.4); font-size:15px; font-weight:600; transition:all .2s; min-width:200px; background:rgba(255,255,255,.02); }
.ene-type-bar-btn:hover { border-color:rgba(255,255,255,.3); color:rgba(255,255,255,.8); background:rgba(255,255,255,.05); }
.ene-type-bar-btn.active { border-color:rgba(255,255,255,.5); color:#fff; background:rgba(255,255,255,.08); }
.ene-type-bar-btn .dashicons { font-size:22px; width:22px; height:22px; color:inherit; }
.ene-type-desc { font-size:11px; font-weight:400; color:rgba(255,255,255,.25); margin-left:auto; white-space:nowrap; }
.ene-type-bar-btn.active .ene-type-desc { color:rgba(255,255,255,.4); }

/* ── ボディ ── */
.ene-body { display:grid; grid-template-columns:1fr 280px; gap:24px; padding:28px; max-width:1100px; }
.ene-form { display:contents; }
.ene-main { display:flex; flex-direction:column; gap:20px; }
.ene-field { display:flex; flex-direction:column; gap:8px; }
.ene-label { font-size:11px; font-weight:600; color:rgba(255,255,255,.45); letter-spacing:.06em; text-transform:uppercase; }
.ene-req { color:rgba(255,100,100,.7); }
.ene-input, .ene-textarea, .ene-select { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:8px; color:#e8e8e8; font-size:14px; padding:11px 14px; outline:none; transition:border-color .2s; font-family:inherit; width:100%; }
.ene-input:focus, .ene-textarea:focus, .ene-select:focus { border-color:rgba(255,255,255,.35); }
.ene-textarea { height:240px; resize:vertical; line-height:1.7; }
.ene-select { cursor:pointer; }
.ene-field-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }

/* ── サイドバー ── */
.ene-sidebar { display:flex; flex-direction:column; gap:16px; }
.ene-side-section { background:#161616; border:1px solid rgba(255,255,255,.07); border-radius:10px; padding:18px; }
.ene-side-title { font-size:10px; font-weight:600; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.3); margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,.05); }

/* ── アイキャッチ ── */
.ene-thumb-preview { width:100%; aspect-ratio:16/9; background:rgba(255,255,255,.04); border:1px dashed rgba(255,255,255,.12); border-radius:8px; cursor:pointer; overflow:hidden; position:relative; display:flex; align-items:center; justify-content:center; transition:border-color .2s; }
.ene-thumb-preview:hover { border-color:rgba(255,255,255,.3); }
.ene-thumb-preview img { width:100%; height:100%; object-fit:cover; }
.ene-thumb-overlay { position:absolute; inset:0; background:rgba(0,0,0,.6); display:flex; align-items:center; justify-content:center; font-size:12px; color:#fff; opacity:0; transition:opacity .2s; }
.ene-thumb-preview:hover .ene-thumb-overlay { opacity:1; }
.ene-thumb-placeholder { display:flex; flex-direction:column; align-items:center; gap:8px; color:rgba(255,255,255,.2); }
.ene-thumb-placeholder .dashicons { font-size:32px; width:32px; height:32px; }
.ene-thumb-placeholder div { font-size:11px; letter-spacing:.05em; }
.ene-remove-thumb { margin-top:8px; background:none; border:none; color:rgba(255,100,100,.5); font-size:11px; cursor:pointer; padding:0; }
.ene-remove-thumb:hover { color:rgba(255,100,100,.8); }

/* ── チェックボックス ── */
.ene-check-label { display:flex; align-items:center; gap:8px; font-size:13px; color:rgba(255,255,255,.55); cursor:pointer; margin-bottom:8px; }
.ene-check-label input { accent-color:#fff; width:14px; height:14px; }
.ene-no-cat { font-size:12px; color:rgba(255,255,255,.2); line-height:1.7; }

/* ── アクション ── */
.ene-actions { display:flex; flex-direction:column; gap:8px; }
.ene-submit-btn { background:#fff; color:#000; border:none; border-radius:8px; font-size:13px; font-weight:600; padding:13px; cursor:pointer; transition:opacity .2s; width:100%; }
.ene-submit-btn:hover { opacity:.85; }
.ene-submit-btn:disabled { opacity:.4; cursor:not-allowed; }
.ene-cancel-btn { display:block; text-align:center; font-size:12px; color:rgba(255,255,255,.3); text-decoration:none; padding:8px; }
.ene-cancel-btn:hover { color:rgba(255,255,255,.6); }
.ene-msg { font-size:12px; text-align:center; min-height:20px; border-radius:6px; padding:8px; }
.ene-msg.success { background:rgba(100,200,100,.08); color:rgba(150,230,150,.8); border:1px solid rgba(100,200,100,.2); }
.ene-msg.error   { background:rgba(255,100,100,.08); color:rgba(255,150,150,.8); border:1px solid rgba(255,100,100,.2); }

/* ── テーブル ── */
.ene-table { width:100%; border-collapse:collapse; font-size:13px; }
.ene-table th { padding:12px 14px; text-align:left; font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.25); font-weight:normal; border-bottom:1px solid rgba(255,255,255,.07); }
.ene-table td { padding:14px; border-bottom:1px solid rgba(255,255,255,.04); vertical-align:middle; }
.ene-table tr:hover td { background:rgba(255,255,255,.02); }
.ene-table-title { color:rgba(255,255,255,.75); text-decoration:none; font-size:13px; transition:color .2s; }
.ene-table-title:hover { color:#fff; }
.ene-table-btn { background:none; border:1px solid rgba(255,255,255,.12); border-radius:5px; color:rgba(255,255,255,.4); font-size:11px; padding:5px 12px; cursor:pointer; text-decoration:none; transition:all .2s; margin-right:4px; display:inline-block; }
.ene-table-btn:hover { border-color:rgba(255,255,255,.35); color:#fff; }
.ene-delete-btn:hover { border-color:rgba(255,100,100,.4); color:rgba(255,100,100,.7); }
.ene-badge { font-size:10px; letter-spacing:.1em; padding:3px 8px; border-radius:4px; }
.ene-badge-pub   { background:rgba(100,200,100,.1); color:rgba(150,230,150,.8); border:1px solid rgba(100,200,100,.2); }
.ene-badge-draft { background:rgba(255,255,255,.05); color:rgba(255,255,255,.3); border:1px solid rgba(255,255,255,.1); }
.ene-badge-none  { background:rgba(255,150,50,.08); color:rgba(255,180,80,.6); border:1px solid rgba(255,150,50,.15); }
.ene-empty { padding:60px 28px; text-align:center; font-size:13px; color:rgba(255,255,255,.2); line-height:2; }

/* ── カテゴリーバー ── */
.ene-cat-bar { background:#111; border-bottom:1px solid rgba(255,255,255,.07); padding:16px 28px; }
.ene-cat-bar-inner { max-width:1100px; }
.ene-cat-bar-title { font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.25); margin-bottom:12px; }
.ene-cat-list { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; min-height:28px; }
.ene-cat-tag { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:20px; padding:4px 12px; font-size:12px; color:rgba(255,255,255,.6); }
.ene-cat-delete { background:none; border:none; color:rgba(255,255,255,.25); cursor:pointer; font-size:14px; padding:0; line-height:1; transition:color .2s; }
.ene-cat-delete:hover { color:rgba(255,100,100,.7); }
.ene-cat-empty { font-size:12px; color:rgba(255,255,255,.2); align-self:center; }
.ene-cat-add { display:flex; gap:8px; align-items:center; }
.ene-cat-input { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:6px; color:#e8e8e8; font-size:13px; padding:7px 12px; outline:none; transition:border-color .2s; width:220px; }
.ene-cat-input:focus { border-color:rgba(255,255,255,.35); }
.ene-cat-add-btn { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); border-radius:6px; color:rgba(255,255,255,.6); font-size:12px; padding:7px 16px; cursor:pointer; transition:all .2s; white-space:nowrap; }
.ene-cat-add-btn:hover { background:rgba(255,255,255,.14); color:#fff; }

/* ── ページ作成 ── */
.enp-sc-wrap { display:flex; gap:8px; margin-bottom:12px; }
.enp-sc-list { display:flex; flex-direction:column; gap:8px; min-height:20px; }
.enp-sc-tag { display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:6px; padding:10px 14px; }
.enp-sc-tag code { font-family:monospace; font-size:13px; color:rgba(255,255,255,.7); background:none; padding:0; }
.enp-sc-empty { font-size:12px; color:rgba(255,255,255,.2); padding:8px 0; }
</style>
<?php }

function emono_ed_admin_script() { ?>
<script>
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
</script>
<?php }
