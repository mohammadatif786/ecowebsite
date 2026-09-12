<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ClipboardList, Download, Ticket } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialRecords: any[];
    initialStats: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;
const eventCommerceRecords = props.initialRecords;

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

const getTotals = () => {
    const rs = getFilteredCountries();
    return {
        gross: rs.reduce((s, c) => s + (Number(c.tickets) || 0), 0) * getScale(),
        users: rs.reduce((s, c) => s + (Number(c.users) || 0), 0) * getScale(),
        merchants: rs.reduce((s, c) => s + (Number(c.merchants) || 0), 0),
        organizers: rs.reduce((s, c) => s + (Number(c.organizers) || 0), 0),
        countries: rs.length,
    };
};

// Report Specific Logic
const reportSearch = ref('');
const reportFilters = ref({
    from: '',
    to: '',
    type: 'All Types',
    event: 'All Events'
});

const filteredTickets = computed(() => {
    const q = reportSearch.value.toLowerCase().trim();
    const f = reportFilters.value;
    return eventCommerceRecords.filter(r => {
        const hay = [r.id, r.buyer, r.email, r.itemType, r.event, r.status].join(' ').toLowerCase();
        return (!f.from || r.date >= f.from) &&
               (!f.to || r.date <= f.to) &&
               (f.type === 'All Types' || r.itemType === f.type) &&
               (f.event === 'All Events' || r.event === f.event) &&
               (!q || hay.includes(q));
    });
});

const formatReportDate = (d: string) => {
    try {
        return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (e) {
        return d;
    }
};

const renderAll = async () => {
    await nextTick();
    const t = getTotals();
    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('rGTV', fmt(t.gross));
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

const exportCSV = () => {
    alert('CSV export is being generated for ' + filteredTickets.value.length + ' records.');
};
</script>

<template>
    <Head title="Tickets Report" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="ticketReportCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Tickets Report" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div>
                    <h3 class="text-3xl font-black text-purple-600">Ticket Sales Report</h3>
                    <p class="text-slate-500">Generate and download detailed ticket sales reports.</p>
                </div>

                <div class="card rounded-3xl overflow-hidden border border-purple-100">
                    <div class="bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white p-5 flex items-center gap-3">
                        <ClipboardList class="h-6 w-6" />
                        <h3 class="text-xl font-black">Report Filters</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 xl:grid-cols-2 gap-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="font-bold text-slate-600 text-sm block mb-2">From Date</label>
                                <input v-model="reportFilters.from" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                            </div>
                            <div>
                                <label class="font-bold text-slate-600 text-sm block mb-2">To Date</label>
                                <input v-model="reportFilters.to" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="font-bold text-slate-600 text-sm block mb-2">Ticket Type</label>
                                <select v-model="reportFilters.type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 bg-white">
                                    <option>All Types</option>
                                    <option>VIP</option>
                                    <option>General</option>
                                    <option>Student</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-bold text-slate-600 text-sm block mb-2">Event</label>
                                <select v-model="reportFilters.event" class="w-full rounded-2xl border border-slate-200 px-4 py-3 bg-white">
                                    <option>All Events</option>
                                    <option v-for="e in [...new Set(eventCommerceRecords.map(r => r.event))]" :key="e">{{ e }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="xl:col-span-2">
                            <label class="font-bold text-slate-600 text-sm block mb-2">Search Tickets</label>
                            <input v-model="reportSearch" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Search customer name, email, or ticket ID...">
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><Ticket class="h-6 w-6" /></div>
                            <div>
                                <h3 class="text-xl font-black">Ticket Sales List</h3>
                                <p class="text-slate-500 text-sm font-bold uppercase tracking-wider">{{ num(filteredTickets.length) }} records found</p>
                            </div>
                        </div>
                        <button @click="exportCSV" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-6 py-3 font-black shadow-lg shadow-purple-200 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                            <Download class="w-5 h-5" /> Download CSV Report
                        </button>
                    </div>

                    <div v-if="filteredTickets.length === 0" class="p-16 text-center">
                        <div class="h-20 w-20 rounded-full bg-slate-100 text-slate-400 grid place-items-center mx-auto mb-5"><Ticket class="w-10 h-10" /></div>
                        <h3 class="text-xl font-black text-slate-800">No records found</h3>
                        <p class="text-slate-500 mt-2">Adjust your filters or try a different search term.</p>
                    </div>

                    <div v-else class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                                <tr>
                                    <th class="py-4 px-6">Customer</th>
                                    <th>Ticket Type</th>
                                    <th class="text-center">Qty</th>
                                    <th>Event</th>
                                    <th>Purchase Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in filteredTickets" :key="r.id" class="border-t border-slate-100 hover:bg-slate-50 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-purple-600 text-white grid place-items-center font-black">
                                                {{ (r.buyer || '?').trim()[0] }}
                                            </div>
                                            <div>
                                                <b class="text-slate-800 block leading-tight">{{ r.buyer }}</b>
                                                <span class="text-xs text-slate-500">{{ r.email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full bg-purple-50 text-purple-700 border border-purple-100 px-3 py-1 text-xs font-black">
                                            {{ r.itemType }} Pass
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="h-8 min-w-8 inline-grid place-items-center rounded-full bg-slate-100 font-black text-sm px-2">
                                            {{ num(r.qty) }}
                                        </span>
                                    </td>
                                    <td class="max-w-xs truncate font-medium text-slate-700">{{ r.event }}</td>
                                    <td>
                                        <div class="flex items-center gap-1.5 text-slate-600 text-sm font-bold">
                                            <span class="opacity-40">◷</span> {{ formatReportDate(r.date) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black border" :class="r.status === 'Paid' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-rose-50 text-rose-700 border-rose-100'">
                                            {{ r.status }}
                                        </span>
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
