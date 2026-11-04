<script setup lang="ts">
import { ref, nextTick, watch } from "vue";
import QrcodeVue from 'qrcode.vue';
const emit = defineEmits(['send-message', 'toggle-emoji', 'toggle-gif', 'toggle-ticket', 'add-message', 'join-call']);

const inputRef = ref<HTMLTextAreaElement | null>(null);
const messagesContainer = ref<HTMLDivElement | null>(null);
const props = defineProps<{
    messages: Array<
        | { type: 'text'; content: string; me?: boolean; created_at?: string }
        | { type: 'gif'; url: string; me?: boolean; created_at?: string }
        | { type: 'image'; url: string; name?: string; me?: boolean; created_at?: string }
        | { type: 'pdf'; url: string; name?: string; me?: boolean; created_at?: string }
        | { type: 'ticket'; ticket: any; me?: boolean; created_at?: string }
    >
}>();
type Attachment =
    | {
        id: string;
        kind: 'photo';
        name: string;
        sizeKB: number;
        previewUrl: string;
        file: File;
    }
    | {
        id: string;
        kind: 'pdf';
        name: string;
        sizeKB: number;
        file: File;
    }
    | {
        id: string;
        kind: 'ticket';
        ticket: any;
    };

const attachments = ref<Attachment[]>([]);

const modalVisible = ref(false);
const selectedTicket = ref<any>(null);

const photoInputRef = ref<HTMLInputElement | null>(null);
const pdfInputRef = ref<HTMLInputElement | null>(null);
function openPhotoPicker() {
    photoInputRef.value?.click();
}

function openPdfPicker() {
    pdfInputRef.value?.click();
}
async function onPhotosSelected(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;

    for (const file of Array.from(input.files)) {
        if (!file.type.startsWith('image/')) continue;

        const previewUrl = await fileToDataUrl(file);

        attachments.value.push({
            id: crypto.randomUUID(),
            kind: 'photo',
            name: file.name,
            sizeKB: Math.round(file.size / 1024),
            previewUrl,
            file
        });
    }

    input.value = '';
}

function onPdfsSelected(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;

    for (const file of Array.from(input.files)) {
        if (file.type !== 'application/pdf') continue;

        attachments.value.push({
            id: crypto.randomUUID(),
            kind: 'pdf',
            name: file.name,
            sizeKB: Math.round(file.size / 1024),
            file
        });
    }

    input.value = '';
}
function removeAttachment(id: string) {
  attachments.value = attachments.value.filter(a => a.id !== id);
}
function sendMessage() {
  const text = inputRef.value?.value.trim() ?? '';

  if (!text && !attachments.value.length) return;

  // No optimistic adds for tickets or files; rely on backend + Echo/props to avoid duplicates
  const fileAttachments = attachments.value.filter(att => att.kind !== 'ticket');
  if (text || fileAttachments.length) {
    emit('send-message', {
      type: 'message',
      text,
      attachments: buildUploadPayload()
    });
  }

  if (inputRef.value) inputRef.value.value = '';
  attachments.value = [];
}
function buildUploadPayload() {
  const formData = new FormData();

  attachments.value.filter(att => att.kind !== 'ticket').forEach((att, index) => {
    formData.append(`attachments[${index}][file]`, att.file);
    formData.append(`attachments[${index}][type]`, att.kind);
    formData.append(`attachments[${index}][name]`, att.name);
  });

  return formData;
}

function fileToDataUrl(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result as string);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

const isCallSignal = (content: string) => {
    return (
        typeof content === 'string' && (
            content.startsWith('__CALL_INVITE__') ||
            content.startsWith('__CALL_INVITE_AUDIO__') ||
            content.startsWith('__CALL_END__')
        )
    );
};
const renderSignalText = (content: string) => {
    if (content.startsWith('__CALL_INVITE_AUDIO__')) return 'Audio call started';
    if (content.startsWith('__CALL_INVITE__')) return 'Video call started';
    if (content.startsWith('__CALL_END__')) return 'Call ended';
    return '';
};
const canJoinFromSignal = (content: string) => content.startsWith('__CALL_INVITE__') || content.startsWith('__CALL_INVITE_AUDIO__');

function joinCall(content: string) {
    emit('join-call', content);
}

function insertEmoji(emoji: string) {
    const el = inputRef.value;
    if (!el) return;

    const start = el.selectionStart ?? el.value.length;
    const end = el.selectionEnd ?? el.value.length;

    el.value =
        el.value.slice(0, start) +
        emoji +
        el.value.slice(end);

    const pos = start + emoji.length;
    el.setSelectionRange(pos, pos);
    el.focus();
}

function addTicket(ticket: any) {
    attachments.value.push({
        id: crypto.randomUUID(),
        kind: 'ticket',
        ticket
    });
}
function sendGif(url: string) {
    emit('send-message', {
        type: 'gif',
        url
    });
}

defineExpose({ insertEmoji, sendGif, addTicket });

const openTicket = (ticket: any) => {
    selectedTicket.value = ticket;
    modalVisible.value = true;
};

function closeModal() {
    modalVisible.value = false;
    selectedTicket.value = null;
}

// Auto-scroll to bottom when new messages are added
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Watch for changes in messages and auto-scroll
watch(() => props.messages, (newMessages, oldMessages) => {
    // Only scroll if new messages were added (not just initial load)
    if (oldMessages && newMessages.length > oldMessages.length) {
        scrollToBottom();
    }
}, { deep: true });

// Initial scroll when component mounts
nextTick(() => {
    scrollToBottom();
});
</script>

<template>
    <div class="chat">
        <div class="msgs" ref="messagesContainer">
            <div v-for="(msg, i) in messages" :key="i" class="bubbleRow" :class="{ me: msg.me }">
                <div class="bubble">
                    <!-- Text & Signals -->
                    <template v-if="msg.type === 'text'">
                        <p v-if="!isCallSignal(msg.content)" class="text">{{ msg.content }}</p>
                        <p v-else class="italic text-gray-600 text">
                            {{ renderSignalText(msg.content) }}
                            <button v-if="canJoinFromSignal(msg.content)"
                                @click="joinCall(msg.content)"
                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded bg-linkup text-black text-xs">
                                Join Call
                            </button>
                        </p>
                    </template>

                    <!-- Media attachments -->
                    <div v-if="msg.type === 'gif' || msg.type === 'image' || msg.type === 'pdf'" class="media">
                        <div class="attCard">
                            <div class="attTop">
                                <div class="attTitle">
                                    <span v-if="msg.type === 'gif'">GIF</span>
                                    <span v-else-if="msg.type === 'image'">Photo</span>
                                    <span v-else>PDF</span>
                                </div>
                                <div class="attMeta">
                                    <span v-if="msg.type !== 'gif'">{{ msg.name || '' }}</span>
                                </div>
                            </div>

                            <template v-if="msg.type === 'gif'">
                                <iframe :src="msg.url" class="gifFrame" frameborder="0" allowfullscreen></iframe>
                            </template>
                            <template v-else-if="msg.type === 'image'">
                                <img :src="msg.url" :alt="msg.name || 'image'" class="thumbImg" />
                            </template>
                            <template v-else>
                                <div class="fileRow">
                                    <div class="fileName">{{ msg.name || 'Document' }}</div>
                                    <a :href="msg.url" target="_blank" rel="noopener" class="btn btnGhost">Open</a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Ticket card -->
                    <template v-else-if="msg.type === 'ticket'">
                        <div class="ticket">
                            <div class="ticketTop">
                                <div>
                                    <h3 class="ticketTitle">🎟️ {{ msg.ticket.event }}</h3>
                                    <div class="ticketMeta">{{ msg.ticket.dateLabel }} • {{ msg.ticket.venue }} • {{ msg.ticket.type }}</div>
                                </div>
                                <div class="tag">Ticket</div>
                            </div>
                            <div class="qrRow">
                                <div class="qrBig">
                                    <qrcode-vue :value="JSON.stringify({ kind: 'linkup_ticket_token', token: msg.ticket._signed_token })" :size="160" level="M" />
                                </div>
                                <div class="ticketInfo">
                                    <div class="idLine">🎟️ Ticket ID: <span class="mono">{{ msg.ticket.ticket_id }}</span></div>
                                    <div class="subLine">📍 {{ msg.ticket.city }} • 🗓️ {{ msg.ticket.dateLabel }}</div>
                                    <div class="subLine">✅ QR is the key — scan to validate entry.</div>
                                    <div class="subLine">🔐 QR payload = signed token.</div>
                                </div>
                            </div>
                            <div class="ticketBtns">
                                <button class="btn btnPrimary" @click="openTicket(msg.ticket)">Open Ticket</button>
                            </div>
                        </div>
                    </template>

                    <div class="time">{{ msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '' }}</div>
                </div>
            </div>
        </div>

        <div class="composer">
            <div id="tray" class="tray" :class="{ show: attachments.length > 0 }">
                <div class="trayHead">
                    <div class="trayTitle">Attachments ready</div>
                    <button class="btn btnDanger" @click="attachments = []">Clear</button>
                </div>
                <div id="trayList" class="trayList">
                    <div v-for="att in attachments" :key="att.id" class="trayItem">
                        <div class="trayLeft">
                            <div class="miniPreview">
                                <template v-if="att.kind === 'photo'">
                                    <img :src="att.previewUrl" />
                                </template>
                                <template v-else-if="att.kind === 'ticket'">
                                    <div class="ticketIcon">🎟️</div>
                                </template>
                                <template v-else>
                                    <div class="pdfIcon">📄</div>
                                </template>
                            </div>
                            <div class="trayMeta">
                                <template v-if="att.kind === 'ticket'">
                                    <div class="name">{{ att.ticket.event }}</div>
                                    <div class="size">Ticket</div>
                                </template>
                                <template v-else>
                                    <div class="name">{{ att.name }}</div>
                                    <div class="size">{{ att.sizeKB }} KB</div>
                                </template>
                            </div>
                        </div>
                        <button class="removeBtn" @click="removeAttachment(att.id)">×</button>
                    </div>
                </div>
            </div>

            <div class="tools">
                <div class="toolLeft">
                    <button class="iconBtn" @click="emit('toggle-emoji')">😊 Emojis</button>
                    <button class="iconBtn" @click="emit('toggle-gif')">🎞️ GIF</button>
                    <button class="iconBtn" @click="openPhotoPicker">🖼️ Photo</button>
                    <button class="iconBtn" @click="openPdfPicker">📎 PDF</button>
                    <button class="iconBtn" @click="emit('toggle-ticket')">🎟️ Ticket</button>
                </div>
                <div class="pill">Pick → preview → send</div>
            </div>

            <div class="row2">
                <textarea ref="inputRef" placeholder="Type a message… add emojis and send attachments"
                    spellcheck="true" @keydown.enter.exact.prevent="sendMessage"></textarea>
                <button class="sendBtn" id="sendBtn" @click="sendMessage">Send</button>
            </div>

            <label class="sr" for="photoInput">photo</label>
            <input ref="photoInputRef" type="file" accept="image/*" multiple hidden @change="onPhotosSelected" />
            <label class="sr" for="pdfInput">pdf</label>
            <input ref="pdfInputRef" type="file" accept="application/pdf" multiple hidden @change="onPdfsSelected" />

        </div>

        <!-- Ticket Details Modal -->
        <div v-if="modalVisible" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm" @click.self="closeModal">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <!-- Header with gradient -->
                    <div class="bg-gradient-to-r from-brand to-brand-strong text-black/50 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold">Ticket Details</h3>
                                <p class="text-brand-light mt-1">Your event admission information</p>
                            </div>
                            <button type="button" @click="closeModal" class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 transition-colors flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal body -->
                    <div v-if="selectedTicket" class="p-6">
                        <div class="grid md:grid-cols-2 gap-8">
                            <!-- Left Column: Event & Ticket Details -->
                            <div class="space-y-6">
                                <!-- Event Details Card -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Event Information
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Title:</span>
                                            <span class="font-medium text-gray-900 flex-1">{{ selectedTicket.event || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">City:</span>
                                            <span class="font-medium text-gray-900 flex-1">{{ selectedTicket.city || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Date:</span>
                                            <span class="font-medium text-gray-900 flex-1">{{ selectedTicket.dateLabel || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Venue:</span>
                                            <span class="font-medium text-gray-900 flex-1">{{ selectedTicket.venue || 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Ticket Details Card -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                        </svg>
                                        Ticket Information
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Name:</span>
                                            <span class="font-medium text-gray-900 flex-1">{{ selectedTicket.ticket_name || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Type:</span>
                                            <span class="font-medium text-gray-900 flex-1">{{ selectedTicket.type || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Ticket ID:</span>
                                            <span class="font-mono text-sm bg-gray-200 px-2 py-1 rounded flex-1">{{ selectedTicket.ticket_id || 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-start">
                                            <span class="text-gray-500 w-20">Price:</span>
                                            <div class="flex-1">
                                                <span v-if="selectedTicket.is_free === 'yes'" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Free
                                                </span>
                                                <span v-else-if="selectedTicket.ticket_price > 'USD 0.00'" class="text-xl font-bold text-gray-900">${{ selectedTicket.ticket_price || '0.00' }}</span>
                                                <span v-else class="text-xl font-bold text-gray-900">Free</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: QR Code -->
                            <div class="flex flex-col items-center justify-center">
                                <div v-if="selectedTicket._signed_token" class="bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-100">
                                    <div class="text-center mb-4">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Scan for Entry</h4>
                                        <p class="text-sm text-gray-600">Present this QR code at the venue</p>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl">
                                        <qrcode-vue :value="JSON.stringify({ kind: 'linkup_ticket_token', token: selectedTicket._signed_token })" :size="200" level="M" />
                                    </div>
                                    <div class="mt-4 text-center">
                                        <p class="text-xs text-gray-500">Valid QR token for secure verification</p>
                                    </div>
                                </div>
                                <div v-else class="bg-gray-100 rounded-2xl p-8 text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    <p class="text-gray-600">QR code not available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Keep this ticket secure and do not share
                            </p>
                            <button @click="closeModal" type="button" class="px-6 py-2.5 bg-blue text-black font-medium rounded-lg hover:bg-brand/90 transition-colors focus:outline-none focus:ring-2 focus:ring-brand/50 focus:ring-offset-2">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

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
    padding: 1rem;    
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
    max-height: calc(100vh - 200px);
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