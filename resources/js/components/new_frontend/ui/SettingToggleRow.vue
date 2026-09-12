<template>
  <div class="p-4 flex items-center justify-between">
    <div class="min-w-0 pr-4">
      <p class="font-black text-slate-900 truncate">{{ label }}</p>
      <p v-if="desc" class="text-xs text-slate-500 font-semibold leading-relaxed mt-0.5">
        {{ desc }}
      </p>
    </div>
    <Toggle :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" />
  </div>
</template>

<script setup>
import Toggle from './Toggle.vue';
defineProps({
  label: String,
  desc: String,
  modelValue: Boolean
});
defineEmits(['update:modelValue']);
</script>
