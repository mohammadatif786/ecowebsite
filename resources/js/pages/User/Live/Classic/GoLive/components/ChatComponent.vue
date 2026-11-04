<template>
    <div class="chat-container flex h-full flex-col overflow-hidden p-3">
        <!-- Chat Messages -->
        <div ref="chatContainer" class="chat-messages min-h-0 flex-1 space-y-2 overflow-y-auto pr-1 hide-scroll">
            <div v-if="chatMessages.length === 0" class="text-center text-gray-400 py-8">
                <div class="text-2xl mb-2">💬</div>
                <p class="text-sm">No messages yet. Start the conversation!</p>
            </div>

            <div v-for="message in chatMessages" :key="message.id"
                class="chat-message bg-black/30 p-2 rounded-lg text-sm">
                <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white text-xs">{{ message.from }}</span>
                            <span v-if="message.isHost" class="bg-amber-500 text-black text-[9px] px-1 rounded font-black uppercase">Host</span>
                            <span class="text-gray-400 text-xs">{{ formatChatTime(message.timestamp) }}</span>
                        </div>
                        <p class="text-gray-200 text-sm break-words">{{ message.text }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div v-if="streamId" class="chat-input mt-auto pt-3">
            <div class="flex gap-2">
                <input
                    v-model="chatInput"
                    @keydown.enter="sendChatMessage"
                    :disabled="chatCooldown"
                    placeholder="Type a message..."
                    class="flex-1 bg-white/5 border border-white/10 text-white px-3 py-2.5 rounded-xl outline-none placeholder:text-white/30 text-sm focus:border-blue-500/50 transition-colors disabled:opacity-50"
                    maxlength="200"
                >
                <button
                    @click="sendChatMessage"
                    :disabled="!chatInput.trim() || chatCooldown"
                    class="bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-black hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95"
                >
                    Send
                </button>
            </div>
            <div class="text-[10px] font-bold text-white/30 mt-1.5 text-right tracking-wide">
                {{ chatInput.length }}/200 characters
            </div>
        </div>
    </div>
</template>

<style scoped>
.hide-scroll::-webkit-scrollbar {
    display: none;
}
.hide-scroll {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { toast } from 'vue-sonner'
import axios from 'axios'

interface ChatMessage {
    id: string
    from: string
    text: string
    timestamp: string | number
    isHost?: boolean
}

const props = defineProps<{
    streamId: string | number | null
    isHost: boolean
    hostId?: string | number | null
    sessionEvent?: { name: string; payload: any } | null
}>()

const chatMessages = ref<ChatMessage[]>([])
const chatInput = ref('')
const chatCooldown = ref(false)
const chatContainer = ref<HTMLElement | null>(null)

const sendChatMessage = async () => {
    const text = chatInput.value.trim()

    if (!props.streamId) {
        toast.error("Stream is not available.")
        return
    }

    if (!text) {
        toast.error("Please enter a message.")
        return
    }

    if (chatCooldown.value) {
        toast.warning("Wait 5 seconds before sending another message.")
        return
    }

    const payloadText = text
        .replace(":)", "😊")
        .replace(":heart", "❤️")
        .replace(":fire", "🔥")

    try {
        const response = await axios.post(
            route('frontend.live.comment', { stream: props.streamId }),
            { text: payloadText }
        )

        if (response.data?.ok || response.data?.success) {
            const ownMessage: ChatMessage = {
                id: String(response.data?.id || 'temp_' + Date.now()),
                from: response.data?.user_name || 'You',
                text: payloadText,
                timestamp: response.data?.timestamp || new Date().toISOString(),
                isHost: props.isHost
            }
            // Own message is added via Echo event if possible, but let's keep local add for instant feedback
            const existingIndex = chatMessages.value.findIndex(msg => String(msg.id) === String(ownMessage.id))
            if (existingIndex === -1) {
                chatMessages.value.push(ownMessage)

                if (chatMessages.value.length > 50) {
                    chatMessages.value = chatMessages.value.slice(-50)
                }

                scrollToBottom()
            }

            chatInput.value = ""
            chatCooldown.value = true
            // toast.success('💬 Message sent!')

            setTimeout(() => {
                chatCooldown.value = false
            }, 2000)
        } else {
            toast.error('Failed to send message')
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to send comment')
    }
}

const formatChatTime = (timestamp: string | number) => {
    const date = new Date(timestamp)
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight
        }
    })
}

const fetchChatMessages = async () => {
    if (!props.streamId) {
        chatMessages.value = []
        return
    }
}

const subscribeToChatEvents = () => {
    // The page parent owns the sole private Echo subscription and forwards events.
    return


    if (!props.streamId) {
        return
    }

    if (!window.Echo) {
        return
    }

    const channel = 'live.' + String(props.streamId)
    const echoChannel = window.Echo.channel(channel)

    const handleCommentEvent = (event: any) => {
        if (event.stream_id && String(event.stream_id) !== String(props.streamId)) {
            return
        }

        const msgText = event.text || event.payload?.text;
        const msgId = event.id || event.payload?.id;
        if (!msgId || !msgText) {
            return
        }

        const existingIndex = chatMessages.value.findIndex(msg => String(msg.id) === String(msgId))

        if (existingIndex === -1) {
            const userId = event.user?.id || event.from_id || event.payload?.user?.id;
            const isHostMsg = userId && props.hostId && String(userId) === String(props.hostId);

            const newMessage: ChatMessage = {
                id: String(msgId),
                from: event.user?.name || event.from || event.payload?.user?.name || 'User',
                text: msgText,
                timestamp: event.ts || event.timestamp || event.payload?.ts || new Date().toISOString(),
                isHost: isHostMsg
            }

            chatMessages.value.push(newMessage)

            if (chatMessages.value.length > 50) {
                chatMessages.value = chatMessages.value.slice(-50)
            }

            scrollToBottom()
        } else {
            const existingMessage = chatMessages.value[existingIndex]
            if (event.user?.name && existingMessage.from === 'You') {
                existingMessage.from = event.user.name
            }
            if (event.ts && !existingMessage.timestamp) {
                existingMessage.timestamp = event.ts
            }
        }
    }

    echoChannel
        .listen('.LiveCommentPosted', (event: any) => {
            handleCommentEvent(event)
        })
        .error((error: any) => {
            console.error('❌ ChatComponent: Channel error:', error)
        })
        .subscribed(() => {
            console.log('✅ ChatComponent: Successfully subscribed to channel:', channel)
        })
        .listen('.StreamEnded', (e: any) => {
            const eventStreamId = String(e.id || e.stream_id || '')

            if (props.streamId && String(props.streamId) === eventStreamId) {
                chatMessages.value = []
                chatInput.value = ''
                chatCooldown.value = false
            }
        })
}

watch(() => props.streamId, async (newStreamId, oldStreamId) => {

    if (oldStreamId !== undefined && oldStreamId !== null) {
        chatMessages.value = []
        chatInput.value = ''
        chatCooldown.value = false
    }

    if (newStreamId) {
        await fetchChatMessages()
        setTimeout(() => {
            subscribeToChatEvents()
        }, 300)
    } else if (oldStreamId !== undefined) {
        chatMessages.value = []
        chatInput.value = ''
        chatCooldown.value = false
    }
}, { immediate: true })

watch(() => props.sessionEvent, (event) => {
    if (!event) return
    if (event.name === 'StreamEnded') {
        chatMessages.value = []
        return
    }
    if (event.name !== 'LiveCommentPosted') return
    const payload = event.payload
    if (!payload.id || !payload.text || chatMessages.value.some(message => String(message.id) === String(payload.id))) return
    chatMessages.value.push({
        id: String(payload.id),
        from: payload.user?.name || payload.from || 'User',
        text: payload.text,
        timestamp: payload.ts || new Date().toISOString(),
        isHost: Boolean(payload.user?.id && props.hostId && String(payload.user.id) === String(props.hostId)),
    })
    if (chatMessages.value.length > 50) chatMessages.value = chatMessages.value.slice(-50)
    scrollToBottom()
})

onMounted(async () => {
    if (props.streamId) {
        await fetchChatMessages()
        setTimeout(() => {
            subscribeToChatEvents()
        }, 300)
    }
})

onUnmounted(() => {

})
</script>
