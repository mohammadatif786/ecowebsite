<script setup lang="ts">
import { computed, ref } from 'vue';
import {
    BadgePercent,
    Coins,
    CreditCard,
    Eye,
    Globe2,
    IdCard,
    Layers,
    PackageX,
    Search,
    ShieldAlert,
    Shuffle,
    Ticket,
    Undo2,
    UserX,
} from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type FraudRisk = 'High' | 'Medium' | 'Low';
type FraudStatus = 'Open' | 'Investigating' | 'Escalated' | 'Blocked' | 'Cleared';

type FraudType = {
    type: string;
    icon: string;
    color: string;
    desc: string;
    example: string;
};

type FraudCase = {
    id: string;
    type: string;
    name: string;
    country: string;
    channel: string;
    amount: number;
    risk: FraudRisk;
    status: FraudStatus;
    date: string;
    detail: string;
};

const iconMap: Record<string, any> = {
    'user-x': UserX,
    'credit-card': CreditCard,
    'undo-2': Undo2,
    layers: Layers,
    'badge-percent': BadgePercent,
    'package-x': PackageX,
    'id-card': IdCard,
    shuffle: Shuffle,
    coins: Coins,
    ticket: Ticket,
};

const fraudTypeCatalog: FraudType[] = [
    { type: 'Account Takeover (ATO)', icon: 'user-x', color: '#ef4444', desc: 'Stolen credentials used to drain a wallet.', example: 'Login from a new device in another country, then instant cash-out.' },
    { type: 'Card Testing', icon: 'credit-card', color: '#f59e0b', desc: 'Bots run many small card charges to find live cards.', example: '120 micro top-ups of $1-2 from one IP in minutes.' },
    { type: 'Chargeback / Friendly Fraud', icon: 'undo-2', color: '#ec4899', desc: 'User disputes a legitimate purchase after receiving goods.', example: 'Ticket scanned at the gate, then a chargeback filed.' },
    { type: 'Money Laundering / Structuring', icon: 'layers', color: '#8b5cf6', desc: 'Funds split to dodge reporting thresholds.', example: 'Many sub-$10k loads then a fast cross-border transfer.' },
    { type: 'Promo & Coupon Abuse', icon: 'badge-percent', color: '#06b6d4', desc: 'Multiple fake accounts farm sign-up bonuses/coupons.', example: '30 wallets from one device each claiming a welcome bonus.' },
    { type: 'Refund / Return Fraud', icon: 'package-x', color: '#10b981', desc: 'Claiming items never arrived to get a refund and keep goods.', example: 'Marketplace item-not-received claim while tracking shows delivered.' },
    { type: 'Synthetic / Identity Fraud', icon: 'id-card', color: '#3b82f6', desc: 'Fabricated identity passes KYC to open accounts.', example: 'Real national ID number paired with a mismatched face/selfie.' },
    { type: 'Marketplace Triangulation', icon: 'shuffle', color: '#d97706', desc: 'Fraudster uses a stolen card to fulfill a real buyer order.', example: 'Seller funds orders with stolen cards, pockets clean payouts.' },
    { type: 'Coin Farming / Collusion', icon: 'coins', color: '#a855f7', desc: 'Creators and accounts collude to inflate gifting/coins.', example: 'Same ring of accounts gifting coins back and forth on Live.' },
    { type: 'Ticket Scalping / Resale', icon: 'ticket', color: '#0ea5e9', desc: 'Bulk ticket buying for inflated resale.', example: 'One account buys 80 tickets to a sold-out fete in seconds.' },
];

const seedCases: FraudCase[] = [
    { id: 'FR-9001', type: 'Account Takeover (ATO)', name: 'Lucas Silva', country: 'Brazil', channel: 'Wallet', amount: 8400, risk: 'High', status: 'Open', date: '2026-06-08', detail: 'New-device login from Brazil then $8,400 cash-out attempt within 6 minutes.' },
    { id: 'FR-9002', type: 'Card Testing', name: '(bot ring) 41.x IP', country: 'United States', channel: 'Cash-In Card', amount: 2300, risk: 'High', status: 'Investigating', date: '2026-06-08', detail: '118 micro card top-ups of $1-2 from a single IP block in 9 minutes.' },
    { id: 'FR-9003', type: 'Chargeback / Friendly Fraud', name: 'Brianna Smith', country: 'Jamaica', channel: 'Events & Tickets', amount: 890, risk: 'Medium', status: 'Open', date: '2026-06-07', detail: 'Ticket scanned at gate for Island Vibes, chargeback filed next day.' },
    { id: 'FR-9004', type: 'Money Laundering / Structuring', name: 'Renata Costa', country: 'Brazil', channel: 'Remittance', amount: 9600, risk: 'High', status: 'Escalated', date: '2026-06-07', detail: 'Five sub-$2k loads then rapid cross-border transfer to 3 corridors.' },
    { id: 'FR-9005', type: 'Promo & Coupon Abuse', name: 'Multi-account (device d-7741)', country: 'Colombia', channel: 'Signups', amount: 600, risk: 'Medium', status: 'Open', date: '2026-06-07', detail: '30 wallets created on one device each claiming the welcome bonus.' },
    { id: 'FR-9006', type: 'Refund / Return Fraud', name: 'Maya Evans', country: 'United States', channel: 'Marketplace', amount: 420, risk: 'Medium', status: 'Investigating', date: '2026-06-06', detail: 'Item-not-received claim; carrier tracking shows delivered and signed.' },
    { id: 'FR-9007', type: 'Synthetic / Identity Fraud', name: '"Andre Knowles"', country: 'Bahamas', channel: 'KYC / Onboarding', amount: 0, risk: 'High', status: 'Open', date: '2026-06-06', detail: 'Valid NIB number paired with a selfie that fails liveness and face match.' },
    { id: 'FR-9008', type: 'Marketplace Triangulation', name: 'QuickDeals Store', country: 'Trinidad & Tobago', channel: 'Marketplace', amount: 3100, risk: 'High', status: 'Open', date: '2026-06-06', detail: 'Seller fulfilling orders with stolen cards, withdrawing clean payouts.' },
    { id: 'FR-9009', type: 'Coin Farming / Collusion', name: 'Creator @islandvibez', country: 'Jamaica', channel: 'LinkUp Live', amount: 1750, risk: 'Medium', status: 'Investigating', date: '2026-06-05', detail: 'Ring of 6 accounts gifting coins back and forth to inflate earnings.' },
    { id: 'FR-9010', type: 'Ticket Scalping / Resale', name: 'Marcus Joseph', country: 'Trinidad & Tobago', channel: 'Events & Tickets', amount: 2400, risk: 'Medium', status: 'Open', date: '2026-06-05', detail: 'One account bought 80 tickets to a sold-out event in under a minute.' },
    { id: 'FR-9011', type: 'Account Takeover (ATO)', name: 'Sofia Hernandez', country: 'Mexico', channel: 'Wallet', amount: 5200, risk: 'High', status: 'Blocked', date: '2026-06-04', detail: 'Credential-stuffing hit; account frozen before cash-out cleared.' },
    { id: 'FR-9012', type: 'Card Testing', name: '(bot ring) 190.x IP', country: 'Colombia', channel: 'Cash-In Card', amount: 1450, risk: 'Medium', status: 'Blocked', date: '2026-06-04', detail: 'Velocity rule auto-blocked 60+ declined card attempts.' },
    { id: 'FR-9013', type: 'Money Laundering / Structuring', name: 'Carlos Mendoza', country: 'Colombia', channel: 'Wallet', amount: 5200, risk: 'Medium', status: 'Cleared', date: '2026-06-03', detail: 'Reviewed as legitimate supplier payments with invoices. Cleared.' },
    { id: 'FR-9014', type: 'Refund / Return Fraud', name: 'Maria Rodriguez', country: 'Dominican Republic', channel: 'Marketplace', amount: 300, risk: 'Low', status: 'Cleared', date: '2026-06-03', detail: 'Genuine non-delivery; refunded and seller penalized.' },
    { id: 'FR-9015', type: 'Chargeback / Friendly Fraud', name: 'Diego Torres', country: 'Mexico', channel: 'Subscriptions', amount: 120, risk: 'Low', status: 'Open', date: '2026-06-02', detail: 'Disputed a premium renewal after a full month of use.' },
    { id: 'FR-9016', type: 'Synthetic / Identity Fraud', name: '"GlobalPay Vendor"', country: 'Dominican Republic', channel: 'Merchant Onboarding', amount: 0, risk: 'High', status: 'Escalated', date: '2026-06-02', detail: 'Shell merchant with fabricated docs; escalated to compliance.' },
];

const regionOrder = ['Caribbean', 'Latin America', 'North America', 'Other'];
const countryOrder = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Dominican Republic', 'Brazil', 'Colombia', 'Mexico', 'United States'];
const regionByCountry: Record<string, string> = {
    Bahamas: 'Caribbean',
    Jamaica: 'Caribbean',
    'Trinidad & Tobago': 'Caribbean',
    'Dominican Republic': 'Caribbean',
    Brazil: 'Latin America',
    Colombia: 'Latin America',
    Mexico: 'Latin America',
    'United States': 'North America',
};

const statusOptions: Array<'all' | FraudStatus> = ['all', 'Open', 'Investigating', 'Escalated', 'Blocked', 'Cleared'];
const monthlyGtv = 46673000;

const loadCases = () => {
    try {
        const stored = JSON.parse(localStorage.getItem('linkupFraudCases') || 'null');
        if (Array.isArray(stored) && stored.length) return stored as FraudCase[];
    } catch {
        // Fall back to standalone dashboard seed data.
    }
    return seedCases;
};

const cases = ref<FraudCase[]>(loadCases());
const selectedType = ref('all');
const selectedStatus = ref<'all' | FraudStatus>('all');
const search = ref('');
const openRegions = ref<Record<string, boolean>>({});
const openCountries = ref<Record<string, boolean>>({});
const selectedCase = ref<FraudCase | null>(null);

const persist = () => {
    localStorage.setItem('linkupFraudCases', JSON.stringify(cases.value));
};

const formatMoney = (value: number) => value ? new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value) : '-';
const isOpenCase = (status: FraudStatus) => status === 'Open' || status === 'Investigating';

const openCases = computed(() => cases.value.filter((item) => isOpenCase(item.status)));
const highRiskOpenCases = computed(() => cases.value.filter((item) => item.risk === 'High' && isOpenCase(item.status)));
const amountAtRisk = computed(() => openCases.value.reduce((total, item) => total + item.amount, 0));
const blockedAmount = computed(() => cases.value.filter((item) => item.status === 'Blocked').reduce((total, item) => total + item.amount, 0));
const fraudRate = computed(() => monthlyGtv ? (amountAtRisk.value / monthlyGtv) * 100 : 0);

const filteredCases = computed(() => {
    const q = search.value.trim().toLowerCase();
    return cases.value.filter((item) => {
        const typeOk = selectedType.value === 'all' || item.type === selectedType.value;
        const statusOk = selectedStatus.value === 'all' || item.status === selectedStatus.value;
        const searchOk = !q || [item.id, item.name, item.type, item.country, item.channel, item.risk, item.status, item.detail].join(' ').toLowerCase().includes(q);
        return typeOk && statusOk && searchOk;
    });
});

const groupedCases = computed(() => {
    const groups = filteredCases.value.reduce<Record<string, Record<string, FraudCase[]>>>((acc, item) => {
        const region = regionByCountry[item.country] || 'Other';
        acc[region] ||= {};
        acc[region][item.country] ||= [];
        acc[region][item.country].push(item);
        return acc;
    }, {});

    return Object.fromEntries(
        Object.entries(groups)
            .sort(([a], [b]) => regionOrder.indexOf(a) - regionOrder.indexOf(b))
            .map(([region, countries]) => [
                region,
                Object.fromEntries(
                    Object.entries(countries).sort(([a], [b]) => countryOrder.indexOf(a) - countryOrder.indexOf(b)),
                ),
            ]),
    );
});

const fraudTypeStats = computed(() => {
    return fraudTypeCatalog.map((type) => {
        const matching = cases.value.filter((item) => item.type === type.type);
        const open = matching.filter((item) => isOpenCase(item.status));
        return {
            ...type,
            openCount: open.length,
            amountAtRisk: open.reduce((total, item) => total + item.amount, 0),
        };
    });
});

const regionCount = (countries: Record<string, FraudCase[]>) => Object.values(countries).reduce((total, items) => total + items.length, 0);
const countryIsOpen = (country: string, count: number) => openCountries.value[country] ?? count <= 1;
const regionIsOpen = (region: string) => openRegions.value[region] ?? true;

const toggleRegion = (region: string) => {
    openRegions.value[region] = !regionIsOpen(region);
};

const toggleCountry = (country: string, count: number) => {
    openCountries.value[country] = !countryIsOpen(country, count);
};

const setType = (type: string) => {
    selectedType.value = selectedType.value === type ? 'all' : type;
};

const clearType = () => {
    selectedType.value = 'all';
};

const setCaseStatus = (item: FraudCase, status: FraudStatus) => {
    item.status = status;
    persist();
    toast.success(`${item.id} marked ${status}.`);
};

const riskClass = (risk: FraudRisk) => ({
    High: 'bg-rose-50 text-rose-700',
    Medium: 'bg-amber-50 text-amber-700',
    Low: 'bg-green-50 text-green-700',
}[risk]);

const statusClass = (status: FraudStatus) => ({
    Open: 'bg-rose-50 text-rose-700',
    Investigating: 'bg-amber-50 text-amber-700',
    Escalated: 'bg-purple-50 text-purple-700',
    Blocked: 'bg-slate-900 text-white',
    Cleared: 'bg-green-50 text-green-700',
}[status]);
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div>
            <h3 class="text-3xl font-black text-slate-950">Fraud Center</h3>
            <p class="mt-1 text-slate-500">Detect, classify and act on fraud across the platform by type, user, entity, country, risk, and channel.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Open Cases</p><h3 class="text-4xl font-black text-rose-600">{{ openCases.length }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">High Risk</p><h3 class="text-4xl font-black text-amber-600">{{ highRiskOpenCases.length }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Amount at Risk</p><h3 class="text-3xl font-black text-rose-600">{{ formatMoney(amountAtRisk) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Blocked / Prevented</p><h3 class="text-3xl font-black text-green-600">{{ formatMoney(blockedAmount) }}</h3></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Fraud Rate</p><h3 class="text-4xl font-black">{{ fraudRate.toFixed(2) }}%</h3><p class="text-xs text-slate-500">of GTV at risk</p></div>
        </div>

        <section class="card rounded-3xl p-6">
            <h3 class="mb-1 text-xl font-black">Fraud by Type</h3>
            <p class="mb-4 text-sm text-slate-500">Click a type to filter the case queue. Each card shows live cases, exposure, and a real-world example.</p>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                <button
                    v-for="type in fraudTypeStats"
                    :key="type.type"
                    class="rounded-2xl border p-4 text-left transition"
                    :style="{
                        borderColor: selectedType === type.type ? type.color : '#e2e8f0',
                        borderWidth: selectedType === type.type ? '2px' : '1px',
                        background: selectedType === type.type ? `${type.color}14` : '#f8fafc',
                    }"
                    @click="setType(type.type)"
                >
                    <div class="flex items-center justify-between">
                        <div class="grid h-9 w-9 place-items-center rounded-xl text-white" :style="{ background: type.color }">
                            <component :is="iconMap[type.icon] || ShieldAlert" class="h-4 w-4" />
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black" :class="type.openCount ? 'text-rose-600' : 'text-green-600'">{{ type.openCount }}</span>
                            <span class="text-xs font-bold text-slate-500"> open</span>
                        </div>
                    </div>
                    <p class="mt-2 text-sm font-black">{{ type.type }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ type.desc }}</p>
                    <p class="mt-1 text-xs italic text-slate-400">e.g. {{ type.example }}</p>
                    <p v-if="type.amountAtRisk" class="mt-1 text-xs font-black text-rose-600">{{ formatMoney(type.amountAtRisk) }} at risk</p>
                </button>
            </div>
        </section>

        <section class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-xl font-black">Fraud Case Queue</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="search" class="w-full rounded-2xl border border-slate-200 py-2 pl-10 pr-4 text-sm sm:w-64" placeholder="Search name, type, country..." />
                    </div>
                    <button class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-black" @click="clearType">{{ selectedType === 'all' ? 'All Types' : selectedType }}</button>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap gap-2">
                <button
                    v-for="status in statusOptions"
                    :key="status"
                    class="rounded-full px-3.5 py-1.5 text-xs font-black"
                    :class="selectedStatus === status ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600'"
                    @click="selectedStatus = status"
                >
                    {{ status === 'all' ? 'All' : status }}
                </button>
            </div>

            <div v-if="!filteredCases.length" class="rounded-3xl border border-slate-100 bg-slate-50 p-10 text-center">
                <ShieldAlert class="mx-auto h-12 w-12 text-slate-300" />
                <h4 class="mt-4 font-black">No fraud cases found</h4>
                <p class="mt-1 text-slate-500">There are no cases matching this search.</p>
            </div>

            <div v-else class="overflow-x-auto scrollbar">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2">Case</th>
                            <th>User / Entity</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Amount</th>
                            <th>Risk</th>
                            <th>Status</th>
                            <th>Detail</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(countries, region) in groupedCases" :key="region">
                            <tr>
                                <td colspan="9" class="p-0">
                                    <button class="flex w-full items-center gap-2 bg-slate-700 px-4 py-3 text-left font-black text-white" @click="toggleRegion(String(region))">
                                        <span class="w-4">{{ regionIsOpen(String(region)) ? 'v' : '>' }}</span>
                                        <span class="grid h-5 w-5 place-items-center rounded-full bg-sky-400 text-white"><Globe2 class="h-3.5 w-3.5" /></span>
                                        <span>{{ region }} <span class="text-slate-300">({{ regionCount(countries) }})</span></span>
                                    </button>
                                </td>
                            </tr>

                            <template v-if="regionIsOpen(String(region))">
                                <template v-for="(items, country) in countries" :key="country">
                                    <tr>
                                        <td colspan="9" class="p-0">
                                            <button class="flex w-full items-center gap-2 bg-slate-100 px-7 py-3 text-left font-black text-slate-800 hover:bg-slate-200" @click="toggleCountry(String(country), items.length)">
                                                <span class="w-4">{{ countryIsOpen(String(country), items.length) ? 'v' : '>' }}</span>
                                                <span>{{ country }} <span class="text-slate-400">({{ items.length }})</span></span>
                                            </button>
                                        </td>
                                    </tr>

                                    <tr v-for="item in items" v-show="countryIsOpen(String(country), items.length)" :key="item.id" class="border-t align-top">
                                        <td class="py-3 font-black">{{ item.id }}<div class="text-xs font-normal text-slate-400">{{ item.date }}</div></td>
                                        <td class="font-black">{{ item.name }}<div class="text-xs font-normal text-slate-500">{{ item.country }}</div></td>
                                        <td>{{ item.type }}</td>
                                        <td class="text-slate-500">{{ item.channel }}</td>
                                        <td class="font-black">{{ formatMoney(item.amount) }}</td>
                                        <td><span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="riskClass(item.risk)">{{ item.risk }}</span></td>
                                        <td><span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="statusClass(item.status)">{{ item.status }}</span></td>
                                        <td class="max-w-xs text-xs text-slate-500">
                                            {{ item.detail }}
                                            <button class="ml-2 inline-flex items-center gap-1 font-black text-sky-600" @click="selectedCase = item"><Eye class="h-3.5 w-3.5" /> View</button>
                                        </td>
                                        <td>
                                            <span v-if="item.status === 'Blocked' || item.status === 'Cleared'" class="text-xs font-bold text-slate-400">closed</span>
                                            <div v-else class="flex flex-wrap gap-1">
                                                <button v-if="item.status === 'Open'" class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-black text-amber-700" @click="setCaseStatus(item, 'Investigating')">Investigate</button>
                                                <button class="rounded-lg bg-slate-900 px-2 py-1 text-xs font-black text-white" @click="setCaseStatus(item, 'Blocked')">Block</button>
                                                <button class="rounded-lg bg-purple-100 px-2 py-1 text-xs font-black text-purple-700" @click="setCaseStatus(item, 'Escalated')">Escalate</button>
                                                <button class="rounded-lg bg-green-100 px-2 py-1 text-xs font-black text-green-700" @click="setCaseStatus(item, 'Cleared')">Clear</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <div v-if="selectedCase" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-5">
            <div class="w-full max-w-3xl rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-6">
                    <div>
                        <h3 class="text-2xl font-black">{{ selectedCase.id }} - {{ selectedCase.type }}</h3>
                        <p class="mt-1 text-slate-500">{{ selectedCase.name }} - {{ selectedCase.country }} - {{ selectedCase.date }}</p>
                    </div>
                    <button class="rounded-2xl bg-slate-100 px-4 py-2 font-black" @click="selectedCase = null">Close</button>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase text-slate-500">Channel</p><h4 class="mt-1 text-lg font-black">{{ selectedCase.channel }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase text-slate-500">Exposure</p><h4 class="mt-1 text-lg font-black">{{ formatMoney(selectedCase.amount) }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase text-slate-500">Risk</p><h4 class="mt-1 text-lg font-black">{{ selectedCase.risk }}</h4></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase text-slate-500">Status</p><h4 class="mt-1 text-lg font-black">{{ selectedCase.status }}</h4></div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 md:col-span-2"><p class="font-black text-amber-900">Detail</p><p class="mt-1 text-amber-800">{{ selectedCase.detail }}</p></div>
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
