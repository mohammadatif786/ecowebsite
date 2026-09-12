<script setup lang="ts">
import { Plus, ShoppingBag, TrendingUp } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type MarketplaceAd = {
    ad: string;
    advertiser: string;
    spend: string;
    impressions: string;
    ctr: string;
    status: 'Active' | 'Paused';
};

const stats = [
    { label: 'Impressions', value: '128,400', trend: '+14% MoM' },
    { label: 'Clicks', value: '6,210', trend: '+9% MoM' },
    { label: 'Avg CTR', value: '4.8%', trend: '+0.6 pts' },
    { label: 'Ad Revenue', value: '$4,820', trend: '+12% MoM' },
];

const marketplaceAds: MarketplaceAd[] = [
    { ad: 'Summer Beauty Drop', advertiser: 'Caribbean Beauty Store', spend: '$640', impressions: '32,400', ctr: '5.1%', status: 'Active' },
    { ad: 'Handmade Crafts Promo', advertiser: 'Nassau Craft Market', spend: '$410', impressions: '21,800', ctr: '4.4%', status: 'Active' },
    { ad: 'Island Spices Bundle', advertiser: 'Trini Flavors Shop', spend: '$280', impressions: '14,200', ctr: '3.9%', status: 'Paused' },
];

function statusClass(status: MarketplaceAd['status']) {
    return status === 'Active'
        ? 'market-status market-status-active'
        : 'market-status market-status-paused';
}

function newMarketplaceAd() {
    toast.info('Marketplace Ads: new ad flow coming next.');
}
</script>

<template>
    <div class="market-ads-page">
        <Toaster rich-colors position="top-right" />

        <div class="market-header">
            <div>
                <div class="market-title">
                    <ShoppingBag class="market-title-icon" />
                    Marketplace Ads
                </div>
                <p class="market-subtitle">Promote seller products and shops across the LinkUp Marketplace.</p>
            </div>
            <button type="button" class="market-primary-btn" @click="newMarketplaceAd">
                <Plus class="market-btn-icon" />
                New Marketplace Ad
            </button>
        </div>

        <div class="market-stats-grid">
            <div v-for="stat in stats" :key="stat.label" class="market-stat-card">
                <div class="market-stat-label">{{ stat.label }}</div>
                <div class="market-stat-value">{{ stat.value }}</div>
                <div class="market-stat-trend">
                    <TrendingUp class="market-trend-icon" />
                    {{ stat.trend }}
                </div>
            </div>
        </div>

        <div class="market-table-card">
            <table class="market-table">
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
                    <tr v-for="ad in marketplaceAds" :key="ad.ad">
                        <td class="market-ad-name">{{ ad.ad }}</td>
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
.market-ads-page {
    padding: 24px 28px;
}

.market-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.market-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 900;
    color: #051020;
}

.market-title-icon {
    width: 24px;
    height: 24px;
    color: #00a63e;
}

.market-subtitle {
    color: #6b7280;
    margin-top: 4px;
}

.market-primary-btn {
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

.market-primary-btn:hover {
    background: #0891b2;
    transform: translateY(-1px);
}

.market-btn-icon {
    width: 16px;
    height: 16px;
}

.market-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.market-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
}

.market-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 800;
}

.market-stat-value {
    font-size: 26px;
    font-weight: 900;
    margin-top: 6px;
    color: #051020;
}

.market-stat-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #16a34a;
    font-size: 12px;
    font-weight: 800;
    margin-top: 4px;
}

.market-trend-icon {
    width: 13px;
    height: 13px;
}

.market-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

.market-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.market-table thead tr {
    background: #f6faf3;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.market-table th {
    padding: 12px 16px;
    font-weight: 800;
}

.market-table td {
    padding: 12px 16px;
    color: #374151;
    border-top: 1px solid #eef0f2;
}

.market-ad-name {
    color: #051020 !important;
    font-weight: 800;
}

.market-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
}

.market-status-active {
    color: #16a34a;
    background: #dcfce7;
}

.market-status-paused {
    color: #b45309;
    background: #fef3c7;
}

@media (max-width: 720px) {
    .market-ads-page {
        padding: 18px;
    }

    .market-table-card {
        overflow-x: auto;
    }

    .market-table {
        min-width: 720px;
    }
}
</style>
