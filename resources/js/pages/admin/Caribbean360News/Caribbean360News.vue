<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Bookmark, BookmarkCheck } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import NewsAuthors from './NewsAuthors.vue';
import NewsCategories from './NewsCategories.vue';
import NewsCreateStory from './NewsCreateStory.vue';
import NewsDashboard from './NewsDashboard.vue';
import NewsFeedReader from './NewsFeedReader.vue';
import NewsList from './NewsList.vue';
import NewsRegions from './NewsRegions.vue';
import NewsSponsored from './NewsSponsored.vue';

defineProps<{
    viewId: string;
}>();

const emit = defineEmits<{
    (event: 'view-changed', id: string): void;
}>();

type Story = {
    id: string;
    title: string;
    summary: string;
    body: string;
    country: string;
    flag: string;
    category: string;
    region: 'Caribbean' | 'Latin America' | 'Global';
    hours: number;
    fire: number;
    breaking: boolean;
    status: 'Published' | 'Review' | 'Scheduled' | 'Draft';
    author: string;
    views: number;
    img: string;
};

type SponsorAd = {
    id: string;
    sponsor: string;
    title: string;
    body: string;
    format: 'image' | 'video' | 'audio';
    media: string;
    cta: string;
    package: string;
    value: number;
    target: string;
    targetType: 'story' | 'region' | 'category' | 'all';
    status: 'Active' | 'Scheduled' | 'Paused';
    impressions: number;
    clicks: number;
};

type Category = {
    key: string;
    label: string;
    stories: number;
    views: number;
};

type Author = {
    name: string;
    role: string;
    region: string;
    stories: number;
    views: number;
    status: 'Active' | 'Review';
};

const STORAGE_ADS = 'c360NewsAds';
const STORAGE_BOOKMARKS = 'c360Bookmarks';
const STORAGE_CATEGORIES = 'c360Categories';

const defaultStories: Story[] = [
    { id: 's1', title: 'Ukraine War Continues as Global Focus Shifts Elsewhere', summary: 'Despite attention shifting, the conflict continues to shape global policy and infrastructure risk.', body: 'Despite the world attention being drawn toward other crises, the war in Ukraine remains active and continues to have significant consequences.', country: 'Global', flag: '🌐', category: 'Business & Economy', region: 'Global', hours: 1054, fire: 1, breaking: false, status: 'Published', author: 'Global Desk', views: 184000, img: 'https://picsum.photos/seed/c360-s1/720/440' },
    { id: 's2', title: 'Iran War Triggers Global Energy Crisis', summary: 'Energy markets react sharply as shipping lanes and fuel supply chains face new pressure.', body: 'The escalating conflict involving Iran has triggered a severe shock to global energy markets.', country: 'Global', flag: '🌐', category: 'Business & Economy', region: 'Global', hours: 1054, fire: 5, breaking: true, status: 'Published', author: 'Energy Desk', views: 228000, img: 'https://picsum.photos/seed/c360-s2/720/440' },
    { id: 's3', title: 'Bahamas Independence Weekend Events Draw Record Crowds', summary: 'Concerts, cookouts and cultural celebrations packed the calendar across the islands.', body: 'Independence weekend events drew record crowds across The Bahamas.', country: 'Bahamas', flag: '🇧🇸', category: 'Events', region: 'Caribbean', hours: 50, fire: 3, breaking: false, status: 'Published', author: 'Caribbean Desk', views: 103500, img: 'https://picsum.photos/seed/c360-s3/720/440' },
    { id: 's4', title: 'Central Bank Signals Focus on Economic Stability', summary: 'The Central Bank of Barbados outlined measures to support local reserves and confidence.', body: 'The Central Bank of Barbados has signaled a renewed focus on economic stability.', country: 'Barbados', flag: '🇧🇧', category: 'Business & Economy', region: 'Caribbean', hours: 63, fire: 2, breaking: true, status: 'Published', author: 'Ramon Persaud', views: 44200, img: 'https://picsum.photos/seed/c360-s4/720/440' },
    { id: 's5', title: 'Transportation Challenges Highlight Infrastructure Needs', summary: 'Congestion underscores the need for major infrastructure investment in Trinidad and Tobago.', body: 'Transportation challenges across Trinidad and Tobago are highlighting the urgent need for infrastructure improvements.', country: 'Trinidad & Tobago', flag: '🇹🇹', category: 'Politics', region: 'Caribbean', hours: 63, fire: 1, breaking: false, status: 'Review', author: 'Sasha Baptiste', views: 51880, img: 'https://picsum.photos/seed/c360-s5/720/440' },
    { id: 's6', title: 'Peace Strategy Advances with Mixed Results', summary: 'Colombia continues to strengthen peace initiatives as progress remains uneven.', body: 'Colombia is continuing its efforts to strengthen peace initiatives, though progress remains uneven.', country: 'Colombia', flag: '🇨🇴', category: 'Politics', region: 'Latin America', hours: 63, fire: 2, breaking: false, status: 'Published', author: 'Lucia Mendez', views: 88600, img: 'https://picsum.photos/seed/c360-s6/720/440' },
    { id: 's7', title: 'Brazil Intensifies Amazon Protection Amid Global Pressure', summary: 'New measures aim to curb deforestation as international attention on the Amazon grows.', body: 'Brazil has intensified Amazon protection efforts amid growing global pressure.', country: 'Brazil', flag: '🇧🇷', category: 'Environment', region: 'Latin America', hours: 65, fire: 2, breaking: false, status: 'Scheduled', author: 'Andre Rolle', views: 63190, img: 'https://picsum.photos/seed/c360-s7/720/440' },
    { id: 's8', title: 'Guyana Energy Corridor Update Signals New Investment', summary: 'New investment is flowing into Guyana energy corridor as production expands.', body: 'Guyana energy corridor is signaling new investment as oil production continues to expand.', country: 'Guyana', flag: '🇬🇾', category: 'Business & Economy', region: 'Caribbean', hours: 70, fire: 1, breaking: false, status: 'Draft', author: 'Ramon Persaud', views: 39910, img: 'https://picsum.photos/seed/c360-s8/720/440' },
];

const defaultCategories: Category[] = [
    { key: 'Breaking News', label: 'Breaking News', stories: 18, views: 225000 },
    { key: 'Politics', label: 'Politics', stories: 34, views: 198000 },
    { key: 'Business & Economy', label: 'Business', stories: 39, views: 201000 },
    { key: 'Entertainment', label: 'Entertainment', stories: 47, views: 292000 },
    { key: 'Sports', label: 'Sports', stories: 24, views: 118000 },
    { key: 'Culture', label: 'Culture', stories: 28, views: 144000 },
    { key: 'Technology', label: 'Technology', stories: 21, views: 132000 },
    { key: 'Events', label: 'Events', stories: 52, views: 312000 },
];

const defaultAds: SponsorAd[] = [
    { id: 'ad1', sponsor: 'Island Audio', title: 'Relaxing Caribbean Sounds', body: 'Let steel drums and ocean waves transport you to paradise.', format: 'audio', media: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', cta: 'Download App', package: 'Featured Audio', value: 1800, target: 's1', targetType: 'story', status: 'Active', impressions: 184000, clicks: 4220 },
    { id: 'ad2', sponsor: 'Caribbean Travel Co.', title: 'Escape to the Islands', body: 'Book your dream Caribbean getaway with exclusive LinkUp member rates.', format: 'image', media: 'https://picsum.photos/seed/adtravel/640/360', cta: 'Book Now', package: 'Banner Image', value: 3200, target: 'Caribbean', targetType: 'region', status: 'Active', impressions: 220000, clicks: 5880 },
    { id: 'ad3', sponsor: 'Scotiabank', title: 'Bank Smarter with Scotia', body: 'Manage your money across the Caribbean with Scotiabank + LinkUp wallet.', format: 'image', media: 'https://picsum.photos/seed/adbank/640/360', cta: 'Learn More', package: 'Category Takeover', value: 2500, target: 'Latin America', targetType: 'region', status: 'Active', impressions: 310000, clicks: 8420 },
];

const authors = ref<Author[]>([
    { name: 'Caribbean Desk', role: 'Editor', region: 'Caribbean', stories: 42, views: 410000, status: 'Active' },
    { name: 'Sasha Baptiste', role: 'Culture Reporter', region: 'Trinidad & Tobago', stories: 18, views: 132000, status: 'Active' },
    { name: 'Ramon Persaud', role: 'Business Reporter', region: 'Guyana', stories: 24, views: 188000, status: 'Active' },
    { name: 'Lucia Mendez', role: 'Latin America Desk', region: 'Latin America', stories: 31, views: 220000, status: 'Review' },
]);

const stories = ref<Story[]>([...defaultStories]);
const categories = ref<Category[]>(structuredClone(defaultCategories));
const ads = ref<SponsorAd[]>(structuredClone(defaultAds));
const bookmarks = ref<Set<string>>(new Set());
const search = ref('');
const quickCategory = ref('all');
const sortMode = ref<'newest' | 'trending'>('newest');
const savedOnly = ref(false);
const showDark = ref(false);
const selectedStory = ref<Story | null>(null);
const form = ref({
    headline: '',
    summary: '',
    body: '',
    region: 'Caribbean' as Story['region'],
    country: 'Bahamas',
    category: 'Events',
    breaking: false,
    author: 'Caribbean Desk',
    source: 'LinkUp Caribbean 360 Desk',
});

const fmt = (value: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value || 0);
const num = (value: number) => Math.round(value || 0).toLocaleString();
const rel = (hours: number) => (hours >= 720 ? `${Math.round(hours / 720)}mo` : hours >= 24 ? `${Math.round(hours / 24)}d` : `${hours}h`);

const categoryOptions = computed(() => categories.value.map((category) => category.key));
const countryOptions = computed(() => Array.from(new Set(stories.value.map((story) => story.country).filter((country) => country !== 'Global'))));

const filteredStories = computed(() => {
    const q = search.value.toLowerCase();
    let list = stories.value.filter((story) => !q || [story.title, story.summary, story.country, story.category, story.region].join(' ').toLowerCase().includes(q));
    if (savedOnly.value) list = list.filter((story) => bookmarks.value.has(story.id));
    if (quickCategory.value !== 'all') list = list.filter((story) => story.category === quickCategory.value);
    return list.slice().sort((a, b) => sortMode.value === 'trending' ? b.fire - a.fire : a.hours - b.hours);
});

const hero = computed(() => filteredStories.value[0] || stories.value[0]);
const breakingStories = computed(() => stories.value.filter((story) => story.breaking));
const adForStory = (story?: Story | null) => {
    if (!story) return ads.value.find((ad) => ad.status === 'Active') || null;
    const active = ads.value.filter((ad) => ad.status === 'Active');
    return active.find((ad) => ad.targetType === 'story' && ad.target === story.id)
        || active.find((ad) => ad.targetType === 'category' && ad.target === story.category)
        || active.find((ad) => ad.targetType === 'region' && ad.target === story.region)
        || active.find((ad) => ad.targetType === 'all')
        || active[0]
        || null;
};

const toggleBookmark = (id: string) => {
    const next = new Set(bookmarks.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    bookmarks.value = next;
};

const publishStory = () => {
    if (!form.value.headline.trim()) {
        toast.error('Headline is required.');
        return;
    }

    stories.value.unshift({
        id: `s${Date.now()}`,
        title: form.value.headline,
        summary: form.value.summary || 'New Caribbean 360 story summary.',
        body: form.value.body || form.value.summary || 'Story body will be completed by the Caribbean 360 desk.',
        country: form.value.country,
        flag: form.value.region === 'Global' ? '🌐' : '📰',
        category: form.value.category,
        region: form.value.region,
        hours: 0,
        fire: form.value.breaking ? 4 : 0,
        breaking: form.value.breaking,
        status: 'Published',
        author: form.value.author,
        views: 0,
        img: `https://picsum.photos/seed/c360-${Date.now()}/720/440`,
    });

    form.value.headline = '';
    form.value.summary = '';
    form.value.body = '';
    form.value.breaking = false;
    toast.success('Story published.');
    emit('view-changed', 'newsListCommand');
};

const hydrate = () => {
    if (typeof window === 'undefined') return;
    try {
        const savedAds = JSON.parse(window.localStorage.getItem(STORAGE_ADS) || 'null');
        if (Array.isArray(savedAds) && savedAds.length) ads.value = savedAds;
    } catch {}
    try {
        const savedCats = JSON.parse(window.localStorage.getItem(STORAGE_CATEGORIES) || 'null');
        if (Array.isArray(savedCats) && savedCats.length) categories.value = savedCats;
    } catch {}
    try {
        const savedBookmarks = JSON.parse(window.localStorage.getItem(STORAGE_BOOKMARKS) || '[]');
        if (Array.isArray(savedBookmarks)) bookmarks.value = new Set(savedBookmarks);
    } catch {}
};

watch(ads, () => window.localStorage?.setItem(STORAGE_ADS, JSON.stringify(ads.value)), { deep: true });
watch(categories, () => window.localStorage?.setItem(STORAGE_CATEGORIES, JSON.stringify(categories.value)), { deep: true });
watch(bookmarks, () => window.localStorage?.setItem(STORAGE_BOOKMARKS, JSON.stringify([...bookmarks.value])), { deep: true });

onMounted(hydrate);
</script>

<template>
    <div class="c360-wrap" :class="{ dark: showDark }">
        <Toaster rich-colors position="top-right" />

        <template v-if="viewId === 'newsFeedCommand'">
            <NewsFeedReader @view-changed="emit('view-changed', $event)" />
            <section v-if="false" class="feed-shell">
                <div class="feed-top">
                    <div class="brand-lockup"><span>360</span><div><h3>Caribbean 360 News</h3><p>Choose your countries. See what matters.</p></div></div>
                    <div class="feed-actions">
                        <button class="soft-btn" @click="savedOnly = !savedOnly"><Bookmark class="btn-icon" /> Saved</button>
                        <button class="soft-btn" @click="showDark = !showDark">Dark</button>
                        <button class="dark-btn" @click="emit('view-changed', 'newsCreateCommand')">Admin</button>
                    </div>
                </div>

                <div v-if="breakingStories.length" class="ticker"><b>BREAKING</b><span v-for="story in breakingStories" :key="story.id">{{ story.flag }} {{ story.title }}</span></div>

                <div class="filter-bar">
                    <div class="chip-row"><span>🌎 Caribbean + Latin America + Global</span><span>{{ countryOptions.length }} Countries</span><span>{{ categoryOptions.length }} Categories</span></div>
                    <input v-model="search" placeholder="Search headlines, keywords..." />
                </div>

                <div class="story-grid hero-grid">
                    <article class="hero-card" @click="selectedStory = hero">
                        <img :src="hero.img" alt="" />
                        <div><span class="region-pill">{{ hero.flag }} {{ hero.country }}</span><h2>{{ hero.title }}</h2><p>{{ hero.summary }}</p><button class="dark-btn">Read Story</button></div>
                    </article>
                    <aside class="trending-card">
                        <h4>Trending Now</h4>
                        <div v-for="story in stories.slice().sort((a, b) => b.fire - a.fire).slice(0, 4)" :key="story.id" class="trend-item">
                            <span>{{ story.flag }} {{ story.country }}</span>
                            <b>{{ story.title }}</b>
                            <small>🔥 {{ story.fire }} · {{ rel(story.hours) }}</small>
                        </div>
                    </aside>
                </div>

                <div class="quick-tabs">
                    <button :class="{ active: quickCategory === 'all' }" @click="quickCategory = 'all'">All</button>
                    <button v-for="category in categories" :key="category.key" :class="{ active: quickCategory === category.key }" @click="quickCategory = category.key">{{ category.label }}</button>
                </div>

                <div class="section-line"><h4>Top Stories for You</h4><div><button @click="sortMode = 'newest'">Newest</button><button @click="sortMode = 'trending'">Trending</button></div></div>
                <div class="story-grid">
                    <article v-for="story in filteredStories.slice(0, 8)" :key="story.id" class="story-card">
                        <img :src="story.img" alt="" />
                        <div class="story-body">
                            <span>{{ story.flag }} {{ story.country }} · {{ story.category }}</span>
                            <h4>{{ story.title }}</h4>
                            <p>{{ story.summary }}</p>
                            <div class="story-actions">
                                <button @click="selectedStory = story">Read</button>
                                <button @click="toggleBookmark(story.id)">
                                    <BookmarkCheck v-if="bookmarks.has(story.id)" class="btn-icon" />
                                    <Bookmark v-else class="btn-icon" />
                                </button>
                            </div>
                            <div v-if="adForStory(story)" class="sponsor-mini">Sponsored: {{ adForStory(story)?.title }}</div>
                        </div>
                    </article>
                </div>
            </section>
        </template>

        <template v-else-if="viewId === 'newsDashboardCommand'">
            <NewsDashboard @view-changed="emit('view-changed', $event)" />
        </template>

        <template v-else-if="viewId === 'newsListCommand'">
            <NewsList @view-changed="emit('view-changed', $event)" />
        </template>

        <template v-else-if="viewId === 'newsCreateCommand'">
            <NewsCreateStory />
        </template>

        <template v-else-if="viewId === 'newsCategoriesCommand'">
            <NewsCategories />
        </template>

        <template v-else-if="viewId === 'newsRegionsCommand'">
            <NewsRegions />
        </template>

        <template v-else-if="viewId === 'newsSponsoredCommand'">
            <NewsSponsored @view-changed="emit('view-changed', $event)" />
        </template>

        <template v-else-if="viewId === 'newsAuthorsCommand'">
            <NewsAuthors />
        </template>

        <div v-if="selectedStory" class="modal-backdrop">
            <section class="reader-modal">
                <button class="modal-close" @click="selectedStory = null">x</button>
                <img :src="selectedStory.img" alt="" />
                <div class="reader-body"><span>{{ selectedStory.flag }} {{ selectedStory.country }} · {{ selectedStory.category }}</span><h2>{{ selectedStory.title }}</h2><p>{{ selectedStory.body }}</p><div v-if="adForStory(selectedStory)" class="reader-ad"><b>Sponsored</b><h4>{{ adForStory(selectedStory)?.title }}</h4><p>{{ adForStory(selectedStory)?.body }}</p><button>{{ adForStory(selectedStory)?.cta }}</button></div></div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.c360-wrap { display: grid; gap: 24px; color: #0f172a; }
.c360-wrap.dark .feed-shell { background: #07111f; color: #fff; }
.c360-header, .panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.c360-header h3, .panel-head h3 { margin: 0; font-size: 30px; font-weight: 950; letter-spacing: 0; }
.c360-header p, .panel-head p { margin: 5px 0 0; color: #64748b; font-weight: 700; }
.header-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.soft-btn, .dark-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 16px; padding: 11px 16px; font-weight: 950; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; }
.dark-btn { border: 0; background: #020617; color: #fff; }
.dark-btn.full { width: 100%; }
.btn-icon { width: 16px; height: 16px; }
.feed-shell, .card-panel, .metric, .story-card, .trending-card, .category-card, .author-card { background: #fff; border: 1px solid #eaf0f7; box-shadow: 0 18px 45px rgba(15,23,42,.07); }
.feed-shell { overflow: hidden; border-radius: 24px; }
.feed-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; border-bottom: 1px solid #f1f5f9; }
.brand-lockup { display: flex; align-items: center; gap: 12px; }
.brand-lockup > span { display: grid; width: 48px; height: 48px; place-items: center; border-radius: 16px; background: #0ea5e9; color: #fff; font-weight: 950; }
.brand-lockup h3 { margin: 0; font-size: 24px; font-weight: 950; }
.brand-lockup p { margin: 2px 0 0; color: #64748b; font-size: 13px; font-weight: 800; }
.feed-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.ticker { display: flex; gap: 18px; overflow: hidden; background: #e11d48; color: #fff; padding: 9px 14px; font-weight: 850; white-space: nowrap; }
.ticker b { background: #be123c; margin: -9px 0 -9px -14px; padding: 9px 14px; }
.filter-bar { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; }
.filter-bar input, .panel-head input, .form-panel input, .form-panel select, .form-panel textarea { border: 1px solid #e2e8f0; border-radius: 16px; padding: 10px 14px; outline: none; }
.chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
.chip-row span, .region-pill, .status-pill { border-radius: 999px; background: #f1f5f9; padding: 6px 11px; font-size: 12px; font-weight: 950; }
.hero-grid { grid-template-columns: minmax(0, 2fr) minmax(310px, 1fr); padding: 20px; }
.story-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
.story-grid.compact { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.hero-card { display: grid; grid-template-columns: 1.1fr .9fr; gap: 20px; border-radius: 24px; background: #fff; border: 1px solid #eaf0f7; padding: 18px; cursor: pointer; }
.hero-card img, .story-card img, .reader-modal img { width: 100%; object-fit: cover; background: #f1f5f9; }
.hero-card img { height: 310px; border-radius: 20px; }
.hero-card h2 { margin: 12px 0 8px; font-size: 30px; line-height: 1.05; font-weight: 950; }
.hero-card p, .story-body p, .reader-body p { color: #64748b; line-height: 1.55; }
.trending-card { border-radius: 24px; padding: 18px; }
.trending-card h4 { margin: 0 0 12px; font-size: 20px; font-weight: 950; }
.trend-item { border: 1px solid #f1f5f9; border-radius: 16px; padding: 12px; margin-top: 10px; }
.trend-item span, .trend-item small, .story-body span { color: #64748b; font-size: 12px; font-weight: 850; }
.trend-item b { display: block; margin: 5px 0; }
.quick-tabs { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 20px 10px; }
.quick-tabs button, .section-line button, table button, .category-card button { border: 1px solid #e2e8f0; border-radius: 999px; background: #fff; padding: 8px 13px; font-weight: 950; cursor: pointer; }
.quick-tabs button.active { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
.section-line { display: flex; justify-content: space-between; align-items: center; padding: 5px 20px; }
.section-line h4 { margin: 0; font-size: 24px; font-weight: 950; }
.feed-shell > .story-grid { padding: 0 20px 24px; }
.story-card { overflow: hidden; border-radius: 22px; }
.story-card img { height: 150px; }
.story-body { padding: 14px; }
.story-body h4 { min-height: 48px; margin: 7px 0; font-weight: 950; line-height: 1.15; }
.story-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; }
.story-actions button { border: 0; border-radius: 12px; background: #f1f5f9; padding: 8px 11px; font-weight: 950; }
.sponsor-mini { margin-top: 10px; border-radius: 14px; background: #f8fafc; padding: 9px; color: #64748b; font-size: 12px; font-weight: 850; }
.kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.metric { position: relative; overflow: hidden; border-radius: 24px; padding: 20px; }
.metric svg { position: absolute; right: 18px; top: 18px; width: 22px; height: 22px; color: #0ea5e9; }
.metric p { margin: 0; color: #64748b; font-weight: 850; }
.metric h3 { margin: 6px 0 0; font-size: 30px; font-weight: 950; }
.card-panel { border-radius: 24px; padding: 22px; }
.card-panel h3 { margin-top: 0; font-size: 22px; font-weight: 950; }
.table-scroll { overflow-x: auto; }
table { width: 100%; min-width: 840px; border-collapse: collapse; text-align: left; }
th { color: #64748b; font-size: 11px; text-transform: uppercase; padding: 12px; background: #f8fafc; }
td { border-top: 1px solid #e2e8f0; padding: 13px 12px; }
td small { display: block; color: #94a3b8; margin-top: 2px; }
.create-grid { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 20px; }
.form-panel { display: grid; gap: 14px; }
.form-panel label { display: grid; gap: 6px; color: #475569; font-weight: 900; }
.check { display: flex !important; align-items: center; justify-content: space-between; border-radius: 18px; background: #fff1f2; padding: 14px; }
.check input { width: 24px; height: 24px; accent-color: #e11d48; }
.category-grid, .author-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.category-card, .author-card { border-radius: 20px; padding: 18px; }
.category-card svg { width: 24px; color: #0ea5e9; }
.category-card h4, .author-card h4 { margin: 10px 0 4px; font-weight: 950; }
.avatar { display: grid; width: 54px; height: 54px; place-items: center; border-radius: 18px; background: #0f172a; color: #fff; font-weight: 950; }
.author-card span { display: block; margin-top: 8px; color: #64748b; font-size: 12px; font-weight: 850; }
.modal-backdrop { position: fixed; inset: 0; z-index: 9998; display: grid; place-items: center; background: rgba(2,6,23,.55); padding: 20px; }
.reader-modal, .ad-modal { position: relative; width: min(100%, 860px); max-height: 90vh; overflow: auto; border-radius: 26px; background: #fff; box-shadow: 0 25px 80px rgba(2,6,23,.35); }
.reader-modal img { height: 360px; }
.reader-body { padding: 24px; }
.reader-body h2 { margin: 8px 0; font-size: 34px; font-weight: 950; line-height: 1.05; }
.reader-ad { margin-top: 20px; border-radius: 20px; background: #f8fafc; padding: 18px; }
.reader-ad button { border: 0; border-radius: 14px; background: #0ea5e9; color: #fff; padding: 10px 14px; font-weight: 950; }
.modal-close { position: absolute; right: 14px; top: 14px; z-index: 2; border: 0; border-radius: 999px; width: 34px; height: 34px; background: #fff; font-weight: 950; }
.ad-modal { padding: 22px; max-width: 620px; }
@media (max-width: 1200px) { .story-grid, .category-grid, .author-grid, .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hero-grid, .create-grid { grid-template-columns: 1fr; } }
@media (max-width: 760px) { .story-grid, .category-grid, .author-grid, .kpi-grid { grid-template-columns: 1fr; } .c360-header, .feed-top, .filter-bar, .section-line, .panel-head { flex-direction: column; align-items: stretch; } .hero-card { grid-template-columns: 1fr; } .hero-card h2 { font-size: 24px; } }
</style>
