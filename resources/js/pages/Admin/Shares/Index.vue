<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, FileCode2, Lock, Flame, Eye, Pencil, Trash2, Link2, Check } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago, bytes } from '@/lib/format';

defineProps({ shares: Object });
const copied = ref(null);
const copy = async (s) => {
    await navigator.clipboard.writeText(s.url);
    copied.value = s.id;
    setTimeout(() => (copied.value = null), 1500);
};
</script>

<template>
    <Head title="Shares" />
    <PageHeader title="Shares" description="A private pastebin. Snippets are unlisted — only people with the link can see them.">
        <Link href="/admin/shares/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New share</Link>
    </PageHeader>

    <div class="card-solid overflow-hidden">
        <ul v-if="shares.data.length" class="divide-y divide-line">
            <li v-for="s in shares.data" :key="s.id" class="flex flex-wrap items-center gap-4 p-4" :class="s.expired ? 'opacity-50' : ''">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/5 text-muted"><FileCode2 :size="18" /></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <Link :href="`/admin/shares/${s.slug}/edit`" class="truncate font-medium hover:text-accent-hot">{{ s.title || 'Untitled' }}</Link>
                        <Lock v-if="s.protected" :size="13" class="text-amber-300" />
                        <Flame v-if="s.burn_after_read" :size="13" class="text-accent-hot" />
                    </div>
                    <div class="mt-0.5 flex flex-wrap gap-x-3 text-sm text-muted">
                        <span class="font-mono">/s/{{ s.slug }}</span>
                        <span>{{ s.language }}</span>
                        <span>{{ bytes(s.size) }}</span>
                        <span class="inline-flex items-center gap-1"><Eye :size="13" /> {{ s.views }}</span>
                        <span>{{ s.expired ? 'Expired' : s.expires_at ? `Expires ${ago(s.expires_at)}` : `Created ${ago(s.created_at)}` }}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-glass btn-sm h-8" @click="copy(s)"><component :is="copied === s.id ? Check : Link2" :size="14" /> {{ copied === s.id ? 'Copied' : 'Copy link' }}</button>
                <Link :href="`/admin/shares/${s.slug}/edit`" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Edit"><Pencil :size="16" /></Link>
                <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" aria-label="Delete" @click="confirmVisit('Delete this share? The link will stop working.', `/admin/shares/${s.slug}`)"><Trash2 :size="16" /></button>
            </li>
        </ul>
        <EmptyState v-else :icon="FileCode2" title="No shares yet" description="Paste code, configs or notes and get a private link to send to anyone.">
            <Link href="/admin/shares/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New share</Link>
        </EmptyState>
    </div>
    <Pagination :paginator="shares" />
</template>
