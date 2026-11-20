<script setup>
defineProps({ label: String, title: String, align: { type: String, default: 'left' } });
</script>

<template>
    <div :class="align === 'center' ? 'mx-auto max-w-3xl text-center' : ''">
        <span class="eyebrow" data-reveal="fade">{{ label }}</span>
        <h2 v-if="title || $slots.default" class="heading mt-6 text-[clamp(2.3rem,5vw,4.4rem)]" data-reveal>
            <slot>{{ title }}</slot>
        </h2>
    </div>
</template>
