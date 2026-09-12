<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import {
    Archive,
    ArrowLeftRight,
    ArrowUpFromLine,
    BadgeDollarSign,
    Building2,
    Coins as CoinsIcon,
    CreditCard,
    Flame,
    Landmark,
    Layers,
    LayoutDashboard,
    Megaphone,
    Newspaper,
    PiggyBank,
    Radio,
    RefreshCw,
    Send,
    ShoppingBag,
    Sparkles,
    Ticket,
    TrendingUp,
    Utensils,
    Wallet,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
}>();

const sidebarVisible = ref(true);

const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model from Backend
const SCOTIA_SHARE = 0.4;
const units = ref(props.initialUnits);
const countries = ref(props.initialCountries);

const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    nextTick(() => renderCharts());
};

const iconMap: Record<string, any> = {
    ticket: Ticket,
    'badge-dollar-sign': BadgeDollarSign,
    'shopping-bag': ShoppingBag,
    utensils: Utensils,
    'credit-card': CreditCard,
    wallet: Wallet,
    radio: Radio,
    megaphone: Megaphone,
    sparkles: Sparkles,
    flame: Flame,
    newspaper: Newspaper,
};

const colorMap: Record<string, string[]> = {
    tickets: ['#28A8FF', '#1269D3', 'rgba(40,168,255,.18)'],
    subscriptions: ['#8B5CF6', '#5B21B6', 'rgba(139,92,246,.18)'],
    marketplace: ['#00C853', '#008A3D', 'rgba(0,200,83,.18)'],
    eats: ['#F97316', '#C2410C', 'rgba(249,115,22,.18)'],
    merchantPay: ['#06B6D4', '#0E7490', 'rgba(6,182,212,.18)'],
    wallet: ['#22C55E', '#15803D', 'rgba(34,197,94,.18)'],
    live: ['#F43F5E', '#BE123C', 'rgba(244,63,94,.18)'],
    ads: ['#F59E0B', '#B45309', 'rgba(245,158,11,.20)'],
    wellness: ['#EC4899', '#BE185D', 'rgba(236,72,153,.18)'],
    cookouts: ['#EF4444', '#B91C1C', 'rgba(239,68,68,.18)'],
    linkup360: ['#3B82F6', '#1D4ED8', 'rgba(59,130,246,.18)'],
};

const alertColorMap: Record<string, string> = {
    warn: 'bg-amber-50 border-amber-100',
    info: 'bg-sky-50 border-sky-100',
    good: 'bg-green-50 border-green-100',
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n || 0);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n || 0));

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const getFilteredCountries = () => {
    return countries.value.filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const countryFin = (c: any) => {
    let gross = 0,
        platform = 0,
        bank = 0,
        cost = 0;
    units.value.forEach((u) => {
        const v = (c[u.key] || 0) * getScale();
        gross += v;
        platform += v * u.platformRate;
        bank += v * u.bankRate;
        cost += v * u.costRate;
    });
    // Fallback if no unit has volume
    const topUnit = units.value.map((u) => ({ name: u.name, val: (c[u.key] || 0) * getScale() })).sort((a, b) => b.val - a.val)[0];
    const top = topUnit ? topUnit.name : 'N/A';
    return { gross, platform, bank, cost, net: platform - cost, top };
};

const unitFin = (u: any) => {
    const rs = getFilteredCountries();
    const gross = rs.reduce((s, c) => s + (c[u.key] || 0), 0) * getScale();
    const platform = gross * u.platformRate;
    const bank = gross * u.bankRate;
    const cost = gross * u.costRate;
    return { gross, platform, bank, cost, net: platform - cost };
};

const totals = computed(() => {
    const rs = getFilteredCountries();
    const fins = rs.map(countryFin);
    const g = fins.reduce((s, x) => s + x.gross, 0);
    const p = fins.reduce((s, x) => s + x.platform, 0);
    const b = fins.reduce((s, x) => s + x.bank, 0);
    const c = fins.reduce((s, x) => s + x.cost, 0);
    const totalRev = p + b * (1 - SCOTIA_SHARE);

    return {
        gross: g,
        platform: p,
        bank: b,
        cost: c,
        net: totalRev - c,
        users: rs.reduce((s, c) => s + (c.users || 0), 0) * getScale(),
        merchants: rs.reduce((s, c) => s + (c.merchants || 0), 0),
        organizers: rs.reduce((s, c) => s + (c.organizers || 0), 0),
        countries: rs.length,
    };
});

const headerMetrics = computed(() => ({
    gtv: fmt(totals.value.gross),
    linkupRev: fmt(totals.value.platform),
    procPool: fmt(totals.value.bank),
    netProfit: fmt(totals.value.net),
    users: num(totals.value.users),
    merchants: num(totals.value.merchants),
    organizers: num(totals.value.organizers),
    countries: num(totals.value.countries),
}));

const scotiaEarnings = computed(() => totals.value.bank * SCOTIA_SHARE);
const bankKeepShare = computed(() => totals.value.bank * (1 - SCOTIA_SHARE));
const totalLinkupRevenue = computed(() => totals.value.platform + totals.value.bank * (1 - SCOTIA_SHARE));
const takeRate = computed(() => (totals.value.gross ? (totalLinkupRevenue.value / totals.value.gross) * 100 : 0));

const trendSeries = computed(() => {
    const t = totals.value;
    const totalRev = totalLinkupRevenue.value;
    const months = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const g = 0.055,
        gtv: number[] = [],
        rev: number[] = [];
    for (let i = 11; i >= 0; i--) {
        gtv[11 - i] = t.gross / Math.pow(1 + g, i);
        rev[11 - i] = totalRev / Math.pow(1 + g, i);
    }
    return { months, gtv, rev };
});

const trendDelta = computed(() => {
    const rev = trendSeries.value.rev;
    if (rev.length < 2 || !rev[rev.length - 2]) return 0;
    return (rev[rev.length - 1] / rev[rev.length - 2] - 1) * 100;
});

const moneyFlow = computed(() => {
    const t = totals.value;
    return [
        { label: 'Gross Transaction Volume', value: t.gross, color: 'text-slate-700', indent: false },
        { label: 'Platform Fees (LinkUp)', value: t.platform, color: 'text-sky-600', indent: false },
        { label: 'Processing Pool', value: t.bank, color: 'text-amber-600', indent: false },
        { label: '↳ LinkUp 60% of pool', value: bankKeepShare.value, color: 'text-sky-600', indent: true },
        { label: '↳ Scotiabank 40% (payout)', value: scotiaEarnings.value, color: 'text-red-600', indent: true },
        { label: '= Total LinkUp Revenue', value: totalLinkupRevenue.value, color: 'text-emerald-600', indent: false },
        { label: '− Operating Costs', value: t.cost, color: 'text-rose-600', indent: false },
        { label: '= Net Profit', value: t.net, color: 'text-green-600', indent: false },
    ];
});

const smartAlerts = computed(() => {
    const t = totals.value;
    const total = totalLinkupRevenue.value;
    const net = t.net;
    const take = t.gross ? (total / t.gross) * 100 : 0;
    const margin = total ? (net / total) * 100 : 0;
    const a: { type: string; title: string; desc: string }[] = [];

    if (margin < 70)
        a.push({
            type: 'warn',
            title: 'Net margin ' + margin.toFixed(0) + '%',
            desc: 'Operating costs are eating into profit — review cost rates in the Fees Center.',
        });
    else a.push({ type: 'good', title: 'Healthy net margin ' + margin.toFixed(0) + '%', desc: 'Costs are well controlled relative to revenue.' });

    const cr = getFilteredCountries()
        .map((c) => ({ c: c.country, g: countryFin(c).gross }))
        .sort((x, y) => y.g - x.g);
    if (cr.length && t.gross && cr[0].g / t.gross > 0.25)
        a.push({
            type: 'info',
            title: cr[0].c + ' = ' + ((cr[0].g / t.gross) * 100).toFixed(0) + '% of GTV',
            desc: 'High market concentration — diversify into other Caribbean / LatAm markets.',
        });

    a.push({
        type: 'info',
        title: 'Scotiabank payout ' + fmt(t.bank * SCOTIA_SHARE),
        desc: 'Due to the bank partner this period — reconcile in the Scotiabank portal.',
    });
    a.push({
        type: take >= 10 ? 'good' : 'warn',
        title: 'Take rate ' + take.toFixed(1) + '%',
        desc: take >= 10 ? 'Strong monetization of GTV.' : 'Below 10% — consider fee or product-mix optimization in the Fees Center.',
    });

    return a.slice(0, 5);
});

const topCountries = computed(() => {
    return getFilteredCountries()
        .map((c) => ({ name: c.country, val: countryFin(c).gross }))
        .sort((a, b) => b.val - a.val)
        .slice(0, 5);
});

const topProducts = computed(() => {
    return units.value
        .map((u) => ({ name: u.name, val: unitFin(u).gross }))
        .sort((a, b) => b.val - a.val)
        .slice(0, 5);
});

const streamCards = computed(() => {
    const tot = totals.value.gross || 1;
    return units.value.map((u) => {
        const f = unitFin(u);
        const share = (f.gross / tot) * 100;
        const color = colorMap[u.key] || colorMap.tickets;
        return { ...u, gross: f.gross, platform: f.platform, share, color };
    });
});

const openStreamDashboard = (key: string) => {
    alert('In the full app this opens the ' + key + ' revenue-stream dashboard.');
};

const openScotiaPortal = () => {
    alert('The Scotiabank Partner Portal is available in the full app.');
};

const charts = ref<any>({});
const trendChartRef = ref<HTMLCanvasElement | null>(null);
const unitChartRef = ref<HTMLCanvasElement | null>(null);
const splitChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const t = totals.value;
    const ts = trendSeries.value;

    if (trendChartRef.value) {
        charts.value.trend = new Chart(trendChartRef.value, {
            type: 'line',
            data: {
                labels: ts.months,
                datasets: [
                    { label: 'GTV', data: ts.gtv, borderColor: '#28A8FF', backgroundColor: 'rgba(40,168,255,.12)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: 0 },
                    {
                        label: 'Total LinkUp Revenue',
                        data: ts.rev,
                        borderColor: '#00C853',
                        backgroundColor: 'rgba(0,200,83,.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 0,
                    },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    }

    if (unitChartRef.value) {
        charts.value.unit = new Chart(unitChartRef.value, {
            type: 'bar',
            data: {
                labels: units.value.map((u) => u.name),
                datasets: [
                    { label: 'GTV', data: units.value.map((u) => unitFin(u).gross), backgroundColor: '#28A8FF', borderRadius: 8 },
                    { label: 'LinkUp Revenue', data: units.value.map((u) => unitFin(u).platform), backgroundColor: '#D9EC10', borderRadius: 8 },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    }

    if (splitChartRef.value) {
        charts.value.split = new Chart(splitChartRef.value, {
            type: 'doughnut',
            data: {
                labels: ['LinkUp', 'Bank', 'Costs'],
                datasets: [{ data: [t.platform, t.bank, t.cost], backgroundColor: ['#28A8FF', '#F59E0B', '#8B5CF6'] }],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });
    }
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderCharts();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Executive Command Center" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="executive" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Executive Command Center"
                :countries="countries"
                :metrics="headerMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <!-- Metric Cards -->
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-6">
                    <div class="card metric dark rounded-3xl p-5 2xl:col-span-2">
                        <p class="font-bold text-slate-300">Gross Transaction Volume (GTV)</p>
                        <h3 class="mt-2 text-5xl font-black">{{ fmt(totals.gross) }}</h3>
                        <p class="mt-1 font-bold text-lime-300">Total money moving through LinkUp · volume, not revenue</p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm">
                        <p class="font-bold text-slate-500">LinkUp Platform Revenue</p>
                        <h3 class="mt-2 text-4xl font-black text-slate-800">{{ fmt(totals.platform) }}</h3>
                        <p class="font-bold text-sky-500">Fees LinkUp charges (its income)</p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm">
                        <p class="font-bold text-slate-500">Processing Fee Pool</p>
                        <h3 class="mt-2 text-4xl font-black text-slate-800">{{ fmt(totals.bank) }}</h3>
                        <p class="font-bold text-amber-500">Card/bank rail fees</p>
                        <p class="mt-1 text-xs text-slate-500">LinkUp keeps {{ fmt(bankKeepShare) }} (60%) · Scotiabank {{ fmt(scotiaEarnings) }} (40%)</p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm">
                        <p class="font-bold text-slate-500">Operating Costs</p>
                        <h3 class="mt-2 text-4xl font-black text-rose-600">{{ fmt(totals.cost) }}</h3>
                        <p class="font-bold text-rose-400">Cost to run each revenue stream</p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm">
                        <p class="font-bold text-slate-500">Net Profit</p>
                        <h3 class="mt-2 text-4xl font-black text-emerald-600">{{ fmt(totals.net) }}</h3>
                        <p class="font-bold text-emerald-500">Platform revenue − operating costs</p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm">
                        <p class="font-bold text-slate-500">Market Value Est.</p>
                        <h3 class="mt-2 text-4xl font-black text-purple-600">{{ fmt(totals.platform * 60) }}</h3>
                        <p class="font-bold text-purple-500">Valuation · revenue multiple</p>
                    </div>
                    <div
                        class="card metric cursor-pointer rounded-3xl border-slate-100 bg-white p-5 shadow-sm 2xl:col-span-2"
                        @click="openScotiaPortal"
                    >
                        <div class="flex items-center gap-2">
                            <div class="grid h-7 w-7 place-items-center rounded-lg bg-red-600 text-xs font-black text-white">S</div>
                            <p class="font-bold text-slate-500">Scotiabank Partner Earnings</p>
                        </div>
                        <h3 class="mt-2 text-4xl font-black text-slate-800">{{ fmt(scotiaEarnings) }}</h3>
                        <p class="font-bold text-red-500">
                            40% of the processing pool <span class="text-slate-400">· paid to partner · tap for portal</span>
                        </p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm 2xl:col-span-2">
                        <p class="font-bold text-slate-500">Total LinkUp Revenue</p>
                        <h3 class="mt-2 text-4xl font-black text-slate-800">{{ fmt(totalLinkupRevenue) }}</h3>
                        <p class="font-bold text-emerald-600">
                            Platform fees + LinkUp's 60% of processing
                            <span class="text-slate-400">· ▲ {{ trendDelta.toFixed(1) }}% MoM</span>
                        </p>
                    </div>
                    <div class="card metric rounded-3xl border-slate-100 bg-white p-5 shadow-sm 2xl:col-span-2">
                        <p class="font-bold text-slate-500">Take Rate</p>
                        <h3 class="mt-2 text-4xl font-black text-slate-800">{{ takeRate.toFixed(1) }}%</h3>
                        <p class="font-bold text-sky-500">LinkUp revenue ÷ GTV</p>
                    </div>
                </div>

                <!-- Trend + Money Flow -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl bg-white p-6 shadow-sm 2xl:col-span-2">
                        <div class="mb-1 flex items-center justify-between">
                            <h3 class="text-xl font-black text-slate-800">12-Month Performance Trend</h3>
                            <span class="text-sm font-black" :class="trendDelta >= 0 ? 'text-green-600' : 'text-red-600'"
                                >▲ {{ trendDelta.toFixed(1) }}% MoM</span
                            >
                        </div>
                        <p class="mb-4 text-sm text-slate-500">GTV &amp; Total LinkUp Revenue over time</p>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="trendChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="mb-1 text-xl font-black text-slate-800">Money Flow (P&amp;L)</h3>
                        <p class="mb-4 text-sm text-slate-500">How GTV becomes profit</p>
                        <div class="space-y-2">
                            <div
                                v-for="(row, i) in moneyFlow"
                                :key="i"
                                class="flex justify-between"
                                :class="row.indent ? '' : 'border-t border-slate-100 pt-2'"
                            >
                                <span :class="row.indent ? 'pl-4 text-sm text-slate-500' : 'font-black'">{{ row.label }}</span>
                                <b :class="row.color">{{ fmt(row.value) }}</b>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Unit + Money Split -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl bg-white p-6 shadow-sm 2xl:col-span-2">
                        <h3 class="mb-4 text-xl font-black text-slate-800">Business Unit Revenue</h3>
                        <div class="relative h-[320px] w-full">
                            <canvas ref="unitChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-xl font-black text-slate-800">Money Split</h3>
                        <div class="relative h-[320px] w-full">
                            <canvas ref="splitChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Countries / Top Products / AI Alerts -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-xl font-black text-slate-800">Top Countries</h3>
                        <div class="space-y-3">
                            <div v-for="c in topCountries" :key="c.name" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                <b>{{ c.name }}</b><span>{{ fmt(c.val) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-xl font-black text-slate-800">Top Products</h3>
                        <div class="space-y-3">
                            <div v-for="p in topProducts" :key="p.name" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                <b>{{ p.name }}</b><span>{{ fmt(p.val) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-xl font-black text-slate-800">AI Executive Alerts</h3>
                        <div class="space-y-3">
                            <div v-for="(a, i) in smartAlerts" :key="i" class="rounded-2xl border p-4" :class="alertColorMap[a.type]">
                                <b>{{ a.title }}</b>
                                <p class="text-sm text-slate-600">{{ a.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Stream Dashboards -->
                <div class="card rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-800">Revenue Stream Dashboards</h3>
                            <p class="text-slate-500">
                                Open an individual dashboard for any revenue-generating activity. Reacts to the Region, Country &amp; Period filters.
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <button
                            v-for="s in streamCards"
                            :key="s.key"
                            @click="openStreamDashboard(s.key)"
                            class="relative overflow-hidden rounded-3xl p-5 text-left transition hover:-translate-y-0.5 hover:shadow-xl"
                            :style="{ border: '1px solid ' + s.color[2], background: 'linear-gradient(135deg,' + s.color[2] + ',rgba(255,255,255,.92))' }"
                        >
                            <div
                                class="absolute top-0 left-0 h-full w-1.5"
                                :style="{ background: 'linear-gradient(' + s.color[0] + ',' + s.color[1] + ')' }"
                            ></div>
                            <div class="relative flex items-center justify-between">
                                <div
                                    class="grid h-11 w-11 place-items-center rounded-2xl text-white"
                                    :style="{ background: 'linear-gradient(135deg,' + s.color[0] + ',' + s.color[1] + ')' }"
                                >
                                    <component :is="iconMap[s.icon]" class="h-5 w-5" />
                                </div>
                                <span class="text-xs font-black" :style="{ color: s.color[1] }">{{ s.share.toFixed(1) }}% of GTV</span>
                            </div>
                            <h4 class="relative mt-3 text-lg font-black">{{ s.name }}</h4>
                            <div class="relative mt-3 grid grid-cols-2 gap-2 text-sm">
                                <div class="rounded-2xl bg-white/70 p-2">
                                    <p class="text-slate-500">GTV</p>
                                    <b>{{ fmt(s.gross) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/70 p-2">
                                    <p class="text-slate-500">LinkUp Rev</p>
                                    <b>{{ fmt(s.platform) }}</b>
                                </div>
                            </div>
                            <div class="relative mt-3 text-sm font-black" :style="{ color: s.color[1] }">Open Dashboard →</div>
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
