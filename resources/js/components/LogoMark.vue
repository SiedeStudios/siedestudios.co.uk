<script setup>
import { useId } from 'vue';

defineProps({
    size: { type: Number, default: 40 },
    color: { type: String, default: 'var(--text)' },
    tile: { type: Boolean, default: false },
});

const id = `ss-grad-${useId()}`;
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" aria-hidden="true">
        <defs>
            <linearGradient :id="id" x1="18" y1="27" x2="46" y2="37" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="var(--accent-hot)" />
                <stop offset="1" stop-color="var(--accent)" />
            </linearGradient>
        </defs>
        <rect v-if="tile" width="64" height="64" rx="15" fill="var(--surface-2)" />
        <g :transform="tile ? 'translate(7 7) scale(.78)' : undefined" stroke-linecap="round" stroke-width="8.5">
            <path data-mark="top" d="M48 12H26A10 10 0 0 0 16 22" :stroke="color" />
            <path data-mark="spine" d="M19.5 28.5 44.5 35.5" :stroke="`url(#${id})`" stroke-width="9.5" />
            <path data-mark="bottom" d="M48 42A10 10 0 0 1 38 52H16" :stroke="color" />
        </g>
    </svg>
</template>
