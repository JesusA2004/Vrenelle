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

type Product = {
    id: number;
    name: string;
    sku: string;
    image: string | null;
    category: string | null;
    collection: string | null;
    price: string;
    stock: number;
    inventoryMode: string;
    isActive: boolean;
    isLowStock: boolean;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    products: Paginated<Product>;
    categories: Array<{ id: number; name: string }>;
    filters: { buscar?: string; categoria?: string; estado?: string };
}>();

const search = ref(props.filters.buscar ?? '');
const { confirmDestructive } = useSweetAlert();

function applySearch(): void {
    router.get(
        '/admin/productos',
        { ...props.filters, buscar: search.value || undefined },
        { preserveState: true },
    );
}

async function destroy(product: Product): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar producto',
        text: `¿Eliminar "${product.name}"? Esta acción se puede revertir desde la base de datos por un desarrollador.`,
    });

    if (confirmed) {
        router.delete(`/admin/productos/${product.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Productos</h1>
                <p class="text-sm text-muted-foreground">
                    Catálogo completo de piezas.
                </p>
            </div>
            <Button as-child
                ><Link href="/admin/productos/create"
                    ><Plus class="mr-1.5 size-4" /> Nuevo producto</Link
                ></Button
            >
        </div>

        <Input
            v-model="search"
            placeholder="Buscar por nombre o SKU..."
            class="max-w-xs"
            @keyup.enter="applySearch"
        />
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Producto</TableHead>
                    <TableHead>Categoría</TableHead>
                    <TableHead>Colección</TableHead>
                    <TableHead>Precio</TableHead>
                    <TableHead>Inventario</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="product in products.data" :key="product.id">
                    <TableCell class="flex items-center gap-3">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            class="size-10 rounded object-cover"
                            alt=""
                        />
                        <div>
                            <p class="font-medium">{{ product.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ product.sku }}
                            </p>
                        </div>
                    </TableCell>
                    <TableCell>{{ product.category ?? '—' }}</TableCell>
                    <TableCell>{{ product.collection ?? '—' }}</TableCell>
                    <TableCell>{{ product.price }}</TableCell>
                    <TableCell>
                        <span
                            v-if="product.inventoryMode === 'stock'"
                            :class="product.isLowStock ? 'text-amber-600' : ''"
                        >
                            {{ product.stock }}
                            {{ product.isLowStock ? '(bajo)' : '' }}
                        </span>
                        <span v-else class="text-muted-foreground">{{
                            product.inventoryMode === 'made_to_order'
                                ? 'Bajo pedido'
                                : 'Solo consulta'
                        }}</span>
                    </TableCell>
                    <TableCell>
                        <span
                            :class="
                                product.isActive
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                            >{{
                                product.isActive ? 'Activo' : 'Inactivo'
                            }}</span
                        >
                    </TableCell>
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/productos/${product.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(product)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="products.links" />
    </div>
</template>
