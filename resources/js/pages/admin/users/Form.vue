<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

const props = defineProps<{
    user?: Record<string, any>;
    roles: string[];
}>();
const isEdit = Boolean(props.user);

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    phone: props.user?.phone ?? '',
    password: '',
    is_active: props.user?.is_active ?? true,
    roles: props.user?.roles ?? [],
});

function toggleRole(role: string): void {
    form.roles = form.roles.includes(role)
        ? form.roles.filter((r: string) => r !== role)
        : [...form.roles, role];
}

function submit(): void {
    if (isEdit) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(
            `/admin/usuarios/${props.user!.id}`,
        );
    } else {
        form.post('/admin/usuarios');
    }
}
</script>

<template>
    <div class="max-w-xl space-y-6">
        <h1 class="text-2xl font-semibold">
            {{ isEdit ? 'Editar usuario' : 'Nuevo usuario' }}
        </h1>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label>Nombre</Label><Input v-model="form.name" required />
            </div>
            <div class="grid gap-2">
                <Label>Correo electrónico</Label
                ><Input v-model="form.email" type="email" required />
            </div>
            <div class="grid gap-2">
                <Label>Teléfono</Label><Input v-model="form.phone" />
            </div>
            <div class="grid gap-2">
                <Label>{{
                    isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña'
                }}</Label>
                <Input
                    v-model="form.password"
                    type="password"
                    :required="!isEdit"
                />
            </div>

            <div class="grid gap-2">
                <Label>Roles</Label>
                <div class="flex flex-wrap gap-3">
                    <label
                        v-for="role in roles"
                        :key="role"
                        class="flex items-center gap-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            :checked="form.roles.includes(role)"
                            @change="toggleRole(role)"
                        />
                        {{ role }}
                    </label>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm"
                ><Switch v-model="form.is_active" /> Activo</label
            >

            <Button type="submit" :disabled="form.processing">Guardar</Button>
        </form>
    </div>
</template>
