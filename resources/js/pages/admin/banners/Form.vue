<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{ banner?: Record<string, any> }>();
const isEdit = Boolean(props.banner);

const form = useForm({
    position: props.banner?.position ?? 'hero',
    title: props.banner?.title ?? '',
    subtitle: props.banner?.subtitle ?? '',
    description: props.banner?.description ?? '',
    cta_text: props.banner?.cta_text ?? '',
    cta_url: props.banner?.cta_url ?? '',
    content_position: props.banner?.content_position ?? 'center',
    text_theme: props.banner?.text_theme ?? 'light',
    overlay: props.banner?.overlay ?? '',
    sort_order: props.banner?.sort_order ?? 0,
    starts_at: props.banner?.starts_at?.slice(0, 16) ?? '',
    ends_at: props.banner?.ends_at?.slice(0, 16) ?? '',
    active: props.banner?.active ?? true,
    desktop_media: null as File | null,
    mobile_media: null as File | null,
});

function submit(): void {
    if (isEdit) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(
            `/admin/banners/${props.banner!.id}`,
        );
    } else {
        form.post('/admin/banners');
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? 'Editar banner' : 'Nuevo banner' }}
        </h1>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label>Posición</Label>
                <select
                    v-model="form.position"
                    class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                >
                    <option value="hero">Hero (Home)</option>
                    <option value="story">Storytelling</option>
                    <option value="special_collection">
                        Colección especial
                    </option>
                    <option value="strip">Franja superior</option>
                </select>
            </div>
            <div class="grid gap-2">
                <Label>Título</Label><Input v-model="form.title" />
            </div>
            <div class="grid gap-2">
                <Label>Subtítulo</Label><Input v-model="form.subtitle" />
            </div>
            <div class="grid gap-2">
                <Label>Descripción</Label
                ><Textarea v-model="form.description" rows="2" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label>Texto del botón</Label
                    ><Input v-model="form.cta_text" />
                </div>
                <div class="grid gap-2">
                    <Label>URL del botón</Label><Input v-model="form.cta_url" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label>Posición del contenido</Label>
                    <select
                        v-model="form.content_position"
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                    >
                        <option value="left">Izquierda</option>
                        <option value="center">Centro</option>
                        <option value="right">Derecha</option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label>Tema del texto</Label>
                    <select
                        v-model="form.text_theme"
                        class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                    >
                        <option value="light">Claro</option>
                        <option value="dark">Oscuro</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label>Imagen escritorio</Label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="
                            form.desktop_media =
                                ($event.target as HTMLInputElement)
                                    .files?.[0] ?? null
                        "
                    />
                </div>
                <div class="grid gap-2">
                    <Label>Imagen móvil</Label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="
                            form.mobile_media =
                                ($event.target as HTMLInputElement)
                                    .files?.[0] ?? null
                        "
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
            </div>
            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.active" /> Activo</label
            >
            <Button type="submit" :disabled="form.processing">Guardar</Button>
        </form>
    </div>
</template>
