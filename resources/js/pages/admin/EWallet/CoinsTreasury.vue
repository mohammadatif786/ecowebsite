<script setup lang="ts">
import Chart from 'chart.js/auto';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    getFilteredCountries: () => any[];
    getScale: () => number;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

// Coin totals — same purchased/redeemed/50-50 split formula used elsewhere in this
// app (e.g. the Overview dashboard's coinTotals()), computed from the real
// per-country coinsPurchased/coinsRedeemed fields already flowing through props.
const coinTotals = computed(() => {
    const rows = props.getFilteredCountries();
    const scale = props.getScale();
    const purchased = rows.reduce((s, c) => s + (c.coinsPurchased || 0), 0) * scale;
    const redeemed = rows.reduce((s, c) => s + (c.coinsRedeemed || 0), 0) * scale;
    const outstanding = Math.max(purchased - redeemed, 0);
    return {
        purchased,
        redeemed,
        outstanding,
        linkup: purchased * 0.5,
        creator: purchased * 0.5,
    };
});

// Coin Treasury Ledger — per-country breakdown, same fields/formula as coinTotals.
const countryCoinRows = computed(() => {
    const scale = props.getScale();
    return props.getFilteredCountries().map((c) => {
        const purchased = (c.coinsPurchased || 0) * scale;
        const redeemed = (c.coinsRedeemed || 0) * scale;
        return {
            country: c.country,
            purchased,
            redeemed,
            outstanding: Math.max(purchased - redeemed, 0),
            linkup: purchased * 0.5,
            creator: purchased * 0.5,
        };
    });
});

// Dormant Coins & Breakage — reactive controls matching the source dashboard's
// renderDormantCoins() (dormancy window is descriptive only; % of outstanding
// dormant and breakage-recognizable % drive the actual math, defaults 35% / 60%).
const dormMonths = ref(12);
const dormSharePct = ref(35);
const dormBreakagePct = ref(60);

const dormantSummary = computed(() => {
    const ct = coinTotals.value;
    const sharePct = (dormSharePct.value || 0) / 100;
    const breakPct = (dormBreakagePct.value || 0) / 100;
    const dormant = ct.outstanding * sharePct;
    return {
        outstanding: ct.outstanding,
        dormant,
        breakage: dormant * breakPct,
        escheat: dormant * (1 - breakPct),
        redemptionRate: ct.purchased ? (ct.redeemed / ct.purchased) * 100 : 0,
    };
});

const dormantByCountry = computed(() => {
    const sharePct = (dormSharePct.value || 0) / 100;
    const breakPct = (dormBreakagePct.value || 0) / 100;
    return countryCoinRows.value.map((c) => {
        const dormant = c.outstanding * sharePct;
        return {
            country: c.country,
            outstanding: c.outstanding,
            dormant,
            breakage: dormant * breakPct,
            escheat: dormant * (1 - breakPct),
        };
    });
});

const charts = ref<any>({});
const coinsCountryChartRef = ref<HTMLCanvasElement | null>(null);
const coinSplitChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const rows = props.getFilteredCountries();
    const scale = props.getScale();

    if (coinsCountryChartRef.value) {
        charts.value.coinsCountry = new Chart(coinsCountryChartRef.value, {
            type: 'bar',
            data: {
                labels: rows.map((c) => c.country),
                datasets: [{ label: 'Purchased', data: rows.map((c) => (c.coinsPurchased || 0) * scale), backgroundColor: '#D9EC10' }],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    }

    if (coinSplitChartRef.value) {
        const t = coinTotals.value;
        charts.value.coinSplit = new Chart(coinSplitChartRef.value, {
            type: 'doughnut',
            data: {
                labels: ['Redeemed', 'Outstanding'],
                datasets: [{ data: [t.redeemed, t.outstanding], backgroundColor: ['#22C55E', '#F59E0B'] }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }
};

onMounted(() => {
    nextTick(renderCharts);
});

watch(coinTotals, () => nextTick(renderCharts));
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card metric dark rounded-3xl p-5">
                <p class="font-bold text-slate-300">Coins Purchased</p>
                <h3 class="text-5xl font-black">{{ fmt(coinTotals.purchased) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Coins Outstanding</p>
                <h3 class="text-4xl font-black">{{ num(coinTotals.outstanding) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">LinkUp 50% Share</p>
                <h3 class="text-4xl font-black text-emerald-600">{{ fmt(coinTotals.linkup) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Creator 50% Share</p>
                <h3 class="text-4xl font-black text-sky-600">{{ fmt(coinTotals.creator) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Coins by Country</h3>
                <div class="h-64">
                    <canvas ref="coinsCountryChartRef"></canvas>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Coin Split / Treasury</h3>
                <div class="h-64">
                    <canvas ref="coinSplitChartRef"></canvas>
                </div>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Coin Treasury Ledger</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Country</th>
                            <th>Purchased</th>
                            <th>Redeemed</th>
                            <th>Outstanding</th>
                            <th>LinkUp 50%</th>
                            <th>Creator 50%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!countryCoinRows.length">
                            <td colspan="6" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="c in countryCoinRows" :key="c.country" class="border-t">
                            <td class="py-3 font-black">{{ c.country }}</td>
                            <td>{{ fmt(c.purchased) }}</td>
                            <td>{{ fmt(c.redeemed) }}</td>
                            <td class="font-bold text-amber-600">{{ fmt(c.outstanding) }}</td>
                            <td class="text-emerald-600">{{ fmt(c.linkup) }}</td>
                            <td class="text-sky-600">{{ fmt(c.creator) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4">
                <h3 class="text-xl font-black">Dormant Coins & Breakage</h3>
                <p class="text-slate-500">
                    Coins purchased but unspent past the dormancy window. Held as a liability; a portion may be recognized as breakage revenue
                    (subject to local unclaimed-property / escheatment rules).
                </p>
            </div>
            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-bold text-slate-500">Outstanding (Liability)</p>
                    <h4 class="text-2xl font-black">{{ fmt(dormantSummary.outstanding) }}</h4>
                </div>
                <div class="rounded-2xl bg-amber-50 p-4">
                    <p class="font-bold text-amber-700">Dormant Coins</p>
                    <h4 class="text-2xl font-black">{{ fmt(dormantSummary.dormant) }}</h4>
                    <p class="text-xs text-slate-500">Inactive &gt; {{ dormMonths }} months · {{ dormSharePct }}% of outstanding</p>
                </div>
                <div class="rounded-2xl bg-green-50 p-4">
                    <p class="font-bold text-green-700">Breakage-Eligible Revenue</p>
                    <h4 class="text-2xl font-black">{{ fmt(dormantSummary.breakage) }}</h4>
                </div>
                <div class="rounded-2xl bg-sky-50 p-4">
                    <p class="font-bold text-sky-700">Escheatment Reserve</p>
                    <h4 class="text-2xl font-black">{{ fmt(dormantSummary.escheat) }}</h4>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-bold text-slate-500">Redemption Rate</p>
                    <h4 class="text-2xl font-black">{{ dormantSummary.redemptionRate.toFixed(1) }}%</h4>
                </div>
            </div>
            <div class="mb-5 grid grid-cols-1 items-end gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm font-bold text-slate-600">Dormancy window (months)</label>
                    <input v-model.number="dormMonths" type="number" min="1" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 font-bold" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-600">% of outstanding dormant</label>
                    <input
                        v-model.number="dormSharePct"
                        type="number"
                        step="1"
                        min="0"
                        max="100"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 font-bold"
                    />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-600">Breakage recognizable %</label>
                    <input
                        v-model.number="dormBreakagePct"
                        type="number"
                        step="1"
                        min="0"
                        max="100"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 font-bold"
                    />
                </div>
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Country</th>
                            <th>Outstanding</th>
                            <th>Dormant</th>
                            <th>Breakage-Eligible</th>
                            <th>Escheatment Reserve</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!dormantByCountry.length">
                            <td colspan="5" class="py-6 text-center text-slate-400 font-bold">No records.</td>
                        </tr>
                        <tr v-for="c in dormantByCountry" :key="c.country" class="border-t">
                            <td class="py-3 font-black">{{ c.country }}</td>
                            <td>{{ fmt(c.outstanding) }}</td>
                            <td class="font-bold text-amber-600">{{ fmt(c.dormant) }}</td>
                            <td class="font-bold text-green-600">{{ fmt(c.breakage) }}</td>
                            <td>{{ fmt(c.escheat) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                <b>Recommended policy:</b> ① Hold outstanding coins as a deferred-revenue liability. ② Flag coins inactive past the dormancy
                window. ③ Re-engage users (nudges, bonus gift events) before any write-off. ④ Recognize breakage revenue only on the
                non-escheatable portion (ASC 606). ⑤ Reserve the remainder for unclaimed-property / escheatment by jurisdiction. No forced
                expiry or fees where prohibited by law.
            </div>
        </div>
    </div>
</template>

<style scoped>
.metric {
    position: relative;
    overflow: hidden;
}
.metric:after {
    content: "";
    position: absolute;
    right: -40px;
    top: -40px;
    width: 135px;
    height: 135px;
    border-radius: 999px;
    background: rgba(40, 168, 255, 0.09);
}
.metric.dark {
    background: linear-gradient(135deg, #07111f, #13233d);
    color: #fff;
}
.metric.dark:after {
    background: rgba(217, 236, 16, 0.14);
}
</style>
