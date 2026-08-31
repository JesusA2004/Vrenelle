<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{ title?: string; breadcrumbs?: BreadcrumbItem[] }>(),
    { title: '', breadcrumbs: () => [] },
);
</script>

<template>
    <Head :title="title || undefined" />

    <AppShell variant="sidebar">
        <AdminSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="p-6">
                <slot />
            </div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
