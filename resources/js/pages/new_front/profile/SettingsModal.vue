<template>
    <div
        v-if="modelValue"
        class="fade fixed inset-0 z-[100] flex items-end justify-center bg-slate-900/40 p-2 backdrop-blur-sm sm:items-center sm:p-4"
    >
        <div class="card flex h-[92vh] w-full max-w-lg flex-col overflow-hidden border-none bg-slate-50 shadow-2xl sm:h-[85vh]">
            <!-- HEADER -->
            <div
                class="flex shrink-0 items-center justify-between p-5 text-white"
                style="background: linear-gradient(120deg, #2f9bef, #2563eb 60%, #6d5efc)"
            >
                <h3 class="text-3xl font-black">Settings</h3>
                <button
                    @click="$emit('update:modelValue', false)"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/20 transition hover:bg-white/30"
                >
                    <i data-lucide="x" class="h-6 w-6"></i>
                </button>
            </div>

            <!-- SCROLLABLE BODY -->
            <div class="custom-scroll flex-1 space-y-6 overflow-y-auto p-4 pb-10">
                <!-- PROFILE CARD -->
                <div class="relative flex items-center gap-4 overflow-hidden rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-full ring-4 ring-slate-50">
                        <img :src="user.avatar" alt="Profile" class="h-full w-full object-cover" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-xl leading-tight font-black text-slate-900">{{ user.name }}</h2>
                        <p class="truncate text-sm font-semibold text-slate-500">{{ user.handle }} &middot; {{ extra.subscriptionPlan }} plan</p>
                    </div>
                    <button @click="showEditProfile = true" class="rounded-xl px-4 py-2 text-sm font-black text-blue-600 transition hover:bg-blue-50">
                        Edit
                    </button>
                </div>

                <!-- ACCOUNT SECTION -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Account</h3>
                    <div class="divide-y divide-slate-50 overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm">
                        <SettingRow label="Personal Information" @click="showEditProfile = true" />
                        <SettingRow label="Subscription" :value="extra.subscriptionPlan" @click="openModal('subscription')" />
                        <SettingRow label="Payment Methods" @click="openModal('payment')" />
                        <SettingRow label="Advertise My Business" @click="openModal('advertise')" />
                        <SettingRow label="Verification" :value="extra.verified ? 'Verified' : 'Not verified'" @click="openModal('verification')" />
                    </div>
                </div>

                <!-- NOTIFICATIONS SECTION -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Notifications</h3>
                    <div class="flex items-center justify-between rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm">
                        <div>
                            <p class="font-black text-slate-900">Push notifications</p>
                            <p class="text-xs font-semibold text-slate-500">Master switch for all alerts</p>
                        </div>
                        <Toggle v-model="notif.push" @update:model-value="saveToDB('lk_settings_notifications', notif)" />
                    </div>
                </div>

                <!-- PRIVACY & SAFETY SECTION -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Privacy & Safety</h3>
                    <div class="divide-y divide-slate-50 overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm">
                        <SettingToggleRow
                            v-model="vis.showDiscover"
                            label="Show me on LinkUp (matching)"
                            desc="Appear in dating / swipe"
                            @update:model-value="saveToDB('lk_settings_visibility', vis)"
                        />
                        <SettingToggleRow
                            v-model="priv.showOnline"
                            label="Active status"
                            desc="Show when you are online"
                            @update:model-value="saveToDB('lk_settings_privacy', priv)"
                        />
                        <SettingToggleRow
                            v-model="priv.readReceipts"
                            label="Read receipts"
                            @update:model-value="saveToDB('lk_settings_privacy', priv)"
                        />
                        <SettingToggleRow
                            v-model="priv.shareLocation"
                            label="Share my location"
                            desc="Used for distance, events & delivery"
                            @update:model-value="saveToDB('lk_settings_privacy', priv)"
                        />
                        <SettingToggleRow
                            v-model="priv.personalizedAds"
                            label="Personalized ads"
                            @update:model-value="saveToDB('lk_settings_privacy', priv)"
                        />
                        <SettingRow label="Blocked accounts" @click="openModal('blocked')" />
                        <SettingRow label="Who can message me" :value="msg.whoCanMessage" @click="openModal('messaging')" />
                    </div>
                </div>

                <!-- IDENTITY VERIFICATION (KYC) -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Identity Verification (KYC)</h3>
                    <button
                        type="button"
                        @click="openModal('verification')"
                        class="w-full rounded-[24px] border border-slate-100 bg-white p-4 text-left shadow-sm"
                    >
                        <div class="mb-1 flex items-center justify-between">
                            <p class="font-black text-slate-900">Verify my identity</p>
                            <div class="flex items-center gap-1 text-slate-400">
                                <span class="text-sm font-bold">{{ extra.verified ? 'Verified' : 'Not verified' }}</span>
                                <i data-lucide="chevron-right" class="h-4 w-4"></i>
                            </div>
                        </div>
                        <p class="text-xs leading-relaxed font-semibold text-slate-500">
                            Required to send/withdraw money, sell, and get the verified badge. Upload a photo ID and take a selfie.
                        </p>
                    </button>
                </div>

                <!-- SECURITY -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Security</h3>
                    <div class="divide-y divide-slate-50 overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm">
                        <SettingToggleRow
                            v-model="sec.faceId"
                            label="Face ID / biometric unlock"
                            @update:model-value="saveToDB('lk_settings_security', sec)"
                        />
                        <SettingToggleRow
                            v-model="sec.twoFactor"
                            label="Two-factor authentication"
                            @update:model-value="saveToDB('lk_settings_security', sec)"
                        />
                        <SettingRow label="Change password" @click="openModal('password')" />
                        <SettingRow label="Transaction PIN" value="Not set" @click="openModal('transaction-pin')" />
                        <SettingRow label="Active sessions" :value="sessions.length + ' devices'" @click="openModal('sessions')" />
                    </div>
                </div>

                <!-- PREFERENCES -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Preferences</h3>
                    <div class="divide-y divide-slate-50 overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm">
                        <SettingRow label="Language" :value="lang" @click="openModal('language')" />
                        <SettingRow label="Currency" :value="pref.currency" @click="openModal('currency')" />
                        <SettingRow label="Region / Country" :value="pref.region" @click="openModal('region')" />
                        <SettingToggleRow v-model="pref.darkMode" label="Dark mode" @update:model-value="saveToDB('lk_settings_preferences', pref)" />
                    </div>
                </div>

                <!-- HOME & NEWS -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Home & News</h3>
                    <div class="divide-y divide-slate-50 overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm">
                        <SettingToggleRow
                            v-model="news.ticker"
                            label="Breaking news ticker"
                            desc="Show the scrolling headline strip on Home"
                            @update:model-value="saveToDB('lk_settings_home_news', news)"
                        />
                        <SettingRow label="News I want to see" :value="news.newsRegion" @click="openModal('news-region')" />
                    </div>
                </div>

                <!-- SUPPORT & LEGAL -->
                <div>
                    <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Support & Legal</h3>
                    <div class="divide-y divide-slate-50 overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm">
                        <SettingRow label="Help & Support" @click="openModal('help')" />
                        <SettingRow label="Contact Us" @click="openModal('contact')" />
                        <SettingRow label="Legal Center" @click="openModal('legal')" />
                        <SettingRow label="Terms of Service" @click="openModal('legal')" />
                        <SettingRow label="Privacy Policy" @click="openModal('legal')" />
                        <div class="flex items-center justify-between p-4">
                            <p class="font-black text-slate-900">App Version</p>
                            <span class="text-sm font-bold text-slate-400">v1.0.0</span>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="space-y-4 pt-4">
                    <button
                        @click="openModal('logout')"
                        class="flex w-full items-center justify-center gap-2 rounded-[24px] border-2 border-red-50 py-4 font-black text-red-500 shadow-sm transition hover:bg-red-50"
                    >
                        <i data-lucide="log-out" class="h-5 w-5"></i>
                        Log Out
                    </button>
                    <div class="text-center">
                        <button @click="openModal('delete-account')" class="text-sm font-black text-red-500/70 transition hover:text-red-500">
                            Delete my account
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <SubscriptionModal
            :model-value="activeModal === 'subscription'"
            :current="extra.subscriptionPlan"
            @update:model-value="closeSub"
            @updated="updateExtra"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <MessagingPrivacyModal
            :model-value="activeModal === 'messaging'"
            :current="msg.whoCanMessage"
            @update:model-value="closeSub"
            @updated="updateMessaging"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <AdvertiseBusinessModal :model-value="activeModal === 'advertise'" @update:model-value="closeSub" @close-all="closeAll" />
        <VerificationModal
            :model-value="activeModal === 'verification'"
            :verified="extra.verified"
            @update:model-value="closeSub"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <BlockedAccountsModal :model-value="activeModal === 'blocked'" @update:model-value="closeSub" @close-all="closeAll" />
        <PasswordModal :model-value="activeModal === 'password'" @update:model-value="closeSub" @toast="toastForward" @close-all="closeAll" />
        <TransactionPinModal
            ref="transactionPinModal"
            :model-value="activeModal === 'transaction-pin'"
            @update:model-value="closeSub"
        />
        <SessionsModal
            :model-value="activeModal === 'sessions'"
            :sessions="sessions"
            @update:model-value="closeSub"
            @updated="updateSessions"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <LanguageModal
            :model-value="activeModal === 'language'"
            :current="lang"
            @update:model-value="closeSub"
            @updated="updateLanguage"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <CurrencyModal
            :model-value="activeModal === 'currency'"
            :current="pref.currency"
            @update:model-value="closeSub"
            @updated="updatePreferences"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <RegionModal
            :model-value="activeModal === 'region'"
            :current="pref.region"
            @update:model-value="closeSub"
            @updated="updatePreferences"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <NewsRegionModal
            :model-value="activeModal === 'news-region'"
            :current="news.newsRegion"
            @update:model-value="closeSub"
            @updated="updateNews"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <HelpModal :model-value="activeModal === 'help'" @update:model-value="closeSub" @toast="toastForward" @close-all="closeAll" />
        <ContactModal :model-value="activeModal === 'contact'" @update:model-value="closeSub" @close-all="closeAll" />
        <LegalModal :model-value="activeModal === 'legal'" @update:model-value="closeSub" @close-all="closeAll" />
        <PaymentMethodsModal
            :model-value="activeModal === 'payment'"
            :methods="methods"
            @update:model-value="closeSub"
            @updated="updateMethods"
            @toast="toastForward"
            @open-add="openModal('add-payment')"
            @close-all="closeAll"
        />
        <AddPaymentMethodModal
            :model-value="activeModal === 'add-payment'"
            @back-to-payment="openModal('payment')"
            @updated="updateMethods"
            @toast="toastForward"
            @close-all="closeAll"
        />
        <LogoutConfirmModal :model-value="activeModal === 'logout'" @update:model-value="closeSub" @confirm="confirmLogout" />
        <DeleteAccountModal :model-value="activeModal === 'delete-account'" @update:model-value="closeSub" />

        <!-- Edit Profile Modal -->
        <EditProfileModal v-model="showEditProfile" :profileUser="user" @toast="$emit('toast', $event)" @profile-updated="onProfileUpdated" />
    </div>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import SettingRow from '@/components/new_frontend/ui/SettingRow.vue';
import SettingToggleRow from '@/components/new_frontend/ui/SettingToggleRow.vue';
import Toggle from '@/components/new_frontend/ui/Toggle.vue';
import { router } from '@inertiajs/vue3';
import { onMounted, reactive, ref, watch } from 'vue';
import EditProfileModal from './EditProfileModal.vue';
import AddPaymentMethodModal from './settings/AddPaymentMethodModal.vue';
import AdvertiseBusinessModal from './settings/AdvertiseBusinessModal.vue';
import BlockedAccountsModal from './settings/BlockedAccountsModal.vue';
import ContactModal from './settings/ContactModal.vue';
import CurrencyModal from './settings/CurrencyModal.vue';
import DeleteAccountModal from './settings/DeleteAccountModal.vue';
import HelpModal from './settings/HelpModal.vue';
import LanguageModal from './settings/LanguageModal.vue';
import LegalModal from './settings/LegalModal.vue';
import LogoutConfirmModal from './settings/LogoutConfirmModal.vue';
import MessagingPrivacyModal from './settings/MessagingPrivacyModal.vue';
import NewsRegionModal from './settings/NewsRegionModal.vue';
import PasswordModal from './settings/PasswordModal.vue';
import TransactionPinModal from './settings/TransactionPinModal.vue';
import PaymentMethodsModal from './settings/PaymentMethodsModal.vue';
import RegionModal from './settings/RegionModal.vue';
import SessionsModal from './settings/SessionsModal.vue';
import SubscriptionModal from './settings/SubscriptionModal.vue';
import VerificationModal from './settings/VerificationModal.vue';

const props = defineProps({
    modelValue: Boolean,
    profileUser: Object,
});

const emit = defineEmits(['update:modelValue', 'toast', 'profile-updated']);

const defaultSessions = [
    { id: 1, device: 'This device - Chrome', location: 'The Valley, Anguilla', current: true },
    { id: 2, device: 'iPhone 15 - Safari', location: 'Nassau, Bahamas', current: false },
];

const user = reactive({ ...props.profileUser });
const extra = reactive(DB.get('lk_settings_account_extra', { subscriptionPlan: 'Brisa', verified: false }));
const priv = reactive(DB.get('lk_settings_privacy', { showOnline: true, readReceipts: false, shareLocation: true, personalizedAds: true }));
const notif = reactive(DB.get('lk_settings_notifications', { push: false }));
const vis = reactive(DB.get('lk_settings_visibility', { showDiscover: true }));
const msg = reactive(DB.get('lk_settings_messaging', { whoCanMessage: 'Everyone' }));
const sec = reactive(DB.get('lk_settings_security', { faceId: true, twoFactor: false }));
const pref = reactive(DB.get('lk_settings_preferences', { currency: 'USD (B$)', region: 'Anguilla', darkMode: false }));
const news = reactive(DB.get('lk_settings_home_news', { ticker: true, newsRegion: 'All countries' }));
const lang = ref(DB.get('lk_settings_language', 'English'));
const methods = ref(DB.get('lk_payment_methods', [{ id: 1, brand: 'Visa', last4: '4242', expiry: '09/28', isDefault: true }]));
const sessions = ref(DB.get('lk_active_sessions', defaultSessions));

const activeModal = ref('');
const transactionPinModal = ref(null);
const showEditProfile = ref(false);

const refreshIcons = () => {
    setTimeout(() => {
        if (window.lucide) window.lucide.createIcons();
    }, 10);
};

const openModal = (name) => {
    activeModal.value = name;
    if (name === 'transaction-pin') {
        transactionPinModal.value?.open();
    }
    refreshIcons();
};

const closeSub = (open) => {
    if (!open) activeModal.value = '';
};

const closeAll = () => {
    activeModal.value = '';
    emit('update:modelValue', false);
};

const toastForward = (message) => {
    emit('toast', message);
};

const saveToDB = (key, val) => {
    DB.set(key, val);
};

const updateExtra = (updated) => {
    Object.assign(extra, updated);
};

const updateMessaging = (updated) => {
    Object.assign(msg, updated);
};

const updatePreferences = (updated) => {
    Object.assign(pref, updated);
};

const updateNews = (updated) => {
    Object.assign(news, updated);
};

const updateLanguage = (updated) => {
    lang.value = updated;
};

const updateMethods = (updated) => {
    methods.value = updated;
};

const updateSessions = (updated) => {
    sessions.value = updated;
};

const onProfileUpdated = (updatedData) => {
    Object.assign(user, updatedData);
    emit('profile-updated', updatedData);
};

const confirmLogout = () => {
    router.post(
        '/new_frontend/logout',
        {},
        {
            onStart: () => {
                activeModal.value = '';
            },
            onSuccess: () => {
                emit('update:modelValue', false);
                emit('toast', 'Logged out successfully');
            },
            onError: () => {
                emit('toast', 'Unable to log out. Please try again.');
            },
        },
    );
};

onMounted(refreshIcons);

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) refreshIcons();
        else activeModal.value = '';
    },
);

watch(
    () => props.profileUser,
    (nextUser) => {
        Object.assign(user, nextUser || {});
    },
);
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    width: 5px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.card {
    border-radius: 32px;
}
@media (max-width: 640px) {
    .card {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
}
</style>
