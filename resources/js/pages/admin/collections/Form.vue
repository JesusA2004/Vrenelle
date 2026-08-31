<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{ collection?: Record<string, any> }>();
const isEdit = Boolean(props.collection);

const form = useForm({
    name: props.collection?.name ?? '',
    slug: props.collection?.slug ?? '',
    short_description: props.collection?.short_description ?? '',
    description: props.collection?.description ?? '',
    story: props.collection?.story ?? '',
    is_active: props.collection?.is_active ?? true,
    is_featured: props.collection?.is_featured ?? false,
    sort_order: props.collection?.sort_order ?? 0,
    meta_title: props.collection?.meta_title ?? '',
    meta_description: props.collection?.meta_description ?? '',
    published_at: props.collection?.published_at?.slice(0, 10) ?? '',
    image: null as File | null,
    mobile_image: null as File | null,
    banner: null as File | null,
});

function submit(): void {
    if (isEdit) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            `/admin/colecciones/${props.collection!.id}`,
        );
    } else {
        form.post('/admin/colecciones');
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? 'Editar colección' : 'Nueva colección' }}
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
                <Label for="short_description">Descripción corta</Label>
                <Textarea
                    id="short_description"
                    v-model="form.short_description"
                    rows="2"
                />
            </div>
            <div class="grid gap-2">
                <Label for="description">Descripción larga</Label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                />
            </div>
            <div class="grid gap-2">
                <Label for="story">Historia de la colección</Label>
                <Textarea id="story" v-model="form.story" rows="4" />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="image">Imagen principal</Label>
                    <input
                        id="image"
                        type="file"
                        accept="image/*"
                        @change="
                            form.image =
                                ($event.target as HTMLInputElement)
                                    .files?.[0] ?? null
                        "
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="mobile_image">Imagen móvil</Label>
                    <input
                        id="mobile_image"
                        type="file"
                        accept="image/*"
                        @change="
                            form.mobile_image =
                                ($event.target as HTMLInputElement)
                                    .files?.[0] ?? null
                        "
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="banner">Banner (hero)</Label>
                    <input
                        id="banner"
                        type="file"
                        accept="image/*"
                        @change="
                            form.banner =
                                ($event.target as HTMLInputElement)
                                    .files?.[0] ?? null
                        "
                    />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="published_at">Fecha de publicación</Label>
                <Input
                    id="published_at"
                    v-model="form.published_at"
                    type="date"
                />
            </div>

            <div class="flex items-center gap-3">
                <Switch id="is_active" v-model="form.is_active" />
                <Label for="is_active">Activa</Label>
            </div>
            <div class="flex items-center gap-3">
                <Switch id="is_featured" v-model="form.is_featured" />
                <Label for="is_featured">Destacada en Home</Label>
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
