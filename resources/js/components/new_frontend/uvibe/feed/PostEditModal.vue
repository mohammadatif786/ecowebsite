<template>
  <!-- "Edit post": title, words, and (not for gigs) the price and the event people buy tickets for. -->
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="post">
      <div class="p-4 flex items-center justify-between border-b border-slate-100">
        <h3 class="text-lg font-black">Edit post</h3>
        <button type="button" @click="close" class="text-slate-400" aria-label="Close"><X class="w-5 h-5" /></button>
      </div>
      <div class="p-4 space-y-3">
        <input v-model="form.title" maxlength="120" placeholder="Title"
               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-[#2f9bef]" />
        <textarea v-model="form.caption" rows="4" maxlength="2200" placeholder="What's happening on campus?"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#2f9bef]"></textarea>
        <template v-if="post.category !== 'opportunity'">
          <PostPriceField v-model="form.price" />
          <PostEventLink v-model="form.eventId" :events="userEvents" />
        </template>
        <p v-else class="text-[11px] text-slate-500 px-1">A gig’s pay can’t change once it’s posted — people may already have applied for it.</p>
        <p v-if="error" class="text-sm font-bold text-rose-600">{{ error }}</p>
        <button type="button" @click="save" :disabled="saving" class="btn btn-primary w-full py-3 disabled:opacity-60">
          {{ saving ? 'Saving…' : 'Save changes' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { X } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import PostPriceField from '../compose/PostPriceField.vue';
import PostEventLink from '../compose/PostEventLink.vue';
import { firstError, notify } from '../../../../services/uvibe';
import { editPost } from '../../../../services/uvibePosts';

defineProps({
  // The member's own events they can link a post to.
  userEvents: { type: Array, default: () => [] },
});
const emit = defineEmits(['updated']);

const modalRef = ref(null);
const post = ref(null);
const saving = ref(false);
const error = ref('');
const form = reactive({ title: '', caption: '', price: '', eventId: null });
let startEventId = null;

const open = (p) => {
  post.value = p;
  error.value = '';
  form.title = p.title || '';
  form.caption = p.desc || '';
  form.price = p.price || '';
  startEventId = p.tag?.kind === 'event' ? Number(p.tag.id) : null;
  form.eventId = startEventId;
  modalRef.value?.open();
};
const close = () => modalRef.value?.close();

const save = async () => {
  error.value = '';
  if (!form.title.trim()) { error.value = 'Add a title.'; return; }
  if (post.value.isText && !form.caption.trim()) { error.value = 'Write something for your text card.'; return; }

  const data = { title: form.title.trim(), caption: form.caption.trim() };
  if (post.value.category !== 'opportunity') {
    data.price = Number(form.price) || 0;
    // Only touch the linked event when it was changed here.
    if ((form.eventId ?? null) !== startEventId) data.event_id = form.eventId;
  }

  saving.value = true;
  try {
    const res = await editPost(post.value.id, data);
    notify(res.message);
    emit('updated', res.post);
    close();
  } catch (e) {
    error.value = firstError(e, 'Could not save your changes');
  } finally {
    saving.value = false;
  }
};

defineExpose({ open, close });
</script>
