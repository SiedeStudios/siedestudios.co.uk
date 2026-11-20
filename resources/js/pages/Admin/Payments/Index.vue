<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, ChevronDown } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import CopyField from '@/components/admin/CopyField.vue';
import Pagination from '@/components/admin/Pagination.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { money, ago } from '@/lib/format';

defineProps({ logs: Object, ipn_url: String, sandbox: Boolean, paypal_email: String });
const open = ref(null);
</script>

<template>
    <Head title="Payments" />
    <PageHeader title="PayPal IPN log" description="Every Instant Payment Notification PayPal sends, its verification result and what it did." back="/admin/invoices">
        <span class="chip" :class="sandbox ? 'text-amber-300' : 'text-emerald-300'">{{ sandbox ? 'Sandbox mode' : 'Live mode' }}</span>
    </PageHeader>

    <section class="card-solid mb-4 p-6">
        <h2 class="title text-lg">IPN listener URL</h2>
        <p class="mt-1 text-sm text-muted">Sent automatically with every checkout as <code class="font-mono text-xs">notify_url</code>. You can also set it in PayPal → Account Settings → Notifications → Instant payment notifications.</p>
        <CopyField class="mt-4" :value="ipn_url" :open="false" />
        <p v-if="!paypal_email" class="mt-4 rounded-xl bg-amber-400/10 p-3 text-sm text-amber-200">No PayPal business email set — checkout is disabled. <Link href="/admin/settings/payments" class="underline">Add it in Settings</Link>.</p>
    </section>

    <div class="card-solid overflow-hidden">
        <ul v-if="logs.data.length" class="divide-y divide-line">
            <li v-for="log in logs.data" :key="log.id">
                <button type="button" class="flex w-full flex-wrap items-center gap-3 px-5 py-4 text-left text-sm transition-colors hover:bg-white/[0.03]" :aria-expanded="open === log.id" @click="open = open === log.id ? null : log.id">
                    <StatusBadge :status="log.verification" />
                    <span class="font-medium">{{ log.payment_status || log.txn_type || 'IPN' }}</span>
                    <span class="font-mono text-xs text-muted">{{ log.txn_id }}</span>
                    <span class="chip">{{ log.outcome?.replaceAll('_', ' ') }}</span>
                    <span class="flex-1"></span>
                    <Link v-if="log.invoice" :href="`/admin/invoices/${log.invoice}`" class="font-mono text-xs hover:text-accent-hot" @click.stop>{{ log.invoice }}</Link>
                    <span v-if="log.mc_gross" class="tabular-nums">{{ money(log.mc_gross, log.mc_currency || 'GBP') }}</span>
                    <span class="text-xs text-muted">{{ ago(log.created_at) }}</span>
                    <ChevronDown :size="16" class="text-muted transition-transform" :class="open === log.id ? 'rotate-180' : ''" />
                </button>
                <div v-if="open === log.id" class="space-y-3 bg-bg-2 px-5 py-4 text-sm">
                    <p class="text-text-2">{{ log.notes }}</p>
                    <pre class="max-h-80 overflow-auto rounded-xl bg-bg p-4 font-mono text-xs text-text-2" data-lenis-prevent>{{ JSON.stringify(log.payload, null, 2) }}</pre>
                </div>
            </li>
        </ul>
        <EmptyState v-else :icon="CreditCard" title="No notifications yet" description="When a client pays an invoice, PayPal's IPN messages will appear here." />
    </div>
    <Pagination :paginator="logs" />
</template>
