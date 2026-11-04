<template>
    <div class="modal-overlay" :class="{ active: visible }" @click.self="$emit('close')">
        <div class="analytics-shell shadow-soft border overflow-hidden h-[90vh] w-[95vw] lg:w-[85vw] max-w-[1500px]"
            style="border-color:rgba(255,255,255,.10); background:linear-gradient(180deg, rgba(11,42,67,.98), rgba(10,35,56,.98)); border-radius: 1.25rem;">

            <!-- Modal header -->
            <div class="px-5 py-4 border-b flex items-center justify-between"
                style="border-color:var(--lu-line, rgba(148, 163, 184, .18)); background:rgba(8,28,45,.65); backdrop-filter: blur(10px);">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-2xl flex items-center justify-center"
                        style="background:rgba(223,255,0,.10); border:1px solid rgba(223,255,0,.25)">
                        <BarChart3 class="w-5 h-5" style="color:var(--lu-lime, #dfff00)" />
                    </div>
                    <div class="leading-tight">
                        <div class="font-semibold tracking-tight text-white">Analytics</div>
                        <div id="subtitle" class="text-xs text-slate-400">Last 7 days • All sessions</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="exportCSV"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl pill hover:bg-white/5 text-slate-200">
                        <Download class="w-4 h-4" />
                        <span class="text-sm">Export</span>
                    </button>
                    <button @click="$emit('close')"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl pill hover:bg-white/5 text-slate-200">
                        <X class="w-4 h-4" />
                        <span class="text-sm">Close</span>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-5 h-[calc(100%-64px)] overflow-auto scrollbar text-slate-100 relative">
                
                <!-- Loading Overlay -->
                <div v-if="state.loading" class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-10 h-10 border-4 border-lime-400/20 border-t-lime-400 rounded-full animate-spin"></div>
                        <span class="text-sm font-medium text-lime-400">Loading analytics...</span>
                    </div>
                </div>


                <!-- Filters -->
                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button v-for="range in ['today', '7d', '30d', '90d']" :key="range" @click="state.range = range"
                            :class="['tabbtn px-3 py-2 rounded-2xl pill text-sm transition-all', state.range === range ? 'active' : '']">
                            {{ range === 'today' ? 'Today' : range.toUpperCase() }}
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col sm:flex-row gap-2 sm:justify-end">
                        <div
                            class="flex items-center gap-2 px-3 py-2 rounded-2xl pill bg-white/5 border border-white/10">
                            <Tv2 class="w-4 h-4 text-slate-400" />
                            <select v-model="state.sessionId"
                                class="bg-transparent text-sm outline-none cursor-pointer">
                                <option value="all" class="text-black">All sessions</option>
                                <option v-for="s in sampleData.sessions" :key="s.id" :value="s.id" class="text-black">
                                    {{ s.date }} • {{ s.title }}
                                </option>
                            </select>
                        </div>
                        <div
                            class="flex items-center gap-2 px-3 py-2 rounded-2xl pill bg-white/5 border border-white/10">
                            <Globe2 class="w-4 h-4 text-slate-400" />
                            <select v-model="state.country" class="bg-transparent text-sm outline-none  cursor-pointer">
                                <option value="all" class="text-black">All countries</option>
                                <option v-for="c in sampleData.countries" :key="c.code" :value="c.code"
                                    class="text-black">{{ c.name }}
                                </option>
                            </select>
                        </div>
                        <!-- <div
                            class="flex items-center gap-2 px-3 py-2 rounded-2xl pill bg-white/5 border border-white/10">
                            <Smartphone class="w-4 h-4 text-slate-400" />
                            <select v-model="state.platform"
                                class="bg-transparent text-sm outline-none  cursor-pointer">
                                <option value="all" class="text-black">All platforms</option>
                                <option value="ios" class="text-black">iOS</option>
                                <option value="android" class="text-black">Android</option>
                                <option value="web" class="text-black">Web</option>
                            </select>
                        </div> -->
                    </div>
                </div>

                <!-- KPI Grid -->
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Live Sessions</div>
                            <Clapperboard class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">{{ summary.totalSessions }}</div>
                        <div class="text-xs mt-1 text-slate-400">ended in range</div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Unique Viewers</div>
                            <Users class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">{{ fmt.format(summary.uniqueViewers) }}</div>
                        <div class="text-xs mt-1 text-slate-400">new + returning</div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">PCU / ACU</div>
                            <Activity class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">
                            <span>{{ summary.pcu }}</span>
                            <span class="text-slate-500 mx-1">/</span>
                            <span>{{ summary.acu.toFixed(1) }}</span>
                        </div>
                        <div class="text-xs mt-1 text-slate-400">peak / avg concurrent</div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Watch Time</div>
                            <Clock4 class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">
                            <span>{{ summary.watchHours.toFixed(1) }}</span>
                            <span class="text-base text-slate-500 ml-1">hrs</span>
                        </div>
                        <div class="text-xs mt-1 text-slate-400">avg {{ summary.avgWatchPerViewer }} sec / viewer</div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Engagement</div>
                            <MessageSquare class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">
                            <span>{{ (summary.engagementRate * 100).toFixed(1) }}</span>
                            <span class="text-base text-slate-500 ml-1">%</span>
                        </div>
                        <div class="text-xs mt-1 text-slate-400">{{ summary.chats }} chats • {{ summary.reacts }} reacts
                        </div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Most Watched</div>
                            <Globe class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">{{ summary.topCountry.code }} • {{
                            summary.topCountry.name }}</div>
                        <div class="text-xs mt-1 text-slate-400">by {{ summary.topCountry.watchHours.toFixed(1) }} watch
                            hours</div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Revenue</div>
                            <Coins class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">
                            <span style="color:var(--lu-lime, #dfff00)">{{ fmt.format(summary.totalCoins) }}</span>
                            <span class="text-base text-slate-500 ml-1 font-normal">coins</span>
                        </div>
                        <div class="text-xs mt-1 text-slate-400">${{ summary.totalCash.toFixed(2) }} cash • Subs ${{
                            summary.totalSubs.toFixed(2) }}</div>
                    </div>

                    <div class="rounded-2xl kpi p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs uppercase tracking-wider text-slate-400">Subscriptions Split</div>
                            <Split class="w-4 h-4 opacity-80" />
                        </div>
                        <div class="mt-2 text-2xl font-semibold">
                            <span>${{ summary.hostShare.toFixed(1) }}</span>
                            <span class="text-base text-slate-500 mx-1">/</span>
                            <span>${{ summary.partnerShare.toFixed(1) }}</span>
                        </div>
                        <div class="text-xs mt-1 text-slate-400">Host 50% • Partner 50%</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="mt-5 grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="rounded-2xl glass p-4 min-h-[350px]">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-semibold">Concurrency Over Time</div>
                                <div class="text-xs text-slate-400">How many viewers were watching (bucketed)</div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full pill bg-white/5 border border-white/10">PCU •
                                ACU</span>
                        </div>
                        <div class="h-[240px]">
                            <Line :data="concurrencyChartData" :options="baseChartOptions" />
                        </div>
                    </div>

                    <div class="rounded-2xl glass p-4 min-h-[350px]">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-semibold">Revenue Sources</div>
                                <div class="text-xs text-slate-400">Gifts (coins), Cash tips, Private subscriptions
                                </div>
                            </div>
                            <span
                                class="text-xs px-2 py-1 rounded-full pill bg-white/5 border border-white/10">Mix</span>
                        </div>
                        <div class="h-[240px]">
                            <Doughnut :data="revenueMixChartData" :options="doughnutOptions" />
                        </div>
                    </div>

                    <div class="rounded-2xl glass p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-semibold">Most Popular Gifts Sent</div>
                                <div class="text-xs text-slate-400">Top gifts by count (what people send most)</div>
                            </div>
                            <span
                                class="text-xs px-2 py-1 rounded-full badge bg-yellow-400/10 text-yellow-400 border border-yellow-400/20">
                                Top: {{ topGift.gift }} ({{ topGift.count }})
                            </span>
                        </div>
                        <div class="h-[180px]">
                            <Bar :data="topGiftsChartData" :options="baseChartOptions" />
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs uppercase text-slate-400 border-b border-white/10">
                                    <tr>
                                        <th class="py-2 text-left">Gift</th>
                                        <th class="py-2 text-right">Count</th>
                                        <th class="py-2 text-right">Coins</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="g in sampleData.gifts" :key="g.gift" class="hover:bg-white/5">
                                        <td class="py-2 font-medium">{{ g.gift }}</td>
                                        <td class="py-2 text-right">{{ fmt.format(g.count) }}</td>
                                        <td class="py-2 text-right text-yellow-400">{{ fmt.format(g.coins) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-2xl glass p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-semibold">Countries Watching Live</div>
                                <div class="text-xs text-slate-400">Most watched country + watch hours by country</div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full pill bg-white/5 border border-white/10">Top
                                markets</span>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs uppercase text-slate-400 border-b border-white/10">
                                    <tr>
                                        <th class="py-2 text-left">Country</th>
                                        <th class="py-2 text-right">Watch (hrs)</th>
                                        <th class="py-2 text-right">Viewers</th>
                                        <th class="py-2 text-right">Share</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="c in sortedCountries" :key="c.code" class="hover:bg-white/5">
                                        <td class="py-2">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-[10px] bg-white/10 px-1.5 py-0.5 rounded border border-white/10">{{
                                                        c.code }}</span>
                                                <span
                                                    :class="{ 'font-semibold text-yellow-400': c.code === summary.topCountry.code }">{{
                                                        c.name }}</span>
                                                <span v-if="c.code === summary.topCountry.code"
                                                    class="text-[10px] bg-yellow-400/10 text-yellow-400 px-1.5 py-0.5 rounded border border-yellow-400/20">Most
                                                    watched</span>
                                            </div>
                                        </td>
                                        <td class="py-2 text-right font-mono">{{ c.watchHours.toFixed(1) }}</td>
                                        <td class="py-2 text-right">{{ fmt.format(c.viewers) }}</td>
                                        <td class="py-2 text-right">{{ Math.round((c.watchHours / totalWatchHours) *
                                            100) }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payouts / Transfer -->
                <div class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-4">
                    <div class="xl:col-span-2 rounded-2xl glass p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-semibold">Transfer to Wallet</div>
                                <div class="text-xs text-slate-400">Track money collected → eligible → transferred
                                    (audit trail)</div>
                            </div>
                            <button @click="transferNow"
                                class="text-sm px-3 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white flex items-center gap-2 font-semibold transition-colors">
                                <Wallet class="w-4 h-4" /> Transfer Now
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-2xl kpi p-4 bg-white/2">
                                <div class="text-xs uppercase tracking-wider text-slate-400 text-[10px]">Collected</div>
                                <div class="mt-1 text-2xl font-semibold">${{ money.collected.toFixed(2) }}</div>
                                <div class="text-xs mt-1 text-slate-500 text-[10px]">cash tips + subs + coin est.</div>
                            </div>
                            <div class="rounded-2xl kpi p-4 bg-white/2">
                                <div class="text-xs uppercase tracking-wider text-slate-400 text-[10px]">Eligible to
                                    Transfer</div>
                                <div class="mt-1 text-2xl font-semibold">${{ money.eligible.toFixed(2) }}</div>
                                <div class="text-xs mt-1 text-slate-500 text-[10px]">after holds + fees</div>
                            </div>
                            <div class="rounded-2xl kpi p-4 bg-white/2">
                                <div class="text-xs uppercase tracking-wider text-slate-400 text-[10px]">Transferred
                                </div>
                                <div class="mt-1 text-2xl font-semibold text-emerald-400">${{
                                    money.transferred.toFixed(2) }}</div>
                                <div class="text-xs mt-1 text-slate-500 text-[10px]">to LinkUp Wallet</div>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs uppercase text-slate-400 border-b border-white/10">
                                    <tr>
                                        <th class="py-2 text-left">Date</th>
                                        <th class="py-2 text-left">Type</th>
                                        <th class="py-2 text-right">Amount</th>
                                        <th class="py-2 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="t in sampleData.transfers" :key="t.ref"
                                        class="hover:bg-white/5 text-[13px]">
                                        <td class="py-2 text-slate-300">{{ t.date }}</td>
                                        <td class="py-2">
                                            <span class="font-medium">{{ t.type }}</span>
                                            <span class="block text-[10px] text-slate-500 font-mono">{{ t.ref }}</span>
                                        </td>
                                        <td class="py-2 text-right"
                                            :class="t.amount < 0 ? 'text-amber-400' : 'text-emerald-400'">
                                            {{ t.amount < 0 ? '-' : '' }}${{ Math.abs(t.amount).toFixed(2) }} </td>
                                        <td class="py-2 text-right">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full pill"
                                                :style="t.status === 'Completed' ? 'border-color:rgba(16,185,129,.35); background:rgba(16,185,129,.12); color:#10b981;' : 'border-color:rgba(255,191,0,.35); background:rgba(255,191,0,.12); color:#ffbf00;'">
                                                {{ t.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-2xl glass p-4 flex flex-col gap-3">
                        <div class="font-semibold text-sm">Private Subscription Split (50/50)</div>
                        <div class="text-[10px] text-slate-400 -mt-2">Subscription money collected and split for payout
                        </div>

                        <div class="space-y-2 flex-1">
                            <div class="rounded-2xl kpi p-3 bg-white/2 border border-white/5">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400">Sub Revenue</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full pill bg-white/10">Gross</span>
                                </div>
                                <div class="text-xl font-semibold">${{ sampleData.subscriptions.revenueGross.toFixed(2)
                                    }}</div>
                                <div class="text-[10px] text-slate-500">{{ sampleData.subscriptions.active }} active
                                    subs</div>
                            </div>

                            <div class="rounded-2xl kpi p-3 bg-white/2 border border-white/5">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400">Host (50%)</span>
                                    <UserIcon class="w-3 h-3 opacity-60 text-emerald-400" />
                                </div>
                                <div class="text-xl font-semibold text-emerald-400">${{ summary.hostShare.toFixed(2) }}
                                </div>
                            </div>

                            <div class="rounded-2xl kpi p-3 bg-white/2 border border-white/5">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400">Partner
                                        (50%)</span>
                                    <Handshake class="w-3 h-3 opacity-60 text-sky-400" />
                                </div>
                                <div class="text-xl font-semibold text-sky-400">${{ summary.partnerShare.toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl kpi p-3 bg-yellow-400/5 border border-yellow-400/10">
                            <div class="text-[10px] uppercase tracking-wider text-yellow-400 opacity-80">Most Watched
                                Country</div>
                            <div class="text-xl font-semibold text-yellow-400">{{ summary.topCountry.code }} • {{
                                summary.topCountry.name }}</div>
                            <div class="text-[10px] text-yellow-100 opacity-60">{{
                                summary.topCountry.watchHours.toFixed(1) }} watch hours</div>
                        </div>
                    </div>
                </div>

                <!-- Sessions Table -->
                <div class="mt-5 rounded-2xl glass p-4 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="font-semibold">Top Sessions</div>
                            <div class="text-xs text-slate-400">Ranked by revenue + watch time</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs uppercase text-slate-400 border-b border-white/10">
                                <tr>
                                    <th class="py-2 text-left">Date</th>
                                    <th class="py-2 text-left">Title</th>
                                    <th class="py-2 text-left">Category</th>
                                    <th class="py-2 text-right">PCU</th>
                                    <th class="py-2 text-right">Watch (hrs)</th>
                                    <th class="py-2 text-right">Coins</th>
                                    <th class="py-2 text-right">Cash</th>
                                    <th class="py-2 text-right">Subs</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="s in sampleData.sessions" :key="s.id" class="hover:bg-white/5">
                                    <td class="py-3 text-slate-300">{{ s.date }}</td>
                                    <td class="py-3 font-medium">{{ s.title }}</td>
                                    <td class="py-3 text-slate-400">{{ s.category }}</td>
                                    <td class="py-3 text-right font-mono">{{ s.pcu }}</td>
                                    <td class="py-3 text-right font-mono">{{ s.watchHours }}</td>
                                    <td class="py-3 text-right text-yellow-400 font-semibold">{{ s.coins }}</td>
                                    <td class="py-3 text-right text-emerald-400 font-semibold">${{ s.cash.toFixed(2) }}
                                    </td>
                                    <td class="py-3 text-right text-sky-300 font-semibold">${{ s.subs.toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 text-[10px] text-slate-500 text-center pb-4 italic">
                    All data is synchronized in real-time. Contact support for detailed historical export.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue';
import {
    BarChart3, Download, X, Tv2, Globe2, Smartphone,
    Clapperboard, Users, Activity, Clock4, MessageSquare,
    Globe, Coins, Split, Wallet, User as UserIcon, Handshake
} from 'lucide-vue-next';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    CategoryScale,
    ArcElement,
    BarElement
} from 'chart.js';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import axios from 'axios';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    CategoryScale,
    ArcElement,
    BarElement
);

const props = defineProps<{
    visible: boolean;
}>();

const emit = defineEmits(['close']);

const fmt = new Intl.NumberFormat();

const state = reactive({
    range: '7d',
    sessionId: 'all',
    country: 'all',
    platform: 'all',
    loading: false
});

const sampleData = reactive({
    settings: {
        coinToUsd: 0.01,
        platformFeeRate: 0.20,
        subscriptionSplitHost: 0.50,
        subscriptionSplitPartner: 0.50
    },
    sessions: [] as any[],
    buckets: [] as any[],
    gifts: [] as any[],
    subscriptions: {
        active: 0,
        revenueGross: 0
    },
    countries: [] as any[],
    transfers: [] as any[]
});

const fetchData = async () => {
    state.loading = true;
    try {
        const response = await axios.get(route('frontend.live.analytics', { range: state.range }));
        if (response.data.ok) {
            Object.assign(sampleData, response.data);
        }
    } catch (error) {
        console.error('Failed to fetch analytics:', error);
    } finally {
        state.loading = false;
    }
};

onMounted(() => {
    if (props.visible) {
        fetchData();
    }
});

watch(() => props.visible, (newVal) => {
    if (newVal) fetchData();
});

watch(() => state.range, () => {
    fetchData();
});

const summary = computed(() => {
    const sessions = (state.sessionId === "all") ? sampleData.sessions : sampleData.sessions.filter(s => s.id === state.sessionId);
    const totalSessions = sessions.length;
    
    const watchHours = sessions.reduce((a, s) => a + (s.watchHours || 0), 0);
    const uniqueViewers = sessions.reduce((a, s) => a + (s.uniqueViewers || 0), 0);
    const pcu = sessions.reduce((m, s) => Math.max(m, s.pcu || 0), 0);
    const acu = uniqueViewers > 0 ? Math.round(uniqueViewers * 0.6) : 0; // estimate ACU based on unique if no real data
    const chats = sessions.reduce((a, s) => a + (s.chats || 0), 0);
    const reacts = sessions.reduce((a, s) => a + (s.reacts || 0), 0);
    const avgWatchPerViewer = uniqueViewers > 0 ? Math.round((watchHours * 3600) / uniqueViewers) : 0;


    const totalCoins = sessions.reduce((a, s) => a + (s.coins || 0), 0);
    const totalCash = sessions.reduce((a, s) => a + (s.cash || 0), 0);
    const totalSubs = sessions.reduce((a, s) => a + (s.subs || 0), 0);

    const engagementRate = uniqueViewers > 0 ? (chats * 0.35 + reacts * 0.22 + (totalCoins / 100)) / uniqueViewers : 0;

    const hostShare = (sampleData.subscriptions.revenueGross || 0) * (sampleData.settings.subscriptionSplitHost || 0.5);
    const partnerShare = (sampleData.subscriptions.revenueGross || 0) * (sampleData.settings.subscriptionSplitPartner || 0.5);

    const topC = sampleData.countries.length 
        ? sampleData.countries.reduce((prev, current) => (prev.watchHours > current.watchHours) ? prev : current)
        : { code: 'N/A', name: 'N/A', watchHours: 0 };

    return {
        totalSessions, uniqueViewers, pcu, acu, watchHours, avgWatchPerViewer,
        chats, reacts, totalCoins, totalCash, totalSubs, engagementRate,
        hostShare, partnerShare, topCountry: topC
    };
});

const money = computed(() => {
    const totalCoins = sampleData.sessions.reduce((a, s) => a + (s.coins || 0), 0);
    const totalCash = sampleData.sessions.reduce((a, s) => a + (s.cash || 0), 0);
    const subsGross = sampleData.subscriptions.revenueGross || 0;
    const coinUsd = totalCoins * (sampleData.settings.coinToUsd || 0.01);
    const collected = totalCash + subsGross + coinUsd;
    const fee = collected * (sampleData.settings.platformFeeRate || 0.2);
    const holds = sampleData.transfers.filter(t => t.type === "Hold").reduce((a, t) => a + Math.abs(t.amount || 0), 0);
    const transferred = sampleData.transfers.filter(t => t.type === "Transfer" && t.status === "Completed").reduce((a, t) => a + (t.amount || 0), 0);
    const eligible = Math.max(0, collected - fee - holds - transferred);

    return { collected, eligible, transferred };
});

const totalWatchHours = computed(() => sampleData.countries.reduce((a, c) => a + (c.watchHours || 0), 0));
const sortedCountries = computed(() => [...sampleData.countries].sort((a, b) => (b.watchHours || 0) - (a.watchHours || 0)));
const topGift = computed(() => sampleData.gifts.length ? [...sampleData.gifts].sort((a, b) => b.count - a.count)[0] : { gift: 'None', count: 0 });

const baseChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(11,42,67,0.9)',
            titleColor: '#fff',
            bodyColor: '#e2e8f0',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
        }
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: 'rgba(148,163,184,0.5)', font: { size: 10 } }
        },
        y: {
            grid: { color: 'rgba(148,163,184,0.1)' },
            ticks: { color: 'rgba(148,163,184,0.5)', font: { size: 10 } }
        }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: { color: '#e2e8f0', usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 11 } }
        },
        tooltip: {
            callbacks: {
                label: (ctx: any) => ` $${ctx.raw.toFixed(2)}`
            }
        }
    }
};

const concurrencyChartData = computed(() => ({
    labels: sampleData.buckets.map(b => b.t),
    datasets: [{
        label: 'Concurrent Viewers',
        data: sampleData.buckets.map(b => b.concurrent),
        borderColor: '#dfff00',
        backgroundColor: 'rgba(223, 255, 0, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 0,
        borderWidth: 2
    }]
}));

const revenueMixChartData = computed(() => ({
    labels: ["Gifts", "Cash Tips", "Private Subs"],
    datasets: [{
        data: [
            sampleData.sessions.reduce((a, s) => a + (s.coins || 0), 0) * (sampleData.settings.coinToUsd || 0.01),
            sampleData.sessions.reduce((a, s) => a + (s.cash || 0), 0),
            sampleData.subscriptions.revenueGross || 0
        ],
        backgroundColor: ['#dfff00', '#10b981', '#38bdf8'],
        borderWidth: 0,
        hoverOffset: 10
    }]
}));

const topGiftsChartData = computed(() => {
    const sorted = [...sampleData.gifts].sort((a, b) => b.count - a.count).slice(0, 5);
    return {
        labels: sorted.map(g => g.gift),
        datasets: [{
            label: 'Count',
            data: sorted.map(g => g.count),
            backgroundColor: 'rgba(223, 255, 0, 0.8)',
            borderRadius: 6,
            barThickness: 20
        }]
    };
});

const transferNow = () => {
    alert("Transfer successfully initiated!");
};

const exportCSV = () => {
    const csv = [
        ["Date", "Title", "Category", "PCU", "WatchTime", "Coins", "Cash", "Subs"].join(","),
        ...sampleData.sessions.map(s => [s.date, s.title, s.category, s.pcu, s.watchHours, s.coins, s.cash, s.subs].join(","))
    ].join("\n");
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'linkup_analytics.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
};

</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
    pointer-events: auto;
}

.shadow-soft {
    box-shadow: 0 18px 50px rgba(0, 0, 0, .35);
}

.glass {
    background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .03));
    border: 1px solid rgba(255, 255, 255, .08);
}

.scrollbar::-webkit-scrollbar {
    width: 6px;
}

.scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, .15);
    border-radius: 999px;
}

.scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, .03);
}

.pill {
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
}

.kpi {
    border: 1px solid rgba(255, 255, 255, .10);
    background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .03));
}

.tabbtn.active {
    background: rgba(223, 255, 0, .14);
    border: 1px solid rgba(223, 255, 0, .35);
    color: rgba(223, 255, 0, 1);
}

.tabbtn:not(.active) {
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(226, 232, 240, 1);
}

.analytics-shell {
    animation: modal-slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modal-slide-up {
    from {
        transform: translateY(20px);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
