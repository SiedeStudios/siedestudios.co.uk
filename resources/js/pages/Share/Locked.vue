<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, Loader2, ArrowRight } from 'lucide-vue-next';

const props = defineProps({ slug: String, title: String });
const form = useForm({ password: '' });
const submit = () => form.post(`/s/${props.slug}/unlock`, { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Protected snippet">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="flex flex-1 items-center justify-center px-5 py-16">
        <form class="card w-full max-w-sm p-8 text-center" @submit.prevent="submit">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl text-white shadow-[0_14px_40px_-12px_var(--accent)]" style="background: var(--gradient)"><Lock :size="22" /></span>
            <h1 class="heading mt-6 text-2xl">This snippet is protected</h1>
            <p class="mt-2 text-sm text-text-2">{{ title ? `"${title}" requires` : 'Enter' }} a password to view.</p>
            <label for="password" class="sr-only">Password</label>
            <input id="password" v-model="form.password" type="password" class="field mt-6 text-center" placeholder="Password" autocomplete="off" autofocus :aria-invalid="!!form.errors.password" required />
            <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
            <button type="submit" class="btn btn-primary mt-4 w-full" :disabled="form.processing">
                <Loader2 v-if="form.processing" :size="16" class="animate-spin" /> Unlock <ArrowRight v-if="!form.processing" :size="16" />
            </button>
        </form>
    </div>
</template>
