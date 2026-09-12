<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import SampleDataBanner from '@/components/organizer/SampleDataBanner.vue';
import { router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';

const props = defineProps({
  cookoutData: {
    type: Object,
    required: true
  }
});

const selectedPeriod = ref<string>(props.cookoutData?.currentPeriod ?? 'hourly');
const selectedView = ref<string | number>(props.cookoutData?.currentView ?? 'all');

const money = (n: any) => {
  const x = Math.round((Number(n) || 0) * 100) / 100;
  return x.toLocaleString(undefined, { style: 'currency', currency: 'USD' });
};

const counts = computed(() => props.cookoutData?.counts ?? { plates: [], addons: [], orders: [], collected: [], drinkUnits: [] });
const chartsData = computed(
  () =>
    props.cookoutData?.charts ?? {
      proteinMix: { unitsByName: {} },
      revenueOverview: {
        foodRevenue: 0,
        drinkRevenue: 0,
        manualAddOnRevenue: 0,
        topBucketLabel: '—',
        topBucketValue: 0,
        topMain: '—',
      },
      revenueByStream: { labels: [], data: [] },
      topMenuItemsByRevenue: { items: [] },
      quickStats: {
        totalOrders: 0,
        drinkUnitsSold: 0,
        mostPopularMain: '—',
        mostPopularDrink: null,
        organizer: null,
      },
    },
);

const kpis = computed(
  () =>
    props.cookoutData?.kpis ?? {
      totalRevenue: 0,
      totalPlates: 0,
      totalAddOns: 0,
      totalOrders: 0,
      foodCollected: 0,
      drinkUnitsSold: 0,
      complimentaryTickets: 0,
      complimentaryOrders: 0,
      foodRevenue: 0,
      drinkRevenue: 0,
      manualAddOnRevenue: 0,
      totalTaxes: 0,
      avgOrder: 0,
      revenueDeltaPct: 0,
      platesDelta: 0,
      addOnsDelta: 0,
      foodCollectedDelta: 0,
    },
);

const overview = computed(
  () =>
    chartsData.value?.revenueOverview ?? {
      topBucketLabel: '—',
      topBucketValue: 0,
      topMain: '—',
    },
);

const grossByLabel = computed(() => props.cookoutData?.grossByLabel ?? []);

// --- Filters / reload ---
const updateFilters = () => {
  router.visit(window.location.pathname, {
    data: {
      period: selectedPeriod.value,
      event_id: selectedView.value,
    },
    preserveScroll: true,
    preserveState: true,
    only: ['cookoutData'],
  });
};

const setPeriod = (p: string) => {
  selectedPeriod.value = p;
  updateFilters();
};

watch(
  () => props.cookoutData,
  () => {
    selectedPeriod.value = props.cookoutData?.currentPeriod ?? selectedPeriod.value;
    selectedView.value = props.cookoutData?.currentView ?? selectedView.value;
  },
  { deep: true },
);

// --- CSV export ---
const exportCsv = () => {
  const rows: any[] = [
    ['Period', selectedPeriod.value],
    ['View', selectedView.value],
    [],
    ['Metric', 'Value'],
    ['Total Revenue', kpis.value.totalRevenue],
    ['Total Plates', kpis.value.totalPlates],
    ['Total Add-Ons', kpis.value.totalAddOns],
    ['Total Orders', kpis.value.totalOrders],
    ['Food Collected', kpis.value.foodCollected],
    ['Food Revenue', kpis.value.foodRevenue],
    ['Drink Revenue', kpis.value.drinkRevenue],
    ['Manual Add-On Revenue', kpis.value.manualAddOnRevenue],
    ['Avg Order', kpis.value.avgOrder],
  ];

  rows.push([]);
  rows.push(['Protein Mix', 'Units']);
  Object.entries((chartsData.value?.proteinMix?.unitsByName ?? {}) as Record<string, any>).forEach(([k, v]) => rows.push([k, v]));

  rows.push([]);
  rows.push(['Top Menu Items by Revenue', 'Amount']);
  (chartsData.value?.topMenuItemsByRevenue?.items ?? []).forEach((item: any) => rows.push([item.name, item.val]));

  const csv = rows
    .map((r) => r.map((v: any) => String(v).replaceAll('"', '""')).map((v: string) => `"${v}"`).join(','))
    .join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `cookout_dashboard_${selectedPeriod.value}_${selectedView.value}.csv`;
  a.click();
  URL.revokeObjectURL(url);
};

// --- Charts ---
const sparkRevenueRef = ref<HTMLCanvasElement | null>(null);
const sparkPlatesRef = ref<HTMLCanvasElement | null>(null);
const sparkAddonsRef = ref<HTMLCanvasElement | null>(null);
const sparkCollectedRef = ref<HTMLCanvasElement | null>(null);
const proteinMixRef = ref<HTMLCanvasElement | null>(null);
const streamRevenueRef = ref<HTMLCanvasElement | null>(null);
const topItemsRef = ref<HTMLCanvasElement | null>(null);

let charts: Record<string, Chart | null> = {
  sparkRevenue: null,
  sparkPlates: null,
  sparkAddons: null,
  sparkCollected: null,
  proteinMix: null,
  streamRevenue: null,
  topItems: null,
};

const destroyCharts = () => {
  Object.values(charts).forEach((c) => {
    try {
      c?.destroy();
    } catch (e) { }
  });
  charts = {
    sparkRevenue: null,
    sparkPlates: null,
    sparkAddons: null,
    sparkCollected: null,
    proteinMix: null,
    streamRevenue: null,
    topItems: null,
  };
};

const sparkOpts: any = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false }, tooltip: { enabled: false } },
  scales: { x: { display: false }, y: { display: false } },
  elements: { point: { radius: 0 }, line: { tension: 0.35, borderWidth: 2 } },
};

const renderCharts = () => {
  destroyCharts();

  const spark = (canvas: HTMLCanvasElement | null, data: any[], border: string, fill: string) => {
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;
    return new Chart(ctx, {
      type: 'line',
      data: { labels: data.map((_, i) => i + 1), datasets: [{ data, borderColor: border, backgroundColor: fill, fill: true }] },
      options: sparkOpts,
    });
  };

  charts.sparkRevenue = spark(sparkRevenueRef.value, grossByLabel.value, 'rgba(249,115,22,1)', 'rgba(249,115,22,.14)');
  charts.sparkPlates = spark(sparkPlatesRef.value, counts.value?.plates ?? [], 'rgba(14,165,233,1)', 'rgba(14,165,233,.14)');
  charts.sparkAddons = spark(sparkAddonsRef.value, counts.value?.addons ?? [], 'rgba(167,139,250,1)', 'rgba(167,139,250,.14)');
  charts.sparkCollected = spark(sparkCollectedRef.value, counts.value?.collected ?? [], 'rgba(34,197,94,1)', 'rgba(34,197,94,.14)');

  if (proteinMixRef.value) {
    const ctx = proteinMixRef.value.getContext('2d');
    if (ctx) {
      const mix = chartsData.value?.proteinMix?.unitsByName ?? {};
      charts.proteinMix = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: Object.keys(mix),
          datasets: [{ data: Object.values(mix), borderWidth: 0 }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } },
            tooltip: { callbacks: { label: (c: any) => ` ${c.label}: ${c.raw} units` } },
          },
          cutout: '62%',
        },
      });
    }
  }

  if (streamRevenueRef.value) {
    const ctx = streamRevenueRef.value.getContext('2d');
    if (ctx) {
      const rev = chartsData.value?.revenueByStream ?? { labels: [], data: [] };
      charts.streamRevenue = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: (rev.labels ?? []) as any,
          datasets: [
            {
              label: 'Revenue',
              data: (rev.data ?? []) as any,
              borderRadius: 12,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c: any) => ` ${money(c.raw)}` } } },
          scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(148,163,184,.18)' }, ticks: { callback: (v: any) => `$${v}` } },
          },
        },
      });
    }
  }

  if (topItemsRef.value) {
    const ctx = topItemsRef.value.getContext('2d');
    if (ctx) {
      const items = chartsData.value?.topMenuItemsByRevenue?.items ?? [];
      charts.topItems = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: items.map((x: any) => x.name),
          datasets: [{ label: 'Revenue', data: items.map((x: any) => x.val), borderRadius: 12 }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c: any) => ` ${money(c.raw)}` } } },
          scales: {
            x: { grid: { color: 'rgba(148,163,184,.18)' }, ticks: { callback: (v: any) => `$${v}` } },
            y: { grid: { display: false } },
          },
        },
      });
    }
  }
};

watch(
  () => props.cookoutData,
  () => {
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
      <select v-model="selectedView" @change="updateFilters" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-700 outline-none bg-white/90 shadow-sm">
        <option value="all">All Cookouts</option>
        <option v-for="event in cookoutData.events" :key="event.id" :value="event.id">{{ event.title }}</option>
      </select>

      <div class="rounded-xl bg-white/15 p-1 flex gap-1">
        <button
          v-for="p in ['hourly', 'daily', 'weekly']"
          :key="p"
          @click="setPeriod(p)"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-black transition',
            selectedPeriod === p ? 'bg-white text-orange-600' : 'text-white hover:bg-white/20'
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
          :show="selectedView === 'all'"
          message="Showing sample cookouts activity — publish your own event to see your own numbers here."
          variant="warning"
        />

      <!-- KPI Row -->
      <section class="grid grid-cols-1 gap-4 md:grid-cols-4">

        <div class="card kpiGrad p-4">
          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Total Revenue</p>

              <p id="kpiRevenue" class="text-3xl font-extrabold tracking-tight">{{ money(kpis.totalRevenue) }}</p>

              <p class="text-xs text-slate-500 mt-1"><span id="kpiRevenueDelta" class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.revenueDeltaPct).toFixed(1) }}%</span> vs last point</p>

            </div>

            <span class="chip">All streams</span>

          </div>

          <div class="mt-3"><canvas ref="sparkRevenueRef" class="tinyCanvas"></canvas></div>

        </div>

        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Total Plates</p>

              <p id="kpiPlates" class="text-3xl font-extrabold tracking-tight">{{ kpis.totalPlates }}</p>

              <p class="text-xs text-slate-500 mt-1"><span id="kpiPlatesDelta" class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.platesDelta) }}</span> sold</p>

            </div>

            <span class="chip">Food</span>

          </div>

          <div class="mt-3"><canvas ref="sparkPlatesRef" class="tinyCanvas"></canvas></div>

        </div>

        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Total Add-Ons</p>

              <p id="kpiAddons" class="text-3xl font-extrabold tracking-tight">{{ kpis.totalAddOns }}</p>

              <p class="text-xs text-slate-500 mt-1"><span id="kpiAddonsDelta" class="text-emerald-600 font-semibold">▲
                  {{ Math.max(0, kpis.addOnsDelta) }}</span> extras</p>

            </div>

            <span class="chip">Upsell</span>

          </div>

          <div class="mt-3"><canvas ref="sparkAddonsRef" class="tinyCanvas"></canvas></div>

        </div>

        <div class="card kpiGrad p-4">

          <div class="flex items-start justify-between gap-3">

            <div>

              <p class="text-sm text-slate-600">Food Collected</p>

              <p id="kpiCollected" class="text-3xl font-extrabold tracking-tight">{{ kpis.foodCollected }}</p>

              <p class="text-xs text-slate-500 mt-1"><span id="kpiCollectedDelta"
                  class="text-emerald-600 font-semibold">▲ {{ Math.max(0, kpis.foodCollectedDelta) }}</span> served</p>

            </div>

            <span class="chip">Pickup</span>

          </div>

          <div class="mt-3"><canvas ref="sparkCollectedRef" class="tinyCanvas"></canvas></div>

        </div>

      </section>

      <!-- KPI Row 2 -->
      <section class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Food Revenue</p>

            <span class="chip">Core</span>

          </div>

          <p id="kpiFoodRevenue" class="mt-1 text-2xl font-extrabold">{{ money(kpis.foodRevenue) }}</p>

          <p class="text-xs text-slate-500 mt-1">Main food sales</p>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Drink Revenue</p>

            <span class="chip">Drinks</span>

          </div>

          <p id="kpiDrinkRevenue" class="mt-1 text-2xl font-extrabold">{{ money(kpis.drinkRevenue) }}</p>

          <p class="text-xs text-slate-500 mt-1">Water, soda, juice, sorrel and more</p>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Manual Add-On Revenue</p>

            <span class="chip">Custom</span>

          </div>

          <p id="kpiManualAddonRevenue" class="mt-1 text-2xl font-extrabold">{{
            money(kpis.manualAddOnRevenue) }}</p>

          <p class="text-xs text-slate-500 mt-1">Custom paid extras</p>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Taxes</p>

            <span class="chip">Tax</span>

          </div>

          <p class="mt-1 text-2xl font-extrabold">{{ money(kpis.totalTaxes) }}</p>

          <p class="text-xs text-slate-500 mt-1">Sum of event tax</p>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <p class="text-sm text-slate-600">Avg Order</p>

            <span class="chip">Spend</span>

          </div>

          <p id="kpiAvgOrder" class="mt-1 text-2xl font-extrabold">{{ money(kpis.avgOrder) }}</p>

          <p class="text-xs text-slate-500 mt-1">Average revenue per order</p>

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

      <!-- Main content -->
      <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Protein Mix</h2>

              <p class="text-sm text-slate-500">Most popular mains</p>

            </div>

            <span class="chip">Units</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="proteinMixRef"></canvas>

          </div>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Revenue Overview</h2>

              <p class="text-sm text-slate-500">Core money buckets this period</p>

            </div>

            <span class="chip">$</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3 space-y-3">

            <div class="statRow">

              <div>

                <p class="text-sm font-semibold text-slate-800">Food Revenue</p>

                <p class="text-xs text-slate-500">Plates and mains</p>

              </div>

              <p id="revOverviewFood" class="text-lg font-extrabold">{{ money(kpis.foodRevenue) }}</p>

            </div>

            <div class="statRow">

              <div>

                <p class="text-sm font-semibold text-slate-800">Drink Revenue</p>

                <p class="text-xs text-slate-500">Drink sales and drink add-ons</p>

              </div>

              <p id="revOverviewDrinks" class="text-lg font-extrabold">{{ money(kpis.drinkRevenue) }}</p>

            </div>

            <div class="statRow">

              <div>

                <p class="text-sm font-semibold text-slate-800">Manual Add-On Revenue</p>

                <p class="text-xs text-slate-500">Custom add-on charges</p>

              </div>

              <p id="revOverviewManualAddons" class="text-lg font-extrabold">{{
                money(kpis.manualAddOnRevenue) }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-slate-50 p-3">

              <p class="text-xs text-slate-500">Highest contributor</p>

              <p id="revOverviewTop" class="text-base font-bold mt-1">{{ `${overview.topBucketLabel} •
                ${money(overview.topBucketValue)}` }}</p>

            </div>

          </div>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Revenue by Stream</h2>

              <p class="text-sm text-slate-500">Food, drinks and manual add-ons</p>

            </div>

            <span class="chip">$</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="streamRevenueRef"></canvas>

          </div>

        </div>

      </section>

      <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="card p-4 md:col-span-2">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Top Menu Items by Revenue</h2>

              <p class="text-sm text-slate-500">Best performers this period</p>

            </div>

            <span class="chip">Ranked</span>

          </div>

          <div class="mt-3 soft-grid rounded-[18px] p-3">

            <canvas ref="topItemsRef"></canvas>

          </div>

        </div>

        <div class="card p-4">

          <div class="flex items-center justify-between">

            <div>

              <h2 class="text-base font-bold">Quick Stats</h2>

              <p class="text-sm text-slate-500">Snapshot</p>

            </div>

            <span class="chip">Cookout</span>

          </div>

          <div class="mt-3 grid grid-cols-2 gap-3">

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Total Orders</p>

              <p id="qsOrders" class="text-xl font-extrabold">{{ chartsData.quickStats.totalOrders }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Drink Units Sold</p>

              <p id="qsDrinkUnits" class="text-xl font-extrabold">{{ chartsData.quickStats.drinkUnitsSold }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Most Popular Main</p>

              <p id="qsTopMain" class="text-base font-extrabold leading-tight">{{ chartsData.quickStats.mostPopularMain || '—' }}</p>

            </div>

            <div class="rounded-[18px] border border-slate-200 bg-white p-3">

              <p class="text-xs text-slate-500">Most Popular Drink</p>

              <p id="qsTopDrink" class="text-base font-extrabold leading-tight">{{ chartsData.quickStats.mostPopularDrink || '—' }}</p>

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

            <p class="text-sm font-semibold">Organizer</p>

            <p class="text-sm text-slate-600" id="organizerName">{{ chartsData.quickStats.organizer || '—' }}</p>

            <p class="text-xs text-slate-500 mt-1">Tip: Compare plates sold with food collected to spot unclaimed meals
              or service gaps.</p>

          </div>

        </div>

      </section>

      </main>
    </div>
  </AppLayout>

</template>

<style scoped>
body {
  background: #f5f7fb;
  color: #0f172a;
}

.card {

  background: #ffffff;

  border: 1px solid rgba(148, 163, 184, .25);

  border-radius: 22px;

  box-shadow: 0 10px 26px rgba(2, 6, 23, .08);

}

.chip {

  border: 1px solid rgba(148, 163, 184, .35);

  border-radius: 999px;

  padding: .35rem .6rem;

  font-size: .75rem;

  color: #64748b;

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
      rgba(249, 115, 22, .12),
      rgba(245, 158, 11, .10),
      rgba(34, 197, 94, .10));

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

.active-period {
  border-color: rgba(249, 115, 22, .55);
  background: rgba(249, 115, 22, .10);
  color: #9a3412;
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

canvas {
  max-height: 340px;
}

.tinyCanvas {
  max-height: 46px;
}
</style>
