<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8">
      <div class="flex justify-between mb-8">
        <h3 class="text-2xl font-bold">Transfer to Bank</h3>
        <button @click="$emit('close')">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="mb-6 p-4 bg-slate-50 rounded-xl flex justify-between items-center">
        <span class="text-sm font-bold text-slate-500">Available</span>
        <span class="text-lg font-bold text-slate-900">{{ formatCurrency(availableBalance) }}</span>
      </div>

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Amount (USD)</label>
      <input v-model="amount" type="number"
        class="w-full p-4 bg-slate-50 rounded-xl mb-4 text-3xl font-bold border border-transparent focus:bg-white focus:border-linkup-blue outline-none"
        placeholder="0.00" min="10" max="10000" step="0.01" />

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Destination</label>
      <select v-model="selectedBankIndex"
        class="w-full p-4 bg-slate-50 rounded-xl mb-8 font-bold border border-transparent outline-none">
        <option v-if="!bankDetails || bankDetails.banks.length === 0" value="" disabled>Select a bank account</option>
        <option v-for="(bank, index) in bankDetails?.banks" :key="index" :value="index">
          {{ bank.bank_name }} (••• {{ bank.account_number?.slice(-4) || '----' }}){{ bank.is_default ? ' - Default' :
          '' }}
        </option>
      </select>

      <div class="mb-6 rounded-xl bg-[#fff7ed] border border-[#fed7aa] px-4 py-3">
        <p class="text-sm font-semibold text-[#9a3412]">
          A {{ isLoadingFee ? '...' : feePercent }}% bank processing fee will be deducted when you confirm this withdrawal.
        </p>
        <p class="text-xs text-[#9a3412] mt-1">
          You withdraw {{ formatCurrency(numericAmount) }} • Fee {{ formatCurrency(feeAmount) }} • You receive {{ formatCurrency(receiveAmount) }}
        </p>
      </div>

      <button
        class="w-full py-4 bg-linkup-dark text-white rounded-xl font-bold text-lg hover:brightness-110 transition-colors"
        @click="handleSubmit">
        Confirm Withdrawal
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps<{
  availableBalance: number;
  formatCurrency: (amount: number) => string;
  bankDetails?: {
    paypal_id?: string | null;
    banks?: any[];
  };
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submit', data: { amount: number; bankIndex: number }): void;
}>();

const amount = ref<number>(0);
const selectedBankIndex = ref<number | null>(null);

const defaultFeePercent = 5;
const feePercent = ref<number>(defaultFeePercent);
const isLoadingFee = ref<boolean>(true);

function parseAmount(value: unknown): number {
  const numeric = Number(value);
  if (!Number.isFinite(numeric) || numeric < 0) return 0;
  return numeric;
}

function parseFeePercent(data: any, fallback = 5): number {
  if (
    data &&
    typeof data.bankProcessingFeePercent === 'number' &&
    Number.isFinite(data.bankProcessingFeePercent) &&
    data.bankProcessingFeePercent >= 0
  ) {
    return data.bankProcessingFeePercent;
  }
  return fallback;
}

const numericAmount = computed(() => parseAmount(amount.value));
const feeAmount = computed(() => numericAmount.value * (feePercent.value / 100));
const receiveAmount = computed(() => Math.max(numericAmount.value - feeAmount.value, 0));

// Auto-select default bank when bankDetails changes
watch(() => props.bankDetails?.banks, (banks) => {
  if (banks && banks.length > 0) {
    const defaultIndex = banks.findIndex((bank) => bank.is_default);
    selectedBankIndex.value = defaultIndex !== -1 ? defaultIndex : 0;
  }
}, { immediate: true });

async function loadWithdrawalFee(): Promise<void> {
  try {
    const response = await fetch('/api/withdrawal-settings', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      return;
    }

    const rawText = await response.text();
    if (!rawText || !rawText.trim()) {
      return;
    }

    let parsedData: any;
    try {
      parsedData = JSON.parse(rawText);
    } catch {
      return;
    }

    feePercent.value = parseFeePercent(parsedData, defaultFeePercent);
  } catch {
    feePercent.value = defaultFeePercent;
  } finally {
    isLoadingFee.value = false;
  }
}

onMounted(() => {
  loadWithdrawalFee();
});

const handleSubmit = () => {
  if (amount.value <= 0) {
    alert('Please enter a valid amount');
    return;
  }

  if (selectedBankIndex.value === null || selectedBankIndex.value < 0) {
    alert('Please select a bank account');
    return;
  }

  emit('submit', {
    amount: amount.value,
    bankIndex: selectedBankIndex.value
  });
};
</script>
