<script setup lang="ts">
const props = defineProps<{
    settlements: any[];
    settlementSummary: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const statusBadge = (status: string) => {
    if (status === 'Completed') return 'bg-green-50 text-green-700';
    if (status === 'Processing') return 'bg-sky-50 text-sky-700';
    if (status === 'Failed') return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Settlement Center</h3>
            <p class="text-slate-500">
                The financial heart of LinkUp Wallet: daily settlements for events, merchants, billers, ASU, and bank/processor fees.
            </p>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Gross Volume Today</p>
                <h3 class="text-3xl font-black">{{ fmt(settlementSummary.grossToday) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Net Revenue Today</p>
                <h3 class="text-3xl font-black">{{ fmt(settlementSummary.netRevenueToday) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Pending</p>
                <h3 class="text-3xl font-black text-amber-600">{{ num(settlementSummary.pending) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Completed</p>
                <h3 class="text-3xl font-black text-green-600">{{ num(settlementSummary.completed) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Failed</p>
                <h3 class="text-3xl font-black text-rose-600">{{ num(settlementSummary.failed) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Processor Split</p>
                <h3 class="text-3xl font-black">{{ settlementSummary.split }}</h3>
            </div>
        </div>
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Settlement Queue</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Settlement Ref</th>
                            <th>Type</th>
                            <th>Party</th>
                            <th>Country</th>
                            <th>Gross</th>
                            <th>LinkUp Fee</th>
                            <th>Bank Share</th>
                            <th>Net Payable</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!settlements.length">
                            <td colspan="9" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="s in settlements" :key="s.id" class="border-t">
                            <td class="py-3 font-black text-purple-600">{{ s.id }}</td>
                            <td>{{ s.type }}</td>
                            <td class="font-bold">{{ s.party }}</td>
                            <td>{{ s.country }}</td>
                            <td>{{ fmt(s.gross) }}</td>
                            <td class="text-sky-600">{{ fmt(s.fees) }}</td>
                            <td class="text-slate-500">{{ fmt(s.bankShare) }}</td>
                            <td class="font-black">{{ fmt(s.payout) }}</td>
                            <td>
                                <span :class="statusBadge(s.status)" class="rounded-full px-3 py-1 text-xs font-black">
                                    {{ s.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
