<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
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
    list_url?: string;
}>();
</script>

<template>
    <Head title="Novo Pedido" />

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
                title="Novo Pedido"
                description="Crie um novo pedido adicionando clientes, produtos e quantidades."
                class="p-0"
            />
        </div>

        <div class="flex-1 overflow-x-hidden overflow-y-auto py-2">
            <OrderForm
                :action="OrderController.store.form()"
                :customers="customers"
                :products="products"
            />
        </div>
    </div>
</template>
