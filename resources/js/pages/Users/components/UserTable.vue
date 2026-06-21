<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
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
import type { PaginatedResponse } from '@/types';
import type { User } from '@/types/auth';
import type { Role } from '@/types/role';
import CreateUserDialog from '../CreateUserDialog.vue';
import DeleteUserDialog from '../DeleteUserDialog.vue';
import EditUserDialog from '../EditUserDialog.vue';
import UserRolesDialog from '../UserRolesDialog.vue';

defineProps<{
    users: PaginatedResponse<User>;
    roles: Role[];
}>();

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const { can, canAny } = usePermissions();
</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <EmptyState
            v-if="users.data.length === 0"
            :icon="Users"
            title="Nenhum usuário encontrado"
            description="Você ainda não tem nenhum usuário cadastrado. Adicione um novo usuário para começar."
        >
            <CreateUserDialog v-if="can('users.store')" />
        </EmptyState>

        <div v-else class="flex flex-1 flex-col overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Nome</TableHead>
                        <TableHead>E-mail</TableHead>
                        <TableHead
                            v-if="
                                canAny([
                                    'users.update',
                                    'users.destroy',
                                    'roles.add',
                                ])
                            "
                            class="w-[120px] text-right"
                        >
                            Ações
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell class="font-medium">{{
                            user.name
                        }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell
                            v-if="
                                canAny([
                                    'users.update',
                                    'users.destroy',
                                    'roles.add',
                                ])
                            "
                            class="text-right"
                        >
                            <div class="flex items-center justify-end gap-1">
                                <UserRolesDialog
                                    v-if="can('roles.add')"
                                    :user="user"
                                    :roles="roles"
                                />
                                <EditUserDialog
                                    v-if="can('users.update')"
                                    :user="user"
                                />
                                <!-- Esconde botão de deletar se for o próprio usuário logado -->
                                <DeleteUserDialog
                                    v-if="
                                        can('users.destroy') &&
                                        user.id !== authUser.id
                                    "
                                    :user="user"
                                />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <TablePagination :paginator="users" />
        </div>
    </div>
</template>
