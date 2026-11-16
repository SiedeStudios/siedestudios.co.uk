<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { scrollToEl } from '@/lib/motion';

defineProps({ eyebrow: String, title: String, intro: String, updated: String, sections: Array });

const active = ref(null);
let io;
onMounted(() => {
    io = new IntersectionObserver((entries) => {
        entries.forEach((e) => e.isIntersecting && (active.value = e.target.id));
    }, { rootMargin: '-20% 0px -70% 0px' });
    document.querySelectorAll('[data-legal-section]').forEach((el) => io.observe(el));
});
onBeforeUnmount(() => io?.disconnect());
</script>

<template>
    <div>
        <section class="relative pb-12 pt-36 lg:pt-44">
            <div class="wrap">
                <span class="eyebrow">{{ eyebrow }}</span>
                <h1 class="display mt-7 text-[clamp(2.6rem,6vw,5rem)]">{{ title }}</h1>
                <p v-if="intro" class="lede mt-6 max-w-2xl">{{ intro }}</p>
                <p v-if="updated" class="mt-6 font-mono text-sm text-muted">Last updated {{ updated }}</p>
            </div>
        </section>

        <section class="wrap grid gap-12 pb-28 lg:grid-cols-12 lg:pb-36">
            <nav class="lg:col-span-3" aria-label="On this page">
                <ol class="space-y-1 text-sm lg:sticky lg:top-28">
                    <li v-for="(s, i) in sections" :key="s.id">
                        <a :href="`#${s.id}`" class="flex gap-3 rounded-lg px-3 py-1.5 transition-colors" :class="active === s.id ? 'bg-white/[0.06] text-text' : 'text-muted hover:text-text'" @click.prevent="scrollToEl(`#${s.id}`)">
                            <span class="font-mono text-xs text-faint">{{ String(i + 1).padStart(2, '0') }}</span>{{ s.title }}
                        </a>
                    </li>
                </ol>
            </nav>
            <article class="prose-studio legal max-w-3xl lg:col-span-8 lg:col-start-5">
                <slot />
                <hr />
                <p class="text-sm text-muted">See also: <Link href="/privacy">Privacy policy</Link> · <Link href="/cookies">Cookie policy</Link> · <Link href="/terms">Terms</Link></p>
            </article>
        </section>
    </div>
</template>

<style>
.legal h2 { scroll-margin-top: 7rem; }
.legal table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.legal th, .legal td { border-bottom: 1px solid var(--line); padding: 0.7rem 0.75rem 0.7rem 0; text-align: left; vertical-align: top; }
.legal th { color: var(--muted); font-weight: 500; }
.legal .table-wrap { overflow-x: auto; }
.legal dl.facts { display: grid; grid-template-columns: max-content 1fr; gap: 0.5rem 1.5rem; }
.legal dl.facts dt { color: var(--muted); }
.legal dl.facts dd { margin: 0; white-space: pre-line; }
</style>
