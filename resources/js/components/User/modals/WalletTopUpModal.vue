<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
      <div class="p-5 flex items-center justify-between pb-3">
        <h3 class="text-xl font-black">Wallet Top Up</h3>
        <button @click="close" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-6 h-6"></i></button>
      </div>
      <div class="p-5 pt-0">
        <div class="grid grid-cols-2 bg-slate-100 rounded-2xl p-1 font-black text-sm mb-4">
          <button @click="topUpTab = 'cash'" :class="['rounded-xl py-2.5 transition', topUpTab === 'cash' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700']">Top Up Cash</button>
          <button @click="topUpTab = 'coins'" :class="['rounded-xl py-2.5 transition', topUpTab === 'coins' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700']">Buy Coins</button>
        </div>

        <div v-if="topUpTab === 'cash'" class="fade">
          <input v-model="tuAmt" type="number" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-4 font-black text-2xl outline-none focus:border-lkblue transition-colors"/>
          <button @click="addFunds" class="btn btn-primary w-full mt-3 py-4 text-lg shadow hover:shadow-md transition">Add Funds</button>
        </div>

        <div v-else class="grid grid-cols-2 gap-3 fade">
          <button v-for="p in coinPacks" :key="p.luc" @click="buyCoins(p.luc, p.usd)" class="rounded-2xl border border-slate-200 p-4 text-left hover:border-amber-400 hover:bg-amber-50/50 transition">
            <p class="font-black text-xl text-amber-500">{{ Number(p.luc).toLocaleString() }} 🪙</p>
            <p class="text-slate-400 font-bold mt-1">${{ p.usd }}</p>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

defineProps({
  coinPacks: { type: Array, required: true }
});

const emit = defineEmits(['add-funds', 'buy-coins']);

const isOpen = ref(false);
const topUpTab = ref('cash');
const tuAmt = ref('');

const open = (tab) => {
  topUpTab.value = tab || 'cash';
  tuAmt.value = '';
  isOpen.value = true;
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};
const close = () => { isOpen.value = false; };

const addFunds = () => {
  const v = parseFloat(tuAmt.value);
  if (!v || v <= 0) { if (window.toast) window.toast('Enter an amount'); return; }
  emit('add-funds', v);
};
const buyCoins = (luc, usd) => {
  emit('buy-coins', { luc, usd });
};

defineExpose({ open, close });
</script>
