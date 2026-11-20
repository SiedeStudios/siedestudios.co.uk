<script setup>
import { computed, ref } from 'vue';
import { useMotion } from '@/composables/useMotion';
import { gsap, ScrollTrigger, prefersReducedMotion } from '@/lib/motion';
import { pad } from '@/lib/format';

/**
 * Scroll-driven case study: chapters scroll on the left while a pinned
 * "stage" on the right swaps to each chapter's screenshot.
 */
const props = defineProps({ chapters: Array, title: String });

const root = ref(null);
const active = ref(0);

// Every chapter shows its own image, or keeps the previous one on stage.
const stage = computed(() => {
    const images = [];
    const map = [];
    props.chapters.forEach((c) => {
        if (c.image && images[images.length - 1]?.url !== c.image.url) images.push(c.image);
        map.push(Math.max(0, images.length - 1));
    });
    return { images, map };
});
const shown = computed(() => stage.value.map[active.value] ?? 0);

useMotion(root, (el) => {
    el.querySelectorAll('[data-chapter]').forEach((chapter, i) => {
        ScrollTrigger.create({
            trigger: chapter,
            start: 'top 55%',
            end: 'bottom 55%',
            onToggle: (self) => self.isActive && (active.value = i),
        });
    });

    if (prefersReducedMotion()) return;
    gsap.fromTo('[data-story-progress]', { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: el, start: 'top 55%', end: 'bottom 55%', scrub: true } });
    el.querySelectorAll('[data-chapter-body]').forEach((body) => {
        gsap.from(body, { opacity: 0, y: 40, duration: 1.1, scrollTrigger: { trigger: body, start: 'top 85%', once: true } });
    });
}, { reveal: false });
</script>

<template>
    <section ref="root" class="wrap relative" aria-label="Case study">
        <div class="grid gap-x-16 lg:grid-cols-12">
            <!-- Chapters -->
            <div class="relative lg:col-span-5">
                <div class="absolute -left-6 top-0 hidden h-full w-px bg-line lg:block" aria-hidden="true">
                    <div data-story-progress class="h-full w-full origin-top" style="background: var(--gradient)"></div>
                </div>
                <article
                    v-for="(c, i) in chapters"
                    :key="i"
                    data-chapter
                    class="flex flex-col justify-center py-12 transition-opacity duration-700 lg:min-h-[78svh] lg:py-20"
                    :class="active === i ? 'opacity-100' : 'lg:opacity-30'"
                >
                    <div data-chapter-body>
                        <span class="mono text-accent-hot">{{ pad(i + 1) }} <span class="text-faint">/ {{ pad(chapters.length) }}</span></span>
                        <h2 class="heading mt-4 text-[clamp(1.9rem,3.2vw,2.8rem)]">{{ c.title }}</h2>
                        <figure v-if="c.image" class="relative mt-7 overflow-hidden rounded-[1.25rem] bg-surface shadow-[inset_0_0_0_1px_var(--line)] lg:hidden">
                            <div class="aspect-[16/10]"></div>
                            <img :src="c.image.url" :srcset="c.image.srcset" sizes="100vw" :alt="c.image.caption || c.title" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover object-top" />
                        </figure>
                        <div class="prose-studio mt-6" v-html="c.html"></div>
                    </div>
                </article>
            </div>

            <!-- Pinned stage -->
            <div class="hidden lg:col-span-7 lg:block">
                <div class="sticky top-24 flex h-[calc(100svh-7rem)] flex-col justify-center">
                    <div class="overflow-hidden rounded-[1.5rem] bg-surface shadow-[inset_0_0_0_1px_var(--line-2),0_50px_120px_-50px_rgba(0,0,0,.9)]">
                        <div class="flex h-10 items-center gap-2 border-b border-line px-4">
                            <span class="size-2.5 rounded-full bg-white/15"></span><span class="size-2.5 rounded-full bg-white/15"></span><span class="size-2.5 rounded-full bg-white/15"></span>
                            <span class="mx-auto truncate font-mono text-xs text-muted transition-opacity duration-500">{{ title }} — {{ chapters[active]?.title }}</span>
                        </div>
                        <div class="relative aspect-[16/10] bg-bg">
                            <img
                                v-for="(img, k) in stage.images"
                                :key="img.url"
                                :src="img.url"
                                :srcset="img.srcset"
                                sizes="55vw"
                                :alt="img.caption || title"
                                :loading="k === 0 ? 'eager' : 'lazy'"
                                decoding="async"
                                class="absolute inset-0 h-full w-full object-cover object-top transition-[clip-path,transform,filter] duration-[1100ms] ease-out-expo"
                                :style="{
                                    zIndex: k,
                                    clipPath: k <= shown ? 'inset(0% 0% 0% 0%)' : 'inset(100% 0% 0% 0%)',
                                    transform: k === shown ? 'scale(1)' : 'scale(1.06)',
                                    filter: k < shown ? 'brightness(.5)' : 'none',
                                }"
                            />
                            <div v-if="!stage.images.length" class="absolute inset-0 grid place-items-center text-faint">No screenshots yet</div>
                        </div>
                    </div>
                    <ol class="mt-6 flex gap-2" aria-label="Chapters">
                        <li v-for="(c, i) in chapters" :key="i" class="min-w-0 flex-1">
                            <span class="block h-1 rounded-full transition-colors duration-500" :class="i < active ? 'bg-white/40' : i === active ? '' : 'bg-white/10'" :style="i === active ? { background: 'var(--gradient)' } : {}"></span>
                            <span class="mt-2 block truncate text-xs transition-colors duration-500" :class="i === active ? 'text-text' : 'text-faint'">{{ c.title }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
</template>
