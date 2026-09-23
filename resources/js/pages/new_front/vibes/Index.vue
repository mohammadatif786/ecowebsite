<template>
    <div class="fade">
        <!-- Section Head -->
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-lkink">✨ Vibes</h1>
                <p class="text-slate-500 font-semibold mt-1">Photos, reels & shoppable moments</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openVibesMessagesModal" class="h-10 px-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 flex items-center gap-2 text-slate-700 hover:text-lkblue transition shadow-sm active:scale-95 text-xs font-bold" title="Vibes Messages">
                    <i data-lucide="message-square" class="w-4 h-4 text-lkblue"></i>
                    <span class="hidden sm:inline">Messages</span>
                </button>
                <button @click="openNewReelModal" class="btn btn-primary px-4 py-2.5 flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>Post
                </button>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Main Feed Column -->
            <div class="lg:col-span-2 space-y-4">

                <!-- Stories Carousel -->
                <div class="card p-4">
                    <div class="flex gap-4 overflow-x-auto hide-scroll">
                        <button @click="openNewReelModal" class="flex flex-col items-center gap-1 shrink-0">
                            <span
                                class="w-16 h-16 rounded-full border-2 border-dashed border-slate-300 grid place-items-center text-slate-400">
                                <i data-lucide="plus" class="w-6 h-6"></i>
                            </span>
                            <span class="text-xs font-bold text-slate-500">Add</span>
                        </button>
                        <button v-for="story in stories" :key="story.id" @click="openReelView(story)"
                            class="flex flex-col items-center gap-1 shrink-0">
                            <span
                                class="w-16 h-16 rounded-full p-[3px] bg-gradient-to-tr from-lkyellow via-pink-500 to-lkblue">
                                <img :src="story.avatar"
                                    class="w-full h-full rounded-full object-cover border-2 border-white" />
                            </span>
                            <span class="text-xs font-semibold text-slate-600 max-w-[64px] truncate">{{ story.handle
                                }}</span>
                        </button>
                    </div>
                </div>

                <!-- Composer Input -->
                <div class="card p-4 flex items-center gap-3">
                    <img :src="user.avatar" class="w-11 h-11 rounded-full object-cover" />
                    <button @click="openCompose"
                        class="flex-1 text-left bg-slate-100 hover:bg-slate-200 rounded-full px-5 py-3 text-slate-500 font-semibold transition">
                        Share a vibe…
                    </button>
                    <button @click="openCompose"
                        class="w-11 h-11 rounded-full bg-gradient-to-tr from-blue-600 to-purple-600 text-white grid place-items-center">
                        <i data-lucide="image-plus" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Feed List -->
                <div id="vibeFeed" class="space-y-4">
                    <VibeCard v-for="post in posts" :key="post.id" :p="post" @deleted="onPostDeleted" />
                </div>
            </div>

            <!-- Right Column Sidebar -->
            <div class="space-y-4">
                <!-- My Earnings Card -->
                <div @click="openEarnings"
                    class="bg-emerald-600 rounded-[1.5rem] p-5 text-white shadow-lg shadow-emerald-600/20 flex items-center justify-between cursor-pointer hover:scale-[1.02] transition-transform active:scale-95">
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <i data-lucide="trending-up" class="w-5 h-5"></i>
                            <h3 class="font-black text-[17px] tracking-tight">My Earnings</h3>
                        </div>
                        <p class="text-emerald-100 text-[11px] font-bold">Affiliate Commissions</p>
                    </div>
                    <div class="text-2xl font-black tracking-tighter">
                        {{ money(props.earningsStats?.total_commission || 0) }}
                    </div>
                </div>

                <div class="card p-4">
                    <h3 class="font-black mb-3">Trending tags</h3>
                    <div v-if="trendingTags.length" class="flex flex-wrap gap-2">
                        <span v-for="t in trendingTags" :key="t" class="chip" @click="showToast(t)">{{ t }}</span>
                    </div>
                    <p v-else class="text-xs font-bold text-slate-400 py-4 text-center">No trending tags yet</p>
                </div>

                <div class="card p-4">
                    <h3 class="font-black mb-2">Suggested creators</h3>

                    <!-- Search Input -->
                    <div class="relative mb-3">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input v-model="creatorSearchQuery"
                            type="text"
                            placeholder="Search creators..."
                            class="w-full bg-slate-100 focus:bg-white border border-slate-200 focus:border-lkblue rounded-xl pl-9 pr-3 py-2 text-xs font-semibold text-slate-800 outline-none transition" />
                    </div>

                    <div v-if="filteredCreators.length" ref="creatorsContainerRef" class="space-y-2 max-h-[520px] overflow-y-auto hide-scroll">
                        <div v-for="creator in visibleSuggestedCreators" :key="creator.user_id"
                            class="flex items-center gap-3 py-2 border-b border-slate-50 last:border-none">
                            <img :src="creator.avatar || ('https://i.pravatar.cc/150?u=' + creator.user_id)"
                                @error="$event.target.src = 'https://i.pravatar.cc/150?u=' + (creator.user_id || 1)"
                                class="w-10 h-10 rounded-full object-cover shrink-0" />
                            <div class="flex-1 min-w-0">
                                <span class="font-bold text-sm block truncate">{{ creator.handle }}</span>
                                <span v-if="creator.name && creator.name !== creator.handle" class="text-xs text-slate-400 block truncate">{{ creator.name }}</span>
                            </div>
                            <button :class="['btn text-xs px-3 py-1.5 shrink-0 transition', creator.is_following ? 'btn-ghost opacity-70' : 'btn-primary']"
                                @click="followCreator(creator)">
                                {{ creator.is_following ? 'Following' : 'Follow' }}
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs font-bold text-slate-400 py-4 text-center">
                        {{ creatorSearchQuery ? 'No creators found' : 'No suggested creators yet' }}
                    </p>

                    <button v-if="filteredCreators.length > visibleCreatorsLimit"
                        @click="loadMoreCreators"
                        :disabled="loadingMoreCreators"
                        class="w-full mt-3 py-2.5 text-xs font-black text-lkblue hover:bg-slate-100 bg-slate-50 rounded-xl transition flex items-center justify-center gap-2 active:scale-95 disabled:opacity-60">
                        <template v-if="loadingMoreCreators">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-lkblue"></i>
                            <span>Loading 10 more creators...</span>
                        </template>
                        <template v-else>
                            <span>See More</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- Compose Modal -->
        <ComposeVibeModal ref="composeModalRef" :publishers="vibePublishers" :affiliateItems="affiliateItems"
            @postCreated="onPostCreated" />

        <!-- Affiliate Hub Modal -->
        <AffiliateHubModal ref="affiliateHubModalRef" :items="affiliateItems" :stats="earningsStats" />

        <!-- New Reel Modal -->
        <NewReelModal ref="newReelModalRef" @reelCreated="onReelCreated" />

        <!-- Reel View Modal -->
        <ReelViewModal ref="reelViewModalRef" :reel="currentReel" :reels="allReels" @likeToggled="onReelLikeToggled"
            @commentAdded="onReelCommentAdded" @giftSent="onReelGiftSent" @shareClicked="onReelShareClicked"
            @userProfileClicked="onUserProfileClicked" />

        <!-- User Profile Modal -->
        <UserProfileModal ref="userProfileModalRef" :user-id="currentUserId" @reelClicked="onProfileReelClicked"
            @messageClicked="onMessageClicked" @followToggled="onFollowToggled" />

        <!-- Vibes Messages Modal -->
        <VibesMessagesModal ref="vibesMessagesModalRef" @selectUser="onSelectChatUser" />

        <!-- Vibes Chat Modal -->
        <VibesChatModal ref="vibesChatModalRef" @back="openVibesMessagesModal" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { formatMoney } from '../../../lib/utils';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import VibeCard from '../../../components/new_frontend/cards/VibeCard.vue';
import ComposeVibeModal from '../../../components/new_frontend/modals/ComposeVibeModal.vue';
import AffiliateHubModal from '../../../components/new_frontend/modals/AffiliateHubModal.vue';
import NewReelModal from '../../../components/new_frontend/modals/NewReelModal.vue';
import ReelViewModal from '../../../components/new_frontend/modals/ReelViewModal.vue';
import UserProfileModal from '../../../components/new_frontend/modals/UserProfileModal.vue';
import VibesMessagesModal from '../../../components/new_frontend/modals/VibesMessagesModal.vue';
import VibesChatModal from '../../../components/new_frontend/modals/VibesChatModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    vibePublishers: { type: Object, default: () => ({ organizations: [], groups: [], custom: [] }) },
    vibes: { type: Array, default: () => [] },
    affiliateItems: { type: Array, default: () => [] },
    earningsStats: { type: Object, default: () => ({}) },
    trendingTags: { type: Array, default: () => [] },
    stories: { type: Array, default: () => [] },
    allReels: {
        type: Array,
        default: () => [],
    },
    suggestedCreators: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const posts = ref([...props.vibes]);

const money = (n) => formatMoney(n);

const stories = ref([...(props.stories || [])]);
const allReels = computed(() => props.allReels || []);

const trendingTags = computed(() => props.trendingTags || []);
const creatorSearchQuery = ref('');
const visibleCreatorsLimit = ref(10);

const suggestedCreators = computed(() => {
    if (props.suggestedCreators && props.suggestedCreators.length) {
        return props.suggestedCreators;
    }

    // Filter stories to get unique users (excluding current user)
    return stories.value
        .filter(story => story.user_id !== user.value.id)
        .reduce((acc, story) => {
            if (!acc.find(u => u.user_id === story.user_id)) {
                acc.push({
                    handle: story.handle,
                    avatar: story.avatar,
                    user_id: story.user_id,
                    name: story.name
                });
            }
            return acc;
        }, []);
});

const filteredCreators = computed(() => {
    const list = suggestedCreators.value || [];
    if (!creatorSearchQuery.value.trim()) {
        return list;
    }
    const q = creatorSearchQuery.value.toLowerCase().trim();
    return list.filter(c =>
        (c.handle && c.handle.toLowerCase().includes(q)) ||
        (c.name && c.name.toLowerCase().includes(q))
    );
});

const visibleSuggestedCreators = computed(() => {
    return filteredCreators.value.slice(0, visibleCreatorsLimit.value);
});

const creatorsContainerRef = ref(null);
const loadingMoreCreators = ref(false);

const loadMoreCreators = async () => {
    if (loadingMoreCreators.value) return;
    loadingMoreCreators.value = true;

    await new Promise(resolve => setTimeout(resolve, 300));
    visibleCreatorsLimit.value += 10;
    loadingMoreCreators.value = false;

    nextTick(() => {
        if (creatorsContainerRef.value) {
            creatorsContainerRef.value.scrollTo({
                top: creatorsContainerRef.value.scrollTop + 280,
                behavior: 'smooth'
            });
        }
        if (window.lucide) window.lucide.createIcons();
    });
};

const composeModalRef = ref(null);
const affiliateHubModalRef = ref(null);
const newReelModalRef = ref(null);
const reelViewModalRef = ref(null);
const userProfileModalRef = ref(null);
const vibesMessagesModalRef = ref(null);
const vibesChatModalRef = ref(null);

const openVibesMessagesModal = () => {
    const followedList = (suggestedCreators.value || []).filter(c => c.is_following);
    if (vibesMessagesModalRef.value) {
        vibesMessagesModalRef.value.open(followedList);
    }
};

const onSelectChatUser = (targetUser) => {
    if (vibesChatModalRef.value) {
        vibesChatModalRef.value.open(targetUser);
    }
};

const openCompose = () => {
    if (composeModalRef.value) {
        composeModalRef.value.open();
    }
};

const openEarnings = () => {
    if (affiliateHubModalRef.value) {
        affiliateHubModalRef.value.open('earnings');
    }
};

const openNewReelModal = () => {
    if (newReelModalRef.value) {
        newReelModalRef.value.open();
    }
};

const currentReel = ref(null);
const currentUserId = ref(null);

const openReelView = (story) => {
    currentReel.value = story;
    if (reelViewModalRef.value) {
        reelViewModalRef.value.open();
    }
};

const onPostCreated = (newPost) => {
    // The ComposeVibeModal already emits a formatted post object
    posts.value.unshift(newPost);
};

const onPostDeleted = (vibeId) => {
    posts.value = posts.value.filter(p => p.id !== vibeId);
};

const onReelCreated = (newReel) => {
    // Add the new reel to the stories
    stories.value.unshift({
        id: newReel.id,
        uid: newReel.uid,
        handle: 'Your Reel',
        avatar: newReel.avatar,
        type: newReel.type,
        file_path: newReel.file_path,
        thumbnail_path: newReel.thumbnail_path,
        user_id: user.value.id,
        name: user.value.name,
    });
};

const onReelLikeToggled = (data) => {
    const story = stories.value.find(s => String(s.id) === String(data.reelId));
    if (story) {
        story.likes_count = data.likesCount;
        story.is_liked = data.isLiked;
    }
    if (currentReel.value && String(currentReel.value.id) === String(data.reelId)) {
        currentReel.value.likes_count = data.likesCount;
        currentReel.value.is_liked = data.isLiked;
    }
};

const onReelCommentAdded = (data) => {
    const story = stories.value.find(s => String(s.id) === String(data.reelId));
    if (story) {
        story.comments_count = (story.comments_count || 0) + 1;
    }
    if (currentReel.value && String(currentReel.value.id) === String(data.reelId)) {
        currentReel.value.comments_count = (currentReel.value.comments_count || 0) + 1;
    }
};

const onReelGiftSent = (data) => {
    const story = stories.value.find(s => String(s.id) === String(data.reelId));
    if (story) {
        story.gifts_count = (story.gifts_count || 0) + 1;
        story.bigups_count = (story.bigups_count || 0) + (data.bigupsCount || 1);
    }
    if (currentReel.value && String(currentReel.value.id) === String(data.reelId)) {
        currentReel.value.gifts_count = (currentReel.value.gifts_count || 0) + 1;
        currentReel.value.bigups_count = (currentReel.value.bigups_count || 0) + (data.bigupsCount || 1);
    }
};

const onReelShareClicked = (data) => {
    const story = stories.value.find(s => String(s.id) === String(data.reelId));
    if (story) {
        story.shares_count = (story.shares_count || 0) + 1;
    }
    if (currentReel.value && String(currentReel.value.id) === String(data.reelId)) {
        currentReel.value.shares_count = (currentReel.value.shares_count || 0) + 1;
    }
};

const onUserProfileClicked = (data) => {
    const id = data?.userId || data?.id || data;
    currentUserId.value = id;
    if (userProfileModalRef.value) {
        userProfileModalRef.value.open(id);
    }
};

const onProfileReelClicked = (reel) => {
    currentReel.value = reel;
    if (reelViewModalRef.value) {
        reelViewModalRef.value.open();
    }
};

const onMessageClicked = (data) => {
    if (vibesChatModalRef.value) {
        vibesChatModalRef.value.open(data);
    }
};

const onFollowToggled = (data) => {
    const creator = (suggestedCreators.value || []).find(c => String(c.user_id) === String(data.userId));
    if (creator) {
        creator.is_following = data.isFollowing;
    }

    if (data.isFollowing && data.reels && data.reels.length) {
        data.reels.forEach(newReel => {
            if (!stories.value.some(s => String(s.id) === String(newReel.id))) {
                stories.value.unshift(newReel);
            }
        });
    } else if (!data.isFollowing) {
        stories.value = stories.value.filter(s => String(s.user_id) !== String(data.userId));
    }
};

const followCreator = async (creator) => {
    try {
        const response = await axios.post(route('new_frontend.creators.toggle-follow', { user: creator.user_id }));
        creator.is_following = response.data.is_following;

        if (response.data.is_following && response.data.reels && response.data.reels.length) {
            response.data.reels.forEach(newReel => {
                if (!stories.value.some(s => String(s.id) === String(newReel.id))) {
                    stories.value.unshift(newReel);
                }
            });
            showToast('Following ' + creator.handle + '! Their reels are now in your list.');
        } else if (!response.data.is_following) {
            stories.value = stories.value.filter(s => String(s.user_id) !== String(creator.user_id));
            showToast('Unfollowed ' + creator.handle);
        } else {
            showToast(response.data.is_following ? 'Following ' + creator.handle : 'Unfollowed ' + creator.handle);
        }
    } catch (error) {
        console.error('Failed to toggle follow creator', error);
        showToast('Error updating follow status');
    }
};

const showToast = (msg) => {
    if (window.toast) window.toast(msg);
};

onMounted(() => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
});

watch(
    () => props.stories,
    (newStories) => {
        stories.value = [...(newStories || [])];
    }
);
</script>
