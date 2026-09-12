<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { BadgeCheck, Eye, FileText, IdCard, Search, ShieldAlert, UserRound, X, ZoomIn } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type KycStatus = 'Verified' | 'Pending Review' | 'Rejected';
type DocumentStatus = KycStatus | 'N/A';
type RiskLevel = 'Low' | 'Medium' | 'High';
type ReviewStatus = 'approved' | 'pending' | 'rejected' | 'na';

type KycDocument = {
    key: string;
    label: string;
    status: DocumentStatus;
    raw_status: string;
    url?: string | null;
};

type KycRecord = {
    id: string;
    source: 'wallet' | 'organizer' | 'user';
    source_id: number;
    profileType: string;
    name: string;
    email: string;
    country: string;
    submitted: string;
    status: KycStatus;
    raw_status: string;
    risk: RiskLevel;
    notes: string;
    documents: KycDocument[];
    updated_at?: string | null;
};

const props = defineProps<{
    initialKycReviews?: KycRecord[];
}>();

const records = computed(() => props.initialKycReviews || []);
const typeFilter = ref('All Types');
const statusFilter = ref('All Statuses');
const search = ref('');
const typeChartRef = ref<HTMLCanvasElement | null>(null);
const statusChartRef = ref<HTMLCanvasElement | null>(null);
const selectedImage = ref<string | null>(null);
const activeRecord = ref<KycRecord | null>(null);
const openGroups = ref<Record<string, boolean>>({});
const brokenDocumentImages = ref<Record<string, boolean>>({});
let typeChart: Chart | null = null;
let statusChart: Chart | null = null;

const regionByCountry: Record<string, string> = {
    Bahamas: 'Caribbean',
    Jamaica: 'Caribbean',
    'Trinidad & Tobago': 'Caribbean',
    Barbados: 'Caribbean',
    Guyana: 'Caribbean',
    'Dominican Republic': 'Caribbean',
    Colombia: 'Latin America',
    Brazil: 'Latin America',
    Mexico: 'Latin America',
    'United States': 'North America',
    Canada: 'North America',
};

const profileTypes = computed(() => [...new Set(records.value.map((record) => record.profileType))].sort());
const statuses: KycStatus[] = ['Verified', 'Pending Review', 'Rejected'];

const filteredRecords = computed(() => {
    const q = search.value.trim().toLowerCase();
    return records.value.filter((record) => {
        const typeOk = typeFilter.value === 'All Types' || record.profileType === typeFilter.value;
        const statusOk = statusFilter.value === 'All Statuses' || record.status === statusFilter.value;
        const searchOk = !q || [record.id, record.profileType, record.name, record.email, record.country, record.status, record.risk, record.notes].join(' ').toLowerCase().includes(q);
        return typeOk && statusOk && searchOk;
    });
});

const groupedRecords = computed(() => {
    return filteredRecords.value.reduce<Record<string, Record<string, KycRecord[]>>>((groups, record) => {
        const region = regionByCountry[record.country] || 'Other';
        groups[region] ||= {};
        groups[region][record.country] ||= [];
        groups[region][record.country].push(record);
        return groups;
    }, {});
});

const totalProfiles = computed(() => records.value.length);
const pendingProfiles = computed(() => records.value.filter((record) => record.status === 'Pending Review').length);
const verifiedProfiles = computed(() => records.value.filter((record) => record.status === 'Verified').length);
const rejectedProfiles = computed(() => records.value.filter((record) => record.status === 'Rejected' || record.risk === 'High').length);
const actionQueue = computed(() => records.value.filter((record) => record.status !== 'Verified'));

const byType = computed(() => {
    return profileTypes.value.map((type) => ({
        label: type,
        count: records.value.filter((record) => record.profileType === type).length,
    }));
});

const statusMix = computed(() => [
    { label: 'Verified', count: verifiedProfiles.value },
    { label: 'Pending Review', count: pendingProfiles.value },
    { label: 'Rejected / High Risk', count: rejectedProfiles.value },
]);

const recentAudit = computed(() => {
    return [...records.value]
        .filter((record) => record.updated_at)
        .sort((a, b) => String(b.updated_at).localeCompare(String(a.updated_at)))
        .slice(0, 8);
});

const destroyCharts = () => {
    typeChart?.destroy();
    statusChart?.destroy();
    typeChart = null;
    statusChart = null;
};

const renderCharts = () => {
    destroyCharts();

    if (typeChartRef.value) {
        typeChart = new Chart(typeChartRef.value, {
            type: 'bar',
            data: {
                labels: byType.value.map((row) => row.label),
                datasets: [
                    {
                        label: 'Profiles',
                        data: byType.value.map((row) => row.count),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 7,
                        barPercentage: 0.72,
                        categoryPercentage: 0.86,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, color: '#475569', font: { weight: 'bold' } },
                    },
                },
                scales: {
                    x: { grid: { color: '#e8edf5' }, ticks: { color: '#52525b', font: { size: 12 } } },
                    y: { beginAtZero: true, suggestedMax: Math.max(2, ...byType.value.map((row) => row.count)), ticks: { stepSize: 1, color: '#52525b' }, grid: { color: '#e8edf5' } },
                },
            },
        });
    }

    if (statusChartRef.value) {
        statusChart = new Chart(statusChartRef.value, {
            type: 'doughnut',
            data: {
                labels: statusMix.value.map((row) => row.label),
                datasets: [{ data: statusMix.value.map((row) => row.count), backgroundColor: ['#00c853', '#f59e0b', '#f43f5e'], borderColor: '#ffffff', borderWidth: 3, hoverOffset: 6 }],
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '48%', plugins: { legend: { display: false } } },
        });
    }
};

const toggleGroup = (key: string) => {
    openGroups.value[key] = !(openGroups.value[key] ?? key.startsWith('region:'));
};

const groupOpen = (key: string) => openGroups.value[key] ?? key.startsWith('region:');

const statusClass = (status: KycStatus) => ({
    Verified: 'bg-green-50 text-green-700',
    'Pending Review': 'bg-amber-50 text-amber-700',
    Rejected: 'bg-rose-50 text-rose-700',
}[status]);

const docClass = (status: DocumentStatus) => ({
    Verified: 'text-green-600',
    'Pending Review': 'text-amber-600',
    Rejected: 'text-rose-600',
    'N/A': 'text-slate-400',
}[status]);

const riskClass = (risk: RiskLevel) => ({
    Low: 'text-green-600',
    Medium: 'text-amber-600',
    High: 'text-rose-600',
}[risk]);

const toRawStatus = (status: KycStatus): ReviewStatus => {
    if (status === 'Verified') return 'approved';
    if (status === 'Rejected') return 'rejected';
    return 'pending';
};

const normalizeDocumentStatus = (status: string): ReviewStatus => {
    if (status === 'approved') return 'approved';
    if (status === 'rejected' || status === 'canceled') return 'rejected';
    if (status === 'na' || status === 'n/a' || status === 'not_applicable') return 'na';
    return 'pending';
};

const documentStatusLabel = (status: ReviewStatus) => ({
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Reject',
    na: 'N/A',
}[status]);

const documentImageKey = (record: KycRecord, doc: KycDocument) => `${record.source}:${record.source_id}:${doc.key}`;

const documentCanPreview = (record: KycRecord, doc: KycDocument) => {
    if (!doc.url) return false;
    if (brokenDocumentImages.value[documentImageKey(record, doc)]) return false;
    return !/\.pdf($|\?)/i.test(doc.url);
};

const markDocumentImageBroken = (record: KycRecord, doc: KycDocument) => {
    brokenDocumentImages.value[documentImageKey(record, doc)] = true;
};

const setStatus = (record: KycRecord, status: KycStatus) => {
    router.patch(
        route('admin.trust.kyc.status', { source: record.source, id: record.source_id }),
        { status: toRawStatus(status) },
        {
            preserveScroll: true,
            onSuccess: () => toast.success(`${record.name} marked ${status}.`),
            onError: () => toast.error('Unable to update KYC status.'),
        },
    );
};

const setDocumentStatus = (record: KycRecord, doc: KycDocument, status: ReviewStatus) => {
    router.patch(
        route('admin.trust.kyc.documents.status', { source: record.source, id: record.source_id, document: doc.key }),
        { status },
        {
            preserveScroll: true,
            onSuccess: () => toast.success(`${doc.label} marked ${documentStatusLabel(status)}.`),
            onError: () => toast.error('Unable to update document status.'),
        },
    );
};

onMounted(() => nextTick(renderCharts));
watch(records, () => nextTick(renderCharts), { deep: true });
onBeforeUnmount(destroyCharts);
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div>
            <h3 class="text-3xl font-black text-slate-950">KYC Review Center</h3>
            <p class="mt-1 text-slate-500">Live KYC submissions from user profiles, wallet KYC, and organizer verification records.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-purple-100 text-purple-600"><UserRound class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Total Profiles</p><h3 class="text-4xl font-black">{{ totalProfiles }}</h3></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-amber-600"><ShieldAlert class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Pending Review</p><h3 class="text-4xl font-black">{{ pendingProfiles }}</h3></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-green-100 text-green-600"><BadgeCheck class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Verified</p><h3 class="text-4xl font-black">{{ verifiedProfiles }}</h3></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-rose-100 text-rose-600"><X class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Rejected / High Risk</p><h3 class="text-4xl font-black">{{ rejectedProfiles }}</h3></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <section class="card rounded-3xl p-6 2xl:col-span-2">
                <h3 class="mb-4 text-xl font-black">KYC by Profile Type</h3>
                <div class="h-[320px]"><canvas ref="typeChartRef"></canvas></div>
            </section>
            <section class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">KYC Status Mix</h3>
                <div class="h-[320px]"><canvas ref="statusChartRef"></canvas></div>
            </section>
        </div>

        <section class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-xl font-black">Submitted KYC Documents</h3>
                    <p class="text-slate-500">Approve or reject identity, address, wallet, and organizer KYC submissions from the database.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select v-model="typeFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                        <option>All Types</option>
                        <option v-for="type in profileTypes" :key="type">{{ type }}</option>
                    </select>
                    <select v-model="statusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                        <option>All Statuses</option>
                        <option v-for="status in statuses" :key="status">{{ status }}</option>
                    </select>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="search" class="w-full rounded-2xl border border-slate-200 py-2 pl-10 pr-4 xl:w-80" placeholder="Search name, email, country..." />
                    </div>
                </div>
            </div>

            <div v-if="!filteredRecords.length" class="rounded-2xl bg-slate-50 p-6 text-center font-bold text-slate-400">No KYC records found.</div>
            <div v-else class="space-y-3">
                <div v-for="(countries, region) in groupedRecords" :key="region" class="overflow-hidden rounded-2xl border border-slate-100">
                    <button class="flex w-full items-center justify-between bg-gradient-to-r from-slate-800 to-slate-600 px-4 py-3 font-black text-white" @click="toggleGroup(`region:${region}`)">
                        <span>{{ groupOpen(`region:${region}`) ? 'v' : '>' }} {{ region }}</span>
                        <span class="opacity-60">{{ Object.values(countries).flat().length }}</span>
                    </button>
                    <div v-show="groupOpen(`region:${region}`)" class="space-y-2 p-2">
                        <div v-for="(items, country) in countries" :key="country" class="overflow-hidden rounded-2xl border border-slate-100">
                            <button class="flex w-full items-center justify-between bg-slate-100 px-4 py-2.5 font-black text-slate-700" @click="toggleGroup(`country:${country}`)">
                                <span>{{ groupOpen(`country:${country}`) ? 'v' : '>' }} {{ country }}</span>
                                <span class="opacity-50">{{ items.length }}</span>
                            </button>
                            <div v-show="groupOpen(`country:${country}`)" class="space-y-3 p-3">
                                <article v-for="record in items" :key="record.id" class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                                    <div class="flex flex-col gap-4 p-5 xl:flex-row xl:items-center xl:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100">
                                                <IdCard class="h-7 w-7 text-slate-600" />
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-black">{{ record.name }}</h4>
                                                <p class="text-slate-500">{{ record.email }}</p>
                                                <p class="text-xs text-slate-400">{{ record.id }} - {{ record.profileType }} - {{ record.submitted }}</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-3 py-1 text-xs font-black" :class="statusClass(record.status)">{{ record.status }}</span>
                                            <span class="text-xs font-black" :class="riskClass(record.risk)">{{ record.risk }} risk</span>
                                            <button class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700" @click="activeRecord = record">
                                                <Eye class="h-3 w-3" /> View
                                            </button>
                                            <button v-if="record.status !== 'Verified'" class="rounded-xl bg-green-600 px-3 py-2 text-xs font-black text-white" @click="setStatus(record, 'Verified')">Verify</button>
                                            <button v-if="record.status !== 'Rejected'" class="rounded-xl bg-red-600 px-3 py-2 text-xs font-black text-white" @click="setStatus(record, 'Rejected')">Reject</button>
                                            <button v-if="record.status !== 'Pending Review'" class="rounded-xl bg-amber-100 px-3 py-2 text-xs font-black text-amber-700" @click="setStatus(record, 'Pending Review')">Pending</button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 border-t border-slate-100 p-5 md:grid-cols-3 xl:grid-cols-5">
                                        <div v-for="doc in record.documents" :key="doc.label" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="mb-3 flex items-center justify-between gap-2">
                                                <p class="text-xs font-bold uppercase text-slate-500">{{ doc.label }}</p>
                                                <span class="text-xs font-black" :class="docClass(doc.status)">{{ doc.status }}</span>
                                            </div>
                                            <button v-if="doc.url" class="group relative grid h-24 w-full place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white" @click="selectedImage = doc.url">
                                                <img
                                                    v-if="documentCanPreview(record, doc)"
                                                    :src="doc.url"
                                                    :alt="doc.label"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                    @error="markDocumentImageBroken(record, doc)"
                                                />
                                                <FileText v-else class="h-8 w-8 text-slate-300" />
                                                <span class="absolute inset-0 grid place-items-center bg-slate-950/0 opacity-0 transition group-hover:bg-slate-950/25 group-hover:opacity-100">
                                                    <ZoomIn class="h-8 w-8 text-white" />
                                                </span>
                                            </button>
                                            <div v-else class="grid h-24 place-items-center rounded-xl border border-dashed border-slate-200 bg-white">
                                                <FileText class="h-8 w-8 text-slate-300" />
                                            </div>
                                            <select
                                                class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700"
                                                :value="normalizeDocumentStatus(doc.raw_status)"
                                                @change="setDocumentStatus(record, doc, ($event.target as HTMLSelectElement).value as ReviewStatus)"
                                            >
                                                <option value="pending">Pending</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Reject</option>
                                                <option value="na">N/A</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="px-5 pb-5">
                                        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4"><b>Notes:</b> {{ record.notes }}</div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <section class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">KYC Action Queue</h3>
                <div class="space-y-3">
                    <div v-for="record in actionQueue" :key="record.id" class="flex justify-between rounded-2xl bg-slate-50 p-4">
                        <div>
                            <b>{{ record.id }} - {{ record.profileType }}</b>
                            <p class="text-sm text-slate-600">{{ record.name }} - {{ record.risk }} risk</p>
                        </div>
                        <span class="font-black">{{ record.status }}</span>
                    </div>
                    <div v-if="!actionQueue.length" class="rounded-2xl bg-green-50 p-4 font-bold">No pending KYC records.</div>
                </div>
            </section>
            <section class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Recent KYC Updates</h3>
                <div class="space-y-3">
                    <div v-for="record in recentAudit" :key="record.id + record.updated_at" class="rounded-2xl bg-slate-50 p-4">
                        <b>{{ record.id }} - {{ record.status }}</b>
                        <p class="text-sm text-slate-600">{{ record.updated_at }} - {{ record.name }}</p>
                        <p class="text-xs text-slate-500">{{ record.notes }}</p>
                    </div>
                    <div v-if="!recentAudit.length" class="rounded-2xl bg-slate-50 p-4 font-bold text-slate-400">No update history available.</div>
                </div>
            </section>
        </div>

        <div v-if="selectedImage" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 p-5" @click="selectedImage = null">
            <button class="absolute right-6 top-6 text-white" @click="selectedImage = null"><X class="h-8 w-8" /></button>
            <img :src="selectedImage" class="max-h-[88vh] max-w-[92vw] rounded-2xl bg-white object-contain" alt="KYC document" @click.stop />
        </div>

        <div v-if="activeRecord" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/50 p-5">
            <div class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between bg-gradient-to-r from-purple-600 to-fuchsia-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">{{ activeRecord.name }}</h3>
                        <p class="text-sm text-purple-100">{{ activeRecord.id }} - {{ activeRecord.profileType }} - {{ activeRecord.country }}</p>
                    </div>
                    <button @click="activeRecord = null"><X class="h-7 w-7" /></button>
                </div>
                <div class="space-y-4 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-black uppercase text-slate-400">Status</p><b>{{ activeRecord.status }}</b></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-black uppercase text-slate-400">Risk</p><b :class="riskClass(activeRecord.risk)">{{ activeRecord.risk }}</b></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-black uppercase text-slate-400">Submitted</p><b>{{ activeRecord.submitted }}</b></div>
                    </div>
                    <p class="rounded-2xl bg-slate-50 p-4 text-slate-700">{{ activeRecord.notes }}</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button class="rounded-2xl bg-slate-100 px-5 py-2.5 font-black" @click="activeRecord = null">Close</button>
                        <button v-if="activeRecord.status !== 'Verified'" class="rounded-2xl bg-green-600 px-5 py-2.5 font-black text-white" @click="setStatus(activeRecord, 'Verified'); activeRecord = null">Verify</button>
                        <button v-if="activeRecord.status !== 'Rejected'" class="rounded-2xl bg-rose-600 px-5 py-2.5 font-black text-white" @click="setStatus(activeRecord, 'Rejected'); activeRecord = null">Reject</button>
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
</style>
