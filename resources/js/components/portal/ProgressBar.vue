<script setup>
defineProps({ value: { type: Number, default: 0 }, label: String });
</script>

<template>
    <div>
        <div class="flex items-center justify-between text-xs text-muted">
            <span>{{ label ?? 'Progress' }}</span>
            <span class="font-mono text-text-2">{{ value }}%</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/[0.06]" role="progressbar" :aria-valuenow="value" aria-valuemin="0" aria-valuemax="100" :aria-label="label ?? 'Progress'">
            <div class="h-full rounded-full transition-[width] duration-1000 ease-out-expo" :style="{ width: `${value}%`, background: 'var(--gradient)' }"></div>
        </div>
    </div>
</template>
