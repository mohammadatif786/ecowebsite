<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
      <div class="p-5 text-white" style="background:linear-gradient(135deg,#0b2942,#1e3a5f)">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-black">Requested Money</h3>
          <button @click="close" class="hover:bg-white/20 p-1 rounded transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <p class="text-white/70 text-xs mt-1 font-bold">See who is asking before you pay.</p>
      </div>

      <div class="p-4 space-y-3 max-h-[70vh] overflow-y-auto">
        <div v-if="form.errors.transaction" class="text-[12px] font-bold text-rose-600 bg-rose-50 p-3 rounded-xl">
          {{ form.errors.transaction }}
        </div>

        <div v-for="request in requests" :key="request.id" class="border border-slate-100 rounded-2xl p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <img :src="request.person.img" class="h-12 w-12 rounded-full object-cover" />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="font-black text-sm truncate">{{ request.person.name }}</p>
                <span :class="['rounded-full px-2 py-0.5 text-[9px] font-black uppercase shrink-0', statusClass(request.status)]">
                  {{ request.status }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 font-bold mt-0.5">
                {{ request.direction === 'incoming' ? 'Requested from you' : 'You requested' }}
              </p>
              <p v-if="request.note" class="text-[11px] text-slate-500 font-bold mt-1 truncate">{{ request.note }}</p>
            </div>
            <span class="font-black text-lg">{{ money(request.amount) }}</span>
          </div>

          <div v-if="request.status === 'pending'" class="grid gap-2 mt-3" :class="request.direction === 'incoming' ? 'grid-cols-2' : 'grid-cols-1'">
            <template v-if="request.direction === 'incoming'">
              <button @click="pay(request)" :disabled="form.processing" class="btn btn-primary py-2 text-xs shadow-sm disabled:opacity-60">
                Pay
              </button>
              <button @click="reject(request)" :disabled="form.processing" class="rounded-xl bg-rose-50 text-rose-600 py-2 text-xs font-black hover:bg-rose-100 transition disabled:opacity-60">
                Reject
              </button>
            </template>
            <button v-else @click="cancel(request)" :disabled="form.processing" class="rounded-xl bg-slate-100 text-slate-700 py-2 text-xs font-black hover:bg-slate-200 transition disabled:opacity-60">
              Cancel Request
            </button>
          </div>
        </div>

        <div v-if="!requests.length" class="text-center py-6 text-slate-400 font-bold">
          No money requests.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
  requests: { type: Array, default: () => [] },
});

const emit = defineEmits(['request-paid', 'request-updated']);

const isOpen = ref(false);
const form = useForm({});

const money = n => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const statusClass = (status) => ({
  pending: 'bg-amber-100 text-amber-700',
  accepted: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-rose-100 text-rose-700',
  cancelled: 'bg-slate-100 text-slate-600',
})[status] || 'bg-slate-100 text-slate-600';

const open = () => {
  form.clearErrors();
  isOpen.value = true;
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

const close = () => {
  form.clearErrors();
  isOpen.value = false;
};

const postAction = (url, onSuccess) => {
  form.post(url, {
    preserveScroll: true,
    onSuccess,
  });
};

const pay = (request) => {
  postAction(route('new_frontend.wallet.requested_money.pay', request.id), () => {
    emit('request-paid', request);
  });
};

const reject = (request) => {
  postAction(route('new_frontend.wallet.requested_money.reject', request.id), () => {
    emit('request-updated', { id: request.id, status: 'rejected' });
  });
};

const cancel = (request) => {
  postAction(route('new_frontend.wallet.requested_money.cancel', request.id), () => {
    emit('request-updated', { id: request.id, status: 'cancelled' });
  });
};

defineExpose({ open, close });
</script>
