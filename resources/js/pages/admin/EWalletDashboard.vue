<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Search, Bell, Newspaper, Wallet } from 'lucide-vue-next';
import { useEWalletData } from '@/pages/admin/composables/useEWalletData';
import { useGovernmentData } from '@/pages/admin/composables/useGovernmentData';
import { useAdManagementData } from '@/pages/admin/composables/useAdManagementData';

// Import View Components
import MissionControl from './EWallet/MissionControl.vue';
import TransactionsLedger from './EWallet/TransactionsLedger.vue';
import WalletUsers from './EWallet/WalletUsers.vue';
import WalletBalances from './EWallet/WalletBalances.vue';
import SettlementCenter from './EWallet/SettlementCenter.vue';
import AsueDrawer from './EWallet/AsueDrawer.vue';
import BillGateway from './EWallet/BillGateway.vue';
import PayoutQueue from './EWallet/PayoutQueue.vue';
import CoinsTreasury from './EWallet/CoinsTreasury.vue';
import LinkUpSave from './EWallet/LinkUpSave.vue';
import Cryptocurrency from './EWallet/Cryptocurrency.vue';
import ComplianceDesk from './EWallet/ComplianceDesk.vue';
import RiskReconciliation from './EWallet/RiskReconciliation.vue';
import AuditTrail from './EWallet/AuditTrail.vue';
import SystemLogs from './EWallet/SystemLogs.vue';
import ScotiaPortal from './EWallet/ScotiaPortal.vue';

// Ad Management Views
import AdDashboard from './AdManagement/AdDashboard.vue';
import AdList from './AdManagement/AdList.vue';
import AdAnalytics from './AdManagement/AdAnalytics.vue';
import AdReports from './AdManagement/AdReports.vue';
import EmailSponsors from './AdManagement/EmailSponsors.vue';
import CampaignBuilder from './AdManagement/CampaignBuilder.vue';
import AdPricing from './AdManagement/AdPricing.vue';
import NewsAds from './AdManagement/NewsAds.vue';
import VibesAds from './AdManagement/VibesAds.vue';
import WalletAds from './AdManagement/WalletAds.vue';
import MarketplaceAds from './AdManagement/MarketplaceAds.vue';
import SponsoredEvents from './AdManagement/SponsoredEvents.vue';
import LiveStreamAds from './AdManagement/LiveStreamAds.vue';
import PushNotificationAds from './AdManagement/PushNotificationAds.vue';
import RestaurantsAds from './AdManagement/RestaurantsAds.vue';
import ClubsFetesAds from './AdManagement/ClubsFetesAds.vue';
import PromoteAdvertise from './AdManagement/PromoteAdvertise.vue';
import SendEmails from './Emails/SendEmails.vue';
import EmailTemplates from './Emails/EmailTemplates.vue';
import Caribbean360News from './Caribbean360News/Caribbean360News.vue';
import PushNotifications from './PushNotifications.vue';
import ReviewsConcerns from './TrustOperations/ReviewsConcerns.vue';
import KycReview from './TrustOperations/KycReview.vue';
import FlaggedUsers from './TrustOperations/FlaggedUsers.vue';
import FraudCenter from './TrustOperations/FraudCenter.vue';
import Controls from './TrustOperations/Controls.vue';
import Operations from './TrustOperations/Operations.vue';
import PartnerPortal from './TrustOperations/PartnerPortal.vue';
import FeesDashboard from './Administration/FeesDashboard.vue';
import AdminsDashboard from './Administration/AdminsDashboard.vue';

// Government Portal Views
import GovDashboard from './GovernmentPortal/GovDashboard.vue';
import GovBeneficiaries from './GovernmentPortal/GovBeneficiaries.vue';
import GovDisbursements from './GovernmentPortal/GovDisbursements.vue';
import GovSettings from './GovernmentPortal/GovSettings.vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialWalletMovements: any[];
    initialWalletUsers: any[];
    initialWalletUserStats: any;
    initialWalletCountryBalances: any[];
    initialCurrencyExposure: any[];
    initialWalletBalanceSummary: any;
    initialSettlements: any[];
    initialSettlementSummary: any;
    initialAsueCircles: any[];
    initialAsueSummary: any;
    initialPayoutQueue: any[];
    initialPayoutQueueSummary: any;
    initialAds?: any[];
    initialEmailAds?: any[];
    initialEmailAdCategories?: any[];
    initialEmailCountries?: any[];
    initialC360NewsAds?: any[];
    initialAdStats?: any;
    initialAdRevenueByChannel?: any[];
    initialAdActivityFeed?: any[];
    initialAdNeedsAttention?: any[];
    initialCampaignTypes?: any[];
    initialTerritoryTiers?: any[];
    initialDeliveryChannels?: any[];
    initialExclusivityUpgrades?: any[];
    initialAdIndustries?: any[];
    initialAdSurgeOptions?: any[];
    initialCampaignLaunches?: any[];
    initialReviewsConcerns?: any[];
    initialKycReviews?: any[];
    initialFlaggedUsers?: any[];
    initialAdminUsers?: any[];
    initialAdminRoles?: any[];
    initialPermissionModules?: string[];
    initialEventFeeSettings?: Record<string, any>;
}>();

const {
    units,
    countries,
    filters,
    fmt,
    num,
    getScale,
    getFilteredCountries,
    ribbonMetrics,
    walletMovements,
    walletFilter,
    filteredMovements,
    walletStats,
    walletCountryStats,
    walletUserDirectory,
    walletUserAccounts,
    walletUserAccountStats,
    walletCountryBalances,
    currencyExposure,
    walletBalanceSummary,
    settlements,
    settlementSummary,
    asueCircles,
    asueSummary,
    payoutQueue,
    payoutQueueSummary,
    complianceCases,
    billProviders,
    auditLogs,
    cryptoPurchases
} = useEWalletData(props);

const {
    filteredAds,
    emailAds,
    emailAdCategories,
    emailCountries,
    c360NewsAds,
    adStats,
    revenueByChannel,
    activityFeed,
    needsAttention,
    vibesAds,
    campaignTypes,
    territoryTiers,
    deliveryChannels,
    exclusivityUpgrades,
    adIndustries,
    adSurgeOptions,
    campaignLaunches
} = useAdManagementData({ filters, countries, fmt, num, getScale, props });

const {
    filteredPrograms,
    filteredBeneficiaries,
    filteredBatches,
    govStats,
    govConfig,
    govMonthlyPayouts,
    govFeePer
} = useGovernmentData({ filters, countries, fmt, num, getScale });

const sidebarVisible = ref(true);
const activeViewId = ref(localStorage.getItem('ewallet_active_view') || 'walletCommand');
const adListRef = ref<any>(null);
const newsViewIds = [
    'newsFeedCommand',
    'newsDashboardCommand',
    'newsListCommand',
    'newsCreateCommand',
    'newsCategoriesCommand',
    'newsRegionsCommand',
    'newsSponsoredCommand',
    'newsAuthorsCommand',
];
const feeViewIds = [
    'feesCenterCommand',
    'eventFeeCommand',
    'eatsFeesPricingCommand',
    'marketplaceFeesCommand',
    'pricingCommand',
];
const adminViewIds = [
    'adminRolesCommand',
    'adminUsersCommand',
    'adminAccessMatrixCommand',
    'settingsSecurityCommand',
    'scotiaAccessCommand',
    'adminAuditCommand',
];

const isAdView = computed(() => {
    return (activeViewId.value.startsWith('ad') && !activeViewId.value.startsWith('admin')) ||
           ['feedAdsCommand', 'organizerPromoteCommand'].includes(activeViewId.value);
});

const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

const handleViewChange = (id: string, options: any = {}) => {
    activeViewId.value = id;
    localStorage.setItem('ewallet_active_view', id);

    if (options.openAdForm && id === 'adAllAdsCommand') {
        setTimeout(() => {
            if (adListRef.value) adListRef.value.openForm();
        }, 50);
    }
};

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});

const labelFor = (id: string) => {
    const pages = [
        ['walletCommand', 'Mission Control'],
        ['walletTransactionsCommand', 'Transactions Ledger'],
        ['walletUsersCommand', 'Wallet Users'],
        ['walletBalancesCommand', 'Wallet Balances'],
        ['walletSettlementCenterCommand', 'Settlement Center'],
        ['asuDrawerCommand', 'ASUE Drawer Data'],
        ['billGatewayCommand', 'Bill Gateway'],
        ['walletPayoutQueueCommand', 'Payout Queue'],
        ['coins', 'Coins'],
        ['savingsCommand', 'LinkUp Save'],
        ['cryptoCommand', 'Cryptocurrency'],
        ['walletComplianceCommand', 'Compliance Desk'],
        ['walletRiskReconCommand', 'Risk & Reconciliation'],
        ['walletAuditCommand', 'Audit Trail'],
        ['walletSystemLogsCommand', 'System Logs'],
        ['reviewsConcernsCommand', 'Reviews & Concerns'],
        ['kycCommand', 'KYC Review'],
        ['flaggedUsersCommand', 'Flagged Users'],
        ['fraud', 'Fraud Center'],
        ['controlCommand', 'Controls'],
        ['ops', 'Operations'],
        ['partner', 'Partner Portal'],
        ['feesCenterCommand', 'Fees Center'],
        ['eventFeeCommand', 'Event Fees'],
        ['eatsFeesPricingCommand', 'Eats Fees & Pricing'],
        ['marketplaceFeesCommand', 'Marketplace Fees'],
        ['pricingCommand', 'Pricing & Plans'],
        ['adminRolesCommand', 'Roles & Permissions'],
        ['adminUsersCommand', 'Admin Users'],
        ['adminAccessMatrixCommand', 'Access Matrix'],
        ['settingsSecurityCommand', 'Security & Access'],
        ['scotiaAccessCommand', 'Partner Access (Scotiabank)'],
        ['adminAuditCommand', 'Admin Audit Trail'],
        ['govDashboardCommand', 'Gov Dashboard'],
        ['govBeneficiariesCommand', 'Beneficiary Registry'],
        ['govDisbursementsCommand', 'Disbursements'],
        ['govSettingsCommand', 'Fees & Settings'],
        ['adDashboardCommand', 'Ad Dashboard'],
        ['adAnalyticsCommand', 'Ad Analytics'],
        ['adReportsCommand', 'Ad Reports'],
        ['adAllAdsCommand', 'All Ads'],
        ['adRestaurantsCommand', 'Restaurant Ads'],
        ['adClubsFetesCommand', 'Clubs & Fetes'],
        ['adCampaignsCommand', 'Campaign Builder'],
        ['adEmailSponsorCommand', 'Email Sponsors'],
        ['adC360NewsCommand', 'News Ads'],
        ['adWalletAdsCommand', 'Wallet Ads'],
        ['adMarketplaceAdsCommand', 'Marketplace Ads'],
        ['adSponsoredEventsCommand', 'Sponsored Events'],
        ['adLiveStreamAdsCommand', 'Live Stream Ads'],
        ['adPushNotificationAdsCommand', 'Push Ads'],
        ['feedAdsCommand', 'Vibes Ads'],
        ['organizerPromoteCommand', 'Promote & Advertise'],
        ['sendEmailCommand', 'Send Emails'],
        ['emailTemplatesCommand', 'Email Templates'],
        ['newsFeedCommand', 'News Feed (Reader) Command Center'],
        ['newsDashboardCommand', 'News Dashboard'],
        ['newsListCommand', 'News List'],
        ['newsCreateCommand', 'Create Story'],
        ['newsCategoriesCommand', 'News Categories'],
        ['newsRegionsCommand', 'News Regions'],
        ['newsSponsoredCommand', 'Sponsored News'],
        ['newsAuthorsCommand', 'News Authors'],
        ['notifications', 'Push Notification Center'],
    ];
    const m = pages.find((p) => p[0] === id);
    return m ? m[1] : id;
};
</script>

<template>
    <Head :title="labelFor(activeViewId)" />
    <div class="flex min-h-screen">
        <NewAppSidebar :active-id="activeViewId" v-show="sidebarVisible" @view-changed="handleViewChange" />

        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                :title="labelFor(activeViewId)"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <!-- FULL SUITE HEADER (ADS ONLY - MOVED DOWN) -->
            <div v-if="isAdView" class="px-5 pt-8 lg:px-8 bg-gradient-to-b from-[#f8fbff] to-transparent">
                <div class="flex items-center justify-between mb-1">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">LinkUp Ad Manager — <span class="text-cyan-600">Full Suite</span></h1>
                    <button class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/50 px-4 py-2 text-xs font-black hover:bg-white transition-colors">
                        Open in new tab <span class="flex h-4 w-4 items-center justify-center rounded bg-cyan-600 text-[10px] text-white">↗</span>
                    </button>
                </div>
                <p class="text-xs font-bold text-slate-400">Complete ad management: dashboard, analytics, reports, ads, restaurants, clubs, campaigns, sponsors & news — all in one.</p>

                <!-- AD SUITE TOPBAR -->
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-[2rem] bg-white p-4 shadow-sm border border-slate-100 mb-6">
                    <div class="flex items-center gap-6">
                        <h3 class="text-lg font-black text-slate-900 ml-4">{{ labelFor(activeViewId) }}</h3>
                        <div class="h-6 w-px bg-slate-100 hidden sm:block"></div>
                        <div class="relative hidden lg:block">
                            <input
                                type="text"
                                class="w-64 rounded-xl border-none bg-slate-50 px-4 py-2 pl-10 text-xs font-bold focus:ring-2 focus:ring-cyan-500/20"
                                placeholder="Search ads, advertisers…"
                            />
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pr-2">
                        <button @click="handleViewChange('adReportsCommand')" class="flex items-center gap-2 rounded-xl border border-slate-100 bg-white px-4 py-2 text-xs font-black text-slate-600 hover:bg-slate-50 transition-colors">
                            <Newspaper class="h-4 w-4" /> Report
                        </button>
                        <div class="relative">
                            <button class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-100 transition-colors">
                                <Bell class="h-4 w-4" />
                            </button>
                            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full border-2 border-white bg-cyan-500"></span>
                        </div>
                        <button
                            @click="handleViewChange('adAllAdsCommand', { openAdForm: true })"
                            class="rounded-xl bg-cyan-500 px-5 py-2 text-xs font-black text-white hover:bg-cyan-600 transition-all shadow-md shadow-cyan-500/20"
                        >
                            + New Ad
                        </button>
                    </div>
                </div>
            </div>

            <section class="p-5 lg:p-8">
                <!-- MISSION CONTROL -->
                <MissionControl
                    v-if="activeViewId === 'walletCommand'"
                    :wallet-stats="walletStats"
                    :wallet-country-stats="walletCountryStats"
                    :get-filtered-countries="getFilteredCountries"
                    :fmt="fmt"
                    :num="num"
                    :get-scale="getScale"
                    :wallet-movements="walletMovements"
                    :filtered-movements="filteredMovements"
                    :wallet-filter="walletFilter"
                    :wallet-user-directory="walletUserDirectory"
                />

                <!-- TRANSACTIONS LEDGER -->
                <TransactionsLedger
                    v-else-if="activeViewId === 'walletTransactionsCommand'"
                    :filtered-movements="filteredMovements"
                    :wallet-filter="walletFilter"
                    :fmt="fmt"
                    :get-scale="getScale"
                />

                <!-- WALLET USERS -->
                <WalletUsers
                    v-else-if="activeViewId === 'walletUsersCommand'"
                    :wallet-user-accounts="walletUserAccounts"
                    :wallet-user-account-stats="walletUserAccountStats"
                    :fmt="fmt"
                    :num="num"
                    :get-scale="getScale"
                />

                <!-- WALLET BALANCES -->
                <WalletBalances
                    v-else-if="activeViewId === 'walletBalancesCommand'"
                    :wallet-country-balances="walletCountryBalances"
                    :currency-exposure="currencyExposure"
                    :wallet-balance-summary="walletBalanceSummary"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- SETTLEMENT CENTER -->
                <SettlementCenter
                    v-else-if="activeViewId === 'walletSettlementCenterCommand'"
                    :settlements="settlements"
                    :settlement-summary="settlementSummary"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- ASUE DRAWER -->
                <AsueDrawer
                    v-else-if="activeViewId === 'asuDrawerCommand'"
                    :circles="asueCircles"
                    :asue-summary="asueSummary"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- BILL GATEWAY -->
                <BillGateway
                    v-else-if="activeViewId === 'billGatewayCommand'"
                    :bill-providers="billProviders"
                    :fmt="fmt"
                />

                <!-- PAYOUT QUEUE -->
                <PayoutQueue
                    v-else-if="activeViewId === 'walletPayoutQueueCommand'"
                    :payout-queue="payoutQueue"
                    :payout-queue-summary="payoutQueueSummary"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- COINS -->
                <CoinsTreasury
                    v-else-if="activeViewId === 'coins'"
                    :get-filtered-countries="getFilteredCountries"
                    :get-scale="getScale"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- SAVINGS -->
                <LinkUpSave
                    v-else-if="activeViewId === 'savingsCommand'"
                />

                <!-- CRYPTO -->
                <Cryptocurrency
                    v-else-if="activeViewId === 'cryptoCommand'"
                    :fmt="fmt"
                    :initial-crypto-purchases="cryptoPurchases"
                />

                <!-- COMPLIANCE DESK -->
                <ComplianceDesk
                    v-else-if="activeViewId === 'walletComplianceCommand'"
                    :compliance-cases="complianceCases"
                    :fmt="fmt"
                    :filters="filters"
                    :countries="countries"
                    :get-scale="getScale"
                />

                <!-- RISK & RECONCILIATION -->
                <RiskReconciliation
                    v-else-if="activeViewId === 'walletRiskReconCommand'"
                    :recon-items="reconItems"
                    :risk-rules="riskRules"
                    :compliance-cases="complianceCases"
                    :fmt="fmt"
                    :filters="filters"
                    :countries="countries"
                />

                <!-- AUDIT TRAIL -->
                <AuditTrail
                    v-else-if="activeViewId === 'walletAuditCommand'"
                    :audit-logs="auditLogs"
                />

                <!-- SYSTEM LOGS -->
                <SystemLogs
                    v-else-if="activeViewId === 'walletSystemLogsCommand'"
                />

                <!-- SCOTIABANK PORTAL -->
                <ScotiaPortal
                    v-else-if="activeViewId === 'scotiaPortal'"
                    :units="units"
                    :countries="countries"
                    :fmt="fmt"
                    :num="num"
                    :get-scale="getScale"
                />

                <!-- GOVERNMENT DASHBOARD -->
                <GovDashboard
                    v-else-if="activeViewId === 'govDashboardCommand'"
                    :stats="govStats"
                    :programs="filteredPrograms"
                    :batches="filteredBatches"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- BENEFICIARY REGISTRY -->
                <GovBeneficiaries
                    v-else-if="activeViewId === 'govBeneficiariesCommand'"
                    :beneficiaries="filteredBeneficiaries"
                    :programs="filteredPrograms"
                    :fmt="fmt"
                    :num="num"
                />

                <!-- DISBURSEMENTS -->
                <GovDisbursements
                    v-else-if="activeViewId === 'govDisbursementsCommand'"
                    :programs="filteredPrograms"
                    :batches="filteredBatches"
                    :fmt="fmt"
                    :num="num"
                    :gov-monthly-payouts="govMonthlyPayouts"
                    :gov-fee-per="govFeePer"
                />

                <!-- GOV SETTINGS -->
                <GovSettings
                    v-else-if="activeViewId === 'govSettingsCommand'"
                    :config="govConfig"
                    :fmt="fmt"
                />

                <!-- AD MANAGEMENT VIEWS -->
                <AdDashboard
                    v-else-if="activeViewId === 'adDashboardCommand'"
                    :ads="filteredAds"
                    :ad-stats="adStats"
                    :revenue-by-channel="revenueByChannel"
                    :activity-feed="activityFeed"
                    :needs-attention="needsAttention"
                    :fmt="fmt"
                    :num="num"
                    @view-changed="handleViewChange"
                />

                <AdList
                    v-else-if="activeViewId === 'adAllAdsCommand'"
                    ref="adListRef"
                    :ads="filteredAds"
                    :ad-stats="adStats"
                    :fmt="fmt"
                    :num="num"
                    @view-changed="handleViewChange"
                />

                <AdAnalytics
                    v-else-if="activeViewId === 'adAnalyticsCommand'"
                    :ads="filteredAds"
                    :email-ads="emailAds"
                    :ad-stats="adStats"
                    :fmt="fmt"
                    :num="num"
                />

                <AdReports
                    v-else-if="activeViewId === 'adReportsCommand'"
                    :ads="filteredAds"
                    :email-ads="emailAds"
                    :ad-stats="adStats"
                    :fmt="fmt"
                    :num="num"
                />

                <EmailSponsors
                    v-else-if="activeViewId === 'adEmailSponsorCommand'"
                    :email-ads="emailAds"
                    :email-categories="emailAdCategories"
                    :email-countries="emailCountries"
                    :fmt="fmt"
                    :num="num"
                />

                <CampaignBuilder
                    v-else-if="activeViewId === 'adCampaignsCommand'"
                    :fmt="fmt"
                    :num="num"
                    :campaign-types="campaignTypes"
                    :territory-tiers="territoryTiers"
                    :delivery-channels="deliveryChannels"
                    :exclusivity-upgrades="exclusivityUpgrades"
                    :ad-industries="adIndustries"
                    :ad-surge-options="adSurgeOptions"
                    :campaign-launches="campaignLaunches"
                    @view-changed="handleViewChange"
                />

                <AdPricing
                    v-else-if="activeViewId === 'adPricingCommand'"
                />

                <NewsAds
                    v-else-if="activeViewId === 'adC360NewsCommand'"
                    :news-ads="c360NewsAds"
                    :fmt="fmt"
                    :num="num"
                />

                <RestaurantsAds
                    v-else-if="activeViewId === 'adRestaurantsCommand'"
                    @view-changed="handleViewChange"
                />

                <ClubsFetesAds
                    v-else-if="activeViewId === 'adClubsFetesCommand'"
                    @view-changed="handleViewChange"
                />

                <VibesAds
                    v-else-if="activeViewId === 'feedAdsCommand'"
                    :vibes-ads="vibesAds"
                    :fmt="fmt"
                    :num="num"
                    @view-changed="handleViewChange"
                />

                <WalletAds
                    v-else-if="activeViewId === 'adWalletAdsCommand'"
                />

                <MarketplaceAds
                    v-else-if="activeViewId === 'adMarketplaceAdsCommand'"
                />

                <SponsoredEvents
                    v-else-if="activeViewId === 'adSponsoredEventsCommand'"
                />

                <PromoteAdvertise
                    v-else-if="activeViewId === 'organizerPromoteCommand'"
                    type="events"
                />

                <LiveStreamAds
                    v-else-if="activeViewId === 'adLiveStreamAdsCommand'"
                />

                <PushNotificationAds
                    v-else-if="activeViewId === 'adPushNotificationAdsCommand'"
                />

                <SendEmails
                    v-else-if="activeViewId === 'sendEmailCommand'"
                />

                <EmailTemplates
                    v-else-if="activeViewId === 'emailTemplatesCommand'"
                    @view-changed="handleViewChange"
                />

                <Caribbean360News
                    v-else-if="newsViewIds.includes(activeViewId)"
                    :view-id="activeViewId"
                    @view-changed="handleViewChange"
                />

                <PushNotifications v-else-if="activeViewId === 'notifications'" />

                <ReviewsConcerns
                    v-else-if="activeViewId === 'reviewsConcernsCommand'"
                    :initial-reviews-concerns="props.initialReviewsConcerns || []"
                />

                <KycReview
                    v-else-if="activeViewId === 'kycCommand'"
                    :initial-kyc-reviews="props.initialKycReviews || []"
                />

                <FlaggedUsers
                    v-else-if="activeViewId === 'flaggedUsersCommand'"
                    :initial-flagged-users="props.initialFlaggedUsers || []"
                />

                <FraudCenter v-else-if="activeViewId === 'fraud'" />

                <Controls v-else-if="activeViewId === 'controlCommand'" />

                <Operations v-else-if="activeViewId === 'ops'" />

                <PartnerPortal v-else-if="activeViewId === 'partner'" />

                <FeesDashboard
                    v-else-if="feeViewIds.includes(activeViewId)"
                    :view-id="activeViewId"
                    :initial-event-fee-settings="initialEventFeeSettings"
                    @change-view="handleViewChange"
                />

                <AdminsDashboard
                    v-else-if="adminViewIds.includes(activeViewId)"
                    :view-id="activeViewId"
                    :initial-admin-users="initialAdminUsers"
                    :initial-admin-roles="initialAdminRoles"
                    :initial-permission-modules="initialPermissionModules"
                    @change-view="handleViewChange"
                />

                <!-- Placeholder for unfinished views -->
                <div v-else class="space-y-6">
                    <div class="card rounded-3xl p-10 text-center">
                        <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-slate-950 text-white">
                            <Wallet />
                        </div>
                        <h3 class="text-2xl font-black">{{ labelFor(activeViewId) }}</h3>
                        <p class="mx-auto mt-2 max-w-xl text-slate-500">
                            This module is part of the E-Wallet ecosystem. In this prototype, we've prioritized the core transaction ledger and mission control.
                        </p>
                        <button @click="activeViewId = 'walletCommand'" class="mt-5 rounded-2xl bg-slate-950 px-5 py-2.5 font-black text-white">
                            Back to Mission Control
                        </button>
                    </div>
                </div>

            </section>
        </main>
    </div>
</template>
