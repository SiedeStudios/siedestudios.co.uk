<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Search, Users, X, Loader2, FolderKanban, UserRound } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import Field from '@/components/admin/Field.vue';
import { ago } from '@/lib/format';

const props = defineProps({ clients: Object, search: String });

const q = ref(props.search ?? '');
let t;
watch(q, (v) => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/admin/clients', v ? { search: v } : {}, { preserveState: true, replace: true }), 300);
});

const adding = ref(false);
const form = useForm({ name: '', company: '', email: '' });
const submit = () => form.post('/admin/clients');
</script>

<template>
    <Head title="Clients" />
    <PageHeader title="Clients" description="Client accounts for the portal. Each client signs in with Discord through a single-use invite link.">
        <label class="relative">
            <Search :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
            <input v-model="q" type="search" class="field h-9 w-56 pl-10 text-sm" placeholder="Search clients" aria-label="Search clients" />
        </label>
        <button type="button" class="btn btn-primary btn-sm" @click="adding = !adding"><component :is="adding ? X : Plus" :size="15" /> {{ adding ? 'Cancel' : 'New client' }}</button>
    </PageHeader>

    <form v-if="adding" class="card-solid mb-6 grid gap-5 p-6 sm:grid-cols-3" @submit.prevent="submit">
        <Field label="Contact name" for="name" :error="form.errors.name"><input id="name" v-model="form.name" class="field" required autofocus /></Field>
        <Field label="Company" for="company" optional :error="form.errors.company"><input id="company" v-model="form.company" class="field" /></Field>
        <Field label="Email" for="email" optional :error="form.errors.email"><input id="email" v-model="form.email" type="email" class="field" /></Field>
        <div class="flex items-center justify-between gap-4 sm:col-span-3">
            <p class="text-sm text-muted">An invite link is created straight away — send it to them on Discord or by email.</p>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing"><Loader2 v-if="form.processing" :size="14" class="animate-spin" /><Plus v-else :size="14" /> Create client</button>
        </div>
    </form>

    <div class="card-solid overflow-hidden">
        <ul v-if="clients.data.length" class="divide-y divide-line">
            <li v-for="c in clients.data" :key="c.id">
                <Link :href="`/admin/clients/${c.id}`" class="flex flex-wrap items-center gap-4 p-4 hover:bg-white/[0.03]">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl text-white" style="background: var(--gradient)">{{ (c.company || c.name)[0] }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium">{{ c.company || c.name }}</span>
                        <span class="block truncate text-sm text-muted">{{ c.company ? c.name : '' }}{{ c.company && c.email ? ' · ' : '' }}{{ c.email }}</span>
                    </span>
                    <span class="flex items-center gap-1.5 text-sm text-muted"><FolderKanban :size="14" /> {{ c.active_projects_count }} active / {{ c.projects_count }}</span>
                    <span class="flex items-center gap-1.5 text-sm" :class="c.users_count ? 'text-emerald-300' : 'text-muted'"><UserRound :size="14" /> {{ c.users_count ? `${c.users_count} signed in` : 'Not joined yet' }}</span>
                    <span class="w-24 text-right text-xs text-muted">{{ ago(c.updated_at) }}</span>
                </Link>
            </li>
        </ul>
        <EmptyState v-else :icon="Users" :title="search ? 'No matching clients' : 'No clients yet'" :description="search ? 'Try another name.' : 'Add a client to share project progress, invoices and files through their own portal.'" />
    </div>
    <Pagination :paginator="clients" />
</template>
