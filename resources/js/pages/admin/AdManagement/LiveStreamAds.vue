<script setup lang="ts">
import { Plus, Radio, TrendingUp } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type LiveStreamAd = {
    ad: string;
    advertiser: string;
    spend: string;
    impressions: string;
    ctr: string;
    status: 'Active' | 'Paused';
};

const stats = [
    { label: 'Impressions', value: '210,500', trend: '+21% MoM' },
    { label: 'Clicks', value: '8,900', trend: '+13% MoM' },
    { label: 'Avg CTR', value: '4.2%', trend: '+0.3 pts' },
    { label: 'Ad Revenue', value: '$6,140', trend: '+18% MoM' },
];

const liveStreamAds: LiveStreamAd[] = [
    { ad: 'Soca Fest Pre-Roll', advertiser: 'Island Vibes Events', spend: '$1,200', impressions: '88,000', ctr: '4.6%', status: 'Active' },
    { ad: 'Creator Gift Boost', advertiser: 'LinkUp Live', spend: '$640', impressions: '41,000', ctr: '3.9%', status: 'Active' },
    { ad: 'Brand Overlay - Rum Co', advertiser: 'Caribbean Rum Co', spend: '$480', impressions: '22,400', ctr: '3.2%', status: 'Paused' },
];

function statusClass(status: LiveStreamAd['status']) {
    return status === 'Active'
        ? 'live-status live-status-active'
        : 'live-status live-status-paused';
}

function newLiveAd() {
    toast.info('Live Stream Ads: new ad flow coming next.');
}
</script>

<template>
    <div class="live-ads-page">
        <Toaster rich-colors position="top-right" />

        <div class="live-header">
            <div>
                <div class="live-title">
                    <Radio class="live-title-icon" />
                    Live Stream Ads
                </div>
                <p class="live-subtitle">Pre-roll and overlay ads across LinkUp Live broadcasts.</p>
            </div>
            <button type="button" class="live-primary-btn" @click="newLiveAd">
                <Plus class="live-btn-icon" />
                New Live Ad
            </button>
        </div>

        <div class="live-stats-grid">
            <div v-for="stat in stats" :key="stat.label" class="live-stat-card">
                <div class="live-stat-label">{{ stat.label }}</div>
                <div class="live-stat-value">{{ stat.value }}</div>
                <div class="live-stat-trend">
                    <TrendingUp class="live-trend-icon" />
                    {{ stat.trend }}
                </div>
            </div>
        </div>

        <div class="live-table-card">
            <table class="live-table">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Advertiser</th>
                        <th>Spend</th>
                        <th>Impressions</th>
                        <th>CTR</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ad in liveStreamAds" :key="ad.ad">
                        <td class="live-ad-name">{{ ad.ad }}</td>
                        <td>{{ ad.advertiser }}</td>
                        <td>{{ ad.spend }}</td>
                        <td>{{ ad.impressions }}</td>
                        <td>{{ ad.ctr }}</td>
                        <td>
                            <span :class="statusClass(ad.status)">{{ ad.status }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.live-ads-page {
    padding: 24px 28px;
}

.live-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.live-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 900;
    color: #051020;
}

.live-title-icon {
    width: 24px;
    height: 24px;
    color: #dc2626;
}

.live-subtitle {
    color: #6b7280;
    margin-top: 4px;
}

.live-primary-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    border-radius: 12px;
    background: #06b6d4;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    font-weight: 900;
    padding: 11px 16px;
    box-shadow: 0 12px 24px rgba(6, 182, 212, 0.18);
    transition: background 160ms ease, transform 160ms ease;
}

.live-primary-btn:hover {
    background: #0891b2;
    transform: translateY(-1px);
}

.live-btn-icon {
    width: 16px;
    height: 16px;
}

.live-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.live-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
}

.live-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 800;
}

.live-stat-value {
    font-size: 26px;
    font-weight: 900;
    margin-top: 6px;
    color: #051020;
}

.live-stat-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #16a34a;
    font-size: 12px;
    font-weight: 800;
    margin-top: 4px;
}

.live-trend-icon {
    width: 13px;
    height: 13px;
}

.live-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

.live-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.live-table thead tr {
    background: #f6faf3;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.live-table th {
    padding: 12px 16px;
    font-weight: 800;
}

.live-table td {
    padding: 12px 16px;
    color: #374151;
    border-top: 1px solid #eef0f2;
}

.live-ad-name {
    color: #051020 !important;
    font-weight: 800;
}

.live-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
}

.live-status-active {
    color: #16a34a;
    background: #dcfce7;
}

.live-status-paused {
    color: #b45309;
    background: #fef3c7;
}

@media (max-width: 720px) {
    .live-ads-page {
        padding: 18px;
    }

    .live-table-card {
        overflow-x: auto;
    }

    .live-table {
        min-width: 720px;
    }
}
</style>
