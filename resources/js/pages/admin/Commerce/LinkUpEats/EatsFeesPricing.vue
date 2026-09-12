<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { SlidersHorizontal, Save, RotateCcw, Info } from 'lucide-vue-next';
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

const handleFilterChange = () => {};

// Fees State
const fees = ref({ commission: 15, service: 5, processing: 3, gratuity: 15 });
const isSaved = ref(false);

const saveFees = () => {
    isSaved.value = true;
    setTimeout(() => { isSaved.value = false; }, 3000);
};

const resetFees = () => {
    fees.value = { commission: 15, service: 5, processing: 3, gratuity: 15 };
};

const feePreview = computed(() => {
    const sub = 100;
    const comm = sub * fees.value.commission / 100;
    const svc = sub * fees.value.service / 100;
    const proc = sub * fees.value.processing / 100;
    return {
        subtotal: sub,
        commission: comm,
        service: svc,
        linkupShare: proc * 0.6,
        bankShare: proc * 0.4
    };
});

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Fees & Pricing" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsFeesPricingCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Fees & Pricing" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h3 class="text-3xl font-black text-orange-600">Fees & Pricing</h3>
                    <p class="text-slate-500 font-medium mt-1">Global settings for commissions, service fees, and processing splits.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="card rounded-[32px] p-6 border-slate-100 shadow-sm bg-white">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Restaurant Commission (%)</p>
                        <div class="flex items-center gap-3">
                            <input v-model.number="fees.commission" type="number" class="w-full text-4xl font-black text-slate-900 outline-none">
                            <span class="text-2xl font-black text-slate-200">%</span>
                        </div>
                    </div>
                    <div class="card rounded-[32px] p-6 border-slate-100 shadow-sm bg-white">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Service Fee (%)</p>
                        <div class="flex items-center gap-3">
                            <input v-model.number="fees.service" type="number" class="w-full text-4xl font-black text-slate-900 outline-none">
                            <span class="text-2xl font-black text-slate-200">%</span>
                        </div>
                    </div>
                    <div class="card rounded-[32px] p-6 border-slate-100 shadow-sm bg-white">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Processing Fee (%)</p>
                        <div class="flex items-center gap-3">
                            <input v-model.number="fees.processing" type="number" class="w-full text-4xl font-black text-slate-900 outline-none">
                            <span class="text-2xl font-black text-slate-200">%</span>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-tight italic">Split LinkUp 60% / Bank 40%</p>
                    </div>
                    <div class="card rounded-[32px] p-6 border-slate-100 shadow-sm bg-white">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 block">Default Gratuity (%)</p>
                        <div class="flex items-center gap-3">
                            <input v-model.number="fees.gratuity" type="number" class="w-full text-4xl font-black text-slate-900 outline-none">
                            <span class="text-2xl font-black text-slate-200">%</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button @click="saveFees" class="rounded-2xl bg-orange-600 text-white px-8 py-4 font-black shadow-xl shadow-orange-100 hover:bg-orange-700 transition active:scale-95 flex items-center gap-2">
                        <Save class="w-5 h-5" /> Save Global Fees
                    </button>
                    <button @click="resetFees" class="rounded-2xl border border-slate-200 bg-white px-8 py-4 font-black text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                        <RotateCcw class="w-5 h-5" /> Reset
                    </button>
                    <div v-if="isSaved" class="text-emerald-600 font-black text-sm uppercase tracking-widest animate-in fade-in duration-300 ml-4">✓ Saved and Synced</div>
                </div>

                <!-- Preview -->
                <div class="card rounded-[40px] p-8 border-slate-100 bg-slate-50/50 shadow-sm">
                    <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">
                        <SlidersHorizontal class="w-6 h-6 text-orange-600" /> Fee Preview <span class="text-sm font-bold text-slate-400">(on a $100 order)</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="rounded-[28px] bg-white p-6 shadow-sm border border-slate-200/50">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Order Subtotal</p>
                            <b class="text-2xl text-slate-900 font-black">${{ feePreview.subtotal.toFixed(2) }}</b>
                        </div>
                        <div class="rounded-[28px] bg-white p-6 shadow-sm border border-slate-200/50">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">LinkUp Commission</p>
                            <b class="text-2xl text-orange-600 font-black">${{ feePreview.commission.toFixed(2) }}</b>
                        </div>
                        <div class="rounded-[28px] bg-white p-6 shadow-sm border border-slate-200/50">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Service Fee</p>
                            <b class="text-2xl text-sky-600 font-black">${{ feePreview.service.toFixed(2) }}</b>
                        </div>
                        <div class="rounded-[28px] bg-white p-6 shadow-sm border border-slate-200/50">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Processing Split</p>
                            <b class="text-lg text-slate-900 font-black">${{ feePreview.linkupShare.toFixed(2) }} <span class="text-xs text-slate-300 font-bold uppercase tracking-tighter">LU</span> / ${{ feePreview.bankShare.toFixed(2) }} <span class="text-xs text-slate-300 font-bold uppercase tracking-tighter">Bank</span></b>
                        </div>
                    </div>
                    <div class="mt-8 flex items-start gap-3 text-slate-400">
                        <Info class="w-5 h-5 flex-shrink-0" />
                        <p class="text-xs font-bold leading-relaxed uppercase tracking-tight">These settings feed the unified platform revenue engine. Commission + Service = LinkUp platform fee. Processing split applies to the bank fee pool managed by Scotiabank.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
