<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({
    intensity: { type: Number, default: 1 },
    follow: { type: Boolean, default: false },
});

const root = ref(null);
let ctx;
let cleanup = () => {};

// Slow-drifting blurred light sources that blend into a liquid crimson glow.
onMounted(() => {
    if (prefersReducedMotion()) return;
    ctx = gsap.context(() => {
        gsap.utils.toArray('[data-blob]').forEach((blob, i) => {
            const drift = () => gsap.to(blob, {
                xPercent: gsap.utils.random(-28, 28),
                yPercent: gsap.utils.random(-22, 22),
                scale: gsap.utils.random(0.85, 1.2),
                rotation: gsap.utils.random(-30, 30),
                duration: gsap.utils.random(7, 11),
                ease: 'sine.inOut',
                onComplete: drift,
            });
            gsap.delayedCall(i * 0.6, drift);
        });
    }, root.value);

    if (props.follow && window.matchMedia('(pointer: fine)').matches) {
        const host = root.value.parentElement;
        const x = gsap.quickTo(root.value.querySelector('[data-follow]'), 'x', { duration: 2.2, ease: 'power3' });
        const y = gsap.quickTo(root.value.querySelector('[data-follow]'), 'y', { duration: 2.2, ease: 'power3' });
        const move = (e) => {
            const r = host.getBoundingClientRect();
            x((e.clientX - r.left - r.width / 2) * 0.35);
            y((e.clientY - r.top - r.height / 2) * 0.35);
        };
        host.addEventListener('pointermove', move);
        cleanup = () => host.removeEventListener('pointermove', move);
    }
});
onBeforeUnmount(() => {
    ctx?.revert();
    cleanup();
});
</script>

<template>
    <div ref="root" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true" :style="{ opacity: intensity }">
        <div data-follow class="absolute inset-0">
            <div data-blob class="absolute right-[-8%] top-[4%] h-[62%] w-[52%] rounded-full opacity-70 blur-[90px]" style="background: radial-gradient(circle at 40% 40%, var(--accent-hot), var(--accent) 45%, transparent 70%)"></div>
            <div data-blob class="absolute right-[18%] top-[38%] h-[58%] w-[44%] rounded-full opacity-80 blur-[100px]" style="background: radial-gradient(circle, var(--accent-deep), transparent 70%)"></div>
            <div data-blob class="absolute right-[2%] top-[-10%] h-[40%] w-[34%] rounded-full opacity-30 blur-[80px]" style="background: radial-gradient(circle, #ff9fb1, transparent 70%)"></div>
            <div data-blob class="absolute left-[-10%] top-[50%] h-[50%] w-[38%] rounded-full opacity-25 blur-[110px]" style="background: radial-gradient(circle, var(--accent), transparent 70%)"></div>
        </div>
    </div>
</template>
