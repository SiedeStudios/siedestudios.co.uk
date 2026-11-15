<script setup>
import { computed } from 'vue';

const props = defineProps({ status: String });
const map = {
    published: ['Published', 'bg-emerald-400/12 text-emerald-300'],
    draft: ['Draft', 'bg-white/[0.07] text-text-2'],
    open: ['Unpaid', 'bg-amber-400/12 text-amber-300'],
    paid: ['Paid', 'bg-emerald-400/12 text-emerald-300'],
    refunded: ['Refunded', 'bg-sky-400/12 text-sky-300'],
    void: ['Void', 'bg-white/[0.07] text-muted'],
    invited: ['Invited', 'bg-sky-400/12 text-sky-300'],
    pending: ['Needs review', 'bg-amber-400/12 text-amber-300'],
    approved: ['Approved', 'bg-emerald-400/12 text-emerald-300'],
    rejected: ['Rejected', 'bg-accent/15 text-accent-hot'],
    VERIFIED: ['Verified', 'bg-emerald-400/12 text-emerald-300'],
    INVALID: ['Invalid', 'bg-accent/15 text-accent-hot'],
    ERROR: ['Error', 'bg-accent/15 text-accent-hot'],
};
const meta = computed(() => map[props.status] ?? [props.status, 'bg-white/[0.07] text-text-2']);
</script>

<template>
    <span class="inline-flex h-6 items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 text-xs font-medium" :class="meta[1]">
        <span class="size-1.5 rounded-full bg-current opacity-80"></span>{{ meta[0] }}
    </span>
</template>
