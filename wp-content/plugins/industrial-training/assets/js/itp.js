/* Industrial Training landing page — vanilla ES2020, no dependencies. */
(() => {
	const root = document.querySelector('[data-itp]');
	if (! root) return;

	const D = window.itpData || { i18n: {} };
	const T = D.i18n;
	const $ = (s, c = root) => c.querySelector(s);
	const $$ = (s, c = root) => [...c.querySelectorAll(s)];
	const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const hasIO = 'IntersectionObserver' in window;

	// Scroll: progress bar, header shadow, back-to-top
	const header = $('.itp-header');
	const bar = $('.itp-progress');
	const toTop = $('.itp-top');
	let ticking = false;
	let lastY = scrollY;
	const onScroll = () => {
		ticking = false;
		const y = scrollY;
		// Hide the header while scrolling down, bring it back as soon as the visitor scrolls up.
		if (Math.abs(y - lastY) > 6) {
			header.classList.toggle('is-hidden', y > lastY && y > 400 && ! root.querySelector('dialog[open]'));
			lastY = y;
		}
		const max = document.documentElement.scrollHeight - innerHeight;
		bar.style.transform = `scaleX(${max > 0 ? Math.min(1, y / max) : 0 })`;
		header.classList.toggle('is-scrolled', y > 8);
		toTop.classList.toggle('is-visible', y > 700);
	};
	addEventListener('scroll', () => ticking || (ticking = requestAnimationFrame(onScroll)), { passive: true });
	onScroll();

	// Scroll-spy
	if (hasIO) {
		const links = $$('.itp-nav a');
		const spy = new IntersectionObserver((entries) => {
			entries.forEach((e) => {
				const link = links.find((a) => a.hash === '#' + e.target.id);
				if (e.isIntersecting) {
					links.forEach((a) => a.removeAttribute('aria-current'));
					link?.setAttribute('aria-current', 'location');
				} else {
					link?.removeAttribute('aria-current');
				}
			});
		}, { rootMargin: '-50% 0px -50% 0px' });
		$$('[data-itp-spy]').forEach((s) => spy.observe(s));
	}

	// Motion
	if (! reduce && hasIO) {
		root.classList.add('motion-ready');

		const reveal = new IntersectionObserver((entries) => {
			let n = 0;
			entries.forEach((e) => {
				if (! e.isIntersecting) return;
				e.target.style.setProperty('--d', n++ * 90 + 'ms');
				e.target.classList.add('itp-in');
				reveal.unobserve(e.target);
			});
		}, { threshold: 0.12 });
		$$('.itp-reveal').forEach((el) => reveal.observe(el));

		// Count-up: "2,400+" → prefix, number, suffix. The final value is in a sibling .itp-sr span.
		const counters = $$('.itp-count').map((el) => {
			const m = el.textContent.trim().match(/^(\D*)([\d,]*\.?\d+)(.*)$/);
			if (! m) return null;
			const end = parseFloat(m[2].replace(/,/g, ''));
			const dec = (m[2].split('.')[1] || '').length;
			const fmt = (v) => m[1] + (m[2].includes(',') ? v.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec }) : v.toFixed(dec)) + m[3];
			el.textContent = fmt(0);
			return { el, end, fmt };
		}).filter(Boolean);
		const count = new IntersectionObserver((entries) => {
			entries.forEach((e) => {
				if (! e.isIntersecting) return;
				count.unobserve(e.target);
				const c = counters.find((x) => x.el === e.target);
				const t0 = performance.now();
				const step = (now) => {
					const p = Math.min(1, (now - t0) / 1600);
					c.el.textContent = c.fmt(c.end * (1 - Math.pow(1 - p, 3)));
					if (p < 1) requestAnimationFrame(step);
				};
				requestAnimationFrame(step);
			});
		}, { threshold: 0.5 });
		counters.forEach((c) => count.observe(c.el));

		// Mouse parallax on the hero collage.
		const hero = $('.itp-hero');
		const collage = $('[data-itp-parallax]');
		if (collage && matchMedia('(hover: hover) and (pointer: fine)').matches) {
			let frame = 0;
			const set = (x, y) => {
				collage.style.setProperty('--rx', (-y * 3.5).toFixed(2) + 'deg');
				collage.style.setProperty('--ry', (x * 3.5).toFixed(2) + 'deg');
				collage.style.setProperty('--tx', (x * 13).toFixed(1) + 'px');
				collage.style.setProperty('--ty', (y * 11).toFixed(1) + 'px');
			};
			hero.addEventListener('pointermove', (e) => {
				if (e.pointerType !== 'mouse' || frame) return;
				frame = requestAnimationFrame(() => {
					frame = 0;
					const r = collage.getBoundingClientRect();
					const clamp = (v) => Math.max(-1, Math.min(1, v));
					set(clamp((e.clientX - r.left - r.width / 2) / (r.width / 2)), clamp((e.clientY - r.top - r.height / 2) / (r.height / 2)));
				});
			});
			hero.addEventListener('pointerleave', () => set(0, 0));
		}
	}

	// Carousels: native scroll-snap track + prev/next + dots. No autoplay.
	$$('[data-itp-carousel]').forEach((car) => {
		const track = $('.itp-car-track', car);
		const slides = [...track.children];
		const prev = $('[data-itp-prev]', car);
		const next = $('[data-itp-next]', car);
		const dots = $('[data-itp-dots]', car);
		if (!slides.length || !prev) return;
		let per = 1;
		let pages = 1;
		const pageOf = () => Math.round((track.scrollLeft / Math.max(1, track.scrollWidth - track.clientWidth)) * (pages - 1));
		const go = (p) => {
			const target = slides[Math.max(0, Math.min(p, pages - 1)) * per];
			track.scrollTo({ left: target.offsetLeft - slides[0].offsetLeft, behavior: reduce ? 'auto' : 'smooth' });
		};
		const update = () => {
			const p = pageOf();
			const end = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
			prev.setAttribute('aria-disabled', String(track.scrollLeft <= 2));
			next.setAttribute('aria-disabled', String(end));
			[...dots.children].forEach((d, i) => d.setAttribute('aria-current', String(i === p)));
		};
		const build = () => {
			per = Math.max(1, Math.round(track.clientWidth / slides[0].getBoundingClientRect().width));
			pages = Math.ceil(slides.length / per);
			car.classList.toggle('is-static', pages < 2);
			dots.replaceChildren(...Array.from({ length: pages }, (_, i) => {
				const b = document.createElement('button');
				b.type = 'button';
				b.setAttribute('aria-label', (T.slide || 'Go to page %d').replace('%d', i + 1));
				b.addEventListener('click', () => go(i));
				return b;
			}));
			update();
		};
		prev.addEventListener('click', () => prev.getAttribute('aria-disabled') !== 'true' && go(pageOf() - 1));
		next.addEventListener('click', () => next.getAttribute('aria-disabled') !== 'true' && go(pageOf() + 1));
		let raf = 0;
		track.addEventListener('scroll', () => raf || (raf = requestAnimationFrame(() => { raf = 0; update(); })), { passive: true });
		if ('ResizeObserver' in window) new ResizeObserver(build).observe(track);
		build();
		car.classList.add('is-ready');
	});

	// Registration modal
	const dlg = $('#itp-register');
	if (! dlg || typeof dlg.showModal !== 'function') return;

	const form = $('.itp-form', dlg);
	const fieldset = $('.itp-fields', dlg);
	const alertBox = $('.itp-form-error', dlg);
	const submit = $('.itp-submit', dlg);
	const label = $('.itp-submit-label', dlg);
	const view = $('.itp-form-view', dlg);
	const done = $('.itp-success', dlg);
	const FIELDS = ['fullName', 'email', 'phone', 'college', 'branch', 'year', 'track'];
	const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	const PHONE = /^\+?[0-9 ()-]{7,20}$/;
	let opener = null;
	let controller = null;
	let submitted = false;
	let closing = false;
	let downOnBackdrop = false;

	const data = () => {
		const d = {};
		new FormData(form).forEach((v, k) => (d[k] = String(v).trim()));
		return d;
	};
	const allowed = (name, v) => v !== '' && [...form.elements[name].options].some((o) => o.value === v);
	const validate = (d) => {
		const e = {};
		if (! d.fullName) e.fullName = T.fullName;
		if (! EMAIL.test(d.email)) e.email = T.email;
		if (! PHONE.test(d.phone)) e.phone = T.phone;
		if (! d.college) e.college = T.college;
		['branch', 'year', 'track'].forEach((k) => allowed(k, d[k]) || (e[k] = T[k]));
		return e;
	};
	const showErrors = (errors, shake) => {
		FIELDS.forEach((f) => {
			const el = form.elements[f];
			const msg = $('#itp-err-' + f, dlg);
			if (errors[f]) {
				el.setAttribute('aria-invalid', 'true');
				if (msg.textContent !== errors[f]) msg.textContent = errors[f];
				if (shake && ! reduce) {
					el.classList.remove('itp-shake');
					void el.offsetWidth;
					el.classList.add('itp-shake');
				}
			} else {
				el.removeAttribute('aria-invalid');
				msg.textContent = '';
			}
		});
		const first = FIELDS.find((f) => errors[f]);
		if (shake && first) form.elements[first].focus();
		return !! first;
	};
	const busy = (on) => {
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

	const open = (btn) => {
		if (dlg.open) return;
		opener = btn;
		reset();
		if (btn.dataset.track) form.elements.track.value = btn.dataset.track;
		dlg.showModal();
		form.elements.fullName.focus();
	};
	const close = () => {
		if (! dlg.open || closing) return;
		controller?.abort();
		if (reduce) return dlg.close();
		closing = true;
		dlg.classList.add('is-closing');
		setTimeout(() => dlg.close(), 180);
	};

	root.addEventListener('click', (e) => {
		const o = e.target.closest('[data-itp-open]');
		if (o) open(o);
		else if (e.target.closest('[data-itp-close]')) close();
	});
	dlg.addEventListener('pointerdown', (e) => (downOnBackdrop = e.target === dlg));
	dlg.addEventListener('click', (e) => e.target === dlg && downOnBackdrop && close());
	dlg.addEventListener('cancel', (e) => {
		e.preventDefault();
		close();
	});
	dlg.addEventListener('close', () => {
		controller?.abort();
		closing = false;
		dlg.classList.remove('is-closing');
		opener?.focus();
	});

	const live = () => submitted && showErrors(validate(data()), false);
	form.addEventListener('input', live);
	form.addEventListener('change', live);

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		if (submit.disabled) return;
		submitted = true;
		const d = data();
		if (showErrors(validate(d), true)) return;

		alertBox.textContent = '';
		busy(true);
		const ctrl = (controller = new AbortController());
		let timedOut = false;
		const timer = setTimeout(() => {
			timedOut = true;
			ctrl.abort();
		}, 15000);

		try {
			const res = await fetch(D.endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': D.nonce },
				body: JSON.stringify(d),
				signal: ctrl.signal,
			});
			const body = await res.json().catch(() => ({}));
			if (res.ok) return success(d);
			if (body.fields) showErrors(body.fields, true);
			fail(body.error || body.message || T.generic);
		} catch (err) {
			if (! timedOut && ctrl.signal.aborted) return; // Modal was closed.
			fail(timedOut ? T.timeout : ! navigator.onLine || err instanceof TypeError ? T.offline : T.generic);
		} finally {
			clearTimeout(timer);
			if (controller === ctrl) controller = null;
		}
	});

	const success = (d) => {
		busy(false);
		const fill = (s) => s.replace(/\{name\}/g, d.fullName.split(/\s+/)[0]).replace(/\{track\}/g, d.track).replace(/\{phone\}/g, d.phone);
		const title = $('.itp-success-title', dlg);
		title.textContent = fill(dlg.dataset.successTitle);
		$('.itp-success-text', dlg).textContent = fill(dlg.dataset.successText);
		view.hidden = true;
		done.hidden = false;
		title.focus();
	};
})();
