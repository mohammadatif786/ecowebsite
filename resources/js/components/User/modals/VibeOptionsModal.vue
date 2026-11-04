<template>
  <Modal ref="modalRef" maxWidth="max-w-xs">
    <div class="py-2">
      <!-- Edit Option (Only if owner) -->
      <button
        v-if="isOwner && step === 'menu'"
        @click="handleEdit"
        class="w-full px-6 py-4 flex items-center gap-3 text-lkblue hover:bg-blue-50 transition active:scale-95"
      >
        <i data-lucide="edit-3" class="w-5 h-5"></i>
        <span class="font-black text-sm">Edit post</span>
      </button>

      <!-- Delete Option (Only if owner) -->
      <button
        v-if="isOwner && step === 'menu'"
        @click="handleDelete"
        class="w-full px-6 py-4 flex items-center gap-3 text-rose-600 hover:bg-rose-50 transition active:scale-95"
      >
        <i data-lucide="trash-2" class="w-5 h-5"></i>
        <span class="font-black text-sm">Delete post</span>
      </button>

      <!-- Copy Link -->
      <button
        v-if="step === 'menu'"
        @click="handleCopyLink"
        class="w-full px-6 py-4 flex items-center gap-3 text-slate-700 hover:bg-slate-50 transition active:scale-95"
      >
        <i data-lucide="link" class="w-5 h-5"></i>
        <span class="font-black text-sm">Copy link</span>
      </button>

      <!-- Hide (other people's posts) -->
      <button
        v-if="!isOwner && step === 'menu'"
        @click="handleHide"
        :disabled="busy"
        class="w-full px-6 py-4 flex items-center gap-3 text-slate-700 hover:bg-slate-50 transition active:scale-95"
      >
        <i data-lucide="eye-off" class="w-5 h-5"></i>
        <span class="font-black text-sm">Hide this post</span>
      </button>

      <!-- Report (other people's posts) -->
      <button
        v-if="!isOwner && step === 'menu'"
        @click="openReport"
        class="w-full px-6 py-4 flex items-center gap-3 text-rose-600 hover:bg-rose-50 transition active:scale-95"
      >
        <i data-lucide="flag" class="w-5 h-5"></i>
        <span class="font-black text-sm">Report post</span>
      </button>

      <!-- Report: pick a reason -->
      <div v-if="step === 'report'" class="px-4 pb-2">
        <p class="font-black text-sm px-2 pt-2 pb-1">What’s wrong with this post?</p>
        <p class="text-[11px] font-semibold text-slate-500 px-2 pb-2">The poster won’t know who reported it.</p>
        <div class="max-h-72 overflow-y-auto space-y-1">
          <button
            v-for="r in reasons"
            :key="r.key"
            @click="reason = r.key"
            :class="['w-full text-left rounded-2xl px-3 py-2 border-2 transition', reason === r.key ? 'border-rose-400 bg-rose-50' : 'border-transparent hover:bg-slate-50']"
          >
            <span class="font-black text-[13px]">{{ r.emoji }} {{ r.label }}</span>
            <span class="block text-[11px] text-slate-500 font-semibold">{{ r.description }}</span>
          </button>
        </div>
        <textarea v-if="reason === 'other'" v-model="notes" rows="2" maxlength="500" placeholder="Tell us briefly what’s wrong"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"></textarea>
        <button @click="submitReport" :disabled="!reason || busy"
          class="mt-2 w-full rounded-2xl py-3 font-black text-white bg-rose-600 disabled:opacity-50">
          {{ busy ? 'Sending…' : 'Report' }}
        </button>
      </div>

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
import axios from 'axios';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  vibe: Object,
  isOwner: Boolean
});

const emit = defineEmits(['delete', 'edit', 'hidden']);

const step = ref('menu');
const busy = ref(false);
const reasons = ref([]);
const reason = ref('');
const notes = ref('');

const say = (msg) => { if (window.toast) window.toast(msg); };
const errorText = (e, fallback) => e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || fallback;

const handleHide = async () => {
  busy.value = true;
  try {
    const { data } = await axios.post(route('user.vibes.hide', { vibe: props.vibe.id }));
    say(data.message || 'Post hidden');
    emit('hidden', props.vibe.id);
    close();
  } catch (e) {
    say(errorText(e, 'Couldn’t hide this post.'));
  } finally {
    busy.value = false;
  }
};

const openReport = async () => {
  step.value = 'report';
  reason.value = '';
  notes.value = '';
  if (!reasons.value.length) {
    try {
      const { data } = await axios.get(route('user.vibes.report-reasons'));
      reasons.value = data.reasons || [];
    } catch (e) {
      say('Couldn’t load report reasons.');
    }
  }
};

const submitReport = async () => {
  busy.value = true;
  try {
    const { data } = await axios.post(route('user.vibes.report', { vibe: props.vibe.id }), { reason: reason.value, notes: notes.value || null });
    say(data.message || 'Thanks — our team will review this post.');
    close();
  } catch (e) {
    say(errorText(e, 'Couldn’t send the report.'));
  } finally {
    busy.value = false;
  }
};

const handleEdit = () => {
  emit('edit', props.vibe);
  close();
};

const modalRef = ref(null);

const open = () => {
  step.value = 'menu';
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
  const url = `${window.location.origin}/user/vibes?id=${props.vibe.id}`;
  navigator.clipboard.writeText(url).then(() => {
    if (window.toast) window.toast('✅ Link copied to clipboard!');
    close();
  });
};

defineExpose({ open, close });
</script>
