<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    promotion?: Record<string, any>;
    categories: Array<{ id: number; name: string }>;
    collections: Array<{ id: number; name: string }>;
    products: Array<{ id: number; name: string }>;
}>();
const isEdit = Boolean(props.promotion);

const form = useForm({
    name: props.promotion?.name ?? '',
    internal_name: props.promotion?.internal_name ?? '',
    description: props.promotion?.description ?? '',
    type: props.promotion?.type ?? 'percentage',
    value: props.promotion?.value ?? 0,
    starts_at: props.promotion?.starts_at?.slice(0, 16) ?? '',
    ends_at: props.promotion?.ends_at?.slice(0, 16) ?? '',
    minimum_purchase: props.promotion?.minimum_purchase ?? '',
    maximum_discount: props.promotion?.maximum_discount ?? '',
    usage_limit: props.promotion?.usage_limit ?? '',
    usage_per_user: props.promotion?.usage_per_user ?? '',
    active: props.promotion?.active ?? true,
    priority: props.promotion?.priority ?? 0,
    stackable: props.promotion?.stackable ?? false,
    category_ids: props.promotion?.category_ids ?? [],
    collection_ids: props.promotion?.collection_ids ?? [],
    product_ids: props.promotion?.product_ids ?? [],
});

function submit(): void {
    if (isEdit) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(
            `/admin/promociones/${props.promotion!.id}`,
        );
    } else {
        form.post('/admin/promociones');
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? 'Editar promoción' : 'Nueva promoción' }}
        </h1>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label>Nombre</Label><Input v-model="form.name" required />
            </div>
            <div class="grid gap-2">
                <Label>Nombre interno</Label
                ><Input v-model="form.internal_name" />
            </div>
            <div class="grid gap-2">
                <Label>Descripción</Label
                ><Textarea v-model="form.description" rows="2" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label>Tipo</Label>
                    <select
                        v-model="form.type"
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                    >
                        <option value="percentage">Porcentaje</option>
                        <option value="fixed">Monto fijo</option>
                        <option value="special_price">Precio especial</option>
                        <option value="free_shipping">Envío gratis</option>
                        <option value="category_discount">
                            Descuento por categoría
                        </option>
                        <option value="collection_discount">
                            Descuento por colección
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label>Valor</Label
                    ><Input
                        v-model.number="form.value"
                        type="number"
                        step="0.01"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label>Inicio</Label
                    ><Input v-model="form.starts_at" type="datetime-local" />
                </div>
                <div class="grid gap-2">
                    <Label>Fin</Label
                    ><Input v-model="form.ends_at" type="datetime-local" />
                </div>
                <div class="grid gap-2">
                    <Label>Compra mínima</Label
                    ><Input
                        v-model="form.minimum_purchase"
                        type="number"
                        step="0.01"
                    />
                </div>
                <div class="grid gap-2">
                    <Label>Descuento máximo</Label
                    ><Input
                        v-model="form.maximum_discount"
                        type="number"
                        step="0.01"
                    />
                </div>
                <div class="grid gap-2">
                    <Label>Límite de usos</Label
                    ><Input v-model="form.usage_limit" type="number" />
                </div>
                <div class="grid gap-2">
                    <Label>Usos por cliente</Label
                    ><Input v-model="form.usage_per_user" type="number" />
                </div>
                <div class="grid gap-2">
                    <Label>Prioridad</Label
                    ><Input v-model.number="form.priority" type="number" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label>Categorías aplicables (vacío = toda la tienda)</Label>
                <select
                    v-model="form.category_ids"
                    multiple
                    class="h-28 rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                >
                    <option v-for="c in categories" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>
            <div class="grid gap-2">
                <Label>Colecciones aplicables</Label>
                <select
                    v-model="form.collection_ids"
                    multiple
                    class="h-28 rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                >
                    <option v-for="c in collections" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.active" /> Activa</label
            >
            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.stackable" /> Acumulable con otras
                promociones</label
            >

            <Button type="submit" :disabled="form.processing">Guardar</Button>
        </form>
    </div>
</template>
