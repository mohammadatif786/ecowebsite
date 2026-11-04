<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div class="bg-lkink text-white px-5 py-4 flex items-center justify-between -m-1 rounded-t-[20px]">
      <h3 class="text-xl font-black">Filters</h3>
      <button @click="close"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>

    <div class="p-5 space-y-6 max-h-[72vh] overflow-y-auto">
      <!-- Age range -->
      <div>
        <div class="flex justify-between items-center mb-3">
          <p class="font-bold">Age Range</p>
          <span class="text-sm font-black text-lkblue2" style="color:var(--lk-blue)">{{ draft.ageMin }} - {{ draft.ageMax }}</span>
        </div>
        
        <SliderRoot v-model="ageRange" class="relative flex h-5 touch-none items-center select-none mb-2" :max="80" :min="15" :step="1">
            <SliderTrack class="bg-slate-200 relative h-[4px] grow rounded-full">
                <SliderRange class="absolute h-full rounded-full" style="background-color: var(--lk-blue)" />
            </SliderTrack>
            <SliderThumb v-for="thumb in 2" :key="thumb"
                class="block h-5 w-5 rounded-full bg-white shadow-md border border-slate-200 focus:outline-none" />
        </SliderRoot>
      </div>

      <!-- Distance range -->
      <div>
        <div class="flex justify-between items-center mb-3">
          <p class="font-bold">Distance (km)</p>
          <span class="text-sm font-black text-lkblue2" style="color:var(--lk-blue)">
            {{ draft.distMin }} - {{ draft.distMax >= 20000 ? 'Whole World' : draft.distMax + ' km' }}
          </span>
        </div>

        <SliderRoot v-model="distRange" class="relative flex h-5 touch-none items-center select-none mb-2" :max="20000" :min="0" :step="1">
            <SliderTrack class="bg-slate-200 relative h-[4px] grow rounded-full">
                <SliderRange class="absolute h-full rounded-full" style="background-color: var(--lk-blue)" />
            </SliderTrack>
            <SliderThumb v-for="thumb in 2" :key="thumb"
                class="block h-5 w-5 rounded-full bg-white shadow-md border border-slate-200 focus:outline-none" />
        </SliderRoot>
      </div>

      <!-- Gender -->
      <div>
        <p class="font-bold mb-2">Search Preferences</p>
        <div class="grid grid-cols-3 bg-slate-100 rounded-2xl p-1 gap-1">
          <button v-for="g in ['both', 'm', 'f']" :key="g" @click="draft.gender = g" :class="['rounded-xl py-2.5 text-sm font-black transition', draft.gender === g ? 'bg-white shadow text-lkblue2' : 'text-slate-500']">
            {{ g === 'both' ? 'Everyone' : g === 'm' ? 'Men' : 'Women' }}
          </button>
        </div>
      </div>

      <!-- Country -->
      <div class="pb-4 border-b border-slate-100">
        <p class="font-bold mb-2">Choose a Caribbean or Latin American Country to See People from</p>
        <div class="flex items-center gap-4 mb-3 text-sm font-bold">
          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="lkCtryMode" :value="'all'" v-model="draft.countryMode" /> All
          </label>
          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="lkCtryMode" :value="'other'" v-model="draft.countryMode" /> Other
          </label>
        </div>
        <select v-if="draft.countryMode === 'other'" v-model="draft.country" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-lkblue">
          <option value="">Select</option>
          <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
        </select>
      </div>

      <!-- Interests -->
      <div>
        <p class="font-bold mb-2">Interests</p>
        <div class="flex flex-wrap gap-2">
          <button v-for="opt in LK_INTERESTS" :key="opt" @click="toggleArr('interests', opt)" :class="['chip', draft.interests.includes(opt) ? 'on bg-lkblue text-white border-lkblue' : '']">
            {{ opt }}
          </button>
        </div>
      </div>

      <!-- Languages -->
      <div>
        <p class="font-bold mb-2">Languages I Know</p>
        <div class="flex flex-wrap gap-2">
          <button v-for="opt in LK_LANGUAGES" :key="opt" @click="toggleArr('languages', opt)" :class="['chip', draft.languages.includes(opt) ? 'on bg-lkblue text-white border-lkblue' : '']">
            {{ opt }}
          </button>
        </div>
      </div>

      <!-- Religions -->
      <div>
        <p class="font-bold mb-2">Religion</p>
        <div class="flex flex-wrap gap-2">
          <button v-for="opt in LK_RELIGIONS" :key="opt" @click="toggleArr('religions', opt)" :class="['chip', draft.religions.includes(opt) ? 'on bg-lkblue text-white border-lkblue' : '']">
            {{ opt }}
          </button>
        </div>
      </div>

      <!-- Goals -->
      <div>
        <p class="font-bold mb-2">Relationship Goals</p>
        <div class="flex flex-wrap gap-2">
          <button v-for="opt in LK_STATUS" :key="opt" @click="toggleArr('goals', opt)" :class="['chip', draft.goals.includes(opt) ? 'on bg-lkblue text-white border-lkblue' : '']">
            {{ opt }}
          </button>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-2 pt-2 sticky bottom-0 bg-white border-t border-slate-100">
        <button @click="reset" class="btn btn-ghost flex-1 py-3">Reset</button>
        <button @click="apply" class="btn btn-primary flex-1 py-3">Apply Filters</button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from '../ui/Modal.vue';
import { SliderRoot, SliderTrack, SliderRange, SliderThumb } from 'reka-ui';

const props = defineProps({
  caribbeanCountries: { type: Array, default: () => [] }
});

const emit = defineEmits(['apply']);
const modalRef = ref(null);
const draft = ref({ 
  ageMin: 15, ageMax: 40,
  distMin: 0, distMax: 20000, 
  gender: 'both', 
  countryMode: 'all', country: '',
  interests: [], languages: [], religions: [], goals: []
});

const LK_LANGUAGES = ['English', 'Spanish', 'French', 'Haitian Creole', 'Dutch', 'Papiamento', 'Portuguese', 'Sranan Tongo'];
const LK_INTERESTS = ['Music Festivals', 'Travel', 'Cooking', 'Books', 'Yoga', 'Movies', 'Wine', 'Church Events', 'Liming', 'Carnival / Mas', 'Junkanoo', 'Soca Fêtes', 'Reggae & Dancehall', 'Kompa / Zouk', 'Steel Pan', 'Boat Fêtes', 'Beach Lime / Bonfire', 'Food & Rum Festivals', 'Crop Over', 'J’ouvert', 'Rake & Scrape', 'Salsa Socials', 'Bachata Nights', 'Reggaetón Parties', 'Cumbia & Vallenato', 'Samba Blocos / Pagode', 'Forró Nights', 'Mariachi / Regional Mexicano', 'Día de los Muertos (festivals)', 'Ferias & Street Fairs', 'Latin Food Fairs', 'Folkloric Dance Shows'];
const LK_RELIGIONS = ['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Judaism', 'Sikhism'];
const LK_STATUS = ['Here to party', 'Here to Link Up', 'Here to casual chat', 'Looking for something serious', 'Just Here for Events', 'Here to make friends'];

const countries = computed(() => {
  const seen = new Set();

  return props.caribbeanCountries
    .map((country) => String(country || '').trim())
    .filter((country) => {
      const key = country.toLocaleLowerCase();
      if (!country || seen.has(key)) return false;
      seen.add(key);
      return true;
    })
    .sort((a, b) => a.localeCompare(b));
});

const ageRange = computed({
  get: () => [draft.value.ageMin, draft.value.ageMax],
  set: (val) => { draft.value.ageMin = val[0]; draft.value.ageMax = val[1]; }
});

const distRange = computed({
  get: () => [draft.value.distMin, draft.value.distMax],
  set: (val) => { draft.value.distMin = val[0]; draft.value.distMax = val[1]; }
});

const toggleArr = (field, opt) => {
  const arr = draft.value[field];
  const idx = arr.indexOf(opt);
  if (idx > -1) arr.splice(idx, 1);
  else arr.push(opt);
};

const open = (currentFilter) => {
  draft.value = JSON.parse(JSON.stringify(currentFilter));
  if (!draft.value.countryMode) draft.value.countryMode = 'all';
  if (!draft.value.interests) draft.value.interests = [];
  if (!draft.value.languages) draft.value.languages = [];
  if (!draft.value.religions) draft.value.religions = [];
  if (!draft.value.goals) draft.value.goals = [];
  
  if (modalRef.value) {
    modalRef.value.open();
    if (window.lucide) setTimeout(() => lucide.createIcons(), 50);
  }
};

const close = () => { if (modalRef.value) modalRef.value.close(); };

const reset = () => {
  draft.value = { 
    ageMin: 15, ageMax: 60, distMin: 0, distMax: 20000,
    gender: 'both', countryMode: 'all', country: '',
    interests: [], languages: [], religions: [], goals: []
  };
};

const apply = () => {
  if (draft.value.countryMode === 'all') draft.value.country = '';
  emit('apply', JSON.parse(JSON.stringify(draft.value)));
  close();
  if (window.toast) window.toast('✅ Filters applied');
};

defineExpose({ open, close });
</script>
