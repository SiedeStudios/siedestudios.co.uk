<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Receipt } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Pagination from '@/components/admin/Pagination.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { money, date } from '@/lib/format';

const props = defineProps({ invoices: Object, filter: String, counts: Object });
const tabs = [
    { key: null, label: 'All' },
    { key: 'open', label: 'Unpaid' },
    { key: 'paid', label: 'Paid' },
    { key: 'draft', label: 'Drafts' },
    { key: 'void', label: 'Void' },
    { key: 'refunded', label: 'Refunded' },
];
const overdue = (i) => i.status === 'open' && i.due_at && new Date(i.due_at) < new Date(new Date().toDateString());
</script>

<template>
    <Head title="Invoices" />
    <PageHeader title="Invoices" description="Each invoice gets a unique pay link your client can settle with PayPal.">
        <Link href="/admin/payments" class="btn btn-glass btn-sm">IPN log</Link>
        <Link href="/admin/invoices/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New invoice</Link>
    </PageHeader>

    <div class="mb-5 flex flex-wrap gap-2">
        <Link v-for="t in tabs" :key="t.label" :href="t.key ? `/admin/invoices?status=${t.key}` : '/admin/invoices'" preserve-scroll
            class="inline-flex h-9 items-center gap-2 rounded-full px-4 text-sm transition-colors"
            :class="filter === t.key ? 'bg-text text-bg' : 'bg-white/[0.04] text-text-2 shadow-[inset_0_0_0_1px_var(--line)] hover:text-text'">
            {{ t.label }} <span v-if="t.key" class="text-xs opacity-60">{{ counts[t.key] ?? 0 }}</span>
        </Link>
    </div>

    <div class="card-solid overflow-x-auto">
        <table v-if="invoices.data.length" class="w-full min-w-[640px] text-left text-sm">
            <thead class="text-muted">
                <tr class="border-b border-line">
                    <th class="px-5 py-3.5 font-normal">Invoice</th>
                    <th class="px-5 py-3.5 font-normal">Client</th>
                    <th class="px-5 py-3.5 font-normal">Due</th>
                    <th class="px-5 py-3.5 text-right font-normal">Amount</th>
                    <th class="px-5 py-3.5 font-normal">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                <tr v-for="inv in invoices.data" :key="inv.id" class="cursor-pointer transition-colors hover:bg-white/[0.03]" @click="$inertia.visit(`/admin/invoices/${inv.reference}`)">
                    <td class="px-5 py-4"><Link :href="`/admin/invoices/${inv.reference}`" class="font-mono" @click.stop>{{ inv.reference }}</Link></td>
                    <td class="px-5 py-4"><div>{{ inv.client_name }}</div><div class="text-muted">{{ inv.client_company }}</div></td>
                    <td class="px-5 py-4" :class="overdue(inv) ? 'text-accent-hot' : 'text-text-2'">{{ inv.status === 'paid' ? `Paid ${date(inv.paid_at)}` : date(inv.due_at) }}</td>
                    <td class="px-5 py-4 text-right tabular-nums">{{ money(inv.total, inv.currency) }}</td>
                    <td class="px-5 py-4"><StatusBadge :status="overdue(inv) ? 'open' : inv.status" /><span v-if="overdue(inv)" class="ml-2 text-xs text-accent-hot">Overdue</span></td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else :icon="Receipt" title="No invoices" description="Create an invoice and send the pay link to your client.">
            <Link href="/admin/invoices/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New invoice</Link>
        </EmptyState>
    </div>
    <Pagination :paginator="invoices" />
</template>
