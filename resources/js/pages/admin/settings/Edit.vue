<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{ settings: Record<string, any> }>();

const form = useForm({
    store_name: props.settings.store_name ?? '',
    store_tagline: props.settings.store_tagline ?? '',
    whatsapp_number: props.settings.whatsapp_number ?? '',
    contact_phone: props.settings.contact_phone ?? '',
    contact_email: props.settings.contact_email ?? '',
    contact_address: props.settings.contact_address ?? '',
    currency: props.settings.currency ?? 'MXN',
    tax_rate: props.settings.tax_rate ?? 0,
    prices_include_tax: Boolean(props.settings.prices_include_tax),
    instagram_url: props.settings.instagram_url ?? '',
    facebook_url: props.settings.facebook_url ?? '',
    tiktok_url: props.settings.tiktok_url ?? '',
    pinterest_url: props.settings.pinterest_url ?? '',
    meta_title: props.settings.meta_title ?? '',
    meta_description: props.settings.meta_description ?? '',
    logo: null as File | null,
    logo_dark: null as File | null,
    favicon: null as File | null,
});

function submit(): void {
    form.post('/admin/configuracion', { preserveScroll: true });
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-2xl font-semibold">Configuración de la tienda</h1>

        <form @submit.prevent="submit">
            <Tabs default-value="general">
                <TabsList>
                    <TabsTrigger value="general">General</TabsTrigger>
                    <TabsTrigger value="contacto">Contacto</TabsTrigger>
                    <TabsTrigger value="comercio">Comercio</TabsTrigger>
                    <TabsTrigger value="redes">Redes sociales</TabsTrigger>
                    <TabsTrigger value="seo">SEO</TabsTrigger>
                </TabsList>

                <TabsContent value="general" class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label>Nombre de la tienda</Label
                        ><Input v-model="form.store_name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label>Slogan</Label
                        ><Input v-model="form.store_tagline" />
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label>Logo</Label
                            ><input
                                type="file"
                                accept="image/*"
                                @change="
                                    form.logo =
                                        ($event.target as HTMLInputElement)
                                            .files?.[0] ?? null
                                "
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Logo oscuro</Label
                            ><input
                                type="file"
                                accept="image/*"
                                @change="
                                    form.logo_dark =
                                        ($event.target as HTMLInputElement)
                                            .files?.[0] ?? null
                                "
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Favicon</Label
                            ><input
                                type="file"
                                accept="image/*"
                                @change="
                                    form.favicon =
                                        ($event.target as HTMLInputElement)
                                            .files?.[0] ?? null
                                "
                            />
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="contacto" class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label
                            >WhatsApp (formato internacional, ej.
                            5215500000000)</Label
                        >
                        <Input v-model="form.whatsapp_number" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Teléfono</Label
                        ><Input v-model="form.contact_phone" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Correo de contacto</Label
                        ><Input v-model="form.contact_email" type="email" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Dirección</Label
                        ><Textarea v-model="form.contact_address" rows="2" />
                    </div>
                </TabsContent>

                <TabsContent value="comercio" class="space-y-4 pt-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Moneda</Label
                            ><Input v-model="form.currency" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Tasa de impuesto (%)</Label
                            ><Input
                                v-model.number="form.tax_rate"
                                type="number"
                                step="0.01"
                            />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm"
                        ><Switch v-model="form.prices_include_tax" /> Los
                        precios ya incluyen impuestos</label
                    >
                </TabsContent>

                <TabsContent value="redes" class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label>Instagram</Label
                        ><Input v-model="form.instagram_url" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Facebook</Label
                        ><Input v-model="form.facebook_url" />
                    </div>
                    <div class="grid gap-2">
                        <Label>TikTok</Label><Input v-model="form.tiktok_url" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Pinterest</Label
                        ><Input v-model="form.pinterest_url" />
                    </div>
                </TabsContent>

                <TabsContent value="seo" class="space-y-4 pt-6">
                    <div class="grid gap-2">
                        <Label>Meta título</Label
                        ><Input v-model="form.meta_title" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Meta descripción</Label
                        ><Textarea v-model="form.meta_description" rows="3" />
                    </div>
                </TabsContent>
            </Tabs>

            <Button type="submit" class="mt-6" :disabled="form.processing"
                >Guardar configuración</Button
            >
        </form>
    </div>
</template>
