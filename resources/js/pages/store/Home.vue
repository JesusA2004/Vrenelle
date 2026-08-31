<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import CollectionCard from '@/components/store/CollectionCard.vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';
import type { ProductCardData } from '@/components/store/ProductCard.vue';
import ProductCard from '@/components/store/ProductCard.vue';
import SectionHeading from '@/components/store/SectionHeading.vue';
import { vReveal } from '@/directives/reveal';

type Banner = {
    title: string | null;
    subtitle: string | null;
    description: string | null;
    desktopMedia: string | null;
    mobileMedia: string | null;
    ctaText: string | null;
    ctaUrl: string | null;
    contentPosition: string;
    textTheme: string;
    overlay: string | null;
} | null;

defineProps<{
    hero: Banner;
    story: Banner;
    special: Banner;
    collections: Array<{
        name: string;
        slug: string;
        shortDescription: string | null;
        image: string | null;
    }>;
    featured: ProductCardData[];
    newArrivals: ProductCardData[];
    bestSellers: ProductCardData[];
}>();
</script>

<template>
    <div>
        <!-- HERO -->
        <section
            class="relative flex h-[92vh] min-h-[560px] items-center overflow-hidden bg-ink"
        >
            <picture v-if="hero?.desktopMedia" class="absolute inset-0">
                <source
                    :srcset="hero.mobileMedia ?? hero.desktopMedia"
                    media="(max-width: 767px)"
                />
                <img
                    :src="hero.desktopMedia"
                    alt=""
                    class="h-full w-full scale-105 object-cover brightness-[0.8]"
                    style="animation: heroZoom 16s ease-out forwards"
                />
            </picture>
            <div
                class="absolute inset-0"
                :style="{
                    backgroundColor: hero?.overlay ?? 'rgba(15,13,10,.4)',
                }"
            />
            <!--
                Dedicated legibility scrim for the header — independent of the
                mood overlay above, which is tuned for how the hero looks, not
                for guaranteeing contrast against whatever image gets uploaded.
            -->
            <div
                class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-black/45 to-transparent"
            />

            <div
                class="relative z-10 mx-auto w-full max-w-7xl px-6 md:px-10"
                :class="{
                    'mx-auto text-center': hero?.contentPosition === 'center',
                    'ml-auto text-right': hero?.contentPosition === 'right',
                }"
            >
                <div
                    class="max-w-xl"
                    :class="hero?.contentPosition === 'right' ? 'ml-auto' : ''"
                >
                    <p
                        v-if="hero?.subtitle"
                        v-reveal
                        class="text-eyebrow mb-5 text-ivory/80"
                    >
                        {{ hero.subtitle }}
                    </p>
                    <h1
                        v-if="hero?.title"
                        v-reveal="80"
                        class="text-display-xl font-display text-ivory"
                    >
                        {{ hero.title }}
                    </h1>
                    <p
                        v-if="hero?.description"
                        v-reveal="160"
                        class="mt-6 max-w-md text-[0.95rem] leading-relaxed text-ivory/85"
                    >
                        {{ hero.description }}
                    </p>
                    <div v-reveal="240" class="mt-9">
                        <LuxuryButton
                            v-if="hero?.ctaUrl"
                            as="link"
                            :href="hero.ctaUrl"
                            variant="secondary"
                        >
                            {{ hero.ctaText ?? 'Descubrir' }}
                        </LuxuryButton>
                        <LuxuryButton
                            v-else
                            as="link"
                            href="/joyeria"
                            variant="secondary"
                        >
                            Descubrir la colección
                        </LuxuryButton>
                    </div>
                </div>
            </div>
        </section>

        <!-- COLECCIONES PRINCIPALES -->
        <section class="mx-auto max-w-7xl px-6 py-24 md:px-10">
            <SectionHeading
                eyebrow="Universos Vrenelle"
                title="Colecciones principales"
                description="Cada colección propone un lenguaje propio: una manera distinta de entender el metal, la piedra y la forma."
                align="center"
            />
            <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-3">
                <CollectionCard
                    v-for="(collection, i) in collections"
                    :key="collection.slug"
                    :collection="collection"
                    :delay="i * 100"
                />
            </div>
        </section>

        <!-- DESTACADOS -->
        <section v-if="featured.length" class="bg-ivory-deep py-24">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <div class="flex items-end justify-between">
                    <SectionHeading
                        eyebrow="Selección"
                        title="Piezas destacadas"
                    />
                    <Link
                        href="/joyeria"
                        class="link-underline hidden text-sm text-ink sm:inline-flex sm:items-center sm:gap-1"
                    >
                        Ver catálogo <ArrowRight class="size-3.5" />
                    </Link>
                </div>
                <div
                    class="mt-12 grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="(product, i) in featured"
                        :key="product.id"
                        :product="product"
                        :delay="(i % 4) * 80"
                    />
                </div>
            </div>
        </section>

        <!-- STORYTELLING -->
        <section v-if="story" class="relative grid grid-cols-1 md:grid-cols-2">
            <div
                class="aspect-[4/3] md:aspect-auto"
                :class="story.contentPosition === 'right' ? 'md:order-2' : ''"
            >
                <img
                    v-if="story.desktopMedia"
                    :src="story.desktopMedia"
                    alt=""
                    class="h-full w-full object-cover"
                />
            </div>
            <div class="flex items-center bg-ink px-8 py-16 md:px-16">
                <div v-reveal>
                    <p class="text-eyebrow mb-4 text-ivory/70">
                        {{ story.subtitle }}
                    </p>
                    <h2 class="text-display-md font-display text-ivory">
                        {{ story.title }}
                    </h2>
                    <p
                        class="mt-6 max-w-md text-[0.95rem] leading-relaxed text-ivory/80"
                    >
                        {{ story.description }}
                    </p>
                    <LuxuryButton
                        v-if="story.ctaUrl"
                        as="link"
                        :href="story.ctaUrl"
                        variant="outline"
                        class="mt-8! border-ivory! text-ivory! hover:bg-ivory! hover:text-ink!"
                    >
                        {{ story.ctaText ?? 'Conocer más' }}
                    </LuxuryButton>
                </div>
            </div>
        </section>

        <!-- NOVEDADES -->
        <section
            v-if="newArrivals.length"
            class="mx-auto max-w-7xl px-6 py-24 md:px-10"
        >
            <SectionHeading
                eyebrow="Recién llegadas"
                title="Novedades"
                align="center"
            />
            <div
                class="mt-12 grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-4"
            >
                <ProductCard
                    v-for="(product, i) in newArrivals"
                    :key="product.id"
                    :product="product"
                    :delay="(i % 4) * 80"
                />
            </div>
        </section>

        <!-- COLECCIÓN ESPECIAL / FULLSCREEN -->
        <section
            v-if="special"
            class="relative flex h-[70vh] min-h-[420px] items-center justify-center overflow-hidden bg-ink text-center"
        >
            <img
                v-if="special.desktopMedia"
                :src="special.desktopMedia"
                alt=""
                class="absolute inset-0 h-full w-full object-cover brightness-[0.75]"
            />
            <div
                class="absolute inset-0"
                :style="{
                    backgroundColor: special.overlay ?? 'rgba(15,13,10,.45)',
                }"
            />
            <div v-reveal class="relative z-10 mx-auto max-w-lg px-6">
                <p class="text-eyebrow mb-4 text-ivory/80">
                    {{ special.subtitle }}
                </p>
                <h2 class="text-display-lg font-display text-ivory">
                    {{ special.title }}
                </h2>
                <p class="mt-6 text-[0.95rem] text-ivory/85">
                    {{ special.description }}
                </p>
                <LuxuryButton
                    v-if="special.ctaUrl"
                    as="link"
                    :href="special.ctaUrl"
                    variant="secondary"
                    class="mt-8"
                >
                    {{ special.ctaText ?? 'Descubrir' }}
                </LuxuryButton>
            </div>
        </section>

        <!-- MÁS VENDIDOS -->
        <section v-if="bestSellers.length" class="bg-ivory-deep py-24">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <SectionHeading
                    eyebrow="Favoritas de nuestros clientes"
                    title="Más buscadas"
                />
                <div
                    class="mt-12 grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="(product, i) in bestSellers"
                        :key="product.id"
                        :product="product"
                        :delay="(i % 4) * 80"
                    />
                </div>
            </div>
        </section>

        <!-- EXPERIENCIA / MANUFACTURA -->
        <section class="mx-auto max-w-5xl px-6 py-24 text-center md:px-10">
            <SectionHeading
                eyebrow="Manufactura"
                title="Una pieza, doce procesos manuales"
                description="Desde el boceto hasta el pulido final, cada pieza Vrenelle pasa por manos expertas. Trabajamos con metales certificados y piedras de origen responsable."
                align="center"
            />
            <div class="mt-14 grid grid-cols-1 gap-10 sm:grid-cols-3">
                <div v-reveal>
                    <p class="font-display text-display-sm text-ink">18K</p>
                    <p class="mt-2 text-sm text-stone">
                        Oro certificado en cada pieza de metal precioso
                    </p>
                </div>
                <div v-reveal="100">
                    <p class="font-display text-display-sm text-ink">2 años</p>
                    <p class="mt-2 text-sm text-stone">
                        De garantía contra defectos de fabricación
                    </p>
                </div>
                <div v-reveal="200">
                    <p class="font-display text-display-sm text-ink">100%</p>
                    <p class="mt-2 text-sm text-stone">
                        Piezas inspeccionadas individualmente antes de enviarse
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes heroZoom {
    from {
        transform: scale(1.08);
    }
    to {
        transform: scale(1);
    }
}

@media (prefers-reduced-motion: reduce) {
    picture img {
        animation: none !important;
    }
}
</style>
