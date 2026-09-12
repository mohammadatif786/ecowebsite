<template>
  <Modal ref="modalRef">
    <div class="p-4 flex items-center justify-between border-b border-slate-100">
      <h3 class="text-lg font-black">New Vibe</h3>
      <button @click="modalRef.close()" class="text-slate-400">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <div class="p-4 space-y-3">
      <div>
        <p class="text-[11px] font-black text-slate-400 uppercase mb-1.5">Post as</p>
        <div class="grid grid-cols-2 gap-2">
          <button @click="postAs = 'me'" class="rounded-2xl py-2.5 text-sm font-black border-2 flex items-center justify-center gap-1.5 transition" :class="postAs === 'me' ? 'border-lkblue bg-blue-50' : 'border-slate-200 bg-white'">
            👤 Myself
          </button>
          <button @click="postAs = 'org'" class="rounded-2xl py-2.5 text-sm font-black border-2 flex items-center justify-center gap-1.5 transition" :class="postAs === 'org' ? 'border-lkblue bg-blue-50' : 'border-slate-200 bg-white'">
            🏢 Organization / Group
          </button>
        </div>
      </div>

      <div v-if="postAs === 'org'" class="rounded-2xl bg-slate-50 p-3 space-y-2">
        <div v-if="postAsEntities.length" class="flex flex-wrap gap-2">
          <span
            v-for="entity in postAsEntities"
            :key="entity.id"
            class="inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-white px-3 py-1.5 text-xs font-black text-slate-700"
          >
            <span>{{ entity.type === 'Organization' ? '🏢' : '👥' }}</span>
            {{ entity.name }}
            <button
              type="button"
              class="ml-0.5 text-slate-400 hover:text-slate-700"
              :aria-label="`Remove ${entity.name}`"
              @click="removePostAsEntity(entity.id)"
            >
              <i data-lucide="x" class="h-3.5 w-3.5"></i>
            </button>
          </span>
        </div>
        <div class="flex gap-2">
          <input
            v-model.trim="postAsEntityName"
            type="text"
            placeholder="Organization or group name"
            class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-lkblue"
            @keyup.enter="addPostAsEntity"
          />
          <select v-model="postAsEntityType" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium outline-none focus:border-lkblue">
            <option>Organization</option>
            <option>Group</option>
          </select>
          <button type="button" class="btn btn-primary px-3 py-2 text-xs" @click="addPostAsEntity">Add</button>
        </div>
      </div>
      
      <div class="rounded-2xl border-2 border-dashed border-slate-200 p-4">
        <div class="grid grid-cols-3 gap-2">
          <label class="rounded-xl bg-slate-50 hover:bg-slate-100 p-3 flex flex-col items-center gap-1 cursor-pointer text-center">
            <input type="file" accept="image/*" capture="environment" class="hidden" @change="onMediaSelected" />
            <i data-lucide="camera" class="w-5 h-5 text-lkblue2"></i><span class="text-[11px] font-bold text-slate-600">Take Photo</span>
          </label>
          <label class="rounded-xl bg-slate-50 hover:bg-slate-100 p-3 flex flex-col items-center gap-1 cursor-pointer text-center">
            <input type="file" accept="video/*" capture="environment" class="hidden" @change="onMediaSelected" />
            <i data-lucide="video" class="w-5 h-5 text-lkblue2"></i><span class="text-[11px] font-bold text-slate-600">Record Video</span>
          </label>
          <label class="rounded-xl bg-slate-50 hover:bg-slate-100 p-3 flex flex-col items-center gap-1 cursor-pointer text-center">
            <input type="file" accept="image/*,video/*" multiple class="hidden" @change="onMediaSelected" />
            <i data-lucide="image-plus" class="w-5 h-5 text-lkblue2"></i><span class="text-[11px] font-bold text-slate-600">Gallery</span>
          </label>
        </div>
        <div v-if="draftMediaUrl" class="relative mt-3 rounded-xl overflow-hidden bg-black">
          <video v-if="draftIsVideo" :src="draftMediaUrl" controls class="w-full max-h-64 object-contain"></video>
          <img v-else :src="draftMediaUrl" class="w-full max-h-64 object-cover" />
          <div class="absolute top-2 right-2 flex gap-1.5">
            <button @click="clearMedia" class="h-7 w-7 rounded-full bg-black/60 text-white grid place-items-center">
              <i data-lucide="x" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
        <p v-else class="text-[11px] text-slate-400 text-center mt-2">Take photo · record video · gallery</p>
      </div>
      
      <textarea v-model="draftText" rows="2" placeholder="Write a caption… #carnival #island" class="w-full border border-slate-200 rounded-2xl p-3 text-sm outline-none focus:border-lkblue"></textarea>
      <input v-model="draftLoc" placeholder="📍 Add location" class="w-full border border-slate-200 rounded-2xl p-3 text-sm outline-none focus:border-lkblue" />
      
      <label class="flex items-center justify-between rounded-2xl bg-amber-50 px-3 py-2.5">
        <span class="font-bold text-sm">🪙 Allow Big Up coin gifts</span>
        <input type="checkbox" checked class="h-5 w-5" />
      </label>
      
      <div class="rounded-2xl border border-slate-200 p-3">
        <div class="flex items-center justify-between">
          <span class="font-bold text-sm">🛍️ Tag a product or 🎟️ event</span>
          <button class="btn btn-primary px-3 py-1.5 text-xs">Tag</button>
        </div>
        <div class="mt-2">
          <p class="text-[11px] text-slate-400">Make your vibe shoppable — viewers buy the product or grab tickets right from your post, and you earn commission.</p>
        </div>
      </div>
      
      <button @click="postVibe" class="btn btn-primary w-full py-3">Share Vibe</button>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../ui/Modal.vue';
import { getUser } from '../MockDataStore';

const emit = defineEmits(['postCreated']);

const user = getUser();
const modalRef = ref(null);
const postAs = ref('me');
const draftText = ref('');
const draftLoc = ref(`${user.city}, ${user.country}`);
const draftMediaUrl = ref(null);
const draftIsVideo = ref(false);
const postAsEntityName = ref('');
const postAsEntityType = ref('Organization');
const postAsEntities = ref([]);

const addPostAsEntity = () => {
  if (!postAsEntityName.value) return;

  postAsEntities.value.push({
    id: Date.now(),
    name: postAsEntityName.value,
    type: postAsEntityType.value,
  });
  postAsEntityName.value = '';
};

const removePostAsEntity = (id) => {
  postAsEntities.value = postAsEntities.value.filter((entity) => entity.id !== id);
};

const open = () => {
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) {
      setTimeout(() => lucide.createIcons(), 50);
    }
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

defineExpose({ open, close });

const onMediaSelected = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  draftIsVideo.value = file.type.startsWith('video/');
  draftMediaUrl.value = URL.createObjectURL(file);
};

const clearMedia = () => {
  draftMediaUrl.value = null;
  draftIsVideo.value = false;
};

const postVibe = () => {
  const newPost = {
    id: Date.now(),
    handle: user.name,
    avatar: user.avatar,
    location: draftLoc.value,
    kind: draftIsVideo.value ? 'reel' : 'photo',
    media: draftMediaUrl.value || 'https://picsum.photos/800/600',
    caption: draftText.value || 'New vibe ✨',
    likes: 0,
    comments: 0,
    bigup: 0,
    shoppable: false,
    realVideo: draftIsVideo.value
  };
  
  emit('postCreated', newPost);
  close();
  draftText.value = '';
  draftMediaUrl.value = null;
};
</script>
