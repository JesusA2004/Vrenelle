<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type NavCategory = { name: string; slug: string; image: string | null };
type NavCollection = { name: string; slug: string; image: string | null };

defineProps<{ categories: NavCategory[]; collections: NavCollection[] }>();
defineEmits<{ close: [] }>();
</script>

<template>
    <div
        class="absolute inset-x-0 top-full z-40 border-t border-line bg-ivory shadow-[0_24px_48px_-24px_rgba(20,18,15,0.25)]"
        @mouseleave="$emit('close')"
    >
        <div class="mx-auto grid max-w-7xl grid-cols-12 gap-10 px-8 py-12">
            <div class="col-span-4">
                <p class="text-eyebrow mb-4">Joyería</p>
                <ul class="space-y-3">
                    <li v-for="category in categories" :key="category.slug">
                        <Link
                            :href="`/joyeria?categoria=${category.slug}`"
                            class="link-underline font-display text-xl text-ink"
                            @click="$emit('close')"
                        >
                            {{ category.name }}
                        </Link>
                    </li>
                    <li class="pt-2">
                        <Link
                            href="/joyeria"
                            class="link-underline text-sm text-stone"
                            @click="$emit('close')"
                        >
                            Ver todo el catálogo
                        </Link>
                    </li>
                </ul>
            </div>

            <div class="col-span-8">
                <p class="text-eyebrow mb-4">Colecciones destacadas</p>
                <div class="grid grid-cols-4 gap-5">
                    <Link
                        v-for="collection in collections"
                        :key="collection.slug"
                        :href="`/colecciones/${collection.slug}`"
                        class="image-zoom group block"
                        @click="$emit('close')"
                    >
                        <div class="aspect-[3/4] overflow-hidden bg-ivory-deep">
                            <img
                                v-if="collection.image"
                                :src="collection.image"
                                :alt="collection.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <p class="font-display mt-3 text-base text-ink">
                            {{ collection.name }}
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
