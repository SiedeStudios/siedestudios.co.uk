<script setup>
import { computed, ref } from 'vue';
import { money } from '@/lib/format';

const props = defineProps({ data: Array, currency: String });

const W = 720;
const H = 240;
const pad = { t: 16, r: 12, b: 28, l: 56 };
const hover = ref(null);
const svg = ref(null);

const max = computed(() => {
    const m = Math.max(...props.data.map((d) => d.total), 0);
    if (m === 0) return 100;
    const pow = 10 ** Math.floor(Math.log10(m));
    return Math.ceil(m / pow) * pow;
});
const x = (i) => pad.l + (i * (W - pad.l - pad.r)) / (props.data.length - 1);
const y = (v) => H - pad.b - (v / max.value) * (H - pad.t - pad.b);
const points = computed(() => props.data.map((d, i) => [x(i), y(d.total)]));

// Monotone cubic interpolation keeps the curve smooth without overshooting zero.
const line = computed(() => {
    const p = points.value;
    if (p.length < 2) return '';
    const n = p.length;
    const dx = [], dy = [], m = [], t = [];
    for (let i = 0; i < n - 1; i++) { dx[i] = p[i + 1][0] - p[i][0]; dy[i] = p[i + 1][1] - p[i][1]; m[i] = dy[i] / dx[i]; }
    t[0] = m[0]; t[n - 1] = m[n - 2];
    for (let i = 1; i < n - 1; i++) t[i] = m[i - 1] * m[i] <= 0 ? 0 : (m[i - 1] + m[i]) / 2;
    for (let i = 0; i < n - 1; i++) {
        if (m[i] === 0) { t[i] = 0; t[i + 1] = 0; continue; }
        const a = t[i] / m[i], b = t[i + 1] / m[i], s = a * a + b * b;
        if (s > 9) { const k = 3 / Math.sqrt(s); t[i] = k * a * m[i]; t[i + 1] = k * b * m[i]; }
    }
    let d = `M${p[0][0]},${p[0][1]}`;
    for (let i = 0; i < n - 1; i++) {
        const h = dx[i] / 3;
        d += ` C${p[i][0] + h},${p[i][1] + t[i] * h} ${p[i + 1][0] - h},${p[i + 1][1] - t[i + 1] * h} ${p[i + 1][0]},${p[i + 1][1]}`;
    }
    return d;
});
const area = computed(() => `${line.value} L${x(props.data.length - 1)},${H - pad.b} L${x(0)},${H - pad.b} Z`);
const ticks = computed(() => [0, 0.25, 0.5, 0.75, 1].map((f) => f * max.value));
const short = (v) => (v >= 1000 ? `${(v / 1000).toFixed(v % 1000 ? 1 : 0)}k` : `${Math.round(v)}`);

const move = (e) => {
    const r = svg.value.getBoundingClientRect();
    const px = ((e.clientX - r.left) / r.width) * W;
    let best = 0;
    points.value.forEach((p, i) => { if (Math.abs(p[0] - px) < Math.abs(points.value[best][0] - px)) best = i; });
    hover.value = best;
};
</script>

<template>
    <div class="relative">
        <svg ref="svg" :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full overflow-visible" role="img" :aria-label="`Paid revenue per month, last 12 months, in ${currency}`" @pointermove="move" @pointerleave="hover = null">
            <defs>
                <linearGradient id="rev-fill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="var(--accent)" stop-opacity="0.32" />
                    <stop offset="1" stop-color="var(--accent)" stop-opacity="0" />
                </linearGradient>
            </defs>
            <g>
                <line v-for="v in ticks" :key="v" :x1="pad.l" :x2="W - pad.r" :y1="y(v)" :y2="y(v)" stroke="var(--line)" :stroke-dasharray="v === 0 ? '' : '3 4'" />
                <text v-for="v in ticks" :key="`t${v}`" :x="pad.l - 10" :y="y(v) + 4" text-anchor="end" class="fill-[var(--muted)] font-mono text-[10px]">{{ short(v) }}</text>
                <text v-for="(d, i) in data" :key="d.month" :x="x(i)" :y="H - 8" text-anchor="middle" class="fill-[var(--muted)] text-[10.5px]">{{ d.label }}</text>
            </g>
            <path :d="area" fill="url(#rev-fill)" />
            <path :d="line" fill="none" stroke="var(--accent-hot)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
            <g v-if="hover !== null">
                <line :x1="points[hover][0]" :x2="points[hover][0]" :y1="pad.t" :y2="H - pad.b" stroke="var(--line-2)" />
                <circle :cx="points[hover][0]" :cy="points[hover][1]" r="5" fill="var(--accent-hot)" stroke="var(--surface)" stroke-width="2" />
            </g>
            <rect :x="pad.l" :y="pad.t" :width="W - pad.l - pad.r" :height="H - pad.t - pad.b" fill="transparent" />
        </svg>
        <div
            v-if="hover !== null"
            class="pointer-events-none absolute -translate-x-1/2 -translate-y-full rounded-xl bg-surface-3 px-3 py-2 text-sm shadow-xl ring-1 ring-line-2"
            :style="{ left: `${(points[hover][0] / W) * 100}%`, top: `${(points[hover][1] / H) * 100 - 4}%` }"
        >
            <div class="text-xs text-muted">{{ data[hover].label }} {{ data[hover].month.slice(0, 4) }}</div>
            <div class="font-medium tabular-nums">{{ money(data[hover].total, currency) }}</div>
        </div>
        <table class="sr-only">
            <caption>Paid revenue per month</caption>
            <tr v-for="d in data" :key="d.month"><th>{{ d.month }}</th><td>{{ money(d.total, currency) }}</td></tr>
        </table>
    </div>
</template>
