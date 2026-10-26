<template>
  <div v-if="isOpen"
       class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300"
       @click.self="close">
    <div class="bg-white rounded-[2rem] max-w-lg w-full p-6 shadow-2xl relative border border-slate-100 flex flex-col max-h-[85vh] overflow-hidden">
      <!-- Header -->
      <div class="flex items-start justify-between gap-3 mb-4 shrink-0">
        <h3 class="text-xl font-black text-slate-900 flex items-center gap-2">
          🧭 Campus Explorer
        </h3>
        <button @click="close" class="h-8 w-8 rounded-full bg-slate-100 hover:bg-slate-200 grid place-items-center text-slate-500 transition shrink-0">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative mb-3 shrink-0">
        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input v-model="searchQuery"
               type="text"
               placeholder="Search by university, nickname or country…"
               class="w-full bg-slate-100 focus:bg-white border border-slate-200 focus:border-violet-500 rounded-2xl pl-10 pr-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 outline-none transition" />
      </div>

      <!-- Region Filter Pills -->
      <div class="flex gap-2 overflow-x-auto hide-scroll pb-2 mb-2 shrink-0">
        <button v-for="r in regions" :key="r.id"
                @click="selectedRegion = r.id"
                :class="['rounded-full px-3.5 py-1.5 text-xs font-black shrink-0 transition flex items-center gap-1.5',
                         selectedRegion === r.id ? 'bg-violet-50 text-violet-700 border-2 border-violet-500' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']">
          {{ r.flag }} {{ r.name }} · {{ r.count }}
        </button>
      </div>

      <!-- Manage Universities Link -->
      <div class="mb-3 shrink-0">
        <button @click="handleManageUniversities" class="text-xs font-black text-violet-600 hover:underline inline-flex items-center gap-1">
          Manage my universities
        </button>
      </div>

      <!-- Scrollable Campuses List -->
      <div class="overflow-y-auto space-y-6 pr-1 hide-scroll flex-1">
        <!-- Trending Section (if no query or query matches trending) -->
        <div v-if="!searchQuery && selectedRegion === 'all'" class="space-y-3">
          <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
            🔥 Trending Now
          </h4>
          <div class="space-y-2">
            <div v-for="c in trendingCampuses" :key="c.name"
                 class="flex items-center justify-between p-2.5 rounded-2xl border border-slate-100 hover:bg-slate-50 transition">
              <div class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 font-black text-xs grid place-items-center shrink-0">
                  {{ c.code }}
                </span>
                <div class="min-w-0">
                  <p class="font-black text-sm text-slate-900 truncate">{{ c.name }}</p>
                  <p class="text-[11px] font-semibold text-slate-400 truncate">{{ c.country }} · {{ c.posts }}</p>
                </div>
              </div>
              <button @click="toggleFollow(c)"
                      :class="['rounded-full px-4 py-1.5 text-xs font-black transition shrink-0 flex items-center gap-1',
                               c.isFollowing ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 hover:bg-slate-200 text-slate-800']">
                {{ c.isFollowing ? '★ Following' : '☆ Follow' }}
              </button>
            </div>
          </div>
        </div>

        <!-- All Campuses A–Z Section -->
        <div class="space-y-3">
          <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider">
            All Campuses · A–Z
          </h4>

          <div v-if="filteredCampuses.length === 0" class="text-center py-8 text-slate-400 font-bold text-xs">
            No campuses match your search.
          </div>

          <div v-else class="space-y-2">
            <div v-for="c in filteredCampuses" :key="c.name"
                 class="flex items-center justify-between p-2.5 rounded-2xl border border-slate-100 hover:bg-slate-50 transition">
              <div class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 font-black text-xs grid place-items-center shrink-0">
                  {{ c.code }}
                </span>
                <div class="min-w-0">
                  <p class="font-black text-sm text-slate-900 truncate">{{ c.name }}</p>
                  <p class="text-[11px] font-semibold text-slate-400 truncate">{{ c.country }} · {{ c.posts }}</p>
                </div>
              </div>
              <button @click="toggleFollow(c)"
                      :class="['rounded-full px-4 py-1.5 text-xs font-black transition shrink-0 flex items-center gap-1',
                               c.isFollowing ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 hover:bg-slate-200 text-slate-800']">
                {{ c.isFollowing ? '★ Following' : '☆ Follow' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const isOpen = ref(false);
const searchQuery = ref('');
const selectedRegion = ref('all');

const emit = defineEmits(['manageUniversities']);

const regions = ref([
  { id: 'all', name: 'All', flag: '🌏', count: 57 },
  { id: 'jm', name: 'Jamaica', flag: '🇯🇲', count: 6 },
  { id: 'us', name: 'United States', flag: '🇺🇸', count: 5 },
  { id: 'tt', name: 'Trinidad & Tobago', flag: '🇹🇹', count: 4 },
  { id: 'bs', name: 'Bahamas', flag: '🇧🇸', count: 3 },
  { id: 'bb', name: 'Barbados', flag: '🇧🇧', count: 2 },
]);

const campuses = ref([
  { code: 'BS', name: 'Univ. of The Bahamas', country: 'Bahamas', region: 'bs', posts: '2 posts', trending: true, isFollowing: false },
  { code: 'TT', name: 'UWI St. Augustine', country: 'Trinidad & Tobago', region: 'tt', posts: '2 posts', trending: true, isFollowing: true },
  { code: 'BB', name: 'UWI Cave Hill', country: 'Barbados', region: 'bb', posts: '1 post', trending: true, isFollowing: false },
  { code: 'JM', name: 'UWI Mona', country: 'Jamaica', region: 'jm', posts: '1 post', trending: true, isFollowing: false },
  { code: 'AI', name: 'Anguilla Community College', country: 'Anguilla', region: 'ai', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'SR', name: 'Anton de Kom University of Suriname', country: 'Suriname', region: 'sr', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'BS', name: 'Bahamas Technical & Vocational Institute', country: 'Bahamas', region: 'bs', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'TT', name: 'COSTAATT', country: 'Trinidad & Tobago', region: 'tt', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'US', name: 'FIU Caribbean Students Assoc.', country: 'United States', region: 'us', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'US', name: 'Howard University CSA', country: 'United States', region: 'us', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'JM', name: 'Northern Caribbean University', country: 'Jamaica', region: 'jm', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'GY', name: 'University of Guyana', country: 'Guyana', region: 'gy', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'PR', name: 'University of Puerto Rico', country: 'Puerto Rico', region: 'pr', posts: 'No posts yet', trending: false, isFollowing: false },
  { code: 'JM', name: 'UTech Jamaica', country: 'Jamaica', region: 'jm', posts: 'No posts yet', trending: false, isFollowing: false },
]);

const open = () => {
  isOpen.value = true;
};

const close = () => {
  isOpen.value = false;
};

const handleManageUniversities = () => {
  close();
  emit('manageUniversities');
};

const toggleFollow = (campus) => {
  campus.isFollowing = !campus.isFollowing;
  if (campus.isFollowing) {
    try {
      const saved = JSON.parse(localStorage.getItem('lk_my_universities') || '[]');
      if (!saved.some(u => u.name === campus.name)) {
        saved.push({ name: campus.name, flag: campus.code });
        localStorage.setItem('lk_my_universities', JSON.stringify(saved));
      }
    } catch (e) {}
  }
};

const trendingCampuses = computed(() => campuses.value.filter(c => c.trending));

const filteredCampuses = computed(() => {
  return campuses.value.filter(c => {
    const matchesRegion = selectedRegion.value === 'all' || c.region === selectedRegion.value;
    const q = searchQuery.value.toLowerCase().trim();
    const matchesQuery = !q || c.name.toLowerCase().includes(q) || c.country.toLowerCase().includes(q);
    return matchesRegion && matchesQuery;
  });
});

defineExpose({
  open,
  close,
  isOpen
});
</script>
