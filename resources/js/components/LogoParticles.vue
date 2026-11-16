<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { prefersReducedMotion } from '@/lib/motion';

/**
 * The S mark rebuilt from particles sampled off the real logo paths.
 * They assemble on load, scatter from the cursor, burst on click and
 * dissolve as the hero scrolls away. Pauses when off-screen; reduced
 * motion gets a still render.
 */
const props = defineProps({ size: { type: Number, default: 540 } });

const canvas = ref(null);
const PATHS = [
    { d: 'M48 12H26A10 10 0 0 0 16 22', width: 8.5, tone: 'light' },
    { d: 'M19.5 28.5 44.5 35.5', width: 9.5, tone: 'accent' },
    { d: 'M48 42A10 10 0 0 1 38 52H16', width: 8.5, tone: 'light' },
];

let ctx;
let raf = 0;
let running = false;
let visible = true;
let particles = [];
let groups = [];
let dpr = 1;
let px = 0; // canvas size in device pixels
let rect = { left: 0, top: 0, width: 0, height: 0 };
const pointer = { x: -9999, y: -9999, active: false };
let scatter = 0; // 0 = assembled, 1 = fully dissolved (driven by scroll)
const cleanups = [];

const cssVar = (name, fallback) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;

/** Draw the logo off-screen and turn its filled pixels into particle homes. */
function sample() {
    const off = document.createElement('canvas');
    off.width = off.height = px;
    const o = off.getContext('2d', { willReadFrequently: true });
    const scale = px / 64;
    o.scale(scale, scale);
    o.lineCap = 'round';
    PATHS.forEach((p) => {
        o.lineWidth = p.width;
        o.strokeStyle = p.tone === 'accent' ? '#ff0000' : '#0000ff'; // channel tags, not the final colours
        o.stroke(new Path2D(p.d));
    });

    const data = o.getImageData(0, 0, px, px).data;
    const step = Math.max(4, Math.round(px / 118));
    const list = [];
    for (let y = step / 2; y < px; y += step) {
        for (let x = step / 2; x < px; x += step) {
            const i = (Math.floor(y) * px + Math.floor(x)) * 4;
            if (data[i + 3] < 128) continue;
            const accent = data[i] > data[i + 2];
            const jitter = step * 0.35;
            const hx = x + (Math.random() - 0.5) * jitter;
            const hy = y + (Math.random() - 0.5) * jitter;
            const angle = Math.random() * Math.PI * 2;
            const far = px * (0.45 + Math.random() * 0.6);
            list.push({
                hx, hy,
                // Where this particle drifts to when the logo dissolves.
                sx: px / 2 + Math.cos(angle) * far,
                sy: px / 2 + Math.sin(angle) * far,
                x: px / 2 + Math.cos(angle) * far * 1.4,
                y: px / 2 + Math.sin(angle) * far * 1.4,
                vx: 0, vy: 0,
                size: step * (0.42 + Math.random() * 0.3),
                group: (accent ? 0 : 3) + Math.floor(Math.random() * 3),
                spring: 0.035 + Math.random() * 0.03,
            });
        }
    }
    return list;
}

function setup() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    px = Math.round(props.size * dpr);
    const el = canvas.value;
    el.width = el.height = px;
    ctx = el.getContext('2d');

    particles = sample();
    const accent = cssVar('--accent-hot', '#ff3b5c');
    const light = '#f4f1ee';
    groups = [
        [accent, 1], [accent, 0.75], [accent, 0.5],
        [light, 0.95], [light, 0.7], [light, 0.45],
    ].map(([color, alpha]) => ({ color, alpha, items: [] }));
    particles.forEach((p) => groups[p.group].items.push(p));
}

function measure() {
    const r = canvas.value.getBoundingClientRect();
    rect = { left: r.left, top: r.top, width: r.width, height: r.height };
    // Dissolve as the canvas scrolls up past the top of the viewport.
    scatter = Math.min(1, Math.max(0, -r.top / (r.height * 0.85)));
}

function step() {
    measure();
    const radius = px * 0.16;
    const r2 = radius * radius;
    const mx = ((pointer.x - rect.left) / rect.width) * px;
    const my = ((pointer.y - rect.top) / rect.height) * px;
    const ease = scatter * scatter;

    for (const p of particles) {
        const tx = p.hx + (p.sx - p.hx) * ease;
        const ty = p.hy + (p.sy - p.hy) * ease;
        p.vx += (tx - p.x) * p.spring;
        p.vy += (ty - p.y) * p.spring;

        if (pointer.active) {
            const dx = p.x - mx;
            const dy = p.y - my;
            const d2 = dx * dx + dy * dy;
            if (d2 < r2 && d2 > 0.01) {
                const d = Math.sqrt(d2);
                const force = (1 - d / radius) * 2.6;
                p.vx += (dx / d) * force;
                p.vy += (dy / d) * force;
            }
        }

        p.vx *= 0.86;
        p.vy *= 0.86;
        p.x += p.vx;
        p.y += p.vy;
    }

    ctx.clearRect(0, 0, px, px);
    const fade = 1 - ease * 0.85;
    for (const g of groups) {
        ctx.globalAlpha = g.alpha * fade;
        ctx.fillStyle = g.color;
        for (const p of g.items) ctx.fillRect(p.x - p.size / 2, p.y - p.size / 2, p.size, p.size);
    }
    ctx.globalAlpha = 1;
}

function loop() {
    if (!running) return;
    step();
    raf = requestAnimationFrame(loop);
}

function start() {
    if (running || !visible || document.hidden) return;
    running = true;
    raf = requestAnimationFrame(loop);
}

function stop() {
    running = false;
    cancelAnimationFrame(raf);
}

function burst(e) {
    if (e.clientX < rect.left || e.clientX > rect.left + rect.width || e.clientY < rect.top || e.clientY > rect.top + rect.height) return;
    const mx = ((e.clientX - rect.left) / rect.width) * px;
    const my = ((e.clientY - rect.top) / rect.height) * px;
    for (const p of particles) {
        const dx = p.x - mx;
        const dy = p.y - my;
        const d = Math.sqrt(dx * dx + dy * dy) || 1;
        const force = Math.max(0, 1 - d / (px * 0.6)) * 38;
        p.vx += (dx / d) * force * (0.6 + Math.random() * 0.8);
        p.vy += (dy / d) * force * (0.6 + Math.random() * 0.8);
    }
}

const on = (target, event, fn, opts) => {
    target.addEventListener(event, fn, opts);
    cleanups.push(() => target.removeEventListener(event, fn, opts));
};

onMounted(() => {
    setup();

    if (prefersReducedMotion()) {
        particles.forEach((p) => { p.x = p.hx; p.y = p.hy; });
        scatter = 0;
        step();
        return;
    }

    on(window, 'pointermove', (e) => { pointer.x = e.clientX; pointer.y = e.clientY; pointer.active = e.pointerType === 'mouse'; }, { passive: true });
    on(document, 'pointerleave', () => (pointer.active = false));
    on(window, 'pointerdown', burst, { passive: true });
    on(document, 'visibilitychange', () => (document.hidden ? stop() : start()));

    const io = new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        visible ? start() : stop();
    }, { rootMargin: '100px' });
    io.observe(canvas.value);
    cleanups.push(() => io.disconnect());

    start();
});

onBeforeUnmount(() => {
    stop();
    cleanups.forEach((fn) => fn());
});
</script>

<template>
    <canvas ref="canvas" :style="{ width: `${size}px`, height: `${size}px` }" aria-hidden="true"></canvas>
</template>
