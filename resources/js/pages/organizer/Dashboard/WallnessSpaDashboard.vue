<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import SampleDataBanner from '@/components/organizer/SampleDataBanner.vue';

const props = defineProps({
  spaData: { type: Object, required: true },
});

const selectedPeriod = ref(props.spaData.currentPeriod || 'hourly');
const selectedSpa = ref(props.spaData.currentSpa || 'all');

const kpis = computed(() => props.spaData.kpis || {});
const chartsData = computed(
  () =>
    props.spaData.charts ?? {
      timeline: { labels: [], grossByLabel: [], counts: {} },
      serviceMix: { bookingsByCategory: {} },
      revenueByCategory: { revenueByCategory: {} },
      revenueByCategoryOverTime: { labels: [], series: {} },
      bookingsTimeline: { labels: [], completed: [], canceled: [], noshow: [] },
      retention: { labels: [], returning: [], new: [] },
      topServicesByRevenue: { items: [] },
      quickStats: {
        addonRevenue: 0,
        mobileRevenue: 0,
        noShowCount: 0,
        avgRating: 0,
        addonsSold: 0,
        mobileBookings: 0,
        avgTicket: 0,
        repeatRate: 0,
        coordinator: '—',
        nextAvail: 0,
        nextEta: '—',
      },
      meta: { activeIndex: 0, prevIndex: 0 },
    },
);
const labels = computed(() => chartsData.value?.timeline?.labels || []);

const selectedPeriodLabel = computed(() => {
  if (selectedPeriod.value === 'daily') return 'Daily';
  if (selectedPeriod.value === 'weekly') return 'Weekly';
  return 'Hourly';
});

const serviceRevenue = computed(() => {
  const sr = chartsData.value?.revenueByCategory?.revenueByCategory || {};
  return {
    Massage: sr['Massage Services'] || 0,
    Nails: sr['Nail Services'] || 0,
    Mobile: sr['Mobile Services'] || 0,
  };
});

const topContributorLabel = computed(() => {
  const sr = chartsData.value?.revenueByCategory?.revenueByCategory || {};
  const top = chartsData.value?.revenueByCategory?.topContributorLabel;
  if (!top || sr[top] == null) return '—';
  return `${top} • ${money(sr[top])}`;
});

const insightLabel = computed(() => {
  const grossBy = chartsData.value?.timeline?.grossByLabel || [];
  const activeIndex = chartsData.value?.meta?.activeIndex ?? Math.max(0, grossBy.length - 1);
  const prevIndex = chartsData.value?.meta?.prevIndex ?? Math.max(0, grossBy.length - 2);
  return (Number(grossBy[activeIndex] || 0) > Number(grossBy[prevIndex] || 0)) ? 'Insight: Demand rising' : 'Insight: Demand stabilizing';
});

const money = (n) => {
  const x = Math.round((Number(n) || 0) * 100) / 100;
  return x.toLocaleString(undefined, { style: 'currency', currency: 'USD' });
};

const setPeriod = (p) => {
  selectedPeriod.value = p;
  updateFilters();
};

const updateFilters = () => {
  router.visit(window.location.pathname, {
    data: {
      period: selectedPeriod.value,
      spa_id: selectedSpa.value,
    },
    preserveScroll: true,
    preserveState: true,
    only: ['spaData'],
  });
};

const exportCsv = () => {
  const d = props.spaData;
  const q = chartsData.value?.quickStats ?? {};
  const rev = chartsData.value?.revenueByCategory?.revenueByCategory ?? {};
  const mix = chartsData.value?.serviceMix?.bookingsByCategory ?? {};
  const rows = [
    ['Period', selectedPeriod.value],
    ['Location', selectedSpa.value],
    [],
    ['Metric', 'Value'],
    ['Total Revenue', kpis.value.gross || 0],
    ['Total Massages', kpis.value.massages || 0],
    ['Total Add-Ons', kpis.value.addonsCount || 0],
    ['Checked-In', kpis.value.checkins || 0],
    ['Add-On Revenue', q.addonRevenue || 0],
    ['Mobile Service Revenue', q.mobileRevenue || 0],
    ['No-shows / Cancellations', q.noShowCount || 0],
    ['Average Rating', q.avgRating || 0],
  ];

  rows.push([]);
  rows.push(['Service Categories', 'Bookings']);
  Object.entries(mix || {}).forEach(([k, v]) => rows.push([k, v]));

  rows.push([]);
  rows.push(['Revenue by Category', 'Amount']);
  Object.entries(rev || {}).forEach(([k, v]) => rows.push([k, v]));

  const csv = rows
    .map((r) => r.map((v) => String(v).replaceAll('"', '""')).map((v) => `"${v}"`).join(','))
    .join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `wellness_spa_dashboard_${selectedPeriod.value}_${selectedSpa.value}.csv`;
  a.click();
  URL.revokeObjectURL(url);
};

const sparkGrossRef = ref(null);
const sparkMassagesRef = ref(null);
const sparkAddonsRef = ref(null);
const sparkCheckinsRef = ref(null);
const serviceMixRef = ref(null);
const serviceTypeRevenueRef = ref(null);
const revStackedRef = ref(null);
const payoutWaterfallRef = ref(null);
const bookingTimelineRef = ref(null);
const retentionRef = ref(null);
const topServicesRef = ref(null);

let charts = {};

const destroyCharts = () => {
  Object.values(charts).forEach((ch) => {
    try {
      ch.destroy();
    } catch {
    }
  });
  charts = {};
};

const buildGradient = (ctx, rgb, alphaTop = 0.32, alphaBot = 0.02) => {
  const g = ctx.createLinearGradient(0, 0, 0, 260);
  g.addColorStop(0, `rgba(${rgb},${alphaTop})`);
  g.addColorStop(1, `rgba(${rgb},${alphaBot})`);
  return g;
};

const renderCharts = () => {
  destroyCharts();

  const d = props.spaData;
  const tl = chartsData.value?.timeline ?? { labels: [], grossByLabel: [], counts: {} };
  const L = (tl.labels ?? []).length || 0;
  if (L <= 0) return;

  const grossByLabel = tl.grossByLabel || [];

  const sparkOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } },
    scales: { x: { display: false }, y: { display: false } },
    elements: { point: { radius: 0 }, line: { tension: 0.35, borderWidth: 2 } },
  };

  const spark = (el, data, border, fill) => {
    if (!el?.value) return null;
    return new Chart(el.value.getContext('2d'), {
      type: 'line',
      data: { labels: labels.value, datasets: [{ data, borderColor: border, backgroundColor: fill, fill: true }] },
      options: sparkOpts,
    });
  };

  charts.sparkGross = spark(sparkGrossRef, grossByLabel, 'rgba(20,184,166,1)', 'rgba(20,184,166,.14)');
  charts.sparkMassages = spark(sparkMassagesRef, (tl.counts?.massage || []), 'rgba(14,165,233,1)', 'rgba(14,165,233,.14)');
  charts.sparkAddons = spark(sparkAddonsRef, (tl.counts?.addons || []), 'rgba(167,139,250,1)', 'rgba(167,139,250,.14)');
  charts.sparkCheckins = spark(sparkCheckinsRef, chartsData.value?.bookingsTimeline?.completed || [], 'rgba(251,113,133,1)', 'rgba(251,113,133,.14)');

  if (serviceMixRef.value) {
    const mix = chartsData.value?.serviceMix?.bookingsByCategory || {};
    charts.serviceMix = new Chart(serviceMixRef.value.getContext('2d'), {
      type: 'doughnut',
      data: { labels: Object.keys(mix), datasets: [{ data: Object.values(mix), borderWidth: 0 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } } },
        cutout: '62%',
      },
    });
  }

  if (serviceTypeRevenueRef.value) {
    const str = chartsData.value?.revenueByCategory?.revenueByCategory || {};
    charts.serviceTypeRevenue = new Chart(serviceTypeRevenueRef.value.getContext('2d'), {
      type: 'bar',
      data: { labels: Object.keys(str), datasets: [{ label: 'Revenue', data: Object.values(str), borderRadius: 12 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => ` ${money(ctx.raw)}` } } },
        scales: {
          x: { grid: { color: 'rgba(148,163,184,.18)' } },
          y: { grid: { display: false } },
        },
      },
    });
  }

  if (revStackedRef.value) {
    const rctx = revStackedRef.value.getContext('2d');
    const overTime = chartsData.value?.revenueByCategoryOverTime ?? { labels: [], series: {} };
    const s = overTime.series ?? {};
    charts.revStacked = new Chart(rctx, {
      type: 'line',
      data: {
        labels: tl.labels ?? overTime.labels,
        datasets: [
          { label: 'Massage Services', data: s.massage || [], borderColor: 'rgba(20,184,166,1)', backgroundColor: buildGradient(rctx, '20,184,166'), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'Nail Services', data: s.nails || [], borderColor: 'rgba(14,165,233,1)', backgroundColor: buildGradient(rctx, '14,165,233', 0.24), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'Facial Services', data: s.facials || [], borderColor: 'rgba(251,113,133,1)', backgroundColor: buildGradient(rctx, '251,113,133', 0.22), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'Wellness Treatments', data: s.wellness || [], borderColor: 'rgba(245,158,11,1)', backgroundColor: buildGradient(rctx, '245,158,11', 0.18), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'Service Add-Ons', data: s.addons || [], borderColor: 'rgba(167,139,250,1)', backgroundColor: buildGradient(rctx, '167,139,250', 0.18), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'Mobile Services', data: s.mobile || [], borderColor: 'rgba(99,102,241,1)', backgroundColor: buildGradient(rctx, '99,102,241', 0.18), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } }, tooltip: { mode: 'index', intersect: false } },
        scales: {
          x: { grid: { color: 'rgba(148,163,184,.18)' } },
          y: { grid: { color: 'rgba(148,163,184,.18)' } },
        },
      },
    });
  }

  if (bookingTimelineRef.value) {
    const bt = chartsData.value?.bookingsTimeline ?? { labels: [], completed: [], canceled: [], noshow: [] };
    charts.bookingTimeline = new Chart(bookingTimelineRef.value.getContext('2d'), {
      type: 'line',
      data: {
        labels: tl.labels,
        datasets: [
          { label: 'Completed', data: bt.completed || [], borderColor: 'rgba(20,184,166,1)', backgroundColor: 'rgba(20,184,166,.10)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'Canceled', data: bt.canceled || [], borderColor: 'rgba(251,113,133,1)', backgroundColor: 'rgba(251,113,133,.10)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'No-show', data: bt.noshow || [], borderColor: 'rgba(245,158,11,1)', backgroundColor: 'rgba(245,158,11,.10)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } } },
        scales: {
          x: { grid: { color: 'rgba(148,163,184,.18)' } },
          y: { grid: { color: 'rgba(148,163,184,.18)' }, ticks: { precision: 0 } },
        },
      },
    });
  }

  if (retentionRef.value) {
    const rctx = retentionRef.value.getContext('2d');
    const rt = chartsData.value?.retention ?? { labels: [], returning: [], new: [] };
    charts.retention = new Chart(rctx, {
      type: 'line',
      data: {
        labels: tl.labels,
        datasets: [
          { label: 'Returning', data: rt.returning || [], borderColor: 'rgba(14,165,233,1)', backgroundColor: buildGradient(rctx, '14,165,233', 0.22, 0.02), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
          { label: 'New', data: rt.new || [], borderColor: 'rgba(167,139,250,1)', backgroundColor: buildGradient(rctx, '167,139,250', 0.18, 0.02), fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } } },
        scales: {
          x: { grid: { color: 'rgba(148,163,184,.18)' } },
          y: { grid: { color: 'rgba(148,163,184,.18)' }, ticks: { precision: 0 } },
        },
      },
    });
  }

  if (topServicesRef.value) {
    const items = chartsData.value?.topServicesByRevenue?.items ?? [];
    charts.topServices = new Chart(topServicesRef.value.getContext('2d'), {
      type: 'bar',
      data: { labels: items.map((x) => x.name), datasets: [{ label: 'Revenue', data: items.map((x) => x.val), borderRadius: 12 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => ` ${money(ctx.raw)}` } } },
        scales: {
          x: { grid: { color: 'rgba(148,163,184,.18)' } },
          y: { grid: { display: false } },
        },
      },
    });
  }
};

watch(
  () => props.spaData,
  () => {
    selectedPeriod.value = props.spaData.currentPeriod || selectedPeriod.value;
    selectedSpa.value = props.spaData.currentSpa || selectedSpa.value;
    renderCharts();
  },
  { deep: true },
);

onMounted(() => {
  renderCharts();
});

onBeforeUnmount(() => {
  destroyCharts();
});
</script>

<template>
  <AppLayout>
    <!-- Inject filters into the layout header -->
    <template #header-actions>
      <select v-model="selectedSpa" @change="updateFilters" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-700 outline-none bg-white/90 shadow-sm">
        <option value="all">All Wellness Events</option>
        <option v-for="ev in spaData.events" :key="ev.id" :value="ev.id">{{ ev.title }}</option>
      </select>

      <div class="rounded-xl bg-white/15 p-1 flex gap-1">
        <button
          v-for="p in ['hourly', 'daily', 'weekly']"
          :key="p"
          @click="setPeriod(p)"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-black transition',
            selectedPeriod === p ? 'bg-white text-emerald-600' : 'text-white hover:bg-white/20'
          ]"
        >
          {{ p.charAt(0).toUpperCase() + p.slice(1) }}
        </button>
      </div>

      <button class="rounded-xl bg-white px-4 py-2 text-sm font-black text-slate-700 shadow-sm" @click="exportCsv">
        Export (CSV)
      </button>
    </template>

    <div>
      <main class="py-0">
        <SampleDataBanner
          :show="selectedSpa === 'all'"
          message="Showing sample wellness activity — publish your own event to see your own numbers here."
          variant="warning"
        />

      <!-- KPI Row -->

      <section class="grid grid-cols-1 gap-4 md:grid-cols-4">

        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Total Revenue</p>

              <p class="text-3xl font-extrabold tracking-tight">{{ money(kpis.gross) }}</p>

              <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.grossDeltaPct).toFixed(1) }}%</span> vs last period</p>

            </div>

            <span class="chip">Total</span>

          </div>

          <div class="mt-3"><canvas ref="sparkGrossRef" class="tinyCanvas"></canvas></div>

        </div>



        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Total Massages</p>

              <p class="text-3xl font-extrabold tracking-tight">{{ kpis.massages }}</p>

              <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.massageDelta) }}</span> vs last period</p>

            </div>

            <span class="chip">Services</span>

          </div>

          <div class="mt-3"><canvas ref="sparkMassagesRef" class="tinyCanvas"></canvas></div>

        </div>



        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Total Add-Ons</p>

              <p class="text-3xl font-extrabold tracking-tight">{{ kpis.addonsCount }}</p>

              <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.addonsDelta) }}</span> vs last period</p>

            </div>

            <span class="chip">Upsell</span>

          </div>

          <div class="mt-3"><canvas ref="sparkAddonsRef" class="tinyCanvas"></canvas></div>

        </div>



        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Checked-In</p>

              <p class="text-3xl font-extrabold tracking-tight">{{ kpis.checkins }}</p>

              <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.checkinsDelta) }}</span> arrivals</p>

            </div>

            <span class="chip">Attendance</span>

          </div>

          <div class="mt-3"><canvas ref="sparkCheckinsRef" class="tinyCanvas"></canvas></div>

        </div>

      </section>



      <!-- KPI Row 2 -->

      <section class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Add-On Revenue</p>

            <span class="chip">Upsell</span>

          </div>

          <p class="mt-1 text-2xl font-extrabold">{{ money(chartsData.quickStats.addonRevenue) }}</p>


          <p class="text-xs text-slate-500 mt-1">Enhancements + extras</p>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Mobile Service Revenue</p>

            <span class="chip">Mobile</span>

          </div>

          <p class="mt-1 text-2xl font-extrabold">{{ money(chartsData.quickStats.mobileRevenue) }}</p>

          <p class="text-xs text-slate-500 mt-1">Mobile wellness bookings</p>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Taxes</p>

            <span class="chip">Tax</span>

          </div>

          <p class="mt-1 text-2xl font-extrabold">{{ money(kpis.totalTaxes || 0) }}</p>

          <p class="text-xs text-slate-500 mt-1">Sum of event tax</p>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">No-shows / Cancellations</p>

            <span class="chip">Risk</span>

          </div>

          <p class="mt-1 text-2xl font-extrabold">{{ chartsData.quickStats.noShowCount }}</p>

          <p class="text-xs text-slate-500 mt-1">Late cancels + no-shows</p>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Avg Rating</p>

            <span class="chip">Reviews</span>

          </div>

          <p class="mt-1 text-2xl font-extrabold">{{ Number(chartsData.quickStats.avgRating || 0).toFixed(1) }}</p>

          <p class="text-xs text-slate-500 mt-1">Last 30 reviews</p>

        </div>

        <div class="card p-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-slate-600">Point of Sale</p>
            <span class="chip">POS</span>
          </div>
          <p class="mt-1 text-2xl font-extrabold">{{ money(kpis.posSalesRevenue || 0) }}</p>
          <p class="text-xs text-slate-500 mt-1">
            {{ (kpis.posTicketsSold || 0).toLocaleString() }} tickets · {{ (kpis.posOrdersCount || 0).toLocaleString() }} orders
          </p>
        </div>

      </section>



      <!-- Category Charts -->

      <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Services by Category</h2>

              <p class="text-sm text-slate-500">Grouped for spa and wellness activity</p>

            </div>

            <span class="chip">Bookings</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="serviceMixRef"></canvas>

          </div>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Revenue Overview</h2>

              <p class="text-sm text-slate-500">Core revenue sources this period</p>

            </div>

            <span class="chip">$</span>

          </div>



          <div class="mt-3 soft-grid rounded-[18px] p-3 space-y-3">

            <div class="statRow">

              <div>

                <p class="text-sm font-semibold text-slate-800">Massage Revenue</p>

                <p class="text-xs text-slate-500">All massage services</p>

              </div>

              <p class="text-lg font-extrabold">{{ money(serviceRevenue.Massage) }}</p>

            </div>



            <div class="statRow">

              <div>

                <p class="text-sm font-semibold text-slate-800">Nail Revenue</p>

                <p class="text-xs text-slate-500">Mani, pedi and nail care</p>

              </div>

              <p class="text-lg font-extrabold">{{ money(serviceRevenue.Nails) }}</p>

            </div>



            <div class="statRow">

              <div>

                <p class="text-sm font-semibold text-slate-800">Mobile Revenue</p>

                <p class="text-xs text-slate-500">Off-site and mobile bookings</p>

              </div>

              <p class="text-lg font-extrabold">{{ money(serviceRevenue.Mobile) }}</p>

            </div>



            <div class="rounded-[18px] border border-slate-200 bg-slate-50 p-3">

              <p class="text-xs text-slate-500">Highest contributor</p>

              <p class="text-base font-bold mt-1">{{ topContributorLabel }}</p>

            </div>

          </div>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Revenue by Category</h2>

              <p class="text-sm text-slate-500">Which offerings are driving income</p>

            </div>

            <span class="chip">$</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="serviceTypeRevenueRef"></canvas>

          </div>

        </div>

      </section>



      <!-- Revenue + Waterfall -->

      <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="card p-4 md:col-span-2">

          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">

            <div>

              <h2 class="text-base font-bold">Revenue by Category Over Time</h2>

              <p class="text-sm text-slate-500">Massage, nails, facials, wellness, add-ons, mobile</p>

            </div>

            <div class="flex items-center gap-2">

              <span class="chip">Range: {{ selectedPeriodLabel }}</span>

              <span class="chip">{{ insightLabel }}</span>

            </div>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="revStackedRef"></canvas>

          </div>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Payout Waterfall</h2>

              <p class="text-sm text-slate-500">Fees + commissions → payout</p>

            </div>

            <span class="chip">Computed</span>

          </div>



          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="payoutWaterfallRef"></canvas>

          </div>



          <div class="mt-3 rounded-[18px] border border-slate-200 bg-slate-50 p-3">

            <p class="text-sm font-semibold">Next payout estimate</p>

            <p class="text-xs text-slate-600 mt-1">Available: <span class="font-semibold">{{ money(chartsData.quickStats.nextAvail ?? 0) }}</span> •
              Expected: <span class="font-semibold">{{ chartsData.quickStats.nextEta ?? '—' }}</span></p>

            <div class="mt-2">

              <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">

                <div class="h-full w-[68%] rounded-full bg-emerald-500"></div>

              </div>

              <p class="text-[11px] text-slate-500 mt-1">Collected → Processing → Available → Paid</p>

            </div>

          </div>

        </div>

      </section>



      <!-- Ops -->

      <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Bookings Timeline</h2>

              <p class="text-sm text-slate-500">Completed vs canceled vs no-show</p>

            </div>

            <span class="chip">Ops</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="bookingTimelineRef"></canvas>

          </div>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Client Retention</h2>

              <p class="text-sm text-slate-500">New vs returning (trend)</p>

            </div>

            <span class="chip">CRM</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="retentionRef"></canvas>

          </div>

        </div>

      </section>



      <!-- Top + Quick -->

      <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="card p-4 md:col-span-2">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Top Services by Revenue</h2>

              <p class="text-sm text-slate-500">Best performers this period</p>

            </div>

            <span class="chip">Ranked</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="topServicesRef"></canvas>

          </div>

        </div>



        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Quick Stats</h2>

              <p class="text-sm text-slate-500">Snapshot</p>

            </div>

            <span class="chip">Today</span>

          </div>



          <div class="mt-3 grid grid-cols-2 gap-3">

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Add-Ons Sold</p>

              <p class="text-xl font-extrabold">{{ chartsData.quickStats.addonsSold ?? 0 }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Mobile Bookings</p>

              <p class="text-xl font-extrabold">{{ chartsData.quickStats.mobileBookings ?? 0 }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Avg Ticket</p>

              <p class="text-xl font-extrabold">{{ money(chartsData.quickStats.avgTicket ?? 0) }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Repeat Rate</p>

              <p class="text-xl font-extrabold">{{ String(chartsData.quickStats.repeatRate ?? 0) }}%</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3 col-span-2">

              <div class="flex items-center justify-between gap-3">

                <div>

                  <p class="text-xs text-slate-500">Complimentary Tickets</p>

                  <p class="text-xl font-extrabold">{{ Number(kpis.complimentaryTickets ?? 0).toLocaleString() }}</p>

                </div>

                <span class="chip">Orders: {{ Number(kpis.complimentaryOrders ?? 0).toLocaleString() }}</span>

              </div>

            </div>

          </div>



          <div class="mt-3 rounded-[18px] border border-slate-200 bg-slate-50 p-3">

            <p class="text-sm font-semibold">Lead Coordinator</p>

            <p class="text-sm text-slate-600">{{ chartsData.quickStats.coordinator ?? '—' }}</p>

            <p class="text-xs text-slate-500 mt-1">Tip: Watch massage, nails, and mobile services closely to spot your
              strongest revenue categories.</p>

          </div>

        </div>

      </section>



      <footer class="mt-8 pb-10 text-center text-xs text-slate-500">

        LinkUp Wellness Inspire Analytics • Demo UI (Chart.js)

      </footer>

    </main>


    </div>
  </AppLayout>
</template>

<style scoped>
:root {

  --lu-blue: #0ea5e9;

  --lu-blue2: #2563eb;

  --spa-teal: #14b8a6;

  --spa-rose: #fb7185;

  --spa-lav: #a78bfa;

  --ink: #0f172a;

  --muted: #64748b;

  --card: #ffffff;

  --bg: #f5f7fb;

  --line: rgba(148, 163, 184, .35);

  --shadow2: 0 10px 26px rgba(2, 6, 23, .08);

  --r: 22px;

}

body {
  background: var(--bg);
  color: var(--ink);
}

.card {

  background: var(--card);

  border: 1px solid rgba(148, 163, 184, .25);

  border-radius: var(--r);

  box-shadow: var(--shadow2);

}

.chip {

  border: 1px solid rgba(148, 163, 184, .35);

  border-radius: 999px;

  padding: .35rem .6rem;

  font-size: .75rem;

  color: black;

  background: rgba(255, 255, 255, .75);

  white-space: nowrap;

}

.soft-grid {

  background-image:

    radial-gradient(circle at 1px 1px, rgba(148, 163, 184, .25) 1px, transparent 0);

  background-size: 18px 18px;

}

.kpiGrad {

  background: linear-gradient(135deg,

      rgba(20, 184, 166, .12),

      rgba(14, 165, 233, .10),

      rgba(167, 139, 250, .10));

}

.btn {

  border: 1px solid rgba(148, 163, 184, .35);

  border-radius: 14px;

  padding: .55rem .8rem;

  background: #fff;

  box-shadow: 0 6px 16px rgba(2, 6, 23, .06);

}

.btn:active {
  transform: translateY(1px);
}

canvas {
  max-height: 340px;
}

.tinyCanvas {
  max-height: 46px;
}

.statRow {

  display: flex;

  align-items: center;

  justify-content: space-between;

  gap: 12px;

  padding: .8rem .9rem;

  border: 1px solid rgba(148, 163, 184, .18);

  border-radius: 16px;

  background: #fff;

}
</style>
