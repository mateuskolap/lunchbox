<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CreateProductDialog from './CreateProductDialog.vue';
import EditProductDialog from './EditProductDialog.vue';
import DeleteProductDialog from './DeleteProductDialog.vue';
import Heading from '@/components/Heading.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationContent,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { PackageOpen } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import type { Product } from '@/types/product';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Produtos',
                href: '/produtos',
            },
        ],
    },
});

const props = defineProps<{
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        total: number;
        per_page: number;
    };
}>();

const formatPrice = (price: string | number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(price));
};
</script>

<template>
    <Head title="Produtos" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Produtos"
                description="Gerencie os produtos do sistema."
            />
            <CreateProductDialog />
        </div>

        <div class="flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card overflow-hidden flex flex-col">
            <div v-if="products.data.length === 0" class="flex flex-col items-center justify-center p-8 flex-1 text-center">
                <div class="flex size-12 items-center justify-center rounded-full bg-muted/50 mb-4">
                    <PackageOpen class="size-6 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-medium">Nenhum produto encontrado</h3>
                <p class="text-sm text-muted-foreground max-w-sm mt-1 mb-4">
                    Você ainda não tem nenhum produto cadastrado. Adicione um novo produto para começar.
                </p>
                <CreateProductDialog />
            </div>

            <div v-else class="flex flex-col flex-1 overflow-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Preço</TableHead>
                            <TableHead class="w-[100px] text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="product in products.data" :key="product.id">
                            <TableCell class="font-medium">{{ product.name }}</TableCell>
                            <TableCell>{{ formatPrice(product.price) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <EditProductDialog :product="product" />
                                    <DeleteProductDialog :product="product" />
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="products.last_page > 1" class="mt-auto border-t border-border p-4 flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Mostrando <span class="font-medium">{{ products.data.length }}</span> de <span class="font-medium">{{ products.total }}</span> resultados
                    </p>
                    <Pagination
                        :total="products.total"
                        :items-per-page="products.per_page"
                        :sibling-count="1"
                        show-edges
                        :default-page="products.current_page"
                    >
                        <PaginationContent class="flex items-center gap-1">
                            <Link v-if="products.prev_page_url" :href="products.prev_page_url">
                                <PaginationPrevious />
                            </Link>
                            <PaginationPrevious v-else disabled />
                            
                            <Link v-if="products.next_page_url" :href="products.next_page_url">
                                <PaginationNext />
                            </Link>
                            <PaginationNext v-else disabled />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </div>
</template>
