<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="guest" class="p-8 text-center bg-white rounded-3xl shadow-2xl">
      <!-- Avatar -->
      <div :class="['w-24 h-24 rounded-full mx-auto flex items-center justify-center text-2xl font-black text-white shadow-xl mb-6', guest.color || 'bg-slate-700']">
        {{ guest.name.substring(0,2).toUpperCase() }}
      </div>

      <h2 class="text-2xl font-black text-slate-900 mb-2">Co-host invitation</h2>

      <p class="text-slate-500 leading-relaxed mb-8 px-4">
        <span class="font-black text-slate-900">@{{ guest.name }}</span>, the host invited you to co-host this live. Join to go on screen together.
      </p>

      <div class="grid grid-cols-2 gap-4 mb-6">
        <button @click="decline" class="py-4 border border-slate-200 rounded-2xl font-black text-slate-700 hover:bg-slate-50 transition transform active:scale-95">
          Decline
        </button>
        <button @click="join" class="py-4 bg-blue-500 hover:bg-blue-600 text-white rounded-2xl font-black shadow-lg shadow-blue-200 transition transform active:scale-95">
          Join as co-host
        </button>
      </div>

      <p class="text-[11px] font-bold text-slate-400">
        Only invited guests can co-host — you choose to join.
      </p>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['join', 'decline']);

const modalRef = ref(null);
const guest = ref(null);

const open = (guestData) => {
  guest.value = guestData;
  modalRef.value?.open();
};

const close = () => {
  modalRef.value?.close();
};

const decline = () => {
  emit('decline', guest.value);
  close();
};

const join = () => {
  emit('join', guest.value);
  close();
};

defineExpose({ open, close });
</script>
