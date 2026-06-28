<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import {
    ArrowLeft,
    CheckCircle2,
    Pencil,
    RotateCcw,
    XCircle,
} from 'lucide-vue-next';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { getOrderStatusBadgeVariant } from '@/lib/formatters';
import { index as ordersIndex, edit as orderEdit } from '@/routes/orders';
import type { Order } from '@/types';

defineProps<{
    order: Order;
    listUrl?: string;
    isMounted: boolean;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-start gap-3 sm:items-center">
            <Button
                variant="outline"
                size="icon"
                as-child
                class="mt-1 shrink-0 sm:mt-0"
            >
                <Link :href="$page.props.previousUrl || listUrl || ordersIndex()">
                    <ArrowLeft class="size-4" />
                    <span class="sr-only">Voltar</span>
                </Link>
            </Button>
            <div class="flex flex-col gap-1">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <Badge
                        :variant="getOrderStatusBadgeVariant(order.status)"
                        class="order-first w-fit capitalize sm:order-last"
                    >
                        {{ trans(order.status) }}
                    </Badge>
                    <Heading
                        :title="`Pedido #${order.id}`"
                        description=""
                        variant="small"
                        class="p-0 text-lg font-bold break-words sm:text-xl"
                    />
                </div>
                <p class="text-xs text-muted-foreground sm:text-sm">
                    Criado em
                    {{
                        isMounted
                            ? new Date(order.created_at).toLocaleString('pt-BR')
                            : ''
                    }}
                </p>
            </div>
        </div>

        <!-- Workflow status actions -->
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <!-- Edit Details button -->
            <Button
                v-if="order.status === 'pending'"
                variant="outline"
                as-child
                class="w-full sm:w-auto"
            >
                <Link :href="orderEdit.url(order.id)">
                    <Pencil class="mr-2 size-4" />
                    Editar Detalhes
                </Link>
            </Button>

            <!-- Conclude Order -->
            <ConfirmationDialog
                v-if="order.status === 'pending'"
                title="Concluir Pedido"
                description="Tem certeza que deseja marcar este pedido como Concluído? Os itens serão fixados."
                confirm-text="Concluir"
                confirming-text="Concluindo..."
                variant="default"
                :form-action="OrderController.conclude.form(order.id)"
            >
                <template #trigger>
                    <Button
                        variant="default"
                        class="w-full bg-emerald-600 text-white hover:bg-emerald-700 sm:w-auto dark:bg-emerald-700 dark:hover:bg-emerald-800"
                    >
                        <CheckCircle2 class="mr-2 size-4" />
                        Concluir Pedido
                    </Button>
                </template>
            </ConfirmationDialog>

            <!-- Cancel Order -->
            <ConfirmationDialog
                v-if="order.status === 'pending'"
                title="Cancelar Pedido"
                description="Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita."
                confirm-text="Cancelar Pedido"
                confirming-text="Cancelando..."
                variant="destructive"
                :form-action="OrderController.cancel.form(order.id)"
            >
                <template #trigger>
                    <Button variant="destructive" class="w-full sm:w-auto">
                        <XCircle class="mr-2 size-4" />
                        Cancelar Pedido
                    </Button>
                </template>
            </ConfirmationDialog>

            <!-- Reopen Order -->
            <ConfirmationDialog
                v-if="order.status !== 'pending'"
                title="Reabrir Pedido"
                description="Deseja reabrir este pedido? Ele voltará ao estado pendente."
                confirm-text="Reabrir"
                confirming-text="Reabrindo..."
                variant="secondary"
                :form-action="OrderController.reopen.form(order.id)"
            >
                <template #trigger>
                    <Button variant="outline" class="w-full sm:w-auto">
                        <RotateCcw class="mr-2 size-4" />
                        Reabrir Pedido
                    </Button>
                </template>
            </ConfirmationDialog>
        </div>
    </div>
</template>
