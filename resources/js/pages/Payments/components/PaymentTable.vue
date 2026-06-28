<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { CreditCard, XCircle } from 'lucide-vue-next';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    formatCurrency,
    formatDateTime,
    getPaymentStatusBadgeVariant,
} from '@/lib/formatters';
import type { Customer, PaginatedResponse, Payment } from '@/types';

defineProps<{
    customer: Customer;
    payments: PaginatedResponse<Payment>;
    canCancelPayment: boolean;
}>();
</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <div class="flex items-center gap-2 border-b border-sidebar-border/50 px-5 py-4">
            <CreditCard class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-muted-foreground">
                Pagamentos
            </h3>
        </div>

        <EmptyState
            v-if="payments.data.length === 0"
            :icon="CreditCard"
            title="Nenhum pagamento encontrado"
            description="Este cliente ainda não possui pagamentos registrados."
        />

        <div v-else class="flex flex-1 flex-col overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Data</TableHead>
                        <TableHead>Método</TableHead>
                        <TableHead class="text-right">Valor</TableHead>
                        <TableHead class="text-center">Status</TableHead>
                        <TableHead
                            v-if="canCancelPayment"
                            class="w-[60px] text-right"
                            >Ações</TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="payment in payments.data"
                        :key="payment.id"
                    >
                        <TableCell class="whitespace-nowrap text-sm">
                            {{
                                formatDateTime(
                                    payment.paid_at ||
                                        payment.created_at,
                                )
                            }}
                        </TableCell>
                        <TableCell class="capitalize">
                            {{ trans(payment.method) }}
                        </TableCell>
                        <TableCell
                            class="text-right font-medium"
                            :class="{
                                'text-muted-foreground line-through':
                                    payment.status === 'canceled',
                            }"
                        >
                            {{ formatCurrency(payment.value) }}
                        </TableCell>
                        <TableCell class="text-center">
                            <Badge
                                :variant="
                                    getPaymentStatusBadgeVariant(
                                        payment.status,
                                    )
                                "
                                class="capitalize"
                            >
                                {{ trans(payment.status) }}
                            </Badge>
                        </TableCell>
                        <TableCell
                            v-if="canCancelPayment"
                            class="text-right"
                        >
                            <ConfirmationDialog
                                v-if="payment.status !== 'canceled'"
                                title="Cancelar Pagamento"
                                :description="`Tem certeza que deseja cancelar o pagamento #${payment.id} no valor de ${formatCurrency(payment.value)}? O saldo do cliente será debitado e pedidos poderão ser reabertos.`"
                                confirm-text="Cancelar Pagamento"
                                confirming-text="Cancelando..."
                                variant="destructive"
                                :form-action="
                                    PaymentController.cancel.form({
                                        customer: customer.id,
                                        payment: payment.id,
                                    })
                                "
                            >
                                <template #trigger>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Cancelar pagamento"
                                    >
                                        <XCircle
                                            class="size-4 text-destructive"
                                        />
                                        <span class="sr-only">Cancelar</span>
                                    </Button>
                                </template>
                            </ConfirmationDialog>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <TablePagination :paginator="payments" />
        </div>
    </div>
</template>
