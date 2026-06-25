<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
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
        <div class="flex items-center gap-1">
            <Button
                v-if="paginator.prev_page_url"
                variant="ghost"
                class="gap-1 px-2.5 sm:pr-2.5"
                as-child
            >
                <Link :href="paginator.prev_page_url">
                    <ChevronLeft />
                    <span class="hidden sm:block">Anterior</span>
                </Link>
            </Button>
            <Button
                v-else
                variant="ghost"
                class="gap-1 px-2.5 sm:pr-2.5"
                disabled
            >
                <ChevronLeft />
                <span class="hidden sm:block">Anterior</span>
            </Button>

            <Button
                v-if="paginator.next_page_url"
                variant="ghost"
                class="gap-1 px-2.5 sm:pr-2.5"
                as-child
            >
                <Link :href="paginator.next_page_url">
                    <span class="hidden sm:block">Próximo</span>
                    <ChevronRight />
                </Link>
            </Button>
            <Button
                v-else
                variant="ghost"
                class="gap-1 px-2.5 sm:pr-2.5"
                disabled
            >
                <span class="hidden sm:block">Próximo</span>
                <ChevronRight />
            </Button>
        </div>
    </div>
</template>

