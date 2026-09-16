<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <!-- Gradient Header -->
    <div class="brandgrad text-white p-4 -m-1 rounded-t-[20px]">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="h-9 w-9 rounded-xl grid place-items-center bg-white/20">
            <i data-lucide="handshake" class="w-5 h-5"></i>
          </span>
          <div>
            <h3 class="text-lg font-black">Promote &amp; Earn</h3>
            <p class="text-white/85 text-[11px]">Sell for any store — earn on every sale you drive</p>
          </div>
        </div>
        <button @click="close" class="bg-white/20 rounded-full px-3 py-1.5 text-xs font-black hover:bg-white/30 transition">✕</button>
      </div>
      <!-- Tabs -->
      <div class="flex gap-2 mt-3">
        <button v-for="t in tabs" :key="t[0]" @click="activeTab = t[0]" :class="['shrink-0 rounded-full px-4 py-1.5 text-sm font-black transition', activeTab === t[0] ? 'bg-white text-[#2196F3] shadow-md' : 'bg-white/20 text-white']">
          {{ t[1] }}
        </button>
      </div>
    </div>

    <div class="p-4">
      <!-- BROWSE TAB -->
      <template v-if="activeTab === 'browse'">
        <p class="text-[12px] text-slate-500 mb-3">Tag any of these on your Live or Vibes — you earn what the seller/organizer is offering, on every sale you drive. No inventory, no risk.</p>
        <div class="space-y-2 max-h-[55vh] overflow-y-auto">
          <div v-for="item in browseList" :key="item.id + item.kind" class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2">
            <div class="relative shrink-0">
              <img :src="item.image" class="h-14 w-14 rounded-xl object-cover" />
              <span class="absolute -top-1 -left-1 text-white text-[9px] font-black px-1 py-0.5 rounded-full" :style="{ background: item.kind === 'event' ? '#2563eb' : '#8b5cf6' }">
                {{ item.kind === 'event' ? '🎟️' : '🛍️' }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-black text-sm truncate">{{ item.title }}</p>
              <p class="text-[11px] text-slate-500 truncate">{{ item.kind === 'event' ? (fmtDate(item.date) + ' · ' + item.location) : item.seller }} · {{ item.price ? money(item.price) : 'Free' }}</p>
              <p class="text-[11px] font-black text-emerald-600 mt-0.5">{{ commLabel(item) }}</p>
            </div>
            <button @click="togglePromote(item)" :class="['btn px-3 py-2 text-xs shrink-0', isPromoting(item) ? 'btn-ghost text-emerald-600' : 'btn-primary']">
              {{ isPromoting(item) ? 'Promoting ✓' : 'Promote' }}
            </button>
          </div>
          <p v-if="!browseList.length" class="text-center text-slate-400 py-6 font-bold">No commissionable products found.</p>
        </div>
      </template>

      <!-- MY PROMOTIONS TAB -->
      <template v-else-if="activeTab === 'promos'">
        <div v-if="promos.length" class="space-y-2 max-h-[55vh] overflow-y-auto">
          <div v-for="p in promos" :key="p.id" class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2">
            <img :src="p.image" class="h-14 w-14 rounded-xl object-cover shrink-0" />
            <div class="flex-1 min-w-0">
              <p class="font-black text-sm truncate">{{ p.kind === 'event' ? '🎟️ ' : '' }}{{ p.title }}</p>
              <p class="text-[11px] text-emerald-600 font-black">{{ commLabel(p) }}</p>
            </div>
            <div class="flex flex-col gap-1.5 shrink-0">
              <button @click="removePromo(p)" class="btn btn-ghost px-3 py-2 text-xs text-rose-600">Remove</button>
            </div>
          </div>
        </div>
        <div v-else class="card p-8 text-center text-slate-400 font-bold">
          No promotions yet — go to Promote and pick products to earn on.
        </div>
      </template>

      <!-- EARNINGS TAB -->
      <template v-else>
        <div class="space-y-3">
          <!-- Stats -->
          <div class="grid grid-cols-3 gap-2">
            <div class="card p-3 text-center">
              <p class="text-xl font-black">{{ stats.sales_driven || 0 }}</p>
              <p class="text-[11px] text-slate-400">Sales driven</p>
            </div>
            <div class="card p-3 text-center">
              <p class="text-xl font-black text-emerald-600">{{ money(stats.total_commission) }}</p>
              <p class="text-[11px] text-slate-400">Commission</p>
            </div>
            <div class="card p-3 text-center">
              <p class="text-xl font-black">0</p>
              <p class="text-[11px] text-slate-400">Taps</p>
            </div>
          </div>

          <!-- Pending / Available -->
          <div class="grid grid-cols-2 gap-2">
            <div class="rounded-2xl bg-[#FFF8E1] border border-[#FFECB3] p-3">
              <p class="text-[11px] font-black text-[#A67C00]">Pending (escrow)</p>
              <p class="text-2xl font-black text-[#A67C00]">{{ money(stats.pending) }}</p>
              <p class="text-[10px] text-[#A67C00]/80">Released when buyers receive orders</p>
            </div>
            <div class="rounded-2xl bg-[#E8F5E9] border border-[#C8E6C9] p-3">
              <p class="text-[11px] font-black text-[#2E7D32]">Available</p>
              <p class="text-2xl font-black text-[#2E7D32]">{{ money(stats.available) }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button v-if="stats.pending > 0" @click="releaseEarnings" class="btn btn-ghost flex-1 py-3">Simulate deliveries → release</button>
            <button @click="transferEarnings" class="btn btn-primary flex-1 py-3">Transfer {{ money(stats.available) }} to Wallet</button>
          </div>
          <p class="text-[11px] text-slate-400 text-center">LinkUp keeps 5% · already paid out {{ money(stats.paid) }}</p>

          <!-- Recent commissions -->
          <div class="flex items-center justify-between mt-4 mb-2">
            <p class="font-black text-sm">Recent commissions</p>
            <div class="flex gap-1">
              <button
                v-for="f in ['All', 'Vibes', 'U Vibe', 'Live']"
                :key="f"
                @click="commissionFilter = f"
                :class="['px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition-all', commissionFilter === f ? 'bg-lkblue text-white shadow-sm' : 'bg-slate-100 text-slate-400 hover:bg-slate-200']"
              >
                {{ f }}
              </button>
            </div>
          </div>

          <div class="space-y-1.5 max-h-40 overflow-y-auto custom-scrollbar">
            <div v-if="filteredRecent.length" v-for="s in filteredRecent" :key="s.title + s.amount" class="flex items-center justify-between border border-slate-100 rounded-xl p-2 text-sm hover:bg-slate-50 transition">
              <div class="min-w-0">
                <p class="font-bold truncate text-slate-800">{{ s.title }}</p>
                <p class="text-[11px] text-slate-400 font-medium">
                  {{ money(s.amount) }} · {{ s.rate }}% ·
                  <span :class="['font-bold', getSourceColor(s.source)]">
                    {{ getSourceLabel(s.source) }}
                  </span>
                </p>
              </div>
              <span class="font-black text-emerald-600 shrink-0">
                +{{ money(s.commission) }} <span :class="['text-[10px] uppercase', s.status === 'paid' ? 'text-emerald-500' : 'text-amber-500']">{{ s.status }}</span>
              </span>
            </div>
            <div v-else class="text-center py-8">
              <div class="text-slate-300 mb-1"><i data-lucide="info" class="w-8 h-8 mx-auto opacity-20"></i></div>
              <p class="text-slate-400 text-[13px] font-bold">No {{ commissionFilter === 'All' ? '' : commissionFilter }} commissions yet</p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';
import { DB, getProducts, getEvents, SEED } from '../MockDataStore';

const props = defineProps({
  items: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) }
});

const modalRef = ref(null);
const activeTab = ref('browse');
const tabs = [['browse', 'Promote'], ['promos', 'My Promotions'], ['earnings', 'Earnings']];

const defaultEarnings = { pending: 0, available: 0, paid: 0, clicks: 0, sales: [] };
const earnings = ref({ ...defaultEarnings });
const promos = ref([]);
const commissionFilter = ref('All');

const filteredRecent = computed(() => {
  const recent = props.stats?.recent || [];
  if (commissionFilter.value === 'All') return recent;

  const mapping = {
    'Vibes': 'vibe',
    'U Vibe': 'uvibe',
    'Live': 'live'
  };

  const targetSource = mapping[commissionFilter.value];
  return recent.filter(s => s.source === targetSource);
});

const getSourceLabel = (source) => {
  const labels = { vibe: '✨ from Vibes', uvibe: '🎓 from U Vibe', live: '📡 from Live', marketplace: '🛍️ from Marketplace' };
  return labels[source] || 'from LinkUp';
};

const getSourceColor = (source) => {
  const colors = { vibe: 'text-purple-500', uvibe: 'text-indigo-500', live: 'text-rose-500', marketplace: 'text-emerald-500' };
  return colors[source] || 'text-slate-500';
};

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const PLATFORM_FEE = 5;

const fmtDate = (d) => {
  if (!d) return '';
  const MONTHS = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
  const dt = new Date(d);
  return MONTHS[dt.getMonth()] + ' ' + dt.getDate();
};

const commMode = (t) => { if (!t) return 'none'; if (t.commMode) return t.commMode; return (t.commission > 0) ? 'pct' : 'none'; };
const tagCommAmt = (t) => { const m = commMode(t); if (m === 'none') return 0; if (m === 'flat') return +(t.commFlat || 0); return +(((t.price || 0) * (t.commission || 0)) / 100).toFixed(2); };
const commLabel = (t) => { const m = commMode(t); if (m === 'none') return 'no commission'; if (m === 'flat') return money(t.commFlat || 0) + ' per sale'; return (t.commission || 0) + '% · ' + money(tagCommAmt(t)) + ' per sale'; };

const totalCommission = computed(() => +(earnings.value.pending + earnings.value.available + earnings.value.paid).toFixed(2));

const browseList = computed(() => {
  return props.items.filter(item => tagCommAmt(item) > 0);
});

const isPromoting = (item) => promos.value.some(p => String(p.id) === String(item.id) && p.kind === item.kind);

const togglePromote = (item) => {
  if (isPromoting(item)) {
    promos.value = promos.value.filter(p => !(String(p.id) === String(item.id) && p.kind === item.kind));
  } else {
    promos.value.unshift({ ...item });
    if (window.toast) window.toast('🤝 Promoting ' + item.title + ' — earn ' + commLabel(item));
  }
  DB.set('lk_affiliate_promos', promos.value);
};

const removePromo = (p) => {
  promos.value = promos.value.filter(x => !(String(x.id) === String(p.id) && x.kind === p.kind));
  DB.set('lk_affiliate_promos', promos.value);
};

const releaseEarnings = () => {
  earnings.value.available = +(earnings.value.available + earnings.value.pending).toFixed(2);
  earnings.value.sales.forEach(s => { if (s.status === 'pending') s.status = 'released'; });
  earnings.value.pending = 0;
  DB.set('lk_affiliate_earnings', earnings.value);
  if (window.toast) window.toast('✅ Deliveries confirmed — commission released');
};

const transferEarnings = () => {
  const available = (props.stats?.available || 0);
  if (available <= 0) {
    if (window.toast) window.toast('Nothing available yet — release delivered orders first');
    return;
  }

  if (window.toast) window.toast(`💸 ${money(available)} commission transfer requested!`);
};

const open = (tab) => {
  activeTab.value = tab || 'browse';
  earnings.value = DB.get('lk_affiliate_earnings', defaultEarnings);
  promos.value = DB.get('lk_affiliate_promos', []);
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) setTimeout(() => window.lucide.createIcons(), 50);
  }
};

const close = () => { if (modalRef.value) modalRef.value.close(); };

defineExpose({ open, close });
</script>

