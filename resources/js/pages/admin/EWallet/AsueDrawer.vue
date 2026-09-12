<script setup lang="ts">
const props = defineProps<{
    circles: any[];
    asueSummary: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const healthBadge = (health: string) => {
    if (health === 'Healthy' || health === 'Completed') return 'bg-green-50 text-green-700';
    if (health === 'Pending') return 'bg-slate-100 text-slate-600';
    return 'bg-rose-50 text-rose-700';
};

const riskBadge = (risk: string) => {
    if (risk === 'Low') return 'bg-green-50 text-green-700';
    if (risk === 'Medium') return 'bg-amber-50 text-amber-700';
    return 'bg-rose-50 text-rose-700';
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">ASUE Drawer Data</h3>
                <p class="text-slate-500">Real-time view of LinkUp social savings circles performance and health.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-bold">
                <span class="rounded-full border border-green-200 bg-green-50 px-3 py-1 text-green-700">• DB: Online</span>
                <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-sky-700">API Latency: 112ms</span>
                <span class="rounded-full bg-slate-950 px-3 py-1 tracking-widest text-white uppercase">ENV: Production</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="card rounded-3xl p-8">
                <p class="font-black text-slate-700 uppercase tracking-widest text-xs">Active Circles</p>
                <h3 class="mt-3 text-5xl font-black">{{ num(asueSummary.activeCircles) }}</h3>
                <p class="mt-2 text-slate-500">Live ASUE circles with funded slots.</p>
            </div>
            <div class="card border-orange-100 bg-orange-50 rounded-3xl p-8">
                <p class="font-black text-orange-600 uppercase tracking-widest text-xs">Pending Payouts</p>
                <h3 class="mt-3 text-5xl font-black">{{ fmt(asueSummary.pendingPayoutTotal) }}</h3>
                <p class="mt-2 text-slate-500">Due within the next 7 days.</p>
            </div>
            <div class="card border-indigo-100 bg-indigo-50 rounded-3xl p-8">
                <p class="font-black text-indigo-600 uppercase tracking-widest text-xs">LinkUp Fees Collected</p>
                <h3 class="mt-3 text-5xl font-black">{{ fmt(asueSummary.feesCollected) }}</h3>
                <p class="mt-2 text-slate-500">Total hand fees processed, 3% per hand.</p>
            </div>
        </div>

        <div class="card rounded-3xl overflow-hidden">
            <div class="p-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-black">Drawer Performance Ledger</h3>
                    <p class="text-slate-500">Circle health, cycle progress & payout risk.</p>
                </div>
                <div class="flex gap-2">
                    <span class="rounded-full bg-green-50 text-green-700 px-4 py-2 text-sm font-black">{{ asueSummary.healthyPct }}% Healthy</span>
                    <span class="rounded-full bg-orange-50 text-orange-700 px-4 py-2 text-sm font-black">{{ num(asueSummary.atRiskCount) }} At Risk</span>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500 font-black">
                        <tr>
                            <th class="py-5 px-8">Circle ID</th>
                            <th>Pot / Hand</th>
                            <th>Cycle</th>
                            <th>Next Draw</th>
                            <th>Fee / Hand</th>
                            <th>Total Fees</th>
                            <th>Health</th>
                            <th>Risk Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-t border-slate-100">
                        <tr v-if="!circles.length">
                            <td colspan="8" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="c in circles" :key="c.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-5 px-8 font-bold text-slate-700">{{ c.id }}</td>
                            <td class="font-black">{{ fmt(c.pot) }}</td>
                            <td class="text-slate-500 font-bold">{{ c.cycle }}</td>
                            <td class="text-slate-500 font-bold">{{ c.nextDraw }}</td>
                            <td class="font-black text-indigo-600">{{ fmt(c.fee) }}</td>
                            <td class="font-black">{{ fmt(c.collected) }}</td>
                            <td>
                                <span :class="healthBadge(c.health)" class="rounded-lg px-3 py-1 text-xs font-black uppercase">
                                    {{ c.health }}
                                </span>
                            </td>
                            <td>
                                <span :class="riskBadge(c.risk)" class="rounded-lg px-3 py-1 text-xs font-black uppercase">
                                    {{ c.risk }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
