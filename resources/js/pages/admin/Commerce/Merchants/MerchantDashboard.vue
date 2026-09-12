<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, Link } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { Store, ExternalLink, Download, ArrowUpRight } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Prototype Stats
const stats = ref({
    gtv: '$2,842,500',
    fees: '$42,800',
    active: 1240,
    pending: 12,
    kycPct: '92%',
    chargeback: '0.42%'
});

const topMerchants = [
    { name: 'SuperValue Nassau', country: 'Bahamas', cat: 'Grocery', volume: '$420,000', growth: 22 },
    { name: 'Kingston Gas Mart', country: 'Jamaica', cat: 'Gas', volume: '$380,000', growth: 18 },
    { name: 'Caribbean Pharmacy', country: 'Trinidad', cat: 'Pharmacy', volume: '$260,000', growth: 31 },
    { name: 'Toronto Island Store', country: 'Canada', cat: 'Retail', volume: '$310,000', growth: 14 }
];

// Charts
const gtvChartRef = ref<HTMLCanvasElement | null>(null);
const catChartRef = ref<HTMLCanvasElement | null>(null);
const charts = ref<any>({});

const renderCharts = () => {
    if (gtvChartRef.value) {
        charts.value.gtv = new Chart(gtvChartRef.value, {
            type: 'bar',
            data: {
                labels: ['Bahamas', 'Jamaica', 'Trinidad', 'Barbados', 'Guyana', 'DR'],
                datasets: [{ label: 'GTV', data: [1000000, 720000, 610000, 330000, 420000, 520000], backgroundColor: '#06B6D4', borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    if (catChartRef.value) {
        charts.value.cat = new Chart(catChartRef.value, {
            type: 'doughnut',
            data: {
                labels: ['Grocery', 'Gas', 'Pharmacy', 'Retail', 'Electronics'],
                datasets: [{ data: [40, 20, 15, 15, 10], backgroundColor: ['#06B6D4', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }
};

const headerMetrics = computed(() => ({
    gtv: stats.value.gtv,
    revenue: '$840k',
    bank: '$2.1M',
    net: '$420k',
    users: '1.2M',
    merchants: stats.value.active.toString(),
    organizers: '2.1k',
    countries: '15'
}));

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderCharts();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
    Object.values(charts.value).forEach((c: any) => c && c.destroy());
});
</script>

<template>
    <Head title="Merchant Dashboard" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="merchants" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Merchant Dashboard" :countries="[]" :metrics="headerMetrics" @toggle-sidebar="toggleSidebar" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-cyan-600">Merchant Operating System</h3>
                        <p class="text-slate-500 font-medium">Track, onboard, verify, and settle merchants across Latin America & the Caribbean.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button class="rounded-2xl border border-cyan-200 bg-cyan-50 text-cyan-700 px-5 py-3 font-black flex items-center gap-2 transition hover:bg-cyan-100 shadow-sm">
                            <ExternalLink class="w-4 h-4" /> Merchant Portal
                        </button>
                        <button class="rounded-2xl bg-slate-950 text-white px-5 py-3 font-black flex items-center gap-2 shadow-lg active:scale-95 transition">
                            <Download class="w-4 h-4" /> Export
                        </button>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                    <div class="card metric dark rounded-3xl p-5 shadow-xl"><p class="text-slate-300 text-xs font-black uppercase tracking-widest">Merchant GTV</p><h3 class="text-2xl font-black mt-1">{{ stats.gtv }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-slate-500 text-xs font-black uppercase tracking-widest">LinkUp Fees</p><h3 class="text-2xl font-black mt-1">{{ stats.fees }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-slate-500 text-xs font-black uppercase tracking-widest">Active Merchants</p><h3 class="text-2xl font-black mt-1">{{ stats.active }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-amber-600 text-xs font-black uppercase tracking-widest">Pending</p><h3 class="text-2xl font-black mt-1">{{ stats.pending }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-emerald-600 text-xs font-black uppercase tracking-widest">KYC Verified</p><h3 class="text-2xl font-black mt-1">{{ stats.kycPct }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-rose-600 text-xs font-black uppercase tracking-widest">Avg Chargeback</p><h3 class="text-2xl font-black mt-1">{{ stats.chargeback }}</h3></div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <!-- Revenue by Region -->
                    <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                        <h4 class="font-black text-slate-800 mb-1">Revenue by Region</h4>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-6">Latin America vs. Caribbean</p>
                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between text-sm font-black text-slate-700 mb-2"><span>Caribbean</span><span>$1,842,000</span></div>
                                <div class="h-3 rounded-full bg-slate-50 overflow-hidden"><div class="h-full bg-cyan-500" style="width: 85%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm font-black text-slate-700 mb-2"><span>Latin America</span><span>$1,000,500</span></div>
                                <div class="h-3 rounded-full bg-slate-50 overflow-hidden"><div class="h-full bg-violet-500" style="width: 45%"></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="xl:col-span-2 card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                        <h4 class="font-black text-slate-800 mb-4">Merchant GTV by Country</h4>
                        <div class="h-[220px] w-full"><canvas ref="gtvChartRef"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                        <h4 class="font-black text-slate-800 mb-4">Revenue by Category</h4>
                        <div class="h-[250px] w-full"><canvas ref="catChartRef"></canvas></div>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                        <h4 class="font-black text-slate-800 mb-4">Top Merchants</h4>
                        <div class="space-y-3">
                            <div v-for="m in topMerchants" :key="m.name" class="rounded-2xl bg-slate-50 p-4 flex justify-between items-center transition hover:bg-slate-100 cursor-pointer">
                                <div>
                                    <b class="text-slate-800">{{ m.name }}</b>
                                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1">{{ m.country }} • {{ m.cat }} • <span class="text-emerald-500">+{{ m.growth }}%</span></p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-black text-slate-900 text-lg">{{ m.volume }}</span>
                                    <ArrowUpRight class="w-4 h-4 text-slate-300" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
