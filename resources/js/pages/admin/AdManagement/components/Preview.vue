<script setup lang="ts">
import axios from 'axios';
import { computed, ref } from 'vue';
import { toast } from "vue-sonner";

const props = defineProps<{
    activeTab: string;
    adId: number | null;
    isEditMode: boolean;
    adData: {
        category: string;
        advertiserName: string;
        websiteUrl: string;
        contactEmail: string;
        contactPhone: string;
        headline: string;
        shortDescription: string;
        country: string;
        state: string;
        city: string;
        venue: string;
        adType: string;
        imagePreview: string;
        videoPreview: string;
        thumbnailPreview: string;
        maxDuration: string;
        autoplaySound: string;
        ctaOverlayTiming: string;
        loopVideo: string;
        ctaText: string;
        brandColor: string;
        startDate: string;
        endDate: string;
        durationDays: number;
        pricePackage: string;
        customCostOverride: string;
        paymentRef: string;
        isPaid: boolean;
        status: string;
        targetingNotes: string;
    };
}>();

const emit = defineEmits(['updatedTab', 'validationErrors']);
const status = ref(props.adData.status || 'Active (publish immediately)');
const targetingNotes = ref(props.adData.targetingNotes || '');

const badgeLabel = computed(() => {
    const map: Record<string, string> = {
        restaurant: 'RESTAURANT',
        club: 'CLUB / FÊTE',
        general: 'GENERAL AD',
    };
    return `SPONSORED · ${map[props.adData.category] ?? 'AD'}`;
});

const previewImage = computed(() => {
    if (props.adData.adType === 'video') {
        return props.adData.thumbnailPreview || props.adData.videoPreview || props.adData.imagePreview;
    }
    return props.adData.imagePreview || props.adData.thumbnailPreview || '';
});

const titleText = computed(() => props.adData.advertiserName || 'Your Ad Name');
const descriptionText = computed(() => props.adData.shortDescription || props.adData.headline || 'Short description here');
const displayDateRange = computed(() => {
    if (props.adData.startDate && props.adData.endDate) {
        return `${props.adData.startDate} → ${props.adData.endDate}`;
    }
    return 'Schedule not set';
});

const packageCost = computed(() => {
    const prices: Record<string, number> = {
        starter: 249,
        growth: 749,
        network: 1999,
    };
    const override = parseFloat(props.adData.customCostOverride);
    if (props.adData.customCostOverride && !Number.isNaN(override)) {
        return `$${override.toFixed(2)}`;
    }
    return `$${(prices[props.adData.pricePackage] ?? 0).toFixed(2)}`;
});

const packageLabel = computed(() => props.adData.pricePackage ? `${props.adData.pricePackage.charAt(0).toUpperCase() + props.adData.pricePackage.slice(1)} Package` : 'Package');

const formatLabel = computed(() => {
    const nameMap: Record<string, string> = {
        restaurant: 'Restaurant',
        club: 'Club / Fête',
        general: 'General Ad',
    };
    return nameMap[props.adData.category] || 'Ad';
});

const isSubmitting = ref(false);
const errors = ref<Record<string, string[]>>({});

const buildPayload = (publicationStatus: string) => ({
    category: props.adData.category,
    advertiser_name: props.adData.advertiserName,
    website_url: props.adData.websiteUrl,
    contact_email: props.adData.contactEmail,
    contact_phone: props.adData.contactPhone,
    headline: props.adData.headline,
    short_description: props.adData.shortDescription,
    country: props.adData.country,
    state: props.adData.state,
    city: props.adData.city,
    venue: props.adData.venue,
    ad_type: props.adData.adType,
    image: props.adData.imagePreview,
    video: props.adData.videoPreview,
    thumbnail: props.adData.thumbnailPreview,
    max_duration: props.adData.maxDuration,
    autoplay_sound: props.adData.autoplaySound,
    cta_overlay_timing: props.adData.ctaOverlayTiming,
    loop_video: props.adData.loopVideo,
    cta_text: props.adData.ctaText,
    brand_color: props.adData.brandColor,
    start_date: props.adData.startDate,
    end_date: props.adData.endDate,
    duration_days: props.adData.durationDays,
    price_package: props.adData.pricePackage,
    custom_cost_override: props.adData.customCostOverride,
    payment_ref: props.adData.paymentRef,
    is_paid: props.adData.isPaid,
    publication_status: publicationStatus,
    targeting_notes: targetingNotes.value,
});

const saveAsDraft = async () => {
    await submitAd('Draft (save without publishing)');
};

// Reads the XSRF-TOKEN cookie, which Laravel reissues on every response.
// Unlike the <meta name="csrf-token"> tag (rendered once on initial page load
// and never refreshed by Inertia's client-side navigation), this stays valid
// for the lifetime of the session even if this wizard tab sits open a while.
const getXsrfToken = () => {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
};

const submitAd = async (publicationStatus = status.value) => {
    isSubmitting.value = true;
    errors.value = {};

    const payload = buildPayload(publicationStatus);

    try {
        let response: Response;

        if (props.isEditMode && props.adId) {
            // UPDATE existing ad via PUT
            response = await fetch(route('admin.ads.update', props.adId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getXsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

        } else {
            // CREATE new ad via POST
            response = await fetch(route('admin.ads.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getXsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
        }

        const responseData = await response.json();

        if (!response.ok) {
            errors.value = responseData.errors || {};
            console.error('Ad submission failed:', responseData);
            emit('validationErrors', errors.value);
            return;
        }

        if (props.isEditMode) {
            toast.success('Ad updated successfully!');

            window.dispatchEvent(new CustomEvent('adUpdated', { detail: responseData.advertisement }));
        } else {
            toast.success('Ad published successfully!');

            window.dispatchEvent(new CustomEvent('adPublished', { detail: responseData.advertisement }));
        }
    } catch (error) {
        console.error('Unexpected error when submitting ad:', error);
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="preview-wrap">
        <div class="preview-phone">
            <div class="preview-inner">
                <div class="preview-img" id="pv-img">
                    <img v-if="previewImage" :src="previewImage" alt="Ad preview"
                        style="width:100%;height:100%;border-radius:inherit;object-fit:cover;" />
                    <span v-else
                        style="font-size:32px;display:flex;align-items:center;justify-content:center;height:100%;">📢</span>
                </div>
                <div class="preview-info">
                    <span class="preview-badge" id="pv-badge">{{ badgeLabel }}</span>
                    <div class="preview-name" id="pv-name">{{ titleText }}</div>
                    <div class="preview-sub" id="pv-desc">{{ descriptionText }}</div>
                </div>
                <div class="swipe-hints">
                    <button class="swipe-btn swipe-left">✕</button>
                    <button class="swipe-btn swipe-right">♥</button>
                </div>
            </div>
        </div>
        <div style="flex:1">
            <h4 style="font-weight:700;margin-bottom:16px;">Review Before Publishing</h4>
            <div class="field mb16">
                <label>Status</label>
                <select v-model="status">
                    <option>Active (publish immediately)</option>
                    <option>Scheduled (go live on start date)</option>
                    <option>Draft (save without publishing)</option>
                </select>
            </div>
            <div class="field mb16">
                <label>Targeting Notes</label>
                <textarea v-model="targetingNotes"
                    placeholder="Optional internal notes about targeting, audience, or special requirements"></textarea>
            </div>
            <div style="font-size:13px;color:var(--mid);margin-bottom:14px;">
                <div><strong>Schedule:</strong> {{ displayDateRange }}</div>
                <div><strong>Package:</strong> {{ packageLabel }} · {{ packageCost }}</div>
                <div><strong>Format:</strong> {{ formatLabel }} · {{ props.adData.adType === 'video' ? 'Video' : 'Image'
                }}</div>
                <div><strong>CTA:</strong> {{ props.adData.ctaText || 'Visit Website' }}</div>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px;">
                <button class="btn btn-outline" @click="emit('updatedTab', 'tab-schedule')">← Back</button>
                <button class="btn btn-ghost" @click="saveAsDraft()">Save as Draft</button>
                <button class="btn btn-primary" @click="submitAd()" :disabled="isSubmitting">
                    {{ isEditMode ? '✓ Update Ad' : '✓ Publish Ad' }}
                </button>
            </div>
        </div>
    </div>
</template>
