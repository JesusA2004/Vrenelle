<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Address = {
    id: number;
    label: string;
    full_name: string;
    phone: string;
    street: string;
    ext_number: string;
    int_number: string | null;
    neighborhood: string;
    city: string;
    state: string;
    postal_code: string;
    references: string | null;
    is_default: boolean;
};

defineProps<{ addresses: Address[] }>();

const showForm = ref(false);
const { confirmDestructive } = useSweetAlert();

const form = useForm({
    label: 'casa',
    full_name: '',
    phone: '',
    street: '',
    ext_number: '',
    int_number: '',
    neighborhood: '',
    city: '',
    state: '',
    postal_code: '',
    references: '',
    is_default: false,
});

function submit(): void {
    form.post('/mi-cuenta/direcciones', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

async function destroy(address: Address): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar dirección',
        text: `¿Eliminar la dirección de ${address.full_name}?`,
    });

    if (confirmed) {
        router.delete(`/mi-cuenta/direcciones/${address.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg text-ink">Mis direcciones</h2>
            <button
                type="button"
                class="text-xs tracking-wide text-ink uppercase underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancelar' : 'Añadir dirección' }}
            </button>
        </div>

        <form
            v-if="showForm"
            class="grid grid-cols-1 gap-4 border border-line p-5 sm:grid-cols-2"
            @submit.prevent="submit"
        >
            <select
                v-model="form.label"
                class="border border-line bg-transparent px-3 py-2.5 text-sm sm:col-span-2"
            >
                <option value="casa">Casa</option>
                <option value="oficina">Oficina</option>
                <option value="otra">Otra</option>
            </select>
            <input
                v-model="form.full_name"
                type="text"
                placeholder="Nombre completo"
                required
                class="border border-line px-3 py-2.5 text-sm sm:col-span-2"
            />
            <input
                v-model="form.phone"
                type="tel"
                placeholder="Teléfono"
                required
                class="border border-line px-3 py-2.5 text-sm sm:col-span-2"
            />
            <input
                v-model="form.street"
                type="text"
                placeholder="Calle"
                required
                class="border border-line px-3 py-2.5 text-sm sm:col-span-2"
            />
            <input
                v-model="form.ext_number"
                type="text"
                placeholder="Número exterior"
                required
                class="border border-line px-3 py-2.5 text-sm"
            />
            <input
                v-model="form.int_number"
                type="text"
                placeholder="Número interior"
                class="border border-line px-3 py-2.5 text-sm"
            />
            <input
                v-model="form.neighborhood"
                type="text"
                placeholder="Colonia"
                required
                class="border border-line px-3 py-2.5 text-sm"
            />
            <input
                v-model="form.city"
                type="text"
                placeholder="Municipio / Alcaldía"
                required
                class="border border-line px-3 py-2.5 text-sm"
            />
            <input
                v-model="form.state"
                type="text"
                placeholder="Estado"
                required
                class="border border-line px-3 py-2.5 text-sm"
            />
            <input
                v-model="form.postal_code"
                type="text"
                placeholder="Código postal"
                required
                class="border border-line px-3 py-2.5 text-sm"
            />
            <textarea
                v-model="form.references"
                placeholder="Referencias (opcional)"
                rows="2"
                class="border border-line px-3 py-2.5 text-sm sm:col-span-2"
            />
            <label
                class="flex items-center gap-2 text-sm text-stone sm:col-span-2"
            >
                <input v-model="form.is_default" type="checkbox" />
                Establecer como predeterminada
            </label>
            <LuxuryButton
                type="submit"
                variant="primary"
                class="sm:col-span-2"
                :disabled="form.processing"
            >
                Guardar dirección
            </LuxuryButton>
        </form>

        <div
            v-if="addresses.length === 0 && !showForm"
            class="border border-line p-6 text-sm text-stone"
        >
            Aún no has guardado ninguna dirección.
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div
                v-for="address in addresses"
                :key="address.id"
                class="border border-line p-5 text-sm"
            >
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-eyebrow">{{ address.label }}</span>
                    <span v-if="address.is_default" class="text-xs text-gold"
                        >Predeterminada</span
                    >
                </div>
                <p class="text-ink">{{ address.full_name }}</p>
                <p class="text-stone">
                    {{ address.street }} {{ address.ext_number
                    }}<span v-if="address.int_number"
                        >, Int. {{ address.int_number }}</span
                    >
                </p>
                <p class="text-stone">
                    {{ address.neighborhood }}, {{ address.city }},
                    {{ address.state }}
                </p>
                <p class="text-stone">CP {{ address.postal_code }}</p>
                <button
                    type="button"
                    class="mt-3 text-xs text-stone underline hover:text-ink"
                    @click="destroy(address)"
                >
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</template>
