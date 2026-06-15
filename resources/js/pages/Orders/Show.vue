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
    Plus,
    Trash2,
    ArrowLeft,
    Pencil,
    ChevronRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

const isAddingItem = ref(false);
const newProductId = ref<string>('');
const newQuantity = ref<number>(1);

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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="ordersIndex()">
                        <ArrowLeft class="size-4" />
                        <span class="sr-only">Voltar</span>
                    </Link>
                </Button>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Heading
                            :title="`Pedido #${order.id}`"
                            description=""
                            class="p-0 break-words"
                        />
                        <Badge :variant="getStatusBadgeVariant(order.status)" class="capitalize">
                            {{ getStatusText(order.status) }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Criado em {{ new Date(order.created_at).toLocaleString('pt-BR') }}
                    </p>
                </div>
            </div>

            <!-- Workflow status actions -->
            <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2">
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
                        <Button variant="default" class="w-full sm:w-auto bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">
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
            <div class="min-w-0 space-y-6 md:col-span-4">
                <!-- Customer info -->
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground flex items-center gap-2">
                        <User class="size-4" />
                        Cliente
                    </h3>
                    <div v-if="order.customer" class="space-y-2 min-w-0">
                        <p class="text-base font-bold text-foreground break-words">{{ order.customer.name }}</p>
                        <p class="text-sm text-muted-foreground">Telefone: {{ formatPhone(order.customer.phone) }}</p>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground flex items-center gap-2">
                        <Calendar class="size-4" />
                        Informações Gerais
                    </h3>
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-baseline justify-between gap-1 text-sm">
                            <span class="text-muted-foreground">Data de Entrega:</span>
                            <span class="font-medium text-foreground">{{ formatDate(order.date) }}</span>
                        </div>
                        <div class="flex flex-wrap items-baseline justify-between gap-1 text-sm border-t border-sidebar-border/50 pt-2">
                            <span class="text-muted-foreground">Valor dos Itens:</span>
                            <span class="font-medium text-foreground">{{ formatCurrency(order.total_items_amount || 0) }}</span>
                        </div>
                        <div class="flex flex-wrap items-baseline justify-between gap-1 text-sm border-t border-sidebar-border/50 pt-2">
                            <span class="text-muted-foreground">Valor Total:</span>
                            <span class="font-bold text-foreground text-base">{{ formatCurrency(order.total_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Observations -->
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                    <h3 class="mb-2 text-sm font-semibold text-muted-foreground flex items-center gap-2">
                        <FileText class="size-4" />
                        Observações
                    </h3>
                    <p class="text-sm text-foreground whitespace-pre-line break-words bg-muted/30 p-3 rounded-lg border border-sidebar-border/50">
                        {{ order.observation || 'Nenhuma observação informada.' }}
                    </p>
                </div>
            </div>

            <!-- Right Side: Items List -->
            <div class="min-w-0 space-y-6 md:col-span-8">
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold flex items-center gap-2">
                            <ShoppingCart class="size-4 text-muted-foreground" />
                            Produtos no Pedido
                        </h3>

                        <!-- Add Item trigger -->
                        <Button
                            v-if="order.status === 'pending'"
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="isAddingItem = !isAddingItem"
                        >
                            <Plus class="mr-1 size-4" />
                            {{ isAddingItem ? 'Fechar formulário' : 'Adicionar Produto' }}
                        </Button>
                    </div>

                    <!-- Add Item Inline Form -->
                    <div
                        v-if="order.status === 'pending' && isAddingItem"
                        class="mb-6 rounded-lg border border-sidebar-border/70 bg-muted/40 p-4"
                    >
                        <h4 class="mb-3 text-sm font-bold text-foreground">Novo Item</h4>
                        <Form
                            v-bind="OrderController.addItems.form(order.id)"
                            reset-on-success
                            @success="() => { isAddingItem = false; newProductId = ''; newQuantity = 1; }"
                            class="grid gap-4 sm:grid-cols-12 items-end"
                            v-slot="{ errors, processing }"
                        >
                            <!-- Hidden inputs matching nested list required by backend -->
                            <input type="hidden" name="order_items[0][product_id]" :value="newProductId" />
                            <input type="hidden" name="order_items[0][quantity]" :value="newQuantity" />

                            <div class="sm:col-span-7">
                                <FormField
                                    label="Produto"
                                    field-id="add-item-product"
                                    :error="errors.order_items || errors['order_items.0.product_id']"
                                >
                                    <select
                                        id="add-item-product"
                                        v-model="newProductId"
                                        required
                                        class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full max-w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                                    >
                                        <option value="" disabled selected>Selecione...</option>
                                        <option
                                            v-for="product in products"
                                            :key="product.id"
                                            :value="product.id"
                                        >
                                            {{ product.name }} - {{ formatCurrency(product.price) }}
                                        </option>
                                    </select>
                                </FormField>
                            </div>

                            <div class="sm:col-span-3">
                                <FormField
                                    label="Qtd"
                                    field-id="add-item-qty"
                                    :error="errors['order_items.0.quantity']"
                                >
                                    <Input
                                        id="add-item-qty"
                                        type="number"
                                        v-model.number="newQuantity"
                                        min="1"
                                        required
                                        class="h-9"
                                    />
                                </FormField>
                            </div>

                            <div class="sm:col-span-2">
                                <Button type="submit" class="w-full h-9" :disabled="processing || !newProductId">
                                    Adicionar
                                </Button>
                            </div>
                        </Form>
                    </div>

                    <!-- Items Table -->
                    <div class="w-full overflow-x-auto rounded-lg border border-sidebar-border bg-card/50">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Produto</TableHead>
                                    <TableHead class="text-right">Unitário</TableHead>
                                    <TableHead class="text-center w-[100px]">Quantidade</TableHead>
                                    <TableHead class="text-right">Subtotal</TableHead>
                                    <TableHead v-if="order.status === 'pending'" class="w-[60px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="!order.items || order.items.length === 0">
                                    <TableCell colspan="5" class="py-6 text-center text-sm text-muted-foreground">
                                        Nenhum item neste pedido.
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="item in order.items"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">
                                        {{ item.product?.name || `Produto #${item.product_id}` }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ formatCurrency(item.unit_price) }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        {{ item.quantity }}
                                    </TableCell>
                                    <TableCell class="text-right font-medium text-foreground">
                                        {{ formatCurrency(item.total_amount) }}
                                    </TableCell>
                                    <TableCell v-if="order.status === 'pending'" class="text-center">
                                        <!-- Remove Item Confirmation -->
                                        <ConfirmationDialog
                                            title="Remover Item"
                                            :description="`Deseja remover o produto ${item.product?.name || ''} do pedido?`"
                                            confirm-text="Remover"
                                            confirming-text="Removendo..."
                                            variant="destructive"
                                            :form-action="OrderController.removeItem.form({ order: order.id, item: item.id })"
                                        >
                                            <template #trigger>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50"
                                                >
                                                    <Trash2 class="size-4" />
                                                    <span class="sr-only">Remover</span>
                                                </Button>
                                            </template>
                                        </ConfirmationDialog>
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
