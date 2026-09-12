<!-- components/BaseInput.vue -->
<template>
    <div class="mb-4">
        <!-- <label :for="name" class="block mb-1 font-medium text-gray-700">{{ label }}</label> -->
        <textarea :rows="rows ? rows : 1" :id="name" :type="type" :placeholder="placeholder" :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="pl-0 border-0 border-b border-gray-400 focus:border-b-1 dark:placeholder:text-gray-500 dark:text-black focus:border-gray-400 focus:ring-0 focus:outline-none w-full  bg-transparent"
            :class="{ 'border-red-500': error }"></textarea>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
<script setup lang="ts">
    defineProps<{
        name: string
        label: string
        type?: string
        modelValue: any
        placeholder?: string
        rows?: string
        error?: string
    }>()

    defineEmits(['update:modelValue'])
</script>
