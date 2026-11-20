<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import Aurora from '@/components/Aurora.vue';

const props = defineProps({ status: Number });
// For the 404, a Lua stack trace for the page that isn't there.
const missing = computed(() => (usePage().url.split('?')[0].replace(/^\/+|\/+$/g, '').replace(/[^a-z0-9/_-]/gi, '') || 'index').slice(0, 60));
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
            <pre v-if="status === 404" class="mt-7 max-w-xl overflow-x-auto rounded-2xl bg-black/40 p-4 font-mono text-[12.5px] leading-relaxed text-[#9ccbff] shadow-[inset_0_0_0_1px_var(--line)]" aria-hidden="true"><span class="text-[#ff6b81]">[ERROR]</span> lua/pages/{{ missing }}.lua:1: attempt to index a nil value (field 'page')
  1. render - lua/pages/router.lua:404
   2. unknown - lua/includes/init.lua:1</pre>
            <Link href="/" class="btn btn-light mt-8"><ArrowLeft :size="16" /> Back home</Link>
        </div>
    </section>
</template>
