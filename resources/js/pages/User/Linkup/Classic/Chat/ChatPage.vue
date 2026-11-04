<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <h1 class="text-2xl font-bold text-gray-900">Public Chat</h1>
        <p class="text-gray-600">Chat with everyone in real-time</p>
      </div>
    </div>

    <!-- Chat Container -->
    <div class="max-w-4xl mx-auto px-4 py-6">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Messages Area -->
        <div
          ref="messagesContainer"
          class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50"
        >
          <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
            <div class="text-4xl mb-2">💬</div>
            <p>No messages yet. Start the conversation!</p>
          </div>

          <div
            v-for="message in messages"
            :key="message.id"
            class="flex items-start space-x-3"
            :class="message.user_id === currentUser?.id ? 'justify-end' : 'justify-start'"
          >
            <div
              v-if="message.user_id !== currentUser?.id"
              class="flex-shrink-0"
            >
              <img
                :src="message.user_avatar"
                :alt="message.user_name"
                class="w-8 h-8 rounded-full object-cover"
                @error="$event.target.src = '/images/default-avatar.png'"
              >
            </div>

            <div
              class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
              :class="message.user_id === currentUser?.id
                ? 'bg-blue-500 text-white'
                : 'bg-white border border-gray-200'"
            >
              <div
                v-if="message.user_id !== currentUser?.id"
                class="text-xs font-medium text-gray-600 mb-1"
              >
                {{ message.user_name }}
              </div>
              <div class="text-sm">{{ message.message }}</div>
              <div
                class="text-xs mt-1"
                :class="message.user_id === currentUser?.id ? 'text-blue-100' : 'text-gray-500'"
              >
                {{ formatTime(message.timestamp) }}
              </div>
            </div>

            <div
              v-if="message.user_id === currentUser?.id"
              class="flex-shrink-0"
            >
              <img
                :src="message.user_avatar"
                :alt="message.user_name"
                class="w-8 h-8 rounded-full object-cover"
                @error="$event.target.src = '/images/default-avatar.png'"
              >
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="border-t bg-white p-4">
          <form @submit.prevent="sendMessage" class="flex space-x-3">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Type your message..."
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :disabled="isSending"
              maxlength="500"
            >
            <button
              type="submit"
              :disabled="!newMessage.trim() || isSending"
              class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="isSending">Sending...</span>
              <span v-else>Send</span>
            </button>
          </form>

          <!-- Character Count -->
          <div class="text-xs text-gray-500 mt-2 text-right">
            {{ newMessage.length }}/500 characters
          </div>
        </div>
      </div>

      <!-- Connection Status -->
      <div class="mt-4 text-center">
        <div
          class="inline-flex items-center px-3 py-1 rounded-full text-sm"
          :class="isConnected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
        >
          <div
            class="w-2 h-2 rounded-full mr-2"
            :class="isConnected ? 'bg-green-500' : 'bg-red-500'"
          ></div>
          {{ isConnected ? 'Connected' : 'Disconnected' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

// Types
interface ChatMessage {
  id: string
  user_id: number
  user_name: string
  user_avatar: string
  message: string
  timestamp: string
}

interface User {
  id: number
  name: string
  avatar?: string
}

// Reactive data
const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const isSending = ref(false)
const isConnected = ref(false)
const messagesContainer = ref<HTMLElement>()

// Get current user from Inertia page
const page = usePage()
const currentUser = ref<User | null>(page.props.auth?.user || null)

// Initialize Pusher connection
let echo: any = null

onMounted(() => {
  initializeChat()
})

onUnmounted(() => {
  if (echo) {
    echo.disconnect()
  }
})

const initializeChat = () => {
  try {
    // Check if Echo is available
    if (typeof window !== 'undefined' && (window as any).Echo) {
      echo = (window as any).Echo

      // Subscribe to public chat channel
      const channel = echo.channel('public-chat')

      // Listen for new messages
      channel.listen('.message.sent', (data: any) => {
        console.log('Received message:', data)
        messages.value.push(data)
        scrollToBottom()
      })

      // Set connection status
      isConnected.value = true
      console.log('Connected to chat channel')

    } else {
      console.error('Echo not available')
      isConnected.value = false
    }
  } catch (error) {
    console.error('Error initializing chat:', error)
    isConnected.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return

  isSending.value = true

  try {
    const response = await axios.post('/chat/send', {
      message: newMessage.value.trim()
    })

    if (response.data.success) {
      newMessage.value = ''
    } else {
      console.error('Failed to send message')
    }
  } catch (error) {
    console.error('Error sending message:', error)
  } finally {
    isSending.value = false
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const formatTime = (timestamp: string) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// Auto-scroll when new messages arrive
const scrollToBottomOnNewMessage = () => {
  scrollToBottom()
}
</script>

<style scoped>
/* Custom scrollbar for messages */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
