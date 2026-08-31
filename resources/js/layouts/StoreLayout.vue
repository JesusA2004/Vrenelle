<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import CartDrawer from '@/components/store/CartDrawer.vue';
import MobileMenu from '@/components/store/MobileMenu.vue';
import SearchOverlay from '@/components/store/SearchOverlay.vue';
import StoreFooter from '@/components/store/StoreFooter.vue';
import StoreHeader from '@/components/store/StoreHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { useCartDrawer } from '@/composables/useCartDrawer';
import { useSweetAlert } from '@/composables/useSweetAlert';

withDefaults(defineProps<{ title?: string; description?: string }>(), {
    title: '',
    description: '',
});

const page = usePage<{
    flash: {
        cartError?: {
            message: string;
            productName: string;
            whatsappUrl: string | null;
        } | null;
    };
    store: { name: string; tagline: string | null };
}>();

const searchOpen = ref(false);
const { isOpen: cartOpen } = useCartDrawer();
const mobileMenuOpen = ref(false);

const { stockExceeded } = useSweetAlert();

// The header is fixed (it needs to float transparently over the Home hero),
// so every other page must reserve its height at the top of the content —
// only Home lets its hero run full-bleed underneath it.
const isHome = computed(() => page.url === '/');

watch(
    () => page.props.flash.cartError,
    (error) => {
        if (error) {
            stockExceeded(error.message, error.whatsappUrl);
        }
    },
);
</script>

<template>
    <Head :title="title || undefined">
        <meta
            name="description"
            :content="description || page.props.store.tagline || ''"
        />
    </Head>

    <div class="store-shell flex min-h-screen flex-col">
        <StoreHeader
            @open-search="searchOpen = true"
            @open-cart="cartOpen = true"
            @open-mobile-menu="mobileMenuOpen = true"
        />

        <main class="flex-1" :class="{ 'pt-20': !isHome }">
            <slot />
        </main>

        <StoreFooter />

        <SearchOverlay :open="searchOpen" @close="searchOpen = false" />
        <MobileMenu :open="mobileMenuOpen" @close="mobileMenuOpen = false" />
        <CartDrawer v-model:open="cartOpen" />
        <Toaster position="bottom-right" />
    </div>
</template>
