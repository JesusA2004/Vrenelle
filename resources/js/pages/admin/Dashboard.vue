<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    cards: {
        salesToday: string;
        salesMonth: string;
        ordersToday: number;
        pendingOrders: number;
        averageTicket: string;
        failedPayments: number;
    };
    salesLast30Days: Array<{ date: string; total: number }>;
    ordersByStatus: Record<string, number>;
    salesByPaymentMethod: Array<{
        paymentMethod: string;
        ordersCount: number;
        revenue: number;
    }>;
    topProducts: Array<{
        productName: string;
        sku: string;
        quantitySold: number;
        revenue: string;
    }>;
    lowStockCount: number;
    outOfStockCount: number;
}>();

const maxSale = (days: Array<{ total: number }>) =>
    Math.max(1, ...days.map((d) => d.total));
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold">Panel</h1>
            <p class="text-sm text-muted-foreground">
                Resumen general de la operación.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Ventas hoy</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    cards.salesToday
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Ventas del mes</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    cards.salesMonth
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Pedidos hoy</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    cards.ordersToday
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Pedidos pendientes</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    cards.pendingOrders
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Ticket promedio</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    cards.averageTicket
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Pagos fallidos</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold text-destructive">{{
                    cards.failedPayments
                }}</CardContent></Card
            >
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Ventas — últimos 30 días</CardTitle
                    ></CardHeader
                >
                <CardContent>
                    <div class="flex h-40 items-end gap-1">
                        <div
                            v-for="(day, i) in salesLast30Days"
                            :key="i"
                            class="flex-1 rounded-t bg-primary/70"
                            :style="{
                                height: `${(day.total / maxSale(salesLast30Days)) * 100}%`,
                            }"
                            :title="`${day.date}: $${day.total}`"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Pedidos por estado</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-2 text-sm">
                    <div
                        v-for="(count, status) in ordersByStatus"
                        :key="status"
                        class="flex justify-between"
                    >
                        <span class="text-muted-foreground">{{ status }}</span>
                        <span class="font-medium">{{ count }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Productos más vendidos (mes)</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-2 text-sm">
                    <div
                        v-for="p in topProducts"
                        :key="p.sku"
                        class="flex justify-between border-b py-1.5 last:border-0"
                    >
                        <span
                            >{{ p.productName }}
                            <span class="text-muted-foreground"
                                >× {{ p.quantitySold }}</span
                            ></span
                        >
                        <span class="font-medium">{{ p.revenue }}</span>
                    </div>
                    <p v-if="!topProducts.length" class="text-muted-foreground">
                        Sin ventas registradas este mes.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Alertas de inventario</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Stock bajo</span
                        ><span class="font-medium">{{ lowStockCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Sin inventario</span
                        ><span class="font-medium">{{ outOfStockCount }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
