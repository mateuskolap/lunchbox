<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
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
import { index as rolesIndex } from '@/routes/roles';
import type { Role, Permission, PaginatedResponse } from '@/types';
import CreateRoleDialog from './CreateRoleDialog.vue';
import DeleteRoleDialog from './DeleteRoleDialog.vue';
import EditRoleDialog from './EditRoleDialog.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Papéis',
                href: rolesIndex(),
            },
        ],
    },
});

defineProps<{
    roles: PaginatedResponse<Role>;
    permissions: Permission[];
}>();

const { can, canAny } = usePermissions();
</script>

<template>
    <Head title="Papéis" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Papéis"
                description="Gerencie os papéis (perfis de acesso) e permissões dos usuários do sistema."
            />
            <CreateRoleDialog
                v-if="can('roles.store')"
                :permissions="permissions"
            />
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
        >
            <EmptyState
                v-if="roles.data.length === 0"
                :icon="Shield"
                title="Nenhum papel encontrado"
                description="Você ainda não tem nenhum papel cadastrado. Adicione um novo papel para começar."
            >
                <CreateRoleDialog
                    v-if="can('roles.store')"
                    :permissions="permissions"
                />
            </EmptyState>

            <div v-else class="flex flex-1 flex-col overflow-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead
                                v-if="canAny(['roles.update', 'roles.destroy'])"
                                class="w-[100px] text-right"
                            >
                                Ações
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="role in roles.data" :key="role.id">
                            <TableCell class="font-medium">{{
                                role.name
                            }}</TableCell>
                            <TableCell
                                v-if="canAny(['roles.update', 'roles.destroy'])"
                                class="text-right"
                            >
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <EditRoleDialog
                                        v-if="can('roles.update')"
                                        :role="role"
                                        :permissions="permissions"
                                    />
                                    <DeleteRoleDialog
                                        v-if="can('roles.destroy')"
                                        :role="role"
                                    />
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <TablePagination :paginator="roles" />
            </div>
        </div>
    </div>
</template>
