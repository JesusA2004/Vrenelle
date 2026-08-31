<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, X } from '@lucide/vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';
import { Sheet, SheetContent } from '@/components/ui/sheet';

const open = defineModel<boolean>('open', { default: false });

type CartLine = {
    id: number;
    product: { name: string; slug: string; image: string | null };
    variant: { id: number; name: string } | null;
    quantity: number;
    unitPrice: string;
    lineTotal: string;
};

const page = usePage<{
    cart: { count: number; subtotal: string; items: CartLine[] };
}>();

function updateQuantity(item: CartLine, quantity: number): void {
    if (quantity < 1) {
        remove(item);

        return;
    }

    router.patch(
        `/carrito/item/${item.id}`,
        { quantity },
        { preserveScroll: true, preserveState: true, only: ['cart', 'flash'] },
    );
}

function remove(item: CartLine): void {
    router.delete(`/carrito/item/${item.id}`, {
        preserveScroll: true,
        preserveState: true,
        only: ['cart', 'flash'],
    });
}
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent
            side="right"
            class="w-full gap-0 border-line bg-ivory p-0 sm:max-w-md"
        >
            <div
                class="flex items-center justify-between border-b border-line px-6 py-5"
            >
                <h2 class="font-display text-xl text-ink">Tu selección</h2>
                <button
                    type="button"
                    aria-label="Cerrar carrito"
                    @click="open = false"
                >
                    <X class="size-5 text-ink" />
                </button>
            </div>

            <div
                v-if="page.props.cart.items.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-4 px-8 text-center"
            >
                <ShoppingBag class="size-10 text-stone-light" />
                <p class="font-display text-lg text-ink">
                    Tu selección está vacía
                </p>
                <p class="text-sm text-stone">
                    Descubre nuestras colecciones y encuentra tu próxima pieza.
                </p>
                <LuxuryButton
                    as="link"
                    href="/joyeria"
                    variant="outline"
                    @click="open = false"
                >
                    Explorar colecciones
                </LuxuryButton>
            </div>

            <div v-else class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                    <div
                        v-for="item in page.props.cart.items"
                        :key="item.id"
                        class="flex gap-4"
                    >
                        <Link
                            :href="`/producto/${item.product.slug}`"
                            class="image-zoom size-24 shrink-0 overflow-hidden bg-ivory-deep"
                            @click="open = false"
                        >
                            <img
                                v-if="item.product.image"
                                :src="item.product.image"
                                :alt="item.product.name"
                                class="h-full w-full object-cover"
                            />
                        </Link>
                        <div class="flex flex-1 flex-col justify-between">
                            <div>
                                <Link
                                    :href="`/producto/${item.product.slug}`"
                                    class="font-display text-base text-ink"
                                    @click="open = false"
                                >
                                    {{ item.product.name }}
                                </Link>
                                <p
                                    v-if="item.variant"
                                    class="text-xs text-stone"
                                >
                                    {{ item.variant.name }}
                                </p>
                                <p class="text-xs text-stone">
                                    {{ item.unitPrice }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div
                                    class="inline-flex items-center border border-line text-xs"
                                >
                                    <button
                                        type="button"
                                        class="flex size-7 items-center justify-center hover:bg-ivory-deep"
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity - 1,
                                            )
                                        "
                                    >
                                        <Minus class="size-3" />
                                    </button>
                                    <span
                                        class="w-6 text-center tabular-nums"
                                        >{{ item.quantity }}</span
                                    >
                                    <button
                                        type="button"
                                        class="flex size-7 items-center justify-center hover:bg-ivory-deep"
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity + 1,
                                            )
                                        "
                                    >
                                        <Plus class="size-3" />
                                    </button>
                                </div>
                                <span class="text-sm text-ink">{{
                                    item.lineTotal
                                }}</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            aria-label="Eliminar"
                            class="self-start text-stone hover:text-ink"
                            @click="remove(item)"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </div>

                <div class="border-t border-line px-6 py-6">
                    <div class="mb-4 flex items-center justify-between text-sm">
                        <span class="text-stone">Subtotal</span>
                        <span class="text-ink">{{
                            page.props.cart.subtotal
                        }}</span>
                    </div>
                    <p class="mb-4 text-xs text-stone">
                        Envío e impuestos calculados en el checkout.
                    </p>
                    <div class="grid gap-3">
                        <LuxuryButton
                            as="link"
                            href="/checkout"
                            variant="primary"
                            @click="open = false"
                        >
                            Finalizar compra
                        </LuxuryButton>
                        <LuxuryButton variant="ghost" @click="open = false">
                            Seguir explorando
                        </LuxuryButton>
                    </div>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
