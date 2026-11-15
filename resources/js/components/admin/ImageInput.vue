<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue';
import { ImagePlus, Trash2, RotateCcw } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: [File, null], default: null },
    current: String,
    remove: Boolean,
    accept: { type: String, default: 'image/*' },
    aspect: { type: String, default: 'aspect-[16/10]' },
    fit: { type: String, default: 'object-cover' },
});
const emit = defineEmits(['update:modelValue', 'update:remove']);
const input = ref(null);
const objectUrl = ref(null);
const dragging = ref(false);

watch(() => props.modelValue, (file) => {
    if (objectUrl.value) URL.revokeObjectURL(objectUrl.value);
    objectUrl.value = file ? URL.createObjectURL(file) : null;
});
onBeforeUnmount(() => objectUrl.value && URL.revokeObjectURL(objectUrl.value));

const preview = computed(() => objectUrl.value || (props.remove ? null : props.current));

const pick = (file) => {
    if (!file) return;
    emit('update:modelValue', file);
    emit('update:remove', false);
};
const clear = () => {
    emit('update:modelValue', null);
    if (props.current) emit('update:remove', true);
};
const drop = (e) => {
    dragging.value = false;
    pick(e.dataTransfer.files?.[0]);
};
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-2xl bg-white/[0.03] shadow-[inset_0_0_0_1px_var(--line-2)] transition-shadow"
        :class="[aspect, dragging ? 'shadow-[inset_0_0_0_2px_var(--accent)]' : '']"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="drop"
    >
        <img v-if="preview" :src="preview" alt="" class="absolute inset-0 h-full w-full" :class="fit" />
        <button v-else type="button" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-sm text-muted transition-colors hover:text-text" @click="input.click()">
            <ImagePlus :size="22" :stroke-width="1.5" />
            <span>Drop an image or <span class="text-text underline decoration-accent underline-offset-2">browse</span></span>
        </button>
        <div v-if="preview" class="absolute inset-x-2 bottom-2 flex justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100">
            <button type="button" class="btn btn-glass btn-sm bg-bg/70" @click="input.click()">Replace</button>
            <button type="button" class="btn btn-danger btn-sm bg-bg/70" aria-label="Remove image" @click="clear"><Trash2 :size="14" /></button>
        </div>
        <button v-if="remove && current" type="button" class="btn btn-glass btn-sm absolute bottom-2 right-2 bg-bg/70" @click="emit('update:remove', false)"><RotateCcw :size="14" /> Undo remove</button>
        <input ref="input" type="file" :accept="accept" class="sr-only" tabindex="-1" @change="pick($event.target.files?.[0]); $event.target.value = ''" />
    </div>
</template>
