<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import LogoMark from './LogoMark.vue';

const props = defineProps({
    size: { type: Number, default: 32 },
    showText: { type: Boolean, default: true },
    forceBuiltIn: { type: Boolean, default: false },
});

const site = computed(() => usePage().props.site ?? {});
const custom = computed(() => !props.forceBuiltIn && site.value.logo);
</script>

<template>
    <span class="inline-flex select-none items-center" :aria-label="site.site_name || 'SiedeStudios'">
        <img v-if="custom" :src="site.logo" :alt="site.site_name" class="block w-auto" :style="{ height: `${size}px` }" />
        <template v-else>
            <LogoMark :size="size" class="shrink-0" />
            <span v-if="showText" class="ml-2.5 font-display text-[1.25em] tracking-[-0.04em]">
                <span class="font-[640] text-text">Siede</span><span class="ml-[0.18em] font-[440] text-muted">Studios</span>
            </span>
        </template>
    </span>
</template>
