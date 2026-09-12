<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8 text-center">
      <div class="w-20 h-20 bg-linkup-lime/20 rounded-full flex items-center justify-center mx-auto mb-6 text-linkup-dark">
        <Store class="w-10 h-10" />
      </div>
      <h3 class="text-2xl font-bold mb-3">Merchant Portal</h3>
      <p class="text-slate-500 mb-8">Phase 2 Feature.</p>
      <button
        class="w-full py-4 bg-linkup-dark text-white rounded-xl font-bold hover:brightness-110 transition-colors"
        @click="$emit('close')"
      >
        Got it
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Store } from 'lucide-vue-next';

defineEmits<{
  (e: 'close'): void;
}>();
</script>

