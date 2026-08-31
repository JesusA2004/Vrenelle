<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    page: { id: number; slug: string; title: string; content: string; meta_title: string | null; meta_description: string | null };
}>();

const form = useForm({
    title: props.page.title,
    content: props.page.content,
    meta_title: props.page.meta_title ?? '',
    meta_description: props.page.meta_description ?? '',
});

function submit(): void {
    form.patch(`/admin/paginas-legales/${props.page.id}`);
}
</script>

<template>
    <div class="max-w-3xl space-y-6">
        <h1 class="text-2xl font-semibold">Editar: {{ page.title }}</h1>
        <p class="text-sm text-muted-foreground">Visible públicamente en /legal/{{ page.slug }}</p>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label>Título</Label>
                <Input v-model="form.title" required />
            </div>
            <div class="grid gap-2">
                <Label>Contenido (admite HTML básico)</Label>
                <Textarea v-model="form.content" rows="16" required class="font-mono text-sm" />
            </div>
            <div class="grid gap-2">
                <Label>Meta título</Label>
                <Input v-model="form.meta_title" />
            </div>
            <div class="grid gap-2">
                <Label>Meta descripción</Label>
                <Textarea v-model="form.meta_description" rows="2" />
            </div>
            <Button type="submit" :disabled="form.processing">Guardar</Button>
        </form>
    </div>
</template>
