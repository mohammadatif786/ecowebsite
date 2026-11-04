<template>
    <input
        type="text"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        autocomplete="given-name"
        class="w-full rounded-lg border-2 border-blue-300 px-2 py-1 focus:outline-none"
        :class="{ 'border-red-400': error }"
        :placeholder="placeholder"
    />
</template>
<script setup lang="ts">
const props = defineProps<{
    modelValue: string;
    error?: string;
    placeholder?: string;
}>();
</script>
