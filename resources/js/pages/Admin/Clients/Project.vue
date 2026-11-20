<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Save, Loader2, Plus, Trash2, Flag, CheckCircle2, Circle, Receipt, Paperclip, MessagesSquare, Star, Eye, Globe } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import ProgressBar from '@/components/portal/ProgressBar.vue';
import Timeline from '@/components/portal/Timeline.vue';
import FileList from '@/components/portal/FileList.vue';
import FileUpload from '@/components/portal/FileUpload.vue';
import InvoiceList from '@/components/portal/InvoiceList.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { date } from '@/lib/format';

const props = defineProps({ project: Object, client: Object, milestones: Array, updates: Array, files: Array, invoices: Array, statuses: Array, case_studies: Array, upload: Object });

const form = useForm({
    title: props.project.title,
    summary: props.project.summary ?? '',
    status: props.project.status,
    staging_url: props.project.staging_url ?? '',
    started_at: props.project.started_at ?? '',
    due_at: props.project.due_at ?? '',
    project_id: props.project.project_id,
});
const save = () => form.patch(`/admin/client-projects/${props.project.id}`, { preserveScroll: true });

const milestone = useForm({ title: '', due_at: '' });
const addMilestone = () => milestone.post(`/admin/client-projects/${props.project.id}/milestones`, { preserveScroll: true, onSuccess: () => milestone.reset() });
const toggle = (m) => router.patch(`/admin/client-milestones/${m.id}`, { done: !m.completed_at }, { preserveScroll: true });
const editing = ref(null);
const rename = (m, title) => {
    editing.value = null;
    if (title.trim() && title !== m.title) router.patch(`/admin/client-milestones/${m.id}`, { title }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="project.title" />
    <PageHeader :title="project.title" :description="client.company || client.name" :back="`/admin/clients/${client.id}`">
        <Link :href="`/admin/invoices/create?client=${client.id}&project=${project.id}`" class="btn btn-glass btn-sm"><Receipt :size="14" /> Invoice this project</Link>
        <button type="button" class="btn btn-glass btn-sm" @click="router.post(`/admin/client-projects/${project.id}/testimonial`, {}, { preserveScroll: true })"><Star :size="14" /> Request testimonial</button>
        <button type="button" class="btn btn-danger btn-sm" @click="confirmVisit('Delete this project with its milestones and updates? Files stay on the client.', `/admin/client-projects/${project.id}`)"><Trash2 :size="14" /></button>
    </PageHeader>

    <div v-if="!client.portal_users" class="mb-4 flex items-center gap-2 rounded-2xl bg-sky-400/10 p-4 text-sm text-sky-200 shadow-[inset_0_0_0_1px_rgb(56_189_248/.2)]">
        <Eye :size="16" /> The client hasn't joined the portal yet — <Link :href="`/admin/clients/${client.id}`" class="underline underline-offset-2">send them their invite link</Link>.
    </div>

    <div class="grid gap-4 xl:grid-cols-5">
        <div class="space-y-4 xl:col-span-2">
            <form class="card-solid space-y-4 p-6" @submit.prevent="save">
                <ProgressBar :value="project.progress" :label="project.milestones_total ? `${project.milestones_done} of ${project.milestones_total} milestones` : 'Progress'" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field label="Title" for="title" :error="form.errors.title" class="sm:col-span-2"><input id="title" v-model="form.title" class="field" required /></Field>
                    <Field label="Status" for="status"><select id="status" v-model="form.status" class="field"><option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option></select></Field>
                    <Field label="Linked case study" for="cs" optional><select id="cs" v-model="form.project_id" class="field"><option :value="null">None</option><option v-for="c in case_studies" :key="c.id" :value="c.id">{{ c.title }}</option></select></Field>
                    <Field label="Started" for="started"><input id="started" v-model="form.started_at" type="date" class="field" /></Field>
                    <Field label="Target date" for="due" optional :error="form.errors.due_at"><input id="due" v-model="form.due_at" type="date" class="field" /></Field>
                    <Field label="Preview / staging URL" for="staging" optional :error="form.errors.staging_url" class="sm:col-span-2"><input id="staging" v-model="form.staging_url" type="url" class="field" placeholder="https://staging.example.com" /></Field>
                    <Field label="Summary (shown to the client)" for="summary" optional class="sm:col-span-2"><textarea id="summary" v-model="form.summary" rows="3" class="field"></textarea></Field>
                </div>
                <button type="submit" class="btn btn-light btn-sm w-full" :disabled="form.processing || !form.isDirty"><Loader2 v-if="form.processing" :size="14" class="animate-spin" /><Save v-else :size="14" /> Save project</button>
            </form>

            <section class="card-solid p-6">
                <h2 class="title flex items-center gap-2 text-lg"><Flag :size="18" class="text-accent-hot" /> Milestones</h2>
                <p class="mt-1 text-xs text-muted">Ticking these off moves the client's progress bar.</p>
                <ol class="mt-4 space-y-1">
                    <li v-for="m in milestones" :key="m.id" class="group flex items-center gap-3 rounded-xl p-2 hover:bg-white/[0.03]">
                        <button type="button" :aria-label="m.completed_at ? 'Mark not done' : 'Mark done'" @click="toggle(m)">
                            <CheckCircle2 v-if="m.completed_at" :size="20" class="text-emerald-300" />
                            <Circle v-else :size="20" class="text-faint hover:text-text-2" />
                        </button>
                        <input v-if="editing === m.id" :value="m.title" class="field h-8 flex-1 px-2 text-sm" autofocus @keydown.enter.prevent="rename(m, $event.target.value)" @blur="rename(m, $event.target.value)" @keydown.esc="editing = null" />
                        <button v-else type="button" class="min-w-0 flex-1 truncate text-left text-sm" :class="m.completed_at && 'text-muted line-through decoration-white/20'" title="Click to rename" @click="editing = m.id">{{ m.title }}</button>
                        <span class="text-xs text-muted">{{ m.completed_at ? date(m.completed_at) : m.due_at ? `Due ${date(m.due_at)}` : '' }}</span>
                        <button type="button" class="text-faint opacity-0 hover:text-accent-hot group-hover:opacity-100" aria-label="Delete milestone" @click="confirmVisit(`Delete “${m.title}”?`, `/admin/client-milestones/${m.id}`)"><Trash2 :size="14" /></button>
                    </li>
                </ol>
                <form class="mt-3 flex gap-2" @submit.prevent="addMilestone">
                    <input v-model="milestone.title" class="field h-10 flex-1 text-sm" placeholder="Add a milestone…" aria-label="Milestone title" required />
                    <input v-model="milestone.due_at" type="date" class="field h-10 w-36 text-sm" aria-label="Due date" />
                    <button type="submit" class="btn btn-glass btn-icon h-10 w-10" aria-label="Add milestone" :disabled="milestone.processing"><Plus :size="16" /></button>
                </form>
            </section>

            <section class="card-solid p-6">
                <h2 class="title flex items-center gap-2 text-lg"><Paperclip :size="18" class="text-accent-hot" /> Files</h2>
                <FileList v-if="files.length" class="mt-2" :files="files" :delete-url="(f) => `/admin/client-files/${f.id}`" />
                <FileUpload class="mt-4" :url="`/admin/client-projects/${project.id}/files`" :project-id="project.id" :upload="upload" />
            </section>

            <section v-if="invoices.length" class="card-solid p-6">
                <h2 class="title flex items-center gap-2 text-lg"><Receipt :size="18" class="text-accent-hot" /> Invoices</h2>
                <InvoiceList class="mt-3" :invoices="invoices" admin />
            </section>
        </div>

        <section class="card-solid p-6 xl:col-span-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="title flex items-center gap-2 text-lg"><MessagesSquare :size="18" class="text-accent-hot" /> Updates &amp; messages</h2>
                <a v-if="project.staging_url" :href="project.staging_url" target="_blank" rel="noopener" class="btn btn-glass btn-sm"><Globe :size="14" /> Staging</a>
            </div>
            <p class="mb-5 mt-1 text-sm text-muted">Post progress updates — the client sees them on their portal and can reply here.</p>
            <Timeline :updates="updates" :post-url="`/admin/client-projects/${project.id}/updates`" :delete-url="(u) => `/admin/client-updates/${u.id}`" placeholder="What's new on this project?" button="Post update" />
        </section>
    </div>
</template>
