<script setup>
import { ref } from 'vue';
import { date } from '@/lib/format';

const props = defineProps({ bars: Array, height: { type: String, default: 'h-9' } });
const hover = ref(null);

const color = (b) => {
    if (b.uptime === null) return 'bg-white/[0.07]';
    if (b.uptime >= 99.9) return 'bg-emerald-400';
    if (b.uptime >= 98) return 'bg-amber-400';
    return 'bg-[#f43f5e]';
};
</script>

<template>
    <div class="relative" @mouseleave="hover = null">
        <div class="flex gap-[2px]" role="img" :aria-label="`Daily uptime for the last ${bars.length} days`">
            <span
                v-for="(b, i) in bars"
                :key="b.date"
                class="flex-1 rounded-[3px] transition-opacity"
                :class="[height, color(b), hover !== null && hover !== i ? 'opacity-50' : '']"
                @mouseenter="hover = i"
            ></span>
        </div>
        <div v-if="hover !== null" class="pointer-events-none absolute bottom-full z-20 mb-2 w-44 -translate-x-1/2 rounded-xl bg-surface-2 p-3 text-xs shadow-[inset_0_0_0_1px_var(--line-2),0_20px_40px_-12px_#000]" :style="{ left: `${((hover + 0.5) / bars.length) * 100}%` }">
            <p class="font-medium">{{ date(bars[hover].date, { weekday: 'short', day: 'numeric', month: 'short' }) }}</p>
            <p class="mt-1 text-text-2">{{ bars[hover].uptime === null ? 'No data' : `${bars[hover].uptime}% uptime` }}</p>
            <p v-if="bars[hover].incidents" class="text-accent-hot">{{ bars[hover].incidents }} incident{{ bars[hover].incidents > 1 ? 's' : '' }}</p>
            <p v-if="bars[hover].avg_ms" class="text-muted">{{ bars[hover].avg_ms }} ms average</p>
        </div>
    </div>
</template>
