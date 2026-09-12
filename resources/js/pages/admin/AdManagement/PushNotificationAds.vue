<script setup lang="ts">
import { BellRing, Plus, TrendingUp } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type PushAd = {
    campaign: string;
    advertiser: string;
    spend: string;
    sent: string;
    openRate: string;
    status: 'Active' | 'Scheduled';
};

const stats = [
    { label: 'Sent', value: '142,000', trend: '+9% MoM' },
    { label: 'Opens', value: '38,900', trend: '+7% MoM' },
    { label: 'Open Rate', value: '27.4%', trend: '+1.1 pts' },
    { label: 'Ad Revenue', value: '$2,980', trend: '+8% MoM' },
];

const pushAds: PushAd[] = [
    { campaign: 'Weekend Events Alert', advertiser: 'LinkUp Events', spend: '$700', sent: '142,000', openRate: '27.4%', status: 'Active' },
    { campaign: 'Flash Marketplace Sale', advertiser: 'Marketplace', spend: '$360', sent: '96,000', openRate: '22.1%', status: 'Active' },
    { campaign: 'Wallet Top-Up Reminder', advertiser: 'LinkUp Wallet', spend: '$220', sent: '64,000', openRate: '18.7%', status: 'Scheduled' },
];

function statusClass(status: PushAd['status']) {
    return status === 'Active'
        ? 'push-status push-status-active'
        : 'push-status push-status-scheduled';
}

function newPushAd() {
    toast.info('Push Notification Ads: new ad flow coming next.');
}
</script>

<template>
    <div class="push-ads-page">
        <Toaster rich-colors position="top-right" />

        <div class="push-header">
            <div>
                <div class="push-title">
                    <BellRing class="push-title-icon" />
                    Push Notification Ads
                </div>
                <p class="push-subtitle">Sponsored push campaigns delivered to opted-in users.</p>
            </div>
            <button type="button" class="push-primary-btn" @click="newPushAd">
                <Plus class="push-btn-icon" />
                New Push Ad
            </button>
        </div>

        <div class="push-stats-grid">
            <div v-for="stat in stats" :key="stat.label" class="push-stat-card">
                <div class="push-stat-label">{{ stat.label }}</div>
                <div class="push-stat-value">{{ stat.value }}</div>
                <div class="push-stat-trend">
                    <TrendingUp class="push-trend-icon" />
                    {{ stat.trend }}
                </div>
            </div>
        </div>

        <div class="push-table-card">
            <table class="push-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Advertiser</th>
                        <th>Spend</th>
                        <th>Sent</th>
                        <th>Open Rate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ad in pushAds" :key="ad.campaign">
                        <td class="push-campaign">{{ ad.campaign }}</td>
                        <td>{{ ad.advertiser }}</td>
                        <td>{{ ad.spend }}</td>
                        <td>{{ ad.sent }}</td>
                        <td>{{ ad.openRate }}</td>
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
.push-ads-page {
    padding: 24px 28px;
}

.push-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.push-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 900;
    color: #051020;
}

.push-title-icon {
    width: 24px;
    height: 24px;
    color: #7c3aed;
}

.push-subtitle {
    color: #6b7280;
    margin-top: 4px;
}

.push-primary-btn {
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

.push-primary-btn:hover {
    background: #0891b2;
    transform: translateY(-1px);
}

.push-btn-icon {
    width: 16px;
    height: 16px;
}

.push-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.push-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
}

.push-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 800;
}

.push-stat-value {
    font-size: 26px;
    font-weight: 900;
    margin-top: 6px;
    color: #051020;
}

.push-stat-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #16a34a;
    font-size: 12px;
    font-weight: 800;
    margin-top: 4px;
}

.push-trend-icon {
    width: 13px;
    height: 13px;
}

.push-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

.push-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.push-table thead tr {
    background: #f6faf3;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.push-table th {
    padding: 12px 16px;
    font-weight: 800;
}

.push-table td {
    padding: 12px 16px;
    color: #374151;
    border-top: 1px solid #eef0f2;
}

.push-campaign {
    color: #051020 !important;
    font-weight: 800;
}

.push-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
}

.push-status-active {
    color: #16a34a;
    background: #dcfce7;
}

.push-status-scheduled {
    color: #1d4ed8;
    background: #dbeafe;
}

@media (max-width: 720px) {
    .push-ads-page {
        padding: 18px;
    }

    .push-table-card {
        overflow-x: auto;
    }

    .push-table {
        min-width: 720px;
    }
}
</style>
