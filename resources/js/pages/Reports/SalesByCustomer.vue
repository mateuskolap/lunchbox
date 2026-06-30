<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BarChart3,
    Filter,
    X,
    TrendingUp,
    Coins,
    Scale,
    Contact,
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import EmptyState from '@/components/EmptyState.vue';
import TablePagination from '@/components/TablePagination.vue';
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
import { useFilters } from '@/composables/useFilters';
import { formatCurrency, formatPhone } from '@/lib/formatters';
import { salesByCustomer } from '@/routes/reports';
import type { PaginatedResponse } from '@/types';

interface CustomerReportRow {
    id: number;
    name: string;
    phone?: string;
    balance: string | number;
    orders_count: number;
    orders_sum_total_amount: string | null;
    orders_sum_paid_amount: string | null;
}

interface SalesReportFiltersState {
    start_date: string;
    end_date: string;
    customer: string;
}

const props = defineProps<{
    customers: PaginatedResponse<CustomerReportRow>;
    total_sales: number;
    total_paid: number;
    total_balance: number;
    filters: {
        start_date?: string;
        end_date?: string;
        customer?: string;
    };
}>();

const { filters, clearFilters, hasActiveFilters } =
    useFilters<SalesReportFiltersState>(
        () => salesByCustomer.url(),
        {
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || '',
            customer: props.filters.customer || '',
        },
        {
            transform: (f) => ({
                start_date: f.start_date || undefined,
                end_date: f.end_date || undefined,
                customer: f.customer || undefined,
            }),
        },
    );

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Relatórios',
                href: '#',
            },
            {
                title: 'Vendas por Cliente',
                href: salesByCustomer.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Relatório de Vendas por Cliente" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Relatório de Vendas por Cliente"
                description="Consulte as vendas, valores pagos e saldos devedores por cliente no período selecionado."
            />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                    >
                        <TrendingUp class="size-5 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground">
                            Total de Vendas
                        </p>
                        <p class="text-lg font-bold text-foreground">
                            {{ formatCurrency(total_sales) }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10"
                    >
                        <Coins
                            class="size-5 text-emerald-600 dark:text-emerald-400"
                        />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground">Total Pago</p>
                        <p
                            class="text-lg font-bold text-emerald-600 text-foreground dark:text-emerald-400"
                        >
                            {{ formatCurrency(total_paid) }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-destructive/10"
                    >
                        <Scale class="size-5 text-destructive" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground">
                            Saldo em Aberto
                        </p>
                        <p
                            class="text-lg font-bold"
                            :class="{
                                'text-destructive': total_balance > 0,
                                'text-foreground': total_balance <= 0,
                            }"
                        >
                            {{ formatCurrency(total_balance) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <div
                    class="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground"
                >
                    <Filter class="size-4" />
                    Filtros
                </div>
                <Button
                    v-if="hasActiveFilters"
                    type="button"
                    variant="ghost"
                    class="h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
                    @click="clearFilters"
                >
                    <X class="mr-1 size-3.5" />
                    Limpar
                </Button>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-[180px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Data Inicial</label
                    >
                    <Input
                        type="date"
                        v-model="filters.start_date"
                        class="h-9"
                    />
                </div>

                <div class="w-full sm:w-[180px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Data Final</label
                    >
                    <Input type="date" v-model="filters.end_date" class="h-9" />
                </div>

                <div class="w-full sm:w-[260px]">
                    <label
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                        >Cliente</label
                    >
                    <Input
                        v-model="filters.customer"
                        placeholder="Nome do cliente"
                        class="h-9"
                    />
                </div>
            </div>
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
        >
            <EmptyState
                v-if="customers.data.length === 0"
                :icon="Contact"
                title="Nenhum registro encontrado"
                description="Não há vendas registradas para os filtros selecionados no período."
            />

            <div v-else class="flex flex-1 flex-col overflow-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Cliente</TableHead>
                            <TableHead>Telefone</TableHead>
                            <TableHead class="text-center"
                                >Qtd. Pedidos</TableHead
                            >
                            <TableHead class="text-right"
                                >Total Vendido</TableHead
                            >
                            <TableHead class="text-right">Total Pago</TableHead>
                            <TableHead class="text-right"
                                >Saldo em Aberto</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in customers.data" :key="item.id">
                            <TableCell class="font-medium">
                                {{ item.name }}
                            </TableCell>
                            <TableCell>
                                {{ formatPhone(item.phone) }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ item.orders_count }}
                            </TableCell>
                            <TableCell class="text-right font-medium">
                                {{
                                    formatCurrency(
                                        item.orders_sum_total_amount || 0,
                                    )
                                }}
                            </TableCell>
                            <TableCell
                                class="text-right font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                {{
                                    formatCurrency(
                                        item.orders_sum_paid_amount || 0,
                                    )
                                }}
                            </TableCell>
                            <TableCell
                                class="text-right font-medium"
                                :class="{
                                    'text-destructive':
                                        Number(
                                            item.orders_sum_total_amount || 0,
                                        ) -
                                            Number(
                                                item.orders_sum_paid_amount ||
                                                    0,
                                            ) >
                                        0,
                                }"
                            >
                                {{
                                    formatCurrency(
                                        Number(
                                            item.orders_sum_total_amount || 0,
                                        ) -
                                            Number(
                                                item.orders_sum_paid_amount ||
                                                    0,
                                            ),
                                    )
                                }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <TablePagination :paginator="customers" />
            </div>
        </div>
    </div>
</template>
