<template>
  <Modal ref="modalRef">
    <div class="flex items-center justify-between border-b p-4">
      <h3 class="text-lg font-black">New Reel</h3>
      <button @click="close">✕</button>
    </div>
    <div class="space-y-4 p-4">
      <!-- Upload Type Selection -->
      <div class="grid grid-cols-3 gap-2">
        <button
          @click="selectType('video')"
          class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all"
          :class="selectedType === 'video' ? 'border-lkblue bg-blue-50' : 'border-slate-200 hover:border-slate-300'"
        >
          <span class="text-2xl">🎥</span>
          <span class="text-xs font-bold">Upload Video</span>
        </button>
        <button
          @click="selectType('image')"
          class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all"
          :class="selectedType === 'image' ? 'border-lkblue bg-blue-50' : 'border-slate-200 hover:border-slate-300'"
        >
          <span class="text-2xl">📷</span>
          <span class="text-xs font-bold">Upload Picture</span>
        </button>
        <button
          @click="selectType('gallery')"
          class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all"
          :class="selectedType === 'gallery' ? 'border-lkblue bg-blue-50' : 'border-slate-200 hover:border-slate-300'"
        >
          <span class="text-2xl">🖼️</span>
          <span class="text-xs font-bold">Gallery</span>
        </button>
      </div>

      <!-- File Upload Area -->
      <div v-if="selectedType" class="rounded-2xl border-2 border-dashed p-6">
        <div v-if="!file" class="text-center">
          <input
            ref="fileInput"
            type="file"
            :accept="acceptTypes"
            class="hidden"
            @change="handleFileSelect"
          />
          <button
            @click="triggerFileInput"
            class="w-full py-4 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-sm transition"
          >
            Choose {{ typeLabel }}
          </button>
          <p class="text-xs font-bold text-slate-400 mt-2">
            Max file size: 50MB
          </p>
        </div>
        <div v-else class="relative">
          <video
            v-if="selectedType === 'video'"
            :src="filePreview"
            controls
            class="w-full h-64 object-contain rounded-xl bg-black"
          />
          <img
            v-else
            :src="filePreview"
            class="w-full h-64 object-contain rounded-xl bg-black"
          />
          <button
            @click="removeFile"
            class="absolute top-2 right-2 rounded-full bg-black/60 px-3 py-1 text-white font-bold text-sm"
          >
            ✕
          </button>
        </div>
      </div>

      <!-- Caption Input -->
      <textarea
        v-model="caption"
        maxlength="1000"
        rows="3"
        placeholder="Write a caption…"
        class="w-full rounded-2xl border p-3 resize-none"
      />

      <!-- Location Input -->
      <input
        v-model="location"
        placeholder="📍 Add location"
        class="w-full rounded-2xl border p-3"
      />

      <p v-if="error" class="text-sm font-semibold text-red-600">{{ error }}</p>
      <button
        :disabled="busy || !file"
        class="btn btn-primary w-full py-3 disabled:opacity-60"
        @click="submit"
      >
        {{ busy ? 'Posting…' : 'Post Reel' }}
      </button>
    </div>
  </Modal>
</template>

<script setup>
import axios from 'axios';
import { ref, computed } from 'vue';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['reelCreated']);
const modalRef = ref();
const fileInput = ref();
const selectedType = ref(null);
const file = ref(null);
const filePreview = ref(null);
const caption = ref('');
const location = ref('');
const busy = ref(false);
const error = ref('');

const typeLabel = computed(() => {
  switch (selectedType.value) {
    case 'video': return 'Video';
    case 'image': return 'Image';
    case 'gallery': return 'Media';
    default: return 'File';
  }
});

const acceptTypes = computed(() => {
  switch (selectedType.value) {
    case 'video': return 'video/*';
    case 'image': return 'image/*';
    case 'gallery': return 'image/*,video/*';
    default: return '*/*';
  }
});

const selectType = (type) => {
  selectedType.value = type;
  file.value = null;
  filePreview.value = null;
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const selectedFile = event.target.files[0];
  if (!selectedFile) return;

  // Validate file size (50MB max)
  if (selectedFile.size > 50 * 1024 * 1024) {
    error.value = 'File size exceeds 50MB limit';
    return;
  }

  file.value = selectedFile;
  filePreview.value = URL.createObjectURL(selectedFile);
  error.value = '';
};

const removeFile = () => {
  if (filePreview.value) {
    URL.revokeObjectURL(filePreview.value);
  }
  file.value = null;
  filePreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const open = () => {
  modalRef.value?.open();
};

const close = () => {
  modalRef.value?.close();
  resetForm();
};

const resetForm = () => {
  selectedType.value = null;
  removeFile();
  caption.value = '';
  location.value = '';
  error.value = '';
};

const submit = async () => {
  error.value = '';
  
  if (!file.value) {
    error.value = 'Please select a file to upload';
    return;
  }

  if (!selectedType.value) {
    error.value = 'Please select a reel type';
    return;
  }

  const form = new FormData();
  form.append('type', selectedType.value);
  form.append('file', file.value);
  if (caption.value.trim()) {
    form.append('caption', caption.value);
  }
  if (location.value.trim()) {
    form.append('location', location.value);
  }

  busy.value = true;
  try {
    const response = await axios.post(route('new_frontend.reels.store'), form);
    emit('reelCreated', response.data.reel);
    close();
  } catch (e) {
    error.value = Object.values(e.response?.data?.errors || {})[0]?.[0] || e.response?.data?.message || 'Unable to post reel';
  } finally {
    busy.value = false;
  }
};

defineExpose({ open, close });
</script>