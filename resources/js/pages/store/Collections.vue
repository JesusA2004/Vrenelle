<script setup lang="ts">
import CollectionCard from '@/components/store/CollectionCard.vue';
import SectionHeading from '@/components/store/SectionHeading.vue';

defineProps<{
    collections: Array<{
        name: string;
        slug: string;
        shortDescription: string | null;
        image: string | null;
    }>;
}>();
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 py-16 md:px-10">
        <SectionHeading
            eyebrow="Universos Vrenelle"
            title="Colecciones"
            description="Cada colección construye su propio lenguaje: una manera distinta de entender el metal, la piedra y la forma."
        />
        <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <CollectionCard
                v-for="(collection, i) in collections"
                :key="collection.slug"
                :collection="collection"
                :delay="i * 80"
            />
        </div>
    </div>
</template>
