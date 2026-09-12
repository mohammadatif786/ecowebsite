<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    complianceCases: any[];
    fmt: (n: number) => string;
    filters: any;
    countries: any[];
    getScale: () => number;
}>();

const wcFilter = ref('all');

const baseCases = computed(() => {
    return props.complianceCases.filter((c) => {
        const country = props.countries.find((x) => x.country === c.country);
        const okRegion = props.filters.region === 'All' || (country && country.region === props.filters.region);
        const okCountry = props.filters.country === 'All Countries' || c.country === props.filters.country;
        return okRegion && okCountry;
    });
});

const stats = computed(() => {
    const list = baseCases.value;
    const open = list.filter((c) => ['Open', 'Investigating', 'Pending Docs'].includes(c.status));
    const highRisk = list.filter((c) => c.risk === 'High' && ['Open', 'Investigating'].includes(c.status));
    const sarFiled = list.filter((c) => c.status === 'SAR Filed');
    const cleared = list.filter((c) => c.status === 'Cleared').length;
    const clearRate = list.length ? Math.round((cleared / list.length) * 1000) / 10 : 0;
    const screened = Math.round(48200 * props.getScale());

    return {
        open: open.length,
        highRisk: highRisk.length,
        sarFiled: sarFiled.length,
        screened: screened.toLocaleString(),
        clearRate
    };
});

const filteredCases = computed(() => {
    if (wcFilter.value === 'all') return baseCases.value;
    if (wcFilter.value === 'High') return baseCases.value.filter((c) => c.risk === 'High');
    return baseCases.value.filter((c) => c.status === wcFilter.value);
});

const setStatus = (id: string, status: string) => {
    const c = props.complianceCases.find((x) => x.id === id);
    if (c) {
        c.status = status;
    }
};

const thresholds = ref({
    singleTxn: 10000,
    dailyVelocity: 25000,
    crossBorder: 5000,
    structuringCount: 3,
    dormantDays: 180
});

const showThresholdNote = ref(false);
const saveThresholds = () => {
    showThresholdNote.value = true;
    setTimeout(() => (showThresholdNote.value = false), 3000);
};

const filterOptions = [
    { key: 'all', label: 'All' },
    { key: 'Open', label: 'Open' },
    { key: 'Investigating', label: 'Investigating' },
    { key: 'High', label: 'High Risk' },
    { key: 'Escalated', label: 'Escalated' },
    { key: 'SAR Filed', label: 'SAR Filed' },
    { key: 'Cleared', label: 'Cleared' },
];

const lkGroupOpen = ref<Record<string, boolean>>({});

const toggleGroup = (key: string) => {
    if (lkGroupOpen.value[key] === undefined) {
        // Initialize based on defaults: Regions open (true), Countries closed (false)
        lkGroupOpen.value[key] = key.includes(':R:');
    }
    lkGroupOpen.value[key] = !lkGroupOpen.value[key];
};

const isGroupOpen = (key: string) => {
    if (lkGroupOpen.value[key] === undefined) {
        return key.includes(':R:');
    }
    return lkGroupOpen.value[key];
};

const userRegion = (countryName: string) => {
    const c = props.countries.find((x) => x.country === countryName);
    return c ? c.region : 'Other';
};

const groupedCases = computed(() => {
    const items = filteredCases.value;
    const groups: Record<string, Record<string, any[]>> = {};

    items.forEach((item) => {
        const country = item.country || 'Other';
        const region = userRegion(country);

        if (!groups[region]) groups[region] = {};
        if (!groups[region][country]) groups[region][country] = [];
        groups[region][country].push(item);
    });

    // Sort regions and countries within them
    const sortedGroups: Record<string, Record<string, any[]>> = {};
    Object.keys(groups).sort().forEach(region => {
        sortedGroups[region] = {};
        Object.keys(groups[region]).sort((a, b) => groups[region][b].length - groups[region][a].length || a.localeCompare(b)).forEach(country => {
            sortedGroups[region][country] = groups[region][country];
        });
    });

    return sortedGroups;
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Wallet Compliance Desk</h3>
            <p class="text-slate-500">
                AML / KYC / sanctions review workspace — work the case queue, then clear, escalate, or file a SAR. Period & region reactive.
            </p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Open Cases</p>
                <h3 class="text-4xl font-black text-rose-600">{{ stats.open }}</h3>
                <p class="text-xs text-slate-500">awaiting action</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">High Risk</p>
                <h3 class="text-4xl font-black text-amber-600">{{ stats.highRisk }}</h3>
                <p class="text-xs text-slate-500">need EDD</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">SARs Filed</p>
                <h3 class="text-4xl font-black">{{ stats.sarFiled }}</h3>
                <p class="text-xs text-slate-500">reported to FIU</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Txns Screened</p>
                <h3 class="text-4xl font-black text-green-600">{{ stats.screened }}</h3>
                <p class="text-xs text-slate-500">{{ stats.clearRate }}% auto-cleared</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <!-- Case Queue -->
            <div class="card 2xl:col-span-2 rounded-3xl p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-xl font-black">Case Queue</h3>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="opt in filterOptions"
                            :key="opt.key"
                            @click="wcFilter = opt.key"
                            :class="wcFilter === opt.key ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600'"
                            class="rounded-full px-4 py-1.5 text-sm font-black transition"
                        >
                            {{ opt.label }}{{ opt.key === 'all' ? ` (${baseCases.length})` : '' }}
                        </button>
                    </div>
                </div>

                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs font-black text-slate-500 uppercase">
                            <tr>
                                <th class="py-2">Case</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Risk</th>
                                <th>Status</th>
                                <th>Detail</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="Object.keys(groupedCases).length === 0">
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                                </tr>
                            </template>
                            <template v-else v-for="(countriesInRegion, region) in groupedCases" :key="region">
                                <!-- Region Header -->
                                <tr @click="toggleGroup('wc:R:' + region)" class="cursor-pointer">
                                    <td colspan="8" class="bg-gradient-to-r from-slate-800 to-slate-600 px-3 py-2 font-black text-white">
                                        {{ isGroupOpen('wc:R:' + region) ? '▾' : '▸' }} 🌎 {{ region }}
                                        <span class="opacity-60">({{ Object.values(countriesInRegion).flat().length }})</span>
                                    </td>
                                </tr>

                                <template v-if="isGroupOpen('wc:R:' + region)">
                                    <template v-for="(cases, country) in countriesInRegion" :key="country">
                                        <!-- Country Header -->
                                        <tr @click="toggleGroup('wc:C:' + country)" class="cursor-pointer">
                                            <td colspan="8" class="bg-slate-100 px-6 py-2 font-black text-slate-700">
                                                {{ isGroupOpen('wc:C:' + country) ? '▾' : '▸' }} {{ country }}
                                                <span class="opacity-50">({{ cases.length }})</span>
                                            </td>
                                        </tr>

                                        <!-- Cases in Country -->
                                        <template v-if="isGroupOpen('wc:C:' + country)">
                                            <tr v-for="c in cases" :key="c.id" class="border-t align-top">
                                                <td class="py-3 font-black">
                                                    {{ c.id }}
                                                    <div class="text-xs font-normal text-slate-400">{{ c.date }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-bold">{{ c.user }}</div>
                                                    <div class="text-xs text-slate-500">{{ c.country }}</div>
                                                </td>
                                                <td>{{ c.type }}</td>
                                                <td class="font-black">{{ c.amount ? fmt(c.amount) : '—' }}</td>
                                                <td>
                                                    <span
                                                        :class="{
                                                            'bg-rose-50 text-rose-700': c.risk === 'High',
                                                            'bg-amber-50 text-amber-700': c.risk === 'Medium',
                                                            'bg-green-50 text-green-700': c.risk === 'Low',
                                                        }"
                                                        class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                                    >
                                                        {{ c.risk }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        :class="{
                                                            'bg-rose-50 text-rose-700': c.status === 'Open',
                                                            'bg-amber-50 text-amber-700': ['Investigating', 'Pending Docs'].includes(c.status),
                                                            'bg-green-50 text-green-700': c.status === 'Cleared',
                                                            'bg-purple-50 text-purple-700': c.status === 'Escalated',
                                                            'bg-slate-900 text-white': c.status === 'SAR Filed',
                                                        }"
                                                        class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                                    >
                                                        {{ c.status }}
                                                    </span>
                                                </td>
                                                <td class="max-w-xs text-xs text-slate-500">{{ c.reason }}</td>
                                                <td>
                                                    <div v-if="c.status !== 'Cleared' && c.status !== 'SAR Filed'" class="flex flex-wrap gap-1">
                                                        <button
                                                            v-if="c.status === 'Open'"
                                                            @click="setStatus(c.id, 'Investigating')"
                                                            class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700"
                                                        >
                                                            Investigate
                                                        </button>
                                                        <button
                                                            @click="setStatus(c.id, 'Cleared')"
                                                            class="rounded-lg bg-green-100 px-2.5 py-1 text-xs font-black text-green-700"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="setStatus(c.id, 'Escalated')"
                                                            class="rounded-lg bg-purple-100 px-2.5 py-1 text-xs font-black text-purple-700"
                                                        >
                                                            Escalate
                                                        </button>
                                                        <button
                                                            @click="setStatus(c.id, 'SAR Filed')"
                                                            class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-black text-white"
                                                        >
                                                            File SAR
                                                        </button>
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

            <!-- Thresholds Card -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">AML Monitoring Thresholds</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Single Transaction Report Line ($)</label>
                        <input
                            v-model="thresholds.singleTxn"
                            type="number"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Daily Velocity Limit ($)</label>
                        <input
                            v-model="thresholds.dailyVelocity"
                            type="number"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Cross-Border Review Line ($)</label>
                        <input
                            v-model="thresholds.crossBorder"
                            type="number"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold text-slate-600">Structuring Count</label>
                            <input
                                v-model="thresholds.structuringCount"
                                type="number"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600">Dormant Days</label>
                            <input
                                v-model="thresholds.dormantDays"
                                type="number"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5"
                            />
                        </div>
                    </div>
                    <button @click="saveThresholds" class="w-full rounded-2xl bg-slate-950 py-3 font-bold text-white">
                        Save Thresholds
                    </button>
                    <p v-if="showThresholdNote" class="text-center text-sm font-bold text-green-600">
                        Monitoring thresholds saved — alerts will use these limits.
                    </p>
                    <p class="text-xs text-slate-500">
                        Transactions breaching any threshold auto-open a case in the queue. Filing a SAR notifies the jurisdiction's
                        Financial Intelligence Unit.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
