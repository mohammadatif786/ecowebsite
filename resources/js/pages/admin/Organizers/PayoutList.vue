<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, router } from '@inertiajs/vue3';
import { Clock, RefreshCw, CheckCircle, XCircle, Banknote, Receipt, Download, Eye, RotateCcw } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import Modals
import NewProcessPayoutModal from "./Components/NewProcessPayoutModal.vue";
import TimelineModal from "@/pages/admin/Organizers/Components/TimelineModal.vue";
import EmailPreviewModal from "@/pages/admin/Organizers/Components/EmailPreviewModal.vue";
import BaseToast from "@/components/admin/BaseToast.vue";

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialPayouts: Array<any>;
    stats: {
        pending: number;
        processing: number;
        completed: number;
        failed: number;
        paidTotal: number;
        feesTotal: number;
    };
    organizers: Array<any>;
    events: Array<any>;
    filters: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
    { country: 'Jamaica', region: 'Regional', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000, coinsPurchased: 420000, coinsRedeemed: 170000 },
    { country: 'Trinidad & Tobago', region: 'Regional', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000, wellness: 150000, cookouts: 120000, linkup360: 47000, coinsPurchased: 360000, coinsRedeemed: 140000 },
    { country: 'Barbados', region: 'Regional', users: 70000, merchants: 380, organizers: 95, tickets: 330000, subscriptions: 42000, marketplace: 190000, eats: 260000, merchantPay: 720000, wallet: 460000, live: 85000, ads: 21000, wellness: 90000, cookouts: 65000, linkup360: 26000, coinsPurchased: 150000, coinsRedeemed: 60000 },
    { country: 'Guyana', region: 'Regional', users: 130000, merchants: 620, organizers: 140, tickets: 420000, subscriptions: 56000, marketplace: 270000, eats: 360000, merchantPay: 980000, wallet: 600000, live: 120000, ads: 28000, wellness: 100000, cookouts: 82000, linkup360: 31000, coinsPurchased: 210000, coinsRedeemed: 85000 },
    { country: 'Dominican Republic', region: 'Regional', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000, wellness: 130000, cookouts: 99000, linkup360: 41000, coinsPurchased: 290000, coinsRedeemed: 120000 },
    { country: 'United States', region: 'International', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000, wellness: 320000, cookouts: 180000, linkup360: 120000, coinsPurchased: 950000, coinsRedeemed: 410000 },
    { country: 'Canada', region: 'International', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000, wellness: 210000, cookouts: 120000, linkup360: 85000, coinsPurchased: 620000, coinsRedeemed: 260000 },
    { country: 'Brazil', region: 'International', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000, wellness: 260000, cookouts: 140000, linkup360: 97000, coinsPurchased: 780000, coinsRedeemed: 330000 },
    { country: 'Colombia', region: 'International', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000, wellness: 180000, cookouts: 95000, linkup360: 70000, coinsPurchased: 520000, coinsRedeemed: 210000 },
];

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

const pageFilters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || 'Pending',
});

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    renderAll();
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const activeTab = ref(props.filters?.status || 'Pending');
const tabs = ['Pending', 'Processing', 'Completed', 'Failed'];

const statusMap: Record<string, string> = {
    'Pending': 'pending',
    'Processing': 'processing',
    'Completed': 'approved',
    'Failed': 'rejected'
};

const filteredPayouts = computed(() => {
    const status = statusMap[activeTab.value];
    return props.initialPayouts.filter(p => p.status === status);
});

// Modal states
const showProcessModal = ref(false);
const showTimelineModal = ref(false);
const showEmailPreviewModal = ref(false);
const currentPayout = ref<any>(null);
const notes = ref('');
const showSuccessPane = ref(false);
const showErrorPane = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

// Modal handlers
const openProcessModal = (payout: any) => {
    currentPayout.value = payout;
    notes.value = payout.notes || '';
    showProcessModal.value = true;
    showSuccessPane.value = false;
    showErrorPane.value = false;
};

const openTimelineModal = (payout: any) => {
    currentPayout.value = payout;
    showTimelineModal.value = true;
};

const openEmailPreviewModal = (payout: any) => {
    currentPayout.value = payout;
    showEmailPreviewModal.value = true;
};

const closeEmailPreviewModal = () => {
    showEmailPreviewModal.value = false;
    currentPayout.value = null;
};

const closeProcessModal = () => {
    showProcessModal.value = false;
    currentPayout.value = null;
};

const closeTimelineModal = () => {
    showTimelineModal.value = false;
    currentPayout.value = null;
};

// Action handlers
const approvePayout = (notesValue?: string) => {
    if (!currentPayout.value) return;
    router.post(route('admin.payouts.approve', currentPayout.value.id), {
        notes: notesValue || 'Approved by admin'
    }, {
        onSuccess: () => {
            closeProcessModal();
        }
    });
};

const rejectPayout = (notesValue?: string) => {
    if (!currentPayout.value) return;
    router.post(route('admin.payouts.reject', currentPayout.value.id), {
        notes: notesValue || 'Rejected by admin'
    }, {
        onSuccess: () => {
            closeProcessModal();
        }
    });
};

const retryPayout = (id: string) => {
    router.post(route('admin.payouts.retry', id), {});
};

const applySearch = () => {
    router.get(route('admin.payout-list'), {
        search: pageFilters.value.search,
        status: activeTab.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportToCSV = () => {
    const currentPayouts = filteredPayouts.value;
    if (!currentPayouts.length) return;

    let csv = 'Reference,Organizer,Event,Method,Amount,Fee,Net Amount,Status,Created\n';
    currentPayouts.forEach(p => {
        csv += `"${p.reference}",`;
        csv += `"${p.organizer?.name || 'Unknown'}",`;
        csv += `"${p.event?.title || 'Unknown'}",`;
        csv += `"${p.method}",`;
        csv += `"${p.amount}",`;
        csv += `"-${p.fee_amount}",`;
        csv += `"${p.net_amount}",`;
        csv += `"${p.status}",`;
        csv += `"${p.created_at}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `payouts_${activeTab.value.toLowerCase()}_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const renderAll = async () => {
    await nextTick();
    const s = getScale();
    const rs = countries.filter(c => (filters.value.region === 'All' || c.region === filters.value.region) && (filters.value.country === 'All Countries' || c.country === filters.value.country));

    const t = rs.reduce((a, c) => {
        a.gtv += c.tickets + c.subscriptions + c.marketplace + c.eats + c.merchantPay + c.wallet + c.live + c.ads + c.wellness + c.cookouts + c.linkup360;
        a.users += c.users;
        a.merchants += c.merchants;
        a.organizers += c.organizers;
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };

    set('rGTV', fmt(t.gtv * s));
    set('rLinkUp', fmt(t.gtv * s * 0.12));
    set('rBank', fmt(t.gtv * s * 0.03));
    set('rNet', fmt(t.gtv * s * 0.09));
    set('rUsers', num(t.users * s));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(rs.length));
    set('sideGTV', fmt(t.gtv * s));
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Organizer Payouts" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="organizerPayoutListCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Organizer Payouts" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-3xl font-black text-slate-950">Organizer Payouts</h3>
                    <button @click="exportToCSV" class="rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-500 text-white px-5 py-2.5 font-black text-sm inline-flex items-center gap-2 shadow-lg shadow-purple-100 transition active:scale-95">
                        <Download class="w-4 h-4" /> Export CSV
                    </button>
                </div>

                <!-- Core Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-5">
                    <div class="card metric dark rounded-3xl p-5 xl:col-span-2 shadow-xl shadow-slate-200">
                        <p class="text-slate-300 font-bold">Total Payout Volume</p>
                        <h3 class="text-5xl font-black mt-2">{{ fmt(stats.paidTotal) }}</h3>
                        <p class="text-lime-300 font-bold mt-1">Settled and completed payouts</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100 cursor-pointer hover:shadow-md transition" @click="activeTab = 'Pending'">
                        <p class="text-slate-500 font-bold">Pending</p>
                        <h3 class="text-4xl font-black mt-1">{{ stats.pending }}</h3>
                        <p class="text-amber-500 font-bold">Needs review</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100 cursor-pointer hover:shadow-md transition" @click="activeTab = 'Processing'">
                        <p class="text-slate-500 font-bold">Processing</p>
                        <h3 class="text-4xl font-black mt-1">{{ stats.processing }}</h3>
                        <p class="text-sky-500 font-bold">Bank rail active</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100">
                        <p class="text-slate-500 font-bold">Fees Collected</p>
                        <h3 class="text-4xl font-black mt-1">{{ fmt(stats.feesTotal) }}</h3>
                        <p class="text-indigo-500 font-bold">Platform revenue</p>
                    </div>
                    <div class="card rounded-3xl p-5 border border-slate-100 cursor-pointer hover:shadow-md transition" @click="activeTab = 'Failed'">
                        <p class="text-slate-500 font-bold">Failed</p>
                        <h3 class="text-4xl font-black mt-1">{{ stats.failed }}</h3>
                        <p class="text-rose-500 font-bold">Retries required</p>
                    </div>
                </div>

                <!-- Country Heat Map -->
                <div class="card rounded-3xl p-6 border-slate-100 shadow-sm">
                    <h3 class="text-xl font-black mb-6 text-slate-900">LinkUp Payout Country Heat Map</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div v-for="c in countries.slice(0, 8)" :key="c.country" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:bg-white hover:shadow-md transition duration-300">
                            <p class="text-slate-500 font-bold text-xs mb-1 tracking-tight">{{ c.country }}</p>
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ fmt(c.tickets * 0.15 * getScale()) }}</h4>
                            <p class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-tighter">
                                {{ num(c.organizers * getScale()) }} organizers • {{ num(c.tickets * 0.05 * getScale()) }} settlements
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6 border-slate-100 shadow-sm">
                    <!-- Status Tabs Row -->
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-8">
                        <div class="flex gap-2 p-1.5 bg-slate-100 rounded-3xl">
                            <button
                                v-for="tab in tabs"
                                :key="tab"
                                @click="activeTab = tab"
                                class="px-8 py-2.5 rounded-2xl text-sm font-black transition-all duration-200"
                                :class="activeTab === tab ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            >
                                {{ tab }}
                            </button>
                        </div>

                        <!-- Dynamic Alert Box -->
                        <div v-if="activeTab === 'Pending'" class="bg-amber-50 text-amber-700 px-6 py-3 rounded-2xl border border-amber-100 font-black text-xs uppercase tracking-wider shadow-sm">
                            Review and approve/reject payout requests from organizers.
                        </div>
                        <div v-if="activeTab === 'Failed'" class="bg-rose-50 text-rose-700 px-6 py-3 rounded-2xl border border-rose-100 font-black text-xs uppercase tracking-wider shadow-sm">
                            Failed payouts can be retried after bank/account correction.
                        </div>
                    </div>

                    <!-- Filter Row -->
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-10">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Payout Request List</h3>
                            <p class="text-slate-400 text-sm font-bold mt-0.5">Pending → Approved → Processing → Completed, or Rejected.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <input v-model="pageFilters.search" @keyup.enter="applySearch" class="rounded-2xl border border-slate-200 px-5 py-3 w-96 text-sm font-bold outline-none focus:ring-4 focus:ring-purple-50 transition shadow-sm" placeholder="Search ref, organizer, event, bank, country...">
                        </div>
                    </div>

                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="py-4 px-2">Reference</th>
                                    <th>Organizer</th>
                                    <th>Event</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Fee</th>
                                    <th>Net</th>
                                    <th v-if="activeTab === 'Pending'">Requested</th>
                                    <th v-if="activeTab === 'Failed'">Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="p in filteredPayouts" :key="p.id" class="border-b border-slate-50 hover:bg-slate-50 transition align-middle">
                                    <td class="py-6 px-2 font-black text-purple-600 tracking-tight">
                                        <button @click="openTimelineModal(p)" class="hover:underline">{{ p.reference }}</button>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-white text-[10px] font-black">
                                                {{ p.organizer?.name?.charAt(0) || '?' }}
                                            </div>
                                            <b class="text-slate-900 text-[15px]">{{ p.organizer?.name }}</b>
                                        </div>
                                    </td>
                                    <td class="text-slate-500 font-bold max-w-[200px] truncate">{{ p.event?.title }}</td>
                                    <td class="text-slate-500 font-bold">
                                        <span class="px-2 py-1 bg-slate-100 rounded-lg text-[10px]">{{ p.method }}</span>
                                    </td>
                                    <td class="text-slate-600 font-bold">{{ fmt(p.amount) }}</td>
                                    <td class="text-rose-500 font-bold">{{ fmt(p.fee_amount) }}</td>
                                    <td class="font-black text-slate-900 text-base tracking-tighter">{{ fmt(p.net_amount) }}</td>
                                    <td v-if="activeTab === 'Pending'" class="text-slate-500 font-bold">{{ new Date(p.created_at).toLocaleDateString() }}</td>
                                    <td v-if="activeTab === 'Failed'" class="text-slate-400 font-bold">{{ p.notes || 'N/A' }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border shadow-sm"
                                            :class="p.status === 'rejected' ? 'bg-rose-50 text-rose-500 border-rose-100' : p.status === 'approved' ? 'bg-green-50 text-green-500 border-green-100' : 'bg-[#8B5CF6]/10 text-[#8B5CF6] border-[#8B5CF6]/20'">
                                            {{ p.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <button v-if="activeTab === 'Pending' || activeTab === 'Processing'" @click="openProcessModal(p)" class="flex items-center gap-2 bg-[#8B5CF6] text-white px-4 py-2 rounded-xl font-black text-xs shadow-lg shadow-purple-100 hover:scale-105 transition active:scale-95">
                                                <Eye class="h-4 w-4" /> Review
                                            </button>
                                            <button v-if="activeTab === 'Failed'" @click="retryPayout(p.id)" class="flex items-center gap-2 bg-[#8B5CF6] text-white px-4 py-2 rounded-xl font-black text-xs shadow-lg shadow-purple-100 hover:scale-105 transition active:scale-95">
                                                <RotateCcw class="h-4 w-4" /> Retry
                                            </button>
                                            <button v-if="activeTab === 'Completed'" @click="openEmailPreviewModal(p)" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-black text-xs hover:bg-slate-50 transition active:scale-95">
                                                View Email
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!filteredPayouts.length">
                                    <td colspan="11" class="py-20 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="h-16 w-16 rounded-full bg-slate-50 grid place-items-center text-slate-300">
                                                <Receipt class="h-8 w-8" />
                                            </div>
                                            <p class="text-slate-400 font-bold">No payouts found in {{ activeTab.toLowerCase() }} status.</p>
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

    <!-- Modals -->
    <NewProcessPayoutModal
        :show="showProcessModal"
        :payout="currentPayout"
        :notes="notes"
        @close="closeProcessModal"
        @approve="approvePayout"
        @reject="rejectPayout"
    />
    <TimelineModal
        :show="showTimelineModal"
        :payout="currentPayout"
        @close="closeTimelineModal"
    />
    <EmailPreviewModal
        :show="showEmailPreviewModal"
        :payout="currentPayout"
        @close="closeEmailPreviewModal"
    />
    <BaseToast />
</template>
