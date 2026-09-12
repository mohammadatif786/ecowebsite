<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Heart, Star, BarChart3, UserRound, Search } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
];

const swipesSearch = ref('');

const swipesData = ref([
    { initials: 'SH', color: 'bg-pink-500', name: 'Sofia Herrera', plan: 'Ritmo', planColor: 'bg-lime-100 text-lime-700', swipes: 187, right: 112, matches: 23, rate: '20.5%', superLikes: 3 },
    { initials: 'MV', color: 'bg-orange-500', name: 'Mateo Vargas', plan: 'El Dorado', planColor: 'bg-amber-100 text-amber-700', swipes: 241, right: 180, matches: 41, rate: '22.8%', superLikes: 5 },
    { initials: 'IC', color: 'bg-cyan-500', name: 'Isabella Cruz', plan: 'Calor', planColor: 'bg-orange-100 text-orange-700', swipes: 143, right: 89, matches: 14, rate: '15.7%', superLikes: 2 },
    { initials: 'DR', color: 'bg-blue-500', name: 'Diego Ramirez', plan: 'Brisa', planColor: 'bg-sky-100 text-sky-700', swipes: 25, right: 18, matches: 2, rate: '11.1%', superLikes: 0 },
    { initials: 'VS', color: 'bg-rose-500', name: 'Valentina Santos', plan: 'Carnaval', planColor: 'bg-pink-100 text-pink-700', swipes: 312, right: 248, matches: 67, rate: '27.0%', superLikes: 8 },
    { initials: 'CM', color: 'bg-violet-500', name: 'Carlos Mendoza', plan: 'Ritmo', planColor: 'bg-lime-100 text-lime-700', swipes: 194, right: 121, matches: 19, rate: '15.7%', superLikes: 4 },
    { initials: 'CR', color: 'bg-orange-500', name: 'Camila Rodriguez', plan: 'Calor', planColor: 'bg-orange-100 text-orange-700', swipes: 108, right: 72, matches: 11, rate: '15.3%', superLikes: 1 },
    { initials: 'RT', color: 'bg-purple-500', name: 'Rafael Torres', plan: 'Carnaval', planColor: 'bg-pink-100 text-pink-700', swipes: 287, right: 201, matches: 52, rate: '25.9%', superLikes: 6 },
]);

const filteredSwipes = computed(() => {
    return swipesData.value.filter(s =>
        s.name.toLowerCase().includes(swipesSearch.value.toLowerCase()) ||
        s.plan.toLowerCase().includes(swipesSearch.value.toLowerCase())
    );
});

// Mock header metrics
const headerMetrics = computed(() => ({
    gtv: '$14.2M',
    revenue: '$840k',
    bank: '$2.1M',
    net: '$420k',
    users: '1.2M',
    merchants: '12.4k',
    organizers: '2.1k',
    countries: '15'
}));

const handleFilterChange = () => {};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Swipes Management" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="swipesCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Swipes Management"
                :countries="countries"
                :metrics="headerMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h3 class="text-3xl font-black text-slate-950">Swipes</h3>
                    <p class="text-slate-500 font-medium">Activity & match analytics across the LinkUp network.</p>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 border-t-4 border-lime-300 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">Total Swipes Today</p>
                                <h3 class="text-4xl font-black mt-3 text-slate-800">34,812</h3>
                                <p class="text-green-600 text-sm font-bold mt-3 flex items-center gap-1">
                                    <TrendingUp class="w-4 h-4" /> +5.8% vs yesterday
                                </p>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-lime-100 text-lime-600 grid place-items-center">
                                <Heart class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 border-t-4 border-lime-300 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">Matches Today</p>
                                <h3 class="text-4xl font-black mt-3 text-slate-800">4,209</h3>
                                <p class="text-green-600 text-sm font-bold mt-3">↑ 12.1% match rate</p>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-lime-100 text-lime-600 grid place-items-center">
                                <Star class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 border-t-4 border-lime-300 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">Swipes This Month</p>
                                <h3 class="text-4xl font-black mt-3 text-slate-800">891K</h3>
                                <p class="text-green-600 text-sm font-bold mt-3">↑ +22% vs last month</p>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-lime-100 text-lime-600 grid place-items-center">
                                <BarChart3 class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 border-t-4 border-lime-300 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">Avg Swipes / User</p>
                                <h3 class="text-4xl font-black mt-3 text-slate-800">14.4</h3>
                                <p class="text-green-600 text-sm font-bold mt-3">↑ Paid users: 28.7/day</p>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-lime-100 text-lime-600 grid place-items-center">
                                <UserRound class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Swipe Activity Ledger -->
                <div class="card rounded-[32px] bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-black text-slate-800">Swipe Activity Ledger</h3>
                            <p class="text-slate-500 text-sm font-medium">Monitoring real-time interactions and match quality.</p>
                        </div>
                        <div class="relative w-full lg:w-80">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input
                                v-model="swipesSearch"
                                type="text"
                                placeholder="Search user or plan..."
                                class="w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-bold text-sm transition"
                            />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Plan</th>
                                    <th class="px-6 py-4">Swipes Today</th>
                                    <th class="px-6 py-4">Right Swipes</th>
                                    <th class="px-6 py-4">Matches</th>
                                    <th class="px-6 py-4 text-center">Match Rate</th>
                                    <th class="px-6 py-4 text-center">Super Likes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <tr v-for="s in filteredSwipes" :key="s.name" class="hover:bg-slate-50/50 transition group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <span :class="s.color" class="h-10 w-10 rounded-full text-white grid place-items-center font-black text-xs shadow-sm group-hover:scale-110 transition-transform">
                                                {{ s.initials }}
                                            </span>
                                            <b class="text-slate-800 font-black text-sm">{{ s.name }}</b>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span :class="s.planColor" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-current opacity-80">
                                            {{ s.plan }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 font-black text-slate-900">{{ s.swipes }}</td>
                                    <td class="px-6 py-5 font-bold text-emerald-600">{{ s.right }}</td>
                                    <td class="px-6 py-5 font-black text-slate-900">{{ s.matches }}</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="font-black" :class="parseFloat(s.rate) > 20 ? 'text-emerald-600' : 'text-amber-600'">
                                            {{ s.rate }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center font-black text-slate-400">
                                        <span v-if="s.superLikes > 0" class="text-purple-600 flex items-center justify-center gap-1">
                                            <Star class="w-3 h-3 fill-purple-600" /> {{ s.superLikes }}
                                        </span>
                                        <span v-else>—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t border-slate-50 flex items-center justify-between text-sm">
                        <span class="text-slate-500 font-bold">Showing {{ filteredSwipes.length }} of {{ swipesData.length }} users</span>
                        <div class="flex gap-2">
                            <button class="h-10 w-10 rounded-xl bg-lime-300 text-slate-950 font-black shadow-lg shadow-lime-100">1</button>
                            <button class="h-10 w-10 rounded-xl border border-slate-200 font-black text-slate-400 hover:bg-slate-50 transition">2</button>
                            <button class="h-10 w-10 rounded-xl border border-slate-200 font-black text-slate-400 hover:bg-slate-50 transition">3</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
