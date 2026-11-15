<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import SiteHeader from '@/components/SiteHeader.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import PageTransition from '@/components/PageTransition.vue';
import FlashToast from '@/components/FlashToast.vue';
import { startSmoothScroll, stopSmoothScroll } from '@/lib/motion';

onMounted(() => startSmoothScroll());
onBeforeUnmount(() => stopSmoothScroll());
</script>

<template>
    <div class="relative">
        <SiteHeader />
        <main id="main" class="relative z-10">
            <slot />
        </main>
        <SiteFooter />
        <div class="grain" aria-hidden="true"></div>
        <PageTransition />
        <FlashToast />
    </div>
</template>
