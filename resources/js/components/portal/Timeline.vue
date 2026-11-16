<script setup>
import { useForm } from '@inertiajs/vue3';
import { Send, Loader2, Trash2 } from 'lucide-vue-next';
import LogoMark from '@/components/LogoMark.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago, date } from '@/lib/format';

const props = defineProps({
    updates: Array,
    postUrl: String,
    deleteUrl: { type: Function, default: null },
    placeholder: { type: String, default: 'Write a message…' },
    button: { type: String, default: 'Send' },
});

const form = useForm({ body: '' });
const submit = () => form.post(props.postUrl, { preserveScroll: true, onSuccess: () => form.reset() });
const full = (d) => date(d, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <div>
        <form v-if="postUrl" class="rounded-2xl bg-bg p-3 shadow-[inset_0_0_0_1px_var(--line)] focus-within:shadow-[inset_0_0_0_1px_var(--line-2)]" @submit.prevent="submit">
            <textarea v-model="form.body" rows="3" class="w-full resize-none bg-transparent p-1 text-[0.9375rem] outline-none placeholder:text-faint" :placeholder="placeholder" aria-label="Message" @keydown.meta.enter="submit" @keydown.ctrl.enter="submit"></textarea>
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-faint">{{ form.errors.body || 'Ctrl + Enter to send' }}</span>
                <button type="submit" class="btn btn-light btn-sm" :disabled="form.processing || !form.body.trim()"><Loader2 v-if="form.processing" :size="14" class="animate-spin" /><Send v-else :size="14" /> {{ button }}</button>
            </div>
        </form>

        <ol class="relative mt-6 space-y-6 before:absolute before:bottom-2 before:left-[1.1rem] before:top-2 before:w-px before:bg-line">
            <li v-for="u in updates" :key="u.id" class="relative flex gap-4">
                <span v-if="u.author.is_studio" class="relative grid size-9 shrink-0 place-items-center rounded-full bg-surface-2 shadow-[0_0_0_4px_var(--surface)]"><LogoMark :size="18" /></span>
                <img v-else-if="u.author.avatar" :src="u.author.avatar" alt="" class="relative size-9 shrink-0 rounded-full shadow-[0_0_0_4px_var(--surface)]" />
                <span v-else class="relative grid size-9 shrink-0 place-items-center rounded-full bg-surface-2 text-sm shadow-[0_0_0_4px_var(--surface)]">{{ u.author.name?.[0] }}</span>
                <div class="min-w-0 flex-1 pt-1">
                    <div class="flex flex-wrap items-baseline gap-x-2">
                        <span class="text-sm font-medium">{{ u.author.is_studio ? 'SiedeStudios' : u.author.name }}</span>
                        <span class="text-xs text-muted" :title="full(u.created_at)">{{ ago(u.created_at) }}</span>
                        <button v-if="deleteUrl" type="button" class="ml-auto text-faint hover:text-accent-hot" aria-label="Delete update" @click="confirmVisit('Delete this update?', deleteUrl(u))"><Trash2 :size="14" /></button>
                    </div>
                    <p class="mt-1.5 whitespace-pre-line break-words text-[0.9375rem] leading-relaxed text-text-2">{{ u.body }}</p>
                    <slot name="meta" :update="u" />
                </div>
            </li>
        </ol>
        <p v-if="!updates.length" class="mt-6 text-sm text-muted">No updates yet.</p>
    </div>
</template>
