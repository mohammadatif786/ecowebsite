<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Search, Receipt } from 'lucide-vue-next';
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

// Payouts State
const payouts = ref([
    { name: 'Bahama Grill', gross: 12450, comm: 1867.50, proc: 248.00, tax: 0, net: 10334.50, status: 'Completed', date: 'Jun 2' },
    { name: 'Island Jerk Kitchen', gross: 8930, comm: 1339.50, proc: 178.60, tax: 0, net: 7411.90, status: 'Processing', date: 'Jun 2' },
    { name: 'Trini Flavors', gross: 6215, comm: 932.25, proc: 124.30, tax: 0, net: 5158.45, status: 'Pending', date: 'Jun 3' }
]);

const payoutSearch = ref('');
const filteredPayouts = computed(() => {
    const q = payoutSearch.value.toLowerCase().trim();
    return payouts.value.filter(p => !q || p.name.toLowerCase().includes(q));
});

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Restaurant Payouts" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsRestaurantPayoutsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Restaurant Payouts" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="() => {}" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Restaurant Payouts</h3>
                        <p class="text-slate-500 font-medium">Merchant settlement center for gross sales, commissions, and net payouts.</p>
                    </div>
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="payoutSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-80 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search restaurant name...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Gross Sales</p><h3 class="text-3xl font-black mt-1 text-slate-900">$428,650</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Total Commission</p><h3 class="text-3xl font-black mt-1 text-rose-500">$64,297</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Processing Fees</p><h3 class="text-3xl font-black mt-1 text-slate-400">$12,859</h3></div>
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm"><p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Net Settlement</p><h3 class="text-3xl font-black mt-1 text-emerald-600">$351,494</h3></div>
                </div>

                <!-- Table -->
                <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Restaurant</th>
                                    <th>Gross</th>
                                    <th>Comm. (15%)</th>
                                    <th>Proc. (3%)</th>
                                    <th>Tax</th>
                                    <th>Net Settlement</th>
                                    <th>Status</th>
                                    <th class="pr-6">Date</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="p in filteredPayouts" :key="p.name" class="border-t border-slate-50 hover:bg-slate-50 transition align-middle">
                                    <td class="py-5 px-6"><b class="text-slate-900">{{ p.name }}</b></td>
                                    <td>{{ fmt(p.gross) }}</td>
                                    <td class="text-rose-500">-{{ fmt(p.comm) }}</td>
                                    <td class="text-slate-400">-{{ fmt(p.proc) }}</td>
                                    <td>{{ fmt(p.tax) }}</td>
                                    <td class="text-base font-black text-slate-900">{{ fmt(p.net) }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="p.status === 'Completed' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ p.status }}</span>
                                    </td>
                                    <td class="pr-6 text-slate-400 font-medium">{{ p.date }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
