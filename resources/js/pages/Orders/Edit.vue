<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
import { index as ordersIndex } from '@/routes/orders';
import type { Customer, Product, Order } from '@/types';
import OrderForm from './OrderForm.vue';

const props = defineProps<{
    order: Order;
    customers: Customer[];
    products: Product[];
    list_url?: string;
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
        <div class="flex items-start gap-3 sm:items-center">
            <Button
                variant="outline"
                size="icon"
                as-child
                class="mt-1 shrink-0 sm:mt-0"
            >
                <Link :href="list_url || ordersIndex()">
                    <ArrowLeft class="size-4" />
                    <span class="sr-only">Voltar</span>
                </Link>
            </Button>
            <Heading
                :title="`Editar Pedido #${order.id}`"
                description="Altere os dados gerais do pedido ou modifique a lista de itens."
                class="p-0"
            />
        </div>

        <div class="flex-1 overflow-x-hidden overflow-y-auto py-2">
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
