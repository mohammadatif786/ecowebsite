<script setup lang="ts">
const props = defineProps<{
    payoutQueue: any[];
    payoutQueueSummary: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const statusBadge = (status: string) => {
    if (status === 'Completed') return 'bg-green-50 text-green-700';
    if (status === 'Processing') return 'bg-sky-50 text-sky-700';
    if (status === 'Failed') return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
};

const actionButtonClass = (action: string) => (action === 'View' ? 'bg-slate-200 text-slate-700' : 'bg-purple-600 text-white');
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Wallet Payout Queue</h3>
            <p class="text-slate-500">Cash-outs from wallets, ASU drawers, creator wallets, organizer wallets, and merchant wallet balances.</p>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Pending Payouts</p>
                <h3 class="text-4xl font-black">{{ num(payoutQueueSummary.pending) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Processing</p>
                <h3 class="text-4xl font-black text-sky-600">{{ num(payoutQueueSummary.processing) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Completed Today</p>
                <h3 class="text-4xl font-black text-green-600">{{ num(payoutQueueSummary.completedToday) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Failed</p>
                <h3 class="text-4xl font-black text-rose-600">{{ num(payoutQueueSummary.failed) }}</h3>
            </div>
        </div>
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Wallet Cash-Out Requests</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Payout Ref</th>
                            <th>User / Party</th>
                            <th>Source</th>
                            <th>Country</th>
                            <th>Bank / Wallet</th>
                            <th>Gross</th>
                            <th>Fee</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!payoutQueue.length">
                            <td colspan="10" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="p in payoutQueue" :key="p.id" class="border-t">
                            <td class="py-3 font-black text-purple-600">{{ p.id }}</td>
                            <td>{{ p.party }}</td>
                            <td>{{ p.source }}</td>
                            <td>{{ p.country }}</td>
                            <td>{{ p.bankWallet }}</td>
                            <td>{{ fmt(p.gross) }}</td>
                            <td>{{ fmt(p.fee) }}</td>
                            <td class="font-black">{{ fmt(p.net) }}</td>
                            <td>
                                <span :class="statusBadge(p.status)" class="rounded-full px-3 py-1 text-xs font-black">
                                    {{ p.status }}
                                </span>
                            </td>
                            <td>
                                <button :class="actionButtonClass(p.action)" class="rounded-xl px-3 py-1 text-xs font-black">{{ p.action }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
