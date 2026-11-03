<template>
  <button type="button" @click="$emit('update:modelValue', !modelValue)"
          :class="['w-full rounded-2xl border-2 border-dashed p-3 flex items-center justify-center gap-2 font-black text-sm text-emerald-700 transition',
                   modelValue ? 'border-lkblue bg-blue-50' : 'border-emerald-200 hover:bg-emerald-50']">
    <Type class="w-4 h-4" />{{ modelValue ? 'Remove Text Card' : 'Add Text Card instead of a photo' }}
  </button>
</template>

<script setup>
import { Type } from 'lucide-vue-next';

defineProps({ modelValue: { type: Boolean, default: false } });
defineEmits(['update:modelValue']);
</script>
