<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import { Button } from '@/components/ui/button';
import type { Customer } from '@/types/customer';

defineProps<{
    customer: Customer;
}>();
</script>

<template>
    <ConfirmationDialog
        title="Excluir Cliente"
        :description="`Tem certeza que deseja excluir o cliente ${customer.name}? Esta ação não pode ser desfeita.`"
        confirm-text="Excluir"
        confirming-text="Excluindo..."
        :form-action="CustomerController.destroy.form(customer.id)"
        :form-options="{ preserveScroll: true }"
    >
        <template #trigger>
            <Button
                variant="ghost"
                size="icon"
                class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50"
                title="Excluir"
                data-test="confirm-delete-customer-button"
            >
                <Trash2 class="size-4" />
                <span class="sr-only">Excluir</span>
            </Button>
        </template>
    </ConfirmationDialog>
</template>
