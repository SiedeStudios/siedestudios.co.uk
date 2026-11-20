<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, GripVertical, Star, Images, MessageSquareQuote, Pencil, Trash2, Eye, FolderKanban } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { useDragSort } from '@/composables/useDragSort';
import { confirmVisit } from '@/composables/useConfirm';

const props = defineProps({ projects: Array });
const list = ref([...props.projects]);
watch(() => props.projects, (v) => (list.value = [...v]));

const sort = useDragSort(list, (items) => router.post('/admin/projects/reorder', { ids: items.map((p) => p.id) }, { preserveScroll: true }));
</script>

<template>
    <Head title="Projects" />
    <PageHeader title="Projects" description="Portfolio work shown on the site. Drag to reorder.">
        <Link href="/admin/categories" class="btn btn-glass btn-sm">Categories</Link>
        <Link href="/admin/projects/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New project</Link>
    </PageHeader>

    <div class="card-solid overflow-hidden">
        <ul v-if="list.length" class="divide-y divide-line">
            <li
                v-for="(p, i) in list"
                :key="p.id"
                draggable="true"
                class="group flex items-center gap-4 p-3 pr-4 transition-colors"
                :class="[sort.over.value === i ? 'bg-white/[0.05]' : '', sort.dragging.value === i ? 'opacity-40' : '']"
                @dragstart="sort.start(i, $event)"
                @dragenter.prevent="sort.enter(i)"
                @dragover.prevent
                @drop.prevent="sort.drop(i)"
                @dragend="sort.end"
            >
                <GripVertical :size="16" class="shrink-0 cursor-grab text-faint group-hover:text-muted" />
                <div class="relative h-14 w-20 shrink-0 overflow-hidden rounded-lg bg-surface-2">
                    <img v-if="p.cover" :src="p.cover" alt="" class="h-full w-full object-cover object-top" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <Link :href="`/admin/projects/${p.id}/edit`" class="truncate font-medium hover:text-accent-hot">{{ p.title }}</Link>
                        <Star v-if="p.is_featured" :size="14" class="shrink-0 fill-amber-300 text-amber-300" />
                    </div>
                    <div class="mt-0.5 flex flex-wrap items-center gap-x-3 text-sm text-muted">
                        <span>{{ p.category || 'Uncategorised' }}</span>
                        <span v-if="p.year">{{ p.year }}</span>
                        <span class="inline-flex items-center gap-1"><Images :size="13" /> {{ p.images_count }}</span>
                        <span class="inline-flex items-center gap-1"><MessageSquareQuote :size="13" /> {{ p.testimonials_count }}</span>
                    </div>
                </div>
                <StatusBadge :status="p.status" class="hidden sm:inline-flex" />
                <div class="flex items-center gap-1">
                    <a :href="`/projects/${p.slug}`" target="_blank" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" title="View"><Eye :size="16" /></a>
                    <Link :href="`/admin/projects/${p.id}/edit`" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" title="Edit"><Pencil :size="16" /></Link>
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" title="Delete" @click="confirmVisit(`Delete “${p.title}” and all its screenshots?`, `/admin/projects/${p.id}`)"><Trash2 :size="16" /></button>
                </div>
            </li>
        </ul>
        <EmptyState v-else :icon="FolderKanban" title="No projects yet" description="Add your first project — you can upload screenshots once it's saved.">
            <Link href="/admin/projects/create" class="btn btn-primary btn-sm"><Plus :size="15" /> New project</Link>
        </EmptyState>
    </div>
</template>
