<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import {
    AlertCircle,
    Banknote,
    CheckCircle,
    ChevronDown,
    ChevronRight,
    Clock,
    Download,
    Info,
    Search,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import '@/../../resources/css/new_admin.css';

type Unit = {
    key: string;
    name: string;
    platformRate: number;
    bankRate: number;
    costRate: number;
};

type Country = {
    country: string;
    region: string;
    users: number;
    merchants: number;
    organizers: number;
    [key: string]: string | number;
};

type PayoutRequest = {
    id: number;
    requestId: string;
    date: string;
    category: string;
    name: string;
    email: string;
    country: string;
    amount: number;
    feePercent: number;
    bank: string;
    account: string;
    accountMasked: string;
    routing: string;
    method: string;
    status: string;
    risk: string;
    notes: string;
    proof: string;
};

type AuditTrail = {
    date: string;
    admin: string;
    action: string;
    detail: string;
};

const props = defineProps<{
    initialUnits?: Unit[];
    initialCountries?: Country[];
    initialPayoutRequests?: PayoutRequest[];
    initialAuditTrails?: AuditTrail[];
    initialTotals?: any;
}>();

const SCOTIA_SHARE = 0.4;

const fallbackUnits: Unit[] = [
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

const fallbackCountries: Country[] = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000 },
    { country: 'Trinidad & Tobago', region: 'Regional', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000, wellness: 150000, cookouts: 120000, linkup360: 47000 },
    { country: 'Barbados', region: 'Regional', users: 70000, merchants: 380, organizers: 95, tickets: 330000, subscriptions: 42000, marketplace: 190000, eats: 260000, merchantPay: 720000, wallet: 460000, live: 85000, ads: 21000, wellness: 90000, cookouts: 65000, linkup360: 26000 },
    { country: 'Guyana', region: 'Regional', users: 130000, merchants: 620, organizers: 140, tickets: 420000, subscriptions: 56000, marketplace: 270000, eats: 360000, merchantPay: 980000, wallet: 600000, live: 120000, ads: 28000, wellness: 100000, cookouts: 82000, linkup360: 31000 },
    { country: 'Dominican Republic', region: 'Regional', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000, wellness: 130000, cookouts: 99000, linkup360: 41000 },
    { country: 'United States', region: 'International', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000, wellness: 320000, cookouts: 180000, linkup360: 120000 },
    { country: 'Canada', region: 'International', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000, wellness: 210000, cookouts: 120000, linkup360: 85000 },
    { country: 'Brazil', region: 'International', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000, wellness: 260000, cookouts: 140000, linkup360: 97000 },
    { country: 'Colombia', region: 'International', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000, wellness: 180000, cookouts: 95000, linkup360: 70000 },
];

const payoutRequests = computed(() => props.initialPayoutRequests || []);

const payoutAudit = computed(() => props.initialAuditTrails || []);

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const payoutSearch = ref('');
const statusFilter = ref('All Statuses');
const categoryFilter = ref('All Categories');

const categoryCanvas = ref<HTMLCanvasElement | null>(null);
const statusCanvas = ref<HTMLCanvasElement | null>(null);
const charts: { category?: Chart; status?: Chart } = {};

const countries = computed(() => (props.initialCountries?.length ? props.initialCountries : fallbackCountries));
const units = computed(() => (props.initialUnits?.length ? props.initialUnits : fallbackUnits));

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n || 0);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n || 0));

const scale = computed(() => {
    const period = filters.value.period;
    if (period === 'Today') return 1 / 30;
    if (period === 'Weekly') return 0.25;
    if (period === 'Quarterly') return 3;
    if (period === 'Yearly') return 12;
    if (period === '5-Year') return 60;
    return 1;
});

const filteredCountries = computed(() =>
    countries.value.filter((country) => {
        const regionOk = filters.value.region === 'All' || country.region === filters.value.region;
        const countryOk = filters.value.country === 'All Countries' || country.country === filters.value.country;
        return regionOk && countryOk;
    }),
);

const countryFin = (country: Country) => {
    return units.value.reduce(
        (total, unit) => {
            const volume = Number(country[unit.key] || 0) * scale.value;
            total.gross += volume;
            total.platform += volume * unit.platformRate;
            total.bank += volume * unit.bankRate;
            total.cost += volume * unit.costRate;
            return total;
        },
        { gross: 0, platform: 0, bank: 0, cost: 0 },
    );
};

const platformTotals = computed(() => {
    return filteredCountries.value.reduce(
        (total, country) => {
            const fin = countryFin(country);
            total.gross += fin.gross;
            total.platform += fin.platform;
            total.bank += fin.bank;
            total.cost += fin.cost;
            total.users += Number(country.users || 0) * scale.value;
            total.merchants += Number(country.merchants || 0);
            total.organizers += Number(country.organizers || 0);
            return total;
        },
        { gross: 0, platform: 0, bank: 0, cost: 0, users: 0, merchants: 0, organizers: 0 },
    );
});

const ribbonMetrics = computed(() => ({
    gtv: fmt(platformTotals.value.gross),
    linkupRev: fmt(platformTotals.value.platform),
    procPool: fmt(platformTotals.value.bank),
    netProfit: fmt(platformTotals.value.platform - platformTotals.value.cost + platformTotals.value.bank * (1 - SCOTIA_SHARE)),
    users: num(platformTotals.value.users),
    merchants: num(platformTotals.value.merchants),
    organizers: num(platformTotals.value.organizers),
    countries: num(filteredCountries.value.length),
}));

const payoutFee = (p: PayoutRequest) => p.amount * (p.feePercent / 100);
const payoutNet = (p: PayoutRequest) => p.amount - payoutFee(p);

const filteredPayoutRequests = computed(() => {
    const q = payoutSearch.value.toLowerCase().trim();
    const status = statusFilter.value;
    const cat = categoryFilter.value;

    return payoutRequests.value.filter((p) => {
        const okStatus = status === 'All Statuses' || p.status === status;
        const okCat = cat === 'All Categories' || p.category === cat;
        const okSearch = !q || [p.requestId, p.name, p.email, p.country, p.category, p.bank, p.accountMasked, p.status, p.risk].join(' ').toLowerCase().includes(q);
        return okStatus && okCat && okSearch;
    });
});

const totals = computed(() => {
    if (props.initialTotals) return props.initialTotals;

    return payoutRequests.value.reduce((a, p) => {
        const net = payoutNet(p), fee = payoutFee(p);
        a.total += p.amount; a.fees += fee;
        if (['Pending Review', 'Processing', 'Ready'].includes(p.status)) a.pending += p.amount;
        if (['Completed', 'Credited', 'Paid'].includes(p.status)) a.completed += p.amount;
        if (p.status === 'Failed') a.failed++;
        if (p.category === 'User Bank Withdrawal') a.user += p.amount;
        if (p.category === 'Seller Cash-Out') a.seller += p.amount;
        if (p.category === 'Creator Cash-Out') a.creator += p.amount;
        if (p.category === 'Organizer Payout') a.organizer += p.amount;
        return a;
    }, { total: 0, fees: 0, pending: 0, completed: 0, failed: 0, user: 0, seller: 0, creator: 0, organizer: 0 });
});

// Accordion logic
const openGroups = ref<Record<string, boolean>>({});
const userRegion = (country: string) => {
    if (['United States', 'Canada'].includes(country)) return 'North America';
    // Simplified region logic for now
    if (['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Barbados', 'Guyana', 'Dominican Republic'].includes(country)) return 'Caribbean';
    if (['Brazil', 'Colombia'].includes(country)) return 'South America';
    return 'Other';
};

const groupedPayouts = computed(() => {
    const items = filteredPayoutRequests.value;
    const rm: Record<string, Record<string, PayoutRequest[]>> = {};
    items.forEach((it) => {
        const c = it.country || 'Other';
        const r = userRegion(c);
        if (!rm[r]) rm[r] = {};
        if (!rm[r][c]) rm[r][c] = [];
        rm[r][c].push(it);
    });
    return rm;
});

const toggleGroup = (key: string) => {
    openGroups.value[key] = !openGroups.value[key];
};

const isGroupOpen = (key: string, def = false) => {
    return openGroups.value[key] !== undefined ? openGroups.value[key] : def;
};

// Modal logic
const showModal = ref(false);
const selectedPayout = ref<PayoutRequest | null>(null);

const openPayoutModal = (id: number) => {
    selectedPayout.value = payoutRequests.value.find(p => p.id === id) || null;
    showModal.value = true;
};

const closePayoutModal = () => {
    showModal.value = false;
    selectedPayout.value = null;
};

// Chart logic
const updateCharts = () => {
    if (categoryCanvas.value) {
        charts.category?.destroy();
        const t = totals.value;
        charts.category = new Chart(categoryCanvas.value, {
            type: 'bar',
            data: {
                labels: ['User', 'Seller', 'Creator', 'Organizer'],
                datasets: [{
                    label: 'Payout Volume',
                    data: [t.user, t.seller, t.creator, t.organizer],
                    backgroundColor: ['#28A8FF', '#00C853', '#8B5CF6', '#F59E0B'],
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } } }
            }
        });
    }

    if (statusCanvas.value) {
        charts.status?.destroy();
        const t = totals.value;
        charts.status = new Chart(statusCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Completed', 'Failed'],
                datasets: [{
                    data: [t.pending, t.completed, t.failed * 100], // multiplying failed count for visual presence in mix
                    backgroundColor: ['#F59E0B', '#00C853', '#F43F5E'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } } }
            }
        });
    }
};

const handleFilterChange = (nextFilters: { region: string; country: string; period: string }) => {
    filters.value = nextFilters;
};

watch([filters, payoutSearch, statusFilter, categoryFilter], () => nextTick(updateCharts), { deep: true });

onMounted(() => {
    document.body.classList.add('new-admin-body');
    nextTick(updateCharts);
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
    charts.category?.destroy();
    charts.status?.destroy();
});
</script>

<template>
    <Head title="Payout Ops" />

    <div class="flex min-h-screen">
        <NewAppSidebar v-show="sidebarVisible" active-id="payoutCommand" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                title="Payout Ops"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="sidebarVisible = !sidebarVisible"
                @filter-change="handleFilterChange"
                @search="payoutSearch = $event"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2">
                        <p class="font-bold text-slate-300">Total Payout Requests</p>
                        <h3 class="text-5xl font-black">{{ fmt(totals.total) }}</h3>
                        <p class="font-bold text-lime-300">User, seller, creator, organizer payouts</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Pending Review</p>
                        <h3 class="text-4xl font-black text-amber-500">{{ fmt(totals.pending) }}</h3>
                        <p class="font-bold text-amber-500/60">Needs admin action</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Completed</p>
                        <h3 class="text-4xl font-black text-green-500">{{ fmt(totals.completed) }}</h3>
                        <p class="font-bold text-green-500/60">Paid / credited</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Failed</p>
                        <h3 class="text-4xl font-black text-rose-500">{{ num(totals.failed) }}</h3>
                        <p class="font-bold text-rose-500/60">Requires correction</p>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">User Withdrawals</p>
                        <h3 class="text-4xl font-black text-sky-500">{{ fmt(totals.user) }}</h3>
                        <p class="text-xs font-bold text-slate-400">Wallet to bank</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Seller Cash-Outs</p>
                        <h3 class="text-4xl font-black text-green-500">{{ fmt(totals.seller) }}</h3>
                        <p class="text-xs font-bold text-slate-400">Marketplace sellers</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Creator Cash-Outs</p>
                        <h3 class="text-4xl font-black text-purple-500">{{ fmt(totals.creator) }}</h3>
                        <p class="text-xs font-bold text-slate-400">Live creators</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Organizer Payouts</p>
                        <h3 class="text-4xl font-black text-amber-500">{{ fmt(totals.organizer) }}</h3>
                        <p class="text-xs font-bold text-slate-400">Event organizers</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Payout Fees</p>
                        <h3 class="text-4xl font-black text-sky-500">{{ fmt(totals.fees) }}</h3>
                        <p class="text-xs font-bold text-slate-400">Cash-out fees</p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl p-6 2xl:col-span-2">
                        <h3 class="mb-4 text-xl font-black">Payout Volume by Category</h3>
                        <div class="h-[250px]"><canvas ref="categoryCanvas"></canvas></div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Payout Status Mix</h3>
                        <div class="h-[250px]"><canvas ref="statusCanvas"></canvas></div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="card rounded-3xl p-6">
                    <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h3 class="text-xl font-black">Bank Withdrawal & Cash-Out Requests</h3>
                            <p class="text-slate-500">Manual transfer support for bank payouts and future Auto ACH processing.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <select v-model="statusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                                <option>All Statuses</option>
                                <option>Pending Review</option>
                                <option>Completed</option>
                                <option>Failed</option>
                                <option>Processing</option>
                            </select>
                            <select v-model="categoryFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                                <option>All Categories</option>
                                <option>User Bank Withdrawal</option>
                                <option>Seller Cash-Out</option>
                                <option>Creator Cash-Out</option>
                                <option>Organizer Payout</option>
                            </select>
                            <div class="relative w-full xl:w-80">
                                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="payoutSearch"
                                    class="w-full rounded-2xl border border-slate-200 py-2 pl-11 pr-4 font-bold"
                                    placeholder="Search name, email, bank..."
                                />
                            </div>
                        </div>
                    </div>

                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3 px-4">ID / Ref</th>
                                    <th>User / Party</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Bank</th>
                                    <th>Account</th>
                                    <th>Fee</th>
                                    <th>Payout</th>
                                    <th>Risk</th>
                                    <th>Status</th>
                                    <th>Requested</th>
                                    <th class="text-right px-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(countries, region) in groupedPayouts" :key="region">
                                    <!-- Region Row -->
                                    <tr class="cursor-pointer bg-slate-800 text-white" @click="toggleGroup('R:' + region)">
                                        <td colspan="12" class="py-2 px-4 font-black">
                                            <span class="flex items-center gap-2">
                                                <ChevronDown v-if="isGroupOpen('R:' + region, true)" class="h-4 w-4" />
                                                <ChevronRight v-else class="h-4 w-4" />
                                                🌎 {{ region }} <span class="opacity-60">({{ Object.values(countries).flat().length }})</span>
                                            </span>
                                        </td>
                                    </tr>

                                    <template v-if="isGroupOpen('R:' + region, true)">
                                        <template v-for="(items, country) in countries" :key="country">
                                            <!-- Country Row -->
                                            <tr class="cursor-pointer bg-slate-100" @click="toggleGroup('C:' + country)">
                                                <td colspan="12" class="py-2 px-8 font-black text-slate-700">
                                                    <span class="flex items-center gap-2">
                                                        <ChevronDown v-if="isGroupOpen('C:' + country)" class="h-4 w-4" />
                                                        <ChevronRight v-else class="h-4 w-4" />
                                                        {{ country }} <span class="opacity-50">({{ items.length }})</span>
                                                    </span>
                                                </td>
                                            </tr>

                                            <!-- Data Rows -->
                                            <tr v-for="p in items" v-show="isGroupOpen('C:' + country)" :key="p.id" class="border-t hover:bg-slate-50">
                                                <td class="py-4 px-4 font-black">
                                                    {{ p.id }}
                                                    <div class="text-[10px] text-slate-400 font-bold">{{ p.requestId }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-black">{{ p.name }}</div>
                                                    <div class="text-[10px] text-slate-400 font-bold">{{ p.email }}</div>
                                                </td>
                                                <td class="text-xs font-bold text-slate-600">{{ p.category }}</td>
                                                <td class="font-black text-slate-900">{{ fmt(p.amount) }}</td>
                                                <td class="text-xs font-bold text-slate-600">{{ p.bank }}</td>
                                                <td class="text-xs font-mono font-bold text-slate-500">{{ p.accountMasked }}</td>
                                                <td class="text-xs">
                                                    <div class="font-bold">{{ p.feePercent }}%</div>
                                                    <div class="text-slate-400">{{ fmt(payoutFee(p)) }}</div>
                                                </td>
                                                <td class="font-black text-emerald-600">{{ fmt(payoutNet(p)) }}</td>
                                                <td>
                                                    <span
                                                        class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase"
                                                        :class="p.risk === 'High' ? 'bg-rose-50 text-rose-600 border border-rose-100' : p.risk === 'Medium' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-green-50 text-green-600 border border-green-100'"
                                                    >{{ p.risk }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase"
                                                        :class="p.status === 'Completed' ? 'bg-green-50 text-green-700' : p.status === 'Failed' ? 'bg-rose-50 text-rose-700' : p.status === 'Processing' ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700'"
                                                    >{{ p.status }}</span>
                                                </td>
                                                <td class="text-[10px] font-bold text-slate-400 whitespace-nowrap">{{ p.date }}</td>
                                                <td class="py-4 px-4 text-right">
                                                    <button @click="openPayoutModal(p.id)" class="rounded-xl bg-slate-950 px-3 py-1.5 text-xs font-black text-white hover:bg-slate-800 transition">
                                                        Account
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                                <tr v-if="filteredPayoutRequests.length === 0">
                                    <td colspan="12" class="py-12 text-center text-slate-400 font-bold">No payout records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lists Section -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Payout Audit Trail</h3>
                        <div class="space-y-3">
                            <div v-for="(a, i) in payoutAudit" :key="i" class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex justify-between items-start">
                                    <b class="text-slate-900">{{ a.action }}</b>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">{{ a.date }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1 font-bold">{{ a.admin }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ a.detail }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Admin Workflow</h3>
                        <div class="space-y-3">
                            <div v-for="(step, i) in [
                                ['1. Request Submitted', 'User, seller, creator, or organizer requests payout.'],
                                ['2. Fee Calculated', 'System calculates cash-out fee and net payout.'],
                                ['3. Admin Reviews Account', 'Admin opens account details, risk, notes, and proof area.'],
                                ['4. Transfer Processed', 'Manual transfer today; ACH automation in future.'],
                                ['5. Mark Completed or Failed', 'Settlement, wallet ledger, and audit trail update.']
                            ]" :key="i" class="rounded-2xl bg-sky-50 border border-sky-100 p-4">
                                <b class="text-blue-900">{{ step[0] }}</b>
                                <p class="text-sm text-blue-700 mt-1 font-medium">{{ step[1] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Payout Detail Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-5 backdrop-blur-sm">
        <div class="w-full max-w-4xl overflow-hidden rounded-[40px] bg-white shadow-2xl animate-in zoom-in-95 duration-200">
            <div class="flex items-start justify-between border-b border-slate-100 p-8">
                <div>
                    <h3 class="text-3xl font-black text-slate-900">Bank Account Information</h3>
                    <div class="mt-1 flex items-center gap-2">
                        <p class="text-lg font-bold text-slate-500">{{ selectedPayout?.name }}</p>
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                        <p class="text-sm font-bold text-slate-400">{{ selectedPayout?.email }}</p>
                    </div>
                </div>
                <button @click="closePayoutModal" class="rounded-2xl bg-slate-100 p-3 text-slate-400 hover:bg-slate-200 hover:text-slate-900 transition">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto p-8 scrollbar">
                <div class="space-y-6">
                    <!-- ACH Notice -->
                    <div class="flex items-center justify-between gap-5 rounded-3xl border border-sky-200 bg-sky-50 p-6">
                        <div>
                            <h4 class="text-xl font-black text-blue-900">Auto ACH Ready</h4>
                            <p class="mt-1 text-sm font-medium text-blue-700/80">When direct bank connections are enabled, withdrawals tied to linked accounts can be processed automatically through ACH instead of manual wire handling.</p>
                        </div>
                        <span class="whitespace-nowrap rounded-2xl bg-white px-5 py-3 text-xs font-black text-slate-700 shadow-sm border border-sky-100">Manual Only</span>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div v-for="item in [
                            ['Bank Name', selectedPayout?.bank],
                            ['Account Number', selectedPayout?.account],
                            ['Fee Percent', selectedPayout?.feePercent + '%'],
                            ['Fee Amount', fmt(payoutFee(selectedPayout!))],
                            ['Payout Amount', fmt(payoutNet(selectedPayout!))],
                            ['Status', selectedPayout?.status]
                        ]" :key="item[0]" class="rounded-3xl border border-slate-100 bg-slate-50/50 p-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ item[0] }}</p>
                            <h4 class="mt-1 text-xl font-black text-slate-900">{{ item[1] }}</h4>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/50 p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Admin Notes / Proof Reference</p>
                        <p class="mt-2 text-lg font-bold text-slate-700">{{ selectedPayout?.notes || 'No notes provided.' }}</p>
                    </div>

                    <!-- Admin Rule -->
                    <div class="flex gap-4 rounded-3xl border border-amber-100 bg-amber-50 p-6">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">
                            <AlertCircle class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="font-black text-amber-900">Admin Rule</p>
                            <p class="mt-1 text-sm font-medium text-amber-800/80">Approving a payout requires confirmation. Failing a payout requires a written reason before the status can be changed.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 p-8 bg-slate-50/30">
                <button @click="closePayoutModal" class="rounded-2xl bg-slate-200 px-8 py-4 text-sm font-black text-slate-700 hover:bg-slate-300 transition">Close</button>
                <button class="flex items-center gap-2 rounded-2xl bg-rose-600 px-8 py-4 text-sm font-black text-white hover:bg-rose-700 transition">
                    <AlertCircle class="h-4 w-4" /> Mark Failed
                </button>
                <button class="flex items-center gap-2 rounded-2xl bg-green-600 px-8 py-4 text-sm font-black text-white hover:bg-green-700 transition shadow-lg shadow-green-100">
                    <CheckCircle class="h-4 w-4" /> Mark Completed
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.new-admin-body {
    background: radial-gradient(circle at top left, rgba(40, 168, 255, 0.12), transparent 30%),
                radial-gradient(circle at top right, rgba(217, 236, 16, 0.18), transparent 28%),
                linear-gradient(180deg, #f8fbff, #eef4fa);
}

.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.metric {
    position: relative;
    overflow: hidden;
}

.metric:after {
    content: "";
    position: absolute;
    right: -40px;
    top: -40px;
    width: 135px;
    height: 135px;
    border-radius: 999px;
    background: rgba(40, 168, 255, 0.09);
}

.metric.dark {
    background: linear-gradient(135deg, #07111f, #13233d);
    color: #fff;
}

.metric.dark:after {
    background: rgba(217, 236, 16, 0.14);
}

.scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}

.nav-active {
    background: linear-gradient(90deg, rgba(40, 168, 255, 0.15), rgba(217, 236, 16, 0.16));
    border-right: 4px solid #28A8FF;
}
</style>
