<template>
  <div class="w-full">
    <div class="rounded-[24px] bg-[#f1f5f9] px-[32px] py-[34px] mb-[24px] flex items-center justify-between">
      <span class="text-[20px] font-semibold text-[#64748b]">Available</span>
      <span class="text-[22px] font-bold text-[#0f172a]">{{ formatCurrency(availableAmount) }}</span>
    </div>

    <div class="mb-[20px]">
      <label
        for="withdrawal-amount"
        class="block text-[14px] font-bold uppercase tracking-[0.02em] text-[#94a3b8] mb-[10px]"
      >
        Amount (USD)
      </label>
      <input
        id="withdrawal-amount"
        v-model="amount"
        type="number"
        min="0"
        step="0.01"
        class="w-full rounded-[18px] bg-[#f1f5f9] px-[18px] py-[16px] text-[24px] font-bold text-black outline-none appearance-none"
      />
    </div>

    <div class="mb-[20px] rounded-[18px] bg-[#fff7ed] border border-[#fed7aa] px-[22px] py-[18px]">
      <p class="text-[14px] font-semibold text-[#9a3412]">
        A {{ isLoadingFee ? '...' : feePercent }}% bank processing fee will be deducted when you confirm this withdrawal.
      </p>
      <p class="text-[13px] text-[#9a3412] mt-[6px]">
        You withdraw {{ formatCurrency(numericAmount) }} • Fee {{ formatCurrency(feeAmount) }} • You receive {{ formatCurrency(receiveAmount) }}
      </p>
    </div>

    <div class="mb-[24px]">
      <label
        for="withdrawal-destination"
        class="block text-[14px] font-bold uppercase tracking-[0.02em] text-[#94a3b8] mb-[10px]"
      >
        Destination
      </label>
      <div
        id="withdrawal-destination"
        class="w-full rounded-[14px] bg-[#f1f5f9] px-[18px] py-[10px] flex items-center justify-between"
      >
        <span class="text-[16px] font-bold text-black leading-tight">{{ destinationLabel }}</span>
        <span class="text-[18px] text-black leading-none">›</span>
      </div>
    </div>

    <button
      type="button"
      class="w-full rounded-[18px] bg-[#072b57] hover:opacity-95 transition text-white text-[18px] font-bold py-[16px]"
      :disabled="disabled"
      @click="confirmWithdrawal"
    >
      Confirm Withdrawal
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    available?: number;
    destination?: string;
    defaultFeePercent?: number;
  }>(),
  {
    available: 0,
    destination: 'Default bank',
    defaultFeePercent: 5,
  },
);

const emit = defineEmits<{
  (e: 'confirm', data: { amount: number; feePercent: number; feeAmount: number; receiveAmount: number }): void;
}>();

function parseAmount(value: unknown): number {
  const numeric = Number(value);
  if (!Number.isFinite(numeric) || numeric < 0) return 0;
  return numeric;
}

function formatCurrency(value: number): string {
  return `$${value.toFixed(2)}`;
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

const amount = ref('0');
const feePercent = ref(props.defaultFeePercent);
const isLoadingFee = ref(true);

const availableAmount = computed(() => parseAmount(props.available));
const destinationLabel = computed(() => props.destination);

const numericAmount = computed(() => parseAmount(amount.value));
const feeAmount = computed(() => numericAmount.value * (feePercent.value / 100));
const receiveAmount = computed(() => Math.max(numericAmount.value - feeAmount.value, 0));

const disabled = computed(() => isLoadingFee.value || numericAmount.value <= 0);

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

    const nextFeePercent = parseFeePercent(parsedData, props.defaultFeePercent);
    feePercent.value = nextFeePercent;
  } catch {
    feePercent.value = props.defaultFeePercent;
  } finally {
    isLoadingFee.value = false;
  }
}

function confirmWithdrawal(): void {
  emit('confirm', {
    amount: numericAmount.value,
    feePercent: feePercent.value,
    feeAmount: feeAmount.value,
    receiveAmount: receiveAmount.value,
  });
}

onMounted(() => {
  loadWithdrawalFee();
});
</script>
