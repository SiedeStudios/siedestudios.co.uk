<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, RefreshCw, ShoppingBag, Star, ShoppingCart, Eye, EyeOff, ExternalLink, Trash2, Loader2, AlertTriangle, Settings, Sparkles, Package } from 'lucide-vue-next';
import PageHeader from '@/components/admin/PageHeader.vue';
import Field from '@/components/admin/Field.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import { confirmVisit } from '@/composables/useConfirm';
import { ago } from '@/lib/format';

const props = defineProps({ products: Array, configured: Boolean, team: Boolean, show_sales: Boolean });

const form = useForm({ product: '' });
const add = () => form.post('/admin/store', { preserveScroll: true, onSuccess: () => form.reset() });

const syncing = ref(false);
const syncAll = () => router.post('/admin/store/sync', {}, { preserveScroll: true, onStart: () => (syncing.value = true), onFinish: () => (syncing.value = false) });
const patch = (p, data) => router.patch(`/admin/store/${p.id}`, data, { preserveScroll: true });

const price = (p) => (p.price == null ? 'Free' : new Intl.NumberFormat('en-GB', { style: 'currency', currency: p.currency || 'USD' }).format(p.price / 100));
</script>

<template>
    <Head title="Store" />
    <PageHeader title="Store" description="GmodStore products shown on /store and the home page. Prices, ratings and sales refresh every hour.">
        <a href="/store" target="_blank" class="btn btn-glass btn-sm" :class="!products.some((p) => p.is_visible && p.is_listed) && 'pointer-events-none opacity-40'"><ExternalLink :size="14" /> View store</a>
        <button type="button" class="btn btn-glass btn-sm" :disabled="!configured || syncing" @click="syncAll"><Loader2 v-if="syncing" :size="14" class="animate-spin" /><RefreshCw v-else :size="14" /> {{ team ? 'Import & refresh' : 'Refresh all' }}</button>
    </PageHeader>

    <div v-if="!configured" class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-amber-400/10 p-5 text-sm text-amber-100 shadow-[inset_0_0_0_1px_rgb(251_191_36/.2)]">
        <span class="flex items-center gap-2.5"><AlertTriangle :size="17" class="text-amber-300" /> Add a GmodStore API key to fetch your products.</span>
        <Link href="/admin/settings/gmodstore" class="btn btn-light btn-sm"><Settings :size="14" /> GmodStore settings</Link>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="card-solid overflow-hidden">
            <ul v-if="products.length" class="divide-y divide-line">
                <li v-for="p in products" :key="p.id" class="flex flex-wrap items-center gap-4 p-4" :class="!p.is_visible || !p.is_listed ? 'opacity-55' : ''">
                    <div class="relative h-16 w-24 shrink-0 overflow-hidden rounded-xl bg-surface-2">
                        <img v-if="p.image_url" :src="p.image_url" alt="" class="absolute inset-0 h-full w-full object-cover" />
                        <Package v-else :size="20" class="absolute inset-0 m-auto text-faint" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a :href="p.url" target="_blank" rel="noopener" class="truncate font-medium hover:text-accent-hot">{{ p.name }}</a>
                            <span v-if="p.is_featured" class="chip !py-0.5 text-[11px]"><Sparkles :size="11" /> Featured</span>
                            <span v-if="!p.is_listed" class="chip !py-0.5 text-[11px] text-amber-300">Not for sale on GmodStore</span>
                        </div>
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted">
                            <span class="text-text-2">{{ price(p) }}</span>
                            <span class="inline-flex items-center gap-1"><Star :size="13" />{{ p.reviews_count ? `${Number(p.rating).toFixed(1)} · ${p.reviews_count}` : 'No reviews' }}</span>
                            <span v-if="show_sales" class="inline-flex items-center gap-1"><ShoppingCart :size="13" />{{ p.sales_count == null ? '—' : `${p.sales_count}${p.sales_capped ? '+' : ''}` }}</span>
                            <span>{{ p.synced_at ? `Synced ${ago(p.synced_at)}` : 'Never synced' }}</span>
                        </div>
                        <p v-if="p.sync_error" class="mt-1.5 flex items-center gap-1.5 text-xs text-accent-hot"><AlertTriangle :size="12" /> {{ p.sync_error }}</p>
                    </div>
                    <label class="flex items-center gap-2 text-xs text-muted" title="Order on the site (lower first)">#<input type="number" min="0" :value="p.sort_order" class="field h-9 w-16 px-2 text-center" @change="patch(p, { sort_order: Number($event.target.value) })" /></label>
                    <button type="button" class="btn btn-glass btn-sm h-9" :class="p.is_featured && '!text-accent-hot'" @click="patch(p, { is_featured: !p.is_featured })"><Sparkles :size="14" /> {{ p.is_featured ? 'Featured' : 'Feature' }}</button>
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-white/5 hover:text-text" :aria-label="p.is_visible ? 'Hide from site' : 'Show on site'" :title="p.is_visible ? 'Hide from site' : 'Show on site'" @click="patch(p, { is_visible: !p.is_visible })"><component :is="p.is_visible ? Eye : EyeOff" :size="16" /></button>
                    <button type="button" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-accent/10 hover:text-accent-hot" aria-label="Remove" @click="confirmVisit(`Remove ${p.name} from the site? It stays on GmodStore.`, `/admin/store/${p.id}`)"><Trash2 :size="16" /></button>
                </li>
            </ul>
            <EmptyState v-else :icon="ShoppingBag" title="No products yet" :description="team ? 'Press “Import & refresh” to pull in everything your team sells.' : 'Add a product by its GmodStore link, or set your team ID in settings to import them all.'" />
        </div>

        <form class="card-solid h-fit space-y-4 p-6" @submit.prevent="add">
            <h2 class="title text-lg">Add a product</h2>
            <Field label="GmodStore link or product ID" for="product" :error="form.errors.product">
                <input id="product" v-model="form.product" class="field font-mono text-sm" placeholder="https://www.gmodstore.com/market/view/…" required />
            </Field>
            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing || !configured"><Loader2 v-if="form.processing" :size="16" class="animate-spin" /><Plus v-else :size="16" /> Add product</button>
            <p class="text-xs leading-relaxed text-muted">Featured products also appear on the home page. Hidden products stay here but aren't shown to visitors.</p>
        </form>
    </div>
</template>
