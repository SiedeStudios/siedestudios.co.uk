<script setup>
import { Link } from '@inertiajs/vue3';
import Logo from '@/components/Logo.vue';
import FlashToast from '@/components/FlashToast.vue';
</script>

<template>
    <div class="relative flex min-h-svh flex-col">
        <header class="relative z-10">
            <div class="wrap flex h-20 items-center justify-between">
                <Link href="/" aria-label="Home" class="text-[15px]"><Logo :size="28" /></Link>
                <slot name="header-right" />
            </div>
        </header>
        <main class="relative z-10 flex flex-1 flex-col"><slot /></main>
        <div class="grain" aria-hidden="true"></div>
        <FlashToast />
    </div>
</template>
