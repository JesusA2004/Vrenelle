<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    page: {
        title: string;
        content: string;
        metaTitle: string;
        metaDescription: string | null;
    };
}>();
</script>

<template>
    <div class="mx-auto max-w-3xl px-6 py-20 md:px-10">
        <Head>
            <title>{{ page.metaTitle }}</title>
            <meta
                v-if="page.metaDescription"
                name="description"
                :content="page.metaDescription"
            />
        </Head>

        <h1 class="text-display-md font-display text-ink">{{ page.title }}</h1>
        <div
            class="prose prose-neutral mt-8 max-w-none text-[0.95rem] leading-relaxed text-stone"
            v-html="page.content"
        />
    </div>
</template>
