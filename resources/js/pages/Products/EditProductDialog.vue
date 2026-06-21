<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
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

import type { Product } from '@/types';

const props = defineProps<{
    product: Product;
}>();

const isOpen = ref(false);
const isLunchbox = ref(props.product.is_lunchbox ?? false);

watch(isOpen, (newVal) => {
    if (newVal) {
        isLunchbox.value = props.product.is_lunchbox ?? false;
    }
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                title="Editar"
                data-test="edit-product-button"
            >
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
                <input
                    type="hidden"
                    name="is_lunchbox"
                    :value="isLunchbox ? '1' : '0'"
                />
                <DialogHeader class="space-y-3">
                    <DialogTitle>Editar Produto</DialogTitle>
                    <DialogDescription>
                        Altere os dados do produto.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Nome"
                        field-id="edit-product-name"
                        :error="errors.name"
                    >
                        <Input
                            id="edit-product-name"
                            name="name"
                            :default-value="product.name"
                            placeholder="Nome do produto"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Preço"
                        field-id="edit-product-price"
                        :error="errors.price"
                    >
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

                    <div class="flex items-center space-x-2 py-1">
                        <Checkbox
                            id="edit-product-is-lunchbox"
                            :model-value="isLunchbox"
                            @update:model-value="isLunchbox = !!$event"
                        />
                        <label
                            for="edit-product-is-lunchbox"
                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Marmita
                        </label>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            @click="
                                () => {
                                    clearErrors();
                                    reset();
                                }
                            "
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="save-edit-product-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
