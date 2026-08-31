<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, X } from '@lucide/vue';
import { ref } from 'vue';

defineProps<{ open: boolean }>();
defineEmits<{ close: [] }>();

const page = usePage<{
    auth: { user: { name: string } | null };
    nav: { categories: { name: string; slug: string }[] };
}>();

const categoriesExpanded = ref(false);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-250 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div v-if="open" class="fixed inset-0 z-[80] flex flex-col bg-ivory">
            <div
                class="flex items-center justify-between border-b border-line px-6 py-5"
            >
                <span class="font-display text-lg text-ink">Menú</span>
                <button
                    type="button"
                    aria-label="Cerrar menú"
                    @click="$emit('close')"
                >
                    <X class="size-6 text-ink" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-6 py-6">
                <button
                    type="button"
                    class="font-display flex w-full items-center justify-between border-b border-line py-4 text-xl text-ink"
                    @click="categoriesExpanded = !categoriesExpanded"
                >
                    Joyería
                    <ChevronDown
                        class="size-4 transition-transform"
                        :class="categoriesExpanded ? 'rotate-180' : ''"
                    />
                </button>
                <div v-if="categoriesExpanded" class="space-y-3 py-3 pl-4">
                    <Link
                        v-for="category in page.props.nav.categories"
                        :key="category.slug"
                        :href="`/joyeria?categoria=${category.slug}`"
                        class="block text-sm text-stone"
                        @click="$emit('close')"
                    >
                        {{ category.name }}
                    </Link>
                </div>

                <Link
                    href="/colecciones"
                    class="font-display block border-b border-line py-4 text-xl text-ink"
                    @click="$emit('close')"
                >
                    Colecciones
                </Link>
                <Link
                    href="/joyeria?novedades=1"
                    class="font-display block border-b border-line py-4 text-xl text-ink"
                    @click="$emit('close')"
                >
                    Novedades
                </Link>
                <Link
                    href="/contacto"
                    class="font-display block border-b border-line py-4 text-xl text-ink"
                    @click="$emit('close')"
                >
                    Contacto
                </Link>
            </nav>

            <div class="border-t border-line px-6 py-6">
                <Link
                    :href="page.props.auth.user ? '/mi-cuenta' : '/login'"
                    class="block text-sm tracking-[0.14em] text-ink uppercase"
                    @click="$emit('close')"
                >
                    {{ page.props.auth.user ? 'Mi cuenta' : 'Iniciar sesión' }}
                </Link>
                <Link
                    href="/favoritos"
                    class="mt-3 block text-sm tracking-[0.14em] text-ink uppercase"
                    @click="$emit('close')"
                >
                    Favoritos
                </Link>
            </div>
        </div>
    </Transition>
</template>
