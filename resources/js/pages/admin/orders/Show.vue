<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useSweetAlert } from '@/composables/useSweetAlert';

const props = defineProps<{
    order: {
        id: number;
        orderNumber: string;
        status: string;
        statusLabel: string;
        paymentStatus: string;
        fulfillmentStatus: string;
        customerName: string;
        email: string;
        phone: string;
        user: { name: string; email: string } | null;
        shippingAddress: Record<string, string> | null;
        billingAddress: Record<string, string> | null;
        shippingMethod: string | null;
        paymentMethod: string | null;
        installmentMonths: number | null;
        trackingNumber: string | null;
        shippingCompany: string | null;
        trackingUrl: string | null;
        notes: string | null;
        items: Array<{
            productName: string;
            variantName: string | null;
            sku: string;
            quantity: number;
            unitPrice: string;
            total: string;
        }>;
        subtotal: string;
        discountTotal: string;
        shippingTotal: string;
        taxTotal: string;
        total: string;
        coupon: string | null;
        transactions: Array<{
            provider: string;
            status: string;
            amount: string;
            processedAt: string | null;
        }>;
        history: Array<{
            fromStatus: string | null;
            toStatus: string;
            comment: string | null;
            user: string | null;
            createdAt: string;
        }>;
        createdAt: string;
    };
}>();

const statuses = [
    { value: 'pending', label: 'Pendiente' },
    { value: 'payment_pending', label: 'Pago pendiente' },
    { value: 'paid', label: 'Pagado' },
    { value: 'confirmed', label: 'Confirmado' },
    { value: 'preparing', label: 'Preparando' },
    { value: 'shipped', label: 'Enviado' },
    { value: 'delivered', label: 'Entregado' },
    { value: 'cancelled', label: 'Cancelado' },
    { value: 'refunded', label: 'Reembolsado' },
];

const statusForm = useForm({ status: props.order.status, comment: '' });
const trackingForm = useForm({
    tracking_number: props.order.trackingNumber ?? '',
    shipping_company: props.order.shippingCompany ?? '',
    tracking_url: props.order.trackingUrl ?? '',
});

const { confirmDestructive } = useSweetAlert();

function updateStatus(): void {
    statusForm.patch(`/admin/pedidos/${props.order.id}/estado`, {
        preserveScroll: true,
    });
}

function updateTracking(): void {
    trackingForm.patch(`/admin/pedidos/${props.order.id}/envio`, {
        preserveScroll: true,
    });
}

async function cancelOrder(): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Cancelar pedido',
        text: 'El inventario reservado se devolverá automáticamente. ¿Deseas continuar?',
    });

    if (confirmed) {
        router.post(
            `/admin/pedidos/${props.order.id}/cancelar`,
            {},
            { preserveScroll: true },
        );
    }
}
</script>

<template>
    <div class="max-w-4xl space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    Pedido {{ order.orderNumber }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ order.createdAt }} · {{ order.customerName }} ({{
                        order.email
                    }})
                </p>
            </div>
            <Button variant="destructive" @click="cancelOrder"
                >Cancelar pedido</Button
            >
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="space-y-4 rounded-lg border p-5">
                <h2 class="font-medium">Actualizar estado</h2>
                <div class="grid gap-2">
                    <Label>Estado</Label>
                    <select
                        v-model="statusForm.status"
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                    >
                        <option
                            v-for="s in statuses"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label>Comentario (opcional)</Label>
                    <Textarea v-model="statusForm.comment" rows="2" />
                </div>
                <Button :disabled="statusForm.processing" @click="updateStatus"
                    >Actualizar estado</Button
                >
            </div>

            <div class="space-y-4 rounded-lg border p-5">
                <h2 class="font-medium">Información de envío</h2>
                <div class="grid gap-2">
                    <Label>Número de guía</Label
                    ><Input v-model="trackingForm.tracking_number" />
                </div>
                <div class="grid gap-2">
                    <Label>Paquetería</Label
                    ><Input v-model="trackingForm.shipping_company" />
                </div>
                <div class="grid gap-2">
                    <Label>URL de rastreo</Label
                    ><Input v-model="trackingForm.tracking_url" />
                </div>
                <Button
                    :disabled="trackingForm.processing"
                    @click="updateTracking"
                    >Guardar</Button
                >
            </div>
        </div>

        <div class="rounded-lg border p-5">
            <h2 class="mb-3 font-medium">Productos</h2>
            <div
                v-for="(item, i) in order.items"
                :key="i"
                class="flex justify-between border-b py-2 text-sm last:border-0"
            >
                <span
                    >{{ item.productName
                    }}<span
                        v-if="item.variantName"
                        class="text-muted-foreground"
                    >
                        — {{ item.variantName }}</span
                    >
                    ({{ item.sku }}) × {{ item.quantity }}</span
                >
                <span>{{ item.total }}</span>
            </div>
            <div class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between text-muted-foreground">
                    <span>Subtotal</span><span>{{ order.subtotal }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span
                        >Descuento{{
                            order.coupon ? ` (${order.coupon})` : ''
                        }}</span
                    ><span>-{{ order.discountTotal }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Envío</span><span>{{ order.shippingTotal }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Impuestos</span><span>{{ order.taxTotal }}</span>
                </div>
                <div class="flex justify-between font-medium">
                    <span>Total</span><span>{{ order.total }}</span>
                </div>
            </div>
        </div>

        <div v-if="order.shippingAddress" class="rounded-lg border p-5 text-sm">
            <h2 class="mb-2 font-medium">Dirección de envío</h2>
            <p>
                {{ order.shippingAddress.full_name }} —
                {{ order.shippingAddress.street }}
                {{ order.shippingAddress.ext_number }},
                {{ order.shippingAddress.neighborhood }},
                {{ order.shippingAddress.city }},
                {{ order.shippingAddress.state }}, CP
                {{ order.shippingAddress.postal_code }}
            </p>
            <p class="text-muted-foreground">
                Tel: {{ order.shippingAddress.phone }}
            </p>
        </div>

        <div
            v-if="order.transactions.length"
            class="rounded-lg border p-5 text-sm"
        >
            <h2 class="mb-2 font-medium">Transacciones de pago</h2>
            <div
                v-for="(t, i) in order.transactions"
                :key="i"
                class="flex justify-between border-b py-1.5 last:border-0"
            >
                <span>{{ t.provider }} — {{ t.status }}</span>
                <span
                    >{{ t.amount }}
                    <span class="text-muted-foreground">{{
                        t.processedAt
                    }}</span></span
                >
            </div>
        </div>

        <div v-if="order.history.length" class="rounded-lg border p-5 text-sm">
            <h2 class="mb-2 font-medium">Historial</h2>
            <ul class="space-y-1.5">
                <li
                    v-for="(h, i) in order.history"
                    :key="i"
                    class="text-muted-foreground"
                >
                    <span class="text-foreground">{{ h.toStatus }}</span> —
                    {{ h.createdAt }}{{ h.user ? ` · ${h.user}` : ''
                    }}{{ h.comment ? ` · ${h.comment}` : '' }}
                </li>
            </ul>
        </div>
    </div>
</template>
