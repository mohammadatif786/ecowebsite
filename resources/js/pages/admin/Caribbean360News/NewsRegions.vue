<script setup lang="ts">
import { computed, ref } from 'vue';
import { BarChart3, Download, Eye, FileBarChart, Map, Send, TrendingUp, Users, X } from 'lucide-vue-next';

type CoarseRegion = 'Caribbean' | 'Latin America';

type RegionRow = {
    name: string;
    region: CoarseRegion;
    coverage: string;
    stories: number;
    views: number;
    readers: number;
    trend: number;
    status: string;
    months: number[];
};

type Ad = {
    id: string;
    sponsor: string;
    title: string;
    format: 'image' | 'video' | 'audio';
    value: number;
    target: string;
    targetType: 'story' | 'region' | 'category' | 'all';
    status: 'Active' | 'Scheduled' | 'Paused';
    impressions: number;
    clicks: number;
};

type TopStory = {
    title: string;
    region: CoarseRegion;
    fire: number;
};

const regions: RegionRow[] = [
    { name: 'Bahamas', region: 'Caribbean', coverage: 'Nassau, Grand Bahama, Eleuthera, Abaco', stories: 44, views: 312000, readers: 38500, trend: 8.4, status: 'Active', months: [210, 238, 255, 272, 295, 312] },
    { name: 'Jamaica', region: 'Caribbean', coverage: 'Kingston, Montego Bay, Ocho Rios', stories: 38, views: 248000, readers: 29150, trend: 6.1, status: 'Active', months: [190, 205, 214, 226, 239, 248] },
    { name: 'Trinidad & Tobago', region: 'Caribbean', coverage: 'Port of Spain, San Fernando, Tobago', stories: 31, views: 191000, readers: 18400, trend: 4.7, status: 'Active', months: [150, 162, 170, 178, 185, 191] },
    { name: 'Barbados / OECS', region: 'Caribbean', coverage: 'Barbados, St. Lucia, Grenada, Antigua', stories: 29, views: 128000, readers: 17600, trend: 5.2, status: 'Active', months: [98, 104, 112, 118, 123, 128] },
    { name: 'Latin America', region: 'Latin America', coverage: 'Colombia, Peru, Mexico, Brazil', stories: 35, views: 210000, readers: 24900, trend: 9.3, status: 'Active', months: [140, 158, 172, 186, 198, 210] },
    { name: 'Caribbean Diaspora', region: 'Caribbean', coverage: 'USA, Canada, UK', stories: 57, views: 461000, readers: 52300, trend: 7.8, status: 'Active', months: [360, 388, 405, 428, 447, 461] },
];

const ads: Ad[] = [
    { id: 'ad1', sponsor: 'Island Audio', title: 'Relaxing Caribbean Sounds', format: 'audio', value: 1800, target: 's1', targetType: 'story', status: 'Active', impressions: 184000, clicks: 4220 },
    { id: 'ad2', sponsor: 'Caribbean Travel Co.', title: 'Escape to the Islands', format: 'image', value: 3200, target: 'Caribbean', targetType: 'region', status: 'Active', impressions: 220000, clicks: 5880 },
    { id: 'ad3', sponsor: 'LinkUp Marketplace', title: 'Shop Local, Ship Global', format: 'video', value: 2600, target: 'Business & Economy', targetType: 'category', status: 'Active', impressions: 98000, clicks: 2110 },
    { id: 'ad4', sponsor: 'Scotiabank', title: 'Bank Smarter with Scotia', format: 'image', value: 2500, target: 'Latin America', targetType: 'region', status: 'Active', impressions: 310000, clicks: 8420 },
    { id: 'ad5', sponsor: 'Digicel', title: 'Stay Connected, Stay 360', format: 'image', value: 850, target: 'all', targetType: 'all', status: 'Active', impressions: 120000, clicks: 3100 },
];

const topStoryPool: TopStory[] = [
    { title: 'Bahamas Independence Weekend Events Draw Record Crowds', region: 'Caribbean', fire: 3 },
    { title: 'Jamaica Carnival Travel Guide', region: 'Caribbean', fire: 2 },
    { title: 'Trinidad Soca Weekend Preview', region: 'Caribbean', fire: 1 },
    { title: 'Barbados Crop Over Road March Watch', region: 'Caribbean', fire: 2 },
    { title: 'Guyana Energy Corridor Update Signals New Investment', region: 'Caribbean', fire: 1 },
    { title: 'Diaspora Entrepreneurs Increase Caribbean Investment', region: 'Caribbean', fire: 2 },
    { title: 'Peace Strategy Advances with Mixed Results', region: 'Latin America', fire: 2 },
    { title: 'Brazil Intensifies Amazon Protection Amid Global Pressure', region: 'Latin America', fire: 2 },
];

const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

const num = (value: number) => Math.round(value || 0).toLocaleString();
const kfmt = (value: number) => (value >= 1000 ? `${(value / 1000).toFixed(value >= 100000 ? 0 : 1)}K` : num(value));

const totalViews = computed(() => regions.reduce((sum, region) => sum + region.views, 0));
const totalReaders = computed(() => regions.reduce((sum, region) => sum + region.readers, 0));
const avgTrend = computed(() => (regions.reduce((sum, region) => sum + region.trend, 0) / regions.length).toFixed(1));

const regionSponsors = (region: RegionRow) =>
    ads.filter((ad) => ad.status === 'Active' && (ad.targetType === 'all' || (ad.targetType === 'region' && ad.target === region.region) || ad.targetType === 'story'));

const regionTopStories = (region: RegionRow) => {
    const matches = topStoryPool.filter((story) => story.region === region.region);
    return (matches.length ? matches : topStoryPool).slice(0, 4);
};

const deepRegion = ref<RegionRow | null>(null);

const openRegionDeep = (region: RegionRow) => {
    deepRegion.value = region;
};

const closeRegionDeep = () => {
    deepRegion.value = null;
};

const regionReportText = (region: RegionRow) => {
    const sponsors = regionSponsors(region);
    let text = `CARIBBEAN 360 NEWS — REGION READERSHIP REPORT\n${'='.repeat(48)}\n\nRegion: ${region.name}\nCoverage: ${region.coverage}\nGenerated: ${new Date().toLocaleString()}\n\n`;
    text += `Monthly Views: ${region.views.toLocaleString()}\nUnique Readers: ${region.readers.toLocaleString()}\nStories Published: ${region.stories}\nMonth-over-Month Growth: ${region.trend}%\n\n`;
    text += `6-Month Views (000s): ${region.months.join(', ')}\n\nACTIVE SPONSORS / PLACEMENTS\n${'-'.repeat(48)}\n`;
    text += sponsors.length
        ? sponsors.map((ad) => `${ad.sponsor} — ${ad.title} (${ad.format}) | ${ad.impressions.toLocaleString()} impressions, ${ad.clicks.toLocaleString()} clicks, $${ad.value.toLocaleString()}`).join('\n')
        : 'None';
    text += '\n\nReport prepared by LinkUp Caribbean 360 Desk.';
    return text;
};

const download = (filename: string, text: string) => {
    const blob = new Blob([text], { type: 'text/plain' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
};

const exportRegionReport = () => {
    if (!deepRegion.value) return;
    download(`C360-${deepRegion.value.name.replace(/[^a-z0-9]+/gi, '-')}-readership.txt`, regionReportText(deepRegion.value));
};

const regionToSponsor = () => {
    if (!deepRegion.value) return;
    const region = deepRegion.value;
    window.alert(
        `📤 Readership report for ${region.name} prepared.\n\n${region.readers.toLocaleString()} readers • ${kfmt(region.views)} monthly views • ${regionSponsors(region).length} active sponsor placement(s).\n\nUse Export to download and send to the sponsor.`,
    );
};

const sponsors = computed(() => Array.from(new Set(ads.map((ad) => ad.sponsor))));
const showSponsorReport = ref(false);
const selectedSponsor = ref('');

const openSponsorReport = () => {
    selectedSponsor.value = sponsors.value[0] || '';
    showSponsorReport.value = true;
};

const closeSponsorReport = () => {
    showSponsorReport.value = false;
};

const sponsorAds = computed(() => ads.filter((ad) => ad.sponsor === selectedSponsor.value));
const sponsorImpressions = computed(() => sponsorAds.value.reduce((sum, ad) => sum + ad.impressions, 0));
const sponsorClicks = computed(() => sponsorAds.value.reduce((sum, ad) => sum + ad.clicks, 0));
const sponsorValue = computed(() => sponsorAds.value.reduce((sum, ad) => sum + ad.value, 0));
const sponsorCtr = computed(() => (sponsorImpressions.value ? ((sponsorClicks.value / sponsorImpressions.value) * 100).toFixed(1) : '0.0'));
const sponsorRegionsReached = computed(() => {
    if (sponsorAds.value.some((ad) => ad.targetType === 'all')) return regions;
    return regions.filter((region) => sponsorAds.value.some((ad) => ad.targetType === 'region' && ad.target === region.region));
});
const sponsorReach = computed(() => sponsorRegionsReached.value.reduce((sum, region) => sum + region.readers, 0));

const targetLabel = (ad: Ad) => (ad.targetType === 'story' ? 'Story' : ad.targetType === 'all' ? 'All Stories' : `${ad.targetType.charAt(0).toUpperCase()}${ad.targetType.slice(1)}: ${ad.target}`);

const exportSponsorReport = () => {
    let text = `CARIBBEAN 360 NEWS — SPONSOR READERSHIP REPORT\n${'='.repeat(48)}\n\nSponsor: ${selectedSponsor.value}\nGenerated: ${new Date().toLocaleString()}\n\n`;
    text += `Total Impressions: ${sponsorImpressions.value.toLocaleString()}\nTotal Clicks: ${sponsorClicks.value.toLocaleString()}\nCTR: ${sponsorCtr.value}%\nMedia Value: $${sponsorValue.value.toLocaleString()}\n\n`;
    text += `PLACEMENTS\n${'-'.repeat(48)}\n`;
    text += sponsorAds.value.map((ad) => `${ad.title} (${ad.format}) | ${ad.impressions.toLocaleString()} impr, ${ad.clicks.toLocaleString()} clicks`).join('\n');
    text += '\n\nReport prepared by LinkUp Caribbean 360 Desk.';
    download(`C360-${(selectedSponsor.value || 'sponsor').replace(/[^a-z0-9]+/gi, '-')}-report.txt`, text);
};

const chartMax = (region: RegionRow) => Math.max(...region.months);
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black">News Regions &amp; Readership</h3>
                <p class="text-slate-500">Deep dive into monthly viewers by region and generate sponsor-ready readership reports.</p>
            </div>
            <button class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="openSponsorReport">
                <FileBarChart class="h-4 w-4" /> Sponsor Report
            </button>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-100 text-sky-600"><Eye class="h-5 w-5" /></div>
                <div><h3 class="text-3xl font-black">{{ kfmt(totalViews) }}</h3><p class="text-sm text-slate-500">Monthly Views</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-green-600"><Users class="h-5 w-5" /></div>
                <div><h3 class="text-3xl font-black">{{ num(totalReaders) }}</h3><p class="text-sm text-slate-500">Unique Readers</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-purple-600"><Map class="h-5 w-5" /></div>
                <div><h3 class="text-3xl font-black">{{ regions.length }}</h3><p class="text-sm text-slate-500">Active Regions</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-100 text-amber-600"><TrendingUp class="h-5 w-5" /></div>
                <div><h3 class="text-3xl font-black">{{ avgTrend }}%</h3><p class="text-sm text-slate-500">Avg MoM Growth</p></div>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-4">Region</th>
                            <th>Coverage</th>
                            <th>Stories</th>
                            <th>Monthly Views</th>
                            <th>Readers</th>
                            <th>MoM</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="region in regions" :key="region.name" class="border-t border-slate-100">
                            <td class="p-4 font-black">{{ region.name }}</td>
                            <td class="text-slate-500">{{ region.coverage }}</td>
                            <td>{{ num(region.stories) }}</td>
                            <td class="font-black">{{ kfmt(region.views) }}</td>
                            <td>{{ num(region.readers) }}</td>
                            <td><span class="font-bold text-green-600">▲ {{ region.trend }}%</span></td>
                            <td><span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">{{ region.status }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="flex items-center gap-1 rounded-xl bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700" @click="openRegionDeep(region)">
                                        <BarChart3 class="h-4 w-4" /> Deep Dive
                                    </button>
                                    <button
                                        class="flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold"
                                        @click="() => { openRegionDeep(region); regionToSponsor(); }"
                                    >
                                        <Send class="h-4 w-4" /> Report
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Region deep dive modal -->
        <div v-if="deepRegion" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3">
            <div class="my-4 w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">{{ deepRegion.name }} — Readership Deep Dive</h3>
                        <p class="text-sm text-sky-100">{{ deepRegion.coverage }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 font-black text-sky-600" @click="exportRegionReport">
                            <Download class="h-4 w-4" /> Export
                        </button>
                        <button class="flex items-center gap-2 rounded-2xl bg-white/20 px-4 py-2.5 font-black" @click="regionToSponsor">
                            <Send class="h-4 w-4" /> Send to Sponsor
                        </button>
                        <button class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20" @click="closeRegionDeep"><X class="h-6 w-6" /></button>
                    </div>
                </div>
                <div class="max-h-[80vh] space-y-5 overflow-y-auto p-6">
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-2xl font-black text-slate-800">{{ kfmt(deepRegion.views) }}</p><p class="mt-1 text-xs font-bold text-slate-500">Monthly Views</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-2xl font-black text-slate-800">{{ num(deepRegion.readers) }}</p><p class="mt-1 text-xs font-bold text-slate-500">Unique Readers</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-2xl font-black text-slate-800">{{ num(deepRegion.stories) }}</p><p class="mt-1 text-xs font-bold text-slate-500">Stories</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-2xl font-black text-slate-800">▲ {{ deepRegion.trend }}%</p><p class="mt-1 text-xs font-bold text-slate-500">MoM Growth</p></div>
                    </div>

                    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <h4 class="mb-3 text-lg font-black">Monthly Viewers — Last 6 Months</h4>
                        <div class="flex h-44 items-end gap-3">
                            <div v-for="(value, idx) in deepRegion.months" :key="idx" class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                                <span class="text-xs font-bold text-slate-500">{{ kfmt(value * 1000) }}</span>
                                <div class="w-full rounded-t-xl bg-linear-to-t from-sky-500 to-cyan-400" :style="{ height: `${Math.round((value / chartMax(deepRegion)) * 100)}%` }"></div>
                                <span class="text-xs font-bold text-slate-400">{{ monthLabels[idx] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <h4 class="mb-3 text-lg font-black">Top Stories in Region</h4>
                            <div class="space-y-2">
                                <div v-for="story in regionTopStories(deepRegion)" :key="story.title" class="flex justify-between gap-2 rounded-2xl bg-slate-50 p-3">
                                    <span class="text-sm font-bold">{{ story.title.length > 40 ? `${story.title.slice(0, 40)}…` : story.title }}</span>
                                    <span class="whitespace-nowrap text-xs font-bold text-slate-400">🔥 {{ story.fire }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <h4 class="mb-3 text-lg font-black">Active Sponsors in Region</h4>
                            <div v-if="regionSponsors(deepRegion).length" class="space-y-2">
                                <div v-for="ad in regionSponsors(deepRegion)" :key="ad.id" class="flex justify-between gap-2 rounded-2xl bg-slate-50 p-3">
                                    <div>
                                        <b class="text-sm">{{ ad.sponsor }}</b>
                                        <p class="text-xs text-slate-500">{{ ad.title }} • {{ ad.format }}</p>
                                    </div>
                                    <div class="text-right text-xs font-bold">
                                        <p>{{ num(ad.impressions) }} impr</p>
                                        <p class="text-sky-600">{{ num(ad.clicks) }} clicks</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm font-bold text-slate-400">No active sponsors in this region.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sponsor report modal -->
        <div v-if="showSponsorReport" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3">
            <div class="my-4 w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 bg-linear-to-r from-purple-600 to-fuchsia-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">Sponsor Readership Report</h3>
                        <p class="text-sm text-purple-100">Reach &amp; engagement delivered to a sponsor across regions</p>
                    </div>
                    <button class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20" @click="closeSponsorReport"><X class="h-6 w-6" /></button>
                </div>
                <div class="max-h-[80vh] space-y-5 overflow-y-auto p-6">
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Select Sponsor</span>
                        <select v-model="selectedSponsor" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="sponsor in sponsors" :key="sponsor">{{ sponsor }}</option>
                        </select>
                    </label>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 p-4 text-center"><p class="text-2xl font-black text-slate-800">{{ num(sponsorImpressions) }}</p><p class="mt-1 text-xs font-bold text-slate-500">Impressions</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4 text-center"><p class="text-2xl font-black text-slate-800">{{ num(sponsorClicks) }}</p><p class="mt-1 text-xs font-bold text-slate-500">Clicks</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4 text-center"><p class="text-2xl font-black text-slate-800">{{ sponsorCtr }}%</p><p class="mt-1 text-xs font-bold text-slate-500">CTR</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4 text-center"><p class="text-2xl font-black text-slate-800">{{ num(sponsorReach) }}</p><p class="mt-1 text-xs font-bold text-slate-500">Reader Reach</p></div>
                    </div>

                    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <h4 class="mb-3 font-black">Placements</h4>
                        <div class="space-y-2">
                            <div v-for="ad in sponsorAds" :key="ad.id" class="flex justify-between gap-2 rounded-2xl bg-slate-50 p-3">
                                <div>
                                    <b class="text-sm">{{ ad.title }}</b>
                                    <p class="text-xs text-slate-500">{{ ad.format }} • {{ targetLabel(ad) }} • ${{ num(ad.value) }}</p>
                                </div>
                                <div class="text-right text-xs font-bold">
                                    <p>{{ num(ad.impressions) }} impr</p>
                                    <p class="text-sky-600">{{ num(ad.clicks) }} clicks</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <h4 class="mb-3 font-black">Regions Reached</h4>
                        <div v-if="sponsorRegionsReached.length" class="space-y-2">
                            <div v-for="region in sponsorRegionsReached" :key="region.name" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                <span class="text-sm font-bold">{{ region.name }}</span>
                                <span class="text-xs font-bold text-slate-500">{{ num(region.readers) }} readers • {{ kfmt(region.views) }} views</span>
                            </div>
                        </div>
                        <p v-else class="text-sm font-bold text-slate-400">Targeted across all regions.</p>
                    </div>

                    <p class="text-sm text-slate-400">Total media value delivered: <b>${{ num(sponsorValue) }}</b></p>

                    <div class="flex justify-end">
                        <button class="flex items-center gap-2 rounded-2xl bg-slate-950 px-6 py-3 font-black text-white" @click="exportSponsorReport">
                            <Download class="h-4 w-4" /> Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
