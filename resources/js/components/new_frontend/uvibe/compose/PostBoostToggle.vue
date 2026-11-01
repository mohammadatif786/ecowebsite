<template>
  <label class="flex items-center justify-between gap-3 rounded-2xl bg-amber-50 px-3 py-2.5 cursor-pointer">
    <span class="font-bold text-sm">⚡ Allow Boosts (coin gifts)</span>
    <input type="checkbox" :checked="modelValue" @change="$emit('update:modelValue', $event.target.checked)" class="h-5 w-5 accent-amber-600 cursor-pointer" />
  </label>
</template>

<script setup>
defineProps({ modelValue: { type: Boolean, default: true } });
defineEmits(['update:modelValue']);
</script>
