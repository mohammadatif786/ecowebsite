<script setup lang="ts">
import { computed } from 'vue';
import {
    Banknote,
    CreditCard,
    Landmark,
    Receipt,
    Ticket,
} from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

type EventFeeForm = {
    serviceFeePct: number;
    serviceFeeFixed: number;
    processingFeePct: number;
    processingFeeFixed: number;
    processingLinkUpSharePct: number;
    processingBankSharePct: number;
    taxRate: number;
    taxInclusive: boolean;
    currency: 'USD' | 'BSD' | 'JMD' | 'TTD';
    drinkFeePct: number;
    bottleFeePct: number;
    vipFeePct: number;
    addonFeePct: number;
    spaPlatformFeePct: number;
    spaPlatformFeeFixed: number;
    spaGratuityDefaultPct: number;
    spaGratuityEnabled: boolean;
    spaUseGlobalTax: boolean;
    cookoutPlatformFeePercent: number;
    cookoutPlatformFeeFixed: number;
    cookoutDefaultGratuity: number;
    cookoutEnableGratuity: boolean;
    wireProcessingFeePct: number;
    wireProcessingFeeFixed: number;
};

const props = defineProps<{
    initialEventFeeSettings?: Record<string, any>;
}>();

const num = (value: any, fallback = 0) => (value === null || value === undefined ? fallback : Number(value));
const bool = (value: any, fallback = false) => (value === null || value === undefined ? fallback : Boolean(value));

const s = props.initialEventFeeSettings ?? {};

const form = useForm<EventFeeForm>({
    serviceFeePct: num(s.service_fee_pct, 3.5),
    serviceFeeFixed: num(s.service_fee_fixed, 1),
    processingFeePct: num(s.processing_fee_pct, 2.9),
    processingFeeFixed: num(s.processing_fee_fixed, 0.3),
    processingLinkUpSharePct: num(s.processing_linkup_share_pct, 60),
    processingBankSharePct: num(s.processing_bank_share_pct, 40),
    taxRate: num(s.tax_rate, 7),
    taxInclusive: bool(s.tax_inclusive, true),
    currency: s.currency ?? 'USD',
    drinkFeePct: num(s.drink_fee_pct, 5),
    bottleFeePct: num(s.bottle_fee_pct, 6),
    vipFeePct: num(s.vip_fee_pct, 5),
    addonFeePct: num(s.addon_fee_pct, 4),
    spaPlatformFeePct: num(s.spa_platform_fee_pct, 6),
    spaPlatformFeeFixed: num(s.spa_platform_fee_fixed, 0.75),
    spaGratuityDefaultPct: num(s.spa_gratuity_default_pct, 0),
    spaGratuityEnabled: bool(s.spa_gratuity_enabled, true),
    spaUseGlobalTax: bool(s.spa_use_global_tax, true),
    cookoutPlatformFeePercent: num(s.cookout_platform_fee_percent, 6),
    cookoutPlatformFeeFixed: num(s.cookout_platform_fee_fixed, 0.75),
    cookoutDefaultGratuity: num(s.cookout_default_gratuity, 0),
    cookoutEnableGratuity: bool(s.cookout_enable_gratuity, false),
    wireProcessingFeePct: num(s.wire_processing_fee_pct, 3),
    wireProcessingFeeFixed: num(s.wire_processing_fee_fixed, 1),
});

const inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-purple-300 focus:ring-4 focus:ring-purple-100';

const currencySymbol = computed(() => {
    const symbols = { USD: '$', BSD: 'B$', JMD: 'J$', TTD: 'TT$' };
    return symbols[form.currency] || '$';
});

const money = (value: number) => `${currencySymbol.value}${Number(value || 0).toFixed(2)}`;

const ticketFees = computed(() => {
    const amount = 100;
    const platform = amount * (form.serviceFeePct / 100) + form.serviceFeeFixed;
    const processing = amount * (form.processingFeePct / 100) + form.processingFeeFixed;
    const linkupProcessing = processing * (form.processingLinkUpSharePct / 100);
    const bankProcessing = processing * (form.processingBankSharePct / 100);
    const tax = !form.taxInclusive ? amount * (form.taxRate / 100) : 0;

    return {
        amount,
        platform,
        processing,
        linkupProcessing,
        bankProcessing,
        tax,
        total: amount + platform + processing + tax,
    };
});

const summaryCards = computed(() => [
    {
        label: 'Ticket Service Fee',
        value: `${form.serviceFeePct.toFixed(2)}% + ${money(form.serviceFeeFixed)}`,
        detail: 'Platform fee per ticket',
        icon: Ticket,
        tone: 'purple',
    },
    {
        label: 'Processing Fee',
        value: `${form.processingFeePct.toFixed(2)}% + ${money(form.processingFeeFixed)}`,
        detail: `Split ${form.processingLinkUpSharePct.toFixed(0)}/${form.processingBankSharePct.toFixed(0)}`,
        icon: CreditCard,
        tone: 'sky',
    },
    {
        label: 'LinkUp Processing Share',
        value: `${form.processingLinkUpSharePct.toFixed(0)}%`,
        detail: 'Processor revenue to LinkUp',
        icon: Landmark,
        tone: 'green',
    },
    {
        label: 'Bank Processing Share',
        value: `${form.processingBankSharePct.toFixed(0)}%`,
        detail: 'Processor revenue to bank/rail',
        icon: Banknote,
        tone: 'amber',
    },
    {
        label: 'Tax Setting',
        value: `${form.taxRate.toFixed(2)}% ${form.taxInclusive ? 'Inclusive' : 'Exclusive'}`,
        detail: 'Default event tax',
        icon: Receipt,
        tone: 'rose',
    },
]);

const previewCards = computed(() => {
    const addOn = 50 * (form.addonFeePct / 100);
    const spa = 100 * (form.spaPlatformFeePct / 100) + form.spaPlatformFeeFixed;
    const cookout = 100 * (form.cookoutPlatformFeePercent / 100) + form.cookoutPlatformFeeFixed;

    return [
        ['Customer Ticket Price', money(100), 'Base demo ticket'],
        ['Platform Ticket Fee', money(ticketFees.value.platform), 'LinkUp service revenue'],
        ['Processing Fee Total', money(ticketFees.value.processing), 'Split between LinkUp and bank'],
        ['Customer Total Paid', money(ticketFees.value.total), 'Ticket + fees + tax'],
        ['Drink / Add-On Fee', money(addOn), 'On $50 add-on'],
        ['Spa Platform Fee', money(spa), 'On $100 spa service'],
        ['Cookout Platform Fee', money(cookout), 'On $100 cookout order'],
        ['Payout Wire Fee', `${form.wireProcessingFeePct.toFixed(2)}% + ${money(form.wireProcessingFeeFixed)}`, 'Vendor payout processing'],
    ];
});

const toneClasses = (tone: string) => {
    const tones: Record<string, string> = {
        purple: 'bg-purple-100 text-purple-600',
        sky: 'bg-sky-100 text-sky-600',
        green: 'bg-green-100 text-green-600',
        amber: 'bg-amber-100 text-amber-600',
        rose: 'bg-rose-100 text-rose-600',
    };

    return tones[tone] || tones.purple;
};

const syncProcessingSplit = (source: 'linkup' | 'bank') => {
    if (source === 'linkup') {
        form.processingLinkUpSharePct = Math.max(0, Math.min(100, Number(form.processingLinkUpSharePct || 0)));
        form.processingBankSharePct = 100 - form.processingLinkUpSharePct;
    } else {
        form.processingBankSharePct = Math.max(0, Math.min(100, Number(form.processingBankSharePct || 0)));
        form.processingLinkUpSharePct = 100 - form.processingBankSharePct;
    }
};

const saveEventFeeSettings = () => {
    form.put(route('admin.administration.event-fees.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Event fee settings saved.'),
        onError: () => toast.error('Could not save event fee settings.'),
    });
};

const revertChanges = () => {
    form.reset();
    toast.success('Reverted to last saved settings.');
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="gradient-title text-3xl font-black">Platform Fee Settings</h3>
                <p class="text-slate-500">
                    Configure event ticket fees, add-on purchase fees, tax, currency, spa/cookout settings, payout fees, and the LinkUp/bank processing split.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="rounded-2xl border border-slate-200 bg-white px-5 py-3 font-black text-slate-700" @click="revertChanges">Revert Changes</button>
                <button class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 px-6 py-3 font-black text-white shadow-lg shadow-purple-200" :disabled="form.processing" @click="saveEventFeeSettings">
                    Save Fee Settings
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
            <div v-for="card in summaryCards" :key="card.label" class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl" :class="toneClasses(card.tone)">
                    <component :is="card.icon" />
                </div>
                <div>
                    <p class="font-bold text-slate-500">{{ card.label }}</p>
                    <h3 class="text-2xl font-black">{{ card.value }}</h3>
                    <p class="text-xs text-slate-500">{{ card.detail }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <div class="card overflow-hidden rounded-3xl 2xl:col-span-2">
                <div class="flex items-center gap-3 bg-gradient-to-r from-purple-600 to-fuchsia-500 p-5 text-white">
                    <Ticket />
                    <div>
                        <h3 class="text-xl font-black">Ticket Sales Fees</h3>
                        <p class="text-sm text-purple-100">Platform fees charged on ticket purchases</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <label class="block"><span class="font-bold text-slate-600">Service Fee (%)</span><input v-model.number="form.serviceFeePct" type="number" step="0.01" :class="inputClass" /><small class="text-slate-400">Percent of ticket price</small></label>
                    <label class="block"><span class="font-bold text-slate-600">Service Fee (Fixed)</span><input v-model.number="form.serviceFeeFixed" type="number" step="0.01" :class="inputClass" /><small class="text-slate-400">Fixed fee per ticket</small></label>
                    <label class="block"><span class="font-bold text-slate-600">Processing Fee (%)</span><input v-model.number="form.processingFeePct" type="number" step="0.01" :class="inputClass" /><small class="text-slate-400">Payment processor percent</small></label>
                    <label class="block"><span class="font-bold text-slate-600">Processing Fee (Fixed)</span><input v-model.number="form.processingFeeFixed" type="number" step="0.01" :class="inputClass" /><small class="text-slate-400">Payment processor fixed fee</small></label>
                </div>
            </div>

            <div class="card overflow-hidden rounded-3xl">
                <div class="border-b border-slate-100 p-5">
                    <h3 class="text-xl font-black">Processing Fee Split</h3>
                    <p class="text-sm text-slate-500">Bank / processor revenue share</p>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-2 gap-3">
                        <label><span class="font-bold text-slate-600">LinkUp %</span><input v-model.number="form.processingLinkUpSharePct" type="number" step="1" :class="inputClass" @input="syncProcessingSplit('linkup')" /></label>
                        <label><span class="font-bold text-slate-600">Bank %</span><input v-model.number="form.processingBankSharePct" type="number" step="1" :class="inputClass" @input="syncProcessingSplit('bank')" /></label>
                    </div>
                    <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                        <p class="font-bold text-slate-500">Processing Split</p>
                        <div class="mt-3 h-4 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-gradient-to-r from-purple-600 to-fuchsia-500" :style="{ width: `${form.processingLinkUpSharePct}%` }"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-2xl bg-white p-3"><p>LinkUp Share</p><b>{{ money(ticketFees.linkupProcessing) }}</b></div>
                            <div class="rounded-2xl bg-white p-3"><p>Bank Share</p><b>{{ money(ticketFees.bankProcessing) }}</b></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <div class="card overflow-hidden rounded-3xl 2xl:col-span-2">
                <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Tax Settings</h3><p class="text-sm text-slate-500">Default tax configuration</p></div>
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <label><span class="font-bold text-slate-600">Tax Rate (%)</span><input v-model.number="form.taxRate" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Tax Type</span><select v-model="form.taxInclusive" :class="inputClass"><option :value="true">Inclusive</option><option :value="false">Exclusive</option></select></label>
                </div>
            </div>
            <div class="card overflow-hidden rounded-3xl">
                <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Platform Currency</h3><p class="text-sm text-slate-500">Default currency for event transactions</p></div>
                <div class="p-6">
                    <label><span class="font-bold text-slate-600">Currency</span><select v-model="form.currency" :class="inputClass"><option value="USD">USD - US Dollar</option><option value="BSD">BSD - Bahamian Dollar</option><option value="JMD">JMD - Jamaican Dollar</option><option value="TTD">TTD - Trinidad Dollar</option></select></label>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden rounded-3xl">
            <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Add-On Purchase Fees</h3><p class="text-sm text-slate-500">Platform fees for drinks, bottles, tables, food, cookout and other event add-ons</p></div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2 xl:grid-cols-4">
                <label><span class="font-bold text-slate-600">Drink Fee (%)</span><input v-model.number="form.drinkFeePct" type="number" step="0.01" :class="inputClass" /></label>
                <label><span class="font-bold text-slate-600">Bottle Fee (%)</span><input v-model.number="form.bottleFeePct" type="number" step="0.01" :class="inputClass" /></label>
                <label><span class="font-bold text-slate-600">VIP Table Fee (%)</span><input v-model.number="form.vipFeePct" type="number" step="0.01" :class="inputClass" /></label>
                <label><span class="font-bold text-slate-600">General Add-On Fee (%)</span><input v-model.number="form.addonFeePct" type="number" step="0.01" :class="inputClass" /></label>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card overflow-hidden rounded-3xl">
                <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Wellness & Spa</h3><p class="text-sm text-slate-500">Platform fees and settings for spa services</p></div>
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <label><span class="font-bold text-slate-600">Platform Fee (%)</span><input v-model.number="form.spaPlatformFeePct" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Platform Fee (Fixed)</span><input v-model.number="form.spaPlatformFeeFixed" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Default Gratuity (%)</span><input v-model.number="form.spaGratuityDefaultPct" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Enable Gratuity</span><select v-model="form.spaGratuityEnabled" :class="inputClass"><option :value="true">Yes</option><option :value="false">No</option></select></label>
                    <label class="md:col-span-2"><span class="font-bold text-slate-600">Use Global Tax</span><select v-model="form.spaUseGlobalTax" :class="inputClass"><option :value="true">Yes</option><option :value="false">No</option></select></label>
                </div>
            </div>

            <div class="card overflow-hidden rounded-3xl">
                <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Cookout</h3><p class="text-sm text-slate-500">Platform fees and settings for cookout services</p></div>
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <label><span class="font-bold text-slate-600">Platform Fee (%)</span><input v-model.number="form.cookoutPlatformFeePercent" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Platform Fee (Fixed)</span><input v-model.number="form.cookoutPlatformFeeFixed" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Default Gratuity (%)</span><input v-model.number="form.cookoutDefaultGratuity" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Enable Gratuity</span><select v-model="form.cookoutEnableGratuity" :class="inputClass"><option :value="true">Yes</option><option :value="false">No</option></select></label>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
            <div class="card overflow-hidden rounded-3xl">
                <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Payout Processing</h3><p class="text-sm text-slate-500">Wire transfer fees for vendor payouts</p></div>
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <label><span class="font-bold text-slate-600">Wire Processing Fee (%)</span><input v-model.number="form.wireProcessingFeePct" type="number" step="0.01" :class="inputClass" /></label>
                    <label><span class="font-bold text-slate-600">Wire Processing Fee (Fixed)</span><input v-model.number="form.wireProcessingFeeFixed" type="number" step="0.01" :class="inputClass" /></label>
                </div>
            </div>
            <div class="card overflow-hidden rounded-3xl 2xl:col-span-2">
                <div class="border-b border-slate-100 p-5"><h3 class="text-xl font-black">Fee Preview</h3><p class="text-sm text-slate-500">Demo preview using a $100 ticket and a $50 add-on purchase</p></div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                    <div v-for="item in previewCards" :key="item[0]" class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
                        <p class="font-bold text-slate-500">{{ item[0] }}</p>
                        <h3 class="mt-1 text-2xl font-black">{{ item[1] }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ item[2] }}</p>
                    </div>
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

.gradient-title {
    background: linear-gradient(90deg, #7c3aed, #d946ef);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
</style>
