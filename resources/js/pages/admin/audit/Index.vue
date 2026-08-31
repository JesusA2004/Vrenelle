<script setup lang="ts">
import AdminPagination from '@/components/admin/AdminPagination.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Log = {
    id: number;
    user: string;
    action: string;
    entity: string;
    entityId: number;
    ip: string | null;
    createdAt: string;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{ logs: Paginated<Log> }>();
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold">Auditoría</h1>
        <p class="text-sm text-muted-foreground">
            Registro de cambios sobre productos, precios, inventario, pedidos,
            promociones, usuarios y configuración.
        </p>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Usuario</TableHead>
                    <TableHead>Acción</TableHead>
                    <TableHead>Entidad</TableHead>
                    <TableHead>IP</TableHead>
                    <TableHead>Fecha</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="log in logs.data" :key="log.id">
                    <TableCell>{{ log.user }}</TableCell>
                    <TableCell>{{ log.action }}</TableCell>
                    <TableCell>{{ log.entity }} #{{ log.entityId }}</TableCell>
                    <TableCell class="text-muted-foreground">{{
                        log.ip
                    }}</TableCell>
                    <TableCell>{{ log.createdAt }}</TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="logs.links" />
    </div>
</template>
