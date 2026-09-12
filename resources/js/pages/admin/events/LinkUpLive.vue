<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { Megaphone, Radio, Trophy, HeartPulse, ShieldAlert, BarChart3, Users, Gift, TrendingUp, Info, ShoppingBag, Landmark, ArrowLeftRight, Coins, Wallet, History, MonitorSmartphone, Bot, Globe2 } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
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

// Live Data
const liveSessions = [
    { creator: 'Island Queen Live', country: 'Bahamas', session: 'Beauty & Vibes', status: 'Live', viewers: 4200, giftsSent: 1850, coinValue: 42000, rose: 650, heart: 430, drink: 310, teddy: 210, diamond: 250 },
    { creator: 'Kingston Star', country: 'Jamaica', session: 'Dancehall Night', status: 'Completed', viewers: 7600, giftsSent: 3300, coinValue: 88000, rose: 900, heart: 820, drink: 640, teddy: 410, diamond: 530 },
    { creator: 'Soca Boss Live', country: 'Trinidad & Tobago', session: 'Carnival Warmup', status: 'Completed', viewers: 6900, giftsSent: 2950, coinValue: 76000, rose: 740, heart: 690, drink: 590, teddy: 380, diamond: 550 },
    { creator: 'Bajan Beauty', country: 'Barbados', session: 'Island Chat', status: 'Live', viewers: 3100, giftsSent: 1200, coinValue: 28000, rose: 360, heart: 330, drink: 210, teddy: 140, diamond: 160 },
    { creator: 'Diaspora Connect Live', country: 'United States', session: 'Caribbean After Dark', status: 'Completed', viewers: 9800, giftsSent: 5200, coinValue: 140000, rose: 1200, heart: 1300, drink: 900, teddy: 700, diamond: 1100 },
    { creator: 'Toronto LinkUp Live', country: 'Canada', session: 'Weekend Lounge', status: 'Upcoming', viewers: 5400, giftsSent: 2400, coinValue: 61000, rose: 620, heart: 580, drink: 450, teddy: 300, diamond: 450 },
    { creator: 'Caribe Global Live', country: 'Colombia', session: 'Latin Caribbean Mix', status: 'Completed', viewers: 4700, giftsSent: 2100, coinValue: 52000, rose: 510, heart: 490, drink: 380, teddy: 260, diamond: 460 }
];

const liveBattles = [
    { battle: 'Island Queen vs Bajan Beauty', country: 'Bahamas', winner: 'Island Queen Live', coins: 38000, time: '5 min' },
    { battle: 'Kingston Star vs Soca Boss', country: 'Jamaica', winner: 'Kingston Star', coins: 72000, time: '10 min' },
    { battle: 'Diaspora Connect vs Toronto', country: 'United States', winner: 'Diaspora Connect Live', coins: 91000, time: '10 min' }
];

const liveAgencies = [
    { agency: 'Caribbean Creator House', country: 'Bahamas', creators: 18, revenue: 185000, payouts: 92500 },
    { agency: 'Dancehall Digital Agency', country: 'Jamaica', creators: 26, revenue: 242000, payouts: 121000 },
    { agency: 'Diaspora Live Network', country: 'United States', creators: 34, revenue: 390000, payouts: 195000 }
];

const liveCommerce = [
    { product: 'Island Queen T-Shirt', creator: 'Island Queen Live', sales: 420, revenue: 12600, commission: 1890 },
    { product: 'Carnival Wristband Pack', creator: 'Soca Boss Live', sales: 310, revenue: 9300, commission: 1395 },
    { product: 'Caribbean Beauty Bundle', creator: 'Bajan Beauty', sales: 280, revenue: 16800, commission: 2520 }
];

// Reusable Helpers
const userAvatar = (name: string, size = 38) => {
    const parts = (name || '?').trim().split(/\s+/);
    const ini = ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '');
    let h = 0;
    for (let i = 0; i < (name || '').length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
    const hues = [h % 360, (h * 7) % 360];
    const bg = `linear-gradient(135deg,hsl(${hues[0]},70%,55%),hsl(${hues[1]},70%,45%))`;
    return `<div style="width:${size}px;height:${size}px;border-radius:9999px;background:${bg};display:grid;place-items:center;color:#fff;font-weight:900;font-size:${size * 0.38}px;box-shadow:0 2px 6px rgba(0,0,0,.15);flex-shrink:0">${ini.toUpperCase()}</div>`;
};

const userRegion = (country: string) => {
    if (country === 'United States' || country === 'Canada') return 'North America';
    return countries.find(x => x.country === country)?.region || 'Other';
};

// Filtering and Stats
const filteredSessions = computed(() => {
    return liveSessions.filter(s =>
        (filters.value.region === 'All' || userRegion(s.country) === filters.value.region) &&
        (filters.value.country === 'All Countries' || s.country === filters.value.country)
    );
});

const totals = computed(() => {
    const s = getScale();
    return filteredSessions.value.reduce((a, x) => {
        a.coinValue += x.coinValue * s;
        a.viewers += x.viewers * s;
        a.gifts += x.giftsSent * s;
        a.rose += x.rose * s; a.heart += x.heart * s; a.drink += x.drink * s; a.teddy += x.teddy * s; a.diamond += x.diamond * s;
        return a;
    }, { coinValue: 0, viewers: 0, gifts: 0, rose: 0, heart: 0, drink: 0, teddy: 0, diamond: 0 });
});

// Revenue Model
const liveRevenueModel = computed(() => {
    const G = totals.value.coinValue;
    return [
        { name: 'Virtual Gifts', vol: G, linkup: 0.50, split: '50 / 50', who: 'Creator' },
        { name: 'Live Battles', vol: G * 0.25, linkup: 0.50, split: '50 / 50', who: 'Creator' },
        { name: 'Creator Subscriptions', vol: G * 0.35, linkup: 0.30, split: '30 / 70', who: 'Creator' },
        { name: 'Sponsored Live Events', vol: G * 0.18, linkup: 0.85, split: '85 / 15', who: 'Creator' },
        { name: 'Live Advertising', vol: G * 0.22, linkup: 1.00, split: '100% LinkUp', who: '—' },
        { name: 'Live Commerce', vol: G * 0.40, linkup: 0.05, split: '5% commission', who: 'Seller' },
        { name: 'Event Integration', vol: G * 0.15, linkup: 0.06, split: '6% fee', who: 'Organizer' },
        { name: 'Wallet Integration', vol: G * 0.30, linkup: 0.025, split: '2.5% txn fee', who: '—' },
        { name: 'Featured Creator Programs', vol: G * 0.05, linkup: 0.50, split: '50 / 50', who: 'Creator' }
    ].map(s => ({ ...s, linkupRev: s.vol * s.linkup, other: s.vol * (1 - s.linkup) }));
});

const totalLiveRev = computed(() => liveRevenueModel.value.reduce((a, s) => a + s.linkupRev, 0));

// Accordion Ledger
const openGroups = ref<Set<string>>(new Set(['R:Caribbean']));
const toggleGroup = (key: string) => {
    if (openGroups.value.has(key)) openGroups.value.delete(key);
    else openGroups.value.add(key);
};

const groupedSessions = computed(() => {
    const rm: any = {};
    filteredSessions.value.forEach(s => {
        const r = userRegion(s.country);
        const c = s.country;
        if (!rm[r]) rm[r] = {};
        if (!rm[r][c]) rm[r][c] = [];
        rm[r][c].push(s);
    });
    return rm;
});

// Charts
const charts = ref<any>({});
const countryChartRef = ref<HTMLCanvasElement | null>(null);
const splitChartRef = ref<HTMLCanvasElement | null>(null);
const giftChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());
    const s = getScale();

    const countryCtx = countryChartRef.value;
    if (countryCtx) {
        const labels = [...new Set(filteredSessions.value.map(x => x.country))];
        charts.value.country = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: 'Coin Value', data: labels.map(c => filteredSessions.value.filter(x => x.country === c).reduce((a, x) => a + x.coinValue * s, 0)), backgroundColor: '#28A8FF', borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } } }
        });
    }

    const splitCtx = splitChartRef.value;
    if (splitCtx) {
        charts.value.split = new Chart(splitCtx, {
            type: 'doughnut',
            data: {
                labels: ['LinkUp 50%', 'Creator 50%'],
                datasets: [{ data: [totals.value.coinValue * 0.5, totals.value.coinValue * 0.5], backgroundColor: ['#28A8FF', '#00C853'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const giftCtx = giftChartRef.value;
    if (giftCtx) {
        const t = totals.value;
        charts.value.gift = new Chart(giftCtx, {
            type: 'bar',
            data: {
                labels: ['Rose', 'Heart', 'Drink', 'Teddy', 'Diamond'],
                datasets: [{ label: 'Gifts', data: [t.rose, t.heart, t.drink, t.teddy, t.diamond], backgroundColor: '#8B5CF6', borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } } }
        });
    }
};

const renderAll = async () => {
    await nextTick();
    const s = getScale();
    const rs = countries.filter(c => (filters.value.region === 'All' || c.region === filters.value.region) && (filters.value.country === 'All Countries' || c.country === filters.value.country));

    const t = rs.reduce((a, c) => {
        a.gtv += c.tickets + c.subscriptions + c.marketplace + c.eats + c.merchantPay + c.wallet + c.live + c.ads + c.wellness + c.cookouts + c.linkup360;
        a.users += c.users;
        a.merchants += c.merchants;
        a.organizers += c.organizers;
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };

    set('rGTV', fmt(t.gtv * s));
    set('rLinkUp', fmt(t.gtv * s * 0.12));
    set('rBank', fmt(t.gtv * s * 0.03));
    set('rNet', fmt(t.gtv * s * 0.09));
    set('rUsers', num(t.users * s));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(rs.length));
    set('sideGTV', fmt(t.gtv * s));

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
    <Head title="LinkUp Live" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="liveCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="LinkUp Live" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-3xl font-black text-slate-950">LinkUp Live</h3>
                    <button class="rounded-2xl bg-gradient-to-r from-indigo-600 to-pink-500 text-white px-5 py-2.5 font-black text-sm inline-flex items-center gap-2 shadow-lg shadow-indigo-100 transition active:scale-95">
                        <Megaphone class="w-4 h-4" /> Advertise
                    </button>
                </div>

                <!-- Core Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2 shadow-xl shadow-slate-200">
                        <p class="text-slate-300 font-bold">Creator Economy GTV</p>
                        <h3 class="text-5xl font-black mt-2">{{ fmt(totals.coinValue) }}</h3>
                        <p class="text-lime-300 font-bold mt-1">Coins spent inside LinkUp Live</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">LinkUp 50%</p>
                        <h3 class="text-4xl font-black mt-1">{{ fmt(totals.coinValue * 0.5) }}</h3>
                        <p class="text-sky-500 font-bold">Retained revenue</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Creator 50%</p>
                        <h3 class="text-4xl font-black mt-1">{{ fmt(totals.coinValue * 0.5) }}</h3>
                        <p class="text-green-500 font-bold">Creator earnings</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Active Creators</p>
                        <h3 class="text-4xl font-black mt-1">{{ num(new Set(filteredSessions.map(x=>x.creator)).size) }}</h3>
                        <p class="text-purple-500 font-bold">Monetized streams</p>
                    </div>
                </div>

                <!-- Secondary Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
                    <div class="card rounded-3xl p-5 border border-slate-50"><p class="text-slate-500 font-bold text-sm">Live Sessions</p><h3 class="text-3xl font-black mt-1">{{ num(filteredSessions.length * getScale()) }}</h3><p class="text-sky-500 font-bold text-xs">Active + completed</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-50"><p class="text-slate-500 font-bold text-sm">Total Gifts Sent</p><h3 class="text-3xl font-black mt-1">{{ num(totals.gifts) }}</h3><p class="text-green-500 font-bold text-xs">All gift actions</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-50"><p class="text-slate-500 font-bold text-sm">Top Gift</p><h3 class="text-3xl font-black mt-1">Diamond</h3><p class="text-amber-500 font-bold text-xs">Most used gift</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-50"><p class="text-slate-500 font-bold text-sm">Avg. Session Value</p><h3 class="text-3xl font-black mt-1">{{ fmt(filteredSessions.length ? totals.coinValue / filteredSessions.length : 0) }}</h3><p class="text-purple-500 font-bold text-xs">Per session</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-50"><p class="text-slate-500 font-bold text-sm">Monthly Growth</p><h3 class="text-3xl font-black mt-1 text-green-600">38%</h3><p class="text-green-500 font-bold text-xs">Creator growth</p></div>
                </div>

                <!-- Revenue Model Table -->
                <div class="card rounded-3xl p-6 border-slate-100 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div><h3 class="text-xl font-black text-slate-800">LinkUp Live — Revenue Model</h3><p class="text-slate-500 text-sm font-medium">9 revenue streams. Gifts & battles split <b>50 / 50</b>; subscriptions split <b>70 / 30</b>.</p></div>
                        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3"><span class="text-sm font-bold text-slate-500">Live Revenue: </span><b class="text-green-600 text-xl font-black">{{ fmt(totalLiveRev) }}</b></div>
                    </div>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-50">
                                <tr><th class="py-3 px-2">Revenue Stream</th><th>Volume</th><th>Split</th><th>LinkUp Revenue</th><th>Creator / Partner</th></tr>
                            </thead>
                            <tbody class="text-sm font-bold text-slate-700">
                                <tr v-for="s in liveRevenueModel" :key="s.name" class="border-b border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-4 px-2">{{ s.name }}</td>
                                    <td>{{ fmt(s.vol) }}</td>
                                    <td class="text-slate-500 text-xs">{{ s.split }}</td>
                                    <td class="text-green-600">{{ fmt(s.linkupRev) }}</td>
                                    <td class="text-slate-400">{{ s.who === '—' ? '—' : fmt(s.other) + ' → ' + s.who }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 rounded-2xl bg-slate-50 p-5 text-sm text-slate-600 border border-slate-100 leading-relaxed italic">
                        <b>Platform Economics:</b> At <b>$1,000,000</b> monthly gift volume, creators receive <b>$500,000</b> and LinkUp receives <b>$500,000</b> — <i>before</i> commerce and ad revenues.
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
                    <div class="2xl:col-span-2 card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-1">Creator Economy by Country</h3>
                        <p class="text-slate-500 text-sm mb-6">Live coin value and LinkUp retained share by jurisdiction.</p>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="countryChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-1 text-center">Live 50 / 50 Split</h3>
                        <p class="text-slate-500 text-sm mb-6 text-center">For every $100 in live gifts, LinkUp keeps $50.</p>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="splitChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Grids Section -->
                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2"><Trophy class="w-5 h-5 text-amber-500" /> Creator Leaderboard</h3>
                        <div class="space-y-3">
                            <div v-for="s in filteredSessions.sort((a,b)=>b.coinValue-a.coinValue).slice(0,5)" :key="s.creator" class="rounded-2xl bg-white border border-slate-100 p-4 flex justify-between items-center shadow-sm hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div v-html="userAvatar(s.creator, 36)"></div>
                                    <div><b class="text-slate-900">{{ s.creator }}</b><p class="text-[10px] text-slate-400 font-bold uppercase">{{ s.country }} • {{ s.session }}</p></div>
                                </div>
                                <div class="text-right">
                                    <b class="text-lg text-slate-900 tracking-tight">{{ fmt(s.coinValue * getScale()) }}</b>
                                    <p class="text-[10px] text-green-600 font-black uppercase">Creator {{ fmt(s.coinValue * getScale() * 0.5) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2"><Gift class="w-5 h-5 text-purple-500" /> Gift Store Performance</h3>
                        <div class="relative h-[280px] w-full">
                            <canvas ref="giftChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Data Grids -->
                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-6">Creator Earnings Center</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="x in [['Today',totals.coinValue/30*0.5],['This Week',totals.coinValue/4*0.5],['This Month',totals.coinValue*0.5],['Lifetime',totals.coinValue*18*0.5]]" :key="x[0]" class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">{{ x[0] }}</p>
                                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ fmt(Number(x[1])) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-6">Live Treasury</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="x in [['Gross Coin Sales',totals.coinValue],['Outstanding Coins',totals.coinValue*0.2],['Creator Cashouts',totals.coinValue*0.42],['Cashout Queue',totals.coinValue*0.08]]" :key="x[0]" class="rounded-3xl bg-indigo-50/30 p-5 border border-indigo-100/50">
                                <p class="text-indigo-400 text-[10px] font-black uppercase tracking-widest">{{ x[0] }}</p>
                                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ fmt(Number(x[1])) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Battles & Goals -->
                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-6">Live Battles</h3>
                        <div class="space-y-3">
                            <div v-for="b in liveBattles" :key="b.battle" class="rounded-3xl bg-rose-50/50 border border-rose-100 p-5 group hover:bg-rose-50 transition">
                                <div class="flex justify-between items-center mb-2"><b>{{ b.battle }}</b><span class="text-[10px] font-black bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full uppercase">{{ b.time }}</span></div>
                                <p class="text-sm text-slate-600">Winner: <b class="text-slate-900">{{ b.winner }}</b> • Coins: <b class="text-slate-900">{{ num(b.coins) }}</b> • Value: <b class="text-green-600">{{ fmt(b.coins) }}</b></p>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-6">Live Goals</h3>
                        <div class="space-y-5">
                            <div v-for="s in filteredSessions.slice(0, 3)" :key="s.creator">
                                <div class="flex justify-between items-center text-sm mb-1.5 font-bold text-slate-700"><span>{{ s.creator }}</span><span>{{ Math.round((s.coinValue / (s.coinValue*1.3)) * 100) }}%</span></div>
                                <div class="h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200"><div class="h-full bg-sky-400 shadow-[0_0_8px_rgba(56,189,248,0.4)]" :style="{ width: '76%' }"></div></div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mt-2">Goal: {{ fmt(s.coinValue*1.3) }} • Current: {{ fmt(s.coinValue) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- dynamic data grids -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6">Creator Levels</h3>
                        <div class="space-y-3">
                            <div v-for="s in filteredSessions.sort((a,b)=>b.coinValue-a.coinValue).slice(0,5)" :key="s.creator" class="rounded-2xl bg-slate-50 p-4 flex justify-between items-center transition hover:bg-white hover:shadow-sm">
                                <b class="text-slate-700">{{ s.creator }}</b>
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="s.coinValue > 80000 ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-100 text-slate-500 border-slate-200'">{{ s.coinValue > 80000 ? 'Legend' : 'Diamond' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6">Agency Dashboard</h3>
                        <div class="space-y-3">
                            <div v-for="a in liveAgencies" :key="a.agency" class="rounded-2xl bg-slate-50 p-4 transition hover:bg-white hover:shadow-sm border border-transparent hover:border-slate-100">
                                <div class="flex justify-between font-black text-slate-800"><span>{{ a.agency }}</span><span class="text-xs">{{ a.country }}</span></div>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-tight">{{ a.creators }} creators • {{ fmt(a.revenue) }} rev • {{ fmt(a.payouts) }} paid</p>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6">Fan Clubs</h3>
                        <div class="space-y-3">
                            <div v-for="s in filteredSessions.slice(0, 3)" :key="s.creator" class="rounded-3xl bg-purple-50/40 border border-purple-100 p-5 group hover:bg-white hover:shadow-md transition">
                                <div class="flex justify-between items-center mb-1"><b class="text-slate-900">{{ s.creator }} Fan Club</b><span class="text-xs font-black text-purple-600">{{ num(s.viewers * 0.12) }} fans</span></div>
                                <p class="text-[10px] text-slate-500 font-medium">VIP chat • badges • exclusive lives</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2"><ShoppingBag class="w-5 h-5 text-emerald-500" /> Live Commerce</h3>
                        <div class="space-y-3">
                            <div v-for="p in liveCommerce" :key="p.product" class="rounded-3xl bg-green-50/40 border border-green-100 p-5 group hover:bg-white transition hover:shadow-md">
                                <div class="flex justify-between items-center mb-1"><b class="text-slate-800">{{ p.product }}</b><b class="text-green-600 text-lg">{{ fmt(p.revenue) }}</b></div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Creator: {{ p.creator }} • {{ num(p.sales) }} sales • Comm: {{ fmt(p.commission) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-50">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2"><ShieldAlert class="w-5 h-5 text-rose-500" /> Live Moderation Center</h3>
                        <div class="space-y-3">
                            <div v-for="x in [['Reports Pending','18','Review chat and behavior'],['Warnings Issued','42','Community warnings'],['Suspensions','4','Temporary restrictions'],['High Risk Streams','2','Needs moderator review']]" :key="x[0]" class="rounded-2xl bg-amber-50/40 border border-amber-100 p-4 flex justify-between items-center group hover:bg-white transition">
                                <div><b class="text-slate-800 block leading-tight">{{ x[0] }}</b><p class="text-[10px] text-slate-400 font-bold mt-0.5">{{ x[2] }}</p></div>
                                <b class="text-xl font-black text-slate-700">{{ x[1] }}</b>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black mb-6">Live Analytics Center</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div v-for="x in [['Watch Time','18,420 hrs'],['Peak Viewers','9.8k'],['Avg. Viewers','4.2k'],['Coins Per Viewer','12'],['Followers Gained','1.5k'],['Stickiness','38%']]" :key="x[0]" class="rounded-3xl bg-slate-50 p-5 border border-slate-100 text-center">
                                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ x[0] }}</p>
                                <b class="text-2xl font-black text-slate-800">{{ x[1] }}</b>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 border-sky-100 bg-sky-50/20 shadow-inner">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2"><Bot class="w-6 h-6 text-sky-500" /> AI Creator Coach</h3>
                        <div class="space-y-4">
                            <div v-for="x in [['Best stream time','8 PM to 11 PM local country time shows strongest gifts.'],['Retention insight','Gift activity rises when creators respond directly to viewers.'],['Revenue suggestion','Push battles between top creators to increase coin spend.']]" :key="x[0]" class="rounded-3xl bg-white p-5 border border-sky-100 shadow-sm transition hover:shadow-md">
                                <b class="text-sky-700 block mb-1">{{ x[0] }}</b>
                                <p class="text-sm text-slate-600 font-medium leading-relaxed">{{ x[1] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Country Heat Map (Updated to match image) -->
                <div class="card rounded-3xl p-6 border-slate-100 shadow-sm">
                    <h3 class="text-xl font-black mb-6 text-slate-900">LinkUp Live Country Heat Map</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div v-for="c in [...new Set(filteredSessions.map(x=>x.country))]" :key="c" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:bg-white hover:shadow-md transition duration-300">
                            <p class="text-slate-500 font-bold text-xs mb-1 tracking-tight">{{ c }}</p>
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ fmt(filteredSessions.find(x=>x.country===c)!.coinValue * getScale()) }}</h4>
                            <p class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-tighter">
                                {{ num(filteredSessions.find(x=>x.country===c)!.viewers * getScale()) }} viewers • 1 creators
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ledger -->
                <div class="card rounded-3xl p-6 border-slate-100">
                    <h3 class="text-xl font-black mb-6">LinkUp Live Activity Ledger</h3>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-50">
                                <tr><th class="py-4 px-2">Creator</th><th>Country</th><th>Session</th><th class="text-center">Viewers</th><th class="text-center">Gifts</th><th class="text-right">Coin Value</th><th class="text-right">LinkUp 50%</th><th class="text-right">Creator 50%</th><th class="text-center">Status</th></tr>
                            </thead>
                            <tbody class="text-sm">
                                <template v-for="(cs, r) in groupedSessions" :key="r">
                                    <tr><td colspan="9" @click="toggleGroup('R:'+r)" class="cursor-pointer py-3 px-4 font-black text-white bg-gradient-to-r from-slate-800 to-slate-600 rounded-xl mb-1">{{ openGroups.has('R:'+r) ? '▾' : '▸' }} 🌎 {{ r }}</td></tr>
                                    <template v-if="openGroups.has('R:'+r)">
                                        <template v-for="(items, c) in cs" :key="c">
                                            <tr v-for="s in items" :key="s.creator" class="border-b border-slate-50 hover:bg-slate-50 transition align-middle">
                                                <td class="py-5 px-2">
                                                    <div class="flex items-center gap-3">
                                                        <div v-html="userAvatar(s.creator, 32)"></div>
                                                        <b class="text-slate-900">{{ s.creator }}</b>
                                                    </div>
                                                </td>
                                                <td class="text-xs font-bold text-slate-600">{{ s.country }}</td>
                                                <td class="text-xs font-medium text-slate-500 italic">{{ s.session }}</td>
                                                <td class="text-center font-bold">{{ num(s.viewers * getScale()) }}</td>
                                                <td class="text-center font-bold">{{ num(s.giftsSent * getScale()) }}</td>
                                                <td class="text-right font-black text-slate-900">{{ fmt(s.coinValue * getScale()) }}</td>
                                                <td class="text-right font-bold text-sky-600">{{ fmt(s.coinValue * getScale() * 0.5) }}</td>
                                                <td class="text-right font-bold text-green-600">{{ fmt(s.coinValue * getScale() * 0.5) }}</td>
                                                <td class="text-center"><span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-tighter" :class="s.status === 'Live' ? 'bg-sky-50 text-sky-600 border border-sky-100' : 'bg-green-50 text-green-600 border border-green-100'">{{ s.status }}</span></td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
