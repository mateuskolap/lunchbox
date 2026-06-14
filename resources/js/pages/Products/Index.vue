<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { PackageOpen } from 'lucide-vue-next';
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
import { formatCurrency } from '@/lib/formatters';
import { index as productsIndex } from '@/routes/products';
import type { Product, PaginatedResponse } from '@/types';
import CreateProductDialog from './CreateProductDialog.vue';
import DeleteProductDialog from './DeleteProductDialog.vue';
import EditProductDialog from './EditProductDialog.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Produtos',
                href: productsIndex(),
            },
        ],
    },
});

defineProps<{
    products: PaginatedResponse<Product>;
}>();

const { can, canAny } = usePermissions();
</script>

<template>
    <Head title="Produtos" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Produtos"
                description="Gerencie os produtos do sistema."
            />
            <CreateProductDialog v-if="can('products.store')" />
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
        >
            <EmptyState
                v-if="products.data.length === 0"
                :icon="PackageOpen"
                title="Nenhum produto encontrado"
                description="Você ainda não tem nenhum produto cadastrado. Adicione um novo produto para começar."
            >
                <CreateProductDialog v-if="can('products.store')" />
            </EmptyState>

            <div v-else class="flex flex-1 flex-col overflow-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Preço</TableHead>
                            <TableHead
                                v-if="
                                    canAny([
                                        'products.update',
                                        'products.destroy',
                                    ])
                                "
                                class="w-[100px] text-right"
                                >Ações</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="product in products.data"
                            :key="product.id"
                        >
                            <TableCell class="font-medium">{{
                                product.name
                            }}</TableCell>
                            <TableCell>{{
                                formatCurrency(product.price)
                            }}</TableCell>
                            <TableCell
                                v-if="
                                    canAny([
                                        'products.update',
                                        'products.destroy',
                                    ])
                                "
                                class="text-right"
                            >
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <EditProductDialog
                                        v-if="can('products.update')"
                                        :product="product"
                                    />
                                    <DeleteProductDialog
                                        v-if="can('products.destroy')"
                                        :product="product"
                                    />
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <TablePagination :paginator="products" />
            </div>
        </div>
    </div>
</template>
