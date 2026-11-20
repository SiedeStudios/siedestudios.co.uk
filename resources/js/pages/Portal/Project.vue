<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarClock, CheckCircle2, Circle, ExternalLink, Globe, Paperclip, Receipt, MessagesSquare, Flag } from 'lucide-vue-next';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import ProgressBar from '@/components/portal/ProgressBar.vue';
import Timeline from '@/components/portal/Timeline.vue';
import FileList from '@/components/portal/FileList.vue';
import FileUpload from '@/components/portal/FileUpload.vue';
import InvoiceList from '@/components/portal/InvoiceList.vue';
import { date } from '@/lib/format';

const props = defineProps({ project: Object, milestones: Array, updates: Array, files: Array, invoices: Array, upload: Object, me: Number });
const nextId = computed(() => props.milestones.find((m) => !m.completed_at)?.id);
</script>

<template>
    <Head :title="project.title" />
    <div class="wrap py-10 lg:py-14">
        <Link href="/portal" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-text"><ArrowLeft :size="14" /> All projects</Link>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-3"><h1 class="heading text-[clamp(2rem,4vw,2.75rem)]">{{ project.title }}</h1><StatusBadge :status="project.status" /></div>
                <p v-if="project.summary" class="mt-3 whitespace-pre-line text-text-2">{{ project.summary }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a v-if="project.staging_url" :href="project.staging_url" target="_blank" rel="noopener" class="btn btn-light btn-sm"><Globe :size="14" /> Preview site <ExternalLink :size="13" /></a>
                <a v-if="project.case_study" :href="project.case_study.url" class="btn btn-glass btn-sm">Case study <ExternalLink :size="13" /></a>
            </div>
        </div>

        <div class="card-solid mt-8 grid gap-6 p-6 sm:grid-cols-3">
            <ProgressBar class="sm:col-span-2" :value="project.progress" :label="project.milestones_total ? `${project.milestones_done} of ${project.milestones_total} milestones complete` : 'Progress'" />
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-muted">Started</dt><dd class="mt-1">{{ date(project.started_at) }}</dd></div>
                <div><dt class="text-xs text-muted">{{ project.status === 'completed' ? 'Completed' : 'Target date' }}</dt><dd class="mt-1">{{ date(project.status === 'completed' ? project.completed_at : project.due_at) }}</dd></div>
            </dl>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-5">
            <div class="space-y-4 lg:col-span-2">
                <section class="card-solid p-6">
                    <h2 class="title flex items-center gap-2 text-lg"><Flag :size="18" class="text-accent-hot" /> Milestones</h2>
                    <ol v-if="milestones.length" class="mt-4 space-y-1">
                        <li v-for="m in milestones" :key="m.id" class="flex items-start gap-3 rounded-xl p-2.5" :class="m.id === nextId && 'bg-white/[0.04] shadow-[inset_0_0_0_1px_var(--line)]'">
                            <CheckCircle2 v-if="m.completed_at" :size="19" class="mt-px shrink-0 text-emerald-300" />
                            <Circle v-else :size="19" class="mt-px shrink-0" :class="m.id === nextId ? 'text-accent-hot' : 'text-faint'" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm" :class="m.completed_at ? 'text-muted line-through decoration-white/20' : ''">{{ m.title }}</span>
                                <span v-if="m.completed_at || m.due_at" class="mt-0.5 flex items-center gap-1 text-xs text-muted"><CalendarClock :size="12" />{{ m.completed_at ? `Done ${date(m.completed_at)}` : `Due ${date(m.due_at)}` }}</span>
                            </span>
                            <span v-if="m.id === nextId" class="text-xs font-medium text-accent-hot">Up next</span>
                        </li>
                    </ol>
                    <p v-else class="mt-4 text-sm text-muted">Milestones will be added once the plan is agreed.</p>
                </section>

                <section v-if="invoices.length" class="card-solid p-6">
                    <h2 class="title flex items-center gap-2 text-lg"><Receipt :size="18" class="text-accent-hot" /> Invoices</h2>
                    <InvoiceList class="mt-3" :invoices="invoices" />
                </section>

                <section class="card-solid p-6">
                    <h2 class="title flex items-center gap-2 text-lg"><Paperclip :size="18" class="text-accent-hot" /> Files</h2>
                    <FileList v-if="files.length" class="mt-2" :files="files" :delete-url="(f) => `/portal/files/${f.id}`" :can-delete="(f) => f.user_id === me" />
                    <FileUpload class="mt-4" url="/portal/files" :project-id="project.id" :upload="upload" />
                </section>
            </div>

            <section class="card-solid p-6 lg:col-span-3">
                <h2 class="title flex items-center gap-2 text-lg"><MessagesSquare :size="18" class="text-accent-hot" /> Updates &amp; messages</h2>
                <p class="mb-5 mt-1 text-sm text-muted">Questions, feedback or changes — post them here and we'll get a notification.</p>
                <Timeline :updates="updates" :post-url="`/portal/projects/${project.id}/updates`" placeholder="Ask a question or leave feedback…" />
            </section>
        </div>
    </div>
</template>
