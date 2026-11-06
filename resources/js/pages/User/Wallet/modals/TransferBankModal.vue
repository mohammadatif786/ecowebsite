<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 shadow-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-black flex items-center gap-1">
          <button v-if="step !== 'form'" @click="back" class="mr-1 hover:bg-slate-100 p-1 rounded" aria-label="Back"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
          {{ step === 'add' ? 'Add Bank Account' : step === 'pin' ? 'Confirm Withdrawal' : 'Transfer to Bank' }}
        </h3>
        <button @click="close" class="hover:bg-slate-100 p-1 rounded" aria-label="Close"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>

      <!-- Withdrawal form (prototype layout) -->
      <div v-if="step === 'form'">
        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 mb-3 border border-slate-100">
          <span class="font-black text-slate-500">Available</span><span class="font-black text-lg">{{ money(balance) }}</span>
        </div>
        <input v-model="amount" type="number" min="10" max="10000" step="0.01" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-black text-lg outline-none focus:border-lkblue transition-colors mb-2" />

        <template v-if="accounts.length">
          <select v-model="bankId" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none bg-white focus:border-lkblue transition-colors mb-1">
            <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.bank_name }} ••{{ a.last4 }}{{ a.is_default ? ' · default' : '' }}</option>
          </select>
          <button @click="openAdd" class="text-[12px] font-black text-lkblue2 mb-3 ml-1 hover:underline">+ Add another bank</button>
        </template>
        <button v-else @click="openAdd" class="w-full rounded-2xl border-2 border-dashed border-slate-300 px-4 py-3 font-black text-slate-500 hover:border-lkblue hover:text-lkblue transition mb-3 flex items-center justify-center gap-2">
          <i data-lucide="building-2" class="w-4 h-4"></i>Add a bank account
        </button>

        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-3 text-amber-800 mb-3">
          <p class="font-bold text-sm">{{ feePercent }}% bank processing fee applies.</p>
          <p class="text-[12px] mt-1 font-bold opacity-80">You withdraw {{ money(amt) }} • Fee {{ money(fee) }} • You receive {{ money(amt - fee) }}</p>
          <p class="text-[11px] mt-1 font-bold opacity-70">Min $10 · max $10,000 · paid out after review.</p>
        </div>

        <p v-if="error" class="text-[12px] font-bold text-rose-600 mb-3 bg-rose-50 p-2 rounded-xl">{{ error }}</p>
        <button @click="review" class="btn w-full py-3.5 text-white shadow" style="background:#0b2942">Confirm Withdrawal</button>
      </div>

      <!-- Add a bank -->
      <div v-else-if="step === 'add'" class="space-y-3">
        <input v-model="bank.bank_name" placeholder="Bank name (e.g. Scotiabank)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue" />
        <input v-model="bank.account_number" placeholder="Account number" inputmode="numeric" autocomplete="off" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue" />
        <input v-model="bank.routing_number" placeholder="Routing / transit number (optional)" inputmode="numeric" autocomplete="off" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue" />
        <div class="grid grid-cols-2 gap-2">
          <button v-for="t in ['checking', 'savings']" :key="t" @click="bank.account_type = t"
            :class="['rounded-2xl border-2 py-2.5 font-black text-sm capitalize transition', bank.account_type === t ? 'border-lkblue bg-blue-50 text-lkblue2' : 'border-slate-100 text-slate-500']">{{ t }}</button>
        </div>
        <label class="flex items-center gap-2 text-sm font-bold text-slate-600"><input v-model="bank.is_default" type="checkbox" class="rounded" />Use as my default bank</label>
        <p v-if="error" class="text-[12px] font-bold text-rose-600 bg-rose-50 p-2 rounded-xl">{{ error }}</p>
        <button @click="saveBank" :disabled="busy" class="btn btn-primary w-full py-3.5 disabled:opacity-60">{{ busy ? 'Saving…' : 'Save Bank' }}</button>
        <p class="text-[11px] text-slate-400 font-bold text-center">Bank details are stored encrypted.</p>
      </div>

      <!-- PIN -->
      <div v-else class="text-center">
        <p class="text-[11px] font-black text-slate-400 uppercase">You're withdrawing</p>
        <p class="text-4xl font-black mt-2">{{ money(amt) }}</p>
        <p class="text-sm font-bold text-slate-500 mt-1">to {{ selected?.bank_name }} ••{{ selected?.last4 }} · you receive {{ money(amt - fee) }}</p>

        <template v-if="hasPin">
          <p class="text-xs font-black text-slate-600 mt-5 mb-3">Enter your transaction PIN</p>
          <PinBoxes ref="pinBoxes" v-model="pin" label="Transaction PIN" @enter="submit" />
          <p v-if="error" class="text-[12px] font-bold text-rose-600 mt-4 bg-rose-50 p-2 rounded-xl">{{ error }}</p>
          <button @click="submit" :disabled="busy" class="btn w-full py-3.5 mt-5 text-white shadow disabled:opacity-60 inline-flex items-center justify-center gap-2" style="background:#0b2942">
            <i data-lucide="lock" class="w-4 h-4"></i>{{ busy ? 'Submitting…' : 'Withdraw ' + money(amt) }}
          </button>
        </template>
        <template v-else>
          <p class="text-sm font-bold text-slate-500 mt-5">Set a transaction PIN first. It protects every withdrawal.</p>
          <button @click="$emit('setup-pin')" class="btn btn-primary w-full py-3.5 mt-4 inline-flex items-center justify-center gap-2"><i data-lucide="lock" class="w-4 h-4"></i>Set a transaction PIN</button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue';
import axios from 'axios';
import PinBoxes from '../components/PinBoxes.vue';

const props = defineProps({
  balance: { type: Number, default: 0 },
  bankAccounts: { type: Array, default: () => [] },
  feePercent: { type: Number, default: 5 },
  hasPin: { type: Boolean, default: false },
});
const emit = defineEmits(['withdrawn', 'setup-pin']);

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const isOpen = ref(false);
const step = ref('form');
const amount = ref('');
const bankId = ref(null);
const pin = ref('');
const error = ref('');
const busy = ref(false);
const pinBoxes = ref(null);
const accounts = ref([...props.bankAccounts]);
const bank = reactive({ bank_name: '', account_number: '', routing_number: '', account_type: 'checking', is_default: false });

watch(() => props.bankAccounts, (list) => { accounts.value = [...list]; }, { deep: true });

const amt = computed(() => Math.max(0, Number(amount.value) || 0));
const fee = computed(() => Math.round(amt.value * props.feePercent) / 100);
const selected = computed(() => accounts.value.find((a) => a.id === bankId.value) || null);

const icons = () => nextTick(() => window.lucide?.createIcons());
const pickDefault = () => { bankId.value = (accounts.value.find((a) => a.is_default) || accounts.value[0] || {}).id ?? null; };

const open = () => {
  step.value = 'form'; amount.value = ''; pin.value = ''; error.value = '';
  pickDefault(); isOpen.value = true; icons();
};
const close = () => { isOpen.value = false; };
const back = () => { error.value = ''; step.value = 'form'; icons(); };
const openAdd = () => { error.value = ''; Object.assign(bank, { bank_name: '', account_number: '', routing_number: '', account_type: 'checking', is_default: false }); step.value = 'add'; icons(); };

const firstError = (e, fallback) => {
  const errors = e?.response?.data?.errors;
  if (errors) return Object.values(errors).flat()[0];
  return e?.response?.data?.message || e?.response?.data?.error || fallback;
};

const saveBank = async () => {
  error.value = '';
  if (!bank.bank_name.trim() || !bank.account_number.trim()) { error.value = 'Enter the bank name and account number.'; return; }
  busy.value = true;
  try {
    const { data } = await axios.post(route('user.wallet.bank_accounts.store'), { ...bank, routing_number: bank.routing_number || null });
    accounts.value = data.bankAccounts || accounts.value;
    bankId.value = data.bank_account_id;
    step.value = 'form';
    window.toast?.('🏦 Bank saved');
    icons();
  } catch (e) {
    error.value = firstError(e, 'Could not save this bank.');
  } finally { busy.value = false; }
};

const review = () => {
  error.value = '';
  if (!amt.value) { error.value = 'Enter an amount.'; return; }
  if (amt.value < 10) { error.value = 'Minimum withdrawal is $10.'; return; }
  if (amt.value > 10000) { error.value = 'Maximum withdrawal is $10,000.'; return; }
  if (amt.value > props.balance) { error.value = 'This is more than your wallet balance.'; return; }
  if (!selected.value) { error.value = 'Add or choose a bank account.'; return; }
  pin.value = ''; step.value = 'pin'; icons();
  nextTick(() => pinBoxes.value?.focus());
};

const submit = async () => {
  if (busy.value) return;
  error.value = '';
  if (pin.value.length < 4) { error.value = 'Enter your 4-digit PIN.'; return; }
  busy.value = true;
  try {
    const { data } = await axios.post(route('user.wallet.bank_withdrawal'), {
      amount: amt.value, bank_account_id: bankId.value, pin: pin.value,
    });
    close();
    window.toast?.(`🏦 Withdrawal of ${money(amt.value)} submitted`);
    emit('withdrawn', data);
  } catch (e) {
    error.value = firstError(e, 'Withdrawal failed. Please try again.');
    pinBoxes.value?.clear();
  } finally { busy.value = false; }
};

defineExpose({ open });
</script>
