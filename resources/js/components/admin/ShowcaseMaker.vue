<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { Download, ImageUp, Loader2, X, Palette, Wand2 } from 'lucide-vue-next';
import { WINDOWS, brandColour, renderShowcase } from '@/lib/showcase';

/**
 * Builds a cover in the studio's mockup style from three of the project's
 * screenshots, lit in the project's colour. Settings are saved with the
 * project (v-model) so the cover can be re-made later.
 */
const props = defineProps({
    modelValue: { type: Object, default: null },
    images: { type: Array, default: () => [] },
    projectColor: { type: String, default: null },
    defaultLabel: { type: String, default: '' },
    filename: { type: String, default: 'cover' },
    busy: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'use']);

const byId = computed(() => new Map(props.images.map((img) => [img.id, img])));
const saved = props.modelValue;
const settings = reactive({
    slots: WINDOWS.map((win, i) => {
        const s = saved?.slots?.[i];
        const fallback = props.images[i];
        return {
            image: s ? s.image : fallback?.id ?? null,
            label: s ? s.label ?? '' : i === 0 ? '' : fallback?.caption || props.defaultLabel,
            focus: s?.focus ?? 0,
        };
    }),
    color: saved?.color ?? null,
});
const emitSettings = () => emit('update:modelValue', JSON.parse(JSON.stringify(settings)));
// Only report changes, so simply opening the form doesn't mark it as edited.
watch(settings, emitSettings, { deep: true });

const active = ref(0);
const assign = (id) => {
    settings.slots[active.value].image = id;
    // Move on to the next empty window, so three clicks fill all three.
    const next = settings.slots.findIndex((s, i) => i > active.value && !s.image);
    if (next !== -1) active.value = next;
};
const thumb = (slot) => byId.value.get(slot.image);

/* ---------- Colour ---------- */
const detected = ref(null);
const autoColour = computed(() => props.projectColor || detected.value || '#DC143C');
const colour = computed(() => settings.color || autoColour.value);
const autoSource = computed(() => (props.projectColor ? 'Project colour' : detected.value ? 'From the main screenshot' : 'Site colour'));
const custom = computed({
    get: () => !!settings.color,
    set: (on) => (settings.color = on ? autoColour.value : null),
});

/* ---------- Rendering ---------- */
const canvas = ref(null);
const cache = new Map();
const load = (img) => {
    if (!cache.has(img.url)) {
        const el = new Image();
        el.decoding = 'async';
        el.src = img.url;
        cache.set(img.url, el.decode().then(() => el, () => null));
    }
    return cache.get(img.url);
};

let token = 0;
let fontsReady = null;
async function draw() {
    const mine = ++token;
    fontsReady ??= document.fonts?.load('500 20px "Onest Variable"').catch(() => null);
    const els = await Promise.all(settings.slots.map((s) => (byId.value.get(s.image) ? load(byId.value.get(s.image)) : null)));
    await fontsReady;
    if (mine !== token || !canvas.value) return;
    detected.value = els[0] ? brandColour(els[0]) : null;
    renderShowcase(canvas.value, {
        images: els,
        labels: settings.slots.map((s) => s.label),
        focus: settings.slots.map((s) => Number(s.focus) || 0),
        color: colour.value,
    });
}
watch([settings, () => props.images, () => props.projectColor], draw, { deep: true });
onMounted(draw);

const hasMain = computed(() => !!thumb(settings.slots[0]));

/* ---------- Output ---------- */
const toFile = () =>
    new Promise((resolve) => {
        // WebP keeps the upload small; browsers without it fall back to PNG.
        canvas.value.toBlob((blob) => {
            if (blob?.type === 'image/webp') return resolve(new File([blob], `${props.filename}.webp`, { type: blob.type }));
            canvas.value.toBlob((png) => resolve(new File([png], `${props.filename}.png`, { type: 'image/png' })), 'image/png');
        }, 'image/webp', 0.94);
    });

const exporting = ref(false);
async function use() {
    exporting.value = true;
    await draw();
    emitSettings();
    emit('use', await toFile());
    exporting.value = false;
}
async function download() {
    await draw();
    const file = await toFile();
    const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(file), download: file.name });
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
}
</script>

<template>
    <section class="card-solid p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="title flex items-center gap-2 text-lg"><Wand2 :size="18" class="text-accent-hot" /> Cover mockup</h2>
                <p class="mt-1 text-sm text-muted">Turns three screenshots into a cover in the studio style, lit in the project's colour.</p>
            </div>
        </div>

        <div v-if="!images.length" class="mt-5 rounded-2xl bg-white/[0.03] p-6 text-center text-sm text-muted shadow-[inset_0_0_0_1px_var(--line)]">Add screenshots above, then build the cover here.</div>

        <template v-else>
            <div class="mt-5 overflow-hidden rounded-2xl bg-bg shadow-[inset_0_0_0_1px_var(--line-2)]">
                <canvas ref="canvas" class="block aspect-[16/10] h-auto w-full" aria-label="Cover mockup preview"></canvas>
            </div>

            <!-- Windows -->
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <div
                    v-for="(win, i) in WINDOWS"
                    :key="win.key"
                    class="rounded-2xl p-3 transition-shadow"
                    :class="active === i ? 'bg-accent/[0.07] shadow-[inset_0_0_0_1.5px_color-mix(in_oklab,var(--accent)_60%,transparent)]' : 'bg-white/[0.03] shadow-[inset_0_0_0_1px_var(--line)]'"
                >
                    <button type="button" class="flex w-full items-center gap-3 text-left" :aria-pressed="active === i" @click="active = i">
                        <span class="relative aspect-[16/10] w-20 shrink-0 overflow-hidden rounded-lg bg-surface-3">
                            <img v-if="thumb(settings.slots[i])" :src="thumb(settings.slots[i]).url" alt="" class="absolute inset-0 h-full w-full object-cover" :style="{ objectPosition: `50% ${settings.slots[i].focus * 100}%` }" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium">{{ win.name }}</span>
                            <span class="block truncate text-xs text-muted">{{ thumb(settings.slots[i]) ? (active === i ? 'Pick a screenshot below' : 'Click to change') : active === i ? 'Pick a screenshot below' : 'Empty' }}</span>
                        </span>
                        <span v-if="i > 0 && settings.slots[i].image" role="button" tabindex="0" class="grid size-7 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" :aria-label="`Remove ${win.name.toLowerCase()}`" @click.stop="settings.slots[i].image = null" @keydown.enter.stop="settings.slots[i].image = null"><X :size="14" /></span>
                    </button>
                    <input v-model="settings.slots[i].label" class="field mt-3 h-9 text-sm" maxlength="60" :placeholder="i === 0 ? 'Title bar text (optional)' : 'e.g. admin · control room'" :aria-label="`${win.name} title`" />
                    <label class="mt-3 flex items-center gap-3 text-xs text-muted">
                        <span class="w-12">Top</span>
                        <input v-model.number="settings.slots[i].focus" type="range" min="0" max="1" step="0.05" class="flex-1 accent-[var(--accent)]" :aria-label="`${win.name} crop position`" />
                        <span class="w-12 text-right">Bottom</span>
                    </label>
                </div>
            </div>

            <!-- Screenshot picker -->
            <p class="mt-5 text-xs text-muted">Click a screenshot to put it in the <span class="text-text-2">{{ WINDOWS[active].name.toLowerCase() }}</span>.</p>
            <div class="mt-2 flex gap-2 overflow-x-auto pb-1">
                <button
                    v-for="img in images"
                    :key="img.id"
                    type="button"
                    class="relative aspect-[16/10] w-28 shrink-0 overflow-hidden rounded-lg bg-surface-2 transition-shadow"
                    :class="settings.slots[active].image === img.id ? 'shadow-[0_0_0_2px_var(--accent)]' : 'shadow-[inset_0_0_0_1px_var(--line)] hover:shadow-[0_0_0_2px_var(--line-2)]'"
                    :aria-label="`Use ${img.caption || 'screenshot'} for the ${WINDOWS[active].name.toLowerCase()}`"
                    @click="assign(img.id)"
                >
                    <img :src="img.url" alt="" class="absolute inset-0 h-full w-full object-cover object-top" loading="lazy" />
                    <span v-for="(s, k) in settings.slots" v-show="s.image === img.id" :key="k" class="absolute bottom-1 right-1 rounded bg-bg/80 px-1.5 font-mono text-[0.6rem] text-text-2 backdrop-blur">{{ ['Main', 'Left', 'Right'][k] }}</span>
                </button>
            </div>

            <!-- Colour + actions -->
            <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-line pt-5">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="flex h-10 items-center gap-2 rounded-full px-4 text-sm transition-colors" :class="!custom ? 'bg-white/10 text-text shadow-[inset_0_0_0_1px_var(--line-2)]' : 'text-muted hover:text-text'" @click="custom = false">
                        <span class="size-3.5 rounded-full shadow-[inset_0_0_0_1px_rgb(255_255_255/.25)]" :style="{ background: autoColour }"></span>
                        {{ autoSource }}
                    </button>
                    <button type="button" class="flex h-10 items-center gap-2 rounded-full px-4 text-sm transition-colors" :class="custom ? 'bg-white/10 text-text shadow-[inset_0_0_0_1px_var(--line-2)]' : 'text-muted hover:text-text'" @click="custom = true">
                        <Palette :size="15" /> Custom
                    </button>
                    <span v-if="custom" class="flex items-center gap-2">
                        <input v-model="settings.color" type="color" class="h-10 w-12 cursor-pointer rounded-xl border border-line-2 bg-transparent p-1" aria-label="Mockup colour" />
                        <span class="font-mono text-xs uppercase text-muted">{{ settings.color }}</span>
                    </span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-glass btn-sm" :disabled="!hasMain" @click="download"><Download :size="15" /> Download</button>
                    <button type="button" class="btn btn-primary btn-sm" :disabled="!hasMain || exporting || busy" @click="use">
                        <Loader2 v-if="exporting || busy" :size="15" class="animate-spin" /><ImageUp v-else :size="15" /> Use as cover & save
                    </button>
                </div>
            </div>
            <p v-if="!hasMain" class="mt-2 text-right text-xs text-amber-300">Pick a screenshot for the main window first.</p>
        </template>
    </section>
</template>
