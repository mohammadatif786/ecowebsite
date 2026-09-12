<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
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

const handleFilterChange = () => {};
const openNewTab = () => {
    window.open('/eats.html', '_blank');
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats App (Live)" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsAppCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats App (Live)" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-[#124d71]">LinkUp Eats — Live App</h3>
                        <p class="text-slate-500 font-medium max-w-2xl mt-1">The full Eats experience (customer ordering, restaurant dashboard, marketplace) embedded in the operating system. The admin pages here are the back office for this app.</p>
                    </div>
                    <button @click="openNewTab" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 font-black flex items-center gap-2 transition hover:bg-slate-50 shadow-sm">
                        <ExternalLink class="w-5 h-5" /> Open in new tab
                    </button>
                </div>

                <div class="rounded-[40px] overflow-hidden border border-slate-200 bg-white shadow-2xl relative aspect-video xl:aspect-[21/9]">
                    <!-- In a real app, this would be an iframe or the embedded app component -->
                    <div class="absolute inset-0 bg-slate-900 grid place-items-center">
                        <div class="text-center">
                            <div class="h-20 w-20 rounded-3xl bg-white/10 grid place-items-center mx-auto mb-4 animate-pulse text-white">
                                <Smartphone class="w-10 h-10" />
                            </div>
                            <h4 class="text-white text-xl font-black">Eats App Simulation</h4>
                            <p class="text-slate-500 text-sm mt-1">Frontend ordering and merchant interface</p>
                        </div>
                    </div>
                    <!--
                    <iframe src="/eats.html" class="w-full h-full border-0"></iframe>
                    -->
                </div>
            </section>
        </main>
    </div>
</template>
