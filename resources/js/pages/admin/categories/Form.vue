<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    category?: Record<string, any>;
    categories: Array<{ id: number; name: string }>;
}>();

const isEdit = Boolean(props.category);

const form = useForm({
    parent_id: props.category?.parent_id ?? null,
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    description: props.category?.description ?? '',
    icon: props.category?.icon ?? '',
    sort_order: props.category?.sort_order ?? 0,
    is_active: props.category?.is_active ?? true,
    is_featured: props.category?.is_featured ?? false,
    meta_title: props.category?.meta_title ?? '',
    meta_description: props.category?.meta_description ?? '',
    image: null as File | null,
});

function submit(): void {
    if (isEdit) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            `/admin/categorias/${props.category!.id}`,
        );
    } else {
        form.post('/admin/categorias');
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? 'Editar categoría' : 'Nueva categoría' }}
        </h1>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">Nombre</Label>
                <Input id="name" v-model="form.name" required />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                    {{ form.errors.name }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="slug">Slug (opcional)</Label>
                <Input
                    id="slug"
                    v-model="form.slug"
                    placeholder="Se genera automáticamente si se deja vacío"
                />
            </div>

            <div class="grid gap-2">
                <Label for="description">Descripción</Label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                />
            </div>

            <div class="grid gap-2">
                <Label for="parent_id">Categoría padre (opcional)</Label>
                <select
                    id="parent_id"
                    v-model="form.parent_id"
                    class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                >
                    <option :value="null">— Ninguna —</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>

            <div class="grid gap-2">
                <Label for="image">Imagen</Label>
                <input
                    id="image"
                    type="file"
                    accept="image/*"
                    @change="
                        form.image =
                            ($event.target as HTMLInputElement).files?.[0] ??
                            null
                    "
                />
                <img
                    v-if="category?.image"
                    :src="category.image"
                    class="mt-2 h-24 w-24 rounded object-cover"
                    alt=""
                />
            </div>

            <div class="grid gap-2">
                <Label for="sort_order">Orden</Label>
                <Input
                    id="sort_order"
                    v-model.number="form.sort_order"
                    type="number"
                />
            </div>

            <div class="flex items-center gap-3">
                <Switch id="is_active" v-model="form.is_active" />
                <Label for="is_active">Activa</Label>
            </div>
            <div class="flex items-center gap-3">
                <Switch id="is_featured" v-model="form.is_featured" />
                <Label for="is_featured">Destacada en el mega menú</Label>
            </div>

            <details class="rounded-md border p-4">
                <summary class="cursor-pointer text-sm font-medium">
                    SEO
                </summary>
                <div class="mt-4 grid gap-4">
                    <div class="grid gap-2">
                        <Label for="meta_title">Meta título</Label>
                        <Input id="meta_title" v-model="form.meta_title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="meta_description">Meta descripción</Label>
                        <Textarea
                            id="meta_description"
                            v-model="form.meta_description"
                            rows="2"
                        />
                    </div>
                </div>
            </details>

            <Button type="submit" :disabled="form.processing">Guardar</Button>
        </form>
    </div>
</template>
