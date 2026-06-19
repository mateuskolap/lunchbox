<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
import { ref, watch, onMounted, computed } from 'vue';
import UserController from '@/actions/App/Http/Controllers/UserController';
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
import type { User } from '@/types/auth';
import type { Role } from '@/types/role';

const props = defineProps<{
    user: User;
    roles: Role[];
}>();

const isOpen = ref(false);
const selectedRoleIds = ref<number[]>([]);
const searchQuery = ref('');

const filteredRoles = computed(() => {
    if (!searchQuery.value) {
        return props.roles;
    }

    const query = searchQuery.value.toLowerCase();

    return props.roles.filter((role) =>
        role.name.toLowerCase().includes(query),
    );
});

const initializeRoles = () => {
    if (props.user && props.user.roles) {
        selectedRoleIds.value = props.user.roles
            .map((r) => {
                if (typeof r === 'object' && r !== null) {
                    return r.id;
                }

                if (typeof r === 'string') {
                    const found = props.roles.find((role) => role.name === r);

                    return found ? found.id : null;
                }

                return null;
            })
            .filter((id): id is number => id !== null);
    } else {
        selectedRoleIds.value = [];
    }
};

onMounted(() => {
    initializeRoles();
});

watch(
    () => props.user,
    () => {
        initializeRoles();
    },
    { deep: true },
);

watch(isOpen, (newVal) => {
    if (newVal) {
        initializeRoles();
    } else {
        searchQuery.value = '';
    }
});

const handleCheckboxChange = (
    id: number,
    checked: boolean | 'indeterminate',
) => {
    if (checked === true) {
        if (!selectedRoleIds.value.includes(id)) {
            selectedRoleIds.value.push(id);
        }
    } else {
        selectedRoleIds.value = selectedRoleIds.value.filter(
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
                title="Vincular Papéis"
                data-test="user-roles-button"
            >
                <Shield class="size-4" />
                <span class="sr-only">Vincular Papéis</span>
            </Button>
        </DialogTrigger>
        <DialogContent class="max-w-2xl">
            <Form
                v-bind="UserController.addRoles.form(user.id)"
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <!-- hidden inputs to send role_ids array -->
                <template v-for="(id, index) in selectedRoleIds" :key="id">
                    <input
                        type="hidden"
                        :name="`role_ids[${index}]`"
                        :value="id"
                    />
                </template>

                <DialogHeader class="space-y-3">
                    <DialogTitle>Vincular Papéis</DialogTitle>
                    <DialogDescription>
                        Selecione os papéis de acesso que deseja atribuir ao
                        usuário <strong>{{ user.name }}</strong
                        >.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <label class="text-sm font-semibold text-foreground"
                                >Papéis Disponíveis</label
                            >
                            <Input
                                v-model="searchQuery"
                                placeholder="Pesquisar papel..."
                                class="h-8 max-w-[240px] bg-sidebar text-xs"
                            />
                        </div>
                        <div
                            class="grid max-h-[300px] gap-3 overflow-y-auto rounded-lg border border-sidebar-border bg-card p-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="role in filteredRoles"
                                :key="role.id"
                                class="flex items-center space-x-2 rounded-md border border-sidebar-border/30 bg-muted/10 p-2 hover:bg-muted/30"
                            >
                                <Checkbox
                                    :id="`user-role-${role.id}`"
                                    :model-value="
                                        selectedRoleIds.includes(role.id)
                                    "
                                    @update:model-value="
                                        (checked: boolean | 'indeterminate') =>
                                            handleCheckboxChange(
                                                role.id,
                                                checked,
                                            )
                                    "
                                />
                                <label
                                    :for="`user-role-${role.id}`"
                                    class="cursor-pointer text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {{ role.name }}
                                </label>
                            </div>
                        </div>
                        <div
                            v-if="errors.role_ids"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ errors.role_ids }}
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
                                    initializeRoles();
                                }
                            "
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="save-user-roles-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
