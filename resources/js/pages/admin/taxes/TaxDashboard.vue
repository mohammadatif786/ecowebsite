<script setup lang="ts">
import TaxLayout from './components/TaxLayout.vue';
import { computed, ref } from 'vue';

type EventTaxRow = {
    event: string;
    organizer: string;
    country: string;
    gross: number;
    rate: number;
    tax_collected: number;
    transactions: number;
    status: string;
};

type MarketplaceTaxRow = {
    country: string;
    rate: number;
    sales: number;
    tax_collected: number;
    transactions: number;
    status: string;
};

type TaxRule = {
    country: string;
    code: string;
    tax_type: string;
    rate: number;
};

type TaxSummary = {
    event_tax: number;
    marketplace_tax: number;
    total_tax: number;
    jurisdictions: number;
    transactions: number;
    events: number;
};

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    eventTaxRows: EventTaxRow[];
    marketplaceTaxRows: MarketplaceTaxRow[];
    taxRules: TaxRule[];
    taxSummary: TaxSummary;
}>();

const selectedCountry = ref('All Countries');

const countries = computed(() => {
    return [...new Set([
        ...props.eventTaxRows.map((row) => row.country),
        ...props.marketplaceTaxRows.map((row) => row.country),
        ...props.taxRules.map((row) => row.country),
    ].filter(Boolean))].sort();
});

const filteredEventRows = computed(() => {
    if (selectedCountry.value === 'All Countries') {
        return props.eventTaxRows;
    }

    return props.eventTaxRows.filter((row) => row.country === selectedCountry.value);
});

const filteredMarketplaceRows = computed(() => {
    if (selectedCountry.value === 'All Countries') {
        return props.marketplaceTaxRows;
    }

    return props.marketplaceTaxRows.filter((row) => row.country === selectedCountry.value);
});

const filteredTaxRules = computed(() => {
    if (selectedCountry.value === 'All Countries') {
        return props.taxRules;
    }

    return props.taxRules.filter((row) => row.country === selectedCountry.value);
});

const totals = computed(() => {
    const eventTax = filteredEventRows.value.reduce((sum, row) => sum + Number(row.tax_collected || 0), 0);
    const marketplaceTax = filteredMarketplaceRows.value.reduce((sum, row) => sum + Number(row.tax_collected || 0), 0);
    const eventTransactions = filteredEventRows.value.reduce((sum, row) => sum + Number(row.transactions || 0), 0);
    const marketplaceTransactions = filteredMarketplaceRows.value.reduce((sum, row) => sum + Number(row.transactions || 0), 0);
    const jurisdictions = new Set([
        ...filteredEventRows.value.map((row) => row.country),
        ...filteredMarketplaceRows.value.map((row) => row.country),
        ...filteredTaxRules.value.map((row) => row.country),
    ].filter(Boolean)).size;

    return {
        eventTax,
        marketplaceTax,
        total: eventTax + marketplaceTax,
        transactions: eventTransactions + marketplaceTransactions,
        jurisdictions,
        events: filteredEventRows.value.length,
    };
});

const statusClass = (status: string) => {
    const normalized = String(status || '').toLowerCase();

    if (normalized === 'collected' || normalized === 'active') {
        return 'bg-green-50 text-green-700';
    }

    if (normalized === 'pending') {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-slate-100 text-slate-600';
};

const formatPercent = (value: number) => `${Number(value || 0).toFixed(2)}%`;

const exportCSV = () => {
    const rows = [
        ['Stream', 'Name/Country', 'Organizer', 'Gross/Sales', 'Tax Collected', 'Transactions', 'Status'],
        ...filteredEventRows.value.map((row) => ['Events', row.event, row.organizer, row.gross, row.tax_collected, row.transactions, row.status]),
        ...filteredMarketplaceRows.value.map((row) => ['Marketplace', row.country, '', row.sales, row.tax_collected, row.transactions, row.status]),
    ];

    const csvContent = rows.map((row) => row.map((cell) => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'linkup_tax_dashboard.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};
</script>

<template>
    <TaxLayout title="Tax Dashboard" active-id="taxDashboardCommand" :initial-units="props.initialUnits" :initial-countries="props.initialCountries" v-slot="{ fmt, num }">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-3xl font-black">Tax Dashboard</h3>
                    <p class="text-slate-500">Live summary of tax collected, remitted, outstanding and jurisdiction exposure from real events, orders, and tax rules.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <select v-model="selectedCountry" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold outline-none focus:border-purple-400">
                        <option>All Countries</option>
                        <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
                    </select>
                    <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white" @click="exportCSV">Export CSV</button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="card metric dark rounded-3xl p-5">
                    <p class="font-bold text-slate-300">Total Tax Collected</p>
                    <h3 class="mt-2 text-4xl font-black">{{ fmt(totals.total) }}</h3>
                    <p class="text-sm font-bold text-lime-300">Events + marketplace orders</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="font-bold text-slate-500">Event Tax</p>
                    <h3 class="mt-2 text-4xl font-black text-emerald-600">{{ fmt(totals.eventTax) }}</h3>
                    <p class="text-sm text-slate-500">{{ num(totals.events) }} taxable event groups</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="font-bold text-slate-500">Marketplace Tax</p>
                    <h3 class="mt-2 text-4xl font-black text-amber-500">{{ fmt(totals.marketplaceTax) }}</h3>
                    <p class="text-sm text-slate-500">{{ num(totals.transactions) }} total transactions</p>
                </div>
                <div class="card rounded-3xl p-5">
                    <p class="font-bold text-slate-500">Tax Jurisdictions</p>
                    <h3 class="mt-2 text-4xl font-black">{{ num(totals.jurisdictions) }}</h3>
                    <p class="text-sm text-slate-500">{{ num(filteredTaxRules.length) }} configured tax rules</p>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <h3 class="mb-1 text-xl font-black">Tax Report by Event</h3>
                <p class="mb-4 text-sm text-slate-500">Each row groups ticket sales by event and uses `ticket_sales.event_tax` from the database.</p>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Event Name</th>
                                <th>Organizer</th>
                                <th>Jurisdiction</th>
                                <th>Gross Sales</th>
                                <th>Tax Collected</th>
                                <th>Txns</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in filteredEventRows" :key="row.event" class="border-t">
                                <td class="py-3 font-black">{{ row.event }}</td>
                                <td><span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-black text-purple-700">{{ row.organizer }}</span></td>
                                <td>{{ row.country }} <span class="text-xs text-slate-400">({{ formatPercent(row.rate) }})</span></td>
                                <td>{{ fmt(row.gross) }}</td>
                                <td class="font-black text-emerald-700">{{ fmt(row.tax_collected) }}</td>
                                <td>{{ num(row.transactions) }}</td>
                                <td><span class="rounded-full px-2 py-0.5 text-xs font-black" :class="statusClass(row.status)">{{ row.status }}</span></td>
                            </tr>
                            <tr v-if="!filteredEventRows.length">
                                <td colspan="7" class="py-10 text-center text-sm font-bold text-slate-400">No event tax records found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <h3 class="mb-1 text-xl font-black">Marketplace Tax by Country</h3>
                <p class="mb-4 text-sm text-slate-500">Collected from real marketplace orders using `orders.tax_amount` and order country.</p>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2">Country</th>
                                <th>Effective Rate</th>
                                <th>Marketplace Sales</th>
                                <th>Tax Collected</th>
                                <th>Txns</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in filteredMarketplaceRows" :key="row.country" class="border-t">
                                <td class="py-3 font-black">{{ row.country }}</td>
                                <td>{{ formatPercent(row.rate) }}</td>
                                <td>{{ fmt(row.sales) }}</td>
                                <td class="font-black text-emerald-700">{{ fmt(row.tax_collected) }}</td>
                                <td>{{ num(row.transactions) }}</td>
                                <td><span class="rounded-full px-2 py-0.5 text-xs font-black" :class="statusClass(row.status)">{{ row.status }}</span></td>
                            </tr>
                            <tr v-if="!filteredMarketplaceRows.length">
                                <td colspan="6" class="py-10 text-center text-sm font-bold text-slate-400">No marketplace tax records found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <h3 class="mb-1 text-xl font-black">Configured Tax Rules</h3>
                <p class="mb-4 text-sm text-slate-500">Rules come from the existing `taxes` table and drive jurisdiction tax setup.</p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div v-for="rule in filteredTaxRules" :key="`${rule.country}-${rule.code}`" class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-black">{{ rule.country }}</h4>
                            <span class="rounded-lg bg-white px-2 py-1 text-xs font-black text-slate-500">{{ rule.code }}</span>
                        </div>
                        <p class="mt-3 text-sm font-bold text-purple-700">{{ rule.tax_type }}</p>
                        <p class="text-2xl font-black">{{ rule.tax_type === 'percentage' ? formatPercent(rule.rate) : fmt(rule.rate) }}</p>
                    </div>
                    <div v-if="!filteredTaxRules.length" class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm font-bold text-slate-400 md:col-span-2 xl:col-span-4">
                        No tax rules configured for this filter
                    </div>
                </div>
            </div>
        </div>
    </TaxLayout>
</template>
