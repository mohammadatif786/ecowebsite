<script setup lang="ts">
import { computed } from 'vue';
import {
    Eye,
    MousePointerClick,
    Heart,
    Megaphone,
    TrendingUp,
    Newspaper,
    Mail,
    ShoppingBag,
    Utensils,
    Trophy,
    AlertCircle,
    Calendar,
    Rocket,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps<{
    ads: any[];
    adStats: any;
    revenueByChannel: any[];
    activityFeed: any[];
    needsAttention: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const emit = defineEmits(['view-changed', 'open-ad-form']);

const topPerformers = computed(() => {
    // Performers logic based on impressions and CTR from real data
    const performers = props.ads
        .map(a => ({
            ...a,
            icon: a.icon || (a.category === 'restaurant' ? '🍕' : a.category === 'club' ? '🎭' : '📢'),
            ctr: (a.impressions_count ? (a.clicks_count / a.impressions_count * 100) : 0).toFixed(1) + '%',
            channel: 'Swipe Ads',
            impressions: a.impressions_count || 0,
            isAd: true
        }))
        .sort((a, b) => b.impressions - a.impressions)
        .slice(0, 5);

    return performers;
});

const quickActions = [
    { icon: Megaphone, label: 'New Ad', viewId: 'adAllAdsCommand' },
    { icon: Rocket, label: 'Campaigns', viewId: 'adCampaignsCommand' },
    { icon: Newspaper, label: 'Reports', viewId: 'adReportsCommand' },
    { icon: TrendingUp, label: 'Analytics', viewId: 'adAnalyticsCommand' },
    { icon: Mail, label: 'Sponsors', viewId: 'adEmailSponsorCommand' },
    { icon: Newspaper, label: 'C360 News', viewId: 'adC360NewsCommand' },
];

const mappedNeedsAttention = computed(() => {
    return props.needsAttention.map(item => ({
        ...item,
        icon: item.type === 'red' ? AlertCircle : item.type === 'amber' ? Calendar : Rocket
    }));
});

const openNewAd = () => {
    emit('view-changed', 'adAllAdsCommand', { openAdForm: true });
};
</script>

<template>
    <div class="space-y-6">
        <!-- HERO -->
        <div class="db-hero relative overflow-hidden rounded-[2rem] border border-cyan-400/10 p-8 shadow-2xl">
            <div class="relative z-10 flex flex-col justify-between md:flex-row md:items-start lg:gap-16">
                <div class="flex-1">
                    <p class="mb-2 text-[10px] font-black uppercase tracking-[0.25em] text-cyan-400">Good morning, Admin 👋</p>
                    <h2 class="mb-2 text-3xl font-black tracking-tight text-white lg:text-4xl">
                        Welcome to <span class="text-cyan-400">LinkUp</span> <span class="text-[#CCFF00]">Vibes</span>
                    </h2>
                    <p class="text-sm text-slate-400">3 items need your attention · Last updated just now</p>

                    <div class="mt-6 flex flex-wrap gap-8 border-t border-white/10 pt-6">
                        <div class="db-hero-stat">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Impressions</p>
                            <h4 class="text-xl font-black text-white">{{ num(adStats.totalImpressions) }}</h4>
                            <p class="text-[11px] font-bold text-[#CCFF00]">▲ +24% MoM</p>
                        </div>
                        <div class="h-10 w-px bg-white/10 hidden sm:block"></div>
                        <div class="db-hero-stat">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Revenue</p>
                            <h4 class="text-xl font-black text-white">{{ fmt(adStats.totalRevenue) }}</h4>
                            <p class="text-[11px] font-bold text-[#CCFF00]">▲ +18% MoM</p>
                        </div>
                        <div class="h-10 w-px bg-white/10 hidden sm:block"></div>
                        <div class="db-hero-stat">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Active Advertisers</p>
                            <h4 class="text-xl font-black text-white">{{ adStats.activeAds }}</h4>
                            <p class="text-[11px] font-bold text-[#CCFF00]">▲ +3 new</p>
                        </div>
                        <div class="h-10 w-px bg-white/10 hidden sm:block"></div>
                        <div class="db-hero-stat">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Avg CTR</p>
                            <h4 class="text-xl font-black text-white">{{ adStats.avgCtr.toFixed(1) }}%</h4>
                            <p class="text-[11px] font-bold text-[#CCFF00]">▲ +0.8pts</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex gap-3 md:mt-0">
                    <button
                        @click="showSection('adReportsCommand')"
                        class="rounded-2xl border border-white/20 bg-white/5 px-4 py-2 text-sm font-bold text-white hover:bg-white/10 transition-colors"
                    >
                        📄 Report
                    </button>
                    <button
                        @click="openNewAd"
                        class="rounded-2xl bg-[#CCFF00] px-5 py-2 text-sm font-black text-slate-950 hover:bg-[#b8e600] transition-colors shadow-lg shadow-[#CCFF00]/20"
                    >
                        ＋ New Ad
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <div class="db-kpi db-kpi-cyan rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 text-2xl">👁️</div>
                <h4 class="text-2xl font-black text-slate-900 leading-none">{{ num(adStats.totalImpressions) }}</h4>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">Platform Impressions</p>
                <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-black text-green-600">
                    ▲ 24% vs last mo
                </div>
            </div>
            <div class="db-kpi db-kpi-lime rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 text-2xl">💵</div>
                <h4 class="text-2xl font-black text-slate-900 leading-none">{{ fmt(adStats.totalRevenue) }}</h4>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">Total Revenue</p>
                <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-black text-green-600">
                    ▲ 18% vs last mo
                </div>
            </div>
            <div class="db-kpi db-kpi-amber rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 text-2xl">📣</div>
                <h4 class="text-2xl font-black text-slate-900 leading-none">{{ adStats.activeAds }}</h4>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">Active Advertisers</p>
                <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-black text-green-600">
                    ▲ 3 new this week
                </div>
            </div>
            <div class="db-kpi db-kpi-purple rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 text-2xl">🎯</div>
                <h4 class="text-2xl font-black text-slate-900 leading-none">{{ adStats.avgCtr.toFixed(1) }}%</h4>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">Avg CTR All Channels</p>
                <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-black text-green-600">
                    ▲ +0.8 pts
                </div>
            </div>
            <div class="db-kpi db-kpi-news rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 text-2xl">📰</div>
                <h4 class="text-2xl font-black text-slate-900 leading-none">{{ fmt(5150) }}</h4>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">C360 News Revenue</p>
                <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[11px] font-black text-green-600">
                    ▲ New channel
                </div>
            </div>
        </div>

        <!-- CHANNEL TILES -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="flex flex-col rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50/50 to-cyan-100/50 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-cyan-600">📱 Swipe Ads</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">{{ num(adStats.totalImpressions) }} <span class="text-sm font-bold text-slate-500">impressions</span></h3>
                <p class="mt-1 text-xs font-bold text-slate-600">{{ num(adStats.totalClicks) }} swipe rights · {{ adStats.avgCtr.toFixed(1) }}% CTR</p>
                <div class="mt-6 flex items-center justify-between border-t border-cyan-200/50 pt-4">
                    <div>
                        <p class="text-lg font-black text-cyan-700">{{ fmt(4320) }}</p>
                        <p class="text-[10px] font-bold text-slate-400">Revenue this month</p>
                    </div>
                    <button @click="showSection('adAnalyticsCommand')" class="rounded-xl border border-cyan-200 bg-white px-3 py-1.5 text-xs font-black text-cyan-700 shadow-sm hover:bg-cyan-50">View →</button>
                </div>
            </div>
            <div class="flex flex-col rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50/50 to-teal-100/50 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-teal-600">✉️ Email Ads</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">92,400 <span class="text-sm font-bold text-slate-500">impressions</span></h3>
                <p class="mt-1 text-xs font-bold text-slate-600">28,760 opens · 10.7% open rate</p>
                <div class="mt-6 flex items-center justify-between border-t border-teal-200/50 pt-4">
                    <div>
                        <p class="text-lg font-black text-teal-700">{{ fmt(2447) }}</p>
                        <p class="text-[10px] font-bold text-slate-400">Revenue this month</p>
                    </div>
                    <button @click="showSection('adAnalyticsCommand')" class="rounded-xl border border-teal-200 bg-white px-3 py-1.5 text-xs font-black text-teal-700 shadow-sm hover:bg-teal-50">View →</button>
                </div>
            </div>
            <div class="flex flex-col rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50/50 to-orange-100/50 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">📰 C360 News</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">141,700 <span class="text-sm font-bold text-slate-500">impressions</span></h3>
                <p class="mt-1 text-xs font-bold text-slate-600">3 active sponsors · 6.3% avg CTR</p>
                <div class="mt-6 flex items-center justify-between border-t border-orange-200/50 pt-4">
                    <div>
                        <p class="text-lg font-black text-orange-700">{{ fmt(5150) }}</p>
                        <p class="text-[10px] font-bold text-slate-400">Revenue this month</p>
                    </div>
                    <button @click="showSection('adC360NewsCommand')" class="rounded-xl border border-orange-200 bg-white px-3 py-1.5 text-xs font-black text-orange-700 shadow-sm hover:bg-orange-50">View →</button>
                </div>
            </div>
        </div>

        <!-- NEWS SPOTLIGHT -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-black text-slate-900">📰 Caribbean 360 News — Active Sponsors</h4>
                <button @click="showSection('adC360NewsCommand')" class="text-xs font-black text-slate-400 hover:text-slate-600">Manage News Ads →</button>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm border-t-4 border-t-orange-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">🏦 Scotiabank</p>
                    <h4 class="mt-1 text-2xl font-black tracking-tight text-slate-900">48,200</h4>
                    <p class="text-[11px] font-bold text-slate-500">Category Takeover · Barbados · $2,500/mo</p>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full bg-orange-500" style="width: 76%"></div>
                    </div>
                    <p class="mt-2 text-[10px] font-bold text-slate-400">5.9% CTR · 2,890 clicks</p>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm border-t-4 border-t-orange-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">📡 Digicel</p>
                    <h4 class="mt-1 text-2xl font-black tracking-tight text-slate-900">32,100</h4>
                    <p class="text-[11px] font-bold text-slate-500">Newsletter Spot · All Caribbean · $850/mo</p>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full bg-orange-500" style="width: 53%"></div>
                    </div>
                    <p class="mt-2 text-[10px] font-bold text-slate-400">5.0% CTR · 1,604 clicks</p>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm border-t-4 border-t-orange-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">🏖️ Sandals Resorts</p>
                    <h4 class="mt-1 text-2xl font-black tracking-tight text-slate-900">61,400</h4>
                    <p class="text-[11px] font-bold text-slate-500">Homepage Feature · Tier 4 · $1,800/mo</p>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full bg-orange-500" style="width: 100%"></div>
                    </div>
                    <p class="mt-2 text-[10px] font-bold text-slate-400">8.0% CTR · 4,912 clicks</p>
                </div>
            </div>
        </div>

        <!-- BOTTOM GRID -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- LEFT COLUMN -->
            <div class="space-y-6">
                <!-- NEEDS ATTENTION -->
                <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-50 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-900">🔴 Needs Attention</h3>
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-black text-rose-600">3</span>
                    </div>
                    <div class="p-4 space-y-2">
                        <div v-for="item in mappedNeedsAttention" :key="item.title"
                            class="flex items-center gap-3 rounded-xl border p-3 transition-colors"
                            :class="{
                                'bg-rose-50/50 border-rose-100': item.type === 'red',
                                'bg-amber-50/50 border-amber-100': item.type === 'amber',
                                'bg-blue-50/50 border-blue-100': item.type === 'blue'
                            }"
                        >
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                                :class="{
                                    'bg-rose-100 text-rose-600': item.type === 'red',
                                    'bg-amber-100 text-amber-600': item.type === 'amber',
                                    'bg-blue-100 text-blue-600': item.type === 'blue'
                                }"
                            >
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-900">{{ item.title }}</p>
                                <p class="text-xs font-bold text-slate-500">{{ item.sub }}</p>
                            </div>
                            <button @click="showSection(item.viewId)" class="rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm hover:bg-slate-50 transition-colors">
                                {{ item.action }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- REVENUE BREAKDOWN -->
                <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-50 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-900">💰 Revenue by Channel</h3>
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-600">▲ 18% MoM</span>
                    </div>
                    <div class="p-6">
                        <div class="mb-6 flex items-baseline gap-2">
                            <h3 class="text-3xl font-black tracking-tight text-slate-950">{{ fmt(adStats.totalRevenue) }}</h3>
                            <p class="text-sm font-bold text-slate-400">total</p>
                        </div>
                        <div class="space-y-4">
                            <div v-for="channel in revenueByChannel" :key="channel.name" class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-slate-500">{{ channel.name }}</span>
                                    <span :style="{ color: channel.color }">{{ fmt(channel.value) }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-50">
                                    <div class="h-full rounded-full transition-all duration-1000" :style="{ width: channel.width, backgroundColor: channel.color }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="space-y-6">
                <!-- TOP PERFORMERS -->
                <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-50 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-900">🏆 Top Performers</h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">All channels · May 2026</span>
                    </div>
                    <div class="p-4 space-y-2">
                        <div v-for="(p, index) in topPerformers" :key="p.id" class="flex items-center gap-3 rounded-xl bg-slate-50/50 p-3 hover:bg-slate-50 transition-colors">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-xl shadow-sm">
                                {{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '🏅' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-900">{{ p.icon }} {{ p.name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-bold text-slate-400">{{ num(p.impressions) }} impr</span>
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-black"
                                        :class="p.channel === 'C360 News' ? 'bg-orange-50 text-orange-600' : 'bg-cyan-50 text-cyan-600'"
                                    >
                                        {{ p.channel }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right mr-2">
                                <p class="text-sm font-black text-cyan-600">{{ p.ctr }}</p>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">CTR</p>
                            </div>
                            <button @click="showSection(p.isAd === false ? 'adC360NewsCommand' : 'adReportsCommand')" class="rounded-lg bg-white border border-slate-200 p-1.5 text-slate-400 hover:text-slate-600 shadow-sm transition-colors">
                                <Newspaper class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- LIVE ACTIVITY -->
                <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-50 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-900">⚡ Live Activity</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500"></span>
                            <span class="text-[10px] font-black text-green-600 uppercase tracking-wider">Live</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-5">
                            <div v-for="act in activityFeed" :key="act.text" class="flex gap-4">
                                <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                    :class="{
                                        'bg-orange-500': act.type === 'orange',
                                        'bg-green-500': act.type === 'green',
                                        'bg-blue-500': act.type === 'blue',
                                        'bg-amber-500': act.type === 'amber'
                                    }"
                                ></div>
                                <div class="min-w-0">
                                    <p class="text-xs leading-relaxed text-slate-600" v-html="act.text"></p>
                                    <p class="mt-1 text-[10px] font-bold text-slate-400">{{ act.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden shadow-sm">
                    <div class="border-b border-slate-50 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-900">⚡ Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <button v-for="action in quickActions" :key="action.label"
                                @click="action.label === 'New Ad' ? openNewAd() : showSection(action.viewId)"
                                class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-100 bg-slate-50/50 py-4 transition-all hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-600 group"
                            >
                                <div class="rounded-xl bg-white p-2 shadow-sm transition-transform group-hover:scale-110">
                                    <component :is="action.icon" class="h-5 w-5" />
                                </div>
                                <span class="text-[11px] font-black">{{ action.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.db-hero {
    background: linear-gradient(135deg, #051020 0%, #0a1f3a 45%, #071525 100%);
}
.db-hero::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(40, 168, 255, 0.12) 0%, transparent 70%);
    pointer-events: none;
}
.db-hero::after {
    content: '';
    position: absolute;
    bottom: -100px;
    right: 180px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(217, 236, 16, 0.07) 0%, transparent 70%);
    pointer-events: none;
}

.db-kpi {
    position: relative;
    overflow: hidden;
}
.db-kpi::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 1rem 1rem 0 0;
}
.db-kpi-cyan::after { background: #29C9E8; }
.db-kpi-lime::after { background: #CCFF00; }
.db-kpi-amber::after { background: #f59e0b; }
.db-kpi-purple::after { background: #8b5cf6; }
.db-kpi-news::after { background: #f97316; }
</style>
