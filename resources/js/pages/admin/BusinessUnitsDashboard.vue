<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import {
    Archive,
    BadgeDollarSign,
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
    ShoppingBag,
    Sparkles,
    Ticket,
    Utensils,
    Wallet,
} from 'lucide-vue-next';
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
    {
        key: 'tickets',
        name: 'Ticket Sales',
        icon: 'ticket',
        platformRate: 0.065,
        bankRate: 0,
        costRate: 0.01,
        desc: 'Tickets, VIP, drink tickets, event add-ons',
    },
    {
        key: 'subscriptions',
        name: 'Subscriptions',
        icon: 'badge-dollar-sign',
        platformRate: 1,
        bankRate: 0,
        costRate: 0.04,
        desc: 'Premium users and paid plans',
    },
    {
        key: 'marketplace',
        name: 'Marketplace',
        icon: 'shopping-bag',
        platformRate: 0.05,
        bankRate: 0,
        costRate: 0.01,
        desc: 'Seller fees and commissions',
    },
    {
        key: 'eats',
        name: 'LinkUp Eats',
        icon: 'utensils',
        platformRate: 0.075,
        bankRate: 0,
        costRate: 0.025,
        desc: 'QR menus, ordering, pickup, delivery',
    },
    {
        key: 'merchantPay',
        name: 'Merchant Pay',
        icon: 'credit-card',
        platformRate: 0.02,
        bankRate: 0,
        costRate: 0.007,
        desc: 'QR payments, card, ACH, bill pay',
    },
    {
        key: 'wallet',
        name: 'Wallet & Money Movement',
        icon: 'wallet',
        platformRate: 0.025,
        bankRate: 0.0175,
        costRate: 0.008,
        desc: 'Cash-in (top-up), cash-out (withdrawal), transfers',
    },
    { key: 'live', name: 'LinkUp Live', icon: 'radio', platformRate: 0.5, bankRate: 0, costRate: 0.08, desc: 'Live coins, gifts, creators' },
    {
        key: 'ads',
        name: 'Advertising Revenue',
        icon: 'megaphone',
        platformRate: 1,
        bankRate: 0,
        costRate: 0.12,
        desc: 'Swipe ads, email ads, promoted posts',
    },
    {
        key: 'wellness',
        name: 'Wellness & Spa',
        icon: 'sparkles',
        platformRate: 0.0675,
        bankRate: 0,
        costRate: 0.015,
        desc: 'Bookings and appointment marketplace',
    },
    { key: 'cookouts', name: 'Cookouts', icon: 'flame', platformRate: 0.0675, bankRate: 0, costRate: 0.015, desc: 'Food events and vendor sales' },
    {
        key: 'linkup360',
        name: 'LinkUp 360 News Ads',
        icon: 'newspaper',
        platformRate: 1,
        bankRate: 0,
        costRate: 0.15,
        desc: 'Sponsored news and media placements',
    },
];

const countries = [
    {
        country: 'Bahamas',
        region: 'Local',
        users: 100000,
        merchants: 600,
        organizers: 120,
        tickets: 1000000,
        subscriptions: 50000,
        marketplace: 250000,
        eats: 500000,
        merchantPay: 2000000,
        wallet: 1500000,
        live: 100000,
        ads: 25000,
        wellness: 150000,
        cookouts: 90000,
        linkup360: 40000,
        coinsPurchased: 250000,
        coinsRedeemed: 90000,
    },
    {
        country: 'Jamaica',
        region: 'Regional',
        users: 280000,
        merchants: 1200,
        organizers: 250,
        tickets: 720000,
        subscriptions: 90000,
        marketplace: 410000,
        eats: 650000,
        merchantPay: 1600000,
        wallet: 950000,
        live: 180000,
        ads: 45000,
        wellness: 190000,
        cookouts: 140000,
        linkup360: 55000,
        coinsPurchased: 420000,
        coinsRedeemed: 170000,
    },
    {
        country: 'Trinidad & Tobago',
        region: 'Regional',
        users: 190000,
        merchants: 900,
        organizers: 190,
        tickets: 610000,
        subscriptions: 70000,
        marketplace: 380000,
        eats: 520000,
        merchantPay: 1350000,
        wallet: 840000,
        live: 160000,
        ads: 39000,
        wellness: 150000,
        cookouts: 120000,
        linkup360: 47000,
        coinsPurchased: 360000,
        coinsRedeemed: 140000,
    },
    {
        country: 'Barbados',
        region: 'Regional',
        users: 70000,
        merchants: 380,
        organizers: 95,
        tickets: 330000,
        subscriptions: 42000,
        marketplace: 190000,
        eats: 260000,
        merchantPay: 720000,
        wallet: 460000,
        live: 85000,
        ads: 21000,
        wellness: 90000,
        cookouts: 65000,
        linkup360: 26000,
        coinsPurchased: 150000,
        coinsRedeemed: 60000,
    },
    {
        country: 'Guyana',
        region: 'Regional',
        users: 130000,
        merchants: 620,
        organizers: 140,
        tickets: 420000,
        subscriptions: 56000,
        marketplace: 270000,
        eats: 360000,
        merchantPay: 980000,
        wallet: 600000,
        live: 120000,
        ads: 28000,
        wellness: 100000,
        cookouts: 82000,
        linkup360: 31000,
        coinsPurchased: 210000,
        coinsRedeemed: 85000,
    },
    {
        country: 'Dominican Republic',
        region: 'Regional',
        users: 220000,
        merchants: 1000,
        organizers: 210,
        tickets: 520000,
        subscriptions: 68000,
        marketplace: 310000,
        eats: 430000,
        merchantPay: 1100000,
        wallet: 710000,
        live: 150000,
        ads: 36000,
        wellness: 130000,
        cookouts: 99000,
        linkup360: 41000,
        coinsPurchased: 290000,
        coinsRedeemed: 120000,
    },
    {
        country: 'United States',
        region: 'International',
        users: 450000,
        merchants: 2400,
        organizers: 410,
        tickets: 1280000,
        subscriptions: 180000,
        marketplace: 820000,
        eats: 0,
        merchantPay: 2500000,
        wallet: 1750000,
        live: 420000,
        ads: 160000,
        wellness: 320000,
        cookouts: 180000,
        linkup360: 120000,
        coinsPurchased: 950000,
        coinsRedeemed: 410000,
    },
    {
        country: 'Canada',
        region: 'International',
        users: 260000,
        merchants: 1400,
        organizers: 260,
        tickets: 840000,
        subscriptions: 130000,
        marketplace: 560000,
        eats: 0,
        merchantPay: 1600000,
        wallet: 1100000,
        live: 300000,
        ads: 110000,
        wellness: 210000,
        cookouts: 120000,
        linkup360: 85000,
        coinsPurchased: 620000,
        coinsRedeemed: 260000,
    },
    {
        country: 'Brazil',
        region: 'International',
        users: 370000,
        merchants: 1900,
        organizers: 350,
        tickets: 960000,
        subscriptions: 150000,
        marketplace: 640000,
        eats: 0,
        merchantPay: 1850000,
        wallet: 1300000,
        live: 360000,
        ads: 130000,
        wellness: 260000,
        cookouts: 140000,
        linkup360: 97000,
        coinsPurchased: 780000,
        coinsRedeemed: 330000,
    },
    {
        country: 'Colombia',
        region: 'International',
        users: 240000,
        merchants: 1300,
        organizers: 240,
        tickets: 690000,
        subscriptions: 105000,
        marketplace: 470000,
        eats: 0,
        merchantPay: 1250000,
        wallet: 860000,
        live: 240000,
        ads: 90000,
        wellness: 180000,
        cookouts: 95000,
        linkup360: 70000,
        coinsPurchased: 520000,
        coinsRedeemed: 210000,
    },
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

const onHeaderFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    // ribbonMetrics is computed, so it will update automatically
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

const unitVolume = (u: any, rs = getFilteredCountries()) => {
    return rs.reduce((s, c: any) => s + (c[u.key] || 0), 0) * getScale();
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
    landmark: Landmark,
    archive: Archive,
    'piggy-bank': PiggyBank,
    coins: CoinsIcon,
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
    coins: ['#D9EC10', '#94A300', 'rgba(217,236,16,.28)'],
};

const coinData = computed(() => {
    const rs = getFilteredCountries();
    const sc = getScale();
    const purchased = rs.reduce((s, c) => s + c.coinsPurchased, 0) * sc;
    const redeemed = rs.reduce((s, c) => s + c.coinsRedeemed, 0) * sc;
    return {
        purchased,
        redeemed,
        outstanding: Math.max(purchased - redeemed, 0),
        linkup: purchased * 0.5,
        creator: purchased * 0.5,
    };
});

const ribbonMetrics = computed(() => {
    const t = getTotals();
    const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
    const net = totalRev - t.cost;
    return {
        gtv: fmt(t.gross),
        linkupRev: fmt(t.platform),
        procPool: fmt(t.bank),
        netProfit: fmt(net),
        users: num(t.users),
        merchants: num(t.merchants),
        organizers: num(t.organizers),
        countries: num(t.countries)
    };
});

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
    <Head title="Business Units" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="business" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Business Units"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="onHeaderFilterChange"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Business Units</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <!-- Individual Business Units -->
                        <div
                            v-for="u in units"
                            :key="u.key"
                            class="unit-card rounded-3xl p-5"
                            :style="{
                                '--main': colorMap[u.key][0],
                                '--dark': colorMap[u.key][1],
                                '--glow': colorMap[u.key][2],
                                '--soft': colorMap[u.key][2],
                            }"
                        >
                            <div class="flex justify-between">
                                <div class="unit-icon grid h-12 w-12 place-items-center rounded-2xl text-white">
                                    <component :is="iconMap[u.icon]" class="h-6 w-6" />
                                </div>
                                <span class="rounded-full bg-white/70 px-3 py-1 text-xs font-black">
                                    {{ u.key === 'ads' || u.key === 'linkup360' ? 'Ad Revenue' : ((u.platformRate + u.bankRate) * 100).toFixed(2) + '% fees' }}
                                </span>
                            </div>
                            <h4 class="mt-4 text-lg font-black">{{ u.name }}</h4>
                            <p class="mt-1 min-h-[42px] text-sm text-slate-600">{{ u.desc }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">GTV</p>
                                    <b>{{ fmt(unitFin(u).gross) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Fees</p>
                                    <b>{{ fmt(unitFin(u).fees) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">LinkUp</p>
                                    <b>{{ fmt(unitFin(u).platform) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Bank</p>
                                    <b>{{ fmt(unitFin(u).bank) }}</b>
                                </div>
                            </div>
                        </div>

                        <!-- LinkUp Coins -->
                        <div
                            class="unit-card rounded-3xl p-5"
                            :style="{
                                '--main': colorMap['coins'][0],
                                '--dark': colorMap['coins'][1],
                                '--glow': colorMap['coins'][2],
                                '--soft': colorMap['coins'][2],
                            }"
                        >
                            <div class="flex justify-between">
                                <div class="unit-icon grid h-12 w-12 place-items-center rounded-2xl text-white">
                                    <CoinsIcon class="h-6 w-6" />
                                </div>
                                <span class="rounded-full bg-white/70 px-3 py-1 text-xs font-black">50/50 Split</span>
                            </div>
                            <h4 class="mt-4 text-lg font-black">LinkUp Coins</h4>
                            <p class="mt-1 min-h-[42px] text-sm text-slate-600">Coins purchased, outstanding, creator share, LinkUp retained share.</p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Purchased</p>
                                    <b>{{ fmt(coinData.purchased) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Outstanding</p>
                                    <b>{{ num(coinData.outstanding) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">LinkUp 50%</p>
                                    <b>{{ fmt(coinData.linkup) }}</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Creator 50%</p>
                                    <b>{{ fmt(coinData.creator) }}</b>
                                </div>
                            </div>
                        </div>

                        <!-- Government Portal (Static/Placeholder like HTML) -->
                        <button
                            @click="alert('In the full app this opens the Government Portal.')"
                            class="unit-card rounded-3xl p-5 text-left transition hover:-translate-y-0.5 hover:shadow-xl"
                            style="--main: #dc2626; --dark: #991b1b; --glow: #fecaca; --soft: #fecaca"
                        >
                            <div class="flex justify-between">
                                <div class="unit-icon grid h-12 w-12 place-items-center rounded-2xl text-white">
                                    <Landmark class="h-6 w-6" />
                                </div>
                                <span class="rounded-full bg-white/70 px-3 py-1 text-xs font-black">G2P Disbursements</span>
                            </div>
                            <h4 class="mt-4 text-lg font-black">Government Portal</h4>
                            <p class="mt-1 min-h-[42px] text-sm text-slate-600">
                                National insurance, pensions & grants paid to citizen wallets. Pre-funded Scotiabank float.
                            </p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Disbursed</p>
                                    <b>$2,450,000</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">LinkUp Rev</p>
                                    <b>$36,750</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Beneficiaries</p>
                                    <b>12,400</b>
                                </div>
                                <div class="rounded-2xl bg-white/75 p-3">
                                    <p class="text-slate-500">Float</p>
                                    <b>$1,200,000</b>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
.unit-card {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: #fff;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
}
.unit-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--soft), rgba(255, 255, 255, 0.94));
    z-index: 0;
}
.unit-card::after {
    content: '';
    position: absolute;
    right: -38px;
    top: -38px;
    width: 130px;
    height: 130px;
    border-radius: 999px;
    background: var(--glow);
    z-index: 0;
}
.unit-card > * {
    position: relative;
    z-index: 1;
}
.unit-icon {
    background: linear-gradient(135deg, var(--main), var(--dark));
    box-shadow: 0 12px 28px var(--glow);
}
</style>
