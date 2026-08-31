<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    filters: { desde: string; hasta: string; preset: string };
    summary: {
        ordersCount: number;
        revenue: string;
        discounts: string;
        averageTicket: string;
        estimatedCost: string;
        estimatedGrossProfit: string;
        estimatedMarginPercentage: number;
        cancelledOrders: number;
        refundedOrders: number;
    };
    salesByDay: Array<{ date: string; total: number; ordersCount: number }>;
    topProducts: Array<{
        productName: string;
        sku: string;
        quantitySold: number;
        revenue: string;
    }>;
    salesByCategory: Array<{ category: string; revenue: string }>;
    salesByPaymentMethod: Array<{
        paymentMethod: string;
        ordersCount: number;
        revenue: string;
    }>;
    couponsUsed: Array<{
        code: string;
        usageCount: number;
        totalDiscount: string;
    }>;
    topCustomers: Array<{
        name: string;
        email: string;
        ordersCount: number;
        totalSpent: string;
    }>;
}>();

const desde = ref(props.filters.desde);
const hasta = ref(props.filters.hasta);

const presets = [
    { value: 'hoy', label: 'Hoy' },
    { value: 'ayer', label: 'Ayer' },
    { value: '7_dias', label: '7 días' },
    { value: '30_dias', label: '30 días' },
    { value: 'mes_actual', label: 'Mes actual' },
    { value: 'mes_anterior', label: 'Mes anterior' },
    { value: 'anio', label: 'Año' },
];

function applyPreset(preset: string): void {
    router.get('/admin/reportes', { preset }, { preserveState: true });
}

function applyCustomRange(): void {
    router.get(
        '/admin/reportes',
        { desde: desde.value, hasta: hasta.value },
        { preserveState: true },
    );
}

const maxSale = Math.max(1, ...props.salesByDay.map((d) => d.total));
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">Reportes</h1>
            <a
                :href="`/admin/reportes/exportar?desde=${filters.desde}&hasta=${filters.hasta}`"
                class="text-sm underline"
                >Exportar productos (CSV)</a
            >
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                v-for="preset in presets"
                :key="preset.value"
                type="button"
                class="rounded-md border px-3 py-1.5 text-xs"
                :class="
                    filters.preset === preset.value
                        ? 'border-primary bg-primary text-primary-foreground'
                        : 'hover:bg-muted'
                "
                @click="applyPreset(preset.value)"
            >
                {{ preset.label }}
            </button>
            <Input v-model="desde" type="date" class="w-40" />
            <Input v-model="hasta" type="date" class="w-40" />
            <button
                type="button"
                class="rounded-md border px-3 py-1.5 text-xs hover:bg-muted"
                @click="applyCustomRange"
            >
                Aplicar
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Pedidos</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    summary.ordersCount
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Ventas</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    summary.revenue
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Descuentos</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    summary.discounts
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Ticket promedio</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    summary.averageTicket
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Costo estimado</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    summary.estimatedCost
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Utilidad bruta estimada</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold">{{
                    summary.estimatedGrossProfit
                }}</CardContent></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Margen estimado</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold"
                    >{{ summary.estimatedMarginPercentage }}%</CardContent
                ></Card
            >
            <Card
                ><CardHeader class="pb-2"
                    ><CardTitle
                        class="text-xs font-normal text-muted-foreground"
                        >Cancelados / Reembolsos</CardTitle
                    ></CardHeader
                ><CardContent class="text-xl font-semibold"
                    >{{ summary.cancelledOrders }} /
                    {{ summary.refundedOrders }}</CardContent
                ></Card
            >
        </div>

        <Card>
            <CardHeader
                ><CardTitle class="text-sm"
                    >Ventas por día</CardTitle
                ></CardHeader
            >
            <CardContent>
                <div class="flex h-40 items-end gap-1">
                    <div
                        v-for="(day, i) in salesByDay"
                        :key="i"
                        class="flex-1 rounded-t bg-primary/70"
                        :style="{ height: `${(day.total / maxSale) * 100}%` }"
                        :title="`${day.date}: ${day.total}`"
                    />
                </div>
            </CardContent>
        </Card>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Productos más vendidos</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="p in topProducts"
                        :key="p.sku"
                        class="flex justify-between border-b py-1 last:border-0"
                    >
                        <span>{{ p.productName }} × {{ p.quantitySold }}</span
                        ><span>{{ p.revenue }}</span>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Ventas por categoría</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="c in salesByCategory"
                        :key="c.category"
                        class="flex justify-between border-b py-1 last:border-0"
                    >
                        <span>{{ c.category }}</span
                        ><span>{{ c.revenue }}</span>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Métodos de pago</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="p in salesByPaymentMethod"
                        :key="p.paymentMethod"
                        class="flex justify-between border-b py-1 last:border-0"
                    >
                        <span>{{ p.paymentMethod }} ({{ p.ordersCount }})</span
                        ><span>{{ p.revenue }}</span>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Cupones utilizados</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="c in couponsUsed"
                        :key="c.code"
                        class="flex justify-between border-b py-1 last:border-0"
                    >
                        <span>{{ c.code }} ({{ c.usageCount }})</span
                        ><span>{{ c.totalDiscount }}</span>
                    </div>
                    <p v-if="!couponsUsed.length" class="text-muted-foreground">
                        Sin uso de cupones en el periodo.
                    </p>
                </CardContent>
            </Card>
            <Card class="lg:col-span-2">
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Clientes principales</CardTitle
                    ></CardHeader
                >
                <CardContent class="space-y-1.5 text-sm">
                    <div
                        v-for="c in topCustomers"
                        :key="c.email"
                        class="flex justify-between border-b py-1 last:border-0"
                    >
                        <span>{{ c.name }} ({{ c.ordersCount }})</span
                        ><span>{{ c.totalSpent }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
