<template>
    <!-- STUDIO VIEW (HOST ONLY) -->
    <StudioView
        v-if="serverStream.isHost"
        :is-host="true"
        :joined="joined"
        :host-name="serverStream.host"
        :host-avatar="serverStream.hostAvatar"
        :followers-count="0"
        :session-coins="liveCoins"
        :session-cash="liveCoins * 0.01"
        :wallet-coins="0"
        :wallet-balance="0"
        :stream-title="serverStream.title"
        :stream-category="serverStream.category"
        :stream-location="serverStream.location"
        :viewer-count="displayViewers"
        :like-count="likes"
        :gift-count="gifts"
        :timer="sessionTime"
        @leave="goBack"
        @end="endLive"
    >
        <template #video-engine>
            <div class="absolute inset-0 z-10 flex overflow-hidden bg-black">
                <div id="local-player" class="relative h-full shrink-0 overflow-hidden bg-black transition-[width] duration-300" :style="{ width: guestGridWidths[0] + '%' }"></div>
                <div
                    v-for="(guest, index) in visibleJoinedGuests"
                    :key="guest.id"
                    :id="'guest-player-' + guest.id"
                    class="relative grid h-full shrink-0 place-items-center overflow-hidden border-l border-white/10 bg-[#16324d] text-sm font-black text-white transition-[width] duration-300"
                    :style="{ width: guestGridWidths[index + 1] + '%' }"
                >
                    <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-600">{{ initials(guest.name || guest.id) }}</span>
                    <span class="absolute bottom-20 left-3 z-20 rounded bg-rose-600 px-1.5 py-0.5 text-[9px] font-black text-white">LIVE</span>
                </div>
                <div v-if="joinedGuestOverflow > 0" class="absolute top-1/2 right-2 z-30 grid h-10 min-w-10 -translate-y-1/2 place-items-center rounded-full bg-black/75 px-2 text-xs font-black text-white">
                    +{{ joinedGuestOverflow }}
                </div>
                <span class="absolute bottom-20 left-3 z-20 rounded bg-emerald-500 px-1.5 py-0.5 text-[9px] font-black text-white">HOST</span>
                <div v-if="!joined" class="absolute inset-0 z-30 grid place-items-center text-center text-white/25">
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-full border border-dashed border-white/25 bg-white/5">
                        <i data-lucide="video" class="h-7 w-7"></i>
                    </span>
                    <p class="mt-4 text-sm font-black uppercase">Initialising Agora Stream...</p>
                </div>
            </div>
        </template>

        <template #guest-slots>
            <div v-for="guest in pendingGuests" :key="guest.id" class="relative h-14 w-11 shrink-0 overflow-hidden rounded-xl border-2 border-amber-300 bg-[#1e3a52] shadow-xl">
                <div class="absolute inset-0 grid place-items-center text-[9px] font-black text-white">{{ initials(guest.name || guest.id) }}</div>
                <div class="absolute bottom-0.5 left-0.5 rounded bg-amber-400 px-1 text-[7px] font-black text-black">…</div>
            </div>
        </template>

        <template #controls>
            <button
                v-for="control in controls"
                :key="control"
                @click="handleControl(control)"
                :class="[
                    'grid h-9 w-9 place-items-center rounded-full text-white transition hover:bg-white/10',
                    (control === 'mic' && isMicMuted) || (control === 'camera' && isCameraOff) ? 'bg-rose-500/25 text-rose-100' : 'bg-white/10',
                ]"
            >
                <i :data-lucide="controlIcon(control)" class="h-5 w-5"></i>
            </button>
        </template>

        <template #chat>
            <ChatComponent :stream-id="serverStream.id" :is-host="true" :host-id="serverStream.hostId" :session-event="sessionEvent" />
        </template>

        <template #qna>
            <QnaComponent :stream-id="serverStream.id" :is-host="true" :session-event="sessionEvent" />
        </template>

        <template #polls>
            <PollComponent :stream-id="serverStream.id" :is-host="true" :session-event="sessionEvent" />
        </template>
    </StudioView>

    <!-- VIEWER VIEW (IMMERSIVE - IMAGE 1) -->
    <div v-else class="h-screen w-screen overflow-hidden bg-black">
        <LiveViewerOverlay
            :session="viewerSessionData"
            :chat="formattedChatMessages"
            :featured-product="viewerFeaturedProduct"
            :hasVideo="true"
            @close="goBack"
            @sendChat="sendChat"
            @sendHeart="sendHeart"
            @openGift="showGiftModal = true"
            @openShop="openViewerShop"
            @follow="handleFollow"
        >
            <div class="relative h-full w-full">
                <div v-if="isGuestMode" class="absolute inset-0 flex overflow-hidden bg-black">
                    <button @click="leaveGuestStage" class="absolute bottom-24 left-1/2 z-40 -translate-x-1/2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-lg hover:bg-rose-500">Leave</button>
                    <div class="relative h-full shrink-0 overflow-hidden bg-black transition-[width] duration-300" :style="{ width: guestGridWidths[0] + '%' }">
                        <div id="remote-player" class="absolute inset-0 h-full w-full"></div>
                        <span class="absolute bottom-20 left-3 z-20 rounded bg-emerald-500 px-1.5 py-0.5 text-[9px] font-black text-white">HOST</span>
                    </div>
                    <div
                        v-for="(guest, index) in visibleJoinedGuests"
                        :key="guest.id"
                        class="relative grid h-full shrink-0 place-items-center overflow-hidden border-l border-white/10 bg-[#16324d] text-sm font-black text-white transition-[width] duration-300"
                        :style="{ width: guestGridWidths[index + 1] + '%' }"
                    >
                        <div :id="isCurrentGuest(guest) ? 'local-guest-player' : 'guest-player-' + guest.id" class="absolute inset-0 grid place-items-center bg-[#16324d]">
                            <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-600">{{ initials(guest.name || guest.id) }}</span>
                        </div>
                        <span class="absolute bottom-20 left-3 z-20 rounded bg-rose-600 px-1.5 py-0.5 text-[9px] font-black text-white">
                            {{ isCurrentGuest(guest) ? 'YOU' : 'LIVE' }}
                        </span>
                    </div>
                    <div v-if="joinedGuestOverflow > 0" class="absolute top-1/2 right-2 z-30 grid h-10 min-w-10 -translate-y-1/2 place-items-center rounded-full bg-black/75 px-2 text-xs font-black text-white">
                        +{{ joinedGuestOverflow }}
                    </div>
                </div>
                <div v-else id="remote-player" class="h-full w-full object-cover"></div>

                <div v-if="!joined" class="absolute inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="text-center text-white/50">
                        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full border border-dashed border-white/25 bg-white/5">
                            <i data-lucide="video" class="h-7 w-7"></i>
                        </span>
                        <p class="mt-4 text-sm font-black uppercase">Connecting to Stream...</p>
                    </div>
                </div>
            </div>
        </LiveViewerOverlay>

        <div class="fixed top-24 right-4 z-[90] flex gap-2">
            <button v-for="panel in audiencePanels" :key="panel.key" @click="toggleAudiencePanel(panel.key)" :class="['flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black text-white shadow-lg transition', activeAudiencePanel === panel.key ? 'bg-blue-500' : 'bg-black/60 hover:bg-black/80']">
                <svg v-if="panel.key === 'qna'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 1 1 5.83 1c0 2-3 2-3 4M12 18h.01"/></svg>
                <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3v18h18M7 16v2M12 11v7M17 6v12"/></svg>
                {{ panel.label }}
            </button>
        </div>

        <aside v-if="activeAudiencePanel" class="fixed top-36 right-4 bottom-24 z-[90] w-[min(360px,calc(100vw-2rem))] overflow-y-auto rounded-3xl border border-white/10 bg-[#0f1736]/95 p-4 text-white shadow-2xl backdrop-blur-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-black">{{ activeAudiencePanel === 'qna' ? 'Questions & Answers' : 'Live Poll' }}</h3>
                <button @click="activeAudiencePanel = null" class="grid h-8 w-8 place-items-center rounded-full bg-white/10" aria-label="Close panel"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
            </div>
            <QnaComponent v-if="activeAudiencePanel === 'qna'" :stream-id="serverStream.id" :is-host="false" :session-event="sessionEvent" />
            <PollComponent v-else :stream-id="serverStream.id" :is-host="false" :session-event="sessionEvent" />
        </aside>

        <LiveGiftModal
            :open="showGiftModal"
            :balance="giftBalance"
            :gifts="viewerGiftItems"
            :is-sending="isSendingGift"
            @close="showGiftModal = false"
            @select="sendGiftItem"
        />

        <LiveShopModal ref="viewerShopModalRef" />

        <div v-if="incomingInvite" class="fixed inset-0 z-[220] grid place-items-center bg-black/75 p-4">
            <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-[#0f172a] p-5 text-center text-white shadow-2xl">
                <img
                    :src="incomingInvite.host?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(incomingInvite.host?.name || 'Host')"
                    class="mx-auto h-16 w-16 rounded-full object-cover"
                />
                <h2 class="mt-4 text-xl font-black">Join as Guest?</h2>
                <p class="mt-2 text-sm font-bold text-white/60">
                    {{ incomingInvite.host?.name || 'The host' }} invited you to join the live stream.
                </p>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button
                        @click="declineInvite"
                        class="rounded-2xl bg-white/10 py-3 text-sm font-black text-white transition hover:bg-white/15"
                    >
                        Decline
                    </button>
                    <button
                        @click="acceptInvite"
                        :disabled="isJoiningAsGuest"
                        class="rounded-2xl bg-emerald-500 py-3 text-sm font-black text-white transition hover:bg-emerald-400 disabled:opacity-60"
                    >
                        {{ isJoiningAsGuest ? 'Joining...' : 'Accept' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AgoraRTC from 'agora-rtc-sdk-ng';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import LiveGiftModal from '../../../components/new_frontend/live/LiveGiftModal.vue';
import LiveShopModal from '../../../components/new_frontend/modals/LiveShopModal.vue';
import LiveViewerOverlay from '../../../components/new_frontend/live/LiveViewerOverlay.vue';
import '../../../layouts/new_front_layout/new_front.css';
import ChatComponent from '../../User/GoLive/Components/ChatComponent.vue';
import PollComponent from '../../User/GoLive/Components/PollComponent.vue';
import QnaComponent from '../../User/GoLive/Components/QnaComponent.vue';
import StudioView from './Components/StudioView.vue';

const props = defineProps({
    serverStream: { type: Object, default: () => ({}) },
    agoraData: { type: Object, default: () => ({}) },
    serverGifts: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const liveCoins = ref(Number(props.serverStream.sessionCoins || 0));
const likes = ref(props.serverStream.likes || 0);
const gifts = ref(props.serverStream.gifts || 0);
const viewers = ref(props.serverStream.viewers || 0);
const chatInput = ref('');
const joined = ref(false);
const sessionTime = ref('00:00');
const showGiftModal = ref(false);
const isSendingGift = ref(false);
const giftBalance = ref(Number(props.serverStream.viewerCoins || 0));
const isMicMuted = ref(false);
const isCameraOff = ref(false);
const incomingInvite = ref(null);
const isJoiningAsGuest = ref(false);
const isGuestMode = ref(false);
const guestRemoveSent = ref(false);
const isFollowing = ref(false);
const viewerShopModalRef = ref(null);
const liveProducts = ref(props.serverStream.products || []);
const activeAudiencePanel = ref(null);
const audiencePanels = [
    { key: 'qna', label: 'Q&A', icon: 'help-circle' },
    { key: 'polls', label: 'Polls', icon: 'bar-chart-2' },
];
const toggleAudiencePanel = (panel) => {
    activeAudiencePanel.value = activeAudiencePanel.value === panel ? null : panel;
    nextTick(refreshIcons);
};
const guests = ref((props.serverStream.guests || []).map((guest) => ({ ...guest, status: guest.status || 'joined' })));
const GUEST_GRID_WIDTHS = {
    1: [100],
    2: [60, 40],
    3: [50, 25, 25],
    4: [40, 20, 20, 20],
};
const visibleJoinedGuests = computed(() => guests.value.filter((guest) => guest.status === 'joined').slice(0, 3));
const pendingGuests = computed(() => guests.value.filter((guest) => guest.status === 'pending'));
const joinedGuestOverflow = computed(() => Math.max(0, guests.value.filter((guest) => guest.status === 'joined').length - 3));
const guestGridWidths = computed(() => GUEST_GRID_WIDTHS[visibleJoinedGuests.value.length + 1] || GUEST_GRID_WIDTHS[1]);
const isCurrentGuest = (guest) => Boolean(user.value?.id) && String(guest.id) === String(user.value.id);

const controls = ['user-plus', 'shopping-bag', 'mic', 'camera'];
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

// Gift IDs and prices must always come from the active server catalogue.
const viewerGiftItems = computed(() => props.serverGifts);

const displayViewers = computed(() => Number(viewers.value || 0));

const isLocalLiveDebug = () => ['localhost', '127.0.0.1'].includes(window.location.hostname);

const panelMessages = ref([{ who: 'LinkUp', msg: 'Welcome to the live stream!', color: '#8b5cf6' }]);
const sessionEvent = ref(null);

const formattedChatMessages = computed(() => {
    return panelMessages.value.map((m) => ({
        who: m.who,
        msg: m.msg,
        isHost: m.isHost,
    }));
});

const forwardSessionEvent = (name, payload) => {
    if (isLocalLiveDebug()) console.info('[Live Echo][Viewer] event received', name, payload);
    sessionEvent.value = { name, payload };
};

const subscribeToLiveChannel = () => {
    if (!props.serverStream.public_id || !window.Echo) {
        if (isLocalLiveDebug()) console.error('[Live Echo][Viewer] unavailable subscription input', { hasEcho: Boolean(window.Echo), publicId: props.serverStream.public_id });
        return;
    }
    const channel = 'live-stream.' + String(props.serverStream.public_id);
    if (isLocalLiveDebug()) console.info('[Live Echo][Viewer] subscribing', channel);

    window.Echo.private(channel)
        .listen('.LiveCommentPosted', (event) => {
            forwardSessionEvent('LiveCommentPosted', event);
            const userId = event.user?.id || event.from_id;
            const isHostMsg = userId && props.serverStream.hostId && String(userId) === String(props.serverStream.hostId);

            const newMessage = {
                who: event.user?.name || event.from || 'User',
                msg: event.text,
                color: '#2563eb',
                isHost: isHostMsg,
            };
            panelMessages.value.push(newMessage);
            if (panelMessages.value.length > 50) panelMessages.value.shift();
        })
        .listen('.LiveGiftSent', (event) => {
            forwardSessionEvent('LiveGiftSent', event);
            gifts.value = Number(event.gift_count || gifts.value || 0);
            if (props.serverStream.isHost && event.gift) {
                liveCoins.value += Number(event.gift.coins || 0);
            }
        })
        .listen('.LiveReactionSent', (event) => {
            forwardSessionEvent('LiveReactionSent', event);
            likes.value = Number(event.like_count || likes.value || 0);
        })
        .listen('.ViewerCountUpdated', (event) => {
            forwardSessionEvent('ViewerCountUpdated', event);
            viewers.value = Number(event.viewer_count || 0);
        })
        .listen('.LiveInviteReply', (event) => {
            forwardSessionEvent('LiveInviteReply', event);
            const guestId = event.user?.id;
            if (!guestId) return;

            const existingGuest = guests.value.find((guest) => String(guest.id) === String(guestId));
            if (event.accepted) {
                const acceptedGuest = {
                    id: guestId,
                    name: event.user?.name || existingGuest?.name || `Guest ${guestId}`,
                    avatar: event.user?.avatar || existingGuest?.avatar || null,
                    status: 'joined',
                };
                if (existingGuest) Object.assign(existingGuest, acceptedGuest);
                else guests.value.push(acceptedGuest);
            } else {
                guests.value = guests.value.filter((guest) => String(guest.id) !== String(guestId));
            }
        })
        .listen('.LiveGuestRemoved', (event) => {
            forwardSessionEvent('LiveGuestRemoved', event);
            guests.value = guests.value.filter((guest) => String(guest.id) !== String(event.guest_id));
            remoteGuestVideoTracks.delete(String(event.guest_id));
            if (user.value?.id && String(user.value.id) === String(event.guest_id)) {
                guestRemoveSent.value = true;
                returnToAudienceAfterGuestRemoval();
            }
        })
        .listen('.StreamEnded', () => {
            forwardSessionEvent('StreamEnded', {});
            redirectAfterStreamEnded();
        })
        .listen('.QnaSubmitted', (event) => forwardSessionEvent('QnaSubmitted', event))
        .listen('.QnaAnswered', (event) => forwardSessionEvent('QnaAnswered', event))
        .listen('.QnaDeleted', (event) => forwardSessionEvent('QnaDeleted', event))
        .listen('.PollCreated', (event) => forwardSessionEvent('PollCreated', event))
        .listen('.PollVoted', (event) => forwardSessionEvent('PollVoted', event))
        .listen('.PollEnded', (event) => forwardSessionEvent('PollEnded', event))
        .listen('.LiveProductsUpdated', (event) => {
            liveProducts.value = event.products || [];
        })
        .subscribed(() => { if (isLocalLiveDebug()) console.info('[Live Echo][Viewer] subscribed', channel); })
        .error((error) => { if (isLocalLiveDebug()) console.error('[Live Echo][Viewer] subscription/authentication failed', channel, error); });

    window.Echo.channel('live-streams').listen('.StreamEnded', (event) => {
        if (String(event.public_id) === String(props.serverStream.public_id)) {
            redirectAfterStreamEnded();
        }
    });
};

const subscribeToInviteChannel = () => {
    if (!window.Echo || !user.value?.id || props.serverStream.isHost) return;

    window.Echo.private(`App.Models.User.${user.value.id}`).listen('.LiveInviteSent', (event) => {
        if (String(event.stream_id) !== String(props.serverStream.id)) return;
        incomingInvite.value = event;
    });
};

const sendChat = async (text) => {
    const message = typeof text === 'string' ? text : chatInput.value.trim();
    if (!message || !props.serverStream.id) return;

    try {
        await axios.post(route('frontend.live.comment', { stream: props.serverStream.id }), { text: message });
        chatInput.value = '';
        // Note: panelMessages will be updated via Echo listener
    } catch (err) {
        console.error('Failed to send chat:', err);
    }
};

const viewerSessionData = computed(() => ({
    host: props.serverStream.host || 'Broadcaster',
    hostAvatar: props.serverStream.hostAvatar,
    hearts: likes.value,
    viewers: displayViewers.value,
    category: props.serverStream.category || 'Just Chatting',
    coins: liveCoins.value,
    isFollowing: isFollowing.value,
    cover: props.serverStream.cover || props.serverStream.thumb,
    thumb: props.serverStream.thumb || props.serverStream.cover,
    products: liveProducts.value,
}));

const viewerFeaturedProduct = computed(() => viewerSessionData.value.products.find((product) => product.featured !== false) || null);

const openViewerShop = () => {
    const products = viewerSessionData.value.products || [];
    viewerShopModalRef.value?.open(products, products.find((product) => product.featured !== false)?.id || null);
};

const num = (n) => Number(n || 0).toLocaleString();
const initials = (name) =>
    String(name || '?')
        .replace('@', '')
        .slice(0, 2)
        .toUpperCase();

const sendHeart = async () => {
    if (!props.serverStream.id) return;

    try {
        const response = await axios.post(route('frontend.live.reaction', { stream: props.serverStream.id }), { type: 'heart' });
        likes.value = Number(response.data?.like_count || likes.value || 0);
        if (window.toast) window.toast('Heart sent');
    } catch (error) {
        if (window.toast) window.toast(error.response?.data?.message || 'Failed to send heart', 'error');
    }
};

const handleFollow = async () => {
    const creatorId = props.serverStream.hostId;
    if (!creatorId) return;

    try {
        const response = await axios.post(`/live/stream/${creatorId}/follow`);
        if (response.data.success) {
            isFollowing.value = response.data.is_following;
            if (window.toast) {
                isFollowing.value ? window.toast('Followed!') : window.toast('Unfollowed');
            }
        }
    } catch (error) {
        console.error('Failed to toggle follow:', error);
        if (window.toast) window.toast('Failed to update follow status', 'error');
    }
};

const controlIcon = (control) => {
    if (control === 'mic') return isMicMuted.value ? 'mic-off' : 'mic';
    if (control === 'camera') return isCameraOff.value ? 'video-off' : 'camera';
    return control;
};

const sendGiftItem = async (gift) => {
    if (!props.serverStream.id || isSendingGift.value) return false;

    isSendingGift.value = true;
    try {
        const response = await axios.post(route('frontend.live.gift', { stream: props.serverStream.id }), {
            gift: { id: gift.id }, qty: 1, idempotency_key: crypto.randomUUID(),
        });
        giftBalance.value = Number(response.data?.remaining_coins ?? giftBalance.value - gift.coins);
        showGiftModal.value = false;
        if (window.toast) window.toast(`${gift.name} sent!`);
        return true;
    } catch (error) {
        if (window.toast) window.toast(error.response?.data?.message || 'Gift failed', 'error');
        return false;
    } finally {
        isSendingGift.value = false;
    }
};

const joinAsGuest = async () => {
    if (!props.serverStream.id || !props.agoraData?.channel) return;

    await cleanup();

    const tokenResponse = await axios.post(route('frontend.go-live.token'), {
        channel: props.agoraData.channel,
        role: 'guest',
    });

    await client.join(tokenResponse.data.appId, props.agoraData.channel, tokenResponse.data.token, tokenResponse.data.uid);
    joined.value = true;
    isGuestMode.value = true;
    guestRemoveSent.value = false;

    if (user.value?.id && !guests.value.some((guest) => String(guest.id) === String(user.value.id))) {
        guests.value.push({
            id: user.value.id,
            name: user.value.name || `Guest ${user.value.id}`,
            avatar: user.value.avatar || null,
            status: 'joined',
        });
    }

    await nextTick();

    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    const videoConfig = isMobile ? { encoderConfig: '480p_1', facingMode: 'user' } : { encoderConfig: '720p_1' };
    const [audioResult, videoResult] = await Promise.allSettled([
        AgoraRTC.createMicrophoneAudioTrack(),
        AgoraRTC.createCameraVideoTrack(videoConfig),
    ]);

    localTracks.audioTrack = audioResult.status === 'fulfilled' ? audioResult.value : null;
    localTracks.videoTrack = videoResult.status === 'fulfilled' ? videoResult.value : null;

    const publishableTracks = [localTracks.audioTrack, localTracks.videoTrack].filter(Boolean);
    if (publishableTracks.length) {
        await client.publish(publishableTracks);
    }

    if (localTracks.videoTrack) {
        const localGuestPlayer = document.getElementById('local-guest-player');
        if (localGuestPlayer) {
            localGuestPlayer.innerHTML = '';
            localTracks.videoTrack.play(localGuestPlayer);
        }
    } else if (window.toast) {
        window.toast('Camera not found. You joined with your profile tile.', 'error');
    }

    if (!localTracks.audioTrack && window.toast) {
        window.toast('Microphone not found. You joined muted.', 'error');
    }

    client.remoteUsers.forEach(async (remoteUser) => {
        if (remoteUser.hasVideo) await handleRemoteUserPublished(remoteUser, 'video');
        if (remoteUser.hasAudio) await handleRemoteUserPublished(remoteUser, 'audio');
    });
};

const declineInvite = async () => {
    if (!incomingInvite.value?.stream_id) {
        incomingInvite.value = null;
        return;
    }

    try {
        await axios.post(route('frontend.live.invite.reply', { stream: incomingInvite.value.stream_id }), { accept: false });
    } catch (error) {
        console.error('Failed to decline invite:', error);
    } finally {
        incomingInvite.value = null;
    }
};

const acceptInvite = async () => {
    if (!incomingInvite.value?.stream_id || isJoiningAsGuest.value) return;

    isJoiningAsGuest.value = true;
    try {
        await axios.post(route('frontend.live.invite.reply', { stream: incomingInvite.value.stream_id }), { accept: true });
        incomingInvite.value = null;
        await joinAsGuest();
        if (window.toast) window.toast('You joined as a guest');
    } catch (error) {
        console.error('Failed to accept invite:', error);
        if (window.toast) window.toast(error.response?.data?.message || 'Failed to join as guest', 'error');
    } finally {
        isJoiningAsGuest.value = false;
    }
};

const removeSelfAsGuest = async () => {
    if (!isGuestMode.value || guestRemoveSent.value || props.serverStream.isHost || !props.serverStream.id || !user.value?.id) return;

    guestRemoveSent.value = true;
    try {
        await axios.post(route('frontend.live.guest.remove', { stream: props.serverStream.id }), {
            guest_id: user.value.id,
        });
    } catch (error) {
        console.error('Failed to leave guest stage:', error);
    } finally {
        isGuestMode.value = false;
    }
};

const leaveGuestStage = async () => {
    if (!isGuestMode.value) return;
    await removeSelfAsGuest();
    await returnToAudienceAfterGuestRemoval();
};

const handleControl = (control) => {
    if (control === 'mic') {
        if (localTracks.audioTrack) {
            const isMuted = localTracks.audioTrack.muted;
            localTracks.audioTrack.setMuted(!isMuted);
            isMicMuted.value = !isMuted;
            if (window.toast) window.toast(isMuted ? 'Microphone unmuted' : 'Microphone muted');
        }
    } else if (control === 'camera') {
        if (localTracks.videoTrack) {
            const isMuted = localTracks.videoTrack.muted;
            localTracks.videoTrack.setMuted(!isMuted);
            isCameraOff.value = !isMuted;
            if (window.toast) window.toast(isMuted ? 'Camera on' : 'Camera off');
        }
    }
};

// Agora RTC references
const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
const remoteGuestVideoTracks = new Map();
let localTracks = {
    videoTrack: null,
    audioTrack: null,
};
let hostHeartbeatInterval = null;
let hostEndSent = false;
let viewerHeartbeatInterval = null;
let statsPollingInterval = null;

const waitForElement = async (elementId) => {
    for (let attempt = 0; attempt < 30; attempt++) {
        await nextTick();
        const element = document.getElementById(elementId);
        if (element) return element;
        await new Promise((resolve) => window.setTimeout(resolve, 100));
    }
    return null;
};

const playGuestVideoTrack = async (uid, track) => {
    if ((!props.serverStream.isHost && !isGuestMode.value) || !track) return;
    const element = await waitForElement(`guest-player-${uid}`);
    if (!element) return;
    element.innerHTML = '';
    track.play(element);
};

const replayVisibleGuestTracks = () => {
    visibleJoinedGuests.value.forEach((guest) => {
        if (isCurrentGuest(guest)) return;
        const track = remoteGuestVideoTracks.get(String(guest.id));
        if (track) playGuestVideoTrack(guest.id, track);
    });
};

const handleRemoteUserPublished = async (remoteUser, mediaType) => {
    await client.subscribe(remoteUser, mediaType);

    if (mediaType === 'audio') {
        remoteUser.audioTrack?.play();
        return;
    }

    if (props.serverStream.isHost) {
        const uid = String(remoteUser.uid);
        if (uid === String(props.serverStream.hostId)) return;
        if (!guests.value.some((guest) => String(guest.id) === uid)) {
            guests.value.push({ id: remoteUser.uid, name: `Guest ${uid}`, avatar: null, status: 'joined' });
        }
        remoteGuestVideoTracks.set(uid, remoteUser.videoTrack);
        await playGuestVideoTrack(uid, remoteUser.videoTrack);
        return;
    }

    if (String(remoteUser.uid) === String(props.serverStream.hostId)) {
        remoteUser.videoTrack?.play('remote-player');
    } else if (isGuestMode.value) {
        const uid = String(remoteUser.uid);
        remoteGuestVideoTracks.set(uid, remoteUser.videoTrack);
        await playGuestVideoTrack(uid, remoteUser.videoTrack, true);
    }
};

client.on('user-published', handleRemoteUserPublished);
client.on('user-unpublished', (remoteUser, mediaType) => {
    if (mediaType === 'video') remoteGuestVideoTracks.delete(String(remoteUser.uid));
});

watch(
    () => visibleJoinedGuests.value.map((guest) => String(guest.id)).join(','),
    () => nextTick(replayVisibleGuestTracks),
);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const sendHostEndBeacon = () => {
    if (!props.serverStream.isHost || !props.serverStream.id || hostEndSent) return;

    hostEndSent = true;

    const formData = new FormData();
    formData.append('stream_id', props.serverStream.id);
    formData.append('_token', csrfToken());

    if (navigator.sendBeacon) {
        navigator.sendBeacon(route('frontend.go-live.end'), formData);
        return;
    }

    fetch(route('frontend.go-live.end'), {
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
    if (joined.value) {
        sendHostEndBeacon();
    }
};

const stopHostLifecycle = () => {
    if (hostHeartbeatInterval) {
        window.clearInterval(hostHeartbeatInterval);
        hostHeartbeatInterval = null;
    }

    window.removeEventListener('pagehide', handleHostPageExit);
    window.removeEventListener('beforeunload', handleHostPageExit);
};

const sendHostHeartbeat = async () => {
    if (!props.serverStream.isHost || !props.serverStream.id || !joined.value || hostEndSent) return;

    try {
        await axios.post(route('frontend.live.host.heartbeat', { stream: props.serverStream.id }));
    } catch (error) {
        console.warn('Host heartbeat failed:', error);
    }
};

const startHostLifecycle = () => {
    if (!props.serverStream.isHost || !props.serverStream.id) return;

    stopHostLifecycle();
    sendHostHeartbeat();
    hostHeartbeatInterval = window.setInterval(sendHostHeartbeat, 30000);
    window.addEventListener('pagehide', handleHostPageExit);
    window.addEventListener('beforeunload', handleHostPageExit);
};

const startViewerTracking = async () => {
    if (props.serverStream.isHost || !props.serverStream.id) return;

    try {
        await axios.post(route('frontend.live.viewer.join', { stream: props.serverStream.id }));
        viewerHeartbeatInterval = window.setInterval(async () => {
            try {
                await axios.post(route('frontend.live.viewer.heartbeat', { stream: props.serverStream.id }));
            } catch (error) {
                console.error('Viewer heartbeat failed:', error);
            }
        }, 20000);
    } catch (error) {
        console.error('Viewer tracking failed:', error);
    }
};

const stopViewerTracking = async () => {
    if (viewerHeartbeatInterval) {
        window.clearInterval(viewerHeartbeatInterval);
        viewerHeartbeatInterval = null;
    }

    if (props.serverStream.isHost || !props.serverStream.id) return;

    try {
        await axios.post(route('frontend.live.viewer.leave', { stream: props.serverStream.id }));
    } catch (error) {
        console.error('Viewer leave failed:', error);
    }
};

const refreshStats = async () => {
    if (!props.serverStream.id) return;

    try {
        const response = await axios.get(route('frontend.live.stats', { stream: props.serverStream.id }));
        if (response.data?.ok) {
            viewers.value = Number(response.data.viewer_count || 0);
            gifts.value = Number(response.data.gift_count || 0);
            likes.value = Number(response.data.like_count || 0);
            if (props.serverStream.isHost) {
                liveCoins.value = Number(response.data.session_coins || 0);
            }
        }
    } catch (error) {
        console.error('Stats refresh failed:', error);
    }
};

const startStatsPolling = () => {
    if (statsPollingInterval) return;
    refreshStats();
    statsPollingInterval = window.setInterval(refreshStats, 10000);
};

const stopStatsPolling = () => {
    if (statsPollingInterval) {
        window.clearInterval(statsPollingInterval);
        statsPollingInterval = null;
    }
};

const completeHostStreamOnServer = async () => {
    if (!props.serverStream.isHost || !props.serverStream.id || hostEndSent) return;

    hostEndSent = true;
    stopHostLifecycle();
    await axios.post(route('frontend.go-live.end'), { stream_id: props.serverStream.id });
};

const initAgora = async () => {
    if (!props.agoraData || !props.agoraData.token) return;

    try {
        await client.join(props.agoraData.appId, props.agoraData.channel, props.agoraData.token, props.agoraData.uid);

        joined.value = true;

        if (props.serverStream.isHost) {
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            const videoConfig = isMobile ? { encoderConfig: '480p_1', facingMode: 'user' } : { encoderConfig: '720p_1' };
            const [audioTrack, videoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks({}, videoConfig);
            localTracks.audioTrack = audioTrack;
            localTracks.videoTrack = videoTrack;

            await client.publish(Object.values(localTracks));
            await nextTick();
            const localPlayer = document.getElementById('local-player');
            if (localPlayer) {
                localPlayer.innerHTML = '';
                localTracks.videoTrack.play(localPlayer);
            }
            client.remoteUsers.forEach(async (remoteUser) => {
                if (remoteUser.hasVideo) await handleRemoteUserPublished(remoteUser, 'video');
                if (remoteUser.hasAudio) await handleRemoteUserPublished(remoteUser, 'audio');
            });
            startHostLifecycle();
            startStatsPolling();
        } else {
            // Check if host is already publishing
            client.remoteUsers.forEach(async (remoteUser) => {
                if (remoteUser.hasVideo) await handleRemoteUserPublished(remoteUser, 'video');
                if (remoteUser.hasAudio) await handleRemoteUserPublished(remoteUser, 'audio');
            });
            await startViewerTracking();
            startStatsPolling();
        }
    } catch (e) {
        console.error('Agora Error:', e);
        if (window.toast) window.toast('Camera and microphone permission are required to start live', 'error');
    }
};

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

const goBack = async () => {
    if (props.serverStream.isHost) {
        await endLive();
        return;
    }

    await removeSelfAsGuest();
    await cleanup();
    window.location.href = route('new_frontend.live');
};

const redirectAfterStreamEnded = async () => {
    if (!props.serverStream.isHost) {
        await cleanup();
    }
    window.location.href = route('new_frontend.live');
};

const returnToAudienceAfterGuestRemoval = async () => {
    try {
        await cleanup();
        joined.value = false;
        isGuestMode.value = false;
        await nextTick();
        await initAgora();
        if (window.toast) window.toast('You are now watching as a viewer');
    } catch (error) {
        console.error('Failed to return removed guest to audience mode:', error);
        if (window.toast) window.toast('Could not reconnect as a viewer. Please refresh.', 'error');
    }
};

const endLive = async () => {
    if (props.serverStream.isHost && props.serverStream.id) {
        try {
            await completeHostStreamOnServer();
        } catch (error) {
            console.error('Failed to end live stream:', error);
        }
    }
    await removeSelfAsGuest();
    await cleanup();
    window.location.href = route('new_frontend.live');
};

const cleanup = async () => {
    stopStatsPolling();
    await stopViewerTracking();
    remoteGuestVideoTracks.clear();

    if (localTracks.audioTrack) {
        localTracks.audioTrack.stop();
        localTracks.audioTrack.close();
        localTracks.audioTrack = null;
    }
    if (localTracks.videoTrack) {
        localTracks.videoTrack.stop();
        localTracks.videoTrack.close();
        localTracks.videoTrack = null;
    }
    try {
        await client.leave();
    } catch (error) {
        console.warn('Agora cleanup skipped or already left:', error);
    }
};

const fetchFollowStatus = async () => {
    const creatorId = props.serverStream.hostId;
    if (!creatorId || props.serverStream.isHost) return;

    try {
        const response = await axios.get(route('frontend.live.stream.followers', { stream: creatorId }));
        if (response.data?.success) {
            isFollowing.value = response.data.is_following || false;
        }
    } catch (error) {
        console.error('Failed to fetch follow status:', error);
    }
};

onMounted(() => {
    refreshIcons();
    initAgora();
    subscribeToLiveChannel();
    subscribeToInviteChannel();
    fetchFollowStatus();
});

onUnmounted(async () => {
    if (props.serverStream.isHost && joined.value && !hostEndSent) {
        sendHostEndBeacon();
    }
    stopHostLifecycle();

    if (props.serverStream.public_id && window.Echo) {
        window.Echo.leave('live-stream.' + String(props.serverStream.public_id));
        window.Echo.leave('live-streams');
    }
    if (user.value?.id && window.Echo) {
        window.Echo.leave(`private-App.Models.User.${user.value.id}`);
    }
    await removeSelfAsGuest();
    await cleanup();
});
</script>

<style scoped>
#local-player {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

#remote-player {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

#local-player :deep(video),
#local-player :deep(div),
#remote-player :deep(video),
#remote-player :deep(div) {
    width: 100% !important;
    height: 100% !important;
}

#local-player :deep(video),
#remote-player :deep(video) {
    object-fit: cover !important;
}

:deep([id^='guest-player-'] > div),
:deep([id^='guest-player-'] video) {
    width: 100% !important;
    height: 100% !important;
}

:deep([id^='guest-player-'] video) {
    object-fit: cover !important;
}
</style>
