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

type Customer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    createdAt: string;
    ordersCount: number;
    totalSpent: string;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    customers: Paginated<Customer>;
    filters: { buscar?: string };
}>();
const search = ref(props.filters.buscar ?? '');

function applySearch(): void {
    router.get(
        '/admin/clientes',
        { buscar: search.value || undefined },
        { preserveState: true },
    );
}
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold">Clientes</h1>
        <Input
            v-model="search"
            placeholder="Buscar cliente..."
            class="max-w-xs"
            @keyup.enter="applySearch"
        />
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Correo</TableHead>
                    <TableHead>Registro</TableHead>
                    <TableHead>Pedidos</TableHead>
                    <TableHead>Total gastado</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="customer in customers.data"
                    :key="customer.id"
                    class="cursor-pointer"
                    @click="router.visit(`/admin/clientes/${customer.id}`)"
                >
                    <TableCell class="font-medium"
                        ><Link
                            :href="`/admin/clientes/${customer.id}`"
                            class="underline"
                            >{{ customer.name }}</Link
                        ></TableCell
                    >
                    <TableCell>{{ customer.email }}</TableCell>
                    <TableCell>{{ customer.createdAt }}</TableCell>
                    <TableCell>{{ customer.ordersCount }}</TableCell>
                    <TableCell>{{ customer.totalSpent }}</TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="customers.links" />
    </div>
</template>
