<script setup lang="ts">
import { computed, ref, watch, watchEffect } from 'vue';

const props = defineProps<{
    schedule: {
        startDate: string;
        endDate: string;
        durationDays: number;
        pricePackage: string;
        customCostOverride: string;
        paymentRef: string;
        isPaid: boolean;
    };
    errors?: Record<string, string[]>;
}>();

const emit = defineEmits<{
    (e: 'update-schedule', payload: Partial<typeof props.schedule>): void;
}>();

const activePickDur = ref(props.schedule.durationDays || 14);
const startDate = ref(props.schedule.startDate || getToday());
const endDate = ref(props.schedule.endDate || addDays(startDate.value, activePickDur.value));
const activePricePkg = ref(props.schedule.pricePackage || 'growth');
const customCostOverride = ref(props.schedule.customCostOverride || '');
const paymentRef = ref(props.schedule.paymentRef || '');
const isPaid = ref(props.schedule.isPaid ?? false);

const priceMap: Record<string, { price: number; duration: string; sends: string }> = {
    starter: { price: 249, duration: '14 days', sends: '~8K' },
    growth: { price: 749, duration: '30 days', sends: '~35K' },
    network: { price: 1999, duration: '90 days', sends: 'unlimited' },
};

function getToday() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

function addDays(dateStr: string, days: number) {
    const date = new Date(dateStr);
    date.setDate(date.getDate() + days);
    return date.toISOString().split('T')[0];
}

const selectedPackagePrice = computed(() => priceMap[activePricePkg.value].price);
const campaignInvestment = computed(() => {
    const override = parseFloat(customCostOverride.value);
    return customCostOverride.value !== '' && !Number.isNaN(override)
        ? override
        : selectedPackagePrice.value;
});

watch(activePickDur, (days) => {
    const today = getToday();
    startDate.value = today;
    endDate.value = addDays(today, days);
}, { immediate: true });

watchEffect(() => {
    emit('update-schedule', {
        startDate: startDate.value,
        endDate: endDate.value,
        durationDays: activePickDur.value,
        pricePackage: activePricePkg.value,
        customCostOverride: customCostOverride.value,
        paymentRef: paymentRef.value,
        isPaid: isPaid.value,
    });
});
</script>

<template>
    <div class="form-grid">
        <div class="field">
            <label>Start Date *</label>
            <input type="date" id="ad-start-date" v-model="startDate" />
            <div v-if="errors?.start_date" class="error-message">{{ errors.start_date.join(', ') }}</div>
        </div>
        <div class="field">
            <label>End Date *</label>
            <input type="date" id="ad-end-date" v-model="endDate" />
            <div v-if="errors?.end_date" class="error-message">{{ errors.end_date.join(', ') }}</div>
        </div>
    </div>

    <div class="field mt16">
        <label>Quick Duration</label>
        <div class="duration-grid mt8">
            <div class="dur-pill" :class="{ 'selected': activePickDur === 7 }" @click="activePickDur = 7">7 Days</div>
            <div class="dur-pill" :class="{ 'selected': activePickDur === 14 }" @click="activePickDur = 14">14 Days</div>
            <div class="dur-pill" :class="{ 'selected': activePickDur === 30 }" @click="activePickDur = 30">1 Month</div>
            <div class="dur-pill" :class="{ 'selected': activePickDur === 60 }" @click="activePickDur = 60">2 Months</div>
            <div class="dur-pill" :class="{ 'selected': activePickDur === 90 }" @click="activePickDur = 90">3 Months</div>
            <div class="dur-pill" :class="{ 'selected': activePickDur === 180 }" @click="activePickDur = 180">6 Months</div>
            <div class="dur-pill" :class="{ 'selected': activePickDur === 365 }" @click="activePickDur = 365">1 Year</div>
        </div>
    </div>

    <hr class="divider" />

    <p class="card-title mb16" style="font-size:15px;">Email Sponsorship Package</p>
    <div class="pricing-row">
        <label class="price-card" :class="{ 'selected': activePricePkg === 'starter' }" @click="activePricePkg = 'starter'">
            <input type="radio" name="ad-price" id="ad-pkg-starter" value="249" />
            <div class="price-amt">$249</div>
            <div style="font-weight:700;margin-top:4px;">Starter</div>
            <div class="price-label">14 days · 1 email category · up to 8K sends</div>
        </label>
        <label class="price-card" :class="{ 'selected': activePricePkg === 'growth' }" @click="activePricePkg = 'growth'">
            <input type="radio" name="ad-price" id="ad-pkg-growth" value="749" />
            <div class="price-amt">$749</div>
            <div style="font-weight:700;margin-top:4px;">Growth</div>
            <div class="price-label">30 days · 3 email categories · up to 35K sends</div>
        </label>
        <label class="price-card" :class="{ 'selected': activePricePkg === 'network' }" @click="activePricePkg = 'network'">
            <input type="radio" name="ad-price" id="ad-pkg-network" value="1999" />
            <div class="price-amt">$1,999</div>
            <div style="font-weight:700;margin-top:4px;">Network</div>
            <div class="price-label">90 days · all categories · unlimited sends</div>
        </label>
    </div>

    <!-- Live cost display -->
    <div style="margin-top:16px;padding:16px;background:black;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--brand);margin-bottom:4px;">
                Campaign Investment</div>
            <div style="font-size:26px;font-weight:900;color:#fff;" id="ad-price-display">
                ${{ campaignInvestment.toFixed(2) }}</div>
            <div style="font-size:11px;color:lavender;margin-top:2px;" id="ad-price-note">
                {{ priceMap[activePricePkg].duration }} ·
                {{ activePricePkg.charAt(0).toUpperCase() + activePricePkg.slice(1) }} Package</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;color:lavender;margin-bottom:2px;">Est. Sends</div>
            <div style="font-size:18px;font-weight:800;color:var(--brand-lime);" id="ad-sends-display">
                {{ priceMap[activePricePkg].sends }}</div>
            <div style="font-size:10px;color:lavender;margin-top:4px;">Open rate: 28–42%</div>
        </div>
    </div>

    <hr class="divider" />
    <div class="form-grid">
        <div class="field">
            <label>Custom Cost Override ($)</label>
            <input type="number" id="ad-cost-override" placeholder="0.00" min="0" step="0.01" v-model="customCostOverride" />
            <span class="field-hint">Leave blank to use package price above.</span>
            <div v-if="errors?.custom_cost_override" class="error-message">{{ errors.custom_cost_override.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Payment Reference / Invoice #</label>
            <input type="text" placeholder="INV-2026-001" v-model="paymentRef" />
            <div v-if="errors?.payment_ref" class="error-message">{{ errors.payment_ref.join(', ') }}</div>
        </div>
    </div>
    <div class="check-row mt16">
        <input type="checkbox" id="is-paid" v-model="isPaid" />
        <label for="is-paid">Mark as Paid</label>
    </div>
</template>

<style scoped>
.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
}
</style>
