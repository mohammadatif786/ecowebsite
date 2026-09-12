<script setup lang="ts">
import { Plus, TrendingUp, Wallet } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type WalletAd = {
    ad: string;
    advertiser: string;
    spend: string;
    impressions: string;
    ctr: string;
    status: 'Active' | 'Scheduled';
};

const stats = [
    { label: 'Impressions', value: '96,300', trend: '+11% MoM' },
    { label: 'Clicks', value: '5,040', trend: '+8% MoM' },
    { label: 'Avg CTR', value: '5.2%', trend: '+0.4 pts' },
    { label: 'Ad Revenue', value: '$3,610', trend: '+10% MoM' },
];

const walletAds: WalletAd[] = [
    { ad: 'Send Money, Get $5', advertiser: 'LinkUp Wallet', spend: '$900', impressions: '48,000', ctr: '6.0%', status: 'Active' },
    { ad: 'Bill Pay Cashback', advertiser: 'BPL Power', spend: '$520', impressions: '26,500', ctr: '4.8%', status: 'Active' },
    { ad: 'Crypto On-Ramp Launch', advertiser: 'MoonPay', spend: '$300', impressions: '12,900', ctr: '3.5%', status: 'Scheduled' },
];

function statusClass(status: WalletAd['status']) {
    return status === 'Active'
        ? 'wallet-status wallet-status-active'
        : 'wallet-status wallet-status-scheduled';
}

function newWalletAd() {
    toast.info('Wallet Ads: new ad flow coming next.');
}
</script>

<template>
    <div class="wallet-ads-page">
        <Toaster rich-colors position="top-right" />

        <div class="wallet-header">
            <div>
                <div class="wallet-title">
                    <Wallet class="wallet-title-icon" />
                    Wallet Ads
                </div>
                <p class="wallet-subtitle">Sponsored promotions shown inside the LinkUp Wallet experience.</p>
            </div>
            <button type="button" class="wallet-primary-btn" @click="newWalletAd">
                <Plus class="wallet-btn-icon" />
                New Wallet Ad
            </button>
        </div>

        <div class="wallet-stats-grid">
            <div v-for="stat in stats" :key="stat.label" class="wallet-stat-card">
                <div class="wallet-stat-label">{{ stat.label }}</div>
                <div class="wallet-stat-value">{{ stat.value }}</div>
                <div class="wallet-stat-trend">
                    <TrendingUp class="wallet-trend-icon" />
                    {{ stat.trend }}
                </div>
            </div>
        </div>

        <div class="wallet-table-card">
            <table class="wallet-table">
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
                    <tr v-for="ad in walletAds" :key="ad.ad">
                        <td class="wallet-ad-name">{{ ad.ad }}</td>
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
.wallet-ads-page {
    padding: 24px 28px;
}

.wallet-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.wallet-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 900;
    color: #051020;
}

.wallet-title-icon {
    width: 24px;
    height: 24px;
    color: #0ea5e9;
}

.wallet-subtitle {
    color: #6b7280;
    margin-top: 4px;
}

.wallet-primary-btn {
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

.wallet-primary-btn:hover {
    background: #0891b2;
    transform: translateY(-1px);
}

.wallet-btn-icon {
    width: 16px;
    height: 16px;
}

.wallet-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.wallet-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
}

.wallet-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 800;
}

.wallet-stat-value {
    font-size: 26px;
    font-weight: 900;
    margin-top: 6px;
    color: #051020;
}

.wallet-stat-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #16a34a;
    font-size: 12px;
    font-weight: 800;
    margin-top: 4px;
}

.wallet-trend-icon {
    width: 13px;
    height: 13px;
}

.wallet-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

.wallet-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.wallet-table thead tr {
    background: #f6faf3;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.wallet-table th {
    padding: 12px 16px;
    font-weight: 800;
}

.wallet-table td {
    padding: 12px 16px;
    color: #374151;
    border-top: 1px solid #eef0f2;
}

.wallet-ad-name {
    color: #051020 !important;
    font-weight: 800;
}

.wallet-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
}

.wallet-status-active {
    color: #16a34a;
    background: #dcfce7;
}

.wallet-status-scheduled {
    color: #1d4ed8;
    background: #dbeafe;
}

@media (max-width: 720px) {
    .wallet-ads-page {
        padding: 18px;
    }

    .wallet-table-card {
        overflow-x: auto;
    }

    .wallet-table {
        min-width: 720px;
    }
}
</style>
