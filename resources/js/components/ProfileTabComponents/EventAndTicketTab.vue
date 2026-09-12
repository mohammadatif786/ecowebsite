<script setup lang="ts">
import { computed, ref } from "vue";
import { formatUpdatedAt } from "../../composables/dateformater";
import { Ticket, XCircle, TrendingUp } from 'lucide-vue-next';

const props = defineProps<{
    TabData: any
}>()

// Tab state
const activeTab = ref('purchased');

// reactive computed properties
const filterPurchased = computed(() =>
    props.TabData?.filter((item: any) => item.ticket_status === 'confirmed') || []
)

const filterCancelled = computed(() =>
    props.TabData?.filter((item: any) => item.ticket_status === 'cancelled') || []
)

const totalSpent = computed(() =>
    filterPurchased.value.reduce((acc: number, item: any) => acc + (item.total || 0), 0)
)

const totalRefunded = computed(() =>
    filterCancelled.value.reduce((acc: number, item: any) => acc + (item.total || 0), 0)
)

const checkedInTickets = computed(() =>
    filterPurchased.value.filter((item: any) => item.checkins && item.checkins.length > 0)
)

const tabs = [
    { id: 'purchased', name: 'Purchased Tickets', icon: Ticket },
    { id: 'cancelled', name: 'Cancelled Tickets', icon: XCircle },
    { id: 'summary', name: 'Summary', icon: TrendingUp }
]

</script>
<template>
    <div class="bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
        <!-- Tab Navigation -->
        <div class="flex space-x-1 border-b border-slate-200 mb-6">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors border-b-2',
                    activeTab === tab.id
                        ? 'text-sky-700 border-sky-700 bg-sky-50/50'
                        : 'text-slate-600 border-transparent hover:text-slate-800 hover:bg-slate-50'
                ]"
            >
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.name }}
            </button>
        </div>

        <!-- Purchased Tickets Tab -->
        <div v-if="activeTab === 'purchased'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Tickets Purchased</div>
                    <div class="text-sm text-slate-500 mt-1">Your confirmed ticket purchases</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-sky-50 border-sky-200 text-sky-700">
                    {{ filterPurchased.length }}
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">Ticket ID</th>
                            <th class="text-left px-4 py-3 font-black">Event</th>
                            <th class="text-left px-4 py-3 font-black">Amount</th>
                            <th class="text-left px-4 py-3 font-black">Checked-in</th>
                            <th class="text-left px-4 py-3 font-black">Scanned by</th>
                            <th class="text-left px-4 py-3 font-black">Status</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="filterPurchased.length > 0" v-for="item in filterPurchased" :key="item.id">
                            <td class="px-4 py-3 align-top font-black">{{ item.id ?? '-' }}</td>
                            <td class="px-4 py-3 align-top">{{ item.event?.title ?? '-' }}</td>
                            <td class="px-4 py-3 align-top font-black">${{ item.total ?? '-' }}</td>
                            <td class="px-4 py-3 align-top font-black">{{item.checkins.map((checkin: any) =>
                                formatUpdatedAt(checkin.checked_in_at, true)).join(', ') ?? '-'}}</td>
                            <td class="px-4 py-3 align-top font-black">{{item.checkins.map((checkin: any) =>
                                checkin.scanned_by_email).join(', ') ?? '-'}}</td>
                            <td class="px-4 py-3 align-top">
                                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-emerald-200 rounded-full px-[0.65rem] py-[0.35rem] bg-emerald-50 text-xs font-extrabold text-emerald-700">
                                    {{ item.ticket_status ?? '-' }}
                                </span>
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">
                                {{ formatUpdatedAt(item.updated_at, false) ?? '-' }}
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="7" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No purchased tickets found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cancelled Tickets Tab -->
        <div v-if="activeTab === 'cancelled'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Tickets Cancelled</div>
                    <div class="text-sm text-slate-500 mt-1">Refund and cancellation history</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-rose-50 border-rose-200 text-rose-700">
                    {{ filterCancelled.length }}
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">Ticket ID</th>
                            <th class="text-left px-4 py-3 font-black">Event</th>
                            <th class="text-left px-4 py-3 font-black">Amount</th>
                            <th class="text-left px-4 py-3 font-black">Status</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="filterCancelled.length > 0" v-for="item in filterCancelled" :key="item.id">
                            <td class="px-4 py-3 align-top font-black">{{ item.id ?? '-' }}</td>
                            <td class="px-4 py-3 align-top">{{ item.event?.title ?? '-' }}</td>
                            <td class="px-4 py-3 align-top font-black">${{ item.total ?? '-' }}</td>
                            <td class="px-4 py-3 align-top">
                                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-rose-200 rounded-full px-[0.65rem] py-[0.35rem] bg-rose-50 text-xs font-extrabold text-rose-700">
                                    {{ item.ticket_status ?? '-' }}
                                </span>
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">{{
                                formatUpdatedAt(item.updated_at, false) ?? '-' }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="5" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No cancelled tickets found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Tab -->
        <div v-if="activeTab === 'summary'" class="space-y-6">
            <div>
                <div class="text-lg font-black tracking-tight">Ticket Summary</div>
                <div class="text-sm text-slate-500 mt-1">Your ticket purchase overview</div>
            </div>

            <!-- Additional Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-100 grid place-items-center">
                            <Ticket class="w-4 h-4 text-sky-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Purchased</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ filterPurchased.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Active tickets</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                            <Ticket class="w-4 h-4 text-emerald-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Checked-in</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ checkedInTickets.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Tickets used</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-100 grid place-items-center">
                            <XCircle class="w-4 h-4 text-rose-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Cancelled</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ filterCancelled.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Refunded tickets</div>
                </div>
            </div>
        </div>
    </div>
</template>