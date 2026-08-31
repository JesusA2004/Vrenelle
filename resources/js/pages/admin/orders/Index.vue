<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Order = {
    id: number;
    orderNumber: string;
    customerName: string;
    email: string;
    status: string;
    statusLabel: string;
    paymentStatus: string;
    total: string;
    createdAt: string;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    orders: Paginated<Order>;
    statuses: Array<{ value: string; label: string }>;
    filters: {
        buscar?: string;
        estado?: string;
        desde?: string;
        hasta?: string;
    };
}>();

const search = ref(props.filters.buscar ?? '');
const estado = ref(props.filters.estado ?? '');

function applyFilters(): void {
    router.get(
        '/admin/pedidos',
        {
            ...props.filters,
            buscar: search.value || undefined,
            estado: estado.value || undefined,
        },
        { preserveState: true },
    );
}
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold">Pedidos</h1>

        <div class="flex flex-wrap gap-3">
            <Input
                v-model="search"
                placeholder="Buscar pedido, cliente o correo..."
                class="max-w-xs"
                @keyup.enter="applyFilters"
            />
            <select
                v-model="estado"
                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                @change="applyFilters"
            >
                <option value="">Todos los estados</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">
                    {{ s.label }}
                </option>
            </select>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Pedido</TableHead>
                    <TableHead>Cliente</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead>Pago</TableHead>
                    <TableHead>Total</TableHead>
                    <TableHead>Fecha</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="order in orders.data"
                    :key="order.id"
                    class="cursor-pointer"
                    @click="router.visit(`/admin/pedidos/${order.id}`)"
                >
                    <TableCell class="font-medium"
                        ><Link
                            :href="`/admin/pedidos/${order.id}`"
                            class="underline"
                            >{{ order.orderNumber }}</Link
                        ></TableCell
                    >
                    <TableCell
                        >{{ order.customerName }}<br /><span
                            class="text-xs text-muted-foreground"
                            >{{ order.email }}</span
                        ></TableCell
                    >
                    <TableCell>{{ order.statusLabel }}</TableCell>
                    <TableCell>{{ order.paymentStatus }}</TableCell>
                    <TableCell>{{ order.total }}</TableCell>
                    <TableCell>{{ order.createdAt }}</TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="orders.links" />
    </div>
</template>
