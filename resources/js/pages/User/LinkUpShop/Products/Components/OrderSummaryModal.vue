<script setup lang="ts">
import { nextTick, watch } from 'vue';

const props = defineProps<{
  show: boolean;
  order: any;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

watch(() => props.show, async (v) => {
  if (v) {
    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }
});
</script>

<template>
  <div v-if="show && order" class="order-modal-backdrop"
    @click="e => { if (e.target === e.currentTarget) emit('close') }">
    <div class="card modal p-4 md:p-6">
      <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-2xl grid place-items-center text-white"
          style="background:linear-gradient(135deg, rgba(14,165,233,1), rgba(34,197,94,1));">
          <i data-lucide="receipt" class="w-6 h-6"></i>
        </div>
        <div class="flex-1">
          <div class="text-xl font-black">Order Placed</div>
          <div class="text-sm mt-1" style="color:#64748b;">Order: {{ order?.no || order?.id }} • {{ order?.date }}</div>
        </div>
        <button class="btn2" @click="emit('close')">
          <span class="inline-flex items-center gap-2"><i data-lucide='x' class="w-5 h-5"></i> Close</span>
        </button>
      </div>

      <div class="mt-5">
        <div class="card p-3" style="border-color:rgba(34,197,94,.22);">
          <div class="inline-flex items-center gap-2" style="color:#166534;">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="font-black">Order placed successfully!</span>
          </div>
        </div>
      </div>

      <div class="mt-4 grid md:grid-cols-2 gap-4">
        <div class="card p-4">
          <div class="font-black text-lg">Buyer</div>
          <div class="mt-2 text-sm" style="color:#64748b;">
            <div><span class="font-black">{{ order?.ship?.name }}</span></div>
            <div v-if="order?.ship?.address">{{ order?.ship?.address }}</div>
            <div v-if="order?.ship?.city">{{ order?.ship?.city }}</div>
          </div>

          <div class="mt-4 card p-3">
            <div class="font-black">Fulfillment</div>
            <div class="mt-2 text-sm" style="color:#64748b;">
              <div class="tiny">Method</div>
              <div>{{ order?.shipping_method === 'standard' ? 'Pickup' : 'Delivery' }}</div>
            </div>
          </div>

          <div v-if="order?.notes" class="mt-4 card p-3">
            <div class="font-black">Notes</div>
            <div class="mt-1 text-sm" style="color:#64748b;">{{ order?.notes }}</div>
          </div>
        </div>

        <div class="card p-4">
          <div class="font-black text-lg">Items</div>
          <div class="mt-2 grid gap-2">
            <div v-for="it in order?.items" :key="it.id" class="flex items-start justify-between">
              <div>
                <div class="font-black">{{ it.product || 'Item' }}</div>
                <div class="tiny" style="color:#64748b;">Qty {{ it.quantity }}</div>
              </div>
              <div class="text-right font-black">${{ (Number(it.unitPrice || 0) * Number(it.quantity || 0)).toFixed(2)
                }}
              </div>
            </div>
          </div>

          <div class="mt-4 border-t pt-4 space-y-2" style="border-color:rgba(148,163,184,.35);">
            <div class="flex items-center justify-between text-sm">
              <div style="color:#64748b;">Subtotal</div>
              <div class="font-bold">${{ Number(order?.subtotal || 0).toFixed(2) }}</div>
            </div>
            <div v-if="Number(order?.fee_amount || 0) > 0" class="flex items-center justify-between text-sm">
              <div style="color:#64748b;">{{ order?.fee_label || 'Marketplace Fee' }}</div>
              <div class="font-bold">${{ Number(order?.fee_amount || 0).toFixed(2) }}</div>
            </div>
            <div class="flex items-center justify-between pt-2 border-t" style="border-color:rgba(148,163,184,0.1);">
              <div class="font-black">Total</div>
              <div class="font-black text-xl text-sky-600">${{ Number(order?.total || 0).toFixed(2) }}</div>
            </div>
            <div class="tiny mt-1" style="color:#64748b;">Status: <span class="font-black">{{ order?.status }}</span> •
              Payment: <span class="font-black">{{ (order?.payment || 'card') === 'wallet' ? 'Wallet' : 'Card' }}</span>
            </div>
            <button @click="emit('close')" class="btn2 mt-3 w-full inline-flex items-center justify-center gap-2">
              <i data-lucide="list" class="w-5 h-5"></i>
              Continue Shopping
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.order-modal-backdrop {
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

.font-black {
  font-weight: 900;
}
</style>
