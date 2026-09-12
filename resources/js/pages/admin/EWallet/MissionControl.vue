<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps<{
    walletStats: any;
    walletCountryStats: (country: string) => any;
    getFilteredCountries: () => any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
    getScale: () => number;
    walletMovements: any[];
    filteredMovements: any[];
    walletFilter: any;
    walletUserDirectory: any[];
}>();

// Active Wallets 24H / Risk & Compliance are computed off the full, unfiltered
// movement log (matches the source dashboard, which doesn't scope these to region/country).
const activeWallets24h = computed(() => new Set(props.walletMovements.map((w) => w.user)).size);
const riskFlagCount = computed(() => props.walletMovements.filter((w) => w.status === 'Review' || w.status === 'Pending').length);

const activeWalletUsers = computed(() => props.getFilteredCountries().reduce((s, c) => s + (c.users || 0) * 0.64, 0) * props.getScale());

const treasuryGrid = computed(() => {
    const w = props.walletStats;
    return [
        { label: 'Total Wallet Balances', value: props.fmt(w.balances) },
        { label: 'Available Reserves', value: props.fmt(w.availableReserves) },
        { label: 'Reserve Surplus', value: props.fmt(w.availableReserves - w.balances) },
        { label: 'Required Reserve 110%', value: props.fmt(w.balances * 1.1) },
        { label: 'Movement Volume', value: props.fmt(w.movementVolume) },
        { label: 'Wallet Fees', value: props.fmt(w.fees) },
        { label: 'Net Inflow', value: props.fmt(w.loads + w.received - w.sent - w.cashouts - w.merchantPayments) },
        { label: 'Active Wallet Users', value: props.num(activeWalletUsers.value) },
    ];
});

const riskGrid = computed(() => {
    const m = props.filteredMovements;
    const largest = Math.max(...m.map((w) => w.amount * props.getScale()), 0);
    return [
        { label: 'Pending Cash Outs', value: String(m.filter((w) => w.status === 'Pending').length) },
        { label: 'Review Transactions', value: String(m.filter((w) => w.status === 'Review').length) },
        { label: 'Largest Movement', value: props.fmt(largest) },
        { label: 'Reserve Ratio', value: Math.round(props.walletStats.reserveRatio) + '%' },
        { label: 'FX / Cross-Border', value: String(m.filter((w) => w.type === 'FX Transfer' || w.channel === 'Cross-Border Transfer').length) },
        { label: 'Merchant Settlements', value: String(m.filter((w) => w.type === 'Merchant Settlement').length) },
    ];
});

// Profit Center reflects total platform-wide wallet fee income, independent of the
// region/country/type/status filters applied to the ledger below (matches source).
const profitCenter = computed(() => {
    const feeTotal = props.walletMovements.reduce((s, w) => s + w.fee, 0) * props.getScale();
    const linkup = feeTotal * 0.6;
    const bank = feeTotal * 0.4;
    return [
        { label: 'Total Wallet Fees', sub: 'All wallet channels', value: props.fmt(feeTotal) },
        { label: 'LinkUp Share 60%', sub: 'Platform income', value: props.fmt(linkup), color: 'text-emerald-600' },
        { label: 'Bank / Processor Share 40%', sub: 'Processing partner', value: props.fmt(bank), color: 'text-amber-600' },
        { label: 'Net Wallet Revenue', sub: 'Before operating cost', value: props.fmt(linkup), color: 'text-purple-600' },
        { label: 'Transaction Count', sub: 'Current filter', value: props.num(props.filteredMovements.length) },
    ];
});

const walletCountryChartRef = ref<HTMLCanvasElement | null>(null);
const walletMixChartRef = ref<HTMLCanvasElement | null>(null);
const charts = ref<any>({});

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const ctxCountry = walletCountryChartRef.value;
    if (ctxCountry) {
        charts.value.walletCountry = new Chart(ctxCountry, {
            type: 'bar',
            data: {
                labels: props.getFilteredCountries().map(c => c.country),
                datasets: [{
                    label: 'Fees',
                    data: props.getFilteredCountries().map(c => props.walletCountryStats(c.country).fees),
                    backgroundColor: '#28A8FF',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
    const ctxMix = walletMixChartRef.value;
    if (ctxMix) {
        const w = props.walletStats;
        charts.value.walletMix = new Chart(ctxMix, {
            type: 'doughnut',
            data: {
                labels: ['Loads', 'Transfers', 'Merchant Pay', 'Bills', 'Cash Outs'],
                datasets: [{
                    data: [w.loads, w.sent, w.merchantPayments, 10000 * props.getScale(), w.cashouts],
                    backgroundColor: ['#28A8FF', '#8B5CF6', '#00C853', '#F59E0B', '#F43F5E']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }
};

onMounted(renderCharts);
watch(() => [props.walletStats, props.getScale()], renderCharts, { deep: true });

const exportWalletCSV = () => {
    alert('Exporting Wallet CSV...');
};

// Geographic grouping for the country-wise accordions below (Transactions Ledger,
// User Directory) — mirrors the source dashboard's Region -> Country breakdown.
const REGION_MAP: Record<string, string> = {
    'United States': 'North America',
    Canada: 'North America',
    Bahamas: 'Caribbean',
    Jamaica: 'Caribbean',
    'Trinidad & Tobago': 'Caribbean',
    Barbados: 'Caribbean',
    'Dominican Republic': 'Caribbean',
    Haiti: 'Caribbean',
    Guyana: 'Latin America',
    Brazil: 'Latin America',
    Colombia: 'Latin America',
};
const userRegion = (country: string) => REGION_MAP[country] || 'Other';

interface CountryGroup<T> {
    country: string;
    items: T[];
}
interface RegionGroup<T> {
    region: string;
    totalCount: number;
    countries: CountryGroup<T>[];
}

function groupByCountry<T>(items: T[], countryFn: (item: T) => string): RegionGroup<T>[] {
    const byRegion: Record<string, Record<string, T[]>> = {};
    items.forEach((item) => {
        const country = countryFn(item) || 'Other';
        const region = userRegion(country);
        byRegion[region] = byRegion[region] || {};
        byRegion[region][country] = byRegion[region][country] || [];
        byRegion[region][country].push(item);
    });
    return Object.keys(byRegion)
        .sort()
        .map((region) => {
            const byCountry = byRegion[region];
            const countryList = Object.keys(byCountry)
                .sort((a, b) => byCountry[b].length - byCountry[a].length || a.localeCompare(b))
                .map((country) => ({ country, items: byCountry[country] }));
            return {
                region,
                totalCount: countryList.reduce((s, c) => s + c.items.length, 0),
                countries: countryList,
            };
        });
}

// Region/country open-state, namespaced so the ledger and directory accordions
// don't clobber each other; regions default open, countries default collapsed.
const groupOpen = ref<Record<string, boolean>>({});
const isRegionOpen = (ns: string, region: string) => {
    const key = `${ns}:R:${region}`;
    return groupOpen.value[key] !== undefined ? groupOpen.value[key] : true;
};
const isCountryOpen = (ns: string, country: string) => {
    const key = `${ns}:C:${country}`;
    return groupOpen.value[key] !== undefined ? groupOpen.value[key] : false;
};
const toggleRegion = (ns: string, region: string) => {
    const key = `${ns}:R:${region}`;
    groupOpen.value[key] = !isRegionOpen(ns, region);
};
const toggleCountry = (ns: string, country: string) => {
    const key = `${ns}:C:${country}`;
    groupOpen.value[key] = !isCountryOpen(ns, country);
};

const ledgerGroups = computed(() => groupByCountry(props.filteredMovements, (w) => w.country));
const directoryGroups = computed(() => groupByCountry(props.walletUserDirectory, (u) => u.country));

const userAvatar = (name: string, size = 26) => {
    const parts = (name || '?').trim().split(/\s+/);
    const ini = ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '');
    return `<div style="width:${size}px;height:${size}px;border-radius:9999px;background:linear-gradient(135deg,#6366f1,#a855f7);display:grid;place-items:center;color:#fff;font-weight:900;font-size:${size * 0.38}px;flex-shrink:0">${ini.toUpperCase()}</div>`;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">E-Wallet Mission Control</h3>
                <p class="text-slate-500">View LinkUp wallet balances, transactions, fees, risk flags, and settlement movement in one place.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full border border-green-200 bg-green-50 px-4 py-2 text-sm font-black text-green-700">● DB: Online</span>
                <span class="rounded-full border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-black text-sky-700">API Latency: 112ms</span>
                <button @click="exportWalletCSV" class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">
                    <Download class="h-4 w-4" /> Export Wallet CSV
                </button>
            </div>
        </div>

        <!-- Top KPIs -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card metric dark rounded-3xl p-5">
                <p class="font-bold text-slate-300">Total Revenue YTD</p>
                <h3 class="mt-2 text-4xl font-black">{{ fmt(walletStats.fees * 0.6) }}</h3>
                <p class="font-bold text-lime-300">Wallet fees + LinkUp events</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Platform Float</p>
                <h3 class="mt-2 text-4xl font-black">{{ fmt(walletStats.balances) }}</h3>
                <p class="font-bold text-slate-500">Held across custodial accounts</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Active Wallets 24H</p>
                <h3 class="mt-2 text-4xl font-black">{{ num(activeWallets24h) }}</h3>
                <p class="font-bold text-green-500">Unique wallets transacting</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Risk & Compliance</p>
                <h3 class="mt-2 text-4xl font-black">{{ riskFlagCount }} Flags</h3>
                <p class="font-bold text-amber-500">Review / pending items</p>
            </div>
        </div>

        <!-- Main Balances -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Total Wallet Balances</p>
                <h3 class="text-4xl font-black">{{ fmt(walletStats.balances) }}</h3>
                <p class="font-bold text-green-500">Customer stored value</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Movement Volume</p>
                <h3 class="text-4xl font-black">{{ fmt(walletStats.movementVolume) }}</h3>
                <p class="font-bold text-sky-500">All wallet movement</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Wallet Fees</p>
                <h3 class="text-4xl font-black">{{ fmt(walletStats.fees) }}</h3>
                <p class="font-bold text-purple-500">Fee income</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Bank / Processor Share</p>
                <h3 class="text-4xl font-black">{{ fmt(walletStats.fees * 0.4) }}</h3>
                <p class="font-bold text-amber-500">Processing partner split</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Reserve Ratio</p>
                <h3 class="text-4xl font-black">{{ Math.round(walletStats.reserveRatio) }}%</h3>
                <p class="font-bold text-rose-500">Funds coverage</p>
            </div>
        </div>

        <!-- Flow Summary -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Wallet Loads</p><h3 class="text-4xl font-black">{{ fmt(walletStats.loads) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Money Sent</p><h3 class="text-4xl font-black">{{ fmt(walletStats.sent) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Money Received</p><h3 class="text-4xl font-black">{{ fmt(walletStats.received) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Cash Outs</p><h3 class="text-4xl font-black">{{ fmt(walletStats.cashouts) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Merchant Payments</p><h3 class="text-4xl font-black">{{ fmt(walletStats.merchantPayments) }}</h3></div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <div class="card rounded-3xl p-6 2xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-black">Revenue Trend</h3>
                        <p class="text-slate-500">Wallet fees, transfers, cash-outs, merchant pay, refunds, and event wallet transactions.</p>
                    </div>
                    <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-black text-white">30d</span>
                </div>
                <div class="h-64">
                    <canvas ref="walletCountryChartRef"></canvas>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-1 text-xl font-black">TX Load by Type</h3>
                <p class="mb-4 text-slate-500">Movement mix by wallet transaction category.</p>
                <div class="h-64">
                    <canvas ref="walletMixChartRef"></canvas>
                </div>
            </div>
        </div>

        <!-- Treasury & Risk -->
        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Wallet Treasury Report</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div v-for="row in treasuryGrid" :key="row.label" class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-bold text-slate-500">{{ row.label }}</p>
                        <h3 class="text-2xl font-black">{{ row.value }}</h3>
                    </div>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Risk & Reconciliation</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div v-for="row in riskGrid" :key="row.label" class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="font-bold text-slate-600">{{ row.label }}</p>
                        <h3 class="text-2xl font-black">{{ row.value }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Ledger -->
        <div class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-xl font-black">Wallet Transactions</h3>
                    <p class="text-slate-500">Complete LinkUp wallet transaction ledger across countries, channels, users, and statuses.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select v-model="walletFilter.type" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                        <option>All Types</option>
                        <option>Wallet Load</option>
                        <option>Send Money</option>
                        <option>Receive Money</option>
                        <option>Merchant Pay</option>
                        <option>Cash Out</option>
                    </select>
                    <select v-model="walletFilter.status" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                        <option>All Statuses</option>
                        <option>Completed</option>
                        <option>Pending</option>
                        <option>Review</option>
                    </select>
                    <input v-model="walletFilter.search" class="w-full rounded-2xl border border-slate-200 px-4 py-2 lg:w-96" placeholder="Search transaction, user, country, channel..." />
                </div>
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Transaction</th>
                            <th>Date</th>
                            <th>Country</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Direction</th>
                            <th>Channel</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Balance After</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="!ledgerGroups.length">
                            <tr><td colspan="12" class="py-6 text-center text-slate-400 font-bold">No records.</td></tr>
                        </template>
                        <template v-for="grp in ledgerGroups" :key="'ledger-region-' + grp.region">
                            <tr>
                                <td
                                    colspan="12"
                                    @click="toggleRegion('ledger', grp.region)"
                                    class="cursor-pointer bg-gradient-to-r from-slate-800 to-slate-600 px-3 py-2 font-black text-white"
                                >
                                    {{ isRegionOpen('ledger', grp.region) ? '▾' : '▸' }} 🌎 {{ grp.region }}
                                    <span class="opacity-60">({{ grp.totalCount }})</span>
                                </td>
                            </tr>
                            <template v-if="isRegionOpen('ledger', grp.region)">
                                <template v-for="cg in grp.countries" :key="'ledger-country-' + cg.country">
                                    <tr>
                                        <td
                                            colspan="12"
                                            @click="toggleCountry('ledger', cg.country)"
                                            class="cursor-pointer bg-slate-100 px-6 py-2 font-black text-slate-700"
                                        >
                                            {{ isCountryOpen('ledger', cg.country) ? '▾' : '▸' }} {{ cg.country }}
                                            <span class="opacity-50">({{ cg.items.length }})</span>
                                        </td>
                                    </tr>
                                    <template v-if="isCountryOpen('ledger', cg.country)">
                                        <tr v-for="w in cg.items" :key="w.id" class="border-t hover:bg-slate-50">
                                            <td class="py-3 font-black text-purple-600">{{ w.id }}</td>
                                            <td class="text-sm">{{ w.date }}</td>
                                            <td class="text-sm font-bold text-slate-700">{{ w.country }}</td>
                                            <td class="font-bold text-slate-900">{{ w.user }}</td>
                                            <td class="text-sm">{{ w.type }}</td>
                                            <td>
                                                <span :class="w.direction === 'In' ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-700'" class="rounded-full px-3 py-1 text-xs font-black">
                                                    {{ w.direction }}
                                                </span>
                                            </td>
                                            <td class="text-sm text-slate-500">{{ w.channel }}</td>
                                            <td class="font-black">{{ fmt(w.amount * getScale()) }}</td>
                                            <td class="font-bold text-sky-600">{{ fmt(w.fee * getScale()) }}</td>
                                            <td class="text-sm font-bold text-slate-600">{{ fmt(w.balanceAfter * getScale()) }}</td>
                                            <td>
                                                <span :class="{
                                                    'bg-green-50 text-green-700': w.status === 'Completed',
                                                    'bg-amber-50 text-amber-700': w.status === 'Pending',
                                                    'bg-rose-50 text-rose-700': w.status === 'Review' || w.status === 'Failed'
                                                }" class="rounded-full px-3 py-1 text-xs font-black">
                                                    {{ w.status }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="rounded-xl bg-purple-600 px-3 py-1 text-xs font-black text-white">View</button>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Directory & Profit Center -->
        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Wallet User Directory</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs font-black text-slate-500 uppercase">
                            <tr>
                                <th class="py-3">User</th>
                                <th>Country</th>
                                <th>Transactions</th>
                                <th>Balance</th>
                                <th>Last Activity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="!directoryGroups.length">
                                <tr><td colspan="6" class="py-6 text-center text-slate-400 font-bold">No records.</td></tr>
                            </template>
                            <template v-for="grp in directoryGroups" :key="'dir-region-' + grp.region">
                                <tr>
                                    <td
                                        colspan="6"
                                        @click="toggleRegion('directory', grp.region)"
                                        class="cursor-pointer bg-gradient-to-r from-slate-800 to-slate-600 px-3 py-2 font-black text-white"
                                    >
                                        {{ isRegionOpen('directory', grp.region) ? '▾' : '▸' }} 🌎 {{ grp.region }}
                                        <span class="opacity-60">({{ grp.totalCount }})</span>
                                    </td>
                                </tr>
                                <template v-if="isRegionOpen('directory', grp.region)">
                                    <template v-for="cg in grp.countries" :key="'dir-country-' + cg.country">
                                        <tr>
                                            <td
                                                colspan="6"
                                                @click="toggleCountry('directory', cg.country)"
                                                class="cursor-pointer bg-slate-100 px-6 py-2 font-black text-slate-700"
                                            >
                                                {{ isCountryOpen('directory', cg.country) ? '▾' : '▸' }} {{ cg.country }}
                                                <span class="opacity-50">({{ cg.items.length }})</span>
                                            </td>
                                        </tr>
                                        <template v-if="isCountryOpen('directory', cg.country)">
                                            <tr v-for="u in cg.items" :key="u.user" class="border-t">
                                                <td class="py-3 font-black">
                                                    <div class="flex items-center gap-2">
                                                        <div v-html="userAvatar(u.user)"></div>
                                                        <span>{{ u.user }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-sm">{{ u.country }}</td>
                                                <td class="text-sm">{{ u.tx }}</td>
                                                <td class="font-black">{{ fmt(u.balance * getScale()) }}</td>
                                                <td class="text-sm text-slate-500">{{ u.last }}</td>
                                                <td>
                                                    <span :class="u.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'" class="rounded-full px-3 py-1 text-xs font-black">
                                                        {{ u.status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Profit Center</h3>
                <div class="space-y-3">
                    <div
                        v-for="row in profitCenter"
                        :key="row.label"
                        class="flex justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4"
                    >
                        <div>
                            <p class="font-black">{{ row.label }}</p>
                            <p class="text-xs text-slate-500">{{ row.sub }}</p>
                        </div>
                        <h3 class="text-xl font-black" :class="row.color">{{ row.value }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Table -->
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Total Wallets by Country</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Country</th>
                            <th>Total Wallet Balance</th>
                            <th>Wallet Users</th>
                            <th>Wallet Loads</th>
                            <th>Sent</th>
                            <th>Received</th>
                            <th>Cash Outs</th>
                            <th>Merchant Pay</th>
                            <th>Fees</th>
                            <th>Reserve Required</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in getFilteredCountries()" :key="c.country" class="border-t">
                            <td class="py-3 font-black">{{ c.country }}</td>
                            <td class="font-bold">{{ fmt(walletCountryStats(c.country).totalBalance) }}</td>
                            <td>{{ num(walletCountryStats(c.country).walletUsers) }}</td>
                            <td class="text-sm">{{ fmt(walletCountryStats(c.country).loads) }}</td>
                            <td class="text-sm">{{ fmt(walletCountryStats(c.country).sent) }}</td>
                            <td class="text-sm">{{ fmt(walletCountryStats(c.country).received) }}</td>
                            <td class="text-sm">{{ fmt(walletCountryStats(c.country).cashouts) }}</td>
                            <td class="text-sm">{{ fmt(walletCountryStats(c.country).merchantPay) }}</td>
                            <td class="font-bold text-sky-600">{{ fmt(walletCountryStats(c.country).fees) }}</td>
                            <td class="font-bold text-purple-600">{{ fmt(walletCountryStats(c.country).reserveRequired) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
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
</style>
