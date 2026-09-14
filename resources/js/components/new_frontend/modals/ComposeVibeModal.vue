<template>
  <Modal ref="modalRef">
    <div class="flex items-center justify-between border-b p-4"><h3 class="text-lg font-black">New Vibe</h3><button @click="close">✕</button></div>
    <div class="space-y-3 p-4">
      <p class="text-xs font-black uppercase text-slate-400">Post as</p>
      <div class="grid grid-cols-2 gap-2">
        <button class="rounded-2xl border-2 py-2.5 font-black" :class="postAs === 'me' ? 'border-lkblue bg-blue-50' : 'border-slate-200'" @click="postAs = 'me'">👤 Myself</button>
        <button class="rounded-2xl border-2 py-2.5 font-black" :class="postAs === 'entity' ? 'border-lkblue bg-blue-50' : 'border-slate-200'" @click="postAs = 'entity'">🏢 Organization / Group</button>
      </div>
      <select v-if="postAs === 'entity'" v-model="selectedPublisher" class="w-full rounded-xl border p-3">
        <option value="" disabled>Select an organization or group</option>
        <optgroup label="Organizations"><option v-for="p in publishers.organizations" :key="`o${p.id}`" :value="`organization:${p.id}`">{{ p.name }}</option></optgroup>
        <optgroup label="Groups"><option v-for="p in publishers.groups" :key="`g${p.id}`" :value="`group:${p.id}`">{{ p.name }}</option></optgroup>
      </select>
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
      <label class="flex justify-between rounded-2xl bg-amber-50 p-3 font-bold">🪙 Allow Big Up coin gifts <input v-model="allowGifts" type="checkbox"/></label>
      <p class="text-xs text-slate-500">Product and event IDs are supported by the API; the existing picker can send them as product_ids and event_ids.</p>
      <p v-if="error" class="text-sm font-semibold text-red-600">{{ error }}</p>
      <button :disabled="busy" class="btn btn-primary w-full py-3 disabled:opacity-60" @click="submit">{{ busy ? 'Sharing…' : 'Share Vibe' }}</button>
    </div>
  </Modal>
</template>

<script setup>
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { getUser } from '../MockDataStore';
import Modal from '../ui/Modal.vue';

const props = defineProps({ publishers: { type: Object, default: () => ({ organizations: [], groups: [] }) } });
const emit = defineEmits(['postCreated']);
const user = usePage().props.auth?.user || getUser();
const modalRef = ref(); const postAs = ref('me'); const selectedPublisher = ref(''); const caption = ref('');
const location = ref(`${user.city}, ${user.country}`); const allowGifts = ref(true); const media = ref([]); const busy = ref(false); const error = ref('');
const pickers = [{ label: '📷 Take Photo', accept: 'image/*', capture: 'environment', source: 'camera' }, { label: '🎥 Record Video', accept: 'video/*', capture: 'environment', source: 'video_recording' }, { label: '🖼️ Gallery', accept: 'image/*,video/*', multiple: true, source: 'gallery' }];
const open = () => modalRef.value?.open(); const close = () => modalRef.value?.close(); defineExpose({ open, close });
const selectMedia = (event, source) => { Array.from(event.target.files || []).forEach(file => media.value.push({ file, source, video: file.type.startsWith('video/'), url: URL.createObjectURL(file) })); event.target.value = ''; };
const removeMedia = i => { URL.revokeObjectURL(media.value[i].url); media.value.splice(i, 1); };
const submit = async () => {
  error.value = ''; if (!caption.value.trim() && !media.value.length) { error.value = 'Add a caption or media.'; return; }
  const [type, id] = postAs.value === 'me' ? ['user', user.id] : selectedPublisher.value.split(':'); if (!id) { error.value = 'Select a publisher.'; return; }
  const form = new FormData(); Object.entries({ publisher_type: type, publisher_id: id, caption: caption.value, location_name: location.value, allow_coin_gifts: allowGifts.value ? 1 : 0, visibility: 'public' }).forEach(([k, v]) => form.append(k, v));
  media.value.forEach((item, i) => { form.append(`media[${i}]`, item.file); form.append(`media_sources[${i}]`, item.source); }); busy.value = true;
  try { const vibe = (await axios.post(route('new_frontend.vibes.store'), form)).data.data; emit('postCreated', { id: vibe.id, handle: vibe.publisher.name, avatar: vibe.publisher.avatar || user.avatar, location: vibe.location.name, kind: vibe.media[0]?.type === 'video' ? 'reel' : 'photo', media: vibe.media[0]?.url, caption: vibe.caption, likes: 0, comments: 0, bigup: 0, shoppable: vibe.products.length + vibe.events.length > 0, realVideo: vibe.media[0]?.type === 'video' }); media.value.forEach(x => URL.revokeObjectURL(x.url)); media.value = []; caption.value = ''; close(); }
  catch (e) { error.value = Object.values(e.response?.data?.errors || {})[0]?.[0] || e.response?.data?.message || 'Unable to share this vibe.'; } finally { busy.value = false; }
};
</script>
