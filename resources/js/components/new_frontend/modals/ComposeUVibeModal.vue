<template>
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="-m-1">
      <div class="rounded-t-[19px] overflow-hidden" style="background:linear-gradient(120deg,#7c3aed,#a855f7)">
        <div class="p-5 flex items-start justify-between gap-3 text-white">
          <div class="flex items-center gap-3 min-w-0">
            <span class="h-11 w-11 rounded-2xl bg-white/20 grid place-items-center shrink-0">
              <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            </span>
            <div class="min-w-0">
              <h3 class="font-black text-xl truncate">Post to U Vibe</h3>
              <p class="text-white/70 text-xs font-bold">Share parties, events and campus life across universities.</p>
            </div>
          </div>
          <button @click="close" class="shrink-0">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
      </div>
      
      <div class="p-5 space-y-3">
        <div>
          <p class="text-[11px] font-black text-slate-400 uppercase mb-1.5">Your campus</p>
          <select v-model="campus" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none">
            <option v-for="[n, flag] in UVIBE_CAMPUSES" :key="n" :value="n">{{ flag }} {{ n }}</option>
          </select>
        </div>
        
        <div>
          <p class="text-[11px] font-black text-slate-400 uppercase mb-1.5">Type</p>
          <select v-model="category" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none">
            <option v-for="[id, label] in UVIBE_CATS.filter(c => c[0] !== 'all')" :key="id" :value="id">{{ label }}</option>
          </select>
        </div>
        
        <input v-model="title" placeholder="Title (e.g. Campus Culture Night)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none" />
        <textarea v-model="desc" rows="3" placeholder="What's happening on campus?" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none"></textarea>
        
        <div v-if="imageUrl" class="relative">
          <img :src="imageUrl" class="w-full h-40 object-cover rounded-2xl" />
          <button @click="imageUrl = null" class="absolute top-2 right-2 h-7 w-7 rounded-full bg-black/60 text-white grid place-items-center">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>
        <label v-else class="w-full rounded-2xl border-2 border-dashed border-violet-200 text-violet-600 font-black py-3 flex items-center justify-center gap-2 cursor-pointer">
          <input type="file" accept="image/*" class="hidden" @change="onImageSelected" />
          <i data-lucide="image-plus" class="w-4 h-4"></i>Add a photo
        </label>
        
        <input v-model="price" type="number" min="0" step="1" placeholder="Entry price USD (optional, info only)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none" />
        
        <label class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3 cursor-pointer">
          <div>
            <p class="font-black text-sm">Also post on Vibes</p>
            <p class="text-xs text-slate-500">Appears in the main Vibes feed</p>
          </div>
          <input v-model="crossPost" type="checkbox" class="h-5 w-5 accent-violet-600 shrink-0" />
        </label>
        
        <button @click="savePost" class="btn btn-primary w-full py-3">Post to U Vibe</button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '../ui/Modal.vue';
import { UVIBE_CAMPUSES, UVIBE_CATS, getUser } from '../MockDataStore';

const emit = defineEmits(['postCreated']);

const user = getUser();
const modalRef = ref(null);

const campus = ref(UVIBE_CAMPUSES[0][0]);
const category = ref('campus-life');
const title = ref('');
const desc = ref('');
const price = ref('');
const imageUrl = ref(null);
const crossPost = ref(true);

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

const onImageSelected = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  imageUrl.value = URL.createObjectURL(file);
};

const savePost = () => {
  if (!title.value.trim()) {
    showToast('Add a title');
    return;
  }
  
  const catLabel = (UVIBE_CATS.find(c => c[0] === category.value) || [, 'Campus Life'])[1];
  
  const newPost = {
    id: Date.now(),
    org: user.name,
    verified: false,
    group: 'You',
    campus: campus.value,
    time: 'Just now',
    category: category.value,
    tagLabel: catLabel,
    image: imageUrl.value,
    title: title.value,
    price: Number(price.value) || 0,
    desc: desc.value,
    likes: 0,
    comments: 0,
    onVibes: crossPost.value
  };
  
  emit('postCreated', newPost);
  close();
  showToast("Post to U Vibe & Vibes")
  // Reset fields
  title.value = '';
  desc.value = '';
  price.value = '';
  imageUrl.value = null;
  crossPost.value = true;
};
const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};
</script>
