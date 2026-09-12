<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type EmailCategory = {
    id?: number;
    key: string;
    shortLabel: string;
    label: string;
    icon: string;
    description: string;
    preview: [string, string];
};

type Region = {
    id?: number;
    key: string;
    code?: string;
    flag: string;
    name: string;
    currency: string;
    group: string;
};

type SponsorAd = {
    id: string;
    rawId?: number | string;
    emailAdCategoryId?: number | string | null;
    companyName: string;
    emailCategory: string;
    regions: string[];
    startDate: string;
    endDate: string;
    priority: string;
    status: 'active' | 'paused' | 'draft';
    headline: string;
    message: string;
    ctaText: string;
    ctaUrl: string;
    image: string;
    impressions: number;
    opens: number;
    clicks: number;
    revenue: number;
};

const props = defineProps<{
    emailAds?: any[];
    emailCategories?: any[];
    emailCountries?: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const activeTab = ref<'manage' | 'analytics' | 'reports'>('manage');
const activeFilter = ref('all');
const previewCategory = ref('events');
const previewCountry = ref('bs');
const selectedSponsorId = ref<string | null>(null);
const reportGenerated = ref(false);
const reportOutputRef = ref<HTMLElement | null>(null);
const editingId = ref<string | null>(null);

const regions = ref<Region[]>([
    { key: 'bs', flag: '🇧🇸', name: 'Bahamas', currency: 'BSD$', group: 'Caribbean' },
    { key: 'jm', flag: '🇯🇲', name: 'Jamaica', currency: 'JMD$', group: 'Caribbean' },
    { key: 'tt', flag: '🇹🇹', name: 'Trinidad & Tobago', currency: 'TTD$', group: 'Caribbean' },
    { key: 'bb', flag: '🇧🇧', name: 'Barbados', currency: 'BBD$', group: 'Caribbean' },
    { key: 'gy', flag: '🇬🇾', name: 'Guyana', currency: 'GYD$', group: 'Caribbean' },
    { key: 'sr', flag: '🇸🇷', name: 'Suriname', currency: 'SRD$', group: 'Caribbean' },
    { key: 'ht', flag: '🇭🇹', name: 'Haiti', currency: 'HTG', group: 'Caribbean' },
    { key: 'do', flag: '🇩🇴', name: 'Dominican Republic', currency: 'DOP$', group: 'Caribbean' },
    { key: 'cu', flag: '🇨🇺', name: 'Cuba', currency: 'CUP', group: 'Caribbean' },
    { key: 'pr', flag: '🇵🇷', name: 'Puerto Rico', currency: 'USD$', group: 'Caribbean' },
    { key: 'ag', flag: '🇦🇬', name: 'Antigua & Barbuda', currency: 'XCD$', group: 'Caribbean' },
    { key: 'lc', flag: '🇱🇨', name: 'Saint Lucia', currency: 'XCD$', group: 'Caribbean' },
    { key: 'gd', flag: '🇬🇩', name: 'Grenada', currency: 'XCD$', group: 'Caribbean' },
    { key: 'vc', flag: '🇻🇨', name: 'St. Vincent', currency: 'XCD$', group: 'Caribbean' },
    { key: 'kn', flag: '🇰🇳', name: 'St. Kitts & Nevis', currency: 'XCD$', group: 'Caribbean' },
    { key: 'ky', flag: '🇰🇾', name: 'Cayman Islands', currency: 'KYD$', group: 'Caribbean' },
    { key: 'tc', flag: '🇹🇨', name: 'Turks & Caicos', currency: 'USD$', group: 'Caribbean' },
    { key: 'mx', flag: '🇲🇽', name: 'Mexico', currency: 'MXN$', group: 'Central America' },
    { key: 'bz', flag: '🇧🇿', name: 'Belize', currency: 'BZD$', group: 'Central America' },
    { key: 'gt', flag: '🇬🇹', name: 'Guatemala', currency: 'GTQ', group: 'Central America' },
    { key: 'sv', flag: '🇸🇻', name: 'El Salvador', currency: 'USD$', group: 'Central America' },
    { key: 'hn', flag: '🇭🇳', name: 'Honduras', currency: 'HNL', group: 'Central America' },
    { key: 'ni', flag: '🇳🇮', name: 'Nicaragua', currency: 'NIO', group: 'Central America' },
    { key: 'cr', flag: '🇨🇷', name: 'Costa Rica', currency: 'CRC', group: 'Central America' },
    { key: 'pa', flag: '🇵🇦', name: 'Panama', currency: 'PAB', group: 'Central America' },
    { key: 'co', flag: '🇨🇴', name: 'Colombia', currency: 'COP$', group: 'South America' },
    { key: 've', flag: '🇻🇪', name: 'Venezuela', currency: 'VES', group: 'South America' },
    { key: 'ec', flag: '🇪🇨', name: 'Ecuador', currency: 'USD$', group: 'South America' },
    { key: 'pe', flag: '🇵🇪', name: 'Peru', currency: 'PEN', group: 'South America' },
    { key: 'br', flag: '🇧🇷', name: 'Brazil', currency: 'BRL$', group: 'South America' },
]);

const categoryColors: Record<string, string> = {
    like_received: '#ec4899',
    matched: '#7c3aed',
    new_follower: '#10b981',
    message: '#126fc9',
    livestream: '#e02424',
    event_digest: '#8b5cf6',
    events: '#7c3aed',
    ticket_purchase: '#0ea5e9',
    marketplace: '#2563eb',
    money_received: '#16a34a',
    money_request: '#f59e0b',
    birthday: '#f97316',
    group_invite: '#f43f5e',
};

const emailCategories = ref<EmailCategory[]>([
    { key: 'like_received', shortLabel: 'Like', label: 'You Have a Like', icon: '💚', description: 'Sent when a member receives a like or profile link.', preview: ['You Have a Like', 'Someone just liked your profile.'] },
    { key: 'matched', shortLabel: 'Match', label: "It's a Match", icon: '💕', description: 'Sent when two members mutually like each other.', preview: ["It's a Match!", 'You and another member just matched.'] },
    { key: 'new_follower', shortLabel: 'Follower', label: 'New Follower', icon: '🔔', description: 'Sent when a member gains a new follower on LinkUp.', preview: ['New Follower', 'Someone just started following you.'] },
    { key: 'message', shortLabel: 'Message', label: 'New Message', icon: '💬', description: 'Sent when a member receives a new chat message.', preview: ['New Message', 'Someone just sent you a message.'] },
    { key: 'livestream', shortLabel: 'Live', label: 'Live Stream Alert', icon: '🔴', description: 'Sent when a followed member or creator starts a live stream.', preview: ['Going Live!', 'Someone you follow just started a live stream.'] },
    { key: 'event_digest', shortLabel: 'Digest', label: 'Upcoming Events Digest', icon: '📅', description: 'Daily or weekly digest of events near the member.', preview: ['Events Near You', 'Here are the top events happening in your area.'] },
    { key: 'ticket_purchase', shortLabel: 'Ticket', label: 'Ticket Purchase Confirmed', icon: '🎫', description: 'Sent after a member successfully buys a ticket.', preview: ["You're Going!", 'Your ticket has been confirmed. See you there!'] },
    { key: 'marketplace', shortLabel: 'Shop', label: 'Marketplace Email', icon: '🛍️', description: 'Sent for shopping, seller, and product emails.', preview: ['Marketplace Picks', 'Discover top products from trusted sellers.'] },
    { key: 'money_received', shortLabel: 'Money In', label: 'You Have Received Money', icon: '💳', description: 'Sent when money lands in a member wallet.', preview: ['You Have Received Money', 'Your wallet has been updated.'] },
    { key: 'money_request', shortLabel: 'Request', label: 'Money Request Received', icon: '📩', description: 'Sent when a payment request is received.', preview: ['Money Request Received', 'A payment request was sent to you.'] },
    { key: 'birthday', shortLabel: 'Birthday', label: 'Birthday Email', icon: '🎂', description: 'Automated birthday greeting.', preview: ['Happy Birthday!', 'Today is all about celebrating you.'] },
    { key: 'group_invite', shortLabel: 'Group', label: 'Group Invite', icon: '👥', description: 'Sent when a member is invited to a LinkUp group.', preview: ["You've Been Invited", 'Someone added you to a group on LinkUp.'] },
]);

const sponsors = ref<SponsorAd[]>([
    {
        id: 'em-1',
        companyName: 'Coca-Cola',
        emailCategory: 'events',
        regions: [],
        startDate: '2026-01-01',
        endDate: '2026-12-31',
        priority: '1',
        status: 'active',
        headline: 'Real Magic. Real Connections.',
        message: 'Proudly supporting unforgettable LinkUp events across the Caribbean.',
        ctaText: 'Learn More',
        ctaUrl: 'https://www.coca-cola.com/',
        image: 'https://images.unsplash.com/photo-1629203851122-3726ecdf080e?q=80&w=600&auto=format&fit=crop',
        impressions: 48200,
        opens: 14960,
        clicks: 1860,
        revenue: 899,
    },
    {
        id: 'em-2',
        companyName: 'Royal Bank of Canada',
        emailCategory: 'money_received',
        regions: ['bs', 'bb', 'tt'],
        startDate: '2026-01-01',
        endDate: '2026-12-31',
        priority: '1',
        status: 'active',
        headline: 'Move Forward With Confidence.',
        message: 'Financial tools and support for your next step across the Caribbean.',
        ctaText: 'Explore Banking',
        ctaUrl: 'https://www.rbc.com/',
        image: 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?q=80&w=600&auto=format&fit=crop',
        impressions: 31400,
        opens: 10962,
        clicks: 942,
        revenue: 1199,
    },
    {
        id: 'em-3',
        companyName: 'Olivele',
        emailCategory: 'birthday',
        regions: ['bs'],
        startDate: '2026-01-01',
        endDate: '2026-12-31',
        priority: '2',
        status: 'active',
        headline: 'Celebrate With Something Sweet.',
        message: 'Birthday treats and local deals made for your special day.',
        ctaText: 'Claim Offer',
        ctaUrl: 'https://linkupvibes.com/',
        image: 'https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?q=80&w=600&auto=format&fit=crop',
        impressions: 12800,
        opens: 5120,
        clicks: 672,
        revenue: 349,
    },
]);

const categoryForm = ref({
    label: '',
    icon: '✉️',
    shortLabel: '',
    description: '',
});

const defaultForm = () => ({
    companyName: '',
    emailCategory: 'events',
    regions: [] as string[],
    startDate: '2026-07-15',
    endDate: '2026-12-31',
    priority: '3',
    status: 'active' as SponsorAd['status'],
    headline: '',
    message: '',
    ctaText: 'Learn More',
    ctaUrl: '',
    image: '',
});

const adForm = ref(defaultForm());
const creativeFile = ref<File | null>(null);
const isSavingSponsor = ref(false);
const isSavingCategory = ref(false);

syncEmailSponsorProps();

watch(
    () => [props.emailAds, props.emailCategories, props.emailCountries],
    () => syncEmailSponsorProps(),
    { deep: true }
);

const groupedRegions = computed(() => {
    return regions.value.reduce<Record<string, Region[]>>((groups, region) => {
        groups[region.group] = groups[region.group] || [];
        groups[region.group].push(region);
        return groups;
    }, {});
});

const selectedCategory = computed(() => emailCategories.value.find((cat) => cat.key === previewCategory.value) ?? emailCategories.value[0]);
const selectedRegion = computed(() => regions.value.find((region) => region.key === previewCountry.value) ?? regions.value[0]);

const filteredSponsors = computed(() => {
    if (activeFilter.value === 'all') return sponsors.value;
    return sponsors.value.filter((sponsor) => sponsor.emailCategory === activeFilter.value);
});

const activeAdForPreview = computed(() => {
    const today = new Date();
    return [...sponsors.value]
        .filter((sponsor) => {
            const inCategory = sponsor.emailCategory === previewCategory.value || sponsor.emailCategory === 'all';
            const inRegion = sponsor.regions.length === 0 || sponsor.regions.includes(previewCountry.value);
            const inDate = new Date(sponsor.startDate) <= today && new Date(sponsor.endDate) >= today;
            return sponsor.status === 'active' && inCategory && inRegion && inDate;
        })
        .sort((a, b) => Number(a.priority) - Number(b.priority))[0] ?? null;
});

const selectedReportSponsor = computed(() => sponsors.value.find((sponsor) => sponsor.id === selectedSponsorId.value) ?? null);
const totalImpressions = computed(() => sponsors.value.reduce((sum, sponsor) => sum + sponsor.impressions, 0));
const totalOpens = computed(() => sponsors.value.reduce((sum, sponsor) => sum + sponsor.opens, 0));
const totalClicks = computed(() => sponsors.value.reduce((sum, sponsor) => sum + sponsor.clicks, 0));
const totalRevenue = computed(() => sponsors.value.reduce((sum, sponsor) => sum + sponsor.revenue, 0));

const analyticsRows = computed(() => sponsors.value.map((sponsor) => ({
    ...sponsor,
    categoryLabel: getCategoryLabel(sponsor.emailCategory),
    regionLabel: regionLabel(sponsor.regions),
    openRate: rate(sponsor.opens, sponsor.impressions),
    ctr: rate(sponsor.clicks, sponsor.opens),
})));

const revenueBars = computed(() => makeBars(sponsors.value.map((sponsor) => ({
    label: sponsor.companyName,
    value: sponsor.revenue,
    display: props.fmt(sponsor.revenue),
}))));

const openRateBars = computed(() => makeBars(emailCategories.value.slice(0, 6).map((cat) => {
    const categoryAds = sponsors.value.filter((sponsor) => sponsor.emailCategory === cat.key);
    const impressions = categoryAds.reduce((sum, sponsor) => sum + sponsor.impressions, 0);
    const opens = categoryAds.reduce((sum, sponsor) => sum + sponsor.opens, 0);
    return {
        label: cat.shortLabel,
        value: Number(rate(opens, impressions)),
        display: `${rate(opens, impressions)}%`,
    };
})));

const regionClickBars = computed(() => makeBars(regions.value.slice(0, 6).map((region) => {
    const clicks = sponsors.value
        .filter((sponsor) => sponsor.regions.length === 0 || sponsor.regions.includes(region.key))
        .reduce((sum, sponsor) => sum + Math.round(sponsor.clicks / Math.max(sponsor.regions.length || 5, 1)), 0);
    return { label: region.name, value: clicks, display: props.num(clicks) };
})));

const ctrBars = computed(() => makeBars(emailCategories.value.slice(0, 6).map((cat) => {
    const categoryAds = sponsors.value.filter((sponsor) => sponsor.emailCategory === cat.key);
    const opens = categoryAds.reduce((sum, sponsor) => sum + sponsor.opens, 0);
    const clicks = categoryAds.reduce((sum, sponsor) => sum + sponsor.clicks, 0);
    return {
        label: cat.shortLabel,
        value: Number(rate(clicks, opens)),
        display: `${rate(clicks, opens)}%`,
    };
})));

const triggerRows = computed(() => emailCategories.value.slice(0, 7).map((cat) => ({
    trigger: cat.label,
    templateKey: cat.key,
    activeAd: getBestAd(cat.key, previewCountry.value),
})));

function slugify(value: string) {
    return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
}

function firstValue(...values: any[]) {
    return values.find((value) => value !== undefined && value !== null && value !== '');
}

function countryFlag(code?: string) {
    const clean = String(code || '').trim().toUpperCase();
    if (!/^[A-Z]{2}$/.test(clean)) return '🌎';

    return clean
        .split('')
        .map((char) => String.fromCodePoint(127397 + char.charCodeAt(0)))
        .join('');
}

function normalizeCountryRows(rows?: any[]): Region[] {
    if (!Array.isArray(rows) || rows.length === 0) return [];

    return rows.map((country) => {
        const code = String(firstValue(country.code, country.iso2, country.country_code, country.key, '')).toLowerCase();
        const id = Number(country.id) || undefined;

        return {
            id,
            key: String(firstValue(country.id, code, country.key)),
            code,
            flag: firstValue(country.flag, country.emoji, countryFlag(code)),
            name: firstValue(country.name, country.country, country.label, code.toUpperCase()),
            currency: firstValue(country.currency, country.currency_code, country.currency_symbol, 'USD$'),
            group: firstValue(country.subregion, country.region, 'Caribbean'),
        };
    });
}

function normalizeCategoryRows(rows?: any[]): EmailCategory[] {
    if (!Array.isArray(rows) || rows.length === 0) return [];

    return rows.map((category) => {
        const label = String(firstValue(category.label, category.name, 'Email'));
        const key = String(firstValue(category.key, slugify(label)));

        return {
            id: Number(category.id) || undefined,
            key,
            shortLabel: String(firstValue(category.short_label, category.shortLabel, label)),
            label,
            icon: String(firstValue(category.icon, '✉️')),
            description: String(firstValue(category.description, 'Ads shown for this email category.')),
            preview: [
                String(firstValue(category.preview_title, category.previewTitle, label)),
                String(firstValue(category.preview_subtitle, category.previewSubtitle, 'Your dynamic email content appears here.')),
            ],
        };
    });
}

function categoryKeyFromAd(ad: any) {
    const category = ad.category || {};
    const id = firstValue(ad.email_ad_category_id, ad.emailAdCategoryId, category.id);
    return String(firstValue(
        category.key,
        ad.email_category,
        ad.emailCategory,
        emailCategories.value.find((cat) => String(cat.id) === String(id))?.key,
        'events'
    ));
}

function normalizeSponsorRows(rows?: any[]): SponsorAd[] {
    if (!Array.isArray(rows) || rows.length === 0) return [];

    return rows.map((ad) => {
        const countries = Array.isArray(ad.countries) ? ad.countries : [];
        const clicks = Number(firstValue(ad.clicks_count, ad.clicks, 0)) || 0;
        const impressions = Number(firstValue(ad.impressions_count, ad.impressions, 0)) || 0;

        return {
            id: String(ad.id),
            rawId: ad.id,
            emailAdCategoryId: firstValue(ad.email_ad_category_id, ad.emailAdCategoryId, ad.category?.id, null),
            companyName: String(firstValue(ad.company_name, ad.companyName, ad.name, 'Sponsor')),
            emailCategory: categoryKeyFromAd(ad),
            regions: countries.map((country: any) => String(firstValue(country.id, country.code, country.key))).filter(Boolean),
            startDate: String(firstValue(ad.start_date, ad.startDate, '2026-07-15')).slice(0, 10),
            endDate: String(firstValue(ad.end_date, ad.endDate, '2026-12-31')).slice(0, 10),
            priority: String(firstValue(ad.priority, '3')),
            status: String(firstValue(ad.status, 'draft')).toLowerCase() as SponsorAd['status'],
            headline: String(firstValue(ad.headline, 'Sponsor headline')),
            message: String(firstValue(ad.message, 'Sponsor message')),
            ctaText: String(firstValue(ad.cta_text, ad.ctaText, 'Learn More')),
            ctaUrl: String(firstValue(ad.cta_url, ad.ctaUrl, 'https://linkupvibes.com/')),
            image: String(firstValue(ad.image_url, ad.image, 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=600&auto=format&fit=crop')),
            impressions,
            opens: Number(firstValue(ad.opens_count, ad.opens, impressions)) || 0,
            clicks,
            revenue: Number(firstValue(ad.revenue, ad.cost, clicks ? clicks * 0.48 : 0)) || 0,
        };
    });
}

function syncEmailSponsorProps() {
    const normalizedRegions = normalizeCountryRows(props.emailCountries);
    if (normalizedRegions.length) {
        regions.value = normalizedRegions;
        if (!regions.value.some((region) => region.key === previewCountry.value)) {
            previewCountry.value = regions.value[0].key;
        }
    }

    const normalizedCategories = normalizeCategoryRows(props.emailCategories);
    if (normalizedCategories.length) {
        emailCategories.value = normalizedCategories;
        if (!emailCategories.value.some((category) => category.key === previewCategory.value)) {
            previewCategory.value = emailCategories.value[0].key;
        }
        if (!emailCategories.value.some((category) => category.key === adForm.value.emailCategory)) {
            adForm.value.emailCategory = emailCategories.value[0].key;
        }
    }

    const normalizedSponsors = normalizeSponsorRows(props.emailAds);
    if (normalizedSponsors.length || Array.isArray(props.emailAds)) {
        sponsors.value = normalizedSponsors;
    }
}

function categoryColor(key: string) {
    return categoryColors[key] || '#6d28d9';
}

function getCategoryLabel(key: string) {
    return emailCategories.value.find((cat) => cat.key === key)?.label ?? key;
}

function categoryCount(key: string) {
    return sponsors.value.filter((sponsor) => sponsor.emailCategory === key).length;
}

function getRegion(key: string) {
    return regions.value.find((region) => region.key === key);
}

function regionLabel(keys: string[]) {
    if (!keys.length) return 'All Regions';
    return keys.map((key) => getRegion(key)?.name ?? key).join(', ');
}

function rate(part: number, whole: number) {
    if (!whole) return '0.0';
    return ((part / whole) * 100).toFixed(1);
}

function makeBars(items: { label: string; value: number; display: string }[]) {
    const max = Math.max(...items.map((item) => item.value), 1);
    return items.map((item) => ({
        ...item,
        width: `${Math.max((item.value / max) * 100, item.value > 0 ? 6 : 0)}%`,
    }));
}

function getBestAd(categoryKey: string, regionKey: string) {
    return [...sponsors.value]
        .filter((sponsor) => sponsor.status === 'active')
        .filter((sponsor) => sponsor.emailCategory === categoryKey || sponsor.emailCategory === 'all')
        .filter((sponsor) => !sponsor.regions.length || sponsor.regions.includes(regionKey))
        .sort((a, b) => Number(a.priority) - Number(b.priority))[0] ?? null;
}

async function addCategory() {
    const label = categoryForm.value.label.trim();
    if (!label) return;

    const key = slugify(label);
    if (!key || emailCategories.value.some((cat) => cat.key === key)) return;

    isSavingCategory.value = true;

    try {
        const response = await axios.post(route('admin.ads.email-sponsor.categories.store'), {
            label,
            short_label: categoryForm.value.shortLabel.trim() || label,
            icon: categoryForm.value.icon.trim() || '✉️',
            description: categoryForm.value.description.trim() || 'Ads shown for this email category.',
        }, { headers: { Accept: 'application/json' } });

        const [category] = normalizeCategoryRows([response.data.category]);
        emailCategories.value.push(category);
        adForm.value.emailCategory = category.key;
        previewCategory.value = category.key;
        categoryForm.value = { label: '', icon: '✉️', shortLabel: '', description: '' };
        toast.success('Email category created.');
    } catch (error: any) {
        toast.error(error?.response?.data?.message || 'Could not create category.');
    } finally {
        isSavingCategory.value = false;
    }
}

async function deleteCategory(key: string) {
    const category = emailCategories.value.find((cat) => cat.key === key);
    if (!category?.id) return;
    if (categoryCount(key) > 0) return;

    try {
        await axios.delete(route('admin.ads.email-sponsor.categories.destroy', { category: category.id }), {
            headers: { Accept: 'application/json' },
        });
        emailCategories.value = emailCategories.value.filter((cat) => cat.key !== key);
        if (previewCategory.value === key) previewCategory.value = emailCategories.value[0]?.key ?? 'events';
        toast.success('Email category deleted.');
    } catch (error: any) {
        toast.error(error?.response?.data?.message || 'Could not delete category.');
    }
}

function toggleRegion(key: string) {
    const regionsValue = adForm.value.regions;
    adForm.value.regions = regionsValue.includes(key)
        ? regionsValue.filter((regionKey) => regionKey !== key)
        : [...regionsValue, key];
}

function clearRegions() {
    adForm.value.regions = [];
}

function updateCreative(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    creativeFile.value = file;
    const reader = new FileReader();
    reader.onload = () => {
        adForm.value.image = String(reader.result || '');
    };
    reader.readAsDataURL(file);
}

function resetAdForm() {
    editingId.value = null;
    creativeFile.value = null;
    adForm.value = defaultForm();
}

function categoryIdForKey(key: string) {
    return emailCategories.value.find((category) => category.key === key)?.id;
}

function buildSponsorPayload() {
    const categoryId = categoryIdForKey(adForm.value.emailCategory);
    const payload = new FormData();

    payload.append('company_name', adForm.value.companyName.trim());
    if (categoryId) payload.append('email_ad_category_id', String(categoryId));
    payload.append('start_date', adForm.value.startDate);
    payload.append('end_date', adForm.value.endDate);
    payload.append('priority', adForm.value.priority);
    payload.append('status', adForm.value.status);
    payload.append('headline', adForm.value.headline.trim());
    payload.append('message', adForm.value.message.trim());
    payload.append('cta_text', adForm.value.ctaText.trim() || 'Learn More');
    payload.append('cta_url', adForm.value.ctaUrl.trim() || 'https://linkupvibes.com/');
    payload.append('all_regions', adForm.value.regions.length === 0 ? '1' : '0');

    adForm.value.regions.forEach((regionKey) => {
        const region = regions.value.find((item) => item.key === regionKey);
        if (region?.id) payload.append('country_ids[]', String(region.id));
    });

    if (creativeFile.value) {
        payload.append('image_file', creativeFile.value);
    } else if (adForm.value.image && !adForm.value.image.startsWith('data:')) {
        payload.append('image_url', adForm.value.image);
    }

    return payload;
}

async function saveSponsor() {
    if (!adForm.value.companyName.trim() || !adForm.value.headline.trim() || !adForm.value.message.trim()) return;

    if (!categoryIdForKey(adForm.value.emailCategory)) {
        toast.error('Please select a database email category.');
        return;
    }

    isSavingSponsor.value = true;

    try {
        const payload = buildSponsorPayload();
        let response;

        if (editingId.value) {
            payload.append('_method', 'PUT');
            response = await axios.post(route('admin.ads.email-sponsor.update', { ad: editingId.value }), payload, {
                headers: { Accept: 'application/json', 'Content-Type': 'multipart/form-data' },
            });
        } else {
            response = await axios.post(route('admin.ads.email-sponsor.store'), payload, {
                headers: { Accept: 'application/json', 'Content-Type': 'multipart/form-data' },
            });
        }

        const [sponsorData] = normalizeSponsorRows([response.data.ad]);
        sponsors.value = editingId.value
            ? sponsors.value.map((sponsor) => sponsor.id === String(sponsorData.id) ? sponsorData : sponsor)
            : [sponsorData, ...sponsors.value];

        activeFilter.value = sponsorData.emailCategory;
        previewCategory.value = sponsorData.emailCategory;
        toast.success(editingId.value ? 'Email sponsor updated.' : 'Email sponsor created.');
        resetAdForm();
    } catch (error: any) {
        const errors = error?.response?.data?.errors;
        const firstError = errors ? Object.values(errors).flat()[0] : null;
        toast.error(String(firstError || error?.response?.data?.message || 'Could not save email sponsor.'));
    } finally {
        isSavingSponsor.value = false;
    }
}

function editSponsor(id: string) {
    const sponsor = sponsors.value.find((item) => item.id === id);
    if (!sponsor) return;

    editingId.value = sponsor.id;
    adForm.value = {
        companyName: sponsor.companyName,
        emailCategory: sponsor.emailCategory,
        regions: [...sponsor.regions],
        startDate: sponsor.startDate,
        endDate: sponsor.endDate,
        priority: sponsor.priority,
        status: sponsor.status,
        headline: sponsor.headline,
        message: sponsor.message,
        ctaText: sponsor.ctaText,
        ctaUrl: sponsor.ctaUrl,
        image: sponsor.image,
    };
    activeTab.value = 'manage';
}

async function deleteSponsor(id: string) {
    try {
        await axios.delete(route('admin.ads.email-sponsor.destroy', { ad: id }), {
            headers: { Accept: 'application/json' },
        });
        sponsors.value = sponsors.value.filter((sponsor) => sponsor.id !== id);
        if (selectedSponsorId.value === id) {
            selectedSponsorId.value = null;
            reportGenerated.value = false;
        }
        toast.success('Email sponsor deleted.');
    } catch (error: any) {
        toast.error(error?.response?.data?.message || 'Could not delete email sponsor.');
    }
}

async function generateReport() {
    if (!selectedSponsorId.value) return;
    reportGenerated.value = true;
    await nextTick();
    reportOutputRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetReport() {
    reportGenerated.value = false;
}

function printReport() {
    window.print();
}
</script>

<template>
    <div class="em-section">
        <Toaster rich-colors position="top-right" />

        <div class="em-topnav">
            <div class="em-brand">
                <div class="em-brand-icon">📢</div>
                <div>
                    <div class="em-title">Email Sponsors</div>
                    <div class="em-subtitle">Assign sponsor ads to email categories - auto-injected at send time across {{ regions.length }} markets</div>
                </div>
            </div>
            <div class="em-nav-actions">
                <button class="em-navbtn" :class="{ active: activeTab === 'manage' }" @click="activeTab = 'manage'">📋 Manage</button>
                <button class="em-navbtn" :class="{ active: activeTab === 'analytics' }" @click="activeTab = 'analytics'">📈 Analytics</button>
                <button class="em-navbtn" :class="{ active: activeTab === 'reports' }" @click="activeTab = 'reports'">📄 Reports</button>
            </div>
        </div>

        <div v-if="activeTab === 'manage'" class="em-root">
            <div class="em-eyebrow">Active Regions</div>
            <div class="em-regions-strip">
                <span v-for="region in regions.slice(0, 12)" :key="region.key" class="em-region-pill">
                    <span class="em-rp-flag">{{ region.flag }}</span>
                    {{ region.name }}
                    <span class="em-rp-count">{{ sponsors.filter((sponsor) => !sponsor.regions.length || sponsor.regions.includes(region.key)).length }}</span>
                </span>
            </div>

            <div class="em-eyebrow">Email Categories</div>
            <section class="em-category-grid">
                <article
                    v-for="cat in emailCategories"
                    :key="cat.key"
                    class="em-category-card"
                    :style="{ '--cat-color': categoryColor(cat.key) }"
                >
                    <span class="em-cat-count">{{ categoryCount(cat.key) }}</span>
                    <span class="em-cat-icon">{{ cat.icon }}</span>
                    <h3>{{ cat.label }}</h3>
                    <p>{{ cat.description }}</p>
                </article>
            </section>

            <div class="em-layout">
                <aside class="em-panel">
                    <div class="em-panel-header">
                        <h2>🏷️ Create Sponsor Ad</h2>
                        <p>Add a sponsor, choose the email category, set dates, and save the ad for auto-injection.</p>
                    </div>
                    <div class="em-panel-body">
                        <div class="em-form-group">
                            <span class="em-form-group-label">Manage Email Categories</span>
                            <div class="em-field">
                                <label>Category Name</label>
                                <input v-model="categoryForm.label" type="text" placeholder="e.g. Ticket Purchase Email">
                            </div>
                            <div class="em-row">
                                <div class="em-field">
                                    <label>Icon</label>
                                    <input v-model="categoryForm.icon" type="text" placeholder="🎫" maxlength="4">
                                </div>
                                <div class="em-field">
                                    <label>Short Label</label>
                                    <input v-model="categoryForm.shortLabel" type="text" placeholder="Tickets">
                                </div>
                            </div>
                            <div class="em-field">
                                <label>Description</label>
                                <textarea v-model="categoryForm.description" placeholder="When is this email sent?"></textarea>
                            </div>
                            <button type="button" class="em-btn em-btn-secondary em-full" :disabled="isSavingCategory" @click="addCategory">
                                {{ isSavingCategory ? 'Saving...' : '+ Add Category' }}
                            </button>

                            <div class="em-category-manager-list">
                                <div v-for="cat in emailCategories" :key="cat.key" class="em-manager-row">
                                    <span class="em-manager-icon">{{ cat.icon }}</span>
                                    <div>
                                        <strong>{{ cat.label }}</strong>
                                        <small>{{ cat.key }}</small>
                                    </div>
                                    <button
                                        type="button"
                                        class="em-mini-danger"
                                        :disabled="!cat.id || categoryCount(cat.key) > 0"
                                        title="Delete empty category"
                                        @click="deleteCategory(cat.key)"
                                    >
                                        x
                                    </button>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="saveSponsor">
                            <div class="em-form-group">
                                <span class="em-form-group-label">Sponsor Info</span>
                                <div class="em-field">
                                    <label>Company Name</label>
                                    <input v-model="adForm.companyName" type="text" placeholder="e.g. Coca-Cola" required>
                                </div>
                                <div class="em-field">
                                    <label>Email Category</label>
                                    <select v-model="adForm.emailCategory" required>
                                        <option v-for="cat in emailCategories" :key="cat.key" :value="cat.key">{{ cat.label }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="em-form-group">
                                <span class="em-form-group-label">Schedule & Status</span>
                                <div class="em-row">
                                    <div class="em-field">
                                        <label>Start Date</label>
                                        <input v-model="adForm.startDate" type="date" required>
                                    </div>
                                    <div class="em-field">
                                        <label>End Date</label>
                                        <input v-model="adForm.endDate" type="date" required>
                                    </div>
                                </div>
                                <div class="em-row">
                                    <div class="em-field">
                                        <label>Priority</label>
                                        <select v-model="adForm.priority">
                                            <option value="1">1 - Highest</option>
                                            <option value="2">2 - High</option>
                                            <option value="3">3 - Normal</option>
                                            <option value="4">4 - Low</option>
                                        </select>
                                    </div>
                                    <div class="em-field">
                                        <label>Status</label>
                                        <select v-model="adForm.status">
                                            <option value="active">Active</option>
                                            <option value="paused">Paused</option>
                                            <option value="draft">Draft</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="em-form-group">
                                <span class="em-form-group-label">Target Regions</span>
                                <div class="em-help">Select which countries see this ad. Leave all unchecked to serve all regions.</div>
                                <label class="em-region-all-btn">
                                    <input type="checkbox" :checked="adForm.regions.length === 0" @change="clearRegions">
                                    All regions
                                </label>
                                <div class="em-region-scroll">
                                    <template v-for="(group, groupName) in groupedRegions" :key="groupName">
                                        <span class="em-region-group-header">{{ groupName }}</span>
                                        <div class="em-region-grid">
                                            <label v-for="region in group" :key="region.key" class="em-region-check-label">
                                                <input
                                                    type="checkbox"
                                                    :checked="adForm.regions.includes(region.key)"
                                                    @change="toggleRegion(region.key)"
                                                >
                                                <span>{{ region.flag }}</span>
                                                {{ region.name }}
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="em-form-group">
                                <span class="em-form-group-label">Ad Content</span>
                                <div class="em-field">
                                    <label>Headline</label>
                                    <input v-model="adForm.headline" type="text" placeholder="e.g. Real Magic. Real Connections." required>
                                </div>
                                <div class="em-field">
                                    <label>Message</label>
                                    <textarea v-model="adForm.message" placeholder="Short sponsor copy for the email slot." required></textarea>
                                </div>
                                <div class="em-row">
                                    <div class="em-field">
                                        <label>Button Text</label>
                                        <input v-model="adForm.ctaText" type="text" placeholder="Learn More" required>
                                    </div>
                                    <div class="em-field">
                                        <label>Button URL</label>
                                        <input v-model="adForm.ctaUrl" type="url" placeholder="https://" required>
                                    </div>
                                </div>
                            </div>

                            <div class="em-form-group">
                                <span class="em-form-group-label">Creative</span>
                                <div class="em-field">
                                    <label>Upload Image <span>JPEG, PNG, GIF, WebP</span></label>
                                    <input type="file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" @change="updateCreative">
                                </div>
                                <div class="em-field">
                                    <label>Or Image URL</label>
                                    <input v-model="adForm.image" type="url" placeholder="https://example.com/banner.gif">
                                </div>
                                <img v-if="adForm.image" :src="adForm.image" class="em-creative-preview" alt="Creative preview">
                            </div>

                            <div class="em-form-actions">
                                <button v-if="editingId" type="button" class="em-btn em-btn-secondary" :disabled="isSavingSponsor" @click="resetAdForm">Cancel Edit</button>
                                <button type="submit" class="em-btn em-btn-primary" :disabled="isSavingSponsor">
                                    💾 {{ isSavingSponsor ? 'Saving...' : (editingId ? 'Update Sponsor Ad' : 'Save Sponsor Ad') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </aside>

                <section class="em-panel">
                    <div class="em-panel-header">
                        <h2>📋 Ads Library</h2>
                        <p>Filter by category, edit or remove ads. The highest-priority active ad per category is injected at send time.</p>
                    </div>
                    <div class="em-panel-body">
                        <div class="em-tabs">
                            <button type="button" class="em-tab" :class="{ active: activeFilter === 'all' }" @click="activeFilter = 'all'">All</button>
                            <button
                                v-for="cat in emailCategories"
                                :key="cat.key"
                                type="button"
                                class="em-tab"
                                :class="{ active: activeFilter === cat.key }"
                                @click="activeFilter = cat.key"
                            >
                                {{ cat.shortLabel }}
                            </button>
                        </div>

                        <div class="em-notice">
                            <strong>How injection works:</strong> When an email triggers, the backend reads
                            <code>user.country</code> and calls <code>getAdForEmail(category, user.country, ads)</code>.
                            The winning ad drops into <code>&lt;!-- SPONSOR SLOT --&gt;</code>.
                        </div>

                        <div class="em-trigger-map">
                            <table class="em-trigger-table">
                                <thead>
                                    <tr>
                                        <th>Email Trigger</th>
                                        <th>Template Key</th>
                                        <th>Active Ad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in triggerRows" :key="row.templateKey">
                                        <td>{{ row.trigger }}</td>
                                        <td><code>{{ row.templateKey }}</code></td>
                                        <td>{{ row.activeAd?.companyName ?? 'No active ad' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="em-ad-list">
                            <article v-for="ad in filteredSponsors" :key="ad.id" class="em-ad-item">
                                <img :src="ad.image" class="em-ad-thumb" alt="">
                                <div class="em-ad-meta">
                                    <h3>{{ ad.companyName }}</h3>
                                    <div class="em-ad-headline">{{ ad.headline }}</div>
                                    <div class="em-ad-msg">{{ ad.message }}</div>
                                    <div class="em-tag-row">
                                        <span class="em-tag">{{ getCategoryLabel(ad.emailCategory) }}</span>
                                        <span class="em-tag green">{{ ad.status }}</span>
                                        <span class="em-tag gold">P{{ ad.priority }}</span>
                                        <span class="em-flag-tag" :class="{ 'all-regions': !ad.regions.length }">{{ regionLabel(ad.regions) }}</span>
                                    </div>
                                </div>
                                <div class="em-ad-actions">
                                    <button type="button" class="em-btn em-btn-secondary em-btn-sm" @click="editSponsor(ad.id)">Edit</button>
                                    <button type="button" class="em-btn em-btn-danger em-btn-sm" @click="deleteSponsor(ad.id)">Delete</button>
                                </div>
                            </article>
                            <div v-if="!filteredSponsors.length" class="em-empty-state">No sponsor ads in this category yet.</div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="em-bottom-layout">
                <section class="em-panel">
                    <div class="em-panel-header">
                        <h2>📧 Email Preview</h2>
                        <p>See exactly how the active ad renders inside the email slot for each category and country.</p>
                    </div>
                    <div class="em-panel-body">
                        <div class="em-row em-preview-controls">
                            <div class="em-field">
                                <label>Preview Category</label>
                                <select v-model="previewCategory">
                                    <option v-for="cat in emailCategories" :key="cat.key" :value="cat.key">{{ cat.label }}</option>
                                </select>
                            </div>
                            <div class="em-field">
                                <label>Preview Country</label>
                                <select v-model="previewCountry">
                                    <option v-for="region in regions" :key="region.key" :value="region.key">{{ region.flag }} {{ region.name }}</option>
                                </select>
                                <div class="em-detected-note">{{ selectedRegion.flag }} {{ selectedRegion.name }} branding and {{ selectedRegion.currency }} currency context.</div>
                            </div>
                        </div>

                        <div class="em-email-preview-wrap">
                            <div class="em-ep-card">
                                <div class="em-ep-logo-bar">Link<span>Up</span> <span class="green">Vibes</span> <span>{{ selectedRegion.flag }}</span></div>
                                <div class="em-ep-body">
                                    <div class="em-ep-pill">{{ selectedCategory.icon }} {{ selectedCategory.shortLabel }}</div>
                                    <div class="em-ep-title">{{ selectedCategory.preview[0] }}</div>
                                    <div class="em-ep-sub">{{ selectedCategory.preview[1] }}</div>
                                    <a href="#" class="em-ep-cta">Main Email CTA</a>
                                </div>
                                <div class="em-ep-ad-slot">
                                    <div v-if="activeAdForPreview" class="em-preview-ad">
                                        <img :src="activeAdForPreview.image" alt="">
                                        <div>
                                            <div class="em-sponsored-by">Sponsored By</div>
                                            <strong>{{ activeAdForPreview.companyName }}</strong>
                                            <p>{{ activeAdForPreview.headline }}</p>
                                            <small>{{ activeAdForPreview.message }}</small>
                                            <a :href="activeAdForPreview.ctaUrl" target="_blank">{{ activeAdForPreview.ctaText }} -></a>
                                        </div>
                                    </div>
                                    <div v-else class="em-empty-state">No active ad for this category and country.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="false" class="em-panel">
                    <div class="em-panel-header">
                        <h2>👨‍💻 Developer Integration</h2>
                        <p>Drop this into your email-sending service. The output matches the LinkUp Vibes sponsor slot pattern.</p>
                    </div>
                    <div class="em-panel-body">
                        <div class="em-code-box"><pre><code>// LINKUP VIBES - MULTI-REGION EMAIL PIPELINE
const REGION_CONFIG = {
  bs: { name: "Bahamas", flag: "🇧🇸", currency: "BSD$" },
  jm: { name: "Jamaica", flag: "🇯🇲", currency: "JMD$" },
  tt: { name: "Trinidad & Tobago", flag: "🇹🇹", currency: "TTD$" }
};

function getAdForEmail(emailCategory, countryKey, ads) {
  const now = new Date();
  return ads
    .filter(ad => ad.status === "active")
    .filter(ad => ad.emailCategory === emailCategory || ad.emailCategory === "all")
    .filter(ad => !ad.regions?.length || ad.regions.includes(countryKey))
    .filter(ad => new Date(ad.startDate) <= now && new Date(ad.endDate) >= now)
    .sort((a, b) => Number(a.priority) - Number(b.priority))[0] ?? null;
}

function renderSponsoredAd(ad) {
  if (!ad) return "";
  return `&lt;div class="sponsor-slot"&gt;
    &lt;strong&gt;Sponsored By ${ad.companyName}&lt;/strong&gt;
    &lt;b&gt;${ad.headline}&lt;/b&gt;
    &lt;p&gt;${ad.message}&lt;/p&gt;
    &lt;a href="${ad.ctaUrl}"&gt;${ad.ctaText} -&gt;&lt;/a&gt;
  &lt;/div&gt;`;
}</code></pre></div>
                    </div>
                </section>
            </div>
        </div>

        <div v-else-if="activeTab === 'analytics'" class="em-root">
            <div class="em-stats-row">
                <div class="em-stat-card"><div class="em-stat-icon green">✉️</div><div><div class="em-stat-num">{{ props.num(totalImpressions) }}</div><div class="em-stat-label">Email Impressions</div></div></div>
                <div class="em-stat-card"><div class="em-stat-icon blue">📬</div><div><div class="em-stat-num">{{ props.num(totalOpens) }}</div><div class="em-stat-label">Total Opens</div></div></div>
                <div class="em-stat-card"><div class="em-stat-icon amber">👆</div><div><div class="em-stat-num">{{ props.num(totalClicks) }}</div><div class="em-stat-label">Ad Clicks</div></div></div>
                <div class="em-stat-card"><div class="em-stat-icon purple">💵</div><div><div class="em-stat-num">{{ props.fmt(totalRevenue) }}</div><div class="em-stat-label">Email Ad Revenue</div></div></div>
            </div>

            <div class="em-analytics-grid">
                <div class="em-panel em-chart-panel">
                    <div class="em-panel-header"><h2>Revenue by Sponsor</h2></div>
                    <div class="em-panel-body"><div v-for="bar in revenueBars" :key="bar.label" class="em-mini-bar-row"><div class="em-mini-bar-label">{{ bar.label }}</div><div class="em-mini-bar-track"><span class="em-mini-bar-fill teal" :style="{ width: bar.width }"></span></div><div class="em-mini-bar-val">{{ bar.display }}</div></div></div>
                </div>
                <div class="em-panel em-chart-panel">
                    <div class="em-panel-header"><h2>Open Rate by Email Category</h2></div>
                    <div class="em-panel-body"><div v-for="bar in openRateBars" :key="bar.label" class="em-mini-bar-row"><div class="em-mini-bar-label">{{ bar.label }}</div><div class="em-mini-bar-track"><span class="em-mini-bar-fill blue" :style="{ width: bar.width }"></span></div><div class="em-mini-bar-val">{{ bar.display }}</div></div></div>
                </div>
                <div class="em-panel em-chart-panel">
                    <div class="em-panel-header"><h2>Ad Clicks by Region</h2></div>
                    <div class="em-panel-body"><div v-for="bar in regionClickBars" :key="bar.label" class="em-mini-bar-row"><div class="em-mini-bar-label">{{ bar.label }}</div><div class="em-mini-bar-track"><span class="em-mini-bar-fill purple" :style="{ width: bar.width }"></span></div><div class="em-mini-bar-val">{{ bar.display }}</div></div></div>
                </div>
                <div class="em-panel em-chart-panel">
                    <div class="em-panel-header"><h2>CTR by Email Category</h2></div>
                    <div class="em-panel-body"><div v-for="bar in ctrBars" :key="bar.label" class="em-mini-bar-row"><div class="em-mini-bar-label">{{ bar.label }}</div><div class="em-mini-bar-track"><span class="em-mini-bar-fill amber" :style="{ width: bar.width }"></span></div><div class="em-mini-bar-val">{{ bar.display }}</div></div></div>
                </div>
            </div>

            <div class="em-panel em-table-panel">
                <div class="em-panel-header"><h2>All Email Sponsors - Performance Table</h2></div>
                <div class="em-table-scroll">
                    <table class="em-report-table">
                        <thead>
                            <tr>
                                <th>Sponsor</th>
                                <th>Category</th>
                                <th>Regions</th>
                                <th>Impressions</th>
                                <th>Opens</th>
                                <th>Clicks</th>
                                <th>Open Rate</th>
                                <th>CTR</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in analyticsRows" :key="row.id">
                                <td><strong>{{ row.companyName }}</strong></td>
                                <td>{{ row.categoryLabel }}</td>
                                <td>{{ row.regionLabel }}</td>
                                <td>{{ props.num(row.impressions) }}</td>
                                <td>{{ props.num(row.opens) }}</td>
                                <td>{{ props.num(row.clicks) }}</td>
                                <td><strong class="em-green">{{ row.openRate }}%</strong></td>
                                <td><strong class="em-purple">{{ row.ctr }}%</strong></td>
                                <td><strong>{{ props.fmt(row.revenue) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-else class="em-root">
            <div v-if="!reportGenerated" class="em-panel">
                <div class="em-panel-header">
                    <h2>Select Email Sponsor to Report On</h2>
                    <p>Generate a detailed performance report for an individual email sponsor - ready to print or email directly to the advertiser.</p>
                </div>
                <div class="em-panel-body">
                    <div class="em-report-picker-grid">
                        <button
                            v-for="sponsor in sponsors"
                            :key="sponsor.id"
                            type="button"
                            class="em-report-picker"
                            :class="{ selected: selectedSponsorId === sponsor.id }"
                            @click="selectedSponsorId = sponsor.id"
                        >
                            <span>📧</span>
                            <strong>{{ sponsor.companyName }}</strong>
                            <small>{{ getCategoryLabel(sponsor.emailCategory) }} - {{ props.fmt(sponsor.revenue) }}/mo</small>
                        </button>
                    </div>
                    <div class="em-report-actions">
                        <button type="button" class="em-btn em-btn-secondary" @click="printReport">🖨️ Print / PDF</button>
                        <button type="button" class="em-btn em-btn-primary em-report-generate" :disabled="!selectedSponsorId" @click="generateReport">Generate Report -></button>
                    </div>
                </div>
            </div>

            <div v-if="reportGenerated && selectedReportSponsor" ref="reportOutputRef" class="em-email-report-shell">
                <div class="em-email-report">
                    <div class="em-report-header-band">
                        <div class="em-report-brand">
                            <div class="em-report-icon">✉️</div>
                            <div>
                                <div class="em-report-kicker">Email Sponsor Report</div>
                                <div class="em-report-name">{{ selectedReportSponsor.companyName }}</div>
                            </div>
                        </div>
                        <div class="em-report-meta">Generated July 15, 2026 - LinkUp Vibes Ad Platform - linkupvibes.com</div>
                    </div>

                    <div class="em-report-kpi-grid">
                        <div class="em-report-kpi" style="--kpi-color:#06b6d4"><div>✉️</div><strong>{{ (selectedReportSponsor.impressions / 1000).toFixed(1) }}k</strong><span>Email Impressions</span></div>
                        <div class="em-report-kpi" style="--kpi-color:#3b82f6"><div>📬</div><strong>{{ (selectedReportSponsor.opens / 1000).toFixed(1) }}k</strong><span>Opens</span></div>
                        <div class="em-report-kpi" style="--kpi-color:#f59e0b"><div>👆</div><strong>{{ props.num(selectedReportSponsor.clicks) }}</strong><span>Ad Clicks</span></div>
                        <div class="em-report-kpi" style="--kpi-color:#fb923c"><div>📊</div><strong>{{ rate(selectedReportSponsor.opens, selectedReportSponsor.impressions) }}%</strong><span>Open Rate</span></div>
                        <div class="em-report-kpi" style="--kpi-color:#8b5cf6"><div>%</div><strong>{{ rate(selectedReportSponsor.clicks, selectedReportSponsor.opens) }}%</strong><span>Click-Through Rate</span></div>
                        <div class="em-report-kpi" style="--kpi-color:#16a34a"><div>💵</div><strong>{{ props.fmt(selectedReportSponsor.revenue) }}</strong><span>Ad Spend</span></div>
                    </div>

                    <div class="em-report-two-col">
                        <div class="em-report-card">
                            <h3>Engagement Funnel</h3>
                            <div class="em-funnel-row">
                                <span>Delivered</span>
                                <div><b style="width:100%">{{ (selectedReportSponsor.impressions / 1000).toFixed(1) }}k</b></div>
                                <strong>100%</strong>
                            </div>
                            <div class="em-funnel-row">
                                <span>Opened</span>
                                <div><b class="blue" :style="{ width: `${rate(selectedReportSponsor.opens, selectedReportSponsor.impressions)}%` }">{{ (selectedReportSponsor.opens / 1000).toFixed(1) }}k</b></div>
                                <strong>{{ rate(selectedReportSponsor.opens, selectedReportSponsor.impressions) }}%</strong>
                            </div>
                            <div class="em-funnel-row">
                                <span>Clicked Ad</span>
                                <div><b class="cyan" :style="{ width: `${Math.max(Number(rate(selectedReportSponsor.clicks, selectedReportSponsor.impressions)), 2)}%` }">{{ props.num(selectedReportSponsor.clicks) }}</b></div>
                                <strong>{{ rate(selectedReportSponsor.clicks, selectedReportSponsor.impressions) }}%</strong>
                            </div>
                        </div>

                        <div class="em-report-card">
                            <h3>Campaign Details</h3>
                            <div class="em-detail-row"><span>Email Category</span><strong>{{ getCategoryLabel(selectedReportSponsor.emailCategory) }}</strong></div>
                            <div class="em-detail-row"><span>Target Regions</span><strong>{{ regionLabel(selectedReportSponsor.regions) }}</strong></div>
                            <div class="em-detail-row"><span>Campaign Start</span><strong>{{ selectedReportSponsor.startDate }}</strong></div>
                            <div class="em-detail-row"><span>Campaign End</span><strong>{{ selectedReportSponsor.endDate }}</strong></div>
                            <div class="em-detail-row"><span>Priority Level</span><strong>P{{ selectedReportSponsor.priority }}</strong></div>
                            <div class="em-detail-row"><span>Cost Per Click</span><strong>{{ props.fmt(selectedReportSponsor.revenue / Math.max(selectedReportSponsor.clicks, 1)) }}</strong></div>
                        </div>
                    </div>

                    <div class="em-summary-callout">
                        <strong>{{ selectedReportSponsor.companyName }}</strong>'s email sponsor campaign is delivering a
                        <strong>{{ rate(selectedReportSponsor.opens, selectedReportSponsor.impressions) }}% open rate</strong>. The
                        <strong>{{ rate(selectedReportSponsor.clicks, selectedReportSponsor.opens) }}% click-through rate</strong> from opens shows solid value against industry email ad rates.
                    </div>

                    <div class="em-report-card">
                        <h3>💡 Recommendations</h3>
                        <div class="em-recommendation"><strong>Consider Regional Targeting</strong><span>Test targeting specific high performing markets against the all-regions baseline.</span></div>
                        <div class="em-recommendation"><strong>Renew Before Campaign Ends</strong><span>Renewing 30 days early prevents impression gaps and keeps priority placement active.</span></div>
                        <div class="em-recommendation"><strong>Add a Swipe Ad to Complete Coverage</strong><span>Pair email sponsorship with a swipe campaign to double touchpoints across both platforms.</span></div>
                    </div>

                    <div class="em-report-footer">
                        <span>LinkUp Vibes - linkupvibes.com - Confidential</span>
                        <div>
                            <button type="button" class="em-btn em-btn-secondary" @click="resetReport">Back</button>
                            <button type="button" class="em-btn em-btn-primary">✉️ Email Report</button>
                            <button type="button" class="em-btn em-btn-secondary" @click="printReport">🖨️ Print PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.em-section {
    background: #f0ebe0;
    border-radius: 16px;
    padding: 20px;
    min-height: 60vh;
}

.em-topnav {
    background: #fffdf8;
    border: 1px solid #e0d5be;
    border-radius: 20px;
    padding: 18px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
    box-shadow: 0 6px 22px rgba(56, 42, 15, .09);
    flex-wrap: wrap;
}

.em-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.em-brand-icon {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #7c3aed, #4c1d95);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.em-title {
    font-size: 17px;
    font-weight: 900;
    color: #1e1b4b;
    letter-spacing: -.3px;
}

.em-subtitle {
    font-size: 12px;
    color: #667085;
}

.em-nav-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.em-navbtn {
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    color: #6d28d9;
    border-radius: 10px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    font-family: inherit;
    transition: all .15s;
}

.em-navbtn.active {
    background: linear-gradient(135deg, #7c3aed, #4c1d95);
    color: #fff;
    border-color: #7c3aed;
}

.em-root {
    padding: 0;
}

.em-eyebrow {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 12px;
}

.em-regions-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 24px;
}

.em-region-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fffdf8;
    border: 1px solid #e0d5be;
    border-radius: 100px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 700;
    color: #344054;
    box-shadow: 0 2px 6px rgba(56, 42, 15, .06);
}

.em-rp-flag {
    font-size: 16px;
    line-height: 1;
}

.em-rp-count {
    background: #f5f3ff;
    color: #6d28d9;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 100px;
    border: 1px solid #ede9fe;
}

.em-category-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.em-category-card {
    background: #fffdf8;
    border: 1px solid #e0d5be;
    border-radius: 18px;
    padding: 16px 14px;
    box-shadow: 0 4px 14px rgba(56, 42, 15, .06);
    position: relative;
    overflow: hidden;
}

.em-category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--cat-color);
    border-radius: 18px 18px 0 0;
}

.em-cat-icon {
    font-size: 26px;
    display: block;
    margin-bottom: 8px;
}

.em-category-card h3 {
    font-size: 13px;
    font-weight: 900;
    color: #101828;
    margin-bottom: 4px;
    line-height: 1.3;
}

.em-category-card p {
    font-size: 11px;
    color: #9ca3af;
    line-height: 1.45;
}

.em-cat-count {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 10px;
    font-weight: 800;
    background: rgba(255, 255, 255, .9);
    border: 1px solid #ede9fe;
    color: #6d28d9;
    padding: 3px 7px;
    border-radius: 100px;
}

.em-layout {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 20px;
}

.em-bottom-layout {
    margin-top: 20px;
}

.em-panel {
    background: #fffdf8;
    border: 1px solid #e0d5be;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(56, 42, 15, .08);
}

.em-panel-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f0e8d4;
}

.em-panel-header h2 {
    font-size: 15px;
    font-weight: 900;
    color: #1e1b4b;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.em-panel-header p {
    font-size: 13px;
    color: #667085;
    line-height: 1.5;
}

.em-panel-body {
    padding: 20px 24px;
}

.em-form-group {
    background: #f9f8f5;
    border: 1px solid #ede8dc;
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 16px;
}

.em-form-group-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 12px;
    display: block;
}

.em-field {
    margin-bottom: 12px;
}

.em-field:last-child {
    margin-bottom: 0;
}

.em-field label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: #344054;
    margin-bottom: 5px;
}

.em-field label span {
    font-weight: 600;
    color: #9ca3af;
    font-size: 10px;
}

.em-field input,
.em-field select,
.em-field textarea,
.em-region-check-label input {
    font-family: inherit;
}

.em-field input,
.em-field select,
.em-field textarea {
    width: 100%;
    border: 1px solid #e0d5be;
    border-radius: 10px;
    padding: 9px 12px;
    font-size: 13px;
    outline: none;
    background: #fff;
    color: #101828;
    transition: border-color .15s, box-shadow .15s;
}

.em-field textarea {
    min-height: 72px;
    resize: vertical;
}

.em-field input:focus,
.em-field select:focus,
.em-field textarea:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
}

.em-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.em-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    border: 0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
    transition: opacity .15s;
}

.em-btn:hover {
    opacity: .86;
}

.em-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.em-btn-primary {
    background: linear-gradient(135deg, #7c3aed, #4c1d95);
    color: #fff;
    box-shadow: 0 6px 18px rgba(124, 58, 237, .28);
    padding: 13px;
}

.em-btn-secondary {
    background: #f2f4f7;
    color: #344054;
}

.em-btn-danger {
    background: #fee4e2;
    color: #b42318;
}

.em-btn-sm {
    padding: 7px 11px;
    font-size: 12px;
    border-radius: 8px;
}

.em-full {
    width: 100%;
    margin-bottom: 10px;
}

.em-form-actions {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 10px;
}

.em-form-actions .em-btn-primary {
    width: 100%;
}

.em-help,
.em-detected-note {
    font-size: 12px;
    color: #667085;
    margin-bottom: 10px;
    line-height: 1.5;
}

.em-detected-note {
    font-size: 11px;
    color: #9ca3af;
    margin: 5px 0 0;
}

.em-category-manager-list {
    display: grid;
    gap: 8px;
    margin-top: 12px;
}

.em-manager-row {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    gap: 10px;
    align-items: center;
    padding: 9px 10px;
    border: 1px solid #ede8dc;
    border-radius: 10px;
    background: #fff;
}

.em-manager-row strong {
    display: block;
    font-size: 12px;
    color: #101828;
}

.em-manager-row small {
    display: block;
    font-size: 10px;
    color: #9ca3af;
    margin-top: 2px;
}

.em-manager-icon {
    font-size: 20px;
}

.em-mini-danger {
    border: 0;
    background: #fee4e2;
    color: #b42318;
    border-radius: 8px;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-weight: 900;
}

.em-mini-danger:disabled {
    opacity: .35;
    cursor: not-allowed;
}

.em-region-all-btn {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px dashed #c4b5fd;
    background: #faf8ff;
    color: #6d28d9;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    user-select: none;
}

.em-region-scroll {
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid #ede8dc;
    border-radius: 10px;
    padding: 8px;
    background: #fafaf8;
}

.em-region-group-header {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #9ca3af;
    padding: 8px 4px 5px;
    display: block;
}

.em-region-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 5px;
}

.em-region-check-label {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 6px 9px;
    border-radius: 7px;
    border: 1px solid #ede8dc;
    background: #fff;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    color: #344054;
    transition: border-color .15s, background .15s;
    user-select: none;
}

.em-region-check-label:hover {
    border-color: #7c3aed;
    background: #faf8ff;
}

.em-creative-preview {
    width: 100%;
    max-height: 160px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e0d5be;
}

.em-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 14px;
}

.em-tab {
    border: 1px solid #e0d5be;
    background: #fff;
    color: #667085;
    border-radius: 100px;
    padding: 7px 13px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    font-family: inherit;
    transition: all .15s;
}

.em-tab.active {
    background: #6d28d9;
    color: #fff;
    border-color: #6d28d9;
}

.em-notice {
    background: #fefce8;
    border: 1px solid #fde68a;
    color: #713f12;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.55;
    margin-bottom: 14px;
}

.em-notice code,
.em-trigger-table code {
    background: rgba(124, 58, 237, .08);
    color: #6d28d9;
    padding: 2px 5px;
    border-radius: 5px;
}

.em-trigger-map {
    margin-bottom: 16px;
    overflow-x: auto;
}

.em-trigger-table,
.em-report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.em-trigger-table th,
.em-report-table th {
    text-align: left;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #9ca3af;
    padding: 8px 12px;
    border-bottom: 1px solid #ede8dc;
}

.em-trigger-table td,
.em-report-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f5f2ec;
    color: #344054;
}

.em-ad-list {
    display: grid;
    gap: 10px;
}

.em-ad-item {
    border: 1px solid #ede8dc;
    border-radius: 16px;
    padding: 12px;
    background: #fff;
    display: grid;
    grid-template-columns: 96px 1fr auto;
    gap: 12px;
    align-items: start;
}

.em-ad-thumb {
    width: 96px;
    height: 68px;
    border-radius: 10px;
    object-fit: cover;
    background: #f2f4f7;
    border: 1px solid #ede8dc;
    display: block;
}

.em-ad-meta h3 {
    font-size: 14px;
    font-weight: 900;
    color: #101828;
    margin-bottom: 3px;
}

.em-ad-headline {
    font-size: 12px;
    color: #344054;
    font-weight: 700;
    margin-bottom: 2px;
}

.em-ad-msg {
    font-size: 12px;
    color: #9ca3af;
    line-height: 1.4;
    margin-bottom: 8px;
}

.em-tag-row {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.em-tag,
.em-flag-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 8px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 800;
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ede9fe;
}

.em-tag.green {
    background: #ecfdf3;
    color: #027a48;
    border-color: #a7f3d0;
}

.em-tag.gold {
    background: #fffbeb;
    color: #92400e;
    border-color: #fde68a;
}

.em-flag-tag {
    background: #f0fdf4;
    color: #15803d;
    border-color: #bbf7d0;
}

.em-flag-tag.all-regions {
    background: #f5f3ff;
    color: #6d28d9;
    border-color: #ede9fe;
}

.em-ad-actions {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.em-empty-state {
    text-align: center;
    padding: 32px 16px;
    color: #9ca3af;
    border: 1px dashed #e0d5be;
    border-radius: 14px;
    background: #fafaf8;
    font-size: 13px;
}

.em-email-preview-wrap {
    background: #ede8dc;
    border-radius: 18px;
    padding: 20px;
}

.em-ep-card {
    background: #fffdf8;
    border-radius: 28px;
    overflow: hidden;
    border: 1px solid #e0d5be;
    box-shadow: 0 12px 36px rgba(56, 42, 15, .13);
    max-width: 640px;
    margin: 0 auto;
}

.em-ep-logo-bar {
    padding: 16px 28px 14px;
    text-align: center;
    border-bottom: 1px solid #f0e8d4;
    font-size: 16px;
    font-weight: 900;
    color: #1e1b4b;
    letter-spacing: -.3px;
}

.em-ep-logo-bar span {
    color: #7c3aed;
}

.em-ep-logo-bar .green {
    color: #6fbd1e;
}

.em-ep-body {
    padding: 28px;
    text-align: center;
}

.em-ep-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    border-radius: 100px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 800;
    color: #6d28d9;
    letter-spacing: .5px;
    margin-bottom: 12px;
}

.em-ep-title {
    font-size: 22px;
    font-weight: 900;
    color: #1e1b4b;
    margin-bottom: 6px;
    line-height: 1.2;
}

.em-ep-sub {
    font-size: 13px;
    color: #667085;
    line-height: 1.6;
    margin-bottom: 18px;
}

.em-ep-cta {
    display: inline-block;
    background: linear-gradient(135deg, #7c3aed, #4c1d95);
    color: #fff;
    text-decoration: none;
    padding: 11px 26px;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 800;
}

.em-ep-ad-slot {
    padding: 0 32px 22px;
}

.em-preview-ad {
    display: grid;
    grid-template-columns: 96px 1fr;
    gap: 14px;
    background: #f9f8f5;
    border: 1px solid #ede8dc;
    border-radius: 16px;
    padding: 14px;
}

.em-preview-ad img {
    width: 96px;
    height: 76px;
    border-radius: 12px;
    object-fit: cover;
}

.em-preview-ad strong,
.em-preview-ad p,
.em-preview-ad small,
.em-preview-ad a {
    display: block;
}

.em-preview-ad p {
    font-size: 13px;
    font-weight: 800;
    color: #344054;
    margin-top: 3px;
}

.em-preview-ad small {
    color: #667085;
    line-height: 1.45;
    margin-top: 3px;
}

.em-preview-ad a {
    color: #6d28d9;
    font-size: 12px;
    font-weight: 900;
    margin-top: 8px;
}

.em-sponsored-by {
    font-size: 9px;
    font-weight: 900;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.em-code-box {
    background: #0f172a;
    border-radius: 16px;
    overflow: auto;
    max-height: 440px;
    padding: 20px 22px;
}

.em-code-box pre {
    font-family: Monaco, Menlo, Consolas, monospace;
    font-size: 12px;
    line-height: 1.75;
    color: #94a3b8;
    white-space: pre;
}

.em-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}

.em-stat-card {
    background: #fffdf8;
    border: 1px solid #e0d5be;
    border-radius: 18px;
    padding: 16px;
    display: flex;
    gap: 12px;
    align-items: center;
    box-shadow: 0 4px 14px rgba(56, 42, 15, .06);
}

.em-stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.em-stat-icon.green { background: #16a34a; }
.em-stat-icon.blue { background: #2563eb; }
.em-stat-icon.amber { background: #f59e0b; }
.em-stat-icon.purple { background: #7c3aed; }

.em-stat-num {
    font-size: 20px;
    font-weight: 900;
    color: #101828;
}

.em-stat-label {
    font-size: 11px;
    font-weight: 800;
    color: #667085;
}

.em-analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.em-chart-panel .em-panel-body {
    display: grid;
    gap: 10px;
}

.em-mini-bar-row {
    display: grid;
    grid-template-columns: 120px 1fr 54px;
    gap: 10px;
    align-items: center;
    font-size: 12px;
}

.em-mini-bar-label {
    font-weight: 800;
    color: #344054;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.em-mini-bar-track {
    height: 9px;
    border-radius: 100px;
    background: #f0e8d4;
    overflow: hidden;
}

.em-mini-bar-fill {
    display: block;
    height: 100%;
    border-radius: inherit;
}

.em-mini-bar-fill.teal { background: #14b8a6; }
.em-mini-bar-fill.blue { background: #3b82f6; }
.em-mini-bar-fill.purple { background: #8b5cf6; }
.em-mini-bar-fill.amber { background: #f59e0b; }

.em-mini-bar-val {
    font-weight: 900;
    color: #101828;
    text-align: right;
}

.em-table-panel {
    margin-top: 16px;
}

.em-table-scroll {
    overflow-x: auto;
}

.em-green {
    color: #14b8a6;
}

.em-purple {
    color: #7c3aed;
}

.em-report-picker-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.em-report-picker {
    border: 1px solid #e0d5be;
    border-radius: 18px;
    background: #fff;
    padding: 18px;
    text-align: left;
    cursor: pointer;
    font-family: inherit;
    transition: all .15s;
}

.em-report-picker.selected {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
}

.em-report-picker span {
    display: block;
    font-size: 24px;
    margin-bottom: 10px;
}

.em-report-picker strong,
.em-report-picker small {
    display: block;
}

.em-report-picker strong {
    color: #101828;
    font-size: 14px;
    margin-bottom: 4px;
}

.em-report-picker small {
    color: #667085;
    font-size: 12px;
    font-weight: 700;
}

.em-report-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #ede8dc;
}

.em-report-generate {
    width: auto;
}

.em-email-report-shell {
    margin-top: 20px;
}

.em-email-report {
    background: #fff;
    border: 1px solid #d8dee7;
    border-radius: 10px;
    padding: 36px;
    max-width: 1050px;
    margin: 0 auto;
    box-shadow: 0 20px 50px rgba(15, 23, 42, .08);
}

.em-report-header-band {
    background: #128d7d;
    border-radius: 10px;
    color: #fff;
    padding: 26px 30px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: flex-start;
    margin-bottom: 28px;
}

.em-report-brand {
    display: flex;
    align-items: center;
    gap: 16px;
}

.em-report-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .15);
    display: flex;
    align-items: center;
    justify-content: center;
}

.em-report-kicker {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: .7;
    font-weight: 900;
}

.em-report-name {
    font-size: 24px;
    font-weight: 900;
}

.em-report-meta {
    font-size: 12px;
    opacity: .7;
    text-align: right;
}

.em-report-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 0;
}

.em-report-kpi {
    border: 1px solid #d8dee7;
    border-top: 3px solid var(--kpi-color);
    border-radius: 8px;
    padding: 14px;
}

.em-report-kpi strong,
.em-report-kpi span {
    display: block;
}

.em-report-kpi strong {
    color: #050816;
    font-size: 26px;
    font-weight: 900;
    margin-top: 8px;
}

.em-report-kpi span {
    font-size: 11px;
    color: #344054;
}

.em-report-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin: 0 0 20px;
}

.em-report-card {
    border: 1px solid #d8dee7;
    border-radius: 8px;
    padding: 18px 20px;
    margin-top: 20px;
}

.em-report-card h3 {
    font-size: 14px;
    color: #050816;
    font-weight: 900;
    margin-bottom: 16px;
}

.em-funnel-row {
    display: grid;
    grid-template-columns: 86px 1fr 46px;
    gap: 12px;
    align-items: center;
    margin-bottom: 14px;
    font-size: 12px;
}

.em-funnel-row > span,
.em-funnel-row > strong {
    font-weight: 900;
    color: #101828;
}

.em-funnel-row > div {
    height: 22px;
    border-radius: 6px;
    background: #edf3ee;
    overflow: hidden;
}

.em-funnel-row b {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    height: 100%;
    min-width: 22px;
    padding-right: 6px;
    border-radius: inherit;
    background: #14b8a6;
    color: #fff;
    font-size: 10px;
}

.em-funnel-row b.blue { background: #4979f5; }
.em-funnel-row b.cyan { background: #20bde3; }

.em-detail-row {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
    font-size: 12px;
}

.em-detail-row span {
    color: #667085;
}

.em-detail-row strong {
    color: #101828;
    text-align: right;
}

.em-summary-callout {
    border-left: 4px solid #22d3ee;
    background: #f5fde8;
    border-radius: 0 8px 8px 0;
    padding: 18px 22px;
    font-size: 13px;
    color: #344054;
    line-height: 1.7;
}

.em-recommendation {
    background: #f5faf2;
    border-radius: 8px;
    padding: 14px 16px;
    margin-top: 10px;
}

.em-recommendation strong,
.em-recommendation span {
    display: block;
}

.em-recommendation strong {
    color: #101828;
    font-size: 12px;
}

.em-recommendation span {
    color: #344054;
    font-size: 12px;
    line-height: 1.5;
    margin-top: 2px;
}

.em-report-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-top: 34px;
    padding-top: 20px;
    border-top: 1px solid #d8dee7;
    font-size: 11px;
    color: #667085;
}

.em-report-footer > div {
    display: flex;
    gap: 8px;
}

@media (max-width: 1100px) {
    .em-layout,
    .em-analytics-grid,
    .em-report-two-col {
        grid-template-columns: 1fr;
    }

    .em-category-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .em-stats-row,
    .em-report-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .em-section {
        padding: 14px;
    }

    .em-topnav {
        flex-direction: column;
        align-items: flex-start;
    }

    .em-category-grid,
    .em-stats-row,
    .em-report-picker-grid,
    .em-report-kpi-grid {
        grid-template-columns: 1fr;
    }

    .em-row,
    .em-region-grid,
    .em-preview-ad,
    .em-ad-item {
        grid-template-columns: 1fr;
    }

    .em-ad-thumb {
        width: 100%;
        height: 140px;
    }

    .em-form-actions,
    .em-report-footer,
    .em-report-header-band {
        flex-direction: column;
        display: flex;
        align-items: stretch;
    }

    .em-report-meta {
        text-align: left;
    }
}
</style>
