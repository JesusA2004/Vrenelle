<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import { computed } from 'vue';
import { useSweetAlert } from '@/composables/useSweetAlert';

const props = defineProps<{ productId: number; size?: 'sm' | 'md' }>();

const page = usePage<{
    auth: { user: unknown };
    wishlist: { productIds: number[] };
}>();

const isFavorite = computed(() =>
    (page.props.wishlist?.productIds ?? []).includes(props.productId),
);

const { info } = useSweetAlert();

function toggle(): void {
    if (!page.props.auth.user) {
        info(
            'Crea una cuenta o inicia sesión para guardar piezas en tus favoritos.',
            'Inicia sesión',
        );

        return;
    }

    router.post(
        '/favoritos/alternar',
        { product_id: props.productId },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['wishlist', 'flash'],
        },
    );
}
</script>

<template>
    <button
        type="button"
        :aria-pressed="isFavorite"
        :aria-label="isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos'"
        class="inline-flex items-center justify-center rounded-full bg-ivory/90 p-2 text-ink shadow-sm backdrop-blur transition hover:scale-105"
        :class="size === 'sm' ? 'size-8' : 'size-10'"
        @click.stop.prevent="toggle"
    >
        <Heart
            :class="[
                size === 'sm' ? 'size-4' : 'size-[18px]',
                isFavorite ? 'fill-ink text-ink' : 'text-ink',
            ]"
        />
    </button>
</template>
