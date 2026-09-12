<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import {
    SlidersHorizontal,
    Tag,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

type PricingRule = {
    module: string;
    country: string;
    rule: string;
    linkup: string;
    bank: string;
    fx: string;
};

type FXRate = {
    pair: string;
    rate: number;
    spread: string;
    exposure: number;
};

const props = defineProps<{
    initialUnits?: Unit[];
    initialCountries?: Country[];
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
];

const fallbackCountries: Country[] = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000 },
    { country: 'Trinidad & Tobago', region: 'Regional', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000 },
    { country: 'Barbados', region: 'Regional', users: 70000, merchants: 380, organizers: 95, tickets: 330000, subscriptions: 42000, marketplace: 190000, eats: 260000, merchantPay: 720000, wallet: 460000, live: 85000, ads: 21000 },
    { country: 'Guyana', region: 'Regional', users: 130000, merchants: 620, organizers: 140, tickets: 420000, subscriptions: 56000, marketplace: 270000, eats: 360000, merchantPay: 980000, wallet: 600000, live: 120000, ads: 28000, wellness: 100000, cookouts: 82000, linkup360: 31000 },
    { country: 'Dominican Republic', region: 'Regional', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000, wellness: 130000, cookouts: 99000, linkup360: 41000 },
    { country: 'United States', region: 'International', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000, wellness: 320000, cookouts: 180000, linkup360: 120000 },
    { country: 'Canada', region: 'International', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000, wellness: 210000, cookouts: 120000, linkup360: 85000 },
    { country: 'Brazil', region: 'International', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000, wellness: 260000, cookouts: 140000, linkup360: 97000 },
    { country: 'Colombia', region: 'International', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000, wellness: 180000, cookouts: 95000, linkup360: 70000 },
];

const pricingRules: PricingRule[] = [
    { module: 'Domestic Remittance', country: 'All', rule: '1.0% fee, min $0.50, max $5', linkup: '1.00%', bank: '0.25%', fx: '0%' },
    { module: 'International Remittance', country: 'All', rule: '2.5% LinkUp + 1% bank + 1% FX', linkup: '2.50%', bank: '1.00%', fx: '1.00%' },
    { module: 'Events', country: 'Bahamas', rule: '6.5% ticket fee + add-on fees', linkup: '6.50%', bank: '2.50%', fx: '0%' },
    { module: 'Merchant Pay', country: 'All', rule: '2.0% LinkUp + 3.0% processing', linkup: '2.00%', bank: '3.00%', fx: '0%' },
    { module: 'LinkUp Live', country: 'All', rule: '50/50 creator split', linkup: '50.00%', bank: '0%', fx: '0%' },
    { module: 'Coins', country: 'All', rule: '50/50 coin split', linkup: '50.00%', bank: '0%', fx: '0%' }
];

const fxRates: FXRate[] = [
    { pair: 'USD/BSD', rate: 1.00, spread: '0.25%', exposure: 1250000 },
    { pair: 'USD/JMD', rate: 155.20, spread: '1.00%', exposure: 850000 },
    { pair: 'USD/TTD', rate: 6.78, spread: '1.00%', exposure: 420000 },
    { pair: 'CAD/USD', rate: 0.73, spread: '0.85%', exposure: 610000 },
    { pair: 'USD/COP', rate: 3925.00, spread: '1.25%', exposure: 390000 }
];

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

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
    <Head title="Pricing" />

    <div class="flex min-h-screen">
        <NewAppSidebar v-show="sidebarVisible" active-id="pricingCommand" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                title="Pricing"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="sidebarVisible = !sidebarVisible"
                @filter-change="handleFilterChange"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <!-- Pricing Engine -->
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-4">Settings / Pricing Engine</h3>
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left">
                                <thead class="text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="py-3 px-2">Module</th>
                                        <th>Country</th>
                                        <th>Rule</th>
                                        <th>LinkUp</th>
                                        <th>Bank</th>
                                        <th>FX</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in pricingRules" :key="p.module" class="border-t hover:bg-slate-50 transition">
                                        <td class="py-4 px-2 font-black">{{ p.module }}</td>
                                        <td class="text-sm font-bold text-slate-600">{{ p.country }}</td>
                                        <td class="text-sm text-slate-500">{{ p.rule }}</td>
                                        <td class="text-sky-600 font-black">{{ p.linkup }}</td>
                                        <td class="text-amber-600 font-black">{{ p.bank }}</td>
                                        <td class="text-green-600 font-black">{{ p.fx }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Currency & FX -->
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-4">Currency & FX Management</h3>
                        <div class="space-y-3">
                            <div v-for="f in fxRates" :key="f.pair" class="rounded-2xl bg-slate-50 p-4 flex justify-between items-center border border-slate-100 hover:bg-white hover:shadow-md transition">
                                <div>
                                    <b class="text-slate-900">{{ f.pair }}</b>
                                    <p class="text-sm text-slate-600 font-medium">Rate: {{ f.rate.toFixed(2) }} • Spread: {{ f.spread }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-slate-900 block">{{ fmt(f.exposure) }}</span>
                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Exposure</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Standalone Message placeholder logic from HTML -->
                <div class="card rounded-3xl p-10 text-center bg-slate-950 text-white relative overflow-hidden">
                    <!-- Decorative background element -->
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
                    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <div class="mx-auto h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-md grid place-items-center mb-6">
                            <Tag class="w-8 h-8 text-white" />
                        </div>
                        <h3 class="text-3xl font-black mb-4 tracking-tight">Pricing Dashboard Active</h3>
                        <p class="text-slate-400 mt-2 max-w-xl mx-auto font-medium">
                            Manage global fees, commission splits, and multi-currency spreads from a single command center.
                            Live exchange rates are processed through the Scotiabank connection.
                        </p>
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
