<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Receipt, Inbox, MessageSquareQuote, Wallet, Plus, FolderPlus, FileCode2, UserPlus, ArrowRight, Check, Circle } from 'lucide-vue-next';
import RevenueChart from '@/components/admin/RevenueChart.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import StarRating from '@/components/StarRating.vue';
import { money, ago } from '@/lib/format';

const props = defineProps({
    currency: String, stats: Object, revenue: Array, messages: Array, pending: Array,
    invoices: Array, payments: Array, checklist: Object,
});

const user = computed(() => usePage().props.auth.user);
const greeting = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
});
const yearTotal = computed(() => props.revenue.reduce((a, b) => a + b.total, 0));
const setup = computed(() => [
    { key: 'paypal', label: 'Add your PayPal business email', href: '/admin/settings/payments' },
    { key: 'webhook', label: 'Connect a Discord webhook for alerts', href: '/admin/settings/notifications' },
    { key: 'logo', label: 'Upload a custom logo (optional)', href: '/admin/settings/branding' },
    { key: 'favicon', label: 'Upload a favicon (optional)', href: '/admin/settings/branding' },
]);
const setupDone = computed(() => setup.value.filter((s) => props.checklist[s.key]).length);

const cards = computed(() => [
    { label: 'Revenue this year', value: money(props.stats.revenue_year, props.currency), icon: Wallet, href: '/admin/invoices?status=paid' },
    { label: 'Outstanding', value: money(props.stats.outstanding, props.currency), sub: `${props.stats.outstanding_count} unpaid invoice${props.stats.outstanding_count === 1 ? '' : 's'}`, icon: Receipt, href: '/admin/invoices?status=open' },
    { label: 'Unread messages', value: props.stats.unread, icon: Inbox, href: '/admin/messages?filter=unread' },
    { label: 'Reviews to approve', value: props.stats.pending_testimonials, icon: MessageSquareQuote, href: '/admin/testimonials?status=pending' },
]);

const moderate = (id, status) => router.patch(`/admin/testimonials/${id}/status`, { status }, { preserveScroll: true });
</script>

<template>
    <Head title="Dashboard" />

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-muted">{{ new Intl.DateTimeFormat('en-GB', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date()) }}</p>
            <h1 class="heading mt-1 text-[2.25rem]">{{ greeting }}, {{ user.name.split(' ')[0] }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <Link href="/admin/invoices/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New invoice</Link>
            <Link href="/admin/projects/create" class="btn btn-glass btn-sm"><FolderPlus :size="15" /> Project</Link>
            <Link href="/admin/testimonials/create" class="btn btn-glass btn-sm"><UserPlus :size="15" /> Invite review</Link>
            <Link href="/admin/shares/create" class="btn btn-glass btn-sm"><FileCode2 :size="15" /> Share</Link>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Link v-for="c in cards" :key="c.label" :href="c.href" class="card group p-5 transition-colors hover:bg-white/[0.05]">
            <div class="flex items-center justify-between">
                <span class="text-sm text-text-2">{{ c.label }}</span>
                <span class="grid size-8 place-items-center rounded-lg bg-white/5 text-muted transition-colors group-hover:text-accent-hot"><component :is="c.icon" :size="16" /></span>
            </div>
            <div class="display mt-4 text-3xl tabular-nums">{{ c.value }}</div>
            <div v-if="c.sub" class="mt-1 text-xs text-muted">{{ c.sub }}</div>
        </Link>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <section class="card-solid p-6 xl:col-span-2">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="title text-lg">Revenue</h2>
                    <p class="text-sm text-muted">Paid invoices, last 12 months · {{ currency }}</p>
                </div>
                <div class="display text-2xl tabular-nums">{{ money(yearTotal, currency) }}</div>
            </div>
            <div class="mt-6"><RevenueChart :data="revenue" :currency="currency" /></div>
        </section>

        <section class="card-solid p-6">
            <div class="flex items-center justify-between">
                <h2 class="title text-lg">Setup</h2>
                <span class="text-sm text-muted">{{ setupDone }}/{{ setup.length }}</span>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-surface-3"><div class="h-full rounded-full" :style="{ width: `${(setupDone / setup.length) * 100}%`, background: 'var(--gradient)' }"></div></div>
            <ul class="mt-5 space-y-1">
                <li v-for="s in setup" :key="s.key">
                    <Link :href="s.href" class="flex items-center gap-3 rounded-xl p-2.5 text-sm transition-colors hover:bg-white/[0.04]" :class="checklist[s.key] ? 'text-muted line-through' : ''">
                        <span class="grid size-5 shrink-0 place-items-center rounded-full" :class="checklist[s.key] ? 'bg-emerald-400/20 text-emerald-300' : 'text-faint'">
                            <Check v-if="checklist[s.key]" :size="12" :stroke-width="3" /><Circle v-else :size="16" />
                        </span>
                        {{ s.label }}
                    </Link>
                </li>
            </ul>
            <div class="mt-5 grid grid-cols-2 gap-3 border-t border-line pt-5 text-sm">
                <div><div class="display text-2xl">{{ stats.projects }}</div><div class="text-muted">Live projects</div></div>
                <div><div class="display text-2xl">{{ stats.shares }}</div><div class="text-muted">Shares</div></div>
            </div>
        </section>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <section class="card-solid p-6">
            <div class="flex items-center justify-between">
                <h2 class="title text-lg">Latest messages</h2>
                <Link href="/admin/messages" class="inline-flex items-center gap-1 text-sm text-muted hover:text-text">All <ArrowRight :size="14" /></Link>
            </div>
            <ul class="mt-4 divide-y divide-line">
                <li v-for="m in messages" :key="m.id">
                    <Link :href="`/admin/messages/${m.id}`" class="flex gap-3 py-3.5">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full" :class="m.read_at ? 'bg-transparent' : 'bg-accent-hot'"></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-3"><span class="truncate font-medium">{{ m.name }}</span><span class="shrink-0 text-xs text-muted">{{ ago(m.created_at) }}</span></span>
                            <span class="block text-xs text-accent-hot/80">{{ m.service_name }}</span>
                            <span class="mt-1 line-clamp-1 block text-sm text-muted">{{ m.body }}</span>
                        </span>
                    </Link>
                </li>
                <li v-if="!messages.length" class="py-8 text-center text-sm text-muted">No messages yet.</li>
            </ul>
        </section>

        <section class="card-solid p-6">
            <div class="flex items-center justify-between">
                <h2 class="title text-lg">Reviews awaiting approval</h2>
                <Link href="/admin/testimonials" class="inline-flex items-center gap-1 text-sm text-muted hover:text-text">All <ArrowRight :size="14" /></Link>
            </div>
            <ul class="mt-4 space-y-3">
                <li v-for="t in pending" :key="t.id" class="rounded-2xl bg-white/[0.03] p-4 shadow-[inset_0_0_0_1px_var(--line)]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium">{{ t.name }} <span class="font-normal text-muted">· {{ t.company }}</span></span>
                        <StarRating :model-value="t.rating" :size="12" />
                    </div>
                    <p class="mt-2 line-clamp-2 text-sm text-text-2">{{ t.body }}</p>
                    <div class="mt-3 flex gap-2">
                        <button type="button" class="btn btn-light btn-sm h-8" @click="moderate(t.id, 'approved')">Approve</button>
                        <button type="button" class="btn btn-glass btn-sm h-8" @click="moderate(t.id, 'rejected')">Reject</button>
                    </div>
                </li>
                <li v-if="!pending.length" class="py-8 text-center text-sm text-muted">You're all caught up.</li>
            </ul>
        </section>

        <section class="card-solid p-6">
            <div class="flex items-center justify-between">
                <h2 class="title text-lg">Recent invoices</h2>
                <Link href="/admin/invoices" class="inline-flex items-center gap-1 text-sm text-muted hover:text-text">All <ArrowRight :size="14" /></Link>
            </div>
            <ul class="mt-4 divide-y divide-line">
                <li v-for="inv in invoices" :key="inv.id">
                    <Link :href="`/admin/invoices/${inv.reference}`" class="flex items-center gap-4 py-3">
                        <span class="min-w-0 flex-1"><span class="block font-mono text-sm">{{ inv.reference }}</span><span class="block truncate text-sm text-muted">{{ inv.client_name }}</span></span>
                        <span class="tabular-nums">{{ money(inv.total, inv.currency) }}</span>
                        <StatusBadge :status="inv.status" />
                    </Link>
                </li>
                <li v-if="!invoices.length" class="py-8 text-center text-sm text-muted">No invoices yet.</li>
            </ul>
        </section>

        <section class="card-solid p-6">
            <div class="flex items-center justify-between">
                <h2 class="title text-lg">PayPal activity</h2>
                <Link href="/admin/payments" class="inline-flex items-center gap-1 text-sm text-muted hover:text-text">Log <ArrowRight :size="14" /></Link>
            </div>
            <ul class="mt-4 divide-y divide-line">
                <li v-for="p in payments" :key="p.id" class="flex items-center gap-4 py-3 text-sm">
                    <span class="min-w-0 flex-1">
                        <span class="block">{{ p.payment_status || 'IPN' }} <span class="text-muted">· {{ p.invoice?.reference || 'no invoice' }}</span></span>
                        <span class="block text-xs text-muted">{{ p.outcome?.replaceAll('_', ' ') }} · {{ ago(p.created_at) }}</span>
                    </span>
                    <span v-if="p.mc_gross" class="tabular-nums">{{ money(p.mc_gross, p.mc_currency || currency) }}</span>
                    <StatusBadge :status="p.verification" />
                </li>
                <li v-if="!payments.length" class="py-8 text-center text-sm text-muted">No IPN messages received yet.</li>
            </ul>
        </section>
    </div>
</template>
