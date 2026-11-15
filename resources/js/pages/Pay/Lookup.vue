<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, Lock, Loader2 } from 'lucide-vue-next';
import Aurora from '@/components/Aurora.vue';
import BrandIcon from '@/components/BrandIcon.vue';

const form = useForm({ reference: '' });
const submit = () => form.post('/pay', { preserveScroll: true });
</script>

<template>
    <Head title="Pay an invoice" />
    <section class="relative flex min-h-[88svh] items-center overflow-hidden pt-28">
        <Aurora :intensity="0.7" />
        <div class="wrap relative">
            <div class="mx-auto max-w-xl text-center">
                <span class="eyebrow">Payments</span>
                <h1 class="display mt-7 text-[clamp(2.8rem,6vw,5rem)]">Pay an <span class="text-gradient">invoice</span></h1>
                <p class="lede mx-auto mt-5 max-w-md">Enter the invoice ID from your email to view it and pay securely with PayPal.</p>

                <form class="mt-10 rounded-[1.75rem] bg-surface/80 p-3 shadow-[inset_0_0_0_1px_var(--line-2),0_30px_80px_-30px_rgba(0,0,0,.8)] backdrop-blur-xl" @submit.prevent="submit">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <label for="reference" class="sr-only">Invoice ID</label>
                        <input
                            id="reference"
                            v-model="form.reference"
                            type="text"
                            class="field h-14 flex-1 border-transparent bg-transparent text-center font-mono text-lg uppercase tracking-[0.12em] focus:bg-white/[0.03] sm:text-left"
                            placeholder="SS-2611-XXXXXX"
                            autocomplete="off"
                            spellcheck="false"
                            :aria-invalid="!!form.errors.reference"
                            required
                        />
                        <button type="submit" class="btn btn-primary btn-lg" :disabled="form.processing || !form.reference">
                            <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                            View invoice <ArrowRight v-if="!form.processing" :size="16" />
                        </button>
                    </div>
                </form>
                <p v-if="form.errors.reference" class="field-error mt-4">{{ form.errors.reference }}</p>

                <div class="mt-10 flex items-center justify-center gap-6 text-sm text-muted">
                    <span class="inline-flex items-center gap-2"><Lock :size="14" /> Secure checkout</span>
                    <span class="inline-flex items-center gap-2"><BrandIcon name="paypal" :size="14" /> Powered by PayPal</span>
                </div>
            </div>
        </div>
    </section>
</template>
