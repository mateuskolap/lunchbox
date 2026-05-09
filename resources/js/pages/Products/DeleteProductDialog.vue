<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { Button } from '@/components/ui/button';
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
import type { Product } from '@/types/product';
import { Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    product: Product;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="ghost" size="icon" class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50" title="Excluir">
                <Trash2 class="size-4" />
                <span class="sr-only">Excluir</span>
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="ProductController.destroy.form(product.id)"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Excluir Produto</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir o produto <strong>{{ product.name }}</strong>? Esta ação não pode ser desfeita.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        {{ processing ? 'Excluindo...' : 'Excluir' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
