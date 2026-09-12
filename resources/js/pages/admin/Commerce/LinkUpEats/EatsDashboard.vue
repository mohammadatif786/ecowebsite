<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, Link } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { RefreshCw, Megaphone, Smartphone, Utensils } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model (From HTML)
const SCOTIA_SHARE = 0.4;
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

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

// Eats Live State (Mocked)
const eatsLiveState = ref({
    restaurants: [1, 2, 3, 4],
    menu: [1, 2, 3, 4, 5, 6, 7, 8],
    drivers: [1, 2, 3, 4, 5],
    orders: [
        { id: 'ORD-8812', restaurant: 'Bahama Grill', type: 'Delivery', city: 'Nassau', total: 42.50, status: 'Completed', deliveryStatus: 'Delivered', currencyCode: 'BSD' },
        { id: 'ORD-8813', restaurant: 'Island Jerk', type: 'Pickup', city: 'Montego Bay', total: 18.75, status: 'Ready', deliveryStatus: '—', currencyCode: 'JMD' },
        { id: 'ORD-8814', restaurant: 'Trini Flavors', type: 'Delivery', city: 'Port of Spain', total: 31.20, status: 'Pending', deliveryStatus: 'Driver Assigned', currencyCode: 'TTD' },
    ]
});

const stats = computed(() => {
    const s = getScale();
    return {
        gross: 428650 * s,
        orders: 18420 * s,
        fees: 51438 * s,
        driverPay: 22620 * s,
        payouts: 351494 * s,
        tax: 38420 * s,
        tips: 11620 * s,
        refunds: 4200 * s,
        avgDelivery: 31
    };
});

const ribbonMetrics = computed(() => {
    const s = getScale();
    const t = countries.reduce((a, c) => {
        a.gtv += (c.tickets + c.subscriptions + c.marketplace + c.eats + c.merchantPay);
        a.users += c.users;
        a.merchants += c.merchants;
        a.organizers += c.organizers;
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    return {
        gtv: fmt(t.gtv * s),
        linkupRev: fmt(t.gtv * s * 0.12),
        procPool: fmt(t.gtv * s * 0.03),
        netProfit: fmt(t.gtv * s * 0.09),
        users: num(t.users * s),
        merchants: num(t.merchants),
        organizers: num(t.organizers),
        countries: num(countries.length)
    };
});

// Charts
const charts = ref<any>({});
const countryChartRef = ref<HTMLCanvasElement | null>(null);
const statusChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const countryCtx = countryChartRef.value;
    if (countryCtx) {
        charts.value.country = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: ['Bahamas', 'Jamaica', 'Trinidad', 'Barbados'],
                datasets: [
                    { label: 'Gross Sales', data: [185000, 142000, 92000, 48000], backgroundColor: '#28A8FF', borderRadius: 8 },
                    { label: 'LinkUp Fees', data: [22200, 17040, 11040, 5760], backgroundColor: '#D9EC10', borderRadius: 8 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    const statusCtx = statusChartRef.value;
    if (statusCtx) {
        charts.value.status = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Preparing', 'Picked Up', 'Cancelled', 'Refunded'],
                datasets: [{ data: [1248, 41, 22, 12, 7], backgroundColor: ['#00C853', '#F59E0B', '#28A8FF', '#F43F5E', '#8B5CF6'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
};

const renderAll = async () => {
    await nextTick();
    const t = countries.reduce((a, c) => { a.gross += c.tickets; return a; }, { gross: 0 });
    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('sideGTV', fmt(t.gross * getScale()));
    renderCharts();
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
    <Head title="LinkUp Eats Dashboard" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="LinkUp Eats Dashboard" :countries="countries" :metrics="ribbonMetrics" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <!-- Live Sync Box -->
                <div class="card rounded-3xl p-6 border-2 border-[#9EDB2F]">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-xl font-black text-[#124d71] flex items-center gap-2">
                                <RefreshCw class="w-5 h-5" /> Live Sync — from the Eats App
                            </h3>
                            <p class="text-slate-500 text-sm">Real-time view of what's happening in the front-end Eats App. Updates automatically.</p>
                        </div>
                        <div class="flex gap-2">
                            <button class="rounded-2xl bg-gradient-to-r from-indigo-600 to-pink-500 text-white px-4 py-2.5 font-black flex items-center gap-2">
                                <Megaphone class="w-4 h-4" /> Advertise
                            </button>
                            <Link :href="route('admin.commerce.eats.live-app')" class="rounded-2xl bg-[#124d71] text-white px-4 py-2.5 font-black flex items-center gap-2">
                                <Smartphone class="w-4 h-4" /> Open Eats App
                            </Link>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
                        <div class="card rounded-2xl p-4 bg-slate-50">
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Restaurants</p>
                            <h3 class="text-2xl font-black">{{ num(eatsLiveState.restaurants.length) }}</h3>
                        </div>
                        <div class="card rounded-2xl p-4 bg-slate-50">
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Menu Items</p>
                            <h3 class="text-2xl font-black">{{ num(eatsLiveState.menu.length) }}</h3>
                        </div>
                        <div class="card rounded-2xl p-4 bg-slate-50">
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Drivers</p>
                            <h3 class="text-2xl font-black">{{ num(eatsLiveState.drivers.length) }}</h3>
                        </div>
                        <div class="card rounded-2xl p-4 bg-white border-2 border-sky-100">
                            <p class="text-sky-600 text-xs font-bold uppercase tracking-wider">Live Orders</p>
                            <h3 class="text-2xl font-black text-[#124d71]">{{ num(eatsLiveState.orders.length) }}</h3>
                        </div>
                        <div class="card rounded-2xl p-4 bg-emerald-50 border border-emerald-100">
                            <p class="text-emerald-600 text-xs font-bold uppercase tracking-wider">Order Revenue</p>
                            <h3 class="text-2xl font-black text-emerald-600">$1,420</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-slate-400 font-black tracking-widest">
                                <tr>
                                    <th class="py-2">Order</th>
                                    <th>Restaurant</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Delivery</th>
                                </tr>
                            </thead>
                            <tbody class="font-bold text-slate-700">
                                <tr v-for="o in eatsLiveState.orders" :key="o.id" class="border-t border-slate-50">
                                    <td class="py-2 font-mono text-xs">{{ o.id }}</td>
                                    <td>{{ o.restaurant }}</td>
                                    <td>{{ o.type }}</td>
                                    <td>{{ o.city }}</td>
                                    <td class="font-black">{{ o.currencyCode }} {{ o.total }}</td>
                                    <td><span class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase" :class="o.status === 'Completed' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">{{ o.status }}</span></td>
                                    <td><span class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase" :class="o.deliveryStatus === 'Delivered' ? 'bg-green-50 text-green-700' : 'bg-sky-50 text-sky-700'">{{ o.deliveryStatus }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Metrics Row 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2 shadow-xl shadow-slate-200">
                        <p class="text-slate-300 font-bold">LinkUp Eats Gross Sales</p>
                        <h3 class="text-5xl font-black mt-2">{{ fmt(stats.gross) }}</h3>
                        <p class="text-lime-300 font-bold mt-1">Food, tax, delivery, service, tips</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Total Orders</p>
                        <h3 class="text-4xl font-black mt-1">{{ num(stats.orders) }}</h3>
                        <p class="text-sky-500 font-bold">All Eats orders</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">LinkUp Fees</p>
                        <h3 class="text-4xl font-black mt-1">{{ fmt(stats.fees) }}</h3>
                        <p class="text-green-500 font-bold">Service + commission</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Driver Pay</p>
                        <h3 class="text-4xl font-black mt-1">{{ fmt(stats.driverPay) }}</h3>
                        <p class="text-purple-500 font-bold">Driver earnings + tips</p>
                    </div>
                </div>

                <!-- Metrics Row 2 -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold">Restaurant Payouts</p><h3 class="text-3xl font-black mt-1">{{ fmt(stats.payouts) }}</h3><p class="text-amber-500 font-bold text-xs">Merchant net</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold">Tax / VAT</p><h3 class="text-3xl font-black mt-1">{{ fmt(stats.tax) }}</h3><p class="text-sky-500 font-bold text-xs">Collected tax</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold">Tips</p><h3 class="text-3xl font-black mt-1">{{ fmt(stats.tips) }}</h3><p class="text-green-500 font-bold text-xs">Customer tips</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold">Refunds</p><h3 class="text-3xl font-black mt-1">{{ fmt(stats.refunds) }}</h3><p class="text-rose-500 font-bold text-xs">Refund exposure</p></div>
                    <div class="card rounded-3xl p-5 border border-slate-100"><p class="text-slate-500 font-bold">Avg Delivery Time</p><h3 class="text-3xl font-black mt-1">{{ stats.avgDelivery }}m</h3><p class="text-purple-500 font-bold text-xs">Completed orders</p></div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
                    <div class="2xl:col-span-2 card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-4">Eats Revenue by Country</h3>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="countryChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-4">Order Status Mix</h3>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="statusChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <!-- Restaurant Reporting -->
                    <div class="card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-4">Restaurant / Merchant Reporting</h3>
                        <div class="space-y-3">
                            <div v-for="r in [['Bahama Grill', 542, 12450, 10334], ['Island Jerk', 411, 8930, 7411], ['Trini Flavors', 326, 6215, 5158]]" :key="r[0]" class="rounded-2xl bg-white border border-slate-100 p-4 flex justify-between shadow-sm hover:shadow-md transition">
                                <div><b class="text-slate-800">{{ r[0] }}</b><p class="text-sm text-slate-500">{{ r[1] }} orders • Best item: Grilled Salmon</p></div>
                                <div class="text-right"><b class="text-slate-900">{{ fmt(Number(r[2])) }}</b><p class="text-xs text-green-600 font-bold">Payout {{ fmt(Number(r[3])) }}</p></div>
                            </div>
                        </div>
                    </div>
                    <!-- Driver Pay Report -->
                    <div class="card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-4">Driver Pay Report</h3>
                        <div class="space-y-3">
                            <div v-for="d in [['Devon Rolle', 182, 340, 'WAL-DRV-001'], ['Tamika Bain', 126, 260, 'WAL-DRV-002'], ['Andre Wilson', 145, 610, 'WAL-DRV-006']]" :key="d[0]" class="rounded-2xl bg-white border border-slate-100 p-4 flex justify-between shadow-sm hover:shadow-md transition">
                                <div><b class="text-slate-800">{{ d[0] }}</b><p class="text-sm text-slate-500">{{ d[1] }} deliveries • 4.8 rating • {{ d[3] }}</p></div>
                                <div class="text-right"><b class="text-slate-900 text-lg">{{ fmt(Number(d[2])) }}</b><p class="text-xs text-slate-400 font-bold uppercase tracking-tighter">Pending payout</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <!-- Delivery Operations -->
                    <div class="card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-4">Delivery Operations</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="op in [['Completed Deliveries', '1,248'], ['Pending / Assigned', '18'], ['Average Prep Time', '18m'], ['Average Distance', '4.2 mi'], ['On-Time Orders', '1,120'], ['Late / Refunded Orders', '7']]" :key="op[0]" class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">{{ op[0] }}</p>
                                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ op[1] }}</h3>
                            </div>
                        </div>
                    </div>
                    <!-- Rules Section -->
                    <div class="card rounded-3xl p-6 border-slate-100">
                        <h3 class="text-xl font-black mb-4">Refunds / Disputes / Settlement Rules</h3>
                        <div class="space-y-3">
                            <div v-for="rule in [['Driver pay formula', 'Base pay + distance pay + time bonus + peak bonus + customer tip.'], ['Restaurant settlement', 'Food subtotal + gratuity minus LinkUp commission, refunds, and holds.'], ['Tax reporting', 'Tax/VAT is tracked by jurisdiction and pushed into the Tax Command Center.'], ['Refund responsibility', 'Missing/wrong item may charge restaurant; late delivery may charge LinkUp or driver.'], ['Driver payout', 'Driver earnings go to wallet first, then optional bank cash-out.']]" :key="rule[0]" class="rounded-2xl bg-sky-50/50 border border-sky-100 p-4">
                                <b class="text-slate-800">{{ rule[0] }}</b>
                                <p class="text-sm text-slate-600 mt-1 font-medium">{{ rule[1] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
