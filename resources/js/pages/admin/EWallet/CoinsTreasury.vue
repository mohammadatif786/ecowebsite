<script setup lang="ts">
import Chart from 'chart.js/auto';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

// Real coin figures from the server (GetAdminCoinsDataAction):
// purchases = Stripe coin purchases, held = coins in members' accounts, spent = coins sent as gifts.
const props = defineProps<{
    coins: {
        summary?: Record<string, number>;
        byCountry?: any[];
        recentPurchases?: any[];
        topHolders?: any[];
        dormant?: { months: number; coins: number; usd: number; members: number }[];
    };
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const s = computed(() => props.coins?.summary || {});
const n = (k: string) => Number(s.value[k] || 0);
const byCountry = computed(() => props.coins?.byCountry || []);
const recent = computed(() => props.coins?.recentPurchases || []);
const holders = computed(() => props.coins?.topHolders || []);
const dormant = computed(() => props.coins?.dormant || []);
const dormMonths = ref(12);
const dormantRow = computed(() => dormant.value.find((d) => d.months === dormMonths.value) || { months: dormMonths.value, coins: 0, usd: 0, members: 0 });

const coins = (x: number) => Number(x || 0).toLocaleString() + ' 🪙';
const when = (d: string | null) => (d ? new Date(d.replace(' ', 'T')).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—');

const chartRef = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;
const renderChart = () => {
    chart?.destroy();
    if (!chartRef.value || !byCountry.value.length) return;
    chart = new Chart(chartRef.value, {
        type: 'bar',
        data: {
            labels: byCountry.value.map((c) => c.country),
            datasets: [
                { label: 'Coins bought', data: byCountry.value.map((c) => c.bought), backgroundColor: '#F59E0B', borderRadius: 8 },
                { label: 'Coins held now', data: byCountry.value.map((c) => c.held), backgroundColor: '#28A8FF', borderRadius: 8 },
            ],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
    });
};
onMounted(() => nextTick(renderChart));
watch(byCountry, () => nextTick(renderChart), { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black text-slate-950">LinkUp Coins</h3>
            <p class="text-slate-500">Coins members bought with Stripe, coins they hold now, and coins sent as gifts — straight from the database.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card metric dark rounded-3xl p-5">
                <p class="font-bold text-slate-300">Coin Sales (all time)</p>
                <h3 class="mt-2 text-5xl font-black">{{ fmt(n('usdPaid')) }}</h3>
                <p class="font-bold text-lime-300">{{ coins(n('coinsBought')) }} · {{ num(n('purchases')) }} purchases by {{ num(n('buyers')) }} members</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Coins Held by Members</p>
                <h3 class="mt-2 text-4xl font-black">{{ coins(n('held')) }}</h3>
                <p class="font-bold text-sky-600">≈ {{ fmt(n('heldUsd')) }} · {{ num(n('holders')) }} members hold coins</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Coins Sent as Gifts</p>
                <h3 class="mt-2 text-4xl font-black">{{ coins(n('spent')) }}</h3>
                <p class="font-bold text-purple-600">Vibes/Reels {{ num(n('spentGifts')) }} · Live {{ num(n('spentLive')) }}</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Sales Today / 30 Days</p>
                <h3 class="mt-2 text-4xl font-black">{{ fmt(n('usdToday')) }}</h3>
                <p class="font-bold text-emerald-600">{{ coins(n('boughtToday')) }} today · {{ fmt(n('usd30d')) }} in 30 days</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <div class="card rounded-3xl p-6 2xl:col-span-2">
                <h3 class="mb-1 text-xl font-black">Coins by Country</h3>
                <p class="mb-4 text-slate-500">Bought vs. still held, by the member's country.</p>
                <div v-if="byCountry.length" class="h-64"><canvas ref="chartRef"></canvas></div>
                <p v-else class="py-10 text-center font-bold text-slate-400">No coin activity yet.</p>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-1 text-xl font-black">Dormant Coins</h3>
                <p class="mb-4 text-slate-500">Coins held by members who haven't signed in for a while.</p>
                <select v-model.number="dormMonths" class="mb-4 w-full rounded-2xl border border-slate-200 px-4 py-2 font-bold">
                    <option v-for="d in dormant" :key="d.months" :value="d.months">Not signed in for {{ d.months }}+ months</option>
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="font-bold text-slate-500">Coins</p><h4 class="text-2xl font-black">{{ num(dormantRow.coins) }}</h4></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="font-bold text-slate-500">Value</p><h4 class="text-2xl font-black">{{ fmt(dormantRow.usd) }}</h4></div>
                    <div class="col-span-2 rounded-2xl bg-amber-50 p-4"><p class="font-bold text-amber-700">{{ num(dormantRow.members) }} members</p>
                        <p class="text-xs text-amber-700">Value uses the average price members actually paid: {{ fmt(n('coinUsd')) }} per coin.</p></div>
                </div>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Coin Ledger by Country</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr><th class="py-3">Country</th><th>Coins Bought</th><th>Paid (USD)</th><th>Buyers</th><th>Coins Held Now</th><th>Held Value</th><th>Holders</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!byCountry.length"><td colspan="7" class="py-6 text-center font-bold text-slate-400">No coin activity yet.</td></tr>
                        <tr v-for="c in byCountry" :key="c.country" class="border-t">
                            <td class="py-3 font-black">{{ c.country }}</td>
                            <td>{{ num(c.bought) }}</td>
                            <td class="font-bold">{{ fmt(c.usd) }}</td>
                            <td>{{ num(c.buyers) }}</td>
                            <td class="font-bold text-amber-600">{{ num(c.held) }}</td>
                            <td class="text-sky-600">{{ fmt(c.heldUsd) }}</td>
                            <td>{{ num(c.holders) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Latest Coin Purchases</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs font-black text-slate-500 uppercase">
                            <tr><th class="py-3">When</th><th>Member</th><th>Country</th><th>Coins</th><th>Paid</th><th>Stripe</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!recent.length"><td colspan="6" class="py-6 text-center font-bold text-slate-400">No coin purchases yet.</td></tr>
                            <tr v-for="p in recent" :key="p.id" class="border-t">
                                <td class="py-3 text-sm text-slate-500 whitespace-nowrap">{{ when(p.date) }}</td>
                                <td><span class="block font-black">{{ p.name }}</span><span class="text-xs text-slate-400">#{{ p.userId }} {{ p.email }}</span></td>
                                <td class="text-sm">{{ p.country }}</td>
                                <td class="font-bold text-amber-600">{{ num(p.coins) }}</td>
                                <td class="font-black">{{ fmt(p.usd) }}</td>
                                <td class="text-xs text-slate-400">{{ p.ref || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Top Coin Holders</h3>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs font-black text-slate-500 uppercase">
                            <tr><th class="py-3">Member</th><th>Country</th><th>Coins</th><th>Value</th><th>Last sign-in</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!holders.length"><td colspan="5" class="py-6 text-center font-bold text-slate-400">No member holds coins yet.</td></tr>
                            <tr v-for="h in holders" :key="h.id" class="border-t">
                                <td class="py-3"><span class="block font-black">{{ h.name }}</span><span class="text-xs text-slate-400">#{{ h.id }} {{ h.email }}</span></td>
                                <td class="text-sm">{{ h.country }}</td>
                                <td class="font-bold text-amber-600">{{ num(h.coins) }}</td>
                                <td>{{ fmt(h.usd) }}</td>
                                <td class="text-sm text-slate-500">{{ when(h.lastLogin) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
