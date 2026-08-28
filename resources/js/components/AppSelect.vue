<script setup lang="ts">
import { Check, ChevronDown } from 'lucide-vue-next';
import {
    PopoverRoot,
    PopoverTrigger,
    PopoverContent,
    PopoverPortal,
} from 'reka-ui';
import { ref, computed, nextTick } from 'vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Option = {
    value: string;
    label: string;
};

const props = withDefaults(
    defineProps<{
        options: Option[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        name?: string;
        required?: boolean;
        disabled?: boolean;
        searchable?: boolean;
        multi?: boolean;
    }>(),
    {
        placeholder: 'Selecione...',
        searchPlaceholder: 'Pesquisar...',
        emptyText: 'Nenhum resultado encontrado',
        searchable: false,
        multi: false,
    },
);

const model = defineModel<string | string[]>();

const isOpen = ref(false);
const searchQuery = ref('');
const searchInputRef = ref<any>(null);

const singleModel = computed<string | undefined>({
    get: () => (typeof model.value === 'string' ? model.value : undefined),
    set: (val) => {
        model.value = val;
    },
});

const selectedValues = computed<string[]>(() => {
    return Array.isArray(model.value) ? model.value : [];
});

const selectedOptions = computed(() => {
    return selectedValues.value.map((val) => {
        const found = props.options.find((opt) => opt.value === val);

        return found || { value: val, label: val };
    });
});

const selectedText = computed(() => {
    if (selectedOptions.value.length === 0) {
        return props.placeholder;
    }

    if (selectedOptions.value.length === 1) {
        return selectedOptions.value[0].label;
    }

    return `${selectedOptions.value.length} selecionados`;
});

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value) {
        return props.options;
    }

    const search = searchQuery.value.toLowerCase();

    return props.options.filter((opt) =>
        opt.label.toLowerCase().includes(search),
    );
});

const toggleValue = (val: string) => {
    const current = [...selectedValues.value];
    const index = current.indexOf(val);

    if (index >= 0) {
        current.splice(index, 1);
    } else {
        current.push(val);
    }

    model.value = current;
};

const onOpenChange = (open: boolean) => {
    if (!open) {
        searchQuery.value = '';
    } else if (props.searchable) {
        nextTick(() => {
            const input =
                searchInputRef.value?.$el?.querySelector('input') ||
                searchInputRef.value?.$el;

            if (input) {
                input.focus();
            }
        });
    }
};
</script>

<template>
    <!-- Single Select Mode -->
    <Select
        v-if="!multi"
        v-model="singleModel"
        :name="name"
        :required="required"
        :disabled="disabled"
        @update:open="onOpenChange"
    >
        <SelectTrigger class="h-9 w-full bg-transparent dark:bg-input/30">
            <SelectValue :placeholder="placeholder" />
        </SelectTrigger>
        <SelectContent>
            <!-- Search input inside Select content dropdown -->
            <div
                v-if="searchable"
                class="sticky -top-1 z-10 -mx-1 -mt-1 touch-none border-b border-sidebar-border bg-popover px-3 pt-2 pb-2"
                @keydown.stop
                @pointerdown.stop
                @mousedown.stop
                @touchstart.stop
                @touchend.stop
            >
                <Input
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="h-8 text-base focus:border-input focus:ring-0 focus-visible:border-input focus-visible:ring-0 md:text-xs"
                    ref="searchInputRef"
                />
            </div>
            <SelectItem
                v-for="opt in filteredOptions"
                :key="opt.value"
                :value="opt.value"
            >
                {{ opt.label }}
            </SelectItem>
            <div
                v-if="filteredOptions.length === 0"
                class="p-4 text-center text-xs text-muted-foreground"
            >
                {{ emptyText }}
            </div>
        </SelectContent>
    </Select>

    <!-- Multi Select Mode -->
    <PopoverRoot v-else v-model:open="isOpen" @update:open="onOpenChange">
        <PopoverTrigger
            :disabled="disabled"
            class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 data-[placeholder]:text-muted-foreground dark:bg-input/30 dark:hover:bg-input/50 [&_svg:not([class*='text-'])]:text-muted-foreground"
        >
            <span
                class="truncate text-sm"
                :class="{
                    'text-muted-foreground': selectedOptions.length === 0,
                    'text-foreground': selectedOptions.length > 0,
                }"
            >
                {{ selectedText }}
            </span>
            <ChevronDown class="size-4 shrink-0 opacity-50" />
        </PopoverTrigger>

        <PopoverPortal>
            <PopoverContent
                class="relative z-50 max-h-60 w-(--reka-popover-trigger-width) min-w-[8rem] overflow-x-hidden overflow-y-auto rounded-md border bg-popover p-1 text-popover-foreground shadow-md data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
                align="start"
                :side-offset="4"
            >
                <div
                    v-if="searchable"
                    class="sticky -top-1 z-10 -mx-1 -mt-1 touch-none border-b border-sidebar-border bg-popover px-3 pt-2 pb-2"
                    @keydown.stop
                    @pointerdown.stop
                    @mousedown.stop
                    @touchstart.stop
                    @touchend.stop
                >
                    <Input
                        v-model="searchQuery"
                        :placeholder="searchPlaceholder"
                        class="h-8 text-base focus:border-input focus:ring-0 focus-visible:border-input focus-visible:ring-0 md:text-xs"
                        ref="searchInputRef"
                    />
                </div>

                <div
                    v-for="opt in filteredOptions"
                    :key="opt.value"
                    class="relative flex w-full cursor-pointer items-center justify-between gap-2 rounded-sm py-1.5 pr-2 pl-2 text-sm outline-hidden select-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
                    @click="toggleValue(opt.value)"
                >
                    <span>{{ opt.label }}</span>
                    <Check
                        v-if="selectedValues.includes(opt.value)"
                        class="size-4 shrink-0 text-primary"
                    />
                </div>

                <div
                    v-if="filteredOptions.length === 0"
                    class="p-4 text-center text-xs text-muted-foreground"
                >
                    {{ emptyText }}
                </div>
            </PopoverContent>
        </PopoverPortal>
    </PopoverRoot>

    <!-- Hidden Inputs for Form submission in multi mode -->
    <template v-if="multi && name && selectedValues.length > 0">
        <input
            v-for="val in selectedValues"
            :key="val"
            type="hidden"
            :name="`${name}[]`"
            :value="val"
        />
    </template>
</template>
