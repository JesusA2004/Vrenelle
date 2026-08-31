<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { ProductCardData } from '@/components/store/ProductCard.vue';
import ProductCard from '@/components/store/ProductCard.vue';
import SectionHeading from '@/components/store/SectionHeading.vue';
import { vReveal } from '@/directives/reveal';

type Paginated<T> = { data: T[] };

defineProps<{
    collection: {
        name: string;
        shortDescription: string | null;
        description: string | null;
        story: string | null;
        image: string | null;
        banner: string | null;
        metaTitle: string;
        metaDescription: string | null;
    };
    products: Paginated<ProductCardData>;
}>();
</script>

<template>
    <div>
        <Head>
            <title>{{ collection.metaTitle }}</title>
            <meta
                v-if="collection.metaDescription"
                name="description"
                :content="collection.metaDescription"
            />
        </Head>

        <section
            class="relative flex h-[75vh] min-h-[480px] items-end overflow-hidden bg-ink"
        >
            <img
                v-if="collection.banner ?? collection.image"
                :src="collection.banner ?? collection.image ?? ''"
                alt=""
                class="absolute inset-0 h-full w-full object-cover brightness-[0.85]"
            />
            <div
                class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"
            />
            <div
                v-reveal
                class="relative z-10 mx-auto w-full max-w-4xl px-6 pb-16 text-center md:px-10"
            >
                <h1 class="text-display-xl font-display text-ivory">
                    {{ collection.name }}
                </h1>
                <p
                    v-if="collection.shortDescription"
                    class="mx-auto mt-4 max-w-lg text-ivory/85"
                >
                    {{ collection.shortDescription }}
                </p>
            </div>
        </section>

        <section
            v-if="collection.description"
            class="mx-auto max-w-3xl px-6 py-20 text-center md:px-10"
        >
            <div
                v-reveal
                class="prose prose-neutral mx-auto text-[1.05rem] leading-relaxed text-ink"
                v-html="collection.description"
            />
        </section>

        <section v-if="collection.story" class="bg-ivory-deep py-20">
            <div v-reveal class="mx-auto max-w-3xl px-6 text-center md:px-10">
                <p class="text-eyebrow mb-4">La historia</p>
                <div
                    class="font-display text-2xl leading-snug text-ink"
                    v-html="collection.story"
                />
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 py-20 md:px-10">
            <SectionHeading
                eyebrow="Catálogo completo"
                :title="`Piezas de ${collection.name}`"
                align="center"
            />
            <div
                class="mt-12 grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-4"
            >
                <ProductCard
                    v-for="(product, i) in products.data"
                    :key="product.id"
                    :product="product"
                    :delay="(i % 4) * 80"
                />
            </div>
        </section>
    </div>
</template>
