<template>
    <div class="flex flex-col gap-1 w-full">
        <label v-if="label" :for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
        </label>
        <div class="relative flex rounded-md shadow-sm">
            <span v-if="prepend"
                class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                {{ prepend }}
            </span>
            <input :id="name" :name="name" :type="type" :placeholder="placeholder" :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                class="flex-1 block w-full min-w-0 rounded-none border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-white dark:border-gray-600"
                :class="{
                    'rounded-l-md': !prepend,
                    'rounded-r-md': !append,
                }" />
            <span v-if="append"
                class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                {{ append }}
            </span>
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: String,
    label: String,
    name: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: String,
    prepend: String,
    append: String,
    error: String,
});

defineEmits(['update:modelValue']);
</script>

