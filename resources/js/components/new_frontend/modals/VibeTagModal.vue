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

      <!-- Step 2: delivery or pickup, priced by the server -->
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
              <p class="text-xs text-slate-500">{{ money(tag.price) }} · {{ tag.seller || 'Marketplace' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 bg-slate-100 rounded-2xl p-1 font-black text-sm mb-3">
            <button @click="setMethod('standard')" :class="['rounded-xl py-2', form.shipping_method === 'standard' ? 'bg-white shadow' : 'text-slate-500']">🚚 Delivery</button>
            <button @click="setMethod('pickup')" :class="['rounded-xl py-2', form.shipping_method === 'pickup' ? 'bg-white shadow' : 'text-slate-500']">📍 Pickup</button>
          </div>

          <div v-if="form.shipping_method === 'standard'" class="grid grid-cols-2 gap-2 mb-3">
            <input v-model="form.address" placeholder="Street address" class="col-span-2 rounded-2xl border border-slate-200 px-3 py-2 text-sm" />
            <input v-model="form.city" placeholder="City" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm" />
            <input v-model="form.state" @change="loadQuote" placeholder="State / Parish" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm" />
            <input v-model="form.country" @change="loadQuote" placeholder="Country" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm" />
            <input v-model="form.zip" placeholder="ZIP (optional)" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <p v-else class="rounded-2xl bg-slate-50 p-3 mb-3 text-[12px] font-bold text-slate-600">
            Pick up from the seller{{ quote?.pickup ? ' · ' + quote.pickup : '' }}. They’ll message you to arrange it.
          </p>

          <div class="flex items-center justify-between mb-3 text-sm font-bold">
            <span>Quantity</span>
            <div class="flex items-center gap-2">
              <button @click="setQty(form.qty - 1)" class="w-8 h-8 rounded-full bg-slate-100 font-black">−</button>
              <span class="w-6 text-center font-black">{{ form.qty }}</span>
              <button @click="setQty(form.qty + 1)" class="w-8 h-8 rounded-full bg-slate-100 font-black">+</button>
            </div>
          </div>

          <div v-if="quote" class="space-y-2.5 border-t border-slate-100 pt-4 text-sm font-semibold">
            <div class="flex justify-between text-slate-600"><span>Item Subtotal</span><span>{{ money(quote.subtotal) }}</span></div>
            <div v-if="quote.fee_amount" class="flex justify-between text-slate-600"><span>{{ quote.fee_label || 'Marketplace Fee' }}</span><span>{{ money(quote.fee_amount) }}</span></div>
            <div class="flex justify-between text-slate-600"><span>Tax</span><span>{{ money(quote.tax) }}</span></div>
            <div v-if="quote.shipping" class="flex justify-between text-slate-600"><span>Shipping</span><span>{{ money(quote.shipping) }}</span></div>
            <div class="flex justify-between text-slate-900 font-black text-lg border-t border-slate-100 pt-3">
              <span>Total Payment</span><span class="text-lkblue">{{ money(quote.total) }}</span>
            </div>
            <p class="text-[11px] text-slate-500 font-bold">Wallet balance {{ money(quote.wallet_balance) }}</p>
          </div>
          <p v-else-if="quoteError" class="text-sm font-bold text-rose-600 border-t border-slate-100 pt-4">{{ quoteError }}</p>
          <p v-else class="text-sm font-bold text-slate-400 border-t border-slate-100 pt-4">Getting the price…</p>

          <div class="rounded-2xl bg-amber-50 p-3 mt-4 text-[11px] font-bold text-amber-800 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
            Paid from your LinkUp wallet · escrow-protected, the seller is paid after delivery.
          </div>

          <div class="flex gap-2 mt-5">
            <button @click="step = 'detail'" class="btn btn-ghost flex-1 py-3">Cancel</button>
            <button @click="buyTag" :disabled="busy || !quote" class="btn btn-primary flex-1 py-3 font-black">
              {{ busy ? 'Processing...' : (quote ? 'Confirm & Pay ' + money(quote.total) : 'Confirm & Pay') }}
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
const quote = ref(null);
const quoteError = ref('');

const authUser = computed(() => page.props.auth?.user || {});
const form = ref({ qty: 1, shipping_method: 'standard', address: '', city: '', state: '', country: '', zip: '' });

const isEvent = computed(() => tag.value && tag.value.kind === 'event');
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const errorText = (e, fallback) => e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || fallback;

const open = (t) => {
  tag.value = t;
  step.value = 'detail';
  quote.value = null;
  quoteError.value = '';
  const u = authUser.value;
  form.value = {
    qty: 1,
    shipping_method: 'standard',
    address: u.address || '',
    city: u.new_city || u.city || '',
    state: u.new_state || u.state || '',
    country: u.new_country || u.country || '',
    zip: '',
  };
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

const payload = () => ({ product_id: tag.value.id, ...form.value });

// The server prices the order (item, fee, tax by location) — the page never sends a price.
const loadQuote = async () => {
  if (!tag.value || isEvent.value) return;
  quoteError.value = '';
  try {
    const { data } = await axios.post(route('new_frontend.vibes.purchase.quote', { vibe: tag.value.vibe_id }), payload());
    quote.value = data;
  } catch (e) {
    quote.value = null;
    quoteError.value = errorText(e, 'Couldn’t price this item.');
  }
};

const setMethod = (m) => { form.value.shipping_method = m; loadQuote(); };
const setQty = (q) => { form.value.qty = Math.min(10, Math.max(1, q)); loadQuote(); };

// Events are bought on the event page (ticket types, add-ons); the Vibe still gets the credit.
const openEventPage = () => {
  try {
    sessionStorage.setItem('lk_vibe_ticket_ref', JSON.stringify({ event_id: tag.value.id, vibe_id: tag.value.vibe_id, at: Date.now() }));
  } catch (e) { /* private mode: the purchase just isn't credited */ }
  close();
  router.visit(route('new_frontend.events', { event: tag.value.id, vibe: tag.value.vibe_id }));
};

const goToReview = () => {
  if (isEvent.value) {
    openEventPage();
    return;
  }
  step.value = 'review';
  loadQuote();
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const buyTag = async () => {
  if (!quote.value) return;
  if (!quote.value.can_afford) {
    showToast(`Insufficient wallet balance (${money(quote.value.wallet_balance)}) — top up first`);
    return;
  }

  busy.value = true;
  try {
    const { data } = await axios.post(route('new_frontend.vibes.purchase', { vibe: tag.value.vibe_id }), {
      ...payload(),
      expected_total: quote.value.total,
    });

    showToast(`✅ ${data.message || 'Bought ' + tag.value.title} · order ${data.order_number}`);
    router.reload({ only: ['earningsStats', 'walletBalance', 'auth'] });
    close();
  } catch (e) {
    showToast(errorText(e, 'Purchase failed. Please try again.'));
    loadQuote();
  } finally {
    busy.value = false;
  }
};

const viewEvent = () => openEventPage();

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
  else alert(msg);
};

defineExpose({ open, close });
</script>
