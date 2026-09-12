<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import {
    BadgeDollarSign,
    Bell,
    Globe2,
    LayoutTemplate,
    Mail,
    MessageSquare,
    Send,
    Shuffle,
    Siren,
    Smartphone,
    Sparkles,
    Users,
    Wand2,
    X,
} from 'lucide-vue-next';

type CountryGroup = 'caribbean' | 'latam' | 'us';
type Country = { key: string; label: string; flag: string; group: CountryGroup };
type AdFormat = 'image' | 'video' | 'audio';
type SponsorAd = {
    id: string;
    sponsor: string;
    title: string;
    body: string;
    format: AdFormat;
    media: string;
    cta: string;
    value: number;
    target: string;
    targetType: 'story' | 'region' | 'category' | 'all';
    status: 'Active' | 'Scheduled' | 'Paused';
    impressions: number;
    clicks: number;
    emailSubject?: string;
    emailBody?: string;
};
type Priority = 'Normal' | 'Important' | 'Emergency';
type SendBy = 'All' | 'Region' | 'Country' | 'Caribbean Island' | 'US State' | 'City' | 'Diaspora Hub' | 'Specific User';
type HistoryEntry = {
    time: string;
    title: string;
    audience: string;
    channels: string;
    reach: number;
    priority: Priority;
    status: 'Sent' | 'Scheduled';
    sponsor: string;
    value: number;
    emailCreative: string;
};

const STORAGE_HISTORY = 'linkupPushHistory';

const countries: Country[] = [
    { key: 'Anguilla', label: 'Anguilla', flag: '🇦🇮', group: 'caribbean' },
    { key: 'Antigua & Barbuda', label: 'Antigua & Barbuda', flag: '🇦🇬', group: 'caribbean' },
    { key: 'Aruba', label: 'Aruba', flag: '🇦🇼', group: 'caribbean' },
    { key: 'Bahamas', label: 'Bahamas', flag: '🇧🇸', group: 'caribbean' },
    { key: 'Barbados', label: 'Barbados', flag: '🇧🇧', group: 'caribbean' },
    { key: 'Belize', label: 'Belize', flag: '🇧🇿', group: 'caribbean' },
    { key: 'Bermuda', label: 'Bermuda', flag: '🇧🇲', group: 'caribbean' },
    { key: 'British Virgin Islands', label: 'British Virgin Is.', flag: '🇻🇬', group: 'caribbean' },
    { key: 'Cayman Islands', label: 'Cayman Islands', flag: '🇰🇾', group: 'caribbean' },
    { key: 'Cuba', label: 'Cuba', flag: '🇨🇺', group: 'caribbean' },
    { key: 'Curaçao', label: 'Curaçao', flag: '🇨🇼', group: 'caribbean' },
    { key: 'Dominica', label: 'Dominica', flag: '🇩🇲', group: 'caribbean' },
    { key: 'Dominican Republic', label: 'Dominican Rep.', flag: '🇩🇴', group: 'caribbean' },
    { key: 'Grenada', label: 'Grenada', flag: '🇬🇩', group: 'caribbean' },
    { key: 'Guadeloupe', label: 'Guadeloupe', flag: '🇬🇵', group: 'caribbean' },
    { key: 'Guyana', label: 'Guyana', flag: '🇬🇾', group: 'caribbean' },
    { key: 'Haiti', label: 'Haiti', flag: '🇭🇹', group: 'caribbean' },
    { key: 'Jamaica', label: 'Jamaica', flag: '🇯🇲', group: 'caribbean' },
    { key: 'Martinique', label: 'Martinique', flag: '🇲🇶', group: 'caribbean' },
    { key: 'Montserrat', label: 'Montserrat', flag: '🇲🇸', group: 'caribbean' },
    { key: 'Puerto Rico', label: 'Puerto Rico', flag: '🇵🇷', group: 'caribbean' },
    { key: 'St. Kitts & Nevis', label: 'St. Kitts & Nevis', flag: '🇰🇳', group: 'caribbean' },
    { key: 'St. Lucia', label: 'St. Lucia', flag: '🇱🇨', group: 'caribbean' },
    { key: 'St. Vincent & the Grenadines', label: 'St. Vincent', flag: '🇻🇨', group: 'caribbean' },
    { key: 'Sint Maarten', label: 'Sint Maarten', flag: '🇸🇽', group: 'caribbean' },
    { key: 'Suriname', label: 'Suriname', flag: '🇸🇷', group: 'caribbean' },
    { key: 'Trinidad & Tobago', label: 'Trinidad & Tobago', flag: '🇹🇹', group: 'caribbean' },
    { key: 'Turks & Caicos', label: 'Turks & Caicos', flag: '🇹🇨', group: 'caribbean' },
    { key: 'US Virgin Islands', label: 'US Virgin Is.', flag: '🇻🇮', group: 'caribbean' },
    { key: 'Argentina', label: 'Argentina', flag: '🇦🇷', group: 'latam' },
    { key: 'Bolivia', label: 'Bolivia', flag: '🇧🇴', group: 'latam' },
    { key: 'Brazil', label: 'Brazil', flag: '🇧🇷', group: 'latam' },
    { key: 'Chile', label: 'Chile', flag: '🇨🇱', group: 'latam' },
    { key: 'Colombia', label: 'Colombia', flag: '🇨🇴', group: 'latam' },
    { key: 'Costa Rica', label: 'Costa Rica', flag: '🇨🇷', group: 'latam' },
    { key: 'Ecuador', label: 'Ecuador', flag: '🇪🇨', group: 'latam' },
    { key: 'El Salvador', label: 'El Salvador', flag: '🇸🇻', group: 'latam' },
    { key: 'French Guiana', label: 'French Guiana', flag: '🇬🇫', group: 'latam' },
    { key: 'Guatemala', label: 'Guatemala', flag: '🇬🇹', group: 'latam' },
    { key: 'Honduras', label: 'Honduras', flag: '🇭🇳', group: 'latam' },
    { key: 'Mexico', label: 'Mexico', flag: '🇲🇽', group: 'latam' },
    { key: 'Nicaragua', label: 'Nicaragua', flag: '🇳🇮', group: 'latam' },
    { key: 'Panama', label: 'Panama', flag: '🇵🇦', group: 'latam' },
    { key: 'Paraguay', label: 'Paraguay', flag: '🇵🇾', group: 'latam' },
    { key: 'Peru', label: 'Peru', flag: '🇵🇪', group: 'latam' },
    { key: 'Uruguay', label: 'Uruguay', flag: '🇺🇾', group: 'latam' },
    { key: 'Venezuela', label: 'Venezuela', flag: '🇻🇪', group: 'latam' },
    { key: 'United States', label: 'United States', flag: '🇺🇸', group: 'us' },
    { key: 'Canada', label: 'Canada', flag: '🇨🇦', group: 'us' },
    { key: 'United Kingdom', label: 'United Kingdom', flag: '🇬🇧', group: 'us' },
    { key: 'US Caribbean Diaspora', label: 'US Diaspora', flag: '🗽', group: 'us' },
];

const sponsorAds: SponsorAd[] = [
    { id: 'ad1', sponsor: 'Island Audio', title: 'Relaxing Caribbean Sounds', body: 'Let the soothing sounds of steel drums and ocean waves transport you to paradise.', format: 'audio', media: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', cta: 'Download App', value: 1800, target: 's1', targetType: 'story', status: 'Active', impressions: 184000, clicks: 4220 },
    { id: 'ad2', sponsor: 'Caribbean Travel Co.', title: 'Escape to the Islands', body: 'Book your dream Caribbean getaway with exclusive LinkUp member rates on flights and resorts.', format: 'image', media: 'https://picsum.photos/seed/adtravel/640/360', cta: 'Book Now', value: 3200, target: 'Caribbean', targetType: 'region', status: 'Active', impressions: 220000, clicks: 5880, emailSubject: 'Your Caribbean escape is calling ☀️', emailBody: 'Exclusive LinkUp member rates on flights + resorts across the islands. Book now and save.' },
    { id: 'ad3', sponsor: 'LinkUp Marketplace', title: 'Shop Local, Ship Global', body: 'Discover Caribbean sellers and products on the LinkUp Marketplace.', format: 'video', media: 'https://www.w3schools.com/html/mov_bbb.mp4', cta: 'Explore', value: 2600, target: 'Business & Economy', targetType: 'category', status: 'Active', impressions: 98000, clicks: 2110 },
    { id: 'ad4', sponsor: 'Scotiabank', title: 'Bank Smarter with Scotia', body: 'Manage your money across the Caribbean with the Scotiabank + LinkUp wallet.', format: 'image', media: 'https://picsum.photos/seed/adbank/640/360', cta: 'Learn More', value: 2500, target: 'Latin America', targetType: 'region', status: 'Active', impressions: 310000, clicks: 8420, emailSubject: 'Earn more with Scotiabank + LinkUp 💰', emailBody: 'Grow your money with high-yield savings powered by Scotiabank, right inside LinkUp. Learn more.' },
    { id: 'ad5', sponsor: 'Digicel', title: 'Stay Connected, Stay 360', body: 'Get the fastest Caribbean data plans and stream Caribbean 360 News anywhere.', format: 'image', media: 'https://picsum.photos/seed/addigicel/640/360', cta: 'Get the Plan', value: 850, target: 'all', targetType: 'all', status: 'Active', impressions: 120000, clicks: 3100 },
];

const diasporaHubs = [
    { key: 'Miami, FL', country: 'United States', users: 38000 },
    { key: 'New York, NY', country: 'United States', users: 52000 },
    { key: 'Brooklyn, NY', country: 'United States', users: 34000 },
    { key: 'Fort Lauderdale, FL', country: 'United States', users: 16000 },
    { key: 'Orlando, FL', country: 'United States', users: 17000 },
    { key: 'Atlanta, GA', country: 'United States', users: 24000 },
    { key: 'Boston, MA', country: 'United States', users: 21000 },
    { key: 'Washington, DC', country: 'United States', users: 19000 },
    { key: 'Houston, TX', country: 'United States', users: 15000 },
    { key: 'Toronto, ON', country: 'Canada', users: 41000 },
    { key: 'Montreal, QC', country: 'Canada', users: 18000 },
    { key: 'Ottawa, ON', country: 'Canada', users: 9000 },
    { key: 'London, UK', country: 'United Kingdom', users: 36000 },
    { key: 'Birmingham, UK', country: 'United Kingdom', users: 12000 },
];

const usStates = ['Florida', 'New York', 'Massachusetts', 'Georgia', 'Texas', 'New Jersey', 'Maryland', 'Connecticut', 'Pennsylvania', 'California', 'Illinois', 'North Carolina', 'Virginia', 'Washington DC'];
const diasporaCities = ['Miami', 'Fort Lauderdale', 'Orlando', 'New York', 'Brooklyn', 'Boston', 'Atlanta', 'Washington DC', 'Houston', 'Toronto', 'Montreal', 'London'];

const emergencyQuickTemplates = [
    { label: '🌀 Hurricane Warning', title: 'Hurricane Warning', message: 'A hurricane is approaching your area. Seek shelter immediately, secure your property, and follow local authority instructions. Stay safe.' },
    { label: '🌊 Flood Alert', title: 'Flood Alert', message: 'Flooding is expected in your area. Move to higher ground, avoid flooded roads, and keep emergency supplies ready.' },
    { label: '🚧 Curfew Notice', title: 'Curfew Notice', message: 'A curfew is now in effect in your area. Please remain indoors until further notice and follow official guidance.' },
    { label: '✅ All Clear', title: 'All Clear', message: 'The emergency alert has been lifted for your area. Conditions are now safe. Thank you for staying alert.' },
];

const templateLibrary: Record<string, { t: string; m: string }[]> = {
    Emergency: [
        { t: '🌀 Hurricane Warning', m: 'A hurricane is approaching your area. Seek shelter immediately, secure your property, and follow local authority instructions. Stay safe — updates on LinkUp.' },
        { t: '🌊 Flood Alert', m: 'Flooding is expected in your area. Move to higher ground, avoid flooded roads, and keep emergency supplies ready. Stay tuned to LinkUp.' },
        { t: '🌎 Earthquake Advisory', m: 'An earthquake has been reported in your region. Drop, cover, and hold on. Check on loved ones and watch for aftershocks. Updates on LinkUp.' },
        { t: '🚧 Curfew Notice', m: 'A curfew is now in effect in your area. Please remain indoors until further notice and follow official guidance.' },
        { t: '✅ All Clear', m: 'The emergency alert has been lifted for your area. Conditions are now safe. Thank you for staying alert with LinkUp.' },
    ],
    Community: [
        { t: '👋 Welcome', m: 'Welcome to LinkUp! Your all-in-one Caribbean super app for events, payments, news and more. Tap to explore what’s near you.' },
        { t: '🏛️ Town Hall', m: 'Join our community town hall this week. Your voice matters — RSVP and add your questions in the LinkUp app.' },
        { t: '📊 Quick Survey', m: 'Help us improve LinkUp! Take a 60-second survey and tell us what you want to see next.' },
    ],
    Diaspora: [
        { t: '🗽 Diaspora Greeting', m: 'To our Caribbean family abroad — stay connected to home with LinkUp. News, events and money transfers, all in one place.' },
        { t: '💸 Remittance Promo', m: 'Send money home for less. Lower fees on remittances to the Caribbean this week with the LinkUp Wallet.' },
        { t: '🎭 Home Events', m: 'Missing home? Catch live streams and events from across the Caribbean right on LinkUp.' },
    ],
    News: [
        { t: '📰 Breaking News', m: 'BREAKING: A major story is developing in your region. Read the full report now on Caribbean 360 News in LinkUp.' },
        { t: '⭐ Top Story', m: 'Today’s top story is live on Caribbean 360 News. Tap to read what everyone’s talking about.' },
    ],
    'Events & Promos': [
        { t: '🎟️ Tickets Live', m: 'Tickets are now on sale! Grab yours before they sell out — secure checkout in the LinkUp app.' },
        { t: '⏰ Event Reminder', m: 'Your event is coming up soon. Don’t forget — view your ticket and details in LinkUp.' },
        { t: '🔥 Limited Offer', m: 'Limited-time offer just for you! Open LinkUp to unlock your deal before it’s gone.' },
    ],
};

const BIG_ESTIMATES: Record<string, number> = {
    'United States': 148000, 'US Caribbean Diaspora': 96000, Jamaica: 84000, 'Trinidad & Tobago': 61000,
    Bahamas: 52000, Barbados: 33000, Haiti: 74000, 'Dominican Republic': 69000, Guyana: 41000,
    Brazil: 58000, Mexico: 47000, Colombia: 39000, 'Puerto Rico': 44000,
};

const userEstimate = (key: string) => {
    if (BIG_ESTIMATES[key] != null) return BIG_ESTIMATES[key];
    let hash = 0;
    for (let i = 0; i < key.length; i++) hash = (hash * 31 + key.charCodeAt(i)) >>> 0;
    return 4000 + (hash % 26000);
};

const num = (value: number) => Math.round(value || 0).toLocaleString();
const numK = (value: number) => (value >= 1000 ? `${(value / 1000).toFixed(value >= 100000 ? 0 : 1)}K` : num(value));

const regionUsers = (group: string) => countries.filter((c) => group === 'all' || c.group === group).reduce((sum, c) => sum + userEstimate(c.key), 0);
const totalUsers = computed(() => regionUsers('all'));

// ---- Composer state ----
const priority = ref<Priority>('Normal');
const title = ref('');
const message = ref('');
const messageRef = ref<HTMLTextAreaElement | null>(null);

const intent = ref('general');
const tone = ref('Auto');
const length = ref<'Short' | 'Standard' | 'Detailed'>('Standard');
const emojiOn = ref(true);
const aiNote = ref('');
const variations = ref<string[]>([]);

const sponsoredOn = ref(false);
const sponsorPickId = ref('');
const sponsorName = ref('');
const sponsorCta = ref('Learn More');
const sponsorLink = ref('');
const sponsorValue = ref(0);
const sponsorEmailOn = ref(false);
const sponsorEmailAvailable = ref(false);

const imageUrl = ref('');
const actionLink = ref('');

const channels = reactive({ push: true, inApp: true, sms: false, email: false });

const schedule = ref<'Send Now' | 'Schedule'>('Send Now');
const scheduledAt = ref('');

const sendBy = ref<SendBy>('All');
const selectedRegionGroups = ref<string[]>([]);
const selectedCountries = ref<string[]>([]);
const selectedState = ref('');
const cityInput = ref('');
const selectedHubs = ref<number[]>([]);
const specificUser = ref('');

const history = ref<HistoryEntry[]>([]);
const showTemplateModal = ref(false);
const templateCategory = ref('Emergency');

const countryListForMode = computed(() => (sendBy.value === 'Caribbean Island' ? countries.filter((c) => c.group === 'caribbean') : countries));

const audience = computed(() => {
    const by = sendBy.value;
    if (by === 'All') return { reach: totalUsers.value, countries: countries.length, desc: 'Everyone on LinkUp' };
    if (by === 'Region') {
        if (!selectedRegionGroups.value.length) return { reach: 0, countries: 0, desc: 'No region selected' };
        let reach = 0;
        const cset = new Set<string>();
        selectedRegionGroups.value.forEach((g) => {
            reach += regionUsers(g);
            countries.filter((c) => g === 'all' || c.group === g).forEach((c) => cset.add(c.key));
        });
        const labelMap: Record<string, string> = { caribbean: 'Caribbean', latam: 'Latin America', us: 'United States', all: 'Global' };
        return { reach, countries: cset.size, desc: selectedRegionGroups.value.map((g) => labelMap[g]).join(', ') };
    }
    if (by === 'Country' || by === 'Caribbean Island') {
        const sel = selectedCountries.value;
        const reach = sel.reduce((sum, k) => sum + userEstimate(k), 0);
        const noun = by === 'Caribbean Island' ? 'island(s)' : 'country(ies)';
        const desc = sel.length ? `${sel.length} ${noun}: ${sel.slice(0, 3).join(', ')}${sel.length > 3 ? '…' : ''}` : `No ${by === 'Caribbean Island' ? 'islands' : 'countries'} selected`;
        return { reach, countries: sel.length, desc };
    }
    if (by === 'US State') {
        const s = selectedState.value;
        return { reach: s ? Math.round(userEstimate(s) * 0.55) : 0, countries: s ? 1 : 0, desc: s ? `${s}, USA` : 'Select a state' };
    }
    if (by === 'City') {
        const s = cityInput.value.trim();
        return { reach: s ? Math.round(userEstimate(s) * 0.22) : 0, countries: s ? 1 : 0, desc: s ? `City: ${s}` : 'Enter a city' };
    }
    if (by === 'Diaspora Hub') {
        const sel = selectedHubs.value.map((i) => diasporaHubs[i]);
        const reach = sel.reduce((sum, h) => sum + h.users, 0);
        return { reach, countries: new Set(sel.map((h) => h.country)).size, desc: sel.length ? `${sel.length} hub(s): ${sel.slice(0, 3).map((h) => h.key).join(', ')}${sel.length > 3 ? '…' : ''}` : 'No hubs selected' };
    }
    if (by === 'Specific User') {
        const s = specificUser.value.trim();
        return { reach: s ? 1 : 0, countries: s ? 1 : 0, desc: s ? `User: ${s}` : 'Enter a user' };
    }
    return { reach: 0, countries: 0, desc: '' };
});

const stats = computed(() => ({
    total: totalUsers.value,
    countries: countries.length,
    sent: history.value.filter((h) => h.status === 'Sent').length,
    emergency: history.value.filter((h) => h.priority === 'Emergency').length,
    revenue: history.value.reduce((sum, h) => sum + (h.value || 0), 0),
}));

const isEmergency = computed(() => priority.value === 'Emergency');
const previewTitle = computed(() => title.value || 'Your title…');
const previewMsg = computed(() => message.value || 'Your message will appear here.');
const selectedChannelLabels = computed(() => {
    const out: string[] = [];
    if (channels.push) out.push('Push');
    if (channels.inApp) out.push('In-App');
    if (channels.sms) out.push('SMS');
    if (channels.email) out.push('Email');
    return out;
});
const selectedSponsorAd = computed(() => sponsorAds.find((ad) => ad.id === sponsorPickId.value) || null);
const sponsors = computed(() => Array.from(new Set(sponsorAds.map((ad) => ad.sponsor))));

watch(sendBy, () => {
    if (sendBy.value === 'US State' && !selectedState.value) selectedState.value = usStates[0];
});

watch(sponsorPickId, () => {
    const ad = selectedSponsorAd.value;
    if (!ad) {
        sponsorEmailAvailable.value = false;
        return;
    }
    sponsorName.value = ad.sponsor || '';
    sponsorCta.value = ad.cta || 'Learn More';
    sponsorLink.value = ad.media || '';
    sponsorValue.value = ad.value || 0;
    const hasEmail = !!(ad.emailSubject || ad.emailBody);
    sponsorEmailAvailable.value = hasEmail;
    sponsorEmailOn.value = hasEmail;
});

const setPriority = (p: Priority) => {
    priority.value = p;
};

const applyEmergencyTemplate = (t: string, m: string) => {
    title.value = t;
    message.value = m;
};

const selectAllCountries = (value: boolean) => {
    selectedCountries.value = value ? countryListForMode.value.map((c) => c.key) : [];
};

const toggleCountry = (key: string) => {
    selectedCountries.value = selectedCountries.value.includes(key)
        ? selectedCountries.value.filter((k) => k !== key)
        : [...selectedCountries.value, key];
};

const toggleRegionGroup = (group: string) => {
    selectedRegionGroups.value = selectedRegionGroups.value.includes(group)
        ? selectedRegionGroups.value.filter((g) => g !== group)
        : [...selectedRegionGroups.value, group];
};

const selectAllHubs = (value: boolean) => {
    selectedHubs.value = value ? diasporaHubs.map((_, i) => i) : [];
};

const toggleHub = (i: number) => {
    selectedHubs.value = selectedHubs.value.includes(i) ? selectedHubs.value.filter((x) => x !== i) : [...selectedHubs.value, i];
};

const pickCity = (city: string) => {
    cityInput.value = city;
};

const insertTag = (tag: string) => {
    const el = messageRef.value;
    if (!el) {
        message.value += tag;
        return;
    }
    const start = el.selectionStart ?? message.value.length;
    const end = el.selectionEnd ?? start;
    message.value = message.value.slice(0, start) + tag + message.value.slice(end);
    nextTick(() => {
        el.focus();
        const pos = start + tag.length;
        el.setSelectionRange(pos, pos);
    });
};

const pick = <T,>(arr: T[]) => arr[Math.floor(Math.random() * arr.length)];

const OPENERS: Record<string, string[]> = {
    Urgent: ['⚠️ URGENT:', '🚨 ALERT:', 'Important —', 'Heads up —'],
    Reassuring: ['', 'We’ve got you —', '💙 '],
    Friendly: ['👋 Hey LinkUp family!', 'Hi there!', 'Good news —', '✨ '],
    Formal: ['Notice:', 'Official update:', 'Please be advised:'],
    Hype: ['🎉 ', '🔥 ', '🚀 Big news —', 'You’re invited!'],
    Heartfelt: ['🗽 To our family abroad —', 'From home to you —', '💛 '],
};

const CLOSERS: Record<string, string[]> = {
    Urgent: ['Stay safe.', 'Act now.', 'Stay alert with LinkUp.'],
    Reassuring: ['We’re here for you.', 'Stay calm and stay informed.', 'LinkUp has your back.'],
    Friendly: ['Tap to learn more!', 'See you in the app!', 'Check it out on LinkUp.'],
    Formal: ['Details available in the LinkUp app.', 'Please review in-app.', 'Thank you.'],
    Hype: ['Don’t miss out!', 'Open LinkUp now!', 'Let’s go! 🙌'],
    Heartfelt: ['Stay connected to home.', 'We’re always with you.', 'LinkUp keeps you close.'],
};

const CORE: Record<string, string[]> = {
    general: ['there’s an important update{where} you’ll want to see', 'we’ve got news{where}'],
    emergency: ['{topic}{whereAffecting}. Follow local authorities, keep your phone charged, and check LinkUp for live updates', 'a safety alert{whereFor} is in effect. Take precautions and stay tuned to LinkUp for updates'],
    event: ['{topic}{whereIn} is happening — grab your spot', 'tickets{topicFor} are moving fast{whereIn}'],
    promo: ['{topic} is live{whereIn} — unlock yours', 'save big{topicOn} this week on LinkUp'],
    news: ['{topic} is developing{whereIn} — read it first on Caribbean 360', 'the latest{whereFrom} is live on Caribbean 360 News'],
    community: ['your community{whereIn} has something new — your voice matters', 'we’re building together{whereIn} and want to hear from you'],
    diaspora: ['stay connected to home{whereFrom} with news, events and money transfers in one place', 'home is closer than ever{whereIn} — catch live events and send support back home'],
    wallet: ['moving money{whereToFrom} just got easier with lower fees on the LinkUp Wallet', 'send and receive{whereAcross} instantly with the LinkUp Wallet'],
    welcome: ['welcome to LinkUp — your all-in-one Caribbean super app for events, payments and news', 'glad you’re here! Explore events, wallet and Caribbean 360 News on LinkUp'],
};

const aiGenerate = () => {
    let resolvedTone = tone.value;
    const intentVal = intent.value;
    const lengthVal = length.value;
    const emoji = emojiOn.value;
    if (isEmergency.value && resolvedTone === 'Auto') resolvedTone = 'Urgent';
    if (resolvedTone === 'Auto') {
        const map: Record<string, string> = { emergency: 'Urgent', event: 'Hype', promo: 'Hype', news: 'Formal', community: 'Friendly', diaspora: 'Heartfelt', wallet: 'Friendly', welcome: 'Friendly', general: 'Friendly' };
        resolvedTone = map[intentVal] || 'Friendly';
    }
    const aud = audience.value;
    const where = aud.desc && aud.desc !== 'Everyone on LinkUp' && !/^No |^Enter |^Select /.test(aud.desc)
        ? aud.desc.replace(/^\d+ (country\(ies\)|island\(s\)|hub\(s\)): /, '').replace(/, USA$/, '').split(',')[0]
        : '';
    const topicRaw = (title.value || message.value || '').trim().replace(/[.!]+$/, '');
    const E = (x: string) => (emoji ? x : '');

    const openers = (OPENERS[resolvedTone] || ['']).map(E);
    const closers = (CLOSERS[resolvedTone] || ['']).map((c) => (c.includes('🙌') ? c.replace('🙌', E('🙌')) : c));
    const coreTemplates = CORE[intentVal] || ['there’s an update on LinkUp'];

    const fillCore = (tpl: string) => tpl
        .replace('{topic}', topicRaw || (intentVal === 'emergency' ? 'an emergency' : intentVal === 'event' ? 'a new event' : intentVal === 'promo' ? 'a limited-time offer' : intentVal === 'news' ? 'a major story' : ''))
        .replace('{where}', where ? ` for ${where}` : '')
        .replace('{whereAffecting}', where ? ` affecting ${where}` : '')
        .replace('{whereFor}', where ? ` for ${where}` : '')
        .replace('{whereIn}', where ? ` in ${where}` : '')
        .replace('{whereFrom}', where ? ` from ${where}` : '')
        .replace('{whereToFrom}', where ? ` to/from ${where}` : '')
        .replace('{whereAcross}', where ? ` across ${where}` : '')
        .replace('{topicFor}', topicRaw ? ` for ${topicRaw}` : '')
        .replace('{topicOn}', topicRaw ? ` on ${topicRaw}` : '');

    const o = pick(openers);
    const c = pick(closers);
    const mid = fillCore(pick(coreTemplates));
    const capped = mid.charAt(0).toUpperCase() + mid.slice(1);

    let body: string;
    if (lengthVal === 'Short') {
        body = `${o ? `${o} ` : ''}${capped}.`;
    } else if (lengthVal === 'Detailed') {
        const extra = pick(['Open LinkUp for the full details and what to do next.', 'Tap in for everything you need to know.', 'We’ll keep this updated as things develop.']);
        body = `${o ? `${o} ` : ''}${capped}. ${extra} ${c}`;
    } else {
        body = `${o ? `${o} ` : ''}${capped}. ${c}`;
    }
    body = body.replace(/\s+/g, ' ').trim();
    if (body.length > 280) body = `${body.slice(0, 277)}…`;
    return { body, tone: resolvedTone, intent: intentVal };
};

const aiEnhance = () => {
    const g = aiGenerate();
    message.value = g.body;
    if (!title.value) {
        const t = (g.intent === 'emergency' ? '⚠️ ' : '') + g.body.split(/[.!]/)[0].replace(/^[^A-Za-z0-9]+/, '');
        title.value = t.slice(0, 65);
    }
    aiNote.value = `✨ ${g.tone} draft`;
    setTimeout(() => {
        aiNote.value = '';
    }, 3000);
    variations.value = [];
};

const generateVariations = () => {
    const seen = new Set<string>();
    const out: string[] = [];
    for (let i = 0; i < 12 && out.length < 3; i++) {
        const g = aiGenerate();
        if (!seen.has(g.body)) {
            seen.add(g.body);
            out.push(g.body);
        }
    }
    variations.value = out;
};

const useVariation = (body: string) => {
    message.value = body;
    variations.value = [];
};

const openTemplates = () => {
    templateCategory.value = 'Emergency';
    showTemplateModal.value = true;
};

const closeTemplates = () => {
    showTemplateModal.value = false;
};

const useTemplate = (cat: string, index: number) => {
    const item = templateLibrary[cat]?.[index];
    if (!item) return;
    title.value = item.t.replace(/^[^\w]+\s*/, '');
    message.value = item.m;
    if (cat === 'Emergency') setPriority('Emergency');
    closeTemplates();
};

const pushEmergencyMode = () => {
    setPriority('Emergency');
    channels.sms = true;
    channels.email = true;
    if (typeof window !== 'undefined') window.scrollTo(0, 0);
};

const hydrate = () => {
    if (typeof window === 'undefined') return;
    try {
        const saved = JSON.parse(window.localStorage.getItem(STORAGE_HISTORY) || 'null');
        if (Array.isArray(saved)) history.value = saved;
    } catch {}
};

watch(history, () => {
    if (typeof window !== 'undefined') window.localStorage.setItem(STORAGE_HISTORY, JSON.stringify(history.value.slice(0, 50)));
}, { deep: true });

const send = () => {
    const t = title.value.trim();
    const m = message.value.trim();
    if (!t) {
        window.alert('Please enter a title.');
        return;
    }
    if (!m) {
        window.alert('Please enter a message.');
        return;
    }
    const ch = selectedChannelLabels.value;
    if (!ch.length) {
        window.alert('Select at least one channel.');
        return;
    }
    const aud = audience.value;
    if (aud.reach <= 0) {
        window.alert('Select an audience to send to.');
        return;
    }

    const scheduled = schedule.value === 'Schedule';
    const sponsor = sponsoredOn.value ? (sponsorName.value.trim() || 'Sponsor') : '';
    const sponsorVal = sponsoredOn.value ? (Number(sponsorValue.value) || 0) : 0;
    const pickAd = sponsoredOn.value && sponsorPickId.value ? sponsorAds.find((ad) => ad.id === sponsorPickId.value) : null;
    const sendEmailCreative = Boolean(sponsoredOn.value && sponsorEmailOn.value && pickAd && (pickAd.emailSubject || pickAd.emailBody));

    if (isEmergency.value && !window.confirm(`🚨 Send EMERGENCY alert to ${numK(aud.reach)} users (${aud.desc}) via ${ch.join(', ')}?`)) return;

    const chOut = [...ch];
    if (sendEmailCreative && !chOut.includes('Email')) chOut.push('Email');

    history.value.unshift({
        time: new Date().toLocaleString(),
        title: t,
        audience: aud.desc,
        channels: chOut.join(', '),
        reach: aud.reach,
        priority: priority.value,
        status: scheduled ? 'Scheduled' : 'Sent',
        sponsor,
        value: sponsorVal,
        emailCreative: sendEmailCreative ? (pickAd?.emailSubject || 'Email ad') : '',
    });
    history.value = history.value.slice(0, 50);

    window.alert(
        `${isEmergency.value ? '🚨 Emergency alert ' : '✅ Notification '}${scheduled ? 'scheduled' : 'sent'} to ${numK(aud.reach)} users.\n\n`
        + `Audience: ${aud.desc}\nChannels: ${ch.join(', ')}`
        + (sponsor ? `\nSponsored by ${sponsor}${sponsorVal ? ` ($${sponsorVal.toLocaleString()})` : ''}` : '')
        + (sendEmailCreative ? `\n✉️ Email creative also sent: "${pickAd?.emailSubject}"` : ''),
    );

    title.value = '';
    message.value = '';
};

const priorityBtnClass = (p: Priority) => {
    if (priority.value !== p) return 'text-slate-600';
    return p === 'Emergency' ? 'bg-rose-600 text-white' : 'bg-purple-600 text-white';
};

onMounted(hydrate);
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-purple-600">Push Notification Center</h3>
                <p class="text-slate-500">Reach everyone on LinkUp — by region, country, Caribbean island, state, city, or individual. Built for announcements and emergency alerts.</p>
            </div>
            <button class="flex items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 font-black text-white shadow-lg shadow-rose-200" @click="pushEmergencyMode">
                <Siren class="h-5 w-5" /> Emergency Alert
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-purple-600"><Users class="h-5 w-5" /></div>
                <div><h3 class="text-2xl font-black">{{ numK(stats.total) }}</h3><p class="text-sm text-slate-500">Reachable Users</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-100 text-sky-600"><Globe2 class="h-5 w-5" /></div>
                <div><h3 class="text-2xl font-black">{{ num(stats.countries) }}</h3><p class="text-sm text-slate-500">Countries Covered</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-green-600"><Send class="h-5 w-5" /></div>
                <div><h3 class="text-2xl font-black">{{ num(stats.sent) }}</h3><p class="text-sm text-slate-500">Sent (30d)</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-100 text-rose-600"><Siren class="h-5 w-5" /></div>
                <div><h3 class="text-2xl font-black">{{ num(stats.emergency) }}</h3><p class="text-sm text-slate-500">Emergency Alerts</p></div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-100 text-amber-600"><BadgeDollarSign class="h-5 w-5" /></div>
                <div><h3 class="text-2xl font-black">${{ num(stats.revenue) }}</h3><p class="text-sm text-slate-500">Sponsored Revenue</p></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h4 class="text-xl font-black">Compose</h4>
                        <div class="flex gap-1 rounded-2xl bg-slate-100 p-1">
                            <button class="rounded-xl px-3 py-1.5 text-sm font-black" :class="priorityBtnClass('Normal')" @click="setPriority('Normal')">Normal</button>
                            <button class="rounded-xl px-3 py-1.5 text-sm font-black" :class="priorityBtnClass('Important')" @click="setPriority('Important')">Important</button>
                            <button class="rounded-xl px-3 py-1.5 text-sm font-black" :class="priorityBtnClass('Emergency')" @click="setPriority('Emergency')">🚨 Emergency</button>
                        </div>
                    </div>

                    <div v-if="isEmergency" class="mb-4 flex flex-wrap gap-2">
                        <button
                            v-for="tpl in emergencyQuickTemplates"
                            :key="tpl.title"
                            class="rounded-full bg-rose-50 px-3 py-1.5 text-sm font-black text-rose-700"
                            @click="applyEmergencyTemplate(tpl.title, tpl.message)"
                        >
                            {{ tpl.label }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Title</span>
                            <input v-model="title" maxlength="65" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="Enter title" />
                        </label>

                        <div>
                            <span class="text-sm font-black text-slate-600">Message</span>
                            <div class="mt-1 mb-2 rounded-2xl border border-fuchsia-200 bg-fuchsia-50/50 p-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-black text-fuchsia-700"><Sparkles class="h-4 w-4" /> AI Message Studio</div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <select v-model="intent" title="Purpose" class="rounded-xl border border-slate-200 px-2 py-1.5 text-xs font-bold">
                                        <option value="general">📣 General Update</option>
                                        <option value="emergency">🚨 Emergency / Safety</option>
                                        <option value="event">🎟️ Event</option>
                                        <option value="promo">🔥 Promo / Offer</option>
                                        <option value="news">📰 News Alert</option>
                                        <option value="community">🤝 Community</option>
                                        <option value="diaspora">🗽 Diaspora</option>
                                        <option value="wallet">💸 Wallet / Remittance</option>
                                        <option value="welcome">👋 Welcome</option>
                                    </select>
                                    <select v-model="tone" title="Tone" class="rounded-xl border border-slate-200 px-2 py-1.5 text-xs font-bold">
                                        <option>Auto</option>
                                        <option>Urgent</option>
                                        <option>Reassuring</option>
                                        <option>Friendly</option>
                                        <option>Formal</option>
                                        <option>Hype</option>
                                        <option>Heartfelt</option>
                                    </select>
                                    <select v-model="length" title="Length" class="rounded-xl border border-slate-200 px-2 py-1.5 text-xs font-bold">
                                        <option>Short</option>
                                        <option>Standard</option>
                                        <option>Detailed</option>
                                    </select>
                                    <label class="flex items-center gap-1 text-xs font-bold text-slate-600">
                                        <input v-model="emojiOn" type="checkbox" class="h-3.5 w-3.5 accent-fuchsia-600" /> Emoji
                                    </label>
                                    <button class="flex items-center gap-1 rounded-xl bg-linear-to-r from-fuchsia-600 to-purple-600 px-3 py-1.5 text-xs font-black text-white" @click="aiEnhance">
                                        <Wand2 class="h-3.5 w-3.5" /> Generate
                                    </button>
                                    <button class="flex items-center gap-1 rounded-xl border border-fuchsia-300 bg-white px-3 py-1.5 text-xs font-black text-fuchsia-700" @click="generateVariations">
                                        <Shuffle class="h-3.5 w-3.5" /> 3 Variations
                                    </button>
                                    <button class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-black" @click="openTemplates">
                                        <LayoutTemplate class="h-3.5 w-3.5" /> Templates
                                    </button>
                                </div>
                                <div v-if="variations.length" class="mt-2 space-y-2">
                                    <div v-for="body in variations" :key="body" class="flex items-start justify-between gap-2 rounded-xl border border-slate-200 bg-white p-2.5">
                                        <p class="flex-1 text-xs text-slate-600">{{ body }}</p>
                                        <button class="shrink-0 rounded-lg bg-fuchsia-600 px-2.5 py-1 text-xs font-black text-white" @click="useVariation(body)">Use</button>
                                    </div>
                                </div>
                            </div>
                            <textarea
                                ref="messageRef"
                                v-model="message"
                                rows="4"
                                maxlength="280"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3"
                                placeholder="Enter message — or use AI Message Studio to generate / strengthen"
                            ></textarea>
                            <div class="mt-1 flex items-center justify-between">
                                <div class="flex flex-wrap gap-1">
                                    <span class="mr-1 text-xs font-bold text-slate-400">Insert:</span>
                                    <button v-for="tag in ['{first_name}', '{country}', '{city}', '{date}']" :key="tag" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold" @click="insertTag(tag)">{{ tag }}</button>
                                </div>
                                <p class="text-xs text-slate-400"><span>{{ message.length }}</span>/280 <span class="font-bold text-fuchsia-600">{{ aiNote }}</span></p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4">
                            <label class="flex items-center justify-between">
                                <span class="flex items-center gap-2 font-black text-amber-800"><BadgeDollarSign class="h-4 w-4" /> Sponsored Push</span>
                                <input v-model="sponsoredOn" type="checkbox" class="h-5 w-5 accent-amber-500" />
                            </label>
                            <div v-if="sponsoredOn" class="mt-3">
                                <label class="mb-3 block">
                                    <span class="text-xs font-black text-slate-600">Pick from Ad Library <span class="font-normal text-slate-400">(shared with Email Sponsors, News &amp; Ad Manager)</span></span>
                                    <select v-model="sponsorPickId" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 font-bold">
                                        <option value="">— Custom (enter manually) —</option>
                                        <option v-for="ad in sponsorAds" :key="ad.id" :value="ad.id">{{ ad.sponsor }} — {{ ad.title }}{{ ad.emailSubject ? ' ✉️' : '' }}</option>
                                    </select>
                                </label>
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <label class="block">
                                        <span class="text-xs font-black text-slate-600">Sponsor</span>
                                        <input v-model="sponsorName" list="pushSponsorList" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2" placeholder="Sponsor name" />
                                        <datalist id="pushSponsorList">
                                            <option v-for="s in sponsors" :key="s" :value="s" />
                                        </datalist>
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-black text-slate-600">CTA Button</span>
                                        <input v-model="sponsorCta" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2" placeholder="Learn More" />
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-black text-slate-600">Link</span>
                                        <input v-model="sponsorLink" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2" placeholder="https://" />
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-black text-slate-600">Ad Value ($)</span>
                                        <input v-model.number="sponsorValue" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2" placeholder="0" />
                                    </label>
                                </div>
                                <label class="mt-3 flex items-center justify-between rounded-2xl border border-amber-200 bg-white px-3 py-2.5">
                                    <span class="flex items-center gap-2 text-sm font-bold text-amber-800"><Mail class="h-4 w-4" /> Also send the Email creative</span>
                                    <input v-model="sponsorEmailOn" type="checkbox" :disabled="!sponsorEmailAvailable" class="h-5 w-5 accent-amber-500" />
                                </label>
                                <div v-if="sponsorEmailOn && selectedSponsorAd && (selectedSponsorAd.emailSubject || selectedSponsorAd.emailBody)" class="mt-2 rounded-2xl border border-slate-100 bg-white p-3">
                                    <p class="mb-1 text-[10px] font-black tracking-widest text-slate-400">EMAIL CREATIVE (from library)</p>
                                    <p class="text-sm font-black">{{ selectedSponsorAd.emailSubject || '(no subject)' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ selectedSponsorAd.emailBody }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-black text-slate-600">Image URL (optional)</span>
                                <input v-model="imageUrl" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="https://..." />
                            </label>
                            <label class="block">
                                <span class="text-sm font-black text-slate-600">Action Link / Deep Link (optional)</span>
                                <input v-model="actionLink" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="linkup://news or https://..." />
                            </label>
                        </div>

                        <div>
                            <span class="text-sm font-black text-slate-600">Channels</span>
                            <div class="mt-1 grid grid-cols-2 gap-2 md:grid-cols-4">
                                <label class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5">
                                    <span class="flex items-center gap-1 text-sm font-bold"><Bell class="h-4 w-4" /> Push</span>
                                    <input v-model="channels.push" type="checkbox" class="h-4 w-4 accent-purple-600" />
                                </label>
                                <label class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5">
                                    <span class="flex items-center gap-1 text-sm font-bold"><MessageSquare class="h-4 w-4" /> In-App</span>
                                    <input v-model="channels.inApp" type="checkbox" class="h-4 w-4 accent-purple-600" />
                                </label>
                                <label class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5">
                                    <span class="flex items-center gap-1 text-sm font-bold"><Smartphone class="h-4 w-4" /> SMS</span>
                                    <input v-model="channels.sms" type="checkbox" class="h-4 w-4 accent-purple-600" />
                                </label>
                                <label class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5">
                                    <span class="flex items-center gap-1 text-sm font-bold"><Mail class="h-4 w-4" /> Email</span>
                                    <input v-model="channels.email" type="checkbox" class="h-4 w-4 accent-purple-600" />
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-black text-slate-600">Delivery</span>
                                <select v-model="schedule" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                    <option>Send Now</option>
                                    <option>Schedule</option>
                                </select>
                            </label>
                            <label v-if="schedule === 'Schedule'" class="block">
                                <span class="text-sm font-black text-slate-600">Scheduled Time</span>
                                <input v-model="scheduledAt" type="datetime-local" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                            </label>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h4 class="mb-3 text-xl font-black">Recent Sends</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="py-2">Time</th>
                                    <th>Title</th>
                                    <th>Audience</th>
                                    <th>Channels</th>
                                    <th>Reach</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody v-if="history.length">
                                <tr v-for="(h, i) in history.slice(0, 12)" :key="i" class="border-t border-slate-100">
                                    <td class="py-2 text-slate-500">{{ h.time }}</td>
                                    <td class="font-bold">
                                        {{ h.title }}
                                        <span v-if="h.sponsor" class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-black text-amber-700">$ {{ h.sponsor }}</span>
                                    </td>
                                    <td>{{ h.audience }}</td>
                                    <td class="text-xs">{{ h.channels }}</td>
                                    <td class="font-black">{{ numK(h.reach) }}</td>
                                    <td>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-black" :class="h.priority === 'Emergency' ? 'bg-rose-50 text-rose-700' : h.priority === 'Important' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600'">
                                            {{ h.priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-black" :class="h.status === 'Scheduled' ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700'">{{ h.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr><td colspan="7" class="py-6 text-center font-bold text-slate-400">No notifications sent yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h4 class="mb-4 text-xl font-black">Audience</h4>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Send By</span>
                        <select v-model="sendBy" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option>All</option>
                            <option>Region</option>
                            <option>Country</option>
                            <option>Caribbean Island</option>
                            <option>US State</option>
                            <option>City</option>
                            <option>Diaspora Hub</option>
                            <option>Specific User</option>
                        </select>
                    </label>

                    <div class="mt-4">
                        <div v-if="sendBy === 'All'" class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-600">
                            Everyone on LinkUp across all countries.
                        </div>

                        <div v-else-if="sendBy === 'Region'">
                            <label
                                v-for="r in [['caribbean', 'Caribbean'], ['latam', 'Latin America'], ['us', 'United States'], ['all', 'Global (Everyone)']]"
                                :key="r[0]"
                                class="mb-2 flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5"
                            >
                                <span class="text-sm font-bold">{{ r[1] }}</span>
                                <input type="checkbox" class="h-4 w-4 accent-purple-600" :checked="selectedRegionGroups.includes(r[0])" @change="toggleRegionGroup(r[0])" />
                            </label>
                        </div>

                        <div v-else-if="sendBy === 'Country' || sendBy === 'Caribbean Island'">
                            <div class="mb-2 flex gap-2">
                                <button class="rounded-xl bg-sky-50 px-3 py-1.5 text-xs font-black text-sky-700" @click="selectAllCountries(true)">Select All</button>
                                <button class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-black" @click="selectAllCountries(false)">Clear</button>
                            </div>
                            <div class="grid max-h-72 grid-cols-2 gap-1.5 overflow-y-auto pr-1">
                                <label v-for="c in countryListForMode" :key="c.key" class="flex items-center justify-between rounded-xl border border-slate-200 px-2.5 py-2">
                                    <span class="truncate text-xs font-bold">{{ c.flag }} {{ c.label }}</span>
                                    <input type="checkbox" class="h-4 w-4 shrink-0 accent-purple-600" :checked="selectedCountries.includes(c.key)" @change="toggleCountry(c.key)" />
                                </label>
                            </div>
                        </div>

                        <div v-else-if="sendBy === 'US State'">
                            <span class="text-xs font-bold text-slate-500">US State / DC</span>
                            <select v-model="selectedState" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option v-for="s in usStates" :key="s">{{ s }}</option>
                            </select>
                            <p class="mt-2 text-xs text-slate-400">Great for reaching Caribbean diaspora in Florida, New York, etc.</p>
                        </div>

                        <div v-else-if="sendBy === 'City'">
                            <input v-model="cityInput" list="pushCityList" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Enter or pick a city" />
                            <datalist id="pushCityList">
                                <option v-for="c in diasporaCities" :key="c" :value="c" />
                            </datalist>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <button v-for="c in diasporaCities.slice(0, 8)" :key="c" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold" @click="pickCity(c)">{{ c }}</button>
                            </div>
                        </div>

                        <div v-else-if="sendBy === 'Diaspora Hub'">
                            <div class="mb-2 flex gap-2">
                                <button class="rounded-xl bg-sky-50 px-3 py-1.5 text-xs font-black text-sky-700" @click="selectAllHubs(true)">Select All</button>
                                <button class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-black" @click="selectAllHubs(false)">Clear</button>
                            </div>
                            <div class="max-h-72 space-y-1.5 overflow-y-auto pr-1">
                                <label v-for="(h, i) in diasporaHubs" :key="h.key" class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
                                    <span class="text-sm font-bold">{{ h.key }} <span class="text-xs text-slate-400">{{ h.country === 'United States' ? '🇺🇸' : h.country === 'Canada' ? '🇨🇦' : '🇬🇧' }}</span></span>
                                    <input type="checkbox" class="h-4 w-4 accent-purple-600" :checked="selectedHubs.includes(i)" @change="toggleHub(i)" />
                                </label>
                            </div>
                        </div>

                        <div v-else-if="sendBy === 'Specific User'">
                            <input v-model="specificUser" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="User email or ID" />
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl p-6 text-white" :class="isEmergency ? 'bg-rose-600' : 'bg-purple-600'">
                    <p class="font-bold text-purple-100" :class="{ 'text-rose-100': isEmergency }">Estimated Reach</p>
                    <h3 class="mt-1 text-4xl font-black">{{ numK(audience.reach) }}</h3>
                    <p class="mt-1 text-sm text-purple-100" :class="{ 'text-rose-100': isEmergency }">{{ audience.desc || 'Select an audience' }}</p>
                </div>

                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="mb-2 text-xs font-black tracking-widest text-slate-400">PREVIEW</p>
                    <div class="flex gap-3 rounded-2xl bg-white p-4 shadow">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-black font-black text-white">L</div>
                        <div class="min-w-0">
                            <p class="text-sm font-black">LinkUp</p>
                            <p class="truncate text-sm font-bold">{{ previewTitle }}</p>
                            <p class="text-xs text-slate-500">{{ previewMsg }}</p>
                        </div>
                    </div>
                </div>

                <button
                    class="flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-4 font-black text-white shadow-lg"
                    :class="isEmergency ? 'bg-rose-600 shadow-rose-200' : 'bg-purple-600 shadow-purple-200'"
                    @click="send"
                >
                    <Send class="h-5 w-5" /> Send Notification
                </button>
            </div>
        </div>

        <div v-if="showTemplateModal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3">
            <div class="my-6 w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-linear-to-r from-fuchsia-600 to-purple-600 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">Message Templates</h3>
                        <p class="text-sm text-fuchsia-100">Pick a template, then tweak or run AI Assist</p>
                    </div>
                    <button @click="closeTemplates"><X class="h-6 w-6" /></button>
                </div>
                <div class="p-5">
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button
                            v-for="cat in Object.keys(templateLibrary)"
                            :key="cat"
                            class="rounded-full px-4 py-2 text-sm font-black"
                            :class="cat === templateCategory ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600'"
                            @click="templateCategory = cat"
                        >
                            {{ cat }}
                        </button>
                    </div>
                    <div class="max-h-[60vh] space-y-2 overflow-y-auto">
                        <div v-for="(item, index) in templateLibrary[templateCategory]" :key="item.t" class="flex items-start justify-between gap-3 rounded-2xl border border-slate-100 p-4">
                            <div>
                                <p class="font-black">{{ item.t }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ item.m }}</p>
                            </div>
                            <button class="shrink-0 rounded-xl bg-purple-600 px-4 py-2 text-sm font-black text-white" @click="useTemplate(templateCategory, index)">Use</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
