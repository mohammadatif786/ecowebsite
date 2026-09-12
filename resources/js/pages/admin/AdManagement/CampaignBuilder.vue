<script setup lang="ts">
import { ref, computed } from 'vue';
import '@/pages/admin/AdManagement/style.css';
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

const props = defineProps<{
    fmt: (n: number) => string;
    num: (n: number) => string;
    campaignTypes?: any[];
    territoryTiers?: any[];
    deliveryChannels?: any[];
    exclusivityUpgrades?: any[];
    adIndustries?: any[];
    adSurgeOptions?: any[];
    campaignLaunches?: any[];
}>();

const emit = defineEmits(['view-changed']);

const viewMode = ref<'list' | 'builder'>('list');
type CampaignTypeKey = string | number;
type ChannelKey = string | number;
type ExclusivityKey = string | number;
type TierKey = string | number;

// ============================================================
// ACTIVE CAMPAIGNS (list view)
// ============================================================
const simpleNum = (value: unknown) => Number(value || 0).toLocaleString();
const parseCampaignDate = (value: unknown) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) return null;
    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
};
const formatCampaignDateRange = (startValue: unknown, endValue: unknown) => {
    const start = parseCampaignDate(startValue);
    const end = parseCampaignDate(endValue);
    if (!start || !end) return '';

    const sameYear = start.getFullYear() === end.getFullYear();
    const sameMonth = sameYear && start.getMonth() === end.getMonth();
    const startText = start.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: sameYear ? undefined : 'numeric',
    });
    const endText = end.toLocaleDateString('en-US', {
        month: sameMonth ? undefined : 'short',
        day: 'numeric',
        year: 'numeric',
    });

    return `${startText} - ${endText}`;
};
const formatLaunchRow = (launch: any) => {
    const type = launch.campaign_type_snapshot || {};
    const tier = launch.territory_tier_snapshot || {};
    const countries = launch.selected_countries || [];
    const diaspora = launch.selected_diaspora_markets || [];
    const channels = launch.delivery_channels_snapshot || [];
    const pricingSnapshot = launch.pricing_snapshot || {};
    const marketCount = countries.length + diaspora.length;
    const tierText = [tier.label, tier.name].filter(Boolean).join(' ');
    const dateRange = formatCampaignDateRange(launch.start_date, launch.end_date);

    return {
        launchId: launch.id,
        rawLaunch: launch,
        reference: launch.reference,
        icon: type.icon || '🚀',
        name: `${launch.reference || 'Campaign'} · ${type.name || 'Campaign'}`,
        meta: `${type.name || 'Campaign'} · ${tierText || 'Territory'} · ${marketCount} market${marketCount === 1 ? '' : 's'}${dateRange ? ' · ' + dateRange : ''}`,
        status: launch.status === 'ended' ? 'ended' : 'active',
        budget: Number(launch.final_total || pricingSnapshot.final_total || 0),
        channels: Array.isArray(channels) ? channels.length : 0,
        dateRange,
        typeName: type.name || 'Campaign',
        tierName: tierText || 'Territory',
        markets: [...countries, ...diaspora],
        countryCount: countries.length,
        diasporaCount: diaspora.length,
        channelList: channels.map((channel: any) => channel.name).filter(Boolean),
        industryName: launch.industry_snapshot?.name || '-',
        frequencyLabel: launch.frequency_snapshot?.label || '-',
        surgeLabel: launch.surge_active && launch.surge_snapshot ? `${launch.surge_snapshot.name || 'Surge'} (${launch.surge_snapshot.multiplier || ''}x)` : 'No surge',
        exclusivityLabel: launch.exclusivity_snapshot?.name || 'No exclusivity',
        calculatedTotal: Number(launch.calculated_total || pricingSnapshot.calculated_total || 0),
        finalTotal: Number(launch.final_total || pricingSnapshot.final_total || 0),
        pricingSnapshot,
        notes: launch.internal_notes || '',
        reach: pricingSnapshot.reach ? simpleNum(pricingSnapshot.reach) : '—',
    };
};

const defaultActiveCampaigns = [
    { icon: '⚡', name: 'Club Nova · Nassau Flash Drop', meta: 'Flash Campaign · Bahamas 🇧🇸 · Nassau Metro · Started May 18, 2026', status: 'active', budget: 750, channels: 2, reach: '48,200' },
    { icon: '🏆', name: 'Heineken · Caribbean Summer Push', meta: 'Regional Brand · Tier 4 Caribbean + Diaspora · Running Jun 1 – Jun 30, 2026', status: 'active', budget: 22500, channels: 8, reach: '1.2M' },
    { icon: '📅', name: 'Carnival Weekend 2026 · Trinidad + Jamaica', meta: 'Event Countdown · Tier 3 Multi-Country · Feb 24 – Mar 4, 2026 · Surge: 5x', status: 'ended', budget: 8400, channels: 6, reach: '890K' },
];
const activeCampaigns = ref(props.campaignLaunches?.length ? props.campaignLaunches.map(formatLaunchRow) : defaultActiveCampaigns);
const selectedCampaign = ref<any | null>(null);
const editingCampaignId = ref<number | null>(null);
const editingCampaignReference = ref('');
const closeCampaignView = () => { selectedCampaign.value = null; };
const viewCampaign = (campaign: any) => {
    if (!campaign.rawLaunch) {
        toast.error('This sample campaign does not have editable saved launch data.');
        return;
    }

    loadCampaignIntoForm(campaign.rawLaunch);
};

// ============================================================
// CAMPAIGN TYPE / TERRITORY TIER / CHANNEL / EXCLUSIVITY DATA
// (fully mutable via the "Manage" CRUD panels, like the source)
// ============================================================
const typeData = ref(props.campaignTypes?.map(t => ({
    id: t.id,
    key: t.id,
    icon: t.icon,
    name: t.name,
    duration: t.duration,
    pmin: t.priceMin,
    pmax: t.priceMax,
    base: t.basePrice,
    reach: t.reach,
    tax: t.taxRate,
    desc: t.description,
    enterprise: t.is_enterprise || t.isEnterprise
})) || [
    { key: 'flash', icon: '⚡', name: 'Flash Campaign', duration: '24 hours', pmin: 250, pmax: 1500, base: 750, reach: 15000, tax: 0, desc: '1 push · boosted feed · analytics' },
    { key: 'weekend', icon: '🔥', name: 'Weekend Domination', duration: '72 hours', pmin: 2500, pmax: 10000, base: 5000, reach: 80000, tax: 0, desc: 'Multi-push · homepage · events · email' },
    { key: 'countdown', icon: '📅', name: 'Event Countdown', duration: '7 days', pmin: 0, pmax: 0, base: 3500, reach: 55000, tax: 0, desc: '4-phase push sequence · feed · email · event page' },
    { key: 'regional', icon: '🏆', name: 'Regional Brand', duration: '14–30 days', pmin: 15000, pmax: 250000, base: 35000, reach: 400000, tax: 0, desc: 'Full-network · enterprise analytics · all channels' },
    { key: 'enterprise', icon: '👑', name: 'Enterprise Network', duration: '30–90 days', pmin: 50000, pmax: 500000, base: 60000, reach: 750000, tax: 0, desc: 'Full-network domination · dedicated account team · custom SLA · priority placements', enterprise: true },
]);

const tierData = ref(props.territoryTiers?.length ? props.territoryTiers.map(t => ({
    id: t.id,
    num: t.id,
    label: t.label,
    name: t.name,
    pmin: t.price_min ?? t.priceMin ?? 0,
    pmax: t.price_max ?? t.priceMax ?? 0,
    mult: t.multiplier ?? t.mult ?? 1
})) : [
    { num: 1, label: 'Tier 1', name: 'Local Territory', pmin: 250, pmax: 750, mult: 1 },
    { num: 2, label: 'Tier 2', name: 'National Territory', pmin: 750, pmax: 2500, mult: 1.8 },
    { num: 3, label: 'Tier 3', name: 'Multi-Country Caribbean', pmin: 3000, pmax: 10000, mult: 3.5 },
    { num: 4, label: 'Tier 4', name: 'Caribbean + Diaspora', pmin: 10000, pmax: 35000, mult: 7 },
    { num: 5, label: 'Tier 5', name: 'Central America + Caribbean', pmin: 15000, pmax: 50000, mult: 12 },
    { num: 6, label: 'Tier 6', name: 'Latin America Network', pmin: 50000, pmax: 250000, mult: 25 },
]);

const channelData = ref(props.deliveryChannels?.length ? props.deliveryChannels.map(ch => ({
    id: ch.id,
    key: ch.id,
    icon: ch.icon,
    name: ch.name,
    price: ch.price ?? 0,
    unit: ch.unit ?? '',
    locked: !!ch.locked
})) : [
    { key: 'push', icon: '🔔', name: 'Push Notifications', price: 0, unit: '', locked: true },
    { key: 'feed', icon: '📱', name: 'In-App Feed', price: 500, unit: 'month', locked: false },
    { key: 'email', icon: '✉️', name: 'Email Sponsorships', price: 750, unit: 'campaign', locked: false },
    { key: 'events', icon: '🎪', name: 'Event Integrations', price: 1000, unit: 'event', locked: false },
    { key: 'homepage', icon: '🏠', name: 'Homepage Placements', price: 1200, unit: 'week', locked: false },
    { key: 'marketplace', icon: '🛍️', name: 'Marketplace Ads', price: 400, unit: 'campaign', locked: false },
    { key: 'wallet', icon: '💳', name: 'Wallet Sponsorships', price: 600, unit: 'campaign', locked: false },
    { key: 'live', icon: '🔴', name: 'LinkUp Live', price: 2000, unit: 'campaign', locked: false },
    { key: 'match', icon: '💜', name: 'Match Feed', price: 800, unit: 'campaign', locked: false },
    { key: 'sponsored-notif', icon: '🔔', name: 'Sponsored Notifications', price: 350, unit: 'campaign', locked: false },
]);

const exclOptions = ref(props.exclusivityUpgrades?.length ? props.exclusivityUpgrades.map(o => ({
    id: o.id,
    key: o.id,
    icon: o.icon,
    name: o.name,
    pct: Number(o.pct) || 0
})) : [
    { key: 'beer', icon: '🍺', name: 'Exclusive Beer Sponsor', pct: 300 },
    { key: 'rum', icon: '🥃', name: 'Exclusive Rum Sponsor', pct: 300 },
    { key: 'vodka', icon: '🍸', name: 'Exclusive Vodka Sponsor', pct: 250 },
    { key: 'airline', icon: '✈️', name: 'Exclusive Airline', pct: 400 },
    { key: 'telecom', icon: '📡', name: 'Exclusive Telecom', pct: 350 },
    { key: 'bank', icon: '🏧', name: 'Exclusive Bank', pct: 250 },
]);

// Static geography lists (not CRUD-managed in the source)
const caribbeanCountries = [
    { key: 'bahamas', flag: '🇧🇸', name: 'Bahamas' },
    { key: 'jamaica', flag: '🇯🇲', name: 'Jamaica' },
    { key: 'trinidad', flag: '🇹🇹', name: 'Trinidad & Tobago' },
    { key: 'barbados', flag: '🇧🇧', name: 'Barbados' },
    { key: 'guyana', flag: '🇬🇾', name: 'Guyana' },
    { key: 'haiti', flag: '🇭🇹', name: 'Haiti' },
    { key: 'domrep', flag: '🇩🇴', name: 'Dominican Rep.' },
    { key: 'puertorico', flag: '🇵🇷', name: 'Puerto Rico' },
    { key: 'stlucia', flag: '🇱🇨', name: 'St. Lucia' },
    { key: 'grenada', flag: '🇬🇩', name: 'Grenada' },
    { key: 'antigua', flag: '🇦🇬', name: 'Antigua & Barbuda' },
    { key: 'cayman', flag: '🇰🇾', name: 'Cayman Islands' },
    { key: 'turks', flag: '🇹🇨', name: 'Turks & Caicos' },
    { key: 'aruba', flag: '🇦🇼', name: 'Aruba' },
    { key: 'curacao', flag: '🇨🇼', name: 'Curaçao' },
];
const centralAmericaCountries = [
    { key: 'panama', flag: '🇵🇦', name: 'Panama' },
    { key: 'costarica', flag: '🇨🇷', name: 'Costa Rica' },
    { key: 'colombia', flag: '🇨🇴', name: 'Colombia' },
    { key: 'mexico', flag: '🇲🇽', name: 'Mexico' },
];
const diasporaHubs = [
    { key: 'miami', flag: '🇺🇸', name: 'Miami Metro' },
    { key: 'nyc', flag: '🇺🇸', name: 'New York City' },
    { key: 'toronto', flag: '🇨🇦', name: 'Toronto' },
    { key: 'london', flag: '🇬🇧', name: 'London' },
    { key: 'amsterdam', flag: '🇳🇱', name: 'Amsterdam' },
    { key: 'paris', flag: '🇫🇷', name: 'Paris' },
];

const industryOptions = ref(props.adIndustries?.map(i => ({
    value: i.slug || i.id,
    id: i.id,
    label: `${i.name} — ${i.multiplier}×`
})) || [
    { value: '', label: 'Select industry (affects pricing)' },
    { value: 'general', label: 'General — 1× (no multiplier)' },
    { value: 'food', label: 'Food & Beverage — 1.5×' },
    { value: 'alcohol', label: 'Alcohol Brands — 2× MULTIPLIER' },
    { value: 'nightlife', label: 'Nightlife & Entertainment — 1.8×' },
    { value: 'airlines', label: 'Airlines & Travel — 2.5× MULTIPLIER' },
    { value: 'tourism', label: 'Tourism Boards — 3× MULTIPLIER' },
    { value: 'telecoms', label: 'Telecoms (e.g. Digicel, Flow) — 2× MULTIPLIER' },
    { value: 'banking', label: 'Banking & Finance (e.g. Scotiabank, NCB) — 1.5×' },
    { value: 'realestate', label: 'Real Estate — 1.3×' },
    { value: 'fashion', label: 'Fashion & Lifestyle — 1.4×' },
    { value: 'political', label: 'Political Campaigns — 5× MULTIPLIER ⚠️' },
    { value: 'casino', label: 'Casinos & Gaming — 3× MULTIPLIER' },
]);

const surgeOptions = ref(props.adSurgeOptions?.map(s => ({
    id: s.id,
    name: s.name,
    mult: s.multiplier,
    label: s.description || `${s.multiplier}×`
})) || [
    { name: 'Normal Weekend', mult: 1.5, label: '1.5×' },
    { name: 'Concert Weekend', mult: 2, label: '2×' },
    { name: 'Tourism Season', mult: 2, label: '2×' },
    { name: 'Carnival Weekend', mult: 4, label: '3×–5×' },
    { name: "New Year's Eve", mult: 5, label: '5×' },
    { name: 'Election Season', mult: 4, label: '4×' },
]);

const frequencyOptions = [
    { value: 1, label: 'One-Time Burst — 1x (single campaign)' },
    { value: 1.2, label: 'Bi-Weekly — 1.2x (2x per month)' },
    { value: 1.4, label: 'Weekly Rotation — 1.4x (4x per month)' },
    { value: 1.8, label: 'Monthly Retainer — 1.8x (ongoing managed)' },
    { value: 2.2, label: 'Quarterly Retainer — 2.2x (90-day managed)' },
];
const defaultSurgeOptions = [
    { name: 'Normal Weekend', mult: 1.5, label: '1.5x' },
    { name: 'Concert Weekend', mult: 2, label: '2x' },
    { name: 'Tourism Season', mult: 2, label: '2x' },
    { name: 'Carnival Weekend', mult: 4, label: '3x-5x' },
    { name: "New Year's Eve", mult: 5, label: '5x' },
    { name: 'Election Season', mult: 4, label: '4x' },
];
if (!props.adSurgeOptions?.length) {
    surgeOptions.value = [...defaultSurgeOptions];
}

if (!industryOptions.value.some(opt => !opt.value)) {
    industryOptions.value.unshift({ value: '', label: 'Select industry (affects pricing)' });
}
if (!industryOptions.value.length) {
    industryOptions.value = [
        { value: '', label: 'Select industry (affects pricing)' },
        { value: 'general', label: 'General — 1x (no multiplier)' },
    ];
}
if (industryOptions.value.length === 1 && !industryOptions.value[0].value) {
    industryOptions.value.push({ value: 'general', label: 'General — 1x (no multiplier)' });
}
if (!surgeOptions.value.length) {
    surgeOptions.value = [...defaultSurgeOptions];
}

const INDUSTRY_MULT = computed(() => {
    const map: Record<string, number> = { '': 1 };
    industryOptions.value.forEach(opt => {
        if (opt.value) {
            const multStr = opt.label.split('—')[1]?.replace('×', '').trim();
            map[opt.value] = parseFloat(multStr) || 1;
        }
    });
    return map;
});

const INDUSTRY_LABELS = computed(() => {
    const map: Record<string, string> = { '': '' };
    industryOptions.value.forEach(opt => {
        if (opt.value) {
            map[opt.value] = opt.label.split('—')[1]?.trim() || '';
        }
    });
    return map;
});

const getIndustryMultiplier = (value: string) => {
    const opt = industryOptions.value.find(o => String(o.value) === String(value));
    if (!opt) return 1;
    const match = String(opt.label).match(/(\d+(?:\.\d+)?)/);
    return match ? Number(match[1]) : 1;
};
const getIndustryLabel = (value: string) => {
    const opt = industryOptions.value.find(o => String(o.value) === String(value));
    if (!opt) return '';
    return String(opt.label).split('—')[1]?.trim() || String(opt.label).split('â€”')[1]?.trim() || '';
};

// ============================================================
// BUILDER STATE
// ============================================================
const cbType = ref<CampaignTypeKey | null>(null);
const cbTier = ref<TierKey | null>(null);
const cbCountries = ref<string[]>([]);
const cbDiaspora = ref<string[]>([]);
const cbChannels = ref<ChannelKey[]>(channelData.value.filter(ch => ch.locked).map(ch => ch.key));
const cbIndustry = ref('');
const cbFrequency = ref(1);
const cbSurge = ref(false);
const cbSurgeMultiplier = ref(1.5);
const cbSurgeSelectedIdx = ref<number | null>(null);
const cbExcl = ref<ExclusivityKey | null>(null);
const cbStartDate = ref('');
const cbEndDate = ref('');
const cbOverrideOpen = ref(false);
const cbOverrideAmount = ref<number | null>(null);
const cbOverrideNotes = ref('');
const isLaunching = ref(false);

const collapsed = ref({ type: false, territory: false, channels: false, industry: false });
const typeDoneReady = ref(false);
const territoryReady = computed(() => cbTier.value !== null && (cbCountries.value.length + cbDiaspora.value.length) > 0);

const manageOpen = ref<'type' | 'territory' | 'channels' | 'excl' | 'industry' | 'surge' | null>(null);

const typeEditKey = ref<string | null>(null);
const typeEditForm = ref({ icon: '', name: '', duration: '', pmin: 0 as number | string, pmax: 0 as number | string, base: 0 as number | string, reach: 0 as number | string, tax: 0 as number | string, desc: '' });
const typeAddOpen = ref(false);
const typeAddForm = ref({ icon: '', name: '', duration: '', pmin: '', pmax: '', base: '', reach: '', tax: '', desc: '' });

const tierEditNum = ref<number | null>(null);
const tierEditForm = ref({ label: '', name: '', pmin: 0 as number | string, pmax: 0 as number | string, mult: 1 as number | string });
const tierAddOpen = ref(false);
const tierAddForm = ref({ label: '', name: '', pmin: '', pmax: '', mult: '' });

const channelEditKey = ref<string | null>(null);
const channelEditForm = ref({ icon: '', name: '', price: 0 as number | string, unit: '', locked: false });
const channelAddOpen = ref(false);
const channelAddForm = ref({ icon: '', name: '', price: '', unit: '', locked: false });

const exclEditKey = ref<string | null>(null);
const exclEditForm = ref({ icon: '', name: '', pct: 0 as number | string });
const exclAddOpen = ref(false);
const exclAddForm = ref({ icon: '', name: '', pct: '' });
const exclIconPickerOpen = ref<'edit' | 'add' | null>(null);
const exclIconCategories = [
    { name: '⭐ Popular', icons: ['⚡', '🔥', '📅', '🏆', '👑', '🎯', '🚀', '💎', '⭐', '🌟', '🎉', '💡', '🔔', '🎪', '💳', '🏅', '🥇', '🎊', '✅', '🔴', '🟡', '🟢', '🔵'] },
    { name: '💼 Business', icons: ['💼', '📊', '📈', '💰', '🤝', '🏦', '🏧', '📋', '🗂️', '💹', '📣', '🔗', '📰', '🏢', '📝', '✍️', '🖊️', '📌', '📎', '🗓️', '💵', '💴', '💶', '💷'] },
    { name: '🍺 Food & Drink', icons: ['🍺', '🥃', '🍸', '🍷', '🥂', '🍹', '🍔', '🌮', '☕', '🎂', '🍕', '🥗', '🍣', '🍜', '🌯', '🥩', '🍦', '🧃', '🧉', '🫖', '🥤', '🧊', '🍫', '🍬'] },
    { name: '✈️ Travel', icons: ['✈️', '🚢', '🏨', '🗺️', '🧳', '🏖️', '🌴', '🌊', '🚗', '🌍', '🛥️', '🏝️', '🗼', '🏔️', '⛵', '🚁', '🛩️', '🚂', '🌅', '🏕️', '🎒', '🧭', '🏄', '🤿'] },
    { name: '🎬 Entertainment', icons: ['🎬', '🎭', '🎵', '🎤', '🎸', '🎡', '🎮', '🎰', '🏟️', '🎃', '🎄', '🎆', '🎇', '🎠', '🎫', '🎟️', '🎨', '🖼️', '🎻', '🥁', '🎷', '🎺', '🎙️', '📽️'] },
    { name: '📱 Tech', icons: ['📱', '📺', '📡', '💻', '🖥️', '🛰️', '🔭', '🔬', '📲', '🌐', '⚙️', '🔧', '💾', '🖨️', '⌨️', '🖱️', '📟', '📠', '🔋', '🔌', '📸', '📷', '🎙️', '📡'] },
    { name: '🏀 Sports', icons: ['⚽', '🏀', '🏈', '🏆', '🥊', '🎽', '🏋️', '⛷️', '🏇', '🎾', '🏊', '🤸', '🏌️', '🤺', '🏹', '🎣', '🧘', '🏂', '🏒', '🥋', '🤼', '🚴', '🧗', '🏆'] },
    { name: '🌺 Nature', icons: ['🌺', '🌴', '🌊', '🦜', '🦁', '🐬', '🌸', '🍀', '🌿', '🌻', '🦋', '🐠', '🌙', '☀️', '🌈', '⛅', '❄️', '🌋', '🏜️', '🌾', '🍂', '🐢', '🦩', '🐦'] },
    { name: '🇯🇲 Caribbean', icons: ['🇯🇲', '🇧🇧', '🇹🇹', '🇧🇸', '🇬🇾', '🇭🇹', '🇩🇴', '🇵🇷', '🇱🇨', '🇬🇩', '🇦🇬', '🇰🇾', '🇹🇨', '🇦🇼', '🇨🇼', '🇨🇺', '🇧🇿', '🇸🇷', '🇵🇦', '🇨🇷', '🇲🇽', '🇨🇴', '🇧🇷', '🇦🇷'] },
];

const industryEditId = ref<number | null>(null);
const industryEditForm = ref({ name: '', multiplier: 1 as number | string });
const industryAddOpen = ref(false);
const industryAddForm = ref({ name: '', multiplier: '' as string });

const surgeEditId = ref<number | null>(null);
const surgeEditForm = ref({ name: '', multiplier: 1 as number | string, description: '' });
const surgeAddOpen = ref(false);
const surgeAddForm = ref({ name: '', multiplier: '' as string, description: '' });

// ============================================================
// VALIDATION ERRORS
// ============================================================
const typeAddErrors = ref<Record<string, string[]>>({});
const typeEditErrors = ref<Record<string, string[]>>({});
const tierAddErrors = ref<Record<string, string[]>>({});
const tierEditErrors = ref<Record<string, string[]>>({});
const channelAddErrors = ref<Record<string, string[]>>({});
const channelEditErrors = ref<Record<string, string[]>>({});
const exclAddErrors = ref<Record<string, string[]>>({});
const exclEditErrors = ref<Record<string, string[]>>({});
const industryAddErrors = ref<Record<string, string[]>>({});
const industryEditErrors = ref<Record<string, string[]>>({});
const surgeAddErrors = ref<Record<string, string[]>>({});
const surgeEditErrors = ref<Record<string, string[]>>({});

const getError = (errors: Record<string, string[]>, field: string) => errors[field]?.[0] || '';
const clearErrors = (errorsRef: any) => { errorsRef.value = {}; };

// ============================================================
// LOOKUP HELPERS
// ============================================================
const getType = (key: CampaignTypeKey | null) => typeData.value.find(t => String(t.key) === String(key) || String(t.id) === String(key));
const getTier = (num: TierKey | null) => tierData.value.find(t => String(t.num) === String(num) || String(t.id) === String(num));
const getChannel = (key: string | number) => channelData.value.find(c => String(c.key) === String(key) || String(c.id) === String(key));
const getExcl = (key: ExclusivityKey | null) => exclOptions.value.find(o => String(o.key) === String(key) || String(o.id) === String(key));
const getExclMult = (key: ExclusivityKey) => { const o = getExcl(key); return o ? 1 + o.pct / 100 : 1; };
const isTypeSelected = (key: CampaignTypeKey) => cbType.value !== null && String(cbType.value) === String(key);
const isTierSelected = (key: TierKey) => cbTier.value !== null && String(cbTier.value) === String(key);
const isExclSelected = (key: ExclusivityKey) => cbExcl.value !== null && String(cbExcl.value) === String(key);
const getExclLabel = (key: ExclusivityKey | null) => {
    const o = getExcl(key);
    return o ? `${o.name} (+${o.pct}%)` : 'None';
};
const isChannelSelected = (key: ChannelKey) => cbChannels.value.some(k => String(k) === String(key));

const typePriceLabel = (t: { pmin: number; pmax: number }) =>
    t.pmin === 0 && t.pmax === 0 ? 'Variable pricing' : `$${t.pmin.toLocaleString()} – $${t.pmax.toLocaleString()}${t.pmax >= 10000 ? '+' : ''}`;
const tierRangeLabel = (t: { pmin: number; pmax: number }) =>
    t.pmin > 0 ? `$${t.pmin.toLocaleString()} – $${t.pmax.toLocaleString()}+` : 'Custom';
const channelPriceLabel = (ch: { price: number; unit: string; locked: boolean }) =>
    ch.locked ? 'Included in all campaigns' : `+$${ch.price.toLocaleString()}${ch.unit ? ' / ' + ch.unit : ''}`;

const fmtMoney = (n: number): string => {
    if (n >= 1000000) return '$' + (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (n >= 1000) return '$' + Math.round(n / 1000) + 'K';
    return '$' + Math.round(n).toLocaleString();
};
const fmtFull = (n: number) => '$' + Math.round(n).toLocaleString();
const fmtReach = (n: number): string => {
    if (n >= 1000000) return (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
    return Math.round(n).toLocaleString();
};

const durationMult = computed(() => {
    if (!cbStartDate.value || !cbEndDate.value) return 1;
    const days = Math.max(1, (new Date(cbEndDate.value).getTime() - new Date(cbStartDate.value).getTime()) / 86400000);
    if (days <= 1) return 1;
    if (days <= 3) return 1.2;
    if (days <= 7) return 1.8;
    if (days <= 14) return 2.2;
    if (days <= 30) return 3;
    return 4;
});

// ============================================================
// LIVE PRICING ENGINE
// ============================================================
const pricing = computed(() => {
    const type = getType(cbType.value);
    const base = type?.base || 0;
    const tier = getTier(cbTier.value);
    const tierNum = Number(tier?.num ?? cbTier.value ?? 0);
    const tierMult = tier?.mult || 1;
    const industryMult = getIndustryMultiplier(cbIndustry.value);
    const surgeMult = cbSurge.value ? cbSurgeMultiplier.value : 1;
    const freqMult = Number(cbFrequency.value) || 1;

    const rawChannelAddons = cbChannels.value.reduce((sum, key) => {
        const ch = getChannel(key);
        return ch && !ch.locked ? sum + ch.price : sum;
    }, 0);
    const channelAddons = rawChannelAddons * tierMult;

    const totalGeos = cbCountries.value.length + cbDiaspora.value.length;
    const tierMinCountries = [0, 1, 1, 3, 5, 4, 10][tierNum] || 0;
    const extraGeos = Math.max(0, totalGeos - tierMinCountries);
    const geoAddon = base * tierMult * extraGeos * 0.15;

    const corePrice = base * tierMult * industryMult * surgeMult * durationMult.value * freqMult;

    let exclPremium = 0;
    if (cbExcl.value) exclPremium = base * tierMult * (getExclMult(cbExcl.value) - 1);

    const taxRate = (type?.tax || 0) / 100;
    const subtotal = corePrice + channelAddons + geoAddon + exclPremium;
    const taxAmount = subtotal * taxRate;
    const calculatedTotal = subtotal + taxAmount;

    const baseReach = type?.reach || 0;
    const reach = Math.round(baseReach * tierMult * (1 + extraGeos * 0.1));
    const impressions = Math.round(reach * 4.2);
    const typeKey = cbType.value === null ? '' : String(cbType.value);
    const ctrMap: Record<string, string> = { flash: '4.8%', weekend: '3.9%', countdown: '5.2%', regional: '6.1%', enterprise: '7.4%' };
    const ctr = ctrMap[typeKey] || '—';

    const mediaMult = type?.enterprise ? 5 : 3 + ((tierNum + (cbType.value ? 1 : 0)) % 3) * 0.7;
    const mediaValue = calculatedTotal > 0 ? Math.round((calculatedTotal * mediaMult) / 500) * 500 : 0;

    let scarcitySlots = 0;
    let scarcityPct = 0;
    if (cbType.value && cbTier.value !== null) {
        const seed = (tierNum * 3 + typeKey.length) % 5;
        scarcitySlots = type?.enterprise ? 1 : [1, 2, 2, 3, 1][seed];
        scarcityPct = Math.round(((5 - scarcitySlots) / 5) * 100);
    }

    let totalRangeText = 'Select campaign type to get started';
    if (cbType.value && cbTier.value === null) {
        totalRangeText = 'Select territory tier to continue';
    } else if (cbType.value && cbTier.value !== null) {
        const lo = Math.round((calculatedTotal * 0.85) / 100) * 100;
        const hi = Math.round((calculatedTotal * 1.25) / 100) * 100;
        totalRangeText = 'Est. range: ' + fmtMoney(lo) + ' – ' + fmtMoney(hi);
    }

    return {
        base, tierMult, industryMult, surgeMult, freqMult, channelAddons, geoAddon, exclPremium,
        taxRate, taxAmount, subtotal, calculatedTotal,
        reach, impressions, ctr, mediaValue, scarcitySlots, scarcityPct, totalRangeText,
    };
});

const displayTotalText = computed(() => {
    if (cbOverrideAmount.value) return '$' + cbOverrideAmount.value.toLocaleString();
    return pricing.value.calculatedTotal >= 1000 ? fmtMoney(pricing.value.calculatedTotal) : fmtFull(pricing.value.calculatedTotal);
});
const displayRangeText = computed(() => (cbOverrideAmount.value ? 'Custom negotiated rate' : pricing.value.totalRangeText));
const mvCostText = computed(() => {
    if (cbOverrideAmount.value) return '$' + cbOverrideAmount.value.toLocaleString();
    return pricing.value.calculatedTotal > 0 ? fmtMoney(pricing.value.calculatedTotal) : '—';
});
const mvValueText = computed(() => (pricing.value.calculatedTotal > 0 ? fmtMoney(pricing.value.mediaValue) : '—'));

const execSummaryLines = computed(() => {
    if (!(cbType.value && cbTier.value !== null)) return null;
    const type = getType(cbType.value);
    const tierLabel = 'Tier ' + cbTier.value + ' — ' + (getTier(cbTier.value)?.name || '');
    const freqOpt = frequencyOptions.find(f => f.value === Number(cbFrequency.value));
    const freqLabel = pricing.value.freqMult > 1 ? (freqOpt ? freqOpt.label.split('—')[0].trim() : '') : 'One-Time';
    const surgeLabel = cbSurge.value ? cbSurgeMultiplier.value + '× Surge' : 'No surge';
    const exclLabel = cbExcl.value ? getExclLabel(cbExcl.value) : 'No exclusivity';
    const mktCnt = cbCountries.value.length + cbDiaspora.value.length;
    return [
        `<strong>${type?.name || cbType.value}</strong> · ${tierLabel}`,
        mktCnt > 0 ? `${mktCnt} market${mktCnt !== 1 ? 's' : ''} selected` : 'No markets selected yet',
        pricing.value.reach > 0 ? `Est. reach: <strong>${fmtReach(pricing.value.reach)}</strong> · CTR: ${pricing.value.ctr}` : '',
        `Frequency: ${freqLabel} · ${surgeLabel}`,
        exclLabel,
        pricing.value.calculatedTotal > 0 ? `Investment: <strong>${fmtFull(Math.round(pricing.value.calculatedTotal))}</strong>` : '',
    ].filter(Boolean).join('<br>');
});

const typeSummaryLabel = computed(() => {
    const t = getType(cbType.value);
    return t ? `${t.icon} ${t.name}` : 'None selected';
});
const territorySummaryChips = computed(() => {
    const tier = getTier(cbTier.value);
    const chips = [`Tier ${cbTier.value} — ${tier?.name || 'None'}`];
    const cnt = cbCountries.value.length + cbDiaspora.value.length;
    if (cnt > 0) chips.push(`${cnt} location${cnt > 1 ? 's' : ''} selected`);
    return chips;
});
const channelsSummaryChips = computed(() => {
    if (!cbChannels.value.length) return ['🔔 Push Notifications (included)'];
    return cbChannels.value.map(key => {
        const ch = getChannel(key);
        return ch ? `${ch.icon} ${ch.name}` : key;
    });
});

// ============================================================
// VIEW TOGGLES
// ============================================================
const dateInputValue = (value: unknown) => String(value || '').match(/^(\d{4}-\d{2}-\d{2})/)?.[1] || '';
const findMarketKeys = (names: string[], options: { key: string; name: string }[]) => {
    return names
        .map(name => options.find(opt => opt.key === name || opt.name === name)?.key)
        .filter((key): key is string => !!key);
};
const findTypeKeyFromSnapshot = (snapshot: any) => {
    const match = typeData.value.find(t => String(t.id ?? t.key) === String(snapshot?.id) || t.name === snapshot?.name);
    return match?.key ?? snapshot?.id ?? null;
};
const findTierKeyFromSnapshot = (snapshot: any) => {
    const match = tierData.value.find(t => String(t.id ?? t.num) === String(snapshot?.id) || t.name === snapshot?.name || t.label === snapshot?.label);
    return match?.num ?? snapshot?.id ?? null;
};
const findIndustryValueFromSnapshot = (snapshot: any) => {
    const match = industryOptions.value.find((opt: any) => String(opt.id ?? opt.value) === String(snapshot?.id) || String(opt.label).split('â€”')[0]?.trim() === snapshot?.name);
    return match?.value ?? '';
};
const findExclKeyFromSnapshot = (snapshot: any) => {
    if (!snapshot) return null;
    const match = exclOptions.value.find(opt => String(opt.id ?? opt.key) === String(snapshot.id) || opt.name === snapshot.name);
    return match?.key ?? snapshot.id ?? null;
};
const resetCampaignForm = () => {
    editingCampaignId.value = null;
    editingCampaignReference.value = '';
    cbType.value = null;
    cbTier.value = null;
    cbCountries.value = [];
    cbDiaspora.value = [];
    cbChannels.value = channelData.value.filter(ch => ch.locked).map(ch => ch.key);
    cbIndustry.value = '';
    cbFrequency.value = 1;
    cbSurge.value = false;
    cbSurgeMultiplier.value = 1.5;
    cbSurgeSelectedIdx.value = null;
    cbExcl.value = null;
    cbStartDate.value = '';
    cbEndDate.value = '';
    cbOverrideOpen.value = false;
    cbOverrideAmount.value = null;
    cbOverrideNotes.value = '';
    typeDoneReady.value = false;
    collapsed.value = { type: false, territory: false, channels: false, industry: false };
    manageOpen.value = null;
};
const startNewCampaign = () => {
    resetCampaignForm();
    viewMode.value = 'builder';
};
const loadCampaignIntoForm = (launch: any) => {
    const channels = launch.delivery_channels_snapshot || [];
    const surge = launch.surge_snapshot || null;

    editingCampaignId.value = launch.id;
    editingCampaignReference.value = launch.reference || '';
    cbType.value = findTypeKeyFromSnapshot(launch.campaign_type_snapshot);
    cbTier.value = findTierKeyFromSnapshot(launch.territory_tier_snapshot);
    cbCountries.value = findMarketKeys(launch.selected_countries || [], [...caribbeanCountries, ...centralAmericaCountries]);
    cbDiaspora.value = findMarketKeys(launch.selected_diaspora_markets || [], diasporaHubs);
    cbChannels.value = channels
        .map((snapshot: any) => {
            const match = channelData.value.find(ch => String(ch.id ?? ch.key) === String(snapshot.id) || ch.name === snapshot.name);
            return match?.key;
        })
        .filter((key: ChannelKey | undefined): key is ChannelKey => key !== undefined);
    if (!cbChannels.value.length) cbChannels.value = channelData.value.filter(ch => ch.locked).map(ch => ch.key);
    cbIndustry.value = findIndustryValueFromSnapshot(launch.industry_snapshot);
    cbFrequency.value = Number(launch.frequency_snapshot?.value) || 1;
    cbStartDate.value = dateInputValue(launch.start_date);
    cbEndDate.value = dateInputValue(launch.end_date);
    cbSurge.value = !!launch.surge_active;
    cbSurgeSelectedIdx.value = surge
        ? surgeOptions.value.findIndex(opt => String((opt as any).id) === String(surge.id) || opt.name === surge.name)
        : null;
    if (cbSurgeSelectedIdx.value === -1) cbSurgeSelectedIdx.value = null;
    cbSurgeMultiplier.value = cbSurgeSelectedIdx.value !== null
        ? Number(surgeOptions.value[cbSurgeSelectedIdx.value]?.mult) || 1.5
        : Number(surge?.multiplier) || 1.5;
    cbExcl.value = findExclKeyFromSnapshot(launch.exclusivity_snapshot);
    cbOverrideAmount.value = Number(launch.final_total) !== Number(launch.calculated_total) ? Number(launch.final_total) : null;
    cbOverrideNotes.value = launch.internal_notes || '';
    cbOverrideOpen.value = !!(cbOverrideAmount.value || cbOverrideNotes.value);
    typeDoneReady.value = !!cbType.value;
    collapsed.value = { type: false, territory: false, channels: false, industry: false };
    manageOpen.value = null;
    viewMode.value = 'builder';
};
const showBuilder = startNewCampaign;
const showList = () => { viewMode.value = 'list'; };

// ============================================================
// SELECTION HANDLERS
// ============================================================
const selectType = (key: CampaignTypeKey) => { cbType.value = key; typeDoneReady.value = true; };
const confirmType = () => {
    if (!cbType.value) { toast.error('Please select a campaign type before confirming.'); return; }
    typeDoneReady.value = true;
    collapseSection('type');
};

const selectTier = (num: TierKey) => { cbTier.value = num; };

const toggleCountry = (key: string) => {
    const idx = cbCountries.value.indexOf(key);
    if (idx === -1) cbCountries.value.push(key); else cbCountries.value.splice(idx, 1);
};
const toggleDiaspora = (key: string) => {
    const idx = cbDiaspora.value.indexOf(key);
    if (idx === -1) cbDiaspora.value.push(key); else cbDiaspora.value.splice(idx, 1);
};

const toggleChannel = (key: ChannelKey) => {
    const ch = getChannel(key);
    if (ch?.locked) return;
    const idx = cbChannels.value.findIndex(k => String(k) === String(key));
    if (idx === -1) cbChannels.value.push(key); else cbChannels.value.splice(idx, 1);
};

const collapseSection = (name: 'type' | 'territory' | 'channels') => { collapsed.value[name] = true; };
const expandSection = (name: 'type' | 'territory' | 'channels') => { collapsed.value[name] = false; };
const confirmTerritory = () => {
    if (cbTier.value === null) { toast.error('Please select a territory tier before confirming.'); return; }
    if (!territoryReady.value) { toast.error('Please select at least one country or diaspora hub before confirming.'); return; }
    collapseSection('territory');
};

const toggleSurge = () => {
    cbSurge.value = !cbSurge.value;
    if (cbSurge.value) {
        if (cbSurgeSelectedIdx.value === null) {
            cbSurgeSelectedIdx.value = 0;
            cbSurgeMultiplier.value = surgeOptions.value[0]?.mult || 1.5;
        }
    } else {
        cbSurgeMultiplier.value = 1.5;
        cbSurgeSelectedIdx.value = null;
    }
};
const selectSurgeType = (idx: number) => {
    cbSurgeSelectedIdx.value = idx;
    cbSurgeMultiplier.value = surgeOptions.value[idx]?.mult || 1.5;
};

const selectExclNone = () => { cbExcl.value = null; };
const selectExclByKey = (key: ExclusivityKey) => { cbExcl.value = isExclSelected(key) ? null : key; };
const toggleExclIconPicker = (target: 'edit' | 'add') => {
    exclIconPickerOpen.value = exclIconPickerOpen.value === target ? null : target;
};
const selectExclIcon = (target: 'edit' | 'add', icon: string) => {
    if (target === 'edit') exclEditForm.value.icon = icon;
    else exclAddForm.value.icon = icon;
    exclIconPickerOpen.value = null;
};

const toggleOverride = () => {
    cbOverrideOpen.value = !cbOverrideOpen.value;
    if (!cbOverrideOpen.value) cbOverrideAmount.value = null;
};
const onOverrideInput = (e: Event) => {
    const val = parseFloat((e.target as HTMLInputElement).value);
    cbOverrideAmount.value = !isNaN(val) && val > 0 ? val : null;
};

const toggleManage = (section: 'type' | 'territory' | 'channels' | 'excl' | 'industry' | 'surge') => {
    manageOpen.value = manageOpen.value === section ? null : section;
};

// ── Campaign Type CRUD ──
const startTypeEdit = (key: string | number) => {
    const t = getType(key); if (!t) return;
    clearErrors(typeEditErrors);
    typeEditKey.value = String(key);
    typeEditForm.value = { icon: t.icon, name: t.name, duration: t.duration, pmin: t.pmin, pmax: t.pmax, base: t.base, reach: t.reach, tax: t.tax || 0, desc: t.desc };
};
const saveTypeEdit = async () => {
    const t = getType(typeEditKey.value); if (!t) return;
    clearErrors(typeEditErrors);

    const payload = {
        icon: typeEditForm.value.icon.trim(),
        name: typeEditForm.value.name.trim(),
        duration: String(typeEditForm.value.duration).trim(),
        priceMin: Number(typeEditForm.value.pmin),
        priceMax: Number(typeEditForm.value.pmax),
        basePrice: Number(typeEditForm.value.base),
        reach: Number(typeEditForm.value.reach),
        taxRate: Number(typeEditForm.value.tax),
        description: typeEditForm.value.desc.trim(),
        isEnterprise: !!t.enterprise
    };

    try {
        const response = await axios.put(route('admin.ads.campaigns.update', { id: t.id || t.key }), payload);
        if (response.data.success) {
            const updated = response.data.campaignType;
            Object.assign(t, {
                icon: updated.icon,
                name: updated.name,
                duration: updated.duration,
                pmin: updated.priceMin,
                pmax: updated.priceMax,
                base: updated.basePrice,
                reach: updated.reach,
                tax: updated.taxRate,
                desc: updated.description,
            });
            typeEditKey.value = null;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            typeEditErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to update campaign type.');
        }
    }
};
const cancelTypeEdit = () => { typeEditKey.value = null; clearErrors(typeEditErrors); };
const toggleAddType = () => { typeAddOpen.value = !typeAddOpen.value; clearErrors(typeAddErrors); };
const addType = async () => {
    clearErrors(typeAddErrors);

    const payload = {
        icon: typeAddForm.value.icon.trim(),
        name: typeAddForm.value.name.trim(),
        duration: typeAddForm.value.duration.trim(),
        priceMin: typeAddForm.value.pmin === '' ? null : Number(typeAddForm.value.pmin),
        priceMax: typeAddForm.value.pmax === '' ? null : Number(typeAddForm.value.pmax),
        basePrice: typeAddForm.value.base === '' ? null : Number(typeAddForm.value.base),
        reach: typeAddForm.value.reach === '' ? null : Number(typeAddForm.value.reach),
        taxRate: typeAddForm.value.tax === '' ? null : Number(typeAddForm.value.tax),
        description: typeAddForm.value.desc.trim(),
        isEnterprise: false
    };

    try {
        const response = await axios.post(route('admin.ads.campaigns.store'), payload);
        const newType = response.data.campaignType ?? response.data.data ?? response.data;
        typeData.value.push({
            id: newType.id,
            key: newType.id,
            icon: newType.icon,
            name: newType.name,
            duration: newType.duration,
            pmin: newType.priceMin,
            pmax: newType.priceMax,
            base: newType.basePrice,
            reach: newType.reach,
            tax: newType.taxRate,
            desc: newType.description,
            enterprise: newType.isEnterprise || newType.is_enterprise
        });
        typeAddForm.value = { icon: '', name: '', duration: '', pmin: '', pmax: '', base: '', reach: '', tax: '', desc: '' };
        typeAddOpen.value = false;
    } catch (err: any) {
        if (err.response?.status === 422) {
            typeAddErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to add campaign type.');
        }
    }
};
const deleteType = async (key: string | number) => {
    if (!confirm('Delete this campaign type?')) return;
    const t = getType(key); if (!t) return;

    try {
        const response = await axios.delete(route('admin.ads.campaigns.destroy', { id: t.id || t.key }));
        if (response.data.success) {
            typeData.value = typeData.value.filter(t => String(t.key) !== String(key) && String(t.id) !== String(key));
            if (isTypeSelected(key)) cbType.value = null;
        }
    } catch (err) {
        console.error(err);
        toast.error('Failed to delete campaign type.');
    }
};

// ── Territory Tier CRUD ──
const startTierEdit = (num: number) => {
    const t = getTier(num); if (!t) return;
    clearErrors(tierEditErrors);
    tierEditNum.value = num;
    tierEditForm.value = { label: t.label, name: t.name, pmin: t.pmin, pmax: t.pmax, mult: t.mult };
};
const saveTierEdit = async () => {
    const t = getTier(tierEditNum.value as number); if (!t) return;
    clearErrors(tierEditErrors);

    const payload = {
        label: tierEditForm.value.label.trim(),
        name: tierEditForm.value.name.trim(),
        priceMin: Number(tierEditForm.value.pmin),
        priceMax: Number(tierEditForm.value.pmax),
        multiplier: Number(tierEditForm.value.mult),
    };

    try {
        const response = await axios.put(route('admin.ads.territory-tiers.update', { id: t.id || t.num }), payload);
        if (response.data.success) {
            const updated = response.data.tier;
            Object.assign(t, {
                label: updated.label,
                name: updated.name,
                pmin: updated.price_min,
                pmax: updated.price_max,
                mult: updated.multiplier,
            });
            tierEditNum.value = null;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            tierEditErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to update territory tier.');
        }
    }
};
const cancelTierEdit = () => { tierEditNum.value = null; clearErrors(tierEditErrors); };
const toggleAddTier = () => { tierAddOpen.value = !tierAddOpen.value; clearErrors(tierAddErrors); };
const addTier = async () => {
    clearErrors(tierAddErrors);

    const payload = {
        label: tierAddForm.value.label.trim(),
        name: tierAddForm.value.name.trim(),
        priceMin: tierAddForm.value.pmin === '' ? null : Number(tierAddForm.value.pmin),
        priceMax: tierAddForm.value.pmax === '' ? null : Number(tierAddForm.value.pmax),
        multiplier: tierAddForm.value.mult === '' ? null : Number(tierAddForm.value.mult),
    };

    try {
        const response = await axios.post(route('admin.ads.territory-tiers.store'), payload);
        if (response.data.success) {
            const newTier = response.data.tier;
            tierData.value.push({
                id: newTier.id,
                num: newTier.id,
                label: newTier.label,
                name: newTier.name,
                pmin: newTier.price_min,
                pmax: newTier.price_max,
                mult: newTier.multiplier,
            });
            tierAddForm.value = { label: '', name: '', pmin: '', pmax: '', mult: '' };
            tierAddOpen.value = false;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            tierAddErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to add territory tier.');
        }
    }
};
const deleteTier = async (num: number) => {
    if (!confirm('Delete this territory tier?')) return;
    const t = getTier(num); if (!t) return;

    try {
        const response = await axios.delete(route('admin.ads.territory-tiers.destroy', { id: t.id || t.num }));
        if (response.data.success) {
            tierData.value = tierData.value.filter(t => String(t.num) !== String(num) && String(t.id) !== String(num));
            if (isTierSelected(num)) cbTier.value = null;
        }
    } catch (err) {
        console.error(err);
        toast.error('Failed to delete territory tier.');
    }
};

// ── Delivery Channel CRUD ──
const startChannelEdit = (key: string | number) => {
    const ch = getChannel(key); if (!ch) return;
    clearErrors(channelEditErrors);
    channelEditKey.value = String(key);
    channelEditForm.value = { icon: ch.icon, name: ch.name, price: ch.price, unit: ch.unit, locked: ch.locked };
};
const saveChannelEdit = async () => {
    const ch = getChannel(channelEditKey.value as string); if (!ch) return;
    clearErrors(channelEditErrors);

    const payload = {
        icon: channelEditForm.value.icon.trim(),
        name: channelEditForm.value.name.trim(),
        price: Number(channelEditForm.value.price),
        unit: channelEditForm.value.unit.trim() || null,
        locked: !!channelEditForm.value.locked,
    };

    try {
        const response = await axios.put(route('admin.ads.delivery-channels.update', { id: ch.id || ch.key }), payload);
        if (response.data.success) {
            const updated = response.data.channel;
            Object.assign(ch, {
                icon: updated.icon,
                name: updated.name,
                price: updated.price,
                unit: updated.unit ?? '',
                locked: !!updated.locked,
            });
            if (ch.locked && !isChannelSelected(ch.key)) cbChannels.value.push(ch.key);
            if (!ch.locked) cbChannels.value = cbChannels.value.filter(k => String(k) !== String(ch.key));
            channelEditKey.value = null;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            channelEditErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to update delivery channel.');
        }
    }
};
const cancelChannelEdit = () => { channelEditKey.value = null; clearErrors(channelEditErrors); };
const toggleAddChannel = () => { channelAddOpen.value = !channelAddOpen.value; clearErrors(channelAddErrors); };
const addChannel = async () => {
    clearErrors(channelAddErrors);

    const payload = {
        icon: channelAddForm.value.icon.trim(),
        name: channelAddForm.value.name.trim(),
        price: channelAddForm.value.price === '' ? null : Number(channelAddForm.value.price),
        unit: channelAddForm.value.unit.trim() || null,
        locked: !!channelAddForm.value.locked,
    };

    try {
        const response = await axios.post(route('admin.ads.delivery-channels.store'), payload);
        if (response.data.success) {
            const newChannel = response.data.channel;
            channelData.value.push({
                id: newChannel.id,
                key: newChannel.id,
                icon: newChannel.icon,
                name: newChannel.name,
                price: newChannel.price,
                unit: newChannel.unit ?? '',
                locked: !!newChannel.locked,
            });
            if (newChannel.locked && !isChannelSelected(newChannel.id)) cbChannels.value.push(newChannel.id);
            channelAddForm.value = { icon: '', name: '', price: '', unit: '', locked: false };
            channelAddOpen.value = false;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            channelAddErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to add delivery channel.');
        }
    }
};
const deleteChannel = async (key: string | number) => {
    if (!confirm('Delete this delivery channel?')) return;
    const ch = getChannel(key); if (!ch) return;

    try {
        const response = await axios.delete(route('admin.ads.delivery-channels.destroy', { id: ch.id || ch.key }));
        if (response.data.success) {
            channelData.value = channelData.value.filter(c => String(c.key) !== String(key) && String(c.id) !== String(key));
            cbChannels.value = cbChannels.value.filter(k => String(k) !== String(key));
        }
    } catch (err) {
        console.error(err);
        toast.error('Failed to delete delivery channel.');
    }
};

// ── Exclusivity Upgrade CRUD ──
const startExclEdit = (key: string | number) => {
    const o = getExcl(key); if (!o) return;
    clearErrors(exclEditErrors);
    exclEditKey.value = String(key);
    exclEditForm.value = { icon: o.icon, name: o.name, pct: o.pct };
};
const saveExclEdit = async () => {
    const o = getExcl(exclEditKey.value); if (!o) return;
    clearErrors(exclEditErrors);

    const payload = {
        icon: exclEditForm.value.icon.trim(),
        name: exclEditForm.value.name.trim(),
        pct: Math.round(Number(exclEditForm.value.pct)),
    };

    try {
        const response = await axios.put(route('admin.ads.exclusivity-upgrades.update', { id: o.id || o.key }), payload);
        if (response.data.success) {
            const updated = response.data.upgrade;
            Object.assign(o, {
                icon: updated.icon,
                name: updated.name,
                pct: Number(updated.pct) || 0,
            });
            exclEditKey.value = null;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            exclEditErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to update exclusivity upgrade.');
        }
    }
};
const cancelExclEdit = () => { exclEditKey.value = null; clearErrors(exclEditErrors); };
const toggleAddExcl = () => { exclAddOpen.value = !exclAddOpen.value; clearErrors(exclAddErrors); };
const addExclOption = async () => {
    clearErrors(exclAddErrors);

    const payload = {
        icon: exclAddForm.value.icon.trim(),
        name: exclAddForm.value.name.trim(),
        pct: exclAddForm.value.pct === '' ? null : Math.round(Number(exclAddForm.value.pct)),
    };

    try {
        const response = await axios.post(route('admin.ads.exclusivity-upgrades.store'), payload);
        if (response.data.success) {
            const newUpgrade = response.data.upgrade;
            exclOptions.value.push({
                id: newUpgrade.id,
                key: newUpgrade.id,
                icon: newUpgrade.icon,
                name: newUpgrade.name,
                pct: Number(newUpgrade.pct) || 0
            });
            exclAddForm.value = { icon: '', name: '', pct: '' };
            exclAddOpen.value = false;
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            exclAddErrors.value = err.response.data.errors;
        } else {
            console.error(err);
            toast.error(err.response?.data?.message || 'Failed to add exclusivity upgrade.');
        }
    }
};
const deleteExclOption = async (key: string | number) => {
    if (!confirm('Delete this exclusivity upgrade?')) return;
    const o = getExcl(key); if (!o) return;

    try {
        const response = await axios.delete(route('admin.ads.exclusivity-upgrades.destroy', { id: o.id || o.key }));
        if (response.data.success) {
            if (isExclSelected(key)) cbExcl.value = null;
            exclOptions.value = exclOptions.value.filter(o => String(o.key) !== String(key) && String(o.id) !== String(key));
        }
    } catch (err) {
        console.error(err);
        toast.error('Failed to delete exclusivity upgrade.');
    }
};

// ── Ad Industry CRUD ──
const startIndustryEdit = (id: number) => {
    const i = industryOptions.value.find(opt => opt.id === id); if (!i) return;
    clearErrors(industryEditErrors);
    industryEditId.value = id;
    const multStr = i.label.split('—')[1]?.replace('×', '').trim();
    industryEditForm.value = { name: i.label.split('—')[0].trim(), multiplier: parseFloat(multStr) || 1 };
};
const saveIndustryEdit = async () => {
    if (!industryEditId.value) return;
    clearErrors(industryEditErrors);
    const payload = { name: industryEditForm.value.name.trim(), multiplier: Number(industryEditForm.value.multiplier) };
    try {
        const response = await axios.put(route('admin.ads.industries.update', { id: industryEditId.value }), payload);
        if (response.data.success) {
            const updated = response.data.industry;
            const idx = industryOptions.value.findIndex(opt => opt.id === updated.id);
            if (idx !== -1) industryOptions.value[idx] = { value: updated.slug, id: updated.id, label: `${updated.name} — ${updated.multiplier}×` };
            industryEditId.value = null;
        }
    } catch (err: any) {
        if (err.response?.status === 422) industryEditErrors.value = err.response.data.errors;
        else toast.error('Failed to update industry.');
    }
};
const cancelIndustryEdit = () => { industryEditId.value = null; clearErrors(industryEditErrors); };
const toggleAddIndustry = () => { industryAddOpen.value = !industryAddOpen.value; clearErrors(industryAddErrors); };
const addIndustry = async () => {
    clearErrors(industryAddErrors);
    const payload = { name: industryAddForm.value.name.trim(), multiplier: Number(industryAddForm.value.multiplier) };
    try {
        const response = await axios.post(route('admin.ads.industries.store'), payload);
        if (response.data.success) {
            const newInd = response.data.industry;
            industryOptions.value.push({ value: newInd.slug, id: newInd.id, label: `${newInd.name} — ${newInd.multiplier}×` });
            industryAddForm.value = { name: '', multiplier: '' };
            industryAddOpen.value = false;
        }
    } catch (err: any) {
        if (err.response?.status === 422) industryAddErrors.value = err.response.data.errors;
        else toast.error('Failed to add industry.');
    }
};
const deleteIndustry = async (id: number) => {
    if (!confirm('Delete this industry category?')) return;
    const existing = industryOptions.value.find(opt => opt.id === id);
    try {
        const response = await axios.delete(route('admin.ads.industries.destroy', { id }));
        if (response.data.success) {
            industryOptions.value = industryOptions.value.filter(opt => opt.id !== id);
            if (existing && String(cbIndustry.value) === String(existing.value)) cbIndustry.value = '';
        }
    } catch (err) { toast.error('Failed to delete industry.'); }
};

// ── Ad Surge Option CRUD ──
const startSurgeEdit = (id: number) => {
    const s = surgeOptions.value.find(opt => opt.id === id); if (!s) return;
    clearErrors(surgeEditErrors);
    surgeEditId.value = id;
    surgeEditForm.value = { name: s.name, multiplier: s.mult, description: s.label };
};
const saveSurgeEdit = async () => {
    if (!surgeEditId.value) return;
    clearErrors(surgeEditErrors);
    const payload = { name: surgeEditForm.value.name.trim(), multiplier: Number(surgeEditForm.value.multiplier), description: surgeEditForm.value.description.trim() || null };
    try {
        const response = await axios.put(route('admin.ads.surge-options.update', { id: surgeEditId.value }), payload);
        if (response.data.success) {
            const updated = response.data.option;
            const idx = surgeOptions.value.findIndex(opt => opt.id === updated.id);
            if (idx !== -1) surgeOptions.value[idx] = { id: updated.id, name: updated.name, mult: updated.multiplier, label: updated.description || `${updated.multiplier}×` };
            surgeEditId.value = null;
        }
    } catch (err: any) {
        if (err.response?.status === 422) surgeEditErrors.value = err.response.data.errors;
        else toast.error('Failed to update surge option.');
    }
};
const cancelSurgeEdit = () => { surgeEditId.value = null; clearErrors(surgeEditErrors); };
const toggleAddSurge = () => { surgeAddOpen.value = !surgeAddOpen.value; clearErrors(surgeAddErrors); };
const addSurge = async () => {
    clearErrors(surgeAddErrors);
    const payload = { name: surgeAddForm.value.name.trim(), multiplier: Number(surgeAddForm.value.multiplier), description: surgeAddForm.value.description.trim() || null };
    try {
        const response = await axios.post(route('admin.ads.surge-options.store'), payload);
        if (response.data.success) {
            const newOpt = response.data.option;
            surgeOptions.value.push({ id: newOpt.id, name: newOpt.name, mult: newOpt.multiplier, label: newOpt.description || `${newOpt.multiplier}×` });
            surgeAddForm.value = { name: '', multiplier: '', description: '' };
            surgeAddOpen.value = false;
        }
    } catch (err: any) {
        if (err.response?.status === 422) surgeAddErrors.value = err.response.data.errors;
        else toast.error('Failed to add surge option.');
    }
};
const deleteSurge = async (id: number) => {
    if (!confirm('Delete this surge option?')) return;
    const deletedIdx = surgeOptions.value.findIndex(opt => opt.id === id);
    try {
        const response = await axios.delete(route('admin.ads.surge-options.destroy', { id }));
        if (response.data.success) {
            surgeOptions.value = surgeOptions.value.filter(opt => opt.id !== id);
            if (cbSurgeSelectedIdx.value === deletedIdx) {
                cbSurgeSelectedIdx.value = surgeOptions.value.length ? 0 : null;
                cbSurgeMultiplier.value = cbSurgeSelectedIdx.value === null ? 1.5 : surgeOptions.value[0].mult;
                if (!surgeOptions.value.length) cbSurge.value = false;
            }
        }
    } catch (err) { toast.error('Failed to delete surge option.'); }
};

// ── Draft / Submit ──
const saveDraft = () => { toast.success('Campaign saved as draft. You can return to it from the Campaigns list.'); };

const submitCampaignPreviewOnly = () => {
    if (!cbType.value) { toast.error('Please select a campaign type (Step 1) before launching.'); return; }
    if (cbTier.value === null) { toast.error('Please select a territory tier (Step 2) before launching.'); return; }
    const totalCountries = cbCountries.value.length + cbDiaspora.value.length;
    if (totalCountries === 0) { toast.error('Please select at least one country or diaspora market (Step 2) before launching.'); return; }
    if (!cbIndustry.value) { toast.error('Please select an industry category (Step 4) before launching.'); return; }
    if (!cbStartDate.value || !cbEndDate.value) { toast.error('Please select a start and end date (Step 4) before launching.'); return; }
    if (new Date(cbEndDate.value) < new Date(cbStartDate.value)) { toast.error('End date must be on or after the start date.'); return; }

    const type = getType(cbType.value);
    const tierLabel = 'Tier ' + cbTier.value + ' — ' + (getTier(cbTier.value)?.name || '');
    const total = pricing.value.calculatedTotal;

    const summary = [
        '✅ Campaign Ready to Launch',
        '',
        'Type: ' + (type?.name || cbType.value),
        'Territory: ' + tierLabel,
        'Markets: ' + totalCountries + ' selected (' + cbCountries.value.length + ' countries + ' + cbDiaspora.value.length + ' diaspora hubs)',
        'Channels: ' + cbChannels.value.length,
        'Surge: ' + (cbSurge.value ? cbSurgeMultiplier.value + '× active' : 'No'),
        'Exclusivity: ' + getExclLabel(cbExcl.value),
        '',
        'Campaign Investment: ' + fmtFull(Math.round(total)),
        '',
        'Confirm launch? Your account manager will contact you within 2 business hours to finalize placement.',
    ].join('\n');

    toast.success('Campaign submitted successfully.');
    showList();
};

const toNumericId = (value: unknown): number | null => {
    const numeric = Number(value);
    return Number.isInteger(numeric) && numeric > 0 ? numeric : null;
};

const getMarketName = (key: string) => {
    return [...caribbeanCountries, ...centralAmericaCountries, ...diasporaHubs].find(m => m.key === key)?.name || key;
};

const buildLaunchPayload = () => {
    const type = getType(cbType.value);
    const tier = getTier(cbTier.value);
    const frequency = frequencyOptions.find(f => Number(f.value) === Number(cbFrequency.value));
    const industry = industryOptions.value.find(i => String(i.value) === String(cbIndustry.value));
    const channels = cbChannels.value
        .map(key => getChannel(key))
        .filter(Boolean)
        .map((channel: any) => ({
            id: toNumericId(channel.id ?? channel.key),
            icon: channel.icon,
            name: channel.name,
            price: Number(channel.price) || 0,
            unit: channel.unit || null,
            locked: !!channel.locked,
        }));
    const surgeOption = cbSurge.value && cbSurgeSelectedIdx.value !== null ? surgeOptions.value[cbSurgeSelectedIdx.value] : null;
    const exclusivity = getExcl(cbExcl.value);
    const finalTotal = Number(cbOverrideAmount.value || pricing.value.calculatedTotal);

    return {
        type: {
            id: toNumericId(type?.id ?? type?.key),
            icon: type?.icon || '',
            name: type?.name || String(cbType.value),
            duration: type?.duration || '',
            base_price: Number(type?.base) || 0,
            tax_rate: Number(type?.tax) || 0,
        },
        tier: {
            id: toNumericId(tier?.id ?? tier?.num),
            label: tier?.label || `Tier ${cbTier.value}`,
            name: tier?.name || '',
            multiplier: Number(tier?.mult) || 1,
        },
        countries: cbCountries.value.map(getMarketName),
        diaspora_markets: cbDiaspora.value.map(getMarketName),
        channels,
        industry: {
            id: toNumericId((industry as any)?.id),
            name: industry?.label?.split('â€”')[0]?.trim() || String(cbIndustry.value),
            multiplier: getIndustryMultiplier(cbIndustry.value),
        },
        frequency: {
            value: Number(cbFrequency.value) || 1,
            label: frequency?.label || 'One-Time Burst - 1x (single campaign)',
        },
        schedule: {
            start_date: cbStartDate.value,
            end_date: cbEndDate.value,
        },
        surge: {
            active: cbSurge.value,
            option: surgeOption ? {
                id: toNumericId((surgeOption as any).id),
                name: surgeOption.name,
                multiplier: Number(surgeOption.mult) || cbSurgeMultiplier.value,
                label: surgeOption.label,
            } : null,
        },
        exclusivity: exclusivity ? {
            id: toNumericId(exclusivity.id ?? exclusivity.key),
            icon: exclusivity.icon,
            name: exclusivity.name,
            pct: Number(exclusivity.pct) || 0,
        } : null,
        pricing: {
            base: pricing.value.base,
            tier_multiplier: pricing.value.tierMult,
            industry_multiplier: pricing.value.industryMult,
            surge_multiplier: pricing.value.surgeMult,
            frequency_multiplier: pricing.value.freqMult,
            channel_addons: pricing.value.channelAddons,
            geo_addon: pricing.value.geoAddon,
            exclusivity_premium: pricing.value.exclPremium,
            tax_amount: pricing.value.taxAmount,
            subtotal: pricing.value.subtotal,
            calculated_total: pricing.value.calculatedTotal,
            final_total: finalTotal,
            reach: pricing.value.reach,
            impressions: pricing.value.impressions,
            ctr: pricing.value.ctr,
            media_value: pricing.value.mediaValue,
        },
        override: {
            amount: cbOverrideAmount.value,
            notes: cbOverrideNotes.value.trim() || null,
        },
    };
};

const submitCampaign = async () => {
    if (isLaunching.value) return;
    if (!cbType.value) { toast.error('Please select a campaign type (Step 1) before launching.'); return; }
    if (cbTier.value === null) { toast.error('Please select a territory tier (Step 2) before launching.'); return; }
    const totalCountries = cbCountries.value.length + cbDiaspora.value.length;
    if (totalCountries === 0) { toast.error('Please select at least one country or diaspora market (Step 2) before launching.'); return; }
    if (!cbChannels.value.length) { toast.error('Please select at least one delivery channel (Step 3) before launching.'); return; }
    if (!cbIndustry.value) { toast.error('Please select an industry category (Step 4) before launching.'); return; }
    if (!cbStartDate.value || !cbEndDate.value) { toast.error('Please select a start and end date (Step 4) before launching.'); return; }
    if (new Date(cbEndDate.value) < new Date(cbStartDate.value)) { toast.error('End date must be on or after the start date.'); return; }

    isLaunching.value = true;
    const savingToast = toast.loading(editingCampaignId.value ? 'Updating campaign...' : 'Launching campaign...');
    try {
        const response = editingCampaignId.value
            ? await axios.put(route('admin.ads.campaigns.launch.update', { id: editingCampaignId.value }), buildLaunchPayload())
            : await axios.post(route('admin.ads.campaigns.launch'), buildLaunchPayload());
        if (response.data.success) {
            const campaign = response.data.campaign;
            const row = formatLaunchRow(campaign);
            if (editingCampaignId.value) {
                const idx = activeCampaigns.value.findIndex(item => item.launchId === editingCampaignId.value);
                if (idx !== -1) activeCampaigns.value[idx] = row;
            } else {
                activeCampaigns.value.unshift(row);
            }
            toast.success(editingCampaignId.value
                ? `Campaign updated successfully. Reference: ${campaign.reference}`
                : `Campaign submitted successfully. Reference: ${campaign.reference}`);
            editingCampaignId.value = null;
            editingCampaignReference.value = '';
            showList();
        }
    } catch (err: any) {
        if (err.response?.status === 422) {
            const errors = err.response.data.errors || {};
            const firstError = Object.values(errors).flat()[0];
            toast.error(String(firstError || 'Please check the campaign details and try again.'));
        } else {
            toast.error(err.response?.data?.message || 'Failed to launch campaign. Please try again.');
        }
    } finally {
        toast.dismiss(savingToast);
        isLaunching.value = false;
    }
};
</script>

<template>
    <div class="cb-root">
        <Toaster position="top-right" rich-colors />
        <!-- ══ CAMPAIGNS LIST VIEW ══ -->
        <div v-if="viewMode === 'list'" id="cb-view-list">
            <div class="cb-list-header">
                <div>
                    <div class="cb-list-title">Active Campaigns</div>
                    <div class="text-muted" style="margin-top:4px;">Caribbean + Diaspora Attention Network · Managed Placements</div>
                </div>
                <button class="cb-new-btn" @click="showBuilder">＋ New Campaign</button>
            </div>

            <div class="cb-campaigns-list">
                <div v-for="c in activeCampaigns" :key="c.name" class="cb-campaign-row">
                    <div class="cb-campaign-type-badge">{{ c.icon }}</div>
                    <div>
                        <div class="cb-campaign-name">{{ c.name }}</div>
                        <div class="cb-campaign-meta">{{ c.meta }}</div>
                    </div>
                    <span class="cb-campaign-status" :class="c.status">{{ c.status === 'active' ? 'Active' : 'Ended' }}</span>
                    <div class="cb-campaign-budget">{{ fmt(c.budget) }}</div>
                    <div class="cb-campaign-channels">{{ c.channels }} channels</div>
                    <div class="cb-campaign-reach">{{ c.reach }} reach</div>
                    <button class="cb-view-btn" @click="viewCampaign(c)">View →</button>
                </div>
            </div>

            <div v-if="selectedCampaign" class="cb-campaign-modal" @click.self="closeCampaignView">
                <div class="cb-campaign-modal-card">
                    <div class="cb-campaign-modal-head">
                        <div class="cb-campaign-modal-icon">{{ selectedCampaign.icon }}</div>
                        <div>
                            <div class="cb-campaign-modal-kicker">{{ selectedCampaign.reference || 'Campaign' }}</div>
                            <div class="cb-campaign-modal-title">{{ selectedCampaign.name }}</div>
                            <div class="cb-campaign-modal-meta">{{ selectedCampaign.meta }}</div>
                        </div>
                        <button class="cb-campaign-modal-close" @click="closeCampaignView">×</button>
                    </div>

                    <div class="cb-campaign-modal-stats">
                        <div>
                            <span>Investment</span>
                            <strong>{{ fmt(selectedCampaign.finalTotal || selectedCampaign.budget || 0) }}</strong>
                        </div>
                        <div>
                            <span>Schedule</span>
                            <strong>{{ selectedCampaign.dateRange || 'Not available' }}</strong>
                        </div>
                        <div>
                            <span>Reach</span>
                            <strong>{{ selectedCampaign.reach }} reach</strong>
                        </div>
                        <div>
                            <span>Channels</span>
                            <strong>{{ selectedCampaign.channels }} selected</strong>
                        </div>
                    </div>

                    <div class="cb-campaign-detail-grid">
                        <div class="cb-campaign-detail-block">
                            <h4>Campaign Setup</h4>
                            <div class="cb-campaign-detail-row"><span>Type</span><strong>{{ selectedCampaign.typeName || selectedCampaign.name }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Territory</span><strong>{{ selectedCampaign.tierName || 'Not available' }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Industry</span><strong>{{ selectedCampaign.industryName || 'Not available' }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Frequency</span><strong>{{ selectedCampaign.frequencyLabel || 'Not available' }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Surge</span><strong>{{ selectedCampaign.surgeLabel || 'No surge' }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Exclusivity</span><strong>{{ selectedCampaign.exclusivityLabel || 'No exclusivity' }}</strong></div>
                        </div>

                        <div class="cb-campaign-detail-block">
                            <h4>Pricing Snapshot</h4>
                            <div class="cb-campaign-detail-row"><span>Calculated</span><strong>{{ fmt(selectedCampaign.calculatedTotal || selectedCampaign.budget || 0) }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Final Total</span><strong>{{ fmt(selectedCampaign.finalTotal || selectedCampaign.budget || 0) }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Media Value</span><strong>{{ selectedCampaign.pricingSnapshot?.media_value ? fmt(selectedCampaign.pricingSnapshot.media_value) : 'Not available' }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>Impressions</span><strong>{{ selectedCampaign.pricingSnapshot?.impressions ? simpleNum(selectedCampaign.pricingSnapshot.impressions) : 'Not available' }}</strong></div>
                            <div class="cb-campaign-detail-row"><span>CTR</span><strong>{{ selectedCampaign.pricingSnapshot?.ctr || 'Not available' }}</strong></div>
                        </div>
                    </div>

                    <div class="cb-campaign-detail-block full">
                        <h4>Markets</h4>
                        <div v-if="selectedCampaign.markets?.length" class="cb-campaign-chip-list">
                            <span v-for="market in selectedCampaign.markets" :key="market" class="cb-campaign-chip">{{ market }}</span>
                        </div>
                        <div v-else class="cb-campaign-empty">Market details are not available for this campaign.</div>
                    </div>

                    <div class="cb-campaign-detail-block full">
                        <h4>Delivery Channels</h4>
                        <div v-if="selectedCampaign.channelList?.length" class="cb-campaign-chip-list">
                            <span v-for="channel in selectedCampaign.channelList" :key="channel" class="cb-campaign-chip">{{ channel }}</span>
                        </div>
                        <div v-else class="cb-campaign-empty">Channel details are not available for this campaign.</div>
                    </div>

                    <div v-if="selectedCampaign.notes" class="cb-campaign-detail-block full">
                        <h4>Internal Notes</h4>
                        <p class="cb-campaign-notes">{{ selectedCampaign.notes }}</p>
                    </div>
                </div>
            </div>
        </div><!-- /#cb-view-list -->

        <!-- ══ BUILDER VIEW ══ -->
        <div v-else id="cb-view-builder">
            <div class="cb-header">
                <div class="cb-header-title">{{ editingCampaignId ? 'Edit Campaign' : 'New Campaign' }}</div>
                <button class="cb-back-btn" @click="showList">← All Campaigns</button>
                <button class="cb-draft-btn" @click="saveDraft">💾 Save Draft</button>
                <button class="cb-launch-header-btn" :disabled="isLaunching" @click="submitCampaign">
                    {{ isLaunching ? (editingCampaignId ? 'Updating...' : 'Launching...') : (editingCampaignId ? 'Update Campaign' : '🚀 Launch Campaign') }}
                </button>
            </div>

            <div class="cb-cols">
                <!-- ══ LEFT: CONFIG ══ -->
                <div class="cb-config">

                    <!-- STEP 1 — Campaign Type -->
                    <div class="cb-section" :class="{ 'cb-collapsed': collapsed.type }">
                        <div class="cb-section-head">
                            <div class="cb-step-num">1</div>
                            <h3>Campaign Type</h3>
                            <span class="cb-step-badge">Required</span>
                            <button class="cb-manage-btn" @click="toggleManage('type')">⚙️ Manage Types</button>
                            <button class="cb-step-edit-btn" @click="expandSection('type')">✏️ Change Type</button>
                        </div>
                        <div class="cb-section-summary">
                            <span class="cb-summary-chip">Campaign: {{ typeSummaryLabel }}</span>
                        </div>

                        <!-- Management panel -->
                        <div class="cb-manage-panel" :class="{ open: manageOpen === 'type' }">
                            <div class="cb-manage-panel-head">
                                <span class="cb-manage-panel-title">⚙️ Manage Campaign Types</span>
                                <button class="cb-manage-close" @click="toggleManage('type')">✕</button>
                            </div>
                            <div class="cb-manage-list">
                                <div v-for="t in typeData" :key="t.key" class="cb-manage-row">
                                    <div class="cb-manage-row-icon">{{ t.icon }}</div>
                                    <div class="cb-manage-row-info">
                                        <div class="cb-manage-row-name">{{ t.name }}</div>
                                        <div class="cb-manage-row-meta">
                                            {{ t.duration }} · {{ t.pmin > 0 ? `$${t.pmin.toLocaleString()}–$${t.pmax.toLocaleString()} · Base $${t.base.toLocaleString()}` : `Base $${t.base.toLocaleString()}` }}<template v-if="t.tax > 0"> · Tax {{ t.tax }}%</template>
                                        </div>
                                    </div>
                                    <div class="cb-manage-row-actions">
                                        <button class="cb-manage-action-btn" @click="startTypeEdit(t.key)">✏️ Edit</button>
                                        <button class="cb-manage-action-btn delete" @click="deleteType(t.key)">🗑</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Inline edit form -->
                            <div class="cb-manage-form" :class="{ open: typeEditKey }">
                                <div class="cb-manage-form-title">Edit Campaign Type</div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Icon (emoji)</label>
                                        <input class="cb-manage-input" v-model="typeEditForm.icon" maxlength="4" placeholder="⚡" />
                                        <div v-if="getError(typeEditErrors, 'icon')" class="cb-error">{{ getError(typeEditErrors, 'icon') }}</div>
                                    </div>
                                    <div class="cb-manage-field" style="grid-column:span 2">
                                        <label>Campaign Name</label>
                                        <input class="cb-manage-input" v-model="typeEditForm.name" placeholder="e.g. Flash Campaign" />
                                        <div v-if="getError(typeEditErrors, 'name')" class="cb-error">{{ getError(typeEditErrors, 'name') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Duration</label>
                                        <input class="cb-manage-input" v-model="typeEditForm.duration" placeholder="24 hours" />
                                        <div v-if="getError(typeEditErrors, 'duration')" class="cb-error">{{ getError(typeEditErrors, 'duration') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Price Min ($)</label>
                                        <input class="cb-manage-input" type="number" v-model="typeEditForm.pmin" placeholder="250" />
                                        <div v-if="getError(typeEditErrors, 'priceMin')" class="cb-error">{{ getError(typeEditErrors, 'priceMin') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Price Max ($)</label>
                                        <input class="cb-manage-input" type="number" v-model="typeEditForm.pmax" placeholder="1500" />
                                        <div v-if="getError(typeEditErrors, 'priceMax')" class="cb-error">{{ getError(typeEditErrors, 'priceMax') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Base Price ($)</label>
                                        <input class="cb-manage-input" type="number" v-model="typeEditForm.base" placeholder="750" />
                                        <div v-if="getError(typeEditErrors, 'basePrice')" class="cb-error">{{ getError(typeEditErrors, 'basePrice') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Est. Reach</label>
                                        <input class="cb-manage-input" type="number" v-model="typeEditForm.reach" placeholder="15000" />
                                        <div v-if="getError(typeEditErrors, 'reach')" class="cb-error">{{ getError(typeEditErrors, 'reach') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Tax Rate (%)</label>
                                        <input class="cb-manage-input" type="number" min="0" max="100" step="0.5" v-model="typeEditForm.tax" placeholder="0" />
                                        <div v-if="getError(typeEditErrors, 'taxRate')" class="cb-error">{{ getError(typeEditErrors, 'taxRate') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-grid cols-1">
                                    <div class="cb-manage-field">
                                        <label>Description</label>
                                        <input class="cb-manage-input" v-model="typeEditForm.desc" placeholder="1 push · boosted feed · analytics" />
                                        <div v-if="getError(typeEditErrors, 'description')" class="cb-error">{{ getError(typeEditErrors, 'description') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-actions">
                                    <button class="cb-manage-save-btn" @click="saveTypeEdit">✓ Save Changes</button>
                                    <button class="cb-manage-cancel-btn" @click="cancelTypeEdit">Cancel</button>
                                </div>
                            </div>
                            <!-- Add new type -->
                            <div class="cb-manage-add-row">
                                <button class="cb-manage-add-toggle" @click="toggleAddType">＋ Add New Campaign Type</button>
                                <div class="cb-manage-form" v-if="typeAddOpen" style="display:block;background:none;border:none;padding:12px 0 0;">
                                    <div class="cb-manage-form-title">New Campaign Type</div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Icon</label>
                                            <input class="cb-manage-input" v-model="typeAddForm.icon" maxlength="4" placeholder="🆕" />
                                            <div v-if="getError(typeAddErrors, 'icon')" class="cb-error">{{ getError(typeAddErrors, 'icon') }}</div>
                                        </div>
                                        <div class="cb-manage-field" style="grid-column:span 2">
                                            <label>Name</label>
                                            <input class="cb-manage-input" v-model="typeAddForm.name" placeholder="New Campaign Type" />
                                            <div v-if="getError(typeAddErrors, 'name')" class="cb-error">{{ getError(typeAddErrors, 'name') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Duration</label>
                                            <input class="cb-manage-input" v-model="typeAddForm.duration" placeholder="24 hours" />
                                            <div v-if="getError(typeAddErrors, 'duration')" class="cb-error">{{ getError(typeAddErrors, 'duration') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Price Min ($)</label>
                                            <input class="cb-manage-input" type="number" v-model="typeAddForm.pmin" placeholder="500" />
                                            <div v-if="getError(typeAddErrors, 'priceMin')" class="cb-error">{{ getError(typeAddErrors, 'priceMin') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Price Max ($)</label>
                                            <input class="cb-manage-input" type="number" v-model="typeAddForm.pmax" placeholder="5000" />
                                            <div v-if="getError(typeAddErrors, 'priceMax')" class="cb-error">{{ getError(typeAddErrors, 'priceMax') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Base Price ($)</label>
                                            <input class="cb-manage-input" type="number" v-model="typeAddForm.base" placeholder="1000" />
                                            <div v-if="getError(typeAddErrors, 'basePrice')" class="cb-error">{{ getError(typeAddErrors, 'basePrice') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Est. Reach</label>
                                            <input class="cb-manage-input" type="number" v-model="typeAddForm.reach" placeholder="20000" />
                                            <div v-if="getError(typeAddErrors, 'reach')" class="cb-error">{{ getError(typeAddErrors, 'reach') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Tax Rate (%)</label>
                                            <input class="cb-manage-input" type="number" min="0" max="100" step="0.5" v-model="typeAddForm.tax" placeholder="0" />
                                            <div v-if="getError(typeAddErrors, 'taxRate')" class="cb-error">{{ getError(typeAddErrors, 'taxRate') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-grid cols-1">
                                        <div class="cb-manage-field">
                                            <label>Description</label>
                                            <input class="cb-manage-input" v-model="typeAddForm.desc" placeholder="Short description of what's included" />
                                            <div v-if="getError(typeAddErrors, 'description')" class="cb-error">{{ getError(typeAddErrors, 'description') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-actions">
                                        <button class="cb-manage-save-btn" @click="addType">＋ Add Campaign Type</button>
                                        <button class="cb-manage-cancel-btn" @click="toggleAddType">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /manage-panel-type -->

                        <div class="cb-section-body">
                            <div class="cb-type-grid">
                                <div v-for="t in typeData" :key="t.key"
                                    class="cb-type-card" :class="{ 'cb-type-card-enterprise': t.enterprise, selected: isTypeSelected(t.key) }"
                                    @click="selectType(t.key)">
                                    <div class="cb-type-check">✓</div>
                                    <template v-if="t.enterprise">
                                        <span class="cb-type-icon">{{ t.icon }}</span>
                                        <div class="cb-type-info">
                                            <div style="margin-bottom:4px;"><span class="cb-enterprise-badge">Enterprise</span></div>
                                            <div class="cb-type-name">{{ t.name }}</div>
                                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:6px;">
                                                <span class="cb-type-duration">{{ t.duration }}</span>
                                                <span class="cb-type-duration" style="background:linear-gradient(90deg,#fffbea,#fff8d6);color:#92680a;border-color:#B8860B44;">White-glove managed</span>
                                            </div>
                                            <div class="cb-type-price">{{ typePriceLabel(t) }}<span v-if="t.tax > 0" style="color:#f59e0b;font-size:10px;"> +{{ t.tax }}% tax</span></div>
                                            <div class="cb-type-desc">{{ t.desc }}</div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="cb-type-icon">{{ t.icon }}</span>
                                        <div class="cb-type-name">{{ t.name }}</div>
                                        <div><span class="cb-type-duration">{{ t.duration }}</span></div>
                                        <div class="cb-type-price">{{ typePriceLabel(t) }}<span v-if="t.tax > 0" style="color:#f59e0b;font-size:10px;"> +{{ t.tax }}% tax</span></div>
                                        <div class="cb-type-desc">{{ t.desc }}</div>
                                    </template>
                                </div>
                            </div>
                            <div class="cb-section-done-row">
                                <button class="cb-section-done-btn" :class="{ ready: typeDoneReady }" @click="confirmType">✓ Confirm Campaign Type</button>
                            </div>
                        </div>
                    </div><!-- /step 1 -->

                    <!-- STEP 2 — Territory -->
                    <div class="cb-section" :class="{ 'cb-collapsed': collapsed.territory }">
                        <div class="cb-section-head">
                            <div class="cb-step-num">2</div>
                            <h3>Territory</h3>
                            <span v-if="cbTier !== null" class="cb-tier-badge">Tier {{ cbTier }} — {{ getTier(cbTier)?.name }}</span>
                            <span class="cb-step-badge">Required</span>
                            <button class="cb-manage-btn" @click="toggleManage('territory')">⚙️ Manage Tiers</button>
                            <button class="cb-step-edit-btn" @click="expandSection('territory')">✏️ Edit Territory</button>
                        </div>
                        <div class="cb-section-summary">
                            <span v-for="(chip, i) in territorySummaryChips" :key="i" class="cb-summary-chip territory">{{ chip }}</span>
                        </div>

                        <!-- Management panel -->
                        <div class="cb-manage-panel" :class="{ open: manageOpen === 'territory' }">
                            <div class="cb-manage-panel-head">
                                <span class="cb-manage-panel-title">⚙️ Manage Territory Tiers</span>
                                <button class="cb-manage-close" @click="toggleManage('territory')">✕</button>
                            </div>
                            <div class="cb-manage-list">
                                <div v-for="t in tierData" :key="t.num" class="cb-manage-row">
                                    <div class="cb-manage-row-icon" style="font-size:13px;font-weight:800;color:var(--brand);">{{ t.label }}</div>
                                    <div class="cb-manage-row-info">
                                        <div class="cb-manage-row-name">{{ t.name }}</div>
                                        <div class="cb-manage-row-meta">${{ t.pmin.toLocaleString() }}–${{ t.pmax.toLocaleString() }}+ · {{ t.mult }}× multiplier</div>
                                    </div>
                                    <div class="cb-manage-row-actions">
                                        <button class="cb-manage-action-btn" @click="startTierEdit(t.num)">✏️ Edit</button>
                                        <button class="cb-manage-action-btn delete" @click="deleteTier(t.num)">🗑</button>
                                    </div>
                                </div>
                            </div>
                            <div class="cb-manage-form" :class="{ open: tierEditNum }">
                                <div class="cb-manage-form-title">Edit Territory Tier</div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Tier Label</label>
                                        <input class="cb-manage-input" v-model="tierEditForm.label" placeholder="Tier 1" />
                                        <div v-if="getError(tierEditErrors, 'label')" class="cb-error">{{ getError(tierEditErrors, 'label') }}</div>
                                    </div>
                                    <div class="cb-manage-field" style="grid-column:span 2">
                                        <label>Territory Name</label>
                                        <input class="cb-manage-input" v-model="tierEditForm.name" placeholder="Local Territory" />
                                        <div v-if="getError(tierEditErrors, 'name')" class="cb-error">{{ getError(tierEditErrors, 'name') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Price Min ($)</label>
                                        <input class="cb-manage-input" type="number" v-model="tierEditForm.pmin" placeholder="250" />
                                        <div v-if="getError(tierEditErrors, 'priceMin')" class="cb-error">{{ getError(tierEditErrors, 'priceMin') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Price Max ($)</label>
                                        <input class="cb-manage-input" type="number" v-model="tierEditForm.pmax" placeholder="750" />
                                        <div v-if="getError(tierEditErrors, 'priceMax')" class="cb-error">{{ getError(tierEditErrors, 'priceMax') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Multiplier (×)</label>
                                        <input class="cb-manage-input" type="number" step="0.1" v-model="tierEditForm.mult" placeholder="1.0" />
                                        <div v-if="getError(tierEditErrors, 'multiplier')" class="cb-error">{{ getError(tierEditErrors, 'multiplier') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-actions">
                                    <button class="cb-manage-save-btn" @click="saveTierEdit">✓ Save Changes</button>
                                    <button class="cb-manage-cancel-btn" @click="cancelTierEdit">Cancel</button>
                                </div>
                            </div>
                            <div class="cb-manage-add-row">
                                <button class="cb-manage-add-toggle" @click="toggleAddTier">＋ Add New Territory Tier</button>
                                <div class="cb-manage-form" v-if="tierAddOpen" style="display:block;background:none;border:none;padding:12px 0 0;">
                                    <div class="cb-manage-form-title">New Territory Tier</div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Tier Label</label>
                                            <input class="cb-manage-input" v-model="tierAddForm.label" placeholder="Tier 7" />
                                            <div v-if="getError(tierAddErrors, 'label')" class="cb-error">{{ getError(tierAddErrors, 'label') }}</div>
                                        </div>
                                        <div class="cb-manage-field" style="grid-column:span 2">
                                            <label>Territory Name</label>
                                            <input class="cb-manage-input" v-model="tierAddForm.name" placeholder="e.g. South America" />
                                            <div v-if="getError(tierAddErrors, 'name')" class="cb-error">{{ getError(tierAddErrors, 'name') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Price Min ($)</label>
                                            <input class="cb-manage-input" type="number" v-model="tierAddForm.pmin" placeholder="5000" />
                                            <div v-if="getError(tierAddErrors, 'priceMin')" class="cb-error">{{ getError(tierAddErrors, 'priceMin') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Price Max ($)</label>
                                            <input class="cb-manage-input" type="number" v-model="tierAddForm.pmax" placeholder="50000" />
                                            <div v-if="getError(tierAddErrors, 'priceMax')" class="cb-error">{{ getError(tierAddErrors, 'priceMax') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Multiplier (×)</label>
                                            <input class="cb-manage-input" type="number" step="0.1" v-model="tierAddForm.mult" placeholder="5.0" />
                                            <div v-if="getError(tierAddErrors, 'multiplier')" class="cb-error">{{ getError(tierAddErrors, 'multiplier') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-actions">
                                        <button class="cb-manage-save-btn" @click="addTier">＋ Add Tier</button>
                                        <button class="cb-manage-cancel-btn" @click="toggleAddTier">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /manage-panel-territory -->

                        <div class="cb-section-body">
                            <div class="cb-tier-grid">
                                <div v-for="t in tierData" :key="t.num" class="cb-tier-opt" :class="{ selected: isTierSelected(t.num) }" @click="selectTier(t.num)">
                                    <div class="cb-tier-num">{{ t.label }}</div>
                                    <div class="cb-tier-name">{{ t.name }}</div>
                                    <div class="cb-tier-range">{{ tierRangeLabel(t) }}</div>
                                </div>
                            </div>

                            <!-- Country selector -->
                            <div v-if="cbTier !== null" id="cb-country-selector">
                                <span class="cb-country-section-label">Caribbean</span>
                                <div class="cb-country-grid">
                                    <div v-for="c in caribbeanCountries" :key="c.key" class="cb-country-item" :class="{ selected: cbCountries.includes(c.key) }" @click="toggleCountry(c.key)">
                                        <input type="checkbox" :checked="cbCountries.includes(c.key)" /><span class="cb-country-flag">{{ c.flag }}</span> {{ c.name }}
                                    </div>
                                </div>

                                <span class="cb-country-section-label">Central America</span>
                                <div class="cb-country-grid">
                                    <div v-for="c in centralAmericaCountries" :key="c.key" class="cb-country-item" :class="{ selected: cbCountries.includes(c.key) }" @click="toggleCountry(c.key)">
                                        <input type="checkbox" :checked="cbCountries.includes(c.key)" /><span class="cb-country-flag">{{ c.flag }}</span> {{ c.name }}
                                    </div>
                                </div>

                                <span class="cb-country-section-label">Diaspora Hubs</span>
                                <div class="cb-diaspora-grid">
                                    <div v-for="d in diasporaHubs" :key="d.key" class="cb-diaspora-item" :class="{ selected: cbDiaspora.includes(d.key) }" @click="toggleDiaspora(d.key)">
                                        <input type="checkbox" :checked="cbDiaspora.includes(d.key)" />{{ d.flag }} {{ d.name }}
                                    </div>
                                </div>
                            </div><!-- /#cb-country-selector -->
                            <div class="cb-section-done-row">
                                <button class="cb-section-done-btn" :class="{ ready: territoryReady }" @click="confirmTerritory">✓ Confirm Territory</button>
                            </div>
                        </div>
                    </div><!-- /step 2 -->

                    <!-- STEP 3 — Delivery Channels -->
                    <div class="cb-section" :class="{ 'cb-collapsed': collapsed.channels }">
                        <div class="cb-section-head">
                            <div class="cb-step-num">3</div>
                            <h3>Delivery Channels</h3>
                            <span class="cb-step-badge">Add-ons</span>
                            <button class="cb-manage-btn" @click="toggleManage('channels')">⚙️ Manage Channels</button>
                            <button class="cb-step-edit-btn" @click="expandSection('channels')">✏️ Edit Channels</button>
                        </div>
                        <div class="cb-section-summary">
                            <span v-for="(chip, i) in channelsSummaryChips" :key="i" class="cb-summary-chip channel">{{ chip }}</span>
                        </div>

                        <!-- Management panel -->
                        <div class="cb-manage-panel" :class="{ open: manageOpen === 'channels' }">
                            <div class="cb-manage-panel-head">
                                <span class="cb-manage-panel-title">⚙️ Manage Delivery Channels</span>
                                <button class="cb-manage-close" @click="toggleManage('channels')">✕</button>
                            </div>
                            <div class="cb-manage-list">
                                <div v-for="ch in channelData" :key="ch.key" class="cb-manage-row">
                                    <div class="cb-manage-row-icon">{{ ch.icon }}</div>
                                    <div class="cb-manage-row-info">
                                        <div class="cb-manage-row-name">{{ ch.name }} <span v-if="ch.locked" style="font-size:10px;color:#CCFF00;">✓ Included</span></div>
                                        <div class="cb-manage-row-meta">{{ ch.locked ? 'Always included' : `$${ch.price.toLocaleString()} / ${ch.unit}` }}</div>
                                    </div>
                                    <div class="cb-manage-row-actions">
                                        <button class="cb-manage-action-btn" @click="startChannelEdit(ch.key)">✏️ Edit</button>
                                        <button v-if="!ch.locked" class="cb-manage-action-btn delete" @click="deleteChannel(ch.key)">🗑</button>
                                    </div>
                                </div>
                            </div>
                            <div class="cb-manage-form" :class="{ open: channelEditKey }">
                                <div class="cb-manage-form-title">Edit Channel</div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Icon (emoji)</label>
                                        <input class="cb-manage-input" v-model="channelEditForm.icon" maxlength="4" placeholder="📱" />
                                        <div v-if="getError(channelEditErrors, 'icon')" class="cb-error">{{ getError(channelEditErrors, 'icon') }}</div>
                                    </div>
                                    <div class="cb-manage-field" style="grid-column:span 2">
                                        <label>Channel Name</label>
                                        <input class="cb-manage-input" v-model="channelEditForm.name" placeholder="In-App Feed" />
                                        <div v-if="getError(channelEditErrors, 'name')" class="cb-error">{{ getError(channelEditErrors, 'name') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-grid cols-3">
                                    <div class="cb-manage-field">
                                        <label>Add-on Price ($)</label>
                                        <input class="cb-manage-input" type="number" v-model="channelEditForm.price" placeholder="500" />
                                        <div v-if="getError(channelEditErrors, 'price')" class="cb-error">{{ getError(channelEditErrors, 'price') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Unit</label>
                                        <input class="cb-manage-input" v-model="channelEditForm.unit" placeholder="month" />
                                        <div v-if="getError(channelEditErrors, 'unit')" class="cb-error">{{ getError(channelEditErrors, 'unit') }}</div>
                                    </div>
                                    <div class="cb-manage-field">
                                        <label>Locked/Included</label>
                                        <select class="cb-manage-input" v-model="channelEditForm.locked">
                                            <option :value="false">Optional add-on</option>
                                            <option :value="true">Locked — always included</option>
                                        </select>
                                        <div v-if="getError(channelEditErrors, 'locked')" class="cb-error">{{ getError(channelEditErrors, 'locked') }}</div>
                                    </div>
                                </div>
                                <div class="cb-manage-form-actions">
                                    <button class="cb-manage-save-btn" @click="saveChannelEdit">✓ Save Changes</button>
                                    <button class="cb-manage-cancel-btn" @click="cancelChannelEdit">Cancel</button>
                                </div>
                            </div>
                            <div class="cb-manage-add-row">
                                <button class="cb-manage-add-toggle" @click="toggleAddChannel">＋ Add New Channel</button>
                                <div class="cb-manage-form" v-if="channelAddOpen" style="display:block;background:none;border:none;padding:12px 0 0;">
                                    <div class="cb-manage-form-title">New Delivery Channel</div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Icon</label>
                                            <input class="cb-manage-input" v-model="channelAddForm.icon" maxlength="4" placeholder="🆕" />
                                            <div v-if="getError(channelAddErrors, 'icon')" class="cb-error">{{ getError(channelAddErrors, 'icon') }}</div>
                                        </div>
                                        <div class="cb-manage-field" style="grid-column:span 2">
                                            <label>Channel Name</label>
                                            <input class="cb-manage-input" v-model="channelAddForm.name" placeholder="New Channel" />
                                            <div v-if="getError(channelAddErrors, 'name')" class="cb-error">{{ getError(channelAddErrors, 'name') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Add-on Price ($)</label>
                                            <input class="cb-manage-input" type="number" v-model="channelAddForm.price" placeholder="500" />
                                            <div v-if="getError(channelAddErrors, 'price')" class="cb-error">{{ getError(channelAddErrors, 'price') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Unit</label>
                                            <input class="cb-manage-input" v-model="channelAddForm.unit" placeholder="campaign" />
                                            <div v-if="getError(channelAddErrors, 'unit')" class="cb-error">{{ getError(channelAddErrors, 'unit') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Type</label>
                                            <select class="cb-manage-input" v-model="channelAddForm.locked">
                                                <option :value="false">Optional add-on</option>
                                                <option :value="true">Always included</option>
                                            </select>
                                            <div v-if="getError(channelAddErrors, 'locked')" class="cb-error">{{ getError(channelAddErrors, 'locked') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-actions">
                                        <button class="cb-manage-save-btn" @click="addChannel">＋ Add Channel</button>
                                        <button class="cb-manage-cancel-btn" @click="toggleAddChannel">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /manage-panel-channels -->

                        <div class="cb-section-body">
                            <div class="cb-channels-note">🔔 Push notifications are included in every campaign at no extra cost</div>
                            <div class="cb-channel-grid">
                                <div v-for="ch in channelData" :key="ch.key"
                                    class="cb-channel-item" :class="{ locked: ch.locked, selected: ch.locked || isChannelSelected(ch.key) }"
                                    @click="!ch.locked && toggleChannel(ch.key)">
                                    <span class="cb-channel-icon">{{ ch.icon }}</span>
                                    <div class="cb-channel-info">
                                        <div class="cb-channel-name">{{ ch.name }}</div>
                                        <div class="cb-channel-price">{{ channelPriceLabel(ch) }}</div>
                                    </div>
                                    <div class="cb-channel-check">✓</div>
                                </div>
                            </div>
                            <div class="cb-section-done-row">
                                <button class="cb-section-done-btn ready" @click="collapseSection('channels')">✓ Done — Save Channel Selection</button>
                            </div>
                        </div>
                    </div><!-- /step 3 -->

                    <!-- STEP 4 — Industry & Schedule -->
                    <div class="cb-section">
                        <div class="cb-section-head">
                            <div class="cb-step-num">4</div>
                            <h3>Industry &amp; Schedule</h3>
                            <span class="cb-step-badge">Required</span>
                            <button class="cb-manage-btn" @click="toggleManage('industry')">⚙️ Manage Industries</button>
                        </div>
                        <div class="cb-section-body">
                            <!-- Industry Manage Panel -->
                            <div class="cb-manage-panel" :class="{ open: manageOpen === 'industry' }">
                                <div class="cb-manage-panel-head">
                                    <span class="cb-manage-panel-title">⚙️ Manage Industries</span>
                                    <button class="cb-manage-close" @click="toggleManage('industry')">✕</button>
                                </div>
                                <div class="cb-manage-list">
                                    <div v-for="i in industryOptions.filter(o => o.value)" :key="i.value" class="cb-manage-row">
                                        <div class="cb-manage-row-info">
                                            <div class="cb-manage-row-name">{{ i.label.split('—')[0].trim() }}</div>
                                            <div class="cb-manage-row-meta">{{ i.label.split('—')[1]?.trim() }} multiplier</div>
                                        </div>
                                        <div class="cb-manage-row-actions">
                                            <button v-if="i.id" class="cb-manage-action-btn" @click="startIndustryEdit(i.id)">✏️ Edit</button>
                                            <button v-if="i.id" class="cb-manage-action-btn delete" @click="deleteIndustry(i.id)">🗑</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Edit Industry Form -->
                                <div class="cb-manage-form" :class="{ open: industryEditId }">
                                    <div class="cb-manage-form-title">Edit Industry</div>
                                    <div class="cb-manage-form-grid">
                                        <div class="cb-manage-field">
                                            <label>Industry Name</label>
                                            <input class="cb-manage-input" v-model="industryEditForm.name" />
                                            <div v-if="getError(industryEditErrors, 'name')" class="cb-error">{{ getError(industryEditErrors, 'name') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Multiplier (×)</label>
                                            <input class="cb-manage-input" type="number" step="0.1" v-model="industryEditForm.multiplier" />
                                            <div v-if="getError(industryEditErrors, 'multiplier')" class="cb-error">{{ getError(industryEditErrors, 'multiplier') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-actions">
                                        <button class="cb-manage-save-btn" @click="saveIndustryEdit">✓ Save</button>
                                        <button class="cb-manage-cancel-btn" @click="cancelIndustryEdit">Cancel</button>
                                    </div>
                                </div>
                                <!-- Add Industry Form -->
                                <div class="cb-manage-add-row">
                                    <button class="cb-manage-add-toggle" @click="toggleAddIndustry">＋ Add New Industry</button>
                                    <div class="cb-manage-form" v-if="industryAddOpen" style="display:block;background:none;border:none;padding:12px 0 0;">
                                        <div class="cb-manage-form-grid">
                                            <div class="cb-manage-field">
                                                <label>Name</label>
                                                <input class="cb-manage-input" v-model="industryAddForm.name" placeholder="e.g. Technology" />
                                                <div v-if="getError(industryAddErrors, 'name')" class="cb-error">{{ getError(industryAddErrors, 'name') }}</div>
                                            </div>
                                            <div class="cb-manage-field">
                                                <label>Multiplier (×)</label>
                                                <input class="cb-manage-input" type="number" step="0.1" v-model="industryAddForm.multiplier" placeholder="1.0" />
                                                <div v-if="getError(industryAddErrors, 'multiplier')" class="cb-error">{{ getError(industryAddErrors, 'multiplier') }}</div>
                                            </div>
                                        </div>
                                        <div class="cb-manage-form-actions">
                                            <button class="cb-manage-save-btn" @click="addIndustry">＋ Add</button>
                                            <button class="cb-manage-cancel-btn" @click="toggleAddIndustry">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cb-field">
                                <label>Campaign Frequency</label>
                                <select v-model="cbFrequency">
                                    <option v-for="f in frequencyOptions" :key="f.value" :value="f.value">{{ f.label }}</option>
                                </select>
                            </div>
                            <div class="cb-field">
                                <label>Industry Category</label>
                                <select v-model="cbIndustry">
                                    <option v-for="i in industryOptions" :key="i.value" :value="i.value">{{ i.label }}</option>
                                </select>
                            </div>
                            <div class="cb-form-row">
                                <div class="cb-field">
                                    <label>Start Date</label>
                                    <input type="date" v-model="cbStartDate" />
                                </div>
                                <div class="cb-field">
                                    <label>End Date</label>
                                    <input type="date" v-model="cbEndDate" />
                                </div>
                            </div>

                            <div class="cb-surge-box" :class="{ active: cbSurge }" @click="toggleSurge">
                                <span class="cb-surge-icon">⚡</span>
                                <div class="cb-surge-info">
                                    <div class="cb-surge-label">Event Surge Pricing Active</div>
                                    <div class="cb-surge-desc">Concert · Carnival · Holiday · Tourism Season · Election</div>
                                </div>
                                <span v-if="cbSurge" class="cb-surge-mult-badge">{{ cbSurgeMultiplier }}×</span>
                                <div class="cb-surge-toggle"></div>
                                <button v-if="cbSurge" class="cb-manage-btn" style="margin-right:40px;" @click.stop="toggleManage('surge')">⚙️ Manage</button>
                            </div>

                            <!-- Surge Manage Panel -->
                            <div class="cb-manage-panel" :class="{ open: manageOpen === 'surge' }" style="margin-top:10px;">
                                <div class="cb-manage-panel-head">
                                    <span class="cb-manage-panel-title">⚙️ Manage Surge Options</span>
                                    <button class="cb-manage-close" @click="toggleManage('surge')">✕</button>
                                </div>
                                <div class="cb-manage-list">
                                    <div v-for="s in surgeOptions" :key="s.name" class="cb-manage-row">
                                        <div class="cb-manage-row-info">
                                            <div class="cb-manage-row-name">{{ s.name }}</div>
                                            <div class="cb-manage-row-meta">{{ s.mult }}× multiplier</div>
                                        </div>
                                        <div class="cb-manage-row-actions">
                                            <button v-if="s.id" class="cb-manage-action-btn" @click="startSurgeEdit(s.id)">✏️ Edit</button>
                                            <button v-if="s.id" class="cb-manage-action-btn delete" @click="deleteSurge(s.id)">🗑</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Edit Surge Form -->
                                <div class="cb-manage-form" :class="{ open: surgeEditId }">
                                    <div class="cb-manage-form-title">Edit Surge Option</div>
                                    <div class="cb-manage-form-grid cols-3">
                                        <div class="cb-manage-field">
                                            <label>Name</label>
                                            <input class="cb-manage-input" v-model="surgeEditForm.name" />
                                            <div v-if="getError(surgeEditErrors, 'name')" class="cb-error">{{ getError(surgeEditErrors, 'name') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Multiplier (×)</label>
                                            <input class="cb-manage-input" type="number" step="0.1" v-model="surgeEditForm.multiplier" />
                                            <div v-if="getError(surgeEditErrors, 'multiplier')" class="cb-error">{{ getError(surgeEditErrors, 'multiplier') }}</div>
                                        </div>
                                        <div class="cb-manage-field">
                                            <label>Label</label>
                                            <input class="cb-manage-input" v-model="surgeEditForm.description" />
                                            <div v-if="getError(surgeEditErrors, 'description')" class="cb-error">{{ getError(surgeEditErrors, 'description') }}</div>
                                        </div>
                                    </div>
                                    <div class="cb-manage-form-actions">
                                        <button class="cb-manage-save-btn" @click="saveSurgeEdit">✓ Save</button>
                                        <button class="cb-manage-cancel-btn" @click="cancelSurgeEdit">Cancel</button>
                                    </div>
                                </div>
                                <!-- Add Surge Form -->
                                <div class="cb-manage-add-row">
                                    <button class="cb-manage-add-toggle" @click="toggleAddSurge">＋ Add New Surge Option</button>
                                    <div class="cb-manage-form" v-if="surgeAddOpen" style="display:block;background:none;border:none;padding:12px 0 0;">
                                        <div class="cb-manage-form-grid cols-3">
                                            <div class="cb-manage-field">
                                                <label>Name</label>
                                                <input class="cb-manage-input" v-model="surgeAddForm.name" placeholder="e.g. Holiday" />
                                                <div v-if="getError(surgeAddErrors, 'name')" class="cb-error">{{ getError(surgeAddErrors, 'name') }}</div>
                                            </div>
                                            <div class="cb-manage-field">
                                                <label>Multiplier (×)</label>
                                                <input class="cb-manage-input" type="number" step="0.1" v-model="surgeAddForm.multiplier" placeholder="2.0" />
                                                <div v-if="getError(surgeAddErrors, 'multiplier')" class="cb-error">{{ getError(surgeAddErrors, 'multiplier') }}</div>
                                            </div>
                                            <div class="cb-manage-field">
                                                <label>Label</label>
                                                <input class="cb-manage-input" v-model="surgeAddForm.description" placeholder="e.g. 2×" />
                                                <div v-if="getError(surgeAddErrors, 'description')" class="cb-error">{{ getError(surgeAddErrors, 'description') }}</div>
                                            </div>
                                        </div>
                                        <div class="cb-manage-form-actions">
                                            <button class="cb-manage-save-btn" @click="addSurge">＋ Add</button>
                                            <button class="cb-manage-cancel-btn" @click="toggleAddSurge">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="cbSurge" id="cb-surge-selector">
                                <div v-for="(opt, idx) in surgeOptions" :key="opt.name" class="cb-surge-opt" :class="{ selected: cbSurgeSelectedIdx === idx }" @click.stop="selectSurgeType(idx)">
                                    <div class="cb-surge-opt-name">{{ opt.name }}</div>
                                    <div class="cb-surge-opt-mult">{{ opt.label }}</div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /step 4 -->

                    <!-- STEP 5 — Exclusivity -->
                    <div class="cb-section">
                        <div class="cb-section-head">
                            <div class="cb-step-num">5</div>
                            <h3>Exclusivity Upgrade</h3>
                            <span class="cb-step-badge optional">Optional</span>
                            <button class="cb-manage-btn" @click="toggleManage('excl')">⚙️ Manage Upgrades</button>
                        </div>
                        <div class="cb-section-body">
                            <div style="margin-bottom:12px;">
                                <div class="cb-excl-none" :class="{ selected: cbExcl === null }" @click="selectExclNone">
                                    No Exclusivity — Standard placement
                                </div>
                            </div>

                            <div class="cb-excl-grid">
                                <div v-if="!exclOptions.length" style="font-size:12px;color:var(--muted);padding:8px 0;">No exclusivity upgrades defined. Add one below.</div>
                                <div v-for="opt in exclOptions" :key="opt.key" class="cb-excl-item" :class="{ selected: isExclSelected(opt.key) }" @click="selectExclByKey(opt.key)">
                                    <div v-if="manageOpen === 'excl'" class="cb-excl-item-actions" @click.stop>
                                        <button class="cb-excl-action-btn edit" title="Edit" @click="startExclEdit(opt.key)">✏️</button>
                                        <button class="cb-excl-action-btn delete" title="Delete" @click="deleteExclOption(opt.key)">🗑</button>
                                    </div>
                                    <div class="cb-excl-icon">{{ opt.icon }}</div>
                                    <div class="cb-excl-name">{{ opt.name }}</div>
                                    <div class="cb-excl-premium">+{{ opt.pct }}% premium</div>
                                </div>
                            </div>

                            <div class="cb-manage-panel" :class="{ open: manageOpen === 'excl' }" style="margin-top:14px;">
                                <div class="cb-manage-panel-head">
                                    <span class="cb-manage-panel-title">⚙️ Manage Exclusivity Upgrades</span>
                                    <button class="cb-manage-close" @click="toggleManage('excl')">✕</button>
                                </div>
                                <div class="cb-manage-list">
                                    <div v-for="opt in exclOptions" :key="`manage-${opt.key}`" class="cb-manage-row">
                                        <div class="cb-manage-row-icon">{{ opt.icon }}</div>
                                        <div class="cb-manage-row-info">
                                            <div class="cb-manage-row-name">{{ opt.name }}</div>
                                            <div class="cb-manage-row-meta">+{{ opt.pct }}% premium</div>
                                        </div>
                                        <div class="cb-manage-row-actions">
                                            <button class="cb-manage-action-btn" @click="startExclEdit(opt.key)">✏️ Edit</button>
                                            <button class="cb-manage-action-btn delete" @click="deleteExclOption(opt.key)">🗑</button>
                                        </div>
                                    </div>
                                </div>

                            <!-- Inline edit form -->
                            <div class="cb-excl-edit-form" v-if="exclEditKey">
                                <div class="cb-excl-edit-title">Edit Upgrade</div>
                                <div class="cb-excl-edit-row">
                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                        <div class="cb-excl-icon-picker-wrap">
                                            <input type="text" class="cb-excl-input" v-model="exclEditForm.icon" placeholder="🍺" maxlength="4" style="width:52px;text-align:center;font-size:20px;" />
                                            <button type="button" class="cb-excl-picker-btn" title="Choose icon" @click.stop="toggleExclIconPicker('edit')">😀</button>
                                            <div v-if="exclIconPickerOpen === 'edit'" class="cb-excl-icon-popover" @click.stop>
                                                <div v-for="group in exclIconCategories" :key="group.name" class="cb-excl-icon-group">
                                                    <div class="cb-excl-icon-group-name">{{ group.name }}</div>
                                                    <div class="cb-excl-icon-list">
                                                        <button v-for="icon in group.icons" :key="`${group.name}-${icon}`" type="button" class="cb-excl-icon-choice" @click="selectExclIcon('edit', icon)">{{ icon }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="getError(exclEditErrors, 'icon')" class="cb-error">{{ getError(exclEditErrors, 'icon') }}</div>
                                    </div>
                                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                                        <input type="text" class="cb-excl-input" v-model="exclEditForm.name" placeholder="Upgrade name e.g. Exclusive Beer Sponsor" style="width:100%;" />
                                        <div v-if="getError(exclEditErrors, 'name')" class="cb-error">{{ getError(exclEditErrors, 'name') }}</div>
                                    </div>
                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                        <div class="cb-excl-pct-wrap">
                                            <span class="cb-excl-pct-prefix">+</span>
                                            <input type="number" class="cb-excl-input" v-model="exclEditForm.pct" placeholder="300" min="10" max="2000" style="width:76px;text-align:right;" />
                                            <span class="cb-excl-pct-suffix">%</span>
                                        </div>
                                        <div v-if="getError(exclEditErrors, 'pct')" class="cb-error">{{ getError(exclEditErrors, 'pct') }}</div>
                                    </div>
                                </div>
                                <div style="display:flex;gap:8px;margin-top:10px;">
                                    <button class="btn btn-primary btn-sm" @click="saveExclEdit">✓ Save</button>
                                    <button class="btn btn-outline btn-sm" @click="cancelExclEdit">Cancel</button>
                                </div>
                            </div>

                            <!-- Add new upgrade -->
                            <div class="cb-excl-add-section">
                                <button class="cb-excl-add-toggle" @click="toggleAddExcl">
                                    <span>{{ exclAddOpen ? '✕' : '＋' }}</span> Add Custom Upgrade
                                </button>
                                <div class="cb-excl-add-form" v-if="exclAddOpen">
                                    <div class="cb-excl-edit-row">
                                        <div style="display:flex;flex-direction:column;gap:4px;">
                                            <div class="cb-excl-icon-picker-wrap">
                                                <input type="text" class="cb-excl-input" v-model="exclAddForm.icon" placeholder="🏅" maxlength="4" style="width:52px;text-align:center;font-size:20px;" />
                                                <button type="button" class="cb-excl-picker-btn" title="Choose icon" @click.stop="toggleExclIconPicker('add')">😀</button>
                                                <div v-if="exclIconPickerOpen === 'add'" class="cb-excl-icon-popover" @click.stop>
                                                    <div v-for="group in exclIconCategories" :key="group.name" class="cb-excl-icon-group">
                                                        <div class="cb-excl-icon-group-name">{{ group.name }}</div>
                                                        <div class="cb-excl-icon-list">
                                                            <button v-for="icon in group.icons" :key="`${group.name}-${icon}`" type="button" class="cb-excl-icon-choice" @click="selectExclIcon('add', icon)">{{ icon }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="getError(exclAddErrors, 'icon')" class="cb-error">{{ getError(exclAddErrors, 'icon') }}</div>
                                        </div>
                                        <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                                            <input type="text" class="cb-excl-input" v-model="exclAddForm.name" placeholder="Upgrade name e.g. Exclusive Energy Drink" style="width:100%;" />
                                            <div v-if="getError(exclAddErrors, 'name')" class="cb-error">{{ getError(exclAddErrors, 'name') }}</div>
                                        </div>
                                        <div style="display:flex;flex-direction:column;gap:4px;">
                                            <div class="cb-excl-pct-wrap">
                                                <span class="cb-excl-pct-prefix">+</span>
                                                <input type="number" class="cb-excl-input" v-model="exclAddForm.pct" placeholder="200" min="10" max="2000" style="width:76px;text-align:right;" />
                                                <span class="cb-excl-pct-suffix">%</span>
                                            </div>
                                            <div v-if="getError(exclAddErrors, 'pct')" class="cb-error">{{ getError(exclAddErrors, 'pct') }}</div>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:8px;margin-top:10px;">
                                        <button class="btn btn-primary btn-sm" @click="addExclOption">＋ Add Upgrade</button>
                                        <button class="btn btn-outline btn-sm" @click="toggleAddExcl">Cancel</button>
                                    </div>
                                </div>
                            </div>
                            </div><!-- /manage-panel-excl -->
                        </div>
                    </div><!-- /step 5 -->

                </div><!-- /.cb-config -->

                <!-- ══ RIGHT: PRICING SIDEBAR ══ -->
                <div class="cb-pricing-card">
                    <div class="cb-pricing-head">
                        <span class="cb-live-dot"></span>
                        <span class="cb-pricing-title">Live Pricing</span>
                    </div>

                    <div id="cb-price-breakdown">
                        <div class="cb-price-row">
                            <span class="cb-price-row-label">Base Campaign</span>
                            <span class="cb-price-row-val">{{ pricing.base > 0 ? fmtFull(pricing.base) : '—' }}</span>
                        </div>
                        <div v-if="cbTier !== null && pricing.tierMult > 1" class="cb-price-row multiplier">
                            <span class="cb-price-row-label">Territory Tier</span>
                            <span class="cb-price-row-val">{{ pricing.tierMult }}× tier</span>
                        </div>
                        <div v-if="pricing.channelAddons > 0" class="cb-price-row channel">
                            <span class="cb-price-row-label">Channel Add-ons</span>
                            <span class="cb-price-row-val">+{{ fmtFull(pricing.channelAddons) }}</span>
                        </div>
                        <div v-if="pricing.industryMult > 1 && cbIndustry" class="cb-price-row multiplier">
                            <span class="cb-price-row-label">Industry Multiplier</span>
                                <span class="cb-price-row-val">{{ getIndustryLabel(cbIndustry) }}</span>
                        </div>
                        <div v-if="cbSurge" class="cb-price-row surge">
                            <span class="cb-price-row-label">Surge Multiplier</span>
                            <span class="cb-price-row-val">{{ cbSurgeMultiplier }}× surge</span>
                        </div>
                        <div v-if="pricing.freqMult > 1" class="cb-price-row multiplier">
                            <span class="cb-price-row-label">Campaign Frequency</span>
                            <span class="cb-price-row-val">{{ { '1.2': 'Bi-Weekly 1.2×', '1.4': 'Weekly 1.4×', '1.8': 'Monthly 1.8×', '2.2': 'Quarterly 2.2×' }[String(pricing.freqMult)] || pricing.freqMult + '×' }}</span>
                        </div>
                        <div v-if="cbExcl && pricing.exclPremium > 0" class="cb-price-row excl">
                            <span class="cb-price-row-label">Exclusivity Premium</span>
                            <span class="cb-price-row-val">+{{ fmtFull(pricing.exclPremium) }}</span>
                        </div>
                        <div v-if="pricing.taxAmount > 0" class="cb-price-row">
                            <span class="cb-price-row-label" style="color:#f59e0b;">Tax</span>
                            <span class="cb-price-row-val" style="color:#f59e0b;">+{{ fmtFull(pricing.taxAmount) }} ({{ (pricing.taxRate * 100).toFixed(1) }}%)</span>
                        </div>
                    </div>

                    <div class="cb-total-box">
                        <div class="cb-total-label">Campaign Investment</div>
                        <div class="cb-total-amount">{{ displayTotalText }}</div>
                        <div class="cb-total-range">{{ displayRangeText }}</div>
                    </div>

                    <div class="cb-reach-row">
                        <div class="cb-reach-stat">
                            <div class="cb-reach-val">{{ pricing.reach > 0 ? fmtReach(pricing.reach) : '—' }}</div>
                            <div class="cb-reach-lbl">Est. Reach</div>
                        </div>
                        <div class="cb-reach-stat">
                            <div class="cb-reach-val">{{ pricing.reach > 0 ? fmtReach(pricing.impressions) : '—' }}</div>
                            <div class="cb-reach-lbl">Impressions</div>
                        </div>
                        <div class="cb-reach-stat">
                            <div class="cb-reach-val">{{ pricing.reach > 0 ? pricing.ctr : '—' }}</div>
                            <div class="cb-reach-lbl">Avg. CTR</div>
                        </div>
                    </div>

                    <div class="cb-media-value-box">
                        <div class="cb-mv-row">
                            <span class="cb-mv-label">Campaign Investment</span>
                            <span class="cb-mv-val">{{ mvCostText }}</span>
                        </div>
                        <div class="cb-mv-row">
                            <span class="cb-mv-label">Estimated Media Value</span>
                            <span class="cb-mv-val green">{{ mvValueText }}</span>
                        </div>
                        <div class="cb-mv-note">Based on equivalent paid media rates across the region</div>
                    </div>

                    <div class="cb-scarcity-bar">
                        <div class="cb-scarcity-head">
                            <span class="cb-scarcity-label">Sponsor Slots Available</span>
                            <span class="cb-scarcity-count">{{ cbType && cbTier !== null ? `${pricing.scarcitySlots} of 5 slots remaining` : '—' }}</span>
                        </div>
                        <div class="cb-scarcity-track">
                            <div class="cb-scarcity-fill" :style="{ width: pricing.scarcityPct + '%' }"></div>
                        </div>
                    </div>

                    <div class="cb-exec-summary" v-if="execSummaryLines">
                        <div class="cb-exec-title">📋 Executive Summary</div>
                        <div class="cb-exec-body" v-html="execSummaryLines"></div>
                    </div>

                    <!-- Cost Override & Notes -->
                    <div class="cb-override-section">
                        <button class="cb-override-toggle" :class="{ active: cbOverrideOpen }" @click="toggleOverride">✏️ Adjust Cost &amp; Notes</button>
                        <div class="cb-override-panel" v-if="cbOverrideOpen">
                            <div class="cb-override-field">
                                <label class="cb-override-label">Custom Cost Override</label>
                                <div class="cb-override-input-wrap">
                                    <span class="cb-override-dollar">$</span>
                                    <input type="number" class="cb-override-input" placeholder="0.00" min="0" step="100" @input="onOverrideInput" />
                                </div>
                                <span class="cb-override-hint">Leave blank to use calculated price</span>
                            </div>
                            <div class="cb-override-field" style="margin-top:10px;">
                                <label class="cb-override-label">Internal Notes</label>
                                <textarea class="cb-override-notes" v-model="cbOverrideNotes" placeholder="e.g. Custom rate negotiated with client — includes 2 bonus placements, agency discount applied…" rows="3"></textarea>
                            </div>
                            <div v-if="cbOverrideAmount" style="margin-top:8px;">
                                <div class="cb-price-row" style="background:rgba(204,255,0,.08);border-radius:6px;padding:6px 8px;">
                                    <span class="cb-price-row-label" style="color:#CCFF00;font-weight:700;">✓ Custom Rate Applied</span>
                                    <span class="cb-price-row-val" style="color:#CCFF00;font-weight:900;">${{ cbOverrideAmount.toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="cb-launch-btn" :disabled="isLaunching" @click="submitCampaign">
                        {{ isLaunching ? (editingCampaignId ? 'Updating...' : 'Launching...') : (editingCampaignId ? 'Update Campaign' : '🚀 Launch Campaign') }}
                    </button>
                </div><!-- /.cb-pricing-card -->

            </div><!-- /.cb-cols -->
        </div><!-- /#cb-view-builder -->
    </div>
</template>

<style scoped>
.cb-root {
    --brand: #29C9E8;
    --brand-dark: #1aaabf;
    --brand-pale: #e6f9fd;
    --brand-lime: #CCFF00;
    --dark: #051020;
    --mid: #374151;
    --muted: #6B7280;
    --border: #E5E7EB;
    --bg: #F6FAF3;
    --white: #ffffff;
    --red: #EF4444;
    --amber: #F59E0B;
    --blue: #3B82F6;
    --purple: #8B5CF6;
    --shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
    max-width: 1340px;
}

/* ── Header row ── */
.cb-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; flex-wrap: wrap; }
.cb-header-title { font-size: 22px; font-weight: 900; letter-spacing: -.5px; color: var(--dark); flex: 1; }
.cb-back-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px;
    border: 1.5px solid var(--border); background: var(--white); color: var(--mid);
    font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .15s;
}
.cb-back-btn:hover { border-color: var(--brand); color: var(--brand); }
.cb-draft-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px;
    border: 1.5px solid var(--border); background: var(--white); color: var(--mid);
    font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .15s;
}
.cb-draft-btn:hover { background: var(--bg); border-color: var(--mid); }
.cb-launch-header-btn {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; border-radius: 9px;
    background: var(--brand); color: #fff; font-size: 14px; font-weight: 800; cursor: pointer;
    border: none; font-family: inherit; box-shadow: 0 4px 14px rgba(111,189,30,.35);
    transition: background .15s, box-shadow .15s, transform .1s;
}
.cb-launch-header-btn:hover { background: var(--brand-dark); }
.cb-launch-header-btn:active { transform: scale(.97); }
.cb-launch-header-btn:disabled,
.cb-launch-btn:disabled {
    opacity: .65;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* ── Two-column layout ── */
.cb-cols { display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start; }
.cb-config { display: flex; flex-direction: column; gap: 20px; }

/* ── Section cards (step cards) ── */
.cb-section { background: var(--white); border-radius: 14px; border: 1.5px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }
.cb-section-head { display: flex; align-items: center; gap: 14px; padding: 18px 22px; border-bottom: 1px solid var(--border); }
.cb-step-num {
    width: 28px; height: 28px; border-radius: 50%; background: var(--brand); color: #fff;
    font-size: 13px; font-weight: 900; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.cb-section-head h3 { font-size: 15px; font-weight: 800; color: var(--dark); flex: 1; }
.cb-step-badge {
    font-size: 10px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; padding: 3px 9px;
    border-radius: 20px; background: var(--brand-pale); color: var(--brand-dark); border: 1px solid rgba(111,189,30,.25);
}
.cb-step-badge.optional { background: #F5F3FF; color: #7C3AED; border-color: #DDD6FE; }
.cb-section-body { padding: 20px 22px; }

/* ── Campaign type grid ── */
.cb-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.cb-type-card-enterprise {
    grid-column: 1 / -1; display: flex; flex-direction: row; align-items: center; gap: 16px; padding: 16px 20px;
    border-color: #B8860B22;
}
.cb-type-card-enterprise .cb-type-icon { margin-bottom: 0; font-size: 32px; flex-shrink: 0; }
.cb-type-card-enterprise .cb-type-info { flex: 1; }
.cb-enterprise-badge {
    display: inline-block; font-size: 9px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase;
    padding: 2px 8px; border-radius: 20px; background: linear-gradient(90deg, #B8860B, #DAA520); color: #fff; margin-bottom: 4px;
}
.cb-type-card {
    border: 2px solid var(--border); border-radius: 12px; padding: 18px 16px; cursor: pointer; position: relative;
    transition: border-color .15s, background .15s, box-shadow .15s; background: var(--white);
}
.cb-type-card:hover { border-color: var(--brand); background: var(--brand-pale); box-shadow: 0 0 0 3px rgba(111,189,30,.12); }
.cb-type-card.selected { border-color: var(--brand); background: var(--brand-pale); box-shadow: 0 0 0 3px rgba(111,189,30,.15); }
.cb-type-icon { font-size: 28px; margin-bottom: 10px; line-height: 1; display: block; }
.cb-type-name { font-size: 14px; font-weight: 800; color: var(--dark); margin-bottom: 4px; }
.cb-type-duration {
    display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px;
    background: var(--bg); color: var(--muted); border: 1px solid var(--border); margin-bottom: 6px;
}
.cb-type-price { font-size: 13px; font-weight: 800; color: var(--brand-dark); margin-bottom: 5px; }
.cb-type-desc { font-size: 11px; color: var(--muted); line-height: 1.5; }
.cb-type-check {
    position: absolute; top: 12px; right: 12px; width: 22px; height: 22px; border-radius: 50%;
    background: var(--brand); color: #fff; font-size: 12px; display: none; align-items: center; justify-content: center; font-weight: 900;
}
.cb-type-card.selected .cb-type-check { display: flex; }
.cb-type-card-enterprise:hover { border-color: #B8860B; background: #fffbea; box-shadow: 0 0 0 3px rgba(184,134,11,.12); }
.cb-type-card-enterprise.selected { border-color: #B8860B; background: #fffbea; box-shadow: 0 0 0 3px rgba(184,134,11,.15); }
.cb-type-card-enterprise .cb-type-price { color: #92680a; }
.cb-type-card-enterprise .cb-type-check { background: #B8860B; }

/* ── Executive Summary ── */
.cb-exec-summary { margin-top: 14px; padding: 14px; background: linear-gradient(135deg, #071525 0%, #0a2040 100%); border-radius: 10px; border: 1px solid rgba(41,201,232,.2); }
.cb-exec-title { font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--brand); margin-bottom: 8px; }
.cb-exec-body { font-size: 11.5px; color: rgba(255,255,255,.75); line-height: 1.7; }
.cb-exec-body :deep(strong) { color: #CCFF00; }

/* ── CRUD Management Panels ── */
.cb-manage-btn {
    margin-left: 8px; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px;
    background: rgba(41,201,232,.13); color: var(--brand); border: 1px solid rgba(41,201,232,.35);
    cursor: pointer; letter-spacing: .3px; white-space: nowrap; transition: background .15s; flex-shrink: 0;
}
.cb-manage-btn:hover { background: rgba(41,201,232,.25); }
.cb-manage-panel { display: none; background: #071525; border-radius: 12px; margin: 0 0 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
.cb-manage-panel.open { display: block; }
.cb-manage-panel-head { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.04); }
.cb-manage-panel-title { font-size: 12px; font-weight: 800; color: #fff; letter-spacing: .5px; text-transform: uppercase; }
.cb-manage-close { background: none; border: none; color: rgba(255,255,255,.4); font-size: 18px; cursor: pointer; line-height: 1; padding: 0 4px; }
.cb-manage-close:hover { color: #fff; }
.cb-manage-list { padding: 8px; }
.cb-manage-row { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; background: rgba(255,255,255,.04); margin-bottom: 4px; transition: background .15s; }
.cb-manage-row:hover { background: rgba(255,255,255,.08); }
.cb-manage-row-icon { font-size: 18px; flex-shrink: 0; width: 28px; text-align: center; }
.cb-manage-row-info { flex: 1; min-width: 0; }
.cb-manage-row-name { font-size: 13px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cb-manage-row-meta { font-size: 11px; color: rgba(255,255,255,.45); margin-top: 1px; }
.cb-manage-row-actions { display: flex; gap: 4px; flex-shrink: 0; }
.cb-manage-action-btn { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 6px; padding: 4px 8px; font-size: 12px; cursor: pointer; color: rgba(255,255,255,.6); transition: all .15s; }
.cb-manage-action-btn:hover { background: rgba(255,255,255,.15); color: #fff; }
.cb-manage-action-btn.delete:hover { background: rgba(220,38,38,.3); border-color: rgba(220,38,38,.5); color: #fca5a5; }
.cb-manage-form { background: rgba(255,255,255,.05); border-top: 1px solid rgba(255,255,255,.08); padding: 14px 16px; display: none; }
.cb-manage-form.open { display: block; }
.cb-manage-form-title { font-size: 11px; font-weight: 800; color: var(--brand); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 12px; }
.cb-manage-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.cb-manage-form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
.cb-manage-form-grid.cols-1 { grid-template-columns: 1fr; }
.cb-manage-field { display: flex; flex-direction: column; gap: 4px; }
.cb-manage-field label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .4px; }
.cb-manage-input { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #fff; width: 100%; box-sizing: border-box; transition: border-color .15s; }
.cb-manage-input:focus { outline: none; border-color: var(--brand); }
.cb-manage-input::placeholder { color: rgba(255,255,255,.25); }
.cb-manage-form-actions { display: flex; gap: 8px; margin-top: 12px; }
.cb-manage-save-btn { padding: 8px 18px; border-radius: 8px; background: var(--brand-lime); color: #0a1a0a; font-weight: 800; font-size: 12px; border: none; cursor: pointer; }
.cb-manage-cancel-btn { padding: 8px 14px; border-radius: 8px; background: rgba(255,255,255,.07); color: rgba(255,255,255,.6); font-size: 12px; border: 1px solid rgba(255,255,255,.1); cursor: pointer; }
.cb-manage-add-row { padding: 8px 16px 12px; border-top: 1px solid rgba(255,255,255,.06); }
.cb-manage-add-toggle { width: 100%; padding: 8px; border-radius: 8px; border: 1px dashed rgba(204,255,0,.35); background: rgba(204,255,0,.06); color: #CCFF00; font-size: 12px; font-weight: 700; cursor: pointer; text-align: center; transition: background .15s; }
.cb-manage-add-toggle:hover { background: rgba(204,255,0,.14); }

.cb-error { color: #fca5a5; font-size: 10px; font-weight: 700; margin-top: 4px; padding-left: 2px; }

/* ── Collapsible step accordion ── */
.cb-step-edit-btn {
    margin-left: auto; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px;
    background: rgba(204,255,0,.15); color: #CCFF00; border: 1px solid rgba(204,255,0,.4);
    cursor: pointer; letter-spacing: .3px; white-space: nowrap; transition: background .15s, border-color .15s; display: none;
}
.cb-step-edit-btn:hover { background: rgba(204,255,0,.28); border-color: #CCFF00; }
.cb-section-summary { display: none; align-items: center; gap: 8px; flex-wrap: wrap; padding: 10px 16px 14px; }
.cb-summary-chip { display: inline-flex; align-items: center; gap: 5px; background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
.cb-summary-chip.territory { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.cb-summary-chip.channel { background: #fefce8; color: #a16207; border-color: #fde68a; }
.cb-section.cb-collapsed .cb-section-body { display: none; }
.cb-section.cb-collapsed .cb-section-summary { display: flex; }
.cb-section.cb-collapsed .cb-step-edit-btn { display: inline-block; }
.cb-section.cb-collapsed .cb-step-badge { display: none; }
.cb-section-done-row { display: flex; justify-content: flex-end; padding: 12px 0 4px; }
.cb-section-done-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border-radius: 8px;
    background: var(--brand-lime); color: #0a1a0a; font-weight: 800; font-size: 12px; border: none; cursor: pointer;
    letter-spacing: .3px; opacity: .5; pointer-events: none; transition: opacity .2s, transform .1s;
}
.cb-section-done-btn.ready { opacity: 1; pointer-events: auto; }
.cb-section-done-btn.ready:hover { transform: translateY(-1px); }

/* ── Exclusivity CRUD ── */
.cb-excl-item { position: relative; }
.cb-excl-item-actions { position: absolute; top: 6px; right: 6px; display: none; gap: 4px; }
.cb-excl-item:hover .cb-excl-item-actions { display: flex; }
.cb-excl-action-btn { width: 22px; height: 22px; border-radius: 5px; border: none; cursor: pointer; font-size: 11px; display: flex; align-items: center; justify-content: center; transition: opacity .15s; line-height: 1; }
.cb-excl-action-btn.edit { background: rgba(59,130,246,.15); color: #3B82F6; }
.cb-excl-action-btn.edit:hover { background: rgba(59,130,246,.3); }
.cb-excl-action-btn.delete { background: rgba(239,68,68,.12); color: var(--red); }
.cb-excl-action-btn.delete:hover { background: rgba(239,68,68,.25); }
.cb-excl-edit-form { background: #f8fafc; border: 1.5px solid var(--brand); border-radius: 10px; padding: 14px; margin-top: 12px; }
.cb-excl-edit-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--brand-dark); margin-bottom: 10px; }
.cb-excl-edit-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.cb-excl-input { border: 1.5px solid var(--border); border-radius: 8px; padding: 8px 10px; font-size: 13px; font-family: inherit; background: #fff; color: var(--dark); outline: none; transition: border-color .15s; }
.cb-excl-input:focus { border-color: var(--brand); }
.cb-excl-icon-picker-wrap { position: relative; display: flex; align-items: center; gap: 8px; }
.cb-excl-picker-btn {
    width: 34px; height: 28px; border-radius: 10px; border: 1px solid rgba(41,201,232,.35);
    background: #e6f9fd; color: #075569; cursor: pointer; display: inline-flex; align-items: center;
    justify-content: center; font-size: 15px; line-height: 1; box-shadow: 0 2px 8px rgba(41,201,232,.12);
}
.cb-excl-picker-btn:hover { border-color: var(--brand); background: #d9f6fc; }
.cb-excl-icon-popover {
    position: absolute; left: 0; top: 42px; z-index: 30; width: min(290px, 78vw); padding: 12px;
    border-radius: 10px; background: #13283b; border: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 18px 45px rgba(5,16,32,.32); color: rgba(255,255,255,.78);
}
.cb-excl-icon-group { margin-bottom: 9px; }
.cb-excl-icon-group:last-child { margin-bottom: 0; }
.cb-excl-icon-group-name { font-size: 11px; font-weight: 800; color: #8fd9ee; margin-bottom: 6px; }
.cb-excl-icon-list { display: flex; flex-wrap: wrap; gap: 6px; }
.cb-excl-icon-choice {
    width: 25px; height: 25px; border-radius: 6px; border: 1px solid transparent; background: rgba(255,255,255,.06);
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; line-height: 1;
}
.cb-excl-icon-choice:hover { background: rgba(41,201,232,.18); border-color: rgba(41,201,232,.35); }
.cb-excl-pct-wrap { display: flex; align-items: center; gap: 2px; background: #fff; border: 1.5px solid var(--border); border-radius: 8px; padding: 0 8px; height: 38px; }
.cb-excl-pct-wrap:focus-within { border-color: var(--brand); }
.cb-excl-pct-prefix, .cb-excl-pct-suffix { font-size: 13px; font-weight: 700; color: var(--muted); }
.cb-excl-pct-wrap input { border: none; outline: none; background: transparent; font-size: 13px; font-weight: 700; color: var(--dark); font-family: inherit; }
.cb-excl-add-section { margin-top: 14px; }
.cb-excl-add-toggle { width: 100%; padding: 9px 14px; border: 1.5px dashed var(--border); border-radius: 8px; background: none; color: var(--muted); font-size: 12px; font-weight: 700; cursor: pointer; transition: all .15s; text-align: center; font-family: inherit; }
.cb-excl-add-toggle:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-pale); }
.cb-excl-add-form { background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 14px; margin-top: 10px; }

/* ── Cost override panel ── */
.cb-override-section { margin-top: 14px; border-top: 1px solid rgba(255,255,255,.08); padding-top: 12px; }
.cb-override-toggle { width: 100%; background: none; border: 1.5px dashed rgba(255,255,255,.15); border-radius: 8px; color: rgba(255,255,255,.4); font-size: 11.5px; font-weight: 700; cursor: pointer; padding: 8px 12px; text-align: center; transition: all .15s; font-family: inherit; }
.cb-override-toggle:hover { border-color: var(--brand-lime); color: var(--brand-lime); }
.cb-override-toggle.active { border-color: var(--brand-lime); color: var(--brand-lime); background: rgba(204,255,0,.06); }
.cb-override-panel { background: rgba(0,0,0,.25); border-radius: 10px; padding: 14px; margin-top: 10px; border: 1px solid rgba(255,255,255,.08); }
.cb-override-label { display: block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.4); margin-bottom: 6px; }
.cb-override-input-wrap { display: flex; align-items: center; gap: 0; background: rgba(255,255,255,.08); border: 1.5px solid rgba(255,255,255,.12); border-radius: 8px; overflow: hidden; }
.cb-override-input-wrap:focus-within { border-color: var(--brand-lime); }
.cb-override-dollar { padding: 0 10px; font-size: 16px; font-weight: 800; color: rgba(255,255,255,.4); }
.cb-override-input { flex: 1; background: transparent; border: none; outline: none; color: #fff; font-size: 18px; font-weight: 900; padding: 8px 10px 8px 0; font-family: inherit; }
.cb-override-input::placeholder { color: rgba(255,255,255,.2); }
.cb-override-hint { font-size: 10px; color: rgba(255,255,255,.25); margin-top: 4px; display: block; }
.cb-override-notes { width: 100%; background: rgba(255,255,255,.06); border: 1.5px solid rgba(255,255,255,.1); border-radius: 8px; color: rgba(255,255,255,.7); font-size: 12px; padding: 8px 10px; font-family: inherit; resize: vertical; outline: none; transition: border-color .15s; }
.cb-override-notes:focus { border-color: var(--brand-lime); }
.cb-override-notes::placeholder { color: rgba(255,255,255,.2); }

/* ── Tier selector ── */
.cb-tier-badge { font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; }
.cb-tier-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.cb-tier-opt { border: 1.5px solid var(--border); border-radius: 10px; padding: 12px 14px; cursor: pointer; transition: border-color .15s, background .15s; background: var(--white); position: relative; }
.cb-tier-opt:hover { border-color: var(--brand); background: var(--brand-pale); }
.cb-tier-opt.selected { border-color: var(--brand); background: var(--brand-pale); }
.cb-tier-opt.selected::after { content: '✓'; position: absolute; top: 8px; right: 10px; font-size: 12px; font-weight: 900; color: var(--brand); }
.cb-tier-num { font-size: 9px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); margin-bottom: 3px; }
.cb-tier-name { font-size: 13px; font-weight: 800; color: var(--dark); margin-bottom: 2px; }
.cb-tier-range { font-size: 11px; font-weight: 700; color: var(--brand-dark); }

/* ── Country selector ── */
#cb-country-selector { margin-top: 16px; border: 1.5px solid var(--border); border-radius: 12px; overflow: hidden; background: var(--bg); }
.cb-country-section-label { font-size: 9px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); padding: 10px 14px 6px; background: var(--bg); display: block; }
.cb-country-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; padding: 0 10px 10px; }
.cb-country-item { display: flex; align-items: center; gap: 7px; padding: 7px 10px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--white); cursor: pointer; font-size: 12px; font-weight: 600; color: var(--mid); transition: border-color .15s, background .15s; user-select: none; }
.cb-country-item:hover { border-color: var(--brand); background: var(--brand-pale); }
.cb-country-item.selected { border-color: var(--brand); background: var(--brand-pale); color: var(--brand-dark); }
.cb-country-item input[type=checkbox] { display: none; }
.cb-country-flag { font-size: 15px; line-height: 1; }

/* ── Diaspora grid ── */
.cb-diaspora-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; padding: 0 10px 10px; }
.cb-diaspora-item { display: flex; align-items: center; gap: 7px; padding: 7px 10px; border-radius: 8px; border: 1.5px solid #DDD6FE; background: #FAFAFF; cursor: pointer; font-size: 12px; font-weight: 600; color: #6D28D9; transition: border-color .15s, background .15s; user-select: none; }
.cb-diaspora-item:hover { border-color: var(--purple); background: #F5F3FF; }
.cb-diaspora-item.selected { border-color: var(--purple); background: #EDE9FE; font-weight: 800; }
.cb-diaspora-item input[type=checkbox] { display: none; }

/* ── Channels ── */
.cb-channels-note { font-size: 12px; color: var(--brand-dark); background: var(--brand-pale); border: 1px solid rgba(111,189,30,.25); border-radius: 8px; padding: 8px 12px; margin-bottom: 14px; font-weight: 600; }
.cb-channel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.cb-channel-item { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 10px; border: 1.5px solid var(--border); background: var(--white); cursor: pointer; font-size: 13px; font-weight: 600; color: var(--mid); transition: border-color .15s, background .15s; user-select: none; position: relative; }
.cb-channel-item:hover:not(.locked) { border-color: var(--blue); background: #EFF6FF; }
.cb-channel-item.selected:not(.locked) { border-color: var(--blue); background: #EFF6FF; color: #1D4ED8; }
.cb-channel-item.locked { background: #F0FDF4; border-color: var(--brand); color: var(--brand-dark); cursor: default; }
.cb-channel-icon { font-size: 18px; flex-shrink: 0; }
.cb-channel-info { flex: 1; min-width: 0; }
.cb-channel-name { font-size: 13px; font-weight: 700; color: inherit; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cb-channel-price { font-size: 11px; color: var(--muted); margin-top: 1px; }
.cb-channel-item.locked .cb-channel-price { color: var(--brand); }
.cb-channel-check { width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid var(--border); background: var(--white); display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; color: transparent; transition: all .15s; }
.cb-channel-item.selected .cb-channel-check, .cb-channel-item.locked .cb-channel-check { background: var(--brand); border-color: var(--brand); color: #fff; }
.cb-channel-item.selected:not(.locked) .cb-channel-check { background: var(--blue); border-color: var(--blue); color: #fff; }

/* ── Industry & Schedule ── */
.cb-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px; }
.cb-field { display: flex; flex-direction: column; gap: 6px; }
.cb-field label { font-size: 12px; font-weight: 700; color: var(--mid); text-transform: uppercase; letter-spacing: .4px; }
.cb-field select, .cb-field input[type=date] { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 13px; color: var(--dark); background: var(--white); outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s; cursor: pointer; }
.cb-field select:focus, .cb-field input[type=date]:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(111,189,30,.14); }

/* ── Surge box ── */
.cb-surge-box { display: flex; align-items: center; gap: 14px; padding: 16px 18px; border: 2px solid #FDE68A; border-radius: 12px; background: #FFFBEB; cursor: pointer; transition: border-color .15s, background .15s; margin-top: 16px; user-select: none; }
.cb-surge-box:hover { border-color: var(--amber); }
.cb-surge-box.active { border-color: #F59E0B; background: #FEF3C7; }
.cb-surge-icon { font-size: 24px; flex-shrink: 0; }
.cb-surge-info { flex: 1; }
.cb-surge-label { font-size: 14px; font-weight: 800; color: #92400E; }
.cb-surge-desc { font-size: 12px; color: #B45309; margin-top: 2px; }
.cb-surge-mult-badge { font-size: 13px; font-weight: 900; color: #fff; background: #F59E0B; padding: 4px 12px; border-radius: 20px; }
.cb-surge-toggle { width: 44px; height: 24px; background: #D1D5DB; border-radius: 12px; position: relative; transition: background .2s; flex-shrink: 0; }
.cb-surge-toggle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: left .2s; }
.cb-surge-box.active .cb-surge-toggle { background: #F59E0B; }
.cb-surge-box.active .cb-surge-toggle::after { left: 23px; }
#cb-surge-selector { margin-top: 12px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.cb-surge-opt { padding: 10px 12px; border-radius: 9px; border: 1.5px solid #FDE68A; background: #FFFBEB; cursor: pointer; text-align: center; font-size: 12px; font-weight: 700; color: #92400E; transition: all .15s; }
.cb-surge-opt:hover { border-color: #F59E0B; background: #FEF3C7; }
.cb-surge-opt.selected { border-color: #F59E0B; background: #F59E0B; color: #fff; }
.cb-surge-opt-name { font-size: 12px; font-weight: 800; margin-bottom: 2px; }
.cb-surge-opt-mult { font-size: 13px; font-weight: 900; }

/* ── Exclusivity ── */
.cb-excl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.cb-excl-item { border: 1.5px solid #DDD6FE; border-radius: 10px; padding: 14px 12px; text-align: center; cursor: pointer; background: #FAFAFF; transition: border-color .15s, background .15s; position: relative; }
.cb-excl-item:hover { border-color: var(--purple); background: #F5F3FF; }
.cb-excl-item.selected { border-color: var(--purple); background: #EDE9FE; box-shadow: 0 0 0 3px rgba(139,92,246,.15); }
.cb-excl-item.selected::after { content: '✓'; position: absolute; top: 6px; right: 8px; font-size: 11px; font-weight: 900; color: var(--purple); }
.cb-excl-icon { font-size: 22px; margin-bottom: 6px; }
.cb-excl-name { font-size: 12px; font-weight: 800; color: #4C1D95; margin-bottom: 3px; }
.cb-excl-premium { font-size: 11px; font-weight: 700; color: #7C3AED; }
.cb-excl-none {
    grid-column: 1 / -1; display: flex; align-items: center; justify-content: center; gap: 8px; border: 1.5px solid var(--border);
    border-radius: 10px; padding: 10px; text-align: center; cursor: pointer; background: var(--white); font-size: 13px; font-weight: 700;
    color: var(--mid); transition: border-color .15s, background .15s; margin-bottom: 4px;
}
.cb-excl-none:hover { border-color: var(--mid); background: var(--bg); }
.cb-excl-none.selected { border-color: var(--mid); background: var(--bg); color: var(--dark); }

/* ══ PRICING SIDEBAR ══ */
.cb-pricing-card { background: #0a1628; border-radius: 16px; padding: 24px; position: sticky; top: 80px; box-shadow: 0 20px 60px rgba(10,22,40,.5); overflow: hidden; }
.cb-pricing-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--brand), #3dd68c, var(--blue)); }
.cb-pricing-head { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; }
.cb-pricing-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.7); flex: 1; }
.cb-live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--brand); animation: cb-pulse 1.8s ease-in-out infinite; flex-shrink: 0; }
@keyframes cb-pulse { 0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(111,189,30,.5); } 50% { opacity: .7; box-shadow: 0 0 0 5px rgba(111,189,30,0); } }

#cb-price-breakdown { margin-bottom: 18px; }
.cb-price-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 9px 0; border-bottom: 1px solid rgba(255,255,255,.06); font-size: 13px; }
.cb-price-row:last-child { border-bottom: none; }
.cb-price-row-label { color: rgba(255,255,255,.55); font-weight: 500; }
.cb-price-row-val { color: #fff; font-weight: 700; white-space: nowrap; }
.cb-price-row.multiplier .cb-price-row-label { color: #FCD34D; }
.cb-price-row.multiplier .cb-price-row-val { color: #FCD34D; }
.cb-price-row.surge .cb-price-row-label { color: #FCA5A5; }
.cb-price-row.surge .cb-price-row-val { color: #FCA5A5; }
.cb-price-row.channel .cb-price-row-label { color: #93C5FD; }
.cb-price-row.channel .cb-price-row-val { color: #93C5FD; }
.cb-price-row.excl .cb-price-row-label { color: #C4B5FD; }
.cb-price-row.excl .cb-price-row-val { color: #C4B5FD; }

.cb-total-box { background: linear-gradient(135deg, rgba(111,189,30,.2), rgba(111,189,30,.1)); border: 1.5px solid rgba(111,189,30,.35); border-radius: 12px; padding: 18px 20px; text-align: center; margin-bottom: 18px; }
.cb-total-label { font-size: 9px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,.45); margin-bottom: 8px; }
.cb-total-amount { font-size: 34px; font-weight: 900; color: #fff; letter-spacing: -1px; line-height: 1; }
.cb-total-range { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 6px; }

.cb-reach-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
.cb-reach-stat { background: rgba(255,255,255,.05); border-radius: 10px; padding: 12px 8px; text-align: center; border: 1px solid rgba(255,255,255,.07); }
.cb-reach-val { font-size: 16px; font-weight: 900; color: #fff; line-height: 1; margin-bottom: 4px; }
.cb-reach-lbl { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: rgba(255,255,255,.35); }

.cb-media-value-box { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09); border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; }
.cb-mv-row { display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 7px; }
.cb-mv-row:last-of-type { margin-bottom: 10px; }
.cb-mv-label { color: rgba(255,255,255,.45); font-weight: 500; }
.cb-mv-val { color: #fff; font-weight: 700; }
.cb-mv-val.green { color: #86EFAC; font-size: 14px; font-weight: 900; }
.cb-mv-note { font-size: 10px; color: rgba(255,255,255,.28); line-height: 1.5; border-top: 1px solid rgba(255,255,255,.07); padding-top: 8px; }

.cb-scarcity-bar { margin-bottom: 18px; }
.cb-scarcity-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
.cb-scarcity-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .5px; }
.cb-scarcity-count { font-size: 12px; font-weight: 900; color: #FCA5A5; }
.cb-scarcity-track { height: 6px; background: rgba(255,255,255,.1); border-radius: 3px; overflow: hidden; }
.cb-scarcity-fill { height: 100%; border-radius: 3px; background: #EF4444; transition: width .5s ease; }

.cb-launch-btn {
    width: 100%; padding: 15px; border-radius: 11px; background: var(--brand); color: #fff; font-size: 15px; font-weight: 900;
    font-family: inherit; border: none; cursor: pointer; box-shadow: 0 6px 24px rgba(111,189,30,.45);
    transition: background .15s, box-shadow .15s, transform .1s; display: flex; align-items: center; justify-content: center; gap: 8px; letter-spacing: -.2px;
}
.cb-launch-btn:hover { background: var(--brand-dark); box-shadow: 0 8px 30px rgba(111,189,30,.55); }
.cb-launch-btn:active { transform: scale(.98); }

/* ══ CAMPAIGNS LIST VIEW ══ */
.cb-list-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
.cb-list-title { font-size: 22px; font-weight: 900; letter-spacing: -.4px; }
.cb-new-btn {
    display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; border-radius: 9px; background: var(--brand);
    color: #fff; font-size: 14px; font-weight: 800; cursor: pointer; border: none; font-family: inherit;
    box-shadow: 0 4px 14px rgba(111,189,30,.3); transition: background .15s;
}
.cb-new-btn:hover { background: var(--brand-dark); }
.cb-campaigns-list { display: flex; flex-direction: column; gap: 10px; }
.cb-campaign-row {
    background: var(--white); border: 1.5px solid var(--border); border-radius: 13px; padding: 16px 20px; display: grid;
    grid-template-columns: auto 1fr auto auto auto auto; gap: 16px; align-items: center; box-shadow: var(--shadow);
    transition: border-color .15s, box-shadow .15s;
}
.cb-campaign-row:hover { border-color: var(--brand); box-shadow: 0 4px 20px rgba(111,189,30,.12); }
.cb-campaign-type-badge { font-size: 22px; width: 44px; height: 44px; border-radius: 10px; background: var(--bg); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cb-campaign-name { font-size: 14px; font-weight: 800; color: var(--dark); margin-bottom: 3px; }
.cb-campaign-meta { font-size: 12px; color: var(--muted); }
.cb-campaign-status { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.cb-campaign-status.active { background: #DCFCE7; color: #16A34A; }
.cb-campaign-status.ended { background: #FEE2E2; color: #DC2626; }
.cb-campaign-status.active::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #16A34A; }
.cb-campaign-budget { font-size: 15px; font-weight: 900; color: var(--dark); }
.cb-campaign-channels { font-size: 12px; color: var(--muted); font-weight: 600; white-space: nowrap; }
.cb-campaign-reach { font-size: 13px; font-weight: 800; color: var(--brand-dark); white-space: nowrap; }
.cb-view-btn { padding: 7px 14px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--white); color: var(--mid); font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .15s; white-space: nowrap; }
.cb-view-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-pale); }
.cb-campaign-modal {
    position: fixed;
    inset: 0;
    z-index: 80;
    background: rgba(5,16,32,.44);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.cb-campaign-modal-card {
    width: min(860px, 100%);
    max-height: calc(100vh - 48px);
    overflow: auto;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 24px 80px rgba(5,16,32,.24);
    padding: 22px;
}
.cb-campaign-modal-head {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 14px;
    align-items: start;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}
.cb-campaign-modal-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.cb-campaign-modal-kicker {
    font-size: 11px;
    font-weight: 900;
    color: var(--brand-dark);
    text-transform: uppercase;
}
.cb-campaign-modal-title {
    margin-top: 2px;
    font-size: 18px;
    font-weight: 900;
    color: var(--dark);
}
.cb-campaign-modal-meta {
    margin-top: 4px;
    font-size: 12px;
    color: var(--muted);
    line-height: 1.45;
}
.cb-campaign-modal-close {
    width: 34px;
    height: 34px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--white);
    color: var(--mid);
    cursor: pointer;
    font-size: 22px;
    line-height: 1;
}
.cb-campaign-modal-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin: 16px 0;
}
.cb-campaign-modal-stats > div {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
}
.cb-campaign-modal-stats span,
.cb-campaign-detail-row span {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}
.cb-campaign-modal-stats strong {
    display: block;
    margin-top: 5px;
    color: var(--dark);
    font-size: 14px;
}
.cb-campaign-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.cb-campaign-detail-block {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px;
    background: #fff;
}
.cb-campaign-detail-block.full { margin-top: 12px; }
.cb-campaign-detail-block h4 {
    margin: 0 0 10px;
    color: var(--dark);
    font-size: 13px;
    font-weight: 900;
}
.cb-campaign-detail-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 8px 0;
    border-top: 1px solid #F1F5F9;
}
.cb-campaign-detail-row strong {
    color: var(--dark);
    font-size: 12px;
    text-align: right;
}
.cb-campaign-chip-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.cb-campaign-chip {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 5px 10px;
    border-radius: 999px;
    background: var(--brand-pale);
    color: var(--brand-dark);
    font-size: 12px;
    font-weight: 800;
}
.cb-campaign-empty,
.cb-campaign-notes {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

/* ── Responsive ── */
@media (max-width: 1100px) {
    .cb-cols { grid-template-columns: 1fr; }
    .cb-pricing-card { position: static; }
}
@media (max-width: 640px) {
    .cb-type-grid { grid-template-columns: 1fr; }
    .cb-tier-grid { grid-template-columns: 1fr; }
    .cb-country-grid { grid-template-columns: 1fr 1fr; }
    .cb-diaspora-grid { grid-template-columns: 1fr 1fr; }
    .cb-channel-grid { grid-template-columns: 1fr; }
    #cb-surge-selector { grid-template-columns: 1fr 1fr; }
    .cb-excl-grid { grid-template-columns: 1fr 1fr; }
    .cb-reach-row { grid-template-columns: 1fr; }
    .cb-campaign-row { grid-template-columns: auto 1fr auto; }
    .cb-campaign-budget, .cb-campaign-channels, .cb-campaign-reach { display: none; }
    .cb-campaign-modal { padding: 12px; align-items: flex-start; }
    .cb-campaign-modal-stats, .cb-campaign-detail-grid { grid-template-columns: 1fr; }
    .cb-campaign-modal-head { grid-template-columns: auto 1fr; }
    .cb-campaign-modal-close { grid-column: 2; justify-self: end; }
}
</style>
