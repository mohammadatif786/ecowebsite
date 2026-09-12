<script setup lang="ts">
import { computed, ref } from 'vue';
// import { router, usePage } from "@inertiajs/vue3";
import { Button } from '@/components/admin/ui/button';
import { Eye, Pencil, Tag, Trash2 } from 'lucide-vue-next';

type Column<T> = {
    accessorKey?: string;
    header: string;
    cell: (row: T) => string | number;
    sortable?: boolean;
    searchable?: boolean;
    width?: string;
    classCell?: string;
    classHead?: string;
};

type Action<T> = {
    label: string;
    icon?: string;
    type?: 'edit' | 'view' | 'ticket' | 'delete'; // Added type property
    onClick: (row: T) => void;
    permission?: string;
    classCell?: string;
    classHead?: string;
};

const props = withDefaults(
    defineProps<{
        columns: Column<any>[];
        rows: any[];
        pagination?: boolean;
        actions?: Action<any>[];
        actionsClass?: string;
        selectable?: boolean;
        title?: string;
        addBorder?: boolean;
    }>(),
    {
        pagination: true,
        selectable: false,
        title: '',
        addBorder: true,
    },
);

const selectedRows = ref<any[]>([]);

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedRows.value = [];
    } else {
        selectedRows.value = [...props.rows];
    }
};

const toggleRow = (row: any) => {
    const index = selectedRows.value.findIndex((r) => r.id === row.id);
    if (index !== -1) {
        selectedRows.value.splice(index, 1);
    } else {
        selectedRows.value.push(row);
    }
};

const allSelected = computed(() => {
    return props.rows.length && selectedRows.value.length === props.rows.length;
});
</script>
<template>
    <div :class="{ 'rounded-xl border p-4 shadow-sm': props.addBorder }">
        <div class="flex items-center justify-between" :class="{ 'mb-4': addBorder }">
            <h2 class="text-xl font-semibold" v-if="title">{{ title }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto text-left text-sm">
                <thead class="bg-gray-100 dark:bg-gray-600">
                    <tr>
                        <!-- <th v-if="selectable" class="px-4 py-2">
                            <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                        </th> -->
                        <th
                            v-for="(col, index) in columns"
                            :key="index"
                            class="px-4 py-2 font-semibold"
                            :class="col.classHead"
                            :style="{ width: col.width || 'auto' }"
                        >
                            {{ col.header }}
                        </th>
                        <th v-if="actions?.length" class="px-4 py-2" :class="actionsClass">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, rowIndex) in rows"
                        :key="rowIndex"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        :class="{ 'bg-gray-50': selectedRows.includes(row) }"
                    >
                        <!-- <td v-if="selectable" class="px-4 py-2">
                            <input type="checkbox" :checked="selectedRows.includes(row)" @change="toggleRow(row)" />
                        </td> -->
                        <td v-for="(col, colIndex) in columns" :key="colIndex" class="px-4 py-2" :class="col.classCell">
                            <!-- If it's a string or number, render as text or HTML -->
                            <span v-if="typeof col.cell(row) === 'string' || typeof col.cell(row) === 'number'" v-html="col.cell(row)" />
                            <!-- If it's a Vue component, render it -->
                            <component v-else :is="col.cell(row ?? 'null')" />
                        </td>
                        <td v-if="actions?.length" class="px-4 py-2" :class="actionsClass">
                            <div class="flex gap-2">
                                <Button
                                    v-for="(action, i) in actions"
                                    :key="i"
                                    variant="ghost"
                                    size="sm"
                                    @click="() => action.onClick(row)"
                                    class="flex cursor-pointer items-center gap-1"
                                >
                                    <component :is="action.type === 'edit' ? Pencil : null" class="h-4 w-4 text-blue-700" />
                                    <component :is="action.type === 'view' ? Eye : null" class="h-4 w-4 text-green-700" />
                                    <component :is="action.type === 'ticket' ? Tag : null" class="h-4 w-4" />
                                    <component :is="action.type === 'delete' ? Trash2 : null" class="h-4 w-4 text-red-700" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!rows?.length" class="py-6 text-center text-gray-500">No data found.</div>
        </div>
    </div>
</template>
