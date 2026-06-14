<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Contact } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import TablePagination from '@/components/TablePagination.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import { formatPhone } from '@/lib/formatters';
import { index as customersIndex } from '@/routes/customers';
import type { Customer, PaginatedResponse } from '@/types';
import CreateCustomerDialog from './CreateCustomerDialog.vue';
import DeleteCustomerDialog from './DeleteCustomerDialog.vue';
import EditCustomerDialog from './EditCustomerDialog.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clientes',
                href: customersIndex(),
            },
        ],
    },
});

defineProps<{
    customers: PaginatedResponse<Customer>;
}>();

const { can, canAny } = usePermissions();
</script>

<template>
    <Head title="Clientes" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Clientes"
                description="Gerencie os clientes do sistema."
            />
            <CreateCustomerDialog v-if="can('customers.store')" />
        </div>

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
                            <TableHead
                                v-if="
                                    canAny([
                                        'customers.update',
                                        'customers.destroy',
                                    ])
                                "
                                class="w-[100px] text-right"
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
                            <TableCell>{{
                                formatPhone(customer.phone)
                            }}</TableCell>
                            <TableCell
                                v-if="
                                    canAny([
                                        'customers.update',
                                        'customers.destroy',
                                    ])
                                "
                                class="text-right"
                            >
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
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
    </div>
</template>
