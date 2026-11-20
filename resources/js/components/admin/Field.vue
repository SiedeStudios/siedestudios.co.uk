<script setup>
defineProps({ label: String, error: String, help: String, for: String, optional: Boolean });
</script>

<template>
    <div>
        <label v-if="label" :for="$props.for" class="field-label">{{ label }} <span v-if="optional" class="text-faint">(optional)</span></label>
        <slot />
        <p v-if="error" class="field-error">{{ error }}</p>
        <p v-else-if="help" class="mt-1.5 text-xs text-muted">{{ help }}</p>
    </div>
</template>
