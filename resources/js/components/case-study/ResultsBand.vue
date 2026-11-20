<script setup>
import { ref } from 'vue';
import { useMotion } from '@/composables/useMotion';
import { gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({ results: Array });
const root = ref(null);

// "38", "200k+", "99.9%", "£4,500" → animate just the numeric part.
const parts = (value) => {
    const m = String(value).match(/^(\D*)([\d.,]+)(.*)$/);
    return m ? { prefix: m[1], number: m[2], suffix: m[3] } : null;
};

useMotion(root, (el) => {
    el.querySelectorAll('[data-count]').forEach((node) => {
        const raw = node.dataset.count;
        const decimals = (raw.split('.')[1] ?? '').length;
        const commas = raw.includes(',');
        const end = parseFloat(raw.replace(/,/g, ''));
        const fmt = (v) => (commas ? v.toLocaleString('en-GB', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : v.toFixed(decimals));
        if (prefersReducedMotion() || Number.isNaN(end)) return;
        const obj = { v: 0 };
        node.textContent = fmt(0);
        gsap.to(obj, { v: end, duration: 2, ease: 'expo.out', scrollTrigger: { trigger: node, start: 'top 88%', once: true }, onUpdate: () => (node.textContent = fmt(obj.v)) });
    });
});
</script>

<template>
    <div ref="root" class="grid gap-px overflow-hidden rounded-[1.75rem] bg-line shadow-[inset_0_0_0_1px_var(--line)]" :class="{ 'sm:grid-cols-2': results.length >= 2, 'lg:grid-cols-3': results.length === 3, 'lg:grid-cols-4': results.length === 4 }">
        <div v-for="(r, i) in results" :key="i" class="bg-surface p-7 lg:p-9" data-reveal>
            <div class="display text-[clamp(2.6rem,5vw,4rem)] leading-none">
                <template v-if="parts(r.value)">{{ parts(r.value).prefix }}<span data-count :data-count="parts(r.value).number">{{ parts(r.value).number }}</span><span class="text-gradient">{{ parts(r.value).suffix }}</span></template>
                <template v-else>{{ r.value }}</template>
            </div>
            <p class="mt-4 max-w-[16rem] text-text-2">{{ r.label }}</p>
        </div>
    </div>
</template>
