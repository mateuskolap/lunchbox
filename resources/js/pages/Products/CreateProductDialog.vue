<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
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

const isOpen = ref(false);
const isLunchbox = ref(false);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button data-test="create-product-button">
                <Plus class="mr-2 size-4" />
                Novo Produto
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="ProductController.store.form()"
                reset-on-success
                @success="
                    () => {
                        isOpen = false;
                        isLunchbox = false;
                    }
                "
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <input
                    type="hidden"
                    name="show_in_prep_summary"
                    :value="isLunchbox ? '1' : '0'"
                />
                <DialogHeader class="space-y-3">
                    <DialogTitle>Novo Produto</DialogTitle>
                    <DialogDescription>
                        Preencha os dados do novo produto.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Nome"
                        field-id="create-product-name"
                        :error="errors.name"
                    >
                        <Input
                            id="create-product-name"
                            name="name"
                            placeholder="Nome do produto"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Preço"
                        field-id="create-product-price"
                        :error="errors.price"
                    >
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

                    <div class="flex items-center space-x-2 py-1">
                        <Checkbox
                            id="create-product-is-lunchbox"
                            :model-value="isLunchbox"
                            @update:model-value="isLunchbox = !!$event"
                        />
                        <label
                            for="create-product-is-lunchbox"
                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Mostrar resumo na tela de pedidos do dia
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
                                    isLunchbox = false;
                                }
                            "
                        >
                            Cancelar
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="save-create-product-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
