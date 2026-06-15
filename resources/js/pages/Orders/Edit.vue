<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import Heading from '@/components/Heading.vue';
import { index as ordersIndex } from '@/routes/orders';
import type { Customer, Product, Order } from '@/types';
import OrderForm from './OrderForm.vue';

const props = defineProps<{
    order: Order;
    customers: Customer[];
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
                title: 'Editar Pedido',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Editar Pedido #${order.id}`" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                :title="`Editar Pedido #${order.id}`"
                description="Altere os dados gerais do pedido ou modifique a lista de itens."
            />
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-hidden py-2">
            <OrderForm
                :action="OrderController.update.form(order.id)"
                :order="order"
                :customers="customers"
                :products="products"
                is-edit
            />
        </div>
    </div>
</template>
