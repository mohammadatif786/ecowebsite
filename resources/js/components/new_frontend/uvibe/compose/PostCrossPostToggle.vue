<template>
  <label class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3 cursor-pointer">
    <div>
      <p class="font-black text-sm">Also post on Vibes</p>
      <p class="text-xs text-slate-500">Appears in the main Vibes feed</p>
    </div>
    <input type="checkbox" :checked="modelValue" @change="$emit('update:modelValue', $event.target.checked)" class="h-5 w-5 accent-violet-600 shrink-0 cursor-pointer" />
  </label>
</template>

<script setup>
defineProps({ modelValue: { type: Boolean, default: true } });
defineEmits(['update:modelValue']);
</script>
