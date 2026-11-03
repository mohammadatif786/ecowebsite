<template>
  <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" type="number" min="0" step="1" inputmode="decimal"
         placeholder="Ticket / entry price USD (optional)"
         class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none" />
</template>

<script setup>
defineProps({ modelValue: { type: [String, Number], default: '' } });
defineEmits(['update:modelValue']);
</script>
