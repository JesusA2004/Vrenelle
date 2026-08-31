<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type LegalPage = { id: number; slug: string; title: string };

defineProps<{ pages: LegalPage[] }>();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Páginas legales</h1>
            <p class="text-sm text-muted-foreground">
                Términos, privacidad, envíos, devoluciones, garantía y demás contenido legal
                visible en el pie de página de la tienda.
            </p>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Página</TableHead>
                    <TableHead>Slug</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="page in pages" :key="page.id">
                    <TableCell class="font-medium">{{ page.title }}</TableCell>
                    <TableCell class="text-muted-foreground">/legal/{{ page.slug }}</TableCell>
                    <TableCell class="text-right">
                        <Link :href="`/admin/paginas-legales/${page.id}/editar`" class="text-sm underline">Editar</Link>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
