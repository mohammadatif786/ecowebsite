<script setup lang="ts">
const props = defineProps<{
    walletUserAccounts: any[];
    walletUserAccountStats: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
    getScale: () => number;
}>();

const statusBadge = (status: string) => {
    if (status === 'Active') return 'bg-green-50 text-green-700';
    if (status === 'Review') return 'bg-amber-50 text-amber-700';
    return 'bg-rose-50 text-rose-700';
};

const riskBadge = (risk: string) => {
    if (risk === 'Low') return 'text-green-600';
    if (risk === 'Medium') return 'text-amber-600';
    return 'text-rose-600';
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">Wallet Users</h3>
            <p class="text-slate-500">User wallet accounts, KYC status, balances, linked bank/card readiness, and account health.</p>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Total Wallet Users</p>
                <h3 class="text-4xl font-black">{{ num(walletUserAccountStats.totalWalletUsers) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Verified KYC</p>
                <h3 class="text-4xl font-black text-green-600">{{ num(walletUserAccountStats.verifiedKyc) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Pending KYC</p>
                <h3 class="text-4xl font-black text-amber-600">{{ num(walletUserAccountStats.pendingKyc) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Suspended</p>
                <h3 class="text-4xl font-black text-rose-600">{{ num(walletUserAccountStats.suspended) }}</h3>
            </div>
        </div>
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Wallet User Directory</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">User</th>
                            <th>Country</th>
                            <th>Wallet ID</th>
                            <th>Balance</th>
                            <th>KYC</th>
                            <th>Linked Bank</th>
                            <th>Linked Card</th>
                            <th>Risk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!walletUserAccounts.length">
                            <td colspan="9" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="u in walletUserAccounts" :key="u.walletId" class="border-t hover:bg-slate-50">
                            <td class="py-3 font-black">{{ u.user }}</td>
                            <td>{{ u.country }}</td>
                            <td class="text-sm text-slate-500">{{ u.walletId }}</td>
                            <td class="font-black">{{ fmt(u.balance * getScale()) }}</td>
                            <td>{{ u.kyc }}</td>
                            <td class="text-sm">{{ u.linkedBank }}</td>
                            <td class="text-sm">{{ u.linkedCard }}</td>
                            <td class="font-bold" :class="riskBadge(u.risk)">{{ u.risk }}</td>
                            <td>
                                <span :class="statusBadge(u.status)" class="rounded-full px-3 py-1 text-xs font-black">
                                    {{ u.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
