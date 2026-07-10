(function () {
	'use strict';
	var L = window.EMOS_HERO_ADMIN || {};

	function fireChange(el) {
		var form = el.closest ? el.closest('form') : null;
		if (form) {
			form.dispatchEvent(new Event('input', { bubbles: true }));
			form.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}

	function openMedia(cb) {
		if (!window.wp || !wp.media) { return; }
		var frame = wp.media({ title: L.select || 'Select image', button: { text: L.use || 'Use' }, multiple: false, library: { type: ['image'] } });
		frame.on('select', function () { cb(frame.state().get('selection').first().toJSON().url); });
		frame.open();
	}

	function cardId(el) {
		var card = el.closest ? el.closest('.emono-section-card') : null;
		return card ? card.getAttribute('data-id') : '';
	}

	// クリック委譲（動的に追加されたセクションカードにも効く）。
	document.addEventListener('click', function (e) {
		var sel = e.target.closest && e.target.closest('.emos-sprite-select');
		if (sel) {
			e.preventDefault();
			var row = sel.closest('.emos-sprite-row');
			openMedia(function (url) {
				var input = row.querySelector('.emos-sprite-url');
				input.value = url;
				var thumb = row.querySelector('.emos-sprite-thumb');
				thumb.innerHTML = '<img src="' + url.replace(/"/g, '&quot;') + '" alt="">';
				fireChange(input);
			});
			return;
		}
		var rm = e.target.closest && e.target.closest('.emos-sprite-remove');
		if (rm) {
			e.preventDefault();
			var r = rm.closest('.emos-sprite-row');
			var wrap = r.parentNode;
			wrap.removeChild(r);
			fireChange(wrap);
			return;
		}
		var add = e.target.closest && e.target.closest('.emos-sprite-add');
		if (add) {
			e.preventDefault();
			var id = cardId(add);
			var card = add.closest('.emono-section-card') || document;
			var wrap2 = card.querySelector('.emos-hero-sprites');
			if (!wrap2) { return; }
			var row2 = document.createElement('div');
			row2.className = 'emos-sprite-row';
			row2.innerHTML =
				'<span class="emos-sprite-thumb"></span>' +
				'<input type="hidden" class="emos-sprite-url" name="sections[' + id + '][sprites][]" value="">' +
				'<button type="button" class="en-media-btn emos-sprite-select">Select</button>' +
				'<button type="button" class="en-media-btn emos-sprite-remove" aria-label="Remove">&times;</button>';
			wrap2.appendChild(row2);
			// 画像未選択の空行は保存/プレビュー時に無視される。
			return;
		}
	});
}());
