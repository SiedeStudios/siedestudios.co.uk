<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ShieldCheck, AlertTriangle } from 'lucide-vue-next';
import BrandIcon from '@/components/BrandIcon.vue';
import LogoMark from '@/components/LogoMark.vue';
import Aurora from '@/components/Aurora.vue';

defineProps({ configured: Boolean });
const error = computed(() => usePage().props.flash?.error);
</script>

<template>
    <Head title="Admin sign in">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="relative flex flex-1 items-center justify-center overflow-hidden px-5 py-16">
        <Aurora :intensity="0.6" style="top: -5rem" />
        <div class="relative w-full max-w-sm rounded-[2rem] bg-surface/85 p-8 text-center shadow-[inset_0_0_0_1px_var(--line-2),0_40px_120px_-40px_rgba(0,0,0,.8)] backdrop-blur-xl">
            <LogoMark :size="52" tile class="mx-auto" />
            <h1 class="heading mt-7 text-3xl">Admin panel</h1>
            <p class="mt-2 text-sm text-text-2">Sign in with an authorised Discord account.</p>

            <div v-if="error" class="mt-6 flex items-start gap-2.5 rounded-xl bg-accent/10 p-3 text-left text-sm text-accent-hot">
                <AlertTriangle :size="16" class="mt-0.5 shrink-0" /> {{ error }}
            </div>

            <a v-if="configured" href="/auth/discord" class="btn btn-lg mt-8 w-full bg-[#5865F2] text-white shadow-[0_14px_40px_-12px_#5865F2] hover:bg-[#4752c4]">
                <BrandIcon name="discord" :size="20" /> Continue with Discord
            </a>
            <div v-else class="mt-8 rounded-xl bg-amber-400/10 p-4 text-left text-sm text-amber-200">
                Discord login isn't configured yet. Set <code class="font-mono text-xs">DISCORD_CLIENT_ID</code>, <code class="font-mono text-xs">DISCORD_CLIENT_SECRET</code> and <code class="font-mono text-xs">ADMIN_DISCORD_IDS</code> in your <code class="font-mono text-xs">.env</code>.
            </div>

            <p class="mt-6 flex items-center justify-center gap-1.5 text-xs text-muted"><ShieldCheck :size="13" /> Only allow-listed Discord IDs can access this area.</p>
        </div>
    </div>
</template>
