<script setup lang="ts">
import { ref, reactive, computed, onMounted, nextTick, onBeforeUnmount, watch } from 'vue';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import SampleDataBanner from '@/components/organizer/SampleDataBanner.vue';
import { Chart, registerables } from 'chart.js';
import { router } from '@inertiajs/vue3';

Chart.register(...registerables);
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.responsive = true;

interface Event {
    id: number | string;
    title?: string;
    // ... other fields if needed for display
}

interface Props {
    organizer: { id: number; organizer_name: string } | null;
    events: Event[];
    kpis: {
        totalRevenue: number;
        netRevenue?: number;
        totalTaxes?: number;
        ticketsSold: number;
        ticketsCancelled: number;
        posSalesRevenue?: number;
        posTicketsSold?: number;
        posOrdersCount?: number;
        complimentaryTickets?: number;
        complimentaryOrders?: number;
        eventsCount: number;
        sponsorsCount: number;
        scannerCount: number;
        pending_payouts: number;
        completed_payouts: number;
        total_payouts: number;
        grossDeltaPct?: number;
        netDeltaPct?: number;
        ticketsDelta?: number;
    };
    selectedEventId?: number | string | null;

    charts: {
        series: {
            labels: string[];
            tickets: number[];
            drinks: number[];
            merch: number[];
            cashFlow: { collected: number[], fees: number[], netAvail: number[] };
            ticketVelocity: number[];
        };
        ops: {
            drinkRevenue: number;
            tableRevenue: number;
            canceledTickets: number;
            checkedIn: number;
            drinkMixUnits: Record<string, number> | any[];
            barRevenue: Record<string, number> | any[];
            incomeSources: Record<string, number>;
        };
        topEvents: { name: string; val: number }[];
    };
}

const props = defineProps<Props>();

const DATA: Event[] = props.events || [];

// State
const selectedOption = ref<string | number>((props.selectedEventId as any) ?? 'all');
// Parse period from URL or default to hourly
const urlParams = new URLSearchParams(window.location.search);
const currentPeriod = ref<'hourly' | 'daily' | 'weekly'>((urlParams.get('period') as any) ?? 'hourly');

// Watchers for filtering
watch([selectedOption, currentPeriod], ([newEvt, newPeriod]) => {
    const payload: Record<string, any> = {};
    payload.event_id = newEvt;
    payload.period = newPeriod;

    router.get(window.location.pathname, payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => scheduleRender(0)
    });
});

// -- Canvas Refs --
const sparkGrossCanvas = ref<HTMLCanvasElement | null>(null);
const sparkNetCanvas = ref<HTMLCanvasElement | null>(null);
const sparkPendingCanvas = ref<HTMLCanvasElement | null>(null);
const sparkTicketsCanvas = ref<HTMLCanvasElement | null>(null);

const drinkMixCanvas = ref<HTMLCanvasElement | null>(null);
const barRevenueCanvas = ref<HTMLCanvasElement | null>(null);
const incomeSourcesCanvas = ref<HTMLCanvasElement | null>(null);

const revStackedCanvas = ref<HTMLCanvasElement | null>(null);
const payoutWaterfallCanvas = ref<HTMLCanvasElement | null>(null);

const cashFlowCanvas = ref<HTMLCanvasElement | null>(null);
const ticketVelocityCanvas = ref<HTMLCanvasElement | null>(null);

const topEventsCanvas = ref<HTMLCanvasElement | null>(null);

// -- Chart Instances --
const charts: Record<string, Chart | null> = {
    sparkGross: null, sparkNet: null, sparkPending: null, sparkTickets: null,
    drinkMix: null, barRevenue: null, incomeSources: null,
    revStacked: null, payoutWaterfall: null, cashFlow: null,
    ticketVelocity: null, topEvents: null,
};

// -- Helpers --
const sum = (arr: number[]) => arr.reduce((p, c) => p + (c ?? 0), 0);
const fmtUSD = (n: number) => '$' + Math.round(n).toLocaleString();

const exportCsv = () => {
    const rows: any[] = [];

    const labels = props.charts?.series?.labels || [];
    const tickets = props.charts?.series?.tickets || [];
    const drinks = props.charts?.series?.drinks || [];
    const merch = props.charts?.series?.merch || [];
    const collected = props.charts?.series?.cashFlow?.collected || [];
    const fees = props.charts?.series?.cashFlow?.fees || [];
    const netAvail = props.charts?.series?.cashFlow?.netAvail || [];
    const velocity = props.charts?.series?.ticketVelocity || [];

    rows.push([
        'label',
        'tickets',
        'drinks',
        'merch',
        'collected',
        'fees',
        'net_available',
        'ticket_velocity',
    ]);

    for (let i = 0; i < labels.length; i++) {
        rows.push([
            labels[i] ?? '',
            tickets[i] ?? 0,
            drinks[i] ?? 0,
            merch[i] ?? 0,
            collected[i] ?? 0,
            fees[i] ?? 0,
            netAvail[i] ?? 0,
            velocity[i] ?? 0,
        ]);
    }

    const csv = rows
        .map((r) => r.map((v: any) => `"${String(v ?? '').replaceAll('"', '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `organizer-dashboard-${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

const destroyCharts = () => {
    Object.values(charts).forEach(c => c?.destroy());
};

const buildGradient = (ctx: CanvasRenderingContext2D, rgb: string, alphaTop = 0.32, alphaBot = 0.02) => {
    const g = ctx.createLinearGradient(0, 0, 0, 260);
    g.addColorStop(0, `rgba(${rgb},${alphaTop})`);
    g.addColorStop(1, `rgba(${rgb},${alphaBot})`);
    return g;
};

// -- Rendering --
const render = async () => {
    await nextTick();
    destroyCharts();

    const s = props.charts.series;
    const o = props.charts.ops;
    const labels = s.labels || [];

    // 1. Sparklines
    const sparkOpts: any = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 }, line: { tension: 0.35, borderWidth: 2 } }
    };

    const mkSpark = (canvas: HTMLCanvasElement | null, data: number[], color: string, bg: string) => {
        if (!canvas) return null;
        const ctx = canvas.getContext('2d');
        if (!ctx) return null;
        return new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ data, borderColor: color, backgroundColor: bg, fill: true }] },
            options: sparkOpts
        });
    };

    charts.sparkGross = mkSpark(sparkGrossCanvas.value, s.cashFlow.collected, "rgba(14,165,233,1)", "rgba(14,165,233,.14)");
    charts.sparkNet = mkSpark(sparkNetCanvas.value, s.cashFlow.netAvail, "rgba(34,197,94,1)", "rgba(34,197,94,.14)");
    // Pending payout sparkline - simulated or use specific data if available. Using Fees for now as a proxy for activity
    charts.sparkPending = mkSpark(sparkPendingCanvas.value, s.cashFlow.fees, "rgba(245,158,11,1)", "rgba(245,158,11,.14)");
    charts.sparkTickets = mkSpark(sparkTicketsCanvas.value, s.ticketVelocity, "rgba(99,102,241,1)", "rgba(99,102,241,.14)");

    // 2. Drink Mix (Donut)
    if (drinkMixCanvas.value) {
        const mixCtx = drinkMixCanvas.value.getContext('2d');
        if (mixCtx) {
            // Handle array or object from PHP
            const mixData = Array.isArray(o.drinkMixUnits) ? {} : o.drinkMixUnits;
            const mixLabels = Object.keys(mixData);
            const mixVals = Object.values(mixData) as number[];

            charts.drinkMix = new Chart(mixCtx, {
                type: 'doughnut',
                data: {
                    labels: mixLabels.length ? mixLabels : ['No Data'],
                    datasets: [{
                        data: mixVals.length ? mixVals : [1],
                        backgroundColor: mixVals.length ? ['#3aa0ea', '#10b981', '#f59e0b', '#64748b', '#ef4444'] : ['#f1f5f9'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } } }
                }
            });
        }
    }

    // 3. Bar Revenue (Horizontal Bar)
    if (barRevenueCanvas.value) {
        const barCtx = barRevenueCanvas.value.getContext('2d');
        if (barCtx) {
            const barData = Array.isArray(o.barRevenue) ? {} : o.barRevenue;
            const barLabels = Object.keys(barData);
            const barVals = Object.values(barData) as number[];

            charts.barRevenue = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: barLabels.length ? barLabels : ['No Data'],
                    datasets: [{
                        label: 'Revenue',
                        data: barVals.length ? barVals : [0],
                        backgroundColor: '#3aa0ea',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { grid: { color: "rgba(148,163,184,.18)" } }, y: { grid: { display: false } } }
                }
            });
        }
    }

    // 4. Income Sources
    if (incomeSourcesCanvas.value) {
        const incCtx = incomeSourcesCanvas.value.getContext('2d');
        if (incCtx) {
            const incData = o.incomeSources || {};
            const incLabels = Object.keys(incData);
            const incVals = Object.values(incData) as number[];

            charts.incomeSources = new Chart(incCtx, {
                type: 'bar',
                data: {
                    labels: incLabels,
                    datasets: [{
                        label: 'Income',
                        data: incVals,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false } }, y: { grid: { color: "rgba(148,163,184,.18)" } } }
                }
            });
        }
    }

    // 5. Revenue Stacked
    if (revStackedCanvas.value) {
        const rctx = revStackedCanvas.value.getContext('2d');
        if (rctx) {
            charts.revStacked = new Chart(rctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: "Tickets", data: s.tickets, borderColor: "rgba(14,165,233,1)", backgroundColor: buildGradient(rctx, "14,165,233"), fill: true, tension: .35, pointRadius: 0, borderWidth: 2 },
                        { label: "Drinks", data: s.drinks, borderColor: "rgba(34,197,94,1)", backgroundColor: buildGradient(rctx, "34,197,94", .26), fill: true, tension: .35, pointRadius: 0, borderWidth: 2 },
                        { label: "Merch", data: s.merch, borderColor: "rgba(245,158,11,1)", backgroundColor: buildGradient(rctx, "245,158,11", .20), fill: true, tension: .35, pointRadius: 0, borderWidth: 2 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: "bottom", labels: { usePointStyle: true, boxWidth: 10 } }, tooltip: { mode: "index", intersect: false } },
                    scales: { x: { grid: { color: "rgba(148,163,184,.18)" } }, y: { grid: { color: "rgba(148,163,184,.18)" } } }
                }
            });
        }
    }

    // 6. Waterfall (Simplified for now - can be made more dynamic if fees are split)
    if (payoutWaterfallCanvas.value) {
        const wctx = payoutWaterfallCanvas.value.getContext('2d');
        if (wctx) {
            const gross = sum(s.cashFlow.collected);
            const fees = sum(s.cashFlow.fees);
            const net = sum(s.cashFlow.netAvail);
            // Rough split for demo waterfall
            const pf = fees * 0.6;
            const pr = fees * 0.4;

            charts.payoutWaterfall = new Chart(wctx, {
                type: 'bar',
                data: {
                    labels: ["Gross", "Platform Fee", "Processing", "Net Payout"],
                    datasets: [
                        { label: "Base", data: [0, gross - pf, gross - pf - pr, 0], backgroundColor: "rgba(0,0,0,0)", stack: "wf" },
                        { label: "Amount", data: [gross, pf, pr, net], backgroundColor: ["#64748b", "#ef4444", "#f59e0b", "#22c55e"], stack: "wf", borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { color: "rgba(148,163,184,.18)" } } }
                }
            });
        }
    }

    // 7. Cash Flow
    if (cashFlowCanvas.value) {
        const cctx = cashFlowCanvas.value.getContext('2d');
        if (cctx) {
            charts.cashFlow = new Chart(cctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: "Collected", data: s.cashFlow.collected, borderColor: "rgba(14,165,233,1)", tension: .35, pointRadius: 0, borderWidth: 2 },
                        { label: "Fees", data: s.cashFlow.fees, borderColor: "rgba(239,68,68,1)", tension: .35, pointRadius: 0, borderWidth: 2 },
                        { label: "Net Available", data: s.cashFlow.netAvail, borderColor: "rgba(34,197,94,1)", tension: .35, pointRadius: 0, borderWidth: 2 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: "bottom", labels: { usePointStyle: true, boxWidth: 10 } } },
                    scales: { x: { grid: { color: "rgba(148,163,184,.18)" } }, y: { grid: { color: "rgba(148,163,184,.18)" } } }
                }
            });
        }
    }

    // 8. Ticket Velocity
    if (ticketVelocityCanvas.value) {
        const tvctx = ticketVelocityCanvas.value.getContext('2d');
        if (tvctx) {
            const avg = s.ticketVelocity.reduce((a, b) => a + b, 0) / Math.max(1, s.ticketVelocity.length);
            charts.ticketVelocity = new Chart(tvctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: "Sales", data: s.ticketVelocity, borderColor: "rgba(99,102,241,1)", backgroundColor: "rgba(99,102,241,.12)", fill: true, tension: .35, pointRadius: 0, borderWidth: 2 },
                        { label: "Average", data: s.ticketVelocity.map(() => avg), borderColor: "rgba(100,116,139,.85)", borderDash: [6, 6], pointRadius: 0, borderWidth: 2 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: "bottom", labels: { usePointStyle: true, boxWidth: 10 } } },
                    scales: { x: { grid: { color: "rgba(148,163,184,.18)" } }, y: { grid: { color: "rgba(148,163,184,.18)" } } }
                }
            });
        }
    }

    // 9. Top Events
    if (topEventsCanvas.value) {
        const teCtx = topEventsCanvas.value.getContext('2d');
        if (teCtx) {
            charts.topEvents = new Chart(teCtx, {
                type: 'bar',
                data: {
                    labels: props.charts.topEvents.map(x => x.name),
                    datasets: [{ label: "Revenue", data: props.charts.topEvents.map(x => x.val), borderRadius: 6, backgroundColor: '#0ea5e9' }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { grid: { color: "rgba(148,163,184,.18)" } }, y: { grid: { display: false } } }
                }
            });
        }
    }
};

let renderTimer: number | null = null;
const scheduleRender = (delay = 50) => {
    if (renderTimer) clearTimeout(renderTimer);
    renderTimer = window.setTimeout(() => { render(); }, delay);
};

onMounted(() => {
    scheduleRender(0);
    window.addEventListener('resize', () => scheduleRender(50));
    (onBeforeUnmount as any)(() => {
        window.removeEventListener('resize', () => nextTick(render));
        destroyCharts();
    });
});
</script>

<template>
    <AppLayout>
        <!-- Inject filters into the layout header -->
        <template #header-actions>
            <select v-model="selectedOption" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-700 outline-none bg-white/90 shadow-sm">
                <option value="all">All Events</option>
                <option v-for="e in DATA" :key="e.id" :value="e.id">{{ e.title ?? `Event #${e.id}` }}</option>
            </select>

            <div class="rounded-xl bg-white/15 p-1 flex gap-1">
                <button
                    v-for="p in ['hourly', 'daily', 'weekly']"
                    :key="p"
                    @click="currentPeriod = p as any"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-black transition',
                        currentPeriod === p ? 'bg-white text-blue-600' : 'text-white hover:bg-white/20'
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
                    :show="selectedOption === 'all'"
                    message="Showing sample events activity — publish your own event to see your own numbers here."
                    variant="warning"
                />

                <!-- KPI Row (Core) -->
                <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="card kpiGrad p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-slate-600">Gross Revenue</p>
                                <p class="text-3xl font-extrabold tracking-tight">{{ fmtUSD(props.kpis.totalRevenue) }}</p>
                                <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲ {{ (props.kpis.grossDeltaPct ?? 0).toFixed(1) }}%</span> vs last period</p>
                            </div>
                            <span class="chip">Total</span>
                        </div>
                        <div class="mt-3 h-[46px]"><canvas ref="sparkGrossCanvas" class="w-full h-full"></canvas></div>
                    </div>

                    <div class="card kpiGrad p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-slate-600">Total Earnings</p>
                                <p class="text-3xl font-extrabold tracking-tight">{{ fmtUSD(props.kpis.netRevenue ?? sum(props.charts.series.cashFlow.netAvail)) }}</p>
                                <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲ {{ (props.kpis.netDeltaPct ?? 0).toFixed(1) }}%</span> vs last period</p>
                            </div>
                            <span class="chip">Total</span>
                        </div>
                        <div class="mt-3 h-[46px]"><canvas ref="sparkNetCanvas" class="w-full h-full"></canvas></div>
                    </div>

                    <div class="card kpiGrad p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-slate-600">Pending Payouts</p>
                                <p class="text-3xl font-extrabold tracking-tight">{{ fmtUSD(props.kpis.pending_payouts) }}</p>
                                <p class="text-xs text-slate-500 mt-1"><span class="text-amber-600 font-semibold">● Processing</span></p>
                            </div>
                            <span class="chip">Cashout</span>
                        </div>
                        <div class="mt-3 h-[46px]"><canvas ref="sparkPendingCanvas" class="w-full h-full"></canvas></div>
                    </div>

                    <div class="card kpiGrad p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-slate-600">Tickets Sold</p>
                                <p class="text-3xl font-extrabold tracking-tight">{{ (props.kpis.ticketsSold ?? 0).toLocaleString() }}</p>
                                <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-semibold">▲ {{ props.kpis.ticketsDelta ?? 0 }}</span> since last period</p>
                            </div>
                            <span class="chip">Volume</span>
                        </div>
                        <div class="mt-3 h-[46px]"><canvas ref="sparkTicketsCanvas" class="w-full h-full"></canvas></div>
                    </div>
                </section>

                <!-- KPI Row (Ops / Money) -->
                <section class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-600">Total Drink Revenue</p>
                            <span class="chip">Bar</span>
                        </div>
                        <p class="mt-1 text-2xl font-extrabold">{{ fmtUSD(props.charts.ops.drinkRevenue) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Drink tickets + POS</p>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-600">Total Table Revenue</p>
                            <span class="chip">VIP</span>
                        </div>
                        <p class="mt-1 text-2xl font-extrabold">{{ fmtUSD(props.charts.ops.tableRevenue) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Tables sold income</p>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-600">Taxes</p>
                            <span class="chip">Tax</span>
                        </div>
                        <p class="mt-1 text-2xl font-extrabold">{{ fmtUSD(props.kpis.totalTaxes ?? 0) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Sum of event tax</p>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-600">Canceled Tickets</p>
                            <span class="chip">Risk</span>
                        </div>
                        <p class="mt-1 text-2xl font-extrabold">{{ props.charts.ops.canceledTickets }}</p>
                        <p class="text-xs text-slate-500 mt-1">Refunds / chargebacks</p>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-600">Checked In</p>
                            <span class="chip">Door</span>
                        </div>
                        <p class="mt-1 text-2xl font-extrabold">{{ props.charts.ops.checkedIn }}</p>
                        <p class="text-xs text-slate-500 mt-1">Scanned attendees</p>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-600">Point of Sale</p>
                            <span class="chip">POS</span>
                        </div>
                        <p class="mt-1 text-2xl font-extrabold">{{ fmtUSD(props.kpis.posSalesRevenue ?? 0) }}</p>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ (props.kpis.posTicketsSold ?? 0).toLocaleString() }} tickets · {{ (props.kpis.posOrdersCount ?? 0).toLocaleString() }} orders
                        </p>
                    </div>
                </section>

                <!-- NEW: Drink Mix + Income Sources -->
                <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Drink Mix -->
                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Drink Mix</h2>
                                <p class="text-sm text-slate-500">Mix drinks, water, soft drinks, beer</p>
                            </div>
                            <span class="chip">Units</span>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-64">
                            <canvas ref="drinkMixCanvas"></canvas>
                        </div>
                    </div>

                    <!-- Bar Revenue Breakdown -->
                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Bar Revenue</h2>
                                <p class="text-sm text-slate-500">Mix vs water vs bottles</p>
                            </div>
                            <span class="chip">$</span>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-64">
                            <canvas ref="barRevenueCanvas"></canvas>
                        </div>
                    </div>

                    <!-- Income Sources -->
                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Income Sources</h2>
                                <p class="text-sm text-slate-500">Tickets, drinks, bottles, tables</p>
                            </div>
                            <span class="chip">This period</span>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-64">
                            <canvas ref="incomeSourcesCanvas"></canvas>
                        </div>
                    </div>
                </section>

                <!-- Revenue + Waterfall -->
                <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="card p-4 md:col-span-2">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-base font-bold">Revenue by Source</h2>
                                <p class="text-sm text-slate-500">Tickets, drinks, and merch stacked over time</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="chip">Range: {{ currentPeriod.charAt(0).toUpperCase() + currentPeriod.slice(1) }}</span>
                                <span class="chip">Insight: Drinks spike late</span>
                            </div>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-72">
                            <canvas ref="revStackedCanvas"></canvas>
                        </div>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Payout Waterfall</h2>
                                <p class="text-sm text-slate-500">Where the money goes</p>
                            </div>
                            <span class="chip">Transparent</span>
                        </div>

                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-48">
                            <canvas ref="payoutWaterfallCanvas"></canvas>
                        </div>

                        <div class="mt-3 rounded-[18px] border border-slate-200 bg-slate-50 p-3">
                            <p class="text-sm font-semibold">Next payout estimate</p>
                            <p class="text-xs text-slate-600 mt-1">Available: <span class="font-semibold">{{ fmtUSD(props.kpis.pending_payouts) }}</span> • Expected: <span class="font-semibold">Tomorrow</span></p>
                            <div class="mt-2">
                                <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full w-[68%] rounded-full bg-emerald-500"></div>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">Collected → Processing → Available → Paid</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Second Row -->
                <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Cash Flow Timeline</h2>
                                <p class="text-sm text-slate-500">Collected vs fees vs net available</p>
                            </div>
                            <span class="chip">In → Out</span>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-64">
                            <canvas ref="cashFlowCanvas"></canvas>
                        </div>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Ticket Sales Velocity</h2>
                                <p class="text-sm text-slate-500">Momentum and peak hours</p>
                            </div>
                            <span class="chip">Speed</span>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-64">
                            <canvas ref="ticketVelocityCanvas"></canvas>
                        </div>
                    </div>
                </section>

                <!-- Third Row -->
                <section class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="card p-4 md:col-span-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Top Events by Revenue</h2>
                                <p class="text-sm text-slate-500">Best performers this period</p>
                            </div>
                            <span class="chip">Ranked</span>
                        </div>
                        <div class="mt-3 soft-grid rounded-[18px] p-3 h-64">
                            <canvas ref="topEventsCanvas"></canvas>
                        </div>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold">Quick Stats</h2>
                                <p class="text-sm text-slate-500">Operational snapshot</p>
                            </div>
                            <span class="chip">Ops</span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div class="rounded-[18px] border border-slate-200 bg-white p-3">
                                <p class="text-xs text-slate-500">Events</p>
                                <p class="text-xl font-extrabold">{{ props.kpis.eventsCount }}</p>
                            </div>
                            <div class="rounded-[18px] border border-slate-200 bg-white p-3">
                                <p class="text-xs text-slate-500">Sponsors</p>
                                <p class="text-xl font-extrabold">{{ props.kpis.sponsorsCount }}</p>
                            </div>
                            <div class="rounded-[18px] border border-slate-200 bg-white p-3">
                                <p class="text-xs text-slate-500">Scanners</p>
                                <p class="text-xl font-extrabold">{{ props.kpis.scannerCount }}</p>
                            </div>
                            <div class="rounded-[18px] border border-slate-200 bg-white p-3">
                                <p class="text-xs text-slate-500">Cancelled</p>
                                <p class="text-xl font-extrabold">{{ props.kpis.ticketsCancelled }}</p>
                            </div>
                            <div class="rounded-[18px] border border-slate-200 bg-white p-3 col-span-2">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs text-slate-500">Complimentary Tickets</p>
                                        <p class="text-xl font-extrabold">{{ (props.kpis.complimentaryTickets ?? 0).toLocaleString() }}</p>
                                    </div>
                                    <span class="chip">Orders: {{ (props.kpis.complimentaryOrders ?? 0).toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 rounded-[18px] border border-slate-200 bg-slate-50 p-3">
                            <p class="text-sm font-semibold">Organizer</p>
                            <p class="text-sm text-slate-600">{{ props.organizer?.organizer_name ?? '—' }}</p>
                            <p class="text-xs text-slate-500 mt-1">Tip: Add payout bank info to speed cashouts.</p>
                        </div>
                    </div>
                </section>

                <footer class="mt-8 pb-10 text-center text-xs text-slate-500">
                    LinkUp Organizer Analytics • Demo UI (Chart.js)
                </footer>
            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
:root {
    --lu-blue: #0ea5e9;
    --lu-blue2: #2563eb;
    --lu-lime: #22c55e;
    --ink: #0f172a;
    --muted: #64748b;
    --card: #ffffff;
    --bg: #f5f7fb;
    --line: rgba(148, 163, 184, .35);
    --shadow: 0 18px 42px rgba(2, 6, 23, .10);
    --shadow2: 0 10px 26px rgba(2, 6, 23, .08);
    --r: 22px;
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
}

.soft-grid {
    background-image: radial-gradient(circle at 1px 1px, rgba(148, 163, 184, .25) 1px, transparent 0);
    background-size: 18px 18px;
}

.kpiGrad {
    background: linear-gradient(135deg, rgba(14, 165, 233, .12), rgba(34, 197, 94, .10));
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
</style>
