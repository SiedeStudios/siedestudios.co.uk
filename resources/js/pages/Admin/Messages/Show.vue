<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Reply, Trash2, MailOpen, Mail } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import BrandIcon from '@/components/BrandIcon.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { date } from '@/lib/format';

const props = defineProps({ message: Object });
const reply = computed(() => `mailto:${props.message.email}?subject=${encodeURIComponent(`Re: your ${props.message.service_name?.toLowerCase() ?? ''} enquiry`)}`);
const toggle = () => router.patch(`/admin/messages/${props.message.id}/read`, {}, { preserveScroll: true });
</script>

<template>
    <Head :title="`Message from ${message.name}`" />
    <PageHeader :title="message.name" :description="message.email" back="/admin/messages">
        <button type="button" class="btn btn-glass btn-sm" @click="toggle"><component :is="message.read_at ? Mail : MailOpen" :size="15" /> Mark {{ message.read_at ? 'unread' : 'read' }}</button>
        <button type="button" class="btn btn-danger btn-sm" @click="confirmVisit('Delete this message?', `/admin/messages/${message.id}`)"><Trash2 :size="15" /></button>
    </PageHeader>
    <div class="grid gap-4 xl:grid-cols-3">
        <article class="card-solid p-7 xl:col-span-2">
            <p class="whitespace-pre-line text-[1.0625rem] leading-relaxed">{{ message.body }}</p>
            <div class="mt-8 flex flex-wrap gap-2 border-t border-line pt-6">
                <a :href="reply" class="btn btn-primary"><Reply :size="16" /> Reply by email</a>
            </div>
        </article>
        <aside class="card-solid h-fit p-6">
            <dl class="space-y-4 text-sm">
                <div><dt class="text-muted">Service</dt><dd class="mt-0.5">{{ message.service_name }}</dd></div>
                <div><dt class="text-muted">Budget</dt><dd class="mt-0.5">{{ message.budget || 'Not given' }}</dd></div>
                <div v-if="message.discord"><dt class="text-muted">Discord</dt><dd class="mt-0.5 inline-flex items-center gap-1.5"><BrandIcon name="discord" :size="14" class="text-[#8f99ff]" /> {{ message.discord }}</dd></div>
                <div><dt class="text-muted">Received</dt><dd class="mt-0.5">{{ date(message.created_at, { dateStyle: 'medium', timeStyle: 'short' }) }}</dd></div>
                <div v-if="message.ip_address"><dt class="text-muted">IP</dt><dd class="mt-0.5 font-mono text-xs">{{ message.ip_address }}</dd></div>
            </dl>
        </aside>
    </div>
</template>
