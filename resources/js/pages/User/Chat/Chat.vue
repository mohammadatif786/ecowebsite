<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import Inbox from "./components/Inbox.vue";
import Emoji from "./components/Emoji.vue";
import { ref, nextTick, watch } from "vue";
import Gif from "./components/Gif.vue";
import Tickets from "./components/Tickets.vue";
import { usePage, router } from "@inertiajs/vue3";
import AgoraRTC, { IAgoraRTCClient, ILocalAudioTrack, ILocalVideoTrack, IRemoteVideoTrack, UID } from "agora-rtc-sdk-ng";

interface Receiver { id: number; name: string; more_photos: string[] }
interface Auth { user: { id: number } }

const props = defineProps<{
    chatmessages: any;
    receiver: Receiver;
    search?: string;
    auth: Auth;
    tickets: any;
    feature: boolean
}>();

const page = usePage<{ auth: Auth }>();

// UI state
const showEmoji = ref(false);
const showGif = ref(false);
const inboxRef = ref<InstanceType<typeof Inbox> | null>(null);
const ticketsRef = ref<InstanceType<typeof Tickets> | null>(null);

// Transform incoming server messages to Inbox-friendly format
const coerceToArray = (v: any) => Array.isArray(v) ? v : (v && typeof v === 'object' ? Object.values(v) : []);
const toInboxMsg = (m: any) => {
    const id = m?.id;
    const me = m?.from_user_id === page.props.auth.user.id;
    const created_at = m?.created_at || new Date().toISOString();
    if (m?.type === 'gif' && m?.meta?.url) return { id, type: 'gif', url: m.meta.url, me, created_at };
    if (m?.type === 'image' && m?.meta?.url) return { id, type: 'image', url: m.meta.url, name: m.meta.name, me, created_at };
    if (m?.type === 'pdf' && m?.meta?.url) return { id, type: 'pdf', url: m.meta.url, name: m.meta.name, me, created_at };
    if (m?.type === 'ticket' && m?.meta) return {
        id, type: 'ticket', ticket: {
            city: m.meta.city,
            dateLabel: m.meta.date_label,
            event: m.meta.event,
            sale_end: m.meta.sale_end,
            sale_start: m.meta.sale_start,
            ticket_id: m.meta.ticket_id,
            ticket_name: m.meta.ticket_name || 'General Ticket',
            ticket_price: m.meta.ticket_price || 0,
            type: m.meta.ticket_type,
            venue: m.meta.venue,
            _signed_token: m.meta.token,

        }, me, created_at
    };
    return { id, type: 'text', content: m.content, me, created_at };
};
const messages = ref<any[]>(coerceToArray(props.chatmessages).map(toInboxMsg).sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0)));

watch(() => props.chatmessages, (val) => {
    messages.value = coerceToArray(val).map(toInboxMsg).sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
});

function toggleEmoji() { showEmoji.value = !showEmoji.value; showGif.value = false; }
function toggleGif() { showGif.value = !showGif.value; showEmoji.value = false; }
function toggleTicket() { ticketsRef.value?.openModal(); }
function closeEmoji() { showEmoji.value = false; showGif.value = false; }
function closeGif() { showGif.value = false; showEmoji.value = false; }
function onEmojiSelect(emoji: string) { inboxRef.value?.insertEmoji(emoji); }
function onGifSelect(url: string) {
    // Persist to backend
    router.post(`/chat/${props.receiver.id}/messages`, { gif_url: url }, { preserveScroll: true });
}
function onTicketSend(ticket: any) {
    // Persist to backend: backend transfers the owned TicketSale (splitting quantity if needed)
    if (!ticket?.ticket_sale_id) return;
    router.post(`/chat/${props.receiver.id}/messages`, {
        ticket_sale_id: ticket.ticket_sale_id,
        ticket_qty: ticket.ticket_qty ?? 1,
        replied_to: null,
    }, { preserveScroll: true });
}

// Handle Inbox emitted send-message (text + attachments FormData)
function onSendMessage(payload: any) {
    if (!payload) return;
    if (payload.type === 'message') {
        const form = payload.attachments instanceof FormData ? payload.attachments : new FormData();
        const text = payload.text || '';
        form.append('content', text);
        form.append('replied_to', '');
        // Optimistic append once (sender will not receive Echo due to toOthers())
        if (text.trim()) {
            messages.value.push({ type: 'text', content: text, me: true, created_at: new Date().toISOString() });
        }
        router.post(`/chat/${props.receiver.id}/messages`, form, { preserveScroll: true });
    } else if (payload.type === 'gif') {
        router.post(`/chat/${props.receiver.id}/messages`, { gif_url: payload.url }, { preserveScroll: true });
    }
}

// Real-time updates
import { onMounted, onUnmounted } from 'vue';

// Real-time updates
onMounted(() => {
    if (window.Echo) {
        window.Echo.private(`App.Models.User.${page.props.auth.user.id}`)
            .listen("MessageSent", (e: any) => {
                try { console.debug('[Echo] MessageSent', e); } catch { }
                if (!e) return;
                if (e.to_user_id === props.receiver.id || e.from_user_id === props.receiver.id) {
                    // Map by type/meta for realtime updates
                    const me = e.from_user_id === page.props.auth.user.id;
                    if (me && (e.type === 'text' || e.type === 'signal' || (!e.type && !e.meta?.url))) {
                        let isDup = false;
                        for (let i = messages.value.length - 1; i >= 0; i--) {
                            const m = messages.value[i];
                            if (m.me && m.type === 'text' && m.content === e.content && !m.id) {
                                m.id = e.id;
                                isDup = true;
                                break;
                            }
                        }
                        if (isDup) return;
                    }
                    const created_at = e.created_at || new Date().toISOString();
                    if (e.type === 'gif' && e.meta?.url) {
                        messages.value.push({ type: 'gif', url: e.meta.url, me, created_at });
                    } else if (e.type === 'image' && e.meta?.url) {
                        messages.value.push({ type: 'image', url: e.meta.url, name: e.meta.name, me, created_at });
                    } else if (e.type === 'pdf' && e.meta?.url) {
                        messages.value.push({ type: 'pdf', url: e.meta.url, name: e.meta.name, me, created_at });
                    } else if (!e.type && e.meta?.url) {
                        // Fallback: infer type by URL when type is missing
                        const url: string = e.meta.url;
                        if (/giphy\.com\/embed\//i.test(url)) {
                            messages.value.push({ type: 'gif', url, me, created_at });
                        } else if (/\.(png|jpe?g|webp|gif)(\?|$)/i.test(url)) {
                            messages.value.push({ type: 'image', url, name: e.meta.name, me, created_at });
                        } else if (/\.pdf(\?|$)/i.test(url)) {
                            messages.value.push({ type: 'pdf', url, name: e.meta.name, me, created_at });
                        } else {
                            messages.value.push({ type: 'image', url, name: e.meta.name, me, created_at });
                        }
                    } else if (e.type === 'ticket' && e.meta) {
                        messages.value.push({
                            type: 'ticket', ticket: {
                                ticket_id: e.meta.ticket_id,
                                _signed_token: e.meta.token,
                                event: e.meta.event,
                                dateLabel: e.meta.date_label,
                                venue: e.meta.venue,
                                city: e.meta.city,
                                type: e.meta.ticket_type,
                            }, me, created_at
                        });
                    } else {
                        messages.value.push({ type: 'text', content: e.content, me, created_at });
                    }

                    if (typeof e.content === 'string' && (e.content.startsWith('__CALL_INVITE__') || e.content.startsWith('__CALL_INVITE_AUDIO__')) && e.to_user_id === page.props.auth.user.id) {
                        const parts = e.content.split(':');
                        const channel = parts[1];
                        if (channel) {
                            callChannelName.value = channel;
                            callMode.value = e.content.startsWith('__CALL_INVITE_AUDIO__') ? 'audio' : 'video';
                            incomingCall.value = true;
                            startRingtone();
                        }
                    }

                    if (typeof e.content === 'string' && e.content.startsWith('__CALL_END__')) {
                        if (isCalling.value || incomingCall.value) {
                            stopRingtone();
                            endCall(true);
                        }
                    }
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.private(`App.Models.User.${page.props.auth.user.id}`).stopListening("MessageSent");
    }
});

// ===== Agora Call Logic =====
const isCalling = ref(false);
const isJoining = ref(false);
const incomingCall = ref(false);
const callChannelName = ref<string>("");
const callMode = ref<'video' | 'audio'>('video');
const client = ref<IAgoraRTCClient | null>(null);
const localAudioTrack = ref<ILocalAudioTrack | null>(null);
const localVideoTrack = ref<ILocalVideoTrack | null>(null);
const micEnabled = ref(true);
const camEnabled = ref(true);
const isMinimized = ref(false);
const pipPos = ref({ x: 16, y: 16 });
const pipSize = ref({ w: 280, h: 180 });
const callStartTime = ref<number | null>(null);
const callDuration = ref('00:00');
const callInfo = ref<{ remoteUser?: string; startTime?: number }>({});
let dragState: null | { startX: number; startY: number; originX: number; originY: number } = null;

// Call duration timer
let durationInterval: number | null = null;

const updateCallDuration = () => {
    if (callStartTime.value) {
        const now = Date.now();
        const seconds = Math.floor((now - callStartTime.value) / 1000);
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        callDuration.value = `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
};

const startCallDuration = () => {
    callStartTime.value = Date.now();
    updateCallDuration();
    durationInterval = setInterval(updateCallDuration, 1000);
};

const stopCallDuration = () => {
    if (durationInterval) {
        clearInterval(durationInterval);
        durationInterval = null;
    }
    callStartTime.value = null;
};
const minimizeCall = () => { isMinimized.value = true; nextTick(() => renderRemoteVideo()); };
const restoreCall = () => { isMinimized.value = false; nextTick(() => renderRemoteVideo()); };
const onDragStart = (e: MouseEvent) => {
    dragState = { startX: e.clientX, startY: e.clientY, originX: pipPos.value.x, originY: pipPos.value.y };
    const onMove = (ev: MouseEvent) => {
        if (!dragState) return;
        const dx = ev.clientX - dragState.startX;
        const dy = ev.clientY - dragState.startY;
        pipPos.value.x = Math.max(8, Math.min(window.innerWidth - pipSize.value.w - 8, dragState.originX + dx));
        pipPos.value.y = Math.max(8, Math.min(window.innerHeight - pipSize.value.h - 8, dragState.originY + dy));
    };
    const onUp = () => { window.removeEventListener('mousemove', onMove); window.removeEventListener('mouseup', onUp); dragState = null; };
    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
};

let ringtoneCtx: AudioContext | null = null;
let ringtoneOsc: OscillatorNode | null = null;
let ringtoneGain: GainNode | null = null;
const isRinging = ref(false);
const startRingtone = () => {
    try {
        if (isRinging.value) return;
        ringtoneCtx = ringtoneCtx || new (window.AudioContext || (window as any).webkitAudioContext)();
        if (!ringtoneCtx) return;
        ringtoneOsc = ringtoneCtx.createOscillator();
        ringtoneGain = ringtoneCtx.createGain();
        ringtoneOsc.type = 'sine';
        ringtoneOsc.frequency.setValueAtTime(880, ringtoneCtx.currentTime);
        ringtoneGain.gain.value = 0.001;
        ringtoneOsc.connect(ringtoneGain);
        ringtoneGain.connect(ringtoneCtx.destination);
        ringtoneOsc.start();
        const schedule = () => {
            if (!ringtoneCtx || !ringtoneGain) return;
            const t = ringtoneCtx.currentTime;
            ringtoneGain.gain.cancelScheduledValues(t);
            ringtoneGain.gain.setValueAtTime(0.0, t);
            ringtoneGain.gain.linearRampToValueAtTime(0.06, t + 0.05);
            ringtoneGain.gain.setValueAtTime(0.06, t + 1.0);
            ringtoneGain.gain.linearRampToValueAtTime(0.0, t + 1.05);
        };
        schedule();
        const interval = setInterval(() => { if (!isRinging.value) { clearInterval(interval); return; } schedule(); }, 1500);
        isRinging.value = true;
    } catch (e) { console.warn('Failed to start ringtone', e); }
};
const stopRingtone = () => {
    try {
        isRinging.value = false;
        if (ringtoneOsc) { try { ringtoneOsc.stop(); } catch { } ringtoneOsc.disconnect(); }
        if (ringtoneGain) ringtoneGain.disconnect();
        ringtoneOsc = null; ringtoneGain = null;
    } catch (e) { console.warn('Failed to stop ringtone', e); }
};

const stableChannelName = () => {
    const a = page.props.auth.user.id as number;
    const b = props.receiver.id as number;
    const min = Math.min(a, b); const max = Math.max(a, b);
    return `video_chat_${min}_${max}`;
};

const fetchToken = async (channel: string) => {
    const url = `/agora/token?channelName=${encodeURIComponent(channel)}`;
    const res = await fetch(url);
    if (!res.ok) throw new Error('Failed to fetch token');
    const json = await res.json();
    return json as { token: string; uid: UID; appId: string };
};

const setupClient = () => {
    if (client.value) return;
    const c = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
    c.on('connection-state-change', (cur, prev) => { console.log('Agora connection state:', prev, '->', cur); });
    const remoteVideoTrackRef = ref<IRemoteVideoTrack | null>(null);
    const remoteUidRef = ref<UID | null>(null);
    const renderRemoteVideo = () => {
        const host = document.getElementById('remote-player');
        if (!host || !remoteVideoTrackRef.value) return;
        host.innerHTML = '';
        const holder = document.createElement('div');
        holder.id = remoteUidRef.value ? `remote-player-${remoteUidRef.value}` : `remote-player-unknown`;
        holder.style.width = '100%'; holder.style.height = '100%'; holder.style.position = 'relative';
        host.appendChild(holder);
        try { remoteVideoTrackRef.value.play(holder); } catch { }
        const v = holder.querySelector('video') as HTMLVideoElement | null;
        if (v) {
            v.style.width = '100%'; v.style.height = '100%'; v.style.objectFit = 'cover';
            v.style.position = 'absolute'; v.style.top = '0'; v.style.left = '0'; v.style.right = '0'; v.style.bottom = '0';
            v.style.background = 'black'; (v as any).playsInline = true; v.autoplay = true;
        }
    };
    c.on('user-published', async (user, mediaType) => {
        try {
            await c.subscribe(user, mediaType);
            if (mediaType === 'video') { callMode.value = 'video'; remoteVideoTrackRef.value = user.videoTrack || null; remoteUidRef.value = user.uid; renderRemoteVideo(); }
            if (mediaType === 'audio') { user.audioTrack?.play(); }
        } catch (err) { console.error('[RTC] subscribe/play failed', user.uid, mediaType, err); }
    });
    c.on('user-unpublished', (user, mediaType) => {
        if (mediaType === 'video') { const el = document.getElementById(`remote-player-${user.uid}`); if (el && el.parentElement) el.parentElement.removeChild(el); else { const host = document.getElementById('remote-player'); if (host) host.innerHTML = ''; } }
    });
    watch([isMinimized, callMode, isCalling], () => nextTick(() => renderRemoteVideo()));
    client.value = c;
};

async function joinCall(channelName: string, withVideo: boolean = true) {
    if (props.feature === true) {
        try {
            setupClient();
            const c = client.value; if (!c) throw new Error('Agora client not initialized');
            const { token, uid, appId } = await fetchToken(channelName);
            await c.join(appId, channelName, token, uid);
            let videoTrack: ILocalVideoTrack | null = null;
            if (withVideo) {
                videoTrack = await AgoraRTC.createCameraVideoTrack();
                localVideoTrack.value = videoTrack;
                await nextTick();
                try { videoTrack.play('local-player'); } catch { }
            }
            let audioTrack = null;
            try { audioTrack = await AgoraRTC.createMicrophoneAudioTrack(); localAudioTrack.value = audioTrack; } catch { }
            const tracks: Array<ILocalAudioTrack | ILocalVideoTrack> = []; if (audioTrack) tracks.push(audioTrack); if (videoTrack) tracks.push(videoTrack);
            if (tracks.length > 0) await c.publish(tracks);
        } catch (error) {
            console.error('Failed to join call:', error); alert('Unable to start the call. Please try again.'); isCalling.value = false;
        }
    } else {
        alert('403 | Upgrade required')
    }
}

const postSignalMessage = (text: string) => { router.post(`/chat/${props.receiver.id}/messages`, { content: text, replied_to: null }, { preserveScroll: true }); };

const startCall = async () => {
    if (props.feature === true) {
        try {
            if (isCalling.value || isJoining.value) return;
            isJoining.value = true;
            callChannelName.value = stableChannelName();
            callMode.value = 'video';
            isCalling.value = true;
            callInfo.value = { remoteUser: props.receiver?.name || 'User' };
            startCallDuration();
            await joinCall(callChannelName.value, true);
            postSignalMessage(`__CALL_INVITE__:${callChannelName.value}`);
        }
        catch (e) { console.error('Failed to start call', e); isCalling.value = false; window.alert('Unable to start call. Please try again.'); }
        finally { isJoining.value = false; }
    } else {
        alert('403 | Upgrade required')
    }
};
const startAudioCall = async () => {
    if (props.feature === true) {
        try {
            if (isCalling.value || isJoining.value) return;
            isJoining.value = true;
            callChannelName.value = stableChannelName();
            callMode.value = 'audio';
            isCalling.value = true;
            callInfo.value = { remoteUser: props.receiver?.name || 'User' };
            startCallDuration();
            await joinCall(callChannelName.value, false);
            postSignalMessage(`__CALL_INVITE_AUDIO__:${callChannelName.value}`);
        }
        catch (e) { console.error('Failed to start audio call', e); isCalling.value = false; window.alert('Unable to start the audio call. Please try again.'); }
        finally { isJoining.value = false; }
    } else {
        alert('403 | Upgrade required')
    }

};
const acceptCall = async () => {
    if (props.feature === true) {

        if (isCalling.value || isJoining.value) return;
        isJoining.value = true;
        if (!callChannelName.value) callChannelName.value = stableChannelName();
        isCalling.value = true;
        callInfo.value = { remoteUser: props.receiver?.name || 'User' };
        startCallDuration();
        try {
            stopRingtone();
            await joinCall(callChannelName.value, callMode.value === 'video');
        }
        catch (e) { console.error('Failed to join call', e); isCalling.value = false; window.alert('Unable to join the call.'); return; }
        incomingCall.value = false;
        isJoining.value = false;
    }
};
const declineCall = () => {
    if (props.feature === true) {

        incomingCall.value = false;
        stopRingtone();
        const channel = callChannelName.value || stableChannelName();
        postSignalMessage(`__CALL_END__:${channel}`);
    }
};
const endCall = async (silent = false) => {
    if (props.feature === true) {
        try {
            stopRingtone();
            stopCallDuration();
            if (localAudioTrack.value) { localAudioTrack.value.stop(); localAudioTrack.value.close(); localAudioTrack.value = null; }
            if (localVideoTrack.value) { localVideoTrack.value.stop(); localVideoTrack.value.close(); localVideoTrack.value = null; }
            if (client.value) { await client.value.leave(); client.value.removeAllListeners(); client.value = null; }
        } finally {
            isCalling.value = false; incomingCall.value = false; if (!silent) postSignalMessage(`__CALL_END__:${callChannelName.value || stableChannelName()}`); callChannelName.value = "";
            callInfo.value = {};
        }
    }
};
const onEndCallClick = (_e: MouseEvent) => { endCall(); };
const toggleMic = async () => { micEnabled.value = !micEnabled.value; if (localAudioTrack.value) await localAudioTrack.value.setEnabled(micEnabled.value); };
const toggleCam = async () => {
    try {
        if (camEnabled.value) { camEnabled.value = false; if (localVideoTrack.value) { setupClient(); const c = client.value; if (c && localVideoTrack.value) { try { await c.unpublish(localVideoTrack.value); } catch { } } try { localVideoTrack.value.stop(); } catch { } try { localVideoTrack.value.close(); } catch { } localVideoTrack.value = null; } return; }
        camEnabled.value = true; if (localVideoTrack.value) { try { await localVideoTrack.value.setEnabled(true); } catch { } return; }
        setupClient(); const c = client.value; if (!c) throw new Error('Agora client not initialized');
        const track = await AgoraRTC.createCameraVideoTrack(); localVideoTrack.value = track; callMode.value = 'video';
        try { await c.publish(track); } catch { }
        nextTick(() => track.play('local-player'));
    } catch (err) { console.error('toggleCam error', err); camEnabled.value = false; }
};

// Inbox events
function onAddMessage(msg: any) { messages.value.push(msg); }
function onJoinCall(content: string) { joinFromSignal(content); }

// Helpers to interpret call signals coming from Inbox
const canJoinFromSignal = (content: string) => content.startsWith('__CALL_INVITE__') || content.startsWith('__CALL_INVITE_AUDIO__');
const joinFromSignal = async (content: string) => {
    if (isCalling.value || isJoining.value) return;
    const parts = content.split(':'); const channel = parts[1];
    if (channel) {
        callMode.value = content.startsWith('__CALL_INVITE_AUDIO__') ? 'audio' : 'video';
        try {
            isJoining.value = true;
            isCalling.value = true; // ensure overlay shows when joining directly from message
            callInfo.value = { remoteUser: props.receiver?.name || 'User' };
            startCallDuration();
            stopRingtone();
            await joinCall(channel, callMode.value === 'video');
        } finally {
            isJoining.value = false;
        }
    }
};
</script>
<template>
    <AuthenticatedLayout>

        <div class="app">
            <div class="topbar">

                <div class="who">

                    <div class="avatar">
                        <img :src="'/storage/' + props.receiver?.more_photos?.[0] || `https://picsum.photos/seed/${props.receiver.id}/100`"
                            alt="Profile" class="avatar" />
                    </div>

                    <div class="meta">

                        <div class="name">{{ props.receiver?.name }}</div>

                        <div class="status">Emojis • GIFs • Photos • PDFs • Ticket Share (QR-first) • Call/Video</div>

                    </div>

                </div>

                <div class="topActions">
                    <div v-if="incomingCall && !isCalling" class="flex items-center gap-2">
                        <span class="text-black font-medium bg-white/80 text-xs px-2 py-1 rounded">Incoming {{ callMode
                            === 'audio'
                            ? 'audio' : 'video' }} call…</span>
                        <button :disabled="isJoining" @click="acceptCall"
                            class="px-3 py-1 rounded-full bg-green-400 text-black text-sm">Accept</button>
                        <button @click="declineCall"
                            class="px-3 py-1 rounded-full bg-red-400 text-black text-sm">Decline</button>
                    </div>

                    <div v-if="!isCalling && !incomingCall" class="flex items-center gap-2">
                        <div class="iconTop" id="callBtn" title="Call" @click="startAudioCall">📞</div>
                        <div class="iconTop" id="videoBtn" title="Video" @click="startCall">🎥</div>
                    </div>

                    <button v-if="isCalling" @click="onEndCallClick"
                        class="px-3 py-1 rounded-full bg-red-500 text-white text-sm">End Call</button>
                </div>

            </div>
            <Inbox ref="inboxRef" :messages="messages" @toggle-emoji="toggleEmoji" @toggle-gif="toggleGif"
                @toggle-ticket="toggleTicket" @add-message="onAddMessage" @send-message="onSendMessage"
                @join-call="onJoinCall" />
        </div>
        <Emoji :open="showEmoji" @close="closeEmoji" @select="onEmojiSelect" />
        <Gif :open="showGif" @close="closeGif" @select="onGifSelect" />
        <Tickets :tickets="props.tickets" ref="ticketsRef" @send-ticket="onTicketSend" />

        <!-- Video/Audio Call Overlay -->
        <div v-if="isCalling && !isMinimized"
            class="fixed inset-0 z-50 bg-gradient-to-br from-gray-900 via-black to-gray-800 flex items-center justify-center">
            <!-- Main call container -->
            <div class="relative w-full max-w-4xl mx-auto p-4">
                <!-- Video call view -->
                <div v-if="callMode === 'video'" class="relative bg-black rounded-2xl overflow-hidden shadow-2xl">
                    <!-- Remote video -->
                    <div id="remote-player" class="w-full aspect-video"></div>

                    <!-- Local video (picture-in-picture style) -->
                    <div
                        class="absolute top-4 right-4 w-32 h-24 bg-gray-800 rounded-lg shadow-lg border-2 border-white/20 overflow-hidden">
                        <div id="local-player" class="w-full h-full"></div>
                    </div>

                    <!-- Call info overlay -->
                    <div class="absolute top-4 left-4 bg-black/60 backdrop-blur-sm rounded-lg px-4 py-2">
                        <div class="text-white text-sm font-medium">{{ callInfo?.remoteUser || 'User' }}</div>
                        <div class="text-white/70 text-xs">{{ callDuration }}</div>
                    </div>
                </div>

                <!-- Audio call view -->
                <div v-else-if="callMode === 'audio'" class="text-center">
                    <!-- Animated audio visualizer circle -->
                    <div class="relative inline-flex items-center justify-center">
                        <div class="absolute w-32 h-32 bg-blue-500/20 rounded-full animate-pulse"></div>
                        <div class="absolute w-24 h-24 bg-blue-500/30 rounded-full animate-pulse animation-delay-200">
                        </div>
                        <div class="absolute w-16 h-16 bg-blue-500/40 rounded-full animate-pulse animation-delay-400">
                        </div>
                        <div
                            class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xl shadow-lg">
                            🔊
                        </div>
                    </div>

                    <!-- Call info -->
                    <div class="mt-8 text-white">
                        <div class="text-2xl font-semibold mb-2">{{ callInfo?.remoteUser || 'User' }}</div>
                        <div class="text-white/70">{{ callDuration }}</div>
                        <div class="mt-2 text-sm text-white/50">Audio call in progress...</div>
                    </div>
                </div>

                <!-- Call controls -->
                <div class="mt-8 flex items-center justify-center gap-4">
                    <button @click="toggleMic"
                        :class="micEnabled ? 'bg-gray-600 hover:bg-gray-700' : 'bg-red-500 hover:bg-red-600'"
                        class="w-14 h-14 rounded-full flex items-center justify-center text-white transition-all transform hover:scale-105 shadow-lg">
                        <svg v-if="micEnabled" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                    </button>

                    <button v-if="callMode === 'video'" @click="toggleCam"
                        :class="camEnabled ? 'bg-gray-600 hover:bg-gray-700' : 'bg-red-500 hover:bg-red-600'"
                        class="w-14 h-14 rounded-full flex items-center justify-center text-white transition-all transform hover:scale-105 shadow-lg">
                        <svg v-if="camEnabled" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </button>

                    <button @click="minimizeCall"
                        class="w-14 h-14 rounded-full bg-gray-600 hover:bg-gray-700 flex items-center justify-center text-white transition-all transform hover:scale-105 shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                    <button @click="onEndCallClick"
                        class="w-16 h-16 rounded-full bg-red-500 hover:bg-red-600 flex items-center justify-center text-white transition-all transform hover:scale-105 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-1a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isCalling && isMinimized"
            class="fixed z-40 shadow-lg rounded-lg overflow-hidden border border-white/20 bg-black"
            :style="{ width: pipSize.w + 'px', height: pipSize.h + 'px', left: pipPos.x + 'px', top: pipPos.y + 'px' }"
            @mousedown.stop>
            <div class="relative w-full h-full" @mousedown="onDragStart">
                <div v-if="callMode === 'video'" id="remote-player" class="w-full h-full"></div>
                <div v-else class="w-full h-full flex items-center justify-center text-white">🔊</div>
                <div class="absolute bottom-1 left-1 right-1 flex items-center justify-center gap-2">
                    <button @click.stop="restoreCall"
                        class="px-2 py-1 rounded bg-white/90 text-black text-xs">Restore</button>
                    <button @click.stop="toggleMic" class="px-2 py-1 rounded bg-white/90 text-black text-xs">{{
                        micEnabled ?
                            'Mute' : 'Unmute' }}</button>
                    <button v-if="callMode === 'video'" @click.stop="toggleCam"
                        class="px-2 py-1 rounded bg-white/90 text-black text-xs">{{ camEnabled ? 'Cam Off' : 'Cam On'
                        }}</button>
                    <button @click.stop="onEndCallClick"
                        class="px-2 py-1 rounded bg-red-500 text-white text-xs">End</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
* {
    box-sizing: border-box
}

body {

    margin: 0;

    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";

    background: radial-gradient(1200px 500px at 15% -10%, rgba(14, 165, 233, .18), transparent 60%),

        radial-gradient(900px 500px at 100% 0%, rgba(223, 255, 0, .18), transparent 55%),

        #f5f7fb;

    color: #0f172a;

}

a {
    color: inherit;
}



.app {
    max-width: 1020px;
    margin: 0 auto;
    padding: 14px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 12px;
}



/* Top bar */

.topbar {

    position: sticky;
    top: 10px;
    z-index: 30;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;

    padding: 12px 14px;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 16px;

    background: rgba(255, 255, 255, .78);
    backdrop-filter: blur(10px);

    box-shadow: 0 12px 26px rgba(2, 6, 23, .08);

}

.who {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.avatar {

    width: 44px;
    height: 44px;
    border-radius: 16px;

    background: linear-gradient(135deg, rgba(14, 165, 233, .95), rgba(223, 255, 0, .75));

    display: grid;
    place-items: center;
    font-weight: 950;

}

.meta {
    min-width: 0
}

.name {
    font-weight: 950;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status {
    font-size: 12px;
    color: #64748b;
    font-weight: 800;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.topActions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.iconTop {

    width: 40px;
    height: 40px;
    border-radius: 14px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .85);

    display: grid;
    place-items: center;

    cursor: pointer;
    transition: .15s;

    box-shadow: 0 10px 18px rgba(2, 6, 23, .05);

    user-select: none;
    font-size: 18px;

}

.iconTop:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .95);
}

.pill {

    font-size: 12px;
    color: #64748b;

    border: 1px solid rgba(148, 163, 184, .35);
    padding: 6px 10px;
    border-radius: 999px;

    background: rgba(255, 255, 255, .85);
    white-space: nowrap;
    font-weight: 800;

}



/* Chat shell */

.chat {

    flex: 1;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 20px;

    background: rgba(255, 255, 255, .82);
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);

    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 640px;

}

.msgs {
    flex: 1;
    padding: 16px;
    overflow: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bubbleRow {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.bubbleRow.me {
    justify-content: flex-end;
}

.bubble {

    max-width: 78%;

    border-radius: 18px;
    padding: 10px 12px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .9);

    box-shadow: 0 10px 18px rgba(2, 6, 23, .06);

}

.me .bubble {
    background: linear-gradient(180deg, rgba(14, 165, 233, .12), rgba(255, 255, 255, .92));
}

.text {
    font-size: 14px;
    line-height: 1.35;
    white-space: pre-wrap;
    word-break: break-word;
}

.time {
    margin-top: 6px;
    font-size: 11px;
    font-weight: 800;
    text-align: right;
}

.media {
    margin-top: 8px;
    display: grid;
    gap: 10px;
}



/* Attachment cards */

.attCard {

    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    padding: 10px;

    display: grid;
    gap: 10px;

}

.attTop {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.attTitle {
    font-weight: 950;
    font-size: 13px;
}

.attMeta {
    font-size: 12px;
    font-weight: 800;
}

.thumbImg {

    width: 100%;

    max-height: 280px;

    object-fit: cover;

    border-radius: 16px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;

}

.gifFrame {

    border-radius: 16px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;

    width: 100%;

    height: 220px;

}

.fileRow {

    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;

    padding: 10px;
    border-radius: 16px;
    border: 1px dashed rgba(148, 163, 184, .55);
    background: rgba(255, 255, 255, .86);

}

.fileName {
    font-weight: 950;
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}



/* QR-first ticket card */

.ticket {

    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    padding: 12px;
    display: grid;
    gap: 12px;

}

.ticketTop {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.ticketTitle {
    font-weight: 950;
    font-size: 14px;
    margin: 0;
}

.ticketMeta {
    margin: 6px 0 0;
    font-size: 12px;
    line-height: 1.35;
    font-weight: 800;
}

.tag {
    font-size: 11px;
    font-weight: 950;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid rgba(14, 165, 233, .30);
    background: rgba(14, 165, 233, .10);
    white-space: nowrap;
}

.qrRow {
    display: flex;
    gap: 14px;
    align-items: center;
    flex-wrap: wrap;
}

.qrBig {

    width: 170px;
    height: 170px;
    border-radius: 18px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;
    display: grid;
    place-items: center;
    overflow: hidden;

    box-shadow: 0 12px 24px rgba(2, 6, 23, .08);

}

.ticketInfo {
    flex: 1;
    min-width: 260px;
    display: grid;
    gap: 8px;
}

.idLine {
    font-weight: 950;
    font-size: 13px;
    line-height: 1.25;
}

.subLine {
    font-weight: 800;
    font-size: 12px;
    line-height: 1.35;
}

.ticketBtns {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}



/* Composer */

.composer {

    border-top: 1px solid rgba(148, 163, 184, .25);

    padding: 12px;
    background: rgba(255, 255, 255, .92);

    display: grid;
    gap: 10px;

}

.tools {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.toolLeft {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.iconBtn {

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .86);

    border-radius: 14px;
    padding: 10px 12px;

    font-weight: 950;
    font-size: 13px;

    cursor: pointer;
    transition: .15s;

    display: inline-flex;
    gap: 8px;
    align-items: center;
    user-select: none;

}

.iconBtn:hover {
    background: rgba(255, 255, 255, .96)
}

.sendBtn {

    border: none;
    border-radius: 14px;
    padding: 12px 14px;
    background: #0ea5e9;

    font-weight: 950;
    font-size: 13px;
    cursor: pointer;
    box-shadow: 0 12px 22px rgba(14, 165, 233, .18);

}

.sendBtn:hover {
    filter: brightness(1.03)
}

.row2 {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

textarea {

    flex: 1;
    min-height: 46px;
    max-height: 160px;
    resize: none;

    border: 1px solid rgba(148, 163, 184, .45);

    border-radius: 14px;
    padding: 12px 12px;
    outline: none;
    font-size: 14px;
    background: rgba(255, 255, 255, .98);

    transition: .15s;

}

textarea:focus {
    border-color: rgba(14, 165, 233, .75);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12)
}



/* Attachment tray (before sending) */

.tray {

    display: none;

    border-radius: 18px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    padding: 10px;

    gap: 10px;

}

.tray.show {
    display: grid;
}

.trayHead {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.trayTitle {
    font-weight: 950;
    font-size: 13px;
}

.trayList {
    display: grid;
    gap: 10px;
}

.trayItem {

    border-radius: 16px;

    border: 1px dashed rgba(148, 163, 184, .55);

    background: rgba(255, 255, 255, .86);

    padding: 10px;

    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;

}

.trayLeft {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.miniPreview {

    width: 54px;
    height: 54px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;
    display: grid;
    place-items: center;
    overflow: hidden;

}

.miniPreview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.removeBtn {

    border: none;
    cursor: pointer;

    border-radius: 12px;

    padding: 8px 10px;

    font-weight: 950;

    background: rgba(239, 68, 68, .10);

    border: 1px solid rgba(239, 68, 68, .25);

}

.removeBtn:hover {
    filter: brightness(1.02)
}



/* Overlay modal */

.overlay {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, .48);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 80;
}

.overlay.show {
    display: flex;
}

.modal {

    width: min(960px, 100%);
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(10px);
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
    overflow: hidden;

}

.modalHead {

    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;

    padding: 14px 16px;
    border-bottom: 1px solid rgba(148, 163, 184, .25);
    background: rgba(255, 255, 255, .85);

}

.modalTitle {
    font-weight: 950;
    margin: 0;
    font-size: 16px;
    letter-spacing: -.2px;
}

.modalSub {
    margin: 6px 0 0;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.35;
}

.x {
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 22px;
    line-height: 1;
    padding: 6px 10px;
    opacity: .75;
}

.x:hover {
    opacity: 1
}

.modalBody {
    padding: 14px 16px 16px;
    display: grid;
    gap: 14px;
}

.grid2 {
    display: grid;
    grid-template-columns: 1.05fr .95fr;
    gap: 14px;
    align-items: start;
}

@media (max-width: 930px) {
    .grid2 {
        grid-template-columns: 1fr;
    }
}



.field {
    display: grid;
    gap: 8px;
}

.label {
    font-size: 12px;
    font-weight: 950;
}

.input {

    width: 100%;
    border: 1px solid rgba(148, 163, 184, .45);
    border-radius: 14px;
    padding: 10px 12px;
    outline: none;
    background: rgba(255, 255, 255, .98);
    font-size: 13px;

}

.input:focus {
    border-color: rgba(14, 165, 233, .75);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12)
}

.notice {

    border-radius: 16px;
    padding: 10px 12px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .85);
    font-size: 12px;
    font-weight: 900;
    line-height: 1.35;
    display: none;

}

.notice.show {
    display: block;
}

.notice.ok {
    border-color: rgba(34, 197, 94, .35);
    background: rgba(34, 197, 94, .10);
    color: #14532d;
}

.notice.warn {
    border-color: rgba(245, 158, 11, .35);
    background: rgba(245, 158, 11, .12);
}

.notice.err {
    border-color: rgba(239, 68, 68, .35);
    background: rgba(239, 68, 68, .10);
}



/* Ticket picker */

.panel {

    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    padding: 12px;
    display: grid;
    gap: 10px;

}

.panelTop {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.kicker {
    font-size: 11px;
    font-weight: 950;
    letter-spacing: .2px;
    text-transform: uppercase;
}

.hint {
    font-size: 12px;
    font-weight: 800;
    line-height: 1.35;
}

.seg {

    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;

    border: 1px solid rgba(148, 163, 184, .35);
    background: rgba(255, 255, 255, .85);

    padding: 6px;
    border-radius: 999px;

}

.seg button {

    border: none;
    background: transparent;
    cursor: pointer;

    font-weight: 950;
    font-size: 12px;
    padding: 8px 10px;
    border-radius: 999px;

}

.seg button.active {

    background: rgba(14, 165, 233, .12);
    border: 1px solid rgba(14, 165, 233, .18);

}

.list {
    display: grid;
    gap: 10px;
}

.ticketRow {

    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    padding: 12px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    align-items: center;

}

.ticketRow:hover {
    box-shadow: 0 14px 26px rgba(2, 6, 23, .06)
}

.rowTitle {
    font-weight: 950;
    margin: 0;
    font-size: 14px;
}

.rowMeta {
    margin: 6px 0 0;
    font-size: 12px;
    line-height: 1.35;
    font-weight: 800;
}

.rowRight {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.miniTag {

    font-size: 11px;
    font-weight: 950;
    padding: 6px 10px;
    border-radius: 999px;

    border: 1px solid rgba(148, 163, 184, .35);
    background: rgba(15, 23, 42, .04);
    white-space: nowrap;

}

.empty {

    padding: 14px;
    border-radius: 18px;
    border: 1px dashed rgba(148, 163, 184, .55);

    background: rgba(255, 255, 255, .86);
    font-weight: 850;
    font-size: 12px;
    line-height: 1.35;

}



/* Buttons */

.btn {

    border: none;
    cursor: pointer;
    border-radius: 14px;
    padding: 10px 12px;

    font-weight: 950;
    font-size: 13px;
    letter-spacing: .2px;
    transition: .15s;

    display: inline-flex;
    align-items: center;
    gap: 8px;

}

.btnPrimary {
    background: #0ea5e9;
    color: #fff;
    box-shadow: 0 12px 22px rgba(14, 165, 233, .18);
}

.btnPrimary:hover {
    filter: brightness(1.03)
}

.btnGhost {
    background: rgba(15, 23, 42, .04);
    border: 1px solid rgba(148, 163, 184, .35);
}

.btnGhost:hover {
    background: rgba(15, 23, 42, .06)
}

.btnDanger {
    background: rgba(239, 68, 68, .10);
    border: 1px solid rgba(239, 68, 68, .25);
}

.btnDanger:hover {
    filter: brightness(1.02)
}



/* Token box */

.tokenBox {

    border-radius: 16px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(2, 6, 23, .92);
    padding: 10px 12px;

    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;

    font-size: 12px;

    line-height: 1.35;

    white-space: pre-wrap;

    overflow: auto;

    max-height: 120px;

}



/* Emoji / GIF picker overlays (small) */

.miniPop {

    position: fixed;

    right: 16px;

    bottom: 110px;

    width: min(520px, calc(100vw - 32px));

    border-radius: 20px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    backdrop-filter: blur(10px);

    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);

    display: none;

    z-index: 75;

    overflow: hidden;

}

.miniPop.show {
    display: block;
}

.miniPopHead {

    padding: 12px 14px;

    border-bottom: 1px solid rgba(148, 163, 184, .25);

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;

    background: rgba(255, 255, 255, .85);

}

.miniPopTitle {
    font-weight: 950;
}

.miniPopBody {
    padding: 12px 14px;
    display: grid;
    gap: 10px;
    overflow-y: auto;
    max-height: 400px;
}

.emojiGrid {

    display: grid;

    grid-template-columns: repeat(10, 1fr);

    gap: 8px;

}

@media (max-width: 560px) {
    .emojiGrid {
        grid-template-columns: repeat(8, 1fr);
    }
}

.emo {

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    border-radius: 14px;

    height: 38px;

    display: grid;
    place-items: center;

    cursor: pointer;

    transition: .12s;

    user-select: none;

    font-size: 18px;

}

.emo:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .98);
}



.gifGrid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 10px;

}

@media (max-width: 560px) {
    .gifGrid {
        grid-template-columns: 1fr;
    }
}

.gifCard {

    border-radius: 18px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    overflow: hidden;

    cursor: pointer;

    transition: .12s;

}

.gifCard:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 22px rgba(2, 6, 23, .08);
}

.gifCard img {
    width: 100%;
    height: 170px;
    border: 0;
}

.gifMsg {
    max-width: 300px;
    height: auto;
}

.gifCap {
    padding: 10px;
    font-size: 12px;
    color: #64748b;
    font-weight: 900;
}

.sr {
    position: absolute;
    left: -9999px
}



/* Hidden file inputs */

input[type="file"] {
    display: none
}

.chat {
    display: flex;
    flex-direction: column;
    height: 100%;
}
</style>
