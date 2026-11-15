<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, Globe, Github, Play, ShoppingBag, Maximize2 } from 'lucide-vue-next';
import BrandIcon from '@/components/BrandIcon.vue';
import StarRating from '@/components/StarRating.vue';
import Lightbox from '@/components/Lightbox.vue';
import Aurora from '@/components/Aurora.vue';
import { useMotion } from '@/composables/useMotion';
import { blurWords, gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({ project: Object, testimonials: Array, next: Object });

const root = ref(null);
const lightbox = ref(null);

const linkMeta = {
    site: { label: 'Visit live site', icon: Globe },
    discord: { label: 'Discord', brand: 'discord' },
    github: { label: 'Source', icon: Github },
    store: { label: 'Store page', icon: ShoppingBag },
    video: { label: 'Watch video', icon: Play },
};
const links = computed(() => Object.entries(props.project.links).map(([key, href]) => ({ key, href, ...linkMeta[key] })));
const gallery = computed(() => props.project.images.length ? props.project.images : (props.project.cover ? [{ id: 0, url: props.project.cover, caption: null }] : []));

const meta = computed(() => [
    ['Client', props.project.client],
    ['Role', props.project.role],
    ['Year', props.project.year],
    ['Category', props.project.category?.name],
].filter(([, v]) => v));

useMotion(root, (el) => {
    blurWords(el.querySelector('[data-title]'), { delay: 0.15 });
    if (prefersReducedMotion()) return;
    gsap.from('[data-fade]', { opacity: 0, y: 16, filter: 'blur(8px)', duration: 1.1, stagger: 0.07, delay: 0.4, clearProps: 'filter' });
    gsap.fromTo('[data-cover]', { clipPath: 'inset(10% 6% 0% 6% round 40px)' }, { clipPath: 'inset(0% 0% 0% 0% round 28px)', duration: 1.6, ease: 'expo.inOut', delay: 0.3 });
    gsap.fromTo('[data-cover] img', { yPercent: -4, scale: 1.12 }, { yPercent: 6, scale: 1.04, ease: 'none', scrollTrigger: { trigger: '[data-cover]', start: 'top bottom', end: 'bottom top', scrub: true } });
});
</script>

<template>
    <Head :title="project.title" />
    <div ref="root">
        <section class="relative overflow-hidden pb-14 pt-32 lg:pt-40">
            <Aurora :intensity="0.45" />
            <div class="wrap relative">
                <div v-if="project.is_draft" class="mb-6 inline-flex rounded-full bg-amber-400/15 px-3 py-1 text-sm text-amber-300">Draft preview — only visible to admins</div>
                <Link href="/projects" data-fade class="inline-flex items-center gap-2 text-sm text-muted transition-colors hover:text-text"><ArrowLeft :size="15" /> All work</Link>
                <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-8">
                        <span data-fade class="eyebrow">{{ project.category?.name ?? 'Project' }}</span>
                        <h1 data-title class="display mt-6 text-[clamp(2.8rem,6.6vw,6.2rem)] opacity-0">{{ project.title }}</h1>
                        <p data-fade class="lede mt-6 max-w-2xl">{{ project.summary }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3 lg:col-span-4 lg:justify-end">
                        <a v-for="(l, i) in links" :key="l.key" data-fade :href="l.href" target="_blank" rel="noopener" class="btn" :class="i === 0 ? 'btn-primary' : 'btn-glass'">
                            <BrandIcon v-if="l.brand" :name="l.brand" :size="16" />
                            <component :is="l.icon" v-else :size="16" />
                            {{ l.label }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="project.cover" class="wrap">
            <div data-cover class="relative overflow-hidden rounded-[1.75rem] bg-surface">
                <div class="aspect-[16/9]"></div>
                <img :src="project.cover" :alt="project.title" class="absolute inset-0 h-full w-full object-cover object-top" />
            </div>
        </section>

        <section class="wrap mt-20 grid gap-14 lg:grid-cols-12">
            <aside class="lg:col-span-4">
                <div class="card p-7 lg:sticky lg:top-28" data-reveal>
                    <dl class="grid grid-cols-2 gap-6 lg:grid-cols-1">
                        <div v-for="[k, v] in meta" :key="k">
                            <dt class="text-sm text-muted">{{ k }}</dt>
                            <dd class="mt-1 font-medium">{{ v }}</dd>
                        </div>
                    </dl>
                    <div v-if="project.tags.length" class="mt-7 border-t border-line pt-6">
                        <div class="text-sm text-muted">Stack</div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span v-for="tag in project.tags" :key="tag" class="chip">{{ tag }}</span>
                        </div>
                    </div>
                </div>
            </aside>
            <article class="lg:col-span-7 lg:col-start-6">
                <div v-if="project.body" class="prose-studio" data-reveal v-html="project.body"></div>
                <p v-else class="lede" data-reveal>{{ project.summary }}</p>
            </article>
        </section>

        <section v-if="gallery.length > 1 || project.images.length" class="wrap mt-28">
            <div class="flex items-end justify-between gap-6">
                <div>
                    <span class="eyebrow" data-reveal="fade">Gallery</span>
                    <h2 class="heading mt-5 text-[clamp(2rem,4vw,3.2rem)]" data-reveal>Screenshots</h2>
                </div>
                <span class="text-sm text-muted">{{ gallery.length }} image{{ gallery.length === 1 ? '' : 's' }}</span>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="(img, i) in gallery"
                    :key="img.id"
                    type="button"
                    class="group relative overflow-hidden rounded-[1.25rem] bg-surface text-left shadow-[inset_0_0_0_1px_var(--line)]"
                    :class="i % 5 === 0 ? 'sm:col-span-2' : ''"
                    data-reveal
                    @click="lightbox = i"
                >
                    <div :class="i % 5 === 0 ? 'aspect-[16/9]' : 'aspect-[4/3]'"></div>
                    <img :src="img.url" :alt="img.caption || `${project.title} screenshot ${i + 1}`" loading="lazy" class="absolute inset-0 h-full w-full object-cover object-top transition-transform duration-[1s] ease-out-expo group-hover:scale-105" />
                    <span class="absolute right-4 top-4 grid size-10 place-items-center rounded-full bg-bg/60 opacity-0 backdrop-blur-md transition-opacity duration-300 group-hover:opacity-100"><Maximize2 :size="15" /></span>
                    <span v-if="img.caption" class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4 pt-10 text-sm">{{ img.caption }}</span>
                </button>
            </div>
            <Lightbox v-model:index="lightbox" :images="gallery" />
        </section>

        <section v-if="testimonials.length" class="wrap mt-28">
            <span class="eyebrow" data-reveal="fade">Client feedback</span>
            <div class="mt-8 grid gap-4" :class="testimonials.length > 1 ? 'md:grid-cols-2' : ''">
                <figure v-for="t in testimonials" :key="t.id" class="card flex flex-col justify-between gap-8 p-8 lg:p-10" data-reveal>
                    <div>
                        <StarRating :model-value="t.rating" :size="16" />
                        <blockquote class="heading mt-6 text-[clamp(1.3rem,2vw,1.75rem)] leading-snug">“{{ t.body }}”</blockquote>
                    </div>
                    <figcaption class="flex items-center gap-3">
                        <img v-if="t.avatar" :src="t.avatar" :alt="t.name" class="size-12 rounded-full object-cover" />
                        <span v-else class="grid size-12 place-items-center rounded-full bg-surface-3 font-medium">{{ t.name.charAt(0) }}</span>
                        <span>
                            <span class="block font-medium">{{ t.name }}</span>
                            <span class="block text-sm text-muted">{{ [t.role, t.company].filter(Boolean).join(' · ') }}</span>
                        </span>
                    </figcaption>
                </figure>
            </div>
        </section>

        <section v-if="next" class="wrap mt-28">
            <Link :href="`/projects/${next.slug}`" class="card-solid group relative block overflow-hidden p-8 lg:p-12" data-reveal>
                <img v-if="next.cover" :src="next.cover" alt="" class="absolute inset-0 h-full w-full object-cover object-top opacity-25 blur-[2px] transition-all duration-[1.2s] ease-out-expo group-hover:scale-105 group-hover:opacity-40 group-hover:blur-0" />
                <div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/80 to-transparent"></div>
                <div class="relative flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <span class="text-sm text-muted">Next project</span>
                        <div class="display mt-3 text-[clamp(2.2rem,5vw,4.5rem)]">{{ next.title }}</div>
                    </div>
                    <span class="grid size-16 place-items-center rounded-full bg-text text-bg transition-transform duration-700 ease-out-expo group-hover:-rotate-45"><ArrowUpRight :size="24" /></span>
                </div>
            </Link>
        </section>
    </div>
</template>
