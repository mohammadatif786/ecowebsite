<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="flex items-center justify-between border-b border-slate-100 p-5">
      <h3 class="text-xl font-black">Your order - {{ eatsType }}</h3>
      <button @click="close">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <div class="max-h-[72vh] space-y-3 overflow-y-auto p-4">
      <div class="space-y-2">
        <div v-for="(item, idx) in cart" :key="idx" class="flex gap-3 rounded-2xl bg-slate-50 p-2">
          <img :src="item.img" class="h-14 w-14 rounded-xl object-cover" />
          <div class="min-w-0 flex-1">
            <div class="flex justify-between gap-3">
              <p class="truncate text-sm font-bold">{{ item.qty }}x {{ item.n }}</p>
              <button @click="removeItem(idx)" class="text-rose-500">
                <i data-lucide="trash-2" class="h-4 w-4"></i>
              </button>
            </div>
            <p class="text-xs text-slate-500">{{ money(item.p * item.qty) }}</p>
            <p v-if="item.notes" class="mt-0.5 text-[11px] text-slate-400">Note: {{ item.notes }}</p>
          </div>
        </div>
      </div>

      <div v-if="eatsType === 'Delivery'" class="rounded-2xl border border-slate-200 p-3">
        <p class="text-[11px] font-black uppercase text-slate-400">Deliver to</p>
        <p class="mt-0.5 text-sm font-bold">{{ user.city }}, {{ user.country }}</p>
      </div>
      <div v-else class="rounded-2xl bg-slate-50 p-3 text-sm font-bold">
        Pickup at {{ restaurant ? restaurant.name : 'restaurant' }} - no delivery fee
      </div>

      <div>
        <p class="mb-1 text-xs font-black">Add a tip</p>
        <div class="grid grid-cols-4 gap-2">
          <button
            v-for="tp in tips"
            :key="tp"
            @click="selectTip(tp)"
            :class="[
              'flex items-center justify-center gap-1 rounded-xl border-2 py-2 text-sm font-black transition',
              tipRate === tp ? 'border-blue-500 text-blue-600' : 'border-slate-200 text-slate-600 hover:border-slate-300'
            ]"
          >
            <span v-if="tipRate === tp" class="text-base leading-none">&#10003;</span>
            {{ tp === 0 ? 'None' : (tp * 100) + '%' }}
          </button>
        </div>
      </div>

      <div class="space-y-1 rounded-2xl bg-slate-50 p-3 text-sm">
        <div class="flex justify-between"><span class="font-bold text-slate-500">Subtotal</span><b>{{ money(totals.subtotal) }}</b></div>
        <div class="flex justify-between"><span class="font-bold text-slate-500">Tax (10%)</span><b>{{ money(totals.tax) }}</b></div>
        <div v-if="eatsType === 'Delivery'" class="flex justify-between"><span class="font-bold text-slate-500">Delivery fee</span><b>{{ money(totals.delivery) }}</b></div>
        <div class="flex justify-between"><span class="font-bold text-slate-500">Service fee</span><b>{{ money(totals.fee) }}</b></div>
        <div v-if="totals.tip > 0" class="flex justify-between"><span class="font-bold text-slate-500">Tip</span><b>{{ money(totals.tip) }}</b></div>
        <div class="mt-1 flex justify-between border-t border-slate-200 pt-1 text-base"><span class="font-black">Total</span><b>{{ money(totals.total) }}</b></div>
      </div>

      <p class="text-[11px] text-slate-400">Pays from LinkUp Wallet ({{ money(wallet.balance) }}) - earn {{ Math.round(totals.total / 2) }} reward points</p>
      <button @click="placeOrder" class="btn btn-primary w-full py-3">
        Place {{ eatsType }} order - {{ money(totals.total) }}
      </button>
    </div>
  </Modal>
</template>

<script setup>
import { computed, ref } from 'vue';
import Modal from '../ui/Modal.vue';
import { getUser, getWallet } from '../MockDataStore';

const props = defineProps({
  cart: Array,
  eatsType: String,
  restaurant: Object
});

const emit = defineEmits(['remove', 'placeOrder']);

const modalRef = ref(null);
const user = getUser();
const wallet = getWallet();

const tips = [0, 0.10, 0.15, 0.20];
const tipRate = ref(0);

const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const refreshIcons = () => {
  setTimeout(() => {
    if (window.lucide) window.lucide.createIcons();
  }, 0);
};

const selectTip = (tip) => {
  tipRate.value = tip;
};

const totals = computed(() => {
  const subtotal = props.cart.reduce((s, i) => s + (i.p * i.qty), 0);
  const tax = subtotal * 0.10;
  const delivery = (props.eatsType === 'Delivery' && props.restaurant) ? props.restaurant.fee : 0;
  const fee = props.cart.length > 0 ? (subtotal * 0.06 + 0.75) : 0;
  const tip = subtotal * tipRate.value;
  const total = subtotal + tax + delivery + fee + tip;

  return { subtotal, tax, delivery, fee, tip, total };
});

const open = () => {
  if (props.cart.length === 0) {
    if (window.toast) window.toast('Your cart is empty');
    return;
  }

  if (modalRef.value) {
    modalRef.value.open();
    refreshIcons();
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const removeItem = (idx) => {
  emit('remove', idx);
  if (props.cart.length <= 1) {
    close();
  }
};

const placeOrder = () => {
  if (wallet.balance < totals.value.total) {
    if (window.toast) window.toast('Insufficient wallet balance - top up first');
    else alert('Insufficient wallet balance - top up first');
    return;
  }

  wallet.balance -= totals.value.total;

  emit('placeOrder', {
    total: totals.value.total,
    points: Math.round(totals.value.total / 2),
    tip: totals.value.tip
  });

  close();
};

defineExpose({ open, close });
</script>
