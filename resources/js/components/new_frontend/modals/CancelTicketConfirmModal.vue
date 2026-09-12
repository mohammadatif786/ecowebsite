<template>
  <Modal ref="modalRef" maxWidth="max-w-[460px]">
    <div v-if="sale" class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-slate-900">Cancel Ticket?</h2>
        <button @click="close" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-black text-slate-900">{{ eventName }}</h3>
        <p class="text-sm text-slate-400 font-bold">#{{ sale.id }} · {{ money(totalPaid) }}</p>
      </div>

      <div class="bg-orange-50 rounded-2xl p-4 border border-orange-100 mb-6">
        <p class="text-sm font-black text-orange-800 mb-2">Please review this organizer's policy before cancelling.</p>
        <p class="text-sm text-orange-700 italic">"Full refund up to 48 hours before your appointment."</p>
      </div>

      <div class="space-y-3 mb-8">
        <div class="flex justify-between items-center text-sm font-bold text-slate-500">
          <span>Ticket price</span>
          <span class="text-slate-900">{{ money(totalPaid) }}</span>
        </div>
        <div class="flex justify-between items-center text-sm font-bold text-slate-400">
          <span>- Service & processing fee (non-refundable)</span>
          <span>{{ money(serviceFee) }}</span>
        </div>
        <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
          <span class="text-base font-black text-slate-900">You'll get back</span>
          <span class="text-xl font-black text-emerald-600">{{ money(refundAmount) }}</span>
        </div>
      </div>

      <p class="text-[13px] text-slate-500 leading-relaxed mb-6">
        Questions or a special circumstance? Contact <span class="font-black text-slate-700">{{ organizerName }}</span> before cancelling — they may be able to help.
      </p>

      <div class="grid grid-cols-2 gap-3 mb-3">
        <button @click="contactPromoter" class="btn bg-white border border-slate-200 py-4 rounded-xl text-sm font-black text-slate-900 hover:bg-slate-50 transition-colors">
          Contact Promoter
        </button>
        <button @click="confirmCancel" class="btn bg-rose-600 hover:bg-rose-700 py-4 rounded-xl text-sm font-black text-white shadow-lg shadow-rose-200 transition-all">
          Cancel & Refund
        </button>
      </div>

      <button @click="close" class="w-full py-4 text-sm font-black text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
        Keep My Ticket
      </button>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['cancel', 'contact']);

const modalRef = ref(null);
const sale = ref(null);

const event = computed(() => sale.value?.event || {});
const eventName = computed(() => event.value?.title || sale.value?.event_name || 'Event');
const organizerName = computed(() => event.value?.organizer_name || 'the organizer');
const totalPaid = computed(() => Number(sale.value?.total ?? sale.value?.paid ?? sale.value?.stripe_price ?? 0));
const serviceFee = computed(() => Number(sale.value?.fee || 0));
const refundAmount = computed(() => Math.max(0, totalPaid.value - serviceFee.value));

const money = (n) => (event.value?.currency_symbol || '$') + Number(n || 0).toFixed(2);

const open = (ticketSale) => {
  sale.value = ticketSale;
  if (modalRef.value) {
    modalRef.value.open();
    nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const contactPromoter = () => {
  emit('contact', sale.value);
};

const confirmCancel = () => {
  emit('cancel', {
    id: sale.value.id,
    refund: refundAmount.value,
    reason: 'User cancelled via frontend'
  });
};

defineExpose({ open, close });
</script>
