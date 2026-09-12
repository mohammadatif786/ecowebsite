<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    filteredMovements: any[];
    walletFilter: any;
    fmt: (n: number) => string;
    getScale: () => number;
}>();

const todayKey = new Date().toISOString().slice(0, 10);

const sumByType = (type: string) => props.filteredMovements.filter((w) => w.type === type).reduce((s, w) => s + w.amount, 0) * props.getScale();

const transactionsToday = computed(() => props.filteredMovements.filter((w) => w.date?.slice(0, 10) === todayKey).length);
const walletLoadsTotal = computed(() => sumByType('Wallet Load'));
const transfersTotal = computed(() => sumByType('Send Money'));
const merchantPayTotal = computed(() => sumByType('Merchant Pay'));
const billPaymentsTotal = computed(() => sumByType('Bill Payment'));
const failedReviewCount = computed(() => props.filteredMovements.filter((w) => w.status === 'Review' || w.status === 'Failed').length);

const exportWalletCSV = () => {
    alert('Exporting Wallet CSV...');
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Wallet Transactions Ledger</h3>
                <p class="text-slate-500">
                    Wallet loads, transfers, merchant QR payments, bill payments, and cash-outs across the system.
                </p>
            </div>
            <button @click="exportWalletCSV" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">Export Transactions CSV</button>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Transactions Today</p>
                <h3 class="text-3xl font-black">{{ transactionsToday.toLocaleString() }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Wallet Loads</p>
                <h3 class="text-3xl font-black">{{ fmt(walletLoadsTotal) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Transfers</p>
                <h3 class="text-3xl font-black">{{ fmt(transfersTotal) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">QR Merchant Pay</p>
                <h3 class="text-3xl font-black">{{ fmt(merchantPayTotal) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Bill Payments</p>
                <h3 class="text-3xl font-black">{{ fmt(billPaymentsTotal) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Failed / Review</p>
                <h3 class="text-3xl font-black text-rose-600">{{ failedReviewCount.toLocaleString() }}</h3>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-xl font-black">Live Wallet Movement</h3>
                    <p class="text-slate-500">Real-time ledger across every wallet transaction type.</p>
                </div>
                <input
                    v-model="walletFilter.search"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-2 lg:w-96"
                    placeholder="Search transaction, user, country, wallet type..."
                />
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Ref</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Country</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredMovements.length">
                            <td colspan="10" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="w in filteredMovements" :key="w.id" class="border-t hover:bg-slate-50">
                            <td class="py-3 font-black text-purple-600">{{ w.id }}</td>
                            <td>{{ w.date }}</td>
                            <td class="font-bold">{{ w.user }}</td>
                            <td>{{ w.country }}</td>
                            <td>{{ w.type }}</td>
                            <td class="text-slate-500">{{ w.channel }}</td>
                            <td class="font-black">{{ fmt(w.amount * getScale()) }}</td>
                            <td class="font-bold text-sky-600">{{ fmt(w.fee * getScale()) }}</td>
                            <td>
                                <span
                                    :class="{
                                        'bg-green-50 text-green-700': w.status === 'Completed',
                                        'bg-amber-50 text-amber-700': w.status === 'Pending',
                                        'bg-rose-50 text-rose-700': w.status === 'Review' || w.status === 'Failed',
                                    }"
                                    class="rounded-full px-3 py-1 text-xs font-black"
                                >
                                    {{ w.status }}
                                </span>
                            </td>
                            <td>{{ fmt(w.balanceAfter * getScale()) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
