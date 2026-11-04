<template>
  <div class="card overflow-hidden h-full flex flex-col rounded-[18px]">
    <div class="relative h-[200px] shrink-0">
      <img :src="group.img || '/images/event-placeholder.jpg'" class="w-full h-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

      <span class="absolute bottom-3 left-3 bg-black/70 text-white text-[10px] font-black px-2 py-1 rounded backdrop-blur-sm">
        {{ group.tickets?.[0]?.date || 'TBA' }}
      </span>
      <span class="absolute top-3 right-3 bg-lkblue2 text-white text-[11px] font-black px-2.5 py-1 rounded-full shadow-lg">
        {{ ticketCount }} Ticket{{ ticketCount !== 1 ? 's' : '' }}
      </span>
    </div>

    <div class="p-3 flex-1 flex flex-col">
      <h4 class="font-black text-lg leading-tight">{{ group.event }}</h4>
      <p class="text-[11px] text-slate-400 mt-1 mb-3 line-clamp-1">{{ group.event_desc || 'LinkUp Event' }}</p>

      <div class="space-y-1 mb-3 rounded-xl bg-slate-50 px-3 py-2 text-[12px] font-semibold">
        <div class="flex justify-between items-center text-slate-500">
          <span>Total Purchases:</span>
          <b class="text-slate-900">{{ purchaseCount }}</b>
        </div>
        <div class="flex justify-between items-center text-slate-500">
          <span>Total Tickets:</span>
          <b class="text-slate-900">{{ ticketCount }}</b>
        </div>
        <div class="flex justify-between items-center text-slate-500">
          <span>Total Paid:</span>
          <b class="text-emerald-600 font-black">{{ money(group.total) }}</b>
        </div>
      </div>

      <button
        @click="isExpanded = !isExpanded"
        class="w-full py-2 text-[11px] font-black text-slate-700 border border-slate-200 rounded-xl flex items-center justify-center gap-2 hover:bg-slate-50 transition mb-2"
      >
        {{ isExpanded ? 'Hide Details' : 'Show All Purchases' }}
        <i data-lucide="chevron-down" :class="['w-4 h-4 transition-transform', isExpanded ? 'rotate-180' : '']"></i>
      </button>

      <button
        v-if="!isExpanded"
        @click="showQR(group.tickets?.[0])"
        class="w-full py-3 transition text-white rounded-xl font-black text-sm shadow-md"
        style="background:linear-gradient(90deg,#2f9bef,#2563eb)"
      >
        View E-Ticket
      </button>

      <div v-if="isExpanded" class="mt-4 pt-4 border-t border-slate-100 space-y-4">
        <div v-for="t in group.tickets" :key="t.id" class="rounded-2xl bg-white border border-slate-100 p-3 shadow-sm">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h5 class="font-black text-lg text-slate-950">{{ Number(t.quantity || 1) }}Ticket{{ Number(t.quantity || 1) !== 1 ? 's' : '' }}</h5>
              <p class="text-[11px] text-slate-400 font-bold mt-0.5">{{ t.date }}</p>
              <p class="text-[10px] text-slate-300 font-bold">Order #{{ t.id }}</p>
            </div>
            <div class="text-right">
              <p class="text-emerald-600 font-black text-lg">{{ money(ticketPaid(t)) }}</p>
              <p class="text-[9px] text-slate-400 font-black uppercase">Total Paid</p>
            </div>
          </div>

          <div class="flex justify-between items-center mb-4">
            <span class="text-xs font-bold text-slate-500">Total Quantity: {{ Number(t.quantity || 1) }}</span>
            <span :class="statusClass" class="px-2 py-0.5 rounded-full text-[10px] font-black border">{{ statusLabel }}</span>
          </div>

          <div class="bg-slate-50/70 rounded-2xl p-3 border border-slate-100 mb-3">
            <p class="text-[9px] font-black text-slate-700 uppercase mb-3 tracking-widest">Tickets in this purchase:</p>
            <div class="flex items-center justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold truncate">
                  {{ ticketLabel(t) }}
                  <span v-if="t.checkin" class="text-emerald-600"> - checked-in</span>
                </p>
                <p class="text-[10px] text-slate-400 font-bold">#{{ t.qr || t.id }} - {{ money(ticketPaid(t)) }}</p>
              </div>
              <div class="flex gap-1.5">
                <button @click="showQR(t)" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition shadow-sm">
                  <i data-lucide="qr-code" class="w-3.5 h-3.5 text-slate-600"></i>
                </button>
                <button v-if="!cancelled" @click="cancelTicket(t)" class="px-3 py-1 bg-white border border-rose-100 rounded-lg hover:bg-rose-50 text-rose-500 transition shadow-sm text-[10px] font-black">
                  Cancel
                </button>
              </div>
            </div>
          </div>

          <button
            @click="showQR(t)"
            class="w-full py-3 transition text-white rounded-xl font-black text-sm shadow-md"
            style="background:linear-gradient(90deg,#2f9bef,#2563eb)"
          >
            View E-Ticket
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';

const props = defineProps({
  group: { type: Object, required: true },
  cancelled: { type: Boolean, default: false }
});

const emit = defineEmits(['qr', 'cancel']);
const isExpanded = ref(false);

const money = (n) => '$' + Number(n || 0).toFixed(2);

const purchaseCount = computed(() => (props.group.tickets || []).length);

const ticketCount = computed(() => {
  return Number(props.group.ticket_count || 0) || (props.group.tickets || []).reduce((sum, ticket) => {
    return sum + Math.max(1, Number(ticket.quantity || 1));
  }, 0);
});

const statusLabel = computed(() => props.cancelled ? 'cancelled' : 'confirmed');
const statusClass = computed(() => {
  return props.cancelled
    ? 'bg-rose-50 text-rose-600 border-rose-100'
    : 'bg-emerald-50 text-emerald-600 border-emerald-100';
});

const ticketPaid = (ticket) => {
  const fallback = purchaseCount.value ? Number(props.group.total || 0) / purchaseCount.value : 0;
  return Number(ticket?.paid ?? ticket?.price ?? fallback);
};

const ticketLabel = (ticket) => {
  return ticket?.ticket_type || ticket?.ticket_name || 'Ticket';
};

const showQR = (ticket) => {
  if (!ticket) return;
  emit('qr', ticket);
};

const cancelTicket = (ticket) => {
  if (!ticket) return;
  emit('cancel', ticket);
};

watch(isExpanded, () => {
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
});

onMounted(() => {
  if (window.lucide) window.lucide.createIcons();
});
</script>
