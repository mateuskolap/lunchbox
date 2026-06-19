<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import { ref } from 'vue';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import FormField from '@/components/FormField.vue';
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
import { Spinner } from '@/components/ui/spinner';
import type { Customer } from '@/types/customer';

defineProps<{
    customer: Customer;
}>();

const isOpen = ref(false);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                title="Editar"
                data-test="edit-customer-button"
            >
                <Pencil class="size-4" />
                <span class="sr-only">Editar</span>
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="CustomerController.update.form(customer.id)"
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Editar Cliente</DialogTitle>
                    <DialogDescription>
                        Altere os dados do cliente.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Nome"
                        field-id="edit-customer-name"
                        :error="errors.name"
                    >
                        <Input
                            id="edit-customer-name"
                            name="name"
                            :default-value="customer.name"
                            placeholder="Nome do cliente"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Telefone"
                        field-id="edit-customer-phone"
                        :error="errors.phone"
                    >
                        <Input
                            id="edit-customer-phone"
                            name="phone"
                            :default-value="customer.phone"
                            placeholder="Ex: 11999999999"
                        />
                    </FormField>
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
                        data-test="save-edit-customer-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
