<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { UploadCloud, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    url: String,
    projects: { type: Array, default: () => [] },
    projectId: { type: Number, default: null },
    upload: Object,
});

const form = useForm({ file: null, project_id: props.projectId });
const over = ref(false);
const input = ref(null);

const send = (file) => {
    if (!file) return;
    form.file = file;
    form.post(props.url, { preserveScroll: true, forceFormData: true, onFinish: () => { form.file = null; if (input.value) input.value.value = ''; } });
};
const drop = (e) => {
    over.value = false;
    send(e.dataTransfer.files?.[0]);
};
</script>

<template>
    <div>
        <select v-if="projects.length && !projectId" v-model="form.project_id" class="field mb-3 h-10 text-sm" aria-label="Attach to project">
            <option :value="null">Not tied to a project</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option>
        </select>
        <label
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border border-dashed p-6 text-center text-sm transition-colors"
            :class="over ? 'border-accent bg-accent/5' : 'border-line-2 hover:border-white/25 hover:bg-white/[0.02]'"
            @dragover.prevent="over = true"
            @dragleave.prevent="over = false"
            @drop.prevent="drop"
        >
            <Loader2 v-if="form.processing" :size="22" class="animate-spin text-accent-hot" />
            <UploadCloud v-else :size="22" class="text-muted" />
            <span v-if="form.processing">Uploading… {{ form.progress?.percentage ?? 0 }}%</span>
            <span v-else><span class="font-medium text-text">Choose a file</span> <span class="text-muted">or drop it here</span></span>
            <span class="text-xs text-faint">Up to {{ upload?.max_mb ?? 50 }} MB · documents, images, archives</span>
            <input ref="input" type="file" class="sr-only" :accept="upload ? upload.mimes.split(',').map((m) => `.${m}`).join(',') : undefined" @change="send($event.target.files?.[0])" />
        </label>
        <p v-if="form.errors.file || form.errors.project_id" class="field-error mt-2">{{ form.errors.file || form.errors.project_id }}</p>
    </div>
</template>
