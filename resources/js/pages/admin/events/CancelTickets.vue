<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, router } from '@inertiajs/vue3';
import { Ticket, TicketX, Wallet, Check, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialRequests: any[];
    initialOrders: any[];
    initialUsers: any[];
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;
const requests = ref([...props.initialRequests]);
const orders = ref([...props.initialOrders]);
const users = ref([...props.initialUsers]);

const cancelSearch = ref('');
const refundPolicy = ref('Full order amount');
const orderStatusFilter = ref('All statuses');

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

const countryFin = (c: any) => {
    let gross = 0, platform = 0, bank = 0, cost = 0;
    units.forEach((u) => {
        const v = (Number(c[u.key]) || 0) * getScale();
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
        users: rs.reduce((s, c) => s + (Number(c.users) || 0), 0) * getScale(),
        merchants: rs.reduce((s, c) => s + (Number(c.merchants) || 0), 0),
        organizers: rs.reduce((s, c) => s + (Number(c.organizers) || 0), 0),
        countries: rs.length,
    };
};

// Logic
const filteredRequests = computed(() => {
    const q = cancelSearch.value.toLowerCase().trim();
    return requests.value.filter(c => [c.id, c.ticket, c.event, c.buyer, c.reason, c.status].join(' ').toLowerCase().includes(q));
});

const filteredOrders = computed(() => {
    const st = orderStatusFilter.value;
    return orders.value.filter(c => st === 'All statuses' || c.status === st);
});

const calculateRefund = (amount: number) => {
    if (refundPolicy.value.includes('No refund')) return 0;
    if (refundPolicy.value.includes('50%')) return amount * 0.5;
    return amount;
};

const processRefund = (req: any, status: string) => {
    const amount = status === 'Approve + Refund' ? calculateRefund(req.amount) : 0;

    router.post(route('admin.request.create'), {
        type: status,
        admin_note: 'Processed via Executive Dashboard',
        ticket: req.raw_request,
        admin_controll: refundPolicy.value,
        ammount: amount,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Request processed successfully');
        },
        onError: (errors) => {
            console.error('Failed to process request:', errors);
            toast.error('Failed to process request');
        },
    });
};

const stats = computed(() => {
    const all = orders.value;
    return {
        total: requests.value.length + orders.value.length,
        approved: all.filter(c => c.status === 'Approve + Refund').length,
        denied: all.filter(c => c.status === 'Refund Denied').length
    };
});

// Sync ref when props update from backend
watch(() => props.initialRequests, (newVal) => { requests.value = [...newVal]; }, { deep: true });
watch(() => props.initialOrders, (newVal) => { orders.value = [...newVal]; }, { deep: true });
watch(() => props.initialUsers, (newVal) => { users.value = [...newVal]; }, { deep: true });

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
    <Head title="Cancellation Requests" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="cancelTicketsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Cancellation Requests" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div>
                    <h3 class="text-3xl font-black text-slate-800">Cancellation Requests</h3>
                    <p class="text-slate-500 max-w-4xl">Admin processing queue for ticket cancellation requests. Review the front-end request, apply refund policy, and update the order status.</p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                    <!-- Requests Queue -->
                    <div class="xl:col-span-3 card rounded-3xl overflow-hidden shadow-sm">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-xl font-black">Requests Queue</h3>
                            <p class="text-slate-500 text-sm">Front-end creates cancellation requests. Admin reviews and processes.</p>
                        </div>
                        <div class="p-6">
                            <div class="relative mb-5">
                                <input v-model="cancelSearch" class="w-full rounded-2xl border border-slate-200 px-4 py-3 pl-12 focus:ring-4 focus:ring-purple-100 transition-all" placeholder="Search by event, user, ticket, or order id...">
                                <Ticket class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 h-5 w-5" />
                            </div>
                            <div class="overflow-x-auto scrollbar">
                                <table class="w-full text-left">
                                    <thead class="text-xs uppercase text-slate-500 font-bold bg-slate-50/80">
                                        <tr>
                                            <th class="py-3 px-3">Request</th>
                                            <th>User</th>
                                            <th>Event</th>
                                            <th>Status</th>
                                            <th class="text-right">Refund</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr v-for="c in filteredRequests" :key="c.id" class="border-t hover:bg-slate-50 transition align-top">
                                            <td class="py-4 px-3">
                                                <b class="text-slate-800">{{ c.id }}</b>
                                                <p class="text-[10px] text-slate-500 font-bold uppercase mt-1">{{ c.ticket }} • {{ c.date }}</p>
                                            </td>
                                            <td class="py-4 font-bold">{{ c.buyer }}</td>
                                            <td class="py-4 max-w-sm">
                                                <span class="block truncate font-medium text-slate-700">{{ c.event }}</span>
                                                <p class="text-xs text-slate-500 italic mt-1 line-clamp-1">"{{ c.reason }}"</p>
                                            </td>
                                            <td class="py-4">
                                                <span class="rounded-full px-3 py-1 text-[10px] font-black tracking-wider uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                                    {{ c.status }}
                                                </span>
                                            </td>
                                            <td class="py-4 text-right font-black text-lg">{{ fmt(calculateRefund(c.amount)) }}</td>
                                            <td class="py-4">
                                                <div class="flex flex-wrap justify-center gap-1.5">
                                                    <button @click="processRefund(c, 'Approve + Refund')" class="rounded-xl bg-green-600 text-white px-3 py-2 text-[10px] font-black hover:bg-green-700 transition shadow-sm">Refund</button>
                                                    <button @click="processRefund(c, 'Approve + No Refund')" class="rounded-xl bg-slate-800 text-white px-3 py-2 text-[10px] font-black hover:bg-black transition shadow-sm">No Refund</button>
                                                    <button @click="processRefund(c, 'Refund Denied')" class="rounded-xl bg-white border border-slate-200 text-rose-600 px-3 py-2 text-[10px] font-black hover:bg-rose-50 transition">Deny</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="filteredRequests.length === 0">
                                            <td colspan="6" class="py-12 text-center text-slate-400 font-bold">No pending requests found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Controls -->
                    <div class="card rounded-3xl p-6 space-y-6 shadow-sm">
                        <div>
                            <h3 class="text-xl font-black">Admin Controls</h3>
                            <p class="text-slate-500 text-sm">Simulate refund policy and view global stats.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-100 p-5 bg-slate-50/50">
                            <h4 class="font-black text-sm mb-2 flex items-center gap-2"><TicketX class="h-4 w-4 text-rose-500" /> Refund Policy Defaults</h4>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-4 leading-tight">Default action when admin clicks “Approve Refund.”</p>
                            <select v-model="refundPolicy" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all appearance-none shadow-sm">
                                <option>Full order amount</option>
                                <option>Partial refund - 50%</option>
                                <option>No refund</option>
                            </select>
                        </div>
                        <div class="rounded-3xl border border-slate-100 p-5 bg-slate-50/50">
                            <h4 class="font-black text-sm mb-3">Snapshot Counts</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs font-bold text-slate-600">
                                    <span>Total Logs</span>
                                    <span class="text-slate-900">{{ stats.total }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-slate-600">
                                    <span>Approved</span>
                                    <span class="text-green-600">{{ stats.approved }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-slate-600">
                                    <span>Denied</span>
                                    <span class="text-rose-600">{{ stats.denied }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                    <!-- Orders Ledger -->
                    <div class="xl:col-span-3 card rounded-3xl overflow-hidden shadow-sm">
                        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50/50">
                            <div>
                                <h3 class="text-xl font-black">Processed Orders</h3>
                                <p class="text-slate-500 text-sm">Refunds are applied to orders and recorded in the ledger.</p>
                            </div>
                            <select v-model="orderStatusFilter" class="rounded-2xl border border-slate-200 px-4 py-2.5 font-bold bg-white text-sm shadow-sm focus:ring-4 focus:ring-purple-50 transition-all">
                                <option>All statuses</option>
                                <option>Approve + Refund</option>
                                <option>Approve + No Refund</option>
                                <option>Refund Denied</option>
                            </select>
                        </div>
                        <div class="p-6 overflow-x-auto scrollbar">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase text-slate-500 font-bold bg-slate-50/80">
                                    <tr>
                                        <th class="py-4 px-6">Order</th>
                                        <th>User</th>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Deduct</th>
                                        <th class="text-right px-6">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="o in filteredOrders" :key="o.id" class="border-t border-slate-100 hover:bg-slate-50 transition">
                                        <td class="py-4 px-6 font-black text-purple-700">{{ o.ticket }}</td>
                                        <td class="py-4 font-bold">{{ o.buyer }}</td>
                                        <td class="py-4 max-w-xs truncate">{{ o.event }}</td>
                                        <td class="py-4">
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase border" :class="o.status === 'Approve + Refund' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                                {{ o.status }}
                                            </span>
                                        </td>
                                        <td class="py-4 font-medium text-slate-400 italic">
                                            {{ o.status === 'Approve + Refund' ? 'Wallet Credit' : 'None' }}
                                        </td>
                                        <td class="py-4 text-right px-6 font-black text-slate-800 text-lg">
                                            {{ fmt(o.amount) }}
                                        </td>
                                    </tr>
                                    <tr v-if="filteredOrders.length === 0">
                                        <td colspan="6" class="py-12 text-center text-slate-400 font-bold">No historical records found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Wallet Refunds -->
                    <div class="card rounded-3xl overflow-hidden shadow-sm flex flex-col">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-xl font-black flex items-center gap-2"><Wallet class="h-5 w-5 text-emerald-500" /> Users Wallet</h3>
                            <p class="text-slate-500 text-sm leading-tight">Approved refunds credit the user wallet instantly.</p>
                        </div>
                        <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[500px] scrollbar">
                            <div v-for="(u, idx) in users" :key="idx" class="rounded-2xl bg-white border border-slate-100 p-4 shadow-sm hover:border-emerald-200 transition-colors">
                                <b class="text-slate-800 block leading-tight">{{ u.name }}</b>
                                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">{{ u.ticket }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <p class="text-emerald-600 font-black text-lg">+ {{ fmt(u.amount) }}</p>
                                </div>
                            </div>
                            <div v-if="users.length === 0" class="py-12 text-center text-slate-400 font-bold text-sm border-2 border-dashed border-slate-100 rounded-3xl mt-2">
                                No wallet credits.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <Toaster rich-colors position="top-right" />
    </div>
</template>
