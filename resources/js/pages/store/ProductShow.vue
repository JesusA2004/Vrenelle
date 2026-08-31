<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import Breadcrumb from '@/components/store/Breadcrumb.vue';
import LuxuryButton from '@/components/store/LuxuryButton.vue';
import type { ProductCardData } from '@/components/store/ProductCard.vue';
import ProductCard from '@/components/store/ProductCard.vue';
import ProductGallery from '@/components/store/ProductGallery.vue';
import QuantitySelector from '@/components/store/QuantitySelector.vue';
import SectionHeading from '@/components/store/SectionHeading.vue';
import WishlistButton from '@/components/store/WishlistButton.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { useCartDrawer } from '@/composables/useCartDrawer';
import { useRecentlyViewed } from '@/composables/useRecentlyViewed';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { useWhatsApp } from '@/composables/useWhatsApp';

type Variant = {
    id: number;
    name: string;
    available: boolean;
    price: string | null;
    options: { name: string; value: string }[];
    images: string[];
};

type Product = {
    id: number;
    slug: string;
    name: string;
    sku: string;
    shortDescription: string | null;
    description: string | null;
    collection: { name: string; slug: string } | null;
    category: { name: string; slug: string } | null;
    material: string | null;
    stone: string | null;
    showPrice: boolean;
    price: string | null;
    compareAtPrice: string | null;
    discountPercentage: number | null;
    inventoryMode: 'stock' | 'made_to_order' | 'inquiry_only';
    hasImmediateStock: boolean;
    isCustomizable: boolean;
    images: { id: number; url: string; alt: string | null; type: string }[];
    variants: Variant[];
    attributes: Record<string, string>;
    warranty: string | null;
    careInstructions: string | null;
};

const props = defineProps<{
    product: Product;
    related: ProductCardData[];
    breadcrumbs: Array<{ label: string; href: string | null }>;
}>();

const quantity = ref(1);
const selectedOptions = ref<Record<string, string>>({});
const inquiryForm = ref({ name: '', phone: '', email: '', message: '' });
const inquirySubmitting = ref(false);

const { open: openCartDrawer } = useCartDrawer();
const { success, stockExceeded } = useSweetAlert();
const { openRequest } = useWhatsApp();
const { record } = useRecentlyViewed();

onMounted(() => {
    record({
        slug: props.product.slug,
        name: props.product.name,
        image: props.product.images[0]?.url ?? null,
        price: props.product.price,
    });

    if (
        props.product.variants.length &&
        props.product.variants[0].options.length
    ) {
        const first =
            props.product.variants.find((v) => v.available) ??
            props.product.variants[0];
        selectedOptions.value = Object.fromEntries(
            first.options.map((o) => [o.name, o.value]),
        );
    }
});

function optionGroups(): Record<string, string[]> {
    const groups: Record<string, Set<string>> = {};

    for (const variant of props.product.variants) {
        for (const option of variant.options) {
            groups[option.name] ??= new Set();
            groups[option.name].add(option.value);
        }
    }

    return Object.fromEntries(
        Object.entries(groups).map(([k, v]) => [k, Array.from(v)]),
    );
}

function selectedVariant(): Variant | null {
    if (!props.product.variants.length) {
        return null;
    }

    return (
        props.product.variants.find(
            (variant) =>
                variant.options.length ===
                    Object.keys(selectedOptions.value).length &&
                variant.options.every(
                    (o) => selectedOptions.value[o.name] === o.value,
                ),
        ) ?? null
    );
}

function addToCart(): void {
    const variant = selectedVariant();

    if (props.product.variants.length && !variant) {
        stockExceeded('Selecciona una variante disponible antes de continuar.');

        return;
    }

    router.post(
        '/carrito/agregar',
        {
            product_id: props.product.id,
            product_variant_id: variant?.id ?? null,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['cart', 'flash'],
            onSuccess: () => {
                success('Pieza añadida a tu selección.');
                openCartDrawer();
            },
        },
    );
}

async function requestOutOfStock(): Promise<void> {
    await openRequest({
        productId: props.product.id,
        quantity: quantity.value,
        reason: 'out_of_stock',
    });
}

async function requestMadeToOrder(): Promise<void> {
    await openRequest({ productId: props.product.id, reason: 'made_to_order' });
}

async function requestInquiry(): Promise<void> {
    await openRequest({ productId: props.product.id, reason: 'inquiry' });
}

function submitInquiry(): void {
    inquirySubmitting.value = true;

    router.post(
        '/solicitudes-producto',
        {
            product_id: props.product.id,
            quantity: quantity.value,
            ...inquiryForm.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                inquirySubmitting.value = false;
            },
            onSuccess: () => {
                inquiryForm.value = {
                    name: '',
                    phone: '',
                    email: '',
                    message: '',
                };
            },
        },
    );
}
</script>

<template>
    <div>
        <Head>
            <title>{{ product.name }}</title>
            <meta
                v-if="product.shortDescription"
                name="description"
                :content="product.shortDescription"
            />
        </Head>

        <div class="mx-auto max-w-7xl px-6 pt-8 md:px-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div
            class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-6 py-8 md:px-10 lg:grid-cols-[1.5fr_1fr]"
        >
            <ProductGallery :images="product.images" />

            <div>
                <p v-if="product.collection" class="text-eyebrow mb-3">
                    {{ product.collection.name }}
                </p>
                <div class="flex items-start justify-between gap-4">
                    <h1 class="text-display-sm font-display text-ink">
                        {{ product.name }}
                    </h1>
                    <WishlistButton :product-id="product.id" />
                </div>
                <p class="mt-1 text-xs text-stone">
                    Referencia {{ product.sku }}
                </p>

                <div class="mt-5 flex items-baseline gap-3">
                    <span
                        v-if="product.showPrice && product.price"
                        class="text-xl text-ink"
                        >{{ product.price }}</span
                    >
                    <span v-else class="text-lg text-stone italic"
                        >Precio bajo consulta</span
                    >
                    <span
                        v-if="product.compareAtPrice"
                        class="text-sm text-stone line-through"
                        >{{ product.compareAtPrice }}</span
                    >
                    <span
                        v-if="product.discountPercentage"
                        class="text-xs text-gold"
                        >-{{ product.discountPercentage }}%</span
                    >
                </div>

                <p
                    v-if="product.shortDescription"
                    class="mt-5 text-[0.95rem] leading-relaxed text-stone"
                >
                    {{ product.shortDescription }}
                </p>

                <!-- Variant selectors -->
                <div
                    v-for="(values, name) in optionGroups()"
                    :key="name"
                    class="mt-7"
                >
                    <p class="text-eyebrow mb-3">{{ name }}</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="value in values"
                            :key="value"
                            type="button"
                            class="min-w-11 border px-3 py-2 text-sm transition"
                            :class="
                                selectedOptions[name] === value
                                    ? 'border-ink bg-ink text-ivory'
                                    : 'border-line text-ink hover:border-ink'
                            "
                            @click="selectedOptions[name] = value"
                        >
                            {{ value }}
                        </button>
                    </div>
                </div>

                <!-- Purchase actions -->
                <div class="mt-8 space-y-4">
                    <template v-if="product.inventoryMode === 'stock'">
                        <template
                            v-if="
                                product.hasImmediateStock ||
                                selectedVariant()?.available
                            "
                        >
                            <div class="flex items-center gap-4">
                                <QuantitySelector v-model="quantity" />
                                <LuxuryButton
                                    variant="primary"
                                    class="flex-1"
                                    @click="addToCart"
                                >
                                    Añadir a la selección
                                </LuxuryButton>
                            </div>
                        </template>
                        <template v-else>
                            <p class="text-sm text-stone">
                                Esta pieza puede solicitarse especialmente para
                                ti. Nuestro equipo confirmará disponibilidad y
                                tiempo estimado.
                            </p>
                            <LuxuryButton
                                variant="outline"
                                class="w-full"
                                @click="requestOutOfStock"
                            >
                                Solicitar por WhatsApp
                            </LuxuryButton>
                        </template>
                    </template>

                    <template
                        v-else-if="product.inventoryMode === 'made_to_order'"
                    >
                        <p class="text-sm text-stone">
                            Disponible bajo pedido. Esta pieza puede solicitarse
                            especialmente para ti; nuestro equipo confirmará
                            disponibilidad y tiempo estimado.
                        </p>
                        <LuxuryButton
                            variant="primary"
                            class="w-full"
                            @click="requestMadeToOrder"
                        >
                            Solicitar pieza por WhatsApp
                        </LuxuryButton>
                    </template>

                    <template v-else>
                        <p class="text-sm text-stone">
                            Consulta disponibilidad y precio con uno de nuestros
                            asesores.
                        </p>
                        <LuxuryButton
                            variant="primary"
                            class="w-full"
                            @click="requestInquiry"
                        >
                            Consultar disponibilidad
                        </LuxuryButton>
                    </template>

                    <!-- Optional inquiry form, always available for pieces without live stock -->
                    <details
                        v-if="
                            product.inventoryMode !== 'stock' ||
                            !(
                                product.hasImmediateStock ||
                                selectedVariant()?.available
                            )
                        "
                        class="border border-line p-4"
                    >
                        <summary
                            class="cursor-pointer text-xs tracking-[0.12em] text-ink uppercase"
                        >
                            Prefiero que me contacten
                        </summary>
                        <form
                            class="mt-4 space-y-3"
                            @submit.prevent="submitInquiry"
                        >
                            <input
                                v-model="inquiryForm.name"
                                type="text"
                                required
                                placeholder="Nombre"
                                class="w-full border border-line px-3 py-2 text-sm"
                            />
                            <input
                                v-model="inquiryForm.phone"
                                type="tel"
                                required
                                placeholder="Teléfono"
                                class="w-full border border-line px-3 py-2 text-sm"
                            />
                            <input
                                v-model="inquiryForm.email"
                                type="email"
                                placeholder="Correo electrónico (opcional)"
                                class="w-full border border-line px-3 py-2 text-sm"
                            />
                            <textarea
                                v-model="inquiryForm.message"
                                placeholder="Mensaje (opcional)"
                                rows="3"
                                class="w-full border border-line px-3 py-2 text-sm"
                            />
                            <LuxuryButton
                                type="submit"
                                variant="secondary"
                                class="w-full"
                                :disabled="inquirySubmitting"
                            >
                                Enviar solicitud
                            </LuxuryButton>
                        </form>
                    </details>
                </div>

                <Accordion
                    type="single"
                    collapsible
                    class="mt-10 border-t border-line"
                >
                    <AccordionItem
                        v-if="Object.keys(product.attributes).length"
                        value="detalles"
                    >
                        <AccordionTrigger
                            class="text-sm tracking-wide text-ink uppercase"
                            >Detalles de la pieza</AccordionTrigger
                        >
                        <AccordionContent>
                            <dl class="space-y-2 text-sm text-stone">
                                <div
                                    v-for="(value, key) in product.attributes"
                                    :key="key"
                                    class="flex justify-between gap-4"
                                >
                                    <dt>{{ key }}</dt>
                                    <dd class="text-ink">{{ value }}</dd>
                                </div>
                            </dl>
                        </AccordionContent>
                    </AccordionItem>
                    <AccordionItem value="envio">
                        <AccordionTrigger
                            class="text-sm tracking-wide text-ink uppercase"
                            >Envío y devoluciones</AccordionTrigger
                        >
                        <AccordionContent class="text-sm text-stone">
                            Envío asegurado a todo México. Cuentas con un
                            periodo de devolución para piezas en su empaque
                            original y sin uso.
                        </AccordionContent>
                    </AccordionItem>
                    <AccordionItem
                        v-if="product.careInstructions"
                        value="cuidados"
                    >
                        <AccordionTrigger
                            class="text-sm tracking-wide text-ink uppercase"
                            >Cuidados</AccordionTrigger
                        >
                        <AccordionContent class="text-sm text-stone">{{
                            product.careInstructions
                        }}</AccordionContent>
                    </AccordionItem>
                    <AccordionItem value="autenticidad">
                        <AccordionTrigger
                            class="text-sm tracking-wide text-ink uppercase"
                            >Autenticidad y garantía</AccordionTrigger
                        >
                        <AccordionContent class="text-sm text-stone">
                            {{
                                product.warranty ??
                                'Cada pieza Vrenelle incluye certificado de autenticidad.'
                            }}
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>

        <section
            v-if="related.length"
            class="mx-auto max-w-7xl px-6 py-20 md:px-10"
        >
            <SectionHeading
                eyebrow="Complementa tu elección"
                title="También podría gustarte"
            />
            <div class="mt-10 grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-4">
                <ProductCard
                    v-for="item in related"
                    :key="item.id"
                    :product="item"
                />
            </div>
        </section>
    </div>
</template>
