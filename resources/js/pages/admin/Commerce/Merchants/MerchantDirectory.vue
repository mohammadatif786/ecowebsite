<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { List, Search, MapPin, ExternalLink, MoreVertical, ShieldCheck, Globe2, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Prototype Data
const mxMerchants = ref([
    { id: 1, name: 'SuperValue Nassau', owner: 'Rupert Roberts', country: 'Bahamas', region: 'Caribbean', category: 'Grocery', status: 'Active', kyc: 'Verified', volume: 420000, txns: 8500, chargeback: 0.31, growth: 22 },
    { id: 2, name: 'Kingston Gas Mart', owner: 'Andre Wright', country: 'Jamaica', region: 'Caribbean', category: 'Gas / Fuel', status: 'Active', kyc: 'Verified', volume: 380000, txns: 6400, chargeback: 0.42, growth: 18 },
    { id: 3, name: 'Caribbean Pharmacy', owner: 'Dr. Indra Singh', country: 'Trinidad & Tobago', region: 'Caribbean', category: 'Pharmacy', status: 'Active', kyc: 'Verified', volume: 260000, txns: 4200, chargeback: 0.18, growth: 31 },
    { id: 12, name: 'São Paulo Retail', owner: 'Mariana Souza', country: 'Brazil', region: 'Latin America', category: 'Retail', status: 'Active', kyc: 'Verified', volume: 520000, txns: 9200, chargeback: 0.51, growth: 28 },
    { id: 10, name: 'CDMX Mercado', owner: 'Lucía Hernández', country: 'Mexico', region: 'Latin America', category: 'Grocery', status: 'Active', kyc: 'Verified', volume: 410000, txns: 7800, chargeback: 0.29, growth: 20 },
    { id: 5, name: 'Georgetown Wholesale', owner: 'Anand Persaud', country: 'Guyana', region: 'Caribbean', category: 'Wholesale', status: 'Pending', kyc: 'Pending', volume: 90000, txns: 900, chargeback: 0, growth: 0 }
]);

const mxSearch = ref('');
const mxFilterRegion = ref('');
const mxFilterStatus = ref('');

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);

const filteredMerchants = computed(() => {
    const q = mxSearch.value.toLowerCase();
    return mxMerchants.value.filter(m =>
        (!q || [m.name, m.owner, m.category, m.country].join(' ').toLowerCase().includes(q)) &&
        (!mxFilterRegion.value || m.region === mxFilterRegion.value) &&
        (!mxFilterStatus.value || m.status === mxFilterStatus.value)
    ).sort((a, b) => a.region.localeCompare(b.region) || a.country.localeCompare(b.country) || b.volume - a.volume);
});

// Grouped for display like HTML (simplified logic)
const headerMetrics = computed(() => ({
    gtv: '$2.8M',
    revenue: '$840k',
    bank: '$2.1M',
    net: '$420k',
    users: '1.2M',
    merchants: filteredMerchants.value.length.toString(),
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
    <Head title="Merchant Directory" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="mxDirectory" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Merchant Directory" :countries="[]" :metrics="headerMetrics" @toggle-sidebar="toggleSidebar" />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 flex items-center gap-3"><List class="w-8 h-8 text-cyan-600" /> Merchant Directory</h2>
                    <p class="text-slate-500 font-medium mt-1">Comprehensive list of all registered merchants across all LinkUp business units.</p>
                </div>

                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex flex-col xl:flex-row xl:items-center gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="mxSearch" type="text" placeholder="Search merchant, owner, category, country..." class="w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-cyan-500 font-bold text-sm transition" />
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <select v-model="mxFilterRegion" class="rounded-2xl bg-slate-50 border-transparent py-3 px-4 font-black text-sm text-slate-600">
                                <option value="">All Regions</option>
                                <option>Caribbean</option>
                                <option>Latin America</option>
                            </select>
                            <select v-model="mxFilterStatus" class="rounded-2xl bg-slate-50 border-transparent py-3 px-4 font-black text-sm text-slate-600">
                                <option value="">All Status</option>
                                <option>Active</option>
                                <option>Pending</option>
                                <option>Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-[#f8fafc] text-[10px] font-black uppercase text-slate-400 tracking-[.08em]">
                                <tr>
                                    <th class="px-6 py-4">Merchant</th>
                                    <th class="px-6 py-4">Country</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">GTV</th>
                                    <th class="px-6 py-4">Fees</th>
                                    <th class="px-6 py-4 text-center">Chargeback</th>
                                    <th class="px-6 py-4 text-center">KYC</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm font-bold">
                                <tr v-for="m in filteredMerchants" :key="m.id" class="hover:bg-slate-50 transition group">
                                    <td class="px-6 py-5">
                                        <div class="font-black text-slate-900 group-hover:text-cyan-700 transition-colors">{{ m.name }}</div>
                                        <div class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ m.owner }}</div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-1.5 text-slate-700">
                                            <Globe2 class="w-3.5 h-3.5 text-slate-400" /> {{ m.country }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-black uppercase tracking-tighter">{{ m.region }}</div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest">{{ m.category }}</span>
                                    </td>
                                    <td class="px-6 py-5 font-black text-slate-900">{{ fmt(m.volume) }}</td>
                                    <td class="px-6 py-5 text-slate-500 font-medium">{{ fmt(m.volume * 0.025) }}</td>
                                    <td class="px-6 py-5 text-center" :class="m.chargeback > 0.5 ? 'text-rose-600 font-black' : 'text-slate-500'">{{ (m.chargeback || 0).toFixed(2) }}%</td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center gap-1.5" :class="m.kyc === 'Verified' ? 'text-emerald-600' : 'text-amber-500'">
                                            <ShieldCheck class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase">{{ m.kyc }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border"
                                            :class="m.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                            {{ m.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button title="Open Portal" class="h-9 w-9 rounded-xl bg-cyan-50 text-cyan-600 grid place-items-center hover:bg-cyan-600 hover:text-white transition shadow-sm"><ExternalLink class="w-4 h-4" /></button>
                                            <button title="Profile" class="h-9 w-9 rounded-xl bg-slate-50 text-slate-400 grid place-items-center hover:bg-slate-200 transition"><Eye class="w-4 h-4" /></button>
                                            <button title="Edit" class="h-9 w-9 rounded-xl bg-slate-50 text-slate-400 grid place-items-center hover:bg-slate-200 transition"><Pencil class="w-4 h-4" /></button>
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
