<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { usePermissions } from '@/composables/usePermissions';
import { index as productsIndex } from '@/routes/products';
import type { Product, PaginatedResponse } from '@/types';
import ProductTable from './components/ProductTable.vue';
import CreateProductDialog from './CreateProductDialog.vue';

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

const { can } = usePermissions();
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

        <ProductTable :products="products" />
    </div>
</template>
