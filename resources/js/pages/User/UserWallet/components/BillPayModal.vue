<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden min-h-[500px] flex flex-col">
    <!-- List View -->
    <div v-if="!selectedBiller" class="p-8 flex flex-col h-full">
      <div class="flex justify-between mb-6 flex-shrink-0">
        <h3 class="text-2xl font-bold">Pay Bills</h3>
        <button @click="$emit('close')"><X class="w-5 h-5" /></button>
      </div>

      <div class="overflow-y-auto pr-2 flex-grow">
        <div class="mb-6">
          <div class="text-xs font-bold text-slate-400 uppercase mb-3">Utilities</div>
          <div class="grid grid-cols-3 gap-3">
            <button
              v-for="b in utilities"
              :key="b.name"
              @click="selectBiller(b.name)"
              class="flex flex-col items-center justify-center p-4 border rounded-2xl hover:border-linkup-blue h-24 transition-colors bg-white"
            >
              <component :is="getIcon(b.icon)" class="w-6 h-6 mb-2 text-slate-400" />
              <div class="text-[10px] font-bold text-center">{{ b.name }}</div>
            </button>
          </div>
        </div>

        <div class="mb-6">
          <div class="text-xs font-bold text-slate-400 uppercase mb-3">Mobile</div>
          <div class="grid grid-cols-3 gap-3">
            <button
              v-for="b in mobile"
              :key="b.name"
              @click="selectBiller(b.name)"
              class="flex flex-col items-center justify-center p-4 border rounded-2xl hover:border-linkup-blue h-24 transition-colors bg-white"
            >
              <component :is="getIcon(b.icon)" class="w-6 h-6 mb-2 text-slate-400" />
              <div class="text-[10px] font-bold text-center">{{ b.name }}</div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Form View -->
    <div v-else class="p-8">
      <div class="flex items-center gap-3 mb-6">
        <button @click="selectedBiller = null" class="p-2 hover:bg-slate-100 rounded-full">
          <ArrowLeft class="w-5 h-5" />
        </button>
        <h3 class="text-xl font-bold">Pay {{ selectedBiller }}</h3>
      </div>

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Account #</label>
      <input
        v-model="accountNumber"
        type="text"
        class="w-full p-4 bg-slate-50 rounded-xl mb-4 text-lg outline-none border border-transparent focus:bg-white focus:border-linkup-blue"
        placeholder="000-000"
      />

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Amount</label>
      <input
        v-model="amount"
        type="number"
        class="w-full p-4 bg-slate-50 rounded-xl mb-8 text-3xl font-bold outline-none border border-transparent focus:bg-white focus:border-linkup-blue"
        placeholder="0.00"
      />

      <button
        @click="submitPayment"
        class="w-full py-4 bg-linkup-blue text-white rounded-xl font-bold text-lg hover:brightness-105 transition-colors"
      >
        Confirm Payment
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { X, ArrowLeft, Zap, Droplet, Smartphone, Wifi, Signal } from 'lucide-vue-next';

defineProps<{
  utilities: Array<{ name: string; icon: string }>;
  mobile: Array<{ name: string; icon: string }>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'select', name: string): void;
}>();

const selectedBiller = ref<string | null>(null);
const accountNumber = ref('');
const amount = ref('');

function selectBiller(name: string) {
  selectedBiller.value = name;
}

function submitPayment() {
  alert(`Paid ${selectedBiller.value} - Account: ${accountNumber.value}, Amount: ${amount.value}`);
  emit('close');
}

function getIcon(iconName: string) {
  switch (iconName) {
    case 'zap': return Zap;
    case 'droplet': return Droplet;
    case 'smartphone': return Smartphone;
    case 'wifi': return Wifi;
    case 'signal': return Signal;
    default: return Zap;
  }
}
</script>

