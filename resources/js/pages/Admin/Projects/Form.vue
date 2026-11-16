<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Save, Loader2, Eye, Trash2, Upload, GripVertical, Globe, Github, ShoppingBag, Play } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import Toggle from '@/components/admin/Toggle.vue';
import ImageInput from '@/components/admin/ImageInput.vue';
import BrandIcon from '@/components/BrandIcon.vue';
import { useDragSort } from '@/composables/useDragSort';
import { confirmVisit } from '@/composables/useConfirm';

const props = defineProps({ project: Object, categories: Array });
const editing = computed(() => !!props.project);

const form = useForm({
    title: props.project?.title ?? '',
    slug: props.project?.slug ?? '',
    project_category_id: props.project?.project_category_id ?? '',
    summary: props.project?.summary ?? '',
    body: props.project?.body ?? '',
    accent_mode: props.project?.accent_mode ?? 'auto',
    accent_color: props.project?.accent_color ?? props.project?.detected_accent ?? '#DC143C',
    results: [0, 1, 2, 3].map((i) => ({ value: props.project?.results?.[i]?.value ?? '', label: props.project?.results?.[i]?.label ?? '' })),
    chapter_images: Object.assign([], props.project?.chapter_images ?? []).map((id) => id ?? null),
    client: props.project?.client ?? '',
    role: props.project?.role ?? '',
    year: props.project?.year ?? String(new Date().getFullYear()),
    tags: (props.project?.tags ?? []).join(', '),
    site_url: props.project?.site_url ?? '',
    discord_url: props.project?.discord_url ?? '',
    github_url: props.project?.github_url ?? '',
    store_url: props.project?.store_url ?? '',
    video_url: props.project?.video_url ?? '',
    is_featured: props.project?.is_featured ?? false,
    status: props.project?.status ?? 'draft',
    cover: null,
    remove_cover: false,
});

// "## " headings become chapters of the scroll story (mirrors App\Support\CaseStudy).
const chapters = computed(() => {
    let fence = false;
    return form.body.split(/\r?\n/).reduce((list, line) => {
        if (/^\s*(```|~~~)/.test(line)) fence = !fence;
        const m = !fence && line.match(/^##\s+(.+?)\s*#*\s*$/);
        if (m) list.push(m[1].trim());
        return list;
    }, []);
});
const chapterImage = (i) => images.value.find((img) => img.id === form.chapter_images[i]);

const submit = () => {
    const options = { preserveScroll: true, forceFormData: true, onSuccess: () => form.reset('cover', 'remove_cover') };
    if (editing.value) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(`/admin/projects/${props.project.id}`, options);
    } else {
        form.post('/admin/projects', options);
    }
};

/* Screenshots */
const images = ref([...(props.project?.images ?? [])]);
watch(() => props.project?.images, (v) => (images.value = [...(v ?? [])]));
const uploading = ref(false);
const uploadErrors = ref([]);
const fileInput = ref(null);
const dropActive = ref(false);

const upload = (files) => {
    const list = Array.from(files ?? []).filter((f) => f.type.startsWith('image/'));
    if (!list.length) return;
    const tooBig = list.filter((f) => f.size > 8 * 1024 * 1024).map((f) => `${f.name} is larger than 8 MB.`);
    uploadErrors.value = tooBig;
    if (tooBig.length) return;
    uploading.value = true;
    router.post(`/admin/projects/${props.project.id}/images`, { images: list }, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => (uploadErrors.value = [...new Set(Object.values(errors))]),
        onFinish: () => (uploading.value = false),
    });
};
const saveCaption = (img) => router.patch(`/admin/project-images/${img.id}`, { caption: img.caption }, { preserveScroll: true, preserveState: true });
const sort = useDragSort(images, (list) => router.post(`/admin/projects/${props.project.id}/images/reorder`, { ids: list.map((i) => i.id) }, { preserveScroll: true, preserveState: true }));

const links = [
    ['site_url', 'Live site', Globe],
    ['discord_url', 'Discord', 'discord'],
    ['github_url', 'GitHub / source', Github],
    ['store_url', 'Store page', ShoppingBag],
    ['video_url', 'Video', Play],
];
</script>

<template>
    <Head :title="editing ? `Edit ${project.title}` : 'New project'" />
    <PageHeader :title="editing ? project.title : 'New project'" :description="editing ? 'Update details, links and screenshots.' : 'Add a project to your portfolio.'" back="/admin/projects">
        <a v-if="editing" :href="`/projects/${form.slug}`" target="_blank" class="btn btn-glass btn-sm"><Eye :size="15" /> Preview</a>
        <button v-if="editing" type="button" class="btn btn-danger btn-sm" @click="confirmVisit('Delete this project and its screenshots?', `/admin/projects/${project.id}`)"><Trash2 :size="15" /></button>
    </PageHeader>

    <form class="grid gap-4 xl:grid-cols-3" @submit.prevent="submit">
        <div class="space-y-4 xl:col-span-2">
            <section class="card-solid space-y-5 p-6">
                <div class="grid gap-5 sm:grid-cols-2">
                    <Field label="Title" for="title" :error="form.errors.title">
                        <input id="title" v-model="form.title" class="field" required />
                    </Field>
                    <Field label="URL slug" for="slug" :error="form.errors.slug" help="Leave blank to generate from the title." optional>
                        <input id="slug" v-model="form.slug" class="field font-mono text-sm" placeholder="my-project" />
                    </Field>
                </div>
                <Field label="Summary" for="summary" :error="form.errors.summary" help="One or two sentences — shown on cards and at the top of the project page.">
                    <textarea id="summary" v-model="form.summary" rows="2" class="field" maxlength="500" required></textarea>
                </Field>
                <Field label="Case study" for="body" :error="form.errors.body" help="Markdown supported: ## headings, **bold**, - lists, [links](https://…)." optional>
                    <textarea id="body" v-model="form.body" rows="12" class="field font-mono text-sm leading-relaxed"></textarea>
                </Field>
            </section>

            <section class="card-solid p-6">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 class="title text-lg">Scroll story</h2>
                    <span class="text-sm" :class="chapters.length >= 2 ? 'text-emerald-300' : 'text-muted'">{{ chapters.length >= 2 ? `${chapters.length} chapters — story layout on` : 'Add two or more ## headings to turn this on' }}</span>
                </div>
                <p class="mt-1 text-sm text-muted">Each <code class="font-mono text-xs">## heading</code> in the case study becomes a chapter. As visitors scroll, the screenshot beside it changes to the one you pick here (or the gallery in order).</p>

                <ol v-if="chapters.length" class="mt-5 space-y-2">
                    <li v-for="(title, i) in chapters" :key="i" class="flex items-center gap-3 rounded-2xl bg-white/[0.03] p-2.5 shadow-[inset_0_0_0_1px_var(--line)]">
                        <span class="w-7 text-center font-mono text-xs text-muted">{{ String(i + 1).padStart(2, '0') }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ title }}</span>
                        <img v-if="chapterImage(i)" :src="chapterImage(i).url" alt="" class="h-9 w-14 rounded-md object-cover object-top" />
                        <select v-model="form.chapter_images[i]" class="field h-9 w-48 text-sm" :aria-label="`Screenshot for ${title}`" :disabled="!images.length">
                            <option :value="null">{{ images.length ? 'Auto (gallery order)' : 'Upload screenshots first' }}</option>
                            <option v-for="(img, k) in images" :key="img.id" :value="img.id">Screenshot {{ k + 1 }}{{ img.caption ? ` — ${img.caption}` : '' }}</option>
                        </select>
                    </li>
                </ol>

                <h3 class="mt-7 text-sm font-medium">Results</h3>
                <p class="mt-1 text-sm text-muted">Up to four headline numbers, counted up as they scroll in. E.g. <span class="font-mono text-xs">38</span> · addons built on it.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div v-for="(r, i) in form.results" :key="i" class="flex gap-2">
                        <input v-model="r.value" class="field w-24 text-center font-medium" maxlength="16" placeholder="38" :aria-label="`Result ${i + 1} number`" />
                        <input v-model="r.label" class="field flex-1" maxlength="60" placeholder="addons built on it" :aria-label="`Result ${i + 1} label`" />
                    </div>
                </div>
                <p v-if="Object.keys(form.errors).some((k) => k.startsWith('results'))" class="field-error mt-2">Each result needs a short label.</p>
            </section>

            <section class="card-solid p-6">
                <h2 class="title text-lg">Details</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-3">
                    <Field label="Client" for="client" :error="form.errors.client" optional><input id="client" v-model="form.client" class="field" /></Field>
                    <Field label="Your role" for="role" :error="form.errors.role" optional><input id="role" v-model="form.role" class="field" placeholder="Design & development" /></Field>
                    <Field label="Year" for="year" :error="form.errors.year" optional><input id="year" v-model="form.year" class="field" /></Field>
                </div>
                <Field class="mt-5" label="Tags / stack" for="tags" :error="form.errors.tags" help="Comma separated, e.g. Laravel, Vue, Lua">
                    <input id="tags" v-model="form.tags" class="field" />
                </Field>
                <div class="mt-5">
                    <div class="field-label">Brand colour</div>
                    <p class="mb-3 text-sm text-muted">The case study page tints the whole site in this colour while it's open.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button v-for="[mode, label] in [['auto', 'Auto from cover'], ['custom', 'Custom'], ['none', 'Off']]" :key="mode" type="button" class="flex h-10 items-center gap-2 rounded-full px-4 text-sm transition-colors" :class="form.accent_mode === mode ? 'bg-white/10 text-text shadow-[inset_0_0_0_1px_var(--line-2)]' : 'text-muted hover:text-text'" @click="form.accent_mode = mode">
                            <span v-if="mode === 'auto'" class="size-3.5 rounded-full shadow-[inset_0_0_0_1px_rgb(255_255_255/.2)]" :style="{ background: project?.detected_accent ?? 'transparent' }"></span>
                            {{ label }}
                        </button>
                        <span v-if="form.accent_mode === 'custom'" class="ml-2 flex items-center gap-2">
                            <input v-model="form.accent_color" type="color" class="h-10 w-12 cursor-pointer rounded-xl border border-line-2 bg-transparent p-1" aria-label="Brand colour" />
                            <input v-model="form.accent_color" class="field h-10 w-28 font-mono text-sm uppercase" maxlength="7" aria-label="Brand colour hex" />
                        </span>
                    </div>
                    <p v-if="form.accent_mode === 'auto'" class="mt-2 text-xs text-muted">{{ project?.detected_accent ? `Detected ${project.detected_accent} from the cover — updates when the cover changes.` : (editing ? 'No strong colour in the cover, so the page keeps the site accent.' : 'Detected once you add a cover.') }}</p>
                    <p v-if="form.accent_mode === 'custom'" class="mt-2 text-xs text-muted">Very dark or washed-out colours are adjusted so buttons and links stay readable.</p>
                    <p v-if="form.errors.accent_color" class="field-error">{{ form.errors.accent_color }}</p>
                </div>
            </section>

            <section class="card-solid p-6">
                <h2 class="title text-lg">Links</h2>
                <p class="mt-1 text-sm text-muted">Shown as buttons on the project page. Leave empty to hide.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <Field v-for="[key, label, icon] in links" :key="key" :label="label" :for="key" :error="form.errors[key]">
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted">
                                <BrandIcon v-if="icon === 'discord'" name="discord" :size="15" />
                                <component :is="icon" v-else :size="15" />
                            </span>
                            <input :id="key" v-model="form[key]" type="url" class="field pl-10" placeholder="https://" />
                        </div>
                    </Field>
                </div>
            </section>

            <!-- Screenshots -->
            <section class="card-solid p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="title text-lg">Screenshots</h2>
                        <p class="mt-1 text-sm text-muted">Drag to reorder. The first image becomes the cover if none is set.</p>
                    </div>
                    <span class="text-sm text-muted">{{ images.length }}</span>
                </div>
                <div v-if="!editing" class="mt-5 rounded-2xl bg-white/[0.03] p-6 text-center text-sm text-muted shadow-[inset_0_0_0_1px_var(--line)]">Save the project first, then add screenshots here.</div>
                <template v-else>
                    <div
                        class="mt-5 flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed p-8 text-center text-sm transition-colors"
                        :class="dropActive ? 'border-accent bg-accent/5' : 'border-line-2'"
                        @dragover.prevent="dropActive = true"
                        @dragleave.prevent="dropActive = false"
                        @drop.prevent="dropActive = false; upload($event.dataTransfer.files)"
                    >
                        <Loader2 v-if="uploading" :size="22" class="animate-spin text-accent-hot" />
                        <Upload v-else :size="22" class="text-muted" />
                        <p class="text-text-2">{{ uploading ? 'Uploading…' : 'Drop screenshots here' }}</p>
                        <button type="button" class="btn btn-glass btn-sm mt-1" :disabled="uploading" @click="fileInput.click()">Choose files</button>
                        <p class="text-xs text-muted">PNG, JPG or WebP · up to 8 MB each · 20 at a time</p>
                        <input ref="fileInput" type="file" accept="image/*" multiple class="sr-only" @change="upload($event.target.files); $event.target.value = ''" />
                    </div>
                    <ul v-if="uploadErrors.length" class="mt-3 space-y-1">
                        <li v-for="e in uploadErrors" :key="e" class="field-error">{{ e }}</li>
                    </ul>
                    <ul v-if="images.length" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <li
                            v-for="(img, i) in images"
                            :key="img.id"
                            draggable="true"
                            class="group overflow-hidden rounded-2xl bg-white/[0.03] shadow-[inset_0_0_0_1px_var(--line)] transition-opacity"
                            :class="[sort.dragging.value === i ? 'opacity-40' : '', sort.over.value === i ? 'ring-2 ring-accent' : '']"
                            @dragstart="sort.start(i, $event)"
                            @dragenter.prevent="sort.enter(i)"
                            @dragover.prevent
                            @drop.prevent="sort.drop(i)"
                            @dragend="sort.end"
                        >
                            <div class="relative aspect-[16/10] bg-surface-2">
                                <img :src="img.url" alt="" class="h-full w-full object-cover object-top" />
                                <span class="absolute left-2 top-2 grid size-7 cursor-grab place-items-center rounded-lg bg-bg/70 text-muted backdrop-blur"><GripVertical :size="14" /></span>
                                <span v-if="i === 0" class="chip absolute bottom-2 left-2 bg-bg/70 backdrop-blur">First</span>
                                <button type="button" class="absolute right-2 top-2 grid size-7 place-items-center rounded-lg bg-bg/70 text-muted opacity-0 backdrop-blur transition-opacity hover:text-accent-hot group-hover:opacity-100" aria-label="Delete screenshot" @click="confirmVisit('Delete this screenshot?', `/admin/project-images/${img.id}`, 'delete', {}, { preserveState: true })"><Trash2 :size="14" /></button>
                            </div>
                            <input v-model="img.caption" class="w-full bg-transparent px-3 py-2.5 text-sm placeholder:text-faint focus:outline-none" placeholder="Add a caption…" @change="saveCaption(img)" />
                        </li>
                    </ul>
                </template>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="card-solid space-y-5 p-6 xl:sticky xl:top-6">
                <Field label="Status" for="status" :error="form.errors.status">
                    <select id="status" v-model="form.status" class="field">
                        <option value="draft">Draft — hidden</option>
                        <option value="published">Published</option>
                    </select>
                </Field>
                <Field label="Category" for="category" :error="form.errors.project_category_id">
                    <select id="category" v-model="form.project_category_id" class="field">
                        <option value="">Uncategorised</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </Field>
                <Toggle v-model="form.is_featured" label="Featured" description="Shown first on the home page." />
                <Field label="Cover image" :error="form.errors.cover">
                    <ImageInput v-model="form.cover" v-model:remove="form.remove_cover" :current="project?.cover" />
                </Field>
                <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                    <Loader2 v-if="form.processing" :size="16" class="animate-spin" /><Save v-else :size="16" />
                    {{ editing ? 'Save changes' : 'Create project' }}
                </button>
                <p v-if="form.isDirty && editing" class="text-center text-xs text-amber-300">You have unsaved changes</p>
            </section>
        </aside>
    </form>
</template>
