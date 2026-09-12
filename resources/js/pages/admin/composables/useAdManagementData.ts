import { ref, computed } from 'vue';

export function useAdManagementData(props: {
    filters: any;
    countries: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
    getScale: () => number;
    props?: any;
}) {
    const ads = ref(props.props?.initialAds || []);
    const emailAds = ref(props.props?.initialEmailAds || []);
    const emailAdCategories = ref(props.props?.initialEmailAdCategories || []);
    const emailCountries = ref(props.props?.initialEmailCountries || []);
    const c360NewsAds = ref(props.props?.initialC360NewsAds || []);
    const campaignTypes = ref(props.props?.initialCampaignTypes || []);
    const territoryTiers = ref(props.props?.initialTerritoryTiers || []);
    const deliveryChannels = ref(props.props?.initialDeliveryChannels || []);
    const exclusivityUpgrades = ref(props.props?.initialExclusivityUpgrades || []);
    const adIndustries = ref(props.props?.initialAdIndustries || []);
    const adSurgeOptions = ref(props.props?.initialAdSurgeOptions || []);
    const campaignLaunches = ref(props.props?.initialCampaignLaunches || []);

    const revenueByChannel = ref(props.props?.initialAdRevenueByChannel || [
        { name: 'C360 News', value: 0, color: '#f97316', width: '0%' },
        { name: 'Swipe Ads', value: 0, color: '#29C9E8', width: '0%' },
        { name: 'Email Ads', value: 0, color: '#14b8a6', width: '0%' },
        { name: 'Clubs', value: 0, color: '#8b5cf6', width: '0%' },
        { name: 'Pending', value: 0, color: '#ef4444', width: '0%' },
    ]);

    const activityFeed = ref(props.props?.initialAdActivityFeed || [
        { type: 'orange', text: '<strong>Sandals Resorts</strong> C360 News placement reached <strong>61,400 impressions</strong>', time: '2 minutes ago' },
        { type: 'green', text: '<strong>Heineken Caribbean</strong> campaign renewed · $22,500 secured', time: '18 minutes ago' },
        { type: 'blue', text: '<strong>Digicel</strong> Newsletter Spot clicked <strong>1,604 times</strong> this week', time: '1 hour ago' },
        { type: 'amber', text: '<strong>Club Nova</strong> ad expiring — renewal recommended', time: '3 hours ago' },
        { type: 'orange', text: '<strong>Scotiabank</strong> C360 News Category Takeover — 2,890 clicks · 5.9% CTR', time: 'Today, 9:14 AM' },
    ]);

    const needsAttention = ref(props.props?.initialAdNeedsAttention || [
        { type: 'red', title: 'Miami Eats — Payment Pending', sub: 'Campaign ended · $49 outstanding', action: 'View', viewId: 'adReportsCommand' },
        { type: 'amber', title: 'Club Nova — Expires in 4 days', sub: '18,700 impressions · 5.2% CTR', action: 'Renew', viewId: 'adReportsCommand' },
        { type: 'blue', title: 'Neon Lounge — Starts in 14 days', sub: '$349 Premium · South Beach', action: 'Preview', viewId: 'adReportsCommand' },
    ]);

    const adPricing = ref({
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
            { key: 'clubs', name: 'Clubs & Fetes', model: 'Flat', base: 150, reach: 1 }
        ],
        tiers: [
            { key: 'standard', name: 'Standard', mult: 1.0, perks: 'Standard rotation' },
            { key: 'premium', name: 'Premium', mult: 1.6, perks: 'Priority slots + geo targeting' },
            { key: 'elite', name: 'Elite', mult: 2.5, perks: 'Top slot, full targeting, verified badge' }
        ],
        durations: [
            { key: 'day', name: 'Per Day', days: 1, disc: 0 },
            { key: 'week', name: 'Per Week', days: 7, disc: 5 },
            { key: 'biweekly', name: 'Bi-Weekly', days: 14, disc: 8 },
            { key: 'monthly', name: 'Monthly', days: 30, disc: 10 },
            { key: 'bimonthly', name: 'Bi-Monthly', days: 60, disc: 18 },
            { key: 'quarterly', name: 'Quarterly', days: 90, disc: 25 },
            { key: 'yearly', name: 'Yearly', days: 365, disc: 40 }
        ],
        targeting: { geo: 20, audience: 30, category: 15 }
    });

    const promoTiers = ref([
        { key: 'starter', name: 'Starter', price: 49, impr: 50000, placements: 'Vibes Feed + event listing boost', perks: 'Standard rotation' },
        { key: 'pro', name: 'Pro', price: 149, impr: 250000, placements: '+ Push + Email + C360 News', perks: 'Priority slots · featured event' },
        { key: 'headliner', name: 'Headliner', price: 399, impr: 1000000, placements: '+ In-App + Live + homepage feature', perks: 'Top slot · full targeting · verified' }
    ]);

    const vibesAds = ref([
        { id: 'AD-9001', advertiser: 'Scotiabank', handle: '@scotiabank', logo: '🏦', caption: 'Bank smarter across the Caribbean with the Scotiabank + LinkUp wallet. 💳 #linkup', img: 'https://picsum.photos/seed/adbank/600/600', cta: 'Learn More', url: 'https://www.scotiabank.com', target: 'Caribbean', status: 'Active', likes: 4820, comments: 120, shares: 340, followers: 18200, liked: false },
        { id: 'AD-9002', advertiser: 'Caribbean Travel Co.', handle: '@caribtravel', logo: '✈️', caption: 'Escape to the islands ☀️ Exclusive LinkUp member rates on flights + resorts.', img: 'https://picsum.photos/seed/adtravel/600/600', cta: 'Book Now', url: 'https://example.com/travel', target: 'All', status: 'Active', likes: 9120, comments: 430, shares: 1200, followers: 52000, liked: false },
        { id: 'AD-9003', advertiser: 'Digicel', handle: '@digicel', logo: '📱', caption: 'Fastest Caribbean data plans. Stream LinkUp Vibes anywhere. 🔥', img: 'https://picsum.photos/seed/addigicel/600/600', cta: 'Get the Plan', url: 'https://www.digicelgroup.com', target: 'All', status: 'Paused', likes: 2100, comments: 60, shares: 140, followers: 9800, liked: false }
    ]);

    const filteredAds = computed(() => {
        return ads.value.filter((a: any) => {
            const countriesArr = Array.isArray(props.countries) ? props.countries : props.countries.value;
            const filtersVal = props.filters.value || props.filters;

            const adCity = a.city || '';
            const c = countriesArr?.find((x: any) => x.country === adCity.split(',')[0]);

            return (filtersVal.region === 'All' || c?.region === filtersVal.region) &&
                   (filtersVal.country === 'All Countries' || adCity.includes(filtersVal.country));
        });
    });

    const adStats = computed(() => {
        if (props.props?.initialAdStats) {
            return {
                totalImpressions: props.props.initialAdStats.impressions,
                totalClicks: props.props.initialAdStats.clicks,
                totalLikes: props.props.initialAdStats.likes,
                activeAds: props.props.initialAdStats.active_ads,
                totalRevenue: props.props.initialAdStats.total_revenue || 0,
                email: props.props.initialAdStats.email || {
                    impressions: 0,
                    clicks: 0,
                    opens: 0,
                    revenue: 0,
                    active_ads: 0,
                    categories: [],
                    countries: [],
                },
                avgCtr: props.props.initialAdStats.impressions ? (props.props.initialAdStats.clicks / props.props.initialAdStats.impressions * 100) : 0
            };
        }

        const list = filteredAds.value;
        const totalImpressions = list.reduce((a: number, b: any) => a + (b.impressions_count || 0), 0);
        const totalClicks = list.reduce((a: number, b: any) => a + (b.clicks_count || 0), 0);
        const totalLikes = list.reduce((a: number, b: any) => a + (b.clicks_count || 0), 0);
        const activeAds = list.filter((a: any) => a.status == 1).length;
        const totalRevenue = list.reduce((a: number, b: any) => a + parseFloat(b.cost || 0), 0);

        return {
            totalImpressions,
            totalClicks,
            totalLikes,
            activeAds,
            totalRevenue,
            email: props.props?.initialAdStats?.email || {
                impressions: 0,
                clicks: 0,
                opens: 0,
                revenue: 0,
                active_ads: 0,
                categories: [],
                countries: [],
            },
            avgCtr: totalImpressions ? (totalClicks / totalImpressions * 100) : 0
        };
    });

    return {
        ads,
        emailAds,
        emailAdCategories,
        emailCountries,
        c360NewsAds,
        adPricing,
        promoTiers,
        vibesAds,
        filteredAds,
        adStats,
        revenueByChannel,
        activityFeed,
        needsAttention,
        campaignTypes,
        territoryTiers,
        deliveryChannels,
        exclusivityUpgrades,
        adIndustries,
        adSurgeOptions,
        campaignLaunches
    };
}
