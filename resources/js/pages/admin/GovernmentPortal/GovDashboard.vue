<script setup lang="ts">
import { Landmark } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        totalBeneficiaries: number;
        totalDisbursed: number;
        revenue: number;
        floatBalance: number;
    };
    programs: any[];
    batches: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const groupedPrograms = computed(() => {
    const groups: Record<string, any[]> = {};
    props.programs.forEach((p) => {
        if (!groups[p.country]) groups[p.country] = [];
        groups[p.country].push(p);
    });
    return groups;
});

const groupedBatches = computed(() => {
    const groups: Record<string, any[]> = {};
    props.batches.forEach((b) => {
        if (!groups[b.country]) groups[b.country] = [];
        groups[b.country].push(b);
    });
    return groups;
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Government Disbursements Portal</h3>
                <p class="text-slate-500">
                    G2P payouts — national insurance, pensions & grants paid straight to citizens' LinkUp wallets (no bank needed).
                    Pre-funded float held at Scotiabank. Period & region reactive.
                </p>
            </div>
            <button class="rounded-2xl bg-slate-950 px-5 py-3 font-black text-white">New Disbursement</button>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Beneficiaries</p>
                <h3 class="text-4xl font-black">{{ num(stats.totalBeneficiaries) }}</h3>
                <p class="text-xs text-slate-500">{{ programs.length }} programs</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Disbursed (period)</p>
                <h3 class="text-3xl font-black text-green-600">{{ fmt(stats.totalDisbursed) }}</h3>
                <p class="text-xs text-slate-500">to citizen wallets</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Scotiabank Float</p>
                <h3 class="text-3xl font-black text-blue-600">{{ fmt(stats.floatBalance) }}</h3>
                <p class="text-xs text-slate-500">pre-funded escrow</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">LinkUp Gov Revenue</p>
                <h3 class="text-3xl font-black text-purple-600">{{ fmt(stats.revenue) }}</h3>
                <p class="text-xs text-slate-500">fees + SaaS + float yield</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <!-- Treasury Float -->
            <div class="card rounded-3xl p-6 2xl:col-span-1">
                <div class="mb-2 flex items-center gap-2">
                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-red-600 font-black text-white">S</div>
                    <h3 class="text-xl font-black">Treasury Float</h3>
                </div>
                <p class="mb-3 text-sm text-slate-500">
                    Ring-fenced Scotiabank escrow. Agencies pre-fund here; payouts draw down from this balance.
                </p>
                <div class="space-y-2 rounded-2xl bg-slate-50 p-4 text-sm">
                    <div class="flex justify-between">
                        <span class="font-bold text-slate-500">Available Balance</span>
                        <span class="text-lg font-black">{{ fmt(stats.floatBalance) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-slate-500">Monthly Payout Need</span>
                        <span class="font-black">$24.2M</span>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <input type="number" placeholder="Amount to pre-fund" class="flex-1 rounded-2xl border border-slate-200 px-4 py-2.5" />
                    <button class="rounded-2xl bg-slate-950 px-4 py-2.5 font-black text-white">Pre-Fund</button>
                </div>
            </div>

            <!-- Programs & Agencies -->
            <div class="card rounded-3xl p-6 2xl:col-span-2">
                <h3 class="mb-4 text-xl font-black">Programs & Agencies</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2">Program</th>
                                <th>Agency</th>
                                <th>Beneficiaries</th>
                                <th>Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(countryPrograms, country) in groupedPrograms" :key="country">
                                <tr>
                                    <td colspan="5" class="py-2 px-3 font-black text-white bg-gradient-to-r from-slate-800 to-slate-600 border-t border-slate-200">
                                        <div class="flex items-center gap-2">
                                            <span>▾</span>
                                            <span>🌎 {{ country }}</span>
                                            <span class="opacity-60 text-xs">({{ countryPrograms.length }})</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="p in countryPrograms" :key="p.id" class="border-t">
                                    <td class="py-2 pl-6 font-black text-slate-700 bg-slate-50/30">{{ p.name }}</td>
                                    <td class="text-slate-500">{{ p.agency }}</td>
                                    <td>{{ num(p.beneficiaries) }}</td>
                                    <td>{{ p.schedule }}</td>
                                    <td>
                                        <span
                                            class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                            :class="p.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
                                        >
                                            {{ p.status }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Batches -->
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Recent Disbursement Batches</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2">Batch</th>
                            <th>Program</th>
                            <th>Beneficiaries</th>
                            <th>Amount</th>
                            <th>LinkUp Fee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(countryBatches, country) in groupedBatches" :key="country">
                            <tr>
                                <td colspan="6" class="py-2 px-3 font-black text-white bg-gradient-to-r from-slate-800 to-slate-600 border-t border-slate-200">
                                    <div class="flex items-center gap-2">
                                        <span>▾</span>
                                        <span>🌎 {{ country }}</span>
                                        <span class="opacity-60 text-xs">({{ countryBatches.length }})</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="b in countryBatches" :key="b.id" class="border-t">
                                <td class="py-2 pl-6 font-black text-slate-700 bg-slate-50/30">{{ b.id }}</td>
                                <td>{{ b.program }}</td>
                                <td>{{ num(b.count) }}</td>
                                <td class="font-black">{{ fmt(b.net) }}</td>
                                <td class="font-bold text-purple-600">{{ fmt(b.fee) }}</td>
                                <td>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                        :class="b.status === 'Disbursed' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600'"
                                    >
                                        {{ b.status }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
