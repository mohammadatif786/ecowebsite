<script setup lang="ts">
import { CalendarDays, Plus, TrendingUp } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type SponsoredEvent = {
    event: string;
    sponsor: string;
    spend: string;
    reach: string;
    ctr: string;
    status: 'Active' | 'Completed';
};

const stats = [
    { label: 'Sponsored Events', value: '28', trend: '+6 new' },
    { label: 'Total Reach', value: '184,000', trend: '+16% MoM' },
    { label: 'Sponsor Revenue', value: '$14,500', trend: '+22% MoM' },
    { label: 'Avg CTR', value: '5.6%', trend: '+0.5 pts' },
];

const sponsoredEvents: SponsoredEvent[] = [
    { event: "Fred's Cookout - Garlic Jalapeno", sponsor: 'The Best Sauce', spend: '$1,500', reach: '38,000', ctr: '6.1%', status: 'Active' },
    { event: 'Miami Carnival Warm-Up', sponsor: 'Island Vibes', spend: '$2,500', reach: '72,000', ctr: '5.4%', status: 'Active' },
    { event: 'NYC Soca Roof', sponsor: 'Carib Link', spend: '$1,800', reach: '49,000', ctr: '5.0%', status: 'Completed' },
];

function statusClass(status: SponsoredEvent['status']) {
    return status === 'Active'
        ? 'events-status events-status-active'
        : 'events-status events-status-completed';
}

function newSponsoredAd() {
    toast.info('Sponsored Events: new ad flow coming next.');
}
</script>

<template>
    <div class="events-ads-page">
        <Toaster rich-colors position="top-right" />

        <div class="events-header">
            <div>
                <div class="events-title">
                    <CalendarDays class="events-title-icon" />
                    Sponsored Events
                </div>
                <p class="events-subtitle">Brand-sponsored events, cookouts, and fetes across the Caribbean.</p>
            </div>
            <button type="button" class="events-primary-btn" @click="newSponsoredAd">
                <Plus class="events-btn-icon" />
                New Sponsored Ad
            </button>
        </div>

        <div class="events-stats-grid">
            <div v-for="stat in stats" :key="stat.label" class="events-stat-card">
                <div class="events-stat-label">{{ stat.label }}</div>
                <div class="events-stat-value">{{ stat.value }}</div>
                <div class="events-stat-trend">
                    <TrendingUp class="events-trend-icon" />
                    {{ stat.trend }}
                </div>
            </div>
        </div>

        <div class="events-table-card">
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Sponsor</th>
                        <th>Spend</th>
                        <th>Reach</th>
                        <th>CTR</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="event in sponsoredEvents" :key="event.event">
                        <td class="events-name">{{ event.event }}</td>
                        <td>{{ event.sponsor }}</td>
                        <td>{{ event.spend }}</td>
                        <td>{{ event.reach }}</td>
                        <td>{{ event.ctr }}</td>
                        <td>
                            <span :class="statusClass(event.status)">{{ event.status }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.events-ads-page {
    padding: 24px 28px;
}

.events-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.events-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 900;
    color: #051020;
}

.events-title-icon {
    width: 24px;
    height: 24px;
    color: #f97316;
}

.events-subtitle {
    color: #6b7280;
    margin-top: 4px;
}

.events-primary-btn {
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

.events-primary-btn:hover {
    background: #0891b2;
    transform: translateY(-1px);
}

.events-btn-icon {
    width: 16px;
    height: 16px;
}

.events-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.events-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
}

.events-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 800;
}

.events-stat-value {
    font-size: 26px;
    font-weight: 900;
    margin-top: 6px;
    color: #051020;
}

.events-stat-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #16a34a;
    font-size: 12px;
    font-weight: 800;
    margin-top: 4px;
}

.events-trend-icon {
    width: 13px;
    height: 13px;
}

.events-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

.events-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.events-table thead tr {
    background: #f6faf3;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.events-table th {
    padding: 12px 16px;
    font-weight: 800;
}

.events-table td {
    padding: 12px 16px;
    color: #374151;
    border-top: 1px solid #eef0f2;
}

.events-name {
    color: #051020 !important;
    font-weight: 800;
}

.events-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
}

.events-status-active {
    color: #16a34a;
    background: #dcfce7;
}

.events-status-completed {
    color: #475569;
    background: #e2e8f0;
}

@media (max-width: 720px) {
    .events-ads-page {
        padding: 18px;
    }

    .events-table-card {
        overflow-x: auto;
    }

    .events-table {
        min-width: 720px;
    }
}
</style>
