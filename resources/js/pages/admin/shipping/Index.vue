<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
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
import { Textarea } from '@/components/ui/textarea';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Method = {
    id: number;
    name: string;
    description: string | null;
    price: number;
    free_from: number | null;
    estimated_days: string | null;
    active: boolean;
};

defineProps<{ methods: Method[] }>();
const { confirmDestructive } = useSweetAlert();
const open = ref(false);
const editing = ref<Method | null>(null);
const form = useForm({
    name: '',
    description: '',
    price: 0,
    free_from: '',
    estimated_days: '',
    active: true,
    sort_order: 0,
});

function openCreate(): void {
    editing.value = null;
    form.reset();
    open.value = true;
}

function openEdit(method: Method): void {
    editing.value = method;
    form.name = method.name;
    form.description = method.description ?? '';
    form.price = method.price / 100;
    form.free_from = method.free_from ? String(method.free_from / 100) : '';
    form.estimated_days = method.estimated_days ?? '';
    form.active = method.active;
    open.value = true;
}

function submit(): void {
    if (editing.value) {
        form.patch(`/admin/envios/${editing.value.id}`, {
            onSuccess: () => (open.value = false),
        });
    } else {
        form.post('/admin/envios', { onSuccess: () => (open.value = false) });
    }
}

async function destroy(method: Method): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar método de envío',
        text: `¿Eliminar "${method.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/envios/${method.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Métodos de envío</h1>
            <Button @click="openCreate"
                ><Plus class="mr-1.5 size-4" /> Nuevo método</Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Precio</TableHead>
                    <TableHead>Gratis desde</TableHead>
                    <TableHead>Tiempo estimado</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="method in methods" :key="method.id">
                    <TableCell class="font-medium">{{ method.name }}</TableCell>
                    <TableCell
                        >${{
                            (method.price / 100).toLocaleString('es-MX')
                        }}</TableCell
                    >
                    <TableCell>{{
                        method.free_from
                            ? `$${(method.free_from / 100).toLocaleString('es-MX')}`
                            : '—'
                    }}</TableCell>
                    <TableCell>{{ method.estimated_days }}</TableCell>
                    <TableCell>{{
                        method.active ? 'Activo' : 'Inactivo'
                    }}</TableCell>
                    <TableCell class="space-x-2 text-right">
                        <button
                            type="button"
                            class="text-sm underline"
                            @click="openEdit(method)"
                        >
                            Editar
                        </button>
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(method)"
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
                        editing ? 'Editar método' : 'Nuevo método'
                    }}</DialogTitle></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label>Nombre</Label
                        ><Input v-model="form.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label>Descripción</Label
                        ><Textarea v-model="form.description" rows="2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Precio (MXN)</Label
                            ><Input
                                v-model.number="form.price"
                                type="number"
                                step="0.01"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Gratis desde (opcional)</Label
                            ><Input
                                v-model="form.free_from"
                                type="number"
                                step="0.01"
                            />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Tiempo estimado</Label
                        ><Input
                            v-model="form.estimated_days"
                            placeholder="3-5 días hábiles"
                        />
                    </div>
                    <label class="flex items-center gap-2 text-sm"
                        ><Switch v-model="form.active" /> Activo</label
                    >
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
