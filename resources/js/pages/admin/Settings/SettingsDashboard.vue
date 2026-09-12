<script setup lang="ts">
import { ref } from 'vue';
import AppSettings from './AppSettings.vue';
import AdsSettings from './AdsSettings.vue';
import SMTPSettings from './SMTPSettings.vue';
import AISettings from './AISettings.vue';
import WalletFundingAPI from './WalletFundingAPI.vue';
import FeatureFlags from './FeatureFlags.vue';
import SystemSettings from './SystemSettings.vue';
import AppLogoCMS from './AppLogoCMS.vue';
import LegalPagesCMS from './LegalPagesCMS.vue';
import DriverPoliciesCMS from './DriverPolicies.vue';

const props = defineProps<{
    activeView?: string;
}>();

const currentView = ref(props.activeView || 'settingsAppCommand');
</script>

<template>
    <div class="p-5 lg:p-8 space-y-6">
        <AppSettings v-if="currentView === 'settingsAppCommand'" />
        <AdsSettings v-if="currentView === 'settingsAdsCommand'" />
        <SMTPSettings v-if="currentView === 'settingsSmtpCommand'" />
        <AISettings v-if="currentView === 'settingsAICommand'" />
        <WalletFundingAPI v-if="currentView === 'settingsWalletFundingCommand'" />
        <FeatureFlags v-if="currentView === 'settingsFeatureFlagsCommand'" />
        <SystemSettings v-if="currentView === 'settingsSystemCommand'" />
        <AppLogoCMS v-if="currentView === 'logoCmsView'" />
        <LegalPagesCMS v-if="currentView === 'legalCmsView'" />
        <DriverPoliciesCMS v-if="currentView === 'driverPoliciesView'" />

        <div v-if="currentView === '__placeholder'" class="view">
            <div class="card rounded-3xl p-10 text-center">
                <div class="mx-auto h-14 w-14 rounded-2xl bg-slate-950 grid place-items-center text-white mb-4">
                    <i data-lucide="settings"></i>
                </div>
                <h3 class="text-2xl font-black">This screen</h3>
                <p class="text-slate-500 mt-2 max-w-xl mx-auto">This is a placeholder for settings views.</p>
                <button @click="currentView = 'settingsAppCommand'" class="mt-5 rounded-2xl bg-slate-950 text-white px-5 py-2.5 font-black">Back to App Settings</button>
            </div>
        </div>
    </div>
</template>
