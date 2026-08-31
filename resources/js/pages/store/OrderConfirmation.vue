<script setup lang="ts">
import LuxuryButton from '@/components/store/LuxuryButton.vue';

defineProps<{
    order: {
        orderNumber: string;
        status: string;
        paymentStatus: string;
        paymentMethod: string | null;
        customerName: string;
        shippingMethod: string | null;
        items: Array<{
            productName: string;
            variantName: string | null;
            quantity: number;
            total: string;
        }>;
        subtotal: string;
        discountTotal: string;
        shippingTotal: string;
        total: string;
        createdAt: string;
    };
}>();
</script>

<template>
    <div class="mx-auto max-w-2xl px-6 py-20 text-center md:px-10">
        <p class="text-eyebrow mb-3">Pedido confirmado</p>
        <h1 class="text-display-md font-display text-ink">
            Gracias por tu compra, {{ order.customerName.split(' ')[0] }}
        </h1>
        <p class="mt-4 text-sm text-stone">
            Hemos recibido tu pedido
            <strong class="text-ink">{{ order.orderNumber }}</strong> el
            {{ order.createdAt }}.
        </p>
        <p
            v-if="order.paymentMethod === 'manual'"
            class="mt-2 text-sm text-stone"
        >
            Tu pedido quedará confirmado en cuanto verifiquemos tu pago. Nuestro
            equipo se pondrá en contacto contigo con las instrucciones para
            completar la transferencia.
        </p>

        <div class="mt-10 space-y-3 border border-line p-6 text-left">
            <div
                v-for="(item, i) in order.items"
                :key="i"
                class="flex justify-between text-sm"
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
            <div class="space-y-1 border-t border-line pt-3 text-sm">
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

        <div class="mt-10 text-left">
            <p class="text-eyebrow mb-2">¿Qué sigue?</p>
            <p class="text-sm text-stone">
                Te enviaremos un correo de confirmación con el detalle de tu
                pedido. En cuanto tu pieza esté lista para viajar, recibirás la
                guía de rastreo{{
                    order.shippingMethod ? ` vía ${order.shippingMethod}` : ''
                }}.
            </p>
        </div>

        <LuxuryButton as="link" href="/joyeria" variant="outline" class="mt-10">
            Seguir explorando
        </LuxuryButton>
    </div>
</template>
