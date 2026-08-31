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

type User = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    isActive: boolean;
    createdAt: string;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{ users: Paginated<User> }>();
const { confirmDestructive } = useSweetAlert();

async function destroy(user: User): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar usuario',
        text: `¿Eliminar a "${user.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/usuarios/${user.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Usuarios</h1>
            <Button as-child
                ><Link href="/admin/usuarios/create"
                    ><Plus class="mr-1.5 size-4" /> Nuevo usuario</Link
                ></Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Correo</TableHead>
                    <TableHead>Roles</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in users.data" :key="user.id">
                    <TableCell class="font-medium">{{ user.name }}</TableCell>
                    <TableCell>{{ user.email }}</TableCell>
                    <TableCell>{{ user.roles.join(', ') || '—' }}</TableCell>
                    <TableCell
                        ><span
                            :class="
                                user.isActive
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                            >{{ user.isActive ? 'Activo' : 'Inactivo' }}</span
                        ></TableCell
                    >
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/usuarios/${user.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(user)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <AdminPagination :links="users.links" />
    </div>
</template>
