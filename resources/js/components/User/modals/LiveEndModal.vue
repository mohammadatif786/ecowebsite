<template>
  <Modal ref="modalRef" maxWidth="max-w-[480px]">
    <div class="p-8 text-center bg-white rounded-3xl overflow-hidden shadow-2xl">
      <!-- Top Icon -->
      <div class="w-16 h-16 rounded-2xl bg-rose-500 text-white grid place-items-center mx-auto mb-4 shadow-lg shadow-rose-200">
        <i data-lucide="radio" class="w-8 h-8"></i>
      </div>

      <h3 class="text-2xl font-black text-slate-900 mb-1">Stream ended</h3>
      <p class="text-slate-500 font-bold text-sm mb-6">{{ hms(stats.secs || 0) }} · {{ num(stats.viewers || 0) }} peak viewers</p>

      <!-- Stats Grid -->
      <div class="grid grid-cols-4 gap-3 mb-6">
        <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
          <p class="text-xl font-black text-slate-950">{{ num(stats.hearts || 0) }}</p>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-tight">Hearts</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
          <p class="text-xl font-black text-slate-950">{{ num(stats.gifts || 0) }}</p>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-tight">Gifts</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
          <p class="text-xl font-black text-slate-950">{{ num(stats.coins || 0) }}</p>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-tight">Coins</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
          <p class="text-xl font-black text-slate-950">{{ stats.sold || 0 }}</p>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-tight">Sold</p>
        </div>
      </div>

      <!-- Financial Box -->
      <div class="rounded-2xl bg-[#f0fdf4] border border-[#dcfce7] p-4 text-left mb-8">
        <div class="flex justify-between items-center text-[13px] mb-2">
          <span class="text-slate-500 font-bold">Gifts gross</span>
          <b class="text-slate-900">{{ money(gross) }}</b>
        </div>
        <div class="flex justify-between items-center text-[13px] pb-2 border-b border-[#dcfce7]">
          <span class="text-slate-500 font-bold">Your 50% gift share</span>
          <b class="text-emerald-600">{{ money(share) }}</b>
        </div>
        <div class="flex justify-between items-center text-[13px] pt-2">
          <span class="text-slate-500 font-bold flex items-center gap-1.5">
             <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-slate-900"></i> Sales commission ({{ stats.sold || 0 }} sold)
          </span>
          <b class="text-emerald-600">{{ money(commission) }}</b>
        </div>
      </div>

      <!-- Actions -->
      <div class="space-y-3">
        <button @click="viewEarnings" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-lg shadow-blue-100 transition transform active:scale-95 text-[15px]">
          View earnings & transfer
        </button>
        <button @click="close" class="w-full py-4 bg-white border border-slate-100 hover:bg-slate-50 text-slate-950 font-black rounded-2xl transition transform active:scale-95 text-[15px]">
          Done
        </button>
      </div>
    </div>
  </Modal>

  <!-- Reusing the analytics modal if needed -->
  <LiveAnalyticsModal ref="analyticsModalRef" />
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from '../ui/Modal.vue';
import LiveAnalyticsModal from './LiveAnalyticsModal.vue';

const modalRef = ref(null);
const analyticsModalRef = ref(null);

const stats = ref({});

const gross = computed(() => +((stats.value.coins || 0) * 0.01).toFixed(2));
const share = computed(() => +(gross.value * 0.5).toFixed(2));
const commission = computed(() => +(stats.value.commissionEarned || 0).toFixed(2));

const num = (n) => Number(n).toLocaleString();
const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const hms = (s) => {
  const m = Math.floor(s / 60);
  const x = s % 60;
  return (m < 10 ? '0' : '') + m + ':' + (x < 10 ? '0' : '') + x;
};

const open = (liveStats) => {
  stats.value = { ...liveStats };
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) setTimeout(() => window.lucide.createIcons(), 50);
  }
};

const close = () => { if (modalRef.value) modalRef.value.close(); };

const viewEarnings = () => {
  close();
  // Usually navigates to the analytics tab in the main studio or opens a modal
  if (window.showStudioAnalytics) {
     window.showStudioAnalytics();
  } else if (analyticsModalRef.value) {
     analyticsModalRef.value.open();
  }
};

defineExpose({ open, close });
</script>
