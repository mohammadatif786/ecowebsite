<template>
  <div class="space-y-1.5">
    <p class="text-[11px] font-black text-slate-400 uppercase">Type</p>
    <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none bg-white">
      <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.label }}</option>
    </select>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: 'campus-life' },
  categories: { type: Array, default: () => [] }, // [{ id, label }] from the backend
});
defineEmits(['update:modelValue']);
</script>
