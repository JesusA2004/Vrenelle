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

type Promotion = {
    id: number;
    name: string;
    type: string;
    value: string;
    active: boolean;
    used_count: number;
    usage_limit: number | null;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{ promotions: Paginated<Promotion> }>();
const { confirmDestructive } = useSweetAlert();

async function destroy(promotion: Promotion): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar promoción',
        text: `¿Eliminar "${promotion.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/promociones/${promotion.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Promociones</h1>
            <Button as-child
                ><Link href="/admin/promociones/create"
                    ><Plus class="mr-1.5 size-4" /> Nueva promoción</Link
                ></Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Tipo</TableHead>
                    <TableHead>Valor</TableHead>
                    <TableHead>Usos</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="promotion in promotions.data"
                    :key="promotion.id"
                >
                    <TableCell class="font-medium">{{
                        promotion.name
                    }}</TableCell>
                    <TableCell>{{ promotion.type }}</TableCell>
                    <TableCell>{{ promotion.value }}</TableCell>
                    <TableCell
                        >{{ promotion.used_count
                        }}{{
                            promotion.usage_limit
                                ? ` / ${promotion.usage_limit}`
                                : ''
                        }}</TableCell
                    >
                    <TableCell
                        ><span
                            :class="
                                promotion.active
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                            >{{
                                promotion.active ? 'Activa' : 'Inactiva'
                            }}</span
                        ></TableCell
                    >
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/promociones/${promotion.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(promotion)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="promotions.links" />
    </div>
</template>
