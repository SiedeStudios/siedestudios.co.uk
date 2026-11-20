<script setup>
import { computed, defineAsyncComponent, onBeforeUnmount, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { prefersReducedMotion } from '@/lib/motion';
import { seasonFor } from '@/lib/seasons';

/**
 * Picks a seasonal effect from the visitor's local date. The canvas code is
 * only downloaded when a season is active (or previewed from the console).
 */
const SeasonCanvas = defineAsyncComponent(() => import('./SeasonCanvas.vue'));

const enabled = computed(() => usePage().props.site?.seasonal_effects === true);
const preview = ref(null);
const mode = computed(() => preview.value ?? (enabled.value && !prefersReducedMotion() ? seasonFor() : null));

const onPreview = (e) => (preview.value = e.detail === 'off' ? false : e.detail);
onMounted(() => window.addEventListener('season-preview', onPreview));
onBeforeUnmount(() => window.removeEventListener('season-preview', onPreview));
</script>

<template>
    <SeasonCanvas v-if="mode" :mode="mode" />
</template>
