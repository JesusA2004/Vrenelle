<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import WishlistButton from '@/components/store/WishlistButton.vue';
import { vReveal } from '@/directives/reveal';

export type ProductCardData = {
    id: number;
    slug: string;
    name: string;
    collection?: { name: string; slug: string } | null;
    material?: string | null;
    image: string | null;
    imageHover?: string | null;
    price: string | null;
    compareAtPrice?: string | null;
    badges: string[];
    available: boolean;
};

defineProps<{ product: ProductCardData; delay?: number }>();
</script>

<template>
    <article v-reveal="delay" class="group">
        <Link :href="`/producto/${product.slug}`" class="block">
            <div
                class="image-zoom image-crossfade relative aspect-[4/5] overflow-hidden bg-ivory-deep"
            >
                <img
                    v-if="product.image"
                    :src="product.image"
                    :alt="product.name"
                    loading="lazy"
                    class="image-primary h-full w-full object-cover"
                />
                <img
                    v-if="product.imageHover"
                    :src="product.imageHover"
                    :alt="product.name"
                    loading="lazy"
                    class="image-secondary absolute inset-0 h-full w-full object-cover"
                />

                <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                    <span
                        v-for="badge in product.badges"
                        :key="badge"
                        class="bg-ivory/90 px-2.5 py-1 text-[0.62rem] font-medium tracking-[0.12em] text-ink uppercase backdrop-blur"
                    >
                        {{ badge }}
                    </span>
                </div>

                <div
                    class="absolute top-3 right-3 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                >
                    <WishlistButton :product-id="product.id" size="sm" />
                </div>

                <div
                    v-if="!product.available"
                    class="absolute inset-x-0 bottom-0 bg-ink/85 py-2 text-center text-[0.65rem] tracking-[0.14em] text-ivory uppercase backdrop-blur-sm"
                >
                    Disponible bajo pedido
                </div>
            </div>

            <div class="mt-4 space-y-1">
                <p v-if="product.collection" class="text-eyebrow">
                    {{ product.collection.name }}
                </p>
                <h3 class="font-display text-lg text-ink">
                    {{ product.name }}
                </h3>
                <p v-if="product.material" class="text-xs text-stone">
                    {{ product.material }}
                </p>
                <div class="flex items-baseline gap-2 pt-1">
                    <span v-if="product.price" class="text-sm text-ink">{{
                        product.price
                    }}</span>
                    <span v-else class="text-sm text-stone italic"
                        >Precio bajo consulta</span
                    >
                    <span
                        v-if="product.compareAtPrice"
                        class="text-xs text-stone line-through"
                    >
                        {{ product.compareAtPrice }}
                    </span>
                </div>
            </div>
        </Link>
    </article>
</template>
