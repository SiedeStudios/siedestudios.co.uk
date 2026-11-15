<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import Aurora from '@/components/Aurora.vue';

const props = defineProps({ status: Number });
const copy = computed(() => ({
    403: ['Access denied', 'You don\'t have permission to view this page.'],
    404: ['Page not found', 'This page doesn\'t exist — or it moved somewhere better.'],
    410: ['Link expired', 'This link has expired or was removed by its owner.'],
    429: ['Slow down a little', 'Too many requests. Take a breath and try again in a minute.'],
    500: ['Something went wrong', 'An error on my side. It\'s been logged — please try again shortly.'],
    503: ['Back soon', 'Scheduled maintenance. The site will be back shortly.'],
}[props.status] ?? ['Error', 'Something went wrong.']));
</script>

<template>
    <Head :title="copy[0]" />
    <section class="relative flex min-h-[86svh] items-center overflow-hidden pt-28">
        <Aurora :intensity="0.6" />
        <div class="wrap relative">
            <span class="eyebrow">Error {{ status }}</span>
            <div class="display mt-6 text-[clamp(7rem,24vw,20rem)] leading-[0.85]"><span class="text-gradient">{{ status }}</span></div>
            <h1 class="heading mt-6 text-4xl">{{ copy[0] }}</h1>
            <p class="mt-3 max-w-md text-text-2">{{ copy[1] }}</p>
            <Link href="/" class="btn btn-light mt-8"><ArrowLeft :size="16" /> Back home</Link>
        </div>
    </section>
</template>
