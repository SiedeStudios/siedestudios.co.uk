<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Cookie } from 'lucide-vue-next';

/**
 * PECR-compliant consent for analytics. Shown only when Google Analytics is
 * configured; nothing non-essential loads until "Accept" is pressed, and
 * "Reject" is just as easy. Reopen via window event "cookie-settings".
 */
const site = computed(() => usePage().props.site ?? {});
const gaId = computed(() => site.value.google_analytics_id);
const open = ref(false);
const COOKIE = 'cookie_consent';
const MAX_AGE = 60 * 60 * 24 * 182; // ~6 months, then we ask again

const read = () => document.cookie.split('; ').find((c) => c.startsWith(`${COOKIE}=`))?.split('=')[1] ?? null;
const write = (value) => {
    document.cookie = `${COOKIE}=${value}; Max-Age=${MAX_AGE}; Path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
};

const loadAnalytics = () => {
    if (!gaId.value || window.__gaLoaded) return;
    window.__gaLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', gaId.value, { anonymize_ip: true });
    const s = document.createElement('script');
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(gaId.value)}`;
    document.head.appendChild(s);
};

const removeAnalyticsCookies = () => {
    const domain = location.hostname.replace(/^www\./, '');
    document.cookie.split('; ').map((c) => c.split('=')[0]).filter((n) => n === '_ga' || n.startsWith('_ga_') || n === '_gid')
        .forEach((n) => ['', `; Domain=.${domain}`, `; Domain=${domain}`].forEach((d) => (document.cookie = `${n}=; Max-Age=0; Path=/${d}`)));
};

const accept = () => {
    write('accepted');
    open.value = false;
    loadAnalytics();
};
const reject = () => {
    const wasLoaded = window.__gaLoaded;
    write('rejected');
    open.value = false;
    removeAnalyticsCookies();
    if (wasLoaded) location.reload(); // unload the already-running script
};

const reopen = () => gaId.value && (open.value = true);

onMounted(() => {
    window.addEventListener('cookie-settings', reopen);
    if (!gaId.value) return;
    const choice = read();
    if (choice === 'accepted') loadAnalytics();
    else if (choice !== 'rejected') open.value = true;
});
onBeforeUnmount(() => window.removeEventListener('cookie-settings', reopen));
</script>

<template>
    <Transition enter-from-class="opacity-0 translate-y-6" leave-to-class="opacity-0 translate-y-6" enter-active-class="transition duration-700 ease-out-expo" leave-active-class="transition duration-300">
        <section v-if="open" class="fixed inset-x-4 bottom-4 z-[85] mx-auto max-w-xl rounded-[1.5rem] bg-surface-2/95 p-5 shadow-[inset_0_0_0_1px_var(--line-2),0_30px_80px_-20px_#000] backdrop-blur-xl sm:p-6" role="dialog" aria-labelledby="cookie-title" aria-live="polite">
            <div class="flex items-start gap-3">
                <Cookie :size="20" class="mt-0.5 shrink-0 text-accent-hot" />
                <div>
                    <h2 id="cookie-title" class="font-medium">Analytics cookies</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-text-2">We'd like to use Google Analytics cookies to see how the site is used and improve it. They're off unless you accept. Strictly necessary cookies are always on. <Link href="/cookies" class="link text-text">Cookie policy</Link></p>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-2">
                <button type="button" class="btn btn-glass" @click="reject">Reject</button>
                <button type="button" class="btn btn-light" @click="accept">Accept</button>
            </div>
        </section>
    </Transition>
</template>
