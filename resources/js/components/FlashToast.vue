<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Check, X, AlertCircle } from 'lucide-vue-next';

const page = usePage();
const toasts = ref([]);
let id = 0;

const push = (type, text) => {
    const toast = { id: ++id, type, text };
    toasts.value.push(toast);
    setTimeout(() => dismiss(toast.id), 5200);
};
const dismiss = (tid) => (toasts.value = toasts.value.filter((t) => t.id !== tid));

const flash = computed(() => page.props.flash ?? {});
watch(flash, (f) => {
    if (f.success) push('success', f.success);
    if (f.error) push('error', f.error);
}, { immediate: true, deep: true });
</script>

<template>
    <div class="pointer-events-none fixed bottom-5 right-5 z-[85] flex w-[min(24rem,calc(100vw-2.5rem))] flex-col gap-3" aria-live="polite">
        <TransitionGroup
            enter-active-class="transition duration-500 ease-out-expo"
            enter-from-class="translate-y-4 scale-95 opacity-0 blur-sm"
            leave-active-class="transition duration-300"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div v-for="t in toasts" :key="t.id" class="pointer-events-auto flex items-start gap-3 rounded-2xl bg-surface-2/90 p-4 pr-11 shadow-[inset_0_0_0_1px_var(--line-2),0_20px_50px_-15px_rgba(0,0,0,.7)] backdrop-blur-xl">
                <span class="grid size-6 shrink-0 place-items-center rounded-full" :class="t.type === 'error' ? 'bg-accent/20 text-accent-hot' : 'bg-emerald-400/15 text-emerald-300'">
                    <component :is="t.type === 'error' ? AlertCircle : Check" :size="14" :stroke-width="2.5" />
                </span>
                <p class="pt-0.5 text-sm leading-snug text-text">{{ t.text }}</p>
                <button type="button" class="absolute right-3 top-3.5 text-muted hover:text-text" aria-label="Dismiss" @click="dismiss(t.id)"><X :size="16" /></button>
            </div>
        </TransitionGroup>
    </div>
</template>
