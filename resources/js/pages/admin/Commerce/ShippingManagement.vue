<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { PackagePlus, Truck, MapPin, Search, Printer, History } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model from HTML Prototype
const carriers = ref([
    { key: 'usps', name: 'USPS', logo: '📮', enabled: true, apiKey: '', apiSecret: '', account: '•••• 5530', meter: '—', test: true, countries: 'US', services: 'Ground Advantage · Priority Mail · Priority Mail Express' },
    { key: 'fedex', name: 'FedEx', logo: '📦', enabled: true, apiKey: '', apiSecret: '', account: '•••• 4821', meter: '1188••••', test: true, countries: 'US, CA', services: 'Ground · 2Day · Overnight' },
    { key: 'ups', name: 'UPS', logo: '🚚', enabled: true, apiKey: '', apiSecret: '', account: '•••• 907A', meter: '—', test: true, countries: 'US, CA', services: 'Ground · 3 Day Select · Next Day Air' }
]);

const shipments = ref([
    { id: 'SHP-7001', carrier: 'FedEx', logo: '📦', service: 'FedEx Ground', order: 'MKT-3055 · Utopia Bed Pillows', to: 'Maya Evans · Miami, FL', rate: 12.99, track: 'FE100482113', status: 'In transit' },
    { id: 'SHP-7002', carrier: 'UPS', logo: '🚚', service: 'UPS Next Day Air', order: 'MKT-3061 · Handmade Straw Bag', to: 'Natalie Brown · Toronto, ON', rate: 52.00, track: '1Z9072AA118', status: 'Out for delivery' },
    { id: 'SHP-7003', carrier: 'FedEx', logo: '📦', service: 'FedEx 2Day', order: 'MKT-3068 · Caribbean Spice Set', to: 'Andre P. · New York, NY', rate: 24.99, track: 'FE100482977', status: 'Delivered' },
    { id: 'SHP-7004', carrier: 'UPS', logo: '🚚', service: 'UPS Ground', order: 'MKT-3072 · Steelpan Mini', to: 'Kayla R. · Brooklyn, NY', rate: 11.49, track: '1Z9072AA204', status: 'Label created' },
    { id: 'SHP-7005', carrier: 'USPS', logo: '📮', service: 'USPS Priority Mail', order: 'MKT-3079 · Caribbean Cookbook', to: 'Renee B. · Orlando, FL', rate: 14.99, track: '9400110200881234', status: 'In transit' }
]);

const countriesData = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
];

const toastMessage = ref('');
const showToast = (msg: string) => {
    toastMessage.value = msg;
    setTimeout(() => { toastMessage.value = ''; }, 2000);
};

const toggleCarrier = (key: string) => {
    const c = carriers.value.find(x => x.key === key);
    if (c) {
        c.enabled = !c.enabled;
        showToast(`${c.name} ${c.enabled ? 'enabled' : 'disabled'}`);
    }
};

const saveCarrier = (key: string) => {
    showToast(`✅ ${key.toUpperCase()} API credentials saved`);
};

const getStatusBadgeClass = (status: string) => {
    const maps: Record<string, string> = {
        'In transit': 'bg-cyan-50 text-cyan-600',
        'Out for delivery': 'bg-blue-50 text-blue-600',
        'Delivered': 'bg-emerald-50 text-emerald-600',
        'Label created': 'bg-amber-50 text-amber-700'
    };
    return maps[status] || 'bg-slate-50 text-slate-600';
};

// Mock metrics for the header
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

const filters = ref({ region: 'All', country: 'All Countries', period: 'Today' });
const handleFilterChange = (newFilters: any) => { filters.value = newFilters; };

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Shipping — USPS, FedEx & UPS" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="shippingCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Shipping (USPS/FedEx/UPS)"
                :countries="countriesData"
                :metrics="headerMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <section class="p-5 lg:p-8 space-y-6">
                <!-- Toast Notification -->
                <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="toastMessage" class="fixed left-1/2 -translate-x-1/2 bottom-10 z-[100] bg-slate-900 text-white px-6 py-3 rounded-full font-black shadow-2xl">
                        {{ toastMessage }}
                    </div>
                </transition>

                <div>
                    <h2 class="text-3xl font-black gradient-title flex items-center gap-3">
                        📦 Shipping — USPS, FedEx & UPS
                    </h2>
                    <p class="text-slate-500 font-medium mt-2 max-w-4xl">
                        Carrier shipping for buyers in the <b>US & Canada</b>, where LinkUp drivers don’t operate. Configure the USPS, FedEx and UPS APIs here; live rates appear in the buyer’s checkout and labels/tracking are generated automatically. Caribbean & LatAm orders continue to use <b>LinkUp Drivers</b>.
                    </p>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Carriers Enabled</p>
                        <h3 class="text-3xl font-black mt-1 text-slate-800">
                            {{ carriers.filter(c => c.enabled).length }} / {{ carriers.length }}
                        </h3>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Active Shipments</p>
                        <h3 class="text-3xl font-black mt-1 text-slate-800">{{ shipments.length }}</h3>
                    </div>
                    <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Coverage</p>
                        <h3 class="text-2xl font-black mt-1 text-slate-800 flex items-center gap-2">
                            🇺🇸 US · 🇨🇦 CA
                        </h3>
                    </div>
                </div>

                <!-- Carrier API Cards -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div v-for="c in carriers" :key="c.key" class="card rounded-[32px] p-6 bg-white border-slate-100 shadow-sm space-y-5">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xl font-black text-slate-800">{{ c.logo }} {{ c.name }}</h4>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" :checked="c.enabled" @change="toggleCarrier(c.key)" class="h-5 w-5 rounded-lg accent-emerald-500">
                                <span class="text-xs font-black uppercase tracking-widest" :class="c.enabled ? 'text-emerald-500' : 'text-slate-400'">
                                    {{ c.enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">API Key</label>
                                <input v-model="c.apiKey" :placeholder="c.name + ' API Key'" class="w-full rounded-xl bg-slate-50 border-transparent px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-sky-500 transition">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">API Secret</label>
                                <input type="password" v-model="c.apiSecret" placeholder="••••••••" class="w-full rounded-xl bg-slate-50 border-transparent px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-sky-500 transition">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">Account #</label>
                                <input v-model="c.account" class="w-full rounded-xl bg-slate-50 border-transparent px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-sky-500 transition">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">{{ c.key === 'fedex' ? 'Meter #' : 'Account Key' }}</label>
                                <input v-model="c.meter" class="w-full rounded-xl bg-slate-50 border-transparent px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-sky-500 transition">
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-500">
                                    <input type="checkbox" v-model="c.test" class="accent-slate-800"> Test / Sandbox Mode
                                </label>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Serves: <b class="text-slate-800">{{ c.countries }}</b></span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium">Services: {{ c.services }}</p>
                        </div>

                        <button @click="saveCarrier(c.key)" class="w-full rounded-2xl bg-slate-900 text-white py-3.5 font-black text-sm shadow-xl shadow-slate-200 transition active:scale-95 uppercase tracking-widest">
                            Save {{ c.name }} Credentials
                        </button>
                    </div>
                </div>

                <!-- Recent Shipments Table -->
                <div class="card rounded-[32px] bg-white border-slate-100 shadow-sm overflow-hidden mt-6">
                    <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                        <h3 class="text-xl font-black text-slate-800">Recent Shipments</h3>
                        <button class="text-xs font-black text-sky-600 uppercase tracking-widest">View All Historical</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Carrier</th>
                                    <th class="px-6 py-4">Order Details</th>
                                    <th class="px-6 py-4">Destination</th>
                                    <th class="px-6 py-4">Tracking</th>
                                    <th class="px-6 py-4 text-right">Rate</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <tr v-for="s in shipments" :key="s.id" class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-5 font-mono text-xs text-slate-400">{{ s.id }}</td>
                                    <td class="px-6 py-5">
                                        <div class="font-black text-slate-800 flex items-center gap-2">
                                            <span class="text-lg">{{ s.logo }}</span> {{ s.carrier }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-bold text-slate-600 text-xs">{{ s.order }}</td>
                                    <td class="px-6 py-5 font-bold text-slate-700">{{ s.to }}</td>
                                    <td class="px-6 py-5 font-mono text-xs">{{ s.track }}</td>
                                    <td class="px-6 py-5 text-right font-black text-slate-900">${{ s.rate.toFixed(2) }}</td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider" :class="getStatusBadgeClass(s.status)">
                                            {{ s.status }}
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
