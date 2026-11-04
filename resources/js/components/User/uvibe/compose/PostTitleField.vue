<template>
  <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" maxlength="120"
         placeholder="Title (e.g. Campus Culture Night)"
         class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none" />
</template>

<script setup>
defineProps({ modelValue: { type: String, default: '' } });
defineEmits(['update:modelValue']);
</script>
