<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, Save, Send, Loader2, GripVertical } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import { money } from '@/lib/format';

const props = defineProps({ invoice: Object, currencies: Array, defaults: Object, clients: { type: Array, default: () => [] } });
const editing = computed(() => !!props.invoice);
const today = new Date().toISOString().slice(0, 10);
const inTwoWeeks = new Date(Date.now() + 14 * 864e5).toISOString().slice(0, 10);

const form = useForm({
    client_id: props.invoice?.client_id ?? props.defaults?.client_id ?? null,
    client_project_id: props.invoice?.client_project_id ?? props.defaults?.client_project_id ?? null,
    client_name: props.invoice?.client_name ?? props.defaults?.client_name ?? '',
    client_email: props.invoice?.client_email ?? props.defaults?.client_email ?? '',
    client_company: props.invoice?.client_company ?? props.defaults?.client_company ?? '',
    currency: props.invoice?.currency ?? props.defaults?.currency ?? 'GBP',
    issued_at: props.invoice?.issued_at ?? today,
    due_at: props.invoice?.due_at ?? inTwoWeeks,
    notes: props.invoice?.notes ?? '',
    items: props.invoice?.items?.length ? props.invoice.items.map((i) => ({ ...i })) : [{ description: '', quantity: 1, unit_price: '' }],
    publish: false,
});

const linkedClient = computed(() => props.clients.find((c) => c.id === form.client_id));
const pickClient = () => {
    form.client_project_id = null;
    const c = linkedClient.value;
    if (c) Object.assign(form, { client_name: c.name, client_company: c.company ?? '', client_email: c.email ?? '' });
};

const lineTotal = (i) => Math.round(Number(i.quantity || 0) * Number(i.unit_price || 0) * 100) / 100;
const total = computed(() => form.items.reduce((sum, i) => sum + lineTotal(i), 0));
const addItem = () => form.items.push({ description: '', quantity: 1, unit_price: '' });
const removeItem = (i) => form.items.length > 1 && form.items.splice(i, 1);

const submit = (publish) => {
    form.publish = publish;
    editing.value ? form.put(`/admin/invoices/${props.invoice.reference}`) : form.post('/admin/invoices');
};
const itemError = (i, key) => form.errors[`items.${i}.${key}`];
</script>

<template>
    <Head :title="editing ? `Edit ${invoice.reference}` : 'New invoice'" />
    <PageHeader :title="editing ? `Edit ${invoice.reference}` : 'New invoice'" :description="editing ? null : 'A unique reference and pay link are generated when you save.'" :back="editing ? `/admin/invoices/${invoice.reference}` : '/admin/invoices'" />

    <form class="grid gap-4 xl:grid-cols-3" @submit.prevent="submit(false)">
        <div class="space-y-4 xl:col-span-2">
            <section class="card-solid p-6">
                <h2 class="title text-lg">Client</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <Field v-if="clients.length" label="Client account" for="client_id" optional help="Shows the invoice in their client portal." :error="form.errors.client_id">
                        <select id="client_id" v-model="form.client_id" class="field" @change="pickClient"><option :value="null">Not linked</option><option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company ? `${c.company} — ${c.name}` : c.name }}</option></select>
                    </Field>
                    <Field v-if="linkedClient?.projects.length" label="Project" for="client_project_id" optional :error="form.errors.client_project_id">
                        <select id="client_project_id" v-model="form.client_project_id" class="field"><option :value="null">General</option><option v-for="p in linkedClient.projects" :key="p.id" :value="p.id">{{ p.title }}</option></select>
                    </Field>
                    <Field label="Name" for="client_name" :error="form.errors.client_name"><input id="client_name" v-model="form.client_name" class="field" required /></Field>
                    <Field label="Company" for="client_company" :error="form.errors.client_company" optional><input id="client_company" v-model="form.client_company" class="field" /></Field>
                    <Field label="Email" for="client_email" :error="form.errors.client_email" optional class="sm:col-span-2"><input id="client_email" v-model="form.client_email" type="email" class="field" /></Field>
                </div>
            </section>

            <section class="card-solid p-6">
                <div class="flex items-center justify-between">
                    <h2 class="title text-lg">Line items</h2>
                    <span class="text-sm text-muted">{{ form.items.length }} item{{ form.items.length === 1 ? '' : 's' }}</span>
                </div>
                <p v-if="form.errors.items" class="field-error">{{ form.errors.items }}</p>
                <div class="mt-5 hidden grid-cols-[1fr_6rem_8rem_7rem_2.5rem] gap-3 px-1 text-xs text-muted sm:grid">
                    <span>Description</span><span>Qty</span><span>Unit price</span><span class="text-right">Total</span><span></span>
                </div>
                <TransitionGroup tag="ul" class="mt-2 space-y-3" enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-1" leave-active-class="transition duration-200" leave-to-class="opacity-0">
                    <li v-for="(item, i) in form.items" :key="i" class="grid gap-3 rounded-2xl bg-white/[0.02] p-3 shadow-[inset_0_0_0_1px_var(--line)] sm:grid-cols-[1fr_6rem_8rem_7rem_2.5rem] sm:items-start sm:bg-transparent sm:p-0 sm:shadow-none">
                        <div>
                            <input v-model="item.description" class="field" placeholder="What was delivered" :aria-invalid="!!itemError(i, 'description')" :aria-label="`Item ${i + 1} description`" />
                            <p v-if="itemError(i, 'description')" class="field-error">{{ itemError(i, 'description') }}</p>
                        </div>
                        <input v-model="item.quantity" type="number" step="0.01" min="0.01" class="field" :aria-label="`Item ${i + 1} quantity`" :aria-invalid="!!itemError(i, 'quantity')" />
                        <input v-model="item.unit_price" type="number" step="0.01" min="0" class="field" placeholder="0.00" :aria-label="`Item ${i + 1} price`" :aria-invalid="!!itemError(i, 'unit_price')" />
                        <div class="flex h-12 items-center justify-end tabular-nums">{{ money(lineTotal(item), form.currency) }}</div>
                        <button type="button" class="grid h-12 place-items-center rounded-xl text-muted hover:bg-accent/10 hover:text-accent-hot disabled:opacity-30" :disabled="form.items.length === 1" aria-label="Remove item" @click="removeItem(i)"><Trash2 :size="16" /></button>
                    </li>
                </TransitionGroup>
                <button type="button" class="btn btn-glass btn-sm mt-4" @click="addItem"><Plus :size="15" /> Add item</button>
                <div class="mt-6 flex items-baseline justify-end gap-6 border-t border-line pt-5">
                    <span class="text-text-2">Total</span>
                    <span class="display text-3xl tabular-nums">{{ money(total, form.currency) }}</span>
                </div>
            </section>

            <section class="card-solid p-6">
                <Field label="Notes for the client" for="notes" :error="form.errors.notes" help="Shown on the invoice — payment terms, milestone details, thanks." optional>
                    <textarea id="notes" v-model="form.notes" rows="3" class="field"></textarea>
                </Field>
            </section>
        </div>

        <aside class="card-solid h-fit space-y-5 p-6 xl:sticky xl:top-6">
            <Field label="Currency" for="currency" :error="form.errors.currency">
                <select id="currency" v-model="form.currency" class="field"><option v-for="c in currencies" :key="c" :value="c">{{ c }}</option></select>
            </Field>
            <div class="grid grid-cols-2 gap-3">
                <Field label="Issued" for="issued_at" :error="form.errors.issued_at"><input id="issued_at" v-model="form.issued_at" type="date" class="field" /></Field>
                <Field label="Due" for="due_at" :error="form.errors.due_at"><input id="due_at" v-model="form.due_at" type="date" class="field" /></Field>
            </div>
            <div class="rounded-2xl bg-white/[0.03] p-4 text-sm text-text-2 shadow-[inset_0_0_0_1px_var(--line)]">
                <div class="flex justify-between"><span>Amount due</span><span class="font-medium tabular-nums text-text">{{ money(total, form.currency) }}</span></div>
            </div>
            <template v-if="!editing || invoice.status === 'draft'">
                <button type="button" class="btn btn-primary w-full" :disabled="form.processing" @click="submit(true)">
                    <Loader2 v-if="form.processing && form.publish" :size="16" class="animate-spin" /><Send v-else :size="16" /> Save &amp; open for payment
                </button>
                <button type="submit" class="btn btn-glass w-full" :disabled="form.processing"><Save :size="16" /> Save as draft</button>
                <p class="text-xs text-muted">Drafts aren't visible to clients. Open invoices can be paid via their link.</p>
            </template>
            <button v-else type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                <Loader2 v-if="form.processing" :size="16" class="animate-spin" /><Save v-else :size="16" /> Save changes
            </button>
        </aside>
    </form>
</template>
