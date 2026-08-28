<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
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

const isOpen = ref(false);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button data-test="create-customer-button">
                <Plus class="mr-2 size-4" />
                Novo Cliente
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="CustomerController.store.form()"
                reset-on-success
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Novo Cliente</DialogTitle>
                    <DialogDescription>
                        Preencha os dados do novo cliente.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Nome"
                        field-id="create-customer-name"
                        :error="errors.name"
                    >
                        <Input
                            id="create-customer-name"
                            name="name"
                            placeholder="Nome do cliente"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Telefone"
                        field-id="create-customer-phone"
                        :error="errors.phone"
                    >
                        <Input
                            id="create-customer-phone"
                            name="phone"
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
                        data-test="save-create-customer-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
