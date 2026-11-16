<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, Star, ShoppingCart, Package, ShieldCheck } from 'lucide-vue-next';
import PageHero from '@/components/PageHero.vue';
import ProductCard from '@/components/ProductCard.vue';
import { useMotion } from '@/composables/useMotion';

const props = defineProps({ products: Array, totals: Object });
const site = computed(() => usePage().props.site);
const root = ref(null);
useMotion(root);

const fmt = (n) => (n >= 1000 ? `${(n / 1000).toFixed(n >= 10000 ? 0 : 1)}k` : String(n));
</script>

<template>
    <Head title="Store" />
    <div ref="root">
        <PageHero eyebrow="GmodStore" lede="Production-ready Garry's Mod addons, built with the same care as client work. Bought and delivered through GmodStore, with updates included.">
            {{ site.store_heading || 'Addons for your server' }}
            <template #actions>
                <a v-if="site.gmodstore_url" :href="site.gmodstore_url" target="_blank" rel="noopener" class="btn btn-light btn-lg">View on GmodStore <span class="arrow-badge"><ArrowUpRight :size="14" /></span></a>
                <Link href="/contact" class="btn btn-glass btn-lg">Need something custom?</Link>
            </template>
        </PageHero>

        <section class="wrap pb-28 lg:pb-40">
            <dl class="mb-10 flex flex-wrap gap-x-10 gap-y-4" data-reveal>
                <div class="flex items-center gap-3"><Package :size="18" class="text-accent-hot" /><dt class="sr-only">Products</dt><dd><b class="display text-2xl">{{ totals.products }}</b> <span class="text-muted">{{ totals.products === 1 ? 'addon' : 'addons' }}</span></dd></div>
                <div v-if="totals.rating" class="flex items-center gap-3"><Star :size="18" class="fill-amber-300 text-amber-300" /><dt class="sr-only">Average rating</dt><dd><b class="display text-2xl">{{ totals.rating.toFixed(1) }}</b> <span class="text-muted">from {{ fmt(totals.reviews) }} reviews</span></dd></div>
                <div v-if="totals.sales" class="flex items-center gap-3"><ShoppingCart :size="18" class="text-accent-hot" /><dt class="sr-only">Sales</dt><dd><b class="display text-2xl">{{ fmt(totals.sales) }}</b> <span class="text-muted">servers running them</span></dd></div>
            </dl>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <ProductCard v-for="(p, i) in products" :key="p.id" :product="p" :large="i === 0 && products.length > 2" :class="i === 0 && products.length > 2 ? 'sm:col-span-2' : ''" data-reveal />
            </div>

            <p class="mt-10 flex items-center gap-2 text-sm text-muted" data-reveal><ShieldCheck :size="15" /> Checkout, licensing and downloads are handled securely by GmodStore. Prices shown in the store's currency and refreshed hourly.</p>
        </section>
    </div>
</template>
