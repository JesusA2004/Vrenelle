<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import NewsletterForm from '@/components/store/NewsletterForm.vue';

const page = usePage<{
    store: {
        name: string;
        instagram: string | null;
        facebook: string | null;
        tiktok: string | null;
        pinterest: string | null;
        email: string | null;
        phone: string | null;
    };
}>();
</script>

<template>
    <footer class="bg-ink text-ivory">
        <div class="mx-auto max-w-7xl px-6 py-16 md:px-10">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-4">
                <div>
                    <p class="text-eyebrow mb-4 text-ivory/60">Boletín</p>
                    <h3 class="font-display mb-4 text-2xl">
                        Historias, colecciones y novedades
                    </h3>
                    <NewsletterForm />
                </div>

                <div>
                    <p class="text-eyebrow mb-4 text-ivory/60">
                        Servicio al cliente
                    </p>
                    <ul class="space-y-2.5 text-sm text-ivory/85">
                        <li>
                            <Link href="/contacto" class="link-underline"
                                >Contacto</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/legal/politica-de-envios"
                                class="link-underline"
                                >Envíos</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/legal/politica-de-devoluciones"
                                class="link-underline"
                                >Devoluciones</Link
                            >
                        </li>
                        <li>
                            <Link href="/legal/garantia" class="link-underline"
                                >Garantía</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/legal/preguntas-frecuentes"
                                class="link-underline"
                                >Preguntas frecuentes</Link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-eyebrow mb-4 text-ivory/60">
                        Sobre nosotros
                    </p>
                    <ul class="space-y-2.5 text-sm text-ivory/85">
                        <li>
                            <Link href="/colecciones" class="link-underline"
                                >Colecciones</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/legal/cuidado-de-joyeria"
                                class="link-underline"
                                >Cuidado de tus piezas</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/legal/metodos-de-pago"
                                class="link-underline"
                                >Métodos de pago</Link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-eyebrow mb-4 text-ivory/60">Legal</p>
                    <ul class="space-y-2.5 text-sm text-ivory/85">
                        <li>
                            <Link
                                href="/legal/aviso-de-privacidad"
                                class="link-underline"
                                >Aviso de privacidad</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/legal/terminos-y-condiciones"
                                class="link-underline"
                                >Términos y condiciones</Link
                            >
                        </li>
                    </ul>
                    <div class="mt-6 flex gap-4 text-sm text-ivory/85">
                        <a
                            v-if="page.props.store.instagram"
                            :href="page.props.store.instagram"
                            target="_blank"
                            rel="noopener"
                            class="link-underline"
                            >Instagram</a
                        >
                        <a
                            v-if="page.props.store.facebook"
                            :href="page.props.store.facebook"
                            target="_blank"
                            rel="noopener"
                            class="link-underline"
                            >Facebook</a
                        >
                        <a
                            v-if="page.props.store.pinterest"
                            :href="page.props.store.pinterest"
                            target="_blank"
                            rel="noopener"
                            class="link-underline"
                            >Pinterest</a
                        >
                    </div>
                </div>
            </div>

            <div
                class="mt-16 flex flex-col items-center justify-between gap-4 border-t border-ivory/15 pt-8 text-xs text-ivory/50 md:flex-row"
            >
                <p>
                    &copy; {{ new Date().getFullYear() }}
                    {{ page.props.store.name }}. Todos los derechos reservados.
                </p>
                <p v-if="page.props.store.email">
                    {{ page.props.store.email
                    }}<span v-if="page.props.store.phone">
                        · {{ page.props.store.phone }}</span
                    >
                </p>
            </div>
        </div>
    </footer>
</template>
