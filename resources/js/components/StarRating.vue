<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: Number, default: 0 },
    size: { type: Number, default: 16 },
    interactive: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);
const hover = ref(0);
const labels = ['Poor', 'Fair', 'Good', 'Great', 'Outstanding'];

const star = 'M11.1 2.6a1 1 0 0 1 1.8 0l2.4 4.9 5.4.8a1 1 0 0 1 .6 1.7l-3.9 3.8.9 5.4a1 1 0 0 1-1.5 1l-4.8-2.5-4.8 2.5a1 1 0 0 1-1.5-1l.9-5.4-3.9-3.8a1 1 0 0 1 .6-1.7l5.4-.8z';
</script>

<template>
    <div class="inline-flex items-center gap-1" :role="interactive ? 'radiogroup' : 'img'" :aria-label="interactive ? 'Rating' : `${modelValue} out of 5 stars`" @mouseleave="hover = 0">
        <component
            :is="interactive ? 'button' : 'span'"
            v-for="i in 5"
            :key="i"
            :type="interactive ? 'button' : undefined"
            :role="interactive ? 'radio' : undefined"
            :aria-checked="interactive ? modelValue === i : undefined"
            :aria-label="interactive ? `${i} star${i > 1 ? 's' : ''} — ${labels[i - 1]}` : undefined"
            class="relative block transition-transform duration-300"
            :class="interactive ? 'hover:scale-110 active:scale-95' : ''"
            @mouseenter="interactive && (hover = i)"
            @click="interactive && emit('update:modelValue', i)"
        >
            <svg :width="size" :height="size" viewBox="0 0 24 24" aria-hidden="true">
                <path :d="star" fill="var(--surface-3)" />
                <path :d="star" fill="#f5b642" class="transition-[clip-path] duration-300" :style="{ clipPath: (hover || modelValue) >= i ? 'inset(0 0 0 0)' : 'inset(0 100% 0 0)' }" />
            </svg>
        </component>
        <span v-if="interactive" class="ml-3 min-w-24 text-sm text-text-2">{{ labels[(hover || modelValue) - 1] ?? 'Select a rating' }}</span>
    </div>
</template>
