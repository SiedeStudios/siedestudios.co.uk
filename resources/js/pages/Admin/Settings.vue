<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, Loader2, Settings2, Palette, Contact, CreditCard, Bell, ShieldCheck, Search, ShoppingBag, KeyRound, ArrowRight, Scale } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import Toggle from '@/components/admin/Toggle.vue';
import ImageInput from '@/components/admin/ImageInput.vue';
import CopyField from '@/components/admin/CopyField.vue';

const props = defineProps({ groups: Array, group: Object, fields: Array, context: Object });

const icons = { general: Settings2, branding: Palette, contact: Contact, payments: CreditCard, legal: Scale, gmodstore: ShoppingBag, notifications: Bell, access: ShieldCheck, seo: Search };

const initial = {};
props.fields.forEach((f) => {
    initial[f.key] = f.type === 'image' ? null : f.value ?? (f.type === 'toggle' ? false : '');
    if (f.type === 'image') initial[`remove_${f.key}`] = false;
    if (f.type === 'secret') initial[`clear_${f.key}`] = false;
});
const form = useForm(initial);

const submit = () => form.post(`/admin/settings/${props.group.key}`, {
    preserveScroll: true,
    forceFormData: props.fields.some((f) => f.type === 'image'),
    onSuccess: () => props.fields.filter((f) => ['image', 'secret'].includes(f.type)).forEach((f) => form.reset(f.key, `remove_${f.key}`, `clear_${f.key}`)),
});
const imageFields = computed(() => props.fields.filter((f) => f.type === 'image'));
const otherFields = computed(() => props.fields.filter((f) => f.type !== 'image'));
</script>

<template>
    <Head title="Settings" />
    <PageHeader title="Settings" description="Everything here is stored in the database and takes effect immediately." />

    <div class="grid gap-6 lg:grid-cols-[14rem_1fr]">
        <nav class="flex gap-1 overflow-x-auto lg:flex-col" aria-label="Settings sections">
            <Link
                v-for="g in groups"
                :key="g.key"
                :href="`/admin/settings/${g.key}`"
                preserve-scroll
                class="flex h-10 shrink-0 items-center gap-3 rounded-xl px-3 text-sm transition-colors"
                :class="g.key === group.key ? 'bg-white/[0.07] text-text shadow-[inset_0_0_0_1px_var(--line)]' : 'text-text-2 hover:bg-white/[0.04] hover:text-text'"
            >
                <component :is="icons[g.key] ?? Settings2" :size="16" :class="g.key === group.key ? 'text-accent-hot' : 'text-muted'" /> {{ g.label }}
            </Link>
        </nav>

        <form class="space-y-4" @submit.prevent="submit">
            <section class="card-solid p-6 sm:p-8">
                <h2 class="title text-xl">{{ group.label }}</h2>
                <p v-if="group.description" class="mt-1 text-sm text-muted">{{ group.description }}</p>

                <div v-if="imageFields.length" class="mt-7 grid gap-6 sm:grid-cols-3">
                    <Field v-for="f in imageFields" :key="f.key" :label="f.label" :error="form.errors[f.key]" :help="f.help">
                        <ImageInput v-model="form[f.key]" v-model:remove="form[`remove_${f.key}`]" :current="f.preview" :aspect="f.key === 'favicon' ? 'aspect-square max-w-36' : f.key === 'og_image' ? 'aspect-[1200/630]' : 'aspect-[3/1]'" fit="object-contain p-3" />
                    </Field>
                </div>

                <div class="mt-7 grid gap-6 sm:grid-cols-2">
                    <template v-for="f in otherFields" :key="f.key">
                        <div v-if="f.type === 'toggle'" class="rounded-2xl bg-white/[0.03] p-4 shadow-[inset_0_0_0_1px_var(--line)] sm:col-span-2">
                            <Toggle v-model="form[f.key]" :label="f.label" :description="f.help" />
                        </div>
                        <Field v-else :label="f.label" :for="f.key" :error="form.errors[f.key]" :help="f.help" :class="f.type === 'textarea' ? 'sm:col-span-2' : ''">
                            <textarea v-if="f.type === 'textarea'" :id="f.key" v-model="form[f.key]" rows="3" class="field"></textarea>
                            <select v-else-if="f.type === 'select'" :id="f.key" v-model="form[f.key]" class="field"><option v-for="o in f.options" :key="o" :value="o">{{ o }}</option></select>
                            <div v-else-if="f.type === 'color'" class="flex items-center gap-3">
                                <input :id="f.key" v-model="form[f.key]" type="color" class="h-12 w-14 cursor-pointer rounded-xl border border-line-2 bg-transparent p-1" />
                                <input v-model="form[f.key]" class="field font-mono uppercase" maxlength="7" :aria-label="`${f.label} hex`" />
                            </div>
                            <div v-else-if="f.type === 'secret'" class="space-y-2">
                                <input :id="f.key" v-model="form[f.key]" type="password" autocomplete="off" class="field font-mono" :placeholder="f.is_set ? '•••••••••••• saved — type to replace' : 'Not set'" :disabled="form[`clear_${f.key}`]" />
                                <label v-if="f.is_set" class="flex items-center gap-2 text-xs text-muted"><input v-model="form[`clear_${f.key}`]" type="checkbox" class="accent-[var(--accent)]" /> Remove the saved key</label>
                            </div>
                            <input v-else :id="f.key" v-model="form[f.key]" :type="f.type === 'email' ? 'email' : f.type === 'url' ? 'url' : 'text'" class="field" />
                        </Field>
                    </template>
                </div>

                <div v-if="group.key === 'payments' && context" class="mt-8 rounded-2xl bg-white/[0.03] p-5 shadow-[inset_0_0_0_1px_var(--line)]">
                    <div class="text-sm font-medium">IPN listener</div>
                    <p class="mt-1 text-sm text-muted">Sent with every checkout automatically. Optionally paste it into PayPal → Settings → Notifications → Instant payment notifications.</p>
                    <CopyField class="mt-3" :value="context.ipn_url" :open="false" />
                </div>
                <div v-if="group.key === 'gmodstore' && context" class="mt-8 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white/[0.03] p-5 text-sm shadow-[inset_0_0_0_1px_var(--line)]">
                    <span class="flex items-center gap-2 text-muted"><KeyRound :size="15" /> The key is stored encrypted and is never sent back to the browser.</span>
                    <Link :href="context.store_admin" class="btn btn-glass btn-sm">Manage products <ArrowRight :size="14" /></Link>
                </div>
                <div v-if="group.key === 'access' && context" class="mt-8 space-y-2 rounded-2xl bg-white/[0.03] p-5 text-sm shadow-[inset_0_0_0_1px_var(--line)]">
                    <div>Your Discord ID: <span class="font-mono text-text">{{ context.me }}</span></div>
                    <div class="text-muted">From <code class="font-mono text-xs">.env</code> (always allowed): <span class="font-mono">{{ context.env_ids.join(', ') || 'none' }}</span></div>
                    <p class="text-muted">Removing an ID here signs that account out on its next request.</p>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <span v-if="form.isDirty" class="text-sm text-amber-300">Unsaved changes</span>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <Loader2 v-if="form.processing" :size="16" class="animate-spin" /><Save v-else :size="16" /> Save {{ group.label.toLowerCase() }}
                </button>
            </div>
        </form>
    </div>
</template>
