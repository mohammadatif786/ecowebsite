<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import {
    Archive,
    ArrowLeftRight,
    ArrowUpFromLine,
    BadgeDollarSign,
    Banknote,
    Building2,
    Coins as CoinsIcon,
    CreditCard,
    Flame,
    Landmark,
    Layers,
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
import { computed, onMounted, onUnmounted, ref, nextTick } from 'vue';
import NewAppHeader from './components/NewAppHeader.vue';
import NewAppSidebar from './components/NewAppSidebar.vue';

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
    return countries.value.filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const unitVolume = (u: any, rs = getFilteredCountries()) => {
    return rs.reduce((s, c: any) => s + (Number(c[u.key]) || 0), 0) * getScale();
};

const unitFin = (u: any, rs = getFilteredCountries()) => {
    const gross = unitVolume(u, rs);
    const platform = gross * u.platformRate;
    const bank = gross * u.bankRate;
    const cost = gross * u.costRate;
    return { gross, platform, bank, fees: platform + bank, cost, net: platform - cost };
};

const countryFin = (c: any) => {
    let gross = 0,
        platform = 0,
        bank = 0,
        cost = 0;
    units.value.forEach((u) => {
        const v = (Number(c[u.key]) || 0) * getScale();
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

const moneyMoveCfg = { cardPct: 2.9, bankPct: 0.8, cashOutPct: 1.5, cardMix: 60 };
const p2pFeeCfg = { domSendPct: 1.5, domRecvPct: 0.5 };

const feeRevenueModel = () => {
    const rs = getFilteredCountries();
    const t = getTotals();
    const c = moneyMoveCfg;
    const share = SCOTIA_SHARE;

    // 1) Platform fees by unit
    const unitRows = units.value
        .map((u) => {
            const f = unitFin(u, rs);
            return { name: u.name, key: u.key, icon: u.icon, gross: f.gross, rate: u.platformRate, fee: f.platform };
        })
        .filter((x) => x.fee > 0)
        .sort((a, b) => b.fee - a.fee);
    const platformTotal = unitRows.reduce((a, x) => a + x.fee, 0);

    // 2) Money-movement volumes
    const walletUnit = units.value.find((u) => u.key === 'wallet');
    const mmVol = walletUnit ? unitFin(walletUnit, rs).gross : 0;
    const cardMix = (c.cardMix || 60) / 100;
    const cashInVol = mmVol * 0.5,
        cashOutVol = mmVol * 0.3,
        p2pVol = mmVol * 0.2;
    const cardVol = cashInVol * cardMix,
        achVol = cashInVol * (1 - cardMix);

    let rawCard = cardVol * (c.cardPct / 100),
        rawAch = achVol * (c.bankPct / 100),
        rawOut = cashOutVol * (c.cashOutPct / 100);
    const rawSum = rawCard + rawAch + rawOut || 1;
    const k = t.bank / rawSum;
    const cardFee = rawCard * k,
        achFee = rawAch * k,
        cashOutFee = rawOut * k;

    // 3) Remittance (Dummy for now)
    const rt = { linkupFees: 0, bankFees: 0, fxFees: 0, totalFees: 0, volume: 0 };

    // 4) P2P
    const p2pRate = (p2pFeeCfg.domSendPct + p2pFeeCfg.domRecvPct) / 100;
    const p2pFee = p2pVol * p2pRate;

    // 5) Coins
    const coinsRevenue = rs.reduce((s, c) => s + (Number(c.coinsPurchased) || 0), 0) * getScale() * 0.5;
    const coinsPurchased = rs.reduce((s, c) => s + (Number(c.coinsPurchased) || 0), 0) * getScale();

    const bankPool = t.bank + rt.bankFees + rt.fxFees;
    const linkupFromBank = bankPool * (1 - share),
        scotia = bankPool * share;
    const linkupTotal = platformTotal + p2pFee + rt.linkupFees + coinsRevenue + linkupFromBank;
    const grandTotal = platformTotal + p2pFee + rt.totalFees + t.bank + coinsRevenue;

    const moveRows = [
        { label: 'Cash-In — Credit/Debit Card', vol: cardVol, rate: c.cardPct + '%', to: 'Bank Pool', fee: cardFee },
        { label: 'Cash-In — Bank / ACH', vol: achVol, rate: c.bankPct + '%', to: 'Bank Pool', fee: achFee },
        { label: 'Cash-Out / Withdrawal', vol: cashOutVol, rate: c.cashOutPct + '%', to: 'Bank Pool', fee: cashOutFee },
        { label: 'Send / Receive (P2P)', vol: p2pVol, rate: (p2pRate * 100).toFixed(2) + '%', to: 'LinkUp', fee: p2pFee },
        { label: 'LinkUp Coins — 50% Share', vol: coinsPurchased, rate: '50%', to: 'LinkUp', fee: coinsRevenue },
    ];

    const streams: any[] = [];
    unitRows.forEach((u) => streams.push({ label: u.name + ' (Platform)', fee: u.fee, to: 'LinkUp', icon: u.icon || 'layers' }));
    streams.push({ label: 'Cash-In — Card', fee: cardFee, to: 'Bank Pool', icon: 'credit-card' });
    streams.push({ label: 'Cash-In — Bank/ACH', fee: achFee, to: 'Bank Pool', icon: 'landmark' });
    streams.push({ label: 'Cash-Out / Withdrawal', fee: cashOutFee, to: 'Bank Pool', icon: 'arrow-up-from-line' });
    streams.push({ label: 'Send / Receive (P2P)', fee: p2pFee, to: 'LinkUp', icon: 'arrow-left-right' });
    streams.push({ label: 'LinkUp Coins (50% share)', fee: coinsRevenue, to: 'LinkUp', icon: 'coins' });

    return {
        unitRows,
        platformTotal,
        cardFee,
        achFee,
        cashOutFee,
        rt,
        p2pFee,
        coinsRevenue,
        bankPool,
        linkupFromBank,
        scotia,
        linkupTotal,
        grandTotal,
        share,
        moveRows,
        streams,
        bankRows: [
            { label: 'Card Processing (cash-in)', fee: cardFee },
            { label: 'Bank / ACH (cash-in)', fee: achFee },
            { label: 'Cash-Out / Withdrawal', fee: cashOutFee },
        ],
    };
};

const model = ref<any>(feeRevenueModel());
const charts = ref<any>({});
const splitChartRef = ref<HTMLCanvasElement | null>(null);
const streamChartRef = ref<HTMLCanvasElement | null>(null);

const FR_GRAD = [
    ['#6366f1', '#8b5cf6'],
    ['#0ea5e9', '#06b6d4'],
    ['#10b981', '#34d399'],
    ['#f59e0b', '#fbbf24'],
    ['#ef4444', '#f97316'],
    ['#ec4899', '#f472b6'],
    ['#8b5cf6', '#a855f7'],
    ['#14b8a6', '#2dd4bf'],
    ['#3b82f6', '#60a5fa'],
    ['#84cc16', '#a3e635'],
    ['#f43f5e', '#fb7185'],
    ['#0891b2', '#22d3ee'],
    ['#7c3aed', '#c084fc'],
    ['#059669', '#10b981'],
    ['#d97706', '#f59e0b'],
    ['#2563eb', '#3b82f6'],
    ['#db2777', '#ec4899'],
    ['#0d9488', '#14b8a6'],
    ['#9333ea', '#a855f7'],
    ['#ca8a04', '#eab308'],
    ['#dc2626', '#ef4444'],
];

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
    layers: Layers,
    'trending-up': TrendingUp,
    landmark: Landmark,
    'building-2': Building2,
    'arrow-up-from-line': ArrowUpFromLine,
    send: Send,
    banknote: Banknote,
    'refresh-cw': RefreshCw,
    'arrow-left-right': ArrowLeftRight,
    archive: Archive,
    'piggy-bank': PiggyBank,
    coins: CoinsIcon,
};

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());
    const m = model.value;

    const splitCtx = splitChartRef.value;
    if (splitCtx) {
        charts.value.split = new Chart(splitCtx, {
            type: 'doughnut',
            data: {
                labels: ['LinkUp Platform', 'LinkUp 60% of Bank', 'Scotiabank 40%'],
                datasets: [
                    {
                        data: [
                            Math.round(m.platformTotal + m.p2pFee + m.coinsRevenue),
                            Math.round(m.linkupFromBank),
                            Math.round(m.scotia),
                        ],
                        backgroundColor: ['#10b981', '#06b6d4', '#ef4444'],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } },
                cutout: '62%',
            },
        });
    }

    const streamCtx = streamChartRef.value;
    if (streamCtx) {
        const top = m.streams
            .slice()
            .sort((a: any, b: any) => b.fee - a.fee)
            .slice(0, 8);
        charts.value.stream = new Chart(streamCtx, {
            type: 'bar',
            data: {
                labels: top.map((s: any) => s.label.replace(' (Platform)', '')),
                datasets: [
                    {
                        label: 'Fee Revenue',
                        data: top.map((s: any) => Math.round(s.fee)),
                        backgroundColor: top.map((s: any) => (s.to === 'LinkUp' ? '#10b981' : '#f59e0b')),
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { ticks: { callback: (v) => '$' + Number(v) / 1000 + 'k' } } },
            },
        });
    }
};

const renderAll = async () => {
    await nextTick();
    model.value = feeRevenueModel();
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

const sortedStreams = computed(() => {
    return model.value.streams.slice().sort((a: any, b: any) => b.fee - a.fee);
});

const maxFee = computed(() => {
    return Math.max(...model.value.streams.map((s: any) => s.fee), 1);
});
</script>

<template>
    <Head title="Fee Revenue Dashboard" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="feeRevenueCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Fee Revenue Dashboard" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div id="feeRevenueCommand" class="space-y-6">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h3 class="text-3xl font-black text-emerald-600">Fee Revenue Dashboard</h3>
                            <p class="text-slate-500">
                                Every fee the platform collects, at a glance — platform fees by unit, money-movement & transfer fees, coins, and
                                exactly what the bank earns.
                            </p>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
                        <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #0f172a, #1e3a8a)">
                            <p class="flex items-center gap-1 text-sm font-bold text-white/70"><Layers class="h-4 w-4" /> Total Fees</p>
                            <h3 class="mt-1 text-3xl font-black">{{ fmt(model.grandTotal) }}</h3>
                        </div>
                        <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #059669, #10b981)">
                            <p class="flex items-center gap-1 text-sm font-bold text-white/80"><TrendingUp class="h-4 w-4" /> LinkUp Revenue</p>
                            <h3 class="mt-1 text-3xl font-black">{{ fmt(model.linkupTotal) }}</h3>
                        </div>
                        <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #d97706, #f59e0b)">
                            <p class="flex items-center gap-1 text-sm font-bold text-white/80"><Landmark class="h-4 w-4" /> Bank Pool</p>
                            <h3 class="mt-1 text-3xl font-black">{{ fmt(model.bankPool) }}</h3>
                        </div>
                        <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #0891b2, #06b6d4)">
                            <p class="flex items-center gap-1 text-sm font-bold text-white/80"><Wallet class="h-4 w-4" /> LinkUp keeps 60%</p>
                            <h3 class="mt-1 text-3xl font-black">{{ fmt(model.linkupFromBank) }}</h3>
                        </div>
                        <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #b91c1c, #ef4444)">
                            <p class="flex items-center gap-1 text-sm font-bold text-white/80"><Building2 class="h-4 w-4" /> Scotiabank 40%</p>
                            <h3 class="mt-1 text-3xl font-black">{{ fmt(model.scotia) }}</h3>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                        <div class="card rounded-3xl p-6">
                            <h4 class="mb-3 font-black">Revenue Split</h4>
                            <div class="relative h-[300px] w-full">
                                <canvas ref="splitChartRef"></canvas>
                            </div>
                        </div>
                        <div class="card rounded-3xl p-6 xl:col-span-2">
                            <h4 class="mb-3 font-black">Top Fee Streams</h4>
                            <div class="relative h-[300px] w-full">
                                <canvas ref="streamChartRef"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Streams Tiles -->
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <h4 class="text-lg font-black">All Fee Streams — Live</h4>
                            <span class="text-xs font-bold text-slate-400">Every revenue stream in the system, updating live</span>
                        </div>
                        <div id="frTiles" class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                            <div
                                v-for="(s, i) in sortedStreams"
                                :key="i"
                                class="relative overflow-hidden rounded-3xl p-5 text-white shadow-lg"
                                :style="{
                                    background: `linear-gradient(135deg, ${FR_GRAD[i % FR_GRAD.length][0]}, ${FR_GRAD[i % FR_GRAD.length][1]})`,
                                }"
                            >
                                <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full" style="background: rgba(255, 255, 255, 0.14)"></div>
                                <div class="relative flex items-start justify-between">
                                    <div class="grid h-11 w-11 place-items-center rounded-2xl" style="background: rgba(255, 255, 255, 0.22)">
                                        <component :is="iconMap[s.icon] || Layers" class="h-5 w-5" />
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-black" style="background: rgba(255, 255, 255, 0.22)">{{
                                        s.to === 'LinkUp' ? 'LinkUp' : 'Bank Pool'
                                    }}</span>
                                </div>
                                <p class="relative mt-3 min-h-[34px] text-sm font-bold text-white/85">{{ s.label }}</p>
                                <h3 class="relative mt-1 text-2xl font-black">{{ fmt(s.fee) }}</h3>
                                <div class="relative mt-3 h-1.5 overflow-hidden rounded-full" style="background: rgba(255, 255, 255, 0.25)">
                                    <div class="h-full bg-white" :style="{ width: Math.round((s.fee / maxFee) * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Fees Table -->
                    <div class="card rounded-3xl p-6">
                        <h4 class="mb-1 font-black">
                            Platform Fees by Business Unit
                            <span class="text-xs font-normal text-slate-400">(LinkUp's cut on in-ecosystem activity)</span>
                        </h4>
                        <div class="scrollbar overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="text-xs text-slate-500 uppercase">
                                    <tr>
                                        <th class="py-2">Business Unit</th>
                                        <th>GTV</th>
                                        <th>Platform Fee %</th>
                                        <th class="text-right">Fee Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="u in model.unitRows" :key="u.name" class="border-t">
                                        <td class="py-2 font-bold">{{ u.name }}</td>
                                        <td>{{ fmt(u.gross) }}</td>
                                        <td>{{ (u.rate * 100).toFixed(2) }}%</td>
                                        <td class="text-right font-black">{{ fmt(u.fee) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-slate-300 font-black">
                                        <td class="py-2">Total Platform Fees</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right text-emerald-600">{{ fmt(model.platformTotal) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Money Movement Table -->
                    <div class="card rounded-3xl p-6">
                        <h4 class="mb-1 font-black">
                            Money Movement, Transfers & Other Revenue
                            <span class="text-xs font-normal text-slate-400">(cash-in/out, P2P, coins)</span>
                        </h4>
                        <div class="scrollbar overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="text-xs text-slate-500 uppercase">
                                    <tr>
                                        <th class="py-2">Fee Stream</th>
                                        <th>Volume</th>
                                        <th>Rate</th>
                                        <th>Goes to</th>
                                        <th class="text-right">Fee Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="r in model.moveRows" :key="r.label" class="border-t">
                                        <td class="py-2 font-bold">{{ r.label }}</td>
                                        <td>{{ fmt(r.vol) }}</td>
                                        <td>{{ r.rate }}</td>
                                        <td>
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs font-black"
                                                :class="r.to === 'LinkUp' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                                >{{ r.to }}</span
                                            >
                                        </td>
                                        <td class="text-right font-black">{{ fmt(r.fee) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-slate-300 font-black">
                                        <td class="py-2">Total Money-Movement Fees</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">{{ fmt(model.moveRows.reduce((a, x) => a + x.fee, 0)) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Bank Processing Pool Table -->
                    <div class="card rounded-3xl border-2 border-amber-200 p-6">
                        <h4 class="mb-1 flex items-center gap-2 font-black">
                            <Landmark class="h-5 w-5 text-amber-600" /> Bank's Cut — Processing Pool Breakdown
                        </h4>
                        <p class="mb-3 text-sm text-slate-500">
                            Exactly what the banking partner earns, by source. Split LinkUp 60% / Scotiabank 40%. This breakdown also appears in the
                            Partner Portal.
                        </p>
                        <div class="scrollbar overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="text-xs text-slate-500 uppercase">
                                    <tr>
                                        <th class="py-2">Bank Fee Source</th>
                                        <th class="text-right">Pool</th>
                                        <th class="text-right">LinkUp 60%</th>
                                        <th class="text-right">Scotiabank 40%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="r in model.bankRows" :key="r.label" class="border-t">
                                        <td class="py-2 font-bold">{{ r.label }}</td>
                                        <td class="text-right">{{ fmt(r.fee) }}</td>
                                        <td class="text-right">{{ fmt(r.fee * (1 - model.share)) }}</td>
                                        <td class="text-right text-red-600">{{ fmt(r.fee * model.share) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-slate-300 font-black">
                                        <td class="py-2">Total Bank Pool</td>
                                        <td class="text-right text-amber-600">{{ fmt(model.bankPool) }}</td>
                                        <td class="text-right">{{ fmt(model.linkupFromBank) }}</td>
                                        <td class="text-right text-red-600">{{ fmt(model.scotia) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
