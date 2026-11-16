<script setup>
import { defineAsyncComponent, onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Hidden extras for curious visitors. Nothing here is loaded until it's
 * triggered — the console UI is its own lazily-loaded chunk.
 *   `  or  ~     open the developer console
 *   ↑↑↓↓←→←→BA   missing textures
 */
const DevConsole = defineAsyncComponent(() => import('./DevConsole.vue'));

const consoleOpen = ref(false);
const notice = ref(null);
let noticeTimer;

const say = (text) => {
    notice.value = text;
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => (notice.value = null), 3200);
};

const typing = (e) => {
    const t = e.target;
    return t instanceof HTMLElement && (t.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(t.tagName));
};

const KONAMI = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
let progress = 0;

const toggleTextures = () => {
    const on = document.documentElement.classList.toggle('missing-textures');
    say(on ? 'Missing textures. Did you forget to mount the content pack?' : 'Textures restored.');
};

const onKey = (e) => {
    if (typing(e)) return;

    if ((e.key === '`' || e.key === '~') && !e.ctrlKey && !e.metaKey && !e.altKey) {
        e.preventDefault();
        consoleOpen.value = !consoleOpen.value;
        return;
    }

    progress = e.key === KONAMI[progress] || e.key.toLowerCase() === KONAMI[progress] ? progress + 1 : e.key === KONAMI[0] ? 1 : 0;
    if (progress === KONAMI.length) {
        progress = 0;
        toggleTextures();
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKey);

    // A note for anyone opening their browser's devtools.
    if (!window.__siedeGreeted) {
        window.__siedeGreeted = true;
        console.log(
            '%c S %c SiedeStudios ',
            'background:linear-gradient(135deg,#ff4d6d,#dc143c);color:#fff;font:700 28px/1.4 system-ui;padding:4px 14px;border-radius:10px 0 0 10px',
            'background:#16161a;color:#f4f1ee;font:600 16px/2.15 system-ui;padding:4px 14px;border-radius:0 10px 10px 0',
        );
        console.log('%cPoking around under the hood? Good — so do I.\nPress %c`%c on the page for a real console, or say hi at /contact.', 'color:#a1a1aa;font:13px/1.6 system-ui', 'color:#ff4d6d;font:700 13px monospace', 'color:#a1a1aa;font:13px/1.6 system-ui');
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    clearTimeout(noticeTimer);
});
</script>

<template>
    <DevConsole v-if="consoleOpen" @close="consoleOpen = false" @say="say" @textures="toggleTextures" />
    <Transition enter-from-class="opacity-0 translate-y-3" leave-to-class="opacity-0 translate-y-3" enter-active-class="transition duration-500 ease-out-expo" leave-active-class="transition duration-300">
        <div v-if="notice" class="fixed bottom-6 left-1/2 z-[95] -translate-x-1/2 rounded-full bg-surface-2/95 px-5 py-2.5 font-mono text-sm shadow-[inset_0_0_0_1px_var(--line-2),0_20px_50px_-15px_#000] backdrop-blur-xl" role="status">{{ notice }}</div>
    </Transition>
</template>

<style>
/* Konami code: every image becomes the classic magenta/black "missing" checkerboard. */
html.missing-textures :is(img, canvas, video) {
    visibility: hidden;
}
html.missing-textures :has(> img, > canvas) {
    background: repeating-conic-gradient(#ff00dc 0 25%, #000 0 50%) 0 0 / 32px 32px !important;
}

/* `noclip`: the page loses gravity for a while. */
@keyframes siede-noclip {
    0% { transform: translate3d(0, 0, 0) rotate(0deg); }
    50% { transform: translate3d(var(--nc-x, 6px), var(--nc-y, -14px), 0) rotate(var(--nc-r, -0.6deg)); }
    100% { transform: translate3d(0, 0, 0) rotate(0deg); }
}
html.noclip main section > * {
    animation: siede-noclip 5.5s ease-in-out infinite;
}
html.noclip main section > *:nth-child(2n) { --nc-x: -8px; --nc-y: -10px; --nc-r: 0.5deg; animation-duration: 6.5s; }
html.noclip main section > *:nth-child(3n) { --nc-x: 4px; --nc-y: -18px; --nc-r: -0.3deg; animation-duration: 7.5s; }

/* `kill`: a quick red flash. */
@keyframes siede-kill { 0% { opacity: 0.85; } 100% { opacity: 0; } }
.siede-kill-flash {
    position: fixed; inset: 0; z-index: 120; pointer-events: none;
    background: radial-gradient(circle, rgb(220 20 60 / 0.35), rgb(120 0 0 / 0.85));
    animation: siede-kill 1.4s ease-out forwards;
}
</style>
