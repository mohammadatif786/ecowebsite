<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { MessagesSquare, X, Send, Loader2 } from 'lucide-vue-next';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { useEcho } from '@laravel/echo-vue';

const props = defineProps<{
    show: boolean;
    attendee: any | null;
}>();

const emit = defineEmits(['close']);

const page = usePage<any>();
const authUser = computed(() => page.props?.auth?.user);

const message = ref('');
const selectedTemplate = ref('');
const messages = ref<any[]>([]);
const isLoading = ref(false);
const threadContainer = ref<HTMLElement | null>(null);

const ticketId = computed(() => props.attendee?.ticket_qrcode_id || props.attendee?.id || '—');
const orderId = computed(() => props.attendee?.stripe_id?.substring(0, 10) || props.attendee?.payment_id || '—');
const eventName = computed(() => props.attendee?.event?.title || 'Event');
const attendeeName = computed(() => props.attendee?.user?.name || props.attendee?.user_name || 'Attendee');
const receiverId = computed(() => props.attendee?.user?.id || props.attendee?.user_id);

const coerceToArray = (v: any) => Array.isArray(v) ? v : (v && typeof v === 'object' ? Object.values(v) : []);

const normalizeDate = (v: any) => {
    if (!v) return new Date().toISOString();
    if (typeof v === 'string') {
        // Echo often sends "YYYY-MM-DD HH:mm:ss" which is not reliably parseable in all browsers.
        if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(v)) {
            return v.replace(' ', 'T');
        }
    }
    return v;
};

const messageSortKey = (m: any) => {
    const idNum = Number(m?.id);
    if (Number.isFinite(idNum)) return idNum;
    const t = new Date(normalizeDate(m?.created_at || 0)).getTime();
    return Number.isFinite(t) ? t : 0;
};

const toInboxMsg = (m: any) => {
    const me = String(m?.from_user_id) === String(authUser.value?.id);
    const created_at = normalizeDate(m?.created_at || new Date().toISOString());
    const id = m?.id || (m?.created_at ? new Date(created_at).getTime() : Math.random());
    if (m?.type === 'gif' && m?.meta?.url) return { id, type: 'gif', url: m.meta.url, me, created_at };
    if (m?.type === 'image' && m?.meta?.url) return { id, type: 'image', url: m.meta.url, name: m.meta.name, me, created_at };
    if (m?.type === 'pdf' && m?.meta?.url) return { id, type: 'pdf', url: m.meta.url, name: m.meta.name, me, created_at };
    if (m?.type === 'ticket' && m?.meta) {
        return {
            id,
            type: 'ticket',
            ticket: {
                ticket_id: m.meta.ticket_id,
                event: m.meta.event,
                dateLabel: m.meta.date_label,
                venue: m.meta.venue,
                city: m.meta.city,
                type: m.meta.ticket_type,
            },
            me,
            created_at
        };
    }
    return {
        id,
        type: m?.type || 'text',
        content: m?.content,
        me,
        created_at,
        meta: m?.meta
    };
};

// Real-time updates
useEcho(`user`, "MessageSent", (e: any) => {
    if (!e || !props.show) return;

    console.log('[MessageModal] Echo event received:', e);

    const isFromThem = String(e.from_user_id) === String(receiverId.value);
    const isFromMe = String(e.from_user_id) === String(authUser.value?.id) && String(e.to_user_id) === String(receiverId.value);

    // Check if the message belongs to this conversation
    if (isFromThem || isFromMe) {
        const incoming = toInboxMsg(e);

        // Avoid duplicate for optimistic messages
        if (isFromMe) {
            const exists = messages.value.some(m => m.content === e.content && (new Date().getTime() - new Date(normalizeDate(m.created_at)).getTime() < 5000));
            if (exists) return;
        }

        // Assign a new array so Vue always re-renders the v-for correctly
        const next = [...messages.value.filter(m => String(m?.id) !== String(incoming?.id)), incoming]
            .sort((a, b) => messageSortKey(a) - messageSortKey(b));

        messages.value = next;
        console.log('[MessageModal] Realtime merged. Count:', messages.value.length, 'Last:', messages.value[messages.value.length - 1]);
        nextTick(() => scrollToBottom());
    }
});

// Fetch messages when modal opens AND receiver is known
watch(
    [() => props.show, () => receiverId.value],
    ([isOpen, rid], [wasOpen, prevRid]) => {
        if (!isOpen) return;

        // If switching attendees while modal is open, reset local state first
        if (String(prevRid || '') !== String(rid || '')) {
            messages.value = [];
            message.value = '';
            selectedTemplate.value = '';
        }

        if (rid) {
            fetchMessages();
        }
    },
    { immediate: true }
);

async function fetchMessages() {
    if (!receiverId.value) {
        console.warn('[MessageModal] No receiverId found. Attendee object:', props.attendee);
        return;
    }

    console.log('[MessageModal] Fetching messages. Me (authUser):', authUser.value?.id, 'Them (receiver):', receiverId.value);
    isLoading.value = true;
    try {
        // Correcting route names to include 'frontend.' prefix per web.php
        let url = `/chat/${receiverId.value}`;
        try {
            if (typeof route !== 'undefined') {
                url = route('frontend.chat.index', { toUserId: receiverId.value });
            }
        } catch (e) {
            console.warn('[MessageModal] Ziggy route frontend.chat.index not found, using fallback path');
        }

        const version = page.version;
        const response = await axios.get(url, {
            headers: {
                'X-Inertia': true,
                'X-Requested-With': 'XMLHttpRequest',
                ...(version ? { 'X-Inertia-Version': version } : {})
            }
        });

        console.log('[MessageModal] Raw response:', response.data);

        if (typeof response.data === 'string' && response.data.includes('<!DOCTYPE html>')) {
            console.error('[MessageModal] Received HTML instead of JSON. Session might be expired.');
            return;
        }

        let msgs = [];
        const data = response.data;
        if (data?.props?.chatmessages) {
            msgs = data.props.chatmessages;
        } else if (data?.chatmessages) {
            msgs = data.chatmessages;
        }

        console.log('[MessageModal] Raw msgs from backend:', msgs.length);

        const decodedMsgs = coerceToArray(msgs).map(toInboxMsg);

        // Remove nullish and ensure we have at least an id to render
        messages.value = decodedMsgs
            .filter(m => m && (m.id || m.content || m.type))
            .sort((a, b) => messageSortKey(a) - messageSortKey(b));

        console.log('[MessageModal] Final messages in state:', messages.value.length);

        // Mark as read if there are messages from them
        const hasUnreadFromThem = msgs.some((m: any) => String(m.from_user_id) === String(receiverId.value) && !m.is_read);
        if (hasUnreadFromThem) {
            axios.post(`/chat/read/${receiverId.value}`).catch(() => { });
        }

        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('[MessageModal] Error fetching messages:', error);
    } finally {
        isLoading.value = false;
    }
}

function applyTemplate() {
    if (!selectedTemplate.value) return;
    let text = selectedTemplate.value;
    text = text.replace('{name}', attendeeName.value);
    text = text.replace('{event}', eventName.value);
    text = text.replace('{ticket}', String(ticketId.value));
    message.value = text;
}

function sendMessage() {
    if (!message.value.trim() || !receiverId.value) return;

    const text = message.value;
    const form = new FormData();
    form.append('content', text);
    form.append('replied_to', '');

    // Optimistic update
    const tempId = Date.now();
    messages.value.push({
        id: tempId,
        type: 'text',
        content: text,
        me: true,
        created_at: new Date().toISOString()
    });

    message.value = '';
    scrollToBottom();

    let url = `/chat/${receiverId.value}/messages`;
    try {
        if (typeof route !== 'undefined') {
            url = route('frontend.messages.store', { toUserId: receiverId.value });
        }
    } catch (e) {
        console.warn('[MessageModal] Ziggy route frontend.messages.store not found, using fallback path');
    }

    axios.post(url, form, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then((res) => {
            console.log('[MessageModal] Send success:', res.data);
        })
        .catch((err) => {
            console.error('[MessageModal] Send error:', err);
            // Remove the optimistic message on error
            messages.value = messages.value.filter(m => m.id !== tempId);
            message.value = text;
        });
}

function scrollToBottom() {
    if (threadContainer.value) {
        threadContainer.value.scrollTop = threadContainer.value.scrollHeight;
    }
}

function formatTime(dateStr: string) {
    if (!dateStr) return '';
    try {
        const d = new Date(normalizeDate(dateStr));
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch {
        return '';
    }
}

function gotoImage(url: string) {
    if (url) window.open(url, '_blank');
}
</script>

<template>
    <div v-show="show" class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="emit('close')"></div>

        <div class="absolute inset-0 flex items-center justify-center p-3 pointer-events-none">
            <div
                class="w-full max-w-3xl rounded-3xl bg-white shadow-2xl border overflow-hidden pointer-events-auto transform transition-all animate-in fade-in zoom-in duration-200">
                <div class="p-4 border-b flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-lg font-semibold flex items-center gap-2 text-slate-900">
                            <MessagesSquare class="w-5 h-5 text-indigo-600" />
                            Direct Message
                        </div>
                        <div class="text-sm text-slate-500 truncate">
                            Conversation with {{ attendeeName }}
                        </div>
                    </div>
                    <button @click="emit('close')"
                        class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors"
                        aria-label="Close">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-4">
                    <div class="rounded-2xl border p-3 bg-slate-50">
                        <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Pinned context
                        </div>
                        <div class="mt-1 text-sm text-slate-700">
                            <span class="font-semibold">{{ eventName }}</span>
                            <span class="text-slate-300 mx-2">•</span>
                            Ticket <span class="mono font-semibold text-indigo-600">{{ ticketId }}</span>
                            <span class="text-slate-300 mx-2">•</span>
                            Order <span class="mono font-semibold">{{ orderId }}</span>
                        </div>
                        <div v-if="true" class="mt-2 text-[9px] text-slate-400 font-mono border-t pt-1 flex gap-3">
                            <span>Me: {{ authUser?.id }}</span>
                            <span>Them: {{ receiverId }}</span>
                            <span>Loaded: {{ messages.length }}</span>
                        </div>
                    </div>

                    <div ref="threadContainer"
                        class="mt-3 h-80 overflow-auto rounded-2xl border p-4 bg-slate-50/30 flex flex-col gap-3 scroll-smooth">

                        <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                            <Loader2 class="w-6 h-6 text-slate-400 animate-spin" />
                        </div>

                        <template v-else-if="messages.length > 0">
                            <div v-for="msg in messages" :key="msg.id" class="flex flex-col"
                                :class="msg.me ? 'items-end' : 'items-start'">
                                <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm" :class="msg.me
                                    ? 'bg-indigo-600 text-white rounded-tr-none'
                                    : 'bg-white border text-slate-700 rounded-tl-none'">

                                    <template v-if="msg.type === 'gif'">
                                        <img :src="msg.url || msg.meta?.url" class="rounded-lg max-w-full" />
                                    </template>
                                    <template v-else-if="msg.type === 'image'">
                                        <img :src="msg.url || msg.meta?.url"
                                            class="rounded-lg max-w-full cursor-pointer"
                                            @click="gotoImage(msg.url || msg.meta?.url)" />
                                        <div v-if="msg.name" class="text-[10px] opacity-70 mt-1">{{ msg.name }}</div>
                                    </template>
                                    <template v-else-if="msg.type === 'pdf'">
                                        <a :href="msg.url || msg.meta?.url" target="_blank"
                                            class="flex items-center gap-2 p-1 hover:underline">
                                            <div class="w-8 h-8 rounded bg-red-50 flex items-center justify-center">
                                                <span class="text-[10px] font-bold text-red-600">PDF</span>
                                            </div>
                                            <span class="text-xs truncate max-w-[150px]">{{ msg.name || 'document.pdf'
                                                }}</span>
                                        </a>
                                    </template>
                                    <template v-else-if="msg.type === 'ticket'">
                                        <div class="p-2 border rounded-xl bg-slate-50 text-slate-900">
                                            <div class="text-[10px] font-bold text-indigo-600 uppercase mb-1">Shared
                                                Ticket</div>
                                            <div class="font-semibold">{{ msg.ticket?.event || 'Event' }}</div>
                                            <div class="text-[10px] text-slate-500">{{ msg.ticket?.ticket_name }} • {{
                                                msg.ticket?.ticket_id }}</div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        {{ msg.content }}
                                    </template>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 px-1">
                                    {{ formatTime(msg.created_at) }}
                                </span>
                            </div>
                        </template>

                        <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-white border shadow-sm grid place-items-center mb-3">
                                <MessagesSquare class="w-6 h-6 text-slate-300" />
                            </div>
                            <div class="text-sm font-medium text-slate-900">No messages yet</div>
                            <div class="text-xs text-slate-500 mt-1">Send a direct message to {{ attendeeName }} to
                                start the conversation.
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-2">
                        <div class="md:col-span-8">
                            <input v-model="message"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all text-sm"
                                placeholder="Type a message…" @keyup.enter="sendMessage" />
                        </div>
                        <div class="md:col-span-4 flex gap-2">
                            <select v-model="selectedTemplate" @change="applyTemplate"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">
                                <option value="">Templates</option>
                                <option value="Hi {name}, quick update for {event}: …">Quick update</option>
                                <option value="Hi {name}, the venue for {event} has changed.">Venue change</option>
                                <option value="Hi {name}, here are your ticket details for {event}: #{ticket}.">Ticket
                                    details</option>
                            </select>
                            <button @click="sendMessage" :disabled="!message.trim()"
                                class="px-3 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-40 flex items-center gap-2 transition-all font-semibold text-sm">
                                <Send class="w-4 h-4" /> Send
                            </button>
                        </div>
                    </div>

                    <div
                        class="mt-4 flex items-center gap-2 text-[11px] text-slate-500 bg-indigo-50/50 p-2.5 rounded-xl border border-indigo-100 italic">
                        <span class="flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        Messages are delivered instantly to the attendee's LinkUp app via push notification.
                    </div>
                </div>

                <div class="p-4 border-t bg-slate-50/50 flex justify-end gap-2">
                    <button
                        class="px-6 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-600 transition-colors"
                        @click="emit('close')">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shadow-soft {
    box-shadow: 0 18px 40px rgba(15, 23, 42, .10);
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* Custom Scrollbar for Chat Thread */
.scroll-smooth::-webkit-scrollbar {
    width: 6px;
}

.scroll-smooth::-webkit-scrollbar-track {
    background: transparent;
}

.scroll-smooth::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.scroll-smooth::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
