<template>
  <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl overflow-hidden">
    <div class="flex items-start justify-between px-6 pt-6">
      <h3 class="text-lg font-bold text-slate-900">Wallet</h3>
      <button class="p-2 rounded-full text-black hover:bg-slate-100" @click="$emit('close')">
        <X class="w-5 h-5" />
      </button>
    </div>

    <div class="px-6 pb-6">
      <div class="flex gap-2 bg-slate-100 rounded-2xl p-1 mt-4">
        <button
          class="flex-1 py-3 rounded-xl text-sm font-bold"
          :class="activeTab === 'cash' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'"
          @click="activeTab = 'cash'"
        >
          Top Up Cash
        </button>
        <button
          class="flex-1 py-3 rounded-xl text-sm font-bold"
          :class="activeTab === 'coins' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'"
          @click="activeTab = 'coins'"
        >
          Buy Coins
        </button>
      </div>

      <div v-if="activeTab === 'cash'" class="mt-6 space-y-4">
        <div class="space-y-2">
          <label class="text-[11px] font-bold uppercase text-slate-500">Amount (USD)</label>
          <input
            v-model="cashAmount"
            type="number"
            placeholder="0.00"
            min="1"
            class="w-full h-16 rounded-2xl bg-slate-50 border border-slate-100 px-4 text-3xl font-bold text-slate-500 focus:outline-none focus:ring-2 focus:ring-linkup-blue"
          />
        </div>
        <button
          class="w-full h-14 rounded-2xl bg-slate-900 text-white font-bold text-lg shadow-sm hover:brightness-105 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!cashAmount || cashAmount < 1 || isSubmitting"
          @click="submitCash"
        >
          {{ isSubmitting ? 'Processing...' : 'Add Funds' }}
        </button>
      </div>

      <div v-else class="mt-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <button
            v-for="pack in coinPacks"
            :key="pack.c"
            class="h-28 rounded-2xl border-2 transition-all bg-white flex flex-col items-center justify-center gap-1"
            :class="selectedCoinPack?.c === pack.c ? 'border-linkup-blue shadow-md' : 'border-slate-200 hover:border-linkup-blue/60 hover:shadow-sm'"
            @click="selectedCoinPack = pack"
          >
            <div class="text-lg font-extrabold text-slate-900">{{ pack.c }} LUC</div>
            <div class="text-xs text-slate-500">${{ pack.p }}</div>
          </button>
        </div>
        <button
          class="w-full h-14 rounded-2xl bg-slate-900 text-white font-bold text-lg shadow-sm hover:brightness-105 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!selectedCoinPack || isSubmitting"
          @click="submitCoins"
        >
          {{ isSubmitting ? 'Processing...' : `Buy ${selectedCoinPack?.c || ''} Coins` }}
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

function submitCash() {
  if (!cashAmount.value || cashAmount.value < 1) return;
  isSubmitting.value = true;

  // POST directly to controller to start Stripe checkout
  router.post(route('frontend.wallet.checkout'), {
    selectedAmount: cashAmount.value,
    redirect_to: window.location.href
  }, {
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
}

function submitCoins() {
  if (!selectedCoinPack.value) return;
  isSubmitting.value = true;

  // POST directly to controller to start Stripe checkout for coins
  // Send both coins count and price
  router.post(route('frontend.wallet.coin.checkout'), {
    selectedCoin: selectedCoinPack.value.p,  // Price in USD for Stripe
    coinCount: selectedCoinPack.value.c,      // Actual coin count to add
    redirect_to: window.location.href
  }, {
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
}
</script>

