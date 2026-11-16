<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, X, Activity, RefreshCw, Pause, Play, Trash2, Pencil, ExternalLink, Globe, Lock, Loader2, Save } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import Toggle from '@/components/admin/Toggle.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import UptimeBars from '@/components/UptimeBars.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago } from '@/lib/format';

const props = defineProps({ monitors: Array, status_url: String, suggestions: Array });

const blank = () => ({ name: '', url: '', method: 'GET', expected_status: null, keyword: '', timeout: 10, is_public: true });
const editing = ref(null); // null = closed, 'new' or a monitor id
const form = useForm(blank());

const open = (m = null) => {
    editing.value = m ? m.id : 'new';
    form.defaults(m ? { name: m.name, url: m.url, method: m.method, expected_status: m.expected_status, keyword: m.keyword ?? '', timeout: m.timeout, is_public: m.is_public } : blank()).reset();
    form.clearErrors();
};
const suggest = (url) => {
    open();
    form.url = url;
    form.name = new URL(url).hostname.replace(/^www\./, '');
};
const save = () => {
    const done = { preserveScroll: true, onSuccess: () => (editing.value = null) };
    editing.value === 'new' ? form.post('/admin/monitors', done) : form.patch(`/admin/monitors/${editing.value}`, done);
};

const checking = ref(null);
const check = (m) => router.post(`/admin/monitors/${m.id}/check`, {}, { preserveScroll: true, onStart: () => (checking.value = m.id), onFinish: () => (checking.value = null) });
const patch = (m, data) => router.patch(`/admin/monitors/${m.id}`, data, { preserveScroll: true });
const fmt = (v) => (v === null ? '—' : `${v}%`);
</script>

<template>
    <Head title="Status page" />
    <PageHeader title="Status page" description="Each site is checked every minute. Two failures in a row open an incident and alert Discord.">
        <a :href="status_url" target="_blank" class="btn btn-glass btn-sm" :class="!monitors.some((m) => m.is_public) && 'pointer-events-none opacity-40'"><ExternalLink :size="14" /> View /status</a>
        <button type="button" class="btn btn-primary btn-sm" @click="editing === 'new' ? (editing = null) : open()"><component :is="editing === 'new' ? X : Plus" :size="15" /> {{ editing === 'new' ? 'Cancel' : 'Add monitor' }}</button>
    </PageHeader>

    <form v-if="editing !== null" class="card-solid mb-6 space-y-5 p-6" @submit.prevent="save">
        <h2 class="title text-lg">{{ editing === 'new' ? 'New monitor' : 'Edit monitor' }}</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <Field label="Name" for="m-name" :error="form.errors.name"><input id="m-name" v-model="form.name" class="field" placeholder="Website" required /></Field>
            <Field label="URL" for="m-url" :error="form.errors.url" class="lg:col-span-2"><input id="m-url" v-model="form.url" type="url" class="field font-mono text-sm" placeholder="https://example.com/up" required /></Field>
            <Field label="Method" for="m-method"><select id="m-method" v-model="form.method" class="field"><option>GET</option><option>HEAD</option></select></Field>
            <Field label="Expected status" for="m-status" optional help="Blank accepts any 2xx/3xx." :error="form.errors.expected_status"><input id="m-status" v-model.number="form.expected_status" type="number" min="100" max="599" class="field" placeholder="200" /></Field>
            <Field label="Must contain text" for="m-keyword" optional help="Catches error pages that return 200." :error="form.errors.keyword" class="lg:col-span-2"><input id="m-keyword" v-model="form.keyword" class="field" :disabled="form.method === 'HEAD'" /></Field>
            <Field label="Timeout (seconds)" for="m-timeout" :error="form.errors.timeout"><input id="m-timeout" v-model.number="form.timeout" type="number" min="2" max="30" class="field" /></Field>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <Toggle v-model="form.is_public" label="Show on the public status page" />
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing"><Loader2 v-if="form.processing" :size="14" class="animate-spin" /><Save v-else :size="14" /> {{ editing === 'new' ? 'Add & check now' : 'Save' }}</button>
        </div>
    </form>

    <div v-if="monitors.length" class="space-y-3">
        <article v-for="m in monitors" :key="m.id" class="card-solid p-5" :class="m.status === 'paused' && 'opacity-60'">
            <div class="flex flex-wrap items-center gap-4">
                <span class="size-2.5 shrink-0 rounded-full" :class="m.status === 'up' ? 'bg-emerald-400 shadow-[0_0_10px_rgb(52_211_153/.7)]' : m.status === 'down' ? 'bg-[#f43f5e] shadow-[0_0_10px_rgb(244_63_94/.7)]' : 'bg-white/25'"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ m.name }}</span>
                        <StatusBadge :status="m.status" />
                        <span class="inline-flex items-center gap-1 text-xs text-muted" :title="m.is_public ? 'Shown on /status' : 'Private'"><component :is="m.is_public ? Globe : Lock" :size="12" /> {{ m.is_public ? 'Public' : 'Private' }}</span>
                    </div>
                    <p class="mt-0.5 truncate font-mono text-xs text-muted">{{ m.method }} {{ m.url }}</p>
                </div>
                <dl class="flex gap-6 text-sm">
                    <div><dt class="text-xs text-muted">Response</dt><dd class="font-mono">{{ m.last_response_ms !== null && m.status === 'up' ? `${m.last_response_ms} ms` : '—' }}</dd></div>
                    <div><dt class="text-xs text-muted">24 h</dt><dd class="font-mono">{{ fmt(m.uptime_1) }}</dd></div>
                    <div><dt class="text-xs text-muted">30 days</dt><dd class="font-mono">{{ fmt(m.uptime_30) }}</dd></div>
                </dl>
                <div class="flex gap-1">
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Check now" title="Check now" :disabled="checking === m.id" @click="check(m)"><RefreshCw :size="16" :class="checking === m.id && 'animate-spin'" /></button>
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" :aria-label="m.status === 'paused' ? 'Resume' : 'Pause'" :title="m.status === 'paused' ? 'Resume' : 'Pause'" @click="patch(m, { is_paused: m.status !== 'paused' })"><component :is="m.status === 'paused' ? Play : Pause" :size="16" /></button>
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" aria-label="Edit" @click="open(m)"><Pencil :size="16" /></button>
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" aria-label="Delete" @click="confirmVisit(`Delete ${m.name} and its uptime history?`, `/admin/monitors/${m.id}`)"><Trash2 :size="16" /></button>
                </div>
            </div>
            <UptimeBars class="mt-4" :bars="m.bars" height="h-6" />
            <p class="mt-2 flex flex-wrap justify-between gap-2 text-xs text-muted">
                <span>{{ m.last_checked_at ? `Checked ${ago(m.last_checked_at)}` : 'Not checked yet' }}<template v-if="m.status_changed_at"> · {{ m.status === 'down' ? 'down' : 'up' }} since {{ ago(m.status_changed_at) }}</template></span>
                <span v-if="m.status === 'down' && m.last_error" class="text-accent-hot">{{ m.last_error }}</span>
                <span v-else>Last 30 days</span>
            </p>
        </article>
    </div>
    <EmptyState v-else-if="editing === null" :icon="Activity" title="Nothing monitored yet" description="Add the sites you host or maintain. They'll appear on a public status page you can share with clients.">
        <div class="flex flex-wrap justify-center gap-2">
            <button v-for="s in suggestions" :key="s" type="button" class="btn btn-glass btn-sm" @click="suggest(s)"><Plus :size="14" /> {{ s.replace(/^https?:\/\//, '') }}</button>
            <button type="button" class="btn btn-primary btn-sm" @click="open()"><Plus :size="14" /> Add monitor</button>
        </div>
    </EmptyState>
</template>
