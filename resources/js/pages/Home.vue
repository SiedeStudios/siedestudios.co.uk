<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, ArrowRight, Check } from 'lucide-vue-next';
import SectionHead from '@/components/SectionHead.vue';
import ServiceIcon from '@/components/ServiceIcon.vue';
import StarRating from '@/components/StarRating.vue';
import LogoMark from '@/components/LogoMark.vue';
import Aurora from '@/components/Aurora.vue';
import { useMotion } from '@/composables/useMotion';
import { gsap, SplitText, blurWords, prefersReducedMotion } from '@/lib/motion';
import { pad } from '@/lib/format';

const props = defineProps({
    projects: Array,
    services: Array,
    testimonials: Array,
    stats: Object,
});

const site = computed(() => usePage().props.site ?? {});
const root = ref(null);

/* Highlight the final word of the tagline with the crimson gradient. */
const headline = computed(() => {
    const text = (site.value.site_tagline || '').trim();
    const i = text.lastIndexOf(' ');
    return i > 0 ? { lead: text.slice(0, i), last: text.slice(i + 1) } : { lead: '', last: text };
});

const stack = ['Laravel', 'Vue', 'Inertia', 'Lua', 'Garry\'s Mod', 'DarkRP', 'Tailwind', 'GSAP', 'MySQL', 'PayPal', 'Discord API', 'Redis'];

const reviewRows = computed(() => {
    const list = props.testimonials ?? [];
    if (list.length < 3) return [list];
    const half = Math.ceil(list.length / 2);
    return [list, [...list.slice(half), ...list.slice(0, half)]];
});

useMotion(root, (el) => {
    const reduced = prefersReducedMotion();
    const mm = gsap.matchMedia();

    /* ---------- Hero ---------- */
    blurWords(el.querySelector('[data-hero-title]'), { delay: 0.25, stagger: 0.06 });
    if (!reduced) {
        gsap.from('[data-hero-fade]', { opacity: 0, y: 18, filter: 'blur(8px)', duration: 1.2, stagger: 0.08, delay: 0.7, clearProps: 'filter' });

        // The mark draws itself, then floats.
        const strokes = gsap.utils.toArray('[data-hero-mark] path');
        strokes.forEach((p) => {
            const len = p.getTotalLength();
            gsap.set(p, { strokeDasharray: len, strokeDashoffset: len });
        });
        gsap.to(strokes, { strokeDashoffset: 0, duration: 1.8, ease: 'expo.inOut', stagger: 0.18, delay: 0.3 });
        gsap.to('[data-hero-mark]', { y: -18, rotation: -3, duration: 5, ease: 'sine.inOut', yoyo: true, repeat: -1 });

        gsap.timeline({ scrollTrigger: { trigger: '[data-hero]', start: 'top top', end: 'bottom top', scrub: 0.8 } })
            .to('[data-hero-copy]', { yPercent: -12, opacity: 0.2, ease: 'none' }, 0)
            .to('[data-hero-art]', { yPercent: 18, scale: 0.92, ease: 'none' }, 0);
    }

    mm.add('(pointer: fine) and (prefers-reduced-motion: no-preference)', () => {
        const hero = el.querySelector('[data-hero]');
        const rx = gsap.quickTo('[data-hero-art]', 'rotationY', { duration: 1.4, ease: 'power3' });
        const ry = gsap.quickTo('[data-hero-art]', 'rotationX', { duration: 1.4, ease: 'power3' });
        const move = (e) => {
            const r = hero.getBoundingClientRect();
            rx(((e.clientX - r.left) / r.width - 0.5) * 18);
            ry(((e.clientY - r.top) / r.height - 0.5) * -14);
        };
        hero.addEventListener('pointermove', move);
        return () => hero.removeEventListener('pointermove', move);
    });

    /* ---------- Ticker ---------- */
    const ticker = el.querySelector('[data-ticker]');
    if (ticker) {
        const loop = gsap.fromTo(ticker, { xPercent: 0 }, { xPercent: -50, duration: 40, ease: 'none', repeat: -1 });
        if (reduced) loop.pause();
    }

    /* ---------- Manifesto: words light up as you read ---------- */
    const manifesto = el.querySelector('[data-manifesto]');
    if (manifesto && !reduced) {
        const split = SplitText.create(manifesto, { type: 'words' });
        gsap.fromTo(split.words, { opacity: 0.16 }, {
            opacity: 1,
            stagger: 0.1,
            ease: 'none',
            scrollTrigger: { trigger: manifesto, start: 'top 80%', end: 'bottom 55%', scrub: 0.6 },
        });
    }
    gsap.utils.toArray('[data-count]').forEach((node) => {
        const end = Number(node.dataset.count);
        const obj = { v: 0 };
        gsap.to(obj, {
            v: end,
            duration: 2.2,
            ease: 'expo.out',
            scrollTrigger: { trigger: node, start: 'top 92%', once: true },
            onUpdate: () => (node.textContent = Math.round(obj.v)),
        });
    });

    /* ---------- Work: stacking cards ---------- */
    mm.add('(min-width: 768px) and (prefers-reduced-motion: no-preference)', () => {
        const cards = gsap.utils.toArray('[data-stack-card]');
        cards.forEach((card, i) => {
            const next = cards[i + 1];
            if (!next) return;
            // Recede as the next card slides over: shrink a little and fade a dark
            // overlay in. (Tweening `filter` from "none" starts at brightness(0),
            // which blacked the card out the moment the next one appeared.)
            const trigger = { trigger: next, start: 'top 85%', end: 'top 25%', scrub: true };
            gsap.fromTo(card.querySelector('[data-stack-inner]'), { scale: 1 }, { scale: 0.94, ease: 'none', scrollTrigger: trigger });
            gsap.fromTo(card.querySelector('[data-stack-shade]'), { opacity: 0 }, { opacity: 0.5, ease: 'power1.in', scrollTrigger: { ...trigger } });
        });
        gsap.utils.toArray('[data-stack-card] img').forEach((img) => {
            gsap.fromTo(img, { yPercent: -6 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: img, start: 'top bottom', end: 'bottom top', scrub: true } });
        });
    });

    /* ---------- Reviews marquee ---------- */
    gsap.utils.toArray('[data-review-row]').forEach((row, i) => {
        const loop = i % 2 === 0
            ? gsap.fromTo(row, { xPercent: 0 }, { xPercent: -50, duration: 60, ease: 'none', repeat: -1 })
            : gsap.fromTo(row, { xPercent: -50 }, { xPercent: 0, duration: 64, ease: 'none', repeat: -1 });
        if (reduced) loop.pause();
        row.addEventListener('mouseenter', () => gsap.to(loop, { timeScale: 0.15, duration: 0.6 }));
        row.addEventListener('mouseleave', () => gsap.to(loop, { timeScale: 1, duration: 0.6 }));
    });

    return () => mm.revert();
});
</script>

<template>
    <Head :title="null" />

    <div ref="root">
        <!-- ============================== HERO ============================== -->
        <section data-hero class="relative flex min-h-svh flex-col overflow-hidden pb-10 pt-32 lg:pt-40">
            <Aurora follow />
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-bg to-transparent" aria-hidden="true"></div>

            <div data-hero-art class="pointer-events-none absolute right-[-5%] top-[16%] hidden [perspective:900px] lg:block" aria-hidden="true">
                <div data-hero-mark class="drop-shadow-[0_30px_80px_rgba(220,20,60,.35)]">
                    <LogoMark :size="540" color="rgb(255 255 255 / 0.9)" />
                </div>
            </div>

            <div class="wrap relative flex flex-1 flex-col">
                <div data-hero-copy class="max-w-[60rem]">
                    <span data-hero-fade class="eyebrow no-dot">
                        <span class="pulse-dot" :class="{ 'is-off': !site.is_available }"></span>
                        {{ site.is_available ? site.availability : 'Currently fully booked' }}
                    </span>
                    <h1 data-hero-title class="display mt-8 text-[clamp(2.9rem,7vw,6.6rem)] opacity-0">
                        {{ headline.lead }} <span class="text-gradient">{{ headline.last }}</span>
                    </h1>
                    <p data-hero-fade class="lede mt-8 max-w-xl">{{ site.hero_intro }}</p>
                    <div data-hero-fade class="mt-10 flex flex-wrap items-center gap-3">
                        <Link href="/projects" class="btn btn-light btn-lg" v-magnetic="0.2">See the work <span class="arrow-badge"><ArrowUpRight :size="14" /></span></Link>
                        <Link href="/contact" class="btn btn-glass btn-lg" v-magnetic="0.2">Start a project</Link>
                    </div>
                </div>

                <div data-hero-fade class="mt-auto flex flex-wrap items-end justify-between gap-6 pt-16">
                    <div class="flex items-center gap-8">
                        <div>
                            <div class="display text-3xl">{{ stats.projects }}<span class="text-accent-hot">+</span></div>
                            <div class="mt-1 text-sm text-muted">Projects shipped</div>
                        </div>
                        <template v-if="stats.reviews">
                            <div class="h-10 w-px bg-line-2"></div>
                            <div>
                                <div class="display flex items-center gap-2.5 text-3xl">{{ Number(stats.rating).toFixed(1) }} <StarRating :model-value="Math.round(stats.rating)" :size="14" /></div>
                                <div class="mt-1 text-sm text-muted">Client rating</div>
                            </div>
                        </template>
                    </div>
                    <span class="mono hidden sm:block">{{ site.location }} — Est. {{ site.founded_year }}</span>
                </div>
            </div>
        </section>

        <!-- ============================== TICKER ============================== -->
        <section class="relative border-y border-line py-7" aria-label="Technologies">
            <div class="fade-x overflow-hidden">
                <div data-ticker class="marquee-track">
                    <template v-for="n in 2" :key="n">
                        <span v-for="item in stack" :key="`${n}-${item}`" class="flex items-center">
                            <span class="px-7 font-display text-xl font-medium tracking-[-0.02em] text-muted lg:text-2xl">{{ item }}</span>
                            <span class="size-1.5 rounded-full bg-accent/60"></span>
                        </span>
                    </template>
                </div>
            </div>
        </section>

        <!-- ============================== MANIFESTO ============================== -->
        <section class="wrap py-28 lg:py-40">
            <span class="eyebrow" data-reveal="fade">The studio</span>
            <p data-manifesto class="heading mt-8 max-w-[68rem] text-[clamp(1.8rem,3.6vw,3.3rem)] leading-[1.12]">
                One studio, two disciplines. I build Laravel platforms and interfaces for the web, and Lua systems for Garry's Mod servers — with the same care for clean code, honest timelines and things that simply work on launch day.
            </p>

            <div class="mt-16 grid gap-4 sm:grid-cols-3">
                <div class="card p-7" data-reveal>
                    <div class="display text-5xl"><span :data-count="stats.projects">0</span><span class="text-accent-hot">+</span></div>
                    <p class="mt-3 text-sm text-muted">Projects shipped across web and game servers.</p>
                </div>
                <div class="card p-7" data-reveal data-reveal-delay="0.08">
                    <div class="display text-5xl">{{ new Date().getFullYear() - Number(site.founded_year || 2020) }}<span class="ml-1 text-2xl text-accent-hot">yrs</span></div>
                    <p class="mt-3 text-sm text-muted">Building for clients since {{ site.founded_year }}.</p>
                </div>
                <div class="card p-7" data-reveal data-reveal-delay="0.16">
                    <div class="display text-5xl">1:1</div>
                    <p class="mt-3 text-sm text-muted">You work directly with the developer — no hand-offs, no account managers.</p>
                </div>
            </div>
        </section>

        <!-- ============================== WORK ============================== -->
        <section v-if="projects.length" class="wrap">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHead label="Selected work">Recent <span class="text-gradient">builds</span></SectionHead>
                <Link href="/projects" class="btn btn-glass" data-reveal>All projects <span class="text-muted">{{ stats.projects }}</span> <ArrowRight :size="16" /></Link>
            </div>

            <div class="mt-14 space-y-6 md:space-y-0">
                <article v-for="(project, i) in projects" :key="project.id" data-stack-card class="md:sticky md:pb-8" :style="{ zIndex: i + 1, top: `${96 + i * 16}px` }">
                    <Link :href="`/projects/${project.slug}`" class="group block">
                        <div data-stack-inner class="card-solid relative grid origin-top gap-8 overflow-hidden p-5 shadow-[inset_0_0_0_1px_var(--line),0_-30px_60px_-30px_rgba(0,0,0,.8)] md:min-h-[33rem] md:grid-cols-12 md:p-6 lg:p-8">
                            <div class="flex flex-col justify-between gap-8 p-2 md:col-span-5 lg:p-4">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="chip">{{ project.category?.name ?? 'Project' }}</span>
                                        <span class="mono">{{ pad(i + 1) }} / {{ pad(projects.length) }}</span>
                                    </div>
                                    <h3 class="heading mt-8 text-[clamp(2rem,3.4vw,3.2rem)]">{{ project.title }}</h3>
                                    <p class="mt-5 leading-relaxed text-text-2">{{ project.summary }}</p>
                                </div>
                                <div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="tag in project.tags.slice(0, 4)" :key="tag" class="chip">{{ tag }}</span>
                                    </div>
                                    <span class="mt-8 inline-flex items-center gap-3 text-[0.9375rem] font-medium">
                                        View case study
                                        <span class="arrow-badge !mr-0 bg-white/10"><ArrowUpRight :size="14" /></span>
                                    </span>
                                </div>
                            </div>
                            <div class="relative overflow-hidden rounded-[1.25rem] bg-surface-2 md:col-span-7">
                                <img v-if="project.cover" :src="project.cover" :alt="project.title" loading="lazy" class="absolute inset-0 h-[112%] w-full object-cover object-top transition-transform duration-[1.2s] ease-out-expo group-hover:scale-[1.04]" />
                                <div class="aspect-[16/10] md:aspect-auto md:h-full"></div>
                                <div class="pointer-events-none absolute inset-0 rounded-[1.25rem] shadow-[inset_0_0_0_1px_rgb(255_255_255/.08)]"></div>
                            </div>
                            <div data-stack-shade class="pointer-events-none absolute inset-0 z-10 hidden bg-[#09090b] opacity-0 md:block" aria-hidden="true"></div>
                        </div>
                    </Link>
                </article>
            </div>
        </section>

        <!-- ============================== SERVICES ============================== -->
        <section class="wrap py-28 lg:py-40">
            <div class="flex flex-wrap items-end justify-between gap-8">
                <SectionHead label="Capabilities">What I can <span class="text-gradient">build for you</span></SectionHead>
                <p class="max-w-sm text-text-2" data-reveal>From the first wireframe to the server it runs on — every project quoted with a fixed scope you can hold me to.</p>
            </div>

            <div class="mt-14 grid gap-4 md:grid-cols-6">
                <Link
                    v-for="(service, i) in services"
                    :key="service.id"
                    v-spotlight
                    :href="`/services#${service.slug}`"
                    class="card group flex flex-col justify-between gap-12 overflow-hidden p-7 lg:p-9"
                    :class="[0, 3].includes(i % 4) ? 'md:col-span-4' : 'md:col-span-2'"
                    data-reveal
                    :data-reveal-delay="(i % 2) * 0.08"
                >
                    <div class="flex items-start justify-between">
                        <span class="grid size-12 place-items-center rounded-2xl text-white shadow-[0_10px_30px_-10px_var(--accent)]" style="background: var(--gradient)">
                            <ServiceIcon :name="service.icon" :size="22" />
                        </span>
                        <span class="grid size-10 place-items-center rounded-full bg-white/5 text-muted transition-all duration-500 ease-out-expo group-hover:-rotate-45 group-hover:bg-white group-hover:text-bg">
                            <ArrowRight :size="16" />
                        </span>
                    </div>
                    <div>
                        <h3 class="title text-2xl lg:text-[1.75rem]">{{ service.title }}</h3>
                        <p class="mt-2 text-text-2">{{ service.tagline }}</p>
                        <ul v-if="[0, 3].includes(i % 4)" class="mt-6 grid gap-2.5 sm:grid-cols-2">
                            <li v-for="f in service.features.slice(0, 4)" :key="f" class="flex items-center gap-2 text-sm text-muted">
                                <Check :size="14" class="shrink-0 text-accent-hot" /> {{ f }}
                            </li>
                        </ul>
                    </div>
                </Link>
            </div>
        </section>

        <!-- ============================== TESTIMONIALS ============================== -->
        <section v-if="testimonials.length" class="relative overflow-hidden pb-8">
            <div class="wrap flex flex-wrap items-end justify-between gap-8">
                <SectionHead label="Kind words">Clients say it <span class="text-gradient">best</span></SectionHead>
                <div class="flex items-center gap-4" data-reveal>
                    <div class="display text-5xl">{{ Number(stats.rating).toFixed(1) }}</div>
                    <div>
                        <StarRating :model-value="Math.round(stats.rating)" :size="16" />
                        <p class="mt-1 text-sm text-muted">from {{ stats.reviews }} review{{ stats.reviews === 1 ? '' : 's' }}</p>
                    </div>
                </div>
            </div>

            <div class="fade-x mt-14 space-y-4 overflow-hidden">
                <div v-for="(row, r) in reviewRows" :key="r" data-review-row class="marquee-track">
                    <template v-for="n in 2" :key="n">
                        <figure v-for="t in row" :key="`${n}-${t.id}`" class="card mr-4 flex w-[min(86vw,26rem)] shrink-0 flex-col justify-between gap-8 p-7">
                            <div>
                                <StarRating :model-value="t.rating" :size="14" />
                                <blockquote class="mt-5 text-[1.0625rem] leading-relaxed text-text">“{{ t.body }}”</blockquote>
                            </div>
                            <figcaption class="flex items-center gap-3">
                                <img v-if="t.avatar" :src="t.avatar" :alt="t.name" class="size-11 rounded-full object-cover" />
                                <span v-else class="grid size-11 place-items-center rounded-full bg-surface-3 font-medium">{{ t.name.charAt(0) }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate font-medium">{{ t.name }}</span>
                                    <span class="block truncate text-sm text-muted">{{ [t.role, t.company].filter(Boolean).join(' · ') }}</span>
                                </span>
                            </figcaption>
                        </figure>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>
