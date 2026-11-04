<template>
  <button @click="$emit('click')" class="group bg-white p-4 rounded-[1.5rem] glow-border text-left h-32 flex flex-col justify-between">
    <div
      class="w-10 h-10 rounded-full bg-blue-50 text-linkup-blue flex items-center justify-center group-hover:scale-110 transition-transform"
      v-if="icon === 'send'"
    >
      <Send class="w-5 h-5" />
    </div>
    <div
      class="w-10 h-10 rounded-full bg-lime-50 text-lime-600 flex items-center justify-center group-hover:scale-110 transition-transform"
      v-else-if="icon === 'hand-coins'"
    >
      <HandCoins class="w-5 h-5" />
    </div>
    <div
      class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform"
      v-else-if="icon === 'receipt'"
    >
      <Receipt class="w-5 h-5" />
    </div>
    <div
      class="w-10 h-10 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center group-hover:scale-110 transition-transform"
      v-else-if="icon === 'download'"
    >
      <Download class="w-5 h-5" />
    </div>
    <div
      class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-800 group-hover:text-white transition-colors"
      v-else
    >
      <StoreIcon class="w-5 h-5" />
    </div>
    <div class="font-bold text-slate-700">{{ label }}</div>
  </button>
</template>

<script setup lang="ts">
import { Send, HandCoins, Receipt, Store as StoreIcon, Download } from 'lucide-vue-next';

defineProps<{
  label: string;
  icon: 'send' | 'hand-coins' | 'receipt' | 'store' | 'download';
}>();

defineEmits<{
  (e: 'click'): void;
}>();
</script>

