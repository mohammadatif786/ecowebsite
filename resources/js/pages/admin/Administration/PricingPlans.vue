<script setup lang="ts">
const pricingRules = [
    { module: 'Domestic Remittance', country: 'All', rule: '1.0% fee, min $0.50, max $5', linkup: '1.00%', bank: '0.25%', fx: '0%' },
    { module: 'International Remittance', country: 'All', rule: '2.5% LinkUp + 1% bank + 1% FX', linkup: '2.50%', bank: '1.00%', fx: '1.00%' },
    { module: 'Events', country: 'Bahamas', rule: '6.5% ticket fee + add-on fees', linkup: '6.50%', bank: '2.50%', fx: '0%' },
    { module: 'Merchant Pay', country: 'All', rule: '2.0% LinkUp + 3.0% processing', linkup: '2.00%', bank: '3.00%', fx: '0%' },
    { module: 'LinkUp Live', country: 'All', rule: '50/50 creator split', linkup: '50.00%', bank: '0%', fx: '0%' },
    { module: 'Coins', country: 'All', rule: '50/50 coin split', linkup: '50.00%', bank: '0%', fx: '0%' },
];

const fxRates = [
    { pair: 'USD/BSD', rate: '1.00', spread: '0.25%', exposure: 1250000 },
    { pair: 'USD/JMD', rate: '155.20', spread: '1.00%', exposure: 850000 },
    { pair: 'USD/TTD', rate: '6.78', spread: '1.00%', exposure: 420000 },
    { pair: 'CAD/USD', rate: '0.73', spread: '0.85%', exposure: 610000 },
    { pair: 'USD/COP', rate: '3925.00', spread: '1.25%', exposure: 390000 },
];

const money = (value: number) => new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
}).format(value);
</script>

<template>
    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Settings / Pricing Engine</h3>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3">Module</th>
                            <th>Country</th>
                            <th>Rule</th>
                            <th>LinkUp</th>
                            <th>Bank</th>
                            <th>FX</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="rule in pricingRules" :key="`${rule.module}-${rule.country}`" class="border-t">
                            <td class="py-3 font-black">{{ rule.module }}</td>
                            <td>{{ rule.country }}</td>
                            <td>{{ rule.rule }}</td>
                            <td class="font-bold text-sky-600">{{ rule.linkup }}</td>
                            <td class="font-bold text-amber-600">{{ rule.bank }}</td>
                            <td class="font-bold text-green-600">{{ rule.fx }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">Currency & FX Management</h3>
            <div class="space-y-3">
                <div v-for="rate in fxRates" :key="rate.pair" class="flex justify-between rounded-2xl bg-slate-50 p-4">
                    <div>
                        <b>{{ rate.pair }}</b>
                        <p class="text-sm text-slate-600">Rate: {{ rate.rate }} - Spread: {{ rate.spread }}</p>
                    </div>
                    <span class="font-black">{{ money(rate.exposure) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
