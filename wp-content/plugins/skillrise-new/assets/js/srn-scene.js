/*
 * Skillrise /new — interactive 3D hero (Three.js r180, bundled in assets/vendor/three-r180/).
 * Loaded by srn-core.js with import(); a plain ES module, no build step.
 * All models are built from code (no model files to download).
 * Performance: capped pixel ratio, fewer objects on small screens, render loop runs only while the hero is
 * on screen and the tab is visible, a single still frame for reduced-motion users.
 */
import {
	ACESFilmicToneMapping, AdditiveBlending, AmbientLight, BoxGeometry, BufferGeometry, CanvasTexture, Color,
	CylinderGeometry, DirectionalLight, EdgesGeometry, ExtrudeGeometry, Float32BufferAttribute, Group,
	IcosahedronGeometry, InstancedMesh, LineBasicMaterial, LineSegments, MathUtils, Mesh, MeshBasicMaterial,
	MeshPhysicalMaterial, MeshStandardMaterial, Object3D, OctahedronGeometry, Path, PerspectiveCamera, PMREMGenerator,
	PointLight, Points, PointsMaterial, Raycaster, Scene, Shape, SphereGeometry, SRGBColorSpace, TetrahedronGeometry,
	TorusGeometry, Vector2, WebGLRenderer,
} from '../vendor/three-r180/three.module.min.js';
import { RoomEnvironment } from '../vendor/three-r180/addons/RoomEnvironment.js';

const BLUE = 0x2f6bff;
const VIOLET = 0x8b5cf6;
const CYAN = 0x22d3ee;
const ORANGE = 0xf97316;

const glass = (color, extra = {}) => new MeshPhysicalMaterial({ color, metalness: 0.25, roughness: 0.2, clearcoat: 1, clearcoatRoughness: 0.12, ...extra });
const metal = (color, extra = {}) => new MeshStandardMaterial({ color, metalness: 0.75, roughness: 0.28, ...extra });

/* ---------- Models ---------- */

function gear(teeth = 12, r = 0.9, depth = 0.3) {
	const s = new Shape();
	const inner = r * 0.8;
	for (let i = 0; i < teeth; i++) {
		const a = (i / teeth) * Math.PI * 2;
		const w = (Math.PI * 2) / teeth;
		const pts = [[inner, a], [r, a + w * 0.15], [r, a + w * 0.45], [inner, a + w * 0.6]];
		pts.forEach(([rad, ang], j) => {
			const x = Math.cos(ang) * rad;
			const y = Math.sin(ang) * rad;
			i === 0 && j === 0 ? s.moveTo(x, y) : s.lineTo(x, y);
		});
	}
	s.closePath();
	const hole = new Path();
	hole.absarc(0, 0, r * 0.32, 0, Math.PI * 2, true);
	s.holes.push(hole);
	const g = new ExtrudeGeometry(s, { depth, bevelEnabled: true, bevelThickness: 0.04, bevelSize: 0.03, bevelSegments: 2, curveSegments: 18 });
	g.center();
	return new Mesh(g, metal(0x9db4ff));
}

function chip() {
	const grp = new Group();
	grp.add(new Mesh(new BoxGeometry(1.2, 0.16, 1.2), metal(0x1b2346, { roughness: 0.45 })));
	const die = new Mesh(new BoxGeometry(0.6, 0.06, 0.6), glass(VIOLET, { emissive: 0x4c1d95, emissiveIntensity: 0.6 }));
	die.position.y = 0.1;
	grp.add(die);
	const pins = new InstancedMesh(new BoxGeometry(0.08, 0.05, 0.22), metal(0xffd27a, { metalness: 0.9, roughness: 0.2 }), 20);
	const o = new Object3D();
	let k = 0;
	for (let side = 0; side < 4; side++) {
		for (let i = 0; i < 5; i++) {
			const t = -0.44 + i * 0.22;
			const rot = (side * Math.PI) / 2;
			o.position.set(Math.cos(rot) * 0.68 - Math.sin(rot) * t, -0.02, Math.sin(rot) * 0.68 + Math.cos(rot) * t);
			o.rotation.set(0, -rot + Math.PI / 2, 0);
			o.updateMatrix();
			pins.setMatrixAt(k++, o.matrix);
		}
	}
	grp.add(pins);
	return grp;
}

function bolt() {
	const s = new Shape();
	[[0.15, 0.9], [-0.45, -0.05], [-0.05, -0.05], [-0.2, -0.9], [0.45, 0.15], [0.05, 0.15], [0.15, 0.9]].forEach(([x, y], i) => (i ? s.lineTo(x, y) : s.moveTo(x, y)));
	const g = new ExtrudeGeometry(s, { depth: 0.22, bevelEnabled: true, bevelThickness: 0.04, bevelSize: 0.03, bevelSegments: 2 });
	g.center();
	return new Mesh(g, glass(0xfacc15, { emissive: 0xb45309, emissiveIntensity: 0.35, metalness: 0.4 }));
}

function iBeam() {
	const s = new Shape();
	[[-0.45, 0.5], [0.45, 0.5], [0.45, 0.36], [0.09, 0.36], [0.09, -0.36], [0.45, -0.36], [0.45, -0.5], [-0.45, -0.5], [-0.45, -0.36], [-0.09, -0.36], [-0.09, 0.36], [-0.45, 0.36]].forEach(([x, y], i) => (i ? s.lineTo(x, y) : s.moveTo(x, y)));
	const g = new ExtrudeGeometry(s, { depth: 1.5, bevelEnabled: false });
	g.center();
	return new Mesh(g, metal(0x7dd3fc, { roughness: 0.35 }));
}

function codeBrackets() {
	const grp = new Group();
	const mat = glass(CYAN, { emissive: 0x0e7490, emissiveIntensity: 0.4 });
	const bar = new BoxGeometry(0.7, 0.14, 0.18);
	const chevron = (dir) => {
		const c = new Group();
		const a = new Mesh(bar, mat);
		const b = new Mesh(bar, mat);
		a.rotation.z = 0.7 * dir;
		b.rotation.z = -0.7 * dir;
		a.position.set(0, 0.22, 0);
		b.position.set(0, -0.22, 0);
		c.add(a, b);
		c.position.x = -0.62 * dir;
		return c;
	};
	grp.add(chevron(1), chevron(-1));
	const slash = new Mesh(new BoxGeometry(0.12, 1.05, 0.18), glass(VIOLET, { emissive: 0x4c1d95, emissiveIntensity: 0.4 }));
	slash.rotation.z = -0.35;
	grp.add(slash);
	return grp;
}

function gradCap() {
	const grp = new Group();
	const board = new Mesh(new BoxGeometry(1.3, 0.07, 1.3), metal(0x141a33, { roughness: 0.5, metalness: 0.4 }));
	board.rotation.y = Math.PI / 4;
	board.position.y = 0.32;
	const base = new Mesh(new CylinderGeometry(0.42, 0.48, 0.42, 28), metal(0x1d2550, { roughness: 0.5, metalness: 0.4 }));
	base.position.y = 0.08;
	const button = new Mesh(new SphereGeometry(0.07, 16, 12), metal(ORANGE));
	button.position.y = 0.38;
	const cord = new Mesh(new CylinderGeometry(0.018, 0.018, 0.6, 6), metal(ORANGE));
	cord.position.set(0.55, 0.08, 0);
	const tassel = new Mesh(new CylinderGeometry(0.03, 0.08, 0.22, 10), metal(ORANGE, { emissive: 0x9a3412, emissiveIntensity: 0.4 }));
	tassel.position.set(0.55, -0.26, 0);
	grp.add(board, base, button, cord, tassel);
	return grp;
}

function dotTexture() {
	const c = document.createElement('canvas');
	c.width = c.height = 64;
	const g = c.getContext('2d');
	const grd = g.createRadialGradient(32, 32, 0, 32, 32, 32);
	grd.addColorStop(0, 'rgba(255,255,255,1)');
	grd.addColorStop(0.35, 'rgba(255,255,255,.55)');
	grd.addColorStop(1, 'rgba(255,255,255,0)');
	g.fillStyle = grd;
	g.fillRect(0, 0, 64, 64);
	return new CanvasTexture(c);
}

/* ---------- Scene ---------- */

export function init(stage, { reduce = false, mobile = false, coarse = false } = {}) {
	const canvas = document.createElement('canvas');
	canvas.setAttribute('aria-hidden', 'true');
	let renderer;
	try {
		renderer = new WebGLRenderer({ canvas, antialias: !mobile, alpha: true, powerPreference: 'high-performance' });
	} catch (e) {
		return null;
	}
	renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, mobile ? 1.25 : 1.75));
	renderer.outputColorSpace = SRGBColorSpace;
	renderer.toneMapping = ACESFilmicToneMapping;
	renderer.toneMappingExposure = 1.15;
	renderer.setClearColor(0x000000, 0);
	stage.appendChild(canvas);

	const scene = new Scene();
	const camera = new PerspectiveCamera(36, 1, 0.1, 80);
	camera.position.set(0, 0, 13);

	// Soft studio reflections (generated, no HDR download). Skipped on small screens to save GPU time.
	if (!mobile) {
		const pmrem = new PMREMGenerator(renderer);
		scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
		pmrem.dispose();
	}
	scene.add(new AmbientLight(0x8090ff, mobile ? 0.9 : 0.35));
	const key = new DirectionalLight(0xffffff, 1.6);
	key.position.set(5, 7, 9);
	scene.add(key);
	const lBlue = new PointLight(BLUE, 60, 30, 1.6);
	lBlue.position.set(-6, 3, 5);
	const lViolet = new PointLight(VIOLET, 60, 30, 1.6);
	lViolet.position.set(6, -3, 4);
	scene.add(lBlue, lViolet);

	const world = new Group(); // placed per layout
	const rig = new Group(); // pointer + scroll rotation
	world.add(rig);
	scene.add(world);

	// Core: faceted icosahedron, glowing edges, faint outer lattice.
	const core = new Mesh(new IcosahedronGeometry(1.7, 0), new MeshPhysicalMaterial({ color: 0x4048e0, metalness: 0.3, roughness: 0.16, flatShading: true, clearcoat: 1, clearcoatRoughness: 0.1, emissive: 0x1d1a6b, emissiveIntensity: 0.5 }));
	const edges = new LineSegments(new EdgesGeometry(new IcosahedronGeometry(1.72, 0)), new LineBasicMaterial({ color: 0xa5c8ff, transparent: true, opacity: 0.8 }));
	core.add(edges);
	const lattice = new Mesh(new IcosahedronGeometry(2.45, 1), new MeshBasicMaterial({ color: 0x5b8cff, wireframe: true, transparent: true, opacity: 0.09 }));
	rig.add(core, lattice);

	// Orbit rings with satellites.
	const rings = [
		{ r: 3.2, tube: 0.02, color: CYAN, opacity: 0.6, rx: 1.22, rz: 0.25, speed: 0.5 },
		{ r: 3.9, tube: 0.013, color: VIOLET, opacity: 0.5, rx: 1.0, rz: -0.55, speed: -0.32 },
	].map((o) => {
		const pivot = new Group();
		pivot.rotation.set(o.rx, 0, o.rz);
		pivot.add(new Mesh(new TorusGeometry(o.r, o.tube, 8, 180), new MeshBasicMaterial({ color: o.color, transparent: true, opacity: o.opacity })));
		const sat = new Mesh(new SphereGeometry(0.09, 16, 12), new MeshBasicMaterial({ color: o.color }));
		const halo = new Mesh(new SphereGeometry(0.2, 16, 12), new MeshBasicMaterial({ color: o.color, transparent: true, opacity: 0.25, blending: AdditiveBlending, depthWrite: false }));
		sat.add(halo);
		pivot.add(sat);
		rig.add(pivot);
		return { ...o, pivot, sat };
	});

	// Floating training objects: [model, position, scale, spin speed]. Small screens get the five main ones.
	const defs = [
		[gear(), [-3.6, 1.9, 0.6], 1, 0.6],
		[chip(), [3.7, -1.7, 0.8], 1, 0.35],
		[gradCap(), [3.1, 2.4, -0.4], 1, 0.3],
		[codeBrackets(), [-3.3, -2.0, 0.9], 0.9, 0.4],
		[bolt(), [4.3, 0.5, -1.2], 0.85, 0.45],
		[iBeam(), [-4.4, 0.1, -1.6], 0.7, 0.3],
		[new Mesh(new OctahedronGeometry(0.42), glass(BLUE, { emissive: 0x1e3a8a, emissiveIntensity: 0.5 })), [0.6, 3.4, -1.5], 1, 0.8],
		[new Mesh(new TetrahedronGeometry(0.4), glass(VIOLET, { emissive: 0x4c1d95, emissiveIntensity: 0.5 })), [-1.2, -3.3, -0.8], 1, 0.9],
		[new Mesh(new TorusGeometry(0.32, 0.1, 12, 40), glass(CYAN, { emissive: 0x0e7490, emissiveIntensity: 0.4 })), [1.8, -3.1, 1.2], 1, 0.7],
	];
	const floaters = (mobile ? defs.slice(0, 5) : defs).map(([obj, pos, scale, spin], i) => {
		const holder = new Group();
		holder.add(obj);
		holder.position.set(...pos);
		holder.scale.setScalar(scale);
		obj.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
		holder.userData = { base: new Vector2(pos[0], pos[1]), z: pos[2], scale, spin, phase: i * 1.7, hover: 0, obj };
		obj.traverse((m) => (m.userData.holder = holder));
		rig.add(holder);
		return holder;
	});

	// Particles in a loose shell.
	const count = mobile ? 260 : 700;
	const pos = new Float32Array(count * 3);
	for (let i = 0; i < count; i++) {
		const r = 5 + Math.random() * 9;
		const t = Math.random() * Math.PI * 2;
		const p = Math.acos(2 * Math.random() - 1);
		pos.set([r * Math.sin(p) * Math.cos(t), r * Math.sin(p) * Math.sin(t) * 0.7, r * Math.cos(p) - 2], i * 3);
	}
	const pGeo = new BufferGeometry();
	pGeo.setAttribute('position', new Float32BufferAttribute(pos, 3));
	const particles = new Points(pGeo, new PointsMaterial({ size: mobile ? 0.09 : 0.07, map: dotTexture(), color: new Color(0xa9bfff), transparent: true, opacity: 0.85, blending: AdditiveBlending, depthWrite: false }));
	scene.add(particles);

	/* ---------- Layout ---------- */
	const hero = stage.closest('section') || stage;
	let wide = true;
	const layout = () => {
		const w = stage.clientWidth || 1;
		const h = stage.clientHeight || 1;
		renderer.setSize(w, h, false);
		camera.aspect = w / h;
		camera.updateProjectionMatrix();
		const halfH = Math.tan(MathUtils.degToRad(camera.fov / 2)) * camera.position.z;
		const halfW = halfH * camera.aspect;
		wide = w > 1100;
		const unit = (2 * halfH) / h; // world units per CSS pixel at z = 0
		if (wide) {
			// Right half of the hero, clear of the headline.
			world.position.set(halfW * 0.5, -0.1, 0);
			world.scale.setScalar(Math.min(0.95, (w * 0.5 * unit) / 2 / 5.2));
		} else {
			// Small screens: centred in the band between the header and the headline (see --stage-h in the CSS).
			const copy = hero.querySelector('.srn-hero-copy');
			const header = 72;
			const bandBottom = copy ? copy.getBoundingClientRect().top - stage.getBoundingClientRect().top + parseFloat(getComputedStyle(copy).paddingTop) : h * 0.4;
			const mid = (header + bandBottom) / 2;
			world.position.set(0, halfH - mid * unit, 0);
			world.scale.setScalar(Math.min((w * unit) / 2 / 4.9, ((bandBottom - header) * unit) / 2 / 4.6));
		}
		// Floaters on the text side tuck in on wide screens.
		floaters.forEach((f) => (f.userData.squeeze = wide && f.userData.base.x < 0 ? 0.62 : 1));
	};

	/* ---------- Input ---------- */
	const pointer = new Vector2(0, 0); // -1..1, eased
	const target = new Vector2(0, 0);
	const ndc = new Vector2(9, 9);
	const ray = new Raycaster();
	let hovered = null;
	const onMove = (e) => {
		if (e.pointerType !== 'mouse') return;
		target.set((e.clientX / innerWidth) * 2 - 1, (e.clientY / innerHeight) * 2 - 1);
		const r = canvas.getBoundingClientRect();
		ndc.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
	};
	if (!coarse && !reduce) addEventListener('pointermove', onMove, { passive: true });

	let scrollP = 0;
	const onScroll = () => {
		const h = hero.offsetHeight || innerHeight;
		scrollP = Math.min(1, Math.max(0, scrollY / h));
	};
	addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	/* ---------- Loop ---------- */
	let running = false;
	let frameId = 0;
	let last = performance.now();
	let t = 0;
	let frame = 0;

	const update = (dt) => {
		t += dt;
		pointer.lerp(target, 1 - Math.pow(0.001, dt));
		const s = scrollP;

		rig.rotation.y = pointer.x * 0.35 + t * 0.06 + s * 1.1;
		rig.rotation.x = pointer.y * 0.22 + s * 0.5;
		camera.position.z = 13 + s * 5;
		camera.position.y = -s * 1.5;
		camera.lookAt(world.position.x * 0.15, world.position.y * 0.15, 0);

		core.rotation.y += dt * 0.25;
		core.rotation.x += dt * 0.12;
		lattice.rotation.y -= dt * 0.05;
		lattice.rotation.z += dt * 0.03;
		const pulse = 0.5 + Math.sin(t * 1.6) * 0.15;
		core.material.emissiveIntensity = pulse;

		rings.forEach((rg) => {
			const a = t * rg.speed;
			rg.sat.position.set(Math.cos(a) * rg.r, Math.sin(a) * rg.r, 0);
			rg.pivot.rotation.y += dt * 0.04 * Math.sign(rg.speed);
		});

		// Hover: raycast every 3rd frame (mouse only).
		if (!coarse && frame % 3 === 0 && ndc.x < 2) {
			ray.setFromCamera(ndc, camera);
			const hit = ray.intersectObjects(floaters, true)[0];
			hovered = hit ? hit.object.userData.holder || null : null;
		}

		const spread = 1 + s * 0.9;
		floaters.forEach((f) => {
			const u = f.userData;
			u.hover += ((f === hovered ? 1 : 0) - u.hover) * Math.min(1, dt * 8);
			f.position.set(u.base.x * u.squeeze * spread, u.base.y * spread + Math.sin(t * 0.9 + u.phase) * 0.18, u.z + s * 2);
			f.scale.setScalar(u.scale * (1 + u.hover * 0.28));
			u.obj.rotation.y += dt * u.spin * (1 + u.hover * 5);
			u.obj.rotation.x += dt * u.spin * 0.4;
		});

		particles.rotation.y = t * 0.015 + pointer.x * 0.08;
		particles.rotation.x = pointer.y * 0.05;
	};

	const render = () => renderer.render(scene, camera);
	const loop = (now) => {
		const dt = Math.min(0.05, (now - last) / 1000);
		last = now;
		frame++;
		update(dt);
		render();
		if (running) frameId = requestAnimationFrame(loop);
	};
	const start = () => {
		if (running || reduce) return;
		running = true;
		last = performance.now();
		frameId = requestAnimationFrame(loop);
	};
	const stop = () => {
		running = false;
		cancelAnimationFrame(frameId);
	};

	layout();
	if (reduce) {
		// One still frame; re-render only when the size changes.
		update(0);
		render();
	}
	canvas.style.cursor = 'default';
	stage.classList.add('is-3d');

	let visible = true;
	const sync = () => (visible && !document.hidden ? start() : stop());
	const io = new IntersectionObserver(([e]) => {
		visible = e.isIntersecting;
		sync();
	});
	io.observe(hero);
	document.addEventListener('visibilitychange', sync);

	const ro = new ResizeObserver(() => {
		layout();
		if (!running) render();
	});
	ro.observe(stage);

	// Context loss (e.g. GPU reset on mobile): show the SVG again.
	canvas.addEventListener('webglcontextlost', (e) => {
		e.preventDefault();
		stop();
		stage.classList.remove('is-3d');
	});

	return {
		dispose() {
			stop();
			io.disconnect();
			ro.disconnect();
			removeEventListener('pointermove', onMove);
			removeEventListener('scroll', onScroll);
			document.removeEventListener('visibilitychange', sync);
			renderer.dispose();
			canvas.remove();
		},
	};
}
