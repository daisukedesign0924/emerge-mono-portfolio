(function () {
	'use strict';

	function init() {
		var list = document.getElementById('emono-sections-list');
		var orderInput = document.getElementById('emono-sections-order');
		if (!list || !orderInput) { return; }
		var form = list.closest ? list.closest('form') : null;

		function updateOrder() {
			var ids = [];
			list.querySelectorAll('.emono-section-card').forEach(function (card) {
				ids.push(card.getAttribute('data-id'));
			});
			orderInput.value = ids.join(',');
		}

		// 本体のライブプレビュー（フォームの input を監視）を発火させる。
		function fire() {
			updateOrder();
			if (form) { form.dispatchEvent(new Event('input', { bubbles: true })); }
		}

		function newId() { return 'sec' + Math.random().toString(36).slice(2, 10); }

		if (window.jQuery && window.jQuery.fn && window.jQuery.fn.sortable) {
			window.jQuery(list).sortable({
				handle: '.emono-section-handle',
				items: '> .emono-section-card',
				tolerance: 'pointer',
				update: function () { fire(); }
			});
		}

		var addBtn = document.getElementById('emono-sections-add-btn');
		var addType = document.getElementById('emono-sections-add-type');
		if (addBtn && addType) {
			addBtn.addEventListener('click', function () {
				var type = addType.value;
				var tpl = document.querySelector('.emono-section-template[data-type="' + type + '"]');
				if (!tpl) { return; }
				var html = tpl.innerHTML.split('__ID__').join(newId());
				var tmp = document.createElement('div');
				tmp.innerHTML = html;
				var card = tmp.querySelector('.emono-section-card');
				if (!card) { return; }
				list.appendChild(card);
				fire();
			});
		}

		list.addEventListener('click', function (e) {
			var removeBtn = e.target.closest ? e.target.closest('.emono-section-remove') : null;
			if (removeBtn) {
				var card = removeBtn.closest('.emono-section-card');
				if (card) { card.parentNode.removeChild(card); fire(); }
				return;
			}
			var toggleBtn = e.target.closest ? e.target.closest('.emono-section-toggle') : null;
			if (toggleBtn) {
				var c = toggleBtn.closest('.emono-section-card');
				if (c) { c.classList.toggle('is-collapsed'); }
			}
		});

		updateOrder();
	}

	if (document.readyState !== 'loading') { init(); }
	else { document.addEventListener('DOMContentLoaded', init); }
}());
