<template>
  <div class="p-4 flex items-center justify-between hover:bg-slate-50/50 transition cursor-pointer">
    <p class="font-black text-slate-900">{{ label }}</p>
    <div class="flex items-center gap-1.5 text-slate-400">
      <span v-if="value" class="text-sm font-bold">{{ value }}</span>
      <i data-lucide="chevron-right" class="w-4 h-4"></i>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: String
});
</script>
