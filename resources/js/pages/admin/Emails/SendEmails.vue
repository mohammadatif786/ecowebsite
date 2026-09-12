<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Mail, Save, Send } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type TargetType = 'All' | 'Country' | 'Region' | 'State' | 'City' | 'Caribbean Island' | 'User Segment';

const emailAudienceMap: Record<string, any> = {
    All: 142850,
    Country: { Bahamas: 38500, Jamaica: 29150, 'Trinidad and Tobago': 18400, Barbados: 9400, Guyana: 7800, 'United States': 31100, Canada: 8500 },
    Region: { Caribbean: 108000, 'United States': 31100, Canada: 8500, 'Central America': 12600, 'South America': 22100 },
    State: { Florida: 14400, 'New York': 7300, Georgia: 4100, Texas: 3600, California: 5200 },
    City: { Nassau: 24000, Freeport: 6200, Kingston: 18500, 'Port of Spain': 9800, Miami: 8900, 'New York City': 6100 },
    'Caribbean Island': { 'Antigua and Barbuda': 2800, Anguilla: 900, Aruba: 2100, Bahamas: 38500, Barbados: 9400, Belize: 2700, Bonaire: 600, 'Cayman Islands': 1900, Cuba: 4100, Curacao: 1500, Dominica: 1200, 'Dominican Republic': 7300, Grenada: 1800, Guadeloupe: 1100, Haiti: 6900, Jamaica: 29150, 'Saint Lucia': 2200, 'St. Kitts and Nevis': 1000, 'St. Vincent and the Grenadines': 1300, 'Trinidad and Tobago': 18400, 'Turks and Caicos': 1400 },
    'User Segment': { 'All Users': 142850, 'Verified Users': 118500, 'Premium Subscribers': 18650, 'Event Buyers': 54200, 'Wallet Users': 76500, 'Marketplace Buyers': 22100, 'Inactive Users': 9700, 'KYC Pending': 5200 },
};

const emailTargets: Record<TargetType, string[]> = {
    All: ['All Customers'],
    Country: ['Bahamas', 'Jamaica', 'Trinidad and Tobago', 'Barbados', 'Guyana', 'United States', 'Canada'],
    Region: ['Caribbean', 'United States', 'Canada', 'Central America', 'South America'],
    State: ['Florida', 'New York', 'Georgia', 'Texas', 'California'],
    City: ['Nassau', 'Freeport', 'Kingston', 'Port of Spain', 'Miami', 'New York City'],
    'Caribbean Island': ['Antigua and Barbuda', 'Anguilla', 'Aruba', 'Bahamas', 'Barbados', 'Belize', 'Bonaire', 'Cayman Islands', 'Cuba', 'Curacao', 'Dominica', 'Dominican Republic', 'Grenada', 'Guadeloupe', 'Haiti', 'Jamaica', 'Saint Lucia', 'St. Kitts and Nevis', 'St. Vincent and the Grenadines', 'Trinidad and Tobago', 'Turks and Caicos'],
    'User Segment': ['All Users', 'Verified Users', 'Premium Subscribers', 'Event Buyers', 'Wallet Users', 'Marketplace Buyers', 'Inactive Users', 'KYC Pending'],
};

const templateCopy: Record<string, { name: string; subject: string; body: string }> = {
    welcome: {
        name: 'Welcome to LinkUp',
        subject: 'Welcome to LinkUp - your Caribbean connection starts here',
        body: 'We are excited to have you inside LinkUp. You can discover events, connect with people, use your wallet, shop the marketplace, and stay connected across the Caribbean and beyond.',
    },
    event: {
        name: 'Event Promotion',
        subject: 'This weekend on LinkUp: tickets are moving fast',
        body: 'New events are now live in your area. Open LinkUp to view tickets, VIP options, cookouts, spa experiences, and more.',
    },
    wallet: {
        name: 'Wallet Feature Notice',
        subject: 'Your LinkUp Wallet is ready for transfers and payments',
        body: 'Your LinkUp Wallet helps you send money, pay merchants, buy tickets, and manage transactions securely.',
    },
    marketplace: {
        name: 'Marketplace Sale',
        subject: 'New Caribbean products just landed in Marketplace',
        body: 'Browse products from Caribbean sellers, local stores, and creators in the LinkUp Marketplace.',
    },
    security: {
        name: 'Security Notice',
        subject: 'Important security update for your LinkUp account',
        body: 'We are improving account protection. Please keep your profile and KYC information updated for continued access.',
    },
};

const campaigns = ref([
    ['Bahamas Event Buyers', 'Event Promotion Blast', 'Bahamas', '18,420', 'Sent', '44.6%'],
    ['Wallet Users - Caribbean', 'Wallet Feature Notice', 'Caribbean', '76,500', 'Sent', '39.9%'],
    ['Marketplace Buyers', 'Marketplace Sale', 'United States', '12,300', 'Draft', '-'],
    ['KYC Pending Reminder', 'Security Notice', 'All Regions', '5,200', 'Scheduled', '-'],
]);

const deliveryLog = ref([
    ['10:42 AM', 'Event blast queued', 'Bahamas', '18,420'],
    ['10:38 AM', 'Security notice delivered', 'All Regions', '5,200'],
    ['09:55 AM', 'Wallet update opened', 'Caribbean', '30,114'],
    ['09:10 AM', 'Marketplace draft saved', 'United States', '12,300'],
]);

const selectedTemplate = ref('blank');
const sendMode = ref('Bulk Campaign');
const targetType = ref<TargetType>('All');
const targetValue = ref('All Customers');
const segment = ref('All Users');
const toAddress = ref('');
const templateName = ref('');
const subject = ref('');
const body = ref('');

const targetOptions = computed(() => emailTargets[targetType.value] || ['All Customers']);

const estimatedAudience = computed(() => {
    let base = 142850;

    if (targetType.value === 'All') base = 142850;
    else if (targetType.value === 'User Segment') base = emailAudienceMap['User Segment'][targetValue.value] || 142850;
    else base = emailAudienceMap[targetType.value]?.[targetValue.value] || 142850;

    if (targetType.value !== 'User Segment' && segment.value !== 'All Users') base = Math.round(base * 0.62);

    if (sendMode.value !== 'Bulk Campaign' && toAddress.value) return 1;

    return base;
});

const targetingSummary = computed(() => [
    ['Send By', targetType.value],
    ['Target', targetValue.value],
    ['Segment', segment.value],
    ['Estimated Recipients', estimatedAudience.value.toLocaleString()],
    ['Delivery Channel', 'Email'],
    ['Compliance', 'Unsubscribe link included'],
]);

watch(targetType, () => {
    targetValue.value = targetOptions.value[0] || 'All Customers';
});

const applyTemplate = () => {
    const selected = templateCopy[selectedTemplate.value];
    if (!selected) return;

    subject.value = selected.subject;
    body.value = selected.body;
    templateName.value = selected.name;
};

const saveEmailDraft = () => {
    campaigns.value.unshift([
        targetType.value === 'All' ? 'All Customers Draft' : `${targetValue.value} Draft`,
        templateName.value || 'Custom Email',
        targetValue.value,
        estimatedAudience.value.toLocaleString(),
        'Draft',
        '-',
    ]);
    toast.success('Email draft saved.');
};

const sendEmail = () => {
    campaigns.value.unshift([
        targetType.value === 'All' ? 'All Customers' : targetValue.value,
        templateName.value || subject.value || 'Custom Email',
        targetType.value,
        estimatedAudience.value.toLocaleString(),
        'Sent',
        '-',
    ]);
    deliveryLog.value.unshift([
        new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        'Email campaign sent',
        targetValue.value,
        estimatedAudience.value.toLocaleString(),
    ]);
    toast.success('Email campaign sent in simulation.');
};
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Compose Email</h3>
                <p class="text-slate-500">Send email campaigns to LinkUp customers by country, region, island, city, user segment, or full customer base.</p>
            </div>
            <div class="flex gap-2">
                <button class="rounded-2xl border border-slate-200 bg-white px-5 py-3 font-black" @click="saveEmailDraft">
                    <Save class="mr-2 inline h-4 w-4" /> Save Draft
                </button>
                <button class="rounded-2xl bg-lime-400 px-5 py-3 font-black text-slate-950 shadow-lg shadow-lime-100" @click="sendEmail">
                    <Send class="mr-2 inline h-4 w-4" /> Send Email
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Total Email Audience</p><h3 class="text-4xl font-black">142,850</h3><p class="mt-1 text-xs font-bold text-green-600">Verified customer emails</p></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Estimated Recipients</p><h3 class="text-4xl font-black">{{ estimatedAudience.toLocaleString() }}</h3><p class="mt-1 text-xs font-bold text-purple-600">Based on targeting</p></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Campaigns Sent</p><h3 class="text-4xl font-black">38</h3><p class="mt-1 text-xs text-slate-500">Last 30 days</p></div>
            <div class="card rounded-3xl p-5"><p class="font-bold text-slate-500">Avg Open Rate</p><h3 class="text-4xl font-black">41.8%</h3><p class="mt-1 text-xs font-bold text-sky-600">Email engagement</p></div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[1.2fr_.8fr]">
            <section class="card overflow-hidden rounded-3xl">
                <div class="flex items-center gap-3 bg-gradient-to-r from-purple-600 to-fuchsia-500 p-5 text-white">
                    <Mail class="h-6 w-6" />
                    <h3 class="text-xl font-black">Email Composer</h3>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="font-bold text-slate-600">Email Template
                            <select v-model="selectedTemplate" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" @change="applyTemplate">
                                <option value="blank">Select Email Template</option>
                                <option value="welcome">Welcome to LinkUp</option>
                                <option value="event">Event Promotion</option>
                                <option value="wallet">Wallet Feature Notice</option>
                                <option value="marketplace">Marketplace Sale</option>
                                <option value="security">Security Notice</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">Send Mode
                            <select v-model="sendMode" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option>Bulk Campaign</option><option>Single Email</option><option>Test Email</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">Send By
                            <select v-model="targetType" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option>All</option><option>Country</option><option>Region</option><option>State</option><option>City</option><option>Caribbean Island</option><option>User Segment</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">Target
                            <select v-model="targetValue" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option v-for="target in targetOptions" :key="target">{{ target }}</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">Customer Segment
                            <select v-model="segment" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option>All Users</option><option>Verified Users</option><option>Premium Subscribers</option><option>Event Buyers</option><option>Wallet Users</option><option>Marketplace Buyers</option><option>Inactive Users</option><option>KYC Pending</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">To Email Address <span class="font-normal text-slate-400">(single/test only)</span>
                            <input v-model="toAddress" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="customer@email.com" />
                        </label>
                        <label class="font-bold text-slate-600 md:col-span-2">Template Name
                            <input v-model="templateName" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Example: Bahamas Event Blast" />
                        </label>
                    </div>

                    <label class="block font-bold text-slate-600">Subject
                        <input v-model="subject" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Enter subject line" />
                    </label>

                    <label class="block font-bold text-slate-600">Email Body
                        <div class="mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <div class="flex flex-wrap gap-2 border-b border-slate-200 bg-slate-50 p-3 text-sm">
                                <button class="rounded-lg border bg-white px-3 py-1 font-black">B</button>
                                <button class="rounded-lg border bg-white px-3 py-1 italic">I</button>
                                <button class="rounded-lg border bg-white px-3 py-1 underline">U</button>
                                <button class="rounded-lg border bg-white px-3 py-1">H1</button>
                                <button class="rounded-lg border bg-white px-3 py-1">Link</button>
                                <button class="rounded-lg border bg-white px-3 py-1">Image</button>
                            </div>
                            <textarea v-model="body" class="min-h-64 w-full p-5 outline-none" placeholder="Write your email here..."></textarea>
                        </div>
                    </label>

                    <div class="flex flex-wrap justify-center gap-3 pt-2">
                        <button class="rounded-2xl border border-slate-200 bg-white px-7 py-3 font-black" @click="saveEmailDraft">Save Draft</button>
                        <button class="rounded-2xl bg-lime-400 px-7 py-3 font-black text-slate-950" @click="sendEmail">Send Email</button>
                    </div>
                </div>
            </section>

            <div class="space-y-6">
                <section class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Email Preview</h3>
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                        <div class="bg-slate-950 p-5 text-white"><p class="text-xs text-slate-300">LinkUp</p><h4 class="text-xl font-black">{{ subject || 'Your subject will appear here' }}</h4></div>
                        <div class="p-5"><p class="mb-3 text-sm text-slate-500">Dear LinkUp Member,</p><p class="whitespace-pre-wrap text-slate-700">{{ body || 'Your email message preview will appear here.' }}</p><button class="mt-5 rounded-2xl bg-purple-600 px-5 py-3 font-black text-white">Open LinkUp</button></div>
                    </div>
                </section>
                <section class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Targeting Summary</h3>
                    <div class="space-y-3">
                        <div v-for="row in targetingSummary" :key="row[0]" class="flex justify-between rounded-2xl bg-slate-50 p-3"><b>{{ row[0] }}</b><span>{{ row[1] }}</span></div>
                    </div>
                </section>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <section class="card rounded-3xl p-6"><h3 class="mb-4 text-xl font-black">Recent Email Campaigns</h3><div class="space-y-3"><div v-for="campaign in campaigns" :key="campaign.join('-')" class="flex justify-between gap-3 rounded-2xl bg-slate-50 p-4"><div><b>{{ campaign[0] }}</b><p class="text-sm text-slate-500">{{ campaign[1] }} - {{ campaign[2] }} - {{ campaign[3] }} recipients</p></div><span class="font-black">{{ campaign[4] }}</span></div></div></section>
            <section class="card rounded-3xl p-6"><h3 class="mb-4 text-xl font-black">Email Delivery Log</h3><div class="space-y-3"><div v-for="log in deliveryLog" :key="log.join('-')" class="flex justify-between rounded-2xl border border-slate-100 bg-white p-4"><div><b>{{ log[1] }}</b><p class="text-sm text-slate-500">{{ log[0] }} - {{ log[2] }}</p></div><span>{{ log[3] }}</span></div></div></section>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
