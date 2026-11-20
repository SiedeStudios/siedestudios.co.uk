<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, GripVertical, Pencil, Trash2, Layers, Inbox } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import ServiceIcon from '@/components/ServiceIcon.vue';
import { useDragSort } from '@/composables/useDragSort';
import { confirmVisit } from '@/composables/useConfirm';
import { money } from '@/lib/format';

const props = defineProps({ services: Array });
const list = ref([...props.services]);
watch(() => props.services, (v) => (list.value = [...v]));
const sort = useDragSort(list, (items) => router.post('/admin/services/reorder', { ids: items.map((s) => s.id) }, { preserveScroll: true }));
</script>

<template>
    <Head title="Services" />
    <PageHeader title="Services" description="What you offer. These also populate the contact form's service dropdown.">
        <Link href="/admin/services/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New service</Link>
    </PageHeader>

    <div class="card-solid overflow-hidden">
        <ul v-if="list.length" class="divide-y divide-line">
            <li
                v-for="(s, i) in list"
                :key="s.id"
                draggable="true"
                class="group flex items-center gap-4 p-4 transition-colors"
                :class="[sort.over.value === i ? 'bg-white/[0.05]' : '', sort.dragging.value === i ? 'opacity-40' : '']"
                @dragstart="sort.start(i, $event)" @dragenter.prevent="sort.enter(i)" @dragover.prevent @drop.prevent="sort.drop(i)" @dragend="sort.end"
            >
                <GripVertical :size="16" class="shrink-0 cursor-grab text-faint group-hover:text-muted" />
                <span class="grid size-10 shrink-0 place-items-center rounded-xl text-white" :class="s.is_active ? '' : 'opacity-40 grayscale'" style="background: var(--gradient)"><ServiceIcon :name="s.icon" :size="18" /></span>
                <div class="min-w-0 flex-1">
                    <Link :href="`/admin/services/${s.id}/edit`" class="font-medium hover:text-accent-hot">{{ s.title }}</Link>
                    <span v-if="!s.is_active" class="chip ml-2">Hidden</span>
                    <div class="truncate text-sm text-muted">{{ s.tagline }}</div>
                </div>
                <span class="hidden text-sm text-muted md:block">{{ s.starting_price ? `From ${money(s.starting_price, $page.props.site.currency)}` : s.price_note }}</span>
                <span class="chip hidden sm:inline-flex"><Inbox :size="12" /> {{ s.messages_count }}</span>
                <Link :href="`/admin/services/${s.id}/edit`" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Edit"><Pencil :size="16" /></Link>
                <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" aria-label="Delete" @click="confirmVisit(`Delete “${s.title}”?`, `/admin/services/${s.id}`)"><Trash2 :size="16" /></button>
            </li>
        </ul>
        <EmptyState v-else :icon="Layers" title="No services yet" description="Add the services you offer.">
            <Link href="/admin/services/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New service</Link>
        </EmptyState>
    </div>
</template>
