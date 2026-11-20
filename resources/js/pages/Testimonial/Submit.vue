<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, Loader2, Send } from 'lucide-vue-next';
import StarRating from '@/components/StarRating.vue';
import Aurora from '@/components/Aurora.vue';
import { gsap, prefersReducedMotion } from '@/lib/motion';

const props = defineProps({ token: String, invite: Object, submitted: Boolean });

const done = ref(props.submitted);
const card = ref(null);
const form = useForm({ rating: 0, body: '', consent: false });
const firstName = computed(() => props.invite.name.split(' ')[0]);

const submit = () => form.post(`/testimonial/${props.token}`, {
    preserveScroll: true,
    onSuccess: () => (done.value = true),
});

onMounted(() => {
    if (!prefersReducedMotion()) gsap.from(card.value, { opacity: 0, y: 40, filter: 'blur(10px)', duration: 1.3, ease: 'expo.out', clearProps: 'filter' });
});
</script>

<template>
    <Head title="Leave a testimonial">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="relative flex flex-1 items-center justify-center overflow-hidden px-5 py-16">
        <Aurora :intensity="0.55" style="top: -5rem" />
        <div ref="card" class="relative w-full max-w-xl rounded-[2rem] bg-surface/85 p-7 shadow-[inset_0_0_0_1px_var(--line-2),0_40px_120px_-40px_rgba(0,0,0,.8)] backdrop-blur-xl sm:p-10">
            <Transition mode="out-in" enter-active-class="transition duration-700 ease-out-expo" enter-from-class="opacity-0 translate-y-4 blur-sm" leave-active-class="transition duration-300" leave-to-class="opacity-0">
                <div v-if="done" key="done" class="py-10 text-center">
                    <span class="mx-auto grid size-20 place-items-center rounded-full text-white shadow-[0_20px_60px_-15px_var(--accent)]" style="background: var(--gradient)"><Check :size="34" :stroke-width="2.5" /></span>
                    <h1 class="heading mt-8 text-4xl">Thank you, {{ firstName }}</h1>
                    <p class="mx-auto mt-3 max-w-sm text-text-2">Your testimonial has been received and will appear on the site once it's been reviewed. It genuinely helps — thank you for taking the time.</p>
                </div>

                <form v-else key="form" @submit.prevent="submit">
                    <div class="flex items-center gap-4">
                        <img v-if="invite.avatar" :src="invite.avatar" :alt="invite.name" class="size-16 rounded-2xl object-cover" />
                        <span v-else class="grid size-16 place-items-center rounded-2xl bg-surface-3 font-display text-2xl font-semibold">{{ invite.name.charAt(0) }}</span>
                        <div>
                            <div class="font-display text-lg font-medium tracking-tight">{{ invite.name }}</div>
                            <div class="text-sm text-muted">{{ [invite.role, invite.company].filter(Boolean).join(' · ') }}</div>
                        </div>
                    </div>

                    <h1 class="heading mt-8 text-[clamp(2rem,4vw,2.6rem)]">How did it go, {{ firstName }}?</h1>
                    <p class="mt-3 text-text-2">A few honest words about working together<template v-if="invite.project"> on <span class="text-text">{{ invite.project }}</span></template>. It'll be shown publicly with your name{{ invite.company ? ' and company' : '' }} once approved.</p>

                    <div class="mt-8">
                        <div class="field-label">Your rating</div>
                        <StarRating v-model="form.rating" interactive :size="30" />
                        <p v-if="form.errors.rating" class="field-error">{{ form.errors.rating }}</p>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-baseline justify-between">
                            <label for="body" class="field-label">Your testimonial</label>
                            <span class="text-xs tabular-nums" :class="form.body.length > 1500 ? 'text-accent-hot' : 'text-faint'">{{ form.body.length }} / 1500</span>
                        </div>
                        <textarea id="body" v-model="form.body" rows="6" class="field resize-y" placeholder="What was the project, how was the process, and how did it turn out?" :aria-invalid="!!form.errors.body" required></textarea>
                        <p v-if="form.errors.body" class="field-error">{{ form.errors.body }}</p>
                    </div>

                    <label class="mt-6 flex items-start gap-3 text-sm leading-relaxed text-text-2">
                        <input v-model="form.consent" type="checkbox" class="mt-1 size-4 shrink-0 accent-[var(--accent)]" required />
                        <span>I agree to my review, name, company{{ invite?.avatar ? ', photo' : '' }} and rating being published on this website. I can ask for it to be removed at any time. <a href="/privacy" target="_blank" class="link text-text">Privacy policy</a></span>
                    </label>
                    <p v-if="form.errors.consent" class="field-error">{{ form.errors.consent }}</p>

                    <button type="submit" class="btn btn-primary btn-lg mt-8 w-full" :disabled="form.processing || !form.rating || form.body.length < 20 || !form.consent">
                        <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                        <Send v-else :size="16" />
                        Submit testimonial
                    </button>
                </form>
            </Transition>
        </div>
    </div>
</template>
