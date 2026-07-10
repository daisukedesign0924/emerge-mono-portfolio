(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
            return;
        }
        document.addEventListener('DOMContentLoaded', fn);
    }

    function hasCategory(el, filter) {
        var categories = (el.getAttribute('data-categories') || 'all').split(/\s+/);
        return filter === 'all' || categories.indexOf(filter) !== -1;
    }

    function getGap(track) {
        var style = window.getComputedStyle(track);
        var gap = parseFloat(style.columnGap || style.gap || '0');
        return isNaN(gap) ? 0 : gap;
    }

    function initTabs(section, itemSelector) {
        var tabs = Array.prototype.slice.call(section.querySelectorAll('.emono-mono-top-tab'));
        var items = Array.prototype.slice.call(section.querySelectorAll(itemSelector));
        if (!tabs.length || !items.length) {
            return;
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var filter = tab.getAttribute('data-filter') || 'all';
                tabs.forEach(function (button) {
                    button.classList.toggle('is-active', button === tab);
                });

                items.forEach(function (item) {
                    item.classList.toggle('is-hidden', !hasCategory(item, filter));
                });
            });
        });
    }

    function initSlider(section) {
        if (!section) {
            return;
        }

        var track = section.querySelector('.emono-mono-top-slider-track');
        if (!track) {
            return;
        }

        var baseCards = Array.prototype.slice.call(track.querySelectorAll('.emono-mono-top-card')).map(function (card) {
            return card.cloneNode(true);
        });
        var tabs = Array.prototype.slice.call(section.querySelectorAll('.emono-mono-top-tab'));
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var frame = 0;
        var activeFilter = 'all';
        var offset = 0;
        var paused = false;

        function stopMotion() {
            if (frame) {
                window.cancelAnimationFrame(frame);
            }
            frame = 0;
            offset = 0;
            track.style.transform = '';
        }

        function buildCards(filter) {
            var visible = baseCards.filter(function (card) {
                return hasCategory(card, filter);
            });
            track.innerHTML = '';
            if (!visible.length) {
                return;
            }

            var targetCount = reduceMotion ? visible.length : Math.max(visible.length, 8);
            for (var i = 0; i < targetCount; i += 1) {
                track.appendChild(visible[i % visible.length].cloneNode(true));
            }

            if (!reduceMotion) {
                for (var j = 0; j < visible.length; j += 1) {
                    track.appendChild(visible[j].cloneNode(true));
                }
            }
        }

        function baseDistance() {
            var cards = Array.prototype.slice.call(track.querySelectorAll('.emono-mono-top-card'));
            var baseCount = baseCards.filter(function (card) {
                return hasCategory(card, activeFilter);
            }).length;
            var gap = getGap(track);
            var distance = 0;

            cards.slice(0, baseCount).forEach(function (card) {
                distance += card.offsetWidth + gap;
            });

            return distance;
        }

        function tick() {
            var distance = baseDistance();
            if (!paused && distance > 0) {
                offset -= 0.45;
                if (Math.abs(offset) >= distance) {
                    offset = 0;
                }
                track.style.transform = 'translate3d(' + offset + 'px,0,0)';
            }
            frame = window.requestAnimationFrame(tick);
        }

        function startMotion() {
            stopMotion();
            if (reduceMotion || baseCards.length < 2) {
                return;
            }
            frame = window.requestAnimationFrame(tick);
        }

        function applyFilter(filter) {
            activeFilter = filter;
            tabs.forEach(function (button) {
                button.classList.toggle('is-active', (button.getAttribute('data-filter') || 'all') === filter);
            });

            track.style.opacity = '0';
            window.setTimeout(function () {
                buildCards(filter);
                startMotion();
                track.style.opacity = '1';
            }, 160);
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                applyFilter(tab.getAttribute('data-filter') || 'all');
            });
        });

        track.addEventListener('mouseenter', function () {
            paused = true;
        });
        track.addEventListener('mouseleave', function () {
            paused = false;
        });

        buildCards(activeFilter);
        startMotion();

        var resizeTimer;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(function () {
                applyFilter(activeFilter);
            }, 180);
        });
    }

    ready(function () {
        Array.prototype.slice.call(document.querySelectorAll('.emono-mono-top')).forEach(function (root) {
            // 同じ型のセクションを複数配置できるため、すべてのスライダー/ニュースを個別に初期化。
            Array.prototype.slice.call(root.querySelectorAll('.emono-mono-top-slider-section')).forEach(function (section) {
                initSlider(section);
            });
            Array.prototype.slice.call(root.querySelectorAll('.emono-mono-top-news-section')).forEach(function (newsSection) {
                initTabs(newsSection, '.emono-mono-top-news-card');
            });
        });
    });
}());
