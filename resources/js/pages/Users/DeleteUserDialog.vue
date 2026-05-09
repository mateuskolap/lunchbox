<script setup lang="ts">
import UserController from '@/actions/App/Http/Controllers/UserController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import { Button } from '@/components/ui/button';
import type { User } from '@/types/auth';
import { Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    user: User;
}>();
</script>

<template>
    <ConfirmationDialog
        title="Excluir Usuário"
        :description="`Tem certeza que deseja excluir o usuário ${user.name}? Esta ação não pode ser desfeita.`"
        confirm-text="Excluir"
        confirming-text="Excluindo..."
        :form-action="UserController.destroy.form(user.id)"
        :form-options="{ preserveScroll: true }"
    >
        <template #trigger>
            <Button
                variant="ghost"
                size="icon"
                class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50"
                title="Excluir"
                data-test="confirm-delete-user-button"
            >
                <Trash2 class="size-4" />
                <span class="sr-only">Excluir</span>
            </Button>
        </template>
    </ConfirmationDialog>
</template>
