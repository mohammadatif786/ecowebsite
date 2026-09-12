<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ShieldCheck, Save } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

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

const policies = ref([
    { key: 'agreement', title: 'Driver Services Agreement', body: 'LinkUp Vibes — Driver Services Agreement\nEffective 1 January 2026\n\n1. INDEPENDENT CONTRACTOR...' },
    { key: 'conduct', title: 'Driver Code of Conduct', body: 'LinkUp Driver — Code of Conduct\n\n• Be honest. Never steal, open, sample or tamper with an order...' },
    { key: 'kyc', title: 'Identity Verification & Background Check', body: 'LinkUp Driver — Identity Verification (KYC) & Background Check\n\nBecause drivers take custody of customers\' food, goods and money...' },
    { key: 'handling', title: 'Item Handling, Custody & Anti-Theft Policy', body: 'LinkUp Driver — Item Handling, Custody & Anti-Theft Policy\n\nTHE RULE: From the moment you pick up an order...' },
    { key: 'payments', title: 'Driver Payments & Payouts', body: 'LinkUp Driver — Payments & Payouts\n\nEARNINGS: You earn a per-delivery fee plus 100% of customer tips...' },
    { key: 'insurance', title: 'Insurance & Liability', body: 'LinkUp Driver — Insurance & Liability\n\nYOUR RESPONSIBILITY: You must hold and maintain valid vehicle insurance...' },
    { key: 'deactivation', title: 'Deactivation & Appeals', body: 'LinkUp Driver — Deactivation & Appeals\n\nGROUNDS FOR ACTION: LinkUp may warn, suspend or permanently deactivate...' },
    { key: 'privacy', title: 'Driver Privacy Notice', body: 'LinkUp Driver — Privacy Notice\n\nWHAT WE COLLECT: Identity & KYC documents and biometric face-match data...' }
]);

const savePolicies = () => {
    alert('✅ Driver policies saved — they update in the Driver app on next load');
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
    <Head title="Driver Policies" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="driverPoliciesView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Driver Policies" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8">
                <div class="max-w-4xl mx-auto space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black text-[#124d71] flex items-center gap-3"><ShieldCheck class="w-8 h-8" /> Driver Policies</h2>
                            <p class="text-slate-500 font-medium mt-1">Legal agreements and rules drivers must accept. Changes sync into the LinkUp Driver app.</p>
                        </div>
                        <button @click="savePolicies" class="rounded-2xl bg-[#124d71] text-white px-6 py-3.5 font-black flex items-center gap-2 shadow-lg shadow-blue-900/20 transition active:scale-95">
                            <Save class="w-5 h-5" /> Save All Policies
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="p in policies" :key="p.key" class="card rounded-[32px] p-8 border-slate-100 bg-white shadow-sm">
                            <h4 class="text-xl font-black text-slate-900 mb-4">{{ p.title }}</h4>
                            <textarea
                                v-model="p.body"
                                class="w-full min-h-[150px] p-4 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-mono text-xs leading-relaxed outline-none transition-all"
                            ></textarea>
                        </div>
                    </div>

                    <div class="pt-4 pb-12">
                        <button @click="savePolicies" class="w-full rounded-2xl bg-[#124d71] text-white py-5 font-black flex items-center justify-center gap-2 shadow-xl shadow-blue-900/20 transition active:scale-95">
                            <Save class="w-6 h-6" /> Save All Driver Policies
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
