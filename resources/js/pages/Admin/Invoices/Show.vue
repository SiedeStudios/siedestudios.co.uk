<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, ExternalLink, Mail } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import CopyField from '@/components/admin/CopyField.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { money, date, ago } from '@/lib/format';
import { computed } from 'vue';

const props = defineProps({ invoice: Object, statuses: Array });
const locked = computed(() => ['paid', 'refunded'].includes(props.invoice.status));
const mailto = computed(() => {
    const subject = encodeURIComponent(`Invoice ${props.invoice.reference}`);
    const body = encodeURIComponent(`Hi ${props.invoice.client_name.split(' ')[0]},\n\nYour invoice ${props.invoice.reference} for ${money(props.invoice.total, props.invoice.currency)} is ready. You can view and pay it securely here:\n\n${props.invoice.pay_url}\n\nThanks!`);
    return `mailto:${props.invoice.client_email ?? ''}?subject=${subject}&body=${body}`;
});
const setStatus = (status) => {
    const msg = { paid: 'Mark this invoice as paid manually (e.g. bank transfer)?', void: 'Void this invoice? The pay link will stop accepting payments.', open: 'Re-open this invoice for payment?', draft: 'Move back to draft? The pay link will be hidden.', refunded: 'Mark as refunded?' }[status];
    confirmVisit(msg, `/admin/invoices/${props.invoice.reference}/status`, 'patch', { status });
};
</script>

<template>
    <Head :title="invoice.reference" />
    <PageHeader :title="invoice.reference" :description="`${invoice.client_name}${invoice.client_company ? ' · ' + invoice.client_company : ''}`" back="/admin/invoices">
        <StatusBadge :status="invoice.status" />
        <Link v-if="!locked" :href="`/admin/invoices/${invoice.reference}/edit`" class="btn btn-glass btn-sm"><Pencil :size="15" /> Edit</Link>
        <button v-if="invoice.status !== 'paid'" type="button" class="btn btn-danger btn-sm" @click="confirmVisit('Delete this invoice permanently?', `/admin/invoices/${invoice.reference}`)"><Trash2 :size="15" /></button>
    </PageHeader>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <section class="card-solid p-6">
                <h2 class="title text-lg">Pay link</h2>
                <p class="mt-1 text-sm text-muted">{{ invoice.status === 'draft' ? 'This is a draft — open it for payment before sending the link.' : 'Send this link to your client. It shows the invoice and a PayPal button.' }}</p>
                <CopyField class="mt-4" :value="invoice.pay_url" />
                <a :href="mailto" class="btn btn-glass btn-sm mt-3"><Mail :size="15" /> Email to client</a>
            </section>

            <section class="card-solid p-6">
                <h2 class="title text-lg">Items</h2>
                <table class="mt-4 w-full text-left text-sm">
                    <tbody class="divide-y divide-line">
                        <tr v-for="(item, i) in invoice.items" :key="i">
                            <td class="py-3 pr-4">{{ item.description }}</td>
                            <td class="py-3 text-right text-muted tabular-nums">{{ item.quantity }} × {{ money(item.unit_price, invoice.currency) }}</td>
                            <td class="py-3 pl-4 text-right tabular-nums">{{ money(item.quantity * item.unit_price, invoice.currency) }}</td>
                        </tr>
                    </tbody>
                    <tfoot><tr class="border-t border-line-2"><td class="pt-4 font-medium" colspan="2">Total</td><td class="display pt-4 text-right text-2xl tabular-nums">{{ money(invoice.total, invoice.currency) }}</td></tr></tfoot>
                </table>
                <p v-if="invoice.notes" class="mt-6 whitespace-pre-line rounded-2xl bg-white/[0.03] p-4 text-sm text-text-2">{{ invoice.notes }}</p>
            </section>

            <section class="card-solid p-6">
                <h2 class="title text-lg">PayPal notifications</h2>
                <p class="mt-1 text-sm text-muted">Every IPN received for this invoice and what was done with it.</p>
                <ul v-if="invoice.ipn_logs.length" class="mt-4 divide-y divide-line">
                    <li v-for="log in invoice.ipn_logs" :key="log.id" class="flex flex-wrap items-center gap-3 py-3 text-sm">
                        <StatusBadge :status="log.verification" />
                        <span class="font-medium">{{ log.payment_status || log.txn_type || 'IPN' }}</span>
                        <span class="text-muted">{{ log.notes }}</span>
                        <span class="flex-1"></span>
                        <span v-if="log.mc_gross" class="tabular-nums">{{ money(log.mc_gross, log.mc_currency || invoice.currency) }}</span>
                        <span class="text-xs text-muted">{{ ago(log.created_at) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 rounded-2xl bg-white/[0.03] p-4 text-sm text-muted">No notifications yet.</p>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="card-solid p-6">
                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-muted">Amount</dt><dd class="font-medium tabular-nums">{{ money(invoice.total, invoice.currency) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted">Issued</dt><dd>{{ date(invoice.issued_at) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted">Due</dt><dd>{{ date(invoice.due_at) }}</dd></div>
                    <div v-if="invoice.client_email" class="flex justify-between gap-4"><dt class="text-muted">Email</dt><dd class="truncate">{{ invoice.client_email }}</dd></div>
                    <template v-if="invoice.paid_at">
                        <div class="border-t border-line pt-4"></div>
                        <div class="flex justify-between gap-4"><dt class="text-muted">Paid</dt><dd>{{ date(invoice.paid_at) }}</dd></div>
                        <div v-if="invoice.paid_amount" class="flex justify-between gap-4"><dt class="text-muted">Received</dt><dd class="tabular-nums">{{ money(invoice.paid_amount, invoice.currency) }}</dd></div>
                        <div v-if="invoice.payer_email" class="flex justify-between gap-4"><dt class="text-muted">Payer</dt><dd class="truncate">{{ invoice.payer_email }}</dd></div>
                        <div v-if="invoice.paypal_txn_id" class="flex justify-between gap-4"><dt class="text-muted">Txn</dt><dd class="truncate font-mono text-xs">{{ invoice.paypal_txn_id }}</dd></div>
                    </template>
                </dl>
            </section>
            <section class="card-solid space-y-2 p-6">
                <h2 class="title mb-3 text-lg">Status</h2>
                <button v-if="invoice.status === 'draft'" type="button" class="btn btn-primary w-full" @click="setStatus('open')">Open for payment</button>
                <button v-if="invoice.status === 'open'" type="button" class="btn btn-glass w-full" @click="setStatus('paid')">Mark as paid manually</button>
                <button v-if="invoice.status === 'open'" type="button" class="btn btn-glass w-full" @click="setStatus('draft')">Move to draft</button>
                <button v-if="['open', 'draft'].includes(invoice.status)" type="button" class="btn btn-danger w-full" @click="setStatus('void')">Void invoice</button>
                <button v-if="invoice.status === 'void'" type="button" class="btn btn-glass w-full" @click="setStatus('open')">Re-open</button>
                <button v-if="invoice.status === 'paid'" type="button" class="btn btn-glass w-full" @click="setStatus('refunded')">Mark as refunded</button>
                <a :href="invoice.pay_url" target="_blank" class="btn btn-glass w-full"><ExternalLink :size="15" /> View as client</a>
            </section>
        </aside>
    </div>
</template>
