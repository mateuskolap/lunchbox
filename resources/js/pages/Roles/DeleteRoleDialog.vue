<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import RoleController from '@/actions/App/Http/Controllers/RoleController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import { Button } from '@/components/ui/button';
import type { Role } from '@/types/role';

defineProps<{
    role: Role;
}>();
</script>

<template>
    <ConfirmationDialog
        title="Excluir Papel"
        :description="`Tem certeza que deseja excluir o papel ${role.name}? Esta ação não pode ser desfeita.`"
        confirm-text="Excluir"
        confirming-text="Excluindo..."
        :form-action="RoleController.destroy.form(role.id)"
        :form-options="{ preserveScroll: true }"
    >
        <template #trigger>
            <Button
                variant="ghost"
                size="icon"
                class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50"
                title="Excluir"
                data-test="confirm-delete-role-button"
            >
                <Trash2 class="size-4" />
                <span class="sr-only">Excluir</span>
            </Button>
        </template>
    </ConfirmationDialog>
</template>
