(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); return; }
		document.addEventListener('DOMContentLoaded', fn);
	}

	var FALLBACK = (window.EMOS_HERO && window.EMOS_HERO.fallback) || '';

	// 選択された複数SVG/PNGが、中央から湧き出し拡大しながら外へ飛ぶ（モノクロ・奥行きワープ）。
	function initHero(canvas) {
		var ctx = canvas.getContext('2d');
		if (!ctx) { return; }
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		var count = parseInt(canvas.getAttribute('data-density'), 10) || 90;
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var W = 0, H = 0, cx = 0, cy = 0, maxDist = 1;
		var particles = [];

		// スプライト一覧（未指定なら同梱のWマーク）。
		var urls = [];
		try { urls = JSON.parse(canvas.getAttribute('data-sprites') || '[]'); } catch (e) { urls = []; }
		if (!Array.isArray(urls) || !urls.length) { urls = FALLBACK ? [FALLBACK] : []; }

		var images = [];   // { img, ready }
		var tinted = [];   // offscreen canvas per sprite (tinted) or null
		var spriteColor = '';
		var rgb = '255,255,255';

		function currentRgb() {
			var c = getComputedStyle(canvas).color;
			var m = c && c.match(/(\d+),\s*(\d+),\s*(\d+)/);
			return m ? (m[1] + ',' + m[2] + ',' + m[3]) : '255,255,255';
		}
		function tint(imgObj) {
			if (!imgObj.ready) { return null; }
			var size = 256;
			var off = document.createElement('canvas');
			off.width = size; off.height = size;
			var o = off.getContext('2d');
			o.clearRect(0, 0, size, size);
			o.drawImage(imgObj.img, 0, 0, size, size);
			o.globalCompositeOperation = 'source-in';
			o.fillStyle = 'rgb(' + rgb + ')';
			o.fillRect(0, 0, size, size);
			return off;
		}
		function buildAll() {
			for (var i = 0; i < images.length; i++) { tinted[i] = tint(images[i]); }
			spriteColor = rgb;
		}
		function ensureColor() {
			var next = currentRgb();
			if (next !== spriteColor) { rgb = next; buildAll(); }
		}
		urls.forEach(function (u, i) {
			var obj = { img: new Image(), ready: false };
			obj.img.onload = function () { obj.ready = true; if (!spriteColor) { rgb = currentRgb(); } tinted[i] = tint(obj); spriteColor = rgb; };
			obj.img.src = u;
			images.push(obj);
			tinted.push(null);
		});

		function rand(a, b) { return a + Math.random() * (b - a); }
		function spawn(p, atStart) {
			p.angle = rand(0, Math.PI * 2);
			p.depth = atStart ? Math.random() : 0;
			p.speed = rand(0.0016, 0.0042);
			p.size = rand(10, 34);
			p.rot = rand(0, Math.PI * 2);
			p.rotSpeed = rand(-0.02, 0.02);
			p.alpha = rand(0.4, 0.95);
			p.si = images.length ? ((Math.random() * images.length) | 0) : 0;
			return p;
		}
		function build() {
			particles = [];
			for (var i = 0; i < count; i++) { particles.push(spawn({}, true)); }
		}

		function resize() {
			W = canvas.clientWidth; H = canvas.clientHeight;
			canvas.width = Math.max(1, (W * dpr) | 0);
			canvas.height = Math.max(1, (H * dpr) | 0);
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			cx = W / 2; cy = H / 2;
			maxDist = Math.hypot(W, H) * 0.58;
			ensureColor();
		}

		function drawParticle(p) {
			var sp = tinted[p.si];
			if (!sp) { return; }
			var t = p.depth;
			var ease = t * t;
			var dist = ease * maxDist;
			var x = cx + Math.cos(p.angle) * dist;
			var y = cy + Math.sin(p.angle) * dist;
			var scale = 0.16 + ease * 1.7;
			var op = p.alpha;
			if (t < 0.14) { op *= t / 0.14; }
			else if (t > 0.82) { op *= Math.max(0, (1 - t) / 0.18); }
			if (op <= 0.01) { return; }

			var s = p.size * scale;
			ctx.save();
			ctx.translate(x, y);
			ctx.rotate(p.rot);
			ctx.globalAlpha = op;
			ctx.drawImage(sp, -s / 2, -s / 2, s, s);
			ctx.restore();
		}

		function frame() {
			ctx.clearRect(0, 0, W, H);
			for (var i = 0; i < particles.length; i++) {
				var p = particles[i];
				p.depth += p.speed;
				p.rot += p.rotSpeed;
				if (p.depth >= 1) { spawn(p, false); }
				drawParticle(p);
			}
			raf = window.requestAnimationFrame(frame);
		}
		function still() {
			ctx.clearRect(0, 0, W, H);
			for (var i = 0; i < particles.length; i++) { drawParticle(particles[i]); }
		}

		var raf = 0;
		resize();
		build();
		if (reduce) {
			window.setTimeout(still, 300);
		} else {
			raf = window.requestAnimationFrame(frame);
		}

		var rt;
		window.addEventListener('resize', function () {
			window.clearTimeout(rt);
			rt = window.setTimeout(function () { resize(); if (reduce) { still(); } }, 180);
		});
	}

	ready(function () {
		Array.prototype.slice.call(document.querySelectorAll('.emos-hero-canvas')).forEach(initHero);
	});
}());
