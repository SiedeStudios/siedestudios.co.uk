<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import LogoMark from './LogoMark.vue';
import { gsap, ScrollTrigger, scrollToTop, prefersReducedMotion } from '@/lib/motion';

/*
 * A fluid curtain: a panel with a curved leading edge sweeps up over the
 * page, the next page swaps in underneath, then the curtain continues up
 * and out, its curve flattening as it leaves.
 */
const curtain = ref(null);
const path = ref(null);
const mark = ref(null);
let covering = null;
let pending = false;
const offs = [];

const flat = 'M0 100 L0 100 Q50 100 100 100 L100 100 Z';
const curved = 'M0 100 L0 40 Q50 0 100 40 L100 100 Z';
const full = 'M0 100 L0 0 Q50 0 100 0 L100 100 Z';
const outCurve = 'M0 0 L0 0 Q50 60 100 0 L100 0 Z';
const outFlat = 'M0 0 L0 0 Q50 0 100 0 L100 0 Z';

const isPageVisit = (visit) =>
    visit.method === 'get' && !visit.only?.length && !visit.except?.length && !visit.prefetch && !visit.preserveState;

onMounted(() => {
    offs.push(router.on('start', (event) => {
        const visit = event.detail.visit;
        if (prefersReducedMotion() || !isPageVisit(visit)) return;
        if (new URL(visit.url).pathname === window.location.pathname) return;

        pending = true;
        covering = gsap.timeline()
            .set(curtain.value, { autoAlpha: 1 })
            .set(path.value, { attr: { d: flat } })
            .to(path.value, { attr: { d: curved }, duration: 0.35, ease: 'power2.in' })
            .to(path.value, { attr: { d: full }, duration: 0.3, ease: 'power2.out' })
            .fromTo(mark.value, { opacity: 0, scale: 0.85, filter: 'blur(6px)' }, { opacity: 1, scale: 1, filter: 'blur(0px)', duration: 0.4 }, 0.4);
    }));

    offs.push(router.on('navigate', () => {
        if (!pending) return;
        pending = false;
        const reveal = () => {
            scrollToTop(true);
            requestAnimationFrame(() => ScrollTrigger.refresh());
            gsap.timeline()
                .to(mark.value, { opacity: 0, y: -20, filter: 'blur(6px)', duration: 0.3 })
                .set(path.value, { attr: { d: 'M0 100 L0 0 Q50 0 100 0 L100 100 Z' } }, 0)
                .to(path.value, { attr: { d: 'M0 60 L0 0 Q50 0 100 0 L100 60 Q50 100 0 60 Z' }, duration: 0.3, ease: 'power2.in' }, 0.05)
                .to(path.value, { attr: { d: 'M0 0 L0 0 Q50 0 100 0 L100 0 Q50 0 0 0 Z' }, duration: 0.35, ease: 'power2.out' })
                .set(curtain.value, { autoAlpha: 0 })
                .set(mark.value, { y: 0 });
        };
        covering?.isActive() ? covering.eventCallback('onComplete', reveal) : reveal();
    }));

    offs.push(router.on('finish', (event) => {
        if (pending && (event.detail.visit.cancelled || event.detail.visit.interrupted)) {
            pending = false;
            gsap.to(curtain.value, { autoAlpha: 0, duration: 0.3 });
        }
    }));
});

onBeforeUnmount(() => offs.forEach((off) => off()));
</script>

<template>
    <div ref="curtain" class="pointer-events-none invisible fixed inset-0 z-[80] opacity-0" aria-hidden="true">
        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <linearGradient id="curtain-grad" x1="0" y1="1" x2="0" y2="0">
                    <stop offset="0" stop-color="var(--surface)" />
                    <stop offset="1" stop-color="var(--bg)" />
                </linearGradient>
            </defs>
            <path ref="path" :d="flat" fill="url(#curtain-grad)" />
        </svg>
        <div class="absolute inset-0 grid place-items-center">
            <div ref="mark" class="opacity-0"><LogoMark :size="64" /></div>
        </div>
    </div>
</template>
