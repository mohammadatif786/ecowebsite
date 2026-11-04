<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8">
      <div class="text-center mb-6">
        <h3 class="text-2xl font-bold mb-1">POS / Receive</h3>
        <p class="text-slate-400 text-sm">Scan to pay {{ tag }}</p>
      </div>

      <div class="flex justify-center mb-6">
        <div class="bg-white p-6 rounded-3xl shadow-lg border border-slate-100">
           <!-- QR Code Placeholder or Logic -->
           <div id="qrcode" ref="qrContainer"></div> 
           <!-- In a real app we'd use a QR library component here. For now keeping structure container mainly -->
           <!-- If previous implementation had a mock grid, I will replicate or try to use a placeholder image if library not available? 
                The previous one used a div grid. example.php uses qrcodejs. 
                I'll stick to a visual placeholder that looks like a QR code or the grid from previous if no library available easily. 
                Wait, previous had a grid. I'll use that grid but inside the styled container of example.php -->
            <div class="w-[160px] h-[160px] bg-slate-900 grid grid-cols-5 grid-rows-5 gap-1 p-2 mx-auto">
                <div v-for="i in 25" :key="i" class="bg-white rounded-sm opacity-50"></div>
            </div>
        </div>
      </div>

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2 text-center">Amount to Request (USD)</label>
      <input
        v-model="amount"
        type="number"
        class="w-full p-4 bg-slate-50 rounded-xl font-bold text-center text-xl outline-none border border-transparent focus:bg-white focus:border-linkup-blue"
        placeholder="0.00"
      />

      <button
        class="w-full py-3 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 text-slate-600 mt-4"
        @click="$emit('close')"
      >
        Close
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

defineProps<{ tag: string }>();

defineEmits<{
  (e: 'close'): void;
}>();

const amount = ref('');
</script>

