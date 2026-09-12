<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Search, ShoppingBag, Eye, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

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

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

// Orders State
const orders = ref([
    { id: '#EATS10001', customer: 'Cassius Stuart', restaurant: 'Bahama Grill', driver: 'Andre Johnson', status: 'Delivered', total: 42.50, payment: 'Wallet', time: '28m', country: 'Bahamas' },
    { id: '#EATS10002', customer: 'Kayley Stuart', restaurant: 'Island Jerk Kitchen', driver: 'Unassigned', status: 'Preparing', total: 18.75, payment: 'Card', time: '12m', country: 'Bahamas' },
    { id: '#EATS10003', customer: 'Maria Gomez', restaurant: 'Trini Flavors', driver: 'Devon Singh', status: 'Picked Up', total: 31.20, payment: 'Wallet', time: '19m', country: 'Trinidad' }
]);

const orderSearch = ref('');
const filteredOrders = computed(() => {
    const q = orderSearch.value.toLowerCase().trim();
    return orders.value.filter(o =>
        !q || [o.id, o.customer, o.restaurant, o.driver, o.status].join(' ').toLowerCase().includes(q)
    );
});

// View Logic
const showDetail = ref(false);
const selectedOrder = ref<any>(null);
const openDetail = (o: any) => {
    selectedOrder.value = o;
    showDetail.value = true;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Orders" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsOrdersCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Orders" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="() => {}" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Orders Command Center</h3>
                        <p class="text-slate-500 font-medium">Track every order from placed to delivered or refunded.</p>
                    </div>
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="orderSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-96 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search order ID, customer, restaurant...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4">
                    <div v-for="s in [['Pending', 18, 'bg-slate-50'], ['Accepted', 34, 'bg-slate-50'], ['Preparing', 41, 'bg-amber-50/50'], ['Driver Assigned', 22, 'bg-sky-50/50'], ['Delivered', 1248, 'bg-emerald-50/50'], ['Cancelled', 12, 'bg-rose-50/50'], ['Refunded', 7, 'bg-rose-50/50']]" :key="s[0]" class="card p-4 rounded-3xl border border-slate-100 shadow-sm" :class="s[2]">
                        <p class="text-slate-400 font-black uppercase text-[9px] tracking-widest mb-1">{{ s[0] }}</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ s[1] }}</h3>
                    </div>
                </div>

                <!-- Table -->
                <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Order</th>
                                    <th>Customer</th>
                                    <th>Restaurant</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th class="text-center pr-6">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="o in filteredOrders" :key="o.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-5 px-6 font-mono text-xs text-slate-400">{{ o.id }}</td>
                                    <td>{{ o.customer }}</td>
                                    <td><span class="text-slate-900 font-black">{{ o.restaurant }}</span></td>
                                    <td><span :class="o.driver === 'Unassigned' ? 'text-amber-600 italic' : 'text-slate-500'">{{ o.driver }}</span></td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="o.status === 'Delivered' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ o.status }}</span>
                                    </td>
                                    <td class="text-base font-black text-slate-900">{{ fmt(o.total) }}</td>
                                    <td><span class="text-xs uppercase tracking-tighter text-slate-400">{{ o.payment }}</span></td>
                                    <td class="text-center pr-6">
                                        <button @click="openDetail(o)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition mx-auto"><Eye class="h-4 w-4" /></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Detail Modal (Minimal for prototype) -->
        <div v-if="showDetail" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-3 backdrop-blur-md">
            <div class="bg-white rounded-[40px] max-w-lg w-full shadow-2xl overflow-hidden animate-in zoom-in duration-200">
                <div class="p-6 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-xl font-black">Order {{ selectedOrder.id }}</h3>
                    <button @click="showDetail = false" class="text-slate-400 hover:text-white"><X class="w-6 h-6" /></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="flex justify-between items-end border-b border-slate-100 pb-4">
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Total Amount</p><h2 class="text-4xl font-black text-slate-900">{{ fmt(selectedOrder.total) }}</h2></div>
                        <span class="rounded-full px-4 py-1.5 bg-green-50 text-green-700 text-xs font-black uppercase border border-green-100">{{ selectedOrder.status }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Customer</p><b class="text-slate-800">{{ selectedOrder.customer }}</b></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Restaurant</p><b class="text-slate-800">{{ selectedOrder.restaurant }}</b></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Driver</p><b class="text-slate-800">{{ selectedOrder.driver }}</b></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Elapsed Time</p><b class="text-slate-800">{{ selectedOrder.time }}</b></div>
                    </div>
                    <div class="pt-6 border-t border-slate-100 flex gap-3">
                        <button @click="showDetail = false" class="flex-1 rounded-2xl bg-slate-950 text-white py-4 font-black transition active:scale-95">Back to Orders</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
