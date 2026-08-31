<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Star, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { useSweetAlert } from '@/composables/useSweetAlert';

const props = defineProps<{
    product?: Record<string, any>;
    categories: Array<{ id: number; name: string }>;
    collections: Array<{ id: number; name: string }>;
    materials: Array<{ id: number; name: string }>;
    stones: Array<{ id: number; name: string }>;
}>();

const isEdit = Boolean(props.product);
const { confirmDestructive } = useSweetAlert();

const form = useForm({
    category_id: props.product?.category_id ?? null,
    collection_id: props.product?.collection_id ?? null,
    material_id: props.product?.material_id ?? null,
    stone_id: props.product?.stone_id ?? null,
    name: props.product?.name ?? '',
    slug: props.product?.slug ?? '',
    sku: props.product?.sku ?? '',
    short_description: props.product?.short_description ?? '',
    description: props.product?.description ?? '',
    price: props.product?.price ?? '',
    compare_at_price: props.product?.compare_at_price ?? '',
    cost: props.product?.cost ?? '',
    inventory_mode: props.product?.inventory_mode ?? 'stock',
    stock: props.product?.stock ?? 0,
    low_stock_threshold: props.product?.low_stock_threshold ?? 2,
    show_price: props.product?.show_price ?? true,
    is_active: props.product?.is_active ?? true,
    is_featured: props.product?.is_featured ?? false,
    is_new: props.product?.is_new ?? false,
    is_exclusive: props.product?.is_exclusive ?? false,
    is_customizable: props.product?.is_customizable ?? false,
    carats: props.product?.carats ?? '',
    purity: props.product?.purity ?? '',
    weight: props.product?.weight ?? '',
    color: props.product?.color ?? '',
    cut: props.product?.cut ?? '',
    size: props.product?.size ?? '',
    length: props.product?.length ?? '',
    width: props.product?.width ?? '',
    height: props.product?.height ?? '',
    finish: props.product?.finish ?? '',
    certification: props.product?.certification ?? '',
    country_of_origin: props.product?.country_of_origin ?? 'México',
    warranty: props.product?.warranty ?? '',
    care_instructions: props.product?.care_instructions ?? '',
    meta_title: props.product?.meta_title ?? '',
    meta_description: props.product?.meta_description ?? '',
    published_at: props.product?.published_at?.slice(0, 10) ?? '',
    new_images: [] as File[],
});

function onFilesSelected(event: Event): void {
    const files = (event.target as HTMLInputElement).files;
    form.new_images = files ? Array.from(files) : [];
}

function submit(): void {
    if (isEdit) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            `/admin/productos/${props.product!.id}`,
            {
                preserveScroll: true,
                onSuccess: () => (form.new_images = []),
            },
        );
    } else {
        form.post('/admin/productos');
    }
}

async function deleteImage(imageId: number): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar imagen',
        text: '¿Eliminar esta imagen del producto?',
    });

    if (confirmed) {
        router.delete(
            `/admin/productos/${props.product!.id}/imagenes/${imageId}`,
            { preserveScroll: true },
        );
    }
}

function setPrimary(imageId: number): void {
    router.post(
        `/admin/productos/${props.product!.id}/imagenes/${imageId}/principal`,
        {},
        { preserveScroll: true },
    );
}

// Variants
const variantForm = useForm({
    name: '',
    sku: '',
    price: '',
    stock: 0,
    is_active: true,
    options: [{ name: 'Talla', value: '' }],
});

function addVariant(): void {
    variantForm.post(`/admin/productos/${props.product!.id}/variantes`, {
        preserveScroll: true,
        onSuccess: () => variantForm.reset(),
    });
}

async function deleteVariant(variantId: number): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar variante',
        text: '¿Eliminar esta variante?',
    });

    if (confirmed) {
        router.delete(
            `/admin/productos/${props.product!.id}/variantes/${variantId}`,
            { preserveScroll: true },
        );
    }
}
</script>

<template>
    <div class="max-w-4xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? `Editar: ${product!.name}` : 'Nuevo producto' }}
        </h1>

        <form @submit.prevent="submit">
            <Tabs default-value="general" class="w-full">
                <TabsList>
                    <TabsTrigger value="general">General</TabsTrigger>
                    <TabsTrigger value="atributos">Atributos</TabsTrigger>
                    <TabsTrigger value="inventario"
                        >Precio e inventario</TabsTrigger
                    >
                    <TabsTrigger value="imagenes" :disabled="!isEdit"
                        >Imágenes</TabsTrigger
                    >
                    <TabsTrigger value="variantes" :disabled="!isEdit"
                        >Variantes</TabsTrigger
                    >
                    <TabsTrigger value="seo">SEO</TabsTrigger>
                </TabsList>

                <TabsContent value="general" class="space-y-5 pt-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Nombre</Label>
                            <Input v-model="form.name" required />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label>SKU</Label>
                            <Input v-model="form.sku" required />
                            <p
                                v-if="form.errors.sku"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.sku }}
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label>Slug (opcional)</Label>
                            <Input v-model="form.slug" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Categoría</Label>
                            <select
                                v-model="form.category_id"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            >
                                <option :value="null">— Ninguna —</option>
                                <option
                                    v-for="c in categories"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label>Colección</Label>
                            <select
                                v-model="form.collection_id"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            >
                                <option :value="null">— Ninguna —</option>
                                <option
                                    v-for="c in collections"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label>Fecha de publicación</Label>
                            <Input v-model="form.published_at" type="date" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Descripción corta</Label>
                        <Textarea v-model="form.short_description" rows="2" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Descripción</Label>
                        <Textarea v-model="form.description" rows="5" />
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 text-sm"
                            ><Switch v-model="form.is_active" /> Activo</label
                        >
                        <label class="flex items-center gap-2 text-sm"
                            ><Switch v-model="form.is_featured" />
                            Destacado</label
                        >
                        <label class="flex items-center gap-2 text-sm"
                            ><Switch v-model="form.is_new" /> Nuevo</label
                        >
                        <label class="flex items-center gap-2 text-sm"
                            ><Switch v-model="form.is_exclusive" />
                            Exclusivo</label
                        >
                        <label class="flex items-center gap-2 text-sm"
                            ><Switch v-model="form.is_customizable" />
                            Personalizable</label
                        >
                    </div>
                </TabsContent>

                <TabsContent value="atributos" class="space-y-4 pt-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label>Material</Label>
                            <select
                                v-model="form.material_id"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            >
                                <option :value="null">— Ninguno —</option>
                                <option
                                    v-for="m in materials"
                                    :key="m.id"
                                    :value="m.id"
                                >
                                    {{ m.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label>Piedra</Label>
                            <select
                                v-model="form.stone_id"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            >
                                <option :value="null">— Ninguna —</option>
                                <option
                                    v-for="s in stones"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label>Quilates</Label
                            ><Input v-model="form.carats" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Pureza</Label><Input v-model="form.purity" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Peso</Label><Input v-model="form.weight" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Color</Label><Input v-model="form.color" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Corte</Label><Input v-model="form.cut" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Talla</Label><Input v-model="form.size" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Longitud</Label
                            ><Input v-model="form.length" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Ancho</Label><Input v-model="form.width" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Alto</Label><Input v-model="form.height" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Acabado</Label
                            ><Input v-model="form.finish" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Certificación</Label
                            ><Input v-model="form.certification" />
                        </div>
                        <div class="grid gap-2">
                            <Label>País de fabricación</Label
                            ><Input v-model="form.country_of_origin" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Garantía</Label><Input v-model="form.warranty" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Cuidados</Label
                        ><Textarea v-model="form.care_instructions" rows="3" />
                    </div>
                </TabsContent>

                <TabsContent value="inventario" class="space-y-4 pt-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label>Precio (MXN)</Label>
                            <Input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                required
                            />
                            <p
                                v-if="form.errors.price"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.price }}
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label>Precio anterior (oferta)</Label
                            ><Input
                                v-model="form.compare_at_price"
                                type="number"
                                step="0.01"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Costo</Label
                            ><Input
                                v-model="form.cost"
                                type="number"
                                step="0.01"
                            />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Modo de disponibilidad</Label>
                        <select
                            v-model="form.inventory_mode"
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                        >
                            <option value="stock">Inventario disponible</option>
                            <option value="made_to_order">Bajo pedido</option>
                            <option value="inquiry_only">Solo consulta</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Inventario</Label
                            ><Input v-model.number="form.stock" type="number" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Umbral de stock bajo</Label
                            ><Input
                                v-model.number="form.low_stock_threshold"
                                type="number"
                            />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm"
                        ><Switch v-model="form.show_price" /> Mostrar precio
                        públicamente</label
                    >
                </TabsContent>

                <TabsContent
                    v-if="isEdit"
                    value="imagenes"
                    class="space-y-4 pt-6"
                >
                    <div class="grid grid-cols-4 gap-4">
                        <div
                            v-for="img in product?.images ?? []"
                            :key="img.id"
                            class="relative"
                        >
                            <img
                                :src="img.url"
                                class="aspect-square w-full rounded object-cover"
                                alt=""
                            />
                            <span
                                v-if="img.isPrimary"
                                class="absolute top-1 left-1 rounded bg-primary px-1.5 py-0.5 text-[0.65rem] text-primary-foreground"
                                >Principal</span
                            >
                            <div class="mt-1 flex justify-between">
                                <button
                                    type="button"
                                    class="text-muted-foreground hover:text-foreground"
                                    @click="setPrimary(img.id)"
                                >
                                    <Star class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="text-destructive"
                                    @click="deleteImage(img.id)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Agregar imágenes</Label>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="onFilesSelected"
                        />
                    </div>
                </TabsContent>

                <TabsContent
                    v-if="isEdit"
                    value="variantes"
                    class="space-y-4 pt-6"
                >
                    <div class="space-y-2">
                        <div
                            v-for="variant in product?.variants ?? []"
                            :key="variant.id"
                            class="flex items-center justify-between rounded border p-3 text-sm"
                        >
                            <span
                                >{{ variant.name }} — SKU {{ variant.sku }} —
                                Stock: {{ variant.stock }}</span
                            >
                            <button
                                type="button"
                                class="text-destructive"
                                @click="deleteVariant(variant.id)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-3 rounded border p-4">
                        <Input
                            v-model="variantForm.options[0].value"
                            placeholder="Talla / valor"
                        />
                        <Input
                            v-model="variantForm.name"
                            placeholder="Nombre (ej. Talla 7)"
                        />
                        <Input v-model="variantForm.sku" placeholder="SKU" />
                        <Input
                            v-model.number="variantForm.stock"
                            type="number"
                            placeholder="Stock"
                        />
                        <Button
                            type="button"
                            class="col-span-4"
                            @click="addVariant"
                            >Añadir variante</Button
                        >
                    </div>
                </TabsContent>

                <TabsContent value="seo" class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label>Meta título</Label
                        ><Input v-model="form.meta_title" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Meta descripción</Label
                        ><Textarea v-model="form.meta_description" rows="2" />
                    </div>
                </TabsContent>
            </Tabs>

            <Button type="submit" class="mt-6" :disabled="form.processing"
                >Guardar producto</Button
            >
        </form>
    </div>
</template>
