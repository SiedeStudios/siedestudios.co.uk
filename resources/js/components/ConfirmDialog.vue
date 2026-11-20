<script setup>
import { nextTick, ref, watch } from 'vue';
import { AlertTriangle, HelpCircle, Loader2, Trash2 } from 'lucide-vue-next';
import { dialog } from '@/composables/useConfirm';
import { getLenis } from '@/lib/motion';

const panel = ref(null);
const cancelBtn = ref(null);
const confirmBtn = ref(null);
let returnFocus = null;

function close(result) {
    if (!dialog.open) return;
    dialog.open = false;
    dialog.busy = false;
    dialog.resolve?.(result);
    dialog.resolve = null;
    dialog.action = null;
}

async function accept() {
    if (dialog.busy) return;
    if (!dialog.action) return close(true);
    dialog.busy = true;
    try {
        await dialog.action();
    } finally {
        close(true);
    }
}

const cancel = () => !dialog.busy && close(false);

function onKey(e) {
    if (e.key === 'Escape') {
        e.preventDefault();
        cancel();
    }
    // Keep Tab inside the dialog.
    if (e.key === 'Tab') {
        const items = [cancelBtn.value, confirmBtn.value].filter(Boolean);
        const i = items.indexOf(document.activeElement);
        e.preventDefault();
        items[(i + (e.shiftKey ? -1 : 1) + items.length) % items.length]?.focus();
    }
}

watch(
    () => dialog.open,
    async (open) => {
        if (open) {
            returnFocus = document.activeElement;
            getLenis()?.stop();
            window.addEventListener('keydown', onKey);
            await nextTick();
            // Destructive actions start on Cancel so a stray Enter can't delete anything.
            (dialog.danger ? cancelBtn : confirmBtn).value?.focus();
        } else {
            getLenis()?.start();
            window.removeEventListener('keydown', onKey);
            returnFocus?.focus?.({ preventScroll: true });
        }
    },
);

const icon = () => (dialog.danger ? (/^(delete|remove)/i.test(dialog.title) ? Trash2 : AlertTriangle) : HelpCircle);
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-300 ease-out-expo" enter-from-class="opacity-0" leave-active-class="transition duration-200" leave-to-class="opacity-0">
            <div v-if="dialog.open" class="fixed inset-0 z-[95] grid place-items-center bg-bg/70 p-4 backdrop-blur-md" @click.self="cancel">
                <Transition appear enter-active-class="transition duration-500 ease-out-expo" enter-from-class="translate-y-3 scale-[.97] opacity-0">
                    <div
                        ref="panel"
                        role="alertdialog"
                        aria-modal="true"
                        aria-labelledby="confirm-title"
                        :aria-describedby="dialog.body ? 'confirm-body' : undefined"
                        class="card-solid w-full max-w-md overflow-hidden p-6 shadow-[0_30px_80px_-20px_rgb(0_0_0/.8)] sm:p-7"
                    >
                        <div
                            class="pointer-events-none absolute inset-x-0 top-0 h-px"
                            :class="dialog.danger ? 'bg-gradient-to-r from-transparent via-accent to-transparent' : 'bg-gradient-to-r from-transparent via-line-2 to-transparent'"
                        ></div>
                        <div class="flex gap-4">
                            <span
                                class="grid size-11 shrink-0 place-items-center rounded-2xl"
                                :class="dialog.danger ? 'bg-accent-soft text-accent-hot shadow-[inset_0_0_0_1px_color-mix(in_oklab,var(--accent)_30%,transparent)]' : 'bg-surface-3 text-text-2 shadow-[inset_0_0_0_1px_var(--line-2)]'"
                            >
                                <component :is="icon()" :size="19" />
                            </span>
                            <div class="min-w-0 pt-1">
                                <h2 id="confirm-title" class="font-display text-lg font-semibold leading-snug tracking-tight text-text">{{ dialog.title }}</h2>
                                <p v-if="dialog.body" id="confirm-body" class="mt-2 text-sm leading-relaxed text-text-2">{{ dialog.body }}</p>
                            </div>
                        </div>
                        <div class="mt-7 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button ref="cancelBtn" type="button" class="btn btn-glass btn-sm" :disabled="dialog.busy" @click="cancel">Cancel</button>
                            <button ref="confirmBtn" type="button" class="btn btn-sm min-w-28 disabled:opacity-90" :class="dialog.danger ? 'btn-danger' : 'btn-primary'" :disabled="dialog.busy" @click="accept">
                                <Loader2 v-if="dialog.busy" :size="15" class="animate-spin" />
                                {{ dialog.confirmLabel }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
