<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Search, Headphones, MessageSquare, AlertCircle, Clock } from 'lucide-vue-next';
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

const handleFilterChange = () => {};

// Support State
const tickets = ref([
    { id: 'SUP-E-1001', customer: 'Kayley Stuart', order: '#EATS10002', issue: 'Late Delivery', priority: 'Medium', status: 'Open', assigned: 'Support Team' },
    { id: 'SUP-E-1002', customer: 'Maria Gomez', order: '#EATS10003', issue: 'Missing Item', priority: 'High', status: 'Investigating', assigned: 'Operations' },
    { id: 'SUP-E-1003', customer: 'Dwayne Clarke', order: '#EATS09994', issue: 'Refund Request', priority: 'High', status: 'Resolved', assigned: 'Finance' }
]);

const ticketSearch = ref('');
const filteredTickets = computed(() => {
    const q = ticketSearch.value.toLowerCase().trim();
    return tickets.value.filter(t =>
        !q || [t.id, t.customer, t.order, t.issue, t.status].join(' ').toLowerCase().includes(q)
    );
});

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Customer Support" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsSupportCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Customer Support" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-orange-600">Customer Support</h3>
                        <p class="text-slate-500 font-medium mt-1">Manage and resolve tickets for missing items, late deliveries, and refunds.</p>
                    </div>
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="ticketSearch" class="rounded-2xl border border-slate-200 pl-10 pr-4 py-3 w-96 outline-none focus:ring-4 focus:ring-orange-50 transition" placeholder="Search ticket ID, customer, issue...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-orange-100 text-orange-600 grid place-items-center"><MessageSquare class="w-6 h-6" /></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Total Tickets</p><h3 class="text-2xl font-black text-slate-800">142</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center"><Clock class="w-6 h-6" /></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Open / New</p><h3 class="text-2xl font-black text-slate-800">18</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-rose-100 text-rose-600 grid place-items-center"><AlertCircle class="w-6 h-6" /></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">High Priority</p><h3 class="text-2xl font-black text-slate-800">4</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-100 text-emerald-600 grid place-items-center"><Headphones class="w-6 h-6" /></div>
                        <div><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Avg. Res. Time</p><h3 class="text-2xl font-black text-slate-800">42m</h3></div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card rounded-[32px] border-slate-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-4 px-6">Ticket ID</th>
                                    <th>Customer</th>
                                    <th>Order</th>
                                    <th>Issue</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th class="pr-6">Assigned To</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="t in filteredTickets" :key="t.id" class="border-t border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-5 px-6 font-mono text-xs text-slate-400">{{ t.id }}</td>
                                    <td>{{ t.customer }}</td>
                                    <td class="text-slate-900 font-black tracking-tighter text-[13px]">{{ t.order }}</td>
                                    <td>
                                        <div class="text-slate-800 font-bold">{{ t.issue }}</div>
                                    </td>
                                    <td>
                                        <span class="text-[10px] font-black uppercase tracking-widest" :class="t.priority === 'High' ? 'text-rose-600' : 'text-amber-600'">{{ t.priority }}</span>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border" :class="t.status === 'Resolved' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ t.status }}</span>
                                    </td>
                                    <td class="pr-6">
                                        <div class="flex items-center gap-2">
                                            <div class="h-6 w-6 rounded-full bg-slate-100 grid place-items-center text-[10px] font-black uppercase text-slate-400">{{ t.assigned[0] }}</div>
                                            <span class="text-xs text-slate-500">{{ t.assigned }}</span>
                                        </div>
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
