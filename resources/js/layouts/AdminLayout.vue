<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, FolderKanban, Tags, Layers, MessageSquareQuote, Receipt, CreditCard,
    FileCode2, Inbox, Settings, LogOut, ExternalLink, Menu, X, Users, ShoppingBag, Activity,
} from 'lucide-vue-next';
import Logo from '@/components/Logo.vue';
import FlashToast from '@/components/FlashToast.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const counts = computed(() => page.props.admin ?? {});
const open = ref(false);

const groups = computed(() => [
    { label: null, items: [{ label: 'Dashboard', href: '/admin', icon: LayoutDashboard, exact: true }] },
    {
        label: 'Content',
        items: [
            { label: 'Projects', href: '/admin/projects', icon: FolderKanban },
            { label: 'Categories', href: '/admin/categories', icon: Tags },
            { label: 'Services', href: '/admin/services', icon: Layers },
            { label: 'Testimonials', href: '/admin/testimonials', icon: MessageSquareQuote, badge: counts.value.pending_testimonials },
            { label: 'Store', href: '/admin/store', icon: ShoppingBag },
        ],
    },
    {
        label: 'Business',
        items: [
            { label: 'Clients', href: '/admin/clients', icon: Users, match: ['/admin/client-projects'] },
            { label: 'Invoices', href: '/admin/invoices', icon: Receipt },
            { label: 'Payments', href: '/admin/payments', icon: CreditCard },
            { label: 'Messages', href: '/admin/messages', icon: Inbox, badge: counts.value.unread_messages },
        ],
    },
    {
        label: 'Tools',
        items: [
            { label: 'Status page', href: '/admin/monitors', icon: Activity, badge: counts.value.monitors_down },
            { label: 'Shares', href: '/admin/shares', icon: FileCode2 },
            { label: 'Settings', href: '/admin/settings', icon: Settings },
        ],
    },
]);

const path = computed(() => page.url.split('?')[0]);
const active = (item) => (item.exact ? path.value === item.href : [item.href, ...(item.match ?? [])].some((h) => path.value.startsWith(h)));

router.on('navigate', () => (open.value = false));
</script>

<template>
    <div class="min-h-svh bg-bg lg:pl-[17rem]">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-[17rem] flex-col border-r border-line bg-bg-2 transition-transform duration-500 ease-out-expo lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center justify-between px-5">
                <Link href="/admin" class="text-[14px]"><Logo :size="26" force-built-in /></Link>
                <button type="button" class="text-muted lg:hidden" aria-label="Close menu" @click="open = false"><X :size="20" /></button>
            </div>
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4" aria-label="Admin">
                <div v-for="(group, gi) in groups" :key="gi">
                    <div v-if="group.label" class="px-3 pb-2 text-xs font-medium text-faint">{{ group.label }}</div>
                    <ul class="space-y-0.5">
                        <li v-for="item in group.items" :key="item.href">
                            <Link
                                :href="item.href"
                                class="group flex h-10 items-center gap-3 rounded-xl px-3 text-[0.9rem] transition-colors"
                                :class="active(item) ? 'bg-white/[0.07] text-text shadow-[inset_0_0_0_1px_var(--line)]' : 'text-text-2 hover:bg-white/[0.04] hover:text-text'"
                            >
                                <component :is="item.icon" :size="17" :stroke-width="1.75" :class="active(item) ? 'text-accent-hot' : 'text-muted group-hover:text-text-2'" />
                                <span class="flex-1">{{ item.label }}</span>
                                <span v-if="item.badge" class="grid h-5 min-w-5 place-items-center rounded-full px-1.5 text-[0.7rem] font-semibold text-white" style="background: var(--gradient)">{{ item.badge }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="border-t border-line p-3">
                <a href="/" target="_blank" class="flex h-10 items-center gap-3 rounded-xl px-3 text-[0.9rem] text-text-2 hover:bg-white/[0.04] hover:text-text">
                    <ExternalLink :size="17" :stroke-width="1.75" class="text-muted" /> View site
                </a>
                <div class="mt-2 flex items-center gap-3 rounded-xl bg-white/[0.03] p-2.5 shadow-[inset_0_0_0_1px_var(--line)]">
                    <img v-if="user?.avatar" :src="user.avatar" alt="" class="size-9 rounded-full" />
                    <span v-else class="grid size-9 place-items-center rounded-full bg-[#5865F2] text-sm font-semibold">{{ user?.name?.charAt(0) }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium">{{ user?.name }}</div>
                        <div class="truncate text-xs text-muted">@{{ user?.username }}</div>
                    </div>
                    <Link href="/logout" method="post" as="button" class="grid size-8 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Log out" title="Log out"><LogOut :size="16" /></Link>
                </div>
            </div>
        </aside>
        <div v-if="open" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" @click="open = false"></div>

        <!-- Mobile top bar -->
        <div class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-line bg-bg/80 px-4 backdrop-blur-xl lg:hidden">
            <button type="button" class="btn btn-glass btn-icon h-9 w-9" aria-label="Open menu" @click="open = true"><Menu :size="18" /></button>
            <Logo :size="24" force-built-in />
            <span class="w-9"></span>
        </div>

        <main class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
            <slot />
        </main>
        <FlashToast />
    </div>
</template>
