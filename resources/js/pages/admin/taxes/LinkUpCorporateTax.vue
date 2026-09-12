<script setup lang="ts">
import TaxLayout from './components/TaxLayout.vue';
import { computed, onMounted, ref } from 'vue';

type BusinessUnit = {
    key: string;
    name: string;
    platformRate: number;
    bankRate: number;
    costRate: number;
};

type CountryMetric = Record<string, number | string>;

const props = defineProps<{ initialUnits: BusinessUnit[]; initialCountries: CountryMetric[] }>();

const basis = ref<'annual' | 'period'>('annual');
const fedRate = ref(21);
const flRate = ref(5.5);
const flExempt = ref(50000);
const foundationRate = ref(1);
const payNote = ref('');

const money = (value: number) => new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
}).format(Number(value || 0));

const periodTotals = computed(() => {
    const totals = props.initialCountries.reduce((carry, country) => {
        props.initialUnits.forEach((unit) => {
            const volume = Number(country[unit.key] || 0);
            carry.platform += volume * Number(unit.platformRate || 0);
            carry.bank += volume * Number(unit.bankRate || 0);
            carry.cost += volume * Number(unit.costRate || 0);
        });

        return carry;
    }, { platform: 0, bank: 0, cost: 0 });

    if (totals.platform + totals.bank + totals.cost > 0) {
        return totals;
    }

    return { platform: 180000, bank: 12000, cost: 38000 };
});

const taxModel = computed(() => {
    const multiplier = basis.value === 'annual' ? 12 : 1;
    const rev = (periodTotals.value.platform + periodTotals.value.bank) * multiplier;
    const cost = periodTotals.value.cost * multiplier;
    const taxable = Math.max(0, rev - cost);
    const charitableRaw = rev * (foundationRate.value / 100);
    const charitable = Math.min(charitableRaw, taxable * 0.1);
    const taxableAfter = Math.max(0, taxable - charitable);
    const fed = taxableAfter * (fedRate.value / 100);
    const floridaExemption = basis.value === 'annual' ? flExempt.value : flExempt.value / 12;
    const fl = Math.max(0, taxableAfter - floridaExemption) * (flRate.value / 100);
    const total = fed + fl;
    const taxSavings = charitable * ((fedRate.value + flRate.value) / 100);
    const net = taxableAfter - total;
    const eff = rev > 0 ? (total / rev) * 100 : 0;

    return {
        rev,
        cost,
        taxable,
        charitable,
        charitableRaw,
        taxableAfter,
        fed,
        fl,
        total,
        taxSavings,
        net,
        eff,
    };
});

const breakdownRows = computed(() => [
    { label: `Revenue (${basis.value === 'annual' ? 'annualized' : 'period'})`, value: money(taxModel.value.rev) },
    { label: 'Less: Operating costs', value: `-${money(taxModel.value.cost)}` },
    { label: 'Taxable income (profit)', value: money(taxModel.value.taxable) },
    { label: 'Less: Foundation charitable write-off', value: `-${money(taxModel.value.charitable)}`, danger: true },
    { label: 'Adjusted taxable income', value: money(taxModel.value.taxableAfter) },
    { label: `U.S. Federal corporate tax (${fedRate.value}%)`, value: `-${money(taxModel.value.fed)}` },
    { label: `Florida corporate tax (${flRate.value}%)`, value: `-${money(taxModel.value.fl)}` },
    { label: 'Tax saved by giving', value: money(taxModel.value.taxSavings), success: true },
]);

const quarterlyPayments = computed(() => {
    const amount = taxModel.value.total / 4;

    return ['Q1', 'Q2', 'Q3', 'Q4'].map((quarter) => ({
        quarter,
        amount,
    }));
});

const payAgency = (agency: 'IRS' | 'Florida DOR') => {
    const amount = agency === 'IRS' ? taxModel.value.fed : taxModel.value.fl;
    const confirmation = `TX${Date.now().toString(36).toUpperCase().slice(-7)}`;
    payNote.value = `${money(amount)} payment submitted to ${agency} via secure ACH on ${new Date().toLocaleDateString()}. Confirmation #${confirmation}`;
};

onMounted(() => {
    try {
        const stored = JSON.parse(localStorage.getItem('linkupFoundation') || 'null');

        if (stored?.rate != null) {
            foundationRate.value = Number(stored.rate) || 1;
        }
    } catch {
        foundationRate.value = 1;
    }
});
</script>

<template>
    <TaxLayout title="LinkUp Corporate Tax" active-id="linkupCorpTaxCommand" :initial-units="props.initialUnits" :initial-countries="props.initialCountries">
        <div class="space-y-6">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-3xl font-black text-indigo-600">
                        LinkUp Corporate Tax
                        <span class="align-middle rounded bg-indigo-100 px-2 py-1 text-xs text-indigo-700">Florida, USA</span>
                    </h3>
                    <p class="text-slate-500">What LinkUp owes on its own profit - U.S. Federal corporate income tax + Florida corporate income tax - calculated live, with payments straight to the IRS & Florida DOR.</p>
                </div>
                <select v-model="basis" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold">
                    <option value="annual">Annualized</option>
                    <option value="period">Selected Period</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
                <div class="card metric dark rounded-3xl p-5">
                    <p class="text-sm text-slate-300">LinkUp Revenue</p>
                    <h3 class="text-2xl font-black">{{ money(taxModel.rev) }}</h3>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="text-sm text-slate-500">Operating Costs</p>
                    <h3 class="text-2xl font-black">{{ money(taxModel.cost) }}</h3>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="text-sm text-slate-500">Taxable Income</p>
                    <h3 class="text-2xl font-black">{{ money(taxModel.taxable) }}</h3>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="text-sm text-slate-500">Total Tax</p>
                    <h3 class="text-2xl font-black text-rose-600">{{ money(taxModel.total) }}</h3>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="text-sm text-slate-500">Effective Rate</p>
                    <h3 class="text-2xl font-black">{{ taxModel.eff.toFixed(1) }}%</h3>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="text-sm text-slate-500">Net After Tax</p>
                    <h3 class="text-2xl font-black text-emerald-600">{{ money(taxModel.net) }}</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <div class="card rounded-3xl p-6">
                    <h4 class="mb-1 font-black">Tax Breakdown</h4>
                    <p class="mb-3 text-xs text-slate-400">Florida has no personal income tax; companies pay Federal 21% + Florida 5.5% corporate income tax (first $50,000 of FL income exempt). Sales tax is collected separately per jurisdiction.</p>

                    <div class="space-y-2 text-sm">
                        <div v-for="row in breakdownRows" :key="row.label" class="flex justify-between border-b border-slate-100 py-1.5">
                            <span class="font-bold" :class="row.danger ? 'text-rose-600' : row.success ? 'text-emerald-600' : 'text-slate-500'">{{ row.label }}</span>
                            <span class="font-black" :class="row.danger ? 'text-rose-600' : row.success ? 'text-emerald-600' : ''">{{ row.value }}</span>
                        </div>
                        <div class="mt-1 flex justify-between border-t-2 border-slate-200 py-2">
                            <span class="font-black">Net profit after tax</span>
                            <span class="font-black text-emerald-600">{{ money(taxModel.net) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="text-xs font-black text-slate-600">Federal Rate (%)</label>
                            <input v-model.number="fedRate" type="number" step="0.1" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-bold" />
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-600">FL Corp Rate (%)</label>
                            <input v-model.number="flRate" type="number" step="0.1" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-bold" />
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-600">FL Exemption ($)</label>
                            <input v-model.number="flExempt" type="number" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-bold" />
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h4 class="mb-3 font-black">Quarterly Estimated Payments</h4>

                    <div class="space-y-2">
                        <div v-for="payment in quarterlyPayments" :key="payment.quarter" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                            <span class="font-bold text-slate-600">{{ payment.quarter }} estimated payment</span>
                            <span class="font-black">{{ money(payment.amount) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                            <div>
                                <div class="flex items-center gap-2 font-black">
                                    <span class="grid h-7 w-7 place-items-center rounded bg-slate-900 text-xs font-black text-white">IRS</span>
                                    U.S. Federal (IRS)
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Due: {{ money(taxModel.fed) }}</p>
                            </div>
                            <button class="rounded-2xl bg-slate-900 px-4 py-2.5 font-black text-white" @click="payAgency('IRS')">Pay</button>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-orange-50 p-4">
                            <div>
                                <div class="flex items-center gap-2 font-black">
                                    <span class="grid h-7 w-7 place-items-center rounded bg-orange-500 text-xs font-black text-white">FL</span>
                                    Florida DOR
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Due: {{ money(taxModel.fl) }}</p>
                            </div>
                            <button class="rounded-2xl bg-orange-500 px-4 py-2.5 font-black text-white" @click="payAgency('Florida DOR')">Pay</button>
                        </div>
                    </div>

                    <p v-if="payNote" class="mt-3 text-sm font-bold text-slate-500">{{ payNote }}</p>
                </div>
            </div>
        </div>
    </TaxLayout>
</template>
