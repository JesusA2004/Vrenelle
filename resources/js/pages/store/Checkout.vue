<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Item = {
    id: number;
    productName: string;
    variantName: string | null;
    image: string | null;
    quantity: number;
    lineTotal: string;
};
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
};

const props = defineProps<{
    items: Item[];
    pricing: {
        subtotal: string;
        discountTotal: string;
        shippingTotal: string;
        taxTotal: string;
        total: string;
        totalCents: number;
        discountTotalCents: number;
        taxTotalCents: number;
        couponCode: string | null;
        freeShipping: boolean;
    };
    shippingMethods: Array<{
        id: number;
        name: string;
        description: string | null;
        estimatedDays: string | null;
        price: string;
    }>;
    paymentProviders: Array<{
        value: string;
        label: string;
        available: boolean;
        installments: Array<{ months: number; feePercentage: number }>;
    }>;
    addresses: Address[];
    defaultAddress: Address | null;
    idempotencyKey: string;
}>();

const page = usePage<{
    auth: {
        user: { name: string; email: string; phone: string | null } | null;
    };
}>();
const { error: showError } = useSweetAlert();

const selectedAddressId = ref(
    props.defaultAddress?.id ?? props.addresses[0]?.id ?? null,
);

const form = useForm({
    name: page.props.auth.user?.name ?? '',
    email: page.props.auth.user?.email ?? '',
    phone: page.props.auth.user?.phone ?? '',
    shipping: {
        full_name: props.defaultAddress?.full_name ?? '',
        phone: props.defaultAddress?.phone ?? '',
        street: props.defaultAddress?.street ?? '',
        ext_number: props.defaultAddress?.ext_number ?? '',
        int_number: props.defaultAddress?.int_number ?? '',
        neighborhood: props.defaultAddress?.neighborhood ?? '',
        city: props.defaultAddress?.city ?? '',
        state: props.defaultAddress?.state ?? '',
        postal_code: props.defaultAddress?.postal_code ?? '',
        references: props.defaultAddress?.references ?? '',
        country: 'MX',
    },
    billing_same_as_shipping: true,
    billing: {
        full_name: '',
        phone: '',
        street: '',
        ext_number: '',
        int_number: '',
        neighborhood: '',
        city: '',
        state: '',
        postal_code: '',
    },
    shipping_method_id: props.shippingMethods[0]?.id ?? null,
    coupon_code: props.pricing.couponCode ?? '',
    payment_method:
        props.paymentProviders.find((p) => p.available)?.value ?? 'manual',
    installment_months: null as number | null,
    save_address: !page.props.auth.user,
    notes: '',
    idempotency_key: props.idempotencyKey,
});

function applyAddress(address: Address): void {
    form.shipping = {
        full_name: address.full_name,
        phone: address.phone,
        street: address.street,
        ext_number: address.ext_number,
        int_number: address.int_number ?? '',
        neighborhood: address.neighborhood,
        city: address.city,
        state: address.state,
        postal_code: address.postal_code,
        references: address.references ?? '',
        country: 'MX',
    };
}

function onSelectAddress(id: number): void {
    selectedAddressId.value = id;
    const address = props.addresses.find((a) => a.id === id);

    if (address) {
        applyAddress(address);
    }
}

const availableInstallments = computed(() => {
    return (
        props.paymentProviders.find((p) => p.value === form.payment_method)
            ?.installments ?? []
    );
});

const submitting = ref(false);

function submit(): void {
    if (submitting.value) {
        return;
    }

    submitting.value = true;

    form.post('/checkout', {
        onError: () => {
            showError('Revisa los datos marcados en el formulario.');
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}

function applyCoupon(): void {
    router.post(
        '/carrito/cupon',
        { code: form.coupon_code },
        {
            preserveScroll: true,
            onSuccess: () => router.reload({ only: ['pricing'] }),
        },
    );
}
</script>

<template>
    <div class="mx-auto max-w-6xl px-6 py-14 md:px-10">
        <h1 class="text-display-md font-display mb-10 text-ink">Checkout</h1>

        <div class="grid grid-cols-1 gap-12 lg:grid-cols-[1fr_400px]">
            <form class="space-y-12" @submit.prevent="submit">
                <!-- Contacto -->
                <section>
                    <h2 class="font-display mb-5 text-xl text-ink">
                        1. Contacto
                    </h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Nombre completo"
                                required
                                class="w-full border border-line px-3 py-2.5 text-sm"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="Teléfono"
                                required
                                class="w-full border border-line px-3 py-2.5 text-sm"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="Correo electrónico"
                                required
                                class="w-full border border-line px-3 py-2.5 text-sm"
                            />
                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Dirección -->
                <section>
                    <h2 class="font-display mb-5 text-xl text-ink">
                        2. Dirección de envío
                    </h2>

                    <div v-if="addresses.length" class="mb-5 space-y-2">
                        <label
                            v-for="address in addresses"
                            :key="address.id"
                            class="flex cursor-pointer items-start gap-3 border border-line p-3 text-sm"
                        >
                            <input
                                type="radio"
                                :checked="selectedAddressId === address.id"
                                @change="onSelectAddress(address.id)"
                            />
                            <span>
                                <strong class="text-ink">{{
                                    address.full_name
                                }}</strong>
                                — {{ address.street }} {{ address.ext_number }},
                                {{ address.neighborhood }}, {{ address.city }}
                            </span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <input
                            v-model="form.shipping.full_name"
                            type="text"
                            placeholder="Nombre del destinatario"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm sm:col-span-2"
                        />
                        <input
                            v-model="form.shipping.street"
                            type="text"
                            placeholder="Calle"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm sm:col-span-2"
                        />
                        <input
                            v-model="form.shipping.ext_number"
                            type="text"
                            placeholder="Número exterior"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm"
                        />
                        <input
                            v-model="form.shipping.int_number"
                            type="text"
                            placeholder="Número interior (opcional)"
                            class="w-full border border-line px-3 py-2.5 text-sm"
                        />
                        <input
                            v-model="form.shipping.neighborhood"
                            type="text"
                            placeholder="Colonia"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm"
                        />
                        <input
                            v-model="form.shipping.city"
                            type="text"
                            placeholder="Municipio / Alcaldía"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm"
                        />
                        <input
                            v-model="form.shipping.state"
                            type="text"
                            placeholder="Estado"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm"
                        />
                        <input
                            v-model="form.shipping.postal_code"
                            type="text"
                            placeholder="Código postal"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm"
                        />
                        <input
                            v-model="form.shipping.phone"
                            type="tel"
                            placeholder="Teléfono de contacto"
                            required
                            class="w-full border border-line px-3 py-2.5 text-sm sm:col-span-2"
                        />
                        <textarea
                            v-model="form.shipping.references"
                            placeholder="Referencias (opcional)"
                            rows="2"
                            class="w-full border border-line px-3 py-2.5 text-sm sm:col-span-2"
                        />
                    </div>

                    <label
                        v-if="page.props.auth.user"
                        class="mt-4 flex items-center gap-2 text-sm text-stone"
                    >
                        <input v-model="form.save_address" type="checkbox" />
                        Guardar esta dirección en mi cuenta
                    </label>
                </section>

                <!-- Envío -->
                <section>
                    <h2 class="font-display mb-5 text-xl text-ink">
                        3. Método de envío
                    </h2>
                    <div class="space-y-2">
                        <label
                            v-for="method in shippingMethods"
                            :key="method.id"
                            class="flex cursor-pointer items-center justify-between border border-line p-4 text-sm"
                        >
                            <span class="flex items-center gap-3">
                                <input
                                    v-model="form.shipping_method_id"
                                    type="radio"
                                    :value="method.id"
                                />
                                <span>
                                    <span class="block text-ink">{{
                                        method.name
                                    }}</span>
                                    <span class="block text-xs text-stone">{{
                                        method.estimatedDays
                                    }}</span>
                                </span>
                            </span>
                            <span class="text-ink">{{ method.price }}</span>
                        </label>
                    </div>
                </section>

                <!-- Pago -->
                <section>
                    <h2 class="font-display mb-5 text-xl text-ink">4. Pago</h2>
                    <div class="space-y-2">
                        <label
                            v-for="provider in paymentProviders.filter(
                                (p) => p.available,
                            )"
                            :key="provider.value"
                            class="flex cursor-pointer items-center gap-3 border border-line p-4 text-sm"
                        >
                            <input
                                v-model="form.payment_method"
                                type="radio"
                                :value="provider.value"
                            />
                            {{ provider.label }}
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-3 border border-line p-4 text-sm"
                        >
                            <input
                                v-model="form.payment_method"
                                type="radio"
                                value="manual"
                            />
                            Transferencia / pago manual (confirmación por
                            nuestro equipo)
                        </label>
                    </div>

                    <div v-if="availableInstallments.length" class="mt-4">
                        <p class="text-eyebrow mb-2">
                            Meses sin intereses disponibles
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="plan in availableInstallments"
                                :key="plan.months"
                                class="flex cursor-pointer items-center gap-2 border border-line px-3 py-2 text-xs"
                            >
                                <input
                                    v-model="form.installment_months"
                                    type="radio"
                                    :value="plan.months"
                                />
                                {{ plan.months }} MSI
                            </label>
                        </div>
                    </div>
                </section>

                <LuxuryButton
                    type="submit"
                    variant="primary"
                    class="w-full"
                    :disabled="submitting || form.processing"
                >
                    Confirmar pedido
                </LuxuryButton>
            </form>

            <!-- Resumen -->
            <aside class="h-fit space-y-6 border border-line p-6">
                <div class="space-y-4">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex gap-3"
                    >
                        <div
                            class="size-16 shrink-0 overflow-hidden bg-ivory-deep"
                        >
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.productName"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="flex-1 text-sm">
                            <p class="text-ink">{{ item.productName }}</p>
                            <p
                                v-if="item.variantName"
                                class="text-xs text-stone"
                            >
                                {{ item.variantName }}
                            </p>
                            <p class="text-xs text-stone">
                                Cantidad: {{ item.quantity }}
                            </p>
                        </div>
                        <span class="text-sm text-ink">{{
                            item.lineTotal
                        }}</span>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-line pt-4">
                    <input
                        v-model="form.coupon_code"
                        type="text"
                        placeholder="Código de descuento"
                        class="w-full border border-line px-3 py-2 text-sm"
                    />
                    <button
                        type="button"
                        class="border border-ink px-4 text-xs tracking-wide text-ink uppercase"
                        @click="applyCoupon"
                    >
                        Aplicar
                    </button>
                </div>

                <div class="space-y-2 border-t border-line pt-4 text-sm">
                    <div class="flex justify-between text-stone">
                        <span>Subtotal</span><span>{{ pricing.subtotal }}</span>
                    </div>
                    <div
                        v-if="pricing.discountTotalCents > 0"
                        class="flex justify-between text-stone"
                    >
                        <span>Descuento</span
                        ><span>-{{ pricing.discountTotal }}</span>
                    </div>
                    <div class="flex justify-between text-stone">
                        <span>Envío</span
                        ><span>{{
                            pricing.freeShipping
                                ? 'Gratis'
                                : pricing.shippingTotal
                        }}</span>
                    </div>
                    <div
                        v-if="pricing.taxTotalCents > 0"
                        class="flex justify-between text-stone"
                    >
                        <span>Impuestos</span
                        ><span>{{ pricing.taxTotal }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t border-line pt-2 text-base text-ink"
                    >
                        <span>Total</span><span>{{ pricing.total }}</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
