<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { UserPlus, Pencil, Trash2, Star, MessageSquareQuote, Link2 } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import StarRating from '@/components/StarRating.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago } from '@/lib/format';
import { ref } from 'vue';

const props = defineProps({ testimonials: Array, counts: Object, filter: String });
const copied = ref(null);
const tabs = [
    { key: null, label: 'All' },
    { key: 'pending', label: 'Needs review' },
    { key: 'invited', label: 'Invited' },
    { key: 'approved', label: 'Approved' },
    { key: 'rejected', label: 'Rejected' },
];
const total = Object.values(props.counts).reduce((a, b) => a + b, 0);
const setStatus = (t, status) => router.patch(`/admin/testimonials/${t.id}/status`, { status }, { preserveScroll: true });
const copy = async (t) => {
    await navigator.clipboard.writeText(t.invite_url);
    copied.value = t.id;
    setTimeout(() => (copied.value = null), 1500);
};
</script>

<template>
    <Head title="Testimonials" />
    <PageHeader title="Testimonials" description="Invite clients with a personal link, review what they write, then attach it to a project.">
        <Link href="/admin/testimonials/create" class="btn btn-primary btn-sm"><UserPlus :size="15" /> Invite client</Link>
    </PageHeader>

    <div class="mb-5 flex flex-wrap gap-2">
        <Link
            v-for="t in tabs"
            :key="t.label"
            :href="t.key ? `/admin/testimonials?status=${t.key}` : '/admin/testimonials'"
            preserve-scroll
            class="inline-flex h-9 items-center gap-2 rounded-full px-4 text-sm transition-colors"
            :class="filter === t.key ? 'bg-text text-bg' : 'bg-white/[0.04] text-text-2 shadow-[inset_0_0_0_1px_var(--line)] hover:text-text'"
        >
            {{ t.label }} <span class="text-xs opacity-60">{{ t.key ? (counts[t.key] ?? 0) : total }}</span>
        </Link>
    </div>

    <div v-if="testimonials.length" class="grid gap-4 lg:grid-cols-2">
        <article v-for="t in testimonials" :key="t.id" class="card-solid flex flex-col p-5">
            <div class="flex items-start gap-3">
                <img v-if="t.avatar" :src="t.avatar" alt="" class="size-11 rounded-full object-cover" />
                <span v-else class="grid size-11 place-items-center rounded-full bg-surface-3 font-medium">{{ t.name.charAt(0) }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2"><span class="truncate font-medium">{{ t.name }}</span><Star v-if="t.is_featured" :size="13" class="fill-amber-300 text-amber-300" /></div>
                    <div class="truncate text-sm text-muted">{{ [t.role, t.company].filter(Boolean).join(' · ') || '—' }}</div>
                </div>
                <StatusBadge :status="t.status" />
            </div>
            <div v-if="t.body" class="mt-4 flex-1">
                <StarRating :model-value="t.rating" :size="13" />
                <p class="mt-2 line-clamp-4 text-sm leading-relaxed text-text-2">“{{ t.body }}”</p>
            </div>
            <p v-else class="mt-4 flex-1 text-sm text-muted">Waiting for {{ t.name.split(' ')[0] }} to write their testimonial.</p>
            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-line pt-4 text-sm">
                <span v-if="t.project" class="chip">{{ t.project }}</span>
                <span v-if="t.submitted_at" class="text-xs text-muted">Submitted {{ ago(t.submitted_at) }}</span>
                <span class="flex-1"></span>
                <template v-if="t.status === 'pending'">
                    <button type="button" class="btn btn-light btn-sm h-8" @click="setStatus(t, 'approved')">Approve</button>
                    <button type="button" class="btn btn-glass btn-sm h-8" @click="setStatus(t, 'rejected')">Reject</button>
                </template>
                <button v-if="t.status === 'invited'" type="button" class="btn btn-glass btn-sm h-8" @click="copy(t)"><Link2 :size="14" /> {{ copied === t.id ? 'Copied!' : 'Copy invite link' }}</button>
                <Link :href="`/admin/testimonials/${t.id}/edit`" class="grid size-8 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Edit"><Pencil :size="15" /></Link>
                <button type="button" class="grid size-8 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" aria-label="Delete" @click="confirmVisit(`Delete testimonial from ${t.name}?`, `/admin/testimonials/${t.id}`)"><Trash2 :size="15" /></button>
            </div>
        </article>
    </div>
    <div v-else class="card-solid">
        <EmptyState :icon="MessageSquareQuote" title="Nothing here" description="Invite a client — they'll get a private link to leave a rating and a few words.">
            <Link href="/admin/testimonials/create" class="btn btn-primary btn-sm"><UserPlus :size="15" /> Invite client</Link>
        </EmptyState>
    </div>
</template>
