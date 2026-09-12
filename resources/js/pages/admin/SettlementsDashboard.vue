<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import '@/../../resources/css/new_admin.css';

type Unit = { key: string; name: string; platformRate: number; bankRate: number; costRate: number };
type Country = { country: string; region: string; users: number; merchants: number; organizers: number; [key: string]: string | number };
type Settlement = {
    id: string;
    date: string;
    party: string;
    type: string;
    country: string;
    gross: number;
    fees: number;
    tax: number;
    payout: number;
    destination: string;
    status: string;
    source: string;
};

const props = defineProps<{
    initialUnits?: Unit[];
    initialCountries?: Country[];
}>();

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
    { country: 'Dominican Republic', region: 'Regional', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000 },
    { country: 'United States', region: 'International', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000 },
    { country: 'Canada', region: 'International', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000 },
    { country: 'Brazil', region: 'International', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000 },
    { country: 'Colombia', region: 'International', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000 },
];

const settlements: Settlement[] = [
    { id: 'SET-9001', date: '2026-06-01', party: 'Nassau Nights Ltd.', type: 'Event Organizer', country: 'Bahamas', gross: 210000, fees: 13650, tax: 15750, payout: 180600, destination: 'External Bank', status: 'Ready', source: 'Events' },
    { id: 'SET-9002', date: '2026-06-01', party: 'Island Queen Live', type: 'Live Creator', country: 'Bahamas', gross: 42000, fees: 21000, tax: 0, payout: 21000, destination: 'LinkUp Wallet', status: 'Credited', source: 'LinkUp Live' },
    { id: 'SET-9003', date: '2026-06-01', party: 'SuperValue Nassau', type: 'Merchant', country: 'Bahamas', gross: 420000, fees: 12600, tax: 0, payout: 407400, destination: 'External Bank', status: 'Paid', source: 'Merchant Pay' },
    { id: 'SET-9004', date: '2026-06-01', party: 'Kingston Social', type: 'Event Organizer', country: 'Jamaica', gross: 310000, fees: 20150, tax: 23250, payout: 266600, destination: 'LinkUp Wallet', status: 'Credited', source: 'Events' },
    { id: 'SET-9005', date: '2026-06-01', party: 'Scotiabank Processing', type: 'Bank Partner', country: 'Regional', gross: 980000, fees: 29400, tax: 0, payout: 29400, destination: 'External Bank', status: 'Paid', source: 'Bank Fees' },
    { id: 'SET-9006', date: '2026-06-01', party: 'Caribbean Beauty Store', type: 'Marketplace Seller', country: 'United States', gross: 16800, fees: 1680, tax: 0, payout: 15120, destination: 'LinkUp Wallet', status: 'Credited', source: 'Marketplace' },
    { id: 'SET-9007', date: '2026-06-01', party: 'Bajan Elite Events', type: 'Event Organizer', country: 'Barbados', gross: 120000, fees: 7800, tax: 9000, payout: 103200, destination: 'External Bank', status: 'Pending', source: 'Events' },
    { id: 'SET-9008', date: '2026-06-01', party: 'Kingston Star', type: 'Live Creator', country: 'Jamaica', gross: 88000, fees: 44000, tax: 0, payout: 44000, destination: 'LinkUp Wallet', status: 'Credited', source: 'LinkUp Live' },
    { id: 'SET-9009', date: '2026-06-01', party: 'Island Grill Nassau', type: 'Restaurant', country: 'Bahamas', gross: 8200, fees: 615, tax: 0, payout: 7585, destination: 'LinkUp Wallet', status: 'Credited', source: 'LinkUp Eats' },
    { id: 'SET-9010', date: '2026-06-01', party: 'Toronto LinkUp Live', type: 'Live Creator Cash-Out', country: 'Canada', gross: 61000, fees: 30500, tax: 0, payout: 22000, destination: 'External Bank', status: 'Processing', source: 'Wallet Cash-Out' },
];

const rules = [
    ['Live Creator Earnings', 'Creator share is first credited to LinkUp Wallet. External cash-out is a second transaction.'],
    ['Marketplace Sellers', 'Seller net proceeds are credited to wallet after platform fee, refunds, and holds.'],
    ['Event Organizers', 'Organizer payouts can settle to wallet or external bank depending on approval and risk.'],
    ['Cash-Outs', 'When wallet funds leave LinkUp, the transaction becomes an external cash-out settlement.'],
];

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const search = ref('');
const partyCanvas = ref<HTMLCanvasElement | null>(null);
const destinationCanvas = ref<HTMLCanvasElement | null>(null);
const charts: { party?: Chart; destination?: Chart } = {};

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

const countryFin = (country: Country) =>
    units.value.reduce(
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

const platformTotals = computed(() =>
    filteredCountries.value.reduce(
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
    ),
);

const ribbonMetrics = computed(() => ({
    gtv: fmt(platformTotals.value.gross),
    linkupRev: fmt(platformTotals.value.platform),
    procPool: fmt(platformTotals.value.bank),
    netProfit: fmt(platformTotals.value.platform - platformTotals.value.cost + platformTotals.value.bank * 0.6),
    users: num(platformTotals.value.users),
    merchants: num(platformTotals.value.merchants),
    organizers: num(platformTotals.value.organizers),
    countries: num(filteredCountries.value.length),
}));

const countryRegion = (name: string) => countries.value.find((country) => country.country === name)?.region;

const filteredSettlements = computed(() => {
    const query = search.value.toLowerCase().trim();
    return settlements.filter((settlement) => {
        const region = countryRegion(settlement.country);
        const regionOk = filters.value.region === 'All' || settlement.country === 'Regional' || region === filters.value.region;
        const countryOk = filters.value.country === 'All Countries' || settlement.country === filters.value.country;
        const searchOk =
            !query ||
            [settlement.id, settlement.date, settlement.party, settlement.type, settlement.source, settlement.country, settlement.destination, settlement.status]
                .join(' ')
                .toLowerCase()
                .includes(query);

        return regionOk && countryOk && searchOk;
    });
});

const settlementTotals = computed(() =>
    filteredSettlements.value.reduce(
        (total, settlement) => {
            const multiplier = scale.value;
            const gross = settlement.gross * multiplier;
            const fees = settlement.fees * multiplier;
            const tax = settlement.tax * multiplier;
            const payout = settlement.payout * multiplier;

            total.volume += gross;
            total.fees += fees;
            total.tax += tax;
            total.payout += payout;
            if (settlement.destination === 'LinkUp Wallet') total.wallet += payout;
            else total.external += payout;
            if (['Pending', 'Processing', 'Ready'].includes(settlement.status)) total.pending += payout;
            if (settlement.type.includes('Creator')) total.creators += payout;
            if (settlement.type.includes('Marketplace')) total.sellers += payout;
            if (settlement.type.includes('Event Organizer')) total.organizers += payout;
            if (['Merchant', 'Restaurant'].includes(settlement.type)) total.merchants += payout;
            return total;
        },
        { volume: 0, fees: 0, tax: 0, payout: 0, wallet: 0, external: 0, pending: 0, creators: 0, sellers: 0, organizers: 0, merchants: 0 },
    ),
);

const groupedByType = computed(() => {
    const map = new Map<string, number>();
    filteredSettlements.value.forEach((settlement) => {
        const amount = settlement.payout * scale.value;
        map.set(settlement.type, (map.get(settlement.type) || 0) + amount);
    });
    return Array.from(map.entries()).sort((a, b) => b[1] - a[1]);
});

const destinationSplit = computed(() => [
    ['LinkUp Wallet', settlementTotals.value.wallet],
    ['External Bank', settlementTotals.value.external],
]);

const taxRows = computed(() => [
    ['Total Tax/VAT Collected', settlementTotals.value.tax, 'money'],
    ['Event VAT', filteredSettlements.value.filter((row) => row.type === 'Event Organizer').reduce((sum, row) => sum + row.tax * scale.value, 0), 'money'],
    ['Restaurant / Eats Tax', filteredSettlements.value.filter((row) => row.type === 'Restaurant').reduce((sum, row) => sum + row.tax * scale.value, 0), 'money'],
    ['Government Reporting Queue', 3, 'num'],
]);

const scaled = (value: number) => value * scale.value;

const destinationClass = (destination: string) =>
    destination === 'LinkUp Wallet' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700';

const statusClass = (status: string) => {
    if (status === 'Paid' || status === 'Credited') return 'bg-green-50 text-green-700';
    if (status === 'Ready') return 'bg-sky-50 text-sky-700';
    return 'bg-amber-50 text-amber-700';
};

const updateCharts = () => {
    if (partyCanvas.value) {
        charts.party?.destroy();
        charts.party = new Chart(partyCanvas.value, {
            type: 'bar',
            data: {
                labels: groupedByType.value.map(([type]) => type),
                datasets: [
                    {
                        label: 'Net Owed',
                        data: groupedByType.value.map(([, amount]) => Math.round(amount)),
                        backgroundColor: '#28A8FF',
                        borderRadius: 10,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { display: false } } },
            },
        });
    }

    if (destinationCanvas.value) {
        charts.destination?.destroy();
        charts.destination = new Chart(destinationCanvas.value, {
            type: 'doughnut',
            data: {
                labels: destinationSplit.value.map(([label]) => label),
                datasets: [{ data: destinationSplit.value.map(([, amount]) => Math.round(Number(amount))), backgroundColor: ['#22c55e', '#f59e0b'], borderWidth: 0 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14 } } },
            },
        });
    }
};

const handleFilterChange = (nextFilters: { region: string; country: string; period: string }) => {
    filters.value = nextFilters;
};

watch([filters, search], () => nextTick(updateCharts), { deep: true });

onMounted(() => {
    document.body.classList.add('new-admin-body');
    nextTick(updateCharts);
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
    charts.party?.destroy();
    charts.destination?.destroy();
});
</script>

<template>
    <Head title="Settlements" />

    <div class="flex min-h-screen">
        <NewAppSidebar v-show="sidebarVisible" active-id="settlementCommand" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                title="Settlements"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="sidebarVisible = !sidebarVisible"
                @filter-change="handleFilterChange"
                @search="search = $event"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2">
                        <p class="font-bold text-slate-300">Total Settlement Volume</p>
                        <h3 class="text-5xl font-black">{{ fmt(settlementTotals.volume) }}</h3>
                        <p class="font-bold text-lime-300">Money owed and released to parties</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Wallet Settlements</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.wallet) }}</h3>
                        <p class="font-bold text-green-500">Credited inside LinkUp</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">External Cash-Outs</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.external) }}</h3>
                        <p class="font-bold text-amber-500">Bank / ACH / card payouts</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Fees Retained</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.fees) }}</h3>
                        <p class="font-bold text-sky-500">LinkUp/platform fees</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Creator Settlements</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.creators) }}</h3>
                        <p class="font-bold text-purple-500">Live creators</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Marketplace Sellers</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.sellers) }}</h3>
                        <p class="font-bold text-green-500">Seller payouts</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Event Organizers</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.organizers) }}</h3>
                        <p class="font-bold text-sky-500">Event payouts</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Merchants / Restaurants</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.merchants) }}</h3>
                        <p class="font-bold text-amber-500">Merchant + Eats payouts</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Pending / Processing</p>
                        <h3 class="text-4xl font-black">{{ fmt(settlementTotals.pending) }}</h3>
                        <p class="font-bold text-rose-500">Needs action</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl p-6 2xl:col-span-2">
                        <h3 class="mb-4 text-xl font-black">Settlement by Party Type</h3>
                        <div class="h-[300px]"><canvas ref="partyCanvas"></canvas></div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Destination Split</h3>
                        <div class="h-[300px]"><canvas ref="destinationCanvas"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Wallet Settlement Flow</h3>
                        <div class="space-y-3">
                            <div v-for="row in filteredSettlements.filter((item) => item.destination === 'LinkUp Wallet')" :key="row.id" class="flex justify-between rounded-2xl border border-green-100 bg-green-50 p-4">
                                <div>
                                    <b>{{ row.party }}</b>
                                    <p class="text-sm text-slate-600">{{ row.type }} - {{ row.source }} - {{ row.status }}</p>
                                </div>
                                <span class="font-black">{{ fmt(scaled(row.payout)) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">External Cash-Out Flow</h3>
                        <div class="space-y-3">
                            <div v-for="row in filteredSettlements.filter((item) => item.destination !== 'LinkUp Wallet')" :key="row.id" class="flex justify-between rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <div>
                                    <b>{{ row.party }}</b>
                                    <p class="text-sm text-slate-600">{{ row.type }} - {{ row.source }} - {{ row.status }}</p>
                                </div>
                                <span class="font-black">{{ fmt(scaled(row.payout)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Tax / VAT / Government Fees</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="row in taxRows" :key="row[0]" class="rounded-2xl bg-slate-50 p-4">
                                <p class="font-bold text-slate-500">{{ row[0] }}</p>
                                <h3 class="text-2xl font-black">{{ row[2] === 'num' ? num(Number(row[1])) : fmt(Number(row[1])) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Settlement Rules</h3>
                        <div class="space-y-3">
                            <div v-for="rule in rules" :key="rule[0]" class="rounded-2xl bg-slate-50 p-4">
                                <b>{{ rule[0] }}</b>
                                <p class="mt-1 text-sm text-slate-600">{{ rule[1] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h3 class="text-xl font-black">Settlement Ledger</h3>
                        <input v-model="search" class="w-full rounded-2xl border border-slate-200 px-4 py-2 lg:w-96" placeholder="Search settlement, party, source, country, status..." />
                    </div>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3">ID</th>
                                    <th>Date</th>
                                    <th>Party</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>Country</th>
                                    <th>Gross</th>
                                    <th>Fees</th>
                                    <th>Tax/VAT</th>
                                    <th>Net Owed</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in filteredSettlements" :key="row.id" class="border-t">
                                    <td class="py-3 font-black">{{ row.id }}</td>
                                    <td>{{ row.date }}</td>
                                    <td>{{ row.party }}</td>
                                    <td>{{ row.type }}</td>
                                    <td>{{ row.source }}</td>
                                    <td>{{ row.country }}</td>
                                    <td>{{ fmt(scaled(row.gross)) }}</td>
                                    <td class="font-bold text-sky-600">{{ fmt(scaled(row.fees)) }}</td>
                                    <td class="font-bold text-amber-600">{{ fmt(scaled(row.tax)) }}</td>
                                    <td class="font-black">{{ fmt(scaled(row.payout)) }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="destinationClass(row.destination)">{{ row.destination }}</span>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="statusClass(row.status)">{{ row.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="!filteredSettlements.length">
                                    <td colspan="12" class="py-8 text-center font-bold text-slate-500">No settlements match the current filters.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
