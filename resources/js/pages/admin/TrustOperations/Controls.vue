<script setup lang="ts">
import { computed, ref } from 'vue';
import { Activity, AlertTriangle, CheckCircle2, ClipboardList, Globe2, ShieldCheck } from 'lucide-vue-next';

type Risk = 'High' | 'Medium' | 'Low';
type ApiState = 'Operational' | 'Monitor';

type Role = {
    role: string;
    wallet: string;
    remittance: string;
    settlements: string;
    compliance: string;
    reports: string;
};

type AuditLog = {
    date: string;
    admin: string;
    action: string;
    module: string;
    detail: string;
};

type ComplianceCase = {
    id: string;
    date: string;
    country: string;
    module: string;
    issue: string;
    risk: Risk;
    status: string;
};

type Dispute = {
    id: string;
    date: string;
    country: string;
    module: string;
    customer: string;
    amount: number;
    type: string;
    status: string;
};

type ApiStatus = {
    service: string;
    status: ApiState;
    uptime: string;
    latency: string;
};

const roles: Role[] = [
    { role: 'Super Admin', wallet: 'Full', remittance: 'Full', settlements: 'Full', compliance: 'Full', reports: 'Full' },
    { role: 'Finance Admin', wallet: 'View/Edit', remittance: 'View', settlements: 'Full', compliance: 'View', reports: 'Full' },
    { role: 'Compliance Officer', wallet: 'View', remittance: 'Review', settlements: 'No', compliance: 'Full', reports: 'Compliance' },
    { role: 'Bank Partner Viewer', wallet: 'Summary', remittance: 'Summary', settlements: 'Bank Only', compliance: 'No', reports: 'Partner' },
    { role: 'Merchant Support', wallet: 'Limited', remittance: 'No', settlements: 'Merchant Only', compliance: 'No', reports: 'Merchant' },
];

const auditLogs: AuditLog[] = [
    { date: '2026-06-01 08:01', admin: 'Finance Admin', action: 'Changed event fee setting', module: 'Pricing', detail: 'Bahamas event fee updated to 6.5%' },
    { date: '2026-06-01 09:12', admin: 'Compliance Officer', action: 'Reviewed remittance alert', module: 'Compliance', detail: 'REM-5005 moved to review' },
    { date: '2026-06-01 10:33', admin: 'Settlement Manager', action: 'Approved payout', module: 'Settlements', detail: 'SET-9003 paid to merchant' },
    { date: '2026-06-01 11:49', admin: 'Super Admin', action: 'Created role', module: 'Access Control', detail: 'Bank Partner Viewer role created' },
];

const complianceCases: ComplianceCase[] = [
    { id: 'AML-3001', date: '2026-06-01', country: 'United States', module: 'Remittance', issue: 'Large transfer to Bahamas requires enhanced review', risk: 'High', status: 'Open' },
    { id: 'AML-3002', date: '2026-06-01', country: 'Brazil', module: 'Wallet', issue: 'Cross-border FX transfer marked for review', risk: 'Medium', status: 'Investigating' },
    { id: 'KYC-1209', date: '2026-06-01', country: 'Jamaica', module: 'Merchant Pay', issue: 'Merchant KYC document expiring', risk: 'Medium', status: 'Pending Docs' },
    { id: 'SAN-0440', date: '2026-06-01', country: 'Canada', module: 'Remittance', issue: 'Sanctions screening clear', risk: 'Low', status: 'Closed' },
];

const disputes: Dispute[] = [
    { id: 'DSP-7001', date: '2026-06-01', country: 'Bahamas', module: 'Events', customer: 'Jordan Rolle', amount: 280, type: 'Refund Request', status: 'Open' },
    { id: 'DSP-7002', date: '2026-06-01', country: 'Jamaica', module: 'Merchant Pay', customer: 'Brianna Smith', amount: 890, type: 'Chargeback', status: 'Review' },
    { id: 'DSP-7003', date: '2026-06-01', country: 'United States', module: 'Marketplace', customer: 'Maya Evans', amount: 420, type: 'Product Dispute', status: 'Pending Merchant' },
    { id: 'DSP-7004', date: '2026-06-01', country: 'Trinidad & Tobago', module: 'Wallet', customer: 'Tanya Baptiste', amount: 1500, type: 'Cashout Delay', status: 'Open' },
];

const apiStatus: ApiStatus[] = [
    { service: 'Payment Gateway', status: 'Operational', uptime: '99.98%', latency: '142ms' },
    { service: 'Wallet Ledger', status: 'Operational', uptime: '99.99%', latency: '88ms' },
    { service: 'Remittance Rail', status: 'Monitor', uptime: '99.40%', latency: '310ms' },
    { service: 'KYC Provider', status: 'Operational', uptime: '99.60%', latency: '220ms' },
    { service: 'SMS / WhatsApp', status: 'Operational', uptime: '99.70%', latency: '180ms' },
    { service: 'Live Streaming', status: 'Monitor', uptime: '98.90%', latency: '420ms' },
    { service: 'Reporting Database', status: 'Operational', uptime: '99.95%', latency: '95ms' },
    { service: 'Email Service', status: 'Operational', uptime: '99.80%', latency: '160ms' },
];

const openRegions = ref<Record<string, boolean>>({});
const openCountries = ref<Record<string, boolean>>({});

const regionOrder = ['Caribbean', 'Latin America', 'North America', 'Other'];
const countryOrder = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Brazil', 'Canada', 'United States'];
const regionByCountry: Record<string, string> = {
    Bahamas: 'Caribbean',
    Jamaica: 'Caribbean',
    'Trinidad & Tobago': 'Caribbean',
    Brazil: 'Latin America',
    Canada: 'North America',
    'United States': 'North America',
};

const formatMoney = (value: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);

const groupedComplianceCases = computed(() => {
    const groups = complianceCases.reduce<Record<string, Record<string, ComplianceCase[]>>>((acc, item) => {
        const region = regionByCountry[item.country] || 'Other';
        acc[region] ||= {};
        acc[region][item.country] ||= [];
        acc[region][item.country].push(item);
        return acc;
    }, {});

    return Object.fromEntries(
        Object.entries(groups)
            .sort(([a], [b]) => regionOrder.indexOf(a) - regionOrder.indexOf(b))
            .map(([region, countries]) => [
                region,
                Object.fromEntries(Object.entries(countries).sort(([a], [b]) => countryOrder.indexOf(a) - countryOrder.indexOf(b))),
            ]),
    );
});

const regionCount = (countries: Record<string, ComplianceCase[]>) => Object.values(countries).reduce((total, items) => total + items.length, 0);
const regionIsOpen = (region: string) => openRegions.value[region] ?? true;
const countryIsOpen = (country: string, count: number) => openCountries.value[country] ?? count <= 1;

const toggleRegion = (region: string) => {
    openRegions.value[region] = !regionIsOpen(region);
};

const toggleCountry = (country: string, count: number) => {
    openCountries.value[country] = !countryIsOpen(country, count);
};

const accessClass = (value: string) => {
    if (value === 'Full') return 'bg-green-50 text-green-700';
    if (value === 'No') return 'bg-slate-100 text-slate-500';
    if (value.includes('Review') || value.includes('Edit')) return 'bg-sky-50 text-sky-700';
    return 'bg-amber-50 text-amber-700';
};

const riskClass = (risk: Risk) => ({
    High: 'bg-rose-50 border-rose-100 text-rose-700',
    Medium: 'bg-amber-50 border-amber-100 text-amber-700',
    Low: 'bg-green-50 border-green-100 text-green-700',
}[risk]);

const disputeStatusClass = (status: string) => {
    if (status === 'Open') return 'text-rose-600';
    if (status === 'Review' || status === 'Pending Merchant') return 'text-amber-600';
    return 'text-slate-500';
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Controls</h3>
            <p class="mt-1 text-slate-500">Access control, audit evidence, compliance cases, disputes, and platform service health.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <section class="card rounded-3xl p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-950 text-white"><ShieldCheck class="h-5 w-5" /></div>
                    <h3 class="text-xl font-black">Admin Roles & Permissions</h3>
                </div>
                <div class="overflow-x-auto scrollbar">
                    <table class="w-full text-left">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Role</th>
                                <th>Wallet</th>
                                <th>Remittance</th>
                                <th>Settlements</th>
                                <th>Compliance</th>
                                <th>Reports</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles" :key="role.role" class="border-t">
                                <td class="py-3 font-black">{{ role.role }}</td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-black" :class="accessClass(role.wallet)">{{ role.wallet }}</span></td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-black" :class="accessClass(role.remittance)">{{ role.remittance }}</span></td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-black" :class="accessClass(role.settlements)">{{ role.settlements }}</span></td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-black" :class="accessClass(role.compliance)">{{ role.compliance }}</span></td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-black" :class="accessClass(role.reports)">{{ role.reports }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card rounded-3xl p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-sky-100 text-sky-700"><ClipboardList class="h-5 w-5" /></div>
                    <h3 class="text-xl font-black">Audit Trail</h3>
                </div>
                <div class="space-y-3">
                    <div v-for="item in auditLogs" :key="item.date + item.action" class="rounded-2xl bg-slate-50 p-4">
                        <b>{{ item.action }}</b>
                        <p class="text-sm text-slate-600">{{ item.date }} - {{ item.admin }} - {{ item.module }}</p>
                        <p class="text-xs text-slate-500">{{ item.detail }}</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <section class="card rounded-3xl p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-100 text-amber-700"><AlertTriangle class="h-5 w-5" /></div>
                    <h3 class="text-xl font-black">Compliance Case Management</h3>
                </div>
                <div class="space-y-2">
                    <template v-for="(countries, region) in groupedComplianceCases" :key="region">
                        <button class="flex w-full items-center gap-2 rounded-t-2xl bg-slate-700 px-4 py-3 text-left font-black text-white" @click="toggleRegion(String(region))">
                            <span class="w-4">{{ regionIsOpen(String(region)) ? 'v' : '>' }}</span>
                            <span class="grid h-5 w-5 place-items-center rounded-full bg-sky-400 text-white"><Globe2 class="h-3.5 w-3.5" /></span>
                            <span>{{ region }} <span class="text-slate-300">({{ regionCount(countries) }})</span></span>
                        </button>

                        <template v-if="regionIsOpen(String(region))">
                            <template v-for="(items, country) in countries" :key="country">
                                <button class="flex w-full items-center gap-2 bg-slate-100 px-7 py-3 text-left font-black text-slate-800 hover:bg-slate-200" @click="toggleCountry(String(country), items.length)">
                                    <span class="w-4">{{ countryIsOpen(String(country), items.length) ? 'v' : '>' }}</span>
                                    <span>{{ country }} <span class="text-slate-400">({{ items.length }})</span></span>
                                </button>
                                <div v-show="countryIsOpen(String(country), items.length)" class="space-y-2 bg-white py-2">
                                    <div v-for="item in items" :key="item.id" class="rounded-2xl border p-4" :class="riskClass(item.risk)">
                                        <b>{{ item.id }} - {{ item.risk }}</b>
                                        <p class="text-sm text-slate-600">{{ item.module }} - {{ item.country }} - {{ item.status }}</p>
                                        <p class="text-xs text-slate-500">{{ item.issue }}</p>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </template>
                </div>
            </section>

            <section class="card rounded-3xl p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-purple-100 text-purple-700"><Activity class="h-5 w-5" /></div>
                    <h3 class="text-xl font-black">Refunds & Disputes</h3>
                </div>
                <div class="space-y-3">
                    <div v-for="item in disputes" :key="item.id" class="flex justify-between rounded-2xl bg-slate-50 p-4">
                        <div>
                            <b>{{ item.id }} - {{ item.type }}</b>
                            <p class="text-sm text-slate-600">{{ item.customer }} - {{ item.module }} - {{ item.country }}</p>
                            <p class="text-xs text-slate-400">{{ item.date }}</p>
                        </div>
                        <div class="text-right">
                            <b>{{ formatMoney(item.amount) }}</b>
                            <p class="text-xs font-black" :class="disputeStatusClass(item.status)">{{ item.status }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="card rounded-3xl p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-2xl bg-green-100 text-green-700"><CheckCircle2 class="h-5 w-5" /></div>
                <h3 class="text-xl font-black">Real API Status</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="service in apiStatus"
                    :key="service.service"
                    class="rounded-2xl border p-4"
                    :class="service.status === 'Operational' ? 'border-green-100 bg-green-50' : 'border-amber-100 bg-amber-50'"
                >
                    <b class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full" :class="service.status === 'Operational' ? 'bg-green-500' : 'bg-amber-500'"></span>
                        {{ service.service }}
                    </b>
                    <p class="text-sm text-slate-600">Uptime: {{ service.uptime }}</p>
                    <p class="text-xs text-slate-500">Latency: {{ service.latency }}</p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
