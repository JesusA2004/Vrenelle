<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Category = {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
    products_count: number;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    categories: Paginated<Category>;
    filters: { buscar?: string };
}>();

const search = ref(props.filters.buscar ?? '');
const { confirmDestructive } = useSweetAlert();

function applySearch(): void {
    router.get(
        '/admin/categorias',
        { buscar: search.value || undefined },
        { preserveState: true },
    );
}

async function destroy(category: Category): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar categoría',
        text: `¿Eliminar "${category.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/categorias/${category.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Categorías</h1>
                <p class="text-sm text-muted-foreground">
                    Organiza el catálogo por tipo de joya.
                </p>
            </div>
            <Button as-child
                ><Link href="/admin/categorias/create"
                    ><Plus class="mr-1.5 size-4" /> Nueva categoría</Link
                ></Button
            >
        </div>

        <Input
            v-model="search"
            placeholder="Buscar categoría..."
            class="max-w-xs"
            @keyup.enter="applySearch"
        />
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Slug</TableHead>
                    <TableHead>Productos</TableHead>
                    <TableHead>Orden</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="category in categories.data"
                    :key="category.id"
                >
                    <TableCell class="font-medium">{{
                        category.name
                    }}</TableCell>
                    <TableCell class="text-muted-foreground">{{
                        category.slug
                    }}</TableCell>
                    <TableCell>{{ category.products_count }}</TableCell>
                    <TableCell>{{ category.sort_order }}</TableCell>
                    <TableCell>
                        <span
                            :class="
                                category.is_active
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ category.is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </TableCell>
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/categorias/${category.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(category)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="categories.links" />
    </div>
</template>
