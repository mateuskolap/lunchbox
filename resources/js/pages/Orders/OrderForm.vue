<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import {
    Calendar,
    FileText,
    Plus,
    ShoppingBag,
    Trash2,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import AppSelect from '@/components/AppSelect.vue';
import FormField from '@/components/FormField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency } from '@/lib/formatters';
import type { Customer, Order, Product } from '@/types';

type SelectedItem = {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
};

const props = defineProps<{
    action: Record<string, unknown>;
    order?: Order;
    customers: Customer[];
    products: Product[];
    isEdit?: boolean;
}>();

const selectedItems = ref<SelectedItem[]>([]);
const currentProductId = ref<string>('');
const currentQuantity = ref<number>(1);
const dateValue = ref<string>('');
const customerIdValue = ref(
    props.order?.customer_id ? String(props.order.customer_id) : '',
);

const customerOptions = computed(() => {
    return props.customers.map((c) => ({
        value: String(c.id),
        label: c.name,
    }));
});

const productOptions = computed(() => {
    return props.products.map((p) => ({
        value: String(p.id),
        label: `${p.name} - ${formatCurrency(p.price)}`,
    }));
});

// Initialize form data
onMounted(() => {
    if (props.order) {
        // Edit mode: Load existing items
        if (props.order.items) {
            selectedItems.value = props.order.items.map((item) => ({
                product_id: item.product_id,
                name: item.product?.name ?? `Produto #${item.product_id}`,
                price: Number(item.unit_price),
                quantity: Number(item.quantity),
            }));
        }

        // Load date
        if (props.order.date) {
            dateValue.value = new Date(props.order.date)
                .toISOString()
                .split('T')[0];
        }
    } else {
        // Create mode: Default date to today
        dateValue.value = new Date().toISOString().split('T')[0];
    }
});

// Calculate total amount
const totalAmount = computed(() => {
    return selectedItems.value.reduce(
        (total, item) => total + item.price * item.quantity,
        0,
    );
});

// Add item to list
const addItem = () => {
    if (!currentProductId.value) {
        return;
    }

    const prodId = Number(currentProductId.value);
    const product = props.products.find((p) => p.id === prodId);

    if (!product) {
        return;
    }

    const qty = Math.max(1, Math.floor(currentQuantity.value));

    // Check if product already added
    const existingIndex = selectedItems.value.findIndex(
        (item) => item.product_id === prodId,
    );

    if (existingIndex > -1) {
        selectedItems.value[existingIndex].quantity += qty;
    } else {
        selectedItems.value.push({
            product_id: prodId,
            name: product.name,
            price: Number(product.price),
            quantity: qty,
        });
    }

    // Reset inputs
    currentProductId.value = '';
    currentQuantity.value = 1;
};

// Remove item from list
const removeItem = (productId: number) => {
    selectedItems.value = selectedItems.value.filter(
        (item) => item.product_id !== productId,
    );
};
</script>

<template>
    <Form v-bind="action" class="space-y-6" v-slot="{ errors, processing }">
        <!-- hidden inputs for nested list submission -->
        <template v-for="(item, index) in selectedItems" :key="item.product_id">
            <input
                type="hidden"
                :name="`order_items[${index}][product_id]`"
                :value="item.product_id"
            />
            <input
                type="hidden"
                :name="`order_items[${index}][quantity]`"
                :value="item.quantity"
            />
        </template>
        <input type="hidden" name="total_amount" :value="totalAmount" />

        <div class="grid gap-6 md:grid-cols-12">
            <!-- Left Side: Basic Info -->
            <div class="order-2 space-y-6 md:order-1 md:col-span-4">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-base font-semibold"
                    >
                        <FileText class="size-4 text-muted-foreground" />
                        Dados do Pedido
                    </h3>

                    <div class="space-y-4">
                        <!-- Customer -->
                        <FormField
                            label="Cliente"
                            field-id="order-customer"
                            :error="errors.customer_id"
                        >
                            <div
                                v-if="isEdit && order?.customer"
                                class="flex items-center gap-2 rounded-md border border-input bg-muted/40 px-3 py-2 text-sm text-foreground"
                            >
                                <User class="size-4 text-muted-foreground" />
                                <span>{{ order.customer.name }}</span>
                                <input
                                    type="hidden"
                                    name="customer_id"
                                    :value="order.customer_id"
                                />
                            </div>
                            <div v-else class="relative">
                                <AppSelect
                                    v-model="customerIdValue"
                                    name="customer_id"
                                    :options="customerOptions"
                                    placeholder="Selecione um cliente..."
                                    search-placeholder="Pesquisar cliente..."
                                    empty-text="Nenhum cliente encontrado"
                                    required
                                    searchable
                                />
                            </div>
                        </FormField>

                        <!-- Date -->
                        <FormField
                            label="Data do Pedido"
                            field-id="order-date"
                            :error="errors.date"
                        >
                            <div class="relative">
                                <Input
                                    id="order-date"
                                    name="date"
                                    type="date"
                                    required
                                    v-model="dateValue"
                                    class="pl-9"
                                />
                                <Calendar
                                    class="absolute top-2.5 left-3 size-4 text-muted-foreground"
                                />
                            </div>
                        </FormField>

                        <!-- Observation -->
                        <FormField
                            label="Observação"
                            field-id="order-observation"
                            :error="errors.observation"
                        >
                            <textarea
                                id="order-observation"
                                name="observation"
                                rows="4"
                                :default-value="order?.observation || ''"
                                placeholder="Insira observações sobre o pedido..."
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                            ></textarea>
                        </FormField>
                    </div>
                </div>
            </div>

            <!-- Right Side: Items Selection -->
            <div class="order-1 space-y-6 md:order-2 md:col-span-8">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-base font-semibold"
                    >
                        <ShoppingBag class="size-4 text-muted-foreground" />
                        Itens do Pedido
                    </h3>

                    <!-- Add Item Inputs -->
                    <div class="mb-6 grid items-end gap-4 sm:grid-cols-12">
                        <div class="sm:col-span-7">
                            <label
                                class="mb-1.5 block text-xs font-semibold text-muted-foreground"
                                >Produto</label
                            >
                            <AppSelect
                                v-model="currentProductId"
                                :options="productOptions"
                                placeholder="Selecione um produto..."
                                search-placeholder="Pesquisar produto..."
                                empty-text="Nenhum produto encontrado"
                                searchable
                            />
                        </div>
                        <div class="sm:col-span-3">
                            <label
                                class="mb-1.5 block text-xs font-semibold text-muted-foreground"
                                >Qtd</label
                            >
                            <Input
                                type="number"
                                v-model.number="currentQuantity"
                                min="1"
                                placeholder="1"
                                class="h-9"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="h-9 w-full"
                                @click="addItem"
                                :disabled="!currentProductId"
                            >
                                <Plus class="mr-1 size-4" />
                                Adicionar
                            </Button>
                        </div>
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
                                        >Qtd</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Subtotal</TableHead
                                    >
                                    <TableHead class="w-[60px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="selectedItems.length === 0">
                                    <TableCell
                                        colspan="5"
                                        class="py-6 text-center text-sm text-muted-foreground"
                                    >
                                        Nenhum item adicionado ao pedido.
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="item in selectedItems"
                                    :key="item.product_id"
                                >
                                    <TableCell class="font-medium">{{
                                        item.name
                                    }}</TableCell>
                                    <TableCell class="text-right">{{
                                        formatCurrency(item.price)
                                    }}</TableCell>
                                    <TableCell class="text-center">{{
                                        item.quantity
                                    }}</TableCell>
                                    <TableCell
                                        class="text-right font-medium text-foreground"
                                    >
                                        {{
                                            formatCurrency(
                                                item.price * item.quantity,
                                            )
                                        }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50"
                                            @click="removeItem(item.product_id)"
                                        >
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Remover</span>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Summary / Footer -->
                    <div
                        class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-sidebar-border pt-4 sm:flex-row"
                    >
                        <div
                            class="flex gap-4 text-sm font-medium text-muted-foreground"
                        >
                            <span
                                >Total de Itens:
                                <strong class="text-foreground">{{
                                    selectedItems.reduce(
                                        (acc, item) => acc + item.quantity,
                                        0,
                                    )
                                }}</strong></span
                            >
                        </div>
                        <div class="text-lg font-bold text-foreground">
                            Valor Total:
                            <span class="text-xl font-extrabold text-primary">{{
                                formatCurrency(totalAmount)
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validation Errors -->
        <div
            v-if="errors.order_items"
            class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-950/30 dark:text-red-400"
        >
            {{ errors.order_items }}
        </div>

        <!-- Submit -->
        <div class="mt-6 flex items-center justify-end gap-3">
            <Button
                type="submit"
                :disabled="processing || selectedItems.length === 0"
                data-test="save-order-button"
            >
                <Spinner v-if="processing" />
                {{ processing ? 'Salvando...' : 'Salvar Pedido' }}
            </Button>
        </div>
    </Form>
</template>
