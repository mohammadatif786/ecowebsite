<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import {
    Bookmark,
    BookmarkCheck,
    BookOpen,
    CheckCheck,
    Clock,
    Eraser,
    Eye,
    FileText,
    Flame,
    Globe,
    Globe2,
    Moon,
    Paperclip,
    Pencil,
    Radio,
    RotateCcw,
    Save,
    Search,
    Send,
    Share2,
    Shield,
    SlidersHorizontal,
    Sparkles,
    Star,
    Tag,
    Users,
    X,
} from 'lucide-vue-next';
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import DefaultImage from '../../../../assets/images/default-image2.png';
import NewsStoryCard from './NewsStoryCard.vue';
import { numFmt, readTimeFor, relTime, type Region, type SponsorAd, type Story } from './types';

const emit = defineEmits<{
    (event: 'view-changed', id: string): void;
}>();

type Country = {
    key: string;
    code?: string;
    label: string;
    flag: string;
    group: 'caribbean' | 'latam' | 'us' | 'global';
    region: Region;
};

type NewsRow = {
    id: number | string;
    title?: string;
    summary?: string;
    body?: string;
    content?: string;
    image_object?: string | null;
    country?: string;
    countryCode?: string;
    country_code?: string;
    region?: Region | string;
    category?: string;
    sourceType?: string;
    source_type?: string;
    sourceName?: string;
    source_name?: string;
    publishedAt?: string;
    published_at?: string;
    created_at?: string;
    isBreaking?: boolean;
    is_breaking?: boolean;
    breakingExpiresAt?: string | null;
    breaking_expires_at?: string | null;
    trending?: number | string;
    views?: number | string;
    media?: Array<{
        type?: string;
        name?: string;
        dataUrl?: string;
        url?: string;
        size?: number;
    }>;
    status?: boolean | number | string;
};

type Preferences = {
    regions: Region[];
    countries: string[];
    categories: string[];
    feeds: boolean;
    internal: boolean;
    personalization: 'Personalized (recommended)' | 'Strict' | 'Relaxed';
};

const STORAGE_PREFS = 'c360Prefs';
const STORAGE_BOOKMARKS = 'c360Bookmarks';

const categories = [
    'Breaking News',
    'Politics',
    'Business & Economy',
    'Entertainment',
    'Sports',
    'Culture',
    'Technology',
    'Faith',
    'Faith & Society',
    'Events',
    'Travel',
    'Environment',
];

const countries: Country[] = [
    { key: 'bahamas', label: 'Bahamas', flag: '🇧🇸', group: 'caribbean', region: 'Caribbean' },
    { key: 'barbados', label: 'Barbados', flag: '🇧🇧', group: 'caribbean', region: 'Caribbean' },
    { key: 'jamaica', label: 'Jamaica', flag: '🇯🇲', group: 'caribbean', region: 'Caribbean' },
    { key: 'trinidad', label: 'Trinidad & Tobago', flag: '🇹🇹', group: 'caribbean', region: 'Caribbean' },
    { key: 'guyana', label: 'Guyana', flag: '🇬🇾', group: 'caribbean', region: 'Caribbean' },
    { key: 'dominican', label: 'Dominican Republic', flag: '🇩🇴', group: 'caribbean', region: 'Caribbean' },
    { key: 'colombia', label: 'Colombia', flag: '🇨🇴', group: 'latam', region: 'Latin America' },
    { key: 'brazil', label: 'Brazil', flag: '🇧🇷', group: 'latam', region: 'Latin America' },
    { key: 'mexico', label: 'Mexico', flag: '🇲🇽', group: 'latam', region: 'Latin America' },
    { key: 'panama', label: 'Panama', flag: '🇵🇦', group: 'latam', region: 'Latin America' },
    { key: 'miami', label: 'Miami Diaspora', flag: '🇺🇸', group: 'us', region: 'Global' },
    { key: 'new-york', label: 'New York Diaspora', flag: '🇺🇸', group: 'us', region: 'Global' },
    { key: 'global', label: 'Global', flag: '🌐', group: 'global', region: 'Global' },
];

const authors = ['Caribbean Desk', 'Ramon Persaud', 'Sasha Baptiste', 'Lucia Mendez', 'Global Desk', 'Energy Desk'];
const countryCodesByName: Record<string, string> = {
    Bahamas: 'BS',
    Barbados: 'BB',
    Jamaica: 'JM',
    'Trinidad & Tobago': 'TT',
    Guyana: 'GY',
    'Dominican Republic': 'DO',
    Colombia: 'CO',
    Brazil: 'BR',
    Mexico: 'MX',
    Panama: 'PA',
    'Miami Diaspora': 'US-MIA',
    'New York Diaspora': 'US-NYC',
    Global: 'GLB',
};

const sponsorAds: SponsorAd[] = [
    {
        id: 'ad1',
        sponsor: 'Caribbean Travel Co.',
        title: 'Escape to the Islands',
        body: 'Book your dream Caribbean getaway with exclusive LinkUp member rates.',
        format: 'image',
        media: 'https://picsum.photos/seed/c360-travel-ad/720/360',
        cta: 'Book Now',
        package: 'Regional Image Banner',
        target: 'Caribbean',
        targetType: 'region',
        status: 'Active',
    },
    {
        id: 'ad2',
        sponsor: 'Island Audio',
        title: 'Relaxing Caribbean Sounds',
        body: 'Let steel drums and ocean waves transport you to paradise.',
        format: 'audio',
        media: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
        cta: 'Listen Now',
        package: 'Audio Insert',
        target: 'Events',
        targetType: 'category',
        status: 'Active',
    },
    {
        id: 'ad3',
        sponsor: 'Scotiabank',
        title: 'Bank Smarter Across the Region',
        body: 'Manage money across the Caribbean with modern digital banking.',
        format: 'image',
        media: 'https://picsum.photos/seed/c360-bank-ad/720/360',
        cta: 'Learn More',
        package: 'Finance Takeover',
        target: 'Business & Economy',
        targetType: 'category',
        status: 'Active',
    },
];

const allCountryKeys = countries.map((country) => country.key);
const page = usePage<{ news?: NewsRow[]; message?: string }>();

const normalizeRegion = (region?: string): Region => {
    if (region === 'Latin America' || region === 'Global') return region;
    return 'Caribbean';
};

const countryForRow = (row: NewsRow) => {
    const countryCode = (row.countryCode || row.country_code || '').toString();
    const countryName = row.country || '';
    return countries.find((country) => country.code === countryCode)
        || countries.find((country) => countryCodesByName[country.label] === countryCode)
        || countries.find((country) => country.label === countryName)
        || countries.find((country) => country.key === countryCode.toLowerCase())
        || (normalizeRegion(row.region) === 'Global' ? countries.find((country) => country.key === 'global') : undefined);
};

const hoursSince = (value?: string) => {
    if (!value) return 0;
    const time = new Date(value).getTime();
    if (Number.isNaN(time)) return 0;
    return Math.max(0, Math.round((Date.now() - time) / 36e5));
};

const isBreakingRowActive = (row: NewsRow) => {
    const isBreaking = Boolean(row.isBreaking ?? row.is_breaking);
    const expiresAt = row.breakingExpiresAt ?? row.breaking_expires_at;
    if (!isBreaking) return false;
    if (!expiresAt) return true;
    return new Date(expiresAt).getTime() > Date.now();
};

const mediaUrl = (row: NewsRow, type: 'image' | 'video' | 'audio') => {
    const media = row.media?.find((item) => item.type === type);
    return media?.dataUrl || media?.url;
};

const statusForRow = (status: NewsRow['status']): Story['status'] => {
    if (status === true || status === 1 || status === '1' || status === 'Published') return 'Published';
    return 'Draft';
};

const storyFromRow = (row: NewsRow): Story => {
    const publishedAt = row.publishedAt || row.published_at || row.created_at;
    const country = countryForRow(row);
    const trend = Number(row.trending || 0);
    const title = row.title?.trim() || 'Untitled Article';
    const body = row.body || row.content || '';
    const summary = row.summary || body.slice(0, 180) || 'No summary available.';

    return {
        id: row.id.toString(),
        title,
        summary,
        body,
        country: row.country || country?.label || 'Global',
        countryCode: row.countryCode || row.country_code || country?.code,
        flag: country?.flag || (normalizeRegion(row.region) === 'Global' ? 'ðŸŒ' : 'ðŸ“°'),
        category: row.category || 'Breaking News',
        region: normalizeRegion(row.region),
        hours: hoursSince(publishedAt),
        fire: trend,
        breaking: isBreakingRowActive(row),
        breakingExpiresAt: row.breakingExpiresAt ?? row.breaking_expires_at ?? null,
        status: statusForRow(row.status),
        author: row.sourceName || row.source_name || 'LinkUp Desk',
        source: row.sourceName || row.source_name || 'LinkUp Desk',
        sourceType: row.sourceType || row.source_type || 'internal',
        publishedAt,
        views: Number(row.views || row.trending || 0),
        img: row.image_object || mediaUrl(row, 'image') || DefaultImage,
        mediaVideo: mediaUrl(row, 'video'),
        mediaAudio: mediaUrl(row, 'audio'),
        media: row.media || [],
    };
};

const defaultPrefs: Preferences = {
    regions: ['Caribbean', 'Latin America', 'Global'],
    countries: allCountryKeys,
    categories: [...categories],
    feeds: true,
    internal: true,
    personalization: 'Personalized (recommended)',
};

const stories = ref<Story[]>([]);
const prefs = ref<Preferences>(structuredClone(defaultPrefs));
const draftPrefs = ref<Preferences>(structuredClone(defaultPrefs));
const bookmarks = ref<Set<string>>(new Set());
const search = ref('');
const quickCategory = ref('all');
const sortMode = ref<'newest' | 'trending'>('newest');
const savedOnly = ref(false);
const darkMode = ref(false);
const showPrefs = ref(false);
const showAdmin = ref(false);
const selectedStory = ref<Story | null>(null);
const editingStory = ref<Story | null>(null);

const imageInputRef = ref<HTMLInputElement | null>(null);
const videoInputRef = ref<HTMLInputElement | null>(null);
const audioInputRef = ref<HTMLInputElement | null>(null);

const incomingNews = computed(() => Array.isArray(page.props.news) ? page.props.news : []);

watch(incomingNews, (news) => {
    stories.value = news.map(storyFromRow);
    if (selectedStory.value) {
        selectedStory.value = stories.value.find((story) => story.id === selectedStory.value?.id) || null;
    }
}, { immediate: true, deep: true });

const adminForm = ref<{
    headline: string;
    summary: string;
    body: string;
    region: Region;
    country: string;
    category: string;
    breaking: boolean;
    author: string;
    source: string;
    imageFile: File | null;
    videoFile: File | null;
    audioFile: File | null;
}>({
    headline: '',
    summary: '',
    body: '',
    region: 'Caribbean',
    country: 'Bahamas',
    category: 'Events',
    breaking: false,
    author: 'Caribbean Desk',
    source: 'LinkUp Caribbean 360 Desk',
    imageFile: null,
    videoFile: null,
    audioFile: null,
});

const num = numFmt;
const readTime = (story: Story) => readTimeFor(story);
const rel = (hours: number) => relTime(hours);
const countryKeyFor = (story: Story) =>
    countries.find((country) => country.label === story.country)?.key
    || countries.find((country) => country.code && country.code === story.countryCode)?.key
    || countries.find((country) => countryCodesByName[country.label] === story.countryCode)?.key
    || (story.country === 'Global' ? 'global' : '');

const selectedCountriesText = computed(() => {
    const count = prefs.value.countries.length;
    return count === countries.length ? `${count} Selected` : `${count} Countries`;
});

const selectedCategoriesText = computed(() => {
    const count = prefs.value.categories.length;
    return count === categories.length ? 'All categories' : `${count} Categories`;
});

const countryOptions = computed(() => countries.filter((country) => {
    if (adminForm.value.region === 'Global') return country.region === 'Global';
    return country.region === adminForm.value.region;
}));

const feedStories = computed(() => {
    const term = search.value.trim().toLowerCase();
    let list = stories.value.filter((story) => {
        const selectedCountry = countryKeyFor(story);
        const sourceType = story.sourceType || 'internal';
        const matchesRegion = prefs.value.regions.includes(story.region);
        const matchesCountry = !selectedCountry || prefs.value.countries.includes(selectedCountry);
        const matchesCategory = prefs.value.categories.includes(story.category) || !categories.includes(story.category) || story.breaking;
        const matchesSource = (sourceType === 'rss' && prefs.value.feeds) || (sourceType !== 'rss' && prefs.value.internal);
        const matchesSaved = !savedOnly.value || bookmarks.value.has(story.id);
        const matchesQuick = quickCategory.value === 'all' || story.category === quickCategory.value;
        const haystack = [story.title, story.summary, story.body, story.country, story.category, story.region, story.author].join(' ').toLowerCase();
        const matchesSearch = !term || haystack.includes(term);
        return matchesRegion && matchesCountry && matchesCategory && matchesSource && matchesSaved && matchesQuick && matchesSearch;
    });

    if (prefs.value.personalization === 'Relaxed' && !list.length) {
        list = stories.value.filter((story) => !savedOnly.value || bookmarks.value.has(story.id));
    }

    return list.slice().sort((a, b) => sortMode.value === 'trending' ? b.fire - a.fire || b.views - a.views : a.hours - b.hours);
});

const hero = computed(() => feedStories.value[0] || stories.value[0]);
const breakingStories = computed(() => stories.value.filter((story) => story.breaking));
const tickerStories = computed(() => breakingStories.value.concat(breakingStories.value));
const trendingStories = computed(() => stories.value.slice().sort((a, b) => b.fire - a.fire || b.views - a.views).slice(0, 4));
const topStories = computed(() => feedStories.value.slice(0, 8));
const caribbeanStories = computed(() => feedStories.value.filter((story) => story.region === 'Caribbean').slice(0, 4));
const latamStories = computed(() => feedStories.value.filter((story) => story.region === 'Latin America').slice(0, 4));
const globalStories = computed(() => feedStories.value.filter((story) => story.region === 'Global').slice(0, 4));
const relatedStories = computed(() => {
    if (!selectedStory.value) return [];
    return stories.value
        .filter((story) => story.id !== selectedStory.value?.id && (story.category === selectedStory.value?.category || story.region === selectedStory.value?.region))
        .slice(0, 3);
});

const adForStory = (story?: Story | null) => {
    if (!story) return sponsorAds.find((ad) => ad.status === 'Active') || null;
    const active = sponsorAds.filter((ad) => ad.status === 'Active');
    return active.find((ad) => ad.targetType === 'story' && ad.target === story.id)
        || active.find((ad) => ad.targetType === 'category' && ad.target === story.category)
        || active.find((ad) => ad.targetType === 'region' && ad.target === story.region)
        || active.find((ad) => ad.targetType === 'all')
        || active[0]
        || null;
};

const featuredAd = computed(() => adForStory(hero.value));

const toggleBookmark = async (id: string) => {
    const next = new Set(bookmarks.value);
    const isSaving = !next.has(id);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    bookmarks.value = next;

    if (!isSaving) return;

    try {
        const response = await axios.post(route('admin.news.trending', id));
        if (response.data?.success) {
            const story = stories.value.find((item) => item.id === id);
            if (story) {
                story.fire = Number(response.data.trending || story.fire);
                story.views = story.fire;
            }
        }
    } catch (error) {
        console.error('Failed to update news trending count.', error);
    }
};

const openPrefs = () => {
    draftPrefs.value = structuredClone(prefs.value);
    showPrefs.value = true;
};

const savePrefs = () => {
    prefs.value = structuredClone(draftPrefs.value);
    showPrefs.value = false;
    toast.success('Feed preferences saved.');
};

const toggleDraftArray = <T extends string>(key: 'regions' | 'countries' | 'categories', value: T) => {
    const values = draftPrefs.value[key] as T[];
    draftPrefs.value = {
        ...draftPrefs.value,
        [key]: values.includes(value) ? values.filter((item) => item !== value) : [...values, value],
    };
};

const selectCountryGroup = (group: Country['group']) => {
    const groupKeys = countries.filter((country) => country.group === group).map((country) => country.key);
    draftPrefs.value.countries = Array.from(new Set([...draftPrefs.value.countries, ...groupKeys]));
};

const selectAllCountries = () => {
    draftPrefs.value.countries = [...allCountryKeys];
};

const clearCountries = () => {
    draftPrefs.value.countries = [];
};

const selectCats = (selectAll: boolean) => {
    draftPrefs.value.categories = selectAll ? [...categories] : [];
};

const resetSearch = () => {
    search.value = '';
    quickCategory.value = 'all';
    savedOnly.value = false;
};

const openStory = (story: Story) => {
    selectedStory.value = story;
    story.views += 1;
};

const resetFileInputs = () => {
    if (imageInputRef.value) imageInputRef.value.value = '';
    if (videoInputRef.value) videoInputRef.value.value = '';
    if (audioInputRef.value) audioInputRef.value.value = '';
};

const openAdminCreate = () => {
    editingStory.value = null;
    clearAdminForm();
    showAdmin.value = true;
};

const editStory = (story?: Story | null) => {
    if (!story) {
        openAdminCreate();
        return;
    }

    selectedStory.value = null;
    editingStory.value = story;
    adminForm.value = {
        headline: story.title,
        summary: story.summary,
        body: story.body,
        region: story.region,
        country: story.country,
        category: story.category,
        breaking: story.breaking,
        author: story.author,
        source: story.source,
        imageFile: null,
        videoFile: null,
        audioFile: null,
    };
    resetFileInputs();
    showAdmin.value = true;
};

const shareStory = async (story: Story) => {
    const link = route('admin.news.show', story.id);
    try {
        await navigator.clipboard?.writeText(link);
        toast.success('Story link copied.');
    } catch {
        toast.info(link);
    }
};

const onAdminFile = (field: 'imageFile' | 'videoFile' | 'audioFile', event: Event) => {
    const target = event.target as HTMLInputElement;
    adminForm.value[field] = target.files?.[0] ?? null;
};

const clearAdminForm = () => {
    editingStory.value = null;
    adminForm.value = {
        headline: '',
        summary: '',
        body: '',
        region: 'Caribbean',
        country: 'Bahamas',
        category: 'Events',
        breaking: false,
        author: 'Caribbean Desk',
        source: 'LinkUp Caribbean 360 Desk',
        imageFile: null,
        videoFile: null,
        audioFile: null,
    };
    resetFileInputs();
};

const publishStory = () => {
    const headline = adminForm.value.headline.trim();
    if (!headline) {
        toast.error('Headline is required.');
        return;
    }

    const country = countries.find((item) => item.label === adminForm.value.country);
    const mediaFiles = [
        adminForm.value.imageFile ? { type: 'image', file: adminForm.value.imageFile } : null,
        adminForm.value.videoFile ? { type: 'video', file: adminForm.value.videoFile } : null,
        adminForm.value.audioFile ? { type: 'audio', file: adminForm.value.audioFile } : null,
    ].filter((item): item is { type: 'image' | 'video' | 'audio'; file: File } => Boolean(item));
    const existingMedia = (editingStory.value?.media || []).map((item) => ({
        type: item.type || 'image',
        name: item.name || 'Existing media',
        dataUrl: item.dataUrl || item.url,
        size: item.size || 0,
        isExisting: true,
    })).filter((item) => Boolean(item.dataUrl));

    if (editingStory.value && !existingMedia.some((item) => item.type === 'image') && editingStory.value.img && editingStory.value.img !== DefaultImage) {
        existingMedia.unshift({
            type: 'image',
            name: 'Current cover image',
            dataUrl: editingStory.value.img,
            size: 0,
            isExisting: true,
        });
    }

    if (!mediaFiles.length && !existingMedia.length) {
        toast.error('Please attach at least one image, video, or audio file.');
        return;
    }
    const isEditing = Boolean(editingStory.value);
    const wasBreaking = adminForm.value.breaking;
    const summary = adminForm.value.summary.trim() || 'New Caribbean 360 story summary.';
    const body = adminForm.value.body.trim() || summary || 'Story body will be completed by the Caribbean 360 desk.';
    const form = useForm({
        title: headline,
        summary,
        body,
        country: adminForm.value.country,
        countryCode: country?.code || countryCodesByName[adminForm.value.country] || country?.key || 'GLB',
        region: adminForm.value.region,
        category: adminForm.value.category,
        sourceName: adminForm.value.source || adminForm.value.author || 'LinkUp Caribbean 360 Desk',
        sourceType: 'internal',
        isBreaking: wasBreaking,
        breakingExpiresAt: wasBreaking ? new Date(Date.now() + 8 * 60 * 60 * 1000).toISOString() : null,
        trending: isEditing ? editingStory.value?.fire || 0 : wasBreaking ? 5 : 0,
        stay_on_page: true,
        news_media: mediaFiles.map((item) => item.file),
        media_info: JSON.stringify(existingMedia.concat(mediaFiles.map((item) => ({
            type: item.type,
            name: item.file.name,
            size: item.file.size,
            isExisting: false,
        })))),
        _method: isEditing ? 'put' : undefined,
    });

    form.post(isEditing ? route('admin.news.update', editingStory.value?.id) : route('admin.news.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            clearAdminForm();
            showAdmin.value = false;
            router.reload({ only: ['news', 'message'] });
            toast.success(isEditing ? 'Story updated.' : `Story published to the reader${wasBreaking ? ' (Breaking).' : '.'}`);
        },
        onError: () => {
            toast.error(`Story could not be ${isEditing ? 'updated' : 'published'}. Check the form fields and try again.`);
        },
    });
};

const hydrate = () => {
    if (typeof window === 'undefined') return;
    try {
        const savedPrefs = JSON.parse(window.localStorage.getItem(STORAGE_PREFS) || 'null');
        if (savedPrefs?.regions && savedPrefs?.countries && savedPrefs?.categories) {
            prefs.value = { ...defaultPrefs, ...savedPrefs };
        }
    } catch {}

    try {
        const savedBookmarks = JSON.parse(window.localStorage.getItem(STORAGE_BOOKMARKS) || '[]');
        if (Array.isArray(savedBookmarks)) bookmarks.value = new Set(savedBookmarks);
    } catch {}
};

watch(prefs, () => {
    if (typeof window !== 'undefined') window.localStorage.setItem(STORAGE_PREFS, JSON.stringify(prefs.value));
}, { deep: true });

watch(bookmarks, () => {
    if (typeof window !== 'undefined') window.localStorage.setItem(STORAGE_BOOKMARKS, JSON.stringify([...bookmarks.value]));
}, { deep: true });

watch(() => adminForm.value.region, () => {
    const first = countryOptions.value[0];
    if (first && !countryOptions.value.some((country) => country.label === adminForm.value.country)) {
        adminForm.value.country = first.label;
    }
});

onMounted(hydrate);
</script>

<template>
    <div class="news-reader overflow-hidden rounded-3xl bg-slate-50" :class="{ 'news-reader--dark': darkMode }">
        <Toaster rich-colors position="top-right" />

        <!-- Reader header -->
        <div class="sticky top-0 z-20 flex flex-col gap-4 border-b border-slate-100 bg-white/90 px-5 py-4 backdrop-blur xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-500 text-lg font-black text-white shadow-lg shadow-sky-200">360</div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900">Caribbean 360 News</h3>
                    <p class="text-sm font-bold text-slate-500">Caribbean + Latin America • Choose your countries • See what matters</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    class="flex items-center gap-2 rounded-2xl px-4 py-2.5 font-black"
                    :class="savedOnly ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-700'"
                    @click="savedOnly = !savedOnly"
                >
                    <Bookmark class="h-4 w-4" /> Saved
                </button>
                <button title="Toggle dark mode" class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-100 text-slate-700" @click="darkMode = !darkMode">
                    <Moon class="h-4 w-4" />
                </button>
                <button class="flex items-center gap-2 rounded-2xl bg-emerald-50 px-4 py-2.5 font-black text-emerald-700" @click="openAdminCreate">
                    <Shield class="h-4 w-4" /> Admin
                </button>
                <button class="flex items-center gap-2 rounded-2xl bg-sky-500 px-4 py-2.5 font-black text-white shadow-lg shadow-sky-200" @click="openPrefs">
                    <SlidersHorizontal class="h-4 w-4" /> Preferences
                </button>
            </div>
        </div>

        <!-- Breaking ticker -->
        <div v-if="breakingStories.length" class="flex items-stretch overflow-hidden bg-rose-600 text-white">
            <span class="flex shrink-0 items-center gap-2 bg-rose-700 px-4 py-2 text-sm font-black">
                <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span> BREAKING
            </span>
            <div class="relative flex-1 overflow-hidden">
                <div class="ticker-track flex gap-10 py-2 text-sm font-bold">
                    <span v-for="(story, idx) in tickerStories" :key="`${story.id}-${idx}`" class="whitespace-nowrap">{{ story.flag }} {{ story.title }}</span>
                </div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-white px-5 py-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-wrap gap-2">
                <span class="flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-black"><Globe class="h-4 w-4" /> {{ prefs.regions.join(' + ') || 'No regions' }}</span>
                <span class="flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-black">🚩 {{ selectedCountriesText }}</span>
                <span class="flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-black"><Tag class="h-4 w-4" /> {{ selectedCategoriesText }}</span>
                <span class="flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-black"><Sparkles class="h-4 w-4" /> {{ prefs.personalization.replace(' (recommended)', '') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3">
                    <Search class="h-4 w-4 text-slate-400" />
                    <input v-model="search" class="w-full py-2.5 outline-none xl:w-72" placeholder="Search headlines, keywords..." />
                </div>
                <button class="grid h-11 w-11 place-items-center rounded-2xl border border-slate-200 text-slate-500" @click="resetSearch"><RotateCcw class="h-4 w-4" /></button>
            </div>
        </div>

        <div class="space-y-8 p-5">
            <!-- Top story + trending -->
            <div class="grid grid-cols-1 gap-5 2xl:grid-cols-3">
                <div class="2xl:col-span-2">
                    <div v-if="hero" class="rounded-3xl bg-white p-5 shadow-sm">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="group relative h-80 cursor-pointer overflow-hidden rounded-3xl" @click="openStory(hero)">
                                <div
                                    class="absolute inset-0 bg-slate-200 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                                    :style="{ backgroundImage: `url('${hero.img}')` }"
                                />
                                <div class="absolute inset-0 bg-linear-to-t from-black/85 via-black/30 to-transparent"></div>
                                <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                    <span class="flex items-center gap-1 rounded-full bg-amber-400 px-3 py-1 text-xs font-black text-amber-950"><Star class="h-3 w-3" /> Top Story</span>
                                    <span v-if="hero.breaking" class="flex items-center gap-1 rounded-full bg-rose-500 px-3 py-1 text-xs font-black text-white">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Breaking
                                    </span>
                                </div>
                                <div class="absolute right-0 bottom-0 left-0 p-5 text-white">
                                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-black backdrop-blur">
                                        {{ hero.region === 'Global' ? '🌐 Global' : `${hero.flag} ${hero.country}` }} • {{ hero.category }}
                                    </span>
                                    <h3 class="mt-2 text-2xl leading-tight font-black">{{ hero.title }}</h3>
                                    <p class="mt-1 line-clamp-2 text-sm text-white/80">{{ hero.summary }}</p>
                                    <div class="mt-3 flex items-center gap-3 text-xs font-bold text-white/70">
                                        <span class="flex items-center gap-1"><BookOpen class="h-3.5 w-3.5" /> {{ readTime(hero) }}</span>
                                        <span class="flex items-center gap-1"><Eye class="h-3.5 w-3.5" /> {{ num(hero.views) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3">
                                <div class="flex justify-end gap-2">
                                    <button class="grid h-10 w-10 place-items-center rounded-2xl border border-slate-200 text-slate-500" @click="editStory(hero)"><Pencil class="h-4 w-4" /></button>
                                    <button class="flex items-center gap-2 rounded-2xl bg-sky-500 px-5 py-2.5 font-black text-white" @click="openStory(hero)"><BookOpen class="h-4 w-4" /> Read Story</button>
                                </div>
                                <div v-if="featuredAd" class="flex flex-1 flex-col rounded-3xl border border-slate-100 p-5">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-lg font-black">Featured Ad</h4>
                                        <span class="flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1 text-xs font-black text-rose-600">Sponsored</span>
                                    </div>
                                    <h5 class="mt-3 font-black">{{ featuredAd.title }}</h5>
                                    <p class="mt-1 flex-1 text-sm text-slate-500">{{ featuredAd.body }}</p>
                                    <p class="mt-2 text-sm font-bold text-slate-400">by {{ featuredAd.sponsor }}</p>
                                    <div class="mt-3">
                                        <img v-if="featuredAd.format === 'image'" :src="featuredAd.media" class="h-28 w-full rounded-2xl bg-slate-100 object-cover" alt="" />
                                        <video v-else-if="featuredAd.format === 'video'" :src="featuredAd.media" class="h-28 w-full rounded-2xl bg-slate-900 object-cover" controls preload="none" />
                                        <audio v-else :src="featuredAd.media" class="w-full" controls preload="none" />
                                    </div>
                                    <button class="mt-3 rounded-2xl bg-sky-500 px-5 py-3 font-black text-white">{{ featuredAd.cta }}</button>
                                </div>
                                <div v-else class="grid flex-1 place-items-center rounded-3xl border border-slate-100 p-5 font-black text-slate-400">No sponsor</div>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-slate-400">{{ hero.body.slice(0, 180) }}...</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h4 class="text-lg font-black">Trending Now</h4>
                        <button class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-500" @click="sortMode = 'trending'"><Flame class="h-4 w-4" /></button>
                    </div>
                    <div class="space-y-3">
                        <div v-for="story in trendingStories" :key="story.id" class="rounded-2xl border border-slate-100 p-4">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">{{ story.region === 'Global' ? '🌐 Global' : `${story.flag} ${story.country}` }}</span>
                                <div class="flex gap-1">
                                    <button class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 text-slate-500" @click="editStory(story)"><Pencil class="h-4 w-4" /></button>
                                    <button
                                        class="grid h-8 w-8 place-items-center rounded-full border border-slate-200"
                                        :class="bookmarks.has(story.id) ? 'text-sky-600' : 'text-slate-500'"
                                        @click="toggleBookmark(story.id)"
                                    >
                                        <BookmarkCheck v-if="bookmarks.has(story.id)" class="h-4 w-4" /><Bookmark v-else class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            <button class="mt-2 block w-full text-left leading-snug font-black text-slate-900" @click="openStory(story)">
                                {{ story.title.length > 46 ? `${story.title.slice(0, 46)}…` : story.title }}
                            </button>
                            <div class="mt-2 flex items-center gap-3 text-xs font-bold text-slate-400">
                                <span class="flex items-center gap-1"><Flame class="h-3.5 w-3.5" /> {{ story.fire }}</span>
                                <span class="flex items-center gap-1"><Clock class="h-3.5 w-3.5" /> {{ rel(story.hours) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-3xl border border-slate-100 p-5 text-center">
                        <p class="text-xs font-black tracking-widest text-slate-400">ADVERTISEMENT</p>
                        <div class="my-3 grid h-40 place-items-center rounded-2xl bg-sky-50 font-black text-slate-400">Ad Space</div>
                        <p class="text-sm font-bold text-slate-500">Support independent journalism.</p>
                    </div>
                </div>
            </div>

            <!-- Quick filter tabs -->
            <div class="flex flex-wrap gap-2">
                <button
                    class="rounded-full px-4 py-2 text-sm font-black"
                    :class="quickCategory === 'all' ? 'bg-sky-500 text-white shadow-lg shadow-sky-200' : 'border border-slate-200 bg-white text-slate-600'"
                    @click="quickCategory = 'all'"
                >
                    All
                </button>
                <button
                    v-for="category in categories"
                    :key="category"
                    class="rounded-full px-4 py-2 text-sm font-black"
                    :class="quickCategory === category ? 'bg-sky-500 text-white shadow-lg shadow-sky-200' : 'border border-slate-200 bg-white text-slate-600'"
                    @click="quickCategory = category"
                >
                    {{ category }}
                </button>
            </div>

            <!-- Top Stories for You -->
            <div>
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-black">{{ savedOnly ? 'Saved Stories' : quickCategory !== 'all' ? quickCategory : 'Top Stories for You' }}</h4>
                        <p class="text-sm font-bold text-slate-500">Based on your selected countries + categories</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="flex items-center gap-2 rounded-2xl px-4 py-2 font-black"
                            :class="sortMode === 'newest' ? 'bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-white'"
                            @click="sortMode = 'newest'"
                        >
                            <Clock class="h-4 w-4" /> Newest
                        </button>
                        <button
                            class="flex items-center gap-2 rounded-2xl px-4 py-2 font-black"
                            :class="sortMode === 'trending' ? 'bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-white'"
                            @click="sortMode = 'trending'"
                        >
                            <Flame class="h-4 w-4" /> Trending
                        </button>
                    </div>
                </div>
                <div v-if="topStories.length" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <NewsStoryCard
                        v-for="story in topStories"
                        :key="story.id"
                        :story="story"
                        :ad="adForStory(story)"
                        :bookmarked="bookmarks.has(story.id)"
                        @open="openStory(story)"
                        @edit="editStory(story)"
                        @toggle-bookmark="toggleBookmark(story.id)"
                    />
                </div>
                <p v-else class="py-6 font-bold text-slate-400">No stories match your filters.</p>
            </div>

            <!-- Caribbean Wide -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-black">Caribbean Wide</h4>
                        <p class="text-sm font-bold text-slate-500">Big stories across the region</p>
                    </div>
                    <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-black">🗺️ Caribbean</span>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <NewsStoryCard
                        v-for="story in caribbeanStories"
                        :key="story.id"
                        :story="story"
                        :ad="adForStory(story)"
                        :bookmarked="bookmarks.has(story.id)"
                        @open="openStory(story)"
                        @edit="editStory(story)"
                        @toggle-bookmark="toggleBookmark(story.id)"
                    />
                </div>
            </div>

            <!-- Latin America -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-black">Latin America</h4>
                        <p class="text-sm font-bold text-slate-500">A broader view beyond the islands</p>
                    </div>
                    <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-black">📍 Latin America</span>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <NewsStoryCard
                        v-for="story in latamStories"
                        :key="story.id"
                        :story="story"
                        :ad="adForStory(story)"
                        :bookmarked="bookmarks.has(story.id)"
                        @open="openStory(story)"
                        @edit="editStory(story)"
                        @toggle-bookmark="toggleBookmark(story.id)"
                    />
                </div>
            </div>

            <!-- Global 360 -->
            <div v-if="prefs.regions.includes('Global')">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-black">Global 360</h4>
                        <p class="text-sm font-bold text-slate-500">Only shows if you enabled Global</p>
                    </div>
                    <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-black"><Globe2 class="h-4 w-4" /> Global</span>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <NewsStoryCard
                        v-for="story in globalStories"
                        :key="story.id"
                        :story="story"
                        :ad="adForStory(story)"
                        :bookmarked="bookmarks.has(story.id)"
                        @open="openStory(story)"
                        @edit="editStory(story)"
                        @toggle-bookmark="toggleBookmark(story.id)"
                    />
                </div>
            </div>
        </div>

        <!-- Preferences modal -->
        <div v-if="showPrefs" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3">
            <div class="my-4 w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">Feed Preferences</h3>
                        <p class="text-sm text-sky-100">Choose countries, categories, and sources</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 font-black text-sky-600" @click="savePrefs"><Save class="h-4 w-4" /> Save</button>
                        <button class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20" @click="showPrefs = false"><X class="h-6 w-6" /></button>
                    </div>
                </div>
                <div class="grid max-h-[80vh] grid-cols-1 gap-5 overflow-y-auto p-6 lg:grid-cols-3">
                    <div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xl font-black">Regions</h4>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">Scope</span>
                        </div>
                        <label v-for="region in (['Caribbean', 'Latin America', 'Global'] as Region[])" :key="region" class="flex items-center justify-between rounded-2xl p-1">
                            <span class="font-black">{{ region === 'Global' ? 'Global 360' : region }}</span>
                            <input type="checkbox" class="h-5 w-5 accent-sky-500" :checked="draftPrefs.regions.includes(region)" @change="toggleDraftArray('regions', region)" />
                        </label>
                        <div class="rounded-2xl bg-sky-50 p-4">
                            <h5 class="font-black">Personalization Mode</h5>
                            <p class="text-sm text-slate-500">Choose how strict filtering should be.</p>
                            <select v-model="draftPrefs.personalization" class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option>Personalized (recommended)</option>
                                <option>Strict</option>
                                <option>Relaxed</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xl font-black">Countries</h4>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">Choose</span>
                        </div>
                        <p class="text-sm font-bold text-slate-500">Select what you want to see.</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button class="flex items-center justify-center gap-1 rounded-2xl bg-emerald-50 px-3 py-2.5 font-black text-emerald-700" @click="selectCountryGroup('caribbean')"><CheckCheck class="h-4 w-4" /> Carib.</button>
                            <button class="flex items-center justify-center gap-1 rounded-2xl bg-emerald-50 px-3 py-2.5 font-black text-emerald-700" @click="selectCountryGroup('latam')"><CheckCheck class="h-4 w-4" /> LatAm.</button>
                            <button class="flex items-center justify-center gap-1 rounded-2xl bg-emerald-50 px-3 py-2.5 font-black text-emerald-700" @click="selectCountryGroup('us')"><CheckCheck class="h-4 w-4" /> US</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="flex items-center justify-center gap-2 rounded-2xl bg-sky-50 px-3 py-2.5 font-black text-sky-700" @click="selectAllCountries"><CheckCheck class="h-4 w-4" /> Select All</button>
                            <button class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-3 py-2.5 font-black" @click="clearCountries"><Eraser class="h-4 w-4" /> Clear</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="country in countries" :key="country.key" class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5">
                                <span class="flex items-center gap-1 text-sm font-bold">{{ country.flag }} {{ country.label }}</span>
                                <input type="checkbox" class="h-4 w-4 accent-sky-500" :checked="draftPrefs.countries.includes(country.key)" @change="toggleDraftArray('countries', country.key)" />
                            </label>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-3 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xl font-black">Categories</h4>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">Filter</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-50 px-3 py-2.5 font-black text-emerald-700" @click="selectCats(true)"><CheckCheck class="h-4 w-4" /> Select All</button>
                                <button class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-3 py-2.5 font-black" @click="selectCats(false)"><Eraser class="h-4 w-4" /> Clear</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label v-for="category in categories" :key="category" class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5">
                                    <span class="text-sm font-black">{{ category }}</span>
                                    <input type="checkbox" class="h-4 w-4 accent-sky-500" :checked="draftPrefs.categories.includes(category)" @change="toggleDraftArray('categories', category)" />
                                </label>
                            </div>
                        </div>
                        <div class="space-y-3 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xl font-black">Source Types</h4>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">Inputs</span>
                            </div>
                            <label class="flex items-center justify-between rounded-2xl p-2">
                                <span class="flex items-center gap-2 font-bold"><Radio class="h-4 w-4 text-slate-400" /> RSS Feeds</span>
                                <input v-model="draftPrefs.feeds" type="checkbox" class="h-5 w-5 accent-sky-500" />
                            </label>
                            <label class="flex items-center justify-between rounded-2xl p-2">
                                <span class="flex items-center gap-2 font-bold"><FileText class="h-4 w-4 text-slate-400" /> Internal Uploads</span>
                                <input v-model="draftPrefs.internal" type="checkbox" class="h-5 w-5 accent-sky-500" />
                            </label>
                            <label class="flex items-center justify-between rounded-2xl p-2 opacity-50">
                                <span class="flex items-center gap-2 font-bold"><Users class="h-4 w-4 text-slate-400" /> Community (Phase 2)</span>
                                <input type="checkbox" disabled class="h-5 w-5" />
                            </label>
                            <div class="rounded-2xl bg-rose-50 p-4">
                                <div class="flex items-center justify-between">
                                    <h5 class="font-black">Breaking News Strip</h5>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-black">Always On</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">Breaking stories show regardless of category filters.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Upload modal -->
        <div v-if="showAdmin" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3">
            <div class="my-4 w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">{{ editingStory ? 'Edit Story' : 'Admin Upload' }}</h3>
                        <p class="text-sm text-sky-100">{{ editingStory ? 'Update this story without leaving the reader' : 'Create internal stories + mark Breaking + add media' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 font-black text-sky-600" @click="publishStory"><Send class="h-4 w-4" /> {{ editingStory ? 'Update' : 'Publish' }}</button>
                        <button class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20" @click="showAdmin = false"><X class="h-6 w-6" /></button>
                    </div>
                </div>
                <div class="grid max-h-[80vh] grid-cols-1 gap-5 overflow-y-auto p-6 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <label class="block">
                                <span class="text-sm font-black text-slate-600">Headline</span>
                                <input v-model="adminForm.headline" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="Write a strong headline..." />
                            </label>
                            <label class="block">
                                <span class="text-sm font-black text-slate-600">Summary (2–3 lines)</span>
                                <textarea v-model="adminForm.summary" rows="4" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Short summary for the cards..."></textarea>
                            </label>
                        </div>
                        <div class="rounded-3xl bg-sky-50 p-5">
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="flex items-center gap-2 font-black"><Paperclip class="h-4 w-4" /> Attach Media</h4>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black">Stored in news media</span>
                            </div>
                            <p v-if="editingStory" class="mb-3 text-sm font-bold text-slate-500">Leave media empty to keep the existing cover/media.</p>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="block">
                                    <span class="mb-1 block text-xs font-black text-slate-600">Images</span>
                                    <input ref="imageInputRef" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 text-xs" @change="onAdminFile('imageFile', $event)" />
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-xs font-black text-slate-600">Videos</span>
                                    <input ref="videoInputRef" type="file" accept="video/*" class="w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 text-xs" @change="onAdminFile('videoFile', $event)" />
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-xs font-black text-slate-600">Audio</span>
                                    <input ref="audioInputRef" type="file" accept="audio/*" class="w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 text-xs" @change="onAdminFile('audioFile', $event)" />
                                </label>
                            </div>
                        </div>
                        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <label class="block">
                                <span class="text-sm font-black text-slate-600">Full Story (Optional - uses template if empty)</span>
                                <textarea v-model="adminForm.body" rows="6" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Write the full story."></textarea>
                            </label>
                        </div>
                    </div>
                    <div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Region</span>
                            <select v-model="adminForm.region" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option>Caribbean</option>
                                <option>Latin America</option>
                                <option>Global</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Country</span>
                            <select v-model="adminForm.country" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option v-for="country in countryOptions" :key="country.key">{{ country.label }}</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Category</span>
                            <select v-model="adminForm.category" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option v-for="category in categories" :key="category">{{ category }}</option>
                            </select>
                        </label>
                        <div class="flex items-start justify-between gap-3 rounded-2xl bg-rose-50 p-4">
                            <div>
                                <h5 class="font-black">Mark as Breaking</h5>
                                <p class="mt-1 text-sm text-slate-500">Breaking appears in the top strip and shows regardless of filters.</p>
                            </div>
                            <input v-model="adminForm.breaking" type="checkbox" class="mt-1 h-6 w-6 accent-rose-500" />
                        </div>
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Author</span>
                            <select v-model="adminForm.author" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                <option v-for="author in authors" :key="author">{{ author }}</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Source Name</span>
                            <input v-model="adminForm.source" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" />
                        </label>
                        <button class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 font-black" @click="clearAdminForm"><Eraser class="h-4 w-4" /> Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Story reader modal -->
        <div v-if="selectedStory" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 p-3">
            <div class="my-4 w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="relative">
                    <div class="h-64 bg-slate-200 bg-cover bg-center" :style="{ backgroundImage: `url('${selectedStory.img}')` }" />
                    <button class="absolute top-4 right-4 grid h-11 w-11 place-items-center rounded-2xl bg-white/90 shadow" @click="selectedStory = null"><X class="h-6 w-6" /></button>
                    <div v-if="selectedStory.breaking" class="absolute top-4 left-4 flex items-center gap-1 rounded-full bg-rose-500 px-3 py-1 text-xs font-black text-white">
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Breaking
                    </div>
                </div>
                <div class="max-h-[64vh] overflow-y-auto p-7">
                    <div class="mb-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">{{ selectedStory.region === 'Global' ? '🌐 Global' : `${selectedStory.flag} ${selectedStory.country}` }}</span>
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-black text-sky-700">{{ selectedStory.category }}</span>
                    </div>
                    <h2 class="text-3xl leading-tight font-black text-slate-900">{{ selectedStory.title }}</h2>
                    <div class="mt-3 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="grid h-11 w-11 place-items-center rounded-full bg-sky-500 font-black text-white">{{ selectedStory.author.slice(0, 1) }}</div>
                        <div>
                            <p class="font-black text-slate-800">{{ selectedStory.author }}</p>
                            <p class="text-xs font-bold text-slate-400">{{ selectedStory.source }} • {{ rel(selectedStory.hours) }} • {{ readTime(selectedStory) }}</p>
                        </div>
                    </div>

                    <div v-if="selectedStory.mediaVideo || selectedStory.mediaAudio" class="mt-4 space-y-3">
                        <video v-if="selectedStory.mediaVideo" :src="selectedStory.mediaVideo" class="w-full rounded-2xl" controls />
                        <audio v-if="selectedStory.mediaAudio" :src="selectedStory.mediaAudio" class="w-full" controls />
                    </div>

                    <p class="mt-4 leading-relaxed whitespace-pre-line text-slate-600">{{ selectedStory.body }}</p>

                    <div v-if="adForStory(selectedStory)" class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-[11px] font-black tracking-widest text-slate-400">SPONSORED</p>
                        <img v-if="adForStory(selectedStory)?.format === 'image'" :src="adForStory(selectedStory)?.media" class="mt-2 h-40 w-full rounded-2xl bg-slate-100 object-cover" alt="" />
                        <video v-else-if="adForStory(selectedStory)?.format === 'video'" :src="adForStory(selectedStory)?.media" class="mt-2 h-40 w-full rounded-2xl bg-slate-900 object-cover" controls preload="none" />
                        <audio v-else :src="adForStory(selectedStory)?.media" class="mt-2 w-full" controls preload="none" />
                        <h4 class="mt-3 font-black">{{ adForStory(selectedStory)?.title }}</h4>
                        <span class="block text-sm text-slate-500">{{ adForStory(selectedStory)?.body }}</span>
                        <button class="mt-3 rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white">{{ adForStory(selectedStory)?.cta }}</button>
                    </div>

                    <div v-if="relatedStories.length" class="mt-6">
                        <h4 class="mb-2 font-black text-slate-800">Related Stories</h4>
                        <button
                            v-for="story in relatedStories"
                            :key="story.id"
                            class="mb-2 flex w-full items-center gap-3 rounded-2xl bg-slate-50 p-2 text-left hover:bg-slate-100"
                            @click="openStory(story)"
                        >
                            <div class="h-12 w-16 shrink-0 rounded-xl bg-cover bg-center" :style="{ backgroundImage: `url('${story.img}')` }" />
                            <div>
                                <p class="leading-snug font-bold text-slate-800">{{ story.title.length > 52 ? `${story.title.slice(0, 52)}…` : story.title }}</p>
                                <p class="text-xs text-slate-400">{{ story.category }} • {{ readTime(story) }}</p>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 p-4">
                    <span class="text-sm font-bold text-slate-400">🔥 {{ selectedStory.fire }} trending • {{ num(selectedStory.views) }} views</span>
                    <div class="flex flex-wrap gap-2">
                        <button class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2.5 font-black" @click="toggleBookmark(selectedStory.id)">
                            <BookmarkCheck v-if="bookmarks.has(selectedStory.id)" class="h-4 w-4" /><Bookmark v-else class="h-4 w-4" /> Save
                        </button>
                        <button class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2.5 font-black" @click="shareStory(selectedStory)"><Share2 class="h-4 w-4" /> Share</button>
                        <button class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2.5 font-black" @click="editStory(selectedStory)"><Pencil class="h-4 w-4" /> Edit</button>
                        <button class="rounded-2xl bg-sky-500 px-5 py-2.5 font-black text-white" @click="selectedStory = null">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ticker-track {
    width: max-content;
    animation: c360-ticker 32s linear infinite;
}

@keyframes c360-ticker {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}

.news-reader--dark {
    background-color: #020617;
}

.news-reader--dark :deep(.bg-white),
.news-reader--dark :deep(.bg-white\/90) {
    background-color: #0f172a;
    color: #e2e8f0;
}

.news-reader--dark :deep(.text-slate-900),
.news-reader--dark :deep(.text-slate-800) {
    color: #e2e8f0;
}

.news-reader--dark :deep(.text-slate-500),
.news-reader--dark :deep(.text-slate-400) {
    color: #94a3b8;
}

.news-reader--dark :deep(.border-slate-100),
.news-reader--dark :deep(.border-slate-200) {
    border-color: #1e293b;
}

.news-reader--dark :deep(.bg-slate-50),
.news-reader--dark :deep(.bg-slate-100) {
    background-color: #1e293b;
    color: #e2e8f0;
}
</style>
