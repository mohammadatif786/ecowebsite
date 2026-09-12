<template>
  <Modal ref="modalRef" max-width="max-w-[1500px]">
    <div class="analytics-shell overflow-hidden rounded-2xl bg-[#081c2d] text-slate-100">
      <div class="flex items-center justify-between border-b border-white/10 bg-white/[0.03] px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-2xl border border-lime-300/25 bg-lime-300/10 text-lime-300">
            <BarChart3 class="h-5 w-5" />
          </div>
          <div>
            <h3 class="text-lg font-black text-white">Live Analytics</h3>
            <p class="text-xs font-bold text-slate-400">{{ subtitle }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button @click="fetchData" class="analytics-action">
            <RefreshCw class="h-4 w-4" />
            Refresh
          </button>
          <button @click="exportCSV" class="analytics-action">
            <Download class="h-4 w-4" />
            Export
          </button>
          <button @click="close" class="analytics-icon-btn">
            <X class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div class="relative max-h-[78vh] overflow-auto p-5">
        <div v-if="state.loading" class="absolute inset-0 z-30 grid place-items-center bg-[#081c2d]/70 backdrop-blur-sm">
          <div class="text-center">
            <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-lime-300/20 border-t-lime-300"></div>
            <p class="mt-3 text-sm font-black text-lime-300">Loading analytics...</p>
          </div>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="range in ranges"
              :key="range.value"
              @click="state.range = range.value"
              :class="['analytics-tab', state.range === range.value ? 'active' : '']"
            >
              {{ range.label }}
            </button>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row">
            <label class="analytics-select">
              <Tv2 class="h-4 w-4 text-slate-400" />
              <select v-model="state.sessionId">
                <option value="all">All sessions</option>
                <option v-for="session in analytics.sessions" :key="session.id" :value="String(session.id)">
                  {{ session.date }} - {{ session.title }}
                </option>
              </select>
            </label>
            <label class="analytics-select">
              <Globe2 class="h-4 w-4 text-slate-400" />
              <select v-model="state.country">
                <option value="all">All countries</option>
                <option v-for="country in analytics.countries" :key="country.code" :value="country.code">
                  {{ country.name }}
                </option>
              </select>
            </label>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <MetricCard title="Live Sessions" :value="summary.totalSessions" hint="sessions in range">
            <Clapperboard class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="Unique Viewers" :value="fmt(summary.uniqueViewers)" hint="database viewer records">
            <Users class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="PCU / ACU" :value="`${summary.pcu} / ${summary.acu.toFixed(1)}`" hint="peak / avg concurrent">
            <Activity class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="Watch Time" :value="`${summary.watchHours.toFixed(1)} hrs`" :hint="`avg ${summary.avgWatchPerViewer} sec / viewer`">
            <Clock4 class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="Engagement" :value="`${(summary.engagementRate * 100).toFixed(1)}%`" :hint="`${fmt(summary.chats)} chats - ${fmt(summary.reacts)} reacts`">
            <MessageSquare class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="Top Country" :value="summary.topCountry.code" :hint="`${summary.topCountry.name} - ${n(summary.topCountry.watchHours).toFixed(1)} hrs`">
            <Globe class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="Revenue" :value="money(summary.totalRevenue)" :hint="`${money(summary.totalCash)} gifts + ${money(summary.totalSubs)} subs`">
            <Coins class="h-4 w-4" />
          </MetricCard>
          <MetricCard title="Subscription Split" :value="`${money(summary.hostShare)} / ${money(summary.partnerShare)}`" hint="host / partner">
            <Split class="h-4 w-4" />
          </MetricCard>
        </div>

        <div v-if="state.error" class="mt-4 rounded-2xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm font-bold text-rose-100">
          {{ state.error }}
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-2">
          <section class="analytics-panel min-h-[330px]">
            <div class="analytics-panel-head">
              <div>
                <h4>Concurrency Over Time</h4>
                <p>Viewer activity by database bucket</p>
              </div>
            </div>
            <div class="h-[245px]">
              <Line :data="concurrencyChartData" :options="lineChartOptions" />
            </div>
          </section>

          <section class="analytics-panel min-h-[330px]">
            <div class="analytics-panel-head">
              <div>
                <h4>Revenue Sources</h4>
                <p>Gifts and private subscriptions</p>
              </div>
            </div>
            <div class="h-[245px]">
              <Doughnut :data="revenueMixChartData" :options="doughnutOptions" />
            </div>
          </section>

          <section class="analytics-panel">
            <div class="analytics-panel-head">
              <div>
                <h4>Most Popular Gifts Sent</h4>
                <p>Top gifts by count</p>
              </div>
              <span class="analytics-badge">Top: {{ topGift.gift }} ({{ topGift.count }})</span>
            </div>
            <div class="h-[190px]">
              <Bar :data="topGiftsChartData" :options="lineChartOptions" />
            </div>
            <DataTable :columns="['Gift', 'Count', 'Coins']" :empty="analytics.gifts.length === 0">
              <tr v-for="gift in analytics.gifts" :key="gift.gift" class="hover:bg-white/5">
                <td class="py-2 font-bold">{{ gift.gift }}</td>
                <td class="py-2 text-right">{{ fmt(gift.count) }}</td>
                <td class="py-2 text-right text-lime-300">{{ fmt(gift.coins) }}</td>
              </tr>
            </DataTable>
          </section>

          <section class="analytics-panel">
            <div class="analytics-panel-head">
              <div>
                <h4>Countries Watching Live</h4>
                <p>Viewer and watch-time distribution</p>
              </div>
            </div>
            <DataTable :columns="['Country', 'Watch (hrs)', 'Viewers', 'Share']" :empty="filteredCountries.length === 0">
              <tr v-for="country in filteredCountries" :key="country.code" class="hover:bg-white/5">
                <td class="py-2 font-bold">{{ country.name }}</td>
                <td class="py-2 text-right">{{ n(country.watchHours).toFixed(1) }}</td>
                <td class="py-2 text-right">{{ fmt(country.viewers) }}</td>
                <td class="py-2 text-right">{{ countryShare(country) }}%</td>
              </tr>
            </DataTable>
          </section>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
          <section class="analytics-panel xl:col-span-2">
            <div class="analytics-panel-head">
              <div>
                <h4>Transfer to Wallet</h4>
                <p>50/50 split, connected to wallet transfer records</p>
              </div>
              <button
                @click="transferNow"
                :disabled="isTransferring || moneySummary.eligible <= 0"
                class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-45"
              >
                {{ isTransferring ? 'Transferring...' : 'Transfer Now' }}
              </button>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
              <div class="analytics-mini">
                <p>Collected</p>
                <strong>{{ money(moneySummary.collected) }}</strong>
              </div>
              <div class="analytics-mini">
                <p>Eligible</p>
                <strong>{{ money(moneySummary.eligible) }}</strong>
              </div>
              <div class="analytics-mini">
                <p>Transferred</p>
                <strong class="text-emerald-300">{{ money(moneySummary.transferred) }}</strong>
              </div>
            </div>

            <DataTable class="mt-4" :columns="['Date', 'Type', 'Amount', 'Status']" :empty="analytics.transfers.length === 0">
              <tr v-for="transfer in analytics.transfers" :key="transfer.ref" class="hover:bg-white/5">
                <td class="py-2 text-slate-300">{{ transfer.date }}</td>
                <td class="py-2">
                  <span class="font-bold">{{ transfer.type }}</span>
                  <span class="block text-[10px] font-mono text-slate-500">{{ transfer.ref }}</span>
                </td>
                <td class="py-2 text-right text-emerald-300">{{ money(transfer.amount) }}</td>
                <td class="py-2 text-right">
                  <span class="rounded-full border border-emerald-400/25 bg-emerald-400/10 px-2 py-0.5 text-[10px] font-black text-emerald-300">
                    {{ transfer.status }}
                  </span>
                </td>
              </tr>
            </DataTable>
          </section>

          <section class="analytics-panel">
            <h4 class="text-sm font-black text-white">Private Subscription Split</h4>
            <p class="mt-1 text-xs font-bold text-slate-400">Subscription money split 50/50.</p>

            <div class="mt-4 space-y-3">
              <div class="analytics-mini">
                <p>Gross subscription revenue</p>
                <strong>{{ money(analytics.subscriptions.revenueGross) }}</strong>
                <span>{{ analytics.subscriptions.active }} active subs</span>
              </div>
              <div class="analytics-mini">
                <p>Host share</p>
                <strong class="text-emerald-300">{{ money(summary.hostShare) }}</strong>
              </div>
              <div class="analytics-mini">
                <p>Partner share</p>
                <strong class="text-sky-300">{{ money(summary.partnerShare) }}</strong>
              </div>
            </div>
          </section>
        </div>

        <section class="analytics-panel mt-5">
          <div class="analytics-panel-head">
            <div>
              <h4>Top Sessions</h4>
              <p>Ranked by latest stream sessions in the selected range</p>
            </div>
          </div>
          <DataTable :columns="['Date', 'Title', 'Category', 'PCU', 'Watch', 'Chats', 'Hearts', 'Coins', 'Cash', 'Subs']" :empty="filteredSessions.length === 0">
            <tr v-for="session in filteredSessions" :key="session.id" class="hover:bg-white/5">
              <td class="py-3 text-slate-300">{{ session.date }}</td>
              <td class="py-3 font-bold">{{ session.title }}</td>
              <td class="py-3 text-slate-400">{{ session.category }}</td>
              <td class="py-3 text-right">{{ fmt(session.pcu) }}</td>
              <td class="py-3 text-right">{{ n(session.watchHours).toFixed(1) }}</td>
              <td class="py-3 text-right">{{ fmt(session.chats) }}</td>
              <td class="py-3 text-right">{{ fmt(session.reacts) }}</td>
              <td class="py-3 text-right text-lime-300">{{ fmt(session.coins) }}</td>
              <td class="py-3 text-right text-emerald-300">{{ money(session.cash) }}</td>
              <td class="py-3 text-right text-sky-300">{{ money(session.subs) }}</td>
            </tr>
          </DataTable>
        </section>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, defineComponent, h, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
  Activity,
  BarChart3,
  Clapperboard,
  Clock4,
  Coins,
  Download,
  Globe,
  Globe2,
  MessageSquare,
  RefreshCw,
  Split,
  Tv2,
  Users,
  X,
} from 'lucide-vue-next';
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Tooltip,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import Modal from '../ui/Modal.vue';

ChartJS.register(ArcElement, BarElement, CategoryScale, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip);

const props = defineProps({
  initialEarnings: { type: Object, default: () => ({ collected: 0, transferred: 0 }) },
});

const MetricCard = defineComponent({
  props: {
    title: String,
    value: [String, Number],
    hint: String,
  },
  setup(cardProps, { slots }) {
    return () =>
      h('div', { class: 'analytics-kpi' }, [
        h('div', { class: 'flex items-center justify-between text-slate-400' }, [
          h('p', { class: 'text-[11px] font-black uppercase tracking-wide' }, cardProps.title),
          h('span', { class: 'opacity-80' }, slots.default?.()),
        ]),
        h('p', { class: 'mt-2 text-2xl font-black text-white' }, String(cardProps.value ?? 0)),
        h('p', { class: 'mt-1 text-xs font-bold text-slate-500' }, cardProps.hint || ''),
      ]);
  },
});

const DataTable = defineComponent({
  props: {
    columns: { type: Array, default: () => [] },
    empty: Boolean,
  },
  setup(tableProps, { slots, attrs }) {
    return () =>
      h('div', { class: ['overflow-x-auto', attrs.class] }, [
        h('table', { class: 'w-full min-w-[720px] text-sm' }, [
          h(
            'thead',
            { class: 'border-b border-white/10 text-xs uppercase text-slate-400' },
            h(
              'tr',
              tableProps.columns.map((column, index) =>
                h('th', { class: ['py-2', index === 0 ? 'text-left' : 'text-right'] }, column),
              ),
            ),
          ),
          h('tbody', { class: 'divide-y divide-white/5' }, tableProps.empty ? [h('tr', [h('td', { class: 'py-8 text-center text-slate-500', colspan: tableProps.columns.length }, 'No analytics data yet')])] : slots.default?.()),
        ]),
      ]);
  },
});

const ranges = [
  { value: 'today', label: 'Today' },
  { value: '7d', label: '7D' },
  { value: '30d', label: '30D' },
  { value: '90d', label: '90D' },
];

const modalRef = ref(null);
const isTransferring = ref(false);
const payoutAttemptKey = ref(null);

const state = reactive({
  range: '7d',
  sessionId: 'all',
  country: 'all',
  loading: false,
  error: '',
});

const defaultAnalytics = () => ({
  settings: {
    coinToUsd: 0.01,
    platformFeeRate: 0.5,
    subscriptionSplitHost: 0.5,
    subscriptionSplitPartner: 0.5,
  },
  sessions: [],
  buckets: [],
  gifts: [],
  subscriptions: { active: 0, revenueGross: 0 },
  countries: [],
  transfers: [],
  earnings: { ...props.initialEarnings },
});

const analytics = reactive(defaultAnalytics());

const n = (value) => {
  const parsed = Number(value || 0);
  return Number.isFinite(parsed) ? parsed : 0;
};

const fmt = (value) => n(value).toLocaleString();
const money = (value) => '$' + n(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const subtitle = computed(() => {
  const current = ranges.find((range) => range.value === state.range)?.label || '7D';
  return `${current} - ${state.sessionId === 'all' ? 'All sessions' : 'Selected session'}`;
});

const filteredSessions = computed(() => {
  const sessions = state.sessionId === 'all' ? analytics.sessions : analytics.sessions.filter((session) => String(session.id) === String(state.sessionId));
  return [...sessions];
});

const filteredCountries = computed(() => {
  const countries = state.country === 'all' ? analytics.countries : analytics.countries.filter((country) => country.code === state.country);
  return [...countries].sort((a, b) => n(b.watchHours) - n(a.watchHours));
});

const summary = computed(() => {
  const sessions = filteredSessions.value;
  const totalSessions = sessions.length;
  const watchHours = sessions.reduce((total, session) => total + n(session.watchHours), 0);
  const uniqueViewers = sessions.reduce((total, session) => total + n(session.uniqueViewers), 0);
  const pcu = sessions.reduce((peak, session) => Math.max(peak, n(session.pcu)), 0);
  const chats = sessions.reduce((total, session) => total + n(session.chats), 0);
  const reacts = sessions.reduce((total, session) => total + n(session.reacts), 0);
  const totalCoins = sessions.reduce((total, session) => total + n(session.coins), 0);
  const totalCash = sessions.reduce((total, session) => total + n(session.cash), 0);
  const totalSubs = sessions.reduce((total, session) => total + n(session.subs), 0);
  const acu = totalSessions > 0 ? uniqueViewers / totalSessions : 0;
  const avgWatchPerViewer = uniqueViewers > 0 ? Math.round((watchHours * 3600) / uniqueViewers) : 0;
  const engagementRate = uniqueViewers > 0 ? (chats + reacts + n(totalCoins / 100)) / uniqueViewers : 0;
  const topCountry = analytics.countries.length
    ? [...analytics.countries].sort((a, b) => n(b.watchHours) - n(a.watchHours))[0]
    : { code: 'N/A', name: 'N/A', watchHours: 0 };
  const hostShare = n(analytics.subscriptions.revenueGross) * n(analytics.settings.subscriptionSplitHost || 0.5);
  const partnerShare = n(analytics.subscriptions.revenueGross) * n(analytics.settings.subscriptionSplitPartner || 0.5);

  return {
    totalSessions,
    watchHours,
    uniqueViewers,
    pcu,
    acu,
    chats,
    reacts,
    avgWatchPerViewer,
    engagementRate,
    topCountry,
    totalCoins,
    totalCash,
    totalSubs,
    totalRevenue: totalCash + totalSubs,
    hostShare,
    partnerShare,
  };
});

const moneySummary = computed(() => {
  const collected = n(analytics.earnings?.collected);
  const hostShare = collected * 0.5;
  const transferred = n(analytics.earnings?.transferred);
  return {
    collected,
    eligible: Math.max(0, hostShare - transferred),
    transferred,
  };
});

const topGift = computed(() => (analytics.gifts.length ? [...analytics.gifts].sort((a, b) => n(b.count) - n(a.count))[0] : { gift: 'None', count: 0 }));
const totalCountryWatchHours = computed(() => analytics.countries.reduce((total, country) => total + n(country.watchHours), 0));
const countryShare = (country) => (totalCountryWatchHours.value > 0 ? Math.round((n(country.watchHours) / totalCountryWatchHours.value) * 100) : 0);

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false }, ticks: { color: 'rgba(148,163,184,.65)', maxRotation: 0, autoSkip: true } },
    y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.12)' }, ticks: { color: 'rgba(148,163,184,.65)' } },
  },
};

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%',
  plugins: {
    legend: { position: 'bottom', labels: { color: '#e2e8f0', usePointStyle: true, padding: 18 } },
  },
};

const concurrencyChartData = computed(() => ({
  labels: analytics.buckets.map((bucket) => bucket.t),
  datasets: [
    {
      label: 'Concurrent viewers',
      data: analytics.buckets.map((bucket) => n(bucket.concurrent)),
      borderColor: '#dfff00',
      backgroundColor: 'rgba(223,255,0,.12)',
      fill: true,
      tension: 0.35,
      pointRadius: 0,
      borderWidth: 2,
    },
  ],
}));

const revenueMixChartData = computed(() => ({
  labels: ['Gifts', 'Private Subs'],
  datasets: [
    {
      data: [summary.value.totalCash, summary.value.totalSubs],
      backgroundColor: ['#dfff00', '#38bdf8'],
      borderWidth: 0,
    },
  ],
}));

const topGiftsChartData = computed(() => {
  const gifts = [...analytics.gifts].sort((a, b) => n(b.count) - n(a.count)).slice(0, 5);
  return {
    labels: gifts.map((gift) => gift.gift),
    datasets: [{ label: 'Count', data: gifts.map((gift) => n(gift.count)), backgroundColor: 'rgba(223,255,0,.85)', borderRadius: 8 }],
  };
});

const fetchData = async () => {
  state.loading = true;
  state.error = '';
  try {
    const response = await axios.get(route('new_frontend.live.analytics_data'), { params: { range: state.range } });
    if (response.data?.ok || response.data?.success) {
      const fresh = { ...defaultAnalytics(), ...response.data };
      Object.assign(analytics, fresh);
      return;
    }

    state.error = response.data?.error || 'Analytics data could not be loaded.';
  } catch (error) {
    console.error('Failed to fetch live analytics:', error);
    state.error = error.response?.data?.message || error.response?.data?.error || 'Failed to fetch live analytics.';
    if (window.toast) window.toast(error.response?.data?.message || 'Failed to fetch live analytics', 'error');
  } finally {
    state.loading = false;
  }
};

const open = () => {
  modalRef.value?.open();
  fetchData();
};

const close = () => {
  modalRef.value?.close();
};

const transferNow = async () => {
  if (moneySummary.value.eligible <= 0 || isTransferring.value) return;

  isTransferring.value = true;
  try {
    payoutAttemptKey.value ||= crypto.randomUUID();
    const response = await axios.post(route('new_frontend.live.transfer_earnings'), { idempotency_key: payoutAttemptKey.value });
    if (response.data?.success) {
      payoutAttemptKey.value = null;
      if (window.toast) window.toast('Transfer successful');
      await fetchData();
      router.reload({ only: ['balance', 'serverEarnings'] });
    }
  } catch (error) {
    console.error('Transfer failed:', error);
    if (window.toast) window.toast(error.response?.data?.message || 'Transfer failed', 'error');
  } finally {
    isTransferring.value = false;
  }
};

const exportCSV = () => {
  const rows = [
    ['Date', 'Title', 'Category', 'Status', 'PCU', 'Watch Hours', 'Unique Viewers', 'Chats', 'Hearts', 'Coins', 'Cash', 'Subs'],
    ...filteredSessions.value.map((session) => [
      session.date,
      session.title,
      session.category,
      session.status,
      session.pcu,
      session.watchHours,
      session.uniqueViewers,
      session.chats,
      session.reacts,
      session.coins,
      session.cash,
      session.subs,
    ]),
  ];
  const csv = rows.map((row) => row.map((cell) => `"${String(cell ?? '').replaceAll('"', '""')}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'linkup_live_analytics.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
};

watch(
  () => state.range,
  () => fetchData(),
);

watch(
  () => props.initialEarnings,
  (value) => {
    analytics.earnings = { ...analytics.earnings, ...value };
  },
  { deep: true },
);

defineExpose({ open, close });
</script>

<style scoped>
.analytics-shell {
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
}
.analytics-action,
.analytics-icon-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  transition: 0.18s ease;
}
.analytics-action {
  border-radius: 1rem;
  padding: 0.55rem 0.8rem;
  font-size: 0.8rem;
  font-weight: 800;
}
.analytics-icon-btn {
  border-radius: 999px;
  padding: 0.55rem;
}
.analytics-action:hover,
.analytics-icon-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}
.analytics-tab {
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  padding: 0.6rem 0.9rem;
  font-size: 0.8rem;
  font-weight: 900;
  color: #cbd5e1;
}
.analytics-tab.active {
  border-color: rgba(223, 255, 0, 0.35);
  background: rgba(223, 255, 0, 0.12);
  color: #dfff00;
}
.analytics-select {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  padding: 0.55rem 0.8rem;
}
.analytics-select select {
  min-width: 150px;
  background: transparent;
  color: #e2e8f0;
  font-size: 0.82rem;
  font-weight: 800;
  outline: none;
}
.analytics-select option {
  color: #0f172a;
}
.analytics-kpi,
.analytics-panel,
.analytics-mini {
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.045);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}
.analytics-kpi,
.analytics-panel {
  border-radius: 1rem;
  padding: 1rem;
}
.analytics-panel-head {
  margin-bottom: 1rem;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}
.analytics-panel h4,
.analytics-panel-head h4 {
  font-weight: 900;
  color: #fff;
}
.analytics-panel p,
.analytics-panel-head p {
  margin-top: 0.2rem;
  font-size: 0.78rem;
  font-weight: 700;
  color: #94a3b8;
}
.analytics-badge {
  white-space: nowrap;
  border-radius: 999px;
  border: 1px solid rgba(223, 255, 0, 0.2);
  background: rgba(223, 255, 0, 0.1);
  padding: 0.35rem 0.6rem;
  font-size: 0.72rem;
  font-weight: 900;
  color: #dfff00;
}
.analytics-mini {
  border-radius: 1rem;
  padding: 0.9rem;
}
.analytics-mini p {
  font-size: 0.68rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  color: #94a3b8;
  text-transform: uppercase;
}
.analytics-mini strong {
  margin-top: 0.25rem;
  display: block;
  font-size: 1.35rem;
  font-weight: 950;
  color: #fff;
}
.analytics-mini span {
  margin-top: 0.15rem;
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  color: #64748b;
}
</style>
