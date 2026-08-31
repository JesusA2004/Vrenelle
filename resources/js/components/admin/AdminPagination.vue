<script setup lang="ts">
import { router } from '@inertiajs/vue3';

type Link = { url: string | null; label: string; active: boolean };

defineProps<{ links: Link[] }>();

function go(url: string | null): void {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}
</script>

<template>
    <nav v-if="links.length > 3" class="mt-4 flex flex-wrap items-center gap-1">
        <button
            v-for="(link, i) in links"
            :key="i"
            type="button"
            :disabled="!link.url"
            class="min-w-8 rounded-md border px-2.5 py-1.5 text-xs"
            :class="
                link.active
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border text-foreground hover:bg-muted disabled:opacity-30'
            "
            v-html="link.label"
            @click="go(link.url)"
        />
    </nav>
</template>
