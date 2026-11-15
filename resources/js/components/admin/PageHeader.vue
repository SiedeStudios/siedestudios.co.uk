<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps({ title: String, description: String, back: String });
</script>

<template>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <Link v-if="back" :href="back" class="mb-3 inline-flex items-center gap-1.5 text-sm text-muted hover:text-text"><ArrowLeft :size="14" /> Back</Link>
            <h1 class="heading text-[2rem] leading-tight">{{ title }}</h1>
            <p v-if="description" class="mt-1.5 text-text-2">{{ description }}</p>
        </div>
        <div v-if="$slots.default" class="flex flex-wrap items-center gap-2"><slot /></div>
    </div>
</template>
