<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Save, Loader2, Trash2, Lock } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import Toggle from '@/components/admin/Toggle.vue';
import CopyField from '@/components/admin/CopyField.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago } from '@/lib/format';

const props = defineProps({ share: Object, languages: Array, expiries: Object });
const editing = computed(() => !!props.share);

const form = useForm({
    title: props.share?.title ?? '',
    content: props.share?.content ?? '',
    language: props.share?.language ?? 'plaintext',
    password: '',
    remove_password: false,
    burn_after_read: props.share?.burn_after_read ?? false,
    expiry: editing.value ? null : 'never',
});
const expiryLabels = { never: 'Never', '1h': '1 hour', '1d': '1 day', '1w': '1 week', '1m': '1 month' };

// Guess the language from a filename-like title.
const guess = () => {
    const ext = form.title.split('.').pop()?.toLowerCase();
    const map = { lua: 'lua', php: 'php', js: 'javascript', ts: 'typescript', json: 'json', html: 'html', css: 'css', scss: 'scss', sql: 'sql', sh: 'bash', py: 'python', yml: 'yaml', yaml: 'yaml', ini: 'ini', cfg: 'ini', md: 'markdown', cs: 'csharp', cpp: 'cpp', xml: 'xml', diff: 'diff' };
    if (map[ext] && form.language === 'plaintext') form.language = map[ext];
};
const tab = (e) => {
    const el = e.target;
    const start = el.selectionStart;
    form.content = `${form.content.slice(0, start)}    ${form.content.slice(el.selectionEnd)}`;
    requestAnimationFrame(() => (el.selectionStart = el.selectionEnd = start + 4));
};
const submit = () => (editing.value
    ? form.put(`/admin/shares/${props.share.slug}`, { preserveScroll: true, onSuccess: () => form.reset('password', 'remove_password') })
    : form.post('/admin/shares'));
</script>

<template>
    <Head :title="editing ? `Share — ${share.title || share.slug}` : 'New share'" />
    <PageHeader :title="editing ? (share.title || 'Untitled share') : 'New share'" :description="editing ? `${share.views} views` : 'Paste anything — it gets a private, unlisted link.'" back="/admin/shares">
        <button v-if="editing" type="button" class="btn btn-danger btn-sm" @click="confirmVisit('Delete this share?', `/admin/shares/${share.slug}`)"><Trash2 :size="15" /></button>
    </PageHeader>

    <section v-if="editing" class="card-solid mb-4 grid gap-3 p-6 md:grid-cols-2">
        <div><div class="field-label">Share link</div><CopyField :value="share.url" /></div>
        <div><div class="field-label">Raw text</div><CopyField :value="share.raw_url" /></div>
    </section>

    <form class="grid gap-4 xl:grid-cols-[1fr_20rem]" @submit.prevent="submit">
        <section class="card-solid flex flex-col p-4">
            <input v-model="form.title" class="field border-transparent bg-transparent text-lg focus:bg-white/[0.03]" placeholder="Title or filename, e.g. sh_config.lua" aria-label="Title" @blur="guess" />
            <textarea
                v-model="form.content"
                class="field mt-2 min-h-[28rem] flex-1 resize-y bg-bg-2 font-mono text-[0.8125rem] leading-6"
                placeholder="Paste your code or text here…"
                spellcheck="false"
                aria-label="Content"
                data-lenis-prevent
                required
                @keydown.tab.prevent="tab"
            ></textarea>
            <p v-if="form.errors.content" class="field-error">{{ form.errors.content }}</p>
        </section>
        <aside class="card-solid h-fit space-y-5 p-6">
            <Field label="Syntax" for="language" :error="form.errors.language">
                <select id="language" v-model="form.language" class="field"><option v-for="l in languages" :key="l" :value="l">{{ l }}</option></select>
            </Field>
            <Field label="Expires" for="expiry" :error="form.errors.expiry" :help="editing && share.expires_at ? `Currently expires ${ago(share.expires_at)}` : null">
                <select id="expiry" v-model="form.expiry" class="field">
                    <option v-if="editing" :value="null">Keep current</option>
                    <option v-for="(v, k) in expiries" :key="k" :value="k">{{ expiryLabels[k] }}</option>
                </select>
            </Field>
            <Field :label="editing && share.protected ? 'New password' : 'Password'" for="password" :error="form.errors.password" optional>
                <div class="relative">
                    <Lock :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                    <input id="password" v-model="form.password" type="password" class="field pl-10" autocomplete="new-password" :placeholder="editing && share.protected ? '•••••• (unchanged)' : 'No password'" />
                </div>
            </Field>
            <Toggle v-if="editing && share.protected" v-model="form.remove_password" label="Remove password" />
            <Toggle v-model="form.burn_after_read" label="Burn after reading" description="Deleted the first time someone opens it." />
            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                <Loader2 v-if="form.processing" :size="16" class="animate-spin" /><Save v-else :size="16" /> {{ editing ? 'Save changes' : 'Create share' }}
            </button>
        </aside>
    </form>
</template>
