<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import Header from './components/Header.vue';
import PreferencesModal from './components/PreferencesModal.vue';
import AdminUpload from './components/AdminUpload.vue';
import FeaturedRow from './components/FeaturedRow.vue';
import Sections from './components/Sections.vue';
import ArticleView from './components/ArticleView.vue';

const LS_KEY = "linkup_c360_prefs_v1";
const LS_SAVED = "linkup_c360_saved_v1";

interface Prefs {
    regions: { caribbean: boolean; latam: boolean; global: boolean };
    mode: string;
    countries: string[];
    categories: string[];
    sources: { rss: boolean; internal: boolean; community: boolean };
}

const CATEGORIES = [
    "Breaking News", "Politics", "Business & Economy", "Entertainment", "Sports", "Culture", "Technology", "Faith & Society"
];

const CARIBBEAN_COUNTRIES = [
    { code: "JM", name: "Jamaica", region: "Caribbean" },
    { code: "BS", name: "Bahamas", region: "Caribbean" },
    { code: "TT", name: "Trinidad & Tobago", region: "Caribbean" },
    { code: "BB", name: "Barbados", region: "Caribbean" },
    { code: "HT", name: "Haiti", region: "Caribbean" },
    { code: "DO", name: "Dominican Republic", region: "Caribbean" },
    { code: "PR", name: "Puerto Rico", region: "Caribbean" },
    { code: "AG", name: "Antigua & Barbuda", region: "Caribbean" },
    { code: "GD", name: "Grenada", region: "Caribbean" },
    { code: "LC", name: "Saint Lucia", region: "Caribbean" },
    { code: "VC", name: "St. Vincent & the Grenadines", region: "Caribbean" },
    { code: "KN", name: "St. Kitts & Nevis", region: "Caribbean" },
    { code: "GY", name: "Guyana", region: "Caribbean" },
    { code: "SR", name: "Suriname", region: "Caribbean" }
];

const LATAM_COUNTRIES = [
    { code: "BR", name: "Brazil", region: "Latin America" },
    { code: "CO", name: "Colombia", region: "Latin America" },
    { code: "MX", name: "Mexico", region: "Latin America" },
    { code: "AR", name: "Argentina", region: "Latin America" },
    { code: "CL", name: "Chile", region: "Latin America" },
    { code: "PE", name: "Peru", region: "Latin America" },
    { code: "VE", name: "Venezuela", region: "Latin America" },
    { code: "PA", name: "Panama", region: "Latin America" },
    { code: "CR", name: "Costa Rica", region: "Latin America" }
];


const DEFAULT_PREFS: Prefs = {
    regions: { caribbean: true, latam: true, global: false },
    mode: "personalized",
    countries: [...CARIBBEAN_COUNTRIES.map(c => c.code), ...LATAM_COUNTRIES.map(c => c.code)],
    categories: [...CATEGORIES],
    sources: { rss: true, internal: true, community: false }
};

const props = defineProps<{
    news: any[];
    news_source?: any[];
}>();

const prefs = ref<Prefs>(structuredClone(DEFAULT_PREFS));
const internalStories = ref<any[]>([]);
const savedIds = ref<string[]>([]);
const searchQuery = ref('');
const sortMode = ref<'newest' | 'trending'>('newest');

const preferencesModal = ref(false);
const adminModal = ref(false);

const articleModal = ref(false);
const currentArticleIdx = ref(-1);
const articleList = ref<any[]>([]);

// Test featured ad data - cycles through different types
const testAdIndex = ref(0);
const testAds = [
    {
        id: 'test-ad-1',
        title: 'Amazing Caribbean Vacation Deal!',
        description: 'Book your dream vacation to Jamaica with exclusive discounts up to 40% off. Crystal clear waters, pristine beaches, and unforgettable experiences await.',
        type: 'image' as const,
        mediaUrl: 'https://picsum.photos/800/400?random=1',
        thumbnailUrl: undefined,
        duration: undefined,
        advertiser: 'Caribbean Tours',
        callToAction: 'Book Now',
        targetUrl: 'https://example.com/vacation',
        isActive: true
    },
    {
        id: 'test-ad-2',
        title: 'Experience Caribbean Culture',
        description: 'Watch this immersive video about the rich cultural heritage of the Caribbean islands, featuring traditional music, dance, and cuisine.',
        type: 'video' as const,
        mediaUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
        thumbnailUrl: 'https://picsum.photos/400/225?random=2',
        duration: 596,
        advertiser: 'Caribbean Cultural Center',
        callToAction: 'Watch More',
        targetUrl: 'https://example.com/culture',
        isActive: true
    },
    {
        id: 'test-ad-3',
        title: 'Relaxing Caribbean Sounds',
        description: 'Let the soothing sounds of steel drums and ocean waves transport you to paradise. Perfect for meditation, work, or just unwinding.',
        type: 'audio' as const,
        mediaUrl: 'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav',
        thumbnailUrl: undefined,
        duration: 120,
        advertiser: 'Island Audio',
        callToAction: 'Download App',
        targetUrl: 'https://example.com/audio',
        isActive: true
    }
];

const featuredAd = computed(() => {
    // Rotate through test ads every 30 seconds for testing
    const index = Math.floor(Date.now() / 30000) % testAds.length;
    return testAds[index];
});

const currentArticle = computed(() => {
    if (currentArticleIdx.value >= 0 && currentArticleIdx.value < articleList.value.length) {
        return articleList.value[currentArticleIdx.value];
    }
    return null;
});

const loadData = () => {
    try {
        const rawPrefs = localStorage.getItem(LS_KEY);
        if (rawPrefs) {
            const parsed = JSON.parse(rawPrefs);
            prefs.value = {
                ...DEFAULT_PREFS,
                ...parsed,
                regions: { ...DEFAULT_PREFS.regions, ...(parsed.regions || {}) },
                sources: { ...DEFAULT_PREFS.sources, ...(parsed.sources || {}) }
            };
        } else {
            prefs.value = structuredClone(DEFAULT_PREFS);
        }
    } catch (e) {
        console.error("Failed to load prefs", e);
        prefs.value = structuredClone(DEFAULT_PREFS);
    }

    internalStories.value = props.news || [];

    try {
        const rawSaved = localStorage.getItem(LS_SAVED);
        if (rawSaved) savedIds.value = JSON.parse(rawSaved);
        else savedIds.value = [];
    } catch (e) {
        console.error("Failed to load saved", e);
        savedIds.value = [];
    }
};

onMounted(() => {
    loadData();
});

watch(() => props.news, (newNews) => {
    internalStories.value = newNews || [];
}, { deep: true });

const allStories = computed(() => internalStories.value);

const isBreakingActive = (s: any) => {
    if (!s.isBreaking) return false;
    if (s.breakingExpiresAt && new Date(s.breakingExpiresAt) < new Date()) return false;
    return true;
};

const storyMatches = (s: any) => {
    if (s.sourceType === 'rss' && !prefs.value.sources.rss) return false;
    if (s.sourceType === 'internal' && !prefs.value.sources.internal) return false;

    if (s.region === 'Caribbean' && !prefs.value.regions.caribbean) return false;
    if (s.region === 'Latin America' && !prefs.value.regions.latam) return false;
    if (s.region === 'Global' && !prefs.value.regions.global) return false;

    const countries = prefs.value.countries || [];
    const countrySelected = countries.includes(s.countryCode) || s.countryCode === 'GLB';

    if (prefs.value.mode === 'country-first') {
        if (!countrySelected) return false;
    } else if (prefs.value.mode === 'personalized') {
        if (s.countryCode !== 'GLB' && !countries.includes(s.countryCode)) return false;
    }

    const categories = prefs.value.categories || [];
    if (!isBreakingActive(s)) {
        if (categories.length > 0 && !categories.includes(s.category)) return false;
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.trim().toLowerCase();
        const blob = `${s.title} ${s.summary} ${s.country} ${s.category}`.toLowerCase();
        if (!blob.includes(q)) return false;
    }
    return true;
};

const filteredStories = computed(() => {
    const stories = allStories.value.filter(storyMatches);
    const sorted = [...stories];
    if (sortMode.value === 'trending') {
        return sorted.sort((a, b) => (b.trending || 0) - (a.trending || 0));
    }
    return sorted.sort((a, b) => new Date(b.publishedAt).getTime() - new Date(a.publishedAt).getTime());
});

const breakingStories = computed(() => {
    return allStories.value.filter(s => {
        if (!isBreakingActive(s)) return false;
        if (s.region === 'Caribbean' && !prefs.value.regions.caribbean) return false;
        if (s.region === 'Latin America' && !prefs.value.regions.latam) return false;
        if (s.region === 'Global' && !prefs.value.regions.global) return false;
        return true;
    });
});

const topStory = computed(() => {
    const stories = filteredStories.value;
    if (!stories.length) return null;
    const breaking = stories.filter(isBreakingActive);
    return breaking.length ? breaking[0] : stories[0];
});

const trendingOffset = ref(0);
const shuffleTrending = () => {
    trendingOffset.value++;
};

const trendingStories = computed(() => {
    const trigger = trendingOffset.value;

    const pool = allStories.value
        .filter(s => {
            const reg = s.region?.toLowerCase().replace(' ', '') || '';
            const key = reg === 'latinamerica' ? 'latam' : reg;
            return (prefs.value.regions as any)[key] !== false;
        });

    if (pool.length === 0) return [];

    if (trigger > 0) {
        return [...pool]
            .sort(() => Math.random() - 0.5)
            .slice(0, 6);
    } else {
        return [...pool]
            .sort((a, b) => (b.trending || 0) - (a.trending || 0))
            .slice(0, 6);
    }
});

const savePrefs = () => {
    localStorage.setItem(LS_KEY, JSON.stringify(prefs.value));
};

const openAdminModal = () => { adminModal.value = true; }
const openPreferencesModal = () => { preferencesModal.value = true; }

import axios from 'axios';

const toggleSave = async (id: string) => {
    const idx = savedIds.value.indexOf(id);
    let isSaving = false;
    if (idx >= 0) {
        savedIds.value.splice(idx, 1);
    } else {
        savedIds.value.unshift(id);
        isSaving = true;
    }
    localStorage.setItem(LS_SAVED, JSON.stringify(savedIds.value));

    if (isSaving) {
        try {
            const response = await axios.post(route('frontend.news.trending', id));
            if (response.data.success) {
                const story = internalStories.value.find(s => s.id === id);
                if (story) story.trending = response.data.trending;
            }
        } catch (e) {
            console.error("Failed to update trending", e);
        }
    }
};

const openArticle = (id: string) => {
    const list = filteredStories.value;
    const idx = list.findIndex(s => s.id === id);
    if (idx >= 0) {
        articleList.value = list;
        currentArticleIdx.value = idx;
    } else {
        const all = allStories.value;
        const sIdx = all.findIndex(s => s.id === id);
        articleList.value = all;
        currentArticleIdx.value = sIdx >= 0 ? sIdx : 0;
    }
    articleModal.value = true;
};

const nextArticle = () => { if (currentArticleIdx.value < articleList.value.length - 1) currentArticleIdx.value++; };
const prevArticle = () => { if (currentArticleIdx.value > 0) currentArticleIdx.value--; };

const quickOneCountry = () => {
    prefs.value = {
        ...structuredClone(DEFAULT_PREFS),
        countries: ["JM"],
        categories: [...CATEGORIES]
    };
    savePrefs();
};

const onReset = () => { searchQuery.value = ''; };
const onPublished = () => { loadData(); };

</script>

<template>
    <div class="min-h-screen bg-[#f5f7fb] text-[#0f172a] font-sans pb-20">
        <Header :breaking-stories="breakingStories" :prefs="prefs" v-model:search-query="searchQuery"
            @open-prefs="openPreferencesModal" @open-admin="openAdminModal" @reset="onReset"
            @open-article="openArticle" />

        <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
            <FeaturedRow v-if="topStory" :top-story="topStory" :trending-stories="trendingStories" :prefs="prefs"
                :saved-ids="savedIds" :featured-ad="featuredAd" @open-article="openArticle" @open-prefs="openPreferencesModal"
                @quick-one-country="quickOneCountry" @toggle-save="toggleSave" @shuffle-trending="shuffleTrending" />

            <Sections :stories="filteredStories" :prefs="prefs" :saved-ids="savedIds" v-model:sort-mode="sortMode"
                @open-article="openArticle" @toggle-save="toggleSave" @open-prefs="openPreferencesModal" />
        </main>

        <ArticleView :show="articleModal" :story="currentArticle"
            :is-saved="currentArticle ? savedIds.includes(currentArticle.id) : false" :has-prev="currentArticleIdx > 0"
            :has-next="currentArticleIdx < articleList.length - 1" @close="articleModal = false"
            @toggle-save="toggleSave" @next="nextArticle" @prev="prevArticle" />

        <PreferencesModal v-if="preferencesModal" :show="preferencesModal" @close="preferencesModal = false"
            :prefs="prefs" @save="loadData" />
        <AdminUpload v-if="adminModal" :show="adminModal" @close="adminModal = false" @published="onPublished" />

        <!-- <footer class="mt-20 border-t border-slate-200 bg-white p-8 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="text-[1.1rem] font-black">Caribbean 360 News — Feed Engine</div>
                <div class="muted font-extrabold text-[.9rem]">This HTML manages internal uploads, filtering,
                    breaking
                    strip, and reading view.</div>
                <div class="flex gap-4 justify-center mt-4">
                    <button class="btn">Export JSON</button>
                    <button class="btn">Import JSON</button>
                </div>
            </div>
        </footer> -->
    </div>
</template>

<style>
/* Global styles if needed */
.gridAuto {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 12px;
}

.muted {
    color: #64748b;
}

.card {
    background: #fff;
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, .35);
    box-shadow: 0 12px 26px rgba(2, 6, 23, .08);
}

.btn {
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    display: inline-flex;
    gap: .6rem;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    transition: transform .12s ease;
    user-select: none;
}

.btn:hover {
    transform: translateY(-1px);
}

.pill {
    border-radius: 999px;
    padding: .25rem .6rem;
    font-size: .75rem;
    font-weight: 900;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
</style>