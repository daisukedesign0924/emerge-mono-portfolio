/**
 * Emerge Mono - Works detail slider.
 * Reads the total slide count from the #en-slider element's data-total attribute,
 * so no inline PHP-generated script is needed.
 */
(function(){
    var slider = document.getElementById('en-slider');
    if ( ! slider ) { return; }

    var total = parseInt( slider.getAttribute('data-total'), 10 ) || 0;
    if ( total <= 1 ) { return; }

    var stage  = document.getElementById('en-slider-stage');
    var slides = stage ? stage.querySelectorAll('.en-slide') : [];
    var fill   = document.getElementById('en-slider-fill');
    var curEl  = document.getElementById('en-slider-cur');
    var cur    = 0;

    if ( ! stage ) { return; }

    function pad(n){ return n < 10 ? '0' + n : '' + n; }

    function goTo(n) {
        cur = (n + total) % total;
        slides.forEach(function(s, i){ s.classList.toggle('active', i === cur); });
        if (fill)  fill.style.width = ((cur + 1) / total * 100) + '%';
        if (curEl) curEl.textContent = pad(cur + 1);
    }

    // Arrows (image edges + bottom UI)
    ['en-slider-prev','en-slider-prev2'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', function(){ goTo(cur - 1); });
    });
    ['en-slider-next','en-slider-next2'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', function(){ goTo(cur + 1); });
    });

    // Swipe (mobile)
    var startX = 0, startY = 0, swiping = false;
    stage.addEventListener('touchstart', function(e){
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        swiping = true;
    }, {passive:true});
    stage.addEventListener('touchend', function(e){
        if (!swiping) return;
        swiping = false;
        var dx = startX - e.changedTouches[0].clientX;
        var dy = startY - e.changedTouches[0].clientY;
        // Only react when horizontal movement exceeds vertical (avoid scroll conflicts)
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
            goTo(dx > 0 ? cur + 1 : cur - 1);
        }
    }, {passive:true});

    // Keyboard (when hovering the slider on desktop)
    var hovering = false;
    slider.addEventListener('mouseenter', function(){ hovering = true; });
    slider.addEventListener('mouseleave', function(){ hovering = false; });
    document.addEventListener('keydown', function(e){
        if (!hovering) return;
        if (e.key === 'ArrowLeft')  goTo(cur - 1);
        if (e.key === 'ArrowRight') goTo(cur + 1);
    });

    goTo(0);
})();
