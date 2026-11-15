<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Check, Clock, AlertTriangle, Lock, Printer, ArrowLeft, Loader2 } from 'lucide-vue-next';
import Logo from '@/components/Logo.vue';
import BrandIcon from '@/components/BrandIcon.vue';
import Aurora from '@/components/Aurora.vue';
import { money, date } from '@/lib/format';
import { gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({ invoice: Object, business: Object, checkout: Object, returnStatus: String });

const site = computed(() => usePage().props.site ?? {});
const paid = computed(() => props.invoice.status === 'paid');
const waiting = ref(false);
const submitting = ref(false);
const doc = ref(null);

const statusMeta = computed(() => ({
    open: { label: 'Awaiting payment', cls: 'bg-amber-400/12 text-amber-300' },
    paid: { label: 'Paid', cls: 'bg-emerald-400/12 text-emerald-300' },
    refunded: { label: 'Refunded', cls: 'bg-sky-400/12 text-sky-300' },
    void: { label: 'Cancelled', cls: 'bg-white/8 text-text-2' },
    draft: { label: 'Draft', cls: 'bg-white/8 text-text-2' },
}[props.invoice.status]));

const lineTotal = (i) => i.quantity * i.unit_price;
const print = () => window.print();
const overdue = computed(() => props.invoice.status === 'open' && props.invoice.due_at && new Date(props.invoice.due_at) < new Date(new Date().toDateString()));

/* After returning from PayPal, poll briefly while the IPN is processed. */
let timer;
let tries = 0;
onMounted(() => {
    if (!prefersReducedMotion()) {
        gsap.from(doc.value, { opacity: 0, y: 40, filter: 'blur(10px)', duration: 1.3, ease: 'expo.out', clearProps: 'filter' });
    }
    if (props.returnStatus === 'success' && !paid.value) {
        waiting.value = true;
        timer = setInterval(() => {
            tries += 1;
            router.reload({
                only: ['invoice'],
                onSuccess: () => {
                    if (props.invoice.status === 'paid' || tries >= 20) {
                        clearInterval(timer);
                        waiting.value = false;
                    }
                },
            });
        }, 3000);
    }
});
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <Head :title="`Invoice ${invoice.reference}`" />
    <section class="relative overflow-hidden pb-10 pt-32 lg:pt-40">
        <Aurora :intensity="0.4" />
        <div class="wrap relative">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex items-center justify-between print:hidden">
                    <Link href="/pay" class="inline-flex items-center gap-2 text-sm text-muted hover:text-text"><ArrowLeft :size="15" /> Find another invoice</Link>
                    <button type="button" class="btn btn-glass btn-sm" @click="print"><Printer :size="15" /> Print</button>
                </div>

                <!-- Return banners -->
                <div v-if="waiting" class="mb-6 flex items-center gap-3 rounded-2xl bg-white/[0.05] p-4 text-sm shadow-[inset_0_0_0_1px_var(--line-2)]">
                    <Loader2 :size="16" class="animate-spin text-accent-hot" /> Thanks! Confirming your payment with PayPal — this usually takes a few seconds.
                </div>
                <div v-else-if="returnStatus === 'success' && !paid" class="mb-6 flex items-center gap-3 rounded-2xl bg-amber-400/10 p-4 text-sm text-amber-200">
                    <Clock :size="16" /> PayPal is still processing your payment. You'll receive a receipt from PayPal, and this invoice will update automatically.
                </div>
                <div v-else-if="returnStatus === 'cancelled' && !paid" class="mb-6 flex items-center gap-3 rounded-2xl bg-white/[0.05] p-4 text-sm text-text-2">
                    <AlertTriangle :size="16" /> Payment was cancelled — no money was taken. You can try again whenever you're ready.
                </div>

                <div class="grid gap-6 lg:grid-cols-12">
                    <!-- Invoice document -->
                    <article ref="doc" class="relative overflow-hidden rounded-[2rem] bg-surface p-7 shadow-[inset_0_0_0_1px_var(--line-2)] sm:p-10 lg:col-span-8">
                        <div v-if="paid" class="pointer-events-none absolute -right-10 top-10 rotate-12 rounded-2xl border-2 border-emerald-400/40 px-6 py-2 font-display text-3xl font-semibold tracking-tight text-emerald-300/50" aria-hidden="true">PAID</div>
                        <header class="flex flex-wrap items-start justify-between gap-6">
                            <div>
                                <Logo :size="28" />
                                <p v-if="business.address" class="mt-4 whitespace-pre-line text-sm text-muted">{{ business.address }}</p>
                                <p v-if="business.email" class="mt-1 text-sm text-muted">{{ business.email }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-muted">Invoice</div>
                                <div class="mt-1 font-mono text-lg">{{ invoice.reference }}</div>
                                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="statusMeta.cls">{{ overdue ? 'Overdue' : statusMeta.label }}</span>
                            </div>
                        </header>

                        <div class="mt-10 grid gap-6 border-y border-line py-6 sm:grid-cols-3">
                            <div>
                                <div class="text-sm text-muted">Billed to</div>
                                <div class="mt-1 font-medium">{{ invoice.client_name }}</div>
                                <div v-if="invoice.client_company" class="text-sm text-text-2">{{ invoice.client_company }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-muted">Issued</div>
                                <div class="mt-1">{{ date(invoice.issued_at) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-muted">{{ paid ? 'Paid' : 'Due' }}</div>
                                <div class="mt-1" :class="overdue ? 'text-accent-hot' : ''">{{ paid ? date(invoice.paid_at) : date(invoice.due_at) }}</div>
                            </div>
                        </div>

                        <table class="mt-6 w-full text-left text-[0.9375rem]">
                            <thead>
                                <tr class="text-sm text-muted">
                                    <th class="pb-3 font-normal">Description</th>
                                    <th class="hidden pb-3 text-right font-normal sm:table-cell">Qty</th>
                                    <th class="hidden pb-3 text-right font-normal sm:table-cell">Price</th>
                                    <th class="pb-3 text-right font-normal">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                <tr v-for="(item, i) in invoice.items" :key="i">
                                    <td class="py-4 pr-4">{{ item.description }}<span class="block text-sm text-muted sm:hidden">{{ item.quantity }} × {{ money(item.unit_price, invoice.currency) }}</span></td>
                                    <td class="hidden py-4 text-right tabular-nums text-text-2 sm:table-cell">{{ item.quantity }}</td>
                                    <td class="hidden py-4 text-right tabular-nums text-text-2 sm:table-cell">{{ money(item.unit_price, invoice.currency) }}</td>
                                    <td class="py-4 text-right tabular-nums">{{ money(lineTotal(item), invoice.currency) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4 flex justify-end border-t border-line pt-6">
                            <div class="w-full max-w-xs space-y-2">
                                <div class="flex justify-between text-sm text-text-2"><span>Subtotal</span><span class="tabular-nums">{{ money(invoice.total, invoice.currency) }}</span></div>
                                <div class="flex items-baseline justify-between"><span class="font-medium">Total</span><span class="display text-3xl tabular-nums">{{ money(invoice.total, invoice.currency) }}</span></div>
                            </div>
                        </div>

                        <div v-if="invoice.notes || business.footer" class="mt-10 space-y-3 rounded-2xl bg-white/[0.03] p-5 text-sm text-text-2">
                            <p v-if="invoice.notes" class="whitespace-pre-line">{{ invoice.notes }}</p>
                            <p v-if="business.footer" class="text-muted">{{ business.footer }}</p>
                        </div>
                    </article>

                    <!-- Payment panel -->
                    <aside class="lg:col-span-4 print:hidden">
                        <div class="card p-7 lg:sticky lg:top-28">
                            <template v-if="paid">
                                <span class="grid size-14 place-items-center rounded-full bg-emerald-400/15 text-emerald-300"><Check :size="26" :stroke-width="2.5" /></span>
                                <h2 class="heading mt-6 text-3xl">Paid in full</h2>
                                <p class="mt-2 text-text-2">Received {{ date(invoice.paid_at) }}. Thank you{{ invoice.client_name ? `, ${invoice.client_name.split(' ')[0]}` : '' }}!</p>
                            </template>
                            <template v-else-if="invoice.status === 'open'">
                                <div class="text-sm text-muted">Amount due</div>
                                <div class="display mt-2 text-5xl tabular-nums">{{ money(invoice.total, invoice.currency) }}</div>
                                <p v-if="invoice.due_at" class="mt-2 text-sm" :class="overdue ? 'text-accent-hot' : 'text-text-2'">{{ overdue ? 'Overdue since' : 'Due by' }} {{ date(invoice.due_at) }}</p>

                                <form v-if="checkout" :action="checkout.action" method="post" class="mt-8" @submit="submitting = true">
                                    <input v-for="(value, name) in checkout.fields" :key="name" type="hidden" :name="name" :value="value" />
                                    <button type="submit" class="btn btn-lg w-full bg-[#ffc439] text-[#111] hover:bg-[#f2b72b]" :disabled="submitting || waiting">
                                        <Loader2 v-if="submitting" :size="18" class="animate-spin" />
                                        <BrandIcon v-else name="paypal" :size="18" />
                                        Pay with PayPal
                                    </button>
                                    <p class="mt-3 text-center text-xs text-muted">Card payments also accepted via PayPal guest checkout.</p>
                                </form>
                                <p v-else class="mt-8 rounded-2xl bg-white/[0.04] p-4 text-sm text-text-2">Online payment isn't available for this invoice yet. Please <Link href="/contact" class="link">get in touch</Link>.</p>

                                <div v-if="checkout?.sandbox" class="mt-4 rounded-xl bg-amber-400/10 px-3 py-2 text-center text-xs text-amber-300">Sandbox mode — test payments only</div>
                                <div class="mt-6 flex items-center gap-2 border-t border-line pt-5 text-xs text-muted"><Lock :size="13" /> You'll be redirected to PayPal to complete payment securely.</div>
                            </template>
                            <template v-else>
                                <h2 class="heading text-2xl">{{ statusMeta.label }}</h2>
                                <p class="mt-2 text-text-2">This invoice can't be paid online. Questions? <Link href="/contact" class="link">Get in touch</Link>.</p>
                            </template>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</template>
