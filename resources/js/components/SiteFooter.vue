<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, ArrowUp, Mail } from 'lucide-vue-next';
import BrandIcon from './BrandIcon.vue';
import Logo from './Logo.vue';
import Aurora from './Aurora.vue';
import { gsap, blurWords, scrollToTop } from '@/lib/motion';

const site = computed(() => usePage().props.site ?? {});
const year = new Date().getFullYear();

// UK Companies Act / E-Commerce Regulations: who you're dealing with.
const businessLine = computed(() => {
    const s = site.value;
    const parts = [];
    if (s.legal_name && s.legal_name !== s.site_name) parts.push(`${s.site_name} is a trading name of ${s.legal_name}`);
    if (s.company_number) parts.push(`Registered in ${s.legal_jurisdiction ?? 'England and Wales'}, company no. ${s.company_number}`);
    if (s.business_address) parts.push(s.business_address.replace(/\s*\n\s*/g, ', '));
    if (s.vat_number) parts.push(`VAT ${s.vat_number}`);
    return parts.join(' · ');
});
const cookieSettings = () => window.dispatchEvent(new CustomEvent('cookie-settings'));
const root = ref(null);
const clock = ref('');

const socials = computed(() => [
    { name: 'discord', label: 'Discord', href: site.value.discord_invite },
    { name: 'github', label: 'GitHub', href: site.value.github_url },
    { name: 'x', label: 'X', href: site.value.twitter_url },
    { name: 'youtube', label: 'YouTube', href: site.value.youtube_url },
    { name: null, label: 'GmodStore', href: site.value.gmodstore_url },
].filter((s) => s.href));

let timer;
let ctx;
const tick = () => {
    clock.value = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit', timeZone: 'Europe/London', timeZoneName: 'short' }).format(new Date());
};

onMounted(() => {
    tick();
    timer = setInterval(tick, 15000);
    ctx = gsap.context(() => {
        blurWords(root.value.querySelector('[data-cta-title]'), { scroll: true });
        gsap.fromTo('[data-cta-card]', { clipPath: 'inset(6% 4% 6% 4% round 48px)' }, {
            clipPath: 'inset(0% 0% 0% 0% round 36px)',
            ease: 'none',
            scrollTrigger: { trigger: '[data-cta-card]', start: 'top bottom', end: 'top 35%', scrub: 0.8 },
        });
        gsap.fromTo('[data-wordmark]', { yPercent: 35, opacity: 0.2 }, {
            yPercent: 0,
            opacity: 1,
            ease: 'none',
            scrollTrigger: { trigger: '[data-wordmark]', start: 'top bottom', end: 'bottom bottom', scrub: 0.6 },
        });
    }, root.value);
});
onBeforeUnmount(() => {
    clearInterval(timer);
    ctx?.revert();
});
</script>

<template>
    <footer ref="root" class="relative z-10 mt-24 overflow-hidden lg:mt-36">
        <!-- CTA -->
        <section class="wrap">
            <div data-cta-card class="relative overflow-hidden rounded-[36px] bg-surface px-6 py-16 shadow-[inset_0_0_0_1px_var(--line)] sm:px-12 lg:px-20 lg:py-24">
                <Aurora :intensity="0.85" follow />
                <div class="relative grid gap-10 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-8">
                        <span class="eyebrow">Next step</span>
                        <h2 data-cta-title class="display mt-6 text-[clamp(2.6rem,6.4vw,5.8rem)] opacity-0">Have a project in mind? Let's make it <span class="text-gradient">real.</span></h2>
                    </div>
                    <div class="lg:col-span-4">
                        <p class="text-text-2">{{ site.response_time }}. Tell me what you're building and I'll come back with a plan, a timeline and a fixed quote.</p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <Link href="/contact" class="btn btn-light btn-lg" v-magnetic="0.2">Start a project <span class="arrow-badge"><ArrowUpRight :size="14" /></span></Link>
                            <a v-if="site.discord_invite" :href="site.discord_invite" target="_blank" rel="noopener" class="btn btn-glass btn-lg"><BrandIcon name="discord" :size="18" /> Discord</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="wrap">
            <div class="grid gap-12 py-20 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <Logo :size="30" />
                    <p class="mt-5 max-w-sm text-[0.9375rem] leading-relaxed text-muted">{{ site.site_description }}</p>
                    <div class="mt-6 flex items-center gap-2 text-sm text-text-2">
                        <span class="pulse-dot" :class="{ 'is-off': !site.is_available }"></span>
                        {{ site.is_available ? site.availability : 'Currently fully booked' }}
                    </div>
                </div>
                <div class="lg:col-span-2 lg:col-start-6">
                    <h3 class="text-sm font-medium text-muted">Studio</h3>
                    <ul class="mt-5 space-y-3 text-[0.9375rem]">
                        <li><Link href="/projects" class="link">Work</Link></li>
                        <li><Link href="/services" class="link">Services</Link></li>
                        <li><Link href="/contact" class="link">Contact</Link></li>
                        <li v-if="$page.props.nav?.store"><Link href="/store" class="link">Store</Link></li>
                        <li><Link href="/pay" class="link">Pay an invoice</Link></li>
                        <li><Link href="/portal" class="link">Client portal</Link></li>
                        <li v-if="$page.props.nav?.status"><Link href="/status" class="link">Status</Link></li>
                    </ul>
                </div>
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-medium text-muted">Elsewhere</h3>
                    <ul class="mt-5 space-y-3 text-[0.9375rem]">
                        <li v-for="s in socials" :key="s.label">
                            <a :href="s.href" target="_blank" rel="noopener" class="group inline-flex items-center gap-2">
                                <BrandIcon v-if="s.name" :name="s.name" :size="14" class="text-muted transition-colors group-hover:text-accent-hot" />
                                <span class="link">{{ s.label }}</span>
                            </a>
                        </li>
                        <li v-if="!socials.length" class="text-faint">Add socials in Settings</li>
                    </ul>
                </div>
                <div class="lg:col-span-3">
                    <h3 class="text-sm font-medium text-muted">Get in touch</h3>
                    <a v-if="site.contact_email" :href="`mailto:${site.contact_email}`" class="mt-5 inline-flex items-center gap-2 text-[0.9375rem]"><Mail :size="15" class="text-muted" /><span class="link">{{ site.contact_email }}</span></a>
                    <p v-if="site.discord_handle" class="mt-3 text-[0.9375rem] text-text-2">Discord · {{ site.discord_handle }}</p>
                    <p class="mono mt-5">{{ site.location }} · {{ clock }}</p>
                </div>
            </div>
        </div>

        <!-- Wordmark fading into the floor -->
        <div class="pointer-events-none select-none overflow-hidden" aria-hidden="true">
            <div data-wordmark class="display whitespace-nowrap text-center text-[15.5vw] leading-[0.78] tracking-[-0.06em]" style="background: linear-gradient(180deg, rgb(255 255 255 / 0.12), rgb(255 255 255 / 0)); -webkit-background-clip: text; background-clip: text; color: transparent;">
                SiedeStudios
            </div>
        </div>

        <div class="wrap flex flex-col gap-4 border-t border-line py-6 text-[0.8125rem] text-muted lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1.5">
                <p>© {{ year }} {{ site.site_name }}. Crafted in the {{ site.location === 'United Kingdom' ? 'UK' : site.location }}.</p>
                <p v-if="businessLine" class="text-xs text-faint">{{ businessLine }}</p>
            </div>
            <nav class="flex flex-wrap gap-x-5 gap-y-2" aria-label="Legal">
                <Link href="/privacy" class="hover:text-text">Privacy</Link>
                <Link href="/cookies" class="hover:text-text">Cookies</Link>
                <Link href="/terms" class="hover:text-text">Terms</Link>
                <button v-if="site.google_analytics_id" type="button" class="hover:text-text" @click="cookieSettings">Cookie settings</button>
            </nav>
            <button type="button" class="group inline-flex items-center gap-2 hover:text-text" @click="scrollToTop(false)">
                Back to top <ArrowUp :size="14" class="transition-transform duration-300 group-hover:-translate-y-0.5" />
            </button>
        </div>
    </footer>
</template>
