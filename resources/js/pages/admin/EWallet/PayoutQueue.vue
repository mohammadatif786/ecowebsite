<script setup lang="ts">
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const busyId = ref<number | null>(null);
const notice = ref<{ ok: boolean; text: string } | null>(null);
const confirming = ref<any | null>(null); // { row, mode: 'approve' | 'reject' }
const reason = ref('');

const reloadQueue = () => router.reload({ only: ['initialPayoutQueue', 'initialPayoutQueueSummary', 'initialWalletSummary', 'initialWalletMovements'], preserveScroll: true });

const ask = (row: any, mode: 'approve' | 'reject') => { confirming.value = { row, mode }; reason.value = ''; notice.value = null; };

const submit = async () => {
    const c = confirming.value;
    if (!c) return;
    if (c.mode === 'reject' && reason.value.trim().length < 3) { notice.value = { ok: false, text: 'Give the member a reason (at least 3 characters).' }; return; }
    busyId.value = c.row.withdrawalId;
    try {
        const url = route(c.mode === 'approve' ? 'admin.e-wallet.withdrawals.approve' : 'admin.e-wallet.withdrawals.reject', { withdrawal: c.row.withdrawalId });
        const { data } = await axios.post(url, c.mode === 'reject' ? { reason: reason.value.trim() } : {});
        notice.value = { ok: true, text: data.message };
        confirming.value = null;
        reloadQueue();
    } catch (e: any) {
        const errs = e?.response?.data?.errors;
        notice.value = { ok: false, text: (errs && Object.values(errs).flat()[0]) || e?.response?.data?.message || 'Could not update this withdrawal.' };
        confirming.value = null;
        reloadQueue();
    } finally { busyId.value = null; }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Wallet Payout Queue</h3>
            <p class="text-slate-500">Member bank withdrawals to approve or reject, plus organizer and ASUE payouts.</p>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Pending Payouts</p><h3 class="text-4xl font-black">{{ num(payoutQueueSummary.pending) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Processing</p><h3 class="text-4xl font-black text-sky-600">{{ num(payoutQueueSummary.processing) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Completed Today</p><h3 class="text-4xl font-black text-green-600">{{ num(payoutQueueSummary.completedToday) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Failed / Rejected</p><h3 class="text-4xl font-black text-rose-600">{{ num(payoutQueueSummary.failed) }}</h3></div>
        </div>

        <p v-if="notice" :class="notice.ok ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-700'" class="rounded-2xl p-3 font-bold">{{ notice.text }}</p>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Cash-Out Requests</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr><th class="py-3">Payout Ref</th><th>Member / Party</th><th>Source</th><th>Country</th><th>Bank / Wallet</th><th>Gross</th><th>Fee</th><th>Net</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!payoutQueue.length"><td colspan="10" class="py-6 text-center text-slate-400 font-bold">No payouts yet.</td></tr>
                        <tr v-for="p in payoutQueue" :key="p.id" class="border-t">
                            <td class="py-3 font-black text-purple-600">{{ p.id }}</td>
                            <td><span class="block font-bold">{{ p.party }}</span><span v-if="p.email" class="text-xs text-slate-400">{{ p.email }}</span></td>
                            <td>{{ p.source }}</td>
                            <td>{{ p.country }}</td>
                            <td>{{ p.bankWallet }}</td>
                            <td>{{ fmt(p.gross) }}</td>
                            <td>{{ fmt(p.fee) }}</td>
                            <td class="font-black">{{ fmt(p.net) }}</td>
                            <td>
                                <span :class="statusBadge(p.status)" class="rounded-full px-3 py-1 text-xs font-black">{{ p.status }}</span>
                                <span v-if="p.failureReason" class="mt-1 block max-w-[14rem] truncate text-xs text-rose-600" :title="p.failureReason">{{ p.failureReason }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <template v-if="p.kind === 'withdrawal' && p.status === 'Pending'">
                                    <button :disabled="busyId === p.withdrawalId" class="rounded-xl bg-green-600 px-3 py-1 text-xs font-black text-white disabled:opacity-50" @click="ask(p, 'approve')">Approve</button>
                                    <button :disabled="busyId === p.withdrawalId" class="ml-1 rounded-xl bg-rose-600 px-3 py-1 text-xs font-black text-white disabled:opacity-50" @click="ask(p, 'reject')">Reject</button>
                                </template>
                                <span v-else-if="p.kind === 'organizer' && p.status === 'Pending'" class="text-xs font-bold text-slate-400">Handled in Payout Ops</span>
                                <span v-else class="text-xs font-bold text-slate-300">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confirm -->
        <div v-if="confirming" class="fixed inset-0 z-[200] grid place-items-center bg-black/40 p-4" @click.self="confirming = null">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                <h4 class="text-2xl font-black">{{ confirming.mode === 'approve' ? 'Approve withdrawal?' : 'Reject withdrawal?' }}</h4>
                <p class="mt-2 text-slate-600">
                    <b>{{ confirming.row.party }}</b> · {{ confirming.row.bankWallet }}<br />
                    Gross {{ fmt(confirming.row.gross) }} · fee {{ fmt(confirming.row.fee) }} · <b>pays out {{ fmt(confirming.row.net) }}</b>
                </p>
                <p v-if="confirming.mode === 'approve'" class="mt-3 rounded-2xl bg-amber-50 p-3 text-sm font-bold text-amber-800">
                    Only approve once the bank transfer has been sent. The member is emailed that it's on the way.
                </p>
                <template v-else>
                    <p class="mt-3 rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-600">{{ fmt(confirming.row.gross) }} goes back to the member's wallet straight away.</p>
                    <textarea v-model="reason" rows="3" maxlength="500" class="mt-3 w-full rounded-2xl border border-slate-200 p-3" placeholder="Reason the member will see (e.g. account number doesn't match the name)"></textarea>
                </template>
                <p v-if="notice && !notice.ok" class="mt-3 rounded-2xl bg-rose-50 p-3 text-sm font-bold text-rose-700">{{ notice.text }}</p>
                <div class="mt-5 flex gap-2">
                    <button class="flex-1 rounded-2xl border px-4 py-3 font-black" @click="confirming = null">Cancel</button>
                    <button :disabled="busyId !== null" :class="confirming.mode === 'approve' ? 'bg-green-600' : 'bg-rose-600'" class="flex-1 rounded-2xl px-4 py-3 font-black text-white disabled:opacity-50" @click="submit">
                        {{ busyId !== null ? 'Saving…' : confirming.mode === 'approve' ? 'Approve' : 'Reject & refund' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
