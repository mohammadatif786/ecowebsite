<script setup lang="ts">
import { ref, computed } from "vue";
import { formatUpdatedAt } from "../../composables/dateformater";
import { ShoppingBag, ShoppingCart, TrendingUp } from 'lucide-vue-next';

const props = defineProps<{
    TabData: any
}>()
const activeTab = ref('orders')

// reactive computed properties
const filterdata = computed(() =>
    props.TabData || []
)

const totalSpend = computed(() =>
    filterdata.value?.reduce((acc: number, item: any) => {
        const itemTotal = parseFloat(item.total) || 0;
        return acc + itemTotal;
    }, 0) || 0
)

const deliveredOrders = computed(() =>
    filterdata.value.filter(item => item.status === 'delivered')
)

const cancelledOrders = computed(() =>
    filterdata.value.filter(item => item.status === 'cancelled')
)

const processingOrders = computed(() =>
    filterdata.value.filter(item => item.status !== 'delivered' && item.status !== 'cancelled')
)

const tabs = [
    { id: 'orders', name: 'Orders', icon: ShoppingCart },
    { id: 'spend', name: 'Spend Summary', icon: TrendingUp }
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

        <!-- Orders Tab -->
        <div v-if="activeTab === 'orders'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Marketplace Orders</div>
                    <div class="text-sm text-slate-500 mt-1">Items purchased</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-emerald-50 border-emerald-200 text-emerald-700">
                    {{ filterdata.length }}
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">Order</th>
                            <th class="text-left px-4 py-3 font-black">Item</th>
                            <th class="text-left px-4 py-3 font-black">Total</th>
                            <th class="text-left px-4 py-3 font-black">Status</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="filterdata.length > 0" v-for="item in filterdata" :key="item.id">
                            <td class="px-4 py-3 align-top font-black">{{ item.id ?? '-' }}</td>
                            <td class="px-4 py-3 align-top">
                                <ul class="list-disc list-inside space-y-1">
                                    <li v-for="(orderItem, index) in item.items" :key="index"
                                        class="text-sm font-medium text-slate-700">
                                        {{ orderItem?.product?.name ?? '--' }}
                                    </li>
                                </ul>
                            </td>

                            <td class="px-4 py-3 align-top font-black">${{ item.net_total ?? 0 }}</td>
                            <td class="px-4 py-3 align-top">
                                <span v-if="item.status === 'delivered'"
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-emerald-200 rounded-full px-[0.65rem] py-[0.35rem] bg-emerald-50 text-xs font-extrabold text-emerald-700">
                                    Delivered
                                </span>
                                <span v-else-if="item.status === 'cancelled'"
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-amber-200 rounded-full px-[0.65rem] py-[0.35rem] bg-amber-50 text-xs font-extrabold text-amber-800">
                                    Cancelled
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-sky-200 rounded-full px-[0.65rem] py-[0.35rem] bg-sky-50 text-xs font-extrabold text-sky-700">
                                    Processing
                                </span>
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">
                                {{ formatUpdatedAt(item.created_at, false) ?? '-' }}
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="5" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No records found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Spend Summary Tab -->
        <div v-if="activeTab === 'spend'" class="space-y-6">
            <div>
                <div class="text-lg font-black tracking-tight">Spend Summary</div>
                <div class="text-sm text-slate-500 mt-1">Your marketplace spending overview</div>
            </div>

            <!-- Main Spend Card -->
            <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-500 font-black">Total spend</div>
                        <div class="mt-1 text-4xl font-black">${{ totalSpend.toFixed(2) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                        <ShoppingBag class="w-5 h-5 text-sky-700" />
                    </div>
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                            <ShoppingBag class="w-4 h-4 text-emerald-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Delivered</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ deliveredOrders.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Orders completed</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-100 grid place-items-center">
                            <ShoppingBag class="w-4 h-4 text-sky-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Processing</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ processingOrders.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Orders in progress</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-100 grid place-items-center">
                            <ShoppingBag class="w-4 h-4 text-amber-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Cancelled</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ cancelledOrders.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Orders cancelled</div>
                </div>
            </div>
        </div>
    </div>
</template>