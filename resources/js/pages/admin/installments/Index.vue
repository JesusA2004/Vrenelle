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
import { useSweetAlert } from '@/composables/useSweetAlert';

type Rule = {
    id: number;
    provider: string;
    months: number;
    minimum_amount: number;
    maximum_amount: number | null;
    fee_percentage: number;
    customer_fee_percentage: number;
    merchant_fee_percentage: number;
    active: boolean;
};

defineProps<{ rules: Rule[] }>();

const { confirmDestructive } = useSweetAlert();
const open = ref(false);
const editing = ref<Rule | null>(null);

const form = useForm({
    provider: 'mercadopago',
    months: 3,
    minimum_amount: 0,
    maximum_amount: '',
    fee_percentage: 0,
    customer_fee_percentage: 0,
    merchant_fee_percentage: 0,
    active: false,
});

function openCreate(): void {
    editing.value = null;
    form.reset();
    open.value = true;
}

function openEdit(rule: Rule): void {
    editing.value = rule;
    form.provider = rule.provider;
    form.months = rule.months;
    form.minimum_amount = rule.minimum_amount / 100;
    form.maximum_amount = rule.maximum_amount
        ? String(rule.maximum_amount / 100)
        : '';
    form.fee_percentage = rule.fee_percentage;
    form.customer_fee_percentage = rule.customer_fee_percentage;
    form.merchant_fee_percentage = rule.merchant_fee_percentage;
    form.active = rule.active;
    open.value = true;
}

function submit(): void {
    if (editing.value) {
        form.patch(`/admin/msi/${editing.value.id}`, {
            onSuccess: () => (open.value = false),
        });
    } else {
        form.post('/admin/msi', { onSuccess: () => (open.value = false) });
    }
}

async function destroy(rule: Rule): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar plan de MSI',
        text: `¿Eliminar el plan de ${rule.months} meses para ${rule.provider}?`,
    });

    if (confirmed) {
        router.delete(`/admin/msi/${rule.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Meses sin intereses</h1>
                <p class="max-w-2xl text-sm text-muted-foreground">
                    Solo los planes marcados como <strong>activos</strong> se
                    ofrecen en el checkout. La disponibilidad real siempre
                    depende también de tu proveedor de pagos y del banco emisor
                    de la tarjeta del cliente — nunca actives un plazo que tu
                    cuenta no soporte realmente.
                </p>
            </div>
            <Button @click="openCreate"
                ><Plus class="mr-1.5 size-4" /> Nuevo plan</Button
            >
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Proveedor</TableHead>
                    <TableHead>Meses</TableHead>
                    <TableHead>Monto mínimo</TableHead>
                    <TableHead>Comisión total</TableHead>
                    <TableHead>Comisión al cliente</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="rule in rules" :key="rule.id">
                    <TableCell class="font-medium">{{
                        rule.provider
                    }}</TableCell>
                    <TableCell>{{ rule.months }} MSI</TableCell>
                    <TableCell
                        >${{
                            (rule.minimum_amount / 100).toLocaleString('es-MX')
                        }}</TableCell
                    >
                    <TableCell>{{ rule.fee_percentage }}%</TableCell>
                    <TableCell>{{ rule.customer_fee_percentage }}%</TableCell>
                    <TableCell>
                        <span
                            :class="
                                rule.active
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ rule.active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </TableCell>
                    <TableCell class="space-x-2 text-right">
                        <button
                            type="button"
                            class="text-sm underline"
                            @click="openEdit(rule)"
                        >
                            Editar
                        </button>
                        <button
                            type="button"
                            class="text-sm text-destructive underline"
                            @click="destroy(rule)"
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
                        editing ? 'Editar plan' : 'Nuevo plan'
                    }}</DialogTitle></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label>Proveedor</Label>
                        <select
                            v-model="form.provider"
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                        >
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                            <option value="mercadopago">Mercado Pago</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label>Meses</Label>
                        <Input
                            v-model.number="form.months"
                            type="number"
                            min="2"
                            max="24"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Monto mínimo (MXN)</Label>
                            <Input
                                v-model.number="form.minimum_amount"
                                type="number"
                                step="0.01"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Monto máximo (opcional)</Label>
                            <Input
                                v-model="form.maximum_amount"
                                type="number"
                                step="0.01"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label>Comisión total %</Label>
                            <Input
                                v-model.number="form.fee_percentage"
                                type="number"
                                step="0.001"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>% al cliente</Label>
                            <Input
                                v-model.number="form.customer_fee_percentage"
                                type="number"
                                step="0.001"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>% que absorbe la tienda</Label>
                            <Input
                                v-model.number="form.merchant_fee_percentage"
                                type="number"
                                step="0.001"
                            />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm"
                        ><Switch v-model="form.active" /> Plan activo</label
                    >
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                        >Guardar</Button
                    >
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
