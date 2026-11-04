<script setup lang="ts">
import { Radio } from 'lucide-vue-next';
import { computed, watch } from 'vue';

const props = defineProps<{
    TabData: any[]
}>()

// Log when TabData changes to help verify API response
watch(() => props.TabData, (newData) => {
    console.log('LinkUp Live TabData Updated:', newData)
}, { immediate: true, deep: true })

const coinUsdRate = 0.01;

// Compute summaries from real TabData
const totalSessions = computed(() => props.TabData?.length || 0);
const totalWatchers = computed(() => props.TabData?.reduce((sum, item) => sum + (item.watchers || 0), 0) || 0);
const totalLikes = computed(() => props.TabData?.reduce((sum, item) => sum + (item.likes || 0), 0) || 0);
const totalCoins = computed(() => props.TabData?.reduce((sum, item) => sum + (item.coins || 0), 0) || 0);
const revenue = computed(() => props.TabData?.reduce((sum, item) => sum + (item.revenue || 0), 0) || 0);
const creatorShare = computed(() => props.TabData?.reduce((sum, item) => sum + (item.creator_share || 0), 0) || 0);
const platformShare = computed(() => props.TabData?.reduce((sum, item) => sum + (item.platform_commission || 0), 0) || 0);
const totalTransferred = computed(() => props.TabData?.reduce((sum, item) => sum + (item.transfer_amount || 0), 0) || 0);
const totalWatchTimeMins = computed(() => props.TabData?.reduce((sum, item) => sum + (item.watch_time_mins || 0), 0) || 0);

</script>
<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Left summary -->
        <div
            class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5 trim">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <div class="flex items-center gap-2">
                            <Radio class="w-5 h-5" />
                            <span>LinkUp Live</span>
                        </div>
                    </div>
                    <div class="text-sm text-slate-500 mt-1">Live broadcasts + engagement + coins</div>
                </div>

                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-purple-50 border-purple-200 text-purple-700">
                    Live Creator
                </span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] trim">
                    <div class="text-xs text-slate-500 font-black">Live broadcasts</div>
                    <div class="mt-1 text-3xl font-black">{{ totalSessions }}</div>
                    <div class="text-xs text-slate-500 font-bold">sessions</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] trim">
                    <div class="text-xs text-slate-500 font-black">Watchers</div>
                    <div class="mt-1 text-3xl font-black">{{ totalWatchers }}</div>
                    <div class="text-xs text-slate-500 font-bold">unique</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] trim">
                    <div class="text-xs text-slate-500 font-black">Likes</div>
                    <div class="mt-1 text-3xl font-black">{{ totalLikes }}</div>
                    <div class="text-xs text-slate-500 font-bold">total</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] trim">
                    <div class="text-xs text-slate-500 font-black">Watch time</div>
                    <div class="mt-1 text-3xl font-black">{{ totalWatchTimeMins.toLocaleString() }}</div>
                    <div class="text-xs text-slate-500 font-bold">minutes</div>
                </div>

                <div
                    class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] col-span-2">
                    <div class="text-xs text-slate-500 font-black">Coins generated from Live</div>
                    <div class="mt-1 text-5xl font-black text-violet-700">{{ totalCoins }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Coins received through Live interactions</div>

                </div>

                <div
                    class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] col-span-2">
                    <div class="text-xs text-slate-500 font-black">Revenue generated from coins</div>
                    <div class="mt-1 text-4xl font-black text-emerald-700">${{ revenue.toFixed(2) }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Gross revenue value from coin activity</div>

                </div>

                <div
                    class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] col-span-2">
                    <div class="text-xs text-slate-500 font-black">Transferred to Wallet (50/50 split)</div>
                    <div class="mt-2 grid grid-cols-2 gap-2">

                        <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] trim">
                            <div class="text-xs text-slate-500 font-black">Transferred</div>
                            <div class="mt-1 text-2xl font-black text-emerald-700">${{ totalTransferred.toFixed(2) }}
                            </div>
                        </div>

                        <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px] trim">
                            <div class="text-xs text-slate-500 font-black">Fees/Commission</div>
                            <div class="mt-1 text-2xl font-black text-sky-700">${{ platformShare.toFixed(2) }}</div>
                        </div>

                    </div>
                    <div class="text-xs text-slate-500 font-bold mt-2">Real data from wallet transactions.</div>
                </div>
            </div>
        </div>
        <!-- Activity table -->
        <div
            class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5 lg:col-span-2 trim">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Live Broadcast Activity</div>
                    <div class="text-sm text-slate-500 mt-1">Broadcasts, engagement, coins, payout</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-violet-50 border-violet-200 text-violet-700">{{
                        TabData.length }}</span>
            </div>

            <div class="mt-4 overflow-x-auto rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem] min-w-[1000px]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Title</th>
                            <th class="text-left px-4 py-3 font-black">Watchers</th>
                            <th class="text-left px-4 py-3 font-black">Watch Time</th>
                            <th class="text-left px-4 py-3 font-black">Likes</th>
                            <th class="text-left px-4 py-3 font-black">Coins</th>
                            <th class="text-left px-4 py-3 font-black">Revenue</th>
                            <th class="text-left px-4 py-3 font-black">Creator (50%)</th>
                            <th class="text-left px-4 py-3 font-black">Transferred</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="TabData.length > 0" v-for="item in TabData" :key="item.id"
                            class="border-t border-slate-100">
                            <td class="px-4 py-3 align-top font-black whitespace-nowrap">LIVE-{{ item.id }}</td>
                            <td class="px-4 py-3 align-top font-black min-w-[150px]">{{ item.title }}</td>
                            <td class="px-4 py-3 align-top text-slate-700 font-bold whitespace-nowrap">
                                {{ Number(item.watchers || 0).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 align-top text-slate-700 font-bold whitespace-nowrap">
                                {{ Number(item.watch_time_mins || 0).toLocaleString() }}m
                            </td>
                            <td class="px-4 py-3 align-top text-slate-700 font-bold whitespace-nowrap">
                                {{ Number(item.likes || 0).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 align-top font-black text-violet-700 whitespace-nowrap">
                                {{ Number(item.coins || 0).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 align-top font-black text-emerald-700 whitespace-nowrap">${{
                                Number(item.revenue ||
                                0).toFixed(2) }}</td>
                            <td class="px-4 py-3 align-top font-black text-sky-700 whitespace-nowrap">${{
                                Number(item.creator_share ||
                                0).toFixed(2) }}</td>
                            <td class="px-4 py-3 align-top font-black text-emerald-700 whitespace-nowrap">${{
                                Number(item.transfer_amount
                                || 0).toFixed(2) }}</td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top whitespace-nowrap">{{ new
                                Date(item.created_at).toLocaleDateString() }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="10" class="text-center px-4 py-6 text-slate-500 font-semibold">
                                No records found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                <div class="text-xs text-slate-500 font-black">Notes</div>
                <div class="text-sm text-slate-700 font-bold mt-1">
                    Coins are generated during Live broadcasts, converted to revenue value, then split 50/50. Creator
                    share can be transferred to wallet.
                </div>
            </div>
        </div>
    </div>
</template>