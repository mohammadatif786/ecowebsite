<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
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

type Transfer = {
    id: string;
    date: string;
    senderCountry: string;
    recipientCountry: string;
    sender: string;
    recipient: string;
    type: 'Domestic' | 'International';
    amount: number;
    currency: string;
    fxRate: number;
    status: 'Completed' | 'Pending' | 'Review';
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

const transfers: Transfer[] = [
    { id: 'REM-5001', date: '2026-06-01 09:10', senderCountry: 'Bahamas', recipientCountry: 'Bahamas', sender: 'Marcus Johnson', recipient: 'Jordan Rolle', type: 'Domestic', amount: 1000, currency: 'BSD', fxRate: 1, status: 'Completed' },
    { id: 'REM-5002', date: '2026-06-01 09:42', senderCountry: 'United States', recipientCountry: 'Jamaica', sender: 'David Miller', recipient: 'Brianna Smith', type: 'International', amount: 2500, currency: 'USD', fxRate: 155.2, status: 'Completed' },
    { id: 'REM-5003', date: '2026-06-01 10:15', senderCountry: 'Canada', recipientCountry: 'Bahamas', sender: 'Natalie Brown', recipient: 'Aaliyah Clarke', type: 'International', amount: 1800, currency: 'CAD', fxRate: 0.73, status: 'Completed' },
    { id: 'REM-5004', date: '2026-06-01 10:51', senderCountry: 'Jamaica', recipientCountry: 'Jamaica', sender: 'Andre Williams', recipient: 'Kingston Vendor', type: 'Domestic', amount: 720, currency: 'USD', fxRate: 1, status: 'Completed' },
    { id: 'REM-5005', date: '2026-06-01 11:30', senderCountry: 'United States', recipientCountry: 'Bahamas', sender: 'Maya Evans', recipient: 'Marcus Johnson', type: 'International', amount: 5000, currency: 'USD', fxRate: 1, status: 'Review' },
    { id: 'REM-5006', date: '2026-06-01 12:05', senderCountry: 'Bahamas', recipientCountry: 'Haiti', sender: 'Jordan Rolle', recipient: 'Marie Jean', type: 'International', amount: 650, currency: 'BSD', fxRate: 132, status: 'Completed' },
    { id: 'REM-5007', date: '2026-06-01 12:45', senderCountry: 'Canada', recipientCountry: 'Jamaica', sender: 'Toronto Island Store', recipient: 'Kingston Social', type: 'International', amount: 2200, currency: 'CAD', fxRate: 113.4, status: 'Pending' },
    { id: 'REM-5008', date: '2026-06-01 13:18', senderCountry: 'Jamaica', recipientCountry: 'Trinidad & Tobago', sender: 'Brianna Smith', recipient: 'Tanya Baptiste', type: 'International', amount: 900, currency: 'USD', fxRate: 6.78, status: 'Completed' },
    { id: 'REM-5009', date: '2026-06-01 14:01', senderCountry: 'Trinidad & Tobago', recipientCountry: 'Trinidad & Tobago', sender: 'Devon Singh', recipient: 'Soca Event Co.', type: 'Domestic', amount: 1200, currency: 'TTD', fxRate: 1, status: 'Completed' },
    { id: 'REM-5010', date: '2026-06-01 14:40', senderCountry: 'United States', recipientCountry: 'Dominican Republic', sender: 'Diaspora Connect', recipient: 'Sofia Martinez', type: 'International', amount: 3100, currency: 'USD', fxRate: 58.8, status: 'Completed' },
    { id: 'REM-5011', date: '2026-06-01 15:07', senderCountry: 'Brazil', recipientCountry: 'Colombia', sender: 'Lucas Silva', recipient: 'Camila Torres', type: 'International', amount: 1700, currency: 'USD', fxRate: 3925, status: 'Review' },
    { id: 'REM-5012', date: '2026-06-01 15:50', senderCountry: 'Barbados', recipientCountry: 'Barbados', sender: 'Keisha Alleyne', recipient: 'Bajan Elite Events', type: 'Domestic', amount: 540, currency: 'BBD', fxRate: 1, status: 'Completed' },
];

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const ledgerSearch = ref('');
const corridorCanvas = ref<HTMLCanvasElement | null>(null);
const typeCanvas = ref<HTMLCanvasElement | null>(null);
const charts: { corridor?: Chart; type?: Chart } = {};

const defaultFees = {
    domLinkupPct: 1,
    domLinkupMin: 0.5,
    domLinkupMax: 5,
    domBankPct: 0.25,
    intlLinkupPct: 2.5,
    intlBankPct: 1,
    intlFxPct: 1,
};

const savedFees = (() => {
    try {
        return JSON.parse(localStorage.getItem('linkupRemitFees') || '{}');
    } catch {
        return {};
    }
})();

const feeConfig = ref({ ...defaultFees, ...savedFees });
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
    netProfit: fmt(platformTotals.value.platform - platformTotals.value.cost + platformTotals.value.bank * 0.6),
    users: num(platformTotals.value.users),
    merchants: num(platformTotals.value.merchants),
    organizers: num(platformTotals.value.organizers),
    countries: num(filteredCountries.value.length),
}));

const filteredTransfers = computed(() => {
    const query = ledgerSearch.value.toLowerCase().trim();

    return transfers.filter((transfer) => {
        const senderCountry = countries.value.find((country) => country.country === transfer.senderCountry);
        const recipientCountry = countries.value.find((country) => country.country === transfer.recipientCountry);
        const regionOk =
            filters.value.region === 'All' || senderCountry?.region === filters.value.region || recipientCountry?.region === filters.value.region;
        const countryOk =
            filters.value.country === 'All Countries' ||
            transfer.senderCountry === filters.value.country ||
            transfer.recipientCountry === filters.value.country;
        const searchOk =
            !query ||
            [
                transfer.id,
                transfer.date,
                transfer.senderCountry,
                transfer.recipientCountry,
                transfer.sender,
                transfer.recipient,
                transfer.type,
                transfer.status,
                `${transfer.senderCountry} -> ${transfer.recipientCountry}`,
            ]
                .join(' ')
                .toLowerCase()
                .includes(query);

        return regionOk && countryOk && searchOk;
    });
});

const remitFees = (transfer: Transfer) => {
    const amount = transfer.amount * scale.value;
    const domestic = transfer.type === 'Domestic';
    const fee = feeConfig.value;
    const linkupFee = domestic
        ? Math.min(Math.max(amount * (fee.domLinkupPct / 100), fee.domLinkupMin), fee.domLinkupMax)
        : amount * (fee.intlLinkupPct / 100);
    const bankFee = domestic ? amount * (fee.domBankPct / 100) : amount * (fee.intlBankPct / 100);
    const fxFee = domestic ? 0 : amount * (fee.intlFxPct / 100);
    const totalFee = linkupFee + bankFee + fxFee;

    return {
        amount,
        linkupFee,
        bankFee,
        fxFee,
        totalFee,
        recipientAmount: amount - totalFee,
    };
};

const remitTotals = computed(() =>
    filteredTransfers.value.reduce(
        (total, transfer) => {
            const fee = remitFees(transfer);
            total.volume += fee.amount;
            total.linkupFees += fee.linkupFee;
            total.bankFees += fee.bankFee;
            total.fxFees += fee.fxFee;
            total.totalFees += fee.totalFee;
            total.count += 1;
            if (transfer.type === 'Domestic') total.domestic += fee.amount;
            else total.international += fee.amount;
            if (transfer.status !== 'Completed') total.alerts += 1;
            return total;
        },
        { volume: 0, domestic: 0, international: 0, linkupFees: 0, bankFees: 0, fxFees: 0, totalFees: 0, count: 0, alerts: 0 },
    ),
);

const corridorRows = computed(() => {
    const map = new Map<string, { corridor: string; type: string; volume: number; count: number; linkupFees: number; bankFees: number; fxFees: number; totalFees: number }>();

    filteredTransfers.value.forEach((transfer) => {
        const corridor = `${transfer.senderCountry} -> ${transfer.recipientCountry}`;
        const fee = remitFees(transfer);
        const row =
            map.get(corridor) ||
            {
                corridor,
                type: transfer.type,
                volume: 0,
                count: 0,
                linkupFees: 0,
                bankFees: 0,
                fxFees: 0,
                totalFees: 0,
            };

        row.volume += fee.amount;
        row.count += 1;
        row.linkupFees += fee.linkupFee;
        row.bankFees += fee.bankFee;
        row.fxFees += fee.fxFee;
        row.totalFees += fee.totalFee;
        map.set(corridor, row);
    });

    return Array.from(map.values()).sort((a, b) => b.volume - a.volume);
});

const countryFlows = computed(() => {
    const map = new Map<string, { country: string; sent: number; received: number; fees: number; net: number }>();

    countries.value.forEach((country) => map.set(country.country, { country: country.country, sent: 0, received: 0, fees: 0, net: 0 }));
    filteredTransfers.value.forEach((transfer) => {
        const fee = remitFees(transfer);
        const sender = map.get(transfer.senderCountry) || { country: transfer.senderCountry, sent: 0, received: 0, fees: 0, net: 0 };
        const recipient = map.get(transfer.recipientCountry) || { country: transfer.recipientCountry, sent: 0, received: 0, fees: 0, net: 0 };

        sender.sent += fee.amount;
        sender.fees += fee.totalFee;
        recipient.received += fee.recipientAmount;
        map.set(transfer.senderCountry, sender);
        map.set(transfer.recipientCountry, recipient);
    });

    return Array.from(map.values())
        .map((row) => ({ ...row, net: row.received - row.sent }))
        .filter((row) => row.sent || row.received || row.fees)
        .sort((a, b) => Math.abs(b.net) - Math.abs(a.net));
});

const pricingRows = computed(() => [
    ['Domestic P2P', `${feeConfig.value.domLinkupPct}% fee, min ${fmt(feeConfig.value.domLinkupMin)}, max ${fmt(feeConfig.value.domLinkupMax)}`, 'Bahamas -> Bahamas / Jamaica -> Jamaica'],
    ['International Remittance', `${feeConfig.value.intlLinkupPct}% LinkUp + ${feeConfig.value.intlBankPct}% bank + ${feeConfig.value.intlFxPct}% FX`, 'USA -> Jamaica / Canada -> Bahamas'],
    ['Bank Revenue', `${feeConfig.value.domBankPct}% domestic, ${feeConfig.value.intlBankPct}% international`, 'Earned by banking or rail partner'],
    ['FX Revenue', `${feeConfig.value.intlFxPct}% spread on cross-border transfers`, 'Shown separately from LinkUp transfer fee'],
]);

const treasuryRows = computed(() => {
    const volume = remitTotals.value.volume;
    return [
        ['Remittance Float', volume * 0.18],
        ['Pending Settlements', volume * 0.07],
        ['Outstanding Transfers', volume * 0.04],
        ['Liquidity Coverage', volume * 0.25],
        ['Reserve Requirement', volume * 0.11],
        ['Settlement Bank Balance', volume * 0.31],
    ];
});

const complianceRows = computed(() => {
    const total = remitTotals.value;
    return [
        ['KYC Approved', num(total.count * 0.91)],
        ['KYC Pending', num(total.count * 0.09)],
        ['AML Alerts', num(total.alerts + 2)],
        ['Sanctions Hits', '0'],
        ['Structuring Alerts', num(total.alerts)],
        ['SAR Queue', num(Math.max(0, total.alerts - 1))],
    ];
});

const statusClass = (status: string) => {
    if (status === 'Completed') return 'bg-green-50 text-green-700';
    if (status === 'Pending') return 'bg-amber-50 text-amber-700';
    return 'bg-rose-50 text-rose-700';
};

const handleFilterChange = (nextFilters: { region: string; country: string; period: string }) => {
    filters.value = nextFilters;
};

const updateCharts = () => {
    if (corridorCanvas.value) {
        charts.corridor?.destroy();
        const rows = corridorRows.value.slice(0, 8);
        charts.corridor = new Chart(corridorCanvas.value, {
            type: 'bar',
            data: {
                labels: rows.map((row) => row.corridor),
                datasets: [
                    {
                        label: 'Volume',
                        data: rows.map((row) => Math.round(row.volume)),
                        backgroundColor: '#28A8FF',
                        borderRadius: 10,
                    },
                    {
                        label: 'Total Fees',
                        data: rows.map((row) => Math.round(row.totalFees)),
                        backgroundColor: '#D9EC10',
                        borderRadius: 10,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { usePointStyle: true } } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { display: false } } },
            },
        });
    }

    if (typeCanvas.value) {
        charts.type?.destroy();
        charts.type = new Chart(typeCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Domestic', 'International'],
                datasets: [
                    {
                        data: [Math.round(remitTotals.value.domestic), Math.round(remitTotals.value.international)],
                        backgroundColor: ['#22c55e', '#0ea5e9'],
                        borderWidth: 0,
                    },
                ],
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

const saveFees = () => {
    localStorage.setItem('linkupRemitFees', JSON.stringify(feeConfig.value));
};

const resetFees = () => {
    feeConfig.value = { ...defaultFees };
    localStorage.removeItem('linkupRemitFees');
};

watch([filters, ledgerSearch, feeConfig], () => nextTick(updateCharts), { deep: true });

onMounted(() => {
    document.body.classList.add('new-admin-body');
    nextTick(updateCharts);
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
    charts.corridor?.destroy();
    charts.type?.destroy();
});
</script>

<template>
    <Head title="Remittance" />

    <div class="flex min-h-screen">
        <NewAppSidebar v-show="sidebarVisible" active-id="remittanceCommand" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                title="Remittance"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="sidebarVisible = !sidebarVisible"
                @filter-change="handleFilterChange"
                @search="ledgerSearch = $event"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2">
                        <p class="font-bold text-slate-300">Total Remittance Volume</p>
                        <h3 class="text-5xl font-black">{{ fmt(remitTotals.volume) }}</h3>
                        <p class="font-bold text-lime-300">Domestic + international money movement</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Domestic Transfers</p>
                        <h3 class="text-4xl font-black">{{ fmt(remitTotals.domestic) }}</h3>
                        <p class="font-bold text-green-500">Within same jurisdiction</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">International Transfers</p>
                        <h3 class="text-4xl font-black">{{ fmt(remitTotals.international) }}</h3>
                        <p class="font-bold text-sky-500">Cross-border remittance</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Total Transfers</p>
                        <h3 class="text-4xl font-black">{{ num(remitTotals.count) }}</h3>
                        <p class="font-bold text-purple-500">Transfer count</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">LinkUp Fees</p>
                        <h3 class="text-4xl font-black">{{ fmt(remitTotals.linkupFees) }}</h3>
                        <p class="font-bold text-green-500">Platform fee revenue</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Bank Fees</p>
                        <h3 class="text-4xl font-black">{{ fmt(remitTotals.bankFees) }}</h3>
                        <p class="font-bold text-amber-500">Bank / rail revenue</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">FX Revenue</p>
                        <h3 class="text-4xl font-black">{{ fmt(remitTotals.fxFees) }}</h3>
                        <p class="font-bold text-sky-500">Currency spread revenue</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Average Transfer</p>
                        <h3 class="text-4xl font-black">{{ fmt(remitTotals.count ? remitTotals.volume / remitTotals.count : 0) }}</h3>
                        <p class="font-bold text-purple-500">Average send amount</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Compliance Alerts</p>
                        <h3 class="text-4xl font-black">{{ num(remitTotals.alerts) }}</h3>
                        <p class="font-bold text-rose-500">Review / pending</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl p-6 2xl:col-span-2">
                        <h3 class="mb-1 text-xl font-black">Top Remittance Corridors</h3>
                        <p class="mb-4 text-slate-500">Volume and fee reporting by sender -> recipient country.</p>
                        <div class="h-[300px]"><canvas ref="corridorCanvas"></canvas></div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-1 text-xl font-black">Domestic vs International</h3>
                        <p class="mb-4 text-slate-500">Transfer classification split.</p>
                        <div class="h-[300px]"><canvas ref="typeCanvas"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Country Flow Heat Map</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="flow in countryFlows" :key="flow.country" class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
                                <p class="font-bold text-slate-500">{{ flow.country }}</p>
                                <h3 class="mt-1 text-2xl font-black">{{ fmt(flow.net) }}</h3>
                                <p class="mt-2 text-sm text-slate-600">Sent: {{ fmt(flow.sent) }}<br />Received: {{ fmt(flow.received) }}<br />Fees: {{ fmt(flow.fees) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <div class="mb-4 flex flex-col justify-between gap-3 xl:flex-row xl:items-center">
                            <h3 class="text-xl font-black">Pricing Engine</h3>
                            <div class="flex gap-2">
                                <button @click="saveFees" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white">Save Fees</button>
                                <button @click="resetFees" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black">Reset</button>
                            </div>
                        </div>
                        <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                            <input v-model.number="feeConfig.domLinkupPct" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold" placeholder="Domestic %" />
                            <input v-model.number="feeConfig.domBankPct" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold" placeholder="Domestic bank %" />
                            <input v-model.number="feeConfig.intlLinkupPct" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold" placeholder="Intl LinkUp %" />
                            <input v-model.number="feeConfig.intlFxPct" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold" placeholder="Intl FX %" />
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="row in pricingRows" :key="row[0]" class="rounded-2xl bg-slate-50 p-4">
                                <b>{{ row[0] }}</b>
                                <p class="mt-1 text-sm text-slate-600">{{ row[1] }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ row[2] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Remittance Treasury</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="row in treasuryRows" :key="row[0]" class="rounded-2xl bg-slate-50 p-4">
                                <p class="font-bold text-slate-500">{{ row[0] }}</p>
                                <h3 class="text-2xl font-black">{{ fmt(Number(row[1])) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Compliance Dashboard</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="row in complianceRows" :key="row[0]" class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <p class="font-bold text-slate-600">{{ row[0] }}</p>
                                <h3 class="text-2xl font-black">{{ row[1] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Corridor Report</h3>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3">Corridor</th>
                                    <th>Type</th>
                                    <th>Volume</th>
                                    <th>Transactions</th>
                                    <th>LinkUp Fees</th>
                                    <th>Bank Fees</th>
                                    <th>FX Revenue</th>
                                    <th>Total Fees</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in corridorRows" :key="row.corridor" class="border-t">
                                    <td class="py-3 font-black">{{ row.corridor }}</td>
                                    <td>{{ row.type }}</td>
                                    <td>{{ fmt(row.volume) }}</td>
                                    <td>{{ num(row.count) }}</td>
                                    <td class="font-bold text-green-600">{{ fmt(row.linkupFees) }}</td>
                                    <td class="font-bold text-amber-600">{{ fmt(row.bankFees) }}</td>
                                    <td class="font-bold text-sky-600">{{ fmt(row.fxFees) }}</td>
                                    <td class="font-black">{{ fmt(row.totalFees) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-xl font-black">Full Remittance Transfer Ledger</h3>
                            <p class="text-slate-500">Tracks domestic and international transfers, fees, FX, bank revenue, compliance status, and corridors.</p>
                        </div>
                        <input
                            v-model="ledgerSearch"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-2 lg:w-96"
                            placeholder="Search corridor, country, sender, recipient, status..."
                        />
                    </div>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3">Transfer ID</th>
                                    <th>Date</th>
                                    <th>Sender</th>
                                    <th>Recipient</th>
                                    <th>Corridor</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>LinkUp Fee</th>
                                    <th>Bank Fee</th>
                                    <th>FX Fee</th>
                                    <th>Total Fee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transfer in filteredTransfers" :key="transfer.id" class="border-t">
                                    <td class="py-3 font-black">{{ transfer.id }}</td>
                                    <td>{{ transfer.date }}</td>
                                    <td>
                                        {{ transfer.sender }}
                                        <div class="text-xs text-slate-500">{{ transfer.senderCountry }}</div>
                                    </td>
                                    <td>
                                        {{ transfer.recipient }}
                                        <div class="text-xs text-slate-500">{{ transfer.recipientCountry }}</div>
                                    </td>
                                    <td>{{ transfer.senderCountry }} -> {{ transfer.recipientCountry }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="transfer.type === 'Domestic' ? 'bg-green-50 text-green-700' : 'bg-sky-50 text-sky-700'">
                                            {{ transfer.type }}
                                        </span>
                                    </td>
                                    <td class="font-black">{{ fmt(remitFees(transfer).amount) }}</td>
                                    <td class="font-bold text-green-600">{{ fmt(remitFees(transfer).linkupFee) }}</td>
                                    <td class="font-bold text-amber-600">{{ fmt(remitFees(transfer).bankFee) }}</td>
                                    <td class="font-bold text-sky-600">{{ fmt(remitFees(transfer).fxFee) }}</td>
                                    <td class="font-black">{{ fmt(remitFees(transfer).totalFee) }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="statusClass(transfer.status)">{{ transfer.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="!filteredTransfers.length">
                                    <td colspan="12" class="py-8 text-center font-bold text-slate-500">No transfers match the current filters.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
