<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { UserPlus, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Prototype Data
const mxMerchants = ref([
    { id: 1, name: 'SuperValue Nassau', country: 'Bahamas', category: 'Grocery', stage: 'Approved', status: 'Active', kyc: 'Verified', joined: '2024-08-12' },
    { id: 5, name: 'Georgetown Wholesale', country: 'Guyana', category: 'Wholesale', stage: 'KYC Review', status: 'Pending', kyc: 'Pending', joined: '2026-05-18' },
    { id: 6, name: 'Port-au-Prince Market', country: 'Haiti', category: 'Grocery', stage: 'Documents', status: 'Pending', kyc: 'Pending', joined: '2026-05-29' },
    { id: 11, name: 'Panamá Services Co', country: 'Panama', category: 'Services', stage: 'Risk Review', status: 'Pending', kyc: 'Pending', joined: '2026-05-22' },
    { id: 14, name: 'Medellín Fashion', country: 'Colombia', category: 'Fashion', stage: 'KYC Review', status: 'Pending', kyc: 'Rejected', joined: '2026-05-12' },
]);

// Funnel Stats like HTML
const funnel = computed(() => {
    const stages = ['Application', 'Documents', 'KYC Review', 'Risk Review', 'Approved'];
    const colors = ['bg-slate-100', 'bg-sky-100', 'bg-amber-100', 'bg-violet-100', 'bg-emerald-100'];
    return stages.map((s, i) => ({
        name: s,
        count: mxMerchants.value.filter(m => m.stage === s).length,
        color: colors[i]
    }));
});

const onboardingList = computed(() => {
    return mxMerchants.value.filter(m => m.stage !== 'Approved' || m.status === 'Pending').sort((a, b) => b.joined.localeCompare(a.joined));
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
    <Head title="Merchant Onboarding" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="mxOnboarding" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Merchant Onboarding" :countries="[]" :metrics="headerMetrics" @toggle-sidebar="toggleSidebar" />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 flex items-center gap-3"><UserPlus class="w-8 h-8 text-cyan-600" /> Onboarding Pipeline</h2>
                    <p class="text-slate-500 font-medium mt-1">Qualification funnel for businesses applying to the LinkUp network.</p>
                </div>

                <!-- Pipeline Funnel -->
                <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                    <h4 class="font-black text-slate-800 mb-6 uppercase tracking-widest text-[11px] opacity-60">Pipeline Stage Distribution</h4>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div v-for="f in funnel" :key="f.name" class="rounded-[24px] p-5 text-center transition hover:scale-[1.02]" :class="f.color">
                            <h3 class="text-3xl font-black text-slate-900">{{ f.count }}</h3>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-1">{{ f.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Onboarding Table -->
                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                        <h4 class="font-black text-slate-800">Pending Merchants</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-[#f8fafc] text-[10px] font-black uppercase text-slate-400 tracking-[.08em]">
                                <tr>
                                    <th class="px-6 py-4">Merchant</th>
                                    <th class="px-6 py-4">Country</th>
                                    <th class="px-6 py-4 text-center">Stage</th>
                                    <th class="px-6 py-4 text-center">KYC</th>
                                    <th class="px-6 py-4 text-center">Applied</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm font-bold">
                                <tr v-for="m in onboardingList" :key="m.id" class="hover:bg-slate-50 transition group">
                                    <td class="px-6 py-5">
                                        <div class="font-black text-slate-900">{{ m.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-0.5">{{ m.category }}</div>
                                    </td>
                                    <td class="px-6 py-5 text-slate-600">{{ m.country }}</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="rounded-full bg-violet-50 text-violet-700 px-3 py-1 text-[10px] font-black uppercase border border-violet-100">
                                            {{ m.stage }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center gap-1.5" :class="m.kyc === 'Verified' ? 'text-emerald-600' : (m.kyc === 'Rejected' ? 'text-rose-600' : 'text-amber-500')">
                                            <CheckCircle2 v-if="m.kyc === 'Verified'" class="w-4 h-4" />
                                            <AlertCircle v-else class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase">{{ m.kyc }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center text-slate-400 text-xs">{{ m.joined }}</td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button class="rounded-xl bg-sky-50 text-sky-700 px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-sky-600 hover:text-white transition">Advance</button>
                                            <button class="rounded-xl bg-emerald-600 text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100">Approve</button>
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
