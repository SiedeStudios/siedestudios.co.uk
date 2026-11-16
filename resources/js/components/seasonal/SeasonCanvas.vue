<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Full-screen seasonal particles: snow, embers or fireworks.
 * Transparent overlay, never blocks clicks, pauses when the tab is hidden.
 */
const props = defineProps({ mode: { type: String, required: true } });

const canvas = ref(null);
let ctx, w, h, dpr, raf = 0, last = 0, running = false;
let flakes = [], embers = [], rockets = [], sparks = [];
let nextLaunch = 0;
let wind = 0, windTarget = 0;
const cleanups = [];

const rand = (a, b) => a + Math.random() * (b - a);
const FIREWORK_COLOURS = ['#ff4d6d', '#ffd166', '#ffffff', '#ff8fa3', '#ffb347'];

function resize() {
    dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    w = window.innerWidth;
    h = window.innerHeight;
    canvas.value.width = w * dpr;
    canvas.value.height = h * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
}

function seed() {
    flakes = [];
    embers = [];
    rockets = [];
    sparks = [];
    if (props.mode === 'snow') {
        const n = Math.min(160, Math.round(w / 9));
        for (let i = 0; i < n; i++) flakes.push(newFlake(true));
    }
    if (props.mode === 'embers') {
        const n = Math.min(70, Math.round(w / 22));
        for (let i = 0; i < n; i++) embers.push(newEmber(true));
    }
    nextLaunch = 0;
}

const newFlake = (anywhere = false) => {
    const r = rand(0.8, 3.2);
    return { x: rand(0, w), y: anywhere ? rand(-h, h) : rand(-40, -5), r, vy: 18 + r * 14, phase: rand(0, Math.PI * 2), sway: rand(10, 28), a: rand(0.35, 0.85) };
};
const newEmber = (anywhere = false) => ({ x: rand(0, w), y: anywhere ? rand(0, h) : h + rand(5, 60), r: rand(0.8, 2.4), vy: rand(18, 46), phase: rand(0, 6.28), life: rand(0.5, 1), hue: rand(10, 32) });

function launch(x = rand(w * 0.15, w * 0.85), targetY = rand(h * 0.12, h * 0.42)) {
    rockets.push({ x, y: h + 10, vx: rand(-25, 25), vy: -Math.sqrt(2 * 520 * (h - targetY)), colour: FIREWORK_COLOURS[Math.floor(Math.random() * FIREWORK_COLOURS.length)] });
}

function burst(r) {
    const n = Math.round(rand(55, 85));
    const speed = rand(150, 240);
    for (let i = 0; i < n; i++) {
        const angle = (i / n) * Math.PI * 2 + rand(-0.05, 0.05);
        const s = speed * rand(0.55, 1);
        sparks.push({ x: r.x, y: r.y, vx: Math.cos(angle) * s, vy: Math.sin(angle) * s, life: 1, decay: rand(0.55, 0.9), colour: Math.random() < 0.18 ? '#ffffff' : r.colour });
    }
}

function step(dt, t) {
    wind += (windTarget - wind) * Math.min(1, dt * 1.5);

    if (props.mode === 'fireworks') {
        // Trails: fade what was drawn instead of clearing, keeping the overlay transparent.
        ctx.globalCompositeOperation = 'destination-out';
        ctx.fillStyle = `rgba(0,0,0,${Math.min(1, dt * 9)})`;
        ctx.fillRect(0, 0, w, h);
        ctx.globalCompositeOperation = 'lighter';
        if (t > nextLaunch) {
            launch();
            nextLaunch = t + rand(0.9, 2.4);
        }
        for (let i = rockets.length - 1; i >= 0; i--) {
            const r = rockets[i];
            r.vy += 520 * dt;
            r.x += r.vx * dt;
            r.y += r.vy * dt;
            ctx.fillStyle = r.colour;
            ctx.fillRect(r.x - 1, r.y - 1, 2, 4);
            if (r.vy >= 0) {
                burst(r);
                rockets.splice(i, 1);
            }
        }
        for (let i = sparks.length - 1; i >= 0; i--) {
            const s = sparks[i];
            s.vx *= 1 - dt * 1.4;
            s.vy = s.vy * (1 - dt * 1.4) + 160 * dt;
            s.x += s.vx * dt;
            s.y += s.vy * dt;
            s.life -= s.decay * dt;
            if (s.life <= 0) {
                sparks.splice(i, 1);
                continue;
            }
            ctx.globalAlpha = Math.max(0, s.life);
            ctx.fillStyle = s.colour;
            ctx.fillRect(s.x, s.y, 2, 2);
        }
        ctx.globalAlpha = 1;
        ctx.globalCompositeOperation = 'source-over';
        return;
    }

    ctx.clearRect(0, 0, w, h);

    if (props.mode === 'snow') {
        ctx.fillStyle = '#ffffff';
        for (const f of flakes) {
            f.phase += dt * 1.2;
            f.y += f.vy * dt;
            f.x += (Math.sin(f.phase) * f.sway + wind * f.r * 18) * dt;
            if (f.y > h + 8 || f.x < -20 || f.x > w + 20) Object.assign(f, newFlake(), { x: f.x < -20 ? w + 10 : f.x > w + 20 ? -10 : rand(0, w) });
            ctx.globalAlpha = f.a;
            ctx.beginPath();
            ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    if (props.mode === 'embers') {
        for (const e of embers) {
            e.phase += dt * 2;
            e.y -= e.vy * dt;
            e.x += (Math.sin(e.phase) * 14 + wind * 30) * dt;
            if (e.y < -10) Object.assign(e, newEmber());
            const flicker = 0.55 + Math.sin(t * 9 + e.phase * 3) * 0.25;
            const fade = Math.min(1, e.y / (h * 0.5));
            ctx.globalAlpha = Math.max(0, flicker * fade * e.life);
            ctx.fillStyle = `hsl(${e.hue}, 100%, 60%)`;
            ctx.shadowColor = `hsl(${e.hue}, 100%, 55%)`;
            ctx.shadowBlur = 8;
            ctx.beginPath();
            ctx.arc(e.x, e.y, e.r, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.shadowBlur = 0;
    }
    ctx.globalAlpha = 1;
}

function loop(now) {
    if (!running) return;
    const dt = Math.min(0.05, (now - (last || now)) / 1000);
    last = now;
    step(dt, now / 1000);
    raf = requestAnimationFrame(loop);
}

const start = () => {
    if (running || document.hidden) return;
    running = true;
    last = 0;
    raf = requestAnimationFrame(loop);
};
const stop = () => {
    running = false;
    cancelAnimationFrame(raf);
};
const on = (target, ev, fn, opts) => {
    target.addEventListener(ev, fn, opts);
    cleanups.push(() => target.removeEventListener(ev, fn, opts));
};

watch(() => props.mode, () => {
    ctx.clearRect(0, 0, w, h);
    seed();
});

onMounted(() => {
    ctx = canvas.value.getContext('2d');
    resize();
    seed();
    on(window, 'resize', () => { resize(); seed(); });
    on(document, 'visibilitychange', () => (document.hidden ? stop() : start()));
    // Snow and embers lean with the mouse; fireworks launch where you click.
    on(window, 'pointermove', (e) => (windTarget = (e.clientX / w - 0.5) * 1.2), { passive: true });
    on(window, 'pointerdown', (e) => props.mode === 'fireworks' && launch(e.clientX, e.clientY), { passive: true });
    start();
});

onBeforeUnmount(() => {
    stop();
    cleanups.forEach((fn) => fn());
});
</script>

<template>
    <canvas ref="canvas" class="pointer-events-none fixed inset-0 z-[45] h-full w-full" aria-hidden="true"></canvas>
</template>
