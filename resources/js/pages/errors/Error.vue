<script setup lang="ts">
import { computed } from 'vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';

const props = defineProps<{ status: number }>();

const content = computed(() => {
    return (
        {
            404: {
                title: 'Esta pieza no existe',
                description:
                    'La página que buscas no está disponible o ha cambiado de lugar.',
            },
            403: {
                title: 'Acceso restringido',
                description: 'No cuentas con permiso para ver esta página.',
            },
            419: {
                title: 'Tu sesión ha expirado',
                description:
                    'Por seguridad, actualiza la página e inténtalo nuevamente.',
            },
            429: {
                title: 'Demasiadas solicitudes',
                description: 'Espera un momento antes de intentarlo de nuevo.',
            },
            500: {
                title: 'Algo no salió como esperábamos',
                description:
                    'Nuestro equipo ya fue notificado. Intenta de nuevo en unos minutos.',
            },
            503: {
                title: 'Estamos en mantenimiento',
                description: 'Volvemos muy pronto con una mejor experiencia.',
            },
        }[props.status] ?? {
            title: 'Algo no salió como esperábamos',
            description: 'Intenta de nuevo en unos minutos.',
        }
    );
});
</script>

<template>
    <div
        class="flex min-h-[70vh] flex-col items-center justify-center px-6 py-24 text-center"
    >
        <p class="text-eyebrow mb-4">Error {{ status }}</p>
        <h1 class="text-display-lg font-display text-ink">
            {{ content.title }}
        </h1>
        <p class="mt-4 max-w-md text-sm text-stone">
            {{ content.description }}
        </p>
        <LuxuryButton
            as="link"
            href="/colecciones"
            variant="outline"
            class="mt-10"
        >
            Volver a las colecciones
        </LuxuryButton>
    </div>
</template>
