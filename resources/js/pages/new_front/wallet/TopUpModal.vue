<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center"
    @click.self="close"
  >
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
      <div class="p-5 flex items-center justify-between pb-3">
        <h3 class="text-xl font-black">Wallet Top Up</h3>
        <button @click="close" class="hover:bg-slate-100 p-1 rounded">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <div class="p-5 pt-0">
        <div class="grid grid-cols-2 bg-slate-100 rounded-2xl p-1 font-black text-sm mb-4">
          <button
            @click="setTab('cash')"
            :class="[
              'rounded-xl py-2.5 transition',
              topUpTab === 'cash' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700',
            ]"
          >
            Top Up Cash
          </button>
          <button
            @click="setTab('coins')"
            :class="[
              'rounded-xl py-2.5 transition',
              topUpTab === 'coins' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700',
            ]"
          >
            Buy Coins
          </button>
        </div>

        <div v-if="topUpTab === 'cash'" class="fade">
          <input
            v-model.number="cashAmount"
            type="number"
            min="10"
            max="1000"
            step="1"
            placeholder="Amount (USD)"
            class="w-full rounded-2xl border border-slate-200 px-4 py-4 font-black text-2xl outline-none focus:border-lkblue transition-colors"
          />
          <p v-if="cashError" class="text-[12px] text-rose-600 font-bold mt-2">{{ cashError }}</p>
          <button
            @click="submitCash"
            :disabled="cashForm.processing"
            class="btn btn-primary w-full mt-3 py-4 text-lg shadow hover:shadow-md transition disabled:opacity-60"
          >
            {{ cashForm.processing ? 'Processing...' : 'Add Funds' }}
          </button>
        </div>

        <div v-else class="fade">
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="p in coinPacks"
              :key="p.luc"
              @click="selectCoinPack(p)"
              :class="[
                'rounded-2xl border p-4 text-left transition',
                selectedCoinPack?.luc === p.luc
                  ? 'border-amber-400 bg-amber-50/70'
                  : 'border-slate-200 hover:border-amber-400 hover:bg-amber-50/50',
              ]"
            >
              <p class="font-black text-xl text-amber-500 flex items-center gap-1.5">
                {{ Number(p.luc).toLocaleString() }}
                <i data-lucide="gem" class="w-4 h-4"></i>
              </p>
              <p class="text-slate-400 font-bold mt-1">${{ p.usd }}</p>
            </button>
          </div>

          <button
            @click="submitCoins"
            :disabled="!selectedCoinPack || coinForm.processing"
            class="btn w-full mt-4 py-4 text-lg text-white shadow hover:shadow-md transition disabled:opacity-60"
            style="background:#0b2942"
          >
            {{ coinButtonText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
  coinPacks: { type: Array, required: true },
});

const isOpen = ref(false);
const topUpTab = ref('cash');
const cashAmount = ref(null);
const cashError = ref('');
const selectedCoinPack = ref(null);
const redirectTo = route('new_frontend.wallet');

const cashForm = useForm({ selectedAmount: '', redirect_to: redirectTo });
const coinForm = useForm({ selectedCoin: '', coinCount: '', redirect_to: redirectTo });

const coinButtonText = computed(() => {
  if (coinForm.processing) return 'Processing...';
  if (!selectedCoinPack.value) return 'Select a coin pack';
  return `Buy ${Number(selectedCoinPack.value.luc).toLocaleString()} Coins`;
});

const refreshIcons = () => {
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const setTab = (tab) => {
  topUpTab.value = tab;
  refreshIcons();
};

const open = (tab) => {
  topUpTab.value = tab || 'cash';
  cashAmount.value = null;
  cashError.value = '';
  selectedCoinPack.value = null;
  isOpen.value = true;
  refreshIcons();
};

const close = () => {
  isOpen.value = false;
};

const submitCash = () => {
  const amount = Number(cashAmount.value);
  if (!Number.isInteger(amount) || amount < 10 || amount > 1000) {
    cashError.value = 'Enter a whole dollar amount from $10 to $1,000.';
    return;
  }

  cashError.value = '';
  cashForm.selectedAmount = String(amount);
  cashForm.redirect_to = redirectTo;
  cashForm.post(route('new_frontend.wallet.checkout'), {
    forceFormData: true,
    onSuccess: close,
  });
};

const selectCoinPack = (pack) => {
  selectedCoinPack.value = pack;
};

const submitCoins = () => {
  if (!selectedCoinPack.value) return;

  coinForm.selectedCoin = String(selectedCoinPack.value.usd);
  coinForm.coinCount = String(selectedCoinPack.value.luc);
  coinForm.redirect_to = redirectTo;
  coinForm.post(route('new_frontend.wallet.coin.checkout'), {
    forceFormData: true,
    onSuccess: close,
  });
};

defineExpose({ open, close });
</script>
