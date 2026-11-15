<script setup>
import { ref } from 'vue';
import { Copy, Check, ExternalLink } from 'lucide-vue-next';

const props = defineProps({ value: String, open: { type: Boolean, default: true } });
const copied = ref(false);
const copy = async () => {
    await navigator.clipboard.writeText(props.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1600);
};
</script>

<template>
    <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] p-1.5 pl-3.5 shadow-[inset_0_0_0_1px_var(--line-2)]">
        <span class="min-w-0 flex-1 truncate font-mono text-sm text-text-2">{{ value }}</span>
        <a v-if="open" :href="value" target="_blank" rel="noopener" class="btn btn-glass btn-sm btn-icon h-8 w-8" aria-label="Open"><ExternalLink :size="14" /></a>
        <button type="button" class="btn btn-sm h-8" :class="copied ? 'btn-glass text-emerald-300' : 'btn-light'" @click="copy">
            <component :is="copied ? Check : Copy" :size="14" /> {{ copied ? 'Copied' : 'Copy' }}
        </button>
    </div>
</template>
