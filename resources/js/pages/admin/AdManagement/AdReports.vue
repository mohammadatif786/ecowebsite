<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import {
    Newspaper,
    Printer,
    ChevronLeft,
    Mail,
    Eye,
    MousePointerClick,
    Heart,
    BarChart3,
    Calendar,
    Globe,
    CheckCircle2,
    Sparkles,
    TrendingUp,
    Target,
    Clock,
    DollarSign,
    Zap,
    LayoutDashboard,
    Smartphone,
    X,
    Send,
    Video,
    Trophy
} from 'lucide-vue-next';

const props = defineProps<{
    ads?: any[];
    emailAds?: any[];
    adStats?: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const activeReportType = ref('swipe');
const selectedAdvId = ref<number | 'all' | null>(null);
const reportGenerated = ref(false);
const reportOutputRef = ref<HTMLElement | null>(null);

// Report Options
const reportPeriod = ref('30');
const includeTrend = ref(true);
const includeBench = ref(true);
const includeRoi = ref(true);
const includeReco = ref(true);

// Email Modal state
const showEmailModal = ref(false);
const emailForm = ref({
    to: '',
    cc: 'manager@linkupvibes.com',
    subject: 'Your LinkUp Vibes Ad Performance Report',
    note: '',
    attachPdf: true
});

const switchType = (type: string) => {
    activeReportType.value = type;
    selectedAdvId.value = null;
    reportGenerated.value = false;
};

const pickAdv = (id: number | 'all') => {
    selectedAdvId.value = id;
    reportGenerated.value = false;
};

const selectAllAdvertisers = async () => {
    selectedAdvId.value = 'all';
    await generateReport();
};

const generateReport = async () => {
    if (selectedAdvId.value !== null) {
        reportGenerated.value = true;
        await nextTick();
        reportOutputRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const resetReport = () => {
    reportGenerated.value = false;
    selectedAdvId.value = null;
};

const ads = computed(() => Array.isArray(props.ads) ? props.ads : []);
const emailAds = computed(() => Array.isArray(props.emailAds) ? props.emailAds : []);
const toNumber = (...values: any[]) => {
    for (const value of values) {
        if (value !== null && value !== undefined && value !== '') {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : 0;
        }
    }

    return 0;
};
const safePct = (part: number, total: number) => total ? (part / total * 100) : 0;
const safeRatio = (part: number, total: number) => total ? (part / total) : 0;
const formatDate = (value: any) => value || 'Not set';
const compactNum = (value: any) => {
    const n = toNumber(value);
    if (n >= 1000000) return `${(n / 1000000).toFixed(1)}M`;
    if (n >= 1000) return `${(n / 1000).toFixed(1)}k`;
    return props.num(n);
};

// Benchmarks (from HTML)
const BENCHMARKS = {
    ctr: 3.2,
    likeRate: 2.8,
    avgImpPerDay: 820
};

// Date/campaign-lifecycle helpers
const daysBetween = (d1: string | Date, d2: string | Date) => {
    const t1 = new Date(d1).getTime();
    const t2 = new Date(d2).getTime();
    if (isNaN(t1) || isNaN(t2)) return 0;
    return Math.round((t2 - t1) / 86400000);
};

const getDaysRunning = (a: any) => a.duration_days || Math.max(1, daysBetween(a.start_date, a.end_date));
const getDaysElapsed = (a: any) => Math.min(Math.max(daysBetween(a.start_date, new Date()), 0), getDaysRunning(a));
const getPctElapsed = (a: any) => {
    const total = getDaysRunning(a);
    return total ? Math.min(100, Math.round(getDaysElapsed(a) / total * 100)) : 0;
};
const getDaysLeft = (a: any) => Math.max(0, getDaysRunning(a) - getDaysElapsed(a));

const getPeakHourLabel = (hours: number[]) => {
    if (!hours || !hours.length) return '—';
    const max = Math.max(...hours);
    const idx = hours.indexOf(max);
    if (idx < 0) return '—';
    if (idx === 0) return '12:00 AM';
    if (idx < 12) return `${idx}:00 AM`;
    if (idx === 12) return '12:00 PM';
    return `${idx - 12}:00 PM`;
};

const selectedAd = computed(() => {
    if (selectedAdvId.value === 'all' || selectedAdvId.value === null) return null;
    const ad = ads.value.find(a => a.id === selectedAdvId.value);
    if (ad) {
        return {
            ...ad,
            impressions_count: toNumber(ad.impressions_count),
            clicks_count: toNumber(ad.clicks_count),
            swipeLeft: toNumber(ad.swipe_lefts_count),
            daily: Array.isArray(ad.daily) ? ad.daily.map((v: any) => toNumber(v)) : [],
            hours: Array.isArray(ad.hours) ? ad.hours.map((v: any) => toNumber(v)) : [],
            week1: ad.week1 || null,
            week2: ad.week2 || null,
            vidPlays: toNumber(ad.vidPlays),
            vidCompletions: toNumber(ad.vidCompletions),
            vidAvgWatch: toNumber(ad.vidAvgWatch),
            vidSoundOn: toNumber(ad.vidSoundOn),
            vidDuration: toNumber(ad.vidDuration)
        };
    }
    return null;
});

// Benchmark bars shown in the "Performance Benchmarks" section
const benchmarkRows = computed(() => {
    const a = selectedAd.value;
    if (!a) return [];
    return [
        { label: 'Click-Through Rate', val: getCtr(a), bench: BENCHMARKS.ctr, unit: '%', scale: 15 },
        { label: 'Swipe-Left Rate', val: getSwipeLeftRate(a), bench: BENCHMARKS.likeRate, unit: '%', scale: 100 },
        { label: 'Avg Impressions / Day', val: a.impressions_count / Math.max(getDaysElapsed(a), 1), bench: BENCHMARKS.avgImpPerDay, unit: '', scale: 2000 }
    ];
});

// Recommendations for the selected swipe ad
const swipeRecommendations = computed(() => {
    const a = selectedAd.value;
    if (!a) return [];
    const recos: { icon: string; title: string; body: string }[] = [];
    if (!a.impressions_count) {
        recos.push({ icon: '📅', title: 'Campaign starts soon', body: `Your ad will go live on ${a.start_date}. Ensure your website is ready to handle incoming visitors and that the landing page matches the ad's message.` });
        recos.push({ icon: '🖼️', title: 'Prepare your creative', body: "Make sure your ad image is high-contrast and readable at small sizes. The headline should be 6 words or fewer for maximum impact on a mobile tile." });
        return recos;
    }
    const ctrVal = getCtr(a);
    if (ctrVal < BENCHMARKS.ctr) {
        recos.push({ icon: '🖼️', title: 'Refresh your creative', body: `Your CTR of ${ctrVal.toFixed(1)}% is below the platform average of ${BENCHMARKS.ctr}%. Try a bolder image, a stronger headline, or a clearer call-to-action. Even a small creative change can lift CTR by 30–50%.` });
    } else {
        recos.push({ icon: '🚀', title: 'Consider upgrading your package', body: `Your CTR of ${ctrVal.toFixed(1)}% is performing well. Upgrading to more days or impressions would scale this success and drive more website visits for the same creative investment.` });
    }
    if (a.hours && a.hours.length) {
        recos.push({ icon: '⏰', title: 'Schedule around your peak hour', body: `Your audience engages most around ${getPeakHourLabel(a.hours)}. If you run future short-burst campaigns or events, launching them in this window will maximise initial momentum.` });
    }
    const daysLeft = getDaysLeft(a);
    if (a.status != 1 || daysLeft <= 7) {
        recos.push({ icon: '🔄', title: 'Renew your campaign', body: `${a.status != 1 ? 'Your campaign has ended.' : daysLeft + ' days remain.'} Your ad has built brand recognition among LinkUp users. Renewing now maintains that momentum — audiences who saw the ad previously are more likely to engage on re-exposure.` });
    } else {
        recos.push({ icon: '📍', title: 'Add a location-specific offer', body: 'Ads that reference a local deal ("Show this ad for 10% off") convert at 2× the rate of generic ads. Consider updating your headline mid-campaign with a time-limited offer.' });
    }
    return recos;
});

// Recommendations for the selected email sponsor
const emailRecommendations = computed(() => {
    const s = selectedEmailSponsor.value;
    if (!s) return [];
    return [
        {
            icon: '🌍',
            title: s.regions === 'All Regions' ? 'Monitor Regional Reach' : 'Review Regional Coverage',
            body: s.regions === 'All Regions'
                ? 'This sponsor can serve across all regions. Review country-level impression totals as new sends are tracked.'
                : 'This sponsor is region-scoped. Compare future country-level impressions before expanding coverage.'
        },
        {
            icon: '📅',
            title: 'Renew Before Campaign Ends',
            body: `Your campaign runs through ${s.end}. Renewing 30 days early ensures no impression gap and maintains your P${s.priority} placement in the ad queue.`
        },
        {
            icon: '🔗',
            title: 'Add a Swipe Ad to Complete Coverage',
            body: 'Email ads reach members in their inbox. Adding a Swipe Ad campaign reaches the same users in-app — doubling touchpoints and reinforcing brand recall across both platforms.'
        }
    ];
});

// Summary Stats for 'All Advertisers'
const platformStats = computed(() => {
    const list = ads.value;
    const totalImp = list.reduce((s, a) => s + toNumber(a.impressions_count), 0);
    const totalClk = list.reduce((s, a) => s + toNumber(a.clicks_count), 0);
    const totalSwipeLeft = list.reduce((s, a) => s + toNumber(a.swipe_lefts_count), 0);
    const totalCost = list.reduce((s, a) => s + toNumber(a.cost), 0);
    const avgCtr = totalImp ? (totalClk / totalImp * 100) : 0;

    return {
        totalImp,
        totalClk,
        totalLike: totalSwipeLeft,
        totalCost,
        avgCtr,
        activeCount: list.filter(a => a.status == 1).length
    };
});

const emailSponsors = computed(() => emailAds.value.map((s: any) => {
    const countries = Array.isArray(s.countries) ? s.countries : [];
    return {
        id: s.id,
        name: s.company_name || s.name || 'Email Sponsor',
        cat: s.category?.label || s.category_name || s.category || 'Email',
        revenue: toNumber(s.revenue, s.cost),
        status: s.status || 'draft',
        impressions: toNumber(s.impressions_count, s.impressions),
        opens: toNumber(s.opens),
        clicks: toNumber(s.clicks_count, s.clicks),
        start: formatDate(s.start_date),
        end: formatDate(s.end_date),
        contact: s.email || s.contact || '',
        priority: toNumber(s.priority),
        regions: countries.length ? countries.map((country: any) => country.name || country.country || country.code).filter(Boolean).join(', ') : 'All Regions'
    };
}));

const selectedEmailSponsor = computed(() => {
    if (activeReportType.value !== 'email' || selectedAdvId.value === null) return null;
    return emailSponsors.value.find(s => s.id === selectedAdvId.value);
});

const getStatusBadgeClass = (status: any) => {
    // Handle numeric status (1 = active, 0 = disabled) or string status
    let statusStr = '';
    if (typeof status === 'number') {
        statusStr = status === 1 ? 'active' : 'disabled';
    } else {
        statusStr = String(status || '');
    }

    const map: any = {
        active: 'bg-green-50 text-green-700',
        scheduled: 'bg-blue-50 text-blue-700',
        expired: 'bg-rose-50 text-rose-700',
        disabled: 'bg-slate-50 text-slate-700'
    };
    return map[statusStr.toLowerCase()] || 'bg-slate-50 text-slate-700';
};

// Calculation helpers
const getCtr = (a: any) => safePct(toNumber(a.clicks_count), toNumber(a.impressions_count));
const getLikeRate = (a: any) => getSwipeLeftRate(a);
const getSwipeLeftRate = (a: any) => safePct(toNumber(a.swipeLeft, a.swipe_lefts_count), toNumber(a.impressions_count));
const getCpc = (a: any) => safeRatio(toNumber(a.cost), toNumber(a.clicks_count));
const getCpm = (a: any) => toNumber(a.impressions_count) ? ((toNumber(a.cost) / toNumber(a.impressions_count)) * 1000) : 0;
const getEmailClickRate = (s: any) => safePct(toNumber(s.clicks), toNumber(s.impressions));
const getEmailOpenRate = (s: any) => safePct(toNumber(s.opens), toNumber(s.impressions));
const getEmailCpc = (s: any) => safeRatio(toNumber(s.revenue), toNumber(s.clicks));
const getWeekChange = (current: number, previous: number) => previous ? ((current - previous) / previous * 100) : 0;
const hasWeekData = computed(() => Boolean(selectedAd.value?.week1 && selectedAd.value?.week2));

const getVerdict = (a: any) => {
    const ctrVal = getCtr(a);
    if (!a.impressions_count) return {
        text: `<strong>${a.name}'s</strong> campaign is scheduled. No performance data is available yet.`,
        color: 'text-blue-600',
        bg: 'bg-blue-50/50',
        border: 'border-blue-100'
    };
    if (ctrVal >= BENCHMARKS.ctr * 1.5) return {
        text: `<strong>${a.name}</strong> is <strong>significantly outperforming</strong> the LinkUp platform average. Their creative is resonating strongly with the audience.`,
        color: 'text-indigo-600',
        bg: 'bg-indigo-50/50',
        border: 'border-indigo-100'
    };
    if (ctrVal >= BENCHMARKS.ctr) return {
        text: `<strong>${a.name}</strong> is performing above the platform average. Their ad is connecting well with LinkUp users.`,
        color: 'text-green-600',
        bg: 'bg-green-50/50',
        border: 'border-green-100'
    };
    return {
        text: `<strong>${a.name}</strong> click-through rate is currently below the platform average. Consider refreshing the creative or headline.`,
        color: 'text-amber-600',
        bg: 'bg-amber-50/50',
        border: 'border-amber-100'
    };
};

const openEmailAdvertiser = (ad: any) => {
    emailForm.value.to = ad.email || ad.contact || '';
    emailForm.value.note = `Hi ${ad.name},\n\nPlease find your latest LinkUp Vibes ad performance report attached. Your campaign is performing well — we'd love to discuss a renewal or expansion.`;
    showEmailModal.value = true;
};

const sendEmail = () => {
    alert(`✅ Report sent to ${emailForm.value.to}`);
    showEmailModal.value = false;
};

const printReport = () => {
    window.print();
};

// Category-based fallback icons for the advertiser picker (matches AdList.vue's convention)
const getSwipeAdIcon = (ad: any) => {
    if (ad.icon) return ad.icon;
    if (ad.category === 'restaurant') return '🍕';
    if (ad.category === 'club') return '🎭';
    return '📢';
};

const EMAIL_CATEGORY_ICONS: Record<string, string> = {
    Events: '🎉',
    Money: '💰',
    Birthday: '🎂'
};
const getEmailSponsorIcon = (s: any) => EMAIL_CATEGORY_ICONS[s.cat] || '✉️';
</script>

<template>
    <div class="space-y-6">
        <!-- HEADER TOGGLE -->
        <div class="flex flex-wrap items-center gap-2 print:hidden">
            <span class="text-xs font-black uppercase tracking-widest text-slate-400 mr-2">Report Type:</span>
            <button @click="switchType('swipe')" class="rounded-lg px-5 py-2 text-xs font-black transition-all flex items-center gap-2" :class="activeReportType === 'swipe' ? 'bg-[#06b6d4] text-white shadow-lg' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'">
                <Smartphone class="w-4 h-4" /> Swipe Ad Reports
            </button>
            <button @click="switchType('email')" class="rounded-lg px-5 py-2 text-xs font-black transition-all flex items-center gap-2" :class="activeReportType === 'email' ? 'bg-[#06b6d4] text-white shadow-lg' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'">
                <Mail class="w-4 h-4" /> Email Sponsor Reports
            </button>
        </div>

        <!-- CHOOSER PANEL -->
        <div v-show="activeReportType === 'swipe' || !reportGenerated" class="card report-builder-card overflow-hidden animate-in fade-in duration-300">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-black text-slate-900">
                        {{ activeReportType === 'swipe' ? 'Select Swipe Ad Advertiser' : 'Select Email Sponsor to Report On' }}
                    </h4>
                    <p v-if="activeReportType === 'email'" class="text-xs text-slate-400 font-bold mt-1">Select an email sponsor to generate a detailed report showing tracked email impressions, ad clicks, click-through rate, targeting, and revenue.</p>
                    <p v-else class="text-xs text-slate-400 font-bold mt-1">Choose one company to generate a detailed individual performance report, or view the combined summary for all advertisers.</p>
                </div>
                <button v-if="activeReportType === 'swipe'" @click="selectAllAdvertisers" class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-2 text-xs font-black text-slate-600 hover:bg-slate-100 transition-colors flex items-center gap-2" :class="{'ring-2 ring-[#06b6d4]': selectedAdvId === 'all'}">
                    <LayoutDashboard class="w-4 h-4" /> All Advertisers Summary
                </button>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- SWIPE ADS CHOOSER -->
                    <template v-if="activeReportType === 'swipe'">
                        <div v-for="ad in ads" :key="ad.id"
                            @click="pickAdv(ad.id)"
                            class="adv-card rounded-2xl border-2 p-6 cursor-pointer transition-all text-center space-y-2"
                            :class="selectedAdvId === ad.id ? 'selected border-[#06b6d4] bg-cyan-50/30' : 'border-slate-50 bg-white hover:border-slate-200'"
                        >
                            <div class="text-4xl mb-2">
                                <img v-if="ad.image" :src="ad.image_url" class="w-12 h-12 rounded-xl mx-auto object-cover border-2 border-white shadow-sm" />
                                <span v-else>{{ getSwipeAdIcon(ad) }}</span>
                            </div>
                            <h5 class="font-black text-slate-900">{{ ad.name }}</h5>
                            <p class="text-xs font-bold text-slate-400 tracking-tight">{{ ad.category }} · {{ ad.city }}</p>
                            <div class="pt-2">
                                <span class="rounded-full px-3 py-1.5 text-[10px] font-black uppercase shadow-sm" :class="getStatusBadgeClass(ad.status)">{{ ad.status == 1 ? 'Active' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </template>
                    <!-- EMAIL SPONSORS CHOOSER -->
                    <template v-else>
                        <div v-for="s in emailSponsors" :key="s.id"
                            @click="pickAdv(s.id)"
                            class="adv-card rounded-2xl border-2 p-6 cursor-pointer transition-all text-center space-y-2"
                            :class="selectedAdvId === s.id ? 'selected border-[#06b6d4] bg-green-50/30' : 'border-slate-50 bg-white hover:border-slate-200'"
                        >
                            <div class="text-4xl mb-2">{{ getEmailSponsorIcon(s) }}</div>
                            <h5 class="font-black text-slate-900">{{ s.name }}</h5>
                            <p class="text-xs font-bold text-slate-400 tracking-tight">{{ s.cat }} · {{ fmt(s.revenue) }}/mo</p>
                            <div class="pt-2">
                                <span class="rounded-full px-3 py-1.5 text-[10px] font-black uppercase shadow-sm" :class="getStatusBadgeClass(s.status)">{{ s.status }}</span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- REPORT OPTIONS -->
                <div class="mt-8 pt-8 border-t border-slate-100 flex flex-wrap items-end justify-between gap-8">
                    <div class="flex items-end gap-12">
                        <div class="space-y-2 min-w-[200px]">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Report Period</label>
                            <select v-model="reportPeriod" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 focus:ring-[#06b6d4] focus:border-[#06b6d4]">
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="60">Last 60 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="0">Full campaign</option>
                            </select>
                        </div>
                        <div v-if="activeReportType === 'swipe' && selectedAdvId !== 'all'" class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Include in Report</label>
                            <div class="flex flex-col gap-2 pt-1">
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                    <input type="checkbox" v-model="includeTrend" class="rounded border-slate-300 text-[#06b6d4] focus:ring-[#06b6d4]" /> Daily Trend
                                </label>
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                    <input type="checkbox" v-model="includeBench" class="rounded border-slate-300 text-[#06b6d4] focus:ring-[#06b6d4]" /> Benchmarks
                                </label>
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                    <input type="checkbox" v-model="includeRoi" class="rounded border-slate-300 text-[#06b6d4] focus:ring-[#06b6d4]" /> ROI Analysis
                                </label>
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                    <input type="checkbox" v-model="includeReco" class="rounded border-slate-300 text-[#06b6d4] focus:ring-[#06b6d4]" /> Recommendations
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="printReport" class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-xs font-black text-slate-600 flex items-center gap-2 hover:bg-slate-50 transition-all shadow-sm">
                            <Printer class="w-4 h-4" /> Print / Export PDF
                        </button>
                        <button @click="generateReport" :disabled="selectedAdvId === null" class="rounded-xl bg-[#06b6d4] px-8 py-3 text-xs font-black text-white disabled:opacity-30 disabled:cursor-not-allowed hover:bg-[#0891b2] transition-all shadow-lg shadow-cyan-500/20 flex items-center gap-2">
                            {{ activeReportType === 'swipe' ? 'Generate Report →' : 'Generate Email Report →' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORT VIEW -->
        <div v-if="reportGenerated" ref="reportOutputRef" class="space-y-6 animate-in slide-in-from-bottom-4 duration-500">
            <div class="flex items-center justify-between px-2 print:hidden">
                <button @click="resetReport" class="flex items-center gap-2 text-xs font-black text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                    <ChevronLeft class="w-4 h-4" /> New Report
                </button>
                <div class="flex gap-2">
                    <button v-if="selectedAdvId !== 'all'" @click="openEmailAdvertiser(selectedAd || selectedEmailSponsor)" class="rounded-2xl bg-white border border-slate-200 px-4 py-2 text-xs font-black text-slate-600 flex items-center gap-2 hover:bg-slate-50 shadow-sm transition-all">
                        <Mail class="w-4 h-4" /> Email Advertiser
                    </button>
                    <button @click="printReport" class="rounded-2xl bg-slate-950 px-5 py-2 text-xs font-black text-white flex items-center gap-2 hover:bg-slate-800 shadow-lg shadow-slate-950/20 transition-all">
                        <Printer class="w-4 h-4" /> Print / Save PDF
                    </button>
                </div>
            </div>

            <!-- REPORT PRINTABLE CONTAINER -->
            <div class="card report-preview rounded-[2.5rem] bg-white shadow-2xl border border-slate-100 overflow-hidden" id="report-printable">

                <!-- ── ALL ADVERTISERS SUMMARY ── -->
                <template v-if="selectedAdvId === 'all'">
                    <!-- Header Band -->
                    <div class="report-header-band p-10 text-white flex flex-col md:flex-row justify-between items-start gap-8 bg-slate-950">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center font-black text-2xl">L</div>
                                <div>
                                    <h2 class="text-2xl font-black tracking-tighter">LinkU</h2>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">Combined Performance Summary</p>
                                </div>
                            </div>
                            <div class="pt-4">
                                <h3 class="text-4xl font-black">📊 Platform Summary</h3>
                                <p class="text-slate-400 font-bold mt-1">Consolidated performance across all active advertisers.</p>
                            </div>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="text-xs font-bold opacity-40">Generated: <strong>{{ new Date().toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}) }}</strong></p>
                            <p class="text-xs font-bold opacity-40 uppercase tracking-widest">Confidential Platform Report</p>
                            <div class="pt-4 flex flex-col items-end">
                                <span class="rounded-full bg-indigo-500 px-3 py-1 text-[10px] font-black uppercase text-white">{{ platformStats.activeCount }} Active Campaigns</span>
                            </div>
                        </div>
                    </div>

                    <div class="report-body p-10 space-y-12">
                        <!-- KPI GRID -->
                        <div class="report-kpi-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-indigo-500">
                                <div class="text-lg opacity-50">👁️</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(platformStats.totalImp) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Impressions</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-blue-500">
                                <div class="text-lg opacity-50">→</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(platformStats.totalClk) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Swipe Rights</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-purple-500">
                                <div class="text-lg opacity-50">❤️</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(platformStats.totalLike) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Swipe Lefts</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-amber-500">
                                <div class="text-lg opacity-50">📊</div>
                                <h5 class="text-xl font-black text-amber-600">{{ platformStats.avgCtr.toFixed(2) }}%</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Avg Platform CTR</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-green-500">
                                <div class="text-lg opacity-50">💵</div>
                                <h5 class="text-xl font-black text-green-600">{{ fmt(platformStats.totalCost) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Revenue</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-slate-500">
                                <div class="text-lg opacity-50">📢</div>
                                <h5 class="text-xl font-black text-slate-900">{{ platformStats.activeCount }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Active Ads</p>
                            </div>
                        </div>

                        <!-- BREAKDOWN TABLE -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Individual Advertiser Breakdown
                            </h4>
                            <div class="rounded-[2rem] border border-slate-50 overflow-hidden shadow-sm overflow-x-auto scrollbar">
                                <table class="w-full text-left text-xs min-w-[800px]">
                                    <thead class="bg-slate-50 font-black text-slate-400 uppercase tracking-widest">
                                        <tr>
                                            <th class="px-6 py-4">Advertiser</th>
                                            <th class="px-6 py-4 text-center">Impressions</th>
                                            <th class="px-6 py-4 text-center">Engagement</th>
                                            <th class="px-6 py-4 text-center">Swipe Lefts</th>
                                            <th class="px-6 py-4 text-center">CTR</th>
                                            <th class="px-6 py-4 text-center">CPC</th>
                                            <th class="px-6 py-4 text-center">Spend</th>
                                            <th class="px-6 py-4 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="ad in ads" :key="ad.id" class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img v-if="ad.image" :src="ad.image_url" class="w-8 h-8 rounded-lg object-cover" />
                                                    <div v-else class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-lg">📢</div>
                                                    <div>
                                                        <p class="font-black text-slate-900 truncate max-w-[150px]">{{ ad.name }}</p>
                                                        <p class="text-[10px] font-bold text-slate-400">{{ ad.category }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ num(ad.impressions_count || 0) }}</td>
                                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ num(ad.clicks_count || 0) }} <span class="text-[10px] opacity-40">swipes</span></td>
                                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ num(ad.swipe_lefts_count || 0) }}</td>
                                            <td class="px-6 py-4 text-center font-black" :class="getCtr(ad) >= BENCHMARKS.ctr ? 'text-green-600' : 'text-slate-900'">{{ getCtr(ad).toFixed(1) }}%</td>
                                            <td class="px-6 py-4 text-center font-bold text-slate-500">${{ getCpc(ad).toFixed(2) }}</td>
                                            <td class="px-6 py-4 text-center font-black text-slate-900">{{ fmt(parseFloat(ad.cost || 0)) }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase" :class="getStatusBadgeClass(ad.status)">{{ ad.status == 1 ? 'Active' : 'Disabled' }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ── INDIVIDUAL SWIPE REPORT ── -->
                <template v-else-if="selectedAd">
                    <!-- Header Band -->
                    <div class="report-header-band p-10 text-white flex flex-col md:flex-row justify-between items-start gap-8 bg-slate-950">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center font-black text-2xl">L</div>
                                <div>
                                    <h2 class="text-2xl font-black tracking-tighter">LinkU</h2>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">Ad Performance Report</p>
                                </div>
                            </div>
                            <div class="pt-4">
                                <h3 class="text-4xl font-black">
                                    <template v-if="selectedAd.image">
                                        <img :src="selectedAd.image_url" class="inline-block w-12 h-12 rounded-xl object-cover border-2 border-white/20 align-middle mr-2" />
                                    </template>
                                    <span v-else class="mr-2">📢</span>
                                    {{ selectedAd.name }}
                                </h3>
                                <div class="mt-3 flex gap-2">
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest border border-white/10">📅 {{ selectedAd.start_date }} → {{ selectedAd.end_date }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="text-xs font-bold opacity-40">Generated: <strong>{{ new Date().toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}) }}</strong></p>
                            <p class="text-xs font-bold opacity-40 uppercase tracking-widest">Confidential Report</p>
                            <div class="pt-4 flex flex-col items-end gap-2">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase" :class="getStatusBadgeClass(selectedAd.status)">{{ selectedAd.status == 1 ? 'Active' : 'Disabled' }}</span>
                                <p class="text-xs font-bold text-white/60">{{ selectedAd.email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="report-body p-10 space-y-12">
                        <!-- Executive Summary -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Executive Summary
                            </h4>
                            <div class="rounded-3xl border p-8 transition-all" :class="[getVerdict(selectedAd).bg, getVerdict(selectedAd).border]">
                                <p class="text-lg leading-relaxed font-medium" :class="getVerdict(selectedAd).color" v-html="getVerdict(selectedAd).text"></p>
                            </div>
                        </div>

                        <!-- KPI SCORECARD (6 items) -->
                        <div class="report-kpi-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="report-kpi p-5 rounded-3xl border border-slate-100 bg-white shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-cyan-500">
                                <div class="text-lg">👁️</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(selectedAd.impressions_count) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Impressions</p>
                                <span class="text-[9px] font-black mt-1" :class="(selectedAd.impressions_count / Math.max(getDaysElapsed(selectedAd), 1)) >= BENCHMARKS.avgImpPerDay ? 'text-green-600' : 'text-amber-600'">
                                    {{ (selectedAd.impressions_count / Math.max(getDaysElapsed(selectedAd), 1)) >= BENCHMARKS.avgImpPerDay ? '▲' : '▼' }} vs {{ num(BENCHMARKS.avgImpPerDay) }}/day avg
                                </span>
                            </div>
                            <div class="report-kpi p-5 rounded-3xl border border-slate-100 bg-white shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-blue-500">
                                <div class="text-lg">→</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(selectedAd.clicks_count) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Swipe Rights</p>
                                <span class="text-[9px] font-black text-slate-400 mt-1">{{ getCtr(selectedAd).toFixed(1) }}% of all views</span>
                            </div>
                            <div class="report-kpi p-5 rounded-3xl border border-slate-100 bg-white shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-purple-500">
                                <div class="text-lg">❤️</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(selectedAd.swipeLeft) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Swipe Lefts</p>
                                <span class="text-[9px] font-black text-slate-400 mt-1">{{ getSwipeLeftRate(selectedAd).toFixed(1) }}% pass rate</span>
                            </div>
                            <div class="report-kpi p-5 rounded-3xl border border-slate-100 bg-white shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-indigo-500">
                                <div class="text-lg">📊</div>
                                <h5 class="text-xl font-black text-indigo-600">{{ getCtr(selectedAd).toFixed(1) }}%</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">CTR</p>
                                <span class="text-[9px] font-black mt-1" :class="getCtr(selectedAd) >= BENCHMARKS.ctr ? 'text-green-600' : 'text-amber-600'">
                                    {{ getCtr(selectedAd) >= BENCHMARKS.ctr ? '▲' : '▼' }} vs {{ BENCHMARKS.ctr }}% avg
                                </span>
                            </div>
                            <div class="report-kpi p-5 rounded-3xl border border-slate-100 bg-white shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-slate-300">
                                <div class="text-lg">←</div>
                                <h5 class="text-xl font-black text-slate-500">{{ num(selectedAd.swipeLeft) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Swipe Lefts</p>
                                <span class="text-[9px] font-black text-slate-400 mt-1">{{ selectedAd.impressions_count ? ((selectedAd.swipeLeft / selectedAd.impressions_count) * 100).toFixed(1) : '0.0' }}% pass rate</span>
                            </div>
                            <div class="report-kpi p-5 rounded-3xl border border-slate-100 bg-white shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-green-500">
                                <div class="text-lg">📆</div>
                                <h5 class="text-xl font-black text-green-600">{{ getPctElapsed(selectedAd) }}%</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Progress</p>
                                <span class="text-[9px] font-black text-slate-400 mt-1">{{ getDaysLeft(selectedAd) }} days remaining</span>
                            </div>
                        </div>

                        <!-- ENGAGEMENT FUNNEL -->
                        <div v-if="false" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Engagement Funnel
                            </h4>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-xs font-black text-slate-500 uppercase tracking-tighter">👁️ Impressions</div>
                                    <div class="flex-1 h-8 bg-slate-50 rounded-xl overflow-hidden relative border border-slate-100">
                                        <div class="h-full bg-slate-400 w-full flex items-center justify-end px-3">
                                            <span class="text-[10px] font-black text-white">{{ num(selectedAd.impressions_count) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-12 text-right text-xs font-black text-slate-400">100%</div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-xs font-black text-slate-500 uppercase tracking-tighter">❤️ Likes</div>
                                    <div class="flex-1 h-8 bg-slate-50 rounded-xl overflow-hidden relative border border-slate-100">
                                        <div class="h-full bg-purple-500 flex items-center justify-end px-3" :style="{ width: getLikeRate(selectedAd) + '%' }">
                                            <span class="text-[10px] font-black text-white">{{ num(selectedAd.swipeLeft) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-12 text-right text-xs font-black text-slate-400">{{ getLikeRate(selectedAd).toFixed(1) }}%</div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-xs font-black text-slate-500 uppercase tracking-tighter">→ Swipe Rights</div>
                                    <div class="flex-1 h-8 bg-slate-50 rounded-xl overflow-hidden relative border border-slate-100">
                                        <div class="h-full bg-indigo-500 flex items-center justify-end px-3" :style="{ width: getCtr(selectedAd) + '%' }">
                                            <span class="text-[10px] font-black text-white">{{ num(selectedAd.clicks_count) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-12 text-right text-xs font-black text-slate-400">{{ getCtr(selectedAd).toFixed(1) }}%</div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-xs font-black text-slate-500 uppercase tracking-tighter">← Swipe Lefts</div>
                                    <div class="flex-1 h-8 bg-slate-50 rounded-xl overflow-hidden relative border border-slate-100">
                                        <div class="h-full bg-slate-200 flex items-center justify-end px-3" :style="{ width: (selectedAd.impressions_count ? (selectedAd.swipeLeft / selectedAd.impressions_count * 100) : 0) + '%' }">
                                            <span class="text-[10px] font-black text-slate-500">{{ num(selectedAd.swipeLeft) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-12 text-right text-xs font-black text-slate-400">{{ selectedAd.impressions_count ? ((selectedAd.swipeLeft / selectedAd.impressions_count) * 100).toFixed(1) : '0.0' }}%</div>
                                </div>
                            </div>
                        </div>

                        <!-- VIDEO PERFORMANCE (Conditional) -->
                        <div v-if="(selectedAd.ad_type === 'video' || selectedAd.ad_type === 'both') && selectedAd.vidPlays > 0" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> 🎬 Video Ad Performance
                            </h4>
                            <div class="p-8 rounded-[2rem] bg-indigo-950 text-white space-y-8">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    <div class="text-center space-y-1">
                                        <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Video Plays</p>
                                        <h5 class="text-3xl font-black">{{ num(selectedAd.vidPlays) }}</h5>
                                    </div>
                                    <div class="text-center space-y-1">
                                        <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Completion Rate</p>
                                        <h5 class="text-3xl font-black text-green-400">{{ (selectedAd.vidCompletions / selectedAd.vidPlays * 100).toFixed(1) }}%</h5>
                                    </div>
                                    <div class="text-center space-y-1">
                                        <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Avg Watch Time</p>
                                        <h5 class="text-3xl font-black">{{ selectedAd.vidAvgWatch }}s</h5>
                                    </div>
                                    <div class="text-center space-y-1">
                                        <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Unmuted Rate</p>
                                        <h5 class="text-3xl font-black">{{ (selectedAd.vidSoundOn / selectedAd.vidPlays * 100).toFixed(1) }}%</h5>
                                    </div>
                                </div>

                                <div class="space-y-4 pt-4 border-t border-white/10">
                                    <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Completion Funnel</p>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-4">
                                            <span class="w-24 text-[10px] font-bold text-white/60">▶️ Played</span>
                                            <div class="flex-1 h-2 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-400 w-full"></div>
                                            </div>
                                            <span class="w-12 text-right text-[10px] font-black">100%</span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="w-24 text-[10px] font-bold text-white/60">📍 Halfway</span>
                                            <div class="flex-1 h-2 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500" style="width: 65%"></div>
                                            </div>
                                            <span class="w-12 text-right text-[10px] font-black">65%</span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="w-24 text-[10px] font-bold text-white/60">✅ Completed</span>
                                            <div class="flex-1 h-2 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full bg-green-400" :style="{ width: (selectedAd.vidCompletions / selectedAd.vidPlays * 100) + '%' }"></div>
                                            </div>
                                            <span class="w-12 text-right text-[10px] font-black">{{ (selectedAd.vidCompletions / selectedAd.vidPlays * 100).toFixed(1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="selectedAd.ad_type === 'video' || selectedAd.ad_type === 'both'" class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Video Ad Performance
                            </h4>
                            <div class="video-empty-state rounded-[2rem] bg-indigo-950 text-white text-center">
                                Video metrics will appear here once the campaign goes live.
                            </div>
                        </div>

                        <!-- VISUALIZATIONS -->
                        <div v-if="includeTrend && (selectedAd.daily.length || selectedAd.hours.length)" class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                            <!-- DAILY TREND -->
                            <div v-if="selectedAd.daily.length" class="space-y-4">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <span class="h-1 w-8 bg-slate-200"></span> Daily Engagement Trend
                                </h4>
                                <div class="p-6 rounded-[2rem] border border-slate-100 bg-slate-50/30">
                                    <div class="h-32 flex items-end gap-1 px-2">
                                        <div v-for="(val, i) in selectedAd.daily" :key="i"
                                            class="flex-1 rounded-t-sm transition-all hover:opacity-80"
                                            :class="i % 7 >= 5 ? 'bg-indigo-400' : 'bg-slate-200'"
                                            :style="{ height: (val / Math.max(...selectedAd.daily) * 100) + '%' }"
                                            :title="`Day ${i+1}: ${val}`">
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-3 px-2 text-[8px] font-black text-slate-300 uppercase tracking-widest">
                                        <span>Start</span>
                                        <span>Campaign Cycle ({{ selectedAd.daily.length }} days)</span>
                                        <span>End</span>
                                    </div>
                                </div>
                            </div>

                            <!-- PEAK HOURS -->
                            <div v-if="selectedAd.hours.length" class="space-y-4">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <span class="h-1 w-8 bg-slate-200"></span> Peak Engagement Hours
                                </h4>
                                <div class="p-6 rounded-[2rem] border border-slate-100 bg-slate-50/30">
                                    <div class="h-32 flex items-end gap-1 px-2">
                                        <div v-for="(val, i) in selectedAd.hours" :key="i"
                                            class="flex-1 rounded-t-sm transition-all hover:bg-indigo-500"
                                            :class="val === Math.max(...selectedAd.hours) ? 'bg-indigo-600' : 'bg-indigo-200'"
                                            :style="{ height: (val / Math.max(...selectedAd.hours) * 100) + '%' }">
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-3 px-2 text-[8px] font-black text-slate-300 uppercase tracking-widest">
                                        <span>12am</span>
                                        <span>12pm</span>
                                        <span>11pm</span>
                                    </div>
                                    <p v-if="selectedAd.hours.length" class="text-[10px] text-slate-500 mt-4 font-bold italic text-center">
                                        Engagement peaks at <span class="text-indigo-600 font-black">{{ getPeakHourLabel(selectedAd.hours) }}</span> local time.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- WOW PERFORMANCE -->
                        <div v-if="hasWeekData" class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Week-over-Week Performance
                            </h4>
                            <div class="rounded-[2rem] border border-slate-100 overflow-hidden shadow-sm">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 font-black text-slate-400 uppercase tracking-widest">
                                        <tr>
                                            <th class="px-6 py-4">Metric</th>
                                            <th class="px-6 py-4">Week 1</th>
                                            <th class="px-6 py-4">Week 2</th>
                                            <th class="px-6 py-4">Change</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 font-black text-slate-600">👁️ Impressions</td>
                                            <td class="px-6 py-4 font-bold">{{ num(selectedAd.week1.imp) }}</td>
                                            <td class="px-6 py-4 font-bold">{{ num(selectedAd.week2.imp) }}</td>
                                            <td class="px-6 py-4 font-black text-green-600">▲ {{ ((selectedAd.week2.imp - selectedAd.week1.imp) / selectedAd.week1.imp * 100).toFixed(1) }}%</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 font-black text-slate-600">→ Swipe Rights</td>
                                            <td class="px-6 py-4 font-bold">{{ num(selectedAd.week1.clicks) }}</td>
                                            <td class="px-6 py-4 font-bold">{{ num(selectedAd.week2.clicks) }}</td>
                                            <td class="px-6 py-4 font-black text-green-600">▲ {{ ((selectedAd.week2.clicks - selectedAd.week1.clicks) / selectedAd.week1.clicks * 100).toFixed(1) }}%</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 font-black text-slate-600">❤️ Likes</td>
                                            <td class="px-6 py-4 font-bold">{{ num(selectedAd.week1.likes) }}</td>
                                            <td class="px-6 py-4 font-bold">{{ num(selectedAd.week2.likes) }}</td>
                                            <td class="px-6 py-4 font-black" :class="selectedAd.week2.likes >= selectedAd.week1.likes ? 'text-green-600' : 'text-rose-600'">
                                                {{ selectedAd.week2.likes >= selectedAd.week1.likes ? '▲' : '▼' }} {{ Math.abs(((selectedAd.week2.likes - selectedAd.week1.likes) / Math.max(selectedAd.week1.likes, 1)) * 100).toFixed(1) }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- BENCHMARKS -->
                        <div v-if="false && includeBench && selectedAd.impressions_count > 0" class="space-y-8">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Performance Benchmarks
                            </h4>
                            <div class="space-y-10">
                                <div v-for="row in benchmarkRows" :key="row.label" class="relative">
                                    <div class="flex justify-between text-xs font-black mb-3">
                                        <span class="text-slate-500">{{ row.label }}</span>
                                        <span :class="row.val >= row.bench ? 'text-green-600' : 'text-amber-600'">{{ Number.isInteger(row.val) ? num(row.val) : row.val.toFixed(1) }}{{ row.unit }}</span>
                                    </div>
                                    <div class="h-3 bg-slate-50 rounded-full relative overflow-visible border border-slate-100">
                                        <div class="absolute top-0 h-full bg-slate-200 rounded-full" :style="{ width: Math.min(row.bench / row.scale * 100, 100) + '%' }"></div>
                                        <div class="absolute -top-1 h-5 rounded-full transition-all shadow-sm"
                                            :class="row.val >= row.bench ? 'bg-green-500' : 'bg-amber-500'"
                                            :style="{ width: Math.min(row.val / row.scale * 100, 100) + '%' }"></div>
                                        <div class="absolute -bottom-6 text-[9px] font-black text-slate-300" :style="{ left: Math.min(row.bench / row.scale * 100, 100) + '%', transform: 'translateX(-50%)' }">
                                            AVG {{ row.bench }}{{ row.unit }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROI ANALYSIS -->
                        <div v-if="false && includeRoi && selectedAd.impressions_count > 0" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Return on Investment
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="rounded-3xl border border-slate-100 bg-slate-50/50 p-6 flex flex-col gap-1">
                                    <span class="text-xl">💵</span>
                                    <h5 class="text-2xl font-black text-slate-900">{{ fmt(parseFloat(selectedAd.cost || 0)) }}</h5>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Ad Spend</p>
                                </div>
                                <div class="rounded-3xl border border-slate-100 bg-slate-50/50 p-6 flex flex-col gap-1">
                                    <span class="text-xl">👆</span>
                                    <h5 class="text-2xl font-black text-blue-600">${{ getCpc(selectedAd).toFixed(2) }}</h5>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cost Per Click</p>
                                </div>
                                <div class="rounded-3xl border border-slate-100 bg-slate-50/50 p-6 flex flex-col gap-1">
                                    <span class="text-xl">👁️</span>
                                    <h5 class="text-2xl font-black text-purple-600">${{ getCpm(selectedAd).toFixed(2) }}</h5>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">CPM (per 1k imp)</p>
                                </div>
                            </div>
                            <div class="rounded-3xl bg-lime-50 border border-lime-100 p-6 text-sm text-slate-600 font-medium leading-relaxed">
                                For every <strong class="text-slate-900">$1 spent</strong>, {{ selectedAd.name }} reached
                                <strong class="text-slate-900">{{ num(Math.round(selectedAd.impressions_count / Math.max(parseFloat(selectedAd.cost || 0), 1))) }} people</strong> and earned
                                <strong class="text-slate-900">{{ num(Math.round(selectedAd.clicks_count / Math.max(parseFloat(selectedAd.cost || 0), 1))) }} website visits</strong>.
                                {{ getCtr(selectedAd) >= BENCHMARKS.ctr ? 'This is above average efficiency for the LinkUp platform.' : 'There is room to improve efficiency — refreshing the creative or headline can increase swipe rights without increasing spend.' }}
                            </div>
                        </div>

                        <!-- RECOMMENDATIONS -->
                        <div v-if="includeReco" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Our Recommendations for {{ selectedAd.name }}
                            </h4>
                            <div class="space-y-3">
                                <div v-for="(reco, i) in swipeRecommendations" :key="i" class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-5">
                                    <div class="text-2xl flex-shrink-0">{{ reco.icon }}</div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900">{{ reco.title }}</div>
                                        <div class="text-xs font-medium text-slate-500 leading-relaxed mt-1">{{ reco.body }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CAMPAIGN DETAILS -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Campaign Details
                            </h4>
                            <div class="detail-panel rounded-[2.5rem] bg-slate-50 border border-slate-100 p-10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-6">
                                    <div class="space-y-4">
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Advertiser</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.name }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Category</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.category || '—' }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Ad Format</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.ad_type === 'video' ? '🎬 Video Ad' : selectedAd.ad_type === 'both' ? '✨ Image + Video' : '🖼️ Image Ad' }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Video Duration</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.vidDuration || 'â€”' }}s</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Package</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.price_package || '—' }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Campaign Cost</span>
                                            <span class="text-xs font-black text-slate-900">{{ fmt(parseFloat(selectedAd.cost || 0)) }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Payment Status</span>
                                            <span class="text-xs font-black" :class="selectedAd.is_paid ? 'text-green-600' : 'text-amber-600'">{{ selectedAd.is_paid ? '✅ Paid' : '⚠️ Pending' }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Start Date</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.start_date }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">End Date</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedAd.end_date }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Duration</span>
                                            <span class="text-xs font-black text-slate-900">{{ getDaysRunning(selectedAd) }} days</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Progress</span>
                                            <span class="text-xs font-black text-slate-900">{{ getPctElapsed(selectedAd) }}% complete ({{ getDaysLeft(selectedAd) }} days left)</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Location</span>
                                            <span class="text-xs font-black text-slate-900">{{ [selectedAd.city, selectedAd.location].filter(Boolean).join(' · ') || '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ── EMAIL SPONSOR REPORT ── -->
                <template v-else-if="selectedEmailSponsor">
                    <!-- Header Band (Teal Gradient) -->
                    <div class="report-header-band report-header-band-email p-10 text-white flex flex-col md:flex-row justify-between items-start gap-8 bg-gradient-to-br from-teal-700 to-teal-900">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="email-report-icon h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center font-black text-2xl">✉️</div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">Email Sponsor Report</p>
                                    <h2 class="text-2xl font-black tracking-tighter">{{ selectedEmailSponsor.name }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="text-xs font-bold opacity-40">Generated: <strong>{{ new Date().toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}) }}</strong></p>
                            <p class="text-xs font-bold opacity-40">LinkUp Vibes Ad Platform · linkupvibes.com</p>
                        </div>
                    </div>

                    <div class="report-body email-report-body p-10 space-y-10">
                        <div v-if="false" class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Executive Summary
                            </h4>
                            <div class="rounded-3xl bg-teal-50 border border-teal-100 p-8">
                                <p class="text-lg text-teal-900 leading-relaxed font-medium">
                                    <strong class="text-slate-900">{{ selectedEmailSponsor.name }}</strong>'s email sponsor campaign has recorded
                                    <strong class="text-indigo-600">{{ num(selectedEmailSponsor.impressions) }} tracked email impressions</strong>
                                    and {{ num(selectedEmailSponsor.clicks) }} recorded ad clicks.
                                    The <strong class="text-indigo-600">{{ getEmailClickRate(selectedEmailSponsor).toFixed(1) }}% click-through rate</strong>
                                    is calculated from sponsor click records.
                                    At <strong class="text-indigo-600">${{ getEmailCpc(selectedEmailSponsor).toFixed(2) }} cost per click</strong>, this campaign represents
                                    this report reflects only events recorded in the database.
                                </p>
                            </div>
                        </div>

                        <!-- KPI GRID (6 items) -->
                        <div class="report-kpi-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-teal-500">
                                <div class="text-lg">✉️</div>
                                <h5 class="text-xl font-black text-slate-900">{{ compactNum(selectedEmailSponsor.impressions) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Email Impressions</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-blue-500">
                                <div class="text-lg">📬</div>
                                <h5 class="text-xl font-black text-slate-900">{{ compactNum(selectedEmailSponsor.opens) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Opens</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-cyan-500">
                                <div class="text-lg">👆</div>
                                <h5 class="text-xl font-black text-slate-900">{{ num(selectedEmailSponsor.clicks) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ad Clicks</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-amber-500">
                                <div class="text-lg">📊</div>
                                <h5 class="text-xl font-black text-slate-900">{{ getEmailOpenRate(selectedEmailSponsor).toFixed(1) }}%</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Open Rate</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-purple-500">
                                <div class="text-lg">%</div>
                                <h5 class="text-xl font-black text-slate-900">{{ getEmailClickRate(selectedEmailSponsor).toFixed(1) }}%</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Click-Through Rate</p>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden border-t-4 border-t-green-500">
                                <div class="text-lg">💵</div>
                                <h5 class="text-xl font-black text-slate-900">{{ fmt(selectedEmailSponsor.revenue) }}</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ad Spend</p>
                            </div>
                        </div>

                        <div class="email-report-two-col">
                        <!-- ENGAGEMENT FUNNEL -->
                        <div class="email-report-card space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Engagement Funnel
                            </h4>
                            <div class="email-funnel-list">
                                <div class="email-funnel-row">
                                    <div class="email-funnel-label">Delivered</div>
                                    <div class="email-funnel-track">
                                        <div class="email-funnel-fill bg-teal-500" style="width:100%">
                                            <span>{{ compactNum(selectedEmailSponsor.impressions) }}</span>
                                        </div>
                                    </div>
                                    <div class="email-funnel-pct">100%</div>
                                </div>
                                <div class="email-funnel-row">
                                    <div class="email-funnel-label">Opened</div>
                                    <div class="email-funnel-track">
                                        <div class="email-funnel-fill bg-blue-500" :style="{ width: getEmailOpenRate(selectedEmailSponsor) + '%' }">
                                            <span>{{ compactNum(selectedEmailSponsor.opens) }}</span>
                                        </div>
                                    </div>
                                    <div class="email-funnel-pct">{{ getEmailOpenRate(selectedEmailSponsor).toFixed(1) }}%</div>
                                </div>
                                <div class="email-funnel-row">
                                    <div class="email-funnel-label">Clicked Ad</div>
                                    <div class="email-funnel-track">
                                        <div class="email-funnel-fill bg-cyan-500" :style="{ width: getEmailClickRate(selectedEmailSponsor) + '%' }">
                                            <span>{{ num(selectedEmailSponsor.clicks) }}</span>
                                        </div>
                                    </div>
                                    <div class="email-funnel-pct">{{ getEmailClickRate(selectedEmailSponsor).toFixed(1) }}%</div>
                                </div>
                            </div>
                        </div>

                        <!-- CAMPAIGN DETAILS -->
                        <div class="email-report-card space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> Campaign Details
                            </h4>
                            <div class="detail-panel email-detail-panel rounded-[2.5rem] bg-slate-50 border border-slate-100 p-10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-6">
                                    <div class="space-y-4">
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Email Category</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedEmailSponsor.cat }} Emails</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Target Regions</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedEmailSponsor.regions }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Priority Level</span>
                                            <span class="text-xs font-black text-slate-900">P{{ selectedEmailSponsor.priority }} — {{ selectedEmailSponsor.priority === 1 ? 'Highest' : 'High' }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Campaign Start</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedEmailSponsor.start }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Campaign End</span>
                                            <span class="text-xs font-black text-slate-900">{{ selectedEmailSponsor.end }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase">Cost Per Click</span>
                                            <span class="text-xs font-black text-teal-600">${{ getEmailCpc(selectedEmailSponsor).toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="email-summary-callout">
                            <strong>{{ selectedEmailSponsor.name }}</strong>'s email sponsor campaign is delivering a
                            <strong>{{ getEmailOpenRate(selectedEmailSponsor).toFixed(1) }}% open rate</strong>.
                            The <strong>{{ getEmailClickRate(selectedEmailSponsor).toFixed(1) }}% click-through rate</strong>
                            and <strong>${{ getEmailCpc(selectedEmailSponsor).toFixed(2) }} cost per click</strong>
                            are calculated from tracked database activity.
                        </div>

                        <!-- RECOMMENDATIONS -->
                        <div class="email-report-card email-recommendations-card space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="h-1 w-8 bg-slate-200"></span> 💡 Recommendations
                            </h4>
                            <div class="space-y-3">
                                <div v-for="(reco, i) in emailRecommendations" :key="i" class="flex gap-4 rounded-2xl border border-teal-100 bg-teal-50/40 p-5">
                                    <div class="text-2xl flex-shrink-0">{{ reco.icon }}</div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900">{{ reco.title }}</div>
                                        <div class="text-xs font-medium text-slate-500 leading-relaxed mt-1">{{ reco.body }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Footer -->
                <div class="report-footer border-t border-slate-50 bg-slate-50/30 px-10 py-8 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-900">LinkUp Vibes</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span class="text-[10px] font-bold text-slate-400">Report generated {{ new Date().toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}) }}</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">© 2026 LinkUp Global</p>
                </div>
            </div>
            <div v-if="selectedAd" class="report-bottom-actions print:hidden">
                <button @click="openEmailAdvertiser(selectedAd)" class="report-bottom-btn report-bottom-btn-email">
                    <Mail class="h-4 w-4" /> Email to Advertiser
                </button>
                <button @click="printReport" class="report-bottom-btn report-bottom-btn-print">
                    <Printer class="h-4 w-4" /> Print / Save as PDF
                </button>
                <button @click="resetReport" class="report-bottom-btn report-bottom-btn-new">
                    <ChevronLeft class="h-4 w-4" /> New Report
                </button>
            </div>
            <div v-else-if="selectedEmailSponsor" class="report-bottom-actions email-report-bottom-actions print:hidden">
                <button @click="resetReport" class="report-bottom-btn report-bottom-btn-new">
                    <ChevronLeft class="h-4 w-4" /> Back
                </button>
                <button @click="openEmailAdvertiser(selectedEmailSponsor)" class="report-bottom-btn report-bottom-btn-email">
                    <Mail class="h-4 w-4" /> Email Report
                </button>
                <button @click="printReport" class="report-bottom-btn report-bottom-btn-new">
                    <Printer class="h-4 w-4" /> Print PDF
                </button>
            </div>
        </div>

        <!-- EMAIL ADVERTISER MODAL -->
        <div v-if="showEmailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="p-8 bg-slate-950 text-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center"><Send class="w-5 h-5 text-indigo-400" /></div>
                        <div>
                            <h3 class="text-lg font-black tracking-tight">Send Report</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-40">Email advertiser directly</p>
                        </div>
                    </div>
                    <button @click="showEmailModal = false" class="h-10 w-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors">
                        <X class="w-6 h-6" />
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">To (Advertiser Email)</label>
                            <input v-model="emailForm.to" type="email" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:ring-[#06b6d4] focus:border-[#06b6d4]" placeholder="contact@brand.com" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Subject</label>
                            <input v-model="emailForm.subject" type="text" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:ring-[#06b6d4] focus:border-[#06b6d4]" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Personal Note</label>
                            <textarea v-model="emailForm.note" rows="4" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:ring-[#06b6d4] focus:border-[#06b6d4] resize-none" placeholder="Add a custom message..."></textarea>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" v-model="emailForm.attachPdf" class="peer h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        </div>
                        <span class="text-xs font-black text-slate-600 group-hover:text-slate-900 transition-colors">Attach Performance PDF Report</span>
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button @click="showEmailModal = false" class="flex-1 rounded-2xl border border-slate-200 px-6 py-3.5 text-xs font-black text-slate-600 hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button @click="sendEmail" class="flex-[2] rounded-2xl bg-[#06b6d4] px-6 py-3.5 text-xs font-black text-white shadow-lg shadow-cyan-500/20 hover:bg-[#0891b2] transition-all flex items-center justify-center gap-2">
                            <Send class="w-4 h-4" /> Send Report Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.report-builder-card {
    border-radius: 18px;
}

.report-preview {
    width: min(100%, 900px);
    max-width: 900px;
    margin: 0 auto;
    padding: 40px !important;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 12px !important;
    box-shadow: none !important;
    overflow: visible !important;
    scroll-margin-top: 96px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.report-preview .report-header-band {
    margin: 0 0 32px;
    padding: 28px 32px !important;
    border-radius: 12px !important;
    background: linear-gradient(135deg, #0f1f0a 0%, #1a3a0d 100%) !important;
    gap: 20px !important;
}

.report-preview .report-header-band-email {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
}

.report-preview .report-header-band-email .email-report-icon {
    display: flex !important;
    width: 52px !important;
    height: 52px !important;
    border-radius: 14px !important;
    background: rgba(255, 255, 255, 0.15) !important;
    font-size: 24px !important;
}

.report-preview .report-header-band-email h2 {
    color: #fff !important;
    font-size: 22px !important;
    letter-spacing: 0 !important;
}

.report-preview .report-header-band .h-12 {
    display: none !important;
}

.report-preview .report-header-band h2 {
    color: #22d3ee;
    font-size: 32px !important;
    line-height: 1 !important;
    letter-spacing: -1.5px;
}

.report-preview .report-header-band-email .email-report-icon {
    display: flex !important;
}

.report-preview .report-header-band-email h2 {
    color: #fff !important;
    font-size: 22px !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
}

.report-preview .report-header-band h3,
.report-preview .report-header-band .text-4xl {
    margin-top: 14px;
    font-size: 22px !important;
    line-height: 1.2 !important;
}

.report-preview .report-header-band p,
.report-preview .report-header-band .text-xs {
    font-size: 10px !important;
}

.report-preview .report-header-band .pt-4 {
    padding-top: 8px !important;
}

.report-preview .report-header-band .rounded-full {
    background: rgba(138, 191, 46, 0.2) !important;
    color: #bef264 !important;
    border-color: rgba(190, 242, 100, 0.2) !important;
}

.report-body {
    padding: 0 !important;
}

.report-body > :not([hidden]) ~ :not([hidden]) {
    margin-top: 28px !important;
}

.report-body h4 {
    margin: 0 0 12px;
    font-size: 10px !important;
    letter-spacing: 1.5px !important;
    color: #94a3b8 !important;
}

.report-body h4 span {
    width: 28px !important;
    height: 1px !important;
}

.report-body .rounded-3xl {
    border-radius: 10px !important;
}

.report-kpi-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 12px !important;
}

.report-kpi-grid > div {
    min-height: 118px;
    padding: 18px 16px !important;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 10px !important;
    background: #fff !important;
    box-shadow: none !important;
}

.report-kpi-grid h5 {
    font-size: 26px !important;
    line-height: 1 !important;
}

.report-kpi-grid p {
    margin-top: 4px;
    font-size: 11px !important;
    letter-spacing: 0 !important;
}

.report-body .h-8 {
    height: 28px !important;
}

.report-body .rounded-xl {
    border-radius: 6px !important;
}

.email-report-body > :not([hidden]) ~ :not([hidden]) {
    margin-top: 20px !important;
}

.email-report-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.email-report-card {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.email-report-card > h4 {
    margin: 0 !important;
    padding: 14px 18px;
    border-bottom: 1px solid #e5e7eb;
    color: #0f172a !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

.email-report-card > h4 span {
    display: none;
}

.email-funnel-list {
    padding: 24px 20px;
    display: grid;
    gap: 14px;
}

.email-funnel-row {
    display: grid;
    grid-template-columns: 84px 1fr 44px;
    align-items: center;
    gap: 10px;
}

.email-funnel-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.email-funnel-track {
    height: 18px;
    border-radius: 5px;
    background: #f1f5f9;
    overflow: hidden;
}

.email-funnel-fill {
    height: 100%;
    min-width: 4px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    color: #fff;
    font-size: 10px;
    font-weight: 900;
}

.email-funnel-pct {
    text-align: right;
    font-size: 12px;
    font-weight: 900;
    color: #0f172a;
}

.email-detail-panel {
    border: none !important;
    border-radius: 0 !important;
    background: #fff !important;
    padding: 16px 18px !important;
    box-shadow: none !important;
}

.email-detail-panel .grid {
    display: block !important;
}

.email-detail-panel .flex {
    padding: 11px 0 !important;
}

.email-summary-callout {
    border-left: 4px solid #06b6d4;
    border-radius: 0 10px 10px 0;
    background: #f7fee7;
    padding: 16px 20px;
    font-size: 13px;
    line-height: 1.65;
    color: #475569;
}

.email-summary-callout strong {
    color: #0f172a;
}

.email-recommendations-card {
    padding-bottom: 14px;
}

.email-recommendations-card .space-y-3 {
    padding: 18px;
}

.email-recommendations-card .space-y-3 > div {
    border: none !important;
    border-radius: 8px !important;
    background: #f7fbf4 !important;
    padding: 14px 16px !important;
}

.email-report-bottom-actions {
    justify-content: flex-end;
}

.video-empty-state {
    padding: 24px;
    border-radius: 14px !important;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.55);
    background: linear-gradient(135deg, #1a0a2e 0%, #2d1060 100%) !important;
}

.detail-panel {
    padding: 20px !important;
    border-radius: 10px !important;
    background: #f8fafc !important;
}

.detail-panel .grid {
    gap: 0 32px !important;
}

.detail-panel .space-y-4 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 0 !important;
}

.detail-panel .flex {
    padding: 10px 0 !important;
}

.report-footer {
    margin-top: 36px;
    padding: 20px 0 0 !important;
    border-top: 1px solid #e5e7eb !important;
    background: transparent !important;
    font-size: 11px;
}

.report-bottom-actions {
    width: min(100%, 900px);
    max-width: 900px;
    margin: 12px auto 0;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.report-bottom-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 36px;
    border-radius: 8px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 800;
    border: 1px solid #dbe5ef;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.report-bottom-btn:hover {
    transform: translateY(-1px);
}

.report-bottom-btn-email {
    color: #fff;
    border-color: #a3e635;
    background: linear-gradient(135deg, #befa00 0%, #8abf2e 100%);
    box-shadow: 0 8px 16px rgba(132, 204, 22, 0.22);
}

.report-bottom-btn-print {
    color: #fff;
    border-color: #06b6d4;
    background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%);
    box-shadow: 0 8px 16px rgba(6, 182, 212, 0.2);
}

.report-bottom-btn-new {
    color: #0f172a;
    background: #fff;
}

.adv-card {
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.adv-card:hover,
.adv-card.selected {
    border-color: #06b6d4;
    background: #ecfeff;
    box-shadow: 0 16px 30px rgba(6, 182, 212, 0.12);
    transform: translateY(-1px);
}

.adv-card.selected {
    outline: 2px solid rgba(6, 182, 212, 0.18);
    outline-offset: 2px;
}

@media print {
    .card {
        box-shadow: none;
        border: none;
    }
    .print\:hidden {
        display: none !important;
    }
    #report-printable {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
    }
}

@media (max-width: 900px) {
    .report-preview {
        padding: 24px !important;
    }

    .report-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .email-report-two-col {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .report-kpi-grid {
        grid-template-columns: 1fr !important;
    }
}

.scrollbar::-webkit-scrollbar {
    height: 4px;
}
.scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
</style>
