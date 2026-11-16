<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight } from 'lucide-vue-next';
import PageHero from '@/components/PageHero.vue';
import { useMotion } from '@/composables/useMotion';
import { Flip } from 'gsap/Flip';
import { gsap, ScrollTrigger, prefersReducedMotion } from '@/lib/motion';

gsap.registerPlugin(Flip);

const props = defineProps({ projects: Array, categories: Array, active: String });

const filter = ref(props.categories.some((c) => c.slug === props.active) ? props.active : 'all');
const root = ref(null);
const grid = ref(null);

const visible = computed(() => (filter.value === 'all' ? props.projects : props.projects.filter((p) => p.category?.slug === filter.value)));

const select = async (slug) => {
    if (slug === filter.value) return;
    const cards = grid.value.querySelectorAll('[data-flip]');
    const state = prefersReducedMotion() ? null : Flip.getState(cards);
    filter.value = slug;
    const url = new URL(window.location.href);
    slug === 'all' ? url.searchParams.delete('category') : url.searchParams.set('category', slug);
    window.history.replaceState(window.history.state, '', url);
    await nextTick();
    if (state) {
        Flip.from(state, {
            duration: 0.8,
            ease: 'expo.inOut',
            absolute: true,
            stagger: 0.03,
            onEnter: (els) => gsap.fromTo(els, { opacity: 0, scale: 0.94, filter: 'blur(8px)' }, { opacity: 1, scale: 1, filter: 'blur(0px)', duration: 0.7, delay: 0.2 }),
            onLeave: (els) => gsap.to(els, { opacity: 0, scale: 0.94, duration: 0.4 }),
            onComplete: () => ScrollTrigger.refresh(),
        });
    }
};

useMotion(root, () => {
    gsap.utils.toArray('[data-flip] img').forEach((img) => {
        gsap.fromTo(img, { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: img, start: 'top bottom', end: 'bottom top', scrub: true } });
    });
});
</script>

<template>
    <Head title="Work" />
    <div ref="root">
        <PageHero eyebrow="Work" :lede="`${projects.length} projects across web platforms, interfaces and Garry's Mod — each one built to be used, not just looked at.`">
            Selected <span class="text-gradient">projects</span>
        </PageHero>

        <section class="wrap">
            <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter by category" data-reveal="fade">
                <button
                    v-for="c in [{ slug: 'all', name: 'All', projects_count: projects.length }, ...categories]"
                    :key="c.slug"
                    type="button"
                    role="tab"
                    :aria-selected="filter === c.slug"
                    class="inline-flex h-10 items-center gap-2 rounded-full px-4 text-sm font-medium transition-all duration-300"
                    :class="filter === c.slug ? 'bg-text text-bg' : 'bg-white/[0.04] text-text-2 shadow-[inset_0_0_0_1px_var(--line)] hover:bg-white/[0.08] hover:text-text'"
                    @click="select(c.slug)"
                >
                    {{ c.name }}
                    <span class="text-xs" :class="filter === c.slug ? 'text-bg/60' : 'text-faint'">{{ c.projects_count }}</span>
                </button>
            </div>

            <div ref="grid" class="mt-10 grid gap-x-6 gap-y-14 md:grid-cols-2">
                <article v-for="(project, i) in visible" :key="project.id" data-flip :data-flip-id="project.id" :class="i % 2 === 1 ? 'md:translate-y-20' : ''">
                    <Link :href="`/projects/${project.slug}`" prefetch class="group block">
                        <div v-liquid data-cursor="View" class="relative overflow-hidden rounded-[1.75rem] bg-surface shadow-[inset_0_0_0_1px_var(--line)]">
                            <div class="aspect-[4/3]"></div>
                            <img v-if="project.cover" :src="project.cover" :srcset="project.cover_srcset" sizes="(min-width: 1024px) 45vw, 100vw" :alt="project.title" loading="lazy" decoding="async" class="absolute inset-0 h-[110%] w-full object-cover object-top transition-transform duration-[1.2s] ease-out-expo group-hover:scale-105" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                            <span class="absolute bottom-5 right-5 flex h-11 items-center gap-2 rounded-full bg-text pl-4 pr-1.5 text-sm font-medium text-bg opacity-0 transition-all duration-500 ease-out-expo translate-y-3 group-hover:translate-y-0 group-hover:opacity-100">
                                View project <span class="grid size-8 place-items-center rounded-full bg-bg text-text"><ArrowUpRight :size="14" /></span>
                            </span>
                            <span v-if="project.is_featured" class="chip absolute left-5 top-5 bg-bg/60 backdrop-blur-md">Featured</span>
                        </div>
                        <div class="mt-6 flex items-start justify-between gap-6">
                            <h2 class="title text-2xl transition-colors group-hover:text-accent-hot lg:text-[1.75rem]">{{ project.title }}</h2>
                            <span class="mt-1.5 shrink-0 text-sm text-muted">{{ project.category?.name }} · {{ project.year }}</span>
                        </div>
                        <p class="mt-2 max-w-xl leading-relaxed text-text-2">{{ project.summary }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span v-for="tag in project.tags.slice(0, 4)" :key="tag" class="chip">{{ tag }}</span>
                        </div>
                    </Link>
                </article>
            </div>
            <p v-if="!visible.length" class="py-20 text-center text-muted">No projects in this category yet.</p>
        </section>
    </div>
</template>
