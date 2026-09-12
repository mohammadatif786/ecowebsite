<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Bot } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model (Verbatim from HTML context)
const SCOTIA_SHARE = 0.4;

const units = [
    { key: 'tickets', name: 'Ticket Sales', icon: 'ticket', platformRate: 0.065, bankRate: 0, costRate: 0.01, desc: 'Tickets, VIP, drink tickets, event add-ons' },
    { key: 'subscriptions', name: 'Subscriptions', icon: 'badge-dollar-sign', platformRate: 1, bankRate: 0, costRate: 0.04, desc: 'Premium users and paid plans' },
    { key: 'marketplace', name: 'Marketplace', icon: 'shopping-bag', platformRate: 0.05, bankRate: 0, costRate: 0.01, desc: 'Seller fees and commissions' },
    { key: 'eats', name: 'LinkUp Eats', icon: 'utensils', platformRate: 0.075, bankRate: 0, costRate: 0.025, desc: 'QR menus, ordering, pickup, delivery' },
    { key: 'merchantPay', name: 'Merchant Pay', icon: 'credit-card', platformRate: 0.02, bankRate: 0, costRate: 0.007, desc: 'QR payments, card, ACH, bill pay' },
    { key: 'wallet', name: 'Wallet & Money Movement', icon: 'wallet', platformRate: 0.025, bankRate: 0.0175, costRate: 0.008, desc: 'Cash-in (top-up), cash-out (withdrawal), transfers' },
    { key: 'live', name: 'LinkUp Live', icon: 'radio', platformRate: 0.5, bankRate: 0, costRate: 0.08, desc: 'Live coins, gifts, creators' },
    { key: 'ads', name: 'Advertising Revenue', icon: 'megaphone', platformRate: 1, bankRate: 0, costRate: 0.12, desc: 'Swipe ads, email ads, promoted posts' },
    { key: 'wellness', name: 'Wellness & Spa', icon: 'sparkles', platformRate: 0.0675, bankRate: 0, costRate: 0.015, desc: 'Bookings and appointment marketplace' },
    { key: 'cookouts', name: 'Cookouts', icon: 'flame', platformRate: 0.0675, bankRate: 0, costRate: 0.015, desc: 'Food events and vendor sales' },
    { key: 'linkup360', name: 'LinkUp 360 News Ads', icon: 'newspaper', platformRate: 1, bankRate: 0, costRate: 0.15, desc: 'Sponsored news and media placements' },
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

const unitFin = (u: any, rs = getFilteredCountries()) => {
    const gross = rs.reduce((s, c: any) => s + (c[u.key] || 0), 0) * getScale();
    const platform = gross * u.platformRate;
    const bank = gross * u.bankRate;
    const cost = gross * u.costRate;
    return { gross, platform, bank, fees: platform + bank, cost, net: platform - cost };
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

// AI Engine Logic
const aiQuestion = ref('');
const aiAnswer = ref({ title: 'Ask me anything about LinkUp’s numbers.', body: '', note: '' });

const aiDataContext = () => {
    const t = getTotals();
    const rs = getFilteredCountries();
    const sc = getScale();

    const purchased = rs.reduce((s, c) => s + c.coinsPurchased, 0) * sc;
    const redeemed = rs.reduce((s, c) => s + c.coinsRedeemed, 0) * sc;
    const ct = { purchased, redeemed, outstanding: Math.max(purchased - redeemed, 0), linkup: purchased * 0.5, creator: purchased * 0.5 };

    const countryRanks = rs.map(c => ({ country: c.country, region: c.region, ...countryFin(c) })).sort((a, b) => b.gross - a.gross);
    const productRanks = units.map(u => ({ name: u.name, key: u.key, ...unitFin(u) })).sort((a, b) => b.gross - a.gross);

    return { t, ct, countryRanks, productRanks };
};

const askAI = (preset?: string) => {
    if (preset) aiQuestion.value = preset;
    const q = (aiQuestion.value || '').toLowerCase().trim();
    const { t, ct, countryRanks, productRanks } = aiDataContext();

    if (!q) {
        aiAnswer.value = { title: 'Ask me a LinkUp business question.', body: 'Example: “Which country made the most money?” or “How much did LinkUp retain from coins?”', note: '' };
        return;
    }

    if (q.includes('most revenue') || q.includes('top country') || q.includes('best country') || (q.includes('country') && q.includes('money'))) {
        const top = countryRanks[0] || { country: 'No data', gross: 0, platform: 0, bank: 0 };
        aiAnswer.value = {
            title: 'Top Revenue Country',
            body: `${top.country} generated the most gross transaction volume with <b>${fmt(top.gross)}</b>. LinkUp revenue from this country is <b>${fmt(top.platform)}</b>, and bank revenue is <b>${fmt(top.bank)}</b>.`,
            note: `Top 5 countries:<br>${countryRanks.slice(0, 5).map((x, i) => `${i + 1}. ${x.country}: ${fmt(x.gross)} GTV`).join('<br>')}`
        };
        return;
    }

    if (q.includes('coin')) {
        aiAnswer.value = {
            title: 'LinkUp Coins Treasury',
            body: `Coins purchased equal <b>${fmt(ct.purchased)}</b>. LinkUp retains <b>${fmt(ct.linkup)}</b> under the 50/50 split, while the creator side receives <b>${fmt(ct.creator)}</b>. There are <b>${num(ct.outstanding)}</b> coins still outstanding in the system.`,
            note: `This is important because outstanding coins should be tracked like an ecosystem balance.`
        };
        return;
    }

    if (q.includes('user') || q.includes('rpu') || q.includes('customer')) {
        const rpuValue = t.users ? t.platform / t.users : 0;
        aiAnswer.value = {
            title: 'User Intelligence',
            body: `The selected view has <b>${num(t.users)}</b> users. LinkUp revenue per user is approximately <b>${fmt(rpuValue)}</b>. Verified users are estimated at <b>${num(t.users * 0.72)}</b>, and premium users are estimated at <b>${num(t.users * 0.28)}</b>.`,
            note: `RPU matters because it helps estimate user value and investor valuation.`
        };
        return;
    }

    if (q.includes('forecast') || q.includes('projection') || q.includes('5 year')) {
        aiAnswer.value = {
            title: 'Forecast',
            body: `Based on the current selected period, a simple 12-month LinkUp revenue projection is <b>${fmt(t.platform * 12 * 1.35)}</b>. A 5-year scale projection is <b>${fmt(t.platform * 60 * 2.4)}</b>.`,
            note: `This is a prototype projection using growth multipliers. In production, it should use real monthly cohorts and retention data.`
        };
        return;
    }

    if (q.includes('profit') || q.includes('net')) {
        aiAnswer.value = {
            title: 'Net Profit',
            body: `Estimated net profit is <b>${fmt(t.net)}</b>. Gross transaction volume is <b>${fmt(t.gross)}</b>, LinkUp revenue is <b>${fmt(t.platform)}</b>, bank revenue is <b>${fmt(t.bank)}</b>, and estimated costs are <b>${fmt(t.cost)}</b>.`,
            note: ''
        };
        return;
    }

    if (q.includes('product') || q.includes('business unit')) {
        const top = productRanks[0] || { name: 'N/A', gross: 0 };
        aiAnswer.value = {
            title: 'Top Product Lines',
            body: `The highest-volume product is <b>${top.name}</b> with <b>${fmt(top.gross)}</b> in GTV.`,
            note: `Top products:<br>${productRanks.slice(0, 5).map((x, i) => `${i + 1}. ${x.name}: ${fmt(x.gross)} GTV, ${fmt(x.platform)} LinkUp revenue`).join('<br>')}`
        };
        return;
    }

    aiAnswer.value = {
        title: 'LinkUp Executive Summary',
        body: `For the selected view, LinkUp has <b>${fmt(t.gross)}</b> in GTV, <b>${fmt(t.platform)}</b> in LinkUp revenue, <b>${fmt(t.bank)}</b> in bank revenue, and <b>${fmt(t.net)}</b> in estimated net profit.`,
        note: `Ask a more specific question about countries, coins, users, bank revenue, or forecasting.`
    };
};

const quickQuestions = [
    'Which country made the most money?',
    'How much did LinkUp retain from coins?',
    'How much did Merchant Pay generate?',
    'What is the forecast?',
    'What is the risk score?'
];

const insightFeed = computed(() => {
    const { t, ct, countryRanks, productRanks } = aiDataContext();
    const topCountry = countryRanks[0] || { country: 'N/A', gross: 0 };
    const topProduct = productRanks[0] || { name: 'N/A', gross: 0 };

    return [
        { title: `${topCountry.country} is leading revenue`, desc: `${fmt(topCountry.gross)} in GTV for selected period.` },
        { title: `${topProduct.name} leads product volume`, desc: `${fmt(topProduct.gross)} in GTV.` },
        { title: `Coins retained revenue`, desc: `LinkUp retains ${fmt(ct.linkup)} from coin purchases.` },
        { title: `Bank partner value`, desc: `Bank revenue is ${fmt(t.bank)}.` },
        { title: `Estimated valuation signal`, desc: `Simple value estimate is ${fmt(t.platform * 60)}.` }
    ];
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
    <Head title="Ask AI" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="assistant" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Ask AI" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl p-6 2xl:col-span-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="mb-2 text-xl font-black">Ask LinkUp AI</h3>
                                <p class="mb-4 text-slate-500">
                                    This assistant reads the live dashboard data in this prototype and answers questions about revenue, users, coins, merchants, and countries.
                                </p>
                            </div>
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 uppercase">Active Local AI</span>
                        </div>
                        <div class="flex flex-col gap-2 md:flex-row">
                            <input
                                v-model="aiQuestion"
                                @keydown.enter="askAI()"
                                class="flex-1 rounded-2xl border border-slate-200 px-4 py-3"
                                placeholder="Ask: Which country made the most money? How much did coins make?"
                            />
                            <button @click="askAI()" class="rounded-2xl bg-slate-950 px-5 py-3 font-bold text-white">Ask</button>
                        </div>
                        <div id="aiAnswer" class="mt-5 rounded-3xl bg-slate-50 p-6">
                            <div class="mb-2 text-lg font-black">{{ aiAnswer.title }}</div>
                            <div class="font-semibold leading-relaxed text-slate-700" v-html="aiAnswer.body"></div>
                            <div v-if="aiAnswer.note" class="mt-4 text-sm text-slate-500" v-html="aiAnswer.note"></div>
                        </div>
                    </div>

                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Fast Questions</h3>
                        <div class="space-y-2">
                            <button
                                v-for="q in quickQuestions"
                                :key="q"
                                @click="askAI(q)"
                                class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-left font-bold transition hover:bg-sky-50"
                            >
                                {{ q }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">AI Insight Feed</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div v-for="item in insightFeed" :key="item.title" class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
                            <b class="text-slate-800">{{ item.title }}</b>
                            <p class="mt-1 text-sm text-slate-600">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
