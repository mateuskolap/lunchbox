<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
    Pagination,
    PaginationContent,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import type { PaginatedResponse } from '@/types';

defineProps<{
    paginator: PaginatedResponse<unknown>;
}>();
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="mt-auto flex items-center justify-between border-t border-border p-4"
    >
        <p class="text-sm text-muted-foreground">
            Mostrando
            <span class="font-medium">{{ paginator.data.length }}</span> de
            <span class="font-medium">{{ paginator.total }}</span> resultados
        </p>
        <Pagination
            :total="paginator.total"
            :items-per-page="paginator.per_page"
            :sibling-count="1"
            show-edges
            :default-page="paginator.current_page"
            class="mx-0 w-auto justify-end"
        >
            <PaginationContent class="flex items-center gap-1">
                <Link
                    v-if="paginator.prev_page_url"
                    :href="paginator.prev_page_url"
                >
                    <PaginationPrevious>
                        <ChevronLeft />
                        <span class="hidden sm:block">Anterior</span>
                    </PaginationPrevious>
                </Link>
                <PaginationPrevious v-else disabled>
                    <ChevronLeft />
                    <span class="hidden sm:block">Anterior</span>
                </PaginationPrevious>

                <Link
                    v-if="paginator.next_page_url"
                    :href="paginator.next_page_url"
                >
                    <PaginationNext>
                        <span class="hidden sm:block">Próximo</span>
                        <ChevronRight />
                    </PaginationNext>
                </Link>
                <PaginationNext v-else disabled>
                    <span class="hidden sm:block">Próximo</span>
                    <ChevronRight />
                </PaginationNext>
            </PaginationContent>
        </Pagination>
    </div>
</template>
