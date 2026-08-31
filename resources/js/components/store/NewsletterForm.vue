<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
</script>

<template>
    <Form
        action="/newsletter"
        method="post"
        reset-on-success
        v-slot="{ errors, processing, wasSuccessful }"
        class="flex flex-col gap-3 sm:flex-row"
    >
        <input
            type="email"
            name="email"
            required
            placeholder="Tu correo electrónico"
            class="w-full border-b border-ivory/40 bg-transparent px-1 py-2 text-sm text-ivory placeholder:text-ivory/50 focus:border-ivory focus:outline-none sm:max-w-xs"
        />
        <button
            type="submit"
            :disabled="processing"
            class="shrink-0 border border-ivory px-6 py-2.5 text-xs tracking-[0.16em] text-ivory uppercase transition hover:bg-ivory hover:text-ink disabled:opacity-50"
        >
            Suscribirme
        </button>
        <p v-if="errors.email" class="text-xs text-red-300">
            {{ errors.email }}
        </p>
        <p v-if="wasSuccessful" class="text-xs text-ivory/80">
            Gracias por suscribirte.
        </p>
    </Form>
</template>
