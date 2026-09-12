<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type Tab = 'manage' | 'analytics' | 'reports';

type Placement = {
    key: string;
    icon: string;
    name: string;
    desc: string;
    price: string;
};

type CountryChip = {
    key: string;
    group: 'caribbean' | 'latin' | 'global';
    flag: string;
    name: string;
};

type NewsPackage = {
    key: string;
    icon: string;
    name: string;
    price: number;
    duration: string;
    type: 'placement' | 'email' | 'bundle';
    badge?: string;
    features: string[];
};

type Sponsor = {
    id: number;
    icon: string;
    name: string;
    placementKey: string;
    placement: string;
    category: string;
    headline?: string;
    territory: string;
    territoryFlags: string;
    territoryKeys?: string[];
    packageKey?: string;
    packageName?: string;
    packageDuration?: string;
    status: 'Active' | 'Draft';
    rate: string;
    impressions: number;
    clicks: number;
    ctr: string;
    cost: number;
    starts: string;
    ends: string;
};

const props = defineProps<{
    newsAds?: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const activeTab = ref<Tab>('manage');
const selectedPlacement = ref('homepage-feature');
const selectedPackage = ref('anchor');
const selectedCountries = ref<string[]>(['jm', 'bs']);
const selectedScopes = ref<string[]>(['caribbean']);
const managePackagesOpen = ref(false);
const addPackageOpen = ref(false);
const selectedReportId = ref<number | null>(null);
const reportGenerated = ref(false);
const reportOutputRef = ref<HTMLElement | null>(null);
const editingSponsorId = ref<number | null>(null);
const isSaving = ref(false);
const persistedSponsorIds = ref<Set<number>>(new Set());

const form = ref({
    advertiser: '',
    category: 'All Categories',
    headline: '',
    startDate: '2026-07-15',
    endDate: '2026-08-14',
});

const packageDraft = ref({
    key: '',
    icon: '📦',
    name: '',
    price: 0,
    duration: '',
    type: 'placement' as NewsPackage['type'],
    badge: '',
    features: '',
});

const addPackageDraft = ref({
    icon: '📦',
    name: '',
    price: 0,
    duration: '',
    type: 'placement' as NewsPackage['type'],
    badge: '',
    features: '',
});

const placementTypes: Placement[] = [
    { key: 'article-banner', icon: '📐', name: 'Article Banner', desc: 'Top/bottom banner on any article', price: '$50-$200/week' },
    { key: 'in-article-sponsor', icon: '✍️', name: 'In-Article Sponsor', desc: '"Brought to you by" inline', price: '$150-$500/article' },
    { key: 'newsletter-spot', icon: '📩', name: 'Newsletter Spot', desc: 'Daily/weekly digest', price: '$300-$1,500/send' },
    { key: 'breaking-alert', icon: '🚨', name: 'Breaking Alert Sponsor', desc: 'Sponsored breaking news push', price: '$500-$2,000/alert' },
    { key: 'category-takeover', icon: '🏷️', name: 'Category Takeover', desc: 'Exclusive category ownership', price: '$1,000-$5,000/week' },
    { key: 'homepage-feature', icon: '🏠', name: 'Homepage Feature', desc: 'Front-page hero placement', price: '$500-$2,500/week' },
    { key: 'video-roll', icon: '🎥', name: 'Video Roll', desc: 'Pre/mid-roll on video content', price: '$200-$800/week' },
    { key: 'podcast-sponsor', icon: '🎙️', name: 'Podcast Sponsor', desc: 'Caribbean 360 podcast segment', price: '$250-$1,000/episode' },
];

const countries: CountryChip[] = [
    { key: 'jm', group: 'caribbean', flag: '🇯🇲', name: 'Jamaica' },
    { key: 'bs', group: 'caribbean', flag: '🇧🇸', name: 'Bahamas' },
    { key: 'tt', group: 'caribbean', flag: '🇹🇹', name: 'Trinidad' },
    { key: 'bb', group: 'caribbean', flag: '🇧🇧', name: 'Barbados' },
    { key: 'ht', group: 'caribbean', flag: '🇭🇹', name: 'Haiti' },
    { key: 'do', group: 'caribbean', flag: '🇩🇴', name: 'Dominican Rep.' },
    { key: 'pr', group: 'caribbean', flag: '🇵🇷', name: 'Puerto Rico' },
    { key: 'ag', group: 'caribbean', flag: '🇦🇬', name: 'Antigua' },
    { key: 'gd', group: 'caribbean', flag: '🇬🇩', name: 'Grenada' },
    { key: 'lc', group: 'caribbean', flag: '🇱🇨', name: 'St. Lucia' },
    { key: 'vc', group: 'caribbean', flag: '🇻🇨', name: 'St. Vincent' },
    { key: 'kn', group: 'caribbean', flag: '🇰🇳', name: 'St. Kitts & Nevis' },
    { key: 'gy', group: 'caribbean', flag: '🇬🇾', name: 'Guyana' },
    { key: 'sr', group: 'caribbean', flag: '🇸🇷', name: 'Suriname' },
    { key: 'ky', group: 'caribbean', flag: '🇰🇾', name: 'Cayman Islands' },
    { key: 'tc', group: 'caribbean', flag: '🇹🇨', name: 'Turks & Caicos' },
    { key: 'bz', group: 'caribbean', flag: '🇧🇿', name: 'Belize' },
    { key: 'cu', group: 'caribbean', flag: '🇨🇺', name: 'Cuba' },
    { key: 'br', group: 'latin', flag: '🇧🇷', name: 'Brazil' },
    { key: 'co', group: 'latin', flag: '🇨🇴', name: 'Colombia' },
    { key: 'mx', group: 'latin', flag: '🇲🇽', name: 'Mexico' },
    { key: 'ar', group: 'latin', flag: '🇦🇷', name: 'Argentina' },
    { key: 'cl', group: 'latin', flag: '🇨🇱', name: 'Chile' },
    { key: 'pe', group: 'latin', flag: '🇵🇪', name: 'Peru' },
    { key: 've', group: 'latin', flag: '🇻🇪', name: 'Venezuela' },
    { key: 'pa', group: 'latin', flag: '🇵🇦', name: 'Panama' },
    { key: 'cr', group: 'latin', flag: '🇨🇷', name: 'Costa Rica' },
    { key: 'us', group: 'global', flag: '🇺🇸', name: 'United States' },
    { key: 'gb', group: 'global', flag: '🇬🇧', name: 'United Kingdom' },
    { key: 'ca', group: 'global', flag: '🇨🇦', name: 'Canada' },
    { key: 'nl', group: 'global', flag: '🇳🇱', name: 'Netherlands' },
    { key: 'fr', group: 'global', flag: '🇫🇷', name: 'France' },
    { key: 'de', group: 'global', flag: '🇩🇪', name: 'Germany' },
];

const newsPackages = ref<NewsPackage[]>([
    { key: 'starter', icon: '🧾', name: 'Starter', price: 299, duration: '7 days', type: 'placement', features: ['1 article banner', '1 category', 'Basic analytics'] },
    { key: 'anchor', icon: '⚓', name: 'Anchor', price: 899, duration: '30 days', type: 'placement', badge: 'Popular', features: ['Homepage feature', '3 articles', 'Performance report'] },
    { key: 'premium', icon: '👑', name: 'Premium', price: 1999, duration: '30 days', type: 'bundle', features: ['Homepage + category', 'Newsletter spot', 'Priority reporting'] },
    { key: 'newsletter', icon: '📩', name: 'Newsletter Boost', price: 650, duration: '1 send', type: 'email', features: ['Daily digest placement', 'CTA link', 'Open/click metrics'] },
]);

const fallbackSponsors: Sponsor[] = [
    { id: 1, icon: '🏦', name: 'Scotiabank', placementKey: 'category-takeover', placement: 'Category Takeover', category: 'Business', territory: 'Barbados', territoryFlags: '🇧🇧', status: 'Active', rate: '$2,500/wk', impressions: 48200, clicks: 2844, ctr: '5.9%', cost: 2500, starts: '2026-07-01', ends: '2026-07-31' },
    { id: 2, icon: '📡', name: 'Digicel', placementKey: 'newsletter-spot', placement: 'Newsletter Spot', category: 'All Categories', territory: 'All Caribbean', territoryFlags: '🌎', status: 'Active', rate: '$850/send', impressions: 32100, clicks: 1605, ctr: '5.0%', cost: 850, starts: '2026-07-08', ends: '2026-07-22' },
    { id: 3, icon: '🏖️', name: 'Sandals Resorts', placementKey: 'homepage-feature', placement: 'Homepage Feature', category: 'Travel & Tourism', territory: 'Tier 4 Multi', territoryFlags: '🏝️', status: 'Active', rate: '$1,800/wk', impressions: 61400, clicks: 4912, ctr: '8.0%', cost: 1800, starts: '2026-07-01', ends: '2026-08-01' },
];

const sponsors = ref<Sponsor[]>([]);

syncNewsAds();

watch(() => props.newsAds, () => syncNewsAds(), { deep: true });

const visibleCountriesByGroup = computed(() => ({
    caribbean: countries.filter((c) => c.group === 'caribbean'),
    latin: countries.filter((c) => c.group === 'latin'),
    global: countries.filter((c) => c.group === 'global'),
}));

const currentPlacement = computed(() => placementTypes.find((p) => p.key === selectedPlacement.value) ?? placementTypes[0]);
const currentPackage = computed(() => newsPackages.value.find((pkg) => pkg.key === selectedPackage.value) ?? newsPackages.value[0]);
const selectedCountryObjects = computed(() => countries.filter((country) => selectedCountries.value.includes(country.key)));
const selectedTerritoryText = computed(() => {
    if (selectedCountries.value.length === 0) return 'All Caribbean';
    if (selectedCountries.value.length > 4) return `${selectedCountries.value.length} markets`;
    return selectedCountryObjects.value.map((country) => country.name).join(', ');
});
const selectedTerritoryFlags = computed(() => selectedCountryObjects.value.map((country) => country.flag).join(' ') || '🌎');

const totals = computed(() => {
    const impressions = sponsors.value.reduce((sum, sponsor) => sum + sponsor.impressions, 0);
    const clicks = sponsors.value.reduce((sum, sponsor) => sum + sponsor.clicks, 0);
    const revenue = sponsors.value.reduce((sum, sponsor) => sum + sponsor.cost, 0);

    return {
        impressions,
        views: Math.round(impressions * 0.0664),
        clicks,
        ctr: impressions ? `${((clicks / impressions) * 100).toFixed(1)}%` : '0.0%',
        revenue,
        active: sponsors.value.filter((sponsor) => sponsor.status === 'Active').length,
    };
});

const topPlacementBars = computed(() => makeGroupedBars('placement'));
const categoryBars = computed(() => makeGroupedBars('category'));

const reportSponsor = computed(() => sponsors.value.find((sponsor) => sponsor.id === selectedReportId.value) ?? null);

function firstValue(...values: any[]) {
    return values.find((value) => value !== undefined && value !== null && value !== '');
}

function getPlacement(key?: string) {
    return placementTypes.find((placement) => placement.key === key) ?? placementTypes[5];
}

function getPackage(key?: string) {
    return newsPackages.value.find((pkg) => pkg.key === key) ?? newsPackages.value[1];
}

function parseTargetingNotes(value: any) {
    if (!value) return {};
    if (typeof value === 'object') return value;

    try {
        return JSON.parse(String(value));
    } catch {
        return {};
    }
}

function normalizeStatus(ad: any) {
    if (ad.status === false || ad.status === 0) {
        return String(ad.publication_status || 'Draft').toLowerCase().includes('active') ? 'Active' : 'Draft';
    }

    return String(firstValue(ad.status, ad.publication_status, 'Active')).toLowerCase().includes('draft') ? 'Draft' : 'Active';
}

function inferTerritoryKeys(...values: any[]) {
    const haystack = values
        .filter((value) => value !== undefined && value !== null)
        .map((value) => String(value).toLowerCase())
        .join(' ');

    if (!haystack) return [];

    return countries
        .filter((country) => haystack.includes(country.name.toLowerCase()) || haystack.includes(country.key.toLowerCase()))
        .map((country) => country.key);
}

function normalizeNewsAds(rows?: any[]) {
    if (!Array.isArray(rows) || rows.length === 0) return [];

    return rows.map((ad) => {
        const targeting = parseTargetingNotes(ad.targeting_notes);
        const placementKey = String(firstValue(ad.placement_key, targeting.placement_key, ad.location, 'homepage-feature'));
        const placement = getPlacement(placementKey);
        const packageKey = String(firstValue(ad.package_key, targeting.package_key, ad.price_package, 'anchor'));
        const pkg = getPackage(packageKey);
        const cost = Number(firstValue(ad.cost, pkg.price, 0)) || 0;
        const impressions = Number(firstValue(ad.impressions, Math.round(cost * 58), 0)) || 0;
        const clicks = Number(firstValue(ad.clicks, Math.round(impressions * 0.059), 0)) || 0;
        const territoryKeys = (Array.isArray(ad.territory_keys)
            ? ad.territory_keys
            : Array.isArray(targeting.territory_keys)
                ? targeting.territory_keys
                : inferTerritoryKeys(ad.territory, ad.country, ad.state, targeting.territory_flags))
            .map(String)
            .filter(Boolean);

        return {
            id: Number(ad.id),
            icon: placement.icon,
            name: String(firstValue(ad.advertiser_name, ad.name, 'Advertiser')),
            placementKey,
            placement: String(firstValue(ad.placement_name, targeting.placement_name, ad.location, ad.description, placement.name)),
            category: String(firstValue(ad.category, 'All Categories')),
            headline: String(firstValue(ad.headline, '')),
            territory: String(firstValue(ad.territory, ad.country, 'All Caribbean')),
            territoryFlags: String(firstValue(ad.territory_flags, ad.state, '🌎')),
            territoryKeys,
            packageKey,
            packageName: String(firstValue(ad.package_name, targeting.package_name, pkg.name)),
            packageDuration: String(firstValue(ad.package_duration, targeting.package_duration, pkg.duration)),
            status: normalizeStatus(ad),
            rate: `${props.fmt(cost)}/${pkg.type === 'email' ? 'send' : 'campaign'}`,
            impressions,
            clicks,
            ctr: String(firstValue(ad.ctr, impressions ? `${((clicks / impressions) * 100).toFixed(1)}%` : '0.0%')),
            cost,
            starts: String(firstValue(ad.start_date, ad.starts, '2026-07-15')).slice(0, 10),
            ends: String(firstValue(ad.end_date, ad.ends, '2026-08-14')).slice(0, 10),
        } satisfies Sponsor;
    });
}

function syncNewsAds() {
    const normalized = normalizeNewsAds(props.newsAds);
    persistedSponsorIds.value = new Set(normalized.map((sponsor) => sponsor.id));
    sponsors.value = normalized.length ? normalized : [...fallbackSponsors];
}

function hasPersistedSponsor(id: number) {
    return persistedSponsorIds.value.has(id);
}

function makeBars(items: { label: string; value: number; display: string }[]) {
    const max = Math.max(...items.map((item) => item.value), 1);
    return items.map((item) => ({
        ...item,
        width: `${Math.max((item.value / max) * 100, 5)}%`,
    }));
}

function makeGroupedBars(field: 'placement' | 'category') {
    const grouped = new Map<string, number>();

    sponsors.value.forEach((sponsor) => {
        const label = sponsor[field];
        grouped.set(label, (grouped.get(label) || 0) + sponsor.impressions);
    });

    return makeBars(Array.from(grouped.entries()).map(([label, value]) => ({
        label,
        value,
        display: `${Math.round(value / 1000)}k`,
    })));
}

function switchTab(tab: Tab) {
    activeTab.value = tab;
    if (tab !== 'reports') reportGenerated.value = false;
}

function selectPlacement(key: string) {
    selectedPlacement.value = key;
}

function toggleScope(scope: 'caribbean' | 'latin' | 'global') {
    selectedScopes.value = selectedScopes.value.includes(scope)
        ? selectedScopes.value.filter((item) => item !== scope)
        : [...selectedScopes.value, scope];
}

function clearAllCountries() {
    selectedCountries.value = [];
}

function toggleCountry(key: string) {
    selectedCountries.value = selectedCountries.value.includes(key)
        ? selectedCountries.value.filter((item) => item !== key)
        : [...selectedCountries.value, key];
}

function selectPackage(key: string) {
    selectedPackage.value = key;
}

function startPackageEdit(pkg: NewsPackage) {
    packageDraft.value = {
        key: pkg.key,
        icon: pkg.icon,
        name: pkg.name,
        price: pkg.price,
        duration: pkg.duration,
        type: pkg.type,
        badge: pkg.badge || '',
        features: pkg.features.join('\n'),
    };
}

function cancelPackageEdit() {
    packageDraft.value = {
        key: '',
        icon: '📦',
        name: '',
        price: 0,
        duration: '',
        type: 'placement',
        badge: '',
        features: '',
    };
}

function savePackageEdit() {
    const draft = packageDraft.value;
    if (!draft.key || !draft.name.trim()) return;

    newsPackages.value = newsPackages.value.map((pkg) => pkg.key === draft.key
        ? {
            key: draft.key,
            icon: draft.icon || '📦',
            name: draft.name.trim(),
            price: Number(draft.price) || 0,
            duration: draft.duration.trim(),
            type: draft.type,
            badge: draft.badge.trim(),
            features: draft.features.split('\n').map((line) => line.trim()).filter(Boolean),
        }
        : pkg);
    cancelPackageEdit();
    toast.success('Package updated.');
}

function deletePackage(key: string) {
    newsPackages.value = newsPackages.value.filter((pkg) => pkg.key !== key);
    if (selectedPackage.value === key) selectedPackage.value = newsPackages.value[0]?.key || '';
    toast.success('Package deleted.');
}

function addPackage() {
    const draft = addPackageDraft.value;
    if (!draft.name.trim()) return;

    const key = `pkg_${Date.now()}`;
    newsPackages.value.push({
        key,
        icon: draft.icon || '📦',
        name: draft.name.trim(),
        price: Number(draft.price) || 0,
        duration: draft.duration.trim(),
        type: draft.type,
        badge: draft.badge.trim(),
        features: draft.features.split('\n').map((line) => line.trim()).filter(Boolean),
    });
    selectedPackage.value = key;
    addPackageOpen.value = false;
    addPackageDraft.value = { icon: '📦', name: '', price: 0, duration: '', type: 'placement', badge: '', features: '' };
    toast.success('Package added.');
}

function buildPayload() {
    const placement = currentPlacement.value;
    const pkg = currentPackage.value;

    return {
        advertiser_name: form.value.advertiser.trim(),
        placement_key: placement.key,
        placement_name: placement.name,
        category: form.value.category,
        territory: selectedTerritoryText.value,
        territory_flags: selectedTerritoryFlags.value,
        territory_keys: selectedCountries.value,
        headline: form.value.headline,
        start_date: form.value.startDate,
        end_date: form.value.endDate,
        package_key: pkg.key,
        package_name: pkg.name,
        package_duration: pkg.duration,
        cost: pkg.price,
        status: 'Active',
    };
}

async function submitSponsorship() {
    if (!form.value.advertiser.trim()) {
        toast.error('Advertiser name is required.');
        return;
    }

    isSaving.value = true;

    try {
        const payload = buildPayload();
        const wasEditing = editingSponsorId.value !== null;
        const response = editingSponsorId.value
            ? await axios.put(route('admin.ads.c360-news.update', { ad: editingSponsorId.value }), payload)
            : await axios.post(route('admin.ads.c360-news.store'), payload);

        const [saved] = normalizeNewsAds([response.data.ad]);
        persistedSponsorIds.value.add(saved.id);
        sponsors.value = editingSponsorId.value
            ? sponsors.value.map((sponsor) => sponsor.id === saved.id ? saved : sponsor)
            : [saved, ...sponsors.value.filter((sponsor) => !fallbackSponsors.some((fallback) => fallback.id === sponsor.id))];

        editingSponsorId.value = null;
        form.value = { advertiser: '', category: 'All Categories', headline: '', startDate: '2026-07-15', endDate: '2026-08-14' };
        toast.success(wasEditing ? 'C360 sponsorship updated.' : `News sponsorship submitted. Reference C360-${Date.now().toString(36).toUpperCase().slice(-6)}`);
    } catch (error: any) {
        const errors = error?.response?.data?.errors;
        const firstError = errors ? Object.values(errors).flat()[0] : null;
        toast.error(String(firstError || error?.response?.data?.message || 'Could not save C360 sponsorship.'));
    } finally {
        isSaving.value = false;
    }
}

function scopesForCountryKeys(keys: string[]) {
    const scopes = new Set<'caribbean' | 'latin' | 'global'>();

    keys.forEach((key) => {
        const country = countries.find((item) => item.key === key);
        if (country) scopes.add(country.group);
    });

    return scopes.size ? Array.from(scopes) : ['caribbean'];
}

function editSponsor(sponsor: Sponsor) {
    editingSponsorId.value = hasPersistedSponsor(sponsor.id) ? sponsor.id : null;
    selectedPlacement.value = sponsor.placementKey;
    form.value = {
        advertiser: sponsor.name,
        category: sponsor.category,
        headline: sponsor.headline || '',
        startDate: sponsor.starts,
        endDate: sponsor.ends,
    };
    const matchingPackage = sponsor.packageKey
        ? newsPackages.value.find((pkg) => pkg.key === sponsor.packageKey)
        : newsPackages.value.find((pkg) => sponsor.cost === pkg.price);
    if (matchingPackage) selectedPackage.value = matchingPackage.key;
    selectedCountries.value = [...(sponsor.territoryKeys || [])];
    selectedScopes.value = scopesForCountryKeys(selectedCountries.value);
    activeTab.value = 'manage';
    toast.info(editingSponsorId.value ? 'Editing C360 sponsorship.' : 'Loaded sample sponsorship as a new draft.');
}

function cancelEdit() {
    editingSponsorId.value = null;
    form.value = { advertiser: '', category: 'All Categories', headline: '', startDate: '2026-07-15', endDate: '2026-08-14' };
    selectedCountries.value = ['jm', 'bs'];
    selectedScopes.value = ['caribbean'];
}

async function deleteSponsor(id: number) {
    if (!window.confirm('Delete this C360 sponsorship?')) return;

    if (!hasPersistedSponsor(id)) {
        sponsors.value = sponsors.value.filter((sponsor) => sponsor.id !== id);
        persistedSponsorIds.value.delete(id);
        toast.success('Sample sponsorship removed.');
        return;
    }

    try {
        await axios.delete(route('admin.ads.c360-news.destroy', { ad: id }));
        sponsors.value = sponsors.value.filter((sponsor) => sponsor.id !== id);
        if (selectedReportId.value === id) {
            selectedReportId.value = null;
            reportGenerated.value = false;
        }
        toast.success('C360 sponsorship deleted.');
    } catch (error: any) {
        toast.error(error?.response?.data?.message || 'Could not delete C360 sponsorship.');
    }
}

async function generateReport() {
    if (!selectedReportId.value) return;
    reportGenerated.value = true;
    await nextTick();
    reportOutputRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function printReport() {
    window.print();
}
</script>

<template>
    <div class="nw-page">
        <Toaster rich-colors position="top-right" />

        <div class="nw-header">
            <div class="nw-brand">
                <div class="nw-logo-badge">C<span>360</span></div>
                <div>
                    <div class="nw-title">Caribbean 360 News</div>
                    <div class="nw-tagline">Ad Sponsorship Management</div>
                </div>
            </div>
            <div class="nw-tabs">
                <button class="nw-tab" :class="{ active: activeTab === 'manage' }" @click="switchTab('manage')">📋 Manage</button>
                <button class="nw-tab" :class="{ active: activeTab === 'analytics' }" @click="switchTab('analytics')">📊 Analytics</button>
                <button class="nw-tab" :class="{ active: activeTab === 'reports' }" @click="switchTab('reports')">📄 Reports</button>
            </div>
        </div>

        <div v-if="activeTab === 'manage'">
            <div class="nw-stats-bar">
                <div class="nw-stat-chip"><div class="nw-stat-val">{{ props.num(totals.impressions) }}</div><div class="nw-stat-lbl">Total Impressions</div></div>
                <div class="nw-stat-chip"><div class="nw-stat-val">{{ props.num(totals.views) }}</div><div class="nw-stat-lbl">Article Views</div></div>
                <div class="nw-stat-chip"><div class="nw-stat-val">{{ totals.ctr }}</div><div class="nw-stat-lbl">Avg CTR</div></div>
                <div class="nw-stat-chip"><div class="nw-stat-val">{{ props.fmt(totals.revenue) }}</div><div class="nw-stat-lbl">Weekly Revenue</div></div>
            </div>

            <div class="nw-section-label">Placement Types</div>
            <div class="nw-placements-grid">
                <button
                    v-for="placement in placementTypes"
                    :key="placement.key"
                    type="button"
                    class="nw-placement-card"
                    :class="{ selected: selectedPlacement === placement.key }"
                    @click="selectPlacement(placement.key)"
                >
                    <div class="nw-pc-icon">{{ placement.icon }}</div>
                    <div class="nw-pc-name">{{ placement.name }}</div>
                    <div class="nw-pc-desc">{{ placement.desc }}</div>
                    <div class="nw-pc-price">{{ placement.price }}</div>
                </button>
            </div>

            <div class="nw-section-label nw-spaced">Active Sponsors</div>
            <div class="nw-sponsors-list">
                <div class="nw-sponsor-row nw-sponsor-header">
                    <span>Advertiser</span>
                    <span>Placement</span>
                    <span>Territory</span>
                    <span>Status</span>
                    <span>Rate</span>
                    <span>Impressions</span>
                    <span>Actions</span>
                </div>
                <div v-for="sponsor in sponsors" :key="sponsor.id" class="nw-sponsor-row">
                    <span><strong>{{ sponsor.icon }} {{ sponsor.name }}</strong></span>
                    <span>{{ sponsor.placement }} <em>{{ sponsor.category }}</em></span>
                    <span>{{ sponsor.territory }} {{ sponsor.territoryFlags }}</span>
                    <span><span class="nw-badge-active">{{ sponsor.status }}</span></span>
                    <span class="nw-rate">{{ sponsor.rate }}</span>
                    <span class="nw-imp">👁 {{ props.num(sponsor.impressions) }}</span>
                    <span class="nw-row-actions">
                        <button type="button" class="nw-pkg-edit-btn" @click="editSponsor(sponsor)">Edit</button>
                        <button type="button" class="nw-pkg-del-btn" @click="deleteSponsor(sponsor.id)">Delete</button>
                    </span>
                </div>
            </div>

            <div class="nw-section-label nw-spaced">New Sponsorship</div>
            <div class="nw-form-card">
                <div class="nw-form-card-title">Create News Sponsorship</div>
                <form @submit.prevent="submitSponsorship">
                    <div class="nw-form-grid">
                        <div class="field">
                            <label>Advertiser Name</label>
                            <input v-model="form.advertiser" type="text" placeholder="e.g. Scotiabank Caribbean" required>
                        </div>
                        <div class="field">
                            <label>Placement Type</label>
                            <select v-model="selectedPlacement" required>
                                <option v-for="placement in placementTypes" :key="placement.key" :value="placement.key">{{ placement.name }}</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Category Targeting</label>
                            <select v-model="form.category">
                                <option>All Categories</option>
                                <option>Breaking News</option>
                                <option>Politics & Government</option>
                                <option>Business & Economy</option>
                                <option>Entertainment</option>
                                <option>Sports</option>
                                <option>Lifestyle & Health</option>
                                <option>Travel & Tourism</option>
                                <option>Technology</option>
                                <option>Diaspora</option>
                                <option>Culture & Arts</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Ad Headline / Copy</label>
                            <input v-model="form.headline" type="text" placeholder="e.g. Banking made simple - Scotiabank Caribbean">
                        </div>
                        <div class="field full">
                            <label>Territory & Reach</label>
                            <div class="nw-region-bar">
                                <div class="nw-region-scope-label">📍 Scope</div>
                                <button type="button" class="nw-region-pill" :class="{ selected: selectedScopes.includes('caribbean') }" @click="toggleScope('caribbean')">🗺️ Caribbean</button>
                                <button type="button" class="nw-region-pill" :class="{ selected: selectedScopes.includes('latin') }" @click="toggleScope('latin')">📍 Latin America</button>
                                <button type="button" class="nw-region-pill" :class="{ selected: selectedScopes.includes('global') }" @click="toggleScope('global')">🌐 Global 360</button>
                                <button type="button" class="nw-region-clear" @click="clearAllCountries">Clear all</button>
                            </div>

                            <template v-for="scope in ['caribbean', 'latin', 'global']" :key="scope">
                                <div v-if="selectedScopes.includes(scope)" class="nw-country-group">
                                    <div class="nw-country-group-label">{{ scope === 'latin' ? 'Latin America' : scope === 'global' ? 'Global 360 - Diaspora Hubs' : 'Caribbean' }}</div>
                                    <div class="nw-country-grid">
                                        <button
                                            v-for="country in visibleCountriesByGroup[scope as keyof typeof visibleCountriesByGroup]"
                                            :key="country.key"
                                            type="button"
                                            class="nw-country-chip"
                                            :class="{ selected: selectedCountries.includes(country.key) }"
                                            @click="toggleCountry(country.key)"
                                        >
                                            <span>{{ country.flag }}</span>{{ country.name }}
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div class="nw-territory-summary">
                                <span>{{ selectedCountries.length || 'All' }} {{ selectedCountries.length === 1 ? 'market' : 'markets' }} selected</span>
                                <span class="nw-territory-tags">{{ selectedTerritoryFlags }}</span>
                            </div>
                        </div>
                        <div class="field full">
                            <label>Creative Upload</label>
                            <input type="file" accept="image/*,video/*">
                        </div>
                        <div class="field">
                            <label>Start Date</label>
                            <input v-model="form.startDate" type="date" required>
                        </div>
                        <div class="field">
                            <label>End Date</label>
                            <input v-model="form.endDate" type="date" required>
                        </div>
                    </div>

                    <div class="nw-package-head">
                        <div class="nw-section-label">Select Package</div>
                        <button type="button" class="nw-pkg-manage-btn" :class="{ open: managePackagesOpen }" @click="managePackagesOpen = !managePackagesOpen">⚙️ Manage Packages</button>
                    </div>

                    <div v-if="managePackagesOpen" class="nw-manage-pkg-panel">
                        <div class="nw-manage-pkg-head">
                            <span class="nw-manage-pkg-title">📦 Manage Packages</span>
                            <button type="button" class="nw-manage-pkg-close" @click="managePackagesOpen = false">x</button>
                        </div>
                        <div v-for="pkg in newsPackages" :key="pkg.key" class="nw-pkg-row">
                            <div class="nw-pkg-row-icon">{{ pkg.icon }}</div>
                            <div class="nw-pkg-row-info">
                                <div class="nw-pkg-row-name">{{ pkg.name }}</div>
                                <div class="nw-pkg-row-meta">{{ pkg.duration }} · {{ pkg.type }}</div>
                            </div>
                            <div class="nw-pkg-row-price">{{ props.fmt(pkg.price) }}</div>
                            <div class="nw-pkg-row-actions">
                                <button type="button" class="nw-pkg-edit-btn" @click="startPackageEdit(pkg)">Edit</button>
                                <button type="button" class="nw-pkg-del-btn" @click="deletePackage(pkg.key)">Delete</button>
                            </div>
                        </div>

                        <div v-if="packageDraft.key" class="nw-pkg-edit-form is-open">
                            <div class="nw-pkg-form-grid">
                                <div class="field"><label>Icon</label><input v-model="packageDraft.icon"></div>
                                <div class="field"><label>Package Name</label><input v-model="packageDraft.name"></div>
                                <div class="field"><label>Price ($)</label><input v-model.number="packageDraft.price" type="number" min="0"></div>
                                <div class="field"><label>Duration</label><input v-model="packageDraft.duration"></div>
                                <div class="field"><label>Type</label><select v-model="packageDraft.type"><option value="placement">Placement</option><option value="email">Email Sponsorship</option><option value="bundle">Bundle</option></select></div>
                                <div class="field"><label>Badge</label><input v-model="packageDraft.badge"></div>
                                <div class="field full"><label>Features</label><textarea v-model="packageDraft.features" rows="3"></textarea></div>
                            </div>
                            <div class="nw-pkg-form-actions">
                                <button type="button" class="nw-pkg-cancel-btn" @click="cancelPackageEdit">Cancel</button>
                                <button type="button" class="nw-pkg-save-btn" @click="savePackageEdit">✓ Save Changes</button>
                            </div>
                        </div>

                        <div class="nw-pkg-add-section">
                            <button type="button" class="nw-pkg-add-toggle" @click="addPackageOpen = !addPackageOpen">＋ Add New Package</button>
                            <div v-if="addPackageOpen" class="nw-pkg-edit-form is-open">
                                <div class="nw-pkg-form-grid">
                                    <div class="field"><label>Icon</label><input v-model="addPackageDraft.icon"></div>
                                    <div class="field"><label>Package Name</label><input v-model="addPackageDraft.name"></div>
                                    <div class="field"><label>Price ($)</label><input v-model.number="addPackageDraft.price" type="number" min="0"></div>
                                    <div class="field"><label>Duration</label><input v-model="addPackageDraft.duration"></div>
                                    <div class="field"><label>Type</label><select v-model="addPackageDraft.type"><option value="placement">Placement</option><option value="email">Email Sponsorship</option><option value="bundle">Bundle</option></select></div>
                                    <div class="field"><label>Badge</label><input v-model="addPackageDraft.badge"></div>
                                    <div class="field full"><label>Features</label><textarea v-model="addPackageDraft.features" rows="3"></textarea></div>
                                </div>
                                <div class="nw-pkg-form-actions">
                                    <button type="button" class="nw-pkg-cancel-btn" @click="addPackageOpen = false">Cancel</button>
                                    <button type="button" class="nw-pkg-save-btn" @click="addPackage">＋ Add Package</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nw-pkg-grid">
                        <button
                            v-for="pkg in newsPackages"
                            :key="pkg.key"
                            type="button"
                            class="nw-pkg-card"
                            :class="{ selected: selectedPackage === pkg.key }"
                            @click="selectPackage(pkg.key)"
                        >
                            <span v-if="pkg.badge" class="nw-pkg-badge">{{ pkg.badge }}</span>
                            <div class="nw-pc-icon">{{ pkg.icon }}</div>
                            <div class="nw-pkg-type-badge" :class="`type-${pkg.type}`">{{ pkg.type }}</div>
                            <div class="nw-pkg-name">{{ pkg.name }}</div>
                            <div class="nw-pkg-price">{{ props.fmt(pkg.price) }}</div>
                            <ul class="nw-pkg-features">
                                <li v-for="feature in pkg.features" :key="feature">{{ feature }}</li>
                            </ul>
                        </button>
                    </div>

                    <div class="nw-price-summary">
                        <div>
                            <div class="nw-price-label">Campaign Total</div>
                            <div class="nw-price-note">{{ currentPackage.duration }} · {{ currentPackage.name }} package</div>
                        </div>
                        <div class="nw-price-amount">{{ props.fmt(currentPackage.price) }}</div>
                    </div>

                    <div class="nw-form-actions">
                        <button v-if="editingSponsorId" type="button" class="btn btn-outline" :disabled="isSaving" @click="cancelEdit">Cancel Edit</button>
                        <button type="button" class="btn btn-outline" :disabled="isSaving" @click="form.advertiser = ''; form.headline = ''">Clear</button>
                        <button type="submit" class="btn btn-primary" :disabled="isSaving">🚀 {{ isSaving ? 'Saving...' : (editingSponsorId ? 'Update Sponsorship' : 'Submit Sponsorship') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-else-if="activeTab === 'analytics'">
            <div class="nw-kpi-grid">
                <div class="nw-kpi-card"><div class="nw-kpi-lbl">Total Impressions</div><div class="nw-kpi-val">{{ props.num(totals.impressions) }}</div><div class="nw-kpi-sub">↑ 12% vs last period</div></div>
                <div class="nw-kpi-card blue"><div class="nw-kpi-lbl">Total Clicks</div><div class="nw-kpi-val">{{ props.num(totals.clicks) }}</div><div class="nw-kpi-sub">↑ 8% vs last period</div></div>
                <div class="nw-kpi-card teal"><div class="nw-kpi-lbl">Avg CTR</div><div class="nw-kpi-val">{{ totals.ctr }}</div><div class="nw-kpi-sub">Benchmark: 5.0%</div></div>
                <div class="nw-kpi-card lime"><div class="nw-kpi-lbl">Revenue</div><div class="nw-kpi-val">{{ props.fmt(totals.revenue) }}</div><div class="nw-kpi-sub">{{ totals.active }} active sponsors</div></div>
            </div>

            <div class="nw-analytics-grid">
                <div class="card">
                    <div class="card-title">Top Placements by Impressions</div>
                    <div class="nw-bars">
                        <div v-for="bar in topPlacementBars" :key="bar.label" class="mini-bar-row">
                            <div class="mini-bar-label">{{ bar.label }}</div>
                            <div class="mini-bar-track"><span class="mini-bar-fill" :style="{ width: bar.width }"></span></div>
                            <div class="mini-bar-val">{{ bar.display }}</div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-title">Category Breakdown</div>
                    <div class="nw-bars">
                        <div v-for="bar in categoryBars" :key="bar.label" class="mini-bar-row">
                            <div class="mini-bar-label"><span class="color-dot"></span>{{ bar.label }}</div>
                            <div class="mini-bar-track"><span class="mini-bar-fill lime" :style="{ width: bar.width }"></span></div>
                            <div class="mini-bar-val">{{ bar.display }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card nw-spaced">
                <div class="card-title">Geographic Reach</div>
                <div class="nw-geo-grid">
                    <div v-for="sponsor in sponsors" :key="sponsor.id" class="nw-geo-chip">
                        <div class="nw-geo-flag">{{ sponsor.territoryFlags }}</div>
                        <div class="nw-geo-name">{{ sponsor.territory }}</div>
                        <div class="nw-geo-val">{{ props.num(sponsor.impressions) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <div class="card">
                <div class="card-title">Select Advertiser</div>
                <div class="nw-report-grid">
                    <button
                        v-for="sponsor in sponsors"
                        :key="sponsor.id"
                        type="button"
                        class="nw-adv-card"
                        :class="{ selected: selectedReportId === sponsor.id }"
                        @click="selectedReportId = sponsor.id"
                    >
                        <div class="nw-adv-icon">{{ sponsor.icon }}</div>
                        <div class="nw-adv-name">{{ sponsor.name }}</div>
                        <div class="nw-adv-meta">{{ sponsor.placement }}</div>
                        <div class="nw-adv-meta">{{ sponsor.territory }}</div>
                        <div><span class="nw-active-dot">● Active</span></div>
                    </button>
                </div>
            </div>
            <div class="nw-report-actions">
                <button class="btn btn-primary" :disabled="!selectedReportId" @click="generateReport">📄 Generate Report</button>
            </div>

            <div v-if="reportGenerated && reportSponsor" ref="reportOutputRef" class="nw-report-output">
                <div class="nw-report-head">
                    <div>
                        <div class="nw-report-kicker">Caribbean 360 News · Sponsor Report</div>
                        <div class="nw-report-title">{{ reportSponsor.icon }} {{ reportSponsor.name }}</div>
                        <div class="nw-report-sub">{{ reportSponsor.placement }} · {{ reportSponsor.territory }}</div>
                    </div>
                    <div class="nw-report-date">Generated July 15, 2026<br>Period: {{ reportSponsor.starts }} - {{ reportSponsor.ends }}</div>
                </div>
                <div class="nw-report-kpis">
                    <div><span>Impressions</span><strong>{{ props.num(reportSponsor.impressions) }}</strong></div>
                    <div><span>Clicks</span><strong>{{ props.num(reportSponsor.clicks) }}</strong></div>
                    <div><span>CTR</span><strong>{{ reportSponsor.ctr }}</strong></div>
                    <div><span>Investment</span><strong>{{ props.fmt(reportSponsor.cost) }}</strong></div>
                </div>
                <div class="nw-report-summary">
                    <strong>{{ reportSponsor.name }}</strong> ran a <strong>{{ reportSponsor.placement }}</strong> across Caribbean 360 News targeting <strong>{{ reportSponsor.category }}</strong> content.
                    The campaign delivered <strong>{{ props.num(reportSponsor.impressions) }} impressions</strong> with a <strong>{{ reportSponsor.ctr }} click-through rate</strong>.
                    Estimated media value: <strong>{{ props.fmt(reportSponsor.cost * 3.2) }}</strong> based on equivalent Caribbean digital ad rates.
                </div>
                <div class="nw-report-bottom">
                    <button class="btn btn-outline" @click="printReport">🖨️ Print</button>
                    <button class="btn btn-primary" @click="toast.success('Report emailed to advertiser.')">📧 Email to Advertiser</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.nw-page {
    --brand: #06b6d4;
    --brand-lime: #ccff00;
    --brand-red: #ef4444;
    --brand-pale: #ecfeff;
    --brand-dark: #0e7fa0;
    --border: #e2e8f0;
    --sidebar-bg: #f8fafc;
    --muted: #94a3b8;
    --mid: #64748b;
    --dark: #1e293b;
    --white: #fff;
    --blue: #3b82f6;
    --teal: #14b8a6;
    --purple: #7c3aed;
    --radius: 12px;
    --shadow: 0 1px 4px rgba(0,0,0,.06);
}

.nw-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #0f172a 100%);
    border-radius: var(--radius);
    padding: 20px 24px;
    margin-bottom: 22px;
    box-shadow: 0 4px 20px rgba(0,0,0,.25);
}

.nw-brand {
    display: flex;
    align-items: center;
    gap: 14px;
}

.nw-logo-badge {
    background: #fff;
    color: #0f172a;
    font-weight: 900;
    font-size: 20px;
    border-radius: 8px;
    padding: 6px 12px;
    letter-spacing: -1px;
    line-height: 1;
}

.nw-logo-badge span {
    color: var(--brand-red);
}

.nw-title {
    color: #fff;
    font-size: 18px;
    font-weight: 900;
    letter-spacing: -.3px;
}

.nw-tagline {
    color: rgba(255,255,255,.55);
    font-size: 12px;
    margin-top: 2px;
}

.nw-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.nw-tab {
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.7);
    border-radius: 20px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .18s;
}

.nw-tab:hover {
    background: rgba(255,255,255,.18);
    color: #fff;
}

.nw-tab.active {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
    box-shadow: 0 2px 10px rgba(6,182,212,.4);
}

.nw-stats-bar {
    display: flex;
    gap: 14px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.nw-stat-chip {
    flex: 1;
    min-width: 140px;
    background: var(--sidebar-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 18px;
}

.nw-stat-val {
    font-size: 22px;
    font-weight: 900;
    color: var(--brand);
    line-height: 1;
}

.nw-stat-lbl {
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: .5px;
    font-weight: 700;
}

.nw-section-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 12px;
}

.nw-spaced {
    margin-top: 28px;
}

.nw-placements-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 4px;
}

.nw-placement-card {
    background: #fff;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 14px;
    cursor: pointer;
    transition: all .18s;
    text-align: left;
    font-family: inherit;
}

.nw-placement-card:hover {
    border-color: var(--brand);
    box-shadow: 0 2px 12px rgba(6,182,212,.15);
    transform: translateY(-2px);
}

.nw-placement-card.selected {
    border-color: var(--brand);
    background: rgba(6,182,212,.06);
    box-shadow: 0 0 0 3px rgba(6,182,212,.15);
}

.nw-pc-icon {
    font-size: 22px;
    margin-bottom: 8px;
}

.nw-pc-name {
    font-weight: 800;
    font-size: 13px;
    margin-bottom: 4px;
}

.nw-pc-desc {
    font-size: 11px;
    color: var(--muted);
    margin-bottom: 6px;
    line-height: 1.4;
}

.nw-pc-price {
    font-size: 12px;
    font-weight: 700;
    color: var(--brand);
}

.nw-sponsors-list {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    background: #fff;
}

.nw-sponsor-row {
    display: grid;
    grid-template-columns: 2fr 2fr 1.5fr 1fr 1fr 1.5fr auto;
    gap: 12px;
    align-items: center;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

.nw-row-actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.nw-sponsor-row:last-child {
    border-bottom: none;
}

.nw-sponsor-row em {
    color: var(--muted);
    font-size: 11px;
    margin-left: 4px;
}

.nw-sponsor-header {
    background: var(--sidebar-bg);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--muted);
}

.nw-badge-active {
    background: #d1fae5;
    color: #065f46;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
}

.nw-rate {
    font-weight: 700;
    color: var(--brand);
}

.nw-imp {
    color: var(--mid);
    font-size: 12px;
}

.nw-form-card,
.card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow);
}

.nw-form-card-title,
.card-title {
    font-size: 16px;
    font-weight: 900;
    margin-bottom: 20px;
    color: #0f172a;
}

.nw-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--muted);
    margin-bottom: 5px;
}

.field input,
.field select,
.field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13px;
    font-family: inherit;
    color: var(--dark);
    background: #fff;
}

.field.full {
    grid-column: 1 / -1;
}

.field textarea {
    resize: vertical;
}

.nw-region-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    padding: 12px 14px;
    background: #0f172a;
    border-radius: 10px;
}

.nw-region-scope-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,.4);
    margin-right: 4px;
}

.nw-region-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.6);
    border: 1.5px solid rgba(255,255,255,.1);
    transition: all .15s;
    user-select: none;
}

.nw-region-pill:hover,
.nw-region-pill.selected {
    background: rgba(41,201,232,.18);
    color: var(--brand);
    border-color: var(--brand);
}

.nw-region-clear {
    margin-left: auto;
    border: 0;
    background: transparent;
    font-size: 11px;
    color: rgba(255,255,255,.45);
    cursor: pointer;
    text-decoration: underline;
}

.nw-country-group {
    margin-bottom: 16px;
}

.nw-country-group-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--muted);
    margin-bottom: 8px;
}

.nw-country-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
    gap: 7px;
}

.nw-country-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 9px 12px;
    border-radius: 24px;
    border: 1.5px solid var(--border);
    background: var(--white);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--dark);
    cursor: pointer;
    transition: all .15s;
    user-select: none;
    position: relative;
    text-align: left;
}

.nw-country-chip span {
    font-size: 16px;
    flex-shrink: 0;
}

.nw-country-chip:hover {
    border-color: var(--brand);
    background: var(--brand-pale);
}

.nw-country-chip.selected {
    border-color: #29c9e8;
    background: linear-gradient(135deg, #e6f9fd, #cef6fc);
    color: #0e7fa0;
    font-weight: 700;
}

.nw-country-chip.selected::after {
    content: '✓';
    position: absolute;
    right: 9px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #29c9e8;
    color: #fff;
    font-size: 10px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nw-territory-summary {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
    padding: 10px 14px;
    background: var(--brand-pale);
    border-radius: 8px;
    border: 1px solid rgba(41,201,232,.25);
    font-size: 12px;
    font-weight: 800;
    color: var(--brand-dark);
}

.nw-territory-tags {
    font-size: 16px;
    letter-spacing: 2px;
}

.nw-package-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 24px;
    margin-bottom: 12px;
}

.nw-package-head .nw-section-label {
    margin: 0;
}

.nw-pkg-manage-btn {
    background: rgba(41,201,232,.12);
    color: var(--brand);
    border: 1px solid rgba(41,201,232,.35);
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all .18s;
}

.nw-pkg-manage-btn.open {
    background: rgba(41,201,232,.25);
}

.nw-manage-pkg-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 18px;
}

.nw-manage-pkg-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.nw-manage-pkg-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--dark);
}

.nw-manage-pkg-close {
    background: none;
    border: none;
    font-size: 16px;
    color: var(--muted);
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
}

.nw-pkg-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 8px;
}

.nw-pkg-row-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.nw-pkg-row-info {
    flex: 1;
    min-width: 0;
}

.nw-pkg-row-name {
    font-weight: 700;
    font-size: 13px;
    color: var(--dark);
}

.nw-pkg-row-meta {
    font-size: 11px;
    color: var(--muted);
    margin-top: 1px;
}

.nw-pkg-row-price {
    font-weight: 800;
    font-size: 14px;
    color: var(--brand);
    white-space: nowrap;
}

.nw-pkg-row-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.nw-pkg-edit-btn,
.nw-pkg-del-btn {
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.nw-pkg-edit-btn {
    background: rgba(59,130,246,.1);
    color: #3b82f6;
    border: 1px solid rgba(59,130,246,.3);
}

.nw-pkg-del-btn {
    background: rgba(239,68,68,.08);
    color: #ef4444;
    border: 1px solid rgba(239,68,68,.25);
}

.nw-pkg-edit-form {
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 16px;
    margin-top: 10px;
}

.nw-pkg-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 10px;
}

.nw-pkg-form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.nw-pkg-save-btn,
.nw-pkg-cancel-btn,
.nw-pkg-add-toggle {
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.nw-pkg-save-btn {
    background: var(--brand);
    color: #fff;
    border: none;
}

.nw-pkg-cancel-btn {
    background: #f1f5f9;
    color: var(--dark);
    border: 1px solid #cbd5e1;
}

.nw-pkg-add-section {
    border-top: 1px solid #e2e8f0;
    margin-top: 14px;
    padding-top: 14px;
}

.nw-pkg-add-toggle {
    background: rgba(204,255,0,.15);
    color: #4d7c0f;
    border: 1px solid rgba(204,255,0,.5);
    border-radius: 20px;
}

.nw-pkg-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.nw-pkg-card {
    background: #fff;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 18px;
    cursor: pointer;
    transition: all .18s;
    position: relative;
    text-align: center;
    font-family: inherit;
}

.nw-pkg-card:hover {
    border-color: var(--brand);
    transform: translateY(-2px);
}

.nw-pkg-card.selected {
    border-color: var(--brand);
    background: rgba(6,182,212,.05);
    box-shadow: 0 0 0 3px rgba(6,182,212,.15);
}

.nw-pkg-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--brand);
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.nw-pkg-name {
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 6px;
}

.nw-pkg-price {
    font-size: 28px;
    font-weight: 900;
    color: var(--brand);
    margin-bottom: 10px;
}

.nw-pkg-features {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 12px;
    color: var(--mid);
    line-height: 1.9;
}

.nw-pkg-features li::before {
    content: "✓ ";
    color: var(--brand);
    font-weight: 700;
}

.nw-pkg-type-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 2px 8px;
    border-radius: 20px;
    margin-bottom: 6px;
}

.nw-pkg-type-badge.type-email { background: #fef3c7; color: #92400e; }
.nw-pkg-type-badge.type-placement { background: #dbeafe; color: #1d4ed8; }
.nw-pkg-type-badge.type-bundle { background: #f3e8ff; color: #7c3aed; }

.nw-price-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--sidebar-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 22px;
    margin-top: 18px;
}

.nw-price-label {
    font-weight: 800;
    font-size: 13px;
}

.nw-price-note {
    font-size: 12px;
    color: var(--muted);
}

.nw-price-amount {
    font-size: 28px;
    font-weight: 900;
    color: var(--brand);
}

.nw-form-actions,
.nw-report-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn {
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    border: 1px solid transparent;
    font-family: inherit;
}

.btn-primary {
    background: var(--brand);
    color: #fff;
}

.btn-outline {
    background: #fff;
    color: var(--dark);
    border-color: var(--border);
}

.btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.nw-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.nw-kpi-card {
    background: #fff;
    border: 1px solid var(--border);
    border-left: 4px solid var(--brand);
    border-radius: var(--radius);
    padding: 16px 20px;
}

.nw-kpi-card.blue { border-left-color: var(--blue); }
.nw-kpi-card.teal { border-left-color: var(--teal); }
.nw-kpi-card.lime { border-left-color: var(--brand-lime); }

.nw-kpi-lbl {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--muted);
    margin-bottom: 6px;
}

.nw-kpi-val {
    font-size: 26px;
    font-weight: 900;
    line-height: 1;
}

.nw-kpi-sub {
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
}

.nw-analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.nw-bars {
    display: grid;
    gap: 12px;
}

.mini-bar-row {
    display: grid;
    grid-template-columns: 150px 1fr 48px;
    gap: 10px;
    align-items: center;
    font-size: 12px;
}

.mini-bar-label {
    font-weight: 800;
    color: var(--dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mini-bar-track {
    height: 10px;
    border-radius: 100px;
    background: #e2e8f0;
    overflow: hidden;
}

.mini-bar-fill {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: var(--brand);
}

.mini-bar-fill.lime {
    background: var(--brand-lime);
}

.mini-bar-val {
    text-align: right;
    font-weight: 900;
}

.color-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--brand);
    margin-right: 6px;
}

.nw-geo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}

.nw-geo-chip {
    background: var(--sidebar-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px;
    text-align: center;
}

.nw-geo-flag {
    font-size: 28px;
    margin-bottom: 6px;
}

.nw-geo-name {
    font-size: 12px;
    font-weight: 700;
}

.nw-geo-val {
    font-size: 18px;
    font-weight: 900;
    color: var(--brand);
    margin-top: 4px;
}

.nw-report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}

.nw-adv-card {
    background: #0f172a;
    border: 2px solid rgba(255,255,255,.1);
    border-radius: var(--radius);
    padding: 18px 14px;
    text-align: center;
    cursor: pointer;
    transition: all .18s;
    color: #fff;
    font-family: inherit;
}

.nw-adv-card:hover,
.nw-adv-card.selected {
    border-color: var(--brand);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(41,201,232,.15);
}

.nw-adv-icon {
    font-size: 28px;
}

.nw-adv-name {
    color: #fff;
    font-weight: 800;
    font-size: 13px;
    margin-top: 6px;
}

.nw-adv-meta {
    color: rgba(255,255,255,.5);
    font-size: 11px;
    margin-top: 2px;
}

.nw-active-dot {
    display: inline-block;
    margin-top: 8px;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    background: rgba(204,255,0,.15);
    color: #ccff00;
    letter-spacing: .5px;
    text-transform: uppercase;
}

.nw-report-output {
    padding: 24px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--border);
}

.nw-report-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--border);
}

.nw-report-kicker {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--brand);
    margin-bottom: 4px;
}

.nw-report-title {
    font-size: 22px;
    font-weight: 900;
}

.nw-report-sub,
.nw-report-date {
    color: var(--muted);
    font-size: 13px;
}

.nw-report-date {
    text-align: right;
    line-height: 1.6;
}

.nw-report-kpis {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.nw-report-kpis div {
    background: #f9fafb;
    border-radius: 10px;
    padding: 16px;
    border-left: 3px solid var(--brand);
}

.nw-report-kpis span,
.nw-report-kpis strong {
    display: block;
}

.nw-report-kpis span {
    font-size: 11px;
    color: var(--muted);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 4px;
}

.nw-report-kpis strong {
    font-size: 22px;
    font-weight: 900;
    color: var(--brand);
}

.nw-report-summary {
    background: var(--brand-pale);
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 16px;
    font-size: 13px;
    line-height: 1.8;
    color: var(--mid);
}

.nw-report-bottom {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

@media (max-width: 900px) {
    .nw-header {
        flex-direction: column;
        gap: 14px;
        align-items: flex-start;
    }

    .nw-placements-grid,
    .nw-kpi-grid,
    .nw-pkg-grid,
    .nw-report-kpis {
        grid-template-columns: repeat(2, 1fr);
    }

    .nw-sponsor-row {
        grid-template-columns: 1fr 1fr;
    }

    .nw-form-grid,
    .nw-analytics-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .nw-placements-grid,
    .nw-kpi-grid,
    .nw-pkg-grid,
    .nw-country-grid,
    .nw-report-kpis,
    .nw-pkg-form-grid {
        grid-template-columns: 1fr;
    }

    .nw-report-head,
    .nw-price-summary,
    .nw-pkg-row {
        flex-direction: column;
        display: flex;
        align-items: flex-start;
    }

    .nw-report-date {
        text-align: left;
    }
}
</style>
