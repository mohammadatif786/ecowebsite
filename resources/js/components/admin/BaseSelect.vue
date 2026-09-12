<!-- components/BaseSelect.vue -->
<template>
    <div class="mb-4 w-full">
        <!-- <label :for="name" class="block mb-1 font-medium text-gray-700">{{ label }}</label> -->
        <select :id="name" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)"
            class="pl-1 text-gray-500 border-0 border-b border-gray-400 focus:border-b-1 focus:border-gray-400 focus:ring-0 focus:outline-none w-full  bg-transparent"
            :class="{ 'border-red-500': error }">
            <option disabled value="">{{ (placeholder ? placeholder : 'Select Option') }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
defineProps<{
  name: string
  label: string
  modelValue: any
  options: { label: string; value: any }[]
  error?: string,
  placeholder?: string
}>()

defineEmits(['update:modelValue'])
</script>
