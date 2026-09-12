<template>
  <LkPageShell active-page="chats">
    <template v-if="activeChat">
      <div class="max-w-2xl mx-auto">
        <div class="card p-3 flex items-center gap-2 mb-3">
          <button @click="closeChat" class="pr-1" aria-label="Back to chats">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
          </button>
          <img :src="activeChat.img" :alt="activeChat.name" class="h-10 w-10 rounded-full object-cover" />
          <div class="flex-1 min-w-0">
            <p class="font-black leading-tight">{{ activeChat.name }} {{ activeChat.flag }}</p>
            <p class="text-[11px] text-emerald-600 font-bold">{{ activeChat.online ? 'Online' : 'Last seen recently' }}</p>
          </div>
          <div v-if="!isCalling && !incomingCall" class="flex items-center gap-2">
            <button @click="startAudioCall" :disabled="isJoining" class="call-action" title="Start audio call" aria-label="Start audio call">
              <Phone class="w-4 h-4" />
            </button>
            <button @click="startVideoCall" :disabled="isJoining" class="call-action" title="Start video call" aria-label="Start video call">
              <Video class="w-4 h-4" />
            </button>
            <!-- Wallpaper control is intentionally visual-only for now. -->
            <button class="call-action" title="Chat wallpaper" aria-label="Chat wallpaper">
              <Palette class="w-4 h-4" />
            </button>
          </div>
          <button v-else-if="isCalling" @click="endCall()" class="call-action !bg-rose-50 !text-rose-600" title="End call" aria-label="End call">
            <PhoneOff class="w-4 h-4" />
          </button>
        </div>

        <div v-if="incomingCall && !isCalling" class="mb-3 rounded-2xl bg-blue-50 border border-blue-100 p-3 flex items-center justify-between gap-3">
          <p class="text-sm font-bold text-slate-700">Incoming {{ callMode }} call from {{ activeChat.name }}</p>
          <div class="flex gap-2">
            <button @click="acceptCall" :disabled="isJoining" class="rounded-full bg-emerald-500 text-white px-3 py-1.5 text-xs font-bold">Accept</button>
            <button @click="declineCall" class="rounded-full bg-rose-500 text-white px-3 py-1.5 text-xs font-bold">Decline</button>
          </div>
        </div>

        <div ref="chatBoxRef" class="rounded-2xl p-3 mb-3 overflow-y-auto hide-scroll" style="height:calc(100vh - 300px);min-height:280px;background:#e7efe8">
          <div v-if="loadingMessages" class="h-full grid place-items-center text-sm text-slate-500">Loading messages…</div>
          <template v-else>
            <div v-if="chatMessages.length === 0" class="h-full grid place-items-center text-sm text-slate-500">Start the conversation.</div>
            <div v-for="msg in chatMessages" :key="msg.id" :class="['flex mb-1.5', isMine(msg) ? 'justify-end' : 'justify-start']">
              <div :class="['max-w-[75%] px-3 py-2 rounded-2xl text-sm shadow-sm', isMine(msg) ? 'text-white rounded-br-sm' : 'bg-white rounded-bl-sm']" :style="isMine(msg) ? 'background:var(--lk-blue)' : ''">
                <img v-if="messageImage(msg)" :src="messageImage(msg)" class="rounded-xl mb-1 max-h-52" alt="Shared image" />
                <iframe v-else-if="messageGif(msg)" :src="messageGif(msg)" class="rounded-xl mb-1 w-full h-48 border-0" title="Shared GIF" allowfullscreen></iframe>
                <a v-else-if="messageFile(msg)" :href="messageFile(msg)" target="_blank" rel="noopener" :class="isMine(msg) ? 'underline text-white' : 'underline text-lkblue2'">{{ messageFileLabel(msg) }}</a>
                <div v-else-if="msg.type === 'ticket'" class="rounded-xl bg-white text-slate-800 p-2.5 min-w-[240px]">
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <p class="font-black">🎟️ {{ ticketData(msg).event || 'Ticket' }}</p>
                      <p class="text-[11px] text-slate-500">{{ ticketData(msg).date_label || 'Date not provided' }} · {{ ticketData(msg).venue || 'Venue not provided' }} · {{ ticketData(msg).ticket_type || 'Ticket' }}</p>
                    </div>
                    <span class="rounded-full bg-blue-50 text-blue-600 px-2 py-1 text-[10px] font-black">Ticket</span>
                  </div>
                  <div class="flex gap-2 items-center mt-2">
                    <button
                      type="button"
                      class="rounded-lg border border-slate-200 p-1 bg-white shrink-0 cursor-pointer transition hover:border-lkblue2 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-lkblue2"
                      title="Open ticket QR code"
                      @click="openTicketQr(msg)"
                    >
                      <QrcodeVue :value="ticketQrValue(msg)" :size="120" level="M" />
                    </button>
                    <div class="text-[11px] leading-5">
                      <button
                        type="button"
                        class="mb-1 rounded-full bg-blue-600 px-3 py-1.5 text-[11px] font-black text-white transition hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60"
                        :disabled="loadingTicketDetails === ticketData(msg).ticket_sale_id"
                        @click="openTicketDetails(msg)"
                      >
                        {{ loadingTicketDetails === ticketData(msg).ticket_sale_id ? 'Loading details...' : 'Check details' }}
                      </button>
                      <p class="font-black break-all">🎟️ {{ ticketData(msg).ticket_id || 'Ticket ID unavailable' }}</p>
                      <p>📍 {{ ticketData(msg).city || 'City not provided' }}</p>
                      <p>🗓️ {{ ticketData(msg).date_label || 'Date not provided' }}</p>
                      <p>✅ QR is the key — scan to validate entry.</p>
                      <p>🔐 QR payload is a signed token.</p>
                    </div>
                  </div>
                </div>
                <span v-else>{{ messageText(msg) }}</span>
                <span :class="['text-[10px] ml-1 whitespace-nowrap', isMine(msg) ? 'text-white/70' : 'text-slate-400']">{{ formatTime(msg.created_at) }}</span>
              </div>
            </div>
          </template>
        </div>

        <div class="card p-2">
          <div class="flex gap-1.5 overflow-x-auto hide-scroll pb-2">
            <button @click="toggleEmoji" class="chip">😊 Emoji</button>
            <button @click="toggleGif" class="chip">🎞 GIF</button>
            <button @click="selectFile('photo')" class="chip">🖼 Photo</button>
            <button @click="selectFile('pdf')" class="chip">📎 PDF</button>
            <button @click="shareLocation" class="chip">📍 Location</button>
            <button @click="openTickets" class="chip on">🎟️ Ticket</button>
            <input ref="fileInputRef" type="file" class="hidden" @change="uploadFile" />
          </div>
          <div class="flex items-center gap-2">
            <input v-model="chatInput" :disabled="sending" @keyup.enter="sendText" class="flex-1 rounded-full border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue disabled:bg-slate-100" placeholder="Type a message…" />
            <button @click="sendText" :disabled="sending" class="rounded-full text-white h-11 w-11 grid place-items-center shrink-0 disabled:opacity-50" style="background:var(--lk-blue)">
              <Send class="w-5 h-5" />
            </button>
          </div>
        </div>
        <Emoji :open="showEmoji" @close="showEmoji = false" @select="onEmojiSelect" />
        <Gif :open="showGif" @close="showGif = false" @select="onGifSelect" />
        <Tickets ref="ticketsRef" :tickets="props.serverTickets" @send-ticket="onTicketSend" />
        <TicketDetailModal ref="ticketDetailModalRef" />

        <div v-if="selectedTicketQr" class="fixed inset-0 z-[60] bg-slate-950/75 p-4 grid place-items-center" @click.self="closeTicketQr">
          <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">
            <div class="flex items-start justify-between gap-3 text-left">
              <div>
                <p class="font-black text-slate-900">{{ ticketData(selectedTicketQr).event || 'Ticket QR Code' }}</p>
                <p class="text-xs text-slate-500 mt-1">Show this code at entry to validate your ticket.</p>
              </div>
              <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" aria-label="Close ticket QR code" @click="closeTicketQr">&times;</button>
            </div>
            <div class="mt-5 inline-block rounded-2xl border border-slate-200 p-3">
              <QrcodeVue :value="ticketQrValue(selectedTicketQr)" :size="280" level="M" />
            </div>
          </div>
        </div>

        <div v-if="isCalling" class="fixed inset-0 z-50 bg-slate-950/95 grid place-items-center p-4">
          <div class="w-full max-w-3xl text-white">
            <div v-if="callMode === 'video'" class="relative rounded-3xl overflow-hidden bg-black aspect-video shadow-2xl">
              <div ref="remotePlayerRef" class="w-full h-full grid place-items-center text-slate-400">Waiting for {{ activeChat.name }}…</div>
              <div ref="localPlayerRef" class="absolute bottom-4 right-4 w-32 aspect-video rounded-xl overflow-hidden bg-slate-800 border border-white/20"></div>
            </div>
            <div v-else class="py-20 text-center">
              <div class="mx-auto w-28 h-28 rounded-full bg-blue-500/20 grid place-items-center animate-pulse"><Phone class="w-10 h-10" /></div>
            </div>
            <div class="text-center mt-5">
              <p class="text-xl font-black">{{ activeChat.name }}</p>
              <p class="text-sm text-white/65 mt-1">{{ callMode === 'video' ? 'Video' : 'Audio' }} call · {{ callDuration }}</p>
              <div class="flex justify-center gap-3 mt-5">
                <button @click="toggleMic" class="call-control">{{ micEnabled ? 'Mute' : 'Unmute' }}</button>
                <button v-if="callMode === 'video'" @click="toggleCamera" class="call-control">{{ cameraEnabled ? 'Camera off' : 'Camera on' }}</button>
                <button @click="endCall()" class="call-control bg-rose-500 border-rose-500">End call</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <h2 class="text-2xl font-black mb-3">Chats</h2>

      <div v-if="people.length === 0" class="card p-12 text-center">
        <i data-lucide="message-circle" class="w-10 h-10 mx-auto text-slate-200"></i>
        <p class="font-black mt-3">No chats yet</p>
        <p class="text-slate-400 text-sm mt-1">Match with someone to start chatting!</p>
        <a href="/new_frontend/dating" class="btn btn-primary px-5 py-3 mt-4 inline-block">Go to Swipe</a>
      </div>

      <section v-if="pinnedPeople.length" class="mb-5">
        <p class="text-xs uppercase tracking-wider font-black text-slate-500 mb-2">Pinned</p>
        <ChatRow v-for="p in pinnedPeople" :key="p.id" :person="p" @open="openChat" @pin="togglePin" />
      </section>
      <section v-if="unpinnedPeople.length">
        <p v-if="pinnedPeople.length" class="text-xs uppercase tracking-wider font-black text-slate-500 mb-2">All chats</p>
        <ChatRow v-for="p in unpinnedPeople" :key="p.id" :person="p" @open="openChat" @pin="togglePin" />
      </section>
    </template>
  </LkPageShell>
</template>

<script setup>
import { computed, defineComponent, h, nextTick, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import AgoraRTC from 'agora-rtc-sdk-ng';
import { Palette, Phone, PhoneOff, Send, Video } from 'lucide-vue-next';
import QrcodeVue from 'qrcode.vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import LkPageShell from '../../../components/new_frontend/LkPageShell.vue';
import Emoji from '../../User/Chat/components/Emoji.vue';
import Gif from '../../User/Chat/components/Gif.vue';
import Tickets from '../../User/Chat/components/Tickets.vue';
import TicketDetailModal from '../../../components/new_frontend/modals/TicketDetailModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  currentUser: { type: Object, default: () => ({}) },
  serverChatPeople: { type: [Array, Object], default: () => [] },
  serverTickets: { type: Array, default: () => [] },
});

const mapUser = (user) => ({
  ...user,
  id: user.id,
  name: user.name,
  flag: user.country_flag || '🌍',
  online: Boolean(user.is_live_streaming),
  pinned: Boolean(user.pinned),
  unread_count: Number(user.unread_count || 0),
  img: user.avatar || `https://i.pravatar.cc/500?img=${user.id % 70}`,
});

const people = ref((Array.isArray(props.serverChatPeople) ? props.serverChatPeople : Object.values(props.serverChatPeople || {})).map(mapUser));
const pinnedPeople = computed(() => people.value.filter((person) => person.pinned));
const unpinnedPeople = computed(() => people.value.filter((person) => !person.pinned));
const activeChat = ref(null);
const chatMessages = ref([]);
const chatInput = ref('');
const chatBoxRef = ref(null);
const fileInputRef = ref(null);
const attachmentType = ref('photo');
const loadingMessages = ref(false);
const sending = ref(false);
const showEmoji = ref(false);
const showGif = ref(false);
const ticketsRef = ref(null);
const ticketDetailModalRef = ref(null);
const selectedTicketQr = ref(null);
const loadingTicketDetails = ref(null);
const isCalling = ref(false);
const isJoining = ref(false);
const incomingCall = ref(false);
const callMode = ref('video');
const callChannelName = ref('');
const callDuration = ref('00:00');
const micEnabled = ref(true);
const cameraEnabled = ref(true);
const remotePlayerRef = ref(null);
const localPlayerRef = ref(null);
let agoraClient = null;
let localAudioTrack = null;
let localVideoTrack = null;
let permissionStream = null;
let callStartedAt = null;
let callTimer = null;

const messagePreview = (message) => {
  if (!message) return 'No messages yet';
  if (message.type === 'image') return 'Sent a photo';
  if (message.type === 'gif') return 'Sent a GIF';
  if (message.type === 'pdf') return 'Sent a PDF';
  if (message.type === 'file') return 'Sent a file';
  if (message.type === 'ticket') return 'Sent a ticket';
  return message.content || 'New message';
};

const ChatRow = defineComponent({
  props: { person: { type: Object, required: true } },
  emits: ['open', 'pin'],
  setup(rowProps, { emit }) {
    return () => h('div', { class: 'card p-3 flex items-center gap-3 mb-2' }, [
      h('button', { class: 'flex flex-1 min-w-0 items-center gap-3 text-left', onClick: () => emit('open', rowProps.person) }, [
        h('div', { class: 'relative shrink-0' }, [
          h('img', { src: rowProps.person.img, class: 'h-14 w-14 rounded-full object-cover', alt: rowProps.person.name }),
          rowProps.person.online ? h('span', { class: 'absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-400 border-2 border-white' }) : null,
        ]),
        h('div', { class: 'flex-1 min-w-0' }, [
          h('p', { class: 'font-black' }, rowProps.person.name),
          h('p', { class: 'text-xs text-slate-500 truncate' }, messagePreview(rowProps.person.last_message)),
        ]),
        rowProps.person.unread_count ? h('span', { class: 'rounded-full bg-lkblue2 text-white text-[11px] min-w-5 h-5 px-1 grid place-items-center font-black' }, String(rowProps.person.unread_count)) : null,
      ]),
      h('button', { class: 'p-2 cursor-pointer text-slate-400 hover:text-lkblue2', title: rowProps.person.pinned ? 'Unpin chat' : 'Pin chat', onClick: () => emit('pin', rowProps.person) }, rowProps.person.pinned ? '📌' : '📍'),
    ]);
  },
});

const conversationUrl = (person) => `/new_frontend/dating/chats/${person.id}/messages`;
const scrollToBottom = () => nextTick(() => {
  if (chatBoxRef.value) chatBoxRef.value.scrollTop = chatBoxRef.value.scrollHeight;
});

const openChat = async (person) => {
  activeChat.value = person;
  loadingMessages.value = true;
  chatMessages.value = [];
  try {
    const { data } = await axios.get(conversationUrl(person));
    chatMessages.value = data.messages || [];
    person.unread_count = 0;
  } catch (error) {
    alert(error.response?.data?.message || 'Unable to load this conversation.');
    activeChat.value = null;
  } finally {
    loadingMessages.value = false;
    scrollToBottom();
  }
};

const closeChat = () => { activeChat.value = null; chatMessages.value = []; };
const isMine = (message) => Number(message.from_user_id) === Number(props.currentUser.id);
const formatTime = (value) => value ? new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
const messageImage = (message) => message.type === 'image' ? message.meta?.url : null;
const messageGif = (message) => message.type === 'gif' ? message.meta?.url : null;
const messageFile = (message) => ['pdf', 'file'].includes(message.type) ? message.meta?.url : null;
const messageFileLabel = (message) => message.meta?.name || (message.type === 'pdf' ? 'View PDF' : 'View attachment');
const messageText = (message) => message.type === 'ticket' ? '🎟️ Shared a ticket' : (message.content || 'Attachment');
const ticketData = (message) => message.meta || {};
const ticketQrValue = (message) => {
  const ticket = ticketData(message);
  const code = ticket.token || ticket.ticket_qrcode_id || ticket.ticket_qrcode || ticket.ticket_sale_id || '';
  return `${window.location.origin}/organizer/scanner/scan?code=${encodeURIComponent(String(code))}`;
};
const openTicketQr = (message) => { selectedTicketQr.value = message; };
const closeTicketQr = () => { selectedTicketQr.value = null; };
const openTicketDetails = async (message) => {
  const sharedTicketDetails = ticketData(message).ticket_details;
  if (sharedTicketDetails) {
    ticketDetailModalRef.value?.open(sharedTicketDetails);
    return;
  }

  const ticketSaleId = ticketData(message).ticket_sale_id;
  if (!ticketSaleId || loadingTicketDetails.value) return;

  loadingTicketDetails.value = ticketSaleId;
  try {
    const { data } = await axios.get(`/api/regular-user/events/bookings/${ticketSaleId}`);
    ticketDetailModalRef.value?.open(data.ticket || data.data || data);
  } catch (error) {
    alert(error.response?.status === 403
      ? 'Only the ticket owner can view its complete details.'
      : (error.response?.data?.message || 'Ticket details could not be loaded.'));
  } finally {
    loadingTicketDetails.value = null;
  }
};

const updateLastMessage = (message) => {
  const person = people.value.find((item) => item.id === activeChat.value?.id);
  if (person) person.last_message = message;
};

const postMessage = async (payload) => {
  if (!activeChat.value || sending.value) return;
  sending.value = true;
  try {
    const { data } = await axios.post(conversationUrl(activeChat.value), payload);
    const created = data.messages || [];
    chatMessages.value.push(...created);
    if (created.length) updateLastMessage(created[created.length - 1]);
    scrollToBottom();
  } catch (error) {
    alert(error.response?.data?.message || 'Message could not be sent.');
  } finally {
    sending.value = false;
  }
};

const sendText = async () => {
  const content = chatInput.value.trim();
  if (!content) return;
  chatInput.value = '';
  await postMessage({ content });
};
const toggleEmoji = () => { showEmoji.value = !showEmoji.value; showGif.value = false; };
const toggleGif = () => { showGif.value = !showGif.value; showEmoji.value = false; };
const onEmojiSelect = (emoji) => { chatInput.value += emoji; };
const onGifSelect = async (url) => {
  showGif.value = false;
  if (url) await postMessage({ gif_url: url });
};
const openTickets = () => ticketsRef.value?.openModal();
const onTicketSend = async (ticket) => {
  if (!ticket?.ticket_sale_id) return;
  await postMessage({ ticket_sale_id: ticket.ticket_sale_id, ticket_qty: ticket.ticket_qty || 1 });
};
const selectFile = (type) => {
  attachmentType.value = type;
  fileInputRef.value.accept = type === 'photo' ? 'image/*' : 'application/pdf';
  fileInputRef.value.click();
};
const uploadFile = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;
  const form = new FormData();
  form.append('attachments[0][file]', file);
  form.append('attachments[0][type]', attachmentType.value);
  form.append('attachments[0][name]', file.name);
  await postMessage(form);
  event.target.value = '';
};
const shareLocation = () => {
  if (!navigator.geolocation) return alert('Location sharing is not supported by this browser.');
  navigator.geolocation.getCurrentPosition(
    ({ coords }) => postMessage({ content: `📍 https://maps.google.com/?q=${coords.latitude},${coords.longitude}` }),
    () => alert('Location permission was not granted.'),
  );
};
const togglePin = async (person) => {
  try {
    const { data } = await axios.post(`${conversationUrl(person).replace('/messages', '')}/pin`);
    person.pinned = data.pinned;
  } catch (error) {
    alert(error.response?.data?.message || 'Pin status could not be updated.');
  }
};

const callSignal = (prefix, channel) => `${prefix}:${channel}`;
const callChannel = () => {
  const [first, second] = [Number(props.currentUser.id), Number(activeChat.value.id)].sort((a, b) => a - b);
  return `video_chat_${first}_${second}`;
};
const updateDuration = () => {
  const seconds = Math.floor((Date.now() - callStartedAt) / 1000);
  callDuration.value = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
};
const startDuration = () => { callStartedAt = Date.now(); updateDuration(); callTimer = window.setInterval(updateDuration, 1000); };
const stopDuration = () => { if (callTimer) window.clearInterval(callTimer); callTimer = null; callStartedAt = null; callDuration.value = '00:00'; };
const fetchAgoraToken = async () => {
  const { data } = await axios.get('/agora/token', { params: { recipient_id: activeChat.value.id } });
  return data;
};
const setupAgoraClient = () => {
  if (agoraClient) return;
  agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
  agoraClient.on('user-published', async (user, mediaType) => {
    await agoraClient.subscribe(user, mediaType);
    if (mediaType === 'audio') user.audioTrack?.play();
    if (mediaType === 'video' && remotePlayerRef.value) {
      // Agora appends the video element to this container. Remove the waiting
      // text first so CSS grid does not lay the text and video out as two rows.
      remotePlayerRef.value.replaceChildren();
      user.videoTrack?.play(remotePlayerRef.value);
    }
  });
};
const requestCallPermissions = async (withVideo) => {
  if (!navigator.mediaDevices?.getUserMedia) {
    throw new Error('This browser does not support microphone or camera access.');
  }

  // Ask while the user is still directly interacting with the call button.
  // Agora then reuses this approved device access when it creates its tracks.
  permissionStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: withVideo });
};
const joinCall = async (withVideo) => {
  setupAgoraClient();
  const { token, uid, appId } = await fetchAgoraToken();
  await agoraClient.join(appId, callChannelName.value, token, uid);
  const microphoneTrack = permissionStream?.getAudioTracks()[0];
  if (!microphoneTrack) throw new Error('No microphone was found. Connect or enable a microphone, then try again.');
  localAudioTrack = AgoraRTC.createCustomAudioTrack({ mediaStreamTrack: microphoneTrack });
  const tracks = [localAudioTrack];
  if (withVideo) {
    const cameraTrack = permissionStream?.getVideoTracks()[0];
    if (!cameraTrack) throw new Error('No camera was found. Connect or enable a camera, then try again.');
    localVideoTrack = AgoraRTC.createCustomVideoTrack({ mediaStreamTrack: cameraTrack });
    tracks.push(localVideoTrack);
    await nextTick();
    if (localPlayerRef.value) localVideoTrack.play(localPlayerRef.value);
  }
  await agoraClient.publish(tracks);
};
const startCall = async (mode) => {
  if (!activeChat.value || isJoining.value || isCalling.value) return;
  isJoining.value = true;
  callMode.value = mode;
  callChannelName.value = callChannel();
  try {
    await requestCallPermissions(mode === 'video');
    isCalling.value = true;
    startDuration();
    await joinCall(mode === 'video');
    await postMessage({ content: callSignal(mode === 'video' ? '__CALL_INVITE__' : '__CALL_INVITE_AUDIO__', callChannelName.value) });
  } catch (error) {
    console.error('Unable to start call', error);
    await endCall(true);
    const denied = ['NotAllowedError', 'SecurityError'].includes(error?.name);
    const deviceMissing = error?.name === 'NotFoundError';
    alert(denied
      ? 'Camera/microphone permission was denied. Please allow access in your browser settings, then try again.'
      : (deviceMissing ? `No ${mode === 'video' ? 'camera or microphone' : 'microphone'} was found. Connect or enable the device, then try again.` : (error?.message || 'Unable to start the call. Please try again.')));
  } finally { isJoining.value = false; }
};
const startVideoCall = () => startCall('video');
const startAudioCall = () => startCall('audio');
const acceptCall = async () => {
  if (isJoining.value || isCalling.value) return;
  isJoining.value = true;
  try {
    await requestCallPermissions(callMode.value === 'video');
    isCalling.value = true;
    startDuration();
    await joinCall(callMode.value === 'video');
    incomingCall.value = false;
  } catch (error) {
    console.error('Unable to accept call', error);
    await endCall(true);
    alert('Unable to join the call.');
  } finally { isJoining.value = false; }
};
const declineCall = async () => {
  const channel = callChannelName.value || callChannel();
  incomingCall.value = false;
  await postMessage({ content: callSignal('__CALL_END__', channel) });
  callChannelName.value = '';
};
const endCall = async (silent = false) => {
  stopDuration();
  try {
    localAudioTrack?.stop(); localAudioTrack?.close(); localAudioTrack = null;
    localVideoTrack?.stop(); localVideoTrack?.close(); localVideoTrack = null;
    permissionStream?.getTracks().forEach((track) => track.stop()); permissionStream = null;
    if (agoraClient) { await agoraClient.leave(); agoraClient.removeAllListeners(); agoraClient = null; }
  } finally {
    const channel = callChannelName.value;
    isCalling.value = false; incomingCall.value = false; callChannelName.value = '';
    if (!silent && channel && activeChat.value) await postMessage({ content: callSignal('__CALL_END__', channel) });
  }
};
const toggleMic = async () => { micEnabled.value = !micEnabled.value; await localAudioTrack?.setEnabled(micEnabled.value); };
const toggleCamera = async () => { cameraEnabled.value = !cameraEnabled.value; await localVideoTrack?.setEnabled(cameraEnabled.value); };
const handleRealtimeMessage = (event) => {
  if (!activeChat.value || Number(event.from_user_id) !== Number(activeChat.value.id)) return;
  const content = event.content || '';
  if (content.startsWith('__CALL_INVITE__') || content.startsWith('__CALL_INVITE_AUDIO__')) {
    callMode.value = content.startsWith('__CALL_INVITE_AUDIO__') ? 'audio' : 'video';
    callChannelName.value = content.split(':')[1] || callChannel();
    incomingCall.value = true;
  } else if (content.startsWith('__CALL_END__')) {
    if (isCalling.value || incomingCall.value) endCall(true);
  } else if (!chatMessages.value.some((message) => message.id === event.id)) {
    chatMessages.value.push(event); scrollToBottom();
  }
};
onMounted(() => {
  const echo = window.Echo;
  if (echo && props.currentUser.id) echo.private(`App.Models.User.${props.currentUser.id}`).listen('MessageSent', handleRealtimeMessage);
});
onUnmounted(() => {
  stopDuration();
  if (isCalling.value) endCall(true);
  if (window.Echo && props.currentUser.id) window.Echo.private(`App.Models.User.${props.currentUser.id}`).stopListening('MessageSent');
});
</script>

<style scoped>
.call-action { align-items:center; background:#eff6ff; border-radius:9999px; color:#2563eb; display:grid; height:2.5rem; justify-items:center; transition:background .15s ease; width:2.5rem; }
.call-action:hover { background:#dbeafe; }
.call-action:disabled { opacity:.5; }
.call-control { background:rgb(255 255 255 / .1); border:1px solid rgb(255 255 255 / .2); border-radius:9999px; font-size:.875rem; font-weight:700; padding:.5rem 1rem; transition:background .15s ease; }
.call-control:hover { background:rgb(255 255 255 / .2); }
</style>
