<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
</script>

<template>
    <div class="mx-auto max-w-2xl px-6 py-20 md:px-10">
        <p class="text-eyebrow mb-3">Estamos para ayudarte</p>
        <h1 class="text-display-md font-display text-ink">Contacto</h1>
        <p class="mt-4 text-sm text-stone">
            Escríbenos y un asesor te responderá a la brevedad. Para atención
            inmediata, también puedes escribirnos por WhatsApp desde cualquier
            pieza del catálogo.
        </p>

        <Form
            action="/contacto"
            method="post"
            reset-on-success
            v-slot="{ errors, processing, wasSuccessful }"
            class="mt-10 space-y-5"
        >
            <div>
                <label
                    class="mb-1.5 block text-xs tracking-wide text-ink uppercase"
                    >Nombre</label
                >
                <input
                    name="name"
                    type="text"
                    required
                    class="w-full border border-line px-3 py-2.5 text-sm"
                />
                <p v-if="errors.name" class="mt-1 text-xs text-red-600">
                    {{ errors.name }}
                </p>
            </div>
            <div>
                <label
                    class="mb-1.5 block text-xs tracking-wide text-ink uppercase"
                    >Correo electrónico</label
                >
                <input
                    name="email"
                    type="email"
                    required
                    class="w-full border border-line px-3 py-2.5 text-sm"
                />
                <p v-if="errors.email" class="mt-1 text-xs text-red-600">
                    {{ errors.email }}
                </p>
            </div>
            <div>
                <label
                    class="mb-1.5 block text-xs tracking-wide text-ink uppercase"
                    >Teléfono (opcional)</label
                >
                <input
                    name="phone"
                    type="tel"
                    class="w-full border border-line px-3 py-2.5 text-sm"
                />
            </div>
            <div>
                <label
                    class="mb-1.5 block text-xs tracking-wide text-ink uppercase"
                    >Motivo</label
                >
                <select
                    name="reason"
                    required
                    class="w-full border border-line bg-transparent px-3 py-2.5 text-sm"
                >
                    <option value="producto">Producto</option>
                    <option value="pedido">Pedido</option>
                    <option value="disponibilidad">Disponibilidad</option>
                    <option value="garantia">Garantía</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <label
                    class="mb-1.5 block text-xs tracking-wide text-ink uppercase"
                    >Mensaje</label
                >
                <textarea
                    name="message"
                    required
                    rows="5"
                    class="w-full border border-line px-3 py-2.5 text-sm"
                />
                <p v-if="errors.message" class="mt-1 text-xs text-red-600">
                    {{ errors.message }}
                </p>
            </div>
            <button
                type="submit"
                :disabled="processing"
                class="w-full bg-ink py-3.5 text-xs tracking-[0.16em] text-ivory uppercase disabled:opacity-50"
            >
                Enviar mensaje
            </button>
            <p v-if="wasSuccessful" class="text-sm text-stone">
                Hemos recibido tu mensaje. Te contactaremos pronto.
            </p>
        </Form>
    </div>
</template>
