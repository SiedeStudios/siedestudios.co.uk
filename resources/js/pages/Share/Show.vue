<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Copy, Check, FileText, Download, Flame, WrapText } from 'lucide-vue-next';
import { highlight } from '@/lib/highlight';
import { ago, bytes } from '@/lib/format';

const props = defineProps({ share: Object });

const copied = ref(false);
const wrap = ref(false);
const lines = computed(() => highlight(props.share.content, props.share.language).split('\n'));

const extensions = { lua: 'lua', php: 'php', javascript: 'js', typescript: 'ts', json: 'json', html: 'html', css: 'css', scss: 'scss', sql: 'sql', bash: 'sh', python: 'py', yaml: 'yml', ini: 'ini', markdown: 'md', csharp: 'cs', cpp: 'cpp', xml: 'xml', diff: 'diff' };
const filename = computed(() => {
    const t = props.share.title;
    if (t && /\.[a-z0-9]+$/i.test(t)) return t.split('/').pop();
    return `${props.share.slug}.${extensions[props.share.language] ?? 'txt'}`;
});

const copy = async () => {
    await navigator.clipboard.writeText(props.share.content);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1800);
};
const download = () => {
    const url = URL.createObjectURL(new Blob([props.share.content], { type: 'text/plain' }));
    const a = Object.assign(document.createElement('a'), { href: url, download: filename.value });
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Head :title="share.title || 'Shared snippet'">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="wrap flex flex-1 flex-col py-6">
        <div v-if="share.burned" class="mb-4 flex items-center gap-3 rounded-2xl bg-accent/10 p-4 text-sm text-accent-hot shadow-[inset_0_0_0_1px_color-mix(in_oklab,var(--accent)_30%,transparent)]">
            <Flame :size="16" /> This snippet was set to burn after reading and has now been deleted. Copy anything you need before leaving.
        </div>

        <div class="flex flex-1 flex-col overflow-hidden rounded-[1.5rem] bg-surface shadow-[inset_0_0_0_1px_var(--line-2)]">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line px-5 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white/5"><FileText :size="16" /></span>
                    <div class="min-w-0">
                        <h1 class="truncate font-display font-medium tracking-tight">{{ share.title || filename }}</h1>
                        <p class="mono">{{ share.language }} · {{ share.lines }} lines · {{ bytes(share.size) }} · {{ ago(share.created_at) }}<template v-if="share.expires_at"> · expires {{ ago(share.expires_at) }}</template></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-glass btn-sm" :class="wrap ? 'bg-white/10' : ''" :aria-pressed="wrap" @click="wrap = !wrap"><WrapText :size="15" /> Wrap</button>
                    <a v-if="!share.burned" :href="`/s/${share.slug}/raw`" class="btn btn-glass btn-sm" target="_blank" rel="noopener">Raw</a>
                    <button type="button" class="btn btn-glass btn-sm" @click="download"><Download :size="15" /> Download</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="copy">
                        <component :is="copied ? Check : Copy" :size="15" /> {{ copied ? 'Copied' : 'Copy' }}
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-auto bg-bg-2 py-4 font-mono text-[0.8125rem] leading-6" data-lenis-prevent>
                <table class="min-w-full border-collapse">
                    <tbody>
                        <tr v-for="(line, i) in lines" :key="i" class="group hover:bg-white/[0.025]">
                            <td class="w-px select-none whitespace-nowrap px-5 text-right align-top text-faint group-hover:text-muted">{{ i + 1 }}</td>
                            <td class="hljs pr-6 align-top" :class="wrap ? 'whitespace-pre-wrap break-all' : 'whitespace-pre'" v-html="line || ' '"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="mt-4 text-center text-xs text-faint">{{ share.views }} view{{ share.views === 1 ? '' : 's' }} · Shared privately via SiedeStudios</p>
    </div>
</template>
