<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Contact,
    LayoutGrid,
    Package,
    Shield,
    ShoppingCart,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as customersIndex } from '@/routes/customers';
import orders, { index as ordersIndex } from '@/routes/orders';
import { index as productsIndex } from '@/routes/products';
import { index as rolesIndex } from '@/routes/roles';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: orders.today(),
        icon: LayoutGrid,
    },
    {
        title: 'Pedidos',
        href: ordersIndex(),
        icon: ShoppingCart,
        permission: 'orders.index',
    },
    {
        title: 'Produtos',
        href: productsIndex(),
        icon: Package,
        permission: 'products.index',
    },
    {
        title: 'Clientes',
        href: customersIndex(),
        icon: Contact,
        permission: 'customers.index',
    },
    {
        title: 'Usuários',
        href: usersIndex(),
        icon: Users,
        permission: 'users.index',
    },
    {
        title: 'Papéis',
        href: rolesIndex(),
        icon: Shield,
        permission: 'roles.index',
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
