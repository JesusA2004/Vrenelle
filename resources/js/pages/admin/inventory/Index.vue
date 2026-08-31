<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Product = {
    id: number;
    name: string;
    sku: string;
    stock?: number;
    low_stock_threshold?: number;
};
type Movement = {
    id: number;
    product: string | null;
    sku: string | null;
    variant: string | null;
    type: string;
    quantity: number;
    previousStock: number;
    newStock: number;
    reason: string | null;
    user: string | null;
    createdAt: string;
};
type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    lowStock: Product[];
    outOfStock: Product[];
    madeToOrder: Product[];
    movements: Paginated<Movement>;
}>();

const open = ref(false);
const selected = ref<Product | null>(null);
const form = useForm({ product_id: 0, new_stock: 0, reason: '' });

function openAdjust(product: Product): void {
    selected.value = product;
    form.product_id = product.id;
    form.new_stock = product.stock ?? 0;
    form.reason = '';
    open.value = true;
}

function submit(): void {
    form.post('/admin/inventario/ajustar', {
        preserveScroll: true,
        onSuccess: () => (open.value = false),
    });
}
</script>

<template>
    <div class="space-y-8">
        <h1 class="text-2xl font-semibold">Inventario</h1>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Stock bajo</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="p in lowStock"
                        :key="p.id"
                        class="flex items-center justify-between border-b py-1.5 last:border-0"
                    >
                        <span
                            >{{ p.name }}
                            <span class="text-muted-foreground"
                                >({{ p.sku }})</span
                            >
                            — {{ p.stock }}</span
                        >
                        <button
                            type="button"
                            class="text-xs underline"
                            @click="openAdjust(p)"
                        >
                            Ajustar
                        </button>
                    </div>
                    <p v-if="!lowStock.length" class="text-muted-foreground">
                        Sin alertas de stock bajo.
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Sin inventario</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="p in outOfStock"
                        :key="p.id"
                        class="flex items-center justify-between border-b py-1.5 last:border-0"
                    >
                        <span
                            >{{ p.name }}
                            <span class="text-muted-foreground"
                                >({{ p.sku }})</span
                            ></span
                        >
                        <button
                            type="button"
                            class="text-xs underline"
                            @click="openAdjust(p)"
                        >
                            Ajustar
                        </button>
                    </div>
                    <p v-if="!outOfStock.length" class="text-muted-foreground">
                        Sin productos agotados.
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Productos bajo pedido</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="p in madeToOrder"
                        :key="p.id"
                        class="border-b py-1.5 last:border-0"
                    >
                        {{ p.name }}
                        <span class="text-muted-foreground">({{ p.sku }})</span>
                    </div>
                    <p v-if="!madeToOrder.length" class="text-muted-foreground">
                        Sin productos bajo pedido.
                    </p>
                </CardContent>
            </Card>
        </div>

        <div>
            <h2 class="mb-3 font-medium">Últimos movimientos</h2>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Producto</TableHead>
                        <TableHead>Tipo</TableHead>
                        <TableHead>Cantidad</TableHead>
                        <TableHead>Anterior → Nuevo</TableHead>
                        <TableHead>Motivo</TableHead>
                        <TableHead>Usuario</TableHead>
                        <TableHead>Fecha</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="m in movements.data" :key="m.id">
                        <TableCell
                            >{{ m.product
                            }}<span
                                v-if="m.variant"
                                class="text-muted-foreground"
                            >
                                — {{ m.variant }}</span
                            ></TableCell
                        >
                        <TableCell>{{ m.type }}</TableCell>
                        <TableCell
                            :class="
                                m.quantity < 0
                                    ? 'text-destructive'
                                    : 'text-green-600'
                            "
                            >{{ m.quantity > 0 ? '+' : ''
                            }}{{ m.quantity }}</TableCell
                        >
                        <TableCell
                            >{{ m.previousStock }} → {{ m.newStock }}</TableCell
                        >
                        <TableCell class="text-muted-foreground">{{
                            m.reason
                        }}</TableCell>
                        <TableCell>{{ m.user ?? 'Sistema' }}</TableCell>
                        <TableCell>{{ m.createdAt }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <AdminPagination :links="movements.links" />
        </div>

        <Dialog v-model:open="open">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle
                        >Ajustar inventario — {{ selected?.name }}</DialogTitle
                    ></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label>Nuevo inventario</Label
                        ><Input
                            v-model.number="form.new_stock"
                            type="number"
                            min="0"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Motivo del ajuste</Label
                        ><Input
                            v-model="form.reason"
                            required
                            placeholder="Conteo físico, devolución, merma..."
                        />
                    </div>
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                        >Confirmar ajuste</Button
                    >
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
