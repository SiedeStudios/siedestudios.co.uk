<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, CalendarClock, Flag, FolderKanban, MessageSquareQuote, Receipt, Paperclip, Star } from 'lucide-vue-next';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import ProgressBar from '@/components/portal/ProgressBar.vue';
import FileList from '@/components/portal/FileList.vue';
import FileUpload from '@/components/portal/FileUpload.vue';
import InvoiceList from '@/components/portal/InvoiceList.vue';
import { useMotion } from '@/composables/useMotion';
import { ago, date, money } from '@/lib/format';
import { gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({ client: Object, projects: Array, invoices: Array, testimonials: Array, updates: Array, files: Array, upload: Object });

const user = computed(() => usePage().props.auth.user);
const due = computed(() => props.invoices.filter((i) => i.status === 'open'));
const dueTotal = computed(() => {
    const byCurrency = {};
    due.value.forEach((i) => (byCurrency[i.currency] = (byCurrency[i.currency] ?? 0) + Number(i.total)));
    return Object.entries(byCurrency).map(([c, t]) => money(t, c)).join(' + ');
});
const active = computed(() => props.projects.filter((p) => !['completed', 'on_hold'].includes(p.status)));
const greeting = (() => { const h = new Date().getHours(); return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening'; })();

const root = ref(null);
useMotion(root, (el) => {
    if (!prefersReducedMotion()) gsap.from(el.querySelectorAll('[data-in]'), { opacity: 0, y: 24, filter: 'blur(6px)', duration: 1, stagger: 0.06, clearProps: 'filter' });
}, { reveal: false });
</script>

<template>
    <Head title="Client portal" />
    <div ref="root" class="wrap py-10 lg:py-14">
        <div data-in class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-sm text-muted">{{ client.company || client.name }}</p>
                <h1 class="heading mt-1 text-[clamp(2rem,4vw,2.75rem)]">{{ greeting }}, {{ user.name.split(' ')[0] }}</h1>
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <div class="card px-4 py-3"><span class="display text-xl">{{ active.length }}</span> <span class="text-muted">active {{ active.length === 1 ? 'project' : 'projects' }}</span></div>
                <div class="card px-4 py-3" :class="due.length && 'shadow-[inset_0_0_0_1px_rgb(251_191_36/.3)]'"><span class="display text-xl">{{ due.length ? dueTotal : money(0, usePage().props.site.currency) }}</span> <span class="text-muted">due</span></div>
            </div>
        </div>

        <!-- Things that need the client -->
        <div v-if="due.length || testimonials.length" data-in class="mt-8 grid gap-3 md:grid-cols-2">
            <a v-for="i in due" :key="i.reference" :href="i.url" class="group flex items-center gap-4 rounded-2xl bg-amber-400/[0.07] p-4 shadow-[inset_0_0_0_1px_rgb(251_191_36/.2)] transition-colors hover:bg-amber-400/10">
                <span class="grid size-10 place-items-center rounded-xl bg-amber-400/15 text-amber-300"><Receipt :size="18" /></span>
                <span class="min-w-0 flex-1"><span class="block font-medium">Invoice {{ i.reference }} · {{ money(i.total, i.currency) }}</span><span class="block text-sm text-text-2">{{ i.overdue ? 'Overdue' : i.due_at ? `Due ${date(i.due_at)}` : 'Ready to pay' }} — pay securely with PayPal</span></span>
                <ArrowUpRight :size="18" class="text-amber-300 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
            </a>
            <a v-for="(t, k) in testimonials" :key="k" :href="t.url" class="group flex items-center gap-4 rounded-2xl bg-white/[0.04] p-4 shadow-[inset_0_0_0_1px_var(--line)] transition-colors hover:bg-white/[0.06]">
                <span class="grid size-10 place-items-center rounded-xl text-white" style="background: var(--gradient)"><Star :size="18" /></span>
                <span class="min-w-0 flex-1"><span class="block font-medium">How did we do?</span><span class="block text-sm text-text-2">Leave a short review{{ t.project ? ` of ${t.project}` : '' }} — it takes a minute.</span></span>
                <ArrowUpRight :size="18" class="text-muted transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
            </a>
        </div>

        <!-- Projects -->
        <section data-in class="mt-10">
            <h2 class="title flex items-center gap-2 text-lg"><FolderKanban :size="18" class="text-accent-hot" /> Projects</h2>
            <div v-if="projects.length" class="mt-4 grid gap-4 md:grid-cols-2">
                <Link v-for="p in projects" :key="p.id" :href="`/portal/projects/${p.id}`" v-spotlight class="group card flex flex-col p-6 transition-colors hover:bg-white/[0.05]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="title truncate text-xl">{{ p.title }}</h3>
                            <p v-if="p.summary" class="mt-1.5 line-clamp-2 text-sm text-text-2">{{ p.summary }}</p>
                        </div>
                        <StatusBadge :status="p.status" />
                    </div>
                    <ProgressBar class="mt-6" :value="p.progress" :label="p.milestones_total ? `${p.milestones_done} of ${p.milestones_total} milestones` : 'Progress'" />
                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted">
                        <span v-if="p.next_milestone" class="flex items-center gap-1.5"><Flag :size="14" /> Next: <span class="text-text-2">{{ p.next_milestone.title }}</span></span>
                        <span v-if="p.due_at && p.status !== 'completed'" class="flex items-center gap-1.5"><CalendarClock :size="14" /> Due {{ date(p.due_at) }}</span>
                        <span class="ml-auto flex items-center gap-1 text-text-2 group-hover:text-text">Open <ArrowUpRight :size="14" /></span>
                    </div>
                </Link>
            </div>
            <div v-else class="card-solid mt-4 p-8 text-center text-text-2">No projects yet — they'll appear here as soon as work starts.</div>
        </section>

        <div class="mt-10 grid gap-4 lg:grid-cols-5">
            <!-- Latest updates -->
            <section data-in class="card-solid p-6 lg:col-span-3">
                <h2 class="title flex items-center gap-2 text-lg"><MessageSquareQuote :size="18" class="text-accent-hot" /> Latest updates</h2>
                <ul v-if="updates.length" class="mt-4 divide-y divide-line">
                    <li v-for="u in updates" :key="u.id">
                        <Link :href="`/portal/projects/${u.project.id}`" class="-mx-2 block rounded-xl px-2 py-3.5 hover:bg-white/[0.03]">
                            <span class="flex items-baseline justify-between gap-3 text-xs text-muted"><span>{{ u.author.is_studio ? 'SiedeStudios' : u.author.name }} · {{ u.project.title }}</span><span>{{ ago(u.created_at) }}</span></span>
                            <span class="mt-1 line-clamp-2 block text-[0.9375rem] text-text-2">{{ u.body }}</span>
                        </Link>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-muted">Progress updates and messages will show here.</p>
            </section>

            <!-- Invoices -->
            <section data-in class="card-solid p-6 lg:col-span-2">
                <h2 class="title flex items-center gap-2 text-lg"><Receipt :size="18" class="text-accent-hot" /> Invoices</h2>
                <InvoiceList v-if="invoices.length" class="mt-3" :invoices="invoices" />
                <p v-else class="mt-4 text-sm text-muted">No invoices yet.</p>
            </section>
        </div>

        <!-- Files -->
        <section data-in class="card-solid mt-4 grid gap-6 p-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <h2 class="title flex items-center gap-2 text-lg"><Paperclip :size="18" class="text-accent-hot" /> Recent files</h2>
                <FileList v-if="files.length" class="mt-2" :files="files" show-project :delete-url="(f) => `/portal/files/${f.id}`" :can-delete="(f) => f.user_id === user.id" />
                <p v-else class="mt-4 text-sm text-muted">Nothing shared yet.</p>
            </div>
            <div class="lg:col-span-2">
                <h3 class="text-sm font-medium">Send us a file</h3>
                <p class="mb-3 mt-1 text-sm text-muted">Logos, content, screenshots, configs — anything we need.</p>
                <FileUpload url="/portal/files" :projects="projects.map((p) => ({ id: p.id, title: p.title }))" :upload="upload" />
            </div>
        </section>
    </div>
</template>
