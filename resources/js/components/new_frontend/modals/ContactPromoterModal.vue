<template>
  <Modal ref="modalRef" maxWidth="max-w-[460px]">
    <div v-if="sale" class="w-full flex flex-col h-[520px]">
      <!-- Header -->
      <div class="p-5 border-b border-slate-100 shrink-0">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-2xl font-black text-slate-900 leading-tight">{{ organizerName }}</h2>
            <p class="text-sm text-slate-400 font-bold">Regarding: {{ eventTitle }}</p>
          </div>
          <button @click="close" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Chat Body -->
      <div ref="chatBody" class="flex-1 overflow-y-auto p-5 bg-slate-50/50 space-y-4">
        <div v-if="messages.length === 0" class="bg-white rounded-2xl rounded-tl-none p-4 shadow-sm border border-slate-100 max-w-[85%]">
          <p class="text-[13px] text-slate-700 leading-relaxed">
            Hi! Thanks for reaching out about {{ eventTitle }}. How can I help?
          </p>
          <span class="text-[10px] text-slate-400 font-bold mt-2 block">{{ currentTime }}</span>
        </div>

        <div v-for="(msg, idx) in messages" :key="idx"
             :class="['flex flex-col', msg.me ? 'items-end' : 'items-start']">
          <div :class="['max-w-[85%] rounded-2xl p-4 shadow-sm text-[13px] leading-relaxed',
                        msg.me ? 'bg-lkblue2 text-white rounded-tr-none' : 'bg-white border border-slate-100 text-slate-700 rounded-tl-none']">
            {{ msg.text }}
          </div>
          <span class="text-[10px] text-slate-400 font-bold mt-1 px-1">{{ msg.time }}</span>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-slate-100 bg-white shrink-0">
        <div class="flex items-center gap-2 mb-4">
          <input
            v-model="newMessage"
            @keyup.enter="sendMessage"
            type="text"
            placeholder="Type a message..."
            class="flex-1 bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-lkblue2/20 transition-all"
          />
          <button
            @click="sendMessage"
            :disabled="!newMessage.trim()"
            class="w-12 h-12 flex items-center justify-center bg-lkblue2 text-white rounded-full hover:bg-lkblue2/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            <Send class="w-5 h-5" />
          </button>
        </div>

        <button @click="back" class="w-full py-4 text-sm font-black text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
          Back to Cancellation
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { X, Send } from 'lucide-vue-next';
import Modal from '../ui/Modal.vue';
import axios from 'axios';

const emit = defineEmits(['back']);

const modalRef = ref(null);
const sale = ref(null);
const newMessage = ref('');
const messages = ref([]);
const chatBody = ref(null);

const eventTitle = computed(() => sale.value?.event?.title || 'Event');
const organizerName = computed(() => sale.value?.event?.organizer_name || 'Organizer');
const organizerUserId = computed(() => sale.value?.event?.organizer_user_id);

const currentTime = computed(() => {
  return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const open = (ticketSale) => {
  sale.value = ticketSale;
  messages.value = [];
  newMessage.value = '';
  if (modalRef.value) {
    modalRef.value.open();
    scrollToBottom();
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const back = () => {
  emit('back', sale.value);
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !organizerUserId.value) return;

  const text = newMessage.value;
  const time = currentTime.value;

  messages.value.push({ text, time, me: true });
  newMessage.value = '';
  scrollToBottom();

  try {
    // Attempt to send message via existing chat API
    await axios.post(route('new_frontend.dating.chats.messages.store', { recipient: organizerUserId.value }), {
      content: text,
      ticket_sale_id: sale.value.id
    });
  } catch (err) {
    console.error('Failed to send message:', err);
    if (window.toast) window.toast('Message sent, but failed to sync with server.');
  }
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatBody.value) {
      chatBody.value.scrollTop = chatBody.value.scrollHeight;
    }
  });
};

defineExpose({ open, close });
</script>
