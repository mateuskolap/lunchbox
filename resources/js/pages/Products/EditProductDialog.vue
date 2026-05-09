<script setup lang="ts">
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import FormField from '@/components/FormField.vue';
import { Spinner } from '@/components/ui/spinner';
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
import { Input } from '@/components/ui/input';
import type { Product } from '@/types/product';
import { Pencil } from 'lucide-vue-next';

const props = defineProps<{
    product: Product;
}>();

const isOpen = ref(false);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="ghost" size="icon" title="Editar" data-test="edit-product-button">
                <Pencil class="size-4" />
                <span class="sr-only">Editar</span>
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="ProductController.update.form(product.id)"
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Editar Produto</DialogTitle>
                    <DialogDescription>
                        Altere os dados do produto.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField label="Nome" field-id="edit-product-name" :error="errors.name">
                        <Input
                            id="edit-product-name"
                            name="name"
                            :default-value="product.name"
                            placeholder="Nome do produto"
                            required
                        />
                    </FormField>

                    <FormField label="Preço" field-id="edit-product-price" :error="errors.price">
                        <Input
                            id="edit-product-price"
                            name="price"
                            type="number"
                            step="0.01"
                            min="0"
                            :default-value="product.price"
                            placeholder="0.00"
                            required
                        />
                    </FormField>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            @click="() => { clearErrors(); reset(); }"
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button type="submit" :disabled="processing" data-test="save-edit-product-button">
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
