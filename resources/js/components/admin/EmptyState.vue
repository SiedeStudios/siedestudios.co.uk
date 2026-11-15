<script setup>
defineProps({ title: String, description: String, icon: [Object, Function] });
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <span v-if="icon" class="grid size-12 place-items-center rounded-2xl bg-white/5 text-muted"><component :is="icon" :size="22" :stroke-width="1.5" /></span>
        <h3 class="title mt-4 text-lg">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-muted">{{ description }}</p>
        <div v-if="$slots.default" class="mt-6"><slot /></div>
    </div>
</template>
