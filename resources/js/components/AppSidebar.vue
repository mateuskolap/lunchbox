<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Contact,
    LayoutDashboard,
    Package,
    Shield,
    ShoppingCart,
    ShoppingBag,
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
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { usePermissions } from '@/composables/usePermissions';
import { dashboard } from '@/routes';
import { index as customersIndex } from '@/routes/customers';
import orders, { index as ordersIndex } from '@/routes/orders';
import { index as productsIndex } from '@/routes/products';
import { generalReport, salesByCustomer } from '@/routes/reports';
import { index as rolesIndex } from '@/routes/roles';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Pedidos do Dia',
        href: orders.today(),
        icon: ShoppingBag,
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

const { can } = usePermissions();
const { isCurrentUrl } = useCurrentUrl();
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

            <SidebarGroup v-if="can('reports.sales.index')" class="px-2 py-0">
                <SidebarGroupLabel>Relatórios</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(generalReport.url())"
                            tooltip="Relatório Geral"
                        >
                            <Link :href="generalReport.url()">
                                <LayoutDashboard class="size-4" />
                                <span>Relatório Geral</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(salesByCustomer.url())"
                            tooltip="Vendas por Cliente"
                        >
                            <Link :href="salesByCustomer.url()">
                                <BarChart3 class="size-4" />
                                <span>Vendas por Cliente</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
