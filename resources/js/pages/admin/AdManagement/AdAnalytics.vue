<script setup lang="ts">
import { ref, computed } from 'vue';
import {
    MousePointerClick,
    Percent,
    Mail,
    Inbox,
    Users,
    DollarSign,
    TrendingUp
} from 'lucide-vue-next';

const props = defineProps<{
    ads?: any[];
    emailAds?: any[];
    adStats: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const activeTab = ref('swipe');

const switchTab = (tab: string) => {
    activeTab.value = tab;
};

const ads = computed(() => Array.isArray(props.ads) ? props.ads : []);
const emailAds = computed(() => Array.isArray(props.emailAds) ? props.emailAds : []);
const formatCurrency = (value: number) => props.fmt(value);
const formatCount = (value: number) => props.num(value);
const toNumber = (...values: any[]) => {
    for (const value of values) {
        if (value !== null && value !== undefined && value !== '') {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : 0;
        }
    }

    return 0;
};
const adStats = computed(() => ({
    totalImpressions: toNumber(props.adStats?.totalImpressions),
    totalClicks: toNumber(props.adStats?.totalClicks),
    totalLikes: toNumber(props.adStats?.totalLikes, props.adStats?.totalClicks),
    activeAds: toNumber(props.adStats?.activeAds),
    totalRevenue: toNumber(props.adStats?.totalRevenue),
    avgCtr: toNumber(props.adStats?.avgCtr),
    email: props.adStats?.email || {},
}));

// Data for charts/bars - using impressions_count and clicks_count from DB
const topImpressions = computed(() => {
    const sorted = [...ads.value].sort((a, b) => toNumber(b.impressions_count) - toNumber(a.impressions_count));
    const max = Math.max(...ads.value.map(a => toNumber(a.impressions_count)), 1);
    return sorted.slice(0, 10).map(a => ({
        name: a.name || 'Untitled Ad',
        val: formatCount(toNumber(a.impressions_count)),
        pct: (toNumber(a.impressions_count) / max * 100).toFixed(0) + '%'
    }));
});

const topCtr = computed(() => {
    const withImp = ads.value.filter(a => toNumber(a.impressions_count) > 0);
    const maxCtr = Math.max(...withImp.map(a => (toNumber(a.clicks_count) / toNumber(a.impressions_count) * 100)), 1);
    return [...withImp].sort((a, b) => (toNumber(b.clicks_count) / toNumber(b.impressions_count)) - (toNumber(a.clicks_count) / toNumber(a.impressions_count))).slice(0, 10).map(a => {
        const ctr = (toNumber(a.clicks_count) / toNumber(a.impressions_count) * 100);
        return {
            name: a.name || 'Untitled Ad',
            val: ctr.toFixed(1) + '%',
            pct: (ctr / maxCtr * 100).toFixed(0) + '%'
        };
    });
});

const categoryBreakdown = computed(() => {
    const cats: any = {};
    const labelMap: Record<string, string> = { restaurant: 'Restaurant', club: 'Club / Fête', general: 'General Ad' };

    ads.value.forEach(a => {
        const label = labelMap[a.category] || a.category || 'General Ad';
        cats[label] = (cats[label] || 0) + toNumber(a.impressions_count);
    });

    const colors = ['bg-[#29C9E8]', 'bg-[#3B82F6]', 'bg-[#8B5CF6]'];
    const total = Object.values(cats).reduce((a: any, b: any) => a + b, 0) || 1;
    return Object.entries(cats).map(([k, v]: [any, any], i) => ({
        name: k,
        val: formatCount(v),
        pct: (v / total * 100).toFixed(0) + '%',
        color: colors[i % colors.length]
    }));
});

// Email Data from DB
const emailStats = computed(() => {
    const email = adStats.value.email || {};
    const impressions = toNumber(email.impressions);
    const opens = Math.round(toNumber(email.opens));
    const clicks = toNumber(email.clicks);

    return {
        impressions,
        opens,
        clicks,
        openRate: impressions ? (opens / impressions * 100).toFixed(1) + '%' : '0.0%',
        clickRate: impressions ? (clicks / impressions * 100).toFixed(1) + '%' : '0.0%',
        revenue: toNumber(email.revenue),
        activeAds: toNumber(email.active_ads, emailAds.value.filter((ad: any) => ad.status === 'active').length),
        categories: Array.isArray(email.categories) ? email.categories : [],
        countries: Array.isArray(email.countries) ? email.countries : [],
    };
});

const emailSponsors = computed(() => {
    return emailAds.value.map((s, index) => {
        const impressions = toNumber(s.impressions_count, s.impressions);
        const clicks = toNumber(s.clicks_count, s.clicks);
        return {
            name: s.company_name || s.name || `Email Sponsor ${index + 1}`,
            category: s.category?.label || s.category_name || s.category || 'Email',
            impressions,
            opens: toNumber(s.opens),
            clicks,
            revenue: toNumber(s.revenue, s.cost),
            status: s.status || 'draft',
        };
    });
});

const maxEmailRevenue = computed(() => Math.max(...emailSponsors.value.map(s => s.revenue), 1));
const emailCategoryBars = computed(() => {
    const maxRate = Math.max(...emailStats.value.categories.map((category: any) => {
        const impressions = toNumber(category.impressions);
        return impressions ? toNumber(category.clicks) / impressions * 100 : 0;
    }), 1);

    return emailStats.value.categories.map((category: any) => {
        const impressions = toNumber(category.impressions);
        const rate = impressions ? toNumber(category.clicks) / impressions * 100 : 0;

        return {
            name: category.name || 'Uncategorized',
            val: `${rate.toFixed(1)}%`,
            pct: `${Math.max(0, rate / maxRate * 100).toFixed(0)}%`,
        };
    });
});
const emailCountryBars = computed(() => {
    const maxImpressions = Math.max(...emailStats.value.countries.map((country: any) => toNumber(country.impressions)), 1);

    return emailStats.value.countries.map((country: any) => {
        const impressions = toNumber(country.impressions);

        return {
            name: country.name || 'Unknown',
            val: formatCount(impressions),
            pct: `${Math.max(0, impressions / maxImpressions * 100).toFixed(0)}%`,
        };
    });
});

const combinedAds = computed(() => {
    const swipeRows = ads.value.map(a => ({
        name: a.name || 'Untitled Ad',
        type: 'Swipe',
        impressions: toNumber(a.impressions_count),
        engagement: toNumber(a.clicks_count),
        engLabel: 'Swipe Rights',
        ctr: (toNumber(a.impressions_count) ? (toNumber(a.clicks_count) / toNumber(a.impressions_count) * 100).toFixed(1) : '0') + '%',
        revenue: formatCurrency(toNumber(a.cost))
    }));
    const emailRows = emailSponsors.value.map(s => ({
        name: s.name,
        type: 'Email',
        impressions: s.impressions,
        engagement: s.clicks,
        engLabel: 'Clicks',
        ctr: (s.impressions ? (s.clicks / s.impressions * 100).toFixed(1) : '0') + '%',
        revenue: formatCurrency(s.revenue)
    }));
    return [...swipeRows, ...emailRows].sort((a, b) => b.impressions - a.impressions);
});

const swipeRevenueTotal = computed(() => adStats.value.totalRevenue);
const combinedRevenueTotal = computed(() => swipeRevenueTotal.value + emailStats.value.revenue);
const revenueSplitPct = computed(() => combinedRevenueTotal.value ? Math.round(swipeRevenueTotal.value / combinedRevenueTotal.value * 100) : 0);
const emailRevenueSplitPct = computed(() => combinedRevenueTotal.value ? 100 - revenueSplitPct.value : 0);

const swipeImpressionsTotal = computed(() => adStats.value.totalImpressions);
const combinedImpressionsTotal = computed(() => swipeImpressionsTotal.value + emailStats.value.impressions);
const impressionsSplitPct = computed(() => combinedImpressionsTotal.value ? Math.round(swipeImpressionsTotal.value / combinedImpressionsTotal.value * 100) : 0);
const emailImpressionsSplitPct = computed(() => combinedImpressionsTotal.value ? 100 - impressionsSplitPct.value : 0);

</script>

<template>
    <div class="space-y-6">
        <!-- System toggle -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-[13px] font-bold text-slate-500 mr-1">View:</span>
            <button
                @click="switchTab('swipe')"
                class="btn btn-sm"
                :class="activeTab === 'swipe' ? 'btn-primary' : 'btn-outline'"
            >
                📱 Swipe Ads
            </button>
            <button
                @click="switchTab('email')"
                class="btn btn-sm"
                :class="activeTab === 'email' ? 'btn-primary' : 'btn-outline'"
            >
                ✉️ Email Ads
            </button>
            <button
                @click="switchTab('combined')"
                class="btn btn-sm"
                :class="activeTab === 'combined' ? 'btn-primary' : 'btn-outline'"
            >
                📊 Combined
            </button>
        </div>

        <!-- ── Swipe Ads Analytics ── -->
        <div v-if="activeTab === 'swipe'" class="animate-in fade-in duration-500 space-y-6">
            <div class="stats-row mb-5">
                <div class="stat-card">
                    <div class="stat-icon green">👁️</div>
                    <div>
                        <div class="stat-num">{{ num(adStats.totalImpressions) }}</div>
                        <div class="stat-label">Total Impressions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">→</div>
                    <div>
                        <div class="stat-num">{{ num(adStats.totalClicks) }}</div>
                        <div class="stat-label">Swipe Rights</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber">❤️</div>
                    <div>
                        <div class="stat-num">{{ num(adStats.totalLikes) }}</div>
                        <div class="stat-label">Likes</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">%</div>
                    <div>
                        <div class="stat-num">{{ adStats.avgCtr.toFixed(1) }}%</div>
                        <div class="stat-label">Avg CTR</div>
                    </div>
                </div>
            </div>

            <div class="analytics-grid">
                <div class="card">
                    <div class="card-header"><span class="card-title">Top Ads by Impressions</span></div>
                    <div class="card-body">
                        <div class="mini-bar-wrap">
                            <div v-for="item in topImpressions" :key="item.name" class="mini-bar-row">
                                <div class="mini-bar-label">{{ item.name }}</div>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill" :style="{ width: item.pct }"></div>
                                </div>
                                <div class="mini-bar-val">{{ item.val }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Top Ads by Swipe-Right Rate</span></div>
                    <div class="card-body">
                        <div class="mini-bar-wrap">
                            <div v-for="item in topCtr" :key="item.name" class="mini-bar-row">
                                <div class="mini-bar-label">{{ item.name }}</div>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill bg-[#3B82F6]" :style="{ width: item.pct }"></div>
                                </div>
                                <div class="mini-bar-val">{{ item.val }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Impressions by Category</span></div>
                    <div class="card-body">
                        <div class="mini-bar-wrap">
                            <div v-for="item in categoryBreakdown" :key="item.name" class="mini-bar-row">
                                <div class="mini-bar-label">
                                    <span class="color-dot" :class="item.color"></span>
                                    {{ item.name }}
                                </div>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill" :class="item.color" :style="{ width: item.pct }"></div>
                                </div>
                                <div class="mini-bar-val">{{ item.val }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Swipe Ad Revenue</span></div>
                    <div class="card-body">
                        <div class="stat-num text-[36px] text-[#29C9E8]">{{ fmt(adStats.totalRevenue) }}</div>
                        <div class="text-muted mt-2">From {{ adStats.activeAds }} active campaigns</div>
                        <hr class="divider my-6" />
                        <div class="flex justify-between text-[14px] mb-2">
                            <span>Paid</span>
                            <strong class="text-[#29C9E8]">{{ fmt(adStats.totalRevenue) }}</strong>
                        </div>
                        <div class="flex justify-between text-[14px]">
                            <span>Pending</span>
                            <strong class="text-[#F59E0B]">{{ fmt(0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EMAIL ADS PANEL -->
        <div v-else-if="activeTab === 'email'" class="animate-in fade-in duration-500 space-y-6">
            <div class="stats-row mb-5">
                <div class="stat-card">
                    <div class="stat-icon green text-teal-600 bg-teal-50"><Mail /></div>
                    <div>
                        <div class="stat-num">{{ num(emailStats.impressions) }}</div>
                        <div class="stat-label">Email Impressions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue text-blue-600 bg-blue-50"><Inbox /></div>
                    <div>
                        <div class="stat-num">{{ num(emailStats.opens) }}</div>
                        <div class="stat-label">Tracked Opens</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber text-amber-600 bg-amber-50"><MousePointerClick /></div>
                    <div>
                        <div class="stat-num">{{ num(emailStats.clicks) }}</div>
                        <div class="stat-label">Ad Clicks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple text-purple-600 bg-purple-50"><Percent /></div>
                    <div>
                        <div class="stat-num">{{ emailStats.clickRate }}</div>
                        <div class="stat-label">Avg Click Rate</div>
                    </div>
                </div>
            </div>

            <div class="analytics-grid">
                <div class="card">
                    <div class="card-header"><span class="card-title">Email Ad Sponsors by Revenue</span></div>
                    <div class="card-body">
                        <div class="mini-bar-wrap">
                            <div v-for="s in emailSponsors" :key="s.name" class="mini-bar-row">
                                <div class="mini-bar-label">{{ s.name }}</div>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill bg-[#14B8A6]" :style="{ width: (s.revenue / maxEmailRevenue * 100) + '%' }"></div>
                                </div>
                                <div class="mini-bar-val">{{ fmt(s.revenue) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Click Rate by Email Category</span></div>
                    <div class="card-body">
                        <div class="mini-bar-wrap">
                            <div v-for="cat in emailCategoryBars" :key="cat.name" class="mini-bar-row">
                                <div class="mini-bar-label">{{ cat.name }}</div>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill bg-[#3B82F6]" :style="{ width: cat.pct }"></div>
                                </div>
                                <div class="mini-bar-val">{{ cat.val }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Email Sends by Country</span></div>
                    <div class="card-body">
                        <div class="mini-bar-wrap">
                            <div v-for="reg in emailCountryBars" :key="reg.name" class="mini-bar-row">
                                <div class="mini-bar-label">{{ reg.name }}</div>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill bg-[#8B5CF6]" :style="{ width: reg.pct }"></div>
                                </div>
                                <div class="mini-bar-val">{{ reg.val }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Email Sponsor Revenue</span></div>
                    <div class="card-body">
                        <div class="stat-num text-[36px] text-[#14B8A6]">{{ fmt(emailStats.revenue) }}</div>
                        <div class="text-muted mt-2">From {{ emailStats.activeAds }} active email campaigns</div>
                        <hr class="divider my-6" />
                        <div v-for="s in emailSponsors" :key="s.name" class="flex justify-between text-[14px] mb-2">
                            <span>{{ s.name }}</span>
                            <strong class="text-[#14B8A6]">{{ fmt(s.revenue) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COMBINED PANEL -->
        <div v-else-if="activeTab === 'combined'" class="animate-in fade-in duration-500 space-y-6">
            <div class="stats-row mb-5">
                <div class="stat-card">
                    <div class="stat-icon green bg-slate-900 text-white"><Users /></div>
                    <div>
                        <div class="stat-num">{{ num(combinedImpressionsTotal) }}</div>
                        <div class="stat-label">Platform Impressions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue bg-indigo-50 text-indigo-600"><TrendingUp /></div>
                    <div>
                        <div class="stat-num">{{ adStats.activeAds + emailStats.activeAds }}</div>
                        <div class="stat-label">Active Advertisers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber bg-emerald-50 text-emerald-600"><DollarSign /></div>
                    <div>
                        <div class="stat-num">{{ fmt(combinedRevenueTotal) }}</div>
                        <div class="stat-label">Total Ad Revenue</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple bg-blue-50 text-blue-600">📈</div>
                    <div>
                        <div class="stat-num">{{ emailStats.activeAds }}</div>
                        <div class="stat-label">Email Sponsors</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="card">
                    <div class="card-header"><span class="card-title">Revenue Split — Swipe vs Email</span></div>
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex-1 text-center">
                                <div class="text-[28px] font-black text-[#29C9E8]">{{ fmt(swipeRevenueTotal) }}</div>
                                <div class="text-[12px] text-slate-500 mt-1">📱 Swipe Ads</div>
                                <div class="text-[11px] text-slate-400">{{ revenueSplitPct }}% of total</div>
                            </div>
                            <div class="w-px h-[60px] bg-slate-200"></div>
                            <div class="flex-1 text-center">
                                <div class="text-[28px] font-black text-[#14B8A6]">{{ fmt(emailStats.revenue) }}</div>
                                <div class="text-[12px] text-slate-500 mt-1">✉️ Email Ads</div>
                                <div class="text-[11px] text-slate-400">{{ emailRevenueSplitPct }}% of total</div>
                            </div>
                        </div>
                        <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden flex">
                            <div class="h-full bg-[#29C9E8]" :style="{ width: revenueSplitPct + '%' }"></div>
                            <div class="h-full bg-[#14B8A6]" :style="{ width: emailRevenueSplitPct + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500 mt-2">
                            <span>📱 {{ revenueSplitPct }}% Swipe Ads</span>
                            <span>✉️ {{ emailRevenueSplitPct }}% Email Ads</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Impressions Split — Swipe vs Email</span></div>
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex-1 text-center">
                                <div class="text-[28px] font-black text-[#29C9E8]">{{ num(swipeImpressionsTotal) }}</div>
                                <div class="text-[12px] text-slate-500 mt-1">📱 Swipe</div>
                                <div class="text-[11px] text-slate-400">{{ impressionsSplitPct }}% of total</div>
                            </div>
                            <div class="w-px h-[60px] bg-slate-200"></div>
                            <div class="flex-1 text-center">
                                <div class="text-[28px] font-black text-[#14B8A6]">{{ num(emailStats.impressions) }}</div>
                                <div class="text-[12px] text-slate-500 mt-1">✉️ Email</div>
                                <div class="text-[11px] text-slate-400">{{ emailImpressionsSplitPct }}% of total</div>
                            </div>
                        </div>
                        <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden flex">
                            <div class="h-full bg-[#29C9E8]" :style="{ width: impressionsSplitPct + '%' }"></div>
                            <div class="h-full bg-[#14B8A6]" :style="{ width: emailImpressionsSplitPct + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500 mt-2">
                            <span>📱 {{ impressionsSplitPct }}% Swipe</span>
                            <span>✉️ {{ emailImpressionsSplitPct }}% Email</span>
                        </div>
                    </div>
                </div>

                <div class="card lg:col-span-2">
                    <div class="card-header"><span class="card-title">All Advertisers — Combined Performance</span></div>
                    <div class="card-body p-0">
                        <div class="scrollbar overflow-x-auto">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Advertiser</th>
                                        <th>Type</th>
                                        <th>Impressions</th>
                                        <th>Engagement</th>
                                        <th>CTR</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in combinedAds" :key="row.name" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-3 font-black text-slate-900">{{ row.name }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">{{ row.type }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-700">{{ (row.impressions/1000).toFixed(1) }}k</td>
                                        <td class="px-4 py-3 font-bold text-slate-700">
                                            {{ (row.engagement/1000).toFixed(1) }}k
                                            <span class="text-[11px] text-slate-400 uppercase ml-1 font-normal">{{ row.engLabel }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-black text-indigo-600">{{ row.ctr }}</td>
                                        <td class="px-4 py-3 font-black text-cyan-600">{{ row.revenue }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 18px;
    border-bottom: 1px solid #E5E7EB;
    background: #fff;
}

.card-title {
    color: #051020;
    font-size: 14px;
    font-weight: 900;
}

.card-body {
    padding: 18px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.stat-card {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.stat-icon.green { background: #F0FDF4; }
.stat-icon.blue { background: #EFF6FF; }
.stat-icon.amber { background: #FFFBEB; }
.stat-icon.purple { background: #F5F3FF; }

.stat-num {
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: #6B7280;
    margin-top: 2px;
}

.mini-bar-wrap {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.mini-bar-row {
    display: grid;
    grid-template-columns: minmax(120px, 1fr) 2fr auto;
    gap: 10px;
    align-items: center;
}

.mini-bar-label {
    color: #374151;
    font-size: 12px;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.mini-bar-track {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #EEF2F7;
}

.mini-bar-fill {
    height: 100%;
    border-radius: 999px;
    background: #29C9E8;
    transition: width 1s ease-out;
}

.mini-bar-val {
    color: #051020;
    font-size: 12px;
    font-weight: 900;
    min-width: 42px;
    text-align: right;
}

.color-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.report-table th {
    background: #F6FAF3;
    padding: 12px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6B7280;
    border-bottom: 2px solid #E5E7EB;
}

.report-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #E5E7EB;
}

.divider {
    border: none;
    border-top: 1px solid #E5E7EB;
}

@media (max-width: 1100px) {
    .stats-row,
    .analytics-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .mini-bar-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .mini-bar-val {
        text-align: left;
    }
}
</style>
