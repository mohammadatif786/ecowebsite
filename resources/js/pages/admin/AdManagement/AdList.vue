<script setup lang="ts">
import Creative from '@/pages/admin/AdManagement/components/Creative.vue';
import Details from '@/pages/admin/AdManagement/components/Details.vue';
import Preview from '@/pages/admin/AdManagement/components/Preview.vue';
import ScheduleAndPricing from '@/pages/admin/AdManagement/components/ScheduleAndPricing.vue';
import { useAdCategory } from '@/pages/admin/AdManagement/composables/useAdCategory';
import { useAdMetrics } from '@/pages/admin/AdManagement/composables/useAdMetrics';
import { useAdStatus } from '@/pages/admin/AdManagement/composables/useAdStatus';
import { useAdType } from '@/pages/admin/AdManagement/composables/useAdType';
import '@/pages/admin/AdManagement/style.css';
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    ads: any[];
    adStats: any;
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

// Reuse the same category/status/ad-type/duration logic as the real admin/ads module
// so labels stay in sync with what's actually stored in the advertisements table.
const { getStatus } = useAdStatus();
const { getCategory } = useAdCategory();
const { getAdType } = useAdType();
const { getDurationDays } = useAdMetrics();

// Admin's pill-badge visual system keyed off the same raw DB values the composables above normalize.
const STATUS_PILL_CLASS: Record<string, string> = {
    Active: 'badge-active',
    Scheduled: 'badge-scheduled',
    Expired: 'badge-expired',
    Draft: 'badge-draft',
};
const CATEGORY_PILL_CLASS: Record<string, string> = {
    restaurant: 'badge-restaurant',
    club: 'badge-club',
    general: 'badge-general',
};
const AD_TYPE_PILL_CLASS: Record<string, string> = {
    image: 'badge-format-image',
    video: 'badge-format-video',
    both: 'badge-format-both',
};

const emit = defineEmits(['view-changed']);

const filterCategory = ref('All Categories');
const filterStatus = ref('All Statuses');

// Local, mutable copy of the ad list so create/update/delete can reflect immediately
// without waiting for a full Inertia page reload.
const localAds = ref<any[]>([...props.ads]);
watch(
    () => props.ads,
    (next) => {
        localAds.value = [...next];
    },
);

const filteredList = computed(() => {
    return localAds.value.filter((a) => {
        const catLabel = getCategory(a.category).label;
        const catMatch = filterCategory.value === 'All Categories' || catLabel === filterCategory.value;

        const statusLabel = getStatus(a).label;
        const statusMatch = filterStatus.value === 'All Statuses' || statusLabel === filterStatus.value;
        return catMatch && statusMatch;
    });
});

const onAdPublished = (event: Event) => {
    const detail = (event as CustomEvent).detail;
    if (!detail) return;
    localAds.value = [detail, ...localAds.value];
    // Reset filters so the ad just created can't be hidden by a stale filter selection.
    filterCategory.value = 'All Categories';
    filterStatus.value = 'All Statuses';
    closeForm();
    toast.success('Ad published successfully!');
};

const onAdUpdated = (event: Event) => {
    const detail = (event as CustomEvent).detail;
    if (!detail) return;
    const idx = localAds.value.findIndex((a: any) => a.id === detail.id);
    if (idx !== -1) {
        const existing = localAds.value[idx];
        localAds.value[idx] = {
            ...existing,
            ...detail,
            impressions_count: detail.impressions_count ?? existing.impressions_count,
            clicks_count: detail.clicks_count ?? existing.clicks_count,
            swipe_lefts_count: detail.swipe_lefts_count ?? existing.swipe_lefts_count,
        };
    }
    // Reset filters so the ad just edited can't be hidden by a stale filter selection.
    filterCategory.value = 'All Categories';
    filterStatus.value = 'All Statuses';
    closeForm();
    toast.success('Ad updated successfully!');
};

onMounted(() => {
    window.addEventListener('adPublished', onAdPublished as EventListener);
    window.addEventListener('adUpdated', onAdUpdated as EventListener);
});

onUnmounted(() => {
    window.removeEventListener('adPublished', onAdPublished as EventListener);
    window.removeEventListener('adUpdated', onAdUpdated as EventListener);
});

const isDeleting = ref<number | null>(null);

const deleteAd = async (ad: any) => {
    if (!window.confirm(`Delete "${ad.name}"? This permanently removes the ad and its tracked impressions/clicks.`)) {
        return;
    }
    isDeleting.value = ad.id;
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch(route('admin.ads.destroy', ad.id), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            toast.error(errorData?.message || 'Failed to delete ad.');
            return;
        }
        localAds.value = localAds.value.filter((a: any) => a.id !== ad.id);
        toast.success(`"${ad.name}" was deleted.`);
    } catch (error) {
        console.error('Error deleting ad:', error);
        toast.error('Failed to delete ad.');
    } finally {
        isDeleting.value = null;
    }
};

const isFormOpen = ref(false);
const adFormCard = ref<HTMLElement | null>(null);
const activeTab = ref('tab-details');

const getToday = () => {
    const today = new Date();
    return today.toISOString().split('T')[0];
};

const addDays = (dateString: string, days: number) => {
    const date = new Date(dateString);
    date.setDate(date.getDate() + days);
    return date.toISOString().split('T')[0];
};

const getDefaultAdState = () => ({
    id: null,
    category: 'restaurant',
    advertiserName: '',
    websiteUrl: '',
    contactEmail: '',
    contactPhone: '',
    headline: '',
    shortDescription: '',
    country: '',
    state: '',
    city: '',
    venue: '',
    adType: 'image',
    imagePreview: '',
    videoPreview: '',
    thumbnailPreview: '',
    maxDuration: '15',
    autoplaySound: 'muted',
    ctaOverlayTiming: 'start',
    loopVideo: 'yes',
    ctaText: 'Visit Website',
    brandColor: '#6FBD1E',
    startDate: getToday(),
    endDate: addDays(getToday(), 14),
    durationDays: 14,
    pricePackage: 'growth',
    customCostOverride: '',
    paymentRef: '',
    isPaid: false,
    status: 'Active (publish immediately)',
    targetingNotes: '',
});

const adFormData = reactive(getDefaultAdState());
const formKey = ref(0);
const validationErrors = ref<Record<string, string[]>>({});

const isEditMode = computed(() => Boolean(adFormData.id));

const openForm = (ad: any = null) => {
    if (ad) {
        Object.assign(adFormData, mapAdToForm(ad));
    } else {
        Object.assign(adFormData, getDefaultAdState());
    }
    isFormOpen.value = true;
    activeTab.value = 'tab-details';
    formKey.value++;
    setTimeout(() => {
        adFormCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
};

defineExpose({ openForm });

const mapAdToForm = (ad: Record<string, any>) => ({
    id: ad.id,
    category: ad.category || 'restaurant',
    advertiserName: ad.name || '',
    websiteUrl: ad.www || '',
    contactEmail: ad.email || '',
    contactPhone: ad.phone || '',
    headline: ad.headline || '',
    shortDescription: ad.description || '',
    country: ad.country || '',
    state: ad.state || '',
    city: ad.city || '',
    venue: ad.location || '',
    adType: ad.ad_type || 'image',
    imagePreview: ad.image || '',
    videoPreview: ad.video || '',
    thumbnailPreview: ad.thumbnail || '',
    maxDuration: ad.max_duration || '15',
    autoplaySound: ad.autoplay_sound || 'muted',
    ctaOverlayTiming: ad.cta_overlay_timing || 'start',
    loopVideo: ad.loop_video || 'yes',
    ctaText: ad.cta_text || 'Visit Website',
    brandColor: ad.brand_color || '#6FBD1E',
    startDate: ad.start_date || getToday(),
    endDate: ad.end_date || addDays(getToday(), 14),
    durationDays: ad.duration_days ?? 14,
    pricePackage: ad.price_package || 'growth',
    customCostOverride: ad.custom_cost_override || '',
    paymentRef: ad.payment_ref || '',
    isPaid: Boolean(ad.is_paid),
    status: ad.publication_status || 'Active (publish immediately)',
    targetingNotes: ad.targeting_notes || '',
});

const closeForm = () => {
    isFormOpen.value = false;
    validationErrors.value = {};
};

const updateFormData = (payload: Partial<typeof adFormData>) => {
    Object.assign(adFormData, payload);
};

const fieldToTabMap: Record<string, string> = {
    category: 'tab-details',
    advertiser_name: 'tab-details',
    website_url: 'tab-details',
    contact_email: 'tab-details',
    contact_phone: 'tab-details',
    headline: 'tab-details',
    short_description: 'tab-details',
    country: 'tab-details',
    state: 'tab-details',
    city: 'tab-details',
    venue: 'tab-details',
    ad_type: 'tab-creative',
    image: 'tab-creative',
    video: 'tab-creative',
    thumbnail: 'tab-creative',
    max_duration: 'tab-creative',
    autoplay_sound: 'tab-creative',
    cta_overlay_timing: 'tab-creative',
    loop_video: 'tab-creative',
    cta_text: 'tab-creative',
    brand_color: 'tab-creative',
    start_date: 'tab-schedule',
    end_date: 'tab-schedule',
    duration_days: 'tab-schedule',
    price_package: 'tab-schedule',
    custom_cost_override: 'tab-schedule',
    payment_ref: 'tab-schedule',
    is_paid: 'tab-schedule',
    publication_status: 'tab-preview',
    targeting_notes: 'tab-preview',
};

const handleValidationErrors = (errors: Record<string, string[]> | null | undefined) => {
    errors = errors || {};
    validationErrors.value = errors;
    const firstErrorField = Object.keys(errors)[0];
    if (firstErrorField && fieldToTabMap[firstErrorField]) {
        activeTab.value = fieldToTabMap[firstErrorField];
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon green">👁️</div>
                <div>
                    <div class="stat-num">{{ num(adStats?.totalImpressions || 0) }}</div>
                    <div class="stat-label">Total Impressions</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">👆</div>
                <div>
                    <div class="stat-num">{{ num(adStats?.totalClicks || 0) }}</div>
                    <div class="stat-label">Swipe Rights (Clicks)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">❤️</div>
                <div>
                    <div class="stat-num">{{ num(adStats?.totalLikes || 0) }}</div>
                    <div class="stat-label">Total Likes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">📢</div>
                <div>
                    <div class="stat-num">{{ num(adStats?.activeAds || 0) }}</div>
                    <div class="stat-label">Active Ads</div>
                </div>
            </div>
        </div>

        <!-- HOW IT WORKS -->
        <div class="card mb16">
            <div class="card-header">
                <span class="card-title">How Ads Appear in LinkU</span>
            </div>
            <div class="card-body">
                <div class="preview-wrap">
                    <div class="preview-phone">
                        <div class="preview-inner">
                            <div class="preview-img">🍕</div>
                            <div class="preview-info">
                                <span class="preview-badge">SPONSORED · RESTAURANT</span>
                                <div class="preview-name">Tony's Pizzeria</div>
                                <div class="preview-sub">Downtown Miami · Visit Website →</div>
                            </div>
                            <div class="swipe-hints">
                                <button class="swipe-btn swipe-left">✕</button>
                                <button class="swipe-btn swipe-right">♥</button>
                            </div>
                        </div>
                    </div>
                    <div class="preview-notes">
                        <h4>Swipe Mechanics</h4>
                        <div class="swipe-rule">
                            <span class="arrow" style="color: var(--brand)">→</span>
                            <div>
                                <strong>Swipe Right</strong> — Opens the advertiser's website in-app. Counts as a <em>click</em> and a <em>like</em>.
                            </div>
                        </div>
                        <div class="swipe-rule">
                            <span class="arrow" style="color: var(--red)">←</span>
                            <div><strong>Swipe Left</strong> — Dismisses the ad, user continues browsing. Counts as an <em>impression</em>.</div>
                        </div>
                        <div class="swipe-rule">
                            <span class="arrow" style="color: var(--blue)">👁</span>
                            <div><strong>View / Eyeball</strong> — Recorded the moment the ad tile is shown on screen, before any swipe.</div>
                        </div>
                        <p class="text-muted mt16">
                            Ads are injected every <strong>5 tiles</strong> as users swipe through LinkUp Vibes. Category badges (Restaurant / Club /
                            Ad) and a <strong>🎬 video indicator</strong> appear automatically based on the format you select.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="card" id="ad-list-card">
            <div class="card-header">
                <span class="card-title">All Advertisements</span>
                <div style="display: flex; gap: 8px">
                    <select
                        v-model="filterCategory"
                        style="padding: 6px 10px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13px"
                    >
                        <option>All Categories</option>
                        <option>Restaurant</option>
                        <option>Club / Fête</option>
                        <option>General</option>
                    </select>
                    <select v-model="filterStatus" style="padding: 6px 10px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13px">
                        <option>All Statuses</option>
                        <option>Active</option>
                        <option>Scheduled</option>
                        <option>Expired</option>
                    </select>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Ad / Advertiser</th>
                            <th>Category</th>
                            <th>Format</th>
                            <th>Duration</th>
                            <th>Impressions</th>
                            <th>Swipe Rights</th>
                            <th>Likes</th>
                            <th>CTR</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredList.length">
                            <td colspan="10" style="text-align: center; padding: 24px">No ads found.</td>
                        </tr>
                        <tr v-for="ad in filteredList" :key="ad.id">
                            <td>
                                <strong>{{ ad.icon || getCategory(ad.category).icon }} {{ ad.name }}</strong>
                                <br /><span class="text-muted">{{ ad.start_date }} → {{ ad.end_date }}</span>
                            </td>
                            <td>
                                <span class="badge" :class="CATEGORY_PILL_CLASS[ad.category] || 'badge-general'">
                                    {{ getCategory(ad.category).icon }} {{ getCategory(ad.category).label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" :class="AD_TYPE_PILL_CLASS[ad.ad_type] || 'badge-format-image'">
                                    {{ getAdType(ad.ad_type).icon }} {{ getAdType(ad.ad_type).label }}
                                </span>
                            </td>
                            <td>{{ ad.start_date ? getDurationDays(ad.start_date, ad.end_date) : (ad.duration_days ?? 0) }} days</td>
                            <td>👁️ {{ num(ad.impressions_count) }}</td>
                            <td>→ {{ num(ad.clicks_count) }}</td>
                            <td>❤️ {{ num(ad.clicks_count) }}</td>
                            <td>{{ ad.impressions_count ? ((ad.clicks_count / ad.impressions_count) * 100).toFixed(1) : '0.0' }}%</td>
                            <td>
                                <span class="badge" :class="STATUS_PILL_CLASS[getStatus(ad).label] || 'badge-draft'">
                                    {{ getStatus(ad).label }}
                                </span>
                            </td>
                            <td>
                                <div class="actions-col">
                                    <button @click="emit('view-changed', 'adReportsCommand')" class="btn btn-ghost btn-sm">📄 Report</button>
                                    <button @click="openForm(ad)" class="btn btn-outline btn-sm">✏️</button>
                                    <button
                                        @click="deleteAd(ad)"
                                        :disabled="isDeleting === ad.id"
                                        class="btn btn-outline btn-sm"
                                        style="color: #b91c1c; border-color: #fee2e2"
                                    >
                                        {{ isDeleting === ad.id ? '…' : '🗑️' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FORM SECTION -->
        <div v-if="isFormOpen" ref="adFormCard" class="card mt24 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="card-header">
                <span class="card-title">{{ isEditMode ? 'Edit' : 'Create' }} New Ad</span>
                <button @click="closeForm" class="btn btn-outline btn-sm">✕ Cancel</button>
            </div>

            <!-- Tab Bar -->
            <div class="tabs">
                <button @click="activeTab = 'tab-details'" class="tab" :class="{ active: activeTab === 'tab-details' }">1 · Details</button>
                <button @click="activeTab = 'tab-creative'" class="tab" :class="{ active: activeTab === 'tab-creative' }">2 · Creative</button>
                <button @click="activeTab = 'tab-schedule'" class="tab" :class="{ active: activeTab === 'tab-schedule' }">
                    3 · Schedule & Pricing
                </button>
                <button @click="activeTab = 'tab-preview'" class="tab" :class="{ active: activeTab === 'tab-preview' }">4 · Preview</button>
            </div>

            <div class="card-body">
                <!-- TAB 1: DETAILS -->
                <div v-if="activeTab === 'tab-details'" :key="`details-${formKey}`">
                    <Details :details="adFormData" :errors="validationErrors" @update-details="updateFormData" />
                    <div style="display: flex; justify-content: flex-end; margin-top: 20px">
                        <button @click="activeTab = 'tab-creative'" class="btn btn-primary">Next: Creative →</button>
                    </div>
                </div>

                <!-- TAB 2: CREATIVE -->
                <div v-else-if="activeTab === 'tab-creative'" :key="`creative-${formKey}`">
                    <Creative :creative="adFormData" :errors="validationErrors" @update-creative="updateFormData" />
                    <div style="display: flex; justify-content: space-between; margin-top: 24px">
                        <button @click="activeTab = 'tab-details'" class="btn btn-outline">← Back</button>
                        <button @click="activeTab = 'tab-schedule'" class="btn btn-primary">Next: Schedule →</button>
                    </div>
                </div>

                <!-- TAB 3: SCHEDULE & PRICING -->
                <div v-else-if="activeTab === 'tab-schedule'" :key="`schedule-${formKey}`">
                    <ScheduleAndPricing :schedule="adFormData" :errors="validationErrors" @update-schedule="updateFormData" />
                    <div style="display: flex; justify-content: space-between; margin-top: 24px">
                        <button @click="activeTab = 'tab-creative'" class="btn btn-outline">← Back</button>
                        <button @click="activeTab = 'tab-preview'" class="btn btn-primary">Next: Preview →</button>
                    </div>
                </div>

                <!-- TAB 4: PREVIEW -->
                <div v-else-if="activeTab === 'tab-preview'" :key="`preview-${formKey}`">
                    <Preview
                        :activeTab="activeTab"
                        :ad-data="adFormData"
                        :ad-id="adFormData.id"
                        :is-edit-mode="isEditMode"
                        @updatedTab="activeTab = $event"
                        @validationErrors="handleValidationErrors"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Scoped overrides to ensure grid and layout classes work as expected */
:deep(.cat-grid) {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

:deep(.form-grid) {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

:deep(.field) {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

:deep(.field.full) {
    grid-column: 1 / -1;
}

:deep(.adtype-toggle) {
    display: flex;
    gap: 0;
    border-radius: 10px;
    overflow: hidden;
    border: 1.5px solid var(--border);
    width: fit-content;
    margin-bottom: 24px;
}

:deep(.pricing-row) {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

:deep(.duration-grid) {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* Ensure tabs from imported components look right */
.tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid var(--border);
    padding: 0 24px;
}

.tab {
    padding: 14px 18px;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    border-bottom: 2.5px solid transparent;
    transition:
        color 0.15s,
        border-color 0.15s;
    white-space: nowrap;
    background: transparent;
    border-top: none;
    border-left: none;
    border-right: none;
}

.tab.active {
    color: var(--brand);
    border-bottom-color: var(--brand);
}

.tab:hover:not(.active) {
    color: var(--dark);
}
</style>
