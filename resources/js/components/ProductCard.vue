<script setup>
import { computed } from 'vue';
import { ArrowUpRight, Package, ShoppingCart, Star } from 'lucide-vue-next';

const props = defineProps({ product: Object, large: Boolean });

const price = (minor) => (minor == null ? null : new Intl.NumberFormat('en-GB', { style: 'currency', currency: props.product.currency || 'USD' }).format(minor / 100));
const now = computed(() => price(props.product.price));
const was = computed(() => price(props.product.original_price));
const off = computed(() => (props.product.original_price ? Math.round((1 - props.product.price / props.product.original_price) * 100) : 0));
const sales = computed(() => {
    const n = props.product.sales;
    if (!n) return null;
    const s = n >= 1000 ? `${(n / 1000).toFixed(n >= 10000 ? 0 : 1)}k` : String(n);
    return `${s}${props.product.sales_capped ? '+' : ''} sold`;
});
</script>

<template>
    <a :href="product.url" target="_blank" rel="noopener" v-spotlight class="group card flex flex-col overflow-hidden p-2.5 transition-colors hover:bg-white/[0.05]">
        <div class="relative overflow-hidden rounded-[1.1rem] bg-surface-2" :class="large ? 'aspect-[16/9]' : 'aspect-[16/10]'">
            <img v-if="product.image" :src="product.image" :alt="product.name" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1.2s] ease-out-expo group-hover:scale-[1.05]" />
            <Package v-else :size="32" class="absolute inset-0 m-auto text-faint" />
            <div class="pointer-events-none absolute inset-0 rounded-[1.1rem] shadow-[inset_0_0_0_1px_rgb(255_255_255/.08)]"></div>
            <span v-if="off >= 5" class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold text-white shadow-lg" style="background: var(--gradient)">−{{ off }}%</span>
            <span v-if="product.featured && !off" class="absolute left-3 top-3 rounded-full bg-black/55 px-2.5 py-1 text-xs font-medium text-white backdrop-blur">Featured</span>
        </div>
        <div class="flex flex-1 flex-col p-3.5 pt-4">
            <div class="flex items-start justify-between gap-3">
                <h3 class="title text-lg leading-snug">{{ product.name }}</h3>
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/[0.06] text-muted transition-all duration-500 ease-out-expo group-hover:rotate-45 group-hover:bg-white group-hover:text-bg"><ArrowUpRight :size="15" /></span>
            </div>
            <p v-if="product.tagline" class="mt-2 line-clamp-2 text-sm leading-relaxed text-text-2">{{ product.tagline }}</p>
            <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-5">
                <div class="flex items-baseline gap-2">
                    <span class="display text-2xl">{{ now ?? 'Free' }}</span>
                    <span v-if="was" class="text-sm text-muted line-through">{{ was }}</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-muted">
                    <span v-if="product.rating" class="inline-flex items-center gap-1"><Star :size="13" class="fill-amber-300 text-amber-300" /><b class="font-medium text-text">{{ product.rating.toFixed(1) }}</b> ({{ product.reviews }})</span>
                    <span v-if="sales" class="inline-flex items-center gap-1"><ShoppingCart :size="13" />{{ sales }}</span>
                </div>
            </div>
        </div>
    </a>
</template>
