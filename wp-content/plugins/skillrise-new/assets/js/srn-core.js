/*
 * Skillrise /new — core behaviour. No dependencies; works on its own.
 * Loads the GSAP motion layer and the Three.js hero scene on demand (see bottom).
 * The form submits to the EXISTING endpoint POST /wp-json/industrial-training/v1/register
 * with the same payload, nonce flow and error handling as the classic site (industrial-training/assets/js/itp.js).
 */
(() => {
	const root = document.querySelector('[data-srn]');
	if (!root) return;

	const D = window.srnData || { i18n: {} };
	const T = D.i18n || {};
	const $ = (s, c = root) => c.querySelector(s);
	const $$ = (s, c = root) => [...c.querySelectorAll(s)];
	const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const hasIO = 'IntersectionObserver' in window;
	const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;
	const raf = (fn) => {
		let id = 0;
		return (...a) => id || (id = requestAnimationFrame(() => { id = 0; fn(...a); }));
	};

	if (!reduce && hasIO) {
		root.classList.add('srn-motion');
		// Drop the finished entrance animation so its fill state doesn't override scroll effects.
		root.addEventListener('animationend', (e) => e.target.classList.contains('srn-intro') && e.target.classList.add('srn-intro-done'));
	}

	/* ---------- Header: background on scroll, progress bar, back-to-top ---------- */
	const header = $('[data-srn-header]');
	const bar = $('.srn-progress');
	const toTop = $('[data-srn-top]');
	const onScroll = raf(() => {
		const y = scrollY;
		const max = document.documentElement.scrollHeight - innerHeight;
		header.classList.toggle('is-scrolled', y > 10 || header.classList.contains('is-open'));
		bar.style.transform = `scaleX(${max > 0 ? Math.min(1, y / max) : 0})`;
		toTop.classList.toggle('is-visible', y > 800);
	});
	addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	/* ---------- Mobile menu ---------- */
	const burger = $('[data-srn-burger]');
	const burgerLabel = $('[data-srn-burger-label]');
	const nav = $('#srn-nav');
	const mqMenu = matchMedia('(max-width: 1100px)');
	const isOpen = () => header.classList.contains('is-open');
	const setMenu = (open, focusBack = true) => {
		header.classList.toggle('is-open', open);
		burger.setAttribute('aria-expanded', String(open));
		burgerLabel.textContent = open ? T.menuClose || 'Close menu' : T.menuOpen || 'Open menu';
		document.documentElement.style.overflow = open ? 'hidden' : '';
		onScroll();
		if (open) setTimeout(() => $('a', nav)?.focus(), 60);
		else if (focusBack) burger.focus();
	};
	// The off-canvas nav is hidden from AT and keyboard while closed on small screens.
	const syncNavHidden = () => {
		if (mqMenu.matches && !isOpen()) nav.setAttribute('inert', '');
		else nav.removeAttribute('inert');
	};
	burger.addEventListener('click', () => {
		setMenu(!isOpen());
		syncNavHidden();
	});
	nav.addEventListener('click', (e) => {
		if (e.target.closest('a') && isOpen()) {
			setMenu(false, false);
			syncNavHidden();
		}
	});
	header.addEventListener('keydown', (e) => {
		if (!isOpen()) return;
		if (e.key === 'Escape') {
			setMenu(false);
			syncNavHidden();
		} else if (e.key === 'Tab') {
			// Keep focus inside the open menu (links, CTA, close button).
			const items = [...$$('a', nav), burger];
			const i = items.indexOf(document.activeElement);
			if (e.shiftKey && i <= 0) { e.preventDefault(); items[items.length - 1].focus(); }
			else if (!e.shiftKey && i === items.length - 1) { e.preventDefault(); items[0].focus(); }
		}
	});
	mqMenu.addEventListener('change', () => {
		if (!mqMenu.matches && isOpen()) setMenu(false, false);
		syncNavHidden();
	});
	syncNavHidden();

	/* ---------- Scroll-spy ---------- */
	if (hasIO) {
		const links = $$('[data-srn-link]');
		const spy = new IntersectionObserver((entries) => {
			entries.forEach((e) => {
				if (!e.isIntersecting) return;
				links.forEach((a) => (a.hash === '#' + e.target.id ? a.setAttribute('aria-current', 'location') : a.removeAttribute('aria-current')));
			});
		}, { rootMargin: '-45% 0px -50% 0px' });
		$$('[data-srn-spy]').forEach((s) => spy.observe(s));
	}

	/* ---------- Reveal on scroll (staggered) ---------- */
	if (root.classList.contains('srn-motion')) {
		const reveal = new IntersectionObserver((entries) => {
			let n = 0;
			entries.forEach((e) => {
				if (!e.isIntersecting) return;
				e.target.style.setProperty('--d', Math.min(n++, 6) * 90 + 'ms');
				e.target.classList.add('is-in');
				reveal.unobserve(e.target);
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		$$('[data-srn-reveal]').forEach((el) => reveal.observe(el));
	}

	/* ---------- Pause CSS loops while off-screen ---------- */
	if (hasIO) {
		const pause = new IntersectionObserver((entries) => {
			entries.forEach((e) => e.target.classList.toggle('srn-offscreen', !e.isIntersecting));
		});
		$$('.srn-hero, .srn-about-visual, .srn-orbit-wrap, .srn-map, .srn-track').forEach((el) => pause.observe(el));
	}

	/* ---------- Count-up (markup from itp_stat_value(): .itp-count + .itp-sr final value) ---------- */
	if (!reduce && hasIO) {
		const counters = $$('.itp-count').map((el) => {
			const m = el.textContent.trim().match(/^(\D*)([\d,]*\.?\d+)(.*)$/);
			if (!m || /\d/.test(m[3])) return null; // Ranges ("₹17,000 – ₹21,500") stay as written.
			const end = parseFloat(m[2].replace(/,/g, ''));
			const dec = (m[2].split('.')[1] || '').length;
			const fmt = (v) => m[1] + (m[2].includes(',') ? v.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec }) : v.toFixed(dec)) + m[3];
			el.textContent = fmt(0);
			return { el, end, fmt };
		}).filter(Boolean);
		const count = new IntersectionObserver((entries) => {
			entries.forEach((e) => {
				if (!e.isIntersecting) return;
				count.unobserve(e.target);
				const c = counters.find((x) => x.el === e.target);
				const t0 = performance.now();
				const step = (now) => {
					const p = Math.min(1, (now - t0) / 1800);
					c.el.textContent = c.fmt(c.end * (1 - Math.pow(1 - p, 4)));
					if (p < 1) requestAnimationFrame(step);
				};
				requestAnimationFrame(step);
			});
		}, { threshold: 0.6 });
		counters.forEach((c) => count.observe(c.el));
	}

	/* ---------- 3D tilt on cards (mouse only) ---------- */
	if (finePointer && !reduce) {
		$$('[data-srn-tilt]').forEach((card) => {
			const max = card.classList.contains('srn-about-card') ? 8 : 6;
			const move = raf((e) => {
				const r = card.getBoundingClientRect();
				const x = (e.clientX - r.left) / r.width - 0.5;
				const y = (e.clientY - r.top) / r.height - 0.5;
				card.style.setProperty('--rx', (-y * max).toFixed(2) + 'deg');
				card.style.setProperty('--ry', (x * max).toFixed(2) + 'deg');
				card.style.setProperty('--lift', '-6px');
			});
			card.addEventListener('pointerenter', () => card.classList.add('is-tilting'));
			card.addEventListener('pointermove', (e) => e.pointerType === 'mouse' && move(e));
			card.addEventListener('pointerleave', () => {
				card.classList.remove('is-tilting');
				['--rx', '--ry', '--lift'].forEach((p) => card.style.removeProperty(p));
			});
		});
	}

	/* ---------- Reviews carousel (native scroll-snap + buttons) ---------- */
	$$('[data-srn-carousel]').forEach((track) => {
		const sec = track.closest('section');
		const prev = $('[data-srn-prev]', sec);
		const next = $('[data-srn-next]', sec);
		const update = raf(() => {
			const max = track.scrollWidth - track.clientWidth;
			prev.setAttribute('aria-disabled', String(track.scrollLeft <= 4));
			next.setAttribute('aria-disabled', String(track.scrollLeft >= max - 4));
		});
		const go = (dir) => {
			const card = track.firstElementChild;
			const step = card ? card.getBoundingClientRect().width + 20 : track.clientWidth;
			track.scrollBy({ left: dir * step, behavior: reduce ? 'auto' : 'smooth' });
		};
		prev.addEventListener('click', () => prev.getAttribute('aria-disabled') !== 'true' && go(-1));
		next.addEventListener('click', () => next.getAttribute('aria-disabled') !== 'true' && go(1));
		track.addEventListener('scroll', update, { passive: true });
		track.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
			if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
		});
		addEventListener('resize', update);
		update();
	});

	/* ---------- FAQ accordion ---------- */
	const accBtns = $$('.srn-acc-btn');
	const toggle = (btn, open) => {
		const panel = document.getElementById(btn.getAttribute('aria-controls'));
		const item = btn.closest('.srn-acc-item');
		if (open === (btn.getAttribute('aria-expanded') === 'true')) return;
		btn.setAttribute('aria-expanded', String(open));
		item.classList.toggle('is-open', open);
		panel.getAnimations?.().forEach((a) => a.cancel());
		if (open) {
			panel.hidden = false;
			if (!reduce && panel.animate) panel.animate([{ height: '0px', opacity: 0 }, { height: panel.scrollHeight + 'px', opacity: 1 }], { duration: 380, easing: 'cubic-bezier(.2,.7,.2,1)' });
		} else if (!reduce && panel.animate) {
			panel.animate([{ height: panel.scrollHeight + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 300, easing: 'cubic-bezier(.2,.7,.2,1)' }).onfinish = () => {
				if (btn.getAttribute('aria-expanded') === 'false') panel.hidden = true;
			};
		} else {
			panel.hidden = true;
		}
	};
	accBtns.forEach((btn, i) => {
		btn.addEventListener('click', () => toggle(btn, btn.getAttribute('aria-expanded') !== 'true'));
		btn.addEventListener('keydown', (e) => {
			const to = { ArrowDown: i + 1, ArrowUp: i - 1, Home: 0, End: accBtns.length - 1 }[e.key];
			if (to === undefined) return;
			e.preventDefault();
			accBtns[(to + accBtns.length) % accBtns.length].focus();
		});
	});
	if (accBtns[0]) toggle(accBtns[0], true);

	/* ---------- Map: load Google Maps only when asked (saves ~1 MB on first load) ---------- */
	$$('[data-srn-map-load]').forEach((btn) => {
		btn.addEventListener('click', () => {
			const box = btn.closest('[data-srn-map]');
			const frame = document.createElement('iframe');
			frame.src = box.dataset.srnMap;
			frame.title = box.dataset.srnMapTitle || '';
			frame.loading = 'lazy';
			frame.referrerPolicy = 'no-referrer-when-downgrade';
			frame.allowFullscreen = true;
			box.replaceChildren(frame);
			box.classList.add('is-loaded');
			frame.setAttribute('tabindex', '0');
			frame.focus();
		});
	});

	/* ---------- Learning journey: static fallback (the motion layer animates it) ---------- */
	const timelineStatic = () => {
		$$('[data-srn-step]').forEach((s) => s.classList.add('is-active'));
		const fill = $('[data-srn-fill]');
		if (fill) fill.style.transform = 'scaleY(1)';
	};

	/* ---------- Registration form → existing REST API ---------- */
	const card = $('.srn-form-card');
	const form = $('[data-srn-form]');
	if (form && D.endpoint && D.nonceUrl) {
		const fieldset = $('.srn-fields', form);
		const alertBox = $('.srn-form-error', form);
		const submit = $('.srn-submit', form);
		const label = $('.srn-submit-label', form);
		const view = $('.srn-form-view', card);
		const done = $('.srn-success', card);
		const FIELDS = ['fullName', 'email', 'phone', 'college', 'branch'];
		// Same rules as the server (industrial-training/includes/helpers.php) and the classic form.
		const EMAIL = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?)*\.[A-Za-z]{2,}$/;
		const phoneDigits = (raw) => {
			const d = raw.replace(/[\s().-]/g, '').replace(/^(?:\+91|0091|91(?=\d{10}$)|0(?=\d{10}$))/, '');
			return /^\d{10}$/.test(d) ? d : '';
		};
		const phoneError = (raw) => {
			const d = phoneDigits(raw);
			if (!d) return T.phone;
			if (!/^[6-9]/.test(d)) return T.phoneStart;
			if (/(\d)\1{7}/.test(d)) return T.phoneRepeat;
			return '';
		};
		const emailError = (v) => (!v ? T.emailEmpty : EMAIL.test(v) && !v.includes('..') ? '' : T.email);
		let submitted = false;
		let inFlight = false;

		const data = () => {
			const d = {};
			new FormData(form).forEach((v, k) => (d[k] = String(v).trim()));
			return d;
		};
		const allowed = (name, v) => v !== '' && [...form.elements[name].options].some((o) => o.value === v);
		const validate = (d) => {
			const e = {};
			if (!d.fullName) e.fullName = T.fullName;
			const em = emailError(d.email);
			if (em) e.email = em;
			const ph = phoneError(d.phone);
			if (ph) e.phone = ph;
			if (!d.college) e.college = T.college;
			if (!allowed('branch', d.branch)) e.branch = T.branch;
			return e;
		};
		const showErrors = (errors, shake) => {
			FIELDS.forEach((f) => {
				const el = form.elements[f];
				const msg = $('#srn-err-' + f, form);
				if (errors[f]) {
					el.setAttribute('aria-invalid', 'true');
					if (msg.textContent !== errors[f]) msg.textContent = errors[f];
					if (shake && !reduce) {
						el.classList.remove('srn-shake');
						void el.offsetWidth;
						el.classList.add('srn-shake');
					}
				} else {
					el.removeAttribute('aria-invalid');
					msg.textContent = '';
				}
			});
			const first = FIELDS.find((f) => errors[f]);
			if (shake && first) form.elements[first].focus();
			return !!first;
		};
		const busy = (on) => {
			inFlight = on;
			form.setAttribute('aria-busy', String(on));
			fieldset.disabled = on;
			submit.disabled = on;
			submit.classList.toggle('is-loading', on);
			if (on) label.textContent = T.submitting;
		};
		const fail = (msg) => {
			busy(false);
			label.textContent = T.retry;
			alertBox.textContent = msg;
		};
		const reset = () => {
			form.reset();
			submitted = false;
			showErrors({}, false);
			alertBox.textContent = '';
			busy(false);
			label.textContent = T.submit;
			view.hidden = false;
			done.hidden = true;
		};
		const success = (d) => {
			busy(false);
			const fill = (s) => (s || '').replace(/\{name\}/g, d.fullName.split(/\s+/)[0]).replace(/\{track\}/g, T.anyTrack).replace(/\{phone\}/g, d.phone);
			const title = $('.srn-success-title', card);
			title.textContent = fill(card.dataset.successTitle);
			$('.srn-success-text', card).textContent = fill(card.dataset.successText);
			view.hidden = true;
			done.hidden = false;
			title.focus();
		};

		const live = () => submitted && showErrors(validate(data()), false);
		form.addEventListener('input', live);
		form.addEventListener('change', live);
		$('[data-srn-again]', card)?.addEventListener('click', () => {
			reset();
			form.elements.fullName.focus();
		});

		form.addEventListener('submit', async (e) => {
			e.preventDefault();
			if (inFlight) return; // Duplicate-submission guard.
			submitted = true;
			const d = data();
			if (showErrors(validate(d), true)) return;

			alertBox.textContent = '';
			busy(true);
			const ctrl = new AbortController();
			let timedOut = false;
			const timer = setTimeout(() => {
				timedOut = true;
				ctrl.abort();
			}, 15000);

			try {
				// Fresh wp_rest nonce from the existing endpoint: the page may be served from cache.
				const { nonce } = await fetch(D.nonceUrl + (D.nonceUrl.includes('?') ? '&' : '?') + 't=' + Date.now(), {
					credentials: 'same-origin',
					cache: 'no-store',
					signal: ctrl.signal,
				}).then((r) => r.json());
				const res = await fetch(D.endpoint, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
					body: JSON.stringify(d), // { fullName, email, phone, college, branch, website } — unchanged payload.
					signal: ctrl.signal,
				});
				const body = await res.json().catch(() => ({}));
				if (res.ok) return success(d);
				if (body.fields) showErrors(body.fields, true);
				fail(body.error || body.message || T.generic);
			} catch (err) {
				fail(timedOut ? T.timeout : !navigator.onLine || err instanceof TypeError ? T.offline : T.generic);
			} finally {
				clearTimeout(timer);
			}
		});
	}

	/* ---------- Enhancements loaded on demand ---------- */
	const idle = (fn, timeout = 1500) => ('requestIdleCallback' in window ? requestIdleCallback(fn, { timeout }) : setTimeout(fn, 200));
	const afterLoad = (fn) => (document.readyState === 'complete' ? idle(fn) : addEventListener('load', () => idle(fn), { once: true }));

	// GSAP + ScrollTrigger: scroll-linked timeline, parallax, 3D section transitions.
	if (!reduce && D.motion) {
		import(D.motion).then((m) => m.init(root)).catch(timelineStatic);
	} else {
		timelineStatic();
	}

	// Three.js hero scene: only with WebGL and on capable devices. Otherwise the SVG illustration stays.
	const stage = $('[data-srn-stage]');
	const webgl = (() => {
		try {
			const c = document.createElement('canvas');
			return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
		} catch (e) {
			return false;
		}
	})();
	const conn = navigator.connection || {};
	const lowEnd = conn.saveData || /(^|-)2g$/.test(conn.effectiveType || '') || (navigator.deviceMemory && navigator.deviceMemory < 4) || (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4);
	if (stage && D.scene && webgl && !lowEnd) {
		afterLoad(() => {
			import(D.scene)
				.then((m) => m.init(stage, { reduce, mobile: matchMedia('(max-width: 1100px)').matches, coarse: !finePointer }))
				.catch(() => {}); // The SVG fallback is already on screen.
		});
	}
})();
