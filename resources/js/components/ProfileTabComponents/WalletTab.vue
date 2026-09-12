<script setup lang="ts">
import { ref, computed } from "vue";
import { Wallet, TrendingUp, History } from 'lucide-vue-next';
import { formatUpdatedAt } from "../../composables/dateformater";

const props = defineProps<{
    TabData: any
}>();

const activeTab = ref('overview')

const transactions = computed(() =>
    props.TabData?.transactions || []
)

const totalWallet = computed(() =>
    props.TabData?.totalWallet || 0
)

const incoming = computed(() =>
    transactions.value
        .filter((item: any) => item.type === 'deposit' || item.type === 'GIFT_COLLECT' || item.type === 'LIVE_COINS_PAYOUT' || item.type === 'wallet' || item.type === 'live deposit')
        .reduce((sum: any, item: any) => sum + Number(item.amount || 0), 0) ?? 0
)

const outgoing = computed(() =>
    transactions.value
        .filter((item: any) => item.type === 'transfer' || item.type === 'MARKET_BUY' || item.type === 'payment' || item.type === 'subscription' || item.type === 'send')
        .reduce((sum: any, item: any) => sum + Number(item.amount || 0), 0) ?? 0
)

const totalPayouts = computed(() =>
    props.TabData?.payouts || 0
)

const tabs = [
    { id: 'overview', name: 'Overview', icon: Wallet },
    { id: 'transactions', name: 'Transactions', icon: History },
    { id: 'analytics', name: 'Analytics', icon: TrendingUp }
]

function txPill(kind: any) {
    const map: Record<string, string> = {
        'transfer': `bg-rose-50 border-rose-200 text-rose-700`,
        'deposit': `bg-emerald-50 border-emerald-200 text-emerald-700`,
        'MARKET_BUY': `bg-amber-50 border-amber-200 text-amber-800`,
        'payment': `bg-yellow-50 border-yellow-200 text-yellow-800`,
        'subscription': `bg-slate-50 border-slate-200 text-slate-700`,
        'wallet': `bg-rose-50 border-rose-200 text-rose-700`,
        'GIFT_COLLECT': `bg-sky-50 border-sky-200 text-sky-700`,
        'coin payment': `bg-violet-50 border-violet-200 text-violet-700`,
        'LIVE_COINS_PAYOUT': `bg-purple-50 border-purple-200 text-purple-700`,
    };
    const cls = map[kind] || `bg-slate-50 border-slate-200 text-slate-700`;
    return `<span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold ${cls}">${kind}</span>`;
}

function getTransactionTypes() {
    const types: Record<string, number> = {};
    transactions.value.forEach((item: any) => {
        if (item.type) {
            types[item.type] = (types[item.type] || 0) + 1;
        }
    });
    return types;
}

</script>
<template>
    <div class="bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
        <!-- Tab Navigation -->
        <div class="flex space-x-1 border-b border-slate-200 mb-6">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-all border-b-2',
                activeTab === tab.id
                    ? 'text-slate-900 border-slate-900'
                    : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300'
            ]">
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.name }}
            </button>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <Wallet class="w-5 h-5" /> Wallet Overview
                    </div>
                    <div class="text-sm text-slate-500 mt-1">Balance, transfers, and payouts</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                    <Wallet class="w-5 h-5 text-emerald-700" />
                </div>
            </div>

            <!-- Current Balance -->
            <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] mb-4">
                <div class="text-xs text-slate-500 font-black">Current balance</div>
                <div class="mt-1 text-5xl font-black">${{ totalWallet }}</div>
                <div class="text-xs text-slate-500 font-bold mt-1">Available balance</div>
            </div>

            <!-- Wallet Activity -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Incoming</div>
                    <div class="mt-1 text-3xl font-black text-emerald-700">${{ incoming }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Money received</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Outgoing</div>
                    <div class="mt-1 text-3xl font-black text-rose-700">${{ outgoing }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Money sent</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Total Payouts</div>
                    <div class="mt-1 text-3xl font-black text-sky-700">${{ totalPayouts }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Cash-outs received</div>
                </div>
            </div>
        </div>

        <!-- Transactions Tab -->
        <div v-if="activeTab === 'transactions'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">Transaction History</div>
                    <div class="text-sm text-slate-500 mt-1">Send/Receive/Bills/Market/Gifts/Coins</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-white border-slate-200 text-slate-800">
                    {{ transactions.length }} Transactions
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Type</th>
                            <th class="text-left px-4 py-3 font-black">Amount</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="transactions.length > 0" v-for="item in transactions">
                            <td class="px-4 py-3 align-top font-black">{{ item?.id?.slice(0, 10) ?? '--' }}</td>
                            <td class="px-4 py-3 align-top" v-html="txPill(item?.type)"></td>
                            <td class="px-4 py-3 align-top font-black">
                                ${{ Number(item?.amount ?? 0).toFixed(2) }}
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">{{ formatUpdatedAt(item?.date,
                                false) ?? '--' }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="4" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No transactions found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div v-if="activeTab === 'analytics'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">Wallet Analytics</div>
                    <div class="text-sm text-slate-500 mt-1">Financial insights and trends</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                    <TrendingUp class="w-5 h-5 text-sky-700" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Cash Flow Summary -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Net Cash Flow</div>
                    <div class="mt-1 text-3xl font-black"
                        :class="incoming - outgoing >= 0 ? 'text-emerald-700' : 'text-rose-700'">
                        ${{ (incoming - outgoing).toFixed(2) }}
                    </div>
                    <div class="text-xs text-slate-500 font-bold mt-1">
                        {{ incoming - outgoing >= 0 ? 'Positive cash flow' : 'Negative cash flow' }}
                    </div>
                </div>

                <!-- Transaction Count -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Total Transactions</div>
                    <div class="mt-1 text-3xl font-black text-slate-900">{{ transactions.length }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">All transaction types</div>
                </div>

                <!-- Average Transaction -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Average Transaction</div>
                    <div class="mt-1 text-3xl font-black text-amber-700">
                        ${{ transactions.length > 0 ? ((incoming + outgoing) / transactions.length).toFixed(2) : '0.00'
                        }}
                    </div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Per transaction average</div>
                </div>

                <!-- Payout Ratio -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Payout Ratio</div>
                    <div class="mt-1 text-3xl font-black text-purple-700">
                        {{ totalWallet > 0 ? ((totalPayouts / totalWallet) * 100).toFixed(1) : '0.0' }}%
                    </div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Of total balance</div>
                </div>
            </div>

            <!-- Transaction Type Breakdown -->
            <div class="mt-6 relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                <div class="text-xs text-slate-500 font-black mb-4">Transaction Types</div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div v-for="(count, type) in getTransactionTypes()" :key="type" class="text-center">
                        <div class="text-lg font-black">{{ count }}</div>
                        <div class="text-xs text-slate-500 mt-1" v-html="txPill(type)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>