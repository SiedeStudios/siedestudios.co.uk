<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Save, Loader2, Send, Trash2 } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import Toggle from '@/components/admin/Toggle.vue';
import ImageInput from '@/components/admin/ImageInput.vue';
import CopyField from '@/components/admin/CopyField.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import StarRating from '@/components/StarRating.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago } from '@/lib/format';

const props = defineProps({ testimonial: Object, projects: Array, statuses: Array });
const editing = computed(() => !!props.testimonial);

const form = useForm({
    name: props.testimonial?.name ?? '',
    company: props.testimonial?.company ?? '',
    role: props.testimonial?.role ?? '',
    project_id: props.testimonial?.project_id ?? '',
    avatar: null,
    remove_avatar: false,
    ...(props.testimonial ? {
        body: props.testimonial.body ?? '',
        rating: props.testimonial.rating ?? 5,
        status: props.testimonial.status,
        is_featured: props.testimonial.is_featured,
    } : {}),
});

const submit = () => {
    const options = { preserveScroll: true, forceFormData: true, onSuccess: () => form.reset('avatar', 'remove_avatar') };
    editing.value
        ? form.transform((d) => ({ ...d, _method: 'put' })).post(`/admin/testimonials/${props.testimonial.id}`, options)
        : form.post('/admin/testimonials', options);
};
</script>

<template>
    <Head :title="editing ? `Testimonial — ${testimonial.name}` : 'Invite a client'" />
    <PageHeader :title="editing ? testimonial.name : 'Invite a client'" :description="editing ? null : 'Set up their profile — they only need to write a few words and pick a rating.'" back="/admin/testimonials">
        <StatusBadge v-if="editing" :status="testimonial.status" />
        <button v-if="editing" type="button" class="btn btn-danger btn-sm" @click="confirmVisit('Delete this testimonial?', `/admin/testimonials/${testimonial.id}`)"><Trash2 :size="15" /></button>
    </PageHeader>

    <section v-if="editing" class="card-solid mb-4 p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="title text-lg">Invite link</h2>
            <span class="text-sm text-muted">{{ testimonial.status === 'invited' ? 'Not used yet' : `Submitted ${ago(testimonial.submitted_at)}` }}</span>
        </div>
        <p class="mt-1 text-sm text-muted">Send this private link to {{ testimonial.name.split(' ')[0] }}. It works once.</p>
        <CopyField class="mt-4" :value="testimonial.invite_url" />
    </section>

    <form class="grid gap-4 xl:grid-cols-3" @submit.prevent="submit">
        <section class="card-solid space-y-5 p-6 xl:col-span-2">
            <div class="grid gap-5 sm:grid-cols-2">
                <Field label="Client name" for="name" :error="form.errors.name"><input id="name" v-model="form.name" class="field" required /></Field>
                <Field label="Company" for="company" :error="form.errors.company" optional><input id="company" v-model="form.company" class="field" /></Field>
                <Field label="Role / title" for="role" :error="form.errors.role" optional><input id="role" v-model="form.role" class="field" placeholder="Founder" /></Field>
                <Field label="Attach to project" for="project" :error="form.errors.project_id" help="Shown on that project's page once approved." optional>
                    <select id="project" v-model="form.project_id" class="field">
                        <option value="">None</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option>
                    </select>
                </Field>
            </div>
            <template v-if="editing">
                <Field label="Rating" :error="form.errors.rating"><StarRating v-model="form.rating" interactive :size="24" /></Field>
                <Field label="Testimonial" for="body" :error="form.errors.body" help="You can tidy typos, but keep the client's words."><textarea id="body" v-model="form.body" rows="5" class="field"></textarea></Field>
            </template>
        </section>
        <aside class="card-solid h-fit space-y-5 p-6">
            <Field label="Avatar / logo" :error="form.errors.avatar" optional>
                <ImageInput v-model="form.avatar" v-model:remove="form.remove_avatar" :current="testimonial?.avatar" aspect="aspect-square max-w-48" />
            </Field>
            <template v-if="editing">
                <Field label="Status" for="status" :error="form.errors.status">
                    <select id="status" v-model="form.status" class="field">
                        <option v-for="s in statuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                    </select>
                </Field>
                <Toggle v-model="form.is_featured" label="Featured" description="Shown first on the home page." />
            </template>
            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                <template v-else-if="editing"><Save :size="16" /> Save changes</template>
                <template v-else><Send :size="16" /> Create invite link</template>
            </button>
        </aside>
    </form>
</template>
