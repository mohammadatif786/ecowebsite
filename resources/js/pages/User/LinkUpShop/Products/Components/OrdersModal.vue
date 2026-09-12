<script setup lang="ts">
import { onMounted, ref, watch, nextTick } from 'vue';
import axios from 'axios';
import OrderTracking from './modals/OrderTracking.vue';
import { Map, Package } from 'lucide-vue-next';

const props = defineProps<{ show: boolean }>();
const emit = defineEmits<{ (e: 'close'): void }>();

const loading = ref(false);
const error = ref<string | null>(null);
const orders = ref<any[]>([]);
const showTrack = ref(false);
const trackOrder = ref<any | null>(null);

async function loadOrders() {
  loading.value = true; error.value = null;
  try {
    const res = await axios.get('/orders', { headers: { Accept: 'application/json' } });
    orders.value = res?.data?.orders || [];
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Failed to load orders.';
  } finally {
    loading.value = false;
  }
}

watch(() => props.show, async (v) => {
  if (v) {
    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
    loadOrders();
  }
});

onMounted(() => {
  if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
  }
});
</script>

<template>
  <div v-if="show" class="orders-modal-backdrop" @click="e => { if (e.target === e.currentTarget) emit('close') }">
    <div class="card modal p-4 md:p-6">
      <div class="flex items-start gap-3">
        <div class="w-30 h-30 rounded-2xl grid place-items-center" style="margin-top: -30px;">
          <img src="/storage/avatars/marketplacelog.png" alt="Marketplace Logo" class="w-full h-full" />
        </div>
        <div class="flex-1">
          <div class="text-xl font-black">Orders</div>
          <div class="text-sm mt-1" style="color:#64748b;">Track your recent orders.</div>
        </div>
        <button class="btn2" @click="emit('close')">
          <span class="inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x"
              class="lucide lucide-x w-5 h-5">
              <path d="M18 6 6 18"></path>
              <path d="m6 6 12 12"></path>
            </svg>
            Close</span>
        </button>
      </div>

      <div class="mt-5">
        <div v-if="error" class="card p-3" style="border-color:rgba(239,68,68,.35);">
          <div class="inline-flex items-center gap-2" style="color:#991b1b;">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            <span class="font-black">{{ error }}</span>
          </div>
        </div>

        <div v-else-if="loading" class="text-sm" style="color:#64748b;">Loading…</div>

        <div v-else>
          <div v-if="orders.length === 0" class="card p-10 text-center">
            <div class="text-2xl font-black">No orders yet</div>
            <div class="mt-2" style="color:#64748b;">Checkout from cart to create an order.</div>
          </div>

          <div v-else class="grid gap-3">
            <div v-for="o in orders" :key="o.id" class="card p-4">
              <!-- Header line -->
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="font-black">Order {{ o.no || o.id }}</div>
                  <div class="text-sm mt-1" style="color:#64748b;">
                    {{ (o.shipping_method === 'standard' ? 'PICKUP' : 'DELIVERY') }} • {{ o.status || 'Processing' }} •
                    ${{ Number(o.total || 0).toFixed(2) }} • {{ (o.payment || 'card') === 'wallet' ? 'Paid: Wallet' :
                      'Paid: Card' }}
                  </div>
                  <div class="tiny mt-1" style="color:#64748b;">Buyer: {{ o.ship?.name || '-' }} • {{ o.date }}</div>
                  <div v-if="o.latest_tracking" class="tiny mt-1" style="color:#64748b;">
                    Last update: <span class="font-black">{{ o.latest_tracking.status }}</span> • {{
                      o.latest_tracking.date }}
                  </div>
                </div>
                <button class="btn2" @click="trackOrder = o; showTrack = true">
                  <span class="inline-flex items-center gap-2">
                    <Map /> Track</span>
                </button>
              </div>

              <!-- Items list -->
              <div class="mt-3 grid gap-2">
                <div v-for="it in o.items" :key="it.id" class="flex items-start justify-between gap-3">
                  <div class="flex items-start gap-3">
                    <img v-if="it.image" :src="it.image" alt="" class="w-12 h-12 rounded-xl object-cover border"
                      style="border-color:rgba(148,163,184,.35);" />
                    <div>
                      <div class="font-black">{{ it.product || 'Item' }}</div>
                      <div class="tiny" style="color:#64748b;">Qty {{ it.quantity }} • ${{
                        Number(it.unitPrice || 0).toFixed(2) }}</div>
                      <div class="tiny mt-1" style="color:#64748b;">
                        <span v-if="it.listing_type" class="tag" :class="{
                          'badge-admin': it.listing_type === 'Administrative' || it.listing_type === 'Administrator',
                          'badge-store': it.listing_type === 'Store',
                          'badge-group': it.listing_type === 'Carnival Group',
                          'badge-individual': it.listing_type === 'Individual'
                        }">{{ it.listing_type }}</span>
                        <span v-if="it.seller"> • Seller: <span class="font-black">{{ it.seller }}</span></span>
                      </div>
                    </div>
                  </div>
                  <div class="font-black">${{ (Number(it.unitPrice || 0) * Number(it.quantity || 0)).toFixed(2) }}</div>
                </div>
              </div>

              <!-- Totals Breakdown -->
              <div class="mt-4 pt-3 border-t border-slate-50 space-y-1">
                <div class="flex justify-between text-xs text-slate-500">
                  <span>Subtotal</span>
                  <span class="font-bold">${{ Number(o.subtotal || 0).toFixed(2) }}</span>
                </div>
                <div v-if="Number(o.fee_amount || 0) > 0" class="flex justify-between text-xs text-slate-500">
                  <span>{{ o.fee_label || 'Marketplace Fee' }}</span>
                  <span class="font-bold">${{ Number(o.fee_amount || 0).toFixed(2) }}</span>
                </div>
                <div v-if="Number(o.process_fee_amount || 0) > 0" class="flex justify-between text-xs text-slate-500">
                  <span>Processing Fee</span>
                  <span class="font-bold">${{ Number(o.process_fee_amount || 0).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between pt-1 mt-1 border-t border-slate-50">
                  <span class="font-black">Total</span>
                  <span class="font-black text-sky-600">${{ Number(o.total || 0).toFixed(2) }}</span>
                </div>
              </div>

              <!-- Action chips (demo look) -->
              <div class="mt-4 flex flex-wrap gap-2">
                <button class="btn2 tiny" disabled>Set Ready for Pickup</button>
                <button class="btn2 tiny" disabled>Set Picked Up</button>
                <button class="btn2 tiny" disabled>Set Out for Delivery</button>
                <button class="btn2 tiny" disabled>Set Delivered</button>
              </div>
              <div class="tiny mt-2" style="color:#64748b;">(Demo) These buttons simulate backend progress updates the
                buyer can track.</div>

              <!-- Seller transfers (placeholder) -->
              <div class="mt-4 card p-3">
                <div class="font-black">Seller Transfers (Escrow → Wallet)</div>
                <div class="tiny mt-1" style="color:#64748b;">Funds are protected until <b>Delivered</b>. Then each
                  seller can transfer their earnings to their wallet.</div>
                <div class="mt-3 grid gap-2">
                  <div class="tiny" style="color:#64748b;">No seller amounts found.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <OrderTracking :show="show && showTrack && !!trackOrder" :order="trackOrder"
    @close="() => { showTrack = false; trackOrder = null; }" />
</template>

<style scoped>
.orders-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, .55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5000;
  padding: 18px;
}

.modal {
  width: min(920px, 100%);
  max-height: 90vh;
  overflow: auto;
}

.card {
  background: #ffffff;
  border-radius: 22px;
  box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
  border: 1px solid rgba(148, 163, 184, .35);
}

.btn2 {
  border: 1px solid rgba(148, 163, 184, .35);
  border-radius: 16px;
  font-weight: 900;
  padding: .5rem .75rem;
  background: #fff;
}

.tiny {
  font-size: .75rem;
}
</style>
