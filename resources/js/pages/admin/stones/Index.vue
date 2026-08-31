<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Stone = {
    id: number;
    name: string;
    sort_order: number;
    is_active: boolean;
    products_count: number;
};

defineProps<{ stones: Stone[] }>();

const { confirmDestructive } = useSweetAlert();
const open = ref(false);
const editing = ref<Stone | null>(null);
const form = useForm({ name: '', sort_order: 0, is_active: true });

function openCreate(): void {
    editing.value = null;
    form.reset();
    open.value = true;
}

function openEdit(stone: Stone): void {
    editing.value = stone;
    form.name = stone.name;
    form.sort_order = stone.sort_order;
    form.is_active = stone.is_active;
    open.value = true;
}

function submit(): void {
    if (editing.value) {
        form.patch(`/admin/piedras/${editing.value.id}`, {
            onSuccess: () => (open.value = false),
        });
    } else {
        form.post('/admin/piedras', { onSuccess: () => (open.value = false) });
    }
}

async function destroy(stone: Stone): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar piedra',
        text: `¿Eliminar "${stone.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/piedras/${stone.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Piedras</h1>
                <p class="text-sm text-muted-foreground">
                    Gemas y piedras disponibles para tus piezas.
                </p>
            </div>
            <Button @click="openCreate"
                ><Plus class="mr-1.5 size-4" /> Nueva piedra</Button
            >
        </div>

        <div class="flex gap-4 border-b text-sm">
            <Link
                href="/admin/materiales"
                class="pb-2 text-muted-foreground hover:text-foreground"
                >Materiales</Link
            >
            <span class="border-b-2 border-primary pb-2 font-medium"
                >Piedras</span
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Productos</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="stone in stones" :key="stone.id">
                    <TableCell class="font-medium">{{ stone.name }}</TableCell>
                    <TableCell>{{ stone.products_count }}</TableCell>
                    <TableCell>{{
                        stone.is_active ? 'Activo' : 'Inactivo'
                    }}</TableCell>
                    <TableCell class="space-x-2 text-right">
                        <button
                            type="button"
                            class="text-sm underline"
                            @click="openEdit(stone)"
                        >
                            Editar
                        </button>
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(stone)"
                        >
                            Eliminar
                        </button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <Dialog v-model:open="open">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>{{
                        editing ? 'Editar piedra' : 'Nueva piedra'
                    }}</DialogTitle></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="s-name">Nombre</Label>
                        <Input id="s-name" v-model="form.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="s-order">Orden</Label>
                        <Input
                            id="s-order"
                            v-model.number="form.sort_order"
                            type="number"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <Switch id="s-active" v-model="form.is_active" />
                        <Label for="s-active">Activo</Label>
                    </div>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full"
                        >Guardar</Button
                    >
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
