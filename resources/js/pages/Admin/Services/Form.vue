<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Save, Loader2 } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import Toggle from '@/components/admin/Toggle.vue';
import ServiceIcon from '@/components/ServiceIcon.vue';

const props = defineProps({ service: Object, icons: Array });
const editing = computed(() => !!props.service);

const form = useForm({
    title: props.service?.title ?? '',
    slug: props.service?.slug ?? '',
    icon: props.service?.icon ?? 'code',
    tagline: props.service?.tagline ?? '',
    description: props.service?.description ?? '',
    features: (props.service?.features ?? []).join('\n'),
    starting_price: props.service?.starting_price ?? '',
    price_note: props.service?.price_note ?? 'Quoted per project',
    is_active: props.service?.is_active ?? true,
});

const submit = () => (editing.value ? form.put(`/admin/services/${props.service.id}`) : form.post('/admin/services'));
</script>

<template>
    <Head :title="editing ? `Edit ${service.title}` : 'New service'" />
    <PageHeader :title="editing ? service.title : 'New service'" back="/admin/services" />

    <form class="grid gap-4 xl:grid-cols-3" @submit.prevent="submit">
        <section class="card-solid space-y-5 p-6 xl:col-span-2">
            <div class="grid gap-5 sm:grid-cols-2">
                <Field label="Title" for="title" :error="form.errors.title"><input id="title" v-model="form.title" class="field" required /></Field>
                <Field label="Slug" for="slug" :error="form.errors.slug" help="Used for /services#slug and contact pre-selection." optional><input id="slug" v-model="form.slug" class="field font-mono text-sm" /></Field>
            </div>
            <Field label="Tagline" for="tagline" :error="form.errors.tagline"><input id="tagline" v-model="form.tagline" class="field" maxlength="160" /></Field>
            <Field label="Description" for="description" :error="form.errors.description"><textarea id="description" v-model="form.description" rows="4" class="field"></textarea></Field>
            <Field label="What's included" for="features" :error="form.errors.features" help="One item per line."><textarea id="features" v-model="form.features" rows="6" class="field"></textarea></Field>
        </section>
        <aside class="card-solid h-fit space-y-5 p-6">
            <Field label="Icon">
                <div class="grid grid-cols-5 gap-2">
                    <button v-for="icon in icons" :key="icon" type="button" class="grid aspect-square place-items-center rounded-xl transition-all" :class="form.icon === icon ? 'text-white shadow-[0_8px_24px_-8px_var(--accent)]' : 'bg-white/[0.04] text-muted hover:text-text'" :style="form.icon === icon ? { background: 'var(--gradient)' } : {}" :aria-label="icon" :aria-pressed="form.icon === icon" @click="form.icon = icon">
                        <ServiceIcon :name="icon" :size="18" />
                    </button>
                </div>
            </Field>
            <Field label="Starting price" for="price" :error="form.errors.starting_price" help="Leave empty to show only the note." optional>
                <input id="price" v-model="form.starting_price" type="number" step="0.01" min="0" class="field" />
            </Field>
            <Field label="Price note" for="note" :error="form.errors.price_note"><input id="note" v-model="form.price_note" class="field" /></Field>
            <Toggle v-model="form.is_active" label="Visible on site" />
            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                <Loader2 v-if="form.processing" :size="16" class="animate-spin" /><Save v-else :size="16" /> {{ editing ? 'Save changes' : 'Create service' }}
            </button>
        </aside>
    </form>
</template>
