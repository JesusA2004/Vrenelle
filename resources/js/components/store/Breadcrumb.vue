<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{ items: Array<{ label: string; href: string | null }> }>();
</script>

<template>
    <nav aria-label="Breadcrumb" class="text-xs text-stone">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li>
                <Link href="/" class="link-underline hover:text-ink"
                    >Inicio</Link
                >
            </li>
            <li
                v-for="item in items"
                :key="item.label"
                class="flex items-center gap-1.5"
            >
                <span aria-hidden="true">/</span>
                <Link
                    v-if="item.href"
                    :href="item.href"
                    class="link-underline hover:text-ink"
                >
                    {{ item.label }}
                </Link>
                <span v-else class="text-ink">{{ item.label }}</span>
            </li>
        </ol>
    </nav>
</template>
