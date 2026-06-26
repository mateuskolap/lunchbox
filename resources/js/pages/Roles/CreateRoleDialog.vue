<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
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
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
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

watch(isOpen, (newVal) => {
    if (!newVal) {
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
            <Button data-test="create-role-button">
                <Plus class="mr-2 size-4" />
                Novo Papel
            </Button>
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
                                    :id="`create-perm-${perm.id}`"
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
                                    :for="`create-perm-${perm.id}`"
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
