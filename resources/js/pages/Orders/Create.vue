<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import Heading from '@/components/Heading.vue';
import { index as ordersIndex } from '@/routes/orders';
import type { Customer, Product } from '@/types';
import OrderForm from './OrderForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pedidos',
                href: ordersIndex(),
            },
            {
                title: 'Novo Pedido',
                href: '#',
            },
        ],
    },
});

defineProps<{
    customers: Customer[];
    products: Product[];
}>();
</script>

<template>
    <Head title="Novo Pedido" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Novo Pedido"
                description="Crie um novo pedido adicionando clientes, produtos e quantidades."
            />
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-hidden py-2">
            <OrderForm
                :action="OrderController.store.form()"
                :customers="customers"
                :products="products"
            />
        </div>
    </div>
</template>
