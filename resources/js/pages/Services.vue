<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Check, Plus } from 'lucide-vue-next';
import PageHero from '@/components/PageHero.vue';
import ServiceIcon from '@/components/ServiceIcon.vue';
import StarRating from '@/components/StarRating.vue';
import { useMotion } from '@/composables/useMotion';
import { gsap, scrollToEl, prefersReducedMotion } from '@/lib/motion';
import { money, pad } from '@/lib/format';

const props = defineProps({ services: Array, testimonials: Array });

const root = ref(null);
const openFaq = ref(0);

const steps = [
    { title: 'Discover', body: 'A short call or Discord chat to understand what you need, who it\'s for and what success looks like.' },
    { title: 'Plan & quote', body: 'A written scope, timeline and fixed quote. No surprises, no hourly meter running in the background.' },
    { title: 'Design & build', body: 'Regular progress updates and a staging link so you can see it take shape and steer early.' },
    { title: 'Launch & support', body: 'Deployment, handover and a support window after launch. Invoices are paid securely through PayPal.' },
];

const faqs = [
    { q: 'How do payments work?', a: 'Each project gets an invoice with a unique link. You can pay it securely via PayPal — no account on this site needed. Larger projects are usually split into milestones.' },
    { q: 'Can you work on an existing codebase or addon?', a: 'Yes. I regularly pick up existing Laravel apps and Garry\'s Mod addons — fixing bugs, improving performance or adding features. I\'ll review the code first and tell you honestly what I find.' },
    { q: 'How long does a typical project take?', a: 'Smaller addons and fixes can take days; full web platforms usually take a few weeks. You\'ll get a realistic timeline with your quote before anything starts.' },
    { q: 'Do you offer support after launch?', a: 'Every project includes a support window after launch for bug fixes. Ongoing maintenance and new features can be arranged separately.' },
];

const toggle = (i) => (openFaq.value = openFaq.value === i ? null : i);

const enterFaq = (el, done) => {
    gsap.fromTo(el, { height: 0, opacity: 0 }, { height: 'auto', opacity: 1, duration: 0.6, ease: 'expo.out', onComplete: done });
};
const leaveFaq = (el, done) => {
    gsap.to(el, { height: 0, opacity: 0, duration: 0.45, ease: 'expo.inOut', onComplete: done });
};

useMotion(root, (el) => {
    if (prefersReducedMotion()) return;
    const path = el.querySelector('[data-process-path]');
    if (path) {
        const len = path.getTotalLength();
        gsap.set(path, { strokeDasharray: len, strokeDashoffset: len });
        gsap.to(path, { strokeDashoffset: 0, ease: 'none', scrollTrigger: { trigger: '[data-process]', start: 'top 70%', end: 'bottom 60%', scrub: 0.8 } });
    }
    gsap.utils.toArray('[data-step]').forEach((step, i) => {
        gsap.from(step, { opacity: 0, y: 30, filter: 'blur(8px)', duration: 1.1, delay: i * 0.08, clearProps: 'filter', scrollTrigger: { trigger: '[data-process]', start: 'top 70%', once: true } });
    });
});
</script>

<template>
    <Head title="Services" />
    <div ref="root">
        <PageHero eyebrow="Services" lede="Web platforms, interfaces and game-server systems — designed, built and supported by one developer who cares how it all fits together.">
            Everything you need to <span class="text-gradient">launch</span>
            <template #actions>
                <Link href="/contact" class="btn btn-light btn-lg">Start a project <span class="arrow-badge"><ArrowUpRight :size="14" /></span></Link>
                <button type="button" class="btn btn-glass btn-lg" @click="scrollToEl('#process')">How it works</button>
            </template>
        </PageHero>

        <!-- quick index -->
        <section class="wrap">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" data-reveal>
                <a v-for="(s, i) in services" :key="s.id" :href="`#${s.slug}`" class="card group flex items-center gap-4 p-4 transition-colors hover:bg-white/[0.06]" @click.prevent="scrollToEl(`#${s.slug}`)">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl text-white" style="background: var(--gradient)"><ServiceIcon :name="s.icon" :size="18" /></span>
                    <span class="min-w-0">
                        <span class="mono block">{{ pad(i + 1) }}</span>
                        <span class="block truncate font-medium">{{ s.title }}</span>
                    </span>
                </a>
            </div>
        </section>

        <!-- service detail -->
        <section class="wrap mt-24 space-y-6">
            <article v-for="(s, i) in services" :id="s.slug" :key="s.id" v-spotlight class="card grid scroll-mt-28 gap-10 overflow-hidden p-7 md:p-10 lg:grid-cols-12 lg:p-14" data-reveal>
                <div class="lg:col-span-5">
                    <div class="flex items-center gap-4">
                        <span class="grid size-14 place-items-center rounded-2xl text-white shadow-[0_14px_40px_-12px_var(--accent)]" style="background: var(--gradient)"><ServiceIcon :name="s.icon" :size="24" /></span>
                        <span class="mono">{{ pad(i + 1) }} / {{ pad(services.length) }}</span>
                    </div>
                    <h2 class="heading mt-8 text-[clamp(2rem,3.6vw,3.2rem)]">{{ s.title }}</h2>
                    <p class="mt-4 text-lg text-text-2">{{ s.tagline }}</p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <Link :href="`/contact?service=${s.slug}`" class="btn btn-primary">Enquire <ArrowUpRight :size="16" /></Link>
                        <span class="text-sm text-muted">
                            <template v-if="s.starting_price">From <span class="font-medium text-text">{{ money(s.starting_price, $page.props.site.currency || 'GBP') }}</span> · </template>{{ s.price_note }}
                        </span>
                    </div>
                </div>
                <div class="lg:col-span-6 lg:col-start-7">
                    <p class="leading-relaxed text-text-2">{{ s.description }}</p>
                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        <li v-for="f in s.features" :key="f" class="flex items-start gap-3 rounded-2xl bg-white/[0.03] p-4 text-[0.9375rem] shadow-[inset_0_0_0_1px_var(--line)]">
                            <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-accent/15 text-accent-hot"><Check :size="12" :stroke-width="3" /></span>
                            {{ f }}
                        </li>
                    </ul>
                </div>
            </article>
        </section>

        <!-- process -->
        <section id="process" data-process class="wrap relative mt-32 scroll-mt-24">
            <div class="max-w-2xl">
                <span class="eyebrow" data-reveal="fade">Process</span>
                <h2 class="heading mt-6 text-[clamp(2.3rem,5vw,4.2rem)]" data-reveal>From first message to <span class="text-gradient">launch day</span></h2>
            </div>
            <div class="relative mt-16">
                <svg class="pointer-events-none absolute inset-x-0 -top-6 hidden h-24 w-full lg:block" viewBox="0 0 1000 100" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="proc-grad" x1="0" x2="1"><stop offset="0" stop-color="var(--accent-hot)" /><stop offset="1" stop-color="var(--accent)" /></linearGradient>
                    </defs>
                    <path d="M 19 50 C 100 -10, 190 110, 274 50 S 450 -10, 528 50 S 700 110, 783 50 S 940 -10, 1000 40" stroke="var(--line-2)" stroke-width="1.5" fill="none" vector-effect="non-scaling-stroke" />
                    <path data-process-path d="M 19 50 C 100 -10, 190 110, 274 50 S 450 -10, 528 50 S 700 110, 783 50 S 940 -10, 1000 40" stroke="url(#proc-grad)" stroke-width="2.5" fill="none" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                </svg>
                <ol class="relative grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(step, i) in steps" :key="step.title" data-step>
                        <span class="relative grid size-12 place-items-center rounded-full bg-surface-2 font-display text-lg font-semibold shadow-[inset_0_0_0_1px_var(--line-2),0_0_0_6px_var(--bg)]">
                            <span class="text-gradient">{{ i + 1 }}</span>
                        </span>
                        <h3 class="title mt-8 text-xl">{{ step.title }}</h3>
                        <p class="mt-3 text-[0.9375rem] leading-relaxed text-text-2">{{ step.body }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- faq + testimonials -->
        <section class="wrap mt-32 grid gap-14 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <span class="eyebrow" data-reveal="fade">FAQ</span>
                <h2 class="heading mt-6 text-[clamp(2.2rem,4.4vw,3.6rem)]" data-reveal>Good questions</h2>
                <p class="mt-5 max-w-sm text-text-2" data-reveal>Can't see yours? <Link href="/contact" class="link">Ask me directly</Link> — I usually reply within a day.</p>
            </div>
            <div class="space-y-3 lg:col-span-7">
                <div v-for="(f, i) in faqs" :key="f.q" class="card overflow-hidden" data-reveal>
                    <button type="button" class="flex w-full items-center justify-between gap-6 p-6 text-left" :aria-expanded="openFaq === i" @click="toggle(i)">
                        <span class="title text-lg">{{ f.q }}</span>
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/5 transition-transform duration-500 ease-out-expo" :class="openFaq === i ? 'rotate-45 bg-white/10' : ''"><Plus :size="16" /></span>
                    </button>
                    <Transition :css="false" @enter="enterFaq" @leave="leaveFaq">
                        <div v-if="openFaq === i" class="overflow-hidden">
                            <p class="px-6 pb-6 leading-relaxed text-text-2">{{ f.a }}</p>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>

        <section v-if="testimonials.length" class="wrap mt-32">
            <div class="grid gap-4 md:grid-cols-3">
                <figure v-for="t in testimonials" :key="t.id" class="card flex flex-col justify-between gap-8 p-7" data-reveal>
                    <div>
                        <StarRating :model-value="t.rating" :size="14" />
                        <blockquote class="mt-5 leading-relaxed">“{{ t.body }}”</blockquote>
                    </div>
                    <figcaption class="text-sm"><span class="font-medium">{{ t.name }}</span> <span class="text-muted">· {{ t.company }}</span></figcaption>
                </figure>
            </div>
        </section>
    </div>
</template>
