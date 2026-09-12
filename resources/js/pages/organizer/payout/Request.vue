<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/organizer/AppLayout.vue'
import NewPayOutModal from './Component/NewPayOutModal.vue'
import dayjs from 'dayjs'
import { Wallet, Info, Search, Plus, Calendar, ArrowRight, Clock, CheckCircle2, XCircle, AlertCircle } from 'lucide-vue-next'

const props = defineProps<{
    payouts: Array<Record<string, any>>
    events: Array<Record<string, any>>
    settings: Array<Record<string, any>>
    bankAccounts: any
    payout_processing: number
    filters: any
}>();

const activeTab = ref('events');
const showModal = ref(false);
const eventId = ref(null)

// Filter reactive data
const localFilters = ref({
    reference: props.filters?.reference || '',
    event_id: props.filters?.event_id || '',
    method: props.filters?.method || '',
    status: props.filters?.status || ''
});

const openModal = (id: any) => {
    eventId.value = id;
    showModal.value = true
}

const formatEventDate = (event: any) => {
    const details = event.event_details;
    if (!details) return "";

    if (details.event_type === "single") {
        const date = details.single_event_date
        return date ? dayjs(date).format("MMM D, YYYY") : "—"
    }

    if (details.event_type === "recurring") {
        const startDate = details.recurr_start_date
        const endDate = details.recurr_end_date
        return startDate && endDate
            ? `${dayjs(startDate).format("MMM D")} - ${dayjs(endDate).format("MMM D, YYYY")}`
            : "—"
    }

    return "—"
};

// Summary Calculations
const allTimeNet = computed(() => {
    return props.events?.reduce((acc, ev) => acc + (parseFloat(ev.net_sales) || 0), 0) || 0;
});

const totalAvailable = computed(() => {
    return props.events?.reduce((acc, ev) => acc + (parseFloat(ev.available_balance) || 0), 0) || 0;
});

const totalPending = computed(() => {
    // In our app, pending payouts are ones requested but not processed.
    // However, prototype describes pending as "held until after event".
    // We'll sum up requested pending payouts for now.
    return props.payouts?.filter(p => p.status === 'pending').reduce((acc, p) => acc + (parseFloat(p.amount) || 0), 0) || 0;
});

const lastPayout = computed(() => {
    const sorted = [...(props.payouts || [])].sort((a, b) => dayjs(b.created_at).diff(dayjs(a.created_at)));
    return sorted.find(p => p.status === 'verified' || p.status === 'approved');
});

// Filter payouts
const filteredPayouts = computed(() => {
    if (!props.payouts) return [];
    return props.payouts; // We filter on server side via applyFilters
});

const applyFilters = () => {
    router.get(route('organizer.payout.request'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    localFilters.value = { reference: '', event_id: '', method: '', status: '' };
    applyFilters();
};

const formatCurrency = (value: number | string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(typeof value === 'string' ? parseFloat(value) : value);
};

const getEventNetSales = (eventId: number) => {
    const event = props.events?.find(e => e.id === eventId);
    return event?.net_sales || 0;
};

const getEventAvailableBalance = (eventId: number) => {
    const event = props.events?.find(e => e.id === eventId);
    return event?.available_balance || 0;
};
</script>

<template>
    <Head title="Payout Request" />

    <AppLayout>
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column: Summaries & Quick Actions -->
            <div class="space-y-4">
                <!-- All-time Net Earnings Card (Sync line 2515) -->
                <div class="rounded-[24px] text-white p-6 text-center shadow-xl shadow-slate-900/10" style="background:linear-gradient(135deg,#1e293b,#312e81)">
                    <p class="text-white/60 text-[11px] font-black uppercase tracking-widest">All-Time Net Earnings</p>
                    <p class="text-4xl font-black mt-2">{{ formatCurrency(allTimeNet) }}</p>
                    <p class="text-white/50 text-[11px] mt-2 font-bold uppercase">After LinkUp fee & promoter commissions</p>
                </div>

                <!-- Available & Pending Grid (Sync line 2516) -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-[24px] bg-emerald-50 border border-emerald-100 p-5 shadow-sm">
                        <p class="text-[11px] font-black text-emerald-700 uppercase tracking-wider mb-1">Available</p>
                        <p class="text-2xl font-black text-emerald-800">{{ formatCurrency(totalAvailable) }}</p>
                    </div>
                    <div class="rounded-[24px] bg-amber-50 border border-amber-100 p-5 shadow-sm">
                        <p class="text-[11px] font-black text-amber-700 uppercase tracking-wider mb-1">Pending</p>
                        <p class="text-2xl font-black text-amber-800">{{ formatCurrency(totalPending) }}</p>
                        <p class="text-[10px] text-amber-600 font-bold mt-1">Held until after processing</p>
                    </div>
                </div>

                <!-- Request Payout Card (Sync line 2520) -->
                <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                    <p class="font-black text-lg mb-2 text-slate-800 flex items-center gap-2">
                        <ArrowRight class="w-5 h-5 text-indigo-500" />
                        Quick Request
                    </p>
                    <p class="text-[12px] text-slate-400 font-medium mb-5 leading-relaxed">
                        Pick an event from the list to request your funds. Payouts are usually processed within 24-48 hours.
                    </p>
                    <button @click="activeTab = 'events'" class="btn w-full py-3.5 rounded-2xl font-black text-white transition hover:scale-[1.01] active:scale-[0.98] shadow-lg shadow-indigo-500/20 bg-indigo-600">
                        Request Payout
                    </button>
                    <p v-if="lastPayout" class="text-[11px] text-slate-400 mt-4 text-center font-bold">
                        Last payout: {{ formatCurrency(lastPayout.amount) }} · {{ lastPayout.status }}
                    </p>
                </div>

                <!-- Tab Switcher -->
                <div class="flex p-1 bg-slate-100 rounded-2xl border border-slate-200">
                    <button @click="activeTab = 'events'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2"
                        :class="activeTab === 'events' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        <Calendar class="w-3.5 h-3.5" />
                        My Events
                    </button>
                    <button @click="activeTab = 'payouts'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2"
                        :class="activeTab === 'payouts' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                        <Clock class="w-3.5 h-3.5" />
                        Payout Reports
                    </button>
                </div>
            </div>

            <!-- Right Column: Data Tables -->
            <div class="lg:col-span-2">
                <!-- Events Tab Content -->
                <div v-if="activeTab === 'events'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="card bg-white border border-slate-100 rounded-[24px] shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                            <h2 class="text-lg font-black text-slate-800">Funds by Event</h2>
                            <span class="text-[11px] font-black text-slate-400 uppercase bg-slate-50 px-3 py-1 rounded-full">
                                {{ props.events?.length || 0 }} events
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-50">
                                        <th class="px-6 py-4">Event Details</th>
                                        <th class="px-4 py-4">Net Sales</th>
                                        <th class="px-4 py-4">Available</th>
                                        <th class="px-4 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="event in props.events" :key="event.id" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <p class="font-black text-slate-800 leading-tight">{{ event.title }}</p>
                                            <p class="text-[11px] text-slate-400 font-bold mt-1 uppercase">{{ formatEventDate(event) }}</p>
                                        </td>
                                        <td class="px-4 py-4 font-bold text-slate-600">{{ formatCurrency(event?.net_sales || 0) }}</td>
                                        <td class="px-4 py-4">
                                            <span class="font-black text-emerald-600">{{ formatCurrency(event?.available_balance || 0) }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                                :class="{
                                                    'bg-emerald-50 text-emerald-600': event?.payout_status === 'approved' || event?.payout_status === 'verified',
                                                    'bg-amber-50 text-amber-600': event?.payout_status === 'pending',
                                                    'bg-rose-50 text-rose-600': ['cancelled', 'failed'].includes(event?.payout_status),
                                                    'bg-slate-50 text-slate-400': event?.payout_status === 'no_request'
                                                }">
                                                {{ event?.payout_status === 'no_request' ? 'No Request' : event?.payout_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button v-if="(parseFloat(event?.available_balance) || 0) > 0"
                                                @click="openModal(event?.id)"
                                                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-black hover:bg-indigo-700 transition shadow-md shadow-indigo-500/10">
                                                Request
                                            </button>
                                            <span v-else class="text-[11px] font-bold text-slate-300 uppercase">Settled</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!props.events?.length">
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <AlertCircle class="w-12 h-12 text-slate-100 mx-auto mb-3" />
                                            <p class="text-slate-400 font-bold">No events with balance found.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payout Tab Content -->
                <div v-else class="animate-in fade-in slide-in-from-left-4 duration-300 space-y-4">
                    <!-- Filters Grid -->
                    <div class="card p-5 bg-white border border-slate-100 rounded-[24px] shadow-sm grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Reference</label>
                            <input v-model="localFilters.reference" @input="applyFilters"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold outline-none focus:border-indigo-400 transition"
                                placeholder="PO_..." />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Event</label>
                            <select v-model="localFilters.event_id" @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold outline-none focus:border-indigo-400 transition">
                                <option value="">All events</option>
                                <option v-for="event in events" :key="event.id" :value="event.id">{{ event.title }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Method</label>
                            <select v-model="localFilters.method" @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold outline-none focus:border-indigo-400 transition">
                                <option value="">All methods</option>
                                <option value="paypal">PayPal</option>
                                <option value="stripe">Stripe</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Status</label>
                            <select v-model="localFilters.status" @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold outline-none focus:border-indigo-400 transition">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="verified">Verified</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="card bg-white border border-slate-100 rounded-[24px] shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                            <h2 class="text-lg font-black text-slate-800">Transaction History</h2>
                            <button @click="resetFilters" class="text-[11px] font-black text-indigo-600 hover:text-indigo-700 uppercase">Reset Filters</button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-50">
                                        <th class="px-6 py-4">Reference</th>
                                        <th class="px-4 py-4">Event</th>
                                        <th class="px-4 py-4 text-center">Method</th>
                                        <th class="px-4 py-4">Amount</th>
                                        <th class="px-4 py-4">Date</th>
                                        <th class="px-6 py-4 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="payout in props.payouts" :key="payout.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-slate-800 text-xs">{{ payout.reference }}</p>
                                        </td>
                                        <td class="px-4 py-4 max-w-[150px]">
                                            <p class="font-bold text-slate-600 truncate">{{ payout.event?.title || 'Unknown' }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded-lg bg-slate-50 border border-slate-100 text-[10px] font-black uppercase text-slate-500">
                                                {{ payout.method }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 font-black text-slate-800">{{ formatCurrency(payout.amount) }}</td>
                                        <td class="px-4 py-4 text-slate-400 text-[11px] font-bold">{{ dayjs(payout.created_at).format('MMM D, YYYY') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5 font-black uppercase text-[10px]"
                                                :class="{
                                                    'text-emerald-600': payout.status === 'verified' || payout.status === 'approved',
                                                    'text-amber-500': payout.status === 'pending',
                                                    'text-rose-500': ['cancelled', 'failed'].includes(payout.status)
                                                }">
                                                <component :is="payout.status === 'pending' ? Clock : (payout.status === 'cancelled' ? XCircle : CheckCircle2)" class="w-3.5 h-3.5" />
                                                {{ payout.status }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!props.payouts?.length">
                                        <td colspan="6" class="px-6 py-20 text-center">
                                            <p class="text-slate-400 font-bold">No transactions found.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Payout Modal -->
        <NewPayOutModal :showModal="showModal" @close="showModal = false" :payouts="props.payouts" :eventId="eventId" :events="events" :settings="props.settings" :bankAccounts="props.bankAccounts" :payout_processing="props.payout_processing"/>
    </AppLayout>
</template>

<style scoped>
.card {
    background: #ffffff;
}

/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-right-4 {
    animation-name: slideInFromRight;
}
.slide-in-from-left-4 {
    animation-name: slideInFromLeft;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromRight {
    from { transform: translateX(1rem); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideInFromLeft {
    from { transform: translateX(-1rem); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
