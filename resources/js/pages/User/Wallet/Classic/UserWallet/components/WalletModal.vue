<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold">Wallet Top Up</h3>
        <button @click="$emit('close')">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="flex gap-2 p-1 bg-slate-100 rounded-xl mb-8">
        <button class="flex-1 py-3 text-sm font-bold rounded-lg transition-all"
          :class="activeTab === 'cash' ? 'bg-white shadow-sm' : 'text-slate-500'" @click="activeTab = 'cash'">
          Top Up Cash
        </button>
        <button class="flex-1 py-3 text-sm font-bold rounded-lg transition-all"
          :class="activeTab === 'coins' ? 'bg-white shadow-sm' : 'text-slate-500'" @click="activeTab = 'coins'">
          Buy Coins
        </button>
      </div>

      <div v-if="activeTab === 'cash'">
        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Amount (USD)</label>
        <input v-model="cashAmount" type="number"
          class="w-full p-4 bg-slate-50 rounded-xl mb-8 text-3xl font-bold border border-transparent focus:bg-white focus:border-linkup-blue outline-none"
          placeholder="0.00" />
        <button @click="submitCash"
          class="w-full py-4 bg-linkup-dark text-white rounded-xl font-bold text-lg hover:brightness-110 transition-all disabled:opacity-50"
          :disabled="!cashAmount || isSubmitting">
          {{ isSubmitting ? 'Processing...' : 'Add Funds' }}
        </button>
      </div>

      <div v-else>
        <div class="grid grid-cols-2 gap-3 mt-4">
          <button v-for="p in coinPacks" :key="p.c" @click="selectPack(p)"
            class="p-4 border border-slate-200 rounded-xl hover:border-linkup-blue hover:bg-blue-50 transition-colors bg-white text-left"
            :class="{ 'border-linkup-blue bg-blue-50': selectedCoinPack?.c === p.c }">
            <div class="font-bold text-lg text-slate-900">{{ p.c }} LUC</div>
            <div class="text-xs text-slate-500">${{ p.p }}</div>
          </button>
        </div>
        <button v-if="selectedCoinPack"
          class="w-full py-4 mt-6 bg-linkup-dark text-white rounded-xl font-bold text-lg hover:brightness-110 transition-all disabled:opacity-50"
          @click="submitCoins" :disabled="isSubmitting">
          {{ isSubmitting ? 'Processing...' : `Buy ${selectedCoinPack?.c} Coins` }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

defineEmits<{
  (e: 'close'): void;
  (e: 'submit'): void;
  (e: 'select-pack', pack: { c: number; p: number }): void;
}>();

const props = defineProps<{ initialTab?: 'cash' | 'coins' }>();
const activeTab = ref<'cash' | 'coins'>(props.initialTab || 'cash');
const cashAmount = ref<number | null>(null);
const selectedCoinPack = ref<{ c: number; p: number } | null>(null);
const isSubmitting = ref(false);

const coinPacks = [
  { c: 100, p: 1 },
  { c: 550, p: 5 },
  { c: 1200, p: 10 },
  { c: 3000, p: 25 },
];

function selectPack(pack: { c: number; p: number }) {
  selectedCoinPack.value = pack;
}

function submitCash() {
  if (!cashAmount.value || cashAmount.value < 1) return;
  isSubmitting.value = true;
  router.post(route('frontend.wallet.checkout'), {
    selectedAmount: cashAmount.value
  }, {
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
}

function submitCoins() {
  if (!selectedCoinPack.value) return;
  isSubmitting.value = true;
  router.post(route('frontend.wallet.coin.checkout'), {
    selectedCoin: selectedCoinPack.value.p,
    coinCount: selectedCoinPack.value.c
  }, {
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
}
</script>
