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

type Material = {
    id: number;
    name: string;
    hex_color: string | null;
    sort_order: number;
    is_active: boolean;
    products_count: number;
};

defineProps<{ materials: Material[] }>();

const { confirmDestructive } = useSweetAlert();
const open = ref(false);
const editing = ref<Material | null>(null);

const form = useForm({
    name: '',
    hex_color: '',
    sort_order: 0,
    is_active: true,
});

function openCreate(): void {
    editing.value = null;
    form.reset();
    open.value = true;
}

function openEdit(material: Material): void {
    editing.value = material;
    form.name = material.name;
    form.hex_color = material.hex_color ?? '';
    form.sort_order = material.sort_order;
    form.is_active = material.is_active;
    open.value = true;
}

function submit(): void {
    if (editing.value) {
        form.patch(`/admin/materiales/${editing.value.id}`, {
            onSuccess: () => (open.value = false),
        });
    } else {
        form.post('/admin/materiales', {
            onSuccess: () => (open.value = false),
        });
    }
}

async function destroy(material: Material): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar material',
        text: `¿Eliminar "${material.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/materiales/${material.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Materiales</h1>
                <p class="text-sm text-muted-foreground">
                    Metales y acabados disponibles para tus piezas.
                </p>
            </div>
            <Button @click="openCreate"
                ><Plus class="mr-1.5 size-4" /> Nuevo material</Button
            >
        </div>

        <div class="flex gap-4 border-b text-sm">
            <span class="border-b-2 border-primary pb-2 font-medium"
                >Materiales</span
            >
            <Link
                href="/admin/piedras"
                class="pb-2 text-muted-foreground hover:text-foreground"
                >Piedras</Link
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
                <TableRow v-for="material in materials" :key="material.id">
                    <TableCell class="flex items-center gap-2 font-medium">
                        <span
                            v-if="material.hex_color"
                            class="size-3 rounded-full border"
                            :style="{ backgroundColor: material.hex_color }"
                        />
                        {{ material.name }}
                    </TableCell>
                    <TableCell>{{ material.products_count }}</TableCell>
                    <TableCell>{{
                        material.is_active ? 'Activo' : 'Inactivo'
                    }}</TableCell>
                    <TableCell class="space-x-2 text-right">
                        <button
                            type="button"
                            class="text-sm underline"
                            @click="openEdit(material)"
                        >
                            Editar
                        </button>
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(material)"
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
                        editing ? 'Editar material' : 'Nuevo material'
                    }}</DialogTitle></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="m-name">Nombre</Label>
                        <Input id="m-name" v-model="form.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="m-color">Color (hex, opcional)</Label>
                        <Input
                            id="m-color"
                            v-model="form.hex_color"
                            placeholder="#D4AF37"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="m-order">Orden</Label>
                        <Input
                            id="m-order"
                            v-model.number="form.sort_order"
                            type="number"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <Switch id="m-active" v-model="form.is_active" />
                        <Label for="m-active">Activo</Label>
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
