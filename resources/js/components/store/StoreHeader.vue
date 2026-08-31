<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Heart, Menu, Search, ShoppingBag, User } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import MegaMenu from '@/components/store/MegaMenu.vue';

const emit = defineEmits<{
    openSearch: [];
    openCart: [];
    openMobileMenu: [];
}>();

const page = usePage<{
    auth: { user: { name: string } | null };
    cart: { count: number };
    store: { name: string; logo: string | null };
    nav: {
        categories: { name: string; slug: string; image: string | null }[];
        collections: { name: string; slug: string; image: string | null }[];
    };
}>();

const scrolled = ref(false);
const menuOpen = ref(false);
let onScroll: (() => void) | undefined;

onMounted(() => {
    onScroll = () => {
        scrolled.value = window.scrollY > 24;
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    if (onScroll) {
        window.removeEventListener('scroll', onScroll);
    }
});

const isHome = computed(() => page.url === '/');

// Solid state: dark icons/text on the opaque ivory bar (always safe).
// Transparent state (home hero only): ivory icons/text, plus a soft drop
// shadow as a safety net — the hero's own scrim/overlay is the real
// contrast guarantee, this just protects against unusually bright photos.
const iconClass = computed(() =>
    scrolled.value || !isHome.value
        ? 'text-ink'
        : 'text-ivory drop-shadow-[0_1px_3px_rgba(0,0,0,0.45)]',
);
const textClass = computed(() =>
    scrolled.value || !isHome.value
        ? 'text-ink'
        : 'text-ivory [text-shadow:0_1px_3px_rgba(0,0,0,0.45)]',
);
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-50 transition-all duration-500"
        :class="
            scrolled || !isHome
                ? 'border-b border-line bg-ivory/95 backdrop-blur-md'
                : 'border-b border-transparent bg-transparent'
        "
        @mouseleave="menuOpen = false"
    >
        <div
            class="relative mx-auto grid h-20 max-w-7xl grid-cols-3 items-center px-6 md:px-10"
        >
            <div class="flex items-center gap-6">
                <button
                    type="button"
                    class="md:hidden"
                    aria-label="Abrir menú"
                    @click="emit('openMobileMenu')"
                >
                    <Menu
                        class="size-5"
                        :class="iconClass"
                    />
                </button>
                <nav class="hidden items-center gap-7 md:flex">
                    <button
                        type="button"
                        class="text-xs tracking-[0.16em] uppercase transition-colors"
                        :class="textClass"
                        @mouseenter="menuOpen = true"
                    >
                        Joyería
                    </button>
                    <Link
                        href="/colecciones"
                        class="text-xs tracking-[0.16em] uppercase transition-colors"
                        :class="textClass"
                    >
                        Colecciones
                    </Link>
                    <Link
                        href="/joyeria?novedades=1"
                        class="text-xs tracking-[0.16em] uppercase transition-colors"
                        :class="textClass"
                    >
                        Novedades
                    </Link>
                </nav>
            </div>

            <Link href="/" class="justify-self-center text-center">
                <img
                    v-if="page.props.store.logo"
                    :src="page.props.store.logo"
                    :alt="page.props.store.name"
                    class="h-8"
                />
                <span
                    v-else
                    class="font-display text-2xl tracking-[0.08em]"
                    :class="textClass"
                >
                    {{ page.props.store.name }}
                </span>
            </Link>

            <div class="flex items-center justify-end gap-5">
                <button
                    type="button"
                    aria-label="Buscar"
                    @click="emit('openSearch')"
                >
                    <Search
                        class="size-[18px]"
                        :class="iconClass"
                    />
                </button>
                <Link
                    :href="page.props.auth.user ? '/mi-cuenta' : '/login'"
                    aria-label="Mi cuenta"
                    class="hidden sm:block"
                >
                    <User
                        class="size-[18px]"
                        :class="iconClass"
                    />
                </Link>
                <Link
                    href="/favoritos"
                    aria-label="Favoritos"
                    class="hidden sm:block"
                >
                    <Heart
                        class="size-[18px]"
                        :class="iconClass"
                    />
                </Link>
                <button
                    type="button"
                    aria-label="Carrito"
                    class="relative"
                    @click="emit('openCart')"
                >
                    <ShoppingBag
                        class="size-[18px]"
                        :class="iconClass"
                    />
                    <span
                        v-if="page.props.cart.count > 0"
                        class="absolute -top-2 -right-2 flex size-4 items-center justify-center rounded-full bg-gold text-[0.6rem] text-ivory"
                    >
                        {{ page.props.cart.count }}
                    </span>
                </button>
            </div>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <MegaMenu
                v-if="menuOpen"
                :categories="page.props.nav.categories"
                :collections="page.props.nav.collections"
                @close="menuOpen = false"
            />
        </Transition>
    </header>
</template>
