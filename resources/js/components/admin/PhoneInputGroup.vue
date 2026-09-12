<template>
    <div class="flex flex-col gap-1 w-full">
        <label v-if="label" :for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
        </label>

        <div class="flex w-full rounded-md shadow-sm">
            <!-- Phone Number Input -->
            <input :id="name" :name="name" type="tel" :placeholder="placeholder" :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                class="w-1/2 flex-1 block min-w-0 rounded-r-md border border-gray-300 px-3 py-2 text-sm text-gray-900 dark:text-white
                focus:border-blue-500 focus:ring-blue-500 " />
        </div>

        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    label: String,
    name: String,
    placeholder: {
        type: String,
        default: 'Enter phone number',
    },
    error: String,
});
</script>

