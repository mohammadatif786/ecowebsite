<script setup lang="ts">
import { ref, computed } from "vue";
import { Coins, ShoppingCart, Info } from 'lucide-vue-next';
import { formatUpdatedAt } from "../../composables/dateformater";

const props = defineProps<{
    coins: any;
    TabData: any
}>()

const activeTab = ref('balance')

const coinsHistory = computed(() =>
    props.TabData?.coins_history || []
)

const coinsEarned = computed(() =>
    props.TabData?.coins_earned || 0
)

const coinsSpent = computed(() =>
    props.TabData?.coins_spent || 0
)

const tabs = [
    { id: 'balance', name: 'Balance', icon: Coins },
    { id: 'purchases', name: 'Purchases', icon: ShoppingCart },
    { id: 'info', name: 'Info', icon: Info }
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

        <!-- Balance Tab -->
        <div v-if="activeTab === 'balance'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <Coins class="w-5 h-5" /> LinkUp Coins Balance
                    </div>
                    <div class="text-sm text-slate-500 mt-1">In-app coins (not crypto)</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-violet-50 border border-violet-100 grid place-items-center">
                    <Coins class="w-5 h-5 text-violet-700" />
                </div>
            </div>

            <!-- Current Balance -->
            <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] mb-4">
                <div class="text-xs text-slate-500 font-black">Current balance</div>
                <div class="mt-1 text-5xl font-black">{{ props.coins }}</div>
                <div class="text-xs text-slate-500 font-bold mt-1">LinkUp Coins</div>
            </div>

            <!-- Coins Activity -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Coins earned</div>
                    <div class="mt-1 text-3xl font-black text-emerald-700">{{ coinsEarned }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">From gifts and activities</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Coins spent</div>
                    <div class="mt-1 text-3xl font-black text-rose-700">{{ coinsSpent }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">On gifts and activities</div>
                </div>
            </div>
        </div>

        <!-- Purchases Tab -->
        <div v-if="activeTab === 'purchases'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">Coin Purchases</div>
                    <div class="text-sm text-slate-500 mt-1">Pack, coins bought, bonus, paid</div>
                </div>
                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-violet-50 border-violet-200 text-violet-700">
                    {{ coinsHistory.length }} Purchases
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Pack</th>
                            <th class="text-left px-4 py-3 font-black">Coins</th>
                            <th class="text-left px-4 py-3 font-black">Bonus</th>
                            <th class="text-left px-4 py-3 font-black">Paid</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="coinsHistory.length > 0" v-for="item in coinsHistory">
                            <td class="px-4 py-3 align-top font-black">{{ item?.id ?? '--' }}</td>
                            <td class="px-4 py-3 align-top font-black">Coin Pack</td>
                            <td class="px-4 py-3 align-top font-black">{{ item?.quantity ?? 0 }}</td>
                            <td class="px-4 py-3 align-top font-black text-emerald-700">0</td>
                            <td class="px-4 py-3 align-top font-black">${{ item?.stripe_price ?? '0.00' }}</td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">
                                {{ formatUpdatedAt(item?.created_at, false) }}
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="6" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No coin purchases found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Tab -->
        <div v-if="activeTab === 'info'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">About LinkUp Coins</div>
                    <div class="text-sm text-slate-500 mt-1">What you can do with coins</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-violet-50 border border-violet-100 grid place-items-center">
                    <Info class="w-5 h-5 text-violet-700" />
                </div>
            </div>

            <div class="space-y-4">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">What LinkUp Coins do</div>
                    <div class="mt-2 text-sm text-slate-700 font-bold">
                        Buy gifts, boost your profile, unlock perks, and fast checkout at events.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Send Gifts</div>
                        <div class="mt-2 text-sm text-slate-700">
                            Use coins to send virtual gifts to other users and show appreciation.
                        </div>
                    </div>

                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Boost Profile</div>
                        <div class="mt-2 text-sm text-slate-700">
                            Increase your visibility and get more matches with profile boosts.
                        </div>
                    </div>

                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Unlock Perks</div>
                        <div class="mt-2 text-sm text-slate-700">
                            Access premium features and exclusive content with coins.
                        </div>
                    </div>

                    <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                        <div class="text-xs text-slate-500 font-black">Event Checkout</div>
                        <div class="mt-2 text-sm text-slate-700">
                            Fast and easy checkout for events using your coin balance.
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-violet-50 p-[14px]">
                    <div class="text-xs text-slate-500 font-black text-violet-700">Important Note</div>
                    <div class="mt-2 text-sm text-slate-700 font-bold">
                        LinkUp Coins are in-app virtual currency and cannot be exchanged for real money or cryptocurrency.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>