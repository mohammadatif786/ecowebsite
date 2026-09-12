<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Trophy, Download, Medal, Star, HelpCircle } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model from HTML
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000, coinsPurchased: 420000, coinsRedeemed: 170000 },
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

const initialEarners = [
    { n: 'Destiny "SocaBoss" Charles', h: '@socaboss', c: 'tt', ct: 'Port of Spain, Trinidad', coins: 486200, src: 'Live', tr: 3 },
    { n: 'Marlon Pierre', h: '@marlonp', c: 'jm', ct: 'Kingston, Jamaica', coins: 412750, src: 'Live', tr: 3 },
    { n: 'Aaliyah Joseph', h: '@aaliyahvibes', c: 'bb', ct: 'Bridgetown, Barbados', coins: 357900, src: 'Vibes', tr: 2 },
    { n: 'Carlos Mendez', h: '@carlosm', c: 'do', ct: 'Santo Domingo, DR', coins: 298430, src: 'Live', tr: 2 },
    { n: 'Shanice Williams', h: '@shanicew', c: 'bs', ct: 'Nassau, Bahamas', coins: 271600, src: 'Gifts', tr: 2 },
    { n: 'Kemar Brown', h: '@kemarb', c: 'jm', ct: 'Montego Bay, Jamaica', coins: 244180, src: 'Live', tr: 1 },
    { n: 'Isabella Rojas', h: '@isarojas', c: 'co', ct: 'Cartagena, Colombia', coins: 219050, src: 'Vibes', tr: 1 },
    { n: 'Tyrese Phillip', h: '@tyresep', c: 'gd', ct: "St. George's, Grenada", coins: 198720, src: 'Live', tr: 1 },
    { n: 'Camille Antoine', h: '@camillea', c: 'lc', ct: 'Castries, St. Lucia', coins: 176300, src: 'Gifts', tr: 1 },
    { n: 'Damian Walcott', h: '@damianw', c: 'tt', ct: 'San Fernando, Trinidad', coins: 154880, src: 'Vibes', tr: 0 },
    { n: 'Renee Baptiste', h: '@reneeb', c: 'dm', ct: 'Roseau, Dominica', coins: 132540, src: 'Live', tr: 0 },
    { n: 'Andre Gittens', h: '@andreg', c: 'bb', ct: 'Holetown, Barbados', coins: 118970, src: 'Gifts', tr: 0 }
];

const timeRange = ref('all');
const earners = computed(() => {
    const mult = timeRange.value === 'week' ? 0.18 : timeRange.value === 'month' ? 0.55 : 1;
    return initialEarners.map(e => ({
        ...e,
        coins: Math.round(e.coins * mult)
    })).sort((a, b) => b.coins - a.coins);
});

const totalCoins = computed(() => earners.value.reduce((a, e) => a + e.coins, 0));
const totalTrophies = computed(() => initialEarners.reduce((a, e) => a + e.tr, 0));
const countriesCount = computed(() => new Set(initialEarners.map(e => e.c)).size);

const fl = (c: string) => `https://flagcdn.com/w40/${c.toLowerCase()}.png`;
const usd = (coins: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(coins * 0.01);
const init = (n: string) => n.split(' ').filter(w => w[0] && /[A-Za-z]/.test(w[0])).slice(0, 2).map(w => w[0]).join('').toUpperCase();

const srcColor = (s: string) => {
    const m: Record<string, string> = { Live: '#ef4444', Vibes: '#8b5cf6', Gifts: '#f59e0b' };
    return m[s] || '#64748b';
};

const medalColor = (rank: number) => {
    if (rank === 1) return '#facc15';
    if (rank === 2) return '#cbd5e1';
    if (rank === 3) return '#f59e0b';
    return null;
};

const renderAll = async () => {
    await nextTick();
    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('sideGTV', '$46,673,000');
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
    <Head title="Top Coin Earners" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="topEarnersView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Top Coin Earners" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900">🏆 Top Coin Earners</h2>
                        <p class="text-slate-500 max-w-2xl font-medium mt-1">The highest coin earners across the Caribbean & Latin America — from LinkUp Live, Vibes and gifting. 1 LinkUp Coin = US$0.01.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select v-model="timeRange" class="rounded-xl border border-slate-200 px-4 py-2.5 font-black bg-white focus:ring-4 focus:ring-purple-50 outline-none transition shadow-sm">
                            <option value="all">All Time</option>
                            <option value="month">This Month</option>
                            <option value="week">This Week</option>
                        </select>
                        <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 font-black flex items-center gap-2 transition hover:bg-black active:scale-95 shadow-lg">
                            <Download class="w-4 h-4" /> Export CSV
                        </button>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="card rounded-[28px] p-6 border-slate-100 shadow-sm">
                        <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-1">Coins Earned (Top 12)</p>
                        <h3 class="text-3xl font-black text-slate-900">{{ totalCoins.toLocaleString() }} 🪙</h3>
                        <p class="text-green-600 font-black text-sm mt-1">{{ usd(totalCoins) }}</p>
                    </div>
                    <div class="card rounded-[28px] p-6 border-slate-100 shadow-sm">
                        <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-1">Top Earner</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ earners[0].n.split(' ')[0] }}</h3>
                        <p class="text-slate-500 font-bold text-xs mt-1">{{ earners[0].coins.toLocaleString() }} 🪙 • {{ earners[0].ct }}</p>
                    </div>
                    <div class="card rounded-[28px] p-6 border-slate-100 shadow-sm">
                        <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-1">Live Trophies Awarded</p>
                        <h3 class="text-3xl font-black text-slate-900">{{ totalTrophies }} 🏆</h3>
                        <p class="text-slate-500 font-bold text-xs mt-1">100 coins auto-paid per milestone</p>
                    </div>
                    <div class="card rounded-[28px] p-6 border-slate-100 shadow-sm">
                        <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-1">Countries Represented</p>
                        <h3 class="text-3xl font-black text-slate-900">{{ countriesCount }}</h3>
                        <p class="text-slate-500 font-bold text-xs mt-1">across the region</p>
                    </div>
                </div>

                <!-- Milestone Legend -->
                <div class="rounded-3xl bg-amber-50 border border-amber-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-black text-amber-900 text-lg">LinkUp Live Likes Milestones</h4>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-2 text-amber-700 font-bold text-sm">
                            <span class="flex items-center gap-1.5">1,000 likes <span class="text-slate-400">→</span> 🏆 +100 coins</span>
                            <span class="flex items-center gap-1.5">5,000 likes <span class="text-slate-400">→</span> 🏆🏆 +100 coins</span>
                            <span class="flex items-center gap-1.5">10,000 likes <span class="text-slate-400">→</span> 🏆🏆🏆 +100 coins</span>
                        </div>
                    </div>
                    <HelpCircle class="w-10 h-10 text-amber-200 hidden md:block" />
                </div>

                <!-- Table -->
                <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest">
                                    <th class="py-4 px-6">#</th>
                                    <th>Creator</th>
                                    <th>Location</th>
                                    <th>Top Source</th>
                                    <th>Trophies</th>
                                    <th class="text-right pr-6">Coins Earned</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-bold text-slate-700">
                                <tr v-for="(e, i) in earners" :key="e.h" class="border-t border-slate-50 hover:bg-slate-50/50 transition align-middle group">
                                    <td class="py-4 px-6">
                                        <div v-if="medalColor(i + 1)"
                                            class="h-8 w-8 rounded-full grid place-items-center text-slate-900 font-black shadow-sm"
                                            :style="{ backgroundColor: medalColor(i + 1) }">
                                            {{ i + 1 }}
                                        </div>
                                        <span v-else class="text-slate-400 pl-3">{{ i + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-pink-500 text-white grid place-items-center text-xs font-black shadow-inner uppercase">
                                                {{ init(e.n) }}
                                            </div>
                                            <div>
                                                <b class="text-slate-900 block text-base leading-tight group-hover:text-purple-600 transition">{{ e.n }}</b>
                                                <p class="text-slate-400 text-[11px] font-black uppercase mt-1">{{ e.h }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <img :src="fl(e.c)" class="h-4 w-6 rounded-sm shadow-sm">
                                            <span class="text-slate-600">{{ e.ct }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-tight"
                                            :style="{ backgroundColor: srcColor(e.src) + '1a', color: srcColor(e.src) }">
                                            {{ e.src }}
                                        </span>
                                    </td>
                                    <td class="text-lg">
                                        <span v-if="e.tr > 0">{{ '🏆'.repeat(e.tr) }}</span>
                                        <span v-else class="text-slate-200">—</span>
                                    </td>
                                    <td class="text-right pr-6">
                                        <div class="text-base font-black text-slate-900">{{ e.coins.toLocaleString() }} <span class="text-amber-500">🪙</span></div>
                                        <div class="text-[11px] text-green-600 font-black uppercase tracking-tighter">{{ usd(e.coins) }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
