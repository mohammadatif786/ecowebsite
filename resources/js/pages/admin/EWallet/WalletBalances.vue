<script setup lang="ts">
const props = defineProps<{
    walletCountryBalances: any[];
    currencyExposure: any[];
    walletBalanceSummary: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Wallet Balances by Country</h3>
            <p class="text-slate-500">Country-level stored value, reserves, currency exposure, and float control.</p>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Stored Value</p>
                <h3 class="text-4xl font-black">{{ fmt(walletBalanceSummary.storedValue) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Required Reserve</p>
                <h3 class="text-4xl font-black">{{ fmt(walletBalanceSummary.requiredReserve) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Reserve Coverage</p>
                <h3 class="text-4xl font-black text-green-600">{{ Math.round(walletBalanceSummary.reserveCoveragePct) }}%</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Currencies</p>
                <h3 class="text-4xl font-black">{{ walletBalanceSummary.currencyCount }}</h3>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Country Balances</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs font-black text-slate-500 uppercase">
                            <tr>
                                <th class="py-3">Country</th>
                                <th>Users</th>
                                <th>Balance</th>
                                <th>Reserve Required</th>
                                <th>Currency</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!walletCountryBalances.length">
                                <td colspan="6" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                            </tr>
                            <tr v-for="c in walletCountryBalances" :key="c.country" class="border-t">
                                <td class="py-3 font-black">{{ c.country }}</td>
                                <td>{{ num(c.walletUsers) }}</td>
                                <td class="font-bold">{{ fmt(c.balance) }}</td>
                                <td class="font-bold text-purple-600">{{ fmt(c.reserveRequired) }}</td>
                                <td>{{ c.currency }}</td>
                                <td><span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-black text-green-700">{{ c.status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Currency Exposure</h3>
                <div class="space-y-3">
                    <div v-if="!currencyExposure.length" class="py-6 text-center text-slate-400 font-bold">No records.</div>
                    <div v-for="c in currencyExposure" :key="c.currency" class="flex justify-between rounded-2xl bg-slate-50 p-4">
                        <b>{{ c.currency }}</b>
                        <b>{{ fmt(c.total) }}</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
