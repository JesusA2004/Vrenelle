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

type Coupon = {
    id: number;
    code: string;
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

defineProps<{ coupons: Paginated<Coupon> }>();
const { confirmDestructive } = useSweetAlert();

async function destroy(coupon: Coupon): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar cupón',
        text: `¿Eliminar "${coupon.code}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/cupones/${coupon.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Cupones</h1>
            <Button as-child
                ><Link href="/admin/cupones/create"
                    ><Plus class="mr-1.5 size-4" /> Nuevo cupón</Link
                ></Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Código</TableHead>
                    <TableHead>Tipo</TableHead>
                    <TableHead>Valor</TableHead>
                    <TableHead>Usos</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="coupon in coupons.data" :key="coupon.id">
                    <TableCell class="font-medium">{{ coupon.code }}</TableCell>
                    <TableCell>{{ coupon.type }}</TableCell>
                    <TableCell>{{ coupon.value }}</TableCell>
                    <TableCell
                        >{{ coupon.used_count
                        }}{{
                            coupon.usage_limit ? ` / ${coupon.usage_limit}` : ''
                        }}</TableCell
                    >
                    <TableCell
                        ><span
                            :class="
                                coupon.active
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                            >{{ coupon.active ? 'Activo' : 'Inactivo' }}</span
                        ></TableCell
                    >
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/cupones/${coupon.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(coupon)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="coupons.links" />
    </div>
</template>
