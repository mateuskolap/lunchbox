<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
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
import type { User } from '@/types/auth';

defineProps<{
    user: User;
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
                data-test="edit-user-button"
            >
                <Pencil class="size-4" />
                <span class="sr-only">Editar</span>
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="UserController.update.form(user.id)"
                @success="isOpen = false"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Editar Usuário</DialogTitle>
                    <DialogDescription>
                        Altere os dados do usuário.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <FormField
                        label="Nome"
                        field-id="edit-user-name"
                        :error="errors.name"
                    >
                        <Input
                            id="edit-user-name"
                            name="name"
                            :default-value="user.name"
                            placeholder="Nome do usuário"
                            required
                        />
                    </FormField>

                    <FormField
                        label="E-mail"
                        field-id="edit-user-email"
                        :error="errors.email"
                    >
                        <Input
                            id="edit-user-email"
                            name="email"
                            type="email"
                            :default-value="user.email"
                            placeholder="email@exemplo.com"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Nova Senha"
                        field-id="edit-user-password"
                        :error="errors.password"
                    >
                        <PasswordInput
                            id="edit-user-password"
                            name="password"
                            placeholder="Deixe em branco para manter a senha atual"
                        />
                    </FormField>

                    <FormField
                        label="Confirmar Nova Senha"
                        field-id="edit-user-password_confirmation"
                        :error="errors.password_confirmation"
                    >
                        <PasswordInput
                            id="edit-user-password_confirmation"
                            name="password_confirmation"
                            placeholder="Deixe em branco para manter a senha atual"
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
                        data-test="save-edit-user-button"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? 'Salvando...' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
