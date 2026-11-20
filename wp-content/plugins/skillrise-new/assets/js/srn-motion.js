/*
 * Skillrise /new — scroll-linked motion (GSAP + ScrollTrigger). A plain ES module, no build step. Loaded by core.js only when the
 * visitor has not asked for reduced motion. Everything here is enhancement: the page is complete without it.
 */
// GSAP 3.15.1 + ScrollTrigger: the official prebuilt files in assets/vendor/gsap-3.15.1/ (they set window.gsap / window.ScrollTrigger).
const VENDOR = new URL('../vendor/gsap-3.15.1/', import.meta.url);
const loadScript = (file) => new Promise((resolve, reject) => {
	const s = document.createElement('script');
	s.src = new URL(file, VENDOR).href;
	s.async = false;
	s.onload = resolve;
	s.onerror = () => reject(new Error('Could not load ' + file));
	document.head.appendChild(s);
});

export async function init(root) {
	if (!window.gsap) await loadScript('gsap.min.js');
	if (!window.ScrollTrigger) await loadScript('ScrollTrigger.min.js');
	const { gsap, ScrollTrigger } = window;
	gsap.registerPlugin(ScrollTrigger);
	run(root, gsap, ScrollTrigger);
}

function run(root, gsap, ScrollTrigger) {
	const q = (s) => root.querySelector(s);
	const qa = (s) => [...root.querySelectorAll(s)];
	const mm = gsap.matchMedia();
	// Elements GSAP drives must not also run CSS transform transitions (they would lag behind the scrub).
	const own = (els) => [].concat(els).filter(Boolean).forEach((e) => e.classList.add('srn-gsap'));
	const release = (els) => els.forEach((e) => e.classList.remove('srn-gsap'));

	/* Hero: copy and stats drift up and fade as the hero scrolls away. */
	const hero = q('.srn-hero');
	if (hero) {
		own([q('.srn-hero-copy'), q('.srn-hero-stats'), ...qa('.srn-hero-chips li')]);
		const st = { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.5 };
		// Explicit start values: the CSS entrance may still be running when this initialises.
		gsap.fromTo(q('.srn-hero-copy'), { yPercent: 0, autoAlpha: 1 }, { yPercent: -14, autoAlpha: 0.15, ease: 'none', immediateRender: false, scrollTrigger: st });
		gsap.fromTo(q('.srn-hero-stats'), { y: 0, autoAlpha: 1 }, { y: -60, autoAlpha: 0, ease: 'none', immediateRender: false, scrollTrigger: { ...st, end: '60% top' } });
		gsap.to(qa('.srn-hero-chips li'), { y: (i) => -120 - i * 60, ease: 'none', scrollTrigger: st });
	}

	/* Section glows: slow parallax for depth. */
	qa('.srn-section-bg .srn-glow').forEach((g) => {
		gsap.fromTo(g, { yPercent: -20 }, { yPercent: 25, ease: 'none', scrollTrigger: { trigger: g.closest('section'), start: 'top bottom', end: 'bottom top', scrub: true } });
	});

	/* About: the brand card turns in 3D as it scrolls through. */
	const about = q('.srn-about-visual');
	if (about) {
		own(about);
		gsap.fromTo(about, { rotateY: -14, rotateX: 6, transformPerspective: 1200 }, { rotateY: 8, rotateX: -2, ease: 'none', scrollTrigger: { trigger: about, start: 'top bottom', end: 'bottom top', scrub: 0.6 } });
	}

	/* Program cards: staggered 3D flip-in (inner card; the <li> fades via the CSS reveal). */
	ScrollTrigger.batch(qa('.srn-track-card'), {
		start: 'top 88%',
		once: true,
		onEnter: (els) => {
			own(els);
			gsap.from(els, { rotateX: 22, y: 70, transformPerspective: 1000, transformOrigin: '50% 100%', duration: 1.1, ease: 'power3.out', stagger: 0.12, clearProps: 'transform', onComplete: () => release(els) });
		},
	});

	/* Why cards: rise with a slight rotation, staggered. */
	ScrollTrigger.batch(qa('.srn-why-card'), {
		start: 'top 90%',
		once: true,
		onEnter: (els) => {
			own(els);
			gsap.from(els, { rotateY: -18, z: -80, transformPerspective: 900, duration: 1, ease: 'power3.out', stagger: 0.1, clearProps: 'transform', onComplete: () => release(els) });
		},
	});

	/* Learning journey: rail fills with scroll, steps light up as they are reached. */
	const timeline = q('[data-srn-timeline]');
	const fill = q('[data-srn-fill]');
	if (timeline && fill) {
		gsap.fromTo(fill, { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: timeline, start: 'top 65%', end: 'bottom 60%', scrub: 0.4 } });
		qa('[data-srn-step]').forEach((step) => {
			ScrollTrigger.create({
				trigger: step,
				start: 'top 62%',
				onEnter: () => step.classList.add('is-active'),
				onLeaveBack: () => step.classList.remove('is-active'),
			});
		});
		mm.add('(min-width: 1000px)', () => {
			qa('[data-srn-step]').forEach((step, i) => {
				own(step.querySelector('.srn-step-card'));
				gsap.from(step.querySelector('.srn-step-card'), { x: i % 2 ? 80 : -80, rotateY: i % 2 ? -12 : 12, transformPerspective: 1000, ease: 'power2.out', scrollTrigger: { trigger: step, start: 'top 90%', end: 'top 55%', scrub: 0.6 } });
			});
		});
	}

	/* Skills orbit: grows and tilts in as it enters. */
	const orbit = q('.srn-orbit-wrap');
	if (orbit) {
		own(orbit);
		gsap.fromTo(orbit, { scale: 0.75, rotateZ: -12 }, { scale: 1, rotateZ: 0, ease: 'none', scrollTrigger: { trigger: orbit, start: 'top bottom', end: 'center center', scrub: 0.6 } });
	}

	/* Benefits CTA band + NAPS banner: fold up from a 3D tilt. */
	qa('.srn-cta-band, .srn-naps').forEach((el) => {
		own(el);
		gsap.fromTo(el, { rotateX: 28, scale: 0.94, transformPerspective: 1100, transformOrigin: '50% 100%' }, { rotateX: 0, scale: 1, ease: 'none', scrollTrigger: { trigger: el, start: 'top bottom', end: 'top 60%', scrub: 0.5 } });
	});

	/* Registration card: lifts into place. */
	const formCard = q('.srn-form-card');
	if (formCard) {
		own(formCard);
		gsap.fromTo(formCard, { rotateX: 10, y: 60, transformPerspective: 1200 }, { rotateX: 0, y: 0, ease: 'none', scrollTrigger: { trigger: formCard, start: 'top bottom', end: 'top 55%', scrub: 0.5 } });
	}

	// Images and fonts change layout after load; keep trigger positions right.
	addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
	document.fonts?.ready.then(() => ScrollTrigger.refresh());
}
