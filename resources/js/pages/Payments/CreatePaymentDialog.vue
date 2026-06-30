<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import type { Customer } from '@/types';

defineProps<{
    customer: Customer;
}>();

const isOpen = ref(false);

const today = new Date();
const year = today.getFullYear();
const month = String(today.getMonth() + 1).padStart(2, '0');
const day = String(today.getDate()).padStart(2, '0');
const paidAtValue = ref(`${year}-${month}-${day}`);

const paymentMethods = [
    { value: 'cash', label: 'Dinheiro' },
    { value: 'pix', label: 'Pix' },
    { value: 'credit_card', label: 'Cartão de Crédito' },
    { value: 'debit_card', label: 'Cartão de Débito' },
    { value: 'food_voucher', label: 'Vale Refeição' },
    { value: 'other', label: 'Outro' },
];
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button data-test="create-payment-button">
                <Plus class="mr-2 size-4" />
                Novo Pagamento
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="PaymentController.store.form(customer.id)"
                reset-on-success
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Novo Pagamento</DialogTitle>
                    <DialogDescription>
                        Registre um novo pagamento para
                        {{ customer.name }}.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Método de Pagamento"
                        field-id="create-payment-method"
                        :error="errors.method"
                    >
                        <Select name="method" required>
                            <SelectTrigger
                                id="create-payment-method"
                                class="w-full"
                            >
                                <SelectValue placeholder="Selecione o método" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    :value="method.value"
                                >
                                    {{ method.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FormField>

                    <FormField
                        label="Valor"
                        field-id="create-payment-value"
                        :error="errors.value"
                    >
                        <Input
                            id="create-payment-value"
                            name="value"
                            type="number"
                            step="0.01"
                            min="0.01"
                            placeholder="0,00"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Data do Pagamento"
                        field-id="create-payment-paid-at"
                        :error="errors.paid_at"
                    >
                        <Input
                            id="create-payment-paid-at"
                            name="paid_at"
                            type="date"
                            v-model="paidAtValue"
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
                        data-test="save-create-payment-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Registrando...' : 'Registrar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
