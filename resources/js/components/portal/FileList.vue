<script setup>
import { FileText, FileImage, FileArchive, FileCode2, File, Download, Trash2 } from 'lucide-vue-next';
import { confirmVisit } from '@/composables/useConfirm';
import { ago, bytes } from '@/lib/format';

const props = defineProps({
    files: Array,
    deleteUrl: { type: Function, default: null },
    canDelete: { type: Function, default: () => true },
    showProject: Boolean,
});

const icon = (f) => {
    const n = f.name.toLowerCase();
    if (/\.(png|jpe?g|gif|webp|svg|psd|fig|ai)$/.test(n)) return FileImage;
    if (/\.(zip|rar|7z)$/.test(n)) return FileArchive;
    if (/\.(lua|json|md|csv|txt)$/.test(n)) return FileCode2;
    if (/\.(pdf|docx?|xlsx?|pptx?)$/.test(n)) return FileText;
    return File;
};
</script>

<template>
    <ul class="divide-y divide-line">
        <li v-for="f in files" :key="f.id" class="flex items-center gap-3 py-3">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/[0.05] text-muted"><component :is="icon(f)" :size="18" /></span>
            <div class="min-w-0 flex-1">
                <a :href="f.url" class="block truncate text-sm font-medium hover:text-accent-hot">{{ f.name }}</a>
                <p class="truncate text-xs text-muted">{{ bytes(f.size) }} · {{ f.from_studio ? 'From us' : f.uploaded_by || 'You' }} · {{ ago(f.created_at) }}<template v-if="showProject && f.project"> · {{ f.project }}</template></p>
            </div>
            <a :href="f.url" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" :aria-label="`Download ${f.name}`"><Download :size="16" /></a>
            <button v-if="deleteUrl && canDelete(f)" type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" :aria-label="`Delete ${f.name}`" @click="confirmVisit(`Delete ${f.name}?`, deleteUrl(f))"><Trash2 :size="16" /></button>
        </li>
    </ul>
</template>
