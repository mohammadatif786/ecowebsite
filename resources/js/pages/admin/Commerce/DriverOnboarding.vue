<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { IdCard, CheckCircle2, XCircle, Bike } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

const apps = ref([
    { handle: '@mariaL', name: 'Maria Lopez', city: 'Miami', country: 'United States', vehicle: 'Car', plate: 'FL·8821', email: 'maria@drivers.linkup', status: 'Pending Review', docs: { id: 'Uploaded', selfie: 'Face match 98%', address: 'Uploaded', license: 'Uploaded', insurance: 'Uploaded', police: 'Pending', vehicleReg: 'Uploaded' }, submitted: '2026-06-18' },
    { handle: '@kfoster', name: 'Kyle Foster', city: 'Kingston', country: 'Jamaica', vehicle: 'Motorbike', plate: 'JM·4410', email: 'kyle@drivers.linkup', status: 'Pending Review', docs: { id: 'Uploaded', selfie: 'Face match 71% — review', address: 'Uploaded', license: 'Uploaded', insurance: 'Pending', police: 'Uploaded', vehicleReg: 'Pending' }, submitted: '2026-06-18' },
    { handle: '@dwalcott', name: 'Denise Walcott', city: 'Bridgetown', country: 'Barbados', vehicle: 'Car', plate: 'BB·2093', email: 'denise@drivers.linkup', status: 'Approved', docs: { id: 'Verified', selfie: 'Face match 99%', address: 'Verified', license: 'Verified', insurance: 'Verified', police: 'Cleared', vehicleReg: 'Verified' }, submitted: '2026-06-15' }
]);

const stats = computed(() => {
    return {
        pending: apps.value.filter(a => a.status === 'Pending Review').length,
        total: apps.value.length,
        approved: apps.value.filter(a => a.status === 'Approved').length
    };
});

const makeDecision = (handle: string, decision: string) => {
    const app = apps.value.find(a => a.handle === handle);
    if (app) app.status = decision;
    alert(`${decision === 'Approved' ? '✅' : '⛔'} ${handle} ${decision}`);
};

const getChipClass = (val: string) => {
    const ok = /Verified|Cleared|9\d%|Uploaded/.test(val) && !/review|Pending/i.test(val);
    const warn = /review|71%/.test(val);
    const pend = /Pending/.test(val);

    if (ok) return 'bg-green-50 text-green-700 border-green-100';
    if (warn) return 'bg-amber-50 text-amber-700 border-amber-100';
    if (pend) return 'bg-rose-50 text-rose-700 border-rose-100';
    return 'bg-slate-50 text-slate-500 border-slate-100';
};

const handleFilterChange = () => {};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Driver Onboarding & KYC" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="driverKycView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Driver Onboarding & KYC" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="max-w-4xl mx-auto space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-[#124d71] flex items-center gap-3"><IdCard class="w-8 h-8" /> Driver Onboarding & KYC</h2>
                        <p class="text-slate-500 font-medium mt-1">Applicants are identity-verified and background-checked before activation. Approvals here unlock the driver to go online.</p>
                    </div>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Awaiting Review</p>
                            <h3 class="text-3xl font-black" :class="stats.pending > 0 ? 'text-amber-600' : 'text-green-600'">{{ stats.pending }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Total Applicants</p>
                            <h3 class="text-3xl font-black text-slate-900">{{ stats.total }}</h3>
                        </div>
                        <div class="card rounded-3xl p-6 bg-white border-slate-100 shadow-sm">
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Approved Drivers</p>
                            <h3 class="text-3xl font-black text-emerald-600">{{ stats.approved }}</h3>
                        </div>
                    </div>

                    <!-- Application Cards -->
                    <div class="space-y-4">
                        <div v-for="a in apps" :key="a.handle" class="card rounded-[32px] p-8 border-slate-100 bg-white hover:shadow-xl transition-all duration-300">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-xl font-black text-slate-900">{{ a.name }}</h4>
                                        <span class="text-slate-400 font-bold text-sm">{{ a.handle }}</span>
                                    </div>
                                    <p class="text-slate-500 font-medium mt-1">{{ a.vehicle }} · {{ a.plate }} · {{ a.city }}, {{ a.country }} · {{ a.email }}</p>
                                </div>
                                <span class="rounded-full px-4 py-1.5 text-xs font-black uppercase border self-start"
                                    :class="a.status === 'Approved' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                    {{ a.status }}
                                </span>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                <span v-for="(val, label) in a.docs" :key="label" :class="getChipClass(val)" class="px-3 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-wider">
                                    {{ label }}: {{ val }}
                                </span>
                            </div>

                            <div v-if="a.status === 'Pending Review'" class="mt-8 flex gap-3">
                                <button @click="makeDecision(a.handle, 'Approved')" class="flex-1 rounded-2xl bg-emerald-600 text-white py-4 font-black shadow-lg shadow-emerald-900/10 transition active:scale-95 flex items-center justify-center gap-2">
                                    <CheckCircle2 class="w-5 h-5" /> Approve Driver
                                </button>
                                <button @click="makeDecision(a.handle, 'Rejected')" class="px-8 rounded-2xl border-2 border-rose-100 bg-white text-rose-600 py-4 font-black hover:bg-rose-50 transition active:scale-95 flex items-center justify-center gap-2">
                                    <XCircle class="w-5 h-5" /> Reject
                                </button>
                            </div>
                            <div v-else class="mt-6 text-slate-400 font-bold text-xs">
                                Reviewed · Submitted {{ a.submitted }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
