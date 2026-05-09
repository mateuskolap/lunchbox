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

const isOpen = ref(false);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button data-test="create-product-button">Novo Produto</Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="ProductController.store.form()"
                reset-on-success
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Novo Produto</DialogTitle>
                    <DialogDescription>
                        Preencha os dados do novo produto.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField label="Nome" field-id="create-product-name" :error="errors.name">
                        <Input
                            id="create-product-name"
                            name="name"
                            placeholder="Nome do produto"
                            required
                        />
                    </FormField>

                    <FormField label="Preço" field-id="create-product-price" :error="errors.price">
                        <Input
                            id="create-product-price"
                            name="price"
                            type="number"
                            step="0.01"
                            min="0"
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

                    <Button type="submit" :disabled="processing" data-test="save-create-product-button">
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
