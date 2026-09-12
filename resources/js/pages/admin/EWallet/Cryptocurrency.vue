<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';

const props = withDefaults(defineProps<{
    fmt: (n: number) => string;
    initialCryptoPurchases: any[];
}>(), {
    initialCryptoPurchases: () => [],
});

const cryptoProviders: Record<string, any> = {
    Transak: { coverage: '160+ countries · 135+ pay methods', fee: '0.5–3%', lic: 'Global MSB · white-label KYC' },
    MoonPay: { coverage: '150+ countries', fee: '1.5–4.5%', lic: 'NY BitLicense · MiCA · PCI DSS 4.0' },
    'Ramp Network': { coverage: 'Europe-strong, 150+ countries', fee: '0.9–2.5%', lic: 'EU/UK e-money' },
    'Stripe Crypto': { coverage: 'High-acceptance conversions', fee: '~1.5%', lic: 'Stripe-regulated on-ramp' },
    'Zero Hash': { coverage: 'Institutional API (US)', fee: 'Custom', lic: 'CFTC-regulated custody & settlement' },
};

const cryptoAssetPrices: Record<string, number> = { BTC: 67000, ETH: 3500, USDC: 1, USDT: 1, SOL: 150 };

const config = ref({
    enabled: false,
    provider: 'Transak',
    apiKey: '',
    env: 'Sandbox',
    fee: 1.0,
    custody: 'Provider-custodied (non-custodial to LinkUp)',
});

const purchases = ref([...(props.initialCryptoPurchases || [])]);
const showConfigNote = ref(false);

const buyForm = ref({
    asset: 'BTC',
    amount: 100,
});

onMounted(() => {
    try {
        const saved = JSON.parse(localStorage.getItem('linkupCryptoConfig') || '{}');
        config.value = { ...config.value, ...saved };
    } catch (e) {}
});

const saveConfig = () => {
    try {
        localStorage.setItem('linkupCryptoConfig', JSON.stringify(config.value));
        showConfigNote.value = true;
        setTimeout(() => (showConfigNote.value = false), 3000);
    } catch (e) {}
};

const quote = computed(() => {
    const amt = buyForm.value.amount || 0;
    const asset = buyForm.value.asset;
    const px = cryptoAssetPrices[asset] || 1;
    const provFee = amt * 0.015;
    const linkupFee = amt * (config.value.fee / 100);
    const coins = Math.max(0, amt - provFee - linkupFee) / px;
    return { amt, asset, coins, provFee, linkupFee };
});

const metrics = computed(() => {
    const vol = purchases.value.reduce((s, x) => s + x.usd, 0);
    const rev = vol * (config.value.fee / 100);
    return {
        volume: props.fmt(vol),
        revenue: props.fmt(rev),
        provider: cryptoProviders[config.value.provider] || {},
    };
});

const cryptoBuy = () => {
    if (!config.value.enabled) {
        alert('Enable the Crypto module first (toggle at top).');
        return;
    }
    const q = quote.value;
    if (!q.amt) {
        alert('Enter a USD amount.');
        return;
    }
    purchases.value.unshift({
        ref: 'CRX-' + Math.floor(5000 + Math.random() * 5000),
        user: 'Demo User',
        asset: q.asset,
        usd: q.amt,
        crypto: q.coins,
        provFee: q.provFee,
        markup: q.linkupFee,
        status: 'Processing',
    });
    alert(`Crypto purchase initiated via ${config.value.provider} (${config.value.env}).\nKYC, payment & settlement handled by the on-ramp provider.`);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Cryptocurrency — Buy & On-Ramp</h3>
                <p class="text-slate-500">
                    Fiat-to-crypto purchasing powered by a licensed on-ramp partner. KYC, custody & settlement handled by the provider; LinkUp embeds the widget/API and earns a markup.
                </p>
            </div>
            <label class="flex items-center gap-2 font-black">
                <input v-model="config.enabled" @change="saveConfig" type="checkbox" class="h-5 w-5 accent-green-600" />
                Module Enabled
            </label>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">On-Ramp Provider</p>
                <h3 class="text-2xl font-black">{{ config.provider }}</h3>
                <p class="text-xs text-slate-500">{{ metrics.provider.coverage }}</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Buy Volume</p>
                <h3 class="text-3xl font-black">{{ metrics.volume }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">LinkUp Markup</p>
                <h3 class="text-3xl font-black">{{ metrics.revenue }}</h3>
                <p class="text-xs text-slate-500">{{ config.fee }}% markup on buys</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Compliance</p>
                <h3 class="text-2xl font-black text-green-600">KYC by Provider</h3>
                <p class="text-xs text-slate-500">Sanctions & AML screened</p>
            </div>
        </div>

        <!-- Forms -->
        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <!-- Configuration -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">On-Ramp Configuration</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Provider</label>
                        <select v-model="config.provider" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="name in Object.keys(cryptoProviders)" :key="name">{{ name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">API Key (publishable)</label>
                        <input v-model="config.apiKey" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="pk_live_..." />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-bold text-slate-600">Environment</label>
                            <select v-model="config.env" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option>Sandbox</option>
                                <option>Production</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600">LinkUp Markup (%)</label>
                            <input v-model.number="config.fee" type="number" step="0.1" min="0" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600 block">Settlement / Custody</label>
                        <select v-model="config.custody" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option>Provider-custodied (non-custodial to LinkUp)</option>
                            <option>Settle to user wallet</option>
                            <option>Zero Hash custody</option>
                        </select>
                    </div>
                    <button @click="saveConfig" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">Save Configuration</button>
                    <p v-if="showConfigNote" class="mt-3 text-sm font-bold text-slate-500">
                        ✓ On-Ramp configuration saved{{ config.enabled ? ' — module live.' : ' (module disabled).' }}
                    </p>
                </div>
            </div>

            <!-- Buy Simulation -->
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Buy Crypto <span class="text-sm font-bold text-slate-400">(simulation)</span></h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Asset</label>
                        <select v-model="buyForm.asset" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="asset in Object.keys(cryptoAssetPrices)" :key="asset">{{ asset }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Amount (USD)</label>
                        <input v-model.number="buyForm.amount" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" />
                    </div>
                    <div class="space-y-1 rounded-2xl bg-slate-50 p-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Provider fee (~1.5%)</span>
                            <b>{{ fmt(quote.provFee) }}</b>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">LinkUp markup ({{ config.fee }}%)</span>
                            <b>{{ fmt(quote.linkupFee) }}</b>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">You receive</span>
                            <b class="text-green-600">{{ quote.coins.toFixed(6) }} {{ quote.asset }}</b>
                        </div>
                    </div>
                    <button @click="cryptoBuy" class="w-full rounded-2xl bg-green-600 px-5 py-3 font-black text-white">Buy via On-Ramp</button>
                </div>
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Recent Crypto Purchases</h3>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-black text-slate-500 uppercase">
                        <tr>
                            <th class="py-3">Ref</th>
                            <th>User</th>
                            <th>Asset</th>
                            <th>USD</th>
                            <th>Crypto</th>
                            <th>Provider Fee</th>
                            <th>LinkUp Markup</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="x in purchases" :key="x.ref" class="border-t">
                            <td class="py-3 font-black text-purple-600">{{ x.ref }}</td>
                            <td>{{ x.user }}</td>
                            <td class="font-bold">{{ x.asset }}</td>
                            <td class="font-black">{{ fmt(x.usd) }}</td>
                            <td class="font-mono">{{ (x.crypto || 0).toFixed(6) }}</td>
                            <td>{{ fmt(x.provFee) }}</td>
                            <td class="font-black text-green-600">{{ fmt(x.markup) }}</td>
                            <td>
                                <span
                                    :class="x.status === 'Settled' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
                                    class="rounded-full px-3 py-1 text-xs font-black"
                                >
                                    {{ x.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Provider Reference -->
        <div class="card rounded-3xl p-6">
            <h3 class="mb-3 text-xl font-black">Integrated On-Ramp Providers <span class="text-sm font-bold text-slate-400">(market reference)</span></h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div
                    v-for="(v, k) in cryptoProviders"
                    :key="k"
                    class="rounded-2xl border p-4"
                    :class="k === config.provider ? 'border-green-300 bg-green-50' : 'border-slate-200'"
                >
                    <div class="flex items-center justify-between">
                        <b class="text-slate-900">{{ k }}</b>
                        <span v-if="k === config.provider" class="text-[10px] font-black text-green-700 uppercase">Selected</span>
                    </div>
                    <p class="mt-1 text-xs font-bold text-slate-600">{{ v.coverage }}</p>
                    <p class="mt-1 text-xs text-slate-500">Fees: {{ v.fee }}</p>
                    <p class="text-xs text-slate-500">{{ v.lic }}</p>
                </div>
            </div>
            <p class="mt-4 text-[10px] font-bold text-slate-400 leading-relaxed">
                Provider-hosted KYC, sanctions screening & money-transmission licensing (e.g. NY BitLicense, MiCA, MSB / state licenses). This is a pure UX integration — the regulatory burden sits with the on-ramp partner.
            </p>
        </div>
    </div>
</template>
