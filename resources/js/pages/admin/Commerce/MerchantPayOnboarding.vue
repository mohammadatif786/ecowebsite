<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { QrCode, CheckCircle2, XCircle } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

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

const applicants = ref([
    { id: '@islandfresh', name: 'Island Fresh Market', owner: 'Patrice Rolle', status: 'Pending Review' }
]);

const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });
const handleFilterChange = (newFilters: any) => { filters.value = newFilters; };

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="MerchantPay Onboarding" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="merchantPayView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="MerchantPay Onboarding" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="max-w-5xl mx-auto">
                    <h2 class="text-3xl font-black text-[#0f172a] flex items-center gap-3"><QrCode class="w-8 h-8" /> MerchantPay Onboarding</h2>
                    <p class="text-slate-500 mt-2">Placeholder for specialized onboarding logic.</p>
                </div>
            </section>
        </main>
    </div>
</template>
