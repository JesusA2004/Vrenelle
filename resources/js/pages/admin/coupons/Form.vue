<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    coupon?: Record<string, any>;
    categories: Array<{ id: number; name: string }>;
    collections: Array<{ id: number; name: string }>;
    products: Array<{ id: number; name: string }>;
}>();
const isEdit = Boolean(props.coupon);

const form = useForm({
    code: props.coupon?.code ?? '',
    description: props.coupon?.description ?? '',
    type: props.coupon?.type ?? 'percentage',
    value: props.coupon?.value ?? 0,
    minimum_purchase: props.coupon?.minimum_purchase ?? '',
    maximum_discount: props.coupon?.maximum_discount ?? '',
    starts_at: props.coupon?.starts_at?.slice(0, 16) ?? '',
    expires_at: props.coupon?.expires_at?.slice(0, 16) ?? '',
    usage_limit: props.coupon?.usage_limit ?? '',
    usage_per_customer: props.coupon?.usage_per_customer ?? '',
    active: props.coupon?.active ?? true,
    first_purchase_only: props.coupon?.first_purchase_only ?? false,
    stackable: props.coupon?.stackable ?? false,
    category_ids: props.coupon?.category_ids ?? [],
    collection_ids: props.coupon?.collection_ids ?? [],
    product_ids: props.coupon?.product_ids ?? [],
});

function submit(): void {
    if (isEdit) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(
            `/admin/cupones/${props.coupon!.id}`,
        );
    } else {
        form.post('/admin/cupones');
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? 'Editar cupón' : 'Nuevo cupón' }}
        </h1>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label>Código</Label>
                <Input v-model="form.code" required class="uppercase" />
                <p v-if="form.errors.code" class="text-sm text-destructive">
                    {{ form.errors.code }}
                </p>
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
                        <option value="free_shipping">Envío gratis</option>
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
                    <Label>Inicio</Label
                    ><Input v-model="form.starts_at" type="datetime-local" />
                </div>
                <div class="grid gap-2">
                    <Label>Expira</Label
                    ><Input v-model="form.expires_at" type="datetime-local" />
                </div>
                <div class="grid gap-2">
                    <Label>Límite de usos totales</Label
                    ><Input v-model="form.usage_limit" type="number" />
                </div>
                <div class="grid gap-2">
                    <Label>Usos por cliente</Label
                    ><Input v-model="form.usage_per_customer" type="number" />
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

            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.active" /> Activo</label
            >
            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.first_purchase_only" /> Solo primera
                compra</label
            >
            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.stackable" /> Acumulable con
                promociones</label
            >

            <Button type="submit" :disabled="form.processing">Guardar</Button>
        </form>
    </div>
</template>
