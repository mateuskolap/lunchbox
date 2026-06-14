<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
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
import { Spinner } from '@/components/ui/spinner';

type Props = {
    title: string;
    description: string;
    confirmText?: string;
    confirmingText?: string;
    cancelText?: string;
    variant?:
        | 'default'
        | 'destructive'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'link';
    formAction: Record<string, unknown>;
    formOptions?: Record<string, unknown>;
};

withDefaults(defineProps<Props>(), {
    confirmText: 'Confirmar',
    confirmingText: 'Processando...',
    cancelText: 'Cancelar',
    variant: 'destructive',
});
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="formAction"
                :options="formOptions"
                class="space-y-6"
                v-slot="{ processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>
                        <slot name="description">{{ description }}</slot>
                    </DialogDescription>
                </DialogHeader>

                <slot name="content" />

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
                            {{ cancelText }}
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :variant="variant"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        {{ processing ? confirmingText : confirmText }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
