<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { BarChart3, TrendingUp, Clock, Users, MapPin } from 'lucide-vue-next';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

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

// Charts
const orderChartRef = ref<HTMLCanvasElement | null>(null);
const deliveryChartRef = ref<HTMLCanvasElement | null>(null);
let orderChart: any = null;
let deliveryChart: any = null;

const initCharts = () => {
    if (orderChartRef.value) {
        orderChart = new Chart(orderChartRef.value, {
            type: 'bar',
            data: {
                labels: ['Bahamas', 'Jamaica', 'Trinidad', 'Barbados'],
                datasets: [{ label: 'Orders', data: [7420, 5880, 3210, 1910], backgroundColor: '#F97316', borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    if (deliveryChartRef.value) {
        deliveryChart = new Chart(deliveryChartRef.value, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{ label: 'Avg Delivery Time (min)', data: [34, 31, 29, 31], borderColor: '#28A8FF', tension: 0.4, fill: true, backgroundColor: 'rgba(40, 168, 255, 0.1)' }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
};

onMounted(async () => {
    document.body.classList.add('new-admin-body');
    await nextTick();
    initCharts();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
    if (orderChart) orderChart.destroy();
    if (deliveryChart) deliveryChart.destroy();
});
</script>

<template>
    <Head title="Eats Analytics" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsAnalyticsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Analytics" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h3 class="text-3xl font-black text-orange-600">LinkUp Eats Analytics</h3>
                    <p class="text-slate-500 font-medium mt-1">Executive performance metrics for orders, logistics, and retention.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="card rounded-[32px] p-8 border-slate-100 shadow-sm bg-white flex flex-col justify-between">
                        <div>
                            <div class="h-10 w-10 rounded-xl bg-orange-100 text-orange-600 grid place-items-center mb-4"><BarChart3 class="w-5 h-5" /></div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Orders This Month</p>
                        </div>
                        <h3 class="text-4xl font-black text-slate-900 mt-2">18,420</h3>
                    </div>
                    <div class="card rounded-[32px] p-8 border-slate-100 shadow-sm bg-white flex flex-col justify-between">
                        <div>
                            <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-600 grid place-items-center mb-4"><TrendingUp class="w-5 h-5" /></div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Avg Order Value</p>
                        </div>
                        <h3 class="text-4xl font-black text-slate-900 mt-2">$28.75</h3>
                    </div>
                    <div class="card rounded-[32px] p-8 border-slate-100 shadow-sm bg-white flex flex-col justify-between">
                        <div>
                            <div class="h-10 w-10 rounded-xl bg-sky-100 text-sky-600 grid place-items-center mb-4"><Clock class="w-5 h-5" /></div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Avg Delivery Time</p>
                        </div>
                        <h3 class="text-4xl font-black text-slate-900 mt-2">31m</h3>
                    </div>
                    <div class="card rounded-[32px] p-8 border-slate-100 shadow-sm bg-white flex flex-col justify-between">
                        <div>
                            <div class="h-10 w-10 rounded-xl bg-purple-100 text-purple-600 grid place-items-center mb-4"><Users class="w-5 h-5" /></div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Repeat Customers</p>
                        </div>
                        <h3 class="text-4xl font-black text-slate-900 mt-2">64%</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">Orders by Country</h4>
                        <div class="h-[300px] w-full relative">
                            <canvas ref="orderChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">Delivery Efficiency Trend</h4>
                        <div class="h-[300px] w-full relative">
                            <canvas ref="deliveryChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">Orders by Country</h4>
                        <div class="space-y-4">
                            <div v-for="c in [['Bahamas', '7,420'], ['Jamaica', '5,880'], ['Trinidad', '3,210'], ['Barbados', '1,910']]" :key="c[0]" class="flex justify-between items-center py-3 border-b border-slate-50 last:border-0">
                                <div class="flex items-center gap-3"><MapPin class="w-4 h-4 text-slate-300" /><b class="text-slate-700">{{ c[0] }}</b></div>
                                <b class="text-slate-900">{{ c[1] }}</b>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">Top Restaurants</h4>
                        <div class="space-y-4">
                            <div v-for="r in [['Bahama Grill', '$12,450'], ['Island Jerk Kitchen', '$8,930'], ['Trini Flavors', '$6,215']]" :key="r[0]" class="flex justify-between items-center py-3 border-b border-slate-50 last:border-0">
                                <b class="text-slate-700">{{ r[0] }}</b>
                                <b class="text-emerald-600 font-black">{{ r[1] }}</b>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">Driver Performance</h4>
                        <div class="space-y-4">
                            <div v-for="p in [['On-Time Rate', '92%'], ['Cancelled Deliveries', '1.8%'], ['Avg Rating', '4.8 ★']]" :key="p[0]" class="flex justify-between items-center py-3 border-b border-slate-50 last:border-0">
                                <span class="text-slate-500 font-bold">{{ p[0] }}</span>
                                <b class="text-slate-900 font-black">{{ p[1] }}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
