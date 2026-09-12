<script setup lang="ts">
import { Map } from 'lucide-vue-next';
import { nextTick, watch, computed, ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  show: boolean;
  order: any | null;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const trackings = computed(() => {
  const list = (props.order?.trackings || []) as Array<any>;
  return Array.isArray(list) ? list : [];
});

const isDelivered = computed(() => {
  const s = (props.order?.status || props.order?.latest_tracking?.status || '').toString().toLowerCase();
  return s === 'delivered';
});

const showConfirm = ref(false);
const received = ref<boolean | null>(null);
const note = ref('');
const submitting = ref(false);
const submitError = ref<string | null>(null);

const closeConfirm = () => {
  showConfirm.value = false;
  received.value = null;
  note.value = '';
  submitError.value = null;
};

const submitBuyerReceived = async () => {
  if (!props.order?.id) return;
  if (received.value === null) {
    submitError.value = 'Please select Yes or No.';
    return;
  }
  if (!note.value.trim()) {
    submitError.value = 'Note is required.';
    return;
  }

  submitting.value = true;
  submitError.value = null;
  try {
    const res = await axios.post(`/orders/${props.order.id}/buyer-received`, {
      received: received.value,
      note: note.value,
    }, {
      headers: { Accept: 'application/json' }
    });

    props.order.status = res?.data?.order_status || 'received_buyer';
    const trackingStatus = res?.data?.tracking_status || (received.value ? 'buyer_received' : 'buyer_not_received');
    const now = new Date();
    const tracking = {
      status: trackingStatus,
      note: note.value,
      date: now.toISOString().slice(0, 19).replace('T', ' '),
      meta: { received: received.value, by: 'buyer' },
    };

    if (Array.isArray(props.order.trackings)) {
      props.order.trackings.unshift(tracking);
    } else {
      props.order.trackings = [tracking];
    }

    props.order.latest_tracking = {
      status: tracking.status,
      note: tracking.note,
      date: tracking.date,
    };

    closeConfirm();
  } catch (e: any) {
    submitError.value = e?.response?.data?.message || 'Failed to submit confirmation.';
  } finally {
    submitting.value = false;
  }
};

watch(() => props.show, async (v) => {
  if (v) {
    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  } else {
    closeConfirm();
  }
});
</script>

<template>
  <div v-if="show && order" class="orders-modal-backdrop" @click="e => { if (e.target === e.currentTarget) emit('close') }">
    <div class="card modal p-4 md:p-6">
      <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-2xl grid place-items-center text-white" style="background:linear-gradient(135deg, rgba(14,165,233,1), rgba(34,197,94,1));">
          <Map />
        </div>
        <div class="flex-1">
          <div class="text-xl font-black">Track Order {{ order?.no || order?.id }}</div>
          <div class="text-sm mt-1" style="color:#64748b;">Latest update • {{ order?.latest_tracking?.date || order?.date }}</div>
        </div>
        <button class="btn2" @click="emit('close')">
          <span class="inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x"
              class="lucide lucide-x w-5 h-5">
              <path d="M18 6 6 18"></path>
              <path d="m6 6 12 12"></path>
            </svg> Close</span>
        </button>
      </div>

      <div class="mt-5 grid gap-3">
        <!-- Status summary -->
        <div class="card p-3">
          <div class="font-black">Current Status</div>
          <div class="tiny mt-1" style="color:#64748b;">
            {{ (order?.status || order?.latest_tracking?.status || 'Processing') }} • {{ order?.latest_tracking?.date || order?.date }}
          </div>

          <div v-if="isDelivered" class="mt-3">
            <button class="btn2 tiny" @click="showConfirm = true">
              Did you get your product?
            </button>
          </div>
        </div>

        <!-- Timeline -->
        <div class="card p-4">
          <div class="font-black text-lg">Timeline</div>
          <div class="mt-3 grid gap-3">
            <div v-if="!trackings.length" class="tiny" style="color:#64748b;">No tracking updates yet.</div>
            <div v-else v-for="(t, idx) in trackings" :key="idx" class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-full grid place-items-center text-white"
                   :style="{background: idx === 0 ? 'linear-gradient(135deg, rgba(34,197,94,1), rgba(14,165,233,1))' : 'linear-gradient(135deg, rgba(148,163,184,1), rgba(100,116,139,1))'}">
                <i :data-lucide="idx === 0 ? 'check' : 'clock'" class="w-4 h-4"></i>
              </div>
              <div class="flex-1">
                <div class="font-black">{{ t.status }}</div>
                <div class="tiny mt-1" style="color:#64748b;">{{ t.note }}</div>
                <div class="tiny mt-1" style="color:#64748b;">{{ t.date }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Items snapshot -->
        <div class="card p-4">
          <div class="font-black text-lg">Items</div>
          <div class="mt-2 grid gap-2">
            <div v-for="it in (order?.items||[])" :key="it.id" class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <img v-if="it.image" :src="it.image" alt="" class="w-12 h-12 rounded-xl object-cover border" style="border-color:rgba(148,163,184,.35);"/>
                <div>
                  <div class="font-black">{{ it.product || 'Item' }}</div>
                  <div class="tiny" style="color:#64748b;">Qty {{ it.quantity }} • ${{ Number(it.unitPrice||0).toFixed(2) }}</div>
                </div>
              </div>
              <div class="font-black">${{ (Number(it.unitPrice||0) * Number(it.quantity||0)).toFixed(2) }}</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div v-if="show && order && showConfirm" class="orders-modal-backdrop" @click="e => { if (e.target === e.currentTarget) closeConfirm() }">
    <div class="card modal p-4 md:p-6">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-xl font-black">Did you get your product?</div>
          <div class="tiny mt-1" style="color:#64748b;">Please confirm delivery. Note is required.</div>
        </div>
        <button class="btn2" @click="closeConfirm">Close</button>
      </div>

      <div class="mt-4 grid gap-3">
        <div class="card p-3">
          <div class="font-black">Your answer</div>
          <div class="mt-2 flex gap-4 tiny" style="color:#0f172a;">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="received" :checked="received === true" @change="received = true" />
              Yes
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="received" :checked="received === false" @change="received = false" />
              No
            </label>
          </div>
        </div>

        <div class="card p-3">
          <div class="font-black">Note</div>
          <textarea class="input mt-2" rows="4" v-model="note" placeholder="Write a note..."></textarea>
        </div>

        <div v-if="submitError" class="tiny" style="color:#991b1b; font-weight:800;">
          {{ submitError }}
        </div>

        <div class="flex justify-between gap-2">
          <button class="btn2" @click="closeConfirm" :disabled="submitting">Cancel</button>
          <button class="btn" @click="submitBuyerReceived" :disabled="submitting">
            {{ submitting ? 'Submitting…' : 'Submit' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.orders-modal-backdrop{position:fixed; inset:0; background:rgba(2,6,23,.55); display:flex; align-items:center; justify-content:center; z-index:5000; padding:18px;}
.modal{ width:min(920px, 100%); max-height:90vh; overflow:auto; }
.card{ background:#ffffff; border-radius:22px; box-shadow:0 18px 40px rgba(2,6,23,.10); border:1px solid rgba(148,163,184,.35); }
.btn2{ border:1px solid rgba(148,163,184,.35); border-radius:16px; font-weight:900; padding:.5rem .75rem; background:#fff; }
.btn{ background: linear-gradient(135deg, rgba(14, 165, 233, 1), rgba(34, 197, 94, 1)); color:#fff; border-radius:16px; font-weight:900; padding:.5rem .75rem; }
.input{ width:100%; border:1px solid rgba(148,163,184,.45); border-radius:16px; padding:.75rem .9rem; outline:none; background:#ffffff; }
.tiny{ font-size:.75rem; }
</style>
