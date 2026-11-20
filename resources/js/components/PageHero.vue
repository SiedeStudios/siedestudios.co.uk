<script setup>
import { onMounted, ref } from 'vue';
import Aurora from './Aurora.vue';
import { blurWords, gsap, prefersReducedMotion } from '@/lib/motion';

defineProps({
    eyebrow: String,
    lede: String,
    aurora: { type: Number, default: 0.55 },
});

const title = ref(null);
const root = ref(null);

onMounted(() => {
    blurWords(title.value, { delay: 0.15 });
    if (!prefersReducedMotion()) {
        gsap.from(root.value.querySelectorAll('[data-hero-fade]'), { opacity: 0, y: 16, filter: 'blur(8px)', duration: 1.1, stagger: 0.08, delay: 0.45, clearProps: 'filter' });
    }
});
</script>

<template>
    <section ref="root" class="relative overflow-hidden pb-16 pt-36 lg:pb-20 lg:pt-48">
        <Aurora :intensity="aurora" />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-bg to-transparent" aria-hidden="true"></div>
        <div class="wrap relative">
            <span v-if="eyebrow" data-hero-fade class="eyebrow">{{ eyebrow }}</span>
            <h1 ref="title" class="display mt-7 max-w-5xl text-[clamp(2.7rem,6.4vw,6rem)] opacity-0"><slot /></h1>
            <p v-if="lede" data-hero-fade class="lede mt-7 max-w-2xl">{{ lede }}</p>
            <div v-if="$slots.actions" data-hero-fade class="mt-9 flex flex-wrap items-center gap-3"><slot name="actions" /></div>
        </div>
    </section>
</template>
