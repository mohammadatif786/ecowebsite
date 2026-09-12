<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Bike, Package, Utensils, TrendingUp, Star } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model matching the HTML prototype exactly
const deliveries = ref([
    { id: 'DLV-5012', kind: 'Eats', pickup: 'Island Grill Nassau', drop: 'Aaliyah Clarke · Nassau', driver: 'Devon Rolle', km: 4.2, payout: 8.20, status: 'Delivered' },
    { id: 'DLV-5013', kind: 'Marketplace', pickup: 'Island Produce Supply · Eleuthera', drop: 'Marcus Johnson · Nassau', driver: 'Tamika Bain', km: 6.1, payout: 7.50, status: 'En route' },
    { id: 'DLV-5014', kind: 'Marketplace', pickup: 'Straw Market Co · Nassau', drop: 'Brianna Smith · Cable Beach', driver: '—', km: 3.4, payout: 6.50, status: 'Finding driver' },
    { id: 'DLV-5015', kind: 'Eats', pickup: 'Kingston Jerk Hub', drop: 'Andre Blake · Kingston', driver: 'Andre Wilson', km: 3.7, payout: 7.30, status: 'Picked up' },
    { id: 'DLV-5016', kind: 'Marketplace', pickup: 'Caribbean Beverage Depot', drop: 'Maya Evans · Miami', driver: 'Luis Carter', km: 7.4, payout: 9.10, status: 'Accepted' },
    { id: 'DLV-5017', kind: 'Eats', pickup: 'Conch Shack Express', drop: 'Jordan Rolle · Nassau', driver: 'Devon Rolle', km: 5.0, payout: 8.00, status: 'Delivered' },
    { id: 'DLV-5018', kind: 'Marketplace', pickup: 'Tech Hub TT · Port of Spain', drop: 'Tanya Baptiste · San Fernando', driver: 'Kevin Singh', km: 9.2, payout: 11.20, status: 'En route' }
]);

const drivers = ref([
    { name: 'Devon Rolle', city: 'Nassau', online: true, serves: 'Eats + Market', today: 42.00, rating: 4.8 },
    { name: 'Tamika Bain', city: 'Nassau', online: true, serves: 'Eats + Market', today: 31.50, rating: 4.7 },
    { name: 'Luis Carter', city: 'Miami', online: true, serves: 'Eats + Market', today: 58.00, rating: 4.9 },
    { name: 'Kevin Singh', city: 'Port of Spain', online: true, serves: 'Market only', today: 24.00, rating: 4.6 },
    { name: 'Andre Wilson', city: 'Toronto', online: false, serves: 'Eats + Market', today: 0, rating: 4.7 }
]);

const stats = computed(() => {
    const eats = deliveries.value.filter(d => d.kind === 'Eats').length;
    const mkt = deliveries.value.filter(d => d.kind === 'Marketplace').length;
    const online = drivers.value.filter(d => d.online).length;
    const finding = deliveries.value.filter(d => d.status === 'Finding driver').length;
    return { total: deliveries.value.length, eats, mkt, online, finding };
});

const getStatusBadgeStyle = (status: string) => {
    const maps: Record<string, string[]> = {
        'Delivered': ['#16a34a', '#dcfce7'],
        'En route': ['#0891b2', '#cffafe'],
        'Picked up': ['#2563eb', '#dbeafe'],
        'Accepted': ['#7c3aed', '#ede9fe'],
        'Finding driver': ['#b45309', '#fef3c7']
    };
    const c = maps[status] || ['#475569', '#f1f5f9'];
    return { color: c[0], background: c[1] };
};

const getKindBadgeStyle = (kind: string) => {
    const c = kind === 'Eats' ? ['#16a34a', '#dcfce7'] : ['#7c3aed', '#ede9fe'];
    return { color: c[0], background: c[1], emoji: kind === 'Eats' ? '🍽️' : '📦' };
};

const headerMetrics = computed(() => ({
    gtv: '$14.2M',
    revenue: '$840k',
    bank: '$2.1M',
    net: '$420k',
    users: '1.2M',
    merchants: '12.4k',
    organizers: '2.1k',
    countries: '15'
}));

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Deliveries Dispatch" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="dispatchCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Deliveries Dispatch"
                :countries="[]"
                :metrics="headerMetrics"
                @toggle-sidebar="toggleSidebar"
            />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h2 class="text-[28px] font-black text-slate-900 leading-tight flex items-center gap-3">
                        🛵 Deliveries Dispatch
                    </h2>
                    <p class="text-slate-600 font-medium mt-1">
                        One unified driver fleet — an Uber-style pool that delivers <b>both LinkUp Eats orders and Marketplace parcels</b>. Drivers see food and marketplace jobs in the same queue.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-[14px]">
                    <div class="card rounded-3xl p-5 bg-white border border-[#eef2f7] shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Active Deliveries</p>
                        <h3 class="text-[26px] font-black mt-1 text-slate-800">{{ stats.total }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5 bg-white border border-[#eef2f7] shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">🍽️ Eats / 📦 Market</p>
                        <h3 class="text-[26px] font-black mt-1 text-slate-800">{{ stats.eats }} / {{ stats.mkt }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5 bg-white border border-[#eef2f7] shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Drivers Online</p>
                        <h3 class="text-[26px] font-black mt-1 text-slate-800 flex items-center gap-2">
                            {{ stats.online }} <span class="text-xs font-black text-emerald-500 uppercase tracking-tighter">● live</span>
                        </h3>
                    </div>
                    <div class="card rounded-3xl p-5 bg-white border border-[#eef2f7] shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Awaiting Driver</p>
                        <h3 class="text-[26px] font-black mt-1" :class="stats.finding > 0 ? 'text-[#b45309]' : 'text-emerald-500'">{{ stats.finding }}</h3>
                    </div>
                </div>

                <div class="card rounded-[18px] bg-white border border-[#eef2f7] shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-[#eef2f7]">
                        <h3 class="font-black text-slate-800 uppercase tracking-widest text-[11px] opacity-70">Live Deliveries — Food & Marketplace</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#f8fafc] text-[12px] font-black uppercase text-slate-500 tracking-[.04em]">
                                <tr>
                                    <th class="p-3">ID</th>
                                    <th class="p-3">Type</th>
                                    <th class="p-3">Pickup → Drop-off</th>
                                    <th class="p-3">Driver</th>
                                    <th class="p-3">Dist</th>
                                    <th class="p-3 text-right">Payout</th>
                                    <th class="p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#eef2f7]">
                                <tr v-for="d in deliveries" :key="d.id" class="hover:bg-slate-50 transition">
                                    <td class="p-3 font-mono text-[12px] text-slate-400">{{ d.id }}</td>
                                    <td class="p-3">
                                        <span :style="{ background: getKindBadgeStyle(d.kind).background, color: getKindBadgeStyle(d.kind).color }" class="font-black text-[11px] px-[9px] py-[3px] rounded-full whitespace-nowrap">
                                            {{ getKindBadgeStyle(d.kind).emoji }} {{ d.kind }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-[13px]">
                                        <b class="text-slate-800 block">{{ d.pickup }}</b>
                                        <div class="text-[12px] text-slate-400 leading-tight mt-0.5">→ {{ d.drop }}</div>
                                    </td>
                                    <td class="p-3 text-[13px]">
                                        <span v-if="d.driver === '—'" class="text-[#b45309] font-black flex items-center gap-1.5"><TrendingUp class="w-3.5 h-3.5" /> Dispatching...</span>
                                        <span v-else class="text-slate-700 font-bold">{{ d.driver }}</span>
                                    </td>
                                    <td class="p-3 text-[13px] font-bold text-slate-500">{{ d.km }} km</td>
                                    <td class="p-3 text-right font-black text-slate-900">${{ d.payout.toFixed(2) }}</td>
                                    <td class="p-3">
                                        <span :style="{ background: getStatusBadgeStyle(d.status).background, color: getStatusBadgeStyle(d.status).color }" class="font-black text-[11px] px-[9px] py-[3px] rounded-full">
                                            {{ d.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card rounded-[18px] bg-white border border-[#eef2f7] shadow-sm overflow-hidden mt-6">
                    <div class="p-4 border-b border-[#eef2f7] flex items-center justify-between">
                        <h3 class="font-black text-slate-800 uppercase tracking-widest text-[11px] opacity-70">Driver Fleet</h3>
                        <span class="text-[12px] font-semibold text-slate-400">one pool serves both services</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#f8fafc] text-[12px] font-black uppercase text-slate-500 tracking-[.04em]">
                                <tr>
                                    <th class="p-3">Driver</th>
                                    <th class="p-3">City</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Serves</th>
                                    <th class="p-3">Today</th>
                                    <th class="p-3">Rating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#eef2f7]">
                                <tr v-for="dr in drivers" :key="dr.name" class="hover:bg-slate-50 transition">
                                    <td class="p-3 font-black text-slate-800 text-[14px]">{{ dr.name }}</td>
                                    <td class="p-3 text-[14px] text-slate-600 font-medium">{{ dr.city }}</td>
                                    <td class="p-3">
                                        <span v-if="dr.online" class="text-[#16a34a] font-black text-[13px]">● Online</span>
                                        <span v-else class="text-slate-400 font-black text-[13px]">Offline</span>
                                    </td>
                                    <td class="p-3 text-[13px]">
                                        <span v-if="dr.serves === 'Market only'" class="text-[#7c3aed] font-bold">📦 Market only</span>
                                        <span v-else class="text-[#0f766e] font-bold">🍽️📦 Eats + Market</span>
                                    </td>
                                    <td class="p-3 font-black text-slate-900 text-[14px]">${{ dr.today.toFixed(2) }}</td>
                                    <td class="p-3 flex items-center gap-1 text-slate-600 font-bold text-[13px]">
                                        <Star class="w-4 h-4 fill-amber-400 text-amber-400" /> {{ dr.rating }}
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
