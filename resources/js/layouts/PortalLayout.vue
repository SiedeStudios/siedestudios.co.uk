<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, LayoutGrid, Mail } from 'lucide-vue-next';
import Logo from '@/components/Logo.vue';
import FlashToast from '@/components/FlashToast.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const site = computed(() => page.props.site ?? {});
</script>

<template>
    <div class="relative flex min-h-svh flex-col bg-bg">
        <header class="sticky top-0 z-30 border-b border-line bg-bg/80 backdrop-blur-xl">
            <div class="wrap flex h-16 items-center gap-4">
                <Link href="/portal" class="text-[14px]" aria-label="Portal home"><Logo :size="26" /></Link>
                <span class="hidden rounded-full bg-white/[0.06] px-2.5 py-1 text-xs text-text-2 shadow-[inset_0_0_0_1px_var(--line)] sm:inline">Client portal</span>
                <nav class="ml-4 hidden items-center gap-1 md:flex" aria-label="Portal">
                    <Link href="/portal" class="flex h-9 items-center gap-2 rounded-full px-3.5 text-sm text-text-2 hover:bg-white/5 hover:text-text" :class="page.url === '/portal' && 'bg-white/[0.07] text-text'"><LayoutGrid :size="15" /> Overview</Link>
                    <Link href="/contact" class="flex h-9 items-center gap-2 rounded-full px-3.5 text-sm text-text-2 hover:bg-white/5 hover:text-text"><Mail :size="15" /> Contact</Link>
                </nav>
                <div class="ml-auto flex items-center gap-3">
                    <div v-if="user" class="flex items-center gap-2.5">
                        <img v-if="user.avatar" :src="user.avatar" alt="" class="size-8 rounded-full" />
                        <span v-else class="grid size-8 place-items-center rounded-full bg-surface-2 text-sm">{{ user.name?.[0] }}</span>
                        <span class="hidden text-sm leading-tight sm:block"><span class="block font-medium">{{ user.name }}</span><span class="block text-xs text-muted">{{ user.client }}</span></span>
                    </div>
                    <Link href="/logout" method="post" :data="{ to: 'portal' }" as="button" class="grid size-9 place-items-center rounded-full text-muted hover:bg-white/5 hover:text-text" aria-label="Sign out" title="Sign out"><LogOut :size="17" /></Link>
                </div>
            </div>
        </header>
        <main class="relative z-10 flex-1"><slot /></main>
        <footer class="wrap flex flex-wrap items-center justify-between gap-3 py-8 text-xs text-faint">
            <span>© {{ new Date().getFullYear() }} {{ site.site_name }}</span>
            <a v-if="site.contact_email" :href="`mailto:${site.contact_email}`" class="hover:text-text-2">{{ site.contact_email }}</a>
        </footer>
        <div class="grain" aria-hidden="true"></div>
        <FlashToast />
    </div>
</template>
