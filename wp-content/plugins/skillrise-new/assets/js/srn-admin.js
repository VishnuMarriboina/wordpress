/*
 * Skillrise /admin — interactions. Plain script, no dependencies. Every screen works without it;
 * this adds the collapsible sidebar, mobile drawer, settings tabs, tracks editor, toasts and small animations.
 */
(() => {
	const root = document.querySelector('.srna-app');
	const html = document.documentElement;
	const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const $ = (s, c = document) => c.querySelector(s);
	const $$ = (s, c = document) => [...c.querySelectorAll(s)];
	const store = (k, v) => { try { v === undefined ? localStorage.removeItem(k) : localStorage.setItem(k, v); } catch (e) { /* private mode */ } };

	/* ---------- Top progress bar while the next page loads ---------- */
	const progress = $('.srna-progress');
	const busyPage = () => progress && progress.classList.add('is-on');
	window.addEventListener('pageshow', () => progress && progress.classList.remove('is-on'));
	document.addEventListener('click', (e) => {
		const a = e.target.closest('a[href]');
		if (!a || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;
		const url = new URL(a.href, location.href);
		if (url.origin !== location.origin || url.pathname.includes('admin-post.php') || (url.pathname === location.pathname && url.search === location.search && url.hash)) return;
		busyPage();
	});

	if (!root) return;

	/* ---------- Sidebar: collapse (desktop) ---------- */
	const collapseBtn = $('[data-srna-collapse]');
	const setCollapsed = (on) => {
		html.classList.toggle('srna-collapsed', on);
		collapseBtn?.setAttribute('aria-pressed', String(on));
		store('srnaCollapsed', on ? '1' : undefined);
	};
	collapseBtn?.setAttribute('aria-pressed', String(html.classList.contains('srna-collapsed')));
	collapseBtn?.addEventListener('click', () => setCollapsed(!html.classList.contains('srna-collapsed')));

	/* ---------- Sidebar: drawer (phones/tablets) ---------- */
	const side = $('#srna-side');
	const opener = $('[data-srna-open]');
	const scrim = $('.srna-scrim');
	const mq = matchMedia('(max-width: 860px)');
	const syncInert = () => (mq.matches && !root.classList.contains('is-drawer') ? side.setAttribute('inert', '') : side.removeAttribute('inert'));
	const setDrawer = (open) => {
		root.classList.toggle('is-drawer', open);
		syncInert(); // Un-inert before moving focus into it.
		opener?.setAttribute('aria-expanded', String(open));
		if (scrim) scrim.hidden = !open;
		html.style.overflow = open ? 'hidden' : '';
		if (open) $('a[aria-current], a', side)?.focus();
		else opener?.focus({ preventScroll: true });
	};
	opener?.addEventListener('click', () => setDrawer(true));
	$$('[data-srna-close]').forEach((b) => b.addEventListener('click', () => setDrawer(false)));
	document.addEventListener('keydown', (e) => {
		if (e.key !== 'Escape') return;
		if (root.classList.contains('is-drawer')) setDrawer(false);
		$$('details.srna-usermenu[open]').forEach((d) => d.removeAttribute('open'));
	});
	mq.addEventListener('change', () => { if (!mq.matches) setDrawer(false); syncInert(); });
	syncInert();

	/* ---------- User menu: close when clicking elsewhere ---------- */
	document.addEventListener('click', (e) => $$('details.srna-usermenu[open]').forEach((d) => !d.contains(e.target) && d.removeAttribute('open')));

	/* ---------- Toast ---------- */
	$$('[data-srna-toast]').forEach((t) => {
		const close = () => { t.classList.add('is-out'); setTimeout(() => t.remove(), 300); };
		$('[data-srna-toast-close]', t)?.addEventListener('click', close);
		setTimeout(close, 6000);
		// Drop the one-time flag so a refresh doesn't show it again.
		const url = new URL(location.href);
		['notice', 'type', 'settings-updated'].forEach((k) => url.searchParams.delete(k));
		history.replaceState(null, '', url.pathname + url.search + location.hash);
	});

	/* ---------- Counters on the metric cards ---------- */
	if (!reduce && 'IntersectionObserver' in window) {
		$$('.srna-kpi dd').forEach((dd) => {
			const node = [...dd.childNodes].find((n) => n.nodeType === 3 && /\d/.test(n.textContent));
			if (!node) return;
			const m = node.textContent.trim().match(/^([\d,]+)(\.\d+)?(%?)$/);
			if (!m) return;
			const end = parseFloat((m[1] + (m[2] || '')).replace(/,/g, ''));
			const dec = (m[2] || '').length ? m[2].length - 1 : 0;
			const fmt = (v) => v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + m[3];
			const t0 = performance.now();
			const step = (now) => {
				const p = Math.min(1, (now - t0) / 900);
				node.textContent = fmt(end * (1 - Math.pow(1 - p, 3)));
				if (p < 1) requestAnimationFrame(step);
			};
			node.textContent = fmt(0);
			requestAnimationFrame(step);
		});
	}

	/* ---------- Buttons show progress and can't be pressed twice ---------- */
	document.addEventListener('submit', (e) => {
		if (e.defaultPrevented) return;
		const btn = $('[data-srna-busy]', e.target);
		if (btn) {
			if (btn.classList.contains('is-busy')) { e.preventDefault(); return; }
			btn.classList.add('is-busy');
			setTimeout(() => (btn.disabled = true), 0);
		}
		busyPage();
	});

	/* ---------- Confirm destructive forms ---------- */
	$$('form[data-srna-confirm]').forEach((f) => f.addEventListener('submit', (e) => { if (!confirm(f.dataset.srnaConfirm)) e.preventDefault(); }));

	/* ---------- Follow-up form ---------- */
	const fuForm = $('[data-srna-fu]');
	if (fuForm) {
		// Contact buttons preselect how the student was contacted.
		$$('[data-srna-channel]').forEach((b) => b.addEventListener('click', () => {
			const r = $(`input[name="channel"][value="${b.dataset.srnaChannel}"]`, fuForm);
			if (r) r.checked = true;
		}));
		// The next-date field only matters while the follow-up is still open.
		const next = $('[data-srna-next]', fuForm);
		const sync = () => {
			const s = $('input[name="status"]:checked', fuForm);
			const open = !s || ['pending', 'progress'].includes(s.dataset.group);
			next.hidden = !open;
			if (!open) $('input', next).value = '';
		};
		fuForm.addEventListener('change', sync);
		sync();
	}

	/* ---------- Password forms ---------- */
	$$('[data-srna-password]').forEach((form) => {
		const nw = $('[name="new_pass"]', form);
		const cf = $('[name="confirm_pass"]', form);
		const check = () => cf.setCustomValidity(cf.value && cf.value !== nw.value ? cf.dataset.msg || 'The new passwords do not match.' : '');
		nw.addEventListener('input', check);
		cf.addEventListener('input', check);
		$('[data-srna-showpass]', form)?.addEventListener('change', (e) => $$('input[type="password"], input[data-was-pass]', form).forEach((i) => {
			i.dataset.wasPass = '1';
			i.type = e.target.checked ? 'text' : 'password';
		}));
	});

	/* ---------- Settings: tabs ---------- */
	const settings = $('[data-srna-settings]');
	if (settings) {
		const tabs = $$('.srna-tab', settings);
		const panels = $$('.srna-panel', settings);
		const show = (id, focus) => {
			const tab = tabs.find((t) => t.getAttribute('aria-controls') === id) || tabs[0];
			tabs.forEach((t) => { const on = t === tab; t.setAttribute('aria-selected', String(on)); t.tabIndex = on ? 0 : -1; });
			panels.forEach((p) => (p.hidden = p.id !== tab.getAttribute('aria-controls')));
			if (focus) tab.focus();
			tab.scrollIntoView({ block: 'nearest', inline: 'center', behavior: reduce ? 'auto' : 'smooth' });
		};
		settings.classList.add('is-tabbed');
		tabs.forEach((t, i) => {
			t.addEventListener('click', (e) => { e.preventDefault(); show(t.getAttribute('aria-controls')); history.replaceState(null, '', '#' + t.getAttribute('aria-controls')); });
			t.addEventListener('keydown', (e) => {
				const to = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 }[e.key];
				if (to === undefined) return;
				e.preventDefault();
				show(tabs[(to + tabs.length) % tabs.length].getAttribute('aria-controls'), true);
			});
		});
		show(location.hash.slice(1));
		window.addEventListener('hashchange', () => show(location.hash.slice(1)));
		// A required field in a hidden tab: open that tab so the browser can point at it.
		settings.addEventListener('invalid', (e) => { const p = e.target.closest('.srna-panel'); if (p?.hidden) show(p.id); }, true);

		/* Tracks editor */
		const list = $('[data-srna-tracks]', settings);
		const tpl = $('[data-srna-track-template]', settings);
		$('[data-srna-add-track]', settings)?.addEventListener('click', () => {
			list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', 'n' + Date.now()));
			$('[data-srna-track-name]', list.lastElementChild)?.focus();
			dirty();
		});
		list?.addEventListener('click', (e) => {
			const btn = e.target.closest('button');
			const row = btn?.closest('[data-srna-track]');
			if (!row) return;
			if (btn.hasAttribute('data-srna-remove')) {
				if (confirm(btn.title + '?')) { row.remove(); dirty(); }
			} else if (btn.hasAttribute('data-srna-up') && row.previousElementSibling) {
				row.previousElementSibling.before(row); btn.focus(); dirty();
			} else if (btn.hasAttribute('data-srna-down') && row.nextElementSibling) {
				row.nextElementSibling.after(row); btn.focus(); dirty();
			}
		});
		list?.addEventListener('input', (e) => {
			if (e.target.matches('[data-srna-track-name]')) $('[data-srna-track-title]', e.target.closest('[data-srna-track]')).textContent = e.target.value || '—';
		});

		/* Unsaved changes */
		let changed = false;
		const note = $('[data-srna-dirty-note]', settings);
		const dirty = () => {
			if (changed) return;
			changed = true;
			settings.classList.add('is-dirty');
			if (note) note.textContent = note.dataset.dirty || 'You have unsaved changes.';
		};
		settings.addEventListener('input', dirty);
		settings.addEventListener('change', dirty);
		settings.addEventListener('submit', () => (changed = false));
		window.addEventListener('beforeunload', (e) => { if (changed) { e.preventDefault(); e.returnValue = ''; } });
	}
})();
