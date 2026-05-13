<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginatedResponse } from '@/types';
import {
    Pagination,
    PaginationContent,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

defineProps<{
    paginator: PaginatedResponse<unknown>;
}>();
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="mt-auto border-t border-border p-4 flex items-center justify-between"
    >
        <p class="text-sm text-muted-foreground">
            Mostrando <span class="font-medium">{{ paginator.data.length }}</span> de <span class="font-medium">{{ paginator.total }}</span> resultados
        </p>
        <Pagination
            :total="paginator.total"
            :items-per-page="paginator.per_page"
            :sibling-count="1"
            show-edges
            :default-page="paginator.current_page"
            class="w-auto mx-0 justify-end"
        >
            <PaginationContent class="flex items-center gap-1">
                <Link v-if="paginator.prev_page_url" :href="paginator.prev_page_url">
                    <PaginationPrevious />
                </Link>
                <PaginationPrevious v-else disabled />
                
                <Link v-if="paginator.next_page_url" :href="paginator.next_page_url">
                    <PaginationNext />
                </Link>
                <PaginationNext v-else disabled />
            </PaginationContent>
        </Pagination>
    </div>
</template>
