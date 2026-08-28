<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import { ref, watch, onMounted, computed } from 'vue';
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
import type { Role, Permission } from '@/types/role';

const props = defineProps<{
    role: Role;
    permissions: Permission[];
}>();

const isOpen = ref(false);
const selectedPermissionIds = ref<number[]>([]);
const searchQuery = ref('');

const filteredPermissions = computed(() => {
    if (!searchQuery.value) {
        return props.permissions;
    }

    const query = searchQuery.value.toLowerCase();

    return props.permissions.filter((perm) =>
        perm.name.toLowerCase().includes(query),
    );
});

const initializePermissions = () => {
    if (props.role && props.role.permissions) {
        selectedPermissionIds.value = props.role.permissions.map((p) => p.id);
    } else {
        selectedPermissionIds.value = [];
    }
};

onMounted(() => {
    initializePermissions();
});

watch(
    () => props.role,
    () => {
        initializePermissions();
    },
    { deep: true },
);

watch(isOpen, (newVal) => {
    if (newVal) {
        initializePermissions();
    } else {
        searchQuery.value = '';
    }
});

const handleCheckboxChange = (
    id: number,
    checked: boolean | 'indeterminate',
) => {
    if (checked === true) {
        if (!selectedPermissionIds.value.includes(id)) {
            selectedPermissionIds.value.push(id);
        }
    } else {
        selectedPermissionIds.value = selectedPermissionIds.value.filter(
            (item) => item !== id,
        );
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                title="Editar"
                data-test="edit-role-button"
            >
                <Pencil class="size-4" />
                <span class="sr-only">Editar</span>
            </Button>
        </DialogTrigger>
        <DialogContent class="max-w-2xl">
            <Form
                v-bind="RoleController.update.form(String(role.id))"
                @success="isOpen = false"
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
                    <DialogTitle>Editar Papel</DialogTitle>
                    <DialogDescription>
                        Altere os dados do papel do sistema e suas permissões.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-6">
                    <FormField
                        label="Nome"
                        field-id="edit-role-name"
                        :error="errors.name"
                    >
                        <Input
                            id="edit-role-name"
                            name="name"
                            :default-value="role.name"
                            placeholder="Nome do papel"
                            required
                        />
                    </FormField>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <label class="text-sm font-semibold text-foreground"
                                >Permissões de Acesso</label
                            >
                            <Input
                                v-model="searchQuery"
                                placeholder="Pesquisar permissão..."
                                class="h-8 max-w-[240px] bg-sidebar text-xs"
                            />
                        </div>
                        <div
                            class="grid max-h-[300px] gap-3 overflow-y-auto rounded-lg border border-sidebar-border bg-card p-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="perm in filteredPermissions"
                                :key="perm.id"
                                class="flex items-center space-x-2 rounded-md border border-sidebar-border/30 bg-muted/10 p-2 hover:bg-muted/30"
                            >
                                <Checkbox
                                    :id="`edit-perm-${perm.id}`"
                                    :model-value="
                                        selectedPermissionIds.includes(perm.id)
                                    "
                                    @update:model-value="
                                        (checked: boolean | 'indeterminate') =>
                                            handleCheckboxChange(
                                                perm.id,
                                                checked,
                                            )
                                    "
                                />
                                <label
                                    :for="`edit-perm-${perm.id}`"
                                    class="cursor-pointer text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {{ perm.name }}
                                </label>
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
                                    initializePermissions();
                                }
                            "
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="save-edit-role-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
