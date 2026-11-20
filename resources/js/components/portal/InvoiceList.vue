<script setup>
import { Link } from '@inertiajs/vue3';
import { Receipt, ArrowUpRight } from 'lucide-vue-next';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { date, money } from '@/lib/format';

defineProps({ invoices: Array, admin: Boolean });
</script>

<template>
    <ul class="divide-y divide-line">
        <li v-for="i in invoices" :key="i.reference">
            <component :is="admin ? Link : 'a'" :href="admin ? `/admin/invoices/${i.reference}` : i.url" class="group -mx-2 flex items-center gap-3 rounded-xl px-2 py-3 hover:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/[0.05] text-muted"><Receipt :size="18" /></span>
                <span class="min-w-0 flex-1">
                    <span class="block font-mono text-sm">{{ i.reference }}</span>
                    <span class="block truncate text-xs text-muted">{{ i.project ? `${i.project} · ` : '' }}{{ i.status === 'paid' ? 'Paid' : i.due_at ? `Due ${date(i.due_at)}` : 'No due date' }}</span>
                </span>
                <span class="text-right">
                    <span class="block font-medium">{{ money(i.total, i.currency) }}</span>
                    <StatusBadge :status="i.overdue ? 'overdue' : i.status" />
                </span>
                <ArrowUpRight v-if="!admin && i.status === 'open'" :size="16" class="text-muted group-hover:text-text" />
            </component>
        </li>
    </ul>
</template>
