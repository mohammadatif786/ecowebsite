<template>
    <div class="fade mx-auto max-w-2xl pb-10">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between px-2">
            <h1 class="text-3xl font-black text-slate-900">Settings</h1>
            <button @click="goBack" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200/50 transition hover:bg-slate-200">
                <i data-lucide="x" class="h-5 w-5 text-slate-600"></i>
            </button>
        </div>

        <!-- Profile Card -->
        <div class="card relative mb-6 flex items-center gap-4 overflow-hidden p-4">
            <div class="h-16 w-16 overflow-hidden rounded-full ring-4 ring-slate-50">
                <img :src="user.avatar" alt="Profile" class="h-full w-full object-cover" />
            </div>
            <div class="flex-1">
                <h2 class="text-xl leading-tight font-black text-slate-900">{{ user.name }}</h2>
                <p class="text-sm font-semibold text-slate-500">@{{ user.username }} · {{ user.plan }}</p>
            </div>
            <button class="text-lkblue2 px-4 py-2 text-sm font-black">Edit</button>
        </div>

        <!-- ACCOUNT SECTION -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Account</h3>
            <div class="card divide-y divide-slate-50 overflow-hidden">
                <SettingRow label="Personal Information" />
                <SettingRow label="Subscription" value="Brisa" />
                <SettingRow label="Payment Methods" />
                <SettingRow label="Advertise My Business" />
                <SettingRow label="Verification" value="Not verified" />
            </div>
        </div>

        <!-- NOTIFICATIONS SECTION -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Notifications</h3>
            <div class="card flex items-center justify-between p-4">
                <div>
                    <p class="font-black text-slate-900">Push notifications</p>
                    <p class="text-xs font-semibold text-slate-500">Master switch for all alerts</p>
                </div>
                <Toggle v-model="settings.push_notifications" />
            </div>
        </div>

        <!-- PRIVACY & SAFETY SECTION -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Privacy & Safety</h3>
            <div class="card divide-y divide-slate-50 overflow-hidden">
                <SettingToggleRow v-model="settings.show_on_linkup" label="Show me on LinkUp (matching)" desc="Appear in dating / swipe" />
                <SettingToggleRow v-model="settings.active_status" label="Active status" desc="Show when you are online" />
                <SettingToggleRow v-model="settings.read_receipts" label="Read receipts" />
                <SettingToggleRow v-model="settings.share_location" label="Share my location" desc="Used for distance, events & delivery" />
                <SettingToggleRow v-model="settings.personalized_ads" label="Personalized ads" />
                <SettingRow label="Blocked accounts" />
                <SettingRow label="Who can message me" value="Everyone" />
            </div>
        </div>

        <!-- IDENTITY VERIFICATION (KYC) -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Identity Verification (KYC)</h3>
            <div class="card p-4">
                <div class="mb-1 flex items-center justify-between">
                    <p class="font-black text-slate-900">Verify my identity</p>
                    <div class="flex items-center gap-1 text-slate-400">
                        <span class="text-sm font-bold">Not verified</span>
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </div>
                </div>
                <p class="text-xs leading-relaxed font-semibold text-slate-500">
                    Requiredasdfad to send/withdraw money, sell, and get the verified badge. Upload a photo ID and take a selfie.
                </p>
            </div>
        </div>

        <!-- SECURITY -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Security</h3>
            <div class="card divide-y divide-slate-50 overflow-hidden">
                <SettingToggleRow v-model="settings.face_id" label="Face ID / biometric unlock" />
                <SettingToggleRow v-model="settings.two_factor" label="Two-factor authentication" />
                <SettingRow label="Change password" />
                <SettingRow label="Transaction PIN" />
                <SettingRow label="Active sessions" value="2 devices" />
            </div>
        </div>

        <!-- PREFERENCES -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Preferences</h3>
            <div class="card divide-y divide-slate-50 overflow-hidden">
                <SettingRow label="Language" value="English" />
                <SettingRow label="Currency" value="USD (B$)" />
                <SettingRow label="Region / Country" value="Anguilla" />
                <SettingToggleRow v-model="settings.dark_mode" label="Dark mode" />
            </div>
        </div>

        <!-- HOME & NEWS -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Home & News</h3>
            <div class="card divide-y divide-slate-50 overflow-hidden">
                <SettingToggleRow v-model="settings.breaking_news" label="Breaking news ticker" desc="Show the scrolling headline strip on Home" />
                <SettingRow label="News I want to see" value="All countries" />
            </div>
        </div>

        <!-- SUPPORT & LEGAL -->
        <div class="mb-6">
            <h3 class="mb-2 px-4 text-[11px] font-black tracking-wider text-slate-400 uppercase">Support & Legal</h3>
            <div class="card divide-y divide-slate-50 overflow-hidden">
                <SettingRow label="Help & Support" />
                <SettingRow label="Contact Us" />
                <SettingRow label="Legal Center" />
                <SettingRow label="Terms of Service" />
                <SettingRow label="Privacy Policy" />
                <div class="flex items-center justify-between p-4">
                    <p class="font-black text-slate-900">App Version</p>
                    <span class="text-sm font-bold text-slate-400">v1.0.0</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 space-y-4 px-4 text-center">
            <button
                @click="logout"
                class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-red-50 py-3.5 font-black text-red-500 transition hover:bg-red-50"
            >
                <i data-lucide="log-out" class="h-5 w-5"></i>
                Log Out
            </button>
            <button @click="confirmDelete" class="text-sm font-black text-red-500/70 transition hover:text-red-500">Delete my account</button>
        </div>
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, reactive } from 'vue';
import SettingRow from '../../components/new_frontend/ui/SettingRow.vue';
import SettingToggleRow from '../../components/new_frontend/ui/SettingToggleRow.vue';
import Toggle from '../../components/new_frontend/ui/Toggle.vue';
import MainLayout from '../../layouts/new_front_layout/MainLayout.vue';

defineOptions({ layout: MainLayout });

const page = usePage();
const authUser = page.props.auth?.user;

const user = reactive({
    name: authUser?.name || 'Cassius',
    username: authUser?.username || 'cassius',
    avatar: authUser?.avatar || '/images/default-avatar.png',
    plan: authUser?.plan || 'Brisa plan',
});

const settings = reactive({
    push_notifications: false,
    show_on_linkup: true,
    active_status: true,
    read_receipts: false,
    share_location: true,
    personalized_ads: true,
    face_id: true,
    two_factor: false,
    dark_mode: false,
    breaking_news: true,
});

const goBack = () => {
    window.history.back();
};

const logout = () => {
    router.post(route('logout'));
};

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        // router.delete(route('profile.destroy'));
    }
};

onMounted(() => {
    if (window.lucide) window.lucide.createIcons();
});
</script>

<style scoped>
.card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 24px;
    box-shadow: 0 1px 2px rgb(226 232 240 / 0.5);
}
</style>
