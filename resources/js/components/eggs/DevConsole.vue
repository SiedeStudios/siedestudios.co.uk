<script setup>
import { nextTick, onMounted, onBeforeUnmount, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { gsap, prefersReducedMotion } from '@/lib/motion';

const emit = defineEmits(['close', 'say', 'textures']);
const page = usePage();

const lines = ref([]);
const input = ref('');
const field = ref(null);
const panel = ref(null);
const log = ref(null);
const history = [];
let cursor = 0;
let cheats = false;

const out = (text, tone = 'muted') => lines.value.push({ text, tone });

const pages = { home: '/', work: '/projects', projects: '/projects', services: '/services', contact: '/contact', store: '/store', status: '/status', pay: '/pay', portal: '/portal' };
const accents = { crimson: '#DC143C', emerald: '#10B981', azure: '#3B82F6', amber: '#F59E0B', violet: '#8B5CF6' };

const commands = {
    help: () => {
        out('Available commands:', 'text');
        [
            ['open <page>', 'go to home, work, services, contact, store, status, pay, portal'],
            ['whoami', 'who you are, as far as this server is concerned'],
            ['theme <colour>', 'crimson, emerald, azure, amber, violet'],
            ['noclip', 'toggle gravity'],
            ['season <name>', 'snow, embers, fireworks or off'],
            ['hire', 'the important one'],
            ['credits', 'what this site is built with'],
            ['clear', 'clear the console'],
            ['exit', 'close (or press ` again)'],
        ].forEach(([c, d]) => out(`  ${c.padEnd(16)} ${d}`));
    },
    whoami: () => {
        out(`guest@siedestudios — ${navigator.language}, ${Intl.DateTimeFormat().resolvedOptions().timeZone}`, 'text');
        out(`${window.innerWidth}×${window.innerHeight} viewport, ${navigator.hardwareConcurrency ?? '?'} CPU threads. Nice machine.`);
    },
    open: (arg) => {
        const href = pages[(arg ?? '').toLowerCase()];
        if (!href) return out(`Usage: open <${Object.keys(pages).join('|')}>`, 'warn');
        out(`Loading ${href}…`);
        router.visit(href);
        emit('close');
    },
    theme: (arg) => {
        const colour = accents[(arg ?? '').toLowerCase()];
        if (!colour) return out(`Usage: theme <${Object.keys(accents).join('|')}>`, 'warn');
        document.documentElement.style.setProperty('--accent', colour);
        out(`Accent set to ${arg}. Refresh to put it back.`, 'ok');
    },
    noclip: () => {
        const on = document.documentElement.classList.toggle('noclip');
        out(on ? 'noclip ON' : 'noclip OFF', 'ok');
    },
    sv_cheats: (arg) => {
        cheats = arg === '1';
        out(cheats ? 'sv_cheats set to 1. You feel powerful.' : 'sv_cheats set to 0.', cheats ? 'ok' : 'muted');
    },
    god: () => (cheats ? out('godmode ON. Invoices still need paying, mind.', 'ok') : out("Can't use cheat command god unless the server has sv_cheats set to 1.", 'warn')),
    kill: () => {
        const flash = document.createElement('div');
        flash.className = 'siede-kill-flash';
        document.body.appendChild(flash);
        setTimeout(() => flash.remove(), 1500);
        out('You died. Respawning…', 'warn');
    },
    lua_run: (...args) => out(args.length ? 'Nice try. Server-side Lua is for people with a contract.' : 'Usage: lua_run <code>', 'warn'),
    retry: () => {
        out('Reconnecting…');
        setTimeout(() => router.reload(), 400);
    },
    textures: () => emit('textures'),
    season: (arg) => {
        const modes = ['snow', 'embers', 'fireworks', 'off'];
        if (!modes.includes((arg ?? '').toLowerCase())) return out(`Usage: season <${modes.join('|')}>`, 'warn');
        window.dispatchEvent(new CustomEvent('season-preview', { detail: arg.toLowerCase() }));
        out(arg.toLowerCase() === 'off' ? 'Seasonal effects off for this visit.' : `Previewing ${arg.toLowerCase()}.${arg.toLowerCase() === 'fireworks' ? ' Click anywhere to launch one.' : ''}`, 'ok');
    },
    hire: () => {
        out(page.props.site?.is_available ? 'Good choice. Opening the contact form…' : 'Fully booked right now — but get in touch anyway.', 'ok');
        setTimeout(() => {
            router.visit('/contact');
            emit('close');
        }, 600);
    },
    credits: () => {
        out('Built by SiedeStudios with Laravel, Vue, Inertia, Tailwind and GSAP.', 'text');
        out('No templates were harmed in the making of this website.');
    },
    clear: () => (lines.value = []),
    exit: () => emit('close'),
};
commands.quit = commands.exit;
commands.cls = commands.clear;
commands.disconnect = commands.exit;

const run = () => {
    const raw = input.value.trim();
    input.value = '';
    if (!raw) return;
    history.push(raw);
    cursor = history.length;
    out(`] ${raw}`, 'cmd');

    const [name, ...args] = raw.split(/\s+/);
    const fn = commands[name.toLowerCase()];
    fn ? fn(...args) : out(`Unknown command "${name}". Type help for a list.`, 'warn');
    nextTick(() => log.value && (log.value.scrollTop = log.value.scrollHeight));
};

const onKey = (e) => {
    if (e.key === 'ArrowUp' && history.length) {
        e.preventDefault();
        cursor = Math.max(0, cursor - 1);
        input.value = history[cursor];
    } else if (e.key === 'ArrowDown' && history.length) {
        e.preventDefault();
        cursor = Math.min(history.length, cursor + 1);
        input.value = history[cursor] ?? '';
    } else if (e.key === 'Tab') {
        e.preventDefault();
        const matches = Object.keys(commands).filter((c) => c.startsWith(input.value.toLowerCase()));
        if (matches.length === 1) input.value = `${matches[0]} `;
        else if (matches.length > 1) out(matches.join('  '));
    } else if (e.key === 'Escape' || e.key === '`' || e.key === '~') {
        e.preventDefault();
        emit('close');
    }
};

onMounted(() => {
    out('SiedeStudios developer console', 'text');
    out('Type help for a list of commands.');
    field.value?.focus();
    if (!prefersReducedMotion()) gsap.from(panel.value, { yPercent: -100, duration: 0.6, ease: 'expo.out' });
});
onBeforeUnmount(() => field.value?.blur());
</script>

<template>
    <div ref="panel" class="fixed inset-x-0 top-0 z-[90] border-b border-line-2 bg-[#0d0d10]/95 font-mono text-[13px] shadow-[0_30px_80px_-20px_#000] backdrop-blur-xl" role="dialog" aria-label="Developer console" @click="field?.focus()">
        <div class="wrap flex h-9 items-center justify-between text-xs text-muted">
            <span>Console</span>
            <button type="button" class="hover:text-text" aria-label="Close console" @click.stop="emit('close')">[ esc ]</button>
        </div>
        <div ref="log" class="wrap h-[38svh] overflow-y-auto pb-2" aria-live="polite">
            <p
                v-for="(l, i) in lines"
                :key="i"
                class="whitespace-pre-wrap break-words leading-relaxed"
                :class="{ 'text-text': l.tone === 'text', 'text-muted': l.tone === 'muted', 'text-[#ff6b81]': l.tone === 'warn', 'text-emerald-300': l.tone === 'ok', 'text-faint': l.tone === 'cmd' }"
            >{{ l.text }}</p>
        </div>
        <form class="wrap flex items-center gap-2 border-t border-line py-2.5" @submit.prevent="run">
            <span class="text-accent-hot">]</span>
            <input ref="field" v-model="input" class="flex-1 bg-transparent text-text outline-none placeholder:text-faint" placeholder="help" autocomplete="off" autocapitalize="off" spellcheck="false" aria-label="Console command" @keydown="onKey" />
        </form>
    </div>
</template>
