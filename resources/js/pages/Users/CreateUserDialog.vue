<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import UserController from '@/actions/App/Http/Controllers/UserController';
import FormField from '@/components/FormField.vue';
import PasswordInput from '@/components/PasswordInput.vue';
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
            <Button data-test="create-user-button">
                <Plus class="mr-2 size-4" />
                Novo Usuário
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="UserController.store.form()"
                reset-on-success
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Novo Usuário</DialogTitle>
                    <DialogDescription>
                        Preencha os dados do novo usuário.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Nome"
                        field-id="create-user-name"
                        :error="errors.name"
                    >
                        <Input
                            id="create-user-name"
                            name="name"
                            placeholder="Nome do usuário"
                            required
                        />
                    </FormField>

                    <FormField
                        label="E-mail"
                        field-id="create-user-email"
                        :error="errors.email"
                    >
                        <Input
                            id="create-user-email"
                            name="email"
                            type="email"
                            placeholder="email@exemplo.com"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Senha"
                        field-id="create-user-password"
                        :error="errors.password"
                    >
                        <PasswordInput
                            id="create-user-password"
                            name="password"
                            placeholder="Digite uma senha forte"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Confirmar Senha"
                        field-id="create-user-password_confirmation"
                        :error="errors.password_confirmation"
                    >
                        <PasswordInput
                            id="create-user-password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirme a senha digitada"
                            required
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
                        data-test="save-create-user-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
