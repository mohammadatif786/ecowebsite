<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import Icon from '@/components/ui/Icon.vue';
import Pagination from '@/components/ui/Pagination.vue';
import PrintTicket from './modals/PrintTicket.vue';
import PaymentDetails from './modals/PaymentDetails.vue';
import ContactAttendeeModal from './modals/ContactAttendeeModal.vue';
import OrderDetails from './modals/OrderDetails.vue';
import ResendTicket from './modals/ResendTicket.vue';
import CheckIn from './modals/CheckIn.vue';
import {
    Megaphone,
    ArrowLeft,
    Users,
    Printer,
    Download,
    Search,
    MoreVertical,
    CreditCard,
    Mail,
    FileText,
    RotateCw,
    Check,
    Loader2,
    X,
    Calendar,
    Ticket
} from 'lucide-vue-next';
import MessageAttendees from './modals/MessageAttendees.vue';
import MessageModal from './modals/MessageModal.vue';
import dayjs from 'dayjs';

const props = defineProps<{
    event: any;
    attendees: {
        data: Array<any>;
        links: Array<any>;
    };
    summaryStats: {
        total: number;
        paid: number;
        pending: number;
        canceled: number;
        checked_in: number;
        revenue: number;
    };
    filters?: {
        search?: string;
        status?: string;
        payment_method?: string;
        from_date?: string;
        until_date?: string;
    };
    appURL: string;
}>();

// State
const sortBy = ref('created_desc');
const activeDropdown = ref<number | null>(null);
const activeAttendee = ref<any | null>(null);
const selectedAttendees = ref<number[]>([]);
const showPrintTicketModal = ref(false);
const printTicketData = ref<any | null>(null);
const showPaymentDetailsModal = ref(false);
const paymentDetailsData = ref<any | null>(null);
const showContactAttendeeModal = ref(false);
const contactAttendee = ref<any | null>(null);
const showOrderDetailsModal = ref(false);
const orderDetailsData = ref<any | null>(null);
const showResendTicketModal = ref(false);
const resendAttendee = ref<any | null>(null);
const showCheckInModal = ref(false);
const checkInAttendee = ref<any | null>(null);
const showMessageAttendees = ref(false);
const showMessageModal = ref(false);

const openMessageAttendeesModal = () => {
    showMessageAttendees.value = true;
}

// Filters matching reference line 4621 logic
const localFilters = ref({
    search: props.filters?.search || '',
    from_date: props.filters?.from_date || '',
    until_date: props.filters?.until_date || '',
    // In our app we have checkboxes for these, sync with prototype "Status" section
    showConfirmed: true,
    showCheckedIn: true
});

const applyFilters = () => {
    router.get(route('organizer.event.report.attendees', props.event.slug), {
        search: localFilters.value.search,
        from_date: localFilters.value.from_date,
        until_date: localFilters.value.until_date,
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    localFilters.value = {
        search: '',
        from_date: '',
        until_date: '',
        showConfirmed: true,
        showCheckedIn: true
    };
    applyFilters();
};

const formatCurrency = (value: number | string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(typeof value === 'string' ? parseFloat(value) : value);
};

const formatDate = (dateString: string) => {
    if (!dateString) return '—';
    return dayjs(dateString).format('MMM D, YYYY · h:mm A');
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'confirmed': return 'bg-blue-50 text-blue-600';
        case 'checked_in': return 'bg-emerald-50 text-emerald-600';
        case 'pending': return 'bg-amber-50 text-amber-600';
        case 'cancelled':
        case 'failed': return 'bg-rose-50 text-rose-600';
        default: return 'bg-slate-50 text-slate-500';
    }
};

const toggleRowMenu = (event: MouseEvent, id: number, attendee: any) => {
    event.stopPropagation();
    if (activeDropdown.value === id) {
        activeDropdown.value = null;
        activeAttendee.value = null;
    } else {
        activeDropdown.value = id;
        activeAttendee.value = attendee;
    }
};

const closeDropdown = () => {
    activeDropdown.value = null;
    activeAttendee.value = null;
};

const exportAttendees = () => {
    const params = new URLSearchParams();
    if (localFilters.value.search) params.set('search', localFilters.value.search);
    window.open(route('organizer.event.report.export', props.event.slug) + '?' + params.toString());
    toast.success('✅ Attendees exported (CSV)');
};

// --- Modal Trigger Helpers ---
const showPaymentDetails = (attendee: any) => {
    activeAttendee.value = attendee;
    activeDropdown.value = null;
    openPaymentDetailsModal();
};

const openPaymentDetailsModal = () => {
    if (!activeAttendee.value) return;
    const attendee = activeAttendee.value;
    const group = (props.attendees?.data || []).filter((a: any) => a.stripe_id && a.stripe_id === attendee.stripe_id);
    const toNumber = (v: any) => Number(v ?? 0);
    const feeSource = group.find((t: any) => t?.fee_breakdown);
    const b = feeSource?.fee_breakdown || {};

    const feesSummary = {
        service_fee_total: group.reduce((sum, t) => sum + toNumber(t.fee), 0),
        processing_fee_total: group.reduce((sum, t) => sum + toNumber(t.tax), 0),
        drink_fees_total: group.reduce((sum, t) => sum + toNumber(t.drink_fees), 0),
        coupon_amount: group.reduce((sum, t) => sum + toNumber(t.coupan_amount), 0),
        bottle_fees_total: group.reduce((sum, t) => sum + toNumber(t.fee_breakdown?.bottle_fee_amount), 0),
        vip_fees_total: group.reduce((sum, t) => sum + toNumber(t.fee_breakdown?.vip_package_fee_amount), 0),
        drink_fee_pct: toNumber(b.drink_fee_pct_rate || b.drink_fee_pct),
        bottle_fee_pct: toNumber(b.bottle_fee_pct_rate || b.bottle_fee_pct),
        vip_fee_pct: toNumber(b.vip_package_fee_pct_rate || b.vip_fee_pct),
    };

    paymentDetailsData.value = { current: attendee, group, feesSummary };
    showPaymentDetailsModal.value = true;
};

const showContactAttendee = (attendee: any) => {
    contactAttendee.value = attendee;
    activeDropdown.value = null;
    showContactAttendeeModal.value = true;
};

const showOrderDetails = (attendee: any) => {
    activeAttendee.value = attendee;
    activeDropdown.value = null;
    // (Similar grouping logic as payment details, showing detailed Breakdown)
    orderDetailsData.value = { current: attendee, group: [], feesSummary: {} };
    showOrderDetailsModal.value = true;
};

const showResendTicket = (attendee: any) => {
    resendAttendee.value = attendee;
    activeDropdown.value = null;
    showResendTicketModal.value = true;
};

const showCheckIn = (attendee: any) => {
    checkInAttendee.value = attendee;
    activeDropdown.value = null;
    showCheckInModal.value = true;
};

const onCheckInToggled = (payload: { id: number; status: string }) => {
    router.reload({ only: ['attendees', 'summaryStats'] });
};

onMounted(() => {
    window.addEventListener('click', closeDropdown);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <Head title="Attendees Report" />

    <AppLayout>
        <!-- Top Navigation matching reference line 4627 -->
        <div class="flex items-center justify-between mb-4">
            <Link :href="route('organizer.report.statistics')" class="h-9 w-9 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50 transition shadow-sm">
                <ArrowLeft class="w-4 h-4 text-slate-600" />
            </Link>
            <button @click="openMessageAttendeesModal" class="btn btn-ghost px-4 py-2 text-sm font-black flex items-center gap-2 text-slate-600 hover:bg-slate-100 transition rounded-xl">
                <Megaphone class="w-4 h-4" />
                Message attendees
            </button>
        </div>

        <!-- Section Header matching line 4631 -->
        <div class="rounded-3xl overflow-hidden mb-6" style="background:linear-gradient(120deg,#7c3aed,#a855f7)">
            <div class="p-5 flex items-center justify-between flex-wrap gap-3 text-white">
                <div class="flex items-center gap-4">
                    <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center shrink-0">
                        <Users class="w-6 h-6" />
                    </span>
                    <div>
                        <p class="text-xl font-black leading-tight">{{ props.event?.title || 'Unknown Event' }}</p>
                        <p class="text-white/80 text-[13px] font-bold mt-0.5 uppercase tracking-wider">Attendees Report</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Cards Grid (Sync line 4632) -->
        <div class="grid grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="card p-4 bg-white border border-slate-100 rounded-[20px] text-center shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Paid</p>
                <p class="text-xl font-black text-emerald-600">{{ props.summaryStats.paid }}</p>
            </div>
            <div class="card p-4 bg-white border border-slate-100 rounded-[20px] text-center shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Awaiting</p>
                <p class="text-xl font-black text-amber-500">{{ props.summaryStats.pending }}</p>
            </div>
            <div class="card p-4 bg-white border border-slate-100 rounded-[20px] text-center shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Canceled</p>
                <p class="text-xl font-black text-rose-500">{{ props.summaryStats.canceled }}</p>
            </div>
            <div class="card p-4 bg-white border border-slate-100 rounded-[20px] text-center shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Checked-in</p>
                <p class="text-xl font-black text-slate-800">{{ props.summaryStats.checked_in }}</p>
            </div>
            <div class="card p-4 bg-white border border-slate-100 rounded-[20px] text-center shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Total</p>
                <p class="text-xl font-black text-slate-800">{{ props.summaryStats.total }}</p>
            </div>
            <div class="card p-4 bg-white border border-slate-100 rounded-[20px] text-center shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Revenue</p>
                <p class="text-xl font-black text-violet-600">{{ formatCurrency(props.summaryStats.revenue) }}</p>
            </div>
        </div>

        <!-- Filter Card matching line 4641 -->
        <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm mb-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Search</label>
                        <div class="relative mt-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="localFilters.search" @input="applyFilters" placeholder="Reference, email, or attendee name…"
                                class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-2.5 text-sm font-medium outline-none focus:border-indigo-400 transition shadow-sm"/>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Status</label>
                        <div class="flex flex-wrap gap-4 mt-2">
                            <label class="flex items-center gap-2 text-sm font-bold cursor-pointer">
                                <input type="checkbox" v-model="localFilters.showConfirmed" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500"/>
                                Confirmed
                            </label>
                            <label class="flex items-center gap-2 text-sm font-bold cursor-pointer">
                                <input type="checkbox" v-model="localFilters.showCheckedIn" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500"/>
                                Checked_in
                            </label>
                            <span class="flex items-center gap-2 text-sm font-bold text-slate-300 select-none">
                                <input type="checkbox" checked disabled class="w-4 h-4 rounded text-slate-200 border-slate-200 opacity-50"/>
                                Paid
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-end items-end gap-3">
                    <div class="flex gap-2">
                        <button @click="resetFilters" class="btn btn-ghost px-5 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition">Reset</button>
                        <button @click="toast.info('Printing...')" class="btn bg-slate-50 border border-slate-100 px-5 py-2.5 text-xs font-black uppercase text-slate-600 flex items-center gap-2 rounded-xl hover:bg-slate-100 transition shadow-sm">
                            <Printer class="w-3.5 h-3.5" /> Print
                        </button>
                        <button @click="exportAttendees" class="btn bg-indigo-600 px-6 py-2.5 text-xs font-black uppercase text-white flex items-center gap-2 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20">
                            <Download class="w-3.5 h-3.5" /> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-[11px] font-black text-slate-400 uppercase mb-3 ml-2">{{ props.attendees.data.length }} results showing</p>

        <!-- Attendees Table matching line 4658 -->
        <div class="card bg-white border border-slate-100 rounded-[24px] shadow-sm relative mb-20">
            <div class="">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-50 bg-slate-50/30">
                            <th class="px-6 py-4">Ticket</th>
                            <th class="px-4 py-4">Attendee</th>
                            <th class="px-4 py-4">Checked-in</th>
                            <th class="px-4 py-4">Order</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="attendee in props.attendees.data" :key="attendee.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-slate-50 grid place-items-center shrink-0">
                                        <Ticket class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-slate-800 leading-tight truncate">{{ attendee.ticket_name || 'Standard Entry' }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">#{{ attendee.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-bold text-slate-700">{{ attendee.user?.name || 'Guest' }}</p>
                                <p class="text-[11px] text-slate-400">{{ attendee.user?.email || 'No email' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2" :class="attendee.checkins?.length ? 'text-emerald-600' : 'text-slate-300'">
                                    <Check v-if="attendee.checkins?.length" class="w-3.5 h-3.5 font-black" />
                                    <span class="text-[11px] font-black uppercase">{{ attendee.checkins?.length ? dayjs(attendee.checkins[0].checked_in_at).format('h:mm A') : '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-[11px] font-bold text-slate-500 uppercase">{{ dayjs(attendee.created_at).format('MMM D, YYYY') }}</p>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                    :class="getStatusBadgeClass(attendee.ticket_status)">
                                    {{ attendee.ticket_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right relative">
                                <button @click.stop="toggleRowMenu($event, attendee.id, attendee)" class="h-8 w-8 rounded-lg grid place-items-center hover:bg-slate-100 ml-auto transition border border-transparent group-hover:border-slate-100">
                                    <MoreVertical class="w-4 h-4 text-slate-400" />
                                </button>

                                <!-- Dropdown Menu matching line 4676 Reference -->
                                <div v-if="activeDropdown === attendee.id"
                                     class="absolute right-6 top-12 z-[100] w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 text-left animate-in fade-in slide-in-from-top-2 duration-200"
                                     @click.stop>
                                    <button @click="showPaymentDetails(attendee)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                        <CreditCard class="w-4 h-4 shrink-0 text-slate-400" /> Payment details
                                    </button>
                                    <button @click="showContactAttendee(attendee)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                        <Mail class="w-4 h-4 shrink-0 text-slate-400" /> Contact attendee
                                    </button>
                                    <button @click="showOrderDetails(attendee)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                        <FileText class="w-4 h-4 shrink-0 text-slate-400" /> Details
                                    </button>
                                    <button @click="showResendTicket(attendee)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                        <RotateCw class="w-4 h-4 shrink-0 text-slate-400" /> Resend ticket
                                    </button>
                                    <div class="my-1 border-t border-slate-50"></div>
                                    <button @click="showCheckIn(attendee)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-emerald-600 transition">
                                        <Check class="w-4 h-4 shrink-0" /> {{ attendee.ticket_status === 'checked_in' ? 'Undo check-in' : 'Check-in' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.attendees.data.length">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <p class="text-slate-400 font-black italic">No attendees match this filter.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <Pagination :links="props.attendees.links" />
        </div>

        <!-- Modals -->
        <PaymentDetails :show="showPaymentDetailsModal" :current="paymentDetailsData?.current || null"
            :group="paymentDetailsData?.group || []" :feesSummary="paymentDetailsData?.feesSummary || null"
            @close="closePaymentDetailsModal" />
        <ContactAttendeeModal :show="showContactAttendeeModal" :attendee="contactAttendee"
            @close="() => (showContactAttendeeModal = false)"
            @openBroadcast="() => { showContactAttendeeModal = false; showMessageAttendees = true; }"
            @openLinkUpMessage="() => { showContactAttendeeModal = false; showMessageModal = true; }" />
        <MessageModal :show="showMessageModal" :attendee="contactAttendee"
            @close="() => (showMessageModal = false)" />
        <OrderDetails :show="showOrderDetailsModal" :current="orderDetailsData?.current || null"
            :group="orderDetailsData?.group || []" :feesSummary="orderDetailsData?.feesSummary || null"
            @close="() => (showOrderDetailsModal = false)" />
        <ResendTicket :show="showResendTicketModal" :attendee="resendAttendee" :eventId="event?.slug"
            @close="() => (showResendTicketModal = false)" @done="() => (showResendTicketModal = false)" />
        <CheckIn :show="showCheckInModal" :attendee="checkInAttendee" @close="() => (showCheckInModal = false)"
            @toggled="onCheckInToggled" />
        <MessageAttendees :show="showMessageAttendees" :attendees="props.attendees.data" :event="event"
            @close="() => (showMessageAttendees = false)" />

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
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
</style>
