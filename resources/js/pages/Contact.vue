<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, Mail, Clock, MapPin, Check, Loader2 } from 'lucide-vue-next';
import BrandIcon from '@/components/BrandIcon.vue';
import Aurora from '@/components/Aurora.vue';
import { useMotion } from '@/composables/useMotion';
import { blurWords, gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({ services: Array, budgets: Array, preselected: Number });

const site = computed(() => usePage().props.site ?? {});
const root = ref(null);
const sent = ref(false);

const form = useForm({
    name: '',
    email: '',
    discord: '',
    service_id: props.preselected ?? '',
    budget: '',
    body: '',
    website: '',
});

const submit = () => {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            sent.value = true;
            form.reset();
        },
    });
};

useMotion(root, (el) => {
    blurWords(el.querySelector('[data-title]'), { delay: 0.15 });
    if (prefersReducedMotion()) return;
    gsap.from('[data-fade]', { opacity: 0, y: 16, filter: 'blur(8px)', duration: 1.1, stagger: 0.07, delay: 0.4, clearProps: 'filter' });
    gsap.from('[data-form-card]', { opacity: 0, y: 40, scale: 0.98, duration: 1.4, delay: 0.3 });
});
</script>

<template>
    <Head title="Contact" />
    <section ref="root" class="relative overflow-hidden pb-10 pt-36 lg:pt-44">
        <Aurora :intensity="0.6" />
        <div class="wrap relative grid gap-14 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <span data-fade class="eyebrow">Contact</span>
                <h1 data-title class="display mt-7 text-[clamp(2.8rem,5.6vw,5.2rem)] opacity-0">Let's build something <span class="text-gradient">good.</span></h1>
                <p data-fade class="lede mt-7 max-w-md">Tell me about your project — what it is, who it's for and when you need it. I'll reply with next steps and an honest estimate.</p>

                <div class="mt-12 space-y-3">
                    <a v-if="site.contact_email" data-fade :href="`mailto:${site.contact_email}`" class="card group flex items-center gap-4 p-5 transition-colors hover:bg-white/[0.06]">
                        <span class="grid size-11 place-items-center rounded-xl bg-white/5"><Mail :size="18" /></span>
                        <span class="flex-1"><span class="block text-sm text-muted">Email</span><span class="font-medium">{{ site.contact_email }}</span></span>
                        <ArrowUpRight :size="16" class="text-muted transition-transform duration-500 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                    </a>
                    <a v-if="site.discord_invite || site.discord_handle" data-fade :href="site.discord_invite || '#'" target="_blank" rel="noopener" class="card group flex items-center gap-4 p-5 transition-colors hover:bg-white/[0.06]">
                        <span class="grid size-11 place-items-center rounded-xl bg-[#5865F2]/20 text-[#8f99ff]"><BrandIcon name="discord" :size="18" /></span>
                        <span class="flex-1"><span class="block text-sm text-muted">Discord</span><span class="font-medium">{{ site.discord_handle || 'Join the server' }}</span></span>
                        <ArrowUpRight :size="16" class="text-muted transition-transform duration-500 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                    </a>
                    <div data-fade class="flex flex-wrap gap-x-6 gap-y-2 px-1 pt-3 text-sm text-text-2">
                        <span class="inline-flex items-center gap-2"><Clock :size="15" class="text-muted" /> {{ site.response_time }}</span>
                        <span class="inline-flex items-center gap-2"><MapPin :size="15" class="text-muted" /> {{ site.location }}</span>
                        <span class="inline-flex items-center gap-2"><span class="pulse-dot" :class="{ 'is-off': !site.is_available }"></span> {{ site.is_available ? site.availability : 'Fully booked' }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div data-form-card class="relative overflow-hidden rounded-[2rem] bg-surface/80 p-6 shadow-[inset_0_0_0_1px_var(--line-2),0_40px_120px_-40px_rgba(0,0,0,.8)] backdrop-blur-xl sm:p-10">
                    <Transition mode="out-in" enter-active-class="transition duration-700 ease-out-expo" enter-from-class="opacity-0 translate-y-4 blur-sm" leave-active-class="transition duration-300" leave-to-class="opacity-0">
                        <div v-if="sent" key="sent" class="flex min-h-[32rem] flex-col items-center justify-center text-center">
                            <span class="grid size-20 place-items-center rounded-full text-white shadow-[0_20px_60px_-15px_var(--accent)]" style="background: var(--gradient)"><Check :size="34" :stroke-width="2.5" /></span>
                            <h2 class="heading mt-8 text-4xl">Message sent</h2>
                            <p class="mt-3 max-w-sm text-text-2">Thanks for reaching out. {{ site.response_time }} — keep an eye on your inbox{{ site.discord_handle ? ' or Discord' : '' }}.</p>
                            <button type="button" class="btn btn-glass mt-8" @click="sent = false">Send another</button>
                        </div>

                        <form v-else key="form" class="space-y-6" novalidate @submit.prevent="submit">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="field-label">Your name</label>
                                    <input id="name" v-model="form.name" type="text" class="field" autocomplete="name" placeholder="Jane Smith" :aria-invalid="!!form.errors.name" required />
                                    <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label for="email" class="field-label">Email</label>
                                    <input id="email" v-model="form.email" type="email" class="field" autocomplete="email" placeholder="jane@company.com" :aria-invalid="!!form.errors.email" required />
                                    <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                                </div>
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="service" class="field-label">Service</label>
                                    <select id="service" v-model="form.service_id" class="field" :aria-invalid="!!form.errors.service_id">
                                        <option value="">General enquiry</option>
                                        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.title }}</option>
                                    </select>
                                    <p v-if="form.errors.service_id" class="field-error">{{ form.errors.service_id }}</p>
                                </div>
                                <div>
                                    <label for="discord" class="field-label">Discord <span class="text-faint">(optional)</span></label>
                                    <input id="discord" v-model="form.discord" type="text" class="field" placeholder="username" />
                                </div>
                            </div>
                            <fieldset>
                                <legend class="field-label">Budget</legend>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="b in budgets" :key="b" class="cursor-pointer">
                                        <input v-model="form.budget" type="radio" name="budget" :value="b" class="peer sr-only" />
                                        <span class="inline-flex h-10 items-center rounded-full px-4 text-sm text-text-2 shadow-[inset_0_0_0_1px_var(--line-2)] transition-all duration-300 hover:text-text peer-checked:bg-text peer-checked:text-bg peer-focus-visible:ring-2 peer-focus-visible:ring-accent">{{ b }}</span>
                                    </label>
                                </div>
                                <p v-if="form.errors.budget" class="field-error">{{ form.errors.budget }}</p>
                            </fieldset>
                            <div>
                                <div class="flex items-baseline justify-between">
                                    <label for="body" class="field-label">Project details</label>
                                    <span class="text-xs tabular-nums" :class="form.body.length > 5000 ? 'text-accent-hot' : 'text-faint'">{{ form.body.length }} / 5000</span>
                                </div>
                                <textarea id="body" v-model="form.body" rows="6" class="field resize-y" placeholder="What are you building, what do you need from me, and when would you like it live?" :aria-invalid="!!form.errors.body" data-lenis-prevent></textarea>
                                <p v-if="form.errors.body" class="field-error">{{ form.errors.body }}</p>
                            </div>
                            <div class="absolute -left-[9999px]" aria-hidden="true">
                                <label for="website">Website</label>
                                <input id="website" v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                                <p class="max-w-sm text-sm text-muted">We'll only use your details to reply about your enquiry. See our <Link href="/privacy" class="link text-text-2">privacy policy</Link>.</p>
                                <button type="submit" class="btn btn-primary btn-lg" :disabled="form.processing">
                                    <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                                    {{ form.processing ? 'Sending…' : 'Send message' }}
                                    <ArrowUpRight v-if="!form.processing" :size="16" />
                                </button>
                            </div>
                        </form>
                    </Transition>
                </div>
            </div>
        </div>
    </section>
</template>
