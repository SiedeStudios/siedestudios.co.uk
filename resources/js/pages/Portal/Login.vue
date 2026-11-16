<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { AlertTriangle, FolderKanban, Receipt, Paperclip, ShieldCheck } from 'lucide-vue-next';
import Aurora from '@/components/Aurora.vue';
import LogoMark from '@/components/LogoMark.vue';
import BrandIcon from '@/components/BrandIcon.vue';

defineProps({ configured: Boolean });
const error = computed(() => usePage().props.flash?.error);
</script>

<template>
    <Head title="Client portal">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="relative flex flex-1 items-center justify-center overflow-hidden px-5 py-16">
        <Aurora :intensity="0.6" style="top: -5rem" />
        <div class="relative w-full max-w-md rounded-[2rem] bg-surface/85 p-8 text-center shadow-[inset_0_0_0_1px_var(--line-2),0_40px_120px_-40px_rgba(0,0,0,.8)] backdrop-blur-xl sm:p-10">
            <LogoMark :size="52" tile class="mx-auto" />
            <h1 class="heading mt-7 text-3xl">Client portal</h1>
            <p class="mt-2 text-text-2">Follow your project, pay invoices and share files.</p>

            <ul class="mt-7 grid grid-cols-3 gap-2 text-xs text-text-2">
                <li class="rounded-2xl bg-white/[0.03] p-3 shadow-[inset_0_0_0_1px_var(--line)]"><FolderKanban :size="17" class="mx-auto mb-2 text-accent-hot" />Progress</li>
                <li class="rounded-2xl bg-white/[0.03] p-3 shadow-[inset_0_0_0_1px_var(--line)]"><Receipt :size="17" class="mx-auto mb-2 text-accent-hot" />Invoices</li>
                <li class="rounded-2xl bg-white/[0.03] p-3 shadow-[inset_0_0_0_1px_var(--line)]"><Paperclip :size="17" class="mx-auto mb-2 text-accent-hot" />Files</li>
            </ul>

            <div v-if="error" class="mt-6 flex items-start gap-2.5 rounded-xl bg-accent/10 p-3 text-left text-sm text-accent-hot"><AlertTriangle :size="16" class="mt-0.5 shrink-0" /> {{ error }}</div>

            <a v-if="configured" href="/auth/discord?for=portal" class="btn btn-lg mt-7 w-full bg-[#5865F2] text-white shadow-[0_14px_40px_-12px_#5865F2] hover:bg-[#4752c4]">
                <BrandIcon name="discord" :size="20" /> Continue with Discord
            </a>
            <p v-else class="mt-7 rounded-xl bg-amber-400/10 p-4 text-left text-sm text-amber-200">Discord sign-in isn't configured yet.</p>

            <p class="mt-6 text-sm text-muted">First time here? Open the invite link we sent you. <Link href="/contact" class="link text-text-2">Need one?</Link></p>
            <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-muted"><ShieldCheck :size="13" /> We only see your public Discord profile.</p>
        </div>
    </div>
</template>
