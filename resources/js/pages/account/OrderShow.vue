<script setup lang="ts">
defineProps<{
    order: {
        orderNumber: string;
        status: string;
        paymentStatus: string;
        shippingMethod: string | null;
        trackingNumber: string | null;
        trackingUrl: string | null;
        shippingCompany: string | null;
        shippingAddress: Record<string, string> | null;
        items: Array<{
            productName: string;
            variantName: string | null;
            quantity: number;
            unitPrice: string;
            total: string;
        }>;
        subtotal: string;
        discountTotal: string;
        shippingTotal: string;
        total: string;
        history: Array<{
            toStatus: string;
            comment: string | null;
            createdAt: string;
        }>;
        createdAt: string;
    };
}>();
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="font-display text-lg text-ink">
                    Pedido {{ order.orderNumber }}
                </h2>
                <p class="text-sm text-stone">{{ order.createdAt }}</p>
            </div>
            <span
                class="border border-line px-3 py-1 text-xs tracking-wide text-ink uppercase"
                >{{ order.status }}</span
            >
        </div>

        <div v-if="order.trackingNumber" class="border border-line p-4 text-sm">
            <p class="text-ink">
                Guía de rastreo: {{ order.trackingNumber }}
                <span v-if="order.shippingCompany"
                    >({{ order.shippingCompany }})</span
                >
            </p>
            <a
                v-if="order.trackingUrl"
                :href="order.trackingUrl"
                target="_blank"
                rel="noopener"
                class="link-underline text-stone"
                >Rastrear pedido</a
            >
        </div>

        <div class="border border-line p-5">
            <div
                v-for="(item, i) in order.items"
                :key="i"
                class="flex justify-between border-b border-line py-3 text-sm last:border-0"
            >
                <span class="text-ink"
                    >{{ item.productName
                    }}<span v-if="item.variantName" class="text-stone">
                        — {{ item.variantName }}</span
                    >
                    × {{ item.quantity }}</span
                >
                <span class="text-ink">{{ item.total }}</span>
            </div>
            <div class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between text-stone">
                    <span>Subtotal</span><span>{{ order.subtotal }}</span>
                </div>
                <div class="flex justify-between text-stone">
                    <span>Descuento</span
                    ><span>-{{ order.discountTotal }}</span>
                </div>
                <div class="flex justify-between text-stone">
                    <span>Envío</span><span>{{ order.shippingTotal }}</span>
                </div>
                <div class="flex justify-between text-base text-ink">
                    <span>Total</span><span>{{ order.total }}</span>
                </div>
            </div>
        </div>

        <div v-if="order.shippingAddress">
            <p class="text-eyebrow mb-2">Dirección de envío</p>
            <p class="text-sm text-stone">
                {{ order.shippingAddress.full_name }} —
                {{ order.shippingAddress.street }}
                {{ order.shippingAddress.ext_number }},
                {{ order.shippingAddress.neighborhood }},
                {{ order.shippingAddress.city }},
                {{ order.shippingAddress.state }}, CP
                {{ order.shippingAddress.postal_code }}
            </p>
        </div>

        <div v-if="order.history.length">
            <p class="text-eyebrow mb-3">Historial</p>
            <ul class="space-y-2 text-sm text-stone">
                <li v-for="(h, i) in order.history" :key="i">
                    <span class="text-ink">{{ h.toStatus }}</span> —
                    {{ h.createdAt }}
                    <span v-if="h.comment"> · {{ h.comment }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>
