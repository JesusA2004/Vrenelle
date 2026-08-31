<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useSweetAlert } from '@/composables/useSweetAlert';

type Role = {
    id: number;
    name: string;
    usersCount: number;
    permissions: string[];
};

defineProps<{ roles: Role[]; permissions: string[] }>();
const { confirmDestructive } = useSweetAlert();

const createOpen = ref(false);
const createForm = useForm({ name: '', permissions: [] as string[] });

const editOpen = ref(false);
const editing = ref<Role | null>(null);
const editForm = useForm({ permissions: [] as string[] });

function openEdit(role: Role): void {
    editing.value = role;
    editForm.permissions = [...role.permissions];
    editOpen.value = true;
}

function toggle(list: string[], permission: string): string[] {
    return list.includes(permission)
        ? list.filter((p) => p !== permission)
        : [...list, permission];
}

function submitCreate(): void {
    createForm.post('/admin/roles', {
        onSuccess: () => {
            createForm.reset();
            createOpen.value = false;
        },
    });
}

function submitEdit(): void {
    editForm.patch(`/admin/roles/${editing.value!.id}`, {
        onSuccess: () => (editOpen.value = false),
    });
}

async function destroy(role: Role): Promise<void> {
    const confirmed = await confirmDestructive({
        title: 'Eliminar rol',
        text: `¿Eliminar el rol "${role.name}"?`,
    });

    if (confirmed) {
        router.delete(`/admin/roles/${role.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Roles y permisos</h1>
            <Button @click="createOpen = true"
                ><Plus class="mr-1.5 size-4" /> Nuevo rol</Button
            >
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
                v-for="role in roles"
                :key="role.id"
                class="rounded-lg border p-5"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="font-medium">{{ role.name }}</h2>
                    <span class="text-xs text-muted-foreground"
                        >{{ role.usersCount }} usuarios</span
                    >
                </div>
                <p class="mb-3 text-xs text-muted-foreground">
                    {{ role.permissions.length }} permisos asignados
                </p>
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="text-sm underline"
                        @click="openEdit(role)"
                    >
                        Editar permisos
                    </button>
                    <button
                        v-if="
                            !['super_admin', 'administrador'].includes(
                                role.name,
                            )
                        "
                        type="button"
                        class="text-sm text-destructive underline"
                        @click="destroy(role)"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>Nuevo rol</DialogTitle></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label>Nombre del rol</Label
                        ><Input v-model="createForm.name" required />
                    </div>
                    <div class="grid max-h-64 gap-2 overflow-y-auto">
                        <label
                            v-for="permission in permissions"
                            :key="permission"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                :checked="
                                    createForm.permissions.includes(permission)
                                "
                                @change="
                                    createForm.permissions = toggle(
                                        createForm.permissions,
                                        permission,
                                    )
                                "
                            />
                            {{ permission }}
                        </label>
                    </div>
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="createForm.processing"
                        >Crear rol</Button
                    >
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="editOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle
                        >Permisos de {{ editing?.name }}</DialogTitle
                    ></DialogHeader
                >
                <form class="space-y-4" @submit.prevent="submitEdit">
                    <div class="grid max-h-64 gap-2 overflow-y-auto">
                        <label
                            v-for="permission in permissions"
                            :key="permission"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                :checked="
                                    editForm.permissions.includes(permission)
                                "
                                @change="
                                    editForm.permissions = toggle(
                                        editForm.permissions,
                                        permission,
                                    )
                                "
                            />
                            {{ permission }}
                        </label>
                    </div>
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="editForm.processing"
                        >Guardar permisos</Button
                    >
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
