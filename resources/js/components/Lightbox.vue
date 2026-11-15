<script setup>
import { computed, onBeforeUnmount, watch } from 'vue';
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { getLenis } from '@/lib/motion';

const props = defineProps({ images: Array, index: { type: Number, default: null } });
const emit = defineEmits(['update:index']);
const open = computed(() => props.index !== null && props.index >= 0);
const current = computed(() => props.images?.[props.index]);

const step = (dir) => emit('update:index', (props.index + dir + props.images.length) % props.images.length);
const close = () => emit('update:index', null);
const key = (e) => {
    if (!open.value) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') step(1);
    if (e.key === 'ArrowLeft') step(-1);
};

watch(open, (v) => {
    v ? getLenis()?.stop() : getLenis()?.start();
    v ? window.addEventListener('keydown', key) : window.removeEventListener('keydown', key);
});
onBeforeUnmount(() => window.removeEventListener('keydown', key));
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-500 ease-out-expo" enter-from-class="opacity-0" leave-active-class="transition duration-300" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[90] flex flex-col bg-bg/90 backdrop-blur-xl" role="dialog" aria-modal="true" @click.self="close">
                <div class="flex items-center justify-between p-5">
                    <span class="mono">{{ index + 1 }} / {{ images.length }}</span>
                    <button type="button" class="btn btn-glass btn-icon" aria-label="Close" @click="close"><X :size="18" /></button>
                </div>
                <div class="relative flex flex-1 items-center justify-center px-4 pb-6 sm:px-20" @click.self="close">
                    <Transition mode="out-in" enter-active-class="transition duration-500 ease-out-expo" enter-from-class="opacity-0 scale-[.98] blur-sm" leave-active-class="transition duration-200" leave-to-class="opacity-0">
                        <figure :key="current.url" class="flex max-h-full flex-col items-center">
                            <img :src="current.url" :alt="current.caption || ''" class="max-h-[78vh] w-auto rounded-2xl object-contain shadow-2xl" />
                            <figcaption v-if="current.caption" class="mt-4 text-sm text-text-2">{{ current.caption }}</figcaption>
                        </figure>
                    </Transition>
                    <template v-if="images.length > 1">
                        <button type="button" class="btn btn-glass btn-icon absolute left-4 top-1/2 -translate-y-1/2" aria-label="Previous" @click="step(-1)"><ChevronLeft :size="18" /></button>
                        <button type="button" class="btn btn-glass btn-icon absolute right-4 top-1/2 -translate-y-1/2" aria-label="Next" @click="step(1)"><ChevronRight :size="18" /></button>
                    </template>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
