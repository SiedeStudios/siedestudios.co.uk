<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Save, Loader2, Link2, RefreshCw, Ban, Trash2, Plus, FolderKanban, Receipt, Paperclip, UserRound, Star, ArrowUpRight, CalendarClock } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import CopyField from '@/components/admin/CopyField.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import ProgressBar from '@/components/portal/ProgressBar.vue';
import FileList from '@/components/portal/FileList.vue';
import FileUpload from '@/components/portal/FileUpload.vue';
import InvoiceList from '@/components/portal/InvoiceList.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago, date } from '@/lib/format';

const props = defineProps({ client: Object, users: Array, projects: Array, invoices: Array, files: Array, testimonials: Array, statuses: Array, case_studies: Array, upload: Object });

const details = useForm({ name: props.client.name, company: props.client.company ?? '', email: props.client.email ?? '', notes: props.client.notes ?? '' });
const save = () => details.patch(`/admin/clients/${props.client.id}`, { preserveScroll: true });

const newProject = ref(false);
const project = useForm({ title: '', summary: '', due_at: '', project_id: null });
const createProject = () => project.post(`/admin/clients/${props.client.id}/projects`);

const post = (url) => router.post(url, {}, { preserveScroll: true });
</script>

<template>
    <Head :title="client.company || client.name" />
    <PageHeader :title="client.company || client.name" :description="client.company ? client.name : null" back="/admin/clients">
        <Link :href="`/admin/invoices/create?client=${client.id}`" class="btn btn-glass btn-sm"><Receipt :size="14" /> New invoice</Link>
        <button type="button" class="btn btn-danger btn-sm" @click="confirmVisit(`Delete ${client.name}? Their projects, files and portal access are removed. Invoices are kept.`, `/admin/clients/${client.id}`)"><Trash2 :size="14" /> Delete</button>
    </PageHeader>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <!-- Projects -->
            <section class="card-solid p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="title flex items-center gap-2 text-lg"><FolderKanban :size="18" class="text-accent-hot" /> Projects</h2>
                    <button type="button" class="btn btn-glass btn-sm" @click="newProject = !newProject"><Plus :size="14" /> New project</button>
                </div>
                <form v-if="newProject" class="mt-5 grid gap-4 rounded-2xl bg-bg/60 p-4 shadow-[inset_0_0_0_1px_var(--line)] sm:grid-cols-2" @submit.prevent="createProject">
                    <Field label="Title" for="p-title" :error="project.errors.title" class="sm:col-span-2"><input id="p-title" v-model="project.title" class="field" required /></Field>
                    <Field label="Summary" for="p-summary" optional class="sm:col-span-2"><textarea id="p-summary" v-model="project.summary" rows="2" class="field"></textarea></Field>
                    <Field label="Target date" for="p-due" optional><input id="p-due" v-model="project.due_at" type="date" class="field" /></Field>
                    <Field label="Linked case study" for="p-cs" optional><select id="p-cs" v-model="project.project_id" class="field"><option :value="null">None</option><option v-for="c in case_studies" :key="c.id" :value="c.id">{{ c.title }}</option></select></Field>
                    <div class="flex justify-end sm:col-span-2"><button type="submit" class="btn btn-primary btn-sm" :disabled="project.processing"><Plus :size="14" /> Create project</button></div>
                </form>
                <ul v-if="projects.length" class="mt-4 grid gap-3 md:grid-cols-2">
                    <li v-for="p in projects" :key="p.id">
                        <Link :href="`/admin/client-projects/${p.id}`" class="group block rounded-2xl bg-white/[0.03] p-4 shadow-[inset_0_0_0_1px_var(--line)] transition-colors hover:bg-white/[0.05]">
                            <div class="flex items-start justify-between gap-3"><span class="font-medium">{{ p.title }}</span><StatusBadge :status="p.status" /></div>
                            <ProgressBar class="mt-4" :value="p.progress" :label="p.milestones_total ? `${p.milestones_done}/${p.milestones_total} milestones` : 'No milestones yet'" />
                            <p class="mt-3 flex items-center justify-between text-xs text-muted"><span class="flex items-center gap-1"><CalendarClock :size="12" /> {{ p.due_at ? `Due ${date(p.due_at)}` : 'No target date' }}</span><span>Updated {{ ago(p.updated_at) }}</span></p>
                        </Link>
                    </li>
                </ul>
                <p v-else-if="!newProject" class="mt-4 text-sm text-muted">No projects yet.</p>
            </section>

            <!-- Files -->
            <section class="card-solid grid gap-6 p-6 md:grid-cols-5">
                <div class="md:col-span-3">
                    <h2 class="title flex items-center gap-2 text-lg"><Paperclip :size="18" class="text-accent-hot" /> Files</h2>
                    <FileList v-if="files.length" class="mt-2" :files="files" show-project :delete-url="(f) => `/admin/client-files/${f.id}`" />
                    <p v-else class="mt-4 text-sm text-muted">Nothing shared yet.</p>
                </div>
                <div class="md:col-span-2">
                    <h3 class="text-sm font-medium">Share a file</h3>
                    <p class="mb-3 mt-1 text-sm text-muted">It appears in their portal straight away.</p>
                    <FileUpload :url="`/admin/clients/${client.id}/files`" :projects="projects.map((p) => ({ id: p.id, title: p.title }))" :upload="upload" />
                </div>
            </section>

            <!-- Invoices -->
            <section class="card-solid p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="title flex items-center gap-2 text-lg"><Receipt :size="18" class="text-accent-hot" /> Invoices</h2>
                    <Link :href="`/admin/invoices/create?client=${client.id}`" class="btn btn-glass btn-sm"><Plus :size="14" /> New invoice</Link>
                </div>
                <InvoiceList v-if="invoices.length" class="mt-3" :invoices="invoices" admin />
                <p v-else class="mt-4 text-sm text-muted">No invoices linked to this client. Drafts stay hidden from the portal until published.</p>
            </section>
        </div>

        <aside class="space-y-4">
            <!-- Portal access -->
            <section class="card-solid p-6">
                <h2 class="title flex items-center gap-2 text-lg"><UserRound :size="18" class="text-accent-hot" /> Portal access</h2>
                <ul v-if="users.length" class="mt-4 space-y-3">
                    <li v-for="u in users" :key="u.id" class="flex items-center gap-3">
                        <img v-if="u.avatar" :src="u.avatar" alt="" class="size-9 rounded-full" />
                        <span v-else class="grid size-9 place-items-center rounded-full bg-surface-2 text-sm">{{ u.name[0] }}</span>
                        <span class="min-w-0 flex-1"><span class="block truncate text-sm font-medium">{{ u.name }}</span><span class="block truncate text-xs text-muted">@{{ u.discord_username }} · {{ u.last_login_at ? `seen ${ago(u.last_login_at)}` : 'never signed in' }}</span></span>
                        <button type="button" class="grid size-8 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" :aria-label="`Remove ${u.name}`" @click="confirmVisit(`Remove ${u.name}'s portal access?`, `/admin/clients/${client.id}/users/${u.id}`)"><Ban :size="15" /></button>
                    </li>
                </ul>
                <p v-else class="mt-3 text-sm text-muted">Nobody has joined yet.</p>

                <div class="mt-5 border-t border-line pt-5">
                    <template v-if="client.invite_url">
                        <p class="mb-2 flex items-center gap-1.5 text-sm"><Link2 :size="14" class="text-accent-hot" /> Invite link <span class="text-xs text-muted">· expires {{ date(client.invite_expires_at) }}</span></p>
                        <CopyField :value="client.invite_url" :open="false" />
                        <p class="mt-2 text-xs text-muted">Works once. Send another for each extra person who needs access.</p>
                        <div class="mt-3 flex gap-2">
                            <button type="button" class="btn btn-glass btn-sm" @click="post(`/admin/clients/${client.id}/invite`)"><RefreshCw :size="13" /> New link</button>
                            <button type="button" class="btn btn-glass btn-sm" @click="confirmVisit('Revoke this invite link?', `/admin/clients/${client.id}/invite`)"><Ban :size="13" /> Revoke</button>
                        </div>
                    </template>
                    <button v-else type="button" class="btn btn-light btn-sm w-full" @click="post(`/admin/clients/${client.id}/invite`)"><Link2 :size="14" /> Create invite link</button>
                </div>
            </section>

            <!-- Details -->
            <form class="card-solid space-y-4 p-6" @submit.prevent="save">
                <h2 class="title text-lg">Details</h2>
                <Field label="Contact name" for="c-name" :error="details.errors.name"><input id="c-name" v-model="details.name" class="field" required /></Field>
                <Field label="Company" for="c-company" optional><input id="c-company" v-model="details.company" class="field" /></Field>
                <Field label="Email" for="c-email" optional :error="details.errors.email"><input id="c-email" v-model="details.email" type="email" class="field" /></Field>
                <Field label="Private notes" for="c-notes" optional help="Only visible to you."><textarea id="c-notes" v-model="details.notes" rows="3" class="field"></textarea></Field>
                <button type="submit" class="btn btn-light btn-sm w-full" :disabled="details.processing || !details.isDirty"><Loader2 v-if="details.processing" :size="14" class="animate-spin" /><Save v-else :size="14" /> Save details</button>
            </form>

            <section v-if="testimonials.length" class="card-solid p-6">
                <h2 class="title flex items-center gap-2 text-lg"><Star :size="18" class="text-accent-hot" /> Testimonials</h2>
                <ul class="mt-3 space-y-2">
                    <li v-for="t in testimonials" :key="t.id" class="flex items-center justify-between gap-3 text-sm">
                        <Link :href="`/admin/testimonials/${t.id}/edit`" class="truncate hover:text-accent-hot">{{ t.project || 'General' }}</Link>
                        <StatusBadge :status="t.status" />
                    </li>
                </ul>
            </section>
        </aside>
    </div>
</template>
