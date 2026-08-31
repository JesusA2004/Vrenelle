<script setup lang="ts">
import { Heart } from '@lucide/vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';
import type { ProductCardData } from '@/components/store/ProductCard.vue';
import ProductCard from '@/components/store/ProductCard.vue';

defineProps<{ products: ProductCardData[] }>();
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 py-16 md:px-10">
        <h1 class="text-display-md font-display text-ink">Tus favoritos</h1>

        <div
            v-if="products.length === 0"
            class="flex flex-col items-center gap-4 py-24 text-center"
        >
            <Heart class="size-10 text-stone-light" />
            <p class="font-display text-xl text-ink">
                Aún no has guardado piezas
            </p>
            <p class="text-sm text-stone">
                Guarda las piezas que te inspiren para encontrarlas fácilmente
                después.
            </p>
            <LuxuryButton as="link" href="/joyeria" variant="outline"
                >Explorar colecciones</LuxuryButton
            >
        </div>

        <div
            v-else
            class="mt-12 grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-4"
        >
            <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
            />
        </div>
    </div>
</template>
