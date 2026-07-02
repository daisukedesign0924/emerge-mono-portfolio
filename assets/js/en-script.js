/* EmergeNucleus — Frontend JS */
(function(){
'use strict';

// ── bfcache（Safari等のページ凍結）から復元されたら最新状態に読み直す ──
// iPhone Safari でタブを長時間開いたまま放置し、再度開いてリンクを踏むと
// 2日前の古い状態が復元されて「サーバーに接続できませんでした」エラーになることがある。
// pageshow の event.persisted が true のときだけ（＝bfcache復元時のみ）リロードする。
// 通常のページ表示・遷移には影響しない。
window.addEventListener('pageshow', function(event){
    if ( event.persisted ) {
        window.location.reload();
    }
});

document.addEventListener('DOMContentLoaded', function(){

    // ── body にクラス付与 ──
    document.body.classList.add('en-active');

    // ── コーナー飾り挿入 ──
    ['tl','tr','bl','br'].forEach(function(p){
        var d = document.createElement('div');
        d.className = 'en-corner en-corner-' + p;
        document.body.appendChild(d);
    });

    // ── カスタムカーソル（タッチデバイスでは非表示）──
    var isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    if ( ! isTouchDevice ) {
        var dot  = document.createElement('div'); dot.className  = 'en-cursor-dot';
        var ring = document.createElement('div'); ring.className = 'en-cursor-ring';
        document.body.appendChild(dot);
        document.body.appendChild(ring);

        var mx=0, my=0, rx=0, ry=0;
        document.addEventListener('mousemove', function(e){
            mx = e.clientX; my = e.clientY;
            dot.style.left = mx + 'px'; dot.style.top = my + 'px';
        });
        (function cr(){
            rx += (mx - rx) * .1; ry += (my - ry) * .1;
            ring.style.left = Math.round(rx) + 'px';
            ring.style.top  = Math.round(ry) + 'px';
            requestAnimationFrame(cr);
        })();
    }

    // ── モバイルメニュー ──
    var menuBtn   = document.getElementById('en-menu-btn');
    var menuClose = document.getElementById('en-menu-close');
    var mobileMenu = document.getElementById('en-mobile-menu');

    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function(){
            mobileMenu.classList.add('open');
        });
    }
    if (menuClose && mobileMenu) {
        menuClose.addEventListener('click', function(){
            mobileMenu.classList.remove('open');
        });
    }
    if (mobileMenu) {
        mobileMenu.querySelectorAll('.en-mobile-nav-item').forEach(function(a){
            a.addEventListener('click', function(){
                mobileMenu.classList.remove('open');
            });
        });
    }

    // ── Works グリッド読み込み ──
    var worksGrid = document.getElementById('en-works-grid');
    if (worksGrid) {
        loadWorks('all');
        buildWorksFilter();
    }

    function buildWorksFilter() {
        var filter = document.getElementById('en-works-filter');
        if (!filter) return;

        fetch(EN.rest_url + 'en_work_category?per_page=100')
            .then(function(r){ return r.json(); })
            .then(function(cats){
                cats.forEach(function(cat){
                    var btn = document.createElement('button');
                    btn.className = 'en-filter-btn';
                    // REST APIの en_work_category はターム ID（数値）で絞り込む。
                    // スラッグを渡すと rest_invalid_param になるため id を使う。
                    btn.dataset.cat = cat.id;
                    btn.textContent = cat.name;
                    btn.addEventListener('click', function(){
                        filter.querySelectorAll('.en-filter-btn').forEach(function(b){ b.classList.remove('active'); });
                        btn.classList.add('active');
                        loadWorks(cat.id);
                    });
                    filter.appendChild(btn);
                });
            });

        filter.querySelector('[data-cat="all"]').addEventListener('click', function(){
            filter.querySelectorAll('.en-filter-btn').forEach(function(b){ b.classList.remove('active'); });
            this.classList.add('active');
            loadWorks('all');
        });
    }

    function loadWorks(cat) {
        var grid    = document.getElementById('en-works-grid');
        var loading = document.getElementById('en-works-loading');
        if (!grid) return;

        grid.innerHTML = '';
        if (loading) loading.style.display = 'block';

        var url = EN.rest_url + 'en_work?per_page=100&_embed=1';
        if (cat !== 'all') {
            url += '&en_work_category=' + encodeURIComponent(cat);
        }

        fetch(url)
            .then(function(r){ return r.json(); })
            .then(function(posts){
                if (loading) loading.style.display = 'none';
                posts.forEach(function(post, i){
                    var card = buildWorkCard(post, i);
                    grid.appendChild(card);
                });
                observeCards();
            })
            .catch(function(){
                if (loading) loading.style.display = 'none';
            });
    }

    function buildWorkCard(post, i) {
        var num   = String(i + 1).padStart(2, '0');
        var title = post.title.rendered;
        var meta  = post.en_meta || {};
        var cats  = post.en_categories || [];
        var thumb = '';

        if (post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0]) {
            thumb = post._embedded['wp:featuredmedia'][0].source_url || '';
        }
        if (meta.video_url) {
            var yt = meta.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
            if (yt) thumb = 'https://img.youtube.com/vi/' + yt[1] + '/maxresdefault.jpg';
        }

        var card = document.createElement('div');
        card.className = 'en-work-card';
        card.style.transitionDelay = Math.min(i * 0.06, 0.36) + 's';

        var catHTML = cats.map(function(c){
            return '<span class="en-work-cat">' + c.name + '</span>';
        }).join('');

        card.innerHTML =
            '<div class="en-work-thumb">' +
                (thumb ? '<img src="' + thumb + '" alt="' + title + '" loading="lazy">' : '') +
                '<div class="en-work-overlay">' +
                    '<div class="en-work-overlay-title">' + title + '</div>' +
                    '<div class="en-work-cats">' + catHTML + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="en-work-meta">' +
                '<div class="en-work-meta-num">' + num + '</div>' +
                '<div class="en-work-meta-title">' + title + '</div>' +
            '</div>';

        card.addEventListener('click', function(){
            window.location.href = post.link;
        });

        return card;
    }

    function observeCards() {
        var obs = new IntersectionObserver(function(entries){
            entries.forEach(function(e){
                if (e.isIntersecting) {
                    e.target.classList.add('en-visible');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.en-work-card').forEach(function(c){ obs.observe(c); });
    }

    // ── コンタクトフォーム ──
    var form = document.getElementById('en-contact-form');
    if (form) {
        // reCAPTCHAなしの場合は通常submit
        if (form.dataset.noRecaptcha) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                enSubmitContact();
            });
        }
        // reCAPTCHAありの場合はcontact.phpのfooterスクリプトが処理
    }

    window.enSubmitContact = function() {
        var form = document.getElementById('en-contact-form');
        if (!form) return;
        var status = document.getElementById('en-form-status');
        var submit = form.querySelector('.en-form-submit');
        var origText = submit.textContent;

        submit.disabled = true;
        submit.textContent = 'Sending...';
        status.className = 'en-form-status';
        status.textContent = '';

        var data = new FormData(form);
        data.set('action', 'en_send_contact');
        data.set('nonce', EN.nonce);

        fetch(EN.ajax_url, { method: 'POST', body: data })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    status.className = 'en-form-status success';
                    status.textContent = res.data.message;
                    form.reset();
                } else {
                    status.className = 'en-form-status error';
                    status.textContent = res.data.message;
                }
                submit.disabled = false;
                submit.textContent = origText;
            })
            .catch(function(){
                status.className = 'en-form-status error';
                status.textContent = (EN.i18n && EN.i18n.sendFailed) ? EN.i18n.sendFailed : 'Failed to send.';
                submit.disabled = false;
                submit.textContent = origText;
            });
    };

}); // DOMContentLoaded
})();
