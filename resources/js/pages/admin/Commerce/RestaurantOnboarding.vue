<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Store, CheckCircle2, XCircle } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model (Matching the HTML's internal data)
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
];

const applications = ref([
    { id: '@islandgrill', name: 'Island Grill Nassau', owner: 'Patrice Rolle', cuisine: 'Caribbean · Grill', city: 'Nassau', country: 'Bahamas', status: 'Approved', docs: { license: 'Verified', foodCert: 'Verified', ownerId: 'Verified', bank: 'Verified' } },
    { id: '@reefandrum', name: 'Reef & Rum Kitchen', owner: 'Andre Cooper', cuisine: 'Seafood · Bar', city: 'Freeport', country: 'Bahamas', status: 'Pending Review', docs: { license: 'Uploaded', foodCert: 'Uploaded — verifying', ownerId: 'Face match 97%', bank: 'Pending' } },
    { id: '@yardflavours', name: 'Yard Flavours', owner: 'Simone Brown', cuisine: 'Jamaican', city: 'Kingston', country: 'Jamaica', status: 'Pending Review', docs: { license: 'Uploaded', foodCert: 'Pending', ownerId: 'Uploaded', bank: 'Uploaded' } }
]);

const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const handleFilterChange = (newFilters: any) => { filters.value = newFilters; renderAll(); };

const makeDecision = (id: string, decision: 'Approved' | 'Rejected') => {
    const app = applications.value.find(x => x.id === id);
    if (app) app.status = decision;
    alert(`${decision === 'Approved' ? '✅' : '⛔'} ${id} ${decision}`);
};

const getChipClass = (val: string) => {
    const ok = /Verified|9\d%/.test(val) && !/verifying/i.test(val);
    const warn = /verifying|review/i.test(val);
    const pend = /Pending|Uploaded/i.test(val) && !ok;

    if (ok) return 'bg-green-50 text-green-700 border-green-100';
    if (warn) return 'bg-amber-50 text-amber-700 border-amber-100';
    if (pend) return 'bg-rose-50 text-rose-700 border-rose-100';
    return 'bg-slate-50 text-slate-500 border-slate-100';
};

const stats = computed(() => {
    return {
        pending: applications.value.filter(a => a.status === 'Pending Review').length,
        total: applications.value.length,
        approved: applications.value.filter(a => a.status === 'Approved').length
    };
});

const renderAll = async () => {
    await nextTick();
    // Header ribbon logic would go here
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Restaurant Onboarding" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="restaurantOnboardingView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Restaurant Onboarding" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="max-w-5xl mx-auto space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-[#124d71] flex items-center gap-3"><Store class="w-8 h-8" /> Restaurant Onboarding</h2>
                        <p class="text-slate-500 font-medium mt-1">Restaurants self-sign-up from their back office and are reviewed here. Approvals activate them on LinkUp Eats so they can publish a menu and receive orders.</p>
                    </div>

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
                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Live Restaurants</p>
                            <h3 class="text-3xl font-black text-emerald-600">{{ stats.approved }}</h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div v-for="a in applications" :key="a.id" class="card rounded-[32px] p-8 border-slate-100 shadow-sm bg-white hover:shadow-xl transition-all duration-300">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-xl font-black text-slate-900">{{ a.name }}</h4>
                                        <span class="text-slate-400 font-bold text-sm">{{ a.id }}</span>
                                    </div>
                                    <p class="text-slate-500 font-medium mt-1">{{ a.cuisine }} · Owner: {{ a.owner }} · {{ a.city }}, {{ a.country }}</p>
                                </div>
                                <span class="rounded-full px-4 py-1.5 text-xs font-black uppercase border self-start"
                                    :class="a.status === 'Approved' ? 'bg-green-50 text-green-700 border-green-100' : (a.status === 'Rejected' ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-amber-50 text-amber-700 border-amber-100')">
                                    {{ a.status }}
                                </span>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                <span v-if="a.docs.license" :class="getChipClass(a.docs.license)" class="px-3 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-wider">Business Licence: {{ a.docs.license }}</span>
                                <span v-if="a.docs.foodCert" :class="getChipClass(a.docs.foodCert)" class="px-3 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-wider">Food Cert: {{ a.docs.foodCert }}</span>
                                <span v-if="a.docs.ownerId" :class="getChipClass(a.docs.ownerId)" class="px-3 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-wider">Owner ID: {{ a.docs.ownerId }}</span>
                                <span v-if="a.docs.bank" :class="getChipClass(a.docs.bank)" class="px-3 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-wider">Bank / Payout: {{ a.docs.bank }}</span>
                            </div>

                            <div v-if="a.status === 'Pending Review'" class="mt-8 flex gap-3">
                                <button @click="makeDecision(a.id, 'Approved')" class="flex-1 rounded-2xl bg-[#124d71] text-white py-4 font-black shadow-lg shadow-blue-900/10 transition active:scale-95 flex items-center justify-center gap-2">
                                    <CheckCircle2 class="w-5 h-5" /> Approve & Onboard
                                </button>
                                <button @click="makeDecision(a.id, 'Rejected')" class="px-8 rounded-2xl border-2 border-rose-100 bg-white text-rose-600 py-4 font-black hover:bg-rose-50 transition active:scale-95 flex items-center justify-center gap-2">
                                    <XCircle class="w-5 h-5" /> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
