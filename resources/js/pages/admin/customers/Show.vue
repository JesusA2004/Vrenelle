<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        createdAt: string;
    };
    stats: {
        ordersCount: number;
        totalSpent: string;
        averageTicket: string;
        lastOrderAt: string | null;
    };
    addresses: Array<{
        id: number;
        full_name: string;
        street: string;
        city: string;
        state: string;
    }>;
    orders: Array<{
        orderNumber: string;
        status: string;
        total: string;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <div class="max-w-4xl space-y-8">
        <div>
            <h1 class="text-2xl font-semibold">{{ customer.name }}</h1>
            <p class="text-sm text-muted-foreground">
                {{ customer.email }} · {{ customer.phone ?? 'Sin teléfono' }} ·
                Cliente desde {{ customer.createdAt }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Pedidos</CardTitle
                    ></CardHeader
                ><CardContent class="text-lg font-semibold">{{
                    stats.ordersCount
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Total gastado</CardTitle
                    ></CardHeader
                ><CardContent class="text-lg font-semibold">{{
                    stats.totalSpent
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Ticket promedio</CardTitle
                    ></CardHeader
                ><CardContent class="text-lg font-semibold">{{
                    stats.averageTicket
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Última compra</CardTitle
                    ></CardHeader
                ><CardContent class="text-lg font-semibold">{{
                    stats.lastOrderAt ?? '—'
                }}</CardContent></Card
            >
        </div>

        <div v-if="addresses.length">
            <h2 class="mb-2 font-medium">Direcciones</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div
                    v-for="a in addresses"
                    :key="a.id"
                    class="rounded border p-3"
                >
                    {{ a.full_name }} — {{ a.street }}, {{ a.city }},
                    {{ a.state }}
                </div>
            </div>
        </div>

        <div>
            <h2 class="mb-2 font-medium">Pedidos</h2>
            <div class="divide-y rounded border">
                <div
                    v-for="order in orders"
                    :key="order.orderNumber"
                    class="flex justify-between px-4 py-2.5 text-sm"
                >
                    <span>{{ order.orderNumber }}</span>
                    <span class="text-muted-foreground">{{
                        order.status
                    }}</span>
                    <span class="text-muted-foreground">{{
                        order.createdAt
                    }}</span>
                    <span>{{ order.total }}</span>
                </div>
                <p
                    v-if="!orders.length"
                    class="px-4 py-3 text-sm text-muted-foreground"
                >
                    Sin pedidos registrados.
                </p>
            </div>
        </div>
    </div>
</template>
