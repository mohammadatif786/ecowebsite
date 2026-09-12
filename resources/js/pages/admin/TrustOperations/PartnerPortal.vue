<script setup lang="ts">
import { computed } from 'vue';
import { BadgeCheck, CreditCard, Landmark, Network, WalletCards } from 'lucide-vue-next';

type UnitKey = 'tickets' | 'subscriptions' | 'marketplace' | 'eats' | 'merchantPay' | 'wallet' | 'live' | 'ads' | 'wellness' | 'cookouts' | 'linkup360';

type Unit = {
    key: UnitKey;
    name: string;
    platformRate: number;
    bankRate: number;
    costRate: number;
};

type Country = Record<UnitKey, number> & {
    country: string;
    users: number;
    merchants: number;
    organizers: number;
};

type PartnerRow = {
    partner: string;
    volume: number;
    revenue: number;
    transactions: number;
    forecast: number;
    status: 'Active' | 'Connected' | 'Monitoring';
    icon: any;
};

const SCOTIA_SHARE = 0.4;

const units: Unit[] = [
    { key: 'tickets', name: 'Ticket Sales', platformRate: 0.065, bankRate: 0, costRate: 0.01 },
    { key: 'subscriptions', name: 'Subscriptions', platformRate: 1, bankRate: 0, costRate: 0.04 },
    { key: 'marketplace', name: 'Marketplace', platformRate: 0.05, bankRate: 0, costRate: 0.01 },
    { key: 'eats', name: 'LinkUp Eats', platformRate: 0.075, bankRate: 0, costRate: 0.025 },
    { key: 'merchantPay', name: 'Merchant Pay', platformRate: 0.02, bankRate: 0, costRate: 0.007 },
    { key: 'wallet', name: 'Wallet & Money Movement', platformRate: 0.025, bankRate: 0.0175, costRate: 0.008 },
    { key: 'live', name: 'LinkUp Live', platformRate: 0.5, bankRate: 0, costRate: 0.08 },
    { key: 'ads', name: 'Advertising Revenue', platformRate: 1, bankRate: 0, costRate: 0.12 },
    { key: 'wellness', name: 'Wellness & Spa', platformRate: 0.0675, bankRate: 0, costRate: 0.015 },
    { key: 'cookouts', name: 'Cookouts', platformRate: 0.0675, bankRate: 0, costRate: 0.015 },
    { key: 'linkup360', name: 'LinkUp 360 News Ads', platformRate: 1, bankRate: 0, costRate: 0.15 },
];

const countries: Country[] = [
    { country: 'Bahamas', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000 },
    { country: 'Jamaica', users: 280000, merchants: 1200, organizers: 250, tickets: 720000, subscriptions: 90000, marketplace: 410000, eats: 650000, merchantPay: 1600000, wallet: 950000, live: 180000, ads: 45000, wellness: 190000, cookouts: 140000, linkup360: 55000 },
    { country: 'Trinidad & Tobago', users: 190000, merchants: 900, organizers: 190, tickets: 610000, subscriptions: 70000, marketplace: 380000, eats: 520000, merchantPay: 1350000, wallet: 840000, live: 160000, ads: 39000, wellness: 150000, cookouts: 120000, linkup360: 47000 },
    { country: 'Barbados', users: 70000, merchants: 380, organizers: 95, tickets: 330000, subscriptions: 42000, marketplace: 190000, eats: 260000, merchantPay: 720000, wallet: 460000, live: 85000, ads: 21000, wellness: 90000, cookouts: 65000, linkup360: 26000 },
    { country: 'Guyana', users: 130000, merchants: 620, organizers: 140, tickets: 420000, subscriptions: 56000, marketplace: 270000, eats: 360000, merchantPay: 980000, wallet: 600000, live: 120000, ads: 28000, wellness: 100000, cookouts: 82000, linkup360: 31000 },
    { country: 'Dominican Republic', users: 220000, merchants: 1000, organizers: 210, tickets: 520000, subscriptions: 68000, marketplace: 310000, eats: 430000, merchantPay: 1100000, wallet: 710000, live: 150000, ads: 36000, wellness: 130000, cookouts: 99000, linkup360: 41000 },
    { country: 'United States', users: 450000, merchants: 2400, organizers: 410, tickets: 1280000, subscriptions: 180000, marketplace: 820000, eats: 0, merchantPay: 2500000, wallet: 1750000, live: 420000, ads: 160000, wellness: 320000, cookouts: 180000, linkup360: 120000 },
    { country: 'Canada', users: 260000, merchants: 1400, organizers: 260, tickets: 840000, subscriptions: 130000, marketplace: 560000, eats: 0, merchantPay: 1600000, wallet: 1100000, live: 300000, ads: 110000, wellness: 210000, cookouts: 120000, linkup360: 85000 },
    { country: 'Brazil', users: 370000, merchants: 1900, organizers: 350, tickets: 960000, subscriptions: 150000, marketplace: 640000, eats: 0, merchantPay: 1850000, wallet: 1300000, live: 360000, ads: 130000, wellness: 260000, cookouts: 140000, linkup360: 97000 },
    { country: 'Colombia', users: 240000, merchants: 1300, organizers: 240, tickets: 690000, subscriptions: 105000, marketplace: 470000, eats: 0, merchantPay: 1250000, wallet: 860000, live: 240000, ads: 90000, wellness: 180000, cookouts: 95000, linkup360: 70000 },
];

const fmt = (value: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
const num = (value: number) => new Intl.NumberFormat('en-US').format(Math.round(value));

const unitVolume = (unit: Unit) => countries.reduce((sum, country) => sum + country[unit.key], 0);
const unitFin = (unit: Unit) => {
    const gross = unitVolume(unit);
    const platform = gross * unit.platformRate;
    const bank = gross * unit.bankRate;
    const cost = gross * unit.costRate;
    return { gross, platform, bank, cost, net: platform - cost };
};

const totals = computed(() => {
    return units.reduce(
        (acc, unit) => {
            const gross = unitVolume(unit);
            acc.gross += gross;
            acc.platform += gross * unit.platformRate;
            acc.bank += gross * unit.bankRate;
            acc.cost += gross * unit.costRate;
            return acc;
        },
        { gross: 0, platform: 0, bank: 0, cost: 0 },
    );
});

const partnerRows = computed<PartnerRow[]>(() => {
    const merchantPay = units.find((unit) => unit.key === 'merchantPay')!;
    const wallet = units.find((unit) => unit.key === 'wallet')!;
    const merchantPayFin = unitFin(merchantPay);
    const walletFin = unitFin(wallet);

    return [
        { partner: 'Scotiabank', volume: merchantPayFin.gross, revenue: merchantPayFin.bank, transactions: 85000, forecast: merchantPayFin.bank * SCOTIA_SHARE, status: 'Connected', icon: Landmark },
        { partner: 'Visa / Mastercard', volume: totals.value.gross * 0.42, revenue: totals.value.bank * 0.45, transactions: 120000, forecast: totals.value.bank * 0.45 * SCOTIA_SHARE, status: 'Active', icon: CreditCard },
        { partner: 'ACH Partner', volume: totals.value.gross * 0.12, revenue: totals.value.bank * 0.18, transactions: 24000, forecast: totals.value.bank * 0.18 * SCOTIA_SHARE, status: 'Monitoring', icon: Network },
        { partner: 'Wallet Bank Rail', volume: walletFin.gross, revenue: walletFin.bank, transactions: 64000, forecast: walletFin.bank * SCOTIA_SHARE, status: 'Connected', icon: WalletCards },
    ];
});

const summary = computed(() => ({
    partners: partnerRows.value.length,
    volume: partnerRows.value.reduce((sum, row) => sum + row.volume, 0),
    revenue: partnerRows.value.reduce((sum, row) => sum + row.revenue, 0),
    transactions: partnerRows.value.reduce((sum, row) => sum + row.transactions, 0),
}));

const statusClass = (status: PartnerRow['status']) => {
    if (status === 'Active') return 'bg-green-50 text-green-700';
    if (status === 'Connected') return 'bg-sky-50 text-sky-700';
    return 'bg-amber-50 text-amber-700';
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Partner Portal</h3>
                <p class="mt-1 text-slate-500">Bank and payment partner volume, processing revenue, transaction load, and forecast share.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-slate-500">Partners</p>
                    <b class="text-xl text-slate-950">{{ summary.partners }}</b>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-sky-700">Volume</p>
                    <b class="text-xl text-sky-700">{{ fmt(summary.volume) }}</b>
                </div>
                <div class="rounded-2xl border border-green-100 bg-green-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-green-700">Revenue</p>
                    <b class="text-xl text-green-700">{{ fmt(summary.revenue) }}</b>
                </div>
                <div class="rounded-2xl border border-purple-100 bg-purple-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-purple-700">Transactions</p>
                    <b class="text-xl text-purple-700">{{ num(summary.transactions) }}</b>
                </div>
            </div>
        </div>

        <section class="card rounded-3xl p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-black text-slate-950">Bank / Partner Portal</h3>
                    <p class="text-sm text-slate-500">Partner rows follow the same revenue formulas used in the standalone dashboard.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-950 px-3 py-1.5 text-xs font-black text-white">
                    <BadgeCheck class="h-4 w-4" />
                    Reconciled
                </span>
            </div>

            <div class="overflow-x-auto scrollbar">
                <table class="w-full text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3">Partner</th>
                            <th>Volume</th>
                            <th>Revenue</th>
                            <th>Transactions</th>
                            <th>Forecast</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in partnerRows" :key="row.partner" class="border-t">
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-950 text-white">
                                        <component :is="row.icon" class="h-5 w-5" />
                                    </div>
                                    <b>{{ row.partner }}</b>
                                </div>
                            </td>
                            <td class="font-black text-slate-900">{{ fmt(row.volume) }}</td>
                            <td class="font-black text-green-700">{{ fmt(row.revenue) }}</td>
                            <td>{{ num(row.transactions) }}</td>
                            <td class="font-black text-purple-700">{{ fmt(row.forecast) }}</td>
                            <td>
                                <span class="rounded-full px-2.5 py-1 text-xs font-black" :class="statusClass(row.status)">
                                    {{ row.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
