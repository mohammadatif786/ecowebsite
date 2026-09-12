<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Ticket, CreditCard, TicketX, TrendingUp, Calendar, DollarSign } from 'lucide-vue-next';
import { formatUpdatedAt } from "../../composables/dateformater";

const props = defineProps<{
    TabData: any
}>();

// Tab state
const activeTab = ref('overview');

const organizer = computed(() =>
    props.TabData?.organizer_profile || false
)

const events = computed(() =>
    props.TabData?.events_data || []
)

const payouts = computed(() =>
    props.TabData?.payout_data || []
)

// Watch for TabData changes and log when available
watch(() => props.TabData, (newTabData) => {
    if (newTabData) {
        console.log("props value", newTabData.payout_data);
        console.log("event value", newTabData.events_data);
    }
}, { immediate: true });
const gross_sales = computed(() =>
    events.value.reduce(
        (sum: any, item: any) => sum + Number(item.ticket_sales_sum_total || 0),
        0
    )
)

const platform_fees = computed(() =>
    events.value.reduce(
        (sum: any, item: any) => sum + Number(item.ticket_sales_sum_fee || 0) + Number(item.ticket_sales_sum_tax || 0),
        0
    )
)

const net_earnings = computed(() =>
    Math.max(0, gross_sales.value + platform_fees.value)
)

const total_refunds = computed(() =>
    payouts.value.reduce(
        (sum: any, item: any) => sum + Number(item.net_amount || 0),
        0
    )
)

const total_tickets = computed(() =>
    events.value.reduce(
        (sum: any, item: any) => sum + Number(item.tickets_count || 0),
        0
    )
)

const live_events = computed(() =>
    events.value.filter((item: any) => item.status === 'live').length
)

const tabs = [
    { id: 'overview', name: 'Overview', icon: TrendingUp },
    { id: 'events', name: 'Events', icon: Calendar },
    { id: 'payouts', name: 'Payouts', icon: CreditCard }
];


</script>
<template>
    <div v-if="organizer" class="space-y-4">
        <!-- Tab Navigation -->
        <div class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <Ticket class="w-5 h-5" /> Event Organizer Dashboard
                    </div>
                    <div class="text-sm text-slate-500 mt-1">Events, sales, payouts, refunds</div>
                </div>
                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-sky-50 border-sky-200 text-sky-700">
                    Organizer
                </span>
            </div>

            <!-- Tab Buttons -->
            <div class="flex gap-2 border-b border-slate-200">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-all border-b-2',
                        activeTab === tab.id
                            ? 'text-slate-900 border-slate-900'
                            : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300'
                    ]"
                >
                    <component :is="tab.icon" class="w-4 h-4" />
                    {{ tab.name }}
                </button>
            </div>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="space-y-4">
            <!-- Main Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-500 font-black">Total Events</div>
                            <div class="mt-1 text-3xl font-black">{{ events?.length ?? 0 }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-100 grid place-items-center">
                            <Calendar class="w-5 h-5 text-slate-700" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-500 font-black">Gross Sales</div>
                            <div class="mt-1 text-3xl font-black">${{ gross_sales.toFixed(2) }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                            <DollarSign class="w-5 h-5 text-emerald-700" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-500 font-black">Platform Fees</div>
                            <div class="mt-1 text-3xl font-black text-rose-700">${{ platform_fees.toFixed(2) }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 grid place-items-center">
                            <CreditCard class="w-5 h-5 text-rose-700" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-500 font-black">Net Earnings</div>
                            <div class="mt-1 text-3xl font-black text-emerald-700">${{ net_earnings.toFixed(2) }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                            <TrendingUp class="w-5 h-5 text-sky-700" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                            <Calendar class="w-4 h-4 text-emerald-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Live Events</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ live_events }}</div>
                    <div class="text-xs text-slate-500 mt-1">Currently active</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-100 grid place-items-center">
                            <Ticket class="w-4 h-4 text-sky-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Total Tickets</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ total_tickets }}</div>
                    <div class="text-xs text-slate-500 mt-1">Tickets sold</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-100 grid place-items-center">
                            <CreditCard class="w-4 h-4 text-amber-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Total Refunds</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">${{ total_refunds.toFixed(2) }}</div>
                    <div class="text-xs text-slate-500 mt-1">Refunded amount</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 border border-purple-100 grid place-items-center">
                            <DollarSign class="w-4 h-4 text-purple-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Avg per Event</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">
                        ${{ events.length > 0 ? (net_earnings / events.length).toFixed(2) : '0.00' }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">Average earnings</div>
                </div>
            </div>
        </div>

        <!-- Events Tab -->
        <div v-if="activeTab === 'events'" class="space-y-4">
            <div class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-lg font-black tracking-tight">Events Management</div>
                        <div class="text-sm text-slate-500 mt-1">Manage your events and track performance</div>
                    </div>
                    <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-sky-50 border-sky-200 text-sky-700">
                        {{ events?.length ?? 0 }} Events
                    </span>
                </div>

                <div class="mt-4 overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                    <table class="w-full text-[0.9rem]">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-4 py-3 font-black">Event</th>
                                <th class="text-left px-4 py-3 font-black">Tickets</th>
                                <th class="text-left px-4 py-3 font-black">Fees</th>
                                <th class="text-left px-4 py-3 font-black">Refunds</th>
                                <th class="text-left px-4 py-3 font-black">Net</th>
                                <th class="text-left px-4 py-3 font-black">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="events.length > 0" v-for="item in events">
                                <td class="px-4 py-3 align-top font-black">
                                    {{ item?.title ?? '--' }}
                                </td>
                                <td class="px-4 py-3 align-top text-slate-700 font-bold">
                                    {{ item?.tickets_count ?? 0 }}
                                </td>
                                <td class="px-4 py-3 align-top font-black text-rose-700">
                                    ${{ item?.ticket_sales_sum_fee ?? 0 }}
                                </td>
                                <td class="px-4 py-3 align-top font-black text-amber-700">
                                    ${{ item?.approved_refund_sum ?? 0 }}
                                </td>
                                <td class="px-4 py-3 align-top font-black text-emerald-700">
                                    ${{ item?.ticket_sales_sum_total ?? 0 }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold"
                                        :class="item?.status === 'live' ? `bg-emerald-50 border-emerald-200 text-emerald-700` : `bg-slate-50 border-slate-200 text-slate-700`">
                                        {{ item?.status ?? '--' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-else>
                                <td colspan="6" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                    No events found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payouts Tab -->
        <div v-if="activeTab === 'payouts'" class="space-y-4">
            <div class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-lg font-black tracking-tight">Payout Activity</div>
                        <div class="text-sm text-slate-500 mt-1">Track your earnings and payouts</div>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                        <CreditCard class="w-5 h-5 text-sky-700" />
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                    <table class="w-full text-[0.9rem]">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-4 py-3 font-black">ID</th>
                                <th class="text-left px-4 py-3 font-black">Event</th>
                                <th class="text-left px-4 py-3 font-black">Amount</th>
                                <th class="text-left px-4 py-3 font-black">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="payouts.length > 0" v-for="item in payouts">
                                <td class="px-4 py-3 align-top font-black">
                                    {{ item?.reference?.slice(0, 7) ?? '--' }}
                                </td>
                                <td class="px-4 py-3 align-top font-black">
                                    {{ item?.event?.title ?? '--' }}
                                </td>
                                <td class="px-4 py-3 align-top font-black text-emerald-700">
                                    {{ item?.net_amount ?? '--'}}
                                </td>
                                <td class="text-slate-500 font-extrabold px-4 py-3 align-top">
                                    {{ formatUpdatedAt(item?.created_at, false) ?? '--' }}
                                </td>
                            </tr>
                            <tr v-else>
                                <td colspan="4" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                    No payouts found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- No Organizer Access -->
    <div v-else class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-10 trim flex flex-col items-center justify-center text-center">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-200 grid place-items-center mb-4">
            <TicketX class="w-7 h-7 text-rose-700" />
        </div>

        <div class="text-xl font-black tracking-tight">
            Organizer Access Required
        </div>

        <p class="text-sm text-slate-500 mt-2 max-w-md">
            You're not registered as an event organizer. This dashboard is only available to users who create and manage events.
        </p>
    </div>
</template>