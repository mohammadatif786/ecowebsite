<template>
  <Modal ref="modalRef" maxWidth="max-w-sm">
    <div class="p-5 text-center">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-xl font-black">🎟️ Ticket QR</h3>
        <button @click="close"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></button>
      </div>
      
      <p class="font-black">{{ eventName }}</p>
      <p class="text-[11px] text-slate-400 mb-3">#{{ ticketId }}</p>
      
      <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=LinkUp-Ticket-' + ticketId" class="mx-auto rounded-2xl border border-slate-100 shadow-sm" alt="QR Code"/>
      
      <p class="text-[12px] text-emerald-600 font-bold mt-3">✅ Scan at the gate to validate entry</p>
      
      <button @click="sendViaChat" class="btn btn-primary w-full mt-4 py-3">Send via Chat</button>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../ui/Modal.vue';

const modalRef = ref(null);
const ticketId = ref('');
const eventName = ref('');

const open = (id, event) => {
  ticketId.value = id;
  eventName.value = event;
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) setTimeout(() => window.lucide.createIcons(), 50);
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const sendViaChat = () => {
  if (window.toast) window.toast('📤 Sent via chat');
  close();
};

defineExpose({ open, close });
</script>
