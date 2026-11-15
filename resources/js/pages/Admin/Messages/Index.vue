<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Inbox } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { ago } from '@/lib/format';

defineProps({ messages: Object, filter: String });
</script>

<template>
    <Head title="Messages" />
    <PageHeader title="Messages" description="Enquiries from the contact form." />
    <div class="mb-5 flex gap-2">
        <Link href="/admin/messages" class="inline-flex h-9 items-center rounded-full px-4 text-sm" :class="!filter ? 'bg-text text-bg' : 'bg-white/[0.04] text-text-2 shadow-[inset_0_0_0_1px_var(--line)]'">All</Link>
        <Link href="/admin/messages?filter=unread" class="inline-flex h-9 items-center rounded-full px-4 text-sm" :class="filter === 'unread' ? 'bg-text text-bg' : 'bg-white/[0.04] text-text-2 shadow-[inset_0_0_0_1px_var(--line)]'">Unread</Link>
    </div>
    <div class="card-solid overflow-hidden">
        <ul v-if="messages.data.length" class="divide-y divide-line">
            <li v-for="m in messages.data" :key="m.id">
                <Link :href="`/admin/messages/${m.id}`" class="flex gap-4 p-5 transition-colors hover:bg-white/[0.03]">
                    <span class="mt-2 size-2 shrink-0 rounded-full" :class="m.read_at ? 'bg-transparent' : 'bg-accent-hot shadow-[0_0_10px_var(--accent)]'"></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-baseline justify-between gap-x-4">
                            <span :class="m.read_at ? 'text-text-2' : 'font-semibold'">{{ m.name }} <span class="font-normal text-muted">· {{ m.email }}</span></span>
                            <span class="text-xs text-muted">{{ ago(m.created_at) }}</span>
                        </span>
                        <span class="mt-1 flex flex-wrap gap-2"><span class="chip">{{ m.service_name }}</span><span v-if="m.budget" class="chip">{{ m.budget }}</span></span>
                        <span class="mt-2 line-clamp-2 block text-sm text-muted">{{ m.body }}</span>
                    </span>
                </Link>
            </li>
        </ul>
        <EmptyState v-else :icon="Inbox" title="Inbox zero" :description="filter ? 'No unread messages.' : 'Messages from the contact form will appear here.'" />
    </div>
    <Pagination :paginator="messages" />
</template>
