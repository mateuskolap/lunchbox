<script setup lang="ts">
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import { Button } from '@/components/ui/button';
import type { Product } from '@/types/product';
import { Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    product: Product;
}>();
</script>

<template>
    <ConfirmationDialog
        title="Excluir Produto"
        :description="`Tem certeza que deseja excluir o produto ${product.name}? Esta ação não pode ser desfeita.`"
        confirm-text="Excluir"
        confirming-text="Excluindo..."
        :form-action="ProductController.destroy.form(product.id)"
        :form-options="{ preserveScroll: true }"
    >
        <template #trigger>
            <Button
                variant="ghost"
                size="icon"
                class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50"
                title="Excluir"
                data-test="confirm-delete-product-button"
            >
                <Trash2 class="size-4" />
                <span class="sr-only">Excluir</span>
            </Button>
        </template>
    </ConfirmationDialog>
</template>
