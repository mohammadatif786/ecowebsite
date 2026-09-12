<template>
    <div class="fade pb-20">
        <div class="rounded-3xl bg-slate-900 text-white p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <span
                    class="w-11 h-11 rounded-xl bg-lkyellow text-slate-900 grid place-items-center font-black">360</span>
                <div>
                    <h1 class="text-2xl font-black">Caribbean 360</h1>
                    <p class="text-white/60 text-sm font-semibold">{{ currentDate }} · Live Edition</p>
                </div>
            </div>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input v-model="searchQuery" @keydown.enter="handleSearch" placeholder="Search headlines, keywords…"
                    class="w-full bg-white/10 rounded-full pl-11 pr-4 py-3 text-sm outline-none focus:bg-white/15 transition placeholder:text-white/40" />
            </div>
        </div>

        <div class="mb-5 flex gap-2 overflow-x-auto hide-scroll pb-1">
            <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat"
                :class="['chip transition', selectedCategory === cat ? 'on' : '']">
                {{ cat }}
            </button>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div v-if="errorMessage" class="card p-6 mb-4 bg-red-50 text-red-700 border border-red-100">
                    {{ errorMessage }}
                </div>
                <div v-if="topStory" class="card overflow-hidden h-full">
                    <div class="relative h-72 lg:h-96">
                        <img :src="topStory.image" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <span
                            class="absolute top-4 left-4 bg-pink-600 text-white text-xs font-black px-3 py-1 rounded-full shadow-sm">
                            {{ topStory.cat }}
                        </span>
                        <span
                            class="absolute top-4 right-4 bg-lkyellow text-slate-900 text-xs font-black px-3 py-1 rounded-full shadow-sm">
                            ★ TOP STORY
                        </span>
                        <div class="absolute bottom-5 left-5 right-5 text-white">
                            <p class="text-sm font-bold text-white/80 mb-1">{{ topStory.flag }} {{ topStory.country }}
                            </p>
                            <h2 class="text-2xl md:text-3xl font-black leading-tight drop-shadow-md">{{ topStory.title
                                }}</h2>
                            <p class="text-white/85 mt-1 max-w-2xl">{{ topStory.excerpt }}</p>
                            <button @click="readFullStory(topStory)"
                                class="btn bg-white text-slate-900 px-4 py-2 mt-3 hover:bg-slate-50 transition shadow-sm font-bold text-sm">
                                Read Full Story →
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="card p-8 text-center text-slate-500 font-bold">
                    No news stories are available right now.
                </div>
            </div>

            <div class="space-y-4">
                <button v-for="n in restStories" :key="n.title" @click="readFullStory(n)"
                    class="card overflow-hidden w-full text-left flex gap-3 p-3 hover:shadow-md hover:-translate-y-0.5 transition-all bg-white group">
                    <img :src="n.image"
                        class="w-24 h-24 rounded-xl object-cover shrink-0 group-hover:scale-105 transition-transform duration-300" />
                    <span class="min-w-0">
                        <span class="text-xs font-black text-lkblue2">{{ n.cat }} · {{ n.flag }}</span>
                        <span
                            class="block font-black leading-tight mt-1 text-slate-900 group-hover:text-lkblue transition-colors">{{
                            n.title }}</span>
                        <span class="block text-xs text-slate-500 mt-1 truncate">{{ n.excerpt }}</span>
                    </span>
                </button>
                <div v-if="!restStories.length" class="text-slate-400 font-bold text-sm py-4 text-center">
                    No other stories match your filters.
                </div>
                <!-- Article modal -->
                <div v-if="articleModal" class="fixed inset-0 z-50 grid place-items-center bg-black/60 p-4">
                    <div class="relative w-full max-w-4xl max-h-[90vh] overflow-auto bg-white rounded-2xl shadow-xl">
                        <button @click="articleModal = false" class="absolute top-4 right-4 bg-white/80 rounded-full p-2 shadow">✕</button>
                        <div class="p-6">
                            <img v-if="currentArticle?.image" :src="currentArticle.image" class="w-full h-56 object-cover rounded-lg mb-4" />
                            <h2 class="text-2xl font-black mb-2">{{ currentArticle?.title }}</h2>
                            <p class="text-sm text-slate-500 mb-4">{{ currentArticle?.flag }} · {{ currentArticle?.country }} · {{ new Date(currentArticle?.publishedAt || Date.now()).toLocaleString() }}</p>
                            <div class="prose max-w-none text-slate-800">
                                <p v-if="currentArticle?.excerpt">{{ currentArticle.excerpt }}</p>
                                <p v-if="currentArticle?.body">{{ currentArticle.body }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    newsItems: { type: Array, default: () => [] },
    newsSources: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const searchQuery = ref(props.filters.search || '');
const categories = ['Caribbean + LatAm + Global', '47 Selected', 'All categories', 'Country-first'];
const selectedCategory = ref('Caribbean + LatAm + Global');
const errorMessage = ref('');

const currentDate = computed(() => {
    return new Date().toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' });
});

const formatStory = (item) => {
    const image = item.image_object || item.media?.find(mediaItem => mediaItem.type === 'image')?.dataUrl;
    return {
        ...item,
        image: image || `https://picsum.photos/seed/linkup-c360-${item.id || item.title}/900/600`,
        cat: item.category || 'News',
        flag: item.countryCode || item.country || 'INT',
        country: item.country || 'Global',
        excerpt: item.summary || (item.body ? item.body.slice(0, 120) + '...' : ''),
    };
};

const newsItems = computed(() => (props.newsItems || []).map(formatStory));

const loadNews = async () => {
    errorMessage.value = '';

    try {
        await router.visit(route('new_frontend.news'), {
            search: searchQuery.value.trim() || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    } catch (error) {
        console.error('Failed to reload Caribbean 360 news:', error);
        errorMessage.value = 'Unable to refresh stories right now. Please try again.';
    }
};

const topStory = computed(() => {
    return newsItems.value.find((story) => story.isBreaking) || newsItems.value[0] || null;
});

const restStories = computed(() => {
    return newsItems.value.filter((story) => story !== topStory.value);
});

const handleSearch = async () => {
    await loadNews();
    if (searchQuery.value.trim() && window.toast) {
        window.toast('🔎 ' + searchQuery.value);
    }
};

// Modal / article view state
const articleModal = ref(false);
const currentArticle = ref(null);
const currentArticleIdx = ref(-1);

const articleList = computed(() => newsItems.value || []);

const readFullStory = (n) => {
    const list = articleList.value;
    const idx = list.findIndex(s => (s.id || s.title) === (n.id || n.title));
    currentArticleIdx.value = idx >= 0 ? idx : 0;
    currentArticle.value = list[currentArticleIdx.value] || n;
    articleModal.value = true;
};

const nextArticle = () => {
    if (currentArticleIdx.value < articleList.value.length - 1) {
        currentArticleIdx.value++;
        currentArticle.value = articleList.value[currentArticleIdx.value];
    }
};

const prevArticle = () => {
    if (currentArticleIdx.value > 0) {
        currentArticleIdx.value--;
        currentArticle.value = articleList.value[currentArticleIdx.value];
    }
};

onMounted(() => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
});
</script>
