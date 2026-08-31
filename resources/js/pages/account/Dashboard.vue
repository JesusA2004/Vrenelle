<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    stats: { ordersCount: number; totalSpent: string; wishlistCount: number };
    recentOrders: Array<{
        orderNumber: string;
        status: string;
        total: string;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <div class="space-y-10">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="border border-line p-5">
                <p class="text-eyebrow mb-2">Pedidos realizados</p>
                <p class="font-display text-2xl text-ink">
                    {{ stats.ordersCount }}
                </p>
            </div>
            <div class="border border-line p-5">
                <p class="text-eyebrow mb-2">Total invertido</p>
                <p class="font-display text-2xl text-ink">
                    {{ stats.totalSpent }}
                </p>
            </div>
            <div class="border border-line p-5">
                <p class="text-eyebrow mb-2">Favoritos guardados</p>
                <p class="font-display text-2xl text-ink">
                    {{ stats.wishlistCount }}
                </p>
            </div>
        </div>

        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-display text-lg text-ink">Pedidos recientes</h2>
                <Link
                    href="/mi-cuenta/pedidos"
                    class="link-underline text-sm text-stone"
                    >Ver todos</Link
                >
            </div>

            <div
                v-if="recentOrders.length === 0"
                class="border border-line p-6 text-sm text-stone"
            >
                Aún no has realizado ningún pedido.
            </div>
            <div v-else class="divide-y divide-line border border-line">
                <Link
                    v-for="order in recentOrders"
                    :key="order.orderNumber"
                    :href="`/mi-cuenta/pedidos/${order.orderNumber}`"
                    class="flex items-center justify-between px-5 py-4 text-sm hover:bg-ivory-deep"
                >
                    <span class="text-ink">{{ order.orderNumber }}</span>
                    <span class="text-stone">{{ order.status }}</span>
                    <span class="text-stone">{{ order.createdAt }}</span>
                    <span class="text-ink">{{ order.total }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
