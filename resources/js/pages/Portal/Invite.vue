<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { LinkIcon, ShieldCheck } from 'lucide-vue-next';
import Aurora from '@/components/Aurora.vue';
import LogoMark from '@/components/LogoMark.vue';
import BrandIcon from '@/components/BrandIcon.vue';

defineProps({ client: Object, configured: Boolean });
</script>

<template>
    <Head title="Join the client portal">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="relative flex flex-1 items-center justify-center overflow-hidden px-5 py-16">
        <Aurora :intensity="0.6" style="top: -5rem" />
        <div class="relative w-full max-w-md rounded-[2rem] bg-surface/85 p-8 text-center shadow-[inset_0_0_0_1px_var(--line-2),0_40px_120px_-40px_rgba(0,0,0,.8)] backdrop-blur-xl sm:p-10">
            <LogoMark :size="52" tile class="mx-auto" />
            <template v-if="client">
                <h1 class="heading mt-7 text-3xl">Welcome{{ client.name ? `, ${client.name.split(' ')[0]}` : '' }}</h1>
                <p class="mt-3 text-text-2">You've been invited to the client portal{{ client.company ? ` for ${client.company}` : '' }}. Sign in with Discord to link your account — you'll use it to sign in from now on.</p>
                <a v-if="configured" href="/auth/discord?for=portal" class="btn btn-lg mt-8 w-full bg-[#5865F2] text-white shadow-[0_14px_40px_-12px_#5865F2] hover:bg-[#4752c4]"><BrandIcon name="discord" :size="20" /> Link with Discord</a>
                <p class="mt-5 flex items-center justify-center gap-1.5 text-xs text-muted"><ShieldCheck :size="13" /> This link works once and expires after 14 days.</p>
            </template>
            <template v-else>
                <span class="mx-auto mt-7 grid size-12 place-items-center rounded-2xl bg-white/5 text-muted"><LinkIcon :size="20" /></span>
                <h1 class="heading mt-5 text-3xl">Link expired</h1>
                <p class="mt-3 text-text-2">This invite has already been used or has expired. If you've joined before, just sign in.</p>
                <div class="mt-8 flex flex-col gap-2">
                    <Link href="/portal/login" class="btn btn-light w-full">Sign in</Link>
                    <Link href="/contact" class="btn btn-glass w-full">Ask for a new link</Link>
                </div>
            </template>
        </div>
    </div>
</template>
