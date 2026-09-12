<script setup lang="ts">
import { computed, ref, watch } from 'vue';

type ReconItem = {
    id: string;
    date: string;
    country: string;
    type: string;
    ref: string;
    amount: number;
    status: string;
    detail: string;
};

type RiskRule = {
    id: string;
    name: string;
    tmpl: string;
    value: number;
    enabled: boolean;
};

const props = withDefaults(defineProps<{
    reconItems?: ReconItem[] | null;
    riskRules?: RiskRule[] | null;
    complianceCases?: any[] | null;
    fmt?: (n: number) => string;
    filters?: any;
    countries?: any[] | null;
}>(), {
    reconItems: () => [],
    riskRules: () => [],
    complianceCases: () => [],
    countries: () => [],
    filters: () => ({ region: 'All', country: 'All Countries' }),
});

const defaultReconItems: ReconItem[] = [
    { id: 'REC-5001', date: '2026-06-05', country: 'Bahamas', type: 'Bank File Delay', ref: 'ST-1002', amount: 0, status: 'Open', detail: 'Scotiabank batch file pending for merchant settlement ST-1002.' },
    { id: 'REC-5002', date: '2026-06-05', country: 'Brazil', type: 'Amount Mismatch', ref: 'WLT-9006', amount: 420, status: 'Open', detail: 'Cross-border transfer: ledger vs bank file differ by $420.' },
    { id: 'REC-5003', date: '2026-06-04', country: 'Jamaica', type: 'Unmatched Credit', ref: 'WLT-9011', amount: 180, status: 'Investigating', detail: 'Inbound credit not matched to a wallet load.' },
    { id: 'REC-5004', date: '2026-06-04', country: 'United States', type: 'Fee Variance', ref: 'SET-9003', amount: 65, status: 'Open', detail: 'Processor fee differs from computed pool by $65.' },
    { id: 'REC-5005', date: '2026-06-03', country: 'Trinidad and Tobago', type: 'Duplicate Entry', ref: 'WLT-9020', amount: 300, status: 'Open', detail: 'Possible duplicate cash-out posting under review.' },
    { id: 'REC-5006', date: '2026-06-03', country: 'Mexico', type: 'FX Rounding', ref: 'WLT-9025', amount: 12, status: 'Open', detail: 'FX rounding drift on MXN settlement.' },
    { id: 'REC-5007', date: '2026-06-02', country: 'Dominican Republic', type: 'Reserve Check', ref: 'RSV-DR', amount: 0, status: 'Matched', detail: 'Reserve coverage 112% - above threshold.' },
    { id: 'REC-5008', date: '2026-06-02', country: 'Colombia', type: 'Amount Mismatch', ref: 'WLT-9031', amount: 90, status: 'Matched', detail: 'Resolved: timing difference cleared next day.' },
];

const defaultRiskRules: RiskRule[] = [
    { id: 'velocity', name: 'Velocity Rule', tmpl: 'More than {v} transfers in 30 minutes triggers Review.', value: 5, enabled: true },
    { id: 'largeTxn', name: 'Large Transaction Rule', tmpl: 'Single wallet movement above ${v} requires enhanced review.', value: 2000, enabled: true },
    { id: 'asu', name: 'ASU Limit Rule', tmpl: 'Contribution above ${v}/month creates a compliance case.', value: 6000, enabled: true },
    { id: 'newAcct', name: 'New-Account Cash-Out', tmpl: 'Cash-out within {v} days of signup is held for review.', value: 3, enabled: true },
    { id: 'crossBorder', name: 'Cross-Border Rule', tmpl: 'Cross-border transfer above ${v} flagged for AML.', value: 5000, enabled: false },
];

const mergeById = <T extends { id: string }>(incoming: T[] | null | undefined, defaults: T[]): T[] => {
    const rows = Array.isArray(incoming) ? incoming : [];
    const map = new Map<string, T>();
    [...defaults, ...rows].forEach((row) => map.set(row.id, { ...row }));
    return [...map.values()];
};

const reconRows = ref<ReconItem[]>(mergeById(props.reconItems, defaultReconItems));
const rules = ref<RiskRule[]>(mergeById(props.riskRules, defaultRiskRules));

watch(() => props.reconItems, (items) => {
    reconRows.value = mergeById(items, defaultReconItems);
});

watch(() => props.riskRules, (items) => {
    rules.value = mergeById(items, defaultRiskRules);
});

const countries = computed(() => Array.isArray(props.countries) ? props.countries : []);
const complianceCases = computed(() => Array.isArray(props.complianceCases) ? props.complianceCases : []);
const activeFilters = computed(() => ({
    region: 'All',
    country: 'All Countries',
    ...(props.filters || {}),
}));

const money = (n: number) => props.fmt ? props.fmt(n) : `$${Number(n || 0).toLocaleString()}`;

const countryRegion = (countryName: string) => {
    const country = countries.value.find((x) => x.country === countryName || x.name === countryName);
    return country?.region || country?.subregion || 'Other';
};

const baseItems = computed(() => reconRows.value.filter((r) => {
    const region = countryRegion(r.country);
    const okRegion = activeFilters.value.region === 'All' || region === activeFilters.value.region;
    const okCountry = activeFilters.value.country === 'All Countries' || r.country === activeFilters.value.country;
    return okRegion && okCountry;
}));

const stats = computed(() => {
    const list = baseItems.value;
    const openItems = list.filter((r) => r.status === 'Open' || r.status === 'Investigating');
    const matched = list.filter((r) => r.status === 'Matched' || r.status === 'Adjusted');
    const unmatchedVal = openItems.reduce((a, r) => a + (Number(r.amount) || 0), 0);
    const matchRate = list.length ? Math.round((matched.length / list.length) * 1000) / 10 : 0;
    const amlAlerts = complianceCases.value.filter((c) => c.status === 'Open' || c.status === 'Investigating').length;

    return { openItems: openItems.length, matchRate, amlAlerts, unmatchedVal };
});

const setStatus = (id: string, status: string) => {
    const item = reconRows.value.find((x) => x.id === id);
    if (!item) return;

    item.status = status;

    if (status === 'Escalated' && Array.isArray(props.complianceCases)) {
        props.complianceCases.unshift({
            id: `AML-${Math.floor(3100 + Math.random() * 800)}`,
            date: new Date().toISOString().slice(0, 10),
            user: item.ref,
            country: item.country,
            type: 'Recon Escalation',
            amount: item.amount,
            risk: 'Medium',
            status: 'Open',
            reason: `Escalated from reconciliation: ${item.detail}`,
        });
    }
};

const groupOpen = ref<Record<string, boolean>>({});
const toggleGroup = (key: string) => {
    if (groupOpen.value[key] === undefined) groupOpen.value[key] = key.includes(':R:');
    groupOpen.value[key] = !groupOpen.value[key];
};
const isGroupOpen = (key: string) => groupOpen.value[key] === undefined ? key.includes(':R:') : groupOpen.value[key];

const groupedItems = computed(() => {
    const groups: Record<string, Record<string, ReconItem[]>> = {};
    baseItems.value.forEach((item) => {
        const country = item.country || 'Other';
        const region = countryRegion(country);
        if (!groups[region]) groups[region] = {};
        if (!groups[region][country]) groups[region][country] = [];
        groups[region][country].push(item);
    });

    return Object.keys(groups).sort().reduce((carry, region) => {
        carry[region] = Object.keys(groups[region])
            .sort((a, b) => groups[region][b].length - groups[region][a].length || a.localeCompare(b))
            .reduce((countryCarry, country) => {
                countryCarry[country] = groups[region][country];
                return countryCarry;
            }, {} as Record<string, ReconItem[]>);
        return carry;
    }, {} as Record<string, Record<string, ReconItem[]>>);
});

const showRulesNote = ref(false);
const saveRules = () => {
    showRulesNote.value = true;
    setTimeout(() => (showRulesNote.value = false), 2500);
};
const toggleRule = (rule: RiskRule) => {
    rule.enabled = !rule.enabled;
};
const ruleParts = (tmpl: string) => {
    const [before, after = ''] = tmpl.split('{v}');
    return { before, after };
};
const getStatusClass = (status: string) => ({
    Open: 'bg-rose-50 text-rose-700',
    Investigating: 'bg-amber-50 text-amber-700',
    Matched: 'bg-green-50 text-green-700',
    Adjusted: 'bg-sky-50 text-sky-700',
    Escalated: 'bg-purple-50 text-purple-700',
}[status] || 'bg-slate-50 text-slate-600');
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Risk & Reconciliation</h3>
            <p class="text-slate-500">Daily wallet-to-bank matching, unresolved differences, risk rules, and escalation to compliance. Period &amp; region reactive.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Open Reconciliation Items</p><h3 class="text-4xl font-black text-rose-600">{{ stats.openItems }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Match Rate</p><h3 class="text-4xl font-black text-green-600">{{ stats.matchRate }}%</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">AML Alerts</p><h3 class="text-4xl font-black text-amber-600">{{ stats.amlAlerts }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Unmatched Value</p><h3 class="text-4xl font-black text-rose-600">{{ money(stats.unmatchedVal) }}</h3></div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <div class="card rounded-3xl p-6 2xl:col-span-2">
                <h3 class="mb-4 text-xl font-black">Reconciliation Items</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Item</th><th>Type</th><th>Ref</th><th>Variance</th><th>Status</th><th>Detail</th><th>Action</th></tr></thead>
                        <tbody>
                            <tr v-if="Object.keys(groupedItems).length === 0"><td colspan="7" class="py-6 text-center font-bold text-slate-400">No records.</td></tr>
                            <template v-for="(countriesInRegion, region) in groupedItems" v-else :key="region">
                                <tr class="cursor-pointer" @click="toggleGroup('rr:R:' + region)"><td colspan="7" class="bg-gradient-to-r from-slate-800 to-slate-600 px-3 py-2 font-black text-white">{{ isGroupOpen('rr:R:' + region) ? 'v' : '>' }} {{ region }} <span class="opacity-60">({{ Object.values(countriesInRegion).flat().length }})</span></td></tr>
                                <template v-if="isGroupOpen('rr:R:' + region)">
                                    <template v-for="(items, country) in countriesInRegion" :key="country">
                                        <tr class="cursor-pointer" @click="toggleGroup('rr:C:' + country)"><td colspan="7" class="bg-slate-100 px-6 py-2 font-black text-slate-700">{{ isGroupOpen('rr:C:' + country) ? 'v' : '>' }} {{ country }} <span class="opacity-50">({{ items.length }})</span></td></tr>
                                        <template v-if="isGroupOpen('rr:C:' + country)">
                                            <tr v-for="r in items" :key="r.id" class="border-t align-top">
                                                <td class="py-3 font-black">{{ r.id }}<div class="text-xs font-normal text-slate-400">{{ r.date }}</div></td>
                                                <td>{{ r.type }}</td>
                                                <td class="text-slate-500">{{ r.ref }}</td>
                                                <td class="font-black">{{ r.amount ? money(r.amount) : '-' }}</td>
                                                <td><span :class="getStatusClass(r.status)" class="rounded-full px-2.5 py-0.5 text-xs font-black">{{ r.status }}</span></td>
                                                <td class="max-w-xs text-xs text-slate-500">{{ r.detail }}</td>
                                                <td>
                                                    <div v-if="r.status !== 'Matched' && r.status !== 'Adjusted'" class="flex flex-wrap gap-1">
                                                        <button v-if="r.status === 'Open'" class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700" @click.stop="setStatus(r.id, 'Investigating')">Investigate</button>
                                                        <button class="rounded-lg bg-green-100 px-2.5 py-1 text-xs font-black text-green-700" @click.stop="setStatus(r.id, 'Matched')">Match</button>
                                                        <button class="rounded-lg bg-sky-100 px-2.5 py-1 text-xs font-black text-sky-700" @click.stop="setStatus(r.id, 'Adjusted')">Adjust</button>
                                                        <button class="rounded-lg bg-purple-100 px-2.5 py-1 text-xs font-black text-purple-700" @click.stop="setStatus(r.id, 'Escalated')">Escalate</button>
                                                    </div>
                                                    <span v-else class="text-xs font-bold text-slate-400">resolved</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Risk Rules</h3>
                <div class="space-y-3">
                    <div v-for="rule in rules" :key="rule.id" class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <b>{{ rule.name }}</b>
                            <label class="relative inline-flex cursor-pointer items-center"><input type="checkbox" :checked="rule.enabled" class="peer sr-only" @change="toggleRule(rule)" /><div class="peer h-6 w-11 rounded-full bg-slate-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:bg-green-500 peer-checked:after:translate-x-5"></div></label>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ ruleParts(rule.tmpl).before }}<input v-model.number="rule.value" class="inline-block w-20 rounded-lg border border-slate-200 px-2 py-0.5 font-black text-slate-900" />{{ ruleParts(rule.tmpl).after }}</p>
                    </div>
                    <button class="mt-1 w-full rounded-2xl bg-slate-950 px-5 py-2.5 font-black text-white" @click="saveRules">Save Risk Rules</button>
                    <span v-if="showRulesNote" class="block text-sm font-bold text-green-600">Risk rules saved.</span>
                </div>
            </div>
        </div>
    </div>
</template>