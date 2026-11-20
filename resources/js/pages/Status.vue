<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, AlertTriangle, XCircle, Clock, RefreshCw, Activity } from 'lucide-vue-next';
import Aurora from '@/components/Aurora.vue';
import UptimeBars from '@/components/UptimeBars.vue';
import { useMotion } from '@/composables/useMotion';
import { ago, date } from '@/lib/format';

const props = defineProps({ overall: String, monitors: Array, incidents: Array, checked_at: String });

const banner = computed(() => ({
    up: { icon: CheckCircle2, title: 'All systems operational', tone: 'text-emerald-300', ring: 'rgb(52 211 153 / .25)', glow: 'rgb(52 211 153 / .18)' },
    degraded: { icon: AlertTriangle, title: 'Some systems are having problems', tone: 'text-amber-300', ring: 'rgb(251 191 36 / .25)', glow: 'rgb(251 191 36 / .16)' },
    down: { icon: XCircle, title: 'Major outage', tone: 'text-accent-hot', ring: 'rgb(244 63 94 / .3)', glow: 'rgb(244 63 94 / .18)' },
    unknown: { icon: Clock, title: 'Waiting for the first checks', tone: 'text-text-2', ring: 'var(--line-2)', glow: 'transparent' },
}[props.overall]));

const label = { up: 'Operational', down: 'Down', unknown: 'Checking…' };
const tone = { up: 'text-emerald-300', down: 'text-accent-hot', unknown: 'text-muted' };

const spark = (series) => {
    const pts = series.map((s) => s.ms);
    const max = Math.max(...pts.filter((v) => v !== null), 1);
    const step = 100 / (pts.length - 1);
    let d = '';
    pts.forEach((v, i) => {
        if (v === null) return;
        d += `${d && pts[i - 1] !== null ? 'L' : 'M'}${(i * step).toFixed(2)},${(28 - (v / max) * 24).toFixed(2)} `;
    });
    return d;
};
const duration = (s) => {
    if (s === null) return 'Ongoing';
    if (s < 60) return `${s}s`;
    const m = Math.round(s / 60);
    return m < 60 ? `${m} min` : `${Math.floor(m / 60)} h ${m % 60} min`;
};

let timer;
const refreshing = ref(false);
onMounted(() => {
    timer = setInterval(() => router.reload({ only: ['overall', 'monitors', 'incidents', 'checked_at'], onStart: () => (refreshing.value = true), onFinish: () => (refreshing.value = false) }), 60_000);
});
onBeforeUnmount(() => clearInterval(timer));

const root = ref(null);
useMotion(root);
</script>

<template>
    <Head title="Status" />
    <div ref="root">
        <section class="relative overflow-hidden pb-12 pt-36 lg:pt-44">
            <Aurora :intensity="0.45" />
            <div class="wrap relative">
                <span class="eyebrow" data-reveal>System status</span>
                <div class="mt-7 flex flex-wrap items-center gap-5 rounded-[1.75rem] bg-surface/70 p-6 backdrop-blur-xl sm:p-8" :style="{ boxShadow: `inset 0 0 0 1px ${banner.ring}, 0 30px 90px -30px ${banner.glow}` }" data-reveal>
                    <span class="relative grid size-14 place-items-center">
                        <span v-if="overall !== 'unknown'" class="absolute inset-0 animate-ping rounded-full opacity-20" :class="overall === 'up' ? 'bg-emerald-400' : overall === 'degraded' ? 'bg-amber-400' : 'bg-[#f43f5e]'" style="animation-duration: 2.4s"></span>
                        <component :is="banner.icon" :size="36" :class="banner.tone" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h1 class="heading text-[clamp(1.6rem,3.4vw,2.4rem)]">{{ banner.title }}</h1>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-muted"><RefreshCw :size="13" :class="refreshing && 'animate-spin'" /> Checked every minute · last check {{ checked_at ? ago(checked_at) : 'pending' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="wrap pb-16">
            <div class="space-y-4">
                <article v-for="m in monitors" :key="m.id" class="card-solid p-6" data-reveal>
                    <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                        <div class="flex items-center gap-3">
                            <span class="size-2.5 rounded-full" :class="m.status === 'up' ? 'bg-emerald-400 shadow-[0_0_12px_rgb(52_211_153/.7)]' : m.status === 'down' ? 'bg-[#f43f5e] shadow-[0_0_12px_rgb(244_63_94/.7)]' : 'bg-white/30'"></span>
                            <h2 class="title text-lg">{{ m.name }}</h2>
                            <span class="text-sm" :class="tone[m.status]">{{ label[m.status] }}</span>
                        </div>
                        <div class="flex items-center gap-6 text-sm">
                            <div v-if="m.response.some((r) => r.ms !== null)" class="hidden items-center gap-3 sm:flex" :title="'Response time, last 24 hours'">
                                <svg viewBox="0 0 100 30" preserveAspectRatio="none" class="h-7 w-28 text-accent-hot" aria-hidden="true"><path :d="spark(m.response)" fill="none" stroke="currentColor" stroke-width="1.6" vector-effect="non-scaling-stroke" stroke-linejoin="round" /></svg>
                                <span class="w-16 text-right font-mono text-xs text-text-2">{{ m.response_ms !== null ? `${m.response_ms} ms` : '—' }}</span>
                            </div>
                            <span class="font-mono text-sm"><b class="font-medium text-text">{{ m.uptime_90 !== null ? `${m.uptime_90}%` : '—' }}</b> <span class="text-muted">uptime</span></span>
                        </div>
                    </div>
                    <UptimeBars class="mt-5" :bars="m.bars" />
                    <div class="mt-2 flex justify-between text-xs text-faint"><span>90 days ago</span><span>Today</span></div>
                </article>
            </div>
        </section>

        <section class="wrap pb-28 lg:pb-36">
            <h2 class="title flex items-center gap-2 text-xl" data-reveal><Activity :size="19" class="text-accent-hot" /> Past incidents</h2>
            <div class="card-solid mt-5 overflow-hidden" data-reveal>
                <ul v-if="incidents.length" class="divide-y divide-line">
                    <li v-for="i in incidents" :key="i.id" class="flex flex-wrap items-center gap-x-6 gap-y-1 p-5">
                        <span class="size-2 rounded-full" :class="i.resolved_at ? 'bg-emerald-400' : 'bg-[#f43f5e]'"></span>
                        <span class="min-w-0 flex-1"><span class="font-medium">{{ i.monitor }}</span> <span class="text-text-2">{{ i.resolved_at ? 'was unavailable' : 'is unavailable' }}</span><span v-if="i.reason" class="text-muted"> — {{ i.reason }}</span></span>
                        <span class="text-sm text-muted">{{ date(i.started_at, { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</span>
                        <span class="w-24 text-right font-mono text-sm" :class="i.resolved_at ? 'text-text-2' : 'text-accent-hot'">{{ duration(i.duration) }}</span>
                    </li>
                </ul>
                <p v-else class="p-6 text-text-2">No incidents in the last 30 days.</p>
            </div>
        </section>
    </div>
</template>
