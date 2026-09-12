<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import {
    BarChart3,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Landmark,
    Megaphone,
    Plus,
    Trash2,
    Users,
    Wallet,
    X,
} from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

defineProps<{
    type?: string;
}>();

type PromoTier = {
    key: string;
    name: string;
    price: number;
    impr: number;
    placements: string;
    perks: string;
};

type PromoRecord = {
    name: string;
    country: string;
    email?: string;
    handle?: string;
};

type Segment = {
    key: string;
    name: string;
    label: string;
    records: PromoRecord[];
};

type BillingRow = {
    who: string;
    label: string;
    amount: number;
    method: string;
    ts: string;
};

type OverageRow = {
    organizer: string;
    placement: string;
    total: number;
    method: string;
    ts: string;
};

type PendingPayment = {
    amount: number;
    label: string;
    who: string;
    onConfirm: (method: string) => void;
};

const STORAGE_KEY = 'linkupOrgPlans';

const defaultTiers: PromoTier[] = [
    {
        key: 'starter',
        name: 'Starter',
        price: 49,
        impr: 50000,
        placements: 'Vibes Feed + event listing boost',
        perks: 'Standard rotation',
    },
    {
        key: 'pro',
        name: 'Pro',
        price: 149,
        impr: 250000,
        placements: '+ Push + Email + C360 News',
        perks: 'Priority slots - featured event',
    },
    {
        key: 'headliner',
        name: 'Headliner',
        price: 399,
        impr: 1000000,
        placements: '+ In-App + Live + homepage feature',
        perks: 'Top slot - full targeting - verified',
    },
];

const segments: Segment[] = [
    {
        key: 'organizers',
        name: 'Event Organizers',
        label: 'organizer',
        records: [
            { name: 'Neon Lounge', country: 'United States', email: 'book@neonlounge.com' },
            { name: 'Electric Carnival', country: 'United States', email: 'info@electriccarnival.com' },
            { name: 'Miami Soca District', country: 'United States', email: 'ads@miamisoca.com' },
            { name: 'Kingston Carnival Crew', country: 'Jamaica', email: 'promo@kingstoncarnival.com' },
            { name: 'Bacchanal Nights', country: 'Trinidad and Tobago', email: 'team@bacchanalnights.com' },
        ],
    },
    {
        key: 'restaurants',
        name: 'Restaurants',
        label: 'restaurant',
        records: [
            { name: 'Bahama Breeze Grill', country: 'Bahamas' },
            { name: 'Kingston Jerk Hut', country: 'Jamaica' },
            { name: 'Trini Roti House', country: 'Trinidad and Tobago' },
            { name: 'Santo Domingo Cocina', country: 'Dominican Republic' },
            { name: 'Bogota Arepas', country: 'Colombia' },
            { name: 'CDMX Taqueria', country: 'Mexico' },
        ],
    },
    {
        key: 'merchants',
        name: 'Merchants',
        label: 'merchant',
        records: [
            { name: 'Island Tech Supply', country: 'Bahamas' },
            { name: 'Caribbean Beauty Store', country: 'Bahamas' },
            { name: 'Soca Style Co.', country: 'Trinidad and Tobago' },
            { name: 'Montego Mobile', country: 'Jamaica' },
        ],
    },
    {
        key: 'marketplace',
        name: 'Marketplace Sellers',
        label: 'seller',
        records: [
            { name: 'Nassau Craft Market', country: 'Bahamas' },
            { name: 'Island Kitchen Supplies', country: 'Bahamas' },
            { name: 'Medellin Moda', country: 'Colombia' },
            { name: 'Rio Artesanato', country: 'Brazil' },
        ],
    },
    {
        key: 'live',
        name: 'Live & Podcasts',
        label: 'creator',
        records: [
            { name: 'Caribbean Talk Live', country: 'Bahamas', handle: '@caribtalk' },
            { name: 'Soca Sessions Podcast', country: 'Trinidad and Tobago', handle: '@socasessions' },
            { name: 'Yard Vibes Radio', country: 'Jamaica', handle: '@yardvibes' },
            { name: 'Latino Beats Live', country: 'Colombia', handle: '@latinobeats' },
        ],
    },
];

const adPricing = {
    placements: [
        { key: 'vibes', name: 'Vibes Feed', model: 'CPM', base: 12, reach: 50000 },
        { key: 'inapp', name: 'In-App Banner', model: 'CPM', base: 8, reach: 80000 },
        { key: 'email', name: 'Email Sponsor', model: 'CPM', base: 15, reach: 40000 },
        { key: 'news', name: 'C360 News', model: 'CPM', base: 10, reach: 30000 },
        { key: 'wallet', name: 'Wallet', model: 'CPM', base: 9, reach: 25000 },
        { key: 'push', name: 'Push Notification', model: 'CPM', base: 20, reach: 200000 },
        { key: 'marketplace', name: 'Marketplace', model: 'CPC', base: 0.45, reach: 1200 },
        { key: 'restaurants', name: 'Restaurants', model: 'CPC', base: 0.35, reach: 900 },
        { key: 'live', name: 'Live Stream', model: 'Flat', base: 250, reach: 1 },
        { key: 'events', name: 'Sponsored Events', model: 'Flat', base: 400, reach: 1 },
        { key: 'clubs', name: 'Clubs & Fetes', model: 'Flat', base: 150, reach: 1 },
    ],
    durations: [
        { key: 'day', name: 'Per Day', days: 1, disc: 0 },
        { key: 'week', name: 'Per Week', days: 7, disc: 5 },
        { key: 'biweekly', name: 'Bi-Weekly', days: 14, disc: 8 },
        { key: 'monthly', name: 'Monthly', days: 30, disc: 10 },
        { key: 'bimonthly', name: 'Bi-Monthly', days: 60, disc: 18 },
        { key: 'quarterly', name: 'Quarterly', days: 90, disc: 25 },
        { key: 'yearly', name: 'Yearly', days: 365, disc: 40 },
    ],
};

const activeSegmentKey = ref('organizers');
const showPlanEditor = ref(false);
const tiers = ref<PromoTier[]>(structuredClone(defaultTiers));
const organizerPlans = ref<Record<string, string>>({});
const orgUsage = ref<Record<string, number>>({});
const promoOverage = ref<OverageRow[]>([]);
const promoBilling = ref<BillingRow[]>([]);
const selectedAdvertiser = ref('');
const selectedPlacement = ref(adPricing.placements[0].key);
const selectedDuration = ref(adPricing.durations[0].key);
const pendingPayment = ref<PendingPayment | null>(null);
const paymentMethod = ref('Wallet');
const hydrated = ref(false);

const activeSegment = computed(() => segments.find((segment) => segment.key === activeSegmentKey.value) || segments[0]);
const records = computed(() => activeSegment.value.records);

const planKey = (recordKey: string, segmentKey = activeSegmentKey.value) => `${segmentKey}:${recordKey}`;
const recordKey = (record: PromoRecord) => record.email || record.handle || record.name;
const findTier = (key?: string) => tiers.value.find((tier) => tier.key === key);

const fmtMoney = (value: number) => `$${Math.round(value || 0).toLocaleString()}`;
const fmtNumber = (value: number) => Math.round(value || 0).toLocaleString();
const compactImpressions = (value: number) => `${(Number(value || 0) / 1000).toLocaleString(undefined, { maximumFractionDigits: 0 })}k`;

const subscriberKeys = computed(() => Object.keys(organizerPlans.value));

const stats = computed(() => {
    const subscribers = subscriberKeys.value;
    const mrr = subscribers.reduce((sum, key) => sum + (findTier(organizerPlans.value[key])?.price || 0), 0);
    const overageRevenue = promoOverage.value.reduce((sum, row) => sum + Number(row.total || 0), 0);
    const allowance = subscribers.reduce((sum, key) => sum + (findTier(organizerPlans.value[key])?.impr || 0), 0);
    const used = subscribers.reduce((sum, key) => sum + (orgUsage.value[key] || 0), 0);

    return {
        subscribers: subscribers.length,
        mrr,
        overageRevenue,
        allowance,
        used,
        allowancePct: allowance ? Math.round((used / allowance) * 100) : 0,
    };
});

const quote = computed(() => {
    const placement = adPricing.placements.find((item) => item.key === selectedPlacement.value) || adPricing.placements[0];
    const duration = adPricing.durations.find((item) => item.key === selectedDuration.value) || adPricing.durations[0];
    const delivery = placement.model === 'Flat' ? null : placement.reach * duration.days;
    const units = placement.model === 'Flat'
        ? duration.days
        : placement.model === 'CPM'
            ? Number(delivery || 0) / 1000
            : Number(delivery || 0);
    const gross = placement.base * units;
    const total = gross * (1 - duration.disc / 100);

    return {
        placement,
        duration,
        delivery,
        total,
        days: duration.days,
    };
});

const selectedAdvertiserName = computed(() => selectedAdvertiser.value || records.value[0]?.name || '');

const selectValue = (event: Event) => (event.target as HTMLSelectElement).value;

const persist = () => {
    if (!hydrated.value || typeof window === 'undefined') return;

    window.localStorage.setItem(STORAGE_KEY, JSON.stringify({
        plans: organizerPlans.value,
        usage: orgUsage.value,
        overage: promoOverage.value,
        billing: promoBilling.value,
        tiers: tiers.value,
    }));
};

const seedPlans = () => {
    if (Object.keys(organizerPlans.value).length) return;

    const organizerRecords = segments[0].records;
    if (organizerRecords[0]) {
        const key = planKey(recordKey(organizerRecords[0]), 'organizers');
        organizerPlans.value[key] = 'pro';
        orgUsage.value[key] = 140000;
    }
    if (organizerRecords[1]) {
        const key = planKey(recordKey(organizerRecords[1]), 'organizers');
        organizerPlans.value[key] = 'starter';
        orgUsage.value[key] = 18000;
    }
    if (organizerRecords[2]) {
        const key = planKey(recordKey(organizerRecords[2]), 'organizers');
        organizerPlans.value[key] = 'headliner';
        orgUsage.value[key] = 620000;
    }
};

const hydrate = () => {
    if (typeof window === 'undefined') return;

    try {
        const saved = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || 'null');
        if (saved) {
            organizerPlans.value = saved.plans || {};
            orgUsage.value = saved.usage || {};
            promoOverage.value = Array.isArray(saved.overage) ? saved.overage : [];
            promoBilling.value = Array.isArray(saved.billing) ? saved.billing : [];
            tiers.value = Array.isArray(saved.tiers) && saved.tiers.length ? saved.tiers : structuredClone(defaultTiers);
        }
    } catch {
        tiers.value = structuredClone(defaultTiers);
    }

    seedPlans();
    selectedAdvertiser.value = records.value[0]?.name || '';
    hydrated.value = true;
    persist();
};

const segmentSubscriberCount = (segmentKey: string) =>
    Object.keys(organizerPlans.value).filter((key) => key.startsWith(`${segmentKey}:`)).length;

const setSegment = (segmentKey: string) => {
    activeSegmentKey.value = segmentKey;
    selectedAdvertiser.value = (segments.find((segment) => segment.key === segmentKey)?.records[0]?.name || '');
};

const subscribersForTier = (tierKey: string) =>
    Object.values(organizerPlans.value).filter((key) => key === tierKey).length;

const addTier = () => {
    tiers.value.push({
        key: `plan${Date.now()}`,
        name: 'New Plan',
        price: 0,
        impr: 0,
        placements: 'Vibes Feed',
        perks: '-',
    });
    toast.success('Promoter plan added.');
};

const deleteTier = (index: number) => {
    const tier = tiers.value[index];
    if (!tier) return;

    const assigned = Object.values(organizerPlans.value).includes(tier.key);
    if (assigned && !window.confirm(`Organizers are on "${tier.name}". Delete anyway? Their plan will be cleared.`)) return;
    if (!assigned && !window.confirm(`Delete plan "${tier.name}"?`)) return;

    Object.keys(organizerPlans.value).forEach((key) => {
        if (organizerPlans.value[key] === tier.key) delete organizerPlans.value[key];
    });
    tiers.value.splice(index, 1);
    toast.success('Promoter plan deleted.');
};

const planForRecord = (record: PromoRecord) => organizerPlans.value[planKey(recordKey(record))] || '';
const tierForRecord = (record: PromoRecord) => findTier(planForRecord(record));

const usageForRecord = (record: PromoRecord) => {
    const key = planKey(recordKey(record));
    const tier = tierForRecord(record);
    const used = orgUsage.value[key] || 0;
    const pct = tier?.impr ? Math.min(100, Math.round((used / tier.impr) * 100)) : 0;
    const state = pct >= 100 ? 'Over - buy overage' : pct >= 80 ? 'Near limit' : 'OK';

    return { used, pct, state };
};

const openPayment = (amount: number, label: string, who: string, onConfirm: (method: string) => void) => {
    paymentMethod.value = 'Wallet';
    pendingPayment.value = { amount, label, who, onConfirm };
};

const closePayment = () => {
    pendingPayment.value = null;
};

const confirmPayment = () => {
    if (!pendingPayment.value) return;

    const payment = pendingPayment.value;
    const method = paymentMethod.value;
    promoBilling.value.unshift({
        who: payment.who,
        label: payment.label,
        amount: payment.amount,
        method,
        ts: new Date().toISOString(),
    });

    closePayment();
    payment.onConfirm(method);
    toast.success(`Paid by ${method} - ${fmtMoney(payment.amount)}`);
};

const requestPlanChange = (record: PromoRecord, tierKey: string) => {
    const key = planKey(recordKey(record));

    if (!tierKey) {
        delete organizerPlans.value[key];
        toast.success('Promote plan cleared.');
        return;
    }

    const tier = findTier(tierKey);
    if (!tier) return;

    openPayment(tier.price, `${tier.name} Promote plan (monthly)`, record.name, () => {
        organizerPlans.value[key] = tierKey;
        if (orgUsage.value[key] === undefined) orgUsage.value[key] = 0;
    });
};

const cancelPlan = (record: PromoRecord) => {
    delete organizerPlans.value[planKey(recordKey(record))];
    toast.success('Promote plan cancelled.');
};

const bookPromotion = () => {
    if (!selectedAdvertiserName.value) {
        toast.error('Please select an advertiser.');
        return;
    }

    openPayment(
        quote.value.total,
        `${quote.value.placement.name} promo (${quote.value.duration.key})`,
        selectedAdvertiserName.value,
        (method) => {
            promoOverage.value.unshift({
                organizer: selectedAdvertiserName.value,
                placement: quote.value.placement.name,
                total: quote.value.total,
                method,
                ts: new Date().toISOString(),
            });
        },
    );
};

const billingBadgeClass = (method: string) => ({
    Wallet: 'pay-wallet',
    Card: 'pay-card',
    'Bank Transfer': 'pay-bank',
}[method] || 'pay-wallet');

watch([tiers, organizerPlans, orgUsage, promoOverage, promoBilling], persist, { deep: true });

onMounted(hydrate);
</script>

<template>
    <div class="promo-wrap">
        <Toaster rich-colors position="top-right" />

        <section class="promo-header">
            <h3>Promote & Advertise</h3>
            <p>
                One promote engine for every vertical - organizers, restaurants, merchants,
                marketplace sellers & live creators. Flat-rate monthly <b>plans</b> include an ad
                allowance; buy extra reach pay-as-you-go at <b>Ad Pricing</b> rates, paid in-system.
            </p>
        </section>

        <div class="promo-tabs">
            <button
                v-for="segment in segments"
                :key="segment.key"
                :class="['promo-tab', { active: activeSegmentKey === segment.key }]"
                type="button"
                @click="setSegment(segment.key)"
            >
                {{ segment.name }}
                <span v-if="segmentSubscriberCount(segment.key)">({{ segmentSubscriberCount(segment.key) }})</span>
            </button>
        </div>

        <section class="promo-kpis">
            <article class="promo-card kpi-card">
                <Users class="kpi-icon" />
                <p>Subscribers</p>
                <h3>{{ stats.subscribers }}</h3>
            </article>
            <article class="promo-card kpi-card">
                <DollarSign class="kpi-icon green" />
                <p>Subscription MRR</p>
                <h3 class="green">{{ fmtMoney(stats.mrr) }}</h3>
                <span>{{ fmtMoney(stats.mrr * 12) }}/yr</span>
            </article>
            <article class="promo-card kpi-card">
                <Megaphone class="kpi-icon indigo" />
                <p>Overage Revenue</p>
                <h3 class="indigo">{{ fmtMoney(stats.overageRevenue) }}</h3>
            </article>
            <article class="promo-card kpi-card">
                <BarChart3 class="kpi-icon" />
                <p>Allowance Used</p>
                <h3>{{ stats.allowancePct }}%</h3>
                <span>{{ compactImpressions(stats.used) }} / {{ compactImpressions(stats.allowance) }} impr</span>
            </article>
        </section>

        <section class="promo-card panel">
            <div class="panel-head">
                <h3>Promoter Plans</h3>
                <button type="button" class="soft-btn" @click="showPlanEditor = !showPlanEditor">
                    Manage Plans
                </button>
            </div>

            <div class="tier-grid">
                <article
                    v-for="tier in tiers"
                    :key="tier.key"
                    :class="['tier-card', { popular: tier.key === 'pro' }]"
                >
                    <div class="tier-title">
                        <h4>{{ tier.name }}</h4>
                        <span v-if="tier.key === 'pro'">Popular</span>
                    </div>
                    <strong>{{ fmtMoney(tier.price) }}<small>/mo</small></strong>
                    <p>{{ tier.placements }}</p>
                    <b>{{ compactImpressions(tier.impr) }} impressions/mo</b>
                    <em>{{ tier.perks }}</em>
                    <small>{{ subscribersForTier(tier.key) }} subscriber{{ subscribersForTier(tier.key) === 1 ? '' : 's' }}</small>
                </article>
            </div>

            <div v-if="showPlanEditor" class="plan-editor">
                <div class="panel-head tight">
                    <h4>Edit Plans</h4>
                    <button type="button" class="dark-btn small" @click="addTier">
                        <Plus class="btn-icon" />
                        Add Plan
                    </button>
                </div>
                <div class="table-scroll">
                    <table class="promo-table edit-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price /mo</th>
                                <th>Impressions /mo</th>
                                <th>Placements</th>
                                <th>Perks</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tier, index) in tiers" :key="tier.key">
                                <td><input v-model="tier.name" class="mini-input name" /></td>
                                <td><input v-model.number="tier.price" type="number" min="0" class="mini-input money" /></td>
                                <td><input v-model.number="tier.impr" type="number" min="0" class="mini-input impr" /></td>
                                <td><input v-model="tier.placements" class="mini-input wide" /></td>
                                <td><input v-model="tier.perks" class="mini-input wide" /></td>
                                <td>
                                    <button type="button" class="danger-btn" @click="deleteTier(index)">
                                        <Trash2 class="btn-icon" />
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="editor-note">Changes apply instantly to plan cards and subscriber dropdowns.</p>
            </div>
        </section>

        <section class="promo-card panel">
            <h3 class="section-title">Subscribers</h3>
            <div class="table-scroll">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>{{ activeSegment.label }}</th>
                            <th>Plan</th>
                            <th>Allowance Used</th>
                            <th>Set Plan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!records.length">
                            <td colspan="5" class="empty-cell">No {{ activeSegment.name.toLowerCase() }}.</td>
                        </tr>
                        <tr v-for="record in records" :key="recordKey(record)">
                            <td class="strong-cell">
                                {{ record.name }}
                                <span>{{ record.country }}</span>
                            </td>
                            <td>
                                <span v-if="tierForRecord(record)" class="plan-pill">
                                    {{ tierForRecord(record)?.name }} - {{ fmtMoney(tierForRecord(record)?.price || 0) }}/mo
                                </span>
                                <span v-else class="muted">No plan</span>
                            </td>
                            <td>
                                <template v-if="tierForRecord(record)">
                                    <div class="usage-copy">
                                        {{ compactImpressions(usageForRecord(record).used) }} /
                                        {{ compactImpressions(tierForRecord(record)?.impr || 0) }}
                                        <span :class="['usage-badge', usageForRecord(record).pct >= 100 ? 'over' : usageForRecord(record).pct >= 80 ? 'near' : 'ok']">
                                            {{ usageForRecord(record).state }}
                                        </span>
                                    </div>
                                    <div class="usage-bar">
                                        <span
                                            :class="[usageForRecord(record).pct >= 100 ? 'over' : usageForRecord(record).pct >= 80 ? 'near' : 'ok']"
                                            :style="{ width: `${usageForRecord(record).pct}%` }"
                                        ></span>
                                    </div>
                                </template>
                                <span v-else class="muted">-</span>
                            </td>
                            <td>
                                <select class="mini-select" :value="planForRecord(record)" @change="requestPlanChange(record, selectValue($event))">
                                    <option value="">- set plan -</option>
                                    <option v-for="tier in tiers" :key="tier.key" :value="tier.key">
                                        {{ tier.name }}
                                    </option>
                                </select>
                            </td>
                            <td>
                                <button
                                    v-if="tierForRecord(record)"
                                    type="button"
                                    class="danger-btn"
                                    @click="cancelPlan(record)"
                                >
                                    Cancel
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="promo-card panel">
            <h3 class="section-title">Promote - Pay-as-you-go</h3>
            <p class="panel-copy">
                Book extra reach beyond a plan's allowance, for the selected segment. Priced from
                the central Ad Pricing engine; paid by Wallet / Card / Transfer.
            </p>

            <div class="paygo-grid">
                <div class="paygo-form">
                    <label>
                        Advertiser
                        <select v-model="selectedAdvertiser">
                            <option v-for="record in records" :key="recordKey(record)" :value="record.name">
                                {{ record.name }}
                            </option>
                        </select>
                    </label>
                    <label>
                        Placement
                        <select v-model="selectedPlacement">
                            <option v-for="placement in adPricing.placements" :key="placement.key" :value="placement.key">
                                {{ placement.name }}
                            </option>
                        </select>
                    </label>
                    <label>
                        Duration
                        <select v-model="selectedDuration">
                            <option v-for="duration in adPricing.durations" :key="duration.key" :value="duration.key">
                                {{ duration.name }}
                            </option>
                        </select>
                    </label>
                    <button type="button" class="dark-btn full" @click="bookPromotion">Book Promotion</button>
                </div>

                <div class="quote-card">
                    <div>
                        <span>Placement</span>
                        <strong>{{ quote.placement.name }}</strong>
                    </div>
                    <div>
                        <span>Est. delivery</span>
                        <strong>{{ quote.delivery ? `${fmtNumber(quote.delivery)} impr` : `${quote.days} days` }}</strong>
                    </div>
                    <div class="quote-total">
                        <span>Overage charge</span>
                        <strong>{{ fmtMoney(quote.total) }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="promo-card panel">
            <h3 class="section-title">Billing & Payments</h3>
            <p class="panel-copy">Subscription & overage charges paid in-system - Wallet, Card or Bank Transfer.</p>

            <div class="table-scroll">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Organizer</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!promoBilling.length">
                            <td colspan="5" class="empty-cell">No payments yet.</td>
                        </tr>
                        <tr v-for="row in promoBilling.slice(0, 30)" :key="`${row.ts}-${row.label}`">
                            <td class="muted">{{ new Date(row.ts).toLocaleDateString() }}</td>
                            <td class="strong-cell">{{ row.who || '-' }}</td>
                            <td>{{ row.label }}</td>
                            <td class="strong-cell">{{ fmtMoney(row.amount) }}</td>
                            <td><span :class="['payment-pill', billingBadgeClass(row.method)]">{{ row.method }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div v-if="pendingPayment" class="modal-backdrop">
            <section class="payment-modal">
                <header>
                    <button type="button" class="modal-close" @click="closePayment">
                        <X />
                    </button>
                    <h3>Pay for Ad</h3>
                    <p>{{ pendingPayment.who }}</p>
                </header>
                <div class="payment-body">
                    <div class="amount-box">
                        <p>{{ pendingPayment.label }}</p>
                        <h3>{{ fmtMoney(pendingPayment.amount) }}</h3>
                    </div>
                    <div class="method-box">
                        <p>Payment method</p>
                        <label>
                            <input v-model="paymentMethod" type="radio" value="Wallet" />
                            <Wallet />
                            LinkUp Wallet
                        </label>
                        <label>
                            <input v-model="paymentMethod" type="radio" value="Card" />
                            <CreditCard />
                            Credit / Debit Card
                        </label>
                        <label>
                            <input v-model="paymentMethod" type="radio" value="Bank Transfer" />
                            <Landmark />
                            Bank Transfer
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="soft-btn" @click="closePayment">Cancel</button>
                        <button type="button" class="dark-btn" @click="confirmPayment">
                            <CheckCircle2 class="btn-icon" />
                            Confirm Payment
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.promo-wrap {
    display: flex;
    flex-direction: column;
    gap: 24px;
    color: #0f172a;
}

.promo-header h3 {
    margin: 0;
    font-size: 30px;
    font-weight: 950;
    letter-spacing: 0;
    color: #020617;
}

.promo-header p,
.panel-copy {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.55;
}

.promo-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.promo-tab {
    border: 0;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 900;
    cursor: pointer;
}

.promo-tab.active {
    background: #020617;
    color: #fff;
}

.promo-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.promo-card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.kpi-card {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 20px;
}

.kpi-card p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
    font-weight: 800;
}

.kpi-card h3 {
    margin: 5px 0 0;
    font-size: 32px;
    font-weight: 950;
}

.kpi-card span {
    display: block;
    margin-top: 2px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.kpi-icon {
    position: absolute;
    right: 18px;
    top: 18px;
    width: 22px;
    height: 22px;
    color: #94a3b8;
}

.green {
    color: #16a34a !important;
}

.indigo {
    color: #4f46e5 !important;
}

.panel {
    border-radius: 24px;
    padding: 24px;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.panel-head.tight {
    margin-bottom: 12px;
}

.panel-head h3,
.section-title {
    margin: 0;
    font-size: 20px;
    font-weight: 950;
}

.panel-head h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 950;
}

.soft-btn,
.dark-btn,
.danger-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 0;
    border-radius: 14px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 950;
    cursor: pointer;
}

.soft-btn {
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #0f172a;
}

.dark-btn {
    background: #020617;
    color: #fff;
}

.dark-btn.small {
    padding: 8px 12px;
}

.dark-btn.full {
    width: 100%;
    min-height: 44px;
}

.danger-btn {
    background: #fff1f2;
    color: #be123c;
    padding: 7px 10px;
    font-size: 12px;
}

.btn-icon {
    width: 15px;
    height: 15px;
}

.tier-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.tier-card {
    border: 2px solid transparent;
    border-radius: 24px;
    padding: 20px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
}

.tier-card.popular {
    border-color: #818cf8;
}

.tier-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.tier-title h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 950;
}

.tier-title span,
.plan-pill {
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    padding: 3px 9px;
    font-size: 11px;
    font-weight: 950;
}

.tier-card strong {
    display: block;
    margin-top: 8px;
    font-size: 30px;
    font-weight: 950;
}

.tier-card small {
    color: #94a3b8;
    font-size: 12px;
    font-weight: 800;
}

.tier-card p,
.tier-card em {
    display: block;
    margin: 8px 0 0;
    color: #64748b;
    font-size: 12px;
    font-style: normal;
    line-height: 1.4;
}

.tier-card b {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #334155;
}

.plan-editor {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
}

.editor-note {
    margin: 10px 0 0;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.table-scroll {
    overflow-x: auto;
}

.promo-table {
    width: 100%;
    min-width: 820px;
    border-collapse: collapse;
    text-align: left;
    font-size: 14px;
}

.promo-table th {
    padding: 10px 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 950;
    text-transform: uppercase;
}

.promo-table td {
    border-top: 1px solid #e2e8f0;
    padding: 12px 8px;
    vertical-align: middle;
}

.strong-cell {
    font-weight: 950;
}

.strong-cell span {
    display: block;
    margin-top: 2px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 800;
}

.muted,
.empty-cell {
    color: #94a3b8;
    font-weight: 800;
}

.empty-cell {
    padding: 28px !important;
    text-align: center;
}

.mini-input,
.mini-select,
.paygo-form select {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    padding: 8px 10px;
    color: #0f172a;
    font-size: 13px;
    font-weight: 800;
    outline: none;
}

.mini-input:focus,
.mini-select:focus,
.paygo-form select:focus {
    border-color: #06b6d4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.12);
}

.mini-input.name {
    min-width: 118px;
}

.mini-input.money {
    width: 92px;
}

.mini-input.impr {
    width: 130px;
}

.mini-input.wide {
    min-width: 190px;
}

.usage-copy {
    min-width: 170px;
    font-size: 12px;
    font-weight: 800;
}

.usage-badge {
    margin-left: 5px;
    border-radius: 999px;
    padding: 2px 7px;
    font-size: 10px;
    font-weight: 950;
}

.usage-badge.ok {
    background: #dcfce7;
    color: #15803d;
}

.usage-badge.near {
    background: #fef3c7;
    color: #b45309;
}

.usage-badge.over {
    background: #ffe4e6;
    color: #be123c;
}

.usage-bar {
    width: 116px;
    height: 7px;
    overflow: hidden;
    border-radius: 999px;
    background: #f1f5f9;
    margin-top: 6px;
}

.usage-bar span {
    display: block;
    height: 100%;
    border-radius: inherit;
}

.usage-bar .ok {
    background: #6366f1;
}

.usage-bar .near {
    background: #f59e0b;
}

.usage-bar .over {
    background: #e11d48;
}

.paygo-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, 1fr);
    gap: 24px;
    margin-top: 16px;
}

.paygo-form {
    display: grid;
    gap: 12px;
}

.paygo-form label {
    color: #475569;
    font-size: 13px;
    font-weight: 900;
}

.paygo-form select {
    display: block;
    margin-top: 5px;
    min-height: 44px;
    border-radius: 16px;
    padding-inline: 14px;
}

.quote-card {
    align-self: start;
    border-radius: 18px;
    background: #f8fafc;
    padding: 20px;
}

.quote-card div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 8px 0;
}

.quote-card span {
    color: #64748b;
    font-weight: 800;
}

.quote-card strong {
    text-align: right;
    font-weight: 950;
}

.quote-total {
    margin-top: 8px;
    border-top: 1px solid #e2e8f0;
}

.quote-total strong {
    color: #16a34a;
    font-size: 28px;
}

.payment-pill {
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 950;
}

.pay-wallet {
    background: #dcfce7;
    color: #15803d;
}

.pay-card {
    background: #e0f2fe;
    color: #0369a1;
}

.pay-bank {
    background: #fef3c7;
    color: #b45309;
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(2, 6, 23, 0.5);
    padding: 20px;
}

.payment-modal {
    width: min(100%, 390px);
    overflow: hidden;
    border-radius: 24px;
    background: #fff;
    box-shadow: 0 25px 80px rgba(2, 6, 23, 0.35);
}

.payment-modal header {
    position: relative;
    padding: 20px;
    background: linear-gradient(90deg, #0f172a, #334155);
    color: #fff;
}

.payment-modal header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 950;
}

.payment-modal header p {
    margin: 4px 0 0;
    color: #cbd5e1;
    font-size: 13px;
}

.modal-close {
    position: absolute;
    right: 14px;
    top: 14px;
    display: grid;
    width: 32px;
    height: 32px;
    place-items: center;
    border: 0;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.13);
    color: #fff;
    cursor: pointer;
}

.modal-close svg {
    width: 17px;
    height: 17px;
}

.payment-body {
    display: grid;
    gap: 16px;
    padding: 20px;
}

.amount-box {
    border-radius: 18px;
    background: #f8fafc;
    padding: 16px;
}

.amount-box p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
    font-weight: 800;
}

.amount-box h3 {
    margin: 5px 0 0;
    font-size: 30px;
    font-weight: 950;
}

.method-box > p {
    margin: 0 0 8px;
    color: #475569;
    font-size: 13px;
    font-weight: 900;
}

.method-box label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px;
    font-weight: 900;
    cursor: pointer;
}

.method-box svg {
    width: 19px;
    height: 19px;
    color: #64748b;
}

.method-box input {
    accent-color: #020617;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@media (max-width: 1100px) {
    .promo-kpis,
    .tier-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .promo-header h3 {
        font-size: 25px;
    }

    .promo-kpis,
    .tier-grid,
    .paygo-grid {
        grid-template-columns: 1fr;
    }

    .panel {
        padding: 18px;
    }

    .panel-head,
    .modal-actions {
        align-items: stretch;
        flex-direction: column;
    }
}
</style>
