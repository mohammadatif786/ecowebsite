<template>
    <div v-if="isOpen"
        class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 animate-in fade-in duration-300"
        @click.self="close">
        <div class="bg-white rounded-[2rem] max-w-lg w-full h-[85vh] max-h-[620px] shadow-2xl overflow-hidden flex flex-col border border-slate-100">
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="backToMessages" class="p-1 text-slate-400 hover:text-slate-600 rounded-full transition sm:hidden">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </button>

                    <div class="relative shrink-0">
                        <img v-if="targetUser?.avatar && !targetUser.avatar.includes('pravatar')"
                            :src="targetUser.avatar"
                            @error="$event.target.style.display='none'"
                            class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm" />
                        <div v-else
                            class="w-10 h-10 rounded-full bg-[#2c3848] text-white font-black text-xs grid place-items-center">
                            @{{ getInitial(targetUser) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="font-black text-sm text-slate-900 truncate">
                            @{{ targetUser?.handle || targetUser?.name }}
                        </h3>
                        <p class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Vibes Live Chat
                        </p>
                    </div>
                </div>

                <button @click="close"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Messages Body -->
            <div ref="chatContainerRef" class="flex-1 p-4 overflow-y-auto hide-scroll space-y-3 bg-slate-50/50">
                <div v-if="loading" class="py-12 text-center text-slate-400 text-xs font-bold flex items-center justify-center gap-2">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    Loading conversation...
                </div>

                <template v-else-if="messages.length">
                    <div v-for="msg in messages" :key="msg.id || msg.temp_id"
                        :class="[
                            'flex flex-col max-w-[80%]',
                            isOwnMessage(msg) ? 'ml-auto items-end' : 'mr-auto items-start'
                        ]">
                        <div :class="[
                            'px-4 py-2.5 rounded-2xl text-xs font-semibold leading-relaxed shadow-xs',
                            isOwnMessage(msg)
                                ? 'bg-blue-100 border border-blue-200 text-slate-900 rounded-tr-xs'
                                : 'bg-white border border-slate-200/80 text-slate-900 rounded-tl-xs'
                        ]">
                            <p v-if="msg.content">{{ msg.content }}</p>
                            <p v-else-if="msg.type === 'gif'" class="italic text-[11px] opacity-80">GIF Message</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 mt-1 px-1">
                            {{ formatTime(msg.created_at) }}
                        </span>
                    </div>
                </template>

                <div v-else class="py-16 text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-lkblue grid place-items-center mx-auto mb-3">
                        <i data-lucide="message-square" class="w-6 h-6"></i>
                    </div>
                    <p class="font-black text-sm text-slate-800">Start a conversation</p>
                    <p class="text-xs font-medium text-slate-400 mt-1">Send a message to @{{ targetUser?.handle || targetUser?.name }}</p>
                </div>
            </div>

            <!-- Message Input Area -->
            <div class="p-3 bg-white border-t border-slate-100 shrink-0">
                <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                    <input ref="inputRef"
                        v-model="newMessageText"
                        type="text"
                        placeholder="Type a message..."
                        :disabled="sending"
                        class="flex-1 bg-slate-100 focus:bg-white border border-transparent focus:border-lkblue/30 rounded-2xl px-4 py-2.5 text-xs font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 disabled:opacity-50" />

                    <button type="submit"
                        :disabled="!newMessageText.trim() || sending"
                        class="w-10 h-10 rounded-2xl bg-lkblue text-white flex items-center justify-center transition active:scale-95 disabled:opacity-40 disabled:scale-100 shrink-0 shadow-md shadow-blue-500/20">
                        <i v-if="sending" data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <i v-else data-lucide="send-horizontal" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const emit = defineEmits(['back']);

const page = usePage();
const currentUser = computed(() => page.props.auth?.user || {});

const isOpen = ref(false);
const loading = ref(false);
const sending = ref(false);
const targetUser = ref(null);
const messages = ref([]);
const newMessageText = ref('');

const chatContainerRef = ref(null);
const inputRef = ref(null);
let pollInterval = null;

const open = (user) => {
    targetUser.value = user;
    isOpen.value = true;
    messages.value = [];
    newMessageText.value = '';

    fetchMessages();
    setupRealtime();

    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

const close = () => {
    isOpen.value = false;
    stopRealtime();
};

const backToMessages = () => {
    close();
    emit('back');
};

const getInitial = (user) => {
    const name = user?.handle || user?.name || 'U';
    return name.charAt(0).toLowerCase();
};

const isOwnMessage = (msg) => {
    return String(msg.from_user_id) === String(currentUser.value.id);
};

const formatTime = (timeStr) => {
    if (!timeStr) return 'Just now';
    const date = new Date(timeStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainerRef.value) {
            chatContainerRef.value.scrollTop = chatContainerRef.value.scrollHeight;
        }
    });
};

const fetchMessages = async () => {
    if (!targetUser.value) return;
    loading.value = true;
    try {
        const recipientId = targetUser.value.user_id || targetUser.value.id;
        const response = await axios.get(route('new_frontend.dating.chats.messages', { recipient: recipientId }));
        messages.value = response.data.messages || [];
    } catch (error) {
        console.error('Failed to load chat messages:', error);
    } finally {
        loading.value = false;
        nextTick(() => {
            scrollToBottom();
            setTimeout(scrollToBottom, 50);
        });
    }
};

const pollMessagesSilently = async () => {
    if (!targetUser.value || !isOpen.value) return;
    try {
        const recipientId = targetUser.value.user_id || targetUser.value.id;
        const response = await axios.get(route('new_frontend.dating.chats.messages', { recipient: recipientId }));
        const fetched = response.data.messages || [];

        if (fetched.length !== messages.value.length) {
            messages.value = fetched;
            scrollToBottom();
        }
    } catch (error) {
        // Silent catch for background polling
    }
};

const sendMessage = async () => {
    const content = newMessageText.value.trim();
    if (!content || sending.value || !targetUser.value) return;

    sending.value = true;
    const recipientId = targetUser.value.user_id || targetUser.value.id;

    // Optimistic local add
    const tempMsg = {
        temp_id: Date.now(),
        from_user_id: currentUser.value.id,
        to_user_id: recipientId,
        content: content,
        type: 'text',
        created_at: new Date().toISOString()
    };
    messages.value.push(tempMsg);
    newMessageText.value = '';
    scrollToBottom();

    try {
        const response = await axios.post(route('new_frontend.dating.chats.messages.store', { recipient: recipientId }), {
            content: content
        });

        if (response.data.success && response.data.messages) {
            // Replace optimistic message with actual DB messages
            messages.value = messages.value.filter(m => !m.temp_id);
            response.data.messages.forEach(m => {
                if (!messages.value.some(existing => existing.id === m.id)) {
                    messages.value.push(m);
                }
            });
            scrollToBottom();
        }
    } catch (error) {
        console.error('Failed to send message:', error);
        if (window.toast) window.toast('Failed to send message');
        messages.value = messages.value.filter(m => m.temp_id !== tempMsg.temp_id);
    } finally {
        sending.value = false;
        nextTick(() => {
            if (inputRef.value) inputRef.value.focus();
        });
    }
};

const setupRealtime = () => {
    stopRealtime();

    // Start 3s polling for guaranteed real-time background sync
    pollInterval = setInterval(pollMessagesSilently, 3000);

    // Also register Echo event listener if available
    if (window.Echo && currentUser.value?.id) {
        const myId = currentUser.value.id;
        window.Echo.private(`App.Models.User.${myId}`)
            .listen('.App\\Events\\MessageSent', handleEchoMessage)
            .listen('MessageSent', handleEchoMessage);
    }
};

const handleEchoMessage = (e) => {
    const msg = e.message || e;
    if (!msg || !targetUser.value) return;

    const recipientId = targetUser.value.user_id || targetUser.value.id;
    if (String(msg.from_user_id) === String(recipientId) || String(msg.to_user_id) === String(recipientId)) {
        if (!messages.value.some(existing => existing.id === msg.id)) {
            messages.value.push(msg);
            scrollToBottom();
        }
    }
};

const stopRealtime = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }

    if (window.Echo && currentUser.value?.id) {
        const myId = currentUser.value.id;
        window.Echo.private(`App.Models.User.${myId}`)
            .stopListening('.App\\Events\\MessageSent')
            .stopListening('MessageSent');
    }
};

onUnmounted(() => {
    stopRealtime();
});

defineExpose({ open, close });
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
