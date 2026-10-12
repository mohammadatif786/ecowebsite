<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="p-5 space-y-4">
      <div class="flex items-center justify-between border-b pb-3">
        <h3 class="text-lg font-black text-slate-900">Edit Post</h3>
        <button @click="close" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>

      <div>
        <label class="block text-xs font-black text-slate-500 mb-1">Caption / Message</label>
        <textarea
          v-model="caption"
          rows="4"
          class="w-full bg-slate-50 border border-slate-200 focus:border-lkblue focus:bg-white rounded-2xl p-3 text-xs font-semibold text-slate-800 outline-none transition resize-none leading-relaxed"
          placeholder="Edit your caption..."
        ></textarea>
      </div>

      <div>
        <label class="block text-xs font-black text-slate-500 mb-1">Location</label>
        <input
          v-model="location"
          type="text"
          class="w-full bg-slate-50 border border-slate-200 focus:border-lkblue focus:bg-white rounded-2xl p-3 text-xs font-semibold text-slate-800 outline-none transition"
          placeholder="📍 Edit location"
        />
      </div>

      <div class="flex gap-2 pt-2">
        <button @click="close" class="btn btn-ghost flex-1 py-3 text-xs font-bold">Cancel</button>
        <button @click="handleSave" :disabled="saving || !caption.trim()" class="btn btn-primary flex-1 py-3 text-xs font-black">
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';
import Modal from '../ui/Modal.vue';

const modalRef = ref(null);
const vibe = ref(null);
const caption = ref('');
const location = ref('');
const saving = ref(false);

const emit = defineEmits(['updated']);

const open = (vibeData) => {
  vibe.value = vibeData;
  caption.value = vibeData.caption || '';
  location.value = vibeData.location || '';
  saving.value = false;
  modalRef.value?.open();
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  modalRef.value?.close();
};

const handleSave = async () => {
  if (!vibe.value || !caption.value.trim()) return;

  saving.value = true;
  try {
    const response = await axios.put(route('new_frontend.vibes.update', { vibe: vibe.value.id }), {
      caption: caption.value,
      location_name: location.value
    });

    if (window.toast) window.toast('✏️ Post updated successfully!');
    emit('updated', {
      id: vibe.value.id,
      caption: caption.value,
      location: location.value
    });
    close();
  } catch (e) {
    console.error('Failed to update vibe', e);
    const msg = e.response?.data?.message || 'Failed to update post.';
    if (window.toast) window.toast(msg);
  } finally {
    saving.value = false;
  }
};

defineExpose({ open, close });
</script>
