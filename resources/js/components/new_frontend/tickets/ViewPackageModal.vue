<template>
  <div v-if="open && ticket" class="fixed inset-0 z-[190] flex items-center justify-center bg-slate-900/40 px-4 py-6">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl">
      <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
        <div>
          <p class="text-lg font-black text-slate-900">Package: {{ packageData?.name || ticket.name }}</p>
          <p class="text-sm text-slate-500">{{ ticket.name }}</p>
        </div>
        <button
          type="button"
          class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-500 hover:bg-slate-50"
          @click="$emit('close')"
        >
          Close
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto p-5">
        <TicketPackageDetails :ticket="ticket" :format-price="formatPrice" />
      </div>

      <div class="border-t border-slate-100 px-5 py-4 text-right">
        <button
          type="button"
          class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50"
          @click="$emit('close')"
        >
          Done
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import TicketPackageDetails from './TicketPackageDetails.vue';
import { getTicketPackage } from './ticketPackages';

const props = defineProps({
  open: { type: Boolean, default: false },
  ticket: { type: Object, default: null },
  formatPrice: { type: Function, required: true },
});

defineEmits(['close']);

const packageData = computed(() => getTicketPackage(props.ticket));
</script>
