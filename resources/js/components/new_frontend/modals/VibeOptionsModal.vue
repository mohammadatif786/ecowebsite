<template>
  <Modal ref="modalRef" maxWidth="max-w-xs">
    <div class="py-2">
      <!-- Delete Option (Only if owner) -->
      <button
        v-if="isOwner"
        @click="handleDelete"
        class="w-full px-6 py-4 flex items-center gap-3 text-rose-600 hover:bg-rose-50 transition active:scale-95"
      >
        <i data-lucide="trash-2" class="w-5 h-5"></i>
        <span class="font-black text-sm">Delete post</span>
      </button>

      <!-- Copy Link -->
      <button
        @click="handleCopyLink"
        class="w-full px-6 py-4 flex items-center gap-3 text-slate-700 hover:bg-slate-50 transition active:scale-95"
      >
        <i data-lucide="link" class="w-5 h-5"></i>
        <span class="font-black text-sm">Copy link</span>
      </button>

      <!-- Cancel -->
      <button
        @click="close"
        class="w-full px-6 py-4 flex items-center gap-3 text-slate-500 hover:bg-slate-50 transition active:scale-95"
      >
        <i data-lucide="x" class="w-5 h-5"></i>
        <span class="font-black text-sm">Cancel</span>
      </button>
    </div>
  </Modal>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  vibe: Object,
  isOwner: Boolean
});

const emit = defineEmits(['delete']);

const modalRef = ref(null);

const open = () => {
  modalRef.value?.open();
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  modalRef.value?.close();
};

const handleDelete = () => {
  if (confirm('Are you sure you want to delete this post?')) {
    emit('delete', props.vibe.id);
    close();
  }
};

const handleCopyLink = () => {
  const url = `${window.location.origin}/new_frontend/vibes?id=${props.vibe.id}`;
  navigator.clipboard.writeText(url).then(() => {
    if (window.toast) window.toast('✅ Link copied to clipboard!');
    close();
  });
};

defineExpose({ open, close });
</script>
