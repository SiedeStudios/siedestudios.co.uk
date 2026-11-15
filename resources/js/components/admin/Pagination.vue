<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ paginator: Object });
</script>

<template>
    <nav v-if="paginator && paginator.last_page > 1" class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm" aria-label="Pagination">
        <span class="text-muted">Showing {{ paginator.from }}–{{ paginator.to }} of {{ paginator.total }}</span>
        <div class="flex gap-1">
            <template v-for="(link, i) in paginator.links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="grid h-9 min-w-9 place-items-center rounded-lg px-3 transition-colors"
                    :class="link.active ? 'bg-text text-bg' : 'text-text-2 hover:bg-white/5'"
                    v-html="link.label"
                />
                <span v-else class="grid h-9 min-w-9 place-items-center rounded-lg px-3 text-faint" v-html="link.label"></span>
            </template>
        </div>
    </nav>
</template>
