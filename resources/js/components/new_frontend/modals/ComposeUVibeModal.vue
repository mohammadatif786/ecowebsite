<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div class="-m-1">
      <!-- Header -->
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
          <button @click="close" class="shrink-0 text-white/80 hover:text-white transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
      </div>

      <div class="p-5 space-y-5 max-h-[80vh] overflow-y-auto hide-scroll">
        <!-- Post As Section -->
        <div class="space-y-2">
          <p class="text-[11px] font-black text-slate-400 uppercase">Post as</p>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="postAsType = 'myself'"
                    :class="['p-3 rounded-2xl border text-left font-black text-sm transition flex items-center gap-2',
                             postAsType === 'myself' ? 'border-2 border-violet-600 bg-violet-50/50 text-violet-900 shadow-sm' : 'border-slate-200 text-slate-700 hover:bg-slate-50']">
              <span>🙋</span> Myself
            </button>
            <button type="button" @click="postAsType = 'organization'"
                    :class="['p-3 rounded-2xl border text-left font-black text-sm transition flex items-center gap-2',
                             postAsType === 'organization' ? 'border-2 border-violet-600 bg-violet-50/50 text-violet-900 shadow-sm' : 'border-slate-200 text-slate-700 hover:bg-slate-50']">
              <span>🏛️</span> An organization
            </button>
          </div>

          <!-- Organization Select / Add Panel -->
          <div v-if="postAsType === 'organization'" class="mt-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
            <div v-if="allPublishers.length" class="flex flex-wrap gap-2">
              <button v-for="org in allPublishers" :key="org.id || org.name"
                      @click="selectedOrg = org"
                      :class="['chip font-bold px-3 py-1.5 transition', (selectedOrg?.name === org.name) ? 'bg-violet-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200']">
                🏛️ {{ org.name }}
              </button>
            </div>

            <!-- Add Custom Organization -->
            <div class="pt-2 border-t border-slate-200 space-y-2.5">
              <p class="text-xs font-black text-slate-700">Posting for a union, club or committee instead? Add it:</p>
              <div class="grid sm:grid-cols-2 gap-2">
                <input v-model="newOrgName" placeholder="Organization name" class="w-full bg-white rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold outline-none" />
                <select v-model="newOrgType" class="w-full bg-white rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold outline-none">
                  <option value="Student Union">Student Union</option>
                  <option value="University (official)">University (official)</option>
                  <option value="Club / Society">Club / Society</option>
                  <option value="Faculty / Department">Faculty / Department</option>
                  <option value="Event Committee">Event Committee</option>
                  <option value="Sports Team">Sports Team</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <input v-model="newOrgRole" placeholder="Your role (optional) — e.g. President, PR Chair" class="w-full bg-white rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold outline-none" />
              <button type="button" @click="createCustomPublisher" :disabled="creatingOrg || !newOrgName.trim()"
                      class="btn btn-primary w-full py-2 text-xs font-black">
                <span v-if="creatingOrg">Adding...</span>
                <span v-else>Add Organization</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Posting To Section -->
        <div class="space-y-1.5">
          <p class="text-[11px] font-black text-slate-400 uppercase">Posting to</p>
          <div @click="openUniversityModal"
               class="p-3.5 rounded-2xl border-2 border-violet-500 bg-violet-50/30 flex items-center justify-between cursor-pointer hover:bg-violet-50/60 transition shadow-sm">
            <div class="flex items-center gap-2.5">
              <span class="text-base">🏫</span>
              <span class="font-black text-sm text-violet-900">{{ selectedCampus || 'Select a university...' }}</span>
            </div>
            <span class="text-xs font-bold text-violet-600 underline">Change</span>
          </div>
          <p class="text-[11px] font-bold text-slate-400">
            Attended more than one school? Add them under My universities in your profile.
          </p>
        </div>

        <!-- Type Selector -->
        <div class="space-y-1.5">
          <p class="text-[11px] font-black text-slate-400 uppercase">Type</p>
          <select v-model="category" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none bg-white">
            <option v-for="[id, label] in UVIBE_CATS.filter(c => c[0] !== 'all')" :key="id" :value="id">{{ label }}</option>
          </select>
        </div>

        <!-- Title Input -->
        <input v-model="title" placeholder="Title (e.g. Campus Culture Night)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none" />

        <!-- Text Card Mode Toggle or Photo Upload Area -->
        <div class="space-y-3">
          <!-- Text Card Mode Box -->
          <div v-if="isTextMode" class="space-y-3">
            <div class="w-full h-72 rounded-3xl p-6 flex items-center justify-center text-center shadow-lg transition-all duration-300 relative overflow-hidden"
                 :style="{ background: selectedTextBg }">
              <textarea
                v-model="textVibeContent"
                rows="4"
                placeholder="Your text will appear here..."
                :class="['w-full bg-transparent text-white placeholder:text-white/70 focus:bg-transparent text-xl md:text-2xl text-center outline-none resize-none leading-relaxed', currentFont.class]"
              ></textarea>
            </div>

            <!-- Color Circles & Font Family Cycle Button -->
            <div class="flex items-center justify-between flex-wrap gap-3 pt-1">
              <div class="flex items-center gap-2 flex-wrap">
                <button
                  v-for="(bg, idx) in textBackgrounds"
                  :key="idx"
                  @click="selectedTextBg = bg"
                  class="w-7 h-7 rounded-full border-2 transition-transform hover:scale-110 shadow-sm"
                  :style="{ background: bg, borderColor: selectedTextBg === bg ? '#fff' : 'transparent' }"
                  :class="{ 'ring-2 ring-violet-500 scale-110': selectedTextBg === bg }"
                ></button>
              </div>
              <button type="button" @click="toggleFont" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-black text-xs px-3.5 py-2 rounded-xl transition shadow-xs">
                {{ currentFont.name }}
              </button>
            </div>

            <textarea v-model="desc" rows="3" placeholder="What's happening on campus?" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none"></textarea>

            <button type="button" @click="isTextMode = false" class="w-full rounded-2xl border-2 border-dashed border-emerald-300 text-emerald-700 font-black py-3 flex items-center justify-center gap-2 hover:bg-emerald-50 transition">
              <i data-lucide="type" class="w-4 h-4"></i>Remove Text Card
            </button>
          </div>

          <!-- Photo Upload & Carousel Area -->
          <div v-else class="space-y-3">
            <div v-if="media.length" class="flex flex-wrap gap-3 items-center">
              <div v-for="(item, i) in media" :key="item.url || i" class="relative w-24 h-24 rounded-2xl overflow-hidden shadow-sm bg-slate-100">
                <img :src="item.url" class="w-full h-full object-cover" />
                <span class="absolute bottom-1 left-1 bg-black/60 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ i + 1 }}</span>
                <button type="button" @click="removeMedia(i)" class="absolute top-1.5 right-1.5 h-6 w-6 rounded-full bg-rose-500 text-white grid place-items-center shadow hover:bg-rose-600 transition">
                  <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
              </div>
              <label class="w-24 h-24 rounded-2xl border-2 border-dashed border-violet-300 text-violet-600 grid place-items-center cursor-pointer hover:bg-violet-50 transition">
                <input type="file" accept="image/*,video/*" multiple class="hidden" @change="onImagesSelected" />
                <i data-lucide="plus" class="w-6 h-6"></i>
              </label>
            </div>

            <label v-else class="w-full rounded-2xl border-2 border-dashed border-violet-300 text-violet-600 font-black py-3.5 flex items-center justify-center gap-2 cursor-pointer hover:bg-violet-50 transition">
              <input type="file" accept="image/*,video/*" multiple class="hidden" @change="onImagesSelected" />
              <i data-lucide="image-plus" class="w-4 h-4"></i>Add photos (select multiple for a carousel)
            </label>

            <button type="button" @click="isTextMode = true; media = []" class="w-full rounded-2xl border-2 border-dashed border-emerald-300 text-emerald-700 font-black py-3 flex items-center justify-center gap-2 hover:bg-emerald-50 transition">
              <i data-lucide="type" class="w-4 h-4"></i>Add Text Card instead of a photo
            </button>

            <textarea v-model="desc" rows="3" placeholder="What's happening on campus?" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none"></textarea>
          </div>
        </div>

        <!-- Entry Price & Event Link -->
        <div class="space-y-3">
          <input v-model="price" type="number" min="0" step="1" placeholder="Ticket / entry price USD (optional)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none" />

          <div v-if="userEvents.length" class="space-y-1">
            <select v-model="selectedEventId" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none bg-white">
              <option :value="null">Link to your event in Events (optional)</option>
              <option v-for="ev in userEvents" :key="ev.id" :value="ev.id">{{ ev.title }} ({{ ev.date }})</option>
            </select>
            <p class="text-[10px] font-bold text-slate-400 px-1">
              Tickets are sold through Events. Add a price and people click it on your post to go to Events and buy.
            </p>
          </div>
        </div>

        <!-- Allow Boosts (coin gifts) -->
        <label class="flex items-center justify-between gap-3 rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3.5 cursor-pointer">
          <div class="flex items-center gap-2.5">
            <span class="text-amber-500 text-lg">⚡</span>
            <span class="font-black text-sm text-amber-900">Allow Boosts (coin gifts)</span>
          </div>
          <input v-model="allowBoosts" type="checkbox" class="h-5 w-5 accent-amber-600 shrink-0 cursor-pointer" />
        </label>

        <!-- Tag a product or event -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span>🛍️</span>
              <span class="font-black text-sm text-slate-800">Tag a product or 🎟️ event</span>
            </div>
            <button type="button" @click="openTagModal" class="btn btn-primary px-4 py-1.5 rounded-xl text-xs font-black shadow-sm">
              Tag
            </button>
          </div>
          <p class="text-[11px] font-bold text-slate-400 leading-relaxed">
            Make your U Vibe post shoppable — classmates buy the product or grab tickets right from it, and you earn commission, same as Vibes. Selling a textbook or something else? <a href="#" @click.prevent="router.visit(route('new_frontend.marketplace'))" class="text-violet-600 underline">List it on the Marketplace</a> first, then tag it here.
          </p>

          <!-- Selected Item Display -->
          <div v-if="taggedItem" class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100 mt-2">
            <img :src="taggedItem.image || 'https://picsum.photos/200'" class="w-10 h-10 rounded-lg object-cover shadow-sm shrink-0" />
            <div class="flex-1 min-w-0">
              <p class="text-xs font-black text-slate-800 truncate">{{ taggedItem.title }}</p>
              <p class="text-[10px] font-bold text-emerald-600">{{ money(taggedItem.price) }} · Earn commission</p>
            </div>
            <button type="button" @click="taggedItem = null" class="p-1.5 rounded-full hover:bg-slate-200 text-slate-400 transition">
              <i data-lucide="x" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- Also post on Vibes -->
        <label class="flex items-center justify-between gap-3 rounded-2xl bg-violet-50 border border-violet-100 px-4 py-3.5 cursor-pointer">
          <div>
            <p class="font-black text-sm text-violet-900">Also post on Vibes</p>
            <p class="text-xs text-violet-700/80">Appears in the main Vibes feed</p>
          </div>
          <input v-model="crossPost" type="checkbox" class="h-5 w-5 accent-violet-600 shrink-0 cursor-pointer" />
        </label>

        <!-- Family-Friendly Rules Callout -->
        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-start gap-2.5">
          <span class="text-lg">🌴</span>
          <p class="text-xs font-bold text-amber-900 leading-relaxed">
            Family-friendly only. Nudity or pornography means your coins are seized and your account is deleted. <a href="#" class="underline font-black">Community rules</a>
          </p>
        </div>

        <button type="button" @click="savePost" :disabled="submitting" class="btn btn-primary w-full py-3.5 text-base font-black shadow-lg shadow-violet-600/20 disabled:opacity-50">
          <span v-if="submitting">Posting...</span>
          <span v-else>Post to U Vibe</span>
        </button>
      </div>
    </div>

    <!-- Tag Product/Event Modal -->
    <VibeTagProductModal ref="tagModalRef" :items="affiliateItems" @selected="onItemSelected" />
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '../ui/Modal.vue';
import VibeTagProductModal from './VibeTagProductModal.vue';
import { UVIBE_CATS, getUser } from '../MockDataStore';

const props = defineProps({
  affiliateItems: { type: Array, default: () => [] },
  publishers: { type: Object, default: () => ({ organizations: [], custom: [] }) },
  userEvents: { type: Array, default: () => [] }
});

const emit = defineEmits(['postCreated', 'openUniversityModal']);

const page = usePage();
const user = computed(() => page.props.auth?.user || getUser());
const modalRef = ref(null);
const tagModalRef = ref(null);

const postAsType = ref('myself');
const selectedOrg = ref(null);
const newOrgName = ref('');
const newOrgType = ref('Student Union');
const newOrgRole = ref('');
const creatingOrg = ref(false);

const customPublishers = ref([...(props.publishers?.custom || [])]);
const organizationPublishers = ref([...(props.publishers?.organizations || [])]);

const allPublishers = computed(() => [...organizationPublishers.value, ...customPublishers.value]);

const selectedCampus = computed(() => {
  try {
    const saved = localStorage.getItem('lk_my_universities');
    const parsed = saved ? JSON.parse(saved) : [];
    if (parsed.length > 0) return parsed[0].name;
  } catch (e) {}
  return '';
});

const category = ref('campus-life');
const title = ref('');
const desc = ref('');
const price = ref('');
const media = ref([]);
const allowBoosts = ref(true);
const taggedItem = ref(null);
const crossPost = ref(true);
const selectedEventId = ref(null);
const submitting = ref(false);

const isTextMode = ref(false);
const textVibeContent = ref('');
const selectedTextBg = ref('linear-gradient(135deg, #a855f7, #ec4899)');

const fonts = [
  { name: 'Aa Classic', class: 'font-serif italic' },
  { name: 'Aa Modern', class: 'font-sans font-bold' },
  { name: 'Aa Mono', class: 'font-mono' },
  { name: 'Aa Script', class: 'font-serif tracking-wide' },
  { name: 'Aa Heavy', class: 'font-black tracking-tighter' },
];
const fontIndex = ref(0);
const currentFont = computed(() => fonts[fontIndex.value]);
const toggleFont = () => { fontIndex.value = (fontIndex.value + 1) % fonts.length; };

const textBackgrounds = [
  'linear-gradient(135deg, #a855f7, #ec4899)',
  'linear-gradient(135deg, #f97316, #ea580c)',
  '#0d9488',
  '#1e293b',
  '#db2777',
  '#7c3aed',
  '#d97706',
  '#2563eb'
];

const open = () => {
  // Check if university is set first!
  if (!selectedCampus.value) {
    emit('openUniversityModal');
    return;
  }

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

const openUniversityModal = () => {
  emit('openUniversityModal');
};

const createCustomPublisher = async () => {
  if (!newOrgName.value.trim()) return;
  try {
    creatingOrg.value = true;
    const res = await axios.post(route('new_frontend.vibes.custom-publishers.store'), {
      name: newOrgName.value.trim(),
      type: newOrgType.value,
    });
    const created = res.data;
    customPublishers.value.push(created);
    selectedOrg.value = created;
    newOrgName.value = '';
    newOrgRole.value = '';
    showToast('✅ Organization added!');
  } catch (e) {
    showToast('Error adding organization');
  } finally {
    creatingOrg.value = false;
  }
};

const onImagesSelected = (e) => {
  const files = Array.from(e.target.files || []);
  files.forEach(file => {
    media.value.push({ file, url: URL.createObjectURL(file) });
  });
  e.target.value = '';
};

const removeMedia = (i) => {
  if (media.value[i]?.url) URL.revokeObjectURL(media.value[i].url);
  media.value.splice(i, 1);
};

const openTagModal = () => {
  if (tagModalRef.value) tagModalRef.value.open();
};

const onItemSelected = (item) => {
  taggedItem.value = item;
  if (item.kind === 'event') {
    selectedEventId.value = item.id;
  }
};

const savePost = async () => {
  const finalDesc = isTextMode.value ? textVibeContent.value : desc.value;
  if (!title.value.trim() && !finalDesc.trim()) {
    showToast('Add a title or description');
    return;
  }

  if (!selectedCampus.value) {
    showToast('Please set your university first');
    emit('openUniversityModal');
    return;
  }

  try {
    submitting.value = true;
    const formData = new FormData();

    const publisherType = postAsType.value === 'myself' ? 'user' : (selectedOrg.value?.type === 'organization' ? 'organization' : 'custom_publisher');
    const publisherId = postAsType.value === 'myself' ? user.value.id : (selectedOrg.value?.id || user.value.id);

    formData.append('publisher_type', publisherType);
    formData.append('publisher_id', publisherId);
    formData.append('title', title.value);
    formData.append('caption', finalDesc);
    formData.append('category', category.value);
    formData.append('campus', selectedCampus.value);
    formData.append('price', Number(price.value) || 0);
    formData.append('is_uvibe', '1');
    formData.append('allow_coin_gifts', allowBoosts.value ? '1' : '0');

    if (isTextMode.value) {
      formData.append('text_bg', selectedTextBg.value);
      formData.append('text_font', currentFont.value.name);
    }

    media.value.forEach((m, idx) => {
      formData.append(`media[${idx}]`, m.file);
      formData.append(`media_sources[${idx}]`, 'upload');
    });

    if (taggedItem.value?.kind === 'product') {
      formData.append('product_ids[0]', taggedItem.value.id);
    }
    if (selectedEventId.value) {
      formData.append('event_ids[0]', selectedEventId.value);
    }

    const res = await axios.post(route('new_frontend.vibes.store'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    emit('postCreated', res.data.data || res.data);
    close();
    showToast("✅ Posted to U Vibe!");

    // Reset
    title.value = '';
    desc.value = '';
    price.value = '';
    media.value = [];
    isTextMode.value = false;
    textVibeContent.value = '';
    taggedItem.value = null;
    selectedEventId.value = null;
    allowBoosts.value = true;
    crossPost.value = true;
  } catch (e) {
    console.error('Failed to post to U Vibe', e);
    showToast(e.response?.data?.message || 'Failed to post to U Vibe');
  } finally {
    submitting.value = false;
  }
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>
