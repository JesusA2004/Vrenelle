<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Collection = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    is_featured: boolean;
    products_count: number;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    collections: Paginated<Collection>;
    filters: { buscar?: string };
}>();

const { confirmDestructive } = useSweetAlert();

async function destroy(collection: Collection): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar colección',
        text: `¿Eliminar "${collection.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/colecciones/${collection.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Colecciones</h1>
                <p class="text-sm text-muted-foreground">
                    Universos editoriales que agrupan piezas.
                </p>
            </div>
            <Button as-child
                ><Link href="/admin/colecciones/create"
                    ><Plus class="mr-1.5 size-4" /> Nueva colección</Link
                ></Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Productos</TableHead>
                    <TableHead>Destacada</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="collection in collections.data"
                    :key="collection.id"
                >
                    <TableCell class="font-medium">{{
                        collection.name
                    }}</TableCell>
                    <TableCell>{{ collection.products_count }}</TableCell>
                    <TableCell>{{
                        collection.is_featured ? 'Sí' : 'No'
                    }}</TableCell>
                    <TableCell>
                        <span
                            :class="
                                collection.is_active
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ collection.is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </TableCell>
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/colecciones/${collection.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(collection)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="collections.links" />
    </div>
</template>
