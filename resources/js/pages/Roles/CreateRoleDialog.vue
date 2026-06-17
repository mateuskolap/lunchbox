<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import RoleController from '@/actions/App/Http/Controllers/RoleController';
import FormField from '@/components/FormField.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import type { Permission } from '@/types/role';

const props = defineProps<{
    permissions: Permission[];
}>();

const isOpen = ref(false);
const selectedPermissionIds = ref<number[]>([]);

const togglePermission = (id: number) => {
    const index = selectedPermissionIds.value.indexOf(id);
    if (index > -1) {
        selectedPermissionIds.value.splice(index, 1);
    } else {
        selectedPermissionIds.value.push(id);
    }
};

const groupedPermissions = computed(() => {
    const groups: Record<string, Permission[]> = {};
    props.permissions.forEach((perm) => {
        const parts = perm.name.split('.');
        const groupName = parts[0] || 'Outros';
        if (!groups[groupName]) {
            groups[groupName] = [];
        }
        groups[groupName].push(perm);
    });
    return groups;
});

const getGroupLabel = (group: string) => {
    const labels: Record<string, string> = {
        users: 'Usuários',
        roles: 'Papéis',
        products: 'Produtos',
        customers: 'Clientes',
        orders: 'Pedidos',
    };
    return labels[group] || group.charAt(0).toUpperCase() + group.slice(1);
};

const getPermissionLabel = (name: string) => {
    const parts = name.split('.');
    const action = parts[1] || name;
    const labels: Record<string, string> = {
        index: 'Visualizar',
        store: 'Criar',
        update: 'Editar',
        destroy: 'Excluir',
    };
    return labels[action] || action.charAt(0).toUpperCase() + action.slice(1);
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button data-test="create-role-button">Novo Papel</Button>
        </DialogTrigger>
        <DialogContent class="max-w-2xl">
            <Form
                v-bind="RoleController.store.form()"
                reset-on-success
                @success="
                    () => {
                        isOpen = false;
                        selectedPermissionIds = [];
                    }
                "
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <!-- hidden inputs to send permission_ids array -->
                <template
                    v-for="(id, index) in selectedPermissionIds"
                    :key="id"
                >
                    <input
                        type="hidden"
                        :name="`permission_ids[${index}]`"
                        :value="id"
                    />
                </template>

                <DialogHeader class="space-y-3">
                    <DialogTitle>Novo Papel</DialogTitle>
                    <DialogDescription>
                        Preencha os dados do novo papel do sistema e selecione
                        suas permissões.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-6">
                    <FormField
                        label="Nome"
                        field-id="create-role-name"
                        :error="errors.name"
                    >
                        <Input
                            id="create-role-name"
                            name="name"
                            placeholder="Nome do papel (ex: admin, gerente)"
                            required
                        />
                    </FormField>

                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-foreground"
                            >Permissões de Acesso</label
                        >
                        <div
                            class="grid max-h-[300px] gap-4 overflow-y-auto rounded-lg border border-sidebar-border bg-card p-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="(perms, group) in groupedPermissions"
                                :key="group"
                                class="space-y-2 rounded-lg border border-sidebar-border/50 bg-muted/20 p-3"
                            >
                                <h4
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    {{ getGroupLabel(group) }}
                                </h4>
                                <div class="grid gap-2">
                                    <div
                                        v-for="perm in perms"
                                        :key="perm.id"
                                        class="flex items-center space-x-2"
                                    >
                                        <Checkbox
                                            :id="`create-perm-${perm.id}`"
                                            :checked="
                                                selectedPermissionIds.includes(
                                                    perm.id,
                                                )
                                            "
                                            @update:checked="
                                                () => togglePermission(perm.id)
                                            "
                                        />
                                        <label
                                            :for="`create-perm-${perm.id}`"
                                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            {{ getPermissionLabel(perm.name) }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="errors.permission_ids"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ errors.permission_ids }}
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2 border-t border-sidebar-border pt-4">
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            @click="
                                () => {
                                    clearErrors();
                                    reset();
                                    selectedPermissionIds = [];
                                }
                            "
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="save-create-role-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
