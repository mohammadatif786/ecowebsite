<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden">
      <div class="p-5 flex items-center justify-between border-b border-slate-100">
        <div>
          <h3 class="text-xl font-black">Wallet Activity</h3>
          <p class="text-xs text-slate-400 font-bold mt-0.5">Send, requests, withdrawals, and wallet/coin history.</p>
        </div>
        <button @click="close" class="hover:bg-slate-100 p-1 rounded" aria-label="Close activity"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>

      <div class="p-4 border-b border-slate-100 space-y-3">
        <div class="flex gap-2 overflow-x-auto hide-scroll pb-1">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="['tab-button', activeTab === tab.id ? 'tab-button-active' : '']">{{ tab.label }}</button>
        </div>
        <input v-model.trim="search" type="search" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue" placeholder="Search activity" />
      </div>

      <div class="p-4 space-y-2 max-h-[60vh] overflow-y-auto bg-slate-50/50">
        <div v-for="item in filteredActivities" :key="item.key" class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:border-slate-200 transition">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="font-black text-sm truncate">{{ item.title }}</p>
              <p class="text-[11px] text-slate-400 font-bold mt-0.5">{{ item.dateLabel }}<span v-if="item.counterparty"> · {{ item.counterparty }}</span></p>
              <p v-if="item.note" class="text-[11px] text-slate-500 font-bold mt-1 truncate">{{ item.note }}</p>
            </div>
            <span :class="['font-black text-lg shrink-0', item.isPositive ? 'text-emerald-600' : 'text-rose-600']">{{ item.isPositive ? '+' : '-' }}{{ money(item.amount) }}</span>
          </div>
          <div class="mt-3 flex items-center justify-between gap-3">
            <span :class="['rounded-full px-2.5 py-1 text-[10px] font-black uppercase', statusClass(item.status)]">{{ item.status }}</span>
            <span v-if="item.runningBalance !== null" class="text-[11px] text-slate-400 font-bold">Balance {{ money(item.runningBalance) }}</span>
          </div>
        </div>
        <div v-if="!filteredActivities.length" class="text-center py-10 text-slate-400 font-bold">No {{ activeTab === 'all' ? '' : tabs.find(tab => tab.id === activeTab)?.label.toLowerCase() + ' ' }}activity yet.</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';

const props = defineProps({
  activities: { type: Array, default: () => [] },
  moneyRequests: { type: Array, default: () => [] },
  subscriptions: { type: Array, default: () => [] },
  bankWithdrawals: { type: Array, default: () => [] },
});

const isOpen = ref(false);
const activeTab = ref('all');
const search = ref('');
const tabs = [
  { id: 'deposits', label: 'Deposits' }, { id: 'sent', label: 'Sent' }, { id: 'received', label: 'Received' },
  { id: 'requested', label: 'Requested' }, { id: 'withdrawals', label: 'Withdrawals' },
  { id: 'wallet-coins', label: 'Wallet & Coins' }, { id: 'all', label: 'All' },
];

const money = (value) => '$' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const dateLabel = (value) => value ? new Date(value).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) : 'Today';
const normalize = (item, index, overrides = {}) => ({
  key: `${overrides.kind || item.kind || 'activity'}-${item.id || index}`,
  kind: overrides.kind || item.kind || 'activity',
  title: overrides.title || item.title || 'Wallet activity', amount: Math.abs(Number(item.amount || 0)),
  isPositive: overrides.isPositive ?? Boolean(item.isPositive), status: String(item.status || 'success').toLowerCase(),
  counterparty: overrides.counterparty ?? item.counterparty ?? '', note: overrides.note ?? item.note ?? '',
  runningBalance: item.runningBalance ?? null, date: item.date || item.created_at || null,
  dateLabel: item.dateLabel || dateLabel(item.date || item.created_at),
});

const allActivities = computed(() => {
  const wallet = props.activities.map((item, index) => normalize(item, index, { kind: item.isPositive ? 'received' : 'sent' }));
  const requests = props.moneyRequests.map((request, index) => normalize(request, index, {
    kind: 'requested', title: request.direction === 'incoming' ? `Request from ${request.person?.name || 'LinkUp User'}` : `Request to ${request.person?.name || 'LinkUp User'}`,
    isPositive: request.direction === 'incoming', counterparty: request.person?.tag || request.person?.name || '',
  }));
  const ledger = props.subscriptions.map((subscription, index) => normalize(subscription, index, {
    kind: 'wallet-coins', title: String(subscription.type || 'Wallet').replace(/_/g, ' '), isPositive: false, counterparty: 'Wallet & Coin ledger',
  }));
  const withdrawals = props.bankWithdrawals.map((withdrawal, index) => normalize(withdrawal, index, {
    kind: 'withdrawals', title: 'Bank withdrawal', isPositive: false, counterparty: withdrawal.bank_name || 'Bank', note: withdrawal.failure_reason || '',
  }));
  return [...wallet, ...requests, ...ledger, ...withdrawals].sort((first, second) => new Date(second.date || 0) - new Date(first.date || 0));
});

const filteredActivities = computed(() => allActivities.value.filter((item) => {
  const matchesTab = activeTab.value === 'all' || (activeTab.value === 'deposits' && item.kind === 'received') || item.kind === activeTab.value;
  const query = search.value.toLowerCase();
  const matchesSearch = !query || [item.title, item.counterparty, item.note, item.status, item.amount].join(' ').toLowerCase().includes(query);
  return matchesTab && matchesSearch;
}));

const statusClass = (status) => ({
  success: 'bg-emerald-100 text-emerald-700', complete: 'bg-emerald-100 text-emerald-700', completed: 'bg-emerald-100 text-emerald-700',
  pending: 'bg-amber-100 text-amber-700', processing: 'bg-blue-100 text-blue-700', failed: 'bg-rose-100 text-rose-700',
  rejected: 'bg-rose-100 text-rose-700', cancelled: 'bg-slate-100 text-slate-600', canceled: 'bg-slate-100 text-slate-600',
})[status] || 'bg-slate-100 text-slate-600';

const open = () => { isOpen.value = true; nextTick(() => window.lucide?.createIcons()); };
const close = () => { isOpen.value = false; };
defineExpose({ open, close });
</script>

<style scoped>
.tab-button { border-radius:9999px; color:#64748b; flex:none; font-size:.75rem; font-weight:700; padding:.5rem .75rem; white-space:nowrap; }
.tab-button:hover { background:#f1f5f9; color:#0f172a; }
.tab-button-active { background:#e0f2fe; color:#0369a1; }
</style>
