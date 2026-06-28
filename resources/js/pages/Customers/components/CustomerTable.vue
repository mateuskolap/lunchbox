<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Contact, Wallet } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import { customerIndex as customerPaymentsIndex } from '@/routes/customers/payments';
import { formatCurrency, formatPhone } from '@/lib/formatters';
import type { Customer, PaginatedResponse } from '@/types';
import CreateCustomerDialog from '../CreateCustomerDialog.vue';
import DeleteCustomerDialog from '../DeleteCustomerDialog.vue';
import EditCustomerDialog from '../EditCustomerDialog.vue';

defineProps<{
    customers: PaginatedResponse<Customer>;
}>();

const { can, canAny } = usePermissions();
</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <EmptyState
            v-if="customers.data.length === 0"
            :icon="Contact"
            title="Nenhum cliente encontrado"
            description="Você ainda não tem nenhum cliente cadastrado. Adicione um novo cliente para começar."
        >
            <CreateCustomerDialog v-if="can('customers.store')" />
        </EmptyState>

        <div v-else class="flex flex-1 flex-col overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Nome</TableHead>
                        <TableHead>Telefone</TableHead>
                        <TableHead class="text-right">Carteira</TableHead>
                        <TableHead
                            v-if="
                                canAny([
                                    'customers.update',
                                    'customers.destroy',
                                    'payments.index',
                                ])
                            "
                            class="w-[140px] text-right"
                            >Ações</TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="customer in customers.data"
                        :key="customer.id"
                    >
                        <TableCell class="font-medium">{{
                            customer.name
                        }}</TableCell>
                        <TableCell>{{ formatPhone(customer.phone) }}</TableCell>
                        <TableCell class="text-right font-medium">
                            {{ formatCurrency(customer.balance) }}
                        </TableCell>
                        <TableCell
                            v-if="
                                canAny([
                                    'customers.update',
                                    'customers.destroy',
                                    'payments.index',
                                ])
                            "
                            class="text-right"
                        >
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    v-if="can('payments.index')"
                                    variant="ghost"
                                    size="icon"
                                    title="Pagamentos"
                                    as-child
                                >
                                    <Link
                                        :href="
                                            customerPaymentsIndex(customer.id)
                                        "
                                    >
                                        <Wallet
                                            class="size-4 text-muted-foreground"
                                        />
                                        <span class="sr-only">Pagamentos</span>
                                    </Link>
                                </Button>
                                <EditCustomerDialog
                                    v-if="can('customers.update')"
                                    :customer="customer"
                                />
                                <DeleteCustomerDialog
                                    v-if="can('customers.destroy')"
                                    :customer="customer"
                                />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <TablePagination :paginator="customers" />
        </div>
    </div>
</template>
