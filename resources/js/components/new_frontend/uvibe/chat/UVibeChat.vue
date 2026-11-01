<template>
  <!-- The messages button in the U Vibe header: your Vibes conversations, newest first; tap one to chat. -->
  <button type="button" @click="open" class="h-11 w-11 rounded-xl bg-white/20 grid place-items-center" title="Messages" aria-label="Messages">
    <MessageCircle class="w-4 h-4" />
  </button>

  <!-- These windows are opened from the purple header (white text); they must not inherit it.
       `contents` = no box of its own, so it doesn't change the header's layout. -->
  <div class="contents text-slate-900">
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="p-4 flex items-center justify-between border-b border-slate-100">
      <h3 class="text-lg font-black flex items-center gap-2"><MessageCircle class="w-5 h-5" />Vibes Messages</h3>
      <button type="button" @click="close" aria-label="Close"><X class="w-5 h-5" /></button>
    </div>
    <div class="p-2">
      <p v-if="loading" class="text-center text-slate-400 font-bold py-10 text-sm">Loading your messages…</p>
      <p v-else-if="failed" class="text-center text-rose-600 font-bold py-10 text-sm px-4">Couldn’t load your messages — close this and try again.</p>
      <p v-else-if="!rows.length" class="text-center text-slate-400 font-bold py-10 text-sm px-4">No Vibes messages yet — message someone from their profile or post.</p>
      <button v-for="c in rows" :key="c.user_id" type="button" @click="openThread(c)"
              class="w-full flex items-center gap-3 rounded-2xl p-3 text-left hover:bg-slate-50">
        <img v-if="c.avatar && !broken[c.user_id]" :src="c.avatar" alt="" @error="broken[c.user_id] = true"
             class="h-11 w-11 rounded-full object-cover bg-slate-100 shrink-0" />
        <div v-else class="h-11 w-11 rounded-full bg-slate-700 text-white grid place-items-center font-black text-xs shrink-0">{{ initials(c) }}</div>
        <div class="flex-1 min-w-0">
          <p class="font-black text-sm truncate">{{ c.name || c.handle }}</p>
          <p class="text-[11px] text-slate-400 truncate">{{ c.last_message || '' }}</p>
        </div>
      </button>
    </div>
  </Modal>

  <!-- The conversation itself (the Vibes chat window); its back arrow returns to this list. -->
  <VibesChatModal ref="threadRef" @back="open" />
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { MessageCircle, X } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import VibesChatModal from '../../modals/VibesChatModal.vue';
import { fetchConversations } from '../../../../services/uvibeChat';

const modalRef = ref(null);
const threadRef = ref(null);
const rows = ref([]);
const loading = ref(false);
const failed = ref(false);
const broken = reactive({});

const open = async () => {
  failed.value = false;
  loading.value = true;
  modalRef.value?.open();
  try {
    rows.value = await fetchConversations();
  } catch (e) {
    rows.value = [];
    failed.value = true;
  } finally {
    loading.value = false;
  }
};
const close = () => modalRef.value?.close();

const initials = (c) => String(c.name || c.handle || '?').replace(/^@/, '').slice(0, 2).toUpperCase();

const openThread = (c) => {
  close();
  // The chat window shows "@" itself, so hand it the handle without one.
  threadRef.value?.open({
    user_id: c.user_id,
    name: c.name,
    avatar: c.avatar,
    handle: String(c.handle || '').replace(/^@/, '') || c.name,
  });
};

defineExpose({ open, close });
</script>
