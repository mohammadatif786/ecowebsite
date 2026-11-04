<template>
  <!-- "Report this post": pick what's wrong; the poster never learns who reported. -->
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="post" class="flex flex-col max-h-[85vh]">
      <div class="p-4 border-b border-slate-100 shrink-0">
        <div class="flex items-center justify-between gap-2">
          <h3 class="text-lg font-black text-slate-900">🚩 Report this post</h3>
          <button type="button" @click="close" aria-label="Close" class="h-8 w-8 rounded-full bg-slate-100 font-black text-slate-600">✕</button>
        </div>
        <p class="mt-1.5 text-xs font-semibold text-slate-500">
          What’s wrong with it? Your report is confidential — {{ author }} won’t know who sent it.
        </p>
      </div>
      <div class="p-4 overflow-y-auto min-h-0">
        <button v-for="r in list" :key="r.key" type="button" @click="reason = r.key" :aria-pressed="reason === r.key"
                class="w-full flex items-center gap-3 text-left p-2.5 mb-1.5 rounded-2xl border-2"
                :class="reason === r.key ? 'border-red-600 bg-red-50' : 'border-slate-100 bg-white'">
          <span class="shrink-0 text-xl">{{ r.emoji }}</span>
          <span class="min-w-0 flex-1">
            <span class="block text-[13.5px] font-black text-slate-900">{{ r.label }}</span>
            <span class="block text-[11.5px] font-semibold text-slate-500">{{ r.description }}</span>
          </span>
          <span class="shrink-0 w-[18px] h-[18px] rounded-full border-2"
                :class="reason === r.key ? 'border-red-600 bg-red-600' : 'border-slate-300 bg-white'"></span>
        </button>
        <textarea v-model="notes" rows="3" maxlength="500" placeholder="Add details (optional) — what does the post show or say?"
                  class="mt-2 w-full rounded-2xl border-[1.5px] border-slate-200 px-3 py-2.5 text-[13px] outline-none text-slate-900 bg-white"></textarea>
        <p v-if="error" class="mt-2 text-sm font-bold text-rose-600">{{ error }}</p>
        <button type="button" @click="send" :disabled="sending"
                class="mt-2.5 w-full rounded-2xl py-3 text-sm font-black text-white bg-red-600 disabled:opacity-60"
                :style="{ opacity: reason && !sending ? 1 : 0.45 }">
          {{ sending ? 'Sending…' : 'Send report' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, ref } from 'vue';
import Modal from '../../ui/Modal.vue';
import { fetchOptions, firstError, notify } from '../../../../services/uvibe';
import { reportPost } from '../../../../services/uvibePosts';
import { posterName } from '../../../../services/uvibeFormat';

const props = defineProps({
  // [{key, emoji, label, description, severity}] — from the options the page already has.
  reasons: { type: Array, default: () => [] },
});

const modalRef = ref(null);
const post = ref(null);
const reason = ref('');
const notes = ref('');
const error = ref('');
const sending = ref(false);
const fetched = ref([]);

const list = computed(() => (props.reasons.length ? props.reasons : fetched.value));
const author = computed(() => (post.value ? posterName(post.value) : 'the poster'));

const open = async (p) => {
  post.value = p;
  reason.value = '';
  notes.value = '';
  error.value = '';
  modalRef.value?.open();
  if (!props.reasons.length && !fetched.value.length) {
    try { fetched.value = (await fetchOptions()).report_reasons || []; } catch (e) { error.value = 'Could not load the reasons'; }
  }
};
const close = () => modalRef.value?.close();

const send = async () => {
  error.value = '';
  if (!reason.value) { error.value = 'Choose what’s wrong with the post'; return; }
  if (reason.value === 'other' && !notes.value.trim()) { error.value = 'Tell us briefly what’s wrong'; return; }

  sending.value = true;
  try {
    const res = await reportPost(post.value.id, reason.value, notes.value.trim());
    notify(res.message);
    close();
  } catch (e) {
    error.value = firstError(e, 'Could not send the report');
  } finally {
    sending.value = false;
  }
};

defineExpose({ open, close });
</script>
