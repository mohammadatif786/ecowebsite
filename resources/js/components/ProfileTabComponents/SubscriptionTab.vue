<script setup lang="ts">
import { ref, computed } from "vue";
import { Crown, Ban, History } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { formatUpdatedAt } from "../../composables/dateformater";

const props = defineProps<{
    TabData: any
}>()

const activeTab = ref('current')

const active = computed(() =>
    props.TabData.active || null
)

const days_left = computed(() =>
    props.TabData.days_left || 0
)

const history = computed(() =>
    props.TabData.subscriptions || []
)

const cancelSubscription = (id: any) => {
    router.get(route('frontend.user.subscription.cancel', { plan: id }))
}

const tabs = [
    { id: 'current', name: 'Current Plan', icon: Crown },
    { id: 'history', name: 'History', icon: History }
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

        <!-- Current Plan Tab -->
        <div v-if="activeTab === 'current'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <Crown class="w-5 h-5 origin-center" />
                        Current Subscription
                    </div>
                    <div class="text-sm text-slate-500 mt-1">Plan details and status</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-sky-50 border-sky-200 text-sky-700">
                    {{ active?.plan?.title ?? 'No Active Plan' }}
                </span>
            </div>

            <div v-if="active" class="space-y-4">
                <!-- Main Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Status</div>
                        <div class="mt-1 text-2xl font-black text-emerald-700">Active</div>
                        <div class="text-xs text-slate-500 font-bold mt-1">Subscription status</div>
                    </div>

                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Duration</div>
                        <div class="mt-1 text-2xl font-black">{{ active?.plan?.duration ?? '--' }}</div>
                        <div class="text-xs text-slate-500 font-bold mt-1">Plan duration</div>
                    </div>

                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Days Left</div>
                        <div class="mt-1 text-2xl font-black text-sky-700">{{ days_left === 0 ? 'Today' : days_left }}</div>
                        <div class="text-xs text-slate-500 font-bold mt-1">Remaining days</div>
                    </div>
                </div>

                <!-- Date Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Start Date</div>
                        <div class="mt-1 text-xl font-black">{{ formatUpdatedAt(active?.start_date, false) ?? "--" }}</div>
                        <div class="text-xs text-slate-500 font-bold mt-1">Subscription began</div>
                    </div>

                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Expires</div>
                        <div class="mt-1 text-xl font-black text-rose-700">{{ formatUpdatedAt(active?.end_date, false) ?? "--" }}</div>
                        <div class="text-xs text-slate-500 font-bold mt-1">Subscription ends</div>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">What you get</div>
                    <div class="mt-2 text-sm text-slate-700 font-bold">
                        Premium visibility, higher match limits, boosted posts, and priority support.
                    </div>
                </div>

                <!-- Cancel Button -->
                <div class="grid gap-2">
                    <button @click="cancelSubscription(active?.id)"
                        class="relative overflow-hidden flex items-center gap-[0.55rem] justify-center rounded-2xl border border-red-300/35 bg-gradient-to-b from-white to-[#fff7f7] font-black px-[0.9rem] py-[0.7rem] transition-all duration-150 shadow-[0_10px_22px_rgba(2,6,23,.06)] hover:-translate-y-px hover:shadow-[0_16px_30px_rgba(2,6,23,.10)] active:translate-y-0 text-red-900 w-full btn btn-danger">
                        <Ban class="w-5 h-5 text-red-500" /> Cancel Subscription
                    </button>
                </div>
            </div>

            <!-- No Active Plan State -->
            <div v-else class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center mx-auto mb-4">
                    <Crown class="w-8 h-8 text-slate-400" />
                </div>
                <div class="text-lg font-black text-slate-900 mb-2">No Active Subscription</div>
                <div class="text-sm text-slate-500">Subscribe to a plan to access premium features</div>
            </div>
        </div>

        <!-- History Tab -->
        <div v-if="activeTab === 'history'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">Subscription History</div>
                    <div class="text-sm text-slate-500 mt-1">Past subscriptions and changes</div>
                </div>
                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-sky-50 border-sky-200 text-sky-700">
                    {{ history.length }} Records
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Plan</th>
                            <th class="text-left px-4 py-3 font-black">Duration</th>
                            <th class="text-left px-4 py-3 font-black">Start</th>
                            <th class="text-left px-4 py-3 font-black">Expires</th>
                            <th class="text-left px-4 py-3 font-black">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="history.length > 0" v-for="item in history">
                            <td class="px-4 py-3 align-top font-black">
                                {{ item?.subscription_id?.slice(0, 10) ?? "--" }}
                            </td>
                            <td class="px-4 py-3 align-top font-black">
                                {{ item?.plan?.title ?? "--" }}
                            </td>
                            <td class="px-4 py-3 align-top text-slate-700 font-bold">
                                {{ item?.plan?.duration ?? "--" }}
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">
                                {{ formatUpdatedAt(item?.start_date, false) ?? "--" }}
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">
                                {{ formatUpdatedAt(item?.end_date, false) ?? "--" }}
                            </td>
                            <td class="px-4 py-3 align-top">
                                <span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold"
                                    :class="item.status === '1'
                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                                        : 'bg-slate-50 border-slate-200 text-slate-700'">
                                    {{ item.status === '1' ? 'Active' : 'Expired' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="6" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No subscription history found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>