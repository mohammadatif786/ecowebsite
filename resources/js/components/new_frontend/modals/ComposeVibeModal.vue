<template>
  <Modal ref="modalRef">
    <div class="flex items-center justify-between border-b p-4"><h3 class="text-lg font-black">New Vibe</h3><button @click="close">✕</button></div>
    <div class="space-y-4 p-4">
      <!-- Tab Selection -->
      <div class="flex bg-slate-100 p-1 rounded-2xl">
        <button
          @click="selectMe"
          class="flex-1 py-2.5 rounded-xl font-black text-sm transition-all"
          :class="postAs === 'me' ? 'bg-white text-lkblue shadow-sm' : 'text-slate-500 hover:text-slate-700'"
        >
          👤 Myself
        </button>
        <button
          @click="postAs = 'entity'"
          class="flex-1 py-2.5 rounded-xl font-black text-sm transition-all"
          :class="postAs === 'entity' ? 'bg-white text-lkblue shadow-sm' : 'text-slate-500 hover:text-slate-700'"
        >
          🏢 Organization / Group
        </button>
      </div>

      <!-- Organization / Group Tab Content -->
      <div v-if="postAs === 'entity'" class="space-y-4 animate-in fade-in slide-in-from-top-1 duration-200">
        <!-- Previously Added Chips Displayed Above Input -->
        <div v-if="allPublishers.length" class="flex flex-wrap gap-2 border-b border-slate-100 pb-3">
          <button
            v-for="p in allPublishers"
            :key="p.id + p.type"
            @click="selectPublisher(p)"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border-2 font-bold text-xs transition"
            :class="isSelected(p) ? 'border-lkblue bg-blue-50 text-lkblue' : 'border-slate-200 text-slate-500 hover:border-slate-300'"
          >
            <span v-if="p.type === 'organization'">🏢</span>
            <span v-else-if="p.type === 'group'">👥</span>
            <span>{{ p.name }}</span>
          </button>
        </div>

        <!-- Add New Publisher Form -->
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-3">
          <div class="flex gap-2">
            <input
              v-model="customName"
              placeholder="Organization or group name"
              class="flex-1 rounded-xl border-slate-200 p-2.5 text-sm outline-none focus:border-lkblue transition"
            />
            <select v-model="customType" class="rounded-xl border-slate-200 p-2.5 text-sm outline-none focus:border-lkblue transition bg-white">
              <option value="organization">Organization</option>
              <option value="group">Group</option>
            </select>
            <button
              @click="addCustomPublisher"
              class="btn btn-primary px-5 py-2.5 rounded-xl text-sm font-black whitespace-nowrap disabled:opacity-50"
              :disabled="!customName.trim() || addingPublisher"
            >
              Add
            </button>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border-2 border-dashed p-4">
        <div class="grid grid-cols-3 gap-2">
          <label v-for="picker in pickers" :key="picker.source" class="cursor-pointer rounded-xl bg-slate-50 p-3 text-center text-xs font-bold">
            <input type="file" :accept="picker.accept" :capture="picker.capture" :multiple="picker.multiple" class="hidden" @change="selectMedia($event, picker.source)" />{{ picker.label }}
          </label>
        </div>
        <div v-if="media.length" class="mt-3 grid grid-cols-2 gap-2">
          <div v-for="(item, i) in media" :key="item.url" class="relative overflow-hidden rounded-xl bg-black">
            <video v-if="item.video" :src="item.url" controls class="h-36 w-full object-contain"/><img v-else :src="item.url" class="h-36 w-full object-cover"/>
            <button class="absolute right-2 top-2 rounded-full bg-black/60 px-2 py-1 text-white" @click="removeMedia(i)">✕</button>
          </div>
        </div>
      </div>
      <textarea v-model="caption" maxlength="2200" rows="3" placeholder="Write a caption… #carnival #island" class="w-full rounded-2xl border p-3"/>
      <input v-model="location" placeholder="📍 Add location" class="w-full rounded-2xl border p-3"/>

      <!-- Tagging Section -->
      <div class="rounded-2xl border border-slate-100 bg-white p-4 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-lg">🛍️</span>
            <p class="text-sm font-black text-slate-800">Tag a product or 🎟️ event</p>
          </div>
          <button @click="openTagModal" class="btn btn-primary px-4 py-1.5 rounded-xl text-xs font-black">
            Tag
          </button>
        </div>

        <p v-if="!taggedItem" class="text-[11px] font-bold text-slate-400 leading-relaxed">
          Make your vibe shoppable — viewers buy the product or grab tickets right from your post, and you earn commission.
        </p>

        <!-- Selected Item Display -->
        <div v-else class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-100 animate-in zoom-in-95 duration-200">
          <img :src="taggedItem.image || 'https://picsum.photos/200'" class="w-10 h-10 rounded-lg object-cover shadow-sm shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-black text-slate-800 truncate">{{ taggedItem.title }}</p>
            <p class="text-[10px] font-bold text-emerald-600">{{ money(taggedItem.price) }} · Earn commission</p>
          </div>
          <button @click="taggedItem = null" class="p-1.5 rounded-full hover:bg-slate-200 text-slate-400 transition">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>
      </div>

      <label class="flex justify-between rounded-2xl bg-amber-50 p-3 font-bold">🪙 Allow Big Up coin gifts <input v-model="allowGifts" type="checkbox"/></label>
      <p v-if="error" class="text-sm font-semibold text-red-600">{{ error }}</p>
      <button :disabled="busy" class="btn btn-primary w-full py-3 disabled:opacity-60" @click="submit">{{ busy ? 'Sharing…' : 'Share Vibe' }}</button>
    </div>

    <!-- Tag Product/Event Modal -->
    <VibeTagProductModal ref="tagModalRef" :items="affiliateItems" @selected="onItemSelected" />
  </Modal>
</template>

<script setup>
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';
import { getUser } from '../MockDataStore';
import Modal from '../ui/Modal.vue';
import VibeTagProductModal from './VibeTagProductModal.vue';

const props = defineProps({
  publishers: { type: Object, default: () => ({ organizations: [], groups: [], custom: [] }) },
  affiliateItems: { type: Array, default: () => [] }
});

const emit = defineEmits(['postCreated']);
const user = usePage().props.auth?.user || getUser();
const modalRef = ref(); const tagModalRef = ref();
const postAs = ref('me'); const selectedPublisher = ref(null); const caption = ref('');
const taggedItem = ref(null);
const customName = ref(''); const customType = ref('organization'); const addingPublisher = ref(false);
const localCustomPublishers = ref([...props.publishers.custom]);

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const allPublishers = computed(() => {
  return [
    ...props.publishers.organizations,
    ...props.publishers.groups,
    ...localCustomPublishers.value
  ];
});

const selectMe = () => {
  postAs.value = 'me';
  selectedPublisher.value = null;
};

const selectPublisher = (p) => {
  postAs.value = 'entity';
  selectedPublisher.value = p;
};

const isSelected = (p) => {
  return postAs.value === 'entity' &&
    selectedPublisher.value?.id === p.id &&
    selectedPublisher.value?.type === p.type;
};

const addCustomPublisher = async () => {
  if (!customName.value.trim() || addingPublisher.value) return;
  addingPublisher.value = true;
  try {
    const response = await axios.post(route('new_frontend.vibes.custom-publishers.store'), {
      name: customName.value,
      type: customType.value
    });
    localCustomPublishers.value.push(response.data);
    selectPublisher(response.data);
    customName.value = '';
  } catch (e) {
    console.error('Failed to add publisher', e);
  } finally {
    addingPublisher.value = false;
  }
};

const location = ref(`${user.city}, ${user.country}`); const allowGifts = ref(true); const media = ref([]); const busy = ref(false); const error = ref('');
const pickers = [{ label: '📷 Take Photo', accept: 'image/*', capture: 'environment', source: 'camera' }, { label: '🎥 Record Video', accept: 'video/*', capture: 'environment', source: 'video_recording' }, { label: '🖼️ Gallery', accept: 'image/*,video/*', multiple: true, source: 'gallery' }];
const open = () => modalRef.value?.open(); const close = () => modalRef.value?.close(); defineExpose({ open, close });
const selectMedia = (event, source) => { Array.from(event.target.files || []).forEach(file => media.value.push({ file, source, video: file.type.startsWith('video/'), url: URL.createObjectURL(file) })); event.target.value = ''; };
const removeMedia = i => { URL.revokeObjectURL(media.value[i].url); media.value.splice(i, 1); };
const openTagModal = () => tagModalRef.value?.open();
const onItemSelected = (item) => { taggedItem.value = item; };

const submit = async () => {
  error.value = ''; if (!caption.value.trim() && !media.value.length) { error.value = 'Add a caption or media.'; return; }

  let type = 'user';
  let id = user.id;

  if (postAs.value === 'entity') {
    if (!selectedPublisher.value) { error.value = 'Select a publisher.'; return; }
    id = selectedPublisher.value.id;

    if (selectedPublisher.value.type === 'organization' || selectedPublisher.value.type === 'group') {
      const isCustom = localCustomPublishers.value.some(cp => cp.id === id && cp.type === selectedPublisher.value.type);
      type = isCustom ? 'custom_publisher' : selectedPublisher.value.type;
    }
  }

  const form = new FormData();
  Object.entries({
    publisher_type: type,
    publisher_id: id,
    caption: caption.value,
    location_name: location.value,
    allow_coin_gifts: allowGifts.value ? 1 : 0,
    visibility: 'public'
  }).forEach(([k, v]) => form.append(k, v));

  if (taggedItem.value) {
    if (taggedItem.value.kind === 'product') {
      form.append('product_ids[0]', taggedItem.value.id);
    } else if (taggedItem.value.kind === 'event') {
      form.append('event_ids[0]', taggedItem.value.id);
    }
  }

  media.value.forEach((item, i) => { form.append(`media[${i}]`, item.file); form.append(`media_sources[${i}]`, item.source); }); busy.value = true;
  try {
    const vibe = (await axios.post(route('new_frontend.vibes.store'), form)).data.data;
    emit('postCreated', {
      id: vibe.id,
      created_by: vibe.created_by || user.id,
      handle: vibe.publisher.name,
      publisher_type: vibe.publisher.type, // Map correct type
      avatar: vibe.publisher.avatar || user.avatar,
      location: vibe.location.name,
      media: vibe.media.map(m => ({
        id: m.id,
        type: m.type,
        url: m.url,
        thumbnail: m.thumbnail_url
      })),
      caption: vibe.caption,
      likes: 0,
      comments: 0,
      bigup: 0,
      shoppable: vibe.products.length + vibe.events.length > 0,
      tag: (vibe.products[0] || vibe.events[0]) ? {
        kind: vibe.products[0] ? 'product' : 'event',
        id: (vibe.products[0] || vibe.events[0]).id,
        title: (vibe.products[0] || vibe.events[0]).name || (vibe.products[0] || vibe.events[0]).title,
        price: (vibe.products[0] || vibe.events[0]).price,
        image: (vibe.products[0] || vibe.events[0]).cover_image || (vibe.products[0] || vibe.events[0]).featured_image,
        seller: (vibe.products[0] || vibe.events[0]).user?.name
      } : null
    });
    media.value.forEach(x => URL.revokeObjectURL(x.url));
    media.value = [];
    caption.value = '';
    taggedItem.value = null;
    close();
  }
  catch (e) { error.value = Object.values(e.response?.data?.errors || {})[0]?.[0] || e.response?.data?.message || 'Unable to share this vibe.'; } finally { busy.value = false; }
};
</script>
