<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { ArrowUpRight } from 'lucide-vue-next';
import Logo from './Logo.vue';
import BrandIcon from './BrandIcon.vue';
import { gsap, ScrollTrigger, getLenis } from '@/lib/motion';

const page = usePage();
const site = computed(() => page.props.site ?? {});
const open = ref(false);
const hidden = ref(false);
const scrolled = ref(false);
const overlay = ref(null);
const pill = ref(null);
const indicator = ref(null);

const nav = computed(() => [
    { label: 'Home', href: '/', match: (u) => u === '/' },
    { label: 'Work', href: '/projects', match: (u) => u.startsWith('/projects') },
    { label: 'Services', href: '/services', match: (u) => u.startsWith('/services') },
    page.props.nav?.store && { label: 'Store', href: '/store', match: (u) => u.startsWith('/store') },
    { label: 'Contact', href: '/contact', match: (u) => u.startsWith('/contact') },
].filter(Boolean));
const path = computed(() => page.url.split('?')[0]);
const activeIndex = computed(() => nav.value.findIndex((n) => n.match(path.value)));

/* Sliding pill indicator that follows hover and settles on the active page. */
const moveIndicator = (index, instant = false) => {
    const links = pill.value?.querySelectorAll('[data-nav-link]');
    const target = links?.[index];
    if (!indicator.value) return;
    if (!target) {
        gsap.to(indicator.value, { opacity: 0, duration: 0.3 });
        return;
    }
    gsap.to(indicator.value, {
        x: target.offsetLeft,
        width: target.offsetWidth,
        opacity: 1,
        duration: instant ? 0 : 0.55,
        ease: 'expo.out',
    });
};
watch(activeIndex, (i) => nextTick(() => moveIndicator(i)));

let trigger;
onMounted(() => {
    document.fonts?.ready.then(() => moveIndicator(activeIndex.value, true));
    moveIndicator(activeIndex.value, true);
    trigger = ScrollTrigger.create({
        start: 0,
        end: 'max',
        onUpdate: (self) => {
            scrolled.value = self.scroll() > 30;
            if (!open.value) hidden.value = self.direction === 1 && self.scroll() > 400;
        },
    });
});
onBeforeUnmount(() => {
    trigger?.kill();
    lockScroll(false);
});

let tl;
const lockScroll = (locked) => {
    document.documentElement.classList.toggle('menu-open', locked);
    locked ? getLenis()?.stop() : getLenis()?.start();
};

watch(open, (value) => {
    lockScroll(value);
    tl?.kill();
    const el = overlay.value;
    if (!el) return;
    if (value) {
        tl = gsap.timeline()
            .set(el, { display: 'block' })
            .fromTo(el, { clipPath: 'inset(0% 0% 100% 0% round 0 0 40px 40px)' }, { clipPath: 'inset(0% 0% 0% 0% round 0 0 0px 0px)', duration: 0.9, ease: 'expo.inOut' })
            .from(el.querySelectorAll('[data-menu-item]'), { y: 40, opacity: 0, filter: 'blur(8px)', duration: 0.9, stagger: 0.05 }, '-=0.45')
            .from(el.querySelectorAll('[data-menu-fade]'), { opacity: 0, y: 12, duration: 0.6, stagger: 0.05 }, '-=0.6');
    } else {
        tl = gsap.timeline()
            .to(el, { clipPath: 'inset(0% 0% 100% 0% round 0 0 40px 40px)', duration: 0.7, ease: 'expo.inOut' })
            .set(el, { display: 'none' });
    }
});

router.on('navigate', () => {
    open.value = false;
    lockScroll(false);
    hidden.value = false;
});
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50 transition-transform duration-700 ease-out-expo" :class="hidden ? '-translate-y-[120%]' : 'translate-y-0'">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-28 bg-gradient-to-b from-bg via-bg/70 to-transparent transition-opacity duration-500" :class="scrolled ? 'opacity-100' : 'opacity-0'"></div>

        <div class="wrap grid h-20 grid-cols-[1fr_auto] items-center gap-4 md:grid-cols-[1fr_auto_1fr] lg:h-24">
            <Link href="/" class="relative z-[60] justify-self-start text-[15px]" aria-label="Home" @click="open = false">
                <Logo :size="30" />
            </Link>

            <nav ref="pill" class="relative hidden items-center rounded-full bg-white/[0.04] p-1.5 shadow-[inset_0_0_0_1px_var(--line)] backdrop-blur-xl md:flex" aria-label="Primary" @mouseleave="moveIndicator(activeIndex)">
                <span ref="indicator" class="absolute left-0 top-1.5 h-[calc(100%-12px)] rounded-full bg-white/[0.09] opacity-0 shadow-[inset_0_1px_0_rgb(255_255_255/0.06)]" aria-hidden="true"></span>
                <Link
                    v-for="(item, i) in nav"
                    :key="item.href"
                    :href="item.href"
                    data-nav-link
                    class="relative z-10 rounded-full px-4 py-2 text-[0.875rem] font-medium transition-colors duration-300"
                    :class="activeIndex === i ? 'text-text' : 'text-muted hover:text-text'"
                    @mouseenter="moveIndicator(i)"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="flex items-center justify-self-end gap-3">
                <Link href="/contact" class="btn btn-light btn-sm hidden sm:inline-flex" v-magnetic="0.15">
                    Start a project
                </Link>
                <button
                    type="button"
                    class="btn btn-glass btn-icon relative z-[60] h-11 md:hidden"
                    :aria-expanded="open"
                    aria-controls="mobile-menu"
                    :aria-label="open ? 'Close menu' : 'Open menu'"
                    @click="open = !open"
                >
                    <span class="relative block h-2.5 w-5">
                        <span class="absolute left-0 top-0 h-[1.5px] w-full rounded bg-text transition-transform duration-500 ease-out-expo" :class="open ? 'translate-y-[4.5px] rotate-45' : ''"></span>
                        <span class="absolute bottom-0 left-0 h-[1.5px] w-full rounded bg-text transition-transform duration-500 ease-out-expo" :class="open ? '-translate-y-[4.5px] -rotate-45' : ''"></span>
                    </span>
                </button>
            </div>
        </div>

    </header>

    <!-- Lives outside the header: the header is transformed (hide-on-scroll), which would trap a fixed overlay inside it. -->
    <Teleport to="body">
        <div id="mobile-menu" ref="overlay" class="fixed inset-0 z-40 hidden overflow-y-auto overscroll-contain bg-bg md:hidden">
                <div class="absolute -right-1/3 top-1/3 size-[80vw] rounded-full bg-accent/30 blur-[100px]" aria-hidden="true"></div>
                <div class="wrap relative flex h-full flex-col justify-between pb-10 pt-28">
                    <nav class="flex flex-col gap-1" aria-label="Mobile">
                        <Link v-for="(item, i) in nav" :key="item.href" :href="item.href" data-menu-item class="flex items-baseline justify-between border-b border-line py-4">
                            <span class="display text-[2.75rem]" :class="activeIndex === i ? 'text-gradient' : ''">{{ item.label }}</span>
                            <span class="mono">0{{ i + 1 }}</span>
                        </Link>
                        <Link href="/pay" data-menu-item class="flex items-baseline justify-between border-b border-line py-4 text-text-2">Pay an invoice <ArrowUpRight :size="16" /></Link>
                        <Link href="/portal" data-menu-item class="flex items-baseline justify-between py-4 text-text-2">Client portal <ArrowUpRight :size="16" /></Link>
                    </nav>
                    <div class="space-y-5">
                        <a v-if="site.contact_email" data-menu-fade :href="`mailto:${site.contact_email}`" class="block text-lg">{{ site.contact_email }}</a>
                        <div data-menu-fade class="flex gap-3">
                            <a v-if="site.discord_invite" :href="site.discord_invite" target="_blank" rel="noopener" class="btn btn-glass btn-icon" aria-label="Discord"><BrandIcon name="discord" :size="18" /></a>
                            <a v-if="site.github_url" :href="site.github_url" target="_blank" rel="noopener" class="btn btn-glass btn-icon" aria-label="GitHub"><BrandIcon name="github" :size="18" /></a>
                            <a v-if="site.twitter_url" :href="site.twitter_url" target="_blank" rel="noopener" class="btn btn-glass btn-icon" aria-label="X"><BrandIcon name="x" :size="16" /></a>
                        </div>
                        <div data-menu-fade class="flex items-center gap-2 text-sm text-text-2">
                            <span class="pulse-dot" :class="{ 'is-off': !site.is_available }"></span>
                            {{ site.is_available ? site.availability : 'Fully booked' }}
                        </div>
                    </div>
                </div>
            </div>
    </Teleport>
</template>
