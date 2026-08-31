<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { SlidersHorizontal, X } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Breadcrumb from '@/components/store/Breadcrumb.vue';
import type { ProductCardData } from '@/components/store/ProductCard.vue';
import ProductCard from '@/components/store/ProductCard.vue';
import { Sheet, SheetContent } from '@/components/ui/sheet';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    title: string;
    description: string | null;
    products: Paginated<ProductCardData>;
    filters: Record<string, string | undefined>;
    facets: {
        categories: { name: string; slug: string }[];
        collections: { name: string; slug: string }[];
        materials: { name: string; slug: string }[];
        stones: { name: string; slug: string }[];
    };
}>();

const form = reactive({
    categoria: props.filters.categoria ?? '',
    coleccion: props.filters.coleccion ?? '',
    material: props.filters.material ?? '',
    piedra: props.filters.piedra ?? '',
    orden: props.filters.orden ?? '',
    novedades: props.filters.novedades === '1',
    oferta: props.filters.oferta === '1',
});

const mobileFiltersOpen = ref(false);

function applyFilters(): void {
    router.get(
        '/joyeria',
        {
            categoria: form.categoria || undefined,
            coleccion: form.coleccion || undefined,
            material: form.material || undefined,
            piedra: form.piedra || undefined,
            orden: form.orden || undefined,
            novedades: form.novedades ? 1 : undefined,
            oferta: form.oferta ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
    mobileFiltersOpen.value = false;
}

function clearFilters(): void {
    form.categoria = '';
    form.coleccion = '';
    form.material = '';
    form.piedra = '';
    form.orden = '';
    form.novedades = false;
    form.oferta = false;
    applyFilters();
}

function goToPage(url: string | null): void {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 py-12 md:px-10">
        <Breadcrumb :items="[{ label: title, href: null }]" />

        <div class="mt-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-display-md font-display text-ink">
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm text-stone">
                    {{ products.total }} piezas
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 border border-line px-4 py-2 text-xs tracking-[0.14em] text-ink uppercase lg:hidden"
                @click="mobileFiltersOpen = true"
            >
                <SlidersHorizontal class="size-3.5" /> Filtros
            </button>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-12 lg:grid-cols-[240px_1fr]">
            <!-- Desktop filters -->
            <aside class="hidden space-y-8 lg:block">
                <div>
                    <p class="text-eyebrow mb-3">Categoría</p>
                    <div class="space-y-2 text-sm">
                        <label
                            v-for="c in facets.categories"
                            :key="c.slug"
                            class="flex cursor-pointer items-center gap-2"
                        >
                            <input
                                v-model="form.categoria"
                                type="radio"
                                :value="c.slug"
                                name="categoria"
                                @change="applyFilters"
                            />
                            <span class="text-ink">{{ c.name }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <p class="text-eyebrow mb-3">Colección</p>
                    <div class="space-y-2 text-sm">
                        <label
                            v-for="c in facets.collections"
                            :key="c.slug"
                            class="flex cursor-pointer items-center gap-2"
                        >
                            <input
                                v-model="form.coleccion"
                                type="radio"
                                :value="c.slug"
                                name="coleccion"
                                @change="applyFilters"
                            />
                            <span class="text-ink">{{ c.name }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <p class="text-eyebrow mb-3">Material</p>
                    <div class="space-y-2 text-sm">
                        <label
                            v-for="m in facets.materials"
                            :key="m.slug"
                            class="flex cursor-pointer items-center gap-2"
                        >
                            <input
                                v-model="form.material"
                                type="radio"
                                :value="m.slug"
                                name="material"
                                @change="applyFilters"
                            />
                            <span class="text-ink">{{ m.name }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <p class="text-eyebrow mb-3">Piedra</p>
                    <div class="space-y-2 text-sm">
                        <label
                            v-for="s in facets.stones"
                            :key="s.slug"
                            class="flex cursor-pointer items-center gap-2"
                        >
                            <input
                                v-model="form.piedra"
                                type="radio"
                                :value="s.slug"
                                name="piedra"
                                @change="applyFilters"
                            />
                            <span class="text-ink">{{ s.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label
                        class="flex cursor-pointer items-center gap-2 text-sm"
                    >
                        <input
                            v-model="form.novedades"
                            type="checkbox"
                            @change="applyFilters"
                        />
                        Novedades
                    </label>
                    <label
                        class="flex cursor-pointer items-center gap-2 text-sm"
                    >
                        <input
                            v-model="form.oferta"
                            type="checkbox"
                            @change="applyFilters"
                        />
                        En oferta
                    </label>
                </div>

                <button
                    type="button"
                    class="link-underline text-xs text-stone"
                    @click="clearFilters"
                >
                    Limpiar filtros
                </button>
            </aside>

            <div>
                <div class="mb-8 flex justify-end">
                    <select
                        v-model="form.orden"
                        class="border border-line bg-transparent px-3 py-2 text-xs tracking-wide text-ink uppercase"
                        @change="applyFilters"
                    >
                        <option value="">Relevancia</option>
                        <option value="novedades">Novedades</option>
                        <option value="precio_asc">
                            Precio: menor a mayor
                        </option>
                        <option value="precio_desc">
                            Precio: mayor a menor
                        </option>
                    </select>
                </div>

                <div
                    v-if="products.data.length"
                    class="grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3"
                >
                    <ProductCard
                        v-for="(product, i) in products.data"
                        :key="product.id"
                        :product="product"
                        :delay="(i % 6) * 60"
                    />
                </div>
                <div v-else class="py-24 text-center">
                    <p class="font-display text-xl text-ink">
                        No encontramos piezas con estos filtros
                    </p>
                    <p class="mt-2 text-sm text-stone">
                        Intenta ajustar tu búsqueda o explora otra categoría.
                    </p>
                </div>

                <nav
                    v-if="products.last_page > 1"
                    class="mt-16 flex flex-wrap justify-center gap-2"
                >
                    <button
                        v-for="(link, i) in products.links"
                        :key="i"
                        type="button"
                        :disabled="!link.url"
                        class="min-w-9 border px-3 py-2 text-xs"
                        :class="
                            link.active
                                ? 'border-ink bg-ink text-ivory'
                                : 'border-line text-ink hover:border-ink disabled:opacity-30'
                        "
                        v-html="link.label"
                        @click="goToPage(link.url)"
                    />
                </nav>
            </div>
        </div>

        <!-- Mobile filters drawer -->
        <Sheet v-model:open="mobileFiltersOpen">
            <SheetContent
                side="bottom"
                class="max-h-[85vh] gap-0 overflow-y-auto rounded-t-2xl border-line bg-ivory p-6"
            >
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="font-display text-lg text-ink">Filtros</h2>
                    <button type="button" @click="mobileFiltersOpen = false">
                        <X class="size-5" />
                    </button>
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-eyebrow mb-3">Categoría</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="c in facets.categories"
                                :key="c.slug"
                                type="button"
                                class="border px-3 py-1.5 text-xs uppercase"
                                :class="
                                    form.categoria === c.slug
                                        ? 'border-ink bg-ink text-ivory'
                                        : 'border-line text-ink'
                                "
                                @click="
                                    form.categoria =
                                        form.categoria === c.slug ? '' : c.slug
                                "
                            >
                                {{ c.name }}
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="w-full bg-ink py-3 text-xs tracking-[0.14em] text-ivory uppercase"
                        @click="applyFilters"
                    >
                        Ver piezas
                    </button>
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
