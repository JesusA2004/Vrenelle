<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    orders: Paginated<{
        orderNumber: string;
        status: string;
        paymentStatus: string;
        total: string;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <div>
        <h2 class="font-display mb-6 text-lg text-ink">Mis pedidos</h2>

        <div
            v-if="orders.data.length === 0"
            class="border border-line p-6 text-sm text-stone"
        >
            Aún no has realizado ningún pedido.
            <Link href="/joyeria" class="link-underline ml-1 text-ink"
                >Explorar colecciones</Link
            >
        </div>

        <div v-else class="divide-y divide-line border border-line">
            <Link
                v-for="order in orders.data"
                :key="order.orderNumber"
                :href="`/mi-cuenta/pedidos/${order.orderNumber}`"
                class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 text-sm hover:bg-ivory-deep"
            >
                <span class="text-ink">{{ order.orderNumber }}</span>
                <span class="text-stone">{{ order.status }}</span>
                <span class="text-stone">{{ order.createdAt }}</span>
                <span class="text-ink">{{ order.total }}</span>
            </Link>
        </div>
    </div>
</template>
