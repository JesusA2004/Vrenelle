<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { vReveal } from '@/directives/reveal';

defineProps<{
    collection: {
        name: string;
        slug: string;
        shortDescription?: string | null;
        image: string | null;
    };
    delay?: number;
}>();
</script>

<template>
    <Link
        v-reveal="delay"
        :href="`/colecciones/${collection.slug}`"
        class="group relative block aspect-[3/4] overflow-hidden bg-ink"
    >
        <img
            v-if="collection.image"
            :src="collection.image"
            :alt="collection.name"
            loading="lazy"
            class="h-full w-full object-cover opacity-90 transition-transform duration-[1400ms] ease-out group-hover:scale-105"
        />
        <div
            class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/10 to-transparent"
        />

        <div class="absolute inset-x-0 bottom-0 p-7">
            <h3 class="text-display-sm font-display text-ivory">
                {{ collection.name }}
            </h3>
            <p
                v-if="collection.shortDescription"
                class="mt-2 max-w-xs text-sm text-ivory/80"
            >
                {{ collection.shortDescription }}
            </p>
            <span
                class="mt-4 inline-flex items-center gap-2 text-[0.7rem] tracking-[0.16em] text-ivory uppercase"
            >
                Explorar colección
                <ArrowRight
                    class="size-3.5 transition-transform duration-300 group-hover:translate-x-1"
                />
            </span>
        </div>
    </Link>
</template>
