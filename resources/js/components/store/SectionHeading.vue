<script setup lang="ts">
import { vReveal } from '@/directives/reveal';

withDefaults(
    defineProps<{
        eyebrow?: string;
        title: string;
        description?: string;
        align?: 'left' | 'center';
    }>(),
    { align: 'left' },
);
</script>

<template>
    <div
        v-reveal
        class="max-w-2xl"
        :class="align === 'center' ? 'mx-auto text-center' : ''"
    >
        <p v-if="eyebrow" class="text-eyebrow mb-3">{{ eyebrow }}</p>
        <h2 class="text-display-md font-display text-ink">{{ title }}</h2>
        <p
            v-if="description"
            class="mt-4 text-[0.95rem] leading-relaxed text-stone"
        >
            {{ description }}
        </p>
    </div>
</template>
