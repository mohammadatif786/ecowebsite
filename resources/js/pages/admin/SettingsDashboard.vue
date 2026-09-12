<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

// Import Settings Components
import AppSettings from './Settings/AppSettings.vue';
import AdsSettings from './Settings/AdsSettings.vue';
import SMTPSettings from './Settings/SMTPSettings.vue';
import AISettings from './Settings/AISettings.vue';
import WalletFundingAPI from './Settings/WalletFundingAPI.vue';
import FeatureFlags from './Settings/FeatureFlags.vue';
import SystemSettings from './Settings/SystemSettings.vue';
import AppLogoCMS from './Settings/AppLogoCMS.vue';
import LegalPagesCMS from './Settings/LegalPagesCMS.vue';
import DriverPolicies from './Settings/DriverPolicies.vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    activeView?: string;
    initialAdsSettings?: Record<string, any>;
    initialSmtpSettings?: Record<string, any>;
}>();

const activeSidebarId = ref(props.activeView || 'settingsAppCommand');

const handleViewChange = (id: string) => {
    activeSidebarId.value = id;
};

onMounted(() => {
    const savedView = localStorage.getItem('settings_active_view');
    if (savedView && !props.activeView) {
        activeSidebarId.value = savedView;
    }
});
</script>

<template>
    <Head title="LinkUp — Settings" />

    <div class="min-h-screen flex bg-slate-50">
        <NewAppSidebar :active-id="activeSidebarId" @view-changed="handleViewChange" />

        <main class="flex-1">
            <NewAppHeader
                title="Settings & CMS"
                :help-context="activeSidebarId"
                description="Manage global app configurations, legal documents, and system preferences."
            />

            <section class="p-5 lg:p-8">
                <AppSettings v-if="activeSidebarId === 'settingsAppCommand'" />
                <AdsSettings v-if="activeSidebarId === 'settingsAdsCommand'" :initial-ads-settings="initialAdsSettings" />
                <SMTPSettings v-if="activeSidebarId === 'settingsSmtpCommand'" :initial-smtp-settings="initialSmtpSettings" />
                <AISettings v-if="activeSidebarId === 'settingsAICommand'" />
                <WalletFundingAPI v-if="activeSidebarId === 'settingsWalletFundingCommand'" />
                <FeatureFlags v-if="activeSidebarId === 'settingsFeatureFlagsCommand'" />
                <SystemSettings v-if="activeSidebarId === 'settingsSystemCommand'" />
                <AppLogoCMS v-if="activeSidebarId === 'logoCmsView'" />
                <LegalPagesCMS v-if="activeSidebarId === 'legalCmsView'" />
                <DriverPolicies v-if="activeSidebarId === 'driverPoliciesView'" />

                <!-- Placeholder for other views if needed -->
                <div v-if="!['settingsAppCommand', 'settingsAdsCommand', 'settingsSmtpCommand', 'settingsAICommand', 'settingsWalletFundingCommand', 'settingsFeatureFlagsCommand', 'settingsSystemCommand', 'logoCmsView', 'legalCmsView', 'driverPoliciesView'].includes(activeSidebarId)" class="card rounded-3xl p-10 text-center">
                    <h3 class="text-2xl font-black">View: {{ activeSidebarId }}</h3>
                    <p class="text-slate-500 mt-2">This setting view is coming soon.</p>
                </div>
            </section>
        </main>
    </div>
</template>
