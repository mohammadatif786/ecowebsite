<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="tag" class="-m-1">
      <!-- Step 1: Product / Event Detail -->
      <template v-if="step === 'detail'">
        <div class="h-44 relative">
          <img :src="tag.image" class="w-full h-full object-cover rounded-t-[20px]" />
          <button @click="close" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/50 text-white grid place-items-center">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
          <span class="absolute top-3 left-3 text-white text-[11px] font-black px-2.5 py-1 rounded-full" :style="{ background: isEvent ? '#2563eb' : '#8b5cf6' }">
            {{ isEvent ? '🎟️ Tagged event' : '🛍️ Tagged in this vibe' }}
          </span>
        </div>

        <div class="p-5">
          <h3 class="text-xl font-black">{{ tag.title }}</h3>
          <p class="text-slate-500 font-semibold mt-0.5">
            {{ isEvent ? `${tag.date} · ${tag.location || 'Location TBA'}` : (tag.seller || 'Marketplace') }}
          </p>
          <p class="text-2xl font-black text-lkink mt-2">{{ tag.price ? money(tag.price) : 'Free' }}</p>

          <div v-if="isEvent" class="rounded-2xl bg-blue-50 p-3 mt-3 text-[12px] font-bold text-blue-700 flex items-center gap-2">
            <i data-lucide="ticket" class="w-4 h-4"></i>Pays from your Wallet · ticket saved to My Tickets
          </div>
          <div v-else class="rounded-2xl bg-emerald-50 p-3 mt-3 text-[12px] font-bold text-emerald-700 flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4"></i>Escrow-protected · seller paid after delivery
          </div>

          <div class="flex gap-2 mt-4">
            <button @click="goToReview" class="btn btn-primary flex-1 py-3 font-black">
              {{ isEvent ? 'Get Tickets' : 'Buy now' }} · {{ tag.price ? money(tag.price) : 'Free' }}
            </button>
            <button v-if="isEvent" @click="viewEvent" class="btn btn-ghost px-4 py-3">
              View event
            </button>
            <button v-else @click="addToCart" class="btn btn-ghost px-4 py-3 font-bold">
              Add to Cart
            </button>
          </div>
        </div>
      </template>

      <!-- Step 2: Checkout Review & Price Breakdown -->
      <template v-else-if="step === 'review'">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-black">Order Summary</h3>
            <button @click="step = 'detail'" class="text-slate-400 hover:text-slate-600 text-sm font-bold flex items-center gap-1">
              ← Back
            </button>
          </div>

          <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl mb-4">
            <img :src="tag.image" class="w-14 h-14 rounded-xl object-cover" />
            <div class="min-w-0 flex-1">
              <p class="font-black text-sm truncate">{{ tag.title }}</p>
              <p class="text-xs text-slate-500">{{ money(tag.price) }}</p>
            </div>
          </div>

          <div class="space-y-2.5 border-t border-slate-100 pt-4 text-sm font-semibold">
            <div class="flex justify-between text-slate-600">
              <span>Item Subtotal</span>
              <span>{{ money(tag.price) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>{{ shopFee.label || 'Platform Fee' }} ({{ shopFee.percent || 5 }}%{{ shopFee.fixed > 0 ? ' + ' + money(shopFee.fixed) : '' }})</span>
              <span>{{ money(platformFee) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Estimated Tax (8%)</span>
              <span>{{ money(taxAmount) }}</span>
            </div>
            <div class="flex justify-between text-slate-900 font-black text-lg border-t border-slate-100 pt-3">
              <span>Total Payment</span>
              <span class="text-lkblue">{{ money(totalPayment) }}</span>
            </div>
          </div>

          <div class="rounded-2xl bg-amber-50 p-3 mt-4 text-[11px] font-bold text-amber-800 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
            Funds will be securely charged from your LinkUp digital wallet.
          </div>

          <div class="flex gap-2 mt-5">
            <button @click="step = 'detail'" class="btn btn-ghost flex-1 py-3">Cancel</button>
            <button @click="buyTag" :disabled="busy" class="btn btn-primary flex-1 py-3 font-black">
              {{ busy ? 'Processing...' : 'Confirm & Pay ' + money(totalPayment) }}
            </button>
          </div>
        </div>
      </template>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '../ui/Modal.vue';
import { DB } from '../MockDataStore';

const modalRef = ref(null);
const tag = ref(null);
const page = usePage();
const busy = ref(false);
const step = ref('detail');

const walletBalance = computed(() => {
  const bal = page.props.walletBalance ?? page.props.auth?.user?.balance ?? 0;
  return Number(bal);
});

const shopFee = computed(() => page.props.shopFee || { percent: 5, fixed: 0.30, label: 'Marketplace Fee' });

const isEvent = computed(() => tag.value && tag.value.kind === 'event');
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const platformFee = computed(() => {
  const price = Number(tag.value?.price || 0);
  const pct = Number(shopFee.value.percent || 5);
  const fixed = Number(shopFee.value.fixed || 0);
  return Number(((price * pct / 100) + fixed).toFixed(2));
});

const taxAmount = computed(() => Number((Number(tag.value?.price || 0) * 0.08).toFixed(2)));
const totalPayment = computed(() => Number((Number(tag.value?.price || 0) + platformFee.value + taxAmount.value).toFixed(2)));

const open = (t) => {
  tag.value = t;
  step.value = 'detail';
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) {
      setTimeout(() => window.lucide.createIcons(), 50);
    }
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const goToReview = () => {
  step.value = 'review';
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const buyTag = async () => {
  const total = totalPayment.value;

  if (walletBalance.value < total) {
    alert(`Insufficient wallet balance ($${walletBalance.value.toFixed(2)}) — top up first`);
    return;
  }

  busy.value = true;
  try {
    await axios.post(route('new_frontend.vibes.purchase', { vibe: tag.value.vibe_id }), {
      kind: tag.value.kind,
      id: tag.value.id,
      price: total,
      affiliate_user_id: tag.value.affiliate_user_id || null
    });

    alert(isEvent.value ? `🎟️ Ticket secured · ${tag.value.title}` : `✅ Bought ${tag.value.title} · 🔒 escrow-protected`);

    router.reload({ only: ['earningsStats', 'walletBalance', 'auth'] });
    close();
  } catch (e) {
    alert(e.response?.data?.message || 'Purchase failed. Please try again.');
  } finally {
    busy.value = false;
  }
};

const viewEvent = () => {
  close();
  router.visit('/new_frontend/events');
};

const addToCart = () => {
  if (!tag.value) return;

  const currentCart = DB.get('lk_cart', []);
  const existing = currentCart.find(i => i.id === tag.value.id);

  if (existing) {
    existing.qty++;
  } else {
    currentCart.push({
      id: tag.value.id,
      title: tag.value.title,
      price: tag.value.price,
      image: tag.value.image,
      vibe_id: tag.value.vibe_id,
      affiliate_user_id: tag.value.affiliate_user_id || null,
      qty: 1
    });
  }

  DB.set('lk_cart', currentCart);
  showToast('🛍️ Added to cart · Sale attributed to promoter');
  close();
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};

defineExpose({ open, close });
</script>
