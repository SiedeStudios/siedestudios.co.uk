<script setup>
import { computed } from 'vue';
import { Code2, Layers, Gamepad2, PenTool, Server, Zap, Box, Terminal, ShieldCheck, MessagesSquare } from 'lucide-vue-next';

const props = defineProps({ name: { type: String, default: 'code' }, size: { type: Number, default: 22 } });
const map = { code: Code2, layers: Layers, gamepad: Gamepad2, pen: PenTool, server: Server, bolt: Zap, cube: Box, terminal: Terminal, shield: ShieldCheck, chat: MessagesSquare };
const icon = computed(() => map[props.name] ?? Code2);
</script>

<template>
    <component :is="icon" :size="size" :stroke-width="1.5" />
</template>
