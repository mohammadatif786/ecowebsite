<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Calculator, Layers, Save, Settings2 } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type PlacementModel = 'CPM' | 'CPC' | 'Flat';

type Placement = {
    key: string;
    name: string;
    model: PlacementModel;
    base: number;
    reach: number;
};

type Tier = {
    key: string;
    name: string;
    mult: number;
    perks: string;
};

type Duration = {
    key: string;
    name: string;
    days: number;
    disc: number;
};

type PricingState = {
    placements: Placement[];
    tiers: Tier[];
    durations: Duration[];
    targeting: Record<'geo' | 'audience' | 'category', number>;
};

const STORAGE_KEY = 'linkupAdPricing';

const defaultPricing: PricingState = {
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
    tiers: [
        { key: 'standard', name: 'Standard', mult: 1, perks: 'Standard rotation' },
        { key: 'premium', name: 'Premium', mult: 1.6, perks: 'Priority slots + geo targeting' },
        { key: 'elite', name: 'Elite', mult: 2.5, perks: 'Top slot, full targeting, verified badge' },
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
    targeting: { geo: 20, audience: 30, category: 15 },
};

const pricing = ref<PricingState>(structuredClone(defaultPricing));
const hydrated = ref(false);

const quotePlacement = ref('vibes');
const quoteTier = ref('standard');
const quoteDuration = ref('day');
const quoteVolume = ref<number | null>(null);
const quoteGeo = ref(false);
const quoteAudience = ref(false);
const quoteCategory = ref(false);

const modelUnit = (model: PlacementModel) => {
    if (model === 'CPM') return '/ 1k impressions';
    if (model === 'CPC') return '/ click';
    return '/ day';
};

const volumeUnit = (model: PlacementModel) => {
    if (model === 'CPM') return 'Impressions';
    if (model === 'CPC') return 'Clicks';
    return 'Days';
};

const fmtMoney = (value: number, digits = 2) =>
    `$${Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    })}`;

const fmtWholeMoney = (value: number) =>
    `$${Math.round(Number(value || 0)).toLocaleString()}`;

const selectedPlacement = computed(() => pricing.value.placements.find((item) => item.key === quotePlacement.value) || pricing.value.placements[0]);
const selectedTier = computed(() => pricing.value.tiers.find((item) => item.key === quoteTier.value) || pricing.value.tiers[0]);
const selectedDuration = computed(() => pricing.value.durations.find((item) => item.key === quoteDuration.value) || pricing.value.durations[0]);

const effectiveRate = (placement: Placement, tier: Tier) => Number(placement.base || 0) * Number(tier.mult || 0);

const quote = computed(() => {
    const placement = selectedPlacement.value;
    const tier = selectedTier.value;
    const duration = selectedDuration.value;
    const rate = effectiveRate(placement, tier);
    let targetMult = 1;

    if (quoteGeo.value) targetMult *= 1 + Number(pricing.value.targeting.geo || 0) / 100;
    if (quoteAudience.value) targetMult *= 1 + Number(pricing.value.targeting.audience || 0) / 100;
    if (quoteCategory.value) targetMult *= 1 + Number(pricing.value.targeting.category || 0) / 100;

    const discount = Number(duration.disc || 0) / 100;
    let delivery: number | null = null;
    let units = Number(duration.days || 1);

    if (placement.model !== 'Flat') {
        delivery = quoteVolume.value && quoteVolume.value > 0
            ? Number(quoteVolume.value)
            : Number(placement.reach || 0) * Number(duration.days || 1);
        units = placement.model === 'CPM' ? delivery / 1000 : delivery;
    }

    const gross = rate * units * targetMult;
    const total = gross * (1 - discount);

    return {
        placement,
        tier,
        duration,
        rate,
        targetMult,
        discount,
        delivery,
        units,
        gross,
        total,
        perDay: total / Number(duration.days || 1),
    };
});

const targetCards = [
    { key: 'geo', label: 'Geo / Country' },
    { key: 'audience', label: 'Audience' },
    { key: 'category', label: 'Category' },
] as const;

const persist = () => {
    if (!hydrated.value || typeof window === 'undefined') return;
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(pricing.value));
};

const hydrate = () => {
    if (typeof window !== 'undefined') {
        try {
            const saved = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || 'null');
            if (saved) {
                pricing.value = {
                    placements: Array.isArray(saved.placements) ? saved.placements : structuredClone(defaultPricing.placements),
                    tiers: Array.isArray(saved.tiers) ? saved.tiers : structuredClone(defaultPricing.tiers),
                    durations: Array.isArray(saved.durations) ? saved.durations : structuredClone(defaultPricing.durations),
                    targeting: { ...defaultPricing.targeting, ...(saved.targeting || {}) },
                };
            }
        } catch {
            pricing.value = structuredClone(defaultPricing);
        }
    }

    quotePlacement.value = pricing.value.placements[0]?.key || 'vibes';
    quoteTier.value = pricing.value.tiers[0]?.key || 'standard';
    quoteDuration.value = pricing.value.durations[0]?.key || 'day';
    hydrated.value = true;
};

const saveNow = () => {
    persist();
    toast.success('Ad pricing saved.');
};

const resetPricing = () => {
    if (!window.confirm('Reset Ad Pricing to the default rate card?')) return;
    pricing.value = structuredClone(defaultPricing);
    quotePlacement.value = pricing.value.placements[0].key;
    quoteTier.value = pricing.value.tiers[0].key;
    quoteDuration.value = pricing.value.durations[0].key;
    quoteVolume.value = null;
    toast.success('Ad pricing reset.');
};

watch(pricing, persist, { deep: true });

watch(selectedPlacement, (placement) => {
    if (placement.model === 'Flat') quoteVolume.value = null;
});

onMounted(hydrate);
</script>

<template>
    <div class="pricing-wrap">
        <Toaster rich-colors position="top-right" />

        <section class="pricing-header">
            <div>
                <h3>Ad Pricing <span>- one engine for every placement</span></h3>
                <p>
                    One pricing system across Vibes, In-App, Email, News, Wallet, Push,
                    Marketplace, Live & Events. Set a base rate + model per placement, tier
                    multipliers, and targeting add-ons - every ad prices from here.
                </p>
            </div>
            <div class="header-actions">
                <button type="button" class="soft-btn" @click="resetPricing">Reset Defaults</button>
                <button type="button" class="dark-btn" @click="saveNow">
                    <Save class="btn-icon" />
                    Save Pricing
                </button>
            </div>
        </section>

        <div class="pricing-grid">
            <section class="pricing-card">
                <div class="card-title">
                    <Layers />
                    <div>
                        <h3>Placements & Base Rates</h3>
                        <p>CPM = per 1k impressions - CPC = per click - Flat = per day.</p>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="pricing-table placements-table">
                        <thead>
                            <tr>
                                <th>Placement</th>
                                <th>Model</th>
                                <th>Base Rate</th>
                                <th>Unit</th>
                                <th>Daily Reach</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="placement in pricing.placements" :key="placement.key">
                                <td class="strong-cell">{{ placement.name }}</td>
                                <td>
                                    <select v-model="placement.model" class="mini-select">
                                        <option>CPM</option>
                                        <option>CPC</option>
                                        <option>Flat</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="money-input">
                                        <span>$</span>
                                        <input v-model.number="placement.base" type="number" min="0" step="0.01" />
                                    </div>
                                </td>
                                <td class="muted small">{{ modelUnit(placement.model) }}</td>
                                <td>
                                    <template v-if="placement.model === 'Flat'">
                                        <span class="dash">-</span>
                                    </template>
                                    <template v-else>
                                        <input v-model.number="placement.reach" type="number" min="0" class="reach-input" />
                                        <small>est/day</small>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="side-stack">
                <section class="pricing-card">
                    <div class="card-title compact">
                        <Settings2 />
                        <h3>Tiers</h3>
                    </div>
                    <div class="table-scroll">
                        <table class="pricing-table">
                            <thead>
                                <tr>
                                    <th>Tier</th>
                                    <th>Multiplier</th>
                                    <th>Perks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tier in pricing.tiers" :key="tier.key">
                                    <td class="strong-cell">{{ tier.name }}</td>
                                    <td>
                                        <div class="mult-input">
                                            <span>x</span>
                                            <input v-model.number="tier.mult" type="number" min="0" step="0.1" />
                                        </div>
                                    </td>
                                    <td class="muted small">{{ tier.perks }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="pricing-card">
                    <h3 class="section-title">Durations & Commitment Discounts</h3>
                    <p class="card-copy">Longer commitments = cheaper per day.</p>
                    <div class="table-scroll">
                        <table class="pricing-table">
                            <thead>
                                <tr>
                                    <th>Duration</th>
                                    <th>Days</th>
                                    <th>Discount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="duration in pricing.durations" :key="duration.key">
                                    <td class="strong-cell">{{ duration.name }}</td>
                                    <td class="muted">{{ duration.days }} day{{ duration.days > 1 ? 's' : '' }}</td>
                                    <td>
                                        <div class="percent-input">
                                            <input v-model.number="duration.disc" type="number" min="0" max="100" />
                                            <span>%</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="pricing-card">
                    <h3 class="section-title">Targeting Add-ons</h3>
                    <div class="target-grid">
                        <article v-for="target in targetCards" :key="target.key" class="target-card">
                            <p>{{ target.label }}</p>
                            <div>
                                <span>+</span>
                                <input v-model.number="pricing.targeting[target.key]" type="number" min="0" max="500" />
                                <span>%</span>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </div>

        <section class="pricing-card">
            <h3 class="section-title">Rate Card <span>(base x tier)</span></h3>
            <div class="table-scroll">
                <table class="pricing-table rate-card">
                    <thead>
                        <tr>
                            <th>Placement</th>
                            <th>Model</th>
                            <th v-for="tier in pricing.tiers" :key="tier.key">{{ tier.name }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="placement in pricing.placements" :key="placement.key">
                            <td class="strong-cell">{{ placement.name }}</td>
                            <td class="muted small">{{ placement.model }}</td>
                            <td v-for="tier in pricing.tiers" :key="tier.key" class="rate-value">
                                {{ fmtMoney(effectiveRate(placement, tier)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="pricing-card">
            <div class="card-title">
                <Calculator />
                <h3>Campaign Price Estimator</h3>
            </div>

            <div class="estimator-grid">
                <div class="estimator-form">
                    <label>
                        Placement
                        <select v-model="quotePlacement">
                            <option v-for="placement in pricing.placements" :key="placement.key" :value="placement.key">
                                {{ placement.name }} ({{ placement.model }})
                            </option>
                        </select>
                    </label>
                    <label>
                        Tier
                        <select v-model="quoteTier">
                            <option v-for="tier in pricing.tiers" :key="tier.key" :value="tier.key">
                                {{ tier.name }}
                            </option>
                        </select>
                    </label>
                    <label>
                        Duration
                        <select v-model="quoteDuration">
                            <option v-for="duration in pricing.durations" :key="duration.key" :value="duration.key">
                                {{ duration.name }}{{ duration.disc ? ` (-${duration.disc}%)` : '' }}
                            </option>
                        </select>
                    </label>
                    <label v-if="selectedPlacement.model !== 'Flat'">
                        Custom {{ volumeUnit(selectedPlacement.model) }} (optional override)
                        <input
                            v-model.number="quoteVolume"
                            type="number"
                            min="0"
                            placeholder="auto from reach x days"
                        />
                    </label>
                    <div class="check-row">
                        <label>
                            <input v-model="quoteGeo" type="checkbox" />
                            Geo
                        </label>
                        <label>
                            <input v-model="quoteAudience" type="checkbox" />
                            Audience
                        </label>
                        <label>
                            <input v-model="quoteCategory" type="checkbox" />
                            Category
                        </label>
                    </div>
                </div>

                <div class="quote-result">
                    <div>
                        <span>Effective rate</span>
                        <strong>{{ fmtMoney(quote.rate) }} {{ modelUnit(selectedPlacement.model) }}</strong>
                    </div>
                    <div v-if="selectedPlacement.model !== 'Flat'">
                        <span>Est. delivery</span>
                        <strong>{{ Math.round(quote.delivery || 0).toLocaleString() }} {{ volumeUnit(selectedPlacement.model).toLowerCase() }}</strong>
                    </div>
                    <div v-else>
                        <span>Duration</span>
                        <strong>{{ quote.duration.days }} day{{ quote.duration.days > 1 ? 's' : '' }}</strong>
                    </div>
                    <div>
                        <span>Targeting uplift</span>
                        <strong>x{{ quote.targetMult.toFixed(2) }}</strong>
                    </div>
                    <div v-if="quote.discount > 0">
                        <span>Commitment discount</span>
                        <strong class="green">-{{ Math.round(quote.discount * 100) }}%</strong>
                    </div>
                    <div>
                        <span>Effective / day</span>
                        <strong>{{ fmtMoney(quote.perDay) }}</strong>
                    </div>
                    <div class="quote-total">
                        <span>Total</span>
                        <strong>{{ fmtWholeMoney(quote.total) }}</strong>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.pricing-wrap {
    display: flex;
    flex-direction: column;
    gap: 24px;
    color: #0f172a;
}

.pricing-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
}

.pricing-header h3 {
    margin: 0;
    color: #020617;
    font-size: 30px;
    font-weight: 950;
    letter-spacing: 0;
}

.pricing-header h3 span,
.section-title span {
    color: #94a3b8;
    font-size: 14px;
    font-weight: 800;
}

.pricing-header p,
.card-copy {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.55;
}

.header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.pricing-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(390px, 0.75fr);
    gap: 24px;
}

.side-stack {
    display: grid;
    gap: 24px;
}

.pricing-card {
    border: 1px solid #eaf0f7;
    border-radius: 24px;
    background: #fff;
    padding: 24px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.card-title {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
}

.card-title.compact {
    align-items: center;
}

.card-title svg {
    width: 22px;
    height: 22px;
    margin-top: 2px;
    color: #06b6d4;
}

.card-title h3,
.section-title {
    margin: 0;
    font-size: 20px;
    font-weight: 950;
}

.card-title p {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 13px;
}

.table-scroll {
    overflow-x: auto;
}

.pricing-table {
    width: 100%;
    min-width: 620px;
    border-collapse: collapse;
    text-align: left;
    font-size: 14px;
}

.placements-table {
    min-width: 760px;
}

.pricing-table th {
    padding: 10px 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 950;
    text-transform: uppercase;
}

.pricing-table td {
    border-top: 1px solid #e2e8f0;
    padding: 12px 8px;
    vertical-align: middle;
}

.strong-cell {
    font-weight: 950;
}

.muted {
    color: #64748b;
}

.small {
    font-size: 12px;
    font-weight: 800;
}

.dash {
    color: #cbd5e1;
    font-weight: 950;
}

.mini-select,
.money-input input,
.mult-input input,
.percent-input input,
.target-card input,
.reach-input,
.estimator-form select,
.estimator-form input[type='number'] {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    color: #0f172a;
    font-weight: 850;
    outline: none;
}

.mini-select {
    padding: 7px 9px;
    font-size: 13px;
}

.money-input,
.mult-input,
.percent-input {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 900;
}

.money-input input,
.mult-input input {
    width: 96px;
    padding: 7px 9px;
}

.percent-input input {
    width: 64px;
    padding: 7px 9px;
}

.reach-input {
    width: 110px;
    padding: 7px 9px;
}

td small {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 800;
}

.target-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 14px;
}

.target-card {
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px;
}

.target-card p {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 950;
}

.target-card div {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 950;
}

.target-card input {
    width: 68px;
    padding: 7px 9px;
}

.rate-card {
    min-width: 760px;
    text-align: center;
}

.rate-card th:first-child,
.rate-card td:first-child {
    text-align: left;
}

.rate-value {
    font-weight: 950;
}

.estimator-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, 1fr);
    gap: 24px;
}

.estimator-form {
    display: grid;
    gap: 12px;
}

.estimator-form label {
    color: #475569;
    font-size: 13px;
    font-weight: 900;
}

.estimator-form select,
.estimator-form input[type='number'] {
    display: block;
    width: 100%;
    min-height: 44px;
    margin-top: 5px;
    border-radius: 16px;
    padding: 8px 14px;
}

.check-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding-top: 4px;
}

.check-row label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #0f172a;
}

.check-row input {
    width: 16px;
    height: 16px;
    accent-color: #4f46e5;
}

.quote-result {
    align-self: start;
    border-radius: 18px;
    background: #f8fafc;
    padding: 20px;
}

.quote-result div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 8px 0;
}

.quote-result span {
    color: #64748b;
    font-weight: 850;
}

.quote-result strong {
    text-align: right;
    font-weight: 950;
}

.green {
    color: #16a34a;
}

.quote-total {
    margin-top: 6px;
    border-top: 1px solid #e2e8f0;
}

.quote-total strong {
    color: #16a34a;
    font-size: 28px;
}

.soft-btn,
.dark-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 14px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 950;
    cursor: pointer;
}

.soft-btn {
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #0f172a;
}

.dark-btn {
    border: 0;
    background: #020617;
    color: #fff;
}

.btn-icon {
    width: 16px;
    height: 16px;
}

input:focus,
select:focus {
    border-color: #06b6d4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.12);
}

@media (max-width: 1200px) {
    .pricing-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .pricing-header,
    .estimator-grid {
        grid-template-columns: 1fr;
    }

    .pricing-header {
        flex-direction: column;
    }

    .target-grid {
        grid-template-columns: 1fr;
    }

    .pricing-card {
        padding: 18px;
    }

    .pricing-header h3 {
        font-size: 25px;
    }
}
</style>
