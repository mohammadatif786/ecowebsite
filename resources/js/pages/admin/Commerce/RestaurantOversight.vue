<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { BarChart3, Star, Flag, ChevronLeft } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model (Matching the HTML's internal data)
const SCOTIA_SHARE = 0.4;
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000, coinsPurchased: 420000, coinsRedeemed: 170000 },
];

const restaurants = ref([
    { id: '@islandgrill', name: 'Island Grill Nassau', city: 'Nassau', country: 'Bahamas', gross: 18420, orders: 642, deliveries: 301, rating: 4.8, commission: 15, status: 'Active' },
    { id: '@conchshack', name: 'Conch Shack Express', city: 'Nassau', country: 'Bahamas', gross: 12760, orders: 511, deliveries: 236, rating: 4.7, commission: 15, status: 'Active' },
    { id: '@yardflavours', name: 'Yard Flavours', city: 'Kingston', country: 'Jamaica', gross: 9840, orders: 402, deliveries: 188, rating: 4.6, commission: 18, status: 'Active' },
    { id: '@caribbowl', name: 'Caribbean Bowl Miami', city: 'Miami', country: 'United States', gross: 21500, orders: 705, deliveries: 362, rating: 4.9, commission: 20, status: 'Active' },
    { id: '@reefandrum', name: 'Reef & Rum Kitchen', city: 'Freeport', country: 'Bahamas', gross: 0, orders: 0, deliveries: 0, rating: 0, commission: 15, status: 'Suspended' }
]);

const selectedRestaurant = ref<any>(null);
const menuItems = ref([
    { name: 'Grilled Salmon', cat: 'Main', price: 24.50, on: true },
    { name: 'Jerk Wings', cat: 'Appetizer', price: 12.00, on: true },
    { name: 'Tropical Salad', cat: 'Salad', price: 14.75, on: false }
]);

const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const handleFilterChange = (newFilters: any) => { filters.value = newFilters; renderAll(); };

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const stats = computed(() => {
    const totGross = restaurants.value.reduce((a, r) => a + r.gross, 0);
    const totComm = restaurants.value.reduce((a, r) => a + (r.gross * r.commission) / 100, 0);
    const totOrders = restaurants.value.reduce((a, r) => a + r.orders, 0);
    const totDeliveries = restaurants.value.reduce((a, r) => a + r.deliveries, 0);
    return { gross: totGross, commission: totComm, active: restaurants.value.filter(r => r.status === 'Active').length, orders: totOrders, deliveries: totDeliveries };
});

const selectRestaurant = (id: string) => {
    selectedRestaurant.value = restaurants.value.find(r => r.id === id);
};

const backToList = () => {
    selectedRestaurant.value = null;
};

const toggleStatus = (id: string) => {
    const r = restaurants.value.find(x => x.id === id);
    if (r) r.status = r.status === 'Suspended' ? 'Active' : 'Suspended';
};

const adjustCommission = (id: string) => {
    const r = restaurants.value.find(x => x.id === id);
    if (!r) return;
    const v = prompt(`Set LinkUp commission % for ${r.name}`, String(r.commission));
    if (v !== null) r.commission = Number(v) || r.commission;
};

const flagItem = (name: string) => {
    alert(`⚑ Flagged "${name}" for review`);
};

const renderAll = async () => {
    await nextTick();
    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('sideGTV', fmt(stats.value.gross));
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
    <Head title="Restaurant Oversight" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsAdminView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Restaurant Oversight" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div v-if="!selectedRestaurant" class="space-y-6 animate-in fade-in duration-500">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black text-[#124d71] flex items-center gap-3"><BarChart3 class="w-8 h-8" /> Restaurant Oversight</h2>
                            <p class="text-slate-500 font-medium max-w-4xl mt-1">Monitor performance & revenue across restaurants. Restaurants manage their own menus & prices in their back office — LinkUp oversees money, quality & compliance.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Restaurant GMV (mo)</p>
                            <h3 class="text-3xl font-black text-slate-900">{{ fmt(stats.gross) }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">LinkUp Commission</p>
                            <h3 class="text-3xl font-black text-green-600">{{ fmt(stats.commission) }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Active Restaurants</p>
                            <h3 class="text-3xl font-black text-slate-900">{{ stats.active }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Total Orders (mo)</p>
                            <h3 class="text-3xl font-black text-slate-900">{{ num(stats.orders) }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Deliveries (billable)</p>
                            <h3 class="text-3xl font-black text-sky-600">{{ num(stats.deliveries) }}</h3>
                        </div>
                    </div>

                    <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 text-[11px] uppercase text-slate-400 font-black tracking-widest">
                                    <tr>
                                        <th class="py-4 px-6">Restaurant</th>
                                        <th>Gross (mo)</th>
                                        <th>LinkUp Cut</th>
                                        <th>Orders</th>
                                        <th>Deliveries</th>
                                        <th>Rating</th>
                                        <th>Comm.</th>
                                        <th>Status</th>
                                        <th class="pr-6"></th>
                                    </tr>
                                </thead>
                                <tbody class="font-bold text-slate-700">
                                    <tr v-for="r in restaurants" :key="r.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                        <td class="py-5 px-6">
                                            <div class="font-black text-slate-900">{{ r.name }}</div>
                                            <div class="text-[11px] text-slate-400 uppercase tracking-tighter">{{ r.city }}, {{ r.country }}</div>
                                        </td>
                                        <td class="text-slate-900">{{ fmt(r.gross) }}</td>
                                        <td class="text-green-600">{{ fmt((r.gross * r.commission) / 100) }}</td>
                                        <td>{{ num(r.orders) }}</td>
                                        <td class="text-sky-600">{{ num(r.deliveries) }}</td>
                                        <td><span v-if="r.rating" class="text-amber-500">⭐ {{ r.rating }}</span><span v-else class="text-slate-300">—</span></td>
                                        <td>{{ r.commission }}%</td>
                                        <td>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="r.status === 'Active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-rose-50 text-rose-700 border-rose-100'">{{ r.status }}</span>
                                        </td>
                                        <td class="pr-6 text-right">
                                            <button @click="selectRestaurant(r.id)" class="rounded-xl bg-[#124d71] text-white px-4 py-2 text-xs font-black shadow-lg shadow-blue-900/10 transition active:scale-95">View</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-else class="space-y-6 animate-in slide-in-from-left-4 duration-500">
                    <button @click="backToList" class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-black text-sm text-slate-600 hover:bg-slate-50 transition">
                        <ChevronLeft class="w-4 h-4" /> Back to All Restaurants
                    </button>

                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-3xl font-black text-slate-900">{{ selectedRestaurant.name }}</h2>
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="selectedRestaurant.status === 'Active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-rose-50 text-rose-700 border-rose-100'">{{ selectedRestaurant.status }}</span>
                            </div>
                            <p class="text-slate-500 font-medium mt-1">{{ selectedRestaurant.city }}, {{ selectedRestaurant.country }} · ⭐ {{ selectedRestaurant.rating || '—' }} · {{ num(selectedRestaurant.orders) }} orders this month</p>
                        </div>
                        <div class="flex gap-3">
                            <button @click="adjustCommission(selectedRestaurant.id)" class="rounded-2xl bg-[#124d71] text-white px-6 py-3 font-black transition active:scale-95">Adjust Commission</button>
                            <button @click="toggleStatus(selectedRestaurant.id)" class="rounded-2xl px-6 py-3 font-black transition active:scale-95" :class="selectedRestaurant.status === 'Suspended' ? 'bg-green-600 text-white' : 'bg-rose-600 text-white'">
                                {{ selectedRestaurant.status === 'Suspended' ? 'Reactivate' : 'Suspend' }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Gross Sales (mo)</p>
                            <h3 class="text-3xl font-black text-slate-900">{{ fmt(selectedRestaurant.gross) }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">LinkUp Commission ({{ selectedRestaurant.commission }}%)</p>
                            <h3 class="text-3xl font-black text-green-600">{{ fmt((selectedRestaurant.gross * selectedRestaurant.commission) / 100) }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Restaurant Net Payout</p>
                            <h3 class="text-3xl font-black text-slate-900">{{ fmt(selectedRestaurant.gross - (selectedRestaurant.gross * selectedRestaurant.commission) / 100) }}</h3>
                        </div>
                    </div>

                    <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm bg-white">
                        <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                            <div>
                                <h4 class="text-xl font-black text-slate-900">Live Menu — Read-only</h4>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-0.5">Managed by the restaurant · you can flag items, not edit</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 text-[11px] uppercase text-slate-400 font-black tracking-widest">
                                    <tr>
                                        <th class="py-4 px-8">Item</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th class="pr-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="font-bold text-slate-700">
                                    <tr v-for="m in menuItems" :key="m.name" class="border-t border-slate-50">
                                        <td class="py-4 px-8 text-slate-900">{{ m.name }}</td>
                                        <td class="text-slate-400">{{ m.cat }}</td>
                                        <td class="text-slate-900">{{ fmt(m.price) }}</td>
                                        <td>
                                            <span v-if="m.on" class="text-green-600 font-black">Available</span>
                                            <span v-else class="text-rose-600 font-black">Sold out</span>
                                        </td>
                                        <td class="pr-8 text-right">
                                            <button @click="flagItem(m.name)" class="rounded-xl border border-amber-200 bg-white px-4 py-2 text-[10px] font-black text-amber-700 hover:bg-amber-50 transition uppercase tracking-widest flex items-center gap-1.5 ml-auto">
                                                <Flag class="w-3 h-3" /> Flag Item
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
