<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { TrendingUp } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;

const units = [
    { key: 'tickets', name: 'Ticket Sales', platformRate: 0.065, bankRate: 0, costRate: 0.01 },
    { key: 'subscriptions', name: 'Subscriptions', platformRate: 1, bankRate: 0, costRate: 0.04 },
    { key: 'marketplace', name: 'Marketplace', platformRate: 0.05, bankRate: 0, costRate: 0.01 },
    { key: 'eats', name: 'LinkUp Eats', platformRate: 0.075, bankRate: 0, costRate: 0.025 },
    { key: 'merchantPay', name: 'Merchant Pay', platformRate: 0.02, bankRate: 0, costRate: 0.007 },
    { key: 'wallet', name: 'Wallet & Money Movement', platformRate: 0.025, bankRate: 0.0175, costRate: 0.008 },
    { key: 'live', name: 'LinkUp Live', platformRate: 0.5, bankRate: 0, costRate: 0.08 },
    { key: 'ads', name: 'Advertising Revenue', platformRate: 1, bankRate: 0, costRate: 0.12 },
    { key: 'wellness', name: 'Wellness & Spa', platformRate: 0.0675, bankRate: 0, costRate: 0.015 },
    { key: 'cookouts', name: 'Cookouts', platformRate: 0.0675, bankRate: 0, costRate: 0.015 },
    { key: 'linkup360', name: 'LinkUp 360 News Ads', platformRate: 1, bankRate: 0, costRate: 0.15 },
];

const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000, coinsPurchased: 420000, coinsRedeemed: 170000 },
    { country: 'Trinidad & Tobago', region: 'Regional', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000, wellness: 150000, cookouts: 120000, linkup360: 47000, coinsPurchased: 360000, coinsRedeemed: 140000 },
    { country: 'Barbados', region: 'Regional', users: 70000, merchants: 380, organizers: 95, tickets: 330000, subscriptions: 42000, marketplace: 190000, eats: 260000, merchantPay: 720000, wallet: 460000, live: 85000, ads: 21000, wellness: 90000, cookouts: 65000, linkup360: 26000, coinsPurchased: 150000, coinsRedeemed: 60000 },
    { country: 'Guyana', region: 'Regional', users: 130000, merchants: 620, organizers: 140, tickets: 420000, subscriptions: 56000, marketplace: 270000, eats: 360000, merchantPay: 980000, wallet: 600000, live: 120000, ads: 28000, wellness: 100000, cookouts: 82000, linkup360: 31000, coinsPurchased: 210000, coinsRedeemed: 85000 },
    { country: 'Dominican Republic', region: 'Regional', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000, wellness: 130000, cookouts: 99000, linkup360: 41000, coinsPurchased: 290000, coinsRedeemed: 120000 },
    { country: 'United States', region: 'International', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000, wellness: 320000, cookouts: 180000, linkup360: 120000, coinsPurchased: 950000, coinsRedeemed: 410000 },
    { country: 'Canada', region: 'International', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000, wellness: 210000, cookouts: 120000, linkup360: 85000, coinsPurchased: 620000, coinsRedeemed: 260000 },
    { country: 'Brazil', region: 'International', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000, wellness: 260000, cookouts: 140000, linkup360: 97000, coinsPurchased: 780000, coinsRedeemed: 330000 },
    { country: 'Colombia', region: 'International', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000, wellness: 180000, cookouts: 95000, linkup360: 70000, coinsPurchased: 520000, coinsRedeemed: 210000 },
];

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    renderAll();
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const getFilteredCountries = () => {
    return countries.filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const countryFin = (c: any) => {
    let gross = 0, platform = 0, bank = 0, cost = 0;
    units.forEach((u) => {
        const v = (c[u.key] || 0) * getScale();
        gross += v;
        platform += v * u.platformRate;
        bank += v * u.bankRate;
        cost += v * u.costRate;
    });
    return { gross, platform, bank, cost, net: platform - cost };
};

const getTotals = () => {
    const rs = getFilteredCountries();
    const fins = rs.map(countryFin);
    return {
        gross: fins.reduce((s, x) => s + x.gross, 0),
        platform: fins.reduce((s, x) => s + x.platform, 0),
        bank: fins.reduce((s, x) => s + x.bank, 0),
        cost: fins.reduce((s, x) => s + x.cost, 0),
        net: fins.reduce((s, x) => s + x.net, 0),
        users: rs.reduce((s, c) => s + c.users, 0) * getScale(),
        merchants: rs.reduce((s, c) => s + c.merchants, 0),
        organizers: rs.reduce((s, c) => s + c.organizers, 0),
        countries: rs.length,
    };
};

const growthRate = ref(5.5);

const forecastData = computed(() => {
    const t = getTotals();
    const sc = getScale() || 1;
    const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
    const netProfit = totalRev - t.cost;

    // monthly run-rate
    const baseGTV = t.gross / sc;
    const baseRev = totalRev / sc;
    const baseNet = netProfit / sc;

    const g = growthRate.value / 100;
    let cumulativeRevenue = 0;
    const months = [];

    for (let i = 0; i < 12; i++) {
        const factor = Math.pow(1 + g, i + 1);
        const gtv = baseGTV * factor;
        const rev = baseRev * factor;
        const np = baseNet * factor;
        cumulativeRevenue += rev;
        months.push({
            month: `Month ${i + 1}`,
            gtv,
            revenue: rev,
            netProfit: np,
            cumulative: cumulativeRevenue
        });
    }

    return {
        months,
        total12moGTV: months[11].gtv,
        total12moRev: cumulativeRevenue,
        total12moNet: months.reduce((s, m) => s + m.netProfit, 0),
        arr: baseRev * 12
    };
});

// Charts
const charts = ref<any>({});
const forecastChartRef = ref<HTMLCanvasElement | null>(null);
const fiveYearChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const fd = forecastData.value;

    // 12 Month Chart
    const forecastCtx = forecastChartRef.value;
    if (forecastCtx) {
        charts.value.forecast = new Chart(forecastCtx, {
            type: 'line',
            data: {
                labels: fd.months.map(m => m.month),
                datasets: [
                    {
                        label: 'GTV',
                        data: fd.months.map(m => m.gtv),
                        borderColor: '#28A8FF',
                        backgroundColor: 'rgba(40,168,255,0.1)',
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'LinkUp Revenue',
                        data: fd.months.map(m => m.revenue),
                        borderColor: '#00C853',
                        backgroundColor: 'rgba(0,200,83,0.1)',
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { usePointStyle: true } } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } },
            }
        });
    }

    // 5 Year Chart
    const fiveYearCtx = fiveYearChartRef.value;
    if (fiveYearCtx) {
        const t = getTotals();
        const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
        const baseRev = totalRev / (getScale() || 1);

        // Use a simple 5-year multiplier for Y1-Y5
        const labels = ['Y1', 'Y2', 'Y3', 'Y4', 'Y5'];
        const data = labels.map((_, i) => baseRev * 12 * Math.pow(1.42, i));

        charts.value.fiveYear = new Chart(fiveYearCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'LinkUp Revenue',
                    data,
                    backgroundColor: '#D9EC10',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { usePointStyle: true } } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } },
            }
        });
    }
};

const renderAll = async () => {
    await nextTick();
    const t = getTotals();
    const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
    const net = totalRev - t.cost;

    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('rGTV', fmt(t.gross));
    set('rLinkUp', fmt(t.platform));
    set('rBank', fmt(t.bank));
    set('rNet', fmt(net));
    set('rUsers', num(t.users));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(t.countries));
    set('sideGTV', fmt(t.gross));

    renderCharts();
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Forecasting" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="forecast" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Forecasting" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-slate-800">Forecasting</h3>
                        <p class="text-slate-500">Projections off the current monthly run-rate. Adjust the growth assumption to model scenarios.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-bold text-slate-600">Monthly growth %</label>
                        <input
                            v-model="growthRate"
                            type="number"
                            step="0.5"
                            class="w-24 rounded-2xl border border-slate-200 px-4 py-2 font-bold"
                            @input="renderCharts"
                        />
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="card metric dark rounded-3xl p-5">
                        <p class="font-bold text-slate-300">Projected GTV — 12 mo</p>
                        <h3 class="mt-1 text-3xl font-black">{{ fmt(forecastData.total12moGTV) }}</h3>
                        <p class="text-sm font-bold text-lime-300">at {{ growthRate }}%/mo growth</p>
                    </div>
                    <div class="card rounded-3xl p-5 shadow-sm">
                        <p class="font-bold text-slate-500">LinkUp Revenue — 12 mo</p>
                        <h3 class="mt-1 text-3xl font-black">{{ fmt(forecastData.total12moRev) }}</h3>
                        <p class="text-sm font-bold text-green-600">Cumulative</p>
                    </div>
                    <div class="card rounded-3xl p-5 shadow-sm">
                        <p class="font-bold text-slate-500">Net Profit — 12 mo</p>
                        <h3 class="mt-1 text-3xl font-black">{{ fmt(forecastData.total12moNet) }}</h3>
                        <p class="text-sm font-bold text-green-600">Cumulative</p>
                    </div>
                    <div class="card rounded-3xl p-5 shadow-sm">
                        <p class="font-bold text-slate-500">Run-Rate (ARR)</p>
                        <h3 class="mt-1 text-3xl font-black">{{ fmt(forecastData.arr) }}</h3>
                        <p class="text-sm font-bold text-purple-500">Annualized revenue</p>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">12 Month Forecast</h3>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="forecastChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">5 Year Forecast</h3>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="fiveYearChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Projection Table -->
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Month-by-Month Projection</h3>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs font-black text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3">Month</th>
                                    <th>GTV</th>
                                    <th>LinkUp Revenue</th>
                                    <th>Net Profit</th>
                                    <th>Cumulative Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in forecastData.months" :key="m.month" class="border-t">
                                    <td class="py-3 font-black">{{ m.month }}</td>
                                    <td>{{ fmt(m.gtv) }}</td>
                                    <td class="font-bold text-green-600">{{ fmt(m.revenue) }}</td>
                                    <td>{{ fmt(m.netProfit) }}</td>
                                    <td class="font-black">{{ fmt(m.cumulative) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
