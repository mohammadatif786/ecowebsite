<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
    ChevronRight,
    FileText,
    Heart,
    Image,
    Link as LinkIcon,
    MessageCircle,
    Music,
    Plus,
    Repeat2,
    Send,
    Upload,
    Video,
    X,
} from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type VibesAd = {
    id: string;
    advertiser: string;
    handle: string;
    logo?: string;
    caption: string;
    img: string;
    mediaType?: 'image' | 'video' | 'audio' | 'pdf';
    cta: string;
    url: string;
    target?: string;
    status: 'Active' | 'Paused';
    likes: number;
    comments: number;
    shares: number;
    followers: number;
    clicks: number;
    liked?: boolean;
};

type VibesForm = {
    id: string;
    advertiser: string;
    handle: string;
    caption: string;
    cta: string;
    url: string;
    status: 'Active' | 'Paused';
    mediaUrl: string;
    mediaType: VibesAd['mediaType'];
};

const props = defineProps<{
    vibesAds?: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const emit = defineEmits(['view-changed']);

const STORAGE_KEY = 'linkupVibesAds';
const ctaOptions = ['Learn More', 'Shop Now', 'Book Now', 'Get the Plan', 'Sign Up', 'Visit Website'];

const fallbackAds: VibesAd[] = [
    {
        id: 'AD-9001',
        advertiser: 'Scotiabank',
        handle: '@scotiabank',
        logo: 'SB',
        caption: 'Bank smarter across the Caribbean with the Scotiabank + LinkUp wallet. #linkup',
        img: 'https://picsum.photos/seed/adbank/600/600',
        mediaType: 'image',
        cta: 'Learn More',
        url: 'https://www.scotiabank.com',
        target: 'Caribbean',
        status: 'Active',
        likes: 4820,
        comments: 120,
        shares: 340,
        followers: 18200,
        clicks: 0,
        liked: false,
    },
    {
        id: 'AD-9002',
        advertiser: 'Caribbean Travel Co.',
        handle: '@caribtravel',
        logo: 'CT',
        caption: 'Escape to the islands. Exclusive LinkUp member rates on flights + resorts.',
        img: 'https://picsum.photos/seed/adtravel/600/600',
        mediaType: 'image',
        cta: 'Book Now',
        url: 'https://example.com/travel',
        target: 'All',
        status: 'Active',
        likes: 9120,
        comments: 430,
        shares: 1200,
        followers: 52000,
        clicks: 0,
        liked: false,
    },
    {
        id: 'AD-9003',
        advertiser: 'Digicel',
        handle: '@digicel',
        logo: 'DG',
        caption: 'Fastest Caribbean data plans. Stream LinkUp Vibes anywhere.',
        img: 'https://picsum.photos/seed/addigicel/600/600',
        mediaType: 'image',
        cta: 'Get the Plan',
        url: 'https://www.digicelgroup.com',
        target: 'All',
        status: 'Paused',
        likes: 2100,
        comments: 60,
        shares: 140,
        followers: 9800,
        clicks: 0,
        liked: false,
    },
];

const isModalOpen = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const fileName = ref('No file chosen - or paste a URL below.');
const form = ref<VibesForm>(blankForm());
const localVibesAds = ref<VibesAd[]>(loadInitialAds());

watch(() => props.vibesAds, (rows) => {
    if (!hasStoredAds()) localVibesAds.value = normalizeAds(rows);
}, { deep: true });

const stats = computed(() => {
    const active = localVibesAds.value.filter((ad) => ad.status === 'Active').length;
    const impressions = localVibesAds.value.reduce((sum, ad) => sum + adImpressions(ad), 0);
    const clicks = localVibesAds.value.reduce((sum, ad) => sum + safeNumber(ad.clicks), 0);
    const followers = localVibesAds.value.reduce((sum, ad) => sum + safeNumber(ad.followers), 0);

    return { active, impressions, clicks, followers };
});

const analytics = computed(() => {
    const totalEngagement = localVibesAds.value.reduce((sum, ad) => sum + safeNumber(ad.likes) + safeNumber(ad.shares) + safeNumber(ad.comments), 0);
    const avgCtr = localVibesAds.value.length
        ? localVibesAds.value.reduce((sum, ad) => sum + ctrFor(ad), 0) / localVibesAds.value.length
        : 0;
    const top = [...localVibesAds.value].sort((a, b) => (safeNumber(b.likes) + safeNumber(b.shares)) - (safeNumber(a.likes) + safeNumber(a.shares)))[0] || null;

    return { totalEngagement, avgCtr, top };
});

const previewType = computed(() => form.value.mediaType || mediaTypeFromUrl(form.value.mediaUrl));

function blankForm(): VibesForm {
    return {
        id: '',
        advertiser: '',
        handle: '',
        caption: '',
        cta: 'Learn More',
        url: '',
        status: 'Active',
        mediaUrl: '',
        mediaType: 'image',
    };
}

function loadInitialAds() {
    if (typeof window !== 'undefined') {
        try {
            const stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || 'null');
            if (Array.isArray(stored) && stored.length) return normalizeAds(stored);
        } catch {
            // Keep the page usable if localStorage has malformed demo data.
        }
    }

    return normalizeAds(props.vibesAds);
}

function hasStoredAds() {
    if (typeof window === 'undefined') return false;
    return !!window.localStorage.getItem(STORAGE_KEY);
}

function persistAds() {
    if (typeof window === 'undefined') return;
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(localVibesAds.value));
}

function normalizeAds(rows?: any[]) {
    const list = Array.isArray(rows) && rows.length ? rows : fallbackAds;

    return list.map((ad, index) => ({
        id: String(ad.id || `AD-${9001 + index}`),
        advertiser: String(ad.advertiser || ad.name || 'Advertiser'),
        handle: String(ad.handle || handleFor(ad.advertiser || ad.name || 'advertiser')),
        logo: String(ad.logo || initials(ad.advertiser || ad.name || 'AD')),
        caption: String(ad.caption || ad.headline || ad.description || ad.advertiser || 'Sponsored Vibes placement'),
        img: String(ad.img || ad.image_url || ad.image || `https://picsum.photos/seed/${ad.id || index}/600/600`),
        mediaType: ad.mediaType || mediaTypeFromUrl(String(ad.img || ad.image_url || ad.image || '')),
        cta: String(ad.cta || ad.cta_text || 'Learn More'),
        url: String(ad.url || ad.www || ad.website || 'https://linkup.app'),
        target: String(ad.target || 'All'),
        status: String(ad.status || 'Active').toLowerCase().includes('pause') ? 'Paused' : 'Active',
        likes: safeNumber(ad.likes),
        comments: safeNumber(ad.comments),
        shares: safeNumber(ad.shares),
        followers: safeNumber(ad.followers),
        clicks: safeNumber(ad.clicks),
        liked: Boolean(ad.liked),
    })) as VibesAd[];
}

function safeNumber(value: any) {
    return Number(value || 0) || 0;
}

function adImpressions(ad: VibesAd) {
    return (safeNumber(ad.likes) + safeNumber(ad.shares)) * 7 + 1000;
}

function ctrFor(ad: VibesAd) {
    const impressions = adImpressions(ad);
    return impressions ? (safeNumber(ad.clicks) / impressions) * 100 : 0;
}

function handleFor(value: string) {
    return `@${String(value).toLowerCase().replace(/[^a-z0-9]/g, '').slice(0, 16) || 'brand'}`;
}

function initials(value: string) {
    const words = String(value).replace(/[^a-zA-Z0-9 ]/g, '').split(' ').filter(Boolean);
    return (words[0]?.[0] || 'A') + (words[1]?.[0] || 'D');
}

function mediaTypeFromUrl(value: string): VibesAd['mediaType'] {
    const url = String(value || '').toLowerCase();
    if (url.startsWith('data:video') || /\.(mp4|mov|webm)(\?|$)/i.test(url)) return 'video';
    if (url.startsWith('data:audio') || /\.(mp3|wav|m4a)(\?|$)/i.test(url)) return 'audio';
    if (url.startsWith('data:application/pdf') || /\.pdf(\?|$)/i.test(url)) return 'pdf';
    return 'image';
}

function openCreateModal(ad?: VibesAd) {
    fileName.value = 'No file chosen - or paste a URL below.';

    if (ad) {
        form.value = {
            id: ad.id,
            advertiser: ad.advertiser,
            handle: ad.handle,
            caption: ad.caption,
            cta: ad.cta,
            url: ad.url,
            status: ad.status,
            mediaUrl: ad.img,
            mediaType: ad.mediaType || 'image',
        };
    } else {
        form.value = blankForm();
    }

    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
}

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    fileName.value = `${file.name} (${Math.round(file.size / 1024)} KB)`;
    const reader = new FileReader();
    reader.onload = () => {
        form.value.mediaUrl = String(reader.result || '');
        form.value.mediaType = mediaTypeFromUrl(form.value.mediaUrl || file.type);
    };
    reader.readAsDataURL(file);
}

function syncUrlPreview() {
    form.value.mediaType = mediaTypeFromUrl(form.value.mediaUrl);
}

function saveAd() {
    const advertiser = form.value.advertiser.trim();
    if (!advertiser) {
        toast.error('Advertiser required.');
        return;
    }

    const fields = {
        advertiser,
        handle: form.value.handle.trim() || handleFor(advertiser),
        caption: form.value.caption.trim() || advertiser,
        cta: form.value.cta,
        url: form.value.url.trim() || 'https://linkup.app',
        status: form.value.status,
        img: form.value.mediaUrl || '',
        mediaType: previewType.value || 'image',
    };

    if (form.value.id) {
        localVibesAds.value = localVibesAds.value.map((ad) => ad.id === form.value.id
            ? { ...ad, ...fields, img: fields.img || ad.img }
            : ad);
        toast.success('Vibes ad updated.');
    } else {
        const id = `AD-${Math.floor(9100 + Math.random() * 800)}`;
        localVibesAds.value = [{
            id,
            logo: initials(advertiser),
            target: 'All',
            likes: 0,
            comments: 0,
            shares: 0,
            followers: 0,
            clicks: 0,
            liked: false,
            ...fields,
            img: fields.img || `https://picsum.photos/seed/${id}/600/600`,
        }, ...localVibesAds.value];
        toast.success('Published to Vibes.');
    }

    persistAds();
    closeModal();
}

function toggleStatus(ad: VibesAd) {
    ad.status = ad.status === 'Active' ? 'Paused' : 'Active';
    persistAds();
    toast.success(`${ad.advertiser} ${ad.status === 'Active' ? 'activated' : 'paused'}.`);
}

function deleteAd(id: string) {
    const ad = localVibesAds.value.find((item) => item.id === id);
    if (!ad || !window.confirm(`Delete ad ${ad.advertiser}?`)) return;
    localVibesAds.value = localVibesAds.value.filter((item) => item.id !== id);
    persistAds();
    toast.success('Vibes ad deleted.');
}

function visitAd(ad: VibesAd) {
    ad.clicks += 1;
    persistAds();
    if (typeof window !== 'undefined') window.open(ad.url, '_blank');
}
</script>

<template>
    <div class="vibes-page">
        <Toaster rich-colors position="top-right" />

        <div class="vibes-header">
            <div>
                <h3 class="vibes-title">
                    Vibes Ads
                    <span>Ad Management -> Vibes Feed placement</span>
                </h3>
                <p class="vibes-subtitle">
                    Drop sponsored posts straight into the LinkUp Vibes feed - followable, likeable, repostable, with a website link.
                    Manage here or from the <b>Ad Manager</b>; all stats roll up to centralized ad reporting.
                </p>
            </div>
            <div class="vibes-actions">
                <button type="button" class="vibes-secondary-btn" @click="emit('view-changed', 'adDashboardCommand')">Open Ad Manager</button>
                <button type="button" class="vibes-primary-btn" @click="openCreateModal()">
                    <Plus class="vibes-btn-icon" />
                    New Vibes Ad
                </button>
            </div>
        </div>

        <div class="vibes-kpi-grid">
            <div class="vibes-card vibes-kpi-card">
                <p>Active Ads</p>
                <h3>{{ stats.active }}</h3>
                <span>of {{ localVibesAds.length }} total</span>
            </div>
            <div class="vibes-card vibes-kpi-card">
                <p>Est. Impressions</p>
                <h3 class="sky">{{ props.num(stats.impressions) }}</h3>
            </div>
            <div class="vibes-card vibes-kpi-card">
                <p>Link Clicks</p>
                <h3 class="indigo">{{ props.num(stats.clicks) }}</h3>
            </div>
            <div class="vibes-card vibes-kpi-card">
                <p>Ad Followers</p>
                <h3 class="pink">{{ props.num(stats.followers) }}</h3>
            </div>
        </div>

        <div class="vibes-card vibes-section-card">
            <h3 class="vibes-section-title">Ad Placements</h3>
            <div class="vibes-table-wrap">
                <table class="vibes-table">
                    <thead>
                        <tr>
                            <th>Advertiser</th>
                            <th>Creative</th>
                            <th>CTA -> URL</th>
                            <th>Likes</th>
                            <th>Followers</th>
                            <th>Reposts</th>
                            <th>Clicks</th>
                            <th>CTR</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ad in localVibesAds" :key="ad.id">
                            <td class="vibes-advertiser-cell">
                                <div class="vibes-logo">{{ ad.logo || initials(ad.advertiser) }}</div>
                                <div>
                                    <strong>{{ ad.advertiser }}</strong>
                                    <span>{{ ad.handle }}</span>
                                </div>
                            </td>
                            <td class="vibes-creative-cell">{{ ad.caption }}</td>
                            <td class="vibes-url-cell">
                                <strong>{{ ad.cta }}</strong>
                                <button type="button" @click="visitAd(ad)">{{ ad.url }}</button>
                            </td>
                            <td><strong>{{ props.num(ad.likes) }}</strong></td>
                            <td><strong class="pink">{{ props.num(ad.followers) }}</strong></td>
                            <td>{{ props.num(ad.shares) }}</td>
                            <td><strong>{{ ad.clicks || 0 }}</strong></td>
                            <td><strong>{{ ctrFor(ad).toFixed(2) }}%</strong></td>
                            <td>
                                <span class="vibes-status" :class="ad.status === 'Active' ? 'active' : 'paused'">{{ ad.status }}</span>
                            </td>
                            <td>
                                <div class="vibes-row-actions">
                                    <button type="button" class="edit" @click="openCreateModal(ad)">Edit</button>
                                    <button type="button" class="toggle" @click="toggleStatus(ad)">{{ ad.status === 'Active' ? 'Pause' : 'Activate' }}</button>
                                    <button type="button" class="delete" @click="deleteAd(ad.id)">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!localVibesAds.length">
                            <td colspan="10" class="vibes-empty">No ads yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="vibes-card vibes-section-card">
            <h3 class="vibes-section-title tight">Ad Analytics</h3>
            <p class="vibes-section-copy">Performance for the Vibes Feed placement - part of centralized ad reporting.</p>
            <div class="vibes-analytics-grid">
                <div class="vibes-analytics-card indigo">
                    <p>Total Engagement</p>
                    <h4>{{ props.num(analytics.totalEngagement) }}</h4>
                    <span>likes + reposts + comments</span>
                </div>
                <div class="vibes-analytics-card sky">
                    <p>Avg CTR</p>
                    <h4>{{ analytics.avgCtr.toFixed(2) }}%</h4>
                    <span>link clicks / impressions</span>
                </div>
                <div class="vibes-analytics-card pink">
                    <p>Top Performer</p>
                    <h4>{{ analytics.top?.advertiser || '-' }}</h4>
                    <span v-if="analytics.top">{{ props.num(analytics.top.likes) }} likes - {{ props.num(analytics.top.followers) }} followers</span>
                    <span v-else>No active data</span>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="isModalOpen" class="vibes-modal-backdrop" @click.self="closeModal">
                <div class="vibes-modal">
                    <div class="vibes-modal-head">
                        <h3>{{ form.id ? `Edit Ad - ${form.advertiser || 'Vibes'}` : 'New Vibes Ad' }}</h3>
                        <button type="button" @click="closeModal"><X /></button>
                    </div>
                    <div class="vibes-modal-body">
                        <input v-model="form.id" type="hidden" />
                        <div class="vibes-form-grid">
                            <label>
                                <span>Advertiser</span>
                                <input v-model="form.advertiser" />
                            </label>
                            <label>
                                <span>Handle</span>
                                <input v-model="form.handle" placeholder="@brand" />
                            </label>
                            <label>
                                <span>CTA label</span>
                                <select v-model="form.cta">
                                    <option v-for="option in ctaOptions" :key="option">{{ option }}</option>
                                </select>
                            </label>
                            <label>
                                <span>Status</span>
                                <select v-model="form.status">
                                    <option>Active</option>
                                    <option>Paused</option>
                                </select>
                            </label>
                        </div>

                        <label class="vibes-field">
                            <span>Website URL</span>
                            <input v-model="form.url" placeholder="https://" />
                        </label>

                        <div class="vibes-field">
                            <span>Creative - upload a file <em>(image, GIF, video, PDF - any format)</em></span>
                            <div class="vibes-upload-box">
                                <input ref="fileInput" id="vibesAdFile" type="file" accept="image/*,video/*,audio/*,.gif,.webp,.pdf,.mp4,.mov,.webm" class="hidden" @change="handleFileChange" />
                                <button type="button" class="vibes-upload-btn" @click="fileInput?.click()">
                                    <Upload />
                                    Choose file
                                </button>
                                <p>{{ fileName }}</p>
                                <div v-if="form.mediaUrl" class="vibes-preview">
                                    <video v-if="previewType === 'video'" :src="form.mediaUrl" controls />
                                    <audio v-else-if="previewType === 'audio'" :src="form.mediaUrl" controls />
                                    <div v-else-if="previewType === 'pdf'" class="vibes-pdf-preview">
                                        <FileText />
                                        <span>PDF attached</span>
                                    </div>
                                    <img v-else :src="form.mediaUrl" alt="Ad creative preview" />
                                </div>
                            </div>
                        </div>

                        <label class="vibes-field">
                            <span>...or Media URL</span>
                            <input v-model="form.mediaUrl" placeholder="https://... (leave blank for a placeholder)" @input="syncUrlPreview" />
                        </label>

                        <label class="vibes-field">
                            <span>Caption</span>
                            <textarea v-model="form.caption" rows="2" placeholder="Ad copy... #hashtags"></textarea>
                        </label>
                    </div>
                    <div class="vibes-modal-foot">
                        <button type="button" class="cancel" @click="closeModal">Cancel</button>
                        <button type="button" class="save" @click="saveAd">{{ form.id ? 'Save Changes' : 'Publish to Vibes' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.hidden {
    display: none;
}

.vibes-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.vibes-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.vibes-title {
    color: #020617;
    font-size: 30px;
    font-weight: 900;
    line-height: 1.1;
}

.vibes-title span {
    color: #94a3b8;
    font-size: 14px;
    font-weight: 800;
}

.vibes-subtitle {
    color: #64748b;
    margin-top: 6px;
    max-width: 850px;
}

.vibes-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.vibes-secondary-btn,
.vibes-primary-btn,
.vibes-upload-btn {
    align-items: center;
    border-radius: 16px;
    display: inline-flex;
    font-weight: 900;
    gap: 8px;
    padding: 12px 20px;
    transition: background 160ms ease, transform 160ms ease;
}

.vibes-secondary-btn {
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #020617;
}

.vibes-primary-btn {
    background: #020617;
    color: #fff;
}

.vibes-secondary-btn:hover,
.vibes-primary-btn:hover,
.vibes-upload-btn:hover {
    transform: translateY(-1px);
}

.vibes-btn-icon,
.vibes-upload-btn svg {
    height: 16px;
    width: 16px;
}

.vibes-kpi-grid,
.vibes-analytics-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.vibes-card {
    background: #fff;
    border: 1px solid #eaf0f7;
    border-radius: 24px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.vibes-kpi-card {
    padding: 20px;
}

.vibes-kpi-card p,
.vibes-analytics-card p {
    color: #64748b;
    font-size: 14px;
    font-weight: 800;
}

.vibes-kpi-card h3 {
    color: #020617;
    font-size: 36px;
    font-weight: 900;
    line-height: 1.1;
    margin-top: 4px;
}

.vibes-kpi-card h3.sky,
.vibes-analytics-card.sky h4 {
    color: #0284c7;
}

.vibes-kpi-card h3.indigo,
.vibes-analytics-card.indigo h4 {
    color: #4f46e5;
}

.vibes-kpi-card h3.pink,
.vibes-analytics-card.pink h4,
.pink {
    color: #db2777;
}

.vibes-kpi-card span,
.vibes-analytics-card span {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.vibes-section-card {
    padding: 24px;
}

.vibes-section-title {
    color: #020617;
    font-size: 20px;
    font-weight: 900;
    margin-bottom: 16px;
}

.vibes-section-title.tight {
    margin-bottom: 4px;
}

.vibes-section-copy {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 16px;
}

.vibes-table-wrap {
    overflow-x: auto;
}

.vibes-table {
    border-collapse: collapse;
    font-size: 14px;
    min-width: 1100px;
    text-align: left;
    width: 100%;
}

.vibes-table thead {
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
}

.vibes-table th {
    font-weight: 800;
    padding: 8px;
}

.vibes-table td {
    border-top: 1px solid #e5e7eb;
    color: #475569;
    padding: 12px 8px;
    vertical-align: middle;
}

.vibes-advertiser-cell {
    align-items: center;
    color: #020617 !important;
    display: flex;
    gap: 10px;
}

.vibes-advertiser-cell strong {
    color: #020617;
    display: block;
    font-weight: 900;
}

.vibes-advertiser-cell span {
    color: #94a3b8;
    display: block;
    font-size: 12px;
    font-weight: 700;
}

.vibes-logo {
    align-items: center;
    background: #f1f5f9;
    border-radius: 999px;
    color: #020617;
    display: flex;
    flex: 0 0 auto;
    font-size: 12px;
    font-weight: 900;
    height: 36px;
    justify-content: center;
    width: 36px;
}

.vibes-creative-cell {
    max-width: 170px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.vibes-url-cell strong {
    color: #020617;
    display: block;
    font-size: 12px;
    font-weight: 900;
}

.vibes-url-cell button {
    color: #4f46e5;
    display: block;
    font-size: 12px;
    max-width: 170px;
    overflow: hidden;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.vibes-status {
    border-radius: 999px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 900;
    padding: 2px 10px;
}

.vibes-status.active {
    background: #f0fdf4;
    color: #15803d;
}

.vibes-status.paused {
    background: #f1f5f9;
    color: #64748b;
}

.vibes-row-actions {
    display: flex;
    gap: 4px;
}

.vibes-row-actions button {
    border-radius: 8px;
    font-size: 12px;
    font-weight: 900;
    padding: 4px 8px;
}

.vibes-row-actions .edit {
    background: #e0f2fe;
    color: #0369a1;
}

.vibes-row-actions .toggle {
    background: #f1f5f9;
    color: #475569;
}

.vibes-row-actions .delete {
    background: #ffe4e6;
    color: #be123c;
}

.vibes-empty {
    color: #94a3b8 !important;
    font-weight: 800;
    padding: 28px !important;
    text-align: center;
}

.vibes-analytics-card {
    border-radius: 16px;
    padding: 16px;
}

.vibes-analytics-card.indigo {
    background: #eef2ff;
}

.vibes-analytics-card.sky {
    background: #f0f9ff;
}

.vibes-analytics-card.pink {
    background: #fdf2f8;
}

.vibes-analytics-card h4 {
    color: #020617;
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
    margin: 4px 0;
}

.vibes-modal-backdrop {
    align-items: center;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    inset: 0;
    justify-content: center;
    padding: 20px;
    position: fixed;
    z-index: 9998;
}

.vibes-modal {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.24);
    display: flex;
    flex-direction: column;
    max-height: 92vh;
    max-width: 520px;
    overflow: hidden;
    width: 100%;
}

.vibes-modal-head {
    align-items: center;
    background: linear-gradient(90deg, #4f46e5, #ec4899);
    color: #fff;
    display: flex;
    flex: 0 0 auto;
    justify-content: space-between;
    padding: 20px;
}

.vibes-modal-head h3 {
    font-size: 20px;
    font-weight: 900;
}

.vibes-modal-head button svg {
    height: 24px;
    width: 24px;
}

.vibes-modal-body {
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
    padding: 20px;
}

.vibes-form-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
}

.vibes-form-grid label,
.vibes-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.vibes-form-grid span,
.vibes-field > span {
    color: #475569;
    font-size: 14px;
    font-weight: 800;
}

.vibes-field em {
    color: #94a3b8;
    font-style: normal;
    font-weight: 500;
}

.vibes-form-grid input,
.vibes-form-grid select,
.vibes-field input,
.vibes-field textarea {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    color: #020617;
    font-weight: 700;
    padding: 10px 14px;
}

.vibes-upload-box {
    border: 2px dashed #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    text-align: center;
}

.vibes-upload-btn {
    background: #f1f5f9;
    color: #020617;
    font-size: 14px;
    margin: 0 auto;
    padding: 8px 14px;
}

.vibes-upload-box p {
    color: #64748b;
    font-size: 12px;
    margin-top: 8px;
}

.vibes-preview {
    margin-top: 12px;
}

.vibes-preview img,
.vibes-preview video {
    border-radius: 12px;
    margin: 0 auto;
    max-height: 160px;
}

.vibes-preview audio {
    width: 100%;
}

.vibes-pdf-preview {
    align-items: center;
    background: #f1f5f9;
    border-radius: 12px;
    display: inline-flex;
    font-size: 14px;
    font-weight: 800;
    gap: 8px;
    padding: 8px 12px;
}

.vibes-pdf-preview svg {
    height: 16px;
    width: 16px;
}

.vibes-modal-foot {
    border-top: 1px solid #f1f5f9;
    display: flex;
    flex: 0 0 auto;
    gap: 12px;
    justify-content: flex-end;
    padding: 16px;
}

.vibes-modal-foot button {
    border-radius: 16px;
    font-weight: 900;
    padding: 10px 20px;
}

.vibes-modal-foot .cancel {
    background: #f1f5f9;
    color: #020617;
}

.vibes-modal-foot .save {
    background: #020617;
    color: #fff;
}

@media (max-width: 720px) {
    .vibes-title {
        font-size: 24px;
    }

    .vibes-actions {
        width: 100%;
    }

    .vibes-actions button {
        justify-content: center;
        width: 100%;
    }

    .vibes-section-card {
        padding: 16px;
    }
}
</style>
