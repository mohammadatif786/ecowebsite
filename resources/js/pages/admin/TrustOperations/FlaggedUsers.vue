<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { Eye, Flag, Globe2, Search, ShieldCheck, TriangleAlert, UserX, X } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type FlaggedStatus = 'Active' | 'Investigating' | 'Resolved';
type Severity = 'High' | 'Medium' | 'Low';

type FlaggedReport = {
    id: string;
    source_id: number;
    reportedBy: string;
    reportedByEmail: string;
    reportedUser: string;
    reportedUserEmail: string;
    country: string;
    message: string;
    category: string;
    severity: Severity;
    status: FlaggedStatus;
    raw_status: string;
    activeUser: boolean;
    reportedAt: string;
    evidence: string;
    action: string;
    notes: string;
    updated_at?: string | null;
};

type AuditEntry = {
    date: string;
    admin: string;
    action: string;
    detail: string;
};

const props = defineProps<{
    initialFlaggedUsers?: FlaggedReport[];
}>();

const statuses: FlaggedStatus[] = ['Active', 'Resolved'];
const severities: Severity[] = ['High', 'Medium', 'Low'];
const reports = computed(() => props.initialFlaggedUsers || []);
const statusFilter = ref('All Statuses');
const severityFilter = ref('All Severities');
const search = ref('');
const activeCase = ref<FlaggedReport | null>(null);
const openRegions = ref<Record<string, boolean>>({});
const openCountries = ref<Record<string, boolean>>({});
const categoryChartRef = ref<HTMLCanvasElement | null>(null);
const severityChartRef = ref<HTMLCanvasElement | null>(null);
let categoryChart: Chart | null = null;
let severityChart: Chart | null = null;

const regionOrder = ['Caribbean', 'Latin America', 'North America', 'Other'];
const countryOrder = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Brazil', 'United States'];
const regionByCountry: Record<string, string> = {
    Bahamas: 'Caribbean',
    Jamaica: 'Caribbean',
    'Trinidad & Tobago': 'Caribbean',
    Brazil: 'Latin America',
    'United States': 'North America',
    Canada: 'North America',
};

const auditTrail = computed<AuditEntry[]>(() => {
    return [...reports.value]
        .filter((report) => report.updated_at || report.reportedAt)
        .sort((a, b) => String(b.updated_at || b.reportedAt).localeCompare(String(a.updated_at || a.reportedAt)))
        .slice(0, 8)
        .map((report) => ({
            date: report.updated_at || report.reportedAt,
            admin: 'Trust & Safety',
            action: `${report.status} ${report.id}`,
            detail: `${report.reportedUser} - ${report.category}`,
        }));
});

const filteredReports = computed(() => {
    const q = search.value.trim().toLowerCase();
    return reports.value.filter((report) => {
        const statusOk = statusFilter.value === 'All Statuses' || report.status === statusFilter.value;
        const severityOk = severityFilter.value === 'All Severities' || report.severity === severityFilter.value;
        const searchOk = !q || [report.id, report.reportedBy, report.reportedUser, report.message, report.category, report.status, report.country, report.reportedUserEmail].join(' ').toLowerCase().includes(q);
        return statusOk && severityOk && searchOk;
    });
});

const groupedReports = computed(() => {
    const groups = filteredReports.value.reduce<Record<string, Record<string, FlaggedReport[]>>>((acc, report) => {
        const region = regionByCountry[report.country] || 'Other';
        acc[region] ||= {};
        acc[region][report.country] ||= [];
        acc[region][report.country].push(report);
        return acc;
    }, {});

    return Object.fromEntries(
        Object.entries(groups)
            .sort(([a], [b]) => regionOrder.indexOf(a) - regionOrder.indexOf(b))
            .map(([region, countries]) => [
                region,
                Object.fromEntries(
                    Object.entries(countries).sort(([a], [b]) => countryOrder.indexOf(a) - countryOrder.indexOf(b)),
                ),
            ]),
    );
});

const totalReports = computed(() => reports.value.length);
const activeReports = computed(() => reports.value.filter((report) => report.status === 'Active' || report.status === 'Investigating').length);
const resolvedReports = computed(() => reports.value.filter((report) => report.status === 'Resolved').length);
const restrictedUsers = computed(() => reports.value.filter((report) => !report.activeUser).length);
const actionQueue = computed(() => reports.value.filter((report) => report.status !== 'Resolved'));

const byCategory = computed(() => {
    const counts = reports.value.reduce<Record<string, number>>((acc, report) => {
        acc[report.category] = (acc[report.category] || 0) + 1;
        return acc;
    }, {});
    return Object.entries(counts).map(([label, count]) => ({ label, count }));
});

const bySeverity = computed(() => severities.map((severity) => ({
    label: severity,
    count: reports.value.filter((report) => report.severity === severity).length,
})));

const destroyCharts = () => {
    categoryChart?.destroy();
    severityChart?.destroy();
    categoryChart = null;
    severityChart = null;
};

const renderCharts = () => {
    destroyCharts();

    if (categoryChartRef.value) {
        categoryChart = new Chart(categoryChartRef.value, {
            type: 'bar',
            data: {
                labels: byCategory.value.map((row) => row.label),
                datasets: [{
                    label: 'Reports',
                    data: byCategory.value.map((row) => row.count),
                    backgroundColor: '#f43f5e',
                    borderRadius: 7,
                    barPercentage: 0.72,
                    categoryPercentage: 0.86,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            color: '#475569',
                            font: { weight: 'bold' },
                        },
                    },
                },
                scales: {
                    x: { grid: { color: '#e8edf5' }, ticks: { color: '#52525b', font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: '#52525b' }, grid: { color: '#e8edf5' } },
                },
            },
        });
    }

    if (severityChartRef.value) {
        severityChart = new Chart(severityChartRef.value, {
            type: 'doughnut',
            data: {
                labels: bySeverity.value.map((row) => row.label),
                datasets: [{
                    data: bySeverity.value.map((row) => row.count),
                    backgroundColor: ['#f43f5e', '#f59e0b', '#00c853'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '48%',
                plugins: { legend: { display: false } },
            },
        });
    }
};

const regionCount = (countries: Record<string, FlaggedReport[]>) => Object.values(countries).reduce((total, items) => total + items.length, 0);

const countryIsOpen = (country: string, count: number) => openCountries.value[country] ?? count <= 1;
const regionIsOpen = (region: string) => openRegions.value[region] ?? true;

const toggleRegion = (region: string) => {
    openRegions.value[region] = !regionIsOpen(region);
};

const toggleCountry = (country: string, count: number) => {
    openCountries.value[country] = !countryIsOpen(country, count);
};

const openCase = (report: FlaggedReport) => {
    activeCase.value = report;
};

const closeCase = () => {
    activeCase.value = null;
};

const setCaseStatus = (status: FlaggedStatus) => {
    if (!activeCase.value) return;
    const report = activeCase.value;
    const payloadStatus = status.toLowerCase().replace(' ', '_');

    router.patch(
        route('admin.trust.flagged-users.status', { flaggedUser: report.source_id }),
        { status: payloadStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`${report.id} updated.`);
                closeCase();
            },
            onError: () => toast.error('Unable to update flagged user case.'),
        },
    );
};

const toggleActive = (report: FlaggedReport, active = !report.activeUser) => {
    router.patch(
        route('admin.trust.flagged-users.activation', { flaggedUser: report.source_id }),
        { active },
        {
            preserveScroll: true,
            onSuccess: () => toast.success(`${report.reportedUser} is now ${active ? 'active' : 'inactive'}.`),
            onError: () => toast.error('Unable to update reported user activation.'),
        },
    );
};

const severityClass = (severity: Severity) => ({
    High: 'bg-rose-50 text-rose-700',
    Medium: 'bg-amber-50 text-amber-700',
    Low: 'bg-green-50 text-green-700',
}[severity]);

const statusClass = (status: FlaggedStatus) => ({
    Resolved: 'bg-green-50 text-green-700',
    Investigating: 'bg-sky-50 text-sky-700',
    Active: 'bg-amber-50 text-amber-700',
}[status]);

onMounted(() => nextTick(renderCharts));
watch(reports, () => nextTick(renderCharts), { deep: true });
onBeforeUnmount(destroyCharts);
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div>
            <h3 class="text-3xl font-black text-slate-950">Flagged Users</h3>
            <p class="mt-1 text-slate-500">Review and manage reported user accounts, inappropriate behavior, scams, harassment, fake profiles, and abuse reports.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-rose-100 text-rose-600"><Flag class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Total Reports</p><h3 class="text-4xl font-black">{{ totalReports }}</h3></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-amber-600"><TriangleAlert class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Active Reports</p><h3 class="text-4xl font-black">{{ activeReports }}</h3></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-green-100 text-green-600"><ShieldCheck class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Resolved</p><h3 class="text-4xl font-black">{{ resolvedReports }}</h3></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-sky-100 text-sky-600"><UserX class="h-7 w-7" /></div>
                <div><p class="font-bold text-slate-500">Restricted Users</p><h3 class="text-4xl font-black">{{ restrictedUsers }}</h3></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <section class="card rounded-3xl p-6 2xl:col-span-2">
                <h3 class="mb-4 text-xl font-black">Reports by Category</h3>
                <div class="h-[320px]"><canvas ref="categoryChartRef"></canvas></div>
            </section>
            <section class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Severity Mix</h3>
                <div class="h-[320px]"><canvas ref="severityChartRef"></canvas></div>
            </section>
        </div>

        <section class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-xl font-black">Reported User Cases</h3>
                    <p class="text-slate-500">Search, review, deactivate, reactivate, resolve, or escalate reported accounts.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select v-model="statusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                        <option>All Statuses</option>
                        <option v-for="status in statuses" :key="status">{{ status }}</option>
                    </select>
                    <select v-model="severityFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                        <option>All Severities</option>
                        <option v-for="severity in severities" :key="severity">{{ severity }}</option>
                    </select>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="search" class="w-full rounded-2xl border border-slate-200 py-2 pl-10 pr-4 xl:w-80" placeholder="Search reported user, reporter, message..." />
                    </div>
                </div>
            </div>

            <div v-if="!filteredReports.length" class="rounded-3xl border border-slate-100 bg-slate-50 p-10 text-center">
                <Flag class="mx-auto h-12 w-12 text-slate-300" />
                <h4 class="mt-4 font-black">No flagged users found</h4>
                <p class="mt-1 text-slate-500">There are no reported users matching this search.</p>
            </div>

            <div v-else class="overflow-x-auto scrollbar">
                <table class="w-full text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3">Reported By</th>
                            <th>Reported User</th>
                            <th>Message</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Reported At</th>
                            <th>Status</th>
                            <th>Activate User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(countries, region) in groupedReports" :key="region">
                            <tr>
                                <td colspan="9" class="border-t-0 p-0">
                                    <button class="flex w-full items-center gap-2 bg-slate-700 px-4 py-3 text-left font-black text-white" @click="toggleRegion(String(region))">
                                        <span class="w-4">{{ regionIsOpen(String(region)) ? 'v' : '>' }}</span>
                                        <span class="grid h-5 w-5 place-items-center rounded-full bg-sky-400 text-white"><Globe2 class="h-3.5 w-3.5" /></span>
                                        <span>{{ region }} <span class="text-slate-300">({{ regionCount(countries) }})</span></span>
                                    </button>
                                </td>
                            </tr>

                            <template v-if="regionIsOpen(String(region))">
                                <template v-for="(items, country) in countries" :key="country">
                                    <tr>
                                        <td colspan="9" class="p-0">
                                            <button class="flex w-full items-center gap-2 bg-slate-100 px-7 py-3 text-left font-black text-slate-800 hover:bg-slate-200" @click="toggleCountry(String(country), items.length)">
                                                <span class="w-4">{{ countryIsOpen(String(country), items.length) ? 'v' : '>' }}</span>
                                                <span>{{ country }} <span class="text-slate-400">({{ items.length }})</span></span>
                                            </button>
                                        </td>
                                    </tr>

                                    <tr v-for="report in items" v-show="countryIsOpen(String(country), items.length)" :key="report.id" class="border-t align-top">
                                        <td class="py-4">
                                            <div class="font-black">{{ report.reportedBy }}</div>
                                            <div class="text-xs text-slate-500">{{ report.reportedByEmail }}</div>
                                        </td>
                                        <td>
                                            <div class="font-black">{{ report.reportedUser }}</div>
                                            <div class="text-xs text-slate-500">{{ report.reportedUserEmail }}<br />{{ report.country }}</div>
                                        </td>
                                        <td class="max-w-md text-slate-600">{{ report.message }}</td>
                                        <td>{{ report.category }}</td>
                                        <td><span class="rounded-full px-3 py-1 text-xs font-black" :class="severityClass(report.severity)">{{ report.severity }}</span></td>
                                        <td class="text-slate-500">{{ report.reportedAt }}</td>
                                        <td><span class="rounded-full px-3 py-1 text-xs font-black" :class="statusClass(report.status)">{{ report.status }}</span></td>
                                        <td><button class="rounded-2xl px-4 py-2 font-bold text-white" :class="report.activeUser ? 'bg-green-600' : 'bg-slate-500'" @click="toggleActive(report)">{{ report.activeUser ? 'Active' : 'Inactive' }}</button></td>
                                        <td><button class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-4 py-2 font-bold text-white" @click="openCase(report)"><Eye class="h-4 w-4" /> Review</button></td>
                                    </tr>
                                </template>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <section class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Trust & Safety Action Queue</h3>
                <div class="space-y-3">
                    <div v-for="report in actionQueue" :key="report.id" class="flex justify-between rounded-2xl bg-slate-50 p-4">
                        <div>
                            <b>{{ report.id }} - {{ report.category }}</b>
                            <p class="text-sm text-slate-600">{{ report.reportedUser }} - {{ report.severity }} severity</p>
                        </div>
                        <span class="font-black">{{ report.action }}</span>
                    </div>
                    <div v-if="!actionQueue.length" class="rounded-2xl bg-green-50 p-4 font-bold">No active trust & safety cases.</div>
                </div>
            </section>
            <section class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Moderation Audit Trail</h3>
                <div class="space-y-3">
                    <div v-for="entry in auditTrail" :key="entry.date + entry.action" class="rounded-2xl bg-slate-50 p-4">
                        <b>{{ entry.action }}</b>
                        <p class="text-sm text-slate-600">{{ entry.date }} - {{ entry.admin }}</p>
                        <p class="text-xs text-slate-500">{{ entry.detail }}</p>
                    </div>
                </div>
            </section>
        </div>

        <div v-if="activeCase" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-5">
            <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="flex justify-between gap-4 border-b border-slate-200 p-8">
                    <div>
                        <h3 class="text-3xl font-black">Flagged User Case</h3>
                        <p class="mt-1 text-lg text-slate-500">{{ activeCase.id }} - {{ activeCase.status }} - {{ activeCase.reportedAt }}</p>
                    </div>
                    <button class="text-slate-500 hover:text-slate-900" @click="closeCase"><X class="h-8 w-8" /></button>
                </div>
                <div class="grid grid-cols-1 gap-5 p-8 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><p class="font-bold uppercase text-slate-500">Reported By</p><h4 class="mt-2 text-xl font-black">{{ activeCase.reportedBy }} - {{ activeCase.reportedByEmail }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><p class="font-bold uppercase text-slate-500">Reported User</p><h4 class="mt-2 text-xl font-black">{{ activeCase.reportedUser }} - {{ activeCase.reportedUserEmail }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><p class="font-bold uppercase text-slate-500">Category / Severity</p><h4 class="mt-2 text-xl font-black">{{ activeCase.category }} - {{ activeCase.severity }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><p class="font-bold uppercase text-slate-500">Evidence</p><h4 class="mt-2 text-xl font-black">{{ activeCase.evidence }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 md:col-span-2"><p class="font-bold uppercase text-slate-500">Message</p><p class="mt-2 text-lg">{{ activeCase.message }}</p></div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 md:col-span-2"><p class="font-black text-amber-900">Moderator Notes</p><p class="mt-1 text-amber-800">{{ activeCase.notes }}</p></div>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 p-6">
                    <button class="rounded-2xl bg-green-600 px-6 py-3 font-black text-white" @click="setCaseStatus('Resolved')">Resolve</button>
                    <button class="rounded-2xl bg-red-600 px-6 py-3 font-black text-white" @click="toggleActive(activeCase, false)">Deactivate User</button>
                    <button class="rounded-2xl bg-sky-600 px-6 py-3 font-black text-white" @click="toggleActive(activeCase, true)">Reactivate User</button>
                    <button class="rounded-2xl bg-slate-200 px-6 py-3 font-black text-slate-800" @click="closeCase">Close</button>
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
