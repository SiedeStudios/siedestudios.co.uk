<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { prefersReducedMotion } from '@/lib/motion';

/** A soft "View" cursor over anything marked data-cursor="…". Mouse users only. */
const el = ref(null);
const label = ref('');
const visible = ref(false);
let x = 0, y = 0, tx = 0, ty = 0, raf = 0, enabled = false;

const loop = () => {
    x += (tx - x) * 0.2;
    y += (ty - y) * 0.2;
    if (el.value) el.value.style.transform = `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`;
    raf = requestAnimationFrame(loop);
};

const move = (e) => {
    tx = e.clientX;
    ty = e.clientY;
    const target = e.target instanceof Element ? e.target.closest('[data-cursor]') : null;
    visible.value = !!target;
    if (target) label.value = target.dataset.cursor;
};
const hide = () => (visible.value = false);

onMounted(() => {
    enabled = window.matchMedia('(hover: hover) and (pointer: fine)').matches && !prefersReducedMotion();
    if (!enabled) return;
    document.documentElement.classList.add('has-custom-cursor');
    window.addEventListener('pointermove', move, { passive: true });
    document.addEventListener('pointerleave', hide);
    router.on('navigate', hide);
    raf = requestAnimationFrame(loop);
});
onBeforeUnmount(() => {
    if (!enabled) return;
    cancelAnimationFrame(raf);
    document.documentElement.classList.remove('has-custom-cursor');
    window.removeEventListener('pointermove', move);
    document.removeEventListener('pointerleave', hide);
});
</script>

<template>
    <div ref="el" class="pointer-events-none fixed left-0 top-0 z-[80] hidden md:block" aria-hidden="true">
        <span
            class="grid size-[5.5rem] place-items-center rounded-full text-sm font-medium text-white shadow-[0_20px_50px_-15px_rgb(220_20_60/.7)] backdrop-blur-sm transition-[transform,opacity] duration-500 ease-out-expo"
            :class="visible ? 'scale-100 opacity-100' : 'scale-0 opacity-0'"
            style="background: linear-gradient(135deg, rgb(255 77 109 / .92), rgb(220 20 60 / .92))"
        >{{ label }}</span>
    </div>
</template>

<style>
html.has-custom-cursor [data-cursor],
html.has-custom-cursor [data-cursor] * {
    cursor: none;
}
</style>
