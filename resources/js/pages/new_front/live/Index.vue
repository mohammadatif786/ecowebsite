<template>
    <div class="fade" v-if="!isStudioActive">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-950">LinkUp Live</h1>
                <p class="mt-1 text-base font-bold text-slate-500">Live streams, shows &amp; podcasts from across the region</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="openAnalytics" class="btn btn-ghost flex items-center gap-2 px-4 py-2.5">
                    <i data-lucide="bar-chart-2" class="h-4 w-4"></i>
                    Analytics
                </button>
                <button
                    @click="openGoLive"
                    class="btn px-5 py-2.5 font-black text-white"
                    style="background: linear-gradient(135deg, #e11d48, #db2777)"
                >
                    ● Go Live
                </button>
            </div>
        </div>

        <div class="live-category-strip mb-5 flex gap-2 overflow-x-scroll pb-2">
            <button
                v-for="category in categories"
                :key="category"
                @click="selectedCategory = category"
                :class="['chip shrink-0', selectedCategory === category ? 'on' : '']"
            >
                {{ category }}
            </button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <button v-for="stream in filteredStreams" :key="stream.id" @click="enterStream(stream)"
                :class="['card group overflow-hidden text-left', stream.status === 'ended' ? 'opacity-60' : '']">
                    <div class="relative h-56 overflow-hidden bg-slate-950">
                        <img :src="streamImage(stream)" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-xl" aria-hidden="true" />
                        <img
                            :src="streamImage(stream)"
                            :alt="stream.title"
                            class="relative h-full w-full object-contain transition duration-500 group-hover:scale-[1.02]"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>

                    <span
                        v-if="stream.status === 'live'"
                        class="absolute top-3 left-3 flex items-center gap-1 rounded-full bg-red-600 px-2.5 py-1 text-xs font-black text-white"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                        LIVE
                    </span>
                    <span v-else-if="stream.status === 'ended'" class="absolute top-3 left-3 rounded-full bg-black/75 px-2.5 py-1 text-xs font-black text-white"> Ended </span>
                    <span v-else class="absolute top-3 left-3 rounded-full bg-slate-800 px-2.5 py-1 text-xs font-black text-white"> Upcoming </span>

                    <!-- Private/Subscribed Badge -->
                    <span v-if="stream.visibility === 'private'"
                        class="absolute top-3 left-3 mt-8 flex items-center gap-1.5 rounded-full border border-white/20 px-2.5 py-1 text-[10px] font-black backdrop-blur-md"
                        :class="stream.is_subscribed ? 'bg-emerald-500/80 text-white' : 'bg-black/40 text-white'"
                    >
                        <i :data-lucide="stream.is_subscribed ? 'check-circle' : 'lock'" class="h-3 w-3"></i>
                        {{ stream.is_subscribed ? 'SUBSCRIBED' : `PRIVATE $${Number(stream.subscription_rate || 0).toFixed(2)}` }}
                    </span>

                    <span
                        v-if="hasProducts(stream)"
                        class="text-lkink absolute top-3 right-3 flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-black"
                    >
                        <i data-lucide="shopping-bag" class="h-3 w-3"></i>
                        Shop
                    </span>

                    <div class="absolute right-3 bottom-3 left-3 text-white">
                        <p class="text-lg leading-tight font-black">{{ stream.title }}</p>
                        <p class="text-xs text-white/80">{{ stream.category }}</p>

                        <div v-if="stream.status === 'live' || stream.status === 'ended'" class="mt-1.5 flex items-center gap-3 text-xs font-bold">
                            <span class="flex items-center gap-1"><i data-lucide="users" class="h-3 w-3"></i>{{ num(stream.viewers) }}</span>
                            <span class="flex items-center gap-1"><i data-lucide="heart" class="h-3 w-3"></i>{{ num(stream.likes || 0) }}</span>
                            <span class="flex items-center gap-1"><i data-lucide="gift" class="h-3 w-3"></i>{{ num(stream.gifts || 0) }}</span>
                        </div>
                        <p v-else class="mt-1 text-xs text-white/80">{{ stream.when || 'Tonight 9:00 PM' }}</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Empty State -->
        <div v-if="filteredStreams.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-400">
            <i data-lucide="video-off" class="mb-4 h-16 w-16 opacity-20"></i>
            <p class="text-lg font-black">No streams found</p>
            <p class="text-sm font-bold">Try choosing a different category or check back later.</p>
        </div>

        <GoLiveModal ref="goLiveModalRef" :categories="categories" @startBroadcast="handleStartBroadcast" />
        <LiveEndedSummaryModal ref="liveEndedSummaryModalRef" />
    </div>

    <LiveAnalyticsModal ref="liveAnalyticsModalRef" :initial-earnings="props.serverEarnings" />

    <!-- STUDIO VIEW (HOST ONLY) -->
    <StudioView
        ref="studioViewRef"
        v-if="isStudioActive && isHost"
        :is-host="isHost"
        :joined="joined"
        :host-name="liveUserData?.name"
        :host-avatar="liveUserData?.avatar"
        :followers-count="state.followers"
        :session-coins="sessionCoins"
        :session-cash="sessionCash"
        :wallet-coins="state.coins"
        :wallet-balance="state.cash"
        :stream-title="liveStreamData?.title"
        :stream-category="liveStreamData?.broadcast_type"
        :stream-location="liveStreamData?.location || hostStreamSettings.location"
        :viewer-count="state.session?.viewer_count || 0"
        :like-count="state.session?.like_count || 0"
        :gift-count="state.session?.gift_count || 0"
        :timer="liveTimer"
        :featured-product="hostFeaturedProduct"
        :product-count="hostProducts.length"
        @leave="leaveStream"
        @end="endLiveStream"
        @openTransfer="showPayoutModal = true"
        @openShop="openHostShop"
        @open-analytics="openAnalytics"
    >
        <template #video-engine>
            <div class="absolute inset-0 z-10 flex overflow-hidden bg-black">
                <div class="relative h-full shrink-0 overflow-hidden transition-[width] duration-300" :style="{ width: guestGridWidths[0] + '%' }">
                    <LiveStreamComponent
                        ref="liveStreamComponent"
                        :stream-id="liveStreamData?.id ?? state.session?.id ?? null"
                        :is-host="isHost"
                        :playback-url="playback"
                        :stream-settings="hostStreamSettings"
                        :channel-name="channelName"
                        :show-controls="false"
                        :is-stream-live="liveStreamData?.status === 'live' || state.session?.status === 'live'"
                        :owner-id="liveStreamData?.user_id || state.session?.user_id"
                        @joined="handleStreamJoined"
                        @left="handleStreamLeft"
                        @stream-ended="handleStreamEnded"
                        @stream-data-loaded="handleStreamDataLoaded"
                        @stream-state-loaded="handleStreamStateLoaded"
                        @guest-published="handleGuestPublished"
                        @guest-unpublished="handleGuestUnpublished"
                        @local-guest-published="handleLocalGuestPublished"
                    />
                    <span class="absolute bottom-20 left-3 z-20 rounded bg-emerald-500 px-1.5 py-0.5 text-[9px] font-black text-white">HOST</span>
                </div>

                <div
                    v-for="(guest, index) in visibleJoinedGuests"
                    :key="guest.id"
                    class="relative h-full shrink-0 overflow-hidden border-l border-white/10 bg-[#16324d] transition-[width] duration-300"
                    :style="{ width: guestGridWidths[index + 1] + '%' }"
                >
                    <div :id="'guest-player-' + guest.id" class="absolute inset-0 grid place-items-center bg-[#16324d] text-sm font-black text-white">
                        <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-600">{{ initials(guest.name || guest.id) }}</span>
                    </div>
                    <button @click="removeGuest(guest.id)" class="absolute top-3 right-3 z-20 grid h-7 w-7 place-items-center rounded-full bg-black/55 text-base font-black leading-none text-white hover:bg-black" title="Remove guest" aria-label="Remove guest">
                        ×
                    </button>
                    <span class="absolute bottom-20 left-3 z-20 rounded bg-rose-600 px-1.5 py-0.5 text-[9px] font-black text-white">LIVE</span>
                    <span class="absolute right-3 bottom-20 left-3 z-20 truncate text-right text-[10px] font-black text-white/80">{{ guest.name }}</span>
                </div>

                <div v-if="joinedGuestOverflow > 0" class="absolute top-1/2 right-2 z-30 grid h-10 min-w-10 -translate-y-1/2 place-items-center rounded-full bg-black/75 px-2 text-xs font-black text-white shadow-xl">
                    +{{ joinedGuestOverflow }}
                </div>
            </div>
        </template>

        <template #guest-slots>
            <div
                v-for="guest in pendingGuests"
                :key="guest.id"
                class="relative h-14 w-11 shrink-0 overflow-hidden rounded-xl border-2 border-amber-300 bg-[#1e3a52] shadow-xl"
            >
                <div class="absolute inset-0 grid place-items-center text-[9px] font-black text-white">{{ initials(guest.name || guest.id) }}</div>
                <div class="absolute bottom-0.5 left-0.5 rounded bg-amber-400 px-1 text-[7px] font-black text-black">…</div>
            </div>
        </template>

        <template #controls>
            <button
                type="button"
                @click="openGuestSection"
                class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-white transition hover:bg-white/10"
                title="Invite guests"
            >
                <i data-lucide="user-plus" class="h-4 w-4"></i>
            </button>
            <button
                type="button"
                @click="openHostShop"
                class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-white transition hover:bg-white/10"
                title="Tag a product"
            >
                <i data-lucide="shopping-bag" class="h-4 w-4"></i>
            </button>
            <button
                @click="
                    isMuted = !isMuted;
                    liveStreamComponent?.toggleMic();
                "
                :class="[
                    'grid h-10 w-10 shrink-0 place-items-center rounded-full transition hover:bg-white/10',
                    isMuted ? 'bg-rose-500/20 text-rose-400' : 'text-white',
                ]"
                title="Mic"
            >
                <i :data-lucide="isMuted ? 'mic-off' : 'mic'" class="h-4 w-4"></i>
            </button>
            <button
                @click="liveStreamComponent?.toggleCam()"
                class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-white transition hover:bg-white/10"
                title="Camera"
            >
                <i data-lucide="camera" class="h-4 w-4"></i>
            </button>
        </template>

        <template #guests-panel>
            <div class="space-y-4">
                <div>
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-[11px] font-black tracking-wide text-white/50 uppercase">Invite by username</p>
                        <button
                            @click="fetchActiveViewers"
                            :disabled="isLoadingViewers"
                            class="grid h-8 w-8 place-items-center rounded-full bg-white/5 text-white/70 transition hover:bg-white/10 disabled:opacity-50"
                        >
                            <i data-lucide="refresh-cw" class="h-3.5 w-3.5"></i>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <input
                            v-model="guestInput"
                            @keyup.enter="inviteGuest()"
                            class="min-w-0 flex-1 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-bold text-white outline-none placeholder:text-white/35"
                            placeholder="name or @linkup_id"
                        />
                        <button
                            @click="inviteGuest()"
                            class="rounded-xl bg-amber-400 px-3 py-2 text-xs font-black text-[#0b0f2d] transition hover:bg-amber-300"
                        >
                            Invite
                        </button>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-[11px] font-black tracking-wide text-white/50 uppercase">Active viewers</p>
                    <div v-if="isLoadingViewers" class="rounded-xl bg-white/5 p-3 text-center text-xs font-bold text-white/45">Loading viewers...</div>
                    <div v-else-if="!activeViewers.length" class="rounded-xl bg-white/5 p-3 text-center text-xs font-bold text-white/45">
                        No active viewers yet.
                    </div>
                    <div v-else class="hide-scroll max-h-56 space-y-2 overflow-y-auto pr-1">
                        <div v-for="viewer in activeViewers" :key="viewer.id" class="flex items-center gap-2 rounded-xl bg-white/5 p-2">
                            <img
                                v-if="viewer.avatar"
                                :src="viewer.avatar"
                                class="h-8 w-8 shrink-0 rounded-full object-cover"
                            />
                            <div v-else class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-700 text-[10px] font-black text-white">
                                {{ initials(viewer.name || viewer.linkup_id) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-black text-white">{{ viewer.name }}</p>
                                <p class="truncate text-[10px] font-bold text-white/40">{{ viewer.linkup_id }}</p>
                            </div>
                            <button
                                @click="inviteGuest(viewer)"
                                class="rounded-lg bg-amber-400 px-2.5 py-1.5 text-[10px] font-black text-[#0b0f2d] transition hover:bg-amber-300"
                            >
                                Invite
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-[11px] font-black tracking-wide text-white/50 uppercase">Guests</p>
                    <div v-if="!state.guests.length" class="rounded-xl bg-white/5 p-3 text-center text-xs font-bold text-white/45">No guests invited.</div>
                    <div v-else class="space-y-2">
                        <div v-for="guest in state.guests" :key="guest.id" class="flex items-center gap-2 rounded-xl bg-white/5 p-2">
                            <img v-if="guest.avatar" :src="guest.avatar" class="h-8 w-8 shrink-0 rounded-full object-cover" />
                            <div v-else class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-700 text-[10px] font-black text-white">
                                {{ initials(guest.name || guest.id) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-black text-white">{{ guest.name || guest.id }}</p>
                                <p class="text-[10px] font-black uppercase" :class="guest.status === 'joined' ? 'text-emerald-300' : 'text-amber-300'">
                                    {{ guest.status || 'pending' }}
                                </p>
                            </div>
                            <button
                                @click="removeGuest(guest.id)"
                                class="shrink-0 rounded-lg bg-rose-500/15 px-2.5 py-1.5 text-[10px] font-black text-rose-300 transition hover:bg-rose-500/25"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #chat>
            <ChatComponent :stream-id="currentStreamId" :is-host="isHost" :session-event="sessionEvent" v-if="currentStreamId" />
        </template>

        <template #qna>
            <QnaComponent :stream-id="currentStreamId" :is-host="isHost" :session-event="sessionEvent" v-if="currentStreamId" />
        </template>

        <template #polls>
            <PollComponent :stream-id="currentStreamId" :is-host="isHost" :session-event="sessionEvent" v-if="currentStreamId" />
        </template>

        <template #gifts>
            <div class="grid max-h-48 grid-cols-4 gap-2 overflow-y-auto pr-1">
                <div
                    v-for="gift in giftItems"
                    :key="gift.id"
                    @click="sendGiftItem(gift)"
                    class="transform cursor-pointer rounded-xl border border-transparent bg-white/5 p-2 text-center transition hover:border-amber-400 hover:bg-white/10 active:scale-95"
                >
                    <div class="mb-1 text-xl">{{ gift.emoji }}</div>
                    <div class="flex items-center justify-center gap-0.5 text-[9px] font-black text-amber-400">
                        <i data-lucide="gem" class="h-2.5 w-2.5"></i> {{ gift.coins }}
                    </div>
                </div>
            </div>
        </template>

        <template #overlays>
            <!-- Gift Animations -->
            <div
                v-for="animation in giftAnimations"
                :key="animation.id"
                class="gift-animation"
                :style="{
                    top: `${animation.top}%`,
                    left: `${animation.progress}%`,
                    animation: `flyAcross ${animation.duration}s linear forwards`,
                }"
            >
                {{ animation.emoji }}
            </div>
            <!-- Floating Reactions -->
            <div v-for="floater in floaters" :key="floater.id" class="floater" :style="{ left: floater.left + '%', bottom: '100px' }">
                {{ floater.emoji }}
            </div>
        </template>
    </StudioView>

    <!-- VIEWER VIEW (IMMERSIVE - IMAGE 1) -->
    <div v-else-if="isStudioActive && !isHost" class="fixed inset-0 z-[100] bg-black">
        <LiveViewerOverlay
            :session="viewerSessionData"
            :chat="chatMessages"
            :featured-product="viewerFeaturedProduct"
            :hasVideo="true"
            @close="leaveStream"
            @sendChat="sendChatMessage"
            @sendHeart="sendReaction('heart')"
            @openGift="showGiftModal = true"
            @openShop="openViewerShop"
            @follow="toggleFollow"
        >
            <LiveStreamComponent
                ref="liveStreamComponent"
                :stream-id="liveStreamData?.id ?? state.session?.id ?? null"
                :is-host="isHost"
                :playback-url="playback"
                :channel-name="channelName"
                :show-controls="false"
                :is-stream-live="liveStreamData?.status === 'live' || state.session?.status === 'live'"
                :owner-id="liveStreamData?.user_id || state.session?.user_id"
                @joined="handleStreamJoined"
                @left="handleStreamLeft"
                @stream-ended="handleStreamEnded"
                @stream-data-loaded="handleStreamDataLoaded"
                @stream-state-loaded="handleStreamStateLoaded"
                @guest-published="handleGuestPublished"
                @guest-unpublished="handleGuestUnpublished"
                @local-guest-published="handleLocalGuestPublished"
            />
        </LiveViewerOverlay>

        <LiveGiftModal
            :open="showGiftModal"
            :balance="state.coins"
            :gifts="viewerGiftItems"
            :is-sending="isSendingGift"
            @close="showGiftModal = false"
            @select="sendGiftItem"
        />

        <LiveShopModal ref="viewerShopModalRef" />
    </div>

    <LiveShopModal ref="hostShopModalRef" :can-manage="true" @feature="featureHostProduct" @remove="removeHostProduct" @add="openHostTagPicker" />
    <TagPickerModal ref="hostTagPickerRef" @pick="addHostProduct" />

    <!-- Payout Modal -->
    <div v-if="showPayoutModal" class="fixed inset-0 z-[200] grid place-items-center bg-black/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/5 p-6">
                <h2 class="text-xl font-black text-white">Transfer to Wallet</h2>
                <button @click="showPayoutModal = false" class="text-slate-400 transition hover:text-white">
                    <i data-lucide="x" class="h-6 w-6"></i>
                </button>
            </div>
            <div class="space-y-4 p-6">
                <div class="rounded-2xl border border-blue-500/20 bg-blue-500/10 p-4">
                    <p class="text-xs leading-relaxed font-bold text-blue-300">
                        LinkUp Live payouts are split 50/50. 50% goes to your wallet, and LinkUp keeps 50%. Minimum cash-out is $50.00.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                        <p class="mb-1 text-[10px] font-black tracking-widest text-slate-400 uppercase">Available</p>
                        <p class="text-2xl font-black text-emerald-400">${{ sessionCash.toFixed(2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                        <p class="mb-1 text-[10px] font-black tracking-widest text-slate-400 uppercase">You Receive</p>
                        <p class="text-2xl font-black text-white">${{ (sessionCash * 0.5).toFixed(2) }}</p>
                    </div>
                </div>

                <button
                    @click="confirmTransfer"
                    :disabled="isTransferring || sessionCash < 50"
                    class="w-full transform rounded-2xl bg-emerald-500 py-4 font-black text-white shadow-xl transition hover:bg-emerald-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ isTransferring ? 'Transferring...' : 'Confirm Transfer' }}
                </button>
            </div>
        </div>
    </div>

    <Toaster position="top-center" />

    <SubscriptionModal
        :visible="showSubscriptionModal"
        :creator="selectedStreamForSub?.host ? '@' + selectedStreamForSub.host : null"
        :amount="selectedStreamForSub?.subscription_rate"
        @close="showSubscriptionModal = false"
        @continue="handleSubscriptionContinue"
    />
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import LiveGiftModal from '../../../components/new_frontend/live/LiveGiftModal.vue';
import LiveShopModal from '../../../components/new_frontend/modals/LiveShopModal.vue';
import LiveViewerOverlay from '../../../components/new_frontend/live/LiveViewerOverlay.vue';
import GoLiveModal from '../../../components/new_frontend/modals/GoLiveModal.vue';
import LiveAnalyticsModal from '../../../components/new_frontend/modals/LiveAnalyticsModal.vue';
import TagPickerModal from '../../../components/new_frontend/modals/TagPickerModal.vue';
import LiveEndedSummaryModal from '../../../components/new_frontend/modals/LiveEndedSummaryModal.vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import ChatComponent from '../../User/GoLive/Components/ChatComponent.vue';
import LiveStreamComponent from '../../User/GoLive/Components/LiveStreamComponent.vue';
import PollComponent from '../../User/GoLive/Components/PollComponent.vue';
import QnaComponent from '../../User/GoLive/Components/QnaComponent.vue';
import SubscriptionModal from '../../User/GoLive/Components/SubscriptionModal.vue';
import StudioView from './Components/StudioView.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    serverStreams: { type: Array, default: () => [] },
    serverCategories: { type: Array, default: () => ['All'] },
    serverGifts: { type: Array, default: () => [] },
    balance: { type: Number, default: 0 },
    serverEarnings: { type: Object, default: () => ({ collected: 0, transferred: 0 }) },
    auth: { type: Object, default: () => ({ user: null }) },
});

const user = computed(() => props.auth.user);

// Studio State
const isStudioActive = ref(false);
const isHost = ref(false);
const joined = ref(false);
const isMuted = ref(false);
const liveStreamData = ref({});
const liveUserData = ref(null);
const playback = ref(null);
const channelName = ref('');
const liveStreamComponent = ref(null);
const currentStreamId = computed(() => liveStreamData.value?.id ?? state.session?.id ?? null);

const hostStreamSettings = ref({
    title: '',
    broadcastType: 'Entertainment',
    visibility: 'public',
    location: '',
    baseResolution: '1920x1080',
    outputResolution: '1280x720',
    downscaleFilter: 'bicubic',
    coverImage: null,
    subscriptionRate: 0,
    products: [],
});

const state = reactive({
    coins: user.value?.coins ?? 0,
    cash: props.balance ?? 0,
    session: null,
    guests: [],
    followers: 0,
});

const sessionCoins = ref(0);
const sessionCash = ref(0);
const liveTimer = ref('00:00:00');
let timerInterval = null;
let liveStartTime = 0;
let hostHeartbeatInterval = null;
let hostLifecycleStreamId = null;

const showPayoutModal = ref(false);
const showGiftModal = ref(false);
const showSubscriptionModal = ref(false);
const selectedStreamForSub = ref(null);
const liveEndedSummaryModalRef = ref(null);
const studioViewRef = ref(null);
const isTransferring = ref(false);
const payoutAttemptKey = ref(null);
const isSendingGift = ref(false);

const giftAnimations = ref([]);
const floaters = ref([]);
const isFollowing = ref(false);

const chatMessages = ref([]);
const sessionEvent = ref(null);
const guestInput = ref('');
const activeViewers = ref([]);
const isLoadingViewers = ref(false);
const GUEST_GRID_WIDTHS = {
    1: [100],
    2: [60, 40],
    3: [50, 25, 25],
    4: [40, 20, 20, 20],
};
const GUEST_GRID_MAX_VISIBLE = 3;
const joinedGuests = computed(() => state.guests.filter((guest) => guest.status === 'joined'));
const visibleJoinedGuests = computed(() => joinedGuests.value.slice(0, GUEST_GRID_MAX_VISIBLE));
const pendingGuests = computed(() => state.guests.filter((guest) => guest.status === 'pending'));
const joinedGuestOverflow = computed(() => Math.max(0, joinedGuests.value.length - GUEST_GRID_MAX_VISIBLE));
const guestGridWidths = computed(() => GUEST_GRID_WIDTHS[visibleJoinedGuests.value.length + 1] || GUEST_GRID_WIDTHS[1]);
const guestVideoTracks = new Map();

const initials = (value) =>
    String(value || '?')
        .replace('@', '')
        .slice(0, 2)
        .toUpperCase();

const viewerSessionData = computed(() => ({
    host: liveUserData.value?.name || 'Broadcaster',
    hostAvatar: liveUserData.value?.avatar,
    hearts: state.session?.like_count || 0,
    viewers: state.session?.viewer_count || 0,
    category: liveStreamData.value?.broadcast_type || 'Entertainment',
    coins: sessionCoins.value,
    isFollowing: isFollowing.value,
    cover: liveStreamData.value?.cover_image_url || liveStreamData.value?.image_url,
    products: liveStreamData.value?.products || [],
}));

const viewerFeaturedProduct = computed(() => viewerSessionData.value.products.find((product) => product.featured !== false) || null);

const fallbackGiftItems = [
    { id: 1, emoji: '🧩', name: 'Dominoes', coins: 10 },
    { id: 2, emoji: '🌶️', name: 'Pepper', coins: 15 },
    { id: 3, emoji: '🥥', name: 'Coconut', coins: 20 },
    { id: 5, emoji: '🌮', name: 'Tacos', coins: 50 },
    { id: 7, emoji: '🍹', name: 'Sex on Beach', coins: 100 },
    { id: 9, emoji: '🍗', name: 'Jerk Chicken', coins: 200 },
    { id: 11, emoji: '🏖️', name: 'Beach', coins: 500 },
    { id: 13, emoji: '👑', name: 'Carnival King', coins: 1000 },
];

const fallbackViewerGiftItems = [
    { id: 1, emoji: '👏', name: 'Applause', coins: 5 },
    { id: 2, emoji: '📣', name: 'Shoutout', coins: 25 },
    { id: 3, emoji: '🎤', name: 'Mic Drop', coins: 50 },
    { id: 4, emoji: '💡', name: 'Spotlight', coins: 100 },
    { id: 5, emoji: '🥁', name: 'Riddim Section', coins: 200 },
    { id: 6, emoji: '🌟', name: 'Soca Star', coins: 350 },
    { id: 7, emoji: '🎉', name: 'Confetti Drop', coins: 500 },
    { id: 8, emoji: '🔥', name: 'Fyah Pon Stage', coins: 1000 },
    { id: 9, emoji: '👑', name: 'Carnival Crown', coins: 2000 },
];

// The server is the only source of gift IDs and coin prices. Never submit a
// locally invented gift ID when the database catalogue is empty.
const giftItems = computed(() => props.serverGifts);
const viewerGiftItems = computed(() => props.serverGifts);

// Methods
const num = (n) => Number(n || 0).toLocaleString();
const hasProducts = (stream) => Boolean(stream.products?.length);
const streamImage = (stream) => stream.cover || 'https://via.placeholder.com/900x600?text=Live+Stream';

const normalizeStream = (stream) => {
    // Robust visibility check
    let visibility = 'public';
    const rawVis = stream.visibility || stream.visibility_status || stream.status_visibility;
    if (rawVis) {
        visibility = String(rawVis).toLowerCase();
    } else if (stream.settings?.visibility) {
        visibility = String(stream.settings.visibility).toLowerCase();
    }

    const userId = stream.user_id || stream.userId || stream.host_id || (stream.user ? stream.user.id : null);

    const rawStatus = String(stream.status || '').toLowerCase();

    return {
        id: stream.id,
        public_id: stream.public_id || stream.publicId || null,
        user_id: userId,
        title: stream.title || 'Untitled Stream',
        host: stream.host || stream.user?.name || 'Host',
        hostAvatar: stream.hostAvatar || stream.user?.avatar || null,
        category: stream.category || stream.broadcast_type || 'Just Chatting',
        status: ['completed', 'ended'].includes(rawStatus) ? 'ended' : (rawStatus === 'live' ? 'live' : 'upcoming'),
        viewers: Number(stream.viewers || stream.viewer_count || 0),
        likes: Number(stream.likes || stream.like_count || 0),
        gifts: Number(stream.gifts || stream.gift_count || 0),
        cover: stream.cover || stream.image_url || stream.cover_image_url || null,
        when: stream.when || 'Upcoming',
        started_at: stream.started_at || null,
        ended_at: stream.ended_at || null,
        ended_expires_at: stream.ended_expires_at || null,
        duration_seconds: Number(stream.duration_seconds || 0),
        products: stream.products || [],
        visibility: visibility,
        subscription_rate: Number(stream.subscription_rate || 0),
        is_subscribed: stream.is_subscribed === true || stream.is_subscribed === 1 || String(stream.is_subscribed) === '1',
    };
};

const replaceStreamInList = (stream) => {
    const normalized = normalizeStream(stream);
    const index = liveStreams.value.findIndex((item) => String(item.id) === String(normalized.id));

    if (index >= 0) {
        const existing = liveStreams.value[index];
        // If the update has no visibility but the existing one does, preserve the existing visibility
        if (normalized.visibility === 'public' && existing.visibility === 'private' && !stream.visibility) {
            normalized.visibility = 'private';
        }
        liveStreams.value.splice(index, 1, { ...existing, ...normalized });
    } else {
        liveStreams.value.unshift(normalized);
    }
};

// Listing State
const liveCategoryFallback = [
    'All',
    'Private',
    'Adult / Mature',
    'Afrobeats & Amapiano',
    'Carnival Mixers',
    'News · Sports',
    'Just Chatting',
    'Comedy & Skits',
    'Education & Growth',
    'Business & Money',
];

const categories = computed(() => {
    const fromServer = (props.serverCategories || []).map((category) => String(category || '').trim()).filter(Boolean);
    return [...new Set([...liveCategoryFallback, ...fromServer])];
});

const selectedCategory = ref('All');
const goLiveModalRef = ref(null);
const liveAnalyticsModalRef = ref(null);
const viewerShopModalRef = ref(null);
const hostShopModalRef = ref(null);
const hostTagPickerRef = ref(null);
const liveStreams = ref(props.serverStreams.map(normalizeStream));

const hostProducts = computed(() => liveStreamData.value?.products || hostStreamSettings.value.products || []);
const hostFeaturedProduct = computed(() => hostProducts.value.find((product) => product.featured !== false) || null);

const streams = computed(() => liveStreams.value);

const filteredStreams = computed(() => {
    if (selectedCategory.value === 'All') return streams.value;
    if (selectedCategory.value === 'Private') {
        return streams.value.filter((stream) => stream.visibility === 'private');
    }
    return streams.value.filter((stream) => String(stream.category || '').toLowerCase() === selectedCategory.value.toLowerCase());
});

const wait = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

const isLocalLiveDebug = () => ['localhost', '127.0.0.1'].includes(window.location.hostname);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const sendHostEndBeacon = (streamId = hostLifecycleStreamId) => {
    if (!streamId) return;

    const formData = new FormData();
    formData.append('_token', csrfToken());

    if (navigator.sendBeacon) {
        navigator.sendBeacon(route('new_frontend.live.end', { stream: streamId }), formData);
        return;
    }

    fetch(route('new_frontend.live.end', { stream: streamId }), {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
        },
    }).catch(() => {});
};

const handleHostPageExit = () => {
    if (isHost.value && joined.value && hostLifecycleStreamId) {
        sendHostEndBeacon(hostLifecycleStreamId);
    }
};

const stopHostLifecycle = () => {
    if (hostHeartbeatInterval) {
        window.clearInterval(hostHeartbeatInterval);
        hostHeartbeatInterval = null;
    }

    window.removeEventListener('pagehide', handleHostPageExit);
    window.removeEventListener('beforeunload', handleHostPageExit);
    hostLifecycleStreamId = null;
};

const sendHostHeartbeat = async () => {
    if (!hostLifecycleStreamId || !isHost.value || !joined.value) return;

    try {
        await axios.post(route('frontend.live.host.heartbeat', { stream: hostLifecycleStreamId }));
    } catch (error) {
        console.warn('Host heartbeat failed:', error);
    }
};

const startHostLifecycle = (streamId) => {
    stopHostLifecycle();
    hostLifecycleStreamId = streamId;
    sendHostHeartbeat();
    hostHeartbeatInterval = window.setInterval(sendHostHeartbeat, 30000);
    window.addEventListener('pagehide', handleHostPageExit);
    window.addEventListener('beforeunload', handleHostPageExit);
};

const completeHostStreamOnServer = async (streamId = hostLifecycleStreamId) => {
    if (!streamId) return;

    stopHostLifecycle();
    await axios.post(route('new_frontend.live.end', { stream: streamId }));
};

const removeStreamFromList = (streamId) => {
    liveStreams.value = liveStreams.value.filter((stream) => String(stream.id) !== String(streamId));
};

const refreshLiveStreams = () => {
    router.reload({
        only: ['serverStreams'],
        preserveState: true,
        preserveScroll: true,
    });
};

const sendChatMessage = async (text) => {
    if (!currentStreamId.value) return;
    try {
        await axios.post(route('frontend.live.comment', { stream: currentStreamId.value }), { text });
    } catch (err) {
        toast.error('Failed to send message');
    }
};

const toggleFollow = async () => {
    const creatorId = liveUserData.value?.id;
    if (!creatorId) return;

    try {
        const response = await axios.post(`/live/stream/${creatorId}/follow`);
        if (response.data.success) {
            isFollowing.value = response.data.is_following;
            state.followers = response.data.count;
            isFollowing.value ? toast.success('Followed!') : toast.info('Unfollowed');
        }
    } catch (error) {
        toast.error('Failed to update follow status');
    }
};

const sendReaction = async (type) => {
    if (!currentStreamId.value) return;

    try {
        const response = await axios.post(route('frontend.live.reaction', { stream: currentStreamId.value }), { type });
        if (response.data?.success && state.session) {
            state.session.like_count = response.data.like_count || state.session.like_count || 0;
        }
        spawnFloater(type === 'heart' ? '❤️' : '🍾');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to send reaction');
    }
};

const fetchActiveViewers = async () => {
    if (!currentStreamId.value) return;

    isLoadingViewers.value = true;
    try {
        const response = await axios.get(route('frontend.live.viewers', { stream: currentStreamId.value }));
        if (response.data?.success) {
            activeViewers.value = (response.data.viewers || []).filter((viewer) => String(viewer?.id) !== String(user.value?.id));
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to load viewers');
    } finally {
        isLoadingViewers.value = false;
    }
};

const openGuestSection = () => {
    studioViewRef.value?.openGuests();
    fetchActiveViewers();
};

const inviteGuest = async (targetUser = null) => {
    if (!currentStreamId.value) return;

    const username = String(targetUser?.linkup_id || targetUser?.name || guestInput.value || '').trim();
    if (!username) {
        toast.error('Please enter a username or select a viewer');
        return;
    }

    const guestId = targetUser?.id || username;
    if (state.guests.some((guest) => String(guest.id) === String(guestId) || String(guest.name) === username)) {
        toast.info('This user is already invited');
        return;
    }

    try {
        const response = await axios.post(route('frontend.live.invite', { stream: currentStreamId.value }), { username });
        if (response.data?.success) {
            const invitedGuest = response.data.guest || {
                id: guestId,
                name: targetUser?.name || username,
                avatar: targetUser?.avatar || null,
                status: 'pending',
            };
            if (!state.guests.some((guest) => String(guest.id) === String(invitedGuest.id))) {
                state.guests.push(invitedGuest);
            }
            guestInput.value = '';
            toast.success(response.data.message || `${username} invited`);
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'Invitation failed');
    }
};

const enterStream = async (stream) => {
    if (stream.status === 'ended') {
        if (stream.visibility === 'private' && !stream.is_subscribed && String(stream.user_id) !== String(user.value?.id)) {
            toast.info('This private stream has ended.');
            return;
        }

        liveEndedSummaryModalRef.value?.open?.(stream);
        return;
    }

    // Check if private and NOT subscribed
    const isPrivate = stream.visibility === 'private';

    if (isPrivate && !stream.is_subscribed && String(stream.user_id) !== String(user.value?.id)) {
        selectedStreamForSub.value = stream;
        showSubscriptionModal.value = true;
        return;
    }

    // Navigate to the watch page which is designed to handle audience viewing
    // and Agora initialization correctly.
    const publicId = stream.public_id || stream.publicId;
    if (!publicId) {
        console.error('Live stream is missing public_id.', stream);
        toast.error('This stream link is unavailable. Please refresh and try again.');
        return;
    }

    window.location.href = route('new_frontend.live.watch', publicId);
};

const handleSubscriptionContinue = async (payload) => {
    if (!selectedStreamForSub.value?.id) return;

    try {
        const response = await axios.post(route('frontend.live.subscribe', {
            stream: selectedStreamForSub.value.id
        }), {
            plan: payload.plan
        });

        if (response.data.url) {
            window.location.href = response.data.url;
        } else {
            toast.error('Failed to initiate payment');
        }
    } catch (error) {
        console.error('Subscription error:', error);
        toast.error(error.response?.data?.error || 'Subscription failed');
    }
};

const handleStartBroadcast = async (data) => {
    isStudioActive.value = true;
    isHost.value = true;
    // liveStreamData.value will be populated by handleStreamJoined(streamId)
    // after LiveStreamComponent creates the stream on the backend.
    liveUserData.value = user.value;

    // Set host settings for LiveStreamComponent
    hostStreamSettings.value = {
        title: data.title,
        broadcastType: data.cat,
        visibility: data.visibility.toLowerCase(),
        location: data.loc,
        baseResolution: data.base,
        outputResolution: data.out,
        downscaleFilter: 'bicubic',
        coverImage: data.coverImage || null,
        subscriptionRate: data.subscription_rate || 0,
        products: data.products || [],
    };

    await nextTick();

    let attempts = 0;
    while (!liveStreamComponent.value && attempts < 30) {
        await wait(50);
        attempts++;
    }

    if (!liveStreamComponent.value) {
        toast.error('Unable to open the live camera. Please try again.');
        isStudioActive.value = false;
        return;
    }

    // We pass null for channelName to let start-agora generate it.
    await liveStreamComponent.value.joinStream(null, 'host');
};

const leaveStream = async () => {
    const streamId = currentStreamId.value;

    if (isHost.value && streamId) {
        try {
            await completeHostStreamOnServer(streamId);
        } catch (error) {
            toast.error('Unable to end stream on the server');
        }
    }

    if (liveStreamComponent.value) {
        await liveStreamComponent.value.leaveStream();
    }
    isStudioActive.value = false;
    stopTimer();
    stopStatsPolling();
    refreshLiveStreams();
};

const endLiveStream = async () => {
    const streamId = liveStreamData.value?.id || state.session?.id;

    if (streamId) {
        try {
            await completeHostStreamOnServer(streamId);
        } catch (e) {
            toast.error('Unable to end stream on the server');
        }
    }

    if (liveStreamComponent.value) {
        await liveStreamComponent.value.endStream();
    }

    isStudioActive.value = false;
    refreshLiveStreams();
    toast.success('Stream ended');
};

const handleStreamJoined = async (streamId) => {
    joined.value = true;
    if (isHost.value) {
        if (streamId) {
            // The list is server-rendered and is a safe fallback if a legacy
            // details response does not include public_id.
            const listedStream = liveStreams.value.find((stream) => String(stream.id) === String(streamId));
            liveStreamData.value = {
                ...liveStreamData.value,
                ...(listedStream || {}),
                id: streamId,
                public_id: liveStreamData.value?.public_id || listedStream?.public_id || listedStream?.publicId || null,
            };
            startHostLifecycle(streamId);

            try {
                const result = await liveStreamComponent.value?.fetchLiveStreamDetail(streamId);
                if (result) {
                    handleStreamDataLoaded(result);
                }
            } catch (error) {
                liveStreamData.value = {
                    ...liveStreamData.value,
                    id: streamId,
                    public_id: liveStreamData.value?.public_id || listedStream?.public_id || listedStream?.publicId || null,
                    title: hostStreamSettings.value.title || 'Live Stream',
                    broadcast_type: hostStreamSettings.value.broadcastType,
                    location: hostStreamSettings.value.location,
                    status: 'live',
                    user_id: user.value?.id,
                };
            }

            if (!state.session || String(state.session.id || '') !== String(streamId)) {
                state.session = {
                    id: streamId,
                    user_id: user.value?.id,
                    host_id: String(user.value?.id || ''),
                    status: 'live',
                    viewer_count: 0,
                    like_count: 0,
                    gift_count: 0,
                    guests: [],
                };
            }
        }

        startTimer();
        startStatsPolling();
        fetchActiveViewers();
    }
};

const handleStreamLeft = () => {
    joined.value = false;
};

const handleStreamEnded = () => {
    joined.value = false;
    isStudioActive.value = false;
    stopHostLifecycle();
    refreshLiveStreams();
};

const handleStreamDataLoaded = (data) => {
    const incomingStream = data.stream || {};
    liveStreamData.value = {
        ...liveStreamData.value,
        ...incomingStream,
        // Legacy detail endpoints do not always serialize public_id. Keep the
        // canonical value returned by the start endpoint instead of erasing it.
        public_id: incomingStream.public_id || incomingStream.publicId || liveStreamData.value?.public_id || null,
    };
    if (data.user) liveUserData.value = data.user;
    if (data.playback !== undefined && data.playback !== null) playback.value = data.playback;
};

const handleStreamStateLoaded = (sessionState) => {
    state.session = {
        ...state.session,
        ...sessionState,
        id: liveStreamData.value?.id || state.session?.id,
        user_id: liveStreamData.value?.user_id || state.session?.user_id,
        host_id: String(liveStreamData.value?.user_id || state.session?.host_id || ''),
    };
    if (sessionState.guests) {
        state.guests = sessionState.guests.map((g) => ({ ...g, status: 'joined' }));
    }
};

const handleGuestPublished = (data) => {
    guestVideoTracks.set(String(data.uid), data.track);
    const playGuestTrack = async () => {
        let attempts = 0;
        let container = null;
        while (!container && attempts < 30) {
            await nextTick();
            container = document.getElementById('guest-player-' + data.uid);
            if (!container) await wait(100);
            attempts++;
        }

        if (container) {
            container.innerHTML = '';
            data.track.play(container);
        }
    };

    playGuestTrack();
};

const handleGuestUnpublished = (uid) => {
    guestVideoTracks.delete(String(uid));
    const containerId = 'guest-player-' + uid;
    const container = document.getElementById(containerId);
    if (container) {
        const guest = state.guests.find((g) => String(g.id) === String(uid));
        container.innerHTML = (guest?.name || String(uid)).substring(0, 2).toUpperCase();
    }
};

const handleLocalGuestPublished = (track) => {
    if (user.value?.id) {
        handleGuestPublished({ uid: user.value.id, track });
    }
};

// Real-time & Stats
const forwardSessionEvent = (name, payload) => {
    if (isLocalLiveDebug()) console.info('[Live Echo][Host] event received', name, payload);
    sessionEvent.value = { name, payload };
};

let subscribedLiveSessionPublicId = null;

const subscribeToLiveChannel = (publicId = liveStreamData.value?.public_id) => {
    if (!publicId || !window.Echo) {
        if (isLocalLiveDebug()) console.error('[Live Echo][Host] unavailable subscription input', {
            hasEcho: Boolean(window.Echo), publicId,
        });
        return;
    }

    const normalizedPublicId = String(publicId);
    if (subscribedLiveSessionPublicId === normalizedPublicId) return;

    if (subscribedLiveSessionPublicId) {
        window.Echo.leave('live-stream.' + subscribedLiveSessionPublicId);
    }

    subscribedLiveSessionPublicId = normalizedPublicId;
    const channel = 'live-stream.' + normalizedPublicId;
    if (isLocalLiveDebug()) console.info('[Live Echo][Host] subscribing', channel);

    window.Echo.private(channel)
        .listen('.LiveCommentPosted', (event) => {
            forwardSessionEvent('LiveCommentPosted', event);
            const newMessage = {
                who: event.user?.name || event.from || 'User',
                msg: event.text,
            };
            chatMessages.value.push(newMessage);
            if (chatMessages.value.length > 50) chatMessages.value.shift();
        })
        .listen('.LiveGiftSent', (event) => {
            forwardSessionEvent('LiveGiftSent', event);
            if (event.gift?.emoji) animateGift(event.gift.emoji);
            if (state.session) state.session.gift_count = event.gift_count || 0;
            if (isHost.value && event.gift) {
                sessionCoins.value += event.gift.coins || 0;
                sessionCash.value += (event.gift.coins || 0) * 0.01;
            }
            confetti({ particleCount: 20, spread: 40, origin: { x: 0.9, y: 0.8 } });
        })
        .listen('.LiveReactionSent', (event) => {
            forwardSessionEvent('LiveReactionSent', event);
            if (state.session) state.session.like_count = event.like_count || 0;
            if (event.emoji) spawnFloater(event.emoji);
        })
        .listen('.ViewerCountUpdated', (event) => {
            forwardSessionEvent('ViewerCountUpdated', event);
            if (state.session) state.session.viewer_count = event.viewer_count || 0;
            if (isHost.value) fetchActiveViewers();
        })
        .listen('.LiveInviteReply', (event) => {
            forwardSessionEvent('LiveInviteReply', event);
            const guestId = event.user?.id;
            if (!guestId) return;

            const guest = state.guests.find((item) => String(item.id) === String(guestId));
            if (event.accepted) {
                if (guest) {
                    guest.status = 'joined';
                    guest.name = event.user.name || guest.name;
                    guest.avatar = event.user.avatar || guest.avatar;
                } else {
                    state.guests.push({
                        id: guestId,
                        name: event.user.name,
                        avatar: event.user.avatar,
                        status: 'joined',
                    });
                }
                if (isHost.value) toast.success(`${event.user.name || 'Guest'} accepted invitation`);
            } else {
                state.guests = state.guests.filter((item) => String(item.id) !== String(guestId));
                if (isHost.value) toast.info(`${event.user.name || 'Guest'} declined invitation`);
            }
        })
        .listen('.LiveGuestRemoved', (event) => {
            forwardSessionEvent('LiveGuestRemoved', event);
            state.guests = state.guests.filter((g) => String(g.id) !== String(event.guest_id));
            if (user.value?.id && String(user.value.id) === String(event.guest_id)) {
                toast.error('You were removed from the stream');
                leaveStream();
            }
        })
        .listen('.StreamEnded', () => {
            forwardSessionEvent('StreamEnded', {});
            leaveStream();
        })
        .listen('.QnaSubmitted', (event) => forwardSessionEvent('QnaSubmitted', event))
        .listen('.QnaAnswered', (event) => forwardSessionEvent('QnaAnswered', event))
        .listen('.QnaDeleted', (event) => forwardSessionEvent('QnaDeleted', event))
        .listen('.PollCreated', (event) => forwardSessionEvent('PollCreated', event))
        .listen('.PollVoted', (event) => forwardSessionEvent('PollVoted', event))
        .listen('.PollEnded', (event) => forwardSessionEvent('PollEnded', event))
        .listen('.LiveProductsUpdated', (event) => {
            if (liveStreamData.value) liveStreamData.value.products = event.products || [];
            hostStreamSettings.value.products = event.products || [];
        })
        .subscribed(() => {
            if (isLocalLiveDebug()) console.info('[Live Echo][Host] subscribed', channel);
        })
        .error((error) => {
            subscribedLiveSessionPublicId = null;
            if (isLocalLiveDebug()) console.error('[Live Echo][Host] subscription/authentication failed', channel, error);
        });
};

const statsPollingInterval = ref(null);
const fetchFollowerCount = async () => {
    const targetId = isHost.value ? user.value?.id : liveUserData.value?.id;
    if (!targetId) return;
    try {
        const response = await axios.get(route('frontend.live.stream.followers', { stream: targetId }));
        if (response.data?.success) {
            state.followers = response.data.count || 0;
        }
    } catch (error) {
        console.warn('Failed to fetch follower count:', error);
    }
};

const startStatsPolling = () => {
    if (statsPollingInterval.value) return;
    fetchFollowerCount();
    statsPollingInterval.value = setInterval(async () => {
        if (!liveStreamData.value?.id) return;
        try {
            const response = await axios.get(route('frontend.live.stats', { stream: liveStreamData.value.id }));
            if (response.data?.ok && state.session) {
                state.session.viewer_count = response.data.viewer_count || 0;
                state.session.gift_count = response.data.gift_count || 0;
                state.session.like_count = response.data.like_count || 0;
                if (isHost.value) {
                    sessionCoins.value = response.data.session_coins || 0;
                    sessionCash.value = Number(response.data.session_cash || 0);
                    fetchActiveViewers();
                    fetchFollowerCount();
                }
            }
        } catch {}
    }, 10000);
};

const stopStatsPolling = () => {
    if (statsPollingInterval.value) {
        clearInterval(statsPollingInterval.value);
        statsPollingInterval.value = null;
    }
};

const startTimer = () => {
    liveStartTime = Date.now();
    timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - liveStartTime) / 1000);
        const h = Math.floor(elapsed / 3600)
            .toString()
            .padStart(2, '0');
        const m = Math.floor((elapsed % 3600) / 60)
            .toString()
            .padStart(2, '0');
        const s = (elapsed % 60).toString().padStart(2, '0');
        liveTimer.value = `${h}:${m}:${s}`;
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval) clearInterval(timerInterval);
    liveTimer.value = '00:00:00';
};

const sendGiftItem = async (gift) => {
    if (!joined.value || isSendingGift.value) return;

    isSendingGift.value = true;
    try {
        const response = await axios.post(route('frontend.live.gift', { stream: currentStreamId.value }), {
            gift: { id: gift.id }, qty: 1, idempotency_key: crypto.randomUUID(),
        });
        state.coins = Number(response.data?.remaining_coins ?? state.coins - gift.coins);
        showGiftModal.value = false;
        toast.success(`${gift.name} sent!`);
        return true;
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gift failed');
        return false;
    } finally {
        isSendingGift.value = false;
    }
};

const confirmTransfer = async () => {
    if (!currentStreamId.value || sessionCash.value < 50) return;
    isTransferring.value = true;
    try {
        payoutAttemptKey.value ||= crypto.randomUUID();
        const response = await axios.post(route('frontend.live.transfer-earnings', { stream: currentStreamId.value }), {
            idempotency_key: payoutAttemptKey.value,
        });
        if (response.data.success) {
            payoutAttemptKey.value = null;
            state.cash = Number(response.data.new_balance ?? state.cash);
            sessionCash.value = 0;
            sessionCoins.value = 0;
            showPayoutModal.value = false;
            toast.success('Earnings transferred!');
        }
    } catch (error) {
        toast.error('Transfer failed');
    } finally {
        isTransferring.value = false;
    }
};

const animateGift = (emoji) => {
    const animation = {
        id: Math.random().toString(36).substring(7),
        emoji,
        top: Math.random() * 70 + 15,
        progress: 100,
        duration: 1.5 + Math.random(),
    };
    giftAnimations.value.push(animation);
    setTimeout(() => {
        giftAnimations.value = giftAnimations.value.filter((a) => a.id !== animation.id);
    }, animation.duration * 1000);
};

const spawnFloater = (emoji) => {
    const id = Math.random().toString(36).substring(7);
    floaters.value.push({ id, emoji, left: Math.random() * 80 + 10 });
    setTimeout(() => {
        floaters.value = floaters.value.filter((f) => f.id !== id);
    }, 2000);
};

const removeGuest = async (guestId) => {
    if (!isHost.value || !currentStreamId.value) return;
    try {
        await axios.post(route('frontend.live.guest.remove', { stream: currentStreamId.value }), { guest_id: guestId });
    } catch {}
};

// Existing logic
const setupEchoListing = () => {
    if (!window.Echo) return;

    window.Echo.channel('live-streams')
        .listen('.StreamStarted', (e) => {
            replaceStreamInList(e.stream || e);
        })
        .listen('.StreamEnded', (e) => {
            refreshLiveStreams();
        })
        .listen('.ViewerCountUpdated', (e) => {
            const stream = liveStreams.value.find((s) => String(s.id) === String(e.stream_id));
            if (stream) stream.viewers = e.viewer_count;
        })
        .listen('.LiveReactionSent', (e) => {
            const stream = liveStreams.value.find((s) => String(s.id) === String(e.stream_id));
            if (stream) stream.likes = e.like_count;
        })
        .listen('.LiveGiftSent', (e) => {
            const stream = liveStreams.value.find((s) => String(s.id) === String(e.stream_id));
            if (stream) stream.gifts = e.gift_count;
        });
};

const openAnalytics = () => {
    liveAnalyticsModalRef.value?.open?.();
};
const openGoLive = () => {
    goLiveModalRef.value?.open?.();
};

const openViewerShop = () => {
    const products = viewerSessionData.value.products || [];
    viewerShopModalRef.value?.open(products, products.find((product) => product.featured !== false)?.id || null);
};

const openHostShop = () => {
    hostShopModalRef.value?.open(hostProducts.value, hostFeaturedProduct.value?.id || null);
};

const persistHostProducts = async (products) => {
    if (!currentStreamId.value) return false;
    try {
        const response = await axios.post(route('frontend.go-live.update-settings', { stream: currentStreamId.value }), { products });
        const saved = response.data?.stream?.products || products;
        if (liveStreamData.value) liveStreamData.value.products = saved;
        hostStreamSettings.value.products = saved;
        return true;
    } catch (error) {
        toast.error(error.response?.data?.message || 'Could not update live products');
        return false;
    }
};

const featureHostProduct = async (product) => {
    await persistHostProducts(hostProducts.value.map((item) => ({ ...item, featured: item.id === product?.id })));
};

const removeHostProduct = async (productId) => {
    const products = hostProducts.value.filter((product) => product.id !== productId);
    await persistHostProducts(products);
};

const openHostTagPicker = () => hostTagPickerRef.value?.open?.();

const addHostProduct = async (item) => {
    const exists = hostProducts.value.some((product) => String(product.id) === String(item.id) && product.kind === item.kind);
    if (exists) {
        toast.info('This item is already tagged');
        return;
    }
    const products = [...hostProducts.value, { ...item, featured: hostProducts.value.length === 0 }];
    if (await persistHostProducts(products)) {
        toast.success(`${item.title} tagged for this live`);
        hostShopModalRef.value?.open(products, products.find((product) => product.featured)?.id || null);
    }
};

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

const endedRemovalTimers = new Map();

const clearEndedRemovalTimers = () => {
    endedRemovalTimers.forEach((timer) => window.clearTimeout(timer));
    endedRemovalTimers.clear();
};

const scheduleEndedRemoval = (stream) => {
    if (stream.status !== 'ended' || !stream.ended_expires_at || endedRemovalTimers.has(String(stream.id))) return;

    const delay = new Date(stream.ended_expires_at).getTime() - Date.now();
    if (delay <= 0) {
        removeStreamFromList(stream.id);
        return;
    }

    const timer = window.setTimeout(() => {
        removeStreamFromList(stream.id);
        endedRemovalTimers.delete(String(stream.id));
    }, delay);
    endedRemovalTimers.set(String(stream.id), timer);
};

onMounted(() => {
    refreshIcons();
    setupEchoListing();
    liveStreams.value.forEach(scheduleEndedRemoval);

    // Check for network param
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('network') === '1') {
        isStudioActive.value = false;
        const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
        window.history.replaceState({ path: newUrl }, '', newUrl);
    }
});

onUnmounted(() => {
    if (isHost.value && joined.value && hostLifecycleStreamId) {
        sendHostEndBeacon(hostLifecycleStreamId);
    }
    stopHostLifecycle();
    stopTimer();
    stopStatsPolling();
    clearEndedRemovalTimers();
    if (window.Echo) {
        window.Echo.leave('live-streams');
        if (subscribedLiveSessionPublicId) {
            window.Echo.leave('live-stream.' + subscribedLiveSessionPublicId);
            subscribedLiveSessionPublicId = null;
        }
    }
});

watch(
    [() => liveStreamData.value?.public_id, () => joined.value, () => isHost.value],
    ([publicId, isJoined, hostMode]) => {
        if (hostMode && isJoined && publicId) {
            subscribeToLiveChannel(publicId);
        }
    },
    { immediate: true },
);

watch(
    () => visibleJoinedGuests.value.map((guest) => String(guest.id)).join(','),
    () => {
        nextTick(() => {
            visibleJoinedGuests.value.forEach((guest) => {
                const track = guestVideoTracks.get(String(guest.id));
                if (track) handleGuestPublished({ uid: guest.id, track });
            });
        });
    },
);

watch(
    () => props.serverStreams,
    (value) => {
        liveStreams.value = [...value].map(normalizeStream);
        clearEndedRemovalTimers();
        liveStreams.value.forEach(scheduleEndedRemoval);
    },
);
</script>

<style scoped>
:deep([id^='guest-player-'] > div),
:deep([id^='guest-player-'] video) {
    width: 100% !important;
    height: 100% !important;
}

:deep([id^='guest-player-'] video) {
    object-fit: cover !important;
}

.gift-animation {
    position: fixed;
    pointer-events: none;
    font-size: 40px;
    z-index: 150;
}

@keyframes flyAcross {
    0% {
        transform: translateX(100vw) scale(1);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateX(-20vw) scale(1);
        opacity: 0;
    }
}

.floater {
    position: absolute;
    pointer-events: none;
    animation: floatUp 2s ease-out forwards;
    font-size: 30px;
    z-index: 110;
}

@keyframes floatUp {
    0% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    100% {
        transform: translateY(-200px) scale(1.5);
        opacity: 0;
    }
}

.live-category-strip {
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #e2e8f0;
}
.live-category-strip::-webkit-scrollbar {
    height: 8px;
}
.live-category-strip::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 999px;
}
.live-category-strip::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 999px;
}
.live-category-strip::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

.hide-scroll::-webkit-scrollbar {
    display: none;
}
.hide-scroll {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

:deep(.live-stream-container),
:deep(.video-player-container),
:deep(.video-player) {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

:deep(.video-player video),
:deep(.video-player > div),
:deep(#local-player video),
:deep(#local-player > div),
:deep(#remote-player video),
:deep(#remote-player > div) {
    width: 100% !important;
    height: 100% !important;
}

:deep(.video-player video),
:deep(#local-player video),
:deep(#remote-player video) {
    object-fit: cover !important;
}

:deep(.video-player-container.hidden) {
    opacity: 0;
    pointer-events: none;
}
</style>
