<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <!-- Asset List View -->
    <div v-if="!selectedAsset" class="p-8">
      <div class="flex justify-between mb-6">
        <h3 class="text-xl font-bold">Invest</h3>
        <button @click="$emit('close')"><X class="w-5 h-5" /></button>
      </div>

      <div class="space-y-2 mb-4 max-h-[300px] overflow-y-auto">
        <button
          v-for="c in assets"
          :key="c.sym"
          @click="selectAsset(c)"
          class="w-full flex items-center justify-between p-4 border border-slate-200 rounded-xl hover:bg-slate-50 group bg-white transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600">
              {{ c.sym[0] }}
            </div>
            <div class="text-left">
              <div class="font-bold text-slate-800">{{ c.name }}</div>
              <div class="text-xs text-slate-400">${{ c.price }}</div>
            </div>
          </div>
          <div class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-xs font-bold">Buy</div>
        </button>
      </div>
    </div>

    <!-- Buy Form View -->
    <div v-else class="p-8">
      <div class="flex items-center gap-3 mb-6">
        <button @click="selectedAsset = null" class="p-2 hover:bg-slate-100 rounded-full">
          <ArrowLeft class="w-5 h-5" />
        </button>
        <div>
          <h3 class="text-xl font-bold">Buy {{ selectedAsset.sym }}</h3>
          <p class="text-xs text-slate-400">Price: ${{ selectedAsset.price }}</p>
        </div>
      </div>

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Amount</label>
      <input
        v-model="amount"
        type="number"
        class="w-full p-4 bg-slate-50 rounded-xl mb-2 text-3xl font-bold outline-none border border-transparent focus:bg-white focus:border-linkup-blue"
        placeholder="0.00"
      />
      <p class="text-sm font-mono text-slate-500 mb-8 text-right">
        ≈ {{ estimatedCrypto }} {{ selectedAsset.sym }}
      </p>

      <button
        @click="confirmBuy"
        class="w-full py-4 bg-linkup-blue text-white rounded-xl font-bold text-lg hover:brightness-105 transition-colors"
      >
        Confirm
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { X, ArrowLeft } from 'lucide-vue-next';

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'select', asset: { sym: string; name: string; price: number }): void;
}>();

const assets = [
  { sym: 'BTC', name: 'Bitcoin', price: 96420 },
  { sym: 'ETH', name: 'Ethereum', price: 3650 },
  { sym: 'SOL', name: 'Solana', price: 240 },
  { sym: 'USDC', name: 'USD Coin', price: 1 },
];

const selectedAsset = ref<{ sym: string; name: string; price: number } | null>(null);
const amount = ref<number | ''>('');

const estimatedCrypto = computed(() => {
  if (!amount.value || !selectedAsset.value) return '0.000000';
  const val = parseFloat(amount.value.toString()) || 0;
  // Assuming USD rate is 1 for simplicity as per example.php FX_RATES of USD
  const est = val / selectedAsset.value.price;
  return est.toFixed(6);
});

function selectAsset(asset: typeof assets[0]) {
  selectedAsset.value = asset;
}

function confirmBuy() {
  if (!amount.value || !selectedAsset.value) return;
  alert(`Bought ${selectedAsset.value.sym} worth $${amount.value}`);
  emit('close');
}
</script>

