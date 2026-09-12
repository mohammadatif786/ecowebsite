<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import {
    HeartHandshake,
    BadgeCheck,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

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

type Allocation = {
    name: string;
    pct: number;
};

const props = defineProps<{
    initialUnits?: Unit[];
    initialCountries?: Country[];
}>();

const SCOTIA_SHARE = 0.4;
const FOUND_COLORS = ['#3b82f6', '#ef4444', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#14b8a6', '#a855f7', '#84cc16', '#0ea5e9'];

const fallbackUnits: Unit[] = [
    { key: 'tickets', name: 'Ticket Sales', platformRate: 0.065, bankRate: 0, costRate: 0.01 },
    { key: 'subscriptions', name: 'Subscriptions', platformRate: 1, bankRate: 0, costRate: 0.04 },
    { key: 'marketplace', name: 'Marketplace', platformRate: 0.05, bankRate: 0, costRate: 0.01 },
    { key: 'eats', name: 'LinkUp Eats', platformRate: 0.075, bankRate: 0, costRate: 0.025 },
    { key: 'merchantPay', name: 'Merchant Pay', platformRate: 0.02, bankRate: 0, costRate: 0.007 },
    { key: 'wallet', name: 'Wallet & Money Movement', platformRate: 0.025, bankRate: 0.0175, costRate: 0.008 },
    { key: 'live', name: 'LinkUp Live', platformRate: 0.5, bankRate: 0, costRate: 0.08 },
    { key: 'ads', name: 'Advertising Revenue', platformRate: 1, bankRate: 0, costRate: 0.12 },
];

const fallbackCountries: Country[] = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000 },
    { country: 'Trinidad & Tobago', region: 'Regional', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000 },
];

// Foundation State
const savedConfig = JSON.parse(localStorage.getItem('linkupFoundation') || 'null');
const foundationCfg = ref({
    rate: savedConfig?.rate ?? 1.0,
    allocations: savedConfig?.allocations ?? [
        { name: 'Education & Youth Tech', pct: 40 },
        { name: 'Disaster Relief', pct: 25 },
        { name: 'Small Business Grants', pct: 20 },
        { name: 'Community Health', pct: 15 }
    ]
});

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

const countries = computed(() => (props.initialCountries?.length ? props.initialCountries : fallbackCountries));
const units = computed(() => (props.initialUnits?.length ? props.initialUnits : fallbackUnits));

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n || 0);

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
    users: new Intl.NumberFormat('en-US').format(Math.round(platformTotals.value.users)),
    merchants: new Intl.NumberFormat('en-US').format(platformTotals.value.merchants),
    organizers: new Intl.NumberFormat('en-US').format(platformTotals.value.organizers),
    countries: new Intl.NumberFormat('en-US').format(filteredCountries.value.length),
}));

// Foundation Calculations
const periodEarnings = computed(() => platformTotals.value.platform + platformTotals.value.bank * (1 - SCOTIA_SHARE));
const annualGiving = computed(() => (periodEarnings.value / scale.value) * 12 * (foundationCfg.value.rate / 100));
const ytdBalance = computed(() => {
    const now = new Date();
    const monthsYtd = now.getMonth() + 1;
    return (periodEarnings.value / scale.value) * (foundationCfg.value.rate / 100) * monthsYtd;
});

const taxSavings = computed(() => annualGiving.value * 0.265);
const totalAllocationPct = computed(() => foundationCfg.value.allocations.reduce((a, x) => a + (Number(x.pct) || 0), 0));

const ledgerRows = computed(() => {
    const mn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const now = new Date();
    const monthsYtd = now.getMonth() + 1;
    const monthly = periodEarnings.value / scale.value;
    const rows = [];
    for (let i = Math.min(5, monthsYtd - 1); i >= 0; i--) {
        const mi = now.getMonth() - i;
        const earn = monthly * (0.9 + ((mi % 3) * 0.06));
        rows.push({
            m: mn[(mi + 12) % 12],
            earn: earn,
            c: earn * (foundationCfg.value.rate / 100)
        });
    }
    return rows;
});

const saveFoundation = () => {
    localStorage.setItem('linkupFoundation', JSON.stringify(foundationCfg.value));
};

const addAllocation = () => {
    foundationCfg.value.allocations.push({ name: '', pct: 0 });
};

const delAllocation = (index: number) => {
    foundationCfg.value.allocations.splice(index, 1);
    saveFoundation();
};

const handleFilterChange = (nextFilters: { region: string; country: string; period: string }) => {
    filters.value = nextFilters;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="LinkUp Foundation" />

    <div class="flex min-h-screen">
        <NewAppSidebar v-show="sidebarVisible" active-id="foundationCommand" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                title="LinkUp Foundation"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="sidebarVisible = !sidebarVisible"
                @filter-change="handleFilterChange"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-rose-600 flex items-center gap-2">
                            <HeartHandshake class="w-8 h-8" /> LinkUp Foundation
                        </h3>
                        <p class="text-slate-500 max-w-3xl">A set % of LinkUp's total earnings is automatically set aside to give back across the Caribbean & Latin America. Adjust the rate anytime.</p>
                    </div>
                    <div class="flex items-end gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider ml-1">Contribution Rate (%)</label>
                            <input v-model.number="foundationCfg.rate" type="number" step="0.1" min="0" class="w-32 rounded-2xl border border-slate-200 px-4 py-3 text-lg font-black focus:ring-4 focus:ring-rose-50 outline-none transition">
                        </div>
                        <button @click="saveFoundation" class="rounded-2xl bg-rose-600 text-white px-6 py-3.5 font-black hover:bg-rose-700 transition shadow-lg shadow-rose-100">Save</button>
                    </div>
                </div>

                <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-5 text-sm font-bold text-emerald-800 flex items-start gap-3">
                    <BadgeCheck class="w-6 h-6 shrink-0 mt-0.5" />
                    <span>
                        Tax-deductible: giving {{ fmt(annualGiving) }}/yr is a charitable write-off, lowering LinkUp's taxable income and saving ~{{ fmt(taxSavings) }}/yr in corporate tax (Federal 21% + Florida 5.5%, capped at 10% of profit). See LinkUp Corporate Tax.
                    </span>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card metric dark rounded-3xl p-6">
                        <p class="text-slate-300 text-sm font-bold uppercase tracking-wider">LinkUp Earnings (period)</p>
                        <h3 class="text-3xl font-black mt-2">{{ fmt(periodEarnings) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <p class="text-slate-500 text-sm font-bold uppercase tracking-wider">Foundation Contribution</p>
                        <h3 class="text-3xl font-black text-rose-600 mt-2">{{ fmt(periodEarnings * (foundationCfg.rate / 100)) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <p class="text-slate-500 text-sm font-bold uppercase tracking-wider">Annualized Giving</p>
                        <h3 class="text-3xl font-black mt-2 text-slate-900">{{ fmt(annualGiving) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <p class="text-slate-500 text-sm font-bold uppercase tracking-wider">Fund Balance (YTD)</p>
                        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ fmt(ytdBalance) }}</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Ledger -->
                    <div class="card rounded-3xl p-6">
                        <h4 class="text-xl font-black mb-4">Contribution Ledger</h4>
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left">
                                <thead class="text-xs uppercase text-slate-400 font-black tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="py-3 px-2">Month</th>
                                        <th>LinkUp Earnings</th>
                                        <th>Rate</th>
                                        <th class="text-right px-2">Contributed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in ledgerRows" :key="row.m" class="border-t hover:bg-slate-50 transition">
                                        <td class="py-4 px-2 font-bold">{{ row.m }}</td>
                                        <td class="font-medium text-slate-600">{{ fmt(row.earn) }}</td>
                                        <td class="text-slate-500 font-bold">{{ foundationCfg.rate.toFixed(1) }}%</td>
                                        <td class="text-right px-2 font-black text-rose-600">{{ fmt(row.c) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Fund Allocation -->
                    <div class="card rounded-3xl p-6">
                        <h4 class="text-xl font-black mb-1">Fund Allocation</h4>
                        <p class="text-xs text-slate-400 mb-6 font-bold uppercase tracking-wider">How the foundation deploys its balance. Adjust focus areas anytime.</p>

                        <div class="space-y-4">
                            <div v-for="(alloc, i) in foundationCfg.allocations" :key="i" class="rounded-2xl bg-slate-50 p-4 border border-slate-100 group transition hover:bg-white hover:shadow-md">
                                <div class="flex items-center gap-3">
                                    <span class="h-3 w-3 rounded-full shrink-0" :style="{ background: FOUND_COLORS[i % FOUND_COLORS.length] }"></span>
                                    <input v-model="alloc.name" @change="saveFoundation" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 font-bold text-sm outline-none focus:ring-4 focus:ring-rose-50 transition" placeholder="Cause / recipient">
                                    <div class="flex items-center gap-2">
                                        <input v-model.number="alloc.pct" @change="saveFoundation" type="number" step="1" class="w-20 rounded-xl border border-slate-200 px-3 py-2 font-black text-right text-sm outline-none focus:ring-4 focus:ring-rose-50 transition">
                                        <span class="text-slate-400 font-black text-xs">%</span>
                                    </div>
                                    <span class="w-28 text-right font-black text-rose-600 text-sm">{{ fmt(ytdBalance * (alloc.pct / 100)) }}</span>
                                    <button @click="delAllocation(i)" class="text-slate-300 hover:text-rose-600 transition p-1">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                                <div class="mt-3 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full transition-all duration-500" :style="{ width: Math.min(100, alloc.pct) + '%', background: FOUND_COLORS[i % FOUND_COLORS.length] }"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <button @click="addAllocation" class="rounded-2xl border border-slate-200 px-5 py-2.5 font-black text-sm flex items-center gap-2 hover:bg-slate-50 transition">
                                    <Plus class="w-4 h-4" /> Add Allocation
                                </button>
                                <div class="text-sm font-black flex items-center gap-2" :class="Math.round(totalAllocationPct) === 100 ? 'text-emerald-600' : 'text-amber-600'">
                                    Total: {{ totalAllocationPct.toFixed(0) }}%
                                    <span v-if="Math.round(totalAllocationPct) === 100">✓</span>
                                    <span v-else class="text-[10px] uppercase font-bold">(Should equal 100%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
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
