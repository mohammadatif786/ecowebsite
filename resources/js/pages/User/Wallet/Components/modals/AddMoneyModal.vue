<template>
  <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50" role="dialog" aria-modal="true" @click.self="$emit('close')">
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="plus-circle" class="w-5 h-5"></i>
        <div class="font-semibold">Add Money</div>
        <button class="ml-auto p-1 rounded-lg hover:bg-slate-100" @click="$emit('close')">
          <i data-lucide="x"></i>
        </button>
      </div>
      <div class="space-y-3">
        <div class="text-sm">Select amount ({{ current().currency }})</div>
        <div class="grid grid-cols-4 gap-2">
          <button
            v-for="amt in quickAmounts"
            :key="amt"
            class="amt-btn px-3 py-2 rounded-xl border"
            :class="{ 'bg-linkup-blue text-white': customAmount === amt }"
            @click="selectAmount(amt)"
          >
            {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: current().currency }).format(amt) }}
          </button>
        </div>
        <input
          v-model.number="customAmount"
          type="number"
          step="0.01"
          min="0.01"
          class="w-full px-3 py-2 border rounded-xl"
          :placeholder="`Or enter custom amount (${current().currency})`"
          aria-label="Custom amount"
        />
        <div class="grid grid-cols-2 gap-2">
          <label class="border rounded-xl p-2 flex items-center gap-2">
            <input type="radio" name="fund" value="Card" v-model="fundMethod" />
            <span>Card (2.9% + 0.30 USD)</span>
          </label>
          <label class="border rounded-xl p-2 flex items-center gap-2">
            <input type="radio" name="fund" value="Bank" v-model="fundMethod" />
            <span>Bank (no fee)</span>
          </label>
        </div>
        <button
          class="w-full px-4 py-2 rounded-xl bg-linkup-lime text-linkup-dark"
          @click="confirmAdd"
        >
          Add
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue';

const current = inject('current');
const addMoneyLocal = inject('addMoneyLocal');
defineEmits(['close']);

const customAmount = ref(0);
const fundMethod = ref('Card');
const quickAmounts = [20, 50, 75, 100, 150, 200, 250, 300];

const selectAmount = (amt) => {
  customAmount.value = amt;
};

const confirmAdd = () => {
  const feeMode = fundMethod.value === 'Card' ? 'card-fee' : 'none';
  addMoneyLocal(customAmount.value, fundMethod.value, feeMode);
  emit('close');
};

onMounted(() => {
  window.$lucide.createIcons();
});
</script>