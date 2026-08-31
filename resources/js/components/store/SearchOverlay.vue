<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Search, X } from '@lucide/vue';
import { nextTick, ref, watch } from 'vue';
import type { ProductCardData } from '@/components/store/ProductCard.vue';

const props = defineProps<{ open: boolean }>();
const emit = defineEmits<{ close: [] }>();

const query = ref('');
const inputRef = ref<HTMLInputElement | null>(null);
const loading = ref(false);
const results = ref<{
    products: ProductCardData[];
    categories: { name: string; slug: string }[];
    collections: { name: string; slug: string; image: string | null }[];
}>({ products: [], categories: [], collections: [] });

let debounceTimer: ReturnType<typeof setTimeout> | undefined;
let requestToken = 0;

watch(query, (value) => {
    clearTimeout(debounceTimer);

    if (value.trim().length < 2) {
        results.value = { products: [], categories: [], collections: [] };

        return;
    }

    debounceTimer = setTimeout(() => runSearch(value), 300);
});

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            await nextTick();
            inputRef.value?.focus();
        } else {
            query.value = '';
            results.value = { products: [], categories: [], collections: [] };
        }
    },
);

async function runSearch(term: string): Promise<void> {
    const token = ++requestToken;
    loading.value = true;

    try {
        const response = await fetch(`/buscar?q=${encodeURIComponent(term)}`, {
            headers: { Accept: 'application/json' },
        });

        if (token !== requestToken) {
            return;
        }

        results.value = await response.json();
    } finally {
        if (token === requestToken) {
            loading.value = false;
        }
    }
}

function close(): void {
    emit('close');
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="open" class="fixed inset-0 z-[70] bg-ivory">
            <button
                type="button"
                aria-label="Cerrar búsqueda"
                class="absolute top-6 right-6 text-ink md:top-10 md:right-10"
                @click="close"
            >
                <X class="size-6" />
            </button>

            <div
                class="mx-auto flex h-full max-w-3xl flex-col px-6 pt-24 md:pt-32"
            >
                <div class="flex items-center gap-4 border-b border-ink pb-4">
                    <Search class="size-5 text-stone" />
                    <input
                        ref="inputRef"
                        v-model="query"
                        type="text"
                        placeholder="¿Qué estás buscando?"
                        class="text-display-sm font-display w-full bg-transparent text-ink placeholder:text-stone-light focus:outline-none"
                        @keydown.escape="close"
                    />
                </div>

                <div class="mt-10 flex-1 overflow-y-auto pb-16">
                    <p
                        v-if="
                            query.length >= 2 &&
                            !loading &&
                            results.products.length === 0 &&
                            results.categories.length === 0
                        "
                        class="text-sm text-stone"
                    >
                        No encontramos piezas que coincidan con “{{ query }}”.
                    </p>

                    <div
                        v-if="results.categories.length"
                        class="mb-8 flex flex-wrap gap-2"
                    >
                        <Link
                            v-for="category in results.categories"
                            :key="category.slug"
                            :href="`/joyeria?categoria=${category.slug}`"
                            class="border border-line px-3 py-1.5 text-xs tracking-wide text-ink uppercase hover:border-ink"
                            @click="close"
                        >
                            {{ category.name }}
                        </Link>
                    </div>

                    <div
                        v-if="results.products.length"
                        class="grid grid-cols-2 gap-6 sm:grid-cols-3"
                    >
                        <Link
                            v-for="item in results.products"
                            :key="item.id"
                            :href="`/producto/${item.slug}`"
                            class="group"
                            @click="close"
                        >
                            <div
                                class="aspect-square overflow-hidden bg-ivory-deep"
                            >
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    :alt="item.name"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                />
                            </div>
                            <p class="mt-2 text-sm text-ink">{{ item.name }}</p>
                            <p v-if="item.price" class="text-xs text-stone">
                                {{ item.price }}
                            </p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
