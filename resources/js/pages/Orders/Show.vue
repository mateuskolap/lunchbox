<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ShoppingCart,
    User,
    Calendar,
    FileText,
    CheckCircle2,
    XCircle,
    RotateCcw,
    ArrowLeft,
    Pencil,
    ChevronRight,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppSelect from '@/components/AppSelect.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency, formatPhone } from '@/lib/formatters';
import { index as ordersIndex } from '@/routes/orders';
import type { Order, Product } from '@/types';

const props = defineProps<{
    order: Order;
    products: Product[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pedidos',
                href: ordersIndex(),
            },
            {
                title: 'Detalhes do Pedido',
                href: '#',
            },
        ],
    },
});

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const datePart = dateStr.split('T')[0];
    const parts = datePart.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${day}/${month}/${year}`;
};

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'concluded':
            return 'default'; // dark/success style
        case 'canceled':
            return 'destructive'; // red style
        default:
            return 'secondary'; // gray/pending style
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'concluded':
            return 'Concluído';
        case 'canceled':
            return 'Cancelado';
        default:
            return 'Pendente';
    }
};
</script>

<template>
    <Head :title="`Pedido #${order.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <!-- Header Actions -->
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
                    <Link :href="ordersIndex()">
                        <ArrowLeft class="size-4" />
                        <span class="sr-only">Voltar</span>
                    </Link>
                </Button>
                <div class="flex flex-col gap-1">
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center"
                    >
                        <Badge
                            :variant="getStatusBadgeVariant(order.status)"
                            class="order-first w-fit capitalize sm:order-last"
                        >
                            {{ getStatusText(order.status) }}
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
                        {{ new Date(order.created_at).toLocaleString('pt-BR') }}
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
                    <Link :href="`/pedidos/${order.id}/editar`">
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

        <div class="grid gap-6 md:grid-cols-12">
            <!-- Left Side: Summary Info Cards -->
            <div class="order-2 min-w-0 space-y-6 md:order-1 md:col-span-4">
                <!-- Customer info -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-sm font-semibold text-muted-foreground"
                    >
                        <User class="size-4" />
                        Cliente
                    </h3>
                    <div v-if="order.customer" class="min-w-0 space-y-2">
                        <p
                            class="text-base font-bold break-words text-foreground"
                        >
                            {{ order.customer.name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Telefone: {{ formatPhone(order.customer.phone) }}
                        </p>
                    </div>
                </div>

                <!-- Order Info -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-sm font-semibold text-muted-foreground"
                    >
                        <Calendar class="size-4" />
                        Informações Gerais
                    </h3>
                    <div class="space-y-3">
                        <div
                            class="flex flex-wrap items-baseline justify-between gap-1 text-sm"
                        >
                            <span class="text-muted-foreground"
                                >Data de Entrega:</span
                            >
                            <span class="font-medium text-foreground">{{
                                formatDate(order.date)
                            }}</span>
                        </div>
                        <div
                            class="flex flex-wrap items-baseline justify-between gap-1 border-t border-sidebar-border/50 pt-2 text-sm"
                        >
                            <span class="text-muted-foreground"
                                >Valor dos Itens:</span
                            >
                            <span class="font-medium text-foreground">{{
                                formatCurrency(order.total_items_amount || 0)
                            }}</span>
                        </div>
                        <div
                            class="flex flex-wrap items-baseline justify-between gap-1 border-t border-sidebar-border/50 pt-2 text-sm"
                        >
                            <span class="text-muted-foreground"
                                >Valor Total:</span
                            >
                            <span class="text-base font-bold text-foreground">{{
                                formatCurrency(order.total_amount)
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Observations -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
                >
                    <h3
                        class="mb-2 flex items-center gap-2 text-sm font-semibold text-muted-foreground"
                    >
                        <FileText class="size-4" />
                        Observações
                    </h3>
                    <p
                        class="rounded-lg border border-sidebar-border/50 bg-muted/30 p-3 text-sm break-words whitespace-pre-line text-foreground"
                    >
                        {{
                            order.observation || 'Nenhuma observação informada.'
                        }}
                    </p>
                </div>
            </div>

            <!-- Right Side: Items List -->
            <div class="order-1 min-w-0 space-y-6 md:order-2 md:col-span-8">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
                >
                    <div
                        class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <h3
                            class="flex items-center gap-2 text-base font-semibold"
                        >
                            <ShoppingCart
                                class="size-4 text-muted-foreground"
                            />
                            Produtos no Pedido
                        </h3>
                    </div>

                    <!-- Items Table -->
                    <div
                        class="w-full overflow-x-auto rounded-lg border border-sidebar-border bg-card/50"
                    >
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Produto</TableHead>
                                    <TableHead class="text-right"
                                        >Unitário</TableHead
                                    >
                                    <TableHead class="w-[100px] text-center"
                                        >Quantidade</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Subtotal</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-if="
                                        !order.items || order.items.length === 0
                                    "
                                >
                                    <TableCell
                                        colspan="4"
                                        class="py-6 text-center text-sm text-muted-foreground"
                                    >
                                        Nenhum item neste pedido.
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="item in order.items"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">
                                        {{
                                            item.product?.name ||
                                            `Produto #${item.product_id}`
                                        }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ formatCurrency(item.unit_price) }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        {{ item.quantity }}
                                    </TableCell>
                                    <TableCell
                                        class="text-right font-medium text-foreground"
                                    >
                                        {{ formatCurrency(item.total_amount) }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
