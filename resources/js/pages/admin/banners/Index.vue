<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
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

type Banner = {
    id: number;
    position: string;
    title: string | null;
    active: boolean;
    sort_order: number;
};

defineProps<{ banners: Banner[] }>();
const { confirmDestructive } = useSweetAlert();

const positionLabels: Record<string, string> = {
    hero: 'Hero (Home)',
    story: 'Storytelling',
    special_collection: 'Colección especial',
    strip: 'Franja',
};

async function destroy(banner: Banner): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar banner',
        text: `¿Eliminar "${banner.title ?? banner.position}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/banners/${banner.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Banners</h1>
                <p class="text-sm text-muted-foreground">
                    Controla las secciones visuales del Home.
                </p>
            </div>
            <Button as-child
                ><Link href="/admin/banners/create"
                    ><Plus class="mr-1.5 size-4" /> Nuevo banner</Link
                ></Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Posición</TableHead>
                    <TableHead>Título</TableHead>
                    <TableHead>Orden</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="banner in banners" :key="banner.id">
                    <TableCell>{{
                        positionLabels[banner.position] ?? banner.position
                    }}</TableCell>
                    <TableCell class="font-medium">{{
                        banner.title ?? '—'
                    }}</TableCell>
                    <TableCell>{{ banner.sort_order }}</TableCell>
                    <TableCell
                        ><span
                            :class="
                                banner.active
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                            >{{ banner.active ? 'Activo' : 'Inactivo' }}</span
                        ></TableCell
                    >
                    <TableCell class="space-x-2 text-right">
                        <Link
                            :href="`/admin/banners/${banner.id}/edit`"
                            class="text-sm underline"
                            >Editar</Link
                        >
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(banner)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
