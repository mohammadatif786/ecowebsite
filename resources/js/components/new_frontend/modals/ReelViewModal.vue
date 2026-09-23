<template>
    <Modal ref="modalRef" class="reel-modal">
        <div class="reel-viewer" @wheel.prevent="handleWheel" @keydown="handleKeydown" tabindex="0">
            <!-- Blurred background -->
            <div v-if="activeReel?.file_path" class="reel-backdrop" :style="{
                backgroundImage: `url(${activeReel.file_path})`,
            }"></div>

            <div class="reel-backdrop-overlay"></div>

            <!-- Close -->
            <button type="button" class="viewer-close" aria-label="Close" @click.stop="close">
                <X class="h-6 w-6" />
            </button>

            <!-- Main viewer layout -->
            <div class="reel-layout" :class="{
                'reel-layout-comments-open': commentsOpen,
            }">
                <!-- Reel navigation -->
                <!-- Previous / Next -->
                <div class="reel-navigation">
                    <button v-if="hasPreviousReel && !commentsOpen" type="button" class="reel-nav-button reel-nav-prev"
                        aria-label="Previous reel" @click.stop="previousReel">
                        <ChevronLeft class="h-7 w-7" />
                    </button>

                    <button v-if="hasNextReel && !commentsOpen" type="button" class="reel-nav-button reel-nav-next"
                        aria-label="Next reel" @click.stop="nextReel">
                        <ChevronRight class="h-7 w-7" />
                    </button>
                </div>

                <!-- Reel -->
                <div class="reel-stage" @dblclick="handleDoubleClick">
                    <!-- Media -->
                    <div class="reel-media">
                        <video v-if="activeReel?.type === 'video'" ref="videoRef" :key="activeReel?.id"
                            :src="activeReel.file_path" :poster="activeReel.thumbnail_path || undefined" autoplay loop
                            playsinline preload="metadata" class="reel-video" @click.stop="togglePlayPause"
                            @play="isPlaying = true" @pause="isPlaying = false" @timeupdate="updateProgress"
                            @loadedmetadata="updateProgress"></video>

                        <img v-else-if="activeReel?.file_path" :key="activeReel?.id" :src="activeReel.file_path"
                            :alt="activeReel.caption || 'Reel'" class="reel-image" />

                        <div v-else class="flex h-full w-full items-center justify-center bg-black">
                            <span class="text-sm font-semibold text-white/60">
                                Media unavailable
                            </span>
                        </div>
                    </div>

                    <!-- Top gradient -->
                    <div class="top-gradient"></div>

                    <!-- Top bar -->
                    <div class="top-bar">
                        <div class="reels-label">
                            <div class="reels-logo">
                                <Play class="h-4 w-4 fill-white" />
                            </div>

                            <span>Reels</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="top-action-button"
                                aria-label="Options"
                                @click.stop="openOptions"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="19" cy="12" r="1"></circle>
                                    <circle cx="5" cy="12" r="1"></circle>
                                </svg>
                            </button>

                            <button v-if="activeReel?.type === 'video'" type="button" class="top-action-button"
                                aria-label="Toggle sound" @click.stop="toggleMute">
                                <VolumeX v-if="isMuted" class="h-5 w-5" />

                                <Volume2 v-else class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Right action rail -->
                    <div class="action-rail">
                        <!-- Like -->
                        <button type="button" class="action-button" @click.stop="toggleLike">
                            <div class="action-icon" :class="{
                                'liked-icon': isLiked,
                            }">
                                <Heart class="h-7 w-7" :class="isLiked
                                    ? 'fill-rose-500 text-rose-500'
                                    : 'text-white'
                                    " />
                            </div>

                            <span>
                                {{
                                    formatCount(
                                        activeReel?.likes_count
                                    )
                                }}
                            </span>
                        </button>

                        <!-- Comments -->
                        <button type="button" class="action-button" @click.stop="openComments">
                            <div class="action-icon" :class="{
                                'comments-active-icon':
                                    commentsOpen,
                            }">
                                <MessageCircle class="h-7 w-7" :class="commentsOpen
                                    ? 'fill-white text-white'
                                    : 'text-white'
                                    " />
                            </div>

                            <span>
                                {{
                                    formatCount(
                                        activeReel?.comments_count
                                    )
                                }}
                            </span>
                        </button>

                        <!-- Gift -->
                        <button
                            v-if="activeReel?.allow_coin_gifts"
                            type="button"
                            class="action-button"
                            @click.stop="openBigUp"
                        >
                            <div class="action-icon gift-icon">
                                <Zap class="h-6 w-6 fill-white text-white" />
                            </div>

                            <span>
                                Big Up
                                {{
                                    num(
                                        bigupsCount
                                    )
                                }}
                            </span>
                        </button>

                        <!-- Share -->
                        <button type="button" class="action-button" @click.stop="shareReel">
                            <div class="action-icon">
                                <Send class="h-7 w-7 text-white" />
                            </div>

                            <span>
                                {{
                                    formatCount(
                                        activeReel?.shares_count
                                    )
                                }}
                            </span>
                        </button>

                        <!-- Bookmark -->
                        <button type="button" class="action-button" @click.stop="saveReel">
                            <div class="action-icon" :class="{ 'saved-icon': isSaved }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 24 24" :fill="isSaved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>

                            <span>{{ isSaved ? 'Saved' : 'Save' }}</span>
                        </button>
                    </div>

                    <!-- Bottom gradient -->
                    <div class="bottom-gradient"></div>

                    <!-- Bottom content -->
                    <div class="bottom-content">
                        <!-- Creator -->
                        <button type="button" class="creator-button" @click.stop="openUserProfile">
                            <img v-if="activeReel?.avatar" :src="activeReel.avatar" :alt="activeReel?.handle || 'User'"
                                class="creator-avatar" />

                            <div v-else class="creator-avatar creator-placeholder">
                                {{
                                    getInitials(
                                        activeReel?.handle
                                    )
                                }}
                            </div>

                            <div class="creator-info">
                                <div class="creator-name-row">
                                    <span class="creator-name">
                                        {{
                                            activeReel?.handle ||
                                            'Unknown user'
                                        }}
                                    </span>

                                    <span v-if="activeReel?.verified" class="verified-badge">
                                        <Check class="h-3 w-3" />
                                    </span>
                                </div>

                                <div v-if="activeReel?.location" class="creator-location">
                                    <MapPin class="h-3 w-3" />

                                    <span>
                                        {{ activeReel.location }}
                                    </span>
                                </div>
                            </div>
                        </button>

                        <!-- Caption -->
                        <div v-if="activeReel?.caption" class="caption-container">
                            <p class="caption" :class="{
                                expanded: captionExpanded,
                            }">
                                {{ activeReel.caption }}
                            </p>

                            <button v-if="
                                activeReel.caption.length > 120
                            " type="button" class="caption-more" @click.stop="
                                captionExpanded =
                                !captionExpanded
                                ">
                                {{
                                    captionExpanded
                                        ? 'less'
                                        : 'more'
                                }}
                            </button>
                        </div>

                        <!-- Audio -->
                        <div class="audio-row">
                            <Music2 class="h-3.5 w-3.5" />

                            <span>
                                Original audio ·
                                {{
                                    activeReel?.handle ||
                                    'Unknown user'
                                }}
                            </span>
                        </div>

                        <!-- VibeTagCard -->
                        <button
                            v-if="activeReel?.tag"
                            @click.stop="openVibeTag"
                            class="vibe-tag-card"
                        >
                            <div class="vibe-tag-image-wrapper">
                                <img :src="activeReel.tag.image" class="vibe-tag-image" />
                                <span class="vibe-tag-badge" :style="{ background: activeReel.tag.kind === 'event' ? '#2563eb' : '#8b5cf6' }">
                                    {{ activeReel.tag.kind === 'event' ? '🎟️' : '🛍️' }}
                                </span>
                            </div>
                            <div class="vibe-tag-content">
                                <p class="vibe-tag-title">{{ activeReel.tag.title }}</p>
                                <p class="vibe-tag-subtitle">
                                    {{ activeReel.tag.kind === 'event' ? `${activeReel.tag.date} · ${activeReel.tag.location || 'Location TBA'}` : (activeReel.tag.seller || 'Marketplace') }}
                                </p>
                            </div>
                            <span class="vibe-tag-action">
                                {{ activeReel.tag.kind === 'event' ? 'Get Tickets' : 'Shop' }} · {{ activeReel.tag.price ? money(activeReel.tag.price) : 'Free' }}
                            </span>
                        </button>
                    </div>

                    <!-- Play button -->
                    <Transition name="play">
                        <button v-if="
                            activeReel?.type === 'video' &&
                            !isPlaying
                        " type="button" class="center-play" @click.stop="togglePlayPause">
                            <Play class="ml-1 h-8 w-8 fill-white" />
                        </button>
                    </Transition>

                    <!-- Double click heart -->
                    <Transition name="heart">
                        <Heart v-if="showHeartAnimation" class="double-heart" />
                    </Transition>

                    <!-- Progress -->
                    <div v-if="activeReel?.type === 'video'" class="progress-track">
                        <div class="progress-bar" :style="{
                            width: `${progress}%`,
                        }"></div>
                    </div>
                </div>

                <!-- Comments panel -->
                <Transition name="comments-slide">
                    <aside v-if="commentsOpen" class="comments-panel" @click.stop @wheel.stop @keydown.stop>
                        <!-- Comments header -->
                        <div class="comments-header">
                            <div>
                                <h2 class="comments-title">
                                    Comments
                                </h2>

                                <p class="comments-count">
                                    {{
                                        formatCount(
                                            activeReel?.comments_count
                                        )
                                    }}
                                </p>
                            </div>

                            <button type="button" class="comments-close" aria-label="Close comments"
                                @click.stop="closeComments">
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Comments body -->
                        <div class="comments-body">
                            <div v-if="commentsLoading" class="comments-loading">
                                <div class="loading-spinner"></div>

                                <span>
                                    Loading comments...
                                </span>
                            </div>

                            <div v-else-if="comments.length" class="comments-list">
                                <div v-for="comment in comments" :key="comment.id" class="comment-item">
                                    <img v-if="comment.user?.avatar" :src="comment.user.avatar" :alt="comment.user?.name ||
                                        'User'
                                        " class="comment-avatar" />

                                    <div v-else class="comment-avatar comment-avatar-placeholder">
                                        {{
                                            getInitials(
                                                comment.user?.name
                                            )
                                        }}
                                    </div>

                                    <div class="comment-content">
                                        <div class="comment-user-row">
                                            <span class="comment-user">
                                                {{
                                                    comment.user?.name ||
                                                    comment.user?.linkup_id ||
                                                    'User'
                                                }}
                                            </span>

                                            <span v-if="
                                                comment.created_at
                                            " class="comment-time">
                                                {{
                                                    comment.created_at
                                                }}
                                            </span>
                                        </div>

                                        <p class="comment-text">
                                            {{
                                                comment.comment
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty comments -->
                            <div v-else class="comments-empty">
                                <div class="comments-empty-icon">
                                    <MessageCircle class="h-7 w-7" />
                                </div>

                                <h3>
                                    No comments yet
                                </h3>

                                <p>
                                    Be the first to comment
                                    on this reel.
                                </p>
                            </div>
                        </div>

                        <!-- Comment input -->
                        <form class="comments-input-area" @submit.prevent="submitComment">
                            <div class="comment-input-wrapper">
                                <input v-model="newComment" type="text" placeholder="Add a comment..."
                                    class="comment-input" maxlength="1000" autocomplete="off" @keydown.stop @keyup.stop
                                    @keypress.stop />

                                <button type="submit" class="comment-send-button" :disabled="!newComment.trim() ||
                                    commentSubmitting
                                    " aria-label="Post comment" @click.stop>
                                    <Send class="h-4 w-4" />
                                </button>
                            </div>
                        </form>
                    </aside>
                </Transition>
            </div>
        </div>
    </Modal>

    <VibeTagModal ref="vibeTagModalRef" />
    <VibeBigUpModal ref="vibeBigUpModalRef" :p="activeReel" :isReel="true" @sent="onBigUpSent" />
    <VibeOptionsModal ref="vibeOptionsModalRef" :vibe="activeReel" :isOwner="isOwner" @delete="handleDelete" />
</template>

<script setup>
import axios from 'axios';

import {
    Check,
    ChevronLeft,
    ChevronRight,
    Heart,
    MapPin,
    MessageCircle,
    Music2,
    Play,
    Send,
    Volume2,
    VolumeX,
    X,
    Zap,
} from 'lucide-vue-next';

import {
    computed,
    nextTick,
    onBeforeUnmount,
    ref,
    watch,
} from 'vue';

import { usePage } from '@inertiajs/vue3';
import Modal from '../ui/Modal.vue';
import VibeTagModal from '../modals/VibeTagModal.vue';
import VibeBigUpModal from '../modals/VibeBigUpModal.vue';
import VibeOptionsModal from '../modals/VibeOptionsModal.vue';

const props = defineProps({
    reel: {
        type: Object,
        default: null,
    },

    reels: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    'likeToggled',
    'commentAdded',
    'giftSent',
    'shareClicked',
    'userProfileClicked',
    'bigUpSent',
    'deleted',
]);

const modalRef = ref(null);
const videoRef = ref(null);

const vibeTagModalRef = ref(null);
const vibeBigUpModalRef = ref(null);
const vibeOptionsModalRef = ref(null);

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const activeReel = ref(null);
const currentIndex = ref(0);

const isPlaying = ref(false);
const isLiked = ref(false);
const isSaved = ref(false);
const isMuted = ref(false);
const progress = ref(0);
const bigupsCount = ref(0);
const isOwner = computed(() => activeReel.value?.user_id === user.value.id);

const showHeartAnimation = ref(false);
const captionExpanded = ref(false);

/*
|--------------------------------------------------------------------------
| Comments
|--------------------------------------------------------------------------
*/

const commentsOpen = ref(false);
const comments = ref([]);
const commentsLoading = ref(false);
const newComment = ref('');
const commentSubmitting = ref(false);

let heartTimeout = null;

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

const hasPreviousReel = computed(() => {
    return currentIndex.value > 0;
});

const hasNextReel = computed(() => {
    return (
        currentIndex.value <
        props.reels.length - 1
    );
});

const findCurrentReelIndex = () => {
    if (
        !props.reel?.id ||
        !props.reels.length
    ) {
        return -1;
    }

    const index = props.reels.findIndex(
        (item) => String(item.id) === String(props.reel.id)
    );

    return index;
};

const previousReel = async () => {
    if (!hasPreviousReel.value) {
        return;
    }

    closeComments();

    currentIndex.value--;

    await switchToReel(
        props.reels[currentIndex.value]
    );
};

const nextReel = async () => {
    if (!hasNextReel.value) {
        return;
    }

    closeComments();

    currentIndex.value++;

    await switchToReel(
        props.reels[currentIndex.value]
    );
};

const fetchReelDetails = async () => {
    if (!activeReel.value?.id) return;
    try {
        const response = await axios.get(route('new_frontend.reels.show', { reel: activeReel.value.id }));
        if (response.data) {
            const data = response.data;
            activeReel.value.likes_count = data.likes_count;
            activeReel.value.comments_count = data.comments_count;
            activeReel.value.shares_count = data.shares_count;
            activeReel.value.bigups_count = data.bigups_count;
            activeReel.value.is_liked = data.is_liked;
            activeReel.value.is_saved = data.is_saved;

            isLiked.value = Boolean(data.is_liked);
            isSaved.value = Boolean(data.is_saved);
            bigupsCount.value = data.bigups_count || 0;
        }
    } catch (err) {
        console.error('Failed to fetch reel details', err);
    }
};

const switchToReel = async (newReel) => {
    if (!newReel) {
        return;
    }

    if (videoRef.value) {
        videoRef.value.pause();
    }

    activeReel.value = newReel;

    isPlaying.value = false;
    progress.value = 0;
    captionExpanded.value = false;

    isLiked.value = Boolean(
        newReel.is_liked
    );

    isSaved.value = Boolean(
        newReel.is_saved
    );

    bigupsCount.value = newReel.bigups_count || newReel.bigup || 0;

    comments.value = [];
    newComment.value = '';

    fetchReelDetails();

    await nextTick();

    if (
        newReel.type === 'video' &&
        videoRef.value
    ) {
        videoRef.value.muted =
            isMuted.value;

        try {
            await videoRef.value.play();

            isPlaying.value = true;
        } catch {
            isPlaying.value = false;
        }
    }
};

/*
|--------------------------------------------------------------------------
| Open / Close
|--------------------------------------------------------------------------
*/

const open = async () => {
    currentIndex.value = findCurrentReelIndex();

    if (currentIndex.value >= 0 && props.reels[currentIndex.value]) {
        activeReel.value = props.reels[currentIndex.value];
    } else if (props.reel) {
        activeReel.value = props.reel;
    } else {
        activeReel.value = null;
    }

    if (activeReel.value) {
        isLiked.value = Boolean(activeReel.value.is_liked);
        isSaved.value = Boolean(activeReel.value.is_saved);
        bigupsCount.value = activeReel.value.bigups_count || activeReel.value.bigup || 0;
    }

    progress.value = 0;
    captionExpanded.value = false;
    commentsOpen.value = false;

    modalRef.value?.open();

    fetchReelDetails();

    await nextTick();

    const viewer =
        document.querySelector(
            '.reel-viewer'
        );

    viewer?.focus();

    if (
        activeReel.value?.type === 'video' &&
        videoRef.value
    ) {
        videoRef.value.muted =
            isMuted.value;

        try {
            await videoRef.value.play();

            isPlaying.value = true;
        } catch {
            isPlaying.value = false;
        }
    }
};

const close = () => {
    if (videoRef.value) {
        videoRef.value.pause();
    }

    isPlaying.value = false;
    commentsOpen.value = false;

    modalRef.value?.close();
};

/*
|--------------------------------------------------------------------------
| Comments
|--------------------------------------------------------------------------
*/

const openComments = async () => {
    if (!activeReel.value?.id) {
        return;
    }

    commentsOpen.value = true;

    await loadComments();

    /*
     * Keep focus inside the comments area.
     * This prevents keyboard events from being
     * handled by the reel viewer.
     */
    await nextTick();

    const input =
        document.querySelector(
            '.comment-input'
        );

    input?.focus();
};

const closeComments = () => {
    commentsOpen.value = false;

    /*
     * Return keyboard focus to viewer
     * after comments are closed.
     */
    nextTick(() => {
        document
            .querySelector('.reel-viewer')
            ?.focus();
    });
};

const loadComments = async () => {
    if (!activeReel.value?.id) {
        return;
    }

    commentsLoading.value = true;

    try {
        const response = await axios.get(
            route(
                'new_frontend.reels.comments.index',
                {
                    reel:
                        activeReel.value.id,
                }
            )
        );

        comments.value = response.data.comments || [];

        if (typeof response.data.comments_count !== 'undefined') {
            activeReel.value.comments_count = response.data.comments_count;
        }
    } catch (error) {
        console.error(
            'Failed to load comments:',
            error
        );

        comments.value = [];
    } finally {
        commentsLoading.value = false;
    }
};

const submitComment = async () => {
    const content =
        newComment.value.trim();

    if (
        !content ||
        !activeReel.value?.id ||
        commentSubmitting.value
    ) {
        return;
    }

    commentSubmitting.value = true;

    try {
        const response =
            await axios.post(
                route(
                    'new_frontend.reels.comments.store',
                    {
                        reel:
                            activeReel.value.id,
                    }
                ),
                {
                    comment: content,
                }
            );

        const createdComment =
            response.data.comment ||
            response.data.data;

        if (createdComment) {
            comments.value.push(
                createdComment
            );
        } else {
            await loadComments();
        }

        newComment.value = '';

        if (
            typeof response.data
                ?.comments_count !==
            'undefined'
        ) {
            activeReel.value.comments_count =
                response.data.comments_count;
        } else {
            activeReel.value.comments_count =
                Number(
                    activeReel.value
                        .comments_count || 0
                ) + 1;
        }

        emit('commentAdded', {
            reelId:
                activeReel.value.id,
        });

        await nextTick();

        const body =
            document.querySelector(
                '.comments-body'
            );

        if (body) {
            body.scrollTop =
                body.scrollHeight;
        }
    } catch (error) {
        console.error(
            'Failed to add comment:',
            error
        );
    } finally {
        commentSubmitting.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Video
|--------------------------------------------------------------------------
*/

const togglePlayPause = async () => {
    if (!videoRef.value) {
        return;
    }

    try {
        if (videoRef.value.paused) {
            await videoRef.value.play();
        } else {
            videoRef.value.pause();
        }
    } catch (error) {
        console.error(
            'Video playback error:',
            error
        );
    }
};

const toggleMute = () => {
    if (!videoRef.value) {
        return;
    }

    isMuted.value =
        !isMuted.value;

    videoRef.value.muted =
        isMuted.value;
};

const updateProgress = () => {
    if (
        !videoRef.value ||
        !videoRef.value.duration ||
        !Number.isFinite(
            videoRef.value.duration
        )
    ) {
        progress.value = 0;

        return;
    }

    progress.value =
        (videoRef.value.currentTime /
            videoRef.value.duration) *
        100;
};

/*
|--------------------------------------------------------------------------
| Like
|--------------------------------------------------------------------------
*/

const toggleLike = async () => {
    if (!activeReel.value?.id) {
        return;
    }

    try {
        const response =
            await axios.post(
                route(
                    'new_frontend.reels.like',
                    {
                        reel:
                            activeReel.value.id,
                    }
                )
            );

        isLiked.value = Boolean(
            response.data.is_liked
        );
        activeReel.value.is_liked = response.data.is_liked;

        if (
            typeof response.data
                .likes_count !==
            'undefined'
        ) {
            activeReel.value.likes_count =
                response.data.likes_count;
        }

        emit('likeToggled', {
            reelId:
                activeReel.value.id,
            isLiked:
                response.data.is_liked,
            likesCount:
                response.data
                    .likes_count,
        });
    } catch (error) {
        console.error(
            'Failed to toggle like:',
            error
        );
    }
};

/*
|--------------------------------------------------------------------------
| Double Click Like
|--------------------------------------------------------------------------
*/

const handleDoubleClick = async () => {
    if (!activeReel.value) {
        return;
    }

    if (!isLiked.value) {
        await toggleLike();
    }

    showHeartAnimation.value = true;

    clearTimeout(
        heartTimeout
    );

    heartTimeout = setTimeout(() => {
        showHeartAnimation.value =
            false;
    }, 750);
};

/*
|--------------------------------------------------------------------------
| Gift
|--------------------------------------------------------------------------
*/

const sendGift = () => {
    if (!activeReel.value?.id) {
        return;
    }

    emit('giftSent', {
        reelId:
            activeReel.value.id,
    });
};

const openBigUp = () => {
    if (vibeBigUpModalRef.value) {
        vibeBigUpModalRef.value.open();
    }
};

const onBigUpSent = (newCount) => {
    bigupsCount.value = newCount;
    if (activeReel.value) {
        activeReel.value.bigups_count = newCount;
    }
};

const openVibeTag = () => {
    if (vibeTagModalRef.value && activeReel.value?.tag) {
        vibeTagModalRef.value.open(activeReel.value.tag);
    }
};

const openOptions = () => {
    if (vibeOptionsModalRef.value) {
        vibeOptionsModalRef.value.open();
    }
};

const handleDelete = async (reelId) => {
    try {
        await axios.delete(route('new_frontend.reels.destroy', { reel: reelId }));
        showToast('🗑️ Reel deleted');
        emit('deleted', { reelId });
        close();
    } catch (error) {
        console.error('Failed to delete reel', error);
    }
};

const saveReel = async () => {
    if (!activeReel.value?.id) {
        return;
    }

    try {
        const response = await axios.post(route('new_frontend.reels.save', { reel: activeReel.value.id }));

        isSaved.value = Boolean(response.data.is_saved);
        if (activeReel.value) {
            activeReel.value.is_saved = response.data.is_saved;
        }

        if (response.data.is_saved) {
            showToast('🔖 Saved');
        } else {
            showToast('Removed from saves');
        }
    } catch (error) {
        console.error('Failed to save reel:', error);
    }
};

/*
|--------------------------------------------------------------------------
| Share
|--------------------------------------------------------------------------
*/

const shareReel = async () => {
    if (!activeReel.value?.id) {
        return;
    }

    const shareData = {
        title: 'LinkUp Reel',
        text: activeReel.value.caption,
        url: window.location.origin + '/reels?id=' + activeReel.value.id,
    };

    try {
        // Record share on server
        await axios.post(route('new_frontend.reels.share', { reel: activeReel.value.id }));

        emit('shareClicked', {
            reelId: activeReel.value.id,
        });

        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            await navigator.clipboard.writeText(shareData.url);
            showToast('Link copied to clipboard!');
        }
    } catch (err) {
        console.error('Error sharing:', err);
    }
};

/*
|--------------------------------------------------------------------------
| User Profile
|--------------------------------------------------------------------------
*/

const openUserProfile = () => {
    if (!activeReel.value) {
        return;
    }

    emit(
        'userProfileClicked',
        {
            userId:
                activeReel.value
                    .user_id,
            handle:
                activeReel.value
                    .handle,
        }
    );
};

/*
|--------------------------------------------------------------------------
| Keyboard
|--------------------------------------------------------------------------
*/

const handleKeydown = (event) => {
    /*
     * IMPORTANT:
     *
     * Never control the reel when the user is
     * typing in an input, textarea or editable
     * element.
     */
    const target = event.target;

    const isTyping =
        target instanceof HTMLElement &&
        (
            target.tagName === 'INPUT' ||
            target.tagName === 'TEXTAREA' ||
            target.isContentEditable
        );

    if (isTyping) {
        return;
    }

    switch (event.key) {
        case 'ArrowUp':
        case 'ArrowLeft':
            event.preventDefault();
            previousReel();
            break;

        case 'ArrowDown':
        case 'ArrowRight':
            event.preventDefault();
            nextReel();
            break;

        case 'Escape':
            event.preventDefault();

            if (commentsOpen.value) {
                closeComments();
            } else {
                close();
            }

            break;

        case ' ':
            event.preventDefault();
            togglePlayPause();
            break;
    }
};

/*
|--------------------------------------------------------------------------
| Mouse Wheel
|--------------------------------------------------------------------------
*/

let wheelLocked = false;

const handleWheel = (event) => {
    /*
     * Do not navigate reels while the user is
     * scrolling the comments panel.
     */
    if (
        commentsOpen.value &&
        event.target.closest(
            '.comments-panel'
        )
    ) {
        return;
    }

    if (wheelLocked) {
        return;
    }

    if (
        Math.abs(event.deltaY) < 20
    ) {
        return;
    }

    wheelLocked = true;

    if (event.deltaY > 0) {
        nextReel();
    } else {
        previousReel();
    }

    setTimeout(() => {
        wheelLocked = false;
    }, 500);
};

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatCount = (count) => {
    const value = Number(
        count || 0
    );

    if (value >= 1000000) {
        return (
            (value / 1000000)
                .toFixed(1) + 'M'
        );
    }

    if (value >= 1000) {
        return (
            (value / 1000)
                .toFixed(1) + 'K'
        );
    }

    return value.toString();
};

const getInitials = (name) => {
    if (!name) {
        return '?';
    }

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) =>
            part.charAt(0)
        )
        .join('')
        .toUpperCase();
};

const num = (n) => Number(n || 0).toLocaleString();
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const showToast = (msg) => {
    if (window.toast) window.toast(msg);
};

/*
|--------------------------------------------------------------------------
| Watch Parent Reel
|--------------------------------------------------------------------------
*/

watch(
    () => props.reel,
    async (newReel) => {
        if (!newReel) {
            return;
        }

        const newIndex = props.reels.findIndex(
            (item) => String(item.id) === String(newReel.id)
        );

        if (newIndex >= 0) {
            currentIndex.value = newIndex;
        }

        if (
            !activeReel.value ||
            String(activeReel.value.id) !== String(newReel.id)
        ) {
            activeReel.value = newIndex >= 0 ? props.reels[newIndex] : newReel;

            isLiked.value = Boolean(newReel.is_liked);
            isSaved.value = Boolean(newReel.is_saved);
            bigupsCount.value = newReel.bigups_count || newReel.bigup || 0;

            progress.value = 0;
            captionExpanded.value = false;

            fetchReelDetails();

            await nextTick();

            if (
                newReel.type ===
                'video' &&
                videoRef.value
            ) {
                videoRef.value.muted =
                    isMuted.value;

                try {
                    await videoRef.value.play();

                    isPlaying.value =
                        true;
                } catch {
                    isPlaying.value =
                        false;
                }
            }
        }
    }
);

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    clearTimeout(
        heartTimeout
    );

    if (videoRef.value) {
        videoRef.value.pause();
    }
});

defineExpose({
    open,
    close,
});
</script>

<style scoped>
/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

.reel-modal {
    overflow: hidden !important;
}

.reel-modal :deep(.modal-overlay) {
    position: fixed !important;
    inset: 0 !important;

    width: 100vw !important;
    height: 100dvh !important;

    max-width: none !important;
    max-height: none !important;

    margin: 0 !important;
    padding: 0 !important;

    overflow: hidden !important;

    display: block !important;

    background: transparent !important;
}

.reel-modal :deep(.modal-content) {
    position: fixed !important;
    inset: 0 !important;

    width: 100vw !important;
    height: 100dvh !important;

    min-width: 0 !important;
    min-height: 0 !important;

    max-width: none !important;
    max-height: none !important;

    margin: 0 !important;
    padding: 0 !important;

    border: 0 !important;
    border-radius: 0 !important;

    background: transparent !important;
    box-shadow: none !important;

    overflow: hidden !important;

    transform: none !important;
}

/*
|--------------------------------------------------------------------------
| Viewer
|--------------------------------------------------------------------------
*/

.reel-viewer {
    position: fixed;
    inset: 0;

    z-index: 9999;

    width: 100vw;
    height: 100dvh;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    outline: none;

    background: #000;
}

/*
|--------------------------------------------------------------------------
| Background
|--------------------------------------------------------------------------
*/

.reel-backdrop {
    position: absolute;
    inset: -40px;

    z-index: 0;

    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;

    filter: blur(35px);

    transform: scale(1.15);

    opacity: 0.45;
}

.reel-backdrop-overlay {
    position: absolute;
    inset: 0;

    z-index: 1;

    background:
        linear-gradient(90deg,
            rgba(0, 0, 0, 0.75),
            rgba(0, 0, 0, 0.15) 35%,
            rgba(0, 0, 0, 0.15) 65%,
            rgba(0, 0, 0, 0.75));
}

/*
|--------------------------------------------------------------------------
| Main Layout
|--------------------------------------------------------------------------
|
| Closed:
|       [ previous ] [ REEL ] [ next ]
|
| Open:
|       [ previous ] [ REEL ] [ COMMENTS ] [ next ]
|
| The reel itself NEVER gets transformed.
|--------------------------------------------------------------------------
*/

.reel-layout {
    position: relative;

    z-index: 10;

    display: flex;

    align-items: center;
    justify-content: center;

    width: 100%;
    height: 100%;

    gap: 0;
}

/*
|--------------------------------------------------------------------------
| Reel
|--------------------------------------------------------------------------
*/

.reel-stage {
    position: relative;

    z-index: 10;

    flex-shrink: 0;

    width: min(430px,
            calc(100vw - 120px));

    height: min(765px,
            calc(100dvh - 30px));

    aspect-ratio: 9 / 16;

    overflow: hidden;

    border-radius: 18px;

    background: #000;

    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.55);
}

/*
|--------------------------------------------------------------------------
| Media
|--------------------------------------------------------------------------
*/

.reel-media {
    position: absolute;
    inset: 0;

    width: 100%;
    height: 100%;

    background: #000;
}

.reel-video,
.reel-image {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: contain;

    background: #000;

    user-select: none;

    -webkit-user-drag: none;
}

/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

.viewer-close {
    position: fixed;

    top: 20px;
    right: 24px;

    z-index: 200;

    display: flex;

    width: 44px;
    height: 44px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    color: white;

    background: rgba(0, 0, 0, 0.45);

    backdrop-filter: blur(12px);

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;
}

.viewer-close:hover {
    background: rgba(0, 0, 0, 0.7);

    transform: scale(1.05);
}

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

.reel-navigation {
    position: absolute;

    inset: 0;

    z-index: 150;

    pointer-events: none;
}

.reel-nav-button {
    position: absolute;

    top: 50%;

    display: flex;

    width: 50px;
    height: 50px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    color: white;

    background: rgba(255, 255, 255, 0.12);

    backdrop-filter: blur(12px);

    box-shadow:
        0 8px 30px rgba(0, 0, 0, 0.25);

    transform: translateY(-50%);

    pointer-events: auto;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;
}

.reel-nav-button:hover {
    background: rgba(255, 255, 255, 0.22);

    transform:
        translateY(-50%) scale(1.05);
}

.reel-nav-button:active {
    transform:
        translateY(-50%) scale(0.92);
}

/*
|--------------------------------------------------------------------------
| Navigation positioning
|--------------------------------------------------------------------------
|
| Buttons stay tied to the reel's horizontal center.
|
| When comments open, we don't transform the reel.
|--------------------------------------------------------------------------
*/

.reel-nav-prev {
    left: calc(50% - 275px);
}

.reel-nav-next {
    right: calc(50% - 275px);
}

/*
|--------------------------------------------------------------------------
| Comments Panel
|--------------------------------------------------------------------------
*/

.comments-panel {
    position: relative;

    z-index: 100;

    display: flex;

    width: 440px;

    height: min(765px,
            calc(100dvh - 30px));

    margin-left: 24px;

    flex-shrink: 0;

    flex-direction: column;

    overflow: hidden;

    border-radius: 18px;

    background: #fff;

    color: #111827;

    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.5);
}

/*
|--------------------------------------------------------------------------
| Comments Header
|--------------------------------------------------------------------------
*/

.comments-header {
    display: flex;

    flex-shrink: 0;

    align-items: center;
    justify-content: space-between;

    padding: 20px 22px;

    border-bottom: 1px solid #e5e7eb;

    background: #fff;
}

.comments-title {
    margin: 0;

    font-size: 18px;
    font-weight: 800;

    color: #111827;
}

.comments-count {
    margin: 3px 0 0;

    color: #6b7280;

    font-size: 12px;
}

.comments-close {
    display: flex;

    width: 38px;
    height: 38px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    color: #4b5563;

    transition:
        background-color 0.2s ease;
}

.comments-close:hover {
    background: #f3f4f6;
}

/*
|--------------------------------------------------------------------------
| Comments Body
|--------------------------------------------------------------------------
*/

.comments-body {
    flex: 1;

    min-height: 0;

    overflow-y: auto;

    padding: 20px 22px;
}

.comments-body::-webkit-scrollbar {
    width: 6px;
}

.comments-body::-webkit-scrollbar-track {
    background: transparent;
}

.comments-body::-webkit-scrollbar-thumb {
    border-radius: 9999px;

    background: #d1d5db;
}

.comments-list {
    display: flex;

    flex-direction: column;

    gap: 20px;
}

.comment-item {
    display: flex;

    align-items: flex-start;

    gap: 12px;
}

.comment-avatar {
    flex-shrink: 0;

    width: 40px;
    height: 40px;

    border-radius: 9999px;

    object-fit: cover;
}

.comment-avatar-placeholder {
    display: flex;

    align-items: center;
    justify-content: center;

    background: #e5e7eb;

    color: #374151;

    font-size: 11px;
    font-weight: 800;
}

.comment-content {
    min-width: 0;

    flex: 1;
}

.comment-user-row {
    display: flex;

    align-items: center;

    gap: 8px;
}

.comment-user {
    color: #111827;

    font-size: 13px;
    font-weight: 800;
}

.comment-time {
    color: #9ca3af;

    font-size: 10px;
}

.comment-text {
    margin: 4px 0 0;

    color: #374151;

    font-size: 13px;

    line-height: 1.5;

    word-break: break-word;
}

/*
|--------------------------------------------------------------------------
| Empty / Loading
|--------------------------------------------------------------------------
*/

.comments-empty {
    display: flex;

    height: 100%;

    flex-direction: column;

    align-items: center;
    justify-content: center;

    padding: 30px;

    text-align: center;
}

.comments-empty-icon {
    display: flex;

    width: 62px;
    height: 62px;

    align-items: center;
    justify-content: center;

    margin-bottom: 14px;

    border-radius: 9999px;

    background: #f3f4f6;

    color: #6b7280;
}

.comments-empty h3 {
    margin: 0;

    color: #111827;

    font-size: 15px;
    font-weight: 800;
}

.comments-empty p {
    margin: 6px 0 0;

    color: #9ca3af;

    font-size: 12px;
}

.comments-loading {
    display: flex;

    height: 100%;

    align-items: center;
    justify-content: center;

    gap: 8px;

    color: #6b7280;

    font-size: 12px;
}

.loading-spinner {
    width: 18px;
    height: 18px;

    border: 2px solid #e5e7eb;

    border-top-color: #111827;

    border-radius: 9999px;

    animation:
        comment-spin 0.7s linear infinite;
}

@keyframes comment-spin {
    to {
        transform: rotate(360deg);
    }
}

/*
|--------------------------------------------------------------------------
| Comment Input
|--------------------------------------------------------------------------
*/

.comments-input-area {
    flex-shrink: 0;

    padding: 16px 18px;

    border-top: 1px solid #e5e7eb;

    background: #fff;
}

.comment-input-wrapper {
    display: flex;

    align-items: center;

    gap: 10px;

    padding: 6px 6px 6px 16px;

    border: 1px solid #d1d5db;

    border-radius: 9999px;

    background: #f9fafb;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.comment-input-wrapper:focus-within {
    border-color: #9ca3af;

    box-shadow:
        0 0 0 3px rgba(107, 114, 128, 0.1);
}

.comment-input {
    min-width: 0;

    flex: 1;

    border: 0;

    outline: 0;

    background: transparent;

    color: #111827;

    font-size: 14px;
}

.comment-input::placeholder {
    color: #9ca3af;
}

.comment-send-button {
    display: flex;

    width: 36px;
    height: 36px;

    flex-shrink: 0;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    background: #111827;

    color: white;

    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.comment-send-button:hover:not(:disabled) {
    transform: scale(1.05);
}

.comment-send-button:disabled {
    cursor: not-allowed;

    opacity: 0.35;
}

/*
|--------------------------------------------------------------------------
| Comments Animation
|--------------------------------------------------------------------------
*/

.comments-slide-enter-active,
.comments-slide-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.3s ease;
}

.comments-slide-enter-from,
.comments-slide-leave-to {
    opacity: 0;

    transform: translateX(-20px);
}

/*
|--------------------------------------------------------------------------
| Top
|--------------------------------------------------------------------------
*/

.top-gradient {
    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    z-index: 11;

    height: 140px;

    pointer-events: none;

    background:
        linear-gradient(to bottom,
            rgba(0, 0, 0, 0.65),
            transparent);
}

.top-bar {
    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    z-index: 20;

    display: flex;

    align-items: center;
    justify-content: space-between;

    padding: 16px;
}

.reels-label {
    display: flex;

    align-items: center;

    gap: 8px;

    color: white;

    font-size: 15px;
    font-weight: 800;
}

.reels-logo {
    display: flex;

    width: 30px;
    height: 30px;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: rgba(255, 255, 255, 0.15);

    backdrop-filter: blur(10px);
}

.top-action-button {
    display: flex;

    width: 38px;
    height: 38px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    color: white;

    background: rgba(0, 0, 0, 0.35);

    backdrop-filter: blur(10px);

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;
}

.top-action-button:hover {
    background: rgba(0, 0, 0, 0.55);

    transform: scale(1.05);
}

/*
|--------------------------------------------------------------------------
| Action Rail
|--------------------------------------------------------------------------
*/

.action-rail {
    position: absolute;

    right: 12px;
    bottom: 92px;

    z-index: 25;

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 16px;
}

.action-button {
    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 4px;

    color: white;

    font-size: 11px;
    font-weight: 700;

    text-shadow:
        0 1px 4px rgba(0, 0, 0, 0.5);

    transition:
        transform 0.2s ease;
}

.action-button:hover {
    transform: scale(1.08);
}

.action-button:active {
    transform: scale(0.92);
}

.action-icon {
    display: flex;

    width: 46px;
    height: 46px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    background: rgba(0, 0, 0, 0.35);

    backdrop-filter: blur(8px);
}

.saved-icon {
    background: rgba(244, 63, 94, 0.12);
}

.liked-icon {
    background: rgba(244, 63, 94, 0.12);
}

.comments-active-icon {
    background: rgba(255, 255, 255, 0.2);
}

.gift-icon {
    background:
        linear-gradient(135deg,
            rgba(249, 115, 22, 0.95),
            rgba(245, 158, 11, 0.95));

    box-shadow:
        0 8px 20px rgba(249, 115, 22, 0.25);
}

/*
|--------------------------------------------------------------------------
| Bottom
|--------------------------------------------------------------------------
*/

.bottom-gradient {
    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    z-index: 11;

    height: 250px;

    pointer-events: none;

    background:
        linear-gradient(to top,
            rgba(0, 0, 0, 0.82),
            rgba(0, 0, 0, 0.4) 45%,
            transparent);
}

.bottom-content {
    position: absolute;

    left: 16px;
    right: 70px;
    bottom: 20px;

    z-index: 20;

    color: white;
}

.creator-button {
    display: flex;

    align-items: center;

    gap: 10px;

    text-align: left;

    color: white;

    cursor: pointer;
}

.creator-button:hover .creator-name {
    text-decoration: underline;
}

.creator-avatar {
    flex-shrink: 0;

    width: 42px;
    height: 42px;

    border-radius: 9999px;

    object-fit: cover;

    border: 2px solid rgba(255, 255, 255, 0.9);

    cursor: pointer;
}

.creator-placeholder {
    display: flex;

    align-items: center;
    justify-content: center;

    background:
        linear-gradient(135deg,
            #334155,
            #0f172a);

    color: white;

    font-size: 13px;
    font-weight: 800;
}

.creator-info {
    min-width: 0;
}

.creator-name-row {
    display: flex;

    align-items: center;

    gap: 5px;
}

.creator-name {
    max-width: 220px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    font-size: 14px;
    font-weight: 800;
}

.verified-badge {
    display: flex;

    width: 15px;
    height: 15px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    background: #1683ff;

    color: white;
}

.creator-location {
    display: flex;

    align-items: center;

    gap: 4px;

    margin-top: 2px;

    color: rgba(255, 255, 255, 0.7);

    font-size: 11px;
}

/*
|--------------------------------------------------------------------------
| Caption
|--------------------------------------------------------------------------
*/

.caption-container {
    display: block;

    max-width: 310px;

    margin-top: 10px;
}

.caption {
    display: -webkit-box;

    overflow: hidden;

    margin: 0;

    color: white;

    font-size: 13px;

    line-height: 1.45;

    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.caption.expanded {
    display: block;

    max-height: 150px;

    overflow-y: auto;
}

.caption-more {
    margin-left: 4px;

    color: rgba(255, 255, 255, 0.7);

    font-size: 12px;
    font-weight: 700;
}

.audio-row {
    display: flex;

    align-items: center;

    gap: 6px;

    margin-top: 9px;

    max-width: 300px;

    overflow: hidden;

    color: rgba(255, 255, 255, 0.75);

    font-size: 11px;

    white-space: nowrap;
}

.audio-row span {
    overflow: hidden;

    text-overflow: ellipsis;
}

/*
||--------------------------------------------------------------------------
|| Vibe Tag Card
||--------------------------------------------------------------------------
*/

.vibe-tag-card {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    max-width: 310px;
    margin-top: 12px;
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    text-align: left;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    transition: border-color 0.2s ease;
}

.vibe-tag-card:hover {
    border-color: rgba(255, 255, 255, 0.4);
}

.vibe-tag-image-wrapper {
    position: relative;
    flex-shrink: 0;
}

.vibe-tag-image {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
}

.vibe-tag-badge {
    position: absolute;
    top: -4px;
    left: -4px;
    padding: 2px 4px;
    font-size: 9px;
    font-weight: 800;
    color: white;
    border-radius: 9999px;
}

.vibe-tag-content {
    flex: 1;
    min-width: 0;
}

.vibe-tag-title {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.vibe-tag-subtitle {
    margin: 2px 0 0;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.vibe-tag-action {
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 800;
    color: white;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    flex-shrink: 0;
}

/*
|--------------------------------------------------------------------------
| Center Play
|--------------------------------------------------------------------------
*/

.center-play {
    position: absolute;

    top: 50%;
    left: 50%;

    z-index: 30;

    display: flex;

    width: 72px;
    height: 72px;

    align-items: center;
    justify-content: center;

    border-radius: 9999px;

    background: rgba(0, 0, 0, 0.45);

    color: white;

    backdrop-filter: blur(8px);

    transform:
        translate(-50%, -50%);

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;
}

.center-play:hover {
    background: rgba(0, 0, 0, 0.65);

    transform:
        translate(-50%, -50%) scale(1.06);
}

/*
|--------------------------------------------------------------------------
| Heart
|--------------------------------------------------------------------------
*/

.double-heart {
    position: absolute;

    top: 50%;
    left: 50%;

    z-index: 50;

    width: 110px;
    height: 110px;

    color: white;

    fill: white;

    filter:
        drop-shadow(0 8px 20px rgba(0, 0, 0, 0.35));

    transform:
        translate(-50%, -50%);
}

/*
|--------------------------------------------------------------------------
| Progress
|--------------------------------------------------------------------------
*/

.progress-track {
    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    z-index: 40;

    width: 100%;
    height: 3px;

    background: rgba(255, 255, 255, 0.25);
}

.progress-bar {
    height: 100%;

    background: white;

    transition:
        width 0.1s linear;
}

/*
|--------------------------------------------------------------------------
| Animations
|--------------------------------------------------------------------------
*/

.play-enter-active,
.play-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.play-enter-from,
.play-leave-to {
    opacity: 0;

    transform:
        translate(-50%, -50%) scale(0.8);
}

.heart-enter-active {
    animation:
        reel-heart 0.75s ease-out;
}

.heart-leave-active {
    transition:
        opacity 0.15s ease;
}

.heart-leave-to {
    opacity: 0;
}

@keyframes reel-heart {
    0% {
        opacity: 0;

        transform:
            translate(-50%, -50%) scale(0.3);
    }

    25% {
        opacity: 1;

        transform:
            translate(-50%, -50%) scale(1.25);
    }

    55% {
        transform:
            translate(-50%, -50%) scale(1);
    }

    100% {
        opacity: 0;

        transform:
            translate(-50%, -50%) scale(1.15);
    }
}

/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 1200px) {
    .comments-panel {
        width: 400px;
    }

    .reel-nav-prev {
        left: calc(50% - 265px);
    }

    .reel-nav-next {
        right: calc(50% - 265px);
    }
}

@media (max-width: 1050px) {
    .comments-panel {
        width: 370px;
    }

    .reel-nav-prev {
        left: 20px;
    }

    .reel-nav-next {
        right: 20px;
    }
}

@media (max-width: 900px) {
    .reel-stage {
        width: min(420px,
                calc(100vw - 100px));
    }

    .comments-panel {
        width: 350px;
    }

    /*
     * Keep navigation outside the reel.
     */
    .reel-nav-prev {
        left: 14px;
    }

    .reel-nav-next {
        right: 14px;
    }
}

@media (max-width: 768px) {
    .reel-viewer {
        align-items: center;
        justify-content: center;
    }

    .reel-layout {
        display: block;

        width: 100%;
        height: 100%;
    }

    .reel-stage {
        width: 100vw;
        height: 100dvh;

        max-width: none;
        max-height: none;

        border-radius: 0;

        box-shadow: none;
    }

    /*
     * Mobile comments become a bottom sheet.
     */
    .comments-panel {
        position: fixed;

        top: auto;
        left: 0;
        right: 0;
        bottom: 0;

        width: 100%;
        height: 70dvh;

        margin: 0;

        border-radius:
            18px 18px 0 0;

        transform: none;
    }

    .comments-slide-enter-from,
    .comments-slide-leave-to {
        opacity: 0;

        transform:
            translateY(100%);
    }

    /*
     * Navigation stays on the sides
     * and is never shifted by comments.
     */
    .reel-nav-prev {
        left: 10px;
    }

    .reel-nav-next {
        right: 10px;
    }

    .viewer-close {
        top: 14px;
        right: 14px;

        width: 40px;
        height: 40px;
    }

    .reel-nav-button {
        width: 42px;
        height: 42px;
    }

    .action-rail {
        right: 10px;
        bottom: 95px;

        gap: 13px;
    }

    .action-icon {
        width: 42px;
        height: 42px;
    }

    .bottom-content {
        left: 14px;
        right: 62px;
        bottom: 18px;
    }

    .caption-container {
        max-width:
            calc(100vw - 100px);
    }
}

@media (max-width: 480px) {
    .reel-nav-button {
        width: 38px;
        height: 38px;
    }

    .reel-nav-prev {
        left: 7px;
    }

    .reel-nav-next {
        right: 7px;
    }

    .action-rail {
        right: 7px;
    }

    .action-icon {
        width: 39px;
        height: 39px;
    }

    .action-button {
        font-size: 10px;
    }

    .comments-header {
        padding: 16px;
    }

    .comments-body {
        padding: 16px;
    }

    .comments-input-area {
        padding: 12px;
    }
}
</style>
