<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import StoreLayout from '@/layouts/StoreLayout.vue';

withDefaults(defineProps<{ title?: string }>(), { title: '' });

const page = usePage<{ auth: { user: { name: string } } }>();
const { isCurrentOrParentUrl } = useCurrentUrl();

const links = [
    { label: 'Resumen', href: '/mi-cuenta' },
    { label: 'Mis pedidos', href: '/mi-cuenta/pedidos' },
    { label: 'Mis direcciones', href: '/mi-cuenta/direcciones' },
    { label: 'Favoritos', href: '/favoritos' },
    { label: 'Datos personales', href: '/settings/profile' },
    { label: 'Contraseña', href: '/settings/security' },
    { label: 'Apariencia', href: '/settings/appearance' },
];
</script>

<template>
    <StoreLayout :title="title">
        <div class="mx-auto max-w-6xl px-6 py-14 md:px-10">
            <p class="text-eyebrow mb-2">Mi cuenta</p>
            <h1 class="font-display text-display-sm mb-10 text-ink">
                Hola, {{ page.props.auth.user.name.split(' ')[0] }}
            </h1>

            <div class="grid grid-cols-1 gap-10 md:grid-cols-[220px_1fr]">
                <nav class="flex gap-2 overflow-x-auto md:block md:space-y-1">
                    <Link
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="block shrink-0 border-b-2 px-1 py-2 text-sm whitespace-nowrap md:border-b-0 md:border-l-2 md:px-4"
                        :class="
                            isCurrentOrParentUrl(link.href)
                                ? 'border-ink text-ink'
                                : 'border-transparent text-stone hover:text-ink'
                        "
                    >
                        {{ link.label }}
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="block shrink-0 border-b-2 border-transparent px-1 py-2 text-left text-sm whitespace-nowrap text-stone hover:text-ink md:border-l-2 md:px-4"
                    >
                        Cerrar sesión
                    </Link>
                </nav>

                <div>
                    <slot />
                </div>
            </div>
        </div>
    </StoreLayout>
</template>
