<template>
    <div class="flex flex-row gap-2">
        <label v-for="option in options" :key="option.value"
            class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-50">
            <input type="radio" :value="option.value" :checked="option.value === modelValue"
                @change="$emit('update:modelValue', option.value)" :name="name" class="accent-blue-600 w-4 h-4" />
            <span>{{ option.label }}</span>
        </label>
    </div>
</template>

<script setup>
defineProps({
    modelValue: [String, Number, Boolean],
    options: {
        type: Array,
        required: true,
    },
    name: {
        type: String,
        default: 'radio-group',
    },
})

defineEmits(['update:modelValue'])
</script>
