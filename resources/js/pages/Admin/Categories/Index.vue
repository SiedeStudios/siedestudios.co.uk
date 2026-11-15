<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Check, X, Tags } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { confirmVisit } from '@/composables/useConfirm';

defineProps({ categories: Array });

const create = useForm({ name: '', description: '', sort_order: 0 });
const editingId = ref(null);
const edit = useForm({ name: '', description: '', sort_order: 0 });

const add = () => create.post('/admin/categories', { preserveScroll: true, onSuccess: () => create.reset() });
const startEdit = (c) => {
    editingId.value = c.id;
    edit.defaults({ name: c.name, description: c.description ?? '', sort_order: c.sort_order });
    edit.reset();
};
const save = (c) => edit.put(`/admin/categories/${c.id}`, { preserveScroll: true, onSuccess: () => (editingId.value = null) });
</script>

<template>
    <Head title="Categories" />
    <PageHeader title="Categories" description="Group projects on the Work page. Only categories with published projects appear publicly." back="/admin/projects" />

    <div class="grid gap-4 xl:grid-cols-3">
        <section class="card-solid overflow-hidden xl:col-span-2">
            <ul v-if="categories.length" class="divide-y divide-line">
                <li v-for="c in categories" :key="c.id" class="p-4">
                    <form v-if="editingId === c.id" class="grid gap-3 sm:grid-cols-[1fr_2fr_5rem_auto]" @submit.prevent="save(c)">
                        <input v-model="edit.name" class="field" placeholder="Name" required />
                        <input v-model="edit.description" class="field" placeholder="Description" />
                        <input v-model.number="edit.sort_order" type="number" min="0" class="field" aria-label="Order" />
                        <div class="flex gap-1">
                            <button type="submit" class="btn btn-light btn-icon" aria-label="Save"><Check :size="16" /></button>
                            <button type="button" class="btn btn-glass btn-icon" aria-label="Cancel" @click="editingId = null"><X :size="16" /></button>
                        </div>
                        <p v-if="edit.errors.name" class="field-error sm:col-span-4">{{ edit.errors.name }}</p>
                    </form>
                    <div v-else class="flex items-center gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="font-medium">{{ c.name }} <span class="ml-2 font-mono text-xs text-faint">/{{ c.slug }}</span></div>
                            <div class="truncate text-sm text-muted">{{ c.description || 'No description' }}</div>
                        </div>
                        <Link :href="`/admin/projects`" class="chip">{{ c.projects_count }} project{{ c.projects_count === 1 ? '' : 's' }}</Link>
                        <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Edit" @click="startEdit(c)"><Pencil :size="16" /></button>
                        <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" aria-label="Delete" @click="confirmVisit(`Delete “${c.name}”? Its projects become uncategorised.`, `/admin/categories/${c.id}`)"><Trash2 :size="16" /></button>
                    </div>
                </li>
            </ul>
            <EmptyState v-else :icon="Tags" title="No categories" description="Create categories like “Web Platforms” or “Garry's Mod”." />
        </section>

        <form class="card-solid h-fit space-y-4 p-6" @submit.prevent="add">
            <h2 class="title text-lg">New category</h2>
            <div>
                <label class="field-label" for="c-name">Name</label>
                <input id="c-name" v-model="create.name" class="field" required />
                <p v-if="create.errors.name" class="field-error">{{ create.errors.name }}</p>
            </div>
            <div>
                <label class="field-label" for="c-desc">Description <span class="text-faint">(optional)</span></label>
                <textarea id="c-desc" v-model="create.description" rows="2" class="field"></textarea>
            </div>
            <div>
                <label class="field-label" for="c-order">Order</label>
                <input id="c-order" v-model.number="create.sort_order" type="number" min="0" class="field" />
            </div>
            <button type="submit" class="btn btn-primary w-full" :disabled="create.processing"><Plus :size="16" /> Add category</button>
        </form>
    </div>
</template>
