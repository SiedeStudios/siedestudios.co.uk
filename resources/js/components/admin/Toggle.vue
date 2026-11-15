<script setup>
defineProps({ modelValue: Boolean, label: String, description: String });
defineEmits(['update:modelValue']);
</script>

<template>
    <label class="flex cursor-pointer items-start justify-between gap-4">
        <span v-if="label || description">
            <span class="block text-[0.9375rem]">{{ label }}</span>
            <span v-if="description" class="mt-0.5 block text-sm text-muted">{{ description }}</span>
        </span>
        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full transition-colors duration-300"
            :class="modelValue ? '' : 'bg-surface-3'"
            :style="modelValue ? { background: 'var(--gradient)' } : {}"
            @click="$emit('update:modelValue', !modelValue)"
        >
            <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform duration-300 ease-out-expo" :class="modelValue ? 'translate-x-5' : ''"></span>
        </button>
    </label>
</template>
