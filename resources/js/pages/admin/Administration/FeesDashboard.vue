<script setup lang="ts">
import { computed, reactive } from 'vue';
import {
    ArrowDownToLine,
    ArrowUpFromLine,
    BadgeDollarSign,
    Banknote,
    Calculator,
    CalendarDays,
    CreditCard,
    Flame,
    Globe2,
    Home,
    Info,
    Landmark,
    Megaphone,
    Newspaper,
    Radio,
    Send,
    ShoppingBag,
    Sparkles,
    Tag,
    Ticket,
    Utensils,
    Wallet,
} from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import EatsFeesPricing from './EatsFeesPricing.vue';
import EventFees from './EventFees.vue';
import MarketplaceFees from './MarketplaceFees.vue';
import PricingPlans from './PricingPlans.vue';

type UnitFee = {
    key: string;
    name: string;
    icon: unknown;
    platformRate: number;
    bankRate: number;
    costRate: number;
    desc: string;
    volume: number;
};

const props = defineProps<{
    viewId?: string;
    initialEventFeeSettings?: Record<string, any>;
}>();

const emit = defineEmits<{
    (event: 'change-view', viewId: string): void;
}>();

const activeTab = computed(() => props.viewId || 'feesCenterCommand');

const defaultUnits: UnitFee[] = [
    { key: 'tickets', name: 'Ticket Sales', icon: Ticket, platformRate: 6.5, bankRate: 0, costRate: 1, desc: 'Tickets, VIP, drink tickets, event add-ons', volume: 6760000 },
    { key: 'subscriptions', name: 'Subscriptions', icon: BadgeDollarSign, platformRate: 100, bankRate: 0, costRate: 4, desc: 'Premium users and paid plans', volume: 841000 },
    { key: 'marketplace', name: 'Marketplace', icon: ShoppingBag, platformRate: 5, bankRate: 0, costRate: 1, desc: 'Seller fees and commissions', volume: 4610000 },
    { key: 'eats', name: 'LinkUp Eats', icon: Utensils, platformRate: 7.5, bankRate: 0, costRate: 2.5, desc: 'QR menus, ordering, pickup, delivery', volume: 2720000 },
    { key: 'merchantPay', name: 'Merchant Pay', icon: CreditCard, platformRate: 2, bankRate: 0, costRate: 0.7, desc: 'QR payments, card, ACH, bill pay', volume: 17880000 },
    { key: 'wallet', name: 'Wallet & Money Movement', icon: Wallet, platformRate: 2.5, bankRate: 1.75, costRate: 0.8, desc: 'Cash-in, cash-out, transfers - the only bank/processing touchpoint', volume: 9670000 },
    { key: 'live', name: 'LinkUp Live', icon: Radio, platformRate: 50, bankRate: 0, costRate: 8, desc: 'Live coins, gifts, creators', volume: 2220000 },
    { key: 'ads', name: 'Advertising Revenue', icon: Megaphone, platformRate: 100, bankRate: 0, costRate: 12, desc: 'Swipe ads, email ads, promoted posts', volume: 659000 },
    { key: 'wellness', name: 'Wellness & Spa', icon: Sparkles, platformRate: 6.75, bankRate: 0, costRate: 1.5, desc: 'Bookings and appointment marketplace', volume: 1780000 },
    { key: 'cookouts', name: 'Cookouts', icon: Flame, platformRate: 6.75, bankRate: 0, costRate: 1.5, desc: 'Food events and vendor sales', volume: 1050000 },
    { key: 'linkup360', name: 'LinkUp 360 News Ads', icon: Newspaper, platformRate: 100, bankRate: 0, costRate: 15, desc: 'Sponsored news and media placements', volume: 488000 },
];

const cloneUnits = () => defaultUnits.map((unit) => ({ ...unit }));

const units = reactive<UnitFee[]>(cloneUnits());

const defaultMoneyMove = {
    cardPct: 2.9,
    cardFixed: 0.3,
    bankPct: 0.8,
    bankFixed: 0.15,
    outPct: 1.25,
    outFixed: 0.25,
    calcType: 'Cash-In (Card)',
    amount: 100,
};

const moneyMove = reactive({ ...defaultMoneyMove });

const defaultRemittance = {
    domesticFee: 2.5,
    domesticMin: 1,
    domesticMax: 25,
    domesticBank: 0.75,
    internationalFee: 4.5,
    internationalBank: 1.5,
    fxFee: 1.2,
};

const remittance = reactive({ ...defaultRemittance });

const defaultP2p = {
    domesticSendPct: 1,
    domesticSendFixed: 0.25,
    domesticSendMin: 0.5,
    domesticSendMax: 10,
    domesticReceivePct: 0.5,
    domesticReceiveFixed: 0,
    domesticReceiveMin: 0,
    domesticReceiveMax: 5,
    internationalSendPct: 2.5,
    internationalSendFixed: 0.75,
    internationalSendMin: 1.5,
    internationalSendMax: 25,
    internationalReceivePct: 1,
    internationalReceiveFixed: 0.25,
    internationalReceiveMin: 0.75,
    internationalReceiveMax: 12,
    fxPct: 1,
    calcScope: 'Domestic',
    amount: 100,
};

const p2p = reactive({ ...defaultP2p });

const asue = reactive({
    handPct: 3,
});

const notes = reactive({
    feeCenter: 'Changes save automatically and apply across the whole platform.',
    moneyMove: '',
    remittance: '',
    p2p: '',
    asue: '',
});

const inputClass = 'mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-bold outline-none focus:border-sky-300 focus:ring-4 focus:ring-sky-100';
const compactInputClass = 'mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 font-bold outline-none focus:border-sky-300 focus:ring-4 focus:ring-sky-100';

const clamp = (value: number, min: number, max: number) => Math.min(Math.max(value, min), max);
const calcFee = (amount: number, pct: number, fixed: number) => amount * (pct / 100) + fixed;
const boundedFee = (amount: number, pct: number, fixed: number, min: number, max: number) => clamp(calcFee(amount, pct, fixed), min, max || Number.MAX_SAFE_INTEGER);
const money = (value: number) => `$${Number(value || 0).toFixed(2)}`;
const margin = (unit: UnitFee) => unit.platformRate - unit.costRate;

const pageTitle = computed(() => {
    const titles: Record<string, string> = {
        feesCenterCommand: 'Fees Center',
        eventFeeCommand: 'Event Fees',
        eatsFeesPricingCommand: 'Eats Fees & Pricing',
        marketplaceFeesCommand: 'Marketplace Fees',
        pricingCommand: 'Pricing & Plans',
    };

    return titles[activeTab.value] || 'Fees Center';
});

const pageDescription = computed(() => {
    if (activeTab.value === 'feesCenterCommand') {
        return 'One place to configure every LinkUp fee. Editing a rate instantly updates revenue, the bank processing split, and all reports across the platform.';
    }

    return 'Configure LinkUp platform fees, bank processing, remittance, event, Eats, marketplace, and pricing plans from the Administration section.';
});

const moneyMovePreview = computed(() => {
    const amount = Number(moneyMove.amount || 0);
    const isCashIn = moneyMove.calcType.startsWith('Cash-In');
    const isCard = moneyMove.calcType === 'Cash-In (Card)';
    const pct = isCard ? moneyMove.cardPct : moneyMove.calcType === 'Cash-In (Bank/ACH)' ? moneyMove.bankPct : moneyMove.outPct;
    const fixed = isCard ? moneyMove.cardFixed : moneyMove.calcType === 'Cash-In (Bank/ACH)' ? moneyMove.bankFixed : moneyMove.outFixed;
    const fee = calcFee(amount, pct, fixed);

    return {
        labelOne: isCashIn ? 'User Pays' : 'Wallet Debited',
        labelTwo: isCashIn ? 'Lands in Wallet' : 'Lands in Bank',
        valueOne: isCashIn ? amount + fee : amount,
        valueTwo: isCashIn ? amount : Math.max(amount - fee, 0),
        fee,
        breakdown: `${pct.toFixed(2)}% + ${money(fixed)} fixed = ${money(fee)} fee. LinkUp/Scotiabank processing pool split: ${money(fee * 0.6)} / ${money(fee * 0.4)}.`,
    };
});

const p2pPreview = computed(() => {
    const amount = Number(p2p.amount || 0);
    const international = p2p.calcScope === 'International';
    const sendFee = international
        ? boundedFee(amount, p2p.internationalSendPct, p2p.internationalSendFixed, p2p.internationalSendMin, p2p.internationalSendMax)
        : boundedFee(amount, p2p.domesticSendPct, p2p.domesticSendFixed, p2p.domesticSendMin, p2p.domesticSendMax);
    const receiveFee = international
        ? boundedFee(amount, p2p.internationalReceivePct, p2p.internationalReceiveFixed, p2p.internationalReceiveMin, p2p.internationalReceiveMax)
        : boundedFee(amount, p2p.domesticReceivePct, p2p.domesticReceiveFixed, p2p.domesticReceiveMin, p2p.domesticReceiveMax);
    const fx = international ? amount * (p2p.fxPct / 100) : 0;

    return {
        senderPays: amount + sendFee,
        transferAmount: amount,
        recipientGets: Math.max(amount - receiveFee - fx, 0),
        linkupEarns: sendFee + receiveFee + fx,
        breakdown: `${p2p.calcScope}: sender fee ${money(sendFee)}, receive fee ${money(receiveFee)}${international ? `, FX spread ${money(fx)}` : ''}.`,
    };
});

const asuePreview = computed(() => {
    const fee = calcFee(100, asue.handPct, 0);

    return {
        fee,
        net: 100 - fee,
    };
});

const updateUnit = (unit: UnitFee, field: 'platformRate' | 'bankRate' | 'costRate', value: number) => {
    unit[field] = Math.max(Number(value || 0), 0);
    if (unit.key !== 'wallet') {
        unit.bankRate = 0;
    }
    notes.feeCenter = 'Changes save automatically and apply across the whole platform.';
};

const resetUnitFees = () => {
    units.splice(0, units.length, ...cloneUnits());
    notes.feeCenter = 'Fee schedule reset to platform defaults.';
    toast.success('Fee schedule reset to platform defaults.');
};

const resetMoneyMove = () => {
    Object.assign(moneyMove, defaultMoneyMove);
    notes.moneyMove = 'Reset to defaults.';
    toast.success('Money movement fees reset.');
};

const saveMoneyMove = () => {
    notes.moneyMove = 'Saved - bank processing pool updated.';
    toast.success('Money movement fees saved.');
};

const resetRemitFees = () => {
    Object.assign(remittance, defaultRemittance);
    notes.remittance = 'Reset to defaults.';
    toast.success('Remittance fees reset.');
};

const saveRemitFees = () => {
    notes.remittance = 'Saved - remittance fees updated.';
    toast.success('Remittance fees saved.');
};

const resetP2PFees = () => {
    Object.assign(p2p, defaultP2p);
    notes.p2p = 'Reset to defaults.';
    toast.success('Send Money fees reset.');
};

const saveP2PFees = () => {
    notes.p2p = 'Saved - Send Money fees updated.';
    toast.success('Send Money fees saved.');
};

const resetAsueFees = () => {
    asue.handPct = 3;
    notes.asue = 'Reset to defaults.';
    toast.success('ASUE fee reset.');
};

const saveAsueFees = () => {
    notes.asue = 'Saved - ASUE fee updated.';
    toast.success('ASUE fee saved.');
};

const save = (label: string) => toast.success(`${label} saved.`);

const openShortcut = (viewId: string) => {
    emit('change-view', viewId);
};

const syncEatsFees = (fees: { commission: number; service: number }) => {
    const eatsUnit = units.find((unit) => unit.key === 'eats');
    if (!eatsUnit) return;

    eatsUnit.platformRate = Number(fees.commission || 0) + Number(fees.service || 0);
    eatsUnit.bankRate = 0;
    notes.feeCenter = 'LinkUp Eats fees synced from Eats Fees & Pricing.';
};

const syncMarketplaceFees = (fees: { commission: number }) => {
    const marketplaceUnit = units.find((unit) => unit.key === 'marketplace');
    if (!marketplaceUnit) return;

    marketplaceUnit.platformRate = Number(fees.commission || 0);
    marketplaceUnit.bankRate = 0;
    notes.feeCenter = 'Marketplace fees synced from Marketplace Fee Settings.';
};
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div v-if="!['eventFeeCommand', 'eatsFeesPricingCommand', 'marketplaceFeesCommand'].includes(activeTab)">
            <h3 class="text-3xl font-black text-slate-950">{{ pageTitle }}</h3>
            <p class="mt-1 max-w-5xl text-slate-500">
                {{ pageDescription }}
            </p>
        </div>

        <section v-if="activeTab === 'feesCenterCommand'" class="space-y-6">
            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-black">Business Unit Fee Schedule</h3>
                        <p class="text-slate-500">Platform Fee = LinkUp's cut on in-ecosystem activity. Operating Cost = LinkUp's cost to run it.</p>
                    </div>
                    <button class="rounded-2xl border border-slate-200 px-5 py-2 font-black transition hover:bg-slate-50" @click="resetUnitFees">Reset to Defaults</button>
                </div>

                <div class="mb-4 flex gap-2 rounded-2xl border border-sky-100 bg-sky-50 p-4 text-sm font-bold text-sky-900">
                    <Info class="h-5 w-5 shrink-0" />
                    <span>
                        Wallet-model: <b>bank/processing fees apply only when money crosses the bank rails</b> - Cash-In, Cash-Out, Remittance,
                        and Send/Receive. Spending from wallet balance carries <b>no bank fee</b>, so those rows show "n/a".
                        Configure bank fees in the <b>Money Movement</b> and <b>Send Money</b> sections below.
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-3">Fee Category</th>
                                <th>Platform Fee</th>
                                <th>Bank Fee</th>
                                <th>Operating Cost</th>
                                <th>Net Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="unit in units" :key="unit.key" class="border-t">
                                <td class="py-3 font-black">
                                    <span class="inline-flex items-center gap-2">
                                        <component :is="unit.icon" class="h-4 w-4" />
                                        {{ unit.name }}
                                    </span>
                                    <div class="text-xs font-normal text-slate-500">{{ unit.desc }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <input
                                            :value="unit.platformRate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-24 rounded-xl border border-slate-200 px-2 py-1 font-bold outline-none focus:border-sky-300"
                                            @input="updateUnit(unit, 'platformRate', Number(($event.target as HTMLInputElement).value))"
                                        />
                                        <span class="text-slate-400">%</span>
                                    </div>
                                </td>
                                <td>
                                    <div v-if="unit.key === 'wallet'" class="flex items-center gap-1">
                                        <input
                                            :value="unit.bankRate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-24 rounded-xl border border-slate-200 px-2 py-1 font-bold outline-none focus:border-sky-300"
                                            @input="updateUnit(unit, 'bankRate', Number(($event.target as HTMLInputElement).value))"
                                        />
                                        <span class="text-slate-400">%</span>
                                    </div>
                                    <span v-else class="text-sm font-bold text-slate-400" title="Bank/processing fees apply only at money-movement points - not on in-wallet spend.">n/a</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <input
                                            :value="unit.costRate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-24 rounded-xl border border-slate-200 px-2 py-1 font-bold outline-none focus:border-sky-300"
                                            @input="updateUnit(unit, 'costRate', Number(($event.target as HTMLInputElement).value))"
                                        />
                                        <span class="text-slate-400">%</span>
                                    </div>
                                </td>
                                <td class="font-black" :class="margin(unit) >= 0 ? 'text-green-600' : 'text-rose-600'">{{ margin(unit).toFixed(2) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-sm text-slate-500">{{ notes.feeCenter }}</p>
            </div>

            <div class="card rounded-3xl border-2 border-amber-200 p-6">
                <div class="mb-2 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-xl font-black">
                            <Banknote class="h-5 w-5 text-amber-600" />
                            Money Movement (Bank / Processing) Fees
                        </h3>
                        <p class="text-slate-500">
                            The bank earns here: <b>Cash-In</b> and <b>Cash-Out</b>. These drive the Bank Revenue pool
                            (LinkUp 60% / Scotiabank 40%).
                        </p>
                    </div>
                    <button class="rounded-2xl border border-slate-200 px-5 py-2 font-black transition hover:bg-slate-50" @click="resetMoneyMove">Reset</button>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-5">
                        <h4 class="mb-3 flex items-center gap-2 font-black">
                            <ArrowDownToLine class="h-4 w-4 text-amber-700" />
                            Cash-In / Top-Up
                            <span class="text-xs font-bold text-slate-500">(money entering - by funding method)</span>
                        </h4>
                        <p class="mb-1 flex items-center gap-1 text-xs font-black text-rose-700">
                            <CreditCard class="h-3.5 w-3.5" />
                            By Credit / Debit Card
                            <span class="font-normal text-slate-500">(incl. pay-by-card at checkout)</span>
                        </p>
                        <div class="mb-3 grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold text-slate-600">Fee (%)<input v-model.number="moneyMove.cardPct" type="number" step="0.01" min="0" :class="inputClass" /></label>
                            <label class="text-sm font-bold text-slate-600">Fixed ($)<input v-model.number="moneyMove.cardFixed" type="number" step="0.01" min="0" :class="inputClass" /></label>
                        </div>
                        <p class="mb-1 flex items-center gap-1 text-xs font-black text-sky-700">
                            <Landmark class="h-3.5 w-3.5" />
                            By Bank / ACH
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold text-slate-600">Fee (%)<input v-model.number="moneyMove.bankPct" type="number" step="0.01" min="0" :class="inputClass" /></label>
                            <label class="text-sm font-bold text-slate-600">Fixed ($)<input v-model.number="moneyMove.bankFixed" type="number" step="0.01" min="0" :class="inputClass" /></label>
                        </div>
                        <p class="mt-2 text-[11px] text-slate-500">
                            Card is charged the higher interchange rate; bank/ACH is cheaper. A direct card purchase is billed at the <b>Card</b> rate.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5">
                        <h4 class="mb-3 flex items-center gap-2 font-black">
                            <ArrowUpFromLine class="h-4 w-4 text-emerald-700" />
                            Cash-Out / Withdrawal
                            <span class="text-xs font-bold text-slate-500">(download wallet -> bank)</span>
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold text-slate-600">Fee (%)<input v-model.number="moneyMove.outPct" type="number" step="0.01" min="0" :class="inputClass" /></label>
                            <label class="text-sm font-bold text-slate-600">Fixed ($)<input v-model.number="moneyMove.outFixed" type="number" step="0.01" min="0" :class="inputClass" /></label>
                        </div>
                        <p class="mt-2 text-[11px] text-slate-500">Applies when moving money out of the wallet to a linked bank account.</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl bg-slate-950 p-5 text-white">
                    <div class="mb-3 flex flex-wrap items-center gap-3">
                        <Calculator class="h-5 w-5 text-amber-300" />
                        <h4 class="font-black">Live Calculator</h4>
                        <div class="ml-auto flex flex-wrap items-center gap-2">
                            <select v-model="moneyMove.calcType" class="rounded-xl border border-white/20 bg-white/10 px-3 py-1.5 font-black">
                                <option>Cash-In (Card)</option>
                                <option>Cash-In (Bank/ACH)</option>
                                <option>Cash-Out</option>
                            </select>
                            <span class="text-sm text-slate-300">Amount</span>
                            <input v-model.number="moneyMove.amount" type="number" class="w-28 rounded-xl border border-white/20 bg-white/10 px-3 py-1.5 text-right font-black" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">{{ moneyMovePreview.labelOne }}</p>
                            <h3 class="text-2xl font-black text-amber-300">{{ money(moneyMovePreview.valueOne) }}</h3>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">{{ moneyMovePreview.labelTwo }}</p>
                            <h3 class="text-2xl font-black">{{ money(moneyMovePreview.valueTwo) }}</h3>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">Bank/LinkUp Fee</p>
                            <h3 class="text-2xl font-black text-lime-300">{{ money(moneyMovePreview.fee) }}</h3>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-300">{{ moneyMovePreview.breakdown }}</p>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white transition hover:bg-slate-800" @click="saveMoneyMove">Save Money Movement Fees</button>
                    <span class="text-sm text-slate-500">{{ notes.moneyMove }}</span>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-black">Remittance Fees</h3>
                        <p class="text-slate-500">Control domestic and international money-transfer fees. Bank fees feed the processing pool (LinkUp 60% / Scotia 40%).</p>
                    </div>
                    <button class="rounded-2xl border border-slate-200 px-5 py-2 font-black transition hover:bg-slate-50" @click="resetRemitFees">Reset</button>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-5">
                        <h4 class="mb-3 flex items-center gap-2 font-black">
                            <Home class="h-4 w-4" />
                            Domestic Transfers
                        </h4>
                        <label class="text-sm font-bold text-slate-600">LinkUp Fee (%)<input v-model.number="remittance.domesticFee" type="number" step="0.01" min="0" :class="`${inputClass} mb-3`" /></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold text-slate-600">Min Fee ($)<input v-model.number="remittance.domesticMin" type="number" step="0.01" min="0" :class="inputClass" /></label>
                            <label class="text-sm font-bold text-slate-600">Max Fee ($)<input v-model.number="remittance.domesticMax" type="number" step="0.01" min="0" :class="inputClass" /></label>
                        </div>
                        <label class="mt-3 block text-sm font-bold text-slate-600">Bank Fee (%)<input v-model.number="remittance.domesticBank" type="number" step="0.01" min="0" :class="inputClass" /></label>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-5">
                        <h4 class="mb-3 flex items-center gap-2 font-black">
                            <Globe2 class="h-4 w-4" />
                            International Transfers
                        </h4>
                        <label class="text-sm font-bold text-slate-600">LinkUp Fee (%)<input v-model.number="remittance.internationalFee" type="number" step="0.01" min="0" :class="`${inputClass} mb-3`" /></label>
                        <label class="text-sm font-bold text-slate-600">Bank Fee (%)<input v-model.number="remittance.internationalBank" type="number" step="0.01" min="0" :class="`${inputClass} mb-3`" /></label>
                        <label class="text-sm font-bold text-slate-600">FX Fee (%)<input v-model.number="remittance.fxFee" type="number" step="0.01" min="0" :class="inputClass" /></label>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white transition hover:bg-slate-800" @click="saveRemitFees">Save Remittance Fees</button>
                    <span class="text-sm text-slate-500">{{ notes.remittance }}</span>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-xl font-black">
                            <Send class="h-5 w-5 text-emerald-600" />
                            Send Money (P2P) Fees
                        </h3>
                        <p class="text-slate-500">PayPal-style wallet transfers - charge the sender a fee on top, and deduct a fee from what the recipient receives. LinkUp keeps both.</p>
                    </div>
                    <button class="rounded-2xl border border-slate-200 px-5 py-2 font-black transition hover:bg-slate-50" @click="resetP2PFees">Reset</button>
                </div>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5">
                        <h4 class="mb-3 flex items-center gap-2 font-black">
                            <Home class="h-4 w-4 text-emerald-700" />
                            Domestic
                            <span class="text-xs font-bold text-slate-500">(same country / currency)</span>
                        </h4>
                        <p class="mb-1 text-xs font-black text-emerald-800">Send Fee (added on top)</p>
                        <div class="mb-3 grid grid-cols-4 gap-2">
                            <label class="text-[11px] font-bold text-slate-500">%<input v-model.number="p2p.domesticSendPct" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Fixed<input v-model.number="p2p.domesticSendFixed" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Min<input v-model.number="p2p.domesticSendMin" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Max<input v-model.number="p2p.domesticSendMax" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                        </div>
                        <p class="mb-1 text-xs font-black text-emerald-800">Receive Fee (deducted)</p>
                        <div class="grid grid-cols-4 gap-2">
                            <label class="text-[11px] font-bold text-slate-500">%<input v-model.number="p2p.domesticReceivePct" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Fixed<input v-model.number="p2p.domesticReceiveFixed" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Min<input v-model.number="p2p.domesticReceiveMin" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Max<input v-model.number="p2p.domesticReceiveMax" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-5">
                        <h4 class="mb-3 flex items-center gap-2 font-black">
                            <Globe2 class="h-4 w-4 text-sky-700" />
                            International
                            <span class="text-xs font-bold text-slate-500">(cross-border)</span>
                        </h4>
                        <p class="mb-1 text-xs font-black text-sky-800">Send Fee (added on top)</p>
                        <div class="mb-3 grid grid-cols-4 gap-2">
                            <label class="text-[11px] font-bold text-slate-500">%<input v-model.number="p2p.internationalSendPct" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Fixed<input v-model.number="p2p.internationalSendFixed" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Min<input v-model.number="p2p.internationalSendMin" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Max<input v-model.number="p2p.internationalSendMax" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                        </div>
                        <p class="mb-1 text-xs font-black text-sky-800">Receive Fee (deducted)</p>
                        <div class="mb-3 grid grid-cols-4 gap-2">
                            <label class="text-[11px] font-bold text-slate-500">%<input v-model.number="p2p.internationalReceivePct" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Fixed<input v-model.number="p2p.internationalReceiveFixed" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Min<input v-model.number="p2p.internationalReceiveMin" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                            <label class="text-[11px] font-bold text-slate-500">Max<input v-model.number="p2p.internationalReceiveMax" type="number" step="0.01" min="0" :class="compactInputClass" /></label>
                        </div>
                        <p class="mb-1 text-xs font-black text-sky-800">FX / Currency Conversion Spread (%)</p>
                        <input v-model.number="p2p.fxPct" type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2 font-bold outline-none focus:border-sky-300 focus:ring-4 focus:ring-sky-100" />
                        <p class="mt-1 text-[11px] text-slate-500">Applied only on cross-border transfers that convert currency.</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl bg-slate-950 p-5 text-white">
                    <div class="mb-3 flex flex-wrap items-center gap-3">
                        <Calculator class="h-5 w-5 text-emerald-300" />
                        <h4 class="font-black">Live Calculator</h4>
                        <div class="ml-auto flex flex-wrap items-center gap-2">
                            <select v-model="p2p.calcScope" class="rounded-xl border border-white/20 bg-white/10 px-3 py-1.5 font-black">
                                <option>Domestic</option>
                                <option>International</option>
                            </select>
                            <span class="text-sm text-slate-300">Amount</span>
                            <input v-model.number="p2p.amount" type="number" class="w-28 rounded-xl border border-white/20 bg-white/10 px-3 py-1.5 text-right font-black" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">Sender Pays</p>
                            <h3 class="text-2xl font-black text-emerald-300">{{ money(p2pPreview.senderPays) }}</h3>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">Transfer Amount</p>
                            <h3 class="text-2xl font-black">{{ money(p2pPreview.transferAmount) }}</h3>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">Recipient Gets</p>
                            <h3 class="text-2xl font-black text-sky-300">{{ money(p2pPreview.recipientGets) }}</h3>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">LinkUp Earns</p>
                            <h3 class="text-2xl font-black text-lime-300">{{ money(p2pPreview.linkupEarns) }}</h3>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-300">{{ p2pPreview.breakdown }}</p>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white transition hover:bg-slate-800" @click="saveP2PFees">Save Send Money Fees</button>
                    <span class="text-sm text-slate-500">{{ notes.p2p }}</span>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-black">ASUE / Social Savings Fee</h3>
                        <p class="text-slate-500">LinkUp earns a percentage of <b>every hand (payout)</b> in the savings rotation. Each time a member receives their pot, LinkUp deducts this fee.</p>
                    </div>
                    <button class="rounded-2xl border border-slate-200 px-5 py-2 font-black transition hover:bg-slate-50" @click="resetAsueFees">Reset</button>
                </div>

                <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Fee per Hand / Payout (%)</label>
                        <input v-model.number="asue.handPct" type="number" step="0.01" min="0" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-2xl font-black outline-none focus:border-sky-300 focus:ring-4 focus:ring-sky-100" />
                        <p class="mt-1 text-xs text-slate-400">Applied to each draw across the full rotation. Example: a $100 hand at 3% = $3 to LinkUp, member receives $97.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Example hand</span><b>$100.00</b></div>
                        <div class="flex justify-between"><span class="text-slate-500">LinkUp fee</span><b>{{ money(asuePreview.fee) }}</b></div>
                        <div class="flex justify-between"><span class="text-slate-500">Member receives</span><b>{{ money(asuePreview.net) }}</b></div>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white transition hover:bg-slate-800" @click="saveAsueFees">Save ASUE Fee</button>
                    <span class="text-sm text-slate-500">{{ notes.asue }}</span>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-xl font-black">Detailed Fee Settings by Category</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <button class="card rounded-3xl p-5 text-left transition hover:shadow-lg" @click="openShortcut('eventFeeCommand')">
                        <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-purple-100 text-purple-600"><CalendarDays /></div>
                        <h4 class="font-black">Event Fees</h4>
                        <p class="text-sm text-slate-500">Ticket service, processing split, drinks, tables, spa, cookout, wire.</p>
                    </button>
                    <button class="card rounded-3xl p-5 text-left transition hover:shadow-lg" @click="openShortcut('eatsFeesPricingCommand')">
                        <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-orange-100 text-orange-600"><Utensils /></div>
                        <h4 class="font-black">LinkUp Eats Fees</h4>
                        <p class="text-sm text-slate-500">Commission, service fee, delivery, gratuity, processing split.</p>
                    </button>
                    <button class="card rounded-3xl p-5 text-left transition hover:shadow-lg" @click="openShortcut('marketplaceFeesCommand')">
                        <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-sky-100 text-sky-600"><ShoppingBag /></div>
                        <h4 class="font-black">Marketplace Fees</h4>
                        <p class="text-sm text-slate-500">Seller commission, listing and transaction fees.</p>
                    </button>
                    <button class="card rounded-3xl p-5 text-left transition hover:shadow-lg" @click="openShortcut('pricingCommand')">
                        <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-green-100 text-green-600"><Tag /></div>
                        <h4 class="font-black">Pricing & Plans</h4>
                        <p class="text-sm text-slate-500">Subscription tiers and plan pricing.</p>
                    </button>
                </div>
            </div>
        </section>

        <EventFees v-else-if="activeTab === 'eventFeeCommand'" :initial-event-fee-settings="initialEventFeeSettings" />

        <EatsFeesPricing v-else-if="activeTab === 'eatsFeesPricingCommand'" @sync-fees="syncEatsFees" />

        <MarketplaceFees v-else-if="activeTab === 'marketplaceFeesCommand'" @sync-fees="syncMarketplaceFees" />

        <PricingPlans v-else-if="activeTab === 'pricingCommand'" />
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

</style>
