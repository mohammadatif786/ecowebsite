<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { ClipboardList, Crown, DollarSign, PackagePlus, Sparkles, Ticket, Utensils, Users, CupSoda, Download } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

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
const eventCommerceRecords = ref([...props.initialRecords]);

watch(() => props.initialRecords, (newVal) => {
    eventCommerceRecords.value = [...newVal];
}, { deep: true });

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

// Ticket Sales Specific Logic
const commerceSearch = ref('');
const commerceFilters = ref({
    from: '',
    to: '',
    type: 'All Types',
    event: 'All Events',
    country: 'All Countries',
    category: 'All Categories'
});

const commerceTotals = (rows: any[]) => {
    return rows.reduce((a, r) => {
        a.qty += r.qty; a.subtotal += r.subtotal; a.fee += r.fee; a.tax += r.tax; a.discount += (r.discount || 0); a.refund += r.refund; a.total += r.total;

        // Map by category for revenue cards
        if (r.category === 'Tickets / Passes') a.tickets += r.subtotal;
        if (r.category === 'Food / Cookout') { a.food += r.subtotal; a.cookoutCount += r.qty; }
        if (['Bars / Drinks', 'Water'].includes(r.category)) { a.bars += r.subtotal; a.drinksCount += r.qty; }
        if (r.category === 'Spa / Beauty') { a.beauty += r.subtotal; a.spaCount += r.qty; }
        if (['Tables / VIP', 'Vendor Booths', 'Sponsors', 'Merchandise'].includes(r.category)) { a.addons += r.subtotal; a.addonCount += r.qty; }

        return a;
    }, {
        qty: props.initialStats.totalItems || 0,
        subtotal: 0,
        fee: 0,
        tax: 0,
        discount: 0,
        refund: 0,
        total: props.initialStats.grossRevenue || 0,
        tickets: 0,
        food: props.initialStats.foodRevenue || 0,
        bars: props.initialStats.barRevenue || 0,
        beauty: props.initialStats.spaRevenue || 0,
        addons: props.initialStats.tableRevenue || 0,
        vipCount: props.initialStats.vipCount || 0,
        generalCount: props.initialStats.generalCount || 0,
        cookoutCount: props.initialStats.foodCount || 0,
        spaCount: props.initialStats.spaCount || 0,
        drinksCount: props.initialStats.barCount || 0,
        addonCount: props.initialStats.addonCount || 0
    });
};

const overallStats = computed(() => commerceTotals(eventCommerceRecords.value));

const filteredReportRows = ref<any[]>([]);
const isReportGenerated = ref(false);

const generateReport = async () => {
    const q = commerceSearch.value.toLowerCase().trim();
    const f = commerceFilters.value;

    filteredReportRows.value = eventCommerceRecords.value.filter(r => {
        const hay = [r.id, r.date, r.buyer, r.email, r.event, r.country, r.category, r.itemType, r.item, r.status, r.payment].join(' ').toLowerCase();
        return (!f.from || r.date >= f.from) &&
               (!f.to || r.date <= f.to) &&
               (f.category === 'All Categories' || r.category === f.category) &&
               (f.type === 'All Types' || r.itemType === f.type) &&
               (f.event === 'All Events' || r.event === f.event) &&
               (f.country === 'All Countries' || r.country === f.country) &&
               (!q || hay.includes(q));
    });
    isReportGenerated.value = true;
    // Wait for the v-else block (and its canvas elements) to actually mount
    await nextTick();
    renderCharts();
};

const reportStats = computed(() => commerceTotals(filteredReportRows.value));

const exportCSV = () => {
    const rows = [
        ['ID', 'Date', 'Buyer', 'Email', 'Event', 'Start Time', 'End Time', 'Category', 'Item', 'Qty', 'Subtotal', 'Fee', 'Tax', 'Discount', 'Refund', 'Total', 'Status', 'Payment'],
        ...filteredReportRows.value.map(r => [r.id, r.date, r.buyer, r.email, r.event, r.startTime, r.endTime, r.category, r.item, r.qty, r.subtotal, r.fee, r.tax, r.discount, r.refund, r.total, r.status, r.payment]),
    ];
    const csv = rows.map((row) => row.map((cell) => `"${String(cell ?? '').replaceAll('"', '""')}"`).join(',')).join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    link.download = 'ticket_sales_report.csv';
    link.click();
    URL.revokeObjectURL(link.href);
};

const topItems = computed(() => {
    const map: any = {};
    filteredReportRows.value.forEach(r => {
        if (!map[r.item]) map[r.item] = { item: r.item, category: r.category, qty: 0, total: 0 };
        map[r.item].qty += r.qty;
        map[r.item].total += r.total;
    });
    return Object.values(map).sort((a: any, b: any) => b.total - a.total).slice(0, 8);
});

const eventDrilldown = computed(() => {
    const map: any = {};
    filteredReportRows.value.forEach(r => {
        if (!map[r.event]) map[r.event] = { event: r.event, total: 0, fees: 0, tax: 0, refund: 0, qty: 0 };
        map[r.event].total += r.total;
        map[r.event].fees += r.fee;
        map[r.event].tax += r.tax;
        map[r.event].refund += r.refund;
        map[r.event].qty += r.qty;
    });
    return Object.values(map).sort((a: any, b: any) => b.total - a.total).slice(0, 8);
});

// Charts
const charts = ref<any>({});
const categoryChartRef = ref<HTMLCanvasElement | null>(null);
const statusChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    // If report is not generated, we skip or render placeholder charts
    if (!isReportGenerated.value && filteredReportRows.value.length === 0) return;

    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const rows = filteredReportRows.value.length > 0 ? filteredReportRows.value : eventCommerceRecords.value;
    const cats = [...new Set(rows.map(r => r.category))];

    if (categoryChartRef.value) {
        charts.value.category = new Chart(categoryChartRef.value, {
            type: 'bar',
            data: {
                labels: cats,
                datasets: [
                    { label: 'Revenue', data: cats.map(c => rows.filter(r => r.category === c).reduce((s, r) => s + r.total, 0)), backgroundColor: '#8B5CF6', borderRadius: 8 },
                    { label: 'Fees', data: cats.map(c => rows.filter(r => r.category === c).reduce((s, r) => s + r.fee, 0)), backgroundColor: '#D9EC10', borderRadius: 8 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } }
            }
        });
    }

    if (statusChartRef.value) {
        const statuses = [...new Set(rows.map(r => r.status))];
        charts.value.status = new Chart(statusChartRef.value, {
            type: 'doughnut',
            data: {
                labels: statuses,
                datasets: [{ data: statuses.map(s => rows.filter(r => r.status === s).length), backgroundColor: ['#00C853', '#F59E0B', '#F43F5E'] }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
};

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

    renderCharts();
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});

const quickSearchResults = computed(() => {
    const q = commerceSearch.value.toLowerCase().trim();
    if (!q) return [];
    return eventCommerceRecords.value.filter(r => [r.id, r.date, r.buyer, r.email, r.event, r.country, r.category, r.itemType, r.item, r.status, r.payment].join(' ').toLowerCase().includes(q)).slice(0, 8);
});
</script>

<template>
    <Head title="Ticket Sales" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="ticketSalesCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Ticket Sales" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div>
                    <h3 class="text-3xl font-black text-purple-600">Ticket Sales</h3>
                    <p class="text-slate-500">View and generate reports for ticket sales, passes, spa, bars, food, drinks, tables, sponsors, coupons, and add-ons.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4 xl:col-span-2">
                        <div class="h-14 w-14 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><Ticket class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Total Event Sales</p><h3 class="text-4xl font-black">{{ num(overallStats.qty) }}</h3><p class="text-xs text-slate-500">Tickets, passes, food, spa, bars, drinks, tables and add-ons</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center"><Crown class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">VIP / Table Sales</p><h3 class="text-4xl font-black">{{ num(overallStats.vipCount) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-sky-100 text-sky-600 grid place-items-center"><DollarSign class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Gross Revenue</p><h3 class="text-4xl font-black">{{ fmt(overallStats.total) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-green-100 text-green-600 grid place-items-center"><Users class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">General / Standard Sales</p><h3 class="text-4xl font-black">{{ num(overallStats.generalCount) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-orange-100 text-orange-600 grid place-items-center"><Utensils class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Food / Cookout Sales</p><h3 class="text-4xl font-black">{{ num(overallStats.cookoutCount) }}</h3><p class="text-xs text-slate-500">{{ fmt(overallStats.food) }}</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-pink-100 text-pink-600 grid place-items-center"><Sparkles class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Spa / Beauty Sales</p><h3 class="text-4xl font-black">{{ num(overallStats.spaCount) }}</h3><p class="text-xs text-slate-500">{{ fmt(overallStats.beauty) }}</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-blue-100 text-blue-600 grid place-items-center"><CupSoda class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Bars / Drinks / Water</p><h3 class="text-4xl font-black">{{ num(overallStats.drinksCount) }}</h3><p class="text-xs text-slate-500">{{ fmt(overallStats.bars) }}</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-fuchsia-100 text-fuchsia-600 grid place-items-center"><PackagePlus class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Add-ons / Sponsors</p><h3 class="text-4xl font-black">{{ num(overallStats.addonCount) }}</h3><p class="text-xs text-slate-500">{{ fmt(overallStats.addons) }}</p></div>
                    </div>
                </div>

                <div class="card rounded-3xl p-5">
                    <label class="font-bold text-slate-600 block mb-3">Find Tickets / Event Sales</label>
                    <div class="flex flex-col xl:flex-row gap-3">
                        <input
                            v-model="commerceSearch"
                            @input="isReportGenerated = false"
                            class="flex-1 rounded-2xl border border-slate-200 px-4 py-3"
                            placeholder="Search ticket ID, buyer, event, item, VIP, spa, cookout, drinks, tables..."
                        />
                        <button @click="generateReport" class="rounded-2xl bg-slate-950 text-white px-8 py-3 font-black">Search / Generate</button>
                    </div>

                    <!-- Quick Search Results -->
                    <div v-if="quickSearchResults.length && !isReportGenerated" class="mt-5 overflow-x-auto scrollbar animate-in fade-in slide-in-from-top-2 duration-200">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="py-3">Sale ID</th>
                                    <th>Buyer</th>
                                    <th>Event</th>
                                    <th>Category</th>
                                    <th>Item</th>
                                    <th class="text-right">Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in quickSearchResults" :key="r.id" class="border-t hover:bg-slate-50 transition">
                                    <td class="py-3 font-black text-purple-600">{{ r.id }}</td>
                                    <td>{{ r.buyer }}</td>
                                    <td class="max-w-xs truncate">{{ r.event }}</td>
                                    <td><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black">{{ r.category }}</span></td>
                                    <td class="text-xs font-bold">{{ r.item }}</td>
                                    <td class="text-right font-black">{{ fmt(r.total) }}</td>
                                    <td><span class="rounded-full px-2 py-0.5 text-[10px] font-black" :class="r.status === 'Paid' ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-700'">{{ r.status }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card rounded-3xl overflow-hidden border border-purple-100">
                    <div class="bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white p-5 flex items-center gap-3">
                        <ClipboardList class="h-6 w-6" />
                        <h3 class="text-xl font-black">Generate Report</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 xl:grid-cols-4 gap-5">
                        <div class="xl:col-span-3">
                            <label class="font-bold text-slate-600 text-sm">From Date</label>
                            <input v-model="commerceFilters.from" type="date" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="font-bold text-slate-600 text-sm">To Date</label>
                            <input v-model="commerceFilters.to" type="date" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            <p v-if="!commerceFilters.to" class="text-rose-500 text-sm mt-2">The to field is required.</p>
                        </div>
                        <div class="xl:col-span-3">
                            <label class="font-bold text-slate-600 text-sm">Ticket Type / Sales Type</label>
                            <select v-model="commerceFilters.type" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 bg-white">
                                <option>All Types</option>
                                <option v-for="t in [...new Set(eventCommerceRecords.map(r => r.itemType))]" :key="t">{{ t }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-slate-600 text-sm">Event</label>
                            <select v-model="commerceFilters.event" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 bg-white">
                                <option>All Events</option>
                                <option v-for="e in [...new Set(eventCommerceRecords.map(r => r.event))]" :key="e">{{ e }}</option>
                            </select>
                        </div>
                        <div class="xl:col-span-3">
                            <label class="font-bold text-slate-600 text-sm">Country</label>
                            <select v-model="commerceFilters.country" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 bg-white">
                                <option>All Countries</option>
                                <option v-for="c in [...new Set(eventCommerceRecords.map(r => r.country))]" :key="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-slate-600 text-sm">Sales Category</label>
                            <select v-model="commerceFilters.category" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 bg-white">
                                <option>All Categories</option>
                                <option v-for="c in [...new Set(eventCommerceRecords.map(r => r.category))]" :key="c">{{ c }}</option>
                            </select>
                        </div>
                        <div class="xl:col-span-4 mt-2">
                            <button @click="generateReport" class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-6 py-4 font-black shadow-lg shadow-purple-200 flex items-center justify-center gap-2 hover:opacity-90 transition">
                                <ClipboardList class="w-5 h-5" /> Generate
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!isReportGenerated" class="card rounded-3xl p-16 text-center bg-slate-50 border-2 border-dashed border-slate-200">
                    <div class="h-20 w-20 rounded-full bg-white shadow-sm text-purple-600 grid place-items-center mx-auto mb-5">
                        <ClipboardList class="w-10 h-10" />
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Ready to Generate</h3>
                    <p class="text-slate-500 mt-2 max-w-sm mx-auto">Select your filters and click "Generate" to view the detailed commerce ledger and analytics.</p>
                </div>

                <div v-else class="space-y-6 animate-in fade-in duration-500">
                    <div class="card rounded-3xl p-6">
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-8">
                            <div>
                                <h3 class="text-2xl font-black">Report Results</h3>
                                <p class="text-slate-500">{{ filteredReportRows.length }} records matching your criteria</p>
                            </div>
                            <button @click="exportCSV" class="rounded-2xl bg-slate-950 text-white px-6 py-3 font-black flex items-center gap-2">
                                <Download class="w-5 h-5" /> Export CSV
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-slate-500 font-bold text-xs uppercase mb-1">Qty</p>
                                <h4 class="text-2xl font-black">{{ num(reportStats.qty) }}</h4>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-slate-500 font-bold text-xs uppercase mb-1">Subtotal</p>
                                <h4 class="text-2xl font-black">{{ fmt(reportStats.subtotal) }}</h4>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-slate-500 font-bold text-xs uppercase mb-1">Fees</p>
                                <h4 class="text-2xl font-black text-emerald-600">{{ fmt(reportStats.fee) }}</h4>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-slate-500 font-bold text-xs uppercase mb-1">Tax</p>
                                <h4 class="text-2xl font-black">{{ fmt(reportStats.tax) }}</h4>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-slate-500 font-bold text-xs uppercase mb-1">Refunds</p>
                                <h4 class="text-2xl font-black text-rose-600">{{ fmt(reportStats.refund) }}</h4>
                            </div>
                            <div class="rounded-2xl bg-purple-600 p-4 shadow-lg shadow-purple-100">
                                <p class="text-purple-100 font-bold text-xs uppercase mb-1">Total Paid</p>
                                <h4 class="text-2xl font-black text-white">{{ fmt(reportStats.total) }}</h4>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6 mb-8">
                            <div class="2xl:col-span-2 rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h3 class="text-lg font-black mb-6">Revenue by Category</h3>
                                <div class="relative h-[300px] w-full">
                                    <canvas ref="categoryChartRef"></canvas>
                                </div>
                            </div>
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h3 class="text-lg font-black mb-6">Status Mix</h3>
                                <div class="relative h-[300px] w-full">
                                    <canvas ref="statusChartRef"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6 mb-8">
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h3 class="text-lg font-black mb-4">Top Items Sold</h3>
                                <div class="space-y-3">
                                    <div v-for="x in topItems" :key="x.item" class="rounded-2xl bg-slate-50 p-4 flex justify-between items-center group hover:bg-slate-100 transition">
                                        <div>
                                            <b class="text-slate-800">{{ x.item }}</b>
                                            <p class="text-sm text-slate-500">{{ x.category }} • {{ num(x.qty) }} sold</p>
                                        </div>
                                        <span class="font-black text-lg">{{ fmt(x.total) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h3 class="text-lg font-black mb-4">Event Summary</h3>
                                <div class="space-y-3">
                                    <div v-for="x in eventDrilldown" :key="x.event" class="rounded-2xl bg-slate-50 p-4 group hover:bg-slate-100 transition">
                                        <div class="flex justify-between items-start mb-2">
                                            <b class="text-slate-800 flex-1 truncate mr-4">{{ x.event }}</b>
                                            <span class="font-black text-lg">{{ fmt(x.total) }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs font-bold text-slate-500">
                                            <span>Items: {{ num(x.qty) }}</span>
                                            <span class="text-emerald-600">Fees: {{ fmt(x.fees) }}</span>
                                            <span>Tax: {{ fmt(x.tax) }}</span>
                                            <span v-if="x.refund > 0" class="text-rose-600">Refunds: {{ fmt(x.refund) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="py-3 px-2">Sale ID</th>
                                        <th>Date</th>
                                        <th>Buyer</th>
                                        <th>Event</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right text-emerald-600">Fee</th>
                                        <th class="text-right">Tax</th>
                                        <th class="text-right text-rose-600">Refund</th>
                                        <th class="text-right">Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="r in filteredReportRows" :key="r.id" class="border-t hover:bg-slate-50 transition">
                                        <td class="py-3 px-2 font-black text-purple-600">{{ r.id }}</td>
                                        <td class="whitespace-nowrap">{{ r.date }}</td>
                                        <td>
                                            <div class="font-bold">{{ r.buyer }}</div>
                                            <div class="text-[10px] text-slate-400">{{ r.email }}</div>
                                        </td>
                                        <td class="max-w-[150px] truncate">{{ r.event }}</td>
                                        <td><span class="text-[10px] font-black uppercase text-slate-500">{{ r.category }}</span></td>
                                        <td class="font-medium">{{ r.itemType }}</td>
                                        <td class="font-medium">{{ r.item }}</td>
                                        <td class="text-right">{{ num(r.qty) }}</td>
                                        <td class="text-right">{{ fmt(r.subtotal) }}</td>
                                        <td class="text-right text-emerald-600 font-bold">{{ fmt(r.fee) }}</td>
                                        <td class="text-right">{{ fmt(r.tax) }}</td>
                                        <td class="text-right text-rose-600 font-bold">{{ fmt(r.refund) }}</td>
                                        <td class="text-right font-black">{{ fmt(r.total) }}</td>
                                        <td><span class="rounded-full px-2 py-0.5 text-[10px] font-black border" :class="r.status === 'Paid' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-rose-50 text-rose-700 border-rose-100'">{{ r.status }}</span></td>
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
