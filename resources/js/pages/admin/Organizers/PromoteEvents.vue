<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Megaphone, Wallet, CreditCard, Landmark, Check, X, Info, Plus } from 'lucide-vue-next';
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

// Promotion Tiers
const promoTiers = ref([
    { key: 'starter', name: 'Starter', price: 49, impr: 50000, placements: 'Vibes Feed + event listing boost', perks: 'Standard rotation' },
    { key: 'pro', name: 'Pro', price: 149, impr: 250000, placements: '+ Push + Email + C360 News', perks: 'Priority slots · featured event' },
    { key: 'headliner', name: 'Headliner', price: 399, impr: 1000000, placements: '+ In-App + Live + homepage feature', perks: 'Top slot · full targeting · verified' }
]);

const adsPlacements = [
    { key: 'vibes_feed', name: 'LinkUp Vibes — Main Feed', cpm: 2.50 },
    { key: 'email_blast', name: 'LinkUp Weekly Email Blast', cpm: 5.00 },
    { key: 'push_target', name: 'Targeted Push Notification', cpm: 8.50 },
    { key: 'news_360', name: 'C360 News Sponsored', cpm: 3.20 }
];

const durations = [
    { key: '7d', name: '7 Days', days: 7, mult: 1.0 },
    { key: '14d', name: '14 Days', days: 14, mult: 0.95 },
    { key: '30d', name: '30 Days', days: 30, mult: 0.85 }
];

// Segment Logic
const segments = [
    { key: 'organizers', name: 'Event Organizers', label: 'organizer' },
    { key: 'restaurants', name: 'Restaurants', label: 'restaurant' },
    { key: 'merchants', name: 'Merchants', label: 'merchant' },
    { key: 'marketplace', name: 'Marketplace Sellers', label: 'seller' },
    { key: 'live', name: 'Live & Podcasts', label: 'creator' }
];

const currentSeg = ref('organizers');

// Data for different segments (Mocked as in HTML)
const segmentData: Record<string, any[]> = {
    organizers: [
        { name: 'Island Vibes Events', country: 'Bahamas', plan: 'pro', usage: 140000 },
        { name: 'Old School Events', country: 'Bahamas', plan: 'starter', usage: 12000 },
        { name: 'Miami Caribbean Fest', country: 'United States', plan: 'headliner', usage: 620000 },
        { name: 'Carnival Link Caribbean', country: 'Trinidad & Tobago', plan: null, usage: 0 }
    ],
    restaurants: [
        { name: 'Bahama Breeze Grill', country: 'Bahamas', plan: 'pro', usage: 85000 },
        { name: 'Kingston Jerk Hut', country: 'Jamaica', plan: 'starter', usage: 5000 }
    ],
    merchants: [
        { name: 'SuperValue Nassau', country: 'Bahamas', plan: 'headliner', usage: 980000 },
        { name: 'NCB Jamaica', country: 'Jamaica', plan: 'pro', usage: 210000 }
    ],
    marketplace: [
        { name: 'Caribbean Beauty Store', country: 'Bahamas', plan: 'starter', usage: 45000 },
        { name: 'Medellín Moda', country: 'Colombia', plan: null, usage: 0 }
    ],
    live: [
        { name: 'Island Queen Live', country: 'Bahamas', plan: 'pro', usage: 180000 },
        { name: 'Yard Vibes Radio', country: 'Jamaica', plan: 'headliner', usage: 750000 }
    ]
};

const advertisers = computed(() => segmentData[currentSeg.value] || []);

const promoBilling = ref([
    { who: 'Island Vibes Events', label: 'Pro plan (monthly)', amount: 149, method: 'Wallet', ts: '2026-06-01T08:00:00' },
    { who: 'Miami Caribbean Fest', label: 'Headliner plan (monthly)', amount: 399, method: 'Card', ts: '2026-05-28T14:30:00' },
    { who: 'Kingston Night Market', label: 'Vibes Feed promo (7 Days)', amount: 75, method: 'Wallet', ts: '2026-06-02T10:15:00' }
]);

const selectedAdvertiser = ref('');
const selectedPlacement = ref('vibes_feed');
const selectedDuration = ref('7d');

// Sync selected advertiser when segment changes
const setSeg = (key: string) => {
    currentSeg.value = key;
    if (advertisers.value.length > 0) {
        selectedAdvertiser.value = advertisers.value[0].name;
    }
};

const quote = computed(() => {
    const p = adsPlacements.find(x => x.key === selectedPlacement.value);
    const d = durations.find(x => x.key === selectedDuration.value);
    if (!p || !d) return { total: 0, impr: 0 };
    const baseAmount = p.cpm * 30; // Mock base
    const total = baseAmount * d.mult;
    return { total, impr: 25000 * d.days / 7 }; // Mock impr
});

// Stats
const stats = computed(() => {
    // Overall stats (across all segments for context)
    const allActive = Object.values(segmentData).flat().filter(a => a.plan);
    const mrr = allActive.reduce((s, a) => {
        const t = promoTiers.value.find(x => x.key === a.plan);
        return s + (t ? t.price : 0);
    }, 0);

    const currentActive = advertisers.value.filter(a => a.plan);
    const totalUsed = currentActive.reduce((s, a) => s + a.usage, 0);
    const totalAllowance = currentActive.reduce((s, a) => {
        const t = promoTiers.value.find(x => x.key === a.plan);
        return s + (t ? t.impr : 0);
    }, 0);

    return {
        subscribers: allActive.length,
        mrr,
        overage: promoBilling.value.filter(b => !b.label.includes('plan')).reduce((s, b) => s + b.amount, 0),
        usagePct: totalAllowance ? Math.round(totalUsed / totalAllowance * 100) : 0,
        totalUsed,
        totalAllowance
    };
});

// Modal Logic
const showPlanEditor = ref(false);
const showPayModal = ref(false);
const payForm = ref({ amount: 0, label: '', who: '', method: 'Wallet' });

const openPay = (amount: number, label: string, who: string) => {
    payForm.value = { amount, label, who, method: 'Wallet' };
    showPayModal.value = true;
};

const confirmPayment = () => {
    promoBilling.value.unshift({
        who: payForm.value.who,
        label: payForm.value.label,
        amount: payForm.value.amount,
        method: payForm.value.method,
        ts: new Date().toISOString()
    });
    showPayModal.value = false;
    alert(`✓ Paid by ${payForm.value.method} — ${fmt(payForm.value.amount)}`);
};

const renderAll = async () => {
    await nextTick();
    const rs = countries.filter(c => (filters.value.region === 'All' || c.region === filters.value.region) && (filters.value.country === 'All Countries' || c.country === filters.value.country));
    const gross = rs.reduce((s, c) => s + c.tickets, 0) * getScale();
    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('sideGTV', fmt(gross));
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    if (advertisers.value.length > 0) selectedAdvertiser.value = advertisers.value[0].name;
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Promote & Advertise" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="organizerPromoteCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Promote & Advertise" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div>
                    <h3 class="text-3xl font-black text-slate-950">Promote & Advertise</h3>
                    <p class="text-slate-500">One promote engine for every vertical — organizers, restaurants, merchants, marketplace sellers & live creators. Flat-rate monthly <b>plans</b> include an ad allowance; buy extra reach pay-as-you-go at <b>Ad Pricing</b> rates, paid in-system.</p>
                </div>

                <div id="promoSegTabs" class="flex flex-wrap gap-2">
                    <button
                        v-for="s in segments"
                        :key="s.key"
                        @click="setSeg(s.key)"
                        class="rounded-full px-4 py-1.5 text-sm font-black transition"
                        :class="currentSeg === s.key ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        {{ s.name }} ({{ segmentData[s.key]?.filter(a => a.plan).length || 0 }})
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="card rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">Subscribers</p>
                        <h3 class="text-4xl font-black">{{ stats.subscribers }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">Subscription MRR</p>
                        <h3 class="text-3xl font-black text-green-600">{{ fmt(stats.mrr) }}</h3>
                        <p class="text-xs text-slate-500 font-bold">{{ fmt(stats.mrr * 12) }}/yr</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">Overage Revenue</p>
                        <h3 class="text-3xl font-black text-indigo-600">{{ fmt(stats.overage) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">Allowance Used</p>
                        <h3 class="text-3xl font-black">{{ stats.usagePct }}%</h3>
                        <p class="text-xs text-slate-500 font-bold">{{ (stats.totalUsed / 1000).toFixed(0) }}k / {{ (stats.totalAllowance / 1000).toFixed(0) }}k impr</p>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black">Promoter Plans</h3>
                        <button @click="showPlanEditor = !showPlanEditor" class="rounded-2xl bg-white border border-slate-200 px-4 py-2 font-black text-sm hover:bg-slate-50">Manage Plans</button>
                    </div>
                    <div id="promoTiers" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="t in promoTiers" :key="t.key" class="card rounded-3xl p-5 border-2 transition hover:shadow-lg" :class="t.key === 'pro' ? 'border-indigo-400 bg-indigo-50/10' : 'border-slate-100'">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xl font-black">{{ t.name }}</h4>
                                <span v-if="t.key === 'pro'" class="rounded-full bg-indigo-50 text-indigo-700 px-2 py-0.5 text-xs font-black">Popular</span>
                            </div>
                            <p class="text-3xl font-black mt-1">{{ fmt(t.price) }}<span class="text-sm text-slate-400 font-bold">/mo</span></p>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">{{ t.placements }}</p>
                            <p class="text-xs font-black text-slate-700 mt-1">{{ (t.impr / 1000).toLocaleString() }}k impressions/mo</p>
                            <p class="text-xs text-slate-500 mt-1 italic">{{ t.perks }}</p>
                            <p class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-wider">{{ Object.values(segmentData).flat().filter(a => a.plan === t.key).length }} subscriber(s)</p>
                        </div>
                    </div>

                    <div v-if="showPlanEditor" class="mt-5 border-t border-slate-100 pt-5 animate-in slide-in-from-top-4 duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-black">Edit Plans</h4>
                            <button class="rounded-2xl bg-slate-950 text-white px-4 py-2 font-black text-sm">+ Add Plan</button>
                        </div>
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase text-slate-500 font-bold">
                                    <tr>
                                        <th class="py-2">Name</th>
                                        <th>Price /mo</th>
                                        <th>Impressions /mo</th>
                                        <th>Placements</th>
                                        <th>Perks</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(t, i) in promoTiers" :key="t.key" class="border-t">
                                        <td class="py-2"><input v-model="t.name" class="w-28 rounded-lg border border-slate-200 px-2 py-1 font-bold"></td>
                                        <td><div class="flex items-center gap-1">$<input v-model.number="t.price" type="number" class="w-20 rounded-lg border border-slate-200 px-2 py-1 font-bold"></div></td>
                                        <td><input v-model.number="t.impr" type="number" class="w-28 rounded-lg border border-slate-200 px-2 py-1 font-bold"></td>
                                        <td><input v-model="t.placements" class="w-48 rounded-lg border border-slate-200 px-2 py-1"></td>
                                        <td><input v-model="t.perks" class="w-40 rounded-lg border border-slate-200 px-2 py-1"></td>
                                        <td><button class="rounded-lg bg-rose-100 text-rose-700 px-2 py-1 text-xs font-black">Delete</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h3 class="text-xl font-black mb-4">Subscribers ({{ segments.find(s => s.key === currentSeg)?.name }})</h3>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500 font-bold">
                                <tr>
                                    <th class="py-2">{{ segments.find(s => s.key === currentSeg)?.label.charAt(0).toUpperCase() }}{{ segments.find(s => s.key === currentSeg)?.label.slice(1) }}</th>
                                    <th>Plan</th>
                                    <th>Allowance Used</th>
                                    <th>Set Plan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="a in advertisers" :key="a.name" class="border-t hover:bg-slate-50 transition">
                                    <td class="py-4">
                                        <b class="text-slate-800">{{ a.name }}</b>
                                        <p class="text-xs text-slate-400">{{ a.country }}</p>
                                    </td>
                                    <td>
                                        <span v-if="a.plan" class="rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-[10px] font-black border border-indigo-100 uppercase tracking-tighter">
                                            {{ promoTiers.find(t => t.key === a.plan)?.name }} - {{ fmt(promoTiers.find(t => t.key === a.plan)?.price || 0) }}/mo
                                        </span>
                                        <span v-else class="text-slate-300 font-black italic">No plan</span>
                                    </td>
                                    <td>
                                        <div v-if="a.plan" class="space-y-1.5">
                                            <div class="flex items-center gap-2 text-[10px] font-black uppercase">
                                                <span class="text-slate-400">{{ (a.usage / 1000).toFixed(0) }}k / {{ (promoTiers.find(t => t.key === a.plan)!.impr / 1000).toLocaleString() }}k</span>
                                                <span class="text-green-600 font-black">OK</span>
                                            </div>
                                            <div class="h-1.5 w-48 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500 transition-all duration-500" :style="{ width: Math.min(100, (a.usage / promoTiers.find(t => t.key === a.plan)!.impr * 100)) + '%' }"></div>
                                            </div>
                                        </div>
                                        <span v-else>—</span>
                                    </td>
                                    <td>
                                        <select class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold bg-white outline-none focus:ring-4 focus:ring-slate-50 transition" @change="openPay(promoTiers.find(t => t.key === ($event.target as HTMLSelectElement).value)!.price, promoTiers.find(t => t.key === ($event.target as HTMLSelectElement).value)!.name + ' plan', a.name)">
                                            <option value="">— set plan —</option>
                                            <option v-for="t in promoTiers" :key="t.key" :value="t.key" :selected="a.plan === t.key">{{ t.name }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button v-if="a.plan" class="rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 px-4 py-1 text-[10px] font-black hover:bg-rose-100 transition active:scale-95">Cancel</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h3 class="text-xl font-black mb-1">Promote — Pay-as-you-go</h3>
                    <p class="text-slate-500 text-sm mb-4">Book extra reach beyond a plan's allowance, for the selected segment. Priced from the central Ad Pricing engine; paid by Wallet / Card / Transfer.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-1 block">Advertiser ({{ segmentData[currentSeg]?.length || 0 }} in segment)</label>
                                <select v-model="selectedAdvertiser" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-bold bg-white focus:ring-4 focus:ring-purple-100 outline-none transition">
                                    <option v-for="a in advertisers" :key="a.name">{{ a.name }}</option>
                                </select>
                            </div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-1 block">Placement</label>
                                <select v-model="selectedPlacement" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-bold bg-white focus:ring-4 focus:ring-purple-100 outline-none transition">
                                    <option v-for="p in adsPlacements" :key="p.key" :value="p.key">{{ p.name }}</option>
                                </select>
                            </div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-1 block">Duration</label>
                                <select v-model="selectedDuration" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-bold bg-white focus:ring-4 focus:ring-purple-100 outline-none transition">
                                    <option v-for="d in durations" :key="d.key" :value="d.key">{{ d.name }}</option>
                                </select>
                            </div>
                            <button @click="openPay(quote.total, adsPlacements.find(p => p.key === selectedPlacement)!.name + ' promo', selectedAdvertiser)" class="rounded-2xl bg-slate-950 text-white px-6 py-3 font-black w-full shadow-lg transition active:scale-95">Book Promotion</button>
                        </div>
                        <div id="opQuote" class="rounded-2xl bg-slate-50 p-6 space-y-3 flex flex-col justify-center border border-slate-100">
                            <div class="flex justify-between"><span class="text-slate-500 font-bold">Placement</span><span class="font-black">{{ adsPlacements.find(p => p.key === selectedPlacement)?.name }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500 font-bold">Est. reach</span><span class="font-black">{{ num(quote.impr) }} impressions</span></div>
                            <div class="flex justify-between border-t border-slate-200 pt-3"><span class="text-slate-500 font-bold text-lg">Charge</span><span class="font-black text-2xl text-green-600">{{ fmt(quote.total) }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h3 class="text-xl font-black mb-1">Billing & Payments</h3>
                    <p class="text-slate-500 text-sm mb-4">Subscription & overage charges paid in-system — Wallet, Card or Bank Transfer.</p>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500 font-bold">
                                <tr>
                                    <th class="py-2 px-2">Date</th>
                                    <th>Advertiser</th>
                                    <th>Item</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in promoBilling" :key="b.ts" class="border-t hover:bg-slate-50 transition">
                                    <td class="py-3 px-2 font-medium text-slate-500">{{ new Date(b.ts).toLocaleDateString() }}</td>
                                    <td class="font-black text-slate-800">{{ b.who }}</td>
                                    <td class="text-slate-600">{{ b.label }}</td>
                                    <td class="text-right font-black">{{ fmt(b.amount) }}</td>
                                    <td class="text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border" :class="b.method === 'Wallet' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-sky-50 text-sky-700 border-sky-200'">
                                            {{ b.method }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Payment Modal -->
        <div v-if="showPayModal" class="fixed inset-0 bg-black/70 z-[70] flex items-center justify-center p-5 backdrop-blur-md">
            <div class="bg-white rounded-[40px] max-w-sm w-full shadow-2xl overflow-hidden animate-in zoom-in duration-200">
                <div class="p-6 bg-slate-900 text-white">
                    <h3 class="text-2xl font-black">Pay for Ad</h3>
                    <p class="text-slate-400 font-bold mt-1 uppercase text-xs tracking-widest">{{ payForm.who }}</p>
                </div>
                <div class="p-8 space-y-6">
                    <div class="p-6 rounded-3xl bg-slate-50 text-center border border-slate-100">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ payForm.label }}</p>
                        <h2 class="text-4xl font-black text-slate-900">{{ fmt(payForm.amount) }}</h2>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 mb-3 uppercase tracking-widest">Choose Method</p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-4 rounded-2xl border-2 p-4 cursor-pointer transition" :class="payForm.method === 'Wallet' ? 'border-slate-900 bg-slate-50' : 'border-slate-100 hover:border-slate-200'">
                                <input type="radio" v-model="payForm.method" value="Wallet" class="h-5 w-5 accent-slate-900">
                                <Wallet class="h-6 w-6 text-slate-600" />
                                <span class="font-bold text-slate-800">LinkUp Wallet</span>
                            </label>
                            <label class="flex items-center gap-4 rounded-2xl border-2 p-4 cursor-pointer transition" :class="payForm.method === 'Card' ? 'border-slate-900 bg-slate-50' : 'border-slate-100 hover:border-slate-200'">
                                <input type="radio" v-model="payForm.method" value="Card" class="h-5 w-5 accent-slate-900">
                                <CreditCard class="h-6 w-6 text-slate-600" />
                                <span class="font-bold text-slate-800">Credit / Debit Card</span>
                            </label>
                            <label class="flex items-center gap-4 rounded-2xl border-2 p-4 cursor-pointer transition" :class="payForm.method === 'Bank Transfer' ? 'border-slate-900 bg-slate-50' : 'border-slate-100 hover:border-slate-200'">
                                <input type="radio" v-model="payForm.method" value="Bank Transfer" class="h-5 w-5 accent-slate-900">
                                <Landmark class="h-6 w-6 text-slate-600" />
                                <span class="font-bold text-slate-800">Bank Transfer</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button @click="confirmPayment" class="w-full rounded-2xl bg-slate-950 text-white px-8 py-4 font-black text-lg shadow-xl transition active:scale-95 hover:bg-black">Confirm Payment</button>
                        <button @click="showPayModal = false" class="w-full py-2 text-slate-400 font-bold hover:text-slate-600 transition">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
