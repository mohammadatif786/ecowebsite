<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ShieldCheck, Search, CheckCircle2, AlertCircle, Eye } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Prototype Data
const mxMerchants = ref([
    { id: 1, name: 'SuperValue Nassau', country: 'Bahamas', taxId: 'BS-TIN-4821', kyc: 'Verified', risk: 14 },
    { id: 5, name: 'Georgetown Wholesale', country: 'Guyana', taxId: 'GY-TIN-2901', kyc: 'Pending', risk: 40 },
    { id: 6, name: 'Port-au-Prince Market', country: 'Haiti', taxId: 'HT-NIF-5540', kyc: 'Pending', risk: 52 },
    { id: 14, name: 'Medellín Fashion', country: 'Colombia', taxId: 'CO-NIT-1133', kyc: 'Rejected', risk: 71 },
    { id: 11, name: 'Panamá Services Co', country: 'Panama', taxId: 'PA-RUC-3380', kyc: 'Pending', risk: 46 },
]);

const kycStats = computed(() => {
    const verified = mxMerchants.value.filter(m => m.kyc === 'Verified').length;
    const pending = mxMerchants.value.filter(m => m.kyc === 'Pending').length;
    const avgRisk = Math.round(mxMerchants.value.reduce((a, m) => a + m.risk, 0) / mxMerchants.value.length);
    return { verified, pending, avgRisk };
});

const kycQueue = computed(() => {
    return mxMerchants.value.filter(m => m.kyc !== 'Verified').sort((a, b) => b.risk - a.risk);
});

const headerMetrics = computed(() => ({
    gtv: '$2.8M',
    revenue: '$840k',
    bank: '$2.1M',
    net: '$420k',
    users: '1.2M',
    merchants: mxMerchants.value.length.toString(),
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
    <Head title="KYC & Verification" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="mxKyc" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="KYC & Verification" :countries="[]" :metrics="headerMetrics" @toggle-sidebar="toggleSidebar" />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 flex items-center gap-3"><ShieldCheck class="w-8 h-8 text-emerald-600" /> Compliance Desk</h2>
                    <p class="text-slate-500 font-medium mt-1">Reviewing merchant identities and business legitimacy.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Verified</p><h3 class="text-3xl font-black mt-1 text-emerald-600">{{ kycStats.verified }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Pending</p><h3 class="text-3xl font-black mt-1 text-amber-600">{{ kycStats.pending }}</h3></div>
                    <div class="card rounded-3xl p-5 bg-white border border-slate-100 shadow-sm"><p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Avg Risk Score</p><h3 class="text-3xl font-black mt-1 text-slate-800">{{ kycStats.avgRisk }}</h3></div>
                </div>

                <!-- Queue Table -->
                <div class="card rounded-3xl bg-white border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                        <h4 class="font-black text-slate-800">Verification Queue</h4>
                        <div class="relative w-72">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input type="text" placeholder="Search queue..." class="w-full pl-11 pr-4 py-2.5 rounded-2xl bg-slate-50 border-transparent font-bold text-sm focus:bg-white focus:border-emerald-500 transition" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-[#f8fafc] text-[10px] font-black uppercase text-slate-400 tracking-[.08em]">
                                <tr>
                                    <th class="px-6 py-4">Merchant</th>
                                    <th class="px-6 py-4">Country</th>
                                    <th class="px-6 py-4">Tax ID / TIN</th>
                                    <th class="px-6 py-4 text-center">Docs</th>
                                    <th class="px-6 py-4 text-center">Risk</th>
                                    <th class="px-6 py-4 text-center">KYC Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm font-bold">
                                <tr v-for="m in kycQueue" :key="m.id" class="hover:bg-slate-50 transition group">
                                    <td class="px-6 py-5">
                                        <div class="font-black text-slate-900">{{ m.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">ID: #{{ m.id }}</div>
                                    </td>
                                    <td class="px-6 py-5 text-slate-600">{{ m.country }}</td>
                                    <td class="px-6 py-5 font-mono text-xs text-slate-400">{{ m.taxId }}</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-slate-600 border border-slate-200">
                                            ID • BIZ • BANK
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                            :class="m.risk < 50 ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100'">
                                            {{ m.risk }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center gap-1.5" :class="m.kyc === 'Rejected' ? 'text-rose-600' : 'text-amber-500'">
                                            <AlertCircle v-if="m.kyc === 'Rejected'" class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase">{{ m.kyc }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button class="h-9 w-9 rounded-xl bg-slate-100 text-slate-400 grid place-items-center hover:bg-slate-950 hover:text-white transition"><Eye class="w-4 h-4" /></button>
                                            <button class="rounded-xl bg-emerald-600 text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition">Verify</button>
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
